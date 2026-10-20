@extends('layouts.app')

@section('title', 'Shipping & Logistics Freight — DealSync Enterprise CRM')

@section('content')
<!-- Centered Max-Width Container matching React page-wrapper (max-width: 1240px, padding: 2.25rem 2.5rem) -->
<div id="shipping-page-root" class="max-w-[1240px] mx-auto w-full px-6 sm:px-10 py-7 space-y-6 animate-fade-in">
    <!-- Top Action Header -->
    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4 pb-2 border-b border-zinc-200 dark:border-zinc-800">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800">
                    Bi-Directional Freight Management
                </span>
                <span class="text-xs text-zinc-500 dark:text-zinc-400">
                    Outbound Customer Orders &amp; Inbound Supplier Deliveries
                </span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-950 dark:text-zinc-100">
                Shipping &amp; Logistics Freight
            </h1>
            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 max-w-2xl">
                Track carrier manifests, dispatch routes, port customs clearances, and multi-leg courier delivery milestones. Synced dynamically with custom fields and field-level security permissions.
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap shrink-0">
            <!-- View Switcher (Manifest Cards vs Kanban vs Table) -->
            <div class="inline-flex items-center p-0.5 rounded-lg bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700">
                <button
                    type="button"
                    id="btn-view-cards"
                    onclick="window.switchShippingView('cards')"
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-md transition-all cursor-pointer bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white shadow-2xs"
                >
                    <i data-lucide="layout-grid" class="w-3.5 h-3.5"></i>
                    <span>Manifests</span>
                </button>
                <button
                    type="button"
                    id="btn-view-kanban"
                    onclick="window.switchShippingView('kanban')"
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-md transition-all cursor-pointer text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white"
                >
                    <i data-lucide="kanban" class="w-3.5 h-3.5"></i>
                    <span>Kanban</span>
                </button>
                <button
                    type="button"
                    id="btn-view-table"
                    onclick="window.switchShippingView('table')"
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-md transition-all cursor-pointer text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white"
                >
                    <i data-lucide="table" class="w-3.5 h-3.5"></i>
                    <span>Table</span>
                </button>
            </div>

            <!-- Customize Visible Fields -->
            <button
                type="button"
                onclick="window.openShippingConfigModal()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs cursor-pointer"
                title="Customize Visible Fields"
            >
                <i data-lucide="sliders" class="w-3.5 h-3.5 text-zinc-500"></i>
                <span>Customize</span>
            </button>

            <!-- Filter Button -->
            <button
                type="button"
                onclick="window.openShippingFilterModal()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs cursor-pointer"
                title="Filter Freight Shipments"
            >
                <i data-lucide="filter" class="w-3.5 h-3.5 text-zinc-500"></i>
                <span>Filter</span>
                <span id="ship-filter-badge" class="hidden px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-amber-600 text-white">0</span>
            </button>

            <!-- Export CSV Button -->
            <button
                type="button"
                onclick="window.exportShippingCsv()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs cursor-pointer"
            >
                <i data-lucide="download" class="w-3.5 h-3.5 text-zinc-500"></i>
                <span>Export CSV</span>
            </button>

            <!-- New Shipment Primary Action -->
            <button
                type="button"
                onclick="window.openShipmentModal('create')"
                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm transition-colors cursor-pointer"
            >
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>+ New Shipment</span>
            </button>
        </div>
    </div>

    <!-- Top KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 border border-zinc-200 dark:border-zinc-800 shadow-xs">
            <div class="text-[11px] font-semibold text-zinc-500 uppercase tracking-wider">IN-TRANSIT FREIGHT</div>
            <div id="kpi-in-transit-count" class="text-2xl font-extrabold text-blue-600 dark:text-blue-400 mt-1 tracking-tight">
                0 En Route
            </div>
            <div class="flex items-center gap-1 text-[11px] text-zinc-500 mt-1">
                <span>Active Carrier Transit</span>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 border border-zinc-200 dark:border-zinc-800 shadow-xs">
            <div class="text-[11px] font-semibold text-zinc-500 uppercase tracking-wider">CUSTOMS CLEARANCE</div>
            <div id="kpi-customs-count" class="text-2xl font-extrabold text-amber-600 dark:text-amber-400 mt-1 tracking-tight">
                0 Manifests
            </div>
            <div class="flex items-center gap-1 text-[11px] text-zinc-500 mt-1">
                <span>Port Entry &amp; Customs Inspection</span>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 border border-zinc-200 dark:border-zinc-800 shadow-xs">
            <div class="text-[11px] font-semibold text-zinc-500 uppercase tracking-wider">DELIVERED &amp; RECEIVED</div>
            <div id="kpi-delivered-count" class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-1 tracking-tight">
                0 Delivered
            </div>
            <div class="flex items-center gap-1 text-[11px] text-emerald-600 dark:text-emerald-400 font-semibold mt-1">
                <i data-lucide="check-circle-2" class="w-3 h-3"></i>
                <span>Signed Proof Verified</span>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 border border-zinc-200 dark:border-zinc-800 shadow-xs">
            <div class="text-[11px] font-semibold text-zinc-500 uppercase tracking-wider">CARRIER ON-TIME SLA</div>
            <div id="kpi-carrier-sla" class="text-2xl font-extrabold text-indigo-600 dark:text-indigo-400 mt-1 tracking-tight">
                98.4%
            </div>
            <div class="text-[11px] text-zinc-500 mt-1">
                FedEx, DHL, Maersk &amp; UPS
            </div>
        </div>
    </div>

    <!-- Quick Direction & Status Filter Pills -->
    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs">
        <span class="text-zinc-500 font-medium shrink-0 mr-1">Filter:</span>
        <button
            type="button"
            data-filter-type="direction"
            data-filter-val="all"
            onclick="window.filterByDirection('all')"
            class="ship-filter-pill px-3 py-1 rounded-full font-semibold transition-all cursor-pointer bg-zinc-900 dark:bg-zinc-100 text-white dark:text-zinc-900 shadow-xs"
        >
            All Manifests (<span id="count-all">0</span>)
        </button>
        <button
            type="button"
            data-filter-type="direction"
            data-filter-val="Outbound"
            onclick="window.filterByDirection('Outbound')"
            class="ship-filter-pill px-3 py-1 rounded-full font-medium transition-all cursor-pointer bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700"
        >
            Outbound Deliveries (<span id="count-outbound">0</span>)
        </button>
        <button
            type="button"
            data-filter-type="direction"
            data-filter-val="Inbound"
            onclick="window.filterByDirection('Inbound')"
            class="ship-filter-pill px-3 py-1 rounded-full font-medium transition-all cursor-pointer bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700"
        >
            Inbound Freight (<span id="count-inbound">0</span>)
        </button>

        <span class="text-zinc-300 dark:text-zinc-700 mx-1">|</span>

        @foreach($stages ?? [] as $st)
            <button
                type="button"
                data-filter-type="status"
                data-filter-val="{{ $st }}"
                onclick="window.filterByStatus('{{ $st }}')"
                class="ship-status-pill px-3 py-1 rounded-full font-medium transition-all cursor-pointer bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700"
            >
                {{ $st }} (<span id="count-status-{{ Str::slug($st) }}">0</span>)
            </button>
        @endforeach
    </div>

    <!-- Toolbar: Search and Filter Summary -->
    <div class="bg-white dark:bg-zinc-900 rounded-xl p-3 border border-zinc-200 dark:border-zinc-800 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-3">
        <div class="relative w-full sm:max-w-md">
            <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400"></i>
            <input
                type="text"
                id="shipping-search-input"
                placeholder="Search shipments by tracking code, carrier, destination..."
                oninput="window.handleShippingSearchInput(this.value)"
                class="w-full pl-9 pr-4 py-1.5 text-xs rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-800/50 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 placeholder-zinc-400"
            >
        </div>

        <div class="text-xs text-zinc-500 dark:text-zinc-400 flex items-center gap-3 w-full sm:w-auto justify-between sm:justify-end">
            <span id="shipping-summary-text">Showing 0 of 0 shipments</span>
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
            onclick="window.clearAllShippingFilters()"
            class="ml-auto text-xs font-semibold text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 cursor-pointer"
        >
            Clear All
        </button>
    </div>

    <!-- VIEW 1: MANIFESTS / PROGRESS CARDS VIEW -->
    <div id="view-cards-container" class="space-y-4">
        <div id="shipping-cards-grid" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Progress Cards Generated dynamically via JS -->
        </div>

        <!-- Empty State for Cards -->
        <div id="shipping-empty-state-cards" class="hidden py-16 px-4 text-center space-y-3 bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800">
            <div class="w-12 h-12 mx-auto rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-400">
                <i data-lucide="truck" class="w-6 h-6"></i>
            </div>
            <h3 class="text-sm font-bold text-zinc-950 dark:text-zinc-100">No Freight Shipments Found</h3>
            <p class="text-xs text-zinc-500 max-w-sm mx-auto">
                No carrier manifests match your active direction filter, search term, or security criteria.
            </p>
            <button
                type="button"
                onclick="window.openShipmentModal('create')"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold transition-colors cursor-pointer"
            >
                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                <span>Create New Shipment</span>
            </button>
        </div>
    </div>

    <!-- VIEW 2: KANBAN BOARD VIEW -->
    <div id="view-kanban-container" class="hidden space-y-4">
        <div id="shipping-kanban-board" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-3.5 items-start overflow-x-auto pb-4">
            <!-- Kanban Columns Generated dynamically via JS -->
        </div>
    </div>

    <!-- VIEW 3: COMPACT TABLE VIEW -->
    <div id="view-table-container" class="hidden space-y-4">
        <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr id="shipping-table-header-row" class="border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/75 dark:bg-zinc-800/50 text-zinc-600 dark:text-zinc-400 font-semibold">
                            <!-- Dynamic Columns Generated via JS -->
                        </tr>
                    </thead>
                    <tbody id="shipping-table-tbody" class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        <!-- Dynamic Rows Generated via JS -->
                    </tbody>
                </table>
            </div>

            <div id="shipping-empty-state-table" class="hidden py-16 px-4 text-center space-y-3">
                <div class="w-12 h-12 mx-auto rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-400">
                    <i data-lucide="truck" class="w-6 h-6"></i>
                </div>
                <h3 class="text-sm font-bold text-zinc-950 dark:text-zinc-100">No Shipments Found</h3>
                <p class="text-xs text-zinc-500 max-w-sm mx-auto">
                    No freight shipments match the current table filter.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODALS                                                                    -->
