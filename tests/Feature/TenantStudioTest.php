<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant;

class TenantStudioTest extends TestCase
{
    public function test_tenant_studio_page_loads(): void
    {
        $response = $this->get('/admin/tenant-studio');

        $response->assertStatus(200);
        $response->assertSee('Tenant Management Studio');
        $response->assertSee('Master Tenant Only');
        $response->assertSee('Register New Tenant');
        $response->assertSee('Edit Branding');
        $response->assertSee('create-tenant-modal');
        $response->assertSee('edit-tenant-modal');
        $response->assertSee('tenant-studio.js');
        $response->assertSee('TENANT_STUDIO_CONFIG');
    }

    public function test_tenant_studio_javascript_assets_exist(): void
    {
        $primaryJs = public_path('js/tenant-studio.js');
        $aliasJs = public_path('js/tenant_studio.js');

        $this->assertFileExists($primaryJs);
        $this->assertFileExists($aliasJs);

        $primaryContent = file_get_contents($primaryJs);
        $this->assertStringContainsString('initTenantStudio', $primaryContent);
        $this->assertStringContainsString('filterTenants', $primaryContent);
        $this->assertStringContainsString('openCreateTenantModal', $primaryContent);
        $this->assertStringContainsString('openEditTenantModal', $primaryContent);
        $this->assertStringContainsString('setLogoPreview', $primaryContent);
        $this->assertStringContainsString('removeLogo', $primaryContent);
    }

    public function test_update_tenant(): void
    {
        $tenant = Tenant::on('mysql')->first();
        $this->assertNotNull($tenant);

        $response = $this->put("/admin/tenant-studio/{$tenant->id}", [
            'name'       => $tenant->name,
            'domain'     => $tenant->domain,
            'legal_name' => 'Updated Legal Name Inc.',
            'plan_tier'  => 'enterprise',
            'max_seats'  => 50,
            'is_active'  => true,
            'logo_url'   => 'https://example.com/branding-logo.png',
        ]);

        $response->assertRedirect(route('admin.tenant_studio'));
        $this->assertEquals('Updated Legal Name Inc.', $tenant->fresh()->legal_name);
        $this->assertEquals(50, $tenant->fresh()->max_seats);
        $this->assertEquals('https://example.com/branding-logo.png', $tenant->fresh()->logo_url);
    }

    public function test_provision_new_tenant(): void
    {
        $uniqueSlug = 'test' . rand(100, 999);
        $domain = $uniqueSlug . '.local';

        $response = $this->post('/admin/tenant-studio/provision', [
            'name'           => 'Stark Corp ' . $uniqueSlug,
            'domain'         => $domain,
            'legal_name'     => 'Stark Industries Inc.',
            'admin_name'     => 'Tony Stark',
            'admin_email'    => 'tony@' . $domain,
            'admin_password' => 'Jarvis@2026',
            'plan_tier'      => 'enterprise',
            'max_seats'      => 25,
            'branding_theme' => 'apple',
            'accent_color'   => '#dc2626',
            'logo_url'       => 'https://example.com/stark-logo.png',
        ]);

        $response->assertRedirect(route('admin.tenant_studio'));

        $newTenant = Tenant::on('mysql')->where('domain', $domain)->first();
        $this->assertNotNull($newTenant);
        $this->assertEquals('Stark Corp ' . $uniqueSlug, $newTenant->name);
        $this->assertEquals(25, $newTenant->max_seats);
        $this->assertEquals('https://example.com/stark-logo.png', $newTenant->logo_url);

        // Clean up created database and tenant after test
        \Illuminate\Support\Facades\DB::connection('mysql')->statement("DROP DATABASE IF EXISTS `{$newTenant->db_name}`");
        $newTenant->delete();
    }

    public function test_switch_workspace(): void
    {
        $tenant = Tenant::on('mysql')->first();
        $this->assertNotNull($tenant);

        $response = $this->post("/admin/tenant-studio/{$tenant->id}/switch");

        $response->assertRedirect(route('dashboard'));
        $this->assertEquals($tenant->id, session('current_tenant_id'));
    }

    public function test_company_profile_appearance_controls_tenant_studio(): void
    {
        $tenant = Tenant::on('mysql')->first();
        $this->assertNotNull($tenant);
        $tenant->connect();

        $profile = \App\Models\CompanyProfile::on('tenant')->first();
        if (!$profile) {
            $profile = \App\Models\CompanyProfile::on('tenant')->create(['name' => $tenant->name]);
        }

        $wallpaperUrl = 'https://images.unsplash.com/photo-custom-wallpaper?w=1200';
        $profile->update([
            'page_bg_color'       => '#1a2332',
            'font_color'          => '#f8fafc',
            'table_bg_color'      => '#243044',
            'table_border_color'  => '#3b4d66',
            'appearance_settings' => [
                'page' => [
                    'mode' => 'image',
                    'imageUrl' => $wallpaperUrl,
                    'fit' => 'cover',
                    'opacity' => 85,
                    'brightness' => 90,
                    'contrast' => 100,
                    'softness' => 0,
                ],
                'table' => [
                    'mode' => 'image',
                    'imageUrl' => $wallpaperUrl,
                    'fit' => 'cover',
                    'opacity' => 90,
                    'brightness' => 100,
                    'contrast' => 100,
                    'softness' => 0,
                ]
            ],
        ]);

        $response = $this->withSession(['current_tenant_id' => $tenant->id])
                         ->get('/admin/tenant-studio');

        $response->assertStatus(200);

        // Verify CSS Variables are injected
        $response->assertSee('--bg-main: #1a2332', false);
        $response->assertSee('--text-primary: #f8fafc', false);
        $response->assertSee('--table-bg: #243044', false);
        $response->assertSee('--table-border: #3b4d66', false);

        // Verify Server-rendered Page Wallpaper image
        $response->assertSee($wallpaperUrl, false);
        $response->assertSee('card-table-wrapper');

        // Verify dealSyncConfig is populated
        $response->assertSee('window.__dealSyncConfig');
    }
}
