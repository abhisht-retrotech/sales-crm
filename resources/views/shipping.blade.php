@extends('layouts.app')

@section('title', 'Shipping & Logistics — DealSync Enterprise CRM')

@section('content')
<div class="shipping-page-container animate-fade-in">
    <!-- Top Action Header -->
    <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                <span class="badge badge-amber" style="font-size: 0.7rem; font-weight: 700;">Bi-Directional Freight Management</span>
                <span style="font-size: 0.725rem; color: var(--zinc-500);">Outbound Customer Orders &amp; Inbound Vendor Deliveries</span>
            </div>
            <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--zinc-950); letter-spacing: -0.03em; margin: 0; line-height: 1.2;">
                Shipping &amp; Logistics Freight
            </h1>
            <p style="font-size: 0.8125rem; color: var(--zinc-500); margin: 0.25rem 0 0 0;">
                Track carrier manifests, dispatch routes, custom clearances, and multi-leg courier delivery milestones.
            </p>
        </div>

        <div style="display: flex; align-items: center; gap: 0.6rem;">
            <!-- Filter Dropdown Trigger -->
            <button type="button" class="btn btn-secondary btn-sm" onclick="showToast('Filtered: Showing active freight manifests')">
                <i data-lucide="filter" style="width: 14px; height: 14px;"></i>
                <span>Filter</span>
            </button>

            <!-- Export -->
            <button type="button" class="btn btn-secondary btn-sm" onclick="showToast('Exporting manifests to CSV...')">
                <i data-lucide="download" style="width: 14px; height: 14px;"></i>
                <span>Export CSV</span>
            </button>

            <!-- New Shipment Button -->
            <button type="button" class="btn btn-primary btn-sm" onclick="openNewRecordModal('shipping')">
                <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
                <span>+ New Shipment</span>
            </button>
        </div>
    </div>

    <!-- Top KPI Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
        <div class="card" style="padding: 1rem;">
            <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500); margin-bottom: 0.25rem;">IN-TRANSIT FREIGHT</div>
            <div style="font-size: 1.625rem; font-weight: 800; color: var(--color-primary); letter-spacing: -0.03em;">2 En Route</div>
            <span style="font-size: 0.7rem; color: var(--zinc-500); font-weight: 500;">Active Courier Tracking</span>
        </div>

        <div class="card" style="padding: 1rem;">
            <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500); margin-bottom: 0.25rem;">CUSTOMS CLEARANCE</div>
            <div style="font-size: 1.625rem; font-weight: 800; color: #d97706; letter-spacing: -0.03em;">1 Manifest</div>
            <span style="font-size: 0.7rem; color: #059669; font-weight: 600;">Port of Long Beach</span>
        </div>

        <div class="card" style="padding: 1rem;">
            <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500); margin-bottom: 0.25rem;">DELIVERED THIS WEEK</div>
            <div style="font-size: 1.625rem; font-weight: 800; color: #059669; letter-spacing: -0.03em;">1 Delivered</div>
            <span style="font-size: 0.7rem; color: #059669; font-weight: 600;">Signed Proof Received</span>
        </div>

        <div class="card" style="padding: 1rem;">
            <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500); margin-bottom: 0.25rem;">CARRIER ON-TIME SLA</div>
            <div style="font-size: 1.625rem; font-weight: 800; color: var(--zinc-950); letter-spacing: -0.03em;">98.4%</div>
            <span style="font-size: 0.7rem; color: var(--zinc-500); font-weight: 500;">FedEx &amp; DHL Express</span>
        </div>
    </div>

    <!-- Quick Direction Filter Chips -->
    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem; overflow-x: auto; padding-bottom: 0.25rem;">
        <span style="font-size: 0.75rem; font-weight: 600; color: var(--zinc-500); margin-right: 0.25rem;">Direction:</span>
        <button type="button" class="btn btn-sm ship-filter-btn" onclick="filterShippingDirection('all')" style="background: var(--zinc-900); color: #ffffff; border-radius: 9999px; font-size: 0.725rem; padding: 0.15rem 0.75rem;">
            All Manifests (4)
        </button>
        <button type="button" class="btn btn-secondary btn-sm ship-filter-btn" onclick="filterShippingDirection('Outbound')" style="border-radius: 9999px; font-size: 0.725rem; padding: 0.15rem 0.75rem;">
            Outbound Deliveries (2)
        </button>
        <button type="button" class="btn btn-secondary btn-sm ship-filter-btn" onclick="filterShippingDirection('Inbound')" style="border-radius: 9999px; font-size: 0.725rem; padding: 0.15rem 0.75rem;">
            Inbound Freight (2)
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
                    id="shipping-search-input"
                    placeholder="Search shipments by tracking code, carrier..."
                    oninput="filterTableRows('shipping-table', this.value)"
                    style="width: 100%; padding: 0.4rem 0.75rem 0.4rem 2.25rem; font-size: 0.8rem; height: 32px; border-radius: 8px; border: 1px solid var(--zinc-300); background: var(--bg-card); color: var(--text-primary);"
                >
            </div>
            <span style="font-size: 0.75rem; color: var(--zinc-500); white-space: nowrap;">
                Showing <strong>4</strong> manifests
            </span>
        </div>

        <!-- Right: Density & Reset Controls -->
        <div style="display: flex; align-items: center; gap: 0.6rem;">
            <!-- Density Toggle -->
            <div style="display: flex; gap: 2px; background: var(--zinc-100); padding: 2px; border-radius: 6px; border: 1px solid var(--zinc-200);">
                <button type="button" class="btn btn-secondary btn-sm" onclick="setTableDensity('shipping-table', 'compact')" title="Compact density (30px rows)" style="padding: 0.25rem 0.5rem; font-size: 0.7rem;">
                    Compact
                </button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="setTableDensity('shipping-table', 'comfortable')" title="Comfortable density (44px rows)" style="padding: 0.25rem 0.5rem; font-size: 0.7rem;">
                    Comfortable
                </button>
            </div>

            <!-- Reset Column Widths -->
            <button type="button" class="btn btn-secondary btn-sm" onclick="resetTableColWidths('shipping-table')" title="Reset column widths" style="padding: 0.25rem 0.55rem; font-size: 0.7rem;">
                <i data-lucide="rotate-ccw" style="width: 12px; height: 12px;"></i>
                <span>Reset Widths</span>
            </button>
        </div>
    </div>

    <!-- Shipping Table -->
    <div class="card-table-wrapper">
        <div class="table-scroll-container">
            <table id="shipping-table" class="data-table">
                <thead>
                    <tr>
                        <!-- Master Checkbox -->
                        <th class="col-index" style="width: 58px; min-width: 58px; text-align: center;">
                            <input type="checkbox" title="Select all shipments" style="cursor: pointer; width: 14px; height: 14px;">
                        </th>
                        <th data-col="tracking" style="width: 170px; min-width: 130px;">Tracking Code</th>
                        <th data-col="direction" style="width: 120px; min-width: 90px;">Direction</th>
                        <th data-col="carrier" style="width: 150px; min-width: 110px;">Freight Carrier</th>
                        <th data-col="status" style="width: 160px; min-width: 120px;">Milestone Status</th>
                        <th data-col="origin" style="width: 220px; min-width: 160px;">Origin Facility</th>
                        <th data-col="destination" style="width: 240px; min-width: 170px;">Destination Address</th>
                        <th data-col="account" style="width: 190px; min-width: 140px;">Linked Account</th>
                        <th data-col="eta" style="width: 140px; min-width: 110px;">Est. Delivery Date</th>
                        <th class="no-sort sticky-actions-col" style="width: 140px; min-width: 130px; text-align: right;">Live Tracking</th>
                    </tr>
                </thead>
                <tbody>
                    <tr data-direction="Outbound">
                        <td class="col-index">
                            <div style="display: flex; align-items: center; justify-content: center; gap: 5px;">
                                <span style="font-size: 0.725rem; color: var(--zinc-400);">1</span>
                                <input type="checkbox" style="cursor: pointer;">
                            </div>
                        </td>
                        <td>
                            <code style="background: var(--zinc-100); padding: 0.2rem 0.45rem; border-radius: 4px; font-weight: 700; color: var(--zinc-800); cursor: pointer;" onclick="openRecordDetailModal('TRK-98421094', 'FedEx Express', 'In Transit', 'Outbound')">
                                TRK-98421094
                            </code>
                        </td>
                        <td><span class="badge badge-blue">Outbound</span></td>
                        <td>FedEx Express</td>
                        <td><span class="badge badge-amber">● In Transit</span></td>
                        <td>Central Hub, Fremont CA</td>
                        <td>100 Market St, San Francisco CA</td>
                        <td>Skyline Cloud Systems</td>
                        <td>In 2 Days</td>
                        <td class="sticky-actions-col" style="text-align: right;">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="showToast('Live status: Package in transit on local courier route (Fremont CA)')">Live Status</button>
                        </td>
                    </tr>

                    <tr data-direction="Inbound">
                        <td class="col-index">
                            <div style="display: flex; align-items: center; justify-content: center; gap: 5px;">
                                <span style="font-size: 0.725rem; color: var(--zinc-400);">2</span>
                                <input type="checkbox" style="cursor: pointer;">
                            </div>
                        </td>
                        <td>
                            <code style="background: var(--zinc-100); padding: 0.2rem 0.45rem; border-radius: 4px; font-weight: 700; color: var(--zinc-800); cursor: pointer;" onclick="openRecordDetailModal('TRK-33910245', 'UPS Freight', 'Dispatched', 'Inbound')">
                                TRK-33910245
                            </code>
                        </td>
                        <td><span class="badge badge-purple">Inbound</span></td>
                        <td>UPS Freight</td>
                        <td><span class="badge badge-indigo">● Dispatched</span></td>
                        <td>Silicon Valley Fab 3, San Jose CA</td>
                        <td>Acme HQ Assembly, Austin TX</td>
                        <td>Quantum Silicon Components</td>
                        <td>In 5 Days</td>
                        <td class="sticky-actions-col" style="text-align: right;">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="showToast('Live status: Freight dispatched from San Jose packaging depot')">Live Status</button>
                        </td>
                    </tr>

                    <tr data-direction="Outbound">
                        <td class="col-index">
                            <div style="display: flex; align-items: center; justify-content: center; gap: 5px;">
                                <span style="font-size: 0.725rem; color: var(--zinc-400);">3</span>
                                <input type="checkbox" style="cursor: pointer;">
                            </div>
                        </td>
                        <td>
                            <code style="background: var(--zinc-100); padding: 0.2rem 0.45rem; border-radius: 4px; font-weight: 700; color: var(--zinc-800); cursor: pointer;" onclick="openRecordDetailModal('TRK-66102911', 'DHL Global', 'Delivered', 'Outbound')">
                                TRK-66102911
                            </code>
                        </td>
                        <td><span class="badge badge-blue">Outbound</span></td>
                        <td>DHL Global</td>
                        <td><span class="badge badge-green">✓ Delivered</span></td>
                        <td>Austin Distribution Center, TX</td>
                        <td>200 Boylston St, Boston MA</td>
                        <td>Vanguard Health Tech</td>
                        <td>Delivered</td>
                        <td class="sticky-actions-col" style="text-align: right;">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="showToast('Proof of delivery signed by E. Rostova')">Proof POD</button>
                        </td>
                    </tr>

                    <tr data-direction="Inbound">
                        <td class="col-index">
                            <div style="display: flex; align-items: center; justify-content: center; gap: 5px;">
                                <span style="font-size: 0.725rem; color: var(--zinc-400);">4</span>
                                <input type="checkbox" style="cursor: pointer;">
                            </div>
                        </td>
                        <td>
                            <code style="background: var(--zinc-100); padding: 0.2rem 0.45rem; border-radius: 4px; font-weight: 700; color: var(--zinc-800); cursor: pointer;" onclick="openRecordDetailModal('TRK-88192033', 'Maersk Line', 'Customs Clearance', 'Inbound')">
                                TRK-88192033
                            </code>
                        </td>
                        <td><span class="badge badge-purple">Inbound</span></td>
                        <td>Maersk Line</td>
                        <td><span class="badge badge-amber">● Customs Cleared</span></td>
                        <td>Port of Taipei, Taiwan</td>
                        <td>Port of Long Beach, CA</td>
                        <td>Titan Steel &amp; Enclosures</td>
                        <td>In 8 Days</td>
                        <td class="sticky-actions-col" style="text-align: right;">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="showToast('Maritime container vessel moored at Terminal 4')">Live Status</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Table Footer -->
        <div style="padding: 0.5rem 1rem; background: var(--zinc-50); border-top: 1px solid var(--border-card); display: flex; justify-content: space-between; align-items: center; font-size: 0.725rem; color: var(--zinc-500);">
            <div>Showing <strong>4</strong> bi-directional freight shipments (Courier Telemetry Linked)</div>
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
    function filterShippingDirection(dir) {
        const rows = document.querySelectorAll('#shipping-table tbody tr');
        rows.forEach(r => {
            if (dir === 'all' || r.getAttribute('data-direction') === dir) {
                r.style.display = '';
            } else {
                r.style.display = 'none';
            }
        });
        document.querySelectorAll('.ship-filter-btn').forEach(btn => {
            btn.style.background = '';
            btn.style.color = '';
            btn.classList.add('btn-secondary');
        });
        event.currentTarget.classList.remove('btn-secondary');
        event.currentTarget.style.background = 'var(--zinc-900)';
        event.currentTarget.style.color = '#ffffff';
    }

    function openRecordDetailModal(trk, carrier, status, dir) {
        const modal = document.getElementById('record-detail-modal');
        if (modal) {
            const title = document.getElementById('modal-detail-title');
            if (title) title.innerText = trk + ' (' + carrier + ' — ' + status + ')';
            modal.classList.add('is-open');
        }
    }
</script>
@endsection
