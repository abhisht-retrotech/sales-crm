<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\PurchaseDeal;
use App\Models\Account;
use App\Models\Contact;
use App\Models\Opportunity;
use App\Models\TenantUser;
use App\Models\CustomField;
use App\Models\FieldPermission;
use App\Models\Profile;
use App\Models\Tenant;

class PurchaseDealController extends Controller
{
    /**
     * Procurement pipeline stages in canonical order.
     */
    public const STAGES = [
        'Draft',
        'RFQ Sent',
        'Quote Received',
        'PO Issued',
        'In Production',
        'Completed',
    ];

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

        return Profile::on('tenant')->where('name', 'like', '%admin%')->first()
            ?? Profile::on('tenant')->first();
    }

    /**
     * Get field permissions map for the current user's profile on purchase_deals.
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
            ->where('module', 'purchase_deals')
            ->get();

        foreach ($records as $r) {
            $perms[$r->field_name] = $r->permission ?: 'Read/Write';
        }

        return $perms;
    }

    /**
     * Sanitize a purchase deal record respecting field-level security permissions.
     */
    protected function sanitizePurchaseDeal(PurchaseDeal $deal, array $fieldPermissions): array
    {
        $data = $deal->toArray();
        $customData = is_array($deal->custom_data) ? $deal->custom_data : (json_decode($deal->custom_data ?? '[]', true) ?: []);

        // Computed helper attributes
        $data['vendor_name'] = $deal->account?->name ?? 'Unlinked Vendor';
        $data['account_name'] = $data['vendor_name'];
        $data['contact_name'] = $deal->contact ? trim(($deal->contact->first_name ?? '') . ' ' . ($deal->contact->last_name ?? '')) : null;
        $data['owner_name'] = $deal->assignedUser?->name ?? 'Unassigned';
        $data['assigned_to_name'] = $data['owner_name'];
        $data['amount_formatted'] = '$' . number_format((float)($deal->amount ?? 0), 2);
        $data['expected_delivery_date_formatted'] = $deal->expected_delivery_date ? date('M d, Y', strtotime($deal->expected_delivery_date)) : 'Pending Schedule';

        // Count linked revenue opportunities
        $linkedOpps = $deal->opportunities;
        $data['linked_opportunities_count'] = $linkedOpps ? $linkedOpps->count() : 0;
        $data['linked_opportunities'] = $linkedOpps ? $linkedOpps->map(function ($opp) {
            return [
                'id' => $opp->id,
                'name' => $opp->name,
                'amount' => $opp->amount,
                'stage' => $opp->stage,
            ];
        })->toArray() : [];

        // Apply permissions on standard attributes
        $protectedKeys = ['id', 'created_at', 'updated_at', 'deleted_at', 'account_id', 'contact_id', 'assigned_to', 'created_by', 'updated_by'];
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
                // Expose at root level for seamless table/kanban/filter access
                $data[$cfKey] = $cfVal;
            } else {
                $data['_hidden_' . $cfKey] = true;
            }
        }
        $data['custom_data'] = $sanitizedCustom;

        return $data;
    }

    /**
     * Ensure default dynamic custom fields exist for the purchase_deals module.
     */
    protected function ensureCustomFields()
    {
        try {
            if (CustomField::on('tenant')->where('module', 'purchase_deals')->count() > 0) {
                return;
            }

            $defaults = [
                [
                    'module' => 'purchase_deals',
                    'field_name' => 'cf_shipping_carrier_preferred',
                    'field_label' => 'Preferred Freight Carrier',
                    'data_type' => 'picklist',
                    'options' => 'FedEx Priority Freight, DHL Express Air, Maersk Ocean Cargo, Expeditors Ground Logistics',
                    'is_required' => false,
                    'help_text' => 'Designated primary global shipping logistics partner for this PO',
                ],
                [
                    'module' => 'purchase_deals',
                    'field_name' => 'cf_quality_inspection_tier',
                    'field_label' => 'Quality QA Inspection Tier',
                    'data_type' => 'picklist',
                    'options' => 'Level 1 (Standard Receipt Verification), Level 2 (Batch Sampling & Tech Inspection), Level 3 (Full Cryptographic & Hardware QA)',
                    'is_required' => false,
                    'help_text' => 'Rigorous hardware & component incoming verification protocol',
                ],
                [
                    'module' => 'purchase_deals',
                    'field_name' => 'cf_supplier_contract_sla',
                    'field_label' => 'Supplier Delivery SLA',
                    'data_type' => 'picklist',
                    'options' => '99.9% On-Time Delivery Guarantee, Standard Commercial SLA, Custom Expedited Logistics Agreement',
                    'is_required' => false,
                    'help_text' => 'Contractual lead-time guarantee and on-time performance warranty',
                ],
                [
                    'module' => 'purchase_deals',
                    'field_name' => 'cf_incoterms',
                    'field_label' => 'International Incoterms',
                    'data_type' => 'picklist',
                    'options' => 'FOB (Free On Board), CIF (Cost Insurance Freight), DDP (Delivered Duty Paid), EXW (Ex Works)',
                    'is_required' => false,
                    'help_text' => 'Standard international commercial shipping delivery risk allocation',
                ],
                [
                    'module' => 'purchase_deals',
                    'field_name' => 'cf_procurement_rebate_percentage',
                    'field_label' => 'Supplier Volume Rebate %',
                    'data_type' => 'number',
                    'options' => null,
                    'is_required' => false,
                    'help_text' => 'Negotiated supplier rebate or volume discount percentage (e.g. 3.5%)',
                ],
            ];

            foreach ($defaults as $cf) {
                CustomField::on('tenant')->updateOrCreate(
                    ['module' => 'purchase_deals', 'field_name' => $cf['field_name']],
                    $cf
                );
            }
        } catch (\Throwable $e) {
            // Silently continue
        }
    }

    /**
     * Seed realistic initial enterprise supplier purchase deals if database table is completely empty.
     */
    protected function ensureDemoPurchaseDeals()
    {
        try {
            if (PurchaseDeal::on('tenant')->count() > 0) {
                return;
            }

            $firstUser = TenantUser::on('tenant')->first();
            $userId = $firstUser?->id;

            $accounts = Account::on('tenant')->get()->keyBy('name');

            // Helper to get or create supplier account
            $getSupplier = function ($name, $industry = 'Hardware & Logistics') use ($accounts, $userId) {
                $acc = $accounts->get($name);
                if (!$acc) {
                    $acc = Account::on('tenant')->create([
                        'name' => $name,
                        'type' => 'Vendor',
                        'industry' => $industry,
                        'phone' => '+1 (800) 555-0199',
                        'website' => 'https://' . Str::slug($name) . '.com',
                        'owner_id' => $userId,
                        'assigned_to' => $userId,
                    ]);
                }
                return $acc;
            };

            $demoDeals = [
                [
                    'supplier_name' => 'Apex Industrial Components',
                    'po_number' => 'PO-2026-8812',
                    'title' => 'High-Throughput Optical Switches & Transceivers',
                    'amount' => 138500.00,
                    'currency' => 'USD',
                    'stage' => 'PO Issued',
                    'expected_delivery_date' => date('Y-m-d', strtotime('+14 days')),
                    'payment_terms' => 'Net 30',
                    'notes' => 'Bulk procurement batch for Q4 data center expansion. Requires certified optical test report prior to customs dispatch.',
                    'custom_data' => [
                        'cf_shipping_carrier_preferred' => 'FedEx Priority Freight',
                        'cf_quality_inspection_tier' => 'Level 2 (Batch Sampling & Tech Inspection)',
                        'cf_supplier_contract_sla' => '99.9% On-Time Delivery Guarantee',
                        'cf_incoterms' => 'DDP (Delivered Duty Paid)',
                        'cf_procurement_rebate_percentage' => '4.5',
                    ],
                ],
                [
                    'supplier_name' => 'Foxconn Precision Industrial',
                    'po_number' => 'PO-2026-8815',
                    'title' => 'Custom Server Chassis & Cryptographic HSM Enclosures',
                    'amount' => 72500.00,
                    'currency' => 'USD',
                    'stage' => 'In Production',
                    'expected_delivery_date' => date('Y-m-d', strtotime('+28 days')),
                    'payment_terms' => 'Net 45',
                    'notes' => 'Precision CNC fabrication active at Taipei facility. Weekly milestone photos required with anti-tamper seal verification.',
                    'custom_data' => [
                        'cf_shipping_carrier_preferred' => 'DHL Express Air',
                        'cf_quality_inspection_tier' => 'Level 3 (Full Cryptographic & Hardware QA)',
                        'cf_supplier_contract_sla' => 'Custom Expedited Logistics Agreement',
                        'cf_incoterms' => 'FOB (Free On Board)',
                        'cf_procurement_rebate_percentage' => '3.0',
                    ],
                ],
                [
                    'supplier_name' => 'Maersk Ocean Logistics Global',
                    'po_number' => 'PO-2026-8819',
                    'title' => 'International Intermodal Freight & Port Cleared Cargo',
                    'amount' => 66000.00,
                    'currency' => 'USD',
                    'stage' => 'Completed',
                    'expected_delivery_date' => date('Y-m-d', strtotime('-5 days')),
                    'payment_terms' => 'Advance Wire',
                    'notes' => 'Full container shipment received at Long Beach terminal and transferred to regional staging depot. 100% quantity verified.',
                    'custom_data' => [
                        'cf_shipping_carrier_preferred' => 'Maersk Ocean Cargo',
                        'cf_quality_inspection_tier' => 'Level 1 (Standard Receipt Verification)',
                        'cf_supplier_contract_sla' => 'Standard Commercial SLA',
                        'cf_incoterms' => 'CIF (Cost Insurance Freight)',
                        'cf_procurement_rebate_percentage' => '2.5',
                    ],
                ],
                [
                    'supplier_name' => 'Infineon Semiconductor Direct',
                    'po_number' => 'PO-2026-8824',
                    'title' => 'Hardware Security Microcontrollers & TPM 2.0 Chips',
                    'amount' => 45000.00,
                    'currency' => 'USD',
                    'stage' => 'Quote Received',
                    'expected_delivery_date' => date('Y-m-d', strtotime('+45 days')),
                    'payment_terms' => 'Net 60',
                    'notes' => 'Formal quotation received from European sales rep. Negotiating bulk pricing rebate for 5,000 unit batch.',
                    'custom_data' => [
                        'cf_shipping_carrier_preferred' => 'Expeditors Ground Logistics',
                        'cf_quality_inspection_tier' => 'Level 3 (Full Cryptographic & Hardware QA)',
                        'cf_supplier_contract_sla' => '99.9% On-Time Delivery Guarantee',
                        'cf_incoterms' => 'DDP (Delivered Duty Paid)',
                        'cf_procurement_rebate_percentage' => '5.0',
                    ],
                ],
                [
                    'supplier_name' => 'Nordic Power Solutions',
                    'po_number' => 'PO-2026-8830',
                    'title' => 'Modular Redundant Power Supplies (1200W Titanium)',
                    'amount' => 28400.00,
                    'currency' => 'USD',
                    'stage' => 'Draft',
                    'expected_delivery_date' => date('Y-m-d', strtotime('+35 days')),
                    'payment_terms' => 'Net 30',
                    'notes' => 'Draft requisition under engineering specifications review. Pending secondary supplier quote for comparison.',
                    'custom_data' => [
                        'cf_shipping_carrier_preferred' => 'FedEx Priority Freight',
                        'cf_quality_inspection_tier' => 'Level 2 (Batch Sampling & Tech Inspection)',
                        'cf_supplier_contract_sla' => 'Standard Commercial SLA',
                        'cf_incoterms' => 'EXW (Ex Works)',
                        'cf_procurement_rebate_percentage' => '1.5',
                    ],
                ],
            ];

            foreach ($demoDeals as $d) {
                $supplier = $getSupplier($d['supplier_name']);
                PurchaseDeal::on('tenant')->create([
                    'account_id' => $supplier->id,
                    'po_number' => $d['po_number'],
                    'title' => $d['title'],
                    'amount' => $d['amount'],
                    'currency' => $d['currency'],
                    'stage' => $d['stage'],
                    'expected_delivery_date' => $d['expected_delivery_date'],
                    'payment_terms' => $d['payment_terms'],
                    'notes' => $d['notes'],
                    'custom_data' => $d['custom_data'],
                    'assigned_to' => $userId,
                ]);
            }
        } catch (\Throwable $e) {
            // Silently continue
        }
    }

    /**
     * Display the main Purchase Deals & Procurement view.
     */
    public function index()
    {
        $this->ensureTenantConnection();
        $this->ensureCustomFields();
        $this->ensureDemoPurchaseDeals();

        $profile = $this->resolveCurrentUserProfile();
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);

        $customFields = CustomField::on('tenant')
            ->where('module', 'purchase_deals')
            ->get();

        $rawDeals = PurchaseDeal::on('tenant')
            ->with(['account', 'contact', 'opportunities', 'assignedUser'])
            ->orderBy('id', 'desc')
            ->get();

        $initialPurchaseDeals = $rawDeals->map(function ($deal) use ($fieldPermissions) {
            return $this->sanitizePurchaseDeal($deal, $fieldPermissions);
        })->toArray();

        $accounts = Account::on('tenant')
            ->select('id', 'name', 'type', 'phone', 'website')
            ->orderBy('name')
            ->get();

        $contacts = Contact::on('tenant')
            ->select('id', 'account_id', 'first_name', 'last_name', 'email', 'phone')
            ->orderBy('first_name')
            ->get();

        $opportunities = Opportunity::on('tenant')
            ->select('id', 'name', 'amount', 'stage', 'account_id')
            ->orderBy('name')
            ->get();

        $users = TenantUser::on('tenant')
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        return view('purchase_deals', [
            'initialPurchaseDeals' => $initialPurchaseDeals,
            'customFields' => $customFields,
            'fieldPermissions' => $fieldPermissions,
            'accounts' => $accounts,
            'contacts' => $contacts,
            'opportunities' => $opportunities,
            'users' => $users,
            'stages' => self::STAGES,
            'currentUserProfile' => $profile?->name ?? 'Administrator',
        ]);
    }

    /**
     * Display dedicated Purchase Deal 360 view page.
     */
    public function show($id)
    {
        $this->ensureTenantConnection();
        $this->ensureCustomFields();

        $profile = $this->resolveCurrentUserProfile();
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);

        $deal = PurchaseDeal::on('tenant')
            ->with(['account', 'contact', 'opportunities', 'shipments', 'payments', 'assignedUser'])
            ->findOrFail($id);

        $sanitizedDeal = $this->sanitizePurchaseDeal($deal, $fieldPermissions);

        $customFields = CustomField::on('tenant')
            ->where('module', 'purchase_deals')
            ->get();

        $users = TenantUser::on('tenant')
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        $accounts = Account::on('tenant')
            ->select('id', 'name', 'type')
            ->orderBy('name')
            ->get();

        $contacts = Contact::on('tenant')
            ->where('account_id', $deal->account_id)
            ->select('id', 'first_name', 'last_name', 'email', 'phone')
            ->orderBy('first_name')
            ->get();

        return view('purchase_deals.show', [
            'purchaseDeal' => $sanitizedDeal,
            'customFields' => $customFields,
            'fieldPermissions' => $fieldPermissions,
            'users' => $users,
            'accounts' => $accounts,
            'contacts' => $contacts,
            'stages' => self::STAGES,
            'currentUserProfile' => $profile?->name ?? 'Administrator',
        ]);
    }

    /**
     * API: Get all purchase deals with optional search & stage filter.
     */
    public function apiList(Request $request)
    {
        $this->ensureTenantConnection();
        $profile = $this->resolveCurrentUserProfile();
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);

        $query = PurchaseDeal::on('tenant')
            ->with(['account', 'contact', 'opportunities', 'assignedUser'])
            ->orderBy('id', 'desc');

        if ($request->filled('stage') && $request->stage !== 'all') {
            $query->where('stage', $request->stage);
        }

        if ($request->filled('search')) {
            $q = '%' . $request->search . '%';
            $query->where(function ($sub) use ($q) {
                $sub->where('po_number', 'like', $q)
                    ->orWhere('title', 'like', $q)
                    ->orWhere('notes', 'like', $q)
                    ->orWhereHas('account', function ($accQ) use ($q) {
                        $accQ->where('name', 'like', $q);
                    });
            });
        }

        $deals = $query->get()->map(function ($deal) use ($fieldPermissions) {
            return $this->sanitizePurchaseDeal($deal, $fieldPermissions);
        });

        return response()->json([
            'success' => true,
            'purchase_deals' => $deals,
            'total' => $deals->count(),
        ]);
    }

    /**
     * API: Get metadata (custom fields, field permissions, stages, users, accounts).
     */
    public function apiMeta()
    {
        $this->ensureTenantConnection();
        $this->ensureCustomFields();

        $profile = $this->resolveCurrentUserProfile();
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);

        $customFields = CustomField::on('tenant')
            ->where('module', 'purchase_deals')
            ->get();

        $accounts = Account::on('tenant')
            ->select('id', 'name', 'type')
            ->orderBy('name')
            ->get();

        $contacts = Contact::on('tenant')
            ->select('id', 'account_id', 'first_name', 'last_name', 'email')
            ->orderBy('first_name')
            ->get();

        $opportunities = Opportunity::on('tenant')
            ->select('id', 'name', 'amount', 'stage')
            ->orderBy('name')
            ->get();

        $users = TenantUser::on('tenant')
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'custom_fields' => $customFields,
            'field_permissions' => $fieldPermissions,
            'accounts' => $accounts,
            'contacts' => $contacts,
            'opportunities' => $opportunities,
            'users' => $users,
            'stages' => self::STAGES,
            'current_profile' => $profile?->name ?? 'Administrator',
        ]);
    }

    /**
     * API: Store a new purchase deal with dynamic custom fields & permissions.
     */
    public function apiStore(Request $request)
    {
        $this->ensureTenantConnection();
        $profile = $this->resolveCurrentUserProfile();
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);

        // Auto-generate PO Number if not provided
        $poNumber = trim($request->input('po_number', ''));
        if (empty($poNumber)) {
            $year = date('Y');
            $rand = mt_rand(1000, 9999);
            $poNumber = "PO-{$year}-{$rand}";
            while (PurchaseDeal::on('tenant')->where('po_number', $poNumber)->exists()) {
                $rand = mt_rand(1000, 9999);
                $poNumber = "PO-{$year}-{$rand}";
            }
        }

        // Validate required fields
        $title = trim($request->input('title', ''));
        if (empty($title)) {
            return response()->json(['success' => false, 'message' => 'PO Order Title is required.'], 422);
        }

        $accountId = $request->input('account_id');
        if (empty($accountId)) {
            // Check if vendor_name was sent
            $vendorName = trim($request->input('vendor_name', ''));
            if (!empty($vendorName)) {
                $acc = Account::on('tenant')->firstOrCreate(
                    ['name' => $vendorName],
                    ['type' => 'Vendor']
                );
                $accountId = $acc->id;
            } else {
                $firstAcc = Account::on('tenant')->first();
                $accountId = $firstAcc ? $firstAcc->id : null;
            }
        }

        if (empty($accountId)) {
            return response()->json(['success' => false, 'message' => 'Supplier / Vendor Account is required.'], 422);
        }

        $amount = (float)$request->input('amount', 0);
        $currency = $request->input('currency', 'USD');
        $stage = $request->input('stage', 'Draft');
        if (!in_array($stage, self::STAGES)) {
            $stage = 'Draft';
        }

        $expectedDeliveryDate = $request->input('expected_delivery_date') ?: null;
        $paymentTerms = $request->input('payment_terms', 'Net 30');
        $notes = $request->input('notes', '');
        $contactId = $request->input('contact_id') ?: null;
        $assignedTo = $request->input('assigned_to') ?: Auth::id();

        // Process dynamic custom fields
        $customFields = CustomField::on('tenant')->where('module', 'purchase_deals')->get();
        $customData = [];
        $rawCustom = $request->input('custom_data', []);
        if (is_string($rawCustom)) {
            $rawCustom = json_decode($rawCustom, true) ?: [];
        }

        foreach ($customFields as $cf) {
            $fn = $cf->field_name;
            $perm = $fieldPermissions[$fn] ?? 'Read/Write';
            if ($perm === 'Read-Only') {
                continue; // Cannot set read-only on create
            }
            if ($request->has($fn)) {
                $customData[$fn] = $request->input($fn);
            } elseif (isset($rawCustom[$fn])) {
                $customData[$fn] = $rawCustom[$fn];
            }
        }

        $deal = PurchaseDeal::on('tenant')->create([
            'account_id' => $accountId,
            'contact_id' => $contactId,
            'po_number' => $poNumber,
            'title' => $title,
            'amount' => $amount,
            'currency' => $currency,
            'stage' => $stage,
            'expected_delivery_date' => $expectedDeliveryDate,
            'payment_terms' => $paymentTerms,
            'notes' => $notes,
            'custom_data' => $customData,
            'assigned_to' => $assignedTo,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        // Link with Opportunity if supplied
        $oppId = $request->input('opportunity_id');
        if (!empty($oppId)) {
            $opp = Opportunity::on('tenant')->find($oppId);
            if ($opp) {
                $opp->update(['purchase_deal_id' => $deal->id]);
            }
        }

        $sanitized = $this->sanitizePurchaseDeal($deal->fresh(['account', 'contact', 'opportunities', 'assignedUser']), $fieldPermissions);

        return response()->json([
            'success' => true,
            'message' => "Purchase Order {$deal->po_number} created successfully!",
            'purchase_deal' => $sanitized,
        ], 201);
    }

    /**
     * API: Get single purchase deal.
     */
    public function apiShow($id)
    {
        $this->ensureTenantConnection();
        $profile = $this->resolveCurrentUserProfile();
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);

        $deal = PurchaseDeal::on('tenant')
            ->with(['account', 'contact', 'opportunities', 'shipments', 'payments', 'assignedUser'])
            ->find($id);

        if (!$deal) {
            return response()->json(['success' => false, 'message' => 'Purchase deal not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'purchase_deal' => $this->sanitizePurchaseDeal($deal, $fieldPermissions),
        ]);
    }

    /**
     * API: Update a purchase deal.
     */
    public function apiUpdate(Request $request, $id)
    {
        $this->ensureTenantConnection();
        $profile = $this->resolveCurrentUserProfile();
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);

        $deal = PurchaseDeal::on('tenant')->find($id);
        if (!$deal) {
            return response()->json(['success' => false, 'message' => 'Purchase deal not found.'], 404);
        }

        $fillableStandard = [
            'title', 'account_id', 'contact_id', 'po_number', 'amount',
            'currency', 'stage', 'expected_delivery_date', 'payment_terms',
            'notes', 'assigned_to'
        ];

        $updateData = [];
        foreach ($fillableStandard as $key) {
            $perm = $fieldPermissions[$key] ?? 'Read/Write';
            if ($perm === 'Read-Only') {
                continue; // Cannot edit Read-Only
            }
            if ($request->has($key)) {
                $updateData[$key] = $request->input($key);
            }
        }

        // Handle dynamic custom fields
        $customFields = CustomField::on('tenant')->where('module', 'purchase_deals')->get();
        $currentCustom = is_array($deal->custom_data) ? $deal->custom_data : (json_decode($deal->custom_data ?? '[]', true) ?: []);
        $rawCustom = $request->input('custom_data', []);
        if (is_string($rawCustom)) {
            $rawCustom = json_decode($rawCustom, true) ?: [];
        }

        foreach ($customFields as $cf) {
            $fn = $cf->field_name;
            $perm = $fieldPermissions[$fn] ?? 'Read/Write';
            if ($perm === 'Read-Only') {
                continue; // Preserve existing value
            }
            if ($request->has($fn)) {
                $currentCustom[$fn] = $request->input($fn);
            } elseif (isset($rawCustom[$fn])) {
                $currentCustom[$fn] = $rawCustom[$fn];
            }
        }
        $updateData['custom_data'] = $currentCustom;
        $updateData['updated_by'] = Auth::id();

        $deal->update($updateData);

        // Update linked opportunity if supplied
        if ($request->has('opportunity_id')) {
            $newOppId = $request->input('opportunity_id');
            // Unlink prior
            Opportunity::on('tenant')->where('purchase_deal_id', $deal->id)->update(['purchase_deal_id' => null]);
            if (!empty($newOppId)) {
                $newOpp = Opportunity::on('tenant')->find($newOppId);
                if ($newOpp) {
                    $newOpp->update(['purchase_deal_id' => $deal->id]);
                }
            }
        }

        $sanitized = $this->sanitizePurchaseDeal($deal->fresh(['account', 'contact', 'opportunities', 'assignedUser']), $fieldPermissions);

        return response()->json([
            'success' => true,
            'message' => "Purchase Order {$deal->po_number} updated successfully!",
            'purchase_deal' => $sanitized,
        ]);
    }

    /**
     * API: Quick Stage update (for Kanban drag & drop).
     */
    public function apiUpdateStage(Request $request, $id)
    {
        $this->ensureTenantConnection();
        $stage = $request->input('stage');

        if (!in_array($stage, self::STAGES)) {
            return response()->json(['success' => false, 'message' => 'Invalid procurement stage.'], 422);
        }

        $deal = PurchaseDeal::on('tenant')->find($id);
        if (!$deal) {
            return response()->json(['success' => false, 'message' => 'Purchase deal not found.'], 404);
        }

        $deal->update([
            'stage' => $stage,
            'updated_by' => Auth::id(),
        ]);

        $profile = $this->resolveCurrentUserProfile();
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);
        $sanitized = $this->sanitizePurchaseDeal($deal->fresh(['account', 'contact', 'opportunities', 'assignedUser']), $fieldPermissions);

        return response()->json([
            'success' => true,
            'message' => "Stage updated to {$stage}!",
            'purchase_deal' => $sanitized,
        ]);
    }

    /**
     * API: Transfer owner.
     */
    public function apiTransfer(Request $request, $id)
    {
        $this->ensureTenantConnection();
        $newOwnerId = $request->input('assigned_to');
        if (empty($newOwnerId)) {
            return response()->json(['success' => false, 'message' => 'New owner is required.'], 422);
        }

        $deal = PurchaseDeal::on('tenant')->find($id);
        if (!$deal) {
            return response()->json(['success' => false, 'message' => 'Purchase deal not found.'], 404);
        }

        $deal->update([
            'assigned_to' => $newOwnerId,
            'updated_by' => Auth::id(),
        ]);

        $profile = $this->resolveCurrentUserProfile();
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);
        $sanitized = $this->sanitizePurchaseDeal($deal->fresh(['account', 'contact', 'opportunities', 'assignedUser']), $fieldPermissions);

        return response()->json([
            'success' => true,
            'message' => "Purchase deal transferred successfully to {$sanitized['owner_name']}!",
            'purchase_deal' => $sanitized,
        ]);
    }

    /**
     * API: Delete a purchase deal.
     */
    public function apiDestroy($id)
    {
        $this->ensureTenantConnection();
        $deal = PurchaseDeal::on('tenant')->find($id);
        if (!$deal) {
            return response()->json(['success' => false, 'message' => 'Purchase deal not found.'], 404);
        }

        $poNumber = $deal->po_number;
        $deal->delete();

        return response()->json([
            'success' => true,
            'message' => "Purchase Order {$poNumber} has been deleted.",
        ]);
    }

    /**
     * API: 360 View details.
     */
    public function api360($id)
    {
        $this->ensureTenantConnection();
        $profile = $this->resolveCurrentUserProfile();
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);

        $deal = PurchaseDeal::on('tenant')
            ->with(['account', 'contact', 'opportunities', 'shipments', 'payments', 'assignedUser'])
            ->find($id);

        if (!$deal) {
            return response()->json(['success' => false, 'message' => 'Purchase deal not found.'], 404);
        }

        $sanitized = $this->sanitizePurchaseDeal($deal, $fieldPermissions);

        return response()->json([
            'success' => true,
            'deal' => $sanitized,
            'vendor' => $deal->account,
            'contact' => $deal->contact,
            'opportunities' => $deal->opportunities,
            'shipments' => $deal->shipments,
            'payments' => $deal->payments,
        ]);
    }
}
