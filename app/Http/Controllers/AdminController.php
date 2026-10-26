<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Models\Profile;
use App\Models\Role;
use App\Services\Admin\AdminRoleProfileService;
use App\Services\Admin\AdminNotificationService;
use App\Services\Admin\AdminEmailTemplateService;
use App\Services\Admin\AdminDocumentTemplateService;
use App\Services\Admin\AdminCustomFieldService;
use App\Services\Admin\AdminCustomObjectService;
use App\Services\Admin\AdminTenantTrait;
use App\Services\TenantProvisioner;
class AdminController extends Controller
{
    use AdminTenantTrait;

    protected AdminRoleProfileService $roleProfileService;
    protected AdminNotificationService $notificationService;
    protected AdminEmailTemplateService $emailTemplateService;
    protected AdminDocumentTemplateService $documentTemplateService;
    protected AdminCustomFieldService $customFieldService;
    protected AdminCustomObjectService $customObjectService;

    public function __construct(
        ?AdminRoleProfileService $roleProfileService = null,
        ?AdminNotificationService $notificationService = null,
        ?AdminEmailTemplateService $emailTemplateService = null,
        ?AdminDocumentTemplateService $documentTemplateService = null,
        ?AdminCustomFieldService $customFieldService = null,
        ?AdminCustomObjectService $customObjectService = null
    ) {
        $this->roleProfileService = $roleProfileService ?? app(AdminRoleProfileService::class);
        $this->notificationService = $notificationService ?? app(AdminNotificationService::class);
        $this->emailTemplateService = $emailTemplateService ?? app(AdminEmailTemplateService::class);
        $this->documentTemplateService = $documentTemplateService ?? app(AdminDocumentTemplateService::class);
        $this->customFieldService = $customFieldService ?? app(AdminCustomFieldService::class);
        $this->customObjectService = $customObjectService ?? app(AdminCustomObjectService::class);
    }

    public static array $STANDARD_MODULES = [
        'leads'          => 'Leads',
        'accounts'       => 'Accounts',
        'contacts'       => 'Contacts',
        'opportunities'  => 'Opportunities',
        'purchase_deals' => 'Purchase Deals',
        'shipping'       => 'Shipping',
        'payments'       => 'Payments',
        'wires'          => 'Wires Received',
        'documents'      => 'Documents',
        'tasks'          => 'Tasks',
        'users'          => 'Users',
    ];

    public static array $SENSITIVE_FIELDS = [
        'opportunities.amount',
        'opportunities.probability',
        'purchase_deals.amount',
        'accounts.credit_limit',
        'accounts.annual_revenue',
        'contacts.phone',
        'wires.amount',
        'users.email',
        'leads.email',
        'payments.amount',
    ];

    public static array $MODULE_OPTIONS = [
        ['id' => 'dashboard', 'name' => 'Dashboard', 'category' => 'core', 'desc' => 'Executive KPIs, analytics charts, and operational widgets'],
        ['id' => 'leads', 'name' => 'Leads', 'category' => 'core', 'desc' => 'Inbound prospects, qualification stages, and conversion engine'],
        ['id' => 'accounts', 'name' => 'Accounts', 'category' => 'core', 'desc' => 'Companies, organizations, customers, and vendor partners'],
        ['id' => 'contacts', 'name' => 'Contacts', 'category' => 'core', 'desc' => 'Individual stakeholder contacts and communication directory'],
        ['id' => 'opportunities', 'name' => 'Opportunities (Sales Pipeline)', 'category' => 'core', 'desc' => 'Sales pipeline, forecast stages, and deal values'],
        ['id' => 'tasks', 'name' => 'Tasks', 'category' => 'core', 'desc' => 'Action items, reminders, follow-ups, and recurring tasks'],
        ['id' => 'emails', 'name' => 'Emails', 'category' => 'core', 'desc' => 'Unified inbox, compose, templates, and outbound correspondence'],
        ['id' => 'calendar', 'name' => 'Calendar', 'category' => 'core', 'desc' => 'Meetings, customer bookings, sync feeds, and scheduling'],
        ['id' => 'purchase_deals', 'name' => 'Deals (Purchase & Procurement)', 'category' => 'extended', 'desc' => 'Procurement orders, vendor agreements, and PO commitments'],
        ['id' => 'shipping', 'name' => 'Shipping & Logistics', 'category' => 'extended', 'desc' => 'Freight tracking, carriers, and dispatch logistics'],
        ['id' => 'documents', 'name' => 'Billing & Ledger', 'category' => 'extended', 'desc' => 'Financial invoices, payment receipts, and ledger audit'],
        ['id' => 'payments', 'name' => 'Payments & Invoicing (AR / AP)', 'category' => 'extended', 'desc' => 'Billing records, accounts receivable, and collections'],
        ['id' => 'wires', 'name' => 'Wire Received & Bank Reconciliation', 'category' => 'extended', 'desc' => 'Bank wire transfers, UTR confirmations, and reconciliation'],
        ['id' => 'users', 'name' => 'Users (Active User Directory)', 'category' => 'extended', 'desc' => 'System user directory and employee profile management'],
    ];

