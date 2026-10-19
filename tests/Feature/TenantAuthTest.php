<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\TenantUser;
use App\Models\SuperAdmin;
use App\Models\Role;
use App\Models\Profile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class TenantAuthTest extends TestCase
{
    protected ?Tenant $tenant = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::on('mysql')->first();
        if ($this->tenant) {
            $this->tenant->connect();

            // Ensure an active admin role and profile exist
            $adminRole = Role::firstOrCreate(
                ['name' => 'System Administrator'],
                ['description' => 'Full administrative access']
            );
            $adminProfile = Profile::firstOrCreate(
                ['name' => 'System Administrator'],
                ['description' => 'System administrator permissions profile']
            );

            // Ensure Tenant Admin user exists
            TenantUser::updateOrCreate(
                ['email' => 'admin@dealsync.com'],
                [
                    'name'                => 'System Administrator',
                    'password'            => Hash::make('admin123'),
                    'role_id'             => $adminRole->id,
                    'profile_id'          => $adminProfile->id,
                    'status'              => 'Active',
                    'surprise_me_enabled' => true,
                ]
            );
        }

        // Also ensure Super Admin exists in central db
        SuperAdmin::updateOrCreate(
            ['email' => 'superadmin@dealsync.com'],
            [
                'name'     => 'Central Super Admin',
                'password' => Hash::make('superadmin123'),
            ]
        );
    }

    public function test_tenant_login_page_renders_successfully(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Sign in to Workspace');
        $response->assertSee('Work Email Address');
        $response->assertSee('Auto-Fill Tenant Admin Demo');
        $response->assertSee(route('tenant.login.submit'));
    }

    public function test_tenant_login_fails_with_invalid_credentials(): void
    {
        $response = $this->from('/login')->post('/login', [
            'email'    => 'admin@dealsync.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertFalse(Auth::guard('web')->check());
    }

    public function test_tenant_admin_login_succeeds_and_redirects_to_dashboard(): void
    {
        $response = $this->post('/login', [
            'email'    => 'admin@dealsync.com',
            'password' => 'admin123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertTrue(Auth::guard('web')->check());
        $this->assertEquals('admin@dealsync.com', Auth::guard('web')->user()->email);
    }

    public function test_tenant_user_logout_clears_session(): void
    {
        $user = TenantUser::where('email', 'admin@dealsync.com')->first();
        if ($user) {
            Auth::guard('web')->login($user);
            $this->assertTrue(Auth::guard('web')->check());

            $response = $this->post('/logout');

            $response->assertRedirect(route('tenant.login'));
            $this->assertFalse(Auth::guard('web')->check());
        }
    }

    public function test_super_admin_can_also_authenticate_and_access(): void
    {
        $super = SuperAdmin::where('email', 'superadmin@dealsync.com')->first();
        Auth::guard('super')->login($super);
        $this->assertTrue(Auth::guard('super')->check());

        // Super Admin accessing central tenants API
        $response = $this->get('/central/tenants');
        $response->assertStatus(200);
    }

    public function test_unauthenticated_request_to_central_api_is_blocked(): void
    {
        Auth::guard('super')->logout();

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'X-Enforce-Super-Auth' => 'true',
        ])->get('/central/tenants');

        $this->assertContains($response->status(), [401, 302]);
    }
}
