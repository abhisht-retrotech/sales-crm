@extends('layouts.app')

@section('title', 'Enterprise Sharing Rules & Record Security Studio — DealSync Admin')

@section('content')
<div id="srStudioRoot" class="max-w-7xl mx-auto pb-16 space-y-6 animate-fade-in">

    <!-- Header Banner (1-to-1 with React SharingRulesStudio.tsx) -->
    <div class="bg-gradient-to-br from-slate-50 via-blue-50/60 to-indigo-50/40 dark:from-zinc-900 dark:via-zinc-900 dark:to-indigo-950/30 border border-blue-100/80 dark:border-indigo-900/40 rounded-2xl p-6 flex flex-col md:flex-row md:items-center justify-between gap-5 shadow-xs transition-colors">
        <div class="flex items-start gap-4 max-w-3xl">
            <div class="w-11 h-11 rounded-xl bg-indigo-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-indigo-600/30">
                <i data-lucide="shield-check" class="w-6 h-6"></i>
            </div>
            <div>
                <div class="flex items-center gap-2.5 flex-wrap">
                    <h2 class="text-lg lg:text-xl font-extrabold text-zinc-950 dark:text-zinc-100 tracking-tight">
                        Enterprise Sharing Rules &amp; Record Security Studio
                    </h2>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-purple-100 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 border border-purple-200/80 dark:border-purple-800">
                        Phase 1 &amp; 3 Engine Active
                    </span>
                </div>
                <p class="text-xs text-zinc-600 dark:text-zinc-400 mt-1.5 leading-relaxed">
                    Conditionally grant record visibility to cross-functional profiles, roles, or users when records meet specific business criteria (e.g. <em class="font-semibold text-zinc-800 dark:text-zinc-200">Deal Amount &ge; $1M</em>, <em class="font-semibold text-zinc-800 dark:text-zinc-200">Stage = Negotiation</em>, or <em class="font-semibold text-zinc-800 dark:text-zinc-200">Industry = Healthcare</em>).
                </p>
            </div>
        </div>

        <button
            type="button"
            onclick="window.openCreateSharingRuleModal()"
            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-xs font-semibold bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm shadow-indigo-600/30 transition-all shrink-0 cursor-pointer"
        >
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>New Sharing Rule</span>
        </button>
    </div>

    <!-- Module Filter Tabs & Search Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-zinc-200 dark:border-zinc-800">
        <!-- Module Tabs -->
        <div id="srModuleTabs" class="flex items-center gap-1.5 overflow-x-auto pb-1 max-w-full">
            <!-- Rendered by JS -->
        </div>

        <!-- Search Input -->
        <div class="relative w-full sm:w-64 shrink-0">
            <i data-lucide="search" class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400"></i>
            <input
                type="text"
                placeholder="Search rules..."
                oninput="window.handleSrSearchInput(this.value)"
                class="w-full h-8.5 pl-8.5 pr-3 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-2xs"
            />
        </div>
    </div>

    <!-- Rules Directory Container -->
    <div id="srRulesContainer" class="space-y-3.5">
        <!-- Server-side Rendered Fallback / Instant Loading Placeholder -->
        <div class="text-center py-12 text-zinc-400 dark:text-zinc-500">
            <i data-lucide="refresh-cw" class="w-6 h-6 animate-spin mx-auto mb-2 text-indigo-600"></i>
            <div class="text-xs font-medium">Loading sharing rules &amp; security schema...</div>
        </div>
    </div>
</div>

<!-- Initial Data Hydration for Zero-Flicker SSR & Fast JS Initialization -->
<script>
    window.INITIAL_SHARING_RULES_DATA = {
        rules: @json($rules ?? []),
        meta: @json($meta ?? null),
        csrfToken: '{{ csrf_token() }}'
    };
</script>
<script src="{{ asset('js/sharing-rules.js') }}?v=2.0"></script>
@endsection
