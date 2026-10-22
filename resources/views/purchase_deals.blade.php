@extends('layouts.app')

@section('title', 'Purchase Deals & Procurement — DealSync Enterprise CRM')

@section('content')
<!-- Centered Max-Width Container matching React page-wrapper (max-width: 1240px, padding: 2.25rem 2.5rem) -->
<div id="purchase-deals-page-root" class="max-w-[1240px] mx-auto w-full px-6 sm:px-10 py-7 space-y-6 animate-fade-in">
    <!-- Top Action Header -->
    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4 pb-2 border-b border-zinc-200 dark:border-zinc-800">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800">
                    Buy-Side Procurement Ledger
                </span>
                <span class="text-xs text-zinc-500 dark:text-zinc-400">
                    Supplier POs &amp; Spend Commitments
                </span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-950 dark:text-zinc-100">
                Purchase Deals &amp; Procurement
            </h1>
            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 max-w-2xl">
                Supplier PO tracking with direct margin calculations, delivery commitments, and back-to-back sales deal integration. Synced dynamically with custom fields and field-level security permissions.
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap shrink-0">
            <!-- View Switcher (Table vs Kanban) -->
            <div class="inline-flex items-center p-0.5 rounded-lg bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700">
                <button
                    type="button"
                    id="btn-view-table"
                    onclick="window.switchPurchaseDealView('table')"
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-md transition-all cursor-pointer bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white shadow-2xs"
                >
                    <i data-lucide="table" class="w-3.5 h-3.5"></i>
                    <span>Table</span>
                </button>
                <button
                    type="button"
                    id="btn-view-kanban"
                    onclick="window.switchPurchaseDealView('kanban')"
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-md transition-all cursor-pointer text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white"
                >
                    <i data-lucide="kanban" class="w-3.5 h-3.5"></i>
                    <span>Kanban</span>
                </button>
            </div>

            <!-- Customize Visible Fields -->
            <button
                type="button"
                onclick="window.openPurchaseDealConfigModal()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs cursor-pointer"
                title="Customize Visible Fields"
            >
                <i data-lucide="sliders" class="w-3.5 h-3.5 text-zinc-500"></i>
                <span>Customize</span>
            </button>

            <!-- Filter Button -->
            <button
                type="button"
                onclick="window.openPurchaseDealFilterModal()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs cursor-pointer"
                title="Filter Purchase Deals"
            >
                <i data-lucide="filter" class="w-3.5 h-3.5 text-zinc-500"></i>
                <span>Filter</span>
                <span id="po-filter-badge" class="hidden px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-amber-600 text-white">0</span>
            </button>

            <!-- Export CSV Button -->
            <button
                type="button"
                onclick="window.exportPurchaseDealsCsv()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs cursor-pointer"
            >
                <i data-lucide="download" class="w-3.5 h-3.5 text-zinc-500"></i>
                <span>Export CSV</span>
            </button>

            <!-- Quick PO Deal Primary Action -->
            <button
                type="button"
                onclick="window.openPurchaseDealModal('create')"
                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm transition-colors cursor-pointer"
            >
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Quick PO Deal</span>
            </button>
        </div>
    </div>

    <!-- Top KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 border border-zinc-200 dark:border-zinc-800 shadow-xs">
            <div class="text-[11px] font-semibold text-zinc-500 uppercase tracking-wider">COMMITTED SPEND</div>
            <div id="kpi-committed-spend" class="text-2xl font-extrabold text-zinc-950 dark:text-zinc-100 mt-1 tracking-tight">
                $0
            </div>
            <div class="flex items-center gap-1 text-[11px] text-zinc-500 mt-1">
                <span id="kpi-committed-count" class="font-medium">0 Orders</span>
                <span>• Total Buy-Side Ledger</span>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 border border-zinc-200 dark:border-zinc-800 shadow-xs">
            <div class="text-[11px] font-semibold text-zinc-500 uppercase tracking-wider">IN-PRODUCTION SPEND</div>
            <div id="kpi-production-spend" class="text-2xl font-extrabold text-amber-600 dark:text-amber-400 mt-1 tracking-tight">
                $0
            </div>
            <div class="flex items-center gap-1 text-[11px] text-zinc-500 mt-1">
                <span id="kpi-production-count" class="font-medium">0 Orders</span>
                <span>• Hardware Fabrication Active</span>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 border border-zinc-200 dark:border-zinc-800 shadow-xs">
            <div class="text-[11px] font-semibold text-zinc-500 uppercase tracking-wider">DELIVERED &amp; RECEIVED</div>
            <div id="kpi-delivered-spend" class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-1 tracking-tight">
                $0
            </div>
            <div class="flex items-center gap-1 text-[11px] text-emerald-600 dark:text-emerald-400 font-semibold mt-1">
                <i data-lucide="check-circle-2" class="w-3 h-3"></i>
                <span id="kpi-delivered-count">0 Completed</span>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 border border-zinc-200 dark:border-zinc-800 shadow-xs">
            <div class="text-[11px] font-semibold text-zinc-500 uppercase tracking-wider">AVG PAYMENT TERMS</div>
            <div id="kpi-avg-terms" class="text-2xl font-extrabold text-indigo-600 dark:text-indigo-400 mt-1 tracking-tight">
                Net 30
            </div>
            <div class="text-[11px] text-zinc-500 mt-1">
                Vendor Credit Lines Active
            </div>
        </div>
    </div>

    <!-- Quick Stage Filter Pills -->
    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs">
        <span class="text-zinc-500 font-medium shrink-0 mr-1">Stages:</span>
        <button
            type="button"
            data-stage="all"
            onclick="window.filterByStage('all')"
            class="po-stage-pill px-3 py-1 rounded-full font-semibold transition-all cursor-pointer bg-zinc-900 dark:bg-zinc-100 text-white dark:text-zinc-900 shadow-xs"
        >
            All Orders (<span id="count-stage-all">0</span>)
        </button>
        @foreach($stages ?? [] as $st)
            <button
                type="button"
                data-stage="{{ $st }}"
                onclick="window.filterByStage('{{ $st }}')"
                class="po-stage-pill px-3 py-1 rounded-full font-medium transition-all cursor-pointer bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700"
            >
                {{ $st }} (<span id="count-stage-{{ Str::slug($st) }}">0</span>)
            </button>
        @endforeach
    </div>

    <!-- Toolbar: Search and Filter Info -->
    <div class="bg-white dark:bg-zinc-900 rounded-xl p-3 border border-zinc-200 dark:border-zinc-800 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-3">
        <div class="relative w-full sm:max-w-md">
            <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400"></i>
            <input
                type="text"
                id="po-search-input"
                placeholder="Search deals by PO number, title, vendor..."
                oninput="window.handleSearchInput(this.value)"
                class="w-full pl-9 pr-4 py-1.5 text-xs rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-800/50 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 placeholder-zinc-400"
            >
        </div>

        <div class="text-xs text-zinc-500 dark:text-zinc-400 flex items-center gap-3 w-full sm:w-auto justify-between sm:justify-end">
            <span id="po-table-summary">Showing 0 of 0 purchase orders</span>
            <div class="flex items-center gap-1">
                <span class="text-zinc-400">Profile:</span>
                <span class="font-bold text-zinc-700 dark:text-zinc-300">{{ $currentUserProfile ?? 'Administrator' }}</span>
            </div>
        </div>
    </div>

    <!-- Active Filter Chips Bar -->
    <div id="active-filter-chips-container" class="hidden flex-wrap items-center gap-1.5 p-2.5 rounded-lg bg-zinc-50 dark:bg-zinc-900/50 border border-zinc-200 dark:border-zinc-800 text-xs">
        <span class="font-semibold text-zinc-600 dark:text-zinc-400 flex items-center gap-1">
            <i data-lucide="filter" class="w-3.5 h-3.5 text-zinc-500"></i>
            <span>Active Filters:</span>
        </span>
        <div id="filter-chips-list" class="flex flex-wrap items-center gap-1.5"></div>
        <button
            type="button"
            onclick="window.clearAllFilters()"
            class="ml-auto text-xs font-semibold text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 cursor-pointer"
        >
            Clear All
        </button>
    </div>

    <!-- VIEW 1: TABLE VIEW CONTAINER -->
    <div id="view-table-container" class="space-y-4">
        <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr id="po-table-header-row" class="border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/75 dark:bg-zinc-800/50 text-zinc-600 dark:text-zinc-400 font-semibold">
                            <!-- Dynamic Columns Generated via JS -->
                        </tr>
                    </thead>
                    <tbody id="po-table-tbody" class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        <!-- Dynamic Rows Generated via JS -->
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            <div id="po-empty-state" class="hidden py-16 px-4 text-center space-y-3">
                <div class="w-12 h-12 mx-auto rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-400">
                    <i data-lucide="shopping-bag" class="w-6 h-6"></i>
                </div>
                <h3 class="text-sm font-bold text-zinc-950 dark:text-zinc-100">No Purchase Deals Found</h3>
                <p class="text-xs text-zinc-500 max-w-sm mx-auto">
                    No supplier purchase orders match your current stage filter, search term, or security criteria.
                </p>
                <button
                    type="button"
                    onclick="window.openPurchaseDealModal('create')"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold transition-colors cursor-pointer"
                >
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>Create First PO Deal</span>
                </button>
            </div>
        </div>
    </div>

    <!-- VIEW 2: KANBAN BOARD CONTAINER -->
    <div id="view-kanban-container" class="hidden space-y-4">
        <div id="po-kanban-board" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-3.5 items-start overflow-x-auto pb-4">
            <!-- Kanban Stage Columns Generated dynamically via JS -->
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODALS                                                                    -->
<!-- ========================================================================= -->

