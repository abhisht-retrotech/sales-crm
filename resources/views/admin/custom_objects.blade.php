@extends('layouts.app')

@section('title', 'Custom Objects Studio — DealSync Admin')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 animate-fade-in" id="coStudioRoot">
    @include('partials.admin_nav')

    <!-- Toast Feedback Container -->
    <div id="coFeedbackContainer" class="hidden"></div>

    <!-- Header Banner (Exact React Parity) -->
    <div class="relative overflow-hidden mb-6 p-5 sm:p-6 bg-gradient-to-br from-zinc-900 to-zinc-800 rounded-2xl border border-zinc-700/60 shadow-lg text-white">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="max-w-2xl">
                <div class="flex items-center gap-2.5 mb-2 flex-wrap">
                    <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-indigo-300">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/>
                            <path d="m3.3 7 8.7 5 8.7-5"/>
                            <path d="M12 22V12"/>
                        </svg>
                    </div>
                    <h1 class="text-lg sm:text-xl font-bold tracking-tight text-white">
                        Custom Objects Studio
                    </h1>
                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-indigo-500/20 text-indigo-200 border border-indigo-400/30">
                        Salesforce-Style Dynamic Schema
                    </span>
                </div>
                <p class="text-xs sm:text-sm text-zinc-300 leading-relaxed">
                    Define custom business entities with <code class="px-1.5 py-0.5 rounded bg-white/10 text-indigo-200 font-mono text-xs">__c</code> API names.
                    Custom objects automatically support Related Lists, Salesforce Files architecture, Activity timelines, and extensible attributes.
                </p>
            </div>

            <div class="shrink-0 flex items-center gap-2">
                <button type="button"
                        onclick="window.openCreateObjectModal()"
                        class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 rounded-xl shadow-md transition-all focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <line x1="12" y1="5" x2="12" y2="19"/>
                        <line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    <span>New Custom Object</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Main 2-Column Responsive Workspace -->
    <div class="flex flex-col lg:flex-row items-start gap-6">
        <!-- Left Column: Configured Objects -->
        <div class="w-full lg:w-96 shrink-0 bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 p-4 shadow-xs">
            <div class="flex items-center justify-between pb-3 mb-3 border-b border-zinc-200 dark:border-zinc-800">
                <h3 class="text-xs font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider">
                    Configured Objects <span id="coConfiguredCount" class="text-zinc-500 font-normal">({{ count($customObjects) }})</span>
                </h3>
                <button type="button"
                        onclick="window.refreshObjects()"
                        class="p-1.5 text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-100 bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 rounded-md border border-zinc-300 dark:border-zinc-700 transition-colors"
                        title="Refresh objects">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                        <path d="M3 3v5h5"/>
                        <path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/>
                        <path d="M16 21h5v-5"/>
                    </svg>
                </button>
            </div>

            <!-- Objects List Placeholder -->
            <div id="coObjectsList" class="space-y-2"></div>
        </div>

        <!-- Right Column: Selected Object Record Workspace -->
        <div class="flex-1 min-w-0 w-full" id="coRecordWorkspace">
            <!-- Will be populated dynamically by custom-objects.js -->
        </div>
    </div>

    <!-- Hidden Server-Side Hydration Payload -->
    <script id="coServerData" type="application/json">
        {!! json_encode($customObjects) !!}
    </script>
</div>

<!-- Custom Objects Client Script -->
<script src="{{ asset('js/custom-objects.js') }}?v=2.0"></script>
@endsection
