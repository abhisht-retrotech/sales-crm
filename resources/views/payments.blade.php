@extends('layouts.app')

@section('title', 'Payments & Invoicing — DealSync Enterprise CRM')

@section('content')
<div class="payments-page-container animate-fade-in">
    <!-- Top Action Header -->
    <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                <span class="badge badge-purple" style="font-size: 0.7rem; font-weight: 700;">Financial Operations &amp; Ledger</span>
                <span style="font-size: 0.725rem; color: var(--zinc-500);">Accounts Receivable (AR) &amp; Accounts Payable (AP)</span>
            </div>
            <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--zinc-950); letter-spacing: -0.03em; margin: 0; line-height: 1.2;">
                Invoicing &amp; Settlement Ledger
            </h1>
            <p style="font-size: 0.8125rem; color: var(--zinc-500); margin: 0.25rem 0 0 0;">
                Accounts Receivable customer billing, Accounts Payable vendor invoices, partial settlements, and aging schedules.
            </p>
        </div>

        <div style="display: flex; align-items: center; gap: 0.6rem;">
            <!-- Filter Dropdown Trigger -->
            <button type="button" class="btn btn-secondary btn-sm" onclick="showToast('Filtered: Showing active invoices')">
                <i data-lucide="filter" style="width: 14px; height: 14px;"></i>
                <span>Filter</span>
            </button>

            <!-- Export -->
            <button type="button" class="btn btn-secondary btn-sm" onclick="showToast('Exporting invoices ledger to CSV...')">
                <i data-lucide="download" style="width: 14px; height: 14px;"></i>
                <span>Export CSV</span>
            </button>

            <!-- Create Invoice Button -->
            <button type="button" class="btn btn-primary btn-sm" onclick="openNewRecordModal('payments')">
                <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
                <span>+ Create Invoice</span>
            </button>
        </div>
    </div>

    <!-- Top KPI Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
        <div class="card" style="padding: 1rem;">
            <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500); margin-bottom: 0.25rem;">TOTAL INVOICED</div>
            <div style="font-size: 1.625rem; font-weight: 800; color: var(--zinc-950); letter-spacing: -0.03em;">$252,000</div>
            <span style="font-size: 0.7rem; color: var(--zinc-500); font-weight: 500;">4 Ledger Entries</span>
        </div>

        <div class="card" style="padding: 1rem;">
            <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500); margin-bottom: 0.25rem;">TOTAL COLLECTED</div>
            <div style="font-size: 1.625rem; font-weight: 800; color: #059669; letter-spacing: -0.03em;">$98,000</div>
            <span style="font-size: 0.7rem; color: #059669; font-weight: 600;">Cash In Deposit Accounts</span>
        </div>

        <div class="card" style="padding: 1rem;">
            <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500); margin-bottom: 0.25rem;">OUTSTANDING AR (RECEIVABLES)</div>
            <div style="font-size: 1.625rem; font-weight: 800; color: var(--color-primary); letter-spacing: -0.03em;">$100,000</div>
            <span style="font-size: 0.7rem; color: var(--zinc-500); font-weight: 500;">Client Due Balance</span>
        </div>

        <div class="card" style="padding: 1rem;">
            <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500); margin-bottom: 0.25rem;">OUTSTANDING AP (PAYABLES)</div>
            <div style="font-size: 1.625rem; font-weight: 800; color: #d97706; letter-spacing: -0.03em;">$54,000</div>
            <span style="font-size: 0.7rem; color: var(--zinc-500); font-weight: 500;">Vendor Spend Liabilities</span>
        </div>
    </div>

    <!-- Quick Ledger Type Filter Chips -->
    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem; overflow-x: auto; padding-bottom: 0.25rem;">
        <span style="font-size: 0.75rem; font-weight: 600; color: var(--zinc-500); margin-right: 0.25rem;">Ledger Type:</span>
        <button type="button" class="btn btn-sm pay-filter-btn" onclick="filterPaymentType('all')" style="background: var(--zinc-900); color: #ffffff; border-radius: 9999px; font-size: 0.725rem; padding: 0.15rem 0.75rem;">
            All Invoices (4)
        </button>
        <button type="button" class="btn btn-secondary btn-sm pay-filter-btn" onclick="filterPaymentType('AR')" style="border-radius: 9999px; font-size: 0.725rem; padding: 0.15rem 0.75rem;">
            Accounts Receivable (AR) (3)
        </button>
        <button type="button" class="btn btn-secondary btn-sm pay-filter-btn" onclick="filterPaymentType('AP')" style="border-radius: 9999px; font-size: 0.725rem; padding: 0.15rem 0.75rem;">
            Accounts Payable (AP) (1)
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
                    id="payments-search-input"
                    placeholder="Search invoices by reference #, account..."
                    oninput="filterTableRows('payments-table', this.value)"
                    style="width: 100%; padding: 0.4rem 0.75rem 0.4rem 2.25rem; font-size: 0.8rem; height: 32px; border-radius: 8px; border: 1px solid var(--zinc-300); background: var(--bg-card); color: var(--text-primary);"
                >
            </div>
            <span style="font-size: 0.75rem; color: var(--zinc-500); white-space: nowrap;">
                Showing <strong>4</strong> ledger records
            </span>
        </div>

        <!-- Right: Density & Reset Controls -->
        <div style="display: flex; align-items: center; gap: 0.6rem;">
            <!-- Density Toggle -->
            <div style="display: flex; gap: 2px; background: var(--zinc-100); padding: 2px; border-radius: 6px; border: 1px solid var(--zinc-200);">
                <button type="button" class="btn btn-secondary btn-sm" onclick="setTableDensity('payments-table', 'compact')" title="Compact density (30px rows)" style="padding: 0.25rem 0.5rem; font-size: 0.7rem;">
                    Compact
                </button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="setTableDensity('payments-table', 'comfortable')" title="Comfortable density (44px rows)" style="padding: 0.25rem 0.5rem; font-size: 0.7rem;">
                    Comfortable
                </button>
            </div>

            <!-- Reset Column Widths -->
            <button type="button" class="btn btn-secondary btn-sm" onclick="resetTableColWidths('payments-table')" title="Reset column widths" style="padding: 0.25rem 0.55rem; font-size: 0.7rem;">
                <i data-lucide="rotate-ccw" style="width: 12px; height: 12px;"></i>
                <span>Reset Widths</span>
            </button>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="card-table-wrapper">
        <div class="table-scroll-container">
            <table id="payments-table" class="data-table">
                <thead>
                    <tr>
                        <!-- Master Checkbox -->
                        <th class="col-index" style="width: 58px; min-width: 58px; text-align: center;">
                            <input type="checkbox" title="Select all invoices" style="cursor: pointer; width: 14px; height: 14px;">
                        </th>
                        <th data-col="ref" style="width: 170px; min-width: 130px;">Invoice Reference</th>
                        <th data-col="type" style="width: 140px; min-width: 110px;">Ledger Type</th>
                        <th data-col="account" style="width: 210px; min-width: 160px;">Billed Account</th>
                        <th data-col="total" style="width: 140px; min-width: 110px;">Total Billed</th>
                        <th data-col="paid" style="width: 140px; min-width: 110px;">Paid Amount</th>
                        <th data-col="balance" style="width: 140px; min-width: 110px;">Balance Due</th>
                        <th data-col="status" style="width: 140px; min-width: 110px;">Aging Status</th>
                        <th data-col="due" style="width: 140px; min-width: 110px;">Maturity Due Date</th>
                        <th class="no-sort sticky-actions-col" style="width: 150px; min-width: 140px; text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr data-type="AR">
                        <td class="col-index">
                            <div style="display: flex; align-items: center; justify-content: center; gap: 5px;">
                                <span style="font-size: 0.725rem; color: var(--zinc-400);">1</span>
                                <input type="checkbox" style="cursor: pointer;">
                            </div>
                        </td>
                        <td>
                            <strong style="color: var(--zinc-950); cursor: pointer;" onclick="openRecordDetailModal('INV-2026-0041', 'Skyline Cloud Systems', '$68,000', 'Partially Paid')">
                                INV-2026-0041
                            </strong>
                        </td>
                        <td><span class="badge badge-blue">AR (Client)</span></td>
                        <td>Skyline Cloud Systems</td>
                        <td>$68,000</td>
                        <td><span style="color: #059669; font-weight: 700;">$30,000</span></td>
                        <td><strong style="color: #b45309;">$38,000</strong></td>
                        <td><span class="badge badge-amber">Partially Paid</span></td>
                        <td>In 10 Days</td>
                        <td class="sticky-actions-col" style="text-align: right;">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="showToast('Recorded settlement transaction for INV-2026-0041')">Settle</button>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="openRecordDetailModal('INV-2026-0041', 'Skyline Cloud Systems', '$68,000', 'Partially Paid')">Inspect</button>
                        </td>
                    </tr>

                    <tr data-type="AR">
                        <td class="col-index">
                            <div style="display: flex; align-items: center; justify-content: center; gap: 5px;">
                                <span style="font-size: 0.725rem; color: var(--zinc-400);">2</span>
                                <input type="checkbox" style="cursor: pointer;">
                            </div>
                        </td>
                        <td>
                            <strong style="color: var(--zinc-950); cursor: pointer;" onclick="openRecordDetailModal('INV-2026-0038', 'Vanguard Health Tech', '$45,000', 'Sent')">
                                INV-2026-0038
                            </strong>
                        </td>
                        <td><span class="badge badge-blue">AR (Client)</span></td>
                        <td>Vanguard Health Tech</td>
                        <td>$45,000</td>
                        <td>$0</td>
                        <td><strong>$45,000</strong></td>
                        <td><span class="badge badge-gray">Sent</span></td>
                        <td>In 15 Days</td>
                        <td class="sticky-actions-col" style="text-align: right;">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="showToast('Recorded settlement transaction for INV-2026-0038')">Settle</button>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="openRecordDetailModal('INV-2026-0038', 'Vanguard Health Tech', '$45,000', 'Sent')">Inspect</button>
                        </td>
                    </tr>

                    <tr data-type="AP">
                        <td class="col-index">
                            <div style="display: flex; align-items: center; justify-content: center; gap: 5px;">
                                <span style="font-size: 0.725rem; color: var(--zinc-400);">3</span>
                                <input type="checkbox" style="cursor: pointer;">
                            </div>
                        </td>
                        <td>
                            <strong style="color: var(--zinc-950); cursor: pointer;" onclick="openRecordDetailModal('INV-VEN-9102', 'Quantum Silicon Components', '$54,000', 'Sent')">
                                INV-VEN-9102
                            </strong>
                        </td>
                        <td><span class="badge badge-purple">AP (Supplier)</span></td>
                        <td>Quantum Silicon Components</td>
                        <td>$54,000</td>
                        <td>$0</td>
                        <td><strong>$54,000</strong></td>
                        <td><span class="badge badge-gray">Sent</span></td>
                        <td>In 45 Days</td>
                        <td class="sticky-actions-col" style="text-align: right;">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="showToast('Initiated AP wire disbursement for INV-VEN-9102')">Disburse</button>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="openRecordDetailModal('INV-VEN-9102', 'Quantum Silicon Components', '$54,000', 'Sent')">Inspect</button>
                        </td>
                    </tr>

                    <tr data-type="AR">
                        <td class="col-index">
                            <div style="display: flex; align-items: center; justify-content: center; gap: 5px;">
                                <span style="font-size: 0.725rem; color: var(--zinc-400);">4</span>
                                <input type="checkbox" style="cursor: pointer;">
                            </div>
                        </td>
                        <td>
                            <strong style="color: var(--zinc-950); cursor: pointer;" onclick="openRecordDetailModal('INV-2026-0029', 'Apex Supply Chain', '$68,000', 'Paid')">
                                INV-2026-0029
                            </strong>
                        </td>
                        <td><span class="badge badge-blue">AR (Client)</span></td>
                        <td>Apex Supply Chain</td>
                        <td>$68,000</td>
                        <td><span style="color: #059669; font-weight: 700;">$68,000</span></td>
                        <td><strong style="color: #059669;">$0</strong></td>
                        <td><span class="badge badge-green">Paid in Full</span></td>
                        <td>Settled</td>
                        <td class="sticky-actions-col" style="text-align: right;">
                            <span class="badge badge-green" style="font-size: 0.7rem;">Settled</span>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="openRecordDetailModal('INV-2026-0029', 'Apex Supply Chain', '$68,000', 'Paid')">Inspect</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Table Footer -->
        <div style="padding: 0.5rem 1rem; background: var(--zinc-50); border-top: 1px solid var(--border-card); display: flex; justify-content: space-between; align-items: center; font-size: 0.725rem; color: var(--zinc-500);">
            <div>Showing <strong>4</strong> financial ledger invoices (AR &amp; AP Balances Reconciled)</div>
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
    function filterPaymentType(type) {
        const rows = document.querySelectorAll('#payments-table tbody tr');
        rows.forEach(r => {
            if (type === 'all' || r.getAttribute('data-type') === type) {
                r.style.display = '';
            } else {
                r.style.display = 'none';
            }
        });
        document.querySelectorAll('.pay-filter-btn').forEach(btn => {
            btn.style.background = '';
            btn.style.color = '';
            btn.classList.add('btn-secondary');
        });
        event.currentTarget.classList.remove('btn-secondary');
        event.currentTarget.style.background = 'var(--zinc-900)';
        event.currentTarget.style.color = '#ffffff';
    }

    function openRecordDetailModal(inv, account, amount, status) {
        const modal = document.getElementById('record-detail-modal');
        if (modal) {
            const title = document.getElementById('modal-detail-title');
            if (title) title.innerText = inv + ' — ' + account + ' (' + amount + ')';
            modal.classList.add('is-open');
        }
    }
</script>
@endsection
