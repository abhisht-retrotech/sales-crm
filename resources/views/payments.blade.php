@extends('layouts.app')

@section('title', 'Invoicing & Settlement Ledger — DealSync Enterprise CRM')

@section('content')
<!-- Centered Max-Width Container matching React page-wrapper (max-width: 1240px, padding: 2.25rem 2.5rem) -->
<div id="payments-page-root" class="max-w-[1240px] mx-auto w-full px-6 sm:px-10 py-7 space-y-6 animate-fade-in">
    <!-- Top Action Header -->
    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4 pb-2 border-b border-zinc-200 dark:border-zinc-800">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-purple-50 text-purple-700 border border-purple-200 dark:bg-purple-950/40 dark:text-purple-300 dark:border-purple-800">
                    <i data-lucide="credit-card" class="w-3.5 h-3.5"></i>
                    Financial Operations &amp; Ledger
                </span>
                <span class="text-xs text-zinc-500 dark:text-zinc-400">
                    Accounts Receivable (AR) &amp; Accounts Payable (AP)
                </span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-950 dark:text-zinc-100">
                Invoicing &amp; Settlement Ledger
            </h1>
            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 max-w-2xl">
                Accounts Receivable customer billing, Accounts Payable vendor invoices, partial settlements, and due dates.
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap shrink-0">
            <!-- Export CSV Button -->
            <button
                type="button"
                onclick="window.exportPaymentsCsv()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs cursor-pointer"
            >
                <i data-lucide="download" class="w-3.5 h-3.5 text-zinc-500"></i>
                <span>Export CSV</span>
            </button>

            <!-- Generate Formal Document Link -->
            <a
                href="/documents"
                class="spa-nav-link inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs no-underline hover:no-underline"
            >
                <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5 text-indigo-600"></i>
                <span>Document Vault</span>
            </a>

            <!-- Quick Invoice Primary Action -->
            <button
                type="button"
                onclick="window.openQuickInvoiceModal('create')"
                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm transition-colors cursor-pointer"
            >
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>+ Quick Invoice</span>
            </button>
        </div>
    </div>

    <!-- Top KPI Summary Cards (4 Cards) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Total Invoiced -->
        <div class="bg-white dark:bg-zinc-900 rounded-2xl p-5 border border-zinc-200 dark:border-zinc-800 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">TOTAL INVOICED</span>
                <div class="p-2 rounded-xl bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400">
                    <i data-lucide="dollar-sign" class="w-4 h-4"></i>
                </div>
            </div>
            <div id="kpi-total-invoiced" class="text-2xl font-black text-zinc-950 dark:text-white tracking-tight">
                {{ $kpis['totalInvoicedFormatted'] ?? '$0.00' }}
            </div>
            <span class="text-[11px] text-zinc-400 mt-1"><span id="kpi-entries-count">{{ $kpis['totalCount'] ?? 0 }}</span> Ledger Entries</span>
        </div>

        <!-- Card 2: Total Collected -->
        <div class="bg-white dark:bg-zinc-900 rounded-2xl p-5 border border-zinc-200 dark:border-zinc-800 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">TOTAL COLLECTED</span>
                <div class="p-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400">
                    <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                </div>
            </div>
            <div id="kpi-total-collected" class="text-2xl font-black text-emerald-600 dark:text-emerald-400 tracking-tight">
                {{ $kpis['totalCollectedFormatted'] ?? '$0.00' }}
            </div>
            <span class="text-[11px] text-emerald-600/80 font-semibold mt-1">Cash In Deposit Accounts</span>
        </div>

        <!-- Card 3: Outstanding AR -->
        <div class="bg-white dark:bg-zinc-900 rounded-2xl p-5 border border-zinc-200 dark:border-zinc-800 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">OUTSTANDING AR (RECEIVABLES)</span>
                <div class="p-2 rounded-xl bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400">
                    <i data-lucide="arrow-down-left" class="w-4 h-4"></i>
                </div>
            </div>
            <div id="kpi-outstanding-ar" class="text-2xl font-black text-blue-600 dark:text-blue-400 tracking-tight">
                {{ $kpis['outstandingARFormatted'] ?? '$0.00' }}
            </div>
            <span class="text-[11px] text-zinc-400 mt-1">Client Due Balance</span>
        </div>

        <!-- Card 4: Outstanding AP -->
        <div class="bg-white dark:bg-zinc-900 rounded-2xl p-5 border border-zinc-200 dark:border-zinc-800 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">OUTSTANDING AP (PAYABLES)</span>
                <div class="p-2 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400">
                    <i data-lucide="arrow-up-right" class="w-4 h-4"></i>
                </div>
            </div>
            <div id="kpi-outstanding-ap" class="text-2xl font-black text-amber-600 dark:text-amber-400 tracking-tight">
                {{ $kpis['outstandingAPFormatted'] ?? '$0.00' }}
            </div>
            <span class="text-[11px] text-zinc-400 mt-1">Vendor Spend Liabilities</span>
        </div>
    </div>

    <!-- Controls Toolbar (Search, Category Filter, View Switcher, Customize, Filter) -->
    <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 border border-zinc-200 dark:border-zinc-800 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-3">
        <!-- Search Input -->
        <div class="relative flex-1 max-w-md">
            <i data-lucide="search" class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
            <input
                type="text"
                id="pay-search-input"
                placeholder="Search invoices by reference #, account..."
                class="w-full text-xs pl-9 pr-4 py-2 rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all"
                oninput="window.handlePaymentSearch(this.value)"
            >
        </div>

        <!-- Controls: Category Pills, View Switcher, Customize, Filter -->
        <div class="flex items-center gap-2 flex-wrap">
            <!-- Category Segmented Filter Control -->
            <div class="inline-flex items-center p-0.5 rounded-lg bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700">
                <button
                    type="button"
                    id="pay-cat-tab-all"
                    onclick="window.switchPaymentCategoryTab('')"
                    class="px-2.5 py-1 text-xs font-semibold rounded-md transition-all cursor-pointer bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white shadow-2xs"
                >
                    All Invoices (<span id="count-cat-all">0</span>)
                </button>
                <button
                    type="button"
                    id="pay-cat-tab-ar"
                    onclick="window.switchPaymentCategoryTab('Accounts Receivable')"
                    class="px-2.5 py-1 text-xs font-medium rounded-md transition-all cursor-pointer text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white"
                >
                    AR (Client) (<span id="count-cat-ar">0</span>)
                </button>
                <button
                    type="button"
                    id="pay-cat-tab-ap"
                    onclick="window.switchPaymentCategoryTab('Accounts Payable')"
                    class="px-2.5 py-1 text-xs font-medium rounded-md transition-all cursor-pointer text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white"
                >
                    AP (Vendor) (<span id="count-cat-ap">0</span>)
                </button>
            </div>

            <!-- View Switcher (Table vs Kanban) -->
            <div class="inline-flex items-center p-0.5 rounded-lg bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700">
                <button
                    type="button"
                    id="pay-view-btn-table"
                    onclick="window.switchPaymentView('table')"
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-md transition-all cursor-pointer bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white shadow-2xs"
                >
                    <i data-lucide="table" class="w-3.5 h-3.5"></i>
                    <span>Table</span>
                </button>
                <button
                    type="button"
                    id="pay-view-btn-kanban"
                    onclick="window.switchPaymentView('kanban')"
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-md transition-all cursor-pointer text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white"
                >
                    <i data-lucide="kanban" class="w-3.5 h-3.5"></i>
                    <span>Kanban</span>
                </button>
            </div>

            <!-- Customize Visible Columns -->
            <button
                type="button"
                onclick="window.openPaymentConfigModal()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs cursor-pointer"
                title="Customize Visible Fields"
            >
                <i data-lucide="sliders" class="w-3.5 h-3.5 text-zinc-500"></i>
                <span>Customize</span>
            </button>

            <!-- Filter Button -->
            <button
                type="button"
                onclick="window.openPaymentFilterModal()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs cursor-pointer"
                title="Filter Payments"
            >
                <i data-lucide="filter" class="w-3.5 h-3.5 text-zinc-500"></i>
                <span>Filter</span>
                <span id="pay-filter-badge" class="hidden px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-indigo-600 text-white">0</span>
            </button>
        </div>
    </div>

    <!-- Active Filter Chips -->
    <div id="pay-active-filter-chips" class="hidden flex items-center gap-2 flex-wrap">
        <!-- Injected via JavaScript -->
    </div>

    <!-- VIEW 1: COMPACT TABLE VIEW -->
    <div id="pay-container-table" class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-zinc-700 dark:text-zinc-300">
                <thead class="bg-zinc-50 dark:bg-zinc-800/60 text-[11px] font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider border-b border-zinc-200 dark:border-zinc-800">
                    <tr id="pay-table-header-row">
                        <!-- Dynamic headers injected via JS -->
                    </tr>
                </thead>
                <tbody id="pay-table-tbody" class="divide-y divide-zinc-200 dark:divide-zinc-800">
                    <!-- Dynamic rows injected via JS -->
                </tbody>
            </table>
        </div>

        <!-- Table Empty State -->
        <div id="pay-empty-state-table" class="hidden py-16 text-center space-y-3">
            <div class="w-12 h-12 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-400 flex items-center justify-center mx-auto">
                <i data-lucide="credit-card" class="w-6 h-6"></i>
            </div>
            <div class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">No invoices found</div>
            <p class="text-xs text-zinc-500 max-w-sm mx-auto">
                No invoices logged matching search or filter criteria. Click "Quick Invoice" to log a receivable or payable.
            </p>
        </div>
    </div>

    <!-- VIEW 2: UNIVERSAL KANBAN VIEW -->
    <div id="pay-container-kanban" class="hidden">
        <div id="pay-kanban-board" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-3 overflow-x-auto pb-4">
            <!-- Kanban Columns injected via JS -->
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL 1: RECORD PAYMENT SETTLEMENT DIALOG  -->
<!-- ========================================== -->
<div id="record-payment-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50 backdrop-blur-xs p-4">
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xl max-w-lg w-full flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-150">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 flex items-center justify-center">
                    <i data-lucide="dollar-sign" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-zinc-950 dark:text-white">Record Settlement Payment</h3>
                    <span id="settle-modal-invoice-num" class="text-xs font-mono font-bold text-indigo-600">INV-2026-000</span>
                </div>
            </div>
            <button type="button" onclick="window.closeRecordPaymentModal()" class="p-1 rounded-lg text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 cursor-pointer">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form id="record-payment-form" onsubmit="window.saveRecordPayment(event)" class="p-6 space-y-4">
            <!-- Account & Outstanding Balance Box -->
            <div class="bg-zinc-50 dark:bg-zinc-800/50 p-4 rounded-xl border border-zinc-200 dark:border-zinc-700 space-y-1.5 text-xs">
                <div class="flex items-center justify-between">
                    <span class="text-zinc-500">Billed Account:</span>
                    <strong id="settle-box-account" class="text-zinc-900 dark:text-zinc-100 font-bold">Apex Technologies</strong>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-zinc-500">Total Invoiced:</span>
                    <strong id="settle-box-total" class="text-zinc-900 dark:text-zinc-100 font-bold">$0.00</strong>
                </div>
                <div class="flex items-center justify-between pt-1 border-t border-zinc-200 dark:border-zinc-700">
                    <span class="text-zinc-500 font-bold">Outstanding Balance:</span>
                    <span id="settle-box-balance" class="text-amber-600 dark:text-amber-400 font-black text-sm">$0.00</span>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Payment Amount to Settle ($ USD) *</label>
                <input
                    type="number"
                    step="any"
                    min="0.01"
                    id="settle-input-amount"
                    required
                    placeholder="Enter amount to record"
                    class="w-full text-sm font-bold rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-indigo-500"
                >
            </div>

            <div>
                <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Notes / Payment Method / Reference</label>
                <input
                    type="text"
                    id="settle-input-notes"
                    placeholder="e.g. Bank Wire #WT-9921, ACH Transfer, or Check #4920"
                    class="w-full text-xs rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-indigo-500"
                >
            </div>

            <div class="pt-3 border-t border-zinc-200 dark:border-zinc-800 flex justify-end gap-2">
                <button type="button" onclick="window.closeRecordPaymentModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer">
                    Cancel
                </button>
                <button type="submit" id="btn-submit-settle-pay" class="px-4 py-2 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs transition-colors cursor-pointer flex items-center gap-1.5">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Confirm Settlement</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL 2: QUICK INVOICE MODAL               -->