    public static array $DATA_TYPES = [
        ['value' => 'Text', 'label' => 'Text (Single Line)'],
        ['value' => 'Textarea', 'label' => 'Textarea (Multi-Line)'],
        ['value' => 'Number', 'label' => 'Number (Numeric / Integer / Decimal)'],
        ['value' => 'Currency', 'label' => 'Currency ($ USD)'],
        ['value' => 'Date', 'label' => 'Date (Calendar Selector)'],
        ['value' => 'Time', 'label' => 'Time (Clock Selector HH:MM)'],
        ['value' => 'DateTime', 'label' => 'Date & Time (Calendar & Clock Selector)'],
        ['value' => 'Dropdown', 'label' => 'Dropdown / Picklist (Single Select)'],
        ['value' => 'MultiSelect', 'label' => 'Multi-Select Picklist (Select Multiple Values)'],
        ['value' => 'Boolean', 'label' => 'Boolean (Checkbox Yes/No)'],
        ['value' => 'Email', 'label' => 'Email Address'],
        ['value' => 'Phone', 'label' => 'Phone Number'],
        ['value' => 'URL', 'label' => 'URL / Web Link'],
        ['value' => 'Percentage', 'label' => '% Percentage (Tax, Margin, Commission)'],
        ['value' => 'Radio', 'label' => '🔘 Radio Buttons (Single Choice Button Group)'],
        ['value' => 'AutoNumber', 'label' => '🔢 Auto Number (Sequential Unique Tracking ID)'],
        ['value' => 'RollupSummary', 'label' => '∑ Rollup Summary (Child Object Aggregation)'],
        ['value' => 'Rating', 'label' => '★ Rating (Visual Score 1–5 / 1–10)'],
        ['value' => 'MaskedText', 'label' => '🔒 Masked / Encrypted Text (SSN, Bank, Tax ID)'],
        ['value' => 'ColorPicker', 'label' => '🎨 Color Picker / Status Badge'],
        ['value' => 'RichText', 'label' => '📝 Rich Text (WYSIWYG HTML Formatted)'],
    ];