<!-- MODAL 1: QUICK PO DEAL / CREATE & EDIT MODAL -->
<div id="purchase-deal-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-xs animate-fade-in overflow-y-auto">
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xl w-full max-w-2xl my-8 overflow-hidden">
        <!-- Modal Header -->
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-950/50 flex items-center justify-center text-amber-600 dark:text-amber-400">
                    <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 id="modal-deal-heading" class="text-base font-bold text-zinc-950 dark:text-zinc-100">
                        Create Purchase Order Deal
                    </h3>
                    <p class="text-xs text-zinc-500">
                        Buy-side procurement commitment with backing sales deal coupling
                    </p>
                </div>
            </div>
            <button type="button" onclick="window.closePurchaseDealModal()" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 cursor-pointer">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Modal Body Form -->
        <form id="purchase-deal-form" onsubmit="window.handlePurchaseDealSubmit(event)" class="p-6 space-y-5 max-h-[75vh] overflow-y-auto">
            <input type="hidden" id="deal-id" value="">

            <!-- Standard Core Fields -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- PO Number -->
                <div>
                    <label class="block text-xs font-semibold text-zinc-800 dark:text-zinc-200 mb-1">
                        PO Number <span class="text-zinc-400 font-normal">(Auto-generated if empty)</span>
                    </label>
                    <div class="relative">
                        <input
                            type="text"
                            id="field-po_number"
                            placeholder="e.g. PO-2026-8840"
                            class="w-full px-3 py-2 text-xs font-mono rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                    </div>
                </div>

                <!-- PO Title -->
                <div>
                    <label class="block text-xs font-semibold text-zinc-800 dark:text-zinc-200 mb-1">
                        PO Order Title <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="field-title"
                        required
                        placeholder="e.g. High-Throughput Optical Switches"
                        class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                </div>

                <!-- Supplier / Vendor Account -->
                <div>
                    <label class="block text-xs font-semibold text-zinc-800 dark:text-zinc-200 mb-1">
                        Supplier / Vendor <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="field-account_id"
                        required
                        class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        <option value="">Select Supplier Account...</option>
                        @foreach($accounts ?? [] as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->name }} ({{ $acc->type ?? $acc->account_type ?? 'Vendor' }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Vendor Contact -->
                <div>
                    <label class="block text-xs font-semibold text-zinc-800 dark:text-zinc-200 mb-1">
                        Vendor Contact Person
                    </label>
                    <select
                        id="field-contact_id"
                        class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        <option value="">Select Vendor Contact...</option>
                        @foreach($contacts ?? [] as $c)
                            <option value="{{ $c->id }}" data-account-id="{{ $c->account_id }}">
                                {{ $c->first_name }} {{ $c->last_name }} ({{ $c->email ?? 'No email' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Backed Sales Opportunity (Coupling) -->
                <div>
                    <label class="block text-xs font-semibold text-zinc-800 dark:text-zinc-200 mb-1">
                        Backed Sales Opportunity <span class="text-zinc-400 font-normal">(Back-to-Back Margin)</span>
                    </label>
                    <select
                        id="field-opportunity_id"
                        class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        <option value="">No linked sales opportunity (Independent PO)</option>
                        @foreach($opportunities ?? [] as $opp)
                            <option value="{{ $opp->id }}">{{ $opp->name }} (${{ number_format($opp->amount ?? 0, 0) }} • {{ $opp->stage }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- PO Total Spend & Currency -->
                <div>
                    <label class="block text-xs font-semibold text-zinc-800 dark:text-zinc-200 mb-1">
                        PO Total Spend &amp; Currency <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-center gap-2">
                        <select
                            id="field-currency"
                            class="w-24 px-2 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                            <option value="USD">USD ($)</option>
                            <option value="EUR">EUR (€)</option>
                            <option value="GBP">GBP (£)</option>
                            <option value="INR">INR (₹)</option>
                            <option value="CAD">CAD ($)</option>
                            <option value="AUD">AUD ($)</option>
                            <option value="JPY">JPY (¥)</option>
                            <option value="AED">AED (د.إ)</option>
                            <option value="SGD">SGD ($)</option>
                            <option value="CHF">CHF (Fr)</option>
                        </select>
                        <input
                            type="number"
                            step="0.01"
                            id="field-amount"
                            required
                            placeholder="0.00"
                            class="flex-1 px-3 py-2 text-xs font-bold rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                    </div>
                </div>

                <!-- Procurement Stage -->
                <div>
                    <label class="block text-xs font-semibold text-zinc-800 dark:text-zinc-200 mb-1">
                        Procurement Stage <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="field-stage"
                        required
                        class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        @foreach($stages ?? [] as $st)
                            <option value="{{ $st }}">{{ $st }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Target Delivery Date -->
                <div>
                    <label class="block text-xs font-semibold text-zinc-800 dark:text-zinc-200 mb-1">
                        Target Delivery Date
                    </label>
                    <input
                        type="date"
                        id="field-expected_delivery_date"
                        class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                </div>

                <!-- Payment Terms -->
                <div>
                    <label class="block text-xs font-semibold text-zinc-800 dark:text-zinc-200 mb-1">
                        Payment Terms
                    </label>
                    <select
                        id="field-payment_terms"
                        class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        <option value="Net 15">Net 15</option>
                        <option value="Net 30" selected>Net 30</option>
                        <option value="Net 45">Net 45</option>
                        <option value="Net 60">Net 60</option>
                        <option value="Advance Wire">Advance Wire</option>
                        <option value="Cash on Delivery">Cash on Delivery (COD)</option>
                    </select>
                </div>

                <!-- Assigned Buyer / Owner -->
                <div>
                    <label class="block text-xs font-semibold text-zinc-800 dark:text-zinc-200 mb-1">
                        Assigned Procurement Buyer
                    </label>
                    <select
                        id="field-assigned_to"
                        class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        @foreach($users ?? [] as $u)
                            <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Order Notes & Instructions -->
            <div>
                <label class="block text-xs font-semibold text-zinc-800 dark:text-zinc-200 mb-1">
                    Order Notes &amp; Logistics Instructions
                </label>
                <textarea
                    id="field-notes"
                    rows="2"
                    placeholder="Enter special shipping instructions, RMA provisions, or customs documentation notes..."
                    class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                ></textarea>
            </div>

            <!-- Dynamic Custom Fields Section with Field-Level Security -->
            <div id="modal-custom-fields-wrapper" class="space-y-3 pt-3 border-t border-zinc-200 dark:border-zinc-800">
                <div class="flex items-center justify-between">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-zinc-600 dark:text-zinc-400">
                        Dynamic Procurement Fields (Synced with Governance)
                    </h4>
                    <span class="text-[11px] text-zinc-400">Respects Field Permissions</span>
                </div>
                <div id="modal-custom-fields-container" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Dynamic custom field inputs injected here via JS -->
                </div>
            </div>

            <!-- Modal Footer Actions -->
            <div class="pt-4 border-t border-zinc-200 dark:border-zinc-800 flex items-center justify-end gap-2.5">
                <button
                    type="button"
                    onclick="window.closePurchaseDealModal()"
                    class="px-4 py-2 text-xs font-semibold rounded-lg border border-zinc-300 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    id="btn-save-deal"
                    class="px-5 py-2 text-xs font-semibold rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white transition-colors cursor-pointer shadow-xs"
                >
                    Save Purchase Deal
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 2: CUSTOMIZE VISIBLE FIELDS MODAL -->
<div id="po-config-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-xs animate-fade-in">
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xl w-full max-w-md overflow-hidden">
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between">
            <div class="flex items-center gap-2 text-zinc-800 dark:text-zinc-200">
                <i data-lucide="sliders" class="w-4 h-4 text-indigo-600"></i>
                <h3 class="text-sm font-bold text-zinc-950 dark:text-zinc-100">Customize Visible Fields</h3>
            </div>
            <button type="button" onclick="window.closePurchaseDealConfigModal()" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 cursor-pointer">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
        <div class="p-6 space-y-4">
            <p class="text-xs text-zinc-500">
                Select which standard and dynamic custom fields are displayed in the active view:
            </p>
            <div id="config-fields-list" class="space-y-2 max-h-60 overflow-y-auto pr-1">
                <!-- Checkboxes populated via JS -->
            </div>
            <div class="flex items-center justify-between pt-3 border-t border-zinc-200 dark:border-zinc-800">
                <button type="button" onclick="window.resetDefaultVisibleFields()" class="text-xs text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200 underline cursor-pointer">
                    Reset Defaults
                </button>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="window.closePurchaseDealConfigModal()" class="px-3 py-1.5 text-xs font-semibold rounded-lg border border-zinc-300 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 cursor-pointer">
                        Cancel
                    </button>
                    <button type="button" onclick="window.saveVisibleFieldsConfig()" class="px-3.5 py-1.5 text-xs font-semibold rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white cursor-pointer shadow-xs">
                        Apply Fields
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL 3: UNIVERSAL FILTER MODAL -->
<div id="po-filter-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-xs animate-fade-in">
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xl w-full max-w-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between">
            <div class="flex items-center gap-2 text-zinc-800 dark:text-zinc-200">
                <i data-lucide="filter" class="w-4 h-4 text-indigo-600"></i>
                <h3 class="text-sm font-bold text-zinc-950 dark:text-zinc-100">Filter Purchase Deals</h3>
            </div>
            <button type="button" onclick="window.closePurchaseDealFilterModal()" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 cursor-pointer">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
        <div class="p-6 space-y-4">
            <div id="filter-rules-list" class="space-y-3 max-h-64 overflow-y-auto pr-1">
                <!-- Filter rule rows injected dynamically -->
            </div>

            <button
                type="button"
                onclick="window.addFilterRuleRow()"
                class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 cursor-pointer"
            >
                <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i>
                <span>Add Filter Condition</span>
            </button>

            <div class="flex items-center justify-between pt-3 border-t border-zinc-200 dark:border-zinc-800">
                <button type="button" onclick="window.clearAllFilters()" class="text-xs text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200 underline cursor-pointer">
                    Clear All
                </button>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="window.closePurchaseDealFilterModal()" class="px-3 py-1.5 text-xs font-semibold rounded-lg border border-zinc-300 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 cursor-pointer">
                        Cancel
                    </button>
                    <button type="button" onclick="window.applyFilterRules()" class="px-3.5 py-1.5 text-xs font-semibold rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white cursor-pointer shadow-xs">
                        Apply Filter
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL 4: TRANSFER OWNER MODAL -->
<div id="po-transfer-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-xs animate-fade-in">
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xl w-full max-w-sm overflow-hidden p-5 space-y-4">
        <div class="flex items-center gap-2.5 text-indigo-600">
            <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-950/50 flex items-center justify-center">
                <i data-lucide="user-check" class="w-4 h-4"></i>
            </div>
            <div>
                <h3 class="text-sm font-bold text-zinc-950 dark:text-zinc-100">Transfer Purchase Deal</h3>
                <p class="text-[11px] text-zinc-500">Reassign buyer ownership &amp; PO management</p>
            </div>
        </div>
        <input type="hidden" id="transfer-deal-id" value="">
        <p class="text-xs text-zinc-600 dark:text-zinc-400" id="transfer-deal-name-label">
            Select a new team member to take ownership of this purchase order.
        </p>
        <div>
            <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300 mb-1">New Owner</label>
            <select id="transfer-new-owner-id" class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                @foreach($users ?? [] as $u)
                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-center justify-end gap-2 pt-2">
            <button type="button" onclick="window.closePurchaseDealTransferModal()" class="px-3 py-1.5 text-xs font-semibold rounded-lg border border-zinc-300 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 cursor-pointer">
                Cancel
            </button>
            <button type="button" onclick="window.submitPurchaseDealTransfer()" class="px-3.5 py-1.5 text-xs font-semibold rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white cursor-pointer shadow-xs">
                Confirm Reassign
            </button>
        </div>
    </div>
</div>

<!-- Safe Blade JSON Hydration with Zero Bracket/Parentheses Nesting Issues -->
@php
    $purchaseDealsHydrationData = [
        'initialPurchaseDeals' => $initialPurchaseDeals ?? [],
        'customFields' => $customFields ?? [],
        'fieldPermissions' => $fieldPermissions ?? (object)[],
        'accounts' => $accounts ?? [],
        'contacts' => $contacts ?? [],
        'opportunities' => $opportunities ?? [],
        'users' => $users ?? [],
        'stages' => $stages ?? ['Draft', 'RFQ Sent', 'Quote Received', 'PO Issued', 'In Production', 'Completed'],
        'currentUserProfile' => $currentUserProfile ?? 'Administrator',
    ];
@endphp
<script>
    window.__PURCHASE_DEALS_DATA__ = {!! json_encode($purchaseDealsHydrationData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};
</script>
<script src="{{ asset('js/purchase_deals.js') }}?v=1.0"></script>
@endsection
