<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\SuperAdmin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class SuperAdminAuthTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        SuperAdmin::updateOrCreate(
            ['email' => 'admin@dealsync.com'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('admin123'),
            ]
        );
    }

    public function test_super_admin_login_page_renders_successfully(): void
    {
        $response = $this->get('/super-admin/login');

        $response->assertStatus(200);
        $response->assertSee('Super Admin Portal');
        $response->assertSee('admin@dealsync.com');
    }

    public function test_super_admin_login_fails_with_invalid_credentials(): void
    {
        $response = $this->from('/super-admin/login')->post('/super-admin/login', [
            'email' => 'admin@dealsync.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertRedirect('/super-admin/login');
        $response->assertSessionHasErrors('email');
        $this->assertFalse(Auth::guard('super')->check());
    }

    public function test_super_admin_login_succeeds_and_redirects_to_dashboard(): void
    {
        $response = $this->post('/super-admin/login', [
            'email' => 'admin@dealsync.com',
            'password' => 'admin123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertTrue(Auth::guard('super')->check());
        $this->assertEquals('admin@dealsync.com', Auth::guard('super')->user()->email);
    }

    public function test_super_admin_logout_clears_session(): void
    {
        $superAdmin = SuperAdmin::where('email', 'admin@dealsync.com')->first();
        Auth::guard('super')->login($superAdmin);
        $this->assertTrue(Auth::guard('super')->check());

        $response = $this->post('/super-admin/logout');

        $response->assertRedirect(route('super_admin.login'));
        $this->assertFalse(Auth::guard('super')->check());
    }
}
