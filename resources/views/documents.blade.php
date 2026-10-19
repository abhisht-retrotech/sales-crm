@extends('layouts.app')

@section('title', 'Billing & Ledger Documents — DealSync Enterprise CRM')

@section('content')
<!-- Page Header & KPI Cards -->
<div style="display: flex; flex-direction: column; gap: 1.25rem; margin-bottom: 1.5rem;">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                <span class="badge badge-indigo">
                    <i data-lucide="file-spreadsheet" style="width: 12px; height: 12px;"></i>
                    Enterprise Document Vault
                </span>
                <span style="font-size: 0.75rem; color: var(--zinc-500);">Financial Governance &amp; Legal Immutability</span>
            </div>
            <h2 style="font-size: 1.35rem; font-weight: 800; color: var(--zinc-900); letter-spacing: -0.02em; margin: 0;">
                Billing, Invoices &amp; Ledger Documents
            </h2>
            <p style="font-size: 0.8125rem; color: var(--zinc-500); margin: 0.15rem 0 0 0;">
                Unified financial repository managing customer invoices, purchase orders, quotes, and audit-locked agreements.
            </p>
        </div>

        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <button type="button" class="btn btn-secondary btn-sm" onclick="alert('Exporting Ledger to CSV...');">
                <i data-lucide="download" style="width: 14px; height: 14px;"></i>
                <span>Export Ledger</span>
            </button>
            <button type="button" class="btn btn-primary btn-sm" onclick="openNewRecordModal('payments')">
                <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
                <span>+ Generate Document</span>
            </button>
        </div>
    </div>

    <!-- KPI Metric Summary Tiles -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
        <div class="dashboard-kpi-card">
            <div class="dashboard-kpi-header">
                <span>Total Active Invoices</span>
                <span class="badge badge-blue">Receivables</span>
            </div>
            <div class="dashboard-kpi-main-val">$1,420,500</div>
            <div style="font-size: 0.725rem; color: var(--zinc-500); margin-top: 0.25rem;">18 verified customer billings</div>
        </div>

        <div class="dashboard-kpi-card">
            <div class="dashboard-kpi-header">
                <span>Purchase Orders (POs)</span>
                <span class="badge badge-purple">Procurement</span>
            </div>
            <div class="dashboard-kpi-main-val">$486,200</div>
            <div style="font-size: 0.725rem; color: var(--zinc-500); margin-top: 0.25rem;">7 active vendor commitments</div>
        </div>

        <div class="dashboard-kpi-card">
            <div class="dashboard-kpi-header">
                <span>Pending Approvals</span>
                <span class="badge badge-amber">Action Needed</span>
            </div>
            <div class="dashboard-kpi-main-val">3 Orders</div>
            <div style="font-size: 0.725rem; color: var(--zinc-500); margin-top: 0.25rem;">$125k awaiting finance sign-off</div>
        </div>

        <div class="dashboard-kpi-card">
            <div class="dashboard-kpi-header">
                <span>Settled / Paid (MTD)</span>
                <span class="badge badge-green">Cleared</span>
            </div>
            <div class="dashboard-kpi-main-val">$890,000</div>
            <div style="font-size: 0.725rem; color: var(--zinc-500); margin-top: 0.25rem;">100% matched with bank wires</div>
        </div>
    </div>
</div>

