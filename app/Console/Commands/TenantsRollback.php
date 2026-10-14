<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class TenantsRollback extends Command
{
    protected $signature = 'tenants:rollback {--tenant_id= : Rollback only for a specific tenant ID}';
    protected $description = 'Rollback tenant migrations across all active tenant databases';

    public function handle(): int
    {
        $query = Tenant::on('mysql')->where('is_active', true);

        if ($tenantId = $this->option('tenant_id')) {
            $query->where('id', $tenantId);
        }

        $tenants = $query->get();

        foreach ($tenants as $tenant) {
            $this->info("Rolling back migrations for tenant #{$tenant->id}: {$tenant->name} [DB: {$tenant->db_name}]");

            try {
                $tenant->connect();

                Artisan::call('migrate:rollback', [
                    '--database' => 'tenant',
                    '--path'     => 'database/migrations/tenant',
                    '--force'    => true,
                ]);

                $this->line(Artisan::output());
            } catch (\Throwable $e) {
                $this->error("FAILED {$tenant->name}: " . $e->getMessage());
            }
        }

        return Command::SUCCESS;
    }
}
