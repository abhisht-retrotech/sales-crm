@extends('layouts.app')

@section('title', 'Leads & Intake — DealSync Enterprise CRM')

@section('content')
<!-- Centered Max-Width Container matching React page-wrapper (max-width: 1240px, padding: 2.25rem 2.5rem) -->
<div id="leads-page-root" class="max-w-[1240px] mx-auto w-full px-6 sm:px-10 py-7 space-y-6 animate-fade-in">
    <!-- Top Action Header -->
    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4 pb-2 border-b border-zinc-200 dark:border-zinc-800">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 dark:bg-indigo-950/40 dark:text-indigo-300 dark:border-indigo-800">
                    Inbound Demand Generation
                </span>
                <span class="text-xs text-zinc-500 dark:text-zinc-400">
                    Decoupled 3-Way Atomic Conversion
                </span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-950 dark:text-zinc-100">
                Leads &amp; Intake
            </h1>
            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 max-w-2xl">
                Capture, score, and qualify inbound leads with instant decoupled conversion into Accounts and Contacts. Synced with dynamic custom fields and field-level permissions.
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap shrink-0">
            <!-- Filter Button -->
            <button
                type="button"
                onclick="window.openFilterModal()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs cursor-pointer"
            >
                <i data-lucide="filter" class="w-3.5 h-3.5 text-zinc-500"></i>
                <span>Filter</span>
                <span id="leads-filter-badge" class="hidden px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-blue-600 text-white">0</span>
            </button>

            <!-- Export CSV Button -->
            <button
                type="button"
                onclick="window.exportLeadsCsv()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs cursor-pointer"
            >
                <i data-lucide="download" class="w-3.5 h-3.5 text-zinc-500"></i>
                <span>Export CSV</span>
            </button>

            <!-- Add Lead Primary Action -->
            <button
                type="button"
                onclick="window.openLeadModal('create')"
                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm transition-colors cursor-pointer"
            >
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Add Lead</span>
            </button>
        </div>
    </div>

    <!-- Quick Stage Filter Chips -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1" id="leads-stage-chips-container">
        <!-- Rendered dynamically by leads.js -->
    </div>

    <!-- Toolbar: Search, View Switcher (Table vs Kanban), Customize, Filter -->
    <div class="bg-white dark:bg-zinc-900 rounded-xl p-3 border border-zinc-200 dark:border-zinc-800 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-3">
        <!-- Left: Search Box -->
        <div class="flex items-center gap-3 w-full sm:w-auto flex-1 max-w-md">
            <div class="relative w-full">
                <i data-lucide="search" class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                <input
                    type="text"
                    id="leads-search-input"
                    placeholder="Search leads by name, company, email..."
                    class="w-full pl-9 pr-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/60 text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                >
            </div>
            <span id="leads-records-count-label" class="text-xs text-zinc-500 dark:text-zinc-400 whitespace-nowrap">
                Showing <strong>0</strong> records
            </span>
        </div>

        <!-- Right: View Mode Toggle & Customize -->
        <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
            <!-- View Mode Switcher -->
            <div class="flex items-center p-0.5 rounded-lg bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-xs">
                <button
                    type="button"
                    id="btn-lead-view-table"
                    onclick="window.switchLeadsViewMode('table')"
                    class="flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-md bg-white text-zinc-950 shadow-xs dark:bg-zinc-800 dark:text-zinc-100 cursor-pointer"
                >
                    <i data-lucide="table" class="w-3.5 h-3.5"></i>
                    <span>Table</span>
                </button>
                <button
                    type="button"
                    id="btn-lead-view-kanban"
                    onclick="window.switchLeadsViewMode('kanban')"
                    class="flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-md text-zinc-500 hover:text-zinc-950 dark:text-zinc-400 dark:hover:text-zinc-100 cursor-pointer"
                >
                    <i data-lucide="kanban" class="w-3.5 h-3.5"></i>
                    <span>Kanban</span>
                </button>
            </div>

            <!-- Customize Visible Fields -->
            <button
                type="button"
                onclick="window.openCustomizeModal()"
                class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs cursor-pointer"
                title="Customize visible fields in current view"
            >
                <i data-lucide="sliders" class="w-3.5 h-3.5 text-zinc-500"></i>
                <span class="hidden sm:inline">Customize</span>
            </button>
        </div>
    </div>

    <!-- Bulk Selection Action Banner -->
    <div id="leads-bulk-action-bar" class="hidden items-center justify-between p-3 rounded-xl bg-indigo-50 border border-indigo-200 dark:bg-indigo-950/40 dark:border-indigo-800">
        <div class="flex items-center gap-2 text-xs font-semibold text-indigo-900 dark:text-indigo-200">
            <i data-lucide="check-square" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"></i>
            <span id="leads-selected-count-label">0 leads selected</span>
        </div>
        <div class="flex items-center gap-2">
            <button
                type="button"
                onclick="document.getElementById('leads-select-all-chk').checked = false; window.initLeadsModule();"
                class="px-2.5 py-1 text-xs font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100 cursor-pointer"
            >
                Deselect
            </button>
            <button
                type="button"
                onclick="window.deleteSelectedLeads()"
                class="px-3 py-1 text-xs font-semibold bg-rose-600 hover:bg-rose-700 text-white rounded-lg transition-colors cursor-pointer inline-flex items-center gap-1"
            >
                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                <span>Delete Selected</span>
            </button>
        </div>
    </div>

    <!-- Active Filter Chips Bar -->
    <div id="leads-active-filters-bar" class="hidden">
        <!-- Rendered dynamically -->
    </div>

    <!-- Loading State Indicator -->
    <div id="leads-loading-indicator" class="hidden py-8 text-center">
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-xs text-zinc-600 dark:text-zinc-400 font-medium">
            <i data-lucide="loader-2" class="w-4 h-4 animate-spin text-indigo-600"></i>
            <span>Loading leads repository...</span>
        </div>
    </div>

    <!-- Main Views -->
    <div id="leads-table-view">
        <!-- Populated by leads.js -->
    </div>

    <div id="leads-kanban-view" class="hidden">
        <!-- Populated by leads.js -->
    </div>

    <!-- ==================== MODALS ==================== -->

    <!-- 1. Add / Edit Lead Record Modal -->
    <div id="leadFormModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-zinc-950/60 backdrop-blur-xs overflow-y-auto">
        <div class="relative w-full max-w-2xl bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xl overflow-hidden my-8">
            <!-- Modal Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-800/40">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-indigo-100 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                        <i data-lucide="user-plus" class="w-4 h-4"></i>
                    </div>
                    <h3 id="leadFormModalTitle" class="text-sm font-bold text-zinc-950 dark:text-zinc-100">Add Lead</h3>
                </div>
                <button
                    type="button"
                    onclick="window.closeLeadModal()"
                    class="p-1 rounded-lg text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors"
                >
                    ✕
                </button>
            </div>

            <!-- Modal Form -->
            <form id="leadRecordForm" class="p-6 space-y-5 max-h-[78vh] overflow-y-auto">
                <input type="hidden" id="lead-input-id" name="id">

                <!-- Section: Core Details -->
                <div>
                    <h4 class="text-xs font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider mb-3 pb-1 border-b border-zinc-200 dark:border-zinc-800 flex items-center gap-1.5">
                        <i data-lucide="info" class="w-3.5 h-3.5 text-indigo-500"></i>
                        <span>Core Lead Identification</span>
                    </h4>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1 form-group-item">
                            <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                First Name <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="lead-input-first_name"
                                name="first_name"
                                required
                                class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                                placeholder="e.g. Sarah"
                            >
                        </div>

                        <div class="space-y-1 form-group-item">
                            <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                Last Name <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="lead-input-last_name"
                                name="last_name"
                                required
                                class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                                placeholder="e.g. Jenkins"
                            >
                        </div>

                        <div class="space-y-1 form-group-item">
                            <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                Company Name <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="lead-input-company"
                                name="company"
                                required
                                class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                                placeholder="e.g. Acme Corporation"
                            >
                        </div>

                        <div class="space-y-1 form-group-item">
                            <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Job Title</label>
                            <input
                                type="text"
                                id="lead-input-title"
                                name="title"
                                class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                                placeholder="e.g. VP of Infrastructure"
                            >
                        </div>

                        <div class="space-y-1 form-group-item">
                            <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Email Address</label>
                            <input
                                type="email"
                                id="lead-input-email"
                                name="email"
                                class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                                placeholder="s.jenkins@acmecorp.io"
                            >
                        </div>

                        <div class="space-y-1 form-group-item">
                            <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Phone Number</label>
                            <input
                                type="text"
                                id="lead-input-phone"
                                name="phone"
                                class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                                placeholder="+1 (512) 890-4412"
                            >
                        </div>
                    </div>
                </div>

                <!-- Section: Qualification & Scoring -->
                <div>
                    <h4 class="text-xs font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider mb-3 pb-1 border-b border-zinc-200 dark:border-zinc-800 flex items-center gap-1.5">
                        <i data-lucide="target" class="w-3.5 h-3.5 text-emerald-500"></i>
                        <span>Status &amp; Qualification</span>
                    </h4>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1 form-group-item">
                            <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Status Stage</label>
                            <select
                                id="lead-input-status"
                                name="status"
                                class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                            >
                                <option value="New">New</option>
                                <option value="Contacted">Contacted</option>
                                <option value="Qualified">Qualified</option>
                                <option value="Lost">Lost</option>
                            </select>
                        </div>

                        <div class="space-y-1 form-group-item">
                            <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Lead Source</label>
                            <select
                                id="lead-input-source"
                                name="source"
                                class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                            >
                                @foreach($sources ?? [] as $src)
                                    <option value="{{ $src }}">{{ $src }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-1 form-group-item">
                            <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Propensity Score (0–100)</label>
                            <input
                                type="number"
                                id="lead-input-score"
                                name="score"
                                min="0"
                                max="100"
                                class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                                placeholder="50"
                            >
                        </div>

                        <div class="space-y-1 form-group-item">
                            <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Rating Priority</label>
                            <select
                                id="lead-input-rating"
                                name="rating"
                                class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                            >
                                <option value="Hot">Hot (Immediate follow-up)</option>
                                <option value="Warm">Warm (Nurture in progress)</option>
                                <option value="Cold">Cold (Low responsiveness)</option>
                            </select>
                        </div>

                        <div class="space-y-1 form-group-item col-span-full">
                            <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Assigned Owner</label>
                            <select
                                id="lead-input-assigned_to"
                                name="assigned_to"
                                class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                            >
                                <option value="">Unassigned</option>
                                @foreach($users ?? [] as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-1 form-group-item col-span-full">
                            <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Qualification Notes</label>
                            <textarea
                                id="lead-input-notes"
                                name="notes"
                                rows="2"
                                class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                                placeholder="Enter context or customer requirements for qualification..."
                            ></textarea>
                        </div>
                    </div>
                </div>

                <!-- Section: Dynamic Custom Fields (Synced from tenant DB with Permissions) -->
                <div>
                    <h4 class="text-xs font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider mb-3 pb-1 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between">
                        <div class="flex items-center gap-1.5">
                            <i data-lucide="layers" class="w-3.5 h-3.5 text-purple-500"></i>
                            <span>Dynamic Custom Fields</span>
                        </div>
                        <span class="text-[10px] text-zinc-400 font-normal">Field Permissions Enforced</span>
                    </h4>

                    <div id="lead-dynamic-custom-fields-container" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Rendered dynamically by leads.js -->
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                    <button
                        type="button"
                        onclick="window.closeLeadModal()"
                        class="px-4 py-1.5 text-xs font-semibold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-lg transition-colors cursor-pointer"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="px-4 py-1.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm transition-colors cursor-pointer"
                    >
                        Save Lead
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- 2. Atomic Lead Conversion Modal -->
    <div id="convertLeadModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-zinc-950/60 backdrop-blur-xs overflow-y-auto">
        <div class="relative w-full max-w-lg bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xl overflow-hidden my-8">
            <div class="flex items-center justify-between px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 bg-emerald-50/50 dark:bg-emerald-950/30">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                        <i data-lucide="arrow-right-left" class="w-4 h-4"></i>
                    </div>
                    <h3 id="convertLeadTitle" class="text-sm font-bold text-zinc-950 dark:text-zinc-100">Convert Lead</h3>
                </div>
                <button
                    type="button"
                    onclick="window.closeConvertModal()"
                    class="p-1 rounded-lg text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors"
                >
                    ✕
                </button>
            </div>

            <form id="convertLeadForm" class="p-6 space-y-4">
                <div class="p-3.5 rounded-xl bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200 dark:border-zinc-700/60 text-xs space-y-2">
                    <p class="font-semibold text-zinc-800 dark:text-zinc-200">
                        Converting this lead atomically generates the following core CRM records:
                    </p>
                    <ul class="space-y-1 pl-4 list-disc text-zinc-600 dark:text-zinc-400">
                        <li>
                            <strong class="text-zinc-900 dark:text-zinc-100">New Account:</strong>
                            <span id="convert-preview-account" class="ml-1 text-indigo-600 dark:text-indigo-400 font-medium">Acme Corporation</span>
                        </li>
                        <li>
                            <strong class="text-zinc-900 dark:text-zinc-100">New Contact:</strong>
                            <span id="convert-preview-contact" class="ml-1 text-indigo-600 dark:text-indigo-400 font-medium">Sarah Jenkins</span>
                        </li>
                    </ul>
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Initial Qualification Notes</label>
                    <textarea
                        id="convert-input-notes"
                        rows="2"
                        class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                        placeholder="Context for Account executive..."
                    ></textarea>
                </div>

                <!-- Opportunity Creation Option -->
                <div class="p-3 rounded-xl border border-zinc-200 dark:border-zinc-700/60 bg-zinc-50/50 dark:bg-zinc-800/30 space-y-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" id="convert-chk-create-opp" class="rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                        <span class="text-xs font-semibold text-zinc-800 dark:text-zinc-200">Also create new Opportunity for this Account</span>
                    </label>

                    <div id="convert-opp-fields" class="space-y-2 pt-2 border-t border-zinc-200 dark:border-zinc-700/40">
                        <div class="space-y-1">
                            <label class="text-[11px] font-medium text-zinc-600 dark:text-zinc-400">Opportunity Deal Name</label>
                            <input
                                type="text"
                                id="convert-input-opp-name"
                                class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100"
                            >
                        </div>
                        <div class="space-y-1">
                            <label class="text-[11px] font-medium text-zinc-600 dark:text-zinc-400">Target Amount ($ USD)</label>
                            <input
                                type="number"
                                id="convert-input-opp-amount"
                                value="50000"
                                class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100"
                            >
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-zinc-200 dark:border-zinc-800">
                    <button
                        type="button"
                        onclick="window.closeConvertModal()"
                        class="px-3.5 py-1.5 text-xs font-semibold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-lg transition-colors cursor-pointer"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="px-4 py-1.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm transition-colors cursor-pointer inline-flex items-center gap-1.5"
                    >
                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                        <span>Confirm Conversion</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- 3. Lead Ownership Transfer Modal -->
    <div id="transferLeadModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-zinc-950/60 backdrop-blur-xs overflow-y-auto">
        <div class="relative w-full max-w-md bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xl overflow-hidden my-8">
            <div class="flex items-center justify-between px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-800/40">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-blue-100 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                        <i data-lucide="user-check" class="w-4 h-4"></i>
                    </div>
                    <h3 id="transferLeadTitle" class="text-sm font-bold text-zinc-950 dark:text-zinc-100">Transfer Ownership</h3>
                </div>
                <button
                    type="button"
                    onclick="window.closeTransferModal()"
                    class="p-1 rounded-lg text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors"
                >
                    ✕
                </button>
            </div>

            <form id="transferLeadForm" class="p-6 space-y-4">
                <div class="text-xs text-zinc-600 dark:text-zinc-400">
                    Current Assigned Owner: <strong id="transferCurrentOwner" class="text-zinc-900 dark:text-zinc-100">Alex Morgan</strong>
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Select New Owner</label>
                    <select
                        id="transferTargetUser"
                        required
                        class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                    >
                        <!-- Populated dynamically -->
                    </select>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-zinc-200 dark:border-zinc-800">
                    <button
                        type="button"
                        onclick="window.closeTransferModal()"
                        class="px-3.5 py-1.5 text-xs font-semibold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-lg transition-colors cursor-pointer"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="px-4 py-1.5 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm transition-colors cursor-pointer inline-flex items-center gap-1.5"
                    >
                        <span>Confirm Transfer</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- 4. Customize Columns Modal -->
    <div id="customizeColumnsModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-zinc-950/60 backdrop-blur-xs overflow-y-auto">
        <div class="relative w-full max-w-md bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xl overflow-hidden my-8">
            <div class="flex items-center justify-between px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-800/40">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-indigo-100 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                        <i data-lucide="sliders" class="w-4 h-4"></i>
                    </div>
                    <h3 class="text-sm font-bold text-zinc-950 dark:text-zinc-100">Customize Visible Fields</h3>
                </div>
                <button
                    type="button"
                    onclick="window.closeCustomizeModal()"
                    class="p-1 rounded-lg text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors"
                >
                    ✕
                </button>
            </div>

            <div class="p-6 space-y-4">
                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                    Select which standard and dynamic custom fields are rendered in the active view:
                </p>

                <div id="customize-fields-checkbox-list" class="space-y-1.5 max-h-64 overflow-y-auto pr-1">
                    <!-- Populated dynamically -->
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-zinc-200 dark:border-zinc-800">
                    <button
                        type="button"
                        onclick="window.closeCustomizeModal()"
                        class="px-3.5 py-1.5 text-xs font-semibold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-lg transition-colors cursor-pointer"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        onclick="window.saveCustomizeFields()"
                        class="px-4 py-1.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm transition-colors cursor-pointer"
                    >
                        Save Preferences
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 5. Universal Filter Rules Modal -->
    <div id="leadsFilterModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-zinc-950/60 backdrop-blur-xs overflow-y-auto">
        <div class="relative w-full max-w-md bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xl overflow-hidden my-8">
            <div class="flex items-center justify-between px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-800/40">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-blue-100 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                        <i data-lucide="filter" class="w-4 h-4"></i>
                    </div>
                    <h3 class="text-sm font-bold text-zinc-950 dark:text-zinc-100">Add Filter Rule</h3>
                </div>
                <button
                    type="button"
                    onclick="window.closeFilterModal()"
                    class="p-1 rounded-lg text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors"
                >
                    ✕
                </button>
            </div>

            <div class="p-6 space-y-4">
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Target Field</label>
                    <select
                        id="filter-rule-field"
                        class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                    >
                        <!-- Populated dynamically -->
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Operator</label>
                    <select
                        id="filter-rule-operator"
                        class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                    >
                        <option value="contains">Contains</option>
                        <option value="equals">Equals (Exact)</option>
                        <option value="not_equals">Does Not Equal</option>
                        <option value="greater_than">Greater Than (&gt;)</option>
                        <option value="less_than">Less Than (&lt;)</option>
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Value to Match</label>
                    <input
                        type="text"
                        id="filter-rule-value"
                        class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                        placeholder="e.g. Acme or Qualified"
                    >
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-zinc-200 dark:border-zinc-800">
                    <button
                        type="button"
                        onclick="window.closeFilterModal()"
                        class="px-3.5 py-1.5 text-xs font-semibold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-lg transition-colors cursor-pointer"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        onclick="window.addFilterRule()"
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
    $leadsHydrationData = [
        'initialLeads' => $initialLeads ?? [],
        'customFields' => $customFields ?? [],
        'fieldPermissions' => !empty($fieldPermissions) ? $fieldPermissions : (object)[],
        'users' => $users ?? [],
        'stages' => $stages ?? ['New', 'Contacted', 'Qualified', 'Lost'],
        'sources' => $sources ?? [],
        'currentUserProfile' => $currentUserProfile ?? 'Administrator',
    ];
@endphp
<script>
    window.__LEADS_DATA__ = {!! json_encode($leadsHydrationData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};
</script>
<script src="{{ asset('js/leads.js') }}?v=1.0"></script>
@endsection
