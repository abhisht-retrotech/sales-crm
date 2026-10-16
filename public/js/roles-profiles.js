/**
 * DealSync Enterprise — Roles Hierarchy & Security Profiles Studio
 * 1-to-1 Parity with React SettingsView.tsx (lines 2483-2760)
 */
(function () {
    'use strict';

    let roles = [];
    let profiles = [];
    let selectedProfileId = null;
    let editingRoleId = null;
    let editingProfileId = null;
    let calSourcesTargetProfile = null;
    let feedbackTimeout = null;

    const DEFAULT_CALENDAR_SOURCES = [
        { id: 'cal-activities', object_name: 'activities', object_label: 'Meetings & Demonstrations', icon: 'video', color: '#7c3aed', is_enabled: true, date_field: 'start_time', title_field: 'subject' },
        { id: 'cal-calls', object_name: 'calls', object_label: 'Customer Phone Calls', icon: 'phone', color: '#10b981', is_enabled: true, date_field: 'call_date', title_field: 'subject' },
        { id: 'cal-tasks', object_name: 'tasks', object_label: 'Tasks & Due Milestones', icon: 'check-square', color: '#f59e0b', is_enabled: true, date_field: 'due_date', title_field: 'title' },
        { id: 'cal-deals', object_name: 'deals', object_label: 'Target Close Dates (Deals)', icon: 'shopping-bag', color: '#6366f1', is_enabled: true, date_field: 'expected_close', title_field: 'name' },
        { id: 'cal-invoices', object_name: 'invoices', object_label: 'Billing & Payment Due Dates', icon: 'credit-card', color: '#2563eb', is_enabled: false, date_field: 'due_date', title_field: 'invoice_number' },
        { id: 'cal-shipping', object_name: 'shipping', object_label: 'Fulfillment & Dispatch Dates', icon: 'truck', color: '#0284c7', is_enabled: false, date_field: 'ship_date', title_field: 'tracking_number' }
    ];

    let currentCalSources = [...DEFAULT_CALENDAR_SOURCES];

    // ============================================================================
    // INITIALIZATION & DATA HYDRATION
    // ============================================================================
    async function initRolesProfiles() {
        // 1. Hydrate from window globals if available
        if (Array.isArray(window.INITIAL_ROLES) && window.INITIAL_ROLES.length > 0) {
            roles = [...window.INITIAL_ROLES];
        }
        if (Array.isArray(window.INITIAL_PROFILES) && window.INITIAL_PROFILES.length > 0) {
            profiles = [...window.INITIAL_PROFILES];
        }

        // 2. Fallback to API if empty
        if (roles.length === 0 || profiles.length === 0) {
            try {
                const [rolesRes, profilesRes] = await Promise.all([
                    fetch('/api/settings/roles', { headers: { 'Accept': 'application/json' } }),
                    fetch('/api/settings/profiles', { headers: { 'Accept': 'application/json' } })
                ]);
                if (rolesRes.ok) {
                    const rData = await rolesRes.json();
                    if (Array.isArray(rData) && rData.length > 0) roles = rData;
                }
                if (profilesRes.ok) {
                    const pData = await profilesRes.json();
                    if (Array.isArray(pData) && pData.length > 0) profiles = pData;
                }
            } catch (err) {
                console.warn('API fetch fallback error:', err);
            }
        }

        if (!selectedProfileId && profiles.length > 0) {
            selectedProfileId = profiles[0].id;
        }

        renderRolesList();
        renderProfilesList();
        updateCountBadges();

        if (window.lucide) {
            lucide.createIcons();
        }
    }

    function updateCountBadges() {
        const rBadge = document.getElementById('rolesCountBadge');
        if (rBadge) rBadge.textContent = roles.length;

        const pBadge = document.getElementById('profilesCountBadge');
        if (pBadge) pBadge.textContent = profiles.length;
    }

    // ============================================================================
    // ROLES HIERARCHY RENDERING & HANDLERS
    // ============================================================================
    function renderRolesList() {
        const container = document.getElementById('rolesListContainer');
        if (!container) return;

        if (roles.length === 0) {
            container.innerHTML = `
                <div class="py-12 px-4 text-center space-y-3 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-xs">
                    <div class="w-10 h-10 mx-auto rounded-full bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                        <i data-lucide="layers" class="w-5 h-5"></i>
                    </div>
                    <h4 class="text-xs font-bold text-zinc-900 dark:text-zinc-100 m-0">No Roles Defined</h4>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 max-w-xs mx-auto">
                        Establish your enterprise organizational hierarchy and vertical data sharing reporting chain.
                    </p>
                    <button
                        type="button"
                        onclick="handleOpenCreateRole()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white shadow-xs cursor-pointer"
                    >
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                        <span>Create Role</span>
                    </button>
                </div>
            `;
            if (window.lucide) lucide.createIcons();
            return;
        }

        const roleMap = new Map(roles.map(r => [String(r.id), r.name]));

        container.innerHTML = roles.map(r => {
            const reportsToName = r.reports_to_role_id ? (roleMap.get(String(r.reports_to_role_id)) || r.reports_to_name) : null;
            return `
                <div class="group bg-white dark:bg-zinc-900 border border-zinc-200/90 dark:border-zinc-800/90 hover:border-zinc-300 dark:hover:border-zinc-700 rounded-xl p-4 shadow-xs transition-all space-y-2.5">
                    <!-- Top Row: Role Title & Action Toolbar -->
                    <div class="flex items-start justify-between gap-3 flex-wrap sm:flex-nowrap">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-bold text-zinc-900 dark:text-zinc-100 text-sm">
                                ${escapeHtml(r.name)}
                            </span>
                            ${reportsToName ? `
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-medium bg-zinc-100 text-zinc-700 border border-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:border-zinc-700">
                                    <span class="text-zinc-400">↳ Reports to:</span>
                                    <strong class="text-zinc-900 dark:text-zinc-100">${escapeHtml(reportsToName)}</strong>
                                </span>
                            ` : `
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800">
                                    ★ Top-level Role
                                </span>
                            `}
                        </div>

                        <!-- Actions -->
                        <div class="inline-flex items-center gap-1 shrink-0">
                            <button
                                type="button"
                                onclick="handleOpenEditRole('${r.id}')"
                                class="inline-flex items-center justify-center p-1.5 rounded-lg bg-zinc-100 hover:bg-zinc-200 text-zinc-600 dark:bg-zinc-800 dark:hover:bg-zinc-700 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700 transition-all cursor-pointer shadow-2xs"
                                title="Edit Role"
                            >
                                <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                            </button>
                            <button
                                type="button"
                                onclick="handleDeleteRole('${r.id}')"
                                class="inline-flex items-center justify-center p-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 dark:bg-rose-950/30 dark:hover:bg-rose-950/50 dark:text-rose-400 border border-rose-200 dark:border-rose-900 transition-all cursor-pointer shadow-2xs"
                                title="Delete Role"
                            >
                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Role Description -->
                    <div class="text-xs text-zinc-500 dark:text-zinc-400 leading-relaxed">
                        ${escapeHtml(r.description || 'Standard corporate organization role assignment.')}
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
        const modal = document.getElementById('roleModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
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
        const modal = document.getElementById('roleModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        if (window.lucide) lucide.createIcons();
    }

    function populateReportsToOptions(selectedId = '', excludeRoleId = '') {
        const select = document.getElementById('roleReportsToSelect');
        if (!select) return;
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
        const modal = document.getElementById('roleModal');
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
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
                updateCountBadges();
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

        if (!confirm(`Are you sure you want to delete role "${role.name}"? Subordinates will be automatically reassigned to its parent.`)) {
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
                updateCountBadges();
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
                <div class="py-12 px-4 text-center space-y-3 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-xs">
                    <div class="w-10 h-10 mx-auto rounded-full bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400 flex items-center justify-center">
                        <i data-lucide="shield" class="w-5 h-5"></i>
                    </div>
                    <h4 class="text-xs font-bold text-zinc-900 dark:text-zinc-100 m-0">No Profiles Configured</h4>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 max-w-xs mx-auto">
                        Create security profiles to define record-level CRUD permissions, login hour policies, and whitelists.
                    </p>
                    <button
                        type="button"
                        onclick="handleOpenCreateProfile()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white shadow-xs cursor-pointer"
                    >
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                        <span>Create Profile</span>
                    </button>
                </div>
            `;
            if (window.lucide) lucide.createIcons();
            return;
        }

        container.innerHTML = profiles.map(p => {
            const isSelected = String(selectedProfileId) === String(p.id);
            const isPrimaryAdmin = Boolean(p.is_admin) || String(p.id) === 'prof-admin' || p.name === 'System Administrator';

            return `
                <div
                    class="group rounded-xl p-4 transition-all cursor-pointer space-y-2.5 border-l-4 ${isSelected
                        ? 'border-l-blue-600 border-t border-r border-b border-blue-500/80 bg-white dark:bg-zinc-900 shadow-sm'
                        : (isPrimaryAdmin
                            ? 'border-l-purple-500 border-t border-r border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/60 dark:bg-zinc-900/60 hover:bg-white dark:hover:bg-zinc-900'
                            : 'border-l-zinc-300 dark:border-l-zinc-700 border-t border-r border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/60 dark:bg-zinc-900/60 hover:bg-white dark:hover:bg-zinc-900')}"
                    onclick="handleSelectProfile('${p.id}')"
                >
                    <!-- Top Row: Profile Title, Badges, and Action Toolbar -->
                    <div class="flex items-start justify-between gap-3 flex-wrap sm:flex-nowrap">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-bold text-zinc-900 dark:text-zinc-100 text-sm">
                                ${escapeHtml(p.name)}
                            </span>

                            ${isPrimaryAdmin ? `
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200 dark:bg-purple-950/40 dark:text-purple-300 dark:border-purple-800">
                                    Primary Admin
                                </span>
                            ` : ''}

                            ${isSelected ? `
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800">
                                    <i data-lucide="check" class="w-3 h-3"></i>
                                    <span>Active</span>
                                </span>
                            ` : `
                                <button
                                    type="button"
                                    onclick="handleSelectProfile('${p.id}', event)"
                                    class="px-2 py-0.5 rounded-full text-[10px] font-semibold text-zinc-500 dark:text-zinc-400 bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 border border-zinc-200 dark:border-zinc-700 transition-all cursor-pointer"
                                >
                                    Select
                                </button>
                            `}
                        </div>

                        <!-- Right Action Toolbar -->
                        <div class="inline-flex items-center gap-1.5 shrink-0" onclick="event.stopPropagation()">
                            <button
                                type="button"
                                onclick="handleOpenCalendarSources('${p.id}', event)"
                                class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200 dark:bg-indigo-950/40 dark:text-indigo-300 dark:border-indigo-800 transition-all cursor-pointer shadow-2xs"
                                title="Configure calendar object sources for this profile"
                            >
                                <i data-lucide="calendar" class="w-3 h-3"></i>
                                <span>Calendar Sources</span>
                            </button>

                            <button
                                type="button"
                                onclick="handleOpenEditProfile('${p.id}', event)"
                                class="inline-flex items-center justify-center p-1.5 rounded-lg bg-zinc-100 hover:bg-zinc-200 text-zinc-600 dark:bg-zinc-800 dark:hover:bg-zinc-700 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700 transition-all cursor-pointer shadow-2xs"
                                title="Edit Profile & Login Security"
                            >
                                <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                            </button>

                            ${!isPrimaryAdmin ? `
                                <button
                                    type="button"
                                    onclick="handleDeleteProfile('${p.id}', event)"
                                    class="inline-flex items-center justify-center p-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 dark:bg-rose-950/30 dark:hover:bg-rose-950/50 dark:text-rose-400 border border-rose-200 dark:border-rose-900 transition-all cursor-pointer shadow-2xs"
                                    title="Delete Profile"
                                >
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                </button>
                            ` : ''}
                        </div>
                    </div>

                    <!-- Profile Description -->
                    <div class="text-xs text-zinc-500 dark:text-zinc-400 leading-relaxed">
                        ${escapeHtml(p.description || 'Configured security policy and access controls.')}
                    </div>

                    <!-- Bottom Metadata & Restrictions Strip -->
                    <div class="flex items-center flex-wrap gap-2 pt-2 border-t border-zinc-100 dark:border-zinc-800/80">
                        <!-- IP Whitelist Badge -->
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-medium ${p.ip_whitelist ? 'bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800' : 'bg-zinc-100 text-zinc-600 border border-zinc-200 dark:bg-zinc-800 dark:text-zinc-400 dark:border-zinc-700'}">
                            <span class="text-zinc-400 dark:text-zinc-500">IP:</span>
                            <strong>${escapeHtml(p.ip_whitelist || 'Any')}</strong>
                        </span>

                        <!-- Login Hours Badge -->
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-medium ${p.login_time_start && p.login_time_end ? 'bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800' : 'bg-zinc-100 text-zinc-600 border border-zinc-200 dark:bg-zinc-800 dark:text-zinc-400 dark:border-zinc-700'}">
                            <span class="text-zinc-400 dark:text-zinc-500">Hours:</span>
                            <strong>${p.login_time_start && p.login_time_end ? `${p.login_time_start} - ${p.login_time_end}` : '24/7'}</strong>
                        </span>

                        <!-- Ambient Themes Badge -->
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-medium ${p.surprise_me_enabled === 0 ? 'bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-900' : 'bg-purple-50 text-purple-700 border border-purple-200 dark:bg-purple-950/40 dark:text-purple-300 dark:border-purple-800'}">
                            <span>${p.surprise_me_enabled === 0 ? '✕ Themes: Locked' : '✨ Themes: Permitted'}</span>
                        </span>

                        <!-- Users Assigned Count -->
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-medium bg-zinc-100 text-zinc-600 border border-zinc-200 dark:bg-zinc-800 dark:text-zinc-400 dark:border-zinc-700">
                            <i data-lucide="users" class="w-3 h-3 text-zinc-400"></i>
                            <span>${p.users_count || 0} user${p.users_count === 1 ? '' : 's'}</span>
                        </span>
                    </div>
                </div>
            `;
        }).join('');

        if (window.lucide) lucide.createIcons();
    }

    function handleSelectProfile(profileId, event) {
        if (event) event.stopPropagation();
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
        const modal = document.getElementById('profileModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
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
        const modal = document.getElementById('profileModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        if (window.lucide) lucide.createIcons();
    }

    function closeProfileModal() {
        const modal = document.getElementById('profileModal');
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
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
                updateCountBadges();
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

        if (profile.is_admin || String(profile.id) === 'prof-admin' || profile.name === 'System Administrator') {
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
                updateCountBadges();
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
    // CALENDAR SOURCES STUDIO MODAL
    // ============================================================================
    function handleOpenCalendarSources(profileId, event) {
        if (event) event.stopPropagation();
        const profile = profiles.find(p => String(p.id) === String(profileId));
        if (!profile) return;

        calSourcesTargetProfile = profile;
        const titleEl = document.getElementById('calSourcesProfileName');
        if (titleEl) titleEl.textContent = profile.name;

        // Restore saved settings from localStorage if any
        try {
            const saved = localStorage.getItem(`dealsync_cal_sources_${profile.id}`);
            if (saved) {
                currentCalSources = JSON.parse(saved);
            } else {
                currentCalSources = [...DEFAULT_CALENDAR_SOURCES];
            }
        } catch (e) {
            currentCalSources = [...DEFAULT_CALENDAR_SOURCES];
        }

        renderCalendarSourcesList();

        const modal = document.getElementById('calendarSourcesModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        if (window.lucide) lucide.createIcons();
    }

    function renderCalendarSourcesList() {
        const listEl = document.getElementById('calSourcesList');
        if (!listEl) return;

        listEl.innerHTML = currentCalSources.map(s => `
            <div class="flex items-center justify-between p-3 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50/70 dark:bg-zinc-800/40">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white text-xs font-bold shrink-0 shadow-2xs" style="background-color: ${s.color};">
                        <i data-lucide="${s.icon}" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <div class="text-xs font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-1.5">
                            <span>${escapeHtml(s.object_label)}</span>
                            <span class="text-[10px] font-mono font-normal text-zinc-400">(${escapeHtml(s.object_name)})</span>
                        </div>
                        <div class="text-[11px] text-zinc-500 dark:text-zinc-400">
                            Mapped Date: <code class="bg-zinc-200/70 dark:bg-zinc-700/60 px-1 rounded text-[10px]">${s.date_field}</code>
                            • Title: <code class="bg-zinc-200/70 dark:bg-zinc-700/60 px-1 rounded text-[10px]">${s.title_field}</code>
                        </div>
                    </div>
                </div>

                <!-- Toggle Switch -->
                <label class="relative inline-flex items-center cursor-pointer">
                    <input
                        type="checkbox"
                        ${s.is_enabled ? 'checked' : ''}
                        onchange="handleToggleCalendarSource('${s.id}')"
                        class="sr-only peer"
                    >
                    <div class="w-9 h-5 bg-zinc-200 peer-focus:outline-none rounded-full peer dark:bg-zinc-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-zinc-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-zinc-600 peer-checked:bg-indigo-600"></div>
                </label>
            </div>
        `).join('');

        if (window.lucide) lucide.createIcons();
    }

    function handleToggleCalendarSource(sourceId) {
        currentCalSources = currentCalSources.map(s =>
            s.id === sourceId ? { ...s, is_enabled: !s.is_enabled } : s
        );
    }

    function closeCalendarSourcesModal() {
        const modal = document.getElementById('calendarSourcesModal');
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function handleSaveCalendarSources() {
        if (!calSourcesTargetProfile) return;

        try {
            localStorage.setItem(`dealsync_cal_sources_${calSourcesTargetProfile.id}`, JSON.stringify(currentCalSources));
        } catch (e) {}

        closeCalendarSourcesModal();
        showFeedback(`Calendar sources for profile "${calSourcesTargetProfile.name}" updated successfully!`, 'success');
    }

    // ============================================================================
    // FEEDBACK BANNER (TOAST)
    // ============================================================================
    function showFeedback(msg, type = 'success') {
        const banner = document.getElementById('feedbackBanner');
        const textEl = document.getElementById('feedbackText');
        const iconContainer = document.getElementById('feedbackIconContainer');
        if (!banner || !textEl) return;

        textEl.textContent = msg;

        if (type === 'success') {
            banner.className = 'fixed bottom-5 right-5 z-50 flex items-center gap-2.5 px-4 py-3 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 dark:bg-emerald-950/80 dark:text-emerald-200 dark:border-emerald-800 shadow-lg text-xs font-semibold animate-fade-in';
            if (iconContainer) iconContainer.innerHTML = '<i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>';
        } else {
            banner.className = 'fixed bottom-5 right-5 z-50 flex items-center gap-2.5 px-4 py-3 rounded-xl bg-rose-50 text-rose-800 border border-rose-200 dark:bg-rose-950/80 dark:text-rose-200 dark:border-rose-900 shadow-lg text-xs font-semibold animate-fade-in';
            if (iconContainer) iconContainer.innerHTML = '<i data-lucide="alert-circle" class="w-4 h-4 text-rose-600 dark:text-rose-400"></i>';
        }

        banner.classList.remove('hidden');

        if (window.lucide) lucide.createIcons();

        if (feedbackTimeout) clearTimeout(feedbackTimeout);
        feedbackTimeout = setTimeout(() => {
            banner.classList.add('hidden');
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
            closeCalendarSourcesModal();
        }
    });

    // Expose all handlers to global window
    window.initRolesProfiles = initRolesProfiles;
    window.handleOpenCreateRole = handleOpenCreateRole;
    window.handleOpenEditRole = handleOpenEditRole;
    window.closeRoleModal = closeRoleModal;
    window.handleSaveRole = handleSaveRole;
    window.handleDeleteRole = handleDeleteRole;
    window.handleSelectProfile = handleSelectProfile;
    window.handleOpenCreateProfile = handleOpenCreateProfile;
    window.handleOpenEditProfile = handleOpenEditProfile;
    window.closeProfileModal = closeProfileModal;
    window.handleSaveProfile = handleSaveProfile;
    window.handleDeleteProfile = handleDeleteProfile;
    window.handleOpenCalendarSources = handleOpenCalendarSources;
    window.closeCalendarSourcesModal = closeCalendarSourcesModal;
    window.handleToggleCalendarSource = handleToggleCalendarSource;
    window.handleSaveCalendarSources = handleSaveCalendarSources;
    window.showFeedback = showFeedback;

    // Auto-run if DOM is already ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initRolesProfiles);
    } else {
        initRolesProfiles();
    }
})();
