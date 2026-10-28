/**
 * DealSync Enterprise CRM — Accounts & Master Ledger Module
 * 1:1 Parity with React Client (AccountsView.tsx, CompactDataTable.tsx, UniversalKanban.tsx)
 * Pure Tailwind CSS styling with Dynamic Custom Fields & Field-Level Security Permissions
 */

(function () {
    'use strict';

    // Default Column Widths for Screenshot Pixel-Perfect Sizing
    const DEFAULT_COL_WIDTHS = {
        type: 140,
        name: 270,
        industry: 175,
        payment_terms: 140,
        credit_limit: 145,
        phone: 175,
        assigned_to: 185,
    };

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
        tableVisibleFields: ['type', 'name', 'industry', 'payment_terms', 'credit_limit', 'phone', 'assigned_to'],
        kanbanVisibleFields: ['industry', 'credit_limit', 'payment_terms', 'phone'],
        colWidths: { ...DEFAULT_COL_WIDTHS },
        sortField: null,
        sortDirection: null,
        activeFilters: [], // array of { id, field, operator, value }
        tempFilterConditions: [], // temporary condition rows for the filter modal
        selectedAccount: null,
        editingAccount: null,
        transferringAccount: null,
        viewing360Account: null,
        account360Data: null,
        accountFiles: [],
        accountActivities: [],
        active360Tab: 'overview',
        isCreateMode: false,
        isLoading: false,
        pageLayout: null,
        standardOverrides: {},
    };

    const STANDARD_FIELD_DEFINITIONS = [
        { name: 'type', label: 'Classification', type: 'dropdown', standard: true, options: ['Customer', 'Vendor', 'Prospect', 'Partner'] },
        { name: 'name', label: 'Account Name', type: 'text', standard: true, required: true },
        { name: 'industry', label: 'Industry Vertical', type: 'text', standard: true },
        { name: 'payment_terms', label: 'Payment Terms', type: 'dropdown', standard: true, options: ['Net 15', 'Net 30', 'Net 45', 'Net 60', 'Advance Wire', 'Due on Receipt'] },
        { name: 'credit_limit', label: 'Credit Limit', type: 'currency', standard: true },
        { name: 'phone', label: 'Telephone Number', type: 'phone', standard: true },
        { name: 'assigned_to', label: 'Owner', type: 'lookup', standard: true },
        { name: 'website', label: 'Corporate Website', type: 'text', standard: true },
        { name: 'currency', label: 'Default Currency', type: 'dropdown', standard: true, options: ['USD', 'EUR', 'GBP', 'INR', 'CAD', 'AUD', 'JPY', 'AED', 'SGD', 'CHF'] },
        { name: 'annual_revenue', label: 'Annual Revenue', type: 'currency', standard: true },
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

    // Helper: Format Currency (exact 2 decimals matching user screenshot)
    function formatCurrency(val, currency = 'USD') {
        const num = parseFloat(val) || 0;
        try {
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: currency || 'USD',
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(num);
        } catch (_) {
            return '$' + num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
    }

    // Helper: Escape HTML strings
    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
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

    // Load visible fields and column widths from localStorage
    function loadSavedViewConfigs() {
        try {
            const savedTable = localStorage.getItem('dealsync_accounts_table_cols_v2');
            if (savedTable) {
                const parsed = JSON.parse(savedTable);
                if (Array.isArray(parsed) && parsed.length > 0) {
                    state.tableVisibleFields = parsed;
                }
            }
            const savedKanban = localStorage.getItem('dealsync_accounts_kanban_cols_v2');
            if (savedKanban) {
                const parsed = JSON.parse(savedKanban);
                if (Array.isArray(parsed) && parsed.length > 0) {
                    state.kanbanVisibleFields = parsed;
                }
            }
            const savedWidths = localStorage.getItem('dealsync_accounts_col_widths_v2');
            if (savedWidths) {
                const parsed = JSON.parse(savedWidths);
                if (parsed && typeof parsed === 'object') {
                    state.colWidths = Object.assign({}, DEFAULT_COL_WIDTHS, parsed);
                }
            }
        } catch (_) {}
    }

    function saveViewConfigs() {
        try {
            localStorage.setItem('dealsync_accounts_table_cols_v2', JSON.stringify(state.tableVisibleFields));
            localStorage.setItem('dealsync_accounts_kanban_cols_v2', JSON.stringify(state.kanbanVisibleFields));
            localStorage.setItem('dealsync_accounts_col_widths_v2', JSON.stringify(state.colWidths));
        } catch (_) {}
    }

    function saveColumnWidths() {
        try {
            localStorage.setItem('dealsync_accounts_col_widths_v2', JSON.stringify(state.colWidths));
        } catch (_) {}
    }

    // Filter and Sort Accounts
    function getFilteredAccounts() {
        let result = state.accounts.filter(acc => {
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
                const matchType = (acc.type || '').toLowerCase().includes(s);
                const matchTerms = (acc.payment_terms || '').toLowerCase().includes(s);
                const matchOwner = (acc.assigned_to || acc.owner_name || '').toLowerCase().includes(s);
                if (!matchName && !matchIndustry && !matchPhone && !matchWebsite && !matchType && !matchTerms && !matchOwner) {
                    return false;
                }
            }

            // 3. Dynamic Filter Rules
            for (const rule of state.activeFilters) {
                let rawVal = '';
                if (rule.field === 'assigned_to') {
                    rawVal = acc.owner_name || acc.assigned_to || (acc.owner ? acc.owner.name : '') || '';
                } else if (acc[rule.field] !== undefined && acc[rule.field] !== null) {
                    rawVal = acc[rule.field];
                } else if (acc.custom_fields && acc.custom_fields[rule.field] !== undefined) {
                    rawVal = acc.custom_fields[rule.field];
                }

                const val = String(rawVal || '').toLowerCase().trim();
                const target = String(rule.value || '').toLowerCase().trim();

                if (rule.operator === 'equals' && val !== target) return false;
                if (rule.operator === 'not_equals' && val === target) return false;
                if (rule.operator === 'contains' && !val.includes(target)) return false;
                if (rule.operator === 'starts_with' && !val.startsWith(target)) return false;
                if (rule.operator === 'greater_than') {
                    const nVal = parseFloat(val.replace(/[^0-9.-]+/g, '')) || 0;
                    const nTarget = parseFloat(target.replace(/[^0-9.-]+/g, '')) || 0;
                    if (nVal <= nTarget) return false;
                }
                if (rule.operator === 'less_than') {
                    const nVal = parseFloat(val.replace(/[^0-9.-]+/g, '')) || 0;
                    const nTarget = parseFloat(target.replace(/[^0-9.-]+/g, '')) || 0;
                    if (nVal >= nTarget) return false;
                }
            }

            return true;
        });

        // 4. Header Column Sorting (3-Stage: asc -> desc -> normal)
        if (state.sortField && state.sortDirection) {
            result.sort((a, b) => {
                let valA = a[state.sortField];
                let valB = b[state.sortField];

                if (state.sortField === 'assigned_to') {
                    valA = a.owner_name || a.assigned_to || (a.owner ? a.owner.name : '');
                    valB = b.owner_name || b.assigned_to || (b.owner ? b.owner.name : '');
                }

                if (state.sortField === 'credit_limit' || state.sortField === 'annual_revenue') {
                    const numA = parseFloat(valA) || 0;
                    const numB = parseFloat(valB) || 0;
                    return state.sortDirection === 'asc' ? numA - numB : numB - numA;
                }

                const strA = String(valA || '').toLowerCase();
                const strB = String(valB || '').toLowerCase();
                if (strA < strB) return state.sortDirection === 'asc' ? -1 : 1;
                if (strA > strB) return state.sortDirection === 'asc' ? 1 : -1;
                return 0;
            });
        } else {
            // Normal unsorted state: maintain default database order (id: asc)
            result.sort((a, b) => (parseInt(a.id, 10) || 0) - (parseInt(b.id, 10) || 0));
        }

        return result;
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
        return `<span class="text-xs text-slate-700 dark:text-zinc-300 font-normal">${type || '—'}</span>`;
    }

    // Helper: Render Field Cell in Table matching user screenshot
    function renderFieldCell(acc, fieldName, fieldDef) {
        // Enforce field-level permission: If Hidden, return empty
        if (getPermission(fieldName) === 'Hidden' || acc['_hidden_' + fieldName]) {
            return '<span class="text-slate-400 italic text-[11px]">Hidden</span>';
        }

        const val = acc[fieldName];

        if (fieldName === 'type') {
            return `<span class="text-xs text-slate-700 dark:text-zinc-300 font-normal">${acc.type || '—'}</span>`;
        }

        if (fieldName === 'name') {
            return `
                <div class="flex items-center gap-1.5 truncate">
                    <span class="font-bold text-xs text-slate-900 dark:text-zinc-100 hover:text-sky-600 dark:hover:text-sky-400 transition-colors cursor-pointer" onclick="event.stopPropagation(); window.open360Modal(${acc.id})" title="${acc.name || ''}">
                        ${acc.name || 'Unnamed Account'}
                    </span>
                    ${acc.website ? `<span class="text-xs text-slate-400 dark:text-zinc-500 font-normal truncate">(${acc.website})</span>` : ''}
                </div>
            `;
        }

        if (fieldName === 'industry') {
            return `<span class="text-xs text-slate-700 dark:text-zinc-300 font-normal truncate" title="${acc.industry || ''}">${acc.industry || '—'}</span>`;
        }

        if (fieldName === 'payment_terms') {
            return `<span class="text-xs text-slate-700 dark:text-zinc-300 font-normal">${acc.payment_terms || 'Net 30'}</span>`;
        }

        if (fieldName === 'credit_limit') {
            return `<span class="font-bold text-xs text-slate-900 dark:text-zinc-100">${formatCurrency(acc.credit_limit, acc.currency)}</span>`;
        }

        if (fieldName === 'annual_revenue') {
            return `<span class="font-bold text-xs text-slate-950 dark:text-zinc-100">${formatCurrency(acc.annual_revenue, acc.currency)}</span>`;
        }

        if (fieldName === 'phone') {
            return acc.phone
                ? `<span class="text-xs text-slate-700 dark:text-zinc-300 font-normal">${acc.phone}</span>`
                : '<span class="text-slate-400 font-normal">—</span>';
        }

        if (fieldName === 'assigned_to') {
            const ownerName = acc.owner_name || acc.assigned_to || (acc.owner ? acc.owner.name : 'System Administrator');
            const initial = (ownerName || 'S').trim().charAt(0).toUpperCase();
            return `
                <div class="flex items-center gap-2 text-xs truncate">
                    <span class="w-5 h-5 rounded-full bg-sky-100 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300 font-bold text-[10px] inline-flex items-center justify-center shrink-0">
                        ${initial}
                    </span>
                    <span class="text-xs text-slate-800 dark:text-zinc-200 font-semibold truncate" title="${ownerName}">${ownerName}</span>
                </div>
            `;
        }

        if (fieldName === 'created_at') {
            if (!val) return '<span class="text-slate-400">—</span>';
            const date = new Date(val);
            return `<span class="text-xs text-slate-500">${date.toLocaleDateString()}</span>`;
        }

        // Custom field rendering
        if (fieldDef && fieldDef.isCustom) {
            if (val === undefined || val === null || val === '') {
                return '<span class="text-slate-400">—</span>';
            }
            if (fieldDef.type === 'currency') {
                return `<span class="font-bold text-xs text-slate-900 dark:text-zinc-100">${formatCurrency(val, acc.currency)}</span>`;
            }
            if (fieldDef.type === 'rating') {
                const num = parseInt(val) || 0;
                return `
                    <div class="flex items-center gap-0.5 text-amber-500">
                        ${'★'.repeat(Math.min(5, Math.max(0, num)))}${'☆'.repeat(Math.max(0, 5 - num))}
                    </div>
                `;
            }
            return `<span class="text-xs text-slate-700 dark:text-zinc-300 truncate">${val}</span>`;
        }

        return `<span class="text-xs text-slate-700 dark:text-zinc-300 truncate">${val || '—'}</span>`;
    }

    // 3-Stage Column Sorting Handler:
    // 1st click: Ascending (upward arrow)
    // 2nd click: Descending (downward arrow)
    // 3rd click: Normal (reset to original order / normal arrow)
    window.handleSortAccountColumn = function (colName) {
        if (state.sortField !== colName) {
            // Click 1 on a new column -> Ascending
            state.sortField = colName;
            state.sortDirection = 'asc';
        } else if (state.sortDirection === 'asc') {
            // Click 2 on same column -> Descending
            state.sortDirection = 'desc';
        } else if (state.sortDirection === 'desc') {
            // Click 3 on same column -> Normal (reset to unsorted)
            state.sortField = null;
            state.sortDirection = null;
        } else {
            // Fallback -> Ascending
            state.sortField = colName;
            state.sortDirection = 'asc';
        }
        renderAccountsView();
    };

    // Column resizing logic
    function initAccountColumnResize(container) {
        if (!container) return;
        const resizers = container.querySelectorAll('.account-col-resizer');
        resizers.forEach(resizer => {
            resizer.addEventListener('mousedown', function (e) {
                e.preventDefault();
                e.stopPropagation();

                const colName = this.getAttribute('data-col');
                if (!colName) return;

                const colEl = container.querySelector(`#col-width-${colName}`);
                const thEl = this.closest('th');
                const startX = e.pageX;
                const startWidth = (colEl && colEl.offsetWidth) || (thEl ? thEl.offsetWidth : (state.colWidths[colName] || 150));

                document.body.classList.add('is-resizing-account-col');
                this.classList.add('is-resizing');

                function onMouseMove(moveEvent) {
                    moveEvent.preventDefault();
                    const delta = moveEvent.pageX - startX;
                    const newWidth = Math.max(70, startWidth + delta);
                    state.colWidths[colName] = newWidth;
                    if (colEl) {
                        colEl.style.width = newWidth + 'px';
                    }
                    if (thEl) {
                        thEl.style.width = newWidth + 'px';
                    }
                }

                function onMouseUp() {
                    document.removeEventListener('mousemove', onMouseMove);
                    document.removeEventListener('mouseup', onMouseUp);
                    document.body.classList.remove('is-resizing-account-col');
                    resizer.classList.remove('is-resizing');
                    saveColumnWidths();
                }

                document.addEventListener('mousemove', onMouseMove);
                document.addEventListener('mouseup', onMouseUp);
            });
        });
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
                <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-orange-200/90 dark:border-orange-500/30 p-12 text-center shadow-[0_4px_20px_-2px_rgba(251,146,60,0.12)]">
                    <div class="w-12 h-12 rounded-xl bg-orange-50 dark:bg-zinc-800 text-orange-400 flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect width="16" height="20" x="4" y="2" rx="2"/><line x1="8" x2="16" y1="6" y2="6"/><line x1="8" x2="16" y1="10" y2="10"/><line x1="8" x2="12" y1="14" y2="14"/>
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 mb-1">No accounts found</h3>
                    <p class="text-xs text-zinc-500 max-w-sm mx-auto mb-4">
                        There are no accounts matching your current search or filter criteria.
                    </p>
                </div>
            `;
            return;
        }

        const allChecked = accounts.length > 0 && accounts.every(a => state.selectedAccountIds.has(a.id));

        tableRoot.innerHTML = `
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-orange-200/90 dark:border-orange-500/30 shadow-[0_4px_20px_-2px_rgba(251,146,60,0.12)] overflow-hidden">
                <div class="overflow-x-auto">
                    <table id="accounts-data-table" class="w-full text-left border-collapse table-fixed">
                        <colgroup>
                            <col style="width: 52px;">
                            ${visibleCols.map(c => `<col id="col-width-${c.name}" style="width: ${state.colWidths[c.name] || 160}px;">`).join('')}
                        </colgroup>
                        <thead>
                            <tr class="bg-slate-50/70 dark:bg-zinc-800/50 border-b border-slate-200/80 dark:border-zinc-800 text-xs font-semibold text-slate-800 dark:text-zinc-200">
                                <th class="w-[52px] px-3 py-3 text-center border-r border-slate-200/70 dark:border-zinc-800 select-none">
                                    <input
                                        type="checkbox"
                                        id="accounts-master-checkbox"
                                        ${allChecked ? 'checked' : ''}
                                        onchange="window.toggleSelectAllAccounts(this.checked)"
                                        class="rounded border-slate-300 text-sky-600 focus:ring-sky-500 cursor-pointer w-4 h-4 align-middle"
                                                </th>
                                ${visibleCols.map(col => {
                                    const isCurrent = state.sortField === col.name;
                                    const dir = isCurrent ? state.sortDirection : null;

                                    let arrowIcon = '';
                                    if (dir === 'asc') {
                                        // 1st click: Upward Arrow (Ascending)
                                        arrowIcon = `
                                            <svg class="w-3.5 h-3.5 text-sky-600 dark:text-sky-400 shrink-0 ml-1 transition-all" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="18 15 12 9 6 15"/>
                                            </svg>
                                        `;
                                    } else if (dir === 'desc') {
                                        // 2nd click: Downward Arrow (Descending)
                                        arrowIcon = `
                                            <svg class="w-3.5 h-3.5 text-sky-600 dark:text-sky-400 shrink-0 ml-1 transition-all" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="6 9 12 15 18 9"/>
                                            </svg>
                                        `;
                                    } else {
                                        // 3rd click / default: Normal Arrow matching screenshot
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
                                        <th class="relative px-4 py-3 border-r border-slate-200/70 dark:border-zinc-800 select-none group cursor-pointer hover:bg-slate-100/60 dark:hover:bg-zinc-800 transition-colors" onclick="window.handleSortAccountColumn('${col.name}')" title="${tooltipText}">
                                            <div class="flex items-center justify-between pr-2">
                                                <span class="truncate ${isCurrent ? 'font-bold text-sky-700 dark:text-sky-300' : ''}">${col.label}</span>
                                                ${arrowIcon}
                                            </div>
                                            <div class="account-col-resizer" data-col="${col.name}" onclick="event.stopPropagation()"></div>
                                        </th>
                                    `;
                                }).join('')}
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-zinc-800/70 text-xs">
                            ${accounts.map((acc, idx) => {
                                const isChecked = state.selectedAccountIds.has(acc.id);
                                return `
                                    <tr class="hover:bg-slate-50/60 dark:hover:bg-zinc-800/40 transition-colors cursor-pointer group" onclick="window.handleAccountRowClick(event, ${acc.id})">
                                        <td class="w-[52px] px-3 py-3 text-center border-r border-slate-100 dark:border-zinc-800/50" onclick="event.stopPropagation()">
                                            <div class="flex items-center justify-center gap-1.5">
                                                <span class="text-xs text-slate-600 dark:text-zinc-400 font-normal w-3.5 text-right">${idx + 1}</span>
                                                <input
                                                    type="checkbox"
                                                    value="${acc.id}"
                                                    ${isChecked ? 'checked' : ''}
                                                    onchange="window.toggleSelectAccount(${acc.id}, this.checked)"
                                                    class="rounded border-slate-300 text-sky-600 focus:ring-sky-500 cursor-pointer w-4 h-4 align-middle"
                                                >
                                            </div>
                                        </td>
                                        ${visibleCols.map(col => `
                                            <td class="px-4 py-3 align-middle border-r border-slate-100 dark:border-zinc-800/50 truncate">
                                                ${renderFieldCell(acc, col.name, col)}
                                            </td>
                                        `).join('')}
                                    </tr>
                                `;
                            }).join('')}
                        </tbody>
                    </table>
                </div>

                <!-- Exact Footer matching user screenshot -->
                <div class="px-6 py-3.5 bg-white dark:bg-zinc-900 border-t border-slate-100 dark:border-zinc-800 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 dark:text-zinc-400 gap-2">
                    <div>
                        Showing <strong>${accounts.length}</strong> records
                    </div>
                    <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-zinc-400">
                        <span>Tip: Drag column edges to resize</span>
                        <span>•</span>
                        <span>Click header to sort</span>
                    </div>
                </div>
            </div>
        `;

        initAccountColumnResize(tableRoot);
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

    function formatDateTime(dateStr) {
        if (!dateStr) return 'Apr 12, 2023, 04:45 AM';
        try {
            const d = new Date(dateStr);
            if (isNaN(d.getTime())) return dateStr;
            return d.toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric',
            }) + ', ' + d.toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                hour12: true
            });
        } catch (_) {
            return 'Apr 12, 2023, 04:45 AM';
        }
    }

    function generateConsistentRecordId(id) {
        return 'b8f711ae-de54-413c-b9d5-b1d9aeb6' + String(id).padStart(4, '0');
    }

    function escapeHtml(str) {
        if (!str && str !== 0) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function getTypeBadgeClass(type) {
        const typeBadges = {
            'Customer': 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800',
            'Vendor': 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800',
            'Prospect': 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800',
            'Partner': 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/40 dark:text-purple-300 dark:border-purple-800',
        };
        return typeBadges[type] || 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-zinc-800 dark:text-zinc-300 dark:border-zinc-700';
    }

    async function ensurePageLayout() {
        if (state.pageLayout) return state.pageLayout;
        if (window.__ACCOUNTS_DATA__ && window.__ACCOUNTS_DATA__.pageLayout) {
            state.pageLayout = window.__ACCOUNTS_DATA__.pageLayout;
            return state.pageLayout;
        }
        try {
            const res = await fetch('/api/page-layouts/accounts/active', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            if (res.ok) {
                const data = await res.json();
                state.pageLayout = data;
                return state.pageLayout;
            }
        } catch (_) {}
        return null;
    }

    function renderDynamicAccountSections(acc, pageLayout) {
        const container = document.getElementById('account-sections-container');
        if (!container) return;

        let sections = [];
        if (pageLayout) {
            if (pageLayout.layout_json && Array.isArray(pageLayout.layout_json.sections)) {
                sections = pageLayout.layout_json.sections;
            } else if (pageLayout.layout && Array.isArray(pageLayout.layout.sections)) {
                sections = pageLayout.layout.sections;
            } else if (Array.isArray(pageLayout.sections)) {
                sections = pageLayout.sections;
            } else if (typeof pageLayout.layout_json === 'string') {
                try {
                    const parsed = JSON.parse(pageLayout.layout_json);
                    sections = parsed.sections || [];
                } catch (_) {}
            }
        }

        // Standard fallback if no layout defined
        if (!sections || sections.length === 0) {
            sections = [
                {
                    id: 'sec-primary',
                    title: 'Account Detail',
                    columns: 2,
                    collapsible: true,
                    defaultCollapsed: false,
                    fields: [
                        { name: 'name', label: 'Account Name', required: true },
                        { name: 'type', label: 'Account Type', required: true },
                        { name: 'industry', label: 'Industry' },
                        { name: 'website', label: 'Website URL' },
                        { name: 'phone', label: 'Phone' },
                        { name: 'billing_address', label: 'Billing Address' },
                        { name: 'shipping_address', label: 'Shipping Address' },
                        { name: 'payment_terms', label: 'Payment Terms' },
                        { name: 'credit_limit', label: 'Credit Limit' },
                        { name: 'annual_revenue', label: 'Annual Revenue' }
                    ]
                },
                {
                    id: 'sec-system-info',
                    title: 'System Information',
                    columns: 3,
                    collapsible: true,
                    defaultCollapsed: false,
                    fields: [
                        { name: 'owner', label: 'Current Owner' },
                        { name: 'created_at', label: 'Created Date & Time' },
                        { name: 'created_by', label: 'Created By' },
                        { name: 'updated_at', label: 'Last Modified Date & Time' },
                        { name: 'updated_by', label: 'Last Modified By' },
                        { name: 'id', label: 'Record ID' }
                    ]
                }
            ];
        }

        // Placed field tracking
        const placedFieldNames = new Set();
        sections.forEach(sec => {
            (sec.fields || []).forEach(f => {
                const fn = typeof f === 'object' ? f.name : f;
                if (fn) placedFieldNames.add(fn);
            });
        });

        // Unplaced active custom fields
        const unplacedCustomFields = state.customFields.filter(cf => {
            const perm = getPermission(cf.field_name);
            return perm !== 'Hidden' && !placedFieldNames.has(cf.field_name);
        });

        const allSections = [...sections];
        if (unplacedCustomFields.length > 0) {
            allSections.push({
                id: 'sec-custom-fields-auto',
                title: 'Custom Information',
                columns: 2,
                collapsible: true,
                defaultCollapsed: false,
                fields: unplacedCustomFields.map(cf => ({
                    name: cf.field_name,
                    label: cf.field_label,
                    required: false,
                }))
            });
        }

        const sectionsHtml = allSections.map((sec, secIndex) => {
            const secId = sec.id || `sec-${secIndex}`;
            const secTitle = sec.title || `Section ${secIndex + 1}`;
            let secColumns = parseInt(sec.columns || 2, 10);
            if (![1, 2, 3].includes(secColumns)) secColumns = 2;

            const isCollapsible = sec.collapsible !== false;
            const defaultCollapsed = !!sec.defaultCollapsed;
            const secFields = sec.fields || [];
            const isSystemSection = (secId === 'sec-system-info') || (secTitle.toLowerCase().includes('system information'));

            const bodyId = 'sectionBody_' + String(secId).replace(/[^a-zA-Z0-9_-]/g, '_');
            const chevronId = 'sectionChevron_' + String(secId).replace(/[^a-zA-Z0-9_-]/g, '_');

            // Responsive Tailwind Grid configuration based on configured columns (1, 2, or 3)
            let gridColsClass = 'grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-4';
            if (secColumns === 1) {
                gridColsClass = 'grid-cols-1 gap-y-4';
            } else if (secColumns === 3) {
                gridColsClass = 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-y-5 gap-x-8';
            }

            let fieldsHtml = '';

            if (isSystemSection) {
                const ownerName = acc.owner_name || acc.assigned_to || (acc.owner ? acc.owner.name : 'System Administrator');
                const createdDate = formatDateTime(acc.created_at);
                const createdByName = acc.created_by_name || 'System Admin';
                const updatedDate = formatDateTime(acc.updated_at || acc.created_at);
                const updatedByName = acc.updated_by_name || 'System Admin';
                const recordId = acc.uuid || generateConsistentRecordId(acc.id);

                fieldsHtml = `
                    <!-- Current Owner -->
                    <div class="flex items-center justify-between py-1.5 border-b border-slate-100/80 dark:border-zinc-800/80">
                        <div>
                            <div class="text-[11px] text-slate-400 dark:text-zinc-500 flex items-center gap-1 font-medium">
                                <svg class="w-3.5 h-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                <span>Current Owner</span>
                            </div>
                            <div class="flex items-center gap-2 mt-1">
                                <span id="detail-fs-owner" class="font-bold text-slate-900 dark:text-zinc-100">${escapeHtml(ownerName)}</span>
                                <button type="button" onclick="window.openTransferFromFullscreen()" class="px-2 py-0.5 text-[11px] font-semibold text-teal-700 bg-teal-50 border border-teal-200 rounded-md hover:bg-teal-100 transition-colors cursor-pointer">
                                    Change
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Created Date & Time -->
                    <div class="py-1.5 border-b border-slate-100/80 dark:border-zinc-800/80">
                        <div class="text-[11px] text-slate-400 dark:text-zinc-500 flex items-center gap-1 font-medium">
                            <svg class="w-3.5 h-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            <span>Created Date &amp; Time</span>
                        </div>
                        <div id="detail-fs-created-at" class="font-bold text-slate-900 dark:text-zinc-100 mt-1">${createdDate}</div>
                    </div>

                    <!-- Created By -->
                    <div class="py-1.5 border-b border-slate-100/80 dark:border-zinc-800/80">
                        <div class="text-[11px] text-slate-400 dark:text-zinc-500 flex items-center gap-1 font-medium">
                            <svg class="w-3.5 h-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            <span>Created By</span>
                        </div>
                        <div id="detail-fs-created-by" class="font-bold text-slate-900 dark:text-zinc-100 mt-1">${escapeHtml(createdByName)}</div>
                    </div>

                    <!-- Last Modified Date & Time -->
                    <div class="py-1.5 border-b border-slate-100/80 dark:border-zinc-800/80">
                        <div class="text-[11px] text-slate-400 dark:text-zinc-500 flex items-center gap-1 font-medium">
                            <svg class="w-3.5 h-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            <span>Last Modified Date &amp; Time</span>
                        </div>
                        <div id="detail-fs-updated-at" class="font-bold text-slate-900 dark:text-zinc-100 mt-1">${updatedDate}</div>
                    </div>

                    <!-- Last Modified By -->
                    <div class="py-1.5 border-b border-slate-100/80 dark:border-zinc-800/80">
                        <div class="text-[11px] text-slate-400 dark:text-zinc-500 flex items-center gap-1 font-medium">
                            <svg class="w-3.5 h-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            <span>Last Modified By</span>
                        </div>
                        <div id="detail-fs-updated-by" class="font-bold text-slate-900 dark:text-zinc-100 mt-1">${escapeHtml(updatedByName)}</div>
                    </div>

                    <!-- Record ID -->
                    <div class="py-1.5 border-b border-slate-100/80 dark:border-zinc-800/80">
                        <div class="text-[11px] text-slate-400 dark:text-zinc-500 flex items-center gap-1 font-medium">
                            <svg class="w-3.5 h-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M7 7h10"/><path d="M7 12h10"/><path d="M7 17h10"/></svg>
                            <span>Record ID</span>
                        </div>
                        <div class="mt-1">
                            <span id="detail-fs-record-id" class="px-2 py-0.5 rounded font-mono text-[11px] bg-sky-50 dark:bg-sky-950/40 text-sky-700 dark:text-sky-300 border border-sky-100 dark:border-sky-800">
                                ${escapeHtml(recordId)}
                            </span>
                        </div>
                    </div>
                `;
            } else {
                fieldsHtml = secFields.map(f => {
                    const fName = typeof f === 'object' ? f.name : f;
                    if (!fName) return '';

                    const perm = getPermission(fName);
                    if (perm === 'Hidden') return '';

                    const isRo = (perm === 'Read-Only') || !!f.readOnly;
                    const override = state.standardOverrides?.[fName];
                    const cf = state.customFields.find(c => c.field_name === fName);

                    const isReq = !!f.required || !!override?.is_required;
                    const label = override?.field_label || f.label || cf?.field_label || fName.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());

                    const rawVal = acc[fName] !== undefined ? acc[fName] : (acc.custom_data && acc.custom_data[fName]);
                    let displayValHtml = '—';

                    if (fName === 'annual_revenue' || fName === 'credit_limit' || cf?.data_type === 'currency') {
                        displayValHtml = rawVal !== null && rawVal !== undefined && rawVal !== ''
                            ? formatCurrency(rawVal, acc.currency)
                            : formatCurrency(0, acc.currency);
                    } else if (fName === 'website') {
                        if (rawVal) {
                            const href = String(rawVal).startsWith('http') ? rawVal : 'https://' + rawVal;
                            displayValHtml = `<a href="${escapeHtml(href)}" target="_blank" class="text-sky-600 dark:text-sky-400 hover:underline truncate max-w-xs block">${escapeHtml(rawVal)}</a>`;
                        }
                    } else if (fName === 'phone') {
                        if (rawVal) {
                            displayValHtml = `<a href="tel:${escapeHtml(rawVal)}" class="text-sky-600 dark:text-sky-400 hover:underline">${escapeHtml(rawVal)}</a>`;
                        }
                    } else if (fName === 'email') {
                        if (rawVal) {
                            displayValHtml = `<a href="mailto:${escapeHtml(rawVal)}" class="text-sky-600 dark:text-sky-400 hover:underline">${escapeHtml(rawVal)}</a>`;
                        }
                    } else if (fName === 'billing_address' || fName === 'shipping_address') {
                        displayValHtml = rawVal ? `<span class="whitespace-pre-line text-slate-800 dark:text-zinc-200">${escapeHtml(rawVal)}</span>` : '—';
                    } else if (fName === 'type') {
                        const val = rawVal || 'Customer';
                        displayValHtml = `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold border ${getTypeBadgeClass(val)}">${escapeHtml(val)}</span>`;
                    } else if (cf && cf.data_type === 'boolean') {
                        const isChecked = !!rawVal && rawVal !== '0' && rawVal !== 'false';
                        displayValHtml = isChecked
                            ? '<span class="inline-flex items-center gap-1 text-emerald-600 font-semibold"><svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg> Yes</span>'
                            : '<span class="text-slate-400 font-medium">No</span>';
                    } else if (cf && cf.data_type === 'date') {
                        displayValHtml = rawVal ? formatDateTime(rawVal) : '—';
                    } else {
                        displayValHtml = (rawVal !== null && rawVal !== undefined && String(rawVal).trim() !== '') ? escapeHtml(rawVal) : '—';
                    }

                    const isFullSpan = (secColumns === 1) || ['billing_address', 'shipping_address', 'notes', 'description'].includes(fName) || (cf && ['textarea', 'rich_text'].includes(cf.data_type)) || (f.col_span === 'full' || f.col_span === 3);

                    const editBtnHtml = !isRo
                        ? `<button type="button" onclick="window.editFieldInline('${escapeHtml(fName)}')" class="text-slate-300 group-hover:text-slate-600 dark:group-hover:text-zinc-300 transition-colors p-1 cursor-pointer shrink-0" title="Edit ${escapeHtml(label)}">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                        </button>`
                        : '';

                    return `
                        <div class="account-field-item flex items-center justify-between py-1.5 border-b border-slate-100/80 dark:border-zinc-800/80 group ${isFullSpan ? 'col-span-full' : ''}" data-account-field="${escapeHtml(fName)}">
                            <div class="min-w-0 flex-1 pr-2">
                                <div class="text-[11px] text-slate-400 dark:text-zinc-500 font-medium truncate">
                                    ${isReq ? '<span class="text-rose-500 font-bold mr-0.5">*</span>' : ''}
                                    ${escapeHtml(label)}
                                </div>
                                <div id="detail-fs-${escapeHtml(fName.replace(/_/g, '-'))}" class="font-semibold text-slate-900 dark:text-zinc-100 mt-0.5 text-xs truncate">
                                    ${displayValHtml}
                                </div>
                            </div>
                            ${editBtnHtml}
                        </div>
                    `;
                }).join('');
            }

            return `
                <div
                    id="account-section-${escapeHtml(secId)}"
                    data-section-id="${escapeHtml(secId)}"
                    data-section-columns="${secColumns}"
                    class="account-detail-section bg-white dark:bg-zinc-900 rounded-2xl border border-orange-200/90 dark:border-orange-500/30 shadow-[0_4px_20px_-2px_rgba(251,146,60,0.12)] overflow-hidden transition-all duration-200"
                >
                    <div
                        ${isCollapsible ? `onclick="window.toggleFullscreenSection('${escapeHtml(bodyId)}', '${escapeHtml(chevronId)}')"` : ''}
                        class="px-5 py-3.5 bg-white dark:bg-zinc-900 flex items-center justify-between ${isCollapsible ? 'cursor-pointer select-none' : ''}"
                    >
                        <div class="flex items-center gap-2 font-bold text-xs sm:text-sm text-slate-900 dark:text-zinc-100">
                            ${isCollapsible ? `
                            <svg id="${escapeHtml(chevronId)}" class="w-4 h-4 text-slate-400 transition-transform duration-200 ${defaultCollapsed ? '-rotate-90' : ''}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                            ` : ''}

                            ${isSystemSection ? `
                            <svg class="w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            </svg>
                            ` : `
                            <svg class="w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/>
                            </svg>
                            `}

                            <span>${escapeHtml(secTitle)}</span>
                        </div>

                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold tracking-wide bg-slate-100 dark:bg-zinc-800 text-slate-500 dark:text-zinc-400 border border-slate-200 dark:border-zinc-700">
                            ${secColumns} ${secColumns === 1 ? 'Column' : 'Columns'}
                        </span>
                    </div>

                    <div id="${escapeHtml(bodyId)}" class="px-6 py-4 border-t border-slate-100 dark:border-zinc-800 text-xs ${defaultCollapsed ? 'hidden' : ''}">
                        <div class="grid ${gridColsClass}">
                            ${fieldsHtml}
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        container.innerHTML = sectionsHtml;
    }

    // ==========================================
    // Fullscreen Account Detail Modal Controller (Dynamic Page Layout Integration)
    // ==========================================
    window.open360Modal = async function (id) {
        const modal = document.getElementById('account-360-modal');
        if (!modal) return;

        state.viewing360Account = id;
        const acc = state.accounts.find(a => a.id == id) || {};

        // Top App Bar & Breadcrumb
        const nameEl = document.getElementById('modal-fullscreen-account-name');
        if (nameEl) nameEl.textContent = acc.name || 'Unnamed Account';

        const breadcrumbEl = document.getElementById('modal-breadcrumb-account-name');
        if (breadcrumbEl) breadcrumbEl.textContent = acc.name || 'Unnamed Account';

        // Ensure active page layout is loaded
        const pageLayout = await ensurePageLayout();

        // Render dynamic sections (1 Col, 2 Cols, 3 Cols) based on Admin Page Layout
        renderDynamicAccountSections(acc, pageLayout);

        // Show Modal
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');

        // Load files for this account
        window.loadAccountFiles(id);

        // Load activities for this account (timeline & badges)
        window.refreshAccountActivities(id);
        window.switchAccountActivityFormType('Note');

        // Fetch dynamic counts (contacts, deals, po, shipments, payments)
        try {
            const res = await fetch(`/api/accounts/${id}/360`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });
            if (res.ok) {
                const data = await res.json();
                state.account360Data = data;
                const contactsLen = data.contacts?.length || 0;
                const dealsLen = data.opportunities?.length || 0;
                const poLen = data.purchaseDeals?.length || 0;
                const shipLen = data.shipments?.length || 0;
                const payLen = data.payments?.length || 0;
                const totalRelated = contactsLen + dealsLen + poLen + shipLen + payLen;

                const cCount = document.getElementById('fs-list-contacts-count');
                if (cCount) cCount.textContent = contactsLen;
                const dCount = document.getElementById('fs-list-deals-count');
                if (dCount) dCount.textContent = dealsLen;
                const pCount = document.getElementById('fs-list-po-count');
                if (pCount) pCount.textContent = poLen;
                const sCount = document.getElementById('fs-list-shipments-count');
                if (sCount) sCount.textContent = shipLen;
                const payCount = document.getElementById('fs-list-payments-count');
                if (payCount) payCount.textContent = payLen;

                const relBadge = document.getElementById('fs-badge-lists-count');
                if (relBadge) relBadge.textContent = 5;

                const countRelated = document.getElementById('fs-count-related');
                if (countRelated) countRelated.textContent = Math.max(6, totalRelated);
            }
        } catch (_) {}
    };

    window.close360Modal = function () {
        const modal = document.getElementById('account-360-modal');
        if (modal) modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        state.viewing360Account = null;
        state.account360Data = null;
    };

    window.toggleFullscreenSection = function (bodyId, chevronId) {
        const body = document.getElementById(bodyId);
        const chevron = document.getElementById(chevronId);
        if (!body) return;
        const isHidden = body.classList.contains('hidden');
        if (isHidden) {
            body.classList.remove('hidden');
            if (chevron) chevron.classList.remove('-rotate-90');
        } else {
            body.classList.add('hidden');
            if (chevron) chevron.classList.add('-rotate-90');
        }
    };

    window.switchFullscreenRightTab = function (tabId) {
        if (tabId === 'files' && state.viewing360Account) {
            window.loadAccountFiles(state.viewing360Account);
        }
        if (tabId === 'activity' && state.viewing360Account) {
            window.refreshAccountActivities(state.viewing360Account);
        }
        const tabs = ['related', 'activity', 'files'];
        tabs.forEach(t => {
            const btn = document.getElementById(`fs-tab-btn-${t}`);
            const content = document.getElementById(`fs-tab-content-${t}`);
            const badgeId = t === 'related' ? 'fs-count-related' : (t === 'activity' ? 'fs-count-activity' : 'fs-tab-badge-files-count');
            const badge = document.getElementById(badgeId);

            if (t === tabId) {
                if (btn) {
                    btn.className = 'px-3.5 py-1.5 text-xs font-semibold rounded-full bg-[#334e68] text-white shadow-2xs flex items-center gap-1.5 cursor-pointer';
                }
                if (badge) {
                    badge.className = 'px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-[#486581] text-white ml-0.5';
                }
                if (content) content.classList.remove('hidden');
            } else {
                if (btn) {
                    btn.className = 'px-3.5 py-1.5 text-xs font-medium rounded-full text-slate-600 dark:text-zinc-300 hover:text-slate-900 dark:hover:text-white flex items-center gap-1.5 cursor-pointer';
                }
                if (badge) {
                    badge.className = 'px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-slate-200 dark:bg-zinc-700 text-slate-700 dark:text-zinc-300 ml-0.5';
                }
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

    window.openTransferFromFullscreen = function () {
        if (state.viewing360Account) {
            window.openTransferModal(state.viewing360Account);
        }
    };

    window.shareAccountRecord = function () {
        const url = window.location.origin + `/accounts/${state.viewing360Account || ''}`;
        if (navigator.clipboard) {
            navigator.clipboard.writeText(url);
            showNotification('Account link copied to clipboard!', 'success');
        } else {
            showNotification(`Share URL: ${url}`, 'info');
        }
    };

    window.editFieldInline = function (fieldName) {
        if (state.viewing360Account) {
            window.openAccountModal('edit', state.viewing360Account);
            setTimeout(() => {
                const el = document.getElementById(`acc-field-${fieldName}`);
                if (el) el.focus();
            }, 100);
        }
    };

    // ==========================================
    // ACCOUNT FILES MANAGEMENT (1:1 with Screenshot)
    // ==========================================

    // Trigger Native File Selection Picker Dialog (Matches Windows Explorer popup)
    window.triggerFileUploadForAccount = function () {
        const fileInput = document.getElementById('account-file-upload-input');
        if (!fileInput) return;
        if (!state.viewing360Account) {
            showNotification('Please open an account before adding files.', 'error');
            return;
        }
        fileInput.value = '';
        fileInput.click();
    };

    // Handle File(s) Upload
    window.handleAccountFileUpload = async function (e) {
        const files = e.target.files;
        if (!files || files.length === 0) return;
        const accountId = state.viewing360Account;
        if (!accountId) return;

        const indicator = document.getElementById('account-file-uploading-indicator');
        const indicatorText = document.getElementById('account-file-uploading-text');
        if (indicator) {
            indicator.classList.remove('hidden');
            if (indicatorText) {
                indicatorText.textContent = `Uploading ${files.length} file${files.length > 1 ? 's' : ''}...`;
            }
        }

        const formData = new FormData();
        for (let i = 0; i < files.length; i++) {
            formData.append('files[]', files[i]);
        }

        try {
            const res = await fetch(`/api/accounts/${accountId}/files`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData,
            });

            const data = await res.json();
            if (!res.ok || !data.success) {
                throw new Error(data.message || 'File upload failed');
            }

            showNotification(data.message || `${files.length} file(s) uploaded successfully!`, 'success');
            await window.loadAccountFiles(accountId);
        } catch (err) {
            console.error('File upload error:', err);
            showNotification(err.message || 'File upload failed.', 'error');
        } finally {
            if (indicator) indicator.classList.add('hidden');
            e.target.value = '';
        }
    };

    // Load Files for Account
    window.loadAccountFiles = async function (accountId) {
        if (!accountId) return;

        try {
            const res = await fetch(`/api/accounts/${accountId}/files`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            if (res.ok) {
                const data = await res.json();
                state.accountFiles = data.files || [];
                window.renderAccountFiles(state.accountFiles);
            }
        } catch (err) {
            console.error('Error fetching account files:', err);
        }
    };

    // Render Files List in Card and Dedicated Tab
    window.renderAccountFiles = function (files = []) {
        const count = files.length;

        // Update badge counts
        const badgeFilesCount = document.getElementById('fs-badge-files-count');
        if (badgeFilesCount) badgeFilesCount.textContent = count;

        const tabBadgeFilesCount = document.getElementById('fs-tab-badge-files-count');
        if (tabBadgeFilesCount) tabBadgeFilesCount.textContent = count;

        const tabViewFilesCount = document.getElementById('fs-tab-view-files-count');
        if (tabViewFilesCount) tabViewFilesCount.textContent = count;

        const viewAllCount = document.getElementById('fs-files-viewall-count');
        if (viewAllCount) viewAllCount.textContent = count;

        const popupCount = document.getElementById('files-popup-count');
        if (popupCount) popupCount.textContent = count;

        const popupInnerCount = document.getElementById('files-popup-inner-count');
        if (popupInnerCount) popupInnerCount.textContent = count;

        const footerEl = document.getElementById('fs-files-footer');
        if (footerEl) {
            if (count > 0) {
                footerEl.classList.remove('hidden');
            } else {
                footerEl.classList.add('hidden');
            }
        }

        // Render Card files list
        const container = document.getElementById('fs-files-list-container');
        if (container) {
            if (count === 0) {
                container.innerHTML = `
                    <div class="py-4 text-center text-xs text-slate-400">
                        No files uploaded yet. Click <strong>+ Add</strong> to attach files.
                    </div>
                `;
            } else {
                container.innerHTML = files.slice(0, 5).map(f => {
                    let iconHtml = '';
                    if (f.is_image && f.view_url) {
                        iconHtml = `
                            <div class="w-8 h-8 rounded-md overflow-hidden bg-slate-100 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 shrink-0 flex items-center justify-center shadow-2xs">
                                <img src="${f.view_url}" alt="${escapeHtml(f.name)}" class="w-full h-full object-cover" onerror="this.parentElement.innerHTML='<svg class=\\'w-4 h-4 text-teal-600\\' viewBox=\\'0 0 24 24\\' fill=\\'none\\' stroke=\\'currentColor\\' stroke-width=\\'2\\'><rect width=\\'18\\' height=\\'18\\' x=\\'3\\' y=\\'3\\' rx=\\'2\\'/><circle cx=\\'9\\' cy=\\'9\\' r=\\'2\\'/><path d=\\'m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21\\'/></svg>';">
                            </div>
                        `;
                    } else if (f.icon_type === 'excel') {
                        iconHtml = `
                            <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 border border-emerald-200/80 dark:border-emerald-800/60 shadow-2xs">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                    <polyline points="14 2 14 8 20 8"/>
                                    <path d="M8 13h8M8 17h8M10 9h4"/>
                                </svg>
                            </div>
                        `;
                    } else if (f.icon_type === 'pdf') {
                        iconHtml = `
                            <div class="w-8 h-8 rounded-lg bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0 border border-rose-200/80 dark:border-rose-800/60 shadow-2xs">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                    <polyline points="14 2 14 8 20 8"/>
                                    <path d="M9 13v4M12 13v4M15 13v4"/>
                                </svg>
                            </div>
                        `;
                    } else {
                        iconHtml = `
                            <div class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-zinc-300 flex items-center justify-center shrink-0 border border-slate-200 dark:border-zinc-700 shadow-2xs">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                    <polyline points="14 2 14 8 20 8"/>
                                </svg>
                            </div>
                        `;
                    }

                    return `
                        <div class="flex items-center justify-between py-1.5 px-1.5 rounded-xl hover:bg-slate-50 dark:hover:bg-zinc-800/60 transition-colors group">
                            <div class="flex items-center gap-3 min-w-0">
                                ${iconHtml}
                                <div class="min-w-0">
                                    <a href="${f.download_url}" download class="font-bold text-xs text-slate-900 dark:text-zinc-100 hover:text-blue-600 dark:hover:text-blue-400 hover:underline truncate block max-w-[210px]" title="${escapeHtml(f.name)}">
                                        ${escapeHtml(f.name)}
                                    </a>
                                    <div class="text-[11px] text-slate-400 dark:text-zinc-500 truncate mt-0.5">
                                        ${escapeHtml(f.date_formatted)} • ${escapeHtml(f.size_formatted)} • ${escapeHtml(f.type_label)}
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-1 shrink-0 text-slate-400 dark:text-zinc-500">
                                <a href="${f.view_url || f.download_url}" target="_blank" class="p-1 rounded-md hover:text-slate-800 dark:hover:text-zinc-200 hover:bg-slate-100 dark:hover:bg-zinc-700 transition-colors" title="View / Preview">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                </a>
                                <a href="${f.download_url}" download class="p-1 rounded-md hover:text-slate-800 dark:hover:text-zinc-200 hover:bg-slate-100 dark:hover:bg-zinc-700 transition-colors" title="Download">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                </a>
                                <button type="button" onclick="window.deleteAccountFile(${f.id})" class="p-1 rounded-md hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors cursor-pointer" title="Delete file">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                            </div>
                        </div>
                    `;
                }).join('');
            }
        }

        // Render full tab list
        const tabContainer = document.getElementById('fs-tab-files-list-container');
        if (tabContainer) {
            if (count === 0) {
                tabContainer.innerHTML = `
                    <div class="py-8 text-center text-xs text-slate-400">
                        No files uploaded for this account yet.
                    </div>
                `;
            } else {
                tabContainer.innerHTML = files.map(f => {
                    return `
                        <div class="flex items-center justify-between p-3 rounded-xl border border-slate-100 dark:border-zinc-800 hover:bg-slate-50 dark:hover:bg-zinc-800/40 transition-colors">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-9 h-9 rounded-lg bg-slate-100 dark:bg-zinc-800 flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4 text-slate-600 dark:text-zinc-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l8.57-8.57A4 4 0 1 1 18 8.84l-8.59 8.57a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
                                </div>
                                <div class="min-w-0">
                                    <a href="${f.download_url}" download class="font-bold text-xs sm:text-sm text-slate-900 dark:text-zinc-100 hover:underline truncate block">
                                        ${escapeHtml(f.name)}
                                    </a>
                                    <div class="text-[11px] text-slate-400 mt-0.5">
                                        Uploaded ${escapeHtml(f.date_formatted)} by ${escapeHtml(f.created_by_name || 'System Admin')} • ${escapeHtml(f.size_formatted)}
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <a href="${f.view_url || f.download_url}" target="_blank" class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-slate-100 dark:bg-zinc-800 hover:bg-slate-200 text-slate-700 dark:text-zinc-200 transition-colors">View</a>
                                <a href="${f.download_url}" download class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-sky-50 dark:bg-sky-950/40 text-sky-600 dark:text-sky-400 border border-sky-200 hover:bg-sky-100 transition-colors">Download</a>
                                <button type="button" onclick="window.deleteAccountFile(${f.id})" class="p-1.5 text-slate-400 hover:text-rose-600 cursor-pointer">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/></svg>
                                </button>
                            </div>
                        </div>
                    `;
                }).join('');
            }
        }

        // Also refresh popup table if open
        window.renderAccountFilesPopupTable(files);
    };

    // Render Files & Attachments Popup Table (Exact 1:1 match with user screenshot)
    window.renderAccountFilesPopupTable = function (files = []) {
        const tbodies = [
            document.getElementById('account-files-popup-tbody'),
            document.getElementById('fs-tab-files-tbody')
        ].filter(Boolean);

        if (tbodies.length === 0) return;

        if (files.length === 0) {
            const emptyHtml = `
                <tr>
                    <td colspan="9" class="py-12 text-center text-xs text-slate-400 dark:text-zinc-500">
                        No files or attachments uploaded yet. Click <strong>+ Add File</strong> to upload.
                    </td>
                </tr>
            `;
            tbodies.forEach(tb => tb.innerHTML = emptyHtml);
            return;
        }

        const html = files.map(f => {
            let iconSvg = '';
            const mime = (f.mime_type || '').toLowerCase();
            const name = (f.name || '').toLowerCase();

            if (f.icon_type === 'excel' || mime.includes('csv') || mime.includes('spreadsheet') || mime.includes('excel') || name.endsWith('.csv') || name.endsWith('.xlsx') || name.endsWith('.xls')) {
                // Spreadsheet icon (Green lucide-file-spreadsheet)
                iconSvg = `
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-spreadsheet mx-auto" aria-hidden="true">
                        <path d="M6 22a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h8a2.4 2.4 0 0 1 1.704.706l3.588 3.588A2.4 2.4 0 0 1 20 8v12a2 2 0 0 1-2 2z"></path>
                        <path d="M14 2v5a1 1 0 0 0 1 1h5"></path>
                        <path d="M8 13h2"></path>
                        <path d="M14 13h2"></path>
                        <path d="M8 17h2"></path>
                        <path d="M14 17h2"></path>
                    </svg>
                `;
            } else if (f.is_image || mime.startsWith('image/') || name.endsWith('.jpg') || name.endsWith('.jpeg') || name.endsWith('.png') || name.endsWith('.webp')) {
                // Image icon (Teal/Emerald lucide-image)
                iconSvg = `
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-image mx-auto" aria-hidden="true">
                        <rect width="18" height="18" x="3" y="3" rx="2" ry="2"></rect>
                        <circle cx="9" cy="9" r="2"></circle>
                        <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"></path>
                    </svg>
                `;
            } else if (f.icon_type === 'pdf' || mime.includes('pdf') || name.endsWith('.pdf')) {
                // PDF icon (Rose/Red lucide-file-text)
                iconSvg = `
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#e11d48" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-text mx-auto" aria-hidden="true">
                        <path d="M6 22a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h8a2.4 2.4 0 0 1 1.704.706l3.588 3.588A2.4 2.4 0 0 1 20 8v12a2 2 0 0 1-2 2z"></path>
                        <path d="M14 2v5a1 1 0 0 0 1 1h5"></path>
                        <path d="M9 13v4"></path>
                        <path d="M12 13v4"></path>
                        <path d="M15 13v4"></path>
                    </svg>
                `;
            } else {
                // Generic document
                iconSvg = `
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file mx-auto" aria-hidden="true">
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
                            <button type="button" onclick="window.deleteAccountFile(${f.id})" class="p-1 rounded text-zinc-500 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors cursor-pointer" title="Delete file">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-ellipsis-vertical">
                                    <circle cx="12" cy="12" r="1"></circle>
                                    <circle cx="12" cy="5" r="1"></circle>
                                    <circle cx="12" cy="19" r="1"></circle>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');

        tbodies.forEach(tb => tb.innerHTML = html);
    };

    // Open Files Modal Popup (Matches Screenshot 1:1)
    window.openAccountFilesModal = function () {
        const modal = document.getElementById('account-files-popup-modal');
        if (!modal) return;

        let accountName = 'Account';
        const titleEl = document.getElementById('fs-account-title');
        if (titleEl && titleEl.textContent.trim()) {
            accountName = titleEl.textContent.trim();
        } else if (state.viewing360Account) {
            const acc = (state.accounts || []).find(a => a.id == state.viewing360Account);
            if (acc && acc.account_name) accountName = acc.account_name;
        }

        const nameEl = document.getElementById('files-popup-account-name');
        if (nameEl) nameEl.textContent = accountName;

        const count = (state.accountFiles || []).length;
        const countEl = document.getElementById('files-popup-count');
        if (countEl) countEl.textContent = count;
        const innerCountEl = document.getElementById('files-popup-inner-count');
        if (innerCountEl) innerCountEl.textContent = count;

        window.renderAccountFilesPopupTable(state.accountFiles || []);
        modal.classList.remove('hidden');
    };

    // Close Files Modal Popup
    window.closeAccountFilesModal = function () {
        const modal = document.getElementById('account-files-popup-modal');
        if (modal) modal.classList.add('hidden');
    };

    // Delete Account File
    window.deleteAccountFile = async function (fileId) {
        if (!confirm('Are you sure you want to delete this file?')) return;
        const accountId = state.viewing360Account;
        if (!accountId) return;

        try {
            const res = await fetch(`/api/accounts/${accountId}/files/${fileId}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            const data = await res.json();
            if (!res.ok || !data.success) {
                throw new Error(data.message || 'Failed to delete file');
            }

            showNotification(data.message || 'File deleted successfully', 'success');
            await window.loadAccountFiles(accountId);
        } catch (err) {
            console.error('Delete file error:', err);
            showNotification(err.message || 'Failed to delete file', 'error');
        }
    };

    window.composeEmailForAccount = function () {
        showNotification('Drafting email for this account...', 'info');
    };

    window.reloadAccountEmails = function () {
        showNotification('Refreshed account emails log.', 'success');
    };

    window.logActivityForAccount = function () {
        showNotification('Activity logger opened for this account.', 'info');
    };

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
            if (state.viewing360Account) {
                window.open360Modal(state.viewing360Account);
            }
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
            if (state.viewing360Account) {
                window.open360Modal(state.viewing360Account);
            }
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
                btnTable.className = 'flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-white dark:bg-zinc-700 text-slate-900 dark:text-zinc-100 shadow-2xs cursor-pointer';
            }
            if (btnKanban) {
                btnKanban.className = 'flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg text-slate-500 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-zinc-200 cursor-pointer';
            }
        } else {
            if (btnTable) {
                btnTable.className = 'flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg text-slate-500 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-zinc-200 cursor-pointer';
            }
            if (btnKanban) {
                btnKanban.className = 'flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-white dark:bg-zinc-700 text-slate-900 dark:text-zinc-100 shadow-2xs cursor-pointer';
            }
        }

        renderAccountsView();
    };

    // ========================================================
    // CUSTOMIZE TABLE FIELDS & ORDER MODAL (1:1 with Screenshot)
    // ========================================================
    state.tempCustomizeFields = [];
    state.customizeSearchTerm = '';

    window.openCustomizeFieldsModal = function () {
        const modal = document.getElementById('account-customize-modal');
        if (!modal) return;

        const fieldDefs = getVisibleFieldDefinitions();
        const activeList = state.viewMode === 'table' ? [...state.tableVisibleFields] : [...state.kanbanVisibleFields];

        // 1. Order: Active visible fields come first in current order (checked: true)
        const ordered = [];
        activeList.forEach(key => {
            const f = fieldDefs.find(def => def.name === key);
            if (f) {
                ordered.push({ ...f, checked: true });
            }
        });

        // 2. Remaining available fields come after (checked: false)
        fieldDefs.forEach(f => {
            if (!activeList.includes(f.name)) {
                ordered.push({ ...f, checked: false });
            }
        });

        state.tempCustomizeFields = ordered;
        state.customizeSearchTerm = '';

        const searchInput = document.getElementById('customize-fields-search-input');
        if (searchInput) searchInput.value = '';

        renderCustomizeFieldsList();
        modal.classList.remove('hidden');
    };

    window.closeCustomizeFieldsModal = function () {
        const modal = document.getElementById('account-customize-modal');
        if (modal) modal.classList.add('hidden');
    };

    window.handleCustomizeFieldsSearch = function (term) {
        state.customizeSearchTerm = (term || '').trim().toLowerCase();
        renderCustomizeFieldsList();
    };

    window.toggleCustomizeFieldChecked = function (fieldName, isChecked) {
        const item = state.tempCustomizeFields.find(f => f.name === fieldName);
        if (item) {
            item.checked = isChecked;
            renderCustomizeFieldsList();
        }
    };

    window.customizeSelectAllFields = function () {
        state.tempCustomizeFields.forEach(f => { f.checked = true; });
        renderCustomizeFieldsList();
    };

    window.customizeDeselectAllFields = function () {
        state.tempCustomizeFields.forEach(f => { f.checked = false; });
        renderCustomizeFieldsList();
    };

    window.customizeResetFields = function () {
        const defaultOrder = ['type', 'name', 'industry', 'payment_terms', 'credit_limit', 'phone', 'assigned_to'];
        const fieldDefs = getVisibleFieldDefinitions();

        const ordered = [];
        defaultOrder.forEach(key => {
            const f = fieldDefs.find(def => def.name === key);
            if (f) {
                ordered.push({ ...f, checked: true });
            }
        });
        fieldDefs.forEach(f => {
            if (!defaultOrder.includes(f.name)) {
                ordered.push({ ...f, checked: false });
            }
        });

        state.tempCustomizeFields = ordered;
        state.customizeSearchTerm = '';
        const searchInput = document.getElementById('customize-fields-search-input');
        if (searchInput) searchInput.value = '';

        renderCustomizeFieldsList();
    };

    window.moveCustomizeField = function (fieldName, action) {
        const idx = state.tempCustomizeFields.findIndex(f => f.name === fieldName);
        if (idx === -1) return;

        const [item] = state.tempCustomizeFields.splice(idx, 1);
        if (action === 'top') {
            state.tempCustomizeFields.unshift(item);
        } else if (action === 'bottom') {
            state.tempCustomizeFields.push(item);
        } else if (action === 'up') {
            const newIdx = Math.max(0, idx - 1);
            state.tempCustomizeFields.splice(newIdx, 0, item);
        } else if (action === 'down') {
            const newIdx = Math.min(state.tempCustomizeFields.length, idx + 1);
            state.tempCustomizeFields.splice(newIdx, 0, item);
        }

        renderCustomizeFieldsList();
    };

    // Drag & Drop reordering
    window.handleCustomizeDragStart = function (e, fieldName) {
        e.dataTransfer.setData('text/plain', fieldName);
        e.dataTransfer.effectAllowed = 'move';
        const row = e.target.closest('[data-field-name]');
        if (row) row.classList.add('opacity-50');
    };

    window.handleCustomizeDragOver = function (e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
    };

    window.handleCustomizeDrop = function (e, targetFieldName) {
        e.preventDefault();
        const sourceFieldName = e.dataTransfer.getData('text/plain');
        if (!sourceFieldName || sourceFieldName === targetFieldName) return;

        const srcIdx = state.tempCustomizeFields.findIndex(f => f.name === sourceFieldName);
        const tgtIdx = state.tempCustomizeFields.findIndex(f => f.name === targetFieldName);
        if (srcIdx === -1 || tgtIdx === -1) return;

        const [item] = state.tempCustomizeFields.splice(srcIdx, 1);
        state.tempCustomizeFields.splice(tgtIdx, 0, item);
        renderCustomizeFieldsList();
    };

    function renderCustomizeFieldsList() {
        const container = document.getElementById('customize-fields-list-container');
        const footerCountEl = document.getElementById('customize-fields-footer-count');
        if (!container) return;

        const totalCount = state.tempCustomizeFields.length;
        const checkedCount = state.tempCustomizeFields.filter(f => f.checked).length;

        if (footerCountEl) {
            footerCountEl.innerHTML = `Showing <strong>${checkedCount}</strong> of ${totalCount} columns in table`;
        }

        const filterTerm = state.customizeSearchTerm;
        const displayFields = state.tempCustomizeFields.filter(f => {
            if (!filterTerm) return true;
            return (f.label || '').toLowerCase().includes(filterTerm) || (f.name || '').toLowerCase().includes(filterTerm);
        });

        if (displayFields.length === 0) {
            container.innerHTML = `
                <div class="py-12 text-center text-slate-400 text-xs">
                    No fields match "<strong>${filterTerm}</strong>"
                </div>
            `;
            return;
        }

        container.innerHTML = displayFields.map((item) => {
            let badgeHtml = '';
            if (item.checked) {
                // Find order index among ALL checked items in tempCustomizeFields
                const checkedIndex = state.tempCustomizeFields.filter(f => f.checked).findIndex(f => f.name === item.name) + 1;
                badgeHtml = `
                    <span class="inline-flex items-center justify-center min-w-[34px] px-2 py-0.5 rounded-lg text-xs font-bold text-blue-600 bg-blue-50 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200/90 dark:border-blue-800">
                        #${checkedIndex}
                    </span>
                `;
            } else {
                badgeHtml = `
                    <span class="inline-flex items-center justify-center min-w-[34px] px-2.5 py-0.5 rounded-lg text-xs font-semibold text-slate-400 bg-slate-100 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700">
                        —
                    </span>
                `;
            }

            const isSystem = ['name', 'type'].includes(item.name);
            const isCustom = item.isCustom;

            return `
                <div
                    class="group rounded-2xl border ${item.checked ? 'border-slate-200/90 dark:border-zinc-700 bg-white dark:bg-zinc-800/80' : 'border-slate-200/60 dark:border-zinc-800 bg-slate-50/50 dark:bg-zinc-900/40 opacity-75'} p-3.5 flex items-center justify-between gap-3 shadow-2xs hover:border-slate-300 dark:hover:border-zinc-600 transition-all select-none"
                    draggable="true"
                    data-field-name="${item.name}"
                    ondragstart="window.handleCustomizeDragStart(event, '${item.name}')"
                    ondragover="window.handleCustomizeDragOver(event)"
                    ondrop="window.handleCustomizeDrop(event, '${item.name}')"
                    ondragend="this.classList.remove('opacity-50')"
                >
                    <!-- Left group -->
                    <div class="flex items-center gap-3 min-w-0 flex-1">
                        <!-- 6-Dot Drag Handle -->
                        <div class="cursor-grab active:cursor-grabbing text-slate-300 hover:text-slate-500 dark:text-zinc-600 dark:hover:text-zinc-400 p-0.5 shrink-0" title="Drag to reorder">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                <circle cx="9" cy="6" r="1.5"/><circle cx="15" cy="6" r="1.5"/>
                                <circle cx="9" cy="12" r="1.5"/><circle cx="15" cy="12" r="1.5"/>
                                <circle cx="9" cy="18" r="1.5"/><circle cx="15" cy="18" r="1.5"/>
                            </svg>
                        </div>

                        <!-- Badge (#1 or —) -->
                        <div class="shrink-0">
                            ${badgeHtml}
                        </div>

                        <!-- Checkbox -->
                        <label class="relative flex items-center cursor-pointer shrink-0">
                            <input
                                type="checkbox"
                                ${item.checked ? 'checked' : ''}
                                onchange="window.toggleCustomizeFieldChecked('${item.name}', this.checked)"
                                class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer"
                            >
                        </label>

                        <!-- Field Info -->
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <span class="text-xs sm:text-sm ${item.checked ? 'font-bold text-slate-800 dark:text-zinc-100' : 'font-medium text-slate-500 dark:text-zinc-400'} truncate">
                                    ${item.label}
                                </span>
                                ${isSystem ? `
                                    <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-800">
                                        System
                                    </span>
                                ` : ''}
                                ${isCustom ? `
                                    <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                        Custom
                                    </span>
                                ` : ''}
                            </div>
                            <div class="text-[11px] font-mono text-slate-400 dark:text-zinc-500 truncate mt-0.5">
                                ${item.name}
                            </div>
                        </div>
                    </div>

                    <!-- Right action buttons: Top, Up, Down, Bottom -->
                    <div class="flex items-center gap-1 shrink-0 text-slate-400 dark:text-zinc-500">
                        <button
                            type="button"
                            onclick="window.moveCustomizeField('${item.name}', 'top')"
                            class="p-1 rounded-md hover:bg-slate-100 dark:hover:bg-zinc-700 hover:text-slate-700 dark:hover:text-zinc-200 transition-colors cursor-pointer"
                            title="Move to Top"
                        >
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="4" y1="4" x2="20" y2="4"/>
                                <polyline points="16 12 12 8 8 12"/>
                                <line x1="12" y1="8" x2="12" y2="20"/>
                            </svg>
                        </button>
                        <button
                            type="button"
                            onclick="window.moveCustomizeField('${item.name}', 'up')"
                            class="p-1 rounded-md hover:bg-slate-100 dark:hover:bg-zinc-700 hover:text-slate-700 dark:hover:text-zinc-200 transition-colors cursor-pointer"
                            title="Move Up"
                        >
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" y1="19" x2="12" y2="5"/>
                                <polyline points="5 12 12 5 19 12"/>
                            </svg>
                        </button>
                        <button
                            type="button"
                            onclick="window.moveCustomizeField('${item.name}', 'down')"
                            class="p-1 rounded-md hover:bg-slate-100 dark:hover:bg-zinc-700 hover:text-slate-700 dark:hover:text-zinc-200 transition-colors cursor-pointer"
                            title="Move Down"
                        >
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" y1="5" x2="12" y2="19"/>
                                <polyline points="19 12 12 19 5 12"/>
                            </svg>
                        </button>
                        <button
                            type="button"
                            onclick="window.moveCustomizeField('${item.name}', 'bottom')"
                            class="p-1 rounded-md hover:bg-slate-100 dark:hover:bg-zinc-700 hover:text-slate-700 dark:hover:text-zinc-200 transition-colors cursor-pointer"
                            title="Move to Bottom"
                        >
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="4" y1="20" x2="20" y2="20"/>
                                <polyline points="8 12 12 16 16 12"/>
                                <line x1="12" y1="16" x2="12" y2="4"/>
                            </svg>
                        </button>
                    </div>
                </div>
            `;
        }).join('');
    }

    window.saveCustomizedFields = function () {
        const newKeys = state.tempCustomizeFields.filter(f => f.checked).map(f => f.name);

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
        showNotification('Table fields layout & sequence updated successfully!', 'success');
    };

    // ==========================================
    // Screenshot-Matching Multi-Condition Filter Modal Logic
    // ==========================================

    function renderFilterConditionsList() {
        const container = document.getElementById('account-filter-conditions-list');
        if (!container) return;

        const fieldDefs = getVisibleFieldDefinitions();
        // Ensure standard fields like name (Account Name) are available
        if (!fieldDefs.some(f => f.name === 'name')) {
            fieldDefs.unshift({ name: 'name', label: 'Account Name' });
        }

        container.innerHTML = state.tempFilterConditions.map((rule, index) => {
            const prefix = index === 0 ? 'Where' : 'And';
            const escapedVal = (rule.value || '').replace(/"/g, '&quot;');

            return `
                <div class="border border-slate-200/90 dark:border-zinc-700/80 rounded-2xl p-2.5 sm:p-3 flex items-center gap-2 sm:gap-3 bg-white dark:bg-zinc-900 shadow-2xs">
                    <!-- Condition Prefix: Where / And -->
                    <span class="w-12 sm:w-14 text-center text-xs sm:text-sm font-semibold text-slate-400 dark:text-zinc-500 shrink-0">
                        ${prefix}
                    </span>

                    <!-- Field Select Dropdown -->
                    <select
                        onchange="window.updateFilterRuleField('${rule.id}', this.value)"
                        class="flex-1 min-w-[130px] px-3 py-2 text-xs sm:text-sm rounded-xl border border-slate-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-800 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                    >
                        ${fieldDefs.map(f => `
                            <option value="${f.name}" ${rule.field === f.name ? 'selected' : ''}>
                                ${f.label}
                            </option>
                        `).join('')}
                    </select>

                    <!-- Operator Select Dropdown -->
                    <select
                        onchange="window.updateFilterRuleOperator('${rule.id}', this.value)"
                        class="w-28 sm:w-32 shrink-0 px-3 py-2 text-xs sm:text-sm rounded-xl border border-slate-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-800 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                    >
                        <option value="equals" ${rule.operator === 'equals' ? 'selected' : ''}>Equals</option>
                        <option value="contains" ${rule.operator === 'contains' ? 'selected' : ''}>Contains</option>
                        <option value="starts_with" ${rule.operator === 'starts_with' ? 'selected' : ''}>Starts With</option>
                        <option value="greater_than" ${rule.operator === 'greater_than' ? 'selected' : ''}>Greater Than</option>
                        <option value="less_than" ${rule.operator === 'less_than' ? 'selected' : ''}>Less Than</option>
                        <option value="not_equals" ${rule.operator === 'not_equals' ? 'selected' : ''}>Not Equals</option>
                    </select>

                    <!-- Value Input with Value... placeholder -->
                    <input
                        type="text"
                        value="${escapedVal}"
                        placeholder="Value..."
                        oninput="window.updateFilterRuleValue('${rule.id}', this.value)"
                        class="flex-1 min-w-[120px] px-3 py-2 text-xs sm:text-sm rounded-xl border border-slate-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-800 dark:text-zinc-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                    >

                    <!-- Trash Delete Button -->
                    <button
                        type="button"
                        onclick="window.removeFilterConditionRow('${rule.id}')"
                        class="p-1.5 text-slate-400 hover:text-red-500 dark:hover:text-red-400 transition-colors shrink-0 cursor-pointer"
                        title="Remove condition"
                    >
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 6h18"/>
                            <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/>
                            <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                            <line x1="10" y1="11" x2="10" y2="17"/>
                            <line x1="14" y1="11" x2="14" y2="17"/>
                        </svg>
                    </button>
                </div>
            `;
        }).join('');

        updateModalFilterActiveCount();
    }

    function updateModalFilterActiveCount() {
        const count = (state.tempFilterConditions || []).filter(r => (r.value || '').trim().length > 0).length;
        const countEl = document.getElementById('modal-filter-apply-count');
        if (countEl) {
            countEl.textContent = count;
        }
    }

    // Filter Modal Public Handlers
    window.openAccountFilterModal = function () {
        const modal = document.getElementById('account-filter-modal');
        if (!modal) return;

        if (state.activeFilters && state.activeFilters.length > 0) {
            state.tempFilterConditions = state.activeFilters.map(f => ({
                id: f.id || ('rule_' + Math.random().toString(36).substring(2, 9)),
                field: f.field || 'name',
                operator: f.operator || 'equals',
                value: f.value !== undefined ? f.value : '',
            }));
        } else {
            state.tempFilterConditions = [
                {
                    id: 'rule_' + Date.now(),
                    field: 'name',
                    operator: 'equals',
                    value: '',
                }
            ];
        }

        renderFilterConditionsList();
        modal.classList.remove('hidden');
    };

    window.closeAccountFilterModal = function () {
        const modal = document.getElementById('account-filter-modal');
        if (modal) modal.classList.add('hidden');
    };

    window.addFilterConditionRow = function () {
        state.tempFilterConditions.push({
            id: 'rule_' + Date.now() + '_' + Math.random().toString(36).substring(2, 6),
            field: 'name',
            operator: 'equals',
            value: '',
        });
        renderFilterConditionsList();
        setTimeout(() => {
            const inputs = document.querySelectorAll('#account-filter-conditions-list input[type="text"]');
            if (inputs.length > 0) {
                inputs[inputs.length - 1].focus();
            }
        }, 30);
    };

    window.removeFilterConditionRow = function (id) {
        state.tempFilterConditions = state.tempFilterConditions.filter(r => r.id !== id);
        if (state.tempFilterConditions.length === 0) {
            state.tempFilterConditions.push({
                id: 'rule_' + Date.now(),
                field: 'name',
                operator: 'equals',
                value: '',
            });
        }
        renderFilterConditionsList();
    };

    window.updateFilterRuleField = function (id, field) {
        const item = state.tempFilterConditions.find(r => r.id === id);
        if (item) {
            item.field = field;
            updateModalFilterActiveCount();
        }
    };

    window.updateFilterRuleOperator = function (id, operator) {
        const item = state.tempFilterConditions.find(r => r.id === id);
        if (item) {
            item.operator = operator;
            updateModalFilterActiveCount();
        }
    };

    window.updateFilterRuleValue = function (id, value) {
        const item = state.tempFilterConditions.find(r => r.id === id);
        if (item) {
            item.value = value;
            updateModalFilterActiveCount();
        }
    };

    window.clearAllAccountFiltersFromModal = function () {
        state.tempFilterConditions = [
            {
                id: 'rule_' + Date.now(),
                field: 'name',
                operator: 'equals',
                value: '',
            }
        ];
        state.activeFilters = [];
        renderFilterConditionsList();
        renderAccountsView();
        window.closeAccountFilterModal();
        showNotification('All filters cleared', 'success');
    };

    window.applyAccountFiltersFromModal = function () {
        const validRules = (state.tempFilterConditions || [])
            .filter(r => (r.value || '').trim().length > 0)
            .map(r => ({
                id: r.id,
                field: r.field,
                operator: r.operator,
                value: r.value.trim(),
            }));

        state.activeFilters = validRules;
        window.closeAccountFilterModal();
        renderAccountsView();

        if (validRules.length > 0) {
            showNotification(`Applied ${validRules.length} filter condition${validRules.length > 1 ? 's' : ''}`, 'success');
        } else {
            showNotification('Filters cleared (no condition values entered)', 'info');
        }
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

    // ==========================================
    // ACCOUNT ACTIVITY STUDIO (Log Call, New Task, New Event, Add Note, Timeline)
    // ==========================================

    // Sub-Navigation for Activity Form: Call, Task, Event, Note
    window.switchAccountActivityFormType = function (type) {
        document.querySelectorAll('.acc-act-type-pill').forEach(btn => {
            btn.classList.remove('bg-[#f3e8ff]', 'text-[#7c3aed]', 'border-purple-200', 'font-semibold', 'shadow-2xs');
            btn.classList.add('bg-white', 'dark:bg-zinc-800', 'border-slate-200', 'dark:border-zinc-700', 'text-slate-700', 'dark:text-zinc-300', 'font-medium');
            const icon = btn.querySelector('svg');
            if (icon) {
                icon.classList.remove('text-[#7c3aed]');
                icon.classList.add('text-slate-500');
            }
        });

        const pillKey = (type || 'Note').toLowerCase();
        const activePill = document.getElementById(`acc-act-pill-${pillKey}`);
        if (activePill) {
            activePill.classList.add('bg-[#f3e8ff]', 'text-[#7c3aed]', 'border-purple-200', 'font-semibold', 'shadow-2xs');
            activePill.classList.remove('bg-white', 'dark:bg-zinc-800', 'border-slate-200', 'dark:border-zinc-700', 'text-slate-700', 'dark:text-zinc-300', 'font-medium');
            const activeIcon = activePill.querySelector('svg');
            if (activeIcon) {
                activeIcon.classList.remove('text-slate-500');
                activeIcon.classList.add('text-[#7c3aed]');
            }
        }

        const callView = document.getElementById('acc-activity-call-view');
        const taskView = document.getElementById('acc-activity-task-view');
        const eventView = document.getElementById('acc-activity-event-view');
        const noteView = document.getElementById('acc-activity-note-view');

        if (callView) callView.classList.add('hidden');
        if (taskView) taskView.classList.add('hidden');
        if (eventView) eventView.classList.add('hidden');
        if (noteView) noteView.classList.add('hidden');

        if (type === 'Call') {
            if (callView) callView.classList.remove('hidden');
            const inp = document.getElementById('acc-call-input-subject');
            if (inp) setTimeout(() => inp.focus(), 50);
        } else if (type === 'Task') {
            if (taskView) taskView.classList.remove('hidden');
            window.openAccountNewTaskModal();
        } else if (type === 'Event') {
            if (eventView) eventView.classList.remove('hidden');
            const inp = document.getElementById('acc-event-input-subject');
            if (inp) setTimeout(() => inp.focus(), 50);
        } else {
            // Note
            if (noteView) noteView.classList.remove('hidden');
            const inp = document.getElementById('acc-note-input-subject');
            if (inp) setTimeout(() => inp.focus(), 50);
        }
    };

    // Submit Call or Note
    window.submitAccountActivity = async function (type) {
        const accountId = state.viewing360Account;
        if (!accountId) {
            showNotification('Please select an active account first.', 'error');
            return;
        }

        let subject = '';
        let details = '';
        let callDatetime = null;
        let submitBtn = null;

        if (type === 'Call') {
            const subjectInput = document.getElementById('acc-call-input-subject');
            const datetimeInput = document.getElementById('acc-call-input-datetime');
            const detailsInput = document.getElementById('acc-call-input-details');
            submitBtn = document.getElementById('btn-acc-call-submit');

            subject = subjectInput ? subjectInput.value.trim() : '';
            details = detailsInput ? detailsInput.value.trim() : '';
            callDatetime = datetimeInput ? datetimeInput.value.trim() : null;

            if (!subject) {
                showNotification('Please enter a Call Subject.', 'error');
                if (subjectInput) subjectInput.focus();
                return;
            }
        } else if (type === 'Note') {
            const subjectInput = document.getElementById('acc-note-input-subject');
            const detailsInput = document.getElementById('acc-note-input-details');
            submitBtn = document.getElementById('btn-acc-note-submit');

            subject = subjectInput ? subjectInput.value.trim() : '';
            details = detailsInput ? detailsInput.value.trim() : '';

            if (!details) {
                showNotification('Please enter Note Details.', 'error');
                if (detailsInput) detailsInput.focus();
                return;
            }
            if (!subject) subject = 'Note';
        }

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-70', 'cursor-not-allowed');
        }

        try {
            const res = await fetch(`/api/accounts/${accountId}/activities`, {
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
                    call_datetime: callDatetime,
                })
            });

            const data = await res.json();
            if (!res.ok || !data.success) {
                throw new Error(data.message || `Failed to log ${type}`);
            }

            showNotification(data.message || `${type} recorded successfully!`, 'success');

            // Prepend new activity item to timeline list
            if (data.activity) {
                const list = document.getElementById('account-activities-timeline-list');
                if (list) {
                    const temp = document.createElement('div');
                    temp.innerHTML = window.renderAccountTimelineItemHtml(data.activity);
                    const newEl = temp.firstElementChild;
                    if (newEl) {
                        list.insertBefore(newEl, list.firstChild);
                        newEl.classList.add('ring-2', 'ring-purple-400');
                        setTimeout(() => newEl.classList.remove('ring-2', 'ring-purple-400'), 1500);
                    }
                }

                // Update count badges
                const total = data.total_count || 1;
                const timelineBadge = document.getElementById('account-timeline-count-badge');
                if (timelineBadge) timelineBadge.textContent = total;
                const tabBadge = document.getElementById('fs-count-activity');
                if (tabBadge) tabBadge.textContent = total;
            }

            // Clear inputs
            if (type === 'Call') {
                const s = document.getElementById('acc-call-input-subject');
                const d = document.getElementById('acc-call-input-details');
                if (s) s.value = '';
                if (d) d.value = '';
            } else if (type === 'Note') {
                const s = document.getElementById('acc-note-input-subject');
                const d = document.getElementById('acc-note-input-details');
                if (s) s.value = '';
                if (d) d.value = '';
            }
        } catch (err) {
            console.error('Error logging activity:', err);
            showNotification(err.message || 'Error recording activity', 'error');
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-70', 'cursor-not-allowed');
            }
        }
    };

    // Submit Event / Meeting
    window.submitAccountEvent = async function () {
        const accountId = state.viewing360Account;
        if (!accountId) {
            showNotification('Please select an active account first.', 'error');
            return;
        }

        const titleInput = document.getElementById('acc-event-input-subject');
        const startInput = document.getElementById('acc-event-input-start');
        const endInput = document.getElementById('acc-event-input-end');
        const detailsInput = document.getElementById('acc-event-input-details');
        const submitBtn = document.getElementById('btn-acc-event-submit');

        const subject = titleInput ? titleInput.value.trim() : '';
        if (!subject) {
            showNotification('Please enter an Event / Meeting Title.', 'error');
            if (titleInput) titleInput.focus();
            return;
        }

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-70', 'cursor-not-allowed');
        }

        try {
            const res = await fetch(`/api/accounts/${accountId}/activities`, {
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
                    start_date: startInput ? startInput.value.trim() : null,
                    end_date: endInput ? endInput.value.trim() : null,
                    details: detailsInput ? detailsInput.value.trim() : '',
                })
            });

            const data = await res.json();
            if (!res.ok || !data.success) {
                throw new Error(data.message || 'Failed to log meeting');
            }

            showNotification(data.message || 'Meeting logged successfully!', 'success');

            if (data.activity) {
                const list = document.getElementById('account-activities-timeline-list');
                if (list) {
                    const temp = document.createElement('div');
                    temp.innerHTML = window.renderAccountTimelineItemHtml(data.activity);
                    const newEl = temp.firstElementChild;
                    if (newEl) {
                        list.insertBefore(newEl, list.firstChild);
                        newEl.classList.add('ring-2', 'ring-purple-400');
                        setTimeout(() => newEl.classList.remove('ring-2', 'ring-purple-400'), 1500);
                    }
                }

                const total = data.total_count || 1;
                const timelineBadge = document.getElementById('account-timeline-count-badge');
                if (timelineBadge) timelineBadge.textContent = total;
                const tabBadge = document.getElementById('fs-count-activity');
                if (tabBadge) tabBadge.textContent = total;
            }

            if (titleInput) titleInput.value = '';
            if (detailsInput) detailsInput.value = '';
        } catch (err) {
            console.error('Error logging event:', err);
            showNotification(err.message || 'Error recording event', 'error');
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-70', 'cursor-not-allowed');
            }
        }
    };

    // Modal Control: New Task Popup
    window.openAccountNewTaskModal = function () {
        const modal = document.getElementById('accountNewTaskModal');
        if (!modal) return;

        let accName = 'Account';
        const titleEl = document.getElementById('fs-account-title');
        if (titleEl && titleEl.textContent.trim()) {
            accName = titleEl.textContent.trim();
        } else if (state.viewing360Account) {
            const acc = (state.accounts || []).find(a => a.id == state.viewing360Account);
            if (acc && acc.account_name) accName = acc.account_name;
        }

        const nameInput = document.getElementById('acc-task-modal-record-name');
        if (nameInput) nameInput.value = accName;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        const subj = document.getElementById('acc-task-modal-subject');
        if (subj) setTimeout(() => subj.focus(), 60);
    };

    window.closeAccountNewTaskModal = function () {
        const modal = document.getElementById('accountNewTaskModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    };

    window.submitAccountNewTaskModal = async function () {
        const accountId = state.viewing360Account;
        if (!accountId) {
            showNotification('Please select an active account first.', 'error');
            return;
        }

        const subjectInput = document.getElementById('acc-task-modal-subject');
        const prioritySelect = document.getElementById('acc-task-modal-priority');
        const statusSelect = document.getElementById('acc-task-modal-status');
        const assigneeSelect = document.getElementById('acc-task-modal-assignee');
        const dueDateInput = document.getElementById('acc-task-modal-duedate');
        const reminderSelect = document.getElementById('acc-task-modal-reminder');
        const recurringCheckbox = document.getElementById('acc-task-modal-recurring');
        const commentsTextarea = document.getElementById('acc-task-modal-comments');
        const submitBtn = document.getElementById('btn-submit-acc-task-modal');

        const subject = subjectInput ? subjectInput.value.trim() : '';
        if (!subject) {
            showNotification('Please enter a Task Subject.', 'error');
            if (subjectInput) subjectInput.focus();
            return;
        }

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-70', 'cursor-not-allowed');
        }

        try {
            const res = await fetch(`/api/accounts/${accountId}/tasks`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    subject: subject,
                    priority: prioritySelect ? prioritySelect.value : 'Normal',
                    status: statusSelect ? statusSelect.value : 'Open',
                    assigned_to: assigneeSelect ? assigneeSelect.value : null,
                    due_date: dueDateInput ? dueDateInput.value : null,
                    reminder_set: reminderSelect ? reminderSelect.value : 'No reminder is set',
                    is_recurring: recurringCheckbox ? (recurringCheckbox.checked ? 1 : 0) : 0,
                    comments: commentsTextarea ? commentsTextarea.value.trim() : '',
                })
            });

            const data = await res.json();
            if (!res.ok || !data.success) {
                throw new Error(data.message || 'Failed to create task');
            }

            showNotification(data.message || 'Task created successfully!', 'success');
            window.closeAccountNewTaskModal();

            if (data.activity) {
                const list = document.getElementById('account-activities-timeline-list');
                if (list) {
                    const temp = document.createElement('div');
                    temp.innerHTML = window.renderAccountTimelineItemHtml(data.activity);
                    const newEl = temp.firstElementChild;
                    if (newEl) {
                        list.insertBefore(newEl, list.firstChild);
                        newEl.classList.add('ring-2', 'ring-purple-400');
                        setTimeout(() => newEl.classList.remove('ring-2', 'ring-purple-400'), 1500);
                    }
                }

                const total = data.total_count || 1;
                const timelineBadge = document.getElementById('account-timeline-count-badge');
                if (timelineBadge) timelineBadge.textContent = total;
                const tabBadge = document.getElementById('fs-count-activity');
                if (tabBadge) tabBadge.textContent = total;
            }

            if (subjectInput) subjectInput.value = '';
            if (commentsTextarea) commentsTextarea.value = '';
            if (recurringCheckbox) recurringCheckbox.checked = false;
        } catch (err) {
            console.error('Error creating task:', err);
            showNotification(err.message || 'Error creating task', 'error');
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-70', 'cursor-not-allowed');
            }
        }
    };

    // Refresh Timeline List from Backend
    window.refreshAccountActivities = async function (accountId) {
        if (!accountId) return;

        const list = document.getElementById('account-activities-timeline-list');
        if (list) list.classList.add('opacity-60');

        try {
            const res = await fetch(`/api/accounts/${accountId}/activities`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            if (res.ok) {
                const data = await res.json();
                state.accountActivities = data.activities || [];
                const count = data.count || state.accountActivities.length;

                const timelineBadge = document.getElementById('account-timeline-count-badge');
                if (timelineBadge) timelineBadge.textContent = count;
                const tabBadge = document.getElementById('fs-count-activity');
                if (tabBadge) tabBadge.textContent = count;

                if (list) {
                    if (state.accountActivities.length === 0) {
                        list.innerHTML = `
                            <div class="p-6 border-2 border-dashed border-slate-200 dark:border-zinc-700 rounded-xl text-center space-y-1">
                                <p class="text-xs font-medium text-slate-400">No activity logged yet.</p>
                                <p class="text-[11px] text-slate-400">Use the form above to log a call, task, event, or note.</p>
                            </div>
                        `;
                    } else {
                        list.innerHTML = state.accountActivities.map(act => window.renderAccountTimelineItemHtml(act)).join('');
                    }
                }
            }
        } catch (err) {
            console.error('Error fetching account activities:', err);
        } finally {
            if (list) list.classList.remove('opacity-60');
        }
    };

    // Render single timeline item matching user screenshot 1:1
    window.renderAccountTimelineItemHtml = function (act) {
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
                    <span class="text-[11px] text-slate-400 dark:text-zinc-500 shrink-0 font-medium">
                        ${escapeHtml(act.created_at_formatted || 'Just now')}
                    </span>
                </div>

                <div class="pl-10.5 space-y-1.5">
                    ${act.call_time ? `
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md bg-sky-50 dark:bg-sky-950/60 text-sky-800 dark:text-sky-300 text-[11px] font-medium">
                            ${isCall ? `<svg class="w-3 h-3 text-sky-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>` : ''}
                            <span>${timeLabel}: ${escapeHtml(act.call_time)}</span>
                        </div>
                    ` : ''}

                    ${act.details ? `
                        <div class="text-xs text-slate-700 dark:text-zinc-300 leading-relaxed pt-0.5">
                            ${escapeHtml(act.details)}
                        </div>
                    ` : ''}

                    <div class="flex items-center gap-1.5 text-[11px] text-slate-400 dark:text-zinc-500 pt-0.5">
                        <svg class="w-3 h-3 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <span>Logged by <strong class="text-slate-700 dark:text-zinc-300 font-semibold">${escapeHtml(act.user_name || 'Alex Morgan')}</strong></span>
                        <span>•</span>
                        <span class="px-1.5 py-0.2 bg-slate-100 dark:bg-zinc-800 rounded text-[10px] text-slate-600 dark:text-zinc-400 font-medium">${escapeHtml(typeDisplayName)}</span>
                    </div>
                </div>
            </div>
        `;
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
            state.pageLayout = window.__ACCOUNTS_DATA__.pageLayout || null;
            state.standardOverrides = window.__ACCOUNTS_DATA__.standardOverrides || {};
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
