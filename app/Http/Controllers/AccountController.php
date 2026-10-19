<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Account;
use App\Models\Contact;
use App\Models\Opportunity;
use App\Models\PurchaseDeal;
use App\Models\Shipment;
use App\Models\Payment;
use App\Models\WireReceived;
use App\Models\TenantUser;
use App\Models\CustomField;
use App\Models\FieldPermission;
use App\Models\Profile;
use App\Models\Tenant;

class AccountController extends Controller
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
     * Get field permissions map for the current user's profile on the accounts module.
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
            ->where('module', 'accounts')
            ->get();

        foreach ($records as $r) {
            $perms[$r->field_name] = $r->permission ?: 'Read/Write';
        }

        return $perms;
    }

    /**
     * Sanitize an account record respecting field-level security permissions.
     */
    protected function sanitizeAccount(Account $account, array $fieldPermissions): array
    {
        $data = $account->toArray();
        $customData = is_array($account->custom_data) ? $account->custom_data : (json_decode($account->custom_data ?? '[]', true) ?: []);

        // Computed helper attributes
        $data['owner_name'] = $account->owner?->name ?? ($account->assigned_to ?: 'Unassigned');
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
     * Ensure default custom fields exist for accounts module.
     */
    protected function ensureCustomFields()
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
    protected function ensureDemoAccounts()
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

    /**
     * Display the Accounts Directory page or return JSON.
     */
    public function index(Request $request)
    {
        $this->ensureTenantConnection();
        $this->ensureCustomFields();
        $this->ensureDemoAccounts();

        $profile = $this->resolveCurrentUserProfile();
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);

        // Fetch custom fields for accounts
        $customFields = CustomField::on('tenant')
            ->where('module', 'accounts')
            ->orderBy('id', 'asc')
            ->get();

        // Build query
        $query = Account::on('tenant')
            ->with(['owner', 'contacts', 'opportunities', 'purchaseDeals', 'shipments', 'payments'])
            ->orderBy('id', 'desc');

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

        // Sanitize accounts according to field permissions
        $sanitizedAccounts = $rawAccounts->map(function ($account) use ($fieldPermissions) {
            return $this->sanitizeAccount($account, $fieldPermissions);
        })->values();

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
        $this->ensureTenantConnection();
        $this->ensureCustomFields();

        $profile = $this->resolveCurrentUserProfile();
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);

        $account = Account::on('tenant')
            ->with(['owner', 'contacts', 'opportunities', 'purchaseDeals', 'shipments', 'payments', 'wires'])
            ->findOrFail($id);

        $sanitizedAccount = $this->sanitizeAccount($account, $fieldPermissions);

        // Fetch custom fields for accounts
        $customFields = CustomField::on('tenant')
            ->where('module', 'accounts')
            ->orderBy('id', 'asc')
            ->get();

        $users = TenantUser::on('tenant')->orderBy('name', 'asc')->get(['id', 'name', 'email']);
        $types = ['Customer', 'Vendor', 'Prospect', 'Partner'];
        $paymentTerms = ['Net 15', 'Net 30', 'Net 45', 'Net 60', 'Advance Wire', 'Due on Receipt'];

        // Related 360 data
        $contacts = $account->contacts()->orderBy('is_primary', 'desc')->get();
        $opportunities = $account->opportunities()->orderBy('id', 'desc')->get();
        $purchaseDeals = $account->purchaseDeals()->orderBy('id', 'desc')->get();
        $shipments = $account->shipments()->orderBy('id', 'desc')->get();
        $payments = $account->payments()->orderBy('id', 'desc')->get();
        $wires = $account->wires()->orderBy('id', 'desc')->get();

        if ($request->wantsJson()) {
            return response()->json([
                'account' => $sanitizedAccount,
                'custom_fields' => $customFields,
                'field_permissions' => $fieldPermissions,
                'users' => $users,
                'contacts' => $contacts,
                'opportunities' => $opportunities,
                'purchaseDeals' => $purchaseDeals,
                'shipments' => $shipments,
                'payments' => $payments,
                'wires' => $wires,
            ]);
        }

        return view('accounts.show', [
            'account' => $sanitizedAccount,
            'rawAccount' => $account,
            'customFields' => $customFields,
            'fieldPermissions' => $fieldPermissions,
            'users' => $users,
            'types' => $types,
            'paymentTerms' => $paymentTerms,
            'contacts' => $contacts,
            'opportunities' => $opportunities,
            'purchaseDeals' => $purchaseDeals,
            'shipments' => $shipments,
            'payments' => $payments,
            'wires' => $wires,
            'currentUserProfile' => $profile ? $profile->name : 'Administrator',
        ]);
    }

    /**
     * API: Get filtered accounts list.
     */
    public function apiList(Request $request)
    {
        $this->ensureTenantConnection();
        $profile = $this->resolveCurrentUserProfile();
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);

        $query = Account::on('tenant')
            ->with(['owner', 'contacts', 'opportunities', 'purchaseDeals'])
            ->orderBy('id', 'desc');

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
        $accounts = $rawAccounts->map(function ($account) use ($fieldPermissions) {
            return $this->sanitizeAccount($account, $fieldPermissions);
        })->values();

        return response()->json($accounts);
    }

    /**
     * API: Get accounts metadata (custom fields, permissions, users, types, terms).
     */
    public function apiMeta(Request $request)
    {
        $this->ensureTenantConnection();
        $profile = $this->resolveCurrentUserProfile();
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);

        $customFields = CustomField::on('tenant')
            ->where('module', 'accounts')
            ->orderBy('id', 'asc')
            ->get();

        $users = TenantUser::on('tenant')->orderBy('name', 'asc')->get(['id', 'name', 'email']);

        return response()->json([
            'custom_fields' => $customFields,
            'field_permissions' => $fieldPermissions,
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
        $this->ensureTenantConnection();
        $profile = $this->resolveCurrentUserProfile();
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);

        $account = Account::on('tenant')->with(['owner', 'contacts'])->findOrFail($id);
        return response()->json($this->sanitizeAccount($account, $fieldPermissions));
    }

    /**
     * API: Store a newly created account.
     */
    public function apiStore(Request $request)
    {
        $this->ensureTenantConnection();
        $profile = $this->resolveCurrentUserProfile();
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);

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

        $account = Account::on('tenant')->create($validated);
        $account->load('owner');

        return response()->json([
            'success' => true,
            'message' => "Account '{$account->name}' created successfully!",
            'account' => $this->sanitizeAccount($account, $fieldPermissions),
        ], 201);
    }

    /**
     * API: Update an existing account.
     */
    public function apiUpdate(Request $request, $id)
    {
        $this->ensureTenantConnection();
        $profile = $this->resolveCurrentUserProfile();
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);

        $account = Account::on('tenant')->findOrFail($id);

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
        $account->update($updates);
        $account->load('owner');

        return response()->json([
            'success' => true,
            'message' => "Account '{$account->name}' updated successfully!",
            'account' => $this->sanitizeAccount($account, $fieldPermissions),
        ]);
    }

    /**
     * API: Delete an account.
     */
    public function apiDestroy(Request $request, $id)
    {
        $this->ensureTenantConnection();
        $account = Account::on('tenant')->findOrFail($id);
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
        $this->ensureTenantConnection();
        $validated = $request->validate([
            'owner_id' => 'nullable|integer',
            'assigned_to' => 'nullable|string|max:150',
        ]);

        $account = Account::on('tenant')->findOrFail($id);

        if (!empty($validated['owner_id'])) {
            $user = TenantUser::on('tenant')->find($validated['owner_id']);
            if ($user) {
                $account->owner_id = $user->id;
                $account->assigned_to = $user->name;
            }
        } elseif (!empty($validated['assigned_to'])) {
            $account->assigned_to = $validated['assigned_to'];
        }

        $account->save();
        $account->load('owner');

        $profile = $this->resolveCurrentUserProfile();
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);

        return response()->json([
            'success' => true,
            'message' => "Account ownership transferred to '{$account->assigned_to}'",
            'account' => $this->sanitizeAccount($account, $fieldPermissions),
        ]);
    }

    /**
     * API: Get Account 360 Degree Data (Overview, Contacts, Deals, POs, Shipments, Invoices).
     */
    public function api360(Request $request, $id)
    {
        $this->ensureTenantConnection();
        $profile = $this->resolveCurrentUserProfile();
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);

        $account = Account::on('tenant')
            ->with(['owner'])
            ->findOrFail($id);

        $sanitizedAccount = $this->sanitizeAccount($account, $fieldPermissions);

        // Fetch related entities
        $contacts = Contact::on('tenant')->where('account_id', $id)->orderBy('is_primary', 'desc')->get();
        $opportunities = Opportunity::on('tenant')->where('account_id', $id)->orderBy('id', 'desc')->get();
        $purchaseDeals = PurchaseDeal::on('tenant')->where('account_id', $id)->orderBy('id', 'desc')->get();
        $shipments = Shipment::on('tenant')->where('account_id', $id)->orderBy('id', 'desc')->get();
        $payments = Payment::on('tenant')->where('account_id', $id)->orderBy('id', 'desc')->get();
        $wires = WireReceived::on('tenant')->where('reconciled_account_id', $id)->orderBy('id', 'desc')->get();

        // If relations are completely empty (e.g. demo account before conversions), provide rich mock relations matching React
        if ($contacts->isEmpty() && $opportunities->isEmpty() && $purchaseDeals->isEmpty() && $shipments->isEmpty() && $payments->isEmpty()) {
            if ($account->type === 'Customer') {
                $contacts = collect([
                    (object)[
                        'id' => 101,
                        'first_name' => 'Elena',
                        'last_name' => 'Vance',
                        'title' => 'Chief Technology Officer',
                        'email' => 'elena.vance@' . ($account->website ? str_replace(['https://', 'http://', 'www.'], '', $account->website) : 'company.io'),
                        'phone' => $account->phone ?: '+1 (415) 890-1200',
                        'contact_role' => 'Executive Sponsor',
                        'is_primary' => true,
                    ],
                    (object)[
                        'id' => 102,
                        'first_name' => 'David',
                        'last_name' => 'Kowalski',
                        'title' => 'VP of Infrastructure',
                        'email' => 'd.kowalski@' . ($account->website ? str_replace(['https://', 'http://', 'www.'], '', $account->website) : 'company.io'),
                        'phone' => '+1 (415) 890-1244',
                        'contact_role' => 'Technical Decision Maker',
                        'is_primary' => false,
                    ]
                ]);

                $opportunities = collect([
                    (object)[
                        'id' => 201,
                        'name' => 'Enterprise Cloud Migration Q4',
                        'amount' => 185000.00,
                        'stage' => 'Proposal / Price Quote',
                        'probability' => 75,
                        'expected_close_date' => '2026-11-15',
                    ],
                    (object)[
                        'id' => 202,
                        'name' => 'Multi-Region High Availability Add-on',
                        'amount' => 45000.00,
                        'stage' => 'Negotiation / Review',
                        'probability' => 85,
                        'expected_close_date' => '2026-12-01',
                    ]
                ]);

                $shipments = collect([
                    (object)[
                        'id' => 301,
                        'carrier' => 'FedEx Freight Direct',
                        'tracking_number' => 'FX-9821039841',
                        'type' => 'High-Speed Hardware Appliance',
                        'status' => 'Delivered',
                        'estimated_delivery_date' => '2026-09-28',
                    ]
                ]);

                $payments = collect([
                    (object)[
                        'id' => 401,
                        'invoice_number' => 'INV-2026-0891',
                        'type' => 'Annual SaaS Subscription',
                        'amount' => 125000.00,
                        'paid_amount' => 125000.00,
                        'due_date' => '2026-10-30',
                        'status' => 'Paid',
                    ],
                    (object)[
                        'id' => 402,
                        'invoice_number' => 'INV-2026-1044',
                        'type' => 'Professional Implementation Services',
                        'amount' => 35000.00,
                        'paid_amount' => 0.00,
                        'due_date' => '2026-11-15',
                        'status' => 'Pending',
                    ]
                ]);
            } elseif ($account->type === 'Vendor') {
                $contacts = collect([
                    (object)[
                        'id' => 501,
                        'first_name' => 'Marcus',
                        'last_name' => 'Brody',
                        'title' => 'Director of Global Accounts',
                        'email' => 'mbrody@' . ($account->website ? str_replace(['https://', 'http://', 'www.'], '', $account->website) : 'vendor.com'),
                        'phone' => $account->phone ?: '+1 (408) 555-4321',
                        'contact_role' => 'Supplier Representative',
                        'is_primary' => true,
                    ]
                ]);

                $purchaseDeals = collect([
                    (object)[
                        'id' => 601,
                        'po_number' => 'PO-2026-781',
                        'title' => 'Batch 400x Silicon Micro-controllers',
                        'amount' => 240000.00,
                        'stage' => 'PO Issued / In Transit',
                        'expected_delivery_date' => '2026-11-10',
                    ],
                    (object)[
                        'id' => 602,
                        'po_number' => 'PO-2026-892',
                        'title' => 'Replacement Server Enclosures Q4',
                        'amount' => 65000.00,
                        'stage' => 'Order Confirmed',
                        'expected_delivery_date' => '2026-11-25',
                    ]
                ]);

                $shipments = collect([
                    (object)[
                        'id' => 701,
                        'carrier' => 'DHL Global Forwarding',
                        'tracking_number' => 'DHL-489102830',
                        'type' => 'Air Freight Palletized',
                        'status' => 'In Transit',
                        'estimated_delivery_date' => '2026-11-08',
                    ]
                ]);

                $payments = collect([
                    (object)[
                        'id' => 801,
                        'invoice_number' => 'BILL-2026-550',
                        'type' => 'Supplier Invoice (PO-781)',
                        'amount' => 240000.00,
                        'paid_amount' => 120000.00,
                        'due_date' => '2026-12-15',
                        'status' => 'Partial',
                    ]
                ]);
            }
        }

        return response()->json([
            'account' => $sanitizedAccount,
            'contacts' => $contacts,
            'opportunities' => $opportunities,
            'purchaseDeals' => $purchaseDeals,
            'shipments' => $shipments,
            'payments' => $payments,
            'wires' => $wires,
        ]);
    }
}
