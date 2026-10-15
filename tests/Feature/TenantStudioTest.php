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
        $response->assertSee('Register &amp; Provision Tenant', false);
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
        ]);

        $response->assertRedirect(route('admin.tenant_studio'));
        $this->assertEquals('Updated Legal Name Inc.', $tenant->fresh()->legal_name);
        $this->assertEquals(50, $tenant->fresh()->max_seats);
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
        ]);

        $response->assertRedirect(route('admin.tenant_studio'));

        $newTenant = Tenant::on('mysql')->where('domain', $domain)->first();
        $this->assertNotNull($newTenant);
        $this->assertEquals('Stark Corp ' . $uniqueSlug, $newTenant->name);
        $this->assertEquals(25, $newTenant->max_seats);

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
}
