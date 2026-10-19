@extends('layouts.app')

@section('title', 'Contacts & Stakeholders — DealSync Enterprise CRM')

@section('content')
<div class="contacts-page-container animate-fade-in">
    <!-- Top Action Header -->
    <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                <span class="badge badge-green" style="font-size: 0.7rem; font-weight: 700;">Stakeholder Graph</span>
                <span style="font-size: 0.725rem; color: var(--zinc-500);">Direct Email &amp; Telephone Reachability</span>
            </div>
            <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--zinc-950); letter-spacing: -0.03em; margin: 0; line-height: 1.2;">
                Contacts &amp; Stakeholders
            </h1>
            <p style="font-size: 0.8125rem; color: var(--zinc-500); margin: 0.25rem 0 0 0;">
                Individual stakeholders across customer accounts with multi-contact relationship mapping and primary contact flags.
            </p>
        </div>

        <div style="display: flex; align-items: center; gap: 0.6rem;">
            <!-- Filter Dropdown Trigger -->
            <button type="button" class="btn btn-secondary btn-sm" onclick="showToast('Filtered: Showing all active contacts')">
                <i data-lucide="filter" style="width: 14px; height: 14px;"></i>
                <span>Filter</span>
            </button>

            <!-- Export -->
            <button type="button" class="btn btn-secondary btn-sm" onclick="showToast('Exporting contacts to CSV...')">
                <i data-lucide="download" style="width: 14px; height: 14px;"></i>
                <span>Export CSV</span>
            </button>

            <!-- Create Contact Button -->
            <button type="button" class="btn btn-primary btn-sm" onclick="openNewRecordModal('contacts')">
                <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
                <span>+ Add Contact</span>
            </button>
        </div>
    </div>

    <!-- Top KPI Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
        <div class="card" style="padding: 1rem;">
            <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500); margin-bottom: 0.25rem;">TOTAL STAKEHOLDERS</div>
            <div style="font-size: 1.625rem; font-weight: 800; color: var(--zinc-950); letter-spacing: -0.03em;">4 Contacts</div>
            <span style="font-size: 0.7rem; color: #059669; font-weight: 600;">Across 3 Client Accounts</span>
        </div>

        <div class="card" style="padding: 1rem;">
            <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500); margin-bottom: 0.25rem;">DECISION MAKERS</div>
            <div style="font-size: 1.625rem; font-weight: 800; color: var(--color-primary); letter-spacing: -0.03em;">2 C-Level / VPs</div>
            <span style="font-size: 0.7rem; color: var(--zinc-500); font-weight: 500;">Direct Buying Authority</span>
        </div>

        <div class="card" style="padding: 1rem;">
            <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500); margin-bottom: 0.25rem;">PRIMARY CHAMPIONS</div>
            <div style="font-size: 1.625rem; font-weight: 800; color: #d97706; letter-spacing: -0.03em;">3 Starred</div>
            <span style="font-size: 0.7rem; color: var(--zinc-500); font-weight: 500;">Default Account Liaisons</span>
        </div>

        <div class="card" style="padding: 1rem;">
            <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500); margin-bottom: 0.25rem;">EMAIL OPT-IN RATE</div>
            <div style="font-size: 1.625rem; font-weight: 800; color: #059669; letter-spacing: -0.03em;">100%</div>
            <span style="font-size: 0.7rem; color: var(--zinc-500); font-weight: 500;">0 Opt-Out Suppressions</span>
        </div>
    </div>

    <!-- Quick Role Filter Chips -->
    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem; overflow-x: auto; padding-bottom: 0.25rem;">
        <span style="font-size: 0.75rem; font-weight: 600; color: var(--zinc-500); margin-right: 0.25rem;">Buying Roles:</span>
        <button type="button" class="btn btn-sm contact-role-btn" onclick="filterContactRole('all')" style="background: var(--zinc-900); color: #ffffff; border-radius: 9999px; font-size: 0.725rem; padding: 0.15rem 0.75rem;">
            All Contacts (4)
        </button>
        <button type="button" class="btn btn-secondary btn-sm contact-role-btn" onclick="filterContactRole('Decision Maker')" style="border-radius: 9999px; font-size: 0.725rem; padding: 0.15rem 0.75rem;">
            Decision Makers (2)
        </button>
        <button type="button" class="btn btn-secondary btn-sm contact-role-btn" onclick="filterContactRole('Purchasing')" style="border-radius: 9999px; font-size: 0.725rem; padding: 0.15rem 0.75rem;">
            Purchasing / Procurement (1)
        </button>
        <button type="button" class="btn btn-secondary btn-sm contact-role-btn" onclick="filterContactRole('Vendor Rep')" style="border-radius: 9999px; font-size: 0.725rem; padding: 0.15rem 0.75rem;">
            Vendor Key Rep (1)
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
                    id="contacts-search-input"
                    placeholder="Search contacts by name, email, account..."
                    oninput="filterTableRows('contacts-table', this.value)"
                    style="width: 100%; padding: 0.4rem 0.75rem 0.4rem 2.25rem; font-size: 0.8rem; height: 32px; border-radius: 8px; border: 1px solid var(--zinc-300); background: var(--bg-card); color: var(--text-primary);"
                >
            </div>
            <span style="font-size: 0.75rem; color: var(--zinc-500); white-space: nowrap;">
                Showing <strong>4</strong> contacts
            </span>
        </div>

        <!-- Right: Density & Reset Controls -->
        <div style="display: flex; align-items: center; gap: 0.6rem;">
            <!-- Density Toggle -->
            <div style="display: flex; gap: 2px; background: var(--zinc-100); padding: 2px; border-radius: 6px; border: 1px solid var(--zinc-200);">
                <button type="button" class="btn btn-secondary btn-sm" onclick="setTableDensity('contacts-table', 'compact')" title="Compact density (30px rows)" style="padding: 0.25rem 0.5rem; font-size: 0.7rem;">
                    Compact
                </button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="setTableDensity('contacts-table', 'comfortable')" title="Comfortable density (44px rows)" style="padding: 0.25rem 0.5rem; font-size: 0.7rem;">
                    Comfortable
                </button>
            </div>

            <!-- Reset Column Widths -->
            <button type="button" class="btn btn-secondary btn-sm" onclick="resetTableColWidths('contacts-table')" title="Reset column widths" style="padding: 0.25rem 0.55rem; font-size: 0.7rem;">
                <i data-lucide="rotate-ccw" style="width: 12px; height: 12px;"></i>
                <span>Reset Widths</span>
            </button>
        </div>
    </div>

    <!-- Contacts Data Table -->
    <div class="card-table-wrapper">
        <div class="table-scroll-container">
            <table id="contacts-table" class="data-table">
                <thead>
                    <tr>
                        <!-- Master Checkbox -->
                        <th class="col-index" style="width: 58px; min-width: 58px; text-align: center;">
                            <input type="checkbox" title="Select all contacts" style="cursor: pointer; width: 14px; height: 14px;">
                        </th>
                        <th data-col="name" style="width: 200px; min-width: 150px;">Contact Name</th>
                        <th data-col="account" style="width: 200px; min-width: 150px;">Linked Account</th>
                        <th data-col="title" style="width: 180px; min-width: 140px;">Designation / Title</th>
                        <th data-col="dept" style="width: 130px; min-width: 100px;">Department</th>
                        <th data-col="role" style="width: 150px; min-width: 120px;">Buying Role</th>
                        <th data-col="primary" style="width: 110px; min-width: 90px; text-align: center;">Primary</th>
                        <th data-col="email" style="width: 210px; min-width: 160px;">Direct Email</th>
                        <th data-col="phone" style="width: 150px; min-width: 120px;">Direct Phone</th>
                        <th class="no-sort sticky-actions-col" style="width: 140px; min-width: 130px; text-align: right;">Reach Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr data-role="Decision Maker">
                        <td class="col-index">
                            <div style="display: flex; align-items: center; justify-content: center; gap: 5px;">
                                <span style="font-size: 0.725rem; color: var(--zinc-400);">1</span>
                                <input type="checkbox" style="cursor: pointer;">
                            </div>
                        </td>
                        <td>
                            <strong style="color: var(--zinc-950); cursor: pointer;" onclick="openRecordDetailModal('Sarah Jenkins', 'Skyline Cloud Systems', 'VP Engineering', 'Decision Maker')">
                                Sarah Jenkins
                            </strong>
                        </td>
                        <td>Skyline Cloud Systems</td>
                        <td>VP of Engineering</td>
                        <td>Engineering</td>
                        <td><span class="badge badge-green">Decision Maker</span></td>
                        <td style="text-align: center;"><span style="color: #f59e0b; font-weight: 700;">⭐ Primary</span></td>
                        <td><a href="mailto:sjenkins@skylinecloud.io" style="color: var(--color-primary); text-decoration: none;">sjenkins@skylinecloud.io</a></td>
                        <td><span style="font-family: monospace; font-size: 0.775rem;">+1 (415) 890-1201</span></td>
                        <td class="sticky-actions-col" style="text-align: right;">
                            <a href="mailto:sjenkins@skylinecloud.io" class="btn btn-secondary btn-sm">Email</a>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="openRecordDetailModal('Sarah Jenkins', 'Skyline Cloud Systems', 'VP Engineering', 'Decision Maker')">Inspect</button>
                        </td>
                    </tr>

                    <tr data-role="Purchasing">
                        <td class="col-index">
                            <div style="display: flex; align-items: center; justify-content: center; gap: 5px;">
                                <span style="font-size: 0.725rem; color: var(--zinc-400);">2</span>
                                <input type="checkbox" style="cursor: pointer;">
                            </div>
                        </td>
                        <td>
                            <strong style="color: var(--zinc-950); cursor: pointer;" onclick="openRecordDetailModal('Michael Chang', 'Skyline Cloud Systems', 'Head Procurement', 'Purchasing')">
                                Michael Chang
                            </strong>
                        </td>
                        <td>Skyline Cloud Systems</td>
                        <td>Head of Procurement</td>
                        <td>Finance</td>
                        <td><span class="badge badge-blue">Purchasing</span></td>
                        <td style="text-align: center;"><span style="color: var(--zinc-400);">—</span></td>
                        <td><a href="mailto:mchang@skylinecloud.io" style="color: var(--color-primary); text-decoration: none;">mchang@skylinecloud.io</a></td>
                        <td><span style="font-family: monospace; font-size: 0.775rem;">+1 (415) 890-1202</span></td>
                        <td class="sticky-actions-col" style="text-align: right;">
                            <a href="mailto:mchang@skylinecloud.io" class="btn btn-secondary btn-sm">Email</a>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="openRecordDetailModal('Michael Chang', 'Skyline Cloud Systems', 'Head Procurement', 'Purchasing')">Inspect</button>
                        </td>
                    </tr>

                    <tr data-role="Decision Maker">
                        <td class="col-index">
                            <div style="display: flex; align-items: center; justify-content: center; gap: 5px;">
                                <span style="font-size: 0.725rem; color: var(--zinc-400);">3</span>
                                <input type="checkbox" style="cursor: pointer;">
                            </div>
                        </td>
                        <td>
                            <strong style="color: var(--zinc-950); cursor: pointer;" onclick="openRecordDetailModal('Elena Rostova', 'Vanguard Health Tech', 'CTO', 'Decision Maker')">
                                Elena Rostova
                            </strong>
                        </td>
                        <td>Vanguard Health Tech</td>
                        <td>Chief Technology Officer</td>
                        <td>C-Suite</td>
                        <td><span class="badge badge-green">Decision Maker</span></td>
                        <td style="text-align: center;"><span style="color: #f59e0b; font-weight: 700;">⭐ Primary</span></td>
                        <td><a href="mailto:erostova@vanguardhealth.com" style="color: var(--color-primary); text-decoration: none;">erostova@vanguardhealth.com</a></td>
                        <td><span style="font-family: monospace; font-size: 0.775rem;">+1 (617) 555-0198</span></td>
                        <td class="sticky-actions-col" style="text-align: right;">
                            <a href="mailto:erostova@vanguardhealth.com" class="btn btn-secondary btn-sm">Email</a>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="openRecordDetailModal('Elena Rostova', 'Vanguard Health Tech', 'CTO', 'Decision Maker')">Inspect</button>
                        </td>
                    </tr>

                    <tr data-role="Vendor Rep">
                        <td class="col-index">
                            <div style="display: flex; align-items: center; justify-content: center; gap: 5px;">
                                <span style="font-size: 0.725rem; color: var(--zinc-400);">4</span>
                                <input type="checkbox" style="cursor: pointer;">
                            </div>
                        </td>
                        <td>
                            <strong style="color: var(--zinc-950); cursor: pointer;" onclick="openRecordDetailModal('David Kowalski', 'Quantum Silicon Components', 'Director Key Accounts', 'Vendor Rep')">
                                David Kowalski
                            </strong>
                        </td>
                        <td>Quantum Silicon Components</td>
                        <td>Director of Key Accounts</td>
                        <td>Sales</td>
                        <td><span class="badge badge-indigo">Vendor Rep</span></td>
                        <td style="text-align: center;"><span style="color: #f59e0b; font-weight: 700;">⭐ Primary</span></td>
                        <td><a href="mailto:dkowalski@quantumsilicon.com" style="color: var(--color-primary); text-decoration: none;">dkowalski@quantumsilicon.com</a></td>
                        <td><span style="font-family: monospace; font-size: 0.775rem;">+1 (408) 555-4322</span></td>
                        <td class="sticky-actions-col" style="text-align: right;">
                            <a href="mailto:dkowalski@quantumsilicon.com" class="btn btn-secondary btn-sm">Email</a>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="openRecordDetailModal('David Kowalski', 'Quantum Silicon Components', 'Director Key Accounts', 'Vendor Rep')">Inspect</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Table Footer -->
        <div style="padding: 0.5rem 1rem; background: var(--zinc-50); border-top: 1px solid var(--border-card); display: flex; justify-content: space-between; align-items: center; font-size: 0.725rem; color: var(--zinc-500);">
            <div>Showing <strong>4</strong> contacts (Multi-Stakeholder Map Linked)</div>
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
    function filterContactRole(role) {
        const rows = document.querySelectorAll('#contacts-table tbody tr');
        rows.forEach(r => {
            if (role === 'all' || r.getAttribute('data-role') === role) {
                r.style.display = '';
            } else {
                r.style.display = 'none';
            }
        });
        document.querySelectorAll('.contact-role-btn').forEach(btn => {
            btn.style.background = '';
            btn.style.color = '';
            btn.classList.add('btn-secondary');
        });
        event.currentTarget.classList.remove('btn-secondary');
        event.currentTarget.style.background = 'var(--zinc-900)';
        event.currentTarget.style.color = '#ffffff';
    }

    function openRecordDetailModal(name, company, title, role) {
        const modal = document.getElementById('record-detail-modal');
        if (modal) {
            const titleElem = document.getElementById('modal-detail-title');
            if (titleElem) titleElem.innerText = name + ' (' + title + ' @ ' + company + ')';
            modal.classList.add('is-open');
        }
    }
</script>
@endsection
