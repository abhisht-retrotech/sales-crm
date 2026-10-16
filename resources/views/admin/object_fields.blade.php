@extends('layouts.app')

@section('title', 'Object & Field Manager Studio — DealSync Admin')

@section('content')
<div id="ofStudioRoot" class="max-w-7xl mx-auto pb-16 space-y-6 animate-fade-in text-zinc-900 dark:text-zinc-100">

    <!-- Floating Toast Notification -->
    <div id="ofToast" class="fixed bottom-6 right-6 z-[99999] hidden items-center gap-2.5 px-4 py-3 rounded-xl bg-emerald-600 text-white shadow-xl shadow-emerald-600/20 text-xs font-semibold animate-fade-in">
        <i id="ofToastIcon" data-lucide="check-circle-2" class="w-4 h-4 text-white shrink-0"></i>
        <span id="ofToastMsg"></span>
    </div>

    <!-- Header Action Ribbon -->
    <div class="bg-gradient-to-br from-slate-50 via-blue-50/60 to-indigo-50/40 dark:from-zinc-900 dark:via-zinc-900 dark:to-indigo-950/30 border border-blue-100/80 dark:border-indigo-900/40 rounded-2xl p-6 flex flex-col md:flex-row md:items-center justify-between gap-5 shadow-xs transition-colors">
        <div class="flex items-start gap-4 max-w-3xl">
            <div class="w-12 h-12 rounded-xl bg-indigo-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-indigo-600/30">
                <i data-lucide="database" class="w-6 h-6"></i>
            </div>
            <div>
                <div class="flex items-center gap-2.5 flex-wrap">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-100 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                        Dynamic Schema Architecture
                    </span>
                    <span class="text-xs text-zinc-500 dark:text-zinc-400">
                        Custom Attributes, Rollup Summaries, Formulas &amp; Global Picklists
                    </span>
                </div>
                <h1 class="text-xl font-extrabold text-zinc-950 dark:text-zinc-100 tracking-tight mt-1">
                    Object &amp; Field Manager Studio
                </h1>
                <p class="text-xs text-zinc-600 dark:text-zinc-400 mt-1 leading-relaxed">
                    Extend standard CRM objects with 21 dynamic data types including formulas, rollup aggregations, masked PII, ratings, and multi-select picklists.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2.5 shrink-0">
            <button
                type="button"
                onclick="window.setSubTab('global_picklists')"
                class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-xs font-semibold bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200 border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-700/60 shadow-xs transition-colors"
            >
                <i data-lucide="list-plus" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"></i>
                <span>Global Picklists</span>
            </button>
            <button
                type="button"
                onclick="window.openCreateFieldModal()"
                class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm shadow-indigo-600/30 transition-colors"
            >
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>+ Create Custom Field</span>
            </button>
        </div>
    </div>

    <!-- KPI Summary Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-4 shadow-xs">
            <div class="text-[11px] font-bold text-zinc-400 uppercase tracking-wider">STANDARD OBJECTS</div>
            <div class="text-2xl font-extrabold text-zinc-900 dark:text-zinc-100 tracking-tight mt-0.5">10 Entities</div>
            <span class="text-xs font-medium text-zinc-500">94 Standard Columns</span>
        </div>

        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-4 shadow-xs">
            <div class="text-[11px] font-bold text-zinc-400 uppercase tracking-wider">ACTIVE CUSTOM FIELDS</div>
            <div id="kpiActiveCustomFields" class="text-2xl font-extrabold text-indigo-600 dark:text-indigo-400 tracking-tight mt-0.5">{{ count($customFields) }} Fields</div>
            <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400">Zero Migration Overhead</span>
        </div>

        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-4 shadow-xs">
            <div class="text-[11px] font-bold text-zinc-400 uppercase tracking-wider">FORMULA &amp; ROLLUP ENGINES</div>
            <div id="kpiFormulasRollups" class="text-2xl font-extrabold text-purple-600 dark:text-purple-400 tracking-tight mt-0.5">1 Active</div>
            <span class="text-xs font-medium text-zinc-500">Real-Time Recalculation</span>
        </div>

        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-4 shadow-xs">
            <div class="text-[11px] font-bold text-zinc-400 uppercase tracking-wider">GLOBAL PICKLISTS</div>
            <div id="kpiGlobalPicklists" class="text-2xl font-extrabold text-sky-600 dark:text-sky-400 tracking-tight mt-0.5">{{ count($globalPicklists) }} Sets</div>
            <span class="text-xs font-medium text-zinc-500">Shared Value Sets</span>
        </div>
    </div>

    <!-- Sub-Navigation for Object Schema vs Global Picklist Sets vs Lead Mapping -->
    <div class="flex items-center gap-1.5 border-b border-zinc-200 dark:border-zinc-800 pb-3">
        <button
            type="button"
            id="subTabBtnFields"
            onclick="window.setSubTab('fields')"
            class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-indigo-600 text-white shadow-xs"
        >
            <i data-lucide="database" class="w-3.5 h-3.5"></i>
            <span>Object Fields &amp; Schema</span>
        </button>
        <button
            type="button"
            id="subTabBtnGlobalPicklists"
            onclick="window.setSubTab('global_picklists')"
            class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-semibold text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors"
        >
            <i data-lucide="list-plus" class="w-3.5 h-3.5"></i>
            <span>Global Picklist Value Sets (<span id="subTabGpCount">{{ count($globalPicklists) }}</span>)</span>
        </button>
        <button
            type="button"
            id="subTabBtnLeadMapping"
            onclick="window.setSubTab('lead_mapping')"
            class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-semibold text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors"
        >
            <i data-lucide="git-merge" class="w-3.5 h-3.5"></i>
            <span>Map Lead Fields</span>
        </button>
    </div>

    <!-- ===================================================================== -->
    <!-- VIEW 1: OBJECT FIELDS & SCHEMA                                        -->
    <!-- ===================================================================== -->
    <div id="viewFieldsSchema" class="space-y-6">

        <!-- Object Selector & Actions Bar -->
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-4 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 flex items-center justify-center shrink-0">
                    <i data-lucide="database" class="w-5 h-5"></i>
                </div>
                <div>
                    <div class="text-[11px] font-bold text-zinc-400 uppercase tracking-wider">Target Object / Entity Schema</div>
                    <select
                        id="selectedModuleForFields"
                        class="mt-1 w-72 text-xs font-semibold bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg px-3 py-1.5 text-zinc-900 dark:text-zinc-100 outline-none focus:border-indigo-500 cursor-pointer"
                    >
                        <!-- Populated by JS -->
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a
                    href="{{ route('admin.page_layouts') }}"
                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-700/60 shadow-xs transition-colors"
                    title="Design record page layout for this object"
                >
                    <i data-lucide="layout-template" class="w-3.5 h-3.5"></i>
                    <span>Customize Page Layout</span>
                </a>
                <button
                    type="button"
                    onclick="window.openCreateFieldModal()"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white shadow-xs transition-colors"
                >
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>Create Custom Field</span>
                </button>
            </div>
        </div>

        <!-- Custom Fields Section -->
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <h3 id="customFieldsHeaderTitle" class="text-sm font-bold text-zinc-950 dark:text-zinc-100">
                    Custom Fields on Leads (0)
                </h3>
                <span class="text-xs text-zinc-500">User-defined attributes with dynamic data types</span>
            </div>

            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden shadow-xs">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-zinc-50 dark:bg-zinc-800/60 border-b border-zinc-200 dark:border-zinc-800 text-[11px] font-bold text-zinc-500 uppercase tracking-wider">
                                <th class="py-3 px-4 min-w-[180px]">Field Label</th>
                                <th class="py-3 px-4 min-w-[160px]">Database Key</th>
                                <th class="py-3 px-4 min-w-[140px]">Data Type</th>
                                <th class="py-3 px-4 min-w-[220px]">Options &amp; Source</th>
                                <th class="py-3 px-4 min-w-[100px]">Required</th>
                                <th class="py-3 px-4 min-w-[180px]">Description</th>
                                <th class="py-3 px-4 min-w-[120px] text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="customFieldsTableBody">
                            <!-- Populated dynamically via JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Standard System Fields Section -->
        <div class="space-y-3 pt-4">
            <div class="flex items-center justify-between">
                <h3 id="standardFieldsHeaderTitle" class="text-sm font-bold text-zinc-950 dark:text-zinc-100">
                    Standard System Fields on Leads (Core CRM Schema)
                </h3>
                <span class="text-xs text-zinc-500">Managed core attributes</span>
            </div>

            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden shadow-xs">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-zinc-50 dark:bg-zinc-800/60 border-b border-zinc-200 dark:border-zinc-800 text-[11px] font-bold text-zinc-500 uppercase tracking-wider">
                                <th class="py-3 px-4 min-w-[180px]">Field Label</th>
                                <th class="py-3 px-4 min-w-[160px]">Database Column</th>
                                <th class="py-3 px-4 min-w-[140px]">Data Type</th>
                                <th class="py-3 px-4 min-w-[100px]">Required</th>
                                <th class="py-3 px-4 min-w-[220px]">Description</th>
                                <th class="py-3 px-4 min-w-[100px] text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody id="standardFieldsTableBody">
                            <!-- Populated dynamically via JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <!-- ===================================================================== -->
    <!-- VIEW 2: GLOBAL PICKLIST VALUE SETS                                    -->
    <!-- ===================================================================== -->
    <div id="viewGlobalPicklists" class="space-y-6 hidden">
        <!-- Global Picklists Header Ribbon -->
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-4 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-sm font-bold text-zinc-950 dark:text-zinc-100">Global Picklist Value Sets</h2>
                <p class="text-xs text-zinc-500 mt-0.5">
                    Centrally managed, reusable value sets shared across custom picklist fields on any CRM object.
                </p>
            </div>

            <button
                type="button"
                onclick="window.openCreateGlobalPicklistModal()"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white shadow-xs transition-colors shrink-0"
            >
                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                <span>New Global Value Set</span>
            </button>
        </div>

        <!-- Global Picklists Table -->
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-zinc-50 dark:bg-zinc-800/60 border-b border-zinc-200 dark:border-zinc-800 text-[11px] font-bold text-zinc-500 uppercase tracking-wider">
                            <th class="py-3 px-4 min-w-[180px]">Value Set Name</th>
                            <th class="py-3 px-4 min-w-[160px]">API Identifier</th>
                            <th class="py-3 px-4 min-w-[120px]">Values Count</th>
                            <th class="py-3 px-4 min-w-[260px]">Values Preview</th>
                            <th class="py-3 px-4 min-w-[200px]">Description</th>
                            <th class="py-3 px-4 min-w-[120px] text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="globalPicklistsTableBody">
                        <!-- Populated dynamically via JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- VIEW 3: LEAD CONVERSION FIELD MAPPING STUDIO                          -->
    <!-- ===================================================================== -->
    <div id="viewLeadMapping" class="space-y-6 hidden">
        <!-- Rendered by window.renderLeadMappingStudio() -->
    </div>

