@extends('layouts.app')

@section('title', 'Approval Process Studio — DealSync Admin')

@section('content')
<div class="admin-page-container animate-fade-in">
    @include('partials.admin_nav')

    <!-- Top Action Header -->
    <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                <span class="badge badge-indigo" style="font-size: 0.7rem; font-weight: 700;">Multi-Step Approval Workflows</span>
                <span style="font-size: 0.725rem; color: var(--zinc-500);">Sequential Tier Approvers, Rejection Rollbacks &amp; Email Dispatch</span>
            </div>
            <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--zinc-950); letter-spacing: -0.03em; margin: 0; line-height: 1.2;">
                Approval Process Studio
            </h1>
            <p style="font-size: 0.8125rem; color: var(--zinc-500); margin: 0.25rem 0 0 0;">
                Orchestrate corporate sign-off policies for high-value sales discounts, massive procurement PO commitments, and banking wire clearances.
            </p>
        </div>

        <div style="display: flex; align-items: center; gap: 0.6rem;">
            <a href="{{ url('/process-studio?flow=mega-deal-discount') }}" class="btn btn-primary btn-sm" style="display: inline-flex; align-items: center; gap: 0.4rem; text-decoration: none;">
                <i data-lucide="workflow" style="width: 14px; height: 14px;"></i>
                <span>Open Approval Flow Studio &rarr;</span>
            </a>
            <button type="button" class="btn btn-secondary btn-sm" onclick="showToast('Exporting approval logs to CSV...')">
                <i data-lucide="download" style="width: 14px; height: 14px;"></i>
                <span>Export Logs</span>
            </button>
            <button type="button" class="btn btn-secondary btn-sm" onclick="showToast('+ New Approval Process wizard opened!')">
                <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
                <span>+ Create Process</span>
            </button>
        </div>
    </div>

    <!-- KPI Summary Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
        <div class="card" style="padding: 1rem;">
            <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500); margin-bottom: 0.25rem;">ACTIVE WORKFLOWS</div>
            <div style="font-size: 1.625rem; font-weight: 800; color: var(--color-primary); letter-spacing: -0.03em;">4 Processes</div>
            <span style="font-size: 0.7rem; color: #059669; font-weight: 600;">100% Online &amp; Armed</span>
        </div>

        <div class="card" style="padding: 1rem;">
            <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500); margin-bottom: 0.25rem;">PENDING APPROVALS</div>
            <div style="font-size: 1.625rem; font-weight: 800; color: #d97706; letter-spacing: -0.03em;">2 Deals</div>
            <span style="font-size: 0.7rem; color: var(--zinc-500); font-weight: 500;">Awaiting Finance &amp; CEO</span>
        </div>

        <div class="card" style="padding: 1rem;">
            <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500); margin-bottom: 0.25rem;">AVG APPROVAL VELOCITY</div>
            <div style="font-size: 1.625rem; font-weight: 800; color: #059669; letter-spacing: -0.03em;">4.2 Hours</div>
            <span style="font-size: 0.7rem; color: var(--zinc-500); font-weight: 500;">SLA Compliance: 99.4%</span>
        </div>

        <div class="card" style="padding: 1rem;">
            <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500); margin-bottom: 0.25rem;">MULTI-TIER STEPS</div>
            <div style="font-size: 1.625rem; font-weight: 800; color: #7c3aed; letter-spacing: -0.03em;">Up to 3 Tiers</div>
            <span style="font-size: 0.7rem; color: var(--zinc-500); font-weight: 500;">Hierarchical Sign-off</span>
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
                    id="approvals-search-input"
                    placeholder="Search approval processes by name, target module..."
                    oninput="filterTableRows('approvals-table', this.value)"
                    style="width: 100%; padding: 0.4rem 0.75rem 0.4rem 2.25rem; font-size: 0.8rem; height: 32px; border-radius: 8px; border: 1px solid var(--zinc-300); background: var(--bg-card); color: var(--text-primary);"
                >
            </div>
            <span style="font-size: 0.75rem; color: var(--zinc-500); white-space: nowrap;">
                Showing <strong>4</strong> Approval Processes
            </span>
        </div>

        <!-- Right: Density & Reset Controls -->
        <div style="display: flex; align-items: center; gap: 0.6rem;">
            <div style="display: flex; gap: 2px; background: var(--zinc-100); padding: 2px; border-radius: 6px; border: 1px solid var(--zinc-200);">
                <button type="button" class="btn btn-secondary btn-sm" onclick="setTableDensity('approvals-table', 'compact')" title="Compact density" style="padding: 0.25rem 0.5rem; font-size: 0.7rem;">
                    Compact
                </button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="setTableDensity('approvals-table', 'comfortable')" title="Comfortable density" style="padding: 0.25rem 0.5rem; font-size: 0.7rem;">
                    Comfortable
                </button>
            </div>

            <button type="button" class="btn btn-secondary btn-sm" onclick="resetTableColWidths('approvals-table')" title="Reset column widths" style="padding: 0.25rem 0.55rem; font-size: 0.7rem;">
                <i data-lucide="rotate-ccw" style="width: 12px; height: 12px;"></i>
                <span>Reset Widths</span>
            </button>
        </div>
    </div>

    <!-- Approval Processes Table -->
    <div class="card-table-wrapper">
        <table class="data-table" id="approvals-table" data-storage-key="dealsync_col_widths_approvals">
            <thead>
                <tr>
                    <th style="width: 40px; text-align: center;">
                        <input type="checkbox" onchange="toggleSelectAll('approvals-table', this.checked)">
                    </th>
                    <th class="sortable" onclick="sortTable('approvals-table', 1)" data-col="name" style="min-width: 230px;">
                        Process Identifier &amp; Title
                        <span class="sort-icon">▲</span>
                        <div class="col-resizer"></div>
                    </th>
                    <th class="sortable" onclick="sortTable('approvals-table', 2)" data-col="module" style="min-width: 140px;">
                        Target Entity
                        <span class="sort-icon">▲</span>
                        <div class="col-resizer"></div>
                    </th>
                    <th class="sortable" onclick="sortTable('approvals-table', 3)" data-col="criteria" style="min-width: 200px;">
                        Trigger Criteria
                        <span class="sort-icon">▲</span>
                        <div class="col-resizer"></div>
                    </th>
                    <th class="sortable" onclick="sortTable('approvals-table', 4)" data-col="tiers" style="min-width: 180px;">
                        Approval Hierarchy
                        <span class="sort-icon">▲</span>
                        <div class="col-resizer"></div>
                    </th>
                    <th class="sortable" onclick="sortTable('approvals-table', 5)" data-col="action_on_approve" style="min-width: 170px;">
                        On Approval Action
                        <span class="sort-icon">▲</span>
                        <div class="col-resizer"></div>
                    </th>
                    <th class="sortable" onclick="sortTable('approvals-table', 6)" data-col="status" style="min-width: 110px;">
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
                        <input type="checkbox" class="row-checkbox" onchange="updateSelectedCount('approvals-table')">
                    </td>
                    <td>
                        <a href="{{ url('/process-studio?flow=mega-deal-discount') }}" style="text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem;" class="group">
                            <strong style="color: var(--color-primary, #4f46e5); font-size: 0.85rem;" class="group-hover:underline">Mega Deal Commercial Discount</strong>
                            <i data-lucide="external-link" style="width: 12px; height: 12px; color: var(--color-primary, #4f46e5);"></i>
                        </a>
                        <div style="font-size: 0.725rem; color: var(--zinc-500);">Deals exceeding $250k require VP &amp; CEO sign-off</div>
                    </td>
                    <td><span class="badge badge-blue">Opportunities</span></td>
                    <td><code>amount &gt; $250,000</code></td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 0.35rem; font-size: 0.75rem;">
                            <span class="badge badge-indigo">Step 1: Sales VP</span>
                            <span>&rarr;</span>
                            <span class="badge badge-purple">Step 2: CEO</span>
                        </div>
                    </td>
                    <td><span class="badge badge-green">stage = 'Closed Won'</span></td>
                    <td><span class="badge badge-green">Active</span></td>
                    <td style="text-align: right;">
                        <a href="{{ url('/process-studio?flow=mega-deal-discount') }}" class="btn btn-secondary btn-sm" style="text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem;">
                            <i data-lucide="workflow" style="width: 12px; height: 12px;"></i>
                            <span>Configure Flow</span>
                        </a>
                    </td>
                </tr>

                <tr>
                    <td style="text-align: center;">
                        <input type="checkbox" class="row-checkbox" onchange="updateSelectedCount('approvals-table')">
                    </td>
                    <td>
                        <a href="{{ url('/process-studio?flow=procurement-po') }}" style="text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem;" class="group">
                            <strong style="color: var(--color-primary, #4f46e5); font-size: 0.85rem;" class="group-hover:underline">High-Spend Procurement PO Sign-Off</strong>
                            <i data-lucide="external-link" style="width: 12px; height: 12px; color: var(--color-primary, #4f46e5);"></i>
                        </a>
                        <div style="font-size: 0.725rem; color: var(--zinc-500);">Supplier POs over $100k require Supply Chain Director</div>
                    </td>
                    <td><span class="badge badge-indigo">Purchase Deals</span></td>
                    <td><code>amount &gt; $100,000</code></td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 0.35rem; font-size: 0.75rem;">
                            <span class="badge badge-purple">Supply Chain Director</span>
                        </div>
                    </td>
                    <td><span class="badge badge-green">stage = 'PO Issued'</span></td>
                    <td><span class="badge badge-green">Active</span></td>
                    <td style="text-align: right;">
                        <a href="{{ url('/process-studio?flow=procurement-po') }}" class="btn btn-secondary btn-sm" style="text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem;">
                            <i data-lucide="workflow" style="width: 12px; height: 12px;"></i>
                            <span>Configure Flow</span>
                        </a>
                    </td>
                </tr>

                <tr>
                    <td style="text-align: center;">
                        <input type="checkbox" class="row-checkbox" onchange="updateSelectedCount('approvals-table')">
                    </td>
                    <td>
                        <a href="{{ url('/process-studio?flow=wire-clearance') }}" style="text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem;" class="group">
                            <strong style="color: var(--color-primary, #4f46e5); font-size: 0.85rem;" class="group-hover:underline">Bank Wire Discrepancy Clearance</strong>
                            <i data-lucide="external-link" style="width: 12px; height: 12px; color: var(--color-primary, #4f46e5);"></i>
                        </a>
                        <div style="font-size: 0.725rem; color: var(--zinc-500);">Unallocated wires require CFO validation</div>
                    </td>
                    <td><span class="badge badge-green">Wires</span></td>
                    <td><code>status == 'Unallocated'</code></td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 0.35rem; font-size: 0.75rem;">
                            <span class="badge badge-green">Corporate Controller</span>
                        </div>
                    </td>
                    <td><span class="badge badge-green">status = 'Reconciled'</span></td>
                    <td><span class="badge badge-green">Active</span></td>
                    <td style="text-align: right;">
                        <a href="{{ url('/process-studio?flow=wire-clearance') }}" class="btn btn-secondary btn-sm" style="text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem;">
                            <i data-lucide="workflow" style="width: 12px; height: 12px;"></i>
                            <span>Configure Flow</span>
                        </a>
                    </td>
                </tr>

                <tr>
                    <td style="text-align: center;">
                        <input type="checkbox" class="row-checkbox" onchange="updateSelectedCount('approvals-table')">
                    </td>
                    <td>
                        <a href="{{ url('/process-studio?flow=air-freight') }}" style="text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem;" class="group">
                            <strong style="color: var(--color-primary, #4f46e5); font-size: 0.85rem;" class="group-hover:underline">Expedited Air Freight Dispatch</strong>
                            <i data-lucide="external-link" style="width: 12px; height: 12px; color: var(--color-primary, #4f46e5);"></i>
                        </a>
                        <div style="font-size: 0.725rem; color: var(--zinc-500);">Priority air freight tariffs approval</div>
                    </td>
                    <td><span class="badge badge-blue">Shipping</span></td>
                    <td><code>carrier == 'Air Express'</code></td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 0.35rem; font-size: 0.75rem;">
                            <span class="badge badge-purple">Procurement Lead</span>
                        </div>
                    </td>
                    <td><span class="badge badge-green">status = 'In Transit'</span></td>
                    <td><span class="badge badge-green">Active</span></td>
                    <td style="text-align: right;">
                        <a href="{{ url('/process-studio?flow=air-freight') }}" class="btn btn-secondary btn-sm" style="text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem;">
                            <i data-lucide="workflow" style="width: 12px; height: 12px;"></i>
                            <span>Configure Flow</span>
                        </a>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
