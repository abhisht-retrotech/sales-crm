<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\PageLayout;
use App\Models\FieldHistoryConfig;
use Illuminate\Support\Facades\DB;

class PageLayoutsTest extends TestCase
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

    public function test_page_layouts_view_loads_with_studio_markup_and_tailwind(): void
    {
        $response = $this->get('/admin/page-layouts');

        $response->assertStatus(200);
        $response->assertSee('Page Layouts Designer');
        $response->assertSee('plStudioRoot');
        $response->assertSee('plServerData');
        $response->assertSee('page-layouts.js');
        $response->assertSee('max-w-[1600px]');
    }

    public function test_get_page_layouts_api_returns_seeded_data(): void
    {
        $response = $this->getJson('/api/page-layouts/leads');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'layouts' => [
                '*' => [
                    'id',
                    'module',
                    'name',
                    'description',
                    'is_default',
                    'layout'
                ]
            ],
            'assignments',
            'assignmentDetails'
        ]);

        $data = $response->json();
        $this->assertNotEmpty($data['layouts']);
        $this->assertEquals('leads', $data['layouts'][0]['module']);
    }

    public function test_page_layout_crud_and_assignment_lifecycle(): void
    {
        // 1. Create a layout
        $createRes = $this->postJson('/api/page-layouts/leads', [
            'name' => 'Automated Test Layout',
            'description' => 'Created during feature test',
            'layout' => [
                'sections' => [
                    [
                        'id' => 'sec_test_1',
                        'title' => 'Test Section One',
                        'columns' => 2,
                        'isCollapsed' => false,
                        'fields' => [
                            ['field' => 'first_name', 'required' => true, 'readOnly' => false],
                            ['field' => 'last_name', 'required' => true, 'readOnly' => false]
                        ]
                    ]
                ],
                'highlights' => ['first_name', 'company'],
                'quickActions' => ['log_call', 'send_email']
            ]
        ]);

        $createRes->assertStatus(201);
        $newLayoutId = $createRes->json('id');
        $this->assertNotNull($newLayoutId);
        $this->assertEquals('Automated Test Layout', $createRes->json('name'));

        // 2. Fetch single layout
        $getRes = $this->getJson("/api/page-layouts/leads/{$newLayoutId}");
        $getRes->assertStatus(200);
        $this->assertEquals('Automated Test Layout', $getRes->json('name'));
        $this->assertCount(1, $getRes->json('layout.sections'));

        // 3. Update the layout
        $updateRes = $this->putJson("/api/page-layouts/leads/{$newLayoutId}", [
            'name' => 'Updated Test Layout',
            'description' => 'Updated description',
            'layout' => [
                'sections' => [
                    [
                        'id' => 'sec_test_1',
                        'title' => 'Renamed Section',
                        'columns' => 1,
                        'isCollapsed' => true,
                        'fields' => [
                            ['field' => 'email', 'required' => true, 'readOnly' => false]
                        ]
                    ]
                ],
                'highlights' => ['email'],
                'quickActions' => ['send_email']
            ]
        ]);

        $updateRes->assertStatus(200);
        $this->assertEquals('Updated Test Layout', $updateRes->json('name'));
        $this->assertEquals('Renamed Section', $updateRes->json('layout.sections.0.title'));

        // 4. Set as default layout
        $defaultRes = $this->postJson("/api/page-layouts/leads/{$newLayoutId}/set-default");
        $defaultRes->assertStatus(200);
        $this->assertTrue((bool)$defaultRes->json('is_default'));

        // 5. Save profile assignments
        $profileId = DB::connection('tenant')->table('profiles')->value('id') ?? 1;
        $assignRes = $this->postJson('/api/page-layouts/leads/assignments', [
            'assignments' => [
                (string)$profileId => (int)$newLayoutId
            ]
        ]);
        $assignRes->assertStatus(200);
        $this->assertTrue($assignRes->json('success'));

        // 6. Delete layout (must unset default first or another default must exist)
        $anotherDefault = PageLayout::on('tenant')
            ->where('module', 'leads')
            ->where('id', '!=', $newLayoutId)
            ->first();
        if ($anotherDefault) {
            $this->postJson("/api/page-layouts/leads/{$anotherDefault->id}/set-default");
        }

        $deleteRes = $this->deleteJson("/api/page-layouts/leads/{$newLayoutId}");
        $deleteRes->assertStatus(200);
        $this->assertTrue($deleteRes->json('success'));
    }

    public function test_field_history_config_api(): void
    {
        $saveRes = $this->postJson('/api/field-history/config/leads', [
            'tracked_fields' => ['first_name', 'status', 'email'],
            'enabled' => true
        ]);

        $saveRes->assertStatus(200);
        $this->assertTrue($saveRes->json('enabled'));
        $this->assertContains('status', $saveRes->json('tracked_fields'));

        $getRes = $this->getJson('/api/field-history/config/leads');
        $getRes->assertStatus(200);
        $this->assertTrue($getRes->json('enabled'));
        $this->assertContains('status', $getRes->json('tracked_fields'));
    }

    public function test_spa_navigation_integration(): void
    {
        $spaContent = file_get_contents(public_path('js/spa-navigation.js'));
        $this->assertStringContainsString('initPageLayouts', $spaContent);
        $this->assertStringContainsString('plStudioRoot', $spaContent);
        $this->assertStringContainsString('/js/page-layouts.js?v=2.0', $spaContent);
    }
}
