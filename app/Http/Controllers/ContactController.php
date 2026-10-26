<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Contact;
use App\Models\CustomField;
use App\Models\TenantUser;
use App\Services\Contacts\ContactActivityService;
use App\Services\Contacts\ContactSecurityService;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    protected ContactSecurityService $securityService;
    protected ContactActivityService $activityService;

    public function __construct(
        ContactSecurityService $securityService,
        ContactActivityService $activityService
    ) {
        $this->securityService = $securityService;
        $this->activityService = $activityService;
    }

    /**
     * Backward-compatible static proxy for formatting file payload.
     */
    public static function formatFilePayload($file, $contactId): array
    {
        return (new ContactActivityService())->formatFilePayload($file, $contactId);
    }

    /**
     * Backward-compatible static proxy for formatting activity payload.
     */
    public static function formatActivityPayload($act): array
    {
        return (new ContactActivityService())->formatActivityPayload($act);
    }

    /**
     * Display the Contacts Directory page or return JSON.
     */
    public function index(Request $request)
    {
        $this->securityService->ensureTenantConnection();
        $this->securityService->ensureCustomFields();
        $this->securityService->ensureDemoContacts();

        $user = $this->securityService->resolveCurrentUser();
        $profile = $this->securityService->resolveCurrentUserProfile($user);
        $profilePermissions = $this->securityService->getProfilePermissions($profile, $user);
        $fieldPermissions = $this->securityService->getFieldPermissionsForProfile($profile);

        if (empty($profilePermissions['can_read'])) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'error' => 'Access denied',
                    'message' => 'You do not have permission to view contacts',
                ], 403);
            }
            abort(403, 'You do not have permission to view contacts.');
        }

        // Fetch custom fields for contacts
        $customFields = CustomField::on('tenant')
            ->where('module', 'contacts')
            ->orderBy('id', 'asc')
            ->get();

        // Build query with security scope
        $query = Contact::on('tenant')
            ->with(['account', 'assignedUser'])
            ->orderBy('id', 'desc');

        $query = $this->securityService->applyContactRecordScopeQuery($query, $user, $profilePermissions);

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('first_name', 'like', "%{$s}%")
                    ->orWhere('last_name', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%")
                    ->orWhere('phone', 'like', "%{$s}%")
                    ->orWhere('title', 'like', "%{$s}%")
                    ->orWhere('department', 'like', "%{$s}%")
                    ->orWhere('account_name', 'like', "%{$s}%");
            });
        }

        if ($request->filled('role') && $request->role !== 'all') {
            $query->where('contact_role', $request->role);
        }

        $rawContacts = $query->get();
        $sanitizedContacts = $this->securityService->sanitizeCollection($rawContacts, $fieldPermissions);

        // Fetch active accounts and users for dropdowns
        $accounts = Account::on('tenant')->orderBy('name', 'asc')->get(['id', 'name']);
        $users = TenantUser::on('tenant')->orderBy('name', 'asc')->get(['id', 'name', 'email']);

        $roles = ['Decision Maker', 'Champion', 'Influencer', 'Evaluator'];
        $canSendBulkEmail = (bool)($profile?->can_send_bulk_email ?? true);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'contacts' => $sanitizedContacts,
                'custom_fields' => $customFields,
                'field_permissions' => $fieldPermissions,
                'profile_permissions' => $profilePermissions,
                'accounts' => $accounts,
                'users' => $users,
                'roles' => $roles,
                'can_send_bulk_email' => $canSendBulkEmail,
            ]);
        }

        return view('contacts', [
            'initialContacts' => $sanitizedContacts,
            'customFields' => $customFields,
            'fieldPermissions' => $fieldPermissions,
            'profilePermissions' => $profilePermissions,
            'accounts' => $accounts,
            'users' => $users,
            'roles' => $roles,
            'canSendBulkEmail' => $canSendBulkEmail,
            'currentUserProfile' => $profile ? $profile->name : 'Administrator',
        ]);
    }

    /**
     * Display the dedicated Contact 360 view page.
     */
    public function show(Request $request, $id)
    {
        $this->securityService->ensureTenantConnection();
        $this->securityService->ensureCustomFields();

        $user = $this->securityService->resolveCurrentUser();
        $profile = $this->securityService->resolveCurrentUserProfile($user);
        $profilePermissions = $this->securityService->getProfilePermissions($profile, $user);
        $fieldPermissions = $this->securityService->getFieldPermissionsForProfile($profile);

        if (empty($profilePermissions['can_read'])) {
            if ($request->wantsJson()) {
                return response()->json([
                    'error' => 'Access denied',
                    'message' => 'You do not have permission to view contacts',
                ], 403);
            }
            abort(403, 'You do not have permission to view contacts.');
        }

        $contact = Contact::on('tenant')
            ->with(['account', 'assignedUser', 'opportunities', 'purchaseDeals'])
            ->findOrFail($id);

        if (!$this->securityService->userCanAccessContact($contact, 'read', $user, $profilePermissions)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'error' => 'Access denied',
                    'message' => 'You do not have access to this contact record.',
                ], 403);
            }
            abort(403, 'You do not have access to this contact record.');
        }

        $sanitizedContact = $this->securityService->sanitizeContact($contact, $fieldPermissions);

        // Fetch custom fields for contacts
        $customFields = CustomField::on('tenant')
            ->where('module', 'contacts')
            ->orderBy('id', 'asc')
            ->get();

        $accounts = Account::on('tenant')->orderBy('name', 'asc')->get(['id', 'name']);
        $users = TenantUser::on('tenant')->orderBy('name', 'asc')->get(['id', 'name', 'email']);
        $roles = ['Decision Maker', 'Champion', 'Influencer', 'Evaluator'];

        $relations = $this->activityService->getContact360Data($contact, $fieldPermissions);

        if ($request->wantsJson()) {
            return response()->json(array_merge([
                'contact' => $sanitizedContact,
                'custom_fields' => $customFields,
                'field_permissions' => $fieldPermissions,
                'profile_permissions' => $profilePermissions,
                'accounts' => $accounts,
                'users' => $users,
                'roles' => $roles,
            ], $relations));
        }

        return view('contacts.show', array_merge([
            'contact' => $sanitizedContact,
            'rawContact' => $contact,
            'customFields' => $customFields,
            'fieldPermissions' => $fieldPermissions,
            'profilePermissions' => $profilePermissions,
            'accounts' => $accounts,
            'users' => $users,
            'roles' => $roles,
            'currentUserProfile' => $profile ? $profile->name : 'Administrator',
        ], $relations));
    }

    /**
     * API: Get filtered contacts list.
     */
    public function apiList(Request $request)
    {
        $this->securityService->ensureTenantConnection();
        $user = $this->securityService->resolveCurrentUser();
        $profile = $this->securityService->resolveCurrentUserProfile($user);
        $profilePermissions = $this->securityService->getProfilePermissions($profile, $user);
        $fieldPermissions = $this->securityService->getFieldPermissionsForProfile($profile);

        if (empty($profilePermissions['can_read'])) {
            return response()->json([
                'error' => 'Access denied',
                'message' => 'You do not have permission to view contacts',
            ], 403);
        }

        $query = Contact::on('tenant')
            ->with(['account', 'assignedUser'])
            ->orderBy('id', 'desc');

        $query = $this->securityService->applyContactRecordScopeQuery($query, $user, $profilePermissions);

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('first_name', 'like', "%{$s}%")
                    ->orWhere('last_name', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%")
                    ->orWhere('phone', 'like', "%{$s}%")
                    ->orWhere('title', 'like', "%{$s}%")
                    ->orWhere('account_name', 'like', "%{$s}%");
            });
        }

        if ($request->filled('role') && $request->role !== 'all') {
            $query->where('contact_role', $request->role);
        }

        $rawContacts = $query->get();
        $contacts = $this->securityService->sanitizeCollection($rawContacts, $fieldPermissions);

        return response()->json($contacts);
    }

    /**
     * API: Get contacts metadata.
     */
    public function apiMeta(Request $request)
    {
        $this->securityService->ensureTenantConnection();
        $user = $this->securityService->resolveCurrentUser();
        $profile = $this->securityService->resolveCurrentUserProfile($user);
        $profilePermissions = $this->securityService->getProfilePermissions($profile, $user);
        $fieldPermissions = $this->securityService->getFieldPermissionsForProfile($profile);

        if (empty($profilePermissions['can_read'])) {
            return response()->json([
                'error' => 'Access denied',
                'message' => 'You do not have permission to view contacts',
            ], 403);
        }

        $customFields = CustomField::on('tenant')
            ->where('module', 'contacts')
            ->orderBy('id', 'asc')
            ->get();

        $accounts = Account::on('tenant')->orderBy('name', 'asc')->get(['id', 'name']);
        $users = TenantUser::on('tenant')->orderBy('name', 'asc')->get(['id', 'name', 'email']);

        return response()->json([
            'custom_fields' => $customFields,
            'field_permissions' => $fieldPermissions,
            'profile_permissions' => $profilePermissions,
            'accounts' => $accounts,
            'users' => $users,
            'roles' => ['Decision Maker', 'Champion', 'Influencer', 'Evaluator'],
            'can_send_bulk_email' => (bool)($profile?->can_send_bulk_email ?? true),
        ]);
    }

    /**
     * API: Get single contact.
     */
    public function apiShow(Request $request, $id)
    {
        $this->securityService->ensureTenantConnection();
        $user = $this->securityService->resolveCurrentUser();
        $profile = $this->securityService->resolveCurrentUserProfile($user);
        $profilePermissions = $this->securityService->getProfilePermissions($profile, $user);
        $fieldPermissions = $this->securityService->getFieldPermissionsForProfile($profile);

        if (empty($profilePermissions['can_read'])) {
            return response()->json([
                'error' => 'Access denied',
                'message' => 'You do not have permission to view contacts',
            ], 403);
        }

        $contact = Contact::on('tenant')->with(['account', 'assignedUser'])->findOrFail($id);

        if (!$this->securityService->userCanAccessContact($contact, 'read', $user, $profilePermissions)) {
            return response()->json([
                'error' => 'Access denied',
                'message' => 'You do not have access to this contact record.',
            ], 403);
        }

        return response()->json($this->securityService->sanitizeContact($contact, $fieldPermissions));
    }

    /**
     * API: Store a newly created contact.
     */
    public function apiStore(Request $request)
    {
        $this->securityService->ensureTenantConnection();
        $user = $this->securityService->resolveCurrentUser();
        $profile = $this->securityService->resolveCurrentUserProfile($user);
        $profilePermissions = $this->securityService->getProfilePermissions($profile, $user);
        $fieldPermissions = $this->securityService->getFieldPermissionsForProfile($profile);

        if (empty($profilePermissions['can_create'])) {
            return response()->json([
                'error' => 'Access denied',
                'message' => 'You do not have permission to create contacts',
            ], 403);
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:80',
            'last_name' => 'required|string|max:80',
            'email' => 'required|email|max:150',
            'phone' => 'nullable|string|max:30',
            'title' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'account_id' => 'nullable|integer',
            'account_name' => 'nullable|string|max:150',
            'contact_role' => 'nullable|string|max:60',
            'is_primary' => 'nullable|boolean',
            'email_opt_out' => 'nullable|boolean',
            'assigned_to' => 'nullable|integer',
            'notes' => 'nullable|string',
            'custom_fields' => 'nullable|array',
        ]);

        // Security check: Ignore any field marked Hidden or Read-Only
        foreach ($validated as $k => $v) {
            $perm = $fieldPermissions[$k] ?? 'Read/Write';
            if ($perm === 'Hidden' || $perm === 'Read-Only') {
                unset($validated[$k]);
            }
        }

        // Link account name if account_id is present
        if (!empty($validated['account_id'])) {
            $account = Account::on('tenant')->find($validated['account_id']);
            if ($account) {
                $validated['account_name'] = $account->name;
            }
        }

        // Process dynamic custom fields
        $inputCustom = $request->input('custom_fields', []);
        $activeCustomFields = CustomField::on('tenant')->where('module', 'contacts')->get();
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

        $validated['custom_data'] = $customDataToSave;
        $validated['contact_role'] = $validated['contact_role'] ?? 'Decision Maker';

        if (empty($validated['assigned_to']) && $user) {
            $validated['assigned_to'] = $user->id;
        }
        if ($user) {
            $validated['created_by'] = $user->id;
        }

        $contact = Contact::on('tenant')->create($validated);
        $contact->load(['account', 'assignedUser']);

        return response()->json([
            'success' => true,
            'message' => "Contact '{$contact->first_name} {$contact->last_name}' created successfully!",
            'contact' => $this->securityService->sanitizeContact($contact, $fieldPermissions),
        ], 201);
    }

    /**
     * API: Update an existing contact.
     */
    public function apiUpdate(Request $request, $id)
    {
        $this->securityService->ensureTenantConnection();
        $user = $this->securityService->resolveCurrentUser();
        $profile = $this->securityService->resolveCurrentUserProfile($user);
        $profilePermissions = $this->securityService->getProfilePermissions($profile, $user);
        $fieldPermissions = $this->securityService->getFieldPermissionsForProfile($profile);

        if (empty($profilePermissions['can_update'])) {
            return response()->json([
                'error' => 'Access denied',
                'message' => 'You do not have permission to edit contacts',
            ], 403);
        }

        $contact = Contact::on('tenant')->findOrFail($id);

        if (!$this->securityService->userCanAccessContact($contact, 'update', $user, $profilePermissions)) {
            return response()->json([
                'error' => 'Access denied',
                'message' => 'You do not have permission to edit this contact record.',
            ], 403);
        }

        $allowedStandard = [
            'first_name', 'last_name', 'email', 'phone', 'title',
            'department', 'account_id', 'account_name', 'contact_role',
            'is_primary', 'email_opt_out', 'assigned_to', 'notes'
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

        // Auto-update account name if account_id modified
        if (isset($updates['account_id'])) {
            $acc = Account::on('tenant')->find($updates['account_id']);
            $updates['account_name'] = $acc ? $acc->name : null;
        }

        // Process dynamic custom fields
        $existingCustom = is_array($contact->custom_data) ? $contact->custom_data : (json_decode($contact->custom_data ?? '[]', true) ?: []);
        $inputCustom = $request->input('custom_fields', []);
        $activeCustomFields = CustomField::on('tenant')->where('module', 'contacts')->get();

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
        if ($user) {
            $updates['updated_by'] = $user->id;
        }

        $contact->update($updates);
        $contact->load(['account', 'assignedUser']);

        return response()->json([
            'success' => true,
            'message' => "Contact '{$contact->first_name} {$contact->last_name}' updated successfully!",
            'contact' => $this->securityService->sanitizeContact($contact, $fieldPermissions),
        ]);
    }

    /**
     * API: Delete a contact.
     */
    public function apiDestroy(Request $request, $id)
    {
        $this->securityService->ensureTenantConnection();
        $user = $this->securityService->resolveCurrentUser();
        $profile = $this->securityService->resolveCurrentUserProfile($user);
        $profilePermissions = $this->securityService->getProfilePermissions($profile, $user);

        if (empty($profilePermissions['can_delete'])) {
            return response()->json([
                'error' => 'Access denied',
                'message' => 'You do not have permission to delete contacts',
            ], 403);
        }

        $contact = Contact::on('tenant')->findOrFail($id);

        if (!$this->securityService->userCanAccessContact($contact, 'delete', $user, $profilePermissions)) {
            return response()->json([
                'error' => 'Access denied',
                'message' => 'You do not have permission to delete this contact record.',
            ], 403);
        }

        $name = trim($contact->first_name . ' ' . $contact->last_name);
        $contact->delete();

        return response()->json([
            'success' => true,
            'message' => "Contact '{$name}' deleted successfully.",
        ]);
    }

    /**
     * API: Transfer contact owner.
     */
    public function apiTransfer(Request $request, $id)
    {
        $this->securityService->ensureTenantConnection();
        $user = $this->securityService->resolveCurrentUser();
        $profile = $this->securityService->resolveCurrentUserProfile($user);
        $profilePermissions = $this->securityService->getProfilePermissions($profile, $user);

        if (empty($profilePermissions['can_transfer'])) {
            return response()->json([
                'error' => 'Access denied',
                'message' => 'You do not have permission to transfer contact ownership',
            ], 403);
        }

        $contact = Contact::on('tenant')->findOrFail($id);

        if (!$this->securityService->userCanAccessContact($contact, 'transfer', $user, $profilePermissions)) {
            return response()->json([
                'error' => 'Access denied',
                'message' => 'You do not have permission to transfer this contact record.',
            ], 403);
        }

        $validated = $request->validate([
            'assigned_to' => 'required|integer',
        ]);

        $targetUser = TenantUser::on('tenant')->findOrFail($validated['assigned_to']);

        $contact->assigned_to = $targetUser->id;
        if ($user) {
            $contact->updated_by = $user->id;
        }
        $contact->save();
        $contact->load('assignedUser');

        $fieldPermissions = $this->securityService->getFieldPermissionsForProfile($profile);

        return response()->json([
            'success' => true,
            'message' => "Contact reassigned to '{$targetUser->name}'",
            'contact' => $this->securityService->sanitizeContact($contact, $fieldPermissions),
        ]);
    }

    /**
     * API: Get files linked to contact.
     */
    public function apiGetFiles(Request $request, $id)
    {
        $this->securityService->ensureTenantConnection();
        $user = $this->securityService->resolveCurrentUser();
        $profile = $this->securityService->resolveCurrentUserProfile($user);
        $profilePermissions = $this->securityService->getProfilePermissions($profile, $user);

        if (empty($profilePermissions['can_read'])) {
            return response()->json(['error' => 'Access denied', 'message' => 'You do not have permission to view contacts'], 403);
        }

        $contact = Contact::on('tenant')->findOrFail($id);

        if (!$this->securityService->userCanAccessContact($contact, 'read', $user, $profilePermissions)) {
            return response()->json(['error' => 'Access denied', 'message' => 'You do not have access to this contact record.'], 403);
        }

        $files = $this->activityService->getFiles($contact->id);

        return response()->json([
            'success' => true,
            'count' => $files->count(),
            'files' => $files,
        ]);
    }

    /**
     * API: Upload file(s) for a contact.
     */
    public function apiUploadFiles(Request $request, $id)
    {
        $this->securityService->ensureTenantConnection();
        $user = $this->securityService->resolveCurrentUser();
        $profile = $this->securityService->resolveCurrentUserProfile($user);
        $profilePermissions = $this->securityService->getProfilePermissions($profile, $user);

        if (empty($profilePermissions['can_update'])) {
            return response()->json(['error' => 'Access denied', 'message' => 'You do not have permission to edit contacts'], 403);
        }

        $contact = Contact::on('tenant')->findOrFail($id);

        if (!$this->securityService->userCanAccessContact($contact, 'update', $user, $profilePermissions)) {
            return response()->json(['error' => 'Access denied', 'message' => 'You do not have permission to edit this contact record.'], 403);
        }

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

        $result = $this->activityService->uploadFiles($contact, $uploadedFiles, $user, $request->input('description'));

        return response()->json($result);
    }

    /**
     * API: Delete a file linked to a contact.
     */
    public function apiDeleteFile(Request $request, $id, $fileId)
    {
        $this->securityService->ensureTenantConnection();
        $user = $this->securityService->resolveCurrentUser();
        $profile = $this->securityService->resolveCurrentUserProfile($user);
        $profilePermissions = $this->securityService->getProfilePermissions($profile, $user);

        if (empty($profilePermissions['can_update'])) {
            return response()->json(['error' => 'Access denied', 'message' => 'You do not have permission to edit contacts'], 403);
        }

        $contact = Contact::on('tenant')->findOrFail($id);

        if (!$this->securityService->userCanAccessContact($contact, 'update', $user, $profilePermissions)) {
            return response()->json(['error' => 'Access denied', 'message' => 'You do not have permission to edit this contact record.'], 403);
        }

        $remainingCount = $this->activityService->deleteFile($contact, $fileId, $user);
        if ($remainingCount === null) {
            return response()->json([
                'success' => false,
                'message' => 'File not found on this contact.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'File deleted successfully.',
            'count' => $remainingCount,
        ]);
    }

    /**
     * API: Download file stream.
     */
    public function apiDownloadFile(Request $request, $id, $fileId)
    {
        $this->securityService->ensureTenantConnection();
        $user = $this->securityService->resolveCurrentUser();
        $profile = $this->securityService->resolveCurrentUserProfile($user);
        $profilePermissions = $this->securityService->getProfilePermissions($profile, $user);

        if (empty($profilePermissions['can_read'])) {
            abort(403, 'You do not have permission to view contacts.');
        }

        $contact = Contact::on('tenant')->findOrFail($id);

        if (!$this->securityService->userCanAccessContact($contact, 'read', $user, $profilePermissions)) {
            abort(403, 'You do not have access to this contact record.');
        }

        return $this->activityService->downloadFile($contact, $fileId);
    }

    /**
     * API: Get activities for a contact (Timeline).
     */
    public function apiGetActivities(Request $request, $id)
    {
        $this->securityService->ensureTenantConnection();
        $user = $this->securityService->resolveCurrentUser();
        $profile = $this->securityService->resolveCurrentUserProfile($user);
        $profilePermissions = $this->securityService->getProfilePermissions($profile, $user);

        if (empty($profilePermissions['can_read'])) {
            return response()->json(['error' => 'Access denied', 'message' => 'You do not have permission to view contacts'], 403);
        }

        $contact = Contact::on('tenant')->findOrFail($id);

        if (!$this->securityService->userCanAccessContact($contact, 'read', $user, $profilePermissions)) {
            return response()->json(['error' => 'Access denied', 'message' => 'You do not have access to this contact record.'], 403);
        }

        $formatted = $this->activityService->getActivities($contact->id);

        return response()->json([
            'success' => true,
            'count' => $formatted->count(),
            'activities' => $formatted,
        ]);
    }

    /**
     * API: Store activity (Call, Event, Note) for a contact.
     */
    public function apiStoreActivity(Request $request, $id)
    {
        $this->securityService->ensureTenantConnection();
        $user = $this->securityService->resolveCurrentUser();
        $profile = $this->securityService->resolveCurrentUserProfile($user);
        $profilePermissions = $this->securityService->getProfilePermissions($profile, $user);

        if (empty($profilePermissions['can_update'])) {
            return response()->json(['error' => 'Access denied', 'message' => 'You do not have permission to edit contacts'], 403);
        }

        $contact = Contact::on('tenant')->findOrFail($id);

        if (!$this->securityService->userCanAccessContact($contact, 'update', $user, $profilePermissions)) {
            return response()->json(['error' => 'Access denied', 'message' => 'You do not have permission to edit this contact record.'], 403);
        }

        $request->validate([
            'subject' => 'required|string|max:200',
            'type' => 'nullable|string|max:40',
            'details' => 'nullable|string',
            'call_datetime' => 'nullable|string',
            'start_date' => 'nullable|string',
            'end_date' => 'nullable|string',
        ]);

        $result = $this->activityService->storeActivity($contact, $request->all(), $user);

        return response()->json($result);
    }

    /**
     * API: Store task specifically for a contact and log to activities timeline.
     */
    public function apiStoreTask(Request $request, $id)
    {
        $this->securityService->ensureTenantConnection();
        $user = $this->securityService->resolveCurrentUser();
        $profile = $this->securityService->resolveCurrentUserProfile($user);
        $profilePermissions = $this->securityService->getProfilePermissions($profile, $user);

        if (empty($profilePermissions['can_update'])) {
            return response()->json(['error' => 'Access denied', 'message' => 'You do not have permission to edit contacts'], 403);
        }

        $contact = Contact::on('tenant')->findOrFail($id);

        if (!$this->securityService->userCanAccessContact($contact, 'update', $user, $profilePermissions)) {
            return response()->json(['error' => 'Access denied', 'message' => 'You do not have permission to edit this contact record.'], 403);
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

        $result = $this->activityService->storeTask($contact, $request->all(), $user);

        return response()->json($result);
    }

    /**
     * API: Contact 360 Degree View Data.
     */
    public function api360(Request $request, $id)
    {
        $this->securityService->ensureTenantConnection();
        $user = $this->securityService->resolveCurrentUser();
        $profile = $this->securityService->resolveCurrentUserProfile($user);
        $profilePermissions = $this->securityService->getProfilePermissions($profile, $user);
        $fieldPermissions = $this->securityService->getFieldPermissionsForProfile($profile);

        if (empty($profilePermissions['can_read'])) {
            return response()->json(['error' => 'Access denied', 'message' => 'You do not have permission to view contacts'], 403);
        }

        $contact = Contact::on('tenant')
            ->with(['account', 'assignedUser'])
            ->findOrFail($id);

        if (!$this->securityService->userCanAccessContact($contact, 'read', $user, $profilePermissions)) {
            return response()->json(['error' => 'Access denied', 'message' => 'You do not have access to this contact record.'], 403);
        }

        $sanitizedContact = $this->securityService->sanitizeContact($contact, $fieldPermissions);
        $relations = $this->activityService->getContact360Data($contact, $fieldPermissions);

        return response()->json(array_merge([
            'contact' => $sanitizedContact,
            'account' => $contact->account,
        ], $relations));
    }
}
