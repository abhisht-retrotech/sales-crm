@extends('layouts.app')

@section('title', 'Billing & Ledger Documents — DealSync Enterprise CRM')

@section('content')
<!-- Centered Max-Width Container matching React page-wrapper (max-width: 1240px, padding: 2.25rem 2.5rem) -->
<div id="documents-page-root" class="max-w-[1240px] mx-auto w-full px-6 sm:px-10 py-7 space-y-6 animate-fade-in">
    <!-- Top Action Header -->
    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4 pb-2 border-b border-zinc-200 dark:border-zinc-800">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 dark:bg-indigo-950/40 dark:text-indigo-300 dark:border-indigo-800">
                    <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5"></i>
                    Enterprise Document Vault
                </span>
                <span class="text-xs text-zinc-500 dark:text-zinc-400">
                    Financial Governance &amp; Legal Immutability
                </span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-950 dark:text-zinc-100">
                Billing &amp; Ledger
            </h1>
            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 max-w-2xl">
                Enterprise Invoices, Purchase Orders, and Quotations with immutable snapshots, approval locking, and file vault routing.
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap shrink-0">
            <!-- Export CSV Button -->
            <button
                type="button"
                onclick="window.exportDocumentsCsv()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs cursor-pointer"
            >
                <i data-lucide="download" class="w-3.5 h-3.5 text-zinc-500"></i>
                <span>Export Ledger</span>
            </button>

            <!-- Generate Document Primary Action -->
            <button
                type="button"
                onclick="window.openGenerateDocumentModal('invoice')"
                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm transition-colors cursor-pointer"
            >
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>+ Generate Document</span>
            </button>
        </div>
    </div>

    <!-- KPI Metric Summary Tiles (4 Cards) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Total Invoiced (AR) -->
        <div class="bg-white dark:bg-zinc-900 rounded-2xl p-5 border border-zinc-200 dark:border-zinc-800 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Total Invoiced (AR)</span>
                <div class="p-2 rounded-xl bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400">
                    <i data-lucide="receipt" class="w-4 h-4"></i>
                </div>
            </div>
            <div id="kpi-total-invoiced" class="text-2xl font-black text-zinc-950 dark:text-white tracking-tight">
                {{ $kpis['totalInvoicedFormatted'] ?? '$0.00' }}
            </div>
            <span class="text-[11px] text-zinc-400 mt-1">Active Commercial Invoices</span>
        </div>

        <!-- Card 2: Purchase Orders (AP) -->
        <div class="bg-white dark:bg-zinc-900 rounded-2xl p-5 border border-zinc-200 dark:border-zinc-800 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Purchase Orders (AP)</span>
                <div class="p-2 rounded-xl bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400">
                    <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                </div>
            </div>
            <div id="kpi-total-po" class="text-2xl font-black text-zinc-950 dark:text-white tracking-tight">
                {{ $kpis['totalPOFormatted'] ?? '$0.00' }}
            </div>
            <span class="text-[11px] text-zinc-400 mt-1">Supplier Commitments</span>
        </div>

        <!-- Card 3: Quotations Pipeline -->
        <div class="bg-white dark:bg-zinc-900 rounded-2xl p-5 border border-zinc-200 dark:border-zinc-800 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Quotations Pipeline</span>
                <div class="p-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400">
                    <i data-lucide="file-text" class="w-4 h-4"></i>
                </div>
            </div>
            <div id="kpi-total-quote" class="text-2xl font-black text-zinc-950 dark:text-white tracking-tight">
                {{ $kpis['totalQuoteFormatted'] ?? '$0.00' }}
            </div>
            <span class="text-[11px] text-zinc-400 mt-1">Active Customer Proposals</span>
        </div>

        <!-- Card 4: Pending Governance -->
        <div class="bg-white dark:bg-zinc-900 rounded-2xl p-5 border border-zinc-200 dark:border-zinc-800 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Pending Governance</span>
                <div class="p-2 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400">
                    <i data-lucide="lock" class="w-4 h-4"></i>
                </div>
            </div>
            <div id="kpi-pending-approval" class="text-2xl font-black {{ ($kpis['pendingApprovalCount'] ?? 0) > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-zinc-950 dark:text-white' }} tracking-tight">
                {{ $kpis['pendingApprovalCount'] ?? 0 }}
            </div>
            <span class="text-[11px] text-zinc-400 mt-1">Awaiting Manager Review</span>
        </div>
    </div>

    <!-- Controls Toolbar (Search, Type Tabs, View Switcher, Customize, Filter) -->
    <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 border border-zinc-200 dark:border-zinc-800 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-3">
        <!-- Search Input -->
        <div class="relative flex-1 max-w-md">
            <i data-lucide="search" class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
            <input
                type="text"
                id="doc-search-input"
                placeholder="Search documents by number, account, reference..."
                class="w-full text-xs pl-9 pr-4 py-2 rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all"
                oninput="window.handleDocumentSearch(this.value)"
            >
        </div>

        <!-- Controls: Tabs, View Switcher, Customize, Filter -->
        <div class="flex items-center gap-2 flex-wrap">
            <!-- Document Type Segmented Tabs -->
            <div class="inline-flex items-center p-0.5 rounded-lg bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700">
                <button
                    type="button"
                    id="doc-type-tab-all"
                    onclick="window.switchDocumentTypeTab('all')"
                    class="px-2.5 py-1 text-xs font-semibold rounded-md transition-all cursor-pointer bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white shadow-2xs"
                >
                    All Documents (<span id="count-type-all">0</span>)
                </button>
                <button
                    type="button"
                    id="doc-type-tab-invoice"
                    onclick="window.switchDocumentTypeTab('invoice')"
                    class="px-2.5 py-1 text-xs font-medium rounded-md transition-all cursor-pointer text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white"
                >
                    Invoices (<span id="count-type-invoice">0</span>)
                </button>
                <button
                    type="button"
                    id="doc-type-tab-po"
                    onclick="window.switchDocumentTypeTab('po')"
                    class="px-2.5 py-1 text-xs font-medium rounded-md transition-all cursor-pointer text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white"
                >
                    Purchase Orders (<span id="count-type-po">0</span>)
                </button>
                <button
                    type="button"
                    id="doc-type-tab-quote"
                    onclick="window.switchDocumentTypeTab('quote')"
                    class="px-2.5 py-1 text-xs font-medium rounded-md transition-all cursor-pointer text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white"
                >
                    Quotes (<span id="count-type-quote">0</span>)
                </button>
            </div>

            <!-- View Switcher (Table vs Kanban) -->
            <div class="inline-flex items-center p-0.5 rounded-lg bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700">
                <button
                    type="button"
                    id="doc-view-btn-table"
                    onclick="window.switchDocumentView('table')"
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-md transition-all cursor-pointer bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white shadow-2xs"
                >
                    <i data-lucide="table" class="w-3.5 h-3.5"></i>
                    <span>Table</span>
                </button>
                <button
                    type="button"
                    id="doc-view-btn-kanban"
                    onclick="window.switchDocumentView('kanban')"
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-md transition-all cursor-pointer text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white"
                >
                    <i data-lucide="kanban" class="w-3.5 h-3.5"></i>
                    <span>Kanban</span>
                </button>
            </div>

            <!-- Customize Visible Columns -->
            <button
                type="button"
                onclick="window.openDocumentConfigModal()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs cursor-pointer"
                title="Customize Visible Columns"
            >
                <i data-lucide="sliders" class="w-3.5 h-3.5 text-zinc-500"></i>
                <span>Customize</span>
            </button>

            <!-- Filter Button -->
            <button
                type="button"
                onclick="window.openDocumentFilterModal()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs cursor-pointer"
                title="Filter Documents"
            >
                <i data-lucide="filter" class="w-3.5 h-3.5 text-zinc-500"></i>
                <span>Filter</span>
                <span id="doc-filter-badge" class="hidden px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-indigo-600 text-white">0</span>
            </button>
        </div>
    </div>

    <!-- Active Filter Chips -->
    <div id="doc-active-filter-chips" class="hidden flex items-center gap-2 flex-wrap">
        <!-- Injected via JavaScript -->
    </div>

    <!-- VIEW 1: COMPACT TABLE VIEW -->
    <div id="doc-container-table" class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-zinc-700 dark:text-zinc-300">
                <thead class="bg-zinc-50 dark:bg-zinc-800/60 text-[11px] font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider border-b border-zinc-200 dark:border-zinc-800">
                    <tr id="doc-table-header-row">
                        <!-- Dynamic headers injected via JS -->
                    </tr>
                </thead>
                <tbody id="doc-table-tbody" class="divide-y divide-zinc-200 dark:divide-zinc-800">
                    <!-- Dynamic rows injected via JS -->
                </tbody>
            </table>
        </div>

        <!-- Table Empty State -->
        <div id="doc-empty-state-table" class="hidden py-16 text-center space-y-3">
            <div class="w-12 h-12 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-400 flex items-center justify-center mx-auto">
                <i data-lucide="file-spreadsheet" class="w-6 h-6"></i>
            </div>
            <div class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">No documents found</div>
            <p class="text-xs text-zinc-500 max-w-sm mx-auto">
                No financial documents matched your search or filter criteria. Click "Generate Document" to create an invoice or PO.
            </p>
        </div>
    </div>

    <!-- VIEW 2: UNIVERSAL KANBAN VIEW -->
    <div id="doc-container-kanban" class="hidden">
        <div id="doc-kanban-board" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-8 gap-3 overflow-x-auto pb-4">
            <!-- Kanban Columns injected via JS -->
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL 1: GENERATE DOCUMENT MODAL           -->
<!-- ========================================== -->
<div id="generate-document-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50 backdrop-blur-xs p-4">
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xl max-w-4xl w-full max-h-[92vh] flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-150">
        <!-- Modal Header -->
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between shrink-0">
            <div>
                <h3 class="text-base font-bold text-zinc-950 dark:text-white flex items-center gap-2">
                    <i data-lucide="file-plus" class="w-5 h-5 text-indigo-600"></i>
                    <span>Generate Financial Document</span>
                </h3>
                <p class="text-xs text-zinc-500">Create immutable commercial invoices, purchase orders, or client quotations.</p>
            </div>
            <button type="button" onclick="window.closeGenerateDocumentModal()" class="p-1 rounded-lg text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 cursor-pointer">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Modal Body Form -->
        <form id="generate-document-form" onsubmit="window.saveGenerateDocument(event)" class="flex-1 overflow-y-auto p-6 space-y-6">
            <!-- Top Controls Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Document Type *</label>
                    <select id="gen-doc-type" required onchange="window.handleGenerateDocTypeChange(this.value)" class="w-full text-xs rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-indigo-500">
                        <option value="invoice">Commercial Invoice</option>
                        <option value="po">Purchase Order (PO)</option>
                        <option value="quote">Customer Quotation</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Layout Template *</label>
                    <select id="gen-doc-template" required class="w-full text-xs rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-indigo-500">
                        @foreach($templates as $tpl)
                            <option value="{{ $tpl->id }}" data-type="{{ $tpl->document_type }}">{{ $tpl->name }} ({{ strtoupper($tpl->document_type) }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Document Number</label>
                    <input type="text" id="gen-doc-number" placeholder="Auto-generated if blank" class="w-full text-xs rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 font-mono text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Account / Client Organization *</label>
                    <select id="gen-doc-account" required onchange="window.handleGenerateAccountChange(this.value)" class="w-full text-xs rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-indigo-500">
                        <option value="">-- Select Client / Vendor --</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}" data-billing="{{ $acc->billing_address }}" data-shipping="{{ $acc->shipping_address }}">{{ $acc->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Issue Date *</label>
                    <input type="date" id="gen-doc-issue-date" required value="{{ date('Y-m-d') }}" class="w-full text-xs rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Due Date / Delivery ETA</label>
                    <input type="date" id="gen-doc-due-date" value="{{ date('Y-m-d', strtotime('+30 days')) }}" class="w-full text-xs rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Payment Terms</label>
                    <select id="gen-doc-payment-terms" class="w-full text-xs rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-indigo-500">
                        <option value="Due on Receipt">Due on Receipt</option>
                        <option value="Net 15 Days">Net 15 Days</option>
                        <option value="Net 30 Days" selected>Net 30 Days</option>
                        <option value="Net 45 Days">Net 45 Days</option>
                        <option value="Net 60 Days">Net 60 Days</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Currency</label>
                    <select id="gen-doc-currency" class="w-full text-xs rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-indigo-500">
                        <option value="USD" selected>USD ($)</option>
                        <option value="EUR">EUR (€)</option>
                        <option value="GBP">GBP (£)</option>
                        <option value="INR">INR (₹)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Overall Discount Rate (%)</label>
                    <input type="number" step="0.1" id="gen-doc-discount-rate" value="0" min="0" max="100" oninput="window.recomputeModalTotals()" class="w-full text-xs rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <!-- Party Addresses Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Billing Address</label>
                    <textarea id="gen-doc-billing-addr" rows="2" placeholder="Customer / Vendor Billing Address" class="w-full text-xs rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Shipping / Delivery Address</label>
                    <textarea id="gen-doc-shipping-addr" rows="2" placeholder="Destination Warehouse / Delivery Dock" class="w-full text-xs rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>
            </div>

            <!-- Dynamic Line Items Section -->
            <div class="space-y-3 pt-2 border-t border-zinc-200 dark:border-zinc-800">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-zinc-900 dark:text-zinc-100">Document Line Items *</h4>
                        <span class="text-[11px] text-zinc-400">Products, services, or line charges</span>
                    </div>
                    <button
                        type="button"
                        onclick="window.addLineItemRow()"
                        class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-lg bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-200 transition-colors cursor-pointer"
                    >
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                        <span>+ Add Line Item</span>
                    </button>
                </div>

                <div class="overflow-x-auto border border-zinc-200 dark:border-zinc-700 rounded-xl">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-zinc-50 dark:bg-zinc-800/60 text-[11px] font-bold text-zinc-500 uppercase tracking-wider border-b border-zinc-200 dark:border-zinc-700">
                            <tr>
                                <th class="py-2.5 px-3">Item / Description *</th>
                                <th class="py-2.5 px-2 w-20">SKU</th>
                                <th class="py-2.5 px-2 w-20 text-right">Qty *</th>
                                <th class="py-2.5 px-2 w-28 text-right">Rate *</th>
                                <th class="py-2.5 px-2 w-20 text-right">Tax %</th>
                                <th class="py-2.5 px-2 w-20 text-right">Disc %</th>
                                <th class="py-2.5 px-3 w-28 text-right">Total</th>
                                <th class="py-2.5 px-2 w-10 text-center"></th>
                            </tr>
                        </thead>
                        <tbody id="gen-doc-items-tbody" class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            <!-- Injected dynamically via JavaScript -->
                        </tbody>
                    </table>
                </div>

                <!-- Financial Totals Summary Box -->
                <div class="flex justify-end pt-2">
                    <div class="w-72 bg-zinc-50 dark:bg-zinc-800/50 p-3.5 rounded-xl border border-zinc-200 dark:border-zinc-700 space-y-1.5 text-xs">
                        <div class="flex items-center justify-between text-zinc-600 dark:text-zinc-400">
                            <span>Subtotal:</span>
                            <span id="gen-summary-subtotal" class="font-semibold text-zinc-900 dark:text-zinc-100">$0.00</span>
                        </div>
                        <div class="flex items-center justify-between text-zinc-600 dark:text-zinc-400">
                            <span>Order Discount:</span>
                            <span id="gen-summary-discount" class="font-semibold text-amber-600">-$0.00</span>
                        </div>
                        <div class="flex items-center justify-between text-zinc-600 dark:text-zinc-400">
                            <span>Tax Amount:</span>
                            <span id="gen-summary-tax" class="font-semibold text-zinc-900 dark:text-zinc-100">$0.00</span>
                        </div>
                        <div class="flex items-center justify-between text-zinc-600 dark:text-zinc-400">
                            <span>Shipping / Freight:</span>
                            <input type="number" step="any" id="gen-doc-shipping-amount" value="0" min="0" oninput="window.recomputeModalTotals()" class="w-20 text-right text-xs rounded border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-1.5 py-0.5">
                        </div>
                        <div class="flex items-center justify-between pt-2 border-t border-zinc-200 dark:border-zinc-700 font-bold text-sm text-zinc-950 dark:text-white">
                            <span>Grand Total:</span>
                            <span id="gen-summary-grandtotal" class="font-black text-indigo-600 dark:text-indigo-400 text-base">$0.00</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes & Remarks -->
            <div class="space-y-1 pt-2 border-t border-zinc-200 dark:border-zinc-800">
                <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Notes &amp; Remarks</label>
                <textarea id="gen-doc-notes" rows="2" placeholder="Payment wire instructions, delivery remarks, or customer notes..." class="w-full text-xs rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-indigo-500"></textarea>
            </div>

            <!-- Form Action Footer -->
            <div class="pt-4 border-t border-zinc-200 dark:border-zinc-800 flex items-center justify-end gap-2">
                <button type="button" onclick="window.closeGenerateDocumentModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer">
                    Cancel
                </button>
                <button type="submit" id="btn-submit-generate-doc" class="px-4 py-2 rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white shadow-xs transition-colors cursor-pointer flex items-center gap-1.5">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Generate &amp; Save Document</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL 2: DOCUMENT PREVIEW & PDF VIEWER     -->
<!-- ========================================== -->
<div id="document-preview-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/60 backdrop-blur-xs p-4">
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xl max-w-4xl w-full max-h-[95vh] flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-150">
        <!-- Top Toolbar -->
        <div class="px-6 py-3.5 bg-zinc-900 text-white flex items-center justify-between shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-white">
                    <i data-lucide="printer" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 id="prev-modal-docnumber" class="text-sm font-extrabold font-mono tracking-tight text-white">INV-2026-001</h3>
                    <span id="prev-modal-type-badge" class="text-[10px] uppercase font-bold text-zinc-400">COMMERCIAL INVOICE</span>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button
                    type="button"
                    onclick="window.printDocumentPreview()"
                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-xs font-semibold text-white transition-colors cursor-pointer"
                >
                    <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                    <span>Print</span>
                </button>

                <button
                    type="button"
                    id="prev-btn-record-payment"
                    onclick="window.promptRecordPayment()"
                    class="hidden inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-xs font-bold text-white transition-colors cursor-pointer"
                >
                    <i data-lucide="credit-card" class="w-3.5 h-3.5"></i>
                    <span>Record Payment</span>
                </button>

                <button type="button" onclick="window.closeDocumentPreviewModal()" class="p-1.5 rounded-lg text-zinc-400 hover:text-white cursor-pointer">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
        </div>

        <!-- Printable Document Canvas -->
        <div class="flex-1 overflow-y-auto p-8 bg-zinc-100 dark:bg-zinc-950/60 flex justify-center">
            <div id="printable-document-sheet" class="bg-white text-zinc-900 p-8 sm:p-12 rounded-xl shadow-lg border border-zinc-200 max-w-3xl w-full space-y-6 print:m-0 print:p-0 print:border-none print:shadow-none">
                <!-- Header: Company & Doc Title -->
                <div class="flex items-start justify-between border-b pb-6 border-zinc-200">
                    <div>
                        <h2 class="text-lg font-black text-zinc-950 uppercase tracking-tight">DealSync Enterprise Technologies</h2>
                        <div class="text-xs text-zinc-500 space-y-0.5 mt-1">
                            <div>100 Innovation Way, Suite 400</div>
                            <div>Silicon Valley, CA 94025, United States</div>
                            <div>Tax ID: US-EIN-94-3829104 | billing@dealsync-crm.com</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div id="prev-sheet-title" class="text-xl font-black text-indigo-600 uppercase tracking-tight">TAX INVOICE</div>
                        <div class="font-mono font-bold text-sm text-zinc-900 mt-1" id="prev-sheet-number">INV-2026-001</div>
                        <div id="prev-sheet-status" class="mt-1">
                            <!-- Status badge -->
                        </div>
                    </div>
                </div>

                <!-- Meta & Bill To Grid -->
                <div class="grid grid-cols-2 gap-6 text-xs">
                    <div>
                        <span class="font-bold text-zinc-400 uppercase tracking-wider block mb-1">Billed To:</span>
                        <div id="prev-sheet-client" class="font-bold text-sm text-zinc-950">Apex Technologies</div>
                        <div id="prev-sheet-billing-addr" class="text-zinc-600 whitespace-pre-line mt-0.5">Corporate HQ, Suite 500, New York, NY</div>
                    </div>
                    <div class="space-y-1 text-right">
                        <div><span class="text-zinc-400">Issue Date:</span> <strong id="prev-sheet-issue-date">Mar 15, 2026</strong></div>
                        <div><span class="text-zinc-400">Payment Due:</span> <strong id="prev-sheet-due-date">Apr 15, 2026</strong></div>
                        <div><span class="text-zinc-400">Payment Terms:</span> <strong id="prev-sheet-terms">Net 30 Days</strong></div>
                    </div>
                </div>

                <!-- Line Items Table -->
                <div class="overflow-hidden border border-zinc-200 rounded-lg">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-zinc-50 border-b border-zinc-200 font-bold text-zinc-600 uppercase text-[11px]">
                            <tr>
                                <th class="py-2.5 px-3">#</th>
                                <th class="py-2.5 px-3">Description</th>
                                <th class="py-2.5 px-3 text-right">Qty</th>
                                <th class="py-2.5 px-3 text-right">Rate</th>
                                <th class="py-2.5 px-3 text-right">Tax</th>
                                <th class="py-2.5 px-3 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody id="prev-sheet-items-tbody" class="divide-y divide-zinc-200">
                            <!-- Injected via JS -->
                        </tbody>
                    </table>
                </div>

                <!-- Financial Summary Grid -->
                <div class="flex justify-end pt-2">
                    <div class="w-64 space-y-1.5 text-xs">
                        <div class="flex justify-between text-zinc-600">
                            <span>Subtotal:</span>
                            <strong id="prev-sheet-subtotal">$0.00</strong>
                        </div>
                        <div class="flex justify-between text-zinc-600">
                            <span>Tax Amount:</span>
                            <strong id="prev-sheet-tax">$0.00</strong>
                        </div>
                        <div class="flex justify-between text-zinc-600">
                            <span>Shipping / Other:</span>
                            <strong id="prev-sheet-shipping">$0.00</strong>
                        </div>
                        <div class="flex justify-between text-sm font-black text-zinc-950 pt-2 border-t border-zinc-200">
                            <span>Grand Total:</span>
                            <span id="prev-sheet-grandtotal" class="text-indigo-600">$0.00</span>
                        </div>
                        <div class="flex justify-between text-zinc-600">
                            <span>Amount Paid:</span>
                            <strong id="prev-sheet-paid" class="text-emerald-600">$0.00</strong>
                        </div>
                        <div class="flex justify-between text-xs font-bold text-red-600 pt-1 border-t border-dashed border-zinc-200">
                            <span>Balance Due:</span>
                            <span id="prev-sheet-balance">$0.00</span>
                        </div>
                    </div>
                </div>

                <!-- Notes & Bank Wire -->
                <div class="border-t border-zinc-200 pt-4 text-xs text-zinc-600 space-y-2">
                    <div id="prev-sheet-notes" class="whitespace-pre-line italic"></div>
                    <div class="p-3 bg-zinc-50 rounded-lg border border-zinc-200 font-mono text-[11px] text-zinc-700">
                        <strong>Bank Wire Instructions:</strong> Silicon Valley Corporate Bank | Routing: 121000358 | Account: 9845-2018-4491 | SWIFT: SVBKUS6S
                    </div>
                </div>

                <!-- Signatory -->
                <div class="flex items-center justify-between pt-6 border-t border-zinc-200 text-xs">
                    <div>
                        <span class="text-zinc-400 block text-[10px] uppercase">Generated By:</span>
                        <strong id="prev-sheet-creator">System Admin</strong>
                    </div>
                    <div class="text-right">
                        <div class="w-36 border-b border-zinc-400 mb-1"></div>
                        <span class="text-zinc-400 text-[10px] uppercase">Authorized Signatory</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL 3: COLUMN CUSTOMIZATION MODAL        -->
<!-- ========================================== -->
<div id="document-config-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50 backdrop-blur-xs p-4">
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xl max-w-md w-full p-6 space-y-4">
        <div class="flex items-center justify-between pb-2 border-b border-zinc-200 dark:border-zinc-800">
            <h3 class="text-sm font-bold text-zinc-950 dark:text-white">Customize Visible Columns</h3>
            <button type="button" onclick="window.closeDocumentConfigModal()" class="text-zinc-400 hover:text-zinc-600">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
        <div id="document-config-checkboxes" class="space-y-2 max-h-72 overflow-y-auto">
            <!-- Checkboxes injected via JS -->
        </div>
        <div class="pt-3 border-t border-zinc-200 dark:border-zinc-800 flex justify-end gap-2">
            <button type="button" onclick="window.closeDocumentConfigModal()" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-zinc-600 hover:bg-zinc-100">Cancel</button>
            <button type="button" onclick="window.saveDocumentConfig()" class="px-3.5 py-1.5 rounded-lg text-xs font-bold bg-indigo-600 text-white">Save Columns</button>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL 4: FILTER MODAL                      -->
<!-- ========================================== -->
<div id="document-filter-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50 backdrop-blur-xs p-4">
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xl max-w-md w-full p-6 space-y-4">
        <div class="flex items-center justify-between pb-2 border-b border-zinc-200 dark:border-zinc-800">
            <h3 class="text-sm font-bold text-zinc-950 dark:text-white">Filter Documents</h3>
            <button type="button" onclick="window.closeDocumentFilterModal()" class="text-zinc-400 hover:text-zinc-600">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
        <div class="space-y-3">
            <div>
                <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Status</label>
                <select id="filter-doc-status" class="w-full text-xs rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-zinc-900 dark:text-zinc-100">
                    <option value="">All Statuses</option>
                    <option value="draft">Draft</option>
                    <option value="pending_approval">Pending Approval</option>
                    <option value="approved">Approved</option>
                    <option value="issued">Issued</option>
                    <option value="sent">Sent</option>
                    <option value="partially_paid">Partially Paid</option>
                    <option value="paid">Paid</option>
                    <option value="overdue">Overdue</option>
                    <option value="void">Void</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Account</label>
                <select id="filter-doc-account" class="w-full text-xs rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-zinc-900 dark:text-zinc-100">
                    <option value="">All Accounts</option>
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="pt-3 border-t border-zinc-200 dark:border-zinc-800 flex justify-between items-center">
            <button type="button" onclick="window.resetDocumentFilters()" class="text-xs text-red-600 font-semibold hover:underline">Reset Filters</button>
            <div class="flex gap-2">
                <button type="button" onclick="window.closeDocumentFilterModal()" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-zinc-600 hover:bg-zinc-100">Cancel</button>
                <button type="button" onclick="window.applyDocumentFilters()" class="px-3.5 py-1.5 rounded-lg text-xs font-bold bg-indigo-600 text-white">Apply</button>
            </div>
        </div>
    </div>
</div>

<!-- Safe Blade JSON Hydration with Zero Bracket/Parentheses Nesting Issues -->
@php
    $documentsData = [
        'initialDocuments' => $initialDocuments ?? [],
        'kpis' => $kpis ?? [],
        'governance' => $governance ?? [],
        'templates' => $templates ?? [],
        'accounts' => $accounts ?? [],
        'contacts' => $contacts ?? [],
    ];
@endphp
<script>
    window.__DOCUMENTS_DATA__ = {!! json_encode($documentsData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};
</script>
<script src="{{ asset('js/documents.js') }}?v=1.0"></script>
@endsection
