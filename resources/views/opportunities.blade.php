@extends('layouts.app')

@section('title', 'Pipeline Opportunities — DealSync Enterprise CRM')

@section('content')
<!-- Centered Max-Width Container matching React page-wrapper (max-width: 1240px, padding: 2.25rem 2.5rem) -->
<div id="opportunities-page-root" class="max-w-[1240px] mx-auto w-full px-6 sm:px-10 py-7 space-y-6 animate-fade-in">
    <!-- Top Action Header -->
    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4 pb-2 border-b border-zinc-200 dark:border-zinc-800">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800">
                    Revenue Pipeline
                </span>
                <span class="text-xs text-zinc-500 dark:text-zinc-400">
                    Procurement PO Coupling &amp; Dynamic Stages
                </span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-950 dark:text-zinc-100">
                Pipeline Opportunities
            </h1>
            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 max-w-2xl">
                Multi-stage enterprise sales pipeline coupled with supplier procurement POs and win probability weighting. Synced dynamically with custom fields and field-level security permissions.
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap shrink-0">
            <!-- View Switcher (Table vs Kanban) -->
            <div class="inline-flex items-center p-0.5 rounded-lg bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700">
                <button
                    type="button"
                    id="btn-view-table"
                    onclick="window.switchOpportunityView('table')"
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-md transition-all cursor-pointer bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white shadow-2xs"
                >
                    <i data-lucide="table" class="w-3.5 h-3.5"></i>
                    <span>Table</span>
                </button>
                <button
                    type="button"
                    id="btn-view-kanban"
                    onclick="window.switchOpportunityView('kanban')"
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-md transition-all cursor-pointer text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white"
                >
                    <i data-lucide="kanban" class="w-3.5 h-3.5"></i>
                    <span>Kanban</span>
                </button>
            </div>

            <!-- Customize Visible Fields -->
            <button
                type="button"
                onclick="window.openOpportunityConfigModal()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs cursor-pointer"
                title="Customize Visible Fields"
            >
                <i data-lucide="sliders" class="w-3.5 h-3.5 text-zinc-500"></i>
                <span>Customize</span>
            </button>

            <!-- Filter Button -->
            <button
                type="button"
                onclick="window.openOpportunityFilterModal()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs cursor-pointer"
                title="Filter Opportunities"
            >
                <i data-lucide="filter" class="w-3.5 h-3.5 text-zinc-500"></i>
                <span>Filter</span>
                <span id="opps-filter-badge" class="hidden px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-blue-600 text-white">0</span>
            </button>

            <!-- Export CSV Button -->
            <button
                type="button"
                onclick="window.exportOpportunitiesCsv()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs cursor-pointer"
            >
                <i data-lucide="download" class="w-3.5 h-3.5 text-zinc-500"></i>
                <span>Export CSV</span>
            </button>

            <!-- Quick Deal Primary Action -->
            <button
                type="button"
                onclick="window.openOpportunityModal('create')"
                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm transition-colors cursor-pointer"
            >
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Quick Deal</span>
            </button>
        </div>
    </div>

    <!-- Top KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 border border-zinc-200 dark:border-zinc-800 shadow-xs">
            <div class="text-[11px] font-semibold text-zinc-500 uppercase tracking-wider">TOTAL PIPELINE VALUE</div>
            <div id="kpi-total-pipeline" class="text-2xl font-extrabold text-zinc-950 dark:text-zinc-100 mt-1 tracking-tight">
                $0
            </div>
            <span id="kpi-total-deals-count" class="text-[11px] text-zinc-500 font-medium mt-1 inline-block">
                {{ count($initialOpportunities ?? []) }} Active Opportunities
            </span>
        </div>

        <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 border border-zinc-200 dark:border-zinc-800 shadow-xs">
            <div class="text-[11px] font-semibold text-zinc-500 uppercase tracking-wider">WEIGHTED FORECAST</div>
            <div id="kpi-weighted-forecast" class="text-2xl font-extrabold text-indigo-600 mt-1 tracking-tight">
                $0
            </div>
            <span id="kpi-realization-rate" class="text-[11px] text-emerald-600 font-semibold mt-1 inline-block">
                Expected Realization
            </span>
        </div>

        <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 border border-zinc-200 dark:border-zinc-800 shadow-xs">
            <div class="text-[11px] font-semibold text-zinc-500 uppercase tracking-wider">PROCUREMENT BACKED</div>
            <div id="kpi-procurement-backed" class="text-2xl font-extrabold text-emerald-600 mt-1 tracking-tight">
                100% Coupled
            </div>
            <span class="text-[11px] text-zinc-500 font-medium mt-1 inline-block">Direct Supplier PO Linking</span>
        </div>

        <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 border border-zinc-200 dark:border-zinc-800 shadow-xs">
            <div class="text-[11px] font-semibold text-zinc-500 uppercase tracking-wider">AVG WIN PROBABILITY</div>
            <div id="kpi-avg-probability" class="text-2xl font-extrabold text-amber-600 mt-1 tracking-tight">
                0%
            </div>
            <span class="text-[11px] text-zinc-500 font-medium mt-1 inline-block">Pipeline Propensity Metric</span>
        </div>
    </div>

    <!-- Pipeline Stages Chips Container -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1" id="opps-stage-chips-container">
        <!-- Rendered dynamically by opportunities.js -->
    </div>

    <!-- Toolbar: Search Box & Dynamic Filter Chips -->
    <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 p-3 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 shadow-xs">
        <div class="relative flex-1 max-w-md">
            <i data-lucide="search" class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
            <input
                type="text"
                id="opps-search-input"
                placeholder="Search opportunities by name, account, PO number..."
                class="w-full pl-9 pr-4 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
            >
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <button
                type="button"
                onclick="window.refreshOpportunitiesData()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors cursor-pointer"
                title="Reload Opportunities"
            >
                <i data-lucide="refresh-cw" class="w-3.5 h-3.5 text-zinc-500"></i>
                <span>Refresh</span>
            </button>
        </div>
    </div>

    <!-- Active Filter Chips Row -->
    <div id="opps-active-filters-bar" class="hidden flex items-center gap-2 flex-wrap text-xs">
        <!-- Rendered dynamically by opportunities.js -->
    </div>

    <!-- MAIN VIEW 1: COMPACT TABLE VIEW -->
    <div id="opps-table-view-container" class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-zinc-50 dark:bg-zinc-800/60 border-b border-zinc-200 dark:border-zinc-800 text-zinc-500 dark:text-zinc-400 uppercase tracking-wider text-[11px] font-semibold select-none">
                    <tr id="opps-table-header-row">
                        <!-- Rendered dynamically by opportunities.js based on visible fields -->
                    </tr>
                </thead>
                <tbody id="opps-table-tbody" class="divide-y divide-zinc-200 dark:divide-zinc-800 text-zinc-800 dark:text-zinc-200">
                    <!-- Rendered dynamically by opportunities.js -->
                </tbody>
            </table>
        </div>

        <!-- Empty State Container -->
        <div id="opps-empty-state" class="hidden py-14 px-4 text-center">
            <div class="w-12 h-12 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center mx-auto text-zinc-400 mb-3">
                <i data-lucide="briefcase" class="w-6 h-6"></i>
            </div>
            <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">No opportunities found</h3>
            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 max-w-sm mx-auto">
                No pipeline opportunities match your active filters or search criteria. Try modifying your search or add a new deal.
            </p>
            <button
                type="button"
                onclick="window.openOpportunityModal('create')"
                class="mt-4 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-xs cursor-pointer"
            >
                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                <span>Add Opportunity</span>
            </button>
        </div>

        <!-- Table Footer / Count -->
        <div class="px-4 py-2.5 bg-zinc-50 dark:bg-zinc-800/40 border-t border-zinc-200 dark:border-zinc-800 flex items-center justify-between text-xs text-zinc-500 dark:text-zinc-400">
            <span id="opps-table-summary">Showing 0 opportunities</span>
            <div class="flex items-center gap-3">
                <span class="text-[11px]">Security Profile: <strong>{{ $currentUserProfile ?? 'Administrator' }}</strong></span>
            </div>
        </div>
    </div>

    <!-- MAIN VIEW 2: KANBAN BOARD VIEW -->
    <div id="opps-kanban-view-container" class="hidden">
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4 items-start" id="opps-kanban-board">
            <!-- 5 Columns rendered dynamically: Discovery, Proposal, Negotiation, Closed Won, Closed Lost -->
        </div>
    </div>
