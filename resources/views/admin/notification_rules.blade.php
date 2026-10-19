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

<!-- CSRF Configuration -->
<script>
    window.CSRF_TOKEN = '{{ csrf_token() }}';
</script>

<!-- Dedicated Notification Rules Studio JavaScript Engine -->
<script src="{{ asset('js/notification_rules.js') }}?v=1.0"></script>
<script>
    if (typeof initNotificationRulesStudio === 'function') {
        initNotificationRulesStudio();
    }
</script>
@endsection
