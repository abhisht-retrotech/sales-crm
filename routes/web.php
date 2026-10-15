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
use App\Http\Controllers\SuperAdminAuthController;

use App\Models\Tenant;
use App\Services\TenantProvisioner;
use Illuminate\Http\Request;

// Super Admin Authentication
Route::get('/super-admin/login', [SuperAdminAuthController::class, 'showLoginForm'])->name('super_admin.login');
Route::post('/super-admin/login', [SuperAdminAuthController::class, 'login'])->name('super_admin.login.submit');
Route::post('/super-admin/logout', [SuperAdminAuthController::class, 'logout'])->name('super_admin.logout');

// Central / Login Aliases
Route::get('/login', fn () => redirect()->route('super_admin.login'))->name('login');
Route::get('/admin/login', fn () => redirect()->route('super_admin.login'));
Route::get('/super-admin', fn () => redirect()->route('super_admin.login'));

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

    // Communications & Schedule (React CRM Parity)
    Route::get('/emails', [DashboardController::class, 'index'])->name('emails.index');
    Route::get('/calendar', [DashboardController::class, 'index'])->name('calendar.index');

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
        Route::post('/tenant-studio/provision', [AdminController::class, 'storeTenant'])->name('admin.tenant_studio.store');
        Route::put('/tenant-studio/{tenant}', [AdminController::class, 'updateTenant'])->name('admin.tenant_studio.update');
        Route::delete('/tenant-studio/{tenant}', [AdminController::class, 'destroyTenant'])->name('admin.tenant_studio.destroy');
        Route::post('/tenant-studio/{tenant}/switch', [AdminController::class, 'switchTenant'])->name('admin.tenant_studio.switch');
        Route::get('/company-profile', [AdminController::class, 'companyProfile'])->name('admin.company_profile');
        Route::post('/company-profile/save', [AdminController::class, 'updateCompanyProfile'])->name('admin.company_profile.save');
        Route::put('/company-profile', [AdminController::class, 'updateCompanyProfile'])->name('admin.company_profile.update');
        Route::get('/company-profile/currencies', [AdminController::class, 'getTenantCurrencies'])->name('admin.company_profile.currencies');
        Route::post('/company-profile/currencies', [AdminController::class, 'addTenantCurrency'])->name('admin.company_profile.currencies.store');
        Route::put('/company-profile/currencies/{currency}', [AdminController::class, 'updateTenantCurrency'])->name('admin.company_profile.currencies.update');
        Route::delete('/company-profile/currencies/{currency}', [AdminController::class, 'deleteTenantCurrency'])->name('admin.company_profile.currencies.destroy');
        Route::post('/company-profile/currencies/set-base', [AdminController::class, 'setTenantBaseCurrency'])->name('admin.company_profile.currencies.set_base');
        Route::get('/company-profile/email-settings', [AdminController::class, 'getTenantEmailSettings'])->name('admin.company_profile.email_settings');
        Route::put('/company-profile/email-settings', [AdminController::class, 'updateTenantEmailSettings'])->name('admin.company_profile.email_settings.update');
        Route::post('/company-profile/email-settings/test', [AdminController::class, 'testTenantEmailDelivery'])->name('admin.company_profile.email_settings.test');
        Route::get('/users-directory', [AdminController::class, 'usersDirectory'])->name('admin.users_directory');
        Route::get('/users', [AdminController::class, 'usersDirectory'])->name('admin.users');
        Route::post('/users-directory/invite', [AdminController::class, 'createTenantInvitation'])->name('admin.users_directory.invite');
        Route::post('/users-directory/invite/{id}/resend', [AdminController::class, 'resendTenantInvitation'])->name('admin.users_directory.invite.resend');
        Route::delete('/users-directory/invite/{id}', [AdminController::class, 'deleteTenantInvitation'])->name('admin.users_directory.invite.delete');
        Route::get('/users-directory/summary/{id}', [AdminController::class, 'getUserOwnedRecordsSummary'])->name('admin.users_directory.summary');
        Route::post('/users-directory/offboard/{id}', [AdminController::class, 'offboardUser'])->name('admin.users_directory.offboard');
        Route::post('/users-directory/add-seats', [AdminController::class, 'addSeats'])->name('admin.users_directory.add_seats');
        Route::put('/users-directory/user/{id}', [AdminController::class, 'updateUserApi'])->name('admin.users_directory.user.update');
        Route::post('/users-directory/user/{id}/impersonate', [AdminController::class, 'impersonateUserApi'])->name('admin.users_directory.user.impersonate');
        Route::get('/dashboard-studio', [AdminController::class, 'dashboardStudio'])->name('admin.dashboard_studio');
        Route::post('/dashboard-studio/save', [AdminController::class, 'saveDashboardLayout'])->name('admin.dashboard_studio.save');
        Route::post('/dashboard-studio/reset', [AdminController::class, 'resetDashboardLayoutWeb'])->name('admin.dashboard_studio.reset');
        Route::get('/roles-permissions', [AdminController::class, 'rolesPermissions'])->name('admin.roles_permissions');
        Route::get('/roles-permissions/roles', [AdminController::class, 'getRoles'])->name('admin.roles_permissions.roles.index');
        Route::post('/roles-permissions/roles', [AdminController::class, 'createRole'])->name('admin.roles_permissions.roles.store');
        Route::put('/roles-permissions/roles/{id}', [AdminController::class, 'updateRole'])->name('admin.roles_permissions.roles.update');
        Route::delete('/roles-permissions/roles/{id}', [AdminController::class, 'deleteRole'])->name('admin.roles_permissions.roles.destroy');
        Route::get('/roles-permissions/profiles', [AdminController::class, 'getProfiles'])->name('admin.roles_permissions.profiles.index');
        Route::post('/roles-permissions/profiles', [AdminController::class, 'createProfile'])->name('admin.roles_permissions.profiles.store');
        Route::put('/roles-permissions/profiles/{id}', [AdminController::class, 'updateProfile'])->name('admin.roles_permissions.profiles.update');
        Route::delete('/roles-permissions/profiles/{id}', [AdminController::class, 'deleteProfile'])->name('admin.roles_permissions.profiles.destroy');

        Route::get('/record-rights', [AdminController::class, 'recordRights'])->name('admin.record_rights');
        Route::get('/field-rights', [AdminController::class, 'fieldRights'])->name('admin.field_rights');
        Route::get('/sharing-rules', [AdminController::class, 'sharingRules'])->name('admin.sharing_rules');
        Route::get('/security-governance', [AdminController::class, 'securityGovernance'])->name('admin.security_governance');
        Route::post('/security-governance/save', [AdminController::class, 'updateTenantSecurityPolicies'])->name('admin.security_governance.save');
        Route::post('/security-governance/override-seats', [AdminController::class, 'updateTenantSecurityPolicies'])->name('admin.security_governance.override_seats');
        Route::post('/security-governance/add-seats', [AdminController::class, 'purchaseTenantSeats'])->name('admin.security_governance.add_seats');
        Route::get('/security-governance/invoices', [AdminController::class, 'getTenantInvoices'])->name('admin.security_governance.invoices');
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

    // Dashboard Studio Layouts Persistence & Analytics API (React Parity)
    Route::prefix('api/analytics')->group(function () {
        Route::get('/dashboard-layouts', [AdminController::class, 'getDashboardLayouts'])->name('api.dashboard_layouts.index');
        Route::get('/dashboard-layouts/{profileId}', [AdminController::class, 'getDashboardLayout'])->name('api.dashboard_layouts.show');
        Route::post('/dashboard-layouts', [AdminController::class, 'saveDashboardLayout'])->name('api.dashboard_layouts.store');
        Route::delete('/dashboard-layouts/{profileId}', [AdminController::class, 'resetDashboardLayout'])->name('api.dashboard_layouts.destroy');
    });

    // React Tenant & Company Profile Endpoints (React Parity)
    Route::prefix('api/tenants')->group(function () {
        Route::get('/{tenant?}', [AdminController::class, 'getTenantApi'])->name('api.tenants.show');
        Route::put('/{tenant?}', [AdminController::class, 'updateTenantApi'])->name('api.tenants.update');
        Route::get('/{tenant}/currencies', [AdminController::class, 'getTenantCurrencies'])->name('api.tenants.currencies');
        Route::post('/{tenant}/currencies', [AdminController::class, 'addTenantCurrency'])->name('api.tenants.currencies.store');
        Route::put('/{tenant}/currencies/{currency}', [AdminController::class, 'updateTenantCurrency'])->name('api.tenants.currencies.update');
        Route::delete('/{tenant}/currencies/{currency}', [AdminController::class, 'deleteTenantCurrency'])->name('api.tenants.currencies.destroy');
        Route::post('/{tenant}/currencies/set-base', [AdminController::class, 'setTenantBaseCurrency'])->name('api.tenants.currencies.set_base');
        Route::get('/{tenant}/email-settings', [AdminController::class, 'getTenantEmailSettings'])->name('api.tenants.email_settings');
        Route::put('/{tenant}/email-settings', [AdminController::class, 'updateTenantEmailSettings'])->name('api.tenants.email_settings.update');
        Route::post('/{tenant}/email-settings/test', [AdminController::class, 'testTenantEmailDelivery'])->name('api.tenants.email_settings.test');
        // Team Invitations
        Route::get('/{tenant}/invitations', [AdminController::class, 'getTenantInvitations'])->name('api.tenants.invitations.index');
        Route::post('/{tenant}/invitations', [AdminController::class, 'createTenantInvitation'])->name('api.tenants.invitations.store');
        Route::post('/{tenant}/invitations/{id}/resend', [AdminController::class, 'resendTenantInvitation'])->name('api.tenants.invitations.resend');
        Route::delete('/{tenant}/invitations/{id}', [AdminController::class, 'deleteTenantInvitation'])->name('api.tenants.invitations.destroy');
        // Security Policies & Subscription Governance (React Parity)
        Route::get('/{tenant}/security-policies', [AdminController::class, 'getTenantSecurityPolicies'])->name('api.tenants.security_policies.show');
        Route::put('/{tenant}/security-policies', [AdminController::class, 'updateTenantSecurityPolicies'])->name('api.tenants.security_policies.update');
        Route::get('/{tenant}/subscription', [AdminController::class, 'getTenantSubscription'])->name('api.tenants.subscription.show');
        Route::post('/{tenant}/subscription/add-seats', [AdminController::class, 'purchaseTenantSeats'])->name('api.tenants.subscription.add_seats');
        Route::get('/{tenant}/subscription/invoices', [AdminController::class, 'getTenantInvoices'])->name('api.tenants.subscription.invoices');
    });

    // Users & Team Management Endpoints (React Parity)
    Route::prefix('api/settings')->group(function () {
        // Roles & Permissions Architecture (React Parity)
        Route::get('/roles', [AdminController::class, 'getRoles'])->name('api.settings.roles.index');
        Route::post('/roles', [AdminController::class, 'createRole'])->name('api.settings.roles.store');
        Route::get('/roles/{id}', [AdminController::class, 'getRole'])->name('api.settings.roles.show');
        Route::put('/roles/{id}', [AdminController::class, 'updateRole'])->name('api.settings.roles.update');
        Route::delete('/roles/{id}', [AdminController::class, 'deleteRole'])->name('api.settings.roles.destroy');

        Route::get('/profiles', [AdminController::class, 'getProfiles'])->name('api.settings.profiles.index');
        Route::post('/profiles', [AdminController::class, 'createProfile'])->name('api.settings.profiles.store');
        Route::get('/profiles/{id}', [AdminController::class, 'getProfile'])->name('api.settings.profiles.show');
        Route::put('/profiles/{id}', [AdminController::class, 'updateProfile'])->name('api.settings.profiles.update');
        Route::delete('/profiles/{id}', [AdminController::class, 'deleteProfile'])->name('api.settings.profiles.destroy');

        Route::get('/permissions/{profileId}', [AdminController::class, 'getPermissions'])->name('api.settings.permissions.show');
        Route::put('/permissions/{profileId}', [AdminController::class, 'updatePermissions'])->name('api.settings.permissions.update');
        Route::post('/permissions/{profileId}/module', [AdminController::class, 'upsertModulePermission'])->name('api.settings.permissions.module.upsert');
        Route::post('/permissions/{profileId}/field', [AdminController::class, 'upsertFieldPermission'])->name('api.settings.permissions.field.upsert');
        Route::delete('/permissions/{profileId}/module/{module}', [AdminController::class, 'deleteModulePermission'])->name('api.settings.permissions.module.destroy');
        Route::post('/permissions/{profileId}/clone', [AdminController::class, 'clonePermissions'])->name('api.settings.permissions.clone');
        Route::post('/permissions/{profileId}/reset', [AdminController::class, 'resetPermissions'])->name('api.settings.permissions.reset');
        Route::get('/users', [AdminController::class, 'getUsersApi'])->name('api.settings.users.index');
        Route::post('/users', [AdminController::class, 'createUserApi'])->name('api.settings.users.store');
        Route::get('/users/{id}', [AdminController::class, 'getUserApi'])->name('api.settings.users.show');
        Route::put('/users/{id}', [AdminController::class, 'updateUserApi'])->name('api.settings.users.update');
        Route::post('/users/{id}/impersonate', [AdminController::class, 'impersonateUserApi'])->name('api.settings.users.impersonate');
        Route::get('/users/{id}/owned-records-summary', [AdminController::class, 'getUserOwnedRecordsSummary'])->name('api.settings.users.owned_records_summary');
        Route::post('/users/{id}/offboard', [AdminController::class, 'offboardUser'])->name('api.settings.users.offboard');
        Route::post('/add-seats', [AdminController::class, 'addSeats'])->name('api.settings.add_seats');
    });
});

