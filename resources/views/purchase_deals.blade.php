@extends('layouts.app')

@section('title', 'Purchase Deals & Procurement — DealSync Enterprise CRM')

@section('content')
<div class="purchase-deals-page-container animate-fade-in">
    <!-- Top Action Header -->
    <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                <span class="badge badge-indigo" style="font-size: 0.7rem; font-weight: 700;">Buy-Side Procurement Ledger</span>
                <span style="font-size: 0.725rem; color: var(--zinc-500);">Supplier POs &amp; Spend Commitments</span>
            </div>
            <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--zinc-950); letter-spacing: -0.03em; margin: 0; line-height: 1.2;">
                Purchase Deals &amp; Procurement
            </h1>
            <p style="font-size: 0.8125rem; color: var(--zinc-500); margin: 0.25rem 0 0 0;">
                Supplier purchase orders with unique PO numbers, inventory delivery commitments, coupled margins, and vendor payment terms.
            </p>
        </div>

        <div style="display: flex; align-items: center; gap: 0.6rem;">
            <!-- Filter Dropdown Trigger -->
            <button type="button" class="btn btn-secondary btn-sm" onclick="showToast('Filtered: Showing active procurement POs')">
                <i data-lucide="filter" style="width: 14px; height: 14px;"></i>
                <span>Filter</span>
            </button>

            <!-- Export -->
            <button type="button" class="btn btn-secondary btn-sm" onclick="showToast('Exporting PO ledger to CSV...')">
                <i data-lucide="download" style="width: 14px; height: 14px;"></i>
                <span>Export CSV</span>
            </button>

            <!-- Create PO Button -->
            <button type="button" class="btn btn-primary btn-sm" onclick="openNewRecordModal('purchase_deals')">
                <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
                <span>+ Create PO Deal</span>
            </button>
        </div>
    </div>

    <!-- Top KPI Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
        <div class="card" style="padding: 1rem;">
            <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500); margin-bottom: 0.25rem;">COMMITTED SPEND</div>
            <div style="font-size: 1.625rem; font-weight: 800; color: var(--zinc-950); letter-spacing: -0.03em;">$138,500</div>
            <span style="font-size: 0.7rem; color: var(--zinc-500); font-weight: 500;">4 Approved Vendor Orders</span>
        </div>

        <div class="card" style="padding: 1rem;">
            <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500); margin-bottom: 0.25rem;">IN-PRODUCTION SPEND</div>
            <div style="font-size: 1.625rem; font-weight: 800; color: #d97706; letter-spacing: -0.03em;">$72,500</div>
            <span style="font-size: 0.7rem; color: var(--zinc-500); font-weight: 500;">Hardware Fabrication Active</span>
        </div>

        <div class="card" style="padding: 1rem;">
            <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500); margin-bottom: 0.25rem;">DELIVERED &amp; RECEIVED</div>
            <div style="font-size: 1.625rem; font-weight: 800; color: #059669; letter-spacing: -0.03em;">$66,000</div>
            <span style="font-size: 0.7rem; color: #059669; font-weight: 600;">100% Quality Inspected</span>
        </div>

        <div class="card" style="padding: 1rem;">
            <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500); margin-bottom: 0.25rem;">AVG PAYMENT TERMS</div>
            <div style="font-size: 1.625rem; font-weight: 800; color: var(--color-primary); letter-spacing: -0.03em;">Net 45</div>
            <span style="font-size: 0.7rem; color: var(--zinc-500); font-weight: 500;">Vendor Credit Line Stable</span>
        </div>
    </div>

    <!-- Quick Stage Filter Chips -->
    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem; overflow-x: auto; padding-bottom: 0.25rem;">
        <span style="font-size: 0.75rem; font-weight: 600; color: var(--zinc-500); margin-right: 0.25rem;">Stages:</span>
        <button type="button" class="btn btn-sm po-filter-btn" onclick="filterPoStage('all')" style="background: var(--zinc-900); color: #ffffff; border-radius: 9999px; font-size: 0.725rem; padding: 0.15rem 0.75rem;">
            All Orders (4)
        </button>
        <button type="button" class="btn btn-secondary btn-sm po-filter-btn" onclick="filterPoStage('PO Issued')" style="border-radius: 9999px; font-size: 0.725rem; padding: 0.15rem 0.75rem;">
            PO Issued (1)
        </button>
        <button type="button" class="btn btn-secondary btn-sm po-filter-btn" onclick="filterPoStage('In Production')" style="border-radius: 9999px; font-size: 0.725rem; padding: 0.15rem 0.75rem;">
            In Production (1)
        </button>
        <button type="button" class="btn btn-secondary btn-sm po-filter-btn" onclick="filterPoStage('Quote Received')" style="border-radius: 9999px; font-size: 0.725rem; padding: 0.15rem 0.75rem;">
            Quote Received (1)
        </button>
        <button type="button" class="btn btn-secondary btn-sm po-filter-btn" onclick="filterPoStage('Completed')" style="border-radius: 9999px; font-size: 0.725rem; padding: 0.15rem 0.75rem;">
            Completed (1)
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
                    id="po-search-input"
                    placeholder="Search purchase deals by PO #, vendor, item..."
                    oninput="filterTableRows('po-table', this.value)"
                    style="width: 100%; padding: 0.4rem 0.75rem 0.4rem 2.25rem; font-size: 0.8rem; height: 32px; border-radius: 8px; border: 1px solid var(--zinc-300); background: var(--bg-card); color: var(--text-primary);"
                >
            </div>
            <span style="font-size: 0.75rem; color: var(--zinc-500); white-space: nowrap;">
                Showing <strong>4</strong> orders
            </span>
        </div>

        <!-- Right: Density & Reset Controls -->
        <div style="display: flex; align-items: center; gap: 0.6rem;">
            <!-- Density Toggle -->
            <div style="display: flex; gap: 2px; background: var(--zinc-100); padding: 2px; border-radius: 6px; border: 1px solid var(--zinc-200);">
                <button type="button" class="btn btn-secondary btn-sm" onclick="setTableDensity('po-table', 'compact')" title="Compact density (30px rows)" style="padding: 0.25rem 0.5rem; font-size: 0.7rem;">
                    Compact
                </button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="setTableDensity('po-table', 'comfortable')" title="Comfortable density (44px rows)" style="padding: 0.25rem 0.5rem; font-size: 0.7rem;">
                    Comfortable
                </button>
            </div>

            <!-- Reset Column Widths -->
            <button type="button" class="btn btn-secondary btn-sm" onclick="resetTableColWidths('po-table')" title="Reset column widths" style="padding: 0.25rem 0.55rem; font-size: 0.7rem;">
                <i data-lucide="rotate-ccw" style="width: 12px; height: 12px;"></i>
                <span>Reset Widths</span>
            </button>
        </div>
    </div>

    <!-- Purchase Deals Table -->
    <div class="card-table-wrapper">
        <div class="table-scroll-container">
            <table id="po-table" class="data-table">
                <thead>
                    <tr>
                        <!-- Master Checkbox -->
                        <th class="col-index" style="width: 58px; min-width: 58px; text-align: center;">
                            <input type="checkbox" title="Select all orders" style="cursor: pointer; width: 14px; height: 14px;">
                        </th>
                        <th data-col="po" style="width: 140px; min-width: 110px;">PO Number</th>
                        <th data-col="desc" style="width: 280px; min-width: 200px;">Order Description</th>
                        <th data-col="vendor" style="width: 210px; min-width: 160px;">Supplier / Vendor</th>
                        <th data-col="spend" style="width: 160px; min-width: 120px;">PO Total Spend</th>
                        <th data-col="stage" style="width: 140px; min-width: 110px;">Milestone Stage</th>
                        <th data-col="delivery" style="width: 150px; min-width: 120px;">Target Delivery Date</th>
                        <th data-col="terms" style="width: 120px; min-width: 90px;">Payment Terms</th>
                        <th data-col="buyer" style="width: 140px; min-width: 110px;">Assigned Buyer</th>
                        <th class="no-sort sticky-actions-col" style="width: 140px; min-width: 130px; text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr data-stage="PO Issued">
                        <td class="col-index">
                            <div style="display: flex; align-items: center; justify-content: center; gap: 5px;">
                                <span style="font-size: 0.725rem; color: var(--zinc-400);">1</span>
                                <input type="checkbox" style="cursor: pointer;">
                            </div>
                        </td>
                        <td>
                            <strong style="color: var(--color-primary); cursor: pointer;" onclick="openRecordDetailModal('PO-882194', 'Batch 48 FPGA Accelerators (120 units)', '$54,000 USD', 'PO Issued')">
                                PO-882194
                            </strong>
                        </td>
                        <td>Batch 48 FPGA Accelerators (120 units)</td>
                        <td>Quantum Silicon Components</td>
                        <td><strong style="color: var(--zinc-950);">$54,000 USD</strong></td>
                        <td><span class="badge badge-blue">PO Issued</span></td>
                        <td>In 7 Days</td>
                        <td><span class="badge badge-gray">Net 60</span></td>
                        <td>
                            <span style="display: inline-flex; align-items: center; gap: 5px;">
                                <span style="width: 18px; height: 18px; border-radius: 50%; background: #e0e7ff; color: #4338ca; font-size: 0.625rem; font-weight: 700; display: inline-flex; align-items: center; justify-content: center;">M</span>
                                <span>Marcus Brody</span>
                            </span>
                        </td>
                        <td class="sticky-actions-col" style="text-align: right;">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="openRecordDetailModal('PO-882194', 'Batch 48 FPGA Accelerators (120 units)', '$54,000 USD', 'PO Issued')">Inspect</button>
                        </td>
                    </tr>

                    <tr data-stage="In Production">
                        <td class="col-index">
                            <div style="display: flex; align-items: center; justify-content: center; gap: 5px;">
                                <span style="font-size: 0.725rem; color: var(--zinc-400);">2</span>
                                <input type="checkbox" style="cursor: pointer;">
                            </div>
                        </td>
                        <td>
                            <strong style="color: var(--color-primary); cursor: pointer;" onclick="openRecordDetailModal('PO-771205', 'Heavy-Duty 42U Server Racks (15 units)', '$18,500 USD', 'In Production')">
                                PO-771205
                            </strong>
                        </td>
                        <td>Heavy-Duty 42U Server Racks (15 units)</td>
                        <td>Titan Steel &amp; Enclosures</td>
                        <td><strong style="color: var(--zinc-950);">$18,500 USD</strong></td>
                        <td><span class="badge badge-amber">In Production</span></td>
                        <td>In 20 Days</td>
                        <td><span class="badge badge-gray">Net 30</span></td>
                        <td>
                            <span style="display: inline-flex; align-items: center; gap: 5px;">
                                <span style="width: 18px; height: 18px; border-radius: 50%; background: #e0e7ff; color: #4338ca; font-size: 0.625rem; font-weight: 700; display: inline-flex; align-items: center; justify-content: center;">M</span>
                                <span>Marcus Brody</span>
                            </span>
                        </td>
                        <td class="sticky-actions-col" style="text-align: right;">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="openRecordDetailModal('PO-771205', 'Heavy-Duty 42U Server Racks (15 units)', '$18,500 USD', 'In Production')">Inspect</button>
                        </td>
                    </tr>

                    <tr data-stage="Completed">
                        <td class="col-index">
                            <div style="display: flex; align-items: center; justify-content: center; gap: 5px;">
                                <span style="font-size: 0.725rem; color: var(--zinc-400);">3</span>
                                <input type="checkbox" style="cursor: pointer;">
                            </div>
                        </td>
                        <td>
                            <strong style="color: var(--color-primary); cursor: pointer;" onclick="openRecordDetailModal('PO-554210', 'High-Speed Fiber Transceivers (300 units)', '$42,000 USD', 'Completed')">
                                PO-554210
                            </strong>
                        </td>
                        <td>High-Speed Fiber Transceivers (300 units)</td>
                        <td>OptiLink Networks</td>
                        <td><strong style="color: var(--zinc-950);">$42,000 USD</strong></td>
                        <td><span class="badge badge-green">Completed</span></td>
                        <td>Delivered</td>
                        <td><span class="badge badge-gray">Net 15</span></td>
                        <td>
                            <span style="display: inline-flex; align-items: center; gap: 5px;">
                                <span style="width: 18px; height: 18px; border-radius: 50%; background: #e0f2fe; color: #0369a1; font-size: 0.625rem; font-weight: 700; display: inline-flex; align-items: center; justify-content: center;">A</span>
                                <span>Alex Morgan</span>
                            </span>
                        </td>
                        <td class="sticky-actions-col" style="text-align: right;">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="openRecordDetailModal('PO-554210', 'High-Speed Fiber Transceivers (300 units)', '$42,000 USD', 'Completed')">Inspect</button>
                        </td>
                    </tr>

                    <tr data-stage="Quote Received">
                        <td class="col-index">
                            <div style="display: flex; align-items: center; justify-content: center; gap: 5px;">
                                <span style="font-size: 0.725rem; color: var(--zinc-400);">4</span>
                                <input type="checkbox" style="cursor: pointer;">
                            </div>
                        </td>
                        <td>
                            <strong style="color: var(--color-primary); cursor: pointer;" onclick="openRecordDetailModal('PO-662910', 'Redundant Power Distribution Units (24 units)', '$24,000 USD', 'Quote Received')">
                                PO-662910
                            </strong>
                        </td>
                        <td>Redundant Power Distribution Units (24 units)</td>
                        <td>PowerGrid Components</td>
                        <td><strong style="color: var(--zinc-950);">$24,000 USD</strong></td>
                        <td><span class="badge badge-purple">Quote Received</span></td>
                        <td>In 35 Days</td>
                        <td><span class="badge badge-gray">Advance Wire</span></td>
                        <td>
                            <span style="display: inline-flex; align-items: center; gap: 5px;">
                                <span style="width: 18px; height: 18px; border-radius: 50%; background: #e0e7ff; color: #4338ca; font-size: 0.625rem; font-weight: 700; display: inline-flex; align-items: center; justify-content: center;">M</span>
                                <span>Marcus Brody</span>
                            </span>
                        </td>
                        <td class="sticky-actions-col" style="text-align: right;">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="openRecordDetailModal('PO-662910', 'Redundant Power Distribution Units (24 units)', '$24,000 USD', 'Quote Received')">Inspect</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Table Footer -->
        <div style="padding: 0.5rem 1rem; background: var(--zinc-50); border-top: 1px solid var(--border-card); display: flex; justify-content: space-between; align-items: center; font-size: 0.725rem; color: var(--zinc-500);">
            <div>Showing <strong>4</strong> procurement purchase deals ($138.5k committed spend)</div>
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
    function filterPoStage(stage) {
        const rows = document.querySelectorAll('#po-table tbody tr');
        rows.forEach(r => {
            if (stage === 'all' || r.getAttribute('data-stage') === stage) {
                r.style.display = '';
            } else {
                r.style.display = 'none';
            }
        });
        document.querySelectorAll('.po-filter-btn').forEach(btn => {
            btn.style.background = '';
            btn.style.color = '';
            btn.classList.add('btn-secondary');
        });
        event.currentTarget.classList.remove('btn-secondary');
        event.currentTarget.style.background = 'var(--zinc-900)';
        event.currentTarget.style.color = '#ffffff';
    }

    function openRecordDetailModal(po, desc, spend, stage) {
        const modal = document.getElementById('record-detail-modal');
        if (modal) {
            const title = document.getElementById('modal-detail-title');
            if (title) title.innerText = po + ' — ' + desc;
            modal.classList.add('is-open');
        }
    }
</script>
@endsection
