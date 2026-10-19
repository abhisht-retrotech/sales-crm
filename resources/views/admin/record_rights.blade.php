@extends('layouts.app')

@section('title', 'Record Rights (CRUD Matrix) — DealSync Admin')

@section('content')
<div id="recordRightsContainer" class="max-w-7xl mx-auto pb-14 space-y-5 animate-fade-in">
    <!-- Live Action Feedback Toast -->
    <div id="rrFeedbackBanner" class="hidden items-center gap-2.5 p-3.5 rounded-xl text-xs font-semibold shadow-xs transition-all">
        <i id="rrFeedbackIcon" data-lucide="check-circle-2" class="w-4 h-4 shrink-0"></i>
        <span id="rrFeedbackText"></span>
    </div>

    <!-- Governance Bar & Profile Switcher Header -->
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-5 shadow-xs transition-colors">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-950/70 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0 border border-indigo-100 dark:border-indigo-900/50">
                        <i data-lucide="shield" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h2 class="text-base lg:text-lg font-bold text-zinc-900 dark:text-zinc-100 tracking-tight">
                            Record-Level Rights Matrix (CRUD &amp; Object Visibility)
                        </h2>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                            Assign or disable objects for any security profile. Disabled objects are completely hidden from navigation for users assigned to that profile.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3 mt-3.5 flex-wrap">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Configuring Profile:</span>
                        <select
                            id="selectedProfileSelect"
                            onchange="window.handleProfileChange(this.value)"
                            class="text-xs font-semibold py-1.5 px-3 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 min-w-[240px] shadow-2xs"
                        >
                            @foreach($profiles as $p)
                                <option value="{{ $p->id }}" {{ (string)$p->id === (string)$selectedProfileId ? 'selected' : '' }}>
                                    {{ $p->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div id="profileGovernanceBadge" class="inline-flex items-center">
                        @php
                            $currProf = $profiles->firstWhere('id', $selectedProfileId) ?? $profiles->first();
                            $isAdminProf = $currProf && ($currProf->is_admin || (string)$currProf->id === 'prof-admin');
                        @endphp
                        @if($isAdminProf)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                                <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 animate-pulse"></span>
                                👑 Full Organization Administrator (Full Access)
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-zinc-400"></span>
                                Custom Access Profile
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Action Buttons Toolbar -->
            <div class="flex items-center gap-2 flex-wrap">
                <button
                    type="button"
                    onclick="window.openCloneModal()"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 text-xs font-semibold hover:bg-zinc-50 dark:hover:bg-zinc-700/60 shadow-2xs transition"
                    title="Clone permissions from another security profile"
                >
                    <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                    <span>Clone Rights...</span>
                </button>

                <button
                    type="button"
                    onclick="window.openResetModal()"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-red-200 dark:border-red-900/50 bg-red-50/50 dark:bg-red-950/40 text-red-700 dark:text-red-300 text-xs font-semibold hover:bg-red-100/70 dark:hover:bg-red-900/50 shadow-2xs transition"
                    title="Reset permissions to system baseline defaults"
                >
                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                    <span>Reset Defaults</span>
                </button>

                <button
                    type="button"
                    onclick="window.openAddCustomModal()"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 text-xs font-semibold hover:bg-zinc-50 dark:hover:bg-zinc-700/60 shadow-2xs transition"
                    title="Add a custom object or module permission right"
                >
                    <i data-lucide="plus-circle" class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400"></i>
                    <span>+ Add Custom Object</span>
                </button>

                <button
                    type="button"
                    id="saveMatrixBtn"
                    onclick="window.handleSavePermissions()"
                    class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-xs hover:shadow transition"
                >
                    <i id="saveMatrixIcon" data-lucide="save" class="w-3.5 h-3.5"></i>
                    <span id="saveMatrixText">Save Matrix</span>
                </button>
            </div>
        </div>
    </div>

    @php
        $totalModulesCount = count($modules) + count($customObjects);
        $initPermCollection = collect($initialPermissions ?? []);
        $createPermCount = $initPermCollection->where('can_create', 1)->count();
        $deletePermCount = $initPermCollection->where('can_delete', 1)->count();
        $transferPermCount = $initPermCollection->where('can_transfer', 1)->count();
        $scopeAllCount = $initPermCollection->where('record_scope', 'All')->count();
        $scopeTeamCount = $initPermCollection->where('record_scope', 'Team')->count();
        $scopeOwnCount = $initPermCollection->where('record_scope', 'Own')->count();
    @endphp

    <!-- KPI Metric Ribbon -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3.5">
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-3.5 shadow-2xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                <i data-lucide="layers" class="w-5 h-5"></i>
            </div>
            <div>
                <div class="text-[11px] font-medium text-zinc-500 dark:text-zinc-400">Active Objects</div>
                <div id="kpiTotalModules" class="text-lg font-bold text-zinc-900 dark:text-zinc-100 leading-tight">{{ $totalModulesCount }}</div>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-3.5 shadow-2xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                <i data-lucide="plus-square" class="w-5 h-5"></i>
            </div>
            <div>
                <div class="text-[11px] font-medium text-zinc-500 dark:text-zinc-400">Create Permitted</div>
                <div id="kpiCreateCount" class="text-lg font-bold text-emerald-600 dark:text-emerald-400 leading-tight">{{ $createPermCount }} / {{ $totalModulesCount }}</div>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-3.5 shadow-2xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-red-50 dark:bg-red-950/60 text-red-600 dark:text-red-400 flex items-center justify-center shrink-0">
                <i data-lucide="trash-2" class="w-5 h-5"></i>
            </div>
            <div>
                <div class="text-[11px] font-medium text-zinc-500 dark:text-zinc-400">Delete Permitted</div>
                <div id="kpiDeleteCount" class="text-lg font-bold text-red-600 dark:text-red-400 leading-tight">{{ $deletePermCount }} / {{ $totalModulesCount }}</div>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-3.5 shadow-2xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                <i data-lucide="user-check" class="w-5 h-5"></i>
            </div>
            <div>
                <div class="text-[11px] font-medium text-zinc-500 dark:text-zinc-400">Transfer Authorized</div>
                <div id="kpiTransferCount" class="text-lg font-bold text-blue-600 dark:text-blue-400 leading-tight">{{ $transferPermCount }} / {{ $totalModulesCount }}</div>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-3.5 shadow-2xs flex items-center gap-3 col-span-2 md:col-span-1">
            <div class="w-10 h-10 rounded-lg bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0">
                <i data-lucide="eye" class="w-5 h-5"></i>
            </div>
            <div>
                <div class="text-[11px] font-medium text-zinc-500 dark:text-zinc-400">Scope Balance</div>
                <div id="kpiScopeBreakdown" class="text-xs font-semibold text-zinc-800 dark:text-zinc-200 leading-tight mt-0.5">
                    All: {{ $scopeAllCount }} | Team: {{ $scopeTeamCount }} | Own: {{ $scopeOwnCount }}
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Email Sending Authority & Quota Card (SettingsView.tsx lines 2980-3056) -->
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-4 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4 transition-colors">
        <div class="flex items-center gap-3.5">
            <div class="w-9 h-9 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0 border border-blue-100 dark:border-blue-900/50">
                <i data-lucide="mail" class="w-5 h-5"></i>
            </div>
            <div>
                <div class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                    Bulk Email Sending Authority
                </div>
                <div class="text-xs text-zinc-500 dark:text-zinc-400">
                    Controls whether users with this profile can view the Bulk Campaigns tab and dispatch bulk blasts to Leads and Contacts.
                </div>
            </div>
        </div>

        <div class="flex items-center gap-5 flex-wrap">
            <div class="flex items-center gap-2">
                <span class="text-xs font-medium text-zinc-600 dark:text-zinc-400">Daily Limit:</span>
                <input
                    type="number"
                    id="bulkEmailDailyLimitInput"
                    min="10"
                    max="10000"
                    value="{{ $currProf?->bulk_email_daily_limit ?? 500 }}"
                    onchange="window.handleBulkEmailDailyLimitChange(this.value)"
                    class="w-20 text-xs font-semibold py-1 px-2.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-2xs"
                    title="Maximum emails this profile can send per 24 hours"
                />
            </div>

            <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-semibold text-zinc-900 dark:text-zinc-100">
                <input
                    type="checkbox"
                    id="allowBulkSendingCheckbox"
                    {{ !empty($currProf?->can_send_bulk_email) ? 'checked' : '' }}
                    onchange="window.handleBulkEmailSendingChange(this.checked)"
                    class="w-4 h-4 rounded border-zinc-300 dark:border-zinc-700 text-blue-600 focus:ring-blue-500 cursor-pointer"
                />
                <span>Allow Bulk Sending</span>
            </label>
        </div>
    </div>

    <!-- Quick Presets, Filter Pills & Search Action Bar -->
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-4 shadow-xs space-y-3.5 transition-colors">
        <!-- Top Row: Quick Presets & Search Filter -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            <!-- Quick Presets Buttons (React SettingsView.tsx lines 2853-2887) -->
            <div class="flex items-center gap-2 flex-wrap">
                <span class="text-[11px] font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Quick Presets:</span>
                <button
                    type="button"
                    onclick="window.applyCoreDefaults()"
                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50 hover:bg-zinc-100 dark:bg-zinc-800 dark:hover:bg-zinc-700/70 text-indigo-700 dark:text-indigo-300 text-xs font-semibold shadow-2xs transition"
                    title="Enable standard 8 default objects (Dashboard, Leads, Accounts, Contacts, Opportunities, Tasks, Emails, Calendar) and disable extended objects"
                >
                    <i data-lucide="sparkles" class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400"></i>
                    <span>⚡ Set 8 Core Defaults</span>
                </button>

                <button
                    type="button"
                    onclick="window.enableAllModules()"
                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50 hover:bg-zinc-100 dark:bg-zinc-800 dark:hover:bg-zinc-700/70 text-emerald-700 dark:text-emerald-300 text-xs font-semibold shadow-2xs transition"
                    title="Enable visibility and read/create/update for all objects"
                >
                    <i data-lucide="check" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400"></i>
                    <span>✓ Enable All Objects</span>
                </button>

                <button
                    type="button"
                    onclick="window.disableExtendedModules()"
                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50 hover:bg-zinc-100 dark:bg-zinc-800 dark:hover:bg-zinc-700/70 text-red-700 dark:text-red-300 text-xs font-semibold shadow-2xs transition"
                    title="Disable all extended enterprise modules (Procurement, Shipping, Ledger, Invoices, Wires)"
                >
                    <i data-lucide="x" class="w-3.5 h-3.5 text-red-600 dark:text-red-400"></i>
                    <span>✕ Disable Extended Only</span>
                </button>
            </div>

            <!-- Search Filter Input (React SettingsView.tsx line 2960) -->
            <div class="relative w-full md:w-64">
                <i data-lucide="search" class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400 pointer-events-none"></i>
                <input
                    type="text"
                    id="recordRightsSearchInput"
                    placeholder="Filter objects..."
                    oninput="window.handleSearchChange(this.value)"
                    class="w-full text-xs font-medium pl-8 pr-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-2xs"
                />
            </div>
        </div>

        <!-- Middle Row: Filter Tabs Pills (React SettingsView.tsx lines 2891-2958) -->
        <div class="flex items-center justify-between gap-3 pt-2 border-t border-zinc-100 dark:border-zinc-800 flex-wrap">
            <div class="inline-flex items-center bg-zinc-100 dark:bg-zinc-800/80 p-1 rounded-xl gap-1">
                <button
                    type="button"
                    id="pill_all"
                    onclick="window.setRecordRightsFilter('all')"
                    class="px-3 py-1 text-xs font-semibold rounded-lg bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 shadow-xs transition"
                >
                    All (<span id="pillCountAll">{{ $totalModulesCount }}</span>)
                </button>
                <button
                    type="button"
                    id="pill_core"
                    onclick="window.setRecordRightsFilter('core')"
                    class="px-3 py-1 text-xs font-medium rounded-lg text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-200 transition"
                >
                    8 Core Defaults (<span id="pillCountCore">8</span>)
                </button>
                <button
                    type="button"
                    id="pill_extended"
                    onclick="window.setRecordRightsFilter('extended')"
                    class="px-3 py-1 text-xs font-medium rounded-lg text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-200 transition"
                >
                    Extended (<span id="pillCountExtended">6</span>)
                </button>
                @if(count($customObjects) > 0)
                <button
                    type="button"
                    id="pill_custom"
                    onclick="window.setRecordRightsFilter('custom')"
                    class="px-3 py-1 text-xs font-medium rounded-lg text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-200 transition"
                >
                    Custom (<span id="pillCountCustom">{{ count($customObjects) }}</span>)
                </button>
                @endif
                <button
                    type="button"
                    id="pill_full_crud"
                    onclick="window.setRecordRightsFilter('full_crud')"
                    class="px-3 py-1 text-xs font-medium rounded-lg text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-200 transition"
                >
                    Full CRUD
                </button>
                <button
                    type="button"
                    id="pill_read_only"
                    onclick="window.setRecordRightsFilter('read_only')"
                    class="px-3 py-1 text-xs font-medium rounded-lg text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-200 transition"
                >
                    Read-Only
                </button>
            </div>

            <!-- Column Batch Toggles & Quick Bulk Scopes -->
            <div class="flex items-center gap-2 flex-wrap">
                <span class="text-[11px] font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Column Toggles:</span>
                <button type="button" onclick="window.toggleColumnBatch('can_create')" class="px-2 py-0.5 text-[11px] font-semibold rounded border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700">C</button>
                <button type="button" onclick="window.toggleColumnBatch('can_read')" class="px-2 py-0.5 text-[11px] font-semibold rounded border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700">R</button>
                <button type="button" onclick="window.toggleColumnBatch('can_update')" class="px-2 py-0.5 text-[11px] font-semibold rounded border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700">U</button>
                <button type="button" onclick="window.toggleColumnBatch('can_delete')" class="px-2 py-0.5 text-[11px] font-semibold rounded border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700">D</button>
                <button type="button" onclick="window.toggleColumnBatch('can_transfer')" class="px-2 py-0.5 text-[11px] font-semibold rounded border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700">T</button>

                <select onchange="window.batchSetScope(this.value); this.value='';" class="text-[11px] font-semibold py-1 px-2 rounded border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 focus:outline-none">
                    <option value="" disabled selected>Bulk Set Scope</option>
                    <option value="All">All Records</option>
                    <option value="Team">Team Records</option>
                    <option value="Own">Own Records</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Rights Matrix Table Card (SettingsView.tsx lines 3058-3212) -->
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl shadow-xs overflow-hidden transition-colors">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-zinc-50 dark:bg-zinc-800/80 border-b border-zinc-200 dark:border-zinc-800 text-[11px] font-semibold text-zinc-600 dark:text-zinc-300 uppercase tracking-wider">
                        <th class="py-3 px-4 min-w-[240px]">Module / CRM Object</th>
                        <th class="py-3 px-3 text-center min-w-[170px]">Object Visibility / Access</th>
                        <th class="py-3 px-3 text-center w-24">Create (C)</th>
                        <th class="py-3 px-3 text-center w-24">Read (R)</th>
                        <th class="py-3 px-3 text-center w-24">Update (U)</th>
                        <th class="py-3 px-3 text-center w-24">Delete (D)</th>
                        <th class="py-3 px-3 text-center w-28">Transfer</th>
                        <th class="py-3 px-3 w-44">Record Scope</th>
                        <th class="py-3 px-4 text-right w-24">Actions</th>
                    </tr>
                </thead>
                <tbody id="matrixTableBody" class="divide-y divide-zinc-200/80 dark:divide-zinc-800/80">
                    @php
                        $moduleDefs = [
                            'dashboard'      => ['name' => 'Dashboard', 'category' => 'core', 'icon' => 'layout-dashboard', 'desc' => 'Executive KPIs, analytics charts, and operational widgets'],
                            'leads'          => ['name' => 'Leads', 'category' => 'core', 'icon' => 'users-2', 'desc' => 'Inbound prospects, qualification stages, and conversion engine'],
                            'accounts'       => ['name' => 'Accounts', 'category' => 'core', 'icon' => 'building-2', 'desc' => 'Companies, organizations, customers, and vendor partners'],
                            'contacts'       => ['name' => 'Contacts', 'category' => 'core', 'icon' => 'contact', 'desc' => 'Individual stakeholder contacts and communication directory'],
                            'opportunities'  => ['name' => 'Opportunities (Sales Pipeline)', 'category' => 'core', 'icon' => 'trending-up', 'desc' => 'Sales pipeline, forecast stages, and deal values'],
                            'tasks'          => ['name' => 'Tasks', 'category' => 'core', 'icon' => 'check-square', 'desc' => 'Activities, reminders, follow-ups, and recurring tasks'],
                            'emails'         => ['name' => 'Emails', 'category' => 'core', 'icon' => 'mail', 'desc' => 'Unified inbox, compose, templates, and outbound correspondence'],
                            'calendar'       => ['name' => 'Calendar', 'category' => 'core', 'icon' => 'calendar', 'desc' => 'Meetings, customer bookings, sync feeds, and scheduling'],
                            'purchase_deals' => ['name' => 'Deals (Purchase & Procurement)', 'category' => 'extended', 'icon' => 'shopping-cart', 'desc' => 'Procurement orders, vendor agreements, and PO commitments'],
                            'shipping'       => ['name' => 'Shipping & Logistics', 'category' => 'extended', 'icon' => 'truck', 'desc' => 'Freight dispatch and customs tracking'],
                            'documents'      => ['name' => 'Billing & Ledger', 'category' => 'extended', 'icon' => 'file-text', 'desc' => 'Financial invoices, payment receipts, and ledger audit'],
                            'payments'       => ['name' => 'Payments & Invoicing (AR / AP)', 'category' => 'extended', 'icon' => 'credit-card', 'desc' => 'Billing records, accounts receivable, and collections'],
                            'wires'          => ['name' => 'Wire Received & Bank Reconciliation', 'category' => 'extended', 'icon' => 'landmark', 'desc' => 'Bank wire transfers, UTR confirmations, and reconciliation'],
                            'users'          => ['name' => 'Users (Active User Directory)', 'category' => 'extended', 'icon' => 'shield', 'desc' => 'System user directory and employee profile management'],
                        ];
                        $permMap = collect($initialPermissions ?? [])->keyBy('module');
                    @endphp

                    @foreach($modules as $modKey => $modLabel)
                        @php
                            $perm = $permMap->get($modKey);
                            $def = $moduleDefs[$modKey] ?? ['name' => $modLabel, 'category' => 'extended', 'icon' => 'layers', 'desc' => 'CRM Module'];
                            $isCore = ($def['category'] ?? '') === 'core';
                            $canRead = $perm ? (bool)$perm['can_read'] : $isCore;
                            $canCreate = $perm ? (bool)$perm['can_create'] : $canRead;
                            $canUpdate = $perm ? (bool)$perm['can_update'] : $canRead;
                            $canDelete = $perm ? (bool)$perm['can_delete'] : false;
                            $canTransfer = $perm ? (bool)$perm['can_transfer'] : false;
                            $scope = $perm['record_scope'] ?? 'All';
                        @endphp
                        <tr id="row-{{ $modKey }}" class="border-b border-zinc-200/80 dark:border-zinc-800/80 {{ $canRead ? 'hover:bg-zinc-50/70 dark:hover:bg-zinc-800/40 transition-colors' : 'opacity-65 bg-zinc-50/60 dark:bg-zinc-900/60' }}">
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 {{ $isCore ? 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400' }}">
                                        <i data-lucide="{{ $def['icon'] ?? 'layers' }}" class="w-4 h-4"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <strong class="text-xs font-semibold text-zinc-900 dark:text-zinc-100">{{ $def['name'] ?? $modLabel }}</strong>
                                            @if($isCore)
                                                <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-semibold bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                                    Core Default
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-medium bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700">
                                                    Extended
                                                </span>
                                            @endif
                                        </div>
                                        @if(!empty($def['desc']))
                                            <div class="text-[11px] text-zinc-500 dark:text-zinc-400 truncate max-w-xs mt-0.5">{{ $def['desc'] }}</div>
                                        @endif
                                        <div class="text-[10px] font-mono text-zinc-400 dark:text-zinc-500 mt-0.5">{{ $modKey }}</div>
                                    </div>
                                </div>
                            </td>

                            <td class="py-3 px-3 text-center whitespace-nowrap">
                                <button
                                    type="button"
                                    onclick="window.toggleModuleVisibility('{{ $modKey }}', {{ $canRead ? 'false' : 'true' }})"
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold transition border shadow-xs {{ $canRead ? 'bg-emerald-50 hover:bg-emerald-100 text-emerald-700 dark:bg-emerald-950/50 dark:hover:bg-emerald-900/60 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800' : 'bg-red-50 hover:bg-red-100 text-red-700 dark:bg-red-950/50 dark:hover:bg-red-900/60 dark:text-red-300 border-red-200 dark:border-red-800' }}"
                                >
                                    <i data-lucide="{{ $canRead ? 'eye' : 'eye-off' }}" class="w-3.5 h-3.5"></i>
                                    <span>{{ $canRead ? 'Visible in Sidebar' : 'Disabled / Hidden' }}</span>
                                </button>
                            </td>

                            <td class="py-3 px-3 text-center">
                                <input
                                    type="checkbox"
                                    {{ $canCreate ? 'checked' : '' }}
                                    {{ !$canRead ? 'disabled' : '' }}
                                    onchange="window.updateRecordPerm('{{ $modKey }}', 'can_create', this.checked)"
                                    class="w-4 h-4 rounded border-zinc-300 dark:border-zinc-700 text-indigo-600 focus:ring-indigo-500 transition {{ $canRead ? 'cursor-pointer' : 'cursor-not-allowed opacity-50' }}"
                                />
                            </td>

                            <td class="py-3 px-3 text-center">
                                <input
                                    type="checkbox"
                                    {{ $canRead ? 'checked' : '' }}
                                    onchange="window.toggleModuleVisibility('{{ $modKey }}', this.checked)"
                                    class="w-4 h-4 rounded border-zinc-300 dark:border-zinc-700 text-indigo-600 focus:ring-indigo-500 cursor-pointer transition"
                                />
                            </td>

                            <td class="py-3 px-3 text-center">
                                <input
                                    type="checkbox"
                                    {{ $canUpdate ? 'checked' : '' }}
                                    {{ !$canRead ? 'disabled' : '' }}
                                    onchange="window.updateRecordPerm('{{ $modKey }}', 'can_update', this.checked)"
                                    class="w-4 h-4 rounded border-zinc-300 dark:border-zinc-700 text-indigo-600 focus:ring-indigo-500 transition {{ $canRead ? 'cursor-pointer' : 'cursor-not-allowed opacity-50' }}"
                                />
                            </td>

                            <td class="py-3 px-3 text-center">
                                <input
                                    type="checkbox"
                                    {{ $canDelete ? 'checked' : '' }}
                                    {{ !$canRead ? 'disabled' : '' }}
                                    onchange="window.updateRecordPerm('{{ $modKey }}', 'can_delete', this.checked)"
                                    class="w-4 h-4 rounded border-zinc-300 dark:border-zinc-700 text-red-600 focus:ring-red-500 transition {{ $canRead ? 'cursor-pointer' : 'cursor-not-allowed opacity-50' }}"
                                />
                            </td>

                            <td class="py-3 px-3 text-center">
                                <input
                                    type="checkbox"
                                    {{ $canTransfer ? 'checked' : '' }}
                                    {{ !$canRead ? 'disabled' : '' }}
                                    onchange="window.updateRecordPerm('{{ $modKey }}', 'can_transfer', this.checked)"
                                    class="w-4 h-4 rounded border-zinc-300 dark:border-zinc-700 text-blue-600 focus:ring-blue-500 transition {{ $canRead ? 'cursor-pointer' : 'cursor-not-allowed opacity-50' }}"
                                />
                            </td>

                            <td class="py-3 px-3">
                                <select
                                    {{ !$canRead ? 'disabled' : '' }}
                                    onchange="window.updateRecordScope('{{ $modKey }}', this.value)"
                                    class="w-full text-xs font-medium py-1.5 px-2.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition shadow-2xs {{ !$canRead ? 'cursor-not-allowed opacity-50' : 'cursor-pointer' }}"
                                >
                                    <option value="All" {{ $scope === 'All' ? 'selected' : '' }}>🌐 All Records</option>
                                    <option value="Team" {{ $scope === 'Team' ? 'selected' : '' }}>👥 Team Records Only</option>
                                    <option value="Own" {{ $scope === 'Own' ? 'selected' : '' }}>👤 Own Records Only</option>
                                </select>
                            </td>

                            <td class="py-3 px-4 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5 justify-end">
                                    <button
                                        type="button"
                                        onclick="window.handleSaveSingleRow('{{ $modKey }}', this)"
                                        class="p-1.5 rounded-lg border border-zinc-200 dark:border-zinc-700 text-zinc-600 hover:text-emerald-600 hover:border-emerald-300 dark:text-zinc-400 dark:hover:text-emerald-400 bg-white dark:bg-zinc-800 transition"
                                        title="Save permissions for this module"
                                    >
                                        <i data-lucide="check" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach

                    @foreach($customObjects as $co)
                        @php
                            $cKey = 'obj_' . $co->api_name;
                            $perm = $permMap->get($cKey);
                            $canRead = $perm ? (bool)$perm['can_read'] : true;
                            $canCreate = $perm ? (bool)$perm['can_create'] : true;
                            $canUpdate = $perm ? (bool)$perm['can_update'] : true;
                            $canDelete = $perm ? (bool)$perm['can_delete'] : false;
                            $canTransfer = $perm ? (bool)$perm['can_transfer'] : false;
                            $scope = $perm['record_scope'] ?? 'All';
                        @endphp
                        <tr id="row-{{ $cKey }}" class="border-b border-zinc-200/80 dark:border-zinc-800/80 {{ $canRead ? 'hover:bg-zinc-50/70 dark:hover:bg-zinc-800/40 transition-colors' : 'opacity-65 bg-zinc-50/60 dark:bg-zinc-900/60' }}">
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 bg-purple-100 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400">
                                        <i data-lucide="box" class="w-4 h-4"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <strong class="text-xs font-semibold text-zinc-900 dark:text-zinc-100">{{ $co->label }} (Custom Object)</strong>
                                            <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-semibold bg-purple-50 dark:bg-purple-950/50 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800">
                                                Custom
                                            </span>
                                        </div>
                                        <div class="text-[10px] font-mono text-zinc-400 dark:text-zinc-500 mt-0.5">{{ $cKey }}</div>
                                    </div>
                                </div>
                            </td>

                            <td class="py-3 px-3 text-center whitespace-nowrap">
                                <button
                                    type="button"
                                    onclick="window.toggleModuleVisibility('{{ $cKey }}', {{ $canRead ? 'false' : 'true' }})"
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold transition border shadow-xs {{ $canRead ? 'bg-emerald-50 hover:bg-emerald-100 text-emerald-700 dark:bg-emerald-950/50 dark:hover:bg-emerald-900/60 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800' : 'bg-red-50 hover:bg-red-100 text-red-700 dark:bg-red-950/50 dark:hover:bg-red-900/60 dark:text-red-300 border-red-200 dark:border-red-800' }}"
                                >
                                    <i data-lucide="{{ $canRead ? 'eye' : 'eye-off' }}" class="w-3.5 h-3.5"></i>
                                    <span>{{ $canRead ? 'Visible in Sidebar' : 'Disabled / Hidden' }}</span>
                                </button>
                            </td>

                            <td class="py-3 px-3 text-center">
                                <input
                                    type="checkbox"
                                    {{ $canCreate ? 'checked' : '' }}
                                    {{ !$canRead ? 'disabled' : '' }}
                                    onchange="window.updateRecordPerm('{{ $cKey }}', 'can_create', this.checked)"
                                    class="w-4 h-4 rounded border-zinc-300 dark:border-zinc-700 text-indigo-600 focus:ring-indigo-500 transition {{ $canRead ? 'cursor-pointer' : 'cursor-not-allowed opacity-50' }}"
                                />
                            </td>

                            <td class="py-3 px-3 text-center">
                                <input
                                    type="checkbox"
                                    {{ $canRead ? 'checked' : '' }}
                                    onchange="window.toggleModuleVisibility('{{ $cKey }}', this.checked)"
                                    class="w-4 h-4 rounded border-zinc-300 dark:border-zinc-700 text-indigo-600 focus:ring-indigo-500 cursor-pointer transition"
                                />
                            </td>

                            <td class="py-3 px-3 text-center">
                                <input
                                    type="checkbox"
                                    {{ $canUpdate ? 'checked' : '' }}
                                    {{ !$canRead ? 'disabled' : '' }}
                                    onchange="window.updateRecordPerm('{{ $cKey }}', 'can_update', this.checked)"
                                    class="w-4 h-4 rounded border-zinc-300 dark:border-zinc-700 text-indigo-600 focus:ring-indigo-500 transition {{ $canRead ? 'cursor-pointer' : 'cursor-not-allowed opacity-50' }}"
                                />
                            </td>

                            <td class="py-3 px-3 text-center">
                                <input
                                    type="checkbox"
                                    {{ $canDelete ? 'checked' : '' }}
                                    {{ !$canRead ? 'disabled' : '' }}
                                    onchange="window.updateRecordPerm('{{ $cKey }}', 'can_delete', this.checked)"
                                    class="w-4 h-4 rounded border-zinc-300 dark:border-zinc-700 text-red-600 focus:ring-red-500 transition {{ $canRead ? 'cursor-pointer' : 'cursor-not-allowed opacity-50' }}"
                                />
                            </td>

                            <td class="py-3 px-3 text-center">
                                <input
                                    type="checkbox"
                                    {{ $canTransfer ? 'checked' : '' }}
                                    {{ !$canRead ? 'disabled' : '' }}
                                    onchange="window.updateRecordPerm('{{ $cKey }}', 'can_transfer', this.checked)"
                                    class="w-4 h-4 rounded border-zinc-300 dark:border-zinc-700 text-blue-600 focus:ring-blue-500 transition {{ $canRead ? 'cursor-pointer' : 'cursor-not-allowed opacity-50' }}"
                                />
                            </td>

                            <td class="py-3 px-3">
                                <select
                                    {{ !$canRead ? 'disabled' : '' }}
                                    onchange="window.updateRecordScope('{{ $cKey }}', this.value)"
                                    class="w-full text-xs font-medium py-1.5 px-2.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition shadow-2xs {{ !$canRead ? 'cursor-not-allowed opacity-50' : 'cursor-pointer' }}"
                                >
                                    <option value="All" {{ $scope === 'All' ? 'selected' : '' }}>🌐 All Records</option>
                                    <option value="Team" {{ $scope === 'Team' ? 'selected' : '' }}>👥 Team Records Only</option>
                                    <option value="Own" {{ $scope === 'Own' ? 'selected' : '' }}>👤 Own Records Only</option>
                                </select>
                            </td>

                            <td class="py-3 px-4 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5 justify-end">
                                    <button
                                        type="button"
                                        onclick="window.handleSaveSingleRow('{{ $cKey }}', this)"
                                        class="p-1.5 rounded-lg border border-zinc-200 dark:border-zinc-700 text-zinc-600 hover:text-emerald-600 hover:border-emerald-300 dark:text-zinc-400 dark:hover:text-emerald-400 bg-white dark:bg-zinc-800 transition"
                                        title="Save permissions for this module"
                                    >
                                        <i data-lucide="check" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400"></i>
                                    </button>
                                    <button
                                        type="button"
                                        onclick="window.handleDeleteCustomRow('{{ $cKey }}')"
                                        class="p-1.5 rounded-lg border border-zinc-200 dark:border-zinc-700 text-zinc-600 hover:text-red-600 hover:border-red-300 dark:text-zinc-400 dark:hover:text-red-400 bg-white dark:bg-zinc-800 transition"
                                        title="Remove custom object permission from profile"
                                    >
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5 text-red-600 dark:text-red-400"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- CLONE PROFILE PERMISSIONS MODAL                                           -->
<!-- ========================================================================= -->
<div id="cloneModal" class="rr-modal-overlay hidden fixed inset-0 z-50 bg-black/60 backdrop-blur-xs items-center justify-center p-4">
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl w-full max-w-md shadow-2xl overflow-hidden animate-fade-in">
        <div class="flex items-center justify-between px-5 py-4 border-b border-zinc-100 dark:border-zinc-800">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                    <i data-lucide="copy" class="w-4 h-4"></i>
                </div>
                <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Clone Rights from Profile</h3>
            </div>
            <button type="button" onclick="window.closeCloneModal()" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 p-1">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form onsubmit="window.handleCloneSubmit(event)" class="p-5 space-y-4">
            <div>
                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">
                    Source Security Profile:
                </label>
                <select id="cloneSourceProfileSelect" required class="w-full text-xs font-semibold py-2 px-3 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-2xs">
                    <!-- Populated dynamically -->
                </select>
                <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-1">
                    All module CRUD rights, visibility switches, and record scopes will be cloned to the selected profile.
                </p>
            </div>

            <label class="flex items-start gap-2.5 cursor-pointer">
                <input type="checkbox" id="cloneIncludeFlsCheckbox" class="mt-0.5 w-4 h-4 rounded border-zinc-300 dark:border-zinc-700 text-indigo-600 focus:ring-indigo-500">
                <span class="text-xs text-zinc-700 dark:text-zinc-300">
                    <strong>Also clone Field-Level Security (FLS)</strong> permissions across all standard and custom fields.
                </span>
            </label>

            <div class="flex justify-end gap-2 pt-3 border-t border-zinc-100 dark:border-zinc-800">
                <button type="button" onclick="window.closeCloneModal()" class="px-3.5 py-1.5 text-xs font-semibold rounded-lg border border-zinc-200 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-800">Cancel</button>
                <button type="submit" id="cloneSubmitBtn" class="px-4 py-1.5 text-xs font-semibold rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white shadow-xs">
                    <span id="cloneSubmitText">Apply &amp; Clone Rights</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- ADD CUSTOM OBJECT RECORD RIGHT MODAL                                      -->
<!-- ========================================================================= -->
<div id="addCustomModal" class="rr-modal-overlay hidden fixed inset-0 z-50 bg-black/60 backdrop-blur-xs items-center justify-center p-4">
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl w-full max-w-md shadow-2xl overflow-hidden animate-fade-in">
        <div class="flex items-center justify-between px-5 py-4 border-b border-zinc-100 dark:border-zinc-800">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                </div>
                <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Add Custom Object Record Right</h3>
            </div>
            <button type="button" onclick="window.closeAddCustomModal()" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 p-1">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form onsubmit="window.handleAddCustomSubmit(event)" class="p-5 space-y-4">
            <div>
                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">
                    Select or Type Custom Module Slug:
                </label>
                <select id="customObjectPresetSelect" onchange="window.handleCustomPresetChange(this.value)" class="w-full text-xs font-medium py-1.5 px-3 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 mb-2">
                    <!-- Populated dynamically -->
                </select>
                <input
                    type="text"
                    id="newCustomModuleKey"
                    placeholder="e.g. obj_contracts, obj_warehouses..."
                    required
                    class="w-full text-xs font-medium py-2 px-3 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                />
            </div>

            <div>
                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-2">
                    Initial Record Permissions:
                </label>
                <div class="grid grid-cols-2 gap-2 text-xs text-zinc-700 dark:text-zinc-300">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" id="newModCreate" checked class="w-4 h-4 rounded text-indigo-600"> Can Create
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" id="newModRead" checked class="w-4 h-4 rounded text-indigo-600"> Can Read
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" id="newModUpdate" checked class="w-4 h-4 rounded text-indigo-600"> Can Update
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" id="newModDelete" class="w-4 h-4 rounded text-red-600"> Can Delete
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer col-span-2">
                        <input type="checkbox" id="newModTransfer" class="w-4 h-4 rounded text-blue-600"> Can Transfer Ownership
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">
                    Record Visibility Scope:
                </label>
                <select id="newModScope" class="w-full text-xs font-medium py-2 px-3 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                    <option value="All">All Records (Global Visibility)</option>
                    <option value="Team">Team Records Only (Hierarchy Visibility)</option>
                    <option value="Own">Own Records Only (Private Ownership)</option>
                </select>
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t border-zinc-100 dark:border-zinc-800">
                <button type="button" onclick="window.closeAddCustomModal()" class="px-3.5 py-1.5 text-xs font-semibold rounded-lg border border-zinc-200 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-800">Cancel</button>
                <button type="submit" id="addCustomSubmitBtn" class="px-4 py-1.5 text-xs font-semibold rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white shadow-xs">
                    <span id="addCustomSubmitText">Register Module Rights</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- RESET TO DEFAULTS CONFIRMATION MODAL                                      -->
<!-- ========================================================================= -->
<div id="resetModal" class="rr-modal-overlay hidden fixed inset-0 z-50 bg-black/60 backdrop-blur-xs items-center justify-center p-4">
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl w-full max-w-sm shadow-2xl p-5 animate-fade-in space-y-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-red-50 dark:bg-red-950/60 text-red-600 dark:text-red-400 flex items-center justify-center shrink-0 border border-red-100 dark:border-red-900/50">
                <i data-lucide="alert-triangle" class="w-5 h-5"></i>
            </div>
            <div>
                <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Reset Permissions?</h3>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">Reverts all CRUD rights to baseline system defaults.</p>
            </div>
        </div>

        <p class="text-xs text-zinc-600 dark:text-zinc-400 leading-relaxed">
            Are you sure you want to reset <strong id="resetProfileNamePlaceholder" class="text-zinc-900 dark:text-zinc-100">this profile</strong>? Any custom module overrides will be restored to their standard tier settings.
        </p>

        <div class="flex justify-end gap-2 pt-2 border-t border-zinc-100 dark:border-zinc-800">
            <button type="button" onclick="window.closeResetModal()" class="px-3.5 py-1.5 text-xs font-semibold rounded-lg border border-zinc-200 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-800">Cancel</button>
            <button type="button" id="confirmResetBtn" onclick="window.handleConfirmReset()" class="px-4 py-1.5 text-xs font-semibold rounded-lg bg-red-600 hover:bg-red-700 text-white shadow-xs">
                <span id="confirmResetText">Reset to Defaults</span>
            </button>
        </div>
    </div>
</div>

<!-- Hydration Data & Dedicated External Script -->
<script>
    window.INITIAL_RECORD_RIGHTS_DATA = {
        profiles: @json($profiles ?? []),
        selectedProfileId: '{{ $selectedProfileId ?? "" }}',
        customObjects: @json($customObjects ?? []),
        initialPermissions: @json($initialPermissions ?? []),
        csrfToken: '{{ csrf_token() }}'
    };
</script>
<script src="{{ asset('js/record-rights.js') }}?v=1.2"></script>
@endsection
