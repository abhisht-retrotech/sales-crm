@extends('layouts.app')

@section('title', 'Custom Buttons & Actions Studio — DealSync Admin')

@section('content')
<div class="max-w-[1600px] mx-auto px-4 sm:px-5 py-3.5 flex flex-col gap-4 animate-fade-in" id="cbStudioRoot">
    @include('partials.admin_nav')

    <!-- Top Banner & Header (Exact React 1:1 Parity) -->
    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
        <div class="flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-xl bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300 flex items-center justify-center shrink-0 shadow-xs">
                <i data-lucide="mouse-pointer-click" class="w-5 h-5 stroke-[2.2]"></i>
            </div>
            <div>
                <h2 class="text-lg sm:text-xl font-bold text-zinc-950 dark:text-zinc-100 tracking-tight m-0">
                    Custom Buttons &amp; Actions Studio
                </h2>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5 leading-relaxed m-0">
                    Create interactive buttons assigned to email dispatches, web redirects, or automated workflows for record detail headers.
                </p>
            </div>
        </div>

        <button
            type="button"
            id="cbCreateBtn"
            onclick="window.handleOpenCreateButtonModal && window.handleOpenCreateButtonModal()"
            class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white rounded-lg text-xs font-semibold shadow-xs transition-colors shrink-0 cursor-pointer self-start sm:self-auto"
        >
            <i data-lucide="plus" class="w-4 h-4 stroke-[2.2]"></i>
            <span>Create Custom Button</span>
        </button>
    </div>

    <!-- Notification / Feedback Banner -->
    <div
        id="cbFeedbackBanner"
        class="hidden px-4 py-3 rounded-lg text-xs font-medium bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 items-center gap-2 transition-all"
        role="alert"
    >
        <i data-lucide="check-circle-2" class="w-4 h-4 shrink-0 text-emerald-600 dark:text-emerald-400"></i>
        <span id="cbFeedbackText"></span>
    </div>

    <!-- Filter Strip with View Mode Switcher (Exact React 1:1 Parity) -->
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-3 sm:px-4 sm:py-3 flex flex-wrap items-center justify-between gap-3 shadow-xs">
        <div class="flex flex-wrap items-center gap-2.5">
            <!-- Search Input -->
            <div class="relative w-full sm:w-64">
                <i data-lucide="search" class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400"></i>
                <input
                    type="text"
                    id="cbSearchInput"
                    placeholder="Search buttons..."
                    class="w-full pl-8.5 pr-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-colors"
                />
            </div>

            <!-- Module Filter Select -->
            <div class="relative">
                <select
                    id="cbModuleSelect"
                    class="min-w-[190px] rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-1.5 text-xs text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-colors"
                >
                    <option value="all">All Modules</option>
                    <option value="leads">Leads</option>
                    <option value="accounts">Accounts</option>
                    <option value="contacts">Contacts</option>
                    <option value="opportunities">Opportunities</option>
                    <option value="purchase_deals">Purchase Deals</option>
                    <option value="shipping">Shipping &amp; Freight</option>
                    <option value="payments">Invoices &amp; Payments</option>
                    <option value="wires">Wire Reconciliation</option>
                    @if(isset($customObjects) && count($customObjects) > 0)
                        <optgroup label="Custom Objects">
                            @foreach($customObjects as $co)
                                <option value="{{ $co->api_name }}">{{ $co->label_plural ?: $co->label_singular }}</option>
                            @endforeach
                        </optgroup>
                    @endif
                </select>
            </div>
        </div>

        <div class="flex items-center gap-3.5 ml-auto sm:ml-0">
            <!-- Count Badge -->
            <div id="cbCountBadge" class="text-xs font-medium text-zinc-500 dark:text-zinc-400">
                0 buttons configured
            </div>

            <!-- View Mode Switcher (List vs Cards) -->
            <div class="inline-flex items-center bg-zinc-100 dark:bg-zinc-800 p-0.5 rounded-lg border border-zinc-200 dark:border-zinc-700">
                <button
                    type="button"
                    id="cbViewListBtn"
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-md transition-all cursor-pointer bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 shadow-xs"
                    title="List View (Table)"
                >
                    <i data-lucide="list" class="w-3.5 h-3.5 stroke-[2.2]"></i>
                    <span>List View</span>
                </button>
                <button
                    type="button"
                    id="cbViewGridBtn"
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-md transition-all cursor-pointer text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-200"
                    title="Cards / Grid View"
                >
                    <i data-lucide="layout-grid" class="w-3.5 h-3.5 stroke-[2.2]"></i>
                    <span>Cards</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Main Dynamic Content (List View or Cards View) -->
    <div id="cbMainContent" class="space-y-4">
        <!-- Rendered dynamically by custom-buttons.js -->
    </div>

    <!-- Modal Mount Point -->
    <div id="cbModalsRoot"></div>

    <!-- Hidden Server-Side Hydration Payload -->
    <script id="cbServerData" type="application/json">
        {!! json_encode([
            'buttons'           => $buttons,
            'templates'         => $templates,
            'notificationRules' => $notificationRules,
            'customObjects'     => $customObjects,
            'processFlows'      => $processFlows ?? [],
            'selectedEntity'    => $selectedEntity ?? 'all',
        ]) !!}
    </script>
</div>

<!-- Custom Buttons Client Script -->
<script src="{{ asset('js/custom-buttons.js') }}?v=2.0"></script>
@endsection
