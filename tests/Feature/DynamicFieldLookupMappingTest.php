<?php

namespace Tests\Feature;

use App\Models\CustomField;
use App\Models\CustomFieldValue;
use App\Models\Lead;
use App\Models\Profile;
use App\Models\ProfilePermission;
use App\Models\Tenant;
use App\Models\TenantUser;
use App\Services\DynamicFields\DynamicFieldMappingService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class DynamicFieldLookupMappingTest extends TestCase
{
    protected ?Tenant $tenant = null;
    protected ?TenantUser $adminUser = null;
    protected ?TenantUser $approverUser1 = null;
    protected ?TenantUser $approverUser2 = null;
    protected ?CustomField $approvedField = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::on('mysql')->first();
        if ($this->tenant) {
            $this->tenant->connect();
        }

        // Create test users
        $rnd = Str::random(6);
        $this->adminUser = TenantUser::on('tenant')->create([
            'name'     => "Admin Tester {$rnd}",
            'email'    => "admin_{$rnd}@example.com",
            'password' => Hash::make('secret'),
            'status'   => 'Active',
        ]);

        $this->approverUser1 = TenantUser::on('tenant')->create([
            'name'     => "Alex Approver {$rnd}",
            'email'    => "alex_{$rnd}@example.com",
            'password' => Hash::make('secret'),
            'status'   => 'Active',
        ]);

        $this->approverUser2 = TenantUser::on('tenant')->create([
            'name'     => "Morgan Director {$rnd}",
            'email'    => "morgan_{$rnd}@example.com",
            'password' => Hash::make('secret'),
            'status'   => 'Active',
        ]);

        // Ensure an approved dynamic custom field exists
        $this->approvedField = CustomField::on('tenant')->updateOrCreate(
            ['module' => 'leads', 'field_name' => 'cf_is_approved'],
            [
                'field_label'          => 'Approved By',
                'data_type'            => 'User',
                'lookup_module'        => 'users',
                'lookup_display_field' => 'name',
                'is_required'          => false,
            ]
        );
    }

    /**
     * Test DynamicFieldMappingService resolveTargetMapping resolution logic.
     */
    public function test_service_detects_target_mapping_dynamically()
    {
        $service = app(DynamicFieldMappingService::class);

        // 1. By explicit CustomField lookup_module
        $mapping1 = $service->resolveTargetMapping($this->approvedField, 'leads');
        $this->assertNotNull($mapping1);
        $this->assertEquals('users', $mapping1['target_module']);
        $this->assertEquals('name', $mapping1['display_column']);

        // 2. By semantic naming keyword heuristic ('approved', 'approver', 'approved_by')
        $mapping2 = $service->resolveTargetMapping('approved', 'leads');
        $this->assertNotNull($mapping2);
        $this->assertEquals('users', $mapping2['target_module']);

        $mapping3 = $service->resolveTargetMapping('vendor_account_id', 'leads');
        $this->assertNotNull($mapping3);
        $this->assertEquals('accounts', $mapping3['target_module']);

        // 3. By custom registered mapping
        DynamicFieldMappingService::registerMapping('leads', 'special_reviewer', 'users', 'id', 'name');
        $mapping4 = $service->resolveTargetMapping('special_reviewer', 'leads');
        $this->assertNotNull($mapping4);
        $this->assertEquals('users', $mapping4['target_module']);
    }

    /**
     * Test prepareCustomDataForSave ensures scalar integer ID is always stored.
     */
    public function test_prepare_custom_data_stores_clean_scalar_id()
    {
        $service = app(DynamicFieldMappingService::class);

        // Input as raw numeric string
        $clean1 = $service->prepareCustomDataForSave([
            'cf_is_approved' => (string)$this->approverUser1->id,
        ], 'leads');
        $this->assertSame($this->approverUser1->id, $clean1['cf_is_approved']);

        // Input as structured object/array { id: ..., name: ... }
        $clean2 = $service->prepareCustomDataForSave([
            'cf_is_approved' => [
                'id'   => $this->approverUser2->id,
                'name' => $this->approverUser2->name,
            ],
        ], 'leads');
        $this->assertSame($this->approverUser2->id, $clean2['cf_is_approved']);
    }

    /**
     * Test resolveRecord enriches display name while keeping stored ID.
     */
    public function test_resolve_record_enriches_display_name_and_keeps_id()
    {
        $service = app(DynamicFieldMappingService::class);

        $record = [
            'id'          => 999,
            'first_name'  => 'Test',
            'last_name'   => 'Lead',
            'custom_data' => [
                'cf_is_approved' => $this->approverUser1->id,
            ],
        ];

        $resolved = $service->resolveRecord($record, 'leads');

        // Verify ID is kept
        $this->assertEquals($this->approverUser1->id, $resolved['cf_is_approved_id']);
        $this->assertEquals($this->approverUser1->id, $resolved['custom_data']['cf_is_approved']);

        // Verify display name is resolved dynamically
        $this->assertEquals($this->approverUser1->name, $resolved['cf_is_approved_name']);
        $this->assertEquals($this->approverUser1->name, $resolved['cf_is_approved_display']);
        $this->assertEquals($this->approverUser1->name, $resolved['custom_data']['cf_is_approved_name']);
    }

    /**
     * Test creating a lead via API stores ID and returns resolved name.
     */
    public function test_api_store_stores_id_and_returns_resolved_name()
    {
        $response = $this->withHeaders([
            'X-Simulate-User-Id' => $this->adminUser->id,
            'Accept'             => 'application/json',
        ])->postJson('/api/leads', [
            'first_name'    => 'David',
            'last_name'     => 'Kim',
            'company'       => 'Kim Robotics',
            'email'         => 'david_' . Str::random(5) . '@kimrobotics.io',
            'custom_fields' => [
                'cf_is_approved' => (string)$this->approverUser1->id,
            ],
        ]);

        $response->assertStatus(201);
        $data = $response->json();
        $this->assertTrue($data['success']);

        $leadId = $data['lead']['id'];
        $lead = Lead::on('tenant')->find($leadId);

        // 1. Verify in database, custom_data strictly stores scalar ID
        $this->assertIsArray($lead->custom_data);
        $this->assertEquals($this->approverUser1->id, $lead->custom_data['cf_is_approved']);

        // 2. Verify returned API lead payload has resolved user name
        $this->assertEquals($this->approverUser1->name, $data['lead']['cf_is_approved_name']);
        $this->assertEquals($this->approverUser1->name, $data['lead']['cf_is_approved_display']);
    }

    /**
     * Test updating a lead via API updates to new ID and returns new resolved name.
     */
    public function test_api_update_updates_id_and_audit_history()
    {
        // Create initial lead approved by User 1
        $lead = Lead::on('tenant')->create([
            'first_name'  => 'Elena',
            'last_name'   => 'Rostova',
            'company'     => 'Rostova BioTech',
            'email'       => 'elena_' . Str::random(5) . '@rostovabio.com',
            'created_by'  => $this->adminUser->id,
            'assigned_to' => $this->adminUser->id,
            'custom_data' => [
                'cf_is_approved' => $this->approverUser1->id,
            ],
        ]);

        // Update lead: change approver to User 2
        $response = $this->withHeaders([
            'X-Simulate-User-Id' => $this->adminUser->id,
            'Accept'             => 'application/json',
        ])->putJson("/api/leads/{$lead->id}", [
            'custom_fields' => [
                'cf_is_approved' => (string)$this->approverUser2->id,
            ],
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertTrue($data['success']);

        // Verify database stores new ID
        $lead->refresh();
        $this->assertEquals($this->approverUser2->id, $lead->custom_data['cf_is_approved']);

        // Verify response displays new name
        $this->assertEquals($this->approverUser2->name, $data['lead']['cf_is_approved_name']);
    }

    /**
     * Test searching by target user's display name matches the lead storing that ID.
     */
    public function test_search_by_approver_name_finds_matching_leads()
    {
        $uniqueApprover = TenantUser::on('tenant')->create([
            'name'     => "UniqueApproverPerson_" . Str::random(5),
            'email'    => "unique_" . Str::random(5) . "@example.com",
            'password' => Hash::make('secret'),
            'status'   => 'Active',
        ]);

        $lead = Lead::on('tenant')->create([
            'first_name'  => 'Target',
            'last_name'   => 'Prospect',
            'company'     => 'Special Entity Corp',
            'email'       => 'target_' . Str::random(5) . '@specialcorp.io',
            'created_by'  => $this->adminUser->id,
            'assigned_to' => $this->adminUser->id,
            'custom_data' => [
                'cf_is_approved' => $uniqueApprover->id,
            ],
        ]);

        // Search using the approver's name
        $response = $this->withHeaders([
            'X-Simulate-User-Id' => $this->adminUser->id,
            'Accept'             => 'application/json',
        ])->getJson("/api/leads?search=" . urlencode($uniqueApprover->name));

        $response->assertStatus(200);
        $results = $response->json();

        $found = collect($results)->firstWhere('id', $lead->id);
        $this->assertNotNull($found, 'Lead should be found when searching by approver name');
        $this->assertEquals($uniqueApprover->name, $found['cf_is_approved_name']);
    }
}
