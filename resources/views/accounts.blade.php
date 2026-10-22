@extends('layouts.app')

@section('title', 'Accounts & Organizations — DealSync Enterprise CRM')

@section('content')
<style>
    /* Draggable Column Width Resizer with Centered Edge Handle & High-Visibility Highlight */
    .account-col-resizer,
    .table-col-resizer {
        position: absolute !important;
        top: 0 !important;
        right: -4px !important;
        width: 10px !important;
        height: 100% !important;
        cursor: col-resize !important;
        user-select: none !important;
        z-index: 40 !important;
        background: transparent !important;
    }
    .account-col-resizer::after,
    .table-col-resizer::after {
        content: '';
        position: absolute;
        top: 0;
        right: 4px;
        width: 2px;
        height: 100%;
        background-color: transparent;
        transition: background-color 0.15s ease;
    }
    .account-col-resizer:hover::after,
    .account-col-resizer.is-resizing::after,
    .table-col-resizer:hover::after,
    .table-col-resizer.is-resizing::after {
        background-color: #f97316 !important;
        box-shadow: 0 0 6px rgba(249, 115, 22, 0.6);
    }
    body.is-resizing-account-col,
    body.is-resizing-account-col * {
        cursor: col-resize !important;
        user-select: none !important;
    }
</style>

