<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\TenantUser;
use Illuminate\Support\Facades\Hash;

class TenantSubdomainLoginTest extends TestCase
{
    public function test_tenant_is_identified_by_subdomain_on_localhost(): void
    {
        $response = $this->get('http://retrotech.localhost:8000/login');

        $response->assertStatus(200);
        $response->assertSee('Retrotech');
        $response->assertSee('Sign in to Workspace');
    }

    public function test_tenant_admin_can_login_on_subdomain(): void
    {
        $tenant = Tenant::on('mysql')->where('name', 'Retrotech')->first();
        $this->assertNotNull($tenant);
        $tenant->connect();

        // Ensure admin user password is known
        $admin = TenantUser::where('email', 'admin@retrotech.in')->first();
        if ($admin) {
            $admin->password = Hash::make('Retrotech123!');
            $admin->save();
        }

        $response = $this->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class])
            ->post('http://retrotech.localhost:8000/login', [
                'email'    => 'admin@retrotech.in',
                'password' => 'Retrotech123!',
            ]);

        $response->assertRedirect(route('dashboard'));
    }

    protected function tearDown(): void
    {
        Tenant::on('mysql')->first()?->connect();
        parent::tearDown();
    }
}
