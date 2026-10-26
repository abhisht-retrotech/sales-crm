<?php

namespace App\Services\DynamicFields;

use App\Models\Account;
use App\Models\Contact;
use App\Models\CustomField;
use App\Models\CustomFieldValue;
use App\Models\Lead;
use App\Models\Role;
use App\Models\TenantUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DynamicFieldMappingService
{
    /**
     * In-memory cache for resolved entity names by [module][id].
     */
    protected static array $entityCache = [];

    /**
     * Custom user-defined field-to-target mappings.
     * [ 'leads.approved' => ['target_module' => 'users', ...] ]
     */
    protected static array $customMappings = [];

    /**
     * Register a custom mapping for a specific module and field.
     */
    public static function registerMapping(string $module, string $fieldName, string $targetModule, string $keyColumn = 'id', string $displayColumn = 'name'): void
    {
        $key = strtolower("{$module}.{$fieldName}");
        self::$customMappings[$key] = [
            'target_module'  => $targetModule,
            'key_column'     => $keyColumn,
            'display_column' => $displayColumn,
        ];
    }

    /**
     * Determine the target lookup mapping for a custom field dynamically.
     */
    public function resolveTargetMapping(CustomField|string $field, string $module = 'leads'): ?array
    {
        $fieldName = $field instanceof CustomField ? $field->field_name : (string)$field;
        $fieldLabel = $field instanceof CustomField ? ($field->field_label ?? '') : '';
        $lookupModule = $field instanceof CustomField ? ($field->lookup_module ?? null) : null;
        $lookupDisplayField = $field instanceof CustomField ? ($field->lookup_display_field ?? null) : null;
        $dataType = strtolower($field instanceof CustomField ? ($field->data_type ?? '') : '');

        $customKey = strtolower("{$module}.{$fieldName}");
        if (isset(self::$customMappings[$customKey])) {
            return $this->formatMapping(
                self::$customMappings[$customKey]['target_module'],
                self::$customMappings[$customKey]['key_column'] ?? 'id',
                self::$customMappings[$customKey]['display_column'] ?? 'name'
            );
        }

        // 1. Explicit lookup_module defined in custom_fields table
        if (!empty($lookupModule)) {
            $displayCol = !empty($lookupDisplayField) ? $lookupDisplayField : $this->getDefaultDisplayColumn($lookupModule);
            return $this->formatMapping($lookupModule, 'id', $displayCol);
        }

        // 2. Keyword & semantic heuristic resolution
        $nameLower = strtolower($fieldName);
        $labelLower = strtolower($fieldLabel);

        // Check if field represents a User / Approver
        $userKeywords = ['approved', 'approver', 'is_approved', 'approved_by', 'verified_by', 'assigned_user', 'user_id', 'by_user', 'manager_id', 'rep_id', 'owner_user'];
        foreach ($userKeywords as $kw) {
            if ($nameLower === $kw || str_contains($nameLower, $kw) || str_contains($labelLower, 'approved by') || str_contains($labelLower, 'approver')) {
                return $this->formatMapping('users', 'id', 'name');
            }
        }

        // Check if field represents an Account
        if (str_contains($nameLower, 'account') || str_contains($nameLower, 'company_id') || str_contains($nameLower, 'vendor_id')) {
            return $this->formatMapping('accounts', 'id', 'name');
        }

        // Check if field represents a Contact
        if (str_contains($nameLower, 'contact') && !str_contains($nameLower, 'contact_method')) {
            return $this->formatMapping('contacts', 'id', 'name');
        }

        // Check if field represents a Lead
        if (str_contains($nameLower, 'lead') && !str_contains($nameLower, 'leads')) {
            return $this->formatMapping('leads', 'id', 'name');
        }

        // Check if field represents a Role
        if (str_contains($nameLower, 'role')) {
            return $this->formatMapping('roles', 'id', 'name');
        }

        // If data type is explicitly 'lookup' or 'user'
        if ($dataType === 'user') {
            return $this->formatMapping('users', 'id', 'name');
        }

        return null;
    }

    /**
     * Format mapping details into a standardized array.
     */
    protected function formatMapping(string $targetModule, string $keyColumn = 'id', string $displayColumn = 'name'): array
    {
        $targetModule = strtolower(trim($targetModule));
        $tableMap = [
            'users'         => 'users',
            'user'          => 'users',
            'accounts'      => 'accounts',
            'account'       => 'accounts',
            'contacts'      => 'contacts',
            'contact'       => 'contacts',
            'leads'         => 'leads',
            'lead'          => 'leads',
            'opportunities' => 'opportunities',
            'opportunity'   => 'opportunities',
            'roles'         => 'roles',
            'role'          => 'roles',
        ];

        $modelMap = [
            'users'         => TenantUser::class,
            'accounts'      => Account::class,
            'contacts'      => Contact::class,
            'leads'         => Lead::class,
            'roles'         => Role::class,
        ];

        $table = $tableMap[$targetModule] ?? $targetModule;
        $model = $modelMap[$targetModule] ?? null;

        return [
            'target_module'  => $targetModule,
            'target_table'   => $table,
            'key_column'     => $keyColumn,
            'display_column' => $displayColumn,
            'model_class'    => $model,
        ];
    }

    /**
     * Get default display column for target module.
     */
    protected function getDefaultDisplayColumn(string $module): string
    {
        $map = [
            'users'         => 'name',
            'accounts'      => 'name',
            'contacts'      => 'name',
            'leads'         => 'name',
            'opportunities' => 'name',
            'roles'         => 'name',
        ];
        return $map[strtolower($module)] ?? 'name';
    }

    /**
     * Get the tenant database connection or fallback to default.
     */
    public function tenantDb(): \Illuminate\Database\ConnectionInterface
    {
        try {
            if (config('database.connections.tenant.database')) {
                return DB::connection('tenant');
            }
        } catch (\Throwable $e) {}

        return DB::connection();
    }

    /**
     * Resolve a single target entity display name by target module and ID.
     */
    public function resolveDisplayValue(string $targetModule, int|string $id): ?string
    {
        if (empty($id)) {
            return null;
        }

        $id = (string)$id;
        $targetModule = strtolower(trim($targetModule));

        if (isset(self::$entityCache[$targetModule][$id])) {
            return self::$entityCache[$targetModule][$id];
        }

        $mapping = $this->formatMapping($targetModule);
        $table = $mapping['target_table'];
        $displayCol = $mapping['display_column'];

        try {
            $db = $this->tenantDb();
            if ($targetModule === 'contacts') {
                $row = $db->table($table)
                    ->where('id', $id)
                    ->select(['id', 'first_name', 'last_name'])
                    ->first();
                if ($row) {
                    $fullName = trim(($row->first_name ?? '') . ' ' . ($row->last_name ?? ''));
                    return self::$entityCache[$targetModule][$id] = $fullName ?: "Contact #{$id}";
                }
            } elseif ($targetModule === 'leads') {
                $row = $db->table($table)
                    ->where('id', $id)
                    ->select(['id', 'first_name', 'last_name', 'company'])
                    ->first();
                if ($row) {
                    $fullName = trim(($row->first_name ?? '') . ' ' . ($row->last_name ?? ''));
                    return self::$entityCache[$targetModule][$id] = $fullName ?: ($row->company ?: "Lead #{$id}");
                }
            } else {
                $row = $db->table($table)
                    ->where('id', $id)
                    ->first([$displayCol]);
                if ($row && isset($row->{$displayCol})) {
                    return self::$entityCache[$targetModule][$id] = (string)$row->{$displayCol};
                }
            }
        } catch (\Throwable $e) {
            // Silently fallback
        }

        return self::$entityCache[$targetModule][$id] = null;
    }

    /**
     * Prime entity cache in bulk for a list of IDs to eliminate N+1 queries.
     */
    public function primeEntityCache(string $targetModule, array $ids): void
    {
        $ids = array_values(array_unique(array_filter($ids)));
        if (empty($ids)) {
            return;
        }

        $targetModule = strtolower(trim($targetModule));
        $missing = [];
        foreach ($ids as $id) {
            $idStr = (string)$id;
            if (!isset(self::$entityCache[$targetModule][$idStr])) {
                $missing[] = $id;
            }
        }

        if (empty($missing)) {
            return;
        }

        $mapping = $this->formatMapping($targetModule);
        $table = $mapping['target_table'];
        $displayCol = $mapping['display_column'];

        try {
            $db = $this->tenantDb();
            if ($targetModule === 'contacts') {
                $rows = $db->table($table)
                    ->whereIn('id', $missing)
                    ->get(['id', 'first_name', 'last_name']);
                foreach ($rows as $r) {
                    $name = trim(($r->first_name ?? '') . ' ' . ($r->last_name ?? ''));
                    self::$entityCache[$targetModule][(string)$r->id] = $name ?: "Contact #{$r->id}";
                }
            } elseif ($targetModule === 'leads') {
                $rows = $db->table($table)
                    ->whereIn('id', $missing)
                    ->get(['id', 'first_name', 'last_name', 'company']);
                foreach ($rows as $r) {
                    $name = trim(($r->first_name ?? '') . ' ' . ($r->last_name ?? ''));
                    self::$entityCache[$targetModule][(string)$r->id] = $name ?: ($r->company ?: "Lead #{$r->id}");
                }
            } else {
                $rows = $db->table($table)
                    ->whereIn('id', $missing)
                    ->get(['id', $displayCol]);
                foreach ($rows as $r) {
                    self::$entityCache[$targetModule][(string)$r->id] = $r->{$displayCol} ?? null;
                }
            }
        } catch (\Throwable $e) {
            // Silently fallback
        }
    }

    /**
     * Dynamically resolve and map all custom fields on a single record.
     * Stored value is kept as raw ID, while {field}_name and {field}_display are enriched.
     */
    public function resolveRecord(array $data, string $module = 'leads'): array
    {
        $recordId = $data['id'] ?? null;
        $customData = is_array($data['custom_data'] ?? null)
            ? $data['custom_data']
            : (json_decode($data['custom_data'] ?? '[]', true) ?: []);

        // Fetch all configured custom fields for this module
        $configuredFields = $this->getConfiguredFields($module);

        foreach ($configuredFields as $cf) {
            $fName = $cf->field_name;
            $mapping = $this->resolveTargetMapping($cf, $module);
            if (!$mapping) {
                continue;
            }

            // Retrieve the stored ID value
            $val = $customData[$fName] ?? ($data[$fName] ?? null);
            if (!empty($val) && is_numeric($val)) {
                $id = (int)$val;
                $resolvedName = $this->resolveDisplayValue($mapping['target_module'], $id);

                if (!empty($resolvedName)) {
                    // Enrich root data
                    $data[$fName] = $id; // Keep scalar ID for forms and selectors
                    $data["{$fName}_id"] = $id;
                    $data["{$fName}_name"] = $resolvedName;
                    $data["{$fName}_display"] = $resolvedName;
                    $data["{$fName}_label"] = $resolvedName;
                    $data["_resolved_{$fName}"] = [
                        'id'      => $id,
                        'name'    => $resolvedName,
                        'display' => $resolvedName,
                        'module'  => $mapping['target_module'],
                    ];

                    // Also enrich custom_data map
                    $customData[$fName] = $id;
                    $customData["{$fName}_id"] = $id;
                    $customData["{$fName}_name"] = $resolvedName;
                    $customData["{$fName}_display"] = $resolvedName;
                    $customData["{$fName}_label"] = $resolvedName;
                }
            }
        }

        // Also check if any custom_data keys by convention map to lookups even if not in custom_fields table
        foreach ($customData as $key => $val) {
            if (is_string($key) && (str_contains($key, 'approved') || str_contains($key, 'approver') || str_contains($key, '_by')) && is_numeric($val) && !isset($data["{$key}_name"])) {
                $resolvedName = $this->resolveDisplayValue('users', (int)$val);
                if (!empty($resolvedName)) {
                    $data[$key] = (int)$val;
                    $data["{$key}_id"] = (int)$val;
                    $data["{$key}_name"] = $resolvedName;
                    $data["{$key}_display"] = $resolvedName;
                    $data["{$key}_label"] = $resolvedName;
                    $customData[$key] = (int)$val;
                    $customData["{$key}_name"] = $resolvedName;
                }
            }
        }

        $data['custom_data'] = $customData;
        return $data;
    }

    /**
     * Bulk resolve and enrich an entire collection of records.
     */
    public function resolveCollection(iterable $records, string $module = 'leads'): Collection
    {
        $items = collect($records)->map(fn($r) => is_array($r) ? $r : $r->toArray());
        if ($items->isEmpty()) {
            return $items;
        }

        $configuredFields = $this->getConfiguredFields($module);

        // Pre-gather all IDs per target module for bulk querying
        $moduleIdsMap = [];
        foreach ($configuredFields as $cf) {
            $mapping = $this->resolveTargetMapping($cf, $module);
            if (!$mapping) {
                continue;
            }
            $targetMod = $mapping['target_module'];
            $fName = $cf->field_name;

            foreach ($items as $item) {
                $custom = is_array($item['custom_data'] ?? null)
                    ? $item['custom_data']
                    : (json_decode($item['custom_data'] ?? '[]', true) ?: []);
                $val = $custom[$fName] ?? ($item[$fName] ?? null);
                if (!empty($val) && is_numeric($val)) {
                    $moduleIdsMap[$targetMod][] = (int)$val;
                }
            }
        }

        // Prime cache in bulk for each target module
        foreach ($moduleIdsMap as $targetMod => $ids) {
            $this->primeEntityCache($targetMod, $ids);
        }

        // Now resolve each record with O(1) in-memory lookups
        return $items->map(fn($item) => $this->resolveRecord($item, $module));
    }

    /**
     * Clean and format input payload so that lookup fields always store the clean integer ID.
     */
    public function prepareCustomDataForSave(array $inputCustom, string $module = 'leads'): array
    {
        $configuredFields = $this->getConfiguredFields($module);
        $cleanData = [];

        foreach ($configuredFields as $cf) {
            $fName = $cf->field_name;
            if (!array_key_exists($fName, $inputCustom)) {
                continue;
            }

            $rawVal = $inputCustom[$fName];
            $mapping = $this->resolveTargetMapping($cf, $module);

            if ($mapping) {
                // If input is an array (e.g. { id: 2, name: 'Alex' }), extract ID
                if (is_array($rawVal) && isset($rawVal['id'])) {
                    $rawVal = $rawVal['id'];
                }

                // If input is a numeric ID, cast to integer
                if (is_numeric($rawVal)) {
                    $cleanData[$fName] = (int)$rawVal;
                } elseif (is_string($rawVal) && trim($rawVal) !== '') {
                    // If a user passed a name instead of ID, attempt reverse lookup
                    $targetTable = $mapping['target_table'];
                    $displayCol = $mapping['display_column'];
                    try {
                        $found = $this->tenantDb()->table($targetTable)
                            ->where($displayCol, trim($rawVal))
                            ->first(['id']);
                        $cleanData[$fName] = $found ? (int)$found->id : $rawVal;
                    } catch (\Throwable $e) {
                        $cleanData[$fName] = $rawVal;
                    }
                } else {
                    $cleanData[$fName] = null;
                }
            } else {
                $cleanData[$fName] = $rawVal;
            }
        }

        // Retain any remaining input fields not in custom_fields table
        foreach ($inputCustom as $k => $v) {
            if (!array_key_exists($k, $cleanData) && !str_ends_with($k, '_name') && !str_ends_with($k, '_display') && !str_starts_with($k, '_resolved_')) {
                // Check if this unconfigured field is an approver / user by keyword
                if ((str_contains($k, 'approved') || str_contains($k, 'approver')) && is_numeric($v)) {
                    $cleanData[$k] = (int)$v;
                } else {
                    $cleanData[$k] = is_array($v) && isset($v['id']) ? $v['id'] : $v;
                }
            }
        }

        return $cleanData;
    }

    /**
     * Synchronize custom field values to the relational custom_field_values table.
     */
    public function syncCustomFieldValues(string $module, int|string $recordId, array $customData): void
    {
        if (empty($recordId) || empty($customData)) {
            return;
        }

        try {
            $configuredFields = $this->getConfiguredFields($module)->keyBy('field_name');

            foreach ($customData as $fName => $val) {
                if (str_ends_with($fName, '_name') || str_ends_with($fName, '_display') || str_starts_with($fName, '_')) {
                    continue;
                }

                $cf = $configuredFields->get($fName);
                if (!$cf) {
                    continue;
                }

                $strVal = is_array($val) ? json_encode($val) : (string)$val;

                $this->getCustomFieldValueQuery()->updateOrCreate(
                    [
                        'module'          => $module,
                        'record_id'       => (string)$recordId,
                        'custom_field_id' => $cf->id,
                    ],
                    [
                        'value'           => $strVal,
                    ]
                );
            }
        } catch (\Throwable $e) {
            // Silently ignore if table not present or during test isolation
        }
    }

    /**
     * Get matching entity IDs for a search string on a dynamic lookup field.
     */
    public function getMatchingIdsForSearch(string $module, string $fieldName, string $search): array
    {
        $search = trim($search);
        if ($search === '') {
            return [];
        }

        $cf = $this->getCustomFieldQuery()
            ->where('module', $module)
            ->where('field_name', $fieldName)
            ->first();

        $mapping = $this->resolveTargetMapping($cf ?: $fieldName, $module);
        if (!$mapping) {
            return [];
        }

        try {
            return $this->tenantDb()->table($mapping['target_table'])
                ->where($mapping['display_column'], 'like', "%{$search}%")
                ->pluck('id')
                ->map(fn($id) => (int)$id)
                ->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Dynamically filter / search a query on a dynamic lookup custom field.
     */
    public function applyDynamicLookupFilter(Builder $query, string $module, string $fieldName, string $search): Builder
    {
        $search = trim($search);
        if ($search === '') {
            return $query;
        }

        $cf = $this->getCustomFieldQuery()
            ->where('module', $module)
            ->where('field_name', $fieldName)
            ->first();

        $mapping = $this->resolveTargetMapping($cf ?: $fieldName, $module);
        if (!$mapping) {
            return $query;
        }

        $table = $mapping['target_table'];
        $displayCol = $mapping['display_column'];

        try {
            $matchingIds = $this->tenantDb()->table($table)
                ->where($displayCol, 'like', "%{$search}%")
                ->pluck('id')
                ->map(fn($id) => (int)$id)
                ->all();

            if (empty($matchingIds)) {
                return $query->whereRaw('1 = 0'); // No matches found
            }

            return $query->where(function ($q) use ($fieldName, $matchingIds) {
                foreach ($matchingIds as $id) {
                    $q->orWhere("custom_data->{$fieldName}", (string)$id)
                      ->orWhere("custom_data->{$fieldName}", $id);
                }
            });
        } catch (\Throwable $e) {
            return $query;
        }
    }

    /**
     * Cached retrieval of configured custom fields for a module.
     */
    public function getConfiguredFields(string $module): Collection
    {
        try {
            return $this->getCustomFieldQuery()
                ->where('module', $module)
                ->get();
        } catch (\Throwable $e) {
            return collect();
        }
    }

    /**
     * Custom field query builder respecting active connection.
     */
    public function getCustomFieldQuery()
    {
        try {
            if (config('database.connections.tenant.database')) {
                return CustomField::on('tenant');
            }
        } catch (\Throwable $e) {}

        return CustomField::query();
    }

    /**
     * Custom field value query builder respecting active connection.
     */
    public function getCustomFieldValueQuery()
    {
        try {
            if (config('database.connections.tenant.database')) {
                return CustomFieldValue::on('tenant');
            }
        } catch (\Throwable $e) {}

        return CustomFieldValue::query();
    }
}
