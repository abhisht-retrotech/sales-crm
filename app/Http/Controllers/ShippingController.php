<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\Shipment;
use App\Models\Account;
use App\Models\Opportunity;
use App\Models\PurchaseDeal;
use App\Models\TenantUser;
use App\Models\CustomField;
use App\Models\FieldPermission;
use App\Models\Profile;
use App\Models\Tenant;

class ShippingController extends Controller
{
    /**
     * Freight shipping stages in canonical order.
     */
    public const STAGES = [
        'Pending',
        'In Transit',
        'Customs Clearance',
        'Out for Delivery',
        'Delivered',
    ];

    public const CARRIERS = [
        'FedEx Freight',
        'UPS Express',
        'DHL Global',
        'Maersk Line',
        'Expeditors International',
        'DB Schenker',
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
     * Get field permissions map for the current user's profile on shipping/shipments.
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
            ->whereIn('module', ['shipping', 'shipments'])
            ->get();

        foreach ($records as $r) {
            $perms[$r->field_name] = $r->permission ?: 'Read/Write';
        }

        return $perms;
    }

    /**
     * Sanitize a shipment record respecting field-level security permissions.
     */
    protected function sanitizeShipment(Shipment $ship, array $fieldPermissions): array
    {
        $data = $ship->toArray();
        $customData = is_array($ship->custom_data) ? $ship->custom_data : (json_decode($ship->custom_data ?? '[]', true) ?: []);

        // Computed helper attributes
        $data['account_name'] = $ship->account?->name ?? 'Unlinked Account';
        $data['opportunity_name'] = $ship->opportunity?->name ?? null;
        $data['po_number'] = $ship->purchaseDeal?->po_number ?? null;
        $data['purchase_deal_title'] = $ship->purchaseDeal?->title ?? null;
        $data['owner_name'] = $ship->assignedUser?->name ?? 'Unassigned';
        $data['assigned_to_name'] = $data['owner_name'];
        $data['estimated_delivery_date_formatted'] = $ship->estimated_delivery_date ? date('M d, Y', strtotime($ship->estimated_delivery_date)) : 'Pending Schedule';
        $data['dispatch_date_formatted'] = $ship->dispatch_date ? date('M d, Y', strtotime($ship->dispatch_date)) : 'Not Dispatched';
        $data['actual_delivery_date_formatted'] = $ship->actual_delivery_date ? date('M d, Y', strtotime($ship->actual_delivery_date)) : null;

        // Apply permissions on standard attributes
        $protectedKeys = ['id', 'created_at', 'updated_at', 'deleted_at', 'account_id', 'opportunity_id', 'purchase_deal_id', 'assigned_to', 'created_by', 'updated_by'];
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
     * Ensure default dynamic custom fields exist for shipments.
     */
    protected function ensureCustomFields()
    {
        try {
            if (CustomField::on('tenant')->whereIn('module', ['shipping', 'shipments'])->count() > 0) {
                return;
            }

            $defaults = [
                [
                    'module' => 'shipments',
                    'field_name' => 'cf_package_weight_kg',
                    'field_label' => 'Gross Cargo Weight (kg)',
                    'data_type' => 'number',
                    'options' => null,
                    'is_required' => false,
                    'help_text' => 'Total manifest gross scale weight in kilograms',
                ],
                [
                    'module' => 'shipments',
                    'field_name' => 'cf_customs_clearance_port',
                    'field_label' => 'Customs Entry Port',
                    'data_type' => 'picklist',
                    'options' => 'Port of Long Beach (USLGB), Port of Los Angeles (USLAX), JFK International Cargo (USJFK), Rotterdam Europort (NLRTM), Frankfurt Cargo City (DEFRA), Shanghai International Port (CNSHA)',
                    'is_required' => false,
                    'help_text' => 'Designated customs declaration and import clearance port facility',
                ],
                [
                    'module' => 'shipments',
                    'field_name' => 'cf_hazmat_certified',
                    'field_label' => 'Hazmat / Battery Classification',
                    'data_type' => 'picklist',
                    'options' => 'No (General Cargo), Class 9 Lithium Ion Batteries (UN3481), Class 3 Flammable Liquids, Class 8 Corrosives, Dry Ice Refrigerant',
                    'is_required' => false,
                    'help_text' => 'IATA / IMO dangerous goods classification and packing declaration',
                ],
                [
                    'module' => 'shipments',
                    'field_name' => 'cf_temperature_controlled',
                    'field_label' => 'Climate & Temperature Protocol',
                    'data_type' => 'picklist',
                    'options' => 'Standard Ambient, Cold Chain (+2°C to +8°C), Frozen Logistics (-20°C), Controlled Room Temp (15°C to 25°C), Strict Anti-Condensation',
                    'is_required' => false,
                    'help_text' => 'Refrigeration and environmental condition requirement for cargo protection',
                ],
                [
                    'module' => 'shipments',
                    'field_name' => 'cf_bill_of_lading_number',
                    'field_label' => 'Master Bill of Lading (BOL / AWB)',
                    'data_type' => 'text',
                    'options' => null,
                    'is_required' => false,
                    'help_text' => 'Master maritime bill of lading or air waybill tracking reference',
                ],
            ];

            foreach ($defaults as $cf) {
                CustomField::on('tenant')->updateOrCreate(
                    ['module' => 'shipments', 'field_name' => $cf['field_name']],
                    $cf
                );
            }
        } catch (\Throwable $e) {
            // Silently continue
        }
    }

    /**
     * Seed realistic initial enterprise freight shipments if database table is completely empty.
     */
    protected function ensureDemoShipments()
    {
        try {
            if (Shipment::on('tenant')->count() > 0) {
                return;
            }

            $firstUser = TenantUser::on('tenant')->first();
            $userId = $firstUser?->id;

            $accounts = Account::on('tenant')->get()->keyBy('name');
            $opportunities = Opportunity::on('tenant')->get()->keyBy('name');
            $purchaseDeals = PurchaseDeal::on('tenant')->get()->keyBy('po_number');

            $demoShipments = [
                [
                    'account_name' => 'Skyline Cloud Systems',
                    'opp_name' => 'Enterprise Cloud Migration — Stage 2',
                    'po_number' => 'PO-2026-8812',
                    'type' => 'Outbound',
                    'carrier' => 'FedEx Freight',
                    'tracking_number' => 'TRK-88219-US',
                    'status' => 'In Transit',
                    'shipping_origin' => 'Silicon Valley Distribution Hub, San Jose CA',
                    'destination_address' => 'Ashburn Tier IV Data Center, 21820 Pacific Blvd, Ashburn VA',
                    'dispatch_date' => date('Y-m-d', strtotime('-2 days')),
                    'estimated_delivery_date' => date('Y-m-d', strtotime('+3 days')),
                    'notes' => 'Expedited priority transit. Pallet 1 of 4 contains cryptographic hardware modules. Signature required upon arrival.',
                    'custom_data' => [
                        'cf_package_weight_kg' => '420.5',
                        'cf_customs_clearance_port' => 'Port of Los Angeles (USLAX)',
                        'cf_hazmat_certified' => 'Class 9 Lithium Ion Batteries (UN3481)',
                        'cf_temperature_controlled' => 'Controlled Room Temp (15°C to 25°C)',
                        'cf_bill_of_lading_number' => 'FX-BOL-990218',
                    ],
                ],
                [
                    'account_name' => 'Apex Industrial Components',
                    'opp_name' => null,
                    'po_number' => 'PO-2026-8812',
                    'type' => 'Inbound',
                    'carrier' => 'Maersk Line',
                    'tracking_number' => 'MAEU-992014-SEA',
                    'status' => 'Customs Clearance',
                    'shipping_origin' => 'Yantian International Container Terminals, Shenzhen CN',
                    'destination_address' => 'Long Beach Port Customs Terminal, Pier G, Long Beach CA',
                    'dispatch_date' => date('Y-m-d', strtotime('-16 days')),
                    'estimated_delivery_date' => date('Y-m-d', strtotime('+4 days')),
                    'notes' => 'Container vessel docked at Berth 204. Customs broker filing ISF 10+2 entry documentation. Duty payments pre-funded.',
                    'custom_data' => [
                        'cf_package_weight_kg' => '3850.0',
                        'cf_customs_clearance_port' => 'Port of Long Beach (USLGB)',
                        'cf_hazmat_certified' => 'No (General Cargo)',
                        'cf_temperature_controlled' => 'Standard Ambient',
                        'cf_bill_of_lading_number' => 'MSK-77120938-BOL',
                    ],
                ],
                [
                    'account_name' => 'Foxconn Precision Industrial',
                    'opp_name' => null,
                    'po_number' => 'PO-2026-8815',
                    'type' => 'Inbound',
                    'carrier' => 'DHL Global',
                    'tracking_number' => 'DHL-440219-EXP',
                    'status' => 'Pending',
                    'shipping_origin' => 'Taipei Aerotropolis Cargo Terminal, Taoyuan City TW',
                    'destination_address' => 'Central Receiving Depot, Bldg 4, Austin TX',
                    'dispatch_date' => date('Y-m-d', strtotime('+1 day')),
                    'estimated_delivery_date' => date('Y-m-d', strtotime('+6 days')),
                    'notes' => 'Awaiting export compliance license signoff. Flight booked on EVA Air Cargo Flight BR628.',
                    'custom_data' => [
                        'cf_package_weight_kg' => '180.2',
                        'cf_customs_clearance_port' => 'JFK International Cargo (USJFK)',
                        'cf_hazmat_certified' => 'No (General Cargo)',
                        'cf_temperature_controlled' => 'Strict Anti-Condensation',
                        'cf_bill_of_lading_number' => 'DHL-AWB-8830192',
                    ],
                ],
                [
                    'account_name' => 'Nexus Distributed Labs',
                    'opp_name' => 'Edge AI Processing Cluster Deployment',
                    'po_number' => null,
                    'type' => 'Outbound',
                    'carrier' => 'UPS Express',
                    'tracking_number' => 'UPS-109284-AIR',
                    'status' => 'Delivered',
                    'shipping_origin' => 'Portland Regional Fulfillment Hub, Portland OR',
                    'destination_address' => 'Nexus AI Research Facility, 400 Pine St, Seattle WA',
                    'dispatch_date' => date('Y-m-d', strtotime('-5 days')),
                    'estimated_delivery_date' => date('Y-m-d', strtotime('-1 day')),
                    'actual_delivery_date' => date('Y-m-d', strtotime('-1 day')),
                    'notes' => 'Delivered and signed by Dr. Harrison Vance at Receiving Dock B. Verified seal integrity and visual condition.',
                    'custom_data' => [
                        'cf_package_weight_kg' => '84.0',
                        'cf_customs_clearance_port' => 'Port of Los Angeles (USLAX)',
                        'cf_hazmat_certified' => 'No (General Cargo)',
                        'cf_temperature_controlled' => 'Standard Ambient',
                        'cf_bill_of_lading_number' => '1Z9999999999999999',
                    ],
                ],
                [
                    'account_name' => 'Maersk Ocean Logistics Global',
                    'opp_name' => null,
                    'po_number' => 'PO-2026-8819',
                    'type' => 'Inbound',
                    'carrier' => 'Expeditors International',
                    'tracking_number' => 'EXP-338291-GND',
                    'status' => 'Out for Delivery',
                    'shipping_origin' => 'Regional Intermodal Railhead, Sparks NV',
                    'destination_address' => 'Reno Advanced Assembly Hub, 100 Innovation Way, Reno NV',
                    'dispatch_date' => date('Y-m-d', strtotime('-1 day')),
                    'estimated_delivery_date' => date('Y-m-d'),
                    'notes' => 'Dedicated flatbed trailer dispatch. Expected dock arrival by 14:00 PST. Forklift operator notified.',
                    'custom_data' => [
                        'cf_package_weight_kg' => '1250.0',
                        'cf_customs_clearance_port' => 'Port of Long Beach (USLGB)',
                        'cf_hazmat_certified' => 'No (General Cargo)',
                        'cf_temperature_controlled' => 'Standard Ambient',
                        'cf_bill_of_lading_number' => 'EXP-GND-99214',
                    ],
                ],
            ];

            foreach ($demoShipments as $d) {
                $acc = $accounts->get($d['account_name']) ?? Account::on('tenant')->first();
                if (!$acc) {
                    $acc = Account::on('tenant')->create([
                        'name' => $d['account_name'],
                        'type' => 'Customer',
                        'industry' => 'Technology & Logistics',
                        'assigned_to' => $userId,
                        'owner_id' => $userId,
                    ]);
                }

                $opp = !empty($d['opp_name']) ? ($opportunities->get($d['opp_name']) ?? Opportunity::on('tenant')->first()) : null;
                $deal = !empty($d['po_number']) ? ($purchaseDeals->get($d['po_number']) ?? PurchaseDeal::on('tenant')->first()) : null;

                Shipment::on('tenant')->create([
                    'account_id' => $acc->id,
                    'opportunity_id' => $opp?->id,
                    'purchase_deal_id' => $deal?->id,
                    'type' => $d['type'],
                    'carrier' => $d['carrier'],
                    'tracking_number' => $d['tracking_number'],
                    'status' => $d['status'],
                    'shipping_origin' => $d['shipping_origin'],
                    'destination_address' => $d['destination_address'],
                    'dispatch_date' => $d['dispatch_date'],
                    'estimated_delivery_date' => $d['estimated_delivery_date'],
                    'actual_delivery_date' => $d['actual_delivery_date'] ?? null,
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
     * Display the main Shipping & Logistics view.
     */
    public function index()
    {
        $this->ensureTenantConnection();
        $this->ensureCustomFields();
        $this->ensureDemoShipments();

        $profile = $this->resolveCurrentUserProfile();
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);

        $customFields = CustomField::on('tenant')
            ->whereIn('module', ['shipping', 'shipments'])
            ->get();

        $rawShipments = Shipment::on('tenant')
            ->with(['account', 'opportunity', 'purchaseDeal', 'assignedUser'])
            ->orderBy('id', 'desc')
            ->get();

        $initialShipments = $rawShipments->map(function ($ship) use ($fieldPermissions) {
            return $this->sanitizeShipment($ship, $fieldPermissions);
        })->toArray();

        $accounts = Account::on('tenant')
            ->select('id', 'name', 'type')
            ->orderBy('name')
            ->get();

        $opportunities = Opportunity::on('tenant')
            ->select('id', 'name', 'amount', 'stage', 'account_id')
            ->orderBy('name')
            ->get();

        $purchaseDeals = PurchaseDeal::on('tenant')
            ->select('id', 'po_number', 'title', 'amount', 'account_id')
            ->orderBy('po_number')
            ->get();

        $users = TenantUser::on('tenant')
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        return view('shipping', [
            'initialShipments' => $initialShipments,
            'customFields' => $customFields,
            'fieldPermissions' => $fieldPermissions,
            'accounts' => $accounts,
            'opportunities' => $opportunities,
            'purchaseDeals' => $purchaseDeals,
            'users' => $users,
            'stages' => self::STAGES,
            'carriers' => self::CARRIERS,
            'currentUserProfile' => $profile?->name ?? 'Administrator',
        ]);
    }

    /**
     * Display dedicated Shipment 360 view page.
     */
    public function show($id)
    {
        $this->ensureTenantConnection();
        $this->ensureCustomFields();

        $profile = $this->resolveCurrentUserProfile();
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);

        $ship = Shipment::on('tenant')
            ->with(['account', 'opportunity', 'purchaseDeal', 'assignedUser'])
            ->findOrFail($id);

        $sanitizedShip = $this->sanitizeShipment($ship, $fieldPermissions);

        $customFields = CustomField::on('tenant')
            ->whereIn('module', ['shipping', 'shipments'])
            ->get();

        $users = TenantUser::on('tenant')
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        $accounts = Account::on('tenant')
            ->select('id', 'name', 'type')
            ->orderBy('name')
            ->get();

        $opportunities = Opportunity::on('tenant')
            ->select('id', 'name', 'amount')
            ->orderBy('name')
            ->get();

        $purchaseDeals = PurchaseDeal::on('tenant')
            ->select('id', 'po_number', 'title', 'amount')
            ->orderBy('po_number')
            ->get();

        return view('shipping.show', [
            'shipment' => $sanitizedShip,
            'customFields' => $customFields,
            'fieldPermissions' => $fieldPermissions,
            'users' => $users,
            'accounts' => $accounts,
            'opportunities' => $opportunities,
            'purchaseDeals' => $purchaseDeals,
            'stages' => self::STAGES,
            'carriers' => self::CARRIERS,
            'currentUserProfile' => $profile?->name ?? 'Administrator',
        ]);
    }

    /**
     * API: Get all shipments with optional search, direction & stage filter.
     */
    public function apiList(Request $request)
    {
        $this->ensureTenantConnection();
        $profile = $this->resolveCurrentUserProfile();
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);

        $query = Shipment::on('tenant')
            ->with(['account', 'opportunity', 'purchaseDeal', 'assignedUser'])
            ->orderBy('id', 'desc');

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        if ($request->filled('carrier') && $request->carrier !== 'all') {
            $query->where('carrier', $request->carrier);
        }

        if ($request->filled('search')) {
            $q = '%' . $request->search . '%';
            $query->where(function ($sub) use ($q) {
                $sub->where('tracking_number', 'like', $q)
                    ->orWhere('carrier', 'like', $q)
                    ->orWhere('shipping_origin', 'like', $q)
                    ->orWhere('destination_address', 'like', $q)
                    ->orWhere('notes', 'like', $q)
                    ->orWhereHas('account', function ($accQ) use ($q) {
                        $accQ->where('name', 'like', $q);
                    });
            });
        }

        $shipments = $query->get()->map(function ($ship) use ($fieldPermissions) {
            return $this->sanitizeShipment($ship, $fieldPermissions);
        });

        return response()->json([
            'success' => true,
            'shipments' => $shipments,
            'total' => $shipments->count(),
        ]);
    }

    /**
     * API: Get metadata (custom fields, field permissions, stages, carriers, accounts, etc.).
     */
    public function apiMeta()
    {
        $this->ensureTenantConnection();
        $this->ensureCustomFields();

        $profile = $this->resolveCurrentUserProfile();
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);

        $customFields = CustomField::on('tenant')
            ->whereIn('module', ['shipping', 'shipments'])
            ->get();

        $accounts = Account::on('tenant')
            ->select('id', 'name', 'type')
            ->orderBy('name')
            ->get();

        $opportunities = Opportunity::on('tenant')
            ->select('id', 'name', 'amount')
            ->orderBy('name')
            ->get();

        $purchaseDeals = PurchaseDeal::on('tenant')
            ->select('id', 'po_number', 'title')
            ->orderBy('po_number')
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
            'opportunities' => $opportunities,
            'purchase_deals' => $purchaseDeals,
            'users' => $users,
            'stages' => self::STAGES,
            'carriers' => self::CARRIERS,
            'current_profile' => $profile?->name ?? 'Administrator',
        ]);
    }

