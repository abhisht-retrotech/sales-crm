@extends('layouts.app')

@section('title', 'Field-Level Security (FLS) — DealSync Admin')

@section('content')
<style>
/* ========================================================================= */
/* FIELD-LEVEL SECURITY (FLS) MATRIX STYLES                                  */
/* ========================================================================= */
.fls-studio-container {
    --text-primary: var(--zinc-900, #0f172a);
    --text-secondary: var(--zinc-600, #475569);
    --text-tertiary: var(--zinc-500, #64748b);
    --text-muted: var(--zinc-400, #94a3b8);
    max-width: 1200px;
    margin: 0 auto;
    padding-bottom: 3rem;
    color: var(--zinc-900, #0f172a);
}

.fls-studio-container * {
    box-sizing: border-box;
}

.form-input, .form-control {
    width: 100%;
    box-sizing: border-box;
    height: 38px;
    background-color: var(--zinc-50, #f8fafc);
    border: 1px solid var(--zinc-200, #e2e8f0);
    border-radius: var(--radius-md, 8px);
    color: var(--zinc-950, #09090b);
    padding: 0.45rem 0.85rem;
    font-size: 0.8125rem;
    font-family: inherit;
    line-height: 1.4;
    outline: none;
    transition: border-color 0.15s ease, background-color 0.15s ease, box-shadow 0.15s ease;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.02);
}

.form-input::placeholder, .form-control::placeholder {
    color: var(--zinc-400, #94a3b8);
    font-size: 0.8125rem;
}

.form-input:hover, .form-control:hover {
    border-color: var(--zinc-300, #cbd5e1);
    background-color: #ffffff;
}

.form-input:focus, .form-control:focus,
.form-input:focus-visible, .form-control:focus-visible {
    background-color: #ffffff !important;
    border-color: var(--color-primary, #4f46e5) !important;
    box-shadow: 0 0 0 3px var(--color-primary-subtle, rgba(79, 70, 229, 0.15)) !important;
    color: var(--zinc-950, #09090b) !important;
}

.form-select {
    width: 100%;
    box-sizing: border-box;
    height: 38px;
    background-color: var(--zinc-50, #f8fafc);
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 0.75rem center;
    background-repeat: no-repeat;
    background-size: 1.15em 1.15em;
    padding: 0.45rem 2.25rem 0.45rem 0.85rem;
    border: 1px solid var(--zinc-200, #e2e8f0);
    border-radius: var(--radius-md, 8px);
    color: var(--zinc-950, #09090b);
    font-size: 0.8125rem;
    font-family: inherit;
    line-height: 1.4;
    outline: none;
    cursor: pointer;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    transition: border-color 0.15s ease, background-color 0.15s ease, box-shadow 0.15s ease;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.02);
}

.form-select:hover {
    border-color: var(--zinc-300, #cbd5e1);
    background-color: #ffffff;
}

.form-select:focus,
.form-select:focus-visible {
    background-color: #ffffff !important;
    border-color: var(--color-primary, #4f46e5) !important;
    box-shadow: 0 0 0 3px var(--color-primary-subtle, rgba(79, 70, 229, 0.15)) !important;
    color: var(--zinc-950, #09090b) !important;
}

.form-select option {
    background: #ffffff;
    color: var(--zinc-900, #09090b);
    padding: 0.5rem 0.75rem;
    font-size: 0.8125rem;
}

/* Specific styling for table inline permission dropdown */
.fls-perm-select {
    height: 32px !important;
    padding: 0.3rem 1.85rem 0.3rem 0.65rem !important;
    font-size: 0.75rem !important;
    font-weight: 600 !important;
    background-size: 1em 1em !important;
    background-position: right 0.5rem center !important;
}

.data-table th {
    background: var(--zinc-100, #f1f5f9);
    color: var(--zinc-700, #334155);
    font-weight: 600;
    font-size: 0.75rem;
    letter-spacing: 0.02em;
    padding: 0.65rem 0.85rem;
    border-bottom: 1px solid var(--zinc-200, #e2e8f0);
    white-space: nowrap;
}

.data-table td {
    padding: 0.75rem 0.85rem;
    border-bottom: 1px solid var(--zinc-200, #e2e8f0);
    font-size: 0.8125rem;
    color: var(--zinc-900, #0f172a);
    vertical-align: middle;
}

.data-table tbody tr:hover td {
    background-color: var(--zinc-50, #f8fafc);
}

.data-table tbody tr:last-child td {
    border-bottom: none;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(4px); }
    to { opacity: 1; transform: translateY(0); }
}

.animate-fade-in {
    animation: fadeIn 0.15s ease-out forwards;
}
</style>


<div class="fls-studio-container animate-fade-in">
    <!-- Feedback Banner (SettingsView.tsx line 1737) -->
    <div id="feedbackBanner" style="display: none; padding: 0.85rem 1.25rem; border-radius: var(--radius-lg); margin-bottom: 1.25rem; align-items: center; gap: 0.6rem; font-size: 0.8125rem; font-weight: 500;">
        <i id="feedbackIcon" data-lucide="check-circle-2" style="width: 16px; height: 16px; flex-shrink: 0;"></i>
        <span id="feedbackText"></span>
    </div>

    <!-- TAB 4: FIELD-LEVEL RIGHTS (SettingsView.tsx lines 2558-2886) -->
    <div style="display: flex; flex-direction: column; gap: 1rem;">
        <!-- Header Bar -->
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; margin-bottom: 0.75rem;">
            <div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <i data-lucide="shield-check" style="width: 20px; height: 20px; color: var(--color-primary, #4f46e5);"></i>
                    <h2 style="font-size: 1.125rem; font-weight: 600; color: var(--zinc-950); letter-spacing: -0.02em; margin: 0;">
                        Field-Level Security (FLS)
                    </h2>
                </div>
                <p style="font-size: 0.8125rem; color: var(--zinc-500); margin: 0.2rem 0 0.5rem 0;">
                    Control field visibility, read-only constraints, and data masking across all standard and custom objects.
                </p>
                <div style="display: flex; align-items: center; gap: 0.65rem;">
                    <span style="font-size: 0.75rem; font-weight: 600; color: var(--zinc-700, #334155);">Configuring Profile:</span>
                    <select
                        id="selectedProfileSelect"
                        class="form-select"
                        onchange="handleProfileChange(this.value)"
                        style="width: 250px; font-weight: 600; font-size: 0.8125rem;"
                    >
                        <!-- Populated dynamically via renderProfileOptions() -->
                    </select>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <button
                    type="button"
                    id="saveFlsBtn"
                    onclick="handleSavePermissions()"
                    class="btn btn-primary"
                    style="display: inline-flex; align-items: center; gap: 0.45rem; padding: 0.5rem 1rem; font-weight: 600;"
                >
                    <i id="saveFlsIcon" data-lucide="save" style="width: 15px; height: 15px;"></i>
                    <span id="saveFlsText">Save Field Security</span>
                </button>
            </div>
        </div>

        <!-- Filter & Selector Toolbar Card -->
        <div class="card" style="padding: 1rem; background: var(--bg-card); border: 1px solid var(--border-card); border-radius: var(--radius-lg, 12px);">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem; align-items: flex-end; margin-bottom: 0.85rem;">
                <!-- Object / Module Selector Dropdown -->
                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--zinc-800, #1e293b); margin-bottom: 0.35rem;">
                        🎯 Object / Module Selector:
                    </label>
                    <select
                        id="moduleFilterSelect"
                        class="form-select"
                        onchange="handleModuleChange(this.value)"
                        style="width: 100%; font-weight: 500; font-size: 0.8125rem;"
                    >
                        <!-- Populated dynamically via renderModuleOptions() -->
                    </select>
                </div>

                <!-- Search Box -->
                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--zinc-800, #1e293b); margin-bottom: 0.35rem;">
                        🔍 Search Fields:
                    </label>
                    <div style="position: relative;">
                        <i data-lucide="search" style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: var(--zinc-400); width: 15px; height: 15px; pointer-events: none;"></i>
                        <input
                            type="text"
                            id="fieldSearchInput"
                            class="form-input"
                            placeholder="Search field label, API key, type..."
                            oninput="handleSearchChange(this.value)"
                            style="padding-left: 2.25rem; padding-right: 2.25rem; font-size: 0.8125rem; width: 100%;"
                        >
                        <button
                            type="button"
                            id="clearSearchBtn"
                            onclick="handleClearSearch()"
                            style="position: absolute; right: 0.65rem; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--zinc-400); display: none; padding: 0.2rem; align-items: center; justify-content: center;"
                        >
                            <i data-lucide="x" style="width: 14px; height: 14px;"></i>
                        </button>
                    </div>
                </div>

                <!-- Batch Actions -->
                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--zinc-800, #1e293b); margin-bottom: 0.35rem;">
                        ⚡ Bulk Action (Visible Fields):
                    </label>
                    <div style="display: flex; gap: 0.35rem; flex-wrap: wrap;">
                        <button
                            type="button"
                            onclick="handleBulkSetFieldPerms('Read/Write')"
                            class="btn btn-ghost btn-sm"
                            style="font-size: 0.72rem; padding: 0.35rem 0.6rem; border: 1px solid rgba(16, 185, 129, 0.25); color: #047857; background: #ecfdf5; font-weight: 600;"
                            title="Set all currently visible fields to Read/Write"
                        >
                            ✓ All Read/Write
                        </button>
                        <button
                            type="button"
                            onclick="handleBulkSetFieldPerms('Read-Only')"
                            class="btn btn-ghost btn-sm"
                            style="font-size: 0.72rem; padding: 0.35rem 0.6rem; border: 1px solid rgba(245, 158, 11, 0.25); color: #b45309; background: #fffbeb; font-weight: 600;"
                            title="Set all currently visible fields to Read-Only"
                        >
                            🔒 All Read-Only
                        </button>
                        <button
                            type="button"
                            onclick="handleBulkSetFieldPerms('Hidden')"
                            class="btn btn-ghost btn-sm"
                            style="font-size: 0.72rem; padding: 0.35rem 0.6rem; border: 1px solid rgba(239, 68, 68, 0.25); color: #b91c1c; background: #fef2f2; font-weight: 600;"
                            title="Set all currently visible fields to Hidden"
                        >
                            👁️ All Hidden
                        </button>
                    </div>
                </div>
            </div>

            <!-- Quick Filter Pills & Metrics Strip -->
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem; padding-top: 0.75rem; border-top: 1px solid var(--border-card);">
                <!-- Filter Pills -->
                <div style="display: flex; align-items: center; gap: 0.4rem; flex-wrap: wrap;">
                    <span style="font-size: 0.72rem; color: var(--zinc-600, #475569); font-weight: 600; margin-right: 0.2rem;">Filter View:</span>
                    <button
                        type="button"
                        id="filterPillAll"
                        onclick="setQuickFilter('all')"
                        class="btn btn-sm btn-primary"
                        style="font-size: 0.72rem; padding: 0.2rem 0.6rem; border-radius: 9999px;"
                    >
                        All Fields
                    </button>
                    <button
                        type="button"
                        id="filterPillSensitive"
                        onclick="setQuickFilter('sensitive')"
                        class="btn btn-sm btn-ghost"
                        style="font-size: 0.72rem; padding: 0.2rem 0.6rem; border-radius: 9999px; border: 1px solid rgba(245, 158, 11, 0.27); color: #d97706;"
                    >
                        ⚠️ Sensitive Only
                    </button>
                    <button
                        type="button"
                        id="filterPillCustom"
                        onclick="setQuickFilter('custom')"
                        class="btn btn-sm btn-ghost"
                        style="font-size: 0.72rem; padding: 0.2rem 0.6rem; border-radius: 9999px; border: 1px solid rgba(99, 102, 241, 0.27); color: #4f46e5;"
                    >
                        🧩 Custom Fields Only
                    </button>
                    <button
                        type="button"
                        id="filterPillRestricted"
                        onclick="setQuickFilter('restricted')"
                        class="btn btn-sm btn-ghost"
                        style="font-size: 0.72rem; padding: 0.2rem 0.6rem; border-radius: 9999px; border: 1px solid rgba(239, 68, 68, 0.27); color: #dc2626;"
                    >
                        🔒 Restricted (Read-Only / Hidden)
                    </button>
                </div>

                <!-- Counts Badge -->
                <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.75rem;">
                    <span style="color: var(--zinc-600, #475569); font-weight: 500;">
                        Showing <strong id="totalCountBadge" style="color: var(--zinc-950, #09090b); font-weight: 700;">0</strong> fields
                    </span>
                    <span id="rwCountBadge" style="background: #ecfdf5; color: #047857; padding: 0.15rem 0.45rem; border-radius: 4px; font-weight: 600; font-size: 0.7rem; border: 1px solid rgba(16, 185, 129, 0.2);">
                        0 Read/Write
                    </span>
                    <span id="roCountBadge" style="background: #fffbeb; color: #b45309; padding: 0.15rem 0.45rem; border-radius: 4px; font-weight: 600; font-size: 0.7rem; border: 1px solid rgba(245, 158, 11, 0.2);">
                        0 Read-Only
                    </span>
                    <span id="hdCountBadge" style="background: #fef2f2; color: #b91c1c; padding: 0.15rem 0.45rem; border-radius: 4px; font-weight: 600; font-size: 0.7rem; border: 1px solid rgba(239, 68, 68, 0.2);">
                        0 Hidden
                    </span>
                </div>
            </div>
        </div>

        <!-- Table Card -->
        <div class="card" style="padding: 0; overflow: hidden; border: 1px solid var(--border-card); border-radius: var(--radius-lg, 12px);">
            <div style="overflow-x: auto;">
                <table class="data-table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th style="width: 180px;">Object / Module</th>
                            <th>Field Label &amp; Key</th>
                            <th style="width: 160px;">Type &amp; Scope</th>
                            <th style="width: 240px;">Access Permission Policy</th>
                        </tr>
                    </thead>
                    <tbody id="flsTableBody">
                        <!-- Populated dynamically via renderFlsRows() -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// ============================================================================
// FIELD-LEVEL SECURITY (FLS) STUDIO STATE ENGINE (1-TO-1 REACT PARITY)
// ============================================================================
const STORAGE_PROFILES_KEY = 'dealsync_profiles';
const STORAGE_SELECTED_PROFILE_KEY = 'dealsync_selected_profile_id';
const STORAGE_FIELD_PERMS_PREFIX = 'dealsync_field_perms_';
const STORAGE_CUSTOM_FIELDS_KEY = 'dealsync_custom_fields';
const STORAGE_CUSTOM_OBJECTS_KEY = 'dealsync_custom_objects';

const SEED_PROFILES = [
    {
        id: 'prof-admin',
        name: 'System Administrator',
        description: 'Unrestricted administrative access to all records and settings'
    },
    {
        id: 'prof-sales',
        name: 'Standard Sales Representative',
        description: 'Read/Write access to Leads, Accounts, and Opportunities'
    },
    {
        id: 'prof-procurement',
        name: 'Procurement Specialist',
        description: 'Read/Write access to Vendors and Purchase Deals'
    },
    {
        id: 'prof-auditor',
        name: 'Read-Only Compliance Auditor',
        description: 'Read-only access across all records with no editing rights'
    }
];

const MODULE_OPTIONS = [
    { id: 'leads', name: 'Leads' },
    { id: 'accounts', name: 'Accounts' },
    { id: 'contacts', name: 'Contacts' },
    { id: 'opportunities', name: 'Opportunities (Sales Pipeline)' },
    { id: 'tasks', name: 'Tasks' },
    { id: 'purchase_deals', name: 'Deals (Purchase & Procurement)' },
    { id: 'shipping', name: 'Shipping & Logistics' },
    { id: 'payments', name: 'Payments & Invoicing (AR / AP)' },
    { id: 'wires', name: 'Wire Received & Bank Reconciliation' },
    { id: 'users', name: 'Users (Active User Directory)' }
];

const SENSITIVE_FIELDS_SET = new Set([
    'opportunities.amount',
    'opportunities.probability',
    'purchase_deals.amount',
    'accounts.credit_limit',
    'accounts.annual_revenue',
    'contacts.phone',
    'wires.amount',
    'users.email',
    'leads.email',
    'payments.amount'
]);

const STANDARD_FIELDS = {
    leads: [
        { name: 'first_name', label: 'First Name', type: 'Text', required: true, description: 'Lead first name' },
        { name: 'last_name', label: 'Last Name', type: 'Text', required: true, description: 'Lead last name' },
        { name: 'company', label: 'Company Name', type: 'Text', required: true, description: 'Lead company or business' },
        { name: 'title', label: 'Job Title', type: 'Text', required: false, description: 'Job designation' },
        { name: 'email', label: 'Email Address', type: 'Email', required: true, description: 'Primary business email' },
        { name: 'phone', label: 'Phone Number', type: 'Phone', required: false, description: 'Direct contact phone' },
        { name: 'status', label: 'Status', type: 'Dropdown', required: true, description: 'Lead qualification status' },
        { name: 'score', label: 'Lead Score', type: 'Number', required: false, description: '0-100 propensity score' },
        { name: 'source', label: 'Lead Source', type: 'Dropdown', required: false, description: 'Inbound marketing channel' },
        { name: 'assigned_to', label: 'Assigned To', type: 'Text', required: false, description: 'Sales rep user assignment' }
    ],
    accounts: [
        { name: 'name', label: 'Account Name', type: 'Text', required: true, description: 'Legal or operating entity name' },
        { name: 'type', label: 'Account Type', type: 'Dropdown', required: true, description: 'Customer, Vendor, Prospect, Partner' },
        { name: 'industry', label: 'Industry', type: 'Text', required: false, description: 'Business vertical or sector' },
        { name: 'website', label: 'Website URL', type: 'URL', required: false, description: 'Corporate website' },
        { name: 'phone', label: 'Phone', type: 'Phone', required: false, description: 'Corporate telephone' },
        { name: 'billing_address', label: 'Billing Address', type: 'Textarea', required: false, description: 'Headquarters billing location' },
        { name: 'shipping_address', label: 'Shipping Address', type: 'Textarea', required: false, description: 'Primary warehouse / receiving location' },
        { name: 'payment_terms', label: 'Payment Terms', type: 'Dropdown', required: false, description: 'Net 15, Net 30, Net 60, Advance Wire' },
        { name: 'credit_limit', label: 'Credit Limit', type: 'Currency', required: false, description: 'Max credit allowance in USD' },
        { name: 'annual_revenue', label: 'Annual Revenue', type: 'Currency', required: false, description: 'Annual turnover in USD' }
    ],
    contacts: [
        { name: 'first_name', label: 'First Name', type: 'Text', required: true, description: 'Stakeholder first name' },
        { name: 'last_name', label: 'Last Name', type: 'Text', required: true, description: 'Stakeholder last name' },
        { name: 'account_id', label: 'Account (Company)', type: 'Lookup', required: false, description: 'Associated company' },
        { name: 'title', label: 'Job Title', type: 'Text', required: false, description: 'Role / Designation' },
        { name: 'department', label: 'Department', type: 'Text', required: false, description: 'Functional department' },
        { name: 'email', label: 'Email', type: 'Email', required: true, description: 'Direct stakeholder email' },
        { name: 'phone', label: 'Phone', type: 'Phone', required: false, description: 'Direct phone number' },
        { name: 'contact_role', label: 'Buying Role', type: 'Dropdown', required: false, description: 'Decision Maker, Champion, Influencer, Evaluator' },
        { name: 'is_primary', label: 'Primary Contact', type: 'Boolean', required: false, description: 'Key corporate contact flag' }
    ],
    opportunities: [
        { name: 'name', label: 'Opportunity Name', type: 'Text', required: true, description: 'Deal title / project identifier' },
        { name: 'account_id', label: 'Customer Account', type: 'Dropdown', required: true, description: 'Client purchasing the deal' },
        { name: 'purchase_deal_id', label: 'Active Procurement Deal (PO)', type: 'Dropdown', required: true, description: 'Required PO deal from which this opportunity is created' },
        { name: 'stage', label: 'Pipeline Stage', type: 'Dropdown', required: true, description: 'Discovery, Proposal, Negotiation, Closed Won, Closed Lost' },
        { name: 'amount', label: 'Deal Amount', type: 'Currency', required: true, description: 'Expected deal value in USD' },
        { name: 'probability', label: 'Win Probability (%)', type: 'Number', required: false, description: 'Forecast weighting 0-100%' },
        { name: 'expected_close_date', label: 'Target Close Date', type: 'Date', required: false, description: 'Forecast closing calendar date' }
    ],
    purchase_deals: [
        { name: 'po_number', label: 'PO Number', type: 'Text', required: true, description: 'Unique Purchase Order reference' },
        { name: 'title', label: 'PO Title / Description', type: 'Text', required: true, description: 'Procurement order summary' },
        { name: 'vendor_id', label: 'Supplier / Vendor', type: 'Dropdown', required: true, description: 'Supplying vendor account' },
        { name: 'amount', label: 'PO Total Spend', type: 'Currency', required: true, description: 'Procurement commitment amount' },
        { name: 'stage', label: 'Procurement Stage', type: 'Dropdown', required: true, description: 'Draft, RFQ Sent, PO Issued, Confirmed, Completed' },
        { name: 'expected_delivery_date', label: 'Target Delivery Date', type: 'Date', required: false, description: 'Supplier promised delivery date' },
        { name: 'payment_terms', label: 'Payment Terms', type: 'Dropdown', required: false, description: 'Vendor payment terms' }
    ],
    shipping: [
        { name: 'tracking_number', label: 'Tracking Number', type: 'Text', required: true, description: 'Carrier tracking code' },
        { name: 'carrier', label: 'Freight Carrier', type: 'Dropdown', required: true, description: 'FedEx, UPS, DHL, Maersk, Linehaul' },
        { name: 'type', label: 'Shipment Type', type: 'Dropdown', required: true, description: 'Outbound (Sales) or Inbound (Procurement)' },
        { name: 'status', label: 'Logistics Status', type: 'Dropdown', required: true, description: 'Pending, In Transit, Customs Clearance, Delivered' },
        { name: 'account_id', label: 'Associated Account', type: 'Dropdown', required: true, description: 'Linked customer or vendor account' },
        { name: 'shipping_origin', label: 'Origin / Dispatch Facility', type: 'Text', required: false, description: 'Dispatch warehouse or supplier dock' },
        { name: 'destination_address', label: 'Destination Address', type: 'Textarea', required: false, description: 'Delivery facility or client site' },
        { name: 'estimated_delivery_date', label: 'Est. Delivery Date', type: 'Date', required: false, description: 'Estimated arrival calendar date' },
        { name: 'notes', label: 'Shipping Notes', type: 'Textarea', required: false, description: 'Special cargo or delivery instructions' }
    ],
    payments: [
        { name: 'invoice_number', label: 'Invoice Number', type: 'Text', required: true, description: 'Legal billing invoice identifier' },
        { name: 'account_id', label: 'Billed Account', type: 'Dropdown', required: true, description: 'Customer or Vendor' },
        { name: 'type', label: 'Invoice Category', type: 'Dropdown', required: true, description: 'Accounts Receivable (AR) or Accounts Payable (AP)' },
        { name: 'amount', label: 'Invoice Amount', type: 'Currency', required: true, description: 'Total invoice billing amount' },
        { name: 'paid_amount', label: 'Paid / Settled Amount', type: 'Currency', required: false, description: 'Amount collected / settled to date' },
        { name: 'due_date', label: 'Payment Due Date', type: 'Date', required: true, description: 'Net terms maturity date' },
        { name: 'status', label: 'Payment Status', type: 'Dropdown', required: true, description: 'Draft, Issued, Partially Paid, Paid, Overdue' }
    ],
    wires: [
        { name: 'utr_reference', label: 'UTR Reference Number', type: 'Text', required: true, description: 'Bank Unique Transaction Reference' },
        { name: 'remitter_name', label: 'Remitter / Sender', type: 'Text', required: true, description: 'Ordering party / sending entity' },
        { name: 'sending_bank', label: 'Sending Financial Institution', type: 'Text', required: true, description: 'Source bank name / SWIFT' },
        { name: 'receiving_bank_account', label: 'Receiving Bank Account', type: 'Text', required: true, description: 'Corporate deposit account' },
        { name: 'amount', label: 'Wire Transfer Amount', type: 'Currency', required: true, description: 'Funds credited in USD' },
        { name: 'date_received', label: 'Credit Value Date', type: 'Date', required: true, description: 'Bank settlement date' },
        { name: 'status', label: 'Reconciliation Status', type: 'Dropdown', required: true, description: 'Pending Verification, Matched/Reconciled, Unallocated' }
    ],
    tasks: [
        { name: 'subject', label: 'Subject', type: 'Text', required: true, description: 'Task subject / title' },
        { name: 'priority', label: 'Priority', type: 'Dropdown', required: true, description: 'Low, Normal, High, Urgent' },
        { name: 'status', label: 'Status', type: 'Dropdown', required: true, description: 'Open, In Progress, Completed, Waiting on someone else, Deferred' },
        { name: 'assigned_to', label: 'Assigned To', type: 'Lookup', required: false, description: 'Assigned owner user' },
        { name: 'due_date', label: 'Due Date', type: 'Date', required: false, description: 'Due calendar date' },
        { name: 'comments', label: 'Comments', type: 'Textarea', required: false, description: 'Task notes and description' },
        { name: 'related_entity_name', label: 'Related To', type: 'Lookup', required: false, description: 'Linked CRM record' },
        { name: 'reminder_set', label: 'Reminder Set', type: 'Text', required: false, description: 'Reminder timing description' },
        { name: 'is_recurring', label: 'Create Recurring Series of Tasks', type: 'Boolean', required: false, description: 'Recurring series flag' }
    ],
    users: [
        { name: 'full_name', label: 'Full Name', type: 'Text', required: true, description: 'User full display name' },
        { name: 'email', label: 'Email Address', type: 'Email', required: true, description: 'Primary corporate email address' },
        { name: 'role_id', label: 'Assign Role', type: 'Lookup', required: true, description: 'Organizational job title & reporting role' },
        { name: 'profile_id', label: 'Security Profile', type: 'Lookup', required: true, description: 'Permissions & security access profile' },
        { name: 'status', label: 'User Status', type: 'Dropdown', required: true, description: 'Active or Inactive account state' },
        { name: 'phone', label: 'Phone Number', type: 'Phone', required: false, description: 'Direct contact phone / mobile' },
        { name: 'avatar', label: 'Profile Photo URL', type: 'URL', required: false, description: 'User profile avatar image URL' },
        { name: 'surprise_me_enabled', label: 'Ambient Themes Permission', type: 'Dropdown', required: false, description: 'Ambient themes and glows toggle permission' }
    ]
};

let profiles = [];
let selectedProfileId = 'prof-admin';
let allCustomFields = [];
let customObjectsList = [];
let fieldPerms = [];

let fieldRightsModule = 'all';
let fieldRightsSearch = '';
let fieldRightsFilter = 'all';

function initFieldRights() {
    try {
        const storedProfiles = localStorage.getItem(STORAGE_PROFILES_KEY);
        profiles = storedProfiles ? JSON.parse(storedProfiles) : [...SEED_PROFILES];

        const storedSelected = localStorage.getItem(STORAGE_SELECTED_PROFILE_KEY);
        if (storedSelected && profiles.some(p => p.id === storedSelected)) {
            selectedProfileId = storedSelected;
        } else {
            selectedProfileId = profiles.length > 0 ? profiles[0].id : 'prof-admin';
        }

        const storedCfs = localStorage.getItem(STORAGE_CUSTOM_FIELDS_KEY);
        allCustomFields = storedCfs ? JSON.parse(storedCfs) : [];

        const storedCos = localStorage.getItem(STORAGE_CUSTOM_OBJECTS_KEY);
        customObjectsList = storedCos ? JSON.parse(storedCos) : [];
    } catch (e) {
        profiles = [...SEED_PROFILES];
        selectedProfileId = 'prof-admin';
        allCustomFields = [];
        customObjectsList = [];
    }

    renderProfileOptions();
    renderModuleOptions();
    loadFieldPermissionsForProfile(selectedProfileId);
}

function renderProfileOptions() {
    const select = document.getElementById('selectedProfileSelect');
    if (!select) return;

    select.innerHTML = profiles.map(p => `
        <option value="${p.id}" ${p.id === selectedProfileId ? 'selected' : ''}>
            ${escapeHtml(p.name)}
        </option>
    `).join('');
}

function renderModuleOptions() {
    const select = document.getElementById('moduleFilterSelect');
    if (!select) return;

    const list = [
        { id: 'all', name: '✨ All Objects & Modules' },
        ...MODULE_OPTIONS.map(m => ({ id: m.id, name: `📁 ${m.name}` }))
    ];

    if (customObjectsList.length > 0) {
        customObjectsList.forEach(co => {
            const modKey = co.api_name ? `obj_${co.api_name}` : `co_${co.id}`;
            list.push({ id: modKey, name: `🧩 ${co.label} (Custom Object)` });
        });
    }

    select.innerHTML = list.map(opt => `
        <option value="${opt.id}" ${opt.id === fieldRightsModule ? 'selected' : ''}>
            ${escapeHtml(opt.name)}
        </option>
    `).join('');
}

function handleProfileChange(newProfileId) {
    selectedProfileId = newProfileId;
    localStorage.setItem(STORAGE_SELECTED_PROFILE_KEY, selectedProfileId);
    loadFieldPermissionsForProfile(selectedProfileId);
}

function handleModuleChange(newModule) {
    fieldRightsModule = newModule;
    renderFlsRows();
}

function handleSearchChange(query) {
    fieldRightsSearch = query;
    const clearBtn = document.getElementById('clearSearchBtn');
    if (clearBtn) {
        clearBtn.style.display = query ? 'block' : 'none';
    }
    renderFlsRows();
}

function handleClearSearch() {
    fieldRightsSearch = '';
    const input = document.getElementById('fieldSearchInput');
    if (input) input.value = '';
    const clearBtn = document.getElementById('clearSearchBtn');
    if (clearBtn) clearBtn.style.display = 'none';
    renderFlsRows();
}

function setQuickFilter(filter) {
    fieldRightsFilter = filter;

    const pills = {
        all: document.getElementById('filterPillAll'),
        sensitive: document.getElementById('filterPillSensitive'),
        custom: document.getElementById('filterPillCustom'),
        restricted: document.getElementById('filterPillRestricted')
    };

    Object.keys(pills).forEach(k => {
        const btn = pills[k];
        if (!btn) return;
        if (k === filter) {
            btn.className = 'btn btn-sm btn-primary';
            btn.style.border = '';
            btn.style.color = '';
        } else {
            btn.className = 'btn btn-sm btn-ghost';
            if (k === 'sensitive') {
                btn.style.border = '1px solid rgba(245, 158, 11, 0.27)';
                btn.style.color = '#d97706';
            } else if (k === 'custom') {
                btn.style.border = '1px solid rgba(99, 102, 241, 0.27)';
                btn.style.color = '#4f46e5';
            } else if (k === 'restricted') {
                btn.style.border = '1px solid rgba(239, 68, 68, 0.27)';
                btn.style.color = '#dc2626';
            }
        }
    });

    renderFlsRows();
}

function loadFieldPermissionsForProfile(profileId) {
    try {
        const stored = localStorage.getItem(STORAGE_FIELD_PERMS_PREFIX + profileId);
        if (stored) {
            fieldPerms = JSON.parse(stored);
        } else {
            fieldPerms = getDefaultFieldPermsForProfile(profileId);
        }
    } catch (e) {
        fieldPerms = getDefaultFieldPermsForProfile(profileId);
    }

    renderFlsRows();
}

function getDefaultFieldPermsForProfile(profileId) {
    const list = [];
    if (profileId === 'prof-admin') {
        return list; // Default fallback is Read/Write for all
    }

    if (profileId === 'prof-sales') {
        list.push({ module: 'accounts', field_name: 'credit_limit', permission: 'Read-Only' });
        list.push({ module: 'accounts', field_name: 'annual_revenue', permission: 'Read-Only' });
        list.push({ module: 'purchase_deals', field_name: 'amount', permission: 'Hidden' });
        list.push({ module: 'wires', field_name: 'amount', permission: 'Hidden' });
    } else if (profileId === 'prof-procurement') {
        list.push({ module: 'opportunities', field_name: 'amount', permission: 'Hidden' });
        list.push({ module: 'opportunities', field_name: 'probability', permission: 'Hidden' });
        list.push({ module: 'wires', field_name: 'amount', permission: 'Hidden' });
    } else if (profileId === 'prof-auditor') {
        MODULE_OPTIONS.forEach(m => {
            const stds = STANDARD_FIELDS[m.id] || [];
            stds.forEach(f => {
                list.push({ module: m.id, field_name: f.name, permission: 'Read-Only' });
            });
        });
    }

    return list;
}

function getComputedFieldRightsList() {
    const items = [];
    const targetModules = fieldRightsModule === 'all'
        ? MODULE_OPTIONS.map(m => m.id)
        : [fieldRightsModule];

    // Standard fields
    targetModules.forEach(modId => {
        const modObj = MODULE_OPTIONS.find(m => m.id === modId);
        if (modObj) {
            const stds = STANDARD_FIELDS[modId] || [];
            stds.forEach(f => {
                const isSens = SENSITIVE_FIELDS_SET.has(`${modId}.${f.name}`);
                items.push({
                    mod: modId,
                    modLabel: modObj.name,
                    field: f.name,
                    label: f.label,
                    dataType: f.type,
                    description: f.description,
                    isCustom: false,
                    isSensitive: isSens
                });
            });
        }
    });

    // Custom fields
    allCustomFields.forEach(cf => {
        if (fieldRightsModule === 'all' || cf.module === fieldRightsModule) {
            const modObj = MODULE_OPTIONS.find(m => m.id === cf.module);
            const co = customObjectsList.find(c => c.api_name === cf.module || `obj_${c.api_name}` === cf.module);
            const modLabel = modObj ? modObj.name : (co ? `${co.label} (Custom Object)` : cf.module);
            const isSens = SENSITIVE_FIELDS_SET.has(`${cf.module}.${cf.field_name}`);
            items.push({
                mod: cf.module,
                modLabel,
                field: cf.field_name,
                label: cf.field_label,
                dataType: cf.data_type,
                description: cf.help_text || 'Custom field',
                isCustom: true,
                isSensitive: isSens
            });
        }
    });

    let filtered = items;

    // Search filter
    if (fieldRightsSearch.trim()) {
        const q = fieldRightsSearch.toLowerCase().trim();
        filtered = filtered.filter(item =>
            item.label.toLowerCase().includes(q) ||
            item.field.toLowerCase().includes(q) ||
            item.modLabel.toLowerCase().includes(q) ||
            item.dataType.toLowerCase().includes(q)
        );
    }

    // Quick filter pills
    if (fieldRightsFilter === 'sensitive') {
        filtered = filtered.filter(item => item.isSensitive);
    } else if (fieldRightsFilter === 'custom') {
        filtered = filtered.filter(item => item.isCustom);
    } else if (fieldRightsFilter === 'restricted') {
        filtered = filtered.filter(item => {
            const perm = fieldPerms.find(fp => fp.module === item.mod && fp.field_name === item.field)?.permission;
            return perm === 'Read-Only' || perm === 'Hidden';
        });
    }

    return filtered;
}

function renderFlsRows() {
    const tbody = document.getElementById('flsTableBody');
    if (!tbody) return;

    const list = getComputedFieldRightsList();

    // Update Counts Badges
    const rwCount = list.filter(i => (fieldPerms.find(fp => fp.module === i.mod && fp.field_name === i.field)?.permission || 'Read/Write') === 'Read/Write').length;
    const roCount = list.filter(i => fieldPerms.find(fp => fp.module === i.mod && fp.field_name === i.field)?.permission === 'Read-Only').length;
    const hdCount = list.filter(i => fieldPerms.find(fp => fp.module === i.mod && fp.field_name === i.field)?.permission === 'Hidden').length;

    document.getElementById('totalCountBadge').textContent = list.length;
    document.getElementById('rwCountBadge').textContent = `${rwCount} Read/Write`;
    document.getElementById('roCountBadge').textContent = `${roCount} Read-Only`;
    document.getElementById('hdCountBadge').textContent = `${hdCount} Hidden`;

    if (list.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="4" style="text-align: center; padding: 3rem; color: var(--zinc-500);">
                    <div style="display: inline-flex; flex-direction: column; alignItems: center; gap: 0.5rem;">
                        <i data-lucide="filter" style="width: 28px; height: 28px; opacity: 0.4; margin: 0 auto;"></i>
                        <div style="font-weight: 600; font-size: 0.9rem; color: var(--zinc-800); margin-top: 0.35rem;">No fields found matching criteria</div>
                        <div style="font-size: 0.75rem; color: var(--zinc-500);">
                            Try selecting another object in the dropdown above or clearing your search query.
                        </div>
                    </div>
                </td>
            </tr>
        `;
        if (window.lucide) lucide.createIcons();
        return;
    }

    tbody.innerHTML = list.map(item => {
        const currentPerm = fieldPerms.find(fp => fp.module === item.mod && fp.field_name === item.field)?.permission || 'Read/Write';
        const isRw = currentPerm === 'Read/Write';
        const isRo = currentPerm === 'Read-Only';
        const isHd = currentPerm === 'Hidden';

        const rowBg = isHd ? 'background: #fef2f233;' : (isRo ? 'background: #fffbeb33;' : '');

        return `
            <tr style="${rowBg}">
                <td>
                    <span style="
                        font-size: 0.72rem;
                        font-weight: 600;
                        text-transform: capitalize;
                        padding: 0.2rem 0.5rem;
                        border-radius: 4px;
                        background: var(--zinc-100);
                        color: var(--zinc-800);
                        border: 1px solid var(--zinc-200);
                        display: inline-block;
                    ">
                        ${escapeHtml(item.modLabel)}
                    </span>
                </td>
                <td>
                    <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                        <span style="color: var(--zinc-950); font-weight: 600; font-size: 0.8125rem;">
                            ${escapeHtml(item.label)}
                        </span>
                        ${item.isSensitive ? `
                            <span style="
                                font-size: 0.65rem;
                                font-weight: 700;
                                background: #fef2f2;
                                color: #b91c1c;
                                border: 1px solid #fecaca;
                                border-radius: 9999px;
                                padding: 0.1rem 0.45rem;
                                display: inline-flex;
                                align-items: center;
                                gap: 0.2rem;
                            ">
                                ⚠️ Sensitive
                            </span>
                        ` : ''}
                    </div>
                    <div style="font-size: 0.7rem; color: var(--zinc-400); font-family: monospace; margin-top: 0.1rem;">
                        ${escapeHtml(item.field)}
                    </div>
                    ${item.description ? `
                        <div style="font-size: 0.68rem; color: var(--zinc-500); margin-top: 0.1rem;">
                            ${escapeHtml(item.description)}
                        </div>
                    ` : ''}
                </td>
                <td>
                    <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                        <div>
                            <span style="
                                font-size: 0.68rem;
                                font-weight: 600;
                                padding: 0.15rem 0.4rem;
                                border-radius: 4px;
                                background: ${item.isCustom ? '#e0e7ff' : '#f4f4f5'};
                                color: ${item.isCustom ? '#4338ca' : '#52525b'};
                                border: 1px solid ${item.isCustom ? '#c7d2fe' : '#e4e4e7'};
                            ">
                                ${item.isCustom ? 'Custom Field' : 'Standard Field'}
                            </span>
                        </div>
                        <span style="font-size: 0.7rem; color: var(--zinc-500); font-weight: 500;">
                            ${escapeHtml(item.dataType)}
                        </span>
                    </div>
                </td>
                <td>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <div style="flex: 1;">
                            <select
                                class="form-select"
                                onchange="updateFieldPerm('${item.mod}', '${item.field}', this.value)"
                                style="
                                    padding: 0.35rem 0.65rem;
                                    font-size: 0.75rem;
                                    width: 100%;
                                    height: 32px;
                                    font-weight: 600;
                                    border-color: ${isRw ? '#a7f3d0' : (isRo ? '#fde68a' : '#fecaca')};
                                    background: ${isRw ? '#f0fdf4' : (isRo ? '#fefce8' : '#fef2f2')};
                                    color: ${isRw ? '#15803d' : (isRo ? '#b45309' : '#b91c1c')};
                                "
                            >
                                <option value="Read/Write" ${isRw ? 'selected' : ''}>Read / Write (Full Access)</option>
                                <option value="Read-Only" ${isRo ? 'selected' : ''}>Read-Only (Cannot Edit)</option>
                                <option value="Hidden" ${isHd ? 'selected' : ''}>Hidden (Masked / Invisible)</option>
                            </select>
                        </div>
                        <div style="width: 22px; display: flex; justify-content: center;">
                            ${isRw ? `<span title="Full Read &amp; Write Access"><i data-lucide="check" style="width: 16px; height: 16px; color: #16a34a;"></i></span>` : ''}
                            ${isRo ? `<span title="Field is Read-Only for this profile"><i data-lucide="lock" style="width: 15px; height: 15px; color: #d97706;"></i></span>` : ''}
                            ${isHd ? `<span title="Field is completely Hidden for this profile"><i data-lucide="eye-off" style="width: 15px; height: 15px; color: #dc2626;"></i></span>` : ''}
                        </div>
                    </div>
                </td>
            </tr>
        `;
    }).join('');

    if (window.lucide) lucide.createIcons();
}

function updateFieldPerm(mod, fieldName, newPerm) {
    const idx = fieldPerms.findIndex(p => p.module === mod && p.field_name === fieldName);
    if (idx >= 0) {
        fieldPerms[idx].permission = newPerm;
    } else {
        fieldPerms.push({
            id: '',
            tenant_id: '',
            profile_id: selectedProfileId,
            module: mod,
            field_name: fieldName,
            permission: newPerm
        });
    }

    renderFlsRows();
}

function handleBulkSetFieldPerms(newPerm) {
    const list = getComputedFieldRightsList();
    if (list.length === 0) return;

    list.forEach(item => {
        const idx = fieldPerms.findIndex(p => p.module === item.mod && p.field_name === item.field);
        if (idx >= 0) {
            fieldPerms[idx].permission = newPerm;
        } else {
            fieldPerms.push({
                id: '',
                tenant_id: '',
                profile_id: selectedProfileId,
                module: item.mod,
                field_name: item.field,
                permission: newPerm
            });
        }
    });

    renderFlsRows();
    showFeedback(`All visible fields set to ${newPerm}!`, 'success');
}

function handleSavePermissions() {
    const saveBtn = document.getElementById('saveFlsBtn');
    const saveText = document.getElementById('saveFlsText');

    saveBtn.disabled = true;
    saveText.textContent = 'Saving Changes...';

    setTimeout(() => {
        try {
            localStorage.setItem(STORAGE_FIELD_PERMS_PREFIX + selectedProfileId, JSON.stringify(fieldPerms));
            showFeedback('Permissions updated successfully!', 'success');
        } catch (e) {
            alert('Error saving field permissions: ' + e.message);
        } finally {
            saveBtn.disabled = false;
            saveText.textContent = 'Save Field Security';
        }
    }, 250);
}

let feedbackTimeout = null;
function showFeedback(msg, type = 'success') {
    const banner = document.getElementById('feedbackBanner');
    const textEl = document.getElementById('feedbackText');
    const iconEl = document.getElementById('feedbackIcon');

    textEl.textContent = msg;
    if (type === 'success') {
        banner.style.background = '#ecfdf5';
        banner.style.border = '1px solid #a7f3d0';
        banner.style.color = '#047857';
        iconEl.setAttribute('data-lucide', 'check-circle-2');
    } else {
        banner.style.background = '#fef2f2';
        banner.style.border = '1px solid #fecaca';
        banner.style.color = '#b91c1c';
        iconEl.setAttribute('data-lucide', 'alert-circle');
    }

    banner.style.display = 'flex';
    if (window.lucide) lucide.createIcons();

    if (feedbackTimeout) clearTimeout(feedbackTimeout);
    feedbackTimeout = setTimeout(() => {
        banner.style.display = 'none';
    }, 4000);
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

document.addEventListener('DOMContentLoaded', initFieldRights);
</script>
@endsection