<!-- ========================================================================= -->

<!-- MODAL 1: NEW SHIPMENT / CREATE & EDIT MODAL -->
<div id="shipment-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-xs animate-fade-in overflow-y-auto">
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xl w-full max-w-2xl my-8 overflow-hidden">
        <!-- Modal Header -->
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-blue-50 dark:bg-blue-950/50 flex items-center justify-center text-blue-600 dark:text-blue-400">
                    <i data-lucide="truck" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 id="modal-shipment-heading" class="text-base font-bold text-zinc-950 dark:text-zinc-100">
                        New Freight Shipment Manifest
                    </h3>
                    <p class="text-xs text-zinc-500">
                        Courier dispatch route, cargo tracking, and order link
                    </p>
                </div>
            </div>
            <button type="button" onclick="window.closeShipmentModal()" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 cursor-pointer">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Modal Body Form -->
        <form id="shipment-form" onsubmit="window.handleShipmentSubmit(event)" class="p-6 space-y-5 max-h-[75vh] overflow-y-auto">
            <input type="hidden" id="shipment-id" value="">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Carrier -->
                <div>
                    <label class="block text-xs font-semibold text-zinc-800 dark:text-zinc-200 mb-1">
                        Freight Carrier <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="field-carrier"
                        required
                        class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        @foreach($carriers ?? [] as $cr)
                            <option value="{{ $cr }}">{{ $cr }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Tracking Number -->
                <div>
                    <label class="block text-xs font-semibold text-zinc-800 dark:text-zinc-200 mb-1">
                        Tracking Code <span class="text-zinc-400 font-normal">(Auto-generated if empty)</span>
                    </label>
                    <input
                        type="text"
                        id="field-tracking_number"
                        placeholder="e.g. TRK-88219-US"
                        class="w-full px-3 py-2 text-xs font-mono rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                </div>

                <!-- Direction Type -->
                <div>
                    <label class="block text-xs font-semibold text-zinc-800 dark:text-zinc-200 mb-1">
                        Logistics Direction <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="field-type"
                        required
                        class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        <option value="Outbound">Outbound (Customer Delivery)</option>
                        <option value="Inbound">Inbound (Supplier PO Delivery)</option>
                        <option value="Drop-ship">Drop-ship (Direct Facility Transfer)</option>
                    </select>
                </div>

                <!-- Shipment Status -->
                <div>
                    <label class="block text-xs font-semibold text-zinc-800 dark:text-zinc-200 mb-1">
                        Shipment Status <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="field-status"
                        required
                        class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        @foreach($stages ?? [] as $st)
                            <option value="{{ $st }}">{{ $st }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Linked Account -->
                <div>
                    <label class="block text-xs font-semibold text-zinc-800 dark:text-zinc-200 mb-1">
                        Linked Account <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="field-account_id"
                        required
                        class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        <option value="">Select Linked Account...</option>
                        @foreach($accounts ?? [] as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->name }} ({{ $acc->type ?? 'Account' }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Linked Sales Order (Opportunity) -->
                <div>
                    <label class="block text-xs font-semibold text-zinc-800 dark:text-zinc-200 mb-1">
                        Linked Sales Order <span class="text-zinc-400 font-normal">(Outbound)</span>
                    </label>
                    <select
                        id="field-opportunity_id"
                        class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        <option value="">None (Inbound or Independent)</option>
                        @foreach($opportunities ?? [] as $opp)
                            <option value="{{ $opp->id }}">{{ $opp->name }} (${{ number_format($opp->amount ?? 0, 0) }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Linked Purchase Deal (PO) -->
                <div>
                    <label class="block text-xs font-semibold text-zinc-800 dark:text-zinc-200 mb-1">
                        Linked Purchase PO <span class="text-zinc-400 font-normal">(Inbound)</span>
                    </label>
                    <select
                        id="field-purchase_deal_id"
                        class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        <option value="">None (Outbound or Independent)</option>
                        @foreach($purchaseDeals ?? [] as $pd)
                            <option value="{{ $pd->id }}">{{ $pd->po_number }} — {{ $pd->title }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Est. Delivery Date -->
                <div>
                    <label class="block text-xs font-semibold text-zinc-800 dark:text-zinc-200 mb-1">
                        Estimated Delivery Date
                    </label>
                    <input
                        type="date"
                        id="field-estimated_delivery_date"
                        class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                </div>

                <!-- Dispatch Date -->
                <div>
                    <label class="block text-xs font-semibold text-zinc-800 dark:text-zinc-200 mb-1">
                        Dispatch Date
                    </label>
                    <input
                        type="date"
                        id="field-dispatch_date"
                        class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                </div>

                <!-- Assigned Logistics Coordinator -->
                <div>
                    <label class="block text-xs font-semibold text-zinc-800 dark:text-zinc-200 mb-1">
                        Logistics Coordinator / Owner
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

            <!-- Origin Facility -->
            <div>
                <label class="block text-xs font-semibold text-zinc-800 dark:text-zinc-200 mb-1">
                    Origin Facility / Warehouse
                </label>
                <input
                    type="text"
                    id="field-shipping_origin"
                    placeholder="e.g. Silicon Valley Distribution Hub, San Jose CA"
                    class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
            </div>

            <!-- Destination Address -->
            <div>
                <label class="block text-xs font-semibold text-zinc-800 dark:text-zinc-200 mb-1">
                    Destination Address &amp; Delivery Site
                </label>
                <textarea
                    id="field-destination_address"
                    rows="2"
                    placeholder="e.g. Ashburn Tier IV Data Center, 21820 Pacific Blvd, Ashburn VA"
                    class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                ></textarea>
            </div>

            <!-- Manifest Notes -->
            <div>
                <label class="block text-xs font-semibold text-zinc-800 dark:text-zinc-200 mb-1">
                    Manifest Notes &amp; Handling Instructions
                </label>
                <textarea
                    id="field-notes"
                    rows="2"
                    placeholder="Enter special handling, liftgate delivery requirements, or customs broker references..."
                    class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                ></textarea>
            </div>

            <!-- Dynamic Custom Fields Section with Field-Level Security -->
            <div id="modal-custom-fields-wrapper" class="space-y-3 pt-3 border-t border-zinc-200 dark:border-zinc-800">
                <div class="flex items-center justify-between">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-zinc-600 dark:text-zinc-400">
                        Dynamic Freight Custom Fields
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
                    onclick="window.closeShipmentModal()"
                    class="px-4 py-2 text-xs font-semibold rounded-lg border border-zinc-300 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    id="btn-save-shipment"
                    class="px-5 py-2 text-xs font-semibold rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white transition-colors cursor-pointer shadow-xs"
                >
                    Save Shipment
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 2: CUSTOMIZE VISIBLE FIELDS MODAL -->
<div id="shipping-config-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-xs animate-fade-in">
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xl w-full max-w-md overflow-hidden">
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between">
            <div class="flex items-center gap-2 text-zinc-800 dark:text-zinc-200">
                <i data-lucide="sliders" class="w-4 h-4 text-indigo-600"></i>
                <h3 class="text-sm font-bold text-zinc-950 dark:text-zinc-100">Customize Visible Fields</h3>
            </div>
            <button type="button" onclick="window.closeShippingConfigModal()" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 cursor-pointer">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
        <div class="p-6 space-y-4">
            <p class="text-xs text-zinc-500">
                Select which standard and dynamic custom fields are displayed in the active view:
            </p>
            <div id="shipping-config-fields-list" class="space-y-2 max-h-60 overflow-y-auto pr-1">
                <!-- Checkboxes populated via JS -->
            </div>
            <div class="flex items-center justify-between pt-3 border-t border-zinc-200 dark:border-zinc-800">
                <button type="button" onclick="window.resetShippingDefaultVisibleFields()" class="text-xs text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200 underline cursor-pointer">
                    Reset Defaults
                </button>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="window.closeShippingConfigModal()" class="px-3 py-1.5 text-xs font-semibold rounded-lg border border-zinc-300 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 cursor-pointer">
                        Cancel
                    </button>
                    <button type="button" onclick="window.saveShippingVisibleFieldsConfig()" class="px-3.5 py-1.5 text-xs font-semibold rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white cursor-pointer shadow-xs">
                        Apply Fields
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL 3: UNIVERSAL FILTER MODAL -->
<div id="shipping-filter-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-xs animate-fade-in">
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xl w-full max-w-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between">
            <div class="flex items-center gap-2 text-zinc-800 dark:text-zinc-200">
                <i data-lucide="filter" class="w-4 h-4 text-indigo-600"></i>
                <h3 class="text-sm font-bold text-zinc-950 dark:text-zinc-100">Filter Freight Shipments</h3>
            </div>
            <button type="button" onclick="window.closeShippingFilterModal()" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 cursor-pointer">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
        <div class="p-6 space-y-4">
            <div id="shipping-filter-rules-list" class="space-y-3 max-h-64 overflow-y-auto pr-1">
                <!-- Filter rule rows injected dynamically -->
            </div>

            <button
                type="button"
                onclick="window.addShippingFilterRuleRow()"
                class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 cursor-pointer"
            >
                <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i>
                <span>Add Filter Condition</span>
            </button>

            <div class="flex items-center justify-between pt-3 border-t border-zinc-200 dark:border-zinc-800">
                <button type="button" onclick="window.clearAllShippingFilters()" class="text-xs text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200 underline cursor-pointer">
                    Clear All
                </button>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="window.closeShippingFilterModal()" class="px-3 py-1.5 text-xs font-semibold rounded-lg border border-zinc-300 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 cursor-pointer">
                        Cancel
                    </button>
                    <button type="button" onclick="window.applyShippingFilterRules()" class="px-3.5 py-1.5 text-xs font-semibold rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white cursor-pointer shadow-xs">
                        Apply Filter
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL 4: TRANSFER OWNER MODAL -->
<div id="shipping-transfer-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-xs animate-fade-in">
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xl w-full max-w-sm overflow-hidden p-5 space-y-4">
        <div class="flex items-center gap-2.5 text-indigo-600">
            <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-950/50 flex items-center justify-center">
                <i data-lucide="user-check" class="w-4 h-4"></i>
            </div>
            <div>
                <h3 class="text-sm font-bold text-zinc-950 dark:text-zinc-100">Transfer Shipment Owner</h3>
                <p class="text-[11px] text-zinc-500">Reassign logistics coordinator</p>
            </div>
        </div>
        <input type="hidden" id="transfer-shipment-id" value="">
        <p class="text-xs text-zinc-600 dark:text-zinc-400" id="transfer-shipment-name-label">
            Select a new team member to take ownership of this freight shipment.
        </p>
        <div>
            <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300 mb-1">New Owner</label>
            <select id="transfer-new-shipping-owner-id" class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                @foreach($users ?? [] as $u)
                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-center justify-end gap-2 pt-2">
            <button type="button" onclick="window.closeShippingTransferModal()" class="px-3 py-1.5 text-xs font-semibold rounded-lg border border-zinc-300 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 cursor-pointer">
                Cancel
            </button>
            <button type="button" onclick="window.submitShippingTransfer()" class="px-3.5 py-1.5 text-xs font-semibold rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white cursor-pointer shadow-xs">
                Confirm Reassign
            </button>
        </div>
    </div>
</div>

<!-- Safe Blade JSON Hydration with Zero Bracket/Parentheses Nesting Issues -->
@php
    $shippingHydrationData = [
        'initialShipments' => $initialShipments ?? [],
        'customFields' => $customFields ?? [],
        'fieldPermissions' => $fieldPermissions ?? (object)[],
        'accounts' => $accounts ?? [],
        'opportunities' => $opportunities ?? [],
        'purchaseDeals' => $purchaseDeals ?? [],
        'users' => $users ?? [],
        'stages' => $stages ?? ['Pending', 'In Transit', 'Customs Clearance', 'Out for Delivery', 'Delivered'],
        'carriers' => $carriers ?? ['FedEx Freight', 'UPS Express', 'DHL Global', 'Maersk Line', 'Expeditors International'],
        'currentUserProfile' => $currentUserProfile ?? 'Administrator',
    ];
@endphp
<script>
    window.__SHIPPING_DATA__ = {!! json_encode($shippingHydrationData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};
</script>
<script src="{{ asset('js/shipping.js') }}?v=1.0"></script>
@endsection