</div>

<!-- ========================================================================= -->
<!-- MODAL 1: CREATE / EDIT CUSTOM FIELD                                       -->
<!-- ========================================================================= -->
<div id="customFieldModalBackdrop" class="fixed inset-0 z-[99999] hidden items-center justify-center p-4 bg-zinc-950/60 backdrop-blur-xs overflow-y-auto">
    <div class="relative w-full max-w-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl shadow-2xl overflow-hidden my-8 animate-fade-in text-zinc-900 dark:text-zinc-100">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-800/50 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <i data-lucide="sliders" class="w-5 h-5 text-indigo-600 dark:text-indigo-400"></i>
                <div>
                    <h3 id="customFieldModalTitle" class="text-sm font-bold text-zinc-950 dark:text-zinc-100">Create Custom Field</h3>
                    <div class="flex items-center gap-2 mt-0.5">
                        <span class="text-xs text-zinc-500">Target Entity:</span>
                        <span id="cfModalTargetModuleBadge" class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300">
                            Leads
                        </span>
                    </div>
                </div>
            </div>
            <button
                type="button"
                onclick="window.closeModalDialog('customFieldModalBackdrop')"
                class="p-1 rounded-lg text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors"
            >
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Form Body -->
        <form id="customFieldForm" class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Field Label *</label>
                    <input
                        type="text"
                        id="cfFieldLabel"
                        required
                        placeholder="e.g. Procurement SLA Tier"
                        class="w-full text-xs font-medium bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg px-3 py-2 text-zinc-900 dark:text-zinc-100 outline-none focus:border-indigo-500"
                    />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                        Database Field Key * <span class="text-zinc-400 font-normal">(Prefix cf_)</span>
                    </label>
                    <input
                        type="text"
                        id="cfFieldName"
                        placeholder="cf_procurement_sla_tier"
                        class="w-full text-xs font-mono bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg px-3 py-2 text-zinc-900 dark:text-zinc-100 outline-none focus:border-indigo-500"
                    />
                </div>
            </div>

            <!-- Data Type -->
            <div>
                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Data Type *</label>
                <select
                    id="cfDataType"
                    class="w-full text-xs font-medium bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg px-3 py-2 text-zinc-900 dark:text-zinc-100 outline-none focus:border-indigo-500"
                >
                    @foreach($dataTypes as $dt)
                        <option value="{{ $dt['value'] }}">{{ $dt['label'] }}</option>
                    @endforeach
                </select>
            </div>

            <!-- DYNAMIC PANEL: Picklist Values Config -->
            <div id="cfPanelPicklist" class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200 dark:border-zinc-700/80 space-y-3 hidden">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-zinc-900 dark:text-zinc-100">Picklist Values Source</span>
                    <div class="inline-flex rounded-lg border border-zinc-200 dark:border-zinc-700 p-0.5 bg-white dark:bg-zinc-900">
                        <button
                            type="button"
                            id="cfBtnSourceCustom"
                            onclick="window.setPicklistSource('custom')"
                            class="px-3 py-1 rounded-md text-xs font-bold bg-indigo-600 text-white shadow-xs"
                        >
                            Custom Values
                        </button>
                        <button
                            type="button"
                            id="cfBtnSourceGlobal"
                            onclick="window.setPicklistSource('global')"
                            class="px-3 py-1 rounded-md text-xs font-semibold text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100"
                        >
                            Global Picklist Set
                        </button>
                    </div>
                </div>

                <div id="cfBoxCustomValues">
                    <label class="block text-xs font-semibold text-zinc-600 dark:text-zinc-400 mb-1">
                        Values (Comma or Newline separated)
                    </label>
                    <textarea
                        id="cfDropdownOptions"
                        rows="3"
                        placeholder="Option 1, Option 2, Option 3"
                        class="w-full text-xs font-medium bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg p-2.5 text-zinc-900 dark:text-zinc-100 outline-none focus:border-indigo-500"
                    ></textarea>
                </div>

                <div id="cfBoxGlobalValues" class="hidden">
                    <label class="block text-xs font-semibold text-zinc-600 dark:text-zinc-400 mb-1">
                        Select Global Value Set *
                    </label>
                    <select
                        id="cfGlobalPicklistSelect"
                        class="w-full text-xs font-medium bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg px-3 py-2 text-zinc-900 dark:text-zinc-100 outline-none focus:border-indigo-500"
                    >
                        <!-- Populated by JS -->
                    </select>
                </div>
            </div>

            <!-- DYNAMIC PANEL: Percentage -->
            <div id="cfPanelPercentage" class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200 dark:border-zinc-700/80 space-y-3 hidden">
                <span class="text-xs font-bold text-zinc-900 dark:text-zinc-100">Percentage Precision &amp; Range</span>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-zinc-600 dark:text-zinc-400 mb-1">Precision</label>
                        <select id="cfPercentagePrecision" class="w-full text-xs bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg p-2">
                            <option value="0">0 decimals (e.g. 15%)</option>
                            <option value="1">1 decimal (e.g. 15.5%)</option>
                            <option value="2" selected>2 decimals (e.g. 15.25%)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-zinc-600 dark:text-zinc-400 mb-1">Min %</label>
                        <input type="number" id="cfPercentageMin" value="0" class="w-full text-xs bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg p-2" />
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-zinc-600 dark:text-zinc-400 mb-1">Max %</label>
                        <input type="number" id="cfPercentageMax" value="100" class="w-full text-xs bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg p-2" />
                    </div>
                </div>
            </div>

            <!-- DYNAMIC PANEL: Rating -->
            <div id="cfPanelRating" class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200 dark:border-zinc-700/80 space-y-3 hidden">
                <span class="text-xs font-bold text-zinc-900 dark:text-zinc-100">Rating Scale Config</span>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-zinc-600 dark:text-zinc-400 mb-1">Scale Max</label>
                        <select id="cfRatingMax" class="w-full text-xs bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg p-2">
                            <option value="5" selected>1 to 5 Stars</option>
                            <option value="10">1 to 10 Scale</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-zinc-600 dark:text-zinc-400 mb-1">Visual Icon</label>
                        <select id="cfRatingIcon" class="w-full text-xs bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg p-2">
                            <option value="star" selected>★ Star</option>
                            <option value="heart">♥ Heart</option>
                            <option value="flame">🔥 Flame</option>
                            <option value="thumb">👍 Thumbs Up</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- DYNAMIC PANEL: AutoNumber -->
            <div id="cfPanelAutoNumber" class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200 dark:border-zinc-700/80 space-y-3 hidden">
                <span class="text-xs font-bold text-zinc-900 dark:text-zinc-100">AutoNumber Formatting</span>
                <div class="grid grid-cols-4 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-zinc-600 dark:text-zinc-400 mb-1">Prefix</label>
                        <input type="text" id="cfAutonumberPrefix" placeholder="OPP-" value="INV-" class="w-full text-xs bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg p-2 font-mono" />
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-zinc-600 dark:text-zinc-400 mb-1">Suffix</label>
                        <input type="text" id="cfAutonumberSuffix" placeholder="-US" class="w-full text-xs bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg p-2 font-mono" />
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-zinc-600 dark:text-zinc-400 mb-1">Zero Padding</label>
                        <input type="number" id="cfAutonumberPadding" value="4" min="1" max="10" class="w-full text-xs bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg p-2" />
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-zinc-600 dark:text-zinc-400 mb-1">Start Num</label>
                        <input type="number" id="cfAutonumberStartNum" value="1" min="1" class="w-full text-xs bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg p-2" />
                    </div>
                </div>
            </div>

            <!-- DYNAMIC PANEL: RollupSummary -->
            <div id="cfPanelRollup" class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200 dark:border-zinc-700/80 space-y-3 hidden">
                <span class="text-xs font-bold text-zinc-900 dark:text-zinc-100">Rollup Summary Engine</span>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-zinc-600 dark:text-zinc-400 mb-1">Child Entity</label>
                        <select id="cfRollupChildModule" class="w-full text-xs bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg p-2">
                            <option value="opportunities">Opportunities (Sales Pipeline)</option>
                            <option value="tasks">Tasks</option>
                            <option value="documents">Billing &amp; Documents</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-zinc-600 dark:text-zinc-400 mb-1">Aggregation Function</label>
                        <select id="cfRollupType" class="w-full text-xs bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg p-2">
                            <option value="COUNT">COUNT (Total Records)</option>
                            <option value="SUM">SUM (Total Field Value)</option>
                            <option value="AVG">AVG (Average Value)</option>
                            <option value="MAX">MAX (Maximum Value)</option>
                            <option value="MIN">MIN (Minimum Value)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-zinc-600 dark:text-zinc-400 mb-1">Target Field</label>
                        <input type="text" id="cfRollupTargetField" value="amount" class="w-full text-xs bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg p-2 font-mono" />
                    </div>
                </div>
            </div>

            <!-- DYNAMIC PANEL: MaskedText -->
            <div id="cfPanelMasked" class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200 dark:border-zinc-700/80 space-y-3 hidden">
                <span class="text-xs font-bold text-zinc-900 dark:text-zinc-100">Encrypted Masking Pattern</span>
                <div>
                    <label class="block text-[11px] font-semibold text-zinc-600 dark:text-zinc-400 mb-1">Visible Characters</label>
                    <select id="cfMaskedPattern" class="w-full text-xs bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg p-2">
                        <option value="last4" selected>Show Last 4 Digits (e.g. ••••••1234)</option>
                        <option value="first4">Show First 4 Digits (e.g. 1234••••••)</option>
                        <option value="all">Mask Entire Field (••••••••••)</option>
                    </select>
                </div>
            </div>

            <!-- Required Checkbox & Default Value -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                <div class="flex items-center gap-2">
                    <input
                        type="checkbox"
                        id="cfIsRequired"
                        class="w-4 h-4 rounded text-indigo-600 border-zinc-300 dark:border-zinc-700 focus:ring-indigo-500"
                    />
                    <label for="cfIsRequired" class="text-xs font-semibold text-zinc-700 dark:text-zinc-300 cursor-pointer">
                        Mark field as Mandatory / Required
                    </label>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Default Value (Optional)</label>
                    <input
                        type="text"
                        id="cfDefaultValue"
                        placeholder="e.g. Standard"
                        class="w-full text-xs font-medium bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg px-3 py-2 text-zinc-900 dark:text-zinc-100 outline-none focus:border-indigo-500"
                    />
                </div>
            </div>

            <!-- Help Text -->
            <div>
                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Description / Help Text</label>
                <textarea
                    id="cfHelpText"
                    rows="2"
                    placeholder="Instructions displayed to users in record modals..."
                    class="w-full text-xs font-medium bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg p-2.5 text-zinc-900 dark:text-zinc-100 outline-none focus:border-indigo-500"
                ></textarea>
            </div>

            <!-- Footer Actions -->
            <div class="pt-4 border-t border-zinc-200 dark:border-zinc-800 flex items-center justify-end gap-2.5">
                <button
                    type="button"
                    onclick="window.closeModalDialog('customFieldModalBackdrop')"
                    class="px-4 py-2 rounded-xl text-xs font-semibold text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    class="px-5 py-2 rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white shadow-xs transition-colors"
                >
                    Save Field
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 2: EDIT STANDARD FIELD OVERRIDE                                     -->
<!-- ========================================================================= -->
<div id="standardFieldModalBackdrop" class="fixed inset-0 z-[99999] hidden items-center justify-center p-4 bg-zinc-950/60 backdrop-blur-xs overflow-y-auto">
    <div class="relative w-full max-w-lg bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl shadow-2xl overflow-hidden my-8 animate-fade-in text-zinc-900 dark:text-zinc-100">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-800/50 flex items-center justify-between">
            <div>
                <h3 id="sfModalTitle" class="text-sm font-bold text-zinc-950 dark:text-zinc-100">Customize Standard Field</h3>
                <div class="flex items-center gap-2 mt-0.5">
                    <span class="text-xs text-zinc-500">Column:</span>
                    <code id="sfModalColumn" class="text-[11px] font-mono font-bold text-zinc-700 dark:text-zinc-300"></code>
                    <span class="text-zinc-300">|</span>
                    <span id="sfModalDataType" class="text-[11px] font-medium text-zinc-500"></span>
                </div>
            </div>
            <button
                type="button"
                onclick="window.closeModalDialog('standardFieldModalBackdrop')"
                class="p-1 rounded-lg text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors"
            >
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Form Body -->
        <form id="standardFieldForm" class="p-6 space-y-4">
            <div>
                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Display Label Override</label>
                <input
                    type="text"
                    id="sfFieldLabel"
                    required
                    class="w-full text-xs font-medium bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg px-3 py-2 text-zinc-900 dark:text-zinc-100 outline-none focus:border-indigo-500"
                />
            </div>

            <div class="flex items-center gap-2">
                <input
                    type="checkbox"
                    id="sfIsRequired"
                    class="w-4 h-4 rounded text-indigo-600 border-zinc-300 dark:border-zinc-700 focus:ring-indigo-500"
                />
                <label for="sfIsRequired" class="text-xs font-semibold text-zinc-700 dark:text-zinc-300 cursor-pointer">
                    Enforce Field as Required
                </label>
            </div>

            <div>
                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Help Text / Description</label>
                <textarea
                    id="sfHelpText"
                    rows="2"
                    class="w-full text-xs font-medium bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg p-2.5 text-zinc-900 dark:text-zinc-100 outline-none focus:border-indigo-500"
                ></textarea>
            </div>

            <div>
                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                    Picklist Options Override <span class="text-zinc-400 font-normal">(For dropdown standard fields)</span>
                </label>
                <textarea
                    id="sfOptions"
                    rows="2"
                    placeholder="Option 1, Option 2, Option 3"
                    class="w-full text-xs font-medium bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg p-2.5 text-zinc-900 dark:text-zinc-100 outline-none focus:border-indigo-500"
                ></textarea>
            </div>

            <!-- Footer -->
            <div class="pt-4 border-t border-zinc-200 dark:border-zinc-800 flex items-center justify-end gap-2.5">
                <button
                    type="button"
                    onclick="window.closeModalDialog('standardFieldModalBackdrop')"
                    class="px-4 py-2 rounded-xl text-xs font-semibold text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    class="px-5 py-2 rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white shadow-xs transition-colors"
                >
                    Save Override
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 3: CREATE / EDIT GLOBAL PICKLIST VALUE SET                          -->
<!-- ========================================================================= -->
<div id="globalPicklistModalBackdrop" class="fixed inset-0 z-[99999] hidden items-center justify-center p-4 bg-zinc-950/60 backdrop-blur-xs overflow-y-auto">
    <div class="relative w-full max-w-lg bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl shadow-2xl overflow-hidden my-8 animate-fade-in text-zinc-900 dark:text-zinc-100">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-800/50 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i data-lucide="list-plus" class="w-5 h-5 text-indigo-600 dark:text-indigo-400"></i>
                <h3 id="gpModalTitle" class="text-sm font-bold text-zinc-950 dark:text-zinc-100">Create Global Picklist Value Set</h3>
            </div>
            <button
                type="button"
                onclick="window.closeModalDialog('globalPicklistModalBackdrop')"
                class="p-1 rounded-lg text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors"
            >
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Form Body -->
        <form id="globalPicklistForm" class="p-6 space-y-4">
            <div>
                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Value Set Name *</label>
                <input
                    type="text"
                    id="gpName"
                    required
                    placeholder="e.g. Industry Verticals"
                    class="w-full text-xs font-medium bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg px-3 py-2 text-zinc-900 dark:text-zinc-100 outline-none focus:border-indigo-500"
                />
            </div>

            <div>
                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                    API Identifier <span class="text-zinc-400 font-normal">(Prefix gp_)</span>
                </label>
                <input
                    type="text"
                    id="gpApiName"
                    placeholder="gp_industry_verticals"
                    class="w-full text-xs font-mono bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg px-3 py-2 text-zinc-900 dark:text-zinc-100 outline-none focus:border-indigo-500"
                />
            </div>

            <div>
                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                    Picklist Values * <span class="text-zinc-400 font-normal">(One per line or comma-separated)</span>
                </label>
                <textarea
                    id="gpValues"
                    rows="4"
                    required
                    placeholder="Finance &amp; Banking&#10;Healthcare&#10;Technology &amp; SaaS&#10;Manufacturing"
                    class="w-full text-xs font-medium bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg p-2.5 text-zinc-900 dark:text-zinc-100 outline-none focus:border-indigo-500"
                ></textarea>
            </div>

            <div>
                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Description (Optional)</label>
                <textarea
                    id="gpDescription"
                    rows="2"
                    placeholder="Shared value set for corporate vertical categorization..."
                    class="w-full text-xs font-medium bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg p-2.5 text-zinc-900 dark:text-zinc-100 outline-none focus:border-indigo-500"
                ></textarea>
            </div>

            <!-- Footer -->
            <div class="pt-4 border-t border-zinc-200 dark:border-zinc-800 flex items-center justify-end gap-2.5">
                <button
                    type="button"
                    onclick="window.closeModalDialog('globalPicklistModalBackdrop')"
                    class="px-4 py-2 rounded-xl text-xs font-semibold text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    class="px-5 py-2 rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white shadow-xs transition-colors"
                >
                    Save Value Set
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Embedded Server State Data for SSR Bootstrapping -->
<script id="ofServerData" type="application/json">
{
    "moduleOptions": @json($moduleOptions),
    "dataTypes": @json($dataTypes),
    "standardFields": @json($standardFields),
    "globalPicklists": @json($globalPicklists),
    "customFields": @json($customFields),
    "standardOverrides": @json($standardOverrides)
}
</script>

<script src="{{ asset('js/object-fields.js') }}?v=2.0"></script>
@endsection
