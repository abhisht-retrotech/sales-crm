/**
 * DealSync Enterprise CRM — Contacts & Stakeholders Module
 * 1:1 Parity with React Client (ContactsView.tsx, CompactDataTable.tsx, UniversalKanban.tsx)
 * Pure Tailwind CSS styling with Dynamic Custom Fields & Field-Level Security Permissions
 */

(function () {
    'use strict';

    // Module State
    const state = {
        contacts: [],
        customFields: [],
        fieldPermissions: {},
        accounts: [],
        users: [],
        roles: ['Decision Maker', 'Champion', 'Influencer', 'Evaluator'],
        activeRole: 'all',
        searchTerm: '',
        viewMode: 'table', // 'table' | 'kanban'
        selectedContactIds: new Set(),
        tableVisibleFields: ['name', 'account_name', 'contact_role', 'email', 'phone', 'title'],
        kanbanVisibleFields: ['account_name', 'email', 'phone', 'title'],
        activeFilters: [], // array of { id, field, operator, value }
        selectedContact: null,
        editingContact: null,
        transferringContact: null,
        previewContact: null,
        isCreateMode: false,
        canSendBulkEmail: true,
        isLoading: false,
    };

    const STANDARD_FIELD_DEFINITIONS = [
        { name: 'name', label: 'Full Name', type: 'text', standard: true },
        { name: 'first_name', label: 'First Name', type: 'text', standard: true, required: true },
        { name: 'last_name', label: 'Last Name', type: 'text', standard: true, required: true },
        { name: 'account_name', label: 'Account Organization', type: 'lookup', standard: true },
        { name: 'title', label: 'Job Title', type: 'text', standard: true },
        { name: 'department', label: 'Department', type: 'text', standard: true },
        { name: 'email', label: 'Email Address', type: 'email', standard: true, required: true },
        { name: 'phone', label: 'Phone Number', type: 'phone', standard: true },
        { name: 'contact_role', label: 'Buying Role', type: 'dropdown', standard: true, options: ['Decision Maker', 'Champion', 'Influencer', 'Evaluator'] },
        { name: 'email_opt_out', label: 'Email Opt Out', type: 'boolean', standard: true },
        { name: 'assigned_to', label: 'Owner / Representative', type: 'lookup', standard: true },
        { name: 'notes', label: 'Stakeholder Context & Notes', type: 'textarea', standard: true },
        { name: 'created_at', label: 'Created Date', type: 'date', standard: true, readOnly: true },
    ];

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

    // Helper: Get Field Permission ('Read/Write', 'Read-Only', 'Hidden')
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

    // Load visible fields from localStorage
    function loadSavedViewConfigs() {
        try {
            const savedTable = localStorage.getItem('dealsync_contacts_table_cols');
            if (savedTable) {
                const parsed = JSON.parse(savedTable);
                if (Array.isArray(parsed) && parsed.length > 0) {
                    state.tableVisibleFields = parsed;
                }
            }
            const savedKanban = localStorage.getItem('dealsync_contacts_kanban_cols');
            if (savedKanban) {
                const parsed = JSON.parse(savedKanban);
                if (Array.isArray(parsed) && parsed.length > 0) {
                    state.kanbanVisibleFields = parsed;
                }
            }
        } catch (_) {}
    }

    function saveViewConfigs() {
        try {
            localStorage.setItem('dealsync_contacts_table_cols', JSON.stringify(state.tableVisibleFields));
            localStorage.setItem('dealsync_contacts_kanban_cols', JSON.stringify(state.kanbanVisibleFields));
        } catch (_) {}
    }

    // Filter Contacts
    function getFilteredContacts() {
        return state.contacts.filter(c => {
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
                if (!matchName && !matchEmail && !matchAccount && !matchTitle && !matchPhone) {
                    return false;
                }
            }

            // 3. Dynamic Filter Rules
            for (const rule of state.activeFilters) {
                const val = (c[rule.field] !== undefined && c[rule.field] !== null) ? String(c[rule.field]).toLowerCase() : '';
                const target = String(rule.value || '').toLowerCase();

                if (rule.operator === 'equals' && val !== target) return false;
                if (rule.operator === 'contains' && !val.includes(target)) return false;
                if (rule.operator === 'starts_with' && !val.startsWith(target)) return false;
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
                return `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">${role || 'Stakeholder'}</span>`;
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
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i> Active Filters:
                </span>
                ${state.activeFilters.map(r => {
                    const f = fieldDefs.find(x => x.name === r.field);
                    const label = f ? f.label : r.field;
                    return `
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-white dark:bg-zinc-800 text-blue-800 dark:text-blue-200 border border-blue-200 dark:border-blue-800 text-[11px] shadow-2xs">
                            <span class="font-medium">${label}</span>
                            <span class="text-zinc-400 font-mono">${r.operator}</span>
                            <span class="font-semibold text-zinc-900 dark:text-zinc-100">"${r.value}"</span>
                            <button type="button" onclick="window.removeContactFilterRule('${r.id}')" class="text-zinc-400 hover:text-red-600 transition-colors cursor-pointer">
                                ✕
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

        if (window.lucide) lucide.createIcons();
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

    // Helper: Render Cell in Table
    function renderFieldCell(c, fieldName, fieldDef) {
        if (getPermission(fieldName) === 'Hidden' || c['_hidden_' + fieldName]) {
            return '<span class="text-zinc-400 italic text-[11px]">Hidden</span>';
        }

        const fullName = c.name || `${c.first_name || ''} ${c.last_name || ''}`.trim() || 'Unnamed Contact';

        if (fieldName === 'name') {
            const initial = (c.first_name || 'C').charAt(0).toUpperCase();
            return `
                <div class="flex items-center gap-2 max-w-[260px]">
                    <div class="w-7 h-7 rounded-lg bg-indigo-50 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-300 flex items-center justify-center shrink-0 font-bold text-xs border border-indigo-200 dark:border-indigo-800">
                        ${initial}
                    </div>
                    <div class="truncate">
                        <div class="flex items-center gap-1.5">
                            <a href="/contacts/${c.id}" onclick="event.preventDefault(); window.navigateToContact(${c.id})" class="font-bold text-zinc-900 dark:text-zinc-100 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                ${fullName}
                            </a>
                            ${c.is_primary ? '<span class="text-amber-500 text-[10px]" title="Primary Account Contact">⭐</span>' : ''}
                        </div>
                        ${c.title ? `<div class="text-[11px] text-zinc-400 truncate">${c.title}</div>` : ''}
                    </div>
                </div>
            `;
        }

        if (fieldName === 'account_name') {
            if (!c.account_name || c.account_name === 'Unlinked') {
                return '<span class="text-zinc-400">—</span>';
            }
            return `
                <div class="flex items-center gap-1.5 text-xs text-zinc-700 dark:text-zinc-300">
                    <i data-lucide="building-2" class="w-3.5 h-3.5 text-zinc-400"></i>
                    <span class="font-medium truncate max-w-[150px]">${c.account_name}</span>
                </div>
            `;
        }

        if (fieldName === 'contact_role') {
            return getRoleBadge(c.contact_role);
        }

        if (fieldName === 'email') {
            return c.email
                ? `<a href="mailto:${c.email}" onclick="event.stopPropagation()" class="text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1 text-xs truncate max-w-[180px]">
                    <i data-lucide="mail" class="w-3 h-3 text-zinc-400 shrink-0"></i>
                    <span class="truncate">${c.email}</span>
                   </a>`
                : '<span class="text-zinc-400">—</span>';
        }

        if (fieldName === 'phone') {
            return c.phone
                ? `<a href="tel:${c.phone}" onclick="event.stopPropagation()" class="font-mono text-xs text-zinc-600 dark:text-zinc-300 hover:text-indigo-600 truncate">
                    ${c.phone}
                   </a>`
                : '<span class="text-zinc-400">—</span>';
        }

        if (fieldName === 'assigned_to') {
            const owner = c.owner_name || 'Unassigned';
            return `
                <div class="flex items-center gap-1.5 text-xs text-zinc-700 dark:text-zinc-300">
                    <span class="w-5 h-5 rounded-full bg-blue-100 text-blue-700 font-bold text-[10px] inline-flex items-center justify-center">
                        ${owner.charAt(0).toUpperCase()}
                    </span>
                    <span class="truncate max-w-[110px]">${owner}</span>
                </div>
            `;
        }

        if (fieldName === 'created_at') {
            const val = c.created_at;
            if (!val) return '<span class="text-zinc-400">—</span>';
            return `<span class="text-xs text-zinc-500">${new Date(val).toLocaleDateString()}</span>`;
        }

        // Custom fields
        if (fieldDef && fieldDef.isCustom) {
            const val = c[fieldName];
            if (val === undefined || val === null || val === '') return '<span class="text-zinc-400">—</span>';
            return `<span class="text-xs text-zinc-700 dark:text-zinc-300">${val}</span>`;
        }

        return `<span class="text-xs text-zinc-700 dark:text-zinc-300 truncate">${c[fieldName] || '—'}</span>`;
    }

    // Render Table View
    function renderTableView(contacts) {
        const tableRoot = document.getElementById('contacts-table-view-root');
        if (!tableRoot) return;

        const fieldDefs = getVisibleFieldDefinitions();
        const visibleCols = state.tableVisibleFields
            .map(key => fieldDefs.find(f => f.name === key))
            .filter(Boolean);

        if (contacts.length === 0) {
            tableRoot.innerHTML = `
                <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 p-12 text-center shadow-xs">
                    <div class="w-12 h-12 rounded-xl bg-zinc-100 dark:bg-zinc-800 text-zinc-400 flex items-center justify-center mx-auto mb-3">
                        <i data-lucide="users" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 mb-1">No contacts found</h3>
                    <p class="text-xs text-zinc-500 max-w-sm mx-auto mb-4">
                        There are no contacts matching your current search, role filters, or security profile permissions.
                    </p>
                    <button
                        type="button"
                        onclick="window.openContactModal('create')"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm transition-colors cursor-pointer"
                    >
                        <i data-lucide="plus" class="w-4 h-4"></i> Add Contact
                    </button>
                </div>
            `;
            if (window.lucide) lucide.createIcons();
            return;
        }

        tableRoot.innerHTML = `
            <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 overflow-hidden shadow-xs">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-zinc-50/80 dark:bg-zinc-800/50 border-b border-zinc-200 dark:border-zinc-800 text-[11px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                <th class="w-10 px-3 py-2.5 text-center">
                                    <input
                                        type="checkbox"
                                        id="contacts-master-checkbox"
                                        onchange="window.toggleSelectAllContacts(this.checked)"
                                        class="rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer w-3.5 h-3.5"
                                    >
                                </th>
                                ${visibleCols.map(col => `
                                    <th class="px-4 py-2.5 whitespace-nowrap">
                                        <div class="flex items-center gap-1">
                                            <span>${col.label}</span>
                                            ${col.isCustom ? '<span class="text-[9px] px-1 rounded bg-zinc-200 dark:bg-zinc-700 text-zinc-600 font-mono">CF</span>' : ''}
                                        </div>
                                    </th>
                                `).join('')}
                                <th class="px-4 py-2.5 text-right whitespace-nowrap w-28">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800 text-xs">
                            ${contacts.map(c => {
                                const isChecked = state.selectedContactIds.has(c.id);
                                return `
                                    <tr class="hover:bg-zinc-50/80 dark:hover:bg-zinc-800/40 transition-colors group cursor-pointer" onclick="window.handleContactRowClick(event, ${c.id})">
                                        <td class="w-10 px-3 py-3 text-center" onclick="event.stopPropagation()">
                                            <input
                                                type="checkbox"
                                                value="${c.id}"
                                                ${isChecked ? 'checked' : ''}
                                                onchange="window.toggleSelectContact(${c.id}, this.checked)"
                                                class="rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer w-3.5 h-3.5"
                                            >
                                        </td>
                                        ${visibleCols.map(col => `
                                            <td class="px-4 py-3 align-middle whitespace-nowrap">
                                                ${renderFieldCell(c, col.name, col)}
                                            </td>
                                        `).join('')}
                                        <td class="px-4 py-3 text-right align-middle whitespace-nowrap" onclick="event.stopPropagation()">
                                            <div class="flex items-center justify-end gap-1.5">
                                                <button
                                                    type="button"
                                                    onclick="window.openContactPreviewModal(${c.id})"
                                                    class="p-1.5 rounded-md hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-500 hover:text-indigo-600 transition-colors cursor-pointer"
                                                    title="Quick Preview"
                                                >
                                                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                                </button>
                                                <button
                                                    type="button"
                                                    onclick="window.openContactModal('edit', ${c.id})"
                                                    class="p-1.5 rounded-md hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-500 hover:text-zinc-800 transition-colors cursor-pointer"
                                                    title="Edit Contact"
                                                >
                                                    <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                                                </button>
                                                <button
                                                    type="button"
                                                    onclick="window.deleteContact(${c.id})"
                                                    class="p-1.5 rounded-md hover:bg-red-50 dark:hover:bg-red-950/40 text-zinc-400 hover:text-red-600 transition-colors cursor-pointer"
                                                    title="Delete Contact"
                                                >
                                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                `;
                            }).join('')}
                        </tbody>
                    </table>
                </div>

                <!-- Footer -->
                <div class="px-4 py-2.5 bg-zinc-50/80 dark:bg-zinc-800/40 border-t border-zinc-200 dark:border-zinc-800 flex flex-col sm:flex-row items-center justify-between text-xs text-zinc-500 gap-2">
                    <div>
                        Showing <strong>${contacts.length}</strong> of <strong>${state.contacts.length}</strong> stakeholders
                    </div>
                    <div class="flex items-center gap-3 text-[11px] text-zinc-400">
                        <span>Click row to open 360° Profile</span>
                        <span>•</span>
                        <span>Multi-select active</span>
                    </div>
                </div>
            </div>
        `;

        if (window.lucide) lucide.createIcons();
    }

    // Render Kanban View
    function renderKanbanView(contacts) {
        const kanbanRoot = document.getElementById('contacts-kanban-view-root');
        if (!kanbanRoot) return;

        const columns = [
            { id: 'Decision Maker', label: 'Decision Makers', color: 'border-t-blue-500', badgeColor: 'bg-blue-50 text-blue-700' },
            { id: 'Champion', label: 'Champions', color: 'border-t-emerald-500', badgeColor: 'bg-emerald-50 text-emerald-700' },
            { id: 'Influencer', label: 'Influencers', color: 'border-t-amber-500', badgeColor: 'bg-amber-50 text-amber-700' },
            { id: 'Evaluator', label: 'Evaluators', color: 'border-t-purple-500', badgeColor: 'bg-purple-50 text-purple-700' },
        ];

        const fieldDefs = getVisibleFieldDefinitions();
        const visibleFields = state.kanbanVisibleFields
            .map(k => fieldDefs.find(f => f.name === k))
            .filter(Boolean);

        kanbanRoot.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-start">
                ${columns.map(col => {
                    const colContacts = contacts.filter(c => c.contact_role === col.id);
                    return `
                        <div
                            class="bg-zinc-100/70 dark:bg-zinc-800/30 rounded-xl p-3 border border-zinc-200 dark:border-zinc-800 border-t-4 ${col.color} min-h-[420px] flex flex-col"
                            ondragover="event.preventDefault()"
                            ondrop="window.handleContactKanbanDrop(event, '${col.id}')"
                        >
                            <div class="flex items-center justify-between mb-3 px-1">
                                <div class="flex items-center gap-2">
                                    <h4 class="text-xs font-bold text-zinc-900 dark:text-zinc-100 tracking-tight">
                                        ${col.label}
                                    </h4>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold ${col.badgeColor}">
                                        ${colContacts.length}
                                    </span>
                                </div>
                                <button
                                    type="button"
                                    onclick="window.openContactModal('create', null, '${col.id}')"
                                    class="p-1 rounded hover:bg-zinc-200 text-zinc-400 hover:text-zinc-700 cursor-pointer"
                                    title="Add Contact"
                                >
                                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                </button>
                            </div>

                            <div class="space-y-2.5 flex-1">
                                ${colContacts.length === 0 ? `
                                    <div class="border-2 border-dashed border-zinc-200 dark:border-zinc-700 rounded-lg p-6 text-center text-zinc-400 text-xs">
                                        Drop ${col.label} here
                                    </div>
                                ` : colContacts.map(c => `
                                    <div
                                        draggable="true"
                                        ondragstart="window.handleContactKanbanDragStart(event, ${c.id})"
                                        onclick="window.navigateToContact(${c.id})"
                                        class="bg-white dark:bg-zinc-900 rounded-lg p-3.5 border border-zinc-200 dark:border-zinc-800 shadow-2xs hover:shadow-md transition-all cursor-grab active:cursor-grabbing group relative"
                                    >
                                        <div class="flex items-start justify-between gap-2 mb-2">
                                            <div class="font-bold text-xs text-zinc-900 dark:text-zinc-100 hover:text-indigo-600 transition-colors truncate">
                                                ${c.name || `${c.first_name} ${c.last_name}`}
                                            </div>
                                            ${c.is_primary ? '<span class="text-amber-500 text-[10px]" title="Primary">⭐</span>' : ''}
                                        </div>

                                        <div class="space-y-1.5 text-[11px] text-zinc-600 dark:text-zinc-400">
                                            ${visibleFields.map(f => {
                                                if (getPermission(f.name) === 'Hidden' || c['_hidden_' + f.name]) return '';
                                                return `
                                                    <div class="flex items-center justify-between text-[11px]">
                                                        <span class="text-zinc-400 text-[10px]">${f.label}:</span>
                                                        <span class="font-medium text-zinc-800 dark:text-zinc-200 truncate max-w-[130px]">${c[f.name] || '—'}</span>
                                                    </div>
                                                `;
                                            }).join('')}
                                        </div>

                                        <div class="flex items-center justify-between pt-2.5 mt-2.5 border-t border-zinc-100 dark:border-zinc-800 text-[11px]">
                                            <div class="flex items-center gap-1.5 text-zinc-500">
                                                <i data-lucide="building-2" class="w-3 h-3 text-zinc-400"></i>
                                                <span class="truncate max-w-[100px] text-[10px]">${c.account_name || 'Unlinked'}</span>
                                            </div>
                                            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity" onclick="event.stopPropagation()">
                                                <button
                                                    type="button"
                                                    onclick="window.openContactModal('edit', ${c.id})"
                                                    class="p-1 rounded hover:bg-zinc-100 text-zinc-400 hover:text-zinc-700 cursor-pointer"
                                                    title="Edit"
                                                >
                                                    <i data-lucide="edit-2" class="w-3 h-3"></i>
                                                </button>
                                                <button
                                                    type="button"
                                                    onclick="window.openContactPreviewModal(${c.id})"
                                                    class="p-1 rounded hover:bg-zinc-100 text-zinc-400 hover:text-indigo-600 cursor-pointer"
                                                    title="Quick View"
                                                >
                                                    <i data-lucide="eye" class="w-3 h-3"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    `;
                }).join('')}
            </div>
        `;

        if (window.lucide) lucide.createIcons();
    }

    // Main Render Function
    function renderContactsView() {
        renderRoleChips();
        renderActiveFilterChips();
        renderBulkEmailBanner();

        const filtered = getFilteredContacts();
        const recordsCountLabel = document.getElementById('contacts-records-count-label');
        if (recordsCountLabel) {
            recordsCountLabel.innerHTML = `Showing <strong>${filtered.length}</strong> of <strong>${state.contacts.length}</strong> contacts`;
        }

        const tableContainer = document.getElementById('contacts-table-view-root');
        const kanbanContainer = document.getElementById('contacts-kanban-view-root');

        if (state.viewMode === 'table') {
            if (tableContainer) tableContainer.classList.remove('hidden');
            if (kanbanContainer) kanbanContainer.classList.add('hidden');
            renderTableView(filtered);
        } else {
            if (tableContainer) tableContainer.classList.add('hidden');
            if (kanbanContainer) kanbanContainer.classList.remove('hidden');
            renderKanbanView(filtered);
        }
    }

    // Fetch Contacts from Server API
    async function fetchContacts() {
        state.isLoading = true;
        try {
            const params = new URLSearchParams();
            if (state.searchTerm) params.append('search', state.searchTerm);
            if (state.activeRole !== 'all') params.append('role', state.activeRole);

            const res = await fetch(`/api/contacts?${params.toString()}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            if (!res.ok) throw new Error('Failed to fetch contacts');
            const data = await res.json();
            state.contacts = data;
            renderContactsView();
        } catch (err) {
            console.error('Error fetching contacts:', err);
            showNotification('Error loading contacts: ' + err.message, 'error');
        } finally {
            state.isLoading = false;
        }
    }

    // Drag & Drop Handlers for Kanban
    window.handleContactKanbanDragStart = function (e, id) {
        e.dataTransfer.setData('text/plain', String(id));
    };

    window.handleContactKanbanDrop = async function (e, newRole) {
        e.preventDefault();
        const id = e.dataTransfer.getData('text/plain');
        if (!id) return;

        const c = state.contacts.find(x => x.id == id);
        if (!c || c.contact_role === newRole) return;

        // Optimistic update
        const prevRole = c.contact_role;
        c.contact_role = newRole;
        renderContactsView();

        try {
            const res = await fetch(`/api/contacts/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ contact_role: newRole })
            });

            if (!res.ok) throw new Error('Server returned error updating role');
            showNotification(`Updated ${c.first_name}'s buying role to ${newRole}`, 'success');
        } catch (err) {
            c.contact_role = prevRole;
            renderContactsView();
            showNotification('Error updating role: ' + err.message, 'error');
        }
    };

    // Row Click Handler
    window.handleContactRowClick = function (e, id) {
        if (e.target.closest('button') || e.target.closest('a') || e.target.closest('input')) {
            return;
        }
        window.navigateToContact(id);
    };

    // Navigation to Contact Show View
    window.navigateToContact = function (id) {
        if (typeof window.loadSpaPage === 'function') {
            window.loadSpaPage(`/contacts/${id}`, true);
        } else {
            window.location.href = `/contacts/${id}`;
        }
    };

    // Quick Preview Modal
    window.openContactPreviewModal = function (id) {
        const c = state.contacts.find(x => x.id == id);
        if (!c) return;

        state.previewContact = c;
        const modal = document.getElementById('contact-preview-modal');
        if (!modal) return;

        const nameEl = document.getElementById('preview-contact-name');
        if (nameEl) nameEl.innerText = `${c.first_name} ${c.last_name}`;

        const roleEl = document.getElementById('preview-contact-role');
        if (roleEl) roleEl.innerHTML = getRoleBadge(c.contact_role);

        const emailEl = document.getElementById('preview-contact-email');
        if (emailEl) emailEl.innerText = c.email || '—';

        const phoneEl = document.getElementById('preview-contact-phone');
        if (phoneEl) phoneEl.innerText = c.phone || '—';

        const accEl = document.getElementById('preview-contact-account');
        if (accEl) accEl.innerText = c.account_name || 'Unlinked';

        const titleEl = document.getElementById('preview-contact-title');
        if (titleEl) titleEl.innerText = c.title || 'Executive';

        const deptEl = document.getElementById('preview-contact-department');
        if (deptEl) deptEl.innerText = c.department || 'General';

        const notesEl = document.getElementById('preview-contact-notes');
        if (notesEl) notesEl.innerText = c.notes || 'No stakeholder notes recorded.';

        const fullBtn = document.getElementById('btn-preview-full-360');
        if (fullBtn) {
            fullBtn.onclick = () => window.navigateToContact(c.id);
        }

        modal.classList.remove('hidden');
        if (window.lucide) lucide.createIcons();
    };

    window.closeContactPreviewModal = function () {
        const modal = document.getElementById('contact-preview-modal');
        if (modal) modal.classList.add('hidden');
        state.previewContact = null;
    };

    // Add / Edit Modal
    window.openContactModal = function (mode = 'create', id = null, defaultRole = null) {
        const modal = document.getElementById('contact-form-modal');
        if (!modal) return;

        state.isCreateMode = (mode === 'create');
        state.editingContact = null;

        const titleEl = document.getElementById('modal-contact-form-title');
        const submitBtn = document.getElementById('btn-submit-contact-form');

        if (state.isCreateMode) {
            if (titleEl) titleEl.innerText = 'Add New Stakeholder Contact';
            if (submitBtn) submitBtn.innerText = 'Create Contact';
            resetContactForm(defaultRole);
        } else {
            const c = state.contacts.find(x => x.id == id);
            if (!c) return;
            state.editingContact = c;
            if (titleEl) titleEl.innerText = `Edit Contact: ${c.first_name} ${c.last_name}`;
            if (submitBtn) submitBtn.innerText = 'Save Changes';
            populateContactForm(c);
        }

        renderDynamicCustomFieldInputs(state.editingContact);
        modal.classList.remove('hidden');
        if (window.lucide) lucide.createIcons();
    };

    window.closeContactModal = function () {
        const modal = document.getElementById('contact-form-modal');
        if (modal) modal.classList.add('hidden');
        state.editingContact = null;
    };

    function resetContactForm(defaultRole = null) {
        const form = document.getElementById('contact-record-form');
        if (form) form.reset();
        const roleSelect = document.getElementById('contact-field-role');
        if (roleSelect && defaultRole) {
            roleSelect.value = defaultRole;
        }
    }

    function populateContactForm(c) {
        const setVal = (id, val) => {
            const el = document.getElementById(id);
            if (el) el.value = val !== undefined && val !== null ? val : '';
        };

        setVal('contact-field-first_name', c.first_name);
        setVal('contact-field-last_name', c.last_name);
        setVal('contact-field-email', c.email);
        setVal('contact-field-phone', c.phone);
        setVal('contact-field-title', c.title);
        setVal('contact-field-department', c.department);
        setVal('contact-field-account_id', c.account_id);
        setVal('contact-field-role', c.contact_role);
        setVal('contact-field-assigned_to', c.assigned_to);
        setVal('contact-field-notes', c.notes);

        const primaryBox = document.getElementById('contact-field-is_primary');
        if (primaryBox) primaryBox.checked = Boolean(c.is_primary);

        const optOutBox = document.getElementById('contact-field-email_opt_out');
        if (optOutBox) optOutBox.checked = Boolean(c.email_opt_out);

        // Enforce Read-Only permissions on form fields
        ['first_name', 'last_name', 'email', 'phone', 'title', 'department', 'account_id', 'contact_role', 'notes'].forEach(fieldName => {
            const el = document.getElementById(`contact-field-${fieldName}`);
            if (el) {
                const perm = getPermission(fieldName);
                if (perm === 'Read-Only') {
                    el.disabled = true;
                    el.classList.add('opacity-60', 'cursor-not-allowed', 'bg-zinc-100');
                } else {
                    el.disabled = false;
                    el.classList.remove('opacity-60', 'cursor-not-allowed', 'bg-zinc-100');
                }
            }
        });
    }

    // Render Dynamic Custom Fields inside Form Modal
    function renderDynamicCustomFieldInputs(contact = null) {
        const container = document.getElementById('contact-custom-fields-form-container');
        if (!container) return;

        const activeFields = state.customFields.filter(cf => getPermission(cf.field_name) !== 'Hidden');

        if (activeFields.length === 0) {
            container.innerHTML = `
                <div class="col-span-full text-zinc-400 text-xs italic py-2">
                    No active custom fields configured for contacts module.
                </div>
            `;
            return;
        }

        container.innerHTML = activeFields.map(cf => {
            const perm = getPermission(cf.field_name);
            const isReadOnly = (perm === 'Read-Only');
            const fName = cf.field_name;
            const currentVal = contact
                ? (contact[fName] !== undefined ? contact[fName] : (contact.custom_data && contact.custom_data[fName]))
                : (cf.default_value || '');

            const dType = (cf.data_type || 'text').toLowerCase();
            const options = cf.options ? cf.options.split(',').map(s => s.trim()) : [];

            let inputHtml = '';
            const disabledAttr = isReadOnly ? 'disabled' : '';
            const readOnlyStyle = isReadOnly ? 'opacity-60 cursor-not-allowed bg-zinc-100 dark:bg-zinc-800' : 'bg-zinc-50 dark:bg-zinc-800/60';

            if (dType === 'picklist' || dType === 'dropdown') {
                inputHtml = `
                    <select
                        name="custom_fields[${fName}]"
                        id="cf-input-${fName}"
                        ${disabledAttr}
                        class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 ${readOnlyStyle} text-zinc-900 dark:text-zinc-100"
                    >
                        <option value="">Select option...</option>
                        ${options.map(opt => `<option value="${opt}" ${currentVal === opt ? 'selected' : ''}>${opt}</option>`).join('')}
                    </select>
                `;
            } else if (dType === 'textarea') {
                inputHtml = `
                    <textarea
                        name="custom_fields[${fName}]"
                        id="cf-input-${fName}"
                        rows="2"
                        ${disabledAttr}
                        class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 ${readOnlyStyle} text-zinc-900 dark:text-zinc-100"
                    >${currentVal || ''}</textarea>
                `;
            } else {
                inputHtml = `
                    <input
                        type="text"
                        name="custom_fields[${fName}]"
                        id="cf-input-${fName}"
                        value="${currentVal || ''}"
                        ${disabledAttr}
                        class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 ${readOnlyStyle} text-zinc-900 dark:text-zinc-100"
                    >
                `;
            }

            return `
                <div class="space-y-1">
                    <label class="flex items-center justify-between text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                        <span>${cf.field_label}</span>
                        ${isReadOnly ? '<span class="text-[10px] text-amber-600 font-bold">🔒 Read-Only</span>' : ''}
                    </label>
                    ${inputHtml}
                    ${cf.help_text ? `<p class="text-[10px] text-zinc-400">${cf.help_text}</p>` : ''}
                </div>
            `;
        }).join('');
    }

    // Submit Create / Edit Contact
    window.handleContactFormSubmit = async function (e) {
        e.preventDefault();
        const form = document.getElementById('contact-record-form');
        if (!form) return;

        const formData = new FormData(form);
        const payload = {
            custom_fields: {},
        };

        formData.forEach((value, key) => {
            if (key.startsWith('custom_fields[')) {
                const match = key.match(/custom_fields\[(.*?)\]/);
                if (match && match[1]) {
                    payload.custom_fields[match[1]] = value;
                }
            } else {
                payload[key] = value;
            }
        });

        payload.is_primary = document.getElementById('contact-field-is_primary')?.checked ? 1 : 0;
        payload.email_opt_out = document.getElementById('contact-field-email_opt_out')?.checked ? 1 : 0;

        // Security check: Ignore Read-Only and Hidden fields
        STANDARD_FIELD_DEFINITIONS.forEach(sf => {
            const perm = getPermission(sf.name);
            if (perm === 'Hidden' || perm === 'Read-Only') {
                delete payload[sf.name];
            }
        });

        state.customFields.forEach(cf => {
            const perm = getPermission(cf.field_name);
            if (perm === 'Hidden' || perm === 'Read-Only') {
                delete payload.custom_fields[cf.field_name];
            }
        });

        const submitBtn = document.getElementById('btn-submit-contact-form');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerText = 'Saving...';
        }

        try {
            const isCreate = state.isCreateMode;
            const url = isCreate ? '/api/contacts' : `/api/contacts/${state.editingContact.id}`;
            const method = isCreate ? 'POST' : 'PUT';

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

            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Error saving contact');

            showNotification(data.message || 'Contact saved successfully!', 'success');
            window.closeContactModal();
            await fetchContacts();
        } catch (err) {
            console.error('Contact error:', err);
            showNotification(err.message, 'error');
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerText = state.isCreateMode ? 'Create Contact' : 'Save Changes';
            }
        }
    };

    // Delete Contact
    window.deleteContact = async function (id) {
        const c = state.contacts.find(x => x.id == id);
        const name = c ? `${c.first_name} ${c.last_name}` : 'this contact';

        if (!confirm(`Are you sure you want to delete "${name}"? This action can be reversed by an administrator.`)) {
            return;
        }

        try {
            const res = await fetch(`/api/contacts/${id}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Failed to delete');

            showNotification(data.message || 'Contact deleted.', 'success');
            state.contacts = state.contacts.filter(x => x.id != id);
            renderContactsView();
        } catch (err) {
            showNotification(err.message, 'error');
        }
    };

    // Filter Controls
    window.setContactRoleFilter = function (roleId) {
        state.activeRole = roleId;
        renderContactsView();
    };

    window.switchContactsViewMode = function (mode) {
        state.viewMode = mode;
        const btnTable = document.getElementById('btn-contact-view-table');
        const btnKanban = document.getElementById('btn-contact-view-kanban');

        if (mode === 'table') {
            if (btnTable) btnTable.className = 'flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-md bg-white text-zinc-950 shadow-xs dark:bg-zinc-800 dark:text-zinc-100 cursor-pointer';
            if (btnKanban) btnKanban.className = 'flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-md text-zinc-500 hover:text-zinc-900 cursor-pointer';
        } else {
            if (btnTable) btnTable.className = 'flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-md text-zinc-500 hover:text-zinc-900 cursor-pointer';
            if (btnKanban) btnKanban.className = 'flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-md bg-white text-zinc-950 shadow-xs dark:bg-zinc-800 dark:text-zinc-100 cursor-pointer';
        }

        renderContactsView();
    };

    // Customize Fields Modal
    window.openCustomizeFieldsModal = function () {
        const modal = document.getElementById('contact-customize-modal');
        if (!modal) return;

        const fieldDefs = getVisibleFieldDefinitions();
        const activeList = state.viewMode === 'table' ? state.tableVisibleFields : state.kanbanVisibleFields;
        const container = document.getElementById('customize-fields-checkboxes-container');

        if (container) {
            container.innerHTML = fieldDefs.map(f => {
                const isChecked = activeList.includes(f.name);
                return `
                    <label class="flex items-center gap-2 p-2 rounded-lg border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 cursor-pointer text-xs">
                        <input
                            type="checkbox"
                            value="${f.name}"
                            ${isChecked ? 'checked' : ''}
                            class="rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer w-3.5 h-3.5"
                        >
                        <span class="font-medium text-zinc-800 dark:text-zinc-200">${f.label}</span>
                        ${f.isCustom ? '<span class="text-[9px] px-1 rounded bg-zinc-200 text-zinc-600 font-mono ml-auto">Custom</span>' : ''}
                    </label>
                `;
            }).join('');
        }

        modal.classList.remove('hidden');
    };

    window.closeCustomizeFieldsModal = function () {
        const modal = document.getElementById('contact-customize-modal');
        if (modal) modal.classList.add('hidden');
    };

    window.saveCustomizedFields = function () {
        const container = document.getElementById('customize-fields-checkboxes-container');
        if (!container) return;

        const checkedBoxes = Array.from(container.querySelectorAll('input[type="checkbox"]:checked'));
        const newKeys = checkedBoxes.map(cb => cb.value);

        if (newKeys.length === 0) {
            showNotification('Please select at least one visible field', 'error');
            return;
        }

        if (state.viewMode === 'table') {
            state.tableVisibleFields = newKeys;
        } else {
            state.kanbanVisibleFields = newKeys;
        }

        saveViewConfigs();
        window.closeCustomizeFieldsModal();
        renderContactsView();
        showNotification('Visible fields updated', 'success');
    };

    // Filter Modal
    window.openContactFilterModal = function () {
        const modal = document.getElementById('contact-filter-modal');
        if (!modal) return;

        const fieldDefs = getVisibleFieldDefinitions();
        const select = document.getElementById('filter-rule-field-select');
        if (select) {
            select.innerHTML = fieldDefs.map(f => `<option value="${f.name}">${f.label}</option>`).join('');
        }

        modal.classList.remove('hidden');
    };

    window.closeContactFilterModal = function () {
        const modal = document.getElementById('contact-filter-modal');
        if (modal) modal.classList.add('hidden');
    };

    window.addContactFilterRule = function () {
        const fieldEl = document.getElementById('filter-rule-field-select');
        const opEl = document.getElementById('filter-rule-operator-select');
        const valEl = document.getElementById('filter-rule-value-input');

        const field = fieldEl ? fieldEl.value : '';
        const op = opEl ? opEl.value : 'contains';
        const val = valEl ? valEl.value.trim() : '';

        if (!val) {
            showNotification('Please enter filter value', 'error');
            return;
        }

        state.activeFilters.push({
            id: 'rule_' + Date.now(),
            field: field,
            operator: op,
            value: val,
        });

        if (valEl) valEl.value = '';
        window.closeContactFilterModal();
        renderContactsView();
    };

    window.removeContactFilterRule = function (id) {
        state.activeFilters = state.activeFilters.filter(r => r.id !== id);
        renderContactsView();
    };

    window.clearAllContactFilters = function () {
        state.activeFilters = [];
        renderContactsView();
    };

    // Selection Handling
    window.toggleSelectAllContacts = function (checked) {
        const filtered = getFilteredContacts();
        if (checked) {
            filtered.forEach(c => state.selectedContactIds.add(c.id));
        } else {
            state.selectedContactIds.clear();
        }
        renderContactsView();
    };

    window.toggleSelectContact = function (id, checked) {
        if (checked) {
            state.selectedContactIds.add(id);
        } else {
            state.selectedContactIds.delete(id);
        }
        renderContactsView();
    };

    window.clearSelectedContacts = function () {
        state.selectedContactIds.clear();
        renderContactsView();
    };

    // Trigger Bulk Email
    window.triggerBulkEmail = function () {
        const count = state.selectedContactIds.size;
        if (count === 0) {
            showNotification('Please select contacts first', 'error');
            return;
        }
        showNotification(`Opening bulk campaign for ${count} contacts...`, 'info');
        // If bulk email modal available
        if (typeof window.openBulkEmailModal === 'function') {
            window.openBulkEmailModal(Array.from(state.selectedContactIds));
        }
    };

    // Export CSV
    window.exportContactsCsv = function () {
        const filtered = getFilteredContacts();
        if (filtered.length === 0) {
            showNotification('No contacts to export', 'error');
            return;
        }

        const fieldDefs = getVisibleFieldDefinitions();
        const headers = fieldDefs.map(f => `"${f.label.replace(/"/g, '""')}"`);

        const rows = filtered.map(c => {
            return fieldDefs.map(f => {
                const v = c[f.name] !== undefined && c[f.name] !== null ? String(c[f.name]) : '';
                return `"${v.replace(/"/g, '""')}"`;
            }).join(',');
        });

        const csvContent = 'data:text/csv;charset=utf-8,' + [headers.join(','), ...rows].join('\n');
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement('a');
        link.setAttribute('href', encodedUri);
        link.setAttribute('download', `contacts_export_${new Date().toISOString().slice(0, 10)}.csv`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        showNotification(`Exported ${filtered.length} contacts to CSV`, 'success');
    };

    // Module Initializer
    window.initContactsModule = function () {
        const root = document.getElementById('contacts-page-root');
        if (!root) return;

        if (window.__CONTACTS_DATA__) {
            state.contacts = window.__CONTACTS_DATA__.initialContacts || [];
            state.customFields = window.__CONTACTS_DATA__.customFields || [];
            state.fieldPermissions = window.__CONTACTS_DATA__.fieldPermissions || {};
            state.accounts = window.__CONTACTS_DATA__.accounts || [];
            state.users = window.__CONTACTS_DATA__.users || [];
            state.roles = window.__CONTACTS_DATA__.roles || state.roles;
            state.canSendBulkEmail = Boolean(window.__CONTACTS_DATA__.canSendBulkEmail);
        }

        loadSavedViewConfigs();

        const searchInput = document.getElementById('contacts-search-input');
        if (searchInput) {
            searchInput.value = state.searchTerm;
            searchInput.addEventListener('input', (e) => {
                state.searchTerm = e.target.value;
                renderContactsView();
            });
        }

        renderContactsView();
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', window.initContactsModule);
    } else {
        window.initContactsModule();
    }
})();