<!-- ========================================== -->
<div id="quick-invoice-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50 backdrop-blur-xs p-4">
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xl max-w-xl w-full flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-150">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between shrink-0">
            <div>
                <h3 id="quick-inv-modal-title" class="text-base font-bold text-zinc-950 dark:text-white">Quick Invoice Entry</h3>
                <p class="text-xs text-zinc-500">Record a customer billing (AR) or supplier liability invoice (AP).</p>
            </div>
            <button type="button" onclick="window.closeQuickInvoiceModal()" class="p-1 rounded-lg text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 cursor-pointer">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form id="quick-invoice-form" onsubmit="window.saveQuickInvoice(event)" class="p-6 space-y-4">
            <input type="hidden" id="quick-inv-id" value="">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Invoice Number</label>
                    <input type="text" id="quick-inv-number" placeholder="Auto-generated if empty" class="w-full text-xs font-mono rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Category *</label>
                    <select id="quick-inv-type" required class="w-full text-xs rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-indigo-500">
                        <option value="Accounts Receivable">Accounts Receivable (Client)</option>
                        <option value="Accounts Payable">Accounts Payable (Vendor)</option>
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Billed Account Organization *</label>
                    <select id="quick-inv-account" required class="w-full text-xs rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-indigo-500">
                        <option value="">-- Select Client / Supplier --</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->name }} ({{ $acc->type ?? 'Organization' }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Invoice Amount ($) *</label>
                    <input type="number" step="any" min="0.01" id="quick-inv-amount" required placeholder="0.00" class="w-full text-xs font-bold rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Paid Amount ($)</label>
                    <input type="number" step="any" min="0" id="quick-inv-paid" value="0" class="w-full text-xs rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Settlement Status *</label>
                    <select id="quick-inv-status" required class="w-full text-xs rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-indigo-500">
                        @foreach($stages as $st)
                            <option value="{{ $st }}">{{ $st }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Currency</label>
                    <select id="quick-inv-currency" class="w-full text-xs rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-indigo-500">
                        <option value="USD">USD ($)</option>
                        <option value="EUR">EUR (€)</option>
                        <option value="GBP">GBP (£)</option>
                        <option value="INR">INR (₹)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Issue Date</label>
                    <input type="date" id="quick-inv-issue-date" value="{{ date('Y-m-d') }}" class="w-full text-xs rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Maturity Due Date</label>
                    <input type="date" id="quick-inv-due-date" value="{{ date('Y-m-d', strtotime('+30 days')) }}" class="w-full text-xs rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Payment Terms / Notes</label>
                    <textarea id="quick-inv-notes" rows="2" placeholder="Wire details, terms conditions, or delivery remarks..." class="w-full text-xs rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>
            </div>

            <div class="pt-4 border-t border-zinc-200 dark:border-zinc-800 flex justify-end gap-2">
                <button type="button" onclick="window.closeQuickInvoiceModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer">
                    Cancel
                </button>
                <button type="submit" id="btn-save-quick-inv" class="px-4 py-2 rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white shadow-xs transition-colors cursor-pointer flex items-center gap-1.5">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Save Invoice</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL 3: CUSTOMIZE VISIBLE COLUMNS MODAL   -->
<!-- ========================================== -->
<div id="payment-config-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50 backdrop-blur-xs p-4">
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xl max-w-md w-full p-6 space-y-4">
        <div class="flex items-center justify-between pb-2 border-b border-zinc-200 dark:border-zinc-800">
            <h3 class="text-sm font-bold text-zinc-950 dark:text-white">Customize Visible Fields</h3>
            <button type="button" onclick="window.closePaymentConfigModal()" class="text-zinc-400 hover:text-zinc-600">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
        <div id="payment-config-checkboxes" class="space-y-2 max-h-72 overflow-y-auto">
            <!-- Checkboxes injected via JS -->
        </div>
        <div class="pt-3 border-t border-zinc-200 dark:border-zinc-800 flex justify-end gap-2">
            <button type="button" onclick="window.closePaymentConfigModal()" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-zinc-600 hover:bg-zinc-100">Cancel</button>
            <button type="button" onclick="window.savePaymentConfig()" class="px-3.5 py-1.5 rounded-lg text-xs font-bold bg-indigo-600 text-white">Save Columns</button>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL 4: FILTER MODAL                      -->
<!-- ========================================== -->
<div id="payment-filter-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50 backdrop-blur-xs p-4">
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xl max-w-md w-full p-6 space-y-4">
        <div class="flex items-center justify-between pb-2 border-b border-zinc-200 dark:border-zinc-800">
            <h3 class="text-sm font-bold text-zinc-950 dark:text-white">Filter Invoices &amp; Settlements</h3>
            <button type="button" onclick="window.closePaymentFilterModal()" class="text-zinc-400 hover:text-zinc-600">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
        <div class="space-y-3">
            <div>
                <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Settlement Status</label>
                <select id="filter-pay-status" class="w-full text-xs rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-zinc-900 dark:text-zinc-100">
                    <option value="">All Statuses</option>
                    @foreach($stages as $st)
                        <option value="{{ $st }}">{{ $st }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Account Organization</label>
                <select id="filter-pay-account" class="w-full text-xs rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-zinc-900 dark:text-zinc-100">
                    <option value="">All Accounts</option>
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="pt-3 border-t border-zinc-200 dark:border-zinc-800 flex justify-between items-center">
            <button type="button" onclick="window.resetPaymentFilters()" class="text-xs text-red-600 font-semibold hover:underline">Reset Filters</button>
            <div class="flex gap-2">
                <button type="button" onclick="window.closePaymentFilterModal()" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-zinc-600 hover:bg-zinc-100">Cancel</button>
                <button type="button" onclick="window.applyPaymentFilters()" class="px-3.5 py-1.5 rounded-lg text-xs font-bold bg-indigo-600 text-white">Apply</button>
            </div>
        </div>
    </div>
</div>

<!-- Safe Blade JSON Hydration with Zero Bracket/Parentheses Nesting Issues -->
@php
    $paymentsData = [
        'initialPayments' => $initialPayments ?? [],
        'kpis' => $kpis ?? [],
        'accounts' => $accounts ?? [],
        'stages' => $stages ?? ['Draft', 'Issued', 'Partially Paid', 'Paid', 'Overdue'],
        'categories' => $categories ?? ['Accounts Receivable', 'Accounts Payable'],
        'fieldPermissions' => $fieldPermissions ?? (object)[],
    ];
@endphp
<script>
    window.__PAYMENTS_DATA__ = {!! json_encode($paymentsData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};
</script>
<script src="{{ asset('js/payments.js') }}?v=1.0"></script>
@endsection
