<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Services\TenantProvisioner;
use App\Models\Tenant;
use App\Models\TenantUser;

class CompanyRegistrationController extends Controller
{
    /**
     * Show the company self-registration page.
     */
    public function showRegistrationForm()
    {
        if (Auth::guard('web')->check() || Auth::guard('super')->check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.register_company');
    }

    /**
     * Provision and register a brand new company workspace and admin user.
     */
    public function register(Request $request, TenantProvisioner $provisioner)
    {
        $validated = $request->validate([
            'company_name'   => ['required', 'string', 'max:150'],
            'domain'         => ['required', 'string', 'max:100'],
            'admin_name'     => ['required', 'string', 'max:120'],
            'admin_email'    => ['required', 'email'],
            'admin_password' => ['required', 'string', 'min:6', 'confirmed'],
            'plan_tier'      => ['nullable', 'string', 'in:starter,professional,enterprise'],
            'max_seats'      => ['nullable', 'integer', 'min:1', 'max:500'],
            'accent_color'   => ['nullable', 'string', 'max:30'],
            'legal_name'     => ['nullable', 'string', 'max:200'],
        ]);

        $domain = strtolower(trim($validated['domain']));
        $domain = preg_replace('#^https?://#', '', $domain);
        if (!str_contains($domain, '.')) {
            $domain = Str::slug($domain, '-') . '.local';
        }

        // Verify domain uniqueness
        if (Tenant::on('mysql')->where('domain', $domain)->exists()) {
            return back()
                ->withInput()
                ->withErrors(['domain' => "The workspace domain '{$domain}' is already taken. Please choose another."]);
        }

        try {
            $accentColor = $validated['accent_color'] ?? '#4F46E5';
            $maxSeats = $validated['max_seats'] ?? 25;
            $planTier = $validated['plan_tier'] ?? 'enterprise';

            // Provision new isolated database and complete seed
            $tenant = $provisioner->create(
                $validated['company_name'],
                $domain,
                $validated['admin_email'],
                $validated['admin_password'],
                [
                    'legal_name'     => $validated['legal_name'] ?? $validated['company_name'],
                    'admin_name'     => $validated['admin_name'],
                    'plan_tier'      => $planTier,
                    'max_seats'      => $maxSeats,
                    'accent_color'   => $accentColor,
                    'branding_theme' => 'apple',
                ]
            );

            // Connect to runtime tenant database to authenticate the new administrator
            $tenant->connect();

            $adminUser = TenantUser::on('tenant')
                ->where('email', $validated['admin_email'])
                ->first();

            // Set tenant session ID
            $request->session()->put('current_tenant_id', $tenant->id);
            $request->session()->regenerate();

            if ($adminUser) {
                Auth::guard('web')->login($adminUser);
            }

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success'  => true,
                    'message'  => "Workspace for '{$tenant->name}' created successfully!",
                    'tenant'   => $tenant,
                    'redirect' => route('dashboard'),
                ], 201);
            }

            return redirect()->route('dashboard')
                ->with('success', "🎉 Congratulations! Company workspace '{$tenant->name}' is now active and ready. Welcome aboard!");

        } catch (\Throwable $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Registration failed: ' . $e->getMessage(),
                ], 500);
            }

            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to provision workspace: ' . $e->getMessage()]);
        }
    }
}
