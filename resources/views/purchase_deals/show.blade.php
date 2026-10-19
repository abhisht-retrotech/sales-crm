@extends('layouts.app')

@section('title', ($purchaseDeal['po_number'] ?? 'Purchase Deal') . ' — Purchase Deal 360° Profile — DealSync CRM')

@section('content')
<!-- Centered Max-Width Container matching React page-wrapper (max-width: 1240px, padding: 2.25rem 2.5rem) -->
<div id="purchase-deal-show-page-root" class="max-w-[1240px] mx-auto w-full px-6 sm:px-10 py-7 space-y-6 animate-fade-in">
    <!-- Breadcrumb & Back Action -->
    <div class="flex items-center justify-between gap-4 flex-wrap">
        <div class="flex items-center gap-2 text-xs">
            <a href="/purchase-deals" class="spa-nav-link inline-flex items-center gap-1.5 font-semibold text-zinc-500 hover:text-indigo-600 dark:text-zinc-400 dark:hover:text-indigo-400 transition-colors">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Purchase Deals &amp; Procurement</span>
            </a>
            <span class="text-zinc-300 dark:text-zinc-700">/</span>
            <span class="text-zinc-600 dark:text-zinc-400 font-medium">{{ $purchaseDeal['vendor_name'] ?? 'Vendor' }}</span>
            <span class="text-zinc-300 dark:text-zinc-700">/</span>
            <span class="font-mono font-bold text-zinc-950 dark:text-zinc-100">{{ $purchaseDeal['po_number'] }}</span>
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
            <!-- Left: PO Number, Title, Stage, Badges -->
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-amber-500 to-indigo-600 text-white font-extrabold text-xl flex items-center justify-center shrink-0 shadow-md">
                    <i data-lucide="shopping-bag" class="w-7 h-7"></i>
                </div>
                <div class="space-y-1.5">
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <span class="font-mono font-extrabold text-xs px-2.5 py-1 rounded bg-zinc-100 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 border border-zinc-200 dark:border-zinc-700">
                            {{ $purchaseDeal['po_number'] }}
                        </span>
                        <h1 class="text-2xl font-extrabold text-zinc-950 dark:text-white tracking-tight">
                            {{ $purchaseDeal['title'] }}
                        </h1>

                        <!-- Stage Badge -->
                        @php
                            $stage = $purchaseDeal['stage'] ?? 'Draft';
                            $stageBadges = [
                                'Draft' => 'bg-zinc-100 text-zinc-700 border-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:border-zinc-700',
                                'RFQ Sent' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800',
                                'Quote Received' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800',
                                'PO Issued' => 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/40 dark:text-purple-300 dark:border-purple-800',
                                'In Production' => 'bg-sky-50 text-sky-700 border-sky-200 dark:bg-sky-950/40 dark:text-sky-300 dark:border-sky-800',
                                'Completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800',
                            ];
                            $stageClass = $stageBadges[$stage] ?? 'bg-zinc-100 text-zinc-700 border-zinc-200';
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $stageClass }}">
                            ● {{ $stage }}
                        </span>

                        <!-- Payment Terms Badge -->
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-semibold bg-zinc-100 text-zinc-800 border border-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:border-zinc-700">
                            Terms: <strong>{{ $purchaseDeal['payment_terms'] ?? 'Net 30' }}</strong>
                        </span>
                    </div>

                    <div class="text-xs text-zinc-600 dark:text-zinc-400 flex items-center gap-3 flex-wrap">
                        @if(!empty($purchaseDeal['vendor_name']) && $purchaseDeal['vendor_name'] !== 'Unlinked Vendor')
                            <a href="/accounts/{{ $purchaseDeal['account_id'] ?? '' }}" class="font-semibold text-zinc-900 dark:text-zinc-200 hover:text-indigo-600 flex items-center gap-1">
                                <i data-lucide="building-2" class="w-3.5 h-3.5 text-zinc-400"></i>
                                <span>Supplier: <strong>{{ $purchaseDeal['vendor_name'] }}</strong></span>
                            </a>
                            <span>•</span>
                        @endif

                        @if(!empty($purchaseDeal['contact_name']))
                            <a href="/contacts/{{ $purchaseDeal['contact_id'] ?? '' }}" class="hover:text-indigo-600 flex items-center gap-1">
                                <i data-lucide="user" class="w-3.5 h-3.5 text-zinc-400"></i>
                                <span>Vendor Rep: <strong>{{ $purchaseDeal['contact_name'] }}</strong></span>
                            </a>
                            <span>•</span>
                        @endif

                        <div class="flex items-center gap-1.5 text-zinc-700 dark:text-zinc-300">
                            <span class="text-zinc-400 font-medium">Assigned Buyer:</span>
                            <span class="w-4 h-4 rounded-full bg-amber-100 text-amber-800 font-bold text-[9px] inline-flex items-center justify-center">
                                {{ strtoupper(substr($purchaseDeal['owner_name'] ?? 'B', 0, 1)) }}
                            </span>
                            <span class="font-semibold">{{ $purchaseDeal['owner_name'] ?? 'Unassigned' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Total PO Spend & Quick Action Buttons -->
            <div class="flex flex-col items-start md:items-end gap-3 shrink-0">
                <div class="text-left md:text-right">
                    <div class="text-[11px] font-semibold text-zinc-500 uppercase tracking-wider">TOTAL PO COMMITMENT</div>
                    <div class="text-3xl font-black text-zinc-950 dark:text-white tracking-tight">
                        {{ $purchaseDeal['amount_formatted'] ?? '$0.00' }}
                    </div>
                    <span class="text-[11px] text-zinc-400">{{ $purchaseDeal['currency'] ?? 'USD' }} Currency</span>
                </div>

                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        onclick="window.openEditCurrentDealModal()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs cursor-pointer"
                    >
                        <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                        <span>Edit PO</span>
                    </button>
                    <button
                        type="button"
                        onclick="window.deleteCurrentPurchaseDeal()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-red-200 dark:border-red-900 bg-red-50 dark:bg-red-950/40 text-xs font-semibold text-red-600 dark:text-red-300 hover:bg-red-100 dark:hover:bg-red-900/60 transition-colors cursor-pointer"
                    >
                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                        <span>Delete</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Procurement Stage Progress Stepper -->
        <div class="mt-6 pt-5 border-t border-zinc-200 dark:border-zinc-800">
            <div class="text-xs font-bold text-zinc-500 uppercase tracking-wider mb-2.5">
                Procurement Pipeline Progression
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2">
                @php
                    $allStages = ['Draft', 'RFQ Sent', 'Quote Received', 'PO Issued', 'In Production', 'Completed'];
                    $curStageIdx = array_search($purchaseDeal['stage'] ?? 'Draft', $allStages);
                    if ($curStageIdx === false) $curStageIdx = 0;
                @endphp

                @foreach($allStages as $idx => $stName)
                    @php
                        $isCurrent = ($idx === $curStageIdx);
                        $isPast = ($idx < $curStageIdx);
                    @endphp
                    <div
                        class="rounded-xl p-2.5 border transition-all text-center cursor-pointer {{ $isCurrent ? 'bg-indigo-50 border-indigo-300 dark:bg-indigo-950/40 dark:border-indigo-700 text-indigo-900 dark:text-indigo-200 shadow-xs' : ($isPast ? 'bg-zinc-50 border-zinc-200 dark:bg-zinc-800/40 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300' : 'bg-transparent border-dashed border-zinc-200 dark:border-zinc-800 text-zinc-400') }}"
                        onclick="window.advanceCurrentPurchaseDealStage('{{ $stName }}')"
                        title="Click to advance stage to {{ $stName }}"
                    >
                        <div class="flex items-center justify-center gap-1.5 text-xs font-bold mb-0.5">
                            @if($isPast)
                                <i data-lucide="check" class="w-3.5 h-3.5 text-emerald-600"></i>
                            @elseif($isCurrent)
                                <span class="w-2 h-2 rounded-full bg-indigo-600 animate-pulse"></span>
                            @endif
                            <span>{{ $stName }}</span>
                        </div>
                        <div class="text-[10px] {{ $isCurrent ? 'text-indigo-600 dark:text-indigo-400 font-semibold' : 'text-zinc-400' }}">
                            {{ $isCurrent ? 'Active Stage' : ($isPast ? 'Completed' : 'Upcoming') }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Segmented Navigation Tabs -->
    <div class="flex items-center gap-1 bg-zinc-100 dark:bg-zinc-800/70 p-1 rounded-xl w-fit border border-zinc-200 dark:border-zinc-700">
        <button
            type="button"
            id="tab-btn-overview"
            onclick="window.switchPurchaseDealTab('overview')"
            class="tab-btn active inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all cursor-pointer bg-white dark:bg-zinc-700 text-zinc-950 dark:text-white shadow-2xs"
        >
            <i data-lucide="layout" class="w-3.5 h-3.5"></i>
            <span>360° Overview</span>
        </button>
        <button
            type="button"
            id="tab-btn-customfields"
            onclick="window.switchPurchaseDealTab('customfields')"
            class="tab-btn inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-medium transition-all cursor-pointer text-zinc-600 dark:text-zinc-400 hover:text-zinc-950 dark:hover:text-white"
        >
            <i data-lucide="sliders" class="w-3.5 h-3.5"></i>
            <span>Procurement &amp; Custom Fields</span>
        </button>
        <button
            type="button"
            id="tab-btn-opportunities"
            onclick="window.switchPurchaseDealTab('opportunities')"
            class="tab-btn inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-medium transition-all cursor-pointer text-zinc-600 dark:text-zinc-400 hover:text-zinc-950 dark:hover:text-white"
        >
            <i data-lucide="briefcase" class="w-3.5 h-3.5"></i>
            <span>Linked Sales Deals ({{ $purchaseDeal['linked_opportunities_count'] ?? 0 }})</span>
        </button>
    </div>

    <!-- TAB PANE 1: 360° OVERVIEW -->
    <div id="tab-pane-overview" class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left 2 Cols: Details & Timeline -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Order Details Card -->
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-5 border border-zinc-200 dark:border-zinc-800 shadow-xs space-y-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-500">
                        Purchase Order Specifications
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div>
                            <span class="text-zinc-400 block mb-0.5">PO Number</span>
                            <span class="font-mono font-bold text-zinc-900 dark:text-zinc-100 text-sm">
                                {{ $purchaseDeal['po_number'] }}
                            </span>
                        </div>
                        <div>
                            <span class="text-zinc-400 block mb-0.5">Order Title</span>
                            <span class="font-semibold text-zinc-900 dark:text-zinc-100">
                                {{ $purchaseDeal['title'] }}
                            </span>
                        </div>
                        <div>
                            <span class="text-zinc-400 block mb-0.5">Total Commitment</span>
                            <span class="font-bold text-zinc-950 dark:text-zinc-100 text-sm">
                                {{ $purchaseDeal['amount_formatted'] }} ({{ $purchaseDeal['currency'] }})
                            </span>
                        </div>
                        <div>
                            <span class="text-zinc-400 block mb-0.5">Target Delivery Date</span>
                            <span class="font-medium text-zinc-800 dark:text-zinc-200">
                                {{ $purchaseDeal['expected_delivery_date_formatted'] ?? 'Pending Schedule' }}
                            </span>
                        </div>
                        <div>
                            <span class="text-zinc-400 block mb-0.5">Agreed Payment Terms</span>
                            <span class="font-semibold text-zinc-800 dark:text-zinc-200">
                                {{ $purchaseDeal['payment_terms'] ?? 'Net 30' }}
                            </span>
                        </div>
                        <div>
                            <span class="text-zinc-400 block mb-0.5">Assigned Buyer</span>
                            <span class="font-medium text-zinc-800 dark:text-zinc-200">
                                {{ $purchaseDeal['owner_name'] ?? 'Unassigned' }}
                            </span>
                        </div>
                    </div>

                    @if(!empty($purchaseDeal['notes']))
                        <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800">
                            <span class="text-zinc-400 block text-xs mb-1">Order Notes &amp; Instructions</span>
                            <div class="p-3 rounded-xl bg-zinc-50 dark:bg-zinc-800/40 text-xs text-zinc-700 dark:text-zinc-300 leading-relaxed whitespace-pre-line">
                                {{ $purchaseDeal['notes'] }}
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Supplier Vendor Details Card -->
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-5 border border-zinc-200 dark:border-zinc-800 shadow-xs space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-500">
                            Supplier &amp; Commercial Vendor Information
                        </h3>
                        @if(!empty($purchaseDeal['account_id']))
                            <a href="/accounts/{{ $purchaseDeal['account_id'] }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 flex items-center gap-1">
                                <span>Open Vendor 360</span>
                                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                            </a>
                        @endif
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div>
                            <span class="text-zinc-400 block mb-0.5">Supplier Name</span>
                            <span class="font-bold text-zinc-900 dark:text-zinc-100">
                                {{ $purchaseDeal['vendor_name'] }}
                            </span>
                        </div>
                        <div>
                            <span class="text-zinc-400 block mb-0.5">Vendor Representative</span>
                            <span class="font-medium text-zinc-800 dark:text-zinc-200">
                                {{ $purchaseDeal['contact_name'] ?? 'No representative assigned' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Col: Quick Highlights & Governance -->
            <div class="space-y-6">
                <!-- Governance & Security Card -->
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-5 border border-zinc-200 dark:border-zinc-800 shadow-xs space-y-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-500 flex items-center gap-1.5">
                        <i data-lucide="shield-check" class="w-4 h-4 text-indigo-600"></i>
                        <span>Security &amp; Governance</span>
                    </h3>
                    <div class="space-y-3 text-xs">
                        <div class="flex items-center justify-between pb-2 border-b border-zinc-100 dark:border-zinc-800">
                            <span class="text-zinc-500">Active Profile</span>
                            <span class="font-bold text-zinc-800 dark:text-zinc-200">{{ $currentUserProfile ?? 'Administrator' }}</span>
                        </div>
                        <div class="flex items-center justify-between pb-2 border-b border-zinc-100 dark:border-zinc-800">
                            <span class="text-zinc-500">Custom Fields Synced</span>
                            <span class="font-bold text-emerald-600">{{ count($customFields ?? []) }} Fields</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-zinc-500">Backing Revenue Link</span>
                            <span class="font-bold text-indigo-600">{{ $purchaseDeal['linked_opportunities_count'] > 0 ? 'Coupled' : 'Independent' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Record Metadata Card -->
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-5 border border-zinc-200 dark:border-zinc-800 shadow-xs space-y-3 text-xs">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-500">Record Audit Trail</h3>
                    <div class="space-y-2 text-zinc-500">
                        <div class="flex items-center justify-between">
                            <span>Record ID:</span>
                            <span class="font-mono text-zinc-800 dark:text-zinc-200">#{{ $purchaseDeal['id'] }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>Created At:</span>
                            <span class="text-zinc-800 dark:text-zinc-200">{{ date('M d, Y h:i A', strtotime($purchaseDeal['created_at'])) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>Last Updated:</span>
                            <span class="text-zinc-800 dark:text-zinc-200">{{ date('M d, Y h:i A', strtotime($purchaseDeal['updated_at'])) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB PANE 2: DYNAMIC CUSTOM FIELDS -->
    <div id="tab-pane-customfields" class="hidden space-y-4">
        <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 border border-zinc-200 dark:border-zinc-800 shadow-xs space-y-6">
            <div class="flex items-center justify-between pb-3 border-b border-zinc-200 dark:border-zinc-800">
                <div>
                    <h2 class="text-base font-bold text-zinc-950 dark:text-white">
                        Dynamic Procurement Custom Fields
                    </h2>
                    <p class="text-xs text-zinc-500">
                        Configured via Object Fields Studio and governed by Field-Level Security Permissions.
                    </p>
                </div>
                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300">
                    {{ count($customFields ?? []) }} Fields Active
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @forelse($customFields ?? [] as $cf)
                    @php
                        $perm = $fieldPermissions[$cf->field_name] ?? 'Read/Write';
                        if ($perm === 'Hidden') continue;

                        $val = $purchaseDeal[$cf->field_name] ?? ($purchaseDeal['custom_data'][$cf->field_name] ?? null);
                    @endphp
                    <div class="p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-800/40 space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-semibold text-zinc-500 uppercase tracking-wider">
                                {{ $cf->field_label }}
                            </span>
                            @if($perm === 'Read-Only')
                                <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                                    Read-Only
                                </span>
                            @endif
                        </div>
                        <div class="text-sm font-bold text-zinc-950 dark:text-zinc-100">
                            {{ $val ? $val : '—' }}
                        </div>
                        @if($cf->help_text)
                            <p class="text-[10px] text-zinc-400">{{ $cf->help_text }}</p>
                        @endif
                    </div>
                @empty
                    <div class="col-span-3 py-12 text-center text-zinc-400 text-xs italic">
                        No dynamic custom fields configured for Purchase Deals.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- TAB PANE 3: LINKED SALES OPPORTUNITIES -->
    <div id="tab-pane-opportunities" class="hidden space-y-4">
        <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 border border-zinc-200 dark:border-zinc-800 shadow-xs space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-zinc-950 dark:text-white">
                        Coupled Sales Opportunities (Revenue Deals)
                    </h2>
                    <p class="text-xs text-zinc-500">
                        Sales pipeline opportunities backed by this procurement order for margin calculations.
                    </p>
                </div>
            </div>

            @if(!empty($purchaseDeal['linked_opportunities']) && count($purchaseDeal['linked_opportunities']) > 0)
                <div class="divide-y divide-zinc-200 dark:divide-zinc-800">
                    @foreach($purchaseDeal['linked_opportunities'] as $opp)
                        <div class="py-3 flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-950/50 flex items-center justify-center text-indigo-600">
                                    <i data-lucide="briefcase" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <a href="/opportunities/{{ $opp['id'] }}" class="text-xs font-bold text-zinc-900 dark:text-zinc-100 hover:text-indigo-600 no-underline hover:no-underline">
                                        {{ $opp['name'] }}
                                    </a>
                                    <div class="text-[11px] text-zinc-500">Stage: <strong>{{ $opp['stage'] }}</strong></div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-xs font-bold text-zinc-950 dark:text-white">
                                    ${{ number_format($opp['amount'] ?? 0, 2) }}
                                </div>
                                <span class="text-[10px] text-emerald-600 font-semibold">Revenue Pipeline</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="py-12 text-center space-y-2">
                    <div class="w-10 h-10 mx-auto rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-400">
                        <i data-lucide="briefcase" class="w-5 h-5"></i>
                    </div>
                    <p class="text-xs text-zinc-500">No sales opportunities currently linked to this purchase order.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Safe Blade JSON Hydration with Zero Bracket/Parentheses Nesting Issues -->
@php
    $purchaseDealDetailData = [
        'purchaseDeal' => $purchaseDeal ?? [],
        'customFields' => $customFields ?? [],
        'fieldPermissions' => $fieldPermissions ?? (object)[],
        'users' => $users ?? [],
        'accounts' => $accounts ?? [],
        'contacts' => $contacts ?? [],
        'stages' => $stages ?? ['Draft', 'RFQ Sent', 'Quote Received', 'PO Issued', 'In Production', 'Completed'],
        'currentUserProfile' => $currentUserProfile ?? 'Administrator',
    ];
@endphp
<script>
    window.__PURCHASE_DEAL_DETAIL_DATA__ = {!! json_encode($purchaseDealDetailData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};
</script>
<script src="{{ asset('js/purchase_deal_detail.js') }}?v=1.0"></script>
@endsection
