<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\CompanyProfile;
use App\Models\TenantSubscription;
use App\Models\TenantInvoice;

class SecurityGovernanceTest extends TestCase
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

    public function test_security_governance_page_loads_successfully_with_policies_and_capacity_ribbon(): void
    {
        $response = $this->get('/admin/security-governance');

        $response->assertStatus(200);
        $response->assertSee('Security &amp; Access Governance Studio', false);
        $response->assertSee('Seat License Quotas &amp; Subscription Metering', false);
        $response->assertSee('Session Inactivity Auto-Timeout');
        $response->assertSee('Password Complexity &amp; Lifecycle Governance', false);
        $response->assertSee('Brute-Force Account Lockout Guard');
        $response->assertSee('Tenant-Wide Two-Factor Authentication (2FA) Policy');
        $response->assertSee('addSeatsModal');
        $response->assertSee('invoicesModal');
        $response->assertSee('superAdminSeatsInput');
        $response->assertSee('sessionTimeoutMinutes');
        $response->assertSee('passwordMinLength');
        $response->assertSee('maxFailedLoginAttempts');
    }

    public function test_get_tenant_security_policies_api_endpoint(): void
    {
        $tenantId = $this->tenant ? $this->tenant->id : 'default';
        $response = $this->getJson("/api/tenants/{$tenantId}/security-policies");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'plan_tier',
                'max_seats',
                'session_timeout_minutes',
                'password_min_length',
                'password_require_special',
                'password_require_numbers',
                'password_require_uppercase',
                'password_expiry_days',
                'max_failed_login_attempts',
                'lockout_duration_minutes',
                'two_factor_policy',
                'allow_self_password_change',
                'stats',
                'is_super_admin',
            ]
        ]);
        $response->assertJson([
            'success' => true,
        ]);
    }

    public function test_update_tenant_security_policies_via_web_save(): void
    {
        $response = $this->postJson('/admin/security-governance/save', [
            'session_timeout_minutes'    => 120,
            'allow_self_password_change' => false,
            'password_min_length'        => 12,
            'password_expiry_days'       => 90,
            'password_require_uppercase' => true,
            'password_require_numbers'   => true,
            'password_require_special'   => true,
            'max_failed_login_attempts'  => 3,
            'lockout_duration_minutes'   => 60,
            'two_factor_policy'          => 'admin_enforced',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Tenant security policies and seat allocations saved successfully!',
        ]);

        $profile = CompanyProfile::on('tenant')->first();
        $this->assertNotNull($profile);
        $this->assertEquals(120, $profile->session_timeout_minutes);
        $this->assertFalse((bool)$profile->allow_self_password_change);
        $this->assertEquals(12, $profile->password_min_length);
        $this->assertEquals(90, $profile->password_expiry_days);
        $this->assertEquals(3, $profile->max_failed_login_attempts);
        $this->assertEquals(60, $profile->lockout_duration_minutes);
        $this->assertEquals('admin_enforced', $profile->two_factor_policy);
    }

    public function test_super_admin_manual_quota_override(): void
    {
        $response = $this->postJson('/admin/security-governance/override-seats', [
            'max_seats' => 28,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $profile = CompanyProfile::on('tenant')->first();
        $this->assertEquals(28, $profile->max_seats);

        if ($this->tenant) {
            $this->assertEquals(28, $this->tenant->fresh()->max_seats);
        }
    }

    public function test_purchase_tenant_seats_creates_invoice_and_updates_capacity(): void
    {
        $initialSub = TenantSubscription::on('tenant')->first();
        $initialSeats = $initialSub ? $initialSub->seat_count : 15;

        $response = $this->postJson('/admin/security-governance/add-seats', [
            'additional_seats'     => 4,
            'payment_method_brand' => 'Visa',
            'payment_method_last4' => '8888',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'new_seat_count'   => $initialSeats + 4,
                'additional_seats' => 4,
                'charge_amount'    => 156.00, // 4 * 39
            ]
        ]);

        // Verify invoice was stored in tenant database
        $latestInvoice = TenantInvoice::on('tenant')->latest('id')->first();
        $this->assertNotNull($latestInvoice);
        $this->assertEquals(156.00, $latestInvoice->amount);
        $this->assertEquals(4, $latestInvoice->seats_purchased);
        $this->assertEquals('paid', $latestInvoice->status);
        $this->assertEquals('8888', $latestInvoice->payment_method_last4);
    }

    public function test_get_tenant_invoices_history_endpoint(): void
    {
        $response = $this->getJson('/admin/security-governance/invoices');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id',
                    'invoice_number',
                    'amount',
                    'seats_purchased',
                    'status',
                    'created_at',
                ]
            ]
        ]);
        $response->assertJson(['success' => true]);
    }

    public function test_react_parity_subscription_api_endpoint(): void
    {
        $tenantId = $this->tenant ? $this->tenant->id : 'default';
        $response = $this->getJson("/api/tenants/{$tenantId}/subscription");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'subscription',
                'usage',
                'pricing_tiers' => [
                    'starter',
                    'professional',
                    'enterprise',
                ],
                'is_super_admin',
            ]
        ]);
    }

    public function test_react_parity_subscription_add_seats_api(): void
    {
        $tenantId = $this->tenant ? $this->tenant->id : 'default';
        $response = $this->postJson("/api/tenants/{$tenantId}/subscription/add-seats", [
            'additional_seats'     => 2,
            'payment_method_brand' => 'Mastercard',
            'payment_method_last4' => '9999',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'additional_seats' => 2,
                'charge_amount'    => 78.00,
            ]
        ]);
    }
}
