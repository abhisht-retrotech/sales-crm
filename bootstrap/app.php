<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'tenant'       => \App\Http\Middleware\IdentifyTenant::class,
            'super.auth'   => \App\Http\Middleware\SuperAdminAuth::class,
            'tenant.auth'  => \App\Http\Middleware\TenantAuth::class,
            'tenant.admin' => \App\Http\Middleware\TenantAdminOnly::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'api/*',
            'admin/dashboard-studio/*',
            'admin/company-profile/*',
            'admin/company-profile',
            'admin/users-directory/*',
            'admin/users-directory',
            'admin/users/*',
            'admin/users',
            'admin/security-governance/*',
            'admin/security-governance',
            'admin/roles-permissions/*',
            'admin/roles-permissions',
            'admin/record-rights/*',
            'admin/record-rights',
            'admin/field-rights/*',
            'admin/field-rights',
            'admin/notification-rules/*',
            'admin/notification-rules',
            'admin/email-templates/*',
            'admin/email-templates',
        ]);

        $middleware->prependToPriorityList(
            before: \Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests::class,
            prepend: \App\Http\Middleware\IdentifyTenant::class,
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
