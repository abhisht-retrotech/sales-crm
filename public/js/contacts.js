/**
 * DealSync Enterprise CRM — Contacts & Stakeholders Module
 * Full 1:1 Parity with Accounts & Leads Architecture
 * Pure Tailwind CSS styling with Dynamic Custom Fields, Column Customization, 3-State Sorting,
 * Fullscreen 360° Profile Modal, Activity Action Center, and In-Tab Files & Attachments.
 */

(function () {
    'use strict';

    // Default Column Widths for Screenshot Pixel-Perfect Sizing
    const DEFAULT_COL_WIDTHS = {
        assigned_to: 155,
        name: 180,
        account_name: 185,
        contact_role: 140,
        email: 215,
        phone: 150,
        title: 160,
        department: 140,
        created_at: 125,
    };

    // Module State
    const state = {
        contacts: [],
        customFields: [],
        fieldPermissions: {},
        accounts: [],
        users: [],
        roles: ['Decision Maker', 'Champion', 'Influencer', 'Evaluator', 'Stakeholder', 'Vendor Rep', 'Purchasing'],
        activeRole: 'all',
        searchTerm: '',
        viewMode: 'table', // 'table' | 'kanban'
        selectedContactIds: new Set(),
        tableVisibleFields: ['assigned_to', 'name', 'account_name', 'contact_role', 'email', 'phone'],
        kanbanVisibleFields: ['account_name', 'email', 'phone', 'title'],
        colWidths: { ...DEFAULT_COL_WIDTHS },
        sortField: null,
        sortDirection: null, // null | 'asc' | 'desc'
        activeFilters: [], // array of { id, field, operator, value }
        tempFilterConditions: [], // temporary condition rows for the filter modal
        selectedContact: null,
        editingContact: null,
        transferringContact: null,
        viewing360Contact: null,
        contact360Data: null,
        contactFiles: [],
        contactActivities: [],
        active360Tab: 'related',
        isCreateMode: false,
        canSendBulkEmail: true,
        isLoading: false,
    };

    const STANDARD_FIELD_DEFINITIONS = [
        { name: 'assigned_to', label: 'Owner', type: 'lookup', standard: true },
        { name: 'name', label: 'Full Name', type: 'text', standard: true, required: true },
        { name: 'account_name', label: 'Account Organization', type: 'lookup', standard: true },
        { name: 'contact_role', label: 'Buying Role', type: 'dropdown', standard: true, options: ['Decision Maker', 'Champion', 'Influencer', 'Evaluator', 'Stakeholder', 'Vendor Rep', 'Purchasing'] },
        { name: 'email', label: 'Email Address', type: 'email', standard: true, required: true },
        { name: 'phone', label: 'Phone Number', type: 'phone', standard: true },
        { name: 'title', label: 'Job Title', type: 'text', standard: true },
        { name: 'department', label: 'Department', type: 'text', standard: true },
        { name: 'first_name', label: 'First Name', type: 'text', standard: true },
        { name: 'last_name', label: 'Last Name', type: 'text', standard: true },
        { name: 'is_primary', label: 'Primary Contact Flag', type: 'boolean', standard: true },
        { name: 'email_opt_out', label: 'Email Opt Out', type: 'boolean', standard: true },
        { name: 'notes', label: 'Stakeholder Notes', type: 'textarea', standard: true },
        { name: 'created_at', label: 'Created Date', type: 'date', standard: true, readOnly: true },
    ];

    // Helper: Escape HTML
    function escapeHtml(str) {
        if (!str && str !== 0) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // Helper: CSRF Header
    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    // Helper: Toast Notification
    function showNotification(message, type = 'success') {
        if (typeof window.showToast === 'function') {
            window.showToast(message, type);
        } else if (typeof window.showSpaToast === 'function') {
            window.showSpaToast(message, type);
        } else {
            console.log(`[${type.toUpperCase()}] ${message}`);
        }
    }

    // Helper: Get Field Permission
    function getPermission(fieldName) {
        if (state.fieldPermissions && state.fieldPermissions[fieldName]) {
            return state.fieldPermissions[fieldName];
        }
        return 'Read/Write';
    }

    // Helper: Get visible field definitions (excluding Hidden fields)
    function getVisibleFieldDefinitions() {
        const fields = [];

        // 1. Standard Fields (except Hidden)
        STANDARD_FIELD_DEFINITIONS.forEach(sf => {
            const perm = getPermission(sf.name);
            if (perm !== 'Hidden') {
                fields.push({
                    ...sf,
                    permission: perm,
                    isCustom: false,
                });
            }
        });

        // 2. Dynamic Custom Fields (except Hidden)
        state.customFields.forEach(cf => {
            const perm = getPermission(cf.field_name);
            if (perm !== 'Hidden') {
                fields.push({
                    name: cf.field_name,
                    label: cf.field_label,
                    type: (cf.data_type || 'text').toLowerCase(),
                    dataTypeRaw: cf.data_type,
                    options: cf.options ? cf.options.split(',').map(s => s.trim()) : [],
                    required: Boolean(cf.is_required),
                    helpText: cf.help_text,
                    permission: perm,
                    isCustom: true,
                });
            }
        });

        return fields;
    }

    // Load visible fields and column widths from localStorage
    function loadSavedViewConfigs() {
        try {
            const savedTable = localStorage.getItem('dealsync_contacts_table_cols_v4');
            if (savedTable) {
                const parsed = JSON.parse(savedTable);
                if (Array.isArray(parsed) && parsed.length > 0) {
                    state.tableVisibleFields = parsed;
                }
            }
            const savedWidths = localStorage.getItem('dealsync_contacts_col_widths_v4');
            if (savedWidths) {
                const parsed = JSON.parse(savedWidths);
                if (typeof parsed === 'object' && parsed !== null) {
                    state.colWidths = { ...DEFAULT_COL_WIDTHS, ...parsed };
                }
            }
        } catch (_) {}
    }

    function saveViewConfigs() {
        try {
            localStorage.setItem('dealsync_contacts_table_cols_v4', JSON.stringify(state.tableVisibleFields));
        } catch (_) {}
    }

    function saveColumnWidths() {
        try {
            localStorage.setItem('dealsync_contacts_col_widths_v4', JSON.stringify(state.colWidths));
        } catch (_) {}
    }

    // Filter and Sort Contacts
    function getFilteredContacts() {
        let result = state.contacts.filter(c => {
            // 1. Role Chip Filter
            if (state.activeRole !== 'all' && c.contact_role !== state.activeRole) {
                return false;
            }

            // 2. Search Term
            if (state.searchTerm) {
                const s = state.searchTerm.toLowerCase();
                const matchName = (c.name || `${c.first_name || ''} ${c.last_name || ''}`).toLowerCase().includes(s);
                const matchEmail = (c.email || '').toLowerCase().includes(s);
                const matchAccount = (c.account_name || '').toLowerCase().includes(s);
                const matchTitle = (c.title || '').toLowerCase().includes(s);
                const matchPhone = (c.phone || '').toLowerCase().includes(s);
                const matchDept = (c.department || '').toLowerCase().includes(s);
                if (!matchName && !matchEmail && !matchAccount && !matchTitle && !matchPhone && !matchDept) {
                    return false;
                }
            }

            // 3. Dynamic Filter Rules
            for (const rule of state.activeFilters) {
                const raw = c[rule.field];
                const val = (raw !== undefined && raw !== null) ? String(raw).toLowerCase().trim() : '';
                const target = String(rule.value || '').toLowerCase().trim();

                if (rule.operator === 'equals' && val !== target) return false;
                if (rule.operator === 'contains' && !val.includes(target)) return false;
                if (rule.operator === 'starts_with' && !val.startsWith(target)) return false;
                if (rule.operator === 'is_empty' && val !== '') return false;
                if (rule.operator === 'is_not_empty' && val === '') return false;
                if (rule.operator === 'greater_than') {
                    const nVal = parseFloat(val) || 0;
                    const nTarget = parseFloat(target) || 0;
                    if (nVal <= nTarget) return false;
                }
                if (rule.operator === 'less_than') {
                    const nVal = parseFloat(val) || 0;
                    const nTarget = parseFloat(target) || 0;
                    if (nVal >= nTarget) return false;
                }
            }

            return true;
        });

        // 4. Sorting logic (Ascending / Descending)
        if (state.sortField && state.sortDirection) {
            const field = state.sortField;
            const dir = state.sortDirection === 'asc' ? 1 : -1;

            result.sort((a, b) => {
                let valA = a[field];
                let valB = b[field];

                if (field === 'name') {
                    valA = a.name || `${a.first_name || ''} ${a.last_name || ''}`;
                    valB = b.name || `${b.first_name || ''} ${b.last_name || ''}`;
                }

                if (valA === undefined || valA === null) valA = '';
                if (valB === undefined || valB === null) valB = '';

                if (typeof valA === 'number' && typeof valB === 'number') {
                    return (valA - valB) * dir;
                }

                return String(valA).localeCompare(String(valB), undefined, { numeric: true, sensitivity: 'base' }) * dir;
            });
        }

        return result;
    }

    // Role Badge Helper
    function getRoleBadge(role) {
        switch (role) {
            case 'Decision Maker':
                return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800">Decision Maker</span>';
            case 'Champion':
                return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800">Champion</span>';
            case 'Influencer':
                return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800">Influencer</span>';
            case 'Evaluator':
                return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-purple-50 text-purple-700 border border-purple-200 dark:bg-purple-950/40 dark:text-purple-300 dark:border-purple-800">Evaluator</span>';
            default:
                return `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">${escapeHtml(role || 'Stakeholder')}</span>`;
        }
    }

    // Render Stats & Buying Role Chips
    function renderRoleChips() {
        const container = document.getElementById('contacts-role-chips-container');
        if (!container) return;

        const total = state.contacts.length;
        const counts = {
            all: total,
            'Decision Maker': state.contacts.filter(c => c.contact_role === 'Decision Maker').length,
            'Champion': state.contacts.filter(c => c.contact_role === 'Champion').length,
            'Influencer': state.contacts.filter(c => c.contact_role === 'Influencer').length,
            'Evaluator': state.contacts.filter(c => c.contact_role === 'Evaluator').length,
        };

        const chips = [
            { id: 'all', label: 'All Contacts', count: counts.all },
            { id: 'Decision Maker', label: 'Decision Makers', count: counts['Decision Maker'] },
            { id: 'Champion', label: 'Champions', count: counts['Champion'] },
            { id: 'Influencer', label: 'Influencers', count: counts['Influencer'] },
            { id: 'Evaluator', label: 'Evaluators', count: counts['Evaluator'] },
        ];

        container.innerHTML = `
            <span class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 mr-1 self-center whitespace-nowrap">Buying Roles:</span>
            ${chips.map(chip => {
                const isActive = state.activeRole === chip.id;
                const activeClasses = isActive
                    ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900 font-bold shadow-xs'
                    : 'bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700/60 border border-zinc-200 dark:border-zinc-700';

                return `
                    <button
                        type="button"
                        onclick="window.setContactRoleFilter('${chip.id}')"
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs transition-all cursor-pointer whitespace-nowrap ${activeClasses}"
                    >
                        <span>${chip.label}</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] font-semibold ${isActive ? 'bg-white/20 text-white dark:bg-zinc-900/20 dark:text-zinc-900' : 'bg-zinc-100 dark:bg-zinc-700 text-zinc-600 dark:text-zinc-300'}">
                            ${chip.count}
                        </span>
                    </button>
                `;
            }).join('')}
        `;

        // Update KPI summary cards
        const kpiTotal = document.getElementById('kpi-total-contacts');
        if (kpiTotal) kpiTotal.innerText = `${total} Contacts`;

        const kpiDecisionMakers = document.getElementById('kpi-decision-makers');
        if (kpiDecisionMakers) kpiDecisionMakers.innerText = `${counts['Decision Maker']} Stakeholders`;

        const kpiChampions = document.getElementById('kpi-primary-champions');
        if (kpiChampions) kpiChampions.innerText = `${counts['Champion']} Champions`;
    }

    // Render Active Filter Chips
    function renderActiveFilterChips() {
        const container = document.getElementById('contacts-filter-chips-bar');
        if (!container) return;

        if (state.activeFilters.length === 0) {
            container.classList.add('hidden');
            container.innerHTML = '';
            const badge = document.getElementById('contacts-filter-badge');
            if (badge) badge.classList.add('hidden');
            return;
        }

        const badge = document.getElementById('contacts-filter-badge');
        if (badge) {
            badge.innerText = state.activeFilters.length;
            badge.classList.remove('hidden');
        }

        container.classList.remove('hidden');
        const fieldDefs = getVisibleFieldDefinitions();

        container.innerHTML = `
            <div class="flex items-center gap-1.5 flex-wrap p-2 rounded-lg bg-blue-50/70 dark:bg-blue-950/20 border border-blue-200 dark:border-blue-900/40 text-xs">
                <span class="font-semibold text-blue-900 dark:text-blue-300 flex items-center gap-1 mr-1">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                    </svg>
                    <span>Active Filters:</span>
                </span>
                ${state.activeFilters.map(r => {
                    const f = fieldDefs.find(x => x.name === r.field);
                    const label = f ? f.label : r.field;
                    return `
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-white dark:bg-zinc-800 text-blue-800 dark:text-blue-200 border border-blue-200 dark:border-blue-800 text-[11px] shadow-2xs">
                            <span class="font-medium">${escapeHtml(label)}</span>
                            <span class="text-zinc-400 font-mono">${escapeHtml(r.operator)}</span>
                            <span class="font-semibold text-zinc-900 dark:text-zinc-100">"${escapeHtml(r.value)}"</span>
                            <button type="button" onclick="window.removeContactFilterRule('${r.id}')" class="text-zinc-400 hover:text-red-600 transition-colors cursor-pointer ml-0.5">
                                &times;
                            </button>
                        </span>
                    `;
                }).join('')}
                <button
                    type="button"
                    onclick="window.clearAllContactFilters()"
                    class="ml-auto text-xs text-blue-700 dark:text-blue-300 hover:underline font-semibold cursor-pointer"
                >
                    Clear All
                </button>
            </div>
        `;
    }

    // Render Bulk Email Selection Bar
    function renderBulkEmailBanner() {
        const banner = document.getElementById('contacts-bulk-email-banner');
        if (!banner) return;

        const count = state.selectedContactIds.size;
        if (count > 0 && state.canSendBulkEmail) {
            banner.classList.remove('hidden');
            const countLabel = document.getElementById('bulk-selected-count-label');
            if (countLabel) {
                countLabel.innerText = `${count} contact${count > 1 ? 's' : ''} selected for bulk outreach`;
            }
        } else {
            banner.classList.add('hidden');
        }
    }

    // 3-State Column Sorting Handler (Ascending -> Descending -> Reset)
    window.handleSortContactColumn = function (colName) {
        if (state.sortField !== colName) {
            state.sortField = colName;
            state.sortDirection = 'asc';
        } else if (state.sortDirection === 'asc') {
            state.sortDirection = 'desc';
        } else if (state.sortDirection === 'desc') {
            state.sortField = null;
            state.sortDirection = null;
        } else {
            state.sortField = colName;
            state.sortDirection = 'asc';
        }
        renderContactsView();
    };

    // Column resizing logic
    // High-performance mathematically synchronized Column Edge Resizer Engine
    function initContactColumnResize(container) {
        if (!container) return;
        const table = container.querySelector('#contacts-data-table') || container.querySelector('table');
        if (!table) return;

        const resizers = container.querySelectorAll('.contact-col-resizer, .table-col-resizer');
        resizers.forEach(resizer => {
            const th = resizer.closest('th');
            if (!th) return;

            const colName = resizer.getAttribute('data-col') || th.getAttribute('data-col');
            if (!colName) return;

            resizer.addEventListener('mousedown', function (e) {
                if (e.button !== 0) return; // Only primary mouse button

                e.preventDefault();
                e.stopPropagation();

                const startX = e.clientX;
                const startColWidth = th.getBoundingClientRect().width;
                const startTableWidth = table.getBoundingClientRect().width;
                const colEl = table.querySelector(`col[data-col="${colName}"]`) || container.querySelector(`#col-width-cnt-${colName}`);

                document.body.classList.add('is-resizing-contact-col');
                resizer.classList.add('is-resizing');

                function onMouseMove(moveEvent) {
                    moveEvent.preventDefault();
                    const deltaX = moveEvent.clientX - startX;
                    const newWidth = Math.max(65, Math.round(startColWidth + deltaX));
                    const actualDelta = newWidth - startColWidth;

                    // 1. Update this column width on th and col
                    state.colWidths[colName] = newWidth;
                    th.style.width = newWidth + 'px';
                    th.style.minWidth = newWidth + 'px';
                    if (colEl) {
                        colEl.style.width = newWidth + 'px';
                        colEl.style.minWidth = newWidth + 'px';
                    }

                    // 2. Synchronously adjust table width so other columns don't compress or drift
                    const containerWidth = container.clientWidth || 0;
                    const newTableWidth = Math.max(startTableWidth + actualDelta, containerWidth);
                    table.style.width = newTableWidth + 'px';
                    table.style.minWidth = newTableWidth + 'px';
                }

                function onMouseUp(upEvent) {
                    upEvent.preventDefault();
                    upEvent.stopPropagation();

                    document.removeEventListener('mousemove', onMouseMove);
                    document.removeEventListener('mouseup', onMouseUp);
                    document.body.classList.remove('is-resizing-contact-col');
                    resizer.classList.remove('is-resizing');

                    const finalWidth = Math.max(65, Math.round(th.getBoundingClientRect().width));
                    state.colWidths[colName] = finalWidth;
                    saveColumnWidths();
                }

                document.addEventListener('mousemove', onMouseMove);
                document.addEventListener('mouseup', onMouseUp);
            });

            // Double click to reset to default
            resizer.addEventListener('dblclick', function (e) {
                e.preventDefault();
                e.stopPropagation();
                const defaultW = DEFAULT_COL_WIDTHS[colName] || 160;
                state.colWidths[colName] = defaultW;
                th.style.width = defaultW + 'px';
                th.style.minWidth = defaultW + 'px';
                if (colEl) {
                    colEl.style.width = defaultW + 'px';
                    colEl.style.minWidth = defaultW + 'px';
                }
                saveColumnWidths();
                renderTableView(getFilteredContacts());
            });
        });
    }

    // Helper: Render Cell in Table with Company Profile Font Color
    function renderFieldCell(c, fieldName, fieldDef) {
        if (getPermission(fieldName) === 'Hidden' || c['_hidden_' + fieldName]) {
            return '<span class="text-zinc-400 italic text-[11px]">Hidden</span>';
        }

        const fullName = c.name || `${c.first_name || ''} ${c.last_name || ''}`.trim() || 'Unnamed Contact';

        if (fieldName === 'assigned_to') {
            const owner = c.owner_name || 'System Admin';
            const initial = owner.charAt(0).toUpperCase();
            const isSystem = owner.toLowerCase().includes('system');
            const bgClass = isSystem ? 'bg-sky-100 text-sky-700 dark:bg-sky-950 dark:text-sky-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300';
            return `
                <div class="flex items-center gap-2 text-xs">
                    <span class="w-5 h-5 rounded-full ${bgClass} font-bold text-[10px] inline-flex items-center justify-center shrink-0">
                        ${initial}
                    </span>
                    <span class="font-normal truncate" style="color: var(--text-primary, #09090b);">${escapeHtml(owner)}</span>
                </div>
            `;
        }

        if (fieldName === 'name') {
            return `
                <span onclick="window.openFullscreenContactDetail(${c.id})" class="font-bold hover:underline cursor-pointer truncate block" style="color: var(--text-primary, #09090b);">
                    ${escapeHtml(fullName)}
                </span>
            `;
        }

        if (fieldName === 'account_name') {
            const accName = c.account_name && c.account_name !== 'Unlinked' ? c.account_name : '—';
            return `<span class="text-xs truncate block" style="color: var(--text-primary, #09090b); opacity: 0.85;">${escapeHtml(accName)}</span>`;
        }

        if (fieldName === 'contact_role') {
            const role = c.contact_role || 'Stakeholder';
            return `<span class="text-xs truncate block font-medium" style="color: var(--text-primary, #09090b);">${escapeHtml(role)}</span>`;
        }

        if (fieldName === 'email') {
            const email = c.email || '—';
            return `<span class="text-xs truncate block font-sans" style="color: var(--text-primary, #09090b); opacity: 0.85;">${escapeHtml(email)}</span>`;
        }

        if (fieldName === 'phone') {
            const phone = c.phone || '—';
            return `<span class="text-xs truncate block font-sans" style="color: var(--text-primary, #09090b); opacity: 0.85;">${escapeHtml(phone)}</span>`;
        }

        if (fieldName === 'title') {
            return `<span class="text-xs truncate block" style="color: var(--text-primary, #09090b); opacity: 0.85;">${escapeHtml(c.title || '—')}</span>`;
        }

        if (fieldName === 'department') {
            return `<span class="text-xs truncate block" style="color: var(--text-primary, #09090b); opacity: 0.85;">${escapeHtml(c.department || '—')}</span>`;
        }

        if (fieldName === 'created_at') {
            const val = c.created_at;
            if (!val) return '<span class="text-zinc-400">—</span>';
            return `<span class="text-xs" style="color: var(--text-primary, #09090b); opacity: 0.7;">${new Date(val).toLocaleDateString()}</span>`;
        }

        // Custom fields
        if (fieldDef && fieldDef.isCustom) {
            const val = c[fieldName];
            if (val === undefined || val === null || val === '') return '<span class="text-zinc-400">—</span>';
            return `<span class="text-xs truncate block" style="color: var(--text-primary, #09090b);">${escapeHtml(val)}</span>`;
        }

        return `<span class="text-xs truncate block" style="color: var(--text-primary, #09090b);">${escapeHtml(c[fieldName] || '—')}</span>`;
    }

    // Render Table View inside its OWN Separate Card with Company Profile Theme Colors
    function renderTableView(contacts) {
        const tableRoot = document.getElementById('contacts-table-view-root');
        if (!tableRoot) return;

        const fieldDefs = getVisibleFieldDefinitions();
        const visibleCols = state.tableVisibleFields
            .map(key => fieldDefs.find(f => f.name === key))
            .filter(Boolean);

        if (contacts.length === 0) {
            tableRoot.innerHTML = `
                <div id="contacts-table-card" class="contacts-themed-card rounded-2xl border border-orange-200/90 dark:border-orange-500/30 p-12 text-center shadow-[0_4px_20px_-2px_rgba(251,146,60,0.12)]" style="background-color: var(--table-bg, #ffffff); color: var(--text-primary, #09090b);">
                    <div class="w-12 h-12 rounded-xl bg-orange-50 dark:bg-zinc-800 text-orange-400 flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold mb-1" style="color: var(--text-primary, #09090b);">No contacts found</h3>
                    <p class="text-xs text-zinc-500 max-w-sm mx-auto mb-4">
                        There are no contacts matching your current search or active filter criteria.
                    </p>
                    <button
                        type="button"
                        onclick="window.openContactModal('create')"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm transition-colors cursor-pointer"
                    >
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        <span>Add Contact</span>
                    </button>
                </div>
            `;
            return;
        }

        const allChecked = contacts.length > 0 && contacts.every(c => state.selectedContactIds.has(c.id));
        const initialTableWidth = Math.max(1050, 58 + visibleCols.reduce((sum, c) => sum + (state.colWidths[c.name] || 160), 0));

        tableRoot.innerHTML = `
            <div id="contacts-table-card" class="contacts-themed-card rounded-2xl border border-orange-200/90 dark:border-orange-500/30 shadow-[0_4px_20px_-2px_rgba(251,146,60,0.12)] overflow-hidden" style="background-color: var(--table-bg, #ffffff); color: var(--text-primary, #09090b);">
                <div class="overflow-x-auto">
                    <table id="contacts-data-table" class="contacts-themed-table text-left border-collapse table-fixed" style="width: ${initialTableWidth}px; min-width: 100%; background-color: var(--table-bg, #ffffff); color: var(--text-primary, #09090b);">
                        <colgroup>
                            <col style="width: 58px; min-width: 58px;">
                            ${visibleCols.map(c => `<col id="col-width-cnt-${c.name}" data-col="${c.name}" style="width: ${state.colWidths[c.name] || 160}px; min-width: ${state.colWidths[c.name] || 160}px;">`).join('')}
                        </colgroup>
                        <thead style="background-color: var(--table-bg, #ffffff); color: var(--text-primary, #09090b);">
                            <tr class="contacts-themed-header border-b text-xs font-semibold select-none" style="background-color: var(--table-bg, #ffffff); color: var(--text-primary, #09090b); border-bottom: 1px solid var(--table-border, #e2e8f0);">
                                <th class="w-[58px] px-2 py-3 text-center select-none" style="width: 58px; min-width: 58px; background-color: var(--table-bg, #ffffff); color: var(--text-primary, #09090b); border-right: 1px solid var(--table-border, #e2e8f0);">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <span class="w-4 inline-block"></span>
                                        <input
                                            type="checkbox"
                                            id="contacts-master-checkbox"
                                            ${allChecked ? 'checked' : ''}
                                            onchange="window.toggleSelectAllContacts(this.checked)"
                                            class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer w-4 h-4 align-middle"
                                        >
                                    </div>
                                </th>
                                ${visibleCols.map(col => {
                                    const isCurrent = state.sortField === col.name;
                                    const dir = isCurrent ? state.sortDirection : null;
                                    const colW = state.colWidths[col.name] || 160;

                                    let arrowIcon = '';
                                    if (dir === 'asc') {
                                        arrowIcon = `
                                            <svg class="w-3.5 h-3.5 text-sky-600 dark:text-sky-400 shrink-0 ml-1 transition-all" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="18 15 12 9 6 15"/>
                                            </svg>
                                        `;
                                    } else if (dir === 'desc') {
                                        arrowIcon = `
                                            <svg class="w-3.5 h-3.5 text-sky-600 dark:text-sky-400 shrink-0 ml-1 transition-all" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="6 9 12 15 18 9"/>
                                            </svg>
                                        `;
                                    } else {
                                        arrowIcon = `
                                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0 ml-1 transition-colors group-hover:text-slate-600 dark:group-hover:text-zinc-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="6 9 12 15 18 9"/>
                                            </svg>
                                        `;
                                    }

                                    const tooltipText = dir === 'asc'
                                        ? `${col.label}: Ascending (Click for Descending)`
                                        : (dir === 'desc'
                                            ? `${col.label}: Descending (Click to reset to Normal)`
                                            : `Click to sort by ${col.label} (Ascending)`);

                                    return `
                                        <th
                                            class="relative px-4 py-3 select-none group cursor-pointer hover:opacity-90 transition-colors"
                                            data-col="${col.name}"
                                            style="width: ${colW}px; min-width: ${colW}px; background-color: var(--table-bg, #ffffff); color: var(--text-primary, #09090b); border-right: 1px solid var(--table-border, #e2e8f0);"
                                            onclick="window.handleSortContactColumn('${col.name}')"
                                            title="${tooltipText}"
                                        >
                                            <div class="flex items-center justify-between pr-2">
                                                <span class="truncate ${isCurrent ? 'font-bold text-sky-600' : 'font-semibold'}" style="color: var(--text-primary, #09090b);">${escapeHtml(col.label)}</span>
                                                ${arrowIcon}
                                            </div>
                                            <div class="contact-col-resizer table-col-resizer" data-col="${col.name}" onclick="event.stopPropagation()" title="Drag to resize column (Double click to reset)"></div>
                                        </th>
                                    `;
                                }).join('')}
                            </tr>
                        </thead>
                        <tbody class="divide-y text-xs" style="border-color: var(--table-border, #e2e8f0); background-color: var(--table-bg, #ffffff); color: var(--text-primary, #09090b);">
                            ${contacts.map((c, index) => {
                                const isChecked = state.selectedContactIds.has(c.id);
                                return `
                                    <tr
                                        class="contacts-themed-row transition-colors group cursor-pointer"
                                        style="background-color: var(--table-bg, #ffffff); color: var(--text-primary, #09090b); border-bottom: 1px solid var(--table-border, #e2e8f0);"
                                        onclick="window.handleContactRowClick(event, ${c.id})"
                                    >
                                        <td class="w-[58px] px-2 py-3.5 text-center select-none" style="width: 58px; min-width: 58px; background-color: var(--table-bg, #ffffff); color: var(--text-primary, #09090b); border-right: 1px solid var(--table-border, #e2e8f0);" onclick="event.stopPropagation()">
                                            <div class="flex items-center justify-center gap-1.5 text-xs font-medium" style="color: var(--text-primary, #09090b);">
                                                <span class="w-4 text-right opacity-70" style="color: var(--text-primary, #09090b);">${index + 1}</span>
                                                <input
                                                    type="checkbox"
                                                    value="${c.id}"
                                                    ${isChecked ? 'checked' : ''}
                                                    onchange="window.toggleSelectContact(${c.id}, this.checked)"
                                                    class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer w-4 h-4 align-middle"
                                                >
                                            </div>
                                        </td>
                                        ${visibleCols.map(col => `
                                            <td class="px-4 py-3.5 align-middle truncate" style="background-color: var(--table-bg, #ffffff); color: var(--text-primary, #09090b); border-right: 1px solid var(--table-border, #e2e8f0);">
                                                ${renderFieldCell(c, col.name, col)}
                                            </td>
                                        `).join('')}
                                    </tr>
                                `;
                            }).join('')}
                        </tbody>
                    </table>
                </div>

                <!-- Footer Summary matching screenshot 1:1 -->
                <div class="contacts-footer-themed px-5 py-3 border-t flex items-center justify-between text-xs" style="background-color: var(--table-bg, #ffffff); color: var(--text-primary, #09090b); border-color: var(--table-border, #e2e8f0);">
                    <div>
                        Showing <strong style="color: var(--text-primary, #09090b);">${contacts.length}</strong> records
                    </div>
                    <div class="text-[11px]" style="color: var(--text-primary, #09090b); opacity: 0.7;">
                        Tip: Drag column edges to resize &bull; Click header to sort
                    </div>
                </div>
            </div>
        `;

        initContactColumnResize(tableRoot);
    }

    // Render Kanban View
    function renderKanbanView(contacts) {
        const kanbanRoot = document.getElementById('contacts-kanban-view-root');
        if (!kanbanRoot) return;

        kanbanRoot.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-start">
                ${state.roles.map(role => {
                    const roleContacts = contacts.filter(c => c.contact_role === role);
                    return `
                        <div class="bg-slate-50 dark:bg-zinc-800/50 rounded-2xl border border-slate-200/80 dark:border-zinc-700/60 p-3 space-y-3">
                            <div class="flex items-center justify-between pb-2 border-b border-slate-200/80 dark:border-zinc-700">
                                <span class="font-bold text-xs text-slate-900 dark:text-zinc-100">${escapeHtml(role)}</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-white dark:bg-zinc-800 text-slate-600 dark:text-zinc-300 shadow-2xs">
                                    ${roleContacts.length}
                                </span>
                            </div>
                            <div class="space-y-2">
                                ${roleContacts.map(c => `
                                    <div
                                        onclick="window.openFullscreenContactDetail(${c.id})"
                                        class="bg-white dark:bg-zinc-900 p-3 rounded-xl border border-slate-200/90 dark:border-zinc-800 shadow-2xs hover:shadow-xs transition-shadow cursor-pointer space-y-2"
                                    >
                                        <div class="flex items-start justify-between gap-2">
                                            <h5 class="font-bold text-xs text-slate-900 dark:text-zinc-100 truncate">${escapeHtml(c.name || `${c.first_name || ''} ${c.last_name || ''}`)}</h5>
                                            ${c.is_primary ? '<span class="text-amber-500 text-xs">⭐</span>' : ''}
                                        </div>
                                        <div class="text-[11px] text-slate-500 truncate">${escapeHtml(c.account_name || 'Unlinked Account')}</div>
                                        <div class="text-[11px] text-indigo-600 truncate font-mono">${escapeHtml(c.email || '—')}</div>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    `;
                }).join('')}
            </div>
        `;
    }

    // Main View Dispatcher
    function renderContactsView() {
        renderRoleChips();
        renderActiveFilterChips();
        renderBulkEmailBanner();

        const filtered = getFilteredContacts();

        const countLabel = document.getElementById('contacts-records-count-label');
        if (countLabel) {
            countLabel.innerHTML = `Showing <strong>${filtered.length}</strong> record${filtered.length !== 1 ? 's' : ''}`;
        }

        const tableRoot = document.getElementById('contacts-table-view-root');
        const kanbanRoot = document.getElementById('contacts-kanban-view-root');

        if (state.viewMode === 'table') {
            if (tableRoot) tableRoot.classList.remove('hidden');
            if (kanbanRoot) kanbanRoot.classList.add('hidden');
            renderTableView(filtered);
        } else {
            if (tableRoot) tableRoot.classList.add('hidden');
            if (kanbanRoot) kanbanRoot.classList.remove('hidden');
            renderKanbanView(filtered);
        }
    }

    // Role Filter Dispatcher
    window.setContactRoleFilter = function (roleId) {
        state.activeRole = roleId;
        renderContactsView();
    };

    // View Switcher (Table vs Kanban)
    window.switchContactsViewMode = function (mode) {
        state.viewMode = mode;
        const btnTable = document.getElementById('btn-contact-view-table');
        const btnKanban = document.getElementById('btn-contact-view-kanban');

        if (mode === 'table') {
            if (btnTable) btnTable.className = 'flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-md bg-white text-zinc-950 shadow-xs dark:bg-zinc-800 dark:text-zinc-100 cursor-pointer';
            if (btnKanban) btnKanban.className = 'flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-md text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-200 cursor-pointer';
        } else {
            if (btnTable) btnTable.className = 'flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-md text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-200 cursor-pointer';
            if (btnKanban) btnKanban.className = 'flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-md bg-white text-zinc-950 shadow-xs dark:bg-zinc-800 dark:text-zinc-100 cursor-pointer';
        }

        renderContactsView();
    };

    // Selection Handling
    window.toggleSelectContact = function (id, isChecked) {
        if (isChecked) {
            state.selectedContactIds.add(id);
        } else {
            state.selectedContactIds.delete(id);
        }
        renderBulkEmailBanner();
        const master = document.getElementById('contacts-master-checkbox');
        if (master) {
            const filtered = getFilteredContacts();
            master.checked = filtered.length > 0 && filtered.every(c => state.selectedContactIds.has(c.id));
        }
    };

    window.toggleSelectAllContacts = function (isChecked) {
        const filtered = getFilteredContacts();
        filtered.forEach(c => {
            if (isChecked) {
                state.selectedContactIds.add(c.id);
            } else {
                state.selectedContactIds.delete(c.id);
            }
        });
        renderContactsView();
    };

    window.clearSelectedContacts = function () {
        state.selectedContactIds.clear();
        renderContactsView();
    };

    window.triggerBulkEmail = function () {
        showNotification(`Prepared campaign outreach for ${state.selectedContactIds.size} stakeholders.`, 'success');
    };

    window.handleContactRowClick = function (e, id) {
        // Prevent opening if clicked on checkbox, links, buttons
        if (e.target.closest('input, button, a')) return;
        window.openFullscreenContactDetail(id);
    };

    // ==========================================
    // FULLSCREEN CONTACT 360 MODAL
    // ==========================================
    window.openFullscreenContactDetail = async function (contactId) {
        state.viewing360Contact = contactId;
        const modal = document.getElementById('contact-360-modal');
        if (!modal) return;

        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');

        // Set default right tab to 'related'
        window.switchFullscreenRightTab('related');

        try {
            const res = await fetch(`/api/contacts/${contactId}/360`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            if (res.ok) {
                const data = await res.json();
                state.contact360Data = data;
                populateContact360Modal(data);
            }
        } catch (err) {
            console.error('Error fetching contact 360 data:', err);
        }

        // Also fetch files and activities count
        window.loadContactFiles(contactId);
        window.refreshContactActivities(contactId);
    };

    window.navigateToContact = function (contactId) {
        window.openFullscreenContactDetail(contactId);
    };

    window.closeContact360Modal = function () {
        const modal = document.getElementById('contact-360-modal');
        if (modal) modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        state.viewing360Contact = null;
        state.contact360Data = null;
    };

    function populateContact360Modal(data) {
        const c = data.contact || {};
        const acc = data.account || {};
        const opps = data.opportunities || [];
        const deals = data.purchase_deals || [];

        const fullName = c.name || `${c.first_name || ''} ${c.last_name || ''}`.trim() || 'Stakeholder';

        // Top App Bar
        const nameHeader = document.getElementById('modal-fullscreen-contact-name');
        if (nameHeader) {
            nameHeader.innerHTML = `
                <span>${escapeHtml(fullName)}</span>
                ${c.is_primary ? '<span class="text-amber-500 text-sm" title="Primary Account Contact">⭐</span>' : ''}
            `;
        }

        const breadcrumb = document.getElementById('modal-breadcrumb-contact-name');
        if (breadcrumb) breadcrumb.textContent = fullName;

        // Left Column: Identity
        const fNameEl = document.getElementById('detail-fs-first_name');
        if (fNameEl) fNameEl.textContent = c.first_name || '—';
        const lNameEl = document.getElementById('detail-fs-last_name');
        if (lNameEl) lNameEl.textContent = c.last_name || '—';
        const titleEl = document.getElementById('detail-fs-title');
        if (titleEl) titleEl.textContent = c.title || '—';
        const deptEl = document.getElementById('detail-fs-department');
        if (deptEl) deptEl.textContent = c.department || '—';
        const roleEl = document.getElementById('detail-fs-role');
        if (roleEl) roleEl.innerHTML = getRoleBadge(c.contact_role);

        // Left Column: Contact Info & Organization
        const accEl = document.getElementById('detail-fs-account');
        if (accEl) {
            accEl.innerHTML = c.account_name && c.account_name !== 'Unlinked'
                ? `<span class="hover:underline cursor-pointer font-semibold text-indigo-600 dark:text-indigo-400">${escapeHtml(c.account_name)}</span>`
                : '<span class="text-slate-400">Unlinked</span>';
        }
        const emailEl = document.getElementById('detail-fs-email');
        if (emailEl) {
            emailEl.innerHTML = c.email
                ? `<a href="mailto:${escapeHtml(c.email)}" class="text-indigo-600 dark:text-indigo-400 hover:underline font-mono">${escapeHtml(c.email)}</a>`
                : '—';
        }
        const phoneEl = document.getElementById('detail-fs-phone');
        if (phoneEl) {
            phoneEl.innerHTML = c.phone
                ? `<a href="tel:${escapeHtml(c.phone)}" class="text-slate-800 dark:text-zinc-200 hover:text-indigo-600 font-mono">${escapeHtml(c.phone)}</a>`
                : '—';
        }
        const primaryEl = document.getElementById('detail-fs-primary');
        if (primaryEl) primaryEl.textContent = c.is_primary ? 'Yes (Primary Contact)' : 'No';
        const optOutEl = document.getElementById('detail-fs-opt-out');
        if (optOutEl) optOutEl.textContent = c.email_opt_out ? 'Suppressed (Opted Out)' : 'Active (Opted In)';

        // Left Column: System Information
        const ownerEl = document.getElementById('detail-fs-owner');
        if (ownerEl) ownerEl.textContent = c.owner_name || 'System Administrator';
        const createdAtEl = document.getElementById('detail-fs-created-at');
        if (createdAtEl) createdAtEl.textContent = c.created_at ? new Date(c.created_at).toLocaleString() : '—';
        const recordIdEl = document.getElementById('detail-fs-record-id');
        if (recordIdEl) recordIdEl.textContent = c.id ? `cnt_${c.id}` : '—';

        // Right Column: Related Cards
        const badgeAcc = document.getElementById('fs-badge-account-name');
        if (badgeAcc) badgeAcc.textContent = acc.name || c.account_name || 'Unlinked';

        const accInfoContainer = document.getElementById('fs-account-info-container');
        if (accInfoContainer) {
            if (acc && acc.id) {
                accInfoContainer.innerHTML = `
                    <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-zinc-800/60 border border-slate-100 dark:border-zinc-800 space-y-1">
                        <div class="font-bold text-slate-900 dark:text-zinc-100">${escapeHtml(acc.name)}</div>
                        <div class="text-[11px] text-slate-500">${escapeHtml(acc.industry || 'Commercial Enterprise')} &bull; ${escapeHtml(acc.type || 'Customer')}</div>
                        ${acc.phone ? `<div class="text-[11px] font-mono text-slate-600 dark:text-zinc-400">Tel: ${escapeHtml(acc.phone)}</div>` : ''}
                        ${acc.website ? `<a href="${escapeHtml(acc.website)}" target="_blank" class="text-[11px] text-sky-600 hover:underline block truncate">${escapeHtml(acc.website)}</a>` : ''}
                    </div>
                `;
            } else {
                accInfoContainer.innerHTML = '<p class="text-xs text-slate-400">No linked organization for this stakeholder.</p>';
            }
        }

        // Opportunities / Deals
        const allDeals = [...opps, ...deals];
        const badgeDeals = document.getElementById('fs-badge-deals-count');
        if (badgeDeals) badgeDeals.textContent = allDeals.length;

        const dealsContainer = document.getElementById('fs-deals-list-container');
        if (dealsContainer) {
            if (allDeals.length === 0) {
                dealsContainer.innerHTML = '<div class="py-4 text-center text-xs text-slate-400">No deals linked yet.</div>';
            } else {
                dealsContainer.innerHTML = allDeals.map(d => `
                    <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 dark:bg-zinc-800/50 border border-slate-100 dark:border-zinc-800 text-xs">
                        <div class="truncate max-w-[190px]">
                            <div class="font-bold text-slate-900 dark:text-zinc-100 truncate">${escapeHtml(d.name || d.title || 'Deal')}</div>
                            <div class="text-[10px] text-slate-400">${escapeHtml(d.stage || 'In Progress')}</div>
                        </div>
                        <span class="font-bold text-emerald-600 text-[11px]">${d.amount ? '$' + Number(d.amount).toLocaleString() : '—'}</span>
                    </div>
                `).join('');
            }
        }
    }

    // Right Column Tabs Switcher (Related, Activity, Files)
    window.switchFullscreenRightTab = function (tabId) {
        state.active360Tab = tabId;

        if (tabId === 'files' && state.viewing360Contact) {
            window.loadContactFiles(state.viewing360Contact);
        }
        if (tabId === 'activity' && state.viewing360Contact) {
            window.refreshContactActivities(state.viewing360Contact);
        }

        const tabs = ['related', 'activity', 'files'];
        tabs.forEach(t => {
            const btn = document.getElementById(`fs-tab-btn-${t}`);
            const content = document.getElementById(`fs-tab-content-${t}`);
            const badgeId = t === 'related' ? 'fs-count-related' : (t === 'activity' ? 'fs-count-activity' : 'fs-tab-badge-files-count');
            const badge = document.getElementById(badgeId);

            if (t === tabId) {
                if (btn) btn.className = 'px-3.5 py-1.5 text-xs font-semibold rounded-full bg-[#334e68] text-white shadow-2xs flex items-center gap-1.5 cursor-pointer';
                if (badge) badge.className = 'px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-[#486581] text-white ml-0.5';
                if (content) content.classList.remove('hidden');
            } else {
                if (btn) btn.className = 'px-3.5 py-1.5 text-xs font-medium rounded-full text-slate-600 dark:text-zinc-300 hover:text-slate-900 dark:hover:text-white flex items-center gap-1.5 cursor-pointer';
                if (badge) badge.className = 'px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-slate-200 dark:bg-zinc-700 text-slate-700 dark:text-zinc-300 ml-0.5';
                if (content) content.classList.add('hidden');
            }
        });
    };

    window.toggleFsRelatedCard = function (collapseId, chevronId = null) {
        const el = document.getElementById(collapseId);
        if (el) el.classList.toggle('hidden');
        if (chevronId) {
            const chev = document.getElementById(chevronId);
            if (chev) chev.classList.toggle('rotate-180');
        }
    };

    window.toggleFullscreenSection = function (bodyId, chevronId = null) {
        const el = document.getElementById(bodyId);
        if (el) el.classList.toggle('hidden');
        if (chevronId) {
            const chev = document.getElementById(chevronId);
            if (chev) chev.classList.toggle('-rotate-90');
        }
    };

    // ==========================================
    // ACTIVITY SUB-NAV & ACTIONS (Call, Task, Event, Note)
    // ==========================================
    window.switchContactActivityFormType = function (type) {
        const pills = ['call', 'task', 'event', 'note'];
        pills.forEach(p => {
            const pillEl = document.getElementById(`cnt-act-pill-${p}`);
            const viewEl = document.getElementById(`cnt-activity-${p}-view`);
            const isMatch = p === type.toLowerCase();

            if (isMatch) {
                if (pillEl) {
                    pillEl.className = 'cnt-act-type-pill inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-[#f3e8ff] text-[#7c3aed] border border-purple-200 dark:bg-purple-950/50 dark:border-purple-800 dark:text-purple-300 font-semibold text-xs shadow-2xs cursor-pointer transition-all shrink-0';
                }
                if (viewEl) viewEl.classList.remove('hidden');
            } else {
                if (pillEl) {
                    pillEl.className = 'cnt-act-type-pill inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-zinc-300 font-semibold text-xs cursor-pointer transition-all shrink-0';
                }
                if (viewEl) viewEl.classList.add('hidden');
            }
        });
    };

    // Submit Call or Note
    window.submitContactActivity = async function (type) {
        if (!state.viewing360Contact) return;

        let subject = '';
        let details = '';
        let callDateTime = '';
        let btn = null;

        if (type === 'Call') {
            subject = (document.getElementById('cnt-call-input-subject')?.value || '').trim();
            details = document.getElementById('cnt-call-input-details')?.value || '';
            callDateTime = document.getElementById('cnt-call-input-datetime')?.value || '';
            btn = document.getElementById('btn-cnt-call-submit');
            if (!subject) {
                showNotification('Please enter a call subject.', 'error');
                return;
            }
        } else if (type === 'Note') {
            subject = (document.getElementById('cnt-note-input-subject')?.value || '').trim() || 'Quick Note';
            details = (document.getElementById('cnt-note-input-details')?.value || '').trim();
            btn = document.getElementById('btn-cnt-note-submit');
            if (!details) {
                showNotification('Please enter note details.', 'error');
                return;
            }
        }

        if (btn) {
            btn.disabled = true;
            btn.classList.add('opacity-70');
        }

        try {
            const res = await fetch(`/api/contacts/${state.viewing360Contact}/activities`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    type: type,
                    subject: subject,
                    details: details,
                    call_datetime: callDateTime,
                })
            });

            if (res.ok) {
                const data = await res.json();
                showNotification(data.message || `${type} recorded successfully.`, 'success');

                // Reset inputs
                if (type === 'Call') {
                    const subjInput = document.getElementById('cnt-call-input-subject');
                    if (subjInput) subjInput.value = '';
                    const detInput = document.getElementById('cnt-call-input-details');
                    if (detInput) detInput.value = '';
                } else if (type === 'Note') {
                    const subjInput = document.getElementById('cnt-note-input-subject');
                    if (subjInput) subjInput.value = '';
                    const detInput = document.getElementById('cnt-note-input-details');
                    if (detInput) detInput.value = '';
                }

                // Refresh timeline
                window.refreshContactActivities(state.viewing360Contact);
            } else {
                showNotification('Failed to save activity.', 'error');
            }
        } catch (err) {
            console.error('Error submitting contact activity:', err);
            showNotification('Network error while saving activity.', 'error');
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.classList.remove('opacity-70');
            }
        }
    };

    // Submit Event / Meeting
    window.submitContactEvent = async function () {
        if (!state.viewing360Contact) return;

        const subject = (document.getElementById('cnt-event-input-subject')?.value || '').trim();
        const start = document.getElementById('cnt-event-input-start')?.value || '';
        const end = document.getElementById('cnt-event-input-end')?.value || '';
        const details = document.getElementById('cnt-event-input-details')?.value || '';
        const btn = document.getElementById('btn-cnt-event-submit');

        if (!subject) {
            showNotification('Please enter meeting subject.', 'error');
            return;
        }

        if (btn) {
            btn.disabled = true;
            btn.classList.add('opacity-70');
        }

        try {
            const res = await fetch(`/api/contacts/${state.viewing360Contact}/activities`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    type: 'Meeting',
                    subject: subject,
                    start_date: start,
                    end_date: end,
                    details: details,
                })
            });

            if (res.ok) {
                const data = await res.json();
                showNotification(data.message || 'Meeting logged successfully.', 'success');
                const subjInput = document.getElementById('cnt-event-input-subject');
                if (subjInput) subjInput.value = '';
                const detInput = document.getElementById('cnt-event-input-details');
                if (detInput) detInput.value = '';
                window.refreshContactActivities(state.viewing360Contact);
            } else {
                showNotification('Failed to save event.', 'error');
            }
        } catch (err) {
            console.error('Error logging event:', err);
            showNotification('Network error saving event.', 'error');
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.classList.remove('opacity-70');
            }
        }
    };

    // Task Creation Modal for Contact
    window.openContactTaskModal = function () {
        const modal = document.getElementById('contact-task-modal');
        if (modal) modal.classList.remove('hidden');
    };

    window.closeContactTaskModal = function () {
        const modal = document.getElementById('contact-task-modal');
        if (modal) modal.classList.add('hidden');
    };

    window.submitContactTaskModal = async function () {
        if (!state.viewing360Contact) return;

        const subject = (document.getElementById('cnt-task-input-subject')?.value || '').trim();
        const due = document.getElementById('cnt-task-input-due')?.value || '';
        const priority = document.getElementById('cnt-task-input-priority')?.value || 'Normal';
        const comments = document.getElementById('cnt-task-input-comments')?.value || '';

        if (!subject) {
            showNotification('Please enter a task subject.', 'error');
            return;
        }

        try {
            const res = await fetch(`/api/contacts/${state.viewing360Contact}/tasks`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    subject: subject,
                    due_date: due,
                    priority: priority,
                    comments: comments,
                })
            });

            if (res.ok) {
                showNotification('Task created and logged to timeline.', 'success');
                window.closeContactTaskModal();
                const subjInput = document.getElementById('cnt-task-input-subject');
                if (subjInput) subjInput.value = '';
                const comInput = document.getElementById('cnt-task-input-comments');
                if (comInput) comInput.value = '';
                window.refreshContactActivities(state.viewing360Contact);
            } else {
                showNotification('Failed to create task.', 'error');
            }
        } catch (err) {
            console.error('Error creating task:', err);
            showNotification('Network error creating task.', 'error');
        }
    };

    // Refresh Contact Timeline
    window.refreshContactActivities = async function (contactId) {
        if (!contactId) return;

        const list = document.getElementById('contact-activities-timeline-list');
        if (list) list.classList.add('opacity-60');

        try {
            const res = await fetch(`/api/contacts/${contactId}/activities`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            if (res.ok) {
                const data = await res.json();
                state.contactActivities = data.activities || [];
                const count = data.count || state.contactActivities.length;

                const timelineBadge = document.getElementById('contact-timeline-count-badge');
                if (timelineBadge) timelineBadge.textContent = count;
                const tabBadge = document.getElementById('fs-count-activity');
                if (tabBadge) tabBadge.textContent = count;

                if (list) {
                    if (state.contactActivities.length === 0) {
                        list.innerHTML = `
                            <div class="p-6 border-2 border-dashed border-slate-200 dark:border-zinc-700 rounded-xl text-center space-y-1">
                                <p class="text-xs font-medium text-slate-400">No activity logged yet.</p>
                                <p class="text-[11px] text-slate-400">Use the form above to log a call, task, event, or note.</p>
                            </div>
                        `;
                    } else {
                        list.innerHTML = state.contactActivities.map(act => window.renderContactTimelineItemHtml(act)).join('');
                    }
                }
            }
        } catch (err) {
            console.error('Error fetching contact activities:', err);
        } finally {
            if (list) list.classList.remove('opacity-60');
        }
    };

    window.renderContactTimelineItemHtml = function (act) {
        const typeLower = (act.type || 'Call').toLowerCase();
        const isCall = typeLower === 'call';
        const isTask = typeLower === 'task';
        const isEvent = typeLower === 'event' || typeLower === 'meeting';
        const isNote = typeLower === 'note';

        let iconSvg = '';
        let iconBg = '';
        let timeLabel = 'Call Time';

        if (isCall) {
            iconSvg = `<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>`;
            iconBg = 'bg-blue-50 dark:bg-blue-950/60 text-blue-500 dark:text-blue-400';
            timeLabel = 'Call Time';
        } else if (isTask) {
            iconSvg = `<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="m9 12 2 2 4-4"/></svg>`;
            iconBg = 'bg-purple-100 dark:bg-purple-950/70 text-purple-600 dark:text-purple-400';
            timeLabel = 'Due Date';
        } else if (isEvent) {
            iconSvg = `<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>`;
            iconBg = 'bg-purple-100 dark:bg-purple-950/70 text-[#7c3aed] dark:text-purple-300';
            timeLabel = 'Meeting Time';
        } else {
            // Note
            iconSvg = `<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>`;
            iconBg = 'bg-purple-100 dark:bg-purple-950/70 text-[#7c3aed] dark:text-purple-300';
            timeLabel = 'Date';
        }

        const typeDisplayName = isEvent ? 'Meeting' : (isTask ? 'Task' : (isNote ? 'Note' : 'Call'));

        return `
            <div class="bg-white dark:bg-zinc-800/80 rounded-xl border border-slate-200/90 dark:border-zinc-700/80 p-3.5 space-y-2 transition-shadow hover:shadow-xs">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-8 h-8 rounded-full ${iconBg} flex items-center justify-center shrink-0">
                            ${iconSvg}
                        </div>
                        <span class="font-bold text-slate-900 dark:text-zinc-100 text-xs sm:text-sm truncate">
                            ${escapeHtml(act.subject)}
                        </span>
                    </div>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 shrink-0">
                        ${typeDisplayName}
                    </span>
                </div>

                ${act.details ? `
                    <p class="text-xs text-slate-600 dark:text-zinc-300 pl-10 whitespace-pre-wrap leading-relaxed">
                        ${escapeHtml(act.details)}
                    </p>
                ` : ''}

                <div class="flex items-center justify-between text-[11px] text-slate-400 dark:text-zinc-500 pt-1 border-t border-slate-100 dark:border-zinc-700/60 pl-10">
                    <span>${timeLabel}: <strong class="text-slate-600 dark:text-zinc-300">${escapeHtml(act.call_time || act.created_at_formatted)}</strong></span>
                    <span>Logged by <strong class="text-slate-600 dark:text-zinc-300">${escapeHtml(act.user_name || 'Alex Morgan')}</strong></span>
                </div>
            </div>
        `;
    };

    // ==========================================
    // FILES MANAGEMENT (In-Tab Table, No Modal Popup)
    // ==========================================
    window.loadContactFiles = async function (contactId) {
        if (!contactId) return;

        try {
            const res = await fetch(`/api/contacts/${contactId}/files`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            if (res.ok) {
                const data = await res.json();
                state.contactFiles = data.files || [];
                window.renderContactFilesTable(state.contactFiles);
            }
        } catch (err) {
            console.error('Error fetching contact files:', err);
        }
    };

    window.renderContactFilesTable = function (files = []) {
        const count = files.length;

        // Update counts
        const badgeCount = document.getElementById('fs-badge-files-count');
        if (badgeCount) badgeCount.textContent = count;
        const tabBadge = document.getElementById('fs-tab-badge-files-count');
        if (tabBadge) tabBadge.textContent = count;
        const tabViewCount = document.getElementById('fs-tab-view-files-count');
        if (tabViewCount) tabViewCount.textContent = count;
        const viewAllCount = document.getElementById('fs-files-viewall-count');
        if (viewAllCount) viewAllCount.textContent = count;

        // 1. Render files list preview in Related card
        const listContainer = document.getElementById('fs-files-list-container');
        if (listContainer) {
            if (count === 0) {
                listContainer.innerHTML = '<div class="py-4 text-center text-xs text-slate-400">No files uploaded yet. Click <strong>+ Add</strong> to attach files.</div>';
            } else {
                listContainer.innerHTML = files.slice(0, 5).map(f => {
                    let iconSvg = '';
                    const mime = (f.mime_type || '').toLowerCase();
                    const name = (f.name || '').toLowerCase();

                    if (f.icon_type === 'excel' || mime.includes('csv') || name.endsWith('.csv') || name.endsWith('.xlsx')) {
                        iconSvg = '<svg class="w-4 h-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><path d="M8 13h8M8 17h8M10 9h4"/></svg>';
                    } else if (f.is_image || mime.startsWith('image/')) {
                        iconSvg = '<svg class="w-4 h-4 text-teal-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="3" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>';
                    } else if (f.icon_type === 'pdf' || mime.includes('pdf')) {
                        iconSvg = '<svg class="w-4 h-4 text-rose-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><path d="M9 13v4M12 13v4M15 13v4"/></svg>';
                    } else {
                        iconSvg = '<svg class="w-4 h-4 text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>';
                    }

                    return `
                        <div class="flex items-center justify-between py-1.5 px-1.5 rounded-xl hover:bg-slate-50 dark:hover:bg-zinc-800/60 transition-colors group">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-zinc-800 flex items-center justify-center shrink-0 border border-slate-200 dark:border-zinc-700 shadow-2xs">
                                    ${iconSvg}
                                </div>
                                <div class="min-w-0">
                                    <a href="${f.download_url}" download class="font-bold text-xs text-slate-900 dark:text-zinc-100 hover:text-blue-600 truncate block max-w-[210px]">
                                        ${escapeHtml(f.name)}
                                    </a>
                                    <div class="text-[11px] text-slate-400 dark:text-zinc-500 truncate mt-0.5">
                                        ${escapeHtml(f.date_formatted)} &bull; ${escapeHtml(f.size_formatted)}
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-1 shrink-0 text-slate-400">
                                <a href="${f.view_url || f.download_url}" target="_blank" class="p-1 rounded hover:bg-slate-100 dark:hover:bg-zinc-700" title="View">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                </a>
                                <a href="${f.download_url}" download class="p-1 rounded hover:bg-slate-100 dark:hover:bg-zinc-700" title="Download">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                </a>
                                <button type="button" onclick="window.deleteContactFile(${f.id})" class="p-1 rounded hover:text-rose-600 hover:bg-rose-50 cursor-pointer" title="Delete">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/></svg>
                                </button>
                            </div>
                        </div>
                    `;
                }).join('');
            }
        }

        // 2. Render In-Tab Table in #fs-tab-files-tbody
        const tbody = document.getElementById('fs-tab-files-tbody');
        if (!tbody) return;

        if (count === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="9" class="py-10 text-center text-xs text-slate-400">
                        No files or attachments uploaded yet. Click <strong>+ Add File</strong> to upload.
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = files.map(f => {
            let iconSvg = '';
            const mime = (f.mime_type || '').toLowerCase();
            const name = (f.name || '').toLowerCase();

            if (f.icon_type === 'excel' || mime.includes('csv') || mime.includes('spreadsheet') || mime.includes('excel') || name.endsWith('.csv') || name.endsWith('.xlsx')) {
                iconSvg = `
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-spreadsheet mx-auto">
                        <path d="M6 22a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h8a2.4 2.4 0 0 1 1.704.706l3.588 3.588A2.4 2.4 0 0 1 20 8v12a2 2 0 0 1-2 2z"></path>
                        <path d="M14 2v5a1 1 0 0 0 1 1h5"></path>
                        <path d="M8 13h2"></path><path d="M14 13h2"></path><path d="M8 17h2"></path><path d="M14 17h2"></path>
                    </svg>
                `;
            } else if (f.is_image || mime.startsWith('image/')) {
                iconSvg = `
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-image mx-auto">
                        <rect width="18" height="18" x="3" y="3" rx="2" ry="2"></rect>
                        <circle cx="9" cy="9" r="2"></circle>
                        <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"></path>
                    </svg>
                `;
            } else if (f.icon_type === 'pdf' || mime.includes('pdf')) {
                iconSvg = `
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#e11d48" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-text mx-auto">
                        <path d="M6 22a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h8a2.4 2.4 0 0 1 1.704.706l3.588 3.588A2.4 2.4 0 0 1 20 8v12a2 2 0 0 1-2 2z"></path>
                        <path d="M14 2v5a1 1 0 0 0 1 1h5"></path>
                        <path d="M9 13v4"></path><path d="M12 13v4"></path><path d="M15 13v4"></path>
                    </svg>
                `;
            } else {
                iconSvg = `
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file mx-auto">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                    </svg>
                `;
            }

            return `
                <tr class="hover:bg-slate-50 dark:hover:bg-zinc-800/40 transition-colors">
                    <td class="w-10 px-3 py-2.5 text-center align-middle">
                        ${iconSvg}
                    </td>
                    <td class="px-3 py-2.5 align-middle">
                        <div class="font-semibold text-blue-600 dark:text-sky-400 hover:underline cursor-pointer truncate max-w-[240px]" onclick="window.open('${f.view_url || f.download_url}', '_blank')">
                            ${escapeHtml(f.name)}
                        </div>
                    </td>
                    <td class="px-3 py-2.5 text-zinc-600 dark:text-zinc-400 text-[0.725rem] align-middle font-mono uppercase tracking-tight max-w-[160px] truncate" title="${escapeHtml(f.type_label)}">
                        ${escapeHtml(f.type_label || 'FILE')}
                    </td>
                    <td class="px-3 py-2.5 text-zinc-600 dark:text-zinc-400 text-xs align-middle whitespace-nowrap">
                        ${escapeHtml(f.size_formatted)}
                    </td>
                    <td class="px-3 py-2.5 align-middle whitespace-nowrap">
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[0.675rem] font-bold bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700 cursor-pointer" title="Click to view Version History">
                            v1
                        </span>
                    </td>
                    <td class="px-3 py-2.5 text-zinc-700 dark:text-zinc-300 text-xs align-middle whitespace-nowrap">
                        ${escapeHtml(f.created_by_name || 'Alex Morgan')}
                    </td>
                    <td class="px-3 py-2.5 text-zinc-500 dark:text-zinc-400 text-[0.725rem] align-middle whitespace-nowrap">
                        ${escapeHtml(f.date_formatted)}
                    </td>
                    <td class="px-3 py-2.5 align-middle whitespace-nowrap">
                        <span class="text-[0.7rem] text-zinc-500 dark:text-zinc-400 bg-zinc-100 dark:bg-zinc-800 px-1.5 py-0.5 rounded font-medium">
                            1 record(s)
                        </span>
                    </td>
                    <td class="w-24 px-3 py-2.5 text-right align-middle whitespace-nowrap">
                        <div class="inline-flex items-center justify-end gap-1">
                            <a href="${f.view_url || f.download_url}" target="_blank" class="p-1 rounded text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-100 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors" title="Preview Document">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-eye">
                                    <path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </a>
                            <a href="${f.download_url}" download="${escapeHtml(f.name)}" class="p-1 rounded text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-100 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors" title="Download">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-download">
                                    <path d="M12 15V3"></path>
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    <path d="m7 10 5 5 5-5"></path>
                                </svg>
                            </a>
                            <button type="button" onclick="window.deleteContactFile(${f.id})" class="p-1 rounded text-zinc-500 hover:text-rose-600 hover:bg-rose-50 cursor-pointer" title="Delete file">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-ellipsis-vertical">
                                    <circle cx="12" cy="12" r="1"></circle><circle cx="12" cy="5" r="1"></circle><circle cx="12" cy="19" r="1"></circle>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    };

    window.triggerFileUploadForContact = function () {
        const input = document.getElementById('contact-file-upload-input');
        if (input) input.click();
    };

    window.handleContactFileUpload = async function (e) {
        const files = e.target.files;
        if (!files || files.length === 0 || !state.viewing360Contact) return;

        const indicator = document.getElementById('contact-file-uploading-indicator');
        if (indicator) indicator.classList.remove('hidden');

        const formData = new FormData();
        for (let i = 0; i < files.length; i++) {
            formData.append('files[]', files[i]);
        }

        try {
            const res = await fetch(`/api/contacts/${state.viewing360Contact}/files`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData,
            });

            if (res.ok) {
                const data = await res.json();
                showNotification(data.message || 'Files uploaded successfully.', 'success');
                window.loadContactFiles(state.viewing360Contact);
            } else {
                showNotification('Upload failed. Please check file type and size.', 'error');
            }
        } catch (err) {
            console.error('File upload error:', err);
            showNotification('Network error during upload.', 'error');
        } finally {
            if (indicator) indicator.classList.add('hidden');
            e.target.value = '';
        }
    };

    window.deleteContactFile = async function (fileId) {
        if (!state.viewing360Contact || !confirm('Are you sure you want to delete this file?')) return;

        try {
            const res = await fetch(`/api/contacts/${state.viewing360Contact}/files/${fileId}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            if (res.ok) {
                showNotification('File deleted.', 'success');
                window.loadContactFiles(state.viewing360Contact);
            } else {
                showNotification('Failed to delete file.', 'error');
            }
        } catch (err) {
            console.error('Delete file error:', err);
        }
    };

    // ==========================================
    // REASSIGN OWNER & DELETE ACTIONS
    // ==========================================
    window.openTransferContactFromFullscreen = function () {
        if (!state.viewing360Contact) return;
        const modal = document.getElementById('contact-transfer-modal');
        if (modal) modal.classList.remove('hidden');
    };

    window.closeTransferContactModal = function () {
        const modal = document.getElementById('contact-transfer-modal');
        if (modal) modal.classList.add('hidden');
    };

    window.submitTransferContact = async function () {
        if (!state.viewing360Contact) return;
        const select = document.getElementById('transfer-contact-user-select');
        const newUserId = select ? select.value : null;
        if (!newUserId) return;

        try {
            const res = await fetch(`/api/contacts/${state.viewing360Contact}/transfer`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ assigned_to: newUserId })
            });

            if (res.ok) {
                const data = await res.json();
                showNotification(data.message || 'Owner reassigned successfully.', 'success');
                window.closeTransferContactModal();

                // Update contact in memory
                const c = state.contacts.find(x => x.id == state.viewing360Contact);
                if (c && data.contact) {
                    Object.assign(c, data.contact);
                }

                renderContactsView();
                window.openFullscreenContactDetail(state.viewing360Contact);
            } else {
                showNotification('Failed to reassign owner.', 'error');
            }
        } catch (err) {
            console.error('Transfer error:', err);
        }
    };

    window.editContactFromFullscreen = function () {
        if (state.viewing360Contact) {
            window.openContactModal('edit', state.viewing360Contact);
        }
    };

    window.deleteContactFromFullscreen = async function () {
        if (!state.viewing360Contact) return;
        if (!confirm('Are you sure you want to permanently delete this contact?')) return;

        try {
            const res = await fetch(`/api/contacts/${state.viewing360Contact}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            if (res.ok) {
                showNotification('Contact deleted successfully.', 'success');
                state.contacts = state.contacts.filter(c => c.id != state.viewing360Contact);
                window.closeContact360Modal();
                renderContactsView();
            } else {
                showNotification('Failed to delete contact.', 'error');
            }
        } catch (err) {
            console.error('Delete error:', err);
        }
    };

    // ==========================================
    // CUSTOMIZE COLUMNS MODAL
    // ==========================================
    window.openCustomizeFieldsModal = function () {
        const modal = document.getElementById('contact-customize-modal');
        if (!modal) return;
        modal.classList.remove('hidden');
        renderCustomizeFieldsList();
    };

    window.closeCustomizeFieldsModal = function () {
        const modal = document.getElementById('contact-customize-modal');
        if (modal) modal.classList.add('hidden');
    };

    function renderCustomizeFieldsList(searchTerm = '') {
        const container = document.getElementById('customize-fields-list-container');
        if (!container) return;

        const fieldDefs = getVisibleFieldDefinitions();
        const search = (searchTerm || '').toLowerCase();

        const filteredDefs = fieldDefs.filter(f => {
            if (!search) return true;
            return f.label.toLowerCase().includes(search) || f.name.toLowerCase().includes(search);
        });

        // Split into visible (in order) and available
        const currentVisibleKeys = state.tableVisibleFields;

        container.innerHTML = `
            <div class="space-y-2">
                ${filteredDefs.map(f => {
                    const isVisible = currentVisibleKeys.includes(f.name);
                    const idx = currentVisibleKeys.indexOf(f.name);

                    return `
                        <div class="flex items-center justify-between p-3 rounded-xl border ${isVisible ? 'border-purple-200 dark:border-purple-800 bg-purple-50/40 dark:bg-purple-950/20' : 'border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-800'} transition-colors">
                            <div class="flex items-center gap-3">
                                <input
                                    type="checkbox"
                                    id="cust-col-chk-${f.name}"
                                    value="${f.name}"
                                    ${isVisible ? 'checked' : ''}
                                    onchange="window.toggleCustomizeField('${f.name}', this.checked)"
                                    class="rounded border-slate-300 text-purple-600 focus:ring-purple-500 w-4 h-4 cursor-pointer"
                                >
                                <div>
                                    <label for="cust-col-chk-${f.name}" class="font-bold text-xs text-slate-800 dark:text-zinc-200 cursor-pointer">
                                        ${escapeHtml(f.label)}
                                    </label>
                                    <div class="text-[10px] text-slate-400 font-mono">
                                        ${escapeHtml(f.name)} &bull; ${f.isCustom ? '<span class="text-purple-600 font-bold">Custom Field</span>' : 'Standard Field'}
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-1">
                                ${isVisible ? `
                                    <button
                                        type="button"
                                        onclick="window.moveCustomizeField('${f.name}', -1)"
                                        ${idx === 0 ? 'disabled class="opacity-30 p-1 text-slate-400"' : 'class="p-1 text-slate-500 hover:text-slate-900 cursor-pointer"'}
                                        title="Move up"
                                    >
                                        &uarr;
                                    </button>
                                    <button
                                        type="button"
                                        onclick="window.moveCustomizeField('${f.name}', 1)"
                                        ${idx === currentVisibleKeys.length - 1 ? 'disabled class="opacity-30 p-1 text-slate-400"' : 'class="p-1 text-slate-500 hover:text-slate-900 cursor-pointer"'}
                                        title="Move down"
                                    >
                                        &darr;
                                    </button>
                                ` : ''}
                            </div>
                        </div>
                    `;
                }).join('')}
            </div>
        `;

        const footerCount = document.getElementById('customize-fields-footer-count');
        if (footerCount) {
            footerCount.innerHTML = `Showing <strong>${state.tableVisibleFields.length}</strong> of ${fieldDefs.length} columns in table`;
        }
    }

    window.filterCustomizeFieldsList = function (term) {
        renderCustomizeFieldsList(term);
    };

    window.toggleCustomizeField = function (fieldName, isChecked) {
        if (isChecked) {
            if (!state.tableVisibleFields.includes(fieldName)) {
                state.tableVisibleFields.push(fieldName);
            }
        } else {
            state.tableVisibleFields = state.tableVisibleFields.filter(k => k !== fieldName);
        }
        renderCustomizeFieldsList(document.getElementById('customize-fields-search')?.value || '');
    };

    window.moveCustomizeField = function (fieldName, dir) {
        const idx = state.tableVisibleFields.indexOf(fieldName);
        if (idx === -1) return;
        const newIdx = idx + dir;
        if (newIdx < 0 || newIdx >= state.tableVisibleFields.length) return;

        const temp = state.tableVisibleFields[idx];
        state.tableVisibleFields[idx] = state.tableVisibleFields[newIdx];
        state.tableVisibleFields[newIdx] = temp;

        renderCustomizeFieldsList(document.getElementById('customize-fields-search')?.value || '');
    };

    window.customizeSelectAllFields = function () {
        const fieldDefs = getVisibleFieldDefinitions();
        state.tableVisibleFields = fieldDefs.map(f => f.name);
        renderCustomizeFieldsList();
    };

    window.customizeDeselectAllFields = function () {
        state.tableVisibleFields = ['name']; // Keep name as minimal required
        renderCustomizeFieldsList();
    };

    window.customizeResetFields = function () {
        state.tableVisibleFields = ['assigned_to', 'name', 'account_name', 'contact_role', 'email', 'phone'];
        state.colWidths = { ...DEFAULT_COL_WIDTHS };
        saveColumnWidths();
        saveViewConfigs();
        renderCustomizeFieldsList();
    };

    window.saveCustomizedFields = function () {
        saveViewConfigs();
        window.closeCustomizeFieldsModal();
        renderContactsView();
        showNotification('Table column layout updated.', 'success');
    };

    // ==========================================
    // FILTER MODAL & CONDITIONS BUILDER
    // ==========================================
    window.openContactFilterModal = function () {
        const modal = document.getElementById('contact-filter-modal');
        if (!modal) return;
        modal.classList.remove('hidden');

        // Copy active filters into temp condition rows
        state.tempFilterConditions = state.activeFilters.length > 0
            ? JSON.parse(JSON.stringify(state.activeFilters))
            : [{ id: Date.now(), field: 'name', operator: 'contains', value: '' }];

        renderFilterConditionsList();
    };

    window.closeContactFilterModal = function () {
        const modal = document.getElementById('contact-filter-modal');
        if (modal) modal.classList.add('hidden');
    };

    function renderFilterConditionsList() {
        const list = document.getElementById('contact-filter-conditions-list');
        if (!list) return;

        const fieldDefs = getVisibleFieldDefinitions();

        list.innerHTML = state.tempFilterConditions.map((cond, i) => `
            <div class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 dark:border-zinc-700 bg-slate-50/50 dark:bg-zinc-800/40">
                <span class="text-xs font-bold text-slate-400 w-5 text-center">${i + 1}.</span>
                <select
                    onchange="window.updateTempFilterCondition(${cond.id}, 'field', this.value)"
                    class="flex-1 px-2.5 py-1.5 text-xs rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-zinc-100"
                >
                    ${fieldDefs.map(f => `
                        <option value="${f.name}" ${cond.field === f.name ? 'selected' : ''}>${escapeHtml(f.label)}</option>
                    `).join('')}
                </select>

                <select
                    onchange="window.updateTempFilterCondition(${cond.id}, 'operator', this.value)"
                    class="w-32 px-2.5 py-1.5 text-xs rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-zinc-100"
                >
                    <option value="contains" ${cond.operator === 'contains' ? 'selected' : ''}>contains</option>
                    <option value="equals" ${cond.operator === 'equals' ? 'selected' : ''}>equals</option>
                    <option value="starts_with" ${cond.operator === 'starts_with' ? 'selected' : ''}>starts with</option>
                    <option value="greater_than" ${cond.operator === 'greater_than' ? 'selected' : ''}>greater than (&gt;)</option>
                    <option value="less_than" ${cond.operator === 'less_than' ? 'selected' : ''}>less than (&lt;)</option>
                    <option value="is_empty" ${cond.operator === 'is_empty' ? 'selected' : ''}>is empty</option>
                    <option value="is_not_empty" ${cond.operator === 'is_not_empty' ? 'selected' : ''}>is not empty</option>
                </select>

                <input
                    type="text"
                    value="${escapeHtml(cond.value || '')}"
                    placeholder="Value..."
                    oninput="window.updateTempFilterCondition(${cond.id}, 'value', this.value)"
                    class="flex-1 px-2.5 py-1.5 text-xs rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-zinc-100"
                >

                <button
                    type="button"
                    onclick="window.removeTempFilterCondition(${cond.id})"
                    class="p-1 text-slate-400 hover:text-red-600 cursor-pointer"
                    title="Remove rule"
                >
                    &times;
                </button>
            </div>
        `).join('');

        const countEl = document.getElementById('modal-filter-apply-count');
        if (countEl) countEl.textContent = state.tempFilterConditions.filter(c => c.value || c.operator === 'is_empty' || c.operator === 'is_not_empty').length;
    }

    window.addFilterConditionRow = function () {
        state.tempFilterConditions.push({
            id: Date.now(),
            field: 'name',
            operator: 'contains',
            value: '',
        });
        renderFilterConditionsList();
    };

    window.updateTempFilterCondition = function (id, prop, val) {
        const cond = state.tempFilterConditions.find(c => c.id === id);
        if (cond) cond[prop] = val;
        const countEl = document.getElementById('modal-filter-apply-count');
        if (countEl) countEl.textContent = state.tempFilterConditions.filter(c => c.value || c.operator === 'is_empty' || c.operator === 'is_not_empty').length;
    };

    window.removeTempFilterCondition = function (id) {
        state.tempFilterConditions = state.tempFilterConditions.filter(c => c.id !== id);
        if (state.tempFilterConditions.length === 0) {
            state.tempFilterConditions.push({ id: Date.now(), field: 'name', operator: 'contains', value: '' });
        }
        renderFilterConditionsList();
    };

    window.clearAllContactFiltersFromModal = function () {
        state.tempFilterConditions = [{ id: Date.now(), field: 'name', operator: 'contains', value: '' }];
        state.activeFilters = [];
        renderFilterConditionsList();
        window.closeContactFilterModal();
        renderContactsView();
        showNotification('Filters cleared.', 'info');
    };

    window.applyContactFiltersFromModal = function () {
        state.activeFilters = state.tempFilterConditions.filter(c => (c.value && c.value.trim() !== '') || c.operator === 'is_empty' || c.operator === 'is_not_empty');
        window.closeContactFilterModal();
        renderContactsView();
        showNotification(`Applied ${state.activeFilters.length} filter condition${state.activeFilters.length !== 1 ? 's' : ''}.`, 'success');
    };

    window.removeContactFilterRule = function (ruleId) {
        state.activeFilters = state.activeFilters.filter(r => r.id != ruleId);
        renderContactsView();
    };

    window.clearAllContactFilters = function () {
        state.activeFilters = [];
        renderContactsView();
    };

    // ==========================================
    // CREATE / EDIT CONTACT MODAL
    // ==========================================
    window.openContactModal = function (mode = 'create', contactId = null) {
        state.isCreateMode = mode === 'create';
        const modal = document.getElementById('contact-form-modal');
        const titleEl = document.getElementById('modal-contact-form-title');
        const submitBtn = document.getElementById('btn-submit-contact-form');
        const form = document.getElementById('contact-record-form');

        if (!modal || !form) return;

        form.reset();

        if (mode === 'create') {
            state.editingContact = null;
            if (titleEl) titleEl.textContent = 'Add New Stakeholder Contact';
            if (submitBtn) submitBtn.textContent = 'Create Contact';
        } else {
            const c = state.contacts.find(x => x.id == contactId);
            if (!c) return;
            state.editingContact = c;
            if (titleEl) titleEl.textContent = `Edit Contact: ${c.name || ''}`;
            if (submitBtn) submitBtn.textContent = 'Save Changes';

            // Populate standard fields
            const fields = ['first_name', 'last_name', 'email', 'phone', 'account_id', 'title', 'department', 'role', 'assigned_to', 'notes'];
            fields.forEach(f => {
                const el = document.getElementById(`contact-field-${f}`);
                if (el) {
                    if (f === 'role') {
                        el.value = c.contact_role || 'Decision Maker';
                    } else {
                        el.value = c[f] || '';
                    }
                }
            });

            const chkPrimary = document.getElementById('contact-field-is_primary');
            if (chkPrimary) chkPrimary.checked = Boolean(c.is_primary);

            const chkOptOut = document.getElementById('contact-field-email_opt_out');
            if (chkOptOut) chkOptOut.checked = Boolean(c.email_opt_out);
        }

        modal.classList.remove('hidden');
    };

    window.closeContactModal = function () {
        const modal = document.getElementById('contact-form-modal');
        if (modal) modal.classList.add('hidden');
        state.editingContact = null;
    };

    window.handleContactFormSubmit = async function (e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);

        const payload = {
            first_name: formData.get('first_name'),
            last_name: formData.get('last_name'),
            email: formData.get('email'),
            phone: formData.get('phone'),
            account_id: formData.get('account_id') || null,
            title: formData.get('title'),
            department: formData.get('department'),
            contact_role: formData.get('contact_role'),
            assigned_to: formData.get('assigned_to') || null,
            notes: formData.get('notes'),
            is_primary: document.getElementById('contact-field-is_primary')?.checked ? 1 : 0,
            email_opt_out: document.getElementById('contact-field-email_opt_out')?.checked ? 1 : 0,
        };

        const isEdit = !state.isCreateMode && state.editingContact;
        const url = isEdit ? `/api/contacts/${state.editingContact.id}` : '/api/contacts';
        const method = isEdit ? 'PUT' : 'POST';

        try {
            const res = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(payload)
            });

            if (res.ok) {
                const data = await res.json();
                showNotification(data.message || (isEdit ? 'Contact updated.' : 'Contact created.'), 'success');
                window.closeContactModal();

                if (isEdit) {
                    const idx = state.contacts.findIndex(c => c.id == state.editingContact.id);
                    if (idx !== -1 && data.contact) {
                        state.contacts[idx] = data.contact;
                    }
                    if (state.viewing360Contact == state.editingContact.id) {
                        window.openFullscreenContactDetail(state.viewing360Contact);
                    }
                } else if (data.contact) {
                    state.contacts.unshift(data.contact);
                }

                renderContactsView();
            } else {
                const errData = await res.json();
                showNotification(errData.message || 'Validation error saving contact.', 'error');
            }
        } catch (err) {
            console.error('Contact form submission error:', err);
            showNotification('Network error saving contact.', 'error');
        }
    };

    window.deleteContact = async function (id) {
        if (!confirm('Are you sure you want to delete this stakeholder contact?')) return;

        try {
            const res = await fetch(`/api/contacts/${id}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            if (res.ok) {
                showNotification('Contact deleted.', 'success');
                state.contacts = state.contacts.filter(c => c.id != id);
                renderContactsView();
            } else {
                showNotification('Failed to delete contact.', 'error');
            }
        } catch (err) {
            console.error('Delete contact error:', err);
        }
    };

    // Quick Preview Modal
    window.openContactPreviewModal = function (contactId) {
        const c = state.contacts.find(x => x.id == contactId);
        if (!c) return;

        const modal = document.getElementById('contact-preview-modal');
        if (!modal) return;

        const nameEl = document.getElementById('preview-contact-name');
        if (nameEl) nameEl.textContent = c.name || `${c.first_name || ''} ${c.last_name || ''}`;
        const roleEl = document.getElementById('preview-contact-role');
        if (roleEl) roleEl.innerHTML = getRoleBadge(c.contact_role);
        const accEl = document.getElementById('preview-contact-account');
        if (accEl) accEl.textContent = c.account_name || 'Unlinked';
        const titleEl = document.getElementById('preview-contact-title');
        if (titleEl) titleEl.textContent = c.title || '—';
        const deptEl = document.getElementById('preview-contact-department');
        if (deptEl) deptEl.textContent = c.department || '—';
        const emailEl = document.getElementById('preview-contact-email');
        if (emailEl) emailEl.textContent = c.email || '—';
        const phoneEl = document.getElementById('preview-contact-phone');
        if (phoneEl) phoneEl.textContent = c.phone || '—';
        const notesEl = document.getElementById('preview-contact-notes');
        if (notesEl) notesEl.textContent = c.notes || 'No notes added.';

        const fullBtn = document.getElementById('btn-preview-full-360');
        if (fullBtn) {
            fullBtn.onclick = function () {
                window.closeContactPreviewModal();
                window.openFullscreenContactDetail(c.id);
            };
        }

        modal.classList.remove('hidden');
    };

    window.closeContactPreviewModal = function () {
        const modal = document.getElementById('contact-preview-modal');
        if (modal) modal.classList.add('hidden');
    };

    // Export CSV
    window.exportContactsCsv = function () {
        const contacts = getFilteredContacts();
        if (contacts.length === 0) {
            showNotification('No contact records to export.', 'error');
            return;
        }

        const headers = ['Full Name', 'Account', 'Buying Role', 'Title', 'Department', 'Email', 'Phone', 'Primary Contact', 'Owner'];
        const rows = contacts.map(c => [
            `"${(c.name || `${c.first_name || ''} ${c.last_name || ''}`).replace(/"/g, '""')}"`,
            `"${(c.account_name || '').replace(/"/g, '""')}"`,
            `"${(c.contact_role || '').replace(/"/g, '""')}"`,
            `"${(c.title || '').replace(/"/g, '""')}"`,
            `"${(c.department || '').replace(/"/g, '""')}"`,
            `"${(c.email || '').replace(/"/g, '""')}"`,
            `"${(c.phone || '').replace(/"/g, '""')}"`,
            `"${c.is_primary ? 'Yes' : 'No'}"`,
            `"${(c.owner_name || '').replace(/"/g, '""')}"`,
        ]);

        const csvContent = 'data:text/csv;charset=utf-8,' + [headers.join(','), ...rows.map(r => r.join(','))].join('\n');
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement('a');
        link.setAttribute('href', encodedUri);
        link.setAttribute('download', `dealsync_contacts_${new Date().toISOString().slice(0, 10)}.csv`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    };

    // Initialize Module
    function initModule() {
        const data = window.__CONTACTS_DATA__ || {};
        state.contacts = data.initialContacts || [];
        state.customFields = data.customFields || [];
        state.fieldPermissions = data.fieldPermissions || {};
        state.accounts = data.accounts || [];
        state.users = data.users || [];
        state.roles = data.roles || ['Decision Maker', 'Champion', 'Influencer', 'Evaluator'];
        state.canSendBulkEmail = data.canSendBulkEmail ?? true;

        loadSavedViewConfigs();

        // Search input binding
        const searchInput = document.getElementById('contacts-search-input');
        if (searchInput) {
            searchInput.addEventListener('input', function (e) {
                state.searchTerm = e.target.value;
                renderContactsView();
            });
        }

        renderContactsView();

        // Re-render when company profile theme colors update
        window.addEventListener('dealsync:theme-updated', function (e) {
            const colors = (e && e.detail) || {};
            const toolbar = document.getElementById('contacts-toolbar-card');
            if (toolbar) {
                if (colors.table_bg_color) toolbar.style.backgroundColor = colors.table_bg_color;
                if (colors.font_color) toolbar.style.color = colors.font_color;
            }
            const searchInput = document.getElementById('contacts-search-input');
            if (searchInput && colors.font_color) {
                searchInput.style.color = colors.font_color;
            }
            renderContactsView();
        });
        window.addEventListener('storage', function (e) {
            if (e.key && (e.key === 'dealsync_table_bg_color' || e.key === 'dealsync_font_color' || e.key === 'dealsync_accent_color')) {
                renderContactsView();
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initModule);
    } else {
        initModule();
    }
})();
