@extends('layouts.app')

@section('title', 'Tenant Management Studio — DealSync Admin')

@section('content')
<div class="animate-fade-in" style="display: flex; flex-direction: column; gap: 1.25rem;">
        <!-- Header Bar -->
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
            <div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <h2 style="font-size: 1.125rem; font-weight: 700; color: var(--zinc-950); letter-spacing: -0.02em; margin: 0;">
                        Tenant Management Studio
                    </h2>
                    <span class="badge badge-purple" style="font-size: 0.675rem; font-weight: 700; padding: 0.15rem 0.5rem;">
                        Master Tenant Only
                    </span>
                </div>
                <p style="font-size: 0.8rem; color: var(--zinc-500); margin: 0.2rem 0 0 0;">
                    Provision isolated client organizations, configure company logos, and manage tenant organizations across the CRM ecosystem.
                </p>
            </div>

            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <button
                    type="button"
                    onclick="showToast('Tenant directory refreshed successfully!');"
                    class="btn btn-secondary btn-sm"
                    style="display: inline-flex; align-items: center; gap: 0.35rem;"
                    title="Refresh tenant directory"
                >
                    <i data-lucide="refresh-cw" style="width: 13px; height: 13px;"></i>
                    <span>Refresh</span>
                </button>

                <button
                    type="button"
                    onclick="openCreateTenantModal()"
                    class="btn btn-primary btn-sm"
                    style="display: inline-flex; align-items: center; gap: 0.4rem; background: #4f46e5; border-color: #4338ca; font-weight: 600; padding: 0.4rem 0.85rem;"
                >
                    <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
                    <span>Register New Tenant</span>
                </button>
            </div>
        </div>

        <!-- Directory Search & Controls -->
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
            <div style="position: relative; width: 320px; max-width: 100%;">
                <i data-lucide="search" style="width: 14px; height: 14px; color: var(--zinc-400); position: absolute; left: 10px; top: 50%; transform: translateY(-50%);"></i>
                <input
                    type="text"
                    id="tenant-search-input"
                    class="form-input"
                    oninput="filterTenants(this.value)"
                    placeholder="Search tenant name or ID..."
                    style="padding-left: 32px; font-size: 0.8125rem; height: 34px; width: 100%; border-radius: var(--radius-md); border: 1px solid var(--zinc-200); background: #ffffff;"
                >
            </div>

            <div style="font-size: 0.78rem; color: var(--zinc-500); font-weight: 500;">
                Showing <strong id="visible-tenant-count">5</strong> of <strong>5</strong> registered tenants
            </div>
        </div>

        <!-- Tenants Data Table -->
        <div class="card" style="padding: 0; overflow-x: auto; -webkit-overflow-scrolling: touch; border: 1px solid var(--border-card); border-radius: var(--radius-lg); background: #ffffff;">
            <table class="data-table" id="tenants-table" style="width: 100%; min-width: 880px; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--zinc-200); background: var(--zinc-50);">
                        <th style="padding: 0.75rem 1rem; text-align: left; font-size: 0.75rem; font-weight: 600; color: var(--zinc-700);">Tenant Organization</th>
                        <th style="padding: 0.75rem 0.85rem; text-align: left; font-size: 0.75rem; font-weight: 600; color: var(--zinc-700);">Tenant ID</th>
                        <th style="padding: 0.75rem 0.85rem; text-align: left; font-size: 0.75rem; font-weight: 600; color: var(--zinc-700);">Users</th>
                        <th style="padding: 0.75rem 0.85rem; text-align: left; font-size: 0.75rem; font-weight: 600; color: var(--zinc-700);">Provisioned Date</th>
                        <th style="padding: 0.75rem 0.85rem; text-align: left; font-size: 0.75rem; font-weight: 600; color: var(--zinc-700);">Type</th>
                        <th class="sticky-actions-col" style="padding: 0.75rem 1rem; width: 220px; min-width: 220px; text-align: right; font-size: 0.75rem; font-weight: 600; color: var(--zinc-700);">Actions</th>
                    </tr>
                </thead>
                <tbody id="tenants-table-body">
                    <!-- Tenant 1: Acme Enterprise (Master) -->
                    <tr class="tenant-row" data-name="Acme Enterprise Tech" data-id="tenant-acme-corp" style="border-bottom: 1px solid var(--zinc-100); background: rgba(99, 102, 241, 0.02);">
                        <td style="padding: 0.75rem 1rem;">
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <div style="width: 36px; height: 36px; border-radius: 8px; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); color: #ffffff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.85rem; border: 1px solid var(--zinc-200); box-shadow: 0 1px 2px rgba(0,0,0,0.05); flex-shrink: 0;">
                                    AC
                                </div>
                                <div>
                                    <div style="font-weight: 600; color: var(--zinc-950); display: flex; align-items: center; gap: 0.35rem; font-size: 0.85rem;">
                                        <span class="tenant-title">Acme Enterprise Tech</span>
                                        <span style="font-size: 0.625rem; color: #047857; background: #ecfdf5; border: 1px solid #a7f3d0; padding: 0.05rem 0.35rem; border-radius: 4px; font-weight: 700;">
                                            Active Workspace
                                        </span>
                                    </div>
                                    <div style="font-size: 0.72rem; color: var(--zinc-400); margin-top: 0.1rem;">
                                        ✨ Custom Logo Set
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td style="padding: 0.75rem 0.85rem;">
                            <span style="font-family: monospace; font-size: 0.75rem; background: var(--zinc-100); padding: 0.15rem 0.45rem; border-radius: 4px; color: var(--zinc-700); border: 1px solid var(--zinc-200);">
                                tenant-acme-corp
                            </span>
                        </td>
                        <td style="padding: 0.75rem 0.85rem;">
                            <span class="badge badge-gray" style="display: inline-flex; align-items: center; gap: 0.3rem; font-size: 0.75rem;">
                                <i data-lucide="users" style="width: 11px; height: 11px;"></i>
                                <span>8 users</span>
                            </span>
                        </td>
                        <td style="padding: 0.75rem 0.85rem; font-size: 0.78rem; color: var(--zinc-500);">
                            <div style="display: flex; align-items: center; gap: 0.35rem;">
                                <i data-lucide="calendar" style="width: 12px; height: 12px; color: var(--zinc-400);"></i>
                                <span>1/12/2026</span>
                            </div>
                        </td>
                        <td style="padding: 0.75rem 0.85rem;">
                            <span class="badge badge-purple" style="font-size: 0.65rem; font-weight: 700;">
                                👑 Master Tenant
                            </span>
                        </td>
                        <td class="sticky-actions-col" style="padding: 0.75rem 1rem; width: 220px; min-width: 220px; text-align: right;">
                            <div style="display: inline-flex; align-items: center; gap: 0.4rem; justify-content: flex-end;">
                                <button
                                    type="button"
                                    onclick="openEditTenantModal('tenant-acme-corp', 'Acme Enterprise Tech')"
                                    class="btn btn-secondary btn-sm"
                                    style="padding: 0.3rem 0.65rem; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 0.3rem; font-weight: 600;"
                                    title="Edit Company Name and Logo"
                                >
                                    <i data-lucide="edit-2" style="width: 12px; height: 12px;"></i>
                                    <span>Edit</span>
                                </button>
                                <button
                                    type="button"
                                    class="btn btn-sm"
                                    style="padding: 0.3rem 0.65rem; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 0.3rem; background: #ecfdf5; border: 1px solid #a7f3d0; color: #047857; font-weight: 600;"
                                >
                                    <i data-lucide="check-circle-2" style="width: 12px; height: 12px;"></i>
                                    <span>Current</span>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <!-- Tenant 2: Apex Global Logistics -->
                    <tr class="tenant-row" data-name="Apex Global Logistics" data-id="tenant-apex-logistics" style="border-bottom: 1px solid var(--zinc-100);">
                        <td style="padding: 0.75rem 1rem;">
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <div style="width: 36px; height: 36px; border-radius: 8px; background: #f1f5f9; color: var(--zinc-600); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.85rem; border: 1px solid var(--zinc-200); flex-shrink: 0;">
                                    AP
                                </div>
                                <div>
                                    <div style="font-weight: 600; color: var(--zinc-950); font-size: 0.85rem;">
                                        <span class="tenant-title">Apex Global Logistics</span>
                                    </div>
                                    <div style="font-size: 0.72rem; color: var(--zinc-400); margin-top: 0.1rem;">
                                        Default Brand Icon
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td style="padding: 0.75rem 0.85rem;">
                            <span style="font-family: monospace; font-size: 0.75rem; background: var(--zinc-100); padding: 0.15rem 0.45rem; border-radius: 4px; color: var(--zinc-700); border: 1px solid var(--zinc-200);">
                                tenant-apex-logistics
                            </span>
                        </td>
                        <td style="padding: 0.75rem 0.85rem;">
                            <span class="badge badge-gray" style="display: inline-flex; align-items: center; gap: 0.3rem; font-size: 0.75rem;">
                                <i data-lucide="users" style="width: 11px; height: 11px;"></i>
                                <span>4 users</span>
                            </span>
                        </td>
                        <td style="padding: 0.75rem 0.85rem; font-size: 0.78rem; color: var(--zinc-500);">
                            <div style="display: flex; align-items: center; gap: 0.35rem;">
                                <i data-lucide="calendar" style="width: 12px; height: 12px; color: var(--zinc-400);"></i>
                                <span>2/3/2026</span>
                            </div>
                        </td>
                        <td style="padding: 0.75rem 0.85rem;">
                            <span class="badge badge-blue" style="font-size: 0.65rem;">
                                Client Org
                            </span>
                        </td>
                        <td class="sticky-actions-col" style="padding: 0.75rem 1rem; width: 220px; min-width: 220px; text-align: right;">
                            <div style="display: inline-flex; align-items: center; gap: 0.4rem; justify-content: flex-end;">
                                <button
                                    type="button"
                                    onclick="openEditTenantModal('tenant-apex-logistics', 'Apex Global Logistics')"
                                    class="btn btn-secondary btn-sm"
                                    style="padding: 0.3rem 0.65rem; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 0.3rem; font-weight: 600;"
                                >
                                    <i data-lucide="edit-2" style="width: 12px; height: 12px;"></i>
                                    <span>Edit Branding</span>
                                </button>
                                <button
                                    type="button"
                                    onclick="handleSwitchTenant('Apex Global Logistics')"
                                    class="btn btn-sm"
                                    style="padding: 0.3rem 0.65rem; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 0.3rem; background: #f8fafc; border: 1px solid #e2e8f0; color: #334155; font-weight: 600;"
                                >
                                    <i data-lucide="external-link" style="width: 12px; height: 12px;"></i>
                                    <span>Switch</span>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <!-- Tenant 3: Skyline Cloud Systems -->
                    <tr class="tenant-row" data-name="Skyline Cloud Systems" data-id="tenant-skyline-cloud" style="border-bottom: 1px solid var(--zinc-100);">
                        <td style="padding: 0.75rem 1rem;">
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <div style="width: 36px; height: 36px; border-radius: 8px; background: #f1f5f9; color: var(--zinc-600); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.85rem; border: 1px solid var(--zinc-200); flex-shrink: 0;">
                                    SK
                                </div>
                                <div>
                                    <div style="font-weight: 600; color: var(--zinc-950); font-size: 0.85rem;">
                                        <span class="tenant-title">Skyline Cloud Systems</span>
                                    </div>
                                    <div style="font-size: 0.72rem; color: var(--zinc-400); margin-top: 0.1rem;">
                                        Default Brand Icon
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td style="padding: 0.75rem 0.85rem;">
                            <span style="font-family: monospace; font-size: 0.75rem; background: var(--zinc-100); padding: 0.15rem 0.45rem; border-radius: 4px; color: var(--zinc-700); border: 1px solid var(--zinc-200);">
                                tenant-skyline-cloud
                            </span>
                        </td>
                        <td style="padding: 0.75rem 0.85rem;">
                            <span class="badge badge-gray" style="display: inline-flex; align-items: center; gap: 0.3rem; font-size: 0.75rem;">
                                <i data-lucide="users" style="width: 11px; height: 11px;"></i>
                                <span>3 users</span>
                            </span>
                        </td>
                        <td style="padding: 0.75rem 0.85rem; font-size: 0.78rem; color: var(--zinc-500);">
                            <div style="display: flex; align-items: center; gap: 0.35rem;">
                                <i data-lucide="calendar" style="width: 12px; height: 12px; color: var(--zinc-400);"></i>
                                <span>3/18/2026</span>
                            </div>
                        </td>
                        <td style="padding: 0.75rem 0.85rem;">
                            <span class="badge badge-blue" style="font-size: 0.65rem;">
                                Client Org
                            </span>
                        </td>
                        <td class="sticky-actions-col" style="padding: 0.75rem 1rem; width: 220px; min-width: 220px; text-align: right;">
                            <div style="display: inline-flex; align-items: center; gap: 0.4rem; justify-content: flex-end;">
                                <button
                                    type="button"
                                    onclick="openEditTenantModal('tenant-skyline-cloud', 'Skyline Cloud Systems')"
                                    class="btn btn-secondary btn-sm"
                                    style="padding: 0.3rem 0.65rem; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 0.3rem; font-weight: 600;"
                                >
                                    <i data-lucide="edit-2" style="width: 12px; height: 12px;"></i>
                                    <span>Edit Branding</span>
                                </button>
                                <button
                                    type="button"
                                    onclick="handleSwitchTenant('Skyline Cloud Systems')"
                                    class="btn btn-sm"
                                    style="padding: 0.3rem 0.65rem; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 0.3rem; background: #f8fafc; border: 1px solid #e2e8f0; color: #334155; font-weight: 600;"
                                >
                                    <i data-lucide="external-link" style="width: 12px; height: 12px;"></i>
                                    <span>Switch</span>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <!-- Tenant 4: Vanguard Aerospace & Defense -->
                    <tr class="tenant-row" data-name="Vanguard Aerospace & Defense" data-id="tenant-vanguard-defense" style="border-bottom: 1px solid var(--zinc-100);">
                        <td style="padding: 0.75rem 1rem;">
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <div style="width: 36px; height: 36px; border-radius: 8px; background: #f1f5f9; color: var(--zinc-600); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.85rem; border: 1px solid var(--zinc-200); flex-shrink: 0;">
                                    VA
                                </div>
                                <div>
                                    <div style="font-weight: 600; color: var(--zinc-950); font-size: 0.85rem;">
                                        <span class="tenant-title">Vanguard Aerospace &amp; Defense</span>
                                    </div>
                                    <div style="font-size: 0.72rem; color: var(--zinc-400); margin-top: 0.1rem;">
                                        Default Brand Icon
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td style="padding: 0.75rem 0.85rem;">
                            <span style="font-family: monospace; font-size: 0.75rem; background: var(--zinc-100); padding: 0.15rem 0.45rem; border-radius: 4px; color: var(--zinc-700); border: 1px solid var(--zinc-200);">
                                tenant-vanguard-defense
                            </span>
                        </td>
                        <td style="padding: 0.75rem 0.85rem;">
                            <span class="badge badge-gray" style="display: inline-flex; align-items: center; gap: 0.3rem; font-size: 0.75rem;">
                                <i data-lucide="users" style="width: 11px; height: 11px;"></i>
                                <span>6 users</span>
                            </span>
                        </td>
                        <td style="padding: 0.75rem 0.85rem; font-size: 0.78rem; color: var(--zinc-500);">
                            <div style="display: flex; align-items: center; gap: 0.35rem;">
                                <i data-lucide="calendar" style="width: 12px; height: 12px; color: var(--zinc-400);"></i>
                                <span>4/2/2026</span>
                            </div>
                        </td>
                        <td style="padding: 0.75rem 0.85rem;">
                            <span class="badge badge-blue" style="font-size: 0.65rem;">
                                Client Org
                            </span>
                        </td>
                        <td class="sticky-actions-col" style="padding: 0.75rem 1rem; width: 220px; min-width: 220px; text-align: right;">
                            <div style="display: inline-flex; align-items: center; gap: 0.4rem; justify-content: flex-end;">
                                <button
                                    type="button"
                                    onclick="openEditTenantModal('tenant-vanguard-defense', 'Vanguard Aerospace & Defense')"
                                    class="btn btn-secondary btn-sm"
                                    style="padding: 0.3rem 0.65rem; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 0.3rem; font-weight: 600;"
                                >
                                    <i data-lucide="edit-2" style="width: 12px; height: 12px;"></i>
                                    <span>Edit Branding</span>
                                </button>
                                <button
                                    type="button"
                                    onclick="handleSwitchTenant('Vanguard Aerospace & Defense')"
                                    class="btn btn-sm"
                                    style="padding: 0.3rem 0.65rem; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 0.3rem; background: #f8fafc; border: 1px solid #e2e8f0; color: #334155; font-weight: 600;"
                                >
                                    <i data-lucide="external-link" style="width: 12px; height: 12px;"></i>
                                    <span>Switch</span>
                                </button>
                            </div>
                        </td>
                    </tr>

                    <!-- Tenant 5: Aurora Biotech Labs -->
                    <tr class="tenant-row" data-name="Aurora Biotech Labs" data-id="tenant-aurora-biotech" style="border-bottom: 1px solid var(--zinc-100);">
                        <td style="padding: 0.75rem 1rem;">
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <div style="width: 36px; height: 36px; border-radius: 8px; background: #f1f5f9; color: var(--zinc-600); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.85rem; border: 1px solid var(--zinc-200); flex-shrink: 0;">
                                    AU
                                </div>
                                <div>
                                    <div style="font-weight: 600; color: var(--zinc-950); font-size: 0.85rem;">
                                        <span class="tenant-title">Aurora Biotech Labs</span>
                                    </div>
                                    <div style="font-size: 0.72rem; color: var(--zinc-400); margin-top: 0.1rem;">
                                        Default Brand Icon
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td style="padding: 0.75rem 0.85rem;">
                            <span style="font-family: monospace; font-size: 0.75rem; background: var(--zinc-100); padding: 0.15rem 0.45rem; border-radius: 4px; color: var(--zinc-700); border: 1px solid var(--zinc-200);">
                                tenant-aurora-biotech
                            </span>
                        </td>
                        <td style="padding: 0.75rem 0.85rem;">
                            <span class="badge badge-gray" style="display: inline-flex; align-items: center; gap: 0.3rem; font-size: 0.75rem;">
                                <i data-lucide="users" style="width: 11px; height: 11px;"></i>
                                <span>2 users</span>
                            </span>
                        </td>
                        <td style="padding: 0.75rem 0.85rem; font-size: 0.78rem; color: var(--zinc-500);">
                            <div style="display: flex; align-items: center; gap: 0.35rem;">
                                <i data-lucide="calendar" style="width: 12px; height: 12px; color: var(--zinc-400);"></i>
                                <span>5/14/2026</span>
                            </div>
                        </td>
                        <td style="padding: 0.75rem 0.85rem;">
                            <span class="badge badge-blue" style="font-size: 0.65rem;">
                                Client Org
                            </span>
                        </td>
                        <td class="sticky-actions-col" style="padding: 0.75rem 1rem; width: 220px; min-width: 220px; text-align: right;">
                            <div style="display: inline-flex; align-items: center; gap: 0.4rem; justify-content: flex-end;">
                                <button
                                    type="button"
                                    onclick="openEditTenantModal('tenant-aurora-biotech', 'Aurora Biotech Labs')"
                                    class="btn btn-secondary btn-sm"
                                    style="padding: 0.3rem 0.65rem; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 0.3rem; font-weight: 600;"
                                >
                                    <i data-lucide="edit-2" style="width: 12px; height: 12px;"></i>
                                    <span>Edit Branding</span>
                                </button>
                                <button
                                    type="button"
                                    onclick="handleSwitchTenant('Aurora Biotech Labs')"
                                    class="btn btn-sm"
                                    style="padding: 0.3rem 0.65rem; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 0.3rem; background: #f8fafc; border: 1px solid #e2e8f0; color: #334155; font-weight: 600;"
                                >
                                    <i data-lucide="external-link" style="width: 12px; height: 12px;"></i>
                                    <span>Switch</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

