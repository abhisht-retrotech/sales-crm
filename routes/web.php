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
use App\Http\Controllers\TenantAuthController;
use App\Http\Controllers\CompanyRegistrationController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\CalendarController;

use App\Models\Tenant;
use App\Services\TenantProvisioner;
use Illuminate\Http\Request;

// Super Admin Authentication
Route::get('/super-admin/login', [SuperAdminAuthController::class, 'showLoginForm'])->name('super_admin.login');
Route::post('/super-admin/login', [SuperAdminAuthController::class, 'login'])->name('super_admin.login.submit');
Route::post('/super-admin/logout', [SuperAdminAuthController::class, 'logout'])->name('super_admin.logout');
Route::get('/super-admin', fn () => redirect()->route('super_admin.login'));

// Company Self-Registration & Onboarding Routes
Route::get('/register', [CompanyRegistrationController::class, 'showRegistrationForm'])->name('tenant.register');
Route::post('/register', [CompanyRegistrationController::class, 'register'])->name('tenant.register.submit');
Route::get('/signup', fn () => redirect()->route('tenant.register'))->name('register');

// Tenant Authentication Routes
Route::middleware('tenant')->group(function () {
    Route::get('/login', [TenantAuthController::class, 'showLoginForm'])->name('tenant.login');
    Route::post('/login', [TenantAuthController::class, 'login'])->name('tenant.login.submit');
    Route::post('/logout', [TenantAuthController::class, 'logout'])->name('tenant.logout');
});

// Central / Login Aliases
Route::get('/signin', fn () => redirect()->route('tenant.login'))->name('login');
Route::get('/admin/login', fn () => redirect()->route('tenant.login'));

// Central Super Admin & Tenant Management API (Super Admin Guarded)
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

