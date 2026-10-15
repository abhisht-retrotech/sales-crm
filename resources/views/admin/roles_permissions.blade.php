@extends('layouts.app')

@section('title', 'Roles & Profiles — DealSync Admin')

@section('content')
<style>
/* ========================================================================= */
/* ROLES & PROFILES STUDIO DEDICATED STYLES                                  */
/* ========================================================================= */
.roles-profiles-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}

@media (max-width: 900px) {
    .roles-profiles-grid {
        grid-template-columns: 1fr;
    }
}

.form-select {
    width: 100%;
    box-sizing: border-box;
    height: 38px;
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

.form-input {
    width: 100%;
    box-sizing: border-box;
    height: 38px;
    background-color: var(--zinc-50, #f8fafc);
    border: 1px solid var(--zinc-200, #e2e8f0);
    border-radius: var(--radius-md, 8px);
    padding: 0.45rem 0.85rem;
    font-size: 0.8125rem;
    color: var(--zinc-950, #09090b);
    font-family: inherit;
    outline: none;
    transition: border-color 0.15s ease, background-color 0.15s ease, box-shadow 0.15s ease;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.02);
}

.form-input::placeholder {
    color: var(--zinc-400, #94a3b8);
    font-size: 0.8rem;
}

.form-input:hover {
    border-color: var(--zinc-300, #cbd5e1);
    background-color: #ffffff;
}

.form-input:focus {
    background-color: #ffffff !important;
    border-color: var(--color-primary, #4f46e5) !important;
    box-shadow: 0 0 0 3px var(--color-primary-subtle, rgba(79, 70, 229, 0.15)) !important;
}

.role-item-card, .profile-item-card {
    padding: 0.85rem 1rem;
    background: var(--zinc-50, #f8fafc);
    border-radius: var(--radius-lg, 12px);
    border: 1px solid var(--zinc-200, #e2e8f0);
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: all 0.15s ease;
}

.role-item-card:hover, .profile-item-card:hover {
    border-color: var(--zinc-300, #cbd5e1);
    background: #ffffff;
}

.profile-item-card.is-active {
    background: var(--zinc-100, #f1f5f9) !important;
    border-color: var(--zinc-900, #0f172a) !important;
}

.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(9, 9, 11, 0.55);
    backdrop-filter: blur(4px);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 99999;
    padding: 1.5rem;
    overflow-y: auto;
}

.modal-content {
    background: #ffffff;
    border: 1px solid var(--border-card, #e2e8f0);
    border-radius: var(--radius-2xl, 16px);
    width: 100%;
    max-height: calc(100vh - 3rem);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    margin: auto;
}

.modal-content > form {
    display: flex;
    flex-direction: column;
    flex: 1;
    min-height: 0;
    overflow: hidden;
    margin: 0;
}

.modal-header {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid var(--border-card, #e2e8f0);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-body {
    padding: 1.5rem;
    overflow-y: auto;
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.modal-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid var(--border-card, #e2e8f0);
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    background: var(--zinc-50, #f8fafc);
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

    <!-- TAB 2: ROLES & PROFILES (SettingsView.tsx lines 2262-2436) -->
    <div class="roles-profiles-grid">
        <!-- Roles Hierarchy Column (Left) -->
        <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <div>
                    <h2 style="font-size: 1rem; font-weight: 600; color: var(--zinc-950); letter-spacing: -0.02em; margin: 0;">Roles Hierarchy</h2>
                    <div style="font-size: 0.75rem; color: var(--zinc-500); margin-top: 0.15rem;">Reporting hierarchy and data visibility scope</div>
                </div>
                <button type="button" onclick="handleOpenCreateRole()" class="btn btn-primary btn-sm" style="display: inline-flex; align-items: center; gap: 0.35rem; font-weight: 600;">
                    <i data-lucide="plus" style="width: 13px; height: 13px;"></i>
                    <span>Create Role</span>
                </button>
            </div>

            <div class="card" id="rolesListContainer" style="display: flex; flex-direction: column; gap: 0.65rem; padding: 1rem;">
                <!-- Populated dynamically via renderRolesList() -->
            </div>
        </div>

        <!-- Security Profiles Column (Right) -->
        <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <div>
                    <h2 style="font-size: 1rem; font-weight: 600; color: var(--zinc-950); letter-spacing: -0.02em; margin: 0;">Security Profiles</h2>
                    <div style="font-size: 0.75rem; color: var(--zinc-500); margin-top: 0.15rem;">CRUD policies, IP whitelist &amp; login time rules</div>
                </div>
                <button type="button" onclick="handleOpenCreateProfile()" class="btn btn-primary btn-sm" style="display: inline-flex; align-items: center; gap: 0.35rem; font-weight: 600;">
                    <i data-lucide="plus" style="width: 13px; height: 13px;"></i>
                    <span>Create Profile</span>
                </button>
            </div>

            <div class="card" id="profilesListContainer" style="display: flex; flex-direction: column; gap: 0.65rem; padding: 1rem;">
                <!-- Populated dynamically via renderProfilesList() -->
            </div>
        </div>
    </div>
</div>

<!-- ===================================================================== -->
<!-- CREATE / EDIT ROLE MODAL (SettingsView.tsx lines 4682-4719)           -->
<!-- ===================================================================== -->
<div id="roleModal" class="modal-overlay" style="display: none;">
    <div class="modal-content animate-fade-in" style="max-width: 480px;">
        <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-card);">
            <h3 id="roleModalTitle" style="font-size: 1rem; font-weight: 600; color: var(--zinc-950); margin: 0;">
                Create New Role
            </h3>
            <button
                type="button"
                onclick="closeRoleModal()"
                style="background: none; border: none; cursor: pointer; font-size: 1.2rem; color: var(--zinc-400);"
            >✕</button>
        </div>
        <form id="roleForm" onsubmit="handleSaveRole(event)">
            <div class="modal-body" style="padding: 1.5rem; display: flex; flex-direction: column; gap: 1rem;">
                <div class="form-group" style="margin: 0;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.8125rem; margin-bottom: 0.35rem; display: block;">Role Title *</label>
                    <input
                        type="text"
                        id="roleNameInput"
                        required
                        placeholder="e.g. Regional Sales Director"
                        class="form-input"
                    >
                </div>
                <div class="form-group" style="margin: 0;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.8125rem; margin-bottom: 0.35rem; display: block;">Reports To Role</label>
                    <select class="form-select" id="roleReportsToSelect">
                        <option value="">-- Top Level (No Parent Role) --</option>
                    </select>
                </div>
                <div class="form-group" style="margin: 0;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.8125rem; margin-bottom: 0.35rem; display: block;">Role Description</label>
                    <input
                        type="text"
                        id="roleDescInput"
                        placeholder="Organizational authority and scope description"
                        class="form-input"
                    >
                </div>
            </div>
            <div class="modal-footer" style="padding: 1rem 1.5rem; border-top: 1px solid var(--border-card); display: flex; justify-content: flex-end; gap: 0.5rem;">
                <button type="button" onclick="closeRoleModal()" class="btn btn-secondary">Cancel</button>
                <button type="submit" id="roleSubmitBtn" class="btn btn-primary" style="font-weight: 600;">Create Role</button>
            </div>
        </form>
    </div>
</div>

<!-- ===================================================================== -->
<!-- CREATE / EDIT PROFILE MODAL (SettingsView.tsx lines 4721-4819)        -->
<!-- ===================================================================== -->
<div id="profileModal" class="modal-overlay" style="display: none;">
    <div class="modal-content animate-fade-in" style="max-width: 540px;">
        <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-card);">
            <h3 id="profileModalTitle" style="font-size: 1rem; font-weight: 600; color: var(--zinc-950); margin: 0;">
                Create Security Profile
            </h3>
            <button
                type="button"
                onclick="closeProfileModal()"
                style="background: none; border: none; cursor: pointer; font-size: 1.2rem; color: var(--zinc-400);"
            >✕</button>
        </div>
        <form id="profileForm" onsubmit="handleSaveProfile(event)">
            <div class="modal-body" style="padding: 1.5rem; display: flex; flex-direction: column; gap: 1rem;">
                <div class="form-group" style="margin: 0;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.8125rem; margin-bottom: 0.35rem; display: block;">Profile Name *</label>
                    <input
                        type="text"
                        id="profileNameInput"
                        required
                        placeholder="e.g. Operations Specialist"
                        class="form-input"
                    >
                </div>

                <div class="form-group" style="margin: 0;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.8125rem; margin-bottom: 0.35rem; display: block;">Description</label>
                    <input
                        type="text"
                        id="profileDescInput"
                        placeholder="Role and access scope description"
                        class="form-input"
                    >
                </div>

                <!-- IP Whitelist & Range Setting -->
                <div class="form-group" style="background: var(--zinc-50); padding: 0.85rem; border-radius: var(--radius-lg); border: 1px solid var(--zinc-200); margin: 0;">
                    <label class="form-label" style="font-weight: 600; color: var(--zinc-900); font-size: 0.8125rem; margin-bottom: 0.35rem; display: block;">
                        Allowed IP Range / Whitelist (Login Security)
                    </label>
                    <input
                        type="text"
                        id="profileIpWhitelistInput"
                        class="form-input"
                        placeholder="e.g. 127.0.0.1, 192.168.1.1-192.168.1.254, 10.0.0.0/24"
                    >
                    <div style="font-size: 0.725rem; color: var(--zinc-500); margin-top: 0.25rem;">
                        Supports individual IPs, IP ranges with hyphens, and CIDR subnets. <em>Leave blank to allow login from any IP address.</em>
                    </div>
                </div>

                <!-- Login Time Window Setting -->
                <div class="form-group" style="background: var(--zinc-50); padding: 0.85rem; border-radius: var(--radius-lg); border: 1px solid var(--zinc-200); margin: 0;">
                    <label class="form-label" style="font-weight: 600; color: var(--zinc-900); font-size: 0.8125rem; margin-bottom: 0.35rem; display: block;">
                        Allowed Login Hours Window
                    </label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                        <div>
                            <label style="font-size: 0.7rem; color: var(--zinc-500); display: block; margin-bottom: 0.15rem;">Start Time</label>
                            <input
                                type="time"
                                id="profileLoginTimeStartInput"
                                class="form-input"
                            >
                        </div>
                        <div>
                            <label style="font-size: 0.7rem; color: var(--zinc-500); display: block; margin-bottom: 0.15rem;">End Time</label>
                            <input
                                type="time"
                                id="profileLoginTimeEndInput"
                                class="form-input"
                            >
                        </div>
                    </div>
                    <div style="font-size: 0.725rem; color: var(--zinc-500); margin-top: 0.25rem;">
                        Users on this profile can only access the system during these hours. <em>Leave blank to allow login anytime 24/7.</em>
                    </div>
                </div>

                <!-- Ambient Themes Feature Toggle -->
                <div class="form-group" style="background: #fbfbfe; padding: 0.85rem; border-radius: var(--radius-lg); border: 1px solid #e0e7ff; margin: 0;">
                    <label style="display: flex; align-items: flex-start; gap: 0.65rem; cursor: pointer;">
                        <input
                            type="checkbox"
                            id="profileSurpriseMeCheckbox"
                            checked
                            style="width: 16px; height: 16px; accent-color: #7c3aed; margin-top: 0.15rem;"
                        >
                        <div>
                            <div style="font-weight: 600; color: #4338ca; font-size: 0.8125rem; display: flex; align-items: center; gap: 0.35rem;">
                                <i data-lucide="sparkles" style="width: 14px; height: 14px; color: #7c3aed;"></i>
                                <span>Enable Ambient Themes &amp; Glow Customization Studio</span>
                            </div>
                            <div style="font-size: 0.725rem; color: var(--zinc-600); margin-top: 0.2rem; line-height: 1.4;">
                                When enabled, users assigned to this profile can choose atmospheric ambient workspace themes (Warm Honey-Gold Glow, Ocean Teal Caustics, Cyberpunk Neon, etc.) and customize particle physics.
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            <div class="modal-footer" style="padding: 1rem 1.5rem; border-top: 1px solid var(--border-card); display: flex; justify-content: flex-end; gap: 0.5rem;">
                <button type="button" onclick="closeProfileModal()" class="btn btn-secondary">Cancel</button>
                <button type="submit" id="profileSubmitBtn" class="btn btn-primary" style="font-weight: 600;">Save Profile Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
// ============================================================================
// ROLES & PROFILES HYDRATED JAVASCRIPT STATE ENGINE (1-TO-1 REACT PARITY)
// ============================================================================
window.INITIAL_ROLES = @json($roles ?? []);
window.INITIAL_PROFILES = @json($profiles ?? []);
window.CSRF_TOKEN = '{{ csrf_token() }}';

let roles = Array.isArray(window.INITIAL_ROLES) ? [...window.INITIAL_ROLES] : [];
let profiles = Array.isArray(window.INITIAL_PROFILES) ? [...window.INITIAL_PROFILES] : [];
let selectedProfileId = profiles.length > 0 ? profiles[0].id : 'prof-admin';

let editingRoleId = null;
let editingProfileId = null;

function initRolesProfiles() {
    renderRolesList();
    renderProfilesList();

    if (window.lucide) lucide.createIcons();
}

// ============================================================================
// ROLES HIERARCHY RENDERING & HANDLERS
// ============================================================================
function renderRolesList() {
    const container = document.getElementById('rolesListContainer');
    if (!container) return;

    if (roles.length === 0) {
        container.innerHTML = `
            <div style="text-align: center; padding: 2rem; color: var(--zinc-500); font-size: 0.825rem;">
                No roles defined. Click "Create Role" to establish your organizational hierarchy.
            </div>
        `;
        return;
    }

    const roleMap = new Map(roles.map(r => [String(r.id), r.name]));

    container.innerHTML = roles.map(r => {
        const reportsToName = r.reports_to_role_id ? (roleMap.get(String(r.reports_to_role_id)) || r.reports_to_name) : null;
        return `
            <div class="role-item-card">
                <div>
                    <div style="font-weight: 600; color: var(--zinc-950); font-size: 0.8125rem;">${escapeHtml(r.name)}</div>
                    <div style="font-size: 0.725rem; color: var(--zinc-500); margin-top: 0.1rem;">${escapeHtml(r.description || 'Standard corporate role')}</div>
                    ${reportsToName ? `
                        <div style="font-size: 0.7rem; color: var(--zinc-600); margin-top: 0.2rem;">
                            Reports to: <strong style="color: var(--zinc-800);">${escapeHtml(reportsToName)}</strong>
                        </div>
                    ` : `
                        <div style="font-size: 0.7rem; color: #2563eb; margin-top: 0.2rem; font-weight: 500;">
                            Top-level Organization Role
                        </div>
                    `}
                </div>

                <div style="display: flex; align-items: center; gap: 0.35rem;">
                    <button
                        type="button"
                        onclick="handleOpenEditRole('${r.id}')"
                        class="btn btn-secondary btn-sm"
                        style="padding: 0.35rem 0.6rem;"
                        title="Edit Role"
                    >
                        <i data-lucide="edit-2" style="width: 12px; height: 12px;"></i>
                    </button>
                    <button
                        type="button"
                        onclick="handleDeleteRole('${r.id}')"
                        class="btn btn-secondary btn-sm"
                        style="color: var(--accent-rose, #e11d48); padding: 0.35rem 0.6rem;"
                        title="Delete Role"
                    >
                        <i data-lucide="trash-2" style="width: 12px; height: 12px;"></i>
                    </button>
                </div>
            </div>
        `;
    }).join('');

    if (window.lucide) lucide.createIcons();
}

function handleOpenCreateRole() {
    editingRoleId = null;
    document.getElementById('roleModalTitle').textContent = 'Create New Role';
    document.getElementById('roleNameInput').value = '';
    document.getElementById('roleDescInput').value = '';
    populateReportsToOptions();
    document.getElementById('roleSubmitBtn').textContent = 'Create Role';
    document.getElementById('roleModal').style.display = 'flex';
    if (window.lucide) lucide.createIcons();
}

function handleOpenEditRole(roleId) {
    const role = roles.find(r => String(r.id) === String(roleId));
    if (!role) return;

    editingRoleId = role.id;
    document.getElementById('roleModalTitle').textContent = `Edit Role: ${role.name}`;
    document.getElementById('roleNameInput').value = role.name;
    document.getElementById('roleDescInput').value = role.description || '';
    populateReportsToOptions(role.reports_to_role_id, role.id);
    document.getElementById('roleSubmitBtn').textContent = 'Save Changes';
    document.getElementById('roleModal').style.display = 'flex';
    if (window.lucide) lucide.createIcons();
}

function populateReportsToOptions(selectedId = '', excludeRoleId = '') {
    const select = document.getElementById('roleReportsToSelect');
    select.innerHTML = '<option value="">-- Top Level (No Parent Role) --</option>';

    roles.filter(r => String(r.id) !== String(excludeRoleId)).forEach(r => {
        const opt = document.createElement('option');
        opt.value = r.id;
        opt.textContent = r.name;
        if (String(r.id) === String(selectedId)) opt.selected = true;
        select.appendChild(opt);
    });
}

function closeRoleModal() {
    document.getElementById('roleModal').style.display = 'none';
}

async function handleSaveRole(e) {
    e.preventDefault();
    const name = document.getElementById('roleNameInput').value.trim();
    const desc = document.getElementById('roleDescInput').value.trim();
    const reportsTo = document.getElementById('roleReportsToSelect').value || null;

    if (!name) return;

    const btn = document.getElementById('roleSubmitBtn');
    const originalText = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'Saving...';

    const payload = {
        name: name,
        description: desc,
        reports_to_role_id: reportsTo,
        _token: window.CSRF_TOKEN
    };

    try {
        const url = editingRoleId 
            ? `/api/settings/roles/${encodeURIComponent(editingRoleId)}` 
            : '/api/settings/roles';
        const method = editingRoleId ? 'PUT' : 'POST';

        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': window.CSRF_TOKEN
            },
            body: JSON.stringify(payload)
        });

        const savedRole = await response.json();
        if (response.ok) {
            if (editingRoleId) {
                const idx = roles.findIndex(r => String(r.id) === String(editingRoleId));
                if (idx !== -1) roles[idx] = savedRole;
                showFeedback(`Role "${name}" updated successfully!`, 'success');
            } else {
                roles.push(savedRole);
                showFeedback(`Role "${name}" created successfully!`, 'success');
            }
            closeRoleModal();
            renderRolesList();
        } else {
            showFeedback(savedRole.message || 'Failed to save role.', 'error');
        }
    } catch (err) {
        console.error('Error saving role:', err);
        showFeedback('Network error while saving role.', 'error');
    } finally {
        btn.disabled = false;
        btn.textContent = originalText;
    }
}

async function handleDeleteRole(roleId) {
    const role = roles.find(r => String(r.id) === String(roleId));
    if (!role) return;

    if (!confirm(`Are you sure you want to delete role "${role.name}"? Subordinates will be reassigned to its parent.`)) {
        return;
    }

    try {
        const response = await fetch(`/api/settings/roles/${encodeURIComponent(roleId)}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': window.CSRF_TOKEN
            }
        });

        const result = await response.json();
        if (response.ok && result.success) {
            roles = roles.filter(r => String(r.id) !== String(roleId));
            roles.forEach(r => {
                if (String(r.reports_to_role_id) === String(roleId)) {
                    r.reports_to_role_id = role.reports_to_role_id;
                }
            });
            renderRolesList();
            showFeedback(`Role "${role.name}" deleted successfully.`, 'success');
        } else {
            showFeedback(result.message || 'Failed to delete role.', 'error');
        }
    } catch (err) {
        console.error('Error deleting role:', err);
        showFeedback('Network error while deleting role.', 'error');
    }
}

// ============================================================================
// SECURITY PROFILES RENDERING & HANDLERS
// ============================================================================
function renderProfilesList() {
    const container = document.getElementById('profilesListContainer');
    if (!container) return;

    if (profiles.length === 0) {
        container.innerHTML = `
            <div style="text-align: center; padding: 2rem; color: var(--zinc-500); font-size: 0.825rem;">
                No profiles defined. Click "Create Profile" to add security profiles.
            </div>
        `;
        return;
    }

    container.innerHTML = profiles.map(p => {
        const isSelected = String(selectedProfileId) === String(p.id);
        const isAdmin = Boolean(p.is_admin);
        return `
            <div
                class="profile-item-card ${isSelected ? 'is-active' : ''}"
                onclick="handleSelectProfile('${p.id}')"
                style="cursor: pointer;"
            >
                <div style="flex: 1; margin-right: 0.75rem;">
                    <div style="display: flex; align-items: center; gap: 0.45rem;">
                        <div style="font-weight: 600; color: var(--zinc-950); font-size: 0.8125rem;">${escapeHtml(p.name)}</div>
                        ${isAdmin ? `
                            <span class="badge badge-purple" style="font-size: 0.65rem;">Primary Admin</span>
                        ` : ''}
                    </div>
                    <div style="font-size: 0.725rem; color: var(--zinc-500); margin-top: 0.1rem;">${escapeHtml(p.description || 'Configured security policy')}</div>

                    <!-- Security Restrictions Badges (SettingsView.tsx line 2369) -->
                    <div style="display: flex; flex-wrap: wrap; gap: 0.35rem; margin-top: 0.35rem;">
                        ${p.ip_whitelist ? `
                            <span style="font-size: 0.675rem; background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; padding: 0.1rem 0.35rem; border-radius: 4px; font-family: monospace;">
                                IP: ${escapeHtml(p.ip_whitelist)}
                            </span>
                        ` : `
                            <span style="font-size: 0.675rem; background: #f4f4f5; color: #71717a; padding: 0.1rem 0.35rem; border-radius: 4px;">
                                IP: Any
                            </span>
                        `}

                        ${p.login_time_start && p.login_time_end ? `
                            <span style="font-size: 0.675rem; background: #fffbeb; color: #b45309; border: 1px solid #fde68a; padding: 0.1rem 0.35rem; border-radius: 4px;">
                                Hours: ${p.login_time_start} - ${p.login_time_end}
                            </span>
                        ` : `
                            <span style="font-size: 0.675rem; background: #f4f4f5; color: #71717a; padding: 0.1rem 0.35rem; border-radius: 4px;">
                                Hours: 24/7
                            </span>
                        `}

                        <span style="
                            font-size: 0.675rem;
                            background: ${p.surprise_me_enabled === 0 ? '#fef2f2' : '#f5f3ff'};
                            color: ${p.surprise_me_enabled === 0 ? '#dc2626' : '#7c3aed'};
                            border: 1px solid ${p.surprise_me_enabled === 0 ? '#fecaca' : '#ddd6fe'};
                            padding: 0.1rem 0.4rem;
                            border-radius: 4px;
                            display: inline-flex;
                            align-items: center;
                            gap: 0.2rem;
                            font-weight: 500;
                        ">
                            ${p.surprise_me_enabled === 0 ? '✕ Themes: Locked' : '✨ Themes: Permitted'}
                        </span>
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 0.35rem;">
                    <button
                        type="button"
                        onclick="handleOpenEditProfile('${p.id}', event)"
                        class="btn btn-secondary btn-sm"
                        style="padding: 0.35rem 0.6rem;"
                        title="Edit Profile & Login Security"
                    >
                        <i data-lucide="edit-2" style="width: 12px; height: 12px;"></i>
                    </button>
                    ${!isAdmin ? `
                        <button
                            type="button"
                            onclick="handleDeleteProfile('${p.id}', event)"
                            class="btn btn-secondary btn-sm"
                            style="color: var(--accent-rose, #e11d48); padding: 0.35rem 0.6rem;"
                            title="Delete Profile"
                        >
                            <i data-lucide="trash-2" style="width: 12px; height: 12px;"></i>
                        </button>
                    ` : ''}
                    <span class="badge ${isSelected ? 'badge-gray' : 'badge-blue'}" style="font-size: 0.7rem;">
                        ${isSelected ? 'Active' : 'Select'}
                    </span>
                </div>
            </div>
        `;
    }).join('');

    if (window.lucide) lucide.createIcons();
}

function handleSelectProfile(profileId) {
    selectedProfileId = profileId;
    renderProfilesList();
}

function handleOpenCreateProfile() {
    editingProfileId = null;
    document.getElementById('profileModalTitle').textContent = 'Create Security Profile';
    document.getElementById('profileNameInput').value = '';
    document.getElementById('profileDescInput').value = '';
    document.getElementById('profileIpWhitelistInput').value = '';
    document.getElementById('profileLoginTimeStartInput').value = '';
    document.getElementById('profileLoginTimeEndInput').value = '';
    document.getElementById('profileSurpriseMeCheckbox').checked = true;
    document.getElementById('profileSubmitBtn').textContent = 'Create Profile';
    document.getElementById('profileModal').style.display = 'flex';
    if (window.lucide) lucide.createIcons();
}

function handleOpenEditProfile(profileId, event) {
    if (event) event.stopPropagation();
    const profile = profiles.find(p => String(p.id) === String(profileId));
    if (!profile) return;

    editingProfileId = profile.id;
    document.getElementById('profileModalTitle').textContent = `Edit Profile: ${profile.name}`;
    document.getElementById('profileNameInput').value = profile.name;
    document.getElementById('profileDescInput').value = profile.description || '';
    document.getElementById('profileIpWhitelistInput').value = profile.ip_whitelist || '';
    document.getElementById('profileLoginTimeStartInput').value = profile.login_time_start || '';
    document.getElementById('profileLoginTimeEndInput').value = profile.login_time_end || '';
    document.getElementById('profileSurpriseMeCheckbox').checked = profile.surprise_me_enabled !== 0;
    document.getElementById('profileSubmitBtn').textContent = 'Save Profile Changes';
    document.getElementById('profileModal').style.display = 'flex';
    if (window.lucide) lucide.createIcons();
}

function closeProfileModal() {
    document.getElementById('profileModal').style.display = 'none';
}

async function handleSaveProfile(e) {
    e.preventDefault();
    const name = document.getElementById('profileNameInput').value.trim();
    const desc = document.getElementById('profileDescInput').value.trim();
    const ipWhitelist = document.getElementById('profileIpWhitelistInput').value.trim() || null;
    const loginStart = document.getElementById('profileLoginTimeStartInput').value || null;
    const loginEnd = document.getElementById('profileLoginTimeEndInput').value || null;
    const surpriseMe = document.getElementById('profileSurpriseMeCheckbox').checked ? 1 : 0;

    if (!name) return;

    const btn = document.getElementById('profileSubmitBtn');
    const originalText = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'Saving...';

    const payload = {
        name: name,
        description: desc,
        ip_whitelist: ipWhitelist,
        login_time_start: loginStart,
        login_time_end: loginEnd,
        surprise_me_enabled: surpriseMe,
        _token: window.CSRF_TOKEN
    };

    try {
        const url = editingProfileId 
            ? `/api/settings/profiles/${encodeURIComponent(editingProfileId)}` 
            : '/api/settings/profiles';
        const method = editingProfileId ? 'PUT' : 'POST';

        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': window.CSRF_TOKEN
            },
            body: JSON.stringify(payload)
        });

        const savedProfile = await response.json();
        if (response.ok) {
            if (editingProfileId) {
                const idx = profiles.findIndex(p => String(p.id) === String(editingProfileId));
                if (idx !== -1) profiles[idx] = savedProfile;
                showFeedback(`Profile "${name}" updated successfully!`, 'success');
            } else {
                profiles.push(savedProfile);
                showFeedback(`Profile "${name}" created successfully!`, 'success');
            }
            closeProfileModal();
            renderProfilesList();
        } else {
            showFeedback(savedProfile.message || 'Failed to save profile.', 'error');
        }
    } catch (err) {
        console.error('Error saving profile:', err);
        showFeedback('Network error while saving profile.', 'error');
    } finally {
        btn.disabled = false;
        btn.textContent = originalText;
    }
}

async function handleDeleteProfile(profileId, event) {
    if (event) event.stopPropagation();
    const profile = profiles.find(p => String(p.id) === String(profileId));
    if (!profile) return;

    if (profile.is_admin) {
        alert('Cannot delete the default System Administrator profile.');
        return;
    }

    if (!confirm(`Are you sure you want to delete profile "${profile.name}"? All associated permissions will be permanently removed.`)) {
        return;
    }

    try {
        const response = await fetch(`/api/settings/profiles/${encodeURIComponent(profileId)}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': window.CSRF_TOKEN
            }
        });

        const result = await response.json();
        if (response.ok && result.success) {
            profiles = profiles.filter(p => String(p.id) !== String(profileId));
            if (String(selectedProfileId) === String(profileId)) {
                selectedProfileId = profiles.length > 0 ? profiles[0].id : 'prof-admin';
            }
            renderProfilesList();
            showFeedback(`Profile "${profile.name}" deleted successfully.`, 'success');
        } else {
            showFeedback(result.message || 'Failed to delete profile.', 'error');
        }
    } catch (err) {
        console.error('Error deleting profile:', err);
        showFeedback('Network error while deleting profile.', 'error');
    }
}

// ============================================================================
// FEEDBACK BANNER
// ============================================================================
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

window.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeRoleModal();
        closeProfileModal();
    }
});

document.addEventListener('DOMContentLoaded', () => {
    initRolesProfiles();

    // Close modals on overlay backdrop click
    document.querySelectorAll('.modal-overlay').forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeRoleModal();
                closeProfileModal();
            }
        });
    });
});
</script>
@endsection