<!-- Main Container matching screenshot layout -->
<div id="accounts-page-root" class="max-w-[1360px] mx-auto w-full px-4 sm:px-8 py-6 space-y-5 animate-fade-in">
    <!-- Top Action Header matching user screenshot -->
    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4 mb-2">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-[#2d4b68] dark:text-zinc-100">
                Accounts
            </h1>
            <p class="text-sm text-slate-500 dark:text-zinc-400 mt-1 font-normal">
                Master organization records with 360° consolidated views across sales, procurement, freight, and invoices.
            </p>
        </div>

        <div class="shrink-0 pt-1">
            <span class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-medium bg-slate-100/90 dark:bg-zinc-800 text-slate-700 dark:text-zinc-300 border border-slate-200/90 dark:border-zinc-700 shadow-2xs">
                Accounts created via Lead Conversion
            </span>
        </div>
    </div>

    <!-- Toolbar Card with orange glowing border -->
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-orange-200/90 dark:border-orange-500/30 shadow-[0_4px_20px_-2px_rgba(251,146,60,0.12)] p-4 flex flex-col sm:flex-row items-center justify-between gap-4 mb-4">
        <!-- Left: Search Box -->
        <div class="relative w-full sm:w-96">
            <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/>
                <line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input
                type="text"
                id="accounts-search-input"
                placeholder="Search accounts by name, industry, phone..."
                class="w-full pl-10 pr-3.5 py-2 text-xs sm:text-sm rounded-xl border border-slate-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-slate-900 dark:text-zinc-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all shadow-2xs"
            >
        </div>

        <!-- Right: View Mode Toggle & Customize & Filter -->
        <div class="flex items-center gap-2.5 w-full sm:w-auto justify-end">
            <!-- View Mode Switcher -->
            <div class="flex items-center p-1 rounded-xl bg-slate-100/90 dark:bg-zinc-800 border border-slate-200/80 dark:border-zinc-700 text-xs">
                <button
                    type="button"
                    id="btn-account-view-table"
                    onclick="window.switchAccountsViewMode('table')"
                    class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-white dark:bg-zinc-700 text-slate-900 dark:text-zinc-100 shadow-2xs cursor-pointer"
                >
                    <svg class="w-3.5 h-3.5 text-slate-600 dark:text-zinc-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect width="18" height="18" x="3" y="3" rx="2"/><path d="M3 9h18"/><path d="M3 15h18"/><path d="M9 3v18"/><path d="M15 3v18"/>
                    </svg>
                    <span>Table</span>
                </button>
                <button
                    type="button"
                    id="btn-account-view-kanban"
                    onclick="window.switchAccountsViewMode('kanban')"
                    class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg text-slate-500 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-zinc-200 cursor-pointer"
                >
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect width="6" height="14" x="4" y="5" rx="1"/><rect width="6" height="10" x="14" y="5" rx="1"/>
                    </svg>
                    <span>Kanban</span>
                </button>
            </div>

            <!-- Customize Visible Fields Button -->
            <button
                type="button"
                onclick="window.openCustomizeFieldsModal()"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-slate-700 dark:text-zinc-200 hover:bg-slate-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs cursor-pointer"
                title="Customize Visible Columns / Fields"
            >
                <svg class="w-3.5 h-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="4" x2="4" y1="21" y2="14"/><line x1="4" x2="4" y1="10" y2="3"/><line x1="12" x2="12" y1="21" y2="12"/><line x1="12" x2="12" y1="8" y2="3"/><line x1="20" x2="20" y1="21" y2="16"/><line x1="20" x2="20" y1="12" y2="3"/><line x1="1" x2="7" y1="14" y2="14"/><line x1="9" x2="15" y1="8" y2="8"/><line x1="17" x2="23" y1="16" y2="16"/>
                </svg>
                <span>Customize</span>
            </button>

            <!-- Filter Button in Toolbar -->
            <button
                type="button"
                onclick="window.openAccountFilterModal()"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-slate-700 dark:text-zinc-200 hover:bg-slate-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs cursor-pointer"
            >
                <svg class="w-3.5 h-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                </svg>
                <span>Filter</span>
                <span id="accounts-filter-badge" class="hidden px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-blue-600 text-white">0</span>
            </button>
        </div>
    </div>

    <!-- Active Filter Chips Bar -->
    <div id="accounts-filter-chips-bar" class="hidden"></div>

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
    <!-- MODAL 2: FULLSCREEN ACCOUNT DETAIL MODAL (Matching Screenshot) -->
    <!-- ========================================== -->
    <div id="account-360-modal" class="fixed inset-0 z-50 hidden bg-white dark:bg-zinc-950 overflow-y-auto flex flex-col">
        <!-- Top App Bar matching screenshot -->
        <div class="sticky top-0 z-30 bg-white dark:bg-zinc-900 border-b border-slate-200/80 dark:border-zinc-800 px-6 sm:px-8 py-3 flex items-center justify-between shadow-2xs">
            <!-- Left: Back button, Category Badge, Title -->
            <div class="flex items-center gap-3">
                <button
                    type="button"
                    onclick="window.close360Modal()"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-slate-700 dark:text-zinc-200 hover:bg-slate-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs cursor-pointer"
                >
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    <span>Back to Accounts</span>
                </button>

                <span class="px-2 py-0.5 rounded text-[10px] font-bold tracking-wider uppercase text-purple-600 bg-purple-50 dark:bg-purple-950/40 dark:text-purple-300 border border-purple-100 dark:border-purple-800">
                    ACCOUNTS
                </span>

                <h2 id="modal-fullscreen-account-name" class="text-base sm:text-lg font-bold text-[#2d4b68] dark:text-zinc-100 truncate max-w-md">
                    Acme Corp SFDC
                </h2>
            </div>

            <!-- Right: Actions -->
            <div class="flex items-center gap-2">
                <button
                    type="button"
                    onclick="window.openTransferFromFullscreen()"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-sky-600 dark:text-sky-400 hover:bg-sky-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs cursor-pointer"
                >
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                    <span>Change Owner</span>
                </button>

                <button
                    type="button"
                    onclick="window.shareAccountRecord()"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-sky-600 dark:text-sky-400 hover:bg-sky-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs cursor-pointer"
                >
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
                    </svg>
                    <span>Share</span>
                </button>

                <button
                    type="button"
                    onclick="window.openAccountModal('edit', state.viewing360Account)"
                    class="px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs text-slate-500 dark:text-zinc-400 hover:bg-slate-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs cursor-pointer font-bold"
                    title="More options"
                >
                    ⋯
                </button>

                <button
                    type="button"
                    onclick="window.close360Modal()"
                    class="text-slate-400 hover:text-slate-600 dark:hover:text-zinc-200 text-xl font-light px-2 py-1 transition-colors cursor-pointer leading-none"
                    title="Close"
                >
                    &times;
                </button>
            </div>
        </div>

        <!-- Breadcrumbs bar matching screenshot -->
        <div class="px-6 sm:px-8 pt-4 pb-2 text-xs text-slate-400 dark:text-zinc-500 flex items-center gap-1.5">
            <span class="hover:underline cursor-pointer" onclick="window.close360Modal()">DealSync</span>
            <span>&rsaquo;</span>
            <span class="hover:underline cursor-pointer" onclick="window.close360Modal()">Accounts</span>
            <span>&rsaquo;</span>
            <span id="modal-breadcrumb-account-name" class="font-medium text-slate-700 dark:text-zinc-300">
                Acme Corp SFDC
            </span>
        </div>

        <!-- Main Body: Two Column Layout matching screenshot -->
        <div class="max-w-[1440px] w-full mx-auto px-6 sm:px-8 py-4 flex flex-col lg:flex-row gap-6 items-start flex-1">
            <!-- LEFT COLUMN (approx 68%): Account Detail, Custom Section, System Information -->
            <div class="flex-1 w-full space-y-5">
                <!-- Section 1: Account Detail -->
                <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-orange-200/90 dark:border-orange-500/30 shadow-[0_4px_20px_-2px_rgba(251,146,60,0.12)] overflow-hidden">
                    <!-- Collapsible Header -->
                    <div
                        onclick="window.toggleFullscreenSection('account-detail-body', 'account-detail-chevron')"
                        class="px-5 py-3.5 bg-white dark:bg-zinc-900 flex items-center justify-between cursor-pointer select-none"
                    >
                        <div class="flex items-center gap-2 font-bold text-xs sm:text-sm text-slate-900 dark:text-zinc-100">
                            <svg id="account-detail-chevron" class="w-4 h-4 text-slate-400 transition-transform duration-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                            <svg class="w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            </svg>
                            <span>Account Detail</span>
                        </div>
                    </div>

                    <!-- Account Detail 2-Column Fields Grid -->
                    <div id="account-detail-body" class="px-6 py-4 border-t border-slate-100 dark:border-zinc-800">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-4 text-xs">
                            <!-- Left Fields -->
                            <div class="space-y-4">
                                <!-- Account Name -->
                                <div class="flex items-center justify-between py-1 border-b border-slate-100/80 dark:border-zinc-800/80 group">
                                    <div>
                                        <div class="text-[11px] text-slate-400 dark:text-zinc-500 font-medium">* Account Name</div>
                                        <div id="detail-fs-name" class="font-semibold text-slate-900 dark:text-zinc-100 mt-0.5">Acme Corp SFDC</div>
                                    </div>
                                    <button type="button" onclick="window.editFieldInline('name')" class="text-slate-300 group-hover:text-slate-600 dark:group-hover:text-zinc-300 transition-colors p-1 cursor-pointer" title="Edit">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                                    </button>
                                </div>

                                <!-- Industry -->
                                <div class="flex items-center justify-between py-1 border-b border-slate-100/80 dark:border-zinc-800/80 group">
                                    <div>
                                        <div class="text-[11px] text-slate-400 dark:text-zinc-500 font-medium">Industry</div>
                                        <div id="detail-fs-industry" class="font-semibold text-slate-900 dark:text-zinc-100 mt-0.5">Software</div>
                                    </div>
                                    <button type="button" onclick="window.editFieldInline('industry')" class="text-slate-300 group-hover:text-slate-600 dark:group-hover:text-zinc-300 transition-colors p-1 cursor-pointer" title="Edit">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                                    </button>
                                </div>

                                <!-- Phone -->
                                <div class="flex items-center justify-between py-1 border-b border-slate-100/80 dark:border-zinc-800/80 group">
                                    <div>
                                        <div class="text-[11px] text-slate-400 dark:text-zinc-500 font-medium">Phone</div>
                                        <div id="detail-fs-phone" class="font-semibold text-slate-900 dark:text-zinc-100 mt-0.5">—</div>
                                    </div>
                                    <button type="button" onclick="window.editFieldInline('phone')" class="text-slate-300 group-hover:text-slate-600 dark:group-hover:text-zinc-300 transition-colors p-1 cursor-pointer" title="Edit">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                                    </button>
                                </div>

                                <!-- Billing Address -->
                                <div class="flex items-center justify-between py-1 border-b border-slate-100/80 dark:border-zinc-800/80 group">
                                    <div>
                                        <div class="text-[11px] text-slate-400 dark:text-zinc-500 font-medium">Billing Address</div>
                                        <div id="detail-fs-billing" class="font-semibold text-slate-900 dark:text-zinc-100 mt-0.5">—</div>
                                    </div>
                                    <button type="button" onclick="window.editFieldInline('billing_address')" class="text-slate-300 group-hover:text-slate-600 dark:group-hover:text-zinc-300 transition-colors p-1 cursor-pointer" title="Edit">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                                    </button>
                                </div>

                                <!-- Shipping Address -->
                                <div class="flex items-center justify-between py-1 border-b border-slate-100/80 dark:border-zinc-800/80 group">
                                    <div>
                                        <div class="text-[11px] text-slate-400 dark:text-zinc-500 font-medium">Shipping Address</div>
                                        <div id="detail-fs-shipping" class="font-semibold text-slate-900 dark:text-zinc-100 mt-0.5">—</div>
                                    </div>
                                    <button type="button" onclick="window.editFieldInline('shipping_address')" class="text-slate-300 group-hover:text-slate-600 dark:group-hover:text-zinc-300 transition-colors p-1 cursor-pointer" title="Edit">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                                    </button>
                                </div>

                                <!-- Payment Terms -->
                                <div class="flex items-center justify-between py-1 border-b border-slate-100/80 dark:border-zinc-800/80 group">
                                    <div>
                                        <div class="text-[11px] text-slate-400 dark:text-zinc-500 font-medium">Payment Terms</div>
                                        <div id="detail-fs-terms" class="font-semibold text-slate-900 dark:text-zinc-100 mt-0.5">Net 30</div>
                                    </div>
                                    <button type="button" onclick="window.editFieldInline('payment_terms')" class="text-slate-300 group-hover:text-slate-600 dark:group-hover:text-zinc-300 transition-colors p-1 cursor-pointer" title="Edit">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                                    </button>
                                </div>

                                <!-- Annual Revenue -->
                                <div class="flex items-center justify-between py-1 border-b border-slate-100/80 dark:border-zinc-800/80 group">
                                    <div>
                                        <div class="text-[11px] text-slate-400 dark:text-zinc-500 font-medium">Annual Revenue</div>
                                        <div id="detail-fs-revenue" class="font-semibold text-slate-900 dark:text-zinc-100 mt-0.5">$0.00</div>
                                    </div>
                                    <button type="button" onclick="window.editFieldInline('annual_revenue')" class="text-slate-300 group-hover:text-slate-600 dark:group-hover:text-zinc-300 transition-colors p-1 cursor-pointer" title="Edit">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Right Fields -->
                            <div class="space-y-4">
                                <!-- Account Type -->
                                <div class="flex items-center justify-between py-1 border-b border-slate-100/80 dark:border-zinc-800/80 group">
                                    <div>
                                        <div class="text-[11px] text-slate-400 dark:text-zinc-500 font-medium">* Account Type</div>
                                        <div id="detail-fs-type" class="font-semibold text-slate-900 dark:text-zinc-100 mt-0.5">Customer</div>
                                    </div>
                                    <button type="button" onclick="window.editFieldInline('type')" class="text-slate-300 group-hover:text-slate-600 dark:group-hover:text-zinc-300 transition-colors p-1 cursor-pointer" title="Edit">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                                    </button>
                                </div>

                                <!-- Website URL -->
                                <div class="flex items-center justify-between py-1 border-b border-slate-100/80 dark:border-zinc-800/80 group">
                                    <div>
                                        <div class="text-[11px] text-slate-400 dark:text-zinc-500 font-medium">Website URL</div>
                                        <div id="detail-fs-website" class="font-semibold text-sky-600 dark:text-sky-400 mt-0.5 truncate max-w-xs">
                                            <a href="https://acme.test" target="_blank" class="hover:underline">https://acme.test</a>
                                        </div>
                                    </div>
                                    <button type="button" onclick="window.editFieldInline('website')" class="text-slate-300 group-hover:text-slate-600 dark:group-hover:text-zinc-300 transition-colors p-1 cursor-pointer" title="Edit">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                                    </button>
                                </div>

                                <!-- Empty placeholder spacers matching screenshot alignment -->
                                <div class="py-1 border-b border-transparent">&nbsp;</div>
                                <div class="py-1 border-b border-transparent">&nbsp;</div>
                                <div class="py-1 border-b border-transparent">&nbsp;</div>

                                <!-- Credit Limit -->
                                <div class="flex items-center justify-between py-1 border-b border-slate-100/80 dark:border-zinc-800/80 group">
                                    <div>
                                        <div class="text-[11px] text-slate-400 dark:text-zinc-500 font-medium">Credit Limit</div>
                                        <div id="detail-fs-credit" class="font-semibold text-slate-900 dark:text-zinc-100 mt-0.5">$50,000.00</div>
                                    </div>
                                    <button type="button" onclick="window.editFieldInline('credit_limit')" class="text-slate-300 group-hover:text-slate-600 dark:group-hover:text-zinc-300 transition-colors p-1 cursor-pointer" title="Edit">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Custom Section -->
                <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-orange-200/90 dark:border-orange-500/30 shadow-[0_4px_20px_-2px_rgba(251,146,60,0.12)] overflow-hidden">
                    <div
                        onclick="window.toggleFullscreenSection('custom-section-body', 'custom-section-chevron')"
                        class="px-5 py-3.5 bg-white dark:bg-zinc-900 flex items-center justify-between cursor-pointer select-none"
                    >
                        <div class="flex items-center gap-2 font-bold text-xs sm:text-sm text-slate-900 dark:text-zinc-100">
                            <svg id="custom-section-chevron" class="w-4 h-4 text-slate-400 transition-transform duration-200 -rotate-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                            <svg class="w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>
                            </svg>
                            <span>Custom Section</span>
                        </div>
                    </div>
                    <div id="custom-section-body" class="hidden px-6 py-4 border-t border-slate-100 dark:border-zinc-800">
                        <div id="detail-fs-custom-fields" class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                            <span class="text-slate-400 italic">No custom fields defined.</span>
                        </div>
                    </div>
                </div>

                <!-- Section 3: System Information -->
                <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-orange-200/90 dark:border-orange-500/30 shadow-[0_4px_20px_-2px_rgba(251,146,60,0.12)] overflow-hidden">
                    <div
                        onclick="window.toggleFullscreenSection('system-info-body', 'system-info-chevron')"
                        class="px-5 py-3.5 bg-white dark:bg-zinc-900 flex items-center justify-between cursor-pointer select-none"
                    >
                        <div class="flex items-center gap-2 font-bold text-xs sm:text-sm text-slate-900 dark:text-zinc-100">
                            <svg id="system-info-chevron" class="w-4 h-4 text-slate-400 transition-transform duration-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                            <svg class="w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            </svg>
                            <span>System Information</span>
                        </div>
                    </div>
                    <div id="system-info-body" class="px-6 py-4 border-t border-slate-100 dark:border-zinc-800 text-xs">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Current Owner -->
                            <div>
                                <div class="text-[11px] text-slate-400 dark:text-zinc-500 flex items-center gap-1 font-medium">
                                    <svg class="w-3.5 h-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                    <span>Current Owner</span>
                                </div>
                                <div class="flex items-center gap-2 mt-1">
                                    <span id="detail-fs-owner" class="font-bold text-slate-900 dark:text-zinc-100">System Administrator</span>
                                    <button type="button" onclick="window.openTransferFromFullscreen()" class="px-2 py-0.5 text-[11px] font-semibold text-teal-700 bg-teal-50 border border-teal-200 rounded-md hover:bg-teal-100 transition-colors cursor-pointer">
                                        Change
                                    </button>
                                </div>
                            </div>

                            <!-- Created Date & Time -->
                            <div>
                                <div class="text-[11px] text-slate-400 dark:text-zinc-500 flex items-center gap-1 font-medium">
                                    <svg class="w-3.5 h-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                    <span>Created Date & Time</span>
                                </div>
                                <div id="detail-fs-created-at" class="font-bold text-slate-900 dark:text-zinc-100 mt-1">Apr 12, 2023, 04:45 AM</div>
                            </div>

                            <!-- Created By -->
                            <div>
                                <div class="text-[11px] text-slate-400 dark:text-zinc-500 flex items-center gap-1 font-medium">
                                    <svg class="w-3.5 h-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                    <span>Created By</span>
                                </div>
                                <div id="detail-fs-created-by" class="font-bold text-slate-900 dark:text-zinc-100 mt-1">System Admin</div>
                            </div>

                            <!-- Last Modified Date & Time -->
                            <div>
                                <div class="text-[11px] text-slate-400 dark:text-zinc-500 flex items-center gap-1 font-medium">
                                    <svg class="w-3.5 h-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                    <span>Last Modified Date & Time</span>
                                </div>
                                <div id="detail-fs-updated-at" class="font-bold text-slate-900 dark:text-zinc-100 mt-1">Apr 12, 2023, 04:45 AM</div>
                            </div>

                            <!-- Last Modified By -->
                            <div>
                                <div class="text-[11px] text-slate-400 dark:text-zinc-500 flex items-center gap-1 font-medium">
                                    <svg class="w-3.5 h-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                    <span>Last Modified By</span>
                                </div>
                                <div id="detail-fs-updated-by" class="font-bold text-slate-900 dark:text-zinc-100 mt-1">System Admin</div>
                            </div>

                            <!-- Record ID -->
                            <div>
                                <div class="text-[11px] text-slate-400 dark:text-zinc-500 flex items-center gap-1 font-medium">
                                    <svg class="w-3.5 h-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M7 7h10"/><path d="M7 12h10"/><path d="M7 17h10"/></svg>
                                    <span>Record ID</span>
                                </div>
                                <div class="mt-1">
                                    <span id="detail-fs-record-id" class="px-2 py-0.5 rounded font-mono text-[11px] bg-sky-50 dark:bg-sky-950/40 text-sky-700 dark:text-sky-300 border border-sky-100 dark:border-sky-800">
                                        b8f711ae-de54-413c-b9d5-b1d9aeb67ba4
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN (approx 32%): Related, Activity, Files Tabs and Collapsible Cards Stack -->
            <div class="w-full lg:w-96 shrink-0 space-y-4">
                <!-- Segmented Tabs Pills -->
                <div class="flex items-center gap-2 p-1 bg-slate-100/80 dark:bg-zinc-800/60 rounded-full w-fit">
                    <button
                        type="button"
                        id="fs-tab-btn-related"
                        onclick="window.switchFullscreenRightTab('related')"
                        class="px-3.5 py-1.5 text-xs font-semibold rounded-full bg-[#334e68] text-white shadow-2xs flex items-center gap-1.5 cursor-pointer"
                    >
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                        <span>Related</span>
                        <span id="fs-count-related" class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-slate-200 dark:bg-zinc-700 text-slate-700 dark:text-zinc-300 ml-0.5">5</span>
                    </button>
                    <button
                        type="button"
                        id="fs-tab-btn-activity"
                        onclick="window.switchFullscreenRightTab('activity')"
                        class="px-3.5 py-1.5 text-xs font-medium rounded-full text-slate-600 dark:text-zinc-300 hover:text-slate-900 dark:hover:text-white flex items-center gap-1.5 cursor-pointer"
                    >
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                        <span>Activity</span>
                        <span id="fs-count-activity" class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-slate-200 dark:bg-zinc-700 text-slate-700 dark:text-zinc-300 ml-0.5">2</span>
                    </button>
                    <button
                        type="button"
                        id="fs-tab-btn-files"
                        onclick="window.switchFullscreenRightTab('files')"
                        class="px-3.5 py-1.5 text-xs font-medium rounded-full text-slate-600 dark:text-zinc-300 hover:text-slate-900 dark:hover:text-white flex items-center gap-1.5 cursor-pointer"
                    >
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l8.57-8.57A4 4 0 1 1 18 8.84l-8.59 8.57a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
                        <span>Files</span>
                        <span id="fs-tab-badge-files-count" class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-slate-200 dark:bg-zinc-700 text-slate-700 dark:text-zinc-300 ml-0.5">3</span>
                    </button>
                </div>

                <!-- Hidden Native File Input (triggers native Open dialog on click) -->
                <input
                    type="file"
                    id="account-file-upload-input"
                    multiple
                    class="hidden"
                    onchange="window.handleAccountFileUpload ? window.handleAccountFileUpload(event) : null"
                >

                <!-- Tab Content 1: Related Cards List -->
                <div id="fs-tab-content-related" class="space-y-3">
                    <!-- Card 1: Files (Exact Match with Target Screenshot) -->
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-orange-200/90 dark:border-orange-500/30 shadow-[0_4px_15px_-2px_rgba(251,146,60,0.1)] p-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-lg bg-slate-800 text-white flex items-center justify-center shrink-0">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l8.57-8.57A4 4 0 1 1 18 8.84l-8.59 8.57a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
                                </div>
                                <span class="font-bold text-xs text-slate-900 dark:text-zinc-100 flex items-center gap-1 cursor-pointer" onclick="window.switchFullscreenRightTab('files')">
                                    Files (<span id="fs-badge-files-count">0</span>)
                                    <svg class="w-3 h-3 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="7" y1="17" x2="17" y2="7"/><polyline points="7 7 17 7 17 17"/></svg>
                                </span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <button
                                    type="button"
                                    onclick="window.triggerFileUploadForAccount()"
                                    id="btn-account-add-file"
                                    class="px-2.5 py-1 text-xs font-semibold rounded-md border border-slate-200 dark:border-zinc-700 hover:bg-slate-50 dark:hover:bg-zinc-800 text-slate-700 dark:text-zinc-300 cursor-pointer shadow-2xs transition-colors"
                                >
                                    + Add
                                </button>
                                <button
                                    type="button"
                                    onclick="window.switchFullscreenRightTab('files')"
                                    class="p-1 text-slate-400 hover:text-slate-600 dark:hover:text-zinc-300 cursor-pointer"
                                    title="Expand to Full Files Tab"
                                >
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 3 21 3 21 9"/><polyline points="9 21 3 21 3 15"/><line x1="21" y1="3" x2="14" y2="10"/><line x1="3" y1="21" x2="10" y2="14"/></svg>
                                </button>
                                <button
                                    type="button"
                                    onclick="window.toggleFsRelatedCard('fs-files-collapse', 'fs-files-chevron')"
                                    class="p-1 text-slate-400 hover:text-slate-600 dark:hover:text-zinc-300 cursor-pointer"
                                    title="Toggle collapse"
                                >
                                    <svg id="fs-files-chevron" class="w-3.5 h-3.5 transition-transform duration-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="18 15 12 9 6 15"/></svg>
                                </button>
                            </div>
                        </div>

                        <!-- Upload progress banner -->
                        <div id="account-file-uploading-indicator" class="hidden mt-2 p-2 rounded-lg bg-orange-50 dark:bg-orange-950/40 border border-orange-200 dark:border-orange-800 flex items-center justify-between text-xs text-orange-700 dark:text-orange-300 animate-pulse">
                            <div class="flex items-center gap-2">
                                <svg class="animate-spin w-3.5 h-3.5 text-orange-600" viewBox="0 0 24 24" fill="none">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                <span id="account-file-uploading-text" class="font-medium text-[11px]">Uploading files...</span>
                            </div>
                            <span class="text-[10px] text-orange-500 font-semibold">Please wait</span>
                        </div>

                        <!-- Files Collapse Container (Open by default) -->
                        <div id="fs-files-collapse" class="pt-2 mt-2 border-t border-slate-100 dark:border-zinc-800">
                            <!-- Dynamic File Rows Container -->
                            <div id="fs-files-list-container" class="space-y-1.5">
                                <div class="text-xs text-slate-400 py-3 text-center">
                                    No files uploaded yet.
                                </div>
                            </div>

                            <!-- Footer: View All (N) centered link matching screenshot -->
                            <div id="fs-files-footer" class="hidden pt-2.5 mt-2 border-t border-slate-100 dark:border-zinc-800/80 text-center">
                                <a href="javascript:void(0)" onclick="window.switchFullscreenRightTab('files')" class="text-xs font-semibold text-[#7c3aed] dark:text-purple-400 hover:underline">
                                    View All (<span id="fs-files-viewall-count">0</span>)
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Card 2: Emails (0) -->
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-orange-200/90 dark:border-orange-500/30 shadow-[0_4px_15px_-2px_rgba(251,146,60,0.1)] p-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-lg bg-[#0088cc] text-white flex items-center justify-center">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                                </div>
                                <span class="font-bold text-xs text-slate-900 dark:text-zinc-100 flex items-center gap-1">
                                    Emails (<span id="fs-badge-emails-count">0</span>)
                                    <svg class="w-3 h-3 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="7" y1="17" x2="17" y2="7"/><polyline points="7 7 17 7 17 17"/></svg>
                                </span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <button type="button" onclick="window.composeEmailForAccount()" class="p-1.5 text-slate-400 hover:text-slate-700 dark:hover:text-zinc-200 cursor-pointer" title="Compose">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                                </button>
                                <button type="button" onclick="window.reloadAccountEmails()" class="p-1.5 text-slate-400 hover:text-slate-700 dark:hover:text-zinc-200 cursor-pointer" title="Refresh">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 21h5v-5"/></svg>
                                </button>
                                <button type="button" onclick="window.toggleFsRelatedCard('fs-emails-collapse')" class="p-1 text-slate-400 hover:text-slate-600 dark:hover:text-zinc-300 cursor-pointer">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                                </button>
                            </div>
                        </div>
                        <div id="fs-emails-collapse" class="hidden pt-3 mt-3 border-t border-slate-100 dark:border-zinc-800 text-xs text-slate-500">
                            No emails logged yet.
                        </div>
                    </div>

                    <!-- Card 3: Notes & Activity (2) -->
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-orange-200/90 dark:border-orange-500/30 shadow-[0_4px_15px_-2px_rgba(251,146,60,0.1)] p-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-lg bg-[#b91c1c] text-white flex items-center justify-center">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                                </div>
                                <span class="font-bold text-xs text-slate-900 dark:text-zinc-100 flex items-center gap-1">
                                    Notes & Activity (<span id="fs-badge-notes-count">2</span>)
                                    <svg class="w-3 h-3 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="7" y1="17" x2="17" y2="7"/><polyline points="7 7 17 7 17 17"/></svg>
                                </span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <button type="button" onclick="window.logActivityForAccount()" class="px-2.5 py-1 text-xs font-semibold rounded-md border border-slate-200 dark:border-zinc-700 hover:bg-slate-50 dark:hover:bg-zinc-800 text-slate-700 dark:text-zinc-300 cursor-pointer shadow-2xs">
                                    + Log
                                </button>
                                <button type="button" class="p-1 text-slate-400 hover:text-slate-600 dark:hover:text-zinc-300 cursor-pointer">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 3 21 3 21 9"/><polyline points="9 21 3 21 3 15"/><line x1="21" y1="3" x2="14" y2="10"/><line x1="3" y1="21" x2="10" y2="14"/></svg>
                                </button>
                                <button type="button" onclick="window.toggleFsRelatedCard('fs-notes-collapse')" class="p-1 text-slate-400 hover:text-slate-600 dark:hover:text-zinc-300 cursor-pointer">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                                </button>
                            </div>
                        </div>
                        <div id="fs-notes-collapse" class="hidden pt-3 mt-3 border-t border-slate-100 dark:border-zinc-800 text-xs space-y-2">
                            <div class="p-2 rounded-lg bg-slate-50 dark:bg-zinc-800 border border-slate-100 dark:border-zinc-700">
                                <div class="font-semibold text-slate-800 dark:text-zinc-200">Account Created via Lead Conversion</div>
                                <div class="text-[10px] text-slate-400">System Admin • Automated DealSync Graph Link</div>
                            </div>
                            <div class="p-2 rounded-lg bg-slate-50 dark:bg-zinc-800 border border-slate-100 dark:border-zinc-700">
                                <div class="font-semibold text-slate-800 dark:text-zinc-200">Payment Terms Set to Net 30</div>
                                <div class="text-[10px] text-slate-400">Finance Team • Credit limit approved at $50,000.00</div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 4: Related Lists (5) -->
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-orange-200/90 dark:border-orange-500/30 shadow-[0_4px_15px_-2px_rgba(251,146,60,0.1)] p-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-lg bg-[#0f766e] text-white flex items-center justify-center">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                                </div>
                                <span class="font-bold text-xs text-slate-900 dark:text-zinc-100 flex items-center gap-1">
                                    Related Lists (<span id="fs-badge-lists-count">5</span>)
                                    <svg class="w-3 h-3 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="7" y1="17" x2="17" y2="7"/><polyline points="7 7 17 7 17 17"/></svg>
                                </span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <button type="button" onclick="window.openAccountModal('edit', state.viewing360Account)" class="p-1.5 text-slate-400 hover:text-slate-700 dark:hover:text-zinc-200 cursor-pointer" title="Edit">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                                </button>
                                <button type="button" onclick="window.toggleFsRelatedCard('fs-lists-collapse')" class="p-1 text-slate-400 hover:text-slate-600 dark:hover:text-zinc-300 cursor-pointer">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                                </button>
                            </div>
                        </div>
                        <div id="fs-lists-collapse" class="hidden pt-3 mt-3 border-t border-slate-100 dark:border-zinc-800 text-xs space-y-1.5 text-slate-600 dark:text-zinc-300">
                            <div class="flex justify-between py-1 border-b border-slate-100 dark:border-zinc-800">
                                <span>Contacts:</span> <span id="fs-list-contacts-count" class="font-bold">0</span>
                            </div>
                            <div class="flex justify-between py-1 border-b border-slate-100 dark:border-zinc-800">
                                <span>Sales Deals / Opportunities:</span> <span id="fs-list-deals-count" class="font-bold">0</span>
                            </div>
                            <div class="flex justify-between py-1 border-b border-slate-100 dark:border-zinc-800">
                                <span>Purchase Orders:</span> <span id="fs-list-po-count" class="font-bold">0</span>
                            </div>
                            <div class="flex justify-between py-1 border-b border-slate-100 dark:border-zinc-800">
                                <span>Logistics / Shipments:</span> <span id="fs-list-shipments-count" class="font-bold">0</span>
                            </div>
                            <div class="flex justify-between py-1 border-b border-slate-100 dark:border-zinc-800">
                                <span>Invoices & Payments:</span> <span id="fs-list-payments-count" class="font-bold">0</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab Content 2: Activity List (Exact 1:1 Match with User Screenshot) -->
                <div id="fs-tab-content-activity" class="hidden space-y-4">
                    <!-- CARD 1: ACTIVITY LOGGING FORM CARD (Log a Call / New Task / New Event / Add Note) -->
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl border-2 border-orange-300 dark:border-orange-500/50 shadow-[0_4px_15px_-2px_rgba(251,146,60,0.1)] p-4 sm:p-5 space-y-4">
                        
                        <!-- Form Sub-Navigation Pills -->
                        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 select-none">
                            <button
                                type="button"
                                onclick="window.switchAccountActivityFormType('Call')"
                                id="acc-act-pill-call"
                                class="acc-act-type-pill inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 text-slate-700 dark:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-700 font-medium text-xs cursor-pointer transition-all shrink-0"
                            >
                                <svg class="w-3.5 h-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                                </svg>
                                <span>Log a Call</span>
                            </button>

                            <button
                                type="button"
                                onclick="window.switchAccountActivityFormType('Task')"
                                id="acc-act-pill-task"
                                class="acc-act-type-pill inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 text-slate-700 dark:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-700 font-medium text-xs cursor-pointer transition-all shrink-0"
                            >
                                <svg class="w-3.5 h-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect width="18" height="18" x="3" y="3" rx="2"/><path d="m9 12 2 2 4-4"/>
                                </svg>
                                <span>New Task</span>
                            </button>

                            <button
                                type="button"
                                onclick="window.switchAccountActivityFormType('Event')"
                                id="acc-act-pill-event"
                                class="acc-act-type-pill inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 text-slate-700 dark:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-700 font-medium text-xs cursor-pointer transition-all shrink-0"
                            >
                                <svg class="w-3.5 h-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                                </svg>
                                <span>New Event</span>
                            </button>

                            <button
                                type="button"
                                onclick="window.switchAccountActivityFormType('Note')"
                                id="acc-act-pill-note"
                                class="acc-act-type-pill inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-[#f3e8ff] text-[#7c3aed] border border-purple-200 dark:bg-purple-950/50 dark:border-purple-800 dark:text-purple-300 font-semibold text-xs shadow-2xs cursor-pointer transition-all shrink-0"
                            >
                                <svg class="w-3.5 h-3.5 text-[#7c3aed]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>
                                </svg>
                                <span>Add Note</span>
                            </button>
                        </div>

                        <!-- Divider bar beneath sub-navigation -->
                        <div class="h-1 bg-slate-300 dark:bg-zinc-700 rounded-full w-4/5 mx-auto my-2"></div>

                        <!-- View 1: Call Logging Form -->
                        <div id="acc-activity-call-view" class="hidden space-y-3 pt-1">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 mb-1.5">
                                        Call Subject
                                    </label>
                                    <input
                                        type="text"
                                        id="acc-call-input-subject"
                                        placeholder="e.g. Discovery call with..."
                                        class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-zinc-100 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-purple-500 transition-colors"
                                    >
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 mb-1.5">
                                        Date &amp; Time of Call
                                    </label>
                                    <div class="relative">
                                        <input
                                            type="text"
                                            id="acc-call-input-datetime"
                                            value="{{ now()->format('d-m-Y H:i') }}"
                                            placeholder="DD-MM-YYYY HH:mm"
                                            class="w-full text-xs px-3.5 py-2.5 pr-9 rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-purple-500 transition-colors cursor-pointer"
                                        >
                                        <div class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 mb-1.5">
                                    Description / Discussion Notes
                                </label>
                                <textarea
                                    id="acc-call-input-details"
                                    rows="3"
                                    placeholder="Key details, discussion points, or action items..."
                                    class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-zinc-100 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-purple-500 transition-colors resize-y"
                                ></textarea>
                            </div>

                            <div class="flex items-center justify-end pt-2">
                                <button
                                    type="button"
                                    onclick="window.submitAccountActivity('Call')"
                                    id="btn-acc-call-submit"
                                    class="inline-flex items-center justify-center gap-2 min-w-[130px] px-6 py-2.5 rounded-xl bg-[#7c3aed] hover:bg-[#6d28d9] text-white text-xs font-bold shadow-sm hover:shadow-md cursor-pointer transition-all whitespace-nowrap shrink-0"
                                >
                                    <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                                    </svg>
                                    <span>Log Call</span>
                                </button>
                            </div>
                        </div>

                        <!-- View 2: Task Creation Prompt View -->
                        <div id="acc-activity-task-view" class="hidden space-y-3 pt-1">
                            <div class="border-2 border-dashed border-slate-200 dark:border-zinc-700 rounded-2xl p-6 sm:p-7 flex flex-col items-center justify-center text-center bg-slate-50/40 dark:bg-zinc-800/20">
                                <div class="w-12 h-12 rounded-2xl bg-[#4f46e5] text-white flex items-center justify-center shadow-md shadow-indigo-500/25 mb-3.5">
                                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <rect width="18" height="18" x="3" y="3" rx="2"/>
                                        <path d="m9 12 2 2 4-4"/>
                                    </svg>
                                </div>
                                <h4 class="text-sm sm:text-base font-bold text-slate-900 dark:text-zinc-100 mb-1.5">
                                    Create Task for this Account
                                </h4>
                                <p class="text-xs text-slate-500 dark:text-zinc-400 max-w-sm mb-5 leading-relaxed">
                                    Open the full Task creation popup with priority, status, assignee, reminders, and recurring scheduling.
                                </p>
                                <button
                                    type="button"
                                    onclick="window.openAccountNewTaskModal()"
                                    class="inline-flex items-center justify-center gap-2 min-w-[150px] px-7 py-2.5 rounded-xl bg-[#7e42d7] hover:bg-[#6d28d9] text-white text-xs font-bold shadow-md hover:shadow-lg transition-all cursor-pointer whitespace-nowrap shrink-0"
                                >
                                    <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                                    </svg>
                                    <span>Add New Task</span>
                                </button>
                            </div>
                        </div>

                        <!-- View 3: New Event Form -->
                        <div id="acc-activity-event-view" class="hidden space-y-3 pt-1">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 mb-1.5">
                                    Event / Meeting Title <span class="text-rose-500 font-bold">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="acc-event-input-subject"
                                    placeholder="e.g. Quarterly Business Review &amp; Architecture Demo"
                                    class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-zinc-100 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-purple-500 transition-colors"
                                >
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 mb-1.5">
                                        Start Date &amp; Time <span class="text-rose-500 font-bold">*</span>
                                    </label>
                                    <div class="relative">
                                        <input
                                            type="text"
                                            id="acc-event-input-start"
                                            value="{{ now()->format('d-m-Y H:i') }}"
                                            placeholder="DD-MM-YYYY HH:mm"
                                            class="w-full text-xs px-3.5 py-2.5 pr-9 rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-purple-500 transition-colors cursor-pointer"
                                        >
                                        <div class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                                            </svg>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 mb-1.5">
                                        End Date &amp; Time <span class="text-rose-500 font-bold">*</span>
                                    </label>
                                    <div class="relative">
                                        <input
                                            type="text"
                                            id="acc-event-input-end"
                                            value="{{ now()->addHour()->format('d-m-Y H:i') }}"
                                            placeholder="DD-MM-YYYY HH:mm"
                                            class="w-full text-xs px-3.5 py-2.5 pr-9 rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-purple-500 transition-colors cursor-pointer"
                                        >
                                        <div class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 mb-1.5">
                                    Description / Discussion Notes
                                </label>
                                <textarea
                                    id="acc-event-input-details"
                                    rows="3"
                                    placeholder="Key details, discussion points, or action items..."
                                    class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-zinc-100 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-purple-500 transition-colors resize-y"
                                ></textarea>
                            </div>

                            <div class="flex items-center justify-end pt-2">
                                <button
                                    type="button"
                                    onclick="window.submitAccountEvent()"
                                    id="btn-acc-event-submit"
                                    class="inline-flex items-center justify-center gap-2 min-w-[140px] px-6 py-2.5 rounded-xl bg-[#7c3aed] hover:bg-[#6d28d9] text-white text-xs font-bold shadow-sm hover:shadow-md cursor-pointer transition-all whitespace-nowrap shrink-0"
                                >
                                    <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
                                    </svg>
                                    <span>Log Meeting</span>
                                </button>
                            </div>
                        </div>

                        <!-- View 4: Add Note Form (Active by default matching screenshot) -->
                        <div id="acc-activity-note-view" class="space-y-3 pt-1">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 mb-1.5">
                                    Note Title (optional)
                                </label>
                                <input
                                    type="text"
                                    id="acc-note-input-subject"
                                    placeholder="Title or headline (optional)..."
                                    class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-zinc-100 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-purple-500 transition-colors"
                                >
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 mb-1.5">
                                    Note Details <span class="text-rose-500 font-bold">*</span>
                                </label>
                                <textarea
                                    id="acc-note-input-details"
                                    rows="3"
                                    placeholder="Type notes here... (captures and saves automatically to record history)"
                                    class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-zinc-100 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-purple-500 transition-colors resize-y"
                                ></textarea>
                            </div>

                            <div class="flex items-center justify-end pt-2">
                                <button
                                    type="button"
                                    onclick="window.submitAccountActivity('Note')"
                                    id="btn-acc-note-submit"
                                    class="inline-flex items-center justify-center gap-2 min-w-[130px] px-6 py-2.5 rounded-xl bg-[#7c3aed] hover:bg-[#6d28d9] text-white text-xs font-bold shadow-sm hover:shadow-md cursor-pointer transition-all whitespace-nowrap shrink-0"
                                >
                                    <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
                                    </svg>
                                    <span>Save Note</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- CARD 2: ACTIVITY HISTORY & TIMELINE CARD (Exact 1:1 Match with User Screenshot) -->
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl border-2 border-orange-300 dark:border-orange-500/50 shadow-[0_4px_15px_-2px_rgba(251,146,60,0.1)] p-4 sm:p-5 space-y-3.5">
                        
                        <!-- Header -->
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-zinc-800 select-none">
                            <div class="flex items-center gap-2">
                                <div class="w-5 h-5 rounded-full flex items-center justify-center text-purple-600 dark:text-purple-400">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                                    </svg>
                                </div>
                                <h3 class="text-xs sm:text-sm font-bold text-slate-800 dark:text-zinc-100">
                                    Activity History &amp; Timeline
                                </h3>
                                <span id="account-timeline-count-badge" class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#f3e8ff] text-[#7c3aed] dark:bg-purple-950/60 dark:text-purple-300">
                                    2
                                </span>
                            </div>

                            <button
                                type="button"
                                onclick="window.refreshAccountActivities(state.viewing360Account)"
                                class="p-1 rounded text-slate-400 hover:text-slate-600 dark:hover:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
                                title="Refresh Timeline"
                            >
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 21h5v-5"/>
                                </svg>
                            </button>
                        </div>

                        <!-- Timeline Items List -->
                        <div id="account-activities-timeline-list" class="space-y-3">
                            <div class="p-6 border-2 border-dashed border-slate-200 dark:border-zinc-700 rounded-xl text-center space-y-1">
                                <p class="text-xs font-medium text-slate-400">Loading activities...</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab Content 3: Dedicated Files Tab View -->
                <!-- Tab Content 3: Dedicated Files Tab View (In-Tab Files & Attachments Table, No Modal) -->
                <div id="fs-tab-content-files" class="hidden space-y-3">
                    <div class="rounded-2xl border-2 border-orange-300 dark:border-orange-500/50 shadow-[0_4px_15px_-2px_rgba(251,146,60,0.1)] p-4 sm:p-5 bg-white dark:bg-zinc-900 space-y-3.5">
                        <div class="flex items-center justify-between pb-1">
                            <div class="flex items-center gap-2.5">
                                <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M6 22a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h8a2.4 2.4 0 0 1 1.704.706l3.588 3.588A2.4 2.4 0 0 1 20 8v12a2 2 0 0 1-2 2z"></path>
                                    <path d="M14 2v5a1 1 0 0 0 1 1h5"></path>
                                    <path d="M10 9H8"></path>
                                    <path d="M16 13H8"></path>
                                    <path d="M16 17H8"></path>
                                </svg>
                                <h4 class="text-[0.95rem] font-bold text-zinc-950 dark:text-zinc-100 m-0">Files &amp; Attachments</h4>
                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-[#f3e8ff] text-[#7c3aed] dark:bg-purple-950/60 dark:text-purple-300" id="fs-tab-view-files-count">3</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    onclick="window.triggerFileUploadForAccount()"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-md border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-700 dark:text-zinc-200 hover:bg-slate-50 dark:hover:bg-zinc-700/60 shadow-2xs transition-colors cursor-pointer"
                                >
                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="12" y1="5" x2="12" y2="19"></line>
                                        <line x1="5" y1="12" x2="19" y2="12"></line>
                                    </svg>
                                    <span>Add File</span>
                                </button>
                                <button
                                    type="button"
                                    onclick="window.loadAccountFiles(state.viewing360Account)"
                                    class="p-1.5 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 rounded-md hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
                                    title="Refresh Files"
                                >
                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"></path>
                                        <path d="M21 3v5h-5"></path>
                                        <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"></path>
                                        <path d="M8 16H3v5"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Table Container (Horizontal scroll, matching user snippet 1:1) -->
                        <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900">
                            <table class="w-full text-left border-collapse text-[0.785rem]">
                                <thead>
                                    <tr class="border-b border-zinc-200 dark:border-zinc-800 bg-slate-50/80 dark:bg-zinc-800/50 text-slate-600 dark:text-zinc-400 text-[11px] font-semibold">
                                        <th class="w-10 px-3 py-2.5 text-center"></th>
                                        <th class="px-3 py-2.5 whitespace-nowrap">Title &amp; Document Name</th>
                                        <th class="px-3 py-2.5 whitespace-nowrap">Type</th>
                                        <th class="px-3 py-2.5 whitespace-nowrap">Size</th>
                                        <th class="px-3 py-2.5 whitespace-nowrap">Version</th>
                                        <th class="px-3 py-2.5 whitespace-nowrap">Uploaded By</th>
                                        <th class="px-3 py-2.5 whitespace-nowrap">Date</th>
                                        <th class="px-3 py-2.5 whitespace-nowrap">Links</th>
                                        <th class="w-24 px-3 py-2.5 text-right whitespace-nowrap">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="fs-tab-files-tbody" class="divide-y divide-zinc-200 dark:divide-zinc-800">
                                    <tr>
                                        <td colspan="9" class="py-8 text-center text-xs text-slate-400">
                                            No files found.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- MODAL: ACCOUNT FILES & ATTACHMENTS POPUP (1:1 with Screenshot) -->
    <!-- ========================================== -->
    <div id="account-files-popup-modal" class="fixed inset-0 z-[70] hidden bg-zinc-950/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4">
        <div class="animate-scale-up relative max-w-[920px] w-[92vw] max-h-[88vh] bg-white dark:bg-zinc-900 rounded-2xl flex flex-col overflow-hidden shadow-[0_25px_50px_-12px_rgba(0,0,0,0.35)] border border-slate-200 dark:border-zinc-800">
            <!-- Modal Header -->
            <div class="px-4 sm:px-5 py-3 sm:py-3.5 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between bg-slate-50 dark:bg-zinc-900/90 shrink-0">
                <div class="flex items-center gap-2.5 sm:gap-3">
                    <div class="w-7 h-7 rounded-md bg-[#71717a] text-white flex items-center justify-center shrink-0 shadow-2xs">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m16 6-8.414 8.586a2 2 0 0 0 2.829 2.829l8.414-8.586a4 4 0 1 0-5.657-5.657l-8.379 8.551a6 6 0 1 0 8.485 8.485l8.379-8.551"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-[0.975rem] font-bold text-zinc-900 dark:text-zinc-100 m-0 leading-tight">Files &amp; Attachments</h3>
                        <div class="text-[0.725rem] text-zinc-500 dark:text-zinc-400 mt-0.5">
                            <span id="files-popup-account-name">Account</span> • <span id="files-popup-count">0</span> files
                        </div>
                    </div>
                </div>
                <button
                    type="button"
                    onclick="window.closeAccountFilesModal()"
                    class="p-1.5 rounded-full text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200 hover:bg-slate-200 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
                    title="Close popup"
                >
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-4 sm:p-5 overflow-y-auto flex-1">
                <div class="flex flex-col gap-4">
                    <!-- Inner Card with Orange/Amber Border matching screenshot -->
                    <div class="rounded-2xl border-2 border-orange-300 dark:border-orange-500/50 shadow-[0_4px_20px_-2px_rgba(251,146,60,0.15)] bg-white dark:bg-zinc-900 p-4 sm:p-5">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2.5">
                                <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M6 22a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h8a2.4 2.4 0 0 1 1.704.706l3.588 3.588A2.4 2.4 0 0 1 20 8v12a2 2 0 0 1-2 2z"></path>
                                    <path d="M14 2v5a1 1 0 0 0 1 1h5"></path>
                                    <path d="M10 9H8"></path>
                                    <path d="M16 13H8"></path>
                                    <path d="M16 17H8"></path>
                                </svg>
                                <h4 class="text-[0.95rem] font-bold text-zinc-950 dark:text-zinc-100 m-0">Files &amp; Attachments</h4>
                                <span class="px-2 py-0.5 text-[0.7rem] font-semibold rounded-full bg-purple-100 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300" id="files-popup-inner-count">0</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    onclick="window.triggerFileUploadForAccount()"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-md border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-700 dark:text-zinc-200 hover:bg-slate-50 dark:hover:bg-zinc-700/60 shadow-2xs transition-colors cursor-pointer"
                                >
                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="12" y1="5" x2="12" y2="19"></line>
                                        <line x1="5" y1="12" x2="19" y2="12"></line>
                                    </svg>
                                    <span>Add File</span>
                                </button>
                                <button
                                    type="button"
                                    onclick="window.loadAccountFiles(state.viewing360Account)"
                                    class="p-1.5 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 rounded-md hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
                                    title="Refresh Files"
                                >
                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"></path>
                                        <path d="M21 3v5h-5"></path>
                                        <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"></path>
                                        <path d="M8 16H3v5"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Table Container -->
                        <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900">
                            <table class="w-full text-left border-collapse text-[0.785rem]">
                                <thead>
                                    <tr class="border-b border-zinc-200 dark:border-zinc-800 bg-slate-50/80 dark:bg-zinc-800/50 text-slate-600 dark:text-zinc-400 text-[11px] font-semibold">
                                        <th class="w-10 px-3 py-2.5 text-center"></th>
                                        <th class="px-3 py-2.5">Title &amp; Document Name</th>
                                        <th class="px-3 py-2.5">Type</th>
                                        <th class="px-3 py-2.5">Size</th>
                                        <th class="px-3 py-2.5">Version</th>
                                        <th class="px-3 py-2.5">Uploaded By</th>
                                        <th class="px-3 py-2.5">Date</th>
                                        <th class="px-3 py-2.5">Links</th>
                                        <th class="w-24 px-3 py-2.5 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="account-files-popup-tbody" class="divide-y divide-zinc-200 dark:divide-zinc-800">
                                    <tr>
                                        <td colspan="9" class="py-8 text-center text-xs text-slate-400">
                                            No files found.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- MODAL: ACCOUNT NEW TASK MODAL              -->
    <!-- ========================================== -->
    <div id="accountNewTaskModal" class="hidden fixed inset-0 z-[80] bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
        <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl border border-slate-200/90 dark:border-zinc-800 w-full max-w-xl overflow-hidden my-auto animate-scale-up">
            <!-- Modal Header -->
            <div class="flex items-start justify-between p-5 border-b border-slate-100 dark:border-zinc-800">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-[#4f46e5] text-white flex items-center justify-center shadow-xs shrink-0">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <rect width="18" height="18" x="3" y="3" rx="2"/>
                            <path d="m9 12 2 2 4-4"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-zinc-100 leading-tight">
                            New Task
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
                            Create and assign an actionable task, follow-up, or reminder.
                        </p>
                    </div>
                </div>

                <button
                    type="button"
                    onclick="window.closeAccountNewTaskModal()"
                    class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-zinc-200 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
                    title="Close"
                >
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>

            <!-- Modal Form Body -->
            <form id="accountNewTaskForm" onsubmit="event.preventDefault(); window.submitAccountNewTaskModal();" class="p-5 space-y-4">
                <!-- Field 1: Subject * -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 mb-1.5">
                        Subject <span class="text-rose-500 font-bold">*</span>
                    </label>
                    <input
                        type="text"
                        id="acc-task-modal-subject"
                        placeholder="e.g. Follow up on proposal pricing, Client onboarding call"
                        required
                        class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-zinc-100 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-purple-500 transition-colors"
                    >
                </div>

                <!-- Row 2: Priority & Status (2 cols) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 mb-1.5">
                            Priority
                        </label>
                        <div class="relative">
                            <select
                                id="acc-task-modal-priority"
                                class="w-full text-xs px-3.5 py-2.5 pr-8 rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-purple-500 transition-colors appearance-none cursor-pointer"
                            >
                                <option value="Normal" selected>Normal</option>
                                <option value="High">High</option>
                                <option value="Low">Low</option>
                                <option value="Urgent">Urgent</option>
                            </select>
                            <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 mb-1.5">
                            Status
                        </label>
                        <div class="relative">
                            <select
                                id="acc-task-modal-status"
                                class="w-full text-xs px-3.5 py-2.5 pr-8 rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-purple-500 transition-colors appearance-none cursor-pointer"
                            >
                                <option value="Open" selected>Open</option>
                                <option value="In Progress">In Progress</option>
                                <option value="Completed">Completed</option>
                                <option value="Deferred">Deferred</option>
                                <option value="Waiting on someone else">Waiting on someone else</option>
                            </select>
                            <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 3: Assigned To & Due Date (2 cols) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 mb-1.5">
                            Assigned To (Assignee)
                        </label>
                        <div class="relative">
                            <select
                                id="acc-task-modal-assignee"
                                class="w-full text-xs px-3.5 py-2.5 pr-8 rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-purple-500 transition-colors appearance-none cursor-pointer"
                            >
                                @foreach($users as $u)
                                    <option value="{{ $u['id'] ?? $u->id }}" {{ stripos($u['name'] ?? $u->name ?? '', 'Alex') !== false ? 'selected' : '' }}>
                                        {{ $u['name'] ?? $u->name }} {{ stripos($u['name'] ?? $u->name ?? '', 'Alex') !== false ? '(Executive / CEO)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 mb-1.5">
                            Due Date
                        </label>
                        <div class="relative">
                            <input
                                type="date"
                                id="acc-task-modal-duedate"
                                value="{{ now()->addDays(2)->format('Y-m-d') }}"
                                class="w-full text-xs px-3.5 py-2.5 pr-9 rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-purple-500 transition-colors cursor-pointer"
                            >
                            <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 4: Related Module & Related Record Name (2 cols) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 mb-1.5">
                            Related Module
                        </label>
                        <div class="relative">
                            <select
                                id="acc-task-modal-module"
                                class="w-full text-xs px-3.5 py-2.5 pr-8 rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-purple-500 transition-colors appearance-none cursor-pointer"
                            >
                                <option value="Accounts" selected>Accounts</option>
                                <option value="Leads">Leads</option>
                                <option value="Contacts">Contacts</option>
                                <option value="Opportunities">Opportunities</option>
                            </select>
                            <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 mb-1.5">
                            Related Record Name
                        </label>
                        <div class="relative">
                            <input
                                type="text"
                                id="acc-task-modal-record-name"
                                value=""
                                placeholder="Account name"
                                class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-purple-500 transition-colors"
                            >
                        </div>
                    </div>
                </div>

                <!-- Row 5: Reminder & Comments -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 mb-1.5">
                            Reminder
                        </label>
                        <div class="relative">
                            <select
                                id="acc-task-modal-reminder"
                                class="w-full text-xs px-3.5 py-2.5 pr-8 rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-purple-500 transition-colors appearance-none cursor-pointer"
                            >
                                <option value="No reminder is set" selected>No reminder is set</option>
                                <option value="15 minutes before">15 minutes before</option>
                                <option value="1 hour before">1 hour before</option>
                                <option value="1 day before">1 day before</option>
                            </select>
                            <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center pt-5">
                        <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                            <input
                                type="checkbox"
                                id="acc-task-modal-recurring"
                                class="w-4 h-4 rounded text-purple-600 focus:ring-purple-500 border-slate-300 dark:border-zinc-700"
                            >
                            <span class="text-xs font-semibold text-slate-700 dark:text-zinc-300">
                                Create as Recurring Task
                            </span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 mb-1.5">
                        Comments / Notes
                    </label>
                    <textarea
                        id="acc-task-modal-comments"
                        rows="3"
                        placeholder="Additional details, scope, or background context..."
                        class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-zinc-100 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-purple-500 transition-colors resize-y"
                    ></textarea>
                </div>

                <!-- Footer Buttons -->
                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-zinc-800">
                    <button
                        type="button"
                        onclick="window.closeAccountNewTaskModal()"
                        class="px-4 py-2 text-xs font-medium rounded-xl border border-slate-200 dark:border-zinc-700 hover:bg-slate-50 dark:hover:bg-zinc-800 text-slate-700 dark:text-zinc-300 cursor-pointer transition-colors"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        id="btn-submit-acc-task-modal"
                        class="inline-flex items-center justify-center gap-2 min-w-[140px] px-6 py-2.5 text-xs font-bold rounded-xl bg-[#7e42d7] hover:bg-[#6d28d9] text-white shadow-md hover:shadow-lg transition-all cursor-pointer whitespace-nowrap shrink-0"
                    >
                        <span>Create Task</span>
                    </button>
                </div>
            </form>
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
    <!-- MODAL 4: CUSTOMIZE TABLE FIELDS & ORDER MODAL -->
    <!-- ========================================== -->
    <div id="account-customize-modal" class="fixed inset-0 z-50 hidden bg-zinc-950/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 overflow-y-auto">
        <div class="bg-white dark:bg-zinc-900 rounded-3xl max-w-2xl w-full border border-slate-200 dark:border-zinc-800 shadow-2xl overflow-hidden animate-scale-up my-6 flex flex-col max-h-[92vh]">
            <!-- Header matching screenshot -->
            <div class="px-6 py-4 border-b border-slate-200/80 dark:border-zinc-800 flex items-center justify-between bg-white dark:bg-zinc-900 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-zinc-800 border border-slate-200/90 dark:border-zinc-700 flex items-center justify-center text-slate-600 dark:text-zinc-300 shrink-0 shadow-2xs">
                        <svg class="w-5 h-5 text-slate-600 dark:text-zinc-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="4" x2="4" y1="21" y2="14"/><line x1="4" x2="4" y1="10" y2="3"/>
                            <line x1="12" x2="12" y1="21" y2="12"/><line x1="12" x2="12" y1="8" y2="3"/>
                            <line x1="20" x2="20" y1="21" y2="16"/><line x1="20" x2="20" y1="12" y2="3"/>
                            <line x1="1" x2="7" y1="14" y2="14"/><line x1="9" x2="15" y1="8" y2="8"/><line x1="17" x2="23" y1="16" y2="16"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-zinc-100">
                            Customize Table Fields & Order
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-zinc-400 font-normal mt-0.5">
                            Drag rows or click ↑ / ↓ to rearrange column sequence from left to right.
                        </p>
                    </div>
                </div>
                <button type="button" onclick="window.closeCustomizeFieldsModal()" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-700 dark:hover:text-zinc-200 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>

            <!-- Subheader Controls Bar -->
            <div class="px-6 py-3 border-b border-slate-100 dark:border-zinc-800 flex flex-col sm:flex-row items-center justify-between gap-3 bg-white dark:bg-zinc-900 shrink-0">
                <!-- Search Box -->
                <div class="relative w-full sm:w-64">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    <input
                        type="text"
                        id="customize-fields-search-input"
                        placeholder="Find field by name..."
                        oninput="window.handleCustomizeFieldsSearch(this.value)"
                        class="w-full pl-9 pr-3 py-1.5 text-xs sm:text-sm rounded-lg border border-slate-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-zinc-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500"
                    >
                </div>
                <!-- Actions -->
                <div class="flex items-center gap-4 text-xs font-semibold text-slate-600 dark:text-zinc-300">
                    <button type="button" onclick="window.customizeSelectAllFields()" class="hover:text-slate-900 dark:hover:text-zinc-100 cursor-pointer transition-colors">
                        Select All
                    </button>
                    <button type="button" onclick="window.customizeDeselectAllFields()" class="hover:text-slate-900 dark:hover:text-zinc-100 cursor-pointer transition-colors">
                        Deselect All
                    </button>
                    <button type="button" onclick="window.customizeResetFields()" class="flex items-center gap-1 hover:text-slate-900 dark:hover:text-zinc-100 cursor-pointer transition-colors">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/>
                            <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M8 16H3v5"/>
                        </svg>
                        <span>Reset</span>
                    </button>
                </div>
            </div>

            <!-- Banner matching screenshot -->
            <div class="px-6 py-2 bg-blue-50/70 dark:bg-blue-950/30 border-b border-blue-100/80 dark:border-blue-900/40 text-xs text-blue-700 dark:text-blue-300 flex items-center gap-2 shrink-0">
                <svg class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400 shrink-0" viewBox="0 0 24 24" fill="currentColor">
                    <circle cx="9" cy="6" r="1.5"/><circle cx="15" cy="6" r="1.5"/>
                    <circle cx="9" cy="12" r="1.5"/><circle cx="15" cy="12" r="1.5"/>
                    <circle cx="9" cy="18" r="1.5"/><circle cx="15" cy="18" r="1.5"/>
                </svg>
                <span>
                    <strong class="font-bold">Drag any row up or down</strong> using the handle or click the arrow buttons (↑ / ↓) to position columns.
                </span>
            </div>

            <!-- Field Items Scrollable Container -->
            <div id="customize-fields-list-container" class="p-6 space-y-2.5 overflow-y-auto flex-1 bg-white dark:bg-zinc-900 min-h-[300px]">
                <!-- Populated dynamically by accounts.js -->
            </div>

            <!-- Footer matching screenshot -->
            <div class="px-6 py-4 border-t border-slate-200/80 dark:border-zinc-800 flex items-center justify-between bg-white dark:bg-zinc-900 shrink-0 rounded-b-3xl">
                <div id="customize-fields-footer-count" class="text-xs text-slate-500 dark:text-zinc-400">
                    Showing <strong>0</strong> of 0 columns in table
                </div>
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        onclick="window.closeCustomizeFieldsModal()"
                        class="px-4 py-2 text-xs sm:text-sm font-semibold text-slate-700 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-xl border border-slate-300 dark:border-zinc-700 transition-colors cursor-pointer"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        onclick="window.saveCustomizedFields()"
                        class="px-5 py-2 text-xs sm:text-sm font-semibold text-white bg-[#7e47a7] hover:bg-[#6c3992] rounded-xl shadow-xs transition-colors cursor-pointer"
                    >
                        Apply & Save View
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- MODAL 5: FILTER ACCOUNTS MODAL (Matching Screenshot) -->
    <!-- ========================================== -->
    <div id="account-filter-modal" class="fixed inset-0 z-50 hidden bg-zinc-950/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white dark:bg-zinc-900 rounded-3xl max-w-2xl w-full border border-slate-200/90 dark:border-zinc-800 shadow-2xl overflow-hidden animate-scale-up">
            <!-- Modal Header -->
            <div class="px-6 py-5 border-b border-slate-100 dark:border-zinc-800/80 flex items-center justify-between">
                <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-950/40 border border-blue-100 dark:border-blue-900/50 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-[#2d4b68] dark:text-zinc-100 leading-tight">
                            Filter Accounts
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5 font-normal">
                            Filter records by matching any object fields
                        </p>
                    </div>
                </div>
                <button
                    type="button"
                    onclick="window.closeAccountFilterModal()"
                    class="text-slate-400 hover:text-slate-600 dark:hover:text-zinc-200 text-2xl font-light p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer leading-none"
                    title="Close"
                >
                    &times;
                </button>
            </div>

            <!-- Modal Body: Conditions List & Add Button -->
            <div class="p-6 space-y-4 max-h-[65vh] overflow-y-auto">
                <!-- Dynamic Filter Condition Rows List -->
                <div id="account-filter-conditions-list" class="space-y-3">
                    <!-- Populated dynamically by accounts.js -->
                </div>

                <!-- Add Condition Button matching screenshot -->
                <div class="pt-1">
                    <button
                        type="button"
                        onclick="window.addFilterConditionRow()"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg border-2 border-slate-900 dark:border-zinc-100 text-slate-900 dark:text-zinc-100 hover:bg-slate-100 dark:hover:bg-zinc-800 text-xs sm:text-sm font-bold transition-colors cursor-pointer shadow-2xs"
                    >
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        <span>Add Filter Condition</span>
                    </button>
                </div>
            </div>

            <!-- Modal Footer matching screenshot -->
            <div class="px-6 py-4 border-t border-slate-100 dark:border-zinc-800/80 flex items-center justify-between bg-white dark:bg-zinc-900 rounded-b-3xl">
                <!-- Left: Clear All -->
                <button
                    type="button"
                    onclick="window.clearAllAccountFiltersFromModal()"
                    class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-red-600 hover:text-red-700 dark:text-red-500 dark:hover:text-red-400 transition-colors cursor-pointer"
                >
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                        <path d="M3 3v5h5"/>
                        <path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/>
                        <path d="M16 21h5v-5"/>
                    </svg>
                    <span>Clear All</span>
                </button>

                <!-- Right: Cancel & Apply Filters -->
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        onclick="window.closeAccountFilterModal()"
                        class="px-4 py-2 text-xs sm:text-sm font-semibold text-slate-700 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-xl border border-slate-300 dark:border-zinc-700 transition-colors cursor-pointer"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        onclick="window.applyAccountFiltersFromModal()"
                        class="inline-flex items-center gap-1.5 px-5 py-2 text-xs sm:text-sm font-semibold text-white bg-[#7e47a7] hover:bg-[#6c3992] rounded-xl shadow-xs transition-colors cursor-pointer"
                    >
                        <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        <span>Apply Filters (<span id="modal-filter-apply-count">0</span>)</span>
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
<script src="{{ asset('js/accounts.js') }}?v=3.0"></script>
@endsection
