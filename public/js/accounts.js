/**
 * DealSync Enterprise CRM — Accounts & Master Ledger Module
 * 1:1 Parity with React Client (AccountsView.tsx, CompactDataTable.tsx, UniversalKanban.tsx)
 * Pure Tailwind CSS styling with Dynamic Custom Fields & Field-Level Security Permissions
 */

(function () {
    'use strict';

    // Module State
    const state = {
        accounts: [],
        customFields: [],
        fieldPermissions: {},
        users: [],
        types: ['Customer', 'Vendor', 'Prospect', 'Partner'],
        paymentTerms: ['Net 15', 'Net 30', 'Net 45', 'Net 60', 'Advance Wire', 'Due on Receipt'],
        currencies: ['USD', 'EUR', 'GBP', 'INR', 'CAD', 'AUD', 'JPY', 'AED', 'SGD', 'CHF'],
        activeType: 'all',
        searchTerm: '',
        viewMode: 'table', // 'table' | 'kanban'
        selectedAccountIds: new Set(),
        tableVisibleFields: ['name', 'type', 'industry', 'payment_terms', 'credit_limit', 'phone', 'assigned_to'],
        kanbanVisibleFields: ['industry', 'credit_limit', 'payment_terms', 'phone'],
        activeFilters: [], // array of { id, field, operator, value }
        selectedAccount: null,
        editingAccount: null,
        transferringAccount: null,
        viewing360Account: null,
        account360Data: null,
        active360Tab: 'overview',
        isCreateMode: false,
        isLoading: false,
    };

    const STANDARD_FIELD_DEFINITIONS = [
        { name: 'name', label: 'Account Name', type: 'text', standard: true, required: true },
        { name: 'type', label: 'Classification', type: 'dropdown', standard: true, options: ['Customer', 'Vendor', 'Prospect', 'Partner'] },
        { name: 'industry', label: 'Industry Sector', type: 'text', standard: true },
        { name: 'website', label: 'Corporate Website', type: 'text', standard: true },
        { name: 'phone', label: 'Telephone Number', type: 'phone', standard: true },
        { name: 'currency', label: 'Default Currency', type: 'dropdown', standard: true, options: ['USD', 'EUR', 'GBP', 'INR', 'CAD', 'AUD', 'JPY', 'AED', 'SGD', 'CHF'] },
        { name: 'payment_terms', label: 'Payment Terms', type: 'dropdown', standard: true, options: ['Net 15', 'Net 30', 'Net 45', 'Net 60', 'Advance Wire', 'Due on Receipt'] },
        { name: 'credit_limit', label: 'Credit Limit', type: 'currency', standard: true },
        { name: 'annual_revenue', label: 'Annual Revenue', type: 'currency', standard: true },
        { name: 'assigned_to', label: 'Account Owner', type: 'lookup', standard: true },
        { name: 'billing_address', label: 'Billing Address', type: 'textarea', standard: true },
        { name: 'shipping_address', label: 'Shipping Address', type: 'textarea', standard: true },
        { name: 'notes', label: 'Account Notes', type: 'textarea', standard: true },
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

    // Helper: Format Currency
    function formatCurrency(val, currency = 'USD') {
        const num = parseFloat(val) || 0;
        try {
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: currency || 'USD',
                maximumFractionDigits: 0
            }).format(num);
        } catch (_) {
            return '$' + num.toLocaleString();
        }
    }

    // Helper: Get Field Permission ('Read/Write', 'Read-Only', 'Hidden')
    function getPermission(fieldName) {
        if (state.fieldPermissions && state.fieldPermissions[fieldName]) {
            return state.fieldPermissions[fieldName];
        }
        return 'Read/Write';
    }

    // Helper: Get merged available field definitions (excluding Hidden fields)
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
            const savedTable = localStorage.getItem('dealsync_accounts_table_cols');
            if (savedTable) {
                const parsed = JSON.parse(savedTable);
                if (Array.isArray(parsed) && parsed.length > 0) {
                    state.tableVisibleFields = parsed;
                }
            }
            const savedKanban = localStorage.getItem('dealsync_accounts_kanban_cols');
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
            localStorage.setItem('dealsync_accounts_table_cols', JSON.stringify(state.tableVisibleFields));
            localStorage.setItem('dealsync_accounts_kanban_cols', JSON.stringify(state.kanbanVisibleFields));
        } catch (_) {}
    }

    // Filter Accounts
    function getFilteredAccounts() {
        return state.accounts.filter(acc => {
            // 1. Classification Chip Filter
            if (state.activeType !== 'all' && acc.type !== state.activeType) {
                return false;
            }

            // 2. Search Term
            if (state.searchTerm) {
                const s = state.searchTerm.toLowerCase();
                const matchName = (acc.name || '').toLowerCase().includes(s);
                const matchIndustry = (acc.industry || '').toLowerCase().includes(s);
                const matchPhone = (acc.phone || '').toLowerCase().includes(s);
                const matchWebsite = (acc.website || '').toLowerCase().includes(s);
                const matchOwner = (acc.assigned_to || acc.owner_name || '').toLowerCase().includes(s);
                if (!matchName && !matchIndustry && !matchPhone && !matchWebsite && !matchOwner) {
                    return false;
                }
            }

            // 3. Dynamic Filter Rules
            for (const rule of state.activeFilters) {
                const val = (acc[rule.field] !== undefined && acc[rule.field] !== null) ? String(acc[rule.field]).toLowerCase() : '';
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

    // Render Stats & Classification Chips
    function renderClassificationChips() {
        const container = document.getElementById('accounts-type-chips-container');
        if (!container) return;

        const total = state.accounts.length;
        const counts = {
            all: total,
            Customer: state.accounts.filter(a => a.type === 'Customer').length,
            Vendor: state.accounts.filter(a => a.type === 'Vendor').length,
            Prospect: state.accounts.filter(a => a.type === 'Prospect').length,
            Partner: state.accounts.filter(a => a.type === 'Partner').length,
        };

        const chips = [
            { id: 'all', label: 'All Accounts', count: counts.all },
            { id: 'Customer', label: 'Customers', count: counts.Customer, color: 'text-blue-700 bg-blue-50 border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800' },
            { id: 'Vendor', label: 'Vendors / Suppliers', count: counts.Vendor, color: 'text-amber-700 bg-amber-50 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800' },
            { id: 'Prospect', label: 'Prospects', count: counts.Prospect, color: 'text-emerald-700 bg-emerald-50 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800' },
            { id: 'Partner', label: 'Strategic Partners', count: counts.Partner, color: 'text-purple-700 bg-purple-50 border-purple-200 dark:bg-purple-950/40 dark:text-purple-300 dark:border-purple-800' },
        ];

        container.innerHTML = `
            <span class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 mr-1 self-center whitespace-nowrap">Classifications:</span>
            ${chips.map(chip => {
                const isActive = state.activeType === chip.id;
                const activeClasses = isActive
                    ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900 font-bold shadow-xs'
                    : 'bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700/60 border border-zinc-200 dark:border-zinc-700';

                return `
                    <button
                        type="button"
                        onclick="window.setAccountTypeFilter('${chip.id}')"
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

        // Update KPI summary cards if present
        const kpiTotal = document.getElementById('kpi-total-accounts');
        if (kpiTotal) kpiTotal.innerText = `${total} Accounts`;

        const kpiCustomers = document.getElementById('kpi-active-customers');
        if (kpiCustomers) kpiCustomers.innerText = `${counts.Customer} Clients`;

        const kpiVendors = document.getElementById('kpi-suppliers-vendors');
        if (kpiVendors) kpiVendors.innerText = `${counts.Vendor} Partners`;

        const totalCredit = state.accounts.reduce((acc, a) => acc + (parseFloat(a.credit_limit) || 0), 0);
        const kpiCredit = document.getElementById('kpi-total-credit');
        if (kpiCredit) kpiCredit.innerText = formatCurrency(totalCredit);
    }

    // Render Active Filter Chips
    function renderActiveFilterChips() {
        const container = document.getElementById('accounts-filter-chips-bar');
        if (!container) return;

        if (state.activeFilters.length === 0) {
            container.classList.add('hidden');
            container.innerHTML = '';
            const badge = document.getElementById('accounts-filter-badge');
            if (badge) badge.classList.add('hidden');
            return;
        }

        const badge = document.getElementById('accounts-filter-badge');
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
                            <button type="button" onclick="window.removeAccountFilterRule('${r.id}')" class="text-zinc-400 hover:text-red-600 transition-colors cursor-pointer">
                                ✕
                            </button>
                        </span>
                    `;
                }).join('')}
                <button
                    type="button"
                    onclick="window.clearAllAccountFilters()"
                    class="ml-auto text-xs text-blue-700 dark:text-blue-300 hover:underline font-semibold cursor-pointer"
                >
                    Clear All
                </button>
            </div>
        `;

        if (window.lucide) lucide.createIcons();
    }

    // Helper: Badge for Account Classification
    function getTypeBadge(type) {
        switch (type) {
            case 'Customer':
                return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800">Customer</span>';
            case 'Vendor':
                return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800">Vendor</span>';
            case 'Prospect':
                return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800">Prospect</span>';
            case 'Partner':
                return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-purple-50 text-purple-700 border border-purple-200 dark:bg-purple-950/40 dark:text-purple-300 dark:border-purple-800">Partner</span>';
            default:
                return `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">${type || 'Account'}</span>`;
        }
    }

    // Helper: Render Field Cell in Table
    function renderFieldCell(acc, fieldName, fieldDef) {
        // Enforce field-level permission: If Hidden, return empty
        if (getPermission(fieldName) === 'Hidden' || acc['_hidden_' + fieldName]) {
            return '<span class="text-zinc-400 italic text-[11px]">Hidden</span>';
        }

        const val = acc[fieldName];

        if (fieldName === 'name') {
            return `
                <div class="flex items-center gap-2 max-w-[280px]">
                    <div class="w-7 h-7 rounded-lg bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-900 dark:text-zinc-100 shrink-0 font-bold text-xs border border-zinc-200 dark:border-zinc-700">
                        ${(acc.name || 'A').charAt(0).toUpperCase()}
                    </div>
                    <div class="truncate">
                        <a href="/accounts/${acc.id}" onclick="event.preventDefault(); window.navigateToAccount(${acc.id})" class="font-bold text-zinc-900 dark:text-zinc-100 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                            ${acc.name || 'Unnamed Account'}
                        </a>
                        ${acc.website ? `<div class="text-[11px] text-zinc-400 truncate">${acc.website.replace(/^https?:\/\//, '')}</div>` : ''}
                    </div>
                </div>
            `;
        }

        if (fieldName === 'type') {
            return getTypeBadge(acc.type);
        }

        if (fieldName === 'credit_limit') {
            return `<span class="font-semibold text-zinc-800 dark:text-zinc-200">${formatCurrency(acc.credit_limit, acc.currency)}</span>`;
        }

        if (fieldName === 'annual_revenue') {
            return `<span class="font-bold text-zinc-950 dark:text-zinc-100">${formatCurrency(acc.annual_revenue, acc.currency)}</span>`;
        }

        if (fieldName === 'payment_terms') {
            return `<span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700">${acc.payment_terms || 'Net 30'}</span>`;
        }

        if (fieldName === 'phone') {
            return acc.phone
                ? `<span class="font-mono text-xs text-zinc-600 dark:text-zinc-300">${acc.phone}</span>`
                : '<span class="text-zinc-400">—</span>';
        }

        if (fieldName === 'assigned_to') {
            const ownerName = acc.owner_name || acc.assigned_to || 'Unassigned';
            const initial = ownerName.charAt(0).toUpperCase();
            return `
                <div class="flex items-center gap-1.5 text-xs text-zinc-700 dark:text-zinc-300">
                    <span class="w-5 h-5 rounded-full bg-blue-100 dark:bg-blue-900/60 text-blue-700 dark:text-blue-300 font-bold text-[10px] inline-flex items-center justify-center">
                        ${initial}
                    </span>
                    <span class="truncate max-w-[120px]">${ownerName}</span>
                </div>
            `;
        }

        if (fieldName === 'created_at') {
            if (!val) return '<span class="text-zinc-400">—</span>';
            const date = new Date(val);
            return `<span class="text-xs text-zinc-500">${date.toLocaleDateString()}</span>`;
        }

        // Custom field rendering
        if (fieldDef && fieldDef.isCustom) {
            if (val === undefined || val === null || val === '') {
                return '<span class="text-zinc-400">—</span>';
            }
            if (fieldDef.type === 'currency') {
                return `<span class="font-semibold text-zinc-800 dark:text-zinc-200">${formatCurrency(val, acc.currency)}</span>`;
            }
            if (fieldDef.type === 'rating') {
                const num = parseInt(val) || 0;
                return `
                    <div class="flex items-center gap-0.5 text-amber-500">
                        ${'★'.repeat(Math.min(5, Math.max(0, num)))}${'☆'.repeat(Math.max(0, 5 - num))}
                    </div>
                `;
            }
            return `<span class="text-xs text-zinc-700 dark:text-zinc-300">${val}</span>`;
        }

        return `<span class="text-xs text-zinc-700 dark:text-zinc-300 truncate">${val || '—'}</span>`;
    }

    // Render Compact Table View
    function renderTableView(accounts) {
        const tableRoot = document.getElementById('accounts-table-view-root');
        if (!tableRoot) return;

        const fieldDefs = getVisibleFieldDefinitions();
        const visibleCols = state.tableVisibleFields
            .map(key => fieldDefs.find(f => f.name === key))
            .filter(Boolean);

        if (accounts.length === 0) {
            tableRoot.innerHTML = `
                <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 p-12 text-center shadow-xs">
                    <div class="w-12 h-12 rounded-xl bg-zinc-100 dark:bg-zinc-800 text-zinc-400 flex items-center justify-center mx-auto mb-3">
                        <i data-lucide="building-2" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 mb-1">No accounts found</h3>
                    <p class="text-xs text-zinc-500 max-w-sm mx-auto mb-4">
                        There are no accounts matching your current search, classification filters, or field restrictions.
                    </p>
                    <button
                        type="button"
                        onclick="window.openAccountModal('create')"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm transition-colors cursor-pointer"
                    >
                        <i data-lucide="plus" class="w-4 h-4"></i> Add Account
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
                                        id="accounts-master-checkbox"
                                        onchange="window.toggleSelectAllAccounts(this.checked)"
                                        class="rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer w-3.5 h-3.5"
                                    >
                                </th>
                                ${visibleCols.map(col => `
                                    <th class="px-4 py-2.5 whitespace-nowrap">
                                        <div class="flex items-center gap-1">
                                            <span>${col.label}</span>
                                            ${col.isCustom ? '<span class="text-[9px] px-1 rounded bg-zinc-200 dark:bg-zinc-700 text-zinc-600 dark:text-zinc-300 font-mono">CF</span>' : ''}
                                        </div>
                                    </th>
                                `).join('')}
                                <th class="px-4 py-2.5 text-right whitespace-nowrap w-32">
                                    360° Operations
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800 text-xs">
                            ${accounts.map(acc => {
                                const isChecked = state.selectedAccountIds.has(acc.id);
                                return `
                                    <tr class="hover:bg-zinc-50/80 dark:hover:bg-zinc-800/40 transition-colors group cursor-pointer" onclick="window.handleAccountRowClick(event, ${acc.id})">
                                        <td class="w-10 px-3 py-3 text-center" onclick="event.stopPropagation()">
                                            <input
                                                type="checkbox"
                                                value="${acc.id}"
                                                ${isChecked ? 'checked' : ''}
                                                onchange="window.toggleSelectAccount(${acc.id}, this.checked)"
                                                class="rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer w-3.5 h-3.5"
                                            >
                                        </td>
                                        ${visibleCols.map(col => `
                                            <td class="px-4 py-3 align-middle whitespace-nowrap">
                                                ${renderFieldCell(acc, col.name, col)}
                                            </td>
                                        `).join('')}
                                        <td class="px-4 py-3 text-right align-middle whitespace-nowrap" onclick="event.stopPropagation()">
                                            <div class="flex items-center justify-end gap-1.5">
                                                <button
                                                    type="button"
                                                    onclick="window.open360Modal(${acc.id})"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-semibold bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 dark:bg-indigo-950/40 dark:text-indigo-300 dark:border-indigo-800 transition-colors cursor-pointer"
                                                    title="Open 360° Profile Modal"
                                                >
                                                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                                    <span>360°</span>
                                                </button>
                                                <button
                                                    type="button"
                                                    onclick="window.openAccountModal('edit', ${acc.id})"
                                                    class="p-1.5 rounded-md hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200 transition-colors cursor-pointer"
                                                    title="Edit Account"
                                                >
                                                    <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                                                </button>
                                                <button
                                                    type="button"
                                                    onclick="window.openTransferModal(${acc.id})"
                                                    class="p-1.5 rounded-md hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200 transition-colors cursor-pointer"
                                                    title="Transfer Ownership"
                                                >
                                                    <i data-lucide="user-check" class="w-3.5 h-3.5"></i>
                                                </button>
                                                <button
                                                    type="button"
                                                    onclick="window.deleteAccount(${acc.id})"
                                                    class="p-1.5 rounded-md hover:bg-red-50 dark:hover:bg-red-950/40 text-zinc-400 hover:text-red-600 transition-colors cursor-pointer"
                                                    title="Delete Account"
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

                <!-- Table Footer -->
                <div class="px-4 py-2.5 bg-zinc-50/80 dark:bg-zinc-800/40 border-t border-zinc-200 dark:border-zinc-800 flex flex-col sm:flex-row items-center justify-between text-xs text-zinc-500 dark:text-zinc-400 gap-2">
                    <div>
                        Showing <strong>${accounts.length}</strong> of <strong>${state.accounts.length}</strong> master ledger accounts
                    </div>
                    <div class="flex items-center gap-3 text-[11px] text-zinc-400">
                        <span>Click row to view 360° Profile</span>
                        <span>•</span>
                        <span>Multi-select enabled</span>
                    </div>
                </div>
            </div>
        `;

        if (window.lucide) lucide.createIcons();
    }

    // Render Kanban Board View
    function renderKanbanView(accounts) {
        const kanbanRoot = document.getElementById('accounts-kanban-view-root');
        if (!kanbanRoot) return;

        const columns = [
            { id: 'Customer', label: 'Customers', color: 'border-t-blue-500', badgeColor: 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300' },
            { id: 'Vendor', label: 'Vendors / Suppliers', color: 'border-t-amber-500', badgeColor: 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300' },
            { id: 'Prospect', label: 'Prospects', color: 'border-t-emerald-500', badgeColor: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300' },
            { id: 'Partner', label: 'Strategic Partners', color: 'border-t-purple-500', badgeColor: 'bg-purple-50 text-purple-700 dark:bg-purple-950/40 dark:text-purple-300' },
        ];

        const fieldDefs = getVisibleFieldDefinitions();
        const visibleFields = state.kanbanVisibleFields
            .map(k => fieldDefs.find(f => f.name === k))
            .filter(Boolean);

        kanbanRoot.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-start">
                ${columns.map(col => {
                    const colAccounts = accounts.filter(a => a.type === col.id);
                    return `
                        <div
                            class="bg-zinc-100/70 dark:bg-zinc-800/30 rounded-xl p-3 border border-zinc-200 dark:border-zinc-800 border-t-4 ${col.color} min-h-[420px] flex flex-col"
                            ondragover="event.preventDefault()"
                            ondrop="window.handleAccountKanbanDrop(event, '${col.id}')"
                        >
                            <div class="flex items-center justify-between mb-3 px-1">
                                <div class="flex items-center gap-2">
                                    <h4 class="text-xs font-bold text-zinc-900 dark:text-zinc-100 tracking-tight">
                                        ${col.label}
                                    </h4>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold ${col.badgeColor}">
                                        ${colAccounts.length}
                                    </span>
                                </div>
                                <button
                                    type="button"
                                    onclick="window.openAccountModal('create', null, '${col.id}')"
                                    class="p-1 rounded hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-400 hover:text-zinc-700 transition-colors cursor-pointer"
                                    title="Add ${col.id}"
                                >
                                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                </button>
                            </div>

                            <div class="space-y-2.5 flex-1">
                                ${colAccounts.length === 0 ? `
                                    <div class="border-2 border-dashed border-zinc-200 dark:border-zinc-700 rounded-lg p-6 text-center text-zinc-400 text-xs">
                                        Drop ${col.id} here
                                    </div>
                                ` : colAccounts.map(acc => `
                                    <div
                                        draggable="true"
                                        ondragstart="window.handleAccountKanbanDragStart(event, ${acc.id})"
                                        onclick="window.open360Modal(${acc.id})"
                                        class="bg-white dark:bg-zinc-900 rounded-lg p-3.5 border border-zinc-200 dark:border-zinc-800 shadow-2xs hover:shadow-md transition-all cursor-grab active:cursor-grabbing group relative"
                                    >
                                        <div class="flex items-start justify-between gap-2 mb-2">
                                            <div class="font-bold text-xs text-zinc-900 dark:text-zinc-100 hover:text-indigo-600 transition-colors truncate">
                                                ${acc.name}
                                            </div>
                                            <span class="text-[10px] text-zinc-400 font-mono shrink-0">${acc.currency || 'USD'}</span>
                                        </div>

                                        <div class="space-y-1.5 text-[11px] text-zinc-600 dark:text-zinc-400">
                                            ${visibleFields.map(f => {
                                                if (getPermission(f.name) === 'Hidden' || acc['_hidden_' + f.name]) return '';
                                                let rendered = acc[f.name];
                                                if (f.name === 'credit_limit' || f.name === 'annual_revenue' || f.type === 'currency') {
                                                    rendered = formatCurrency(acc[f.name], acc.currency);
                                                }
                                                return `
                                                    <div class="flex items-center justify-between text-[11px]">
                                                        <span class="text-zinc-400 text-[10px]">${f.label}:</span>
                                                        <span class="font-medium text-zinc-800 dark:text-zinc-200 truncate max-w-[130px]">${rendered || '—'}</span>
                                                    </div>
                                                `;
                                            }).join('')}
                                        </div>

                                        <div class="flex items-center justify-between pt-2.5 mt-2.5 border-t border-zinc-100 dark:border-zinc-800 text-[11px]">
                                            <div class="flex items-center gap-1.5 text-zinc-500">
                                                <span class="w-4 h-4 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 font-bold text-[9px] inline-flex items-center justify-center">
                                                    ${(acc.assigned_to || acc.owner_name || 'U').charAt(0).toUpperCase()}
                                                </span>
                                                <span class="truncate max-w-[90px] text-[10px]">${acc.assigned_to || acc.owner_name || 'Unassigned'}</span>
                                            </div>
                                            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity" onclick="event.stopPropagation()">
                                                <button
                                                    type="button"
                                                    onclick="window.openAccountModal('edit', ${acc.id})"
                                                    class="p-1 rounded hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-400 hover:text-zinc-700 cursor-pointer"
                                                    title="Edit"
                                                >
                                                    <i data-lucide="edit-2" class="w-3 h-3"></i>
                                                </button>
                                                <button
                                                    type="button"
                                                    onclick="window.navigateToAccount(${acc.id})"
                                                    class="p-1 rounded hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-400 hover:text-indigo-600 cursor-pointer"
                                                    title="Full 360 Page"
                                                >
                                                    <i data-lucide="external-link" class="w-3 h-3"></i>
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
    function renderAccountsView() {
        renderClassificationChips();
        renderActiveFilterChips();

        const filtered = getFilteredAccounts();
        const recordsCountLabel = document.getElementById('accounts-records-count-label');
        if (recordsCountLabel) {
            recordsCountLabel.innerHTML = `Showing <strong>${filtered.length}</strong> of <strong>${state.accounts.length}</strong> records`;
        }

        const tableContainer = document.getElementById('accounts-table-view-root');
        const kanbanContainer = document.getElementById('accounts-kanban-view-root');

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

    // Fetch Accounts from Server API
    async function fetchAccounts() {
        state.isLoading = true;
        try {
            const params = new URLSearchParams();
            if (state.searchTerm) params.append('search', state.searchTerm);
            if (state.activeType !== 'all') params.append('type', state.activeType);

            const res = await fetch(`/api/accounts?${params.toString()}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            if (!res.ok) throw new Error('Failed to fetch accounts');
            const data = await res.json();
            state.accounts = data;
            renderAccountsView();
        } catch (err) {
            console.error('Error fetching accounts:', err);
            showNotification('Error loading accounts: ' + err.message, 'error');
        } finally {
            state.isLoading = false;
        }
    }

    // Drag and Drop Handlers for Kanban
    window.handleAccountKanbanDragStart = function (e, id) {
        e.dataTransfer.setData('text/plain', String(id));
    };

    window.handleAccountKanbanDrop = async function (e, newType) {
        e.preventDefault();
        const id = e.dataTransfer.getData('text/plain');
        if (!id) return;

        const acc = state.accounts.find(a => a.id == id);
        if (!acc || acc.type === newType) return;

        // Optimistic update
        const prevType = acc.type;
        acc.type = newType;
        renderAccountsView();

        try {
            const res = await fetch(`/api/accounts/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ type: newType })
            });

            if (!res.ok) throw new Error('Server returned error updating classification');
            showNotification(`Updated ${acc.name} classification to ${newType}`, 'success');
        } catch (err) {
            acc.type = prevType;
            renderAccountsView();
            showNotification('Error updating account: ' + err.message, 'error');
        }
    };

    // Row Click Handler
    window.handleAccountRowClick = function (e, id) {
        // Prevent opening if clicked checkbox, button, or link
        if (e.target.closest('button') || e.target.closest('a') || e.target.closest('input')) {
            return;
        }
        window.open360Modal(id);
    };

    // Navigate to full show page
    window.navigateToAccount = function (id) {
        if (typeof window.loadSpaPage === 'function') {
            window.loadSpaPage(`/accounts/${id}`, true);
        } else {
            window.location.href = `/accounts/${id}`;
        }
    };

    // Open 360 Degree Account Profile Modal
    window.open360Modal = async function (id) {
        const modal = document.getElementById('account-360-modal');
        if (!modal) return;

        state.viewing360Account = id;
        state.active360Tab = 'overview';
        modal.classList.remove('hidden');

        const titleEl = document.getElementById('modal-360-title');
        const subEl = document.getElementById('modal-360-subtitle');
        const bodyEl = document.getElementById('modal-360-tab-content');

        if (titleEl) titleEl.innerText = 'Loading Account 360°...';
        if (subEl) subEl.innerText = '';
        if (bodyEl) {
            bodyEl.innerHTML = `
                <div class="py-12 text-center text-zinc-400 text-xs">
                    <div class="animate-spin inline-block w-6 h-6 border-2 border-indigo-600 border-t-transparent rounded-full mb-2"></div>
                    <div>Consolidating 360° operations across sales, POs, logistics, and invoices...</div>
                </div>
            `;
        }

        try {
            const res = await fetch(`/api/accounts/${id}/360`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            if (!res.ok) throw new Error('Failed to load 360 profile');
            const data = await res.json();
            state.account360Data = data;
            render360ModalContent();
        } catch (err) {
            console.error('Error loading 360:', err);
            if (bodyEl) {
                bodyEl.innerHTML = `
                    <div class="p-6 text-center text-red-500 text-xs">
                        Error loading 360 profile: ${err.message}
                    </div>
                `;
            }
        }
    };

    window.close360Modal = function () {
        const modal = document.getElementById('account-360-modal');
        if (modal) modal.classList.add('hidden');
        state.viewing360Account = null;
        state.account360Data = null;
    };

    window.set360Tab = function (tabId) {
        state.active360Tab = tabId;
        render360ModalContent();
    };

    function render360ModalContent() {
        if (!state.account360Data) return;
        const { account, contacts, opportunities, purchaseDeals, shipments, payments } = state.account360Data;

        const titleEl = document.getElementById('modal-360-title');
        const subEl = document.getElementById('modal-360-subtitle');
        const tabsBarEl = document.getElementById('modal-360-segmented-tabs');
        const bodyEl = document.getElementById('modal-360-tab-content');

        if (titleEl) titleEl.innerText = account.name;
        if (subEl) {
            subEl.innerText = `${account.type} • ${account.industry || 'General Industry'} • Terms: ${account.payment_terms || 'Net 30'} • Owner: ${account.owner_name || account.assigned_to || 'Unassigned'}`;
        }

        // Render Tabs
        const tabs = [
            { id: 'overview', label: 'Company Overview' },
            { id: 'contacts', label: `Contacts (${contacts?.length || 0})` },
            { id: 'sales', label: `Sales Deals (${opportunities?.length || 0})` },
            { id: 'purchases', label: `PO Deals (${purchaseDeals?.length || 0})` },
            { id: 'shipping', label: `Logistics (${shipments?.length || 0})` },
            { id: 'finance', label: `Invoices (${payments?.length || 0})` },
        ];

        if (tabsBarEl) {
            tabsBarEl.innerHTML = tabs.map(t => {
                const isActive = state.active360Tab === t.id;
                return `
                    <button
                        type="button"
                        onclick="window.set360Tab('${t.id}')"
                        class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-all cursor-pointer whitespace-nowrap ${
                            isActive
                                ? 'bg-white text-zinc-950 dark:bg-zinc-800 dark:text-zinc-100 shadow-2xs border border-zinc-200 dark:border-zinc-700'
                                : 'text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-200'
                        }"
                    >
                        ${t.label}
                    </button>
                `;
            }).join('');
        }

        if (!bodyEl) return;

        // Render Active Tab Content
        if (state.active360Tab === 'overview') {
            // Find active custom fields with values
            const cfDefs = state.customFields.filter(cf => getPermission(cf.field_name) !== 'Hidden');
            const hasCustomFields = cfDefs.length > 0;

            bodyEl.innerHTML = `
                <div class="space-y-4">
                    <!-- Metric Cards -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="bg-zinc-50 dark:bg-zinc-800/60 p-3.5 rounded-xl border border-zinc-200 dark:border-zinc-800">
                            <div class="text-[11px] font-semibold text-zinc-500 uppercase">Credit Limit</div>
                            <div class="text-xl font-bold text-zinc-950 dark:text-zinc-100 mt-1">
                                ${formatCurrency(account.credit_limit, account.currency)}
                            </div>
                        </div>
                        <div class="bg-zinc-50 dark:bg-zinc-800/60 p-3.5 rounded-xl border border-zinc-200 dark:border-zinc-800">
                            <div class="text-[11px] font-semibold text-zinc-500 uppercase">Annual Revenue</div>
                            <div class="text-xl font-bold text-zinc-950 dark:text-zinc-100 mt-1">
                                ${formatCurrency(account.annual_revenue, account.currency)}
                            </div>
                        </div>
                        <div class="bg-zinc-50 dark:bg-zinc-800/60 p-3.5 rounded-xl border border-zinc-200 dark:border-zinc-800">
                            <div class="text-[11px] font-semibold text-zinc-500 uppercase">Account Classification</div>
                            <div class="mt-1.5">${getTypeBadge(account.type)}</div>
                        </div>
                    </div>

                    <!-- Address & Logistics Card -->
                    <div class="bg-zinc-50 dark:bg-zinc-800/60 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 text-xs space-y-2">
                        <div class="font-bold text-zinc-900 dark:text-zinc-100 text-xs">Addresses &amp; Logistics:</div>
                        <div class="text-zinc-700 dark:text-zinc-300">
                            <span class="text-zinc-400 font-medium">Billing Address:</span> ${account.billing_address || 'Not specified'}
                        </div>
                        <div class="text-zinc-700 dark:text-zinc-300">
                            <span class="text-zinc-400 font-medium">Shipping Address:</span> ${account.shipping_address || 'Not specified'}
                        </div>
                        <div class="text-zinc-700 dark:text-zinc-300">
                            <span class="text-zinc-400 font-medium">Phone &amp; Web:</span> ${account.phone || 'No phone'} ${account.website ? `• <a href="${account.website.startsWith('http') ? account.website : 'https://' + account.website}" target="_blank" class="text-indigo-600 hover:underline">${account.website}</a>` : ''}
                        </div>
                    </div>

                    <!-- Dynamic Custom Fields Section -->
                    ${hasCustomFields ? `
                        <div class="bg-zinc-50 dark:bg-zinc-800/60 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 space-y-2">
                            <div class="font-bold text-zinc-900 dark:text-zinc-100 text-xs flex items-center justify-between">
                                <span>Extended Dynamic Custom Fields</span>
                                <span class="text-[10px] text-zinc-400 font-normal">Field-Level Security Active</span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-1 text-xs">
                                ${cfDefs.map(cf => {
                                    const val = account[cf.field_name] || (account.custom_data && account.custom_data[cf.field_name]);
                                    const perm = getPermission(cf.field_name);
                                    let formattedVal = val || '—';
                                    if (cf.data_type === 'currency' && val) {
                                        formattedVal = formatCurrency(val, account.currency);
                                    }
                                    return `
                                        <div class="p-2.5 rounded-lg bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 flex flex-col justify-between">
                                            <div class="flex items-center justify-between text-[11px] text-zinc-400">
                                                <span>${cf.field_label}</span>
                                                ${perm === 'Read-Only' ? '<span class="text-[9px] text-amber-600 font-bold">🔒 Read-Only</span>' : ''}
                                            </div>
                                            <div class="font-semibold text-zinc-800 dark:text-zinc-200 mt-1">${formattedVal}</div>
                                        </div>
                                    `;
                                }).join('')}
                            </div>
                        </div>
                    ` : ''}

                    <!-- Account Notes -->
                    ${account.notes ? `
                        <div class="bg-zinc-50 dark:bg-zinc-800/60 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 text-xs">
                            <div class="font-bold text-zinc-900 dark:text-zinc-100 mb-1">Account Notes:</div>
                            <div class="text-zinc-600 dark:text-zinc-300 whitespace-pre-wrap">${account.notes}</div>
                        </div>
                    ` : ''}
                </div>
            `;
        } else if (state.active360Tab === 'contacts') {
            bodyEl.innerHTML = `
                <div class="space-y-2">
                    ${(!contacts || contacts.length === 0) ? `
                        <div class="text-center py-10 text-zinc-400 text-xs">No contacts linked to this account.</div>
                    ` : contacts.map(c => `
                        <div class="bg-zinc-50 dark:bg-zinc-800/60 p-3 rounded-xl border border-zinc-200 dark:border-zinc-800 flex items-center justify-between gap-3 text-xs">
                            <div>
                                <div class="font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-1.5">
                                    <span>${c.first_name} ${c.last_name}</span>
                                    ${c.is_primary ? '<span class="px-1.5 py-0.2 rounded bg-amber-100 text-amber-800 text-[10px] font-bold">⭐ Primary</span>' : ''}
                                </div>
                                <div class="text-zinc-500 text-[11px] mt-0.5">
                                    ${c.title || 'Executive'} • ${c.email || 'No email'} • ${c.phone || 'No phone'}
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-zinc-200 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-300">
                                ${c.contact_role || 'Contact'}
                            </span>
                        </div>
                    `).join('')}
                </div>
            `;
        } else if (state.active360Tab === 'sales') {
            bodyEl.innerHTML = `
                <div class="space-y-2">
                    ${(!opportunities || opportunities.length === 0) ? `
                        <div class="text-center py-10 text-zinc-400 text-xs">No sales deals / opportunities linked.</div>
                    ` : opportunities.map(o => `
                        <div class="bg-zinc-50 dark:bg-zinc-800/60 p-3 rounded-xl border border-zinc-200 dark:border-zinc-800 flex items-center justify-between gap-3 text-xs">
                            <div>
                                <div class="font-bold text-zinc-900 dark:text-zinc-100">${o.name}</div>
                                <div class="text-zinc-500 text-[11px] mt-0.5">
                                    Target Close: ${o.expected_close_date || '—'} • Probability: ${o.probability || 0}%
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-bold text-zinc-900 dark:text-zinc-100">${formatCurrency(o.amount, account.currency)}</div>
                                <span class="inline-block mt-0.5 px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-950/40 dark:text-blue-300">
                                    ${o.stage || 'Open'}
                                </span>
                            </div>
                        </div>
                    `).join('')}
                </div>
            `;
        } else if (state.active360Tab === 'purchases') {
            bodyEl.innerHTML = `
                <div class="space-y-2">
                    ${(!purchaseDeals || purchaseDeals.length === 0) ? `
                        <div class="text-center py-10 text-zinc-400 text-xs">No procurement purchase orders on record for this vendor.</div>
                    ` : purchaseDeals.map(p => `
                        <div class="bg-zinc-50 dark:bg-zinc-800/60 p-3 rounded-xl border border-zinc-200 dark:border-zinc-800 flex items-center justify-between gap-3 text-xs">
                            <div>
                                <div class="font-bold text-zinc-900 dark:text-zinc-100">${p.po_number || 'PO'}: ${p.title}</div>
                                <div class="text-zinc-500 text-[11px] mt-0.5">
                                    Target Delivery: ${p.expected_delivery_date || '—'}
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-bold text-zinc-900 dark:text-zinc-100">${formatCurrency(p.amount, account.currency)}</div>
                                <span class="inline-block mt-0.5 px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300">
                                    ${p.stage || 'Pending'}
                                </span>
                            </div>
                        </div>
                    `).join('')}
                </div>
            `;
        } else if (state.active360Tab === 'shipping') {
            bodyEl.innerHTML = `
                <div class="space-y-2">
                    ${(!shipments || shipments.length === 0) ? `
                        <div class="text-center py-10 text-zinc-400 text-xs">No logistics shipments on record.</div>
                    ` : shipments.map(s => `
                        <div class="bg-zinc-50 dark:bg-zinc-800/60 p-3 rounded-xl border border-zinc-200 dark:border-zinc-800 flex items-center justify-between gap-3 text-xs">
                            <div>
                                <div class="font-bold text-zinc-900 dark:text-zinc-100">${s.carrier || 'Freight'}: ${s.tracking_number}</div>
                                <div class="text-zinc-500 text-[11px] mt-0.5">
                                    ${s.type || 'Standard Freight'} • Estimated: ${s.estimated_delivery_date || '—'}
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300">
                                ${s.status || 'Dispatched'}
                            </span>
                        </div>
                    `).join('')}
                </div>
            `;
        } else if (state.active360Tab === 'finance') {
            bodyEl.innerHTML = `
                <div class="space-y-2">
                    ${(!payments || payments.length === 0) ? `
                        <div class="text-center py-10 text-zinc-400 text-xs">No invoices or payment aging records.</div>
                    ` : payments.map(pay => `
                        <div class="bg-zinc-50 dark:bg-zinc-800/60 p-3 rounded-xl border border-zinc-200 dark:border-zinc-800 flex items-center justify-between gap-3 text-xs">
                            <div>
                                <div class="font-bold text-zinc-900 dark:text-zinc-100">${pay.invoice_number} (${pay.type || 'Invoice'})</div>
                                <div class="text-zinc-500 text-[11px] mt-0.5">
                                    Due: ${pay.due_date || '—'} • Settled: ${formatCurrency(pay.paid_amount, account.currency)} / ${formatCurrency(pay.amount, account.currency)}
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold ${
                                pay.status === 'Paid'
                                    ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                                    : pay.status === 'Overdue'
                                    ? 'bg-red-50 text-red-700 border border-red-200'
                                    : 'bg-amber-50 text-amber-700 border border-amber-200'
                            }">
                                ${pay.status || 'Pending'}
                            </span>
                        </div>
                    `).join('')}
                </div>
            `;
        }

        if (window.lucide) lucide.createIcons();
    }

    // Modal: Open Create / Edit Account
    window.openAccountModal = function (mode = 'create', id = null, defaultType = null) {
        const modal = document.getElementById('account-form-modal');
        if (!modal) return;

        state.isCreateMode = (mode === 'create');
        state.editingAccount = null;

        const titleEl = document.getElementById('modal-account-form-title');
        const submitBtn = document.getElementById('btn-submit-account-form');

        if (state.isCreateMode) {
            if (titleEl) titleEl.innerText = 'Create Organization Account';
            if (submitBtn) submitBtn.innerText = 'Create Account';
            resetAccountForm(defaultType);
        } else {
            const acc = state.accounts.find(a => a.id == id);
            if (!acc) return;
            state.editingAccount = acc;
            if (titleEl) titleEl.innerText = `Edit Account: ${acc.name}`;
            if (submitBtn) submitBtn.innerText = 'Save Changes';
            populateAccountForm(acc);
        }

        renderDynamicCustomFieldInputs(state.editingAccount);
        modal.classList.remove('hidden');
        if (window.lucide) lucide.createIcons();
    };

    window.closeAccountModal = function () {
        const modal = document.getElementById('account-form-modal');
        if (modal) modal.classList.add('hidden');
        state.editingAccount = null;
    };

    function resetAccountForm(defaultType = null) {
        const f = document.getElementById('account-record-form');
        if (f) f.reset();
        const typeSelect = document.getElementById('acc-field-type');
        if (typeSelect && defaultType) {
            typeSelect.value = defaultType;
        }
        const currSelect = document.getElementById('acc-field-currency');
        if (currSelect) currSelect.value = 'USD';
    }

    function populateAccountForm(acc) {
        const setVal = (id, val) => {
            const el = document.getElementById(id);
            if (el) el.value = val !== undefined && val !== null ? val : '';
        };

        setVal('acc-field-name', acc.name);
        setVal('acc-field-type', acc.type);
        setVal('acc-field-industry', acc.industry);
        setVal('acc-field-website', acc.website);
        setVal('acc-field-phone', acc.phone);
        setVal('acc-field-currency', acc.currency);
        setVal('acc-field-payment_terms', acc.payment_terms);
        setVal('acc-field-credit_limit', acc.credit_limit);
        setVal('acc-field-annual_revenue', acc.annual_revenue);
        setVal('acc-field-assigned_to', acc.assigned_to);
        setVal('acc-field-billing_address', acc.billing_address);
        setVal('acc-field-shipping_address', acc.shipping_address);
        setVal('acc-field-notes', acc.notes);

        // Apply read-only locks to standard fields if permission is Read-Only
        ['name', 'type', 'industry', 'website', 'phone', 'currency', 'payment_terms', 'credit_limit', 'annual_revenue', 'billing_address', 'shipping_address', 'notes'].forEach(fieldName => {
            const el = document.getElementById(`acc-field-${fieldName}`);
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

    // Render Dynamic Custom Fields inside Form Modal respecting Permissions
    function renderDynamicCustomFieldInputs(acc = null) {
        const container = document.getElementById('account-custom-fields-form-container');
        if (!container) return;

        // Filter active custom fields: Skip completely if Hidden
        const activeFields = state.customFields.filter(cf => getPermission(cf.field_name) !== 'Hidden');

        if (activeFields.length === 0) {
            container.innerHTML = `
                <div class="col-span-full text-zinc-400 text-xs italic py-2">
                    No active custom fields configured for accounts module.
                </div>
            `;
            return;
        }

        container.innerHTML = activeFields.map(cf => {
            const perm = getPermission(cf.field_name);
            const isReadOnly = (perm === 'Read-Only');
            const fName = cf.field_name;
            const currentVal = acc
                ? (acc[fName] !== undefined ? acc[fName] : (acc.custom_data && acc.custom_data[fName]))
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
            } else if (dType === 'currency' || dType === 'number') {
                inputHtml = `
                    <div class="relative">
                        ${dType === 'currency' ? '<span class="absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400 text-xs">$</span>' : ''}
                        <input
                            type="number"
                            step="any"
                            name="custom_fields[${fName}]"
                            id="cf-input-${fName}"
                            value="${currentVal || ''}"
                            ${disabledAttr}
                            class="w-full ${dType === 'currency' ? 'pl-7' : 'px-3'} pr-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 ${readOnlyStyle} text-zinc-900 dark:text-zinc-100"
                        >
                    </div>
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
                        <span class="flex items-center gap-1">
                            ${cf.field_label}
                            ${cf.is_required ? '<span class="text-red-500">*</span>' : ''}
                        </span>
                        ${isReadOnly ? '<span class="text-[10px] text-amber-600 font-bold">🔒 Read-Only</span>' : ''}
                    </label>
                    ${inputHtml}
                    ${cf.help_text ? `<p class="text-[10px] text-zinc-400">${cf.help_text}</p>` : ''}
                </div>
            `;
        }).join('');
    }

    // Submit Create or Update Account Form
    window.handleAccountFormSubmit = async function (e) {
        e.preventDefault();
        const form = document.getElementById('account-record-form');
        if (!form) return;

        const formData = new FormData(form);
        const payload = {
            custom_fields: {},
        };

        // Extract regular fields & custom fields
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

        // Security check: Ignore any field marked Read-Only or Hidden
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

        const submitBtn = document.getElementById('btn-submit-account-form');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerText = 'Saving...';
        }

        try {
            const isCreate = state.isCreateMode;
            const url = isCreate ? '/api/accounts' : `/api/accounts/${state.editingAccount.id}`;
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
            if (!res.ok) {
                throw new Error(data.message || 'Validation or database error occurred');
            }

            showNotification(data.message || (isCreate ? 'Account created successfully!' : 'Account updated!'), 'success');
            window.closeAccountModal();
            await fetchAccounts();
        } catch (err) {
            console.error('Account save error:', err);
            showNotification(err.message, 'error');
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerText = state.isCreateMode ? 'Create Account' : 'Save Changes';
            }
        }
    };

    // Delete Account
    window.deleteAccount = async function (id) {
        const acc = state.accounts.find(a => a.id == id);
        const name = acc ? acc.name : 'this account';

        if (!confirm(`Are you sure you want to delete "${name}"? This action can be reversed by an administrator.`)) {
            return;
        }

        try {
            const res = await fetch(`/api/accounts/${id}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Failed to delete');
            showNotification(data.message || 'Account deleted successfully.', 'success');
            state.accounts = state.accounts.filter(a => a.id != id);
            renderAccountsView();
        } catch (err) {
            showNotification(err.message, 'error');
        }
    };

    // Transfer Account Ownership
    window.openTransferModal = function (id) {
        const acc = state.accounts.find(a => a.id == id);
        if (!acc) return;
        state.transferringAccount = acc;

        const modal = document.getElementById('account-transfer-modal');
        if (!modal) return;

        const nameEl = document.getElementById('transfer-account-name');
        if (nameEl) nameEl.innerText = acc.name;

        const currentOwnerEl = document.getElementById('transfer-current-owner');
        if (currentOwnerEl) currentOwnerEl.innerText = acc.assigned_to || acc.owner_name || 'Unassigned';

        const userSelect = document.getElementById('transfer-new-owner-select');
        if (userSelect) {
            userSelect.innerHTML = `
                <option value="">Select team member...</option>
                ${state.users.map(u => `<option value="${u.id}">${u.name} (${u.email})</option>`).join('')}
            `;
        }

        modal.classList.remove('hidden');
    };

    window.closeTransferModal = function () {
        const modal = document.getElementById('account-transfer-modal');
        if (modal) modal.classList.add('hidden');
        state.transferringAccount = null;
    };

    window.submitAccountTransfer = async function (e) {
        e.preventDefault();
        if (!state.transferringAccount) return;

        const select = document.getElementById('transfer-new-owner-select');
        const userId = select ? select.value : null;

        if (!userId) {
            showNotification('Please select a team member to assign ownership', 'error');
            return;
        }

        try {
            const res = await fetch(`/api/accounts/${state.transferringAccount.id}/transfer`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ owner_id: userId })
            });

            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Transfer failed');

            showNotification(data.message || 'Ownership transferred!', 'success');
            window.closeTransferModal();
            await fetchAccounts();
        } catch (err) {
            showNotification(err.message, 'error');
        }
    };

    // Filter Controls
    window.setAccountTypeFilter = function (typeId) {
        state.activeType = typeId;
        renderAccountsView();
    };

    window.switchAccountsViewMode = function (mode) {
        state.viewMode = mode;
        const btnTable = document.getElementById('btn-account-view-table');
        const btnKanban = document.getElementById('btn-account-view-kanban');

        if (mode === 'table') {
            if (btnTable) {
                btnTable.className = 'flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-md bg-white text-zinc-950 shadow-xs dark:bg-zinc-800 dark:text-zinc-100 cursor-pointer';
            }
            if (btnKanban) {
                btnKanban.className = 'flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-md text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-200 cursor-pointer';
            }
        } else {
            if (btnTable) {
                btnTable.className = 'flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-md text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-200 cursor-pointer';
            }
            if (btnKanban) {
                btnKanban.className = 'flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-md bg-white text-zinc-950 shadow-xs dark:bg-zinc-800 dark:text-zinc-100 cursor-pointer';
            }
        }

        renderAccountsView();
    };

    // Customize Fields Modal
    window.openCustomizeFieldsModal = function () {
        const modal = document.getElementById('account-customize-modal');
        if (!modal) return;

        const fieldDefs = getVisibleFieldDefinitions();
        const activeList = state.viewMode === 'table' ? state.tableVisibleFields : state.kanbanVisibleFields;
        const container = document.getElementById('customize-fields-checkboxes-container');

        if (container) {
            container.innerHTML = fieldDefs.map(f => {
                const isChecked = activeList.includes(f.name);
                return `
                    <label class="flex items-center gap-2 p-2 rounded-lg border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800/60 cursor-pointer text-xs">
                        <input
                            type="checkbox"
                            value="${f.name}"
                            ${isChecked ? 'checked' : ''}
                            class="rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer w-3.5 h-3.5"
                        >
                        <span class="font-medium text-zinc-800 dark:text-zinc-200">${f.label}</span>
                        ${f.isCustom ? '<span class="text-[9px] px-1 rounded bg-zinc-200 dark:bg-zinc-700 text-zinc-600 dark:text-zinc-300 font-mono ml-auto">Custom</span>' : ''}
                    </label>
                `;
            }).join('');
        }

        modal.classList.remove('hidden');
    };

    window.closeCustomizeFieldsModal = function () {
        const modal = document.getElementById('account-customize-modal');
        if (modal) modal.classList.add('hidden');
    };

    window.saveCustomizedFields = function () {
        const container = document.getElementById('customize-fields-checkboxes-container');
        if (!container) return;

        const checkedBoxes = Array.from(container.querySelectorAll('input[type="checkbox"]:checked'));
        const newKeys = checkedBoxes.map(cb => cb.value);

        if (newKeys.length === 0) {
            showNotification('Please select at least one visible column', 'error');
            return;
        }

        if (state.viewMode === 'table') {
            state.tableVisibleFields = newKeys;
        } else {
            state.kanbanVisibleFields = newKeys;
        }

        saveViewConfigs();
        window.closeCustomizeFieldsModal();
        renderAccountsView();
        showNotification('Visible fields updated successfully', 'success');
    };

    // Filter Modal
    window.openAccountFilterModal = function () {
        const modal = document.getElementById('account-filter-modal');
        if (!modal) return;

        const fieldDefs = getVisibleFieldDefinitions();
        const select = document.getElementById('filter-rule-field-select');
        if (select) {
            select.innerHTML = fieldDefs.map(f => `<option value="${f.name}">${f.label}</option>`).join('');
        }

        modal.classList.remove('hidden');
    };

    window.closeAccountFilterModal = function () {
        const modal = document.getElementById('account-filter-modal');
        if (modal) modal.classList.add('hidden');
    };

    window.addAccountFilterRule = function () {
        const fieldEl = document.getElementById('filter-rule-field-select');
        const opEl = document.getElementById('filter-rule-operator-select');
        const valEl = document.getElementById('filter-rule-value-input');

        const field = fieldEl ? fieldEl.value : '';
        const op = opEl ? opEl.value : 'contains';
        const val = valEl ? valEl.value.trim() : '';

        if (!val) {
            showNotification('Please specify a filter comparison value', 'error');
            return;
        }

        state.activeFilters.push({
            id: 'rule_' + Date.now(),
            field: field,
            operator: op,
            value: val,
        });

        if (valEl) valEl.value = '';
        window.closeAccountFilterModal();
        renderAccountsView();
    };

    window.removeAccountFilterRule = function (id) {
        state.activeFilters = state.activeFilters.filter(r => r.id !== id);
        renderAccountsView();
    };

    window.clearAllAccountFilters = function () {
        state.activeFilters = [];
        renderAccountsView();
    };

    // Selection Handling
    window.toggleSelectAllAccounts = function (checked) {
        const filtered = getFilteredAccounts();
        if (checked) {
            filtered.forEach(a => state.selectedAccountIds.add(a.id));
        } else {
            state.selectedAccountIds.clear();
        }
        renderAccountsView();
    };

    window.toggleSelectAccount = function (id, checked) {
        if (checked) {
            state.selectedAccountIds.add(id);
        } else {
            state.selectedAccountIds.delete(id);
        }
        renderAccountsView();
    };

    // Export CSV
    window.exportAccountsCsv = function () {
        const filtered = getFilteredAccounts();
        if (filtered.length === 0) {
            showNotification('No accounts available to export', 'error');
            return;
        }

        const fieldDefs = getVisibleFieldDefinitions();
        const headers = fieldDefs.map(f => `"${f.label.replace(/"/g, '""')}"`);

        const rows = filtered.map(acc => {
            return fieldDefs.map(f => {
                const v = acc[f.name] !== undefined && acc[f.name] !== null ? String(acc[f.name]) : '';
                return `"${v.replace(/"/g, '""')}"`;
            }).join(',');
        });

        const csvContent = 'data:text/csv;charset=utf-8,' + [headers.join(','), ...rows].join('\n');
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement('a');
        link.setAttribute('href', encodedUri);
        link.setAttribute('download', `accounts_export_${new Date().toISOString().slice(0, 10)}.csv`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        showNotification(`Exported ${filtered.length} accounts to CSV`, 'success');
    };

    // Module Initializer
    window.initAccountsModule = function () {
        const root = document.getElementById('accounts-page-root');
        if (!root) return;

        // Hydrate from window.__ACCOUNTS_DATA__
        if (window.__ACCOUNTS_DATA__) {
            state.accounts = window.__ACCOUNTS_DATA__.initialAccounts || [];
            state.customFields = window.__ACCOUNTS_DATA__.customFields || [];
            state.fieldPermissions = window.__ACCOUNTS_DATA__.fieldPermissions || {};
            state.users = window.__ACCOUNTS_DATA__.users || [];
            state.types = window.__ACCOUNTS_DATA__.types || state.types;
            state.paymentTerms = window.__ACCOUNTS_DATA__.paymentTerms || state.paymentTerms;
            state.currencies = window.__ACCOUNTS_DATA__.currencies || state.currencies;
        }

        loadSavedViewConfigs();

        // Search listener
        const searchInput = document.getElementById('accounts-search-input');
        if (searchInput) {
            searchInput.value = state.searchTerm;
            searchInput.addEventListener('input', (e) => {
                state.searchTerm = e.target.value;
                renderAccountsView();
            });
        }

        renderAccountsView();
    };

    // Auto-run if DOM already loaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', window.initAccountsModule);
    } else {
        window.initAccountsModule();
    }
})();
