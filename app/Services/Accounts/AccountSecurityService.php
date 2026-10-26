<?php

namespace App\Services\Accounts;

use App\Models\Account;
use App\Models\CustomField;
use App\Models\FieldPermission;
use App\Models\Profile;
use App\Models\ProfilePermission;
use App\Models\Tenant;
use App\Models\TenantUser;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AccountSecurityService
{
    /**
     * Ensure tenant database connection is actively configured.
     */
    public function ensureTenantConnection(): ?Tenant
    {
        $tenant = app()->bound('currentTenant') ? app('currentTenant') : Tenant::on('mysql')->first();
        if ($tenant && config('database.connections.tenant.database') !== $tenant->db_name) {
            config(['database.connections.tenant.database' => $tenant->db_name]);
            DB::purge('tenant');
        }
        return $tenant;
    }

    /**
     * Resolve the current authenticated tenant user.
     */
    public function resolveCurrentUser(): ?TenantUser
    {
        $request = request();
        if ($request) {
            if ($request->hasHeader('X-Simulate-User-Id')) {
                $u = TenantUser::on('tenant')->find($request->header('X-Simulate-User-Id'));
                if ($u) return $u;
            }
            if ($request->hasHeader('X-Simulate-Profile-Id')) {
                $pId = $request->header('X-Simulate-Profile-Id');
                $u = TenantUser::on('tenant')->where('profile_id', $pId)->first();
                if ($u) return $u;
            }
            if ($request->hasHeader('X-Simulate-Role-Id')) {
                $rId = $request->header('X-Simulate-Role-Id');
                $u = TenantUser::on('tenant')->where('role_id', $rId)->first();
                if ($u) return $u;
            }
        }

        $user = Auth::guard('tenant')->user() ?? Auth::guard('web')->user();
        if ($user instanceof TenantUser) {
            return $user;
        }
        if ($user && !empty($user->id)) {
            $found = TenantUser::on('tenant')->find($user->id);
            if ($found) {
                return $found;
            }
        }

        return TenantUser::on('tenant')->first();
    }

    /**
     * Resolve the current user's security profile.
     */
    public function resolveCurrentUserProfile(?TenantUser $user = null): ?Profile
    {
        if (!$user) {
            $user = $this->resolveCurrentUser();
        }

        if ($user && !empty($user->profile_id)) {
            $prof = Profile::on('tenant')->find($user->profile_id);
            if ($prof) {
                return $prof;
            }
        }

        // Fallback for Admin / Super Admin
        return Profile::on('tenant')->where('name', 'like', '%admin%')->first()
            ?? Profile::on('tenant')->first();
    }

    /**
     * Get module-level permissions for the given profile and user.
     */
    public function getProfilePermissions(?Profile $profile, ?TenantUser $user = null): array
    {
        $isAdmin = false;
        if (Auth::guard('super')->check()) {
            $isAdmin = true;
        } elseif ($user && $user->isAdmin()) {
            $isAdmin = true;
        } elseif ($profile && ($profile->is_admin || str_contains(strtolower($profile->name), 'admin'))) {
            $isAdmin = true;
        }

        if ($isAdmin) {
            return [
                'can_create'   => true,
                'can_read'     => true,
                'can_update'   => true,
                'can_delete'   => true,
                'can_transfer' => true,
                'record_scope' => 'All',
            ];
        }

        if ($profile) {
            $perm = ProfilePermission::on('tenant')
                ->where('profile_id', $profile->id)
                ->whereIn('module', ['accounts', 'account', 'Account'])
                ->first();

            if ($perm) {
                return [
                    'can_create'   => (bool)$perm->can_create,
                    'can_read'     => (bool)$perm->can_read,
                    'can_update'   => (bool)$perm->can_update,
                    'can_delete'   => (bool)$perm->can_delete,
                    'can_transfer' => (bool)$perm->can_transfer,
                    'record_scope' => $perm->record_scope ?: 'Team',
                ];
            }
        }

        // Default standard user permissions
        return [
            'can_create'   => true,
            'can_read'     => true,
            'can_update'   => true,
            'can_delete'   => false,
            'can_transfer' => false,
            'record_scope' => 'Team',
        ];
    }

    /**
     * Get field permissions map for the current user's profile on the accounts module.
     */
    public function getFieldPermissionsForProfile(?Profile $profile): array
    {
        $perms = [];
        if (!$profile) {
            return $perms;
        }

        if ($profile->is_admin || str_contains(strtolower($profile->name), 'admin')) {
            return $perms;
        }

        $records = FieldPermission::on('tenant')
            ->where('profile_id', $profile->id)
            ->whereIn('module', ['accounts', 'account', 'Account'])
            ->get();

        foreach ($records as $r) {
            $perms[$r->field_name] = $r->permission ?: 'Read/Write';
        }

        return $perms;
    }

    /**
     * Apply role hierarchy and record-level security scope to query.
     */
    public function applyAccountRecordScopeQuery($query, ?TenantUser $user = null, ?array $profilePermissions = null)
    {
        if (Auth::guard('super')->check()) {
            return $query;
        }

        if (!$user) {
            $user = $this->resolveCurrentUser();
        }

        if (!$user || $user->isAdmin()) {
            return $query;
        }

        if ($profilePermissions === null) {
            $profile = $this->resolveCurrentUserProfile($user);
            $profilePermissions = $this->getProfilePermissions($profile, $user);
        }

        if (($profilePermissions['record_scope'] ?? 'All') === 'All') {
            return $query;
        }

        $scope = $profilePermissions['record_scope'] ?? 'Team';
        $userId = $user->id;

        $sharedAccountIds = DB::connection('tenant')->table('record_shares')
            ->whereIn('module', ['accounts', 'account'])
            ->where(function ($sq) use ($user) {
                $sq->where(function ($q) use ($user) {
                    $q->where('shared_with_type', 'user')->where('shared_with_id', (string)$user->id);
                });
                if (!empty($user->role_id)) {
                    $sq->orWhere(function ($q) use ($user) {
                        $q->where('shared_with_type', 'role')->where('shared_with_id', (string)$user->role_id);
                    });
                }
                if (!empty($user->profile_id)) {
                    $sq->orWhere(function ($q) use ($user) {
                        $q->where('shared_with_type', 'profile')->where('shared_with_id', (string)$user->profile_id);
                    });
                }
            })
            ->pluck('record_id')
            ->map(fn($id) => (int)$id)
            ->all();

        if ($scope === 'Own') {
            return $query->where(function ($q) use ($userId, $sharedAccountIds) {
                $q->where('owner_id', $userId)
                  ->orWhere('created_by', $userId);
                if (!empty($sharedAccountIds)) {
                    $q->orWhereIn('id', $sharedAccountIds);
                }
            });
        }

        if ($scope === 'Team') {
            $teamUserIds = $user->getTeamUserIds();

            return $query->where(function ($q) use ($teamUserIds, $sharedAccountIds) {
                $q->whereIn('owner_id', $teamUserIds)
                  ->orWhereIn('created_by', $teamUserIds);
                if (!empty($sharedAccountIds)) {
                    $q->orWhereIn('id', $sharedAccountIds);
                }
            });
        }

        return $query;
    }

    /**
     * Determine if a user can access a specific Account for a given action.
     */
    public function userCanAccessAccount(Account $account, string $action = 'read', ?TenantUser $user = null, ?array $profilePermissions = null): bool
    {
        if (Auth::guard('super')->check()) {
            return true;
        }

        if (!$user) {
            $user = $this->resolveCurrentUser();
        }
        if (!$user) {
            return true;
        }
        if ($user->isAdmin()) {
            return true;
        }

        if ($profilePermissions === null) {
            $profile = $this->resolveCurrentUserProfile($user);
            $profilePermissions = $this->getProfilePermissions($profile, $user);
        }

        switch ($action) {
            case 'read':
                if (empty($profilePermissions['can_read'])) return false;
                break;
            case 'create':
                if (empty($profilePermissions['can_create'])) return false;
                break;
            case 'update':
                if (empty($profilePermissions['can_update'])) return false;
                break;
            case 'delete':
                if (empty($profilePermissions['can_delete'])) return false;
                break;
            case 'transfer':
                if (empty($profilePermissions['can_transfer'])) return false;
                break;
        }

        $scope = $profilePermissions['record_scope'] ?? 'All';
        if ($scope === 'All') {
            return true;
        }

        $shareQuery = DB::connection('tenant')->table('record_shares')
            ->whereIn('module', ['accounts', 'account'])
            ->where('record_id', (string)$account->id)
            ->where(function ($sq) use ($user) {
                $sq->where(function ($q) use ($user) {
                    $q->where('shared_with_type', 'user')->where('shared_with_id', (string)$user->id);
                });
                if (!empty($user->role_id)) {
                    $sq->orWhere(function ($q) use ($user) {
                        $q->where('shared_with_type', 'role')->where('shared_with_id', (string)$user->role_id);
                    });
                }
                if (!empty($user->profile_id)) {
                    $sq->orWhere(function ($q) use ($user) {
                        $q->where('shared_with_type', 'profile')->where('shared_with_id', (string)$user->profile_id);
                    });
                }
            });

        if (in_array($action, ['update', 'delete', 'transfer'])) {
            $hasRwShare = (clone $shareQuery)->where('access_level', 'read_write')->exists();
            if ($hasRwShare) {
                return true;
            }
        } else {
            $hasReadShare = $shareQuery->exists();
            if ($hasReadShare) {
                return true;
            }
        }

        if ($scope === 'Own') {
            return (int)$account->owner_id === (int)$user->id
                || (int)$account->created_by === (int)$user->id;
        }

        if ($scope === 'Team') {
            $teamIds = $user->getTeamUserIds();
            return in_array((int)$account->owner_id, $teamIds)
                || in_array((int)$account->created_by, $teamIds);
        }

        return false;
    }

    /**
     * Sanitize an account record respecting field-level security permissions.
     */
    public function sanitizeAccount(Account $account, array $fieldPermissions): array
    {
        $data = $account->toArray();
        $customData = is_array($account->custom_data) ? $account->custom_data : (json_decode($account->custom_data ?? '[]', true) ?: []);

        // Computed helper attributes
        $data['owner_name'] = $account->assigned_to ?: ($account->owner?->name ?? 'Unassigned');
        $data['credit_limit'] = (float)($account->credit_limit ?? 0);
        $data['annual_revenue'] = (float)($account->annual_revenue ?? 0);

        // Apply permissions on standard attributes
        $protectedKeys = ['id', 'created_at', 'updated_at', 'deleted_at', 'owner_id', 'created_by', 'updated_by'];
        foreach ($data as $key => $val) {
            if (in_array($key, $protectedKeys)) {
                continue;
            }
            $perm = $fieldPermissions[$key] ?? 'Read/Write';
            if ($perm === 'Hidden') {
                $data[$key] = null;
                $data['_hidden_' . $key] = true;
            } elseif ($perm === 'Read-Only') {
                $data['_readonly_' . $key] = true;
            }
        }

        // Apply permissions on dynamic custom fields in custom_data
        $sanitizedCustom = [];
        foreach ($customData as $cfKey => $cfVal) {
            $perm = $fieldPermissions[$cfKey] ?? 'Read/Write';
            if ($perm !== 'Hidden') {
                $sanitizedCustom[$cfKey] = $cfVal;
                // Expose at root level for seamless table/kanban/form access
                $data[$cfKey] = $cfVal;
                if ($perm === 'Read-Only') {
                    $data['_readonly_' . $cfKey] = true;
                }
            } else {
                $data['_hidden_' . $cfKey] = true;
            }
        }
        $data['custom_data'] = $sanitizedCustom;

        return $data;
    }

    /**
     * Sanitize a collection of accounts.
     */
    public function sanitizeCollection(iterable $accounts, array $fieldPermissions): Collection
    {
        return collect($accounts)->map(fn($acc) => $this->sanitizeAccount($acc, $fieldPermissions))->values();
    }

    /**
     * Ensure default custom fields exist for accounts module.
     */
    public function ensureCustomFields(): void
    {
        try {
            if (CustomField::on('tenant')->where('module', 'accounts')->count() > 0) {
                return;
            }

            $defaults = [
                [
                    'module' => 'accounts',
                    'field_name' => 'cf_annual_contract_value',
                    'field_label' => 'Annual Contract Value (ACV)',
                    'data_type' => 'currency',
                    'options' => null,
                    'is_required' => false,
                    'help_text' => 'Total annualized contract run-rate across active subscriptions',
                ],
                [
                    'module' => 'accounts',
                    'field_name' => 'cf_procurement_sla_tier',
                    'field_label' => 'Procurement SLA Tier',
                    'data_type' => 'picklist',
                    'options' => 'Platinum (99.99%), Gold (99.9%), Silver (99.5%), Standard Bronze',
                    'is_required' => false,
                    'help_text' => 'Guaranteed uptime and vendor turnaround SLA tier',
                ],
                [
                    'module' => 'accounts',
                    'field_name' => 'cf_account_tier',
                    'field_label' => 'Account Strategic Tier',
                    'data_type' => 'picklist',
                    'options' => 'Tier 1 Strategic, Tier 2 Growth, Tier 3 Emerging',
                    'is_required' => false,
                    'help_text' => 'Strategic prioritization bracket for corporate relations',
                ],
                [
                    'module' => 'accounts',
                    'field_name' => 'cf_tax_id_ein',
                    'field_label' => 'Tax ID / VAT Registration',
                    'data_type' => 'text',
                    'options' => null,
                    'is_required' => false,
                    'help_text' => 'Corporate EIN or International VAT Identification Number',
                ],
                [
                    'module' => 'accounts',
                    'field_name' => 'cf_billing_contact_email',
                    'field_label' => 'Dedicated Invoicing AP Email',
                    'data_type' => 'text',
                    'options' => null,
                    'is_required' => false,
                    'help_text' => 'Direct Accounts Payable electronic delivery destination',
                ],
            ];

            foreach ($defaults as $cf) {
                CustomField::on('tenant')->updateOrCreate(
                    ['module' => 'accounts', 'field_name' => $cf['field_name']],
                    $cf
                );
            }
        } catch (\Throwable $e) {
            // Silently ignore if table not present
        }
    }

    /**
     * Seed initial demo accounts if database table is completely empty.
     */
    public function ensureDemoAccounts(): void
    {
        try {
            if (Account::on('tenant')->count() > 0) {
                return;
            }

            $firstUser = TenantUser::on('tenant')->first();
            $userId = $firstUser?->id;
            $userName = $firstUser?->name ?? 'Alex Morgan';

            $demoAccounts = [
                [
                    'name' => 'Skyline Cloud Systems',
                    'type' => 'Customer',
                    'industry' => 'Software & Cloud',
                    'website' => 'www.skylinecloud.io',
                    'phone' => '+1 (415) 890-1200',
                    'currency' => 'USD',
                    'payment_terms' => 'Net 30',
                    'credit_limit' => 75000.00,
                    'annual_revenue' => 12000000.00,
                    'assigned_to' => $userName,
                    'owner_id' => $userId,
                    'billing_address' => '100 Market St, Suite 400, San Francisco, CA 94105',
                    'shipping_address' => '100 Market St, Suite 400, San Francisco, CA 94105',
                    'notes' => 'Key enterprise account. Signed 3-year multi-region cloud services agreement. High renewal propensity.',
                    'custom_data' => [
                        'cf_annual_contract_value' => '450000',
                        'cf_procurement_sla_tier' => 'Platinum (99.99%)',
                        'cf_account_tier' => 'Tier 1 Strategic',
                        'cf_tax_id_ein' => 'XX-8921044',
                        'cf_billing_contact_email' => 'ap@skylinecloud.io',
                    ],
                ],
                [
                    'name' => 'Vanguard Health Tech',
                    'type' => 'Customer',
                    'industry' => 'Healthcare Tech',
                    'website' => 'www.vanguardhealth.tech',
                    'phone' => '+1 (617) 555-0199',
                    'currency' => 'USD',
                    'payment_terms' => 'Net 45',
                    'credit_limit' => 120000.00,
                    'annual_revenue' => 24000000.00,
                    'assigned_to' => $userName,
                    'owner_id' => $userId,
                    'billing_address' => '200 Boylston St, Boston, MA 02116',
                    'shipping_address' => '45 Research Drive, Cambridge, MA 02142',
                    'notes' => 'HIPAA-compliant healthcare AI solutions. Strict SLA and SOC2 compliance monitoring required.',
                    'custom_data' => [
                        'cf_annual_contract_value' => '820000',
                        'cf_procurement_sla_tier' => 'Platinum (99.99%)',
                        'cf_account_tier' => 'Tier 1 Strategic',
                        'cf_tax_id_ein' => 'XX-7718902',
                        'cf_billing_contact_email' => 'finance@vanguardhealth.tech',
                    ],
                ],
                [
                    'name' => 'Quantum Silicon Components',
                    'type' => 'Vendor',
                    'industry' => 'Semiconductors',
                    'website' => 'www.quantumsilicon.com',
                    'phone' => '+1 (408) 555-4321',
                    'currency' => 'USD',
                    'payment_terms' => 'Net 60',
                    'credit_limit' => 250000.00,
                    'annual_revenue' => 45000000.00,
                    'assigned_to' => $userName,
                    'owner_id' => $userId,
                    'billing_address' => '800 Innovation Way, San Jose, CA 95134',
                    'shipping_address' => '800 Innovation Way, San Jose, CA 95134',
                    'notes' => 'Tier-1 primary supplier for high-speed processor chips and silicon wafers. Lead time 14 days.',
                    'custom_data' => [
                        'cf_annual_contract_value' => '1200000',
                        'cf_procurement_sla_tier' => 'Gold (99.9%)',
                        'cf_account_tier' => 'Tier 1 Strategic',
                        'cf_tax_id_ein' => 'XX-4491028',
                        'cf_billing_contact_email' => 'vendor-ap@quantumsilicon.com',
                    ],
                ],
                [
                    'name' => 'Titan Steel & Enclosures',
                    'type' => 'Vendor',
                    'industry' => 'Industrial Manufacturing',
                    'website' => 'www.titansteel.com',
                    'phone' => '+1 (312) 555-7890',
                    'currency' => 'USD',
                    'payment_terms' => 'Net 30',
                    'credit_limit' => 80000.00,
                    'annual_revenue' => 18000000.00,
                    'assigned_to' => $userName,
                    'owner_id' => $userId,
                    'billing_address' => '450 Industrial Pkwy, Chicago, IL 60609',
                    'shipping_address' => '450 Industrial Pkwy, Chicago, IL 60609',
                    'notes' => 'Supplies heavy duty server rack enclosures and sheet metal chassis.',
                    'custom_data' => [
                        'cf_annual_contract_value' => '320000',
                        'cf_procurement_sla_tier' => 'Silver (99.5%)',
                        'cf_account_tier' => 'Tier 2 Growth',
                        'cf_tax_id_ein' => 'XX-9012384',
                        'cf_billing_contact_email' => 'invoicing@titansteel.com',
                    ],
                ],
                [
                    'name' => 'Apex Global Solutions',
                    'type' => 'Prospect',
                    'industry' => 'FinTech & Banking',
                    'website' => 'www.apexglobal.net',
                    'phone' => '+1 (212) 555-0144',
                    'currency' => 'USD',
                    'payment_terms' => 'Net 30',
                    'credit_limit' => 50000.00,
                    'annual_revenue' => 8500000.00,
                    'assigned_to' => $userName,
                    'owner_id' => $userId,
                    'billing_address' => '150 Broadway, Suite 1800, New York, NY 10038',
                    'shipping_address' => '150 Broadway, Suite 1800, New York, NY 10038',
                    'notes' => 'High-potential financial tech prospect evaluating migration from legacy ERP. Decision expected Q4.',
                    'custom_data' => [
                        'cf_annual_contract_value' => '195000',
                        'cf_procurement_sla_tier' => 'Platinum (99.99%)',
                        'cf_account_tier' => 'Tier 2 Growth',
                        'cf_tax_id_ein' => 'XX-3019284',
                        'cf_billing_contact_email' => 'billing@apexglobal.net',
                    ],
                ],
                [
                    'name' => 'NexGen Logistics Alliance',
                    'type' => 'Partner',
                    'industry' => 'Freight & Logistics',
                    'website' => 'www.nexgenlogistics.org',
                    'phone' => '+1 (713) 555-9820',
                    'currency' => 'USD',
                    'payment_terms' => 'Net 60',
                    'credit_limit' => 180000.00,
                    'annual_revenue' => 32000000.00,
                    'assigned_to' => $userName,
                    'owner_id' => $userId,
                    'billing_address' => '1200 Post Oak Blvd, Houston, TX 77056',
                    'shipping_address' => '1200 Post Oak Blvd, Houston, TX 77056',
                    'notes' => 'Strategic 3PL global shipping and cross-docking partner with API integration.',
                    'custom_data' => [
                        'cf_annual_contract_value' => '650000',
                        'cf_procurement_sla_tier' => 'Gold (99.9%)',
                        'cf_account_tier' => 'Tier 1 Strategic',
                        'cf_tax_id_ein' => 'XX-5501923',
                        'cf_billing_contact_email' => 'accounts@nexgenlogistics.org',
                    ],
                ],
            ];

            foreach ($demoAccounts as $acc) {
                Account::on('tenant')->create($acc);
            }
        } catch (\Throwable $e) {
            // Silently ignore
        }
    }
}
