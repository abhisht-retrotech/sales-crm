<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\Profile;
use App\Models\DashboardLayout;

class DashboardStudioTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $tenant = Tenant::on('mysql')->first();
        if ($tenant) {
            $tenant->connect();
        }
    }

    public function test_dashboard_studio_page_loads_with_profiles(): void
    {
        $response = $this->get('/admin/dashboard-studio');

        $response->assertStatus(200);
        $response->assertSee('Dashboard Studio &amp; Layout Designer', false);
        $response->assertSee('16-Column Canvas');
        $response->assertSee('dashboard-grid', false);
    }

    public function test_get_dashboard_layouts_api(): void
    {
        $response = $this->getJson('/api/analytics/dashboard-layouts');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'layouts',
            'sections',
            'records',
        ]);
    }

    public function test_save_dashboard_layout_persists_to_tenant_database(): void
    {
        $payload = [
            'profile_id' => 'prof-admin',
            'widgets' => [
                [
                    'id' => 'widget-arr-test',
                    'type' => 'kpi',
                    'title' => 'Annual Recurring Revenue (ARR)',
                    'colSpan' => 4,
                    'height' => 'compact',
                    'palette' => 'violet',
                    'sectionId' => 'sec-revenue',
                ],
                [
                    'id' => 'widget-trajectory-test',
                    'type' => 'area',
                    'title' => 'ARR Multi-Trajectory Forecast',
                    'colSpan' => 10,
                    'height' => 'standard',
                    'palette' => 'cyan',
                    'sectionId' => 'sec-pipeline',
                ],
            ],
            'sections' => [
                [
                    'id' => 'sec-revenue',
                    'title' => 'Executive Revenue & Quota Attainment',
                    'subtitle' => 'Primary revenue run-rate and targets',
                    'collapsed' => false,
                ],
                [
                    'id' => 'sec-pipeline',
                    'title' => 'Pipeline Health & Telemetry',
                    'subtitle' => 'Active opportunities and velocity',
                    'collapsed' => false,
                ],
            ],
        ];

        $response = $this->postJson('/api/analytics/dashboard-layouts', $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'profile_id' => 'prof-admin',
        ]);

        // Verify layout exists in tenant database
        $adminProfile = Profile::on('tenant')->where('name', 'like', '%Admin%')->first();
        $this->assertNotNull($adminProfile);

        $layout = DashboardLayout::on('tenant')->where('profile_id', $adminProfile->id)->first();
        $this->assertNotNull($layout);
        $this->assertCount(2, $layout->widgets);
        $this->assertEquals('Annual Recurring Revenue (ARR)', $layout->widgets[0]['title']);
    }

    public function test_get_single_profile_dashboard_layout(): void
    {
        // First save layout
        $payload = [
            'profile_id' => 'prof-sales',
            'widgets' => [
                [
                    'id' => 'widget-rep-quota',
                    'type' => 'gauge',
                    'title' => 'My Quota Attainment',
                    'colSpan' => 4,
                ]
            ],
            'sections' => [
                [
                    'id' => 'sec-rep',
                    'title' => 'Sales Representative Sprint',
                ]
            ]
        ];

        $this->postJson('/api/analytics/dashboard-layouts', $payload)->assertStatus(200);

        // Fetch single
        $response = $this->getJson('/api/analytics/dashboard-layouts/prof-sales');
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'profile_id' => 'prof-sales',
        ]);
    }

    public function test_reset_dashboard_layout(): void
    {
        $response = $this->deleteJson('/api/analytics/dashboard-layouts/prof-sales');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $salesProfile = Profile::on('tenant')->where('name', 'like', '%Sales%')->orWhere('name', 'like', '%Standard%')->first();
        if ($salesProfile) {
            $this->assertNull(DashboardLayout::on('tenant')->where('profile_id', $salesProfile->id)->first());
        }
    }

    public function test_get_settings_profiles_api(): void
    {
        $response = $this->getJson('/api/settings/profiles');

        $response->assertStatus(200);
        $this->assertIsArray($response->json());
        $this->assertNotEmpty($response->json());
    }
}
