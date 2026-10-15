@extends('layouts.app')

@section('title', 'Field-Level Security (FLS) — DealSync Admin')

@section('content')
<style>
/* ========================================================================= */
/* FIELD-LEVEL SECURITY (FLS) STUDIO STYLES (REACT PARITY)                   */
/* ========================================================================= */
.fls-studio-container {
    --text-primary: var(--zinc-900, #0f172a);
    --text-secondary: var(--zinc-600, #475569);
    --text-tertiary: var(--zinc-500, #64748b);
    --text-muted: var(--zinc-400, #94a3b8);
    max-width: 1240px;
    margin: 0 auto;
    padding-bottom: 3.5rem;
    color: var(--zinc-900, #0f172a);
}

.fls-studio-container * {
    box-sizing: border-box;
}

.form-input, .form-control {
    width: 100%;
    box-sizing: border-box;
    height: 38px;
    background-color: var(--zinc-50, #f8fafc);
    border: 1px solid var(--zinc-200, #e2e8f0);
    border-radius: var(--radius-md, 8px);
    color: var(--zinc-950, #09090b);
    padding: 0.45rem 0.85rem;
    font-size: 0.8125rem;
    font-family: inherit;
    line-height: 1.4;
    outline: none;
    transition: border-color 0.15s ease, background-color 0.15s ease, box-shadow 0.15s ease;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.02);
}

.form-input::placeholder, .form-control::placeholder {
    color: var(--zinc-400, #94a3b8);
    font-size: 0.8125rem;
}

.form-input:hover, .form-control:hover {
    border-color: var(--zinc-300, #cbd5e1);
    background-color: #ffffff;
}

.form-input:focus, .form-control:focus,
.form-input:focus-visible, .form-control:focus-visible {
    background-color: #ffffff !important;
    border-color: var(--color-primary, #4f46e5) !important;
    box-shadow: 0 0 0 3px var(--color-primary-subtle, rgba(79, 70, 229, 0.15)) !important;
    color: var(--zinc-950, #09090b) !important;
}

.form-select {
    width: 100%;
    box-sizing: border-box;
    height: 38px;
    background-color: var(--zinc-50, #f8fafc);
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 0.75rem center;
    background-repeat: no-repeat;
    background-size: 1.15em 1.15em;
    padding: 0.45rem 2.25rem 0.45rem 0.85rem;
    border: 1px solid var(--zinc-200, #e2e8f0);
    border-radius: var(--radius-md, 8px);
    color: var(--zinc-950, #09090b);
    font-size: 0.8125rem;
    font-family: inherit;
    line-height: 1.4;
    outline: none;
    cursor: pointer;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    transition: border-color 0.15s ease, background-color 0.15s ease, box-shadow 0.15s ease;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.02);
}

.form-select:hover {
    border-color: var(--zinc-300, #cbd5e1);
    background-color: #ffffff;
}

.form-select:focus,
.form-select:focus-visible {
    background-color: #ffffff !important;
    border-color: var(--color-primary, #4f46e5) !important;
    box-shadow: 0 0 0 3px var(--color-primary-subtle, rgba(79, 70, 229, 0.15)) !important;
    color: var(--zinc-950, #09090b) !important;
}

.form-select option {
    background: #ffffff;
    color: var(--zinc-900, #09090b);
    padding: 0.5rem 0.75rem;
    font-size: 0.8125rem;
}

/* Specific styling for table inline permission dropdown */
.fls-perm-select {
    height: 32px !important;
    padding: 0.25rem 1.85rem 0.25rem 0.65rem !important;
    font-size: 0.75rem !important;
    font-weight: 600 !important;
    background-size: 1em 1em !important;
    background-position: right 0.5rem center !important;
    border-radius: 6px !important;
    transition: all 0.15s ease;
}

.fls-perm-select.perm-rw {
    border-color: #a7f3d0 !important;
    background-color: #f0fdf4 !important;
    color: #15803d !important;
}

.fls-perm-select.perm-ro {
    border-color: #fde68a !important;
    background-color: #fefce8 !important;
    color: #b45309 !important;
}

.fls-perm-select.perm-hd {
    border-color: #fecaca !important;
    background-color: #fef2f2 !important;
    color: #b91c1c !important;
}

.data-table th {
    background: var(--zinc-100, #f1f5f9);
    color: var(--zinc-700, #334155);
    font-weight: 600;
    font-size: 0.75rem;
    letter-spacing: 0.02em;
    padding: 0.65rem 0.85rem;
    border-bottom: 1px solid var(--zinc-200, #e2e8f0);
    white-space: nowrap;
}

.data-table td {
    padding: 0.75rem 0.85rem;
    border-bottom: 1px solid var(--zinc-200, #e2e8f0);
    font-size: 0.8125rem;
    color: var(--zinc-900, #0f172a);
    vertical-align: middle;
}

.data-table tbody tr {
    transition: background-color 0.15s ease;
}

.data-table tbody tr:hover td {
    background-color: var(--zinc-50, #f8fafc);
}

.data-table tbody tr.row-hidden td {
    background-color: rgba(254, 242, 242, 0.4);
}

.data-table tbody tr.row-readonly td {
    background-color: rgba(255, 251, 235, 0.4);
}

.data-table tbody tr:last-child td {
    border-bottom: none;
}

.btn-filter-pill {
    font-size: 0.72rem;
    padding: 0.25rem 0.65rem;
    border-radius: 9999px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s ease;
    border: 1px solid transparent;
}

.btn-filter-pill.active {
    background-color: var(--color-primary, #4f46e5);
    color: #ffffff;
    box-shadow: 0 1px 2px rgba(79, 70, 229, 0.2);
}

.btn-filter-pill:not(.active) {
    background-color: transparent;
}

.btn-filter-pill.pill-all:not(.active) {
    color: var(--zinc-600, #475569);
    border-color: var(--zinc-200, #e2e8f0);
}

.btn-filter-pill.pill-all:not(.active):hover {
    background-color: var(--zinc-100, #f1f5f9);
}

.btn-filter-pill.pill-sensitive:not(.active) {
    color: #d97706;
    border-color: rgba(245, 158, 11, 0.3);
    background-color: rgba(254, 243, 199, 0.2);
}

.btn-filter-pill.pill-sensitive:not(.active):hover {
    background-color: rgba(254, 243, 199, 0.6);
}

.btn-filter-pill.pill-custom:not(.active) {
    color: #4f46e5;
    border-color: rgba(99, 102, 241, 0.3);
    background-color: rgba(238, 242, 255, 0.2);
}

.btn-filter-pill.pill-custom:not(.active):hover {
    background-color: rgba(238, 242, 255, 0.6);
}

.btn-filter-pill.pill-restricted:not(.active) {
    color: #dc2626;
    border-color: rgba(239, 68, 68, 0.3);
    background-color: rgba(254, 242, 242, 0.2);
}

.btn-filter-pill.pill-restricted:not(.active):hover {
    background-color: rgba(254, 242, 242, 0.6);
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(4px); }
    to { opacity: 1; transform: translateY(0); }
}

.animate-fade-in {
    animation: fadeIn 0.15s ease-out forwards;
}
</style>

<div class="fls-studio-container animate-fade-in">
    <!-- Feedback Banner -->
    <div id="feedbackBanner" style="display: none; padding: 0.85rem 1.25rem; border-radius: var(--radius-lg, 12px); margin-bottom: 1.25rem; align-items: center; gap: 0.6rem; font-size: 0.8125rem; font-weight: 500;">
        <i id="feedbackIcon" data-lucide="check-circle-2" style="width: 16px; height: 16px; flex-shrink: 0;"></i>
        <span id="feedbackText"></span>
    </div>

    <!-- TAB 4: FIELD-LEVEL RIGHTS (SettingsView.tsx lines 2558-2886) -->
    <div style="display: flex; flex-direction: column; gap: 1rem;">
        <!-- Header Bar -->
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; margin-bottom: 0.75rem;">
            <div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <i data-lucide="shield-check" style="width: 22px; height: 22px; color: var(--color-primary, #4f46e5);"></i>
                    <h2 style="font-size: 1.15rem; font-weight: 700; color: var(--zinc-950, #09090b); letter-spacing: -0.02em; margin: 0;">
                        Field-Level Security (FLS)
                    </h2>
                    @if($selectedProfile && $selectedProfile->is_admin)
                        <span style="font-size: 0.68rem; font-weight: 700; background: #ecfdf5; color: #047857; border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 9999px; padding: 0.15rem 0.5rem;">
                            ★ System Administrator
                        </span>
                    @else
                        <span style="font-size: 0.68rem; font-weight: 700; background: #eff6ff; color: #1d4ed8; border: 1px solid rgba(59, 130, 246, 0.3); border-radius: 9999px; padding: 0.15rem 0.5rem;">
                            Profile Scoped
                        </span>
                    @endif
                </div>
                <p style="font-size: 0.8125rem; color: var(--zinc-500, #64748b); margin: 0.25rem 0 0.5rem 0;">
                    Control field visibility, read-only constraints, and data masking across all standard and custom objects.
                </p>
                <div style="display: flex; align-items: center; gap: 0.65rem; flex-wrap: wrap;">
                    <span style="font-size: 0.75rem; font-weight: 600; color: var(--zinc-700, #334155);">Configuring Profile:</span>
                    <select
                        id="selectedProfileSelect"
                        class="form-select"
                        onchange="handleProfileChange(this.value)"
                        style="width: 260px; font-weight: 600; font-size: 0.8125rem;"
                    >
                        @foreach($profiles as $p)
                            <option value="{{ $p->id }}" {{ (string)$p->id === (string)$selectedProfileId ? 'selected' : '' }}>
                                {{ $p->name }} {{ $p->is_admin ? '★' : '' }}
                            </option>
                        @endforeach
                    </select>

                    <button
                        type="button"
                        onclick="handleResetBaseline()"
                        class="btn btn-ghost btn-sm"
                        style="font-size: 0.72rem; padding: 0.35rem 0.6rem; color: var(--zinc-600); border: 1px solid var(--zinc-200);"
                        title="Reset this profile's field security to system baseline"
                    >
                        <i data-lucide="rotate-ccw" style="width: 12px; height: 12px; margin-right: 0.25rem;"></i>
                        Reset Baseline
                    </button>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <button
                    type="button"
                    id="saveFlsBtn"
                    onclick="handleSavePermissions()"
                    class="btn btn-primary"
                    style="display: inline-flex; align-items: center; gap: 0.45rem; padding: 0.5rem 1.15rem; font-weight: 600; border-radius: 8px; box-shadow: 0 1px 3px rgba(79, 70, 229, 0.3);"
                >
                    <i id="saveFlsIcon" data-lucide="save" style="width: 15px; height: 15px;"></i>
                    <span id="saveFlsText">Save Field Security</span>
                </button>
            </div>
        </div>

        <!-- Filter & Selector Toolbar Card -->
        <div class="card" style="padding: 1rem; background: var(--bg-card, #ffffff); border: 1px solid var(--border-card, #e2e8f0); border-radius: var(--radius-lg, 12px); box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem; align-items: flex-end; margin-bottom: 0.85rem;">
                <!-- Object / Module Selector Dropdown -->
                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--zinc-800, #1e293b); margin-bottom: 0.35rem;">
                        🎯 Object / Module Selector:
                    </label>
                    <select
                        id="moduleFilterSelect"
                        class="form-select"
                        onchange="handleModuleChange(this.value)"
                        style="width: 100%; font-weight: 500; font-size: 0.8125rem;"
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

                <!-- Search Box -->
                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--zinc-800, #1e293b); margin-bottom: 0.35rem;">
                        🔍 Search Fields:
                    </label>
                    <div style="position: relative;">
                        <i data-lucide="search" style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: var(--zinc-400); width: 15px; height: 15px; pointer-events: none;"></i>
                        <input
                            type="text"
                            id="fieldSearchInput"
                            class="form-input"
                            placeholder="Search field label, API key, type..."
                            oninput="handleSearchChange(this.value)"
                            style="padding-left: 2.25rem; padding-right: 2.25rem; font-size: 0.8125rem; width: 100%;"
                        >
                        <button
                            type="button"
                            id="clearSearchBtn"
                            onclick="handleClearSearch()"
                            style="position: absolute; right: 0.65rem; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--zinc-400); display: none; padding: 0.2rem; align-items: center; justify-content: center;"
                        >
                            <i data-lucide="x" style="width: 14px; height: 14px;"></i>
                        </button>
                    </div>
                </div>

                <!-- Batch Actions -->
                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--zinc-800, #1e293b); margin-bottom: 0.35rem;">
                        ⚡ Bulk Action (Visible Fields):
                    </label>
                    <div style="display: flex; gap: 0.35rem; flex-wrap: wrap;">
                        <button
                            type="button"
                            onclick="handleBulkSetFieldPerms('Read/Write')"
                            class="btn btn-ghost btn-sm"
                            style="font-size: 0.72rem; padding: 0.35rem 0.65rem; border: 1px solid rgba(16, 185, 129, 0.3); color: #047857; background: #ecfdf5; font-weight: 600;"
                            title="Set all currently visible fields to Read/Write"
                        >
                            ✓ All Read/Write
                        </button>
                        <button
                            type="button"
                            onclick="handleBulkSetFieldPerms('Read-Only')"
                            class="btn btn-ghost btn-sm"
                            style="font-size: 0.72rem; padding: 0.35rem 0.65rem; border: 1px solid rgba(245, 158, 11, 0.3); color: #b45309; background: #fffbeb; font-weight: 600;"
                            title="Set all currently visible fields to Read-Only"
                        >
                            🔒 All Read-Only
                        </button>
                        <button
                            type="button"
                            onclick="handleBulkSetFieldPerms('Hidden')"
                            class="btn btn-ghost btn-sm"
                            style="font-size: 0.72rem; padding: 0.35rem 0.65rem; border: 1px solid rgba(239, 68, 68, 0.3); color: #b91c1c; background: #fef2f2; font-weight: 600;"
                            title="Set all currently visible fields to Hidden"
                        >
                            👁️ All Hidden
                        </button>
                    </div>
                </div>
            </div>

            <!-- Quick Filter Pills & Metrics Strip -->
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem; padding-top: 0.75rem; border-top: 1px solid var(--border-card, #e2e8f0);">
                <!-- Filter Pills -->
                <div style="display: flex; align-items: center; gap: 0.4rem; flex-wrap: wrap;">
                    <span style="font-size: 0.72rem; color: var(--zinc-600, #475569); font-weight: 600; margin-right: 0.2rem;">Filter View:</span>
                    <button
                        type="button"
                        id="filterPillAll"
                        onclick="setQuickFilter('all')"
                        class="btn-filter-pill pill-all active"
                    >
                        All Fields
                    </button>
                    <button
                        type="button"
                        id="filterPillSensitive"
                        onclick="setQuickFilter('sensitive')"
                        class="btn-filter-pill pill-sensitive"
                    >
                        ⚠️ Sensitive Only
                    </button>
                    <button
                        type="button"
                        id="filterPillCustom"
                        onclick="setQuickFilter('custom')"
                        class="btn-filter-pill pill-custom"
                    >
                        🧩 Custom Fields Only
                    </button>
                    <button
                        type="button"
                        id="filterPillRestricted"
                        onclick="setQuickFilter('restricted')"
                        class="btn-filter-pill pill-restricted"
                    >
                        🔒 Restricted (Read-Only / Hidden)
                    </button>
                </div>

                <!-- Counts Badge -->
                <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.75rem; flex-wrap: wrap;">
                    <span style="color: var(--zinc-600, #475569); font-weight: 500;">
                        Showing <strong id="totalCountBadge" style="color: var(--zinc-950, #09090b); font-weight: 700;">{{ $counts['total'] }}</strong> fields
                    </span>
                    <span id="rwCountBadge" style="background: #ecfdf5; color: #047857; padding: 0.15rem 0.5rem; border-radius: 4px; font-weight: 600; font-size: 0.7rem; border: 1px solid rgba(16, 185, 129, 0.25);">
                        {{ $counts['rw'] }} Read/Write
                    </span>
                    <span id="roCountBadge" style="background: #fffbeb; color: #b45309; padding: 0.15rem 0.5rem; border-radius: 4px; font-weight: 600; font-size: 0.7rem; border: 1px solid rgba(245, 158, 11, 0.25);">
                        {{ $counts['ro'] }} Read-Only
                    </span>
                    <span id="hdCountBadge" style="background: #fef2f2; color: #b91c1c; padding: 0.15rem 0.5rem; border-radius: 4px; font-weight: 600; font-size: 0.7rem; border: 1px solid rgba(239, 68, 68, 0.25);">
                        {{ $counts['hd'] }} Hidden
                    </span>
                </div>
            </div>
        </div>

        <!-- Table Card -->
        <div class="card" style="padding: 0; overflow: hidden; border: 1px solid var(--border-card, #e2e8f0); border-radius: var(--radius-lg, 12px); box-shadow: 0 1px 3px rgba(0,0,0,0.02); background: #ffffff;">
            <div style="overflow-x: auto;">
                <table class="data-table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th style="width: 180px;">Object / Module</th>
                            <th>Field Label &amp; Key</th>
                            <th style="width: 170px;">Type &amp; Scope</th>
                            <th style="width: 250px;">Access Permission Policy</th>
                        </tr>
                    </thead>
                    <tbody id="flsTableBody">
                        @forelse($initialFieldRightsList as $item)
                            @php
                                $isRw = $item['permission'] === 'Read/Write';
                                $isRo = $item['permission'] === 'Read-Only';
                                $isHd = $item['permission'] === 'Hidden';
                                $rowClass = $isHd ? 'row-hidden' : ($isRo ? 'row-readonly' : '');
                                $permClass = $isRw ? 'perm-rw' : ($isRo ? 'perm-ro' : 'perm-hd');
                            @endphp
                            <tr
                                id="row-{{ $item['mod'] }}-{{ $item['field'] }}"
                                class="{{ $rowClass }}"
                                data-mod="{{ $item['mod'] }}"
                                data-field="{{ $item['field'] }}"
                                data-label="{{ strtolower($item['label']) }}"
                                data-key="{{ strtolower($item['field']) }}"
                                data-type="{{ strtolower($item['dataType']) }}"
                                data-custom="{{ $item['isCustom'] ? '1' : '0' }}"
                                data-sensitive="{{ $item['isSensitive'] ? '1' : '0' }}"
                                data-permission="{{ $item['permission'] }}"
                            >
                                <td>
                                    <span style="
                                        font-size: 0.72rem;
                                        font-weight: 600;
                                        text-transform: capitalize;
                                        padding: 0.2rem 0.5rem;
                                        border-radius: 4px;
                                        background: var(--zinc-100, #f1f5f9);
                                        color: var(--zinc-800, #1e293b);
                                        border: 1px solid var(--zinc-200, #e2e8f0);
                                        display: inline-block;
                                    ">
                                        {{ $item['modLabel'] }}
                                    </span>
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                                        <span style="color: var(--zinc-950, #09090b); font-weight: 600; font-size: 0.8125rem;">
                                            {{ $item['label'] }}
                                        </span>
                                        @if($item['isSensitive'])
                                            <span style="
                                                font-size: 0.65rem;
                                                font-weight: 700;
                                                background: #fef2f2;
                                                color: #b91c1c;
                                                border: 1px solid #fecaca;
                                                border-radius: 9999px;
                                                padding: 0.1rem 0.45rem;
                                                display: inline-flex;
                                                align-items: center;
                                                gap: 0.2rem;
                                            ">
                                                ⚠️ Sensitive
                                            </span>
                                        @endif
                                    </div>
                                    <div style="font-size: 0.7rem; color: var(--zinc-400, #94a3b8); font-family: monospace; margin-top: 0.15rem;">
                                        {{ $item['field'] }}
                                    </div>
                                    @if(!empty($item['description']))
                                        <div style="font-size: 0.68rem; color: var(--zinc-500, #64748b); margin-top: 0.15rem;">
                                            {{ $item['description'] }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                                        <div>
                                            <span style="
                                                font-size: 0.68rem;
                                                font-weight: 600;
                                                padding: 0.15rem 0.4rem;
                                                border-radius: 4px;
                                                background: {{ $item['isCustom'] ? '#e0e7ff' : '#f4f4f5' }};
                                                color: {{ $item['isCustom'] ? '#4338ca' : '#52525b' }};
                                                border: 1px solid {{ $item['isCustom'] ? '#c7d2fe' : '#e4e4e7' }};
                                                display: inline-block;
                                            ">
                                                {{ $item['isCustom'] ? 'Custom Field' : 'Standard Field' }}
                                            </span>
                                        </div>
                                        <span style="font-size: 0.7rem; color: var(--zinc-500, #64748b); font-weight: 500;">
                                            {{ $item['dataType'] }}
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <div style="flex: 1;">
                                            <select
                                                class="form-select fls-perm-select {{ $permClass }}"
                                                onchange="updateFieldPerm('{{ $item['mod'] }}', '{{ $item['field'] }}', this.value)"
                                            >
                                                <option value="Read/Write" {{ $isRw ? 'selected' : '' }}>Read / Write (Full Access)</option>
                                                <option value="Read-Only" {{ $isRo ? 'selected' : '' }}>Read-Only (Cannot Edit)</option>
                                                <option value="Hidden" {{ $isHd ? 'selected' : '' }}>Hidden (Masked / Invisible)</option>
                                            </select>
                                        </div>
                                        <div id="icon-{{ $item['mod'] }}-{{ $item['field'] }}" style="width: 22px; display: flex; justify-content: center;">
                                            @if($isRw)
                                                <span title="Full Read &amp; Write Access"><i data-lucide="check" style="width: 16px; height: 16px; color: #16a34a;"></i></span>
                                            @elseif($isRo)
                                                <span title="Field is Read-Only for this profile"><i data-lucide="lock" style="width: 15px; height: 15px; color: #d97706;"></i></span>
                                            @else
                                                <span title="Field is completely Hidden for this profile"><i data-lucide="eye-off" style="width: 15px; height: 15px; color: #dc2626;"></i></span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 3rem; color: var(--zinc-500);">
                                    <div style="display: inline-flex; flex-direction: column; align-items: center; gap: 0.5rem;">
                                        <i data-lucide="filter" style="width: 28px; height: 28px; opacity: 0.4; margin: 0 auto;"></i>
                                        <div style="font-weight: 600; font-size: 0.9rem; color: var(--zinc-800); margin-top: 0.35rem;">No fields found matching criteria</div>
                                        <div style="font-size: 0.75rem; color: var(--zinc-500);">
                                            Try selecting another object in the dropdown above or clearing your search query.
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// ============================================================================
// FIELD-LEVEL SECURITY (FLS) STUDIO STATE ENGINE (1-TO-1 REACT PARITY)
// ============================================================================
window.CSRF_TOKEN = '{{ csrf_token() }}';
window.INITIAL_PROFILES = @json($profiles);
window.INITIAL_CUSTOM_OBJECTS = @json($customObjects);
window.INITIAL_CUSTOM_FIELDS = @json($customFields);
window.INITIAL_FIELD_PERMISSIONS = @json($existingFieldPerms);
window.SELECTED_PROFILE_ID = '{{ $selectedProfileId }}';
window.STANDARD_FIELDS_CONFIG = @json(\App\Http\Controllers\AdminController::$STANDARD_FIELDS);
window.SENSITIVE_FIELDS_LIST = @json(\App\Http\Controllers\AdminController::$SENSITIVE_FIELDS);
window.INITIAL_MODULES_MAP = @json($modules);

let selectedProfileId = String(window.SELECTED_PROFILE_ID || '1');
let fieldRightsModule = 'all';
let fieldRightsSearch = '';
let fieldRightsFilter = 'all';

// Real database-backed in-memory state
let fieldPerms = Array.isArray(window.INITIAL_FIELD_PERMISSIONS) ? [...window.INITIAL_FIELD_PERMISSIONS] : [];
let customObjectsList = Array.isArray(window.INITIAL_CUSTOM_OBJECTS) ? [...window.INITIAL_CUSTOM_OBJECTS] : [];
let allCustomFields = Array.isArray(window.INITIAL_CUSTOM_FIELDS) ? [...window.INITIAL_CUSTOM_FIELDS] : [];
let profiles = Array.isArray(window.INITIAL_PROFILES) ? [...window.INITIAL_PROFILES] : [];

const SENSITIVE_FIELDS_SET = new Set(window.SENSITIVE_FIELDS_LIST || [
    'opportunities.amount',
    'opportunities.probability',
    'purchase_deals.amount',
    'accounts.credit_limit',
    'accounts.annual_revenue',
    'contacts.phone',
    'wires.amount',
    'users.email',
    'leads.email',
    'payments.amount'
]);

function handleProfileChange(newProfileId) {
    if (!newProfileId || newProfileId === selectedProfileId) return;
    window.location.href = `/admin/field-rights?profile_id=${encodeURIComponent(newProfileId)}`;
}

function handleModuleChange(newModule) {
    fieldRightsModule = newModule;
    applyFiltersToTable();
}

function handleSearchChange(query) {
    fieldRightsSearch = (query || '').toLowerCase().trim();
    const clearBtn = document.getElementById('clearSearchBtn');
    if (clearBtn) {
        clearBtn.style.display = fieldRightsSearch ? 'flex' : 'none';
    }
    applyFiltersToTable();
}

function handleClearSearch() {
    fieldRightsSearch = '';
    const input = document.getElementById('fieldSearchInput');
    if (input) input.value = '';
    const clearBtn = document.getElementById('clearSearchBtn');
    if (clearBtn) clearBtn.style.display = 'none';
    applyFiltersToTable();
}

function setQuickFilter(filter) {
    fieldRightsFilter = filter;

    const pills = {
        all: document.getElementById('filterPillAll'),
        sensitive: document.getElementById('filterPillSensitive'),
        custom: document.getElementById('filterPillCustom'),
        restricted: document.getElementById('filterPillRestricted')
    };

    Object.keys(pills).forEach(k => {
        const btn = pills[k];
        if (!btn) return;
        if (k === filter) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });

    applyFiltersToTable();
}

function applyFiltersToTable() {
    const tbody = document.getElementById('flsTableBody');
    if (!tbody) return;

    const rows = tbody.querySelectorAll('tr[data-mod]');
    let visibleCount = 0;
    let rwCount = 0;
    let roCount = 0;
    let hdCount = 0;

    rows.forEach(tr => {
        const mod = tr.getAttribute('data-mod');
        const field = tr.getAttribute('data-field');
        const label = tr.getAttribute('data-label') || '';
        const key = tr.getAttribute('data-key') || '';
        const type = tr.getAttribute('data-type') || '';
        const isCustom = tr.getAttribute('data-custom') === '1';
        const isSensitive = tr.getAttribute('data-sensitive') === '1';
        const perm = tr.getAttribute('data-permission') || 'Read/Write';

        // 1. Module filter
        let matchesModule = (fieldRightsModule === 'all') || (mod === fieldRightsModule);

        // 2. Search query filter
        let matchesSearch = true;
        if (fieldRightsSearch) {
            matchesSearch = label.includes(fieldRightsSearch) ||
                            key.includes(fieldRightsSearch) ||
                            type.includes(fieldRightsSearch) ||
                            mod.toLowerCase().includes(fieldRightsSearch);
        }

        // 3. Quick filter pill
        let matchesPill = true;
        if (fieldRightsFilter === 'sensitive') {
            matchesPill = isSensitive;
        } else if (fieldRightsFilter === 'custom') {
            matchesPill = isCustom;
        } else if (fieldRightsFilter === 'restricted') {
            matchesPill = (perm === 'Read-Only' || perm === 'Hidden');
        }

        if (matchesModule && matchesSearch && matchesPill) {
            tr.style.display = '';
            visibleCount++;
            if (perm === 'Read-Only') {
                roCount++;
            } else if (perm === 'Hidden') {
                hdCount++;
            } else {
                rwCount++;
            }
        } else {
            tr.style.display = 'none';
        }
    });

    // Update KPI Counters
    document.getElementById('totalCountBadge').textContent = visibleCount;
    document.getElementById('rwCountBadge').textContent = `${rwCount} Read/Write`;
    document.getElementById('roCountBadge').textContent = `${roCount} Read-Only`;
    document.getElementById('hdCountBadge').textContent = `${hdCount} Hidden`;

    // Handle empty state row if visibleCount === 0
    let emptyRow = document.getElementById('flsEmptyRow');
    if (visibleCount === 0) {
        if (!emptyRow) {
            emptyRow = document.createElement('tr');
            emptyRow.id = 'flsEmptyRow';
            emptyRow.innerHTML = `
                <td colspan="4" style="text-align: center; padding: 3rem; color: var(--zinc-500);">
                    <div style="display: inline-flex; flex-direction: column; align-items: center; gap: 0.5rem;">
                        <i data-lucide="filter" style="width: 28px; height: 28px; opacity: 0.4; margin: 0 auto;"></i>
                        <div style="font-weight: 600; font-size: 0.9rem; color: var(--zinc-800); margin-top: 0.35rem;">No fields found matching criteria</div>
                        <div style="font-size: 0.75rem; color: var(--zinc-500);">
                            Try selecting another object in the dropdown above or clearing your search query.
                        </div>
                    </div>
                </td>
            `;
            tbody.appendChild(emptyRow);
            if (window.lucide) lucide.createIcons();
        } else {
            emptyRow.style.display = '';
        }
    } else if (emptyRow) {
        emptyRow.style.display = 'none';
    }
}

async function updateFieldPerm(mod, fieldName, newPerm) {
    // 1. Update in-memory array
    const idx = fieldPerms.findIndex(p => p.module === mod && p.field_name === fieldName);
    if (idx >= 0) {
        fieldPerms[idx].permission = newPerm;
    } else {
        fieldPerms.push({
            id: '',
            profile_id: selectedProfileId,
            module: mod,
            field_name: fieldName,
            permission: newPerm
        });
    }

    // 2. Update DOM row classes, data attributes, select style, and status icon
    const tr = document.getElementById(`row-${mod}-${fieldName}`);
    if (tr) {
        tr.setAttribute('data-permission', newPerm);
        tr.className = newPerm === 'Hidden' ? 'row-hidden' : (newPerm === 'Read-Only' ? 'row-readonly' : '');

        const select = tr.querySelector('.fls-perm-select');
        if (select) {
            select.className = 'form-select fls-perm-select ' + (
                newPerm === 'Read/Write' ? 'perm-rw' : (newPerm === 'Read-Only' ? 'perm-ro' : 'perm-hd')
            );
            select.value = newPerm;
        }

        const iconContainer = document.getElementById(`icon-${mod}-${fieldName}`);
        if (iconContainer) {
            if (newPerm === 'Read/Write') {
                iconContainer.innerHTML = '<span title="Full Read &amp; Write Access"><i data-lucide="check" style="width: 16px; height: 16px; color: #16a34a;"></i></span>';
            } else if (newPerm === 'Read-Only') {
                iconContainer.innerHTML = '<span title="Field is Read-Only for this profile"><i data-lucide="lock" style="width: 15px; height: 15px; color: #d97706;"></i></span>';
            } else {
                iconContainer.innerHTML = '<span title="Field is completely Hidden for this profile"><i data-lucide="eye-off" style="width: 15px; height: 15px; color: #dc2626;"></i></span>';
            }
            if (window.lucide) lucide.createIcons();
        }
    }

    // 3. Recalculate KPI strip
    applyFiltersToTable();

    // 4. Auto-save in background via dedicated single field upsert endpoint
    try {
        await fetch(`/api/settings/permissions/${encodeURIComponent(selectedProfileId)}/field`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': window.CSRF_TOKEN
            },
            body: JSON.stringify({
                module: mod,
                field_name: fieldName,
                permission: newPerm,
                _token: window.CSRF_TOKEN
            })
        });
    } catch (e) {
        console.warn('Autosave background sync note:', e);
    }
}

async function handleBulkSetFieldPerms(newPerm) {
    const tbody = document.getElementById('flsTableBody');
    if (!tbody) return;

    const visibleRows = Array.from(tbody.querySelectorAll('tr[data-mod]')).filter(tr => tr.style.display !== 'none');
    if (visibleRows.length === 0) {
        showFeedback('No visible fields to update.', 'error');
        return;
    }

    visibleRows.forEach(tr => {
        const mod = tr.getAttribute('data-mod');
        const field = tr.getAttribute('data-field');

        const idx = fieldPerms.findIndex(p => p.module === mod && p.field_name === field);
        if (idx >= 0) {
            fieldPerms[idx].permission = newPerm;
        } else {
            fieldPerms.push({
                id: '',
                profile_id: selectedProfileId,
                module: mod,
                field_name: field,
                permission: newPerm
            });
        }

        tr.setAttribute('data-permission', newPerm);
        tr.className = newPerm === 'Hidden' ? 'row-hidden' : (newPerm === 'Read-Only' ? 'row-readonly' : '');

        const select = tr.querySelector('.fls-perm-select');
        if (select) {
            select.className = 'form-select fls-perm-select ' + (
                newPerm === 'Read/Write' ? 'perm-rw' : (newPerm === 'Read-Only' ? 'perm-ro' : 'perm-hd')
            );
            select.value = newPerm;
        }

        const iconContainer = document.getElementById(`icon-${mod}-${field}`);
        if (iconContainer) {
            if (newPerm === 'Read/Write') {
                iconContainer.innerHTML = '<span title="Full Read &amp; Write Access"><i data-lucide="check" style="width: 16px; height: 16px; color: #16a34a;"></i></span>';
            } else if (newPerm === 'Read-Only') {
                iconContainer.innerHTML = '<span title="Field is Read-Only for this profile"><i data-lucide="lock" style="width: 15px; height: 15px; color: #d97706;"></i></span>';
            } else {
                iconContainer.innerHTML = '<span title="Field is completely Hidden for this profile"><i data-lucide="eye-off" style="width: 15px; height: 15px; color: #dc2626;"></i></span>';
            }
        }
    });

    if (window.lucide) lucide.createIcons();
    applyFiltersToTable();
    showFeedback(`Applied '${newPerm}' to ${visibleRows.length} visible field(s) for this profile. Click 'Save Field Security' to persist.`, 'success');
}

async function handleSavePermissions() {
    const saveBtn = document.getElementById('saveFlsBtn');
    const saveText = document.getElementById('saveFlsText');
    const saveIcon = document.getElementById('saveFlsIcon');

    saveBtn.disabled = true;
    saveText.textContent = 'Saving Changes...';

    const payload = {
        fieldPermissions: fieldPerms,
        _token: window.CSRF_TOKEN
    };

    try {
        const response = await fetch(`/api/settings/permissions/${encodeURIComponent(selectedProfileId)}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': window.CSRF_TOKEN
            },
            body: JSON.stringify(payload)
        });

        const result = await response.json();
        if (response.ok && result.success) {
            showFeedback('Field permissions updated successfully in database!', 'success');
        } else {
            showFeedback(result.message || 'Error saving field permissions.', 'error');
        }
    } catch (e) {
        console.error('Error saving field permissions:', e);
        showFeedback('Network error while saving field permissions.', 'error');
    } finally {
        saveBtn.disabled = false;
        saveText.textContent = 'Save Field Security';
    }
}

async function handleResetBaseline() {
    if (!confirm('Are you sure you want to reset field permissions to standard profile baseline? Any custom field overrides will be reverted.')) {
        return;
    }

    try {
        const response = await fetch(`/api/settings/permissions/${encodeURIComponent(selectedProfileId)}/reset`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': window.CSRF_TOKEN
            },
            body: JSON.stringify({ _token: window.CSRF_TOKEN })
        });

        const result = await response.json();
        if (response.ok && result.success) {
            showFeedback('Field permissions reset to default baseline!', 'success');
            setTimeout(() => {
                window.location.reload();
            }, 800);
        } else {
            showFeedback(result.message || 'Failed to reset baseline.', 'error');
        }
    } catch (e) {
        showFeedback('Error contacting server to reset permissions.', 'error');
    }
}

let feedbackTimeout = null;
function showFeedback(msg, type = 'success') {
    const banner = document.getElementById('feedbackBanner');
    const textEl = document.getElementById('feedbackText');
    const iconEl = document.getElementById('feedbackIcon');

    textEl.textContent = msg;
    if (type === 'success') {
        banner.style.background = '#ecfdf5';
        banner.style.border = '1px solid #a7f3d0';
        banner.style.color = '#047857';
        iconEl.setAttribute('data-lucide', 'check-circle-2');
    } else {
        banner.style.background = '#fef2f2';
        banner.style.border = '1px solid #fecaca';
        banner.style.color = '#b91c1c';
        iconEl.setAttribute('data-lucide', 'alert-circle');
    }

    banner.style.display = 'flex';
    if (window.lucide) lucide.createIcons();

    if (feedbackTimeout) clearTimeout(feedbackTimeout);
    feedbackTimeout = setTimeout(() => {
        banner.style.display = 'none';
    }, 4000);
}

document.addEventListener('DOMContentLoaded', () => {
    if (window.lucide) lucide.createIcons();
    applyFiltersToTable();
});
</script>
@endsection