    public static array $STANDARD_FIELDS = [
        'leads' => [
            ['name' => 'first_name', 'label' => 'First Name', 'type' => 'Text', 'required' => true, 'description' => 'Lead first name'],
            ['name' => 'last_name', 'label' => 'Last Name', 'type' => 'Text', 'required' => true, 'description' => 'Lead last name'],
            ['name' => 'company', 'label' => 'Company Name', 'type' => 'Text', 'required' => true, 'description' => 'Lead company or business'],
            ['name' => 'title', 'label' => 'Job Title', 'type' => 'Text', 'required' => false, 'description' => 'Job designation'],
            ['name' => 'email', 'label' => 'Email Address', 'type' => 'Email', 'required' => true, 'description' => 'Primary business email'],
            ['name' => 'phone', 'label' => 'Phone Number', 'type' => 'Phone', 'required' => false, 'description' => 'Direct contact phone'],
            ['name' => 'status', 'label' => 'Status', 'type' => 'Dropdown', 'required' => true, 'description' => 'Lead qualification status'],
            ['name' => 'score', 'label' => 'Lead Score', 'type' => 'Number', 'required' => false, 'description' => '0-100 propensity score'],
            ['name' => 'source', 'label' => 'Lead Source', 'type' => 'Dropdown', 'required' => false, 'description' => 'Inbound marketing channel'],
            ['name' => 'assigned_to', 'label' => 'Assigned To', 'type' => 'Text', 'required' => false, 'description' => 'Sales rep user assignment'],
        ],
        'accounts' => [
            ['name' => 'name', 'label' => 'Account Name', 'type' => 'Text', 'required' => true, 'description' => 'Legal or operating entity name'],
            ['name' => 'type', 'label' => 'Account Type', 'type' => 'Dropdown', 'required' => true, 'description' => 'Customer, Vendor, Prospect, Partner'],
            ['name' => 'industry', 'label' => 'Industry', 'type' => 'Text', 'required' => false, 'description' => 'Business vertical or sector'],
            ['name' => 'website', 'label' => 'Website URL', 'type' => 'URL', 'required' => false, 'description' => 'Corporate website'],
            ['name' => 'phone', 'label' => 'Phone', 'type' => 'Phone', 'required' => false, 'description' => 'Corporate telephone'],
            ['name' => 'billing_address', 'label' => 'Billing Address', 'type' => 'Textarea', 'required' => false, 'description' => 'Headquarters billing location'],
            ['name' => 'shipping_address', 'label' => 'Shipping Address', 'type' => 'Textarea', 'required' => false, 'description' => 'Primary warehouse / receiving location'],
            ['name' => 'payment_terms', 'label' => 'Payment Terms', 'type' => 'Dropdown', 'required' => false, 'description' => 'Net 15, Net 30, Net 60, Advance Wire'],
            ['name' => 'credit_limit', 'label' => 'Credit Limit', 'type' => 'Currency', 'required' => false, 'description' => 'Max credit allowance in USD'],
            ['name' => 'annual_revenue', 'label' => 'Annual Revenue', 'type' => 'Currency', 'required' => false, 'description' => 'Annual turnover in USD'],
        ],
        'contacts' => [
            ['name' => 'first_name', 'label' => 'First Name', 'type' => 'Text', 'required' => true, 'description' => 'Stakeholder first name'],
            ['name' => 'last_name', 'label' => 'Last Name', 'type' => 'Text', 'required' => true, 'description' => 'Stakeholder last name'],
            ['name' => 'account_id', 'label' => 'Account (Company)', 'type' => 'Lookup', 'required' => false, 'description' => 'Associated company'],
            ['name' => 'title', 'label' => 'Job Title', 'type' => 'Text', 'required' => false, 'description' => 'Role / Designation'],
            ['name' => 'department', 'label' => 'Department', 'type' => 'Text', 'required' => false, 'description' => 'Functional department'],
            ['name' => 'email', 'label' => 'Email', 'type' => 'Email', 'required' => true, 'description' => 'Direct stakeholder email'],
            ['name' => 'phone', 'label' => 'Phone', 'type' => 'Phone', 'required' => false, 'description' => 'Direct phone number'],
            ['name' => 'contact_role', 'label' => 'Buying Role', 'type' => 'Dropdown', 'required' => false, 'description' => 'Decision Maker, Champion, Influencer, Evaluator'],
            ['name' => 'is_primary', 'label' => 'Primary Contact', 'type' => 'Boolean', 'required' => false, 'description' => 'Key corporate contact flag'],
        ],
        'opportunities' => [
            ['name' => 'name', 'label' => 'Opportunity Name', 'type' => 'Text', 'required' => true, 'description' => 'Deal title / project identifier'],
            ['name' => 'account_id', 'label' => 'Customer Account', 'type' => 'Dropdown', 'required' => true, 'description' => 'Client purchasing the deal'],
            ['name' => 'purchase_deal_id', 'label' => 'Active Procurement Deal (PO)', 'type' => 'Dropdown', 'required' => true, 'description' => 'Required PO deal from which this opportunity is created'],
            ['name' => 'stage', 'label' => 'Pipeline Stage', 'type' => 'Dropdown', 'required' => true, 'description' => 'Discovery, Proposal, Negotiation, Closed Won, Closed Lost'],
            ['name' => 'amount', 'label' => 'Deal Amount', 'type' => 'Currency', 'required' => true, 'description' => 'Expected deal value in USD'],
            ['name' => 'probability', 'label' => 'Win Probability (%)', 'type' => 'Number', 'required' => false, 'description' => 'Forecast weighting 0-100%'],
            ['name' => 'expected_close_date', 'label' => 'Target Close Date', 'type' => 'Date', 'required' => false, 'description' => 'Forecast closing calendar date'],
        ],
        'purchase_deals' => [
            ['name' => 'po_number', 'label' => 'PO Number', 'type' => 'Text', 'required' => true, 'description' => 'Unique Purchase Order reference'],
            ['name' => 'title', 'label' => 'PO Title / Description', 'type' => 'Text', 'required' => true, 'description' => 'Procurement order summary'],
            ['name' => 'vendor_id', 'label' => 'Supplier / Vendor', 'type' => 'Dropdown', 'required' => true, 'description' => 'Supplying vendor account'],
            ['name' => 'amount', 'label' => 'PO Total Spend', 'type' => 'Currency', 'required' => true, 'description' => 'Procurement commitment amount'],
            ['name' => 'stage', 'label' => 'Procurement Stage', 'type' => 'Dropdown', 'required' => true, 'description' => 'Draft, RFQ Sent, PO Issued, Confirmed, Completed'],
            ['name' => 'expected_delivery_date', 'label' => 'Target Delivery Date', 'type' => 'Date', 'required' => false, 'description' => 'Supplier promised delivery date'],
            ['name' => 'payment_terms', 'label' => 'Payment Terms', 'type' => 'Dropdown', 'required' => false, 'description' => 'Vendor payment terms'],
        ],
        'shipping' => [
            ['name' => 'tracking_number', 'label' => 'Tracking Number', 'type' => 'Text', 'required' => true, 'description' => 'Carrier tracking code'],
            ['name' => 'carrier', 'label' => 'Freight Carrier', 'type' => 'Dropdown', 'required' => true, 'description' => 'FedEx, UPS, DHL, Maersk, Linehaul'],
            ['name' => 'type', 'label' => 'Shipment Type', 'type' => 'Dropdown', 'required' => true, 'description' => 'Outbound (Sales) or Inbound (Procurement)'],
            ['name' => 'status', 'label' => 'Logistics Status', 'type' => 'Dropdown', 'required' => true, 'description' => 'Pending, In Transit, Customs Clearance, Delivered'],
            ['name' => 'account_id', 'label' => 'Associated Account', 'type' => 'Dropdown', 'required' => true, 'description' => 'Linked customer or vendor account'],
            ['name' => 'shipping_origin', 'label' => 'Origin / Dispatch Facility', 'type' => 'Text', 'required' => false, 'description' => 'Dispatch warehouse or supplier dock'],
            ['name' => 'destination_address', 'label' => 'Destination Address', 'type' => 'Textarea', 'required' => false, 'description' => 'Delivery facility or client site'],
            ['name' => 'estimated_delivery_date', 'label' => 'Est. Delivery Date', 'type' => 'Date', 'required' => false, 'description' => 'Estimated arrival calendar date'],
            ['name' => 'notes', 'label' => 'Shipping Notes', 'type' => 'Textarea', 'required' => false, 'description' => 'Special cargo or delivery instructions'],
        ],
        'payments' => [
            ['name' => 'invoice_number', 'label' => 'Invoice Number', 'type' => 'Text', 'required' => true, 'description' => 'Legal billing invoice identifier'],
            ['name' => 'account_id', 'label' => 'Billed Account', 'type' => 'Dropdown', 'required' => true, 'description' => 'Customer or Vendor'],
            ['name' => 'type', 'label' => 'Invoice Category', 'type' => 'Dropdown', 'required' => true, 'description' => 'Accounts Receivable (AR) or Accounts Payable (AP)'],
            ['name' => 'amount', 'label' => 'Invoice Amount', 'type' => 'Currency', 'required' => true, 'description' => 'Total invoice billing amount'],
            ['name' => 'paid_amount', 'label' => 'Paid / Settled Amount', 'type' => 'Currency', 'required' => false, 'description' => 'Amount collected / settled to date'],
            ['name' => 'due_date', 'label' => 'Payment Due Date', 'type' => 'Date', 'required' => true, 'description' => 'Net terms maturity date'],
            ['name' => 'status', 'label' => 'Payment Status', 'type' => 'Dropdown', 'required' => true, 'description' => 'Draft, Issued, Partially Paid, Paid, Overdue'],
        ],
        'wires' => [
            ['name' => 'utr_reference', 'label' => 'UTR Reference Number', 'type' => 'Text', 'required' => true, 'description' => 'Bank Unique Transaction Reference'],
            ['name' => 'remitter_name', 'label' => 'Remitter / Sender', 'type' => 'Text', 'required' => true, 'description' => 'Ordering party / sending entity'],
            ['name' => 'sending_bank', 'label' => 'Sending Financial Institution', 'type' => 'Text', 'required' => true, 'description' => 'Source bank name / SWIFT'],
            ['name' => 'receiving_bank_account', 'label' => 'Receiving Bank Account', 'type' => 'Text', 'required' => true, 'description' => 'Corporate deposit account'],
            ['name' => 'amount', 'label' => 'Wire Transfer Amount', 'type' => 'Currency', 'required' => true, 'description' => 'Funds credited in USD'],
            ['name' => 'date_received', 'label' => 'Credit Value Date', 'type' => 'Date', 'required' => true, 'description' => 'Bank settlement date'],
            ['name' => 'status', 'label' => 'Reconciliation Status', 'type' => 'Dropdown', 'required' => true, 'description' => 'Pending Verification, Matched/Reconciled, Unallocated'],
        ],
        'documents' => [
            ['name' => 'title', 'label' => 'Document Title', 'type' => 'Text', 'required' => true, 'description' => 'Document file or contract title'],
            ['name' => 'category', 'label' => 'Category', 'type' => 'Dropdown', 'required' => false, 'description' => 'Contract, NDA, Invoice, Spec Sheet'],
            ['name' => 'file_url', 'label' => 'File URL / Path', 'type' => 'URL', 'required' => true, 'description' => 'Storage URL / Attachment path'],
            ['name' => 'status', 'label' => 'Document Status', 'type' => 'Dropdown', 'required' => false, 'description' => 'Draft, Shared, Signed, Archived'],
        ],
        'tasks' => [
            ['name' => 'subject', 'label' => 'Subject', 'type' => 'Text', 'required' => true, 'description' => 'Task subject / title'],
            ['name' => 'priority', 'label' => 'Priority', 'type' => 'Dropdown', 'required' => true, 'description' => 'Low, Normal, High, Urgent'],
            ['name' => 'status', 'label' => 'Status', 'type' => 'Dropdown', 'required' => true, 'description' => 'Open, In Progress, Completed, Waiting on someone else, Deferred'],
            ['name' => 'assigned_to', 'label' => 'Assigned To', 'type' => 'Lookup', 'required' => false, 'description' => 'Assigned owner user'],
            ['name' => 'due_date', 'label' => 'Due Date', 'type' => 'Date', 'required' => false, 'description' => 'Due calendar date'],
            ['name' => 'comments', 'label' => 'Comments', 'type' => 'Textarea', 'required' => false, 'description' => 'Task notes and description'],
            ['name' => 'related_entity_name', 'label' => 'Related To', 'type' => 'Lookup', 'required' => false, 'description' => 'Linked CRM record'],
            ['name' => 'reminder_set', 'label' => 'Reminder Set', 'type' => 'Text', 'required' => false, 'description' => 'Reminder timing description'],
            ['name' => 'is_recurring', 'label' => 'Create Recurring Series of Tasks', 'type' => 'Boolean', 'required' => false, 'description' => 'Recurring series flag'],
        ],
        'users' => [
            ['name' => 'full_name', 'label' => 'Full Name', 'type' => 'Text', 'required' => true, 'description' => 'User full display name'],
            ['name' => 'email', 'label' => 'Email Address', 'type' => 'Email', 'required' => true, 'description' => 'Primary corporate email address'],
            ['name' => 'role_id', 'label' => 'Assign Role', 'type' => 'Lookup', 'required' => true, 'description' => 'Organizational job title & reporting role'],
            ['name' => 'profile_id', 'label' => 'Security Profile', 'type' => 'Lookup', 'required' => true, 'description' => 'Permissions & security access profile'],
            ['name' => 'status', 'label' => 'User Status', 'type' => 'Dropdown', 'required' => true, 'description' => 'Active or Inactive account state'],
            ['name' => 'phone', 'label' => 'Phone Number', 'type' => 'Phone', 'required' => false, 'description' => 'Direct contact phone / mobile'],
            ['name' => 'avatar', 'label' => 'Profile Photo URL', 'type' => 'URL', 'required' => false, 'description' => 'User profile avatar image URL'],
        ],
    ];

