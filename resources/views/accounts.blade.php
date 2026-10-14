@extends('layouts.app')

@section('title', 'Accounts — DealSync Enterprise CRM')

@section('content')
<div class="accounts-page-container animate-fade-in">
    <!-- Top Action Header -->
    <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                <span class="badge badge-blue" style="font-size: 0.7rem; font-weight: 700;">Master Organization Ledger</span>
                <span style="font-size: 0.725rem; color: var(--zinc-500);">360° Consolidated Operations Graph</span>
            </div>
            <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--zinc-950); letter-spacing: -0.03em; margin: 0; line-height: 1.2;">
                Accounts Directory
            </h1>
            <p style="font-size: 0.8125rem; color: var(--zinc-500); margin: 0.25rem 0 0 0;">
                Master company ledger consolidating sell-side customer contracts, supplier procurement POs, credit lines, and financial aging.
            </p>
        </div>

        <div style="display: flex; align-items: center; gap: 0.6rem;">
            <!-- Filter Dropdown Trigger -->
            <button type="button" class="btn btn-secondary btn-sm" onclick="showToast('Filtered: Showing all registered accounts')">
                <i data-lucide="filter" style="width: 14px; height: 14px;"></i>
                <span>Filter</span>
            </button>

            <!-- Export -->
            <button type="button" class="btn btn-secondary btn-sm" onclick="showToast('Exporting accounts to CSV...')">
                <i data-lucide="download" style="width: 14px; height: 14px;"></i>
                <span>Export CSV</span>
            </button>

            <!-- Create Account Button -->
            <button type="button" class="btn btn-primary btn-sm" onclick="openNewRecordModal('accounts')">
                <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
                <span>+ Add Account</span>
            </button>
        </div>
    </div>

    <!-- Top KPI Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
        <div class="card" style="padding: 1rem;">
            <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500); margin-bottom: 0.25rem;">TOTAL ORGANIZATIONS</div>
            <div style="font-size: 1.625rem; font-weight: 800; color: var(--zinc-950); letter-spacing: -0.03em;">4 Accounts</div>
            <span style="font-size: 0.7rem; color: #059669; font-weight: 600;">100% Verified RLS Tenants</span>
        </div>

        <div class="card" style="padding: 1rem;">
            <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500); margin-bottom: 0.25rem;">ACTIVE CUSTOMERS</div>
            <div style="font-size: 1.625rem; font-weight: 800; color: var(--color-primary); letter-spacing: -0.03em;">2 Clients</div>
            <span style="font-size: 0.7rem; color: var(--zinc-500); font-weight: 500;">$36.0M Aggregate ARR</span>
        </div>

        <div class="card" style="padding: 1rem;">
            <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500); margin-bottom: 0.25rem;">SUPPLIERS &amp; VENDORS</div>
            <div style="font-size: 1.625rem; font-weight: 800; color: #d97706; letter-spacing: -0.03em;">2 Partners</div>
            <span style="font-size: 0.7rem; color: var(--zinc-500); font-weight: 500;">$63.0M Procurement Capacity</span>
        </div>

        <div class="card" style="padding: 1rem;">
            <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500); margin-bottom: 0.25rem;">TOTAL CREDIT LINE</div>
            <div style="font-size: 1.625rem; font-weight: 800; color: #059669; letter-spacing: -0.03em;">$525,000</div>
            <span style="font-size: 0.7rem; color: var(--zinc-500); font-weight: 500;">Average Net 45 Terms</span>
        </div>
    </div>

    <!-- Quick Classification Filter Chips -->
    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem; overflow-x: auto; padding-bottom: 0.25rem;">
        <span style="font-size: 0.75rem; font-weight: 600; color: var(--zinc-500); margin-right: 0.25rem;">Classifications:</span>
        <button type="button" class="btn btn-sm account-filter-btn" onclick="filterAccountCategory('all')" style="background: var(--zinc-900); color: #ffffff; border-radius: 9999px; font-size: 0.725rem; padding: 0.15rem 0.75rem;">
            All Accounts (4)
        </button>
        <button type="button" class="btn btn-secondary btn-sm account-filter-btn" onclick="filterAccountCategory('Customer')" style="border-radius: 9999px; font-size: 0.725rem; padding: 0.15rem 0.75rem;">
            Customers (2)
        </button>
        <button type="button" class="btn btn-secondary btn-sm account-filter-btn" onclick="filterAccountCategory('Vendor')" style="border-radius: 9999px; font-size: 0.725rem; padding: 0.15rem 0.75rem;">
            Vendors / Suppliers (2)
        </button>
        <button type="button" class="btn btn-secondary btn-sm account-filter-btn" onclick="filterAccountCategory('Partner')" style="border-radius: 9999px; font-size: 0.725rem; padding: 0.15rem 0.75rem;">
            Strategic Partners (0)
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
                    id="accounts-search-input"
                    placeholder="Search accounts by name, industry, phone..."
                    oninput="filterTableRows('accounts-table', this.value)"
                    style="width: 100%; padding: 0.4rem 0.75rem 0.4rem 2.25rem; font-size: 0.8rem; height: 32px; border-radius: 8px; border: 1px solid var(--zinc-300); background: var(--bg-card); color: var(--text-primary);"
                >
            </div>
            <span style="font-size: 0.75rem; color: var(--zinc-500); white-space: nowrap;">
                Showing <strong>4</strong> accounts
            </span>
        </div>

        <!-- Right: Density & Reset Controls -->
        <div style="display: flex; align-items: center; gap: 0.6rem;">
            <!-- Density Toggle -->
            <div style="display: flex; gap: 2px; background: var(--zinc-100); padding: 2px; border-radius: 6px; border: 1px solid var(--zinc-200);">
                <button type="button" class="btn btn-secondary btn-sm" onclick="setTableDensity('accounts-table', 'compact')" title="Compact density (30px rows)" style="padding: 0.25rem 0.5rem; font-size: 0.7rem;">
                    Compact
                </button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="setTableDensity('accounts-table', 'comfortable')" title="Comfortable density (44px rows)" style="padding: 0.25rem 0.5rem; font-size: 0.7rem;">
                    Comfortable
                </button>
            </div>

            <!-- Reset Column Widths -->
            <button type="button" class="btn btn-secondary btn-sm" onclick="resetTableColWidths('accounts-table')" title="Reset column widths" style="padding: 0.25rem 0.55rem; font-size: 0.7rem;">
                <i data-lucide="rotate-ccw" style="width: 12px; height: 12px;"></i>
                <span>Reset Widths</span>
            </button>
        </div>
    </div>

    <!-- Accounts Data Table -->
    <div class="card-table-wrapper">
        <div class="table-scroll-container">
            <table id="accounts-table" class="data-table">
                <thead>
                    <tr>
                        <!-- Master Checkbox -->
                        <th class="col-index" style="width: 58px; min-width: 58px; text-align: center;">
                            <input type="checkbox" title="Select all accounts" style="cursor: pointer; width: 14px; height: 14px;">
                        </th>
                        <th data-col="name" style="width: 220px; min-width: 160px;">Account Name</th>
                        <th data-col="type" style="width: 130px; min-width: 100px;">Classification</th>
                        <th data-col="industry" style="width: 160px; min-width: 120px;">Industry Sector</th>
                        <th data-col="revenue" style="width: 150px; min-width: 120px;">Annual Revenue</th>
                        <th data-col="credit" style="width: 140px; min-width: 110px;">Credit Limit</th>
                        <th data-col="terms" style="width: 120px; min-width: 90px;">Net Terms</th>
                        <th data-col="phone" style="width: 150px; min-width: 120px;">Corporate Phone</th>
                        <th data-col="billing" style="width: 240px; min-width: 180px;">Primary Billing Address</th>
                        <th data-col="owner" style="width: 140px; min-width: 110px;">Account Owner</th>
                        <th class="no-sort sticky-actions-col" style="width: 140px; min-width: 130px; text-align: right;">360° Operations</th>
                    </tr>
                </thead>
                <tbody>
                    <tr data-type="Customer">
                        <td class="col-index">
                            <div style="display: flex; align-items: center; justify-content: center; gap: 5px;">
                                <span style="font-size: 0.725rem; color: var(--zinc-400);">1</span>
                                <input type="checkbox" style="cursor: pointer;">
                            </div>
                        </td>
                        <td>
                            <strong style="color: var(--zinc-950); cursor: pointer;" onclick="openRecordDetailModal('Skyline Cloud Systems', 'San Francisco, CA', '$12M ARR', 'Active Customer')">
                                Skyline Cloud Systems
                            </strong>
                        </td>
                        <td><span class="badge badge-blue">Customer</span></td>
                        <td>Software &amp; Cloud</td>
                        <td><strong style="color: var(--zinc-950);">$12,000,000</strong></td>
                        <td>$75,000</td>
                        <td><span class="badge badge-gray">Net 30</span></td>
                        <td><span style="font-family: monospace; font-size: 0.775rem;">+1 (415) 890-1200</span></td>
                        <td>100 Market St, San Francisco, CA</td>
                        <td>
                            <span style="display: inline-flex; align-items: center; gap: 5px;">
                                <span style="width: 18px; height: 18px; border-radius: 50%; background: #e0f2fe; color: #0369a1; font-size: 0.625rem; font-weight: 700; display: inline-flex; align-items: center; justify-content: center;">A</span>
                                <span>Alex Morgan</span>
                            </span>
                        </td>
                        <td class="sticky-actions-col" style="text-align: right;">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="openRecordDetailModal('Skyline Cloud Systems', 'San Francisco, CA', '$12M ARR', 'Active Customer')">
                                360° Profile
                            </button>
                        </td>
                    </tr>

                    <tr data-type="Customer">
                        <td class="col-index">
                            <div style="display: flex; align-items: center; justify-content: center; gap: 5px;">
                                <span style="font-size: 0.725rem; color: var(--zinc-400);">2</span>
                                <input type="checkbox" style="cursor: pointer;">
                            </div>
                        </td>
                        <td>
                            <strong style="color: var(--zinc-950); cursor: pointer;" onclick="openRecordDetailModal('Vanguard Health Tech', 'Boston, MA', '$24M ARR', 'Active Customer')">
                                Vanguard Health Tech
                            </strong>
                        </td>
                        <td><span class="badge badge-blue">Customer</span></td>
                        <td>Healthcare Tech</td>
                        <td><strong style="color: var(--zinc-950);">$24,000,000</strong></td>
                        <td>$120,000</td>
                        <td><span class="badge badge-gray">Net 45</span></td>
                        <td><span style="font-family: monospace; font-size: 0.775rem;">+1 (617) 555-0199</span></td>
                        <td>200 Boylston St, Boston, MA</td>
                        <td>
                            <span style="display: inline-flex; align-items: center; gap: 5px;">
                                <span style="width: 18px; height: 18px; border-radius: 50%; background: #fef3c7; color: #b45309; font-size: 0.625rem; font-weight: 700; display: inline-flex; align-items: center; justify-content: center;">J</span>
                                <span>Jordan Lee</span>
                            </span>
                        </td>
                        <td class="sticky-actions-col" style="text-align: right;">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="openRecordDetailModal('Vanguard Health Tech', 'Boston, MA', '$24M ARR', 'Active Customer')">
                                360° Profile
                            </button>
                        </td>
                    </tr>

                    <tr data-type="Vendor">
                        <td class="col-index">
                            <div style="display: flex; align-items: center; justify-content: center; gap: 5px;">
                                <span style="font-size: 0.725rem; color: var(--zinc-400);">3</span>
                                <input type="checkbox" style="cursor: pointer;">
                            </div>
                        </td>
                        <td>
                            <strong style="color: var(--zinc-950); cursor: pointer;" onclick="openRecordDetailModal('Quantum Silicon Components', 'San Jose, CA', '$45M Revenue', 'Approved Vendor')">
                                Quantum Silicon Components
                            </strong>
                        </td>
                        <td><span class="badge badge-indigo">Vendor</span></td>
                        <td>Semiconductors</td>
                        <td><strong style="color: var(--zinc-950);">$45,000,000</strong></td>
                        <td>$250,000</td>
                        <td><span class="badge badge-gray">Net 60</span></td>
                        <td><span style="font-family: monospace; font-size: 0.775rem;">+1 (408) 555-4321</span></td>
                        <td>800 Innovation Way, San Jose, CA</td>
                        <td>
                            <span style="display: inline-flex; align-items: center; gap: 5px;">
                                <span style="width: 18px; height: 18px; border-radius: 50%; background: #e0e7ff; color: #4338ca; font-size: 0.625rem; font-weight: 700; display: inline-flex; align-items: center; justify-content: center;">M</span>
                                <span>Marcus Brody</span>
                            </span>
                        </td>
                        <td class="sticky-actions-col" style="text-align: right;">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="openRecordDetailModal('Quantum Silicon Components', 'San Jose, CA', '$45M Revenue', 'Approved Vendor')">
                                360° Profile
                            </button>
                        </td>
                    </tr>

                    <tr data-type="Vendor">
                        <td class="col-index">
                            <div style="display: flex; align-items: center; justify-content: center; gap: 5px;">
                                <span style="font-size: 0.725rem; color: var(--zinc-400);">4</span>
                                <input type="checkbox" style="cursor: pointer;">
                            </div>
                        </td>
                        <td>
                            <strong style="color: var(--zinc-950); cursor: pointer;" onclick="openRecordDetailModal('Titan Steel & Enclosures', 'Chicago, IL', '$18M Revenue', 'Approved Vendor')">
                                Titan Steel &amp; Enclosures
                            </strong>
                        </td>
                        <td><span class="badge badge-indigo">Vendor</span></td>
                        <td>Industrial Manufacturing</td>
                        <td><strong style="color: var(--zinc-950);">$18,000,000</strong></td>
                        <td>$80,000</td>
                        <td><span class="badge badge-gray">Net 30</span></td>
                        <td><span style="font-family: monospace; font-size: 0.775rem;">+1 (312) 555-7890</span></td>
                        <td>450 Industrial Pkwy, Chicago, IL</td>
                        <td>
                            <span style="display: inline-flex; align-items: center; gap: 5px;">
                                <span style="width: 18px; height: 18px; border-radius: 50%; background: #e0e7ff; color: #4338ca; font-size: 0.625rem; font-weight: 700; display: inline-flex; align-items: center; justify-content: center;">M</span>
                                <span>Marcus Brody</span>
                            </span>
                        </td>
                        <td class="sticky-actions-col" style="text-align: right;">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="openRecordDetailModal('Titan Steel & Enclosures', 'Chicago, IL', '$18M Revenue', 'Approved Vendor')">
                                360° Profile
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Table Footer -->
        <div style="padding: 0.5rem 1rem; background: var(--zinc-50); border-top: 1px solid var(--border-card); display: flex; justify-content: space-between; align-items: center; font-size: 0.725rem; color: var(--zinc-500);">
            <div>Showing <strong>4</strong> accounts (Full 360° Consolidation Linked)</div>
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
    function filterAccountCategory(type) {
        const rows = document.querySelectorAll('#accounts-table tbody tr');
        rows.forEach(r => {
            if (type === 'all' || r.getAttribute('data-type') === type) {
                r.style.display = '';
            } else {
                r.style.display = 'none';
            }
        });
        document.querySelectorAll('.account-filter-btn').forEach(btn => {
            btn.style.background = '';
            btn.style.color = '';
            btn.classList.add('btn-secondary');
        });
        event.currentTarget.classList.remove('btn-secondary');
        event.currentTarget.style.background = 'var(--zinc-900)';
        event.currentTarget.style.color = '#ffffff';
    }

    function openRecordDetailModal(name, location, revenue, status) {
        const modal = document.getElementById('record-detail-modal');
        if (modal) {
            const title = document.getElementById('modal-detail-title');
            if (title) title.innerText = name + ' (' + location + ')';
            modal.classList.add('is-open');
        }
    }
</script>
@endsection
