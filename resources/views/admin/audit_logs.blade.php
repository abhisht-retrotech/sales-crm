@extends('layouts.app')

@section('title', 'SOC2 Compliance Audit Trail — DealSync Admin')

@section('content')
<div class="admin-page-container animate-fade-in">
    @include('partials.admin_nav')

    <!-- Top Action Header -->
    <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                <span class="badge badge-indigo" style="font-size: 0.7rem; font-weight: 700;">Immutable Governance Ledger</span>
                <span style="font-size: 0.725rem; color: var(--zinc-500);">Cryptographic Hash Verification, IP Telemetry &amp; Access Log Trail</span>
            </div>
            <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--zinc-950); letter-spacing: -0.03em; margin: 0; line-height: 1.2;">
                SOC2 Compliance &amp; Security Audit Trail
            </h1>
            <p style="font-size: 0.8125rem; color: var(--zinc-500); margin: 0.25rem 0 0 0;">
                Tamper-proof audit logs recording administrator policy updates, delegated impersonation sessions, wire settlements, and schema alterations.
            </p>
        </div>

        <div style="display: flex; align-items: center; gap: 0.6rem;">
            <button type="button" class="btn btn-secondary btn-sm" onclick="showToast('Cryptographic audit trail verified: 100% hash integrity!')">
                <i data-lucide="shield-check" style="width: 14px; height: 14px;"></i>
                <span>Verify Hashes</span>
            </button>
            <button type="button" class="btn btn-primary btn-sm" onclick="showToast('Exporting immutable audit logs to CSV...')">
                <i data-lucide="download" style="width: 14px; height: 14px;"></i>
                <span>Export Audit CSV</span>
            </button>
        </div>
    </div>

    <!-- KPI Summary Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
        <div class="card" style="padding: 1rem;">
            <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500); margin-bottom: 0.25rem;">TOTAL AUDIT EVENTS</div>
            <div style="font-size: 1.625rem; font-weight: 800; color: var(--color-primary); letter-spacing: -0.03em;">284 Events</div>
            <span style="font-size: 0.7rem; color: var(--zinc-500); font-weight: 500;">Past 30 Days Telemetry</span>
        </div>

        <div class="card" style="padding: 1rem;">
            <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500); margin-bottom: 0.25rem;">POLICY ALTERATIONS</div>
            <div style="font-size: 1.625rem; font-weight: 800; color: #0284c7; letter-spacing: -0.03em;">12 Modifies</div>
            <span style="font-size: 0.7rem; color: var(--zinc-500); font-weight: 500;">FLS, Sharing Rules, Roles</span>
        </div>

        <div class="card" style="padding: 1rem;">
            <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500); margin-bottom: 0.25rem;">TAMPER-PROOF VERIFY</div>
            <div style="font-size: 1.625rem; font-weight: 800; color: #059669; letter-spacing: -0.03em;">100% Pass</div>
            <span style="font-size: 0.7rem; color: #059669; font-weight: 600;">Zero Hash Collisions</span>
        </div>

        <div class="card" style="padding: 1rem;">
            <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500); margin-bottom: 0.25rem;">SECURITY BREACHES</div>
            <div style="font-size: 1.625rem; font-weight: 800; color: #059669; letter-spacing: -0.03em;">0 Incidents</div>
            <span style="font-size: 0.7rem; color: #059669; font-weight: 600;">SOC2 Type II Protected</span>
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
                    id="audit-search-input"
                    placeholder="Search audit trail by actor, IP, event action..."
                    oninput="filterTableRows('audit-logs-table', this.value)"
                    style="width: 100%; padding: 0.4rem 0.75rem 0.4rem 2.25rem; font-size: 0.8rem; height: 32px; border-radius: 8px; border: 1px solid var(--zinc-300); background: var(--bg-card); color: var(--text-primary);"
                >
            </div>
            <span style="font-size: 0.75rem; color: var(--zinc-500); white-space: nowrap;">
                Showing <strong>5</strong> Verified Events
            </span>
        </div>

        <!-- Right: Density & Reset Controls -->
        <div style="display: flex; align-items: center; gap: 0.6rem;">
            <div style="display: flex; gap: 2px; background: var(--zinc-100); padding: 2px; border-radius: 6px; border: 1px solid var(--zinc-200);">
                <button type="button" class="btn btn-secondary btn-sm" onclick="setTableDensity('audit-logs-table', 'compact')" title="Compact density" style="padding: 0.25rem 0.5rem; font-size: 0.7rem;">
                    Compact
                </button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="setTableDensity('audit-logs-table', 'comfortable')" title="Comfortable density" style="padding: 0.25rem 0.5rem; font-size: 0.7rem;">
                    Comfortable
                </button>
            </div>

            <button type="button" class="btn btn-secondary btn-sm" onclick="resetTableColWidths('audit-logs-table')" title="Reset column widths" style="padding: 0.25rem 0.55rem; font-size: 0.7rem;">
                <i data-lucide="rotate-ccw" style="width: 12px; height: 12px;"></i>
                <span>Reset Widths</span>
            </button>
        </div>
    </div>

    <!-- Audit Logs Table -->
    <div class="card-table-wrapper">
        <table class="data-table" id="audit-logs-table" data-storage-key="dealsync_col_widths_audit_logs">
            <thead>
                <tr>
                    <th class="sortable" onclick="sortTable('audit-logs-table', 0)" data-col="timestamp" style="min-width: 170px;">
                        Timestamp (UTC)
                        <span class="sort-icon">▲</span>
                        <div class="col-resizer"></div>
                    </th>
                    <th class="sortable" onclick="sortTable('audit-logs-table', 1)" data-col="actor" style="min-width: 160px;">
                        Actor Identity
                        <span class="sort-icon">▲</span>
                        <div class="col-resizer"></div>
                    </th>
                    <th class="sortable" onclick="sortTable('audit-logs-table', 2)" data-col="action" style="min-width: 160px;">
                        Event Action
                        <span class="sort-icon">▲</span>
                        <div class="col-resizer"></div>
                    </th>
                    <th class="sortable" onclick="sortTable('audit-logs-table', 3)" data-col="target" style="min-width: 200px;">
                        Target Entity / Resource
                        <span class="sort-icon">▲</span>
                        <div class="col-resizer"></div>
                    </th>
                    <th class="sortable" onclick="sortTable('audit-logs-table', 4)" data-col="ip" style="min-width: 130px;">
                        Client IP
                        <span class="sort-icon">▲</span>
                        <div class="col-resizer"></div>
                    </th>
                    <th class="sortable" onclick="sortTable('audit-logs-table', 5)" data-col="status" style="min-width: 110px;">
                        Status
                        <span class="sort-icon">▲</span>
                        <div class="col-resizer"></div>
                    </th>
                    <th data-col="checksum" style="min-width: 180px;">
                        SHA-256 Checksum
                        <div class="col-resizer"></div>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="font-family: monospace; font-size: 0.75rem;">2026-10-13 17:15:30</td>
                    <td><strong>Alex Morgan (CEO)</strong></td>
                    <td><span class="badge badge-indigo">POLICY_UPDATE</span></td>
                    <td>FLS: credit_limit Masking Rule</td>
                    <td style="font-family: monospace; font-size: 0.75rem;">192.168.1.104</td>
                    <td><span class="badge badge-green">Success</span></td>
                    <td style="font-family: monospace; font-size: 0.7rem; color: var(--zinc-500);">e8b91a27f01c...4d9a</td>
                </tr>

                <tr>
                    <td style="font-family: monospace; font-size: 0.75rem;">2026-10-13 16:42:08</td>
                    <td><strong>Sarah Chen (Finance)</strong></td>
                    <td><span class="badge badge-blue">WIRE_MATCH</span></td>
                    <td>Wire UTR-8829103 &rarr; INV-2026-001</td>
                    <td style="font-family: monospace; font-size: 0.75rem;">192.168.1.109</td>
                    <td><span class="badge badge-green">Success</span></td>
                    <td style="font-family: monospace; font-size: 0.7rem; color: var(--zinc-500);">92f1b74a009c...e13b</td>
                </tr>

                <tr>
                    <td style="font-family: monospace; font-size: 0.75rem;">2026-10-13 15:38:55</td>
                    <td><strong>Jordan Lee (Sales)</strong></td>
                    <td><span class="badge badge-yellow">DEAL_UPDATE</span></td>
                    <td>Opportunity: Enterprise Cloud Suite</td>
                    <td style="font-family: monospace; font-size: 0.75rem;">192.168.1.112</td>
                    <td><span class="badge badge-green">Success</span></td>
                    <td style="font-family: monospace; font-size: 0.7rem; color: var(--zinc-500);">33a9cd17bc42...88fe</td>
                </tr>

                <tr>
                    <td style="font-family: monospace; font-size: 0.75rem;">2026-10-13 14:15:30</td>
                    <td><strong>Marcus Brody (Buyer)</strong></td>
                    <td><span class="badge badge-purple">PO_COMMITTED</span></td>
                    <td>Purchase Deal: PO-99210-HW</td>
                    <td style="font-family: monospace; font-size: 0.75rem;">192.168.1.118</td>
                    <td><span class="badge badge-green">Success</span></td>
                    <td style="font-family: monospace; font-size: 0.7rem; color: var(--zinc-500);">f8319a27c001...22b1</td>
                </tr>

                <tr>
                    <td style="font-family: monospace; font-size: 0.75rem;">2026-10-13 12:00:15</td>
                    <td><strong>System Scheduler</strong></td>
                    <td><span class="badge badge-gray">SNAPSHOT_BACKUP</span></td>
                    <td>Daily Tenant Database Snapshot</td>
                    <td style="font-family: monospace; font-size: 0.75rem;">127.0.0.1</td>
                    <td><span class="badge badge-green">Success</span></td>
                    <td style="font-family: monospace; font-size: 0.7rem; color: var(--zinc-500);">77b819f0a2e1...aa98</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
