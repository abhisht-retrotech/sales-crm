@extends('layouts.app')

@section('title', 'Wire Reconcile — DealSync Enterprise CRM')

@section('content')
<div class="wires-page-container animate-fade-in">
    <!-- Top Action Header -->
    <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                <span class="badge badge-amber" style="font-size: 0.7rem; font-weight: 700;">Banking Intake &amp; EFT Reconciliation</span>
                <span style="font-size: 0.725rem; color: var(--zinc-500);">Direct Bank Feed Matching to Invoices</span>
            </div>
            <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--zinc-950); letter-spacing: -0.03em; margin: 0; line-height: 1.2;">
                Wire Received Workbench
            </h1>
            <p style="font-size: 0.8125rem; color: var(--zinc-500); margin: 0.25rem 0 0 0;">
                Capture real-time banking wire references (UTR), remitter institutions, and match incoming funds directly to open Accounts Receivable invoices.
            </p>
        </div>

        <div style="display: flex; align-items: center; gap: 0.6rem;">
            <!-- Filter Dropdown Trigger -->
            <button type="button" class="btn btn-secondary btn-sm" onclick="showToast('Filtered: Showing active bank wires')">
                <i data-lucide="filter" style="width: 14px; height: 14px;"></i>
                <span>Filter</span>
            </button>

            <!-- Export -->
            <button type="button" class="btn btn-secondary btn-sm" onclick="showToast('Exporting bank wires to CSV...')">
                <i data-lucide="download" style="width: 14px; height: 14px;"></i>
                <span>Export CSV</span>
            </button>

            <!-- Log Wire Button -->
            <button type="button" class="btn btn-primary btn-sm" onclick="openNewRecordModal('wires')">
                <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
                <span>+ Log Wire Intake</span>
            </button>
        </div>
    </div>

    <!-- Top KPI Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
        <div class="card" style="padding: 1rem;">
            <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500); margin-bottom: 0.25rem;">TOTAL WIRES RECEIVED</div>
            <div style="font-size: 1.625rem; font-weight: 800; color: var(--zinc-950); letter-spacing: -0.03em;">$83,000</div>
            <span style="font-size: 0.7rem; color: var(--zinc-500); font-weight: 500;">3 Incoming UTR Transits</span>
        </div>

        <div class="card" style="padding: 1rem;">
            <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500); margin-bottom: 0.25rem;">RECONCILED FUNDS</div>
            <div style="font-size: 1.625rem; font-weight: 800; color: #059669; letter-spacing: -0.03em;">$30,000</div>
            <span style="font-size: 0.7rem; color: #059669; font-weight: 600;">Matched to Open Invoices</span>
        </div>

        <div class="card" style="padding: 1rem;">
            <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500); margin-bottom: 0.25rem;">PENDING VERIFICATION</div>
            <div style="font-size: 1.625rem; font-weight: 800; color: #d97706; letter-spacing: -0.03em;">$53,000</div>
            <span style="font-size: 0.7rem; color: #d97706; font-weight: 600;">2 Unallocated Remittances</span>
        </div>

        <div class="card" style="padding: 1rem;">
            <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500); margin-bottom: 0.25rem;">AUDIT DISCREPANCY</div>
            <div style="font-size: 1.625rem; font-weight: 800; color: #059669; letter-spacing: -0.03em;">$0.00</div>
            <span style="font-size: 0.7rem; color: var(--zinc-500); font-weight: 500;">Zero Ledger Variance</span>
        </div>
    </div>

    <!-- Quick Reconciliation Filter Chips -->
    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem; overflow-x: auto; padding-bottom: 0.25rem;">
        <span style="font-size: 0.75rem; font-weight: 600; color: var(--zinc-500); margin-right: 0.25rem;">Status:</span>
        <button type="button" class="btn btn-sm wire-filter-btn" onclick="filterWireStatus('all')" style="background: var(--zinc-900); color: #ffffff; border-radius: 9999px; font-size: 0.725rem; padding: 0.15rem 0.75rem;">
            All Wires (3)
        </button>
        <button type="button" class="btn btn-secondary btn-sm wire-filter-btn" onclick="filterWireStatus('Matched')" style="border-radius: 9999px; font-size: 0.725rem; padding: 0.15rem 0.75rem;">
            Matched / Reconciled (1)
        </button>
        <button type="button" class="btn btn-secondary btn-sm wire-filter-btn" onclick="filterWireStatus('Pending')" style="border-radius: 9999px; font-size: 0.725rem; padding: 0.15rem 0.75rem;">
            Pending Verification (2)
        </button>
    </div>

    <!-- Table Toolbar: Search, Density, Reset Widths -->
    <div class="card" style="margin-bottom: 0.75rem; padding: 0.65rem 1rem; display: flex; gap: 0.75rem; align-items: center; justify-content: space-between; flex-wrap: wrap;">
        <!-- Left: Search Box -->
        <div style="display: flex; align-items: center; gap: 0.75rem; flex: 1; min-width: 280px;">
            <div style="position: relative; flex: 1; max-width: 380px;">
                <i data-lucide="search" style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); width: 15px; height: 15px; color: var(--zinc-400);"></i>
                <input
                    type="text"
                    id="wires-search-input"
                    placeholder="Search wires by UTR reference, remitter..."
                    oninput="filterTableRows('wires-table', this.value)"
                    style="width: 100%; padding: 0.4rem 0.75rem 0.4rem 2.25rem; font-size: 0.8rem; height: 32px; border-radius: 8px; border: 1px solid var(--zinc-300); background: var(--bg-card); color: var(--text-primary);"
                >
            </div>
            <span style="font-size: 0.75rem; color: var(--zinc-500); white-space: nowrap;">
                Showing <strong>3</strong> incoming wires
            </span>
        </div>

        <!-- Right: Density & Reset Controls -->
        <div style="display: flex; align-items: center; gap: 0.6rem;">
            <!-- Density Toggle -->
            <div style="display: flex; gap: 2px; background: var(--zinc-100); padding: 2px; border-radius: 6px; border: 1px solid var(--zinc-200);">
                <button type="button" class="btn btn-secondary btn-sm" onclick="setTableDensity('wires-table', 'compact')" title="Compact density (30px rows)" style="padding: 0.25rem 0.5rem; font-size: 0.7rem;">
                    Compact
                </button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="setTableDensity('wires-table', 'comfortable')" title="Comfortable density (44px rows)" style="padding: 0.25rem 0.5rem; font-size: 0.7rem;">
                    Comfortable
                </button>
            </div>

            <!-- Reset Column Widths -->
            <button type="button" class="btn btn-secondary btn-sm" onclick="resetTableColWidths('wires-table')" title="Reset column widths" style="padding: 0.25rem 0.55rem; font-size: 0.7rem;">
                <i data-lucide="rotate-ccw" style="width: 12px; height: 12px;"></i>
                <span>Reset Widths</span>
            </button>
        </div>
    </div>

    <!-- Wires Table -->
    <div class="card-table-wrapper">
        <div class="table-scroll-container">
            <table id="wires-table" class="data-table">
                <thead>
                    <tr>
                        <!-- Master Checkbox -->
                        <th class="col-index" style="width: 58px; min-width: 58px; text-align: center;">
                            <input type="checkbox" title="Select all wires" style="cursor: pointer; width: 14px; height: 14px;">
                        </th>
                        <th data-col="utr" style="width: 180px; min-width: 140px;">Bank UTR Reference</th>
                        <th data-col="remitter" style="width: 220px; min-width: 160px;">Remitter Entity Name</th>
                        <th data-col="bank" style="width: 180px; min-width: 130px;">Sending Bank Institution</th>
                        <th data-col="deposit" style="width: 180px; min-width: 130px;">Deposit Account</th>
                        <th data-col="amount" style="width: 150px; min-width: 110px;">Wire Amount ($ USD)</th>
                        <th data-col="date" style="width: 130px; min-width: 100px;">Date Received</th>
                        <th data-col="status" style="width: 180px; min-width: 130px;">Reconciliation Status</th>
                        <th data-col="invoice" style="width: 150px; min-width: 110px;">Matched Invoice</th>
                        <th class="no-sort sticky-actions-col" style="width: 150px; min-width: 140px; text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr data-status="Matched">
                        <td class="col-index">
                            <div style="display: flex; align-items: center; justify-content: center; gap: 5px;">
                                <span style="font-size: 0.725rem; color: var(--zinc-400);">1</span>
                                <input type="checkbox" style="cursor: pointer;">
                            </div>
                        </td>
                        <td>
                            <code style="font-weight: 700; color: #059669; background: #ecfdf5; padding: 0.2rem 0.45rem; border-radius: 4px; border: 1px solid #a7f3d0; cursor: pointer;" onclick="openRecordDetailModal('UTR9982410294', 'Skyline Cloud Systems LLC', '$30,000 USD', 'Matched')">
                                UTR9982410294
                            </code>
                        </td>
                        <td>Skyline Cloud Systems LLC</td>
                        <td>JPMorgan Chase NY</td>
                        <td>CITI-US-MAIN-9482</td>
                        <td><strong style="color: #059669;">$30,000</strong></td>
                        <td>4 Days Ago</td>
                        <td><span class="badge badge-green">✓ Matched/Reconciled</span></td>
                        <td><strong style="color: var(--color-primary);">INV-2026-0041</strong></td>
                        <td class="sticky-actions-col" style="text-align: right;">
                            <span class="badge badge-green" style="font-size: 0.7rem;">Reconciled</span>
                        </td>
                    </tr>

                    <tr data-status="Pending">
                        <td class="col-index">
                            <div style="display: flex; align-items: center; justify-content: center; gap: 5px;">
                                <span style="font-size: 0.725rem; color: var(--zinc-400);">2</span>
                                <input type="checkbox" style="cursor: pointer;">
                            </div>
                        </td>
                        <td>
                            <code style="font-weight: 700; color: var(--zinc-900); background: var(--zinc-100); padding: 0.2rem 0.45rem; border-radius: 4px; border: 1px solid var(--zinc-300); cursor: pointer;" onclick="openRecordDetailModal('UTR4491029381', 'Skyline Cloud Systems LLC', '$38,000 USD', 'Pending Verification')">
                                UTR4491029381
                            </code>
                        </td>
                        <td>Skyline Cloud Systems LLC</td>
                        <td>JPMorgan Chase NY</td>
                        <td>CITI-US-MAIN-9482</td>
                        <td><strong style="color: var(--zinc-950);">$38,000</strong></td>
                        <td>Yesterday</td>
                        <td><span class="badge badge-amber">● Pending Verification</span></td>
                        <td><span style="color: var(--zinc-400);">Unlinked</span></td>
                        <td class="sticky-actions-col" style="text-align: right;">
                            <button type="button" class="btn btn-primary btn-sm" onclick="showToast('Matched wire UTR4491029381 to INV-2026-0041 remaining balance ($38,000)!')">Match Invoice</button>
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
                            <code style="font-weight: 700; color: var(--zinc-900); background: var(--zinc-100); padding: 0.2rem 0.45rem; border-radius: 4px; border: 1px solid var(--zinc-300); cursor: pointer;" onclick="openRecordDetailModal('UTR7710294822', 'Apex Holdings Group', '$15,000 USD', 'Pending Verification')">
                                UTR7710294822
                            </code>
                        </td>
                        <td>Apex Holdings Group</td>
                        <td>Bank of America NC</td>
                        <td>CITI-US-MAIN-9482</td>
                        <td><strong style="color: var(--zinc-950);">$15,000</strong></td>
                        <td>Today</td>
                        <td><span class="badge badge-amber">● Pending Verification</span></td>
                        <td><span style="color: var(--zinc-400);">Unallocated</span></td>
                        <td class="sticky-actions-col" style="text-align: right;">
                            <button type="button" class="btn btn-primary btn-sm" onclick="showToast('Allocated wire UTR7710294822 to Apex Supply Chain AR account!')">Match Invoice</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Table Footer -->
        <div style="padding: 0.5rem 1rem; background: var(--zinc-50); border-top: 1px solid var(--border-card); display: flex; justify-content: space-between; align-items: center; font-size: 0.725rem; color: var(--zinc-500);">
            <div>Showing <strong>3</strong> bank wires ($83k total funds intake)</div>
            <div style="display: flex; gap: 0.75rem; color: var(--zinc-400);">
                <span>Tip: Drag column edges to resize</span>
                <span>•</span>
                <span>Click headers to sort ▲/▼</span>
                <span>•</span>
                <span>Double click resizer to reset</span>
            </div>
        </div>
    </div>
</div>

<script>
    function filterWireStatus(status) {
        const rows = document.querySelectorAll('#wires-table tbody tr');
        rows.forEach(r => {
            if (status === 'all' || r.getAttribute('data-status') === status) {
                r.style.display = '';
            } else {
                r.style.display = 'none';
            }
        });
        document.querySelectorAll('.wire-filter-btn').forEach(btn => {
            btn.style.background = '';
            btn.style.color = '';
            btn.classList.add('btn-secondary');
        });
        event.currentTarget.classList.remove('btn-secondary');
        event.currentTarget.style.background = 'var(--zinc-900)';
        event.currentTarget.style.color = '#ffffff';
    }

    function openRecordDetailModal(utr, remitter, amount, status) {
        const modal = document.getElementById('record-detail-modal');
        if (modal) {
            const title = document.getElementById('modal-detail-title');
            if (title) title.innerText = utr + ' — ' + remitter + ' (' + amount + ')';
            modal.classList.add('is-open');
        }
    }
</script>
@endsection
