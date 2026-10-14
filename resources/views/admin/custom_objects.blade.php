@extends('layouts.app')

@section('title', 'Custom Objects Studio — DealSync Admin')

@section('content')
<div class="admin-page-container animate-fade-in">
    @include('partials.admin_nav')

    <!-- Top Action Header -->
    <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                <span class="badge badge-indigo" style="font-size: 0.7rem; font-weight: 700;">Multi-Tenant Custom Objects Engine</span>
                <span style="font-size: 0.725rem; color: var(--zinc-500);">Dynamic Entity Provisioning, Relational Lookups &amp; Schema Builder</span>
            </div>
            <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--zinc-950); letter-spacing: -0.03em; margin: 0; line-height: 1.2;">
                Custom Objects Studio
            </h1>
            <p style="font-size: 0.8125rem; color: var(--zinc-500); margin: 0.25rem 0 0 0;">
                Create new business entities (e.g. Master Contracts, Hardware Assets, RMA Returns) with relational integrity and automatic UI views.
            </p>
        </div>

        <div style="display: flex; align-items: center; gap: 0.6rem;">
            <button type="button" class="btn btn-secondary btn-sm" onclick="showToast('Exporting custom object schemas to JSON...')">
                <i data-lucide="download" style="width: 14px; height: 14px;"></i>
                <span>Export Schemas</span>
            </button>
            <button type="button" class="btn btn-primary btn-sm" onclick="showToast('+ New Custom Object wizard opened!')">
                <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
                <span>+ Provision Custom Object</span>
            </button>
        </div>
    </div>

    <!-- KPI Summary Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
        <div class="card" style="padding: 1rem;">
            <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500); margin-bottom: 0.25rem;">CUSTOM OBJECTS</div>
            <div style="font-size: 1.625rem; font-weight: 800; color: var(--color-primary); letter-spacing: -0.03em;">4 Entities</div>
            <span style="font-size: 0.7rem; color: #059669; font-weight: 600;">100% Deployed &amp; Active</span>
        </div>

        <div class="card" style="padding: 1rem;">
            <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500); margin-bottom: 0.25rem;">RECORDS STORED</div>
            <div style="font-size: 1.625rem; font-weight: 800; color: #0284c7; letter-spacing: -0.03em;">328 Records</div>
            <span style="font-size: 0.7rem; color: var(--zinc-500); font-weight: 500;">Contracts &amp; Hardware Assets</span>
        </div>

        <div class="card" style="padding: 1rem;">
            <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500); margin-bottom: 0.25rem;">RELATIONAL LOOKUPS</div>
            <div style="font-size: 1.625rem; font-weight: 800; color: #7c3aed; letter-spacing: -0.03em;">7 Relationships</div>
            <span style="font-size: 0.7rem; color: var(--zinc-500); font-weight: 500;">Linked to Accounts &amp; POs</span>
        </div>

        <div class="card" style="padding: 1rem;">
            <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500); margin-bottom: 0.25rem;">DB ENGINE OVERHEAD</div>
            <div style="font-size: 1.625rem; font-weight: 800; color: #059669; letter-spacing: -0.03em;">0 Migrations</div>
            <span style="font-size: 0.7rem; color: #059669; font-weight: 600;">Pure JSON Document Storage</span>
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
                    id="objects-search-input"
                    placeholder="Search custom objects..."
                    oninput="filterTableRows('custom-objects-table', this.value)"
                    style="width: 100%; padding: 0.4rem 0.75rem 0.4rem 2.25rem; font-size: 0.8rem; height: 32px; border-radius: 8px; border: 1px solid var(--zinc-300); background: var(--bg-card); color: var(--text-primary);"
                >
            </div>
            <span style="font-size: 0.75rem; color: var(--zinc-500); white-space: nowrap;">
                Showing <strong>4</strong> Custom Objects
            </span>
        </div>

        <!-- Right: Density & Reset Controls -->
        <div style="display: flex; align-items: center; gap: 0.6rem;">
            <div style="display: flex; gap: 2px; background: var(--zinc-100); padding: 2px; border-radius: 6px; border: 1px solid var(--zinc-200);">
                <button type="button" class="btn btn-secondary btn-sm" onclick="setTableDensity('custom-objects-table', 'compact')" title="Compact density" style="padding: 0.25rem 0.5rem; font-size: 0.7rem;">
                    Compact
                </button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="setTableDensity('custom-objects-table', 'comfortable')" title="Comfortable density" style="padding: 0.25rem 0.5rem; font-size: 0.7rem;">
                    Comfortable
                </button>
            </div>

            <button type="button" class="btn btn-secondary btn-sm" onclick="resetTableColWidths('custom-objects-table')" title="Reset column widths" style="padding: 0.25rem 0.55rem; font-size: 0.7rem;">
                <i data-lucide="rotate-ccw" style="width: 12px; height: 12px;"></i>
                <span>Reset Widths</span>
            </button>
        </div>
    </div>

    <!-- Custom Objects Table -->
    <div class="card-table-wrapper">
        <table class="data-table" id="custom-objects-table" data-storage-key="dealsync_col_widths_custom_objects">
            <thead>
                <tr>
                    <th style="width: 40px; text-align: center;">
                        <input type="checkbox" onchange="toggleSelectAll('custom-objects-table', this.checked)">
                    </th>
                    <th class="sortable" onclick="sortTable('custom-objects-table', 1)" data-col="api_name" style="min-width: 180px;">
                        Object API Name
                        <span class="sort-icon">▲</span>
                        <div class="col-resizer"></div>
                    </th>
                    <th class="sortable" onclick="sortTable('custom-objects-table', 2)" data-col="label" style="min-width: 180px;">
                        Display Label
                        <span class="sort-icon">▲</span>
                        <div class="col-resizer"></div>
                    </th>
                    <th class="sortable" onclick="sortTable('custom-objects-table', 3)" data-col="plural" style="min-width: 170px;">
                        Plural Title
                        <span class="sort-icon">▲</span>
                        <div class="col-resizer"></div>
                    </th>
                    <th class="sortable" onclick="sortTable('custom-objects-table', 4)" data-col="records" style="min-width: 130px;">
                        Records
                        <span class="sort-icon">▲</span>
                        <div class="col-resizer"></div>
                    </th>
                    <th class="sortable" onclick="sortTable('custom-objects-table', 5)" data-col="sharing" style="min-width: 160px;">
                        Default Sharing
                        <span class="sort-icon">▲</span>
                        <div class="col-resizer"></div>
                    </th>
                    <th class="sortable" onclick="sortTable('custom-objects-table', 6)" data-col="status" style="min-width: 110px;">
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
                        <input type="checkbox" class="row-checkbox" onchange="updateSelectedCount('custom-objects-table')">
                    </td>
                    <td><code>c_contracts</code></td>
                    <td><strong>Master Client Contract</strong></td>
                    <td>Contracts</td>
                    <td><strong>42 Records</strong></td>
                    <td><span class="badge badge-indigo">Private (Owner Only)</span></td>
                    <td><span class="badge badge-green">Deployed</span></td>
                    <td style="text-align: right;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="showToast('Configuring c_contracts schema')">Fields</button>
                    </td>
                </tr>

                <tr>
                    <td style="text-align: center;">
                        <input type="checkbox" class="row-checkbox" onchange="updateSelectedCount('custom-objects-table')">
                    </td>
                    <td><code>c_hardware_assets</code></td>
                    <td><strong>IT Hardware Asset</strong></td>
                    <td>Hardware Assets</td>
                    <td><strong>194 Records</strong></td>
                    <td><span class="badge badge-blue">Public Read-Only</span></td>
                    <td><span class="badge badge-green">Deployed</span></td>
                    <td style="text-align: right;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="showToast('Configuring c_hardware_assets schema')">Fields</button>
                    </td>
                </tr>

                <tr>
                    <td style="text-align: center;">
                        <input type="checkbox" class="row-checkbox" onchange="updateSelectedCount('custom-objects-table')">
                    </td>
                    <td><code>c_rma_warranties</code></td>
                    <td><strong>Warranty RMA Return</strong></td>
                    <td>RMA Warranties</td>
                    <td><strong>68 Records</strong></td>
                    <td><span class="badge badge-purple">Controlled by Parent</span></td>
                    <td><span class="badge badge-green">Deployed</span></td>
                    <td style="text-align: right;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="showToast('Configuring c_rma_warranties schema')">Fields</button>
                    </td>
                </tr>

                <tr>
                    <td style="text-align: center;">
                        <input type="checkbox" class="row-checkbox" onchange="updateSelectedCount('custom-objects-table')">
                    </td>
                    <td><code>c_certifications</code></td>
                    <td><strong>ISO/SOC Compliance Cert</strong></td>
                    <td>Certifications</td>
                    <td><strong>24 Records</strong></td>
                    <td><span class="badge badge-green">Public Read / Write</span></td>
                    <td><span class="badge badge-green">Deployed</span></td>
                    <td style="text-align: right;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="showToast('Configuring c_certifications schema')">Fields</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
