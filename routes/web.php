<?php

use Illuminate\Support\Facades\Route;
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
use App\Http\Controllers\AdminController;

use App\Models\Tenant;
use App\Services\TenantProvisioner;
use Illuminate\Http\Request;

// Central Super Admin & Tenant Management API
Route::prefix('central')->group(function () {
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

// All Tenant-isolated CRM Operations & Admin Studio
Route::middleware('tenant')->group(function () {
    // Current Tenant Information Inspection
    Route::get('/tenant/whoami', function () {
        return response()->json([
            'tenant_id' => app('currentTenant')->id,
            'company'   => app('currentTenant')->name,
            'domain'    => app('currentTenant')->domain,
            'database'  => \Illuminate\Support\Facades\DB::connection('tenant')->getDatabaseName(),
        ]);
    })->name('tenant.whoami');

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    // Core Sales & Operations
    Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');
    Route::get('/accounts', [AccountController::class, 'index'])->name('accounts.index');
    Route::get('/contacts', [ContactController::class, 'index'])->name('contacts.index');
    Route::get('/opportunities', [OpportunityController::class, 'index'])->name('opportunities.index');
    Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');

    // Procurement & Logistics
    Route::get('/purchase-deals', [PurchaseDealController::class, 'index'])->name('purchase_deals.index');
    Route::get('/shipping', [ShippingController::class, 'index'])->name('shipping.index');

    // Finance & Banking
    Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/wires', [WireController::class, 'index'])->name('wires.index');

    // Governance & Admin
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');

    // Dedicated Admin Console & Rules Modules
    Route::prefix('admin')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('admin.index');
        Route::get('/tenant-studio', [AdminController::class, 'tenantStudio'])->name('admin.tenant_studio');
        Route::get('/company-profile', [AdminController::class, 'companyProfile'])->name('admin.company_profile');
        Route::get('/users-directory', [AdminController::class, 'usersDirectory'])->name('admin.users_directory');
        Route::get('/users', [AdminController::class, 'usersDirectory'])->name('admin.users');
        Route::get('/dashboard-studio', [AdminController::class, 'dashboardStudio'])->name('admin.dashboard_studio');
        Route::get('/roles-permissions', [AdminController::class, 'rolesPermissions'])->name('admin.roles_permissions');
        Route::get('/record-rights', [AdminController::class, 'recordRights'])->name('admin.record_rights');
        Route::get('/field-rights', [AdminController::class, 'fieldRights'])->name('admin.field_rights');
        Route::get('/sharing-rules', [AdminController::class, 'sharingRules'])->name('admin.sharing_rules');
        Route::get('/security-governance', [AdminController::class, 'securityGovernance'])->name('admin.security_governance');
        Route::get('/approval-rules', [AdminController::class, 'approvalRules'])->name('admin.approval_rules');
        Route::get('/duplicate-rules', [AdminController::class, 'duplicateRules'])->name('admin.duplicate_rules');
        Route::get('/process-studio', [AdminController::class, 'processStudio'])->name('admin.process_studio');
        Route::get('/notification-rules', [AdminController::class, 'notificationRules'])->name('admin.notification_rules');
        Route::get('/object-fields', [AdminController::class, 'objectFields'])->name('admin.object_fields');
        Route::get('/custom-objects', [AdminController::class, 'customObjects'])->name('admin.custom_objects');
        Route::get('/page-layouts', [AdminController::class, 'pageLayouts'])->name('admin.page_layouts');
        Route::get('/data-studio', [AdminController::class, 'dataStudio'])->name('admin.data_studio');
        Route::get('/audit-logs', [AdminController::class, 'auditLogs'])->name('admin.audit_logs');
    });
});
