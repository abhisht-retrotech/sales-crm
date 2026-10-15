@extends('layouts.app')

@section('title', 'Record Rights (CRUD Matrix) — DealSync Admin')

@section('content')
<style>
/* ========================================================================= */
/* RECORD-LEVEL RIGHTS MATRIX DEDICATED STYLES                               */
/* ========================================================================= */
.record-rights-container {
    max-width: 1240px;
    margin: 0 auto;
    padding-bottom: 3.5rem;
}

.form-select, .form-input {
    box-sizing: border-box;
    background-color: var(--zinc-50, #f8fafc);
    border: 1px solid var(--zinc-200, #e2e8f0);
    border-radius: var(--radius-md, 8px);
    color: var(--zinc-950, #09090b);
    font-size: 0.8125rem;
    font-family: inherit;
    line-height: 1.4;
    outline: none;
    transition: border-color 0.15s ease, background-color 0.15s ease, box-shadow 0.15s ease;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.02);
}

.form-select {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 0.75rem center;
    background-repeat: no-repeat;
    background-size: 1.15em 1.15em;
    padding: 0.45rem 2.25rem 0.45rem 0.85rem;
    cursor: pointer;
    appearance: none;
}

.form-select:hover, .form-input:hover {
    border-color: var(--zinc-300, #cbd5e1);
    background-color: #ffffff;
}

.form-select:focus, .form-input:focus,
.form-select:focus-visible, .form-input:focus-visible {
    background-color: #ffffff !important;
    border-color: var(--color-primary, #4f46e5) !important;
    box-shadow: 0 0 0 3px var(--color-primary-subtle, rgba(79, 70, 229, 0.15)) !important;
}

.form-input {
    padding: 0.45rem 0.85rem;
    width: 100%;
}

.kpi-card {
    background: var(--bg-card, #ffffff);
    border: 1px solid var(--border-card, #e2e8f0);
    border-radius: var(--radius-lg, 10px);
    padding: 0.85rem 1rem;
    display: flex;
    align-items: center;
    gap: 0.85rem;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.02);
}

.kpi-icon-box {
    width: 38px;
    height: 38px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.data-table th {
    background: var(--zinc-100, #f1f5f9);
    color: var(--zinc-700, #334155);
    font-weight: 600;
    font-size: 0.75rem;
    letter-spacing: 0.02em;
    padding: 0.75rem 0.85rem;
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

.data-table tbody tr:hover td {
    background-color: var(--zinc-50, #f8fafc);
}

.data-table tbody tr:last-child td {
    border-bottom: none;
}

.perm-checkbox-label {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    cursor: pointer;
    font-size: 0.725rem;
    font-weight: 600;
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    transition: all 0.15s ease;
}

.perm-checkbox-label.is-allowed {
    background: #ecfdf5;
    color: #047857;
    border: 1px solid #a7f3d0;
}

.perm-checkbox-label.is-blocked {
    background: #f4f4f5;
    color: #71717a;
    border: 1px solid #e4e4e7;
}

.filter-pill {
    padding: 0.35rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
    cursor: pointer;
    border: 1px solid var(--zinc-200, #e2e8f0);
    background: #ffffff;
    color: var(--zinc-600, #475569);
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    transition: all 0.15s ease;
}

.filter-pill:hover {
    background: var(--zinc-100, #f1f5f9);
    color: var(--zinc-900, #0f172a);
}

.filter-pill.is-active {
    background: var(--zinc-950, #09090b);
    color: #ffffff;
    border-color: var(--zinc-950, #09090b);
}

.rr-modal-overlay {
    position: fixed;
    inset: 0;
    background-color: rgba(9, 9, 11, 0.55);
    backdrop-filter: blur(4px);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 1050;
    padding: 1rem;
}

.rr-modal-overlay.is-open {
    display: flex !important;
}

.modal-content {
    background: var(--bg-card, #ffffff);
    border-radius: var(--radius-xl, 14px);
    border: 1px solid var(--border-card, #e2e8f0);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
    width: 100%;
    overflow: hidden;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(4px); }
    to { opacity: 1; transform: translateY(0); }
}

.animate-fade-in {
    animation: fadeIn 0.15s ease-out forwards;
}
</style>

<div class="record-rights-container animate-fade-in">
    <!-- Feedback Banner (SettingsView.tsx line 1737) -->
    <div id="feedbackBanner" style="display: none; padding: 0.85rem 1.25rem; border-radius: var(--radius-lg); margin-bottom: 1.25rem; align-items: center; gap: 0.6rem; font-size: 0.8125rem; font-weight: 500;">
        <i id="feedbackIcon" data-lucide="check-circle-2" style="width: 16px; height: 16px; flex-shrink: 0;"></i>
        <span id="feedbackText"></span>
    </div>

    <!-- Top Governance & Profile Bar -->
    <div class="card" style="padding: 1.25rem; margin-bottom: 1.25rem; border-radius: var(--radius-xl, 14px);">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(79, 70, 229, 0.1); color: var(--color-primary, #4f46e5); display: flex; align-items: center; justify-content: center;">
                        <i data-lucide="shield" style="width: 18px; height: 18px;"></i>
                    </div>
                    <div>
                        <h2 style="font-size: 1.125rem; font-weight: 600; color: var(--zinc-950); letter-spacing: -0.02em; margin: 0;">
                            Record-Level Rights Matrix (CRUD)
                        </h2>
                        <div style="font-size: 0.775rem; color: var(--zinc-500); margin-top: 0.1rem;">
                            Configure object-level permissions, record ownership transfer authorizations, and data visibility boundary scopes per security profile.
                        </div>
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 0.75rem; margin-top: 0.85rem; flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <span style="font-size: 0.75rem; font-weight: 600; color: var(--zinc-700);">Configuring Profile:</span>
                        <select
                            id="selectedProfileSelect"
                            class="form-select"
                            onchange="handleProfileChange(this.value)"
                            style="width: 250px; font-weight: 600; padding: 0.35rem 0.75rem; font-size: 0.8125rem; height: 36px;"
                        >
                            @foreach($profiles as $p)
                                <option value="{{ $p->id }}" {{ (string)$p->id === (string)$selectedProfileId ? 'selected' : '' }}>
                                    {{ $p->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div id="profileGovernanceBadge" style="display: inline-flex; align-items: center; gap: 0.35rem;">
                        @php
                            $currProf = $profiles->firstWhere('id', $selectedProfileId) ?? $profiles->first();
                            $isAdminProf = $currProf && ($currProf->is_admin || (string)$currProf->id === 'prof-admin');
                        @endphp
                        @if($isAdminProf)
                            <span style="font-size: 0.68rem; font-weight: 700; background: #e0e7ff; color: #4338ca; border: 1px solid #c7d2fe; padding: 0.15rem 0.5rem; border-radius: 9999px; display: inline-flex; align-items: center; gap: 0.25rem;">
                                👑 Full Organization Administrator
                            </span>
                        @else
                            <span style="font-size: 0.68rem; font-weight: 600; background: #f4f4f5; color: #52525b; border: 1px solid #e4e4e7; padding: 0.15rem 0.5rem; border-radius: 9999px;">
                                Custom Access Profile
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                <button
                    type="button"
                    onclick="openCloneModal()"
                    class="btn btn-secondary"
                    style="display: inline-flex; align-items: center; gap: 0.4rem; font-weight: 600; font-size: 0.8125rem; padding: 0.45rem 0.85rem;"
                    title="Clone permissions from another security profile"
                >
                    <i data-lucide="copy" style="width: 14px; height: 14px;"></i>
                    <span>Clone Rights...</span>
                </button>

                <button
                    type="button"
                    onclick="openResetModal()"
                    class="btn btn-secondary"
                    style="display: inline-flex; align-items: center; gap: 0.4rem; font-weight: 600; font-size: 0.8125rem; padding: 0.45rem 0.85rem; color: #b91c1c; border-color: #fecaca;"
                    title="Reset permissions to system baseline defaults"
                >
                    <i data-lucide="rotate-ccw" style="width: 14px; height: 14px;"></i>
                    <span>Reset Defaults</span>
                </button>

                <button
                    type="button"
                    onclick="openAddCustomModal()"
                    class="btn btn-secondary"
                    style="display: inline-flex; align-items: center; gap: 0.4rem; font-weight: 600; font-size: 0.8125rem; padding: 0.45rem 0.85rem;"
                    title="Add a custom object or module permission right"
                >
                    <i data-lucide="plus-circle" style="width: 14px; height: 14px; color: var(--color-primary, #4f46e5);"></i>
                    <span>+ Add Custom Object</span>
                </button>

                <button
                    type="button"
                    id="saveMatrixBtn"
                    onclick="handleSavePermissions()"
                    class="btn btn-primary"
                    style="display: inline-flex; align-items: center; gap: 0.45rem; font-weight: 600; font-size: 0.8125rem; padding: 0.5rem 1.15rem;"
                >
                    <i id="saveMatrixIcon" data-lucide="save" style="width: 14px; height: 14px;"></i>
                    <span id="saveMatrixText">Save Matrix</span>
                </button>
            </div>
        </div>
    </div>

    @php
        $totalModulesCount = count($modules) + count($customObjects);
        $initPermCollection = collect($initialPermissions ?? []);
        $createPermCount = $initPermCollection->where('can_create', 1)->count();
        $deletePermCount = $initPermCollection->where('can_delete', 1)->count();
        $transferPermCount = $initPermCollection->where('can_transfer', 1)->count();
        $scopeAllCount = $initPermCollection->where('record_scope', 'All')->count();
        $scopeTeamCount = $initPermCollection->where('record_scope', 'Team')->count();
        $scopeOwnCount = $initPermCollection->where('record_scope', 'Own')->count();
    @endphp

    <!-- KPI Metric Badges Ribbon -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.25rem;">
        <div class="kpi-card">
            <div class="kpi-icon-box" style="background: #eef2ff; color: #4f46e5;">
                <i data-lucide="layers" style="width: 20px; height: 20px;"></i>
            </div>
            <div>
                <div style="font-size: 0.725rem; color: var(--zinc-500); font-weight: 500;">Active Objects</div>
                <div id="kpiTotalModules" style="font-size: 1.25rem; font-weight: 700; color: var(--zinc-950);">{{ $totalModulesCount }}</div>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon-box" style="background: #ecfdf5; color: #059669;">
                <i data-lucide="plus-square" style="width: 20px; height: 20px;"></i>
            </div>
            <div>
                <div style="font-size: 0.725rem; color: var(--zinc-500); font-weight: 500;">Create Permitted</div>
                <div id="kpiCreateCount" style="font-size: 1.25rem; font-weight: 700; color: #059669;">{{ $createPermCount }} / {{ $totalModulesCount }}</div>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon-box" style="background: #fef2f2; color: #dc2626;">
                <i data-lucide="trash-2" style="width: 20px; height: 20px;"></i>
            </div>
            <div>
                <div style="font-size: 0.725rem; color: var(--zinc-500); font-weight: 500;">Delete Permitted</div>
                <div id="kpiDeleteCount" style="font-size: 1.25rem; font-weight: 700; color: #dc2626;">{{ $deletePermCount }} / {{ $totalModulesCount }}</div>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon-box" style="background: #eff6ff; color: #2563eb;">
                <i data-lucide="user-check" style="width: 20px; height: 20px;"></i>
            </div>
            <div>
                <div style="font-size: 0.725rem; color: var(--zinc-500); font-weight: 500;">Transfer Authorized</div>
                <div id="kpiTransferCount" style="font-size: 1.25rem; font-weight: 700; color: #2563eb;">{{ $transferPermCount }} / {{ $totalModulesCount }}</div>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon-box" style="background: #fdf4ff; color: #a855f7;">
                <i data-lucide="eye" style="width: 20px; height: 20px;"></i>
            </div>
            <div>
                <div style="font-size: 0.725rem; color: var(--zinc-500); font-weight: 500;">Scope Balance</div>
                <div id="kpiScopeBreakdown" style="font-size: 0.8125rem; font-weight: 600; color: var(--zinc-800); margin-top: 0.15rem;">
                    All: {{ $scopeAllCount }} | Team: {{ $scopeTeamCount }} | Own: {{ $scopeOwnCount }}
                </div>
            </div>
        </div>
    </div>

    <!-- Batch Actions & Live Filtering Toolbar -->
    <div class="card" style="padding: 1rem; margin-bottom: 1.25rem; border-radius: var(--radius-xl, 14px);">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <!-- Filter Pills -->
            <div style="display: flex; gap: 0.35rem; flex-wrap: wrap; align-items: center;">
                <button type="button" class="filter-pill is-active" id="pillAll" onclick="setQuickFilter('all')">
                    <span>✨ All Objects</span>
                    <span id="pillCountAll" style="font-size: 0.675rem; background: rgba(255,255,255,0.25); padding: 0.1rem 0.35rem; border-radius: 9999px;">10</span>
                </button>
                <button type="button" class="filter-pill" id="pillStandard" onclick="setQuickFilter('standard')">
                    <span>📁 Standard Objects</span>
                </button>
                <button type="button" class="filter-pill" id="pillCustom" onclick="setQuickFilter('custom')">
                    <span>🧩 Custom Objects</span>
                </button>
                <button type="button" class="filter-pill" id="pillFullCrud" onclick="setQuickFilter('full_crud')">
                    <span>⚡ Full CRUD</span>
                </button>
                <button type="button" class="filter-pill" id="pillReadOnly" onclick="setQuickFilter('read_only')">
                    <span>🔒 Read-Only</span>
                </button>
            </div>

            <!-- Search Filter -->
            <div style="position: relative; width: 280px; max-width: 100%;">
                <i data-lucide="search" style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); width: 14px; height: 14px; color: var(--zinc-400); pointer-events: none;"></i>
                <input
                    type="text"
                    id="moduleSearchInput"
                    placeholder="Search module or API slug..."
                    class="form-input"
                    oninput="handleSearchChange(this.value)"
                    style="padding-left: 2.15rem; font-size: 0.8125rem; height: 34px;"
                >
            </div>
        </div>

        <!-- Column Batch Toggles -->
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem; margin-top: 0.85rem; padding-top: 0.85rem; border-top: 1px dashed var(--zinc-200, #e2e8f0);">
            <div style="display: flex; align-items: center; gap: 0.4rem; flex-wrap: wrap;">
                <span style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500); text-transform: uppercase; letter-spacing: 0.03em;">Column Toggles:</span>
                <button type="button" onclick="toggleColumnBatch('can_create')" class="btn btn-secondary btn-sm" style="font-size: 0.72rem; padding: 0.25rem 0.55rem; font-weight: 600;">
                    Toggle Create (C)
                </button>
                <button type="button" onclick="toggleColumnBatch('can_read')" class="btn btn-secondary btn-sm" style="font-size: 0.72rem; padding: 0.25rem 0.55rem; font-weight: 600;">
                    Toggle Read (R)
                </button>
                <button type="button" onclick="toggleColumnBatch('can_update')" class="btn btn-secondary btn-sm" style="font-size: 0.72rem; padding: 0.25rem 0.55rem; font-weight: 600;">
                    Toggle Update (U)
                </button>
                <button type="button" onclick="toggleColumnBatch('can_delete')" class="btn btn-secondary btn-sm" style="font-size: 0.72rem; padding: 0.25rem 0.55rem; font-weight: 600;">
                    Toggle Delete (D)
                </button>
                <button type="button" onclick="toggleColumnBatch('can_transfer')" class="btn btn-secondary btn-sm" style="font-size: 0.72rem; padding: 0.25rem 0.55rem; font-weight: 600;">
                    Toggle Transfer (T)
                </button>
            </div>

            <div style="display: flex; align-items: center; gap: 0.4rem; flex-wrap: wrap;">
                <span style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500); text-transform: uppercase; letter-spacing: 0.03em;">Quick Set:</span>
                <button type="button" onclick="batchSetAllAccess(true)" class="btn btn-secondary btn-sm" style="font-size: 0.72rem; padding: 0.25rem 0.55rem; color: #047857; background: #ecfdf5; border-color: #a7f3d0; font-weight: 600;">
                    ✓ All Full Access
                </button>
                <button type="button" onclick="batchSetAllAccess(false)" class="btn btn-secondary btn-sm" style="font-size: 0.72rem; padding: 0.25rem 0.55rem; color: #b45309; background: #fffbeb; border-color: #fde68a; font-weight: 600;">
                    🔒 All Read-Only
                </button>
                <select class="form-select" onchange="batchSetScope(this.value); this.value='';" style="font-size: 0.72rem; height: 28px; width: 135px; padding: 0.15rem 1.75rem 0.15rem 0.55rem; font-weight: 600;">
                    <option value="" disabled selected>Bulk Set Scope</option>
                    <option value="All">Scope: All</option>
                    <option value="Team">Scope: Team</option>
                    <option value="Own">Scope: Own</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Record-Level Rights Matrix Table Card -->
    <div class="card" style="padding: 0; overflow: hidden; border-radius: var(--radius-xl, 14px);">
        <div style="overflow-x: auto;">
            <table class="data-table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="padding: 0.85rem 1.15rem; min-width: 220px;">Module / Object</th>
                        <th style="text-align: center; padding: 0.75rem 0.85rem; width: 110px;">Create (C)</th>
                        <th style="text-align: center; padding: 0.75rem 0.85rem; width: 110px;">Read (R)</th>
                        <th style="text-align: center; padding: 0.75rem 0.85rem; width: 110px;">Update (U)</th>
                        <th style="text-align: center; padding: 0.75rem 0.85rem; width: 110px;">Delete (D)</th>
                        <th style="text-align: center; padding: 0.75rem 0.85rem; width: 130px;">Transfer (T)</th>
                        <th style="padding: 0.75rem 1rem; width: 170px;">Record Scope</th>
                        <th style="text-align: right; padding: 0.75rem 1.15rem; width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="matrixTableBody">
                    @php
                        $moduleDefs = [
                            'leads'          => ['name' => 'Leads', 'icon' => 'users-2', 'desc' => 'Inbound prospects and inquiries'],
                            'accounts'       => ['name' => 'Accounts', 'icon' => 'building-2', 'desc' => 'Client & vendor organizations'],
                            'contacts'       => ['name' => 'Contacts', 'icon' => 'contact', 'desc' => 'Individual persons and stakeholders'],
                            'opportunities'  => ['name' => 'Opportunities', 'icon' => 'trending-up', 'desc' => 'Sales pipeline and active deals'],
                            'tasks'          => ['name' => 'Tasks', 'icon' => 'check-square', 'desc' => 'Activities, reminders, and action items'],
                            'purchase_deals' => ['name' => 'Purchase Deals', 'icon' => 'shopping-cart', 'desc' => 'Procurement contracts & supplier deals'],
                            'shipping'       => ['name' => 'Shipping & Logistics', 'icon' => 'truck', 'desc' => 'Freight dispatch and customs tracking'],
                            'payments'       => ['name' => 'Payments & Invoicing', 'icon' => 'credit-card', 'desc' => 'AR/AP invoices and receivables'],
                            'wires'          => ['name' => 'Wires & Reconciliation', 'icon' => 'landmark', 'desc' => 'UTR bank transaction settlements'],
                            'users'          => ['name' => 'Users Directory', 'icon' => 'shield', 'desc' => 'Internal personnel and seat licenses'],
                        ];
                        $permMap = collect($initialPermissions ?? [])->keyBy('module');
                    @endphp

                    @foreach($modules as $modKey => $modLabel)
                        @php
                            $perm = $permMap->get($modKey);
                            $def = $moduleDefs[$modKey] ?? ['name' => $modLabel, 'icon' => 'layers', 'desc' => 'CRM Module'];
                            $canCreate = $perm ? (bool)$perm['can_create'] : true;
                            $canRead = $perm ? (bool)$perm['can_read'] : true;
                            $canUpdate = $perm ? (bool)$perm['can_update'] : true;
                            $canDelete = $perm ? (bool)$perm['can_delete'] : false;
                            $canTransfer = $perm ? (bool)$perm['can_transfer'] : false;
                            $scope = $perm['record_scope'] ?? 'All';
                        @endphp
                        <tr id="row-{{ $modKey }}">
                            <td style="padding: 0.85rem 1.15rem;">
                                <div style="display: flex; align-items: center; gap: 0.65rem;">
                                    <div style="width: 32px; height: 32px; border-radius: 8px; background: #f4f4f5; color: #52525b; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                        <i data-lucide="{{ $def['icon'] ?? 'layers' }}" style="width: 16px; height: 16px;"></i>
                                    </div>
                                    <div>
                                        <div style="display: flex; align-items: center; gap: 0.35rem;">
                                            <strong style="color: var(--zinc-950); font-size: 0.8125rem;">{{ $def['name'] ?? $modLabel }}</strong>
                                        </div>
                                        <div style="font-size: 0.7rem; color: var(--zinc-400); font-family: monospace;">{{ $modKey }}</div>
                                    </div>
                                </div>
                            </td>

                            <td style="text-align: center; padding: 0.75rem 0.85rem;">
                                <label class="perm-checkbox-label {{ $canCreate ? 'is-allowed' : 'is-blocked' }}">
                                    <input
                                        type="checkbox"
                                        {{ $canCreate ? 'checked' : '' }}
                                        onchange="updateRecordPerm('{{ $modKey }}', 'can_create', this.checked)"
                                        style="cursor: pointer; width: 14px; height: 14px; accent-color: #059669;"
                                    >
                                    <span>{{ $canCreate ? 'Allow' : 'Block' }}</span>
                                </label>
                            </td>

                            <td style="text-align: center; padding: 0.75rem 0.85rem;">
                                <label class="perm-checkbox-label {{ $canRead ? 'is-allowed' : 'is-blocked' }}">
                                    <input
                                        type="checkbox"
                                        {{ $canRead ? 'checked' : '' }}
                                        onchange="updateRecordPerm('{{ $modKey }}', 'can_read', this.checked)"
                                        style="cursor: pointer; width: 14px; height: 14px; accent-color: #059669;"
                                    >
                                    <span>{{ $canRead ? 'Allow' : 'Block' }}</span>
                                </label>
                            </td>

                            <td style="text-align: center; padding: 0.75rem 0.85rem;">
                                <label class="perm-checkbox-label {{ $canUpdate ? 'is-allowed' : 'is-blocked' }}">
                                    <input
                                        type="checkbox"
                                        {{ $canUpdate ? 'checked' : '' }}
                                        onchange="updateRecordPerm('{{ $modKey }}', 'can_update', this.checked)"
                                        style="cursor: pointer; width: 14px; height: 14px; accent-color: #059669;"
                                    >
                                    <span>{{ $canUpdate ? 'Allow' : 'Block' }}</span>
                                </label>
                            </td>

                            <td style="text-align: center; padding: 0.75rem 0.85rem;">
                                <label class="perm-checkbox-label {{ $canDelete ? 'is-allowed' : 'is-blocked' }}" style="{{ $canDelete ? 'background: #fef2f2; color: #dc2626; border-color: #fecaca;' : '' }}">
                                    <input
                                        type="checkbox"
                                        {{ $canDelete ? 'checked' : '' }}
                                        onchange="updateRecordPerm('{{ $modKey }}', 'can_delete', this.checked)"
                                        style="cursor: pointer; width: 14px; height: 14px; accent-color: #dc2626;"
                                    >
                                    <span>{{ $canDelete ? 'Allow' : 'Block' }}</span>
                                </label>
                            </td>

                            <td style="text-align: center; padding: 0.75rem 0.85rem;">
                                <label class="perm-checkbox-label {{ $canTransfer ? 'is-allowed' : 'is-blocked' }}" style="{{ $canTransfer ? 'background: #eff6ff; color: #2563eb; border-color: #bfdbfe;' : '' }}">
                                    <input
                                        type="checkbox"
                                        {{ $canTransfer ? 'checked' : '' }}
                                        onchange="updateRecordPerm('{{ $modKey }}', 'can_transfer', this.checked)"
                                        style="cursor: pointer; width: 14px; height: 14px; accent-color: #2563eb;"
                                    >
                                    <span>{{ $canTransfer ? 'Transfer' : 'None' }}</span>
                                </label>
                            </td>

                            <td style="padding: 0.75rem 1rem;">
                                <select
                                    class="form-select"
                                    onchange="updateRecordScope('{{ $modKey }}', this.value)"
                                    style="
                                        padding: 0.3rem 0.65rem;
                                        font-size: 0.75rem;
                                        width: 100%;
                                        height: 32px;
                                        font-weight: 600;
                                        border-color: {{ $scope === 'All' ? '#c7d2fe' : ($scope === 'Team' ? '#fde68a' : '#e4e4e7') }};
                                        background: {{ $scope === 'All' ? '#f5f7ff' : ($scope === 'Team' ? '#fffdf5' : '#ffffff') }};
                                        color: {{ $scope === 'All' ? '#3730a3' : ($scope === 'Team' ? '#92400e' : '#334155') }};
                                    "
                                >
                                    <option value="All" {{ $scope === 'All' ? 'selected' : '' }}>🌐 All Records (Global)</option>
                                    <option value="Team" {{ $scope === 'Team' ? 'selected' : '' }}>👥 Team Records (Hierarchy)</option>
                                    <option value="Own" {{ $scope === 'Own' ? 'selected' : '' }}>👤 Own Records Only</option>
                                </select>
                            </td>

                            <td style="text-align: right; padding: 0.75rem 1.15rem;">
                                <div style="display: inline-flex; align-items: center; gap: 0.35rem;">
                                    <button
                                        type="button"
                                        onclick="handleSaveSingleRow('{{ $modKey }}', this)"
                                        class="btn btn-secondary btn-sm"
                                        style="padding: 0.25rem 0.45rem; font-size: 0.7rem;"
                                        title="Save rights for this module"
                                    >
                                        <i data-lucide="check" style="width: 13px; height: 13px; color: #16a34a;"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach

                    @foreach($customObjects as $co)
                        @php
                            $cKey = 'obj_' . $co->api_name;
                            $perm = $permMap->get($cKey);
                            $canCreate = $perm ? (bool)$perm['can_create'] : true;
                            $canRead = $perm ? (bool)$perm['can_read'] : true;
                            $canUpdate = $perm ? (bool)$perm['can_update'] : true;
                            $canDelete = $perm ? (bool)$perm['can_delete'] : false;
                            $canTransfer = $perm ? (bool)$perm['can_transfer'] : false;
                            $scope = $perm['record_scope'] ?? 'All';
                        @endphp
                        <tr id="row-{{ $cKey }}">
                            <td style="padding: 0.85rem 1.15rem;">
                                <div style="display: flex; align-items: center; gap: 0.65rem;">
                                    <div style="width: 32px; height: 32px; border-radius: 8px; background: #f5f3ff; color: #7c3aed; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                        <i data-lucide="box" style="width: 16px; height: 16px;"></i>
                                    </div>
                                    <div>
                                        <div style="display: flex; align-items: center; gap: 0.35rem;">
                                            <strong style="color: var(--zinc-950); font-size: 0.8125rem;">{{ $co->label }} (Custom)</strong>
                                            <span style="font-size: 0.625rem; font-weight: 600; background: #f5f3ff; color: #7c3aed; border: 1px solid #ddd6fe; padding: 0.05rem 0.35rem; border-radius: 4px;">Custom</span>
                                        </div>
                                        <div style="font-size: 0.7rem; color: var(--zinc-400); font-family: monospace;">{{ $cKey }}</div>
                                    </div>
                                </div>
                            </td>

                            <td style="text-align: center; padding: 0.75rem 0.85rem;">
                                <label class="perm-checkbox-label {{ $canCreate ? 'is-allowed' : 'is-blocked' }}">
                                    <input
                                        type="checkbox"
                                        {{ $canCreate ? 'checked' : '' }}
                                        onchange="updateRecordPerm('{{ $cKey }}', 'can_create', this.checked)"
                                        style="cursor: pointer; width: 14px; height: 14px; accent-color: #059669;"
                                    >
                                    <span>{{ $canCreate ? 'Allow' : 'Block' }}</span>
                                </label>
                            </td>

                            <td style="text-align: center; padding: 0.75rem 0.85rem;">
                                <label class="perm-checkbox-label {{ $canRead ? 'is-allowed' : 'is-blocked' }}">
                                    <input
                                        type="checkbox"
                                        {{ $canRead ? 'checked' : '' }}
                                        onchange="updateRecordPerm('{{ $cKey }}', 'can_read', this.checked)"
                                        style="cursor: pointer; width: 14px; height: 14px; accent-color: #059669;"
                                    >
                                    <span>{{ $canRead ? 'Allow' : 'Block' }}</span>
                                </label>
                            </td>

                            <td style="text-align: center; padding: 0.75rem 0.85rem;">
                                <label class="perm-checkbox-label {{ $canUpdate ? 'is-allowed' : 'is-blocked' }}">
                                    <input
                                        type="checkbox"
                                        {{ $canUpdate ? 'checked' : '' }}
                                        onchange="updateRecordPerm('{{ $cKey }}', 'can_update', this.checked)"
                                        style="cursor: pointer; width: 14px; height: 14px; accent-color: #059669;"
                                    >
                                    <span>{{ $canUpdate ? 'Allow' : 'Block' }}</span>
                                </label>
                            </td>

                            <td style="text-align: center; padding: 0.75rem 0.85rem;">
                                <label class="perm-checkbox-label {{ $canDelete ? 'is-allowed' : 'is-blocked' }}" style="{{ $canDelete ? 'background: #fef2f2; color: #dc2626; border-color: #fecaca;' : '' }}">
                                    <input
                                        type="checkbox"
                                        {{ $canDelete ? 'checked' : '' }}
                                        onchange="updateRecordPerm('{{ $cKey }}', 'can_delete', this.checked)"
                                        style="cursor: pointer; width: 14px; height: 14px; accent-color: #dc2626;"
                                    >
                                    <span>{{ $canDelete ? 'Allow' : 'Block' }}</span>
                                </label>
                            </td>

                            <td style="text-align: center; padding: 0.75rem 0.85rem;">
                                <label class="perm-checkbox-label {{ $canTransfer ? 'is-allowed' : 'is-blocked' }}" style="{{ $canTransfer ? 'background: #eff6ff; color: #2563eb; border-color: #bfdbfe;' : '' }}">
                                    <input
                                        type="checkbox"
                                        {{ $canTransfer ? 'checked' : '' }}
                                        onchange="updateRecordPerm('{{ $cKey }}', 'can_transfer', this.checked)"
                                        style="cursor: pointer; width: 14px; height: 14px; accent-color: #2563eb;"
                                    >
                                    <span>{{ $canTransfer ? 'Transfer' : 'None' }}</span>
                                </label>
                            </td>

                            <td style="padding: 0.75rem 1rem;">
                                <select
                                    class="form-select"
                                    onchange="updateRecordScope('{{ $cKey }}', this.value)"
                                    style="
                                        padding: 0.3rem 0.65rem;
                                        font-size: 0.75rem;
                                        width: 100%;
                                        height: 32px;
                                        font-weight: 600;
                                        border-color: {{ $scope === 'All' ? '#c7d2fe' : ($scope === 'Team' ? '#fde68a' : '#e4e4e7') }};
                                        background: {{ $scope === 'All' ? '#f5f7ff' : ($scope === 'Team' ? '#fffdf5' : '#ffffff') }};
                                        color: {{ $scope === 'All' ? '#3730a3' : ($scope === 'Team' ? '#92400e' : '#334155') }};
                                    "
                                >
                                    <option value="All" {{ $scope === 'All' ? 'selected' : '' }}>🌐 All Records (Global)</option>
                                    <option value="Team" {{ $scope === 'Team' ? 'selected' : '' }}>👥 Team Records (Hierarchy)</option>
                                    <option value="Own" {{ $scope === 'Own' ? 'selected' : '' }}>👤 Own Records Only</option>
                                </select>
                            </td>

                            <td style="text-align: right; padding: 0.75rem 1.15rem;">
                                <div style="display: inline-flex; align-items: center; gap: 0.35rem;">
                                    <button
                                        type="button"
                                        onclick="handleSaveSingleRow('{{ $cKey }}', this)"
                                        class="btn btn-secondary btn-sm"
                                        style="padding: 0.25rem 0.45rem; font-size: 0.7rem;"
                                        title="Save rights for this module"
                                    >
                                        <i data-lucide="check" style="width: 13px; height: 13px; color: #16a34a;"></i>
                                    </button>
                                    <button
                                        type="button"
                                        onclick="handleDeleteCustomRow('{{ $cKey }}')"
                                        class="btn btn-secondary btn-sm"
                                        style="padding: 0.25rem 0.45rem; font-size: 0.7rem; color: #dc2626;"
                                        title="Remove custom object permission from profile"
                                    >
                                        <i data-lucide="trash-2" style="width: 13px; height: 13px;"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- CLONE RIGHTS MODAL                                                        -->
<!-- ========================================================================= -->
<div id="cloneModal" class="rr-modal-overlay" style="display: none;">
    <div class="modal-content animate-fade-in" style="max-width: 480px;">
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 1.15rem 1.5rem; border-bottom: 1px solid var(--zinc-200, #e2e8f0);">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <i data-lucide="copy" style="width: 18px; height: 18px; color: var(--color-primary, #4f46e5);"></i>
                <h3 style="font-size: 1rem; font-weight: 600; color: var(--zinc-950); margin: 0;">
                    Clone Record Rights From Profile
                </h3>
            </div>
            <button type="button" onclick="closeCloneModal()" style="background: none; border: none; cursor: pointer; color: var(--zinc-400); font-size: 1.25rem;">&times;</button>
        </div>

        <form onsubmit="handleCloneSubmit(event)" style="padding: 1.25rem 1.5rem;">
            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--zinc-800); margin-bottom: 0.35rem;">
                    Source Security Profile:
                </label>
                <select id="cloneSourceProfileSelect" class="form-select" required style="width: 100%;">
                    <!-- Dynamically populated -->
                </select>
                <div style="font-size: 0.725rem; color: var(--zinc-500); margin-top: 0.35rem;">
                    All module CRUD rights and record visibility scopes will be copied to the currently selected profile.
                </div>
            </div>

            <div style="margin-bottom: 1.25rem; display: flex; align-items: flex-start; gap: 0.5rem;">
                <input type="checkbox" id="cloneIncludeFlsCheckbox" style="margin-top: 0.15rem; cursor: pointer;">
                <label for="cloneIncludeFlsCheckbox" style="font-size: 0.75rem; color: var(--zinc-700); cursor: pointer;">
                    <strong>Also clone Field-Level Security (FLS)</strong> permissions across all standard and custom fields.
                </label>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.5rem; border-top: 1px solid var(--zinc-100); padding-top: 1rem;">
                <button type="button" onclick="closeCloneModal()" class="btn btn-secondary">Cancel</button>
                <button type="submit" id="cloneSubmitBtn" class="btn btn-primary">
                    <span id="cloneSubmitText">Apply &amp; Clone Rights</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- ADD CUSTOM OBJECT / MODULE MODAL                                          -->
<!-- ========================================================================= -->
<div id="addCustomModal" class="rr-modal-overlay" style="display: none;">
    <div class="modal-content animate-fade-in" style="max-width: 500px;">
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 1.15rem 1.5rem; border-bottom: 1px solid var(--zinc-200, #e2e8f0);">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <i data-lucide="plus-circle" style="width: 18px; height: 18px; color: var(--color-primary, #4f46e5);"></i>
                <h3 style="font-size: 1rem; font-weight: 600; color: var(--zinc-950); margin: 0;">
                    Add Custom Object Record Right
                </h3>
            </div>
            <button type="button" onclick="closeAddCustomModal()" style="background: none; border: none; cursor: pointer; color: var(--zinc-400); font-size: 1.25rem;">&times;</button>
        </div>

        <form onsubmit="handleAddCustomSubmit(event)" style="padding: 1.25rem 1.5rem;">
            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--zinc-800); margin-bottom: 0.35rem;">
                    Select or Type Custom Module Slug:
                </label>
                <select id="customObjectPresetSelect" class="form-select" onchange="handleCustomPresetChange(this.value)" style="width: 100%; margin-bottom: 0.5rem;">
                    <!-- Options populated from window.INITIAL_CUSTOM_OBJECTS -->
                </select>
                <input
                    type="text"
                    id="newCustomModuleKey"
                    class="form-input"
                    placeholder="e.g. obj_contracts, obj_warehouses, assets..."
                    required
                >
            </div>

            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--zinc-800); margin-bottom: 0.35rem;">
                    Initial Record Permissions:
                </label>
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.5rem;">
                    <label style="font-size: 0.75rem; display: flex; align-items: center; gap: 0.35rem; cursor: pointer;">
                        <input type="checkbox" id="newModCreate" checked> Can Create
                    </label>
                    <label style="font-size: 0.75rem; display: flex; align-items: center; gap: 0.35rem; cursor: pointer;">
                        <input type="checkbox" id="newModRead" checked> Can Read
                    </label>
                    <label style="font-size: 0.75rem; display: flex; align-items: center; gap: 0.35rem; cursor: pointer;">
                        <input type="checkbox" id="newModUpdate" checked> Can Update
                    </label>
                    <label style="font-size: 0.75rem; display: flex; align-items: center; gap: 0.35rem; cursor: pointer;">
                        <input type="checkbox" id="newModDelete"> Can Delete
                    </label>
                    <label style="font-size: 0.75rem; display: flex; align-items: center; gap: 0.35rem; cursor: pointer;">
                        <input type="checkbox" id="newModTransfer"> Can Transfer Ownership
                    </label>
                </div>
            </div>

            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--zinc-800); margin-bottom: 0.35rem;">
                    Record Visibility Scope:
                </label>
                <select id="newModScope" class="form-select" style="width: 100%;">
                    <option value="All">All Records (Global Visibility)</option>
                    <option value="Team">Team Records Only (Hierarchy Visibility)</option>
                    <option value="Own">Own Records Only (Private Ownership)</option>
                </select>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.5rem; border-top: 1px solid var(--zinc-100); padding-top: 1rem;">
                <button type="button" onclick="closeAddCustomModal()" class="btn btn-secondary">Cancel</button>
                <button type="submit" id="addCustomSubmitBtn" class="btn btn-primary">
                    <span id="addCustomSubmitText">Register Module Rights</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- RESET TO DEFAULTS CONFIRMATION MODAL                                      -->
<!-- ========================================================================= -->
<div id="resetModal" class="rr-modal-overlay" style="display: none;">
    <div class="modal-content animate-fade-in" style="max-width: 440px;">
        <div style="padding: 1.5rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
                <div style="width: 40px; height: 40px; border-radius: 50%; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i data-lucide="alert-triangle" style="width: 20px; height: 20px;"></i>
                </div>
                <div>
                    <h3 style="font-size: 1rem; font-weight: 600; color: var(--zinc-950); margin: 0;">
                        Reset Permissions to Baseline?
                    </h3>
                    <div style="font-size: 0.75rem; color: var(--zinc-500); margin-top: 0.15rem;">
                        This will revert all CRUD and scoping rights for this profile back to standard CRM baseline defaults.
                    </div>
                </div>
            </div>

            <p style="font-size: 0.8125rem; color: var(--zinc-600); margin-bottom: 1.25rem;">
                Are you sure you want to proceed with resetting <strong id="resetProfileNamePlaceholder">this profile</strong>? Any custom module overrides will be restored to their standard tier settings.
            </p>

            <div style="display: flex; justify-content: flex-end; gap: 0.5rem;">
                <button type="button" onclick="closeResetModal()" class="btn btn-secondary">Cancel</button>
                <button type="button" id="confirmResetBtn" onclick="handleConfirmReset()" class="btn btn-secondary" style="background: #dc2626; color: #ffffff; border-color: #dc2626;">
                    <span id="confirmResetText">Reset to Defaults</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// ============================================================================
// RECORD-LEVEL RIGHTS MATRIX (1-TO-1 REACT PARITY WITH SettingsView.tsx)
// ============================================================================
const STANDARD_MODULES_DEF = [
    { id: 'leads', name: 'Leads', icon: 'users-2', desc: 'Inbound prospects and inquiries', is_custom: false },
    { id: 'accounts', name: 'Accounts', icon: 'building-2', desc: 'Client & vendor organizations', is_custom: false },
    { id: 'contacts', name: 'Contacts', icon: 'contact', desc: 'Individual persons and stakeholders', is_custom: false },
    { id: 'opportunities', name: 'Opportunities', icon: 'trending-up', desc: 'Sales pipeline and active deals', is_custom: false },
    { id: 'tasks', name: 'Tasks', icon: 'check-square', desc: 'Activities, reminders, and action items', is_custom: false },
    { id: 'purchase_deals', name: 'Purchase Deals', icon: 'shopping-cart', desc: 'Procurement contracts & supplier deals', is_custom: false },
    { id: 'shipping', name: 'Shipping & Logistics', icon: 'truck', desc: 'Freight dispatch and customs tracking', is_custom: false },
    { id: 'payments', name: 'Payments & Invoicing', icon: 'credit-card', desc: 'AR/AP invoices and receivables', is_custom: false },
    { id: 'wires', name: 'Wires & Reconciliation', icon: 'landmark', desc: 'UTR bank transaction settlements', is_custom: false },
    { id: 'users', name: 'Users Directory', icon: 'shield', desc: 'Internal personnel and seat licenses', is_custom: false }
];

window.INITIAL_PROFILES = @json($profiles ?? []);
window.INITIAL_SELECTED_PROFILE_ID = '{{ $selectedProfileId ?? "" }}';
window.INITIAL_CUSTOM_OBJECTS = @json($customObjects ?? []);
window.INITIAL_RECORD_PERMISSIONS = @json($initialPermissions ?? []);
window.CSRF_TOKEN = '{{ csrf_token() }}';

let profiles = Array.isArray(window.INITIAL_PROFILES) ? [...window.INITIAL_PROFILES] : [];
let selectedProfileId = window.INITIAL_SELECTED_PROFILE_ID || (profiles.length > 0 ? profiles[0].id : 'prof-admin');
let customObjects = Array.isArray(window.INITIAL_CUSTOM_OBJECTS) ? [...window.INITIAL_CUSTOM_OBJECTS] : [];
let recordPerms = Array.isArray(window.INITIAL_RECORD_PERMISSIONS) && window.INITIAL_RECORD_PERMISSIONS.length > 0
    ? [...window.INITIAL_RECORD_PERMISSIONS]
    : [];

let activeFilter = 'all';
let searchQuery = '';

function initRecordRights() {
    if (profiles.length === 0) {
        profiles = [
            { id: 'prof-admin', name: 'System Administrator', is_admin: 1 },
            { id: 'prof-sales', name: 'Standard Sales Representative', is_admin: 0 },
            { id: 'prof-procurement', name: 'Procurement Specialist', is_admin: 0 },
            { id: 'prof-auditor', name: 'Read-Only Compliance Auditor', is_admin: 0 }
        ];
    }
    if (!profiles.some(p => String(p.id) === String(selectedProfileId))) {
        selectedProfileId = profiles[0].id;
    }

    renderProfileOptions();
    renderCustomPresetOptions();

    if (recordPerms.length === 0) {
        loadPermissionsForProfile(selectedProfileId);
    } else {
        renderMatrixRows();
        updateKpiCounters();
    }
}

function renderProfileOptions() {
    const select = document.getElementById('selectedProfileSelect');
    if (!select) return;

    select.innerHTML = profiles.map(p => `
        <option value="${p.id}" ${String(p.id) === String(selectedProfileId) ? 'selected' : ''}>
            ${escapeHtml(p.name)}
        </option>
    `).join('');

    renderGovernanceBadge();
}

function renderGovernanceBadge() {
    const badgeEl = document.getElementById('profileGovernanceBadge');
    if (!badgeEl) return;

    const currentProfile = profiles.find(p => String(p.id) === String(selectedProfileId));
    if (currentProfile && (currentProfile.is_admin || String(currentProfile.id) === 'prof-admin')) {
        badgeEl.innerHTML = `
            <span style="font-size: 0.68rem; font-weight: 700; background: #e0e7ff; color: #4338ca; border: 1px solid #c7d2fe; padding: 0.15rem 0.5rem; border-radius: 9999px; display: inline-flex; align-items: center; gap: 0.25rem;">
                👑 Full Organization Administrator
            </span>
        `;
    } else {
        badgeEl.innerHTML = `
            <span style="font-size: 0.68rem; font-weight: 600; background: #f4f4f5; color: #52525b; border: 1px solid #e4e4e7; padding: 0.15rem 0.5rem; border-radius: 9999px;">
                Custom Access Profile
            </span>
        `;
    }
}

function renderCustomPresetOptions() {
    const presetSelect = document.getElementById('customObjectPresetSelect');
    if (!presetSelect) return;

    let html = '<option value="">-- Choose Registered Custom Object (or type below) --</option>';
    customObjects.forEach(co => {
        const key = `obj_${co.api_name || co.label.toLowerCase().replace(/\\s+/g, '_')}`;
        html += `<option value="${key}">${escapeHtml(co.label)} (${key})</option>`;
    });
    presetSelect.innerHTML = html;
}

function handleCustomPresetChange(val) {
    const input = document.getElementById('newCustomModuleKey');
    if (input && val) {
        input.value = val;
    }
}

function handleProfileChange(newProfileId) {
    selectedProfileId = newProfileId;
    renderGovernanceBadge();
    loadPermissionsForProfile(selectedProfileId);
}

async function loadPermissionsForProfile(profileId) {
    try {
        const response = await fetch(`/api/settings/permissions/${encodeURIComponent(profileId)}`, {
            headers: { 'Accept': 'application/json' }
        });
        const result = await response.json();
        if (result && Array.isArray(result.recordPermissions) && result.recordPermissions.length > 0) {
            recordPerms = result.recordPermissions;
        } else {
            recordPerms = getDefaultPermsForProfile(profileId);
        }
    } catch (e) {
        console.warn('Failed to load permissions from API, using defaults:', e);
        recordPerms = getDefaultPermsForProfile(profileId);
    }

    renderMatrixRows();
    updateKpiCounters();
}

function getAllAvailableModules() {
    const map = new Map();

    STANDARD_MODULES_DEF.forEach(m => map.set(m.id, { ...m }));

    customObjects.forEach(co => {
        const key = `obj_${co.api_name || co.label.toLowerCase().replace(/\\s+/g, '_')}`;
        map.set(key, {
            id: key,
            name: co.label + ' (Custom)',
            icon: 'box',
            desc: co.description || 'Custom object module',
            is_custom: true
        });
    });

    recordPerms.forEach(rp => {
        if (!map.has(rp.module)) {
            const isCustom = rp.module.startsWith('obj_') || !STANDARD_MODULES_DEF.some(s => s.id === rp.module);
            map.set(rp.module, {
                id: rp.module,
                name: rp.module.replace('obj_', '').replace(/_/g, ' ').replace(/\\b\\w/g, l => l.toUpperCase()),
                icon: isCustom ? 'box' : 'layers',
                desc: isCustom ? 'Registered custom entity' : 'CRM Module',
                is_custom: isCustom
            });
        }
    });

    return Array.from(map.values());
}

function getDefaultPermsForProfile(profileId) {
    const all = getAllAvailableModules();
    const currentProfile = profiles.find(p => String(p.id) === String(profileId));
    const isAdmin = currentProfile ? Boolean(currentProfile.is_admin) : profileId === 'prof-admin';

    return all.map(mod => {
        if (isAdmin || profileId === 'prof-admin') {
            return {
                module: mod.id,
                can_create: 1,
                can_read: 1,
                can_update: 1,
                can_delete: 1,
                can_transfer: 1,
                record_scope: 'All'
            };
        }
        if (profileId === 'prof-sales') {
            const isSales = ['leads', 'accounts', 'contacts', 'opportunities', 'tasks'].includes(mod.id);
            return {
                module: mod.id,
                can_create: isSales ? 1 : 0,
                can_read: 1,
                can_update: isSales ? 1 : 0,
                can_delete: isSales ? 1 : 0,
                can_transfer: ['leads', 'accounts'].includes(mod.id) ? 1 : 0,
                record_scope: isSales ? 'Team' : 'All'
            };
        }
        if (profileId === 'prof-procurement') {
            const isProc = ['accounts', 'purchase_deals', 'shipping', 'payments', 'wires', 'tasks'].includes(mod.id);
            return {
                module: mod.id,
                can_create: isProc ? 1 : 0,
                can_read: 1,
                can_update: isProc ? 1 : 0,
                can_delete: isProc ? 1 : 0,
                can_transfer: 0,
                record_scope: isProc ? 'Team' : 'All'
            };
        }
        if (profileId === 'prof-auditor') {
            return {
                module: mod.id,
                can_create: 0,
                can_read: 1,
                can_update: 0,
                can_delete: 0,
                can_transfer: 0,
                record_scope: 'All'
            };
        }
        return {
            module: mod.id,
            can_create: 1,
            can_read: 1,
            can_update: 1,
            can_delete: 0,
            can_transfer: 0,
            record_scope: 'All'
        };
    });
}

function getFilteredModules() {
    let list = getAllAvailableModules();

    if (searchQuery.trim()) {
        const q = searchQuery.toLowerCase().trim();
        list = list.filter(m => m.name.toLowerCase().includes(q) || m.id.toLowerCase().includes(q));
    }

    if (activeFilter === 'standard') {
        list = list.filter(m => !m.is_custom);
    } else if (activeFilter === 'custom') {
        list = list.filter(m => m.is_custom);
    } else if (activeFilter === 'full_crud') {
        list = list.filter(m => {
            const perm = recordPerms.find(p => p.module === m.id);
            return perm && perm.can_create && perm.can_read && perm.can_update && perm.can_delete;
        });
    } else if (activeFilter === 'read_only') {
        list = list.filter(m => {
            const perm = recordPerms.find(p => p.module === m.id);
            return perm && perm.can_read && !perm.can_create && !perm.can_update && !perm.can_delete;
        });
    }

    return list;
}

function renderMatrixRows() {
    const tbody = document.getElementById('matrixTableBody');
    if (!tbody) return;

    const visibleModules = getFilteredModules();

    if (visibleModules.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" style="text-align: center; padding: 2.5rem 1rem; color: var(--zinc-400);">
                    <i data-lucide="shield-alert" style="width: 28px; height: 28px; margin: 0 auto 0.5rem auto; display: block; opacity: 0.5;"></i>
                    <div style="font-weight: 500;">No modules found matching your search filter</div>
                    <button type="button" onclick="handleClearFilters()" class="btn btn-secondary btn-sm" style="margin-top: 0.65rem;">Clear Filters</button>
                </td>
            </tr>
        `;
        if (window.lucide) lucide.createIcons();
        return;
    }

    tbody.innerHTML = visibleModules.map(mod => {
        const perm = recordPerms.find(p => p.module === mod.id);
        const canCreate = perm ? Boolean(perm.can_create) : true;
        const canRead = perm ? Boolean(perm.can_read) : true;
        const canUpdate = perm ? Boolean(perm.can_update) : true;
        const canDelete = perm ? Boolean(perm.can_delete) : false;
        const canTransfer = perm ? (perm.can_transfer !== undefined ? Boolean(perm.can_transfer) : false) : false;
        const scope = perm?.record_scope || 'All';

        return `
            <tr id="row-${mod.id}">
                <td style="padding: 0.85rem 1.15rem;">
                    <div style="display: flex; align-items: center; gap: 0.65rem;">
                        <div style="width: 32px; height: 32px; border-radius: 8px; background: ${mod.is_custom ? '#f5f3ff' : '#f4f4f5'}; color: ${mod.is_custom ? '#7c3aed' : '#52525b'}; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i data-lucide="${mod.icon || 'box'}" style="width: 16px; height: 16px;"></i>
                        </div>
                        <div>
                            <div style="display: flex; align-items: center; gap: 0.35rem;">
                                <strong style="color: var(--zinc-950); font-size: 0.8125rem;">${escapeHtml(mod.name)}</strong>
                                ${mod.is_custom ? `
                                    <span style="font-size: 0.625rem; font-weight: 600; background: #f5f3ff; color: #7c3aed; border: 1px solid #ddd6fe; padding: 0.05rem 0.35rem; border-radius: 4px;">
                                        Custom
                                    </span>
                                ` : ''}
                            </div>
                            <div style="font-size: 0.7rem; color: var(--zinc-400); font-family: monospace;">${escapeHtml(mod.id)}</div>
                        </div>
                    </div>
                </td>

                <td style="text-align: center; padding: 0.75rem 0.85rem;">
                    <label class="perm-checkbox-label ${canCreate ? 'is-allowed' : 'is-blocked'}">
                        <input
                            type="checkbox"
                            ${canCreate ? 'checked' : ''}
                            onchange="updateRecordPerm('${mod.id}', 'can_create', this.checked)"
                            style="cursor: pointer; width: 14px; height: 14px; accent-color: #059669;"
                        >
                        <span>${canCreate ? 'Allow' : 'Block'}</span>
                    </label>
                </td>

                <td style="text-align: center; padding: 0.75rem 0.85rem;">
                    <label class="perm-checkbox-label ${canRead ? 'is-allowed' : 'is-blocked'}">
                        <input
                            type="checkbox"
                            ${canRead ? 'checked' : ''}
                            onchange="updateRecordPerm('${mod.id}', 'can_read', this.checked)"
                            style="cursor: pointer; width: 14px; height: 14px; accent-color: #059669;"
                        >
                        <span>${canRead ? 'Allow' : 'Block'}</span>
                    </label>
                </td>

                <td style="text-align: center; padding: 0.75rem 0.85rem;">
                    <label class="perm-checkbox-label ${canUpdate ? 'is-allowed' : 'is-blocked'}">
                        <input
                            type="checkbox"
                            ${canUpdate ? 'checked' : ''}
                            onchange="updateRecordPerm('${mod.id}', 'can_update', this.checked)"
                            style="cursor: pointer; width: 14px; height: 14px; accent-color: #059669;"
                        >
                        <span>${canUpdate ? 'Allow' : 'Block'}</span>
                    </label>
                </td>

                <td style="text-align: center; padding: 0.75rem 0.85rem;">
                    <label class="perm-checkbox-label ${canDelete ? 'is-allowed' : 'is-blocked'}" style="${canDelete ? 'background: #fef2f2; color: #dc2626; border-color: #fecaca;' : ''}">
                        <input
                            type="checkbox"
                            ${canDelete ? 'checked' : ''}
                            onchange="updateRecordPerm('${mod.id}', 'can_delete', this.checked)"
                            style="cursor: pointer; width: 14px; height: 14px; accent-color: #dc2626;"
                        >
                        <span>${canDelete ? 'Allow' : 'Block'}</span>
                    </label>
                </td>

                <td style="text-align: center; padding: 0.75rem 0.85rem;">
                    <label class="perm-checkbox-label ${canTransfer ? 'is-allowed' : 'is-blocked'}" style="${canTransfer ? 'background: #eff6ff; color: #2563eb; border-color: #bfdbfe;' : ''}">
                        <input
                            type="checkbox"
                            ${canTransfer ? 'checked' : ''}
                            onchange="updateRecordPerm('${mod.id}', 'can_transfer', this.checked)"
                            style="cursor: pointer; width: 14px; height: 14px; accent-color: #2563eb;"
                        >
                        <span>${canTransfer ? 'Transfer' : 'None'}</span>
                    </label>
                </td>

                <td style="padding: 0.75rem 1rem;">
                    <select
                        class="form-select"
                        onchange="updateRecordScope('${mod.id}', this.value)"
                        style="
                            padding: 0.3rem 0.65rem;
                            font-size: 0.75rem;
                            width: 100%;
                            height: 32px;
                            font-weight: 600;
                            border-color: ${scope === 'All' ? '#c7d2fe' : (scope === 'Team' ? '#fde68a' : '#e4e4e7')};
                            background: ${scope === 'All' ? '#f5f7ff' : (scope === 'Team' ? '#fffdf5' : '#ffffff')};
                            color: ${scope === 'All' ? '#3730a3' : (scope === 'Team' ? '#92400e' : '#334155')};
                        "
                    >
                        <option value="All" ${scope === 'All' ? 'selected' : ''}>🌐 All Records (Global)</option>
                        <option value="Team" ${scope === 'Team' ? 'selected' : ''}>👥 Team Records (Hierarchy)</option>
                        <option value="Own" ${scope === 'Own' ? 'selected' : ''}>👤 Own Records Only</option>
                    </select>
                </td>

                <td style="text-align: right; padding: 0.75rem 1.15rem;">
                    <div style="display: inline-flex; align-items: center; gap: 0.35rem;">
                        <button
                            type="button"
                            onclick="handleSaveSingleRow('${mod.id}', this)"
                            class="btn btn-secondary btn-sm"
                            style="padding: 0.25rem 0.45rem; font-size: 0.7rem;"
                            title="Save rights for this module"
                        >
                            <i data-lucide="check" style="width: 13px; height: 13px; color: #16a34a;"></i>
                        </button>
                        ${mod.is_custom ? `
                            <button
                                type="button"
                                onclick="handleDeleteCustomRow('${mod.id}')"
                                class="btn btn-secondary btn-sm"
                                style="padding: 0.25rem 0.45rem; font-size: 0.7rem; color: #dc2626;"
                                title="Remove custom object permission from profile"
                            >
                                <i data-lucide="trash-2" style="width: 13px; height: 13px;"></i>
                            </button>
                        ` : ''}
                    </div>
                </td>
            </tr>
        `;
    }).join('');

    if (window.lucide) lucide.createIcons();
}

function updateRecordPerm(modId, field, checked) {
    let perm = recordPerms.find(p => p.module === modId);
    if (perm) {
        perm[field] = checked ? 1 : 0;
    } else {
        perm = {
            module: modId,
            can_create: field === 'can_create' ? 1 : 0,
            can_read: field === 'can_read' ? 1 : 0,
            can_update: field === 'can_update' ? 1 : 0,
            can_delete: field === 'can_delete' ? 1 : 0,
            can_transfer: field === 'can_transfer' ? 1 : 0,
            record_scope: 'All'
        };
        recordPerms.push(perm);
    }

    renderMatrixRows();
    updateKpiCounters();
}

function updateRecordScope(modId, scope) {
    let perm = recordPerms.find(p => p.module === modId);
    if (perm) {
        perm.record_scope = scope;
    } else {
        perm = {
            module: modId,
            can_create: 1,
            can_read: 1,
            can_update: 1,
            can_delete: 0,
            can_transfer: 0,
            record_scope: scope
        };
        recordPerms.push(perm);
    }

    renderMatrixRows();
    updateKpiCounters();
}

function updateKpiCounters() {
    const all = getAllAvailableModules();
    const totalCount = all.length;

    let createCount = 0;
    let deleteCount = 0;
    let transferCount = 0;
    let scopeAll = 0, scopeTeam = 0, scopeOwn = 0;

    all.forEach(mod => {
        const perm = recordPerms.find(p => p.module === mod.id);
        if (perm) {
            if (perm.can_create) createCount++;
            if (perm.can_delete) deleteCount++;
            if (perm.can_transfer) transferCount++;
            if (perm.record_scope === 'Team') scopeTeam++;
            else if (perm.record_scope === 'Own') scopeOwn++;
            else scopeAll++;
        }
    });

    const elTotal = document.getElementById('kpiTotalModules');
    const elCreate = document.getElementById('kpiCreateCount');
    const elDelete = document.getElementById('kpiDeleteCount');
    const elTransfer = document.getElementById('kpiTransferCount');
    const elScope = document.getElementById('kpiScopeBreakdown');
    const pillCount = document.getElementById('pillCountAll');

    if (elTotal) elTotal.textContent = totalCount;
    if (elCreate) elCreate.textContent = `${createCount} / ${totalCount}`;
    if (elDelete) elDelete.textContent = `${deleteCount} / ${totalCount}`;
    if (elTransfer) elTransfer.textContent = `${transferCount} / ${totalCount}`;
    if (elScope) elScope.textContent = `All: ${scopeAll} | Team: ${scopeTeam} | Own: ${scopeOwn}`;
    if (pillCount) pillCount.textContent = totalCount;
}

function setQuickFilter(filter) {
    activeFilter = filter;

    const pills = ['all', 'standard', 'custom', 'full_crud', 'read_only'];
    pills.forEach(p => {
        const btn = document.getElementById(`pill${p.charAt(0).toUpperCase() + p.slice(1).replace(/_([a-z])/g, g => g[1].toUpperCase())}`);
        if (btn) {
            if (p === filter) {
                btn.classList.add('is-active');
            } else {
                btn.classList.remove('is-active');
            }
        }
    });

    renderMatrixRows();
}

function handleSearchChange(val) {
    searchQuery = val;
    renderMatrixRows();
}

function handleClearFilters() {
    searchQuery = '';
    const input = document.getElementById('moduleSearchInput');
    if (input) input.value = '';
    setQuickFilter('all');
}

function toggleColumnBatch(field) {
    const visible = getFilteredModules();
    if (visible.length === 0) return;

    // Check if majority are already on
    const currentOn = visible.filter(m => {
        const p = recordPerms.find(rp => rp.module === m.id);
        return p ? Boolean(p[field]) : false;
    }).length;

    const targetVal = currentOn < visible.length ? 1 : 0;

    visible.forEach(m => {
        let perm = recordPerms.find(rp => rp.module === m.id);
        if (!perm) {
            perm = {
                module: m.id,
                can_create: 1,
                can_read: 1,
                can_update: 1,
                can_delete: 0,
                can_transfer: 0,
                record_scope: 'All'
            };
            recordPerms.push(perm);
        }
        perm[field] = targetVal;
    });

    renderMatrixRows();
    updateKpiCounters();
    showFeedback(`Toggled "${field.replace('can_', '')}" for ${visible.length} visible modules.`, 'success');
}

function batchSetAllAccess(isFull) {
    const visible = getFilteredModules();
    if (visible.length === 0) return;

    visible.forEach(m => {
        let perm = recordPerms.find(rp => rp.module === m.id);
        if (!perm) {
            perm = { module: m.id, record_scope: 'All' };
            recordPerms.push(perm);
        }
        perm.can_create = isFull ? 1 : 0;
        perm.can_read = 1;
        perm.can_update = isFull ? 1 : 0;
        perm.can_delete = isFull ? 1 : 0;
        perm.can_transfer = isFull ? 1 : 0;
    });

    renderMatrixRows();
    updateKpiCounters();
    showFeedback(`Set ${visible.length} modules to ${isFull ? 'Full Access' : 'Read-Only'}.`, 'success');
}

function batchSetScope(scopeVal) {
    if (!scopeVal) return;
    const visible = getFilteredModules();
    if (visible.length === 0) return;

    visible.forEach(m => {
        let perm = recordPerms.find(rp => rp.module === m.id);
        if (!perm) {
            perm = {
                module: m.id,
                can_create: 1,
                can_read: 1,
                can_update: 1,
                can_delete: 0,
                can_transfer: 0
            };
            recordPerms.push(perm);
        }
        perm.record_scope = scopeVal;
    });

    renderMatrixRows();
    updateKpiCounters();
    showFeedback(`Record scope set to "${scopeVal}" for ${visible.length} modules.`, 'success');
}

async function handleSaveSingleRow(modId, btn) {
    const perm = recordPerms.find(p => p.module === modId);
    if (!perm) return;

    const originalContent = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = `<i data-lucide="loader-2" style="width: 13px; height: 13px; animation: spin 1s linear infinite;"></i>`;
    if (window.lucide) lucide.createIcons();

    try {
        const response = await fetch(`/api/settings/permissions/${encodeURIComponent(selectedProfileId)}/module`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': window.CSRF_TOKEN
            },
            body: JSON.stringify({
                module: modId,
                can_create: perm.can_create,
                can_read: perm.can_read,
                can_update: perm.can_update,
                can_delete: perm.can_delete,
                can_transfer: perm.can_transfer,
                record_scope: perm.record_scope,
                _token: window.CSRF_TOKEN
            })
        });

        const res = await response.json();
        if (response.ok && res.success) {
            showFeedback(`Rights for module "${modId}" saved successfully!`, 'success');
        } else {
            showFeedback(res.message || 'Error saving module rights.', 'error');
        }
    } catch (e) {
        console.error(e);
        showFeedback('Network error saving row.', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalContent;
        if (window.lucide) lucide.createIcons();
    }
}

async function handleDeleteCustomRow(modId) {
    if (!confirm(`Remove permission right for custom object "${modId}"?`)) return;

    try {
        const response = await fetch(`/api/settings/permissions/${encodeURIComponent(selectedProfileId)}/module/${encodeURIComponent(modId)}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': window.CSRF_TOKEN
            }
        });

        const res = await response.json();
        if (response.ok && res.success) {
            recordPerms = recordPerms.filter(p => p.module !== modId);
            renderMatrixRows();
            updateKpiCounters();
            showFeedback(`Permission right for "${modId}" removed.`, 'success');
        } else {
            showFeedback(res.message || 'Error deleting custom right.', 'error');
        }
    } catch (e) {
        console.error(e);
        showFeedback('Network error deleting custom row.', 'error');
    }
}

async function handleSavePermissions() {
    const saveBtn = document.getElementById('saveMatrixBtn');
    const saveText = document.getElementById('saveMatrixText');

    saveBtn.disabled = true;
    saveText.textContent = 'Saving...';

    const payload = {
        recordPermissions: recordPerms,
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
            showFeedback('Permissions matrix updated successfully in database!', 'success');
        } else {
            showFeedback(result.message || 'Error saving permissions.', 'error');
        }
    } catch (e) {
        console.error('Error saving permissions:', e);
        showFeedback('Network error while saving permissions.', 'error');
    } finally {
        saveBtn.disabled = false;
        saveText.textContent = 'Save Matrix';
    }
}

// ============================================================================
// MODAL CONTROLS & HANDLERS
// ============================================================================
function openCloneModal() {
    const sourceSelect = document.getElementById('cloneSourceProfileSelect');
    if (sourceSelect) {
        sourceSelect.innerHTML = profiles
            .filter(p => String(p.id) !== String(selectedProfileId))
            .map(p => `<option value="${p.id}">${escapeHtml(p.name)}</option>`)
            .join('');
    }
    const modal = document.getElementById('cloneModal');
    if (modal) {
        modal.classList.add('is-open');
        modal.style.display = 'flex';
    }
}

function closeCloneModal() {
    const modal = document.getElementById('cloneModal');
    if (modal) {
        modal.classList.remove('is-open');
        modal.style.display = 'none';
    }
}

async function handleCloneSubmit(e) {
    e.preventDefault();
    const sourceProfileId = document.getElementById('cloneSourceProfileSelect').value;
    const includeFls = document.getElementById('cloneIncludeFlsCheckbox').checked;

    const btn = document.getElementById('cloneSubmitBtn');
    const btnText = document.getElementById('cloneSubmitText');
    btn.disabled = true;
    btnText.textContent = 'Cloning...';

    try {
        const response = await fetch(`/api/settings/permissions/${encodeURIComponent(selectedProfileId)}/clone`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': window.CSRF_TOKEN
            },
            body: JSON.stringify({
                source_profile_id: sourceProfileId,
                include_field_permissions: includeFls,
                _token: window.CSRF_TOKEN
            })
        });

        const res = await response.json();
        if (response.ok && res.success) {
            closeCloneModal();
            showFeedback(res.message || 'Rights cloned successfully!', 'success');
            await loadPermissionsForProfile(selectedProfileId);
        } else {
            alert(res.message || 'Error cloning permissions.');
        }
    } catch (err) {
        alert('Network error while cloning rights: ' + err.message);
    } finally {
        btn.disabled = false;
        btnText.textContent = 'Apply & Clone Rights';
    }
}

function openAddCustomModal() {
    const modal = document.getElementById('addCustomModal');
    if (modal) {
        modal.classList.add('is-open');
        modal.style.display = 'flex';
    }
}

function closeAddCustomModal() {
    const modal = document.getElementById('addCustomModal');
    if (modal) {
        modal.classList.remove('is-open');
        modal.style.display = 'none';
    }
}

async function handleAddCustomSubmit(e) {
    e.preventDefault();
    const key = document.getElementById('newCustomModuleKey').value.trim();
    if (!key) return;

    const canCreate = document.getElementById('newModCreate').checked;
    const canRead = document.getElementById('newModRead').checked;
    const canUpdate = document.getElementById('newModUpdate').checked;
    const canDelete = document.getElementById('newModDelete').checked;
    const canTransfer = document.getElementById('newModTransfer').checked;
    const scope = document.getElementById('newModScope').value;

    const btn = document.getElementById('addCustomSubmitBtn');
    const text = document.getElementById('addCustomSubmitText');
    btn.disabled = true;
    text.textContent = 'Registering...';

    try {
        const response = await fetch(`/api/settings/permissions/${encodeURIComponent(selectedProfileId)}/module`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': window.CSRF_TOKEN
            },
            body: JSON.stringify({
                module: key,
                can_create: canCreate,
                can_read: canRead,
                can_update: canUpdate,
                can_delete: canDelete,
                can_transfer: canTransfer,
                record_scope: scope,
                _token: window.CSRF_TOKEN
            })
        });

        const res = await response.json();
        if (response.ok && res.success) {
            closeAddCustomModal();
            showFeedback(`Custom object "${key}" rights registered successfully!`, 'success');
            await loadPermissionsForProfile(selectedProfileId);
        } else {
            alert(res.message || 'Error registering custom module rights.');
        }
    } catch (err) {
        alert('Network error registering custom object: ' + err.message);
    } finally {
        btn.disabled = false;
        text.textContent = 'Register Module Rights';
    }
}

function openResetModal() {
    const currentProfile = profiles.find(p => String(p.id) === String(selectedProfileId));
    const nameEl = document.getElementById('resetProfileNamePlaceholder');
    if (nameEl && currentProfile) {
        nameEl.textContent = `"${currentProfile.name}"`;
    }
    const modal = document.getElementById('resetModal');
    if (modal) {
        modal.classList.add('is-open');
        modal.style.display = 'flex';
    }
}

function closeResetModal() {
    const modal = document.getElementById('resetModal');
    if (modal) {
        modal.classList.remove('is-open');
        modal.style.display = 'none';
    }
}

async function handleConfirmReset() {
    const btn = document.getElementById('confirmResetBtn');
    const text = document.getElementById('confirmResetText');
    btn.disabled = true;
    text.textContent = 'Resetting...';

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

        const res = await response.json();
        if (response.ok && res.success) {
            closeResetModal();
            showFeedback(res.message || 'Permissions reset to defaults.', 'success');
            await loadPermissionsForProfile(selectedProfileId);
        } else {
            alert(res.message || 'Error resetting permissions.');
        }
    } catch (err) {
        alert('Network error resetting permissions: ' + err.message);
    } finally {
        btn.disabled = false;
        text.textContent = 'Reset to Defaults';
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

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

window.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeCloneModal();
        closeAddCustomModal();
        closeResetModal();
    }
});

document.addEventListener('DOMContentLoaded', () => {
    initRecordRights();

    // Close RR modals on overlay backdrop click
    document.querySelectorAll('.rr-modal-overlay').forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeCloneModal();
                closeAddCustomModal();
                closeResetModal();
            }
        });
    });
});
</script>
@endsection