// All Tenant-isolated CRM Operations & Admin Studio
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

    // Communications & Schedule (React CRM Parity)
    Route::get('/emails', [EmailController::class, 'index'])->name('emails.index');
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    // Core Sales & Operations
    Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');
    Route::get('/leads/{id}', [LeadController::class, 'show'])->name('leads.show');
    Route::prefix('api/leads')->group(function () {
        Route::get('/', [LeadController::class, 'apiList'])->name('api.leads.index');
        Route::get('/meta', [LeadController::class, 'apiMeta'])->name('api.leads.meta');
        Route::post('/', [LeadController::class, 'apiStore'])->name('api.leads.store');
        Route::get('/{id}', [LeadController::class, 'apiShow'])->name('api.leads.show');
        Route::put('/{id}', [LeadController::class, 'apiUpdate'])->name('api.leads.update');
        Route::delete('/{id}', [LeadController::class, 'apiDestroy'])->name('api.leads.destroy');
        Route::post('/{id}/convert', [LeadController::class, 'apiConvert'])->name('api.leads.convert');
        Route::post('/{id}/transfer', [LeadController::class, 'apiTransfer'])->name('api.leads.transfer');
    });
    // Accounts & Master Ledger
    Route::get('/accounts', [AccountController::class, 'index'])->name('accounts.index');
    Route::get('/accounts/{id}', [AccountController::class, 'show'])->name('accounts.show');
    Route::prefix('api/accounts')->group(function () {
        Route::get('/', [AccountController::class, 'apiList'])->name('api.accounts.index');
        Route::get('/meta', [AccountController::class, 'apiMeta'])->name('api.accounts.meta');
        Route::post('/', [AccountController::class, 'apiStore'])->name('api.accounts.store');
        Route::get('/{id}', [AccountController::class, 'apiShow'])->name('api.accounts.show');
        Route::put('/{id}', [AccountController::class, 'apiUpdate'])->name('api.accounts.update');
        Route::delete('/{id}', [AccountController::class, 'apiDestroy'])->name('api.accounts.destroy');
        Route::post('/{id}/transfer', [AccountController::class, 'apiTransfer'])->name('api.accounts.transfer');
        Route::get('/{id}/360', [AccountController::class, 'api360'])->name('api.accounts.360');
    });
    // Contacts Directory & Stakeholders
    Route::get('/contacts', [ContactController::class, 'index'])->name('contacts.index');
    Route::get('/contacts/{id}', [ContactController::class, 'show'])->name('contacts.show');
    Route::prefix('api/contacts')->group(function () {
        Route::get('/', [ContactController::class, 'apiList'])->name('api.contacts.index');
        Route::get('/meta', [ContactController::class, 'apiMeta'])->name('api.contacts.meta');
        Route::post('/', [ContactController::class, 'apiStore'])->name('api.contacts.store');
        Route::get('/{id}', [ContactController::class, 'apiShow'])->name('api.contacts.show');
        Route::put('/{id}', [ContactController::class, 'apiUpdate'])->name('api.contacts.update');
        Route::delete('/{id}', [ContactController::class, 'apiDestroy'])->name('api.contacts.destroy');
        Route::post('/{id}/transfer', [ContactController::class, 'apiTransfer'])->name('api.contacts.transfer');
        Route::get('/{id}/360', [ContactController::class, 'api360'])->name('api.contacts.360');
    });
    // Opportunities & Sales Pipeline
    Route::get('/opportunities', [OpportunityController::class, 'index'])->name('opportunities.index');
    Route::get('/opportunities/{id}', [OpportunityController::class, 'show'])->name('opportunities.show');
    Route::prefix('api/opportunities')->group(function () {
        Route::get('/', [OpportunityController::class, 'apiList'])->name('api.opportunities.index');
        Route::get('/meta', [OpportunityController::class, 'apiMeta'])->name('api.opportunities.meta');
        Route::post('/', [OpportunityController::class, 'apiStore'])->name('api.opportunities.store');
        Route::get('/{id}', [OpportunityController::class, 'apiShow'])->name('api.opportunities.show');
        Route::put('/{id}', [OpportunityController::class, 'apiUpdate'])->name('api.opportunities.update');
        Route::delete('/{id}', [OpportunityController::class, 'apiDestroy'])->name('api.opportunities.destroy');
        Route::post('/{id}/transfer', [OpportunityController::class, 'apiTransfer'])->name('api.opportunities.transfer');
        Route::get('/{id}/360', [OpportunityController::class, 'api360'])->name('api.opportunities.360');
    });
    // Tasks & Action Queue
    Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::get('/tasks/{id}', [TaskController::class, 'show'])->name('tasks.show');
    Route::prefix('api/tasks')->group(function () {
        Route::get('/', [TaskController::class, 'apiList'])->name('api.tasks.index');
        Route::get('/meta', [TaskController::class, 'apiMeta'])->name('api.tasks.meta');
        Route::post('/', [TaskController::class, 'apiStore'])->name('api.tasks.store');
        Route::get('/{id}', [TaskController::class, 'apiShow'])->name('api.tasks.show');
        Route::put('/{id}', [TaskController::class, 'apiUpdate'])->name('api.tasks.update');
        Route::put('/{id}/status', [TaskController::class, 'apiUpdateStatus'])->name('api.tasks.status');
        Route::delete('/{id}', [TaskController::class, 'apiDestroy'])->name('api.tasks.destroy');
        Route::post('/{id}/transfer', [TaskController::class, 'apiTransfer'])->name('api.tasks.transfer');
        Route::get('/{id}/360', [TaskController::class, 'api360'])->name('api.tasks.360');
    });

    // Procurement & Logistics
    Route::get('/purchase-deals', [PurchaseDealController::class, 'index'])->name('purchase_deals.index');
    Route::get('/purchase-deals/{id}', [PurchaseDealController::class, 'show'])->name('purchase_deals.show');
    Route::prefix('api/purchase-deals')->group(function () {
        Route::get('/', [PurchaseDealController::class, 'apiList'])->name('api.purchase_deals.index');
        Route::get('/meta', [PurchaseDealController::class, 'apiMeta'])->name('api.purchase_deals.meta');
        Route::post('/', [PurchaseDealController::class, 'apiStore'])->name('api.purchase_deals.store');
        Route::get('/{id}', [PurchaseDealController::class, 'apiShow'])->name('api.purchase_deals.show');
        Route::put('/{id}', [PurchaseDealController::class, 'apiUpdate'])->name('api.purchase_deals.update');
        Route::put('/{id}/stage', [PurchaseDealController::class, 'apiUpdateStage'])->name('api.purchase_deals.stage');
        Route::delete('/{id}', [PurchaseDealController::class, 'apiDestroy'])->name('api.purchase_deals.destroy');
        Route::post('/{id}/transfer', [PurchaseDealController::class, 'apiTransfer'])->name('api.purchase_deals.transfer');
        Route::get('/{id}/360', [PurchaseDealController::class, 'api360'])->name('api.purchase_deals.360');
    });
    Route::get('/shipping', [ShippingController::class, 'index'])->name('shipping.index');
    Route::get('/shipping/{id}', [ShippingController::class, 'show'])->name('shipping.show');
    Route::prefix('api/shipping')->group(function () {
        Route::get('/', [ShippingController::class, 'apiList'])->name('api.shipping.index');
        Route::get('/meta', [ShippingController::class, 'apiMeta'])->name('api.shipping.meta');
        Route::post('/', [ShippingController::class, 'apiStore'])->name('api.shipping.store');
        Route::get('/{id}', [ShippingController::class, 'apiShow'])->name('api.shipping.show');
        Route::put('/{id}', [ShippingController::class, 'apiUpdate'])->name('api.shipping.update');
        Route::put('/{id}/status', [ShippingController::class, 'apiUpdateStatus'])->name('api.shipping.status');
        Route::delete('/{id}', [ShippingController::class, 'apiDestroy'])->name('api.shipping.destroy');
        Route::post('/{id}/transfer', [ShippingController::class, 'apiTransfer'])->name('api.shipping.transfer');
        Route::get('/{id}/360', [ShippingController::class, 'api360'])->name('api.shipping.360');
    });
    // Alias /api/shipments to shipping API for client compatibility
    Route::prefix('api/shipments')->group(function () {
        Route::get('/', [ShippingController::class, 'apiList']);
        Route::post('/', [ShippingController::class, 'apiStore']);
        Route::get('/{id}', [ShippingController::class, 'apiShow']);
        Route::put('/{id}', [ShippingController::class, 'apiUpdate']);
        Route::put('/{id}/status', [ShippingController::class, 'apiUpdateStatus']);
        Route::delete('/{id}', [ShippingController::class, 'apiDestroy']);
    });

    // Finance & Banking
    Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::get('/documents/{id}', [DocumentController::class, 'show'])->name('documents.show');
    Route::prefix('api/documents')->group(function () {
        Route::get('/', [DocumentController::class, 'apiList'])->name('api.documents.index');
        Route::post('/', [DocumentController::class, 'apiStore'])->name('api.documents.store');
        Route::get('/next-number/{type}', [DocumentController::class, 'apiNextNumber'])->name('api.documents.next_number');
        Route::get('/governance/my-rights', [DocumentController::class, 'apiMyGovernanceRights'])->name('api.documents.governance_rights');
        Route::get('/{id}', [DocumentController::class, 'apiShow'])->name('api.documents.show');
        Route::match(['put', 'patch'], '/{id}/status', [DocumentController::class, 'apiUpdateStatus'])->name('api.documents.status');
        Route::post('/{id}/record-payment', [DocumentController::class, 'apiRecordPayment'])->name('api.documents.record_payment');
        Route::delete('/{id}', [DocumentController::class, 'apiDestroy'])->name('api.documents.destroy');
    });
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/wires', [WireController::class, 'index'])->name('wires.index');

    // Governance & Admin
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::view('/process-studio', 'process_studio')->name('process_studio');

    // Dedicated Admin Console & Rules Modules (Guarded by Tenant Admin & Super Admin)
    Route::prefix('admin')->middleware('tenant.admin')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('admin.index');
        // Tenant Studio (Exclusively restricted to Super Admin)
        Route::middleware('super.auth')->group(function () {
            Route::get('/tenant-studio', [AdminController::class, 'tenantStudio'])->name('admin.tenant_studio');
            Route::post('/tenant-studio/provision', [AdminController::class, 'storeTenant'])->name('admin.tenant_studio.store');
            Route::put('/tenant-studio/{tenant}', [AdminController::class, 'updateTenant'])->name('admin.tenant_studio.update');
            Route::delete('/tenant-studio/{tenant}', [AdminController::class, 'destroyTenant'])->name('admin.tenant_studio.destroy');
            Route::post('/tenant-studio/{tenant}/switch', [AdminController::class, 'switchTenant'])->name('admin.tenant_studio.switch');
        });
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
        Route::post('/notification-rules', [AdminController::class, 'storeNotificationRuleApi'])->name('admin.notification_rules.store');
        Route::put('/notification-rules/{id}', [AdminController::class, 'updateNotificationRuleApi'])->name('admin.notification_rules.update');
        Route::delete('/notification-rules/{id}', [AdminController::class, 'deleteNotificationRuleApi'])->name('admin.notification_rules.destroy');
        Route::post('/notification-rules/{id}/toggle', [AdminController::class, 'toggleNotificationRuleApi'])->name('admin.notification_rules.toggle');

        Route::get('/email-templates', [AdminController::class, 'emailTemplates'])->name('admin.email_templates');
        Route::post('/email-templates', [AdminController::class, 'storeEmailTemplateApi'])->name('admin.email_templates.store');
        Route::get('/email-templates/{id}', [AdminController::class, 'getEmailTemplateApi'])->name('admin.email_templates.show');
        Route::put('/email-templates/{id}', [AdminController::class, 'updateEmailTemplateApi'])->name('admin.email_templates.update');
        Route::delete('/email-templates/{id}', [AdminController::class, 'deleteEmailTemplateApi'])->name('admin.email_templates.destroy');
        Route::post('/email-templates/{id}/clone', [AdminController::class, 'cloneEmailTemplateApi'])->name('admin.email_templates.clone');
        Route::post('/email-templates/{id}/render', [AdminController::class, 'renderEmailTemplateApi'])->name('admin.email_templates.render');
        Route::post('/email-templates/send', [AdminController::class, 'sendEmailApi'])->name('admin.email_templates.send');

        Route::get('/email-templates/rules/all', [AdminController::class, 'getEmailRulesApi'])->name('admin.email_templates.rules.index');
        Route::post('/email-templates/rules', [AdminController::class, 'storeEmailRuleApi'])->name('admin.email_templates.rules.store');
        Route::put('/email-templates/rules/{id}', [AdminController::class, 'updateEmailRuleApi'])->name('admin.email_templates.rules.update');
        Route::delete('/email-templates/rules/{id}', [AdminController::class, 'deleteEmailRuleApi'])->name('admin.email_templates.rules.destroy');
        Route::post('/email-templates/rules/{id}/toggle', [AdminController::class, 'toggleEmailRuleApi'])->name('admin.email_templates.rules.toggle');
        Route::post('/email-templates/rules/evaluate-scheduled', [AdminController::class, 'evaluateScheduledEmailRulesApi'])->name('admin.email_templates.rules.evaluate');

        Route::get('/document-templates', [AdminController::class, 'documentTemplates'])->name('admin.document_templates');
        Route::post('/document-templates', [AdminController::class, 'storeDocumentTemplateApi'])->name('admin.document_templates.store');
        Route::get('/document-templates/governance-rights', [AdminController::class, 'getDocumentGovernanceRightsApi'])->name('admin.document_templates.governance.index');
        Route::put('/document-templates/governance-rights/{profileId}', [AdminController::class, 'updateDocumentGovernanceRightApi'])->name('admin.document_templates.governance.update');
        Route::get('/document-templates/my-governance-rights', [AdminController::class, 'getMyDocumentGovernanceRights'])->name('admin.document_templates.my_governance');
        Route::get('/document-templates/{id}', [AdminController::class, 'getDocumentTemplateApi'])->name('admin.document_templates.show');
        Route::put('/document-templates/{id}', [AdminController::class, 'updateDocumentTemplateApi'])->name('admin.document_templates.update');
        Route::delete('/document-templates/{id}', [AdminController::class, 'deleteDocumentTemplateApi'])->name('admin.document_templates.destroy');
        Route::post('/document-templates/{id}/set-default', [AdminController::class, 'setDefaultDocumentTemplateApi'])->name('admin.document_templates.set_default');
        Route::post('/document-templates/{id}/clone', [AdminController::class, 'cloneDocumentTemplateApi'])->name('admin.document_templates.clone');

        Route::get('/object-fields', [AdminController::class, 'objectFields'])->name('admin.object_fields');
        Route::get('/custom-objects', [AdminController::class, 'customObjects'])->name('admin.custom_objects');
        Route::get('/page-layouts', [AdminController::class, 'pageLayouts'])->name('admin.page_layouts');
        Route::get('/custom-buttons', [AdminController::class, 'customButtonsStudio'])->name('admin.custom_buttons');
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

    // Email Templates Studio API (React Parity)
    Route::prefix('api/email-templates')->group(function () {
        Route::get('/', [AdminController::class, 'getEmailTemplatesApi'])->name('api.email_templates.index');
        Route::post('/', [AdminController::class, 'storeEmailTemplateApi'])->name('api.email_templates.store');
        Route::get('/rules', [AdminController::class, 'getEmailRulesApi'])->name('api.email_templates.rules');
        Route::get('/rules/{id}', [AdminController::class, 'getEmailRuleApi'])->name('api.email_templates.rules.show');
        Route::post('/rules', [AdminController::class, 'storeEmailRuleApi'])->name('api.email_templates.rules.store_api');
        Route::put('/rules/{id}', [AdminController::class, 'updateEmailRuleApi'])->name('api.email_templates.rules.update_api');
        Route::delete('/rules/{id}', [AdminController::class, 'deleteEmailRuleApi'])->name('api.email_templates.rules.destroy_api');
        Route::patch('/rules/{id}/toggle', [AdminController::class, 'toggleEmailRuleApi'])->name('api.email_templates.rules.toggle_api');
        Route::post('/rules/evaluate-scheduled', [AdminController::class, 'evaluateScheduledEmailRulesApi'])->name('api.email_templates.rules.evaluate_api');
        Route::get('/{id}', [AdminController::class, 'getEmailTemplateApi'])->name('api.email_templates.show');
        Route::put('/{id}', [AdminController::class, 'updateEmailTemplateApi'])->name('api.email_templates.update');
        Route::delete('/{id}', [AdminController::class, 'deleteEmailTemplateApi'])->name('api.email_templates.destroy');
        Route::post('/{id}/render', [AdminController::class, 'renderEmailTemplateApi'])->name('api.email_templates.render');
        Route::post('/send', [AdminController::class, 'sendEmailApi'])->name('api.email_templates.send');
    });

    // Document & Billing Templates Studio API (React Parity)
    Route::prefix('api/document-templates')->group(function () {
        Route::get('/', [AdminController::class, 'getDocumentTemplatesApi'])->name('api.document_templates.index');
        Route::post('/', [AdminController::class, 'storeDocumentTemplateApi'])->name('api.document_templates.store');
        Route::get('/governance-rights', [AdminController::class, 'getDocumentGovernanceRightsApi'])->name('api.document_templates.governance.index');
        Route::put('/governance-rights/{profileId}', [AdminController::class, 'updateDocumentGovernanceRightApi'])->name('api.document_templates.governance.update');
        Route::get('/my-governance-rights', [AdminController::class, 'getMyDocumentGovernanceRights'])->name('api.document_templates.my_governance');
        Route::get('/{id}', [AdminController::class, 'getDocumentTemplateApi'])->name('api.document_templates.show');
        Route::put('/{id}', [AdminController::class, 'updateDocumentTemplateApi'])->name('api.document_templates.update');
        Route::delete('/{id}', [AdminController::class, 'deleteDocumentTemplateApi'])->name('api.document_templates.destroy');
        Route::post('/{id}/set-default', [AdminController::class, 'setDefaultDocumentTemplateApi'])->name('api.document_templates.set_default');
        Route::post('/{id}/clone', [AdminController::class, 'cloneDocumentTemplateApi'])->name('api.document_templates.clone');
    });
    Route::get('/api/document-governance-rights', [AdminController::class, 'getDocumentGovernanceRightsApi']);
    Route::put('/api/document-governance-rights/{profileId}', [AdminController::class, 'updateDocumentGovernanceRightApi']);

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

        // Object & Field Manager Studio (React Parity)
        Route::get('/fields', [AdminController::class, 'getCustomFields'])->name('api.settings.fields.index');
        Route::post('/fields', [AdminController::class, 'createCustomField'])->name('api.settings.fields.store');
        Route::get('/fields/{id}', [AdminController::class, 'getCustomField'])->name('api.settings.fields.show');
        Route::put('/fields/{id}', [AdminController::class, 'updateCustomField'])->name('api.settings.fields.update');
        Route::delete('/fields/{id}', [AdminController::class, 'deleteCustomField'])->name('api.settings.fields.destroy');
        Route::get('/standard-fields', [AdminController::class, 'getStandardFieldOverrides'])->name('api.settings.standard_fields.index');
        Route::post('/standard-fields', [AdminController::class, 'saveStandardFieldOverride'])->name('api.settings.standard_fields.store');
    });

    // Global Picklist Value Sets API (React Parity)
    Route::prefix('api/global-picklists')->group(function () {
        Route::get('/', [AdminController::class, 'getGlobalPicklists'])->name('api.global_picklists.index');
        Route::post('/', [AdminController::class, 'createGlobalPicklist'])->name('api.global_picklists.store');
        Route::get('/{id}', [AdminController::class, 'getGlobalPicklist'])->name('api.global_picklists.show');
        Route::put('/{id}', [AdminController::class, 'updateGlobalPicklist'])->name('api.global_picklists.update');
        Route::delete('/{id}', [AdminController::class, 'deleteGlobalPicklist'])->name('api.global_picklists.destroy');
    });

    // Lead Conversion Field Mappings API (React Parity)
    Route::prefix('api/lead-mappings')->group(function () {
        Route::get('/', [AdminController::class, 'getLeadMappings'])->name('api.lead_mappings.index');
        Route::put('/', [AdminController::class, 'saveLeadMappings'])->name('api.lead_mappings.save');
        Route::post('/create-target-field', [AdminController::class, 'createTargetFieldForLead'])->name('api.lead_mappings.create_target_field');
        Route::post('/auto-match', [AdminController::class, 'autoMatchLeadMappings'])->name('api.lead_mappings.auto_match');
    });

    // Enterprise Sharing Rules & Record Security Endpoints (React Parity)
    Route::prefix('api/sharing-rules')->group(function () {
        Route::get('/meta', [AdminController::class, 'getSharingRulesMeta'])->name('api.sharing_rules.meta');
        Route::get('/', [AdminController::class, 'getSharingRules'])->name('api.sharing_rules.index');
        Route::post('/', [AdminController::class, 'createSharingRule'])->name('api.sharing_rules.store');
        Route::put('/{id}', [AdminController::class, 'updateSharingRule'])->name('api.sharing_rules.update');
        Route::delete('/{id}', [AdminController::class, 'deleteSharingRule'])->name('api.sharing_rules.destroy');
        Route::post('/{id}/recalculate', [AdminController::class, 'recalculateSharingRule'])->name('api.sharing_rules.recalculate');
    });

    // Custom Objects & Dynamic Schema API (React Parity)
    Route::prefix('api/custom-objects')->group(function () {
        Route::get('/', [AdminController::class, 'getCustomObjectsApi'])->name('api.custom_objects.index');
        Route::post('/', [AdminController::class, 'createCustomObjectApi'])->name('api.custom_objects.store');
        Route::get('/{id}', [AdminController::class, 'getCustomObjectApi'])->name('api.custom_objects.show');
        Route::delete('/{id}', [AdminController::class, 'deleteCustomObjectApi'])->name('api.custom_objects.destroy');
        Route::get('/{id}/records', [AdminController::class, 'getCustomObjectRecordsApi'])->name('api.custom_objects.records.index');
        Route::post('/{id}/records', [AdminController::class, 'createCustomObjectRecordApi'])->name('api.custom_objects.records.store');
        Route::get('/{id}/records/{recordId}', [AdminController::class, 'getCustomObjectRecordApi'])->name('api.custom_objects.records.show');
        Route::put('/{id}/records/{recordId}', [AdminController::class, 'updateCustomObjectRecordApi'])->name('api.custom_objects.records.update');
        Route::delete('/{id}/records/{recordId}', [AdminController::class, 'deleteCustomObjectRecordApi'])->name('api.custom_objects.records.destroy');
    });

    // Page Layouts & Dynamic Schema Builder API (React Parity)
    Route::prefix('api/page-layouts')->group(function () {
        Route::get('/{module}', [AdminController::class, 'getPageLayoutsApi'])->name('api.page_layouts.index');
        Route::get('/{module}/active', [AdminController::class, 'getActivePageLayoutApi'])->name('api.page_layouts.active');
        Route::get('/{module}/{id}', [AdminController::class, 'getPageLayoutApi'])->name('api.page_layouts.show');
        Route::post('/{module}', [AdminController::class, 'createPageLayoutApi'])->name('api.page_layouts.store');
        Route::put('/{module}/{id}', [AdminController::class, 'updatePageLayoutApi'])->name('api.page_layouts.update');
        Route::delete('/{module}/{id}', [AdminController::class, 'deletePageLayoutApi'])->name('api.page_layouts.destroy');
        Route::post('/{module}/{id}/set-default', [AdminController::class, 'setDefaultPageLayoutApi'])->name('api.page_layouts.set_default');
        Route::post('/{module}/assignments', [AdminController::class, 'savePageLayoutAssignmentsApi'])->name('api.page_layouts.assignments');
    });

    // Field History Tracking Configuration (React Parity)
    Route::prefix('api/field-history')->group(function () {
        Route::get('/config/{module}', [AdminController::class, 'getFieldHistoryConfigApi'])->name('api.field_history.config.show');
        Route::post('/config/{module}', [AdminController::class, 'saveFieldHistoryConfigApi'])->name('api.field_history.config.save');
    });

    // Custom Buttons & Actions Studio API (React Parity)
    Route::prefix('api/custom-buttons')->group(function () {
        Route::get('/', [AdminController::class, 'getCustomButtonsApi'])->name('api.custom_buttons.index');
        Route::post('/', [AdminController::class, 'createCustomButtonApi'])->name('api.custom_buttons.store');
        Route::get('/{id}', [AdminController::class, 'getCustomButtonApi'])->name('api.custom_buttons.show');
        Route::put('/{id}', [AdminController::class, 'updateCustomButtonApi'])->name('api.custom_buttons.update');
        Route::delete('/{id}', [AdminController::class, 'deleteCustomButtonApi'])->name('api.custom_buttons.destroy');
        Route::post('/{id}/execute', [AdminController::class, 'executeCustomButtonApi'])->name('api.custom_buttons.execute');
    });
});
