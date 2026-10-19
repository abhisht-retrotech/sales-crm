@extends('layouts.app')

@section('title', ($opportunity['name'] ?? 'Opportunity') . ' — Opportunity 360° Profile — DealSync CRM')

@section('content')
<!-- Centered Max-Width Container matching React page-wrapper (max-width: 1240px, padding: 2.25rem 2.5rem) -->
<div id="opportunity-show-page-root" class="max-w-[1240px] mx-auto w-full px-6 sm:px-10 py-7 space-y-6 animate-fade-in">
    <!-- Breadcrumb & Back Action -->
    <div class="flex items-center justify-between gap-4 flex-wrap">
        <div class="flex items-center gap-2 text-xs">
            <a href="/opportunities" class="spa-nav-link inline-flex items-center gap-1.5 font-semibold text-zinc-500 hover:text-indigo-600 dark:text-zinc-400 dark:hover:text-indigo-400 transition-colors">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Pipeline Opportunities</span>
            </a>
            <span class="text-zinc-300 dark:text-zinc-700">/</span>
            <span class="text-zinc-600 dark:text-zinc-400 font-medium">{{ $opportunity['account_name'] ?? 'Account' }}</span>
            <span class="text-zinc-300 dark:text-zinc-700">/</span>
            <span class="text-zinc-950 dark:text-zinc-100 font-bold">{{ $opportunity['name'] }}</span>
        </div>

        <div class="flex items-center gap-2">
            <span class="text-xs text-zinc-500 dark:text-zinc-400">Security Profile:</span>
            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-zinc-100 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-200 border border-zinc-200 dark:border-zinc-700">
                {{ $currentUserProfile ?? 'Administrator' }}
            </span>
        </div>
    </div>

    <!-- Hero Header Card -->
    <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 border border-zinc-200 dark:border-zinc-800 shadow-xs relative overflow-hidden">
        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-6 relative z-10">
            <!-- Left: Icon, Name, Stage, Badges -->
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-blue-600 text-white font-extrabold text-xl flex items-center justify-center shrink-0 shadow-md">
                    <i data-lucide="briefcase" class="w-7 h-7"></i>
                </div>
                <div class="space-y-1.5">
                    <div class="flex items-center gap-3 flex-wrap">
                        <h1 class="text-2xl font-extrabold text-zinc-950 dark:text-white tracking-tight">
                            {{ $opportunity['name'] }}
                        </h1>

                        <!-- Stage Badge -->
                        @php
                            $stage = $opportunity['stage'] ?? 'Discovery';
                            $stageBadges = [
                                'Discovery' => 'bg-sky-50 text-sky-700 border-sky-200 dark:bg-sky-950/40 dark:text-sky-300 dark:border-sky-800',
                                'Proposal' => 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-950/40 dark:text-indigo-300 dark:border-indigo-800',
                                'Negotiation' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800',
                                'Closed Won' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800',
                                'Closed Lost' => 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800',
                            ];
                            $stageClass = $stageBadges[$stage] ?? 'bg-zinc-100 text-zinc-700 border-zinc-200';
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $stageClass }}">
                            {{ $stage }}
                        </span>

                        <!-- Probability Badge -->
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-bold bg-blue-50 text-blue-800 border border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800">
                            {{ $opportunity['probability'] ?? 50 }}% Win Probability
                        </span>

                        <!-- Backed PO Badge -->
                        @if(!empty($opportunity['po_number']))
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200 dark:bg-amber-950/30 dark:text-amber-300 dark:border-amber-800">
                                <i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i>
                                <span>Backed by PO: <strong>{{ $opportunity['po_number'] }}</strong></span>
                            </span>
                        @endif
                    </div>

                    <div class="text-xs text-zinc-600 dark:text-zinc-400 flex items-center gap-3 flex-wrap">
                        @if(!empty($opportunity['account_name']) && $opportunity['account_name'] !== 'Unlinked Account')
                            <a href="/accounts/{{ $opportunity['account_id'] ?? '' }}" class="font-semibold text-zinc-900 dark:text-zinc-200 hover:text-indigo-600 flex items-center gap-1">
                                <i data-lucide="building-2" class="w-3.5 h-3.5 text-zinc-400"></i>
                                <span>{{ $opportunity['account_name'] }}</span>
                            </a>
                            <span>•</span>
                        @endif

                        @if(!empty($opportunity['contact_name']))
                            <a href="/contacts/{{ $opportunity['contact_id'] ?? '' }}" class="hover:text-indigo-600 flex items-center gap-1">
                                <i data-lucide="user" class="w-3.5 h-3.5 text-zinc-400"></i>
                                <span>Contact: <strong>{{ $opportunity['contact_name'] }}</strong></span>
                            </a>
                            <span>•</span>
                        @endif

                        <div class="flex items-center gap-1.5 text-zinc-700 dark:text-zinc-300">
                            <span class="text-zinc-400 font-medium">Owner:</span>
                            <span class="w-4 h-4 rounded-full bg-blue-100 text-blue-700 font-bold text-[9px] inline-flex items-center justify-center">
                                {{ strtoupper(substr($opportunity['owner_name'] ?? 'U', 0, 1)) }}
                            </span>
                            <span class="font-semibold">{{ $opportunity['owner_name'] ?? 'Unassigned' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Primary Actions & Financial Summary -->
            <div class="flex flex-col sm:items-end gap-3 shrink-0">
                <div class="text-right">
                    <div class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wider">DEAL VALUE</div>
                    <div class="text-3xl font-extrabold text-zinc-950 dark:text-white tracking-tight">
                        {{ $opportunity['amount_formatted'] ?? '$0.00' }}
                    </div>
                    <div class="text-[11px] text-zinc-500 font-medium mt-0.5">
                        Target Close: <strong class="text-zinc-800 dark:text-zinc-200">{{ $opportunity['expected_close_date_formatted'] ?? 'Not set' }}</strong>
                    </div>
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    <!-- Stage Advancement Action -->
                    @php
                        $nextStageMap = [
                            'Discovery' => 'Proposal',
                            'Proposal' => 'Negotiation',
                            'Negotiation' => 'Closed Won',
                        ];
                        $nextStage = $nextStageMap[$stage] ?? null;
                    @endphp
                    @if($nextStage)
                        <button
                            type="button"
                            onclick="window.advanceCurrentOpportunityStage('{{ $nextStage }}')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-xs transition-colors cursor-pointer"
                        >
                            <span>Advance to {{ $nextStage }}</span>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </button>
                    @endif

                    <button
                        type="button"
                        onclick="window.openOpportunityDetailEditModal()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs cursor-pointer"
                    >
                        <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                        <span>Edit Deal</span>
                    </button>

                    <button
                        type="button"
                        onclick="window.openOpportunityDetailTransferModal()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs cursor-pointer"
                    >
                        <i data-lucide="user-check" class="w-3.5 h-3.5"></i>
                        <span>Transfer</span>
                    </button>

                    <button
                        type="button"
                        onclick="window.deleteCurrentOpportunity()"
                        class="p-2 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-zinc-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/40 transition-colors shadow-2xs cursor-pointer"
                        title="Delete Deal"
                    >
                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Segmented Tabs Navigation -->
    <div class="flex items-center gap-1.5 p-1 rounded-xl bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 overflow-x-auto">
        <button
            type="button"
            onclick="window.switchOpportunityTab('overview')"
            id="tab-btn-overview"
            class="tab-btn active inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all cursor-pointer bg-white dark:bg-zinc-700 text-zinc-950 dark:text-white shadow-2xs"
        >
            <i data-lucide="layout-dashboard" class="w-3.5 h-3.5"></i>
            <span>Overview &amp; Custom Fields</span>
        </button>

        <button
            type="button"
            onclick="window.switchOpportunityTab('procurement')"
            id="tab-btn-procurement"
            class="tab-btn inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-medium transition-all cursor-pointer text-zinc-600 dark:text-zinc-400 hover:text-zinc-950 dark:hover:text-white"
        >
            <i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i>
            <span>Procurement PO &amp; Logistics ({{ count($shipments ?? []) }})</span>
        </button>

        <button
            type="button"
            onclick="window.switchOpportunityTab('finance')"
            id="tab-btn-finance"
            class="tab-btn inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-medium transition-all cursor-pointer text-zinc-600 dark:text-zinc-400 hover:text-zinc-950 dark:hover:text-white"
        >
            <i data-lucide="dollar-sign" class="w-3.5 h-3.5"></i>
            <span>Payments &amp; Wires ({{ count($payments ?? []) }})</span>
        </button>

        <button
            type="button"
            onclick="window.switchOpportunityTab('tasks')"
            id="tab-btn-tasks"
            class="tab-btn inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-medium transition-all cursor-pointer text-zinc-600 dark:text-zinc-400 hover:text-zinc-950 dark:hover:text-white"
        >
            <i data-lucide="check-square" class="w-3.5 h-3.5"></i>
            <span>Tasks &amp; Activities ({{ count($tasks ?? []) }})</span>
        </button>
    </div>

    <!-- TAB PANE 1: OVERVIEW & DYNAMIC CUSTOM FIELDS -->
    <div id="tab-pane-overview" class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
            <!-- Left 2 Cols: Financials, Stage Timeline, Notes, Custom Fields -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Pipeline Stage Progress Bar -->
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-5 border border-zinc-200 dark:border-zinc-800 shadow-xs space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider">Pipeline Stage Progression</span>
                        <span class="text-xs font-semibold text-indigo-600">{{ $opportunity['stage'] }} ({{ $opportunity['probability'] }}% Win Likelihood)</span>
                    </div>

                    <div class="grid grid-cols-5 gap-2 pt-1">
                        @php
                            $allStages = ['Discovery', 'Proposal', 'Negotiation', 'Closed Won', 'Closed Lost'];
                            $currentIdx = array_search($opportunity['stage'], $allStages);
                            if ($currentIdx === false) $currentIdx = 0;
                        @endphp
                        @foreach($allStages as $idx => $stg)
                            @php
                                $isPastOrCurrent = ($idx <= $currentIdx && $opportunity['stage'] !== 'Closed Lost') || ($stg === $opportunity['stage']);
                                $isLost = ($opportunity['stage'] === 'Closed Lost' && $stg === 'Closed Lost');
                            @endphp
                            <div class="space-y-1 text-center">
                                <div class="h-2 rounded-full {{ $isLost ? 'bg-rose-500' : ($isPastOrCurrent ? 'bg-indigo-600' : 'bg-zinc-200 dark:bg-zinc-700') }}"></div>
                                <span class="text-[10px] {{ $stg === $opportunity['stage'] ? 'font-bold text-indigo-600 dark:text-indigo-400' : 'text-zinc-400' }}">
                                    {{ $stg }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Standard Details Section -->
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 border border-zinc-200 dark:border-zinc-800 shadow-xs space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-zinc-100 dark:border-zinc-800">
                        <div class="flex items-center gap-2">
                            <i data-lucide="info" class="w-4 h-4 text-indigo-600"></i>
                            <h3 class="text-sm font-bold text-zinc-950 dark:text-zinc-100">Deal Parameters</h3>
                        </div>
                        <span class="text-[11px] text-zinc-400">Created: {{ date('M d, Y', strtotime($rawOpportunity->created_at ?? now())) }}</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div class="p-3 rounded-xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-100 dark:border-zinc-800">
                            <span class="text-zinc-400 block mb-1 font-medium">Opportunity Name</span>
                            <span class="font-bold text-zinc-900 dark:text-zinc-100">{{ $opportunity['name'] }}</span>
                        </div>

                        <div class="p-3 rounded-xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-100 dark:border-zinc-800">
                            <span class="text-zinc-400 block mb-1 font-medium">Customer Account</span>
                            <span class="font-bold text-zinc-900 dark:text-zinc-100">
                                @if(!empty($opportunity['account_id']))
                                    <a href="/accounts/{{ $opportunity['account_id'] }}" class="text-indigo-600 hover:underline">
                                        {{ $opportunity['account_name'] }}
                                    </a>
                                @else
                                    {{ $opportunity['account_name'] }}
                                @endif
                            </span>
                        </div>

                        <div class="p-3 rounded-xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-100 dark:border-zinc-800">
                            <span class="text-zinc-400 block mb-1 font-medium">Backed Purchase Deal PO</span>
                            <span class="font-bold text-zinc-900 dark:text-zinc-100">
                                @if(!empty($opportunity['po_number']))
                                    <span class="inline-flex items-center gap-1 text-amber-700 dark:text-amber-400">
                                        <i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i>
                                        <span>{{ $opportunity['po_number'] }}</span>
                                    </span>
                                @else
                                    <span class="text-zinc-400 italic">None linked</span>
                                @endif
                            </span>
                        </div>

                        <div class="p-3 rounded-xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-100 dark:border-zinc-800">
                            <span class="text-zinc-400 block mb-1 font-medium">Primary Contact Stakeholder</span>
                            <span class="font-bold text-zinc-900 dark:text-zinc-100">
                                @if(!empty($opportunity['contact_name']))
                                    <a href="/contacts/{{ $opportunity['contact_id'] ?? '' }}" class="text-indigo-600 hover:underline">
                                        {{ $opportunity['contact_name'] }}
                                    </a>
                                @else
                                    <span class="text-zinc-400 italic">None linked</span>
                                @endif
                            </span>
                        </div>

                        <div class="p-3 rounded-xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-100 dark:border-zinc-800">
                            <span class="text-zinc-400 block mb-1 font-medium">Deal Amount</span>
                            <span class="font-extrabold text-zinc-900 dark:text-zinc-100 text-sm">
                                {{ $opportunity['amount_formatted'] }} ({{ $opportunity['currency'] ?? 'USD' }})
                            </span>
                        </div>

                        <div class="p-3 rounded-xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-100 dark:border-zinc-800">
                            <span class="text-zinc-400 block mb-1 font-medium">Target Close Date</span>
                            <span class="font-bold text-zinc-900 dark:text-zinc-100">
                                {{ $opportunity['expected_close_date_formatted'] }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Dynamic Custom Fields Section (Field-Level Permissions Enforced) -->
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 border border-zinc-200 dark:border-zinc-800 shadow-xs space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-zinc-100 dark:border-zinc-800">
                        <div class="flex items-center gap-2">
                            <i data-lucide="sliders" class="w-4 h-4 text-indigo-600"></i>
                            <h3 class="text-sm font-bold text-zinc-950 dark:text-zinc-100">Dynamic Custom Fields</h3>
                        </div>
                        <span class="text-[11px] text-zinc-500 font-medium">Security Permissions Enforced</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        @php
                            $renderedCustomCount = 0;
                        @endphp
                        @foreach($customFields ?? [] as $cf)
                            @php
                                $fName = $cf->field_name;
                                $perm = $fieldPermissions[$fName] ?? 'Read/Write';
                                if ($perm === 'Hidden') continue;
                                $renderedCustomCount++;
                                $val = $opportunity[$fName] ?? ($opportunity['custom_data'][$fName] ?? null);
                            @endphp
                            <div class="p-3 rounded-xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-100 dark:border-zinc-800">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-zinc-400 font-medium">{{ $cf->field_label }}</span>
                                    @if($perm === 'Read-Only')
                                        <span class="inline-flex items-center gap-1 text-[10px] text-amber-600 dark:text-amber-400 font-semibold">
                                            <i data-lucide="lock" class="w-3 h-3"></i> Read-Only
                                        </span>
                                    @endif
                                </div>
                                <span class="font-bold text-zinc-900 dark:text-zinc-100">
                                    {{ $val !== null && $val !== '' ? $val : '—' }}
                                </span>
                                @if(!empty($cf->help_text))
                                    <p class="text-[10px] text-zinc-400 mt-0.5">{{ $cf->help_text }}</p>
                                @endif
                            </div>
                        @endforeach

                        @if($renderedCustomCount === 0)
                            <div class="col-span-2 py-4 text-center text-zinc-400 italic">
                                No custom fields visible under your current profile permissions.
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Strategy & Notes Section -->
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 border border-zinc-200 dark:border-zinc-800 shadow-xs space-y-3">
                    <div class="flex items-center gap-2 pb-2 border-b border-zinc-100 dark:border-zinc-800">
                        <i data-lucide="file-text" class="w-4 h-4 text-zinc-500"></i>
                        <h3 class="text-sm font-bold text-zinc-950 dark:text-zinc-100">Deal Notes &amp; Strategy</h3>
                    </div>
                    <p class="text-xs text-zinc-600 dark:text-zinc-300 leading-relaxed whitespace-pre-wrap">
                        {{ $opportunity['notes'] ?: 'No notes or procurement strategy recorded for this opportunity.' }}
                    </p>
                </div>
            </div>

            <!-- Right 1 Col: Quick Info Cards & Backed PO Card -->
            <div class="space-y-6">
                <!-- Backed Purchase Deal Card -->
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-5 border border-zinc-200 dark:border-zinc-800 shadow-xs space-y-3">
                    <div class="flex items-center justify-between pb-2 border-b border-zinc-100 dark:border-zinc-800">
                        <div class="flex items-center gap-2">
                            <i data-lucide="shopping-bag" class="w-4 h-4 text-amber-600"></i>
                            <span class="text-xs font-bold text-zinc-900 dark:text-zinc-100">Procurement Deal</span>
                        </div>
                        <span class="text-[10px] font-bold text-amber-700 bg-amber-50 dark:bg-amber-950/40 px-2 py-0.5 rounded">
                            Backed PO
                        </span>
                    </div>

                    @if(!empty($rawOpportunity->purchaseDeal))
                        @php $pd = $rawOpportunity->purchaseDeal; @endphp
                        <div class="space-y-2 text-xs">
                            <div>
                                <span class="text-zinc-400 block font-medium">PO Number:</span>
                                <span class="font-extrabold text-indigo-600">{{ $pd->po_number }}</span>
                            </div>
                            <div>
                                <span class="text-zinc-400 block font-medium">Deal Title:</span>
                                <span class="font-bold text-zinc-900 dark:text-zinc-100">{{ $pd->title }}</span>
                            </div>
                            <div>
                                <span class="text-zinc-400 block font-medium">Procurement Stage:</span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-zinc-100 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200">
                                    {{ $pd->stage }}
                                </span>
                            </div>
                            <div>
                                <span class="text-zinc-400 block font-medium">Purchase Amount:</span>
                                <span class="font-bold text-zinc-900 dark:text-zinc-100">${{ number_format($pd->amount, 2) }}</span>
                            </div>
                        </div>
                    @else
                        <p class="text-xs text-zinc-400 italic">No linked procurement purchase deal recorded.</p>
                    @endif
                </div>

                <!-- Customer Account Card -->
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-5 border border-zinc-200 dark:border-zinc-800 shadow-xs space-y-3">
                    <div class="flex items-center justify-between pb-2 border-b border-zinc-100 dark:border-zinc-800">
                        <div class="flex items-center gap-2">
                            <i data-lucide="building-2" class="w-4 h-4 text-indigo-600"></i>
                            <span class="text-xs font-bold text-zinc-900 dark:text-zinc-100">Customer Account</span>
                        </div>
                        @if(!empty($opportunity['account_id']))
                            <a href="/accounts/{{ $opportunity['account_id'] }}" class="text-[11px] font-semibold text-indigo-600 hover:underline">
                                View Account →
                            </a>
                        @endif
                    </div>

                    @if(!empty($rawOpportunity->account))
                        @php $acc = $rawOpportunity->account; @endphp
                        <div class="space-y-2 text-xs">
                            <div>
                                <span class="text-zinc-400 block font-medium">Account Name:</span>
                                <span class="font-bold text-zinc-900 dark:text-zinc-100">{{ $acc->name }}</span>
                            </div>
                            <div>
                                <span class="text-zinc-400 block font-medium">Industry:</span>
                                <span class="text-zinc-700 dark:text-zinc-300">{{ $acc->industry ?? 'Technology & Services' }}</span>
                            </div>
                            <div>
                                <span class="text-zinc-400 block font-medium">Phone / Desk:</span>
                                <span class="text-zinc-700 dark:text-zinc-300">{{ $acc->phone ?? '—' }}</span>
                            </div>
                            <div>
                                <span class="text-zinc-400 block font-medium">Website:</span>
                                <span class="text-zinc-700 dark:text-zinc-300">{{ $acc->website ?? '—' }}</span>
                            </div>
                        </div>
                    @else
                        <p class="text-xs text-zinc-400 italic">No account profile linked.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- TAB PANE 2: PROCUREMENT PO & LOGISTICS -->
    <div id="tab-pane-procurement" class="hidden space-y-6">
        <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 border border-zinc-200 dark:border-zinc-800 shadow-xs space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-zinc-100 dark:border-zinc-800">
                <div class="flex items-center gap-2">
                    <i data-lucide="truck" class="w-4 h-4 text-blue-600"></i>
                    <h3 class="text-sm font-bold text-zinc-950 dark:text-zinc-100">Linked Shipments &amp; Freight</h3>
                </div>
                <span class="text-xs text-zinc-400">{{ count($shipments ?? []) }} Record(s)</span>
            </div>

            @if(count($shipments ?? []) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-zinc-50 dark:bg-zinc-800/50 text-zinc-400 font-semibold border-b border-zinc-200 dark:border-zinc-700">
                            <tr>
                                <th class="py-2.5 px-3">Tracking / Waybill</th>
                                <th class="py-2.5 px-3">Carrier</th>
                                <th class="py-2.5 px-3">Origin &amp; Destination</th>
                                <th class="py-2.5 px-3">Status</th>
                                <th class="py-2.5 px-3">ETA Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                            @foreach($shipments as $sh)
                                <tr>
                                    <td class="py-2.5 px-3 font-semibold text-zinc-900 dark:text-zinc-100">{{ $sh->tracking_number ?? 'SH-'.$sh->id }}</td>
                                    <td class="py-2.5 px-3 text-zinc-600 dark:text-zinc-300">{{ $sh->carrier ?? 'Standard Air' }}</td>
                                    <td class="py-2.5 px-3 text-zinc-600 dark:text-zinc-300">{{ $sh->origin ?? 'Hub' }} → {{ $sh->destination ?? 'Client' }}</td>
                                    <td class="py-2.5 px-3"><span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700">{{ $sh->status ?? 'In Transit' }}</span></td>
                                    <td class="py-2.5 px-3 text-zinc-500">{{ $sh->eta ?? 'Pending' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="py-8 text-center text-zinc-400 text-xs italic">
                    No freight shipments recorded for this opportunity yet.
                </div>
            @endif
        </div>
    </div>

    <!-- TAB PANE 3: PAYMENTS & WIRES -->
    <div id="tab-pane-finance" class="hidden space-y-6">
        <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 border border-zinc-200 dark:border-zinc-800 shadow-xs space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-zinc-100 dark:border-zinc-800">
                <div class="flex items-center gap-2">
                    <i data-lucide="dollar-sign" class="w-4 h-4 text-emerald-600"></i>
                    <h3 class="text-sm font-bold text-zinc-950 dark:text-zinc-100">Customer Wire Transferences &amp; Payments</h3>
                </div>
                <span class="text-xs text-zinc-400">{{ count($payments ?? []) }} Payment(s)</span>
            </div>

            @if(count($payments ?? []) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-zinc-50 dark:bg-zinc-800/50 text-zinc-400 font-semibold border-b border-zinc-200 dark:border-zinc-700">
                            <tr>
                                <th class="py-2.5 px-3">Reference</th>
                                <th class="py-2.5 px-3">Amount</th>
                                <th class="py-2.5 px-3">Method</th>
                                <th class="py-2.5 px-3">Status</th>
                                <th class="py-2.5 px-3">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                            @foreach($payments as $p)
                                <tr>
                                    <td class="py-2.5 px-3 font-semibold text-zinc-900 dark:text-zinc-100">{{ $p->payment_reference ?? 'PAY-'.$p->id }}</td>
                                    <td class="py-2.5 px-3 font-bold text-zinc-950 dark:text-white">${{ number_format($p->amount ?? 0, 2) }}</td>
                                    <td class="py-2.5 px-3 text-zinc-600 dark:text-zinc-300">{{ $p->payment_method ?? 'Wire Transfer' }}</td>
                                    <td class="py-2.5 px-3"><span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700">{{ $p->status ?? 'Settled' }}</span></td>
                                    <td class="py-2.5 px-3 text-zinc-500">{{ $p->payment_date ?? date('M d, Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="py-8 text-center text-zinc-400 text-xs italic">
                    No payment wires or settled invoices recorded for this opportunity yet.
                </div>
            @endif
        </div>
    </div>

    <!-- TAB PANE 4: TASKS & ACTIVITIES -->
    <div id="tab-pane-tasks" class="hidden space-y-6">
        <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 border border-zinc-200 dark:border-zinc-800 shadow-xs space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-zinc-100 dark:border-zinc-800">
                <div class="flex items-center gap-2">
                    <i data-lucide="check-square" class="w-4 h-4 text-indigo-600"></i>
                    <h3 class="text-sm font-bold text-zinc-950 dark:text-zinc-100">Tasks &amp; Action Items</h3>
                </div>
                <span class="text-xs text-zinc-400">{{ count($tasks ?? []) }} Task(s)</span>
            </div>

            @if(count($tasks ?? []) > 0)
                <div class="space-y-2.5">
                    @foreach($tasks as $t)
                        <div class="p-3 rounded-xl border border-zinc-200 dark:border-zinc-800 flex items-center justify-between text-xs">
                            <div class="flex items-center gap-3">
                                <input type="checkbox" {{ ($t->status ?? '') === 'Completed' ? 'checked' : '' }} class="rounded text-indigo-600">
                                <div>
                                    <h4 class="font-bold text-zinc-900 dark:text-zinc-100">{{ $t->title ?? 'Task Item' }}</h4>
                                    <p class="text-[11px] text-zinc-500">Due: {{ $t->due_date ?? 'Not set' }}</p>
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                                {{ $t->status ?? 'Pending' }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="py-8 text-center text-zinc-400 text-xs italic">
                    No open tasks associated with this opportunity.
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Opportunity Show Hydration Data -->
@php
    $oppDetailHydrationData = [
        'opportunity' => $opportunity ?? [],
        'customFields' => $customFields ?? [],
        'fieldPermissions' => $fieldPermissions ?? (object)[],
        'accounts' => $accounts ?? [],
        'contacts' => $contacts ?? [],
        'purchaseDeals' => $purchaseDeals ?? [],
        'users' => $users ?? [],
        'stages' => $stages ?? [],
        'shipments' => $shipments ?? [],
        'payments' => $payments ?? [],
        'wires' => $wires ?? [],
        'tasks' => $tasks ?? [],
        'currentUserProfile' => $currentUserProfile ?? 'Administrator',
    ];
@endphp
<script>
    window.__OPPORTUNITY_DETAIL_DATA__ = {!! json_encode($oppDetailHydrationData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};
</script>
<script src="{{ asset('js/opportunity_detail.js') }}?v=1.0"></script>
@endsection
