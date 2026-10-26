<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Services\TenantProvisioner;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\OpportunityController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\PurchaseDealController;
use App\Http\Controllers\ShippingController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\WireController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SuperAdminAuthController;
use App\Http\Controllers\TenantAuthController;
use App\Http\Controllers\CompanyRegistrationController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\WorkFlowController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Central authentication, workspace registration, and tenant CRM web pages.
| Admin console routes are loaded from routes/admin.php.
| JSON API endpoints are loaded from routes/api.php.
|
*/

// =========================================================================
// 1. Super Admin Central Authentication
// =========================================================================
Route::get('/super-admin/login', [SuperAdminAuthController::class, 'showLoginForm'])->name('super_admin.login');
Route::post('/super-admin/login', [SuperAdminAuthController::class, 'login'])->name('super_admin.login.submit');
Route::post('/super-admin/logout', [SuperAdminAuthController::class, 'logout'])->name('super_admin.logout');
Route::get('/super-admin', fn () => redirect()->route('super_admin.login'));

// =========================================================================
// 2. Company Self-Registration & Onboarding Routes
// =========================================================================
Route::get('/register', [CompanyRegistrationController::class, 'showRegistrationForm'])->name('tenant.register');
Route::post('/register', [CompanyRegistrationController::class, 'register'])->name('tenant.register.submit');
Route::get('/signup', fn () => redirect()->route('tenant.register'))->name('register');

// =========================================================================
// 3. Tenant Authentication Routes
// =========================================================================
Route::middleware('tenant')->group(function () {
    Route::get('/login', [TenantAuthController::class, 'showLoginForm'])->name('tenant.login');
    Route::post('/login', [TenantAuthController::class, 'login'])->name('tenant.login.submit');
    Route::post('/logout', [TenantAuthController::class, 'logout'])->name('tenant.logout');
});

// Central / Login Aliases
Route::get('/signin', fn () => redirect()->route('tenant.login'))->name('login');
Route::get('/admin/login', fn () => redirect()->route('tenant.login'));

// =========================================================================
// 4. Central Super Admin & Tenant Management API (Super Admin Guarded)
// =========================================================================
Route::prefix('central')->middleware('super.auth')->group(function () {
    Route::get('/tenants', function () {
        return response()->json(Tenant::on('mysql')->latest()->get());
    })->name('central.tenants.index');

    Route::post('/tenants', function (Request $request, TenantProvisioner $provisioner) {
        $validated = $request->validate([
            'name'           => 'required|string|max:150',
            'domain'         => 'required|string|max:100|unique:mysql.tenants,domain',
            'admin_email'    => 'required|email',
            'admin_password' => 'required|min:6',
            'plan_tier'      => 'nullable|string',
        ]);

        $tenant = $provisioner->create(
            $validated['name'],
            $validated['domain'],
            $validated['admin_email'],
            $validated['admin_password'],
            ['plan_tier' => $validated['plan_tier'] ?? 'enterprise']
        );

        return response()->json([
            'message' => 'Tenant provisioned successfully with isolated database',
            'tenant'  => $tenant,
        ], 201);
    })->name('central.tenants.store');
});

// =========================================================================
// 5. Tenant-Isolated CRM Web Operations
// =========================================================================
Route::middleware(['tenant', 'tenant.auth'])->group(function () {
    // Current Tenant Information Inspection
    Route::get('/tenant/whoami', function () {
        return response()->json([
            'tenant_id' => app('currentTenant')->id,
            'company'   => app('currentTenant')->name,
            'domain'    => app('currentTenant')->domain,
            'database'  => \Illuminate\Support\Facades\DB::connection('tenant')->getDatabaseName(),
        ]);
    })->name('tenant.whoami');

    // Communications & Schedule
    Route::get('/emails', [EmailController::class, 'index'])->name('emails.index');
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    // Core Sales & Operations Views
    Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');
    Route::get('/leads/{id}', [LeadController::class, 'show'])->name('leads.show');

    Route::get('/accounts', [AccountController::class, 'index'])->name('accounts.index');
    Route::get('/accounts/{id}', [AccountController::class, 'show'])->name('accounts.show');

    Route::get('/contacts', [ContactController::class, 'index'])->name('contacts.index');
    Route::get('/contacts/{id}', [ContactController::class, 'show'])->name('contacts.show');

    Route::get('/opportunities', [OpportunityController::class, 'index'])->name('opportunities.index');
    Route::get('/opportunities/{id}', [OpportunityController::class, 'show'])->name('opportunities.show');

    Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::get('/tasks/{id}', [TaskController::class, 'show'])->name('tasks.show');

    // Procurement & Logistics Views
    Route::get('/purchase-deals', [PurchaseDealController::class, 'index'])->name('purchase_deals.index');
    Route::get('/purchase-deals/{id}', [PurchaseDealController::class, 'show'])->name('purchase_deals.show');

    Route::get('/shipping', [ShippingController::class, 'index'])->name('shipping.index');
    Route::get('/shipping/{id}', [ShippingController::class, 'show'])->name('shipping.show');

    // Finance & Banking Views
    Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::get('/documents/{id}', [DocumentController::class, 'show'])->name('documents.show');

    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/{id}', [PaymentController::class, 'show'])->name('payments.show');

    Route::get('/wires', [WireController::class, 'index'])->name('wires.index');
    Route::get('/wires/{id}', [WireController::class, 'show'])->name('wires.show');

    // Settings & Process Studios Views
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::get('/process-studios', [WorkFlowController::class, 'canvas'])->name('process_studio');
    Route::get('/process-studio', [WorkFlowController::class, 'canvas'])->name('process_studio.canvas');
});

// =========================================================================
// 6. Dedicated Admin Console & Governance Routes (routes/admin.php)
// =========================================================================
require __DIR__ . '/admin.php';
