@extends('layouts.app')

@section('title', ($shipment['tracking_number'] ?? 'Freight Shipment') . ' — Shipping & Logistics 360° — DealSync CRM')

@section('content')
<!-- Centered Max-Width Container matching React page-wrapper (max-width: 1240px, padding: 2.25rem 2.5rem) -->
<div id="shipment-show-page-root" class="max-w-[1240px] mx-auto w-full px-6 sm:px-10 py-7 space-y-6 animate-fade-in">
    <!-- Breadcrumb & Back Action -->
    <div class="flex items-center justify-between gap-4 flex-wrap">
        <div class="flex items-center gap-2 text-xs">
            <a href="/shipping" class="spa-nav-link inline-flex items-center gap-1.5 font-semibold text-zinc-500 hover:text-indigo-600 dark:text-zinc-400 dark:hover:text-indigo-400 transition-colors">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Shipping &amp; Logistics Freight</span>
            </a>
            <span class="text-zinc-300 dark:text-zinc-700">/</span>
            <span class="text-zinc-600 dark:text-zinc-400 font-medium">{{ $shipment['carrier'] ?? 'Carrier' }}</span>
            <span class="text-zinc-300 dark:text-zinc-700">/</span>
            <span class="font-mono font-bold text-zinc-950 dark:text-zinc-100">{{ $shipment['tracking_number'] }}</span>
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
            <!-- Left: Tracking Number, Carrier, Type, Status Badges -->
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-amber-500 to-indigo-600 text-white font-extrabold text-xl flex items-center justify-center shrink-0 shadow-md">
                    <i data-lucide="truck" class="w-7 h-7"></i>
                </div>
                <div class="space-y-1.5">
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <span class="font-mono font-extrabold text-xs px-2.5 py-1 rounded bg-zinc-100 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 border border-zinc-200 dark:border-zinc-700">
                            {{ $shipment['tracking_number'] }}
                        </span>
                        <h1 class="text-2xl font-extrabold text-zinc-950 dark:text-white tracking-tight">
                            {{ $shipment['carrier'] }}
                        </h1>

                        <!-- Direction Type Badge -->
                        @php
                            $shipType = $shipment['type'] ?? 'Outbound';
                            $typeBadges = [
                                'Outbound' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800',
                                'Inbound' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800',
                                'Drop-ship' => 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/40 dark:text-purple-300 dark:border-purple-800',
                            ];
                            $typeClass = $typeBadges[$shipType] ?? 'bg-zinc-100 text-zinc-700 border-zinc-200';
                        @endphp
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold border {{ $typeClass }}">
                            {{ $shipType }}
                        </span>

                        <!-- Logistics Status Badge -->
                        @php
                            $status = $shipment['status'] ?? 'Pending';
                            $statusBadges = [
                                'Pending' => 'bg-zinc-100 text-zinc-700 border-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:border-zinc-700',
                                'In Transit' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800',
                                'Customs Clearance' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800',
                                'Out for Delivery' => 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/40 dark:text-purple-300 dark:border-purple-800',
                                'Delivered' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800',
                            ];
                            $statusClass = $statusBadges[$status] ?? 'bg-zinc-100 text-zinc-700 border-zinc-200';
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $statusClass }}">
                            ● {{ $status }}
                        </span>
                    </div>

                    <div class="text-xs text-zinc-600 dark:text-zinc-400 flex items-center gap-3 flex-wrap">
                        @if(!empty($shipment['account_name']) && $shipment['account_name'] !== 'Unlinked Account')
                            <a href="/accounts/{{ $shipment['account_id'] ?? '' }}" class="font-semibold text-zinc-900 dark:text-zinc-200 hover:text-indigo-600 no-underline hover:no-underline flex items-center gap-1">
                                <i data-lucide="building-2" class="w-3.5 h-3.5 text-zinc-400"></i>
                                <span>Account: <strong>{{ $shipment['account_name'] }}</strong></span>
                            </a>
                            <span>•</span>
                        @endif

                        @if(!empty($shipment['opportunity_name']))
                            <a href="/opportunities/{{ $shipment['opportunity_id'] ?? '' }}" class="hover:text-indigo-600 no-underline hover:no-underline flex items-center gap-1">
                                <i data-lucide="briefcase" class="w-3.5 h-3.5 text-zinc-400"></i>
                                <span>Sales Order: <strong>{{ $shipment['opportunity_name'] }}</strong></span>
                            </a>
                            <span>•</span>
                        @endif

                        @if(!empty($shipment['po_number']))
                            <a href="/purchase-deals/{{ $shipment['purchase_deal_id'] ?? '' }}" class="hover:text-indigo-600 no-underline hover:no-underline flex items-center gap-1">
                                <i data-lucide="shopping-bag" class="w-3.5 h-3.5 text-zinc-400"></i>
                                <span>Procurement PO: <strong>{{ $shipment['po_number'] }}</strong></span>
                            </a>
                            <span>•</span>
                        @endif

                        <div class="flex items-center gap-1.5 text-zinc-700 dark:text-zinc-300">
                            <span class="text-zinc-400 font-medium">Assigned Dispatcher:</span>
                            <span class="w-4 h-4 rounded-full bg-amber-100 text-amber-800 font-bold text-[9px] inline-flex items-center justify-center">
                                {{ strtoupper(substr($shipment['owner_name'] ?? 'D', 0, 1)) }}
                            </span>
                            <span class="font-semibold">{{ $shipment['owner_name'] ?? 'Unassigned' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Delivery ETA & Quick Action Buttons -->
            <div class="flex flex-col items-start md:items-end gap-3 shrink-0">
                <div class="text-left md:text-right">
                    <div class="text-[11px] font-semibold text-zinc-500 uppercase tracking-wider">ESTIMATED DELIVERY</div>
                    <div class="text-xl font-black text-zinc-950 dark:text-white tracking-tight">
                        {{ $shipment['estimated_delivery_date_formatted'] ?? 'Pending Schedule' }}
                    </div>
                    <span class="text-[11px] text-zinc-400">
                        Dispatched: {{ $shipment['dispatch_date_formatted'] ?? 'Not Dispatched' }}
                    </span>
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    @if($shipment['status'] !== 'In Transit' && $shipment['status'] !== 'Delivered')
                        <button
                            type="button"
                            onclick="window.advanceCurrentShipmentStatus('In Transit')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-950/40 text-xs font-semibold text-blue-700 dark:text-blue-300 hover:bg-blue-100 dark:hover:bg-blue-900/60 transition-colors shadow-2xs cursor-pointer"
                        >
                            <i data-lucide="navigation" class="w-3.5 h-3.5"></i>
                            <span>Mark In Transit</span>
                        </button>
                    @endif

                    @if($shipment['status'] !== 'Delivered')
                        <button
                            type="button"
                            onclick="window.advanceCurrentShipmentStatus('Delivered')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-2xs transition-colors cursor-pointer"
                        >
                            <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                            <span>Mark Delivered</span>
                        </button>
                    @endif

                    <button
                        type="button"
                        onclick="window.openEditCurrentShipmentModal()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs cursor-pointer"
                    >
                        <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                        <span>Edit</span>
                    </button>

                    <button
                        type="button"
                        onclick="window.deleteCurrentShipment()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-red-200 dark:border-red-900 bg-red-50 dark:bg-red-950/40 text-xs font-semibold text-red-600 dark:text-red-300 hover:bg-red-100 dark:hover:bg-red-900/60 transition-colors cursor-pointer"
                    >
                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                        <span>Delete</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Logistics Stage Progress Stepper (5-Steps) -->
        <div class="mt-6 pt-5 border-t border-zinc-200 dark:border-zinc-800">
            <div class="text-xs font-bold text-zinc-500 uppercase tracking-wider mb-2.5">
                Logistics Route Progression
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2">
                @php
                    $allStages = ['Pending', 'In Transit', 'Customs Clearance', 'Out for Delivery', 'Delivered'];
                    $curStageIdx = array_search($shipment['status'] ?? 'Pending', $allStages);
                    if ($curStageIdx === false) $curStageIdx = 0;
                @endphp

                @foreach($allStages as $idx => $stName)
                    @php
                        $isCurrent = ($idx === $curStageIdx);
                        $isPast = ($idx < $curStageIdx);
                    @endphp
                    <div
                        class="rounded-xl p-2.5 border transition-all text-center cursor-pointer {{ $isCurrent ? 'bg-indigo-50 border-indigo-300 dark:bg-indigo-950/40 dark:border-indigo-700 text-indigo-900 dark:text-indigo-200 shadow-xs' : ($isPast ? 'bg-zinc-50 border-zinc-200 dark:bg-zinc-800/40 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300' : 'bg-transparent border-dashed border-zinc-200 dark:border-zinc-800 text-zinc-400') }}"
                        onclick="window.advanceCurrentShipmentStatus('{{ $stName }}')"
                        title="Click to advance status to {{ $stName }}"
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
                            {{ $isCurrent ? 'Active Leg' : ($isPast ? 'Completed' : 'Upcoming') }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Segmented Navigation Tabs -->
    <div class="flex items-center gap-1 bg-zinc-100 dark:bg-zinc-800/70 p-1 rounded-xl w-fit border border-zinc-200 dark:border-zinc-700 flex-wrap">
        <button
            type="button"
            id="tab-btn-overview"
            onclick="window.switchShipmentTab('overview')"
            class="tab-btn active inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all cursor-pointer bg-white dark:bg-zinc-700 text-zinc-950 dark:text-white shadow-2xs"
        >
            <i data-lucide="layout" class="w-3.5 h-3.5"></i>
            <span>360° Manifest Overview</span>
        </button>
        <button
            type="button"
            id="tab-btn-logistics"
            onclick="window.switchShipmentTab('logistics')"
            class="tab-btn inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-medium transition-all cursor-pointer text-zinc-600 dark:text-zinc-400 hover:text-zinc-950 dark:hover:text-white"
        >
            <i data-lucide="map-pin" class="w-3.5 h-3.5"></i>
            <span>Route &amp; Waybill Tracking</span>
        </button>
        <button
            type="button"
            id="tab-btn-customfields"
            onclick="window.switchShipmentTab('customfields')"
            class="tab-btn inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-medium transition-all cursor-pointer text-zinc-600 dark:text-zinc-400 hover:text-zinc-950 dark:hover:text-white"
        >
            <i data-lucide="sliders" class="w-3.5 h-3.5"></i>
            <span>Logistics &amp; Custom Fields</span>
        </button>
        <button
            type="button"
            id="tab-btn-linked"
            onclick="window.switchShipmentTab('linked')"
            class="tab-btn inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-medium transition-all cursor-pointer text-zinc-600 dark:text-zinc-400 hover:text-zinc-950 dark:hover:text-white"
        >
            <i data-lucide="link-2" class="w-3.5 h-3.5"></i>
            <span>Linked Orders &amp; Deals</span>
        </button>
    </div>

    <!-- TAB PANE 1: 360° MANIFEST OVERVIEW -->
    <div id="tab-pane-overview" class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left 2 Cols: Details & Timeline -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Manifest Specifications Card -->
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-5 border border-zinc-200 dark:border-zinc-800 shadow-xs space-y-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-500">
                        Freight Shipment Specifications
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div>
                            <span class="text-zinc-400 block mb-0.5">Carrier Service</span>
                            <span class="font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-1.5">
                                <i data-lucide="truck" class="w-4 h-4 text-indigo-600"></i>
                                <span>{{ $shipment['carrier'] }}</span>
                            </span>
                        </div>
                        <div>
                            <span class="text-zinc-400 block mb-0.5">Tracking / Airway Bill Number</span>
                            <span class="font-mono font-bold text-zinc-900 dark:text-zinc-100 text-sm">
                                {{ $shipment['tracking_number'] }}
                            </span>
                        </div>
                        <div>
                            <span class="text-zinc-400 block mb-0.5">Logistics Direction</span>
                            <span class="font-semibold text-zinc-900 dark:text-zinc-100">
                                {{ $shipment['type'] }}
                            </span>
                        </div>
                        <div>
                            <span class="text-zinc-400 block mb-0.5">Dispatch / Departure Date</span>
                            <span class="font-medium text-zinc-800 dark:text-zinc-200">
                                {{ $shipment['dispatch_date_formatted'] ?? 'Not Dispatched' }}
                            </span>
                        </div>
                        <div>
                            <span class="text-zinc-400 block mb-0.5">Estimated Delivery Date</span>
                            <span class="font-medium text-zinc-800 dark:text-zinc-200">
                                {{ $shipment['estimated_delivery_date_formatted'] ?? 'Pending Schedule' }}
                            </span>
                        </div>
                        <div>
                            <span class="text-zinc-400 block mb-0.5">Actual Delivery Date</span>
                            <span class="font-medium {{ !empty($shipment['actual_delivery_date_formatted']) ? 'text-emerald-600 font-bold' : 'text-zinc-400' }}">
                                {{ $shipment['actual_delivery_date_formatted'] ?? 'Not Delivered Yet' }}
                            </span>
                        </div>
                        <div>
                            <span class="text-zinc-400 block mb-0.5">Assigned Dispatcher</span>
                            <span class="font-medium text-zinc-800 dark:text-zinc-200">
                                {{ $shipment['owner_name'] ?? 'Unassigned' }}
                            </span>
                        </div>
                        <div>
                            <span class="text-zinc-400 block mb-0.5">Last Updated</span>
                            <span class="font-medium text-zinc-500">
                                {{ date('M d, Y H:i', strtotime($shipment['updated_at'] ?? 'now')) }}
                            </span>
                        </div>
                    </div>

                    @if(!empty($shipment['notes']))
                        <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800">
                            <span class="text-zinc-400 block text-xs mb-1">Logistics Notes &amp; Special Handling</span>
                            <div class="p-3 rounded-xl bg-zinc-50 dark:bg-zinc-800/40 text-xs text-zinc-700 dark:text-zinc-300 leading-relaxed whitespace-pre-line">
                                {{ $shipment['notes'] }}
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Origin & Destination Routing Card -->
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-5 border border-zinc-200 dark:border-zinc-800 shadow-xs space-y-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-500">
                        Transit Corridor Routing
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                        <div class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200/80 dark:border-zinc-700/60 space-y-1.5">
                            <div class="flex items-center gap-1.5 text-zinc-500 font-semibold text-[11px] uppercase tracking-wider">
                                <i data-lucide="map-pin" class="w-3.5 h-3.5 text-blue-500"></i>
                                <span>Shipping Origin</span>
                            </div>
                            <div class="text-sm font-bold text-zinc-900 dark:text-zinc-100">
                                {{ $shipment['shipping_origin'] ?? 'Central Distribution Hub' }}
                            </div>
                            <span class="text-[11px] text-zinc-400 block">Warehouse / Factory Dispatch Point</span>
                        </div>

                        <div class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200/80 dark:border-zinc-700/60 space-y-1.5">
                            <div class="flex items-center gap-1.5 text-zinc-500 font-semibold text-[11px] uppercase tracking-wider">
                                <i data-lucide="navigation" class="w-3.5 h-3.5 text-emerald-500"></i>
                                <span>Destination Address</span>
                            </div>
                            <div class="text-sm font-bold text-zinc-900 dark:text-zinc-100">
                                {{ $shipment['destination_address'] ?? 'Customer Receiving Dock' }}
                            </div>
                            <span class="text-[11px] text-zinc-400 block">Final Delivery &amp; Unloading Location</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Col: Carrier Portal & Status Summary -->
            <div class="space-y-6">
                <!-- Carrier Info Card -->
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-5 border border-zinc-200 dark:border-zinc-800 shadow-xs space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 flex items-center justify-center text-indigo-600 shrink-0">
                            <i data-lucide="globe" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-zinc-900 dark:text-zinc-100">Carrier Verification</h4>
                            <span class="text-[11px] text-zinc-500">{{ $shipment['carrier'] }}</span>
                        </div>
                    </div>
                    <div class="p-3 rounded-xl bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200/70 dark:border-zinc-700/50 text-xs space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-zinc-500">Service Class:</span>
                            <strong class="text-zinc-900 dark:text-zinc-100 font-semibold">Priority Air / Ground</strong>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-zinc-500">Tracking Code:</span>
                            <span class="font-mono font-bold text-zinc-800 dark:text-zinc-200">{{ $shipment['tracking_number'] }}</span>
                        </div>
                    </div>
                    <button
                        type="button"
                        onclick="window.openCarrierPortal('{{ $shipment['carrier'] }}', '{{ $shipment['tracking_number'] }}')"
                        class="w-full inline-flex items-center justify-center gap-1.5 py-2 px-3 rounded-xl bg-zinc-900 hover:bg-zinc-800 dark:bg-zinc-100 dark:hover:bg-zinc-200 text-white dark:text-zinc-900 text-xs font-bold transition-all shadow-xs cursor-pointer"
                    >
                        <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                        <span>Open Carrier Tracking Portal</span>
                    </button>
                </div>

                <!-- Linked Account Card -->
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-5 border border-zinc-200 dark:border-zinc-800 shadow-xs space-y-3">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-zinc-500">Linked Account</h4>
                    @if(!empty($shipment['account_name']) && $shipment['account_name'] !== 'Unlinked Account')
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 font-extrabold flex items-center justify-center shrink-0">
                                <i data-lucide="building-2" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <a href="/accounts/{{ $shipment['account_id'] ?? '' }}" class="text-sm font-bold text-zinc-950 dark:text-zinc-100 hover:text-indigo-600 no-underline hover:no-underline">
                                    {{ $shipment['account_name'] }}
                                </a>
                                <span class="text-[11px] text-zinc-400 block">Organization Profile</span>
                            </div>
                        </div>
                    @else
                        <p class="text-xs text-zinc-400">No account linked to this freight record.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- TAB PANE 2: ROUTE & WAYBILL TRACKING -->
    <div id="tab-pane-logistics" class="hidden space-y-6">
        <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 border border-zinc-200 dark:border-zinc-800 shadow-xs space-y-6">
            <div>
                <h2 class="text-base font-bold text-zinc-950 dark:text-white">
                    Waybill &amp; Route Logistics Corridor
                </h2>
                <p class="text-xs text-zinc-500">
                    Comprehensive logistics corridor, route transit points, and port customs status.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-4 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-800/30 space-y-1">
                    <span class="text-zinc-400 text-[11px] font-semibold uppercase">1. Origin Facility</span>
                    <div class="text-sm font-bold text-zinc-900 dark:text-zinc-100">{{ $shipment['shipping_origin'] ?? 'Origin Warehouse' }}</div>
                    <span class="text-xs text-zinc-500">Dispatch: {{ $shipment['dispatch_date_formatted'] ?? 'Pending' }}</span>
                </div>

                <div class="p-4 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-800/30 space-y-1">
                    <span class="text-zinc-400 text-[11px] font-semibold uppercase">2. Current Transit State</span>
                    <div class="text-sm font-bold text-indigo-600 dark:text-indigo-400">{{ $shipment['status'] }}</div>
                    <span class="text-xs text-zinc-500">Carrier: {{ $shipment['carrier'] }}</span>
                </div>

                <div class="p-4 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-800/30 space-y-1">
                    <span class="text-zinc-400 text-[11px] font-semibold uppercase">3. Final Receiving Site</span>
                    <div class="text-sm font-bold text-zinc-900 dark:text-zinc-100">{{ $shipment['destination_address'] ?? 'Customer Site' }}</div>
                    <span class="text-xs text-zinc-500">Est Delivery: {{ $shipment['estimated_delivery_date_formatted'] ?? 'Pending' }}</span>
                </div>
            </div>

            <!-- Notes & Handling Instructions -->
            <div class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200 dark:border-zinc-700 space-y-2">
                <h4 class="text-xs font-bold uppercase tracking-wider text-zinc-500">Waybill Special Instructions</h4>
                <p class="text-xs text-zinc-700 dark:text-zinc-300 leading-relaxed whitespace-pre-line">
                    {{ $shipment['notes'] ?: 'No special handling instructions recorded for this consignment.' }}
                </p>
            </div>
        </div>
    </div>

    <!-- TAB PANE 3: LOGISTICS & DYNAMIC CUSTOM FIELDS -->
    <div id="tab-pane-customfields" class="hidden space-y-6">
        <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 border border-zinc-200 dark:border-zinc-800 shadow-xs space-y-5">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h2 class="text-base font-bold text-zinc-950 dark:text-white">
                        Freight Logistics &amp; Dynamic Custom Fields
                    </h2>
                    <p class="text-xs text-zinc-500">
                        Configured dynamic custom fields synced with tenant field-level permissions for this consignment.
                    </p>
                </div>
                <button
                    type="button"
                    onclick="window.openEditCurrentShipmentModal()"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs cursor-pointer"
                >
                    <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                    <span>Edit Custom Fields</span>
                </button>
            </div>

            <!-- Custom Fields Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse($customFields as $cf)
                    @php
                        $key = $cf->field_name;
                        $perm = $fieldPermissions[$key] ?? 'Read/Write';
                        if ($perm === 'Hidden') continue;
                        $val = $shipment[$key] ?? ($shipment['custom_data'][$key] ?? null);
                    @endphp
                    <div class="p-4 rounded-xl bg-zinc-50/70 dark:bg-zinc-800/40 border border-zinc-200 dark:border-zinc-700 space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200">{{ $cf->field_label }}</span>
                            <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded {{ $perm === 'Read-Only' ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' : 'bg-zinc-200 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-300' }}">
                                {{ $perm }}
                            </span>
                        </div>
                        <div class="font-mono text-xs text-zinc-400">{{ $key }}</div>
                        <div class="text-sm font-semibold text-zinc-950 dark:text-zinc-100 pt-1">
                            @if(is_bool($val))
                                <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded font-bold {{ $val ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-zinc-200 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-400' }}">
                                    {{ $val ? 'Yes / True' : 'No / False' }}
                                </span>
                            @elseif($val !== null && $val !== '')
                                {{ is_array($val) ? json_encode($val) : $val }}
                            @else
                                <span class="text-zinc-400 italic text-xs">Not specified</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-8 text-center text-xs text-zinc-400">
                        No dynamic custom fields configured for Shipping &amp; Logistics.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- TAB PANE 4: LINKED ORDERS & DEALS -->
    <div id="tab-pane-linked" class="hidden space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Linked Sales Opportunity Card -->
            <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 border border-zinc-200 dark:border-zinc-800 shadow-xs space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 flex items-center justify-center text-indigo-600 shrink-0">
                        <i data-lucide="briefcase" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-zinc-950 dark:text-white">Linked Sales Order (Opportunity)</h3>
                        <span class="text-xs text-zinc-500">Customer revenue contract for this shipment</span>
                    </div>
                </div>

                @if(!empty($shipment['opportunity_name']))
                    <div class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200 dark:border-zinc-700 space-y-2">
                        <div class="flex items-center justify-between">
                            <a href="/opportunities/{{ $shipment['opportunity_id'] ?? '' }}" class="text-sm font-bold text-zinc-950 dark:text-zinc-100 hover:text-indigo-600 no-underline hover:no-underline">
                                {{ $shipment['opportunity_name'] }}
                            </a>
                            <span class="font-mono text-xs font-bold text-emerald-600">Sales Deal</span>
                        </div>
                    </div>
                @else
                    <div class="py-8 text-center text-xs text-zinc-400 border border-dashed border-zinc-200 dark:border-zinc-800 rounded-xl">
                        No sales opportunity currently associated with this consignment.
                    </div>
                @endif
            </div>

            <!-- Linked Purchase Order Card -->
            <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 border border-zinc-200 dark:border-zinc-800 shadow-xs space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/40 flex items-center justify-center text-amber-700 dark:text-amber-300 shrink-0">
                        <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-zinc-950 dark:text-white">Linked Procurement PO (Purchase Deal)</h3>
                        <span class="text-xs text-zinc-500">Supplier order consignment backing this delivery</span>
                    </div>
                </div>

                @if(!empty($shipment['po_number']))
                    <div class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200 dark:border-zinc-700 space-y-2">
                        <div class="flex items-center justify-between">
                            <a href="/purchase-deals/{{ $shipment['purchase_deal_id'] ?? '' }}" class="text-sm font-bold text-zinc-950 dark:text-zinc-100 hover:text-indigo-600 no-underline hover:no-underline">
                                PO: {{ $shipment['po_number'] }}
                            </a>
                            <span class="font-mono text-xs font-bold text-indigo-600">Procurement</span>
                        </div>
                        @if(!empty($shipment['purchase_deal_title']))
                            <div class="text-xs text-zinc-500">{{ $shipment['purchase_deal_title'] }}</div>
                        @endif
                    </div>
                @else
                    <div class="py-8 text-center text-xs text-zinc-400 border border-dashed border-zinc-200 dark:border-zinc-800 rounded-xl">
                        No purchase procurement order linked to this freight record.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Edit Shipment Modal -->
<div id="edit-shipment-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50 backdrop-blur-xs p-4">
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xl max-w-2xl w-full max-h-[90vh] flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-150">
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between shrink-0">
            <div>
                <h3 class="text-base font-bold text-zinc-950 dark:text-white">Edit Freight Consignment</h3>
                <p class="text-xs text-zinc-500">Update carrier status, waybill routing, and logistics custom fields.</p>
            </div>
            <button type="button" onclick="window.closeEditCurrentShipmentModal()" class="p-1 rounded-lg text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 cursor-pointer">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form id="edit-shipment-form" onsubmit="window.saveCurrentShipment(event)" class="flex-1 overflow-y-auto p-6 space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Carrier *</label>
                    <select id="edit-ship-carrier" required class="w-full text-xs rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-indigo-500">
                        @foreach($carriers as $c)
                            <option value="{{ $c }}" {{ ($shipment['carrier'] ?? '') === $c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Tracking Number *</label>
                    <input type="text" id="edit-ship-tracking" value="{{ $shipment['tracking_number'] ?? '' }}" required class="w-full text-xs rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 font-mono text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Logistics Direction *</label>
                    <select id="edit-ship-type" required class="w-full text-xs rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-indigo-500">
                        <option value="Outbound" {{ ($shipment['type'] ?? '') === 'Outbound' ? 'selected' : '' }}>Outbound</option>
                        <option value="Inbound" {{ ($shipment['type'] ?? '') === 'Inbound' ? 'selected' : '' }}>Inbound</option>
                        <option value="Drop-ship" {{ ($shipment['type'] ?? '') === 'Drop-ship' ? 'selected' : '' }}>Drop-ship</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Consignment Status *</label>
                    <select id="edit-ship-status" required class="w-full text-xs rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-indigo-500">
                        @foreach($stages as $st)
                            <option value="{{ $st }}" {{ ($shipment['status'] ?? '') === $st ? 'selected' : '' }}>{{ $st }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Dispatch Date</label>
                    <input type="date" id="edit-ship-dispatch-date" value="{{ $shipment['dispatch_date'] ?? '' }}" class="w-full text-xs rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Estimated Delivery Date</label>
                    <input type="date" id="edit-ship-delivery-date" value="{{ $shipment['estimated_delivery_date'] ?? '' }}" class="w-full text-xs rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Shipping Origin (Dispatch Location)</label>
                    <input type="text" id="edit-ship-origin" value="{{ $shipment['shipping_origin'] ?? '' }}" class="w-full text-xs rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Destination Address (Delivery Site)</label>
                    <input type="text" id="edit-ship-destination" value="{{ $shipment['destination_address'] ?? '' }}" class="w-full text-xs rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Assigned Dispatcher</label>
                    <select id="edit-ship-assigned" class="w-full text-xs rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-indigo-500">
                        <option value="">-- Unassigned --</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ ($shipment['assigned_to'] ?? '') == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->email }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Logistics Notes &amp; Special Handling</label>
                    <textarea id="edit-ship-notes" rows="3" class="w-full text-xs rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-indigo-500">{{ $shipment['notes'] ?? '' }}</textarea>
                </div>
            </div>

            <!-- Dynamic Custom Fields Form Section -->
            @if(count($customFields) > 0)
                <div class="pt-4 border-t border-zinc-200 dark:border-zinc-800 space-y-3">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-zinc-500">Dynamic Custom Fields</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($customFields as $cf)
                            @php
                                $cfKey = $cf->field_name;
                                $perm = $fieldPermissions[$cfKey] ?? 'Read/Write';
                                if ($perm === 'Hidden') continue;
                                $val = $shipment[$cfKey] ?? ($shipment['custom_data'][$cfKey] ?? '');
                                $isRo = ($perm === 'Read-Only');
                            @endphp
                            <div>
                                <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">
                                    {{ $cf->field_label }}
                                    @if($isRo)
                                        <span class="text-[10px] text-amber-600 font-normal ml-1">(Read-Only)</span>
                                    @endif
                                </label>
                                @if($cf->data_type === 'boolean')
                                    <select id="edit-cf-{{ $cfKey }}" data-cf-key="{{ $cfKey }}" {{ $isRo ? 'disabled' : '' }} class="w-full text-xs rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-indigo-500">
                                        <option value="1" {{ $val ? 'selected' : '' }}>Yes / Enabled</option>
                                        <option value="0" {{ !$val ? 'selected' : '' }}>No / Disabled</option>
                                    </select>
                                @elseif($cf->data_type === 'number')
                                    <input type="number" step="any" id="edit-cf-{{ $cfKey }}" data-cf-key="{{ $cfKey }}" value="{{ $val }}" {{ $isRo ? 'readonly' : '' }} class="w-full text-xs rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-indigo-500 {{ $isRo ? 'opacity-60 bg-zinc-100' : '' }}">
                                @else
                                    <input type="text" id="edit-cf-{{ $cfKey }}" data-cf-key="{{ $cfKey }}" value="{{ $val }}" {{ $isRo ? 'readonly' : '' }} class="w-full text-xs rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-indigo-500 {{ $isRo ? 'opacity-60 bg-zinc-100' : '' }}">
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="pt-4 border-t border-zinc-200 dark:border-zinc-800 flex items-center justify-end gap-2">
                <button type="button" onclick="window.closeEditCurrentShipmentModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer">
                    Cancel
                </button>
                <button type="submit" id="btn-save-current-shipment" class="px-4 py-2 rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white shadow-xs transition-colors cursor-pointer flex items-center gap-1.5">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Save Changes</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Safe Blade JSON Hydration with Zero Bracket/Parentheses Nesting Issues -->
@php
    $shipmentDetailData = [
        'shipment' => $shipment ?? [],
        'customFields' => $customFields ?? [],
        'fieldPermissions' => $fieldPermissions ?? (object)[],
        'users' => $users ?? [],
        'accounts' => $accounts ?? [],
        'opportunities' => $opportunities ?? [],
        'purchaseDeals' => $purchaseDeals ?? [],
        'stages' => $stages ?? ['Pending', 'In Transit', 'Customs Clearance', 'Out for Delivery', 'Delivered'],
        'carriers' => $carriers ?? ['FedEx Freight', 'UPS Express', 'DHL Global', 'Maersk Line', 'Expeditors International'],
        'currentUserProfile' => $currentUserProfile ?? 'Administrator',
    ];
@endphp
<script>
    window.__SHIPMENT_DETAIL_DATA__ = {!! json_encode($shipmentDetailData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};
</script>
<script src="{{ asset('js/shipment_detail.js') }}?v=1.0"></script>
@endsection
