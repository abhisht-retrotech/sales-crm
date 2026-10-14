<?php

namespace App\Console\Commands;

use App\Services\TenantProvisioner;
use Illuminate\Console\Command;

class CreateTenant extends Command
{
    protected $signature = 'tenant:create 
                            {name : Name of the company/tenant} 
                            {domain : Domain or subdomain for the tenant (e.g. acme.localhost)}
                            {--admin_email=admin@company.local : Email of the initial tenant administrator}
                            {--admin_password=password : Password for the initial tenant administrator}
                            {--plan=enterprise : Subscription plan tier}';

    protected $description = 'Create a new tenant company with an isolated MySQL database and complete schema';

    public function handle(TenantProvisioner $provisioner): int
    {
        $name     = $this->argument('name');
        $domain   = $this->argument('domain');
        $email    = $this->option('admin_email');
        $password = $this->option('admin_password');
        $plan     = $this->option('plan');

        $this->info("Provisioning isolated database for: {$name} ({$domain})...");

        try {
            $tenant = $provisioner->create($name, $domain, $email, $password, [
                'plan_tier' => $plan,
            ]);

            $this->info("✓ Tenant created successfully!");
            $this->table(
                ['Tenant ID', 'Company Name', 'Domain', 'Database Name', 'Admin Email'],
                [[$tenant->id, $tenant->name, $tenant->domain, $tenant->db_name, $email]]
            );

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Failed to provision tenant: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
