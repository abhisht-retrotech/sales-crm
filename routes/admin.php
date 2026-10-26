<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;

/*
|--------------------------------------------------------------------------
| Tenant Administration & Governance Routes
|--------------------------------------------------------------------------
|
| These routes handle all dedicated Admin Console views, configuration studios,
| role & permission governance, security policies, and schema builders.
| Protected by tenant identification, tenant authentication, and tenant.admin.
|
*/

Route::middleware(['web', 'tenant', 'tenant.auth', 'tenant.admin'])
    ->prefix('admin')
    ->group(function () {
        // Admin Dashboard Overview
        Route::get('/', [AdminController::class, 'index'])->name('admin.index');

        // Tenant Studio (Exclusively restricted to Super Admin)
        Route::middleware('super.auth')->group(function () {
            Route::get('/tenant-studio', [AdminController::class, 'tenantStudio'])->name('admin.tenant_studio');
            Route::post('/tenant-studio/provision', [AdminController::class, 'storeTenant'])->name('admin.tenant_studio.store');
            Route::put('/tenant-studio/{tenant}', [AdminController::class, 'updateTenant'])->name('admin.tenant_studio.update');
            Route::delete('/tenant-studio/{tenant}', [AdminController::class, 'destroyTenant'])->name('admin.tenant_studio.destroy');
            Route::post('/tenant-studio/{tenant}/switch', [AdminController::class, 'switchTenant'])->name('admin.tenant_studio.switch');
        });

        // Company Profile & Currency / Email Handshakes
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

        // Users & Team Directory
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

        // Dashboard Studio
        Route::get('/dashboard-studio', [AdminController::class, 'dashboardStudio'])->name('admin.dashboard_studio');
        Route::post('/dashboard-studio/save', [AdminController::class, 'saveDashboardLayout'])->name('admin.dashboard_studio.save');
        Route::post('/dashboard-studio/reset', [AdminController::class, 'resetDashboardLayoutWeb'])->name('admin.dashboard_studio.reset');

        // Roles & Permissions Architecture
        Route::get('/roles-permissions', [AdminController::class, 'rolesPermissions'])->name('admin.roles_permissions');
        Route::get('/roles-permissions/roles', [AdminController::class, 'getRoles'])->name('admin.roles_permissions.roles.index');
        Route::post('/roles-permissions/roles', [AdminController::class, 'createRole'])->name('admin.roles_permissions.roles.store');
        Route::put('/roles-permissions/roles/{id}', [AdminController::class, 'updateRole'])->name('admin.roles_permissions.roles.update');
        Route::delete('/roles-permissions/roles/{id}', [AdminController::class, 'deleteRole'])->name('admin.roles_permissions.roles.destroy');
        Route::get('/roles-permissions/profiles', [AdminController::class, 'getProfiles'])->name('admin.roles_permissions.profiles.index');
        Route::post('/roles-permissions/profiles', [AdminController::class, 'createProfile'])->name('admin.roles_permissions.profiles.store');
        Route::put('/roles-permissions/profiles/{id}', [AdminController::class, 'updateProfile'])->name('admin.roles_permissions.profiles.update');
        Route::delete('/roles-permissions/profiles/{id}', [AdminController::class, 'deleteProfile'])->name('admin.roles_permissions.profiles.destroy');

        // Security Governance & Rights
        Route::get('/record-rights', [AdminController::class, 'recordRights'])->name('admin.record_rights');
        Route::get('/field-rights', [AdminController::class, 'fieldRights'])->name('admin.field_rights');
        Route::get('/sharing-rules', [AdminController::class, 'sharingRules'])->name('admin.sharing_rules');
        Route::get('/security-governance', [AdminController::class, 'securityGovernance'])->name('admin.security_governance');
        Route::post('/security-governance/save', [AdminController::class, 'updateTenantSecurityPolicies'])->name('admin.security_governance.save');
        Route::post('/security-governance/override-seats', [AdminController::class, 'updateTenantSecurityPolicies'])->name('admin.security_governance.override_seats');
        Route::post('/security-governance/add-seats', [AdminController::class, 'purchaseTenantSeats'])->name('admin.security_governance.add_seats');
        Route::get('/security-governance/invoices', [AdminController::class, 'getTenantInvoices'])->name('admin.security_governance.invoices');
        Route::get('/approval-rules', [AdminController::class, 'approvalRules'])->name('admin.approval_rules');
        Route::post('/approval-rules', [AdminController::class, 'storeApprovalRuleApi'])->name('admin.approval_rules.store');
        Route::get('/approval-rules/{id}', [AdminController::class, 'getApprovalRuleApi'])->name('admin.approval_rules.show');
        Route::put('/approval-rules/{id}', [AdminController::class, 'updateApprovalRuleApi'])->name('admin.approval_rules.update');
        Route::delete('/approval-rules/{id}', [AdminController::class, 'deleteApprovalRuleApi'])->name('admin.approval_rules.destroy');
        Route::post('/approval-rules/{id}/toggle', [AdminController::class, 'toggleApprovalRuleApi'])->name('admin.approval_rules.toggle');
        Route::get('/duplicate-rules', [AdminController::class, 'duplicateRules'])->name('admin.duplicate_rules');
        Route::get('/process-studio', [AdminController::class, 'processStudio'])->name('admin.process_studio');

        // Notification Rules
        Route::get('/notification-rules', [AdminController::class, 'notificationRules'])->name('admin.notification_rules');
        Route::post('/notification-rules', [AdminController::class, 'storeNotificationRuleApi'])->name('admin.notification_rules.store');
        Route::put('/notification-rules/{id}', [AdminController::class, 'updateNotificationRuleApi'])->name('admin.notification_rules.update');
        Route::delete('/notification-rules/{id}', [AdminController::class, 'deleteNotificationRuleApi'])->name('admin.notification_rules.destroy');
        Route::post('/notification-rules/{id}/toggle', [AdminController::class, 'toggleNotificationRuleApi'])->name('admin.notification_rules.toggle');

        // Email Templates & Automation Rules
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

        // Document & Billing Templates Studio
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

        // Dynamic Schema & Data Studio
        Route::get('/object-fields', [AdminController::class, 'objectFields'])->name('admin.object_fields');
        Route::get('/custom-objects', [AdminController::class, 'customObjects'])->name('admin.custom_objects');
        Route::get('/page-layouts', [AdminController::class, 'pageLayouts'])->name('admin.page_layouts');
        Route::get('/custom-buttons', [AdminController::class, 'customButtonsStudio'])->name('admin.custom_buttons');
        Route::get('/data-studio', [AdminController::class, 'dataStudio'])->name('admin.data_studio');
        Route::get('/audit-logs', [AdminController::class, 'auditLogs'])->name('admin.audit_logs');
    });
