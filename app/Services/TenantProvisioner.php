<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\TenantUser;
use App\Models\Role;
use App\Models\Profile;
use App\Models\TenantSetting;
use App\Models\TenantCurrency;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TenantProvisioner
{
    /**
     * Provision a completely isolated new database for a tenant company.
     */
    public function create(
        string $name,
        string $domain,
        string $adminEmail,
        string $adminPassword,
        array $extra = []
    ): Tenant {
        $dbName = 'crm_' . Str::slug($name, '_') . '_' . Str::lower(Str::random(5));
        $tenant = null;

        // 1. Create isolated MySQL database for this tenant
        DB::connection('mysql')->statement(
            "CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
        );

        try {
            // 2. Register Tenant record in central database
            $tenant = Tenant::create([
                'name'           => $name,
                'legal_name'     => $extra['legal_name'] ?? $name,
                'domain'         => strtolower($domain),
                'db_host'        => env('DB_HOST', '127.0.0.1'),
                'db_port'        => env('DB_PORT', 3306),
                'db_name'        => $dbName,
                'db_username'    => env('DB_USERNAME', 'root'),
                'db_password'    => env('DB_PASSWORD', ''),
                'is_active'      => true,
                'plan_tier'      => $extra['plan_tier'] ?? 'enterprise',
                'max_seats'      => $extra['max_seats'] ?? 15,
                'branding_theme' => $extra['branding_theme'] ?? 'apple',
                'accent_color'   => $extra['accent_color'] ?? '#4F46E5',
            ]);

            // 3. Connect runtime connection to this tenant database
            $tenant->connect();

            // 4. Run tenant migrations inside the tenant's new database
            Artisan::call('migrate', [
                '--database' => 'tenant',
                '--path'     => 'database/migrations/tenant',
                '--force'    => true,
            ]);

            // Run tenant seeder for roles, profiles, picklists, and currencies
            Artisan::call('db:seed', [
                '--class'    => 'Database\\Seeders\\TenantDatabaseSeeder',
                '--database' => 'tenant',
                '--force'    => true,
            ]);

            $adminRole = Role::where('name', 'System Administrator')->first() ?? Role::first();
            $adminProfile = Profile::where('name', 'System Administrator')->first() ?? Profile::first();

            // 6. Create initial Administrator user in tenant database
            TenantUser::updateOrCreate(
                ['email' => $adminEmail],
                [
                    'name'                => $extra['admin_name'] ?? 'Administrator',
                    'password'            => $adminPassword,
                    'role_id'             => $adminRole?->id,
                    'profile_id'          => $adminProfile?->id,
                    'status'              => 'Active',
                    'surprise_me_enabled' => true,
                ]
            );

            // 7. Seed base tenant settings & currency
            TenantSetting::updateOrCreate(
                ['key' => 'company_profile'],
                ['value' => json_encode([
                    'company_name'   => $name,
                    'legal_name'     => $extra['legal_name'] ?? $name,
                    'time_zone'      => 'America/New_York',
                    'date_format'    => 'YYYY-MM-DD',
                    'time_format'    => '12h',
                    'branding_theme' => $extra['branding_theme'] ?? 'apple',
                    'accent_color'   => $extra['accent_color'] ?? '#4F46E5',
                ])]
            );

            TenantCurrency::updateOrCreate(
                ['currency_code'   => 'USD'],
                [
                    'currency_symbol' => '$',
                    'currency_name'   => 'US Dollar',
                    'exchange_rate'   => 1.0,
                    'is_base'         => true,
                    'is_active'       => true,
                ]
            );

            return $tenant;
        } catch (\Throwable $e) {
            // Rollback if failure occurs
            $tenant?->delete();
            DB::connection('mysql')->statement("DROP DATABASE IF EXISTS `$dbName`");
            throw $e;
        }
    }
}