<!-- Controls Bar: Filter Tabs & Tools -->
<div class="table-toolbar" style="border-radius: var(--radius-lg); margin-bottom: 1rem; border: 1px solid var(--border-card);">
    <!-- Filter Tabs -->
    <div style="display: flex; align-items: center; gap: 0.35rem;">
        <button type="button" class="btn btn-sm btn-secondary" onclick="filterDocType('all')" id="tab-doc-all" style="background: var(--zinc-900); color: #ffffff; font-weight: 600;">All Documents (6)</button>
        <button type="button" class="btn btn-sm btn-secondary" onclick="filterDocType('invoice')" id="tab-doc-invoice">Invoices (3)</button>
        <button type="button" class="btn btn-sm btn-secondary" onclick="filterDocType('po')" id="tab-doc-po">Purchase Orders (2)</button>
        <button type="button" class="btn btn-sm btn-secondary" onclick="filterDocType('quote')" id="tab-doc-quote">Quotes (1)</button>
    </div>

    <!-- Search & Action Cluster -->
    <div style="display: flex; align-items: center; gap: 0.6rem; flex-wrap: wrap;">
        <div style="position: relative; width: 240px;">
            <i data-lucide="search" style="position: absolute; left: 9px; top: 8px; width: 14px; height: 14px; color: var(--zinc-400);"></i>
            <input type="text" placeholder="Search document #, client..." oninput="filterTableRows('documents-table', this.value)" style="width: 100%; padding: 0.38rem 0.65rem 0.38rem 1.85rem; border-radius: 8px; border: 1px solid var(--zinc-300); font-size: 0.775rem;">
        </div>

        <!-- Density Dropdown -->
        <div style="display: flex; align-items: center; border: 1px solid var(--zinc-200); border-radius: 8px; overflow: hidden;">
            <button type="button" class="btn btn-sm btn-secondary" onclick="setTableDensity('documents-table', 'compact')" style="padding: 0.32rem 0.5rem; border: none; border-radius: 0;" title="Compact Rows">
                <i data-lucide="align-justify" style="width: 13px; height: 13px;"></i>
            </button>
            <button type="button" class="btn btn-sm btn-secondary" onclick="setTableDensity('documents-table', 'normal')" style="padding: 0.32rem 0.5rem; border: none; border-radius: 0;" title="Normal Rows">
                <i data-lucide="menu" style="width: 13px; height: 13px;"></i>
            </button>
            <button type="button" class="btn btn-sm btn-secondary" onclick="setTableDensity('documents-table', 'comfortable')" style="padding: 0.32rem 0.5rem; border: none; border-radius: 0;" title="Comfortable Rows">
                <i data-lucide="stretch-horizontal" style="width: 13px; height: 13px;"></i>
            </button>
        </div>

        <!-- Reset Column Widths -->
        <button type="button" class="btn btn-secondary btn-sm" onclick="resetTableColWidths('documents-table')" title="Reset Resized Columns">
            <i data-lucide="rotate-ccw" style="width: 13px; height: 13px;"></i>
        </button>
    </div>
</div>

