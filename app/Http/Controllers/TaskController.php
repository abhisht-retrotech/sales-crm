<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Task;
use App\Models\Account;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\Contact;
use App\Models\PurchaseDeal;
use App\Models\TenantUser;
use App\Models\CustomField;
use App\Models\FieldPermission;
use App\Models\Profile;
use App\Models\Tenant;

class TaskController extends Controller
{
    public const PRIORITIES = ['Low', 'Normal', 'High', 'Urgent'];
    public const STATUSES = ['Open', 'In Progress', 'Waiting on someone else', 'Deferred', 'Completed'];

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
     * Get field permissions map for the current user's profile on the tasks module.
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
            ->where('module', 'tasks')
            ->get();

        foreach ($records as $r) {
            $perms[$r->field_name] = $r->permission ?: 'Read/Write';
        }

        return $perms;
    }

    /**
     * Sanitize a task record respecting field-level security permissions.
     */
    protected function sanitizeTask(Task $task, array $fieldPermissions): array
    {
        $data = $task->toArray();
        $customData = is_array($task->custom_fields)
            ? $task->custom_fields
            : (json_decode($task->custom_fields ?? '[]', true) ?: []);

        $today = date('Y-m-d');
        $dueDateStr = $task->due_date ? date('Y-m-d', strtotime($task->due_date)) : null;

        // Computed helper attributes
        $data['assigned_to_name'] = $task->assignedUser?->name ?? ($task->assigned_to_name ?: 'Unassigned');
        $data['created_by_name'] = $task->creator?->name ?? ($task->created_by_name ?: 'System');
        $data['due_date'] = $dueDateStr;
        $data['due_date_formatted'] = $dueDateStr ? date('M d, Y', strtotime($dueDateStr)) : 'No due date';
        $data['is_overdue'] = (bool)($dueDateStr && $dueDateStr < $today && $task->status !== 'Completed');
        $data['is_due_today'] = (bool)($dueDateStr && $dueDateStr === $today);
        $data['created_at_formatted'] = $task->created_at ? date('M d, Y H:i', strtotime($task->created_at)) : '';

        // Apply permissions on standard attributes
        $protectedKeys = ['id', 'created_at', 'updated_at', 'assigned_to', 'created_by', 'updated_by', 'related_entity_id', 'related_entity_type'];
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

        // Apply permissions on dynamic custom fields in custom_fields
        $sanitizedCustom = [];
        foreach ($customData as $cfKey => $cfVal) {
            $perm = $fieldPermissions[$cfKey] ?? 'Read/Write';
            if ($perm !== 'Hidden') {
                $sanitizedCustom[$cfKey] = $cfVal;
                // Expose at root level for seamless table/kanban/filter access
                $data[$cfKey] = $cfVal;
            } else {
                $data['_hidden_' . $cfKey] = true;
            }
        }
        $data['custom_fields'] = $sanitizedCustom;

        return $data;
    }

    /**
     * Ensure default dynamic custom fields exist for the tasks module.
     */
    protected function ensureCustomFields()
    {
        try {
            if (CustomField::on('tenant')->where('module', 'tasks')->count() > 0) {
                return;
            }

            $defaults = [
                [
                    'module' => 'tasks',
                    'field_name' => 'cf_task_category',
                    'field_label' => 'Task Category',
                    'data_type' => 'picklist',
                    'options' => 'Client Meeting / QBR, Contract Follow-up, Product Demo, Technical Scoping, Billing & Wire Verification, Procurement Review, General Admin',
                    'is_required' => false,
                    'help_text' => 'Business function and operational classification',
                ],
                [
                    'module' => 'tasks',
                    'field_name' => 'cf_client_impact_level',
                    'field_label' => 'Client Impact Tier',
                    'data_type' => 'picklist',
                    'options' => 'Tier 1 (Critical SLA / Revenue At Risk), Tier 2 (Core Operational Deliverable), Tier 3 (Internal Housekeeping / Routine)',
                    'is_required' => false,
                    'help_text' => 'Business urgency and contract deliverable impact',
                ],
                [
                    'module' => 'tasks',
                    'field_name' => 'cf_estimated_effort_hours',
                    'field_label' => 'Estimated Effort (Hours)',
                    'data_type' => 'number',
                    'options' => null,
                    'is_required' => false,
                    'help_text' => 'Expected time in hours needed to execute this item',
                ],
                [
                    'module' => 'tasks',
                    'field_name' => 'cf_completion_blocker',
                    'field_label' => 'Blocker / Dependency',
                    'data_type' => 'text',
                    'options' => null,
                    'is_required' => false,
                    'help_text' => 'Current impediment or external stakeholder dependency',
                ],
                [
                    'module' => 'tasks',
                    'field_name' => 'cf_follow_up_channel',
                    'field_label' => 'Execution Channel',
                    'data_type' => 'picklist',
                    'options' => 'Executive Video Call, Direct Phone Call, Direct Email, Slack / In-App Message, In-Person Meeting',
                    'is_required' => false,
                    'help_text' => 'Primary communication medium for completing this action',
                ],
            ];

            foreach ($defaults as $cf) {
                CustomField::on('tenant')->updateOrCreate(
                    ['module' => 'tasks', 'field_name' => $cf['field_name']],
                    $cf
                );
            }
        } catch (\Throwable $e) {
            // Silently continue
        }
    }

    /**
     * Seed realistic initial enterprise tasks if database table is completely empty.
     */
    protected function ensureDemoTasks()
    {
        try {
            if (Task::on('tenant')->count() > 0) {
                return;
            }

            $firstUser = TenantUser::on('tenant')->first();
            $userId = $firstUser?->id;
            $userName = $firstUser?->name ?? 'Pramod Singh';

            $account = Account::on('tenant')->first();
            $opp = Opportunity::on('tenant')->first();
            $lead = Lead::on('tenant')->first();
            $contact = Contact::on('tenant')->first();

            $today = date('Y-m-d');
            $yesterday = date('Y-m-d', strtotime('-1 day'));
            $tomorrow = date('Y-m-d', strtotime('+1 day'));
            $nextWeek = date('Y-m-d', strtotime('+5 days'));

            $demoTasks = [
                [
                    'subject' => 'Executive Sign-off Review with CTO Elena Vance',
                    'priority' => 'Urgent',
                    'status' => 'Open',
                    'assigned_to' => $userId,
                    'assigned_to_name' => $userName,
                    'due_date' => $yesterday, // Overdue alert!
                    'comments' => 'Confirm multi-region security architecture addendum and 99.99% uptime SLA guarantee.',
                    'related_entity_type' => 'Opportunity',
                    'related_entity_id' => $opp?->id,
                    'related_entity_name' => $opp?->name ?? 'Enterprise Cloud Migration',
                    'reminder_set' => '15 minutes before',
                    'is_recurring' => false,
                    'recurrence_pattern' => null,
                    'custom_fields' => [
                        'cf_task_category' => 'Contract Follow-up',
                        'cf_client_impact_level' => 'Tier 1 (Critical SLA / Revenue At Risk)',
                        'cf_estimated_effort_hours' => 2,
                        'cf_completion_blocker' => 'Awaiting General Counsel legal signature stamp',
                        'cf_follow_up_channel' => 'Executive Video Call',
                    ],
                ],
                [
                    'subject' => 'Reconcile Inbound Wire Transfer for Vanguard Health Tech',
                    'priority' => 'High',
                    'status' => 'In Progress',
                    'assigned_to' => $userId,
                    'assigned_to_name' => $userName,
                    'due_date' => $today, // Today!
                    'comments' => 'Cross-verify federal wire UTR number with bank ledger settlement report before releasing delivery.',
                    'related_entity_type' => 'Account',
                    'related_entity_id' => $account?->id,
                    'related_entity_name' => $account?->name ?? 'Vanguard Health Tech',
                    'reminder_set' => '1 hour before',
                    'is_recurring' => false,
                    'recurrence_pattern' => null,
                    'custom_fields' => [
                        'cf_task_category' => 'Billing & Wire Verification',
                        'cf_client_impact_level' => 'Tier 1 (Critical SLA / Revenue At Risk)',
                        'cf_estimated_effort_hours' => 1,
                        'cf_completion_blocker' => 'Bank portal 2FA authentication token needed',
                        'cf_follow_up_channel' => 'Direct Phone Call',
                    ],
                ],
                [
                    'subject' => 'Weekly Commercial Architecture Standup',
                    'priority' => 'Normal',
                    'status' => 'Open',
                    'assigned_to' => $userId,
                    'assigned_to_name' => $userName,
                    'due_date' => $tomorrow,
                    'comments' => 'Review open pipeline milestones, cloud API latency benchmarks, and supplier logistics.',
                    'related_entity_type' => 'Lead',
                    'related_entity_id' => $lead?->id,
                    'related_entity_name' => $lead ? ($lead->first_name . ' ' . $lead->last_name) : 'Apex Global Lead',
                    'reminder_set' => '30 minutes before',
                    'is_recurring' => true,
                    'recurrence_pattern' => 'Weekly',
                    'custom_fields' => [
                        'cf_task_category' => 'Product Demo',
                        'cf_client_impact_level' => 'Tier 2 (Core Operational Deliverable)',
                        'cf_estimated_effort_hours' => 1,
                        'cf_completion_blocker' => 'None',
                        'cf_follow_up_channel' => 'Executive Video Call',
                    ],
                ],
                [
                    'subject' => 'Procurement PO Dispatch to Titan Steel Fabricators',
                    'priority' => 'Normal',
                    'status' => 'Waiting on someone else',
                    'assigned_to' => $userId,
                    'assigned_to_name' => $userName,
                    'due_date' => $nextWeek,
                    'comments' => 'Supplier freight quotation pending signed bill of lading from dock manager.',
                    'related_entity_type' => 'Contact',
                    'related_entity_id' => $contact?->id,
                    'related_entity_name' => $contact ? ($contact->first_name . ' ' . $contact->last_name) : 'Rachel Sterling',
                    'reminder_set' => 'No reminder is set',
                    'is_recurring' => false,
                    'recurrence_pattern' => null,
                    'custom_fields' => [
                        'cf_task_category' => 'Procurement Review',
                        'cf_client_impact_level' => 'Tier 2 (Core Operational Deliverable)',
                        'cf_estimated_effort_hours' => 3,
                        'cf_completion_blocker' => 'Pending freight dock manager rate card',
                        'cf_follow_up_channel' => 'Direct Email',
                    ],
                ],
                [
                    'subject' => 'Signed Master Service Agreement Archived to Vault',
                    'priority' => 'Low',
                    'status' => 'Completed',
                    'assigned_to' => $userId,
                    'assigned_to_name' => $userName,
                    'due_date' => date('Y-m-d', strtotime('-3 days')),
                    'comments' => 'Executed countersigned MSA securely indexed with cryptographic checksum verification.',
                    'related_entity_type' => 'Account',
                    'related_entity_id' => $account?->id,
                    'related_entity_name' => $account?->name ?? 'Skyline Cloud Systems',
                    'reminder_set' => 'No reminder is set',
                    'is_recurring' => false,
                    'recurrence_pattern' => null,
                    'custom_fields' => [
                        'cf_task_category' => 'General Admin',
                        'cf_client_impact_level' => 'Tier 3 (Internal Housekeeping / Routine)',
                        'cf_estimated_effort_hours' => 1,
                        'cf_completion_blocker' => 'None',
                        'cf_follow_up_channel' => 'Slack / In-App Message',
                    ],
                ],
            ];

            foreach ($demoTasks as $t) {
                Task::on('tenant')->create($t);
            }
        } catch (\Throwable $e) {
            // Silently continue
        }
    }

    /**
     * Compute task counts for the 6 filter tabs: all, open, pending, recurring, today, overdue.
     */
    protected function calculateTaskCounts(): array
    {
        $today = date('Y-m-d');
        $all = Task::on('tenant')->count();
        $open = Task::on('tenant')->where('status', '!=', 'Completed')->count();
        $pending = Task::on('tenant')->whereIn('status', ['Open', 'In Progress', 'Waiting on someone else', 'Deferred'])->count();
        $recurring = Task::on('tenant')->where('is_recurring', true)->count();
        $todayCount = Task::on('tenant')->whereDate('due_date', $today)->count();
        $overdue = Task::on('tenant')->where('due_date', '<', $today)->where('status', '!=', 'Completed')->count();

        return [
            'all' => $all,
            'open' => $open,
            'pending' => $pending,
            'recurring' => $recurring,
            'today' => $todayCount,
            'overdue' => $overdue,
        ];
    }

    /**
     * Display the Tasks page or return JSON.
     */
    public function index(Request $request)
    {
        $this->ensureTenantConnection();
        $this->ensureCustomFields();
        $this->ensureDemoTasks();

        $profile = $this->resolveCurrentUserProfile();
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);

        // Fetch dynamic custom fields for tasks
        $customFields = CustomField::on('tenant')
            ->where('module', 'tasks')
            ->orderBy('id', 'asc')
            ->get();

        $counts = $this->calculateTaskCounts();
        $today = date('Y-m-d');

        // Build query
        $query = Task::on('tenant')
            ->with(['assignedUser', 'creator'])
            ->orderByRaw("CASE WHEN status = 'Completed' THEN 1 ELSE 0 END ASC")
            ->orderByRaw("CASE WHEN due_date IS NOT NULL AND due_date < '{$today}' AND status != 'Completed' THEN 0 ELSE 1 END ASC")
            ->orderBy('due_date', 'asc')
            ->orderBy('id', 'desc');

        // Apply Tab Filter
        $tab = $request->input('filter', 'all');
        if ($tab === 'open') {
            $query->where('status', '!=', 'Completed');
        } elseif ($tab === 'pending') {
            $query->whereIn('status', ['Open', 'In Progress', 'Waiting on someone else', 'Deferred']);
        } elseif ($tab === 'recurring') {
            $query->where('is_recurring', true);
        } elseif ($tab === 'today') {
            $query->whereDate('due_date', $today);
        } elseif ($tab === 'overdue') {
            $query->where('due_date', '<', $today)->where('status', '!=', 'Completed');
        }

        // Text Search
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('subject', 'like', "%{$s}%")
                    ->orWhere('comments', 'like', "%{$s}%")
                    ->orWhere('assigned_to_name', 'like', "%{$s}%")
                    ->orWhere('related_entity_name', 'like', "%{$s}%")
                    ->orWhere('priority', 'like', "%{$s}%")
                    ->orWhere('status', 'like', "%{$s}%");
            });
        }

        if ($request->filled('priority') && $request->priority !== 'all') {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $rawTasks = $query->get();

        $sanitizedTasks = $rawTasks->map(function ($task) use ($fieldPermissions) {
            return $this->sanitizeTask($task, $fieldPermissions);
        })->values();

        // Dropdown references
        $users = TenantUser::on('tenant')->orderBy('name', 'asc')->get(['id', 'name', 'email']);
        $accounts = Account::on('tenant')->orderBy('name', 'asc')->get(['id', 'name']);
        $leads = Lead::on('tenant')->orderBy('id', 'desc')->get(['id', 'first_name', 'last_name', 'company']);
        $opportunities = Opportunity::on('tenant')->orderBy('id', 'desc')->get(['id', 'name']);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'tasks' => $sanitizedTasks,
                'counts' => $counts,
                'custom_fields' => $customFields,
                'field_permissions' => $fieldPermissions,
                'users' => $users,
                'accounts' => $accounts,
                'leads' => $leads,
                'opportunities' => $opportunities,
                'priorities' => self::PRIORITIES,
                'statuses' => self::STATUSES,
            ]);
        }

        return view('tasks', [
            'initialTasks' => $sanitizedTasks,
            'counts' => $counts,
            'customFields' => $customFields,
            'fieldPermissions' => $fieldPermissions,
            'users' => $users,
            'accounts' => $accounts,
            'leads' => $leads,
            'opportunities' => $opportunities,
            'priorities' => self::PRIORITIES,
            'statuses' => self::STATUSES,
            'currentUserProfile' => $profile ? $profile->name : 'Administrator',
        ]);
    }

    /**
     * Display the dedicated Task 360 view page.
     */
    public function show(Request $request, $id)
    {
        $this->ensureTenantConnection();
        $this->ensureCustomFields();

        $profile = $this->resolveCurrentUserProfile();
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);

        $task = Task::on('tenant')
            ->with(['assignedUser', 'creator'])
            ->findOrFail($id);

        $sanitizedTask = $this->sanitizeTask($task, $fieldPermissions);

        // Fetch custom fields for tasks
        $customFields = CustomField::on('tenant')
            ->where('module', 'tasks')
            ->orderBy('id', 'asc')
            ->get();

        $users = TenantUser::on('tenant')->orderBy('name', 'asc')->get(['id', 'name', 'email']);
        $counts = $this->calculateTaskCounts();

        if ($request->wantsJson()) {
            return response()->json([
                'task' => $sanitizedTask,
                'custom_fields' => $customFields,
                'field_permissions' => $fieldPermissions,
                'users' => $users,
                'counts' => $counts,
            ]);
        }

        return view('tasks.show', [
            'task' => $sanitizedTask,
            'rawTask' => $task,
            'customFields' => $customFields,
            'fieldPermissions' => $fieldPermissions,
            'users' => $users,
            'priorities' => self::PRIORITIES,
            'statuses' => self::STATUSES,
            'currentUserProfile' => $profile ? $profile->name : 'Administrator',
        ]);
    }

    /**
     * API: Get tasks list.
     */
    public function apiList(Request $request)
    {
        return $this->index($request);
    }

    /**
     * API: Get tasks metadata.
     */
    public function apiMeta(Request $request)
    {
        $this->ensureTenantConnection();
        $profile = $this->resolveCurrentUserProfile();
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);

        $customFields = CustomField::on('tenant')
            ->where('module', 'tasks')
            ->orderBy('id', 'asc')
            ->get();

        $users = TenantUser::on('tenant')->orderBy('name', 'asc')->get(['id', 'name', 'email']);
        $accounts = Account::on('tenant')->orderBy('name', 'asc')->get(['id', 'name']);
        $leads = Lead::on('tenant')->orderBy('id', 'desc')->get(['id', 'first_name', 'last_name', 'company']);
        $opportunities = Opportunity::on('tenant')->orderBy('id', 'desc')->get(['id', 'name']);

        return response()->json([
            'custom_fields' => $customFields,
            'field_permissions' => $fieldPermissions,
            'users' => $users,
            'accounts' => $accounts,
            'leads' => $leads,
            'opportunities' => $opportunities,
            'counts' => $this->calculateTaskCounts(),
            'priorities' => self::PRIORITIES,
            'statuses' => self::STATUSES,
        ]);
    }

    /**
     * API: Get single task.
     */
    public function apiShow(Request $request, $id)
    {
        $this->ensureTenantConnection();
        $profile = $this->resolveCurrentUserProfile();
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);

        $task = Task::on('tenant')->with(['assignedUser', 'creator'])->findOrFail($id);
        return response()->json($this->sanitizeTask($task, $fieldPermissions));
    }

    /**
     * API: Store a newly created task.
     */
    public function apiStore(Request $request)
    {
        $this->ensureTenantConnection();
        $profile = $this->resolveCurrentUserProfile();
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);

        $validated = $request->validate([
            'subject' => 'required|string|max:200',
            'priority' => 'nullable|string|max:30',
            'status' => 'nullable|string|max:40',
            'assigned_to' => 'nullable|integer',
            'assigned_to_name' => 'nullable|string|max:150',
            'due_date' => 'nullable|date',
            'comments' => 'nullable|string',
            'related_entity_type' => 'nullable|string|max:60',
            'related_entity_id' => 'nullable|integer',
            'related_entity_name' => 'nullable|string|max:150',
            'reminder_set' => 'nullable|string|max:100',
            'is_recurring' => 'nullable|boolean',
            'recurrence_pattern' => 'nullable|string|max:30',
            'custom_fields' => 'nullable|array',
        ]);

        // Security check: Ignore any field marked Hidden or Read-Only
        foreach ($validated as $k => $v) {
            $perm = $fieldPermissions[$k] ?? 'Read/Write';
            if ($perm === 'Hidden' || $perm === 'Read-Only') {
                unset($validated[$k]);
            }
        }

        // Link assignee name
        if (!empty($validated['assigned_to']) && empty($validated['assigned_to_name'])) {
            $u = TenantUser::on('tenant')->find($validated['assigned_to']);
            if ($u) {
                $validated['assigned_to_name'] = $u->name;
            }
        }

        // Process dynamic custom fields
        $inputCustom = $request->input('custom_fields', []);
        $activeCustomFields = CustomField::on('tenant')->where('module', 'tasks')->get();
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

        $validated['custom_fields'] = $customDataToSave;
        $validated['priority'] = $validated['priority'] ?? 'Normal';
        $validated['status'] = $validated['status'] ?? 'Open';
        $validated['reminder_set'] = $validated['reminder_set'] ?? 'No reminder is set';

        $task = Task::on('tenant')->create($validated);
        $task->load(['assignedUser', 'creator']);

        return response()->json([
            'success' => true,
            'message' => "Task '{$task->subject}' created successfully!",
            'task' => $this->sanitizeTask($task, $fieldPermissions),
            'counts' => $this->calculateTaskCounts(),
        ], 201);
    }

    /**
     * API: Update an existing task.
     */
    public function apiUpdate(Request $request, $id)
    {
        $this->ensureTenantConnection();
        $profile = $this->resolveCurrentUserProfile();
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);

        $task = Task::on('tenant')->findOrFail($id);

        $allowedStandard = [
            'subject', 'priority', 'status', 'assigned_to', 'assigned_to_name',
            'due_date', 'comments', 'related_entity_type', 'related_entity_id',
            'related_entity_name', 'reminder_set', 'is_recurring', 'recurrence_pattern'
        ];

        $updates = [];
        foreach ($allowedStandard as $field) {
            if ($request->has($field)) {
                $perm = $fieldPermissions[$field] ?? 'Read/Write';
                if ($perm === 'Read/Write') {
                    $updates[$field] = $request->input($field);
                }
            }
        }

        // Sync assignee name if assigned_to modified
        if (isset($updates['assigned_to'])) {
            $u = TenantUser::on('tenant')->find($updates['assigned_to']);
            $updates['assigned_to_name'] = $u ? $u->name : 'Unassigned';
        }

        // Process dynamic custom fields
        $existingCustom = is_array($task->custom_fields)
            ? $task->custom_fields
            : (json_decode($task->custom_fields ?? '[]', true) ?: []);

        $inputCustom = $request->input('custom_fields', []);
        $activeCustomFields = CustomField::on('tenant')->where('module', 'tasks')->get();

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

        $updates['custom_fields'] = $existingCustom;
        $task->update($updates);
        $task->load(['assignedUser', 'creator']);

        return response()->json([
            'success' => true,
            'message' => "Task '{$task->subject}' updated successfully!",
            'task' => $this->sanitizeTask($task, $fieldPermissions),
            'counts' => $this->calculateTaskCounts(),
        ]);
    }

    /**
     * API: Quick Toggle Status (e.g. Open <-> Completed).
     */
    public function apiUpdateStatus(Request $request, $id)
    {
        $this->ensureTenantConnection();
        $profile = $this->resolveCurrentUserProfile();
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);

        $task = Task::on('tenant')->findOrFail($id);

        $status = $request->input('status');
        if (!$status) {
            $status = ($task->status === 'Completed') ? 'Open' : 'Completed';
        }

        $task->status = $status;
        $task->save();
        $task->load(['assignedUser', 'creator']);

        return response()->json([
            'success' => true,
            'message' => "Task marked as '{$status}'",
            'task' => $this->sanitizeTask($task, $fieldPermissions),
            'counts' => $this->calculateTaskCounts(),
        ]);
    }

    /**
     * API: Delete a task.
     */
    public function apiDestroy(Request $request, $id)
    {
        $this->ensureTenantConnection();
        $task = Task::on('tenant')->findOrFail($id);
        $subject = $task->subject;
        $task->delete();

        return response()->json([
            'success' => true,
            'message' => "Task '{$subject}' deleted successfully.",
            'counts' => $this->calculateTaskCounts(),
        ]);
    }

    /**
     * API: Transfer task assignee.
     */
    public function apiTransfer(Request $request, $id)
    {
        $this->ensureTenantConnection();
        $validated = $request->validate([
            'assigned_to' => 'required|integer',
        ]);

        $task = Task::on('tenant')->findOrFail($id);
        $user = TenantUser::on('tenant')->findOrFail($validated['assigned_to']);

        $task->assigned_to = $user->id;
        $task->assigned_to_name = $user->name;
        $task->save();
        $task->load(['assignedUser', 'creator']);

        $profile = $this->resolveCurrentUserProfile();
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);

        return response()->json([
            'success' => true,
            'message' => "Task reassigned to '{$user->name}'",
            'task' => $this->sanitizeTask($task, $fieldPermissions),
            'counts' => $this->calculateTaskCounts(),
        ]);
    }

    /**
     * API: Task 360 Degree View Data.
     */
    public function api360(Request $request, $id)
    {
        return $this->show($request, $id);
    }
}