    /**
     * API: Store a new freight shipment with dynamic custom fields & permissions.
     */
    public function apiStore(Request $request)
    {
        $this->ensureTenantConnection();
        $profile = $this->resolveCurrentUserProfile();
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);

        // Auto-generate tracking number if empty
        $trackingNumber = trim($request->input('tracking_number', ''));
        if (empty($trackingNumber)) {
            $prefix = strtoupper(substr($request->input('carrier', 'TRK'), 0, 3));
            $rand = mt_rand(100000, 999999);
            $trackingNumber = "{$prefix}-{$rand}-EXP";
        }

        $carrier = trim($request->input('carrier', ''));
        if (empty($carrier)) {
            return response()->json(['success' => false, 'message' => 'Freight Carrier is required.'], 422);
        }

        $accountId = $request->input('account_id');
        if (empty($accountId)) {
            $accountName = trim($request->input('account_name', ''));
            if (!empty($accountName)) {
                $acc = Account::on('tenant')->firstOrCreate(
                    ['name' => $accountName],
                    ['type' => 'Customer']
                );
                $accountId = $acc->id;
            } else {
                $firstAcc = Account::on('tenant')->first();
                $accountId = $firstAcc ? $firstAcc->id : null;
            }
        }

        if (empty($accountId)) {
            return response()->json(['success' => false, 'message' => 'Linked Customer/Vendor Account is required.'], 422);
        }

