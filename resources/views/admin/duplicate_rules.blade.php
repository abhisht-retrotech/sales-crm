@extends('layouts.app')

@section('title', 'Duplicate Rules & Merge Studio — DealSync Admin')

@section('content')
<div class="admin-page-container animate-fade-in">
    @include('partials.admin_nav')

    <!-- Top Action Header -->
    <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                <span class="badge badge-indigo" style="font-size: 0.7rem; font-weight: 700;">Data Hygiene &amp; Deduplication Engine</span>
                <span style="font-size: 0.725rem; color: var(--zinc-500);">Fuzzy Matching, Block/Alert Interceptors &amp; Side-by-Side Merge Studio</span>
            </div>
            <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--zinc-950); letter-spacing: -0.03em; margin: 0; line-height: 1.2;">
                Duplicate Rules &amp; Merge Studio
            </h1>
            <p style="font-size: 0.8125rem; color: var(--zinc-500); margin: 0.25rem 0 0 0;">
                Prevent duplicate accounts, leads, and contacts with fuzzy matching rules, automated creation blockers, and side-by-side field merging.
            </p>
        </div>

        <div style="display: flex; align-items: center; gap: 0.6rem;">
            <button type="button" class="btn btn-secondary btn-sm" onclick="showToast('+ Add Exception Domain modal opened!')">
                <i data-lucide="globe" style="width: 14px; height: 14px;"></i>
                <span>+ Exception Domain</span>
            </button>
            <button type="button" class="btn btn-primary btn-sm" onclick="showToast('+ New Duplicate Rule modal opened!')">
                <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
                <span>+ Create Duplicate Rule</span>
            </button>
        </div>
    </div>

    <!-- KPI Summary Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
        <div class="card" style="padding: 1rem;">
            <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500); margin-bottom: 0.25rem;">ACTIVE DEDUPE RULES</div>
            <div style="font-size: 1.625rem; font-weight: 800; color: var(--color-primary); letter-spacing: -0.03em;">4 Rules</div>
            <span style="font-size: 0.7rem; color: #059669; font-weight: 600;">Leads, Accounts, Contacts Active</span>
        </div>

        <div class="card" style="padding: 1rem;">
            <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500); margin-bottom: 0.25rem;">DUPLICATES INTERCEPTED</div>
            <div style="font-size: 1.625rem; font-weight: 800; color: #d97706; letter-spacing: -0.03em;">23 Blocked</div>
            <span style="font-size: 0.7rem; color: var(--zinc-500); font-weight: 500;">99.1% Database Purity</span>
        </div>

        <div class="card" style="padding: 1rem;">
            <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500); margin-bottom: 0.25rem;">PENDING MERGE QUEUE</div>
            <div style="font-size: 1.625rem; font-weight: 800; color: #0284c7; letter-spacing: -0.03em;">2 Sets</div>
            <span style="font-size: 0.7rem; color: var(--zinc-500); font-weight: 500;">Side-by-Side Review Ready</span>
        </div>

        <div class="card" style="padding: 1rem;">
            <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500); margin-bottom: 0.25rem;">WHITELISTED DOMAINS</div>
            <div style="font-size: 1.625rem; font-weight: 800; color: #059669; letter-spacing: -0.03em;">3 Domains</div>
            <span style="font-size: 0.7rem; color: var(--zinc-500); font-weight: 500;">gmail.com, yahoo.com, outlook.com</span>
        </div>
    </div>

    <!-- Table Toolbar: Search, Density, Reset Widths -->
    <div class="card" style="margin-bottom: 0.75rem; padding: 0.65rem 1rem; display: flex; gap: 0.75rem; align-items: center; justify-content: space-between; flex-wrap: wrap;">
        <!-- Left: Search Box -->
        <div style="display: flex; align-items: center; gap: 0.75rem; flex: 1; min-width: 280px;">
            <div style="position: relative; flex: 1; max-width: 380px;">
                <i data-lucide="search" style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); width: 15px; height: 15px; color: var(--zinc-400);"></i>
                <input
                    type="text"
                    id="dedupe-search-input"
                    placeholder="Search duplicate matching rules..."
                    oninput="filterTableRows('dedupe-rules-table', this.value)"
                    style="width: 100%; padding: 0.4rem 0.75rem 0.4rem 2.25rem; font-size: 0.8rem; height: 32px; border-radius: 8px; border: 1px solid var(--zinc-300); background: var(--bg-card); color: var(--text-primary);"
                >
            </div>
            <span style="font-size: 0.75rem; color: var(--zinc-500); white-space: nowrap;">
                Showing <strong>4</strong> Active Dedupe Rules
            </span>
        </div>

        <!-- Right: Density & Reset Controls -->
        <div style="display: flex; align-items: center; gap: 0.6rem;">
            <div style="display: flex; gap: 2px; background: var(--zinc-100); padding: 2px; border-radius: 6px; border: 1px solid var(--zinc-200);">
                <button type="button" class="btn btn-secondary btn-sm" onclick="setTableDensity('dedupe-rules-table', 'compact')" title="Compact density" style="padding: 0.25rem 0.5rem; font-size: 0.7rem;">
                    Compact
                </button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="setTableDensity('dedupe-rules-table', 'comfortable')" title="Comfortable density" style="padding: 0.25rem 0.5rem; font-size: 0.7rem;">
                    Comfortable
                </button>
            </div>

            <button type="button" class="btn btn-secondary btn-sm" onclick="resetTableColWidths('dedupe-rules-table')" title="Reset column widths" style="padding: 0.25rem 0.55rem; font-size: 0.7rem;">
                <i data-lucide="rotate-ccw" style="width: 12px; height: 12px;"></i>
                <span>Reset Widths</span>
            </button>
        </div>
    </div>

    <!-- Duplicate Rules Table -->
    <div class="card-table-wrapper">
        <table class="data-table" id="dedupe-rules-table" data-storage-key="dealsync_col_widths_dedupe_rules">
            <thead>
                <tr>
                    <th style="width: 40px; text-align: center;">
                        <input type="checkbox" onchange="toggleSelectAll('dedupe-rules-table', this.checked)">
                    </th>
                    <th class="sortable" onclick="sortTable('dedupe-rules-table', 1)" data-col="name" style="min-width: 220px;">
                        Rule Name &amp; Description
                        <span class="sort-icon">▲</span>
                        <div class="col-resizer"></div>
                    </th>
                    <th class="sortable" onclick="sortTable('dedupe-rules-table', 2)" data-col="module" style="min-width: 140px;">
                        Target Entity
                        <span class="sort-icon">▲</span>
                        <div class="col-resizer"></div>
                    </th>
                    <th class="sortable" onclick="sortTable('dedupe-rules-table', 3)" data-col="matching" style="min-width: 200px;">
                        Matching Criteria Logic
                        <span class="sort-icon">▲</span>
                        <div class="col-resizer"></div>
                    </th>
                    <th class="sortable" onclick="sortTable('dedupe-rules-table', 4)" data-col="action" style="min-width: 130px;">
                        Enforcement Action
                        <span class="sort-icon">▲</span>
                        <div class="col-resizer"></div>
                    </th>
                    <th class="sortable" onclick="sortTable('dedupe-rules-table', 5)" data-col="merge_strategy" style="min-width: 160px;">
                        Merge Strategy
                        <span class="sort-icon">▲</span>
                        <div class="col-resizer"></div>
                    </th>
                    <th class="sortable" onclick="sortTable('dedupe-rules-table', 6)" data-col="status" style="min-width: 110px;">
                        Status
                        <span class="sort-icon">▲</span>
                        <div class="col-resizer"></div>
                    </th>
                    <th data-col="actions" style="text-align: right; min-width: 130px;">
                        Actions
                        <div class="col-resizer"></div>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="text-align: center;">
                        <input type="checkbox" class="row-checkbox" onchange="updateSelectedCount('dedupe-rules-table')">
                    </td>
                    <td>
                        <strong style="color: var(--zinc-900);">Lead Email &amp; Phone Deduping</strong>
                        <div style="font-size: 0.725rem; color: var(--zinc-500);">Exact match on lead corporate email or telephone</div>
                    </td>
                    <td><span class="badge badge-blue">Leads</span></td>
                    <td><code>Exact(email) OR Cleaned(phone)</code></td>
                    <td><span class="badge badge-red">Block Creation</span></td>
                    <td><span class="badge badge-indigo">Side-by-Side Review</span></td>
                    <td><span class="badge badge-green">Active</span></td>
                    <td style="text-align: right;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="showToast('Editing Lead dedupe rule')">Configure</button>
                    </td>
                </tr>

                <tr>
                    <td style="text-align: center;">
                        <input type="checkbox" class="row-checkbox" onchange="updateSelectedCount('dedupe-rules-table')">
                    </td>
                    <td>
                        <strong style="color: var(--zinc-900);">Account Domain &amp; Name Fuzzy Match</strong>
                        <div style="font-size: 0.725rem; color: var(--zinc-500);">Fuzzy match on legal entity name or web domain</div>
                    </td>
                    <td><span class="badge badge-blue">Accounts</span></td>
                    <td><code>Fuzzy(name, 85%) OR Domain(website)</code></td>
                    <td><span class="badge badge-yellow">Alert with Warning</span></td>
                    <td><span class="badge badge-purple">Keep Oldest Cascade</span></td>
                    <td><span class="badge badge-green">Active</span></td>
                    <td style="text-align: right;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="showToast('Editing Account dedupe rule')">Configure</button>
                    </td>
                </tr>

                <tr>
                    <td style="text-align: center;">
                        <input type="checkbox" class="row-checkbox" onchange="updateSelectedCount('dedupe-rules-table')">
                    </td>
                    <td>
                        <strong style="color: var(--zinc-900);">Contact Primary Email Deduping</strong>
                        <div style="font-size: 0.725rem; color: var(--zinc-500);">Unique email enforcement across account contacts</div>
                    </td>
                    <td><span class="badge badge-blue">Contacts</span></td>
                    <td><code>Exact(email) AND Exact(account_id)</code></td>
                    <td><span class="badge badge-red">Block Creation</span></td>
                    <td><span class="badge badge-indigo">Manual Only</span></td>
                    <td><span class="badge badge-green">Active</span></td>
                    <td style="text-align: right;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="showToast('Editing Contact dedupe rule')">Configure</button>
                    </td>
                </tr>

                <tr>
                    <td style="text-align: center;">
                        <input type="checkbox" class="row-checkbox" onchange="updateSelectedCount('dedupe-rules-table')">
                    </td>
                    <td>
                        <strong style="color: var(--zinc-900);">Purchase Deal PO Number Uniqueness</strong>
                        <div style="font-size: 0.725rem; color: var(--zinc-500);">Strict unique PO number check per vendor</div>
                    </td>
                    <td><span class="badge badge-indigo">Purchase Deals</span></td>
                    <td><code>Exact(po_number)</code></td>
                    <td><span class="badge badge-red">Block Creation</span></td>
                    <td><span class="badge badge-indigo">Manual Only</span></td>
                    <td><span class="badge badge-green">Active</span></td>
                    <td style="text-align: right;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="showToast('Editing PO dedupe rule')">Configure</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