    /*
    |--------------------------------------------------------------------------
    | Roles, Profiles, Tenants, Security & Governance
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        return $this->roleProfileService->index();
    }

    public function tenantStudio()
    {
        return $this->roleProfileService->tenantStudio();
    }

    public function storeTenant(Request $request, TenantProvisioner $provisioner)
    {
        return $this->roleProfileService->storeTenant($request, $provisioner);
    }

    public function updateTenant(Request $request, Tenant $tenant)
    {
        return $this->roleProfileService->updateTenant($request, $tenant);
    }

    public function destroyTenant(Request $request, Tenant $tenant)
    {
        return $this->roleProfileService->destroyTenant($request, $tenant);
    }

    public function switchTenant(Request $request, Tenant $tenant)
    {
        return $this->roleProfileService->switchTenant($request, $tenant);
    }

    public function companyProfile()
    {
        return $this->roleProfileService->companyProfile();
    }

    public function getTenantApi(Request $request, $tenantId = NULL)
    {
        return $this->roleProfileService->getTenantApi($request, $tenantId);
    }

    public function updateCompanyProfile(Request $request, $tenantId = NULL)
    {
        return $this->roleProfileService->updateCompanyProfile($request, $tenantId);
    }

    public function updateTenantApi(Request $request, $tenantId = NULL)
    {
        return $this->roleProfileService->updateTenantApi($request, $tenantId);
    }

    public function getTenantCurrencies(Request $request, $tenantId = NULL)
    {
        return $this->roleProfileService->getTenantCurrencies($request, $tenantId);
    }

    public function addTenantCurrency(Request $request, $tenantId = NULL)
    {
        return $this->roleProfileService->addTenantCurrency($request, $tenantId);
    }

    public function updateTenantCurrency(Request $request, $currencyId, $tenantId = NULL)
    {
        return $this->roleProfileService->updateTenantCurrency($request, $currencyId, $tenantId);
    }

    public function deleteTenantCurrency(Request $request, $currencyId, $tenantId = NULL)
    {
        return $this->roleProfileService->deleteTenantCurrency($request, $currencyId, $tenantId);
    }

    public function setTenantBaseCurrency(Request $request, $tenantId = NULL)
    {
        return $this->roleProfileService->setTenantBaseCurrency($request, $tenantId);
    }

    public function getTenantEmailSettings(Request $request, $tenantId = NULL)
    {
        return $this->roleProfileService->getTenantEmailSettings($request, $tenantId);
    }

    public function updateTenantEmailSettings(Request $request, $tenantId = NULL)
    {
        return $this->roleProfileService->updateTenantEmailSettings($request, $tenantId);
    }

    public function testTenantEmailDelivery(Request $request, $tenantId = NULL)
    {
        return $this->roleProfileService->testTenantEmailDelivery($request, $tenantId);
    }

    public function usersDirectory(Request $request)
    {
        return $this->roleProfileService->usersDirectory($request);
    }

    public function getUsersApi(Request $request, $tenantId = NULL)
    {
        return $this->roleProfileService->getUsersApi($request, $tenantId);
    }

    public function getUserApi(Request $request, $userId, $tenantId = NULL)
    {
        return $this->roleProfileService->getUserApi($request, $userId, $tenantId);
    }

    public function createUserApi(Request $request, $tenantId = NULL)
    {
        return $this->roleProfileService->createUserApi($request, $tenantId);
    }

    public function updateUserApi(Request $request, $userId, $tenantId = NULL)
    {
        return $this->roleProfileService->updateUserApi($request, $userId, $tenantId);
    }

    public function impersonateUserApi(Request $request, $userId, $tenantId = NULL)
    {
        return $this->roleProfileService->impersonateUserApi($request, $userId, $tenantId);
    }

    public function getTenantInvitations(Request $request, $tenantId = NULL)
    {
        return $this->roleProfileService->getTenantInvitations($request, $tenantId);
    }

    public function createTenantInvitation(Request $request, $tenantId = NULL)
    {
        return $this->roleProfileService->createTenantInvitation($request, $tenantId);
    }

    public function resendTenantInvitation(Request $request, $inviteId, $tenantId = NULL)
    {
        return $this->roleProfileService->resendTenantInvitation($request, $inviteId, $tenantId);
    }

    public function deleteTenantInvitation(Request $request, $inviteId, $tenantId = NULL)
    {
        return $this->roleProfileService->deleteTenantInvitation($request, $inviteId, $tenantId);
    }

    public function getUserOwnedRecordsSummary(Request $request, $userId, $tenantId = NULL)
    {
        return $this->roleProfileService->getUserOwnedRecordsSummary($request, $userId, $tenantId);
    }

    public function offboardUser(Request $request, $userId, $tenantId = NULL)
    {
        return $this->roleProfileService->offboardUser($request, $userId, $tenantId);
    }

    public function addSeats(Request $request, $tenantId = NULL)
    {
        return $this->roleProfileService->addSeats($request, $tenantId);
    }

    public function dashboardStudio(Request $request)
    {
        return $this->roleProfileService->dashboardStudio($request);
    }

    public function getDashboardLayouts(Request $request)
    {
        return $this->roleProfileService->getDashboardLayouts($request);
    }

    public function getDashboardLayout(Request $request, $profileId)
    {
        return $this->roleProfileService->getDashboardLayout($request, $profileId);
    }

    public function saveDashboardLayout(Request $request)
    {
        return $this->roleProfileService->saveDashboardLayout($request);
    }

    public function resetDashboardLayout(Request $request, $profileId)
    {
        return $this->roleProfileService->resetDashboardLayout($request, $profileId);
    }

    public function resetDashboardLayoutWeb(Request $request)
    {
        return $this->roleProfileService->resetDashboardLayoutWeb($request);
    }

    public function resolveProfileInstance($profileIdentifier)
    {
        return $this->roleProfileService->resolveProfileInstance($profileIdentifier);
    }

    public function rolesPermissions(Request $request)
    {
        return $this->roleProfileService->rolesPermissions($request);
    }

    public function recordRights(Request $request)
    {
        return $this->roleProfileService->recordRights($request);
    }

    public function getHydratedPermissionsForProfile(Profile $profile, $customObjects = NULL)
    {
        return $this->roleProfileService->getHydratedPermissionsForProfile($profile, $customObjects);
    }

    public function fieldRights(Request $request)
    {
        return $this->roleProfileService->fieldRights($request);
    }

    public function computeFieldRightsList(?Profile $profile, $customObjects = NULL, $customFields = NULL, $existingFieldPerms = NULL, string $moduleFilter = 'all')
    {
        return $this->roleProfileService->computeFieldRightsList($profile, $customObjects, $customFields, $existingFieldPerms, $moduleFilter);
    }

    public function sharingRules(Request $request)
    {
        return $this->roleProfileService->sharingRules($request);
    }

    public function getSharingRulesMeta()
    {
        return $this->roleProfileService->getSharingRulesMeta();
    }

    public function getSharingRules(Request $request)
    {
        return $this->roleProfileService->getSharingRules($request);
    }

    public function createSharingRule(Request $request)
    {
        return $this->roleProfileService->createSharingRule($request);
    }

    public function updateSharingRule(Request $request, $id)
    {
        return $this->roleProfileService->updateSharingRule($request, $id);
    }

    public function deleteSharingRule($id)
    {
        return $this->roleProfileService->deleteSharingRule($id);
    }

    public function recalculateSharingRule($id)
    {
        return $this->roleProfileService->recalculateSharingRule($id);
    }

    public function getRoles(Request $request)
    {
        return $this->roleProfileService->getRoles($request);
    }

    public function getRole(Request $request, $id)
    {
        return $this->roleProfileService->getRole($request, $id);
    }

    public function createRole(Request $request)
    {
        return $this->roleProfileService->createRole($request);
    }

    public function updateRole(Request $request, $id)
    {
        return $this->roleProfileService->updateRole($request, $id);
    }

    public function deleteRole(Request $request, $id)
    {
        return $this->roleProfileService->deleteRole($request, $id);
    }

    public function getProfiles(Request $request)
    {
        return $this->roleProfileService->getProfiles($request);
    }

    public function getProfile(Request $request, $id)
    {
        return $this->roleProfileService->getProfile($request, $id);
    }

    public function createProfile(Request $request)
    {
        return $this->roleProfileService->createProfile($request);
    }

    public function updateProfile(Request $request, $id)
    {
        return $this->roleProfileService->updateProfile($request, $id);
    }

    public function deleteProfile(Request $request, $id)
    {
        return $this->roleProfileService->deleteProfile($request, $id);
    }

    public function getPermissions(Request $request, $profileId)
    {
        return $this->roleProfileService->getPermissions($request, $profileId);
    }

    public function updatePermissions(Request $request, $profileId)
    {
        return $this->roleProfileService->updatePermissions($request, $profileId);
    }

    public function upsertModulePermission(Request $request, $profileId)
    {
        return $this->roleProfileService->upsertModulePermission($request, $profileId);
    }

    public function deleteModulePermission(Request $request, $profileId, $module)
    {
        return $this->roleProfileService->deleteModulePermission($request, $profileId, $module);
    }

    public function upsertFieldPermission(Request $request, $profileId)
    {
        return $this->roleProfileService->upsertFieldPermission($request, $profileId);
    }

    public function clonePermissions(Request $request, $profileId)
    {
        return $this->roleProfileService->clonePermissions($request, $profileId);
    }

    public function resetPermissions(Request $request, $profileId)
    {
        return $this->roleProfileService->resetPermissions($request, $profileId);
    }

    public function securityGovernance(Request $request)
    {
        return $this->roleProfileService->securityGovernance($request);
    }

    public function getTenantSecurityPolicies(Request $request, $tenantId = NULL)
    {
        return $this->roleProfileService->getTenantSecurityPolicies($request, $tenantId);
    }

    public function updateTenantSecurityPolicies(Request $request, $tenantId = NULL)
    {
        return $this->roleProfileService->updateTenantSecurityPolicies($request, $tenantId);
    }

    public function getTenantSubscription(Request $request, $tenantId = NULL)
    {
        return $this->roleProfileService->getTenantSubscription($request, $tenantId);
    }

    public function purchaseTenantSeats(Request $request, $tenantId = NULL)
    {
        return $this->roleProfileService->purchaseTenantSeats($request, $tenantId);
    }

    public function getTenantInvoices(Request $request, $tenantId = NULL)
    {
        return $this->roleProfileService->getTenantInvoices($request, $tenantId);
    }

    public function approvalRules(Request $request)
    {
        return $this->roleProfileService->approvalRules($request);
    }

    public function storeApprovalRuleApi(Request $request)
    {
        return $this->roleProfileService->storeApprovalRuleApi($request);
    }

    public function getApprovalRuleApi(Request $request, $id)
    {
        return $this->roleProfileService->getApprovalRuleApi($request, $id);
    }

    public function updateApprovalRuleApi(Request $request, $id)
    {
        return $this->roleProfileService->updateApprovalRuleApi($request, $id);
    }

    public function deleteApprovalRuleApi(Request $request, $id)
    {
        return $this->roleProfileService->deleteApprovalRuleApi($request, $id);
    }

    public function toggleApprovalRuleApi(Request $request, $id)
    {
        return $this->roleProfileService->toggleApprovalRuleApi($request, $id);
    }

    public function duplicateRules()
    {
        return $this->roleProfileService->duplicateRules();
    }

    public function processStudio()
    {
        return $this->roleProfileService->processStudio();
    }

    /*
    |--------------------------------------------------------------------------
    | Notification Rules
    |--------------------------------------------------------------------------
    */

