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
use App\Models\ApprovalProcess;
use App\Models\ApprovalStep;
use App\Models\ApprovalAction;
use App\Models\TenantSetting;
use App\Models\FieldHistory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

        // Fetch saved column widths & visible fields from database
        $savedColWidths = [];
        $savedTableFields = [];
        $savedKanbanFields = [];
        try {
            $setting = TenantSetting::on('tenant')->where('key', 'leads_table_col_widths')->first();
            if ($setting && $setting->value) {
                $savedColWidths = json_decode($setting->value, true) ?: [];
            }
            $settingTable = TenantSetting::on('tenant')->where('key', 'leads_table_fields')->first();
            if ($settingTable && $settingTable->value) {
                $savedTableFields = json_decode($settingTable->value, true) ?: [];
            }
            $settingKanban = TenantSetting::on('tenant')->where('key', 'leads_kanban_fields')->first();
            if ($settingKanban && $settingKanban->value) {
                $savedKanbanFields = json_decode($settingKanban->value, true) ?: [];
            }
            if (empty($savedColWidths) || empty($savedTableFields)) {
                $layout = PageLayout::on('tenant')->where('module', 'leads')->where('is_default', true)->first();
                if ($layout) {
                    if (empty($savedColWidths) && isset($layout->layout_json['col_widths'])) {
                        $savedColWidths = $layout->layout_json['col_widths'] ?: [];
                    }
                    if (empty($savedTableFields) && isset($layout->layout_json['leads_table_fields'])) {
                        $savedTableFields = $layout->layout_json['leads_table_fields'] ?: [];
                    }
                    if (empty($savedKanbanFields) && isset($layout->layout_json['leads_kanban_fields'])) {
                        $savedKanbanFields = $layout->layout_json['leads_kanban_fields'] ?: [];
                    }
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
                'table_visible_fields' => $savedTableFields,
                'kanban_visible_fields' => $savedKanbanFields,
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
            'tableVisibleFields' => $savedTableFields,
            'kanbanVisibleFields' => $savedKanbanFields,
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
            ->with('user')
            ->orderBy('id', 'desc')
            ->get();

        $approvals = ApprovalRequest::on('tenant')
            ->where('module', 'leads')
            ->where('record_id', (string)$lead->id)
            ->with(['actions', 'process.steps'])
            ->latest()
            ->get()
            ->map(function ($r) {
                return self::formatApprovalPayload($r);
            });

        $approvalsCount = $approvals->count();

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

        $files = DB::connection('tenant')->table('file_links')
            ->join('files', 'file_links.file_id', '=', 'files.id')
            ->leftJoin('file_versions', 'files.latest_version_id', '=', 'file_versions.id')
            ->where('file_links.entity_type', 'leads')
            ->where('file_links.entity_id', $lead->id)
            ->select(
                'files.id',
                'files.name',
                'files.mime_type',
                'files.created_at',
                'files.created_by_name',
                'file_versions.original_filename',
                'file_versions.size_bytes',
                'file_versions.storage_key'
            )
            ->orderBy('files.id', 'desc')
            ->get()
            ->map(function ($f) use ($lead) {
                return self::formatFilePayload($f, $lead->id);
            });

        $filesCount = $files->count();
        $emailCount = 1;

        $leadHistory = FieldHistory::on('tenant')
            ->where('entity_type', 'leads')
            ->where('entity_id', (string)$lead->id)
            ->latest('id')
            ->get()
            ->map(function ($h) {
                return self::formatHistoryPayload($h);
            });
        $historyCount = $leadHistory->count();

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
                'approvalsCount' => $approvalsCount,
                'auditLogs' => $auditLogs,
                'files' => $files,
                'filesCount' => $filesCount,
                'emailCount' => $emailCount,
                'leadHistory' => $leadHistory,
                'historyCount' => $historyCount,
            ]);
        }

        $viewData = [
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
            'approvalsCount' => $approvalsCount,
            'auditLogs' => $auditLogs,
            'tasks' => $tasks,
            'files' => $files,
            'filesCount' => $filesCount,
            'emailCount' => $emailCount,
            'leadHistory' => $leadHistory,
            'historyCount' => $historyCount,
        ];

        if ($request->query('modal') || $request->header('X-Modal')) {
            return view('leads.partials.fullscreen_detail', $viewData);
        }

        return view('leads.show', $viewData);
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

        $userId = Auth::guard('tenant')->id() ?? Auth::guard('web')->id();
        $validUserId = null;
        if ($userId && DB::connection('tenant')->table('users')->where('id', $userId)->exists()) {
            $validUserId = $userId;
        }

        $userName = 'System Administrator';
        if ($userId) {
            $tenantUser = TenantUser::on('tenant')->find($userId);
            if ($tenantUser) {
                $userName = $tenantUser->name;
            }
        }

        $historyEntries = [];

        $updates = [];
        foreach ($allowedStandard as $field) {
            if ($request->has($field)) {
                $perm = $fieldPermissions[$field] ?? 'Read/Write';
                // Enforce field permission: Cannot mutate Read-Only or Hidden fields
                if ($perm === 'Read/Write') {
                    $newVal = $request->input($field);
                    $oldVal = $lead->{$field};

                    $oldDisplay = (string)($oldVal ?? '');
                    $newDisplay = (string)($newVal ?? '');

                    if ($field === 'assigned_to') {
                        $oldUser = $oldVal ? DB::connection('tenant')->table('users')->where('id', $oldVal)->value('name') : null;
                        $newUser = $newVal ? DB::connection('tenant')->table('users')->where('id', $newVal)->value('name') : null;
                        $oldDisplay = $oldUser ?: ($oldVal ? "User #{$oldVal}" : '—');
                        $newDisplay = $newUser ?: ($newVal ? "User #{$newVal}" : '—');
                    } elseif ($field === 'email_opt_out') {
                        $oldDisplay = !empty($oldVal) ? 'Yes' : 'No';
                        $newDisplay = !empty($newVal) ? 'Yes' : 'No';
                    }

                    if ($oldDisplay !== $newDisplay) {
                        $historyEntries[] = [
                            'entity_type' => 'leads',
                            'entity_id' => (string)$lead->id,
                            'field_name' => $field,
                            'field_label' => self::getFieldLabel($field),
                            'old_value' => $oldDisplay ?: null,
                            'new_value' => $newDisplay ?: null,
                            'changed_by_id' => $validUserId,
                            'changed_by_name' => $userName,
                            'created_at' => now(),
                        ];
                    }

                    $updates[$field] = $newVal;
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

            $newVal = null;
            $hasChange = false;
            if (array_key_exists($fName, $inputCustom)) {
                $newVal = $inputCustom[$fName];
                $hasChange = true;
            } elseif ($request->has($fName)) {
                $newVal = $request->input($fName);
                $hasChange = true;
            }

            if ($hasChange) {
                $oldVal = $existingCustom[$fName] ?? null;
                $oldDisplay = is_array($oldVal) ? json_encode($oldVal) : (string)($oldVal ?? '');
                $newDisplay = is_array($newVal) ? json_encode($newVal) : (string)($newVal ?? '');

                if ($oldDisplay !== $newDisplay) {
                    $historyEntries[] = [
                        'entity_type' => 'leads',
                        'entity_id' => (string)$lead->id,
                        'field_name' => $fName,
                        'field_label' => $cf->field_label ?: self::getFieldLabel($fName),
                        'old_value' => $oldDisplay ?: null,
                        'new_value' => $newDisplay ?: null,
                        'changed_by_id' => $validUserId,
                        'changed_by_name' => $userName,
                        'created_at' => now(),
                    ];
                }
                $existingCustom[$fName] = $newVal;
            }
        }
        $updates['custom_data'] = $existingCustom;

        $lead->update($updates);
        $lead->load('assignedUser');

        if (!empty($historyEntries)) {
            FieldHistory::on('tenant')->insert($historyEntries);
        }

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

        $oldOwner = $lead->assignedUser ? $lead->assignedUser->name : ($lead->assigned_to ? "User #{$lead->assigned_to}" : 'Unassigned');
        $newOwner = $targetUser->name;

        $lead->update(['assigned_to' => $targetUser->id]);

        $userId = Auth::guard('tenant')->id() ?? Auth::guard('web')->id();
        $validUserId = null;
        if ($userId && DB::connection('tenant')->table('users')->where('id', $userId)->exists()) {
            $validUserId = $userId;
        }

        $userName = 'System Administrator';
        if ($userId) {
            $tenantUser = TenantUser::on('tenant')->find($userId);
            if ($tenantUser) {
                $userName = $tenantUser->name;
            }
        }

        FieldHistory::on('tenant')->create([
            'entity_type' => 'leads',
            'entity_id' => (string)$lead->id,
            'field_name' => 'assigned_to',
            'field_label' => 'Lead Owner / Assigned User',
            'old_value' => $oldOwner,
            'new_value' => $newOwner,
            'changed_by_id' => $validUserId,
            'changed_by_name' => $userName,
            'created_at' => now(),
        ]);

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

    /**
     * Persist customized leads view fields (sequence & visible columns) in database.
     */
    public function apiSaveViewFields(Request $request)
    {
        $this->ensureTenantConnection();
        $fields = $request->input('fields', []);
        $viewMode = $request->input('view_mode', 'table');
        $key = $viewMode === 'kanban' ? 'leads_kanban_fields' : 'leads_table_fields';

        if (is_array($fields) && !empty($fields)) {
            // 1. Store in tenant_settings table
            TenantSetting::on('tenant')->updateOrCreate(
                ['key' => $key],
                ['value' => json_encode(array_values($fields))]
            );

            // 2. Store in default PageLayout if present
            try {
                $layout = PageLayout::on('tenant')->where('module', 'leads')->where('is_default', true)->first();
                if ($layout) {
                    $json = $layout->layout_json ?: [];
                    $json[$key] = array_values($fields);
                    $layout->layout_json = $json;
                    $layout->save();
                }
            } catch (\Throwable $e) {}
        }

        return response()->json([
            'success' => true,
            'message' => 'View fields successfully saved to database.',
            'fields' => array_values($fields),
        ]);
    }

    /**
     * API: Get activities for a lead.
     */
    public function apiGetActivities(Request $request, $id)
    {
        $this->ensureTenantConnection();
        $activities = Activity::on('tenant')
            ->where('entity_type', 'leads')
            ->where('entity_id', $id)
            ->with('user')
            ->orderBy('id', 'desc')
            ->get();

        $formatted = $activities->map(function ($act) {
            if ($act->start_date && $act->end_date) {
                $callTime = \Carbon\Carbon::parse($act->start_date)->format('M d, Y, h:i A') . ' - ' . \Carbon\Carbon::parse($act->end_date)->format('h:i A');
            } elseif ($act->start_date) {
                $callTime = \Carbon\Carbon::parse($act->start_date)->format('M d, Y, h:i A');
            } elseif ($act->activity_date) {
                $callTime = \Carbon\Carbon::parse($act->activity_date)->format('M d, Y');
            } else {
                $callTime = null;
            }
            $createdAt = $act->created_at ? \Carbon\Carbon::parse($act->created_at)->format('m/d/y, h:i A') : now()->format('m/d/y, h:i A');
            $userName = $act->user?->name ?? 'Alex Morgan';

            return [
                'id' => $act->id,
                'type' => $act->type ?: 'Call',
                'subject' => $act->subject,
                'details' => $act->details,
                'call_time' => $callTime,
                'created_at_formatted' => $createdAt,
                'user_name' => $userName,
            ];
        });

        return response()->json([
            'success' => true,
            'count' => $formatted->count(),
            'activities' => $formatted,
        ]);
    }

    /**
     * API: Store activity (Call, Task, Event, Note) for a lead.
     */
    public function apiStoreActivity(Request $request, $id)
    {
        $this->ensureTenantConnection();
        $lead = Lead::on('tenant')->findOrFail($id);

        $request->validate([
            'subject' => 'required|string|max:200',
            'type' => 'nullable|string|max:40',
            'details' => 'nullable|string',
            'call_datetime' => 'nullable|string',
            'start_date' => 'nullable|string',
            'end_date' => 'nullable|string',
        ]);

        $type = $request->input('type', 'Call');
        $subject = trim($request->input('subject'));
        $details = $request->input('details');

        $startDate = null;
        $endDate = null;
        $activityDate = now()->toDateString();

        $startInput = $request->input('start_date') ?: $request->input('call_datetime');
        if (!empty($startInput)) {
            try {
                $startDate = \Carbon\Carbon::parse($startInput);
                $activityDate = $startDate->toDateString();
            } catch (\Exception $e) {
                $startDate = now();
            }
        } else {
            $startDate = now();
        }

        if ($request->filled('end_date')) {
            try {
                $endDate = \Carbon\Carbon::parse($request->input('end_date'));
            } catch (\Exception $e) {
                $endDate = $startDate ? $startDate->copy()->addHour() : now()->addHour();
            }
        }

        $userId = Auth::guard('tenant')->id() ?? Auth::guard('web')->id();
        if (!$userId) {
            $firstUser = TenantUser::on('tenant')->where('name', 'like', '%Morgan%')->first()
                      ?? TenantUser::on('tenant')->first();
            $userId = $firstUser?->id;
        }

        $activity = Activity::on('tenant')->create([
            'entity_type' => 'leads',
            'entity_id' => $lead->id,
            'type' => $type,
            'subject' => $subject,
            'details' => $details,
            'performed_by' => $userId,
            'activity_date' => $activityDate,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        $activity->load('user');

        if ($activity->start_date && $activity->end_date) {
            $callTime = \Carbon\Carbon::parse($activity->start_date)->format('M d, Y, h:i A') . ' - ' . \Carbon\Carbon::parse($activity->end_date)->format('h:i A');
        } elseif ($activity->start_date) {
            $callTime = \Carbon\Carbon::parse($activity->start_date)->format('M d, Y, h:i A');
        } elseif ($activity->activity_date) {
            $callTime = \Carbon\Carbon::parse($activity->activity_date)->format('M d, Y');
        } else {
            $callTime = null;
        }

        $createdAt = $activity->created_at ? \Carbon\Carbon::parse($activity->created_at)->format('m/d/y, h:i A') : now()->format('m/d/y, h:i A');
        $userName = $activity->user?->name ?? 'Alex Morgan';

        $totalCount = Activity::on('tenant')->where('entity_type', 'leads')->where('entity_id', $lead->id)->count();

        $msgType = (in_array(strtolower($type), ['meeting', 'event'])) ? 'Meeting' : $type;
        $actionVerb = strtolower($type) === 'note' ? 'saved' : 'logged';

        return response()->json([
            'success' => true,
            'message' => "{$msgType} {$actionVerb} successfully.",
            'activity' => [
                'id' => $activity->id,
                'type' => $activity->type ?: 'Call',
                'subject' => $activity->subject,
                'details' => $activity->details,
                'call_time' => $callTime,
                'created_at_formatted' => $createdAt,
                'user_name' => $userName,
            ],
            'total_count' => $totalCount,
        ]);
    }

    /**
     * API: Store task specifically for a lead and log to activities timeline.
     */
    public function apiStoreTask(Request $request, $id)
    {
        $this->ensureTenantConnection();
        $lead = Lead::on('tenant')->findOrFail($id);

        $request->validate([
            'subject' => 'required|string|max:200',
            'priority' => 'nullable|string|max:30',
            'status' => 'nullable|string|max:40',
            'assigned_to' => 'nullable|integer',
            'due_date' => 'nullable|string',
            'reminder_set' => 'nullable|string|max:100',
            'comments' => 'nullable|string',
            'is_recurring' => 'nullable|boolean',
        ]);

        $subject = trim($request->subject);
        $priority = $request->input('priority', 'Normal');
        $status = $request->input('status', 'Open');
        $dueDate = null;
        if ($request->filled('due_date')) {
            try {
                $dueDate = \Carbon\Carbon::parse($request->due_date)->toDateString();
            } catch (\Exception $e) {
                $dueDate = now()->addDays(2)->toDateString();
            }
        }

        $userId = Auth::guard('tenant')->id() ?? Auth::guard('web')->id();
        $assigneeId = $request->input('assigned_to');
        if (!$assigneeId) {
            $assigneeId = $userId;
        }

        $assignee = $assigneeId ? TenantUser::on('tenant')->find($assigneeId) : null;
        $assigneeName = $assignee ? $assignee->name : 'Alex Morgan';

        $creator = $userId ? TenantUser::on('tenant')->find($userId) : null;
        $creatorName = $creator ? $creator->name : 'System Admin';

        $leadName = trim(($lead->first_name ?? '') . ' ' . ($lead->last_name ?? ''));
        if (empty($leadName)) {
            $leadName = $lead->company ?? ('Lead #' . $lead->id);
        }

        // 1. Create in Task model
        $task = Task::on('tenant')->create([
            'subject' => $subject,
            'priority' => $priority,
            'status' => $status,
            'assigned_to' => $assigneeId,
            'assigned_to_name' => $assigneeName,
            'due_date' => $dueDate,
            'comments' => $request->comments,
            'related_entity_type' => 'leads',
            'related_entity_id' => $lead->id,
            'related_entity_name' => $leadName,
            'reminder_set' => $request->input('reminder_set', 'No reminder is set'),
            'is_recurring' => (bool)$request->input('is_recurring', false),
            'created_by' => $userId,
            'created_by_name' => $creatorName,
        ]);

        // 2. Also log to activities table for timeline
        $activity = Activity::on('tenant')->create([
            'entity_type' => 'leads',
            'entity_id' => $lead->id,
            'type' => 'Task',
            'subject' => $subject,
            'details' => $request->comments ?: "Priority: {$priority} | Status: {$status}",
            'performed_by' => $assigneeId,
            'activity_date' => now()->toDateString(),
            'due_date' => $dueDate,
            'start_date' => $dueDate ? \Carbon\Carbon::parse($dueDate) : now(),
        ]);

        $activity->load('user');

        $callTime = $dueDate ? \Carbon\Carbon::parse($dueDate)->format('M d, Y') : now()->format('M d, Y');
        $createdAt = now()->format('m/d/y, h:i A');

        $totalCount = Activity::on('tenant')->where('entity_type', 'leads')->where('entity_id', $lead->id)->count();

        return response()->json([
            'success' => true,
            'message' => 'Task created and scheduled successfully.',
            'task' => $task,
            'activity' => [
                'id' => $activity->id,
                'type' => 'Task',
                'subject' => $activity->subject,
                'details' => $activity->details,
                'call_time' => $callTime,
                'created_at_formatted' => $createdAt,
                'user_name' => $assigneeName,
            ],
            'total_count' => $totalCount,
        ]);
    }

    /**
     * Format a file record for frontend consumption matching the UI screenshot.
     */
    public static function formatFilePayload($file, $leadId): array
    {
        $name = $file->name ?: ($file->original_filename ?? 'Unnamed File');
        $mime = strtolower($file->mime_type ?? '');
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

        $iconType = 'default';
        $typeLabel = 'FILE';

        if (in_array($ext, ['xlsx', 'xls', 'csv']) || str_contains($mime, 'spreadsheet') || str_contains($mime, 'excel') || str_contains($mime, 'sheet')) {
            $iconType = 'excel';
            $typeLabel = 'VND.OPENXMLFORMATS-OF...';
        } elseif ($ext === 'pdf' || str_contains($mime, 'pdf')) {
            $iconType = 'pdf';
            $typeLabel = 'PDF';
        } elseif (in_array($ext, ['jpg', 'jpeg'])) {
            $iconType = 'image';
            $typeLabel = 'JPEG';
        } elseif ($ext === 'png') {
            $iconType = 'image';
            $typeLabel = 'PNG';
        } elseif (in_array($ext, ['gif', 'webp', 'svg'])) {
            $iconType = 'image';
            $typeLabel = strtoupper($ext);
        } elseif (in_array($ext, ['doc', 'docx']) || str_contains($mime, 'word')) {
            $iconType = 'word';
            $typeLabel = 'DOCX';
        } else {
            $typeLabel = $ext ? strtoupper($ext) : 'FILE';
        }

        $sizeBytes = $file->size_bytes ?? 0;
        if ($sizeBytes >= 1048576) {
            $sizeFormatted = round($sizeBytes / 1048576, 1) . ' MB';
        } elseif ($sizeBytes >= 1024) {
            $sizeFormatted = round($sizeBytes / 1024, 0) . ' KB';
        } else {
            $sizeFormatted = $sizeBytes > 0 ? $sizeBytes . ' B' : '';
        }

        $createdAtFormatted = '';
        if (!empty($file->created_at)) {
            try {
                $createdAtFormatted = \Carbon\Carbon::parse($file->created_at)->format('d-m-Y H:i');
            } catch (\Exception $e) {
                $createdAtFormatted = (string)$file->created_at;
            }
        }

        return [
            'id' => (int)$file->id,
            'name' => $name,
            'mime_type' => $file->mime_type,
            'type_label' => $typeLabel,
            'icon_type' => $iconType,
            'size_bytes' => (int)$sizeBytes,
            'size_formatted' => $sizeFormatted,
            'download_url' => url("/api/leads/{$leadId}/files/{$file->id}/download"),
            'created_at_formatted' => $createdAtFormatted,
            'created_by_name' => $file->created_by_name ?? 'System Admin',
        ];
    }

    /**
     * API: Get files linked to lead.
     */
    public function apiGetFiles($id)
    {
        $this->ensureTenantConnection();
        $lead = Lead::on('tenant')->findOrFail($id);

        $files = DB::connection('tenant')->table('file_links')
            ->join('files', 'file_links.file_id', '=', 'files.id')
            ->leftJoin('file_versions', 'files.latest_version_id', '=', 'file_versions.id')
            ->where('file_links.entity_type', 'leads')
            ->where('file_links.entity_id', $lead->id)
            ->select(
                'files.id',
                'files.name',
                'files.mime_type',
                'files.created_at',
                'files.created_by_name',
                'file_versions.original_filename',
                'file_versions.size_bytes',
                'file_versions.storage_key'
            )
            ->orderBy('files.id', 'desc')
            ->get()
            ->map(function ($f) use ($lead) {
                return self::formatFilePayload($f, $lead->id);
            });

        return response()->json([
            'success' => true,
            'count' => $files->count(),
            'files' => $files,
        ]);
    }

    /**
     * API: Upload file(s) for a lead.
     */
    public function apiUploadFiles(Request $request, $id)
    {
        $this->ensureTenantConnection();
        $lead = Lead::on('tenant')->findOrFail($id);

        $uploadedFiles = [];
        if ($request->hasFile('files')) {
            $uploadedFiles = $request->file('files');
            if (!is_array($uploadedFiles)) {
                $uploadedFiles = [$uploadedFiles];
            }
        } elseif ($request->hasFile('file')) {
            $uploadedFiles = [$request->file('file')];
        }

        if (empty($uploadedFiles)) {
            return response()->json([
                'success' => false,
                'message' => 'No files were selected for upload.'
            ], 422);
        }

        $userId = Auth::guard('tenant')->id() ?? Auth::guard('web')->id();
        $validUserId = null;
        if ($userId && DB::connection('tenant')->table('users')->where('id', $userId)->exists()) {
            $validUserId = $userId;
        }

        $userName = 'System Admin';
        if ($userId) {
            $tenantUser = TenantUser::on('tenant')->find($userId);
            if ($tenantUser) {
                $userName = $tenantUser->name;
            }
        }

        $savedFiles = [];

        foreach ($uploadedFiles as $uploadedFile) {
            if (!$uploadedFile->isValid()) {
                continue;
            }

            $originalName = $uploadedFile->getClientOriginalName();
            $mimeType = $uploadedFile->getClientMimeType() ?: $uploadedFile->getMimeType();
            $sizeBytes = $uploadedFile->getSize();
            $extension = $uploadedFile->getClientOriginalExtension();
            $checksum = @md5_file($uploadedFile->getRealPath());

            // Store file in public storage
            $storageDir = "leads/{$lead->id}/files";
            $storedFilename = time() . '_' . Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '.' . ($extension ?: 'dat');
            $storedKey = $uploadedFile->storeAs($storageDir, $storedFilename, 'public');

            // 1. Insert into files table
            $fileId = DB::connection('tenant')->table('files')->insertGetId([
                'name' => $originalName,
                'description' => $request->input('description', null),
                'mime_type' => substr($mimeType, 0, 100),
                'created_by_id' => $validUserId,
                'created_by_name' => $userName,
                'latest_version_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 2. Insert into file_versions table
            $versionId = DB::connection('tenant')->table('file_versions')->insertGetId([
                'file_id' => $fileId,
                'version_number' => 1,
                'storage_key' => $storedKey,
                'original_filename' => $originalName,
                'mime_type' => substr($mimeType, 0, 100),
                'size_bytes' => $sizeBytes,
                'checksum' => $checksum,
                'uploaded_by_id' => $validUserId,
                'uploaded_by_name' => $userName,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Link file with latest version
            DB::connection('tenant')->table('files')->where('id', $fileId)->update([
                'latest_version_id' => $versionId,
            ]);

            // 3. Insert into file_links table
            DB::connection('tenant')->table('file_links')->insert([
                'file_id' => $fileId,
                'entity_type' => 'leads',
                'entity_id' => (string)$lead->id,
                'visibility' => 'all',
                'created_by_id' => $validUserId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $savedFiles[] = self::formatFilePayload((object)[
                'id' => $fileId,
                'name' => $originalName,
                'mime_type' => $mimeType,
                'created_at' => now(),
                'created_by_name' => $userName,
                'original_filename' => $originalName,
                'size_bytes' => $sizeBytes,
                'storage_key' => $storedKey,
            ], $lead->id);
        }

        $totalCount = DB::connection('tenant')->table('file_links')
            ->where('entity_type', 'leads')
            ->where('entity_id', $lead->id)
            ->count();

        return response()->json([
            'success' => true,
            'message' => count($savedFiles) . ' file(s) uploaded successfully.',
            'files' => $savedFiles,
            'count' => $totalCount,
        ]);
    }

    /**
     * API: Delete a file linked to a lead.
     */
    public function apiDeleteFile($id, $fileId)
    {
        $this->ensureTenantConnection();
        $lead = Lead::on('tenant')->findOrFail($id);

        $link = DB::connection('tenant')->table('file_links')
            ->where('file_id', $fileId)
            ->where('entity_type', 'leads')
            ->where('entity_id', $lead->id)
            ->first();

        if (!$link) {
            return response()->json([
                'success' => false,
                'message' => 'File not found on this lead.'
            ], 404);
        }

        $versions = DB::connection('tenant')->table('file_versions')
            ->where('file_id', $fileId)
            ->get();

        foreach ($versions as $v) {
            if ($v->storage_key && Storage::disk('public')->exists($v->storage_key)) {
                Storage::disk('public')->delete($v->storage_key);
            }
        }

        DB::connection('tenant')->table('file_links')
            ->where('file_id', $fileId)
            ->where('entity_type', 'leads')
            ->where('entity_id', $lead->id)
            ->delete();

        DB::connection('tenant')->table('file_versions')->where('file_id', $fileId)->delete();
        DB::connection('tenant')->table('files')->where('id', $fileId)->delete();

        $totalCount = DB::connection('tenant')->table('file_links')
            ->where('entity_type', 'leads')
            ->where('entity_id', $lead->id)
            ->count();

        return response()->json([
            'success' => true,
            'message' => 'File deleted successfully.',
            'count' => $totalCount,
        ]);
    }

    /**
     * API: Download a file.
     */
    public function apiDownloadFile($id, $fileId)
    {
        $this->ensureTenantConnection();
        $lead = Lead::on('tenant')->findOrFail($id);

        $link = DB::connection('tenant')->table('file_links')
            ->where('file_id', $fileId)
            ->where('entity_type', 'leads')
            ->where('entity_id', $lead->id)
            ->first();

        if (!$link) {
            abort(404, 'File not found');
        }

        $version = DB::connection('tenant')->table('file_versions')
            ->where('file_id', $fileId)
            ->orderBy('version_number', 'desc')
            ->first();

        if (!$version || !Storage::disk('public')->exists($version->storage_key)) {
            abort(404, 'File binary not found on disk');
        }

        return Storage::disk('public')->download($version->storage_key, $version->original_filename);
    }

    /**
     * Format an approval request for UI display.
     */
    public static function formatApprovalPayload($req): array
    {
        $process = $req->process;
        $latestAction = $req->actions()->latest()->first();
        $step = $process ? $process->steps()->where('step_number', $req->current_step)->first() : null;

        $stepName = $step?->step_name ?? ('Step ' . $req->current_step . ': ' . ($req->status === 'pending' ? 'Pending Approval' : ucfirst($req->status)));
        $assignedTo = $step?->approver_name ?? 'System Administrator';
        $actualApprover = ($latestAction && in_array($latestAction->action, ['approved', 'rejected'])) 
            ? $latestAction->actor_name 
            : '—';
        $comments = $latestAction?->comments ?: ($req->submission_notes ?: '—');

        return [
            'id' => (int)$req->id,
            'process_name' => $process?->name ?? 'Lead Qualification & Discount Approval',
            'step_name' => $stepName,
            'date_formatted' => $req->created_at ? $req->created_at->format('M d, Y, h:i A') : now()->format('M d, Y, h:i A'),
            'status' => ucfirst($req->status),
            'status_raw' => strtolower($req->status),
            'assigned_to' => $assignedTo,
            'actual_approver' => $actualApprover,
            'comments' => $comments,
            'submitter_name' => $req->submitter_name,
        ];
    }

    /**
     * API: Get approval requests for lead.
     */
    public function apiGetApprovals($id)
    {
        $this->ensureTenantConnection();
        $lead = Lead::on('tenant')->findOrFail($id);

        $approvals = ApprovalRequest::on('tenant')
            ->where('module', 'leads')
            ->where('record_id', (string)$lead->id)
            ->with(['actions', 'process.steps'])
            ->latest()
            ->get()
            ->map(function ($r) {
                return self::formatApprovalPayload($r);
            });

        return response()->json([
            'success' => true,
            'count' => $approvals->count(),
            'approvals' => $approvals,
        ]);
    }

    /**
     * API: Submit lead record for approval.
     */
    public function apiSubmitApproval(Request $request, $id)
    {
        $this->ensureTenantConnection();
        $lead = Lead::on('tenant')->findOrFail($id);

        $userId = Auth::guard('tenant')->id() ?? Auth::guard('web')->id();
        $validUserId = null;
        if ($userId && DB::connection('tenant')->table('users')->where('id', $userId)->exists()) {
            $validUserId = $userId;
        }

        $userName = 'System Administrator';
        if ($userId) {
            $tenantUser = TenantUser::on('tenant')->find($userId);
            if ($tenantUser) {
                $userName = $tenantUser->name;
            }
        }

        // Find or create default process
        $process = ApprovalProcess::on('tenant')->where('module', 'leads')->where('is_active', true)->first();
        if (!$process) {
            $process = ApprovalProcess::on('tenant')->create([
                'name' => 'Lead Qualification & Discount Approval',
                'description' => 'Multi-step approval process for high value leads',
                'module' => 'leads',
                'trigger_type' => 'manual',
                'is_active' => true,
            ]);

            ApprovalStep::on('tenant')->create([
                'process_id' => $process->id,
                'step_number' => 1,
                'step_name' => 'Step 1: Manager Qualification',
                'approver_type' => 'user',
                'approver_name' => $userName,
            ]);
        }

        $notes = $request->input('comments') ?: $request->input('submission_notes', 'Record submitted for formal approval.');

        $req = ApprovalRequest::on('tenant')->create([
            'process_id' => $process->id,
            'module' => 'leads',
            'record_id' => (string)$lead->id,
            'current_step' => 1,
            'total_steps' => 1,
            'status' => 'pending',
            'submitted_by' => $validUserId,
            'submitter_name' => $userName,
            'submission_notes' => $notes,
        ]);

        ApprovalAction::on('tenant')->create([
            'request_id' => $req->id,
            'step_number' => 1,
            'action' => 'submitted',
            'actor_id' => $validUserId,
            'actor_name' => $userName,
            'comments' => $notes,
        ]);

        $formatted = self::formatApprovalPayload($req);

        $totalCount = ApprovalRequest::on('tenant')
            ->where('module', 'leads')
            ->where('record_id', (string)$lead->id)
            ->count();

        return response()->json([
            'success' => true,
            'message' => 'Lead record submitted for approval successfully.',
            'count' => $totalCount,
            'approval' => $formatted,
        ]);
    }

    /**
     * API: Decide on an approval request (Approve / Reject).
     */
    public function apiActionApproval(Request $request, $id, $requestId)
    {
        $this->ensureTenantConnection();
        $lead = Lead::on('tenant')->findOrFail($id);

        $req = ApprovalRequest::on('tenant')
            ->where('id', $requestId)
            ->where('module', 'leads')
            ->where('record_id', (string)$lead->id)
            ->firstOrFail();

        $action = strtolower(trim($request->input('action', 'approved')));
        if (!in_array($action, ['approved', 'rejected', 'recalled'])) {
            return response()->json(['success' => false, 'message' => 'Invalid approval action.'], 422);
        }

        $userId = Auth::guard('tenant')->id() ?? Auth::guard('web')->id();
        $validUserId = null;
        if ($userId && DB::connection('tenant')->table('users')->where('id', $userId)->exists()) {
            $validUserId = $userId;
        }

        $userName = 'System Administrator';
        if ($userId) {
            $tenantUser = TenantUser::on('tenant')->find($userId);
            if ($tenantUser) {
                $userName = $tenantUser->name;
            }
        }

        $comments = $request->input('comments', $action === 'approved' ? 'Approved by manager.' : 'Rejected by manager.');

        $req->update([
            'status' => $action,
        ]);

        ApprovalAction::on('tenant')->create([
            'request_id' => $req->id,
            'step_number' => $req->current_step,
            'action' => $action,
            'actor_id' => $validUserId,
            'actor_name' => $userName,
            'comments' => $comments,
        ]);

        $formatted = self::formatApprovalPayload($req->fresh());

        return response()->json([
            'success' => true,
            'message' => 'Approval status updated to ' . ucfirst($action) . '.',
            'approval' => $formatted,
        ]);
    }

    /**
     * API: Get field history for a lead.
     */
    public function apiGetHistory($id)
    {
        $this->ensureTenantConnection();
        $lead = Lead::on('tenant')->findOrFail($id);

        $history = FieldHistory::on('tenant')
            ->where('entity_type', 'leads')
            ->where('entity_id', (string)$lead->id)
            ->latest('id')
            ->get()
            ->map(function ($h) {
                return self::formatHistoryPayload($h);
            });

        return response()->json([
            'success' => true,
            'count' => $history->count(),
            'history' => $history,
        ]);
    }

    /**
     * Format a field history record for UI display.
     */
    public static function formatHistoryPayload($h): array
    {
        return [
            'id' => (int)$h->id,
            'date_formatted' => $h->created_at ? $h->created_at->format('M d, Y, h:i A') : now()->format('M d, Y, h:i A'),
            'field_name' => $h->field_label ?: self::getFieldLabel($h->field_name),
            'user' => $h->changed_by_name ?: ($h->user?->name ?? 'System Administrator'),
            'original_value' => $h->old_value !== null && $h->old_value !== '' ? $h->old_value : '—',
            'new_value' => $h->new_value !== null && $h->new_value !== '' ? $h->new_value : '—',
        ];
    }

    /**
     * Get a human-friendly field label for standard or custom fields.
     */
    public static function getFieldLabel(string $fieldName): string
    {
        $labels = [
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'company' => 'Company',
            'email' => 'Email',
            'phone' => 'Phone',
            'title' => 'Title / Job Position',
            'source' => 'Lead Source',
            'status' => 'Lead Status',
            'score' => 'Lead Score',
            'lead_value' => 'Lead Value',
            'rating' => 'Rating',
            'notes' => 'Description / Notes',
            'assigned_to' => 'Lead Owner / Assigned User',
            'email_opt_out' => 'Email Opt Out',
        ];

        if (isset($labels[$fieldName])) {
            return $labels[$fieldName];
        }

        return ucwords(str_replace(['_', '-'], ' ', $fieldName));
    }
}
