/**
 * DealSync Users Directory & Quota Governance Engine
 * Parity with React SettingsView.tsx (lines 1978-2480) & UserDetailPage.tsx
 */
(function () {
    'use strict';

    const STORAGE_USERS_KEY = 'dealsync_users_directory_data';
    const STORAGE_INVITES_KEY = 'dealsync_user_invitations_data';
    const STORAGE_QUOTA_KEY = 'dealsync_tenant_seats_quota';
    const STORAGE_IMPERSONATED_KEY = 'dealsync_impersonated_user_id';

    const DEFAULT_USERS = [
        {
            id: 'usr-101',
            full_name: 'Alex Morgan',
            email: 'alex.morgan@acmecorp.com',
            role_id: 'role-ceo',
            role_name: 'Executive / CEO',
            profile_id: 'prof-admin',
            profile_name: 'System Administrator',
            status: 'Active',
            phone: '+1 (415) 890-1200',
            surprise_me_enabled: null,
            created_at: '2024-10-12T00:00:00Z',
            pipeline_owned: 1450000,
            opps_count: 12,
            accounts_count: 18,
            leads_count: 34,
            tasks_count: 8
        },
        {
            id: 'usr-102',
            full_name: 'Jordan Lee',
            email: 'jordan.lee@acmecorp.com',
            role_id: 'role-sales-rep',
            role_name: 'Sales Executive',
            profile_id: 'prof-sales',
            profile_name: 'Standard Sales Representative',
            status: 'Active',
            phone: '+1 (617) 555-0199',
            surprise_me_enabled: 1,
            created_at: '2024-10-20T00:00:00Z',
            pipeline_owned: 380000,
            opps_count: 6,
            accounts_count: 8,
            leads_count: 14,
            tasks_count: 5
        },
        {
            id: 'usr-103',
            full_name: 'Marcus Brody',
            email: 'marcus.brody@acmecorp.com',
            role_id: 'role-procurement-mgr',
            role_name: 'Procurement Lead',
            profile_id: 'prof-procurement',
            profile_name: 'Procurement Specialist',
            status: 'Active',
            phone: '+1 (408) 555-4321',
            surprise_me_enabled: 0,
            created_at: '2024-11-05T00:00:00Z',
            pipeline_owned: 210000,
            opps_count: 4,
            accounts_count: 5,
            leads_count: 6,
            tasks_count: 3
        },
        {
            id: 'usr-104',
            full_name: 'Sarah Chen',
            email: 'sarah.finance@acmecorp.com',
            role_id: 'role-finance-lead',
            role_name: 'Finance & Billing Lead',
            profile_id: 'prof-admin',
            profile_name: 'System Administrator',
            status: 'Active',
            phone: '+1 (312) 555-7890',
            surprise_me_enabled: null,
            created_at: '2024-11-18T00:00:00Z',
            pipeline_owned: 940000,
            opps_count: 8,
            accounts_count: 11,
            leads_count: 19,
            tasks_count: 4
        }
    ];

    const DEFAULT_INVITATIONS = [
        {
            id: 'inv-201',
            full_name: 'Samantha Vance',
            email: 'samantha.vance@acmecorp.com',
            role_id: 'role-sales-rep',
            role_name: 'Sales Executive',
            profile_id: 'prof-sales',
            profile_name: 'Standard Sales Representative',
            inviter_name: 'Alex Morgan',
            token: 'tok-sam-77291a84f',
            created_at: new Date(Date.now() - 24 * 60 * 60 * 1000).toISOString(),
            expires_at: new Date(Date.now() + 6 * 24 * 60 * 60 * 1000).toISOString(),
            status: 'pending'
        },
        {
            id: 'inv-202',
            full_name: 'David Kim',
            email: 'david.kim@acmecorp.com',
            role_id: 'role-procurement-mgr',
            role_name: 'Procurement Lead',
            profile_id: 'prof-procurement',
            profile_name: 'Procurement Specialist',
            inviter_name: 'Alex Morgan',
            token: 'tok-dav-88310be2c',
            created_at: new Date(Date.now() - 3 * 24 * 60 * 60 * 1000).toISOString(),
            expires_at: new Date(Date.now() + 4 * 24 * 60 * 60 * 1000).toISOString(),
            status: 'pending'
        }
    ];

    let usersList = [];
    let invitationsList = [];
    let maxSeats = 15;
    let userDirectoryTab = 'users'; // 'users' | 'invitations'
    let selectedUserForDetail = null;
    let offboardTargetUser = null;
    let feedbackTimer = null;

    const ROLE_NAMES = {
        'role-ceo': 'Executive / CEO',
        'role-sales-vp': 'VP of Sales',
        'role-sales-rep': 'Sales Executive',
        'role-procurement-mgr': 'Procurement Lead',
        'role-finance-lead': 'Finance & Billing Lead'
    };

    const PROFILE_NAMES = {
        'prof-admin': 'System Administrator',
        'prof-sales': 'Standard Sales Representative',
        'prof-procurement': 'Procurement Specialist',
        'prof-auditor': 'Read-Only Compliance Auditor'
    };

    function initUsersDirectory() {
        if (Array.isArray(window.INITIAL_ROLES)) {
            window.INITIAL_ROLES.forEach(r => {
                ROLE_NAMES[String(r.id)] = r.name;
            });
        }
        if (Array.isArray(window.INITIAL_PROFILES)) {
            window.INITIAL_PROFILES.forEach(p => {
                PROFILE_NAMES[String(p.id)] = p.name;
            });
        }

        try {
            let serverUsers = Array.isArray(window.INITIAL_USERS) && window.INITIAL_USERS.length > 0 ? window.INITIAL_USERS : null;
            let serverInvites = Array.isArray(window.INITIAL_INVITATIONS) && window.INITIAL_INVITATIONS.length > 0 ? window.INITIAL_INVITATIONS : null;

            if (serverUsers) {
                usersList = serverUsers.map(u => ({
                    id: String(u.id),
                    full_name: u.full_name || u.name || 'User',
                    email: u.email,
                    role_id: u.role_id ? String(u.role_id) : 'role-sales-rep',
                    role_name: u.role_name || u.role?.name || (ROLE_NAMES[u.role_id] || 'Sales Executive'),
                    profile_id: u.profile_id ? String(u.profile_id) : 'prof-sales',
                    profile_name: u.profile_name || u.profile?.name || (PROFILE_NAMES[u.profile_id] || 'Standard Profile'),
                    status: u.status || 'Active',
                    phone: u.phone || '+1 (555) 019-2834',
                    surprise_me_enabled: u.surprise_me_enabled,
                    created_at: u.created_at || new Date().toISOString(),
                    pipeline_owned: parseFloat(u.pipeline_owned || 0),
                    opps_count: parseInt(u.opps_count || 0),
                    accounts_count: parseInt(u.accounts_count || 0),
                    leads_count: parseInt(u.leads_count || 0),
                    tasks_count: parseInt(u.tasks_count || 0)
                }));
                persistUsers();
            } else {
                const u = localStorage.getItem(STORAGE_USERS_KEY);
                usersList = u ? JSON.parse(u) : [...DEFAULT_USERS];
            }

            if (serverInvites) {
                invitationsList = serverInvites.map(inv => ({
                    id: String(inv.id),
                    full_name: inv.full_name,
                    email: inv.email,
                    role_id: inv.role_id ? String(inv.role_id) : 'role-sales-rep',
                    role_name: inv.role?.name || (ROLE_NAMES[inv.role_id] || 'Sales Executive'),
                    profile_id: inv.profile_id ? String(inv.profile_id) : 'prof-sales',
                    profile_name: inv.profile_name || inv.profile?.name || (PROFILE_NAMES[inv.profile_id] || 'Standard Profile'),
                    inviter_name: inv.invited_by?.name || 'Alex Morgan',
                    token: inv.token,
                    created_at: inv.created_at,
                    expires_at: inv.expires_at,
                    status: inv.status || 'pending'
                }));
                persistInvitations();
            } else {
                const inv = localStorage.getItem(STORAGE_INVITES_KEY);
                invitationsList = inv ? JSON.parse(inv) : [...DEFAULT_INVITATIONS];
            }

            maxSeats = window.INITIAL_MAX_SEATS || 15;
            persistQuota();
        } catch (e) {
            usersList = [...DEFAULT_USERS];
            invitationsList = [...DEFAULT_INVITATIONS];
            maxSeats = window.INITIAL_MAX_SEATS || 15;
        }

        renderDirectory();
        updateQuotaGauge();
        initUsersTableResizing();

        if (window.lucide) {
            lucide.createIcons();
        }
    }

    function persistUsers() {
        try {
            localStorage.setItem(STORAGE_USERS_KEY, JSON.stringify(usersList));
        } catch (e) {}
    }

    function persistInvitations() {
        try {
            localStorage.setItem(STORAGE_INVITES_KEY, JSON.stringify(invitationsList));
        } catch (e) {}
    }

    function persistQuota() {
        try {
            localStorage.setItem(STORAGE_QUOTA_KEY, String(maxSeats));
        } catch (e) {}
    }

    function updateQuotaGauge() {
        const activeCount = usersList.filter(u => u.status === 'Active').length;
        const inactiveCount = usersList.filter(u => u.status === 'Inactive').length;
        const pendingCount = invitationsList.filter(i => i.status === 'pending').length;
        const totalAllocated = activeCount + pendingCount;
        const availableSeats = Math.max(0, maxSeats - totalAllocated);
        const pct = Math.min(100, Math.round((totalAllocated / maxSeats) * 100));

        const allocEl = document.getElementById('quotaAllocatedText');
        if (allocEl) allocEl.textContent = `${totalAllocated} of ${maxSeats} Seats Allocated (${pct}%)`;

        const availEl = document.getElementById('quotaAvailableText');
        if (availEl) {
            availEl.textContent = `${availableSeats} Seats Available`;
            availEl.className = availableSeats <= 0 ? 'text-rose-500 font-semibold' : 'text-emerald-600 font-semibold';
        }

        const barEl = document.getElementById('quotaProgressBar');
        if (barEl) {
            barEl.style.width = `${pct}%`;
            barEl.className = `h-full rounded-full transition-all duration-500 ${pct >= 100 ? 'bg-rose-500' : (pct >= 80 ? 'bg-amber-500' : 'bg-emerald-500')}`;
        }

        const mActive = document.getElementById('metricActiveUsers');
        if (mActive) mActive.textContent = activeCount;

        const mPending = document.getElementById('metricPendingInvites');
        if (mPending) mPending.textContent = pendingCount;

        const mAvail = document.getElementById('metricAvailableSeats');
        if (mAvail) {
            mAvail.textContent = availableSeats;
            mAvail.className = `text-lg sm:text-xl font-bold tracking-tight ${availableSeats > 0 ? 'text-indigo-600 dark:text-indigo-400' : 'text-rose-600 dark:text-rose-400'}`;
        }

        const mInactive = document.getElementById('metricInactiveAccounts');
        if (mInactive) mInactive.textContent = inactiveCount;

        const countBadge = document.getElementById('userCountBadge');
        if (countBadge) countBadge.textContent = usersList.length;

        const pendingBadge = document.getElementById('pendingCountBadge');
        if (pendingBadge) {
            pendingBadge.textContent = pendingCount;
            pendingBadge.style.display = pendingCount > 0 ? 'inline-flex' : 'none';
        }

        const invRem = document.getElementById('inviteModalRemainingSeats');
        if (invRem) {
            invRem.textContent = `${availableSeats} Seat${availableSeats === 1 ? '' : 's'} Remaining`;
            invRem.className = `font-bold ${availableSeats <= 0 ? 'text-rose-600' : 'text-emerald-600'}`;
        }
    }

    function setUserDirectoryTab(tab) {
        userDirectoryTab = tab;
        const usersBtn = document.getElementById('subtabBtnUsers');
        const invBtn = document.getElementById('subtabBtnInvitations');
        const usersCard = document.getElementById('usersTableCard');
        const invCard = document.getElementById('invitationsTableCard');

        if (!usersBtn || !invBtn || !usersCard || !invCard) return;

        if (tab === 'users') {
            usersBtn.className = 'px-3.5 py-1.5 text-xs font-semibold rounded-lg bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white shadow-xs transition-all cursor-pointer';
            invBtn.className = 'px-3.5 py-1.5 text-xs font-medium rounded-lg text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-all inline-flex items-center gap-1.5 cursor-pointer';

            usersCard.style.display = 'block';
            invCard.style.display = 'none';
        } else {
            invBtn.className = 'px-3.5 py-1.5 text-xs font-semibold rounded-lg bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white shadow-xs inline-flex items-center gap-1.5 transition-all cursor-pointer';
            usersBtn.className = 'px-3.5 py-1.5 text-xs font-medium rounded-lg text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-all cursor-pointer';

            usersCard.style.display = 'none';
            invCard.style.display = 'block';
        }

        renderDirectory();
        initUsersTableResizing();
        if (window.lucide) lucide.createIcons();
    }

    function renderDirectory() {
        const impersonatedId = localStorage.getItem(STORAGE_IMPERSONATED_KEY);

        // 1. Render Active Users
        const usersTbody = document.getElementById('activeUsersTbody');
        if (usersTbody) {
            usersTbody.innerHTML = '';

            usersList.forEach(u => {
                const isCurrentActive = impersonatedId === u.id || (!impersonatedId && (u.id === 'usr-101' || u.email === 'alex.morgan@acmecorp.com'));
                const profileDefault = true;
                const currentVal = u.surprise_me_enabled === null || u.surprise_me_enabled === undefined
                    ? 'inherit'
                    : (u.surprise_me_enabled === 1 || u.surprise_me_enabled === true ? '1' : '0');

                const initials = u.full_name ? u.full_name.split(' ').map(n => n[0]).join('').slice(0, 2).toUpperCase() : 'U';

                const tr = document.createElement('tr');
                tr.className = 'group hover:bg-zinc-50/80 dark:hover:bg-zinc-800/50 cursor-pointer transition-colors duration-150 border-b border-zinc-100 dark:border-zinc-800/80 text-zinc-900 dark:text-zinc-100';
                tr.title = 'Click to open full user profile page';
                tr.onclick = () => openUserDetailView(u);

                tr.innerHTML = `
                    <td class="py-3 px-4">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-indigo-600 to-violet-500 text-white font-bold text-xs flex items-center justify-center shrink-0 shadow-2xs">
                                ${initials}
                            </div>
                            <div>
                                <div class="font-semibold text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1 text-[13px]">
                                    <span>${escapeHtml(u.full_name)}</span>
                                </div>
                                <div class="text-[11px] text-zinc-400 dark:text-zinc-500 font-mono">ID: ${escapeHtml(u.id)}</div>
                            </div>
                        </div>
                    </td>
                    <td class="py-3 px-4 text-xs text-zinc-600 dark:text-zinc-300">
                        ${escapeHtml(u.email)}
                    </td>
                    <td class="py-3 px-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-purple-50 text-purple-700 border border-purple-200 dark:bg-purple-950/40 dark:text-purple-300 dark:border-purple-800 shadow-2xs">
                            ${escapeHtml(u.role_name || ROLE_NAMES[u.role_id] || 'No Role Assigned')}
                        </span>
                    </td>
                    <td class="py-3 px-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800 shadow-2xs">
                            ${escapeHtml(u.profile_name || PROFILE_NAMES[u.profile_id] || 'No Profile Assigned')}
                        </span>
                    </td>
                    <td class="py-3 px-4">
                        <div class="flex items-center gap-1.5" onclick="event.stopPropagation()">
                            <select
                                onchange="handleUpdateUserSurpriseMe('${u.id}', this.value)"
                                class="text-xs py-1 px-2.5 rounded-lg border font-medium transition-colors cursor-pointer focus:outline-none ${currentVal === '1' ? 'border-purple-300 bg-purple-50 text-purple-700 dark:bg-purple-950/40 dark:text-purple-300 dark:border-purple-800' : (currentVal === '0' ? 'border-red-300 bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-red-300 dark:border-red-800' : 'border-zinc-200 bg-zinc-50 text-zinc-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300')}"
                                title="Configure Ambient Themes &amp; Glow Customization permission for this user"
                            >
                                <option value="inherit" ${currentVal === 'inherit' ? 'selected' : ''}>Inherit (${profileDefault ? '✨ Permitted' : 'Locked'})</option>
                                <option value="1" ${currentVal === '1' ? 'selected' : ''}>Permitted (✨ On)</option>
                                <option value="0" ${currentVal === '0' ? 'selected' : ''}>Locked (Disabled)</option>
                            </select>
                        </div>
                    </td>
                    <td class="py-3 px-4">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold ${u.status === 'Active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800' : 'bg-zinc-100 text-zinc-600 border border-zinc-200 dark:bg-zinc-800 dark:text-zinc-400 dark:border-zinc-700'}">
                            ${escapeHtml(u.status)}
                        </span>
                    </td>
                    <td class="py-3 px-4 text-right whitespace-nowrap sticky-actions-col sticky right-0 bg-white group-hover:bg-zinc-50 dark:bg-zinc-900 dark:group-hover:bg-zinc-800 z-10 border-l border-zinc-200 dark:border-zinc-800 shadow-[-4px_0_8px_-2px_rgba(0,0,0,0.08)]" onclick="event.stopPropagation()">
                        <div class="inline-flex items-center justify-end gap-1.5">
                            <button
                                type="button"
                                onclick="handleOpenEditUser('${u.id}')"
                                class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-700 border border-zinc-200 dark:border-zinc-700 transition-all cursor-pointer shadow-2xs"
                                title="Edit ${escapeHtml(u.full_name)}"
                            >
                                <i data-lucide="edit-2" class="w-3 h-3"></i>
                                <span>Edit</span>
                            </button>

                            ${u.status === 'Active' && !isCurrentActive ? `
                            <button
                                type="button"
                                onclick="handleOpenOffboardModal('${u.id}')"
                                class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-lg bg-rose-50 dark:bg-rose-950/30 text-rose-600 dark:text-rose-400 hover:bg-rose-100 dark:hover:bg-rose-950/50 border border-rose-200 dark:border-rose-900 transition-all cursor-pointer shadow-2xs"
                                title="Offboard ${escapeHtml(u.full_name)} and reassign owned records"
                            >
                                <i data-lucide="user-x" class="w-3 h-3"></i>
                                <span>Offboard</span>
                            </button>
                            ` : ''}

                            ${isCurrentActive ? `
                            <span
                                class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800 shadow-2xs"
                                title="Currently active session identity"
                            >
                                <i data-lucide="user-check" class="w-3 h-3"></i>
                                <span>Current</span>
                            </span>
                            ` : `
                            <button
                                type="button"
                                onclick="handleLoginAsUser('${u.id}')"
                                ${u.status === 'Inactive' ? 'disabled' : ''}
                                class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-lg ${u.status === 'Inactive' ? 'bg-zinc-100 text-zinc-400 border border-zinc-200 cursor-not-allowed' : 'bg-sky-50 text-sky-700 hover:bg-sky-100 border border-sky-200 dark:bg-sky-950/40 dark:text-sky-300 dark:border-sky-800 cursor-pointer'} transition-all shadow-2xs"
                                title="${u.status === 'Inactive' ? 'Cannot log in as inactive user' : `Log in as ${escapeHtml(u.full_name)} (Impersonation Mode)`}"
                            >
                                <i data-lucide="log-in" class="w-3 h-3"></i>
                                <span>Login</span>
                            </button>
                            `}
                        </div>
                    </td>
                `;
                usersTbody.appendChild(tr);
            });
        }

        // 2. Render Pending Invitations Table
        const invitesTbody = document.getElementById('invitationsTbody');
        const emptyStateEl = document.getElementById('invitationsEmptyState');
        const tableEl = document.getElementById('invitationsTable');

        if (invitesTbody) {
            invitesTbody.innerHTML = '';

            if (invitationsList.length === 0) {
                if (emptyStateEl) emptyStateEl.style.display = 'block';
                if (tableEl) tableEl.style.display = 'none';
            } else {
                if (emptyStateEl) emptyStateEl.style.display = 'none';
                if (tableEl) tableEl.style.display = 'table';

                invitationsList.forEach(inv => {
                    const isExpired = new Date(inv.expires_at) < new Date();
                    const daysLeft = Math.max(0, Math.ceil((new Date(inv.expires_at).getTime() - Date.now()) / (1000 * 60 * 60 * 24)));

                    const tr = document.createElement('tr');
                    tr.className = 'group hover:bg-zinc-50/80 dark:hover:bg-zinc-800/50 transition-colors duration-150 border-b border-zinc-100 dark:border-zinc-800/80 text-zinc-900 dark:text-zinc-100';
                    tr.innerHTML = `
                        <td class="py-3 px-4">
                            <div class="font-semibold text-zinc-900 dark:text-zinc-100 text-[13px]">${escapeHtml(inv.full_name)}</div>
                            <div class="text-[11px] text-zinc-500 dark:text-zinc-400">${escapeHtml(inv.email)}</div>
                        </td>
                        <td class="py-3 px-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-purple-50 text-purple-700 border border-purple-200 dark:bg-purple-950/40 dark:text-purple-300 dark:border-purple-800 shadow-2xs">${escapeHtml(inv.role_name || ROLE_NAMES[inv.role_id] || 'Standard')}</span>
                        </td>
                        <td class="py-3 px-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800 shadow-2xs">${escapeHtml(inv.profile_name || PROFILE_NAMES[inv.profile_id] || 'Standard')}</span>
                        </td>
                        <td class="py-3 px-4 text-xs text-zinc-600 dark:text-zinc-300">
                            ${escapeHtml(inv.inviter_name || 'System Admin')}
                        </td>
                        <td class="py-3 px-4 text-xs text-zinc-500 dark:text-zinc-400 font-mono">
                            ${new Date(inv.created_at).toLocaleDateString()}
                        </td>
                        <td class="py-3 px-4">
                            ${isExpired ? `
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-red-50 text-red-700 border border-red-200 dark:bg-red-950/40 dark:text-red-300 dark:border-red-800">Expired</span>
                            ` : `
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800" title="Expires ${new Date(inv.expires_at).toLocaleString()}">
                                ${daysLeft} days left
                            </span>
                            `}
                        </td>
                        <td class="py-3 px-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold ${inv.status === 'pending' ? 'bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800' : (inv.status === 'accepted' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800' : 'bg-zinc-100 text-zinc-600 border border-zinc-200 dark:bg-zinc-800 dark:text-zinc-400')}">
                                ${escapeHtml(inv.status)}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right whitespace-nowrap sticky-actions-col sticky right-0 bg-white group-hover:bg-zinc-50 dark:bg-zinc-900 dark:group-hover:bg-zinc-800 z-10 border-l border-zinc-200 dark:border-zinc-800 shadow-[-4px_0_8px_-2px_rgba(0,0,0,0.08)]">
                            <div class="inline-flex items-center justify-end gap-1.5">
                                <button
                                    type="button"
                                    onclick="handleCopyInviteLink('${inv.token}', this)"
                                    class="inline-flex items-center gap-1 px-2 py-1 text-xs font-semibold rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-700 border border-zinc-200 dark:border-zinc-700 transition-all cursor-pointer shadow-2xs"
                                    title="Copy direct self-activation URL"
                                >
                                    <i data-lucide="copy" class="w-3 h-3"></i>
                                    <span>Link</span>
                                </button>

                                <button
                                    type="button"
                                    onclick="previewInviteToken('${inv.token}')"
                                    class="inline-flex items-center gap-1 px-2 py-1 text-xs font-semibold rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-700 border border-zinc-200 dark:border-zinc-700 transition-all cursor-pointer shadow-2xs"
                                    title="Preview public activation wizard"
                                >
                                    <span>Preview</span>
                                </button>

                                <button
                                    type="button"
                                    onclick="handleResendInvite('${inv.id}', this)"
                                    class="inline-flex items-center gap-1 px-2 py-1 text-xs font-semibold rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-700 border border-zinc-200 dark:border-zinc-700 transition-all cursor-pointer shadow-2xs"
                                    title="Resend invitation email &amp; refresh 7-day token"
                                >
                                    <i data-lucide="rotate-ccw" class="w-3 h-3"></i>
                                    <span>Resend</span>
                                </button>

                                <button
                                    type="button"
                                    onclick="handleRevokeInvite('${inv.id}')"
                                    class="inline-flex items-center gap-1 px-2 py-1 text-xs font-semibold rounded-lg bg-rose-50 dark:bg-rose-950/30 text-rose-600 dark:text-rose-400 hover:bg-rose-100 border border-rose-200 dark:border-rose-900 transition-all cursor-pointer shadow-2xs"
                                    title="Revoke invitation and release seat license"
                                >
                                    <span>Revoke</span>
                                </button>
                            </div>
                        </td>
                    `;
                    invitesTbody.appendChild(tr);
                });
            }
        }

        initUsersTableResizing();

        if (window.lucide) {
            lucide.createIcons();
        }
    }

    // ============================================================================
    // DYNAMIC COLUMN RESIZING & PERSISTENCE ENGINE
    // ============================================================================
    function initUsersTableResizing() {
        const tableConfigs = [
            { id: 'activeUsersTable', storageKey: 'dealsync_col_widths_users' },
            { id: 'invitationsTable', storageKey: 'dealsync_col_widths_invitations' }
        ];

        tableConfigs.forEach(({ id, storageKey }) => {
            const table = document.getElementById(id);
            if (!table) return;

            let savedWidths = {};
            try {
                savedWidths = JSON.parse(localStorage.getItem(storageKey) || '{}');
            } catch (e) {
                savedWidths = {};
            }

            const headers = table.querySelectorAll('thead th');
            headers.forEach((th, idx) => {
                const colKey = th.getAttribute('data-col') || `col_${idx}`;
                const isActions = colKey === 'actions' || th.classList.contains('sticky-actions-col');

                // 1. Restore saved width or apply default width
                if (savedWidths[colKey]) {
                    const widthVal = savedWidths[colKey];
                    th.style.width = widthVal + 'px';
                    th.style.minWidth = widthVal + 'px';
                } else if (th.getAttribute('data-default-width')) {
                    const defW = th.getAttribute('data-default-width');
                    th.style.width = defW + 'px';
                    th.style.minWidth = defW + 'px';
                }

                // Exclude sticky actions column from resize handles
                if (isActions) return;

                // 2. Attach or retrieve resizer divider handle
                let resizer = th.querySelector('.table-col-resizer');
                if (!resizer) {
                    resizer = document.createElement('div');
                    resizer.className = 'table-col-resizer';
                    resizer.title = 'Drag to resize column (Double-click to reset)';
                    th.appendChild(resizer);
                }

                // 3. Bind drag-to-resize event listeners
                resizer.onmousedown = (e) => {
                    e.preventDefault();
                    e.stopPropagation();

                    const startX = e.clientX;
                    const startWidth = th.offsetWidth;
                    const minWidth = parseInt(th.getAttribute('data-min-width') || '70', 10);

                    resizer.classList.add('is-resizing');
                    document.body.style.cursor = 'col-resize';
                    document.body.style.userSelect = 'none';

                    function onMouseMove(moveEvent) {
                        const diff = moveEvent.clientX - startX;
                        const newWidth = Math.max(minWidth, startWidth + diff);
                        th.style.width = newWidth + 'px';
                        th.style.minWidth = newWidth + 'px';

                        updateTableTotalWidth(table);
                    }

                    function onMouseUp() {
                        resizer.classList.remove('is-resizing');
                        document.body.style.cursor = '';
                        document.body.style.userSelect = '';
                        document.removeEventListener('mousemove', onMouseMove);
                        document.removeEventListener('mouseup', onMouseUp);

                        // Save new width to localStorage
                        const finalWidth = th.offsetWidth;
                        savedWidths[colKey] = finalWidth;
                        try {
                            localStorage.setItem(storageKey, JSON.stringify(savedWidths));
                        } catch (err) {}

                        updateTableTotalWidth(table);
                    }

                    document.addEventListener('mousemove', onMouseMove);
                    document.addEventListener('mouseup', onMouseUp);
                };

                // 4. Double-click on resizer handle resets to default width
                resizer.ondblclick = (e) => {
                    e.preventDefault();
                    e.stopPropagation();

                    const defaultWidth = th.getAttribute('data-default-width');
                    if (defaultWidth) {
                        th.style.width = defaultWidth + 'px';
                        th.style.minWidth = defaultWidth + 'px';
                    } else {
                        th.style.width = '';
                        th.style.minWidth = '';
                    }

                    delete savedWidths[colKey];
                    try {
                        localStorage.setItem(storageKey, JSON.stringify(savedWidths));
                    } catch (err) {}

                    updateTableTotalWidth(table);
                };
            });

            updateTableTotalWidth(table);
        });
    }

    function updateTableTotalWidth(table) {
        if (!table) return;
        const headers = table.querySelectorAll('thead th');
        let sum = 0;
        headers.forEach(th => {
            sum += th.offsetWidth || parseInt(th.style.width || th.getAttribute('data-default-width') || '120', 10);
        });
        if (sum > 0) {
            table.style.minWidth = sum + 'px';
        }
    }

    // ============================================================================
    // USER DETAIL PAGE VIEW
    // ============================================================================
    function openUserDetailView(user) {
        selectedUserForDetail = user;
        const mainView = document.getElementById('mainDirectoryView');
        const detailView = document.getElementById('userDetailContainer');
        if (mainView) mainView.style.display = 'none';
        if (detailView) detailView.style.display = 'block';

        const impersonatedId = localStorage.getItem(STORAGE_IMPERSONATED_KEY);
        const isCurrentActive = impersonatedId === user.id || (!impersonatedId && (user.id === 'usr-101' || user.email === 'alex.morgan@acmecorp.com'));

        const initials = user.full_name ? user.full_name.split(' ').map(n => n[0]).join('').slice(0, 2).toUpperCase() : 'U';
        const avatarBox = document.getElementById('detailAvatarBox');
        if (avatarBox) avatarBox.textContent = initials;

        const fullNameEl = document.getElementById('detailFullName');
        if (fullNameEl) fullNameEl.textContent = user.full_name;

        const statusPill = document.getElementById('detailStatusPill');
        if (statusPill) {
            statusPill.textContent = user.status === 'Active' ? '● Active Employee' : '○ Suspended';
            statusPill.className = `inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold ${user.status === 'Active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800' : 'bg-zinc-100 text-zinc-600 border border-zinc-200 dark:bg-zinc-800 dark:text-zinc-400 dark:border-zinc-700'}`;
        }

        const dot = document.getElementById('detailStatusDot');
        if (dot) {
            dot.className = `absolute bottom-0.5 right-0.5 w-4 h-4 rounded-full border-2 border-white dark:border-zinc-900 ${user.status === 'Active' ? 'bg-emerald-500' : 'bg-zinc-400'}`;
        }

        const adminPill = document.getElementById('detailAdminPill');
        if (adminPill) {
            adminPill.style.display = (user.profile_id === 'prof-admin' || user.role_id === 'role-ceo') ? 'inline-flex' : 'none';
        }

        const roleBadge = document.getElementById('detailRoleBadge');
        if (roleBadge) {
            roleBadge.innerHTML = `<i data-lucide="shield" class="w-3.5 h-3.5 shrink-0"></i> <span>${escapeHtml(user.role_name || ROLE_NAMES[user.role_id] || 'Standard User')}</span>`;
        }

        const profBadge = document.getElementById('detailProfileBadge');
        if (profBadge) {
            profBadge.innerHTML = `<i data-lucide="lock" class="w-3.5 h-3.5 shrink-0"></i> <span>${escapeHtml(user.profile_name || PROFILE_NAMES[user.profile_id] || 'Standard Profile')}</span>`;
        }

        const reportsToEl = document.getElementById('detailReportsToBadge');
        if (reportsToEl) {
            if (user.role_id === 'role-ceo') {
                reportsToEl.innerHTML = `<i data-lucide="building-2" class="w-3.5 h-3.5 text-zinc-400"></i> <span>Reports to: <strong>Board of Directors</strong></span>`;
            } else {
                reportsToEl.innerHTML = `<i data-lucide="building-2" class="w-3.5 h-3.5 text-zinc-400"></i> <span>Reports to: <strong>Alex Morgan (CEO)</strong></span>`;
            }
        }

        const emailLink = document.getElementById('detailEmailLink');
        if (emailLink) {
            emailLink.href = `mailto:${user.email}`;
            emailLink.textContent = user.email;
        }

        const phoneLink = document.getElementById('detailPhoneLink');
        const phoneSpan = document.getElementById('detailPhoneSpan');
        if (phoneLink && phoneSpan) {
            if (user.phone) {
                phoneLink.href = `tel:${user.phone}`;
                phoneLink.textContent = user.phone;
                phoneSpan.style.display = 'inline-flex';
            } else {
                phoneSpan.style.display = 'none';
            }
        }

        const joinedEl = document.getElementById('detailJoinedDate');
        if (joinedEl) {
            joinedEl.textContent = `Joined ${new Date(user.created_at || '2024-10-12').toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' })}`;
        }

        const idBadge = document.getElementById('detailUserIdBadge');
        if (idBadge) idBadge.textContent = `ID: ${user.id}`;

        // KPI Ribbons
        const kpiPipe = document.getElementById('detailKpiPipeline');
        if (kpiPipe) kpiPipe.textContent = `$${(user.pipeline_owned || 380000).toLocaleString()}`;

        const kpiDeals = document.getElementById('detailKpiDeals');
        if (kpiDeals) kpiDeals.textContent = `Across ${user.opps_count || 6} active deals`;

        const kpiAcc = document.getElementById('detailKpiAccounts');
        if (kpiAcc) kpiAcc.textContent = `${user.accounts_count || 8} Accounts`;

        const kpiLeads = document.getElementById('detailKpiLeads');
        if (kpiLeads) kpiLeads.textContent = `${user.leads_count || 14} Leads`;

        const kpiTasks = document.getElementById('detailKpiTasks');
        if (kpiTasks) kpiTasks.textContent = `${user.tasks_count || 5} Tasks`;

        // Overview Tab Details
        const ovName = document.getElementById('ovFullName');
        if (ovName) ovName.textContent = user.full_name;

        const ovEmail = document.getElementById('ovEmail');
        if (ovEmail) ovEmail.textContent = user.email;

        const ovPhone = document.getElementById('ovPhone');
        if (ovPhone) ovPhone.textContent = user.phone || 'None provided';

        const ovRole = document.getElementById('ovRole');
        if (ovRole) ovRole.textContent = user.role_name || ROLE_NAMES[user.role_id] || 'Standard Role';

        const ovProf = document.getElementById('ovProfile');
        if (ovProf) ovProf.textContent = user.profile_name || PROFILE_NAMES[user.profile_id] || 'Standard Profile';

        const ovStatus = document.getElementById('ovStatus');
        if (ovStatus) {
            ovStatus.textContent = user.status;
            ovStatus.className = `inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold ${user.status === 'Active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800' : 'bg-zinc-100 text-zinc-600 border border-zinc-200 dark:bg-zinc-800 dark:text-zinc-400'}`;
        }

        const themesLabel = user.surprise_me_enabled === null || user.surprise_me_enabled === undefined
            ? 'Inherit from Profile (✨ Permitted)'
            : (user.surprise_me_enabled === 1 || user.surprise_me_enabled === true ? 'Permitted (✨ On)' : 'Locked (Disabled)');
        const ovThemes = document.getElementById('ovThemes');
        if (ovThemes) ovThemes.textContent = themesLabel;

        // Permissions Tab Matrix
        const permProf = document.getElementById('permProfileName');
        if (permProf) permProf.textContent = user.profile_name || PROFILE_NAMES[user.profile_id] || 'System Administrator';
        renderPermissionsMatrix(user.profile_id || 'prof-admin');

        // Login CTA State
        const loginBtn = document.getElementById('detailLoginBtn');
        const loginBtnText = document.getElementById('detailLoginBtnText');
        if (loginBtn && loginBtnText) {
            if (isCurrentActive) {
                loginBtn.disabled = true;
                loginBtn.className = 'inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800 cursor-default shadow-2xs';
                loginBtnText.textContent = 'Current Active User';
            } else {
                loginBtn.disabled = user.status === 'Inactive';
                loginBtn.className = `inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg ${user.status === 'Inactive' ? 'bg-zinc-100 text-zinc-400 border border-zinc-200 cursor-not-allowed' : 'bg-sky-50 text-sky-700 hover:bg-sky-100 border border-sky-200 dark:bg-sky-950/40 dark:text-sky-300 dark:border-sky-800 cursor-pointer'} transition-all shadow-2xs`;
                loginBtnText.textContent = 'Login as User';
            }
        }

        switchDetailSubtab('overview');
        if (window.lucide) lucide.createIcons();
    }

    function closeUserDetailView() {
        selectedUserForDetail = null;
        const mainView = document.getElementById('mainDirectoryView');
        const detailView = document.getElementById('userDetailContainer');
        if (detailView) detailView.style.display = 'none';
        if (mainView) mainView.style.display = 'block';
        renderDirectory();
        if (window.lucide) lucide.createIcons();
    }

    function switchDetailSubtab(tab) {
        const tabs = ['overview', 'permissions', 'security', 'audit'];
        tabs.forEach(t => {
            const btn = document.getElementById(`detailSubtabBtn${capitalize(t)}`);
            const pane = document.getElementById(`detailTab${capitalize(t)}`);
            if (btn && pane) {
                if (t === tab) {
                    btn.className = 'inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-lg text-indigo-600 dark:text-indigo-400 bg-indigo-50/70 dark:bg-indigo-950/30 border-b-2 border-indigo-600 dark:border-indigo-400 transition-all cursor-pointer whitespace-nowrap';
                    pane.style.display = 'block';
                } else {
                    btn.className = 'inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-medium rounded-lg text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white hover:bg-zinc-100 dark:hover:bg-zinc-800 border-b-2 border-transparent transition-all cursor-pointer whitespace-nowrap';
                    pane.style.display = 'none';
                }
            }
        });
        if (window.lucide) lucide.createIcons();
    }

    function renderPermissionsMatrix(profileId) {
        const tbody = document.getElementById('permMatrixTbody');
        if (!tbody) return;
        const modules = [
            { mod: 'Leads', r: 1, c: 1, u: 1, d: 1, t: 1, s: 'All' },
            { mod: 'Accounts', r: 1, c: 1, u: 1, d: 1, t: 1, s: 'All' },
            { mod: 'Contacts', r: 1, c: 1, u: 1, d: 1, t: 1, s: 'All' },
            { mod: 'Opportunities', r: 1, c: 1, u: 1, d: 1, t: 0, s: 'All' },
            { mod: 'Purchase Deals', r: 1, c: profileId === 'prof-procurement' || profileId === 'prof-admin' ? 1 : 0, u: 1, d: 0, t: 0, s: 'All' },
            { mod: 'Shipping', r: 1, c: profileId === 'prof-procurement' || profileId === 'prof-admin' ? 1 : 0, u: 1, d: 0, t: 0, s: 'All' },
            { mod: 'Payments', r: 1, c: profileId === 'prof-admin' ? 1 : 0, u: profileId === 'prof-admin' ? 1 : 0, d: 0, t: 0, s: 'All' },
            { mod: 'Wires', r: profileId === 'prof-admin' ? 1 : 0, c: profileId === 'prof-admin' ? 1 : 0, u: profileId === 'prof-admin' ? 1 : 0, d: 0, t: 0, s: 'All' }
        ];

        tbody.innerHTML = modules.map(m => `
            <tr class="hover:bg-zinc-50/80 dark:hover:bg-zinc-800/40 border-b border-zinc-100 dark:border-zinc-800/80">
                <td class="py-2.5 px-3 font-semibold text-zinc-900 dark:text-zinc-100">${m.mod}</td>
                <td class="py-2.5 px-3 text-center">${m.r ? '<span class="text-emerald-600 font-bold">✓</span>' : '<span class="text-zinc-300 dark:text-zinc-600">—</span>'}</td>
                <td class="py-2.5 px-3 text-center">${m.c ? '<span class="text-emerald-600 font-bold">✓</span>' : '<span class="text-zinc-300 dark:text-zinc-600">—</span>'}</td>
                <td class="py-2.5 px-3 text-center">${m.u ? '<span class="text-emerald-600 font-bold">✓</span>' : '<span class="text-zinc-300 dark:text-zinc-600">—</span>'}</td>
                <td class="py-2.5 px-3 text-center">${m.d ? '<span class="text-emerald-600 font-bold">✓</span>' : '<span class="text-zinc-300 dark:text-zinc-600">—</span>'}</td>
                <td class="py-2.5 px-3 text-center">${m.t ? '<span class="text-emerald-600 font-bold">✓</span>' : '<span class="text-zinc-300 dark:text-zinc-600">—</span>'}</td>
                <td class="py-2.5 px-3"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-purple-50 text-purple-700 border border-purple-200 dark:bg-purple-950/40 dark:text-purple-300 dark:border-purple-800">${m.s}</span></td>
            </tr>
        `).join('');
    }

    function detailTriggerLogin() {
        if (selectedUserForDetail) {
            handleLoginAsUser(selectedUserForDetail.id);
        }
    }

    function detailTriggerEdit() {
        if (selectedUserForDetail) {
            handleOpenEditUser(selectedUserForDetail.id);
        }
    }

    // ============================================================================
    // IMPERSONATION (handleLoginAsUser)
    // ============================================================================
    async function handleLoginAsUser(userId) {
        const user = usersList.find(u => String(u.id) === String(userId));
        if (!user) return;

        if (user.status === 'Inactive') {
            alert(`Cannot log in as "${user.full_name}" because this user account is Inactive.`);
            return;
        }

        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        try {
            await fetch(`/admin/users-directory/user/${userId}/impersonate`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf
                }
            });
        } catch (e) {
            console.error('Impersonate error:', e);
        }

        localStorage.setItem(STORAGE_IMPERSONATED_KEY, String(user.id));
        showFeedback(`Switched active session identity to "${user.full_name}"!`);
        setTimeout(() => {
            renderDirectory();
            if (selectedUserForDetail && String(selectedUserForDetail.id) === String(user.id)) {
                openUserDetailView(user);
            }
        }, 600);
    }

    // ============================================================================
    // AMBIENT THEMES IMMEDIATE UPDATE
    // ============================================================================
    async function handleUpdateUserSurpriseMe(userId, val) {
        const u = usersList.find(item => String(item.id) === String(userId));
        if (!u) return;

        u.surprise_me_enabled = val === 'inherit' ? null : (val === '1' ? 1 : 0);
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        try {
            await fetch(`/admin/users-directory/user/${userId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf
                },
                body: JSON.stringify({ surprise_me_enabled: u.surprise_me_enabled })
            });
        } catch (e) {
            console.error('Surprise me update error:', e);
        }

        persistUsers();
        showFeedback(`Ambient themes permission for "${u.full_name}" updated!`);
        renderDirectory();
    }

    // ============================================================================
    // CREATE / EDIT USER MODAL
    // ============================================================================
    function openCreateUserModal() {
        const modal = document.getElementById('userModal');
        if (!modal) return;
        document.getElementById('modalUserId').value = '';
        document.getElementById('userModalTitle').textContent = 'Create New CRM User';
        document.getElementById('modalSubmitBtn').textContent = 'Create User';

        document.getElementById('modalFullName').value = '';
        document.getElementById('modalEmail').value = '';
        document.getElementById('modalRoleId').selectedIndex = 0;
        document.getElementById('modalProfileId').selectedIndex = 0;
        document.getElementById('modalStatus').value = 'Active';
        document.getElementById('modalPhone').value = '';
        document.getElementById('modalSurpriseMe').value = 'inherit';

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function handleOpenEditUser(userId) {
        const modal = document.getElementById('userModal');
        const u = usersList.find(item => String(item.id) === String(userId));
        if (!modal || !u) return;

        document.getElementById('modalUserId').value = u.id;
        document.getElementById('userModalTitle').textContent = `Edit User: ${u.full_name}`;
        document.getElementById('modalSubmitBtn').textContent = 'Save Changes';

        document.getElementById('modalFullName').value = u.full_name || '';
        document.getElementById('modalEmail').value = u.email || '';
        document.getElementById('modalRoleId').value = u.role_id || 'role-sales-rep';
        document.getElementById('modalProfileId').value = u.profile_id || 'prof-sales';
        document.getElementById('modalStatus').value = u.status || 'Active';
        document.getElementById('modalPhone').value = u.phone || '';

        const themesVal = u.surprise_me_enabled === null || u.surprise_me_enabled === undefined
            ? 'inherit'
            : (u.surprise_me_enabled === 1 || u.surprise_me_enabled === true ? '1' : '0');
        document.getElementById('modalSurpriseMe').value = themesVal;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeUserModal() {
        const modal = document.getElementById('userModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    async function saveUserModal(e) {
        e.preventDefault();
        const id = document.getElementById('modalUserId').value;
        const fullName = document.getElementById('modalFullName').value.trim();
        const email = document.getElementById('modalEmail').value.trim();
        const roleId = document.getElementById('modalRoleId').value;
        const profileId = document.getElementById('modalProfileId').value;
        const status = document.getElementById('modalStatus').value;
        const phone = document.getElementById('modalPhone').value.trim();
        const surpriseMe = document.getElementById('modalSurpriseMe').value;
        const surpriseMeVal = surpriseMe === 'inherit' ? null : (surpriseMe === '1' ? 1 : 0);

        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const payload = {
            full_name: fullName,
            email: email,
            role_id: roleId,
            profile_id: profileId,
            status: status,
            phone: phone,
            surprise_me_enabled: surpriseMeVal
        };

        try {
            if (id) {
                await fetch(`/admin/users-directory/user/${id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf
                    },
                    body: JSON.stringify(payload)
                });

                const u = usersList.find(item => String(item.id) === String(id));
                if (u) {
                    u.full_name = fullName;
                    u.email = email;
                    u.role_id = roleId;
                    u.role_name = ROLE_NAMES[roleId] || u.role_name || 'Role';
                    u.profile_id = profileId;
                    u.profile_name = PROFILE_NAMES[profileId] || u.profile_name || 'Profile';
                    u.status = status;
                    u.phone = phone;
                    u.surprise_me_enabled = surpriseMeVal;
                    if (selectedUserForDetail && String(selectedUserForDetail.id) === String(id)) {
                        openUserDetailView(u);
                    }
                }
                showFeedback(`User "${fullName}" updated successfully!`);
            } else {
                const res = await fetch(`/api/settings/users`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf
                    },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                const created = data.user || {
                    id: `usr-${Date.now().toString().slice(-4)}`,
                    full_name: fullName,
                    email: email,
                    role_id: roleId,
                    role_name: ROLE_NAMES[roleId] || 'Sales Executive',
                    profile_id: profileId,
                    profile_name: PROFILE_NAMES[profileId] || 'Standard Profile',
                    status: status,
                    phone: phone,
                    surprise_me_enabled: surpriseMeVal,
                    pipeline_owned: 0,
                    opps_count: 0,
                    accounts_count: 0,
                    leads_count: 0,
                    tasks_count: 0
                };
                usersList.push(created);
                showFeedback(`User "${fullName}" created successfully!`);
            }
        } catch (err) {
            console.error('Error saving user:', err);
        }

        persistUsers();
        closeUserModal();
        updateQuotaGauge();
        renderDirectory();
    }

    // ============================================================================
    // INVITE TEAM MEMBER MODAL
    // ============================================================================
    function openInviteModal() {
        const activeCount = usersList.filter(u => u.status === 'Active').length;
        const pendingCount = invitationsList.filter(i => i.status === 'pending').length;
        const available = Math.max(0, maxSeats - (activeCount + pendingCount));

        if (available <= 0) {
            alert('Your organization license seat limit has been reached! Please add more seats first.');
            openAddSeatsModal();
            return;
        }

        const modal = document.getElementById('inviteModal');
        if (!modal) return;

        document.getElementById('inviteForm').style.display = 'block';
        document.getElementById('inviteSuccessScreen').style.display = 'none';

        document.getElementById('inviteEmail').value = '';
        document.getElementById('inviteFullName').value = '';
        document.getElementById('inviteWelcomeNote').value = '';
        document.getElementById('inviteModalRemainingSeats').textContent = `${available} Seats Remaining`;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeInviteModal() {
        const modal = document.getElementById('inviteModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
        renderDirectory();
        updateQuotaGauge();
    }

    async function submitInviteForm(e) {
        e.preventDefault();
        const email = document.getElementById('inviteEmail').value.trim();
        const fullName = document.getElementById('inviteFullName').value.trim();
        const roleId = document.getElementById('inviteRole').value;
        const profileId = document.getElementById('inviteProfile').value;
        const welcomeNote = document.getElementById('inviteWelcomeNote')?.value.trim() || '';

        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        let activationUrl = '';
        let token = '';

        try {
            const res = await fetch('/admin/users-directory/invite', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf
                },
                body: JSON.stringify({
                    email,
                    full_name: fullName,
                    role_id: roleId,
                    profile_id: profileId,
                    welcome_note: welcomeNote
                })
            });
            const resp = await res.json();
            if (!res.ok || !resp.success) {
                alert(resp.message || 'Failed to send invitation');
                return;
            }

            token = resp.data.token;
            activationUrl = resp.data.activation_url;

            const newInvite = {
                id: String(resp.data.id || resp.data.invitation?.id),
                full_name: fullName,
                email: email,
                role_id: roleId,
                role_name: ROLE_NAMES[roleId] || resp.data.invitation?.role?.name || 'Role',
                profile_id: profileId,
                profile_name: PROFILE_NAMES[profileId] || resp.data.invitation?.profile?.name || 'Profile',
                inviter_name: 'Alex Morgan',
                token: token,
                created_at: new Date().toISOString(),
                expires_at: resp.data.expires_at || new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString(),
                status: 'pending'
            };

            invitationsList.unshift(newInvite);
            persistInvitations();
            updateQuotaGauge();
        } catch (err) {
            console.error('Invitation send error:', err);
            token = 'tok-' + Math.random().toString(36).substring(2, 11) + Date.now().toString(36);
            activationUrl = `${window.location.origin}/activate?token=${token}`;
        }

        document.getElementById('successInvitedEmail').textContent = email;
        document.getElementById('successActivationUrl').value = activationUrl;

        document.getElementById('inviteForm').style.display = 'none';
        document.getElementById('inviteSuccessScreen').style.display = 'block';
        showFeedback(`Invitation sent to ${email}!`);
        if (window.lucide) lucide.createIcons();
    }

    function copyActivationUrl() {
        const url = document.getElementById('successActivationUrl').value;
        navigator.clipboard.writeText(url).then(() => {
            const btnText = document.getElementById('copyUrlBtnText');
            if (btnText) btnText.textContent = 'Copied!';
            setTimeout(() => {
                const btn = document.getElementById('copyUrlBtnText');
                if (btn) btn.textContent = 'Copy Link';
            }, 2500);
        });
    }

    function handleCopyInviteLink(token, btn) {
        const url = `${window.location.origin}/activate?token=${token}`;
        navigator.clipboard.writeText(url).then(() => {
            const span = btn.querySelector('span');
            if (span) span.textContent = 'Copied';
            btn.classList.add('text-emerald-600');
            setTimeout(() => {
                if (span) span.textContent = 'Link';
                btn.classList.remove('text-emerald-600');
            }, 2500);
        });
    }

    function previewInviteToken(token) {
        const inv = invitationsList.find(i => i.token === token);
        if (!inv) return;

        document.getElementById('previewCandidateName').textContent = inv.full_name;
        document.getElementById('previewCandidateEmail').textContent = inv.email;
        const modal = document.getElementById('previewInviteModal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    }

    function closePreviewInviteModal() {
        const modal = document.getElementById('previewInviteModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    async function handleResendInvite(invId, btn) {
        const inv = invitationsList.find(i => String(i.id) === String(invId));
        if (!inv) return;

        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        try {
            const res = await fetch(`/admin/users-directory/invite/${invId}/resend`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf
                }
            });
            const data = await res.json();
            if (data.data?.token) {
                inv.token = data.data.token;
                inv.expires_at = data.data.expires_at;
            }
        } catch (err) {
            console.error('Resend error:', err);
            inv.expires_at = new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString();
            inv.token = 'tok-' + Math.random().toString(36).substring(2, 11);
        }

        persistInvitations();
        const span = btn.querySelector('span');
        if (span) span.textContent = 'Sent!';
        showFeedback(`Invitation email resent to ${inv.email}!`);
        setTimeout(() => {
            if (span) span.textContent = 'Resend';
            renderDirectory();
        }, 1500);
    }

    async function handleRevokeInvite(invId) {
        const inv = invitationsList.find(i => String(i.id) === String(invId));
        if (!inv) return;

        if (!confirm(`Are you sure you want to revoke the invitation for ${inv.email}? This will immediately release their allocated seat.`)) {
            return;
        }

        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        try {
            await fetch(`/admin/users-directory/invite/${invId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf
                }
            });
        } catch (err) {
            console.error('Revoke error:', err);
        }

        invitationsList = invitationsList.filter(i => String(i.id) !== String(invId));
        persistInvitations();
        updateQuotaGauge();
        renderDirectory();
        showFeedback(`Invitation for ${inv.email} revoked and seat reclaimed.`);
    }

    // ============================================================================
    // USER OFFBOARDING WIZARD
    // ============================================================================
    async function handleOpenOffboardModal(userId) {
        const user = usersList.find(u => String(u.id) === String(userId));
        if (!user) return;

        offboardTargetUser = user;
        document.getElementById('offboardTargetName').textContent = `${user.full_name} (${user.email})`;

        let leads = user.leads_count || 14;
        let accounts = user.accounts_count || 8;
        let opps = user.opps_count || 6;
        let oppsVal = user.pipeline_owned || 380000;
        let tasks = user.tasks_count || 5;

        try {
            const res = await fetch(`/admin/users-directory/summary/${userId}`);
            const json = await res.json();
            if (json.success && json.data) {
                leads = json.data.leads;
                accounts = json.data.accounts;
                opps = json.data.opportunities;
                oppsVal = json.data.opportunity_value;
                tasks = json.data.tasks;
            }
        } catch (e) {
            // fallback to memory metrics
        }

        const total = leads + accounts + opps + tasks;

        document.getElementById('offboardLeadsCount').textContent = leads;
        document.getElementById('offboardAccountsCount').textContent = accounts;
        document.getElementById('offboardOppsCount').textContent = `${opps} ($${Number(oppsVal).toLocaleString()})`;
        document.getElementById('offboardTasksCount').textContent = tasks;
        document.getElementById('offboardTotalCount').textContent = `${total} records`;

        const eligible = usersList.filter(u => String(u.id) !== String(user.id) && u.status === 'Active');
        const selectEl = document.getElementById('offboardDestinationUser');
        selectEl.innerHTML = eligible.map(u => `
            <option value="${u.id}">${escapeHtml(u.full_name)} (${escapeHtml(u.email)}) — ${escapeHtml(u.role_name || 'Active Member')}</option>
        `).join('') + '<option value="">Do not reassign (Keep unassigned / historical)</option>';

        const modal = document.getElementById('offboardModal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
        if (window.lucide) lucide.createIcons();
    }

    function closeOffboardModal() {
        offboardTargetUser = null;
        const modal = document.getElementById('offboardModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    async function executeOffboarding() {
        if (!offboardTargetUser) return;

        const destId = document.getElementById('offboardDestinationUser').value;
        const destUser = usersList.find(u => String(u.id) === String(destId));
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        try {
            await fetch(`/admin/users-directory/offboard/${offboardTargetUser.id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf
                },
                body: JSON.stringify({ reassign_to_user_id: destId })
            });
        } catch (e) {
            console.error('Offboard error:', e);
        }

        if (destUser) {
            destUser.leads_count = (destUser.leads_count || 0) + (offboardTargetUser.leads_count || 0);
            destUser.accounts_count = (destUser.accounts_count || 0) + (offboardTargetUser.accounts_count || 0);
            destUser.opps_count = (destUser.opps_count || 0) + (offboardTargetUser.opps_count || 0);
            destUser.pipeline_owned = (destUser.pipeline_owned || 0) + (offboardTargetUser.pipeline_owned || 0);
            destUser.tasks_count = (destUser.tasks_count || 0) + (offboardTargetUser.tasks_count || 0);
        }

        offboardTargetUser.status = 'Inactive';
        offboardTargetUser.leads_count = 0;
        offboardTargetUser.accounts_count = 0;
        offboardTargetUser.opps_count = 0;
        offboardTargetUser.pipeline_owned = 0;
        offboardTargetUser.tasks_count = 0;

        persistUsers();
        closeOffboardModal();
        updateQuotaGauge();
        renderDirectory();

        const reassignedMsg = destUser ? ` and records reassigned to ${destUser.full_name}` : '';
        showFeedback(`User ${offboardTargetUser.full_name} offboarded successfully${reassignedMsg}. 1 seat reclaimed!`);
    }

    // ============================================================================
    // ADD SEATS MODAL
    // ============================================================================
    function openAddSeatsModal() {
        const modal = document.getElementById('addSeatsModal');
        if (!modal) return;
        document.getElementById('currentSeatsLabel').textContent = `${maxSeats} seats`;
        document.getElementById('newSeatsCountInput').value = maxSeats + 2;
        updateAddSeatsCost();
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeAddSeatsModal() {
        const modal = document.getElementById('addSeatsModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    function adjustSeatCount(delta) {
        const input = document.getElementById('newSeatsCountInput');
        const cur = parseInt(input.value, 10) || maxSeats;
        const next = Math.max(maxSeats + 1, cur + delta);
        input.value = next;
        updateAddSeatsCost();
    }

    function updateAddSeatsCost() {
        const input = document.getElementById('newSeatsCountInput');
        const val = parseInt(input.value, 10) || maxSeats;
        const diff = Math.max(0, val - maxSeats);
        const cost = diff * 39;
        const costEl = document.getElementById('seatsCostIncrease');
        if (costEl) costEl.textContent = `+$${cost.toFixed(2)} / mo`;
    }

    async function confirmAddSeats() {
        const input = document.getElementById('newSeatsCountInput');
        const newTotal = parseInt(input.value, 10);
        if (isNaN(newTotal) || newTotal <= maxSeats) {
            alert('Please choose a seat count higher than your current allocation.');
            return;
        }

        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        try {
            await fetch('/admin/users-directory/add-seats', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf
                },
                body: JSON.stringify({ new_seats_count: newTotal })
            });
        } catch (e) {
            console.error('Add seats error:', e);
        }

        maxSeats = newTotal;
        persistQuota();
        closeAddSeatsModal();
        updateQuotaGauge();
        showFeedback(`Tenant seat license limit updated to ${maxSeats} seats successfully!`);
    }

    // ============================================================================
    // FEEDBACK BANNER
    // ============================================================================
    function showFeedback(msg) {
        const banner = document.getElementById('feedbackBanner');
        const textEl = document.getElementById('feedbackBannerText');
        if (!banner || !textEl) return;

        textEl.textContent = msg;
        banner.classList.remove('hidden');
        banner.classList.add('flex');

        if (feedbackTimer) clearTimeout(feedbackTimer);
        feedbackTimer = setTimeout(() => {
            banner.classList.add('hidden');
            banner.classList.remove('flex');
        }, 4000);

        if (window.lucide) lucide.createIcons();
    }

    function capitalize(s) {
        if (!s) return '';
        return s.charAt(0).toUpperCase() + s.slice(1);
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

    // Expose all public handlers on window
    window.initUsersDirectory = initUsersDirectory;
    window.setUserDirectoryTab = setUserDirectoryTab;
    window.openUserDetailView = openUserDetailView;
    window.closeUserDetailView = closeUserDetailView;
    window.switchDetailSubtab = switchDetailSubtab;
    window.detailTriggerLogin = detailTriggerLogin;
    window.detailTriggerEdit = detailTriggerEdit;
    window.handleLoginAsUser = handleLoginAsUser;
    window.handleUpdateUserSurpriseMe = handleUpdateUserSurpriseMe;
    window.openCreateUserModal = openCreateUserModal;
    window.handleOpenEditUser = handleOpenEditUser;
    window.closeUserModal = closeUserModal;
    window.saveUserModal = saveUserModal;
    window.openInviteModal = openInviteModal;
    window.closeInviteModal = closeInviteModal;
    window.submitInviteForm = submitInviteForm;
    window.copyActivationUrl = copyActivationUrl;
    window.handleCopyInviteLink = handleCopyInviteLink;
    window.previewInviteToken = previewInviteToken;
    window.closePreviewInviteModal = closePreviewInviteModal;
    window.handleResendInvite = handleResendInvite;
    window.handleRevokeInvite = handleRevokeInvite;
    window.handleOpenOffboardModal = handleOpenOffboardModal;
    window.closeOffboardModal = closeOffboardModal;
    window.executeOffboarding = executeOffboarding;
    window.openAddSeatsModal = openAddSeatsModal;
    window.closeAddSeatsModal = closeAddSeatsModal;
    window.adjustSeatCount = adjustSeatCount;
    window.updateAddSeatsCost = updateAddSeatsCost;
    window.confirmAddSeats = confirmAddSeats;
    window.showFeedback = showFeedback;
    window.initUsersTableResizing = initUsersTableResizing;

    // Auto-run if DOM is already ready (e.g. initial full load or SPA script swap)
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initUsersDirectory);
    } else {
        initUsersDirectory();
    }
})();