    public function notificationRules(Request $request)
    {
        return $this->notificationService->notificationRules($request);
    }

    public function storeNotificationRuleApi(Request $request)
    {
        return $this->notificationService->storeNotificationRuleApi($request);
    }

    public function updateNotificationRuleApi(Request $request, $id)
    {
        return $this->notificationService->updateNotificationRuleApi($request, $id);
    }

    public function deleteNotificationRuleApi(Request $request, $id)
    {
        return $this->notificationService->deleteNotificationRuleApi($request, $id);
    }

    public function toggleNotificationRuleApi(Request $request, $id)
    {
        return $this->notificationService->toggleNotificationRuleApi($request, $id);
    }

    /*
    |--------------------------------------------------------------------------
    | Email Templates & Automated Email Rules
    |--------------------------------------------------------------------------
    */

    public function emailTemplates(Request $request)
    {
        return $this->emailTemplateService->emailTemplates($request);
    }

    public function getEmailTemplatesApi(Request $request)
    {
        return $this->emailTemplateService->getEmailTemplatesApi($request);
    }

    public function getEmailTemplateApi(Request $request, $id)
    {
        return $this->emailTemplateService->getEmailTemplateApi($request, $id);
    }

    public function storeEmailTemplateApi(Request $request)
    {
        return $this->emailTemplateService->storeEmailTemplateApi($request);
    }

