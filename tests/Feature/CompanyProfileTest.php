<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\CompanyProfile;
use App\Models\TenantCurrency;

class CompanyProfileTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $tenant = Tenant::on('mysql')->first();
        if ($tenant) {
            $tenant->connect();
        }
    }

    public function test_company_profile_page_loads_with_tenant_data(): void
    {
        $response = $this->get('/admin/company-profile');

        $response->assertStatus(200);
        $response->assertSee('Company Profile &amp; Localization', false);
        $response->assertSee('Company Profile &amp; Legal', false);
        $response->assertSee('Regional &amp; Localization', false);
        $response->assertSee('Multi-Currency &amp; Exchange Rates', false);
        $response->assertSee('Email &amp; Mailbox Sync Gateway', false);
        $response->assertSee('header-tenant-id-badge');
        $response->assertSee('header-base-currency-badge');
    }

    public function test_update_company_profile_persists_to_database(): void
    {
        $payload = [
            'name'            => 'DealSync Global Corp',
            'legal_name'      => 'DealSync Worldwide Operations Inc.',
            'tax_id'          => 'US-EIN-112233445',
            'phone'           => '+1 (800) 555-0199',
            'address_street'  => '500 Silicon Vista Blvd',
            'address_city'    => 'San Francisco',
            'address_state'   => 'CA',
            'address_country' => 'US',
            'branding_theme'  => 'apple',
            'accent_color'    => '#10B981',
            'time_zone'       => 'America/Los_Angeles',
            'date_format'     => 'YYYY-MM-DD',
            'time_format'     => '24h',
        ];

        $response = $this->postJson('/admin/company-profile/save', $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $profile = CompanyProfile::on('tenant')->first();
        $this->assertNotNull($profile);
        $this->assertEquals('DealSync Global Corp', $profile->name);
        $this->assertEquals('DealSync Worldwide Operations Inc.', $profile->legal_name);
        $this->assertEquals('US-EIN-112233445', $profile->tax_id);
        $this->assertEquals('+1 (800) 555-0199', $profile->phone);
        $this->assertEquals('500 Silicon Vista Blvd', $profile->address_street);
        $this->assertEquals('#10B981', $profile->accent_color);
        $this->assertEquals('America/Los_Angeles', $profile->time_zone);
        $this->assertEquals('24h', $profile->time_format);
    }

    public function test_currencies_endpoints_support_crud_and_rebasing(): void
    {
        // 1. Get initial currencies
        $response = $this->getJson('/admin/company-profile/currencies');
        $response->assertStatus(200);
        $this->assertIsArray($response->json());

        // 2. Add new test currency: SGD
        $addResponse = $this->postJson('/admin/company-profile/currencies', [
            'currency_code'   => 'SGD',
            'currency_symbol' => 'S$',
            'currency_name'   => 'Singapore Dollar',
            'exchange_rate'   => 1.35,
            'is_active'       => true,
        ]);

        $addResponse->assertStatus(201);
        $addResponse->assertJson(['success' => true]);

        $sgd = TenantCurrency::on('tenant')->where('currency_code', 'SGD')->first();
        $this->assertNotNull($sgd);
        $this->assertEquals('S$', $sgd->currency_symbol);

        // 3. Update SGD exchange rate
        $updateResponse = $this->putJson("/admin/company-profile/currencies/{$sgd->id}", [
            'exchange_rate' => 1.38,
            'is_active'     => true,
        ]);

        $updateResponse->assertStatus(200);
        $updateResponse->assertJson(['success' => true]);
        $sgd->refresh();
        $this->assertEquals('1.380000', $sgd->exchange_rate);

        // 4. Set EUR as Base Currency
        $rebaseResponse = $this->postJson('/admin/company-profile/currencies/set-base', [
            'currency_code' => 'EUR',
        ]);

        $rebaseResponse->assertStatus(200);
        $rebaseResponse->assertJson(['success' => true]);

        $eur = TenantCurrency::on('tenant')->where('currency_code', 'EUR')->first();
        $this->assertTrue((bool)$eur->is_base);
        $this->assertEquals('1.000000', $eur->exchange_rate);

        // Restore USD as Base Currency
        $restoreResponse = $this->postJson('/admin/company-profile/currencies/set-base', [
            'currency_code' => 'USD',
        ]);
        $restoreResponse->assertStatus(200);
        $usd = TenantCurrency::on('tenant')->where('currency_code', 'USD')->first();
        $this->assertTrue((bool)$usd->is_base);

        // 5. Delete SGD test currency
        $deleteResponse = $this->deleteJson("/admin/company-profile/currencies/{$sgd->id}");
        $deleteResponse->assertStatus(200);
        $deleteResponse->assertJson(['success' => true]);

        $this->assertNull(TenantCurrency::on('tenant')->where('currency_code', 'SGD')->first());
    }

    public function test_email_settings_endpoints_save_and_test_handshake(): void
    {
        // 1. Get email settings
        $getResponse = $this->getJson('/admin/company-profile/email-settings');
        $getResponse->assertStatus(200);
        $getResponse->assertJsonStructure([
            'success',
            'data' => [
                'email_provider',
                'smtp_host',
                'smtp_port',
                'smtp_user',
                'smtp_secure',
                'smtp_status',
                'email_from_name',
                'email_from_address',
            ]
        ]);

        // 2. Update email settings
        $updateResponse = $this->putJson('/admin/company-profile/email-settings', [
            'email_provider'     => 'sendgrid',
            'smtp_host'          => 'smtp.sendgrid.net',
            'smtp_port'          => 587,
            'smtp_user'          => 'apikey_updated',
            'smtp_pass'          => 'SG.secret_key_123',
            'smtp_secure'        => false,
            'email_from_name'    => 'Enterprise Deal Notifications',
            'email_from_address' => 'deals@dealsync.com',
            'enforce_legal_footer'=> true,
        ]);

        $updateResponse->assertStatus(200);
        $updateResponse->assertJson(['success' => true]);

        $profile = CompanyProfile::on('tenant')->first();
        $this->assertEquals('smtp.sendgrid.net', $profile->smtp_host);
        $this->assertEquals('apikey_updated', $profile->smtp_user);
        $this->assertEquals('Enterprise Deal Notifications', $profile->email_from_name);

        // 3. Test verification email handshake
        $testResponse = $this->postJson('/admin/company-profile/email-settings/test', [
            'recipient_email' => 'compliance@dealsync.com',
            'smtp_host'       => 'smtp.sendgrid.net',
            'smtp_port'       => 587,
            'smtp_user'       => 'apikey_updated',
        ]);

        $testResponse->assertStatus(200);
        $testResponse->assertJson([
            'success'     => true,
            'smtp_status' => 'verified',
        ]);
        $this->assertArrayHasKey('latencyMs', $testResponse->json());
        $this->assertArrayHasKey('messageId', $testResponse->json());
    }

    public function test_react_tenant_api_endpoints_parity(): void
    {
        $tenant = Tenant::on('mysql')->first();
        $tenantId = $tenant->id;

        // 1. GET /api/tenants/{tenant}
        $showResponse = $this->getJson("/api/tenants/{$tenantId}");
        $showResponse->assertStatus(200);
        $showResponse->assertJsonStructure([
            'id',
            'name',
            'currency_code',
            'currency_symbol',
            'time_zone',
            'email_provider',
            'smtp_host',
        ]);

        // 2. PUT /api/tenants/{tenant}
        $updateResponse = $this->putJson("/api/tenants/{$tenantId}", [
            'industry' => 'Financial Technology & Cloud',
            'website'  => 'https://fintech.example.com',
        ]);
        $updateResponse->assertStatus(200);
        $updateResponse->assertJson(['success' => true]);

        // 3. GET /api/tenants/{tenant}/currencies
        $currsResponse = $this->getJson("/api/tenants/{$tenantId}/currencies");
        $currsResponse->assertStatus(200);
        $this->assertIsArray($currsResponse->json());

        // 4. GET /api/tenants/{tenant}/email-settings
        $emailResponse = $this->getJson("/api/tenants/{$tenantId}/email-settings");
        $emailResponse->assertStatus(200);
        $emailResponse->assertJson(['success' => true]);
    }
}
