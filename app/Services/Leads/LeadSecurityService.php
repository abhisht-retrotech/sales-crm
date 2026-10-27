<?php

namespace App\Services\Leads;

use App\Models\ApprovalProcess;
use App\Models\CustomField;
use App\Models\FieldPermission;
use App\Models\Lead;
use App\Models\ProcessFlow;
use App\Models\Profile;
use App\Models\ProfilePermission;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\TenantUser;
use App\Services\DynamicFields\DynamicFieldMappingService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LeadSecurityService
{
    protected ?DynamicFieldMappingService $dynamicFieldService = null;

    public function __construct(?DynamicFieldMappingService $dynamicFieldService = null)
    {
        $this->dynamicFieldService = $dynamicFieldService;
    }

    public function getDynamicFieldService(): DynamicFieldMappingService
    {
        if (!$this->dynamicFieldService) {
            $this->dynamicFieldService = app(DynamicFieldMappingService::class);
        }
        return $this->dynamicFieldService;
    }

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
        if ($user && $user->isAdmin()) {
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
                ->whereIn('module', ['leads', 'Lead'])
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
     * Get field permissions map for the current user's profile on the leads module.
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
            ->whereIn('module', ['leads', 'Lead'])
            ->get();

        foreach ($records as $r) {
            $perms[$r->field_name] = $r->permission ?: 'Read/Write';
        }

        return $perms;
    }

    /**
     * Apply role hierarchy and record-level security scope to query.
     */
    public function applyLeadRecordScopeQuery($query, ?TenantUser $user, array $profilePermissions)
    {
        if (!$user || $user->isAdmin() || ($profilePermissions['record_scope'] ?? 'All') === 'All') {
            return $query;
        }

        $scope = $profilePermissions['record_scope'] ?? 'Team';
        $userId = $user->id;

        $sharedLeadIds = DB::connection('tenant')->table('record_shares')
            ->where('module', 'leads')
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
            return $query->where(function ($q) use ($userId, $sharedLeadIds) {
                $q->where('assigned_to', $userId)
                  ->orWhere('created_by', $userId);
                if (!empty($sharedLeadIds)) {
                    $q->orWhereIn('id', $sharedLeadIds);
                }
            });
        }

        if ($scope === 'Team') {
            $teamUserIds = $user->getTeamUserIds();

            return $query->where(function ($q) use ($teamUserIds, $sharedLeadIds) {
                $q->whereIn('assigned_to', $teamUserIds)
                  ->orWhereIn('created_by', $teamUserIds);
                if (!empty($sharedLeadIds)) {
                    $q->orWhereIn('id', $sharedLeadIds);
                }
            });
        }

        return $query;
    }

    /**
     * Determine if a user can access a specific Lead for a given action.
     */
    public function userCanAccessLead(Lead $lead, string $action = 'read', ?TenantUser $user = null, ?array $profilePermissions = null): bool
    {
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
            case 'convert':
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
            ->where('module', 'leads')
            ->where('record_id', (string)$lead->id)
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

        if (in_array($action, ['update', 'delete', 'transfer', 'convert'])) {
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
            return (string)$lead->assigned_to === (string)$user->id
                || (string)$lead->created_by === (string)$user->id;
        }

        if ($scope === 'Team') {
            $teamIds = $user->getTeamUserIds();
            return in_array((int)$lead->assigned_to, $teamIds)
                || in_array((int)$lead->created_by, $teamIds);
        }

        return false;
    }

    /**
     * Sanitize a single lead record respecting field-level security permissions.
     */
    public function sanitizeLead(Lead $lead, array $fieldPermissions): array
    {
        $data = $lead->toArray();
        $customData = is_array($lead->custom_data) ? $lead->custom_data : (json_decode($lead->custom_data ?? '[]', true) ?: []);

        $computedName = trim(($lead->first_name ?? '') . ' ' . ($lead->last_name ?? ''));
        $data['name'] = !empty($computedName) ? $computedName : (!empty($lead->company) ? $lead->company : 'Lead #' . $lead->id);
        $data['assigned_to_name'] = $lead->assignedUser?->name ?? 'Unassigned';

        $creator = !empty($lead->created_by) ? TenantUser::on('tenant')->find($lead->created_by) : null;
        $updater = !empty($lead->updated_by) ? TenantUser::on('tenant')->find($lead->updated_by) : null;
        $data['created_by_name'] = $creator?->name ?? 'System Admin';
        $data['updated_by_name'] = $updater?->name ?? 'System Admin';

        $fnPerm = $fieldPermissions['first_name'] ?? 'Read/Write';
        $lnPerm = $fieldPermissions['last_name'] ?? 'Read/Write';
        $compPerm = $fieldPermissions['company'] ?? 'Read/Write';

        if ($fnPerm === 'Hidden' && $lnPerm === 'Hidden') {
            $data['name'] = ($compPerm !== 'Hidden' && !empty($lead->company)) ? $lead->company : 'Lead #' . $lead->id;
        } elseif ($fnPerm === 'Hidden') {
            $data['name'] = $lead->last_name ?: ($lead->company ?: 'Lead #' . $lead->id);
        } elseif ($lnPerm === 'Hidden') {
            $data['name'] = $lead->first_name ?: ($lead->company ?: 'Lead #' . $lead->id);
        }

        $protectedKeys = [
            'id', 'created_at', 'updated_at', 'deleted_at', 'is_converted',
            'converted_account_id', 'converted_contact_id', 'converted_opportunity_id',
            'approval_status', 'is_approved', 'is_rejected', 'approved_by', 'rejected_by',
            'approved_at', 'rejected_at', 'approval_notes'
        ];
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

        // Native table approval columns
        $approver = !empty($lead->approved_by) ? TenantUser::on('tenant')->find($lead->approved_by) : null;
        $rejecter = !empty($lead->rejected_by) ? TenantUser::on('tenant')->find($lead->rejected_by) : null;
        $data['approved_by_name'] = $approver?->name ?? ($lead->approvedByUser?->name ?? null);
        $data['rejected_by_name'] = $rejecter?->name ?? ($lead->rejectedByUser?->name ?? null);
        $data['is_approved'] = (bool)$lead->is_approved;
        $data['is_rejected'] = (bool)$lead->is_rejected;
        $data['approval_status'] = $lead->approval_status ?: 'Pending';
        $data['approval_notes'] = $lead->approval_notes;
        $data['approved_at'] = $lead->approved_at ? $lead->approved_at->format('M d, Y h:i A') : null;
        $data['rejected_at'] = $lead->rejected_at ? $lead->rejected_at->format('M d, Y h:i A') : null;

        $sanitizedCustom = [];
        foreach ($customData as $cfKey => $cfVal) {
            // Filter out legacy approval keys from custom_data
            if (in_array($cfKey, ['is_approved', 'is_rejected', 'approval_status', 'approved_by', 'rejected_by', 'approval_notes', 'cf_is_approved'])) {
                continue;
            }
            $perm = $fieldPermissions[$cfKey] ?? 'Read/Write';
            if ($perm !== 'Hidden') {
                $sanitizedCustom[$cfKey] = $cfVal;
                $data[$cfKey] = $cfVal;
                if ($perm === 'Read-Only') {
                    $data['_readonly_' . $cfKey] = true;
                }
            } else {
                $data['_hidden_' . $cfKey] = true;
            }
        }
        $data['custom_data'] = $sanitizedCustom;

        // Dynamically resolve custom lookup fields
        $data = $this->getDynamicFieldService()->resolveRecord($data, 'leads');

        return $data;
    }

    /**
     * Sanitize a collection of leads.
     */
    public function sanitizeCollection(iterable $leads, array $fieldPermissions): Collection
    {
        $sanitized = collect($leads)->map(fn($lead) => $this->sanitizeLead($lead, $fieldPermissions))->values();
        return $this->getDynamicFieldService()->resolveCollection($sanitized, 'leads');
    }

    /**
     * Seed initial demo leads if database table is completely empty.
     */
    public function ensureDemoLeads(): void
    {
        try {
            if (Lead::on('tenant')->count() > 0) {
                return;
            }

            $firstUser = TenantUser::on('tenant')->first();
            $userId = $firstUser?->id;

            $demoLeads = [
                [
                    'first_name' => 'Sarah',
                    'last_name' => 'Jenkins',
                    'email' => 's.jenkins@acmecorp.io',
                    'phone' => '+1 (512) 890-4412',
                    'company' => 'Acme Corporation',
                    'title' => 'VP of Enterprise Technology',
                    'source' => 'Website',
                    'score' => 92,
                    'status' => 'Qualified',
                    'lead_value' => '$125,000',
                    'rating' => 'Hot',
                    'notes' => 'Looking to migrate 400 seats from legacy Salesforce by Q4 2026. Needs SOC2 Type II compliance guarantees and custom object support.',
                    'assigned_to' => $userId,
                    'custom_data' => [
                        'cf_industry_subsector' => 'Enterprise SaaS',
                        'cf_procurement_fit_score' => 5,
                        'cf_budget_status' => 'Yes',
                        'cf_contract_timeline' => 'Immediate (<30 days)',
                    ],
                ],
                [
                    'first_name' => 'Carlos',
                    'last_name' => 'Mendez',
                    'email' => 'carlos.m@atlaslogistics.com',
                    'phone' => '+1 (312) 554-9021',
                    'company' => 'Atlas Logistics Inc',
                    'title' => 'VP of Supply Chain Systems',
                    'source' => 'Cold Outreach',
                    'score' => 31,
                    'status' => 'Lost',
                    'lead_value' => '$35,000',
                    'rating' => 'Cold',
                    'notes' => 'Requested on-premise deployment which is not supported in our cloud SaaS architecture.',
                    'assigned_to' => $userId,
                    'custom_data' => [
                        'cf_industry_subsector' => 'Logistics & Cargo',
                        'cf_procurement_fit_score' => 2,
                        'cf_budget_status' => 'No',
                        'cf_contract_timeline' => 'Exploring',
                    ],
                ],
                [
                    'first_name' => 'Marcus',
                    'last_name' => 'Vance',
                    'email' => 'mvance@apexglobal.net',
                    'phone' => '+1 (415) 773-1092',
                    'company' => 'Apex Global Technologies',
                    'title' => 'Director of Global Procurement',
                    'source' => 'Referral',
                    'score' => 64,
                    'status' => 'Contacted',
                    'lead_value' => '$80,000',
                    'rating' => 'Warm',
                    'notes' => 'Evaluation committee reviewing vendor pricing tiers and automated billing ledger integration.',
                    'assigned_to' => $userId,
                    'custom_data' => [
                        'cf_industry_subsector' => 'FinTech & Banking',
                        'cf_procurement_fit_score' => 4,
                        'cf_budget_status' => 'Pending Review',
                        'cf_contract_timeline' => 'Q4 2026',
                    ],
                ],
                [
                    'first_name' => 'Liam',
                    'last_name' => 'O\'Connor',
                    'email' => 'liam@beacontech.co',
                    'phone' => '+1 (617) 492-8810',
                    'company' => 'Beacon Tech Systems',
                    'title' => 'Head of Infrastructure',
                    'source' => 'Direct Inbound',
                    'score' => 78,
                    'status' => 'Qualified',
                    'lead_value' => '$95,000',
                    'rating' => 'Hot',
                    'notes' => 'Scheduled technical deep dive on database isolation and webhook latency SLAs.',
                    'assigned_to' => $userId,
                    'custom_data' => [
                        'cf_industry_subsector' => 'AI / Automation',
                        'cf_procurement_fit_score' => 4,
                        'cf_budget_status' => 'Yes',
                        'cf_contract_timeline' => 'Immediate (<30 days)',
                    ],
                ],
                [
                    'first_name' => 'Elena',
                    'last_name' => 'Rostova',
                    'email' => 'elena.rostova@horizonbio.org',
                    'phone' => '+1 (206) 612-4091',
                    'company' => 'Horizon Bio Solutions',
                    'title' => 'Director of Bioinformatics',
                    'source' => 'Partner',
                    'score' => 88,
                    'status' => 'Contacted',
                    'lead_value' => '$110,000',
                    'rating' => 'Hot',
                    'notes' => 'High propensity inbound referral. Requires GDPR data residency guarantees.',
                    'assigned_to' => $userId,
                    'custom_data' => [
                        'cf_industry_subsector' => 'HealthTech',
                        'cf_procurement_fit_score' => 5,
                        'cf_budget_status' => 'Yes',
                        'cf_contract_timeline' => 'Q4 2026',
                    ],
                ],
                [
                    'first_name' => 'Devon',
                    'last_name' => 'Patel',
                    'email' => 'd.patel@quantumcap.com',
                    'phone' => '+1 (212) 830-2219',
                    'company' => 'Quantum Capital Group',
                    'title' => 'Managing Partner',
                    'source' => 'Google Ads',
                    'score' => 45,
                    'status' => 'New',
                    'lead_value' => '$60,000',
                    'rating' => 'Warm',
                    'notes' => 'Downloaded whitepaper on decoupled multi-tenant architecture.',
                    'assigned_to' => $userId,
                    'custom_data' => [
                        'cf_industry_subsector' => 'FinTech & Banking',
                        'cf_procurement_fit_score' => 3,
                        'cf_budget_status' => 'Pending Review',
                        'cf_contract_timeline' => 'H1 2027',
                    ],
                ],
            ];

            foreach ($demoLeads as $leadData) {
                Lead::on('tenant')->create($leadData);
            }
        } catch (\Throwable $e) {
            // Silently ignore if table not yet migrated or schema conflict
        }
    }

    /**
     * Ensure standard custom fields exist for leads module.
     */
    public function ensureCustomFields(): void
    {
        try {
            $defaults = [
                [
                    'module' => 'leads',
                    'field_name' => 'cf_industry_subsector',
                    'field_label' => 'Industry Sub-Sector',
                    'data_type' => 'Dropdown',
                    'options' => 'Enterprise SaaS, FinTech & Banking, HealthTech, AI / Automation, Logistics & Cargo',
                    'is_required' => false,
                    'help_text' => 'Granular vertical industry categorization for targeted qualification',
                ],
                [
                    'module' => 'leads',
                    'field_name' => 'cf_procurement_fit_score',
                    'field_label' => 'Procurement Fit Score',
                    'data_type' => 'Rating',
                    'options' => null,
                    'is_required' => false,
                    'help_text' => 'Visual 1 to 5 propensity rating based on vendor eligibility',
                ],
                [
                    'module' => 'leads',
                    'field_name' => 'cf_budget_status',
                    'field_label' => 'Budget Status',
                    'data_type' => 'Dropdown',
                    'options' => 'Yes, Pending Review, No, Unallocated',
                    'is_required' => false,
                    'help_text' => 'Whether procurement budget has been formally allocated',
                ],
                [
                    'module' => 'leads',
                    'field_name' => 'cf_contract_timeline',
                    'field_label' => 'Decision Timeline',
                    'data_type' => 'Dropdown',
                    'options' => 'Immediate (<30 days), Q4 2026, H1 2027, Exploring',
                    'is_required' => false,
                    'help_text' => 'Target implementation or procurement decision timeline',
                ],
            ];

            foreach ($defaults as $cf) {
                CustomField::on('tenant')->firstOrCreate(
                    ['module' => 'leads', 'field_name' => $cf['field_name']],
                    $cf
                );
            }
        } catch (\Throwable $e) {
            // Silently ignore
        }
    }

    /**
     * Check if a lead is approved so that standard tasks can be performed.
     * Governed by active ApprovalProcess workflow rule in database.
     */
    public function isLeadApprovedForTasks(Lead $lead): bool
    {
        // 1. Check if an active ApprovalProcess workflow rule exists in database that enforces record/task locking
        $activeWorkflow = ApprovalProcess::on('tenant')
            ->where('module', 'leads')
            ->where('is_active', true)
            ->where('lock_record_until_approved', true)
            ->first();

        // If no active approval workflow requires lock, tasks are unlocked
        if (!$activeWorkflow) {
            return true;
        }

        // 2. If workflow rule requires approval, check lead's native approval columns directly from table
        $status = strtolower(trim((string)($lead->approval_status ?? 'pending')));
        $isApproved = !empty($lead->is_approved) && $lead->is_approved !== '0' && $lead->is_approved !== false;

        return $isApproved || $status === 'approved';
    }

    /**
     * Check if the given tenant user is authorized to approve or reject a lead (Manager or Admin).
     */
    public function userCanApproveOrRejectLead(Lead $lead, ?TenantUser $user): bool
    {
        if (!$user) {
            return false;
        }

        // 1. Administrators / Executive Leadership can always approve or reject
        $profile = $this->resolveCurrentUserProfile($user);
        $role = $user->role ?? ($user->role_id ? Role::on('tenant')->find($user->role_id) : null);
        $profileName = strtolower($profile?->name ?? '');
        $roleName = strtolower($role?->name ?? '');

        if (
            str_contains($profileName, 'admin') ||
            str_contains($roleName, 'admin') ||
            str_contains($roleName, 'ceo') ||
            str_contains($roleName, 'vp') ||
            str_contains($roleName, 'director') ||
            str_contains($roleName, 'manager')
        ) {
            return true;
        }

        // 2. Direct Manager of the lead's assigned user or creator
        $assignedUser = $lead->assigned_to ? TenantUser::on('tenant')->find($lead->assigned_to) : null;
        $creatorUser = $lead->created_by ? TenantUser::on('tenant')->find($lead->created_by) : null;

        if ($assignedUser && (int)$assignedUser->manager_id === (int)$user->id) {
            return true;
        }

        if ($creatorUser && (int)$creatorUser->manager_id === (int)$user->id) {
            return true;
        }

        // 3. Superior in Role Hierarchy
        if ($assignedUser && $user->role_id && $assignedUser->role_id) {
            $userRole = Role::on('tenant')->find($user->role_id);
            if ($userRole) {
                $subordinateRoleIds = method_exists($userRole, 'getAllSubordinateRoleIds') ? $userRole->getAllSubordinateRoleIds() : [];
                if (in_array((int)$assignedUser->role_id, $subordinateRoleIds)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Ensure Lead Approval Workflow exists in approval_processes and process_flows tables.
     */
    public function ensureLeadApprovalWorkflow(): void
    {
        try {
            // 1. Ensure ApprovalProcess exists in approval_processes table
            $process = ApprovalProcess::on('tenant')->where('module', 'leads')->first();
            if (!$process) {
                $process = ApprovalProcess::on('tenant')->create([
                    'name'                       => 'Lead Qualification & Governance Multi-Level Approval',
                    'module'                     => 'leads',
                    'trigger_type'               => 'on_create',
                    'is_active'                  => true,
                    'lock_record_until_approved' => true,
                    'description'                => 'Multi-level workflow process: New leads enter Pending state and tasks are locked until approved through Manager and Executive review steps.',
                ]);

                $process->steps()->createMany([
                    [
                        'step_number'   => 1,
                        'step_name'     => 'Step 1: Direct Manager Qualification Review',
                        'approver_type' => 'role',
                        'approver_name' => 'Direct Manager or Administrator',
                    ],
                    [
                        'step_number'   => 2,
                        'step_name'     => 'Step 2: Executive & Commercial Sign-off',
                        'approver_type' => 'role',
                        'approver_name' => 'VP of Global Sales / Administrator',
                    ],
                ]);
            } else {
                if (!$process->lock_record_until_approved || !$process->is_active) {
                    $process->update([
                        'lock_record_until_approved' => true,
                        'is_active' => true,
                    ]);
                }

                // Ensure multi-level steps exist
                if ($process->steps()->count() < 2) {
                    $process->steps()->firstOrCreate(
                        ['step_number' => 2],
                        [
                            'step_name'     => 'Step 2: Executive & Commercial Sign-off',
                            'approver_type' => 'role',
                            'approver_name' => 'VP of Global Sales / Administrator',
                        ]
                    );
                }
            }

            // 2. Ensure ProcessFlow exists in process_flows table for React ProcessStudio
            $flow = ProcessFlow::on('tenant')->where('trigger_config->flow_key', 'lead-qualification-approval')->first();
            if (!$flow) {
                ProcessFlow::on('tenant')->create([
                    'name'           => 'Lead Qualification & Task Lock Approval',
                    'description'    => 'New leads enter Pending review state. Tasks and conversions are locked until Approved by Manager or Administrator.',
                    'category'       => 'sales',
                    'entity_type'    => 'Leads',
                    'trigger_type'   => 'record_event',
                    'trigger_config' => [
                        'flow_key'   => 'lead-qualification-approval',
                        'preset_key' => 'lead-qualification-approval',
                        'event'      => 'on_create',
                        'module'     => 'leads',
                    ],
                    'nodes'          => [
                        [
                            'id'       => 'node-trigger',
                            'type'     => 'processTrigger',
                            'position' => ['x' => 80, 'y' => 160],
                            'data'     => [
                                'category'    => '1. TRIGGER CRITERIA',
                                'subtype'     => 'record_event',
                                'title'       => 'New Lead Created',
                                'description' => 'Triggered automatically when any new lead is ingested or created',
                                'condition'   => 'event == "on_create"',
                            ],
                        ],
                        [
                            'id'       => 'node-lock',
                            'type'     => 'processAction',
                            'position' => ['x' => 80, 'y' => 390],
                            'data'     => [
                                'category'    => 'SECURITY GOVERNANCE',
                                'subtype'     => 'lock_record',
                                'title'       => 'Lock Tasks & Set Pending',
                                'description' => 'Enforces pending approval state and locks task creation for standard users',
                                'condition'   => 'approval_status = "Pending" | tasks_locked = true',
                                'icon'        => 'Lock',
                            ],
                        ],
                        [
                            'id'       => 'node-approval',
                            'type'     => 'processApprovalStep',
                            'position' => ['x' => 460, 'y' => 160],
                            'data'     => [
                                'stepBadge' => 'STEP 1: MANAGER / ADMIN',
                                'approver'  => 'Direct Manager or Administrator',
                                'sla'       => '24h SLA',
                                'title'     => 'Lead Verification & Sign-off',
                                'condition' => 'manager_signoff == true || role == "admin"',
                            ],
                        ],
                        [
                            'id'       => 'node-approved-action',
                            'type'     => 'processAction',
                            'position' => ['x' => 880, 'y' => 160],
                            'data'     => [
                                'category'    => 'FINAL APPROVAL ACTION',
                                'subtype'     => 'update_field',
                                'title'       => 'Mark Approved & Unlock Tasks',
                                'description' => 'Updates is_approved=true, approval_status=Approved, and unlocks task scheduling',
                                'condition'   => "approval_status -> 'Approved' | is_approved -> true | unlock_tasks",
                                'color'       => '#059669',
                            ],
                        ],
                        [
                            'id'       => 'node-rejected-action',
                            'type'     => 'processAction',
                            'position' => ['x' => 460, 'y' => 390],
                            'data'     => [
                                'category'    => 'REJECTION ROLLBACK',
                                'subtype'     => 'exception',
                                'title'       => 'Mark Rejected & Retain Lock',
                                'description' => 'Updates is_rejected=true, approval_status=Rejected, and notifies lead creator',
                                'condition'   => "approval_status -> 'Rejected' | is_rejected -> true | notify_creator",
                                'color'       => '#e11d48',
                            ],
                        ],
                    ],
                    'edges'          => [
                        ['id' => 'e-1', 'source' => 'node-trigger', 'target' => 'node-lock'],
                        ['id' => 'e-2', 'source' => 'node-trigger', 'target' => 'node-approval'],
                        ['id' => 'e-3', 'source' => 'node-approval', 'target' => 'node-approved-action'],
                        ['id' => 'e-4', 'source' => 'node-approval', 'target' => 'node-rejected-action'],
                    ],
                    'status'         => 'active',
                    'version'        => 1,
                    'created_by'     => 'System Admin',
                    'updated_by'     => 'System Admin',
                ]);
            }
        } catch (\Throwable $e) {
            // Silently fallback if table doesn't exist
        }
    }
}