    public function updateEmailTemplateApi(Request $request, $id)
    {
        return $this->emailTemplateService->updateEmailTemplateApi($request, $id);
    }

    public function deleteEmailTemplateApi(Request $request, $id)
    {
        return $this->emailTemplateService->deleteEmailTemplateApi($request, $id);
    }

    public function cloneEmailTemplateApi(Request $request, $id)
    {
        return $this->emailTemplateService->cloneEmailTemplateApi($request, $id);
    }

    public function renderEmailTemplateApi(Request $request, $id)
    {
        return $this->emailTemplateService->renderEmailTemplateApi($request, $id);
    }

    public function sendEmailApi(Request $request)
    {
        return $this->emailTemplateService->sendEmailApi($request);
    }

    public function getEmailRulesApi(Request $request)
    {
        return $this->emailTemplateService->getEmailRulesApi($request);
    }

    public function getEmailRuleApi(Request $request, $id)
    {
        return $this->emailTemplateService->getEmailRuleApi($request, $id);
    }

    public function storeEmailRuleApi(Request $request)
    {
        return $this->emailTemplateService->storeEmailRuleApi($request);
    }

    public function updateEmailRuleApi(Request $request, $id)
    {
        return $this->emailTemplateService->updateEmailRuleApi($request, $id);
    }