        $type = $request->input('type', 'Outbound');
        $status = $request->input('status', 'Pending');
        if (!in_array($status, self::STAGES)) {
            $status = 'Pending';
        }

        $shippingOrigin = $request->input('shipping_origin') ?: 'Central Distribution Facility';
        $destinationAddress = $request->input('destination_address') ?: '';
        $dispatchDate = $request->input('dispatch_date') ?: null;
        $estimatedDeliveryDate = $request->input('estimated_delivery_date') ?: null;
        $actualDeliveryDate = $request->input('actual_delivery_date') ?: null;
        $notes = $request->input('notes', '');
        $opportunityId = $request->input('opportunity_id') ?: null;
        $purchaseDealId = $request->input('purchase_deal_id') ?: null;
        $assignedTo = $request->input('assigned_to') ?: Auth::id();

        // Process dynamic custom fields
        $customFields = CustomField::on('tenant')->whereIn('module', ['shipping', 'shipments'])->get();
        $customData = [];
        $rawCustom = $request->input('custom_data', []);
        if (is_string($rawCustom)) {
            $rawCustom = json_decode($rawCustom, true) ?: [];
        }

        foreach ($customFields as $cf) {
            $fn = $cf->field_name;
            $perm = $fieldPermissions[$fn] ?? 'Read/Write';
            if ($perm === 'Read-Only') {
                continue;
            }
            if ($request->has($fn)) {
                $customData[$fn] = $request->input($fn);
            } elseif (isset($rawCustom[$fn])) {
                $customData[$fn] = $rawCustom[$fn];
            }
        }

