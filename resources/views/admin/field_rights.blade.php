@extends('layouts.app')

@section('title', 'Field-Level Security (FLS) — DealSync Admin')

@section('content')
<div id="fieldRightsContainer" class="max-w-7xl mx-auto pb-14 space-y-5 animate-fade-in">
    <!-- Live Action Feedback Toast -->
    <div id="flsFeedbackBanner" class="hidden items-center gap-2.5 p-3.5 rounded-xl text-xs font-semibold shadow-xs transition-all">
        <i id="flsFeedbackIcon" data-lucide="check-circle-2" class="w-4 h-4 shrink-0"></i>
        <span id="flsFeedbackText"></span>
    </div>

    <!-- Governance Bar & Profile Header (SettingsView.tsx lines 3218-3255) -->
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-5 shadow-xs transition-colors">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-950/70 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0 border border-indigo-100 dark:border-indigo-900/50">
                        <i data-lucide="shield-check" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h2 class="text-base lg:text-lg font-bold text-zinc-900 dark:text-zinc-100 tracking-tight">
                            Field-Level Security (FLS)
                        </h2>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                            Control field visibility, read-only constraints, and data masking across all standard and custom objects.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3 mt-3.5 flex-wrap">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Configuring Profile:</span>
                        <select
                            id="selectedProfileSelect"
                            onchange="window.handleProfileChange(this.value)"
                            class="text-xs font-semibold py-1.5 px-3 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 min-w-[240px] shadow-2xs"
                        >
                            @foreach($profiles as $p)
                                <option value="{{ $p->id }}" {{ (string)$p->id === (string)$selectedProfileId ? 'selected' : '' }}>
                                    {{ $p->name }} {{ $p->is_admin ? '★' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div id="profileGovernanceBadge" class="inline-flex items-center">
                        @if($selectedProfile && $selectedProfile->is_admin)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                ★ System Administrator (Full Access)
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                Profile Scoped
                            </span>
                        @endif
                    </div>

                    <button
                        type="button"
                        onclick="window.handleResetBaseline()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 text-xs font-semibold hover:bg-zinc-50 dark:hover:bg-zinc-700/60 shadow-2xs transition"
                        title="Reset this profile's field security to system baseline defaults"
                    >
                        <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                        <span>Reset Baseline</span>
                    </button>
                </div>
            </div>

            <!-- Header Action Toolbar -->
            <div class="flex items-center gap-2">
                <button
                    type="button"
                    id="saveFlsBtn"
                    onclick="window.handleSavePermissions()"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-xs hover:shadow transition"
                >
                    <i id="saveFlsIcon" data-lucide="save" class="w-3.5 h-3.5"></i>
                    <span id="saveFlsText">Save Field Security</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Filter & Selector Toolbar Card (SettingsView.tsx lines 3257-3396) -->
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-4 shadow-xs space-y-3.5 transition-colors">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5 items-end">
            <!-- Object / Module Selector Dropdown (React line 3261) -->
            <div>
                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">
                    🎯 Object / Module Selector:
                </label>
                <select
                    id="moduleFilterSelect"
                    onchange="window.handleModuleChange(this.value)"
                    class="w-full text-xs font-medium py-2 px-3 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-2xs"
                >
                    <option value="all">✨ All Objects &amp; Modules</option>
                    @foreach($modules as $modKey => $modLabel)
                        <option value="{{ $modKey }}">📁 {{ $modLabel }}</option>
                    @endforeach
                    @foreach($customObjects as $co)
                        <option value="obj_{{ $co->api_name }}">🧩 {{ $co->label }} (Custom Object)</option>
                    @endforeach
                </select>
            </div>

            <!-- Search Box (React line 3278) -->
            <div>
                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">
                    🔍 Search Fields:
                </label>
                <div class="relative">
                    <i data-lucide="search" class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400 pointer-events-none"></i>
                    <input
                        type="text"
                        id="fieldSearchInput"
                        placeholder="Search field label, API key, type..."
                        oninput="window.handleSearchChange(this.value)"
                        class="w-full text-xs font-medium pl-8 pr-8 py-2 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-2xs"
                    />
                    <button
                        type="button"
                        id="clearSearchBtn"
                        onclick="window.handleClearSearch()"
                        class="hidden absolute right-2.5 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200"
                    >
                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                    </button>
                </div>
            </div>

            <!-- Bulk Action (Visible Fields) (React line 3305) -->
            <div>
                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">
                    ⚡ Bulk Action (Visible Fields):
                </label>
                <div class="flex items-center gap-1.5">
                    <button
                        type="button"
                        onclick="window.handleBulkSetFieldPerms('Read/Write')"
                        class="flex-1 py-1.5 px-2.5 text-xs font-semibold rounded-lg border border-emerald-200 dark:border-emerald-800/80 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/50 dark:hover:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 transition shadow-2xs text-center"
                        title="Set all currently visible fields to Read/Write"
                    >
                        ✓ All Read/Write
                    </button>
                    <button
                        type="button"
                        onclick="window.handleBulkSetFieldPerms('Read-Only')"
                        class="flex-1 py-1.5 px-2.5 text-xs font-semibold rounded-lg border border-amber-200 dark:border-amber-800/80 bg-amber-50 hover:bg-amber-100 dark:bg-amber-950/50 dark:hover:bg-amber-900/60 text-amber-700 dark:text-amber-300 transition shadow-2xs text-center"
                        title="Set all currently visible fields to Read-Only"
                    >
                        🔒 All Read-Only
                    </button>
                    <button
                        type="button"
                        onclick="window.handleBulkSetFieldPerms('Hidden')"
                        class="flex-1 py-1.5 px-2.5 text-xs font-semibold rounded-lg border border-red-200 dark:border-red-800/80 bg-red-50 hover:bg-red-100 dark:bg-red-950/50 dark:hover:bg-red-900/60 text-red-700 dark:text-red-300 transition shadow-2xs text-center"
                        title="Set all currently visible fields to Hidden"
                    >
                        👁️ All Hidden
                    </button>
                </div>
            </div>
        </div>

        <!-- Filter View Pills & Metrics Strip (React lines 3342-3395) -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-3 border-t border-zinc-100 dark:border-zinc-800">
            <!-- Filter Pills -->
            <div class="flex items-center gap-2 flex-wrap">
                <span class="text-[11px] font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Filter View:</span>
                <button
                    type="button"
                    id="filterPill_all"
                    onclick="window.setQuickFilter('all')"
                    class="px-3 py-1 text-xs font-semibold rounded-full bg-indigo-600 text-white shadow-xs transition"
                >
                    All Fields
                </button>
                <button
                    type="button"
                    id="filterPill_sensitive"
                    onclick="window.setQuickFilter('sensitive')"
                    class="px-3 py-1 text-xs font-medium rounded-full border border-amber-300 dark:border-amber-800/80 bg-amber-50/60 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 hover:bg-amber-100/60 transition"
                >
                    ⚠️ Sensitive Only
                </button>
                <button
                    type="button"
                    id="filterPill_custom"
                    onclick="window.setQuickFilter('custom')"
                    class="px-3 py-1 text-xs font-medium rounded-full border border-indigo-200 dark:border-indigo-800/80 bg-indigo-50/60 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 hover:bg-indigo-100/60 transition"
                >
                    🧩 Custom Fields Only
                </button>
                <button
                    type="button"
                    id="filterPill_restricted"
                    onclick="window.setQuickFilter('restricted')"
                    class="px-3 py-1 text-xs font-medium rounded-full border border-red-200 dark:border-red-800/80 bg-red-50/60 dark:bg-red-950/40 text-red-700 dark:text-red-300 hover:bg-red-100/60 transition"
                >
                    🔒 Restricted (Read-Only / Hidden)
                </button>
            </div>

            <!-- Counts Badges Strip (React lines 3381-3394) -->
            <div class="flex items-center gap-2 flex-wrap text-xs">
                <span class="text-zinc-500 dark:text-zinc-400 font-medium">
                    Showing <strong id="totalCountBadge" class="text-zinc-900 dark:text-zinc-100">{{ $counts['total'] }}</strong> fields
                </span>
                <span id="rwCountBadge" class="px-2 py-0.5 rounded-md font-semibold text-[11px] bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                    {{ $counts['rw'] }} Read/Write
                </span>
                <span id="roCountBadge" class="px-2 py-0.5 rounded-md font-semibold text-[11px] bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                    {{ $counts['ro'] }} Read-Only
                </span>
                <span id="hdCountBadge" class="px-2 py-0.5 rounded-md font-semibold text-[11px] bg-red-50 dark:bg-red-950/50 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-800">
                    {{ $counts['hd'] }} Hidden
                </span>
            </div>
        </div>
    </div>

    <!-- Table Card (SettingsView.tsx lines 3399-3541) -->
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl shadow-xs overflow-hidden transition-colors">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-zinc-50 dark:bg-zinc-800/80 border-b border-zinc-200 dark:border-zinc-800 text-[11px] font-semibold text-zinc-600 dark:text-zinc-300 uppercase tracking-wider">
                        <th class="py-3 px-4 w-44">Object / Module</th>
                        <th class="py-3 px-4">Field Label &amp; Key</th>
                        <th class="py-3 px-4 w-40">Type &amp; Scope</th>
                        <th class="py-3 px-4 w-64">Access Permission Policy</th>
                    </tr>
                </thead>
                <tbody id="flsTableBody" class="divide-y divide-zinc-200/80 dark:divide-zinc-800/80">
                    @forelse($initialFieldRightsList as $item)
                        @php
                            $isRw = $item['permission'] === 'Read/Write';
                            $isRo = $item['permission'] === 'Read-Only';
                            $isHd = $item['permission'] === 'Hidden';
                            $bgClass = $isHd ? 'bg-red-50/30 dark:bg-red-950/20' : ($isRo ? 'bg-amber-50/30 dark:bg-amber-950/20' : '');
                        @endphp
                        <tr
                            id="row-{{ $item['mod'] }}-{{ $item['field'] }}"
                            data-mod="{{ $item['mod'] }}"
                            data-field="{{ $item['field'] }}"
                            data-label="{{ $item['label'] }}"
                            data-key="{{ $item['mod'] }}.{{ $item['field'] }}"
                            data-type="{{ $item['dataType'] }}"
                            data-custom="{{ $item['isCustom'] ? '1' : '0' }}"
                            data-sensitive="{{ $item['isSensitive'] ? '1' : '0' }}"
                            data-permission="{{ $item['permission'] }}"
                            class="border-b border-zinc-200/80 dark:border-zinc-800/80 hover:bg-zinc-50/70 dark:hover:bg-zinc-800/40 transition-colors {{ $bgClass }}"
                        >
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-semibold bg-zinc-100 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 border border-zinc-200 dark:border-zinc-700">
                                    {{ $item['modLabel'] }}
                                </span>
                            </td>

                            <td class="py-3 px-4">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <strong class="text-xs font-semibold text-zinc-900 dark:text-zinc-100">
                                        {{ $item['label'] }}
                                    </strong>
                                    @if($item['isSensitive'])
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-50 dark:bg-red-950/60 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-800">
                                            ⚠️ Sensitive
                                        </span>
                                    @endif
                                </div>
                                <div class="text-[11px] font-mono text-zinc-400 dark:text-zinc-500 mt-0.5">
                                    {{ $item['field'] }}
                                </div>
                                @if(!empty($item['description']))
                                    <div class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5">
                                        {{ $item['description'] }}
                                    </div>
                                @endif
                            </td>

                            <td class="py-3 px-4 whitespace-nowrap">
                                <div class="flex flex-col gap-1">
                                    <div>
                                        @if($item['isCustom'])
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-indigo-50 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                                                Custom Field
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700">
                                                Standard Field
                                            </span>
                                        @endif
                                    </div>
                                    <span class="text-[11px] font-medium text-zinc-500 dark:text-zinc-400">
                                        {{ $item['dataType'] }}
                                    </span>
                                </div>
                            </td>

                            <td class="py-3 px-4">
                                <div class="flex items-center gap-2.5">
                                    <div class="flex-1">
                                        <select
                                            onchange="window.updateFieldPerm('{{ $item['mod'] }}', '{{ $item['field'] }}', this.value)"
                                            class="fls-perm-select w-full text-xs font-semibold py-1.5 px-3 rounded-lg border focus:outline-none focus:ring-2 transition shadow-2xs {{
                                                $isRw
                                                    ? 'border-emerald-300 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 focus:ring-emerald-500/20'
                                                    : ($isRo
                                                        ? 'border-amber-300 dark:border-amber-800 bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300 focus:ring-amber-500/20'
                                                        : 'border-red-300 dark:border-red-800 bg-red-50 dark:bg-red-950/50 text-red-700 dark:text-red-300 focus:ring-red-500/20')
                                            }}"
                                        >
                                            <option value="Read/Write" {{ $isRw ? 'selected' : '' }}>Read/Write (Full Access)</option>
                                            <option value="Read-Only" {{ $isRo ? 'selected' : '' }}>Read-Only (Cannot Edit)</option>
                                            <option value="Hidden" {{ $isHd ? 'selected' : '' }}>Hidden (Masked / Invisible)</option>
                                        </select>
                                    </div>
                                    <div id="icon-{{ $item['mod'] }}-{{ $item['field'] }}" class="w-5 flex items-center justify-center shrink-0">
                                        @if($isRw)
                                            <span title="Full Read &amp; Write Access"><i data-lucide="check" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i></span>
                                        @elseif($isRo)
                                            <span title="Field is Read-Only for this profile"><i data-lucide="lock" class="w-4 h-4 text-amber-600 dark:text-amber-400"></i></span>
                                        @else
                                            <span title="Field is completely Hidden for this profile"><i data-lucide="eye-off" class="w-4 h-4 text-red-600 dark:text-red-400"></i></span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr id="flsEmptyRow">
                            <td colspan="4" class="py-14 text-center text-zinc-400 dark:text-zinc-500">
                                <i data-lucide="filter" class="w-8 h-8 mx-auto mb-2 opacity-40"></i>
                                <p class="font-semibold text-sm text-zinc-700 dark:text-zinc-300">No fields found matching criteria</p>
                                <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-1">Try selecting another object in the dropdown above or clearing your search query.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Global Hydration Payload & Dedicated External Script -->
<script>
    window.INITIAL_FIELD_RIGHTS_DATA = {
        profiles: @json($profiles ?? []),
        selectedProfileId: '{{ $selectedProfileId ?? "" }}',
        customObjects: @json($customObjects ?? []),
        customFields: @json($customFields ?? []),
        standardFieldsConfig: @json(\App\Http\Controllers\AdminController::$STANDARD_FIELDS ?? []),
        sensitiveFields: @json(\App\Http\Controllers\AdminController::$SENSITIVE_FIELDS ?? []),
        modulesMap: @json($modules ?? []),
        existingFieldPerms: @json($existingFieldPerms ?? []),
        csrfToken: '{{ csrf_token() }}'
    };
</script>
<script src="{{ asset('js/field-rights.js') }}?v=1.1"></script>
@endsection