    public function deleteEmailRuleApi(Request $request, $id)
    {
        return $this->emailTemplateService->deleteEmailRuleApi($request, $id);
    }

    public function toggleEmailRuleApi(Request $request, $id)
    {
        return $this->emailTemplateService->toggleEmailRuleApi($request, $id);
    }

    public function evaluateScheduledEmailRulesApi(Request $request)
    {
        return $this->emailTemplateService->evaluateScheduledEmailRulesApi($request);
    }

    /*
    |--------------------------------------------------------------------------
    | Document Templates & Governance Rights
    |--------------------------------------------------------------------------
    */

    public function documentTemplates(Request $request)
    {
        return $this->documentTemplateService->documentTemplates($request);
    }

    public function getDocumentTemplatesApi(Request $request)
    {
        return $this->documentTemplateService->getDocumentTemplatesApi($request);
    }

    public function getDocumentTemplateApi(Request $request, $id)
    {
        return $this->documentTemplateService->getDocumentTemplateApi($request, $id);
    }

    public function storeDocumentTemplateApi(Request $request)
    {
        return $this->documentTemplateService->storeDocumentTemplateApi($request);
    }

    public function updateDocumentTemplateApi(Request $request, $id)
    {
        return $this->documentTemplateService->updateDocumentTemplateApi($request, $id);
    }

    public function setDefaultDocumentTemplateApi(Request $request, $id)
    {
        return $this->documentTemplateService->setDefaultDocumentTemplateApi($request, $id);
    }

    public function cloneDocumentTemplateApi(Request $request, $id)
    {
        return $this->documentTemplateService->cloneDocumentTemplateApi($request, $id);
    }

    public function deleteDocumentTemplateApi(Request $request, $id)
    {
        return $this->documentTemplateService->deleteDocumentTemplateApi($request, $id);
    }

    public function getDocumentGovernanceRightsApi(Request $request)
    {
        return $this->documentTemplateService->getDocumentGovernanceRightsApi($request);
    }

    public function getMyDocumentGovernanceRights(Request $request)
    {
        return $this->documentTemplateService->getMyDocumentGovernanceRights($request);
    }

    public function updateDocumentGovernanceRightApi(Request $request, $profileId)
    {
        return $this->documentTemplateService->updateDocumentGovernanceRightApi($request, $profileId);
    }

    /*
    |--------------------------------------------------------------------------
    | Custom Fields & Standard Overrides
    |--------------------------------------------------------------------------
    */

    public function objectFields()
    {
        return $this->customFieldService->objectFields();
    }

    public function getCustomFields(Request $request)
    {
        return $this->customFieldService->getCustomFields($request);
    }

    public function getCustomField(Request $request, $id)
    {
        return $this->customFieldService->getCustomField($request, $id);
    }

    public function createCustomField(Request $request)
    {
        return $this->customFieldService->createCustomField($request);
    }

    public function updateCustomField(Request $request, $id)
    {
        return $this->customFieldService->updateCustomField($request, $id);
    }

    public function deleteCustomField(Request $request, $id)
    {
        return $this->customFieldService->deleteCustomField($request, $id);
    }

    public function getStandardFieldOverrides(Request $request)
    {
        return $this->customFieldService->getStandardFieldOverrides($request);
    }

    public function saveStandardFieldOverride(Request $request)
    {
        return $this->customFieldService->saveStandardFieldOverride($request);
    }

