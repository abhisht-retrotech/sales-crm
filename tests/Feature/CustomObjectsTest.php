<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\CustomObject;
use App\Models\CustomObjectRecord;

class CustomObjectsTest extends TestCase
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

    public function test_custom_objects_page_loads_with_studio_header_and_tailwind_markup(): void
    {
        $response = $this->get('/admin/custom-objects');

        $response->assertStatus(200);
        $response->assertSee('Custom Objects Studio');
        $response->assertSee('Salesforce-Style Dynamic Schema');
        $response->assertSee('coStudioRoot');
        $response->assertSee('coServerData');
        $response->assertSee('custom-objects.js');
    }

    public function test_get_custom_objects_api(): void
    {
        $response = $this->getJson('/api/custom-objects');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => [
                'id',
                'label',
                'plural_label',
                'api_name',
                'primary_field',
                'icon',
                'record_count',
            ]
        ]);
    }

    public function test_custom_object_crud_lifecycle(): void
    {
        // 1. Create Custom Object
        $createRes = $this->postJson('/api/custom-objects', [
            'label'         => 'Test Drone',
            'plural_label'  => 'Test Drones',
            'api_name'      => 'Test_Drone__c',
            'icon'          => 'Cpu',
            'primary_field' => 'name',
            'description'   => 'Automated delivery and inspection drones',
        ]);

        $createRes->assertStatus(201);
        $createdData = $createRes->json();
        $this->assertNotEmpty($createdData['id']);
        $this->assertEquals('Test_Drone__c', $createdData['api_name']);
        $objectId = $createdData['id'];

        // 2. Fetch Single Object
        $getRes = $this->getJson("/api/custom-objects/{$objectId}");
        $getRes->assertStatus(200);
        $this->assertEquals('Test Drone', $getRes->json('label'));

        // 3. Delete Custom Object
        $delRes = $this->deleteJson("/api/custom-objects/{$objectId}");
        $delRes->assertStatus(200);
        $delRes->assertJson(['success' => true]);

        // 4. Verify Not Found
        $afterDel = $this->getJson("/api/custom-objects/{$objectId}");
        $afterDel->assertStatus(404);
    }

    public function test_custom_object_auto_generates_c_suffix_and_plural(): void
    {
        $createRes = $this->postJson('/api/custom-objects', [
            'label' => 'Warehouse Bay',
            // Omitted plural_label and api_name
        ]);

        $createRes->assertStatus(201);
        $objId = $createRes->json('id');
        $this->assertEquals('Warehouse Bays', $createRes->json('plural_label'));
        $this->assertEquals('warehouse_bay__c', strtolower($createRes->json('api_name')));

        // Clean up
        $this->deleteJson("/api/custom-objects/{$objId}")->assertStatus(200);
    }

    public function test_custom_object_record_crud_lifecycle(): void
    {
        // Get or create custom object
        $listRes = $this->getJson('/api/custom-objects');
        $list = $listRes->json();
        $this->assertNotEmpty($list);
        $obj = $list[0];
        $objId = $obj['id'];

        // 1. Create record
        $createRecRes = $this->postJson("/api/custom-objects/{$objId}/records", [
            'name'  => 'Enterprise Unit Alpha #992',
            'notes' => 'Critical production component for testing',
            'extra_spec' => 'Gigabit LAN',
        ]);

        $createRecRes->assertStatus(201);
        $rec = $createRecRes->json('data');
        $this->assertNotEmpty($rec['id']);
        $this->assertEquals('Enterprise Unit Alpha #992', $rec['name']);
        $recId = $rec['id'];

        // 2. Get records list with search filter
        $listRecsRes = $this->getJson("/api/custom-objects/{$objId}/records?search=Alpha");
        $listRecsRes->assertStatus(200);
        $records = $listRecsRes->json('records');
        $this->assertNotEmpty($records);

        // 3. Get single record
        $getRecRes = $this->getJson("/api/custom-objects/{$objId}/records/{$recId}");
        $getRecRes->assertStatus(200);
        $this->assertEquals('Enterprise Unit Alpha #992', $getRecRes->json('data.name'));
        $this->assertEquals('Critical production component for testing', $getRecRes->json('data.notes'));

        // 4. Update record
        $updateRecRes = $this->putJson("/api/custom-objects/{$objId}/records/{$recId}", [
            'name'  => 'Enterprise Unit Alpha #992 Updated',
            'notes' => 'Updated notes text',
        ]);
        $updateRecRes->assertStatus(200);
        $this->assertEquals('Enterprise Unit Alpha #992 Updated', $updateRecRes->json('data.name'));
        $this->assertEquals('Updated notes text', $updateRecRes->json('data.notes'));

        // 5. Delete record
        $delRecRes = $this->deleteJson("/api/custom-objects/{$objId}/records/{$recId}");
        $delRecRes->assertStatus(200);
        $delRecRes->assertJson(['success' => true]);

        // 6. Verify deleted
        $afterDel = $this->getJson("/api/custom-objects/{$objId}/records/{$recId}");
        $afterDel->assertStatus(404);
    }

    public function test_spa_navigation_includes_custom_objects_hook(): void
    {
        $spaNavContent = file_get_contents(public_path('js/spa-navigation.js'));
        $this->assertStringContainsString('initCustomObjects', $spaNavContent);
        $this->assertStringContainsString('coStudioRoot', $spaNavContent);
        $this->assertStringContainsString('/js/custom-objects.js', $spaNavContent);
    }
}
