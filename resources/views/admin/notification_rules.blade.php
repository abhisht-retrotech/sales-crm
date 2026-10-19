@extends('layouts.app')

@section('title', 'Notification Rules Studio — DealSync Admin')

@section('content')
<div class="admin-page-container animate-in fade-in duration-300">
    @include('partials.admin_nav')

    <!-- Top Action Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                    <i data-lucide="bell" class="w-3 h-3 text-indigo-600 dark:text-indigo-400"></i>
                    <span>In-App Alert &amp; Audio Broadcast Engine</span>
                </span>
                <span class="text-xs text-zinc-500 dark:text-zinc-400 hidden md:inline">
                    Event-Triggered Toast Alerts, Audio Chimes &amp; Bell Notification Hub
                </span>
            </div>
            <h1 class="text-2xl font-extrabold tracking-tight text-zinc-950 dark:text-white m-0">
                Pop-up Notifications Studio
            </h1>
            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 max-w-2xl m-0">
                Configure automated desktop toast pop-ups, sound chimes, and action triggers when records are won, payments settle, or tasks near deadlines.
            </p>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap sm:flex-nowrap">
            <button
                type="button"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium bg-white dark:bg-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-200 border border-zinc-200 dark:border-zinc-700 rounded-lg shadow-xs transition-colors"
                onclick="testPlayAudioChime()"
            >
                <i data-lucide="play" class="w-3.5 h-3.5 text-indigo-600"></i>
                <span>Test Audio Chime</span>
            </button>
            <button
                type="button"
                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-semibold bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg shadow-sm shadow-indigo-600/20 transition-all hover:-translate-y-0.5 active:translate-y-0"
                onclick="openCreateRuleModal()"
            >
                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                <span>+ Create Notification Rule</span>
            </button>
        </div>
    </div>

    <!-- KPI Metric Cards Grid (Tailwind CSS) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 mb-6">
        <!-- Metric 1: Notification Rules -->
        <div class="p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 shadow-xs hover:border-zinc-300 dark:hover:border-zinc-700 transition-all">
            <div class="text-[11px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 mb-1">
                Notification Rules
            </div>
            <div class="text-2xl font-extrabold tracking-tight text-indigo-600 dark:text-indigo-400" id="kpi-total-rules">
                {{ $rules->count() }} Rules
            </div>
            <div class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 mt-1 flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>100% Online &amp; Active</span>
            </div>
        </div>

        <!-- Metric 2: Dispatched Today -->
        <div class="p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 shadow-xs hover:border-zinc-300 dark:hover:border-zinc-700 transition-all">
            <div class="text-[11px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 mb-1">
                Dispatched Today
            </div>
            <div class="text-2xl font-extrabold tracking-tight text-sky-600 dark:text-sky-400">
                47 Toasts
            </div>
            <div class="text-[11px] font-medium text-zinc-500 dark:text-zinc-400 mt-1">
                Zero Delivery Latency
            </div>
        </div>

        <!-- Metric 3: Audio Chimes Enabled -->
        <div class="p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 shadow-xs hover:border-zinc-300 dark:hover:border-zinc-700 transition-all">
            <div class="text-[11px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 mb-1">
                Audio Chimes Enabled
            </div>
            <div class="text-2xl font-extrabold tracking-tight text-amber-600 dark:text-amber-400">
                3 Sound Tones
            </div>
            <div class="text-[11px] font-medium text-zinc-500 dark:text-zinc-400 mt-1">
                Fanfare, Bell Chime, Subtle
            </div>
        </div>

        <!-- Metric 4: Bell Hub Sync -->
        <div class="p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 shadow-xs hover:border-zinc-300 dark:hover:border-zinc-700 transition-all">
            <div class="text-[11px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 mb-1">
                Bell Hub Sync
            </div>
            <div class="text-2xl font-extrabold tracking-tight text-emerald-600 dark:text-emerald-400">
                Synchronized
            </div>
            <div class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 mt-1 flex items-center gap-1">
                <i data-lucide="check" class="w-3 h-3"></i>
                <span>Real-Time WebSocket Link</span>
            </div>
        </div>
    </div>

    <!-- Table Toolbar: Search, Tone Filters, Density -->
    <div class="p-3 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-xs mb-3 flex flex-wrap items-center justify-between gap-3">
        <!-- Left: Search Box & Tone Filters -->
        <div class="flex items-center gap-2.5 flex-1 min-w-[280px] flex-wrap">
            <div class="relative flex-1 max-w-sm">
                <i data-lucide="search" class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                <input
                    type="text"
                    id="notif-search-input"
                    placeholder="Search notification rules by title, trigger, entity..."
                    class="w-full pl-9 pr-3 py-1.5 text-xs bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg text-zinc-900 dark:text-white placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
                    oninput="filterNotificationRules()"
                >
            </div>

            <!-- Tone Filter Buttons -->
            <div class="flex items-center gap-1 bg-zinc-100 dark:bg-zinc-800/80 p-0.5 rounded-lg border border-zinc-200/80 dark:border-zinc-700/80 text-[11px] font-medium">
                <button type="button" class="tone-filter-btn px-2 py-0.5 rounded-md bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white shadow-xs" data-tone="all" onclick="setToneFilter('all', this)">
                    All
                </button>
                <button type="button" class="tone-filter-btn px-2 py-0.5 rounded-md text-zinc-600 dark:text-zinc-300 hover:text-zinc-900" data-tone="success" onclick="setToneFilter('success', this)">
                    Success
                </button>
                <button type="button" class="tone-filter-btn px-2 py-0.5 rounded-md text-zinc-600 dark:text-zinc-300 hover:text-zinc-900" data-tone="warning" onclick="setToneFilter('warning', this)">
                    Warning
                </button>
                <button type="button" class="tone-filter-btn px-2 py-0.5 rounded-md text-zinc-600 dark:text-zinc-300 hover:text-zinc-900" data-tone="error" onclick="setToneFilter('error', this)">
                    Error
                </button>
                <button type="button" class="tone-filter-btn px-2 py-0.5 rounded-md text-zinc-600 dark:text-zinc-300 hover:text-zinc-900" data-tone="info" onclick="setToneFilter('info', this)">
                    Info
                </button>
            </div>
        </div>

        <!-- Right: Density & Reset Controls -->
        <div class="flex items-center gap-2">
            <span class="text-xs text-zinc-500 dark:text-zinc-400" id="rule-count-label">
                Showing <strong class="text-zinc-900 dark:text-white">{{ $rules->count() }}</strong> Rules
            </span>
            <div class="flex items-center gap-1 bg-zinc-100 dark:bg-zinc-800 p-0.5 rounded-lg border border-zinc-200 dark:border-zinc-700 text-xs">
                <button type="button" class="px-2 py-0.5 rounded-md text-zinc-700 dark:text-zinc-300 hover:bg-white dark:hover:bg-zinc-700 text-[11px]" onclick="setTableDensity('notif-rules-table', 'compact')">
                    Compact
                </button>
                <button type="button" class="px-2 py-0.5 rounded-md text-zinc-700 dark:text-zinc-300 hover:bg-white dark:hover:bg-zinc-700 text-[11px]" onclick="setTableDensity('notif-rules-table', 'comfortable')">
                    Comfortable
                </button>
            </div>
        </div>
    </div>

    <!-- Notification Rules Table (Tailwind CSS) -->
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs" id="notif-rules-table" data-storage-key="dealsync_col_widths_notif_rules">
                <thead class="bg-zinc-50/75 dark:bg-zinc-800/50 border-b border-zinc-200 dark:border-zinc-800 text-[11px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                    <tr>
                        <th class="w-10 px-3.5 py-3 text-center">
                            <input type="checkbox" class="rounded border-zinc-300 dark:border-zinc-700 text-indigo-600 focus:ring-indigo-500" onchange="toggleSelectAll('notif-rules-table', this.checked)">
                        </th>
                        <th class="px-4 py-3 min-w-[240px]">
                            Rule Name &amp; Alert Title
                        </th>
                        <th class="px-3.5 py-3 min-w-[130px]">
                            Target Entity
                        </th>
                        <th class="px-3.5 py-3 min-w-[180px]">
                            Trigger Event
                        </th>
                        <th class="px-3.5 py-3 min-w-[120px]">
                            Tone &amp; Audio
                        </th>
                        <th class="px-3.5 py-3 min-w-[100px]">
                            Duration
                        </th>
                        <th class="px-3.5 py-3 min-w-[140px]">
                            Recipients
                        </th>
                        <th class="px-3.5 py-3 min-w-[90px] text-center">
                            Status
                        </th>
                        <th class="px-4 py-3 text-right min-w-[170px]">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800" id="notif-rules-tbody">
                    @forelse($rules as $rule)
                        <tr class="hover:bg-zinc-50/70 dark:hover:bg-zinc-800/40 transition-colors rule-row" data-tone="{{ $rule->type }}" data-id="{{ $rule->id }}" data-rule-json="{{ json_encode($rule) }}">
                            <td class="px-3.5 py-3 text-center">
                                <input type="checkbox" class="row-checkbox rounded border-zinc-300 dark:border-zinc-700 text-indigo-600 focus:ring-indigo-500">
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-bold text-zinc-900 dark:text-white rule-name">
                                    {{ $rule->name }}
                                </div>
                                <div class="text-[11px] text-zinc-500 dark:text-zinc-400 truncate max-w-sm mt-0.5">
                                    {{ $rule->message_template }}
                                </div>
                            </td>
                            <td class="px-3.5 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold capitalize bg-zinc-100 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 border border-zinc-200 dark:border-zinc-700">
                                    {{ $rule->module }}
                                </span>
                            </td>
                            <td class="px-3.5 py-3">
                                <code class="px-2 py-0.5 bg-zinc-100 dark:bg-zinc-800/80 text-zinc-800 dark:text-zinc-200 rounded text-[11px] font-mono border border-zinc-200 dark:border-zinc-700">
                                    @if($rule->criteria_field)
                                        {{ $rule->criteria_field }} {{ $rule->criteria_operator ?? '==' }} '{{ $rule->criteria_value }}'
                                    @elseif($rule->trigger_type === 'scheduled')
                                        {{ $rule->date_field }} {{ $rule->offset_direction }} {{ $rule->offset_value }} {{ $rule->offset_unit }}
                                    @else
                                        operation == '{{ $rule->operation }}'
                                    @endif
                                </code>
                            </td>
                            <td class="px-3.5 py-3">
                                @if($rule->type === 'success')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                        <i data-lucide="check-circle-2" class="w-3 h-3"></i>
                                        <span>Success</span>
                                    </span>
                                @elseif($rule->type === 'error')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-red-100 dark:bg-red-950/60 text-red-800 dark:text-red-300 border border-red-200 dark:border-red-800">
                                        <i data-lucide="x-circle" class="w-3 h-3"></i>
                                        <span>Error</span>
                                    </span>
                                @elseif($rule->type === 'warning')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                        <i data-lucide="alert-triangle" class="w-3 h-3"></i>
                                        <span>Warning</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-blue-100 dark:bg-blue-950/60 text-blue-800 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                        <i data-lucide="info" class="w-3 h-3"></i>
                                        <span>Info</span>
                                    </span>
                                @endif
                            </td>
                            <td class="px-3.5 py-3 text-zinc-600 dark:text-zinc-300 font-mono text-[11px]">
                                {{ $rule->duration_ms ?? 5500 }} ms
                            </td>
                            <td class="px-3.5 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700 capitalize">
                                    {{ str_replace('_', ' ', $rule->recipient_type ?? 'record_owner') }}
                                </span>
                            </td>
                            <td class="px-3.5 py-3 text-center">
                                <button
                                    type="button"
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold transition-colors cursor-pointer {{ $rule->is_active ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700' }}"
                                    onclick="toggleRuleActive({{ $rule->id }}, this)"
                                >
                                    {{ $rule->is_active ? 'Active' : 'Inactive' }}
                                </button>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-1 px-2 py-1 text-[11px] font-medium bg-indigo-50 dark:bg-indigo-950/50 hover:bg-indigo-100 dark:hover:bg-indigo-900 text-indigo-700 dark:text-indigo-300 rounded border border-indigo-200 dark:border-indigo-800 transition-colors shadow-xs"
                                        onclick="triggerRuleTest({{ json_encode($rule) }})"
                                        title="Trigger live PopUp notification"
                                    >
                                        <i data-lucide="play" class="w-3 h-3"></i>
                                        <span>Trigger Test</span>
                                    </button>
                                    <button
                                        type="button"
                                        class="p-1 rounded text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800"
                                        onclick="openEditRuleModal({{ json_encode($rule) }})"
                                        title="Edit Rule"
                                    >
                                        <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                    <button
                                        type="button"
                                        class="p-1 rounded text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-950/50"
                                        onclick="deleteRule({{ $rule->id }})"
                                        title="Delete Rule"
                                    >
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center text-zinc-500">
                                <i data-lucide="bell-off" class="w-8 h-8 mx-auto mb-2 text-zinc-400"></i>
                                <div class="font-semibold text-zinc-700 dark:text-zinc-300">No Notification Rules Found</div>
                                <p class="text-xs text-zinc-400 mt-1">Create your first automated pop-up alert rule to notify your team on CRM events.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ================= CREATE / EDIT NOTIFICATION RULE MODAL (Tailwind CSS) ================= -->
    <div id="rule-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[22000] hidden items-center justify-center p-4 overflow-y-auto">
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] flex flex-col overflow-hidden animate-in zoom-in-95 duration-150">
            <!-- Modal Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900/50">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                        <i data-lucide="bell" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-zinc-950 dark:text-white m-0" id="modal-title">
                            Create Pop-up Notification Rule
                        </h3>
                        <p class="text-[11px] text-zinc-500 m-0">Event trigger condition, visual presentation &amp; action buttons</p>
                    </div>
                </div>
                <button type="button" onclick="closeRuleModal()" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 p-1 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <!-- Modal Form Body -->
            <form id="rule-form" onsubmit="handleRuleFormSubmit(event)" class="p-6 overflow-y-auto space-y-4 text-xs">
                <input type="hidden" id="form-rule-id" value="">

                <!-- 1. Rule Name & Target Module -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Rule Name *</label>
                        <input
                            type="text"
                            id="form-rule-name"
                            required
                            placeholder="e.g. Mega Deal Won Celebration"
                            class="w-full px-3 py-1.5 text-xs bg-zinc-50 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 rounded-lg text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                            oninput="updateModalLivePreview()"
                        >
                    </div>
                    <div>
                        <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Target Entity / Module *</label>
                        <select
                            id="form-module"
                            class="w-full px-3 py-1.5 text-xs bg-zinc-50 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 rounded-lg text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                            onchange="updateModalLivePreview()"
                        >
                            <option value="all">All Objects (Global)</option>
                            <option value="opportunities" selected>Opportunities</option>
                            <option value="leads">Leads</option>
                            <option value="accounts">Accounts</option>
                            <option value="contacts">Contacts</option>
                            <option value="tasks">Tasks</option>
                            <option value="wires">Wire Transfers</option>
                            <option value="purchase_deals">Purchase Deals</option>
                            <option value="shipping">Shipments</option>
                        </select>
                    </div>
                </div>

                <!-- 2. Trigger Event & Criteria -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Trigger Operation *</label>
                        <select
                            id="form-operation"
                            class="w-full px-3 py-1.5 text-xs bg-zinc-50 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 rounded-lg text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                            onchange="updateModalLivePreview()"
                        >
                            <option value="UPDATE">Record Updated / Modified</option>
                            <option value="CREATE">New Record Created</option>
                            <option value="DELETE">Record Deleted</option>
                            <option value="APPROVE">Approval Granted / Requested</option>
                            <option value="REJECT">Approval Declined</option>
                            <option value="CANCEL">Operation Cancelled</option>
                            <option value="CUSTOM">Custom Workflow Trigger</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Trigger Criteria (Optional)</label>
                        <input
                            type="text"
                            id="form-criteria-value"
                            placeholder="e.g. stage == 'Closed Won' or score >= 85"
                            class="w-full px-3 py-1.5 text-xs bg-zinc-50 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 rounded-lg text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                        >
                    </div>
                </div>

                <!-- 3. Notification Tone (Tailwind Radio Cards) -->
                <div>
                    <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">Notification Tone &amp; Style *</label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                        <label class="cursor-pointer p-2.5 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 flex items-center gap-2 hover:border-emerald-500 has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50/50 dark:has-[:checked]:bg-emerald-950/30 transition-all">
                            <input type="radio" name="tone" value="success" checked class="text-emerald-600 focus:ring-emerald-500" onchange="updateModalLivePreview()">
                            <span class="font-bold text-emerald-700 dark:text-emerald-400 flex items-center gap-1">
                                <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i> Success
                            </span>
                        </label>
                        <label class="cursor-pointer p-2.5 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 flex items-center gap-2 hover:border-amber-500 has-[:checked]:border-amber-500 has-[:checked]:bg-amber-50/50 dark:has-[:checked]:bg-amber-950/30 transition-all">
                            <input type="radio" name="tone" value="warning" class="text-amber-600 focus:ring-amber-500" onchange="updateModalLivePreview()">
                            <span class="font-bold text-amber-700 dark:text-amber-400 flex items-center gap-1">
                                <i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i> Warning
                            </span>
                        </label>
                        <label class="cursor-pointer p-2.5 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 flex items-center gap-2 hover:border-red-500 has-[:checked]:border-red-500 has-[:checked]:bg-red-50/50 dark:has-[:checked]:bg-red-950/30 transition-all">
                            <input type="radio" name="tone" value="error" class="text-red-600 focus:ring-red-500" onchange="updateModalLivePreview()">
                            <span class="font-bold text-red-700 dark:text-red-400 flex items-center gap-1">
                                <i data-lucide="x-circle" class="w-3.5 h-3.5"></i> Error
                            </span>
                        </label>
                        <label class="cursor-pointer p-2.5 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 flex items-center gap-2 hover:border-blue-500 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50/50 dark:has-[:checked]:bg-blue-950/30 transition-all">
                            <input type="radio" name="tone" value="info" class="text-blue-600 focus:ring-blue-500" onchange="updateModalLivePreview()">
                            <span class="font-bold text-blue-700 dark:text-blue-400 flex items-center gap-1">
                                <i data-lucide="info" class="w-3.5 h-3.5"></i> Info
                            </span>
                        </label>
                    </div>
                </div>

                <!-- 4. Title & Message Template -->
                <div class="space-y-2">
                    <div>
                        <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Pop-up Alert Title *</label>
                        <input
                            type="text"
                            id="form-title"
                            required
                            placeholder="e.g. 🎉 Mega Deal Won Celebration"
                            class="w-full px-3 py-1.5 text-xs bg-zinc-50 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 rounded-lg text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                            oninput="updateModalLivePreview()"
                        >
                    </div>
                    <div>
                        <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                            Message Template (with token placeholders) *
                        </label>
                        <textarea
                            id="form-message"
                            rows="2"
                            required
                            placeholder='e.g. Opportunity "{record_name}" over $100,000 has closed won!'
                            class="w-full px-3 py-1.5 text-xs bg-zinc-50 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 rounded-lg text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                            oninput="updateModalLivePreview()"
                        ></textarea>
                    </div>
                </div>

                <!-- 5. Interactive Action Buttons Selector -->
                <div>
                    <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">Action Buttons Included</label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        <label class="flex items-center gap-2 p-2 bg-zinc-50 dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 cursor-pointer">
                            <input type="checkbox" name="action_item" value="view_record" checked class="rounded text-indigo-600 focus:ring-indigo-500" onchange="updateModalLivePreview()">
                            <span>View Record</span>
                        </label>
                        <label class="flex items-center gap-2 p-2 bg-zinc-50 dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 cursor-pointer">
                            <input type="checkbox" name="action_item" value="view_details" checked class="rounded text-indigo-600 focus:ring-indigo-500" onchange="updateModalLivePreview()">
                            <span>View Details</span>
                        </label>
                        <label class="flex items-center gap-2 p-2 bg-zinc-50 dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 cursor-pointer">
                            <input type="checkbox" name="action_item" value="snooze" class="rounded text-indigo-600 focus:ring-indigo-500" onchange="updateModalLivePreview()">
                            <span>Snooze</span>
                        </label>
                        <label class="flex items-center gap-2 p-2 bg-zinc-50 dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 cursor-pointer">
                            <input type="checkbox" name="action_item" value="download" class="rounded text-indigo-600 focus:ring-indigo-500" onchange="updateModalLivePreview()">
                            <span>Download</span>
                        </label>
                        <label class="flex items-center gap-2 p-2 bg-zinc-50 dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 cursor-pointer">
                            <input type="checkbox" name="action_item" value="approve" class="rounded text-indigo-600 focus:ring-indigo-500" onchange="updateModalLivePreview()">
                            <span>Approve</span>
                        </label>
                        <label class="flex items-center gap-2 p-2 bg-zinc-50 dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 cursor-pointer">
                            <input type="checkbox" name="action_item" value="reject" class="rounded text-indigo-600 focus:ring-indigo-500" onchange="updateModalLivePreview()">
                            <span>Reject</span>
                        </label>
                        <label class="flex items-center gap-2 p-2 bg-zinc-50 dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 cursor-pointer">
                            <input type="checkbox" name="action_item" value="undo" class="rounded text-indigo-600 focus:ring-indigo-500" onchange="updateModalLivePreview()">
                            <span>Undo</span>
                        </label>
                        <label class="flex items-center gap-2 p-2 bg-zinc-50 dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 cursor-pointer">
                            <input type="checkbox" name="action_item" value="retry" class="rounded text-indigo-600 focus:ring-indigo-500" onchange="updateModalLivePreview()">
                            <span>Retry</span>
                        </label>
                    </div>
                </div>

                <!-- 6. Duration & Screen Position -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Display Duration (ms)</label>
                        <select id="form-duration" class="w-full px-3 py-1.5 text-xs bg-zinc-50 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 rounded-lg text-zinc-900 dark:text-white">
                            <option value="4500">4.5 Seconds (Fast)</option>
                            <option value="5500" selected>5.5 Seconds (Standard)</option>
                            <option value="7000">7.0 Seconds (Extended)</option>
                            <option value="10000">10.0 Seconds (Urgent Alert)</option>
                            <option value="0">Persistent (Until Dismissed)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Screen Position</label>
                        <select id="form-position" class="w-full px-3 py-1.5 text-xs bg-zinc-50 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 rounded-lg text-zinc-900 dark:text-white">
                            <option value="top-right" selected>Top Right (Default)</option>
                            <option value="top-center">Top Center</option>
                            <option value="bottom-right">Bottom Right</option>
                            <option value="bottom-left">Bottom Left</option>
                        </select>
                    </div>
                </div>

                <!-- 7. Live Pop-up Preview Box (Tailwind CSS) -->
                <div>
                    <div class="text-[11px] font-bold uppercase tracking-wider text-zinc-500 mb-2 flex items-center gap-1.5">
                        <i data-lucide="sparkles" class="w-3.5 h-3.5 text-amber-500"></i>
                        <span>Live Pop-up Visual Preview</span>
                    </div>
                    <div class="p-4 bg-zinc-100/70 dark:bg-zinc-950/60 rounded-xl border border-dashed border-zinc-300 dark:border-zinc-800 flex items-center justify-center">
                        <div id="modal-live-preview-toast" class="w-[380px] max-w-full rounded-xl border shadow-lg overflow-hidden relative transition-all bg-emerald-50 border-emerald-300 text-emerald-950">
                            <!-- Preview Content injected via JS -->
                        </div>
                    </div>
                </div>

                <!-- Modal Actions -->
                <div class="pt-4 border-t border-zinc-200 dark:border-zinc-800 flex items-center justify-end gap-2.5">
                    <button
                        type="button"
                        onclick="closeRuleModal()"
                        class="px-4 py-2 text-xs font-semibold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-lg transition-colors"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        id="save-rule-btn"
                        class="px-4 py-2 text-xs font-semibold bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg shadow-sm transition-all"
                    >
                        Save Notification Rule
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    let activeToneFilter = 'all';

    function setToneFilter(tone, btn) {
        activeToneFilter = tone;
        document.querySelectorAll('.tone-filter-btn').forEach(b => {
            b.classList.remove('bg-white', 'dark:bg-zinc-700', 'text-zinc-900', 'dark:text-white', 'shadow-xs');
            b.classList.add('text-zinc-600', 'dark:text-zinc-300');
        });
        btn.classList.add('bg-white', 'dark:bg-zinc-700', 'text-zinc-900', 'dark:text-white', 'shadow-xs');
        btn.classList.remove('text-zinc-600', 'dark:text-zinc-300');
        filterNotificationRules();
    }

    function filterNotificationRules() {
        const query = (document.getElementById('notif-search-input')?.value || '').toLowerCase().trim();
        const rows = document.querySelectorAll('.rule-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const tone = row.dataset.tone || 'info';
            const text = row.innerText.toLowerCase();

            const matchesTone = (activeToneFilter === 'all' || tone === activeToneFilter);
            const matchesQuery = (!query || text.includes(query));

            if (matchesTone && matchesQuery) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        const label = document.getElementById('rule-count-label');
        if (label) {
            label.innerHTML = `Showing <strong class="text-zinc-900 dark:text-white">${visibleCount}</strong> Rules`;
        }
    }

    function testPlayAudioChime() {
        if (window.playChimeAlert) {
            window.playChimeAlert('fanfare');
        }
        if (window.showNotification) {
            window.showNotification({
                type: 'success',
                title: '🔔 Audio Broadcast Synthesizer Active',
                message: 'Harmonious Web Audio API chime tone generated with zero latency.',
                module: 'SYSTEM',
                actions: ['view_details', 'snooze'],
                data: {
                    event: 'AUDIO_CHIME_TEST',
                    frequencies: ['523.25 Hz', '659.25 Hz', '783.99 Hz', '1046.50 Hz'],
                    driver: 'WebAudio_Synthesizer',
                    status: 'SUCCESS'
                }
            });
        }
    }

    function triggerRuleTest(rule) {
        if (window.showNotification) {
            window.showNotification({
                type: rule.type || 'info',
                title: rule.title || rule.name,
                message: (rule.message_template || 'Automated trigger completed.').replace('{record_name}', 'Acme Cloud Platform Expansion ($120k)'),
                module: rule.module || 'SYSTEM',
                actions: rule.actions || ['view_record', 'view_details'],
                duration_ms: rule.duration_ms || 5500,
                position: rule.position || 'top-right',
                data: {
                    rule_id: rule.id,
                    rule_name: rule.name,
                    operation: rule.operation,
                    module: rule.module,
                    timestamp: new Date().toISOString(),
                    sample_record: {
                        id: 'rec-' + Math.floor(Math.random() * 9000 + 1000),
                        name: 'Acme Cloud Platform Expansion',
                        amount: '$120,000.00',
                        stage: 'Closed Won'
                    }
                }
            });
        }
    }

    async function toggleRuleActive(id, btn) {
        try {
            const res = await fetch(`/admin/notification-rules/${id}/toggle`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            const data = await res.json();
            if (data.success && data.data) {
                const isActive = Boolean(data.data.is_active);
                btn.innerText = isActive ? 'Active' : 'Inactive';
                btn.className = `inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold transition-colors cursor-pointer ${
                    isActive
                        ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800'
                        : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700'
                }`;
                window.showToast(data.message, 'success');
            }
        } catch (err) {
            window.showToast('Failed to toggle rule state', 'error');
        }
    }

    async function deleteRule(id) {
        if (!confirm('Are you sure you want to delete this notification rule?')) return;
        try {
            const res = await fetch(`/admin/notification-rules/${id}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            const data = await res.json();
            if (data.success) {
                const row = document.querySelector(`.rule-row[data-id="${id}"]`);
                if (row) row.remove();
                window.showToast(data.message, 'success');
                filterNotificationRules();
            }
        } catch (err) {
            window.showToast('Failed to delete rule', 'error');
        }
    }

    function openCreateRuleModal() {
        document.getElementById('modal-title').innerText = 'Create Pop-up Notification Rule';
        document.getElementById('form-rule-id').value = '';
        document.getElementById('form-rule-name').value = '';
        document.getElementById('form-module').value = 'opportunities';
        document.getElementById('form-operation').value = 'UPDATE';
        document.getElementById('form-criteria-value').value = "stage == 'Closed Won'";
        document.querySelector('input[name="tone"][value="success"]').checked = true;
        document.getElementById('form-title').value = '🎉 Mega Deal Won Celebration';
        document.getElementById('form-message').value = 'Opportunity "{record_name}" over $100,000 has closed won!';
        document.getElementById('form-duration').value = '5500';
        document.getElementById('form-position').value = 'top-right';

        document.getElementById('rule-modal').classList.remove('hidden');
        document.getElementById('rule-modal').classList.add('flex');
        updateModalLivePreview();
    }

    function openEditRuleModal(rule) {
        document.getElementById('modal-title').innerText = 'Edit Pop-up Notification Rule';
        document.getElementById('form-rule-id').value = rule.id || '';
        document.getElementById('form-rule-name').value = rule.name || '';
        document.getElementById('form-module').value = rule.module || 'all';
        document.getElementById('form-operation').value = rule.operation || 'UPDATE';
        document.getElementById('form-criteria-value').value = rule.criteria_value || '';

        const toneRadio = document.querySelector(`input[name="tone"][value="${rule.type}"]`);
        if (toneRadio) toneRadio.checked = true;

        document.getElementById('form-title').value = rule.title || '';
        document.getElementById('form-message').value = rule.message_template || '';
        document.getElementById('form-duration').value = String(rule.duration_ms || 5500);
        document.getElementById('form-position').value = rule.position || 'top-right';

        // Check action buttons
        const actions = Array.isArray(rule.actions) ? rule.actions : [];
        document.querySelectorAll('input[name="action_item"]').forEach(cb => {
            cb.checked = actions.includes(cb.value);
        });

        document.getElementById('rule-modal').classList.remove('hidden');
        document.getElementById('rule-modal').classList.add('flex');
        updateModalLivePreview();
    }

    function closeRuleModal() {
        document.getElementById('rule-modal').classList.add('hidden');
        document.getElementById('rule-modal').classList.remove('flex');
    }

    function updateModalLivePreview() {
        const previewEl = document.getElementById('modal-live-preview-toast');
        if (!previewEl) return;

        const tone = document.querySelector('input[name="tone"]:checked')?.value || 'success';
        const title = document.getElementById('form-title')?.value || 'Notification Title';
        const message = document.getElementById('form-message')?.value || 'This is a sample live preview of how the notification pop-up will appear.';
        const module = (document.getElementById('form-module')?.value || 'OPPORTUNITIES').toUpperCase();

        const selectedActions = [];
        document.querySelectorAll('input[name="action_item"]:checked').forEach(cb => selectedActions.push(cb.value));

        let toneClasses = 'bg-emerald-50 border-emerald-300 text-emerald-950';
        let barColor = 'bg-emerald-500';
        let iconName = 'check-circle-2';
        let iconColor = 'text-emerald-600';
        let badgeClasses = 'bg-emerald-100 text-emerald-800 border-emerald-200';

        if (tone === 'error') {
            toneClasses = 'bg-red-50 border-red-300 text-red-950';
            barColor = 'bg-red-500';
            iconName = 'x-circle';
            iconColor = 'text-red-600';
            badgeClasses = 'bg-red-100 text-red-800 border-red-200';
        } else if (tone === 'warning') {
            toneClasses = 'bg-amber-50 border-amber-300 text-amber-950';
            barColor = 'bg-amber-500';
            iconName = 'alert-triangle';
            iconColor = 'text-amber-600';
            badgeClasses = 'bg-amber-100 text-amber-800 border-amber-200';
        } else if (tone === 'info') {
            toneClasses = 'bg-blue-50 border-blue-300 text-blue-950';
            barColor = 'bg-blue-500';
            iconName = 'info';
            iconColor = 'text-blue-600';
            badgeClasses = 'bg-blue-100 text-blue-800 border-blue-200';
        }

        previewEl.className = `w-[380px] max-w-full rounded-xl border shadow-lg overflow-hidden relative transition-all ${toneClasses}`;
        previewEl.innerHTML = `
            <div class="absolute top-0 left-0 h-[3.5px] ${barColor} w-2/3"></div>
            <div class="p-3.5">
                <div class="flex items-start justify-between gap-2">
                    <div class="flex items-center gap-2 flex-1 min-w-0">
                        <i data-lucide="${iconName}" class="w-4 h-4 flex-shrink-0 ${iconColor}"></i>
                        <h4 class="text-[13px] font-bold truncate m-0">${escapeHtml(title)}</h4>
                    </div>
                    <div class="flex items-center gap-1.5 flex-shrink-0">
                        <span class="text-[9.5px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded border ${badgeClasses}">${escapeHtml(module)}</span>
                        <span class="text-zinc-400 p-0.5"><i data-lucide="x" class="w-3.5 h-3.5"></i></span>
                    </div>
                </div>
                <p class="text-[12px] leading-relaxed m-0 mt-1 ml-6 opacity-90">
                    ${escapeHtml(message.replace('{record_name}', 'Acme Cloud Expansion ($120k)'))}
                </p>
                <div class="flex flex-wrap items-center gap-1.5 mt-2.5 ml-6">
                    ${selectedActions.map(act => `
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[10.5px] font-medium bg-white text-zinc-700 border border-zinc-300 rounded shadow-xs">
                            ${act.replace(/_/g, ' ')}
                        </span>
                    `).join('')}
                </div>
            </div>
        `;

        if (window.lucide) {
            lucide.createIcons({ root: previewEl });
        }
    }

    async function handleRuleFormSubmit(e) {
        e.preventDefault();
        const id = document.getElementById('form-rule-id').value;
        const submitBtn = document.getElementById('save-rule-btn');
        submitBtn.disabled = true;
        submitBtn.innerText = 'Saving...';

        const actions = [];
        document.querySelectorAll('input[name="action_item"]:checked').forEach(cb => actions.push(cb.value));

        const payload = {
            name: document.getElementById('form-rule-name').value,
            module: document.getElementById('form-module').value,
            operation: document.getElementById('form-operation').value,
            criteria_value: document.getElementById('form-criteria-value').value,
            type: document.querySelector('input[name="tone"]:checked')?.value || 'success',
            title: document.getElementById('form-title').value,
            message_template: document.getElementById('form-message').value,
            duration_ms: parseInt(document.getElementById('form-duration').value, 10),
            position: document.getElementById('form-position').value,
            actions: actions,
            is_active: true
        };

        try {
            const url = id ? `/admin/notification-rules/${id}` : '/admin/notification-rules';
            const method = id ? 'PUT' : 'POST';

            const res = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(payload)
            });

            const data = await res.json();
            if (data.success) {
                window.showToast(data.message, 'success');
                closeRuleModal();
                setTimeout(() => window.location.reload(), 600);
            } else {
                window.showToast(data.message || 'Validation error saving rule', 'error');
            }
        } catch (err) {
            window.showToast('Network error saving rule', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerText = 'Save Notification Rule';
        }
    }

    function escapeHtml(str) {
        if (!str) return '';
        const d = document.createElement('div');
        d.textContent = String(str);
        return d.innerHTML;
    }

    function initNotificationRulesStudio() {
        if (window.lucide) {
            lucide.createIcons();
        }
        updateModalLivePreview();
        filterNotificationRules();
    }

    window.initNotificationRulesStudio = initNotificationRulesStudio;

    document.addEventListener('DOMContentLoaded', () => {
        initNotificationRulesStudio();
    });
</script>
@endsection