        $ship = Shipment::on('tenant')->create([
            'account_id' => $accountId,
            'opportunity_id' => $opportunityId,
            'purchase_deal_id' => $purchaseDealId,
            'type' => $type,
            'carrier' => $carrier,
            'tracking_number' => $trackingNumber,
            'status' => $status,
            'shipping_origin' => $shippingOrigin,
            'destination_address' => $destinationAddress,
            'dispatch_date' => $dispatchDate,
            'estimated_delivery_date' => $estimatedDeliveryDate,
            'actual_delivery_date' => $actualDeliveryDate,
            'notes' => $notes,
            'custom_data' => $customData,
            'assigned_to' => $assignedTo,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        $sanitized = $this->sanitizeShipment($ship->fresh(['account', 'opportunity', 'purchaseDeal', 'assignedUser']), $fieldPermissions);

        return response()->json([
            'success' => true,
            'message' => "Shipment {$ship->tracking_number} created successfully!",
            'shipment' => $sanitized,
        ], 201);
    }

    /**
     * API: Get single shipment.
     */
    public function apiShow($id)
    {
        $this->ensureTenantConnection();
        $profile = $this->resolveCurrentUserProfile();
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);

        $ship = Shipment::on('tenant')
            ->with(['account', 'opportunity', 'purchaseDeal', 'assignedUser'])
            ->find($id);

        if (!$ship) {
            return response()->json(['success' => false, 'message' => 'Shipment not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'shipment' => $this->sanitizeShipment($ship, $fieldPermissions),
        ]);
    }

    /**
     * API: Update a freight shipment.
     */
    public function apiUpdate(Request $request, $id)
    {
        $this->ensureTenantConnection();
        $profile = $this->resolveCurrentUserProfile();
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);

        $ship = Shipment::on('tenant')->find($id);
        if (!$ship) {
            return response()->json(['success' => false, 'message' => 'Shipment not found.'], 404);
        }

        $fillableStandard = [
            'carrier', 'account_id', 'opportunity_id', 'purchase_deal_id',
            'type', 'tracking_number', 'status', 'shipping_origin',
            'destination_address', 'dispatch_date', 'estimated_delivery_date',
            'actual_delivery_date', 'notes', 'assigned_to'
        ];

        $updateData = [];
        foreach ($fillableStandard as $key) {
            $perm = $fieldPermissions[$key] ?? 'Read/Write';
            if ($perm === 'Read-Only') {
                continue;
            }
            if ($request->has($key)) {
                $updateData[$key] = $request->input($key);
            }
        }

        // If status marked as Delivered, auto set actual_delivery_date
        if (isset($updateData['status']) && $updateData['status'] === 'Delivered' && empty($ship->actual_delivery_date)) {
            $updateData['actual_delivery_date'] = date('Y-m-d');
        }

        // Handle dynamic custom fields
        $customFields = CustomField::on('tenant')->whereIn('module', ['shipping', 'shipments'])->get();
        $currentCustom = is_array($ship->custom_data) ? $ship->custom_data : (json_decode($ship->custom_data ?? '[]', true) ?: []);
        $rawCustom = $request->input('custom_data', []);
        if (is_string($rawCustom)) {
            $rawCustom = json_decode($rawCustom, true) ?: [];
        }

        foreach ($customFields as $cf) {
            $fn = $cf->field_name;
            $perm = $fieldPermissions[$fn] ?? 'Read/Write';
            if ($perm === 'Read-Only') {
                continue;
            }
            if ($request->has($fn)) {
                $currentCustom[$fn] = $request->input($fn);
            } elseif (isset($rawCustom[$fn])) {
                $currentCustom[$fn] = $rawCustom[$fn];
            }
        }
        $updateData['custom_data'] = $currentCustom;
        $updateData['updated_by'] = Auth::id();

        $ship->update($updateData);

        $sanitized = $this->sanitizeShipment($ship->fresh(['account', 'opportunity', 'purchaseDeal', 'assignedUser']), $fieldPermissions);

        return response()->json([
            'success' => true,
            'message' => "Shipment {$ship->tracking_number} updated successfully!",
            'shipment' => $sanitized,
        ]);
    }

