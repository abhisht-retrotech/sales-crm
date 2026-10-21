@extends('layouts.app')

@section('title', 'Leads & Intake — DealSync Enterprise CRM')

@section('content')
<style>
    /* Draggable Column Width Resizer with Centered Edge Handle & High-Visibility Cyan Highlight */
    .lead-col-resizer,
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
    .lead-col-resizer::after,
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
    .lead-col-resizer:hover::after,
    .lead-col-resizer.is-resizing::after,
    .table-col-resizer:hover::after,
    .table-col-resizer.is-resizing::after {
        background-color: #06b6d4 !important;
        box-shadow: 0 0 6px #06b6d4;
    }
    body.is-resizing-lead-col,
    body.is-resizing-lead-col * {
        cursor: col-resize !important;
        user-select: none !important;
    }

    /* Kanban Horizontal X-Axis Slider Track & Scrollbar Styling */
    .kanban-slider-viewport {
        overflow-x: auto !important;
        overflow-y: hidden !important;
        scroll-behavior: smooth;
        -webkit-overflow-scrolling: touch;
    }
    .kanban-slider-viewport::-webkit-scrollbar {
        height: 10px;
    }
    .kanban-slider-viewport::-webkit-scrollbar-track {
        background: rgba(226, 232, 240, 0.7);
        border-radius: 9999px;
        margin: 0 4px;
    }
    .dark .kanban-slider-viewport::-webkit-scrollbar-track {
        background: rgba(39, 39, 42, 0.7);
    }
    .kanban-slider-viewport::-webkit-scrollbar-thumb {
        background: #06b6d4;
        border-radius: 9999px;
        border: 2px solid transparent;
        background-clip: content-box;
    }
    .kanban-slider-viewport::-webkit-scrollbar-thumb:hover {
        background: #0891b2;
        background-clip: content-box;
    }
    .kanban-cards-wrapper::-webkit-scrollbar {
        width: 6px;
    }
    .kanban-cards-wrapper::-webkit-scrollbar-track {
        background: transparent;
    }
    .kanban-cards-wrapper::-webkit-scrollbar-thumb {
        background: rgba(148, 163, 184, 0.4);
        border-radius: 9999px;
    }
    .kanban-column {
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    /* Customize Modal Drag and Drop Styles */
    .customize-field-row {
        transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
    }
    .customize-field-row.is-dragging {
        opacity: 0.35;
        transform: scale(0.98);
        border-color: #7c3aed !important;
        background-color: rgba(124, 58, 237, 0.05) !important;
    }
    .customize-field-row.drag-over-top {
        border-top: 2px solid #7c3aed !important;
    }
    .customize-field-row.drag-over-bottom {
        border-bottom: 2px solid #7c3aed !important;
    }
</style>

<!-- Page Root Container (Exact Parity with Target Screenshot) -->
<div id="leads-page-root" class="max-w-[1520px] mx-auto w-full px-4 sm:px-8 py-6 space-y-4 animate-fade-in text-zinc-900 dark:text-zinc-100">

    <!-- 1. Header Section (Exact Match with Target Layout) -->
    <div class="flex items-start justify-between gap-4 pb-1">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-800 dark:text-white">
                Leads &amp; Intake
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-zinc-400 mt-1">
                Capture, score, and qualify inbound leads with instant decoupled conversion into Accounts and Contacts.
            </p>
        </div>
        <button
            type="button"
            onclick="window.openLeadModal('create')"
            class="inline-flex items-center gap-1.5 px-4 py-2 sm:px-5 sm:py-2.5 rounded-xl bg-[#7c3aed] hover:bg-[#6d28d9] text-white text-xs sm:text-sm font-semibold shadow-xs transition-colors cursor-pointer no-underline hover:no-underline shrink-0"
        >
            <span class="text-base leading-none font-bold">+</span>
            <span>Add Lead</span>
        </button>
    </div>

    <!-- 2. Quick Feedback Banner -->
    <div id="leads-feedback-banner" class="hidden p-3 px-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/80 rounded-xl text-emerald-800 dark:text-emerald-300 text-xs font-medium items-center gap-2 shadow-xs transition-all">
        <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0"></i>
        <span id="leads-feedback-message"></span>
    </div>

    <!-- 3. Card Toolbar: Search, Table/Kanban Switcher, Customize, Filter, Bulk Email (Glowing Cyan Border) -->
    <div class="bg-white dark:bg-zinc-900 rounded-2xl p-3 px-4 border border-cyan-400 shadow-[0_0_18px_rgba(56,189,248,0.22)] flex flex-col sm:flex-row items-center justify-between gap-3 transition-all">
        <!-- Search Input -->
        <div class="flex items-center gap-3 w-full sm:w-auto flex-1">
            <div class="relative w-full max-w-[360px]">
                <i data-lucide="search" class="w-3.5 h-3.5 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                <input
                    type="text"
                    id="leads-search-input"
                    placeholder="Search leads by name, company, email..."
                    class="w-full pl-9 pr-3 py-1.5 text-xs rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:outline-none focus:ring-1 focus:ring-cyan-400 h-9"
                >
            </div>
        </div>

        <!-- Controls Cluster -->
        <div class="flex items-center gap-2.5 w-full sm:w-auto justify-end flex-wrap">
            <!-- View Mode Switcher (Table vs Kanban) -->
            <div class="flex items-center p-0.5 rounded-xl bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-xs">
                <button
                    type="button"
                    id="btn-lead-view-table"
                    onclick="window.switchLeadsViewMode('table')"
                    class="flex items-center gap-1.5 px-3 py-1 text-xs font-bold rounded-lg bg-white text-zinc-950 shadow-2xs dark:bg-zinc-700 dark:text-zinc-100 cursor-pointer transition-all no-underline hover:no-underline"
                >
                    <i data-lucide="table" class="w-3.5 h-3.5"></i>
                    <span>Table</span>
                </button>
                <button
                    type="button"
                    id="btn-lead-view-kanban"
                    onclick="window.switchLeadsViewMode('kanban')"
                    class="flex items-center gap-1.5 px-3 py-1 text-xs font-medium rounded-lg text-zinc-500 hover:text-zinc-950 dark:text-zinc-400 dark:hover:text-zinc-100 cursor-pointer transition-all no-underline hover:no-underline"
                >
                    <i data-lucide="kanban" class="w-3.5 h-3.5"></i>
                    <span>Kanban</span>
                </button>
            </div>

            <!-- Customize Visible Fields Button -->
            <button
                type="button"
                id="leads-customize-btn"
                onclick="window.openCustomizeModal()"
                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs h-9 cursor-pointer no-underline hover:no-underline"
                title="Customize Visible Fields"
            >
                <i data-lucide="sliders-horizontal" class="w-3.5 h-3.5 text-zinc-500"></i>
                <span>Customize</span>
            </button>

            <!-- Filter Button with active counter badge -->
            <button
                type="button"
                id="leads-filter-btn"
                onclick="window.openUniversalFilterModal()"
                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs h-9 cursor-pointer no-underline hover:no-underline"
                title="Filter Leads by Field"
            >
                <i data-lucide="filter" class="w-3.5 h-3.5 text-zinc-500"></i>
                <span>Filter</span>
                <span id="leads-filter-badge" class="hidden text-[10px] px-1.5 py-0.2 rounded-full bg-blue-600 text-white font-bold leading-none">0</span>
            </button>

            <!-- Bulk Email Campaign Button -->
            <button
                type="button"
                id="leads-bulk-email-btn"
                onclick="window.openBulkWizardModal(false)"
                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl border border-pink-200 dark:border-pink-900/60 bg-pink-50/70 dark:bg-pink-950/40 text-pink-600 dark:text-pink-300 text-xs font-bold hover:bg-pink-100 dark:hover:bg-pink-900/40 transition-colors shadow-2xs h-9 cursor-pointer no-underline hover:no-underline"
                title="Broadcast bulk email to leads"
            >
                <i data-lucide="megaphone" class="w-3.5 h-3.5 text-pink-500"></i>
                <span>Bulk Email</span>
            </button>
        </div>
    </div>

    <!-- 4. Selected Leads Action Banner (Matching React LeadsView.tsx lines 353-398) -->
    <div id="leads-selected-action-banner" class="hidden items-center justify-between p-2 px-4 rounded-xl bg-pink-50 dark:bg-pink-950/40 border border-pink-200 dark:border-pink-900/60 shadow-xs transition-all">
        <div class="flex items-center gap-2 text-xs font-semibold text-pink-900 dark:text-pink-200">
            <i data-lucide="megaphone" class="w-4 h-4 text-pink-600 dark:text-pink-400"></i>
            <span id="leads-selected-count-label">0 leads selected</span>
        </div>
        <div class="flex items-center gap-2">
            <button
                type="button"
                onclick="window.deselectAllLeads()"
                class="px-2.5 py-1 text-xs font-medium text-pink-700 hover:text-pink-900 dark:text-pink-300 dark:hover:text-pink-100 cursor-pointer no-underline hover:no-underline"
            >
                Deselect
            </button>
            <button
                type="button"
                onclick="window.openBulkWizardModal(true)"
                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-semibold text-white shadow-xs transition-colors cursor-pointer bg-gradient-to-r from-pink-600 to-rose-600 hover:from-pink-700 hover:to-rose-700 no-underline hover:no-underline"
            >
                <i data-lucide="mail" class="w-3.5 h-3.5"></i>
                <span id="leads-bulk-send-label">Send Bulk Email (0)</span>
            </button>
        </div>
    </div>

    <!-- 5. Active Filter Chips Bar (Matching React LeadsView.tsx lines 400-406 & ActiveFilterChips) -->
    <div id="leads-active-filter-chips-container" class="hidden flex items-center gap-2 flex-wrap text-xs pt-0.5">
        <!-- Rendered dynamically -->
    </div>

    <!-- Loading State Indicator -->
    <div id="leads-loading-indicator" class="hidden py-10 text-center">
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-xs text-zinc-600 dark:text-zinc-400 font-medium">
            <i data-lucide="loader-2" class="w-4 h-4 animate-spin text-zinc-900 dark:text-zinc-100"></i>
            <span>Loading leads repository...</span>
        </div>
    </div>

    <!-- 6. Main Views: Table vs Kanban (Matching React LeadsView.tsx lines 408-438) -->
    <div id="leads-table-container">
        <!-- CompactDataTable populated by leads.js -->
    </div>

    <div id="leads-kanban-container" class="hidden">
        <!-- UniversalKanban populated by leads.js -->
    </div>

    <!-- ==================== MODALS ==================== -->

    <!-- Modal 1: Record Detail Modal (Matching React RecordDetailModal on row or kanban card click) -->
    <div id="recordDetailModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-zinc-950/60 backdrop-blur-xs overflow-y-auto">
        <div class="relative w-full max-w-2xl bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xl overflow-hidden my-8">
            <!-- Modal Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/70 dark:bg-zinc-800/40">
                <div class="flex items-center gap-2.5">
                    <div id="recordDetailAvatar" class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 font-bold text-xs flex items-center justify-center shrink-0">
                        L
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 id="recordDetailTitle" class="text-sm font-bold text-zinc-950 dark:text-zinc-100 leading-tight">Lead Detail</h3>
                            <span id="recordDetailStatusBadge" class="text-[11px] px-2 py-0.5 rounded-full font-semibold">New</span>
                            <span id="recordDetailScoreBadge" class="text-[11px] px-2 py-0.5 rounded-full font-bold">50</span>
                        </div>
                        <p id="recordDetailSubtitle" class="text-xs text-zinc-500 dark:text-zinc-400">Company Name</p>
                    </div>
                </div>
                <button
                    type="button"
                    onclick="window.closeRecordDetailModal()"
                    class="p-1 rounded-lg text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
                >
                    ✕
                </button>
            </div>

            <!-- Modal Body: Tabs / Fields -->
            <div class="p-6 space-y-5 max-h-[72vh] overflow-y-auto" id="recordDetailBody">
                <!-- Populated dynamically by leads.js -->
            </div>

            <!-- Modal Footer: Quick Actions (Convert, Transfer, Edit, Delete, Close) -->
            <div class="flex items-center justify-between px-6 py-3.5 border-t border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-800/30">
                <div class="flex items-center gap-2" id="recordDetailActionLeft">
                    <!-- Dynamic Left Actions -->
                </div>
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        onclick="window.closeRecordDetailModal()"
                        class="px-3.5 py-1.5 text-xs font-semibold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-lg transition-colors cursor-pointer no-underline hover:no-underline"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal 2: Add / Edit Lead Record Modal (Matching React NewRecordModal / leadFormModal) -->
    <div id="leadFormModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-zinc-950/60 backdrop-blur-xs overflow-y-auto">
        <div class="relative w-full max-w-2xl bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xl overflow-hidden my-8">
            <div class="flex items-center justify-between px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/60 dark:bg-zinc-800/40">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 flex items-center justify-center">
                        <i data-lucide="user-plus" class="w-4 h-4"></i>
                    </div>
                    <h3 id="leadFormModalTitle" class="text-sm font-bold text-zinc-950 dark:text-zinc-100">Add Lead</h3>
                </div>
                <button
                    type="button"
                    onclick="window.closeLeadModal()"
                    class="p-1 rounded-lg text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
                >
                    ✕
                </button>
            </div>

            <form id="leadRecordForm" class="p-6 space-y-5 max-h-[76vh] overflow-y-auto">
                <input type="hidden" id="lead-input-id" name="id">

                <!-- Core Details -->
                <div>
                    <h4 class="text-xs font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider mb-3 pb-1 border-b border-zinc-200 dark:border-zinc-800 flex items-center gap-1.5">
                        <i data-lucide="info" class="w-3.5 h-3.5 text-zinc-500"></i>
                        <span>Core Lead Identification</span>
                    </h4>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                First Name <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="lead-input-first_name"
                                name="first_name"
                                required
                                class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-1 focus:ring-zinc-400 focus:outline-none"
                                placeholder="e.g. Sarah"
                            >
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                Last Name <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="lead-input-last_name"
                                name="last_name"
                                required
                                class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-1 focus:ring-zinc-400 focus:outline-none"
                                placeholder="e.g. Jenkins"
                            >
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                Company Name <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="lead-input-company"
                                name="company"
                                required
                                class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-1 focus:ring-zinc-400 focus:outline-none"
                                placeholder="e.g. Acme Corporation"
                            >
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Job Title</label>
                            <input
                                type="text"
                                id="lead-input-title"
                                name="title"
                                class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-1 focus:ring-zinc-400 focus:outline-none"
                                placeholder="e.g. VP of Enterprise Technology"
                            >
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Email Address</label>
                            <input
                                type="email"
                                id="lead-input-email"
                                name="email"
                                class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-1 focus:ring-zinc-400 focus:outline-none"
                                placeholder="s.jenkins@acmecorp.io"
                            >
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Phone Number</label>
                            <input
                                type="text"
                                id="lead-input-phone"
                                name="phone"
                                class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-1 focus:ring-zinc-400 focus:outline-none"
                                placeholder="+1 (512) 890-4412"
                            >
                        </div>
                    </div>
                </div>

                <!-- Status & Qualification -->
                <div>
                    <h4 class="text-xs font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider mb-3 pb-1 border-b border-zinc-200 dark:border-zinc-800 flex items-center gap-1.5">
                        <i data-lucide="target" class="w-3.5 h-3.5 text-zinc-500"></i>
                        <span>Status &amp; Qualification</span>
                    </h4>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Status Stage</label>
                            <select
                                id="lead-input-status"
                                name="status"
                                class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-1 focus:ring-zinc-400 focus:outline-none"
                            >
                                <option value="New">New</option>
                                <option value="Contacted">Contacted</option>
                                <option value="Qualified">Qualified</option>
                                <option value="Lost">Lost</option>
                            </select>
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Lead Source</label>
                            <select
                                id="lead-input-source"
                                name="source"
                                class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-1 focus:ring-zinc-400 focus:outline-none"
                            >
                                @foreach($sources ?? [] as $src)
                                    <option value="{{ $src }}">{{ $src }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Lead Propensity Score (0–100)</label>
                            <input
                                type="number"
                                id="lead-input-score"
                                name="score"
                                min="0"
                                max="100"
                                class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-1 focus:ring-zinc-400 focus:outline-none"
                                placeholder="50"
                            >
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Priority Rating</label>
                            <select
                                id="lead-input-rating"
                                name="rating"
                                class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-1 focus:ring-zinc-400 focus:outline-none"
                            >
                                <option value="Hot">Hot (Immediate follow-up)</option>
                                <option value="Warm">Warm (Nurture in progress)</option>
                                <option value="Cold">Cold (Low responsiveness)</option>
                            </select>
                        </div>

                        <div class="space-y-1 col-span-full">
                            <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Assigned Owner</label>
                            <select
                                id="lead-input-assigned_to"
                                name="assigned_to"
                                class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-1 focus:ring-zinc-400 focus:outline-none"
                            >
                                <option value="">Unassigned</option>
                                @foreach($users ?? [] as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-1 col-span-full">
                            <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Qualification Notes</label>
                            <textarea
                                id="lead-input-notes"
                                name="notes"
                                rows="2"
                                class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-1 focus:ring-zinc-400 focus:outline-none"
                                placeholder="Enter context or customer requirements..."
                            ></textarea>
                        </div>
                    </div>
                </div>

                <!-- Dynamic Custom Fields -->
                <div id="lead-dynamic-custom-fields-wrapper" class="hidden">
                    <h4 class="text-xs font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider mb-3 pb-1 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between">
                        <div class="flex items-center gap-1.5">
                            <i data-lucide="layers" class="w-3.5 h-3.5 text-zinc-500"></i>
                            <span>Custom Attributes</span>
                        </div>
                        <span class="text-[10px] text-zinc-400 font-normal">Permissions Enforced</span>
                    </h4>

                    <div id="lead-dynamic-custom-fields-container" class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <!-- Populated dynamically -->
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                    <button
                        type="button"
                        onclick="window.closeLeadModal()"
                        class="px-3.5 py-1.5 text-xs font-semibold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-lg transition-colors cursor-pointer no-underline hover:no-underline"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="px-4 py-1.5 text-xs font-semibold text-white bg-zinc-900 hover:bg-zinc-800 dark:bg-zinc-100 dark:hover:bg-white dark:text-zinc-950 rounded-lg shadow-xs transition-colors cursor-pointer no-underline hover:no-underline"
                    >
                        Save Lead
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 3: Decoupled 3-Way Atomic Lead Conversion Modal (Matching React LeadsView.tsx lines 486-553) -->
    <div id="convertLeadModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-zinc-950/60 backdrop-blur-xs overflow-y-auto">
        <div class="relative w-full max-w-lg bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xl overflow-hidden my-8">
            <div class="flex items-center justify-between px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/70 dark:bg-zinc-800/40">
                <div class="flex items-center gap-2">
                    <i data-lucide="arrow-right-left" class="w-4 h-4 text-zinc-900 dark:text-zinc-100"></i>
                    <h3 id="convertLeadModalTitle" class="text-sm font-bold text-zinc-950 dark:text-zinc-100">Convert Lead</h3>
                </div>
                <button
                    type="button"
                    onclick="window.closeConvertModal()"
                    class="p-1 rounded-lg text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
                >
                    ✕
                </button>
            </div>

            <form id="convertLeadForm" class="p-6 space-y-4">
                <input type="hidden" id="convert-lead-id">

                <div class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200 dark:border-zinc-700/60 text-xs space-y-2">
                    <p class="font-medium text-zinc-700 dark:text-zinc-300">
                        Converting this lead generates the following core CRM records:
                    </p>
                    <ul class="space-y-1.5 pl-4 list-disc text-zinc-600 dark:text-zinc-400">
                        <li>
                            <strong class="text-zinc-950 dark:text-zinc-100">New Account:</strong>
                            <span id="convert-preview-account" class="ml-1 text-zinc-800 dark:text-zinc-200 font-medium">Acme Corporation (Customer)</span>
                        </li>
                        <li>
                            <strong class="text-zinc-950 dark:text-zinc-100">New Contact:</strong>
                            <span id="convert-preview-contact" class="ml-1 text-zinc-800 dark:text-zinc-200 font-medium">Sarah Jenkins (s.jenkins@acmecorp.io)</span>
                        </li>
                    </ul>
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Initial Qualification Notes</label>
                    <textarea
                        id="convert-input-notes"
                        rows="3"
                        class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-1 focus:ring-zinc-400 focus:outline-none"
                        placeholder="Enter context or customer requirements for the Account team..."
                    ></textarea>
                </div>

                <!-- Optional Opportunity -->
                <div class="p-3.5 rounded-xl border border-zinc-200 dark:border-zinc-700/60 bg-zinc-50/50 dark:bg-zinc-800/30 space-y-2.5">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" id="convert-chk-create-opp" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 cursor-pointer">
                        <span class="text-xs font-semibold text-zinc-800 dark:text-zinc-200">Also create new Opportunity for this Account</span>
                    </label>

                    <div id="convert-opp-fields" class="space-y-2 pt-2 border-t border-zinc-200 dark:border-zinc-700/40 hidden">
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

                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-zinc-200 dark:border-zinc-800">
                    <button
                        type="button"
                        onclick="window.closeConvertModal()"
                        class="px-3.5 py-1.5 text-xs font-semibold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-lg transition-colors cursor-pointer no-underline hover:no-underline"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        id="convert-submit-btn"
                        class="px-4 py-1.5 text-xs font-semibold text-white bg-zinc-900 hover:bg-zinc-800 dark:bg-zinc-100 dark:hover:bg-white dark:text-zinc-950 rounded-lg shadow-xs transition-colors cursor-pointer no-underline hover:no-underline"
                    >
                        Confirm Conversion
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 4: Lead Ownership Transfer Modal (Matching React RecordTransferModal.tsx) -->
    <div id="transferLeadModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-zinc-950/60 backdrop-blur-xs overflow-y-auto">
        <div class="relative w-full max-w-md bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xl overflow-hidden my-8">
            <div class="flex items-center justify-between px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/70 dark:bg-zinc-800/40">
                <div class="flex items-center gap-2">
                    <i data-lucide="user-check" class="w-4 h-4 text-zinc-900 dark:text-zinc-100"></i>
                    <h3 id="transferLeadTitle" class="text-sm font-bold text-zinc-950 dark:text-zinc-100">Transfer Ownership</h3>
                </div>
                <button
                    type="button"
                    onclick="window.closeTransferModal()"
                    class="p-1 rounded-lg text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
                >
                    ✕
                </button>
            </div>

            <form id="transferLeadForm" class="p-6 space-y-4">
                <input type="hidden" id="transfer-lead-id">

                <div class="text-xs text-zinc-600 dark:text-zinc-400">
                    Current Assigned Owner: <strong id="transferCurrentOwner" class="text-zinc-900 dark:text-zinc-100">Unassigned</strong>
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Select New Owner</label>
                    <select
                        id="transferTargetUser"
                        required
                        class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-1 focus:ring-zinc-400 focus:outline-none"
                    >
                        <!-- Populated dynamically -->
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Transfer Note (Optional)</label>
                    <textarea
                        id="transferNote"
                        rows="2"
                        class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-1 focus:ring-zinc-400 focus:outline-none"
                        placeholder="Reason for transferring ownership..."
                    ></textarea>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-zinc-200 dark:border-zinc-800">
                    <button
                        type="button"
                        onclick="window.closeTransferModal()"
                        class="px-3.5 py-1.5 text-xs font-semibold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-lg transition-colors cursor-pointer no-underline hover:no-underline"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="px-4 py-1.5 text-xs font-semibold text-white bg-zinc-900 hover:bg-zinc-800 dark:bg-zinc-100 dark:hover:bg-white dark:text-zinc-950 rounded-lg shadow-xs transition-colors cursor-pointer no-underline hover:no-underline"
                    >
                        Confirm Transfer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 5: Customize Table Fields & Order Modal (Exact 1:1 Parity with Target Screenshot) -->
    <div id="viewConfigModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-zinc-950/60 backdrop-blur-xs overflow-y-auto">
        <div class="relative w-full max-w-2xl bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-2xl overflow-hidden my-8 animate-scale-in">
            <!-- Modal Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-zinc-800 bg-white dark:bg-zinc-900">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-800 text-slate-700 dark:text-zinc-200 flex items-center justify-center shrink-0 shadow-2xs">
                        <svg class="w-5 h-5 text-slate-600 dark:text-zinc-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="4" y1="21" x2="4" y2="14"></line>
                            <line x1="4" y1="10" x2="4" y2="3"></line>
                            <line x1="12" y1="21" x2="12" y2="12"></line>
                            <line x1="12" y1="8" x2="12" y2="3"></line>
                            <line x1="20" y1="21" x2="20" y2="16"></line>
                            <line x1="20" y1="12" x2="20" y2="3"></line>
                            <line x1="1" y1="14" x2="7" y2="14"></line>
                            <line x1="9" y1="8" x2="15" y2="8"></line>
                            <line x1="17" y1="16" x2="23" y2="16"></line>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-zinc-100 leading-tight">Customize Table Fields &amp; Order</h3>
                        <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">Drag rows or click ↑ / ↓ to rearrange column sequence from left to right.</p>
                    </div>
                </div>
                <button
                    type="button"
                    onclick="window.closeCustomizeModal()"
                    class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-zinc-200 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
                >
                    ✕
                </button>
            </div>

            <div class="p-5 sm:p-6 space-y-3.5">
                <!-- Search & Quick Selection Toolbar -->
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 pb-1">
                    <div class="relative flex-1 max-w-xs">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <input
                            type="text"
                            id="customize-fields-search-input"
                            placeholder="Find field by name..."
                            class="w-full pl-9 pr-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-800 dark:text-zinc-100 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-purple-500 h-9"
                        >
                    </div>
                    <div class="flex items-center gap-3 self-end sm:self-auto text-xs font-semibold">
                        <button
                            type="button"
                            id="customize-select-all-btn"
                            class="text-slate-600 hover:text-slate-900 dark:text-zinc-400 dark:hover:text-zinc-100 cursor-pointer transition-colors"
                        >
                            Select All
                        </button>
                        <button
                            type="button"
                            id="customize-deselect-all-btn"
                            class="text-slate-600 hover:text-slate-900 dark:text-zinc-400 dark:hover:text-zinc-100 cursor-pointer transition-colors"
                        >
                            Deselect All
                        </button>
                        <button
                            type="button"
                            id="customize-reset-defaults-btn"
                            onclick="window.resetViewConfigDefaults()"
                            class="inline-flex items-center gap-1 text-slate-600 hover:text-slate-900 dark:text-zinc-400 dark:hover:text-zinc-100 cursor-pointer transition-colors"
                        >
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                            <span>Reset</span>
                        </button>
                    </div>
                </div>

                <!-- Info Guide Banner -->
                <div class="flex items-center gap-2 p-2.5 px-3.5 rounded-xl bg-blue-50/70 dark:bg-blue-950/40 border border-blue-100 dark:border-blue-900/50 text-xs text-blue-800 dark:text-blue-300">
                    <svg class="w-4 h-4 text-blue-500 shrink-0" viewBox="0 0 24 24" fill="currentColor"><circle cx="9" cy="5" r="1.5"/><circle cx="9" cy="12" r="1.5"/><circle cx="9" cy="19" r="1.5"/><circle cx="15" cy="5" r="1.5"/><circle cx="15" cy="12" r="1.5"/><circle cx="15" cy="19" r="1.5"/></svg>
                    <span><strong>Drag any row up or down</strong> using the handle or click the arrow buttons (↑ / ↓) to position columns.</span>
                </div>

                <!-- Scrollable Field List with Drag-and-Drop Rows -->
                <div id="view-config-fields-list" class="space-y-2.5 max-h-[380px] overflow-y-auto pr-1">
                    <!-- Populated dynamically by leads.js with exact layout -->
                </div>

                <!-- Modal Footer -->
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-3 border-t border-slate-100 dark:border-zinc-800">
                    <div class="text-xs text-slate-500 dark:text-zinc-400">
                        Showing <strong id="customize-checked-count" class="font-bold text-slate-900 dark:text-zinc-100">4</strong> of <span id="customize-total-count">15</span> columns in table
                    </div>
                    <div class="flex items-center gap-2.5 w-full sm:w-auto justify-end">
                        <button
                            type="button"
                            onclick="window.closeCustomizeModal()"
                            class="px-4 py-2 text-xs font-semibold text-slate-700 dark:text-zinc-300 bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 hover:bg-slate-50 dark:hover:bg-zinc-700 rounded-xl transition-colors cursor-pointer shadow-2xs"
                        >
                            Cancel
                        </button>
                        <button
                            type="button"
                            onclick="window.saveViewConfig()"
                            class="px-5 py-2 text-xs font-bold text-white bg-[#7c3aed] hover:bg-[#6d28d9] rounded-xl shadow-xs transition-colors cursor-pointer"
                        >
                            Apply &amp; Save View
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal 6: Universal Filter Modal (Matching React UniversalFilterModal.tsx) -->
    <div id="universalFilterModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-zinc-950/60 backdrop-blur-xs overflow-y-auto">
        <div class="relative w-full max-w-xl bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xl overflow-hidden my-8">
            <div class="flex items-center justify-between px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/70 dark:bg-zinc-800/40">
                <div class="flex items-center gap-2">
                    <i data-lucide="filter" class="w-4 h-4 text-zinc-900 dark:text-zinc-100"></i>
                    <h3 class="text-sm font-bold text-zinc-950 dark:text-zinc-100">Filter Leads</h3>
                </div>
                <button
                    type="button"
                    onclick="window.closeUniversalFilterModal()"
                    class="p-1 rounded-lg text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
                >
                    ✕
                </button>
            </div>

            <div class="p-6 space-y-4">
                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                    Define filter conditions to match against leads:
                </p>

                <!-- Rules List -->
                <div id="filter-rules-container" class="space-y-2.5 max-h-72 overflow-y-auto pr-1">
                    <!-- Populated dynamically -->
                </div>

                <!-- Add Rule Button -->
                <button
                    type="button"
                    onclick="window.addFilterRuleRow()"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-dashed border-zinc-300 dark:border-zinc-700 text-xs font-semibold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors cursor-pointer w-full justify-center no-underline hover:no-underline"
                >
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>Add Filter Rule</span>
                </button>

                <!-- Modal Footer -->
                <div class="flex items-center justify-between pt-3 border-t border-zinc-200 dark:border-zinc-800">
                    <button
                        type="button"
                        onclick="window.clearAllFilters()"
                        class="text-xs font-semibold text-rose-600 hover:text-rose-700 dark:text-rose-400 cursor-pointer no-underline hover:no-underline"
                    >
                        Clear All Filters
                    </button>
                    <div class="flex items-center gap-2.5">
                        <button
                            type="button"
                            onclick="window.closeUniversalFilterModal()"
                            class="px-3.5 py-1.5 text-xs font-semibold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-lg transition-colors cursor-pointer no-underline hover:no-underline"
                        >
                            Cancel
                        </button>
                        <button
                            type="button"
                            onclick="window.applyFilterRules()"
                            class="px-4 py-1.5 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-xs transition-colors cursor-pointer no-underline hover:no-underline"
                        >
                            Apply Filters
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal 7: Bulk Email Wizard Modal (Matching React BulkEmailWizardModal.tsx) -->
    <div id="bulkEmailWizardModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-zinc-950/60 backdrop-blur-xs overflow-y-auto">
        <div class="relative w-full max-w-xl bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xl overflow-hidden my-8">
            <div class="flex items-center justify-between px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 bg-pink-50/50 dark:bg-pink-950/30">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-pink-100 dark:bg-pink-950/60 text-pink-600 dark:text-pink-400 flex items-center justify-center">
                        <i data-lucide="megaphone" class="w-4 h-4"></i>
                    </div>
                    <h3 class="text-sm font-bold text-zinc-950 dark:text-zinc-100">Broadcast Bulk Email Campaign</h3>
                </div>
                <button
                    type="button"
                    onclick="window.closeBulkWizardModal()"
                    class="p-1 rounded-lg text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
                >
                    ✕
                </button>
            </div>

            <form id="bulkEmailForm" class="p-6 space-y-4">
                <div class="p-3.5 rounded-xl bg-pink-50/60 dark:bg-pink-950/30 border border-pink-200 dark:border-pink-900/60 text-xs flex items-center justify-between">
                    <div class="flex items-center gap-2 text-pink-900 dark:text-pink-200 font-semibold">
                        <i data-lucide="users" class="w-4 h-4 text-pink-600"></i>
                        <span id="bulk-wizard-recipient-count">0 Target Recipients Selected</span>
                    </div>
                    <span class="text-[11px] text-pink-600 dark:text-pink-400 font-medium">Verified Compliance</span>
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Campaign Subject Line <span class="text-rose-500">*</span></label>
                    <input
                        type="text"
                        id="bulk-email-subject"
                        required
                        class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-1 focus:ring-pink-500 focus:outline-none"
                        placeholder="e.g. Special Invitation: DealSync Enterprise Product Roadmap & Q4 Demo"
                    >
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Email Message Body <span class="text-rose-500">*</span></label>
                    <textarea
                        id="bulk-email-body"
                        rows="5"
                        required
                        class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-1 focus:ring-pink-500 focus:outline-none"
                        placeholder="Hi @{{first_name}},&#10;&#10;We wanted to follow up regarding your enterprise requirements..."
                    ></textarea>
                </div>

                <div class="p-3 rounded-lg bg-zinc-50 dark:bg-zinc-800/40 text-[11px] text-zinc-500 dark:text-zinc-400 space-y-1">
                    <div class="font-semibold text-zinc-700 dark:text-zinc-300">Available Merge Tags:</div>
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <code class="px-1.5 py-0.5 rounded bg-zinc-200 dark:bg-zinc-700 text-zinc-800 dark:text-zinc-200">@{{first_name}}</code>
                        <code class="px-1.5 py-0.5 rounded bg-zinc-200 dark:bg-zinc-700 text-zinc-800 dark:text-zinc-200">@{{last_name}}</code>
                        <code class="px-1.5 py-0.5 rounded bg-zinc-200 dark:bg-zinc-700 text-zinc-800 dark:text-zinc-200">@{{company}}</code>
                        <code class="px-1.5 py-0.5 rounded bg-zinc-200 dark:bg-zinc-700 text-zinc-800 dark:text-zinc-200">@{{title}}</code>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-zinc-200 dark:border-zinc-800">
                    <button
                        type="button"
                        onclick="window.closeBulkWizardModal()"
                        class="px-3.5 py-1.5 text-xs font-semibold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-lg transition-colors cursor-pointer no-underline hover:no-underline"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="inline-flex items-center gap-1.5 px-4 py-1.5 text-xs font-semibold text-white rounded-lg shadow-xs transition-colors cursor-pointer bg-gradient-to-r from-pink-600 to-rose-600 hover:from-pink-700 hover:to-rose-700 no-underline hover:no-underline"
                    >
                        <i data-lucide="send" class="w-3.5 h-3.5"></i>
                        <span>Broadcast Campaign</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 7: Fullscreen Lead 360 Detail Modal (Completely covers header, footer, and sidebar) -->
    <div id="leadDetailFullscreenModal" class="fixed inset-0 z-[99999] hidden bg-[#f8fafc] dark:bg-zinc-950 overflow-y-auto w-screen h-screen">
        <div id="leadDetailFullscreenModalBody" class="w-full min-h-screen">
            <!-- Dynamically populated via fetch when clicking any lead record -->
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
        'canSendBulkEmail' => $canSendBulkEmail ?? true,
        'colWidths' => $colWidths ?? [],
        'tableVisibleFields' => $tableVisibleFields ?? [],
        'kanbanVisibleFields' => $kanbanVisibleFields ?? [],
    ];
@endphp
<script>
    window.__LEADS_DATA__ = {!! json_encode($leadsHydrationData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};
</script>
<script src="{{ asset('js/leads.js') }}?v=2.2"></script>
@endsection
