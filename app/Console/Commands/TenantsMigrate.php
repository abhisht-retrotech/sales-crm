<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class TenantsMigrate extends Command
{
    protected $signature = 'tenants:migrate {--tenant_id= : Run migration only for a specific tenant ID}';
    protected $description = 'Run tenant migrations across all active tenant databases';

    public function handle(): int
    {
        $query = Tenant::on('mysql')->where('is_active', true);

        if ($tenantId = $this->option('tenant_id')) {
            $query->where('id', $tenantId);
        }

        $tenants = $query->get();

        if ($tenants->isEmpty()) {
            $this->warn("No active tenants found to migrate.");
            return Command::SUCCESS;
        }

        $this->info("Found {$tenants->count()} tenant(s). Running migrations...");

        foreach ($tenants as $tenant) {
            $this->line("--------------------------------------------------");
            $this->info("Migrating tenant #{$tenant->id}: {$tenant->name} [DB: {$tenant->db_name}]");

            try {
                $tenant->connect();

                Artisan::call('migrate', [
                    '--database' => 'tenant',
                    '--path'     => 'database/migrations/tenant',
                    '--force'    => true,
                ]);

                $this->line(Artisan::output());
                $this->info("✓ Completed for {$tenant->name}");
            } catch (\Throwable $e) {
                $this->error("✗ FAILED {$tenant->name}: " . $e->getMessage());
            }
        }

        return Command::SUCCESS;
    }
}