<!-- ==================== REGISTER NEW TENANT MODAL ==================== -->
<div id="create-tenant-modal" class="modal-overlay">
    <div class="modal-card" style="max-width: 580px; width: 95%; max-height: 90vh; overflow-y: auto;">
        <!-- Header -->
        <div style="padding: 1.15rem 1.5rem; border-bottom: 1px solid var(--zinc-200); display: flex; align-items: center; justify-content: space-between; background: #ffffff;">
            <div style="display: flex; align-items: center; gap: 0.6rem;">
                <div style="width: 30px; height: 30px; border-radius: 8px; background: #e0e7ff; color: #4f46e5; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="building-2" style="width: 17px; height: 17px;"></i>
                </div>
                <h3 style="font-size: 1rem; font-weight: 700; color: var(--zinc-900); margin: 0;">Register New Tenant Organization</h3>
            </div>
            <button type="button" onclick="closeCreateTenantModal()" style="background: none; border: none; cursor: pointer; color: var(--zinc-400); padding: 0.25rem;">
                <i data-lucide="x" style="width: 18px; height: 18px;"></i>
            </button>
        </div>

        <form onsubmit="handleCreateTenantSubmit(event)" style="padding: 1.25rem 1.5rem; display: flex; flex-direction: column; gap: 1rem;">
            <!-- Automated Provisioning Info Callout -->
            <div style="background: #f5f3ff; border: 1px solid #ddd6fe; border-radius: var(--radius-md); padding: 0.75rem 0.85rem; font-size: 0.78rem; color: #5b21b6; display: flex; align-items: flex-start; gap: 0.5rem; line-height: 1.45;">
                <i data-lucide="sparkles" style="width: 16px; height: 16px; flex-shrink: 0; margin-top: 2px;"></i>
                <div>
                    <strong>Automated Organization Provisioning:</strong> Registering this company entity will automatically generate its isolated database workspace, seed default Security Profiles (System Administrator, Sales, Procurement), baseline Roles, default Page Layouts, and create its initial Administrator user.
                </div>
            </div>

            <!-- Company Name -->
            <div class="form-group">
                <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--zinc-800); margin-bottom: 0.35rem;">Company / Organization Entity Name *</label>
                <input
                    type="text"
                    id="new-tenant-name"
                    required
                    placeholder="e.g. Stark Industries or Apex Logistics"
                    oninput="handleNameSlugInput(this.value)"
                    style="width: 100%; padding: 0.5rem 0.75rem; border-radius: 8px; border: 1px solid var(--zinc-300); font-size: 0.8125rem;"
                >
            </div>

            <!-- Tenant ID -->
            <div class="form-group">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                    <label style="font-size: 0.75rem; font-weight: 600; color: var(--zinc-800);">Unique Tenant ID (System Identifier)</label>
                    <span style="font-size: 0.7rem; color: var(--zinc-400);">(Auto-generated, editable)</span>
                </div>
                <input
                    type="text"
                    id="new-tenant-id"
                    placeholder="tenant-company-name"
                    style="width: 100%; padding: 0.5rem 0.75rem; border-radius: 8px; border: 1px solid var(--zinc-300); font-family: monospace; font-size: 0.8rem; background: var(--zinc-50);"
                >
            </div>

            <!-- Company Logo Upload -->
            <div class="form-group">
                <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--zinc-800); margin-bottom: 0.35rem;">Company Logo (Branding)</label>
                <div
                    style="border: 2px dashed var(--zinc-300); border-radius: var(--radius-lg); padding: 1rem; text-align: center; background: var(--zinc-50); cursor: pointer;"
                    onclick="document.getElementById('new-tenant-logo-file').click()"
                >
                    <input
                        type="file"
                        id="new-tenant-logo-file"
                        style="display: none;"
                        accept="image/*"
                        onchange="handleLogoFileSelect(this, 'create-logo-preview')"
                    >
                    <div id="create-logo-preview" style="display: flex; flex-direction: column; align-items: center; gap: 0.35rem;">
                        <div style="width: 36px; height: 36px; border-radius: 50%; background: #e0e7ff; display: flex; align-items: center; justify-content: center; color: #4f46e5;">
                            <i data-lucide="upload" style="width: 16px; height: 16px;"></i>
                        </div>
                        <div style="font-size: 0.8125rem; font-weight: 600; color: var(--zinc-800);">
                            Click to upload company logo
                        </div>
                        <div style="font-size: 0.72rem; color: var(--zinc-500);">
                            PNG, JPG, SVG, or WebP (max 2 MB)
                        </div>
                    </div>
                </div>
            </div>

            <!-- Primary Administrator Details -->
            <div style="border-top: 1px solid var(--zinc-200); padding-top: 0.85rem;">
                <div style="font-weight: 600; font-size: 0.8125rem; color: var(--zinc-900); margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.35rem;">
                    <i data-lucide="shield-check" style="width: 14px; height: 14px; color: #4f46e5;"></i>
                    <span>Primary Tenant Administrator</span>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                    <div>
                        <label style="display: block; font-size: 0.725rem; font-weight: 600; color: var(--zinc-700); margin-bottom: 0.25rem;">Admin Full Name *</label>
                        <input type="text" required value="Admin User" style="width: 100%; padding: 0.45rem 0.65rem; border-radius: 6px; border: 1px solid var(--zinc-300); font-size: 0.8rem;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.725rem; font-weight: 600; color: var(--zinc-700); margin-bottom: 0.25rem;">Admin Email Address *</label>
                        <input type="email" required placeholder="admin@company.com" style="width: 100%; padding: 0.45rem 0.65rem; border-radius: 6px; border: 1px solid var(--zinc-300); font-size: 0.8rem;">
                    </div>
                </div>
            </div>

            <!-- Footer Actions -->
            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 0.65rem; padding-top: 0.75rem; border-top: 1px solid var(--zinc-200);">
                <button type="button" onclick="closeCreateTenantModal()" class="btn btn-secondary btn-sm">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary btn-sm" style="background: #4f46e5; border-color: #4338ca; display: inline-flex; align-items: center; gap: 0.4rem;">
                    <i data-lucide="sparkles" style="width: 14px; height: 14px;"></i>
                    <span>Register &amp; Provision Tenant</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ==================== EDIT TENANT BRANDING MODAL ==================== -->
