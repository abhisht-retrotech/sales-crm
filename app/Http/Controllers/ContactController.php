<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Contact;
use App\Models\Account;
use App\Models\Opportunity;
use App\Models\PurchaseDeal;
use App\Models\TenantUser;
use App\Models\CustomField;
use App\Models\FieldPermission;
use App\Models\Profile;
use App\Models\Tenant;

class ContactController extends Controller
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
     * Get field permissions map for the current user's profile on the contacts module.
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
            ->where('module', 'contacts')
            ->get();

        foreach ($records as $r) {
            $perms[$r->field_name] = $r->permission ?: 'Read/Write';
        }

        return $perms;
    }

    /**
     * Sanitize a contact record respecting field-level security permissions.
     */
    protected function sanitizeContact(Contact $contact, array $fieldPermissions): array
    {
        $data = $contact->toArray();
        $customData = is_array($contact->custom_data) ? $contact->custom_data : (json_decode($contact->custom_data ?? '[]', true) ?: []);

        // Computed helper attributes
        $data['name'] = trim(($contact->first_name ?? '') . ' ' . ($contact->last_name ?? ''));
        $data['account_name'] = $contact->account?->name ?? ($contact->account_name ?: 'Unlinked');
        $data['owner_name'] = $contact->assignedUser?->name ?? 'Unassigned';

        // Apply permissions on standard attributes
        $protectedKeys = ['id', 'created_at', 'updated_at', 'deleted_at', 'account_id', 'assigned_to', 'created_by', 'updated_by'];
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
     * Ensure default custom fields exist for contacts module.
     */
    protected function ensureCustomFields()
    {
        try {
            if (CustomField::on('tenant')->where('module', 'contacts')->count() > 0) {
                return;
            }

            $defaults = [
                [
                    'module' => 'contacts',
                    'field_name' => 'cf_preferred_contact_method',
                    'field_label' => 'Preferred Channel',
                    'data_type' => 'picklist',
                    'options' => 'Email, Corporate Phone, Mobile / SMS, Video Meeting',
                    'is_required' => false,
                    'help_text' => 'The executive stakeholder’s preferred outreach channel',
                ],
                [
                    'module' => 'contacts',
                    'field_name' => 'cf_linkedin_profile',
                    'field_label' => 'LinkedIn Profile URL',
                    'data_type' => 'text',
                    'options' => null,
                    'is_required' => false,
                    'help_text' => 'Professional verified LinkedIn profile handle or URL',
                ],
                [
                    'module' => 'contacts',
                    'field_name' => 'cf_time_zone',
                    'field_label' => 'Primary Timezone',
                    'data_type' => 'picklist',
                    'options' => 'US/Eastern (UTC-5), US/Central (UTC-6), US/Mountain (UTC-7), US/Pacific (UTC-8), Europe/London (UTC+0), Europe/Berlin (UTC+1), Asia/Kolkata (UTC+5:30), Asia/Tokyo (UTC+9)',
                    'is_required' => false,
                    'help_text' => 'Stakeholder local working hours timezone',
                ],
                [
                    'module' => 'contacts',
                    'field_name' => 'cf_budget_authority',
                    'field_label' => 'Sign-off Authority Tier',
                    'data_type' => 'picklist',
                    'options' => 'Direct Sign-off (> $100k), Commercial Sign-off (< $50k), Key Recommender Only, Technical Evaluator',
                    'is_required' => false,
                    'help_text' => 'Discretionary commercial purchasing authority tier',
                ],
                [
                    'module' => 'contacts',
                    'field_name' => 'cf_assistant_name_contact',
                    'field_label' => 'Executive Assistant & Desk',
                    'data_type' => 'text',
                    'options' => null,
                    'is_required' => false,
                    'help_text' => 'Direct administrative assistant name and routing desk phone',
                ],
            ];

            foreach ($defaults as $cf) {
                CustomField::on('tenant')->updateOrCreate(
                    ['module' => 'contacts', 'field_name' => $cf['field_name']],
                    $cf
                );
            }
        } catch (\Throwable $e) {
            // Silently ignore
        }
    }

    /**
     * Seed initial demo contacts if database table is completely empty.
     */
    protected function ensureDemoContacts()
    {
        try {
            if (Contact::on('tenant')->count() > 0) {
                return;
            }

            $firstUser = TenantUser::on('tenant')->first();
            $userId = $firstUser?->id;

            $accounts = Account::on('tenant')->get()->keyBy('name');

            $demoContacts = [
                [
                    'first_name' => 'Elena',
                    'last_name' => 'Vance',
                    'email' => 'elena.vance@skylinecloud.io',
                    'phone' => '+1 (415) 890-1201',
                    'title' => 'Chief Technology Officer',
                    'department' => 'Executive & Technology',
                    'contact_role' => 'Decision Maker',
                    'is_primary' => true,
                    'email_opt_out' => false,
                    'account_name' => 'Skyline Cloud Systems',
                    'account_id' => $accounts->get('Skyline Cloud Systems')?->id,
                    'assigned_to' => $userId,
                    'notes' => 'Executive champion for cloud migration. Signs off on all infrastructure contracts over $50k.',
                    'custom_data' => [
                        'cf_preferred_contact_method' => 'Email',
                        'cf_linkedin_profile' => 'https://linkedin.com/in/elena-vance-cto',
                        'cf_time_zone' => 'US/Pacific (UTC-8)',
                        'cf_budget_authority' => 'Direct Sign-off (> $100k)',
                        'cf_assistant_name_contact' => 'Marcus Kelly (Ext 401)',
                    ],
                ],
                [
                    'first_name' => 'David',
                    'last_name' => 'Kowalski',
                    'email' => 'd.kowalski@skylinecloud.io',
                    'phone' => '+1 (415) 890-1244',
                    'title' => 'VP of Cloud Infrastructure',
                    'department' => 'Cloud Operations',
                    'contact_role' => 'Champion',
                    'is_primary' => false,
                    'email_opt_out' => false,
                    'account_name' => 'Skyline Cloud Systems',
                    'account_id' => $accounts->get('Skyline Cloud Systems')?->id,
                    'assigned_to' => $userId,
                    'notes' => 'Leads technical proof-of-concept evaluations and benchmark trials.',
                    'custom_data' => [
                        'cf_preferred_contact_method' => 'Video Meeting',
                        'cf_linkedin_profile' => 'https://linkedin.com/in/dkowalski-cloud',
                        'cf_time_zone' => 'US/Pacific (UTC-8)',
                        'cf_budget_authority' => 'Commercial Sign-off (< $50k)',
                        'cf_assistant_name_contact' => 'Direct line',
                    ],
                ],
                [
                    'first_name' => 'Dr. Sarah',
                    'last_name' => 'Al-Mansoor',
                    'email' => 's.almansoor@vanguardhealth.tech',
                    'phone' => '+1 (617) 555-0192',
                    'title' => 'Chief Medical Information Officer',
                    'department' => 'Healthcare Informatics',
                    'contact_role' => 'Decision Maker',
                    'is_primary' => true,
                    'email_opt_out' => false,
                    'account_name' => 'Vanguard Health Tech',
                    'account_id' => $accounts->get('Vanguard Health Tech')?->id,
                    'assigned_to' => $userId,
                    'notes' => 'Requires strict HIPAA compliance documentation and high uptime SLAs.',
                    'custom_data' => [
                        'cf_preferred_contact_method' => 'Email',
                        'cf_linkedin_profile' => 'https://linkedin.com/in/dralmansoor',
                        'cf_time_zone' => 'US/Eastern (UTC-5)',
                        'cf_budget_authority' => 'Direct Sign-off (> $100k)',
                        'cf_assistant_name_contact' => 'Jessica Chen (Ext 112)',
                    ],
                ],
                [
                    'first_name' => 'Marcus',
                    'last_name' => 'Brody',
                    'email' => 'mbrody@quantumsilicon.com',
                    'phone' => '+1 (408) 555-4322',
                    'title' => 'Director of Global Accounts',
                    'department' => 'Supplier Operations',
                    'contact_role' => 'Evaluator',
                    'is_primary' => true,
                    'email_opt_out' => false,
                    'account_name' => 'Quantum Silicon Components',
                    'account_id' => $accounts->get('Quantum Silicon Components')?->id,
                    'assigned_to' => $userId,
                    'notes' => 'Primary vendor representative for semiconductor deliveries and lead times.',
                    'custom_data' => [
                        'cf_preferred_contact_method' => 'Corporate Phone',
                        'cf_linkedin_profile' => 'https://linkedin.com/in/marcus-brody-silicon',
                        'cf_time_zone' => 'US/Pacific (UTC-8)',
                        'cf_budget_authority' => 'Key Recommender Only',
                        'cf_assistant_name_contact' => 'Desk Line 408-555-4320',
                    ],
                ],
                [
                    'first_name' => 'Rachel',
                    'last_name' => 'Sterling',
                    'email' => 'rachel.s@titansteel.com',
                    'phone' => '+1 (312) 555-7892',
                    'title' => 'VP of Enterprise Procurement',
                    'department' => 'Procurement & Logistics',
                    'contact_role' => 'Influencer',
                    'is_primary' => true,
                    'email_opt_out' => false,
                    'account_name' => 'Titan Steel & Enclosures',
                    'account_id' => $accounts->get('Titan Steel & Enclosures')?->id,
                    'assigned_to' => $userId,
                    'notes' => 'Oversees purchase orders, supplier warranties, and freight routing schedules.',
                    'custom_data' => [
                        'cf_preferred_contact_method' => 'Email',
                        'cf_linkedin_profile' => 'https://linkedin.com/in/rachelsterling-titan',
                        'cf_time_zone' => 'US/Central (UTC-6)',
                        'cf_budget_authority' => 'Commercial Sign-off (< $50k)',
                        'cf_assistant_name_contact' => 'Gary Vance (Ext 890)',
                    ],
                ],
                [
                    'first_name' => 'Alex',
                    'last_name' => 'Thorne',
                    'email' => 'a.thorne@apexglobal.net',
                    'phone' => '+1 (212) 555-0149',
                    'title' => 'Head of Solutions Architecture',
                    'department' => 'Architecture & Engineering',
                    'contact_role' => 'Champion',
                    'is_primary' => true,
                    'email_opt_out' => false,
                    'account_name' => 'Apex Global Solutions',
                    'account_id' => $accounts->get('Apex Global Solutions')?->id,
                    'assigned_to' => $userId,
                    'notes' => 'Architect testing API endpoints and database latency for financial workloads.',
                    'custom_data' => [
                        'cf_preferred_contact_method' => 'Video Meeting',
                        'cf_linkedin_profile' => 'https://linkedin.com/in/alex-thorne-fintech',
                        'cf_time_zone' => 'US/Eastern (UTC-5)',
                        'cf_budget_authority' => 'Technical Evaluator',
                        'cf_assistant_name_contact' => 'Direct Slack / Desk',
                    ],
                ],
            ];

            foreach ($demoContacts as $c) {
                Contact::on('tenant')->create($c);
            }
        } catch (\Throwable $e) {
            // Silently ignore
        }
    }

    /**
     * Display the Contacts Directory page or return JSON.
     */
    public function index(Request $request)
    {
        $this->ensureTenantConnection();
        $this->ensureCustomFields();
        $this->ensureDemoContacts();

        $profile = $this->resolveCurrentUserProfile();
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);

        // Fetch custom fields for contacts
        $customFields = CustomField::on('tenant')
            ->where('module', 'contacts')
            ->orderBy('id', 'asc')
            ->get();

        // Build query
        $query = Contact::on('tenant')
            ->with(['account', 'assignedUser'])
            ->orderBy('id', 'desc');

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

        // Sanitize contacts according to field permissions
        $sanitizedContacts = $rawContacts->map(function ($contact) use ($fieldPermissions) {
            return $this->sanitizeContact($contact, $fieldPermissions);
        })->values();

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
        $this->ensureTenantConnection();
        $this->ensureCustomFields();

        $profile = $this->resolveCurrentUserProfile();
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);

        $contact = Contact::on('tenant')
            ->with(['account', 'assignedUser', 'opportunities', 'purchaseDeals'])
            ->findOrFail($id);

        $sanitizedContact = $this->sanitizeContact($contact, $fieldPermissions);

        // Fetch custom fields for contacts
        $customFields = CustomField::on('tenant')
            ->where('module', 'contacts')
            ->orderBy('id', 'asc')
            ->get();

        $accounts = Account::on('tenant')->orderBy('name', 'asc')->get(['id', 'name']);
        $users = TenantUser::on('tenant')->orderBy('name', 'asc')->get(['id', 'name', 'email']);
        $roles = ['Decision Maker', 'Champion', 'Influencer', 'Evaluator'];

        $opportunities = $contact->opportunities()->orderBy('id', 'desc')->get();
        $purchaseDeals = $contact->purchaseDeals()->orderBy('id', 'desc')->get();

        if ($request->wantsJson()) {
            return response()->json([
                'contact' => $sanitizedContact,
                'custom_fields' => $customFields,
                'field_permissions' => $fieldPermissions,
                'accounts' => $accounts,
                'users' => $users,
                'opportunities' => $opportunities,
                'purchase_deals' => $purchaseDeals,
            ]);
        }

        return view('contacts.show', [
            'contact' => $sanitizedContact,
            'rawContact' => $contact,
            'customFields' => $customFields,
            'fieldPermissions' => $fieldPermissions,
            'accounts' => $accounts,
            'users' => $users,
            'roles' => $roles,
            'opportunities' => $opportunities,
            'purchaseDeals' => $purchaseDeals,
            'currentUserProfile' => $profile ? $profile->name : 'Administrator',
        ]);
    }

    /**
     * API: Get filtered contacts list.
     */
    public function apiList(Request $request)
    {
        $this->ensureTenantConnection();
        $profile = $this->resolveCurrentUserProfile();
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);

        $query = Contact::on('tenant')
            ->with(['account', 'assignedUser'])
            ->orderBy('id', 'desc');

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
        $contacts = $rawContacts->map(function ($contact) use ($fieldPermissions) {
            return $this->sanitizeContact($contact, $fieldPermissions);
        })->values();

        return response()->json($contacts);
    }

    /**
     * API: Get contacts metadata.
     */
    public function apiMeta(Request $request)
    {
        $this->ensureTenantConnection();
        $profile = $this->resolveCurrentUserProfile();
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);

        $customFields = CustomField::on('tenant')
            ->where('module', 'contacts')
            ->orderBy('id', 'asc')
            ->get();

        $accounts = Account::on('tenant')->orderBy('name', 'asc')->get(['id', 'name']);
        $users = TenantUser::on('tenant')->orderBy('name', 'asc')->get(['id', 'name', 'email']);

        return response()->json([
            'custom_fields' => $customFields,
            'field_permissions' => $fieldPermissions,
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
        $this->ensureTenantConnection();
        $profile = $this->resolveCurrentUserProfile();
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);

        $contact = Contact::on('tenant')->with(['account', 'assignedUser'])->findOrFail($id);
        return response()->json($this->sanitizeContact($contact, $fieldPermissions));
    }

    /**
     * API: Store a newly created contact.
     */
    public function apiStore(Request $request)
    {
        $this->ensureTenantConnection();
        $profile = $this->resolveCurrentUserProfile();
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);

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

        $contact = Contact::on('tenant')->create($validated);
        $contact->load(['account', 'assignedUser']);

        return response()->json([
            'success' => true,
            'message' => "Contact '{$contact->first_name} {$contact->last_name}' created successfully!",
            'contact' => $this->sanitizeContact($contact, $fieldPermissions),
        ], 201);
    }

    /**
     * API: Update an existing contact.
     */
    public function apiUpdate(Request $request, $id)
    {
        $this->ensureTenantConnection();
        $profile = $this->resolveCurrentUserProfile();
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);

        $contact = Contact::on('tenant')->findOrFail($id);

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
        $contact->update($updates);
        $contact->load(['account', 'assignedUser']);

        return response()->json([
            'success' => true,
            'message' => "Contact '{$contact->first_name} {$contact->last_name}' updated successfully!",
            'contact' => $this->sanitizeContact($contact, $fieldPermissions),
        ]);
    }

    /**
     * API: Delete a contact.
     */
    public function apiDestroy(Request $request, $id)
    {
        $this->ensureTenantConnection();
        $contact = Contact::on('tenant')->findOrFail($id);
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
        $this->ensureTenantConnection();
        $validated = $request->validate([
            'assigned_to' => 'required|integer',
        ]);

        $contact = Contact::on('tenant')->findOrFail($id);
        $user = TenantUser::on('tenant')->findOrFail($validated['assigned_to']);

        $contact->assigned_to = $user->id;
        $contact->save();
        $contact->load('assignedUser');

        $profile = $this->resolveCurrentUserProfile();
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);

        return response()->json([
            'success' => true,
            'message' => "Contact reassigned to '{$user->name}'",
            'contact' => $this->sanitizeContact($contact, $fieldPermissions),
        ]);
    }

    /**
     * API: Contact 360 Degree View Data.
     */
    public function api360(Request $request, $id)
    {
        $this->ensureTenantConnection();
        $profile = $this->resolveCurrentUserProfile();
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);

        $contact = Contact::on('tenant')
            ->with(['account', 'assignedUser', 'opportunities', 'purchaseDeals'])
            ->findOrFail($id);

        $sanitizedContact = $this->sanitizeContact($contact, $fieldPermissions);
        $opportunities = $contact->opportunities()->orderBy('id', 'desc')->get();
        $purchaseDeals = $contact->purchaseDeals()->orderBy('id', 'desc')->get();

        return response()->json([
            'contact' => $sanitizedContact,
            'account' => $contact->account,
            'opportunities' => $opportunities,
            'purchase_deals' => $purchaseDeals,
        ]);
    }
}
