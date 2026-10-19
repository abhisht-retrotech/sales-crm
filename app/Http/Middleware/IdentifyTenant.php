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
        $host = strtolower($request->getHost());
        $tenant = null;

        // 1. Try finding tenant by exact domain match
        $tenant = Tenant::on('mysql')
            ->where('domain', $host)
            ->where('is_active', true)
            ->first();

        // 2. Try finding tenant by subdomain (e.g. "retrotech" from "retrotech.localhost" or "retrotech.local")
        if (!$tenant && str_contains($host, '.')) {
            $subdomain = explode('.', $host)[0];

            if (!in_array($subdomain, ['www', 'api', 'central', 'mail', 'superadmin', 'admin', 'crm'])) {
                $tenant = Tenant::on('mysql')
                    ->where('is_active', true)
                    ->where(function ($query) use ($subdomain) {
                        $query->where('domain', $subdomain)
                            ->orWhere('domain', 'like', $subdomain . '.%')
                            ->orWhere('domain', 'like', '%.' . $subdomain . '.%')
                            ->orWhere('name', $subdomain)
                            ->orWhere('name', 'like', $subdomain . '%');
                    })
                    ->first();
            }
        }

        // 3. Try finding tenant by custom header (e.g. for API or development)
        if (!$tenant && $request->hasHeader('X-Tenant-Domain')) {
            $domainHeader = strtolower($request->header('X-Tenant-Domain'));
            $tenant = Tenant::on('mysql')
                ->where('domain', $domainHeader)
                ->where('is_active', true)
                ->first();

            if (!$tenant && str_contains($domainHeader, '.')) {
                $subdomain = explode('.', $domainHeader)[0];
                $tenant = Tenant::on('mysql')
                    ->where('is_active', true)
                    ->where(function ($query) use ($subdomain) {
                        $query->where('domain', $subdomain)
                            ->orWhere('domain', 'like', $subdomain . '.%')
                            ->orWhere('name', $subdomain);
                    })
                    ->first();
            }
        }

        if (!$tenant && $request->hasHeader('X-Tenant-Id')) {
            $tenant = Tenant::on('mysql')
                ->where('id', $request->header('X-Tenant-Id'))
                ->where('is_active', true)
                ->first();
        }

        // 4. Try finding tenant by query parameter (?tenant=retrotech or ?tenant=retrotech.local)
        if (!$tenant && $request->query('tenant')) {
            $param = strtolower($request->query('tenant'));
            $tenant = Tenant::on('mysql')
                ->where('is_active', true)
                ->where(function ($query) use ($param) {
                    $query->where('domain', $param)
                        ->orWhere('domain', 'like', $param . '.%')
                        ->orWhere('name', $param);
                })
                ->first();
        }

        if (!$tenant && $request->query('tenant_id')) {
            $tenant = Tenant::on('mysql')
                ->where('id', $request->query('tenant_id'))
                ->where('is_active', true)
                ->first();
        }

        // 5. Try session (if already identified or logged into)
        if (!$tenant && $request->hasSession() && $request->session()->has('current_tenant_id')) {
            $tenant = Tenant::on('mysql')
                ->where('id', $request->session()->get('current_tenant_id'))
                ->where('is_active', true)
                ->first();
        }

        // 6. Localhost fallback: If developing on bare localhost / 127.0.0.1 without subdomain, auto-select first active tenant
        if (!$tenant && in_array($host, ['localhost', '127.0.0.1'])) {
            $tenant = Tenant::on('mysql')
                ->where('is_active', true)
                ->first();
        }

        abort_if(!$tenant, 404, 'Tenant company not found or inactive.');

        // Switch runtime database connection to tenant's isolated DB
        $tenant->connect();

        // Also store in session if session available
        if ($request->hasSession()) {
            $request->session()->put('current_tenant_id', $tenant->id);
        }

        // Share tenant with all Blade views
        if (function_exists('view')) {
            view()->share('currentTenant', $tenant);
        }

        return $next($request);
    }
}
