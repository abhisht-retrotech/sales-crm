<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuperAdminAuthController extends Controller
{
    /**
     * Show the Super Admin login form.
     */
    public function showLoginForm()
    {
        if (Auth::guard('super')->check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.super_admin_login');
    }

    /**
     * Handle a Super Admin login request.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::guard('super')->attempt($credentials, $remember)) {
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Welcome back! Logged in successfully as Super Administrator.');
        }

        return back()
            ->withInput($request->only('email', 'remember'))
            ->withErrors([
                'email' => 'Invalid Super Admin credentials. Please verify your email and password.',
            ]);
    }

    /**
     * Log the Super Admin out of the application.
     */
    public function logout(Request $request)
    {
        Auth::guard('super')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('super_admin.login')
            ->with('success', 'You have been logged out successfully.');
    }
}
