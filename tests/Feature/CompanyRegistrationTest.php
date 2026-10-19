<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\TenantUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CompanyRegistrationTest extends TestCase
{
    public function test_registration_page_renders_successfully(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee('Register Your Company Workspace');
        $response->assertSee('Company / Organization Name');
        $response->assertSee('Dedicated Workspace Domain / Slug');
        $response->assertSee('Auto-Fill', false);
        $response->assertSee('Retrotech', false);
        $response->assertSee(route('tenant.register.submit'));
    }

    public function test_company_registration_validation_fails_with_invalid_data(): void
    {
        $response = $this->from('/register')->post('/register', [
            'company_name'   => '',
            'domain'         => '',
            'admin_name'     => '',
            'admin_email'    => 'invalid-email',
            'admin_password' => '123',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors(['company_name', 'domain', 'admin_name', 'admin_email', 'admin_password']);
    }

    public function test_company_registration_creates_tenant_and_authenticates_admin(): void
    {
        $uniqueSlug = 'retrotech-test-' . uniqid();
        $uniqueEmail = 'admin@' . $uniqueSlug . '.com';

        $payload = [
            'company_name'                => 'Retrotech Solutions',
            'domain'                      => $uniqueSlug . '.local',
            'admin_name'                  => 'Retrotech Administrator',
            'admin_email'                 => $uniqueEmail,
            'admin_password'              => 'Password123!',
            'admin_password_confirmation' => 'Password123!',
            'plan_tier'                   => 'enterprise',
            'max_seats'                   => 30,
            'accent_color'                => '#059669',
        ];

        $response = $this->post('/register', $payload);

        $response->assertRedirect(route('dashboard'));

        // Verify tenant registered in central database
        $tenant = Tenant::on('mysql')->where('domain', $uniqueSlug . '.local')->first();
        $this->assertNotNull($tenant);
        $this->assertEquals('Retrotech Solutions', $tenant->name);
        $this->assertEquals(30, $tenant->max_seats);

        // Verify active authentication as the new tenant admin
        $this->assertTrue(Auth::guard('web')->check());
        $this->assertEquals($uniqueEmail, Auth::guard('web')->user()->email);

        // Clean up created test database
        if ($tenant && $tenant->db_name) {
            DB::connection('mysql')->statement("DROP DATABASE IF EXISTS `{$tenant->db_name}`");
            $tenant->delete();
        }
    }
}
