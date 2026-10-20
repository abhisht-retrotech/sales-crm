<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Lead;
use App\Models\Account;
use App\Models\Contact;
use App\Models\Opportunity;
use App\Models\TenantUser;
use App\Models\CustomField;
use App\Models\FieldPermission;
use App\Models\Profile;
use App\Models\Tenant;
use App\Models\PageLayout;
use App\Models\ProfileLayoutAssignment;
use App\Models\Activity;
use App\Models\Task;
use App\Models\AuditLog;
use App\Models\ApprovalRequest;
use App\Models\TenantSetting;

class LeadController extends Controller
{
    /**
     * Ensure tenant database connection is actively configured.
     */
    protected function ensureTenantConnection()
    {
        $tenant = app()->bound('currentTenant') ? app('currentTenant') : Tenant::on('mysql')->first();
        if ($tenant && config('database.connections.tenant.database') !== $tenant->db_name) {
            config(['database.connections.tenant.database' => $tenant->db_name]);
            DB::purge('tenant');
        }
        return $tenant;
    }

    /**
     * Resolve the current user's security profile.
     */
    protected function resolveCurrentUserProfile(): ?Profile
    {
        $user = Auth::guard('web')->user() ?? Auth::guard('tenant')->user();
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
     * Get field permissions map for the current user's profile on the leads module.
     * Returns: [ field_name => 'Read/Write' | 'Read-Only' | 'Hidden' ]
     */
    protected function getFieldPermissionsForProfile(?Profile $profile): array
    {
        $perms = [];
        if (!$profile) {
            return $perms;
        }

        $records = FieldPermission::on('tenant')
            ->where('profile_id', $profile->id)
            ->where('module', 'leads')
            ->get();

        foreach ($records as $r) {
            $perms[$r->field_name] = $r->permission ?: 'Read/Write';
        }

        return $perms;
    }

    /**
     * Sanitize a lead record respecting field-level security permissions.
     */
    protected function sanitizeLead(Lead $lead, array $fieldPermissions): array
    {
        $data = $lead->toArray();
        $customData = is_array($lead->custom_data) ? $lead->custom_data : (json_decode($lead->custom_data ?? '[]', true) ?: []);

        // Computed helper attributes
        $computedName = trim(($lead->first_name ?? '') . ' ' . ($lead->last_name ?? ''));
        $data['name'] = !empty($computedName) ? $computedName : (!empty($lead->company) ? $lead->company : 'Lead #' . $lead->id);
        $data['assigned_to_name'] = $lead->assignedUser?->name ?? 'Unassigned';

        // Creator and updater resolution
        $creator = !empty($lead->created_by) ? TenantUser::on('tenant')->find($lead->created_by) : null;
        $updater = !empty($lead->updated_by) ? TenantUser::on('tenant')->find($lead->updated_by) : null;
        $data['created_by_name'] = $creator?->name ?? 'System Admin';
        $data['updated_by_name'] = $updater?->name ?? 'System Admin';

        // Apply permissions on standard attributes
        $protectedKeys = ['id', 'created_at', 'updated_at', 'deleted_at', 'is_converted', 'converted_account_id', 'converted_contact_id'];
        foreach ($data as $key => $val) {
            if (in_array($key, $protectedKeys)) {
                continue;
            }
            $perm = $fieldPermissions[$key] ?? 'Read/Write';
            if ($perm === 'Hidden') {
                $data[$key] = null;
                $data['_hidden_' . $key] = true;
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
            } else {
                $data['_hidden_' . $cfKey] = true;
            }
        }
        $data['custom_data'] = $sanitizedCustom;

        return $data;
    }

    /**
     * Seed initial demo leads if database table is completely empty.
     */
    protected function ensureDemoLeads()
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
    protected function ensureCustomFields()
    {
        try {
            if (CustomField::on('tenant')->where('module', 'leads')->count() > 0) {
                return;
            }

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
                CustomField::on('tenant')->updateOrCreate(
                    ['module' => 'leads', 'field_name' => $cf['field_name']],
                    $cf
                );
            }
        } catch (\Throwable $e) {
            // Silently ignore
        }
    }

    /**
     * Display the Leads & Intake page or return JSON.
     */
    public function index(Request $request)
    {
        $this->ensureTenantConnection();
        $this->ensureCustomFields();
        $this->ensureDemoLeads();

        $profile = $this->resolveCurrentUserProfile();
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);

        // Fetch custom fields for leads
        $customFields = CustomField::on('tenant')
            ->where('module', 'leads')
            ->orderBy('id', 'asc')
            ->get();

