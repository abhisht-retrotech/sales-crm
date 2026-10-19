@extends('layouts.app')

@section('title', 'Accounts & Organizations — DealSync Enterprise CRM')

@section('content')
<!-- Centered Max-Width Container matching React page-wrapper (max-width: 1240px, padding: 2.25rem 2.5rem) -->
<div id="accounts-page-root" class="max-w-[1240px] mx-auto w-full px-6 sm:px-10 py-7 space-y-6 animate-fade-in">
    <!-- Top Action Header -->
    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4 pb-2 border-b border-zinc-200 dark:border-zinc-800">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800">
                    Master Organization Ledger
                </span>
                <span class="text-xs text-zinc-500 dark:text-zinc-400">
                    360° Consolidated Operations Graph
                </span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-950 dark:text-zinc-100">
                Accounts
            </h1>
            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 max-w-2xl">
                Master company ledger consolidating sell-side customer contracts, supplier procurement POs, credit lines, logistics, and financial invoices. Dynamic custom fields synced with field-level permissions.
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap shrink-0">
            <!-- Filter Button -->
            <button
                type="button"
                onclick="window.openAccountFilterModal()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs cursor-pointer"
            >
                <i data-lucide="filter" class="w-3.5 h-3.5 text-zinc-500"></i>
                <span>Filter</span>
                <span id="accounts-filter-badge" class="hidden px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-blue-600 text-white">0</span>
            </button>

            <!-- Export CSV Button -->
            <button
                type="button"
                onclick="window.exportAccountsCsv()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs cursor-pointer"
            >
                <i data-lucide="download" class="w-3.5 h-3.5 text-zinc-500"></i>
                <span>Export CSV</span>
            </button>

            <!-- Add Account Primary Action -->
            <button
                type="button"
                onclick="window.openAccountModal('create')"
                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm transition-colors cursor-pointer"
            >
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Add Account</span>
            </button>
        </div>
    </div>

    <!-- Top KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 border border-zinc-200 dark:border-zinc-800 shadow-xs">
            <div class="text-[11px] font-semibold text-zinc-500 uppercase tracking-wider">TOTAL ORGANIZATIONS</div>
            <div id="kpi-total-accounts" class="text-2xl font-extrabold text-zinc-950 dark:text-zinc-100 mt-1 tracking-tight">
                {{ count($initialAccounts ?? []) }} Accounts
            </div>
            <span class="text-[11px] text-emerald-600 dark:text-emerald-400 font-semibold mt-1 inline-block">100% Verified RLS Tenants</span>
        </div>

        <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 border border-zinc-200 dark:border-zinc-800 shadow-xs">
            <div class="text-[11px] font-semibold text-zinc-500 uppercase tracking-wider">ACTIVE CUSTOMERS</div>
            <div id="kpi-active-customers" class="text-2xl font-extrabold text-blue-600 dark:text-blue-400 mt-1 tracking-tight">
                0 Clients
            </div>
            <span class="text-[11px] text-zinc-500 font-medium mt-1 inline-block">Direct Revenue Contracts</span>
        </div>

        <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 border border-zinc-200 dark:border-zinc-800 shadow-xs">
            <div class="text-[11px] font-semibold text-zinc-500 uppercase tracking-wider">SUPPLIERS &amp; VENDORS</div>
            <div id="kpi-suppliers-vendors" class="text-2xl font-extrabold text-amber-600 dark:text-amber-400 mt-1 tracking-tight">
                0 Partners
            </div>
            <span class="text-[11px] text-zinc-500 font-medium mt-1 inline-block">Procurement &amp; Supply Chain</span>
        </div>

        <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 border border-zinc-200 dark:border-zinc-800 shadow-xs">
            <div class="text-[11px] font-semibold text-zinc-500 uppercase tracking-wider">TOTAL CREDIT LINE</div>
            <div id="kpi-total-credit" class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-1 tracking-tight">
                $0
            </div>
            <span class="text-[11px] text-zinc-500 font-medium mt-1 inline-block">Aggregate Net Terms Extended</span>
        </div>
    </div>

    <!-- Quick Classification Filter Chips Container -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1" id="accounts-type-chips-container">
        <!-- Rendered dynamically by accounts.js -->
    </div>

    <!-- Toolbar: Search, View Switcher (Table vs Kanban), Customize, Filter -->
    <div class="bg-white dark:bg-zinc-900 rounded-xl p-3 border border-zinc-200 dark:border-zinc-800 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-3">
        <!-- Left: Search Box -->
        <div class="flex items-center gap-3 w-full sm:w-auto flex-1 max-w-md">
            <div class="relative w-full">
                <i data-lucide="search" class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                <input
                    type="text"
                    id="accounts-search-input"
                    placeholder="Search accounts by name, industry, phone, website..."
                    class="w-full pl-9 pr-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/60 text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                >
            </div>
            <span id="accounts-records-count-label" class="text-xs text-zinc-500 dark:text-zinc-400 whitespace-nowrap">
                Showing <strong>0</strong> records
            </span>
        </div>

        <!-- Right: View Mode Toggle & Customize -->
        <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
            <!-- View Mode Switcher -->
            <div class="flex items-center p-0.5 rounded-lg bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-xs">
                <button
                    type="button"
                    id="btn-account-view-table"
                    onclick="window.switchAccountsViewMode('table')"
                    class="flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-md bg-white text-zinc-950 shadow-xs dark:bg-zinc-800 dark:text-zinc-100 cursor-pointer"
                >
                    <i data-lucide="table" class="w-3.5 h-3.5"></i>
                    <span>Table</span>
                </button>
                <button
                    type="button"
                    id="btn-account-view-kanban"
                    onclick="window.switchAccountsViewMode('kanban')"
                    class="flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-md text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-200 cursor-pointer"
                >
                    <i data-lucide="kanban" class="w-3.5 h-3.5"></i>
                    <span>Kanban</span>
                </button>
            </div>

            <!-- Customize Visible Fields Button -->
            <button
                type="button"
                onclick="window.openCustomizeFieldsModal()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs cursor-pointer"
                title="Customize Visible Columns / Fields"
            >
                <i data-lucide="sliders" class="w-3.5 h-3.5 text-zinc-500"></i>
                <span>Customize</span>
            </button>

            <!-- Filter Button in Toolbar -->
            <button
                type="button"
                onclick="window.openAccountFilterModal()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs cursor-pointer"
            >
                <i data-lucide="filter" class="w-3.5 h-3.5 text-zinc-500"></i>
                <span>Filter</span>
            </button>
        </div>
    </div>

    <!-- Active Filter Chips Bar -->
    <div id="accounts-filter-chips-bar" class="hidden">
        <!-- Rendered dynamically by accounts.js -->
    </div>

    <!-- VIEW CONTAINER 1: Table View -->
    <div id="accounts-table-view-root">
        <!-- Rendered dynamically by accounts.js -->
    </div>

    <!-- VIEW CONTAINER 2: Kanban View -->
    <div id="accounts-kanban-view-root" class="hidden">
        <!-- Rendered dynamically by accounts.js -->
    </div>

    <!-- ========================================== -->
    <!-- MODAL 1: CREATE / EDIT ACCOUNT FORM MODAL   -->
    <!-- ========================================== -->
    <div id="account-form-modal" class="fixed inset-0 z-50 hidden bg-zinc-950/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
        <div class="bg-white dark:bg-zinc-900 rounded-2xl max-w-2xl w-full border border-zinc-200 dark:border-zinc-800 shadow-2xl overflow-hidden animate-scale-up my-8">
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between bg-zinc-50/50 dark:bg-zinc-800/30">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                        <i data-lucide="building-2" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 id="modal-account-form-title" class="text-sm font-bold text-zinc-950 dark:text-zinc-100">
                            Create Organization Account
                        </h3>
                        <p class="text-[11px] text-zinc-500">Master company record with automated 360° graph linkage</p>
                    </div>
                </div>
                <button
                    type="button"
                    onclick="window.closeAccountModal()"
                    class="p-1 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition-colors cursor-pointer"
                >
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form id="account-record-form" onsubmit="window.handleAccountFormSubmit(event)" class="p-6 space-y-5 max-h-[80vh] overflow-y-auto">
                <!-- Section 1: Core Company Profile -->
                <div>
                    <h4 class="text-xs font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider mb-3 pb-1 border-b border-zinc-100 dark:border-zinc-800 flex items-center gap-1.5">
                        <i data-lucide="building" class="w-3.5 h-3.5 text-zinc-400"></i> Primary Identity
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="space-y-1 sm:col-span-2">
                            <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                Account Name <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                name="name"
                                id="acc-field-name"
                                required
                                placeholder="e.g. Acme Global Industries Inc."
                                class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/60 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            >
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                Classification <span class="text-red-500">*</span>
                            </label>
                            <select
                                name="type"
                                id="acc-field-type"
                                required
                                class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/60 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            >
                                <option value="Customer">Customer</option>
                                <option value="Vendor">Vendor / Supplier</option>
                                <option value="Prospect">Prospect</option>
                                <option value="Partner">Strategic Partner</option>
                            </select>
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                Industry Sector
                            </label>
                            <input
                                type="text"
                                name="industry"
                                id="acc-field-industry"
                                placeholder="e.g. Software & Cloud, Semiconductors"
                                class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/60 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            >
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                Corporate Website
                            </label>
                            <input
                                type="text"
                                name="website"
                                id="acc-field-website"
                                placeholder="e.g. https://www.acmeglobal.com"
                                class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/60 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            >
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                Corporate Phone
                            </label>
                            <input
                                type="text"
                                name="phone"
                                id="acc-field-phone"
                                placeholder="e.g. +1 (415) 890-1200"
                                class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/60 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            >
                        </div>
                    </div>
                </div>

                <!-- Section 2: Financial Terms & Credit Line -->
                <div>
                    <h4 class="text-xs font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider mb-3 pb-1 border-b border-zinc-100 dark:border-zinc-800 flex items-center gap-1.5">
                        <i data-lucide="dollar-sign" class="w-3.5 h-3.5 text-zinc-400"></i> Commercial &amp; Financial Terms
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                Default Currency
                            </label>
                            <select
                                name="currency"
                                id="acc-field-currency"
                                class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/60 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            >
                                <option value="USD">USD ($)</option>
                                <option value="EUR">EUR (€)</option>
                                <option value="GBP">GBP (£)</option>
                                <option value="INR">INR (₹)</option>
                                <option value="CAD">CAD ($)</option>
                                <option value="AUD">AUD ($)</option>
                                <option value="JPY">JPY (¥)</option>
                                <option value="AED">AED</option>
                                <option value="SGD">SGD ($)</option>
                                <option value="CHF">CHF</option>
                            </select>
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                Payment Terms
                            </label>
                            <select
                                name="payment_terms"
                                id="acc-field-payment_terms"
                                class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/60 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            >
                                <option value="Net 15">Net 15</option>
                                <option value="Net 30" selected>Net 30</option>
                                <option value="Net 45">Net 45</option>
                                <option value="Net 60">Net 60</option>
                                <option value="Advance Wire">Advance Wire</option>
                                <option value="Due on Receipt">Due on Receipt</option>
                            </select>
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                Credit Limit
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400 text-xs">$</span>
                                <input
                                    type="number"
                                    name="credit_limit"
                                    id="acc-field-credit_limit"
                                    step="any"
                                    placeholder="0.00"
                                    class="w-full pl-7 pr-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/60 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                >
                            </div>
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                Annual Revenue
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400 text-xs">$</span>
                                <input
                                    type="number"
                                    name="annual_revenue"
                                    id="acc-field-annual_revenue"
                                    step="any"
                                    placeholder="0.00"
                                    class="w-full pl-7 pr-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/60 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                >
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Addresses & Owner -->
                <div>
                    <h4 class="text-xs font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider mb-3 pb-1 border-b border-zinc-100 dark:border-zinc-800 flex items-center gap-1.5">
                        <i data-lucide="map-pin" class="w-3.5 h-3.5 text-zinc-400"></i> Location &amp; Ownership
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="space-y-1 sm:col-span-2">
                            <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                Account Owner / Representative
                            </label>
                            <select
                                name="assigned_to"
                                id="acc-field-assigned_to"
                                class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/60 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            >
                                <option value="">Select account manager...</option>
                                @foreach($users ?? [] as $u)
                                    <option value="{{ $u->name }}">{{ $u->name }} ({{ $u->email }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                Billing Address
                            </label>
                            <textarea
                                name="billing_address"
                                id="acc-field-billing_address"
                                rows="2"
                                placeholder="Full corporate legal billing address"
                                class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/60 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            ></textarea>
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                Shipping / Logistics Address
                            </label>
                            <textarea
                                name="shipping_address"
                                id="acc-field-shipping_address"
                                rows="2"
                                placeholder="Warehouse or delivery logistics address"
                                class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/60 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            ></textarea>
                        </div>

                        <div class="space-y-1 sm:col-span-2">
                            <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                Account Notes &amp; Special Contract Instructions
                            </label>
                            <textarea
                                name="notes"
                                id="acc-field-notes"
                                rows="2"
                                placeholder="Key enterprise background, SLA requirements, renewal dates..."
                                class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/60 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            ></textarea>
                        </div>
                    </div>
                </div>

                <!-- Section 4: Dynamic Custom Fields (with Field-Level Security) -->
                <div>
                    <h4 class="text-xs font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider mb-3 pb-1 border-b border-zinc-100 dark:border-zinc-800 flex items-center justify-between">
                        <span class="flex items-center gap-1.5">
                            <i data-lucide="sliders" class="w-3.5 h-3.5 text-zinc-400"></i> Dynamic Custom Fields
                        </span>
                        <span class="text-[10px] text-zinc-400 font-normal lowercase">governed by field-level permissions</span>
                    </h4>
                    <div id="account-custom-fields-form-container" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <!-- Populated dynamically by accounts.js with read-only badges and hidden filtering -->
                    </div>
                </div>

                <!-- Form Action Buttons -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                    <button
                        type="button"
                        onclick="window.closeAccountModal()"
                        class="px-4 py-2 text-xs font-semibold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-lg transition-colors cursor-pointer"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        id="btn-submit-account-form"
                        class="px-5 py-2 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm transition-colors cursor-pointer"
                    >
                        Create Account
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- MODAL 2: 360 DEGREE PROFILE MODAL          -->
    <!-- ========================================== -->
    <div id="account-360-modal" class="fixed inset-0 z-50 hidden bg-zinc-950/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
        <div class="bg-white dark:bg-zinc-900 rounded-2xl max-w-4xl w-full border border-zinc-200 dark:border-zinc-800 shadow-2xl overflow-hidden animate-scale-up my-8">
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between bg-zinc-50/50 dark:bg-zinc-800/30">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-sm">
                        <i data-lucide="building-2" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 id="modal-360-title" class="text-base font-extrabold text-zinc-950 dark:text-zinc-100 tracking-tight">
                            Account 360° Profile
                        </h3>
                        <div id="modal-360-subtitle" class="text-xs text-zinc-500 mt-0.5">
                            Consolidated Master Ledger View
                        </div>
                    </div>
                </div>
                <button
                    type="button"
                    onclick="window.close360Modal()"
                    class="p-1 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition-colors cursor-pointer"
                >
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <!-- Apple / Linear Segmented Tabs Bar -->
            <div class="px-6 py-2 bg-zinc-100/70 dark:bg-zinc-800/40 border-b border-zinc-200 dark:border-zinc-800 overflow-x-auto flex items-center gap-1.5" id="modal-360-segmented-tabs">
                <!-- Tabs rendered by accounts.js -->
            </div>

            <!-- Tab Body Container -->
            <div class="p-6 max-h-[70vh] overflow-y-auto" id="modal-360-tab-content">
                <!-- Rendered dynamically by accounts.js -->
            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-3.5 bg-zinc-50 dark:bg-zinc-800/40 border-t border-zinc-200 dark:border-zinc-800 flex items-center justify-between">
                <div class="text-xs text-zinc-400">
                    Consolidated 360° master ledger graph linked
                </div>
                <button
                    type="button"
                    onclick="window.close360Modal()"
                    class="px-4 py-1.5 text-xs font-semibold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-700 rounded-lg transition-colors cursor-pointer"
                >
                    Close 360° Profile
                </button>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- MODAL 3: TRANSFER ACCOUNT OWNERSHIP MODAL   -->
    <!-- ========================================== -->
    <div id="account-transfer-modal" class="fixed inset-0 z-50 hidden bg-zinc-950/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white dark:bg-zinc-900 rounded-2xl max-w-md w-full border border-zinc-200 dark:border-zinc-800 shadow-2xl overflow-hidden animate-scale-up">
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i data-lucide="user-check" class="w-4 h-4 text-indigo-600"></i>
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Transfer Account Ownership</h3>
                </div>
                <button type="button" onclick="window.closeTransferModal()" class="text-zinc-400 hover:text-zinc-600 cursor-pointer">
                    ✕
                </button>
            </div>
            <form onsubmit="window.submitAccountTransfer(event)" class="p-6 space-y-4">
                <div class="text-xs text-zinc-600 dark:text-zinc-400">
                    Reassign ownership of <strong id="transfer-account-name" class="text-zinc-900 dark:text-zinc-100"></strong> to another team member.
                </div>
                <div class="p-3 rounded-lg bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200 dark:border-zinc-700 text-xs">
                    <span class="text-zinc-400">Current Owner:</span>
                    <span id="transfer-current-owner" class="font-bold text-zinc-800 dark:text-zinc-200 ml-1"></span>
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                        New Account Owner <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="transfer-new-owner-select"
                        required
                        class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    >
                        <!-- Populated by accounts.js -->
                    </select>
                </div>
                <div class="flex items-center justify-end gap-3 pt-3 border-t border-zinc-200 dark:border-zinc-800">
                    <button
                        type="button"
                        onclick="window.closeTransferModal()"
                        class="px-3.5 py-1.5 text-xs font-semibold text-zinc-600 hover:bg-zinc-100 rounded-lg transition-colors cursor-pointer"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="px-4 py-1.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm transition-colors cursor-pointer"
                    >
                        Transfer Ownership
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- MODAL 4: CUSTOMIZE VISIBLE FIELDS MODAL     -->
    <!-- ========================================== -->
    <div id="account-customize-modal" class="fixed inset-0 z-50 hidden bg-zinc-950/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white dark:bg-zinc-900 rounded-2xl max-w-lg w-full border border-zinc-200 dark:border-zinc-800 shadow-2xl overflow-hidden animate-scale-up">
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i data-lucide="sliders" class="w-4 h-4 text-indigo-600"></i>
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Customize Visible Fields</h3>
                </div>
                <button type="button" onclick="window.closeCustomizeFieldsModal()" class="text-zinc-400 hover:text-zinc-600 cursor-pointer">
                    ✕
                </button>
            </div>
            <div class="p-6 space-y-4">
                <p class="text-xs text-zinc-500">
                    Select standard and dynamic custom fields to display in your active view. Fields hidden by your security profile are automatically excluded.
                </p>
                <div id="customize-fields-checkboxes-container" class="grid grid-cols-2 gap-2 max-h-64 overflow-y-auto p-1">
                    <!-- Populated dynamically by accounts.js -->
                </div>
                <div class="flex items-center justify-end gap-3 pt-3 border-t border-zinc-200 dark:border-zinc-800">
                    <button
                        type="button"
                        onclick="window.closeCustomizeFieldsModal()"
                        class="px-3.5 py-1.5 text-xs font-semibold text-zinc-600 hover:bg-zinc-100 rounded-lg transition-colors cursor-pointer"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        onclick="window.saveCustomizedFields()"
                        class="px-4 py-1.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm transition-colors cursor-pointer"
                    >
                        Save View Layout
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- MODAL 5: UNIVERSAL FILTER MODAL            -->
    <!-- ========================================== -->
    <div id="account-filter-modal" class="fixed inset-0 z-50 hidden bg-zinc-950/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white dark:bg-zinc-900 rounded-2xl max-w-md w-full border border-zinc-200 dark:border-zinc-800 shadow-2xl overflow-hidden animate-scale-up">
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i data-lucide="filter" class="w-4 h-4 text-blue-600"></i>
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Add Account Filter Rule</h3>
                </div>
                <button type="button" onclick="window.closeAccountFilterModal()" class="text-zinc-400 hover:text-zinc-600 cursor-pointer">
                    ✕
                </button>
            </div>
            <div class="p-6 space-y-4">
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Filter By Field</label>
                    <select
                        id="filter-rule-field-select"
                        class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100"
                    >
                        <!-- Populated by accounts.js -->
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Comparison Operator</label>
                    <select
                        id="filter-rule-operator-select"
                        class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100"
                    >
                        <option value="contains">contains</option>
                        <option value="equals">equals exactly</option>
                        <option value="starts_with">starts with</option>
                        <option value="greater_than">greater than (&gt;)</option>
                        <option value="less_than">less than (&lt;)</option>
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Value</label>
                    <input
                        type="text"
                        id="filter-rule-value-input"
                        placeholder="Enter search value..."
                        class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100"
                    >
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-zinc-200 dark:border-zinc-800">
                    <button
                        type="button"
                        onclick="window.closeAccountFilterModal()"
                        class="px-3.5 py-1.5 text-xs font-semibold text-zinc-600 hover:bg-zinc-100 rounded-lg transition-colors cursor-pointer"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        onclick="window.addAccountFilterRule()"
                        class="px-4 py-1.5 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm transition-colors cursor-pointer"
                    >
                        Apply Filter
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Safe JSON hydration for SPA and first-load rendering -->
@php
    $accountsHydrationData = [
        'initialAccounts' => $initialAccounts ?? [],
        'customFields' => $customFields ?? [],
        'fieldPermissions' => !empty($fieldPermissions) ? $fieldPermissions : (object)[],
        'users' => $users ?? [],
        'types' => $types ?? ['Customer', 'Vendor', 'Prospect', 'Partner'],
        'paymentTerms' => $paymentTerms ?? ['Net 15', 'Net 30', 'Net 45', 'Net 60', 'Advance Wire', 'Due on Receipt'],
        'currencies' => $currencies ?? ['USD', 'EUR', 'GBP', 'INR', 'CAD', 'AUD', 'JPY', 'AED', 'SGD', 'CHF'],
        'currentUserProfile' => $currentUserProfile ?? 'Administrator',
    ];
@endphp
<script>
    window.__ACCOUNTS_DATA__ = {!! json_encode($accountsHydrationData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};
</script>
<script src="{{ asset('js/accounts.js') }}?v=1.0"></script>
@endsection
