@extends('layouts.app')

@section('title', 'Tasks & Action Queue — DealSync Enterprise CRM')

@section('content')
<div class="tasks-page-container animate-fade-in">
    <!-- Top Action Header -->
    <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                <span class="badge badge-red" style="font-size: 0.7rem; font-weight: 700;">2 Overdue Alerts Active</span>
                <span style="font-size: 0.725rem; color: var(--zinc-500);">Salesforce Lightning Activity Timeline Sync</span>
            </div>
            <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--zinc-950); letter-spacing: -0.03em; margin: 0; line-height: 1.2;">
                Tasks &amp; Action Queue
            </h1>
            <p style="font-size: 0.8125rem; color: var(--zinc-500); margin: 0.25rem 0 0 0;">
                First-class standard CRM tasks with Table &amp; Kanban views, overdue notifications, and linked record autocomplete lookups.
            </p>
        </div>

        <div style="display: flex; align-items: center; gap: 0.6rem;">
            <!-- Filter Dropdown Trigger -->
            <button type="button" class="btn btn-secondary btn-sm" onclick="showToast('Filtered: Showing active tasks')">
                <i data-lucide="filter" style="width: 14px; height: 14px;"></i>
                <span>Filter</span>
            </button>

            <!-- Export -->
            <button type="button" class="btn btn-secondary btn-sm" onclick="showToast('Exporting tasks to CSV...')">
                <i data-lucide="download" style="width: 14px; height: 14px;"></i>
                <span>Export CSV</span>
            </button>

            <!-- Create Task Button -->
            <button type="button" class="btn btn-primary btn-sm" onclick="openNewRecordModal('tasks')">
                <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
                <span>+ Add Task</span>
            </button>
        </div>
    </div>

    <!-- Overdue Alert Banner -->
    <div style="padding: 0.85rem 1.25rem; background: #fff1f2; border: 1px solid #fecdd3; border-radius: 12px; color: #9f1239; margin-bottom: 1.25rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: gap; gap: 1rem;">
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <i data-lucide="alert-triangle" style="width: 20px; height: 20px; color: #e11d48; flex-shrink: 0;"></i>
            <div>
                <strong style="font-size: 0.85rem;">2 Urgent Action Items Require Attention Today</strong>
                <p style="font-size: 0.775rem; margin: 0.15rem 0 0 0; color: #be123c;">
                    Contract follow-up call with Ram Enterprises and bank wire UTR7710294822 reconciliation are past target deadlines.
                </p>
            </div>
        </div>
        <button type="button" class="btn btn-secondary btn-sm" onclick="filterTaskTab('overdue')" style="background: #ffffff; border-color: #fecdd3; color: #be123c; font-weight: 700; font-size: 0.75rem;">
            View Overdue Tasks
        </button>
    </div>

    <!-- Top KPI Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
        <div class="card" style="padding: 1rem;">
            <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500); margin-bottom: 0.25rem;">TOTAL ACTIVE QUEUE</div>
            <div style="font-size: 1.625rem; font-weight: 800; color: var(--zinc-950); letter-spacing: -0.03em;">4 Tasks</div>
            <span style="font-size: 0.7rem; color: var(--zinc-500); font-weight: 500;">Across 4 Reps</span>
        </div>

        <div class="card" style="padding: 1rem;">
            <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500); margin-bottom: 0.25rem;">DUE TODAY</div>
            <div style="font-size: 1.625rem; font-weight: 800; color: var(--color-primary); letter-spacing: -0.03em;">2 Actions</div>
            <span style="font-size: 0.7rem; color: #059669; font-weight: 600;">High Engagement Phase</span>
        </div>

        <div class="card" style="padding: 1rem;">
            <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500); margin-bottom: 0.25rem;">OVERDUE ITEMS</div>
            <div style="font-size: 1.625rem; font-weight: 800; color: #e11d48; letter-spacing: -0.03em;">2 Critical</div>
            <span style="font-size: 0.7rem; color: #e11d48; font-weight: 600;">Immediate Escalation</span>
        </div>

        <div class="card" style="padding: 1rem;">
            <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500); margin-bottom: 0.25rem;">ON-TIME VELOCITY</div>
            <div style="font-size: 1.625rem; font-weight: 800; color: #059669; letter-spacing: -0.03em;">84.5%</div>
            <span style="font-size: 0.7rem; color: var(--zinc-500); font-weight: 500;">Within SLA Thresholds</span>
        </div>
    </div>

    <!-- Quick Filter Tabs -->
    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem; overflow-x: auto; padding-bottom: 0.25rem;">
        <span style="font-size: 0.75rem; font-weight: 600; color: var(--zinc-500); margin-right: 0.25rem;">Queue Filter:</span>
        <button type="button" class="btn btn-sm task-tab-btn" onclick="filterTaskTab('all')" style="background: var(--zinc-900); color: #ffffff; border-radius: 9999px; font-size: 0.725rem; padding: 0.15rem 0.75rem;">
            All Tasks (4)
        </button>
        <button type="button" class="btn btn-secondary btn-sm task-tab-btn" onclick="filterTaskTab('Open')" style="border-radius: 9999px; font-size: 0.725rem; padding: 0.15rem 0.75rem;">
            Open (3)
        </button>
        <button type="button" class="btn btn-secondary btn-sm task-tab-btn" onclick="filterTaskTab('Pending')" style="border-radius: 9999px; font-size: 0.725rem; padding: 0.15rem 0.75rem;">
            Pending (1)
        </button>
        <button type="button" class="btn btn-secondary btn-sm task-tab-btn" onclick="filterTaskTab('today')" style="border-radius: 9999px; font-size: 0.725rem; padding: 0.15rem 0.75rem;">
            Today's (2)
        </button>
        <button type="button" class="btn btn-secondary btn-sm task-tab-btn" onclick="filterTaskTab('overdue')" style="border-radius: 9999px; font-size: 0.725rem; padding: 0.15rem 0.75rem; color: #e11d48; font-weight: 700;">
            ⚠️ Overdue (2)
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
                    id="tasks-search-input"
                    placeholder="Search tasks by subject, assigned rep..."
                    oninput="filterTableRows('tasks-table', this.value)"
                    style="width: 100%; padding: 0.4rem 0.75rem 0.4rem 2.25rem; font-size: 0.8rem; height: 32px; border-radius: 8px; border: 1px solid var(--zinc-300); background: var(--bg-card); color: var(--text-primary);"
                >
            </div>
            <span style="font-size: 0.75rem; color: var(--zinc-500); white-space: nowrap;">
                Showing <strong>4</strong> tasks
            </span>
        </div>

        <!-- Right: View Switcher, Density Toggles, Reset Widths -->
        <div style="display: flex; align-items: center; gap: 0.6rem;">
            <!-- View Switcher (Table vs Kanban) -->
            <div style="display: flex; padding: 0.2rem; background: var(--zinc-100); border-radius: var(--radius-md); border: 1px solid var(--zinc-200);">
                <button
                    type="button"
                    id="btn-task-view-table"
                    onclick="switchTaskView('table')"
                    style="display: flex; align-items: center; gap: 0.35rem; padding: 0.25rem 0.6rem; font-size: 0.75rem; font-weight: 600; color: var(--zinc-950); background: #ffffff; border: none; border-radius: 5px; cursor: pointer; box-shadow: var(--shadow-xs);"
                >
                    <i data-lucide="table" style="width: 13px; height: 13px;"></i>
                    <span>Table</span>
                </button>
                <button
                    type="button"
                    id="btn-task-view-kanban"
                    onclick="switchTaskView('kanban')"
                    style="display: flex; align-items: center; gap: 0.35rem; padding: 0.25rem 0.6rem; font-size: 0.75rem; font-weight: 500; color: var(--zinc-500); background: transparent; border: none; border-radius: 5px; cursor: pointer;"
                >
                    <i data-lucide="kanban" style="width: 13px; height: 13px;"></i>
                    <span>Kanban</span>
                </button>
            </div>

            <!-- Density Toggle -->
            <div style="display: flex; gap: 2px; background: var(--zinc-100); padding: 2px; border-radius: 6px; border: 1px solid var(--zinc-200);">
                <button type="button" class="btn btn-secondary btn-sm" onclick="setTableDensity('tasks-table', 'compact')" title="Compact density (30px rows)" style="padding: 0.25rem 0.5rem; font-size: 0.7rem;">
                    Compact
                </button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="setTableDensity('tasks-table', 'comfortable')" title="Comfortable density (44px rows)" style="padding: 0.25rem 0.5rem; font-size: 0.7rem;">
                    Comfortable
                </button>
            </div>

            <!-- Reset Column Widths -->
            <button type="button" class="btn btn-secondary btn-sm" onclick="resetTableColWidths('tasks-table')" title="Reset column widths" style="padding: 0.25rem 0.55rem; font-size: 0.7rem;">
                <i data-lucide="rotate-ccw" style="width: 12px; height: 12px;"></i>
                <span>Reset Widths</span>
            </button>
        </div>
    </div>

    <!-- 1. TABLE VIEW CONTAINER -->
    <div id="tasks-table-view" class="card-table-wrapper">
        <div class="table-scroll-container">
            <table id="tasks-table" class="data-table">
                <thead>
                    <tr>
                        <!-- Master Checkbox -->
                        <th class="col-index" style="width: 58px; min-width: 58px; text-align: center;">
                            <input type="checkbox" title="Select all tasks" style="cursor: pointer; width: 14px; height: 14px;">
                        </th>
                        <th data-col="subject" style="width: 320px; min-width: 220px;">Task Subject</th>
                        <th data-col="related" style="width: 220px; min-width: 160px;">Related CRM Record</th>
                        <th data-col="priority" style="width: 120px; min-width: 90px;">Priority Level</th>
                        <th data-col="status" style="width: 130px; min-width: 100px;">Task Status</th>
                        <th data-col="due" style="width: 200px; min-width: 150px;">Due Date &amp; Overdue Alert</th>
                        <th data-col="owner" style="width: 140px; min-width: 110px;">Assigned Rep</th>
                        <th class="no-sort sticky-actions-col" style="width: 140px; min-width: 130px; text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr data-status="Open" data-overdue="true" data-today="true">
                        <td class="col-index">
                            <div style="display: flex; align-items: center; justify-content: center; gap: 5px;">
                                <span style="font-size: 0.725rem; color: var(--zinc-400);">1</span>
                                <input type="checkbox" style="cursor: pointer;">
                            </div>
                        </td>
                        <td>
                            <strong style="color: var(--zinc-950); cursor: pointer;" onclick="openRecordDetailModal('Contract follow-up call with Ram Enterprises', 'Deal: Ram Hardware Expansion', 'High Priority', 'Open')">
                                Contract follow-up call with Ram Enterprises
                            </strong>
                        </td>
                        <td>
                            <span class="badge badge-gray">Deal: Ram Hardware Expansion</span>
                        </td>
                        <td><span class="badge badge-red">High</span></td>
                        <td><span class="badge badge-blue">Open</span></td>
                        <td><strong style="color: #e11d48;">⚠️ Today 2:00 PM (Overdue)</strong></td>
                        <td>
                            <span style="display: inline-flex; align-items: center; gap: 5px;">
                                <span style="width: 18px; height: 18px; border-radius: 50%; background: #e0f2fe; color: #0369a1; font-size: 0.625rem; font-weight: 700; display: inline-flex; align-items: center; justify-content: center;">A</span>
                                <span>Alex Morgan</span>
                            </span>
                        </td>
                        <td class="sticky-actions-col" style="text-align: right;">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="showToast('Task marked as completed!')">Complete</button>
                        </td>
                    </tr>

                    <tr data-status="Open" data-today="true">
                        <td class="col-index">
                            <div style="display: flex; align-items: center; justify-content: center; gap: 5px;">
                                <span style="font-size: 0.725rem; color: var(--zinc-400);">2</span>
                                <input type="checkbox" style="cursor: pointer;">
                            </div>
                        </td>
                        <td>
                            <strong style="color: var(--zinc-950); cursor: pointer;" onclick="openRecordDetailModal('Send updated CPQ price quote to Horizon Biotech', 'Contact: Dr. Sarah Vance', 'Normal Priority', 'Open')">
                                Send updated CPQ price quote to Horizon Biotech
                            </strong>
                        </td>
                        <td>
                            <span class="badge badge-gray">Contact: Dr. Sarah Vance</span>
                        </td>
                        <td><span class="badge badge-blue">Normal</span></td>
                        <td><span class="badge badge-blue">Open</span></td>
                        <td><strong style="color: #4f46e5;">Today 4:30 PM</strong></td>
                        <td>
                            <span style="display: inline-flex; align-items: center; gap: 5px;">
                                <span style="width: 18px; height: 18px; border-radius: 50%; background: #fef3c7; color: #b45309; font-size: 0.625rem; font-weight: 700; display: inline-flex; align-items: center; justify-content: center;">J</span>
                                <span>Jordan Lee</span>
                            </span>
                        </td>
                        <td class="sticky-actions-col" style="text-align: right;">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="showToast('Task marked as completed!')">Complete</button>
                        </td>
                    </tr>

                    <tr data-status="Pending">
                        <td class="col-index">
                            <div style="display: flex; align-items: center; justify-content: center; gap: 5px;">
                                <span style="font-size: 0.725rem; color: var(--zinc-400);">3</span>
                                <input type="checkbox" style="cursor: pointer;">
                            </div>
                        </td>
                        <td>
                            <strong style="color: var(--zinc-950); cursor: pointer;" onclick="openRecordDetailModal('Prepare quarterly business review deck for SolarEnergix Grid', 'Account: SolarEnergix Grid', 'Normal Priority', 'Pending')">
                                Prepare quarterly business review deck for SolarEnergix Grid
                            </strong>
                        </td>
                        <td>
                            <span class="badge badge-gray">Account: SolarEnergix Grid</span>
                        </td>
                        <td><span class="badge badge-blue">Normal</span></td>
                        <td><span class="badge badge-amber">Pending</span></td>
                        <td>Tomorrow 10:00 AM</td>
                        <td>
                            <span style="display: inline-flex; align-items: center; gap: 5px;">
                                <span style="width: 18px; height: 18px; border-radius: 50%; background: #e0e7ff; color: #4338ca; font-size: 0.625rem; font-weight: 700; display: inline-flex; align-items: center; justify-content: center;">M</span>
                                <span>Marcus Brody</span>
                            </span>
                        </td>
                        <td class="sticky-actions-col" style="text-align: right;">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="showToast('Task marked as completed!')">Complete</button>
                        </td>
                    </tr>

                    <tr data-status="Open" data-overdue="true">
                        <td class="col-index">
                            <div style="display: flex; align-items: center; justify-content: center; gap: 5px;">
                                <span style="font-size: 0.725rem; color: var(--zinc-400);">4</span>
                                <input type="checkbox" style="cursor: pointer;">
                            </div>
                        </td>
                        <td>
                            <strong style="color: var(--zinc-950); cursor: pointer;" onclick="openRecordDetailModal('Reconcile unallocated bank wire UTR7710294822', 'Wire: Apex Holdings', 'High Priority', 'Open')">
                                Reconcile unallocated bank wire UTR7710294822
                            </strong>
                        </td>
                        <td>
                            <span class="badge badge-gray">Wire: Apex Holdings</span>
                        </td>
                        <td><span class="badge badge-red">High</span></td>
                        <td><span class="badge badge-blue">Open</span></td>
                        <td><strong style="color: #e11d48;">⚠️ Yesterday (Overdue)</strong></td>
                        <td>
                            <span style="display: inline-flex; align-items: center; gap: 5px;">
                                <span style="width: 18px; height: 18px; border-radius: 50%; background: #ecfdf5; color: #047857; font-size: 0.625rem; font-weight: 700; display: inline-flex; align-items: center; justify-content: center;">S</span>
                                <span>Sarah Chen</span>
                            </span>
                        </td>
                        <td class="sticky-actions-col" style="text-align: right;">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="showToast('Task marked as completed!')">Complete</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Table Footer -->
        <div style="padding: 0.5rem 1rem; background: var(--zinc-50); border-top: 1px solid var(--border-card); display: flex; justify-content: space-between; align-items: center; font-size: 0.725rem; color: var(--zinc-500);">
            <div>Showing <strong>4</strong> tasks (Activity Timeline &amp; Priority SLA Monitored)</div>
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
    <div id="tasks-kanban-view" style="display: none; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-top: 0.5rem;">
        <!-- Open -->
        <div style="background: var(--zinc-50); border: 1px solid var(--border-card); border-radius: 12px; padding: 0.85rem;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
                <div style="display: flex; align-items: center; gap: 0.45rem;">
                    <span style="width: 8px; height: 8px; border-radius: 50%; background: #3b82f6;"></span>
                    <strong style="font-size: 0.8125rem; color: var(--zinc-900);">Open</strong>
                </div>
                <span class="badge badge-blue" style="font-size: 0.65rem;">3</span>
            </div>
            <div style="display: flex; flex-direction: column; gap: 0.6rem;">
                <div class="card" onclick="openRecordDetailModal('Contract follow-up call with Ram Enterprises', 'Deal: Ram Hardware Expansion', 'High Priority', 'Open')" style="padding: 0.75rem; cursor: pointer; border: 1px solid var(--border-card);">
                    <strong style="font-size: 0.8125rem; color: var(--zinc-950); display: block;">Contract Follow-up Call</strong>
                    <span style="font-size: 0.725rem; color: var(--zinc-500); display: block;">Ram Enterprises</span>
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 0.5rem;">
                        <span class="badge badge-red" style="font-size: 0.65rem;">⚠️ Overdue</span>
                        <span style="font-size: 0.68rem; color: var(--zinc-400);">Alex Morgan</span>
                    </div>
                </div>

                <div class="card" onclick="openRecordDetailModal('Send updated CPQ price quote', 'Contact: Dr. Sarah Vance', 'Normal Priority', 'Open')" style="padding: 0.75rem; cursor: pointer; border: 1px solid var(--border-card);">
                    <strong style="font-size: 0.8125rem; color: var(--zinc-950); display: block;">Send CPQ Price Quote</strong>
                    <span style="font-size: 0.725rem; color: var(--zinc-500); display: block;">Horizon Biotech</span>
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 0.5rem;">
                        <span class="badge badge-blue" style="font-size: 0.65rem;">Normal</span>
                        <span style="font-size: 0.68rem; color: var(--zinc-400);">Jordan Lee</span>
                    </div>
                </div>

                <div class="card" onclick="openRecordDetailModal('Reconcile unallocated bank wire', 'Wire: Apex Holdings', 'High Priority', 'Open')" style="padding: 0.75rem; cursor: pointer; border: 1px solid var(--border-card);">
                    <strong style="font-size: 0.8125rem; color: var(--zinc-950); display: block;">Reconcile Bank Wire</strong>
                    <span style="font-size: 0.725rem; color: var(--zinc-500); display: block;">Apex Holdings</span>
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 0.5rem;">
                        <span class="badge badge-red" style="font-size: 0.65rem;">⚠️ Overdue</span>
                        <span style="font-size: 0.68rem; color: var(--zinc-400);">Sarah Chen</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- In Progress / Pending -->
        <div style="background: var(--zinc-50); border: 1px solid var(--border-card); border-radius: 12px; padding: 0.85rem;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
                <div style="display: flex; align-items: center; gap: 0.45rem;">
                    <span style="width: 8px; height: 8px; border-radius: 50%; background: #f59e0b;"></span>
                    <strong style="font-size: 0.8125rem; color: var(--zinc-900);">Pending / In Review</strong>
                </div>
                <span class="badge badge-amber" style="font-size: 0.65rem;">1</span>
            </div>
            <div class="card" onclick="openRecordDetailModal('Prepare quarterly business review deck', 'Account: SolarEnergix Grid', 'Normal Priority', 'Pending')" style="padding: 0.75rem; cursor: pointer; border: 1px solid var(--border-card);">
                <strong style="font-size: 0.8125rem; color: var(--zinc-950); display: block;">Quarterly Business Review</strong>
                <span style="font-size: 0.725rem; color: var(--zinc-500); display: block;">SolarEnergix Grid</span>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 0.5rem;">
                    <span class="badge badge-amber" style="font-size: 0.65rem;">Tomorrow</span>
                    <span style="font-size: 0.68rem; color: var(--zinc-400);">Marcus Brody</span>
                </div>
            </div>
        </div>

        <!-- Waiting on External -->
        <div style="background: var(--zinc-50); border: 1px solid var(--border-card); border-radius: 12px; padding: 0.85rem;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
                <div style="display: flex; align-items: center; gap: 0.45rem;">
                    <span style="width: 8px; height: 8px; border-radius: 50%; background: #8b5cf6;"></span>
                    <strong style="font-size: 0.8125rem; color: var(--zinc-900);">Waiting on External</strong>
                </div>
                <span class="badge badge-purple" style="font-size: 0.65rem;">0</span>
            </div>
            <div style="padding: 1.5rem; text-align: center; color: var(--zinc-400); font-size: 0.75rem;">
                No blockers pending third-party feedback
            </div>
        </div>

        <!-- Completed -->
        <div style="background: var(--zinc-50); border: 1px solid var(--border-card); border-radius: 12px; padding: 0.85rem;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
                <div style="display: flex; align-items: center; gap: 0.45rem;">
                    <span style="width: 8px; height: 8px; border-radius: 50%; background: #10b981;"></span>
                    <strong style="font-size: 0.8125rem; color: var(--zinc-900);">Completed</strong>
                </div>
                <span class="badge badge-green" style="font-size: 0.65rem;">2</span>
            </div>
            <div style="display: flex; flex-direction: column; gap: 0.6rem;">
                <div class="card" style="padding: 0.75rem; border: 1px solid var(--border-card); opacity: 0.75;">
                    <strong style="font-size: 0.8125rem; color: var(--zinc-950); display: block; text-decoration: line-through;">Client Onboarding Call</strong>
                    <span style="font-size: 0.725rem; color: var(--zinc-500); display: block;">Apex Supply Chain</span>
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 0.5rem;">
                        <span class="badge badge-green" style="font-size: 0.65rem;">Done</span>
                        <span style="font-size: 0.68rem; color: var(--zinc-400);">Marcus Brody</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function switchTaskView(view) {
        const tableView = document.getElementById('tasks-table-view');
        const kanbanView = document.getElementById('tasks-kanban-view');
        const btnTable = document.getElementById('btn-task-view-table');
        const btnKanban = document.getElementById('btn-task-view-kanban');

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

    function filterTaskTab(tab) {
        const rows = document.querySelectorAll('#tasks-table tbody tr');
        rows.forEach(r => {
            if (tab === 'all') {
                r.style.display = '';
            } else if (tab === 'today') {
                r.style.display = r.getAttribute('data-today') === 'true' ? '' : 'none';
            } else if (tab === 'overdue') {
                r.style.display = r.getAttribute('data-overdue') === 'true' ? '' : 'none';
            } else {
                r.style.display = r.getAttribute('data-status') === tab ? '' : 'none';
            }
        });
        document.querySelectorAll('.task-tab-btn').forEach(btn => {
            btn.style.background = '';
            btn.style.color = '';
            btn.classList.add('btn-secondary');
        });
        event.currentTarget.classList.remove('btn-secondary');
        event.currentTarget.style.background = 'var(--zinc-900)';
        event.currentTarget.style.color = '#ffffff';
    }

    function openRecordDetailModal(subject, related, priority, status) {
        const modal = document.getElementById('record-detail-modal');
        if (modal) {
            const title = document.getElementById('modal-detail-title');
            if (title) title.innerText = subject;
            modal.classList.add('is-open');
        }
    }
</script>
@endsection
