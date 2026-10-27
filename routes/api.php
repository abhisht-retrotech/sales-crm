<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\OpportunityController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\PurchaseDealController;
use App\Http\Controllers\ShippingController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\WireController;
use App\Http\Controllers\WorkFlowController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\NotificationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application.
| These routes are loaded by Laravel with prefix 'api'.
|
*/

// Public Health & System Status Endpoints
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/status', function () {
    return response()->json([
        'status' => 'online',
        'framework' => 'Laravel ' . app()->version(),
        'app' => 'DealSync Enterprise CRM',
        'timestamp' => now()->toIso8601String(),
    ]);
});

// Authenticated Tenant CRM & Studio API Endpoints
Route::middleware(['web', 'tenant', 'tenant.auth'])->group(function () {

    // ---------------------------------------------------------
    // 1. Leads & Intake API
    // ---------------------------------------------------------
    Route::prefix('leads')->group(function () {
        Route::get('/', [LeadController::class, 'apiList'])->name('api.leads.index');
        Route::get('/meta', [LeadController::class, 'apiMeta'])->name('api.leads.meta');
        Route::post('/', [LeadController::class, 'apiStore'])->name('api.leads.store');
        Route::get('/{id}', [LeadController::class, 'apiShow'])->name('api.leads.show');
        Route::put('/{id}', [LeadController::class, 'apiUpdate'])->name('api.leads.update');
        Route::delete('/{id}', [LeadController::class, 'apiDestroy'])->name('api.leads.destroy');
        Route::post('/{id}/convert', [LeadController::class, 'apiConvert'])->name('api.leads.convert');
        Route::post('/{id}/transfer', [LeadController::class, 'apiTransfer'])->name('api.leads.transfer');
        Route::post('/column-widths', [LeadController::class, 'apiSaveColumnWidths'])->name('api.leads.column_widths');
        Route::post('/view-fields', [LeadController::class, 'apiSaveViewFields'])->name('api.leads.view_fields');
        Route::get('/{id}/activities', [LeadController::class, 'apiGetActivities'])->name('api.leads.activities.index');
        Route::post('/{id}/activities', [LeadController::class, 'apiStoreActivity'])->name('api.leads.activities.store');
        Route::post('/{id}/tasks', [LeadController::class, 'apiStoreTask'])->name('api.leads.tasks.store');
        Route::get('/{id}/files', [LeadController::class, 'apiGetFiles'])->name('api.leads.files.index');
        Route::post('/{id}/files', [LeadController::class, 'apiUploadFiles'])->name('api.leads.files.upload');
        Route::delete('/{id}/files/{fileId}', [LeadController::class, 'apiDeleteFile'])->name('api.leads.files.delete');
        Route::get('/{id}/files/{fileId}/download', [LeadController::class, 'apiDownloadFile'])->name('api.leads.files.download');
        Route::get('/{id}/approvals', [LeadController::class, 'apiGetApprovals'])->name('api.leads.approvals.index');
        Route::post('/{id}/approvals', [LeadController::class, 'apiSubmitApproval'])->name('api.leads.approvals.submit');
        Route::post('/{id}/approvals/{requestId}/action', [LeadController::class, 'apiActionApproval'])->name('api.leads.approvals.action');
        Route::post('/{id}/decide-approval', [LeadController::class, 'apiDecideApproval'])->name('api.leads.decide_approval');
        Route::get('/{id}/history', [LeadController::class, 'apiGetHistory'])->name('api.leads.history.index');
    });

    // ---------------------------------------------------------
    // 2. Accounts & Master Ledger API
    // ---------------------------------------------------------
    Route::prefix('accounts')->group(function () {
        Route::get('/', [AccountController::class, 'apiList'])->name('api.accounts.index');
        Route::get('/meta', [AccountController::class, 'apiMeta'])->name('api.accounts.meta');
        Route::post('/', [AccountController::class, 'apiStore'])->name('api.accounts.store');
        Route::get('/{id}', [AccountController::class, 'apiShow'])->name('api.accounts.show');
        Route::put('/{id}', [AccountController::class, 'apiUpdate'])->name('api.accounts.update');
        Route::delete('/{id}', [AccountController::class, 'apiDestroy'])->name('api.accounts.destroy');
        Route::post('/{id}/transfer', [AccountController::class, 'apiTransfer'])->name('api.accounts.transfer');
        Route::get('/{id}/360', [AccountController::class, 'api360'])->name('api.accounts.360');
        Route::get('/{id}/files', [AccountController::class, 'apiGetFiles'])->name('api.accounts.files.index');
        Route::post('/{id}/files', [AccountController::class, 'apiUploadFiles'])->name('api.accounts.files.upload');
        Route::delete('/{id}/files/{fileId}', [AccountController::class, 'apiDeleteFile'])->name('api.accounts.files.delete');
        Route::get('/{id}/files/{fileId}/download', [AccountController::class, 'apiDownloadFile'])->name('api.accounts.files.download');
        Route::get('/{id}/activities', [AccountController::class, 'apiGetActivities'])->name('api.accounts.activities.index');
        Route::post('/{id}/activities', [AccountController::class, 'apiStoreActivity'])->name('api.accounts.activities.store');
        Route::post('/{id}/tasks', [AccountController::class, 'apiStoreTask'])->name('api.accounts.tasks.store');
    });

    // ---------------------------------------------------------
    // 3. Contacts Directory & Stakeholders API
    // ---------------------------------------------------------
    Route::prefix('contacts')->group(function () {
        Route::get('/', [ContactController::class, 'apiList'])->name('api.contacts.index');
        Route::get('/meta', [ContactController::class, 'apiMeta'])->name('api.contacts.meta');
        Route::post('/', [ContactController::class, 'apiStore'])->name('api.contacts.store');
        Route::get('/{id}', [ContactController::class, 'apiShow'])->name('api.contacts.show');
        Route::put('/{id}', [ContactController::class, 'apiUpdate'])->name('api.contacts.update');
        Route::delete('/{id}', [ContactController::class, 'apiDestroy'])->name('api.contacts.destroy');
        Route::post('/{id}/transfer', [ContactController::class, 'apiTransfer'])->name('api.contacts.transfer');
        Route::get('/{id}/360', [ContactController::class, 'api360'])->name('api.contacts.360');
        Route::get('/{id}/files', [ContactController::class, 'apiGetFiles'])->name('api.contacts.files.index');
        Route::post('/{id}/files', [ContactController::class, 'apiUploadFiles'])->name('api.contacts.files.upload');
        Route::delete('/{id}/files/{fileId}', [ContactController::class, 'apiDeleteFile'])->name('api.contacts.files.delete');
        Route::get('/{id}/files/{fileId}/download', [ContactController::class, 'apiDownloadFile'])->name('api.contacts.files.download');
        Route::get('/{id}/activities', [ContactController::class, 'apiGetActivities'])->name('api.contacts.activities.index');
        Route::post('/{id}/activities', [ContactController::class, 'apiStoreActivity'])->name('api.contacts.activities.store');
        Route::post('/{id}/tasks', [ContactController::class, 'apiStoreTask'])->name('api.contacts.tasks.store');
    });

    // ---------------------------------------------------------
    // 4. Opportunities & Pipeline API
    // ---------------------------------------------------------
    Route::prefix('opportunities')->group(function () {
        Route::get('/', [OpportunityController::class, 'apiList'])->name('api.opportunities.index');
        Route::get('/meta', [OpportunityController::class, 'apiMeta'])->name('api.opportunities.meta');
        Route::post('/', [OpportunityController::class, 'apiStore'])->name('api.opportunities.store');
        Route::get('/{id}', [OpportunityController::class, 'apiShow'])->name('api.opportunities.show');
        Route::put('/{id}', [OpportunityController::class, 'apiUpdate'])->name('api.opportunities.update');
        Route::delete('/{id}', [OpportunityController::class, 'apiDestroy'])->name('api.opportunities.destroy');
        Route::post('/{id}/transfer', [OpportunityController::class, 'apiTransfer'])->name('api.opportunities.transfer');
        Route::get('/{id}/360', [OpportunityController::class, 'api360'])->name('api.opportunities.360');
    });

    // ---------------------------------------------------------
    // 5. Tasks & Action Queue API
    // ---------------------------------------------------------
    Route::prefix('tasks')->group(function () {
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

    // ---------------------------------------------------------
    // 6. Procurement & Logistics API
    // ---------------------------------------------------------
    Route::prefix('purchase-deals')->group(function () {
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

    Route::prefix('shipping')->group(function () {
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

    // Alias /api/shipments for client compatibility
    Route::prefix('shipments')->group(function () {
        Route::get('/', [ShippingController::class, 'apiList']);
        Route::post('/', [ShippingController::class, 'apiStore']);
        Route::get('/{id}', [ShippingController::class, 'apiShow']);
        Route::put('/{id}', [ShippingController::class, 'apiUpdate']);
        Route::put('/{id}/status', [ShippingController::class, 'apiUpdateStatus']);
        Route::delete('/{id}', [ShippingController::class, 'apiDestroy']);
    });

    // ---------------------------------------------------------
    // 7. Finance, Banking & Documents API
    // ---------------------------------------------------------
    Route::prefix('documents')->group(function () {
        Route::get('/', [DocumentController::class, 'apiList'])->name('api.documents.index');
        Route::post('/', [DocumentController::class, 'apiStore'])->name('api.documents.store');
        Route::get('/next-number/{type}', [DocumentController::class, 'apiNextNumber'])->name('api.documents.next_number');
        Route::get('/governance/my-rights', [DocumentController::class, 'apiMyGovernanceRights'])->name('api.documents.governance_rights');
        Route::get('/{id}', [DocumentController::class, 'apiShow'])->name('api.documents.show');
        Route::match(['put', 'patch'], '/{id}/status', [DocumentController::class, 'apiUpdateStatus'])->name('api.documents.status');
        Route::post('/{id}/record-payment', [DocumentController::class, 'apiRecordPayment'])->name('api.documents.record_payment');
        Route::delete('/{id}', [DocumentController::class, 'apiDestroy'])->name('api.documents.destroy');
    });

    Route::prefix('payments')->group(function () {
        Route::get('/', [PaymentController::class, 'apiList'])->name('api.payments.index');
        Route::post('/', [PaymentController::class, 'apiStore'])->name('api.payments.store');
        Route::get('/{id}', [PaymentController::class, 'apiShow'])->name('api.payments.show');
        Route::put('/{id}', [PaymentController::class, 'apiUpdate'])->name('api.payments.update');
        Route::put('/{id}/status', [PaymentController::class, 'apiUpdateStatus'])->name('api.payments.status');
        Route::post('/{id}/record-payment', [PaymentController::class, 'apiRecordPayment'])->name('api.payments.record_payment');
        Route::delete('/{id}', [PaymentController::class, 'apiDestroy'])->name('api.payments.destroy');
    });

    Route::prefix('wires')->group(function () {
        Route::get('/', [WireController::class, 'apiList'])->name('api.wires.index');
        Route::post('/', [WireController::class, 'apiStore'])->name('api.wires.store');
        Route::get('/{id}', [WireController::class, 'apiShow'])->name('api.wires.show');
        Route::put('/{id}', [WireController::class, 'apiUpdate'])->name('api.wires.update');
        Route::put('/{id}/status', [WireController::class, 'apiUpdateStatus'])->name('api.wires.status');
        Route::post('/{id}/reconcile', [WireController::class, 'apiReconcile'])->name('api.wires.reconcile');
        Route::delete('/{id}', [WireController::class, 'apiDestroy'])->name('api.wires.destroy');
    });

    // ---------------------------------------------------------
    // 8. Workflows & Process Studio API
    // ---------------------------------------------------------
    Route::prefix('flows')->group(function () {
        Route::get('/', [WorkFlowController::class, 'index'])->name('api.workflow.index');
        Route::post('/', [WorkFlowController::class, 'store'])->name('api.workflow.store');
        Route::get('/{id}', [WorkFlowController::class, 'show'])->name('api.workflow.show');
        Route::delete('/{id}', [WorkFlowController::class, 'destroy'])->name('api.workflow.destroy');
    });

    // ---------------------------------------------------------
    // 9. Analytics & Dashboard Layouts API
    // ---------------------------------------------------------
    Route::prefix('analytics')->group(function () {
        Route::get('/dashboard-layouts', [AdminController::class, 'getDashboardLayouts'])->name('api.dashboard_layouts.index');
        Route::get('/dashboard-layouts/{profileId}', [AdminController::class, 'getDashboardLayout'])->name('api.dashboard_layouts.show');
        Route::post('/dashboard-layouts', [AdminController::class, 'saveDashboardLayout'])->name('api.dashboard_layouts.store');
        Route::delete('/dashboard-layouts/{profileId}', [AdminController::class, 'resetDashboardLayout'])->name('api.dashboard_layouts.destroy');
    });

    // ---------------------------------------------------------
    // 10. Email Templates & Rules API
    // ---------------------------------------------------------
    Route::prefix('email-templates')->group(function () {
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

    // ---------------------------------------------------------
    // 11. Document & Billing Templates API
    // ---------------------------------------------------------
    Route::prefix('document-templates')->group(function () {
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
    Route::get('/document-governance-rights', [AdminController::class, 'getDocumentGovernanceRightsApi']);
    Route::put('/document-governance-rights/{profileId}', [AdminController::class, 'updateDocumentGovernanceRightApi']);

    // ---------------------------------------------------------
    // 12. Tenant Profile, Currencies & Subscriptions API
    // ---------------------------------------------------------
    Route::prefix('tenants')->group(function () {
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
        // Security Policies & Subscription Governance
        Route::get('/{tenant}/security-policies', [AdminController::class, 'getTenantSecurityPolicies'])->name('api.tenants.security_policies.show');
        Route::put('/{tenant}/security-policies', [AdminController::class, 'updateTenantSecurityPolicies'])->name('api.tenants.security_policies.update');
        Route::get('/{tenant}/subscription', [AdminController::class, 'getTenantSubscription'])->name('api.tenants.subscription.show');
        Route::post('/{tenant}/subscription/add-seats', [AdminController::class, 'purchaseTenantSeats'])->name('api.tenants.subscription.add_seats');
        Route::get('/{tenant}/subscription/invoices', [AdminController::class, 'getTenantInvoices'])->name('api.tenants.subscription.invoices');
    });

    // ---------------------------------------------------------
    // 13. Settings: Roles, Profiles, Permissions, Users & Fields API
    // ---------------------------------------------------------
    Route::prefix('settings')->group(function () {
        // Roles & Permissions Architecture
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

        // Object & Field Manager Studio
        Route::get('/fields', [AdminController::class, 'getCustomFields'])->name('api.settings.fields.index');
        Route::post('/fields', [AdminController::class, 'createCustomField'])->name('api.settings.fields.store');
        Route::get('/fields/{id}', [AdminController::class, 'getCustomField'])->name('api.settings.fields.show');
        Route::put('/fields/{id}', [AdminController::class, 'updateCustomField'])->name('api.settings.fields.update');
        Route::delete('/fields/{id}', [AdminController::class, 'deleteCustomField'])->name('api.settings.fields.destroy');
        Route::get('/standard-fields', [AdminController::class, 'getStandardFieldOverrides'])->name('api.settings.standard_fields.index');
        Route::post('/standard-fields', [AdminController::class, 'saveStandardFieldOverride'])->name('api.settings.standard_fields.store');
    });

    // ---------------------------------------------------------
    // 14. Global Picklists API
    // ---------------------------------------------------------
    Route::prefix('global-picklists')->group(function () {
        Route::get('/', [AdminController::class, 'getGlobalPicklists'])->name('api.global_picklists.index');
        Route::post('/', [AdminController::class, 'createGlobalPicklist'])->name('api.global_picklists.store');
        Route::get('/{id}', [AdminController::class, 'getGlobalPicklist'])->name('api.global_picklists.show');
        Route::put('/{id}', [AdminController::class, 'updateGlobalPicklist'])->name('api.global_picklists.update');
        Route::delete('/{id}', [AdminController::class, 'deleteGlobalPicklist'])->name('api.global_picklists.destroy');
    });

    // ---------------------------------------------------------
    // 15. Lead Conversion Mappings API
    // ---------------------------------------------------------
    Route::prefix('lead-mappings')->group(function () {
        Route::get('/', [AdminController::class, 'getLeadMappings'])->name('api.lead_mappings.index');
        Route::put('/', [AdminController::class, 'saveLeadMappings'])->name('api.lead_mappings.save');
        Route::post('/create-target-field', [AdminController::class, 'createTargetFieldForLead'])->name('api.lead_mappings.create_target_field');
        Route::post('/auto-match', [AdminController::class, 'autoMatchLeadMappings'])->name('api.lead_mappings.auto_match');
    });

    // ---------------------------------------------------------
    // 16. Sharing Rules & Record Security API
    // ---------------------------------------------------------
    Route::prefix('sharing-rules')->group(function () {
        Route::get('/meta', [AdminController::class, 'getSharingRulesMeta'])->name('api.sharing_rules.meta');
        Route::get('/', [AdminController::class, 'getSharingRules'])->name('api.sharing_rules.index');
        Route::post('/', [AdminController::class, 'createSharingRule'])->name('api.sharing_rules.store');
        Route::put('/{id}', [AdminController::class, 'updateSharingRule'])->name('api.sharing_rules.update');
        Route::delete('/{id}', [AdminController::class, 'deleteSharingRule'])->name('api.sharing_rules.destroy');
        Route::post('/{id}/recalculate', [AdminController::class, 'recalculateSharingRule'])->name('api.sharing_rules.recalculate');
    });

    // ---------------------------------------------------------
    // 17. Custom Objects & Dynamic Records API
    // ---------------------------------------------------------
    Route::prefix('custom-objects')->group(function () {
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

    // ---------------------------------------------------------
    // 18. Dynamic Page Layouts API
    // ---------------------------------------------------------
    Route::prefix('page-layouts')->group(function () {
        Route::get('/{module}', [AdminController::class, 'getPageLayoutsApi'])->name('api.page_layouts.index');
        Route::get('/{module}/active', [AdminController::class, 'getActivePageLayoutApi'])->name('api.page_layouts.active');
        Route::get('/{module}/{id}', [AdminController::class, 'getPageLayoutApi'])->name('api.page_layouts.show');
        Route::post('/{module}', [AdminController::class, 'createPageLayoutApi'])->name('api.page_layouts.store');
        Route::put('/{module}/{id}', [AdminController::class, 'updatePageLayoutApi'])->name('api.page_layouts.update');
        Route::delete('/{module}/{id}', [AdminController::class, 'deletePageLayoutApi'])->name('api.page_layouts.destroy');
        Route::post('/{module}/{id}/set-default', [AdminController::class, 'setDefaultPageLayoutApi'])->name('api.page_layouts.set_default');
        Route::post('/{module}/assignments', [AdminController::class, 'savePageLayoutAssignmentsApi'])->name('api.page_layouts.assignments');
    });

    // ---------------------------------------------------------
    // 19. Field History Tracking Configuration API
    // ---------------------------------------------------------
    Route::prefix('field-history')->group(function () {
        Route::get('/config/{module}', [AdminController::class, 'getFieldHistoryConfigApi'])->name('api.field_history.config.show');
        Route::post('/config/{module}', [AdminController::class, 'saveFieldHistoryConfigApi'])->name('api.field_history.config.save');
    });

    // ---------------------------------------------------------
    // 20. Custom Buttons & Automated Actions Studio API
    // ---------------------------------------------------------
    Route::prefix('custom-buttons')->group(function () {
        Route::get('/', [AdminController::class, 'getCustomButtonsApi'])->name('api.custom_buttons.index');
        Route::post('/', [AdminController::class, 'createCustomButtonApi'])->name('api.custom_buttons.store');
        Route::get('/{id}', [AdminController::class, 'getCustomButtonApi'])->name('api.custom_buttons.show');
        Route::put('/{id}', [AdminController::class, 'updateCustomButtonApi'])->name('api.custom_buttons.update');
        Route::delete('/{id}', [AdminController::class, 'deleteCustomButtonApi'])->name('api.custom_buttons.destroy');
        Route::post('/{id}/execute', [AdminController::class, 'executeCustomButtonApi'])->name('api.custom_buttons.execute');
    });

    // ---------------------------------------------------------
    // 21. User Notifications & In-App Alerts API
    // ---------------------------------------------------------
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'apiList'])->name('api.notifications.index');
        Route::post('/{id}/read', [NotificationController::class, 'apiMarkRead'])->name('api.notifications.read');
        Route::post('/mark-all-read', [NotificationController::class, 'apiMarkAllRead'])->name('api.notifications.mark_all_read');
    });
});