    /**
     * API: Quick Status update (for Kanban drag & drop or action buttons).
     */
    public function apiUpdateStatus(Request $request, $id)
    {
        $this->ensureTenantConnection();
        $status = $request->input('status');

        if (!in_array($status, self::STAGES)) {
            return response()->json(['success' => false, 'message' => 'Invalid freight shipping status.'], 422);
        }

        $ship = Shipment::on('tenant')->find($id);
        if (!$ship) {
            return response()->json(['success' => false, 'message' => 'Shipment not found.'], 404);
        }

        $updateData = [
            'status' => $status,
            'updated_by' => Auth::id(),
        ];

        if ($status === 'Delivered' && empty($ship->actual_delivery_date)) {
            $updateData['actual_delivery_date'] = date('Y-m-d');
        }

        $ship->update($updateData);

        $profile = $this->resolveCurrentUserProfile();
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);
        $sanitized = $this->sanitizeShipment($ship->fresh(['account', 'opportunity', 'purchaseDeal', 'assignedUser']), $fieldPermissions);

        return response()->json([
            'success' => true,
            'message' => "Shipment status updated to {$status}!",
            'shipment' => $sanitized,
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

        $ship = Shipment::on('tenant')->find($id);
        if (!$ship) {
            return response()->json(['success' => false, 'message' => 'Shipment not found.'], 404);
        }

        $ship->update([
            'assigned_to' => $newOwnerId,
            'updated_by' => Auth::id(),
        ]);

        $profile = $this->resolveCurrentUserProfile();
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);
        $sanitized = $this->sanitizeShipment($ship->fresh(['account', 'opportunity', 'purchaseDeal', 'assignedUser']), $fieldPermissions);

        return response()->json([
            'success' => true,
            'message' => "Shipment transferred successfully to {$sanitized['owner_name']}!",
            'shipment' => $sanitized,
        ]);
    }

    /**
     * API: Delete a shipment.
     */
    public function apiDestroy($id)
    {
        $this->ensureTenantConnection();
        $ship = Shipment::on('tenant')->find($id);
        if (!$ship) {
            return response()->json(['success' => false, 'message' => 'Shipment not found.'], 404);
        }

        $trk = $ship->tracking_number;
        $ship->delete();

        return response()->json([
            'success' => true,
            'message' => "Shipment manifest {$trk} has been deleted.",
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

        $ship = Shipment::on('tenant')
            ->with(['account', 'opportunity', 'purchaseDeal', 'assignedUser'])
            ->find($id);

        if (!$ship) {
            return response()->json(['success' => false, 'message' => 'Shipment not found.'], 404);
        }

        $sanitized = $this->sanitizeShipment($ship, $fieldPermissions);

        return response()->json([
            'success' => true,
            'shipment' => $sanitized,
            'account' => $ship->account,
            'opportunity' => $ship->opportunity,
            'purchase_deal' => $ship->purchaseDeal,
        ]);
    }
}
