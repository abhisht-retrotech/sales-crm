/**
 * DealSync - Field-Level Security (FLS) Studio
 * 1-to-1 Parity with React SettingsView.tsx (Tab 4: Field-Level Rights)
 * Pure Tailwind CSS & Isolated Execution Scope (No Global Variable Clashes)
 */
(function () {
    'use strict';

    // Local State (Scoped strictly within this IIFE)
    let state = {
        profiles: [],
        selectedProfileId: '',
        customObjects: [],
        customFields: [],
        standardFieldsConfig: {},
        sensitiveFieldsSet: new Set(),
        modulesMap: {},
        fieldPerms: [],
        selectedModule: 'all',
        searchQuery: '',
        activeFilter: 'all', // 'all', 'sensitive', 'custom', 'restricted'
        csrfToken: '',
        saving: false
    };

    let feedbackTimeout = null;

    /**
     * Initialize Field Rights View
     */
    function initFieldRights() {
        const root = document.getElementById('fieldRightsContainer');
        if (!root) return; // Not on Field Rights page

        const rawData = window.INITIAL_FIELD_RIGHTS_DATA || {};
        state.profiles = Array.isArray(rawData.profiles) ? [...rawData.profiles] : [];
        state.selectedProfileId = String(rawData.selectedProfileId || (state.profiles.length > 0 ? state.profiles[0].id : '1'));
        state.customObjects = Array.isArray(rawData.customObjects) ? [...rawData.customObjects] : [];
        state.customFields = Array.isArray(rawData.customFields) ? [...rawData.customFields] : [];
        state.standardFieldsConfig = rawData.standardFieldsConfig || {};
        state.sensitiveFieldsSet = new Set(rawData.sensitiveFields || [
            'opportunities.amount',
            'opportunities.probability',
            'purchase_deals.amount',
            'accounts.credit_limit',
            'accounts.annual_revenue',
            'contacts.phone',
            'wires.amount',
            'users.email',
            'leads.email',
            'payments.amount'
        ]);
        state.modulesMap = rawData.modulesMap || {};
        state.fieldPerms = Array.isArray(rawData.existingFieldPerms) ? [...rawData.existingFieldPerms] : [];
        state.csrfToken = rawData.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content || '';

        renderProfileSelector();
        renderGovernanceBadge();
        applyFiltersToTable();
    }

    /**
     * Render Profile Selector Dropdown
     */
    function renderProfileSelector() {
        const select = document.getElementById('selectedProfileSelect');
        if (!select) return;

        select.innerHTML = state.profiles.map(p => `
            <option value="${p.id}" ${String(p.id) === String(state.selectedProfileId) ? 'selected' : ''}>
                ${escapeHtml(p.name)} ${p.is_admin ? '★' : ''}
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
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    ★ System Administrator (Full Access)
                </span>
            `;
        } else {
            badgeEl.innerHTML = `
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                    Profile Scoped
                </span>
            `;
        }
    }

    /**
     * Handle Profile Change from Selector
     */
    async function handleProfileChange(newProfileId) {
        if (!newProfileId || newProfileId === state.selectedProfileId) return;
        state.selectedProfileId = String(newProfileId);
        renderGovernanceBadge();

        try {
            const res = await fetch(`/api/settings/permissions/${encodeURIComponent(state.selectedProfileId)}`, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();
            if (data && Array.isArray(data.fieldPermissions)) {
                state.fieldPerms = data.fieldPermissions;
            } else {
                state.fieldPerms = [];
            }
        } catch (e) {
            console.warn('Failed to load field permissions for profile:', e);
        }

        // Synchronize table row permission attributes & select inputs
        syncTableWithPermissions();
        applyFiltersToTable();
        showFeedback(`Switched to profile: ${state.profiles.find(p => String(p.id) === state.selectedProfileId)?.name || newProfileId}`, 'success');
    }

    /**
     * Synchronize DOM rows with current state.fieldPerms
     */
    function syncTableWithPermissions() {
        const tbody = document.getElementById('flsTableBody');
        if (!tbody) return;

        const rows = tbody.querySelectorAll('tr[data-mod]');
        rows.forEach(tr => {
            const mod = tr.getAttribute('data-mod');
            const field = tr.getAttribute('data-field');
            const permObj = state.fieldPerms.find(p => p.module === mod && p.field_name === field);
            const perm = permObj?.permission || 'Read/Write';

            tr.setAttribute('data-permission', perm);
            applyRowVisualStyles(tr, mod, field, perm);
        });
    }

    /**
     * Apply visual styling classes to a specific table row
     */
    function applyRowVisualStyles(tr, mod, field, perm) {
        const isRw = perm === 'Read/Write';
        const isRo = perm === 'Read-Only';
        const isHd = perm === 'Hidden';

        // Background highlight
        if (isHd) {
            tr.className = 'border-b border-zinc-200/80 dark:border-zinc-800/80 bg-red-50/30 dark:bg-red-950/20 hover:bg-red-50/50 transition-colors';
        } else if (isRo) {
            tr.className = 'border-b border-zinc-200/80 dark:border-zinc-800/80 bg-amber-50/30 dark:bg-amber-950/20 hover:bg-amber-50/50 transition-colors';
        } else {
            tr.className = 'border-b border-zinc-200/80 dark:border-zinc-800/80 hover:bg-zinc-50/70 dark:hover:bg-zinc-800/40 transition-colors';
        }

        // Dropdown styling
        const select = tr.querySelector('.fls-perm-select');
        if (select) {
            select.value = perm;
            if (isRw) {
                select.className = 'fls-perm-select w-full text-xs font-semibold py-1.5 px-3 rounded-lg border border-emerald-300 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition shadow-2xs';
            } else if (isRo) {
                select.className = 'fls-perm-select w-full text-xs font-semibold py-1.5 px-3 rounded-lg border border-amber-300 dark:border-amber-800 bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300 focus:outline-none focus:ring-2 focus:ring-amber-500/20 transition shadow-2xs';
            } else {
                select.className = 'fls-perm-select w-full text-xs font-semibold py-1.5 px-3 rounded-lg border border-red-300 dark:border-red-800 bg-red-50 dark:bg-red-950/50 text-red-700 dark:text-red-300 focus:outline-none focus:ring-2 focus:ring-red-500/20 transition shadow-2xs';
            }
        }

        // Status icon
        const iconContainer = document.getElementById(`icon-${mod}-${field}`);
        if (iconContainer) {
            if (isRw) {
                iconContainer.innerHTML = '<span title="Full Read & Write Access"><i data-lucide="check" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i></span>';
            } else if (isRo) {
                iconContainer.innerHTML = '<span title="Field is Read-Only for this profile"><i data-lucide="lock" class="w-4 h-4 text-amber-600 dark:text-amber-400"></i></span>';
            } else {
                iconContainer.innerHTML = '<span title="Field is completely Hidden for this profile"><i data-lucide="eye-off" class="w-4 h-4 text-red-600 dark:text-red-400"></i></span>';
            }
            if (window.lucide) lucide.createIcons();
        }
    }

    /**
     * Handle Module Selector Change (SettingsView.tsx line 3268)
     */
    function handleModuleChange(newModule) {
        state.selectedModule = newModule;
        applyFiltersToTable();
    }

    /**
     * Handle Search Input Change (SettingsView.tsx line 3289)
     */
    function handleSearchChange(query) {
        state.searchQuery = (query || '').toLowerCase().trim();
        const clearBtn = document.getElementById('clearSearchBtn');
        if (clearBtn) {
            if (state.searchQuery) {
                clearBtn.classList.remove('hidden');
            } else {
                clearBtn.classList.add('hidden');
            }
        }
        applyFiltersToTable();
    }

    /**
     * Clear Search Input
     */
    function handleClearSearch() {
        state.searchQuery = '';
        const input = document.getElementById('fieldSearchInput');
        if (input) input.value = '';
        const clearBtn = document.getElementById('clearSearchBtn');
        if (clearBtn) clearBtn.classList.add('hidden');
        applyFiltersToTable();
    }

    /**
     * Quick Filter Pills (SettingsView.tsx lines 3346-3378)
     */
    function setQuickFilter(filter) {
        state.activeFilter = filter;

        const pills = ['all', 'sensitive', 'custom', 'restricted'];
        pills.forEach(p => {
            const btn = document.getElementById(`filterPill_${p}`);
            if (btn) {
                if (p === filter) {
                    btn.className = 'px-3 py-1 text-xs font-semibold rounded-full bg-indigo-600 text-white shadow-xs transition';
                } else if (p === 'sensitive') {
                    btn.className = 'px-3 py-1 text-xs font-medium rounded-full border border-amber-300 dark:border-amber-800/80 bg-amber-50/60 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 hover:bg-amber-100/60 transition';
                } else if (p === 'custom') {
                    btn.className = 'px-3 py-1 text-xs font-medium rounded-full border border-indigo-200 dark:border-indigo-800/80 bg-indigo-50/60 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 hover:bg-indigo-100/60 transition';
                } else if (p === 'restricted') {
                    btn.className = 'px-3 py-1 text-xs font-medium rounded-full border border-red-200 dark:border-red-800/80 bg-red-50/60 dark:bg-red-950/40 text-red-700 dark:text-red-300 hover:bg-red-100/60 transition';
                } else {
                    btn.className = 'px-3 py-1 text-xs font-medium rounded-full border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700/60 transition';
                }
            }
        });

        applyFiltersToTable();
    }

    /**
     * Filter table rows and update count ribbons
     */
    function applyFiltersToTable() {
        const tbody = document.getElementById('flsTableBody');
        if (!tbody) return;

        const rows = tbody.querySelectorAll('tr[data-mod]');
        let visibleCount = 0;
        let rwCount = 0;
        let roCount = 0;
        let hdCount = 0;

        rows.forEach(tr => {
            const mod = tr.getAttribute('data-mod');
            const field = tr.getAttribute('data-field');
            const label = (tr.getAttribute('data-label') || '').toLowerCase();
            const key = (tr.getAttribute('data-key') || '').toLowerCase();
            const type = (tr.getAttribute('data-type') || '').toLowerCase();
            const isCustom = tr.getAttribute('data-custom') === '1';
            const isSensitive = tr.getAttribute('data-sensitive') === '1';
            const perm = tr.getAttribute('data-permission') || 'Read/Write';

            // 1. Module filter
            let matchesModule = (state.selectedModule === 'all') || (mod === state.selectedModule);

            // 2. Search query filter
            let matchesSearch = true;
            if (state.searchQuery) {
                matchesSearch = label.includes(state.searchQuery) ||
                                key.includes(state.searchQuery) ||
                                type.includes(state.searchQuery) ||
                                mod.toLowerCase().includes(state.searchQuery);
            }

            // 3. Quick filter pill
            let matchesPill = true;
            if (state.activeFilter === 'sensitive') {
                matchesPill = isSensitive;
            } else if (state.activeFilter === 'custom') {
                matchesPill = isCustom;
            } else if (state.activeFilter === 'restricted') {
                matchesPill = (perm === 'Read-Only' || perm === 'Hidden');
            }

            if (matchesModule && matchesSearch && matchesPill) {
                tr.style.display = '';
                visibleCount++;
                if (perm === 'Read-Only') {
                    roCount++;
                } else if (perm === 'Hidden') {
                    hdCount++;
                } else {
                    rwCount++;
                }
            } else {
                tr.style.display = 'none';
            }
        });

        // Update counts badges (SettingsView.tsx lines 3381-3394)
        const totalEl = document.getElementById('totalCountBadge');
        const rwEl = document.getElementById('rwCountBadge');
        const roEl = document.getElementById('roCountBadge');
        const hdEl = document.getElementById('hdCountBadge');

        if (totalEl) totalEl.textContent = visibleCount;
        if (rwEl) rwEl.textContent = `${rwCount} Read/Write`;
        if (roEl) roEl.textContent = `${roCount} Read-Only`;
        if (hdEl) hdEl.textContent = `${hdCount} Hidden`;

        // Empty state row
        let emptyRow = document.getElementById('flsEmptyRow');
        if (visibleCount === 0) {
            if (!emptyRow) {
                emptyRow = document.createElement('tr');
                emptyRow.id = 'flsEmptyRow';
                emptyRow.innerHTML = `
                    <td colspan="4" class="py-14 text-center text-zinc-400 dark:text-zinc-500">
                        <i data-lucide="filter" class="w-8 h-8 mx-auto mb-2 opacity-40"></i>
                        <p class="font-semibold text-sm text-zinc-700 dark:text-zinc-300">No fields found matching criteria</p>
                        <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-1">Try selecting another object in the dropdown above or clearing your search query.</p>
                        <button type="button" onclick="window.handleClearSearch()" class="mt-3 px-3.5 py-1.5 text-xs font-semibold rounded-lg bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 transition">
                            Reset Search & Filters
                        </button>
                    </td>
                `;
                tbody.appendChild(emptyRow);
                if (window.lucide) lucide.createIcons();
            } else {
                emptyRow.style.display = '';
            }
        } else if (emptyRow) {
            emptyRow.style.display = 'none';
        }
    }

    /**
     * Update Single Field Permission (SettingsView.tsx line 1618)
     */
    async function updateFieldPerm(mod, fieldName, newPerm) {
        // 1. Update in-memory state
        const idx = state.fieldPerms.findIndex(p => p.module === mod && p.field_name === fieldName);
        if (idx >= 0) {
            state.fieldPerms[idx].permission = newPerm;
        } else {
            state.fieldPerms.push({
                id: '',
                profile_id: state.selectedProfileId,
                module: mod,
                field_name: fieldName,
                permission: newPerm
            });
        }

        // 2. Update DOM row
        const tr = document.getElementById(`row-${mod}-${fieldName}`);
        if (tr) {
            tr.setAttribute('data-permission', newPerm);
            applyRowVisualStyles(tr, mod, fieldName, newPerm);
        }

        // 3. Recalculate KPIs
        applyFiltersToTable();

        // 4. Background auto-save via single field endpoint
        try {
            await fetch(`/api/settings/permissions/${encodeURIComponent(state.selectedProfileId)}/field`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': state.csrfToken
                },
                body: JSON.stringify({
                    module: mod,
                    field_name: fieldName,
                    permission: newPerm,
                    _token: state.csrfToken
                })
            });
        } catch (e) {
            console.warn('Autosave background sync note:', e);
        }
    }

    /**
     * Bulk Set Field Permissions for Visible Fields (SettingsView.tsx lines 3310-3337)
     */
    async function handleBulkSetFieldPerms(newPerm) {
        const tbody = document.getElementById('flsTableBody');
        if (!tbody) return;

        const visibleRows = Array.from(tbody.querySelectorAll('tr[data-mod]')).filter(tr => tr.style.display !== 'none');
        if (visibleRows.length === 0) {
            showFeedback('No visible fields to update.', 'error');
            return;
        }

        visibleRows.forEach(tr => {
            const mod = tr.getAttribute('data-mod');
            const field = tr.getAttribute('data-field');

            const idx = state.fieldPerms.findIndex(p => p.module === mod && p.field_name === field);
            if (idx >= 0) {
                state.fieldPerms[idx].permission = newPerm;
            } else {
                state.fieldPerms.push({
                    id: '',
                    profile_id: state.selectedProfileId,
                    module: mod,
                    field_name: field,
                    permission: newPerm
                });
            }

            tr.setAttribute('data-permission', newPerm);
            applyRowVisualStyles(tr, mod, field, newPerm);
        });

        applyFiltersToTable();
        showFeedback(`Applied '${newPerm}' to ${visibleRows.length} visible field(s). Click 'Save Field Security' to persist.`, 'success');
    }

    /**
     * Batch Save All Field Permissions (SettingsView.tsx line 3246)
     */
    async function handleSavePermissions() {
        const saveBtn = document.getElementById('saveFlsBtn');
        const saveText = document.getElementById('saveFlsText');

        if (saveBtn) saveBtn.disabled = true;
        if (saveText) saveText.textContent = 'Saving Changes...';

        try {
            const res = await fetch(`/api/settings/permissions/${encodeURIComponent(state.selectedProfileId)}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': state.csrfToken
                },
                body: JSON.stringify({
                    fieldPermissions: state.fieldPerms,
                    _token: state.csrfToken
                })
            });

            const result = await res.json();
            if (res.ok && result.success) {
                showFeedback('Field permissions updated successfully in database!', 'success');
            } else {
                showFeedback(result.message || 'Error saving field permissions.', 'error');
            }
        } catch (e) {
            console.error('Error saving field permissions:', e);
            showFeedback('Network error while saving field permissions.', 'error');
        } finally {
            if (saveBtn) saveBtn.disabled = false;
            if (saveText) saveText.textContent = 'Save Field Security';
        }
    }

    /**
     * Reset Profile Field Permissions to Baseline Defaults
     */
    async function handleResetBaseline() {
        if (!confirm('Are you sure you want to reset field permissions to standard profile baseline? Any custom field overrides will be reverted.')) {
            return;
        }

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

            const result = await res.json();
            if (res.ok && result.success) {
                showFeedback(result.message || 'Field permissions reset to system baseline.', 'success');
                // Reload profile permissions
                const permRes = await fetch(`/api/settings/permissions/${encodeURIComponent(state.selectedProfileId)}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const permData = await permRes.json();
                if (permData && Array.isArray(permData.fieldPermissions)) {
                    state.fieldPerms = permData.fieldPermissions;
                }
                syncTableWithPermissions();
                applyFiltersToTable();
            } else {
                showFeedback(result.message || 'Error resetting field permissions.', 'error');
            }
        } catch (e) {
            console.error('Error resetting field permissions:', e);
            showFeedback('Network error while resetting field permissions.', 'error');
        }
    }

    /**
     * Feedback Notification Toast
     */
    function showFeedback(msg, type = 'success') {
        const banner = document.getElementById('flsFeedbackBanner');
        const textEl = document.getElementById('flsFeedbackText');
        const iconEl = document.getElementById('flsFeedbackIcon');

        if (!banner || !textEl) return;

        textEl.textContent = msg;
        if (type === 'success') {
            banner.className = 'flex items-center gap-2.5 p-3.5 mb-4 rounded-xl text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-200 dark:border-emerald-800/80 animate-fade-in shadow-xs';
            if (iconEl) iconEl.setAttribute('data-lucide', 'check-circle-2');
        } else {
            banner.className = 'flex items-center gap-2.5 p-3.5 mb-4 rounded-xl text-xs font-semibold bg-red-50 text-red-800 border border-red-200 dark:bg-red-950/50 dark:text-red-200 dark:border-red-800/80 animate-fade-in shadow-xs';
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

    // Expose handlers to window object for Blade HTML inline onclick handlers & spa navigation
    window.initFieldRights = initFieldRights;
    window.handleProfileChange = handleProfileChange;
    window.handleModuleChange = handleModuleChange;
    window.handleSearchChange = handleSearchChange;
    window.handleClearSearch = handleClearSearch;
    window.setQuickFilter = setQuickFilter;
    window.updateFieldPerm = updateFieldPerm;
    window.handleBulkSetFieldPerms = handleBulkSetFieldPerms;
    window.handleSavePermissions = handleSavePermissions;
    window.handleResetBaseline = handleResetBaseline;

    // Auto-init on script load if container is present
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initFieldRights);
    } else {
        initFieldRights();
    }
})();
