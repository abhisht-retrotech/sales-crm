<?php

namespace App\Services\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Tenant;
use App\Models\CustomObject;
use App\Models\CustomObjectRecord;
use App\Models\PageLayout;
use App\Models\Profile;
use App\Models\ProfileLayoutAssignment;
use App\Models\FieldHistoryConfig;
use App\Models\CustomButton;
use App\Models\EmailTemplate;
use App\Models\NotificationRule;
use App\Models\CustomField;
use App\Models\StandardFieldOverride;

class AdminCustomObjectService
{
    use AdminTenantTrait;

    public function ensureDefaultCustomObjects($tenant)
    {
        $this->ensureTenantConnection($tenant);

        if (CustomObject::on('tenant')->count() === 0) {
            $assetObj = CustomObject::on('tenant')->create([
                'label'         => 'IT Hardware Asset',
                'plural_label'  => 'Hardware Assets',
                'api_name'      => 'Asset__c',
                'description'   => 'Enterprise computing hardware, field laptops, network routers, and cloud server nodes.',
                'primary_field' => 'name',
                'icon'          => 'HardDrive',
                'created_by'    => 'System Admin',
            ]);

            CustomObjectRecord::on('tenant')->create([
                'custom_object_id' => $assetObj->id,
                'name'             => 'MacBook Pro 16" M3 Max (Dev Fleet #042)',
                'attributes_json'  => [
                    'notes'           => 'Assigned to Senior Lead Architect. Serial: MBP-992384-US. 64GB Unified RAM, 2TB NVMe.',
                    'department'      => 'Engineering Core',
                    'serial_number'   => 'MBP-992384-US',
                    'status'          => 'Active Deployed',
                ],
                'created_by'       => 'System Admin',
                'updated_by'       => 'System Admin',
            ]);

            CustomObjectRecord::on('tenant')->create([
                'custom_object_id' => $assetObj->id,
                'name'             => 'Dell PowerEdge R750 Rack Server',
                'attributes_json'  => [
                    'notes'           => 'Primary production database replica in Ashburn DC-2. Dual Xeon Platinum 8380.',
                    'department'      => 'Infrastructure & Ops',
                    'serial_number'   => 'SRV-DL-88319',
                    'status'          => 'In Production',
                ],
                'created_by'       => 'System Admin',
                'updated_by'       => 'System Admin',
            ]);

            CustomObjectRecord::on('tenant')->create([
                'custom_object_id' => $assetObj->id,
                'name'             => 'Cisco Meraki MX95 Security Gateway',
                'attributes_json'  => [
                    'notes'           => 'HQ core branch firewall & site-to-site SD-WAN concentrator. Throughput: 2 Gbps.',
                    'department'      => 'NetOps Security',
                    'serial_number'   => 'CSCO-MK-00129',
                    'status'          => 'Active Deployed',
                ],
                'created_by'       => 'System Admin',
                'updated_by'       => 'System Admin',
            ]);

            $warrantyObj = CustomObject::on('tenant')->create([
                'label'         => 'Service Warranty',
                'plural_label'  => 'Service Warranties',
                'api_name'      => 'Warranty__c',
                'description'   => 'OEM service contracts, extended coverage, and guaranteed hardware replacement SLAs.',
                'primary_field' => 'name',
                'icon'          => 'Shield',
                'created_by'    => 'System Admin',
            ]);

            CustomObjectRecord::on('tenant')->create([
                'custom_object_id' => $warrantyObj->id,
                'name'             => 'Dell ProSupport Plus 3-Year 24/7 NBD',
                'attributes_json'  => [
                    'notes'           => 'Enterprise SLA covering mission-critical server hardware with 4-hour onsite response.',
                    'sla_tier'        => 'Platinum 24/7',
                    'coverage_type'   => 'Hardware + Onsite Dispatch',
                ],
                'created_by'       => 'System Admin',
                'updated_by'       => 'System Admin',
            ]);

            CustomObjectRecord::on('tenant')->create([
                'custom_object_id' => $warrantyObj->id,
                'name'             => 'AppleCare+ for Enterprise (Tier 1)',
                'attributes_json'  => [
                    'notes'           => 'Next-day onsite repairs, priority 24/7 IT helpdesk, and up to 2% buffer unit replacement.',
                    'sla_tier'        => 'Tier 1 Enterprise',
                    'coverage_type'   => 'Accidental Damage + Rapid Swap',
                ],
                'created_by'       => 'System Admin',
                'updated_by'       => 'System Admin',
            ]);

            $inspectionObj = CustomObject::on('tenant')->create([
                'label'         => 'Safety Inspection',
                'plural_label'  => 'Safety Inspections',
                'api_name'      => 'Inspection__c',
                'description'   => 'Quarterly datacenter compliance, electrical redundancy, and environmental audits.',
                'primary_field' => 'name',
                'icon'          => 'Wrench',
                'created_by'    => 'System Admin',
            ]);

            CustomObjectRecord::on('tenant')->create([
                'custom_object_id' => $inspectionObj->id,
                'name'             => 'Q3 Data Center Environmental & Cooling Audit',
                'attributes_json'  => [
                    'notes'           => 'All HVAC containment cooling zones passed ASHRAE TC 9.9 thermal regulations with zero hotspot alerts.',
                    'passed'          => 'Yes',
                    'auditor'         => 'Bureau Veritas',
                ],
                'created_by'       => 'System Admin',
                'updated_by'       => 'System Admin',
            ]);
        }
    }

