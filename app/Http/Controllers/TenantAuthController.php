<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\TenantUser;
use App\Models\Tenant;

class TenantAuthController extends Controller
{
    /**
     * Show the tenant login form.
     */
    public function showLoginForm()
    {
        if (Auth::guard('web')->check() || Auth::guard('super')->check()) {
            return redirect()->route('dashboard');
        }

        $tenant = app()->bound('currentTenant') ? app('currentTenant') : null;

        if (!$tenant) {
            $tenant = Tenant::on('mysql')->where('is_active', true)->first();
        }

        $tenantAdminEmail = null;
        if ($tenant) {
            try {
                $tenant->connect();
                $tenantAdminEmail = TenantUser::first()?->email;
            } catch (\Throwable $e) {
                // Fallback gracefully if database connection unavailable
            }
        }

        return view('auth.tenant_login', compact('tenant', 'tenantAdminEmail'));
    }

    /**
     * Handle an incoming authentication request for tenant users.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        // Check user status in active tenant
        $user = TenantUser::where('email', $credentials['email'])->first();

        if ($user && isset($user->status) && strtolower($user->status) !== 'active') {
            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors([
                    'email' => 'Your account is inactive or suspended. Please contact your organization administrator.',
                ]);
        }

        // Attempt authentication via tenant web guard
        if (Auth::guard('web')->attempt($credentials, $remember)) {
            $request->session()->regenerate();

            if (app()->bound('currentTenant')) {
                $request->session()->put('current_tenant_id', app('currentTenant')->id);
            }

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success'  => true,
                    'message'  => 'Logged in successfully',
                    'redirect' => route('dashboard'),
                ]);
            }

            $userName = Auth::guard('web')->user()->name ?? 'User';

            return redirect()->intended(route('dashboard'))
                ->with('success', "Welcome back, {$userName}!");
        }

        // Also allow Super Admin credentials to log in seamlessly
        if (Auth::guard('super')->attempt($credentials, $remember)) {
            $request->session()->regenerate();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success'  => true,
                    'message'  => 'Logged in as Super Administrator',
                    'redirect' => route('dashboard'),
                ]);
            }

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Logged in with Super Administrator privileges.');
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password for this organization workspace.',
            ], 422);
        }

        return back()
            ->withInput($request->only('email', 'remember'))
            ->withErrors([
                'email' => 'Invalid email or password for this organization workspace.',
            ]);
    }

    /**
     * Destroy an authenticated session.
     */
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        Auth::guard('super')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('tenant.login')
            ->with('success', 'You have been signed out successfully.');
    }
}