<div id="edit-tenant-modal" class="modal-overlay">
    <div class="modal-card" style="max-width: 480px; width: 95%;">
        <div style="padding: 1.15rem 1.5rem; border-bottom: 1px solid var(--zinc-200); display: flex; align-items: center; justify-content: space-between; background: #ffffff;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <i data-lucide="edit-2" style="width: 16px; height: 16px; color: #4f46e5;"></i>
                <h3 id="edit-modal-title" style="font-size: 0.95rem; font-weight: 700; color: var(--zinc-900); margin: 0;">Edit Company Branding</h3>
            </div>
            <button type="button" onclick="closeEditTenantModal()" style="background: none; border: none; cursor: pointer; color: var(--zinc-400); padding: 0.25rem;">
                <i data-lucide="x" style="width: 18px; height: 18px;"></i>
            </button>
        </div>

        <form onsubmit="handleSaveEditSubmit(event)" style="padding: 1.25rem 1.5rem; display: flex; flex-direction: column; gap: 1rem;">
            <input type="hidden" id="edit-tenant-id">
            
            <div class="form-group">
                <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--zinc-800); margin-bottom: 0.35rem;">Company / Organization Name *</label>
                <input
                    type="text"
                    id="edit-tenant-name"
                    required
                    style="width: 100%; padding: 0.5rem 0.75rem; border-radius: 8px; border: 1px solid var(--zinc-300); font-size: 0.8125rem;"
                >
            </div>

            <div class="form-group">
                <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--zinc-800); margin-bottom: 0.35rem;">Company Logo (Upload or Replace)</label>
                <div
                    style="border: 2px dashed var(--zinc-300); border-radius: var(--radius-lg); padding: 1rem; text-align: center; background: var(--zinc-50); cursor: pointer;"
                    onclick="document.getElementById('edit-tenant-logo-file').click()"
                >
                    <input
                        type="file"
                        id="edit-tenant-logo-file"
                        style="display: none;"
                        accept="image/*"
                        onchange="handleLogoFileSelect(this, 'edit-logo-preview')"
                    >
                    <div id="edit-logo-preview" style="display: flex; flex-direction: column; align-items: center; gap: 0.35rem;">
                        <div style="width: 36px; height: 36px; border-radius: 50%; background: #e0e7ff; display: flex; align-items: center; justify-content: center; color: #4f46e5;">
                            <i data-lucide="upload" style="width: 16px; height: 16px;"></i>
                        </div>
                        <div style="font-size: 0.8125rem; font-weight: 600; color: var(--zinc-800);">
                            Click to upload new logo
                        </div>
                        <div style="font-size: 0.72rem; color: var(--zinc-500);">
                            PNG, JPG, SVG, or WebP
                        </div>
                    </div>
                </div>
            </div>

            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 0.65rem; padding-top: 0.75rem; border-top: 1px solid var(--zinc-200);">
                <button type="button" onclick="closeEditTenantModal()" class="btn btn-secondary btn-sm">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary btn-sm" style="background: #4f46e5; border-color: #4338ca;">
                    Save Branding Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Real-time table filter
    function filterTenants(query) {
        var q = query.toLowerCase().trim();
        var rows = document.querySelectorAll('#tenants-table-body .tenant-row');
        var visibleCount = 0;

        rows.forEach(function(row) {
            var name = (row.getAttribute('data-name') || '').toLowerCase();
            var id = (row.getAttribute('data-id') || '').toLowerCase();

            if (!q || name.indexOf(q) !== -1 || id.indexOf(q) !== -1) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        var countElem = document.getElementById('visible-tenant-count');
        if (countElem) countElem.textContent = visibleCount;
    }

    // Modal helpers
    function openCreateTenantModal() {
        var modal = document.getElementById('create-tenant-modal');
        if (modal) modal.classList.add('is-open');
    }

    function closeCreateTenantModal() {
        var modal = document.getElementById('create-tenant-modal');
        if (modal) modal.classList.remove('is-open');
    }

    function handleNameSlugInput(val) {
        var slug = val.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
        var idInput = document.getElementById('new-tenant-id');
        if (idInput) {
            idInput.value = slug ? ('tenant-' + slug) : '';
        }
    }

    function openEditTenantModal(tenantId, tenantName) {
        var modal = document.getElementById('edit-tenant-modal');
        var title = document.getElementById('edit-modal-title');
        var nameInput = document.getElementById('edit-tenant-name');
        var idInput = document.getElementById('edit-tenant-id');

        if (title) title.textContent = 'Edit Company Branding: ' + tenantName;
        if (nameInput) nameInput.value = tenantName;
        if (idInput) idInput.value = tenantId;

        if (modal) modal.classList.add('is-open');
    }

    function closeEditTenantModal() {
        var modal = document.getElementById('edit-tenant-modal');
        if (modal) modal.classList.remove('is-open');
    }

    function handleCreateTenantSubmit(e) {
        e.preventDefault();
        var name = document.getElementById('new-tenant-name').value;
        showToast('Tenant "' + name + '" successfully provisioned & workspace initialized!');
        closeCreateTenantModal();
    }

    function handleSaveEditSubmit(e) {
        e.preventDefault();
        var name = document.getElementById('edit-tenant-name').value;
        showToast('Tenant branding updated for "' + name + '"!');
        closeEditTenantModal();
    }

    function handleSwitchTenant(name, id) {
        if (typeof handleHeaderTenantChange === 'function') {
            handleHeaderTenantChange(id || name);
        } else {
            showToast('Switched active workspace to ' + name + '!');
        }
        var select = document.getElementById('header-tenant-select');
        if (select) {
            for (var i = 0; i < select.options.length; i++) {
                if (select.options[i].text.indexOf(name) !== -1 || (id && select.options[i].value === id)) {
                    select.selectedIndex = i;
                    break;
                }
            }
        }
    }

    function handleLogoFileSelect(input, previewContainerId) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                var container = document.getElementById(previewContainerId);
                if (container) {
                    container.innerHTML = '<img src="' + e.target.result + '" style="max-height: 50px; max-width: 140px; object-fit: contain; border-radius: 6px; padding: 2px; border: 1px solid var(--zinc-300); background: #fff;" /><span style="font-size: 0.72rem; color: #047857; font-weight: 600;">✓ Logo loaded</span>';
                }
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection
