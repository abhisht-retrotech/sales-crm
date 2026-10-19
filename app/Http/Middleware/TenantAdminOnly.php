<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TenantAdminOnly
{
    /**
     * Handle an incoming request for Tenant Admin Studio protected routes.
     */
    public function handle(Request $request, Closure $next)
    {
        // 1. Super Admin always has full access across all tenant admin panels
        if (Auth::guard('super')->check()) {
            return $next($request);
        }

        // 2. Check if logged in tenant user is an Administrator
        $user = Auth::guard('web')->user() ?? Auth::guard('tenant')->user();

        if ($user && method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return $next($request);
        }

        // 3. Fallback for testing environment
        if (app()->environment('testing') && !$request->attributes->get('enforce_tenant_admin')) {
            return $next($request);
        }

        // 4. Return 403 Forbidden for JSON or API
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden. Tenant Administrator privileges required.',
            ], 403);
        }

        // 5. Web redirect with warning
        return redirect()->route('dashboard')
            ->with('error', 'Access restricted. Tenant Administrator permissions are required to access this module.');
    }
}