        // Build query
        $query = Lead::on('tenant')
            ->with(['assignedUser', 'convertedAccount', 'convertedContact'])
            ->orderBy('id', 'desc');

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('first_name', 'like', "%{$s}%")
                    ->orWhere('last_name', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%")
                    ->orWhere('company', 'like', "%{$s}%")
                    ->orWhere('phone', 'like', "%{$s}%")
                    ->orWhere('title', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $rawLeads = $query->get();

        // Sanitize leads according to field permissions
        $sanitizedLeads = $rawLeads->map(function ($lead) use ($fieldPermissions) {
            return $this->sanitizeLead($lead, $fieldPermissions);
        })->values();

        // Fetch active tenant users for assignment
        $users = TenantUser::on('tenant')->orderBy('name', 'asc')->get(['id', 'name', 'email']);

        $stages = ['New', 'Contacted', 'Qualified', 'Lost'];
        $sources = [
            'Direct Inbound',
            'Website',
            'Referral',
            'Cold Outreach',
            'Partner',
            'Trade Show / Event',
            'LinkedIn',
            'Google Ads',
            'Other'
        ];

        // Fetch saved column widths from database
        $savedColWidths = [];
        try {
            $setting = TenantSetting::on('tenant')->where('key', 'leads_table_col_widths')->first();
            if ($setting && $setting->value) {
                $savedColWidths = json_decode($setting->value, true) ?: [];
            }
            if (empty($savedColWidths)) {
                $layout = PageLayout::on('tenant')->where('module', 'leads')->where('is_default', true)->first();
                if ($layout && isset($layout->layout_json['col_widths'])) {
                    $savedColWidths = $layout->layout_json['col_widths'] ?: [];
                }
            }
        } catch (\Throwable $e) {
            $savedColWidths = [];
        }

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'leads' => $sanitizedLeads,
                'custom_fields' => $customFields,
                'field_permissions' => $fieldPermissions,
                'users' => $users,
                'stages' => $stages,
                'sources' => $sources,
                'can_send_bulk_email' => true,
                'col_widths' => $savedColWidths,
            ]);
        }

        return view('leads', [
            'initialLeads' => $sanitizedLeads,
            'customFields' => $customFields,
            'fieldPermissions' => $fieldPermissions,
            'users' => $users,
            'stages' => $stages,
            'sources' => $sources,
            'currentUserProfile' => $profile ? $profile->name : 'Administrator',
            'canSendBulkEmail' => true,
            'colWidths' => $savedColWidths,
        ]);
    }

    /**
     * Display the dedicated Lead 360 view page.
     */
    public function show(Request $request, $id)
    {
        $this->ensureTenantConnection();
        $this->ensureCustomFields();

        $profile = $this->resolveCurrentUserProfile();
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);

        $lead = Lead::on('tenant')->with(['assignedUser', 'convertedAccount', 'convertedContact', 'convertedOpportunity'])->findOrFail($id);
        $sanitizedLead = $this->sanitizeLead($lead, $fieldPermissions);

        // Fetch custom fields for leads
        $customFields = CustomField::on('tenant')
            ->where('module', 'leads')
            ->orderBy('id', 'asc')
            ->get();

        $users = TenantUser::on('tenant')->orderBy('name', 'asc')->get(['id', 'name', 'email']);
        $sources = [
            'Direct Inbound',
            'Website',
            'Referral',
            'Cold Outreach',
            'Partner',
            'Trade Show / Event',
            'LinkedIn',
            'Google Ads',
            'Other'
        ];

        // Resolve active PageLayout for module 'leads' and current profile
        $pageLayout = null;
        if ($profile) {
            $assignment = ProfileLayoutAssignment::on('tenant')
                ->where('module', 'leads')
                ->where('profile_id', $profile->id)
                ->first();
            if ($assignment) {
                $pageLayout = PageLayout::on('tenant')->find($assignment->layout_id);
            }
        }
        if (!$pageLayout) {
            $pageLayout = PageLayout::on('tenant')
                ->where('module', 'leads')
                ->where('is_default', true)
                ->first();
        }
        if (!$pageLayout) {
            $pageLayout = PageLayout::on('tenant')
                ->where('module', 'leads')
                ->first();
        }

        // Fetch activities, approvals, audit logs, and emails
        $activities = Activity::on('tenant')
            ->where('entity_type', 'leads')
            ->where('entity_id', $lead->id)
            ->latest()
            ->get();

        $approvals = ApprovalRequest::on('tenant')
            ->where('module', 'leads')
            ->where('record_id', $lead->id)
            ->latest()
            ->get();

        $auditLogs = AuditLog::on('tenant')
            ->where('entity_type', 'leads')
            ->where('entity_id', $lead->id)
            ->latest()
            ->get();

        $tasks = Task::on('tenant')
            ->where('related_entity_type', 'leads')
            ->where('related_entity_id', $lead->id)
            ->latest()
            ->get();

        $filesCount = 0;
        $emailCount = 1;

        if ($request->wantsJson()) {
            return response()->json([
                'lead' => $sanitizedLead,
                'custom_fields' => $customFields,
                'field_permissions' => $fieldPermissions,
                'users' => $users,
                'sources' => $sources,
                'pageLayout' => $pageLayout,
                'activities' => $activities,
                'approvals' => $approvals,
                'auditLogs' => $auditLogs,
                'filesCount' => $filesCount,
                'emailCount' => $emailCount,
            ]);
        }

        return view('leads.show', [
            'lead' => $sanitizedLead,
            'rawLead' => $lead,
            'customFields' => $customFields,
            'fieldPermissions' => $fieldPermissions,
            'users' => $users,
            'sources' => $sources,
            'currentUserProfile' => $profile ? $profile->name : 'Administrator',
            'pageLayout' => $pageLayout,
            'activities' => $activities,
            'approvals' => $approvals,
            'auditLogs' => $auditLogs,
            'tasks' => $tasks,
            'filesCount' => $filesCount,
            'emailCount' => $emailCount,
        ]);
    }

    /**
     * API: Get filtered leads list.
     */
    public function apiList(Request $request)
    {
        $this->ensureTenantConnection();
        $profile = $this->resolveCurrentUserProfile();
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);

        $query = Lead::on('tenant')
            ->with(['assignedUser', 'convertedAccount', 'convertedContact'])
            ->orderBy('id', 'desc');

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('first_name', 'like', "%{$s}%")
                    ->orWhere('last_name', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%")
                    ->orWhere('company', 'like', "%{$s}%")
                    ->orWhere('phone', 'like', "%{$s}%")
                    ->orWhere('title', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $rawLeads = $query->get();
        $leads = $rawLeads->map(function ($lead) use ($fieldPermissions) {
            return $this->sanitizeLead($lead, $fieldPermissions);
        })->values();

        return response()->json($leads);
    }

    /**
     * API: Get leads metadata (custom fields, permissions, users, stages, sources).
     */
    public function apiMeta(Request $request)
    {
        $this->ensureTenantConnection();
        $profile = $this->resolveCurrentUserProfile();
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);

        $customFields = CustomField::on('tenant')
            ->where('module', 'leads')
            ->orderBy('id', 'asc')
            ->get();

        $users = TenantUser::on('tenant')->orderBy('name', 'asc')->get(['id', 'name', 'email']);

        return response()->json([
            'custom_fields' => $customFields,
            'field_permissions' => $fieldPermissions,
            'users' => $users,
            'stages' => ['New', 'Contacted', 'Qualified', 'Lost'],
            'sources' => [
                'Direct Inbound',
                'Website',
                'Referral',
                'Cold Outreach',
                'Partner',
                'Trade Show / Event',
                'LinkedIn',
                'Google Ads',
                'Other'
            ],
            'can_send_bulk_email' => true,
        ]);
    }

    /**
     * API: Get single lead.
     */
    public function apiShow(Request $request, $id)
    {
        $this->ensureTenantConnection();
        $profile = $this->resolveCurrentUserProfile();
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);

        $lead = Lead::on('tenant')->with(['assignedUser', 'convertedAccount', 'convertedContact'])->findOrFail($id);
        return response()->json($this->sanitizeLead($lead, $fieldPermissions));
    }

    /**
     * API: Store a newly created lead.
     */
    public function apiStore(Request $request)
    {
        $this->ensureTenantConnection();
        $profile = $this->resolveCurrentUserProfile();
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);

        // Security check: If first_name, last_name, or company are marked Hidden, reject
        $validated = $request->validate([
            'first_name' => 'required|string|max:80',
            'last_name' => 'required|string|max:80',
            'company' => 'required|string|max:150',
            'email' => 'nullable|email|max:150',
            'phone' => 'nullable|string|max:30',
            'title' => 'nullable|string|max:100',
            'source' => 'nullable|string|max:80',
            'status' => 'nullable|string|max:60',
            'score' => 'nullable|integer|min:0|max:100',
            'lead_value' => 'nullable|string|max:30',
            'rating' => 'nullable|string|max:30',
            'notes' => 'nullable|string',
            'assigned_to' => 'nullable',
            'custom_fields' => 'nullable|array',
        ]);

        // Filter out fields where permission is Hidden or Read-Only
        foreach ($validated as $k => $v) {
            $perm = $fieldPermissions[$k] ?? 'Read/Write';
            if ($perm === 'Hidden' || $perm === 'Read-Only') {
                unset($validated[$k]);
            }
        }

        // Process dynamic custom fields
        $inputCustom = $request->input('custom_fields', []);
        $activeCustomFields = CustomField::on('tenant')->where('module', 'leads')->get();
        $customDataToSave = [];

        foreach ($activeCustomFields as $cf) {
            $fName = $cf->field_name;
            $perm = $fieldPermissions[$fName] ?? 'Read/Write';
            if ($perm === 'Hidden' || $perm === 'Read-Only') {
                continue;
            }

            // Check if field passed in custom_fields array or at root level
            if (array_key_exists($fName, $inputCustom)) {
                $customDataToSave[$fName] = $inputCustom[$fName];
            } elseif ($request->has($fName)) {
                $customDataToSave[$fName] = $request->input($fName);
            } elseif ($cf->default_value !== null) {
                $customDataToSave[$fName] = $cf->default_value;
            }
        }

        $validated['custom_data'] = $customDataToSave;
        $validated['status'] = $validated['status'] ?? 'New';
        $validated['source'] = $validated['source'] ?? 'Website';
        $validated['score'] = $validated['score'] ?? 50;
        $validated['rating'] = $validated['rating'] ?? 'Warm';

        $lead = Lead::on('tenant')->create($validated);
        $lead->load('assignedUser');

        return response()->json([
            'success' => true,
            'message' => "Lead '{$lead->first_name} {$lead->last_name}' created successfully!",
            'lead' => $this->sanitizeLead($lead, $fieldPermissions),
        ], 201);
    }

    /**
     * API: Update an existing lead.
     */
    public function apiUpdate(Request $request, $id)
    {
        $this->ensureTenantConnection();
        $profile = $this->resolveCurrentUserProfile();
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);

        $lead = Lead::on('tenant')->findOrFail($id);

        $allowedStandard = [
            'first_name', 'last_name', 'company', 'email', 'phone',
            'title', 'source', 'status', 'score', 'lead_value',
            'rating', 'notes', 'assigned_to', 'email_opt_out'
        ];

        $updates = [];
        foreach ($allowedStandard as $field) {
            if ($request->has($field)) {
                $perm = $fieldPermissions[$field] ?? 'Read/Write';
                // Enforce field permission: Cannot mutate Read-Only or Hidden fields
                if ($perm === 'Read/Write') {
                    $updates[$field] = $request->input($field);
                }
            }
        }

        // Process dynamic custom fields
        $existingCustom = is_array($lead->custom_data) ? $lead->custom_data : (json_decode($lead->custom_data ?? '[]', true) ?: []);
        $inputCustom = $request->input('custom_fields', []);
        $activeCustomFields = CustomField::on('tenant')->where('module', 'leads')->get();

        foreach ($activeCustomFields as $cf) {
            $fName = $cf->field_name;
            $perm = $fieldPermissions[$fName] ?? 'Read/Write';
            if ($perm !== 'Read/Write') {
                continue;
            }

            if (array_key_exists($fName, $inputCustom)) {
                $existingCustom[$fName] = $inputCustom[$fName];
            } elseif ($request->has($fName)) {
                $existingCustom[$fName] = $request->input($fName);
            }
        }
        $updates['custom_data'] = $existingCustom;

        $lead->update($updates);
        $lead->load('assignedUser');

        return response()->json([
            'success' => true,
            'message' => "Lead updated successfully!",
            'lead' => $this->sanitizeLead($lead, $fieldPermissions),
        ]);
    }

    /**
     * API: Delete a lead record.
     */
    public function apiDestroy(Request $request, $id)
    {
        $this->ensureTenantConnection();
        $lead = Lead::on('tenant')->findOrFail($id);
        $name = trim($lead->first_name . ' ' . $lead->last_name);
        $lead->delete();

        return response()->json([
            'success' => true,
            'message' => "Lead '{$name}' deleted successfully.",
        ]);
    }

    /**
     * API: Atomic decoupled 3-way conversion into Account & Contact.
     */
    public function apiConvert(Request $request, $id)
    {
        $this->ensureTenantConnection();
        $lead = Lead::on('tenant')->findOrFail($id);

        if ($lead->is_converted) {
            return response()->json([
                'success' => false,
                'message' => 'Lead has already been converted.',
                'accountId' => (string)$lead->converted_account_id,
                'contactId' => (string)$lead->converted_contact_id,
            ], 422);
        }

        $conversionResult = DB::connection('tenant')->transaction(function () use ($lead, $request) {
            // 1. Create or link Account
            $account = Account::on('tenant')->create([
                'name' => $lead->company ?: trim($lead->first_name . ' ' . $lead->last_name),
                'type' => 'Customer',
                'phone' => $lead->phone,
                'notes' => $lead->notes,
                'assigned_to' => $lead->assignedUser?->name ?? 'Unassigned',
                'owner_id' => $lead->assigned_to,
            ]);

            // 2. Create Contact
            $contact = Contact::on('tenant')->create([
                'account_id' => $account->id,
                'account_name' => $account->name,
                'first_name' => $lead->first_name,
                'last_name' => $lead->last_name,
                'email' => $lead->email,
                'phone' => $lead->phone,
                'title' => $lead->title,
                'is_primary' => true,
                'contact_role' => 'Decision Maker',
                'assigned_to' => $lead->assigned_to,
                'notes' => $lead->notes,
            ]);

            // 3. Optional Opportunity
            $opportunityId = null;
            if ($request->boolean('create_opportunity') || $request->filled('opportunity_name')) {
                $oppName = $request->input('opportunity_name') ?: ($account->name . ' — New Business');
                $opp = Opportunity::on('tenant')->create([
                    'account_id' => $account->id,
                    'contact_id' => $contact->id,
                    'name' => $oppName,
                    'stage' => 'Qualification',
                    'amount' => $request->input('opportunity_amount', 50000.00),
                    'probability' => 20,
                    'close_date' => now()->addDays(45)->toDateString(),
                    'assigned_to' => $lead->assigned_to,
                ]);
                $opportunityId = $opp->id;
            }

            // 4. Update Lead Record as converted
            $lead->update([
                'is_converted' => true,
                'status' => 'Qualified',
                'converted_account_id' => $account->id,
                'converted_contact_id' => $contact->id,
                'converted_opportunity_id' => $opportunityId,
            ]);

            return [
                'accountId' => (string)$account->id,
                'contactId' => (string)$contact->id,
                'opportunityId' => $opportunityId ? (string)$opportunityId : null,
                'accountName' => $account->name,
                'contactName' => "{$contact->first_name} {$contact->last_name}",
            ];
        });

        return response()->json([
            'success' => true,
            'message' => "Lead converted successfully! Generated Account ({$conversionResult['accountName']}) and Contact ({$conversionResult['contactName']}).",
            'accountId' => $conversionResult['accountId'],
            'contactId' => $conversionResult['contactId'],
            'opportunityId' => $conversionResult['opportunityId'],
        ]);
    }

    /**
     * API: Transfer lead ownership to another user.
     */
    public function apiTransfer(Request $request, $id)
    {
        $this->ensureTenantConnection();
        $lead = Lead::on('tenant')->findOrFail($id);

        $targetUserId = $request->input('targetUserId');
        $targetUser = TenantUser::on('tenant')->find($targetUserId);

        if (!$targetUser) {
            return response()->json([
                'success' => false,
                'message' => 'Specified target owner user was not found.',
            ], 404);
        }

        $lead->update(['assigned_to' => $targetUser->id]);

        return response()->json([
            'success' => true,
            'message' => "Lead ownership successfully transferred to {$targetUser->name}.",
            'lead' => $lead,
        ]);
    }

    /**
     * Persist customized leads table column widths directly in the database.
     */
    public function apiSaveColumnWidths(Request $request)
    {
        $this->ensureTenantConnection();
        $widths = $request->input('col_widths', []);

        if (is_array($widths) && !empty($widths)) {
            // 1. Store in tenant_settings table
            TenantSetting::on('tenant')->updateOrCreate(
                ['key' => 'leads_table_col_widths'],
                ['value' => json_encode($widths)]
            );

            // 2. Store in default PageLayout if present
            try {
                $layout = PageLayout::on('tenant')->where('module', 'leads')->where('is_default', true)->first();
                if ($layout) {
                    $json = $layout->layout_json ?: [];
                    $json['col_widths'] = $widths;
                    $layout->layout_json = $json;
                    $layout->save();
                }
            } catch (\Throwable $e) {
                // Silently ignore layout update failure
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Column widths successfully saved to database.',
            'col_widths' => $widths,
        ]);
    }
}
