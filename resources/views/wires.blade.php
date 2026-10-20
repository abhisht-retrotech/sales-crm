@extends('layouts.app')

@section('title', 'Wire Received Workbench — DealSync Enterprise CRM')

@section('content')
<!-- Centered Max-Width Container matching React page-wrapper (max-width: 1240px, padding: 2.25rem 2.5rem) -->
<div id="wires-page-root" class="max-w-[1240px] mx-auto w-full px-6 sm:px-10 py-7 space-y-6 animate-fade-in">
    <!-- Top Action Header -->
    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4 pb-2 border-b border-zinc-200 dark:border-zinc-800">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800">
                    <i data-lucide="landmark" class="w-3.5 h-3.5"></i>
                    Banking Intake &amp; EFT Reconciliation
                </span>
                <span class="text-xs text-zinc-500 dark:text-zinc-400">
                    Direct Bank Feed Matching to Invoices &amp; Accounts
                </span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-950 dark:text-zinc-100">
                Wire Received Workbench
            </h1>
            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 max-w-2xl">
                Capture real-time banking wire references (UTR), remitter institutions, and match incoming funds directly to open Accounts Receivable invoices.
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap shrink-0">
            <!-- Export CSV Button -->
            <button
                type="button"
                onclick="window.exportWiresCsv()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs cursor-pointer"
            >
                <i data-lucide="download" class="w-3.5 h-3.5 text-zinc-500"></i>
                <span>Export CSV</span>
            </button>

            <!-- Invoices Ledger Quick Link -->
            <a
                href="/payments"
                class="spa-nav-link inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs no-underline hover:no-underline"
            >
                <i data-lucide="credit-card" class="w-3.5 h-3.5 text-indigo-600"></i>
                <span>Invoices &amp; AR/AP</span>
            </a>

            <!-- Log Incoming Wire Action -->
            <button
                type="button"
                onclick="window.openWireIntakeModal('create')"
                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold shadow-sm transition-colors cursor-pointer"
            >
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>+ Log Wire Intake</span>
            </button>
        </div>
    </div>

    <!-- Top KPI Summary Cards (4 Cards) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Total Wires Received -->
        <div class="bg-white dark:bg-zinc-900 rounded-2xl p-5 border border-zinc-200 dark:border-zinc-800 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">TOTAL WIRES RECEIVED</span>
                <div class="p-2 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400">
                    <i data-lucide="dollar-sign" class="w-4 h-4"></i>
                </div>
            </div>
            <div id="kpi-total-wires" class="text-2xl font-black text-zinc-950 dark:text-white tracking-tight">
                {{ $kpis['totalWiresReceivedFormatted'] ?? '$0.00' }}
            </div>
            <span class="text-[11px] text-zinc-400 mt-1"><span id="kpi-wires-count">{{ $kpis['totalCount'] ?? 0 }}</span> Incoming UTR Transits</span>
        </div>

        <!-- Card 2: Reconciled Funds -->
        <div class="bg-white dark:bg-zinc-900 rounded-2xl p-5 border border-zinc-200 dark:border-zinc-800 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">RECONCILED FUNDS</span>
                <div class="p-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400">
                    <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                </div>
            </div>
            <div id="kpi-reconciled-funds" class="text-2xl font-black text-emerald-600 dark:text-emerald-400 tracking-tight">
                {{ $kpis['reconciledFundsFormatted'] ?? '$0.00' }}
            </div>
            <span class="text-[11px] text-emerald-600/80 font-semibold mt-1">Matched to Open Invoices</span>
        </div>

        <!-- Card 3: Pending Verification -->
        <div class="bg-white dark:bg-zinc-900 rounded-2xl p-5 border border-zinc-200 dark:border-zinc-800 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">PENDING VERIFICATION</span>
                <div class="p-2 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400">
                    <i data-lucide="clock" class="w-4 h-4"></i>
                </div>
            </div>
            <div id="kpi-pending-funds" class="text-2xl font-black text-amber-600 dark:text-amber-400 tracking-tight">
                {{ $kpis['pendingVerificationFormatted'] ?? '$0.00' }}
            </div>
            <span class="text-[11px] text-zinc-400 mt-1">Unallocated Remittances</span>
        </div>

        <!-- Card 4: Audit Discrepancy -->
        <div class="bg-white dark:bg-zinc-900 rounded-2xl p-5 border border-zinc-200 dark:border-zinc-800 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">AUDIT DISCREPANCY</span>
                <div class="p-2 rounded-xl bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400">
                    <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                </div>
            </div>
            <div id="kpi-discrepancy-funds" class="text-2xl font-black text-zinc-950 dark:text-white tracking-tight">
                {{ $kpis['discrepancyFundsFormatted'] ?? '$0.00' }}
            </div>
            <span class="text-[11px] text-zinc-400 mt-1">Ledger Variance Flagged</span>
        </div>
    </div>

    <!-- Controls Toolbar (Search, Status Pills, View Switcher, Customize, Filter) -->
    <div class="bg-white dark:bg-zinc-900 rounded-2xl p-4 border border-zinc-200 dark:border-zinc-800 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-3">
        <!-- Search Input -->
        <div class="relative flex-1 max-w-md">
            <i data-lucide="search" class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
            <input
                type="text"
                id="wire-search-input"
                placeholder="Search wires by UTR reference, remitter, bank..."
                class="w-full text-xs pl-9 pr-4 py-2 rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-amber-500 transition-all"
                oninput="window.handleWireSearch(this.value)"
            >
        </div>

        <!-- Controls: Status Pills, View Switcher, Customize, Filter -->
        <div class="flex items-center gap-2 flex-wrap">
            <!-- Status Segmented Filter Control -->
            <div class="inline-flex items-center p-0.5 rounded-lg bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700">
                <button
                    type="button"
                    id="wire-status-tab-all"
                    onclick="window.switchWireStatusTab('')"
                    class="px-2.5 py-1 text-xs font-semibold rounded-md transition-all cursor-pointer bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white shadow-2xs"
                >
                    All Wires (<span id="count-status-all">0</span>)
                </button>
                <button
                    type="button"
                    id="wire-status-tab-matched"
                    onclick="window.switchWireStatusTab('Matched/Reconciled')"
                    class="px-2.5 py-1 text-xs font-medium rounded-md transition-all cursor-pointer text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white"
                >
                    Matched (<span id="count-status-matched">0</span>)
                </button>
                <button
                    type="button"
                    id="wire-status-tab-pending"
                    onclick="window.switchWireStatusTab('Pending Verification')"
                    class="px-2.5 py-1 text-xs font-medium rounded-md transition-all cursor-pointer text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white"
                >
                    Pending (<span id="count-status-pending">0</span>)
                </button>
                <button
                    type="button"
                    id="wire-status-tab-discrepancy"
                    onclick="window.switchWireStatusTab('Discrepancy')"
                    class="px-2.5 py-1 text-xs font-medium rounded-md transition-all cursor-pointer text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white"
                >
                    Discrepancy (<span id="count-status-discrepancy">0</span>)
                </button>
            </div>

            <!-- View Switcher (Table vs Kanban) -->
            <div class="inline-flex items-center p-0.5 rounded-lg bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700">
                <button
                    type="button"
                    id="wire-view-btn-table"
                    onclick="window.switchWireView('table')"
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-md transition-all cursor-pointer bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white shadow-2xs"
                >
                    <i data-lucide="table" class="w-3.5 h-3.5"></i>
                    <span>Table</span>
                </button>
                <button
                    type="button"
                    id="wire-view-btn-kanban"
                    onclick="window.switchWireView('kanban')"
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-md transition-all cursor-pointer text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white"
                >
                    <i data-lucide="kanban" class="w-3.5 h-3.5"></i>
                    <span>Kanban</span>
                </button>
            </div>

            <!-- Customize Visible Columns -->
            <button
                type="button"
                onclick="window.openWireConfigModal()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs cursor-pointer"
                title="Customize Visible Fields"
            >
                <i data-lucide="sliders" class="w-3.5 h-3.5 text-zinc-500"></i>
                <span>Customize</span>
            </button>

            <!-- Filter Button -->
            <button
                type="button"
                onclick="window.openWireFilterModal()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs cursor-pointer"
                title="Filter Bank Wires"
            >
                <i data-lucide="filter" class="w-3.5 h-3.5 text-zinc-500"></i>
                <span>Filter</span>
                <span id="wire-filter-badge" class="hidden px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-amber-600 text-white">0</span>
            </button>
        </div>
    </div>

    <!-- Active Filter Chips -->
    <div id="wire-active-filter-chips" class="hidden flex items-center gap-2 flex-wrap">
        <!-- Injected via JavaScript -->
    </div>

    <!-- VIEW 1: COMPACT TABLE VIEW -->
    <div id="wire-container-table" class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-zinc-700 dark:text-zinc-300">
                <thead class="bg-zinc-50 dark:bg-zinc-800/60 text-[11px] font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider border-b border-zinc-200 dark:border-zinc-800">
                    <tr id="wire-table-header-row">
                        <!-- Dynamic headers injected via JS -->
                    </tr>
                </thead>
                <tbody id="wire-table-tbody" class="divide-y divide-zinc-200 dark:divide-zinc-800">
                    <!-- Dynamic rows injected via JS -->
                </tbody>
            </table>
        </div>

        <!-- Table Empty State -->
        <div id="wire-empty-state-table" class="hidden py-16 text-center space-y-3">
            <div class="w-12 h-12 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-400 flex items-center justify-center mx-auto">
                <i data-lucide="landmark" class="w-6 h-6"></i>
            </div>
            <div class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">No bank wires found</div>
            <p class="text-xs text-zinc-500 max-w-sm mx-auto">
                No incoming wire records logged matching search or filter criteria. Click "+ Log Wire Intake" to record bank transfers.
            </p>
        </div>
    </div>

    <!-- VIEW 2: UNIVERSAL KANBAN VIEW -->
    <div id="wire-container-kanban" class="hidden">
        <div id="wire-kanban-board" class="grid grid-cols-1 md:grid-cols-3 gap-4 overflow-x-auto pb-4">
            <!-- Kanban Columns injected via JS -->
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL 1: RECONCILE BANK WIRE WORKBENCH     -->
<!-- ========================================== -->
<div id="reconcile-wire-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50 backdrop-blur-xs p-4">
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xl max-w-lg w-full flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-150">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 flex items-center justify-center">
                    <i data-lucide="landmark" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-zinc-950 dark:text-white">Reconcile Bank Wire</h3>
                    <span id="reconcile-modal-utr" class="text-xs font-mono font-bold text-amber-600">UTR0000000000</span>
                </div>
            </div>
            <button type="button" onclick="window.closeReconcileWireModal()" class="p-1 rounded-lg text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 cursor-pointer">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form id="reconcile-wire-form" onsubmit="window.saveReconcileWire(event)" class="p-6 space-y-4">
            <!-- Remitter, Bank, & Amount Details Box -->
            <div class="bg-zinc-50 dark:bg-zinc-800/50 p-4 rounded-xl border border-zinc-200 dark:border-zinc-700 space-y-1.5 text-xs">
                <div class="flex items-center justify-between">
                    <span class="text-zinc-500">Remitter Name:</span>
                    <strong id="reconcile-box-remitter" class="text-zinc-900 dark:text-zinc-100 font-bold">Skyline Cloud Systems LLC</strong>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-zinc-500">Sending Bank:</span>
                    <span id="reconcile-box-bank" class="text-zinc-700 dark:text-zinc-300 font-medium">JPMorgan Chase NY</span>
                </div>
                <div class="flex items-center justify-between pt-1 border-t border-zinc-200 dark:border-zinc-700">
                    <span class="text-zinc-500 font-bold">Transfer Amount:</span>
                    <span id="reconcile-box-amount" class="text-emerald-600 dark:text-emerald-400 font-black text-sm">$0.00</span>
                </div>
            </div>

            <!-- Select Matching Account -->
            <div>
                <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Select Matching Account (Company) *</label>
                <select
                    id="reconcile-select-account"
                    required
                    onchange="window.handleReconcileAccountChange(this.value)"
                    class="w-full text-xs rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-amber-500"
                >
                    <option value="">-- Choose Account --</option>
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->name }} ({{ $acc->type ?? 'Organization' }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Apply to Outstanding Invoice (Optional) -->
            <div>
                <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Apply to Outstanding Invoice (Optional)</label>
                <select
                    id="reconcile-select-payment"
                    class="w-full text-xs rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-amber-500"
                >
                    <option value="">-- Unallocated Deposit (Or Choose Open Invoice) --</option>
                    @foreach($unpaidPayments as $p)
                        <option value="{{ $p->id }}" data-account="{{ $p->account_id }}" class="invoice-option">
                            {{ $p->invoice_number }} (Bal: ${{ number_format((float)$p->amount - (float)$p->paid_amount, 2) }})
                        </option>
                    @endforeach
                </select>
                <p class="text-[11px] text-zinc-400 mt-1">If selected, the wire amount will be credited to this invoice automatically.</p>
            </div>

            <!-- Auditor Notes -->
            <div>
                <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Auditor / Reconciliation Notes</label>
                <input
                    type="text"
                    id="reconcile-input-notes"
                    placeholder="e.g. Bank credit matched by Treasury Controller"
                    class="w-full text-xs rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-amber-500"
                >
            </div>

            <div class="pt-3 border-t border-zinc-200 dark:border-zinc-800 flex justify-end gap-2">
                <button type="button" onclick="window.closeReconcileWireModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer">
                    Cancel
                </button>
                <button type="submit" id="btn-submit-reconcile-wire" class="px-4 py-2 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs transition-colors cursor-pointer flex items-center gap-1.5">
                    <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                    <span>Confirm Reconciliation</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL 2: WIRE INTAKE MODAL (CREATE / EDIT) -->
<!-- ========================================== -->
<div id="wire-intake-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50 backdrop-blur-xs p-4">
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xl max-w-xl w-full flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-150">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between shrink-0">
            <div>
                <h3 id="wire-intake-modal-title" class="text-base font-bold text-zinc-950 dark:text-white">Log Bank Wire Intake</h3>
                <p class="text-xs text-zinc-500">Record an incoming bank EFT, wire transfer, or treasury credit.</p>
            </div>
            <button type="button" onclick="window.closeWireIntakeModal()" class="p-1 rounded-lg text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 cursor-pointer">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form id="wire-intake-form" onsubmit="window.saveWireIntake(event)" class="p-6 space-y-4">
            <input type="hidden" id="wire-intake-id" value="">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">UTR Reference Number</label>
                    <input type="text" id="wire-input-utr" placeholder="Auto-generated if empty" class="w-full text-xs font-mono rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-amber-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Wire Amount ($ USD) *</label>
                    <input type="number" step="any" min="0.01" id="wire-input-amount" required placeholder="0.00" class="w-full text-xs font-bold rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-amber-500">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Ordering Party / Remitter Name *</label>
                    <input type="text" id="wire-input-remitter" required placeholder="e.g. Skyline Cloud Systems LLC" class="w-full text-xs rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-amber-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Sending Financial Institution *</label>
                    <input type="text" id="wire-input-sending-bank" required placeholder="e.g. JPMorgan Chase NY" class="w-full text-xs rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-amber-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Receiving Deposit Account</label>
                    <input type="text" id="wire-input-receiving-bank" value="CITI-US-MAIN-9482" class="w-full text-xs rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-amber-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Credit / Intake Date *</label>
                    <input type="date" id="wire-input-date" value="{{ date('Y-m-d') }}" required class="w-full text-xs rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-amber-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Reconciliation Status *</label>
                    <select id="wire-input-status" required class="w-full text-xs rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-amber-500">
                        @foreach($stages as $st)
                            <option value="{{ $st }}">{{ $st }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Matched Customer / Vendor (Optional)</label>
                    <select id="wire-input-account" class="w-full text-xs rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-amber-500">
                        <option value="">-- Unallocated / Unmatched --</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->name }} ({{ $acc->type ?? 'Organization' }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Auditor / Reconciliation Notes</label>
                    <textarea id="wire-input-notes" rows="2" placeholder="Bank advice notes, remittance remarks, or currency info..." class="w-full text-xs rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-amber-500"></textarea>
                </div>
            </div>

            <div class="pt-4 border-t border-zinc-200 dark:border-zinc-800 flex justify-end gap-2">
                <button type="button" onclick="window.closeWireIntakeModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer">
                    Cancel
                </button>
                <button type="submit" id="btn-save-wire-intake" class="px-4 py-2 rounded-xl text-xs font-bold bg-amber-600 hover:bg-amber-700 text-white shadow-xs transition-colors cursor-pointer flex items-center gap-1.5">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Save Wire Record</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL 3: CUSTOMIZE VISIBLE COLUMNS MODAL   -->
<!-- ========================================== -->
<div id="wire-config-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50 backdrop-blur-xs p-4">
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xl max-w-md w-full p-6 space-y-4">
        <div class="flex items-center justify-between pb-2 border-b border-zinc-200 dark:border-zinc-800">
            <h3 class="text-sm font-bold text-zinc-950 dark:text-white">Customize Visible Fields</h3>
            <button type="button" onclick="window.closeWireConfigModal()" class="text-zinc-400 hover:text-zinc-600">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
        <div id="wire-config-checkboxes" class="space-y-2 max-h-72 overflow-y-auto">
            <!-- Checkboxes injected via JS -->
        </div>
        <div class="pt-3 border-t border-zinc-200 dark:border-zinc-800 flex justify-end gap-2">
            <button type="button" onclick="window.closeWireConfigModal()" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-zinc-600 hover:bg-zinc-100">Cancel</button>
            <button type="button" onclick="window.saveWireConfig()" class="px-3.5 py-1.5 rounded-lg text-xs font-bold bg-amber-600 text-white">Save Columns</button>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL 4: FILTER MODAL                      -->
<!-- ========================================== -->
<div id="wire-filter-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50 backdrop-blur-xs p-4">
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xl max-w-md w-full p-6 space-y-4">
        <div class="flex items-center justify-between pb-2 border-b border-zinc-200 dark:border-zinc-800">
            <h3 class="text-sm font-bold text-zinc-950 dark:text-white">Filter Bank Wires</h3>
            <button type="button" onclick="window.closeWireFilterModal()" class="text-zinc-400 hover:text-zinc-600">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
        <div class="space-y-3">
            <div>
                <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Reconciliation Status</label>
                <select id="filter-wire-status" class="w-full text-xs rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-zinc-900 dark:text-zinc-100">
                    <option value="">All Statuses</option>
                    @foreach($stages as $st)
                        <option value="{{ $st }}">{{ $st }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Matched Account</label>
                <select id="filter-wire-account" class="w-full text-xs rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-zinc-900 dark:text-zinc-100">
                    <option value="">All Accounts</option>
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="pt-3 border-t border-zinc-200 dark:border-zinc-800 flex justify-between items-center">
            <button type="button" onclick="window.resetWireFilters()" class="text-xs text-rose-600 font-semibold hover:underline cursor-pointer">Reset Filters</button>
            <div class="flex gap-2">
                <button type="button" onclick="window.closeWireFilterModal()" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-zinc-600 hover:bg-zinc-100">Cancel</button>
                <button type="button" onclick="window.applyWireFilters()" class="px-3.5 py-1.5 rounded-lg text-xs font-bold bg-amber-600 text-white cursor-pointer">Apply</button>
            </div>
        </div>
    </div>
</div>

<!-- Safe Blade JSON Hydration with Zero Bracket/Parentheses Nesting Issues -->
@php
    $wiresData = [
        'initialWires' => $initialWires ?? [],
        'kpis' => $kpis ?? [],
        'accounts' => $accounts ?? [],
        'unpaidPayments' => $unpaidPayments ?? [],
        'stages' => $stages ?? ['Pending Verification', 'Matched/Reconciled', 'Discrepancy'],
        'fieldPermissions' => $fieldPermissions ?? (object)[],
    ];
@endphp
<script>
    window.__WIRES_DATA__ = {!! json_encode($wiresData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};
</script>
<script src="{{ asset('js/wires.js') }}?v=1.0"></script>
@endsection
