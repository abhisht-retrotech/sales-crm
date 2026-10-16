<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\Role;
use App\Models\Profile;
use App\Models\TenantUser;
use App\Models\SharingRule;
use App\Models\RecordShare;

class SharingRulesTest extends TestCase
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

    public function test_sharing_rules_page_loads_with_studio_header_and_tabs(): void
    {
        $response = $this->get('/admin/sharing-rules');

        $response->assertStatus(200);
        $response->assertSee('Enterprise Sharing Rules');
        $response->assertSee('Record Security Studio');
        $response->assertSee('Phase 1 &amp; 3 Engine Active', false);
        $response->assertSee('New Sharing Rule');
        $response->assertSee('srRulesContainer');
        $response->assertSee('sharing-rules.js');
    }

    public function test_get_sharing_rules_meta_api(): void
    {
        $response = $this->getJson('/api/sharing-rules/meta');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'profiles',
                'roles',
                'users',
                'modules' => [
                    'opportunities',
                    'leads',
                    'accounts',
                    'payments',
                    'purchase_deals',
                    'shipments',
                ],
            ],
        ]);
    }

    public function test_get_sharing_rules_index_api(): void
    {
        $response = $this->getJson('/api/sharing-rules');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id',
                    'module',
                    'name',
                    'rule_type',
                    'criteria',
                    'access_level',
                    'is_active',
                    'shared_records_count',
                ],
            ],
        ]);
    }

    public function test_create_update_recalculate_delete_sharing_rule_lifecycle(): void
    {
        $profile = Profile::on('tenant')->first();
        $this->assertNotNull($profile);

        // 1. Create Rule
        $createPayload = [
            'name'            => 'Automated Test High-Value Opportunities',
            'description'     => 'Created during automated feature testing.',
            'module'          => 'opportunities',
            'rule_type'       => 'criteria_based',
            'criteria'        => [
                ['field' => 'amount', 'operator' => 'greater_or_equal', 'value' => '750000']
            ],
            'share_with_type' => 'profile',
            'share_with_id'   => (string)$profile->id,
            'access_level'    => 'read_write',
            'is_active'       => true,
        ];

        $createResponse = $this->postJson('/api/sharing-rules', $createPayload);
        $createResponse->assertStatus(200);
        $createResponse->assertJson(['success' => true]);

        $createdId = $createResponse->json('data.id');
        $this->assertNotEmpty($createdId);

        // Verify in DB
        $rule = SharingRule::on('tenant')->find($createdId);
        $this->assertNotNull($rule);
        $this->assertEquals('Automated Test High-Value Opportunities', $rule->name);
        $this->assertEquals('opportunities', $rule->module);
        $this->assertEquals('read_write', $rule->access_level);
        $this->assertTrue((bool)$rule->is_active);

        // 2. Update Rule
        $updatePayload = [
            'name'         => 'Automated Test High-Value Opportunities (Updated)',
            'access_level' => 'read',
            'is_active'    => false,
        ];

        $updateResponse = $this->putJson("/api/sharing-rules/{$createdId}", $updatePayload);
        $updateResponse->assertStatus(200);
        $updateResponse->assertJson(['success' => true]);

        $rule->refresh();
        $this->assertEquals('Automated Test High-Value Opportunities (Updated)', $rule->name);
        $this->assertEquals('read', $rule->access_level);
        $this->assertFalse((bool)$rule->is_active);

        // 3. Recalculate Rule
        $recalcResponse = $this->postJson("/api/sharing-rules/{$createdId}/recalculate");
        $recalcResponse->assertStatus(200);
        $recalcResponse->assertJson(['success' => true]);

        // 4. Delete Rule
        $deleteResponse = $this->deleteJson("/api/sharing-rules/{$createdId}");
        $deleteResponse->assertStatus(200);
        $deleteResponse->assertJson(['success' => true]);

        $this->assertNull(SharingRule::on('tenant')->find($createdId));
    }
}
