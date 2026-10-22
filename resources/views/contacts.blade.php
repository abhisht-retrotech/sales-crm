@extends('layouts.app')

@section('title', 'Contacts & Stakeholders — DealSync Enterprise CRM')

@section('content')
<style>
    /* Draggable Column Width Resizer with Centered Edge Handle & High-Visibility Orange Highlight */
    .contact-col-resizer,
    .table-col-resizer {
        position: absolute !important;
        top: 0 !important;
        right: -6px !important;
        width: 12px !important;
        height: 100% !important;
        cursor: col-resize !important;
        user-select: none !important;
        z-index: 50 !important;
        background: transparent !important;
    }
    .contact-col-resizer::after,
    .table-col-resizer::after {
        content: '';
        position: absolute;
        top: 0;
        right: 5px;
        width: 2px;
        height: 100%;
        background-color: transparent;
        transition: background-color 0.15s ease;
    }
    .contact-col-resizer:hover::after,
    .contact-col-resizer.is-resizing::after,
    .table-col-resizer:hover::after,
    .table-col-resizer.is-resizing::after {
        background-color: #f97316 !important;
        box-shadow: 0 0 8px rgba(249, 115, 22, 0.85);
    }
    body.is-resizing-contact-col,
    body.is-resizing-contact-col * {
        cursor: col-resize !important;
        user-select: none !important;
    }

    /* Company Profile Dynamic Theme Integration for Contacts View */
    .contacts-themed-card {
        background-color: var(--table-bg, #ffffff) !important;
        color: var(--text-primary, #09090b) !important;
    }
    .contacts-themed-table,
    #contacts-data-table {
        background-color: var(--table-bg, #ffffff) !important;
        color: var(--text-primary, #09090b) !important;
    }
    #contacts-data-table thead,
    #contacts-data-table thead tr,
    #contacts-data-table thead th {
        background-color: var(--table-bg, #ffffff) !important;
        color: var(--text-primary, #09090b) !important;
        border-color: var(--table-border, #e2e8f0) !important;
    }
    #contacts-data-table tbody,
    #contacts-data-table tbody tr,
    #contacts-data-table tbody td {
        background-color: var(--table-bg, #ffffff) !important;
        color: var(--text-primary, #09090b) !important;
        border-color: var(--table-border, #e2e8f0) !important;
    }
    #contacts-data-table tbody tr:hover,
    #contacts-data-table tbody tr:hover td {
        filter: brightness(0.96) !important;
    }
    [data-theme="dark"] #contacts-data-table tbody tr:hover,
    [data-theme="dark"] #contacts-data-table tbody tr:hover td {
        filter: brightness(1.08) !important;
    }
    .contacts-footer-themed {
        background-color: var(--table-bg, #ffffff) !important;
        color: var(--text-primary, #09090b) !important;
        border-color: var(--table-border, #e2e8f0) !important;
    }
    .contacts-text-primary {
        color: var(--text-primary, #09090b) !important;
    }
</style>

<!-- Main Container matching screenshot layout -->
<div id="contacts-page-root" class="max-w-[1360px] mx-auto w-full px-4 sm:px-8 py-6 space-y-4 animate-fade-in">
    <!-- Top Action Header matching user screenshot -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-2">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-[#2563eb] dark:text-sky-400">
                Contacts &amp; Stakeholders
            </h1>
            <p class="text-sm text-slate-500 dark:text-zinc-400 mt-1 font-normal">
                Individual stakeholders across customer accounts with multi-contact relationship mapping.
            </p>
        </div>

        <div>
            <!-- Add Contact Primary Action -->
            <button
                type="button"
                onclick="window.openContactModal('create')"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-[#7e47a7] hover:bg-[#6c3992] text-white text-xs font-semibold shadow-sm transition-colors cursor-pointer"
            >
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <span>Add Contact</span>
            </button>
        </div>
    </div>

    <!-- 1. SEPARATE TOOLBAR CARD (With High-Visibility Orange Border & Shadow & Company Profile Theme) -->
    <div id="contacts-toolbar-card" class="contacts-themed-card rounded-2xl border border-orange-200/90 dark:border-orange-500/30 shadow-[0_4px_20px_-2px_rgba(251,146,60,0.12)] p-4 flex flex-col sm:flex-row items-center justify-between gap-4 mb-4" style="background-color: var(--table-bg, #ffffff); color: var(--text-primary, #09090b);">
        <!-- Left: Search Box -->
        <div class="relative w-full sm:w-80 md:w-96">
            <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input
                type="text"
                id="contacts-search-input"
                placeholder="Search contacts by name, email, account..."
                class="w-full pl-10 pr-3.5 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-indigo-500 transition-all shadow-2xs"
                style="color: var(--text-primary, #09090b);"
            >
        </div>

        <!-- Right: Action Buttons -->
        <div class="flex items-center gap-2 w-full sm:w-auto justify-end flex-wrap">
            <!-- View Mode Switcher -->
            <div class="flex items-center p-0.5 rounded-lg border border-slate-200 dark:border-zinc-700 bg-slate-100 dark:bg-zinc-800 text-xs">
                <button
                    type="button"
                    id="btn-contact-view-table"
                    onclick="window.switchContactsViewMode('table')"
                    class="flex items-center gap-1.5 px-3 py-1 text-xs font-semibold rounded-md bg-white dark:bg-zinc-700 text-slate-900 dark:text-zinc-100 shadow-2xs cursor-pointer"
                >
                    <svg class="w-3.5 h-3.5 text-slate-600 dark:text-zinc-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect width="18" height="18" x="3" y="3" rx="2"/><path d="M3 9h18"/><path d="M3 15h18"/><path d="M9 3v18"/><path d="M15 3v18"/>
                    </svg>
                    <span>Table</span>
                </button>
                <button
                    type="button"
                    id="btn-contact-view-kanban"
                    onclick="window.switchContactsViewMode('kanban')"
                    class="flex items-center gap-1.5 px-3 py-1 text-xs font-medium rounded-md text-slate-500 hover:text-slate-900 dark:hover:text-zinc-200 cursor-pointer"
                >
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect width="6" height="14" x="4" y="5" rx="1"/><rect width="6" height="10" x="14" y="5" rx="1"/>
                    </svg>
                    <span>Kanban</span>
                </button>
            </div>

            <!-- Customize Button -->
            <button
                type="button"
                onclick="window.openCustomizeFieldsModal()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-slate-700 dark:text-zinc-200 hover:bg-slate-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs cursor-pointer"
                title="Customize Visible Columns"
            >
                <svg class="w-3.5 h-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="4" y1="21" x2="4" y2="14"></line><line x1="4" y1="10" x2="4" y2="3"></line>
                    <line x1="12" y1="21" x2="12" y2="12"></line><line x1="12" y1="8" x2="12" y2="3"></line>
                    <line x1="20" y1="21" x2="20" y2="16"></line><line x1="20" y1="12" x2="20" y2="3"></line>
                    <line x1="1" y1="14" x2="7" y2="14"></line><line x1="9" y1="8" x2="15" y2="8"></line><line x1="17" y1="16" x2="23" y2="16"></line>
                </svg>
                <span>Customize</span>
            </button>

            <!-- Filter Button -->
            <button
                type="button"
                onclick="window.openContactFilterModal()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-slate-700 dark:text-zinc-200 hover:bg-slate-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs cursor-pointer"
            >
                <svg class="w-3.5 h-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                </svg>
                <span>Filter</span>
                <span id="contacts-filter-badge" class="hidden px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-blue-600 text-white">0</span>
            </button>

            <!-- Bulk Email Button matching screenshot -->
            <button
                type="button"
                onclick="window.triggerBulkEmail()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-pink-200 dark:border-pink-900/60 bg-pink-50 dark:bg-pink-950/30 text-pink-700 dark:text-pink-300 hover:bg-pink-100 transition-colors text-xs font-semibold shadow-2xs cursor-pointer"
            >
                <svg class="w-3.5 h-3.5 text-pink-600 dark:text-pink-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="m3 11 18-5v12L3 14v-3z"></path>
                    <path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"></path>
                </svg>
                <span>Bulk Email</span>
            </button>
        </div>
    </div>

    <!-- Active Filter Chips Bar -->
    <div id="contacts-filter-chips-bar" class="hidden mb-3"></div>

    <!-- 2. SEPARATE VIEW CONTAINER 1: Table View (Renders separate card) -->
    <div id="contacts-table-view-root">
        <!-- Rendered dynamically by contacts.js -->
    </div>

    <!-- 3. SEPARATE VIEW CONTAINER 2: Kanban View -->
    <div id="contacts-kanban-view-root" class="hidden">
        <!-- Rendered dynamically by contacts.js -->
    </div>

    <!-- ========================================== -->
    <!-- MODAL 1: ADD / EDIT CONTACT MODAL          -->
    <!-- ========================================== -->
    <div id="contact-form-modal" class="fixed inset-0 z-50 hidden bg-zinc-950/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
        <div class="bg-white dark:bg-zinc-900 rounded-2xl max-w-2xl w-full border border-zinc-200 dark:border-zinc-800 shadow-2xl overflow-hidden animate-scale-up my-8">
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between bg-zinc-50/50 dark:bg-zinc-800/30">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </div>
                    <div>
                        <h3 id="modal-contact-form-title" class="text-sm font-bold text-zinc-950 dark:text-zinc-100">
                            Add New Stakeholder Contact
                        </h3>
                        <p class="text-[11px] text-zinc-500">Key organization stakeholder with buying influence role</p>
                    </div>
                </div>
                <button
                    type="button"
                    onclick="window.closeContactModal()"
                    class="p-1 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-400 hover:text-zinc-700 cursor-pointer"
                >
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <form id="contact-record-form" onsubmit="window.handleContactFormSubmit(event)" class="p-6 space-y-5 max-h-[80vh] overflow-y-auto">
                <!-- Section 1: Contact Identity -->
                <div>
                    <h4 class="text-xs font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider mb-3 pb-1 border-b border-zinc-100 dark:border-zinc-800 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        <span>Individual Information</span>
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        <div class="space-y-1">
                            <label class="font-semibold text-zinc-700 dark:text-zinc-300">
                                First Name <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                name="first_name"
                                id="contact-field-first_name"
                                required
                                placeholder="e.g. Elena"
                                class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/60 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            >
                        </div>

                        <div class="space-y-1">
                            <label class="font-semibold text-zinc-700 dark:text-zinc-300">
                                Last Name <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                name="last_name"
                                id="contact-field-last_name"
                                required
                                placeholder="e.g. Vance"
                                class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/60 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            >
                        </div>

                        <div class="space-y-1">
                            <label class="font-semibold text-zinc-700 dark:text-zinc-300">
                                Email Address <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="email"
                                name="email"
                                id="contact-field-email"
                                required
                                placeholder="elena.vance@company.io"
                                class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/60 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            >
                        </div>

                        <div class="space-y-1">
                            <label class="font-semibold text-zinc-700 dark:text-zinc-300">
                                Telephone Number
                            </label>
                            <input
                                type="text"
                                name="phone"
                                id="contact-field-phone"
                                placeholder="+1 (415) 890-1200"
                                class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/60 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            >
                        </div>
                    </div>
                </div>

                <!-- Section 2: Account Organization & Role -->
                <div>
                    <h4 class="text-xs font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider mb-3 pb-1 border-b border-zinc-100 dark:border-zinc-800 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"></path>
                            <path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"></path>
                            <path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"></path>
                        </svg>
                        <span>Account Organization &amp; Role</span>
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        <div class="space-y-1 sm:col-span-2">
                            <label class="font-semibold text-zinc-700 dark:text-zinc-300">
                                Parent Account Organization
                            </label>
                            <select
                                name="account_id"
                                id="contact-field-account_id"
                                class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/60 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            >
                                <option value="">Select account organization...</option>
                                @foreach($accounts ?? [] as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-1">
                            <label class="font-semibold text-zinc-700 dark:text-zinc-300">
                                Job Title
                            </label>
                            <input
                                type="text"
                                name="title"
                                id="contact-field-title"
                                placeholder="e.g. Chief Technology Officer"
                                class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/60 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            >
                        </div>

                        <div class="space-y-1">
                            <label class="font-semibold text-zinc-700 dark:text-zinc-300">
                                Department
                            </label>
                            <input
                                type="text"
                                name="department"
                                id="contact-field-department"
                                placeholder="e.g. Engineering, Procurement"
                                class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/60 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            >
                        </div>

                        <div class="space-y-1">
                            <label class="font-semibold text-zinc-700 dark:text-zinc-300">
                                Buying / Influence Role <span class="text-red-500">*</span>
                            </label>
                            <select
                                name="contact_role"
                                id="contact-field-role"
                                required
                                class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/60 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            >
                                <option value="Decision Maker">Decision Maker</option>
                                <option value="Champion">Champion</option>
                                <option value="Influencer">Influencer</option>
                                <option value="Evaluator">Evaluator</option>
                            </select>
                        </div>

                        <div class="space-y-1">
                            <label class="font-semibold text-zinc-700 dark:text-zinc-300">
                                Account Representative
                            </label>
                            <select
                                name="assigned_to"
                                id="contact-field-assigned_to"
                                class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/60 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            >
                                <option value="">Select owner...</option>
                                @foreach($users ?? [] as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Checkboxes: Primary & Opt Out -->
                        <div class="sm:col-span-2 flex items-center gap-6 pt-1">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input
                                    type="checkbox"
                                    id="contact-field-is_primary"
                                    class="rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer w-4 h-4"
                                >
                                <span class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                    ⭐ Primary Contact for Account
                                </span>
                            </label>

                            <label class="flex items-center gap-2 cursor-pointer">
                                <input
                                    type="checkbox"
                                    id="contact-field-email_opt_out"
                                    class="rounded border-zinc-300 text-rose-600 focus:ring-rose-500 cursor-pointer w-4 h-4"
                                >
                                <span class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                    Email Opt-Out (Do Not Broadcast)
                                </span>
                            </label>
                        </div>

                        <div class="sm:col-span-2 space-y-1">
                            <label class="font-semibold text-zinc-700 dark:text-zinc-300">
                                Notes &amp; Context
                            </label>
                            <textarea
                                name="notes"
                                id="contact-field-notes"
                                rows="2"
                                placeholder="Stakeholder priorities, background, decision authority..."
                                class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/60 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            ></textarea>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Dynamic Custom Fields (with Field-Level Security) -->
                <div>
                    <h4 class="text-xs font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider mb-3 pb-1 border-b border-zinc-100 dark:border-zinc-800 flex items-center justify-between">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="4" y1="21" x2="4" y2="14"></line><line x1="4" y1="10" x2="4" y2="3"></line>
                                <line x1="12" y1="21" x2="12" y2="12"></line><line x1="12" y1="8" x2="12" y2="3"></line>
                                <line x1="20" y1="21" x2="20" y2="16"></line><line x1="20" y1="12" x2="20" y2="3"></line>
                                <line x1="1" y1="14" x2="7" y2="14"></line><line x1="9" y1="8" x2="15" y2="8"></line><line x1="17" y1="16" x2="23" y2="16"></line>
                            </svg>
                            <span>Dynamic Custom Fields</span>
                        </span>
                        <span class="text-[10px] text-zinc-400 font-normal lowercase">governed by field-level permissions</span>
                    </h4>
                    <div id="contact-custom-fields-form-container" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <!-- Populated dynamically by contacts.js -->
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                    <button
                        type="button"
                        onclick="window.closeContactModal()"
                        class="px-4 py-2 text-xs font-semibold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-lg transition-colors cursor-pointer"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        id="btn-submit-contact-form"
                        class="px-5 py-2 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm transition-colors cursor-pointer"
                    >
                        Create Contact
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- MODAL 2: QUICK PREVIEW MODAL               -->
    <!-- ========================================== -->
    <div id="contact-preview-modal" class="fixed inset-0 z-50 hidden bg-zinc-950/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white dark:bg-zinc-900 rounded-2xl max-w-md w-full border border-zinc-200 dark:border-zinc-800 shadow-2xl overflow-hidden animate-scale-up">
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    <h3 id="preview-contact-name" class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Contact Details</h3>
                </div>
                <button type="button" onclick="window.closeContactPreviewModal()" class="text-zinc-400 hover:text-zinc-600 cursor-pointer">
                    &times;
                </button>
            </div>
            <div class="p-6 space-y-4 text-xs">
                <div class="flex items-center justify-between">
                    <span class="text-zinc-400 font-medium">Buying Role:</span>
                    <span id="preview-contact-role"></span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-zinc-400 font-medium">Organization:</span>
                    <span id="preview-contact-account" class="font-bold text-zinc-900 dark:text-zinc-100"></span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-zinc-400 font-medium">Job Title:</span>
                    <span id="preview-contact-title" class="font-medium text-zinc-800 dark:text-zinc-200"></span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-zinc-400 font-medium">Department:</span>
                    <span id="preview-contact-department" class="text-zinc-700 dark:text-zinc-300"></span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-zinc-400 font-medium">Direct Email:</span>
                    <span id="preview-contact-email" class="text-indigo-600 font-mono"></span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-zinc-400 font-medium">Phone Number:</span>
                    <span id="preview-contact-phone" class="font-mono text-zinc-800 dark:text-zinc-200"></span>
                </div>
                <div class="p-3 rounded-lg bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200 dark:border-zinc-700">
                    <div class="text-[10px] font-semibold text-zinc-400 uppercase mb-1">Notes:</div>
                    <div id="preview-contact-notes" class="text-zinc-700 dark:text-zinc-300 whitespace-pre-wrap"></div>
                </div>

                <div class="flex items-center justify-between pt-3 border-t border-zinc-200 dark:border-zinc-800">
                    <button
                        type="button"
                        id="btn-preview-full-360"
                        class="text-xs font-semibold text-indigo-600 hover:underline cursor-pointer"
                    >
                        View Full 360° Profile &rarr;
                    </button>
                    <button
                        type="button"
                        onclick="window.closeContactPreviewModal()"
                        class="px-4 py-1.5 text-xs font-semibold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 rounded-lg cursor-pointer"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- MODAL 3: FULLSCREEN CONTACT 360° DETAIL MODAL (Matching Account 360 1:1) -->
    <!-- ========================================== -->
    <div id="contact-360-modal" class="fixed inset-0 z-50 hidden bg-white dark:bg-zinc-950 overflow-y-auto flex flex-col">
        <!-- Top App Bar -->
        <div class="sticky top-0 z-30 bg-white dark:bg-zinc-900 border-b border-slate-200/80 dark:border-zinc-800 px-6 sm:px-8 py-3 flex items-center justify-between shadow-2xs">
            <!-- Left: Back button, Category Badge, Title -->
            <div class="flex items-center gap-3">
                <button
                    type="button"
                    onclick="window.closeContact360Modal()"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-slate-700 dark:text-zinc-200 hover:bg-slate-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs cursor-pointer"
                >
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    <span>Back to Contacts</span>
                </button>

                <span class="px-2 py-0.5 rounded text-[10px] font-bold tracking-wider uppercase text-emerald-700 bg-emerald-50 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                    CONTACTS
                </span>

                <h2 id="modal-fullscreen-contact-name" class="text-base sm:text-lg font-bold text-[#2d4b68] dark:text-zinc-100 truncate max-w-md flex items-center gap-1.5">
                    <span>Contact Profile</span>
                    <span id="cnt-fs-star-badge" class="hidden text-amber-500 text-sm">⭐</span>
                </h2>
            </div>

            <!-- Right: Actions -->
            <div class="flex items-center gap-2">
                <button
                    type="button"
                    onclick="window.openTransferContactFromFullscreen()"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-sky-600 dark:text-sky-400 hover:bg-sky-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs cursor-pointer"
                >
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                    <span>Change Owner</span>
                </button>

                <button
                    type="button"
                    onclick="window.editContactFromFullscreen()"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-slate-700 dark:text-zinc-200 hover:bg-slate-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs cursor-pointer"
                >
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
                    </svg>
                    <span>Edit</span>
                </button>

                <button
                    type="button"
                    onclick="window.deleteContactFromFullscreen()"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-rose-200 dark:border-rose-800/60 bg-rose-50 dark:bg-rose-950/40 text-xs font-semibold text-rose-600 dark:text-rose-400 hover:bg-rose-100 transition-colors shadow-2xs cursor-pointer"
                >
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    </svg>
                    <span>Delete</span>
                </button>

                <button
                    type="button"
                    onclick="window.closeContact360Modal()"
                    class="text-slate-400 hover:text-slate-600 dark:hover:text-zinc-200 text-xl font-light px-2 py-1 transition-colors cursor-pointer leading-none"
                    title="Close"
                >
                    &times;
                </button>
            </div>
        </div>

        <!-- Breadcrumbs bar -->
        <div class="px-6 sm:px-8 pt-4 pb-2 text-xs text-slate-400 dark:text-zinc-500 flex items-center gap-1.5">
            <span class="hover:underline cursor-pointer" onclick="window.closeContact360Modal()">DealSync</span>
            <span>&rsaquo;</span>
            <span class="hover:underline cursor-pointer" onclick="window.closeContact360Modal()">Contacts</span>
            <span>&rsaquo;</span>
            <span id="modal-breadcrumb-contact-name" class="font-medium text-slate-700 dark:text-zinc-300">
                Contact Profile
            </span>
        </div>

        <!-- Main Body: Two Column Layout -->
        <div class="max-w-[1440px] w-full mx-auto px-6 sm:px-8 py-4 flex flex-col lg:flex-row gap-6 items-start flex-1">
            <!-- LEFT COLUMN (approx 68%): Contact Detail, Custom Section, System Information -->
            <div class="flex-1 w-full space-y-5">
                <!-- Section 1: Contact Detail -->
                <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-orange-200/90 dark:border-orange-500/30 shadow-[0_4px_20px_-2px_rgba(251,146,60,0.12)] overflow-hidden">
                    <!-- Collapsible Header -->
                    <div
                        onclick="window.toggleFullscreenSection('contact-detail-body', 'contact-detail-chevron')"
                        class="px-5 py-3.5 bg-white dark:bg-zinc-900 flex items-center justify-between cursor-pointer select-none"
                    >
                        <div class="flex items-center gap-2 font-bold text-xs sm:text-sm text-slate-900 dark:text-zinc-100">
                            <svg id="contact-detail-chevron" class="w-4 h-4 text-slate-400 transition-transform duration-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                            <svg class="w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            <span>Contact Detail</span>
                        </div>
                    </div>

                    <!-- Contact Detail 2-Column Fields Grid -->
                    <div id="contact-detail-body" class="px-6 py-4 border-t border-slate-100 dark:border-zinc-800">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-4 text-xs">
                            <!-- Left Fields -->
                            <div class="space-y-4">
                                <!-- First Name -->
                                <div class="flex items-center justify-between py-1 border-b border-slate-100/80 dark:border-zinc-800/80 group">
                                    <div>
                                        <div class="text-[11px] text-slate-400 dark:text-zinc-500 font-medium">First Name</div>
                                        <div id="detail-fs-first_name" class="font-semibold text-slate-900 dark:text-zinc-100 mt-0.5">—</div>
                                    </div>
                                </div>

                                <!-- Last Name -->
                                <div class="flex items-center justify-between py-1 border-b border-slate-100/80 dark:border-zinc-800/80 group">
                                    <div>
                                        <div class="text-[11px] text-slate-400 dark:text-zinc-500 font-medium">Last Name</div>
                                        <div id="detail-fs-last_name" class="font-semibold text-slate-900 dark:text-zinc-100 mt-0.5">—</div>
                                    </div>
                                </div>

                                <!-- Job Title -->
                                <div class="flex items-center justify-between py-1 border-b border-slate-100/80 dark:border-zinc-800/80 group">
                                    <div>
                                        <div class="text-[11px] text-slate-400 dark:text-zinc-500 font-medium">Job Title</div>
                                        <div id="detail-fs-title" class="font-semibold text-slate-900 dark:text-zinc-100 mt-0.5">—</div>
                                    </div>
                                </div>

                                <!-- Department -->
                                <div class="flex items-center justify-between py-1 border-b border-slate-100/80 dark:border-zinc-800/80 group">
                                    <div>
                                        <div class="text-[11px] text-slate-400 dark:text-zinc-500 font-medium">Department</div>
                                        <div id="detail-fs-department" class="font-semibold text-slate-900 dark:text-zinc-100 mt-0.5">—</div>
                                    </div>
                                </div>

                                <!-- Buying Role -->
                                <div class="flex items-center justify-between py-1 border-b border-slate-100/80 dark:border-zinc-800/80 group">
                                    <div>
                                        <div class="text-[11px] text-slate-400 dark:text-zinc-500 font-medium">Buying / Influence Role</div>
                                        <div id="detail-fs-role" class="mt-0.5">—</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Fields -->
                            <div class="space-y-4">
                                <!-- Parent Account -->
                                <div class="flex items-center justify-between py-1 border-b border-slate-100/80 dark:border-zinc-800/80 group">
                                    <div>
                                        <div class="text-[11px] text-slate-400 dark:text-zinc-500 font-medium">Account Organization</div>
                                        <div id="detail-fs-account" class="font-semibold text-indigo-600 dark:text-indigo-400 mt-0.5 truncate max-w-xs">
                                            —
                                        </div>
                                    </div>
                                </div>

                                <!-- Direct Email -->
                                <div class="flex items-center justify-between py-1 border-b border-slate-100/80 dark:border-zinc-800/80 group">
                                    <div>
                                        <div class="text-[11px] text-slate-400 dark:text-zinc-500 font-medium">Direct Email</div>
                                        <div id="detail-fs-email" class="font-semibold text-slate-900 dark:text-zinc-100 mt-0.5 font-mono">
                                            —
                                        </div>
                                    </div>
                                </div>

                                <!-- Telephone Number -->
                                <div class="flex items-center justify-between py-1 border-b border-slate-100/80 dark:border-zinc-800/80 group">
                                    <div>
                                        <div class="text-[11px] text-slate-400 dark:text-zinc-500 font-medium">Telephone Number</div>
                                        <div id="detail-fs-phone" class="font-semibold text-slate-900 dark:text-zinc-100 mt-0.5 font-mono">
                                            —
                                        </div>
                                    </div>
                                </div>

                                <!-- Primary Contact Flag -->
                                <div class="flex items-center justify-between py-1 border-b border-slate-100/80 dark:border-zinc-800/80 group">
                                    <div>
                                        <div class="text-[11px] text-slate-400 dark:text-zinc-500 font-medium">Primary Account Contact</div>
                                        <div id="detail-fs-primary" class="font-semibold text-slate-900 dark:text-zinc-100 mt-0.5">
                                            No
                                        </div>
                                    </div>
                                </div>

                                <!-- Email Opt Out -->
                                <div class="flex items-center justify-between py-1 border-b border-slate-100/80 dark:border-zinc-800/80 group">
                                    <div>
                                        <div class="text-[11px] text-slate-400 dark:text-zinc-500 font-medium">Email Opt-Out</div>
                                        <div id="detail-fs-opt-out" class="font-semibold text-slate-900 dark:text-zinc-100 mt-0.5">
                                            Active (Opted-in)
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Custom Section -->
                <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-orange-200/90 dark:border-orange-500/30 shadow-[0_4px_20px_-2px_rgba(251,146,60,0.12)] overflow-hidden">
                    <div
                        onclick="window.toggleFullscreenSection('contact-custom-section-body', 'contact-custom-section-chevron')"
                        class="px-5 py-3.5 bg-white dark:bg-zinc-900 flex items-center justify-between cursor-pointer select-none"
                    >
                        <div class="flex items-center gap-2 font-bold text-xs sm:text-sm text-slate-900 dark:text-zinc-100">
                            <svg id="contact-custom-section-chevron" class="w-4 h-4 text-slate-400 transition-transform duration-200 -rotate-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                            <svg class="w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>
                            </svg>
                            <span>Custom Section</span>
                        </div>
                    </div>
                    <div id="contact-custom-section-body" class="hidden px-6 py-4 border-t border-slate-100 dark:border-zinc-800">
                        <div id="detail-fs-custom-fields" class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                            <span class="text-slate-400 italic">No custom fields defined.</span>
                        </div>
                    </div>
                </div>

                <!-- Section 3: System Information -->
                <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-orange-200/90 dark:border-orange-500/30 shadow-[0_4px_20px_-2px_rgba(251,146,60,0.12)] overflow-hidden">
                    <div
                        onclick="window.toggleFullscreenSection('contact-system-info-body', 'contact-system-info-chevron')"
                        class="px-5 py-3.5 bg-white dark:bg-zinc-900 flex items-center justify-between cursor-pointer select-none"
                    >
                        <div class="flex items-center gap-2 font-bold text-xs sm:text-sm text-slate-900 dark:text-zinc-100">
                            <svg id="contact-system-info-chevron" class="w-4 h-4 text-slate-400 transition-transform duration-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                            <svg class="w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            </svg>
                            <span>System Information</span>
                        </div>
                    </div>
                    <div id="contact-system-info-body" class="px-6 py-4 border-t border-slate-100 dark:border-zinc-800 text-xs">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Current Owner -->
                            <div>
                                <div class="text-[11px] text-slate-400 dark:text-zinc-500 flex items-center gap-1 font-medium">
                                    <svg class="w-3.5 h-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                    <span>Current Owner</span>
                                </div>
                                <div class="flex items-center gap-2 mt-1">
                                    <span id="detail-fs-owner" class="font-bold text-slate-900 dark:text-zinc-100">System Administrator</span>
                                    <button type="button" onclick="window.openTransferContactFromFullscreen()" class="px-2 py-0.5 text-[11px] font-semibold text-teal-700 bg-teal-50 border border-teal-200 rounded-md hover:bg-teal-100 transition-colors cursor-pointer">
                                        Change
                                    </button>
                                </div>
                            </div>

                            <!-- Created Date & Time -->
                            <div>
                                <div class="text-[11px] text-slate-400 dark:text-zinc-500 flex items-center gap-1 font-medium">
                                    <svg class="w-3.5 h-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                    <span>Created Date &amp; Time</span>
                                </div>
                                <div id="detail-fs-created-at" class="font-bold text-slate-900 dark:text-zinc-100 mt-1">—</div>
                            </div>

                            <!-- Created By -->
                            <div>
                                <div class="text-[11px] text-slate-400 dark:text-zinc-500 flex items-center gap-1 font-medium">
                                    <svg class="w-3.5 h-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                    <span>Created By</span>
                                </div>
                                <div id="detail-fs-created-by" class="font-bold text-slate-900 dark:text-zinc-100 mt-1">System Admin</div>
                            </div>

                            <!-- Record ID -->
                            <div>
                                <div class="text-[11px] text-slate-400 dark:text-zinc-500 flex items-center gap-1 font-medium">
                                    <svg class="w-3.5 h-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M7 7h10"/><path d="M7 12h10"/><path d="M7 17h10"/></svg>
                                    <span>Record ID</span>
                                </div>
                                <div class="mt-1">
                                    <span id="detail-fs-record-id" class="px-2 py-0.5 rounded font-mono text-[11px] bg-sky-50 dark:bg-sky-950/40 text-sky-700 dark:text-sky-300 border border-sky-100 dark:border-sky-800">
                                        —
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
                        <span id="fs-count-related" class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-slate-200 dark:bg-zinc-700 text-slate-700 dark:text-zinc-300 ml-0.5">2</span>
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
                    id="contact-file-upload-input"
                    multiple
                    class="hidden"
                    onchange="window.handleContactFileUpload ? window.handleContactFileUpload(event) : null"
                >

                <!-- Tab Content 1: Related Cards List -->
                <div id="fs-tab-content-related" class="space-y-3">
                    <!-- Card 1: Files in Related -->
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
                                    onclick="window.triggerFileUploadForContact()"
                                    id="btn-contact-add-file"
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
                        <div id="contact-file-uploading-indicator" class="hidden mt-2 p-2 rounded-lg bg-orange-50 dark:bg-orange-950/40 border border-orange-200 dark:border-orange-800 flex items-center justify-between text-xs text-orange-700 dark:text-orange-300 animate-pulse">
                            <div class="flex items-center gap-2">
                                <svg class="animate-spin w-3.5 h-3.5 text-orange-600" viewBox="0 0 24 24" fill="none">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                <span>Uploading file(s)...</span>
                            </div>
                        </div>

                        <!-- Collapsible Body -->
                        <div id="fs-files-collapse" class="mt-2.5 pt-2 border-t border-slate-100 dark:border-zinc-800 space-y-2">
                            <!-- Files list container -->
                            <div id="fs-files-list-container" class="space-y-1 max-h-48 overflow-y-auto">
                                <div class="py-4 text-center text-xs text-slate-400">Loading files...</div>
                            </div>

                            <div id="fs-files-footer" class="pt-1.5 text-center border-t border-slate-100 dark:border-zinc-800">
                                <a href="javascript:void(0)" onclick="window.switchFullscreenRightTab('files')" class="text-xs font-semibold text-[#7c3aed] dark:text-purple-400 hover:underline">
                                    View All (<span id="fs-files-viewall-count">0</span>)
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Card 2: Parent Account Details -->
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-orange-200/90 dark:border-orange-500/30 shadow-[0_4px_15px_-2px_rgba(251,146,60,0.1)] p-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-lg bg-indigo-600 text-white flex items-center justify-center shrink-0">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"></path>
                                        <path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"></path>
                                        <path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"></path>
                                    </svg>
                                </div>
                                <span class="font-bold text-xs text-slate-900 dark:text-zinc-100 flex items-center gap-1">
                                    Organization (<span id="fs-badge-account-name">Unlinked</span>)
                                </span>
                            </div>
                            <button
                                type="button"
                                onclick="window.toggleFsRelatedCard('fs-account-collapse', 'fs-account-chevron')"
                                class="p-1 text-slate-400 hover:text-slate-600 dark:hover:text-zinc-300 cursor-pointer"
                                title="Toggle collapse"
                            >
                                <svg id="fs-account-chevron" class="w-3.5 h-3.5 transition-transform duration-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="18 15 12 9 6 15"/></svg>
                            </button>
                        </div>
                        <div id="fs-account-collapse" class="mt-2.5 pt-2 border-t border-slate-100 dark:border-zinc-800 space-y-1.5 text-xs">
                            <div id="fs-account-info-container" class="space-y-1 text-slate-600 dark:text-zinc-300">
                                <p class="text-xs text-slate-400">Loading organization details...</p>
                            </div>
                        </div>
                    </div>

                    <!-- Card 3: Opportunities / Deals Associated -->
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-orange-200/90 dark:border-orange-500/30 shadow-[0_4px_15px_-2px_rgba(251,146,60,0.1)] p-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-lg bg-emerald-600 text-white flex items-center justify-center shrink-0">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <path d="m16 10-4 4-2-2"></path>
                                    </svg>
                                </div>
                                <span class="font-bold text-xs text-slate-900 dark:text-zinc-100 flex items-center gap-1">
                                    Opportunities &amp; Deals (<span id="fs-badge-deals-count">0</span>)
                                </span>
                            </div>
                            <button
                                type="button"
                                onclick="window.toggleFsRelatedCard('fs-deals-collapse', 'fs-deals-chevron')"
                                class="p-1 text-slate-400 hover:text-slate-600 dark:hover:text-zinc-300 cursor-pointer"
                                title="Toggle collapse"
                            >
                                <svg id="fs-deals-chevron" class="w-3.5 h-3.5 transition-transform duration-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="18 15 12 9 6 15"/></svg>
                            </button>
                        </div>
                        <div id="fs-deals-collapse" class="mt-2.5 pt-2 border-t border-slate-100 dark:border-zinc-800 space-y-2">
                            <div id="fs-deals-list-container" class="space-y-1.5 max-h-48 overflow-y-auto">
                                <div class="py-4 text-center text-xs text-slate-400">No deals linked yet.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab Content 2: Activity Tab View -->
                <div id="fs-tab-content-activity" class="hidden space-y-4">
                    <!-- CARD 1: ACTIVITY ACTIONS & CREATION CARD -->
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl border-2 border-orange-300 dark:border-orange-500/50 shadow-[0_4px_15px_-2px_rgba(251,146,60,0.1)] p-4 sm:p-5 space-y-3.5">
                        <!-- Card Header -->
                        <div class="flex items-center justify-between pb-1 border-b border-slate-100 dark:border-zinc-800">
                            <div>
                                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-zinc-200">
                                    Activity &amp; Action Item Center
                                </h4>
                                <p class="text-[10px] text-slate-400 dark:text-zinc-500">
                                    MULTI-TYPE ACTION TRACKER
                                </p>
                            </div>
                        </div>

                        <!-- Sub-navigation Pills -->
                        <div class="flex items-center gap-1.5 overflow-x-auto pb-1" id="cnt-activity-pills-bar">
                            <button
                                type="button"
                                onclick="window.switchContactActivityFormType('Call')"
                                id="cnt-act-pill-call"
                                class="cnt-act-type-pill inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-zinc-300 font-semibold text-xs cursor-pointer transition-all shrink-0"
                            >
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                                </svg>
                                <span>Log a Call</span>
                            </button>

                            <button
                                type="button"
                                onclick="window.switchContactActivityFormType('Task')"
                                id="cnt-act-pill-task"
                                class="cnt-act-type-pill inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-zinc-300 font-semibold text-xs cursor-pointer transition-all shrink-0"
                            >
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect width="18" height="18" x="3" y="3" rx="2"/><path d="m9 12 2 2 4-4"/>
                                </svg>
                                <span>New Task</span>
                            </button>

                            <button
                                type="button"
                                onclick="window.switchContactActivityFormType('Event')"
                                id="cnt-act-pill-event"
                                class="cnt-act-type-pill inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-zinc-300 font-semibold text-xs cursor-pointer transition-all shrink-0"
                            >
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                                </svg>
                                <span>New Event</span>
                            </button>

                            <button
                                type="button"
                                onclick="window.switchContactActivityFormType('Note')"
                                id="cnt-act-pill-note"
                                class="cnt-act-type-pill inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-[#f3e8ff] text-[#7c3aed] border border-purple-200 dark:bg-purple-950/50 dark:border-purple-800 dark:text-purple-300 font-semibold text-xs shadow-2xs cursor-pointer transition-all shrink-0"
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
                        <div id="cnt-activity-call-view" class="hidden space-y-3 pt-1">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 mb-1.5">
                                        Call Subject
                                    </label>
                                    <input
                                        type="text"
                                        id="cnt-call-input-subject"
                                        placeholder="e.g. Follow-up call with stakeholder..."
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
                                            id="cnt-call-input-datetime"
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
                                    id="cnt-call-input-details"
                                    rows="3"
                                    placeholder="Key details, discussion points, or action items..."
                                    class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-zinc-100 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-purple-500 transition-colors resize-y"
                                ></textarea>
                            </div>

                            <div class="flex items-center justify-end pt-2">
                                <button
                                    type="button"
                                    onclick="window.submitContactActivity('Call')"
                                    id="btn-cnt-call-submit"
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
                        <div id="cnt-activity-task-view" class="hidden space-y-3 pt-1">
                            <div class="border-2 border-dashed border-slate-200 dark:border-zinc-700 rounded-2xl p-6 sm:p-7 flex flex-col items-center justify-center text-center bg-slate-50/40 dark:bg-zinc-800/20">
                                <div class="w-12 h-12 rounded-2xl bg-[#4f46e5] text-white flex items-center justify-center shadow-md shadow-indigo-500/25 mb-3.5">
                                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <rect width="18" height="18" x="3" y="3" rx="2"/>
                                        <path d="m9 12 2 2 4-4"/>
                                    </svg>
                                </div>
                                <h5 class="text-sm font-bold text-slate-800 dark:text-zinc-200 mb-1">
                                    Create a New Assigned Task
                                </h5>
                                <p class="text-xs text-slate-400 dark:text-zinc-500 max-w-sm mb-4">
                                    Track next action items, stakeholder due dates, and task assignments for this contact.
                                </p>
                                <button
                                    type="button"
                                    onclick="window.openContactTaskModal()"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#4f46e5] hover:bg-[#4338ca] text-white text-xs font-bold shadow-sm hover:shadow-md cursor-pointer transition-all"
                                >
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <line x1="12" y1="5" x2="12" y2="19"></line>
                                        <line x1="5" y1="12" x2="19" y2="12"></line>
                                    </svg>
                                    <span>Create New Task</span>
                                </button>
                            </div>
                        </div>

                        <!-- View 3: New Event / Meeting Form -->
                        <div id="cnt-activity-event-view" class="hidden space-y-3 pt-1">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 mb-1.5">
                                    Meeting / Event Subject
                                </label>
                                <input
                                    type="text"
                                    id="cnt-event-input-subject"
                                    placeholder="e.g. Discovery &amp; Demo session..."
                                    class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-zinc-100 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-purple-500 transition-colors"
                                >
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 mb-1.5">
                                        Start Date &amp; Time
                                    </label>
                                    <div class="relative">
                                        <input
                                            type="text"
                                            id="cnt-event-input-start"
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
                                        End Date &amp; Time
                                    </label>
                                    <div class="relative">
                                        <input
                                            type="text"
                                            id="cnt-event-input-end"
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
                                    id="cnt-event-input-details"
                                    rows="3"
                                    placeholder="Key details, discussion points, or action items..."
                                    class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-zinc-100 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-purple-500 transition-colors resize-y"
                                ></textarea>
                            </div>

                            <div class="flex items-center justify-end pt-2">
                                <button
                                    type="button"
                                    onclick="window.submitContactEvent()"
                                    id="btn-cnt-event-submit"
                                    class="inline-flex items-center justify-center gap-2 min-w-[140px] px-6 py-2.5 rounded-xl bg-[#7c3aed] hover:bg-[#6d28d9] text-white text-xs font-bold shadow-sm hover:shadow-md cursor-pointer transition-all whitespace-nowrap shrink-0"
                                >
                                    <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
                                    </svg>
                                    <span>Log Meeting</span>
                                </button>
                            </div>
                        </div>

                        <!-- View 4: Add Note Form (Active by default) -->
                        <div id="cnt-activity-note-view" class="space-y-3 pt-1">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 mb-1.5">
                                    Note Title (optional)
                                </label>
                                <input
                                    type="text"
                                    id="cnt-note-input-subject"
                                    placeholder="Title or headline (optional)..."
                                    class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-zinc-100 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-purple-500 transition-colors"
                                >
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 mb-1.5">
                                    Note Details <span class="text-rose-500 font-bold">*</span>
                                </label>
                                <textarea
                                    id="cnt-note-input-details"
                                    rows="3"
                                    placeholder="Type notes here... (captures and saves automatically to record history)"
                                    class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-zinc-100 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-purple-500 transition-colors resize-y"
                                ></textarea>
                            </div>

                            <div class="flex items-center justify-end pt-2">
                                <button
                                    type="button"
                                    onclick="window.submitContactActivity('Note')"
                                    id="btn-cnt-note-submit"
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

                    <!-- CARD 2: ACTIVITY HISTORY & TIMELINE CARD (Exact 1:1 Match) -->
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl border-2 border-orange-300 dark:border-orange-500/50 shadow-[0_4px_15px_-2px_rgba(251,146,60,0.1)] p-4 sm:p-5 space-y-3.5">
                        <!-- Header -->
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-zinc-800 select-none">
                            <div class="flex items-center gap-2">
                                <div class="w-2.5 h-2.5 rounded-full bg-orange-500 animate-pulse"></div>
                                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-zinc-200">
                                    Activity History
                                </h4>
                                <span id="contact-timeline-count-badge" class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#f3e8ff] text-[#7c3aed] dark:bg-purple-950/60 dark:text-purple-300">
                                    0
                                </span>
                            </div>

                            <button
                                type="button"
                                onclick="window.refreshContactActivities(state.viewing360Contact)"
                                class="p-1 rounded-md text-slate-400 hover:text-slate-600 dark:hover:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
                                title="Refresh timeline"
                            >
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 21h5v-5"/>
                                </svg>
                            </button>
                        </div>

                        <!-- Timeline Items List -->
                        <div id="contact-activities-timeline-list" class="space-y-3">
                            <div class="p-6 border-2 border-dashed border-slate-200 dark:border-zinc-700 rounded-xl text-center space-y-1">
                                <p class="text-xs font-medium text-slate-400">Loading activities...</p>
                            </div>
                        </div>
                    </div>
                </div>

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
                                    onclick="window.triggerFileUploadForContact()"
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
                                    onclick="window.loadContactFiles(state.viewing360Contact)"
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
    <!-- MODAL 4: CUSTOMIZE VISIBLE FIELDS MODAL     -->
    <!-- ========================================== -->
    <div id="contact-customize-modal" class="fixed inset-0 z-50 hidden bg-zinc-950/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white dark:bg-zinc-900 rounded-3xl max-w-2xl w-full border border-slate-200/90 dark:border-zinc-800 shadow-2xl overflow-hidden flex flex-col max-h-[90vh] animate-scale-up">
            <!-- Modal Header -->
            <div class="px-6 py-5 border-b border-slate-100 dark:border-zinc-800/80 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-950/40 border border-purple-100 dark:border-purple-900/50 text-[#7c3aed] dark:text-purple-400 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-[#7c3aed] dark:text-purple-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="4" y1="21" x2="4" y2="14"></line><line x1="4" y1="10" x2="4" y2="3"></line>
                            <line x1="12" y1="21" x2="12" y2="12"></line><line x1="12" y1="8" x2="12" y2="3"></line>
                            <line x1="20" y1="21" x2="20" y2="16"></line><line x1="20" y1="12" x2="20" y2="3"></line>
                            <line x1="1" y1="14" x2="7" y2="14"></line><line x1="9" y1="8" x2="15" y2="8"></line><line x1="17" y1="16" x2="23" y2="16"></line>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-[#2d4b68] dark:text-zinc-100 leading-tight">
                            Customize Contact Table Columns
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5 font-normal">
                            Choose visible columns and drag or reorder their display order
                        </p>
                    </div>
                </div>
                <button
                    type="button"
                    onclick="window.closeCustomizeFieldsModal()"
                    class="text-slate-400 hover:text-slate-600 dark:hover:text-zinc-200 text-2xl font-light p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer leading-none"
                    title="Close"
                >
                    &times;
                </button>
            </div>

            <!-- Toolbar: Search + Select / Deselect / Reset -->
            <div class="px-6 py-3 border-b border-slate-100 dark:border-zinc-800 flex items-center justify-between gap-3 bg-slate-50/50 dark:bg-zinc-800/30 shrink-0">
                <div class="relative flex-1 max-w-xs">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input
                        type="text"
                        id="customize-fields-search"
                        oninput="window.filterCustomizeFieldsList(this.value)"
                        placeholder="Search columns..."
                        class="w-full pl-9 pr-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-zinc-100 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-purple-500"
                    >
                </div>

                <div class="flex items-center gap-3 text-xs text-slate-500 dark:text-zinc-400 font-semibold select-none">
                    <button type="button" onclick="window.customizeSelectAllFields()" class="hover:text-slate-900 dark:hover:text-zinc-100 cursor-pointer transition-colors">
                        Select All
                    </button>
                    <span>&bull;</span>
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

            <!-- Banner -->
            <div class="px-6 py-2 bg-blue-50/70 dark:bg-blue-950/30 border-b border-blue-100/80 dark:border-blue-900/40 text-xs text-blue-700 dark:text-blue-300 flex items-center gap-2 shrink-0">
                <svg class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400 shrink-0" viewBox="0 0 24 24" fill="currentColor">
                    <circle cx="9" cy="6" r="1.5"/><circle cx="15" cy="6" r="1.5"/>
                    <circle cx="9" cy="12" r="1.5"/><circle cx="15" cy="12" r="1.5"/>
                    <circle cx="9" cy="18" r="1.5"/><circle cx="15" cy="18" r="1.5"/>
                </svg>
                <span>
                    <strong class="font-bold">Click the arrow buttons (↑ / ↓)</strong> or toggle checkboxes to position columns.
                </span>
            </div>

            <!-- Field Items Scrollable Container -->
            <div id="customize-fields-list-container" class="p-6 space-y-2.5 overflow-y-auto flex-1 bg-white dark:bg-zinc-900 min-h-[300px]">
                <!-- Populated dynamically by contacts.js -->
            </div>

            <!-- Footer -->
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
                        Apply &amp; Save View
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- MODAL 5: FILTER CONTACTS MODAL (Matching Screenshot) -->
    <!-- ========================================== -->
    <div id="contact-filter-modal" class="fixed inset-0 z-50 hidden bg-zinc-950/60 backdrop-blur-xs flex items-center justify-center p-4">
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
                            Filter Contacts
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5 font-normal">
                            Filter records by matching any object fields
                        </p>
                    </div>
                </div>
                <button
                    type="button"
                    onclick="window.closeContactFilterModal()"
                    class="text-slate-400 hover:text-slate-600 dark:hover:text-zinc-200 text-2xl font-light p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer leading-none"
                    title="Close"
                >
                    &times;
                </button>
            </div>

            <!-- Modal Body: Conditions List & Add Button -->
            <div class="p-6 space-y-4 max-h-[65vh] overflow-y-auto">
                <!-- Dynamic Filter Condition Rows List -->
                <div id="contact-filter-conditions-list" class="space-y-3">
                    <!-- Populated dynamically by contacts.js -->
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
                    onclick="window.clearAllContactFiltersFromModal()"
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
                        onclick="window.closeContactFilterModal()"
                        class="px-4 py-2 text-xs sm:text-sm font-semibold text-slate-700 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-xl border border-slate-300 dark:border-zinc-700 transition-colors cursor-pointer"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        onclick="window.applyContactFiltersFromModal()"
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

    <!-- ========================================== -->
    <!-- MODAL 6: REASSIGN / TRANSFER OWNER MODAL   -->
    <!-- ========================================== -->
    <div id="contact-transfer-modal" class="fixed inset-0 z-50 hidden bg-zinc-950/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white dark:bg-zinc-900 rounded-2xl max-w-md w-full border border-zinc-200 dark:border-zinc-800 shadow-2xl overflow-hidden animate-scale-up">
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-sky-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Change Contact Owner</h3>
                </div>
                <button type="button" onclick="window.closeTransferContactModal()" class="text-zinc-400 hover:text-zinc-600 cursor-pointer">
                    &times;
                </button>
            </div>
            <div class="p-6 space-y-4 text-xs">
                <p class="text-zinc-500">
                    Reassign ownership of this contact to another team member:
                </p>
                <div class="space-y-1">
                    <label class="font-semibold text-zinc-700 dark:text-zinc-300">Select New Owner</label>
                    <select
                        id="transfer-contact-user-select"
                        class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100"
                    >
                        @foreach($users ?? [] as $u)
                            <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center justify-end gap-3 pt-3 border-t border-zinc-200 dark:border-zinc-800">
                    <button
                        type="button"
                        onclick="window.closeTransferContactModal()"
                        class="px-4 py-1.5 text-xs font-semibold text-zinc-600 hover:bg-zinc-100 rounded-lg cursor-pointer"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        onclick="window.submitTransferContact()"
                        class="px-4 py-1.5 text-xs font-semibold text-white bg-sky-600 hover:bg-sky-700 rounded-lg shadow-sm cursor-pointer"
                    >
                        Reassign Owner
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- MODAL 7: TASK CREATION MODAL FOR CONTACT   -->
    <!-- ========================================== -->
    <div id="contact-task-modal" class="fixed inset-0 z-50 hidden bg-zinc-950/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white dark:bg-zinc-900 rounded-2xl max-w-lg w-full border border-slate-200 dark:border-zinc-800 shadow-2xl overflow-hidden animate-scale-up">
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-indigo-600 text-white flex items-center justify-center">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="m9 12 2 2 4-4"/></svg>
                    </div>
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Create Task for Contact</h3>
                </div>
                <button type="button" onclick="window.closeContactTaskModal()" class="text-zinc-400 hover:text-zinc-600 cursor-pointer">
                    &times;
                </button>
            </div>
            <div class="p-6 space-y-3.5 text-xs">
                <div>
                    <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Task Subject <span class="text-red-500">*</span></label>
                    <input type="text" id="cnt-task-input-subject" placeholder="e.g. Follow up on proposal" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-zinc-100">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Due Date</label>
                        <input type="date" id="cnt-task-input-due" value="{{ now()->addDays(2)->format('Y-m-d') }}" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-zinc-100">
                    </div>
                    <div>
                        <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Priority</label>
                        <select id="cnt-task-input-priority" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-zinc-100">
                            <option value="Normal">Normal</option>
                            <option value="High">High</option>
                            <option value="Urgent">Urgent</option>
                            <option value="Low">Low</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Comments / Instructions</label>
                    <textarea id="cnt-task-input-comments" rows="3" placeholder="Task specifics, action items..." class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-zinc-100"></textarea>
                </div>
                <div class="flex items-center justify-end gap-3 pt-3 border-t border-zinc-200 dark:border-zinc-800">
                    <button type="button" onclick="window.closeContactTaskModal()" class="px-4 py-2 text-xs font-semibold text-zinc-600 hover:bg-zinc-100 rounded-xl cursor-pointer">
                        Cancel
                    </button>
                    <button type="button" onclick="window.submitContactTaskModal()" class="px-5 py-2 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-xs cursor-pointer">
                        Save Task
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Safe JSON hydration for SPA and first-load rendering -->
@php
    $contactsHydrationData = [
        'initialContacts' => $initialContacts ?? [],
        'customFields' => $customFields ?? [],
        'fieldPermissions' => !empty($fieldPermissions) ? $fieldPermissions : (object)[],
        'accounts' => $accounts ?? [],
        'users' => $users ?? [],
        'roles' => $roles ?? ['Decision Maker', 'Champion', 'Influencer', 'Evaluator'],
        'canSendBulkEmail' => $canSendBulkEmail ?? true,
        'currentUserProfile' => $currentUserProfile ?? 'Administrator',
    ];
@endphp
<script>
    window.__CONTACTS_DATA__ = {!! json_encode($contactsHydrationData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};
</script>
<script src="{{ asset('js/contacts.js') }}?v=3.5"></script>
@endsection
