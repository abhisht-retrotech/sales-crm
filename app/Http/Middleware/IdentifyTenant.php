<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;

class IdentifyTenant
{
    /**
     * Handle an incoming request and switch database to the active tenant.
     */
    public function handle(Request $request, Closure $next)
    {
        $host = $request->getHost();
        $tenant = null;

        // 1. Try finding tenant by exact domain or subdomain
        $tenant = Tenant::on('mysql')
            ->where('domain', $host)
            ->where('is_active', true)
            ->first();

        // 2. Try finding tenant by custom header (e.g. for API or development)
        if (!$tenant && $request->hasHeader('X-Tenant-Domain')) {
            $tenant = Tenant::on('mysql')
                ->where('domain', $request->header('X-Tenant-Domain'))
                ->where('is_active', true)
                ->first();
        }

        if (!$tenant && $request->hasHeader('X-Tenant-Id')) {
            $tenant = Tenant::on('mysql')
                ->where('id', $request->header('X-Tenant-Id'))
                ->where('is_active', true)
                ->first();
        }

        // 3. Try finding tenant by query parameter (?tenant=company-domain or ?tenant_id=...)
        if (!$tenant && $request->query('tenant')) {
            $tenant = Tenant::on('mysql')
                ->where('domain', $request->query('tenant'))
                ->where('is_active', true)
                ->first();
        }

        if (!$tenant && $request->query('tenant_id')) {
            $tenant = Tenant::on('mysql')
                ->where('id', $request->query('tenant_id'))
                ->where('is_active', true)
                ->first();
        }

        // 4. Try session
        if (!$tenant && $request->hasSession() && $request->session()->has('current_tenant_id')) {
            $tenant = Tenant::on('mysql')
                ->where('id', $request->session()->get('current_tenant_id'))
                ->where('is_active', true)
                ->first();
        }

        // 5. Localhost fallback: If developing on bare localhost / 127.0.0.1, auto-select first active tenant
        if (!$tenant && in_array($host, ['localhost', '127.0.0.1'])) {
            $tenant = Tenant::on('mysql')
                ->where('is_active', true)
                ->first();
        }

        abort_if(!$tenant, 404, 'Tenant company not found or inactive.');

        // Switch runtime database connection to tenant's isolated DB
        $tenant->connect();

        // Share tenant with all Blade views
        if (function_exists('view')) {
            view()->share('currentTenant', $tenant);
        }

        return $next($request);
    }
}
