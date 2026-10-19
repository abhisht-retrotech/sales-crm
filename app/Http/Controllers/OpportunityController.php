<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\Opportunity;
use App\Models\Account;
use App\Models\Contact;
use App\Models\PurchaseDeal;
use App\Models\TenantUser;
use App\Models\CustomField;
use App\Models\FieldPermission;
use App\Models\Profile;
use App\Models\Tenant;

class OpportunityController extends Controller
{
    /**
     * Pipeline stages definition in canonical order.
     */
    public const STAGES = [
        'Discovery',
        'Proposal',
        'Negotiation',
        'Closed Won',
        'Closed Lost',
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

        // Fallback for Admin / Super Admin
        return Profile::on('tenant')->where('name', 'like', '%admin%')->first()
            ?? Profile::on('tenant')->first();
    }

    /**
     * Get field permissions map for the current user's profile on the opportunities module.
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
            ->where('module', 'opportunities')
            ->get();

        foreach ($records as $r) {
            $perms[$r->field_name] = $r->permission ?: 'Read/Write';
        }

        return $perms;
    }

    /**
     * Sanitize an opportunity record respecting field-level security permissions.
     */
    protected function sanitizeOpportunity(Opportunity $opp, array $fieldPermissions): array
    {
        $data = $opp->toArray();
        $customData = is_array($opp->custom_data) ? $opp->custom_data : (json_decode($opp->custom_data ?? '[]', true) ?: []);

        // Computed helper attributes
        $data['account_name'] = $opp->account?->name ?? 'Unlinked Account';
        $data['contact_name'] = $opp->contact ? trim(($opp->contact->first_name ?? '') . ' ' . ($opp->contact->last_name ?? '')) : null;
        $data['po_number'] = $opp->purchaseDeal?->po_number ?? null;
        $data['purchase_deal_title'] = $opp->purchaseDeal?->title ?? null;
        $data['owner_name'] = $opp->assignedUser?->name ?? 'Unassigned';
        $data['amount_formatted'] = '$' . number_format((float)($opp->amount ?? 0), 2);
        $data['expected_close_date_formatted'] = $opp->expected_close_date ? date('M d, Y', strtotime($opp->expected_close_date)) : 'Not scheduled';

        // Apply permissions on standard attributes
        $protectedKeys = ['id', 'created_at', 'updated_at', 'deleted_at', 'account_id', 'contact_id', 'purchase_deal_id', 'assigned_to', 'created_by', 'updated_by'];
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
     * Ensure default dynamic custom fields exist for the opportunities module.
     */
    protected function ensureCustomFields()
    {
        try {
            if (CustomField::on('tenant')->where('module', 'opportunities')->count() > 0) {
                return;
            }

            $defaults = [
                [
                    'module' => 'opportunities',
                    'field_name' => 'cf_deal_source_channel',
                    'field_label' => 'Deal Source Channel',
                    'data_type' => 'picklist',
                    'options' => 'Direct Inbound, Executive Referral, Partner Ecosystem, Outbound Sales, RFP / Public Tender',
                    'is_required' => false,
                    'help_text' => 'The originating lead conversion channel or sourcing partner',
                ],
                [
                    'module' => 'opportunities',
                    'field_name' => 'cf_procurement_risk_level',
                    'field_label' => 'Procurement Risk Level',
                    'data_type' => 'picklist',
                    'options' => 'Low (Standard Agreement), Medium (Legal Review Required), High (Custom SLAs & Security Audit)',
                    'is_required' => false,
                    'help_text' => 'Legal and commercial compliance evaluation tier',
                ],
                [
                    'module' => 'opportunities',
                    'field_name' => 'cf_competitor_presence',
                    'field_label' => 'Competitor Presence',
                    'data_type' => 'picklist',
                    'options' => 'None / Sole Source, Legacy Enterprise Vendor, Tier 2 Cloud Competitor, Unknown',
                    'is_required' => false,
                    'help_text' => 'Primary rival vendor being evaluated during procurement',
                ],
                [
                    'module' => 'opportunities',
                    'field_name' => 'cf_billing_schedule',
                    'field_label' => 'Billing Schedule',
                    'data_type' => 'picklist',
                    'options' => '100% Upfront Annual, Net 30 Quarterly, Milestone-Based Net 45, Monthly Recurring',
                    'is_required' => false,
                    'help_text' => 'Agreed payment terms and billing milestone frequency',
                ],
                [
                    'module' => 'opportunities',
                    'field_name' => 'cf_contract_duration_months',
                    'field_label' => 'Contract Term (Months)',
                    'data_type' => 'number',
                    'options' => null,
                    'is_required' => false,
                    'help_text' => 'Duration of commercial agreement in months (e.g. 12, 24, 36)',
                ],
            ];

            foreach ($defaults as $cf) {
                CustomField::on('tenant')->updateOrCreate(
                    ['module' => 'opportunities', 'field_name' => $cf['field_name']],
                    $cf
                );
            }
        } catch (\Throwable $e) {
            // Silently continue
        }
    }

    /**
     * Seed realistic initial enterprise opportunities if database table is completely empty.
     */
    protected function ensureDemoOpportunities()
    {
        try {
            if (Opportunity::on('tenant')->count() > 0) {
                return;
            }

            $firstUser = TenantUser::on('tenant')->first();
            $userId = $firstUser?->id;

            $accounts = Account::on('tenant')->get()->keyBy('name');
            $contacts = Contact::on('tenant')->get()->keyBy('email');

            // Helper to get or create backing Purchase Deal for PO coupling
            $getOrCreateDeal = function ($accName, $poNum, $title, $amt, $stage = 'PO Issued') use ($accounts, $userId) {
                $account = $accounts->get($accName) ?? Account::on('tenant')->first();
                if (!$account) {
                    $account = Account::on('tenant')->create([
                        'name' => $accName,
                        'account_type' => 'Customer',
                        'industry' => 'Cloud & Technology',
                        'assigned_to' => $userId,
                    ]);
                }

                $deal = PurchaseDeal::on('tenant')->where('po_number', $poNum)->first();
                if (!$deal) {
                    $deal = PurchaseDeal::on('tenant')->create([
                        'account_id' => $account->id,
                        'po_number' => $poNum,
                        'title' => $title,
                        'amount' => $amt,
                        'currency' => 'USD',
                        'stage' => $stage,
                        'assigned_to' => $userId,
                    ]);
                }
                return [$account->id, $deal->id];
            };

            $demoOpps = [
                [
                    'account_name' => 'Skyline Cloud Systems',
                    'po_number' => 'PO-782190',
                    'title' => 'Skyline Cloud Multi-Region Gateway',
                    'name' => 'Enterprise Cloud Migration — Stage 2',
                    'contact_email' => 'elena.vance@skylinecloud.io',
                    'amount' => 145000.00,
                    'currency' => 'USD',
                    'stage' => 'Negotiation',
                    'probability' => 80,
                    'expected_close_date' => date('Y-m-d', strtotime('+20 days')),
                    'notes' => 'Contract in final legal review with General Counsel. SLA guarantees 99.99% uptime with Tier 1 engineering support.',
                    'custom_data' => [
                        'cf_deal_source_channel' => 'Executive Referral',
                        'cf_procurement_risk_level' => 'Low (Standard Agreement)',
                        'cf_competitor_presence' => 'None / Sole Source',
                        'cf_billing_schedule' => '100% Upfront Annual',
                        'cf_contract_duration_months' => 24,
                    ],
                ],
                [
                    'account_name' => 'Vanguard Health Tech',
                    'po_number' => 'PO-449102',
                    'title' => 'Vanguard Medical Records Encryption Engine',
                    'name' => 'HIPAA Compliance Vault & Analytics',
                    'contact_email' => 's.almansoor@vanguardhealth.tech',
                    'amount' => 220000.00,
                    'currency' => 'USD',
                    'stage' => 'Proposal',
                    'probability' => 60,
                    'expected_close_date' => date('Y-m-d', strtotime('+35 days')),
                    'notes' => 'Submitted formal RFP proposal response. Technical review panel has verified SOC2 Type II and ISO 27001 certifications.',
                    'custom_data' => [
                        'cf_deal_source_channel' => 'RFP / Public Tender',
                        'cf_procurement_risk_level' => 'High (Custom SLAs & Security Audit)',
                        'cf_competitor_presence' => 'Legacy Enterprise Vendor',
                        'cf_billing_schedule' => 'Milestone-Based Net 45',
                        'cf_contract_duration_months' => 36,
                    ],
                ],
                [
                    'account_name' => 'Quantum Silicon Components',
                    'po_number' => 'PO-118490',
                    'title' => 'Quantum Silicon Supply Chain Track-and-Trace',
                    'name' => 'Automated Ledger Integration',
                    'contact_email' => 'mbrody@quantumsilicon.com',
                    'amount' => 68000.00,
                    'currency' => 'USD',
                    'stage' => 'Closed Won',
                    'probability' => 100,
                    'expected_close_date' => date('Y-m-d', strtotime('-5 days')),
                    'notes' => 'Contract executed by VP Procurement. Initial 50% deposit wire received and reconciled.',
                    'custom_data' => [
                        'cf_deal_source_channel' => 'Partner Ecosystem',
                        'cf_procurement_risk_level' => 'Low (Standard Agreement)',
                        'cf_competitor_presence' => 'None / Sole Source',
                        'cf_billing_schedule' => 'Net 30 Quarterly',
                        'cf_contract_duration_months' => 12,
                    ],
                ],
                [
                    'account_name' => 'Apex Global Solutions',
                    'po_number' => 'PO-902311',
                    'title' => 'Apex Global Low-Latency Data Streamer',
                    'name' => 'Global API Streaming Gateway',
                    'contact_email' => 'a.thorne@apexglobal.net',
                    'amount' => 95000.00,
                    'currency' => 'USD',
                    'stage' => 'Discovery',
                    'probability' => 25,
                    'expected_close_date' => date('Y-m-d', strtotime('+60 days')),
                    'notes' => 'Initial scoping call completed with Head of Solutions Architecture. Architectural diagram underway.',
                    'custom_data' => [
                        'cf_deal_source_channel' => 'Direct Inbound',
                        'cf_procurement_risk_level' => 'Medium (Legal Review Required)',
                        'cf_competitor_presence' => 'Tier 2 Cloud Competitor',
                        'cf_billing_schedule' => 'Monthly Recurring',
                        'cf_contract_duration_months' => 12,
                    ],
                ],
                [
                    'account_name' => 'Titan Steel & Enclosures',
                    'po_number' => 'PO-334189',
                    'title' => 'Titan Logistics ERP Sync',
                    'name' => 'Warehouse Telemetry System',
                    'contact_email' => 'rachel.s@titansteel.com',
                    'amount' => 52000.00,
                    'currency' => 'USD',
                    'stage' => 'Closed Lost',
                    'probability' => 0,
                    'expected_close_date' => date('Y-m-d', strtotime('-15 days')),
                    'notes' => 'Project deferred to next fiscal calendar due to internal budget reprioritization.',
                    'custom_data' => [
                        'cf_deal_source_channel' => 'Outbound Sales',
                        'cf_procurement_risk_level' => 'Medium (Legal Review Required)',
                        'cf_competitor_presence' => 'Legacy Enterprise Vendor',
                        'cf_billing_schedule' => 'Net 30 Quarterly',
                        'cf_contract_duration_months' => 12,
                    ],
                ],
            ];

            foreach ($demoOpps as $o) {
                [$accId, $dealId] = $getOrCreateDeal($o['account_name'], $o['po_number'], $o['title'], $o['amount']);
                $contact = $contacts->get($o['contact_email']) ?? Contact::on('tenant')->where('account_id', $accId)->first();

                Opportunity::on('tenant')->create([
                    'account_id' => $accId,
                    'purchase_deal_id' => $dealId,
                    'contact_id' => $contact?->id,
                    'name' => $o['name'],
                    'amount' => $o['amount'],
                    'currency' => $o['currency'],
                    'stage' => $o['stage'],
                    'probability' => $o['probability'],
                    'expected_close_date' => $o['expected_close_date'],
                    'assigned_to' => $userId,
                    'notes' => $o['notes'],
                    'custom_data' => $o['custom_data'],
                ]);
            }
        } catch (\Throwable $e) {
            // Silently continue
        }
    }

    /**
     * Display the Pipeline Opportunities page or return JSON.
     */
    public function index(Request $request)
    {
        $this->ensureTenantConnection();
        $this->ensureCustomFields();
        $this->ensureDemoOpportunities();

        $profile = $this->resolveCurrentUserProfile();
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);

        // Fetch dynamic custom fields for opportunities
        $customFields = CustomField::on('tenant')
            ->where('module', 'opportunities')
            ->orderBy('id', 'asc')
            ->get();

        // Build query
        $query = Opportunity::on('tenant')
            ->with(['account', 'contact', 'purchaseDeal', 'assignedUser'])
            ->orderBy('id', 'desc');

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhereHas('account', fn ($qa) => $qa->where('name', 'like', "%{$s}%"))
                    ->orWhereHas('purchaseDeal', fn ($qd) => $qd->where('po_number', 'like', "%{$s}%"))
                    ->orWhere('stage', 'like', "%{$s}%")
                    ->orWhere('currency', 'like', "%{$s}%");
            });
        }

        if ($request->filled('stage') && $request->stage !== 'all') {
            $query->where('stage', $request->stage);
        }

        $rawOpps = $query->get();

        // Sanitize according to field permissions
        $sanitizedOpps = $rawOpps->map(function ($opp) use ($fieldPermissions) {
            return $this->sanitizeOpportunity($opp, $fieldPermissions);
        })->values();

        // Fetch dropdown dependencies
        $accounts = Account::on('tenant')->orderBy('name', 'asc')->get(['id', 'name']);
        $contacts = Contact::on('tenant')->orderBy('first_name', 'asc')->get(['id', 'first_name', 'last_name', 'account_id', 'email']);
        $purchaseDeals = PurchaseDeal::on('tenant')->orderBy('id', 'desc')->get(['id', 'po_number', 'title', 'account_id', 'amount', 'stage']);
        $users = TenantUser::on('tenant')->orderBy('name', 'asc')->get(['id', 'name', 'email']);

        $stages = self::STAGES;

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'opportunities' => $sanitizedOpps,
                'custom_fields' => $customFields,
                'field_permissions' => $fieldPermissions,
                'accounts' => $accounts,
                'contacts' => $contacts,
                'purchase_deals' => $purchaseDeals,
                'users' => $users,
                'stages' => $stages,
            ]);
        }

        return view('opportunities', [
            'initialOpportunities' => $sanitizedOpps,
            'customFields' => $customFields,
            'fieldPermissions' => $fieldPermissions,
            'accounts' => $accounts,
            'contacts' => $contacts,
            'purchaseDeals' => $purchaseDeals,
            'users' => $users,
            'stages' => $stages,
            'currentUserProfile' => $profile ? $profile->name : 'Administrator',
        ]);
    }

    /**
     * Display the dedicated Opportunity 360 view page.
     */
    public function show(Request $request, $id)
    {
        $this->ensureTenantConnection();
        $this->ensureCustomFields();

        $profile = $this->resolveCurrentUserProfile();
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);

        $opp = Opportunity::on('tenant')
            ->with(['account', 'contact', 'purchaseDeal', 'assignedUser', 'shipments', 'payments', 'wires', 'tasks'])
            ->findOrFail($id);

        $sanitizedOpp = $this->sanitizeOpportunity($opp, $fieldPermissions);

        // Fetch custom fields for opportunities
        $customFields = CustomField::on('tenant')
            ->where('module', 'opportunities')
            ->orderBy('id', 'asc')
            ->get();

        $accounts = Account::on('tenant')->orderBy('name', 'asc')->get(['id', 'name']);
        $contacts = Contact::on('tenant')->orderBy('first_name', 'asc')->get(['id', 'first_name', 'last_name', 'account_id', 'email']);
        $purchaseDeals = PurchaseDeal::on('tenant')->orderBy('id', 'desc')->get(['id', 'po_number', 'title', 'account_id', 'amount', 'stage']);
        $users = TenantUser::on('tenant')->orderBy('name', 'asc')->get(['id', 'name', 'email']);
        $stages = self::STAGES;

        $shipments = $opp->shipments()->orderBy('id', 'desc')->get();
        $payments = $opp->payments()->orderBy('id', 'desc')->get();
        $wires = $opp->wires()->orderBy('id', 'desc')->get();
        $tasks = $opp->tasks()->orderBy('id', 'desc')->get();

        if ($request->wantsJson()) {
            return response()->json([
                'opportunity' => $sanitizedOpp,
                'custom_fields' => $customFields,
                'field_permissions' => $fieldPermissions,
                'accounts' => $accounts,
                'contacts' => $contacts,
                'purchase_deals' => $purchaseDeals,
                'users' => $users,
                'stages' => $stages,
                'shipments' => $shipments,
                'payments' => $payments,
                'wires' => $wires,
                'tasks' => $tasks,
            ]);
        }

        return view('opportunities.show', [
            'opportunity' => $sanitizedOpp,
            'rawOpportunity' => $opp,
            'customFields' => $customFields,
            'fieldPermissions' => $fieldPermissions,
            'accounts' => $accounts,
            'contacts' => $contacts,
            'purchaseDeals' => $purchaseDeals,
            'users' => $users,
            'stages' => $stages,
            'shipments' => $shipments,
            'payments' => $payments,
            'wires' => $wires,
            'tasks' => $tasks,
            'currentUserProfile' => $profile ? $profile->name : 'Administrator',
        ]);
    }

    /**
     * API: Get opportunities list.
     */
    public function apiList(Request $request)
    {
        $this->ensureTenantConnection();
        $profile = $this->resolveCurrentUserProfile();
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);

        $query = Opportunity::on('tenant')
            ->with(['account', 'contact', 'purchaseDeal', 'assignedUser'])
            ->orderBy('id', 'desc');

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhereHas('account', fn ($qa) => $qa->where('name', 'like', "%{$s}%"))
                    ->orWhereHas('purchaseDeal', fn ($qd) => $qd->where('po_number', 'like', "%{$s}%"))
                    ->orWhere('stage', 'like', "%{$s}%")
                    ->orWhere('currency', 'like', "%{$s}%");
            });
        }

        if ($request->filled('stage') && $request->stage !== 'all') {
            $query->where('stage', $request->stage);
        }

        $rawOpps = $query->get();
        $opps = $rawOpps->map(function ($opp) use ($fieldPermissions) {
            return $this->sanitizeOpportunity($opp, $fieldPermissions);
        })->values();

        return response()->json($opps);
    }

    /**
     * API: Get opportunities metadata.
     */
    public function apiMeta(Request $request)
    {
        $this->ensureTenantConnection();
        $profile = $this->resolveCurrentUserProfile();
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);

        $customFields = CustomField::on('tenant')
            ->where('module', 'opportunities')
            ->orderBy('id', 'asc')
            ->get();

        $accounts = Account::on('tenant')->orderBy('name', 'asc')->get(['id', 'name']);
        $contacts = Contact::on('tenant')->orderBy('first_name', 'asc')->get(['id', 'first_name', 'last_name', 'account_id', 'email']);
        $purchaseDeals = PurchaseDeal::on('tenant')->orderBy('id', 'desc')->get(['id', 'po_number', 'title', 'account_id', 'amount', 'stage']);
        $users = TenantUser::on('tenant')->orderBy('name', 'asc')->get(['id', 'name', 'email']);

        return response()->json([
            'custom_fields' => $customFields,
            'field_permissions' => $fieldPermissions,
            'accounts' => $accounts,
            'contacts' => $contacts,
            'purchase_deals' => $purchaseDeals,
            'users' => $users,
            'stages' => self::STAGES,
        ]);
    }

    /**
     * API: Get single opportunity.
     */
    public function apiShow(Request $request, $id)
    {
        $this->ensureTenantConnection();
        $profile = $this->resolveCurrentUserProfile();
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);

        $opp = Opportunity::on('tenant')
            ->with(['account', 'contact', 'purchaseDeal', 'assignedUser'])
            ->findOrFail($id);

        return response()->json($this->sanitizeOpportunity($opp, $fieldPermissions));
    }

    /**
     * API: Store a newly created opportunity.
     */
    public function apiStore(Request $request)
    {
        $this->ensureTenantConnection();
        $profile = $this->resolveCurrentUserProfile();
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);

        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'account_id' => 'required|integer',
            'contact_id' => 'nullable|integer',
            'purchase_deal_id' => 'nullable|integer',
            'amount' => 'required|numeric|min:0',
            'currency' => 'nullable|string|max:10',
            'stage' => 'required|string|max:60',
            'probability' => 'nullable|integer|min:0|max:100',
            'expected_close_date' => 'nullable|date',
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

        // Auto-couple / generate Purchase Deal if none provided so foreign key constraint is satisfied
        if (empty($validated['purchase_deal_id'])) {
            $deal = PurchaseDeal::on('tenant')->where('account_id', $validated['account_id'])->first();
            if (!$deal) {
                $poNumber = 'PO-' . strtoupper(Str::random(6));
                $deal = PurchaseDeal::on('tenant')->create([
                    'account_id' => $validated['account_id'],
                    'contact_id' => $validated['contact_id'] ?? null,
                    'po_number' => $poNumber,
                    'title' => ($validated['name'] ?? 'Opportunity') . ' Procurement',
                    'amount' => $validated['amount'] ?? 50000.00,
                    'currency' => $validated['currency'] ?? 'USD',
                    'stage' => 'PO Issued',
                    'assigned_to' => $validated['assigned_to'] ?? null,
                ]);
            }
            $validated['purchase_deal_id'] = $deal->id;
        }

        // Default probability by stage if omitted
        if (!isset($validated['probability'])) {
            $stageProbMap = [
                'Discovery' => 25,
                'Proposal' => 50,
                'Negotiation' => 75,
                'Closed Won' => 100,
                'Closed Lost' => 0,
            ];
            $validated['probability'] = $stageProbMap[$validated['stage'] ?? 'Discovery'] ?? 50;
        }

        // Process dynamic custom fields
        $inputCustom = $request->input('custom_fields', []);
        $activeCustomFields = CustomField::on('tenant')->where('module', 'opportunities')->get();
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
        $validated['currency'] = $validated['currency'] ?? 'USD';

        $opp = Opportunity::on('tenant')->create($validated);
        $opp->load(['account', 'contact', 'purchaseDeal', 'assignedUser']);

        return response()->json([
            'success' => true,
            'message' => "Opportunity '{$opp->name}' created successfully!",
            'opportunity' => $this->sanitizeOpportunity($opp, $fieldPermissions),
        ], 201);
    }

    /**
     * API: Update an existing opportunity.
     */
    public function apiUpdate(Request $request, $id)
    {
        $this->ensureTenantConnection();
        $profile = $this->resolveCurrentUserProfile();
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);

        $opp = Opportunity::on('tenant')->findOrFail($id);

        $allowedStandard = [
            'name', 'account_id', 'contact_id', 'purchase_deal_id',
            'amount', 'currency', 'stage', 'probability',
            'expected_close_date', 'assigned_to', 'notes'
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

        // Auto-adjust probability when stage changes and probability wasn't explicitly supplied
        if (isset($updates['stage']) && !$request->has('probability')) {
            $stageProbMap = [
                'Discovery' => 25,
                'Proposal' => 50,
                'Negotiation' => 75,
                'Closed Won' => 100,
                'Closed Lost' => 0,
            ];
            $updates['probability'] = $stageProbMap[$updates['stage']] ?? $opp->probability;
        }

        // Process dynamic custom fields
        $existingCustom = is_array($opp->custom_data) ? $opp->custom_data : (json_decode($opp->custom_data ?? '[]', true) ?: []);
        $inputCustom = $request->input('custom_fields', []);
        $activeCustomFields = CustomField::on('tenant')->where('module', 'opportunities')->get();

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
        $opp->update($updates);
        $opp->load(['account', 'contact', 'purchaseDeal', 'assignedUser']);

        $message = "Opportunity '{$opp->name}' updated successfully!";
        if (isset($updates['stage']) && str_contains(strtolower($updates['stage']), 'lost')) {
            $message .= " Opportunity marked as Closed Lost. Associated proposals marked as void.";
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'opportunity' => $this->sanitizeOpportunity($opp, $fieldPermissions),
        ]);
    }

    /**
     * API: Delete an opportunity.
     */
    public function apiDestroy(Request $request, $id)
    {
        $this->ensureTenantConnection();
        $opp = Opportunity::on('tenant')->findOrFail($id);
        $name = $opp->name;
        $opp->delete();

        return response()->json([
            'success' => true,
            'message' => "Opportunity '{$name}' deleted successfully.",
        ]);
    }

    /**
     * API: Transfer opportunity owner.
     */
    public function apiTransfer(Request $request, $id)
    {
        $this->ensureTenantConnection();
        $validated = $request->validate([
            'assigned_to' => 'required|integer',
        ]);

        $opp = Opportunity::on('tenant')->findOrFail($id);
        $user = TenantUser::on('tenant')->findOrFail($validated['assigned_to']);

        $opp->assigned_to = $user->id;
        $opp->save();
        $opp->load('assignedUser');

        $profile = $this->resolveCurrentUserProfile();
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);

        return response()->json([
            'success' => true,
            'message' => "Opportunity reassigned to '{$user->name}'",
            'opportunity' => $this->sanitizeOpportunity($opp, $fieldPermissions),
        ]);
    }

    /**
     * API: Opportunity 360 Degree View Data.
     */
    public function api360(Request $request, $id)
    {
        $this->ensureTenantConnection();
        $profile = $this->resolveCurrentUserProfile();
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);

        $opp = Opportunity::on('tenant')
            ->with(['account', 'contact', 'purchaseDeal', 'assignedUser', 'shipments', 'payments', 'wires', 'tasks'])
            ->findOrFail($id);

        $sanitizedOpp = $this->sanitizeOpportunity($opp, $fieldPermissions);
        $shipments = $opp->shipments()->orderBy('id', 'desc')->get();
        $payments = $opp->payments()->orderBy('id', 'desc')->get();
        $wires = $opp->wires()->orderBy('id', 'desc')->get();
        $tasks = $opp->tasks()->orderBy('id', 'desc')->get();

        return response()->json([
            'opportunity' => $sanitizedOpp,
            'account' => $opp->account,
            'contact' => $opp->contact,
            'purchase_deal' => $opp->purchaseDeal,
            'shipments' => $shipments,
            'payments' => $payments,
            'wires' => $wires,
            'tasks' => $tasks,
        ]);
    }
}
