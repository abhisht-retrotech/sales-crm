<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\CustomField;
use App\Models\GlobalPicklist;
use App\Models\StandardFieldOverride;
use App\Models\TenantSetting;

class ObjectFieldsTest extends TestCase
{
    protected ?Tenant $tenant = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::on('mysql')->first();
        if ($this->tenant) {
            $this->tenant->connect();
        }
    }

    public function test_object_fields_page_loads_with_studio_header_and_subtabs(): void
    {
        $response = $this->get('/admin/object-fields');

        $response->assertStatus(200);
        $response->assertSee('Object &amp; Field Manager Studio', false);
        $response->assertSee('Dynamic Schema Architecture', false);
        $response->assertSee('Global Picklist Value Sets', false);
        $response->assertSee('Map Lead Fields', false);
        $response->assertSee('+ Create Custom Field', false);
        $response->assertSee('kpiActiveCustomFields');
        $response->assertSee('object-fields.js');
    }

    public function test_get_custom_fields_api(): void
    {
        $response = $this->getJson('/api/settings/fields?module=leads');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => [
                'id',
                'module',
                'field_name',
                'field_label',
                'data_type',
            ]
        ]);
    }

    public function test_custom_field_crud_lifecycle(): void
    {
        // 1. Create
        $createRes = $this->postJson('/api/settings/fields', [
            'module'      => 'leads',
            'fieldLabel'  => 'Test Security Clearance',
            'dataType'    => 'Dropdown',
            'options'     => 'Level 1, Level 2, Top Secret',
            'isRequired'  => true,
            'helpText'    => 'Security clearance level for defence deals',
        ]);

        $createRes->assertStatus(201);
        $createRes->assertJson([
            'success' => true,
        ]);
        $fieldId = $createRes->json('field.id');
        $this->assertNotNull($fieldId);

        // 2. Read Single
        $showRes = $this->getJson("/api/settings/fields/{$fieldId}");
        $showRes->assertStatus(200);
        $showRes->assertJson([
            'id'          => $fieldId,
            'field_label' => 'Test Security Clearance',
            'data_type'   => 'Dropdown',
            'is_required' => true,
        ]);

        // 3. Update
        $updateRes = $this->putJson("/api/settings/fields/{$fieldId}", [
            'fieldLabel' => 'Test Security Clearance (Updated)',
            'isRequired' => false,
            'helpText'   => 'Updated help text for clearance',
        ]);

        $updateRes->assertStatus(200);
        $updateRes->assertJson([
            'success' => true,
            'field'   => [
                'field_label' => 'Test Security Clearance (Updated)',
                'is_required' => false,
            ]
        ]);

        // 4. Delete
        $deleteRes = $this->deleteJson("/api/settings/fields/{$fieldId}");
        $deleteRes->assertStatus(200);
        $deleteRes->assertJson([
            'success' => true,
        ]);

        // Assert removed
        $this->assertNull(CustomField::on('tenant')->find($fieldId));
    }

    public function test_standard_field_override_lifecycle(): void
    {
        // 1. Save Override
        $saveRes = $this->postJson('/api/settings/standard-fields', [
            'module'     => 'leads',
            'fieldName'  => 'status',
            'fieldLabel' => 'Lifecycle Qualification Stage',
            'isRequired' => true,
            'helpText'   => 'Override description for lead status',
            'options'    => 'New, Working, Qualified, Unqualified',
        ]);

        $saveRes->assertStatus(200);
        $saveRes->assertJson([
            'success' => true,
            'override' => [
                'module'      => 'leads',
                'field_name'  => 'status',
                'field_label' => 'Lifecycle Qualification Stage',
                'is_required' => true,
            ]
        ]);

        // 2. Fetch Overrides
        $getRes = $this->getJson('/api/settings/standard-fields?module=leads');
        $getRes->assertStatus(200);
        $overrides = $getRes->json();
        $this->assertTrue(collect($overrides)->contains('field_name', 'status'));
    }

    public function test_global_picklists_crud_lifecycle(): void
    {
        // 1. Create
        $createRes = $this->postJson('/api/global-picklists', [
            'name'        => 'Test Region Territories',
            'apiName'     => 'gp_test_territories',
            'description' => 'Global region codes',
            'values'      => "North America\nEurope\nAsia-Pacific\nLatin America",
        ]);

        $createRes->assertStatus(201);
        $createRes->assertJson([
            'success' => true,
            'data'    => [
                'name'     => 'Test Region Territories',
                'api_name' => 'gp_test_territories',
            ]
        ]);
        $gpId = $createRes->json('data.id');
        $this->assertNotNull($gpId);

        // 2. Read All & Single
        $listRes = $this->getJson('/api/global-picklists');
        $listRes->assertStatus(200);
        $this->assertTrue(collect($listRes->json())->contains('id', $gpId));

        $showRes = $this->getJson("/api/global-picklists/{$gpId}");
        $showRes->assertStatus(200);
        $showRes->assertJson([
            'id'   => $gpId,
            'name' => 'Test Region Territories',
        ]);
        $this->assertCount(4, $showRes->json('values'));

        // 3. Update
        $updateRes = $this->putJson("/api/global-picklists/{$gpId}", [
            'name'        => 'Test Region Territories (Global)',
            'description' => 'Updated descriptions',
        ]);
        $updateRes->assertStatus(200);
        $updateRes->assertJson([
            'success' => true,
            'data'    => [
                'name' => 'Test Region Territories (Global)',
            ]
        ]);

        // 4. Delete
        $deleteRes = $this->deleteJson("/api/global-picklists/{$gpId}");
        $deleteRes->assertStatus(200);
        $deleteRes->assertJson([
            'success' => true,
        ]);

        $this->assertNull(GlobalPicklist::on('tenant')->find($gpId));
    }

    public function test_lead_mappings_api(): void
    {
        // 1. Get Lead Mappings
        $getRes = $this->getJson('/api/lead-mappings');
        $getRes->assertStatus(200);
        $getRes->assertJsonStructure([
            'leadFields',
            'accountFields',
            'contactFields',
            'opportunityFields',
            'mappings',
        ]);

        // 2. Save Lead Mappings
        $mappingsPayload = [
            [
                'source_field_name'  => 'company',
                'source_field_label' => 'Company Name',
                'source_data_type'   => 'Text',
                'target_module'      => 'accounts',
                'target_field_name'  => 'name',
                'target_field_label' => 'Account Name',
                'target_data_type'   => 'Text',
                'is_active'          => 1,
            ],
            [
                'source_field_name'  => 'email',
                'source_field_label' => 'Email Address',
                'source_data_type'   => 'Email',
                'target_module'      => 'contacts',
                'target_field_name'  => 'email',
                'target_field_label' => 'Email Address',
                'target_data_type'   => 'Email',
                'is_active'          => 1,
            ]
        ];

        $saveRes = $this->putJson('/api/lead-mappings', [
            'mappings' => $mappingsPayload
        ]);

        $saveRes->assertStatus(200);
        $saveRes->assertJson([
            'success' => true,
        ]);

        // 3. Auto Match
        $autoMatchRes = $this->postJson('/api/lead-mappings/auto-match', [
            'targetModule' => 'accounts'
        ]);
        $autoMatchRes->assertStatus(200);
        $autoMatchRes->assertJsonStructure([
            'success',
            'proposed',
        ]);
    }
}
