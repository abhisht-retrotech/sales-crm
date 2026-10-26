<?php

namespace App\Services\Admin;

use App\Models\Tenant;
use App\Models\CompanyProfile;
use Illuminate\Support\Facades\DB;

trait AdminTenantTrait
{
    public function ensureTenantConnection(?Tenant $tenant)
    {
        if ($tenant && config('database.connections.tenant.database') !== $tenant->db_name) {
            config(['database.connections.tenant.database' => $tenant->db_name]);
            DB::purge('tenant');
        }
    }

    public function resolveTenantInstance($tenantId = null): ?Tenant
    {
        $tenant = null;
        if ($tenantId) {
            $tenant = is_numeric($tenantId)
                ? Tenant::on('mysql')->find($tenantId)
                : Tenant::on('mysql')->where('domain', $tenantId)->orWhere('id', $tenantId)->first();
        }
        if (!$tenant && app()->bound('currentTenant')) {
            $tenant = app('currentTenant');
        }
        if (!$tenant) {
            $tenant = Tenant::on('mysql')->first();
        }
        $this->ensureTenantConnection($tenant);
        return $tenant;
    }

    public function getOrCreateCompanyProfile(?Tenant $tenant)
    {
        $this->ensureTenantConnection($tenant);
        $profile = CompanyProfile::on('tenant')->first();
        if (!$profile) {
            $tName = $tenant ? $tenant->name : 'DealSync Central Demo';
            $tLegal = ($tenant && $tenant->legal_name) ? $tenant->legal_name : $tName . ' Global Inc.';
            $theme = ($tenant && $tenant->branding_theme) ? $tenant->branding_theme : 'apple';
            $accent = ($tenant && $tenant->accent_color) ? $tenant->accent_color : '#4F46E5';

            $profile = CompanyProfile::on('tenant')->create([
                'name'                       => $tName,
                'legal_name'                 => $tLegal,
                'tax_id'                     => 'US-EIN-987654321',
                'registration_number'        => 'CR-09871234',
                'industry'                   => 'Enterprise Software & Cloud',
                'website'                    => 'https://dealsync.example.com',
                'phone'                      => '+1 (555) 019-2834',
                'billing_email'              => 'billing@dealsync.example.com',
                'support_email'              => 'support@dealsync.example.com',
                'address_street'             => '100 Technology Park, Suite 400',
                'address_city'               => 'San Francisco',
                'address_state'              => 'CA',
                'address_postal_code'        => '94107',
                'address_country'            => 'US',
                'currency_code'              => 'USD',
                'currency_symbol'            => '$',
                'currency_position'          => 'prefix',
                'time_zone'                  => 'America/New_York',
                'date_format'                => 'YYYY-MM-DD',
                'time_format'                => '12h',
                'number_format'              => 'comma_dot',
                'fiscal_year_start_month'    => 1,
                'working_days'               => 'Mon,Tue,Wed,Thu,Fri',
                'working_hours_start'        => '09:00',
                'working_hours_end'          => '18:00',
                'allow_self_password_change' => true,
                'email_provider'             => 'sendgrid',
                'smtp_host'                  => 'smtp.sendgrid.net',
                'smtp_port'                  => 587,
                'smtp_user'                  => 'apikey',
                'smtp_pass'                  => 'SG.sample_key_987216',
                'smtp_secure'                => false,
                'smtp_status'                => 'verified',
                'smtp_last_tested_at'        => now(),
                'email_from_name'            => $tName . ' Sales',
                'email_from_address'         => 'notifications@dealsync.example.com',
                'email_reply_to'             => 'support@dealsync.example.com',
                'sender_identity_policy'     => 'org_default',
                'enforce_legal_footer'       => true,
                'legal_footer_html'          => '<div style="font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif; font-size: 11px; line-height: 1.5; color: #64748b; padding-top: 14px; border-top: 1px solid #e2e8f0; margin-top: 20px;">
  <p style="margin: 0 0 4px 0; color: #334155; font-weight: 600;">© ' . date('Y') . ' ' . $tLegal . ' &bull; Tax/VAT ID: US-EIN-987654321</p>
  <p style="margin: 0 0 6px 0;">100 Technology Park, Suite 400, San Francisco, CA 94107, United States</p>
  <p style="margin: 0; color: #94a3b8;">
    You are receiving this communication regarding your account or transaction with ' . $tName . '. 
    <a href="{{unsubscribe_link}}" style="color: #4f46e5; text-decoration: underline;">Manage email preferences or unsubscribe</a>.
  </p>
</div>',
                'logo_url'                   => null,
                'branding_theme'             => $theme,
                'accent_color'               => $accent,
                'page_bg_color'              => '#fafafa',
                'font_color'                 => '#09090b',
                'header_bg_color'            => '#ffffff',
                'sidebar_bg_color'           => '#ffffff',
                'table_bg_color'             => '#ffffff',
                'table_border_color'          => '#e2e8f0',
                'appearance_settings'        => json_encode([
                    'page'    => ['mode' => 'color', 'opacity' => 100, 'brightness' => 100, 'contrast' => 100, 'softness' => 0, 'fit' => 'cover'],
                    'header'  => ['mode' => 'color', 'opacity' => 100, 'brightness' => 100, 'contrast' => 100, 'softness' => 0, 'fit' => 'cover'],
                    'sidebar' => ['mode' => 'color', 'opacity' => 100, 'brightness' => 100, 'contrast' => 100, 'softness' => 0, 'fit' => 'cover'],
                    'table'   => ['mode' => 'color', 'opacity' => 100, 'brightness' => 100, 'contrast' => 100, 'softness' => 0, 'fit' => 'cover']
                ]),
                'plan_tier'                  => $tenant ? $tenant->plan_tier : 'enterprise',
                'max_seats'                  => $tenant ? $tenant->max_seats : 15,
            ]);
        }

        return $profile;
    }
}
