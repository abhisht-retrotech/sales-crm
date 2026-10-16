/**
 * DealSync - Record-Level Rights Matrix (CRUD & Object Visibility)
 * 1-to-1 Parity with React SettingsView.tsx (Tab 3: Record-Level Rights Matrix)
 * Modern Tailwind CSS & Isolated Execution Scope (No Global Variable Clashes)
 */
(function () {
    'use strict';

    const CORE_MODULE_IDS = [
        'dashboard',
        'leads',
        'accounts',
        'contacts',
        'opportunities',
        'tasks',
        'emails',
        'calendar'
    ];

    const EXTENDED_MODULE_IDS = [
        'purchase_deals',
        'shipping',
        'documents',
        'payments',
        'wires',
        'users'
    ];

    const STANDARD_MODULES = [
        { id: 'dashboard', name: 'Dashboard', category: 'core', icon: 'layout-dashboard', desc: 'Executive KPIs, analytics charts, and operational widgets' },
        { id: 'leads', name: 'Leads', category: 'core', icon: 'users-2', desc: 'Inbound prospects, qualification stages, and conversion engine' },
        { id: 'accounts', name: 'Accounts', category: 'core', icon: 'building-2', desc: 'Companies, organizations, customers, and vendor partners' },
        { id: 'contacts', name: 'Contacts', category: 'core', icon: 'contact', desc: 'Individual stakeholder contacts and communication directory' },
        { id: 'opportunities', name: 'Opportunities (Sales Pipeline)', category: 'core', icon: 'trending-up', desc: 'Sales pipeline, forecast stages, and deal values' },
        { id: 'tasks', name: 'Tasks', category: 'core', icon: 'check-square', desc: 'Activities, reminders, follow-ups, and recurring tasks' },
        { id: 'emails', name: 'Emails', category: 'core', icon: 'mail', desc: 'Unified inbox, compose, templates, and outbound correspondence' },
        { id: 'calendar', name: 'Calendar', category: 'core', icon: 'calendar', desc: 'Meetings, customer bookings, sync feeds, and scheduling' },
        { id: 'purchase_deals', name: 'Deals (Purchase & Procurement)', category: 'extended', icon: 'shopping-cart', desc: 'Procurement orders, vendor agreements, and PO commitments' },
        { id: 'shipping', name: 'Shipping & Logistics', category: 'extended', icon: 'truck', desc: 'Freight dispatch and customs tracking' },
        { id: 'documents', name: 'Billing & Ledger', category: 'extended', icon: 'file-text', desc: 'Financial invoices, payment receipts, and ledger audit' },
        { id: 'payments', name: 'Payments & Invoicing (AR / AP)', category: 'extended', icon: 'credit-card', desc: 'Billing records, accounts receivable, and collections' },
        { id: 'wires', name: 'Wire Received & Bank Reconciliation', category: 'extended', icon: 'landmark', desc: 'Bank wire transfers, UTR confirmations, and reconciliation' },
        { id: 'users', name: 'Users (Active User Directory)', category: 'extended', icon: 'shield', desc: 'System user directory and employee profile management' }
    ];

    // Local State (Scoped strictly within this IIFE)
    let state = {
        profiles: [],
        selectedProfileId: '',
        customObjects: [],
        recordPerms: [],
        filter: 'all', // 'all', 'core', 'extended', 'custom', 'full_crud', 'read_only'
        searchQuery: '',
        csrfToken: '',
        saving: false
    };

    let feedbackTimeout = null;

    /**
     * Initialize Record Rights View
     */
    function initRecordRights() {
        const root = document.getElementById('recordRightsContainer');
        if (!root) return; // Not on Record Rights page

        const rawData = window.INITIAL_RECORD_RIGHTS_DATA || {};
        state.profiles = Array.isArray(rawData.profiles) && rawData.profiles.length > 0
            ? [...rawData.profiles]
            : [
                { id: 'prof-admin', name: 'System Administrator', is_admin: 1, bulk_email_daily_limit: 1000, can_send_bulk_email: true },
                { id: 'prof-sales', name: 'Standard Sales Representative', is_admin: 0, bulk_email_daily_limit: 500, can_send_bulk_email: false }
            ];

        state.selectedProfileId = rawData.selectedProfileId || (state.profiles.length > 0 ? String(state.profiles[0].id) : 'prof-admin');
        state.customObjects = Array.isArray(rawData.customObjects) ? [...rawData.customObjects] : [];
        state.csrfToken = rawData.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content || '';

        if (Array.isArray(rawData.initialPermissions) && rawData.initialPermissions.length > 0) {
            state.recordPerms = [...rawData.initialPermissions];
        } else {
            state.recordPerms = [];
        }

        renderProfileSelector();
        renderGovernanceBadge();
        renderBulkEmailSection();
        renderCustomPresetOptions();

        if (state.recordPerms.length === 0) {
            loadPermissionsForProfile(state.selectedProfileId);
        } else {
            renderMatrixRows();
            updateKpis();
        }

        bindModals();
    }

    /**
     * Render Profile Dropdown
     */
    function renderProfileSelector() {
        const select = document.getElementById('selectedProfileSelect');
        if (!select) return;

        select.innerHTML = state.profiles.map(p => `
            <option value="${p.id}" ${String(p.id) === String(state.selectedProfileId) ? 'selected' : ''}>
                ${escapeHtml(p.name)}
            </option>
        `).join('');
    }

    /**
     * Render Governance Badge
     */
    function renderGovernanceBadge() {
        const badgeEl = document.getElementById('profileGovernanceBadge');
        if (!badgeEl) return;

        const cur = state.profiles.find(p => String(p.id) === String(state.selectedProfileId));
        const isAdmin = cur && (Boolean(cur.is_admin) || String(cur.id) === 'prof-admin');

        if (isAdmin) {
            badgeEl.innerHTML = `
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 animate-pulse"></span>
                    👑 Full Organization Administrator (Full Access)
                </span>
            `;
        } else {
            badgeEl.innerHTML = `
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700">
                    <span class="w-1.5 h-1.5 rounded-full bg-zinc-400"></span>
                    Custom Access Profile
                </span>
            `;
        }
    }

    /**
     * Render Bulk Email Authority Card (SettingsView.tsx lines 2980-3056)
     */
    function renderBulkEmailSection() {
        const limitInput = document.getElementById('bulkEmailDailyLimitInput');
        const allowCheckbox = document.getElementById('allowBulkSendingCheckbox');
        const cur = state.profiles.find(p => String(p.id) === String(state.selectedProfileId));

        if (limitInput) {
            limitInput.value = cur?.bulk_email_daily_limit ?? 500;
        }
        if (allowCheckbox) {
            allowCheckbox.checked = Boolean(cur?.can_send_bulk_email);
        }
    }

    /**
     * Handle Profile Change from Selector
     */
    async function handleProfileChange(newProfileId) {
        state.selectedProfileId = String(newProfileId);
        renderGovernanceBadge();
        renderBulkEmailSection();
        await loadPermissionsForProfile(state.selectedProfileId);
    }

    /**
     * Fetch permissions for profile via API
     */
    async function loadPermissionsForProfile(profileId) {
        const tbody = document.getElementById('matrixTableBody');
        if (tbody) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="9" class="py-12 text-center text-zinc-400 dark:text-zinc-500">
                        <div class="inline-flex items-center gap-2 font-medium text-sm">
                            <svg class="animate-spin h-5 w-5 text-indigo-600 dark:text-indigo-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span>Loading profile permissions...</span>
                        </div>
                    </td>
                </tr>
            `;
        }

        try {
            const res = await fetch(`/api/settings/permissions/${encodeURIComponent(profileId)}`, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();
            if (data && Array.isArray(data.recordPermissions)) {
                state.recordPerms = data.recordPermissions;
            } else {
                state.recordPerms = getDefaultPerms(profileId);
            }
        } catch (e) {
            console.warn('Failed to load permissions via API, falling back to defaults:', e);
            state.recordPerms = getDefaultPerms(profileId);
        }

        renderMatrixRows();
        updateKpis();
    }

    /**
     * Build full list of available modules (Core + Extended + Custom Objects)
     */
    function getAllModules() {
        const map = new Map();

        // 1. Standard Modules
        STANDARD_MODULES.forEach(m => {
            map.set(m.id, {
                id: m.id,
                name: m.name,
                category: m.category,
                icon: m.icon,
                desc: m.desc,
                is_custom: false
            });
        });

        // 2. Custom Objects
        state.customObjects.forEach(co => {
            const key = co.api_name ? `obj_${co.api_name}` : `co_${co.id}`;
            map.set(key, {
                id: key,
                name: `${co.label} (Custom Object)`,
                category: 'custom',
                icon: 'box',
                desc: co.description || `Custom object schema: ${co.api_name}`,
                is_custom: true
            });
        });

        // 3. Existing permissions that might be custom
        state.recordPerms.forEach(rp => {
            if (!map.has(rp.module)) {
                const isCustom = rp.module.startsWith('obj_') || !STANDARD_MODULES.some(s => s.id === rp.module);
                map.set(rp.module, {
                    id: rp.module,
                    name: rp.module.replace('obj_', '').replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()),
                    category: isCustom ? 'custom' : 'extended',
                    icon: isCustom ? 'box' : 'layers',
                    desc: isCustom ? 'Registered custom entity' : 'CRM Module',
                    is_custom: isCustom
                });
            }
        });

        return Array.from(map.values());
    }

    /**
     * Generate standard fallback permissions based on profile type
     */
    function getDefaultPerms(profileId) {
        const all = getAllModules();
        const cur = state.profiles.find(p => String(p.id) === String(profileId));
        const isAdmin = cur ? Boolean(cur.is_admin) : profileId === 'prof-admin';

        return all.map(mod => {
            const isCore = CORE_MODULE_IDS.includes(mod.id);
            if (isAdmin) {
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
            return {
                module: mod.id,
                can_create: isCore ? 1 : 0,
                can_read: isCore ? 1 : 0,
                can_update: isCore ? 1 : 0,
                can_delete: 0,
                can_transfer: 0,
                record_scope: 'All'
            };
        });
    }

    /**
     * Filter modules based on filter pill & search query
     */
    function getFilteredModules() {
        let list = getAllModules();

        // Search Query
        if (state.searchQuery.trim()) {
            const q = state.searchQuery.toLowerCase().trim();
            list = list.filter(m => m.name.toLowerCase().includes(q) || m.id.toLowerCase().includes(q) || (m.desc && m.desc.toLowerCase().includes(q)));
        }

        // Active Filter Pill
        if (state.filter === 'core') {
            list = list.filter(m => CORE_MODULE_IDS.includes(m.id));
        } else if (state.filter === 'extended') {
            list = list.filter(m => EXTENDED_MODULE_IDS.includes(m.id));
        } else if (state.filter === 'custom') {
            list = list.filter(m => m.is_custom);
        } else if (state.filter === 'full_crud') {
            list = list.filter(m => {
                const perm = state.recordPerms.find(p => p.module === m.id);
                return perm && perm.can_create && perm.can_read && perm.can_update && perm.can_delete;
            });
        } else if (state.filter === 'read_only') {
            list = list.filter(m => {
                const perm = state.recordPerms.find(p => p.module === m.id);
                return perm && perm.can_read && !perm.can_create && !perm.can_update && !perm.can_delete;
            });
        }

        return list;
    }

    /**
     * Render the Matrix Rows inside #matrixTableBody
     */
    function renderMatrixRows() {
        const tbody = document.getElementById('matrixTableBody');
        if (!tbody) return;

        const visibleModules = getFilteredModules();
        const curProfile = state.profiles.find(p => String(p.id) === String(state.selectedProfileId));
        const isAdmin = curProfile && (Boolean(curProfile.is_admin) || String(curProfile.id) === 'prof-admin');

        if (visibleModules.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="9" class="py-12 text-center text-zinc-400 dark:text-zinc-500">
                        <i data-lucide="shield-alert" class="w-8 h-8 mx-auto mb-2 opacity-40"></i>
                        <p class="font-medium text-sm text-zinc-600 dark:text-zinc-400">No CRM objects match the selected filter</p>
                        <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-1">Try switching to "All Objects" or clearing the search query.</p>
                        <button type="button" onclick="window.clearRecordRightsFilters()" class="mt-3 px-3 py-1.5 text-xs font-semibold rounded-lg bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 transition">
                            Reset Filters
                        </button>
                    </td>
                </tr>
            `;
            if (window.lucide) lucide.createIcons();
            return;
        }

        tbody.innerHTML = visibleModules.map(mod => {
            const perm = state.recordPerms.find(p => p.module === mod.id);
            const isCore = CORE_MODULE_IDS.includes(mod.id);
            const canRead = perm ? Boolean(perm.can_read && perm.can_read !== 0) : isCore;
            const canCreate = perm ? Boolean(perm.can_create) : canRead;
            const canUpdate = perm ? Boolean(perm.can_update) : canRead;
            const canDelete = perm ? Boolean(perm.can_delete) : false;
            const canTransfer = perm ? (perm.can_transfer !== undefined ? Boolean(perm.can_transfer) : isAdmin) : isAdmin;
            const scope = perm?.record_scope || 'All';

            const rowDimClass = canRead
                ? 'hover:bg-zinc-50/70 dark:hover:bg-zinc-800/40 transition-colors'
                : 'opacity-65 bg-zinc-50/60 dark:bg-zinc-900/60';

            return `
                <tr id="row-${mod.id}" class="border-b border-zinc-200/80 dark:border-zinc-800/80 ${rowDimClass}">
                    <!-- Module Title & Category Badge -->
                    <td class="py-3 px-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 ${
                                mod.is_custom
                                    ? 'bg-purple-100 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400'
                                    : (isCore ? 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400')
                            }">
                                <i data-lucide="${mod.icon || 'layers'}" class="w-4 h-4"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <strong class="text-xs font-semibold text-zinc-900 dark:text-zinc-100">${escapeHtml(mod.name)}</strong>
                                    ${isCore ? `
                                        <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-semibold bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                            Core Default
                                        </span>
                                    ` : (mod.is_custom ? `
                                        <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-semibold bg-purple-50 dark:bg-purple-950/50 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800">
                                            Custom Object
                                        </span>
                                    ` : `
                                        <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-medium bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700">
                                            Extended
                                        </span>
                                    `)}
                                </div>
                                ${mod.desc ? `<div class="text-[11px] text-zinc-500 dark:text-zinc-400 truncate max-w-xs mt-0.5">${escapeHtml(mod.desc)}</div>` : ''}
                                <div class="text-[10px] font-mono text-zinc-400 dark:text-zinc-500 mt-0.5">${escapeHtml(mod.id)}</div>
                            </div>
                        </div>
                    </td>

                    <!-- Object Visibility Switch Button (SettingsView.tsx line 3112) -->
                    <td class="py-3 px-3 text-center whitespace-nowrap">
                        <button
                            type="button"
                            onclick="window.toggleModuleVisibility('${mod.id}', ${!canRead})"
                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold transition border shadow-xs ${
                                canRead
                                    ? 'bg-emerald-50 hover:bg-emerald-100 text-emerald-700 dark:bg-emerald-950/50 dark:hover:bg-emerald-900/60 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800'
                                    : 'bg-red-50 hover:bg-red-100 text-red-700 dark:bg-red-950/50 dark:hover:bg-red-900/60 dark:text-red-300 border-red-200 dark:border-red-800'
                            }"
                            title="${canRead ? 'Click to disable object and hide from sidebar' : 'Click to enable object and show in sidebar'}"
                        >
                            <i data-lucide="${canRead ? 'eye' : 'eye-off'}" class="w-3.5 h-3.5"></i>
                            <span>${canRead ? 'Visible in Sidebar' : 'Disabled / Hidden'}</span>
                        </button>
                    </td>

                    <!-- Create (C) -->
                    <td class="py-3 px-3 text-center">
                        <input
                            type="checkbox"
                            ${canCreate ? 'checked' : ''}
                            ${!canRead ? 'disabled' : ''}
                            onchange="window.updateRecordPerm('${mod.id}', 'can_create', this.checked)"
                            class="w-4 h-4 rounded border-zinc-300 dark:border-zinc-700 text-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-400 transition ${canRead ? 'cursor-pointer' : 'cursor-not-allowed opacity-50'}"
                            title="Create records right"
                        />
                    </td>

                    <!-- Read (R) -->
                    <td class="py-3 px-3 text-center">
                        <input
                            type="checkbox"
                            ${canRead ? 'checked' : ''}
                            onchange="window.toggleModuleVisibility('${mod.id}', this.checked)"
                            class="w-4 h-4 rounded border-zinc-300 dark:border-zinc-700 text-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-400 cursor-pointer transition"
                            title="Read/Access records right"
                        />
                    </td>

                    <!-- Update (U) -->
                    <td class="py-3 px-3 text-center">
                        <input
                            type="checkbox"
                            ${canUpdate ? 'checked' : ''}
                            ${!canRead ? 'disabled' : ''}
                            onchange="window.updateRecordPerm('${mod.id}', 'can_update', this.checked)"
                            class="w-4 h-4 rounded border-zinc-300 dark:border-zinc-700 text-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-400 transition ${canRead ? 'cursor-pointer' : 'cursor-not-allowed opacity-50'}"
                            title="Update records right"
                        />
                    </td>

                    <!-- Delete (D) -->
                    <td class="py-3 px-3 text-center">
                        <input
                            type="checkbox"
                            ${canDelete ? 'checked' : ''}
                            ${!canRead ? 'disabled' : ''}
                            onchange="window.updateRecordPerm('${mod.id}', 'can_delete', this.checked)"
                            class="w-4 h-4 rounded border-zinc-300 dark:border-zinc-700 text-red-600 focus:ring-red-500 dark:focus:ring-red-400 transition ${canRead ? 'cursor-pointer' : 'cursor-not-allowed opacity-50'}"
                            title="Delete records right"
                        />
                    </td>

                    <!-- Transfer (T) -->
                    <td class="py-3 px-3 text-center">
                        <input
                            type="checkbox"
                            ${canTransfer ? 'checked' : ''}
                            ${!canRead ? 'disabled' : ''}
                            onchange="window.updateRecordPerm('${mod.id}', 'can_transfer', this.checked)"
                            class="w-4 h-4 rounded border-zinc-300 dark:border-zinc-700 text-blue-600 focus:ring-blue-500 dark:focus:ring-blue-400 transition ${canRead ? 'cursor-pointer' : 'cursor-not-allowed opacity-50'}"
                            title="Grant record ownership transfer rights to this profile"
                        />
                    </td>

                    <!-- Record Scope -->
                    <td class="py-3 px-3">
                        <select
                            ${!canRead ? 'disabled' : ''}
                            onchange="window.updateRecordScope('${mod.id}', this.value)"
                            class="w-full text-xs font-medium py-1.5 px-2.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition shadow-2xs ${
                                !canRead ? 'cursor-not-allowed opacity-50' : 'cursor-pointer'
                            }"
                        >
                            <option value="All" ${scope === 'All' ? 'selected' : ''}>🌐 All Records</option>
                            <option value="Team" ${scope === 'Team' ? 'selected' : ''}>👥 Team Records Only</option>
                            <option value="Own" ${scope === 'Own' ? 'selected' : ''}>👤 Own Records Only</option>
                        </select>
                    </td>

                    <!-- Actions -->
                    <td class="py-3 px-4 text-right whitespace-nowrap">
                        <div class="inline-flex items-center gap-1.5 justify-end">
                            <button
                                type="button"
                                onclick="window.handleSaveSingleRow('${mod.id}', this)"
                                class="p-1.5 rounded-lg border border-zinc-200 dark:border-zinc-700 text-zinc-600 hover:text-emerald-600 hover:border-emerald-300 dark:text-zinc-400 dark:hover:text-emerald-400 bg-white dark:bg-zinc-800 transition"
                                title="Save permissions for this module"
                            >
                                <i data-lucide="check" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400"></i>
                            </button>
                            ${mod.is_custom ? `
                                <button
                                    type="button"
                                    onclick="window.handleDeleteCustomRow('${mod.id}')"
                                    class="p-1.5 rounded-lg border border-zinc-200 dark:border-zinc-700 text-zinc-600 hover:text-red-600 hover:border-red-300 dark:text-zinc-400 dark:hover:text-red-400 bg-white dark:bg-zinc-800 transition"
                                    title="Delete custom object permission"
                                >
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5 text-red-600 dark:text-red-400"></i>
                                </button>
                            ` : ''}
                        </div>
                    </td>
                </tr>
            `;
        }).join('');

        if (window.lucide) lucide.createIcons();
    }

    /**
     * Update KPI counters and badges
     */
    function updateKpis() {
        const all = getAllModules();
        const totalCount = all.length;

        let createCount = 0;
        let deleteCount = 0;
        let transferCount = 0;
        let scopeAll = 0, scopeTeam = 0, scopeOwn = 0;

        all.forEach(mod => {
            const isCore = CORE_MODULE_IDS.includes(mod.id);
            const perm = state.recordPerms.find(p => p.module === mod.id);
            const canRead = perm ? Boolean(perm.can_read && perm.can_read !== 0) : isCore;
            const canCreate = perm ? Boolean(perm.can_create) : canRead;
            const canDelete = perm ? Boolean(perm.can_delete) : false;
            const canTransfer = perm ? Boolean(perm.can_transfer) : false;
            const scope = perm?.record_scope || 'All';

            if (canCreate) createCount++;
            if (canDelete) deleteCount++;
            if (canTransfer) transferCount++;
            if (scope === 'Team') scopeTeam++;
            else if (scope === 'Own') scopeOwn++;
            else scopeAll++;
        });

        const elTotal = document.getElementById('kpiTotalModules');
        const elCreate = document.getElementById('kpiCreateCount');
        const elDelete = document.getElementById('kpiDeleteCount');
        const elTransfer = document.getElementById('kpiTransferCount');
        const elScope = document.getElementById('kpiScopeBreakdown');
        const pillCountAll = document.getElementById('pillCountAll');
        const pillCountCore = document.getElementById('pillCountCore');
        const pillCountExtended = document.getElementById('pillCountExtended');
        const pillCountCustom = document.getElementById('pillCountCustom');

        if (elTotal) elTotal.textContent = totalCount;
        if (elCreate) elCreate.textContent = `${createCount} / ${totalCount}`;
        if (elDelete) elDelete.textContent = `${deleteCount} / ${totalCount}`;
        if (elTransfer) elTransfer.textContent = `${transferCount} / ${totalCount}`;
        if (elScope) elScope.textContent = `All: ${scopeAll} | Team: ${scopeTeam} | Own: ${scopeOwn}`;
        if (pillCountAll) pillCountAll.textContent = totalCount;
        if (pillCountCore) pillCountCore.textContent = CORE_MODULE_IDS.length;
        if (pillCountExtended) pillCountExtended.textContent = EXTENDED_MODULE_IDS.length;
        if (pillCountCustom) pillCountCustom.textContent = state.customObjects.length;
    }

    /**
     * Toggle module visibility (SettingsView.tsx lines 1471-1508)
     */
    function toggleModuleVisibility(modId, enabled) {
        let perm = state.recordPerms.find(p => p.module === modId);
        if (perm) {
            if (!enabled) {
                perm.can_read = 0;
                perm.can_create = 0;
                perm.can_update = 0;
                perm.can_delete = 0;
            } else {
                perm.can_read = 1;
                perm.can_create = 1;
                perm.can_update = 1;
            }
        } else {
            perm = {
                module: modId,
                can_read: enabled ? 1 : 0,
                can_create: enabled ? 1 : 0,
                can_update: enabled ? 1 : 0,
                can_delete: 0,
                can_transfer: 0,
                record_scope: 'All'
            };
            state.recordPerms.push(perm);
        }

        renderMatrixRows();
        updateKpis();
    }

    /**
     * Update single permission flag (can_create, can_update, can_delete, can_transfer)
     */
    function updateRecordPerm(modId, field, checked) {
        let perm = state.recordPerms.find(p => p.module === modId);
        if (perm) {
            perm[field] = checked ? 1 : 0;
        } else {
            perm = {
                module: modId,
                can_create: field === 'can_create' ? 1 : 0,
                can_read: field === 'can_read' ? 1 : 1,
                can_update: field === 'can_update' ? 1 : 0,
                can_delete: field === 'can_delete' ? 1 : 0,
                can_transfer: field === 'can_transfer' ? 1 : 0,
                record_scope: 'All'
            };
            state.recordPerms.push(perm);
        }

        renderMatrixRows();
        updateKpis();
    }

    /**
     * Update Record Scope (All, Team, Own)
     */
    function updateRecordScope(modId, scope) {
        let perm = state.recordPerms.find(p => p.module === modId);
        if (perm) {
            perm.record_scope = scope;
        } else {
            perm = {
                module: modId,
                can_create: 1,
                can_read: 1,
                can_update: 1,
                can_delete: 0,
                can_transfer: 0,
                record_scope: scope
            };
            state.recordPerms.push(perm);
        }

        renderMatrixRows();
        updateKpis();
    }

    /**
     * Preset 1: Apply Core Defaults (SettingsView.tsx lines 1510-1545)
     */
    function applyCoreDefaults() {
        const all = getAllModules();
        all.forEach(mod => {
            const isCore = CORE_MODULE_IDS.includes(mod.id);
            let perm = state.recordPerms.find(p => p.module === mod.id);
            if (perm) {
                perm.can_read = isCore ? 1 : 0;
                perm.can_create = isCore ? 1 : 0;
                perm.can_update = isCore ? 1 : 0;
                perm.can_delete = 0;
            } else {
                state.recordPerms.push({
                    module: mod.id,
                    can_read: isCore ? 1 : 0,
                    can_create: isCore ? 1 : 0,
                    can_update: isCore ? 1 : 0,
                    can_delete: 0,
                    can_transfer: 0,
                    record_scope: 'All'
                });
            }
        });

        renderMatrixRows();
        updateKpis();
        showFeedback('Applied 8 Core CRM Defaults. Extended modules disabled.', 'success');
    }

    /**
     * Preset 2: Enable All Objects (SettingsView.tsx lines 1547-1580)
     */
    function enableAllModules() {
        const all = getAllModules();
        all.forEach(mod => {
            let perm = state.recordPerms.find(p => p.module === mod.id);
            if (perm) {
                perm.can_read = 1;
                perm.can_create = 1;
                perm.can_update = 1;
            } else {
                state.recordPerms.push({
                    module: mod.id,
                    can_read: 1,
                    can_create: 1,
                    can_update: 1,
                    can_delete: 0,
                    can_transfer: 0,
                    record_scope: 'All'
                });
            }
        });

        renderMatrixRows();
        updateKpis();
        showFeedback('Enabled visibility, read, and write permissions for all objects.', 'success');
    }

    /**
     * Preset 3: Disable Extended Only (SettingsView.tsx lines 1582-1616)
     */
    function disableExtendedModules() {
        const targetExtended = [
            ...EXTENDED_MODULE_IDS,
            ...state.customObjects.map(co => co.api_name ? `obj_${co.api_name}` : `co_${co.id}`)
        ];

        targetExtended.forEach(modId => {
            let perm = state.recordPerms.find(p => p.module === modId);
            if (perm) {
                perm.can_read = 0;
                perm.can_create = 0;
                perm.can_update = 0;
                perm.can_delete = 0;
            } else {
                state.recordPerms.push({
                    module: modId,
                    can_read: 0,
                    can_create: 0,
                    can_update: 0,
                    can_delete: 0,
                    can_transfer: 0,
                    record_scope: 'All'
                });
            }
        });

        renderMatrixRows();
        updateKpis();
        showFeedback('Disabled extended enterprise modules.', 'success');
    }

    /**
     * Quick Filter Tabs
     */
    function setRecordRightsFilter(filterName) {
        state.filter = filterName;

        const filterIds = ['all', 'core', 'extended', 'custom', 'full_crud', 'read_only'];
        filterIds.forEach(f => {
            const btn = document.getElementById(`pill_${f}`);
            if (btn) {
                if (f === filterName) {
                    btn.className = 'px-3 py-1 text-xs font-semibold rounded-lg bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 shadow-xs transition';
                } else {
                    btn.className = 'px-3 py-1 text-xs font-medium rounded-lg text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-200 transition';
                }
            }
        });

        renderMatrixRows();
    }

    /**
     * Search Input Handler
     */
    function handleSearchChange(query) {
        state.searchQuery = query;
        renderMatrixRows();
    }

    /**
     * Clear all filters
     */
    function clearRecordRightsFilters() {
        state.searchQuery = '';
        const input = document.getElementById('recordRightsSearchInput');
        if (input) input.value = '';
        setRecordRightsFilter('all');
    }

    /**
     * Column Batch Toggle
     */
    function toggleColumnBatch(field) {
        const visible = getFilteredModules();
        if (visible.length === 0) return;

        const currentOn = visible.filter(m => {
            const p = state.recordPerms.find(rp => rp.module === m.id);
            return p ? Boolean(p[field]) : false;
        }).length;

        const targetVal = currentOn < visible.length ? 1 : 0;

        visible.forEach(m => {
            let perm = state.recordPerms.find(rp => rp.module === m.id);
            if (!perm) {
                perm = {
                    module: m.id,
                    can_create: 1,
                    can_read: 1,
                    can_update: 1,
                    can_delete: 0,
                    can_transfer: 0,
                    record_scope: 'All'
                };
                state.recordPerms.push(perm);
            }
            perm[field] = targetVal;
        });

        renderMatrixRows();
        updateKpis();
        showFeedback(`Toggled "${field.replace('can_', '')}" for ${visible.length} modules.`, 'success');
    }

    /**
     * Bulk Set All Full Access or Read-Only
     */
    function batchSetAllAccess(isFull) {
        const visible = getFilteredModules();
        if (visible.length === 0) return;

        visible.forEach(m => {
            let perm = state.recordPerms.find(rp => rp.module === m.id);
            if (!perm) {
                perm = { module: m.id, record_scope: 'All' };
                state.recordPerms.push(perm);
            }
            perm.can_read = 1;
            perm.can_create = isFull ? 1 : 0;
            perm.can_update = isFull ? 1 : 0;
            perm.can_delete = isFull ? 1 : 0;
            perm.can_transfer = isFull ? 1 : 0;
        });

        renderMatrixRows();
        updateKpis();
        showFeedback(`Set ${visible.length} modules to ${isFull ? 'Full Access' : 'Read-Only'}.`, 'success');
    }

    /**
     * Bulk Set Scope
     */
    function batchSetScope(scopeVal) {
        if (!scopeVal) return;
        const visible = getFilteredModules();
        if (visible.length === 0) return;

        visible.forEach(m => {
            let perm = state.recordPerms.find(rp => rp.module === m.id);
            if (!perm) {
                perm = {
                    module: m.id,
                    can_create: 1,
                    can_read: 1,
                    can_update: 1,
                    can_delete: 0,
                    can_transfer: 0
                };
                state.recordPerms.push(perm);
            }
            perm.record_scope = scopeVal;
        });

        renderMatrixRows();
        updateKpis();
        showFeedback(`Record scope set to "${scopeVal}" for ${visible.length} modules.`, 'success');
    }

    /**
     * Bulk Email Authority & Quota handler (SettingsView.tsx lines 3026-3052)
     */
    async function handleBulkEmailDailyLimitChange(newLimit) {
        const val = parseInt(newLimit, 10) || 500;
        const cur = state.profiles.find(p => String(p.id) === String(state.selectedProfileId));
        if (cur) {
            cur.bulk_email_daily_limit = val;
        }

        try {
            await fetch(`/api/settings/profiles/${encodeURIComponent(state.selectedProfileId)}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': state.csrfToken
                },
                body: JSON.stringify({
                    bulk_email_daily_limit: val,
                    _token: state.csrfToken
                })
            });
            showFeedback(`Bulk email daily limit updated to ${val} emails/day.`, 'success');
        } catch (e) {
            console.warn('Failed to update bulk email quota:', e);
        }
    }

    async function handleBulkEmailSendingChange(checked) {
        const cur = state.profiles.find(p => String(p.id) === String(state.selectedProfileId));
        if (cur) {
            cur.can_send_bulk_email = checked;
        }

        try {
            await fetch(`/api/settings/profiles/${encodeURIComponent(state.selectedProfileId)}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': state.csrfToken
                },
                body: JSON.stringify({
                    can_send_bulk_email: checked,
                    _token: state.csrfToken
                })
            });
            showFeedback(checked ? 'Bulk email sending authorized.' : 'Bulk email sending restricted.', 'success');
        } catch (e) {
            console.warn('Failed to update bulk email authority:', e);
        }
    }

    /**
     * Save Single Row Rights
     */
    async function handleSaveSingleRow(modId, btn) {
        const perm = state.recordPerms.find(p => p.module === modId);
        if (!perm) return;

        const origHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = `<svg class="animate-spin h-3.5 w-3.5 text-indigo-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>`;

        try {
            const res = await fetch(`/api/settings/permissions/${encodeURIComponent(state.selectedProfileId)}/module`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': state.csrfToken
                },
                body: JSON.stringify({
                    module: modId,
                    can_create: perm.can_create ? 1 : 0,
                    can_read: perm.can_read ? 1 : 0,
                    can_update: perm.can_update ? 1 : 0,
                    can_delete: perm.can_delete ? 1 : 0,
                    can_transfer: perm.can_transfer ? 1 : 0,
                    record_scope: perm.record_scope || 'All',
                    _token: state.csrfToken
                })
            });
            const data = await res.json();
            if (res.ok && data.success) {
                showFeedback(`Saved permissions for module "${modId}".`, 'success');
            } else {
                showFeedback(data.message || 'Error saving module rights.', 'error');
            }
        } catch (e) {
            console.error(e);
            showFeedback('Network error saving row.', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = origHtml;
            if (window.lucide) lucide.createIcons();
        }
    }

    /**
     * Delete Custom Object Row
     */
    async function handleDeleteCustomRow(modId) {
        if (!confirm(`Remove permission right for custom object "${modId}"?`)) return;

        try {
            const res = await fetch(`/api/settings/permissions/${encodeURIComponent(state.selectedProfileId)}/module/${encodeURIComponent(modId)}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': state.csrfToken
                }
            });
            const data = await res.json();
            if (res.ok && data.success) {
                state.recordPerms = state.recordPerms.filter(p => p.module !== modId);
                renderMatrixRows();
                updateKpis();
                showFeedback(`Custom object "${modId}" right removed.`, 'success');
            } else {
                showFeedback(data.message || 'Error removing custom right.', 'error');
            }
        } catch (e) {
            console.error(e);
            showFeedback('Network error removing custom right.', 'error');
        }
    }

    /**
     * Batch Save Entire Permissions Matrix
     */
    async function handleSavePermissions() {
        const btn = document.getElementById('saveMatrixBtn');
        const text = document.getElementById('saveMatrixText');
        const icon = document.getElementById('saveMatrixIcon');

        if (btn) btn.disabled = true;
        if (text) text.textContent = 'Saving Matrix...';

        try {
            const res = await fetch(`/api/settings/permissions/${encodeURIComponent(state.selectedProfileId)}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': state.csrfToken
                },
                body: JSON.stringify({
                    recordPermissions: state.recordPerms,
                    _token: state.csrfToken
                })
            });

            const data = await res.json();
            if (res.ok && data.success) {
                showFeedback('Permissions matrix updated successfully!', 'success');
            } else {
                showFeedback(data.message || 'Error saving matrix.', 'error');
            }
        } catch (e) {
            console.error(e);
            showFeedback('Network error while saving matrix.', 'error');
        } finally {
            if (btn) btn.disabled = false;
            if (text) text.textContent = 'Save Matrix';
        }
    }

    /**
     * Modals Bindings & Handlers
     */
    function bindModals() {
        // Modal Overlay Backdrop Clicks
        document.querySelectorAll('.rr-modal-overlay').forEach(overlay => {
            overlay.onclick = (e) => {
                if (e.target === overlay) {
                    overlay.classList.add('hidden');
                    overlay.classList.remove('flex');
                }
            };
        });
    }

    function openCloneModal() {
        const sourceSelect = document.getElementById('cloneSourceProfileSelect');
        if (sourceSelect) {
            sourceSelect.innerHTML = state.profiles
                .filter(p => String(p.id) !== String(state.selectedProfileId))
                .map(p => `<option value="${p.id}">${escapeHtml(p.name)}</option>`)
                .join('');
        }
        const modal = document.getElementById('cloneModal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    }

    function closeCloneModal() {
        const modal = document.getElementById('cloneModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    async function handleCloneSubmit(e) {
        e.preventDefault();
        const sourceId = document.getElementById('cloneSourceProfileSelect')?.value;
        const includeFls = document.getElementById('cloneIncludeFlsCheckbox')?.checked;

        const btn = document.getElementById('cloneSubmitBtn');
        const text = document.getElementById('cloneSubmitText');
        if (btn) btn.disabled = true;
        if (text) text.textContent = 'Cloning Rights...';

        try {
            const res = await fetch(`/api/settings/permissions/${encodeURIComponent(state.selectedProfileId)}/clone`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': state.csrfToken
                },
                body: JSON.stringify({
                    source_profile_id: sourceId,
                    include_field_permissions: includeFls,
                    _token: state.csrfToken
                })
            });

            const data = await res.json();
            if (res.ok && data.success) {
                closeCloneModal();
                showFeedback(data.message || 'Permissions cloned successfully!', 'success');
                await loadPermissionsForProfile(state.selectedProfileId);
            } else {
                alert(data.message || 'Error cloning rights.');
            }
        } catch (err) {
            alert('Network error while cloning: ' + err.message);
        } finally {
            if (btn) btn.disabled = false;
            if (text) text.textContent = 'Apply & Clone Rights';
        }
    }

    function openAddCustomModal() {
        renderCustomPresetOptions();
        const modal = document.getElementById('addCustomModal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    }

    function closeAddCustomModal() {
        const modal = document.getElementById('addCustomModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    function renderCustomPresetOptions() {
        const select = document.getElementById('customObjectPresetSelect');
        if (!select) return;

        let html = '<option value="">-- Choose Registered Custom Object (or type below) --</option>';
        state.customObjects.forEach(co => {
            const key = co.api_name ? `obj_${co.api_name}` : `co_${co.id}`;
            html += `<option value="${key}">${escapeHtml(co.label)} (${key})</option>`;
        });
        select.innerHTML = html;
    }

    function handleCustomPresetChange(val) {
        const input = document.getElementById('newCustomModuleKey');
        if (input && val) input.value = val;
    }

    async function handleAddCustomSubmit(e) {
        e.preventDefault();
        const key = document.getElementById('newCustomModuleKey')?.value?.trim();
        if (!key) return;

        const canCreate = document.getElementById('newModCreate')?.checked;
        const canRead = document.getElementById('newModRead')?.checked;
        const canUpdate = document.getElementById('newModUpdate')?.checked;
        const canDelete = document.getElementById('newModDelete')?.checked;
        const canTransfer = document.getElementById('newModTransfer')?.checked;
        const scope = document.getElementById('newModScope')?.value || 'All';

        const btn = document.getElementById('addCustomSubmitBtn');
        const text = document.getElementById('addCustomSubmitText');
        if (btn) btn.disabled = true;
        if (text) text.textContent = 'Registering...';

        try {
            const res = await fetch(`/api/settings/permissions/${encodeURIComponent(state.selectedProfileId)}/module`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': state.csrfToken
                },
                body: JSON.stringify({
                    module: key,
                    can_create: canCreate ? 1 : 0,
                    can_read: canRead ? 1 : 0,
                    can_update: canUpdate ? 1 : 0,
                    can_delete: canDelete ? 1 : 0,
                    can_transfer: canTransfer ? 1 : 0,
                    record_scope: scope,
                    _token: state.csrfToken
                })
            });

            const data = await res.json();
            if (res.ok && data.success) {
                closeAddCustomModal();
                showFeedback(`Custom object "${key}" rights registered!`, 'success');
                await loadPermissionsForProfile(state.selectedProfileId);
            } else {
                alert(data.message || 'Error registering custom module.');
            }
        } catch (err) {
            alert('Network error registering custom object: ' + err.message);
        } finally {
            if (btn) btn.disabled = false;
            if (text) text.textContent = 'Register Module Rights';
        }
    }

    function openResetModal() {
        const cur = state.profiles.find(p => String(p.id) === String(state.selectedProfileId));
        const ph = document.getElementById('resetProfileNamePlaceholder');
        if (ph && cur) {
            ph.textContent = `"${cur.name}"`;
        }
        const modal = document.getElementById('resetModal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    }

    function closeResetModal() {
        const modal = document.getElementById('resetModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    async function handleConfirmReset() {
        const btn = document.getElementById('confirmResetBtn');
        const text = document.getElementById('confirmResetText');
        if (btn) btn.disabled = true;
        if (text) text.textContent = 'Resetting...';

        try {
            const res = await fetch(`/api/settings/permissions/${encodeURIComponent(state.selectedProfileId)}/reset`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': state.csrfToken
                },
                body: JSON.stringify({ _token: state.csrfToken })
            });

            const data = await res.json();
            if (res.ok && data.success) {
                closeResetModal();
                showFeedback(data.message || 'Permissions reset to baseline defaults.', 'success');
                await loadPermissionsForProfile(state.selectedProfileId);
            } else {
                alert(data.message || 'Error resetting permissions.');
            }
        } catch (err) {
            alert('Network error resetting permissions: ' + err.message);
        } finally {
            if (btn) btn.disabled = false;
            if (text) text.textContent = 'Reset to Defaults';
        }
    }

    /**
     * Feedback Notification Toast
     */
    function showFeedback(msg, type = 'success') {
        const banner = document.getElementById('rrFeedbackBanner');
        const textEl = document.getElementById('rrFeedbackText');
        const iconEl = document.getElementById('rrFeedbackIcon');

        if (!banner || !textEl) return;

        textEl.textContent = msg;
        if (type === 'success') {
            banner.className = 'flex items-center gap-2 p-3 mb-4 rounded-xl text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-200 dark:border-emerald-800/80 animate-fade-in shadow-xs';
            if (iconEl) iconEl.setAttribute('data-lucide', 'check-circle-2');
        } else {
            banner.className = 'flex items-center gap-2 p-3 mb-4 rounded-xl text-xs font-semibold bg-red-50 text-red-800 border border-red-200 dark:bg-red-950/50 dark:text-red-200 dark:border-red-800/80 animate-fade-in shadow-xs';
            if (iconEl) iconEl.setAttribute('data-lucide', 'alert-circle');
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

    // Attach to window object for Blade HTML inline onclick handlers & spa navigation
    window.initRecordRights = initRecordRights;
    window.handleProfileChange = handleProfileChange;
    window.handleSavePermissions = handleSavePermissions;
    window.applyCoreDefaults = applyCoreDefaults;
    window.enableAllModules = enableAllModules;
    window.disableExtendedModules = disableExtendedModules;
    window.setRecordRightsFilter = setRecordRightsFilter;
    window.handleSearchChange = handleSearchChange;
    window.clearRecordRightsFilters = clearRecordRightsFilters;
    window.toggleModuleVisibility = toggleModuleVisibility;
    window.updateRecordPerm = updateRecordPerm;
    window.updateRecordScope = updateRecordScope;
    window.toggleColumnBatch = toggleColumnBatch;
    window.batchSetAllAccess = batchSetAllAccess;
    window.batchSetScope = batchSetScope;
    window.handleSaveSingleRow = handleSaveSingleRow;
    window.handleDeleteCustomRow = handleDeleteCustomRow;
    window.handleBulkEmailDailyLimitChange = handleBulkEmailDailyLimitChange;
    window.handleBulkEmailSendingChange = handleBulkEmailSendingChange;
    window.openCloneModal = openCloneModal;
    window.closeCloneModal = closeCloneModal;
    window.handleCloneSubmit = handleCloneSubmit;
    window.openAddCustomModal = openAddCustomModal;
    window.closeAddCustomModal = closeAddCustomModal;
    window.handleCustomPresetChange = handleCustomPresetChange;
    window.handleAddCustomSubmit = handleAddCustomSubmit;
    window.openResetModal = openResetModal;
    window.closeResetModal = closeResetModal;
    window.handleConfirmReset = handleConfirmReset;

    // Keyboard Shortcuts (Escape to close modals)
    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeCloneModal();
            closeAddCustomModal();
            closeResetModal();
        }
    });

    // Auto-init on script load if container is present
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initRecordRights);
    } else {
        initRecordRights();
    }
})();
