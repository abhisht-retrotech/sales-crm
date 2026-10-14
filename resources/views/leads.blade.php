@extends('layouts.app')

@section('title', 'Leads & Intake — DealSync Enterprise CRM')

@section('content')
<div class="leads-page-container animate-fade-in">
    <!-- Top Action Header -->
    <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                <span class="badge badge-purple" style="font-size: 0.7rem; font-weight: 700;">Inbound Demand Generation</span>
                <span style="font-size: 0.725rem; color: var(--zinc-500);">Decoupled 3-Way Atomic Conversion</span>
            </div>
            <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--zinc-950); letter-spacing: -0.03em; margin: 0; line-height: 1.2;">
                Leads &amp; Intake
            </h1>
            <p style="font-size: 0.8125rem; color: var(--zinc-500); margin: 0.25rem 0 0 0;">
                Capture, score, and qualify inbound prospects (0–100 Propensity) with instant decoupled conversion into Accounts and Contacts.
            </p>
        </div>

        <div style="display: flex; align-items: center; gap: 0.6rem;">
            <!-- Filter Dropdown Trigger -->
            <button type="button" class="btn btn-secondary btn-sm" onclick="showToast('Filtered: Showing all active leads')">
                <i data-lucide="filter" style="width: 14px; height: 14px;"></i>
                <span>Filter</span>
            </button>

            <!-- Export -->
            <button type="button" class="btn btn-secondary btn-sm" onclick="showToast('Exporting leads directory to CSV...')">
                <i data-lucide="download" style="width: 14px; height: 14px;"></i>
                <span>Export CSV</span>
            </button>

            <!-- Create Lead Button -->
            <button type="button" class="btn btn-primary btn-sm" onclick="openNewRecordModal('leads')">
                <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
                <span>+ Add Lead</span>
            </button>
        </div>
    </div>

    <!-- Quick Stage Filter Chips -->
    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem; overflow-x: auto; padding-bottom: 0.25rem;">
        <span style="font-size: 0.75rem; font-weight: 600; color: var(--zinc-500); margin-right: 0.25rem;">Stages:</span>
        <button type="button" class="btn btn-sm stage-filter-btn" onclick="filterLeadsStage('all')" style="background: var(--zinc-900); color: #ffffff; border-radius: 9999px; font-size: 0.725rem; padding: 0.15rem 0.75rem;">
            All Leads (6)
        </button>
        <button type="button" class="btn btn-secondary btn-sm stage-filter-btn" onclick="filterLeadsStage('New')" style="border-radius: 9999px; font-size: 0.725rem; padding: 0.15rem 0.75rem;">
            New (1)
        </button>
        <button type="button" class="btn btn-secondary btn-sm stage-filter-btn" onclick="filterLeadsStage('Contacted')" style="border-radius: 9999px; font-size: 0.725rem; padding: 0.15rem 0.75rem;">
            Contacted (2)
        </button>
        <button type="button" class="btn btn-secondary btn-sm stage-filter-btn" onclick="filterLeadsStage('Qualified')" style="border-radius: 9999px; font-size: 0.725rem; padding: 0.15rem 0.75rem;">
            Qualified (2)
        </button>
        <button type="button" class="btn btn-secondary btn-sm stage-filter-btn" onclick="filterLeadsStage('Lost')" style="border-radius: 9999px; font-size: 0.725rem; padding: 0.15rem 0.75rem;">
            Lost (1)
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
                    id="leads-search-input"
                    placeholder="Search leads by name, company, email..."
                    oninput="filterTableRows('leads-table', this.value)"
                    style="width: 100%; padding: 0.4rem 0.75rem 0.4rem 2.25rem; font-size: 0.8rem; height: 32px; border-radius: 8px; border: 1px solid var(--zinc-300); background: var(--bg-card); color: var(--text-primary);"
                >
            </div>
            <span style="font-size: 0.75rem; color: var(--zinc-500); white-space: nowrap;">
                Showing <strong>6</strong> records
            </span>
        </div>

        <!-- Right: View Switcher, Density Toggles, Reset Widths -->
        <div style="display: flex; align-items: center; gap: 0.6rem;">
            <!-- View Switcher (Table vs Kanban) -->
            <div style="display: flex; padding: 0.2rem; background: var(--zinc-100); border-radius: var(--radius-md); border: 1px solid var(--zinc-200);">
                <button
                    type="button"
                    id="btn-view-table"
                    onclick="switchLeadView('table')"
                    style="display: flex; align-items: center; gap: 0.35rem; padding: 0.25rem 0.6rem; font-size: 0.75rem; font-weight: 600; color: var(--zinc-950); background: #ffffff; border: none; border-radius: 5px; cursor: pointer; box-shadow: var(--shadow-xs);"
                >
                    <i data-lucide="table" style="width: 13px; height: 13px;"></i>
                    <span>Table</span>
                </button>
                <button
                    type="button"
                    id="btn-view-kanban"
                    onclick="switchLeadView('kanban')"
                    style="display: flex; align-items: center; gap: 0.35rem; padding: 0.25rem 0.6rem; font-size: 0.75rem; font-weight: 500; color: var(--zinc-500); background: transparent; border: none; border-radius: 5px; cursor: pointer;"
                >
                    <i data-lucide="kanban" style="width: 13px; height: 13px;"></i>
                    <span>Kanban</span>
                </button>
            </div>

            <!-- Density Toggle -->
            <div style="display: flex; gap: 2px; background: var(--zinc-100); padding: 2px; border-radius: 6px; border: 1px solid var(--zinc-200);">
                <button type="button" class="btn btn-secondary btn-sm" onclick="setTableDensity('leads-table', 'compact')" title="Compact density (30px rows)" style="padding: 0.25rem 0.5rem; font-size: 0.7rem;">
                    Compact
                </button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="setTableDensity('leads-table', 'comfortable')" title="Comfortable density (44px rows)" style="padding: 0.25rem 0.5rem; font-size: 0.7rem;">
                    Comfortable
                </button>
            </div>

            <!-- Reset Column Widths -->
            <button type="button" class="btn btn-secondary btn-sm" onclick="resetTableColWidths('leads-table')" title="Reset column widths" style="padding: 0.25rem 0.55rem; font-size: 0.7rem;">
                <i data-lucide="rotate-ccw" style="width: 12px; height: 12px;"></i>
                <span>Reset Widths</span>
            </button>
        </div>
    </div>

    <!-- 1. TABLE VIEW CONTAINER -->
    <div id="leads-table-view" class="card-table-wrapper">
        <div class="table-scroll-container">
            <table id="leads-table" class="data-table">
                <thead>
                    <tr>
                        <!-- Checkbox Master Column -->
                        <th class="col-index" style="width: 58px; min-width: 58px; text-align: center;">
                            <input type="checkbox" title="Select all leads" style="cursor: pointer; width: 14px; height: 14px;">
                        </th>
                        <th data-col="name" style="width: 180px; min-width: 140px;">Lead Name</th>
                        <th data-col="company" style="width: 170px; min-width: 130px;">Company</th>
                        <th data-col="title" style="width: 160px; min-width: 120px;">Job Title</th>
                        <th data-col="score" style="width: 120px; min-width: 100px;">Propensity Score</th>
                        <th data-col="status" style="width: 120px; min-width: 100px;">Qualification Stage</th>
                        <th data-col="email" style="width: 190px; min-width: 140px;">Email Address</th>
                        <th data-col="phone" style="width: 140px; min-width: 110px;">Phone</th>
                        <th data-col="source" style="width: 140px; min-width: 110px;">Source Channel</th>
                        <th data-col="owner" style="width: 140px; min-width: 110px;">Owner / Rep</th>
                        <th class="no-sort sticky-actions-col" style="width: 160px; min-width: 150px; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr data-stage="Qualified">
                        <td class="col-index">
                            <div style="display: flex; align-items: center; justify-content: center; gap: 5px;">
                                <span style="font-size: 0.725rem; color: var(--zinc-400);">1</span>
                                <input type="checkbox" style="cursor: pointer;">
                            </div>
                        </td>
                        <td>
                            <strong style="color: var(--zinc-950); cursor: pointer;" onclick="openRecordDetailModal('Alexander Wright', 'Aurora Financial Tech', '$85,000', 'Qualified')">
                                Alexander Wright
                            </strong>
                        </td>
                        <td>Aurora Financial Tech</td>
                        <td>VP Operations</td>
                        <td>
                            <span class="badge badge-green" style="font-weight: 800; font-size: 0.75rem;">
                                88 / 100 Tier-A
                            </span>
                        </td>
                        <td><span class="badge badge-green">Qualified</span></td>
                        <td><a href="mailto:alex.wright@aurorafin.com" style="color: var(--color-primary); text-decoration: none;">alex.wright@aurorafin.com</a></td>
                        <td><span style="font-family: monospace; font-size: 0.775rem;">+1 (212) 555-9011</span></td>
                        <td>LinkedIn Outreach</td>
                        <td>
                            <span style="display: inline-flex; align-items: center; gap: 5px;">
                                <span style="width: 18px; height: 18px; border-radius: 50%; background: #e0f2fe; color: #0369a1; font-size: 0.625rem; font-weight: 700; display: inline-flex; align-items: center; justify-content: center;">A</span>
                                <span>Alex Morgan</span>
                            </span>
                        </td>
                        <td class="sticky-actions-col" style="text-align: right;">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="openRecordDetailModal('Alexander Wright', 'Aurora Financial Tech', '$85,000', 'Qualified')">Inspect</button>
                            <button type="button" class="btn btn-primary btn-sm" onclick="showToast('Lead Converted! Account and Contact created.')">Convert</button>
                        </td>
                    </tr>

                    <tr data-stage="Contacted">
                        <td class="col-index">
                            <div style="display: flex; align-items: center; justify-content: center; gap: 5px;">
                                <span style="font-size: 0.725rem; color: var(--zinc-400);">2</span>
                                <input type="checkbox" style="cursor: pointer;">
                            </div>
                        </td>
                        <td>
                            <strong style="color: var(--zinc-950); cursor: pointer;" onclick="openRecordDetailModal('Samantha Miller', 'BioGenix Labs', '$120,000', 'Contacted')">
                                Samantha Miller
                            </strong>
                        </td>
                        <td>BioGenix Labs</td>
                        <td>Head of IT Infrastructure</td>
                        <td>
                            <span class="badge badge-amber" style="font-weight: 800; font-size: 0.75rem;">
                                72 / 100 Tier-B
                            </span>
                        </td>
                        <td><span class="badge badge-blue">Contacted</span></td>
                        <td><a href="mailto:smiller@biogenixlab.org" style="color: var(--color-primary); text-decoration: none;">smiller@biogenixlab.org</a></td>
                        <td><span style="font-family: monospace; font-size: 0.775rem;">+1 (206) 555-3344</span></td>
                        <td>Website Form</td>
                        <td>
                            <span style="display: inline-flex; align-items: center; gap: 5px;">
                                <span style="width: 18px; height: 18px; border-radius: 50%; background: #fef3c7; color: #b45309; font-size: 0.625rem; font-weight: 700; display: inline-flex; align-items: center; justify-content: center;">J</span>
                                <span>Jordan Lee</span>
                            </span>
                        </td>
                        <td class="sticky-actions-col" style="text-align: right;">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="openRecordDetailModal('Samantha Miller', 'BioGenix Labs', '$120,000', 'Contacted')">Inspect</button>
                            <button type="button" class="btn btn-primary btn-sm" onclick="showToast('Lead Converted! Account and Contact created.')">Convert</button>
                        </td>
                    </tr>

                    <tr data-stage="New">
                        <td class="col-index">
                            <div style="display: flex; align-items: center; justify-content: center; gap: 5px;">
                                <span style="font-size: 0.725rem; color: var(--zinc-400);">3</span>
                                <input type="checkbox" style="cursor: pointer;">
                            </div>
                        </td>
                        <td>
                            <strong style="color: var(--zinc-950); cursor: pointer;" onclick="openRecordDetailModal('Derek Stone', 'SolarEnergix Grid', '$50,000', 'New')">
                                Derek Stone
                            </strong>
                        </td>
                        <td>SolarEnergix Grid</td>
                        <td>Director of Automation</td>
                        <td>
                            <span class="badge badge-gray" style="font-weight: 800; font-size: 0.75rem;">
                                45 / 100 Tier-C
                            </span>
                        </td>
                        <td><span class="badge badge-purple">New</span></td>
                        <td><a href="mailto:dstone@solarenergix.com" style="color: var(--color-primary); text-decoration: none;">dstone@solarenergix.com</a></td>
                        <td><span style="font-family: monospace; font-size: 0.775rem;">+1 (415) 555-7788</span></td>
                        <td>Cold Outreach</td>
                        <td>
                            <span style="display: inline-flex; align-items: center; gap: 5px;">
                                <span style="width: 18px; height: 18px; border-radius: 50%; background: #e0e7ff; color: #4338ca; font-size: 0.625rem; font-weight: 700; display: inline-flex; align-items: center; justify-content: center;">M</span>
                                <span>Marcus Brody</span>
                            </span>
                        </td>
                        <td class="sticky-actions-col" style="text-align: right;">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="openRecordDetailModal('Derek Stone', 'SolarEnergix Grid', '$50,000', 'New')">Inspect</button>
                            <button type="button" class="btn btn-primary btn-sm" onclick="showToast('Lead Converted! Account and Contact created.')">Convert</button>
                        </td>
                    </tr>

                    <tr data-stage="Qualified">
                        <td class="col-index">
                            <div style="display: flex; align-items: center; justify-content: center; gap: 5px;">
                                <span style="font-size: 0.725rem; color: var(--zinc-400);">4</span>
                                <input type="checkbox" style="cursor: pointer;">
                            </div>
                        </td>
                        <td>
                            <strong style="color: var(--zinc-950); cursor: pointer;" onclick="openRecordDetailModal('Elena Rostova', 'Quantum Global Solutions', '$175,000', 'Qualified')">
                                Elena Rostova
                            </strong>
                        </td>
                        <td>Quantum Global Solutions</td>
                        <td>Chief Procurement Officer</td>
                        <td>
                            <span class="badge badge-green" style="font-weight: 800; font-size: 0.75rem;">
                                94 / 100 Tier-A
                            </span>
                        </td>
                        <td><span class="badge badge-green">Qualified</span></td>
                        <td><a href="mailto:elena.r@quantumglobal.io" style="color: var(--color-primary); text-decoration: none;">elena.r@quantumglobal.io</a></td>
                        <td><span style="font-family: monospace; font-size: 0.775rem;">+44 20 7946 0912</span></td>
                        <td>Partner Referral</td>
                        <td>
                            <span style="display: inline-flex; align-items: center; gap: 5px;">
                                <span style="width: 18px; height: 18px; border-radius: 50%; background: #e0f2fe; color: #0369a1; font-size: 0.625rem; font-weight: 700; display: inline-flex; align-items: center; justify-content: center;">A</span>
                                <span>Alex Morgan</span>
                            </span>
                        </td>
                        <td class="sticky-actions-col" style="text-align: right;">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="openRecordDetailModal('Elena Rostova', 'Quantum Global Solutions', '$175,000', 'Qualified')">Inspect</button>
                            <button type="button" class="btn btn-primary btn-sm" onclick="showToast('Lead Converted! Account and Contact created.')">Convert</button>
                        </td>
                    </tr>

                    <tr data-stage="Contacted">
                        <td class="col-index">
                            <div style="display: flex; align-items: center; justify-content: center; gap: 5px;">
                                <span style="font-size: 0.725rem; color: var(--zinc-400);">5</span>
                                <input type="checkbox" style="cursor: pointer;">
                            </div>
                        </td>
                        <td>
                            <strong style="color: var(--zinc-950); cursor: pointer;" onclick="openRecordDetailModal('David Chen', 'Nexus Robotics', '$92,000', 'Contacted')">
                                David Chen
                            </strong>
                        </td>
                        <td>Nexus Robotics</td>
                        <td>Engineering Director</td>
                        <td>
                            <span class="badge badge-amber" style="font-weight: 800; font-size: 0.75rem;">
                                63 / 100 Tier-B
                            </span>
                        </td>
                        <td><span class="badge badge-blue">Contacted</span></td>
                        <td><a href="mailto:dchen@nexusrobotics.ai" style="color: var(--color-primary); text-decoration: none;">dchen@nexusrobotics.ai</a></td>
                        <td><span style="font-family: monospace; font-size: 0.775rem;">+1 (512) 555-0199</span></td>
                        <td>Trade Show / Event</td>
                        <td>
                            <span style="display: inline-flex; align-items: center; gap: 5px;">
                                <span style="width: 18px; height: 18px; border-radius: 50%; background: #fef3c7; color: #b45309; font-size: 0.625rem; font-weight: 700; display: inline-flex; align-items: center; justify-content: center;">J</span>
                                <span>Jordan Lee</span>
                            </span>
                        </td>
                        <td class="sticky-actions-col" style="text-align: right;">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="openRecordDetailModal('David Chen', 'Nexus Robotics', '$92,000', 'Contacted')">Inspect</button>
                            <button type="button" class="btn btn-primary btn-sm" onclick="showToast('Lead Converted! Account and Contact created.')">Convert</button>
                        </td>
                    </tr>

                    <tr data-stage="Lost">
                        <td class="col-index">
                            <div style="display: flex; align-items: center; justify-content: center; gap: 5px;">
                                <span style="font-size: 0.725rem; color: var(--zinc-400);">6</span>
                                <input type="checkbox" style="cursor: pointer;">
                            </div>
                        </td>
                        <td>
                            <strong style="color: var(--zinc-950); cursor: pointer;" onclick="openRecordDetailModal('Carlos Mendez', 'Atlas Logistics Inc', '$35,000', 'Lost')">
                                Carlos Mendez
                            </strong>
                        </td>
                        <td>Atlas Logistics Inc</td>
                        <td>Head of Facilities</td>
                        <td>
                            <span class="badge badge-red" style="font-weight: 800; font-size: 0.75rem;">
                                31 / 100 Tier-D
                            </span>
                        </td>
                        <td><span class="badge badge-red">Lost</span></td>
                        <td><a href="mailto:cmendez@atlaslogistics.net" style="color: var(--color-primary); text-decoration: none;">cmendez@atlaslogistics.net</a></td>
                        <td><span style="font-family: monospace; font-size: 0.775rem;">+1 (305) 555-8821</span></td>
                        <td>Google Ads</td>
                        <td>
                            <span style="display: inline-flex; align-items: center; gap: 5px;">
                                <span style="width: 18px; height: 18px; border-radius: 50%; background: #e0e7ff; color: #4338ca; font-size: 0.625rem; font-weight: 700; display: inline-flex; align-items: center; justify-content: center;">M</span>
                                <span>Marcus Brody</span>
                            </span>
                        </td>
                        <td class="sticky-actions-col" style="text-align: right;">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="openRecordDetailModal('Carlos Mendez', 'Atlas Logistics Inc', '$35,000', 'Lost')">Inspect</button>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="showToast('Lead status reactivated to New.')">Reopen</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Table Footer -->
        <div style="padding: 0.5rem 1rem; background: var(--zinc-50); border-top: 1px solid var(--border-card); display: flex; justify-content: space-between; align-items: center; font-size: 0.725rem; color: var(--zinc-500);">
            <div>Showing <strong>6</strong> records (Propensity Tier Score &amp; Conversion Enabled)</div>
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
    <div id="leads-kanban-view" style="display: none; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-top: 0.5rem;">
        <!-- Column 1: New -->
        <div style="background: var(--zinc-50); border: 1px solid var(--border-card); border-radius: 12px; padding: 0.85rem;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
                <div style="display: flex; align-items: center; gap: 0.45rem;">
                    <span style="width: 8px; height: 8px; border-radius: 50%; background: #a855f7;"></span>
                    <strong style="font-size: 0.8125rem; color: var(--zinc-900);">New</strong>
                </div>
                <span class="badge badge-purple" style="font-size: 0.65rem;">1</span>
            </div>
            <div style="display: flex; flex-direction: column; gap: 0.6rem;">
                <div class="card" onclick="openRecordDetailModal('Derek Stone', 'SolarEnergix Grid', '$50,000', 'New')" style="padding: 0.75rem; cursor: pointer; border: 1px solid var(--border-card);">
                    <strong style="font-size: 0.8125rem; color: var(--zinc-950); display: block;">Derek Stone</strong>
                    <span style="font-size: 0.725rem; color: var(--zinc-500); display: block;">SolarEnergix Grid</span>
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 0.5rem;">
                        <span class="badge badge-gray" style="font-size: 0.65rem;">Score: 45</span>
                        <span style="font-size: 0.68rem; color: var(--zinc-400);">Marcus Brody</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Column 2: Contacted -->
        <div style="background: var(--zinc-50); border: 1px solid var(--border-card); border-radius: 12px; padding: 0.85rem;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
                <div style="display: flex; align-items: center; gap: 0.45rem;">
                    <span style="width: 8px; height: 8px; border-radius: 50%; background: #3b82f6;"></span>
                    <strong style="font-size: 0.8125rem; color: var(--zinc-900);">Contacted</strong>
                </div>
                <span class="badge badge-blue" style="font-size: 0.65rem;">2</span>
            </div>
            <div style="display: flex; flex-direction: column; gap: 0.6rem;">
                <div class="card" onclick="openRecordDetailModal('Samantha Miller', 'BioGenix Labs', '$120,000', 'Contacted')" style="padding: 0.75rem; cursor: pointer; border: 1px solid var(--border-card);">
                    <strong style="font-size: 0.8125rem; color: var(--zinc-950); display: block;">Samantha Miller</strong>
                    <span style="font-size: 0.725rem; color: var(--zinc-500); display: block;">BioGenix Labs</span>
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 0.5rem;">
                        <span class="badge badge-amber" style="font-size: 0.65rem;">Score: 72</span>
                        <span style="font-size: 0.68rem; color: var(--zinc-400);">Jordan Lee</span>
                    </div>
                </div>

                <div class="card" onclick="openRecordDetailModal('David Chen', 'Nexus Robotics', '$92,000', 'Contacted')" style="padding: 0.75rem; cursor: pointer; border: 1px solid var(--border-card);">
                    <strong style="font-size: 0.8125rem; color: var(--zinc-950); display: block;">David Chen</strong>
                    <span style="font-size: 0.725rem; color: var(--zinc-500); display: block;">Nexus Robotics</span>
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 0.5rem;">
                        <span class="badge badge-amber" style="font-size: 0.65rem;">Score: 63</span>
                        <span style="font-size: 0.68rem; color: var(--zinc-400);">Jordan Lee</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Column 3: Qualified -->
        <div style="background: var(--zinc-50); border: 1px solid var(--border-card); border-radius: 12px; padding: 0.85rem;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
                <div style="display: flex; align-items: center; gap: 0.45rem;">
                    <span style="width: 8px; height: 8px; border-radius: 50%; background: #10b981;"></span>
                    <strong style="font-size: 0.8125rem; color: var(--zinc-900);">Qualified</strong>
                </div>
                <span class="badge badge-green" style="font-size: 0.65rem;">2</span>
            </div>
            <div style="display: flex; flex-direction: column; gap: 0.6rem;">
                <div class="card" onclick="openRecordDetailModal('Alexander Wright', 'Aurora Financial Tech', '$85,000', 'Qualified')" style="padding: 0.75rem; cursor: pointer; border: 1px solid var(--border-card);">
                    <strong style="font-size: 0.8125rem; color: var(--zinc-950); display: block;">Alexander Wright</strong>
                    <span style="font-size: 0.725rem; color: var(--zinc-500); display: block;">Aurora Financial Tech</span>
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 0.5rem;">
                        <span class="badge badge-green" style="font-size: 0.65rem;">Score: 88</span>
                        <span style="font-size: 0.68rem; color: var(--zinc-400);">Alex Morgan</span>
                    </div>
                </div>

                <div class="card" onclick="openRecordDetailModal('Elena Rostova', 'Quantum Global Solutions', '$175,000', 'Qualified')" style="padding: 0.75rem; cursor: pointer; border: 1px solid var(--border-card);">
                    <strong style="font-size: 0.8125rem; color: var(--zinc-950); display: block;">Elena Rostova</strong>
                    <span style="font-size: 0.725rem; color: var(--zinc-500); display: block;">Quantum Global Solutions</span>
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 0.5rem;">
                        <span class="badge badge-green" style="font-size: 0.65rem;">Score: 94</span>
                        <span style="font-size: 0.68rem; color: var(--zinc-400);">Alex Morgan</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Column 4: Lost -->
        <div style="background: var(--zinc-50); border: 1px solid var(--border-card); border-radius: 12px; padding: 0.85rem;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
                <div style="display: flex; align-items: center; gap: 0.45rem;">
                    <span style="width: 8px; height: 8px; border-radius: 50%; background: #ef4444;"></span>
                    <strong style="font-size: 0.8125rem; color: var(--zinc-900);">Lost</strong>
                </div>
                <span class="badge badge-red" style="font-size: 0.65rem;">1</span>
            </div>
            <div style="display: flex; flex-direction: column; gap: 0.6rem;">
                <div class="card" onclick="openRecordDetailModal('Carlos Mendez', 'Atlas Logistics Inc', '$35,000', 'Lost')" style="padding: 0.75rem; cursor: pointer; border: 1px solid var(--border-card);">
                    <strong style="font-size: 0.8125rem; color: var(--zinc-950); display: block;">Carlos Mendez</strong>
                    <span style="font-size: 0.725rem; color: var(--zinc-500); display: block;">Atlas Logistics Inc</span>
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 0.5rem;">
                        <span class="badge badge-red" style="font-size: 0.65rem;">Score: 31</span>
                        <span style="font-size: 0.68rem; color: var(--zinc-400);">Marcus Brody</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Lead View Switcher (Table vs Kanban)
    function switchLeadView(view) {
        const tableView = document.getElementById('leads-table-view');
        const kanbanView = document.getElementById('leads-kanban-view');
        const btnTable = document.getElementById('btn-view-table');
        const btnKanban = document.getElementById('btn-view-kanban');

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

    // Quick Stage Filter
    function filterLeadsStage(stage) {
        const rows = document.querySelectorAll('#leads-table tbody tr');
        rows.forEach(r => {
            if (stage === 'all' || r.getAttribute('data-stage') === stage) {
                r.style.display = '';
            } else {
                r.style.display = 'none';
            }
        });
        document.querySelectorAll('.stage-filter-btn').forEach(btn => {
            btn.style.background = '';
            btn.style.color = '';
            btn.classList.add('btn-secondary');
        });
        event.currentTarget.classList.remove('btn-secondary');
        event.currentTarget.style.background = 'var(--zinc-900)';
        event.currentTarget.style.color = '#ffffff';
    }

    // Open detail modal with record info
    function openRecordDetailModal(name, company, amount, stage) {
        const modal = document.getElementById('record-detail-modal');
        if (modal) {
            const title = document.getElementById('modal-detail-title');
            if (title) title.innerText = name + ' — ' + company;
            modal.classList.add('is-open');
        }
    }
</script>
@endsection
