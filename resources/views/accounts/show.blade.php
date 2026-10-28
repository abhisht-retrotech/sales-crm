@extends('layouts.app')

@section('title', ($account['name'] ?? 'Account') . ' — 360° Account View — DealSync CRM')

@section('content')
<!-- Centered Max-Width Container matching React page-wrapper (max-width: 1240px, padding: 2.25rem 2.5rem) -->
<div id="account-show-page-root" class="max-w-[1240px] mx-auto w-full px-6 sm:px-10 py-7 space-y-6 animate-fade-in">
    <!-- Breadcrumb & Back Action -->
    <div class="flex items-center justify-between gap-4 flex-wrap">
        <div class="flex items-center gap-2 text-xs">
            <a href="/accounts" class="spa-nav-link inline-flex items-center gap-1.5 font-semibold text-zinc-500 hover:text-indigo-600 dark:text-zinc-400 dark:hover:text-indigo-400 transition-colors">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Accounts Directory</span>
            </a>
            <span class="text-zinc-300 dark:text-zinc-700">/</span>
            <span class="text-zinc-600 dark:text-zinc-400 font-medium">{{ $account['type'] ?? 'Account' }}</span>
            <span class="text-zinc-300 dark:text-zinc-700">/</span>
            <span class="text-zinc-950 dark:text-zinc-100 font-bold">{{ $account['name'] }}</span>
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
            <!-- Left: Avatar, Name, Type, Info -->
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-blue-600 text-white font-extrabold text-xl flex items-center justify-center shrink-0 shadow-md">
                    {{ strtoupper(substr($account['name'] ?? 'A', 0, 1)) }}
                </div>
                <div class="space-y-1.5">
                    <div class="flex items-center gap-3 flex-wrap">
                        <h1 class="text-2xl font-extrabold text-zinc-950 dark:text-white tracking-tight">
                            {{ $account['name'] }}
                        </h1>
                        <!-- Classification Badge -->
                        @php
                            $accType = $account['type'] ?? 'Customer';
                            $typeBadges = [
                                'Customer' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800',
                                'Vendor' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800',
                                'Prospect' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800',
                                'Partner' => 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/40 dark:text-purple-300 dark:border-purple-800',
                            ];
                            $typeClass = $typeBadges[$accType] ?? 'bg-zinc-100 text-zinc-700 border-zinc-200';
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $typeClass }}">
                            {{ $accType }}
                        </span>

                        @if(!empty($account['industry']))
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-xs font-semibold bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700">
                                <i data-lucide="briefcase" class="w-3 h-3 text-zinc-400"></i>
                                <span>{{ $account['industry'] }}</span>
                            </span>
                        @endif

                        @if(!empty($account['payment_terms']))
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-xs font-semibold bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700">
                                <i data-lucide="clock" class="w-3 h-3 text-zinc-400"></i>
                                <span>Terms: {{ $account['payment_terms'] }}</span>
                            </span>
                        @endif
                    </div>

                    <div class="text-xs text-zinc-600 dark:text-zinc-400 flex items-center gap-3 flex-wrap">
                        @if(!empty($account['website']))
                            <a href="{{ str_starts_with($account['website'], 'http') ? $account['website'] : 'https://' . $account['website'] }}" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1">
                                <i data-lucide="globe" class="w-3.5 h-3.5"></i>
                                <span>{{ $account['website'] }}</span>
                            </a>
                            <span>•</span>
                        @endif

                        @if(!empty($account['phone']))
                            <a href="tel:{{ $account['phone'] }}" class="hover:text-indigo-600 flex items-center gap-1">
                                <i data-lucide="phone" class="w-3.5 h-3.5"></i>
                                <span>{{ $account['phone'] }}</span>
                            </a>
                            <span>•</span>
                        @endif

                        <div class="flex items-center gap-1.5 text-zinc-700 dark:text-zinc-300">
                            <span class="text-zinc-400 font-medium">Owner:</span>
                            <span class="w-4 h-4 rounded-full bg-blue-100 dark:bg-blue-900/60 text-blue-700 dark:text-blue-300 font-bold text-[9px] inline-flex items-center justify-center">
                                {{ strtoupper(substr($account['owner_name'] ?? ($account['assigned_to'] ?? 'U'), 0, 1)) }}
                            </span>
                            <span class="font-semibold">{{ $account['owner_name'] ?? ($account['assigned_to'] ?? 'Unassigned') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Action Buttons -->
            <div class="flex items-center gap-2 flex-wrap shrink-0">
                <button
                    type="button"
                    onclick="window.openAccountDetailEditModal()"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs cursor-pointer"
                >
                    <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                    <span>Edit Account</span>
                </button>

                <button
                    type="button"
                    onclick="window.openAccountDetailTransferModal()"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs cursor-pointer"
                >
                    <i data-lucide="user-check" class="w-3.5 h-3.5"></i>
                    <span>Transfer Owner</span>
                </button>

                <button
                    type="button"
                    onclick="window.deleteCurrentAccount()"
                    class="p-2 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-zinc-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/40 transition-colors shadow-2xs cursor-pointer"
                    title="Delete Account"
                >
                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                </button>
            </div>
        </div>

        <!-- 3 Quick KPI Cards inside Hero -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-5 mt-5 border-t border-zinc-100 dark:border-zinc-800">
            <div class="bg-zinc-50/80 dark:bg-zinc-800/40 p-3 rounded-xl border border-zinc-200 dark:border-zinc-700/60">
                <div class="text-[10px] font-semibold text-zinc-400 uppercase tracking-wider">Credit Line Extended</div>
                <div class="text-lg font-bold text-zinc-950 dark:text-zinc-100 mt-0.5">
                    ${{ number_format($account['credit_limit'] ?? 0, 0) }}
                </div>
            </div>

            <div class="bg-zinc-50/80 dark:bg-zinc-800/40 p-3 rounded-xl border border-zinc-200 dark:border-zinc-700/60">
                <div class="text-[10px] font-semibold text-zinc-400 uppercase tracking-wider">Annual Revenue</div>
                <div class="text-lg font-bold text-zinc-950 dark:text-zinc-100 mt-0.5">
                    ${{ number_format($account['annual_revenue'] ?? 0, 0) }}
                </div>
            </div>

            <div class="bg-zinc-50/80 dark:bg-zinc-800/40 p-3 rounded-xl border border-zinc-200 dark:border-zinc-700/60">
                <div class="text-[10px] font-semibold text-zinc-400 uppercase tracking-wider">Operational Graph Entities</div>
                <div class="text-lg font-bold text-zinc-950 dark:text-zinc-100 mt-0.5">
                    {{ count($contacts ?? []) }} Contacts • {{ count($opportunities ?? []) }} Deals
                </div>
            </div>
        </div>
    </div>

    <!-- Apple / Linear Segmented Tabs Bar -->
    <div class="flex items-center gap-1.5 p-1 rounded-xl bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 overflow-x-auto">
        <button
            type="button"
            onclick="window.switchDetailTab('overview')"
            id="tab-btn-overview"
            class="px-4 py-1.5 text-xs font-semibold rounded-lg bg-white dark:bg-zinc-700 text-zinc-950 dark:text-zinc-100 shadow-xs cursor-pointer whitespace-nowrap"
        >
            Company Overview
        </button>
        <button
            type="button"
            onclick="window.switchDetailTab('contacts')"
            id="tab-btn-contacts"
            class="px-4 py-1.5 text-xs font-medium rounded-lg text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-200 cursor-pointer whitespace-nowrap"
        >
            Contacts ({{ count($contacts ?? []) }})
        </button>
        <button
            type="button"
            onclick="window.switchDetailTab('sales')"
            id="tab-btn-sales"
            class="px-4 py-1.5 text-xs font-medium rounded-lg text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-200 cursor-pointer whitespace-nowrap"
        >
            Sales Deals ({{ count($opportunities ?? []) }})
        </button>
        <button
            type="button"
            onclick="window.switchDetailTab('purchases')"
            id="tab-btn-purchases"
            class="px-4 py-1.5 text-xs font-medium rounded-lg text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-200 cursor-pointer whitespace-nowrap"
        >
            PO Deals ({{ count($purchaseDeals ?? []) }})
        </button>
        <button
            type="button"
            onclick="window.switchDetailTab('shipping')"
            id="tab-btn-shipping"
            class="px-4 py-1.5 text-xs font-medium rounded-lg text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-200 cursor-pointer whitespace-nowrap"
        >
            Logistics ({{ count($shipments ?? []) }})
        </button>
        <button
            type="button"
            onclick="window.switchDetailTab('finance')"
            id="tab-btn-finance"
            class="px-4 py-1.5 text-xs font-medium rounded-lg text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-200 cursor-pointer whitespace-nowrap"
        >
            Invoices ({{ count($payments ?? []) }})
        </button>
    </div>

    <!-- TAB 1: OVERVIEW (Dynamic Page Layout Integration - 1, 2, or 3 Columns) -->
    <div id="tab-content-overview" class="space-y-6">
        @php
            $layoutJson = $pageLayout->layout_json ?? ($pageLayout->layout ?? null);
            if (is_string($layoutJson)) {
                $layoutJson = json_decode($layoutJson, true) ?: [];
            }
            $layoutSections = $layoutJson['sections'] ?? [];

            $customFieldsMap = collect($customFields ?? [])->keyBy('field_name');
            $standardOverridesMap = collect($standardOverrides ?? [])->keyBy('field_name');

            if (empty($layoutSections)) {
                $layoutSections = [
                    [
                        'id' => 'sec-primary',
                        'title' => 'Account Detail',
                        'columns' => 2,
                        'collapsible' => true,
                        'defaultCollapsed' => false,
                        'fields' => [
                            ['name' => 'name', 'label' => 'Account Name', 'required' => true],
                            ['name' => 'type', 'label' => 'Account Type', 'required' => true],
                            ['name' => 'industry', 'label' => 'Industry'],
                            ['name' => 'website', 'label' => 'Website URL'],
                            ['name' => 'phone', 'label' => 'Phone'],
                            ['name' => 'billing_address', 'label' => 'Billing Address'],
                            ['name' => 'shipping_address', 'label' => 'Shipping Address'],
                            ['name' => 'payment_terms', 'label' => 'Payment Terms'],
                            ['name' => 'credit_limit', 'label' => 'Credit Limit'],
                            ['name' => 'annual_revenue', 'label' => 'Annual Revenue'],
                        ]
                    ],
                    [
                        'id' => 'sec-system-info',
                        'title' => 'System Information',
                        'columns' => 3,
                        'collapsible' => true,
                        'defaultCollapsed' => false,
                        'fields' => [
                            ['name' => 'owner', 'label' => 'Current Owner'],
                            ['name' => 'created_at', 'label' => 'Created Date & Time'],
                            ['name' => 'created_by', 'label' => 'Created By'],
                            ['name' => 'updated_at', 'label' => 'Last Modified Date & Time'],
                            ['name' => 'updated_by', 'label' => 'Last Modified By'],
                            ['name' => 'id', 'label' => 'Record ID'],
                        ]
                    ]
                ];
            }

            // Track placed fields to identify any unplaced custom fields
            $placedFieldNames = [];
            foreach ($layoutSections as $s) {
                foreach ($s['fields'] ?? [] as $f) {
                    $fn = is_array($f) ? ($f['name'] ?? null) : $f;
                    if ($fn) $placedFieldNames[] = $fn;
                }
            }
            $unplacedCfs = collect($customFields ?? [])->filter(function($cf) use ($placedFieldNames, $fieldPermissions) {
                return ($fieldPermissions[$cf->field_name] ?? 'Read/Write') !== 'Hidden' && !in_array($cf->field_name, $placedFieldNames);
            });
        @endphp

        @foreach($layoutSections as $secIndex => $sec)
            @php
                $secId = $sec['id'] ?? 'sec-' . $secIndex;
                $secTitle = $sec['title'] ?? 'Section ' . ($secIndex + 1);
                $secColumns = (int)($sec['columns'] ?? 2);
                if (!in_array($secColumns, [1, 2, 3])) {
                    $secColumns = 2;
                }
                $isCollapsible = !isset($sec['collapsible']) || (bool)$sec['collapsible'];
                $defaultCollapsed = !empty($sec['defaultCollapsed']);
                $secFields = $sec['fields'] ?? [];
                $isSystemSection = ($secId === 'sec-system-info') || (stripos($secTitle, 'System Information') !== false);

                $bodyId = 'showSecBody_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $secId);
                $chevronId = 'showSecChevron_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $secId);

                if ($secColumns === 1) {
                    $gridColsClass = 'grid-cols-1 gap-y-4';
                } elseif ($secColumns === 3) {
                    $gridColsClass = 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-y-5 gap-x-8';
                } else {
                    $gridColsClass = 'grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-4';
                }
            @endphp

            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs overflow-hidden">
                <div
                    @if($isCollapsible) onclick="window.toggleShowDetailSection('{{ $bodyId }}', '{{ $chevronId }}')" @endif
                    class="px-6 py-4 bg-zinc-50/60 dark:bg-zinc-800/40 border-b border-zinc-100 dark:border-zinc-800 flex items-center justify-between {{ $isCollapsible ? 'cursor-pointer select-none' : '' }}"
                >
                    <div class="flex items-center gap-2.5 font-bold text-xs sm:text-sm text-zinc-900 dark:text-zinc-100">
                        @if($isCollapsible)
                        <svg id="{{ $chevronId }}" class="w-4 h-4 text-zinc-400 transition-transform duration-200 {{ $defaultCollapsed ? '-rotate-90' : '' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                        @endif

                        @if($isSystemSection)
                        <svg class="w-4 h-4 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        </svg>
                        @else
                        <svg class="w-4 h-4 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/>
                        </svg>
                        @endif

                        <span>{{ $secTitle }}</span>
                    </div>

                    <span class="px-2.5 py-0.5 rounded text-[11px] font-semibold bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700">
                        {{ $secColumns }} {{ $secColumns === 1 ? 'Column' : 'Columns' }}
                    </span>
                </div>

                <div id="{{ $bodyId }}" class="p-6 text-xs {{ $defaultCollapsed ? 'hidden' : '' }}">
                    <div class="grid {{ $gridColsClass }}">
                        @if($isSystemSection)
                            <div class="py-2 border-b border-zinc-100 dark:border-zinc-800">
                                <div class="text-[11px] text-zinc-400 font-medium">Current Owner</div>
                                <div class="font-bold text-zinc-900 dark:text-zinc-100 mt-1 flex items-center gap-2">
                                    <span>{{ $account['owner_name'] ?? ($account['assigned_to'] ?? 'System Administrator') }}</span>
                                    <button type="button" onclick="window.openAccountDetailTransferModal()" class="px-2 py-0.5 text-[11px] font-semibold text-teal-700 bg-teal-50 border border-teal-200 rounded-md hover:bg-teal-100 transition-colors cursor-pointer">Change</button>
                                </div>
                            </div>
                            <div class="py-2 border-b border-zinc-100 dark:border-zinc-800">
                                <div class="text-[11px] text-zinc-400 font-medium">Created Date &amp; Time</div>
                                <div class="font-bold text-zinc-900 dark:text-zinc-100 mt-1">{{ !empty($account['created_at']) ? date('M d, Y h:i A', strtotime($account['created_at'])) : '—' }}</div>
                            </div>
                            <div class="py-2 border-b border-zinc-100 dark:border-zinc-800">
                                <div class="text-[11px] text-zinc-400 font-medium">Created By</div>
                                <div class="font-bold text-zinc-900 dark:text-zinc-100 mt-1">{{ $account['created_by_name'] ?? 'System Admin' }}</div>
                            </div>
                            <div class="py-2 border-b border-zinc-100 dark:border-zinc-800">
                                <div class="text-[11px] text-zinc-400 font-medium">Last Modified Date &amp; Time</div>
                                <div class="font-bold text-zinc-900 dark:text-zinc-100 mt-1">{{ !empty($account['updated_at']) ? date('M d, Y h:i A', strtotime($account['updated_at'])) : '—' }}</div>
                            </div>
                            <div class="py-2 border-b border-zinc-100 dark:border-zinc-800">
                                <div class="text-[11px] text-zinc-400 font-medium">Last Modified By</div>
                                <div class="font-bold text-zinc-900 dark:text-zinc-100 mt-1">{{ $account['updated_by_name'] ?? 'System Admin' }}</div>
                            </div>
                            <div class="py-2 border-b border-zinc-100 dark:border-zinc-800">
                                <div class="text-[11px] text-zinc-400 font-medium">Record ID</div>
                                <div class="mt-1">
                                    <span class="px-2 py-0.5 rounded font-mono text-[11px] bg-sky-50 dark:bg-sky-950/40 text-sky-700 dark:text-sky-300 border border-sky-100 dark:border-sky-800">
                                        {{ $account['uuid'] ?? ('ACC-' . str_pad($account['id'] ?? 1, 5, '0', STR_PAD_LEFT)) }}
                                    </span>
                                </div>
                            </div>
                        @else
                            @foreach($secFields as $f)
                                @php
                                    $fName = is_array($f) ? ($f['name'] ?? '') : $f;
                                    if (empty($fName)) continue;

                                    $perm = $fieldPermissions[$fName] ?? 'Read/Write';
                                    if ($perm === 'Hidden') continue;

                                    $isRo = ($perm === 'Read-Only') || !empty($f['readOnly']);
                                    $override = $standardOverridesMap[$fName] ?? null;
                                    $cf = $customFieldsMap[$fName] ?? null;

                                    $isReq = !empty($f['required']) || (!empty($override->is_required));
                                    $label = $override->field_label ?? ($f['label'] ?? ($cf->field_label ?? ucwords(str_replace('_', ' ', $fName))));

                                    $rawVal = $account[$fName] ?? ($account['custom_data'][$fName] ?? null);
                                    $isFullSpan = ($secColumns === 1) || in_array($fName, ['billing_address', 'shipping_address', 'notes', 'description']) || ($cf && in_array($cf->data_type, ['textarea', 'rich_text'])) || (!empty($f['col_span']) && ($f['col_span'] === 'full' || (int)$f['col_span'] === 3));
                                @endphp

                                <div class="flex items-center justify-between py-2 border-b border-zinc-100 dark:border-zinc-800 group {{ $isFullSpan ? 'col-span-full' : '' }}">
                                    <div class="min-w-0 flex-1 pr-2">
                                        <div class="text-[11px] text-zinc-400 font-medium truncate">
                                            @if($isReq)<span class="text-rose-500 font-bold mr-0.5">*</span>@endif
                                            {{ $label }}
                                            @if($isRo)<span class="text-[10px] text-amber-600 font-bold ml-1">🔒</span>@endif
                                        </div>
                                        <div class="font-bold text-zinc-900 dark:text-zinc-100 mt-1 text-xs">
                                            @if($fName === 'annual_revenue' || $fName === 'credit_limit' || ($cf && $cf->data_type === 'currency'))
                                                ${{ number_format((float)($rawVal ?? 0), 0) }}
                                            @elseif($fName === 'website' && !empty($rawVal))
                                                <a href="{{ str_starts_with($rawVal, 'http') ? $rawVal : 'https://' . $rawVal }}" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:underline">{{ $rawVal }}</a>
                                            @elseif($fName === 'phone' && !empty($rawVal))
                                                <a href="tel:{{ $rawVal }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">{{ $rawVal }}</a>
                                            @elseif($fName === 'type' && !empty($rawVal))
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold border {{ $typeBadges[$rawVal] ?? 'bg-zinc-100 text-zinc-700 border-zinc-200' }}">{{ $rawVal }}</span>
                                            @elseif(in_array($fName, ['billing_address', 'shipping_address', 'notes', 'description']) && !empty($rawVal))
                                                <span class="whitespace-pre-line text-zinc-800 dark:text-zinc-200">{{ $rawVal }}</span>
                                            @else
                                                {{ ($rawVal !== null && $rawVal !== '') ? $rawVal : '—' }}
                                            @endif
                                        </div>
                                    </div>
                                    @if(!$isRo)
                                    <button type="button" onclick="window.openAccountDetailEditModal()" class="text-zinc-300 group-hover:text-indigo-600 transition-colors p-1 cursor-pointer shrink-0" title="Edit {{ $label }}">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                                    </button>
                                    @endif
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        @endforeach

        @if($unplacedCfs->count() > 0)
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs overflow-hidden">
                <div class="px-6 py-4 bg-zinc-50/60 dark:bg-zinc-800/40 border-b border-zinc-100 dark:border-zinc-800 flex items-center justify-between">
                    <h3 class="text-xs sm:text-sm font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                        <i data-lucide="sliders" class="w-4 h-4 text-indigo-600"></i> Additional Custom Information
                    </h3>
                    <span class="px-2.5 py-0.5 rounded text-[11px] font-semibold bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700">
                        2 Columns
                    </span>
                </div>
                <div class="p-6 text-xs">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-4">
                        @foreach($unplacedCfs as $cf)
                            @php
                                $fName = $cf->field_name;
                                $perm = $fieldPermissions[$fName] ?? 'Read/Write';
                                $isRo = ($perm === 'Read-Only');
                                $val = $account[$fName] ?? ($account['custom_data'][$fName] ?? null);
                            @endphp
                            <div class="flex items-center justify-between py-2 border-b border-zinc-100 dark:border-zinc-800 group">
                                <div class="min-w-0 flex-1 pr-2">
                                    <div class="text-[11px] text-zinc-400 font-medium">
                                        {{ $cf->field_label }}
                                        @if($isRo)<span class="text-[10px] text-amber-600 font-bold ml-1">🔒</span>@endif
                                    </div>
                                    <div class="font-bold text-zinc-900 dark:text-zinc-100 mt-1 text-xs">
                                        @if($cf->data_type === 'currency' && $val)
                                            ${{ number_format((float)$val, 0) }}
                                        @else
                                            {{ $val ?: '—' }}
                                        @endif
                                    </div>
                                </div>
                                @if(!$isRo)
                                <button type="button" onclick="window.openAccountDetailEditModal()" class="text-zinc-300 group-hover:text-indigo-600 transition-colors p-1 cursor-pointer shrink-0">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                                </button>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        @if(!empty($account['notes']))
            <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 border border-zinc-200 dark:border-zinc-800 shadow-xs space-y-2">
                <h3 class="text-xs font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider flex items-center gap-2">
                    <i data-lucide="align-left" class="w-4 h-4 text-indigo-600"></i> Account Notes &amp; History
                </h3>
                <p class="text-xs text-zinc-700 dark:text-zinc-300 whitespace-pre-wrap">
                    {{ $account['notes'] }}
                </p>
            </div>
        @endif
    </div>

    <!-- TAB 2: CONTACTS -->
    <div id="tab-content-contacts" class="hidden space-y-3">
        @forelse($contacts ?? [] as $c)
            <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 border border-zinc-200 dark:border-zinc-800 shadow-xs flex items-center justify-between gap-4 text-xs">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 font-bold flex items-center justify-center shrink-0">
                        {{ strtoupper(substr($c->first_name ?? 'C', 0, 1)) }}
                    </div>
                    <div>
                        <div class="font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                            <span>{{ $c->first_name }} {{ $c->last_name }}</span>
                            @if($c->is_primary)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">⭐ Primary Contact</span>
                            @endif
                        </div>
                        <div class="text-zinc-500 text-[11px] mt-0.5">
                            {{ $c->title ?? 'Executive' }} • {{ $c->email ?? 'No email' }} • {{ $c->phone ?? 'No phone' }}
                        </div>
                    </div>
                </div>
                <span class="px-2.5 py-1 rounded-md text-xs font-semibold bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700">
                    {{ $c->contact_role ?? 'Contact' }}
                </span>
            </div>
        @empty
            <div class="bg-white dark:bg-zinc-900 rounded-xl p-12 text-center border border-zinc-200 dark:border-zinc-800 text-zinc-400 text-xs">
                No contacts currently linked to this organization account.
            </div>
        @endforelse
    </div>

    <!-- TAB 3: SALES DEALS -->
    <div id="tab-content-sales" class="hidden space-y-3">
        @forelse($opportunities ?? [] as $o)
            <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 border border-zinc-200 dark:border-zinc-800 shadow-xs flex items-center justify-between gap-4 text-xs">
                <div>
                    <div class="font-bold text-zinc-900 dark:text-zinc-100">{{ $o->name }}</div>
                    <div class="text-zinc-500 text-[11px] mt-0.5">
                        Target Close: {{ $o->expected_close_date ?? '—' }} • Probability: {{ $o->probability ?? 0 }}%
                    </div>
                </div>
                <div class="text-right">
                    <div class="font-bold text-zinc-900 dark:text-zinc-100 text-sm">${{ number_format($o->amount ?? 0, 0) }}</div>
                    <span class="inline-block mt-0.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-950/40 dark:text-blue-300">
                        {{ $o->stage ?? 'Open Deal' }}
                    </span>
                </div>
            </div>
        @empty
            <div class="bg-white dark:bg-zinc-900 rounded-xl p-12 text-center border border-zinc-200 dark:border-zinc-800 text-zinc-400 text-xs">
                No active sales opportunities or deals linked to this account.
            </div>
        @endforelse
    </div>

    <!-- TAB 4: PO DEALS -->
    <div id="tab-content-purchases" class="hidden space-y-3">
        @forelse($purchaseDeals ?? [] as $p)
            <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 border border-zinc-200 dark:border-zinc-800 shadow-xs flex items-center justify-between gap-4 text-xs">
                <div>
                    <div class="font-bold text-zinc-900 dark:text-zinc-100">{{ $p->po_number ?? 'PO' }}: {{ $p->title }}</div>
                    <div class="text-zinc-500 text-[11px] mt-0.5">
                        Target Delivery: {{ $p->expected_delivery_date ?? '—' }}
                    </div>
                </div>
                <div class="text-right">
                    <div class="font-bold text-zinc-900 dark:text-zinc-100 text-sm">${{ number_format($p->amount ?? 0, 0) }}</div>
                    <span class="inline-block mt-0.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300">
                        {{ $p->stage ?? 'PO Active' }}
                    </span>
                </div>
            </div>
        @empty
            <div class="bg-white dark:bg-zinc-900 rounded-xl p-12 text-center border border-zinc-200 dark:border-zinc-800 text-zinc-400 text-xs">
                No supplier purchase orders or procurement deals on record for this account.
            </div>
        @endforelse
    </div>

    <!-- TAB 5: LOGISTICS -->
    <div id="tab-content-shipping" class="hidden space-y-3">
        @forelse($shipments ?? [] as $s)
            <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 border border-zinc-200 dark:border-zinc-800 shadow-xs flex items-center justify-between gap-4 text-xs">
                <div>
                    <div class="font-bold text-zinc-900 dark:text-zinc-100">{{ $s->carrier ?? 'Freight' }}: {{ $s->tracking_number }}</div>
                    <div class="text-zinc-500 text-[11px] mt-0.5">
                        {{ $s->type ?? 'Freight' }} • Estimated Delivery: {{ $s->estimated_delivery_date ?? '—' }}
                    </div>
                </div>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300">
                    {{ $s->status ?? 'In Transit' }}
                </span>
            </div>
        @empty
            <div class="bg-white dark:bg-zinc-900 rounded-xl p-12 text-center border border-zinc-200 dark:border-zinc-800 text-zinc-400 text-xs">
                No freight shipments or delivery dispatches recorded.
            </div>
        @endforelse
    </div>

    <!-- TAB 6: INVOICES -->
    <div id="tab-content-finance" class="hidden space-y-3">
        @forelse($payments ?? [] as $pay)
            <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 border border-zinc-200 dark:border-zinc-800 shadow-xs flex items-center justify-between gap-4 text-xs">
                <div>
                    <div class="font-bold text-zinc-900 dark:text-zinc-100">{{ $pay->invoice_number }} ({{ $pay->type ?? 'Invoice' }})</div>
                    <div class="text-zinc-500 text-[11px] mt-0.5">
                        Due: {{ $pay->due_date ?? '—' }} • Settled: ${{ number_format($pay->paid_amount ?? 0, 0) }} of ${{ number_format($pay->amount ?? 0, 0) }}
                    </div>
                </div>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold {{
                    $pay->status === 'Paid'
                        ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                        : ($pay->status === 'Overdue'
                            ? 'bg-red-50 text-red-700 border border-red-200'
                            : 'bg-amber-50 text-amber-700 border border-amber-200')
                }}">
                    {{ $pay->status ?? 'Pending' }}
                </span>
            </div>
        @empty
            <div class="bg-white dark:bg-zinc-900 rounded-xl p-12 text-center border border-zinc-200 dark:border-zinc-800 text-zinc-400 text-xs">
                No invoices, billing ledger aging, or wires on record for this account.
            </div>
        @endforelse
    </div>

    <!-- ========================================== -->
    <!-- EDIT MODAL FOR SHOW PAGE                    -->
    <!-- ========================================== -->
    <div id="account-detail-edit-modal" class="fixed inset-0 z-50 hidden bg-zinc-950/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
        <div class="bg-white dark:bg-zinc-900 rounded-2xl max-w-2xl w-full border border-zinc-200 dark:border-zinc-800 shadow-2xl overflow-hidden animate-scale-up my-8">
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between">
                <h3 class="text-sm font-bold text-zinc-950 dark:text-zinc-100">Edit Account: {{ $account['name'] }}</h3>
                <button type="button" onclick="window.closeAccountDetailEditModal()" class="text-zinc-400 hover:text-zinc-600 cursor-pointer">✕</button>
            </div>
            <form onsubmit="window.submitAccountDetailEdit(event)" class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
                <input type="hidden" id="edit-account-id" value="{{ $account['id'] }}">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div class="sm:col-span-2 space-y-1">
                        <label class="font-semibold text-zinc-700 dark:text-zinc-300">Account Name *</label>
                        <input type="text" id="detail-field-name" value="{{ $account['name'] }}" required class="w-full px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                    </div>
                    <div class="space-y-1">
                        <label class="font-semibold text-zinc-700 dark:text-zinc-300">Classification *</label>
                        <select id="detail-field-type" required class="w-full px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                            @foreach($types ?? ['Customer', 'Vendor', 'Prospect', 'Partner'] as $t)
                                <option value="{{ $t }}" {{ ($account['type'] ?? '') === $t ? 'selected' : '' }}>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="font-semibold text-zinc-700 dark:text-zinc-300">Industry Sector</label>
                        <input type="text" id="detail-field-industry" value="{{ $account['industry'] ?? '' }}" class="w-full px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                    </div>
                    <div class="space-y-1">
                        <label class="font-semibold text-zinc-700 dark:text-zinc-300">Corporate Website</label>
                        <input type="text" id="detail-field-website" value="{{ $account['website'] ?? '' }}" class="w-full px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                    </div>
                    <div class="space-y-1">
                        <label class="font-semibold text-zinc-700 dark:text-zinc-300">Corporate Phone</label>
                        <input type="text" id="detail-field-phone" value="{{ $account['phone'] ?? '' }}" class="w-full px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                    </div>
                    <div class="space-y-1">
                        <label class="font-semibold text-zinc-700 dark:text-zinc-300">Credit Limit ($)</label>
                        <input type="number" step="any" id="detail-field-credit_limit" value="{{ $account['credit_limit'] ?? 0 }}" class="w-full px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                    </div>
                    <div class="space-y-1">
                        <label class="font-semibold text-zinc-700 dark:text-zinc-300">Annual Revenue ($)</label>
                        <input type="number" step="any" id="detail-field-annual_revenue" value="{{ $account['annual_revenue'] ?? 0 }}" class="w-full px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                    </div>
                    <div class="space-y-1">
                        <label class="font-semibold text-zinc-700 dark:text-zinc-300">Payment Terms</label>
                        <select id="detail-field-payment_terms" class="w-full px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                            @foreach($paymentTerms ?? ['Net 15', 'Net 30', 'Net 45', 'Net 60', 'Advance Wire'] as $term)
                                <option value="{{ $term }}" {{ ($account['payment_terms'] ?? '') === $term ? 'selected' : '' }}>{{ $term }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="font-semibold text-zinc-700 dark:text-zinc-300">Account Owner</label>
                        <select id="detail-field-assigned_to" class="w-full px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                            <option value="">Select owner...</option>
                            @foreach($users ?? [] as $u)
                                <option value="{{ $u->name }}" {{ ($account['assigned_to'] ?? '') === $u->name ? 'selected' : '' }}>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-2 space-y-1">
                        <label class="font-semibold text-zinc-700 dark:text-zinc-300">Billing Address</label>
                        <textarea id="detail-field-billing_address" rows="2" class="w-full px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">{{ $account['billing_address'] ?? '' }}</textarea>
                    </div>
                    <div class="sm:col-span-2 space-y-1">
                        <label class="font-semibold text-zinc-700 dark:text-zinc-300">Shipping Address</label>
                        <textarea id="detail-field-shipping_address" rows="2" class="w-full px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">{{ $account['shipping_address'] ?? '' }}</textarea>
                    </div>
                    <div class="sm:col-span-2 space-y-1">
                        <label class="font-semibold text-zinc-700 dark:text-zinc-300">Account Notes</label>
                        <textarea id="detail-field-notes" rows="2" class="w-full px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">{{ $account['notes'] ?? '' }}</textarea>
                    </div>
                </div>

                <!-- Dynamic Custom Fields in Edit Modal -->
                @if(!empty($customFields))
                    <div class="pt-3 border-t border-zinc-200 dark:border-zinc-800">
                        <div class="font-bold text-xs text-zinc-900 dark:text-zinc-100 mb-2">Dynamic Custom Fields</div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                            @foreach($customFields as $cf)
                                @php
                                    $fName = $cf->field_name;
                                    $perm = $fieldPermissions[$fName] ?? 'Read/Write';
                                    if ($perm === 'Hidden') continue;
                                    $val = $account[$fName] ?? ($account['custom_data'][$fName] ?? '');
                                    $isRo = ($perm === 'Read-Only');
                                @endphp
                                <div class="space-y-1">
                                    <label class="font-semibold text-zinc-700 dark:text-zinc-300 flex items-center justify-between">
                                        <span>{{ $cf->field_label }}</span>
                                        @if($isRo)<span class="text-[9px] text-amber-600 font-bold">🔒 Read-Only</span>@endif
                                    </label>
                                    <input
                                        type="text"
                                        data-cf-name="{{ $fName }}"
                                        value="{{ $val }}"
                                        {{ $isRo ? 'disabled' : '' }}
                                        class="detail-custom-field-input w-full px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 {{ $isRo ? 'opacity-60 bg-zinc-100 cursor-not-allowed' : '' }}"
                                    >
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-zinc-200 dark:border-zinc-800">
                    <button type="button" onclick="window.closeAccountDetailEditModal()" class="px-3.5 py-1.5 text-xs font-semibold text-zinc-600 hover:bg-zinc-100 rounded-lg cursor-pointer">Cancel</button>
                    <button type="submit" class="px-4 py-1.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm cursor-pointer">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- TRANSFER MODAL FOR SHOW PAGE                -->
    <!-- ========================================== -->
    <div id="account-detail-transfer-modal" class="fixed inset-0 z-50 hidden bg-zinc-950/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white dark:bg-zinc-900 rounded-2xl max-w-md w-full border border-zinc-200 dark:border-zinc-800 shadow-2xl overflow-hidden animate-scale-up">
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between">
                <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Transfer Ownership</h3>
                <button type="button" onclick="window.closeAccountDetailTransferModal()" class="text-zinc-400 hover:text-zinc-600 cursor-pointer">✕</button>
            </div>
            <form onsubmit="window.submitAccountDetailTransfer(event)" class="p-6 space-y-4">
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Select New Account Owner</label>
                    <select id="detail-transfer-user-select" required class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                        <option value="">Choose team member...</option>
                        @foreach($users ?? [] as $u)
                            <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center justify-end gap-3 pt-3 border-t border-zinc-200 dark:border-zinc-800">
                    <button type="button" onclick="window.closeAccountDetailTransferModal()" class="px-3.5 py-1.5 text-xs font-semibold text-zinc-600 hover:bg-zinc-100 rounded-lg cursor-pointer">Cancel</button>
                    <button type="submit" class="px-4 py-1.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm cursor-pointer">Transfer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Safe JSON Hydration -->
@php
    $accountDetailData = [
        'account' => $account ?? [],
        'customFields' => $customFields ?? [],
        'fieldPermissions' => !empty($fieldPermissions) ? $fieldPermissions : (object)[],
        'users' => $users ?? [],
    ];
@endphp
<script>
    window.__ACCOUNT_DETAIL_DATA__ = {!! json_encode($accountDetailData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};
</script>
<script src="{{ asset('js/account_detail.js') }}?v=1.0"></script>
@endsection