    public function getGlobalPicklists(Request $request)
    {
        return $this->customFieldService->getGlobalPicklists($request);
    }

    public function getGlobalPicklist(Request $request, $id)
    {
        return $this->customFieldService->getGlobalPicklist($request, $id);
    }

    public function createGlobalPicklist(Request $request)
    {
        return $this->customFieldService->createGlobalPicklist($request);
    }

    public function updateGlobalPicklist(Request $request, $id)
    {
        return $this->customFieldService->updateGlobalPicklist($request, $id);
    }

    public function deleteGlobalPicklist(Request $request, $id)
    {
        return $this->customFieldService->deleteGlobalPicklist($request, $id);
    }

    public function getLeadMappings(Request $request)
    {
        return $this->customFieldService->getLeadMappings($request);
    }

    public function saveLeadMappings(Request $request)
    {
        return $this->customFieldService->saveLeadMappings($request);
    }

    public function createTargetFieldForLead(Request $request)
    {
        return $this->customFieldService->createTargetFieldForLead($request);
    }

    public function autoMatchLeadMappings(Request $request)
    {
        return $this->customFieldService->autoMatchLeadMappings($request);
    }

    /*
    |--------------------------------------------------------------------------
    | Custom Objects, Page Layouts, Field History & Custom Buttons
    |--------------------------------------------------------------------------
    */

    public function customObjects()
    {
        return $this->customObjectService->customObjects();
    }

    public function getCustomObjectsApi(Request $request)
    {
        return $this->customObjectService->getCustomObjectsApi($request);
    }

    public function createCustomObjectApi(Request $request)
    {
        return $this->customObjectService->createCustomObjectApi($request);
    }

    public function getCustomObjectApi(Request $request, $id)
    {
        return $this->customObjectService->getCustomObjectApi($request, $id);
    }

    public function deleteCustomObjectApi(Request $request, $id)
    {
        return $this->customObjectService->deleteCustomObjectApi($request, $id);
    }

    public function getCustomObjectRecordsApi(Request $request, $objectId)
    {
        return $this->customObjectService->getCustomObjectRecordsApi($request, $objectId);
    }

    public function createCustomObjectRecordApi(Request $request, $objectId)
    {
        return $this->customObjectService->createCustomObjectRecordApi($request, $objectId);
    }

    public function getCustomObjectRecordApi(Request $request, $objectId, $recordId)
    {
        return $this->customObjectService->getCustomObjectRecordApi($request, $objectId, $recordId);
    }

    public function updateCustomObjectRecordApi(Request $request, $objectId, $recordId)
    {
        return $this->customObjectService->updateCustomObjectRecordApi($request, $objectId, $recordId);
    }

    public function deleteCustomObjectRecordApi(Request $request, $objectId, $recordId)
    {
        return $this->customObjectService->deleteCustomObjectRecordApi($request, $objectId, $recordId);
    }

    public function pageLayouts()
    {
        return $this->customObjectService->pageLayouts();
    }

    public function getPageLayoutsApi(Request $request, $module)
    {
        return $this->customObjectService->getPageLayoutsApi($request, $module);
    }

    public function getActivePageLayoutApi(Request $request, $module)
    {
        return $this->customObjectService->getActivePageLayoutApi($request, $module);
    }

    public function getPageLayoutApi(Request $request, $module, $id)
    {
        return $this->customObjectService->getPageLayoutApi($request, $module, $id);
    }

    public function createPageLayoutApi(Request $request, $module)
    {
        return $this->customObjectService->createPageLayoutApi($request, $module);
    }

    public function updatePageLayoutApi(Request $request, $module, $id)
    {
        return $this->customObjectService->updatePageLayoutApi($request, $module, $id);
    }

    public function deletePageLayoutApi(Request $request, $module, $id)
    {
        return $this->customObjectService->deletePageLayoutApi($request, $module, $id);
    }

    public function setDefaultPageLayoutApi(Request $request, $module, $id)
    {
        return $this->customObjectService->setDefaultPageLayoutApi($request, $module, $id);
    }

    public function savePageLayoutAssignmentsApi(Request $request, $module)
    {
        return $this->customObjectService->savePageLayoutAssignmentsApi($request, $module);
    }

    public function getFieldHistoryConfigApi(Request $request, $module)
    {
        return $this->customObjectService->getFieldHistoryConfigApi($request, $module);
    }

    public function saveFieldHistoryConfigApi(Request $request, $module)
    {
        return $this->customObjectService->saveFieldHistoryConfigApi($request, $module);
    }

    public function dataStudio()
    {
        return $this->customObjectService->dataStudio();
    }

    public function auditLogs()
    {
        return $this->customObjectService->auditLogs();
    }

    public function customButtonsStudio(Request $request)
    {
        return $this->customObjectService->customButtonsStudio($request);
    }

    public function getCustomButtonsApi(Request $request)
    {
        return $this->customObjectService->getCustomButtonsApi($request);
    }

    public function getCustomButtonApi($id)
    {
        return $this->customObjectService->getCustomButtonApi($id);
    }

    public function createCustomButtonApi(Request $request)
    {
        return $this->customObjectService->createCustomButtonApi($request);
    }

    public function updateCustomButtonApi(Request $request, $id)
    {
        return $this->customObjectService->updateCustomButtonApi($request, $id);
    }

    public function deleteCustomButtonApi($id)
    {
        return $this->customObjectService->deleteCustomButtonApi($id);
    }

    public function executeCustomButtonApi(Request $request, $id)
    {
        return $this->customObjectService->executeCustomButtonApi($request, $id);
    }

}
