@extends('layouts.app')

@section('title', 'Pipeline Opportunities — DealSync Enterprise CRM')

@section('content')
<div class="opportunities-page-container animate-fade-in">
    <!-- Top Action Header -->
    <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                <span class="badge badge-blue" style="font-size: 0.7rem; font-weight: 700;">Sell-Side Revenue Pipeline</span>
                <span style="font-size: 0.725rem; color: var(--zinc-500);">Procurement PO Coupling Rule Enforced</span>
            </div>
            <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--zinc-950); letter-spacing: -0.03em; margin: 0; line-height: 1.2;">
                Pipeline Opportunities
            </h1>
            <p style="font-size: 0.8125rem; color: var(--zinc-500); margin: 0.25rem 0 0 0;">
                Multi-stage enterprise sales pipeline coupled with supplier procurement POs and win probability weighting.
            </p>
        </div>

        <div style="display: flex; align-items: center; gap: 0.6rem;">
            <!-- Filter Dropdown Trigger -->
            <button type="button" class="btn btn-secondary btn-sm" onclick="showToast('Filtered: Showing active pipeline deals')">
                <i data-lucide="filter" style="width: 14px; height: 14px;"></i>
                <span>Filter</span>
            </button>

            <!-- Export -->
            <button type="button" class="btn btn-secondary btn-sm" onclick="showToast('Exporting pipeline to CSV...')">
                <i data-lucide="download" style="width: 14px; height: 14px;"></i>
                <span>Export CSV</span>
            </button>

            <!-- Create Opportunity Button -->
            <button type="button" class="btn btn-primary btn-sm" onclick="openNewRecordModal('opportunities')">
                <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
                <span>+ Add Opportunity</span>
            </button>
        </div>
    </div>

    <!-- Top KPI Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
        <div class="card" style="padding: 1rem;">
            <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500); margin-bottom: 0.25rem;">TOTAL PIPELINE VALUE</div>
            <div style="font-size: 1.625rem; font-weight: 800; color: var(--zinc-950); letter-spacing: -0.03em;">$433,000</div>
            <span style="font-size: 0.7rem; color: var(--zinc-500); font-weight: 500;">4 Active Opportunities</span>
        </div>

        <div class="card" style="padding: 1rem;">
            <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500); margin-bottom: 0.25rem;">WEIGHTED FORECAST</div>
            <div style="font-size: 1.625rem; font-weight: 800; color: var(--color-primary); letter-spacing: -0.03em;">$324,750</div>
            <span style="font-size: 0.7rem; color: #059669; font-weight: 600;">75% Expected Realization</span>
        </div>

        <div class="card" style="padding: 1rem;">
            <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500); margin-bottom: 0.25rem;">PROCUREMENT BACKED</div>
            <div style="font-size: 1.625rem; font-weight: 800; color: #059669; letter-spacing: -0.03em;">100% Coupled</div>
            <span style="font-size: 0.7rem; color: var(--zinc-500); font-weight: 500;">Direct Supplier PO Linking</span>
        </div>

        <div class="card" style="padding: 1rem;">
            <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500); margin-bottom: 0.25rem;">AVERAGE WIN PROBABILITY</div>
            <div style="font-size: 1.625rem; font-weight: 800; color: #d97706; letter-spacing: -0.03em;">71.2%</div>
            <span style="font-size: 0.7rem; color: var(--zinc-500); font-weight: 500;">High Propensity Pipeline</span>
        </div>
    </div>

    <!-- Quick Pipeline Stage Chips -->
    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem; overflow-x: auto; padding-bottom: 0.25rem;">
        <span style="font-size: 0.75rem; font-weight: 600; color: var(--zinc-500); margin-right: 0.25rem;">Pipeline Stages:</span>
        <button type="button" class="btn btn-sm opp-stage-btn" onclick="filterOppStage('all')" style="background: var(--zinc-900); color: #ffffff; border-radius: 9999px; font-size: 0.725rem; padding: 0.15rem 0.75rem;">
            All Deals ($433k)
        </button>
        <button type="button" class="btn btn-secondary btn-sm opp-stage-btn" onclick="filterOppStage('Proposal')" style="border-radius: 9999px; font-size: 0.725rem; padding: 0.15rem 0.75rem;">
            Proposal ($220k)
        </button>
        <button type="button" class="btn btn-secondary btn-sm opp-stage-btn" onclick="filterOppStage('Negotiation')" style="border-radius: 9999px; font-size: 0.725rem; padding: 0.15rem 0.75rem;">
            Negotiation ($145k)
        </button>
        <button type="button" class="btn btn-secondary btn-sm opp-stage-btn" onclick="filterOppStage('Closed Won')" style="border-radius: 9999px; font-size: 0.725rem; padding: 0.15rem 0.75rem;">
            Closed Won ($68k)
        </button>
    </div>

    <!-- Table Toolbar: Search, View Switcher (Table vs Kanban), Density, Reset Widths -->
    <div class="card" style="margin-bottom: 0.75rem; padding: 0.65rem 1rem; display: flex; gap: 0.75rem; align-items: center; justify-content: space-between; flex-wrap: wrap;">
        <!-- Left: Search Box -->
        <div style="display: flex; align-items: center; gap: 0.75rem; flex: 1; min-width: 280px;">
            <div style="position: relative; flex: 1; max-width: 380px;">
                <i data-lucide="search" style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); width: 15px; height: 15px; color: var(--zinc-400);"></i>
                <input
                    type="text"
                    id="opps-search-input"
                    placeholder="Search opportunities by name, account, PO..."
                    oninput="filterTableRows('opps-table', this.value)"
                    style="width: 100%; padding: 0.4rem 0.75rem 0.4rem 2.25rem; font-size: 0.8rem; height: 32px; border-radius: 8px; border: 1px solid var(--zinc-300); background: var(--bg-card); color: var(--text-primary);"
                >
            </div>
            <span style="font-size: 0.75rem; color: var(--zinc-500); white-space: nowrap;">
                Showing <strong>4</strong> deals
            </span>
        </div>

        <!-- Right: View Switcher, Density Toggles, Reset Widths -->
        <div style="display: flex; align-items: center; gap: 0.6rem;">
            <!-- View Switcher (Table vs Kanban) -->
            <div style="display: flex; padding: 0.2rem; background: var(--zinc-100); border-radius: var(--radius-md); border: 1px solid var(--zinc-200);">
                <button
                    type="button"
                    id="btn-opp-view-table"
                    onclick="switchOppView('table')"
                    style="display: flex; align-items: center; gap: 0.35rem; padding: 0.25rem 0.6rem; font-size: 0.75rem; font-weight: 600; color: var(--zinc-950); background: #ffffff; border: none; border-radius: 5px; cursor: pointer; box-shadow: var(--shadow-xs);"
                >
                    <i data-lucide="table" style="width: 13px; height: 13px;"></i>
                    <span>Table</span>
                </button>
                <button
                    type="button"
                    id="btn-opp-view-kanban"
                    onclick="switchOppView('kanban')"
                    style="display: flex; align-items: center; gap: 0.35rem; padding: 0.25rem 0.6rem; font-size: 0.75rem; font-weight: 500; color: var(--zinc-500); background: transparent; border: none; border-radius: 5px; cursor: pointer;"
                >
                    <i data-lucide="kanban" style="width: 13px; height: 13px;"></i>
                    <span>Kanban</span>
                </button>
            </div>

            <!-- Density Toggle -->
            <div style="display: flex; gap: 2px; background: var(--zinc-100); padding: 2px; border-radius: 6px; border: 1px solid var(--zinc-200);">
                <button type="button" class="btn btn-secondary btn-sm" onclick="setTableDensity('opps-table', 'compact')" title="Compact density (30px rows)" style="padding: 0.25rem 0.5rem; font-size: 0.7rem;">
                    Compact
                </button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="setTableDensity('opps-table', 'comfortable')" title="Comfortable density (44px rows)" style="padding: 0.25rem 0.5rem; font-size: 0.7rem;">
                    Comfortable
                </button>
            </div>

            <!-- Reset Column Widths -->
            <button type="button" class="btn btn-secondary btn-sm" onclick="resetTableColWidths('opps-table')" title="Reset column widths" style="padding: 0.25rem 0.55rem; font-size: 0.7rem;">
                <i data-lucide="rotate-ccw" style="width: 12px; height: 12px;"></i>
                <span>Reset Widths</span>
            </button>
        </div>
    </div>

    <!-- 1. TABLE VIEW CONTAINER -->
    <div id="opps-table-view" class="card-table-wrapper">
        <div class="table-scroll-container">
            <table id="opps-table" class="data-table">
                <thead>
                    <tr>
                        <!-- Master Checkbox -->
                        <th class="col-index" style="width: 58px; min-width: 58px; text-align: center;">
                            <input type="checkbox" title="Select all opportunities" style="cursor: pointer; width: 14px; height: 14px;">
                        </th>
                        <th data-col="name" style="width: 250px; min-width: 180px;">Opportunity Name</th>
                        <th data-col="account" style="width: 190px; min-width: 140px;">Client Account</th>
                        <th data-col="po" style="width: 150px; min-width: 120px;">Coupled Procurement PO</th>
                        <th data-col="amount" style="width: 150px; min-width: 120px;">Deal Value ($ USD)</th>
                        <th data-col="stage" style="width: 140px; min-width: 110px;">Current Stage</th>
                        <th data-col="prob" style="width: 130px; min-width: 100px;">Win Probability</th>
                        <th data-col="close" style="width: 140px; min-width: 110px;">Target Close Date</th>
                        <th data-col="owner" style="width: 140px; min-width: 110px;">Assigned Rep</th>
                        <th class="no-sort sticky-actions-col" style="width: 160px; min-width: 150px; text-align: right;">Record Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr data-stage="Negotiation">
                        <td class="col-index">
                            <div style="display: flex; align-items: center; justify-content: center; gap: 5px;">
                                <span style="font-size: 0.725rem; color: var(--zinc-400);">1</span>
                                <input type="checkbox" style="cursor: pointer;">
                            </div>
                        </td>
                        <td>
                            <strong style="color: var(--zinc-950); cursor: pointer;" onclick="openRecordDetailModal('Skyline Cloud — Phase 2 Compute Cluster', 'Skyline Cloud Systems', '$145,000', 'Negotiation')">
                                Skyline Cloud — Phase 2 Compute Cluster
                            </strong>
                        </td>
                        <td>Skyline Cloud Systems</td>
                        <td>
                            <span class="badge badge-indigo" title="Coupled to procurement order">PO-882194</span>
                        </td>
                        <td><strong style="color: var(--zinc-950);">$145,000</strong></td>
                        <td><span class="badge badge-amber">Negotiation</span></td>
                        <td><strong style="color: #059669;">75%</strong></td>
                        <td>In 15 Days</td>
                        <td>
                            <span style="display: inline-flex; align-items: center; gap: 5px;">
                                <span style="width: 18px; height: 18px; border-radius: 50%; background: #e0f2fe; color: #0369a1; font-size: 0.625rem; font-weight: 700; display: inline-flex; align-items: center; justify-content: center;">A</span>
                                <span>Alex Morgan</span>
                            </span>
                        </td>
                        <td class="sticky-actions-col" style="text-align: right;">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="openRecordDetailModal('Skyline Cloud — Phase 2 Compute Cluster', 'Skyline Cloud Systems', '$145,000', 'Negotiation')">Details</button>
                            <button type="button" class="btn btn-primary btn-sm" onclick="showToast('Stage advanced to Closed Won!')">Advance</button>
                        </td>
                    </tr>

                    <tr data-stage="Proposal">
                        <td class="col-index">
                            <div style="display: flex; align-items: center; justify-content: center; gap: 5px;">
                                <span style="font-size: 0.725rem; color: var(--zinc-400);">2</span>
                                <input type="checkbox" style="cursor: pointer;">
                            </div>
                        </td>
                        <td>
                            <strong style="color: var(--zinc-950); cursor: pointer;" onclick="openRecordDetailModal('Vanguard Health — Diagnostic Gateway Platform', 'Vanguard Health Tech', '$220,000', 'Proposal')">
                                Vanguard Health — Diagnostic Gateway Platform
                            </strong>
                        </td>
                        <td>Vanguard Health Tech</td>
                        <td>
                            <span class="badge badge-indigo" title="Coupled to procurement order">PO-771205</span>
                        </td>
                        <td><strong style="color: var(--zinc-950);">$220,000</strong></td>
                        <td><span class="badge badge-blue">Proposal</span></td>
                        <td><strong style="color: #059669;">65%</strong></td>
                        <td>In 30 Days</td>
                        <td>
                            <span style="display: inline-flex; align-items: center; gap: 5px;">
                                <span style="width: 18px; height: 18px; border-radius: 50%; background: #fef3c7; color: #b45309; font-size: 0.625rem; font-weight: 700; display: inline-flex; align-items: center; justify-content: center;">J</span>
                                <span>Jordan Lee</span>
                            </span>
                        </td>
                        <td class="sticky-actions-col" style="text-align: right;">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="openRecordDetailModal('Vanguard Health — Diagnostic Gateway Platform', 'Vanguard Health Tech', '$220,000', 'Proposal')">Details</button>
                            <button type="button" class="btn btn-primary btn-sm" onclick="showToast('Stage advanced to Negotiation!')">Advance</button>
                        </td>
                    </tr>

                    <tr data-stage="Closed Won">
                        <td class="col-index">
                            <div style="display: flex; align-items: center; justify-content: center; gap: 5px;">
                                <span style="font-size: 0.725rem; color: var(--zinc-400);">3</span>
                                <input type="checkbox" style="cursor: pointer;">
                            </div>
                        </td>
                        <td>
                            <strong style="color: var(--zinc-950); cursor: pointer;" onclick="openRecordDetailModal('Apex Supply Chain — Fleet Telemetry Integration', 'Apex Supply Chain', '$68,000', 'Closed Won')">
                                Apex Supply Chain — Fleet Telemetry Integration
                            </strong>
                        </td>
                        <td>Apex Supply Chain</td>
                        <td>
                            <span class="badge badge-indigo" title="Coupled to procurement order">PO-554210</span>
                        </td>
                        <td><strong style="color: var(--zinc-950);">$68,000</strong></td>
                        <td><span class="badge badge-green">Closed Won</span></td>
                        <td><strong style="color: #059669;">100%</strong></td>
                        <td>Closed</td>
                        <td>
                            <span style="display: inline-flex; align-items: center; gap: 5px;">
                                <span style="width: 18px; height: 18px; border-radius: 50%; background: #e0e7ff; color: #4338ca; font-size: 0.625rem; font-weight: 700; display: inline-flex; align-items: center; justify-content: center;">M</span>
                                <span>Marcus Brody</span>
                            </span>
                        </td>
                        <td class="sticky-actions-col" style="text-align: right;">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="openRecordDetailModal('Apex Supply Chain — Fleet Telemetry Integration', 'Apex Supply Chain', '$68,000', 'Closed Won')">Details</button>
                            <span class="badge badge-green" style="font-size: 0.7rem;">Won</span>
                        </td>
                    </tr>

                    <tr data-stage="Discovery">
                        <td class="col-index">
                            <div style="display: flex; align-items: center; justify-content: center; gap: 5px;">
                                <span style="font-size: 0.725rem; color: var(--zinc-400);">4</span>
                                <input type="checkbox" style="cursor: pointer;">
                            </div>
                        </td>
                        <td>
                            <strong style="color: var(--zinc-950); cursor: pointer;" onclick="openRecordDetailModal('SolarEnergix Grid — Substation Edge Gateway', 'SolarEnergix Grid', '$50,000', 'Discovery')">
                                SolarEnergix Grid — Substation Edge Gateway
                            </strong>
                        </td>
                        <td>SolarEnergix Grid</td>
                        <td>
                            <span class="badge badge-indigo" title="Coupled to procurement order">PO-662910</span>
                        </td>
                        <td><strong style="color: var(--zinc-950);">$50,000</strong></td>
                        <td><span class="badge badge-purple">Discovery</span></td>
                        <td><strong style="color: #059669;">45%</strong></td>
                        <td>In 45 Days</td>
                        <td>
                            <span style="display: inline-flex; align-items: center; gap: 5px;">
                                <span style="width: 18px; height: 18px; border-radius: 50%; background: #e0f2fe; color: #0369a1; font-size: 0.625rem; font-weight: 700; display: inline-flex; align-items: center; justify-content: center;">A</span>
                                <span>Alex Morgan</span>
                            </span>
                        </td>
                        <td class="sticky-actions-col" style="text-align: right;">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="openRecordDetailModal('SolarEnergix Grid — Substation Edge Gateway', 'SolarEnergix Grid', '$50,000', 'Discovery')">Details</button>
                            <button type="button" class="btn btn-primary btn-sm" onclick="showToast('Stage advanced to Proposal!')">Advance</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Table Footer -->
        <div style="padding: 0.5rem 1rem; background: var(--zinc-50); border-top: 1px solid var(--border-card); display: flex; justify-content: space-between; align-items: center; font-size: 0.725rem; color: var(--zinc-500);">
            <div>Showing <strong>4</strong> pipeline deals ($433k total revenue value)</div>
            <div style="display: flex; gap: 0.75rem; color: var(--zinc-400);">
                <span>Tip: Drag column edges to resize</span>
                <span>•</span>
                <span>Click headers to sort ▲/▼</span>
                <span>•</span>
                <span>Double click resizer to reset</span>
            </div>
        </div>
    </div>

    <!-- 2. KANBAN SWIMLANES CONTAINER (Toggleable) -->
    <div id="opps-kanban-view" style="display: none; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-top: 0.5rem;">
        <!-- Discovery -->
        <div style="background: var(--zinc-50); border: 1px solid var(--border-card); border-radius: 12px; padding: 0.85rem;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
                <div style="display: flex; align-items: center; gap: 0.45rem;">
                    <span style="width: 8px; height: 8px; border-radius: 50%; background: #a855f7;"></span>
                    <strong style="font-size: 0.8125rem; color: var(--zinc-900);">Discovery</strong>
                </div>
                <span class="badge badge-purple" style="font-size: 0.65rem;">$50k</span>
            </div>
            <div class="card" onclick="openRecordDetailModal('SolarEnergix Grid — Substation Edge Gateway', 'SolarEnergix Grid', '$50,000', 'Discovery')" style="padding: 0.75rem; cursor: pointer; border: 1px solid var(--border-card);">
                <strong style="font-size: 0.8125rem; color: var(--zinc-950); display: block;">SolarEnergix Substation Edge</strong>
                <span style="font-size: 0.725rem; color: var(--zinc-500); display: block;">SolarEnergix Grid</span>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 0.5rem;">
                    <span class="badge badge-indigo" style="font-size: 0.65rem;">PO-662910</span>
                    <strong style="font-size: 0.8rem; color: var(--zinc-900);">$50,000</strong>
                </div>
            </div>
        </div>

        <!-- Proposal -->
        <div style="background: var(--zinc-50); border: 1px solid var(--border-card); border-radius: 12px; padding: 0.85rem;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
                <div style="display: flex; align-items: center; gap: 0.45rem;">
                    <span style="width: 8px; height: 8px; border-radius: 50%; background: #3b82f6;"></span>
                    <strong style="font-size: 0.8125rem; color: var(--zinc-900);">Proposal</strong>
                </div>
                <span class="badge badge-blue" style="font-size: 0.65rem;">$220k</span>
            </div>
            <div class="card" onclick="openRecordDetailModal('Vanguard Health — Diagnostic Gateway Platform', 'Vanguard Health Tech', '$220,000', 'Proposal')" style="padding: 0.75rem; cursor: pointer; border: 1px solid var(--border-card);">
                <strong style="font-size: 0.8125rem; color: var(--zinc-950); display: block;">Vanguard Diagnostic Gateway</strong>
                <span style="font-size: 0.725rem; color: var(--zinc-500); display: block;">Vanguard Health Tech</span>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 0.5rem;">
                    <span class="badge badge-indigo" style="font-size: 0.65rem;">PO-771205</span>
                    <strong style="font-size: 0.8rem; color: var(--zinc-900);">$220,000</strong>
                </div>
            </div>
        </div>

        <!-- Negotiation -->
        <div style="background: var(--zinc-50); border: 1px solid var(--border-card); border-radius: 12px; padding: 0.85rem;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
                <div style="display: flex; align-items: center; gap: 0.45rem;">
                    <span style="width: 8px; height: 8px; border-radius: 50%; background: #f59e0b;"></span>
                    <strong style="font-size: 0.8125rem; color: var(--zinc-900);">Negotiation</strong>
                </div>
                <span class="badge badge-amber" style="font-size: 0.65rem;">$145k</span>
            </div>
            <div class="card" onclick="openRecordDetailModal('Skyline Cloud — Phase 2 Compute Cluster', 'Skyline Cloud Systems', '$145,000', 'Negotiation')" style="padding: 0.75rem; cursor: pointer; border: 1px solid var(--border-card);">
                <strong style="font-size: 0.8125rem; color: var(--zinc-950); display: block;">Skyline Phase 2 Cluster</strong>
                <span style="font-size: 0.725rem; color: var(--zinc-500); display: block;">Skyline Cloud Systems</span>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 0.5rem;">
                    <span class="badge badge-indigo" style="font-size: 0.65rem;">PO-882194</span>
                    <strong style="font-size: 0.8rem; color: var(--zinc-900);">$145,000</strong>
                </div>
            </div>
        </div>

        <!-- Closed Won -->
        <div style="background: var(--zinc-50); border: 1px solid var(--border-card); border-radius: 12px; padding: 0.85rem;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
                <div style="display: flex; align-items: center; gap: 0.45rem;">
                    <span style="width: 8px; height: 8px; border-radius: 50%; background: #10b981;"></span>
                    <strong style="font-size: 0.8125rem; color: var(--zinc-900);">Closed Won</strong>
                </div>
                <span class="badge badge-green" style="font-size: 0.65rem;">$68k</span>
            </div>
            <div class="card" onclick="openRecordDetailModal('Apex Supply Chain — Fleet Telemetry Integration', 'Apex Supply Chain', '$68,000', 'Closed Won')" style="padding: 0.75rem; cursor: pointer; border: 1px solid var(--border-card);">
                <strong style="font-size: 0.8125rem; color: var(--zinc-950); display: block;">Apex Fleet Telemetry</strong>
                <span style="font-size: 0.725rem; color: var(--zinc-500); display: block;">Apex Supply Chain</span>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 0.5rem;">
                    <span class="badge badge-indigo" style="font-size: 0.65rem;">PO-554210</span>
                    <strong style="font-size: 0.8rem; color: #059669;">$68,000</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function switchOppView(view) {
        const tableView = document.getElementById('opps-table-view');
        const kanbanView = document.getElementById('opps-kanban-view');
        const btnTable = document.getElementById('btn-opp-view-table');
        const btnKanban = document.getElementById('btn-opp-view-kanban');

        if (view === 'kanban') {
            tableView.style.display = 'none';
            kanbanView.style.display = 'grid';
            btnKanban.style.background = '#ffffff';
            btnKanban.style.color = 'var(--zinc-950)';
            btnKanban.style.boxShadow = 'var(--shadow-xs)';
            btnTable.style.background = 'transparent';
            btnTable.style.color = 'var(--zinc-500)';
            btnTable.style.boxShadow = 'none';
        } else {
            tableView.style.display = 'block';
            kanbanView.style.display = 'none';
            btnTable.style.background = '#ffffff';
            btnTable.style.color = 'var(--zinc-950)';
            btnTable.style.boxShadow = 'var(--shadow-xs)';
            btnKanban.style.background = 'transparent';
            btnKanban.style.color = 'var(--zinc-500)';
            btnKanban.style.boxShadow = 'none';
        }
    }

    function filterOppStage(stage) {
        const rows = document.querySelectorAll('#opps-table tbody tr');
        rows.forEach(r => {
            if (stage === 'all' || r.getAttribute('data-stage') === stage) {
                r.style.display = '';
            } else {
                r.style.display = 'none';
            }
        });
        document.querySelectorAll('.opp-stage-btn').forEach(btn => {
            btn.style.background = '';
            btn.style.color = '';
            btn.classList.add('btn-secondary');
        });
        event.currentTarget.classList.remove('btn-secondary');
        event.currentTarget.style.background = 'var(--zinc-900)';
        event.currentTarget.style.color = '#ffffff';
    }

    function openRecordDetailModal(name, account, amount, stage) {
        const modal = document.getElementById('record-detail-modal');
        if (modal) {
            const title = document.getElementById('modal-detail-title');
            if (title) title.innerText = name;
            modal.classList.add('is-open');
        }
    }
</script>
@endsection
