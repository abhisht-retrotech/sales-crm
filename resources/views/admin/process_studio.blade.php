@extends('layouts.app')

@section('title', 'ProcessStudio Automation Rules — DealSync Admin')

@section('content')
<div class="admin-page-container animate-fade-in">
    @include('partials.admin_nav')

    <!-- Top Action Header -->
    <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                <span class="badge badge-indigo" style="font-size: 0.7rem; font-weight: 700;">Visual Workflow &amp; Trigger Automation</span>
                <span style="font-size: 0.725rem; color: var(--zinc-500);">Event-Driven Processes, Field Updates, Webhooks &amp; Multi-Step Logic</span>
            </div>
            <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--zinc-950); letter-spacing: -0.03em; margin: 0; line-height: 1.2;">
                ProcessStudio Visual Flow Engine
            </h1>
            <p style="font-size: 0.8125rem; color: var(--zinc-500); margin: 0.25rem 0 0 0;">
                Build drag-and-drop business logic linking button triggers, record create/update events, scheduled timer offsets, and API webhooks.
            </p>
        </div>

        <div style="display: flex; align-items: center; gap: 0.6rem;">
            <button type="button" class="btn btn-secondary btn-sm" onclick="showToast('Process execution logs exported!')">
                <i data-lucide="activity" style="width: 14px; height: 14px;"></i>
                <span>Execution Telemetry</span>
            </button>
            <button type="button" class="btn btn-primary btn-sm" onclick="showToast('+ New ProcessFlow Designer canvas opened!')">
                <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
                <span>+ Design New Process</span>
            </button>
        </div>
    </div>

    <!-- Visual Process Flow Canvas Demonstration -->
    <div class="card" style="padding: 1.25rem; margin-bottom: 1.5rem; background: linear-gradient(135deg, #09090b 0%, #18181b 100%); color: #ffffff; border: 1px solid #27272a;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; border-bottom: 1px solid #27272a; padding-bottom: 0.75rem;">
            <div style="display: flex; align-items: center; gap: 0.6rem;">
                <span style="font-size: 1rem;">⚡</span>
                <h3 style="font-size: 0.95rem; font-weight: 700; color: #f4f4f5; margin: 0;">Featured Workflow: Opportunity Won &rarr; Procurement PO Coupling</h3>
            </div>
            <span class="badge badge-green">Production Active</span>
        </div>

        <div style="display: flex; align-items: center; gap: 0.75rem; overflow-x: auto; padding: 0.5rem 0;">
            <!-- Node 1: Trigger -->
            <div style="background: #27272a; border: 1px solid #3f3f46; border-radius: 8px; padding: 0.75rem 1rem; min-width: 200px;">
                <div style="font-size: 0.65rem; color: #a1a1aa; font-weight: 700; text-transform: uppercase;">1. EVENT TRIGGER</div>
                <div style="font-weight: 700; font-size: 0.85rem; color: #60a5fa; margin-top: 2px;">Opportunity Stage Change</div>
                <div style="font-size: 0.725rem; color: #d4d4d8;">stage == 'Closed Won'</div>
            </div>

            <i data-lucide="arrow-right" style="width: 18px; height: 18px; color: #a1a1aa; flex-shrink: 0;"></i>

            <!-- Node 2: Decision Condition -->
            <div style="background: #27272a; border: 1px solid #3f3f46; border-radius: 8px; padding: 0.75rem 1rem; min-width: 200px;">
                <div style="font-size: 0.65rem; color: #a1a1aa; font-weight: 700; text-transform: uppercase;">2. DECISION LOGIC</div>
                <div style="font-weight: 700; font-size: 0.85rem; color: #f59e0b; margin-top: 2px;">Check Linked PO</div>
                <div style="font-size: 0.725rem; color: #d4d4d8;">purchase_deal_id is present</div>
            </div>

            <i data-lucide="arrow-right" style="width: 18px; height: 18px; color: #a1a1aa; flex-shrink: 0;"></i>

            <!-- Node 3: Automated Field Action -->
            <div style="background: #27272a; border: 1px solid #3f3f46; border-radius: 8px; padding: 0.75rem 1rem; min-width: 200px;">
                <div style="font-size: 0.65rem; color: #a1a1aa; font-weight: 700; text-transform: uppercase;">3. RECORD UPDATE</div>
                <div style="font-weight: 700; font-size: 0.85rem; color: #34d399; margin-top: 2px;">Update Procurement Deal</div>
                <div style="font-size: 0.725rem; color: #d4d4d8;">PO.stage &rarr; 'Confirmed'</div>
            </div>

            <i data-lucide="arrow-right" style="width: 18px; height: 18px; color: #a1a1aa; flex-shrink: 0;"></i>

            <!-- Node 4: Outbound Webhook -->
            <div style="background: #27272a; border: 1px solid #3f3f46; border-radius: 8px; padding: 0.75rem 1rem; min-width: 200px;">
                <div style="font-size: 0.65rem; color: #a1a1aa; font-weight: 700; text-transform: uppercase;">4. OUTBOUND API</div>
                <div style="font-weight: 700; font-size: 0.85rem; color: #c084fc; margin-top: 2px;">ERP Webhook Sync</div>
                <div style="font-size: 0.725rem; color: #d4d4d8;">POST /api/erp/dispatch</div>
            </div>
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
                    id="flows-search-input"
                    placeholder="Search automated process flows..."
                    oninput="filterTableRows('process-flows-table', this.value)"
                    style="width: 100%; padding: 0.4rem 0.75rem 0.4rem 2.25rem; font-size: 0.8rem; height: 32px; border-radius: 8px; border: 1px solid var(--zinc-300); background: var(--bg-card); color: var(--text-primary);"
                >
            </div>
            <span style="font-size: 0.75rem; color: var(--zinc-500); white-space: nowrap;">
                Showing <strong>4</strong> Automated Process Flows
            </span>
        </div>

        <!-- Right: Density & Reset Controls -->
        <div style="display: flex; align-items: center; gap: 0.6rem;">
            <div style="display: flex; gap: 2px; background: var(--zinc-100); padding: 2px; border-radius: 6px; border: 1px solid var(--zinc-200);">
                <button type="button" class="btn btn-secondary btn-sm" onclick="setTableDensity('process-flows-table', 'compact')" title="Compact density" style="padding: 0.25rem 0.5rem; font-size: 0.7rem;">
                    Compact
                </button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="setTableDensity('process-flows-table', 'comfortable')" title="Comfortable density" style="padding: 0.25rem 0.5rem; font-size: 0.7rem;">
                    Comfortable
                </button>
            </div>

            <button type="button" class="btn btn-secondary btn-sm" onclick="resetTableColWidths('process-flows-table')" title="Reset column widths" style="padding: 0.25rem 0.55rem; font-size: 0.7rem;">
                <i data-lucide="rotate-ccw" style="width: 12px; height: 12px;"></i>
                <span>Reset Widths</span>
            </button>
        </div>
    </div>

    <!-- Process Flows Table -->
    <div class="card-table-wrapper">
        <table class="data-table" id="process-flows-table" data-storage-key="dealsync_col_widths_process_flows">
            <thead>
                <tr>
                    <th style="width: 40px; text-align: center;">
                        <input type="checkbox" onchange="toggleSelectAll('process-flows-table', this.checked)">
                    </th>
                    <th class="sortable" onclick="sortTable('process-flows-table', 1)" data-col="name" style="min-width: 230px;">
                        Process Name &amp; Category
                        <span class="sort-icon">▲</span>
                        <div class="col-resizer"></div>
                    </th>
                    <th class="sortable" onclick="sortTable('process-flows-table', 2)" data-col="entity" style="min-width: 140px;">
                        Parent Object
                        <span class="sort-icon">▲</span>
                        <div class="col-resizer"></div>
                    </th>
                    <th class="sortable" onclick="sortTable('process-flows-table', 3)" data-col="trigger" style="min-width: 160px;">
                        Trigger Mechanism
                        <span class="sort-icon">▲</span>
                        <div class="col-resizer"></div>
                    </th>
                    <th class="sortable" onclick="sortTable('process-flows-table', 4)" data-col="steps" style="min-width: 130px;">
                        Node Count
                        <span class="sort-icon">▲</span>
                        <div class="col-resizer"></div>
                    </th>
                    <th class="sortable" onclick="sortTable('process-flows-table', 5)" data-col="executions" style="min-width: 140px;">
                        Executions
                        <span class="sort-icon">▲</span>
                        <div class="col-resizer"></div>
                    </th>
                    <th class="sortable" onclick="sortTable('process-flows-table', 6)" data-col="status" style="min-width: 110px;">
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
                        <input type="checkbox" class="row-checkbox" onchange="updateSelectedCount('process-flows-table')">
                    </td>
                    <td>
                        <strong style="color: var(--zinc-900);">Opportunity Won &rarr; Procurement Sync</strong>
                        <div style="font-size: 0.725rem; color: var(--zinc-500);">Auto updates vendor PO upon sales closing</div>
                    </td>
                    <td><span class="badge badge-blue">Opportunities</span></td>
                    <td><span class="badge badge-purple">Record Event (On Update)</span></td>
                    <td><span class="badge badge-indigo">4 Nodes</span></td>
                    <td><strong>34 Executed</strong> (100% Success)</td>
                    <td><span class="badge badge-green">Active</span></td>
                    <td style="text-align: right;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="showToast('Opening Visual Flow Designer')">Design Flow</button>
                    </td>
                </tr>

                <tr>
                    <td style="text-align: center;">
                        <input type="checkbox" class="row-checkbox" onchange="updateSelectedCount('process-flows-table')">
                    </td>
                    <td>
                        <strong style="color: var(--zinc-900);">Lead Round-Robin Assignment &amp; Scoring</strong>
                        <div style="font-size: 0.725rem; color: var(--zinc-500);">Evaluates inbound leads and routes to sales reps</div>
                    </td>
                    <td><span class="badge badge-blue">Leads</span></td>
                    <td><span class="badge badge-blue">Record Event (On Create)</span></td>
                    <td><span class="badge badge-indigo">3 Nodes</span></td>
                    <td><strong>182 Executed</strong> (100% Success)</td>
                    <td><span class="badge badge-green">Active</span></td>
                    <td style="text-align: right;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="showToast('Opening Visual Flow Designer')">Design Flow</button>
                    </td>
                </tr>

                <tr>
                    <td style="text-align: center;">
                        <input type="checkbox" class="row-checkbox" onchange="updateSelectedCount('process-flows-table')">
                    </td>
                    <td>
                        <strong style="color: var(--zinc-900);">Banking Wire API Webhook Ingestion</strong>
                        <div style="font-size: 0.725rem; color: var(--zinc-500);">Real-time bank SWIFT/UTR ingestion and matching</div>
                    </td>
                    <td><span class="badge badge-green">Wires</span></td>
                    <td><span class="badge badge-yellow">Inbound Webhook API</span></td>
                    <td><span class="badge badge-indigo">5 Nodes</span></td>
                    <td><strong>89 Executed</strong> (100% Success)</td>
                    <td><span class="badge badge-green">Active</span></td>
                    <td style="text-align: right;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="showToast('Opening Visual Flow Designer')">Design Flow</button>
                    </td>
                </tr>

                <tr>
                    <td style="text-align: center;">
                        <input type="checkbox" class="row-checkbox" onchange="updateSelectedCount('process-flows-table')">
                    </td>
                    <td>
                        <strong style="color: var(--zinc-900);">Logistics Freight Milestone Alerter</strong>
                        <div style="font-size: 0.725rem; color: var(--zinc-500);">Dispatches tracking SMS &amp; email when cargo departs</div>
                    </td>
                    <td><span class="badge badge-blue">Shipping</span></td>
                    <td><span class="badge badge-gray">Scheduled Timer Offset</span></td>
                    <td><span class="badge badge-indigo">3 Nodes</span></td>
                    <td><strong>64 Executed</strong> (100% Success)</td>
                    <td><span class="badge badge-green">Active</span></td>
                    <td style="text-align: right;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="showToast('Opening Visual Flow Designer')">Design Flow</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