</div>

<!-- MODAL 1: QUICK DEAL / OPPORTUNITY MODAL (Create & Edit) -->
<div id="opp-form-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-xs animate-fade-in">
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xl w-full max-w-2xl overflow-hidden max-h-[90vh] flex flex-col">
        <!-- Modal Header -->
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between shrink-0 bg-zinc-50/50 dark:bg-zinc-800/50">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 flex items-center justify-center">
                    <i data-lucide="briefcase" class="w-4 h-4"></i>
                </div>
                <div>
                    <h2 id="opp-form-modal-title" class="text-base font-bold text-zinc-950 dark:text-zinc-100">
                        Create Quick Deal
                    </h2>
                    <p class="text-[11px] text-zinc-500">Pipeline Deal synced with Procurement Purchase Orders</p>
                </div>
            </div>
            <button
                type="button"
                onclick="window.closeOpportunityModal()"
                class="w-7 h-7 rounded-lg text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800 flex items-center justify-center transition-colors cursor-pointer"
            >
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- Modal Form Body -->
        <form id="opp-record-form" onsubmit="window.saveOpportunityForm(event)" class="overflow-y-auto p-6 space-y-5 flex-1">
            <input type="hidden" id="form-opp-id" value="">

            <!-- SECTION: STANDARD DEAL DETAILS -->
            <div class="space-y-4">
                <div class="flex items-center justify-between pb-1 border-b border-zinc-100 dark:border-zinc-800">
                    <span class="text-xs font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider">Standard Deal Details</span>
                    <span class="text-[10px] text-zinc-400">Fields marked * are mandatory</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Deal Name -->
                    <div class="sm:col-span-2 opp-field-group" data-field-name="name">
                        <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            Opportunity Name <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="form-opp-name"
                            required
                            placeholder="e.g. Enterprise Cloud Migration — Stage 2"
                            class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                    </div>

                    <!-- Customer Account -->
                    <div class="opp-field-group" data-field-name="account_id">
                        <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            Customer Account <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="form-opp-account-id"
                            required
                            class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                            <option value="">-- Select Customer Account --</option>
                            @foreach($accounts ?? [] as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Primary Contact Stakeholder -->
                    <div class="opp-field-group" data-field-name="contact_id">
                        <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            Stakeholder Contact
                        </label>
                        <select
                            id="form-opp-contact-id"
                            class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                            <option value="">-- Optional Linked Contact --</option>
                            @foreach($contacts ?? [] as $c)
                                <option value="{{ $c->id }}">{{ $c->first_name }} {{ $c->last_name }} ({{ $c->email }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Backed Purchase PO (Purchase Deal) -->
                    <div class="opp-field-group" data-field-name="purchase_deal_id">
                        <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            Backed Purchase PO
                        </label>
                        <select
                            id="form-opp-purchase-deal-id"
                            class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                            <option value="">-- Auto-generate PO or Select Existing --</option>
                            @foreach($purchaseDeals ?? [] as $pd)
                                <option value="{{ $pd->id }}">{{ $pd->po_number }} — {{ $pd->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Deal Value ($) -->
                    <div class="opp-field-group" data-field-name="amount">
                        <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            Deal Value ($ USD) <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="number"
                            id="form-opp-amount"
                            required
                            step="0.01"
                            min="0"
                            placeholder="50000.00"
                            class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                    </div>

                    <!-- Pipeline Stage -->
                    <div class="opp-field-group" data-field-name="stage">
                        <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            Pipeline Stage <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="form-opp-stage"
                            required
                            onchange="window.handleStageChangeInForm(this.value)"
                            class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                            <option value="Discovery">Discovery</option>
                            <option value="Proposal">Proposal</option>
                            <option value="Negotiation">Negotiation</option>
                            <option value="Closed Won">Closed Won</option>
                            <option value="Closed Lost">Closed Lost</option>
                        </select>
                    </div>

                    <!-- Win Probability (%) -->
                    <div class="opp-field-group" data-field-name="probability">
                        <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            Win Probability (%) <span id="form-opp-probability-badge" class="font-bold text-indigo-600">50%</span>
                        </label>
                        <input
                            type="range"
                            id="form-opp-probability"
                            min="0"
                            max="100"
                            value="50"
                            step="5"
                            oninput="document.getElementById('form-opp-probability-badge').innerText = this.value + '%'"
                            class="w-full accent-indigo-600 cursor-pointer"
                        >
                    </div>

                    <!-- Expected Close Date -->
                    <div class="opp-field-group" data-field-name="expected_close_date">
                        <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            Target Close Date
                        </label>
                        <input
                            type="date"
                            id="form-opp-close-date"
                            class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                    </div>

                    <!-- Opportunity Owner -->
                    <div class="opp-field-group" data-field-name="assigned_to">
                        <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            Assigned Owner
                        </label>
                        <select
                            id="form-opp-assigned-to"
                            class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                            <option value="">-- Unassigned --</option>
                            @foreach($users ?? [] as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Deal Strategy / Notes -->
                    <div class="sm:col-span-2 opp-field-group" data-field-name="notes">
                        <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            Deal Notes / Strategy
                        </label>
                        <textarea
                            id="form-opp-notes"
                            rows="2"
                            placeholder="Executive sponsor, procurement dynamics, contract milestones..."
                            class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        ></textarea>
                    </div>
                </div>
            </div>

            <!-- SECTION: DYNAMIC CUSTOM FIELDS (Permission Enforced) -->
            <div id="opp-dynamic-custom-fields-section" class="space-y-4 pt-3 border-t border-zinc-200 dark:border-zinc-800">
                <div class="flex items-center justify-between pb-1 border-b border-zinc-100 dark:border-zinc-800">
                    <span class="text-xs font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider">Dynamic Custom Fields</span>
                    <span class="text-[10px] text-zinc-400">Field-Level Security Active</span>
                </div>

                <div id="opp-dynamic-fields-container" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Populated dynamically by opportunities.js respecting Hidden / Read-Only / Read/Write permissions -->
                </div>
            </div>

            <!-- Modal Action Buttons -->
            <div class="pt-4 border-t border-zinc-200 dark:border-zinc-800 flex items-center justify-end gap-2 shrink-0">
                <button
                    type="button"
                    onclick="window.closeOpportunityModal()"
                    class="px-4 py-2 text-xs font-semibold rounded-lg border border-zinc-300 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    id="btn-save-opp-form"
                    class="px-4 py-2 text-xs font-semibold rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white transition-colors cursor-pointer shadow-xs"
                >
                    Save Opportunity
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 2: CUSTOMIZE VISIBLE FIELDS MODAL -->
<div id="opp-config-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-xs animate-fade-in">
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xl w-full max-w-md overflow-hidden flex flex-col">
        <div class="px-5 py-4 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between bg-zinc-50/50 dark:bg-zinc-800/50">
            <div class="flex items-center gap-2">
                <i data-lucide="sliders" class="w-4 h-4 text-indigo-600"></i>
                <h3 class="text-sm font-bold text-zinc-950 dark:text-zinc-100">Customize Visible Fields</h3>
            </div>
            <button type="button" onclick="window.closeOpportunityConfigModal()" class="text-zinc-400 hover:text-zinc-600 cursor-pointer">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
        <div class="p-5 overflow-y-auto max-h-[60vh] space-y-3" id="opp-config-fields-list">
            <!-- Populated dynamically by opportunities.js -->
        </div>
        <div class="px-5 py-3 border-t border-zinc-200 dark:border-zinc-800 flex items-center justify-between bg-zinc-50 dark:bg-zinc-800/40">
            <button type="button" onclick="window.resetOpportunityDefaultFields()" class="text-xs text-indigo-600 hover:underline cursor-pointer font-medium">
                Reset Defaults
            </button>
            <div class="flex items-center gap-2">
                <button type="button" onclick="window.closeOpportunityConfigModal()" class="px-3 py-1.5 text-xs font-semibold rounded-lg border border-zinc-300 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 cursor-pointer">
                    Cancel
                </button>
                <button type="button" onclick="window.saveOpportunityFieldConfigs()" class="px-3.5 py-1.5 text-xs font-semibold rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white cursor-pointer shadow-xs">
                    Apply Fields
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL 3: UNIVERSAL FILTER MODAL -->
<div id="opp-filter-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-xs animate-fade-in">
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xl w-full max-w-lg overflow-hidden flex flex-col">
        <div class="px-5 py-4 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between bg-zinc-50/50 dark:bg-zinc-800/50">
            <div class="flex items-center gap-2">
                <i data-lucide="filter" class="w-4 h-4 text-blue-600"></i>
                <h3 class="text-sm font-bold text-zinc-950 dark:text-zinc-100">Filter Pipeline Opportunities</h3>
            </div>
            <button type="button" onclick="window.closeOpportunityFilterModal()" class="text-zinc-400 hover:text-zinc-600 cursor-pointer">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
        <div class="p-5 space-y-3 overflow-y-auto max-h-[60vh]">
            <div id="opp-filter-rules-container" class="space-y-2.5">
                <!-- Filter rules added here dynamically -->
            </div>
            <button
                type="button"
                onclick="window.addOpportunityFilterRule()"
                class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600 hover:text-indigo-700 pt-1 cursor-pointer"
            >
                <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i>
                <span>+ Add Condition</span>
            </button>
        </div>
        <div class="px-5 py-3 border-t border-zinc-200 dark:border-zinc-800 flex items-center justify-between bg-zinc-50 dark:bg-zinc-800/40">
            <button type="button" onclick="window.clearAllOpportunityFilters()" class="text-xs text-red-600 hover:underline cursor-pointer font-medium">
                Clear Filters
            </button>
            <div class="flex items-center gap-2">
                <button type="button" onclick="window.closeOpportunityFilterModal()" class="px-3 py-1.5 text-xs font-semibold rounded-lg border border-zinc-300 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 cursor-pointer">
                    Cancel
                </button>
                <button type="button" onclick="window.applyOpportunityFilters()" class="px-3.5 py-1.5 text-xs font-semibold rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white cursor-pointer shadow-xs">
                    Apply Filter
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL 4: TRANSFER OWNER MODAL -->
<div id="opp-transfer-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-xs animate-fade-in">
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xl w-full max-w-sm overflow-hidden p-5 space-y-4">
        <div class="flex items-center gap-2.5 text-indigo-600">
            <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-950/50 flex items-center justify-center">
                <i data-lucide="user-check" class="w-4 h-4"></i>
            </div>
            <div>
                <h3 class="text-sm font-bold text-zinc-950 dark:text-zinc-100">Transfer Opportunity</h3>
                <p class="text-[11px] text-zinc-500">Reassign deal owner &amp; pipeline quota</p>
            </div>
        </div>
        <input type="hidden" id="transfer-opp-id" value="">
        <p class="text-xs text-zinc-600 dark:text-zinc-400" id="transfer-opp-name-label">
            Select a new team member to take ownership of this opportunity.
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
            <button type="button" onclick="window.closeOpportunityTransferModal()" class="px-3 py-1.5 text-xs font-semibold rounded-lg border border-zinc-300 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 cursor-pointer">
                Cancel
            </button>
            <button type="button" onclick="window.submitOpportunityTransfer()" class="px-3.5 py-1.5 text-xs font-semibold rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white cursor-pointer shadow-xs">
                Confirm Reassign
            </button>
        </div>
    </div>
</div>

<!-- Safe Blade JSON Hydration with Zero Bracket/Parentheses Nesting Issues -->
@php
    $opportunitiesHydrationData = [
        'initialOpportunities' => $initialOpportunities ?? [],
        'customFields' => $customFields ?? [],
        'fieldPermissions' => $fieldPermissions ?? (object)[],
        'accounts' => $accounts ?? [],
        'contacts' => $contacts ?? [],
        'purchaseDeals' => $purchaseDeals ?? [],
        'users' => $users ?? [],
        'stages' => $stages ?? ['Discovery', 'Proposal', 'Negotiation', 'Closed Won', 'Closed Lost'],
        'currentUserProfile' => $currentUserProfile ?? 'Administrator',
    ];
@endphp
<script>
    window.__OPPORTUNITIES_DATA__ = {!! json_encode($opportunitiesHydrationData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};
</script>
<script src="{{ asset('js/opportunities.js') }}?v=1.0"></script>
@endsection
