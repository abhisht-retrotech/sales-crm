<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\CustomButton;
use Illuminate\Support\Facades\DB;

class CustomButtonsTest extends TestCase
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

    public function test_custom_buttons_view_loads_with_studio_markup_and_tailwind(): void
    {
        $response = $this->get('/admin/custom-buttons');

        $response->assertStatus(200);
        $response->assertSee('Custom Buttons &amp; Actions Studio', false);
        $response->assertSee('cbStudioRoot');
        $response->assertSee('cbServerData');
        $response->assertSee('custom-buttons.js');
        $response->assertSee('max-w-[1600px]');
        $response->assertSee('List View');
        $response->assertSee('Cards');
    }

    public function test_get_custom_buttons_api_returns_seeded_defaults(): void
    {
        $response = $this->getJson('/api/custom-buttons');

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);

        // Verify seeded default buttons exist
        $labels = array_column($data, 'label');
        $this->assertContains('Send Welcome Kit', $labels);

        // Filter by entity_type=leads
        $leadsRes = $this->getJson('/api/custom-buttons?entity_type=leads');
        $leadsRes->assertStatus(200);
        $leadsData = $leadsRes->json();
        foreach ($leadsData as $btn) {
            $this->assertEquals('leads', $btn['entity_type']);
        }
    }

    public function test_custom_button_crud_lifecycle(): void
    {
        // 1. Create a custom button
        $createRes = $this->postJson('/api/custom-buttons', [
            'entity_type'   => 'opportunities',
            'label'         => 'Feature Test Proposal',
            'icon'          => 'Send',
            'style_variant' => 'purple',
            'action_type'   => 'send_email',
            'action_config' => [
                'execution_mode'          => 'composer',
                'default_recipient_field' => 'email',
            ],
            'placement'     => 'record_header',
            'visibility_conditions' => [
                ['field' => 'stage', 'operator' => 'equals', 'value' => 'Proposal']
            ],
            'is_active'     => true,
            'display_order' => 10,
        ]);

        $createRes->assertStatus(201);
        $createdData = $createRes->json('data');
        $buttonId = $createdData['id'];
        $this->assertNotNull($buttonId);
        $this->assertEquals('Feature Test Proposal', $createdData['label']);
        $this->assertEquals('feature_test_proposal', $createdData['name']);

        // 2. Fetch single button
        $getRes = $this->getJson("/api/custom-buttons/{$buttonId}");
        $getRes->assertStatus(200);
        $this->assertEquals('Feature Test Proposal', $getRes->json('label'));
        $this->assertEquals('purple', $getRes->json('style_variant'));

        // 3. Update the custom button
        $updateRes = $this->putJson("/api/custom-buttons/{$buttonId}", [
            'label'         => 'Updated Test Proposal',
            'style_variant' => 'success',
            'is_active'     => false,
        ]);

        $updateRes->assertStatus(200);
        $updatedData = $updateRes->json('data');
        $this->assertEquals('Updated Test Proposal', $updatedData['label']);
        $this->assertEquals('success', $updatedData['style_variant']);
        $this->assertFalse((bool)$updatedData['is_active']);

        // 4. Delete the custom button
        $deleteRes = $this->deleteJson("/api/custom-buttons/{$buttonId}");
        $deleteRes->assertStatus(200);
        $this->assertTrue($deleteRes->json('success'));

        // Confirm deletion
        $checkDeleted = CustomButton::on('tenant')->find($buttonId);
        $this->assertNull($checkDeleted);
    }

    public function test_custom_button_workflow_execution(): void
    {
        // 1. Create a button configured for workflow field updates
        $btn = CustomButton::on('tenant')->create([
            'entity_type'   => 'leads',
            'name'          => 'qualify_test_btn',
            'label'         => 'Quick Qualify',
            'icon'          => 'CheckCircle',
            'style_variant' => 'success',
            'action_type'   => 'workflow_trigger',
            'action_config' => [
                'trigger_type'    => 'field_update',
                'field_updates'   => ['status' => 'Qualified'],
                'success_message' => 'Lead successfully marked as Qualified.',
            ],
            'placement'     => 'record_header',
            'is_active'     => true,
            'display_order' => 1,
        ]);

        // 2. Execute the button
        $execRes = $this->postJson("/api/custom-buttons/{$btn->id}/execute", [
            'recordId' => null, // Dry run execution check
        ]);

        $execRes->assertStatus(200);
        $this->assertTrue($execRes->json('success'));
        $this->assertEquals('Lead successfully marked as Qualified.', $execRes->json('message'));

        // Cleanup
        $btn->delete();
    }

    public function test_spa_navigation_contains_custom_buttons_hook(): void
    {
        $spaFile = file_get_contents(public_path('js/spa-navigation.js'));

        $this->assertStringContainsString('initCustomButtonsStudio', $spaFile);
        $this->assertStringContainsString('cbStudioRoot', $spaFile);
        $this->assertStringContainsString('/js/custom-buttons.js?v=2.0', $spaFile);
    }
}
