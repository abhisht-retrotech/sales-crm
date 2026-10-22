@extends('layouts.app')

@section('title', 'Email Templates Studio — DealSync Admin')

@section('content')
<!-- Centered Max-Width Container with balanced left & right margins matching Modern DealSync Layout -->
<div id="email-templates-studio-root" class="max-w-[1240px] mx-auto w-full px-4 sm:px-6 lg:px-8 py-5 pb-16 space-y-6 animate-fade-in">
    @include('partials.admin_nav')

    <!-- Top Action Header (Tailwind CSS) -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                    <i data-lucide="mail" class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400"></i>
                    <span>Email Templates &amp; Time-Based Automation Studio</span>
                </span>
                <span class="text-xs text-zinc-500 dark:text-zinc-400 hidden md:inline">
                    Rich-Text Composer, Dynamic Merge Tags &amp; Automated Triggers
                </span>
            </div>
            <h1 class="text-2xl font-extrabold tracking-tight text-zinc-950 dark:text-white m-0">
                Email Templates Studio
            </h1>
            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 max-w-2xl m-0">
                Design responsive marketing &amp; transactional email templates with dynamic merge tags (<code class="text-indigo-600 dark:text-indigo-400 font-mono">@{{first_name}}</code>, <code class="text-indigo-600 dark:text-indigo-400 font-mono">@{{company}}</code>) and configure time-based automated dispatch triggers.
            </p>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap sm:flex-nowrap">
            <button
                type="button"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-medium bg-white dark:bg-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-200 border border-zinc-200 dark:border-zinc-700 rounded-lg shadow-xs transition-colors cursor-pointer"
                onclick="evaluateScheduledEmailTriggers()"
            >
                <i data-lucide="play" class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400"></i>
                <span>Evaluate Triggers Now</span>
            </button>
            <button
                type="button"
                id="btn-create-email-template"
                class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold rounded-lg bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white border border-indigo-700 shadow-sm transition-all hover:shadow-indigo-500/20 cursor-pointer"
                onclick="openCreateTemplateModal()"
            >
                <i data-lucide="plus" class="w-4 h-4 stroke-[2.5]"></i>
                <span>Create Email Template</span>
            </button>
        </div>
    </div>

    <!-- 4 KPI Metric Cards (Responsive Grid) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 mb-6">
        <!-- Metric 1: Total Templates -->
        <div class="p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 shadow-xs hover:border-zinc-300 dark:hover:border-zinc-700 transition-all">
            <div class="text-[11px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 mb-1">
                Email Templates
            </div>
            <div class="text-2xl font-extrabold tracking-tight text-indigo-600 dark:text-indigo-400" id="kpi-total-templates">
                {{ $templates->count() }} Templates
            </div>
            <div class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 mt-1 flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Ready for Instant Dispatch</span>
            </div>
        </div>

        <!-- Metric 2: Modules Covered -->
        <div class="p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 shadow-xs hover:border-zinc-300 dark:hover:border-zinc-700 transition-all">
            <div class="text-[11px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 mb-1">
                Modules Covered
            </div>
            <div class="text-2xl font-extrabold tracking-tight text-sky-600 dark:text-sky-400">
                {{ max(1, $templates->pluck('entity_type')->unique()->count()) }} Entities
            </div>
            <div class="text-[11px] font-medium text-zinc-500 dark:text-zinc-400 mt-1">
                Leads, Deals, Accounts, POs
            </div>
        </div>

        <!-- Metric 3: Scheduled Automation Triggers -->
        <div class="p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 shadow-xs hover:border-zinc-300 dark:hover:border-zinc-700 transition-all">
            <div class="text-[11px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 mb-1">
                Scheduled Automation
            </div>
            <div class="text-2xl font-extrabold tracking-tight text-amber-600 dark:text-amber-400" id="kpi-total-rules">
                {{ $emailRules->count() }} Active Rules
            </div>
            <div class="text-[11px] font-medium text-zinc-500 dark:text-zinc-400 mt-1">
                Time-based offsets &amp; criteria
            </div>
        </div>

        <!-- Metric 4: Dynamic Merge Tags Supported -->
        <div class="p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 shadow-xs hover:border-zinc-300 dark:hover:border-zinc-700 transition-all">
            <div class="text-[11px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 mb-1">
                Dynamic Merge Tags
            </div>
            <div class="text-2xl font-extrabold tracking-tight text-emerald-600 dark:text-emerald-400">
                38 Ready Tags
            </div>
            <div class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 mt-1 flex items-center gap-1">
                <i data-lucide="check" class="w-3 h-3"></i>
                <span>Auto-Substitution Active</span>
            </div>
        </div>
    </div>

    <!-- Main Navigation Tabs (Templates vs Triggers) -->
    <div class="flex items-center gap-2 border-b border-zinc-200 dark:border-zinc-800 mb-5">
        <button
            type="button"
            id="tab-btn-templates"
            class="main-studio-tab inline-flex items-center gap-2 px-4 py-2.5 text-xs font-bold border-b-2 border-indigo-600 text-indigo-600 dark:text-indigo-400 transition-colors cursor-pointer"
            onclick="switchMainStudioTab('templates')"
        >
            <i data-lucide="mail" class="w-4 h-4"></i>
            <span>Email Templates Library</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 font-bold">
                {{ $templates->count() }}
            </span>
        </button>

        <button
            type="button"
            id="tab-btn-triggers"
            class="main-studio-tab inline-flex items-center gap-2 px-4 py-2.5 text-xs font-bold border-b-2 border-transparent text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-200 transition-colors cursor-pointer"
            onclick="switchMainStudioTab('triggers')"
        >
            <i data-lucide="clock" class="w-4 h-4"></i>
            <span>Time-Based &amp; Scheduled Triggers</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 font-bold">
                {{ $emailRules->count() }}
            </span>
        </button>
    </div>

    <!-- ================= TAB 1: EMAIL TEMPLATES LIBRARY ================= -->
    <div id="tab-content-templates" class="space-y-4">
        <!-- Filter Toolbar (Tailwind CSS) -->
        <div class="p-3.5 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-3 flex-wrap">
            <!-- Left: Search and Category -->
            <div class="flex items-center gap-2.5 flex-1 min-w-[280px]">
                <div class="relative flex-1">
                    <i data-lucide="search" class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400"></i>
                    <input
                        type="text"
                        id="template-search-input"
                        placeholder="Search template name, subject, or merge tags..."
                        class="w-full pl-9 pr-8 py-1.5 text-xs bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg text-zinc-900 dark:text-white placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                        oninput="filterEmailTemplates()"
                    >
                    <button
                        type="button"
                        class="absolute right-2.5 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 text-xs"
                        onclick="clearTemplateSearch()"
                    >
                        ✕
                    </button>
                </div>

                <select
                    id="template-category-filter"
                    class="px-3 py-1.5 text-xs bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg text-zinc-700 dark:text-zinc-200 font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                    onchange="filterEmailTemplates()"
                >
                    <option value="all">All Categories</option>
                    <option value="sales">Sales &amp; Outreach</option>
                    <option value="onboarding">Customer Onboarding</option>
                    <option value="procurement">Procurement &amp; PO</option>
                    <option value="billing">Invoices &amp; Billing</option>
                    <option value="support">Client Support</option>
                    <option value="general">General / Internal</option>
                </select>
            </div>

            <!-- Right: Entity Filter Pills & View Mode Switcher -->
            <div class="flex items-center gap-2 flex-wrap">
                <!-- Entity Pills -->
                <div class="inline-flex p-0.5 rounded-lg bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-xs">
                    <button type="button" class="entity-filter-btn px-2.5 py-1 rounded-md text-[11px] font-semibold bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white shadow-xs transition-all cursor-pointer" onclick="setEntityFilter('all', this)">All</button>
                    <button type="button" class="entity-filter-btn px-2.5 py-1 rounded-md text-[11px] font-semibold text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-all cursor-pointer" onclick="setEntityFilter('leads', this)">Leads</button>
                    <button type="button" class="entity-filter-btn px-2.5 py-1 rounded-md text-[11px] font-semibold text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-all cursor-pointer" onclick="setEntityFilter('opportunities', this)">Deals</button>
                    <button type="button" class="entity-filter-btn px-2.5 py-1 rounded-md text-[11px] font-semibold text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-all cursor-pointer" onclick="setEntityFilter('accounts', this)">Accounts</button>
                    <button type="button" class="entity-filter-btn px-2.5 py-1 rounded-md text-[11px] font-semibold text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-all cursor-pointer" onclick="setEntityFilter('purchase_deals', this)">Procurement</button>
                    <button type="button" class="entity-filter-btn px-2.5 py-1 rounded-md text-[11px] font-semibold text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-all cursor-pointer" onclick="setEntityFilter('payments', this)">Billing</button>
                </div>

                <!-- View Mode (List vs Grid) -->
                <div class="inline-flex p-0.5 rounded-lg bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-xs">
                    <button
                        type="button"
                        id="view-mode-list-btn"
                        class="p-1.5 rounded-md bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white shadow-xs cursor-pointer"
                        onclick="setViewMode('list')"
                        title="Table List View"
                    >
                        <i data-lucide="list" class="w-3.5 h-3.5"></i>
                    </button>
                    <button
                        type="button"
                        id="view-mode-grid-btn"
                        class="p-1.5 rounded-md text-zinc-500 hover:text-zinc-900 dark:hover:text-white cursor-pointer"
                        onclick="setViewMode('grid')"
                        title="Card Grid View"
                    >
                        <i data-lucide="layout-grid" class="w-3.5 h-3.5"></i>
                    </button>
                </div>

                <!-- Quick Create Template Button -->
                <button
                    type="button"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white shadow-xs transition-colors cursor-pointer"
                    onclick="openCreateTemplateModal()"
                    title="Quick create new email template"
                >
                    <i data-lucide="plus" class="w-3.5 h-3.5 stroke-[2.5]"></i>
                    <span>New Template</span>
                </button>
            </div>
        </div>

        <!-- 1. Table List View -->
        <div id="templates-list-view" class="rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/80 dark:bg-zinc-800/40 text-[11px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            <th class="px-4 py-3">Template Name &amp; Subject</th>
                            <th class="px-3.5 py-3">Target Entity</th>
                            <th class="px-3.5 py-3">Category</th>
                            <th class="px-3.5 py-3">Attachments</th>
                            <th class="px-3.5 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800" id="templates-table-body">
                        @forelse($templates as $tmpl)
                            <tr
                                class="template-row hover:bg-zinc-50/70 dark:hover:bg-zinc-800/30 transition-colors"
                                data-id="{{ $tmpl->id }}"
                                data-entity="{{ $tmpl->entity_type }}"
                                data-category="{{ $tmpl->category }}"
                                data-name="{{ strtolower($tmpl->name) }}"
                                data-subject="{{ strtolower($tmpl->subject) }}"
                            >
                                <td class="px-4 py-3">
                                    <div class="flex items-start gap-2.5">
                                        <div class="w-7 h-7 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center flex-shrink-0 mt-0.5">
                                            <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-bold text-zinc-900 dark:text-white truncate max-w-md">
                                                {{ $tmpl->name }}
                                            </div>
                                            <div class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5 truncate max-w-md font-mono">
                                                {!! preg_replace('/(\{\{.*?\}\}|\{.*?\})/', '<span class="bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 px-1 py-0.2 rounded font-bold text-[10px]">$1</span>', e($tmpl->subject)) !!}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3.5 py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10.5px] font-bold uppercase tracking-wider bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700">
                                        {{ str_replace('_', ' ', $tmpl->entity_type) }}
                                    </span>
                                </td>
                                <td class="px-3.5 py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10.5px] font-medium capitalize bg-indigo-50 text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                                        {{ $tmpl->category ?: 'general' }}
                                    </span>
                                </td>
                                <td class="px-3.5 py-3">
                                    <span class="inline-flex items-center gap-1 text-[11px] text-zinc-500 dark:text-zinc-400">
                                        <i data-lucide="paperclip" class="w-3 h-3 text-zinc-400"></i>
                                        <span>{{ is_array($tmpl->attached_file_ids) ? count($tmpl->attached_file_ids) : 0 }} files</span>
                                    </span>
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <button
                                        type="button"
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold transition-colors cursor-pointer {{ $tmpl->is_active ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700' }}"
                                        onclick="toggleTemplateActive({{ $tmpl->id }}, this)"
                                    >
                                        {{ $tmpl->is_active ? 'Active' : 'Inactive' }}
                                    </button>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="inline-flex items-center gap-1">
                                        <button
                                            type="button"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-semibold bg-indigo-50 dark:bg-indigo-950/50 hover:bg-indigo-100 dark:hover:bg-indigo-900 text-indigo-700 dark:text-indigo-300 rounded-md border border-indigo-200 dark:border-indigo-800 transition-colors shadow-xs cursor-pointer"
                                            onclick="openTestSendModal({{ json_encode($tmpl) }})"
                                            title="Test send &amp; preview merge tags"
                                        >
                                            <i data-lucide="send" class="w-3 h-3"></i>
                                            <span>Test Send</span>
                                        </button>
                                        <button
                                            type="button"
                                            class="p-1 rounded text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800 cursor-pointer"
                                            onclick="openEditTemplateModal({{ json_encode($tmpl) }})"
                                            title="Edit Template"
                                        >
                                            <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                        <button
                                            type="button"
                                            class="p-1 rounded text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800 cursor-pointer"
                                            onclick="cloneTemplate({{ $tmpl->id }})"
                                            title="Clone Template"
                                        >
                                            <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                                        </button>
                                        <button
                                            type="button"
                                            class="p-1 rounded text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-950/50 cursor-pointer"
                                            onclick="deleteTemplate({{ $tmpl->id }})"
                                            title="Delete Template"
                                        >
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-zinc-500">
                                    <div class="w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 mx-auto mb-3 flex items-center justify-center">
                                        <i data-lucide="mail" class="w-6 h-6"></i>
                                    </div>
                                    <div class="text-sm font-bold text-zinc-900 dark:text-white">No Email Templates Found</div>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 max-w-sm mx-auto">Create customized transactional &amp; marketing email templates with rich text and dynamic merge tags.</p>
                                    <div class="mt-4">
                                        <button
                                            type="button"
                                            onclick="openCreateTemplateModal()"
                                            class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm border border-indigo-700 transition-all cursor-pointer"
                                        >
                                            <i data-lucide="plus" class="w-4 h-4 stroke-[2.5]"></i>
                                            <span>Create First Template</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 2. Card Grid View (Hidden by default) -->
        <div id="templates-grid-view" class="hidden grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($templates as $tmpl)
                <div
                    class="template-card p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 shadow-xs hover:border-zinc-300 dark:hover:border-zinc-700 transition-all flex flex-col justify-between"
                    data-id="{{ $tmpl->id }}"
                    data-entity="{{ $tmpl->entity_type }}"
                    data-category="{{ $tmpl->category }}"
                    data-name="{{ strtolower($tmpl->name) }}"
                    data-subject="{{ strtolower($tmpl->subject) }}"
                >
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700">
                                {{ str_replace('_', ' ', $tmpl->entity_type) }}
                            </span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium capitalize bg-indigo-50 text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-300">
                                {{ $tmpl->category ?: 'general' }}
                            </span>
                        </div>
                        <h4 class="text-sm font-bold text-zinc-950 dark:text-white truncate m-0">
                            {{ $tmpl->name }}
                        </h4>
                        <div class="text-xs text-zinc-600 dark:text-zinc-400 mt-1 line-clamp-1 font-mono text-[11px]">
                            {!! preg_replace('/(\{\{.*?\}\}|\{.*?\})/', '<span class="bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 px-1 py-0.2 rounded font-bold text-[10px]">$1</span>', e($tmpl->subject)) !!}
                        </div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-2 line-clamp-2 leading-relaxed">
                            {{ Str::limit(strip_tags($tmpl->body_html), 110) }}
                        </div>
                    </div>

                    <div class="flex items-center justify-between border-t border-zinc-100 dark:border-zinc-800 pt-3 mt-4">
                        <span class="text-[11px] text-zinc-400 flex items-center gap-1">
                            <i data-lucide="paperclip" class="w-3 h-3"></i>
                            <span>{{ is_array($tmpl->attached_file_ids) ? count($tmpl->attached_file_ids) : 0 }} attachments</span>
                        </span>
                        <div class="flex items-center gap-1">
                            <button
                                type="button"
                                class="px-2.5 py-1 text-xs font-semibold bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/60 dark:hover:bg-indigo-900 text-indigo-700 dark:text-indigo-300 rounded border border-indigo-200 dark:border-indigo-800 transition-colors"
                                onclick="openTestSendModal({{ json_encode($tmpl) }})"
                            >
                                Test Send
                            </button>
                            <button
                                type="button"
                                class="p-1 text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200"
                                onclick="openEditTemplateModal({{ json_encode($tmpl) }})"
                            >
                                <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                            </button>
                            <button
                                type="button"
                                class="p-1 text-red-500 hover:text-red-700"
                                onclick="deleteTemplate({{ $tmpl->id }})"
                            >
                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- ================= TAB 2: TIME-BASED & SCHEDULED TRIGGERS ================= -->
    <div id="tab-content-triggers" class="hidden space-y-4">
        <!-- Trigger Toolbar -->
        <div class="p-3.5 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-3">
            <div class="flex items-center gap-2.5 flex-1">
                <div class="relative flex-1 max-w-sm">
                    <i data-lucide="search" class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400"></i>
                    <input
                        type="text"
                        id="rule-search-input"
                        placeholder="Search automated triggers..."
                        class="w-full pl-9 pr-3 py-1.5 text-xs bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg text-zinc-900 dark:text-white placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                        oninput="filterTriggerRules()"
                    >
                </div>
                <select
                    id="rule-module-filter"
                    class="px-3 py-1.5 text-xs bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg text-zinc-700 dark:text-zinc-200 font-medium"
                    onchange="filterTriggerRules()"
                >
                    <option value="all">All Modules</option>
                    <option value="leads">Leads</option>
                    <option value="opportunities">Opportunities</option>
                    <option value="accounts">Accounts</option>
                    <option value="purchase_deals">Purchase Deals</option>
                    <option value="shipping">Shipping</option>
                    <option value="payments">Payments</option>
                </select>
            </div>

            <button
                type="button"
                id="btn-create-automation-rule"
                class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white border border-indigo-700 shadow-sm transition-all cursor-pointer"
                onclick="openCreateRuleModal()"
            >
                <i data-lucide="plus" class="w-4 h-4 stroke-[2.5]"></i>
                <span>Create Automation Trigger</span>
            </button>
        </div>

        <!-- Trigger Rules Table -->
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/80 dark:bg-zinc-800/40 text-[11px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            <th class="px-4 py-3">Rule Name &amp; Timing</th>
                            <th class="px-3.5 py-3">Target Module</th>
                            <th class="px-3.5 py-3">Linked Template</th>
                            <th class="px-3.5 py-3">Criteria Condition</th>
                            <th class="px-3.5 py-3">Recipient Target</th>
                            <th class="px-3.5 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800" id="rules-table-body">
                        @forelse($emailRules as $rule)
                            <tr
                                class="rule-row hover:bg-zinc-50/70 dark:hover:bg-zinc-800/30 transition-colors"
                                data-id="{{ $rule->id }}"
                                data-module="{{ $rule->module }}"
                                data-name="{{ strtolower($rule->name) }}"
                            >
                                <td class="px-4 py-3">
                                    <div class="font-bold text-zinc-900 dark:text-white">
                                        {{ $rule->name }}
                                    </div>
                                    <div class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5 flex items-center gap-1">
                                        <i data-lucide="timer" class="w-3 h-3 text-amber-500"></i>
                                        <span>{{ $rule->offset_value }} {{ $rule->offset_unit }} {{ $rule->offset_direction }} <code class="font-mono text-[10px] text-indigo-600 dark:text-indigo-400 font-semibold">{{ $rule->date_field }}</code></span>
                                    </div>
                                </td>
                                <td class="px-3.5 py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700">
                                        {{ str_replace('_', ' ', $rule->module) }}
                                    </span>
                                </td>
                                <td class="px-3.5 py-3">
                                    <div class="font-medium text-zinc-800 dark:text-zinc-200">
                                        {{ $rule->template ? $rule->template->name : 'Template #' . $rule->email_template_id }}
                                    </div>
                                    <div class="text-[10px] text-zinc-400 truncate max-w-xs">
                                        {{ $rule->template ? $rule->template->subject : '' }}
                                    </div>
                                </td>
                                <td class="px-3.5 py-3">
                                    @if($rule->criteria_field)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10.5px] font-mono bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700">
                                            {{ $rule->criteria_field }} {{ $rule->criteria_operator }} '{{ $rule->criteria_value }}'
                                        </span>
                                    @else
                                        <span class="text-zinc-400 italic text-[11px]">All records</span>
                                    @endif
                                </td>
                                <td class="px-3.5 py-3">
                                    <span class="capitalize text-zinc-600 dark:text-zinc-300 font-medium">
                                        {{ str_replace('_', ' ', $rule->recipient_type ?? 'record_owner') }}
                                    </span>
                                </td>
                                <td class="px-3.5 py-3 text-center">
                                    <button
                                        type="button"
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold transition-colors cursor-pointer {{ $rule->is_active ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700' }}"
                                        onclick="toggleEmailRuleActive({{ $rule->id }}, this)"
                                    >
                                        {{ $rule->is_active ? 'Active' : 'Inactive' }}
                                    </button>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="inline-flex items-center gap-1">
                                        <button
                                            type="button"
                                            class="inline-flex items-center gap-1 px-2 py-1 text-[11px] font-medium bg-indigo-50 dark:bg-indigo-950/50 hover:bg-indigo-100 dark:hover:bg-indigo-900 text-indigo-700 dark:text-indigo-300 rounded border border-indigo-200 dark:border-indigo-800 transition-colors shadow-xs cursor-pointer"
                                            onclick="triggerRuleEvaluationTest({{ json_encode($rule) }})"
                                            title="Evaluate Trigger Condition"
                                        >
                                            <i data-lucide="play" class="w-3 h-3"></i>
                                            <span>Test</span>
                                        </button>
                                        <button
                                            type="button"
                                            class="p-1 rounded text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800 cursor-pointer"
                                            onclick="openEditRuleModal({{ json_encode($rule) }})"
                                            title="Edit Rule"
                                        >
                                            <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                        <button
                                            type="button"
                                            class="p-1 rounded text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-950/50 cursor-pointer"
                                            onclick="deleteEmailRule({{ $rule->id }})"
                                            title="Delete Rule"
                                        >
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-zinc-500">
                                    <i data-lucide="clock" class="w-8 h-8 mx-auto mb-2 text-zinc-400"></i>
                                    <div class="font-semibold text-zinc-700 dark:text-zinc-300">No Automated Email Rules</div>
                                    <p class="text-xs text-zinc-400 mt-1">Configure time-based triggers like deal close reminders or lead follow-ups.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ================= MODAL 1: CREATE / EDIT EMAIL TEMPLATE ================= -->
    <div id="template-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[22000] hidden items-center justify-center p-4 overflow-y-auto">
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl shadow-2xl max-w-4xl w-full max-h-[92vh] flex flex-col overflow-hidden animate-in zoom-in-95 duration-150">
            <!-- Modal Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900/50">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                        <i data-lucide="mail" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-zinc-950 dark:text-white m-0" id="tmpl-modal-title">
                            Create Email Template
                        </h3>
                        <p class="text-[11px] text-zinc-500 m-0">Visual rich text composer &amp; dynamic merge tags</p>
                    </div>
                </div>
                <button type="button" onclick="closeTemplateModal()" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 p-1 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 cursor-pointer">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <!-- Modal Form Body -->
            <form id="template-form" onsubmit="handleTemplateFormSubmit(event)" class="p-6 overflow-y-auto space-y-4 text-xs">
                <input type="hidden" id="form-tmpl-id" value="">

                <!-- 1. Name, Category, Module -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                    <div>
                        <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Template Name *</label>
                        <input
                            type="text"
                            id="form-tmpl-name"
                            required
                            placeholder="e.g. Lead Welcome Solution Overview"
                            class="w-full px-3 py-1.5 text-xs bg-zinc-50 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 rounded-lg text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                        >
                    </div>
                    <div>
                        <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Target Entity / Module *</label>
                        <select
                            id="form-tmpl-entity"
                            required
                            class="w-full px-3 py-1.5 text-xs bg-zinc-50 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 rounded-lg text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                            onchange="updateMergeTagAssistant()"
                        >
                            <option value="leads">Leads</option>
                            <option value="opportunities">Opportunities</option>
                            <option value="accounts">Accounts</option>
                            <option value="contacts">Contacts</option>
                            <option value="purchase_deals">Purchase Deals</option>
                            <option value="shipping">Shipping &amp; Logistics</option>
                            <option value="payments">Invoices &amp; Payments</option>
                            <option value="wires">Wire Transactions</option>
                            <option value="global">Global (All Objects)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Template Category</label>
                        <select
                            id="form-tmpl-category"
                            class="w-full px-3 py-1.5 text-xs bg-zinc-50 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 rounded-lg text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                        >
                            <option value="sales">Sales &amp; Outreach</option>
                            <option value="onboarding">Customer Onboarding</option>
                            <option value="procurement">Procurement &amp; RFQ</option>
                            <option value="billing">Invoices &amp; Billing</option>
                            <option value="support">Customer Support</option>
                            <option value="general">General / Internal</option>
                        </select>
                    </div>
                </div>

                <!-- 2. Dynamic Merge Tag Assistant Banner -->
                <div class="p-3 rounded-xl border border-indigo-200 dark:border-indigo-800/80 bg-indigo-50/50 dark:bg-indigo-950/30">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-1.5 text-[11.5px] font-bold text-indigo-900 dark:text-indigo-200">
                            <i data-lucide="sparkles" class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400"></i>
                            <span>Dynamic Merge Tag Assistant</span>
                        </div>
                        <span class="text-[10px] text-indigo-700 dark:text-indigo-300">Click any tag below to insert into Subject or Body</span>
                    </div>
                    <div id="merge-tags-container" class="flex flex-wrap gap-1.5">
                        <!-- Populated dynamically via JS -->
                    </div>
                </div>

                <!-- 3. Subject Line -->
                <div>
                    <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Email Subject Line *</label>
                    <div class="relative">
                        <input
                            type="text"
                            id="form-tmpl-subject"
                            required
                            placeholder="e.g. Welcome to DealSync, @{{first_name}} — Next Steps for @{{company}}"
                            class="w-full px-3 py-2 text-xs bg-zinc-50 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 rounded-lg text-zinc-900 dark:text-white font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-mono"
                        >
                    </div>
                </div>

                <!-- 4. Visual Rich Text Email Editor (Tailwind CSS) -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="font-semibold text-zinc-700 dark:text-zinc-300">Email Body *</label>
                        <div class="inline-flex p-0.5 rounded-lg bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-[10px]">
                            <button type="button" id="editor-mode-visual-btn" class="px-2 py-0.5 rounded font-bold bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white shadow-xs cursor-pointer" onclick="toggleEditorMode('visual')">Visual Editor</button>
                            <button type="button" id="editor-mode-html-btn" class="px-2 py-0.5 rounded font-bold text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white cursor-pointer" onclick="toggleEditorMode('html')">Raw HTML</button>
                        </div>
                    </div>

                    <div class="border border-zinc-300 dark:border-zinc-700 rounded-xl overflow-hidden focus-within:ring-2 focus-within:ring-indigo-500/20 focus-within:border-indigo-500">
                        <!-- Rich Text Toolbar -->
                        <div id="rich-editor-toolbar" class="flex flex-wrap items-center gap-1 p-1.5 bg-zinc-50 dark:bg-zinc-800/80 border-b border-zinc-200 dark:border-zinc-700 text-zinc-600 dark:text-zinc-300 select-none">
                            <button type="button" class="toolbar-btn p-1.5 rounded hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-200 cursor-pointer" onclick="execEditorCommand('bold')" title="Bold (Ctrl+B)"><i data-lucide="bold" class="w-3.5 h-3.5"></i></button>
                            <button type="button" class="toolbar-btn p-1.5 rounded hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-200 cursor-pointer" onclick="execEditorCommand('italic')" title="Italic (Ctrl+I)"><i data-lucide="italic" class="w-3.5 h-3.5"></i></button>
                            <button type="button" class="toolbar-btn p-1.5 rounded hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-200 cursor-pointer" onclick="execEditorCommand('underline')" title="Underline (Ctrl+U)"><i data-lucide="underline" class="w-3.5 h-3.5"></i></button>
                            <button type="button" class="toolbar-btn p-1.5 rounded hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-200 cursor-pointer" onclick="execEditorCommand('strikeThrough')" title="Strikethrough"><i data-lucide="strikethrough" class="w-3.5 h-3.5"></i></button>
                            <div class="w-px h-4 bg-zinc-300 dark:bg-zinc-700 mx-1"></div>
                            <button type="button" class="toolbar-btn p-1.5 rounded hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-200 cursor-pointer" onclick="execEditorCommand('formatBlock', 'h2')" title="Heading 2"><i data-lucide="heading-2" class="w-3.5 h-3.5"></i></button>
                            <button type="button" class="toolbar-btn p-1.5 rounded hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-200 cursor-pointer" onclick="execEditorCommand('formatBlock', 'p')" title="Paragraph"><i data-lucide="type" class="w-3.5 h-3.5"></i></button>
                            <div class="w-px h-4 bg-zinc-300 dark:bg-zinc-700 mx-1"></div>
                            <button type="button" class="toolbar-btn p-1.5 rounded hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-200 cursor-pointer" onclick="execEditorCommand('insertUnorderedList')" title="Bullet List"><i data-lucide="list" class="w-3.5 h-3.5"></i></button>
                            <button type="button" class="toolbar-btn p-1.5 rounded hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-200 cursor-pointer" onclick="execEditorCommand('insertOrderedList')" title="Numbered List"><i data-lucide="list-ordered" class="w-3.5 h-3.5"></i></button>
                            <div class="w-px h-4 bg-zinc-300 dark:bg-zinc-700 mx-1"></div>
                            <button type="button" class="toolbar-btn p-1.5 rounded hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-200 cursor-pointer" onclick="execEditorCommand('justifyLeft')" title="Align Left"><i data-lucide="align-left" class="w-3.5 h-3.5"></i></button>
                            <button type="button" class="toolbar-btn p-1.5 rounded hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-200 cursor-pointer" onclick="execEditorCommand('justifyCenter')" title="Align Center"><i data-lucide="align-center" class="w-3.5 h-3.5"></i></button>
                            <button type="button" class="toolbar-btn p-1.5 rounded hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-200 cursor-pointer" onclick="execEditorCommand('justifyRight')" title="Align Right"><i data-lucide="align-right" class="w-3.5 h-3.5"></i></button>
                            <div class="w-px h-4 bg-zinc-300 dark:bg-zinc-700 mx-1"></div>
                            <button type="button" class="toolbar-btn p-1.5 rounded hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-200 cursor-pointer" onclick="insertLinkPrompt()" title="Insert Link"><i data-lucide="link" class="w-3.5 h-3.5"></i></button>
                            <button type="button" class="toolbar-btn p-1.5 rounded hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-200 cursor-pointer" onclick="execEditorCommand('insertHorizontalRule')" title="Horizontal Divider"><i data-lucide="minus" class="w-3.5 h-3.5"></i></button>
                            <button type="button" class="toolbar-btn p-1.5 rounded hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-200 cursor-pointer" onclick="execEditorCommand('removeFormat')" title="Clear Formatting"><i data-lucide="remove-formatting" class="w-3.5 h-3.5"></i></button>
                        </div>

                        <!-- Visual Canvas -->
                        <div
                            id="visual-canvas"
                            contenteditable="true"
                            class="p-4 min-h-[260px] max-h-[360px] overflow-y-auto bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white leading-relaxed focus:outline-none"
                            placeholder="Type email body here..."
                        ></div>

                        <!-- Raw HTML Textarea (Toggled) -->
                        <textarea
                            id="html-canvas"
                            class="hidden w-full p-4 min-h-[260px] max-h-[360px] font-mono text-xs bg-zinc-950 text-emerald-400 focus:outline-none border-none resize-y"
                            placeholder="<p>Write raw HTML here...</p>"
                        ></textarea>
                    </div>
                </div>

                <!-- 5. Active Status -->
                <div class="flex items-center gap-2 pt-2">
                    <input
                        type="checkbox"
                        id="form-tmpl-active"
                        checked
                        class="w-4 h-4 text-indigo-600 rounded border-zinc-300 focus:ring-indigo-500 cursor-pointer"
                    >
                    <label for="form-tmpl-active" class="font-medium text-zinc-700 dark:text-zinc-300 cursor-pointer">
                        Enable this template for immediate dispatch and automation triggers
                    </label>
                </div>

                <!-- Modal Footer -->
                <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                    <button
                        type="button"
                        onclick="closeTemplateModal()"
                        class="px-4 py-1.5 text-xs font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-lg transition-colors cursor-pointer"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        id="save-template-btn"
                        class="px-5 py-1.5 text-xs font-semibold bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg shadow-sm transition-all cursor-pointer"
                    >
                        Save Email Template
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ================= MODAL 2: TEST SEND & PREVIEW MODAL ================= -->
    <div id="test-send-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[23000] hidden items-center justify-center p-4 overflow-y-auto">
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] flex flex-col overflow-hidden animate-in zoom-in-95 duration-150">
            <!-- Modal Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900/50">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                        <i data-lucide="send" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-zinc-950 dark:text-white m-0">
                            Test Dispatch &amp; Merge Tag Preview
                        </h3>
                        <p class="text-[11px] text-zinc-500 m-0" id="test-modal-subtitle">Simulate real recipient delivery with resolved tags</p>
                    </div>
                </div>
                <button type="button" onclick="closeTestSendModal()" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 p-1 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 cursor-pointer">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <!-- Modal Content -->
            <div class="p-6 overflow-y-auto space-y-4 text-xs">
                <div>
                    <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Recipient Test Email *</label>
                    <input
                        type="email"
                        id="test-send-email-to"
                        required
                        value="alex.morgan@apexrobotics.io"
                        placeholder="recipient@example.com"
                        class="w-full px-3 py-1.5 text-xs bg-zinc-50 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 rounded-lg text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                    >
                </div>

                <!-- Resolved Subject Preview -->
                <div>
                    <div class="text-[11px] font-semibold text-zinc-600 dark:text-zinc-400 mb-1">Rendered Subject Line</div>
                    <div id="test-preview-subject" class="p-3 bg-zinc-50 dark:bg-zinc-800/80 border border-zinc-200 dark:border-zinc-700 rounded-lg text-zinc-900 dark:text-white font-bold text-xs">
                        Subject preview...
                    </div>
                </div>

                <!-- Rendered Body Preview -->
                <div>
                    <div class="text-[11px] font-semibold text-zinc-600 dark:text-zinc-400 mb-1">Rendered Email Body Preview</div>
                    <div id="test-preview-body" class="p-4 bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200 dark:border-zinc-700 rounded-lg text-zinc-800 dark:text-zinc-200 text-xs leading-relaxed max-h-60 overflow-y-auto">
                        Body preview...
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-end gap-2.5 px-6 py-3.5 border-t border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900/50">
                <button
                    type="button"
                    onclick="closeTestSendModal()"
                    class="px-4 py-1.5 text-xs font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-lg transition-colors cursor-pointer"
                >
                    Cancel
                </button>
                <button
                    type="button"
                    id="confirm-test-send-btn"
                    onclick="dispatchTestEmail()"
                    class="inline-flex items-center gap-1.5 px-5 py-1.5 text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg shadow-sm transition-all cursor-pointer"
                >
                    <i data-lucide="send" class="w-3.5 h-3.5"></i>
                    <span>Send Test Email</span>
                </button>
            </div>
        </div>
    </div>

    <!-- ================= MODAL 3: CREATE / EDIT AUTOMATION RULE ================= -->
    <div id="rule-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[22000] hidden items-center justify-center p-4 overflow-y-auto">
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] flex flex-col overflow-hidden animate-in zoom-in-95 duration-150">
            <!-- Modal Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900/50">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                        <i data-lucide="clock" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-zinc-950 dark:text-white m-0" id="rule-modal-title">
                            Create Automated Email Trigger
                        </h3>
                        <p class="text-[11px] text-zinc-500 m-0">Time-based offsets, criteria formulas &amp; recipients</p>
                    </div>
                </div>
                <button type="button" onclick="closeRuleModal()" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 p-1 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 cursor-pointer">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <!-- Modal Form Body -->
            <form id="rule-form" onsubmit="handleRuleFormSubmit(event)" class="p-6 overflow-y-auto space-y-4 text-xs">
                <input type="hidden" id="form-rule-id" value="">

                <div>
                    <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Trigger Rule Name *</label>
                    <input
                        type="text"
                        id="form-rule-name"
                        required
                        placeholder="e.g. High Value Deal Close Reminder"
                        class="w-full px-3 py-1.5 text-xs bg-zinc-50 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 rounded-lg text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                    >
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Target Module *</label>
                        <select
                            id="form-rule-module"
                            required
                            class="w-full px-3 py-1.5 text-xs bg-zinc-50 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 rounded-lg text-zinc-900 dark:text-white"
                        >
                            <option value="opportunities">Opportunities</option>
                            <option value="leads">Leads</option>
                            <option value="accounts">Accounts</option>
                            <option value="purchase_deals">Purchase Deals</option>
                            <option value="shipping">Shipping</option>
                            <option value="payments">Payments</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Linked Email Template *</label>
                        <select
                            id="form-rule-template-id"
                            required
                            class="w-full px-3 py-1.5 text-xs bg-zinc-50 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 rounded-lg text-zinc-900 dark:text-white"
                        >
                            @foreach($templates as $t)
                                <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->entity_type }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Timing Offset Config -->
                <div class="p-3.5 bg-zinc-50 dark:bg-zinc-800/50 rounded-xl border border-zinc-200 dark:border-zinc-800 space-y-2.5">
                    <div class="font-semibold text-zinc-800 dark:text-zinc-200 text-xs">Trigger Schedule &amp; Date Offset</div>
                    <div class="grid grid-cols-3 gap-2">
                        <div>
                            <label class="block text-[11px] text-zinc-500 mb-1">Offset Value</label>
                            <input
                                type="number"
                                id="form-rule-offset-val"
                                value="2"
                                min="0"
                                class="w-full px-2.5 py-1.5 text-xs bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 rounded-lg text-zinc-900 dark:text-white"
                            >
                        </div>
                        <div>
                            <label class="block text-[11px] text-zinc-500 mb-1">Unit</label>
                            <select
                                id="form-rule-offset-unit"
                                class="w-full px-2.5 py-1.5 text-xs bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 rounded-lg text-zinc-900 dark:text-white"
                            >
                                <option value="hours">Hours</option>
                                <option value="days" selected>Days</option>
                                <option value="minutes">Minutes</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] text-zinc-500 mb-1">Direction</label>
                            <select
                                id="form-rule-offset-dir"
                                class="w-full px-2.5 py-1.5 text-xs bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 rounded-lg text-zinc-900 dark:text-white"
                            >
                                <option value="before" selected>Before</option>
                                <option value="after">After</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-[11px] text-zinc-500 mb-1">Trigger Date Field</label>
                        <select
                            id="form-rule-date-field"
                            class="w-full px-2.5 py-1.5 text-xs bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 rounded-lg text-zinc-900 dark:text-white"
                        >
                            <option value="expected_close_date">Expected Close Date (expected_close_date)</option>
                            <option value="created_at">Record Creation Date (created_at)</option>
                            <option value="due_date">Due Date (due_date)</option>
                            <option value="delivery_date">Target Delivery Date (delivery_date)</option>
                        </select>
                    </div>
                </div>

                <!-- Criteria Formula -->
                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Criteria Field</label>
                        <input
                            type="text"
                            id="form-rule-crit-field"
                            placeholder="e.g. stage"
                            value="stage"
                            class="w-full px-2.5 py-1.5 text-xs bg-zinc-50 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 rounded-lg text-zinc-900 dark:text-white"
                        >
                    </div>
                    <div>
                        <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Operator</label>
                        <select
                            id="form-rule-crit-op"
                            class="w-full px-2.5 py-1.5 text-xs bg-zinc-50 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 rounded-lg text-zinc-900 dark:text-white"
                        >
                            <option value="equals">equals</option>
                            <option value="not_equals">not equals</option>
                            <option value="contains">contains</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Filter Value</label>
                        <input
                            type="text"
                            id="form-rule-crit-val"
                            placeholder="e.g. Proposal / Quote"
                            value="Proposal / Quote"
                            class="w-full px-2.5 py-1.5 text-xs bg-zinc-50 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 rounded-lg text-zinc-900 dark:text-white"
                        >
                    </div>
                </div>

                <!-- Recipient Type -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Recipient Target</label>
                        <select
                            id="form-rule-recipient-type"
                            class="w-full px-3 py-1.5 text-xs bg-zinc-50 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 rounded-lg text-zinc-900 dark:text-white"
                        >
                            <option value="record_owner" selected>Record Owner</option>
                            <option value="all_users">All Tenant Users</option>
                            <option value="record_email">Record Email Field</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Additional CC Emails</label>
                        <input
                            type="text"
                            id="form-rule-cc"
                            placeholder="alerts@company.com, manager@company.com"
                            class="w-full px-3 py-1.5 text-xs bg-zinc-50 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 rounded-lg text-zinc-900 dark:text-white"
                        >
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                    <button
                        type="button"
                        onclick="closeRuleModal()"
                        class="px-4 py-1.5 text-xs font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-lg transition-colors cursor-pointer"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        id="save-rule-btn"
                        class="px-5 py-1.5 text-xs font-semibold bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg shadow-sm transition-all cursor-pointer"
                    >
                        Save Automation Trigger
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

<!-- Dedicated Email Templates Studio JavaScript Engine -->
<script src="{{ asset('js/email_templates.js') }}?v=1.2"></script>
<script>
    if (typeof initEmailTemplatesStudio === 'function') {
        initEmailTemplatesStudio();
    }
</script>
@endsection
