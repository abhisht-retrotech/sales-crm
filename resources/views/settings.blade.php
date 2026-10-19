@extends('layouts.app')

@section('title', 'Enterprise Settings & Governance — DealSync CRM')

@section('content')
<div class="settings-page-container animate-fade-in">
    <!-- Top Action Header -->
    <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                <span class="badge badge-indigo" style="font-size: 0.7rem; font-weight: 700;">Admin Governance Console</span>
                <span style="font-size: 0.725rem; color: var(--zinc-500);">RBAC, Profiles, Field-Level Security, Ambient Themes &amp; Audit Logs</span>
            </div>
            <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--zinc-950); letter-spacing: -0.03em; margin: 0; line-height: 1.2;">
                Enterprise Settings &amp; Schema Studio
            </h1>
            <p style="font-size: 0.8125rem; color: var(--zinc-500); margin: 0.25rem 0 0 0;">
                Manage tenant user privileges, role reporting trees, Profile CRUD rights, custom fields, ambient design systems, and delegated administrator impersonation.
            </p>
        </div>

        <div style="display: flex; align-items: center; gap: 0.6rem;">
            <!-- Export Audit -->
            <button type="button" class="btn btn-secondary btn-sm" onclick="showToast('Exporting security audit trail to CSV...')">
                <i data-lucide="shield-check" style="width: 14px; height: 14px;"></i>
                <span>Export Audit Log</span>
            </button>

            <!-- Provision User -->
            <button type="button" class="btn btn-primary btn-sm" onclick="openNewRecordModal('users')">
                <i data-lucide="user-plus" style="width: 14px; height: 14px;"></i>
                <span>+ Provision User</span>
            </button>
        </div>
    </div>

    <!-- Top KPI Quota & Governance Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
        <div class="card" style="padding: 1rem;">
            <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500); margin-bottom: 0.25rem;">SEAT LICENSE QUOTA</div>
            <div style="font-size: 1.625rem; font-weight: 800; color: var(--color-primary); letter-spacing: -0.03em;">4 / 10 Seats</div>
            <div style="width: 100%; height: 6px; background: var(--zinc-200); border-radius: 999px; margin-top: 0.4rem; overflow: hidden;">
                <div style="width: 40%; height: 100%; background: var(--color-primary); border-radius: 999px;"></div>
            </div>
            <span style="font-size: 0.7rem; color: var(--zinc-500); font-weight: 500; margin-top: 0.25rem; display: block;">6 Enterprise Seats Available</span>
        </div>

        <div class="card" style="padding: 1rem;">
            <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500); margin-bottom: 0.25rem;">SECURITY TIER</div>
            <div style="font-size: 1.625rem; font-weight: 800; color: #059669; letter-spacing: -0.03em;">SOC2 Type II</div>
            <span style="font-size: 0.7rem; color: #059669; font-weight: 600;">Enforced RLS &amp; IP Whitelist</span>
        </div>

        <div class="card" style="padding: 1rem;">
            <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500); margin-bottom: 0.25rem;">MULTI-TENANT REALM</div>
            <div style="font-size: 1.625rem; font-weight: 800; color: var(--zinc-950); letter-spacing: -0.03em;">Acme Corp</div>
            <span style="font-size: 0.7rem; color: var(--zinc-500); font-weight: 500;">Realm ID: tenant-acme-corp</span>
        </div>

        <div class="card" style="padding: 1rem;">
            <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500); margin-bottom: 0.25rem;">COMPLIANCE AUDIT</div>
            <div style="font-size: 1.625rem; font-weight: 800; color: #d97706; letter-spacing: -0.03em;">100% Pass</div>
            <span style="font-size: 0.7rem; color: var(--zinc-500); font-weight: 500;">Zero Vulnerability Flags</span>
        </div>
    </div>

    <!-- Admin Subtabs Navigation -->
    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.25rem; border-bottom: 1px solid var(--zinc-200); padding-bottom: 0.5rem; overflow-x: auto;">
        <button type="button" class="btn btn-sm settings-tab-btn active-tab" onclick="switchSettingsTab('users', this)" style="background: var(--zinc-900); color: #ffffff; border-radius: 9999px; font-size: 0.75rem; padding: 0.35rem 0.9rem;">
            <i data-lucide="users" style="width: 14px; height: 14px; display: inline-block; vertical-align: middle; margin-right: 4px;"></i>
            Users Directory (4)
        </button>
        <button type="button" class="btn btn-secondary btn-sm settings-tab-btn" onclick="switchSettingsTab('roles', this)" style="border-radius: 9999px; font-size: 0.75rem; padding: 0.35rem 0.9rem;">
            <i data-lucide="git-pull-request" style="width: 14px; height: 14px; display: inline-block; vertical-align: middle; margin-right: 4px;"></i>
            Roles &amp; Hierarchy (4)
        </button>
        <button type="button" class="btn btn-secondary btn-sm settings-tab-btn" onclick="switchSettingsTab('crud', this)" style="border-radius: 9999px; font-size: 0.75rem; padding: 0.35rem 0.9rem;">
            <i data-lucide="shield" style="width: 14px; height: 14px; display: inline-block; vertical-align: middle; margin-right: 4px;"></i>
            Record Rights (CRUD)
        </button>
        <button type="button" class="btn btn-secondary btn-sm settings-tab-btn" onclick="switchSettingsTab('fls', this)" style="border-radius: 9999px; font-size: 0.75rem; padding: 0.35rem 0.9rem;">
            <i data-lucide="lock" style="width: 14px; height: 14px; display: inline-block; vertical-align: middle; margin-right: 4px;"></i>
            Field-Level Rights (FLS)
        </button>
        <button type="button" class="btn btn-secondary btn-sm settings-tab-btn" onclick="switchSettingsTab('fields', this)" style="border-radius: 9999px; font-size: 0.75rem; padding: 0.35rem 0.9rem;">
            <i data-lucide="sliders" style="width: 14px; height: 14px; display: inline-block; vertical-align: middle; margin-right: 4px;"></i>
            Custom Schema Fields
        </button>
        <button type="button" class="btn btn-secondary btn-sm settings-tab-btn" onclick="switchSettingsTab('branding', this)" style="border-radius: 9999px; font-size: 0.75rem; padding: 0.35rem 0.9rem;">
            <i data-lucide="palette" style="width: 14px; height: 14px; display: inline-block; vertical-align: middle; margin-right: 4px;"></i>
            Theme &amp; Background Studio
        </button>
        <button type="button" class="btn btn-secondary btn-sm settings-tab-btn" onclick="switchSettingsTab('audit', this)" style="border-radius: 9999px; font-size: 0.75rem; padding: 0.35rem 0.9rem;">
            <i data-lucide="file-text" style="width: 14px; height: 14px; display: inline-block; vertical-align: middle; margin-right: 4px;"></i>
            Audit Logs Trail
        </button>
    </div>

    <!-- ==================== TAB 1: USERS DIRECTORY ==================== -->
    <div id="tab-pane-users" class="settings-tab-pane">
        <!-- Table Toolbar: Search, Density, Reset Widths -->
        <div class="card" style="margin-bottom: 0.75rem; padding: 0.65rem 1rem; display: flex; gap: 0.75rem; align-items: center; justify-content: space-between; flex-wrap: wrap;">
            <!-- Left: Search Box -->
            <div style="display: flex; align-items: center; gap: 0.75rem; flex: 1; min-width: 280px;">
                <div style="position: relative; flex: 1; max-width: 380px;">
                    <i data-lucide="search" style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); width: 15px; height: 15px; color: var(--zinc-400);"></i>
                    <input
                        type="text"
                        id="settings-users-search-input"
                        placeholder="Search active users by name, role, email..."
                        oninput="filterTableRows('settings-users-table', this.value)"
                        style="width: 100%; padding: 0.4rem 0.75rem 0.4rem 2.25rem; font-size: 0.8rem; height: 32px; border-radius: 8px; border: 1px solid var(--zinc-300); background: var(--bg-card); color: var(--text-primary);"
                    >
                </div>
                <span style="font-size: 0.75rem; color: var(--zinc-500); white-space: nowrap;">
                    Showing <strong>4</strong> provisioned users
                </span>
            </div>

            <!-- Right: Density & Reset Controls -->
            <div style="display: flex; align-items: center; gap: 0.6rem;">
                <!-- Density Toggle -->
                <div style="display: flex; gap: 2px; background: var(--zinc-100); padding: 2px; border-radius: 6px; border: 1px solid var(--zinc-200);">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="setTableDensity('settings-users-table', 'compact')" title="Compact density (30px rows)" style="padding: 0.25rem 0.5rem; font-size: 0.7rem;">
                        Compact
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="setTableDensity('settings-users-table', 'comfortable')" title="Comfortable density (44px rows)" style="padding: 0.25rem 0.5rem; font-size: 0.7rem;">
                        Comfortable
                    </button>
                </div>

                <!-- Reset Column Widths -->
                <button type="button" class="btn btn-secondary btn-sm" onclick="resetTableColWidths('settings-users-table')" title="Reset column widths" style="padding: 0.25rem 0.55rem; font-size: 0.7rem;">
                    <i data-lucide="rotate-ccw" style="width: 12px; height: 12px;"></i>
                    <span>Reset Widths</span>
                </button>
            </div>
        </div>

        <!-- Floating Bulk Action Bar -->
        <div id="settings-users-table-bulk-actions" class="card" style="display: none; margin-bottom: 0.75rem; padding: 0.65rem 1rem; background: var(--zinc-900); color: #ffffff; align-items: center; justify-content: space-between; border-radius: 8px;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <span style="font-size: 0.8rem; font-weight: 600;"><span class="bulk-count">0</span> users selected</span>
            </div>
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <button type="button" class="btn btn-sm btn-secondary" onclick="showToast('Password reset emails dispatched to selected users!')" style="font-size: 0.725rem; padding: 0.25rem 0.6rem; background: #374151; color: #fff; border: none;">
                    Reset Passwords
                </button>
                <button type="button" class="btn btn-sm btn-secondary" onclick="showToast('Exporting selected users to CSV...')" style="font-size: 0.725rem; padding: 0.25rem 0.6rem; background: #374151; color: #fff; border: none;">
                    Export Selected
                </button>
                <button type="button" class="btn btn-sm btn-secondary" onclick="bulkDeleteSelected('settings-users-table')" style="font-size: 0.725rem; padding: 0.25rem 0.6rem; background: #ef4444; color: #fff; border: none;">
                    Deactivate
                </button>
            </div>
        </div>

        <!-- Users Directory Table -->
        <div class="card-table-wrapper">
            <table class="data-table" id="settings-users-table" data-storage-key="dealsync_col_widths_settings_users">
                <thead>
                    <tr>
                        <th style="width: 40px; text-align: center;">
                            <input type="checkbox" onchange="toggleSelectAll('settings-users-table', this.checked)">
                        </th>
                        <th class="sortable" onclick="sortTable('settings-users-table', 1)" data-col="user" style="min-width: 220px;">
                            User Identity &amp; Title
                            <span class="sort-icon">▲</span>
                            <div class="col-resizer"></div>
                        </th>
                        <th class="sortable" onclick="sortTable('settings-users-table', 2)" data-col="email" style="min-width: 220px;">
                            Corporate Email
                            <span class="sort-icon">▲</span>
                            <div class="col-resizer"></div>
                        </th>
                        <th class="sortable" onclick="sortTable('settings-users-table', 3)" data-col="role" style="min-width: 170px;">
                            Organizational Role
                            <span class="sort-icon">▲</span>
                            <div class="col-resizer"></div>
                        </th>
                        <th class="sortable" onclick="sortTable('settings-users-table', 4)" data-col="profile" style="min-width: 180px;">
                            Security Profile (RBAC)
                            <span class="sort-icon">▲</span>
                            <div class="col-resizer"></div>
                        </th>
                        <th class="sortable" onclick="sortTable('settings-users-table', 5)" data-col="status" style="min-width: 120px;">
                            Status
                            <span class="sort-icon">▲</span>
                            <div class="col-resizer"></div>
                        </th>
                        <th class="sortable" onclick="sortTable('settings-users-table', 6)" data-col="ambient" style="min-width: 160px;">
                            Ambient Lighting
                            <span class="sort-icon">▲</span>
                            <div class="col-resizer"></div>
                        </th>
                        <th data-col="actions" style="text-align: right; min-width: 180px;">
                            Delegated Impersonation
                            <div class="col-resizer"></div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr data-category="Executive">
                        <td style="text-align: center;">
                            <input type="checkbox" class="row-checkbox" onchange="updateSelectedCount('settings-users-table')">
                        </td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.65rem;">
                                <div style="width: 32px; height: 32px; border-radius: 50%; background: #e0e7ff; color: #4338ca; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.8rem;">
                                    AM
                                </div>
                                <div>
                                    <div style="font-weight: 700; color: var(--zinc-900);">Alex Morgan</div>
                                    <div style="font-size: 0.725rem; color: var(--zinc-500);">Chief Executive Officer</div>
                                </div>
                            </div>
                        </td>
                        <td style="color: var(--zinc-600); font-family: monospace; font-size: 0.8rem;">alex.morgan@acmecorp.com</td>
                        <td><span style="font-weight: 600; color: var(--zinc-800);">Executive / CEO</span></td>
                        <td><span class="badge badge-indigo">prof-admin (Administrator)</span></td>
                        <td><span class="badge badge-green">Active</span></td>
                        <td><span class="badge badge-purple" style="font-size: 0.7rem;">Surprise-Me Enabled</span></td>
                        <td style="text-align: right;">
                            <span style="font-size: 0.75rem; color: #059669; font-weight: 700;">★ Current Active Session</span>
                        </td>
                    </tr>

                    <tr data-category="Sales">
                        <td style="text-align: center;">
                            <input type="checkbox" class="row-checkbox" onchange="updateSelectedCount('settings-users-table')">
                        </td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.65rem;">
                                <div style="width: 32px; height: 32px; border-radius: 50%; background: #dbeafe; color: #1d4ed8; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.8rem;">
                                    JL
                                </div>
                                <div>
                                    <div style="font-weight: 700; color: var(--zinc-900);">Jordan Lee</div>
                                    <div style="font-size: 0.725rem; color: var(--zinc-500);">Enterprise Account Exec</div>
                                </div>
                            </div>
                        </td>
                        <td style="color: var(--zinc-600); font-family: monospace; font-size: 0.8rem;">jordan.lee@acmecorp.com</td>
                        <td><span style="font-weight: 600; color: var(--zinc-800);">Sales Executive</span></td>
                        <td><span class="badge badge-blue">prof-sales (Sales Rep)</span></td>
                        <td><span class="badge badge-green">Active</span></td>
                        <td><span class="badge badge-gray" style="font-size: 0.7rem;">Inherit Tenant</span></td>
                        <td style="text-align: right;">
                            <button type="button" class="btn btn-secondary btn-sm" style="background: #cffafe; color: #0e7490; border-color: #a5f3fc; font-weight: 600;" onclick="showToast('Switched delegated impersonation context to: Jordan Lee (Sales Rep)!');">
                                <i data-lucide="log-in" style="width: 12px; height: 12px; margin-right: 3px;"></i>
                                Login as User
                            </button>
                        </td>
                    </tr>

                    <tr data-category="Procurement">
                        <td style="text-align: center;">
                            <input type="checkbox" class="row-checkbox" onchange="updateSelectedCount('settings-users-table')">
                        </td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.65rem;">
                                <div style="width: 32px; height: 32px; border-radius: 50%; background: #fef3c7; color: #b45309; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.8rem;">
                                    MB
                                </div>
                                <div>
                                    <div style="font-weight: 700; color: var(--zinc-900);">Marcus Brody</div>
                                    <div style="font-size: 0.725rem; color: var(--zinc-500);">Supply Chain Director</div>
                                </div>
                            </div>
                        </td>
                        <td style="color: var(--zinc-600); font-family: monospace; font-size: 0.8rem;">marcus.brody@acmecorp.com</td>
                        <td><span style="font-weight: 600; color: var(--zinc-800);">Procurement Lead</span></td>
                        <td><span class="badge badge-purple">prof-procurement</span></td>
                        <td><span class="badge badge-green">Active</span></td>
                        <td><span class="badge badge-gray" style="font-size: 0.7rem;">Inherit Tenant</span></td>
                        <td style="text-align: right;">
                            <button type="button" class="btn btn-secondary btn-sm" style="background: #cffafe; color: #0e7490; border-color: #a5f3fc; font-weight: 600;" onclick="showToast('Switched delegated impersonation context to: Marcus Brody (Procurement Lead)!');">
                                <i data-lucide="log-in" style="width: 12px; height: 12px; margin-right: 3px;"></i>
                                Login as User
                            </button>
                        </td>
                    </tr>

                    <tr data-category="Finance">
                        <td style="text-align: center;">
                            <input type="checkbox" class="row-checkbox" onchange="updateSelectedCount('settings-users-table')">
                        </td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.65rem;">
                                <div style="width: 32px; height: 32px; border-radius: 50%; background: #ecfdf5; color: #047857; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.8rem;">
                                    SC
                                </div>
                                <div>
                                    <div style="font-weight: 700; color: var(--zinc-900);">Sarah Chen</div>
                                    <div style="font-size: 0.725rem; color: var(--zinc-500);">Corporate Financial Controller</div>
                                </div>
                            </div>
                        </td>
                        <td style="color: var(--zinc-600); font-family: monospace; font-size: 0.8rem;">sarah.finance@acmecorp.com</td>
                        <td><span style="font-weight: 600; color: var(--zinc-800);">Finance &amp; Billing Lead</span></td>
                        <td><span class="badge badge-indigo">prof-admin</span></td>
                        <td><span class="badge badge-green">Active</span></td>
                        <td><span class="badge badge-purple" style="font-size: 0.7rem;">Surprise-Me Enabled</span></td>
                        <td style="text-align: right;">
                            <button type="button" class="btn btn-secondary btn-sm" style="background: #cffafe; color: #0e7490; border-color: #a5f3fc; font-weight: 600;" onclick="showToast('Switched delegated impersonation context to: Sarah Chen (Finance Lead)!');">
                                <i data-lucide="log-in" style="width: 12px; height: 12px; margin-right: 3px;"></i>
                                Login as User
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ==================== TAB 2: ROLES & HIERARCHY ==================== -->
    <div id="tab-pane-roles" class="settings-tab-pane" style="display: none;">
        <div class="card" style="padding: 1.25rem; margin-bottom: 1.25rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <div>
                    <h3 style="font-size: 1rem; font-weight: 700; color: var(--zinc-900); margin: 0;">Organizational Role Reporting Hierarchy</h3>
                    <p style="font-size: 0.8rem; color: var(--zinc-500); margin: 0.25rem 0 0 0;">Defines the vertical data visibility and manager approval delegation tree.</p>
                </div>
                <button type="button" class="btn btn-secondary btn-sm" onclick="showToast('+ Add Child Role modal opened')">
                    <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
                    <span>+ Add Role</span>
                </button>
            </div>

            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                <div style="padding: 0.85rem 1rem; background: var(--zinc-50); border: 1px solid var(--zinc-200); border-radius: 8px; display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <span class="badge badge-indigo" style="font-weight: 700;">LEVEL 0</span>
                        <div>
                            <strong style="color: var(--zinc-900); font-size: 0.85rem;">Executive / CEO</strong>
                            <div style="font-size: 0.725rem; color: var(--zinc-500);">Reports to: Board of Directors &bull; 1 Active User (Alex Morgan)</div>
                        </div>
                    </div>
                    <span class="badge badge-green">Top Authority</span>
                </div>

                <div style="margin-left: 2rem; padding: 0.85rem 1rem; background: var(--zinc-50); border: 1px solid var(--zinc-200); border-radius: 8px; display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <span class="badge badge-blue" style="font-weight: 700;">LEVEL 1</span>
                        <div>
                            <strong style="color: var(--zinc-900); font-size: 0.85rem;">Sales Executive</strong>
                            <div style="font-size: 0.725rem; color: var(--zinc-500);">Reports to: Executive / CEO &bull; 1 Active User (Jordan Lee)</div>
                        </div>
                    </div>
                    <span class="badge badge-blue">Pipeline Revenue</span>
                </div>

                <div style="margin-left: 2rem; padding: 0.85rem 1rem; background: var(--zinc-50); border: 1px solid var(--zinc-200); border-radius: 8px; display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <span class="badge badge-purple" style="font-weight: 700;">LEVEL 1</span>
                        <div>
                            <strong style="color: var(--zinc-900); font-size: 0.85rem;">Procurement Lead</strong>
                            <div style="font-size: 0.725rem; color: var(--zinc-500);">Reports to: Executive / CEO &bull; 1 Active User (Marcus Brody)</div>
                        </div>
                    </div>
                    <span class="badge badge-purple">Vendor Operations</span>
                </div>

                <div style="margin-left: 2rem; padding: 0.85rem 1rem; background: var(--zinc-50); border: 1px solid var(--zinc-200); border-radius: 8px; display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <span class="badge badge-green" style="font-weight: 700;">LEVEL 1</span>
                        <div>
                            <strong style="color: var(--zinc-900); font-size: 0.85rem;">Finance &amp; Billing Lead</strong>
                            <div style="font-size: 0.725rem; color: var(--zinc-500);">Reports to: Executive / CEO &bull; 1 Active User (Sarah Chen)</div>
                        </div>
                    </div>
                    <span class="badge badge-green">Fiscal Ledger</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== TAB 3: RECORD RIGHTS (CRUD MATRIX) ==================== -->
    <div id="tab-pane-crud" class="settings-tab-pane" style="display: none;">
        <div class="card" style="padding: 1.25rem; margin-bottom: 1.25rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <div>
                    <h3 style="font-size: 1rem; font-weight: 700; color: var(--zinc-900); margin: 0;">RBAC Profile Record Rights (CRUD) Matrix</h3>
                    <p style="font-size: 0.8rem; color: var(--zinc-500); margin: 0.25rem 0 0 0;">Profile level permissions governing Create, Read, Edit, and Delete access across CRM objects.</p>
                </div>
                <button type="button" class="btn btn-primary btn-sm" onclick="showToast('CRUD permissions matrix saved successfully!')">
                    <i data-lucide="save" style="width: 14px; height: 14px;"></i>
                    <span>Save Rights Matrix</span>
                </button>
            </div>

            <div class="card-table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>CRM Data Module</th>
                            <th style="text-align: center;">prof-admin (Create)</th>
                            <th style="text-align: center;">prof-admin (Read)</th>
                            <th style="text-align: center;">prof-admin (Update)</th>
                            <th style="text-align: center;">prof-admin (Delete)</th>
                            <th style="text-align: center;">prof-sales (CRUD)</th>
                            <th style="text-align: center;">prof-procurement (CRUD)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Leads</strong></td>
                            <td style="text-align: center;"><span style="color: #059669; font-weight: 700;">✓</span></td>
                            <td style="text-align: center;"><span style="color: #059669; font-weight: 700;">✓</span></td>
                            <td style="text-align: center;"><span style="color: #059669; font-weight: 700;">✓</span></td>
                            <td style="text-align: center;"><span style="color: #059669; font-weight: 700;">✓</span></td>
                            <td style="text-align: center;"><span class="badge badge-blue">C / R / U</span></td>
                            <td style="text-align: center;"><span class="badge badge-gray">Read Only</span></td>
                        </tr>
                        <tr>
                            <td><strong>Accounts</strong></td>
                            <td style="text-align: center;"><span style="color: #059669; font-weight: 700;">✓</span></td>
                            <td style="text-align: center;"><span style="color: #059669; font-weight: 700;">✓</span></td>
                            <td style="text-align: center;"><span style="color: #059669; font-weight: 700;">✓</span></td>
                            <td style="text-align: center;"><span style="color: #059669; font-weight: 700;">✓</span></td>
                            <td style="text-align: center;"><span class="badge badge-blue">C / R / U</span></td>
                            <td style="text-align: center;"><span class="badge badge-blue">C / R / U</span></td>
                        </tr>
                        <tr>
                            <td><strong>Opportunities</strong></td>
                            <td style="text-align: center;"><span style="color: #059669; font-weight: 700;">✓</span></td>
                            <td style="text-align: center;"><span style="color: #059669; font-weight: 700;">✓</span></td>
                            <td style="text-align: center;"><span style="color: #059669; font-weight: 700;">✓</span></td>
                            <td style="text-align: center;"><span style="color: #059669; font-weight: 700;">✓</span></td>
                            <td style="text-align: center;"><span class="badge badge-blue">Full CRUD</span></td>
                            <td style="text-align: center;"><span class="badge badge-gray">Read Only</span></td>
                        </tr>
                        <tr>
                            <td><strong>Purchase Deals</strong></td>
                            <td style="text-align: center;"><span style="color: #059669; font-weight: 700;">✓</span></td>
                            <td style="text-align: center;"><span style="color: #059669; font-weight: 700;">✓</span></td>
                            <td style="text-align: center;"><span style="color: #059669; font-weight: 700;">✓</span></td>
                            <td style="text-align: center;"><span style="color: #059669; font-weight: 700;">✓</span></td>
                            <td style="text-align: center;"><span class="badge badge-gray">Read Only</span></td>
                            <td style="text-align: center;"><span class="badge badge-purple">Full CRUD</span></td>
                        </tr>
                        <tr>
                            <td><strong>Shipping &amp; Logistics</strong></td>
                            <td style="text-align: center;"><span style="color: #059669; font-weight: 700;">✓</span></td>
                            <td style="text-align: center;"><span style="color: #059669; font-weight: 700;">✓</span></td>
                            <td style="text-align: center;"><span style="color: #059669; font-weight: 700;">✓</span></td>
                            <td style="text-align: center;"><span style="color: #059669; font-weight: 700;">✓</span></td>
                            <td style="text-align: center;"><span class="badge badge-blue">Track Status</span></td>
                            <td style="text-align: center;"><span class="badge badge-purple">Full CRUD</span></td>
                        </tr>
                        <tr>
                            <td><strong>Payments &amp; Invoices</strong></td>
                            <td style="text-align: center;"><span style="color: #059669; font-weight: 700;">✓</span></td>
                            <td style="text-align: center;"><span style="color: #059669; font-weight: 700;">✓</span></td>
                            <td style="text-align: center;"><span style="color: #059669; font-weight: 700;">✓</span></td>
                            <td style="text-align: center;"><span style="color: #059669; font-weight: 700;">✓</span></td>
                            <td style="text-align: center;"><span class="badge badge-gray">Read Only</span></td>
                            <td style="text-align: center;"><span class="badge badge-gray">Read Only</span></td>
                        </tr>
                        <tr>
                            <td><strong>Wires &amp; Bank Reconcile</strong></td>
                            <td style="text-align: center;"><span style="color: #059669; font-weight: 700;">✓</span></td>
                            <td style="text-align: center;"><span style="color: #059669; font-weight: 700;">✓</span></td>
                            <td style="text-align: center;"><span style="color: #059669; font-weight: 700;">✓</span></td>
                            <td style="text-align: center;"><span style="color: #059669; font-weight: 700;">✓</span></td>
                            <td style="text-align: center;"><span class="badge badge-red">No Access</span></td>
                            <td style="text-align: center;"><span class="badge badge-red">No Access</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ==================== TAB 4: FIELD-LEVEL RIGHTS (FLS) ==================== -->
    <div id="tab-pane-fls" class="settings-tab-pane" style="display: none;">
        <div class="card" style="padding: 1.25rem; margin-bottom: 1.25rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <div>
                    <h3 style="font-size: 1rem; font-weight: 700; color: var(--zinc-900); margin: 0;">Field-Level Security (FLS) Policies</h3>
                    <p style="font-size: 0.8rem; color: var(--zinc-500); margin: 0.25rem 0 0 0;">Strict masking and column-level encryption rights per role profile.</p>
                </div>
                <button type="button" class="btn btn-primary btn-sm" onclick="showToast('FLS security rules saved successfully!')">
                    <i data-lucide="shield-check" style="width: 14px; height: 14px;"></i>
                    <span>Enforce FLS</span>
                </button>
            </div>

            <div class="card-table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Sensitive Field</th>
                            <th>Parent Module</th>
                            <th>Security Classification</th>
                            <th>prof-admin</th>
                            <th>prof-sales</th>
                            <th>prof-procurement</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>credit_limit</strong></td>
                            <td>Accounts</td>
                            <td><span class="badge badge-yellow">Confidential</span></td>
                            <td><span class="badge badge-green">Read / Write</span></td>
                            <td><span class="badge badge-blue">Read Only</span></td>
                            <td><span class="badge badge-gray">Hidden</span></td>
                        </tr>
                        <tr>
                            <td><strong>utr_reference</strong></td>
                            <td>Wires &amp; Banking</td>
                            <td><span class="badge badge-red">Restricted Financial</span></td>
                            <td><span class="badge badge-green">Read / Write</span></td>
                            <td><span class="badge badge-red">Hidden</span></td>
                            <td><span class="badge badge-red">Hidden</span></td>
                        </tr>
                        <tr>
                            <td><strong>annual_revenue</strong></td>
                            <td>Accounts</td>
                            <td><span class="badge badge-yellow">Corporate Sensitive</span></td>
                            <td><span class="badge badge-green">Read / Write</span></td>
                            <td><span class="badge badge-green">Read / Write</span></td>
                            <td><span class="badge badge-blue">Read Only</span></td>
                        </tr>
                        <tr>
                            <td><strong>ssn_tax_id</strong></td>
                            <td>Users &amp; Vendors</td>
                            <td><span class="badge badge-purple">PII Masked</span></td>
                            <td><span class="badge badge-purple">Masked (Last 4)</span></td>
                            <td><span class="badge badge-red">Hidden</span></td>
                            <td><span class="badge badge-red">Hidden</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ==================== TAB 5: CUSTOM SCHEMA FIELDS ==================== -->
    <div id="tab-pane-fields" class="settings-tab-pane" style="display: none;">
        <div class="card" style="padding: 1.25rem; margin-bottom: 1.25rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <div>
                    <h3 style="font-size: 1rem; font-weight: 700; color: var(--zinc-900); margin: 0;">Enterprise Dynamic Schema &amp; Custom Fields Studio</h3>
                    <p style="font-size: 0.8rem; color: var(--zinc-500); margin: 0.25rem 0 0 0;">Provision new formula columns, rollup aggregations, and picklists without database migrations.</p>
                </div>
                <button type="button" class="btn btn-primary btn-sm" onclick="showToast('+ New Custom Field modal opened!')">
                    <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
                    <span>+ New Custom Field</span>
                </button>
            </div>

            <div class="card-table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Field API Name</th>
                            <th>Field Display Label</th>
                            <th>Target Module</th>
                            <th>Data Type</th>
                            <th>Required</th>
                            <th>Configuration / Formula</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>cf_target_roi</code></td>
                            <td>Target Return on Investment</td>
                            <td>Opportunities</td>
                            <td><span class="badge badge-blue">Percentage</span></td>
                            <td><span class="badge badge-green">Yes</span></td>
                            <td style="font-size: 0.75rem; color: var(--zinc-600);">Min 0%, Max 100%, 2 decimals</td>
                        </tr>
                        <tr>
                            <td><code>cf_total_order_spend</code></td>
                            <td>Aggregated PO Commitments</td>
                            <td>Accounts</td>
                            <td><span class="badge badge-purple">RollupSummary</span></td>
                            <td><span class="badge badge-gray">Auto</span></td>
                            <td style="font-size: 0.75rem; color: var(--zinc-600);">SUM(purchase_deals.amount)</td>
                        </tr>
                        <tr>
                            <td><code>cf_procurement_po_ref</code></td>
                            <td>Linked Procurement PO Number</td>
                            <td>Opportunities</td>
                            <td><span class="badge badge-indigo">Formula</span></td>
                            <td><span class="badge badge-gray">Auto</span></td>
                            <td style="font-size: 0.75rem; color: var(--zinc-600);">purchase_deal_id &rarr; po_number</td>
                        </tr>
                        <tr>
                            <td><code>cf_client_nps</code></td>
                            <td>Customer Satisfaction Rating</td>
                            <td>Accounts</td>
                            <td><span class="badge badge-yellow">Rating (★ 1-5)</span></td>
                            <td><span class="badge badge-gray">No</span></td>
                            <td style="font-size: 0.75rem; color: var(--zinc-600);">Star Icon Rating Score</td>
                        </tr>
                        <tr>
                            <td><code>cf_vat_number</code></td>
                            <td>Tax / VAT Identification</td>
                            <td>Accounts</td>
                            <td><span class="badge badge-red">MaskedText</span></td>
                            <td><span class="badge badge-green">Yes</span></td>
                            <td style="font-size: 0.75rem; color: var(--zinc-600);">Encrypted last-4 view</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ==================== TAB 6: THEME & BACKGROUND STUDIO ==================== -->
    <div id="tab-pane-branding" class="settings-tab-pane" style="display: none;">
        <div class="card" style="padding: 1.5rem; margin-bottom: 1.5rem;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.25rem;">
                <div>
                    <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--zinc-900); margin: 0; letter-spacing: -0.02em;">
                        Theme, Ambient Lighting &amp; Canvas Studio
                    </h3>
                    <p style="font-size: 0.8125rem; color: var(--zinc-500); margin: 0.25rem 0 0 0;">
                        Customize your CRM workspace with 10 ambient glow themes, custom background colors, and design systems.
                    </p>
                </div>
                <button type="button" class="btn btn-secondary btn-sm" onclick="resetThemeDefaults()">
                    <i data-lucide="rotate-ccw" style="width: 14px; height: 14px;"></i>
                    <span>Reset All Defaults</span>
                </button>
            </div>

            <!-- Ambient Glow Themes Grid -->
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; font-size: 0.8rem; font-weight: 700; color: var(--zinc-700); margin-bottom: 0.75rem;">
                    10 Ambient Atmosphere Presets:
                </label>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 0.75rem;">
                    <button type="button" onclick="applyAmbientTheme('sunset')" class="btn btn-secondary" style="padding: 0.75rem; text-align: left; display: flex; flex-direction: column; gap: 4px; border: 1px solid #fbcfe8;">
                        <span style="font-weight: 750; font-size: 0.85rem; color: #db2777;">🌅 Sunset Glow</span>
                        <span style="font-size: 0.7rem; color: var(--zinc-500);">Rose, Orange &amp; Amber</span>
                    </button>
                    <button type="button" onclick="applyAmbientTheme('ocean')" class="btn btn-secondary" style="padding: 0.75rem; text-align: left; display: flex; flex-direction: column; gap: 4px; border: 1px solid #bae6fd;">
                        <span style="font-weight: 750; font-size: 0.85rem; color: #0284c7;">🌊 Ocean Breeze</span>
                        <span style="font-size: 0.7rem; color: var(--zinc-500);">Cyan, Sky &amp; Teal</span>
                    </button>
                    <button type="button" onclick="applyAmbientTheme('cyberpunk')" class="btn btn-secondary" style="padding: 0.75rem; text-align: left; display: flex; flex-direction: column; gap: 4px; border: 1px solid #ddd6fe;">
                        <span style="font-weight: 750; font-size: 0.85rem; color: #7c3aed;">⚡ Cyberpunk Neon</span>
                        <span style="font-size: 0.7rem; color: var(--zinc-500);">Fuchsia &amp; Electric Violet</span>
                    </button>
                    <button type="button" onclick="applyAmbientTheme('cosmic')" class="btn btn-secondary" style="padding: 0.75rem; text-align: left; display: flex; flex-direction: column; gap: 4px; border: 1px solid #c7d2fe;">
                        <span style="font-weight: 750; font-size: 0.85rem; color: #4f46e5;">🌌 Cosmic Deep</span>
                        <span style="font-size: 0.7rem; color: var(--zinc-500);">Indigo &amp; Stellar Purple</span>
                    </button>
                    <button type="button" onclick="applyAmbientTheme('synthwave')" class="btn btn-secondary" style="padding: 0.75rem; text-align: left; display: flex; flex-direction: column; gap: 4px; border: 1px solid #fecdd3;">
                        <span style="font-weight: 750; font-size: 0.85rem; color: #e11d48;">🌆 Synthwave 80s</span>
                        <span style="font-size: 0.7rem; color: var(--zinc-500);">Retro Hot Pink &amp; Cyan</span>
                    </button>
                    <button type="button" onclick="applyAmbientTheme('sakura')" class="btn btn-secondary" style="padding: 0.75rem; text-align: left; display: flex; flex-direction: column; gap: 4px; border: 1px solid #fce7f3;">
                        <span style="font-weight: 750; font-size: 0.85rem; color: #ec4899;">🌸 Sakura Blossom</span>
                        <span style="font-size: 0.7rem; color: var(--zinc-500);">Cherry Pink &amp; Peach</span>
                    </button>
                    <button type="button" onclick="applyAmbientTheme('matrix')" class="btn btn-secondary" style="padding: 0.75rem; text-align: left; display: flex; flex-direction: column; gap: 4px; border: 1px solid #a7f3d0;">
                        <span style="font-weight: 750; font-size: 0.85rem; color: #059669;">💻 Matrix Green</span>
                        <span style="font-size: 0.7rem; color: var(--zinc-500);">Emerald Terminal Glow</span>
                    </button>
                    <button type="button" onclick="applyAmbientTheme('pastel')" class="btn btn-secondary" style="padding: 0.75rem; text-align: left; display: flex; flex-direction: column; gap: 4px; border: 1px solid #fed7aa;">
                        <span style="font-weight: 750; font-size: 0.85rem; color: #ea580c;">🎨 Pastel Dream</span>
                        <span style="font-size: 0.7rem; color: var(--zinc-500);">Soft Muted Rainbow</span>
                    </button>
                    <button type="button" onclick="applyAmbientTheme('it-hardware')" class="btn btn-secondary" style="padding: 0.75rem; text-align: left; display: flex; flex-direction: column; gap: 4px; border: 1px solid #99f6e4;">
                        <span style="font-weight: 750; font-size: 0.85rem; color: #0d9488;">🖥️ IT Hardware Pro</span>
                        <span style="font-size: 0.7rem; color: var(--zinc-500);">Server Room Teal &amp; Amber</span>
                    </button>
                    <button type="button" onclick="applyAmbientTheme('classic')" class="btn btn-secondary" style="padding: 0.75rem; text-align: left; display: flex; flex-direction: column; gap: 4px; border: 1px solid var(--zinc-300);">
                        <span style="font-weight: 750; font-size: 0.85rem; color: var(--zinc-900);">🏢 Classic Enterprise</span>
                        <span style="font-size: 0.7rem; color: var(--zinc-500);">Clean Slate &amp; Minimalist</span>
                    </button>
                </div>
            </div>

            <!-- Custom Canvas Background Color Picker -->
            <div style="background: var(--zinc-50); border: 1px solid var(--zinc-200); border-radius: 12px; padding: 1.25rem; margin-bottom: 1.5rem;">
                <h4 style="font-size: 0.9rem; font-weight: 700; color: var(--zinc-900); margin: 0 0 0.5rem 0;">
                    🎨 Custom Workspace Canvas Background
                </h4>
                <p style="font-size: 0.75rem; color: var(--zinc-500); margin-bottom: 0.75rem;">
                    Set your personalized background color hex code or pick from enterprise curated shades:
                </p>
                <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: 0.5rem; background: #fff; padding: 0.35rem 0.65rem; border-radius: 8px; border: 1px solid var(--zinc-300);">
                        <input type="color" id="settings-canvas-color-picker" value="#f8fafc" onchange="applyCustomCanvasBg(this.value)" style="width: 28px; height: 28px; border: none; cursor: pointer; border-radius: 4px;">
                        <span style="font-size: 0.8rem; font-family: monospace; font-weight: 600;" id="settings-canvas-hex-display">#f8fafc</span>
                    </div>

                    <div style="display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap;">
                        <button type="button" onclick="applyCustomCanvasBg('#f8fafc')" style="width: 24px; height: 24px; border-radius: 50%; background: #f8fafc; border: 2px solid var(--zinc-300);" title="Clean Slate"></button>
                        <button type="button" onclick="applyCustomCanvasBg('#f1f5f9')" style="width: 24px; height: 24px; border-radius: 50%; background: #f1f5f9; border: 2px solid var(--zinc-300);" title="Muted Gray"></button>
                        <button type="button" onclick="applyCustomCanvasBg('#eef2ff')" style="width: 24px; height: 24px; border-radius: 50%; background: #eef2ff; border: 2px solid #c7d2fe;" title="Soft Indigo"></button>
                        <button type="button" onclick="applyCustomCanvasBg('#f0fdf4')" style="width: 24px; height: 24px; border-radius: 50%; background: #f0fdf4; border: 2px solid #bbf7d0;" title="Soft Emerald"></button>
                        <button type="button" onclick="applyCustomCanvasBg('#fdf2f8')" style="width: 24px; height: 24px; border-radius: 50%; background: #fdf2f8; border: 2px solid #fbcfe8;" title="Soft Rose"></button>
                        <button type="button" onclick="applyCustomCanvasBg('#0f172a')" style="width: 24px; height: 24px; border-radius: 50%; background: #0f172a; border: 2px solid #334155;" title="Pitch Dark Navy"></button>
                        <button type="button" onclick="applyCustomCanvasBg('#18181b')" style="width: 24px; height: 24px; border-radius: 50%; background: #18181b; border: 2px solid #3f3f46;" title="Zinc Black"></button>
                    </div>
                </div>
            </div>

            <!-- Primary Accent Colors -->
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 700; color: var(--zinc-700); margin-bottom: 0.5rem;">
                    Primary Brand Accent Color:
                </label>
                <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                    <button type="button" onclick="setAccentColor('#4f46e5')" class="btn btn-sm btn-secondary" style="border-left: 5px solid #4f46e5;">Electric Violet</button>
                    <button type="button" onclick="setAccentColor('#10b981')" class="btn btn-sm btn-secondary" style="border-left: 5px solid #10b981;">Emerald Growth</button>
                    <button type="button" onclick="setAccentColor('#f59e0b')" class="btn btn-sm btn-secondary" style="border-left: 5px solid #f59e0b;">Sunset Amber</button>
                    <button type="button" onclick="setAccentColor('#f43f5e')" class="btn btn-sm btn-secondary" style="border-left: 5px solid #f43f5e;">Rose Flare</button>
                    <button type="button" onclick="setAccentColor('#06b6d4')" class="btn btn-sm btn-secondary" style="border-left: 5px solid #06b6d4;">Cyber Cyan</button>
                    <button type="button" onclick="setAccentColor('#475569')" class="btn btn-sm btn-secondary" style="border-left: 5px solid #475569;">Slate Tech</button>
                    <button type="button" onclick="setAccentColor('#09090b')" class="btn btn-sm btn-secondary" style="border-left: 5px solid #09090b;">Obsidian Dark</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== TAB 7: AUDIT LOGS TRAIL ==================== -->
    <div id="tab-pane-audit" class="settings-tab-pane" style="display: none;">
        <div class="card" style="padding: 1.25rem; margin-bottom: 1.25rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <div>
                    <h3 style="font-size: 1rem; font-weight: 700; color: var(--zinc-900); margin: 0;">SOC2 Security &amp; Compliance Audit Trail</h3>
                    <p style="font-size: 0.8rem; color: var(--zinc-500); margin: 0.25rem 0 0 0;">Immutable session logs with IP address tracking and cryptographic checksums.</p>
                </div>
                <button type="button" class="btn btn-secondary btn-sm" onclick="showToast('Audit trail verified: All signatures match!')">
                    <i data-lucide="shield-check" style="width: 14px; height: 14px;"></i>
                    <span>Verify Hashes</span>
                </button>
            </div>

            <div class="card-table-wrapper">
                <table class="data-table" id="settings-audit-table">
                    <thead>
                        <tr>
                            <th class="sortable" onclick="sortTable('settings-audit-table', 0)" style="min-width: 160px;">
                                Timestamp (UTC)
                                <span class="sort-icon">▲</span>
                                <div class="col-resizer"></div>
                            </th>
                            <th class="sortable" onclick="sortTable('settings-audit-table', 1)" style="min-width: 150px;">
                                Actor User
                                <span class="sort-icon">▲</span>
                                <div class="col-resizer"></div>
                            </th>
                            <th class="sortable" onclick="sortTable('settings-audit-table', 2)" style="min-width: 140px;">
                                Action Type
                                <span class="sort-icon">▲</span>
                                <div class="col-resizer"></div>
                            </th>
                            <th class="sortable" onclick="sortTable('settings-audit-table', 3)" style="min-width: 180px;">
                                Target Entity
                                <span class="sort-icon">▲</span>
                                <div class="col-resizer"></div>
                            </th>
                            <th class="sortable" onclick="sortTable('settings-audit-table', 4)" style="min-width: 130px;">
                                Source IP
                                <span class="sort-icon">▲</span>
                                <div class="col-resizer"></div>
                            </th>
                            <th style="min-width: 110px;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="font-family: monospace; font-size: 0.75rem;">2026-10-13 01:44:12</td>
                            <td><strong>Alex Morgan</strong></td>
                            <td><span class="badge badge-indigo">LOGIN_SESSION</span></td>
                            <td>Auth / SSO Okta</td>
                            <td style="font-family: monospace; font-size: 0.75rem;">192.168.1.104</td>
                            <td><span class="badge badge-green">Success</span></td>
                        </tr>
                        <tr>
                            <td style="font-family: monospace; font-size: 0.75rem;">2026-10-13 01:42:08</td>
                            <td><strong>Sarah Chen</strong></td>
                            <td><span class="badge badge-blue">WIRE_MATCH</span></td>
                            <td>Wire UTR-8829103 &rarr; INV-2026-001</td>
                            <td style="font-family: monospace; font-size: 0.75rem;">192.168.1.109</td>
                            <td><span class="badge badge-green">Success</span></td>
                        </tr>
                        <tr>
                            <td style="font-family: monospace; font-size: 0.75rem;">2026-10-13 01:38:55</td>
                            <td><strong>Jordan Lee</strong></td>
                            <td><span class="badge badge-yellow">DEAL_UPDATE</span></td>
                            <td>Opportunity: Enterprise Cloud Suite</td>
                            <td style="font-family: monospace; font-size: 0.75rem;">192.168.1.112</td>
                            <td><span class="badge badge-green">Success</span></td>
                        </tr>
                        <tr>
                            <td style="font-family: monospace; font-size: 0.75rem;">2026-10-13 01:15:30</td>
                            <td><strong>Marcus Brody</strong></td>
                            <td><span class="badge badge-purple">PO_COMMITTED</span></td>
                            <td>Purchase Deal: PO-99210-HW</td>
                            <td style="font-family: monospace; font-size: 0.75rem;">192.168.1.118</td>
                            <td><span class="badge badge-green">Success</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    // Tab Switcher Function
    function switchSettingsTab(tabId, btn) {
        // Hide all tab panes
        document.querySelectorAll('.settings-tab-pane').forEach(function(pane) {
            pane.style.display = 'none';
        });

        // Remove active class from all tab buttons
        document.querySelectorAll('.settings-tab-btn').forEach(function(b) {
            b.classList.remove('active-tab');
            b.style.background = 'var(--bg-card)';
            b.style.color = 'var(--text-secondary)';
        });

        // Show target pane
        var targetPane = document.getElementById('tab-pane-' + tabId);
        if (targetPane) {
            targetPane.style.display = 'block';
        }

        // Activate button
        if (btn) {
            btn.classList.add('active-tab');
            btn.style.background = 'var(--zinc-900)';
            btn.style.color = '#ffffff';
        }

        // Re-initialize column resizing for tables in this tab if needed
        if (window.initColumnResizing) {
            window.initColumnResizing();
        }
        if (window.lucide) {
            lucide.createIcons();
        }
    }

    // Set accent color helper
    function setAccentColor(hex) {
        if (typeof window.applyAccentColor === 'function') {
            window.applyAccentColor(hex);
        } else {
            document.documentElement.style.setProperty('--color-primary', hex);
            try { localStorage.setItem('dealsync_accent_color', hex); } catch(e) {}
        }
        showToast('Primary accent color set to ' + hex);
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Init table resizers
        if (window.initColumnResizing) {
            window.initColumnResizing();
        }
    });
</script>
@endsection
