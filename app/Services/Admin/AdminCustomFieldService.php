<?php

namespace App\Services\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Tenant;
use App\Models\CustomField;
use App\Models\GlobalPicklist;
use App\Models\StandardFieldOverride;
use App\Models\TenantSetting;
use App\Http\Controllers\AdminController;

class AdminCustomFieldService
{
    use AdminTenantTrait;

    public function objectFields()
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);
        $this->ensureDefaultCustomFields($tenant);

        $moduleOptions = AdminController::$MODULE_OPTIONS;
        $dataTypes = AdminController::$DATA_TYPES;
        $standardFields = AdminController::$STANDARD_FIELDS;

        $globalPicklists = GlobalPicklist::on('tenant')->orderBy('name')->get()->map(function ($gp) {
            $gp->values = is_array($gp->values_json) ? $gp->values_json : (json_decode($gp->values_json, true) ?: []);
            return $gp;
        });

        $customFields = CustomField::on('tenant')->with('globalPicklist')->orderBy('module')->orderBy('field_label')->get();
        $standardOverrides = StandardFieldOverride::on('tenant')->get();

        return view('admin.object_fields', compact(
            'moduleOptions',
            'dataTypes',
            'standardFields',
            'globalPicklists',
            'customFields',
            'standardOverrides'
        ));
    }

    public function ensureDefaultCustomFields(?Tenant $tenant = null): void
    {
        $this->ensureTenantConnection($tenant);

        if (CustomField::on('tenant')->count() === 0) {
            $defaults = [
                // Leads custom fields
                [
                    'module'      => 'leads',
                    'field_name'  => 'cf_industry_subsector',
                    'field_label' => 'Industry Sub-Sector',
                    'data_type'   => 'Dropdown',
                    'options'     => 'Enterprise SaaS, FinTech & Banking, HealthTech, AI / Automation, Logistics & Cargo',
                    'is_required' => false,
                    'help_text'   => 'Granular vertical industry categorization for targeted qualification',
                ],
                [
                    'module'      => 'leads',
                    'field_name'  => 'cf_procurement_fit_score',
                    'field_label' => 'Procurement Fit Score',
                    'data_type'   => 'Rating',
                    'rating_max'  => 5,
                    'rating_icon' => 'star',
                    'is_required' => false,
                    'help_text'   => 'Visual 1 to 5 propensity rating based on vendor eligibility',
                ],
                [
                    'module'         => 'leads',
                    'field_name'     => 'cf_tax_id_masked',
                    'field_label'    => 'Tax Identification (EIN)',
                    'data_type'      => 'MaskedText',
                    'masked_pattern' => 'last4',
                    'is_required'    => false,
                    'help_text'      => 'Encrypted corporate tax identifier showing only the last 4 digits',
                ],

                // Accounts custom fields
                [
                    'module'      => 'accounts',
                    'field_name'  => 'cf_annual_contract_value',
                    'field_label' => 'Annual Contract Value (ACV)',
                    'data_type'   => 'Currency',
                    'is_required' => false,
                    'help_text'   => 'Committed recurring contract spend in USD across active subscriptions',
                ],
                [
                    'module'      => 'accounts',
                    'field_name'  => 'cf_procurement_sla_tier',
                    'field_label' => 'Procurement SLA Tier',
                    'data_type'   => 'Dropdown',
                    'options'     => 'Platinum Priority (4h), Gold Standard (8h), Silver (24h), Bronze Standard',
                    'is_required' => true,
                    'help_text'   => 'Operational service level response agreement with client or vendor',
                ],
                [
                    'module'                    => 'accounts',
                    'field_name'                => 'cf_open_deals_count',
                    'field_label'               => 'Active Pipeline Deals Count',
                    'data_type'                 => 'RollupSummary',
                    'rollup_child_module'       => 'opportunities',
                    'rollup_relationship_field' => 'account_id',
                    'rollup_type'               => 'COUNT',
                    'is_required'               => false,
                    'help_text'                 => 'Dynamic rollup counting open opportunities belonging to this account',
                ],

                // Opportunities custom fields
                [
                    'module'               => 'opportunities',
                    'field_name'           => 'cf_deal_registration_code',
                    'field_label'          => 'Deal Registration Code',
                    'data_type'            => 'AutoNumber',
                    'autonumber_prefix'    => 'OPP-REG-',
                    'autonumber_suffix'    => '',
                    'autonumber_padding'   => 5,
                    'autonumber_start_num' => 1001,
                    'is_required'          => false,
                    'help_text'            => 'Unique auto-generated enterprise deal registration code',
                ],
                [
                    'module'               => 'opportunities',
                    'field_name'           => 'cf_margin_percentage',
                    'field_label'          => 'Target Gross Margin %',
                    'data_type'            => 'Percentage',
                    'percentage_precision' => 2,
                    'percentage_min'       => 0,
                    'percentage_max'       => 100,
                    'is_required'          => false,
                    'help_text'            => 'Calculated margin percentage targeted on opportunity closing',
                ],
                [
                    'module'        => 'opportunities',
                    'field_name'    => 'cf_priority_status_color',
                    'field_label'   => 'Executive Priority Color',
                    'data_type'     => 'ColorPicker',
                    'color_presets' => '#3b82f6,#10b981,#f59e0b,#ef4444,#8b5cf6,#06b6d4',
                    'is_required'   => false,
                    'help_text'     => 'Color-coded visual indicator for executive radar prioritization',
                ],
                [
                    'module'           => 'opportunities',
                    'field_name'       => 'cf_special_terms_notes',
                    'field_label'      => 'Special Contract Rider Terms',
                    'data_type'        => 'RichText',
                    'rich_text_height' => 140,
                    'is_required'      => false,
                    'help_text'        => 'WYSIWYG formatted legal and commercial exceptions or non-standard terms',
                ],
            ];

            foreach ($defaults as $def) {
                CustomField::on('tenant')->create($def);
            }
        }
    }

    public function getCustomFields(Request $request)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);
        $this->ensureDefaultCustomFields($tenant);

        $query = CustomField::on('tenant')->with('globalPicklist');

        if ($request->has('module') && !empty($request->query('module'))) {
            $query->where('module', $request->query('module'));
        }

        $fields = $query->orderBy('field_label')->get();

        return response()->json($fields);
    }

    public function getCustomField(Request $request, $id)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);

        $field = CustomField::on('tenant')->with('globalPicklist')->find($id);
        if (!$field) {
            return response()->json(['success' => false, 'message' => 'Custom field not found.'], 404);
        }

        return response()->json($field);
    }

    public function createCustomField(Request $request)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);

        $validated = $request->validate([
            'module'      => 'required|string|max:60',
            'field_label' => 'nullable|string|max:150',
            'fieldLabel'  => 'nullable|string|max:150',
            'field_name'  => 'nullable|string|max:100',
            'fieldName'   => 'nullable|string|max:100',
            'data_type'   => 'nullable|string|max:50',
            'dataType'    => 'nullable|string|max:50',
        ]);

        $label = $request->input('fieldLabel', $request->input('field_label', 'New Custom Field'));
        $dataType = $request->input('dataType', $request->input('data_type', 'Text'));

        $rawName = $request->input('fieldName', $request->input('field_name'));
        if (empty($rawName) || $rawName === 'cf_') {
            $slug = Str::slug($label, '_');
            $rawName = "cf_{$slug}";
        } elseif (!Str::startsWith($rawName, 'cf_')) {
            $rawName = "cf_{$rawName}";
        }

        $isReq = $request->has('isRequired')
            ? filter_var($request->input('isRequired'), FILTER_VALIDATE_BOOLEAN)
            : ($request->has('is_required') ? filter_var($request->input('is_required'), FILTER_VALIDATE_BOOLEAN) : false);

        $isMulti = $request->has('is_multiselect')
            ? filter_var($request->input('is_multiselect'), FILTER_VALIDATE_BOOLEAN)
            : ($request->has('isMultiSelect') ? filter_var($request->input('isMultiSelect'), FILTER_VALIDATE_BOOLEAN) : false);

        $field = CustomField::on('tenant')->create([
            'module'                     => $request->input('module'),
            'field_name'                 => $rawName,
            'field_label'                => $label,
            'data_type'                  => $dataType,
            'options'                    => $request->input('options'),
            'is_required'                => $isReq,
            'default_value'              => $request->input('defaultValue', $request->input('default_value')),
            'help_text'                  => $request->input('helpText', $request->input('help_text')),
            'lookup_module'              => $request->input('lookup_module', $request->input('lookupModule')),
            'lookup_display_field'       => $request->input('lookup_display_field', $request->input('lookupDisplayField')),
            'formula_mode'               => $request->input('formula_mode', $request->input('formulaMode')),
            'formula_source_field'       => $request->input('formula_source_field', $request->input('formulaSourceField')),
            'formula_target_field'       => $request->input('formula_target_field', $request->input('formulaTargetField')),
            'formula_expression'         => $request->input('formula_expression', $request->input('formulaExpression')),
            'formula_return_type'        => $request->input('formula_return_type', $request->input('formulaReturnType')),
            'global_picklist_id'         => $request->input('global_picklist_id', $request->input('selectedGlobalPicklistId')),
            'is_multiselect'             => $isMulti,
            'autonumber_prefix'          => $request->input('autonumber_prefix', 'INV-'),
            'autonumber_suffix'          => $request->input('autonumber_suffix', ''),
            'autonumber_padding'         => (int)$request->input('autonumber_padding', 4),
            'autonumber_start_num'       => (int)$request->input('autonumber_start_num', 1),
            'autonumber_next_val'        => (int)$request->input('autonumber_start_num', 1),
            'rollup_child_module'        => $request->input('rollup_child_module', 'opportunities'),
            'rollup_relationship_field'  => $request->input('rollup_relationship_field', 'account_id'),
            'rollup_type'                => $request->input('rollup_type', 'COUNT'),
            'rollup_target_field'        => $request->input('rollup_target_field', 'amount'),
            'rollup_filter_json'         => $request->input('rollup_filter_json'),
            'rating_max'                 => (int)$request->input('rating_max', 5),
            'rating_icon'                => $request->input('rating_icon', 'star'),
            'percentage_precision'       => (int)$request->input('percentage_precision', 2),
            'percentage_min'             => $request->filled('percentage_min') ? (float)$request->input('percentage_min') : null,
            'percentage_max'             => $request->filled('percentage_max') ? (float)$request->input('percentage_max') : null,
            'radio_layout'               => $request->input('radio_layout', 'horizontal'),
            'masked_pattern'             => $request->input('masked_pattern', 'last4'),
            'color_presets'              => $request->input('color_presets', '#3b82f6,#10b981,#f59e0b,#ef4444,#8b5cf6,#06b6d4'),
            'rich_text_height'           => (int)$request->input('rich_text_height', 160),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Custom field '{$field->field_label}' created on '{$field->module}'.",
            'data'    => $field->load('globalPicklist'),
            'field'   => $field,
        ], 201);
    }

    public function updateCustomField(Request $request, $id)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);

        $field = CustomField::on('tenant')->find($id);
        if (!$field) {
            return response()->json(['success' => false, 'message' => 'Custom field not found.'], 404);
        }

        $updates = [];
        if ($request->has('fieldLabel') || $request->has('field_label')) {
            $updates['field_label'] = $request->input('fieldLabel', $request->input('field_label'));
        }
        if ($request->has('dataType') || $request->has('data_type')) {
            $updates['data_type'] = $request->input('dataType', $request->input('data_type'));
        }
        if ($request->has('options')) {
            $updates['options'] = $request->input('options');
        }
        if ($request->has('isRequired') || $request->has('is_required')) {
            $updates['is_required'] = filter_var($request->input('isRequired', $request->input('is_required')), FILTER_VALIDATE_BOOLEAN);
        }
        if ($request->has('defaultValue') || $request->has('default_value')) {
            $updates['default_value'] = $request->input('defaultValue', $request->input('default_value'));
        }
        if ($request->has('helpText') || $request->has('help_text')) {
            $updates['help_text'] = $request->input('helpText', $request->input('help_text'));
        }
        if ($request->has('global_picklist_id') || $request->has('selectedGlobalPicklistId')) {
            $updates['global_picklist_id'] = $request->input('global_picklist_id', $request->input('selectedGlobalPicklistId'));
        }
        if ($request->has('is_multiselect') || $request->has('isMultiSelect')) {
            $updates['is_multiselect'] = filter_var($request->input('is_multiselect', $request->input('isMultiSelect')), FILTER_VALIDATE_BOOLEAN);
        }

        // Additional dynamic type attributes
        foreach ([
            'lookup_module', 'lookup_display_field', 'formula_mode', 'formula_source_field',
            'formula_target_field', 'formula_expression', 'formula_return_type',
            'autonumber_prefix', 'autonumber_suffix', 'autonumber_padding', 'autonumber_start_num',
            'rollup_child_module', 'rollup_relationship_field', 'rollup_type', 'rollup_target_field',
            'rollup_filter_json', 'rating_max', 'rating_icon', 'percentage_precision',
            'percentage_min', 'percentage_max', 'radio_layout', 'masked_pattern',
            'color_presets', 'rich_text_height'
        ] as $attr) {
            if ($request->has($attr)) {
                $updates[$attr] = $request->input($attr);
            }
        }

        $field->update($updates);

        return response()->json([
            'success' => true,
            'message' => "Custom field '{$field->field_label}' updated successfully.",
            'data'    => $field->fresh()->load('globalPicklist'),
            'field'   => $field->fresh(),
        ]);
    }

    public function deleteCustomField(Request $request, $id)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);

        $field = CustomField::on('tenant')->find($id);
        if (!$field) {
            return response()->json(['success' => false, 'message' => 'Custom field not found.'], 404);
        }

        $label = $field->field_label;
        $field->delete();

        return response()->json([
            'success' => true,
            'message' => "Custom field '{$label}' removed successfully.",
        ]);
    }

    public function getStandardFieldOverrides(Request $request)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);

        $query = StandardFieldOverride::on('tenant');
        if ($request->has('module') && !empty($request->query('module'))) {
            $query->where('module', $request->query('module'));
        }

        return response()->json($query->get());
    }

    public function saveStandardFieldOverride(Request $request)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);

        $module = $request->input('module');
        $fieldName = $request->input('fieldName', $request->input('field_name'));

        if (!$module || !$fieldName) {
            return response()->json(['success' => false, 'message' => 'Module and field name are required.'], 422);
        }

        $fieldLabel = $request->input('fieldLabel', $request->input('field_label'));
        $isRequired = $request->has('isRequired')
            ? filter_var($request->input('isRequired'), FILTER_VALIDATE_BOOLEAN)
            : ($request->has('is_required') ? filter_var($request->input('is_required'), FILTER_VALIDATE_BOOLEAN) : false);
        $options = $request->input('options');
        $helpText = $request->input('helpText', $request->input('help_text'));

        $override = StandardFieldOverride::on('tenant')->updateOrCreate(
            ['module' => $module, 'field_name' => $fieldName],
            [
                'field_label' => $fieldLabel,
                'is_required' => $isRequired,
                'options'     => $options,
                'help_text'   => $helpText,
                'updated_at'  => now(),
            ]
        );

        return response()->json([
            'success'  => true,
            'message'  => "Standard field '{$fieldName}' on '{$module}' customized successfully.",
            'override' => $override,
        ]);
    }

    public function getGlobalPicklists(Request $request)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);

        $picklists = GlobalPicklist::on('tenant')->orderBy('name')->get()->map(function ($gp) {
            $vals = is_array($gp->values_json) ? $gp->values_json : (json_decode($gp->values_json, true) ?: []);
            $gp->values = $vals;
            return $gp;
        });

        return response()->json($picklists);
    }

    public function getGlobalPicklist(Request $request, $id)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);

        $gp = GlobalPicklist::on('tenant')->find($id);
        if (!$gp) {
            return response()->json(['success' => false, 'message' => 'Global picklist not found.'], 404);
        }

        $gp->values = is_array($gp->values_json) ? $gp->values_json : (json_decode($gp->values_json, true) ?: []);
        return response()->json($gp);
    }

    public function createGlobalPicklist(Request $request)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);

        $validated = $request->validate([
            'name'        => 'required|string|max:100',
            'api_name'    => 'nullable|string|max:100',
            'apiName'     => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'values'      => 'required',
        ]);

        $name = $validated['name'];
        $apiName = $request->input('apiName', $request->input('api_name'));
        if (empty($apiName)) {
            $apiName = 'gp_' . Str::slug($name, '_');
        } elseif (!Str::startsWith($apiName, 'gp_') && !Str::startsWith($apiName, 'gp')) {
            $apiName = "gp_{$apiName}";
        }

        $rawVals = $request->input('values');
        if (is_string($rawVals)) {
            $parsed = array_filter(array_map('trim', preg_split('/[\r\n,]+/', $rawVals)));
            $vals = array_values($parsed);
        } else {
            $vals = array_values((array)$rawVals);
        }

        $gp = GlobalPicklist::on('tenant')->create([
            'name'        => $name,
            'api_name'    => $apiName,
            'description' => $request->input('description'),
            'values_json' => $vals,
        ]);

        $gp->values = $vals;

        return response()->json([
            'success' => true,
            'message' => "Global picklist '{$gp->name}' created successfully.",
            'data'    => $gp,
        ], 201);
    }

    public function updateGlobalPicklist(Request $request, $id)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);

        $gp = GlobalPicklist::on('tenant')->find($id);
        if (!$gp) {
            return response()->json(['success' => false, 'message' => 'Global picklist not found.'], 404);
        }

        $updates = [];
        if ($request->has('name')) $updates['name'] = $request->input('name');
        if ($request->has('description')) $updates['description'] = $request->input('description');
        if ($request->has('values')) {
            $rawVals = $request->input('values');
            if (is_string($rawVals)) {
                $parsed = array_filter(array_map('trim', preg_split('/[\r\n,]+/', $rawVals)));
                $updates['values_json'] = array_values($parsed);
            } else {
                $updates['values_json'] = array_values((array)$rawVals);
            }
        }

        $gp->update($updates);
        $gp->values = is_array($gp->values_json) ? $gp->values_json : (json_decode($gp->values_json, true) ?: []);

        return response()->json([
            'success' => true,
            'message' => "Global picklist '{$gp->name}' updated successfully.",
            'data'    => $gp,
        ]);
    }

    public function deleteGlobalPicklist(Request $request, $id)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);

        $gp = GlobalPicklist::on('tenant')->find($id);
        if (!$gp) {
            return response()->json(['success' => false, 'message' => 'Global picklist not found.'], 404);
        }

        $name = $gp->name;
        $gp->delete();

        return response()->json([
            'success' => true,
            'message' => "Global picklist '{$name}' deleted successfully.",
        ]);
    }

    public function getLeadMappings(Request $request)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);
        $this->ensureDefaultCustomFields($tenant);

        $allCustomFields = CustomField::on('tenant')->whereIn('module', ['leads', 'accounts', 'contacts', 'opportunities'])->get();

        $formatFields = function (string $module) use ($allCustomFields) {
            $std = AdminController::$STANDARD_FIELDS[$module] ?? [];
            $result = [];

            foreach ($std as $sf) {
                $isLocked = ($module === 'leads' && in_array($sf['name'], ['company', 'first_name', 'last_name', 'email']));
                $result[] = [
                    'id'          => "std_{$module}_{$sf['name']}",
                    'field_name'  => $sf['name'],
                    'field_label' => $sf['label'],
                    'data_type'   => $sf['type'],
                    'options'     => null,
                    'help_text'   => $sf['description'] ?? null,
                    'is_standard' => true,
                    'is_locked'   => $isLocked,
                ];
            }

            foreach ($allCustomFields->where('module', $module) as $cf) {
                $result[] = [
                    'id'          => (string)$cf->id,
                    'field_name'  => $cf->field_name,
                    'field_label' => $cf->field_label,
                    'data_type'   => $cf->data_type,
                    'options'     => $cf->options,
                    'help_text'   => $cf->help_text,
                    'is_standard' => false,
                    'is_locked'   => false,
                ];
            }

            return $result;
        };

        $leadFields = $formatFields('leads');
        $accountFields = $formatFields('accounts');
        $contactFields = $formatFields('contacts');
        $opportunityFields = $formatFields('opportunities');

        $setting = TenantSetting::on('tenant')->where('key', 'lead_conversion_mappings')->first();
        $mappings = [];
        if ($setting && !empty($setting->value)) {
            $mappings = is_array($setting->value) ? $setting->value : json_decode($setting->value, true);
        }

        if (empty($mappings)) {
            $mappings = [
                [
                    'source_field_name'  => 'company',
                    'source_field_label' => 'Company Name',
                    'source_data_type'   => 'Text',
                    'target_module'      => 'accounts',
                    'target_field_name'  => 'name',
                    'target_field_label' => 'Account Name',
                    'target_data_type'   => 'Text',
                    'is_active'          => 1,
                ],
                [
                    'source_field_name'  => 'first_name',
                    'source_field_label' => 'First Name',
                    'source_data_type'   => 'Text',
                    'target_module'      => 'contacts',
                    'target_field_name'  => 'first_name',
                    'target_field_label' => 'First Name',
                    'target_data_type'   => 'Text',
                    'is_active'          => 1,
                ],
                [
                    'source_field_name'  => 'last_name',
                    'source_field_label' => 'Last Name',
                    'source_data_type'   => 'Text',
                    'target_module'      => 'contacts',
                    'target_field_name'  => 'last_name',
                    'target_field_label' => 'Last Name',
                    'target_data_type'   => 'Text',
                    'is_active'          => 1,
                ],
                [
                    'source_field_name'  => 'email',
                    'source_field_label' => 'Email Address',
                    'source_data_type'   => 'Email',
                    'target_module'      => 'contacts',
                    'target_field_name'  => 'email',
                    'target_field_label' => 'Email Address',
                    'target_data_type'   => 'Email',
                    'is_active'          => 1,
                ],
                [
                    'source_field_name'  => 'phone',
                    'source_field_label' => 'Phone Number',
                    'source_data_type'   => 'Phone',
                    'target_module'      => 'contacts',
                    'target_field_name'  => 'phone',
                    'target_field_label' => 'Phone',
                    'target_data_type'   => 'Phone',
                    'is_active'          => 1,
                ],
            ];
        }

        return response()->json([
            'leadFields'        => $leadFields,
            'accountFields'     => $accountFields,
            'contactFields'     => $contactFields,
            'opportunityFields' => $opportunityFields,
            'mappings'          => array_values($mappings),
        ]);
    }

    public function saveLeadMappings(Request $request)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);

        $mappings = $request->input('mappings', $request->all());
        if (isset($mappings['mappings'])) {
            $mappings = $mappings['mappings'];
        }

        $clean = array_values((array)$mappings);

        TenantSetting::on('tenant')->updateOrCreate(
            ['key' => 'lead_conversion_mappings'],
            ['value' => json_encode($clean)]
        );

        return response()->json([
            'success'  => true,
            'message'  => 'Lead conversion field mappings saved successfully!',
            'mappings' => $clean,
        ]);
    }

    public function createTargetFieldForLead(Request $request)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);

        $targetModule = $request->input('targetModule');
        $leadFieldId = $request->input('leadFieldId');

        $leadField = CustomField::on('tenant')->find($leadFieldId);
        if (!$leadField) {
            return response()->json(['success' => false, 'message' => 'Source lead field not found.'], 404);
        }

        $existing = CustomField::on('tenant')->where('module', $targetModule)->where('field_name', $leadField->field_name)->first();
        if ($existing) {
            return response()->json([
                'success'      => true,
                'createdField' => $existing,
                'message'      => "Target field '{$leadField->field_name}' already exists on {$targetModule}.",
            ]);
        }

        $newField = CustomField::on('tenant')->create([
            'module'               => $targetModule,
            'field_name'           => $leadField->field_name,
            'field_label'          => $leadField->field_label,
            'data_type'            => $leadField->data_type,
            'options'              => $leadField->options,
            'is_required'          => false,
            'help_text'            => $leadField->help_text,
            'global_picklist_id'   => $leadField->global_picklist_id,
            'rating_max'           => $leadField->rating_max,
            'rating_icon'          => $leadField->rating_icon,
            'percentage_precision' => $leadField->percentage_precision,
            'masked_pattern'       => $leadField->masked_pattern,
            'color_presets'        => $leadField->color_presets,
        ]);

        return response()->json([
            'success'      => true,
            'createdField' => $newField,
            'message'      => "Created matching field '{$newField->field_label}' on {$targetModule}.",
        ]);
    }

    public function autoMatchLeadMappings(Request $request)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);

        $targetModule = $request->input('targetModule', 'accounts');

        $res = $this->getLeadMappings($request)->getData(true);
        $leadFields = $res['leadFields'] ?? [];
        $targetFields = match ($targetModule) {
            'accounts'      => $res['accountFields'] ?? [],
            'contacts'      => $res['contactFields'] ?? [],
            'opportunities' => $res['opportunityFields'] ?? [],
            default         => [],
        };

        $proposed = [];
        foreach ($leadFields as $lf) {
            $lfName = strtolower(str_replace(['cf_', '_', ' '], '', $lf['field_name']));
            $lfLabel = strtolower(str_replace([' ', '_'], '', $lf['field_label']));

            foreach ($targetFields as $tf) {
                $tfName = strtolower(str_replace(['cf_', '_', ' '], '', $tf['field_name']));
                $tfLabel = strtolower(str_replace([' ', '_'], '', $tf['field_label']));

                if ($lfName === $tfName || $lfLabel === $tfLabel) {
                    $proposed[] = [
                        'source_field_name'  => $lf['field_name'],
                        'source_field_label' => $lf['field_label'],
                        'source_data_type'   => $lf['data_type'],
                        'target_module'      => $targetModule,
                        'target_field_name'  => $tf['field_name'],
                        'target_field_label' => $tf['field_label'],
                        'target_data_type'   => $tf['data_type'],
                    ];
                    break;
                }
            }
        }

        return response()->json([
            'success'  => true,
            'proposed' => $proposed,
        ]);
    }
}