<!-- Primary Data Table -->
<div class="card-table-wrapper">
    <div class="table-scroll-container">
        <table id="documents-table" class="data-table">
            <thead>
                <tr>
                    <th class="col-index">
                        <input type="checkbox" aria-label="Select all rows">
                    </th>
                    <th style="min-width: 150px;">
                        <span>Document #</span>
                        <div class="table-col-resizer"></div>
                    </th>
                    <th style="min-width: 130px;">
                        <span>Type</span>
                        <div class="table-col-resizer"></div>
                    </th>
                    <th style="min-width: 200px;">
                        <span>Party / Organization</span>
                        <div class="table-col-resizer"></div>
                    </th>
                    <th style="min-width: 110px;">
                        <span>Issue Date</span>
                        <div class="table-col-resizer"></div>
                    </th>
                    <th style="min-width: 110px;">
                        <span>Due Date</span>
                        <div class="table-col-resizer"></div>
                    </th>
                    <th style="min-width: 120px;">
                        <span>Grand Total</span>
                        <div class="table-col-resizer"></div>
                    </th>
                    <th style="min-width: 120px;">
                        <span>Balance Due</span>
                        <div class="table-col-resizer"></div>
                    </th>
                    <th style="min-width: 120px;">
                        <span>Status</span>
                        <div class="table-col-resizer"></div>
                    </th>
                    <th style="min-width: 130px;">
                        <span>Governance</span>
                        <div class="table-col-resizer"></div>
                    </th>
                    <th style="min-width: 100px; text-align: right;">
                        <span>Actions</span>
                    </th>
                </tr>
            </thead>
            <tbody>
                <!-- Row 1: Invoice -->
                <tr data-type="invoice" onclick="openDetailModal('INV-2026-0041', 'Skyline Cloud Systems')">
                    <td class="col-index" onclick="event.stopPropagation()">
                        <input type="checkbox">
                    </td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 0.4rem;">
                            <i data-lucide="file-text" style="width: 14px; height: 14px; color: var(--color-primary);"></i>
                            <strong style="color: var(--color-primary); font-family: monospace;">INV-2026-0041</strong>
                        </div>
                    </td>
                    <td>
                        <span class="badge badge-blue">Tax Invoice</span>
                    </td>
                    <td>
                        <strong style="color: var(--zinc-900);">Skyline Cloud Systems LLC</strong>
                    </td>
                    <td>2026-10-02</td>
                    <td>2026-11-02</td>
                    <td>
                        <strong style="color: var(--zinc-900);">$145,000.00</strong>
                    </td>
                    <td>
                        <span style="color: #b45309; font-weight: 700;">$115,000.00</span>
                    </td>
                    <td>
                        <span class="badge badge-amber">Partially Paid</span>
                    </td>
                    <td>
                        <span style="display: inline-flex; align-items: center; gap: 4px; font-size: 0.725rem; color: #047857;">
                            <i data-lucide="lock" style="width: 12px; height: 12px;"></i> Vault Locked
                        </span>
                    </td>
                    <td style="text-align: right;" onclick="event.stopPropagation()">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="alert('Viewing PDF print preview for INV-2026-0041...');" style="padding: 0.2rem 0.5rem;">
                            <i data-lucide="eye" style="width: 13px; height: 13px;"></i>
                        </button>
                    </td>
                </tr>

                <!-- Row 2: Purchase Order -->
                <tr data-type="po" onclick="openDetailModal('PO-882194', 'Nexis Global Hardware')">
                    <td class="col-index" onclick="event.stopPropagation()">
                        <input type="checkbox">
                    </td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 0.4rem;">
                            <i data-lucide="shopping-bag" style="width: 14px; height: 14px; color: #7e22ce;"></i>
                            <strong style="color: #7e22ce; font-family: monospace;">PO-882194</strong>
                        </div>
                    </td>
                    <td>
                        <span class="badge badge-purple">Purchase Order</span>
                    </td>
                    <td>
                        <strong style="color: var(--zinc-900);">Nexis Global Hardware BV</strong>
                    </td>
                    <td>2026-10-05</td>
                    <td>2026-10-25</td>
                    <td>
                        <strong style="color: var(--zinc-900);">$54,000.00</strong>
                    </td>
                    <td>
                        <span style="color: #047857; font-weight: 600;">$0.00</span>
                    </td>
                    <td>
                        <span class="badge badge-green">Approved</span>
                    </td>
                    <td>
                        <span style="display: inline-flex; align-items: center; gap: 4px; font-size: 0.725rem; color: #047857;">
                            <i data-lucide="check-circle" style="width: 12px; height: 12px;"></i> Audited
                        </span>
                    </td>
                    <td style="text-align: right;" onclick="event.stopPropagation()">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="alert('Viewing PO-882194 spec manifest...');" style="padding: 0.2rem 0.5rem;">
                            <i data-lucide="eye" style="width: 13px; height: 13px;"></i>
                        </button>
                    </td>
                </tr>

                <!-- Row 3: Invoice -->
                <tr data-type="invoice" onclick="openDetailModal('INV-2026-0042', 'Apex Telecom Networks')">
                    <td class="col-index" onclick="event.stopPropagation()">
                        <input type="checkbox">
                    </td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 0.4rem;">
                            <i data-lucide="file-text" style="width: 14px; height: 14px; color: var(--color-primary);"></i>
                            <strong style="color: var(--color-primary); font-family: monospace;">INV-2026-0042</strong>
                        </div>
                    </td>
                    <td>
                        <span class="badge badge-blue">Tax Invoice</span>
                    </td>
                    <td>
                        <strong style="color: var(--zinc-900);">Apex Telecom Networks Ltd</strong>
                    </td>
                    <td>2026-09-15</td>
                    <td>2026-10-15</td>
                    <td>
                        <strong style="color: var(--zinc-900);">$88,500.00</strong>
                    </td>
                    <td>
                        <span style="color: #be123c; font-weight: 700;">$88,500.00</span>
                    </td>
                    <td>
                        <span class="badge badge-red">Overdue (28d)</span>
                    </td>
                    <td>
                        <span style="display: inline-flex; align-items: center; gap: 4px; font-size: 0.725rem; color: #b45309;">
                            <i data-lucide="alert-circle" style="width: 12px; height: 12px;"></i> Dunning Tier 1
                        </span>
                    </td>
                    <td style="text-align: right;" onclick="event.stopPropagation()">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="alert('Opening dunning notice generator...');" style="padding: 0.2rem 0.5rem;">
                            <i data-lucide="mail" style="width: 13px; height: 13px;"></i>
                        </button>
                    </td>
                </tr>

                <!-- Row 4: Quote -->
                <tr data-type="quote" onclick="openDetailModal('QT-2026-0089', 'CyberShield Solutions')">
                    <td class="col-index" onclick="event.stopPropagation()">
                        <input type="checkbox">
                    </td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 0.4rem;">
                            <i data-lucide="file-spreadsheet" style="width: 14px; height: 14px; color: #d97706;"></i>
                            <strong style="color: #d97706; font-family: monospace;">QT-2026-0089</strong>
                        </div>
                    </td>
                    <td>
                        <span class="badge badge-amber">Sales Quote</span>
                    </td>
                    <td>
                        <strong style="color: var(--zinc-900);">CyberShield Solutions Inc</strong>
                    </td>
                    <td>2026-10-10</td>
                    <td>2026-11-10</td>
                    <td>
                        <strong style="color: var(--zinc-900);">$210,000.00</strong>
                    </td>
                    <td>
                        <span style="color: var(--zinc-500); font-weight: 600;">$210,000.00</span>
                    </td>
                    <td>
                        <span class="badge badge-indigo">Pending Approval</span>
                    </td>
                    <td>
                        <span style="display: inline-flex; align-items: center; gap: 4px; font-size: 0.725rem; color: var(--zinc-500);">
                            <i data-lucide="clock" style="width: 12px; height: 12px;"></i> VP Approval
                        </span>
                    </td>
                    <td style="text-align: right;" onclick="event.stopPropagation()">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="alert('Viewing Quote terms & SLA specifications...');" style="padding: 0.2rem 0.5rem;">
                            <i data-lucide="eye" style="width: 13px; height: 13px;"></i>
                        </button>
                    </td>
                </tr>

                <!-- Row 5: Purchase Order -->
                <tr data-type="po" onclick="openDetailModal('PO-882195', 'Broadcom Semi Corp')">
                    <td class="col-index" onclick="event.stopPropagation()">
                        <input type="checkbox">
                    </td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 0.4rem;">
                            <i data-lucide="shopping-bag" style="width: 14px; height: 14px; color: #7e22ce;"></i>
                            <strong style="color: #7e22ce; font-family: monospace;">PO-882195</strong>
                        </div>
                    </td>
                    <td>
                        <span class="badge badge-purple">Purchase Order</span>
                    </td>
                    <td>
                        <strong style="color: var(--zinc-900);">Broadcom Semi Corp</strong>
                    </td>
                    <td>2026-10-08</td>
                    <td>2026-11-15</td>
                    <td>
                        <strong style="color: var(--zinc-900);">$312,200.00</strong>
                    </td>
                    <td>
                        <span style="color: #b45309; font-weight: 700;">$156,100.00</span>
                    </td>
                    <td>
                        <span class="badge badge-blue">Issued</span>
                    </td>
                    <td>
                        <span style="display: inline-flex; align-items: center; gap: 4px; font-size: 0.725rem; color: #047857;">
                            <i data-lucide="lock" style="width: 12px; height: 12px;"></i> Escrow Bound
                        </span>
                    </td>
                    <td style="text-align: right;" onclick="event.stopPropagation()">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="alert('Viewing PO Escrow terms...');" style="padding: 0.2rem 0.5rem;">
                            <i data-lucide="eye" style="width: 13px; height: 13px;"></i>
                        </button>
                    </td>
                </tr>

                <!-- Row 6: Invoice -->
                <tr data-type="invoice" onclick="openDetailModal('INV-2026-0043', 'Vortex Data Labs')">
                    <td class="col-index" onclick="event.stopPropagation()">
                        <input type="checkbox">
                    </td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 0.4rem;">
                            <i data-lucide="file-text" style="width: 14px; height: 14px; color: var(--color-primary);"></i>
                            <strong style="color: var(--color-primary); font-family: monospace;">INV-2026-0043</strong>
                        </div>
                    </td>
                    <td>
                        <span class="badge badge-blue">Tax Invoice</span>
                    </td>
                    <td>
                        <strong style="color: var(--zinc-900);">Vortex Data Labs GmbH</strong>
                    </td>
                    <td>2026-10-11</td>
                    <td>2026-11-11</td>
                    <td>
                        <strong style="color: var(--zinc-900);">$94,000.00</strong>
                    </td>
                    <td>
                        <span style="color: #047857; font-weight: 700;">$0.00</span>
                    </td>
                    <td>
                        <span class="badge badge-green">Paid in Full</span>
                    </td>
                    <td>
                        <span style="display: inline-flex; align-items: center; gap: 4px; font-size: 0.725rem; color: #047857;">
                            <i data-lucide="lock" style="width: 12px; height: 12px;"></i> Wire Matched
                        </span>
                    </td>
                    <td style="text-align: right;" onclick="event.stopPropagation()">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="alert('Receipt voucher verified.');" style="padding: 0.2rem 0.5rem;">
                            <i data-lucide="printer" style="width: 13px; height: 13px;"></i>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Table Footer -->
    <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.65rem 1rem; border-top: 1px solid var(--border-card); background: var(--bg-card); font-size: 0.775rem; color: var(--zinc-500);">
        <div>Showing <strong>6</strong> ledger documents • 100% Static Mock Data</div>
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <span>Page 1 of 1</span>
        </div>
    </div>
</div>

<script>
    function filterDocType(type) {
        // Update tab button styles
        ['all', 'invoice', 'po', 'quote'].forEach(t => {
            const btn = document.getElementById(`tab-doc-${t}`);
            if (btn) {
                if (t === type) {
                    btn.style.background = 'var(--zinc-900)';
                    btn.style.color = '#ffffff';
                    btn.style.fontWeight = '600';
                } else {
                    btn.style.background = 'var(--bg-card)';
                    btn.style.color = 'var(--zinc-800)';
                    btn.style.fontWeight = '500';
                }
            }
        });

        // Filter table rows
        document.querySelectorAll('#documents-table tbody tr').forEach(row => {
            if (type === 'all' || row.getAttribute('data-type') === type) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    function openDetailModal(docNum, org) {
        const modal = document.getElementById('record-detail-modal');
        if (modal) {
            document.getElementById('modal-detail-title').innerText = `${docNum} — ${org}`;
            modal.classList.add('is-open');
        }
    }
</script>
@endsection
