@extends('layouts.app')

@section('title', 'Record Rights (CRUD Matrix) — DealSync Admin')

@section('content')
<style>
/* ========================================================================= */
/* RECORD-LEVEL RIGHTS MATRIX DEDICATED STYLES                               */
/* ========================================================================= */
.form-select {
    box-sizing: border-box;
    background-color: var(--zinc-50, #f8fafc);
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 0.75rem center;
    background-repeat: no-repeat;
    background-size: 1.15em 1.15em;
    padding: 0.45rem 2.25rem 0.45rem 0.85rem;
    border: 1px solid var(--zinc-200, #e2e8f0);
    border-radius: var(--radius-md, 8px);
    color: var(--zinc-950, #09090b);
    font-size: 0.8125rem;
    font-family: inherit;
    line-height: 1.4;
    outline: none;
    cursor: pointer;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    transition: border-color 0.15s ease, background-color 0.15s ease, box-shadow 0.15s ease;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.02);
}

.form-select:hover {
    border-color: var(--zinc-300, #cbd5e1);
    background-color: #ffffff;
}

.form-select:focus,
.form-select:focus-visible {
    background-color: #ffffff !important;
    border-color: var(--color-primary, #4f46e5) !important;
    box-shadow: 0 0 0 3px var(--color-primary-subtle, rgba(79, 70, 229, 0.15)) !important;
}

.form-select option {
    background: #ffffff;
    color: var(--zinc-900, #09090b);
    padding: 0.5rem 0.75rem;
    font-size: 0.8125rem;
}

.data-table th {
    background: var(--zinc-100, #f1f5f9);
    color: var(--zinc-700, #334155);
    font-weight: 600;
    font-size: 0.75rem;
    letter-spacing: 0.02em;
    padding: 0.65rem 0.85rem;
    border-bottom: 1px solid var(--zinc-200, #e2e8f0);
    white-space: nowrap;
}

.data-table td {
    padding: 0.75rem 0.85rem;
    border-bottom: 1px solid var(--zinc-200, #e2e8f0);
    font-size: 0.8125rem;
    color: var(--zinc-900, #0f172a);
    vertical-align: middle;
}

.data-table tbody tr:hover td {
    background-color: var(--zinc-50, #f8fafc);
}

.data-table tbody tr:last-child td {
    border-bottom: none;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(4px); }
    to { opacity: 1; transform: translateY(0); }
}

.animate-fade-in {
    animation: fadeIn 0.15s ease-out forwards;
}
</style>


<div style="max-width: 1200px; margin: 0 auto; padding-bottom: 3rem;" class="animate-fade-in">
    <!-- Feedback Banner (SettingsView.tsx line 1737) -->
    <div id="feedbackBanner" style="display: none; padding: 0.85rem 1.25rem; border-radius: var(--radius-lg); margin-bottom: 1.25rem; align-items: center; gap: 0.6rem; font-size: 0.8125rem; font-weight: 500;">
        <i id="feedbackIcon" data-lucide="check-circle-2" style="width: 16px; height: 16px; flex-shrink: 0;"></i>
        <span id="feedbackText"></span>
    </div>

    <!-- TAB 3: RECORD-LEVEL RIGHTS (CRUD) (SettingsView.tsx lines 2439-2556) -->
    <div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.75rem;">
            <div>
                <h2 style="font-size: 1rem; font-weight: 600; color: var(--zinc-950); letter-spacing: -0.02em; margin: 0;">
                    Record-Level Rights Matrix
                </h2>
                <div style="display: flex; align-items: center; gap: 0.65rem; margin-top: 0.25rem;">
                    <span style="font-size: 0.75rem; color: var(--zinc-500);">Configuring Profile:</span>
                    <select
                        id="selectedProfileSelect"
                        class="form-select"
                        onchange="handleProfileChange(this.value)"
                        style="width: 240px; font-weight: 600; padding: 0.3rem 0.65rem; font-size: 0.8125rem;"
                    >
                        <!-- Populated dynamically via renderProfileOptions() -->
                    </select>
                </div>
            </div>

            <button type="button" id="saveMatrixBtn" onclick="handleSavePermissions()" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 0.4rem; font-weight: 600;">
                <i id="saveMatrixIcon" data-lucide="save" style="width: 14px; height: 14px;"></i>
                <span id="saveMatrixText">Save Matrix</span>
            </button>
        </div>

        <div class="card" style="padding: 0; overflow: hidden; border-radius: var(--radius-xl, 14px);">
            <div style="overflow-x: auto;">
                <table class="data-table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th style="padding: 0.75rem 1rem;">Module / Object</th>
                            <th style="text-align: center; padding: 0.75rem 0.85rem;">Create (C)</th>
                            <th style="text-align: center; padding: 0.75rem 0.85rem;">Read (R)</th>
                            <th style="text-align: center; padding: 0.75rem 0.85rem;">Update (U)</th>
                            <th style="text-align: center; padding: 0.75rem 0.85rem;">Delete (D)</th>
                            <th style="text-align: center; padding: 0.75rem 0.85rem;">Transfer (Ownership)</th>
                            <th style="padding: 0.75rem 1rem;">Record Scope</th>
                        </tr>
                    </thead>
                    <tbody id="matrixTableBody">
                        <!-- Populated dynamically via renderMatrixRows() -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// ============================================================================
// RECORD-LEVEL RIGHTS MATRIX (1-TO-1 REACT PARITY WITH SettingsView.tsx)
// ============================================================================
const STORAGE_PROFILES_KEY = 'dealsync_profiles';
const STORAGE_SELECTED_PROFILE_KEY = 'dealsync_selected_profile_id';
const STORAGE_RECORD_PERMS_PREFIX = 'dealsync_record_perms_';

const SEED_PROFILES = [
    {
        id: 'prof-admin',
        name: 'System Administrator',
        description: 'Unrestricted administrative access to all records and settings'
    },
    {
        id: 'prof-sales',
        name: 'Standard Sales Representative',
        description: 'Read/Write access to Leads, Accounts, and Opportunities'
    },
    {
        id: 'prof-procurement',
        name: 'Procurement Specialist',
        description: 'Read/Write access to Vendors and Purchase Deals'
    },
    {
        id: 'prof-auditor',
        name: 'Read-Only Compliance Auditor',
        description: 'Read-only access across all records with no editing rights'
    }
];

const MODULE_OPTIONS = [
    { id: 'leads', name: 'Leads' },
    { id: 'accounts', name: 'Accounts' },
    { id: 'contacts', name: 'Contacts' },
    { id: 'opportunities', name: 'Opportunities (Sales Pipeline)' },
    { id: 'tasks', name: 'Tasks' },
    { id: 'purchase_deals', name: 'Deals (Purchase & Procurement)' },
    { id: 'shipping', name: 'Shipping & Logistics' },
    { id: 'payments', name: 'Payments & Invoicing (AR / AP)' },
    { id: 'wires', name: 'Wire Received & Bank Reconciliation' },
    { id: 'users', name: 'Users (Active User Directory)' }
];

let profiles = [];
let selectedProfileId = 'prof-admin';
let recordPerms = [];

function initRecordRights() {
    try {
        const storedProfiles = localStorage.getItem(STORAGE_PROFILES_KEY);
        profiles = storedProfiles ? JSON.parse(storedProfiles) : [...SEED_PROFILES];

        const storedSelected = localStorage.getItem(STORAGE_SELECTED_PROFILE_KEY);
        if (storedSelected && profiles.some(p => p.id === storedSelected)) {
            selectedProfileId = storedSelected;
        } else {
            selectedProfileId = profiles.length > 0 ? profiles[0].id : 'prof-admin';
        }
    } catch (e) {
        profiles = [...SEED_PROFILES];
        selectedProfileId = 'prof-admin';
    }

    renderProfileOptions();
    loadPermissionsForProfile(selectedProfileId);
}

function renderProfileOptions() {
    const select = document.getElementById('selectedProfileSelect');
    if (!select) return;

    select.innerHTML = profiles.map(p => `
        <option value="${p.id}" ${p.id === selectedProfileId ? 'selected' : ''}>
            ${escapeHtml(p.name)}
        </option>
    `).join('');
}

function handleProfileChange(newProfileId) {
    selectedProfileId = newProfileId;
    localStorage.setItem(STORAGE_SELECTED_PROFILE_KEY, selectedProfileId);
    loadPermissionsForProfile(selectedProfileId);
}

function loadPermissionsForProfile(profileId) {
    try {
        const stored = localStorage.getItem(STORAGE_RECORD_PERMS_PREFIX + profileId);
        if (stored) {
            recordPerms = JSON.parse(stored);
        } else {
            recordPerms = getDefaultPermsForProfile(profileId);
        }
    } catch (e) {
        recordPerms = getDefaultPermsForProfile(profileId);
    }

    renderMatrixRows();
}

function getDefaultPermsForProfile(profileId) {
    return MODULE_OPTIONS.map(mod => {
        if (profileId === 'prof-admin') {
            return {
                module: mod.id,
                can_create: 1,
                can_read: 1,
                can_update: 1,
                can_delete: 1,
                can_transfer: 1,
                record_scope: 'All'
            };
        }
        if (profileId === 'prof-sales') {
            const isSales = ['leads', 'accounts', 'contacts', 'opportunities', 'tasks'].includes(mod.id);
            const canTransfer = ['leads', 'accounts'].includes(mod.id) ? 1 : 0;
            return {
                module: mod.id,
                can_create: isSales ? 1 : 0,
                can_read: 1,
                can_update: isSales ? 1 : 0,
                can_delete: isSales ? 1 : 0,
                can_transfer: canTransfer,
                record_scope: isSales ? 'Team' : 'All'
            };
        }
        if (profileId === 'prof-procurement') {
            const isProc = ['accounts', 'purchase_deals', 'shipping', 'tasks'].includes(mod.id);
            return {
                module: mod.id,
                can_create: isProc ? 1 : 0,
                can_read: 1,
                can_update: isProc ? 1 : 0,
                can_delete: 0,
                can_transfer: 0,
                record_scope: isProc ? 'All' : 'Own'
            };
        }
        if (profileId === 'prof-auditor') {
            return {
                module: mod.id,
                can_create: 0,
                can_read: 1,
                can_update: 0,
                can_delete: 0,
                can_transfer: 0,
                record_scope: 'All'
            };
        }
        // General custom profile defaults
        return {
            module: mod.id,
            can_create: 1,
            can_read: 1,
            can_update: 1,
            can_delete: 0,
            can_transfer: 0,
            record_scope: 'All'
        };
    });
}

function renderMatrixRows() {
    const tbody = document.getElementById('matrixTableBody');
    if (!tbody) return;

    tbody.innerHTML = MODULE_OPTIONS.map(mod => {
        const perm = recordPerms.find(p => p.module === mod.id);
        const canCreate = perm ? Boolean(perm.can_create) : true;
        const canRead = perm ? Boolean(perm.can_read) : true;
        const canUpdate = perm ? Boolean(perm.can_update) : true;
        const canDelete = perm ? Boolean(perm.can_delete) : false;
        const canTransfer = perm ? (perm.can_transfer !== undefined ? Boolean(perm.can_transfer) : (selectedProfileId === 'prof-admin')) : (selectedProfileId === 'prof-admin');
        const scope = perm?.record_scope || 'All';

        return `
            <tr>
                <td>
                    <strong style="color: var(--zinc-950);">${escapeHtml(mod.name)}</strong>
                    <div style="font-size: 0.7rem; color: var(--zinc-400); font-family: monospace;">${escapeHtml(mod.id)}</div>
                </td>
                <td style="text-align: center;">
                    <input
                        type="checkbox"
                        ${canCreate ? 'checked' : ''}
                        onchange="updateRecordPerm('${mod.id}', 'can_create', this.checked)"
                        style="cursor: pointer; width: 15px; height: 15px; accent-color: var(--zinc-950);"
                    >
                </td>
                <td style="text-align: center;">
                    <input
                        type="checkbox"
                        ${canRead ? 'checked' : ''}
                        onchange="updateRecordPerm('${mod.id}', 'can_read', this.checked)"
                        style="cursor: pointer; width: 15px; height: 15px; accent-color: var(--zinc-950);"
                    >
                </td>
                <td style="text-align: center;">
                    <input
                        type="checkbox"
                        ${canUpdate ? 'checked' : ''}
                        onchange="updateRecordPerm('${mod.id}', 'can_update', this.checked)"
                        style="cursor: pointer; width: 15px; height: 15px; accent-color: var(--zinc-950);"
                    >
                </td>
                <td style="text-align: center;">
                    <input
                        type="checkbox"
                        ${canDelete ? 'checked' : ''}
                        onchange="updateRecordPerm('${mod.id}', 'can_delete', this.checked)"
                        style="cursor: pointer; width: 15px; height: 15px; accent-color: var(--zinc-950);"
                    >
                </td>
                <td style="text-align: center;">
                    <input
                        type="checkbox"
                        ${canTransfer ? 'checked' : ''}
                        onchange="updateRecordPerm('${mod.id}', 'can_transfer', this.checked)"
                        style="cursor: pointer; width: 15px; height: 15px; accent-color: #2563eb;"
                        title="Grant record ownership transfer rights to this profile"
                    >
                </td>
                <td>
                    <select
                        class="form-select"
                        onchange="updateRecordScope('${mod.id}', this.value)"
                        style="padding: 0.25rem 0.55rem; font-size: 0.75rem; width: 140px; height: 32px;"
                    >
                        <option value="All" ${scope === 'All' ? 'selected' : ''}>All Records</option>
                        <option value="Own" ${scope === 'Own' ? 'selected' : ''}>Own Records Only</option>
                        <option value="Team" ${scope === 'Team' ? 'selected' : ''}>Team Records Only</option>
                    </select>
                </td>
            </tr>
        `;
    }).join('');

    if (window.lucide) lucide.createIcons();
}

function updateRecordPerm(modId, field, checked) {
    const perm = recordPerms.find(p => p.module === modId);
    if (perm) {
        perm[field] = checked ? 1 : 0;
    } else {
        const newPerm = {
            module: modId,
            can_create: field === 'can_create' ? 1 : 0,
            can_read: field === 'can_read' ? 1 : 0,
            can_update: field === 'can_update' ? 1 : 0,
            can_delete: field === 'can_delete' ? 1 : 0,
            can_transfer: field === 'can_transfer' ? 1 : 0,
            record_scope: 'All'
        };
        recordPerms.push(newPerm);
    }
}

function updateRecordScope(modId, scope) {
    const perm = recordPerms.find(p => p.module === modId);
    if (perm) {
        perm.record_scope = scope;
    } else {
        const newPerm = {
            module: modId,
            can_create: 1,
            can_read: 1,
            can_update: 1,
            can_delete: 0,
            can_transfer: 0,
            record_scope: scope
        };
        recordPerms.push(newPerm);
    }
}

function handleSavePermissions() {
    const saveBtn = document.getElementById('saveMatrixBtn');
    const saveText = document.getElementById('saveMatrixText');

    saveBtn.disabled = true;
    saveText.textContent = 'Saving...';

    setTimeout(() => {
        try {
            localStorage.setItem(STORAGE_RECORD_PERMS_PREFIX + selectedProfileId, JSON.stringify(recordPerms));
            showFeedback('Permissions updated successfully!', 'success');
        } catch (e) {
            alert('Error saving permissions: ' + e.message);
        } finally {
            saveBtn.disabled = false;
            saveText.textContent = 'Save Matrix';
        }
    }, 250);
}

let feedbackTimeout = null;
function showFeedback(msg, type = 'success') {
    const banner = document.getElementById('feedbackBanner');
    const textEl = document.getElementById('feedbackText');
    const iconEl = document.getElementById('feedbackIcon');

    textEl.textContent = msg;
    if (type === 'success') {
        banner.style.background = '#ecfdf5';
        banner.style.border = '1px solid #a7f3d0';
        banner.style.color = '#047857';
        iconEl.setAttribute('data-lucide', 'check-circle-2');
    } else {
        banner.style.background = '#fef2f2';
        banner.style.border = '1px solid #fecaca';
        banner.style.color = '#b91c1c';
        iconEl.setAttribute('data-lucide', 'alert-circle');
    }

    banner.style.display = 'flex';
    if (window.lucide) lucide.createIcons();

    if (feedbackTimeout) clearTimeout(feedbackTimeout);
    feedbackTimeout = setTimeout(() => {
        banner.style.display = 'none';
    }, 4000);
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

document.addEventListener('DOMContentLoaded', initRecordRights);
</script>
@endsection