    public function customObjects()
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);
        $this->ensureDefaultCustomObjects($tenant);

        $customObjects = CustomObject::on('tenant')
            ->withCount('records')
            ->orderBy('label')
            ->get()
            ->map(function ($obj) {
                return [
                    'id'            => (string) $obj->id,
                    'label'         => $obj->label,
                    'plural_label'  => $obj->plural_label,
                    'api_name'      => $obj->api_name,
                    'description'   => $obj->description,
                    'primary_field' => $obj->primary_field ?: 'name',
                    'icon'          => $obj->icon ?: 'Box',
                    'record_count'  => $obj->records_count,
                    'created_by'    => $obj->created_by ?: 'System Admin',
                    'created_at'    => $obj->created_at ? $obj->created_at->toIso8601String() : null,
                    'updated_at'    => $obj->updated_at ? $obj->updated_at->toIso8601String() : null,
                ];
            });

        return view('admin.custom_objects', compact('customObjects'));
    }

    public function getCustomObjectsApi(Request $request)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);
        $this->ensureDefaultCustomObjects($tenant);

        $customObjects = CustomObject::on('tenant')
            ->withCount('records')
            ->orderBy('label')
            ->get()
            ->map(function ($obj) {
                return [
                    'id'            => (string) $obj->id,
                    'label'         => $obj->label,
                    'plural_label'  => $obj->plural_label,
                    'api_name'      => $obj->api_name,
                    'description'   => $obj->description,
                    'primary_field' => $obj->primary_field ?: 'name',
                    'icon'          => $obj->icon ?: 'Box',
                    'record_count'  => $obj->records_count,
                    'created_by'    => $obj->created_by ?: 'System Admin',
                    'created_at'    => $obj->created_at ? $obj->created_at->toIso8601String() : null,
                    'updated_at'    => $obj->updated_at ? $obj->updated_at->toIso8601String() : null,
                ];
            });

        return response()->json($customObjects);
    }

    public function createCustomObjectApi(Request $request)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);

        $request->validate([
            'label'         => 'required|string|max:150',
            'plural_label'  => 'nullable|string|max:150',
            'api_name'      => 'nullable|string|max:100',
            'description'   => 'nullable|string|max:1000',
            'icon'          => 'nullable|string|max:50',
            'primary_field' => 'nullable|string|max:100',
        ]);

        $label = trim($request->input('label'));
        $pluralLabel = trim($request->input('plural_label', '')) ?: ($label . 's');

        $apiName = trim($request->input('api_name', ''));
        if (empty($apiName)) {
            $slug = Str::slug($label, '_');
            $apiName = "{$slug}__c";
        } elseif (!Str::endsWith($apiName, '__c')) {
            $apiName = "{$apiName}__c";
        }

        if (CustomObject::on('tenant')->where('api_name', $apiName)->exists()) {
            return response()->json([
                'success' => false,
                'message' => "Custom Object with API name '{$apiName}' already exists."
            ], 422);
        }

        $obj = CustomObject::on('tenant')->create([
            'label'         => $label,
            'plural_label'  => $pluralLabel,
            'api_name'      => $apiName,
            'description'   => $request->input('description'),
            'icon'          => $request->input('icon', 'Box'),
            'primary_field' => $request->input('primary_field', 'name') ?: 'name',
            'created_by'    => 'System Admin',
        ]);

        $res = [
            'id'            => (string) $obj->id,
            'label'         => $obj->label,
            'plural_label'  => $obj->plural_label,
            'api_name'      => $obj->api_name,
            'description'   => $obj->description,
            'primary_field' => $obj->primary_field,
            'icon'          => $obj->icon,
            'record_count'  => 0,
            'created_by'    => $obj->created_by,
            'created_at'    => $obj->created_at ? $obj->created_at->toIso8601String() : null,
            'updated_at'    => $obj->updated_at ? $obj->updated_at->toIso8601String() : null,
        ];

        return response()->json($res, 201);
    }

    public function getCustomObjectApi(Request $request, $id)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);

        $obj = CustomObject::on('tenant')->withCount('records')
            ->where(function ($q) use ($id) {
                if (is_numeric($id)) {
                    $q->where('id', $id)->orWhere('api_name', $id);
                } else {
                    $q->where('api_name', $id);
                }
            })->first();

        if (!$obj) {
            return response()->json(['success' => false, 'message' => 'Custom Object not found.'], 404);
        }

        return response()->json([
            'id'            => (string) $obj->id,
            'label'         => $obj->label,
            'plural_label'  => $obj->plural_label,
            'api_name'      => $obj->api_name,
            'description'   => $obj->description,
            'primary_field' => $obj->primary_field,
            'icon'          => $obj->icon,
            'record_count'  => $obj->records_count,
            'created_by'    => $obj->created_by,
            'created_at'    => $obj->created_at ? $obj->created_at->toIso8601String() : null,
            'updated_at'    => $obj->updated_at ? $obj->updated_at->toIso8601String() : null,
        ]);
    }

    public function deleteCustomObjectApi(Request $request, $id)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);

        $obj = CustomObject::on('tenant')
            ->where(function ($q) use ($id) {
                if (is_numeric($id)) {
                    $q->where('id', $id)->orWhere('api_name', $id);
                } else {
                    $q->where('api_name', $id);
                }
            })->first();

        if (!$obj) {
            return response()->json(['success' => false, 'message' => 'Custom Object not found.'], 404);
        }

        $label = $obj->label;
        $apiName = $obj->api_name;
        $obj->delete();

        return response()->json([
            'success' => true,
            'message' => "Custom Object '{$label}' ({$apiName}) and all associated records have been permanently deleted."
        ]);
    }

    public function getCustomObjectRecordsApi(Request $request, $objectId)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);
        $this->ensureDefaultCustomObjects($tenant);

        $obj = CustomObject::on('tenant')
            ->where(function ($q) use ($objectId) {
                if (is_numeric($objectId)) {
                    $q->where('id', $objectId)->orWhere('api_name', $objectId);
                } else {
                    $q->where('api_name', $objectId);
                }
            })->first();

        if (!$obj) {
            $obj = CustomObject::on('tenant')->first();
        }

        if (!$obj) {
            return response()->json([
                'success'    => true,
                'records'    => [],
                'page'       => 1,
                'totalPages' => 1,
                'totalCount' => 0,
            ]);
        }

        $page = max(1, (int) $request->query('page', 1));
        $limit = max(1, min(100, (int) $request->query('limit', 10)));
        $search = trim($request->query('search', ''));

        $query = CustomObjectRecord::on('tenant')->where('custom_object_id', $obj->id);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('attributes_json', 'like', "%{$search}%");
            });
        }

        $totalCount = $query->count();
        $records = $query->orderBy('id', 'desc')
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->get()
            ->map(function ($rec) {
                $data = [
                    'id'                => (string) $rec->id,
                    'custom_object_id'  => (string) $rec->custom_object_id,
                    'name'              => $rec->name,
                    'created_by'        => $rec->created_by ?: 'System Admin',
                    'updated_by'        => $rec->updated_by ?: 'System Admin',
                    'created_at'        => $rec->created_at ? $rec->created_at->toIso8601String() : null,
                    'updated_at'        => $rec->updated_at ? $rec->updated_at->toIso8601String() : null,
                ];
                if (!empty($rec->attributes_json) && is_array($rec->attributes_json)) {
                    foreach ($rec->attributes_json as $k => $v) {
                        if (!isset($data[$k])) {
                            $data[$k] = $v;
                        }
                    }
                }
                return $data;
            });

        return response()->json([
            'object'     => [
                'id'            => (string) $obj->id,
                'label'         => $obj->label,
                'plural_label'  => $obj->plural_label,
                'api_name'      => $obj->api_name,
                'primary_field' => $obj->primary_field,
                'icon'          => $obj->icon,
            ],
            'totalCount' => $totalCount,
            'page'       => $page,
            'limit'      => $limit,
            'totalPages' => $totalCount > 0 ? (int) ceil($totalCount / $limit) : 1,
            'records'    => $records,
        ]);
    }

    public function createCustomObjectRecordApi(Request $request, $objectId)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);

        $obj = CustomObject::on('tenant')
            ->where(function ($q) use ($objectId) {
                if (is_numeric($objectId)) {
                    $q->where('id', $objectId)->orWhere('api_name', $objectId);
                } else {
                    $q->where('api_name', $objectId);
                }
            })->first();

        if (!$obj) {
            return response()->json(['success' => false, 'message' => 'Custom Object not found.'], 404);
        }

        $request->validate([
            'name' => 'required|string|max:200',
        ]);

        $attributes = $request->except(['name', 'id', '_token', 'custom_object_id']);

        $rec = CustomObjectRecord::on('tenant')->create([
            'custom_object_id' => $obj->id,
            'name'             => $request->input('name'),
            'attributes_json'  => !empty($attributes) ? $attributes : [],
            'created_by'       => 'System Admin',
            'updated_by'       => 'System Admin',
        ]);

        $data = [
            'id'                => (string) $rec->id,
            'custom_object_id'  => (string) $rec->custom_object_id,
            'name'              => $rec->name,
            'created_by'        => $rec->created_by,
            'updated_by'        => $rec->updated_by,
            'created_at'        => $rec->created_at ? $rec->created_at->toIso8601String() : null,
            'updated_at'        => $rec->updated_at ? $rec->updated_at->toIso8601String() : null,
        ];
        if (!empty($rec->attributes_json) && is_array($rec->attributes_json)) {
            foreach ($rec->attributes_json as $k => $v) {
                if (!isset($data[$k])) {
                    $data[$k] = $v;
                }
            }
        }

        return response()->json([
            'success' => true,
            'data'    => $data,
        ], 201);
    }

    public function getCustomObjectRecordApi(Request $request, $objectId, $recordId)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);

        $rec = CustomObjectRecord::on('tenant')->find($recordId);
        if (!$rec) {
            return response()->json(['success' => false, 'message' => 'Record not found.'], 404);
        }

        $data = [
            'id'                => (string) $rec->id,
            'custom_object_id'  => (string) $rec->custom_object_id,
            'name'              => $rec->name,
            'created_by'        => $rec->created_by,
            'updated_by'        => $rec->updated_by,
            'created_at'        => $rec->created_at ? $rec->created_at->toIso8601String() : null,
            'updated_at'        => $rec->updated_at ? $rec->updated_at->toIso8601String() : null,
        ];
        if (!empty($rec->attributes_json) && is_array($rec->attributes_json)) {
            foreach ($rec->attributes_json as $k => $v) {
                if (!isset($data[$k])) {
                    $data[$k] = $v;
                }
            }
        }

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    public function updateCustomObjectRecordApi(Request $request, $objectId, $recordId)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);

        $rec = CustomObjectRecord::on('tenant')->find($recordId);
        if (!$rec) {
            return response()->json(['success' => false, 'message' => 'Record not found.'], 404);
        }

        if ($request->has('name') && !empty($request->input('name'))) {
            $rec->name = $request->input('name');
        }

        $extra = $request->except(['name', 'id', '_token', 'custom_object_id', 'created_at', 'updated_at', 'created_by', 'updated_by']);
        $currentAttrs = is_array($rec->attributes_json) ? $rec->attributes_json : [];
        $rec->attributes_json = array_merge($currentAttrs, $extra);
        $rec->updated_by = 'System Admin';
        $rec->save();

        $data = [
            'id'                => (string) $rec->id,
            'custom_object_id'  => (string) $rec->custom_object_id,
            'name'              => $rec->name,
            'created_by'        => $rec->created_by,
            'updated_by'        => $rec->updated_by,
            'created_at'        => $rec->created_at ? $rec->created_at->toIso8601String() : null,
            'updated_at'        => $rec->updated_at ? $rec->updated_at->toIso8601String() : null,
        ];
        if (!empty($rec->attributes_json) && is_array($rec->attributes_json)) {
            foreach ($rec->attributes_json as $k => $v) {
                if (!isset($data[$k])) {
                    $data[$k] = $v;
                }
            }
        }

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    public function deleteCustomObjectRecordApi(Request $request, $objectId, $recordId)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);

        $rec = CustomObjectRecord::on('tenant')->find($recordId);
        if (!$rec) {
            return response()->json(['success' => false, 'message' => 'Record not found.'], 404);
        }

        $rec->delete();

        return response()->json([
            'success' => true,
            'message' => 'Record deleted successfully.'
        ]);
    }

    public function getDefaultLayoutDefinition($module)
    {
        $standardFields = [
            'leads' => [
                ['name' => 'first_name', 'label' => 'First Name', 'type' => 'Text', 'required' => true],
                ['name' => 'last_name', 'label' => 'Last Name', 'type' => 'Text', 'required' => true],
                ['name' => 'company', 'label' => 'Company Name', 'type' => 'Text', 'required' => true],
                ['name' => 'title', 'label' => 'Job Title', 'type' => 'Text'],
                ['name' => 'email', 'label' => 'Email Address', 'type' => 'Email', 'required' => true],
                ['name' => 'phone', 'label' => 'Phone Number', 'type' => 'Phone'],
                ['name' => 'status', 'label' => 'Status', 'type' => 'Dropdown', 'required' => true],
                ['name' => 'score', 'label' => 'Lead Score', 'type' => 'Number'],
                ['name' => 'source', 'label' => 'Lead Source', 'type' => 'Dropdown'],
                ['name' => 'rating', 'label' => 'Rating', 'type' => 'Rating'],
                ['name' => 'assigned_to', 'label' => 'Assigned To', 'type' => 'Text'],
                ['name' => 'notes', 'label' => 'Notes', 'type' => 'Textarea'],
            ],
            'accounts' => [
                ['name' => 'name', 'label' => 'Account Name', 'type' => 'Text', 'required' => true],
                ['name' => 'type', 'label' => 'Account Type', 'type' => 'Dropdown', 'required' => true],
                ['name' => 'industry', 'label' => 'Industry', 'type' => 'Text'],
                ['name' => 'website', 'label' => 'Website URL', 'type' => 'URL'],
                ['name' => 'phone', 'label' => 'Phone', 'type' => 'Phone'],
                ['name' => 'billing_address', 'label' => 'Billing Address', 'type' => 'Textarea'],
                ['name' => 'shipping_address', 'label' => 'Shipping Address', 'type' => 'Textarea'],
                ['name' => 'payment_terms', 'label' => 'Payment Terms', 'type' => 'Dropdown'],
                ['name' => 'credit_limit', 'label' => 'Credit Limit', 'type' => 'Currency'],
                ['name' => 'annual_revenue', 'label' => 'Annual Revenue', 'type' => 'Currency'],
            ],
            'contacts' => [
                ['name' => 'first_name', 'label' => 'First Name', 'type' => 'Text', 'required' => true],
                ['name' => 'last_name', 'label' => 'Last Name', 'type' => 'Text', 'required' => true],
                ['name' => 'account_id', 'label' => 'Account (Company)', 'type' => 'Lookup'],
                ['name' => 'title', 'label' => 'Job Title', 'type' => 'Text'],
                ['name' => 'department', 'label' => 'Department', 'type' => 'Text'],
                ['name' => 'email', 'label' => 'Email', 'type' => 'Email', 'required' => true],
                ['name' => 'phone', 'label' => 'Phone', 'type' => 'Phone'],
                ['name' => 'contact_role', 'label' => 'Buying Role', 'type' => 'Dropdown'],
                ['name' => 'is_primary', 'label' => 'Primary Contact', 'type' => 'Boolean'],
            ],
            'opportunities' => [
                ['name' => 'name', 'label' => 'Opportunity Name', 'type' => 'Text', 'required' => true],
                ['name' => 'account_id', 'label' => 'Customer Account', 'type' => 'Dropdown', 'required' => true],
                ['name' => 'purchase_deal_id', 'label' => 'Procurement PO Deal', 'type' => 'Dropdown', 'required' => true],
                ['name' => 'stage', 'label' => 'Pipeline Stage', 'type' => 'Dropdown', 'required' => true],
                ['name' => 'amount', 'label' => 'Deal Amount', 'type' => 'Currency', 'required' => true],
                ['name' => 'probability', 'label' => 'Win Probability (%)', 'type' => 'Number'],
                ['name' => 'expected_close_date', 'label' => 'Target Close Date', 'type' => 'Date'],
            ],
            'purchase_deals' => [
                ['name' => 'po_number', 'label' => 'PO Number', 'type' => 'Text', 'required' => true],
                ['name' => 'title', 'label' => 'PO Title / Description', 'type' => 'Text', 'required' => true],
                ['name' => 'vendor_id', 'label' => 'Supplier / Vendor', 'type' => 'Dropdown', 'required' => true],
                ['name' => 'amount', 'label' => 'PO Total Spend', 'type' => 'Currency', 'required' => true],
                ['name' => 'stage', 'label' => 'Procurement Stage', 'type' => 'Dropdown', 'required' => true],
                ['name' => 'expected_delivery_date', 'label' => 'Target Delivery Date', 'type' => 'Date'],
            ],
            'shipping' => [
                ['name' => 'tracking_number', 'label' => 'Tracking Number', 'type' => 'Text', 'required' => true],
                ['name' => 'carrier', 'label' => 'Freight Carrier', 'type' => 'Dropdown', 'required' => true],
                ['name' => 'type', 'label' => 'Direction', 'type' => 'Dropdown', 'required' => true],
                ['name' => 'status', 'label' => 'Shipment Status', 'type' => 'Dropdown', 'required' => true],
                ['name' => 'account_id', 'label' => 'Associated Account', 'type' => 'Dropdown', 'required' => true],
                ['name' => 'shipping_origin', 'label' => 'Origin / Dispatch Facility', 'type' => 'Text'],
                ['name' => 'destination_address', 'label' => 'Destination Address', 'type' => 'Textarea'],
                ['name' => 'estimated_delivery_date', 'label' => 'Estimated Delivery Date', 'type' => 'Date'],
                ['name' => 'actual_delivery_date', 'label' => 'Actual Delivery Date', 'type' => 'Date'],
            ],
            'payments' => [
                ['name' => 'invoice_number', 'label' => 'Invoice Number', 'type' => 'Text', 'required' => true],
                ['name' => 'account_id', 'label' => 'Associated Account', 'type' => 'Dropdown', 'required' => true],
                ['name' => 'amount', 'label' => 'Settlement Amount', 'type' => 'Currency', 'required' => true],
                ['name' => 'status', 'label' => 'Settlement Status', 'type' => 'Dropdown', 'required' => true],
                ['name' => 'type', 'label' => 'Category', 'type' => 'Dropdown', 'required' => true],
                ['name' => 'due_date', 'label' => 'Due Date', 'type' => 'Date'],
                ['name' => 'paid_amount', 'label' => 'Paid Amount', 'type' => 'Currency'],
            ],
            'wires' => [
                ['name' => 'reference_number', 'label' => 'Federal / Wire Reference', 'type' => 'Text', 'required' => true],
                ['name' => 'bank_name', 'label' => 'Originating Institution', 'type' => 'Text', 'required' => true],
                ['name' => 'amount', 'label' => 'Remittance Amount', 'type' => 'Currency', 'required' => true],
                ['name' => 'status', 'label' => 'Clearing Status', 'type' => 'Dropdown', 'required' => true],
                ['name' => 'sender_account', 'label' => 'Sender Account Identifier', 'type' => 'Text'],
                ['name' => 'value_date', 'label' => 'Value / Posting Date', 'type' => 'Date'],
            ],
            'tasks' => [
                ['name' => 'subject', 'label' => 'Subject', 'type' => 'Text', 'required' => true],
                ['name' => 'priority', 'label' => 'Priority', 'type' => 'Dropdown', 'required' => true],
                ['name' => 'status', 'label' => 'Status', 'type' => 'Dropdown', 'required' => true],
                ['name' => 'assigned_to', 'label' => 'Assigned To', 'type' => 'Lookup'],
                ['name' => 'due_date', 'label' => 'Due Date', 'type' => 'Date'],
                ['name' => 'comments', 'label' => 'Comments', 'type' => 'Textarea'],
                ['name' => 'related_entity_name', 'label' => 'Related To', 'type' => 'Lookup'],
            ],
            'users' => [
                ['name' => 'full_name', 'label' => 'Full Name', 'type' => 'Text', 'required' => true],
                ['name' => 'email', 'label' => 'Email Address', 'type' => 'Email', 'required' => true],
                ['name' => 'role_id', 'label' => 'Assign Role', 'type' => 'Lookup', 'required' => true],
                ['name' => 'profile_id', 'label' => 'Security Profile', 'type' => 'Lookup', 'required' => true],
                ['name' => 'status', 'label' => 'User Status', 'type' => 'Dropdown', 'required' => true],
                ['name' => 'phone', 'label' => 'Phone Number', 'type' => 'Phone'],
            ]
        ];

        $fields = $standardFields[$module] ?? [
            ['name' => 'name', 'label' => 'Name', 'type' => 'Text', 'required' => true],
            ['name' => 'notes', 'label' => 'Notes', 'type' => 'Textarea'],
        ];

        $mainSectionTitle = ucfirst(str_replace(['__c', '_'], ['', ' '], $module)) . ' Information';

        return [
            'sections' => [
                [
                    'id'               => 'sec-primary',
                    'title'            => $mainSectionTitle,
                    'columns'          => 2,
                    'collapsible'      => true,
                    'defaultCollapsed' => false,
                    'fields'           => $fields,
                ],
                [
                    'id'               => 'sec-system-info',
                    'title'            => 'System Information',
                    'columns'          => 2,
                    'collapsible'      => true,
                    'defaultCollapsed' => false,
                    'fields'           => [
                        ['name' => 'id', 'label' => 'Record ID', 'type' => 'Text', 'readOnly' => true],
                        ['name' => 'created_at', 'label' => 'Created Date', 'type' => 'Date', 'readOnly' => true],
                        ['name' => 'updated_at', 'label' => 'Last Modified Date', 'type' => 'Date', 'readOnly' => true],
                        ['name' => 'created_by', 'label' => 'Created By', 'type' => 'Text', 'readOnly' => true],
                    ],
                ]
            ],
            'relatedLists' => [
                [
                    'relation'   => 'contacts',
                    'label'      => 'Contacts',
                    'enabled'    => true,
                    'location'   => 'right_sidebar',
                    'columns'    => ['first_name', 'last_name', 'email', 'phone', 'title'],
                ],
                [
                    'relation'   => 'opportunities',
                    'label'      => 'Opportunities',
                    'enabled'    => true,
                    'location'   => 'right_sidebar',
                    'columns'    => ['name', 'stage', 'amount', 'probability', 'expected_close_date'],
                ],
                [
                    'relation'   => 'files',
                    'label'      => 'Notes & Attachments',
                    'enabled'    => true,
                    'location'   => 'right_sidebar',
                    'columns'    => ['file_name', 'file_size', 'created_at'],
                ],
            ],
            'components' => [
                'layoutStyle'             => 'two_column',
                'sidebarOrder'            => ['files', 'activity', 'related', 'approvals', 'field_history', 'emails'],
                'filesLocation'           => 'right_sidebar',
                'activityLocation'        => 'right_sidebar',
                'relatedLocation'         => 'right_sidebar',
                'showApprovalHistory'     => true,
                'showFieldHistory'        => true,
                'showEmails'              => true,
                'emailsVisibilityPolicy'  => 'everyone',
            ],
            'activitySettings' => [
                'allowedTypes' => ['Call', 'Task', 'Meeting', 'Note'],
                'defaultType'  => 'Call',
            ],
            'dependentFields' => [],
            'validationRules' => [],
            'showSystemInfo'  => true,
            'headerConfig'    => [
                'primary_field'     => $fields[0]['name'] ?? 'name',
                'show_status_badge' => true,
            ],
        ];
    }

    public function ensureDefaultPageLayouts($tenant, $module)
    {
        $this->ensureTenantConnection($tenant);

        if (PageLayout::on('tenant')->where('module', $module)->count() === 0) {
            $formattedName = ucfirst(str_replace(['__c', '_'], ['', ' '], $module)) . ' Default Layout';
            PageLayout::on('tenant')->create([
                'module'      => $module,
                'name'        => $formattedName,
                'description' => 'Standard enterprise production layout with 2-column detail sections, related lists, and activity tracking.',
                'is_default'  => true,
                'layout_json' => $this->getDefaultLayoutDefinition($module),
                'created_by'  => 'System Admin',
                'updated_by'  => 'System Admin',
            ]);
        }
    }

    public function pageLayouts()
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);

        $initialModule = request()->query('module', 'leads');
        $this->ensureDefaultPageLayouts($tenant, $initialModule);

        $customObjects = CustomObject::on('tenant')->orderBy('label')->get();
        $profiles = Profile::on('tenant')->orderBy('name')->get();

        $layouts = PageLayout::on('tenant')
            ->where('module', $initialModule)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        $assignments = ProfileLayoutAssignment::on('tenant')
            ->where('module', $initialModule)
            ->pluck('layout_id', 'profile_id')
            ->toArray();

        $customFields = CustomField::on('tenant')->get();
        $standardOverrides = StandardFieldOverride::on('tenant')
            ->where('module', $initialModule)
            ->get()
            ->keyBy('field_name');

        return view('admin.page_layouts', compact('initialModule', 'customObjects', 'profiles', 'layouts', 'assignments', 'customFields', 'standardOverrides'));
    }

    public function getPageLayoutsApi(Request $request, $module)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);
        $this->ensureDefaultPageLayouts($tenant, $module);

        $layouts = PageLayout::on('tenant')
            ->where('module', $module)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        $assignmentRecords = ProfileLayoutAssignment::on('tenant')
            ->with('profile')
            ->where('module', $module)
            ->get();

        $assignments = [];
        $assignmentDetails = [];

        foreach ($assignmentRecords as $rec) {
            $assignments[(string) $rec->profile_id] = (string) $rec->layout_id;
            $assignmentDetails[] = [
                'id'           => (string) $rec->id,
                'profile_id'   => (string) $rec->profile_id,
                'layout_id'    => (string) $rec->layout_id,
                'profile_name' => $rec->profile ? $rec->profile->name : 'Unknown Profile',
            ];
        }

        $customFields = CustomField::on('tenant')
            ->where('module', $module)
            ->get();

        $standardOverrides = StandardFieldOverride::on('tenant')
            ->where('module', $module)
            ->get()
            ->keyBy('field_name');

        return response()->json([
            'layouts'            => $layouts,
            'assignments'        => $assignments,
            'assignmentDetails'  => $assignmentDetails,
            'custom_fields'      => $customFields,
            'standard_overrides' => $standardOverrides,
        ]);
    }

    public function getActivePageLayoutApi(Request $request, $module)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);
        $this->ensureDefaultPageLayouts($tenant, $module);

        $profile = Profile::on('tenant')->first();
        $profileId = $profile ? $profile->id : 1;

        $assignment = ProfileLayoutAssignment::on('tenant')
            ->where('module', $module)
            ->where('profile_id', $profileId)
            ->first();

        $layout = null;
        if ($assignment) {
            $layout = PageLayout::on('tenant')->find($assignment->layout_id);
        }

        if (!$layout) {
            $layout = PageLayout::on('tenant')->where('module', $module)->where('is_default', true)->first();
        }

        if (!$layout) {
            $layout = PageLayout::on('tenant')->where('module', $module)->first();
        }

        if (!$layout) {
            return response()->json(['success' => false, 'message' => 'No layout found.'], 404);
        }

        $res = $layout->toArray();
        $res['resolvedProfileId'] = (string) $profileId;
        return response()->json($res);
    }

    public function getPageLayoutApi(Request $request, $module, $id)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);

        $layout = PageLayout::on('tenant')->where('module', $module)->find($id);
        if (!$layout) {
            return response()->json(['success' => false, 'message' => 'Page layout not found.'], 404);
        }

        return response()->json($layout);
    }

    public function createPageLayoutApi(Request $request, $module)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);

        $request->validate([
            'name'          => 'required|string|max:150',
            'description'   => 'nullable|string|max:500',
            'clone_from_id' => 'nullable',
        ]);

        $name = trim($request->input('name'));
        $description = $request->input('description');
        $cloneFromId = $request->input('clone_from_id');

        $layoutJson = null;
        if ($cloneFromId) {
            $source = PageLayout::on('tenant')->where('module', $module)->find($cloneFromId);
            if ($source) {
                $layoutJson = $source->layout_json;
            }
        }

        if (!$layoutJson && $request->has('layout')) {
            $layoutJson = $request->input('layout');
        }

        if (!$layoutJson) {
            $layoutJson = $this->getDefaultLayoutDefinition($module);
        }

        $isDefault = $request->boolean('is_default', false);
        if ($isDefault) {
            PageLayout::on('tenant')->where('module', $module)->update(['is_default' => false]);
        }

        $layout = PageLayout::on('tenant')->create([
            'module'      => $module,
            'name'        => $name,
            'description' => $description,
            'is_default'  => $isDefault,
            'layout_json' => $layoutJson,
            'created_by'  => 'System Admin',
            'updated_by'  => 'System Admin',
        ]);

        return response()->json($layout, 201);
    }

    public function updatePageLayoutApi(Request $request, $module, $id)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);

        $layout = PageLayout::on('tenant')->where('module', $module)->find($id);
        if (!$layout) {
            return response()->json(['success' => false, 'message' => 'Page layout not found.'], 404);
        }

        if ($request->has('name') && !empty($request->input('name'))) {
            $layout->name = trim($request->input('name'));
        }

        if ($request->has('description')) {
            $layout->description = $request->input('description');
        }

        if ($request->has('is_default')) {
            $isDef = $request->boolean('is_default');
            if ($isDef) {
                PageLayout::on('tenant')->where('module', $module)->where('id', '!=', $id)->update(['is_default' => false]);
            }
            $layout->is_default = $isDef;
        }

        if ($request->has('layout')) {
            $layout->layout_json = $request->input('layout');
        } elseif ($request->has('layout_json')) {
            $layout->layout_json = $request->input('layout_json');
        }

        $layout->updated_by = 'System Admin';
        $layout->save();

        return response()->json($layout);
    }

    public function deletePageLayoutApi(Request $request, $module, $id)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);

        $layout = PageLayout::on('tenant')->where('module', $module)->find($id);
        if (!$layout) {
            return response()->json(['success' => false, 'message' => 'Page layout not found.'], 404);
        }

        $wasDefault = $layout->is_default;
        $layout->delete();

        if ($wasDefault) {
            $remaining = PageLayout::on('tenant')->where('module', $module)->first();
            if ($remaining) {
                $remaining->is_default = true;
                $remaining->save();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Layout deleted.'
        ]);
    }

    public function setDefaultPageLayoutApi(Request $request, $module, $id)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);

        PageLayout::on('tenant')->where('module', $module)->update(['is_default' => false]);
        $layout = PageLayout::on('tenant')->where('module', $module)->find($id);
        if (!$layout) {
            return response()->json(['success' => false, 'message' => 'Page layout not found.'], 404);
        }

        $layout->is_default = true;
        $layout->save();

        return response()->json([
            'success' => true,
            'message' => 'Set as default layout.',
            'is_default' => true,
            'layout' => $layout,
        ]);
    }

    public function savePageLayoutAssignmentsApi(Request $request, $module)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);

        $assignments = $request->input('assignments', []);
        if (is_array($assignments)) {
            foreach ($assignments as $profileId => $layoutId) {
                if (!empty($profileId) && !empty($layoutId)) {
                    ProfileLayoutAssignment::on('tenant')->updateOrCreate(
                        ['profile_id' => $profileId, 'module' => $module],
                        ['layout_id' => $layoutId]
                    );
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Profile layout assignments updated!'
        ]);
    }

    public function getFieldHistoryConfigApi(Request $request, $module)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);

        $config = FieldHistoryConfig::on('tenant')->where('module', $module)->first();
        if (!$config) {
            return response()->json([
                'module'         => $module,
                'tracked_fields' => [],
                'enabled'        => true,
            ]);
        }

        return response()->json($config);
    }

    public function saveFieldHistoryConfigApi(Request $request, $module)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);

        $trackedFields = $request->input('tracked_fields', []);
        $enabled = $request->boolean('enabled', true);

        $config = FieldHistoryConfig::on('tenant')->updateOrCreate(
            ['module' => $module],
            [
                'tracked_fields' => is_array($trackedFields) ? $trackedFields : [],
                'enabled'        => $enabled,
            ]
        );

        return response()->json([
            'success'        => true,
            'module'         => $config->module,
            'tracked_fields' => $config->tracked_fields ?? [],
            'enabled'        => (bool)$config->enabled,
            'config'         => $config,
        ]);
    }

    public function dataStudio()
    {
        return view('admin.data_studio');
    }

    public function auditLogs()
    {
        return view('admin.audit_logs');
    }

    public function ensureDefaultCustomButtons($tenant): void
    {
        $this->ensureTenantConnection($tenant);

        if (CustomButton::on('tenant')->count() === 0) {
            $defaultButtons = [
                // Leads
                [
                    'entity_type'   => 'leads',
                    'name'          => 'send_welcome_kit',
                    'label'         => 'Send Welcome Kit',
                    'icon'          => 'Mail',
                    'style_variant' => 'indigo',
                    'action_type'   => 'send_email',
                    'action_config' => [
                        'template_id'             => 'tmpl_welcome',
                        'execution_mode'          => 'composer',
                        'default_recipient_field' => 'email',
                    ],
                    'placement'     => 'record_header',
                    'visibility_conditions' => [],
                    'is_active'     => true,
                    'display_order' => 1,
                ],
                [
                    'entity_type'   => 'leads',
                    'name'          => 'qualify_lead',
                    'label'         => 'Qualify Lead',
                    'icon'          => 'CheckCircle',
                    'style_variant' => 'success',
                    'action_type'   => 'workflow_trigger',
                    'action_config' => [
                        'trigger_type'    => 'field_update',
                        'field_updates'   => ['status' => 'Qualified'],
                        'success_message' => 'Lead has been successfully qualified!',
                    ],
                    'placement'     => 'record_header',
                    'visibility_conditions' => [
                        ['field' => 'status', 'operator' => 'not_equals', 'value' => 'Qualified']
                    ],
                    'is_active'     => true,
                    'display_order' => 2,
                ],
                [
                    'entity_type'   => 'leads',
                    'name'          => 'verify_profile',
                    'label'         => 'Verify Profile',
                    'icon'          => 'Globe',
                    'style_variant' => 'primary',
                    'action_type'   => 'url_redirect',
                    'action_config' => [
                        'url_template' => 'https://www.google.com/search?q={{company}}+{{first_name}}+{{last_name}}',
                        'open_target'  => '_blank',
                    ],
                    'placement'     => 'record_header',
                    'visibility_conditions' => [],
                    'is_active'     => true,
                    'display_order' => 3,
                ],

                // Opportunities
                [
                    'entity_type'   => 'opportunities',
                    'name'          => 'send_proposal',
                    'label'         => 'Send Proposal',
                    'icon'          => 'Send',
                    'style_variant' => 'primary',
                    'action_type'   => 'send_email',
                    'action_config' => [
                        'template_id'             => 'tmpl_quote',
                        'execution_mode'          => 'composer',
                        'default_recipient_field' => 'email',
                    ],
                    'placement'     => 'record_header',
                    'visibility_conditions' => [],
                    'is_active'     => true,
                    'display_order' => 1,
                ],
                [
                    'entity_type'   => 'opportunities',
                    'name'          => 'weight_dims_cargo',
                    'label'         => 'Weight & Dimensions',
                    'icon'          => 'Box',
                    'style_variant' => 'purple',
                    'action_type'   => 'quick_action',
                    'action_config' => [
                        'component'           => 'add_weight_dims',
                        'default_weight_unit' => 'LBS',
                        'default_dim_unit'    => 'INCH',
                        'description'         => 'Opens Weight & Dimensions table specification studio',
                    ],
                    'placement'     => 'record_header',
                    'visibility_conditions' => [],
                    'is_active'     => true,
                    'display_order' => 2,
                ],
                [
                    'entity_type'   => 'opportunities',
                    'name'          => 'mark_closed_won',
                    'label'         => 'Mark as Closed Won',
                    'icon'          => 'CheckCircle',
                    'style_variant' => 'success',
                    'action_type'   => 'workflow_trigger',
                    'action_config' => [
                        'trigger_type'    => 'field_update',
                        'field_updates'   => ['stage' => 'Closed Won'],
                        'success_message' => 'Opportunity marked as Closed Won!',
                    ],
                    'placement'     => 'record_header',
                    'visibility_conditions' => [
                        ['field' => 'stage', 'operator' => 'not_equals', 'value' => 'Closed Won']
                    ],
                    'is_active'     => true,
                    'display_order' => 3,
                ],

                // Shipping
                [
                    'entity_type'   => 'shipping',
                    'name'          => 'track_freight',
                    'label'         => 'Track Freight',
                    'icon'          => 'ExternalLink',
                    'style_variant' => 'indigo',
                    'action_type'   => 'url_redirect',
                    'action_config' => [
                        'url_template' => 'https://www.google.com/search?q={{carrier}}+tracking+{{tracking_number}}',
                        'open_target'  => '_blank',
                    ],
                    'placement'     => 'record_header',
                    'visibility_conditions' => [],
                    'is_active'     => true,
                    'display_order' => 1,
                ],
                [
                    'entity_type'   => 'shipping',
                    'name'          => 'cargo_specifications',
                    'label'         => 'Cargo Specifications',
                    'icon'          => 'Package',
                    'style_variant' => 'amber',
                    'action_type'   => 'quick_action',
                    'action_config' => [
                        'component'           => 'add_weight_dims',
                        'default_weight_unit' => 'KG',
                        'default_dim_unit'    => 'CM',
                        'description'         => 'Opens freight dimensions and packing manifest',
                    ],
                    'placement'     => 'record_header',
                    'visibility_conditions' => [],
                    'is_active'     => true,
                    'display_order' => 2,
                ],

                // Purchase Deals
                [
                    'entity_type'   => 'purchase_deals',
                    'name'          => 'po_cargo_specs',
                    'label'         => 'PO Cargo Specs',
                    'icon'          => 'Scale',
                    'style_variant' => 'indigo',
                    'action_type'   => 'quick_action',
                    'action_config' => [
                        'component'           => 'add_weight_dims',
                        'default_weight_unit' => 'KG',
                        'default_dim_unit'    => 'CM',
                    ],
                    'placement'     => 'record_header',
                    'visibility_conditions' => [],
                    'is_active'     => true,
                    'display_order' => 1,
                ],
                [
                    'entity_type'   => 'purchase_deals',
                    'name'          => 'approve_po',
                    'label'         => 'Approve PO',
                    'icon'          => 'CheckCircle',
                    'style_variant' => 'success',
                    'action_type'   => 'workflow_trigger',
                    'action_config' => [
                        'trigger_type'    => 'field_update',
                        'field_updates'   => ['stage' => 'Approved'],
                        'success_message' => 'Purchase order approved!',
                    ],
                    'placement'     => 'record_header',
                    'visibility_conditions' => [
                        ['field' => 'stage', 'operator' => 'not_equals', 'value' => 'Approved']
                    ],
                    'is_active'     => true,
                    'display_order' => 2,
                ],
            ];

            foreach ($defaultButtons as $btnData) {
                CustomButton::on('tenant')->create($btnData);
            }
        }
    }

    public function customButtonsStudio(Request $request)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);
        $this->ensureDefaultCustomButtons($tenant);

        $selectedEntity = $request->query('entity', 'all');
        $query = CustomButton::on('tenant')->orderBy('display_order')->orderBy('id');
        if ($selectedEntity !== 'all') {
            $query->where('entity_type', $selectedEntity);
        }
        $buttons = $query->get();

        $templates = EmailTemplate::on('tenant')->where('is_active', true)->get();
        $notificationRules = NotificationRule::on('tenant')->get();
        $customObjects = CustomObject::on('tenant')->get();
        try {
            $processFlows = Schema::connection('tenant')->hasTable('process_flows')
                ? DB::connection('tenant')->table('process_flows')->get()
                : collect();
        } catch (\Throwable $e) {
            $processFlows = collect();
        }

        return view('admin.custom_buttons', [
            'buttons'           => $buttons,
            'templates'         => $templates,
            'notificationRules' => $notificationRules,
            'customObjects'     => $customObjects,
            'processFlows'      => $processFlows,
            'selectedEntity'    => $selectedEntity,
        ]);
    }

    public function getCustomButtonsApi(Request $request)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);
        $this->ensureDefaultCustomButtons($tenant);

        $query = CustomButton::on('tenant')->orderBy('display_order')->orderBy('id');

        if ($request->filled('entity_type') && $request->input('entity_type') !== 'all') {
            $query->where('entity_type', $request->input('entity_type'));
        }

        if ($request->has('is_active')) {
            $isActive = filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($isActive !== null) {
                $query->where('is_active', $isActive);
            }
        }

        return response()->json($query->get());
    }

    public function getCustomButtonApi($id)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);

        $button = CustomButton::on('tenant')->findOrFail($id);
        return response()->json($button);
    }

    public function createCustomButtonApi(Request $request)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);

        $validated = $request->validate([
            'entity_type'           => 'required|string|max:60',
            'label'                 => 'required|string|max:150',
            'name'                  => 'nullable|string|max:100',
            'icon'                  => 'nullable|string|max:50',
            'style_variant'         => 'nullable|string|max:30',
            'action_type'           => 'required|string|max:50',
            'action_config'         => 'nullable|array',
            'placement'             => 'nullable|string|max:40',
            'visibility_conditions' => 'nullable|array',
            'is_active'             => 'nullable',
            'display_order'         => 'nullable|integer',
        ]);

        if (empty($validated['name'])) {
            $validated['name'] = Str::snake(preg_replace('/[^a-zA-Z0-9_]/', '', $validated['label']));
        }
        $validated['icon'] = $validated['icon'] ?? 'Mail';
        $validated['style_variant'] = $validated['style_variant'] ?? 'secondary';
        $validated['placement'] = $validated['placement'] ?? 'record_header';
        $validated['action_config'] = $validated['action_config'] ?? [];
        $validated['visibility_conditions'] = $validated['visibility_conditions'] ?? [];
        $validated['is_active'] = isset($validated['is_active']) ? (bool)$validated['is_active'] : true;

        $button = CustomButton::on('tenant')->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Custom button created successfully.',
            'data'    => $button,
        ], 201);
    }

    public function updateCustomButtonApi(Request $request, $id)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);

        $button = CustomButton::on('tenant')->findOrFail($id);

        $data = $request->only([
            'entity_type', 'name', 'label', 'icon', 'style_variant',
            'action_type', 'action_config', 'placement',
            'visibility_conditions', 'is_active', 'display_order'
        ]);

        if (isset($data['is_active'])) {
            $data['is_active'] = (bool)$data['is_active'];
        }

        $button->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Custom button updated successfully.',
            'data'    => $button,
        ]);
    }

    public function deleteCustomButtonApi($id)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);

        $button = CustomButton::on('tenant')->findOrFail($id);
        $button->delete();

        return response()->json([
            'success' => true,
            'message' => 'Custom button deleted successfully.',
        ]);
    }

    public function executeCustomButtonApi(Request $request, $id)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);

        $button = CustomButton::on('tenant')->findOrFail($id);
        $recordId = $request->input('recordId');
        $cfg = $button->action_config ?? [];
        $msg = $cfg['success_message'] ?? 'Action completed successfully.';

        if ($button->action_type === 'workflow_trigger' && ($cfg['trigger_type'] ?? '') === 'field_update') {
            $entity = $button->entity_type;
            $updates = $cfg['field_updates'] ?? [];
            if ($recordId && !empty($updates)) {
                $tableMap = [
                    'leads'          => 'leads',
                    'accounts'       => 'accounts',
                    'contacts'       => 'contacts',
                    'opportunities'  => 'opportunities',
                    'purchase_deals' => 'purchase_deals',
                    'shipping'       => 'shipments',
                    'payments'       => 'payments',
                    'wires'          => 'wires_received',
                    'tasks'          => 'tasks',
                ];
                $tbl = $tableMap[$entity] ?? (str_ends_with($entity, '__c') ? 'custom_object_records' : null);
                if ($tbl) {
                    DB::connection('tenant')->table($tbl)->where('id', $recordId)->update($updates);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => $msg,
            'button'  => $button,
        ]);
    }
}
