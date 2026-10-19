@extends('layouts.app')

@section('title', 'Contacts & Stakeholders — DealSync Enterprise CRM')

@section('content')
<!-- Centered Max-Width Container matching React page-wrapper (max-width: 1240px, padding: 2.25rem 2.5rem) -->
<div id="contacts-page-root" class="max-w-[1240px] mx-auto w-full px-6 sm:px-10 py-7 space-y-6 animate-fade-in">
    <!-- Top Action Header -->
    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4 pb-2 border-b border-zinc-200 dark:border-zinc-800">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800">
                    Stakeholder Graph
                </span>
                <span class="text-xs text-zinc-500 dark:text-zinc-400">
                    Direct Email &amp; Telephone Reachability
                </span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-950 dark:text-zinc-100">
                Contacts &amp; Stakeholders
            </h1>
            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 max-w-2xl">
                Individual stakeholders across customer accounts with multi-contact relationship mapping, buying influence roles, and primary contact flags. Synced with dynamic custom fields and permissions.
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap shrink-0">
            <!-- Filter Button -->
            <button
                type="button"
                onclick="window.openContactFilterModal()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs cursor-pointer"
            >
                <i data-lucide="filter" class="w-3.5 h-3.5 text-zinc-500"></i>
                <span>Filter</span>
                <span id="contacts-filter-badge" class="hidden px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-blue-600 text-white">0</span>
            </button>

            <!-- Export CSV Button -->
            <button
                type="button"
                onclick="window.exportContactsCsv()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs cursor-pointer"
            >
                <i data-lucide="download" class="w-3.5 h-3.5 text-zinc-500"></i>
                <span>Export CSV</span>
            </button>

            <!-- Add Contact Primary Action -->
            <button
                type="button"
                onclick="window.openContactModal('create')"
                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm transition-colors cursor-pointer"
            >
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Add Contact</span>
            </button>
        </div>
    </div>

    <!-- Top KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 border border-zinc-200 dark:border-zinc-800 shadow-xs">
            <div class="text-[11px] font-semibold text-zinc-500 uppercase tracking-wider">TOTAL STAKEHOLDERS</div>
            <div id="kpi-total-contacts" class="text-2xl font-extrabold text-zinc-950 dark:text-zinc-100 mt-1 tracking-tight">
                {{ count($initialContacts ?? []) }} Contacts
            </div>
            <span class="text-[11px] text-emerald-600 font-semibold mt-1 inline-block">Direct Buying Authority</span>
        </div>

        <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 border border-zinc-200 dark:border-zinc-800 shadow-xs">
            <div class="text-[11px] font-semibold text-zinc-500 uppercase tracking-wider">DECISION MAKERS</div>
            <div id="kpi-decision-makers" class="text-2xl font-extrabold text-blue-600 mt-1 tracking-tight">
                0 Stakeholders
            </div>
            <span class="text-[11px] text-zinc-500 font-medium mt-1 inline-block">C-Level &amp; VP Sign-off</span>
        </div>

        <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 border border-zinc-200 dark:border-zinc-800 shadow-xs">
            <div class="text-[11px] font-semibold text-zinc-500 uppercase tracking-wider">CHAMPIONS</div>
            <div id="kpi-primary-champions" class="text-2xl font-extrabold text-emerald-600 mt-1 tracking-tight">
                0 Champions
            </div>
            <span class="text-[11px] text-zinc-500 font-medium mt-1 inline-block">Internal Solution Advocates</span>
        </div>

        <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 border border-zinc-200 dark:border-zinc-800 shadow-xs">
            <div class="text-[11px] font-semibold text-zinc-500 uppercase tracking-wider">EMAIL REACHABILITY</div>
            <div class="text-2xl font-extrabold text-zinc-950 dark:text-zinc-100 mt-1 tracking-tight">
                100%
            </div>
            <span class="text-[11px] text-emerald-600 font-semibold mt-1 inline-block">0 Opt-Out Suppressions</span>
        </div>
    </div>

    <!-- Buying Role Filter Chips Container -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1" id="contacts-role-chips-container">
        <!-- Rendered dynamically by contacts.js -->
    </div>

    <!-- Bulk Email Campaign Banner (Shows when contacts selected) -->
    <div id="contacts-bulk-email-banner" class="hidden flex items-center justify-between p-3 rounded-xl bg-pink-50 border border-pink-200 dark:bg-pink-950/20 dark:border-pink-900/40 text-xs">
        <div class="flex items-center gap-2 text-pink-900 dark:text-pink-300 font-semibold">
            <i data-lucide="megaphone" class="w-4 h-4 text-pink-600"></i>
            <span id="bulk-selected-count-label">0 contacts selected for bulk email</span>
        </div>
        <div class="flex items-center gap-2">
            <button
                type="button"
                onclick="window.clearSelectedContacts()"
                class="px-2.5 py-1 text-xs text-pink-700 dark:text-pink-300 hover:underline font-semibold cursor-pointer"
            >
                Deselect All
            </button>
            <button
                type="button"
                onclick="window.triggerBulkEmail()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-pink-600 hover:bg-pink-700 text-white text-xs font-semibold shadow-xs cursor-pointer"
            >
                <i data-lucide="mail" class="w-3.5 h-3.5"></i>
                <span>Broadcast Campaign</span>
            </button>
        </div>
    </div>

    <!-- Toolbar: Search, View Switcher (Table vs Kanban), Customize, Filter -->
    <div class="bg-white dark:bg-zinc-900 rounded-xl p-3 border border-zinc-200 dark:border-zinc-800 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-3">
        <!-- Left: Search Box -->
        <div class="flex items-center gap-3 w-full sm:w-auto flex-1 max-w-md">
            <div class="relative w-full">
                <i data-lucide="search" class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                <input
                    type="text"
                    id="contacts-search-input"
                    placeholder="Search contacts by name, email, account, title..."
                    class="w-full pl-9 pr-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/60 text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                >
            </div>
            <span id="contacts-records-count-label" class="text-xs text-zinc-500 dark:text-zinc-400 whitespace-nowrap">
                Showing <strong>0</strong> records
            </span>
        </div>

        <!-- Right: View Mode Toggle & Customize -->
        <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
            <!-- View Mode Switcher -->
            <div class="flex items-center p-0.5 rounded-lg bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-xs">
                <button
                    type="button"
                    id="btn-contact-view-table"
                    onclick="window.switchContactsViewMode('table')"
                    class="flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-md bg-white text-zinc-950 shadow-xs dark:bg-zinc-800 dark:text-zinc-100 cursor-pointer"
                >
                    <i data-lucide="table" class="w-3.5 h-3.5"></i>
                    <span>Table</span>
                </button>
                <button
                    type="button"
                    id="btn-contact-view-kanban"
                    onclick="window.switchContactsViewMode('kanban')"
                    class="flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-md text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-200 cursor-pointer"
                >
                    <i data-lucide="kanban" class="w-3.5 h-3.5"></i>
                    <span>Kanban</span>
                </button>
            </div>

            <!-- Customize Visible Fields Button -->
            <button
                type="button"
                onclick="window.openCustomizeFieldsModal()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs cursor-pointer"
                title="Customize Visible Columns / Fields"
            >
                <i data-lucide="sliders" class="w-3.5 h-3.5 text-zinc-500"></i>
                <span>Customize</span>
            </button>

            <!-- Filter Button in Toolbar -->
            <button
                type="button"
                onclick="window.openContactFilterModal()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs cursor-pointer"
            >
                <i data-lucide="filter" class="w-3.5 h-3.5 text-zinc-500"></i>
                <span>Filter</span>
            </button>
        </div>
    </div>

    <!-- Active Filter Chips Bar -->
    <div id="contacts-filter-chips-bar" class="hidden">
        <!-- Rendered dynamically by contacts.js -->
    </div>

    <!-- VIEW CONTAINER 1: Table View -->
    <div id="contacts-table-view-root">
        <!-- Rendered dynamically by contacts.js -->
    </div>

    <!-- VIEW CONTAINER 2: Kanban View -->
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
                        <i data-lucide="user" class="w-4 h-4"></i>
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
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form id="contact-record-form" onsubmit="window.handleContactFormSubmit(event)" class="p-6 space-y-5 max-h-[80vh] overflow-y-auto">
                <!-- Section 1: Contact Identity -->
                <div>
                    <h4 class="text-xs font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider mb-3 pb-1 border-b border-zinc-100 dark:border-zinc-800 flex items-center gap-1.5">
                        <i data-lucide="user" class="w-3.5 h-3.5 text-zinc-400"></i> Individual Information
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
                        <i data-lucide="building-2" class="w-3.5 h-3.5 text-zinc-400"></i> Account Organization &amp; Role
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
                            <i data-lucide="sliders" class="w-3.5 h-3.5 text-zinc-400"></i> Dynamic Custom Fields
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
                    <i data-lucide="user" class="w-4 h-4 text-indigo-600"></i>
                    <h3 id="preview-contact-name" class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Contact Details</h3>
                </div>
                <button type="button" onclick="window.closeContactPreviewModal()" class="text-zinc-400 hover:text-zinc-600 cursor-pointer">
                    ✕
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
    <!-- MODAL 3: CUSTOMIZE VISIBLE FIELDS MODAL     -->
    <!-- ========================================== -->
    <div id="contact-customize-modal" class="fixed inset-0 z-50 hidden bg-zinc-950/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white dark:bg-zinc-900 rounded-2xl max-w-lg w-full border border-zinc-200 dark:border-zinc-800 shadow-2xl overflow-hidden animate-scale-up">
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i data-lucide="sliders" class="w-4 h-4 text-indigo-600"></i>
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Customize Visible Fields</h3>
                </div>
                <button type="button" onclick="window.closeCustomizeFieldsModal()" class="text-zinc-400 hover:text-zinc-600 cursor-pointer">
                    ✕
                </button>
            </div>
            <div class="p-6 space-y-4">
                <p class="text-xs text-zinc-500">
                    Select standard and custom fields to display in your active view. Hidden fields are automatically excluded.
                </p>
                <div id="customize-fields-checkboxes-container" class="grid grid-cols-2 gap-2 max-h-64 overflow-y-auto p-1">
                    <!-- Populated dynamically by contacts.js -->
                </div>
                <div class="flex items-center justify-end gap-3 pt-3 border-t border-zinc-200 dark:border-zinc-800">
                    <button
                        type="button"
                        onclick="window.closeCustomizeFieldsModal()"
                        class="px-3.5 py-1.5 text-xs font-semibold text-zinc-600 hover:bg-zinc-100 rounded-lg transition-colors cursor-pointer"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        onclick="window.saveCustomizedFields()"
                        class="px-4 py-1.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm transition-colors cursor-pointer"
                    >
                        Save View Layout
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- MODAL 4: UNIVERSAL FILTER MODAL            -->
    <!-- ========================================== -->
    <div id="contact-filter-modal" class="fixed inset-0 z-50 hidden bg-zinc-950/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white dark:bg-zinc-900 rounded-2xl max-w-md w-full border border-zinc-200 dark:border-zinc-800 shadow-2xl overflow-hidden animate-scale-up">
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i data-lucide="filter" class="w-4 h-4 text-blue-600"></i>
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Add Contact Filter Rule</h3>
                </div>
                <button type="button" onclick="window.closeContactFilterModal()" class="text-zinc-400 hover:text-zinc-600 cursor-pointer">
                    ✕
                </button>
            </div>
            <div class="p-6 space-y-4 text-xs">
                <div class="space-y-1">
                    <label class="font-semibold text-zinc-700 dark:text-zinc-300">Filter By Field</label>
                    <select
                        id="filter-rule-field-select"
                        class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100"
                    >
                        <!-- Populated by contacts.js -->
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="font-semibold text-zinc-700 dark:text-zinc-300">Comparison Operator</label>
                    <select
                        id="filter-rule-operator-select"
                        class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100"
                    >
                        <option value="contains">contains</option>
                        <option value="equals">equals exactly</option>
                        <option value="starts_with">starts with</option>
                        <option value="greater_than">greater than (&gt;)</option>
                        <option value="less_than">less than (&lt;)</option>
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="font-semibold text-zinc-700 dark:text-zinc-300">Value</label>
                    <input
                        type="text"
                        id="filter-rule-value-input"
                        placeholder="Enter filter value..."
                        class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100"
                    >
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-zinc-200 dark:border-zinc-800">
                    <button
                        type="button"
                        onclick="window.closeContactFilterModal()"
                        class="px-3.5 py-1.5 text-xs font-semibold text-zinc-600 hover:bg-zinc-100 rounded-lg cursor-pointer"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        onclick="window.addContactFilterRule()"
                        class="px-4 py-1.5 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm cursor-pointer"
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
<script src="{{ asset('js/contacts.js') }}?v=1.0"></script>
@endsection
