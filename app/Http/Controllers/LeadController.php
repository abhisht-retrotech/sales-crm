<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ApprovalProcess;
use App\Models\ApprovalRequest;
use App\Models\AuditLog;
use App\Models\CustomField;
use App\Models\Lead;
use App\Models\PageLayout;
use App\Models\Profile;
use App\Models\ProfileLayoutAssignment;
use App\Models\Task;
use App\Models\Tenant;
use App\Models\TenantSetting;
use App\Models\TenantUser;
use App\Services\DynamicFields\DynamicFieldMappingService;
use App\Services\Leads\LeadActivityService;
use App\Services\Leads\LeadConversionService;
use App\Services\Leads\LeadDocumentService;
use App\Services\Leads\LeadSecurityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeadController extends Controller
{
    public function __construct(
        protected LeadSecurityService $securityService,
        protected LeadConversionService $conversionService,
        protected LeadActivityService $activityService,
        protected LeadDocumentService $documentService,
        protected DynamicFieldMappingService $dynamicFieldService,
    ) {}

    /**
     * Ensure tenant database connection is actively configured.
     */
    protected function ensureTenantConnection(): ?Tenant
    {
        return $this->securityService->ensureTenantConnection();
    }

    /**
     * Resolve the current authenticated tenant user.
     */
    protected function resolveCurrentUser(): ?TenantUser
    {
        return $this->securityService->resolveCurrentUser();
    }

    /**
     * Resolve the current user's security profile.
     */
    protected function resolveCurrentUserProfile(?TenantUser $user = null): ?Profile
    {
        return $this->securityService->resolveCurrentUserProfile($user);
    }

    /**
     * Get module-level permissions for the given profile and user.
     */
    protected function getProfilePermissions(?Profile $profile, ?TenantUser $user = null): array
    {
        return $this->securityService->getProfilePermissions($profile, $user);
    }

    /**
     * Get field permissions map for the current user's profile on the leads module.
     */
    protected function getFieldPermissionsForProfile(?Profile $profile): array
    {
        return $this->securityService->getFieldPermissionsForProfile($profile);
    }

    /**
     * Apply role hierarchy and record-level security scope to query.
     */
    protected function applyLeadRecordScopeQuery($query, ?TenantUser $user, array $profilePermissions)
    {
        return $this->securityService->applyLeadRecordScopeQuery($query, $user, $profilePermissions);
    }

    /**
     * Determine if a user can access a specific Lead for a given action.
     */
    protected function userCanAccessLead(Lead $lead, string $action = 'read', ?TenantUser $user = null, ?array $profilePermissions = null): bool
    {
        return $this->securityService->userCanAccessLead($lead, $action, $user, $profilePermissions);
    }

    /**
     * Sanitize a lead record respecting field-level security permissions.
     */
    protected function sanitizeLead(Lead $lead, array $fieldPermissions): array
    {
        return $this->securityService->sanitizeLead($lead, $fieldPermissions);
    }

    /**
     * Seed initial demo leads if database table is completely empty.
     */
    protected function ensureDemoLeads(): void
    {
        $this->securityService->ensureDemoLeads();
    }

    /**
     * Ensure standard custom fields exist for leads module.
     */
    protected function ensureCustomFields(): void
    {
        $this->securityService->ensureCustomFields();
    }

    /**
     * Display the Leads & Intake page or return JSON.
     */
    public function index(Request $request)
    {
        $this->ensureTenantConnection();
        $this->ensureCustomFields();
        $this->ensureDemoLeads();

        $user = $this->resolveCurrentUser();
        $profile = $this->resolveCurrentUserProfile($user);
        $profilePermissions = $this->getProfilePermissions($profile, $user);
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);

        // Enforce can_read profile permission
        if (!$profilePermissions['can_read']) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: Your security profile does not have permission to view leads.',
                ], 403);
            }

            return view('leads', [
                'accessDenied' => true,
                'initialLeads' => collect(),
                'customFields' => collect(),
                'fieldPermissions' => $fieldPermissions,
                'profilePermissions' => $profilePermissions,
                'users' => collect(),
                'stages' => ['New', 'Contacted', 'Qualified', 'Lost'],
                'sources' => [],
                'currentUser' => $user,
                'currentUserProfile' => $profile ? $profile->name : 'Restricted Profile',
                'canSendBulkEmail' => false,
                'colWidths' => [],
                'tableVisibleFields' => [],
                'kanbanVisibleFields' => [],
            ]);
        }

        // Fetch custom fields for leads
        $customFields = CustomField::on('tenant')
            ->where('module', 'leads')
            ->orderBy('id', 'asc')
            ->get();

        // Build query filtered by role hierarchy and record security scope
        $query = Lead::on('tenant')
            ->with(['assignedUser', 'convertedAccount', 'convertedContact'])
            ->orderBy('id', 'desc');

        $query = $this->applyLeadRecordScopeQuery($query, $user, $profilePermissions);

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('first_name', 'like', "%{$s}%")
                    ->orWhere('last_name', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%")
                    ->orWhere('company', 'like', "%{$s}%")
                    ->orWhere('phone', 'like', "%{$s}%")
                    ->orWhere('title', 'like', "%{$s}%");

                // Dynamically search in any custom lookup fields (e.g. approved / cf_is_approved) by name
                $matchingUserIds = $this->dynamicFieldService->getMatchingIdsForSearch('leads', 'approved', $s);
                if (!empty($matchingUserIds)) {
                    foreach ($matchingUserIds as $mid) {
                        $q->orWhere("custom_data->approved", (string)$mid)
                          ->orWhere("custom_data->approved", $mid)
                          ->orWhere("custom_data->cf_is_approved", (string)$mid)
                          ->orWhere("custom_data->cf_is_approved", $mid);
                    }
                }
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $rawLeads = $query->get();

        // Sanitize leads according to field permissions
        $sanitizedLeads = $this->securityService->sanitizeCollection($rawLeads, $fieldPermissions);

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

        // Fetch saved column widths & visible fields
        $savedColWidths = [];
        $savedTableFields = [];
        $savedKanbanFields = [];

        try {
            $colWidthsSetting = TenantSetting::on('tenant')->where('key', 'leads_table_col_widths')->value('value');
            if ($colWidthsSetting) {
                $savedColWidths = json_decode($colWidthsSetting, true) ?: [];
            }
            $tableFieldsSetting = TenantSetting::on('tenant')->where('key', 'leads_table_fields')->value('value');
            if ($tableFieldsSetting) {
                $savedTableFields = json_decode($tableFieldsSetting, true) ?: [];
            }
            $kanbanFieldsSetting = TenantSetting::on('tenant')->where('key', 'leads_kanban_fields')->value('value');
            if ($kanbanFieldsSetting) {
                $savedKanbanFields = json_decode($kanbanFieldsSetting, true) ?: [];
            }
        } catch (\Throwable $e) {}

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'leads' => $sanitizedLeads,
                'custom_fields' => $customFields,
                'field_permissions' => $fieldPermissions,
                'profile_permissions' => $profilePermissions,
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
            'profilePermissions' => $profilePermissions,
            'users' => $users,
            'stages' => $stages,
            'sources' => $sources,
            'currentUser' => $user,
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

        $user = $this->resolveCurrentUser();
        $profile = $this->resolveCurrentUserProfile($user);
        $profilePermissions = $this->getProfilePermissions($profile, $user);
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);

        if (!$profilePermissions['can_read']) {
            abort(403, 'Unauthorized: Your security profile does not have permission to view leads.');
        }

        $lead = Lead::on('tenant')->with(['assignedUser', 'convertedAccount', 'convertedContact', 'convertedOpportunity'])->findOrFail($id);

        if (!$this->userCanAccessLead($lead, 'read', $user, $profilePermissions)) {
            abort(403, 'Unauthorized: You do not have permission to access this lead record.');
        }

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

        $approvals = $this->documentService->getApprovals($lead->id);
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

        $files = $this->documentService->getFiles($lead->id);
        $filesCount = $files->count();
        $emailCount = 1;

        $leadHistory = $this->activityService->getFieldHistory($lead->id);
        $historyCount = $leadHistory->count();

        $canApproveOrReject = $this->securityService->userCanApproveOrRejectLead($lead, $user);
        $isApproved = $this->securityService->isLeadApprovedForTasks($lead);

        if ($request->wantsJson()) {
            return response()->json([
                'lead' => $sanitizedLead,
                'canApproveOrReject' => $canApproveOrReject,
                'isApproved' => $isApproved,
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
            'profilePermissions' => $profilePermissions,
            'users' => $users,
            'sources' => $sources,
            'currentUser' => $user,
            'currentUserProfile' => $profile ? $profile->name : 'Administrator',
            'canApproveOrReject' => $canApproveOrReject,
            'isApproved' => $isApproved,
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
     * API: Get all leads with scope and search filtering.
     */
    public function apiList(Request $request)
    {
        $this->ensureTenantConnection();
        $this->ensureCustomFields();
        $this->ensureDemoLeads();

        $user = $this->resolveCurrentUser();
        $profile = $this->resolveCurrentUserProfile($user);
        $profilePermissions = $this->getProfilePermissions($profile, $user);
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);

        if (!$profilePermissions['can_read']) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Your security profile does not have permission to view leads.',
            ], 403);
        }

        $query = Lead::on('tenant')
            ->with(['assignedUser', 'convertedAccount', 'convertedContact'])
            ->orderBy('id', 'desc');

        $query = $this->applyLeadRecordScopeQuery($query, $user, $profilePermissions);

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('first_name', 'like', "%{$s}%")
                    ->orWhere('last_name', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%")
                    ->orWhere('company', 'like', "%{$s}%")
                    ->orWhere('phone', 'like', "%{$s}%")
                    ->orWhere('title', 'like', "%{$s}%");

                // Dynamically search in any custom lookup fields (e.g. approved / cf_is_approved) by name
                $matchingUserIds = $this->dynamicFieldService->getMatchingIdsForSearch('leads', 'approved', $s);
                if (!empty($matchingUserIds)) {
                    foreach ($matchingUserIds as $mid) {
                        $q->orWhere("custom_data->approved", (string)$mid)
                          ->orWhere("custom_data->approved", $mid)
                          ->orWhere("custom_data->cf_is_approved", (string)$mid)
                          ->orWhere("custom_data->cf_is_approved", $mid);
                    }
                }
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $rawLeads = $query->get();
        $leads = $this->securityService->sanitizeCollection($rawLeads, $fieldPermissions);

        return response()->json($leads);
    }

    /**
     * API: Get metadata (custom fields, statuses, users, sources, permissions).
     */
    public function apiMeta(Request $request)
    {
        $this->ensureTenantConnection();
        $this->ensureCustomFields();

        $user = $this->resolveCurrentUser();
        $profile = $this->resolveCurrentUserProfile($user);
        $profilePermissions = $this->getProfilePermissions($profile, $user);
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);

        $customFields = CustomField::on('tenant')
            ->where('module', 'leads')
            ->orderBy('id', 'asc')
            ->get();

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

        return response()->json([
            'custom_fields' => $customFields,
            'users' => $users,
            'stages' => $stages,
            'sources' => $sources,
            'can_send_bulk_email' => true,
            'profile_permissions' => $profilePermissions,
            'field_permissions' => $fieldPermissions,
        ]);
    }

    /**
     * API: Get single lead record.
     */
    public function apiShow(Request $request, $id)
    {
        $this->ensureTenantConnection();
        $user = $this->resolveCurrentUser();
        $profile = $this->resolveCurrentUserProfile($user);
        $profilePermissions = $this->getProfilePermissions($profile, $user);
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);

        if (!$profilePermissions['can_read']) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Your security profile does not have permission to view leads.',
            ], 403);
        }

        $lead = Lead::on('tenant')->with(['assignedUser', 'convertedAccount', 'convertedContact'])->findOrFail($id);

        if (!$this->userCanAccessLead($lead, 'read', $user, $profilePermissions)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: You do not have permission to view this lead record.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'lead' => $this->sanitizeLead($lead, $fieldPermissions),
            'can_approve_or_reject' => $this->securityService->userCanApproveOrRejectLead($lead, $user),
            'is_approved' => $this->securityService->isLeadApprovedForTasks($lead),
        ]);
    }

    /**
     * API: Create a new lead record.
     */
    public function apiStore(Request $request)
    {
        $this->ensureTenantConnection();
        $user = $this->resolveCurrentUser();
        $profile = $this->resolveCurrentUserProfile($user);
        $profilePermissions = $this->getProfilePermissions($profile, $user);
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);

        // Security check: Verify can_create permission
        if (!$profilePermissions['can_create']) {
            return response()->json([
                'success' => false,
                'message' => 'Access Denied: Your security profile does not have permission to create leads.',
            ], 403);
        }

        $validated = $request->validate([
            'first_name' => 'nullable|string|max:80',
            'last_name' => 'nullable|string|max:80',
            'email' => 'nullable|email|max:150',
            'phone' => 'nullable|string|max:30',
            'company' => 'nullable|string|max:150',
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
        $inputCustom = $request->input('custom_fields', $request->input('custom_data', []));
        $activeCustomFields = CustomField::on('tenant')->where('module', 'leads')->get();
        $customDataToSave = [];

        foreach ($activeCustomFields as $cf) {
            $fName = $cf->field_name;
            $perm = $fieldPermissions[$fName] ?? 'Read/Write';
            if ($perm === 'Hidden' || $perm === 'Read-Only') {
                continue;
            }

            if (array_key_exists($fName, $inputCustom)) {
                $customDataToSave[$fName] = $inputCustom[$fName];
            } elseif ($request->has($fName)) {
                $customDataToSave[$fName] = $request->input($fName);
            } elseif ($cf->default_value !== null) {
                $customDataToSave[$fName] = $cf->default_value;
            }
        }

        // Clean & prepare dynamic lookup fields (guarantees storing clean scalar integer ID)
        $customDataToSave = $this->dynamicFieldService->prepareCustomDataForSave($customDataToSave, 'leads');

        // Check active approval workflow in database
        $activeWorkflow = ApprovalProcess::on('tenant')
            ->where('module', 'leads')
            ->where('is_active', true)
            ->first();

        if ($activeWorkflow) {
            // Workflow rule active: initialize in Pending state
            $customDataToSave['approval_status'] = 'Pending';
            $customDataToSave['is_approved'] = 0;
            $customDataToSave['is_rejected'] = 0;
        }

        unset($validated['custom_fields']);
        $validated['custom_data'] = $customDataToSave;
        $validated['status'] = $validated['status'] ?? 'New';
        $validated['source'] = $validated['source'] ?? 'Website';
        $validated['score'] = $validated['score'] ?? 50;
        $validated['rating'] = $validated['rating'] ?? 'Warm';
        $validated['company'] = !empty($validated['company']) ? $validated['company'] : '[Confidential]';
        $validated['first_name'] = !empty($validated['first_name']) ? $validated['first_name'] : 'Lead';
        $validated['last_name'] = !empty($validated['last_name']) ? $validated['last_name'] : 'Contact';
        $validated['email'] = !empty($validated['email']) ? $validated['email'] : ('lead_' . uniqid() . '@confidential.local');
        $validated['created_by'] = $user?->id;
        if (!isset($validated['assigned_to']) || empty($validated['assigned_to'])) {
            $validated['assigned_to'] = $user?->id;
        }

        $lead = Lead::on('tenant')->create($validated);
        $lead->load('assignedUser');

        // If workflow rule active, ensure workflow instances & submit initial approval request
        if ($activeWorkflow) {
            $this->securityService->ensureLeadApprovalWorkflow();
            try {
                $this->documentService->submitApproval($lead, 'Initial lead qualification approval workflow initiated on creation.', $user);
            } catch (\Throwable $e) {}
        }

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
        $user = $this->resolveCurrentUser();
        $profile = $this->resolveCurrentUserProfile($user);
        $profilePermissions = $this->getProfilePermissions($profile, $user);
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);

        // Security check: Verify can_update permission
        if (!$profilePermissions['can_update']) {
            return response()->json([
                'success' => false,
                'message' => 'Access Denied: Your security profile does not have permission to update leads.',
            ], 403);
        }

        $lead = Lead::on('tenant')->findOrFail($id);

        // Security check: Verify role-based record scope and ownership
        if (!$this->userCanAccessLead($lead, 'update', $user, $profilePermissions)) {
            return response()->json([
                'success' => false,
                'message' => 'Access Denied: You do not have permission to update this lead record.',
            ], 403);
        }

        $allowedStandard = [
            'first_name', 'last_name', 'company', 'email', 'phone',
            'title', 'source', 'status', 'score', 'lead_value',
            'rating', 'notes', 'assigned_to', 'email_opt_out'
        ];

        $userId = $user?->id;
        $validUserId = null;
        if ($userId && DB::connection('tenant')->table('users')->where('id', $userId)->exists()) {
            $validUserId = $userId;
        }

        $userName = $user?->name ?? 'System Administrator';

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
                            'field_label' => $this->activityService->getFieldLabel($field),
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
        $inputCustom = $request->input('custom_fields', $request->input('custom_data', []));
        $activeCustomFields = CustomField::on('tenant')->where('module', 'leads')->get();

        foreach ($activeCustomFields as $cf) {
            $fName = $cf->field_name;
            $perm = $fieldPermissions[$fName] ?? 'Read/Write';
            if ($perm !== 'Read/Write') {
                continue;
            }

            // Security Governance: Approval state fields can only be updated by Manager or Administrator
            if (in_array($fName, ['is_approved', 'is_rejected', 'approval_status', 'approved_by', 'rejected_by', 'approval_notes'])) {
                if (!$this->securityService->userCanApproveOrRejectLead($lead, $user)) {
                    continue;
                }
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
                // If it's a lookup, clean the new value to scalar ID
                $prep = $this->dynamicFieldService->prepareCustomDataForSave([$fName => $newVal], 'leads');
                $newValClean = array_key_exists($fName, $prep) ? $prep[$fName] : $newVal;

                $oldVal = $existingCustom[$fName] ?? null;
                $oldDisplay = is_array($oldVal) ? json_encode($oldVal) : (string)($oldVal ?? '');
                $newDisplay = is_array($newValClean) ? json_encode($newValClean) : (string)($newValClean ?? '');

                // If lookup mapping exists, show friendly entity names in change history
                $mapping = $this->dynamicFieldService->resolveTargetMapping($cf, 'leads');
                if ($mapping) {
                    $oldName = $oldVal ? $this->dynamicFieldService->resolveDisplayValue($mapping['target_module'], $oldVal) : null;
                    $newName = $newValClean ? $this->dynamicFieldService->resolveDisplayValue($mapping['target_module'], $newValClean) : null;
                    if ($oldName) $oldDisplay = $oldName;
                    if ($newName) $newDisplay = $newName;
                }

                if ($oldDisplay !== $newDisplay) {
                    $historyEntries[] = [
                        'entity_type' => 'leads',
                        'entity_id' => (string)$lead->id,
                        'field_name' => $fName,
                        'field_label' => $cf->field_label ?: $this->activityService->getFieldLabel($fName),
                        'old_value' => $oldDisplay ?: null,
                        'new_value' => $newDisplay ?: null,
                        'changed_by_id' => $validUserId,
                        'changed_by_name' => $userName,
                        'created_at' => now(),
                    ];
                }
                $existingCustom[$fName] = $newValClean;
            }
        }
        $existingCustom = $this->dynamicFieldService->prepareCustomDataForSave($existingCustom, 'leads');
        $updates['custom_data'] = $existingCustom;
        $updates['updated_by'] = $validUserId;

        $lead->update($updates);
        $lead->load('assignedUser');

        if (!empty($historyEntries)) {
            $this->activityService->recordFieldChanges($historyEntries);
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
        $user = $this->resolveCurrentUser();
        $profile = $this->resolveCurrentUserProfile($user);
        $profilePermissions = $this->getProfilePermissions($profile, $user);

        // Security check: Verify can_delete permission
        if (!$profilePermissions['can_delete']) {
            return response()->json([
                'success' => false,
                'message' => 'Access Denied: Your security profile does not have permission to delete leads.',
            ], 403);
        }

        $lead = Lead::on('tenant')->findOrFail($id);

        // Security check: Verify role-based record scope and ownership
        if (!$this->userCanAccessLead($lead, 'delete', $user, $profilePermissions)) {
            return response()->json([
                'success' => false,
                'message' => 'Access Denied: You do not have permission to delete this lead record.',
            ], 403);
        }

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
        $user = $this->resolveCurrentUser();
        $profile = $this->resolveCurrentUserProfile($user);
        $profilePermissions = $this->getProfilePermissions($profile, $user);

        // Security check: Conversion modifies record, requires can_update
        if (!$profilePermissions['can_update']) {
            return response()->json([
                'success' => false,
                'message' => 'Access Denied: Your security profile does not have permission to convert leads.',
            ], 403);
        }

        $lead = Lead::on('tenant')->findOrFail($id);

        // Security check: Verify role-based record scope and ownership
        if (!$this->userCanAccessLead($lead, 'convert', $user, $profilePermissions)) {
            return response()->json([
                'success' => false,
                'message' => 'Access Denied: You do not have permission to convert this lead record.',
            ], 403);
        }

        if ($lead->is_converted) {
            return response()->json([
                'success' => false,
                'message' => 'Lead has already been converted.',
                'accountId' => (string)$lead->converted_account_id,
                'contactId' => (string)$lead->converted_contact_id,
            ], 422);
        }

        $conversionResult = $this->conversionService->convert($lead, $request, $user);

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
        $user = $this->resolveCurrentUser();
        $profile = $this->resolveCurrentUserProfile($user);
        $profilePermissions = $this->getProfilePermissions($profile, $user);

        // Security check: Verify can_transfer permission
        if (!$profilePermissions['can_transfer']) {
            return response()->json([
                'success' => false,
                'message' => 'Access Denied: Your security profile does not have permission to transfer lead ownership.',
            ], 403);
        }

        $lead = Lead::on('tenant')->findOrFail($id);

        // Security check: Verify role-based record scope and ownership
        if (!$this->userCanAccessLead($lead, 'transfer', $user, $profilePermissions)) {
            return response()->json([
                'success' => false,
                'message' => 'Access Denied: You do not have permission to transfer this lead record.',
            ], 403);
        }

        $targetUserId = (int)$request->input('targetUserId');
        $targetUser = TenantUser::on('tenant')->find($targetUserId);

        if (!$targetUser) {
            return response()->json([
                'success' => false,
                'message' => 'Specified target owner user was not found.',
            ], 404);
        }

        $updatedLead = $this->conversionService->transfer($lead, $targetUserId, $user);

        return response()->json([
            'success' => true,
            'message' => "Lead ownership successfully transferred to {$targetUser->name}.",
            'lead' => $updatedLead,
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
            TenantSetting::on('tenant')->updateOrCreate(
                ['key' => 'leads_table_col_widths'],
                ['value' => json_encode($widths)]
            );

            try {
                $layout = PageLayout::on('tenant')->where('module', 'leads')->where('is_default', true)->first();
                if ($layout) {
                    $json = $layout->layout_json ?: [];
                    $json['col_widths'] = $widths;
                    $layout->layout_json = $json;
                    $layout->save();
                }
            } catch (\Throwable $e) {}
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
            TenantSetting::on('tenant')->updateOrCreate(
                ['key' => $key],
                ['value' => json_encode(array_values($fields))]
            );

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
        $formatted = $this->activityService->getActivities($id);

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

        $type = strtolower($request->input('type', ''));
        if (in_array($type, ['task', 'to-do', 'todo']) && !$this->securityService->isLeadApprovedForTasks($lead)) {
            return response()->json([
                'success' => false,
                'message' => 'Action Locked: This lead is currently pending approval. Tasks cannot be performed until a Manager or Administrator approves the lead.',
            ], 422);
        }

        $request->validate([
            'subject' => 'required|string|max:200',
            'type' => 'nullable|string|max:40',
            'details' => 'nullable|string',
            'call_datetime' => 'nullable|string',
            'start_date' => 'nullable|string',
            'end_date' => 'nullable|string',
        ]);

        $user = $this->resolveCurrentUser();
        $result = $this->activityService->storeActivity($lead, $request->all(), $user);

        return response()->json($result);
    }

    /**
     * API: Store task specifically for a lead and log to activities timeline.
     */
    public function apiStoreTask(Request $request, $id)
    {
        $this->ensureTenantConnection();
        $lead = Lead::on('tenant')->findOrFail($id);

        // Workflow Governance Check: Tasks can only be performed if lead is approved
        if (!$this->securityService->isLeadApprovedForTasks($lead)) {
            return response()->json([
                'success' => false,
                'message' => 'Action Locked: This lead is currently pending approval. Tasks cannot be performed until a Manager or Administrator approves the lead.',
            ], 422);
        }

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

        $user = $this->resolveCurrentUser();
        $result = $this->activityService->storeTask($lead, $request->all(), $user);

        return response()->json($result);
    }

    /**
     * API: Get files linked to lead.
     */
    public function apiGetFiles($id)
    {
        $this->ensureTenantConnection();
        $files = $this->documentService->getFiles($id);

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
                'message' => 'No files were attached for upload.',
            ], 422);
        }

        $user = $this->resolveCurrentUser();
        $result = $this->documentService->uploadFiles(
            $lead,
            $uploadedFiles,
            $user,
            $request->input('category'),
            $request->input('description')
        );

        return response()->json($result);
    }

    /**
     * API: Delete file link and underlying file versions.
     */
    public function apiDeleteFile($id, $fileId)
    {
        $this->ensureTenantConnection();
        $lead = Lead::on('tenant')->findOrFail($id);
        $user = $this->resolveCurrentUser();

        $remainingCount = $this->documentService->deleteFile($lead, $fileId, $user);

        if ($remainingCount === null) {
            return response()->json([
                'success' => false,
                'message' => 'File not found on this lead.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'File deleted successfully.',
            'count' => $remainingCount,
        ]);
    }

    /**
     * API: Download file binary.
     */
    public function apiDownloadFile($id, $fileId)
    {
        $this->ensureTenantConnection();
        $lead = Lead::on('tenant')->findOrFail($id);

        return $this->documentService->downloadFile($lead, $fileId);
    }

    /**
     * API: Get approval requests for lead.
     */
    public function apiGetApprovals($id)
    {
        $this->ensureTenantConnection();
        $approvals = $this->documentService->getApprovals($id);

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
        $user = $this->resolveCurrentUser();

        $result = $this->documentService->submitApproval($lead, $request->input('notes'), $user);

        return response()->json($result);
    }

    /**
     * API: Perform action (approved/rejected) on approval request.
     */
    public function apiActionApproval(Request $request, $id, $requestId)
    {
        $this->ensureTenantConnection();
        $lead = Lead::on('tenant')->findOrFail($id);
        $user = $this->resolveCurrentUser();

        $request->validate([
            'action' => 'required|in:approved,rejected',
            'comments' => 'nullable|string',
        ]);

        $result = $this->documentService->actionApproval(
            $lead,
            $requestId,
            $request->input('action'),
            $request->input('comments'),
            $user
        );

        return response()->json($result);
    }

    /**
     * API: Manager or Administrator approval / rejection decision for a lead.
     */
    public function apiDecideApproval(Request $request, $id)
    {
        $this->ensureTenantConnection();
        $this->ensureCustomFields();

        $lead = Lead::on('tenant')->findOrFail($id);
        $user = $this->resolveCurrentUser();

        // 1. Authorization check: Only Manager or Administrator can approve or reject
        if (!$this->securityService->userCanApproveOrRejectLead($lead, $user)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Only an Administrator or Manager has permission to approve or reject leads.',
            ], 403);
        }

        // 2. Validate input
        $request->validate([
            'decision' => 'required|string|in:approve,reject,approved,rejected',
            'comments' => 'nullable|string|max:1000',
        ]);

        $rawDecision = strtolower($request->input('decision'));
        $isApproved = in_array($rawDecision, ['approve', 'approved']);
        $decision = $isApproved ? 'approved' : 'rejected';
        $comments = trim((string)$request->input('comments', ''));
        $userName = $user?->name ?? 'Administrator';

        // 3. Update dynamic custom fields in custom_data JSON
        $customData = is_array($lead->custom_data) ? $lead->custom_data : (json_decode($lead->custom_data ?? '[]', true) ?: []);

        $customData['is_approved'] = $isApproved ? 1 : 0;
        $customData['is_rejected'] = $isApproved ? 0 : 1;
        $customData['approval_status'] = $isApproved ? 'Approved' : 'Rejected';
        $customData['approval_notes'] = $comments ?: ($isApproved ? "Approved by {$userName}" : "Rejected by {$userName}");

        if ($isApproved) {
            $customData['approved_by'] = $user?->id;
            $customData['cf_is_approved'] = $user?->id;
            unset($customData['rejected_by']);
        } else {
            $customData['rejected_by'] = $user?->id;
            unset($customData['approved_by']);
            unset($customData['cf_is_approved']);
        }

        $lead->custom_data = $customData;
        $lead->save();

        // 4. Update approval_requests and log action
        try {
            $pendingReq = ApprovalRequest::on('tenant')
                ->where('module', 'leads')
                ->where('record_id', (string)$lead->id)
                ->where('status', 'pending')
                ->latest()
                ->first();

            if ($pendingReq) {
                $this->documentService->actionApproval($lead, $pendingReq->id, $decision, $comments, $user);
            } else {
                $sub = $this->documentService->submitApproval($lead, 'Direct decision submitted by ' . $userName, $user);
                if (!empty($sub['approval']['id'])) {
                    $this->documentService->actionApproval($lead, $sub['approval']['id'], $decision, $comments, $user);
                }
            }
        } catch (\Throwable $e) {
            // Silently continue if approval tables are not migrated
        }

        // 5. Log activity & audit trail
        try {
            Activity::on('tenant')->create([
                'entity_type' => 'leads',
                'entity_id' => $lead->id,
                'type' => 'status_change',
                'subject' => 'Lead ' . ucfirst($decision) . ' by ' . $userName,
                'details' => $comments ?: ('Governance decision: ' . ucfirst($decision)),
                'user_id' => $user?->id,
                'created_at' => now(),
            ]);

            AuditLog::on('tenant')->create([
                'entity_type' => 'leads',
                'entity_id' => $lead->id,
                'action' => 'approval_' . $decision,
                'actor_id' => $user?->id,
                'actor_name' => $userName,
                'details' => json_encode([
                    'decision' => $decision,
                    'comments' => $comments,
                    'timestamp' => now()->toIso8601String(),
                ]),
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {}

        $lead->load(['assignedUser', 'convertedAccount', 'convertedContact']);
        $profile = $this->resolveCurrentUserProfile($user);
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);

        return response()->json([
            'success' => true,
            'message' => "Lead successfully " . ($isApproved ? 'approved' : 'rejected') . "!",
            'decision' => $decision,
            'is_approved' => $isApproved,
            'is_rejected' => !$isApproved,
            'approval_status' => ucfirst($decision),
            'lead' => $this->sanitizeLead($lead, $fieldPermissions),
        ]);
    }

    /**
     * API: Get field history for a lead.
     */
    public function apiGetHistory($id)
    {
        $this->ensureTenantConnection();
        $history = $this->activityService->getFieldHistory($id);

        return response()->json([
            'success' => true,
            'count' => $history->count(),
            'history' => $history,
        ]);
    }

    /**
     * Format a file record for UI display (static helper proxy).
     */
    public static function formatFilePayload($file, $leadId): array
    {
        return app(LeadDocumentService::class)->formatFilePayload($file, $leadId);
    }

    /**
     * Format an approval request for UI display (static helper proxy).
     */
    public static function formatApprovalPayload($req): array
    {
        return app(LeadDocumentService::class)->formatApprovalPayload($req);
    }

    /**
     * Format a field history record for UI display (static helper proxy).
     */
    public static function formatHistoryPayload($h): array
    {
        return app(LeadActivityService::class)->formatHistoryPayload($h);
    }

    /**
     * Get a human-friendly field label (static helper proxy).
     */
    public static function getFieldLabel(string $fieldName): string
    {
        return app(LeadActivityService::class)->getFieldLabel($fieldName);
    }
}
