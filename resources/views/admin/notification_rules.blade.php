@extends('layouts.app')

@section('title', 'Notification Rules Studio — DealSync Admin')

@section('content')
<div class="admin-page-container animate-fade-in">
    @include('partials.admin_nav')

    <!-- Top Action Header -->
    <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                <span class="badge badge-indigo" style="font-size: 0.7rem; font-weight: 700;">In-App Alert &amp; Audio Broadcast Engine</span>
                <span style="font-size: 0.725rem; color: var(--zinc-500);">Event-Triggered Toast Alerts, Audio Chimes &amp; Bell Notification Hub</span>
            </div>
            <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--zinc-950); letter-spacing: -0.03em; margin: 0; line-height: 1.2;">
                Notification Rules Studio
            </h1>
            <p style="font-size: 0.8125rem; color: var(--zinc-500); margin: 0.25rem 0 0 0;">
                Configure automated desktop toast pop-ups and chime tones triggered when records are won, payments settle, or tasks near deadlines.
            </p>
        </div>

        <div style="display: flex; align-items: center; gap: 0.6rem;">
            <button type="button" class="btn btn-secondary btn-sm" onclick="showToast('🔔 Test Audio Chime dispatched successfully!')">
                <i data-lucide="play" style="width: 14px; height: 14px;"></i>
                <span>Test Audio Chime</span>
            </button>
            <button type="button" class="btn btn-primary btn-sm" onclick="showToast('+ New Notification Rule modal opened!')">
                <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
                <span>+ Create Notification Rule</span>
            </button>
        </div>
    </div>

    <!-- KPI Summary Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
        <div class="card" style="padding: 1rem;">
            <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500); margin-bottom: 0.25rem;">NOTIFICATION RULES</div>
            <div style="font-size: 1.625rem; font-weight: 800; color: var(--color-primary); letter-spacing: -0.03em;">4 Rules</div>
            <span style="font-size: 0.7rem; color: #059669; font-weight: 600;">100% Online &amp; Active</span>
        </div>

        <div class="card" style="padding: 1rem;">
            <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500); margin-bottom: 0.25rem;">DISPATCHED TODAY</div>
            <div style="font-size: 1.625rem; font-weight: 800; color: #0284c7; letter-spacing: -0.03em;">47 Toasts</div>
            <span style="font-size: 0.7rem; color: var(--zinc-500); font-weight: 500;">Zero Delivery Latency</span>
        </div>

        <div class="card" style="padding: 1rem;">
            <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500); margin-bottom: 0.25rem;">AUDIO CHIMES ENABLED</div>
            <div style="font-size: 1.625rem; font-weight: 800; color: #d97706; letter-spacing: -0.03em;">3 Sound Tones</div>
            <span style="font-size: 0.7rem; color: var(--zinc-500); font-weight: 500;">Fanfare, Bell Chime, Subtle</span>
        </div>

        <div class="card" style="padding: 1rem;">
            <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500); margin-bottom: 0.25rem;">BELL HUB SYNC</div>
            <div style="font-size: 1.625rem; font-weight: 800; color: #059669; letter-spacing: -0.03em;">Synchronized</div>
            <span style="font-size: 0.7rem; color: #059669; font-weight: 600;">Real-Time WebSocket Link</span>
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
                    id="notif-search-input"
                    placeholder="Search notification rules by title, trigger..."
                    oninput="filterTableRows('notif-rules-table', this.value)"
                    style="width: 100%; padding: 0.4rem 0.75rem 0.4rem 2.25rem; font-size: 0.8rem; height: 32px; border-radius: 8px; border: 1px solid var(--zinc-300); background: var(--bg-card); color: var(--text-primary);"
                >
            </div>
            <span style="font-size: 0.75rem; color: var(--zinc-500); white-space: nowrap;">
                Showing <strong>4</strong> Notification Rules
            </span>
        </div>

        <!-- Right: Density & Reset Controls -->
        <div style="display: flex; align-items: center; gap: 0.6rem;">
            <div style="display: flex; gap: 2px; background: var(--zinc-100); padding: 2px; border-radius: 6px; border: 1px solid var(--zinc-200);">
                <button type="button" class="btn btn-secondary btn-sm" onclick="setTableDensity('notif-rules-table', 'compact')" title="Compact density" style="padding: 0.25rem 0.5rem; font-size: 0.7rem;">
                    Compact
                </button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="setTableDensity('notif-rules-table', 'comfortable')" title="Comfortable density" style="padding: 0.25rem 0.5rem; font-size: 0.7rem;">
                    Comfortable
                </button>
            </div>

            <button type="button" class="btn btn-secondary btn-sm" onclick="resetTableColWidths('notif-rules-table')" title="Reset column widths" style="padding: 0.25rem 0.55rem; font-size: 0.7rem;">
                <i data-lucide="rotate-ccw" style="width: 12px; height: 12px;"></i>
                <span>Reset Widths</span>
            </button>
        </div>
    </div>

    <!-- Notification Rules Table -->
    <div class="card-table-wrapper">
        <table class="data-table" id="notif-rules-table" data-storage-key="dealsync_col_widths_notif_rules">
            <thead>
                <tr>
                    <th style="width: 40px; text-align: center;">
                        <input type="checkbox" onchange="toggleSelectAll('notif-rules-table', this.checked)">
                    </th>
                    <th class="sortable" onclick="sortTable('notif-rules-table', 1)" data-col="name" style="min-width: 220px;">
                        Rule Name &amp; Alert Title
                        <span class="sort-icon">▲</span>
                        <div class="col-resizer"></div>
                    </th>
                    <th class="sortable" onclick="sortTable('notif-rules-table', 2)" data-col="module" style="min-width: 140px;">
                        Target Entity
                        <span class="sort-icon">▲</span>
                        <div class="col-resizer"></div>
                    </th>
                    <th class="sortable" onclick="sortTable('notif-rules-table', 3)" data-col="event" style="min-width: 180px;">
                        Trigger Event
                        <span class="sort-icon">▲</span>
                        <div class="col-resizer"></div>
                    </th>
                    <th class="sortable" onclick="sortTable('notif-rules-table', 4)" data-col="tone" style="min-width: 140px;">
                        Audio Chime
                        <span class="sort-icon">▲</span>
                        <div class="col-resizer"></div>
                    </th>
                    <th class="sortable" onclick="sortTable('notif-rules-table', 5)" data-col="duration" style="min-width: 130px;">
                        Duration
                        <span class="sort-icon">▲</span>
                        <div class="col-resizer"></div>
                    </th>
                    <th class="sortable" onclick="sortTable('notif-rules-table', 6)" data-col="recipients" style="min-width: 170px;">
                        Recipients
                        <span class="sort-icon">▲</span>
                        <div class="col-resizer"></div>
                    </th>
                    <th class="sortable" onclick="sortTable('notif-rules-table', 7)" data-col="status" style="min-width: 110px;">
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
                        <input type="checkbox" class="row-checkbox" onchange="updateSelectedCount('notif-rules-table')">
                    </td>
                    <td>
                        <strong style="color: var(--zinc-900);">🎉 Mega Deal Won Celebration</strong>
                        <div style="font-size: 0.725rem; color: var(--zinc-500);">Deals over $100k trigger broadcast celebration toast</div>
                    </td>
                    <td><span class="badge badge-blue">Opportunities</span></td>
                    <td><code>stage == 'Closed Won'</code></td>
                    <td><span class="badge badge-purple">🎺 Fanfare Melody</span></td>
                    <td>7000 ms</td>
                    <td><span class="badge badge-indigo">All Sales Reps</span></td>
                    <td><span class="badge badge-green">Active</span></td>
                    <td style="text-align: right;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="showToast('Testing Mega Deal alert')">Trigger Test</button>
                    </td>
                </tr>

                <tr>
                    <td style="text-align: center;">
                        <input type="checkbox" class="row-checkbox" onchange="updateSelectedCount('notif-rules-table')">
                    </td>
                    <td>
                        <strong style="color: var(--zinc-900);">⏰ Task Deadline Warning</strong>
                        <div style="font-size: 0.725rem; color: var(--zinc-500);">Urgent toast fired 24 hours prior to due date</div>
                    </td>
                    <td><span class="badge badge-blue">Tasks</span></td>
                    <td><code>due_date - now &lt;= 24h</code></td>
                    <td><span class="badge badge-yellow">🔔 Alert Chime</span></td>
                    <td>10000 ms</td>
                    <td><span class="badge badge-blue">Assigned Owner</span></td>
                    <td><span class="badge badge-green">Active</span></td>
                    <td style="text-align: right;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="showToast('Testing Task Deadline alert')">Trigger Test</button>
                    </td>
                </tr>

                <tr>
                    <td style="text-align: center;">
                        <input type="checkbox" class="row-checkbox" onchange="updateSelectedCount('notif-rules-table')">
                    </td>
                    <td>
                        <strong style="color: var(--zinc-900);">💵 Wire Transfer Credited</strong>
                        <div style="font-size: 0.725rem; color: var(--zinc-500);">Instant pop-up when bank settlement completes</div>
                    </td>
                    <td><span class="badge badge-green">Wires</span></td>
                    <td><code>status == 'Matched'</code></td>
                    <td><span class="badge badge-blue">✨ Subtle Chime</span></td>
                    <td>5000 ms</td>
                    <td><span class="badge badge-green">prof-finance</span></td>
                    <td><span class="badge badge-green">Active</span></td>
                    <td style="text-align: right;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="showToast('Testing Wire Credited alert')">Trigger Test</button>
                    </td>
                </tr>

                <tr>
                    <td style="text-align: center;">
                        <input type="checkbox" class="row-checkbox" onchange="updateSelectedCount('notif-rules-table')">
                    </td>
                    <td>
                        <strong style="color: var(--zinc-900);">🔥 Hot Inbound Lead Intake</strong>
                        <div style="font-size: 0.725rem; color: var(--zinc-500);">Fired when lead propensity score exceeds 85</div>
                    </td>
                    <td><span class="badge badge-blue">Leads</span></td>
                    <td><code>score &gt;= 85</code></td>
                    <td><span class="badge badge-yellow">🔔 Alert Chime</span></td>
                    <td>6000 ms</td>
                    <td><span class="badge badge-blue">Assigned Rep</span></td>
                    <td><span class="badge badge-green">Active</span></td>
                    <td style="text-align: right;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="showToast('Testing Hot Lead alert')">Trigger Test</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
