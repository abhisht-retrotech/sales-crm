<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\CustomField;
use App\Models\TenantUser;
use App\Services\Accounts\AccountActivityService;
use App\Services\Accounts\AccountSecurityService;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    protected AccountSecurityService $securityService;
    protected AccountActivityService $activityService;

    public function __construct(
        AccountSecurityService $securityService,
        AccountActivityService $activityService
    ) {
        $this->securityService = $securityService;
        $this->activityService = $activityService;
    }

    /**
     * Backward-compatible static proxy for formatting file payload.
     */
    public static function formatFilePayload($file, $accountId): array
    {
        return (new AccountActivityService())->formatFilePayload($file, $accountId);
    }

    /**
     * Backward-compatible static proxy for formatting activity payload.
     */
    public static function formatActivityPayload($act): array
    {
        return (new AccountActivityService())->formatActivityPayload($act);
    }

    /**
     * Display the Accounts Directory page or return JSON.
     */
    public function index(Request $request)
    {
        $this->securityService->ensureTenantConnection();
        $this->securityService->ensureCustomFields();
        $this->securityService->ensureDemoAccounts();

        $user = $this->securityService->resolveCurrentUser();
        $profile = $this->securityService->resolveCurrentUserProfile($user);
        $profilePermissions = $this->securityService->getProfilePermissions($profile, $user);
        $fieldPermissions = $this->securityService->getFieldPermissionsForProfile($profile);

        if (empty($profilePermissions['can_read'])) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'error' => 'Access denied',
                    'message' => 'You do not have permission to view accounts',
                ], 403);
            }
            abort(403, 'You do not have permission to view accounts.');
        }

        // Fetch custom fields for accounts
        $customFields = CustomField::on('tenant')
            ->where('module', 'accounts')
            ->orderBy('id', 'asc')
            ->get();

        // Build query with security scope
        $query = Account::on('tenant')
            ->with(['owner', 'contacts', 'opportunities', 'purchaseDeals', 'shipments', 'payments'])
            ->orderBy('id', 'asc');

        $query = $this->securityService->applyAccountRecordScopeQuery($query, $user, $profilePermissions);

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('industry', 'like', "%{$s}%")
                    ->orWhere('website', 'like', "%{$s}%")
                    ->orWhere('phone', 'like', "%{$s}%")
                    ->orWhere('assigned_to', 'like', "%{$s}%");
            });
        }

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        $rawAccounts = $query->get();
        $sanitizedAccounts = $this->securityService->sanitizeCollection($rawAccounts, $fieldPermissions);

        // Fetch active tenant users for assignment
        $users = TenantUser::on('tenant')->orderBy('name', 'asc')->get(['id', 'name', 'email']);

        $types = ['Customer', 'Vendor', 'Prospect', 'Partner'];
        $paymentTerms = ['Net 15', 'Net 30', 'Net 45', 'Net 60', 'Advance Wire', 'Due on Receipt'];
        $currencies = ['USD', 'EUR', 'GBP', 'INR', 'CAD', 'AUD', 'JPY', 'AED', 'SGD', 'CHF'];

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'accounts' => $sanitizedAccounts,
                'custom_fields' => $customFields,
                'field_permissions' => $fieldPermissions,
                'profile_permissions' => $profilePermissions,
                'users' => $users,
                'types' => $types,
                'payment_terms' => $paymentTerms,
                'currencies' => $currencies,
            ]);
        }

        return view('accounts', [
            'initialAccounts' => $sanitizedAccounts,
            'customFields' => $customFields,
            'fieldPermissions' => $fieldPermissions,
            'profilePermissions' => $profilePermissions,
            'users' => $users,
            'types' => $types,
            'paymentTerms' => $paymentTerms,
            'currencies' => $currencies,
            'currentUserProfile' => $profile ? $profile->name : 'Administrator',
        ]);
    }

    /**
     * Display the dedicated Account 360 view page.
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
                    'message' => 'You do not have permission to view accounts',
                ], 403);
            }
            abort(403, 'You do not have permission to view accounts.');
        }

        $account = Account::on('tenant')
            ->with(['owner', 'contacts', 'opportunities', 'purchaseDeals', 'shipments', 'payments', 'wires'])
            ->findOrFail($id);

        if (!$this->securityService->userCanAccessAccount($account, 'read', $user, $profilePermissions)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'error' => 'Access denied',
                    'message' => 'You do not have access to this account record.',
                ], 403);
            }
            abort(403, 'You do not have access to this account record.');
        }

        $sanitizedAccount = $this->securityService->sanitizeAccount($account, $fieldPermissions);

        // Fetch custom fields for accounts
        $customFields = CustomField::on('tenant')
            ->where('module', 'accounts')
            ->orderBy('id', 'asc')
            ->get();

        $users = TenantUser::on('tenant')->orderBy('name', 'asc')->get(['id', 'name', 'email']);
        $types = ['Customer', 'Vendor', 'Prospect', 'Partner'];
        $paymentTerms = ['Net 15', 'Net 30', 'Net 45', 'Net 60', 'Advance Wire', 'Due on Receipt'];

        $relations = $this->activityService->getAccount360Data($account, $fieldPermissions);

        if ($request->wantsJson()) {
            return response()->json(array_merge([
                'account' => $sanitizedAccount,
                'custom_fields' => $customFields,
                'field_permissions' => $fieldPermissions,
                'profile_permissions' => $profilePermissions,
                'users' => $users,
            ], $relations));
        }

        return view('accounts.show', array_merge([
            'account' => $sanitizedAccount,
            'rawAccount' => $account,
            'customFields' => $customFields,
            'fieldPermissions' => $fieldPermissions,
            'profilePermissions' => $profilePermissions,
            'users' => $users,
            'types' => $types,
            'paymentTerms' => $paymentTerms,
            'currentUserProfile' => $profile ? $profile->name : 'Administrator',
        ], $relations));
    }

    /**
     * API: Get filtered accounts list.
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
                'message' => 'You do not have permission to view accounts',
            ], 403);
        }

        $query = Account::on('tenant')
            ->with(['owner', 'contacts', 'opportunities', 'purchaseDeals'])
            ->orderBy('id', 'asc');

        $query = $this->securityService->applyAccountRecordScopeQuery($query, $user, $profilePermissions);

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('industry', 'like', "%{$s}%")
                    ->orWhere('website', 'like', "%{$s}%")
                    ->orWhere('phone', 'like', "%{$s}%")
                    ->orWhere('assigned_to', 'like', "%{$s}%");
            });
        }

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        $rawAccounts = $query->get();
        $accounts = $this->securityService->sanitizeCollection($rawAccounts, $fieldPermissions);

        return response()->json($accounts);
    }

    /**
     * API: Get accounts metadata (custom fields, permissions, users, types, terms).
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
                'message' => 'You do not have permission to view accounts',
            ], 403);
        }

        $customFields = CustomField::on('tenant')
            ->where('module', 'accounts')
            ->orderBy('id', 'asc')
            ->get();

        $users = TenantUser::on('tenant')->orderBy('name', 'asc')->get(['id', 'name', 'email']);

        return response()->json([
            'custom_fields' => $customFields,
            'field_permissions' => $fieldPermissions,
            'profile_permissions' => $profilePermissions,
            'users' => $users,
            'types' => ['Customer', 'Vendor', 'Prospect', 'Partner'],
            'payment_terms' => ['Net 15', 'Net 30', 'Net 45', 'Net 60', 'Advance Wire', 'Due on Receipt'],
            'currencies' => ['USD', 'EUR', 'GBP', 'INR', 'CAD', 'AUD', 'JPY', 'AED', 'SGD', 'CHF'],
        ]);
    }

    /**
     * API: Get single account.
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
                'message' => 'You do not have permission to view accounts',
            ], 403);
        }

        $account = Account::on('tenant')->with(['owner', 'contacts'])->findOrFail($id);

        if (!$this->securityService->userCanAccessAccount($account, 'read', $user, $profilePermissions)) {
            return response()->json([
                'error' => 'Access denied',
                'message' => 'You do not have access to this account record.',
            ], 403);
        }

        return response()->json($this->securityService->sanitizeAccount($account, $fieldPermissions));
    }

    /**
     * API: Store a newly created account.
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
                'message' => 'You do not have permission to create accounts',
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'type' => 'nullable|string|max:50',
            'industry' => 'nullable|string|max:100',
            'website' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:30',
            'currency' => 'nullable|string|max:10',
            'payment_terms' => 'nullable|string|max:60',
            'credit_limit' => 'nullable|numeric|min:0',
            'annual_revenue' => 'nullable|numeric|min:0',
            'assigned_to' => 'nullable|string|max:150',
            'owner_id' => 'nullable|integer',
            'billing_address' => 'nullable|string',
            'shipping_address' => 'nullable|string',
            'notes' => 'nullable|string',
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
        $activeCustomFields = CustomField::on('tenant')->where('module', 'accounts')->get();
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
        $validated['type'] = $validated['type'] ?? 'Customer';
        $validated['currency'] = $validated['currency'] ?? 'USD';

        if (empty($validated['owner_id']) && $user) {
            $validated['owner_id'] = $user->id;
        }
        if (empty($validated['assigned_to']) && $user) {
            $validated['assigned_to'] = $user->name;
        }
        if ($user) {
            $validated['created_by'] = $user->id;
        }

        $account = Account::on('tenant')->create($validated);
        $account->load('owner');

        return response()->json([
            'success' => true,
            'message' => "Account '{$account->name}' created successfully!",
            'account' => $this->securityService->sanitizeAccount($account, $fieldPermissions),
        ], 201);
    }

    /**
     * API: Update an existing account.
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
                'message' => 'You do not have permission to edit accounts',
            ], 403);
        }

        $account = Account::on('tenant')->findOrFail($id);

        if (!$this->securityService->userCanAccessAccount($account, 'update', $user, $profilePermissions)) {
            return response()->json([
                'error' => 'Access denied',
                'message' => 'You do not have permission to edit this account record.',
            ], 403);
        }

        $allowedStandard = [
            'name', 'type', 'industry', 'website', 'phone',
            'currency', 'payment_terms', 'credit_limit', 'annual_revenue',
            'assigned_to', 'owner_id', 'billing_address', 'shipping_address', 'notes'
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

        // Process dynamic custom fields
        $existingCustom = is_array($account->custom_data) ? $account->custom_data : (json_decode($account->custom_data ?? '[]', true) ?: []);
        $inputCustom = $request->input('custom_fields', []);
        $activeCustomFields = CustomField::on('tenant')->where('module', 'accounts')->get();

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

        $account->update($updates);
        $account->load('owner');

        return response()->json([
            'success' => true,
            'message' => "Account '{$account->name}' updated successfully!",
            'account' => $this->securityService->sanitizeAccount($account, $fieldPermissions),
        ]);
    }

    /**
     * API: Delete an account.
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
                'message' => 'You do not have permission to delete accounts',
            ], 403);
        }

        $account = Account::on('tenant')->findOrFail($id);

        if (!$this->securityService->userCanAccessAccount($account, 'delete', $user, $profilePermissions)) {
            return response()->json([
                'error' => 'Access denied',
                'message' => 'You do not have permission to delete this account record.',
            ], 403);
        }

        $name = $account->name;
        $account->delete();

        return response()->json([
            'success' => true,
            'message' => "Account '{$name}' deleted successfully.",
        ]);
    }

    /**
     * API: Transfer account ownership.
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
                'message' => 'You do not have permission to transfer account ownership',
            ], 403);
        }

        $account = Account::on('tenant')->findOrFail($id);

        if (!$this->securityService->userCanAccessAccount($account, 'transfer', $user, $profilePermissions)) {
            return response()->json([
                'error' => 'Access denied',
                'message' => 'You do not have permission to transfer this account record.',
            ], 403);
        }

        $validated = $request->validate([
            'owner_id' => 'nullable|integer',
            'assigned_to' => 'nullable|string|max:150',
        ]);

        if (!empty($validated['owner_id'])) {
            $targetUser = TenantUser::on('tenant')->find($validated['owner_id']);
            if ($targetUser) {
                $account->owner_id = $targetUser->id;
                $account->assigned_to = $targetUser->name;
            }
        } elseif (!empty($validated['assigned_to'])) {
            $account->assigned_to = $validated['assigned_to'];
        }

        if ($user) {
            $account->updated_by = $user->id;
        }

        $account->save();
        $account->load('owner');

        $fieldPermissions = $this->securityService->getFieldPermissionsForProfile($profile);

        return response()->json([
            'success' => true,
            'message' => "Account ownership transferred to '{$account->assigned_to}'",
            'account' => $this->securityService->sanitizeAccount($account, $fieldPermissions),
        ]);
    }

    /**
     * API: Get Account 360 Degree Data (Overview, Contacts, Deals, POs, Shipments, Invoices).
     */
    public function api360(Request $request, $id)
    {
        $this->securityService->ensureTenantConnection();
        $user = $this->securityService->resolveCurrentUser();
        $profile = $this->securityService->resolveCurrentUserProfile($user);
        $profilePermissions = $this->securityService->getProfilePermissions($profile, $user);
        $fieldPermissions = $this->securityService->getFieldPermissionsForProfile($profile);

        if (empty($profilePermissions['can_read'])) {
            return response()->json([
                'error' => 'Access denied',
                'message' => 'You do not have permission to view accounts',
            ], 403);
        }

        $account = Account::on('tenant')->with(['owner'])->findOrFail($id);

        if (!$this->securityService->userCanAccessAccount($account, 'read', $user, $profilePermissions)) {
            return response()->json([
                'error' => 'Access denied',
                'message' => 'You do not have access to this account record.',
            ], 403);
        }

        $sanitizedAccount = $this->securityService->sanitizeAccount($account, $fieldPermissions);
        $relations = $this->activityService->getAccount360Data($account, $fieldPermissions);

        return response()->json(array_merge([
            'account' => $sanitizedAccount,
        ], $relations));
    }

    /**
     * API: Get files linked to account.
     */
    public function apiGetFiles(Request $request, $id)
    {
        $this->securityService->ensureTenantConnection();
        $user = $this->securityService->resolveCurrentUser();
        $profile = $this->securityService->resolveCurrentUserProfile($user);
        $profilePermissions = $this->securityService->getProfilePermissions($profile, $user);

        if (empty($profilePermissions['can_read'])) {
            return response()->json(['error' => 'Access denied', 'message' => 'You do not have permission to view accounts'], 403);
        }

        $account = Account::on('tenant')->findOrFail($id);
        if (!$this->securityService->userCanAccessAccount($account, 'read', $user, $profilePermissions)) {
            return response()->json(['error' => 'Access denied', 'message' => 'You do not have access to this account record.'], 403);
        }

        $files = $this->activityService->getFiles($account->id);

        return response()->json([
            'success' => true,
            'count' => $files->count(),
            'files' => $files,
        ]);
    }

    /**
     * API: Upload file(s) for an account.
     */
    public function apiUploadFiles(Request $request, $id)
    {
        $this->securityService->ensureTenantConnection();
        $user = $this->securityService->resolveCurrentUser();
        $profile = $this->securityService->resolveCurrentUserProfile($user);
        $profilePermissions = $this->securityService->getProfilePermissions($profile, $user);

        if (empty($profilePermissions['can_update'])) {
            return response()->json(['error' => 'Access denied', 'message' => 'You do not have permission to edit accounts'], 403);
        }

        $account = Account::on('tenant')->findOrFail($id);
        if (!$this->securityService->userCanAccessAccount($account, 'update', $user, $profilePermissions)) {
            return response()->json(['error' => 'Access denied', 'message' => 'You do not have permission to edit this account record.'], 403);
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

        $result = $this->activityService->uploadFiles($account, $uploadedFiles, $user, $request->input('description'));

        return response()->json($result);
    }

    /**
     * API: Delete a file linked to an account.
     */
    public function apiDeleteFile(Request $request, $id, $fileId)
    {
        $this->securityService->ensureTenantConnection();
        $user = $this->securityService->resolveCurrentUser();
        $profile = $this->securityService->resolveCurrentUserProfile($user);
        $profilePermissions = $this->securityService->getProfilePermissions($profile, $user);

        if (empty($profilePermissions['can_update'])) {
            return response()->json(['error' => 'Access denied', 'message' => 'You do not have permission to edit accounts'], 403);
        }

        $account = Account::on('tenant')->findOrFail($id);
        if (!$this->securityService->userCanAccessAccount($account, 'update', $user, $profilePermissions)) {
            return response()->json(['error' => 'Access denied', 'message' => 'You do not have permission to edit this account record.'], 403);
        }

        $remainingCount = $this->activityService->deleteFile($account, $fileId, $user);
        if ($remainingCount === null) {
            return response()->json([
                'success' => false,
                'message' => 'File not found on this account.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'File deleted successfully.',
            'count' => $remainingCount,
        ]);
    }

    /**
     * API: Download an account file.
     */
    public function apiDownloadFile(Request $request, $id, $fileId)
    {
        $this->securityService->ensureTenantConnection();
        $user = $this->securityService->resolveCurrentUser();
        $profile = $this->securityService->resolveCurrentUserProfile($user);
        $profilePermissions = $this->securityService->getProfilePermissions($profile, $user);

        if (empty($profilePermissions['can_read'])) {
            abort(403, 'You do not have permission to view accounts.');
        }

        $account = Account::on('tenant')->findOrFail($id);
        if (!$this->securityService->userCanAccessAccount($account, 'read', $user, $profilePermissions)) {
            abort(403, 'You do not have access to this account record.');
        }

        return $this->activityService->downloadFile($account, $fileId);
    }

    /**
     * API: Get activities for an account (Timeline).
     */
    public function apiGetActivities(Request $request, $id)
    {
        $this->securityService->ensureTenantConnection();
        $user = $this->securityService->resolveCurrentUser();
        $profile = $this->securityService->resolveCurrentUserProfile($user);
        $profilePermissions = $this->securityService->getProfilePermissions($profile, $user);

        if (empty($profilePermissions['can_read'])) {
            return response()->json(['error' => 'Access denied', 'message' => 'You do not have permission to view accounts'], 403);
        }

        $account = Account::on('tenant')->findOrFail($id);
        if (!$this->securityService->userCanAccessAccount($account, 'read', $user, $profilePermissions)) {
            return response()->json(['error' => 'Access denied', 'message' => 'You do not have access to this account record.'], 403);
        }

        $formatted = $this->activityService->getActivities($account->id);

        return response()->json([
            'success' => true,
            'count' => $formatted->count(),
            'activities' => $formatted,
        ]);
    }

    /**
     * API: Store activity (Call, Event, Note) for an account.
     */
    public function apiStoreActivity(Request $request, $id)
    {
        $this->securityService->ensureTenantConnection();
        $user = $this->securityService->resolveCurrentUser();
        $profile = $this->securityService->resolveCurrentUserProfile($user);
        $profilePermissions = $this->securityService->getProfilePermissions($profile, $user);

        if (empty($profilePermissions['can_update'])) {
            return response()->json(['error' => 'Access denied', 'message' => 'You do not have permission to edit accounts'], 403);
        }

        $account = Account::on('tenant')->findOrFail($id);
        if (!$this->securityService->userCanAccessAccount($account, 'update', $user, $profilePermissions)) {
            return response()->json(['error' => 'Access denied', 'message' => 'You do not have permission to edit this account record.'], 403);
        }

        $request->validate([
            'subject' => 'required|string|max:200',
            'type' => 'nullable|string|max:40',
            'details' => 'nullable|string',
            'call_datetime' => 'nullable|string',
            'start_date' => 'nullable|string',
            'end_date' => 'nullable|string',
        ]);

        $result = $this->activityService->storeActivity($account, $request->all(), $user);

        return response()->json($result);
    }

    /**
     * API: Store task specifically for an account and log to activities timeline.
     */
    public function apiStoreTask(Request $request, $id)
    {
        $this->securityService->ensureTenantConnection();
        $user = $this->securityService->resolveCurrentUser();
        $profile = $this->securityService->resolveCurrentUserProfile($user);
        $profilePermissions = $this->securityService->getProfilePermissions($profile, $user);

        if (empty($profilePermissions['can_update'])) {
            return response()->json(['error' => 'Access denied', 'message' => 'You do not have permission to edit accounts'], 403);
        }

        $account = Account::on('tenant')->findOrFail($id);
        if (!$this->securityService->userCanAccessAccount($account, 'update', $user, $profilePermissions)) {
            return response()->json(['error' => 'Access denied', 'message' => 'You do not have permission to edit this account record.'], 403);
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

        $result = $this->activityService->storeTask($account, $request->all(), $user);

        return response()->json($result);
    }
}
