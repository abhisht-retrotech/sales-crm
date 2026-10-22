/**
 * DealSync Enterprise CRM — Pipeline Opportunities Engine
 * 1:1 Parity with React Client (OpportunitiesView.tsx, CompactDataTable.tsx, UniversalKanban.tsx)
 * Pure Tailwind CSS styling with Dynamic Custom Fields & Field-Level Security Permissions
 */

(function () {
    'use strict';

    // Module State
    const state = {
        opportunities: [],
        customFields: [],
        fieldPermissions: {},
        accounts: [],
        contacts: [],
        purchaseDeals: [],
        users: [],
        stages: ['Discovery', 'Proposal', 'Negotiation', 'Closed Won', 'Closed Lost'],
        activeStage: 'all',
        searchTerm: '',
        viewMode: 'table', // 'table' | 'kanban'
        tableVisibleFields: ['name', 'account_name', 'po_number', 'amount', 'stage', 'probability', 'expected_close_date'],
        kanbanVisibleFields: ['account_name', 'amount', 'po_number', 'probability', 'expected_close_date'],
        activeFilters: [], // array of { id, field, operator, value }
        selectedOpp: null,
        editingOpp: null,
        transferringOpp: null,
        isCreateMode: false,
        isLoading: false,
    };

    const STANDARD_FIELD_DEFINITIONS = [
        { name: 'name', label: 'Opportunity Name', type: 'text', standard: true, required: true },
        { name: 'account_name', label: 'Customer Account', type: 'lookup', standard: true, required: true },
        { name: 'contact_name', label: 'Primary Contact', type: 'lookup', standard: true },
        { name: 'po_number', label: 'Backed Purchase PO', type: 'lookup', standard: true },
        { name: 'amount', label: 'Deal Value ($ USD)', type: 'currency', standard: true, required: true },
        { name: 'currency', label: 'Deal Currency', type: 'dropdown', standard: true, options: ['USD', 'EUR', 'GBP', 'INR', 'CAD', 'AUD', 'JPY', 'AED', 'SGD', 'CHF'] },
        { name: 'stage', label: 'Pipeline Stage', type: 'dropdown', standard: true, required: true, options: ['Discovery', 'Proposal', 'Negotiation', 'Closed Won', 'Closed Lost'] },
        { name: 'probability', label: 'Win Probability (%)', type: 'number', standard: true },
        { name: 'expected_close_date', label: 'Target Close Date', type: 'date', standard: true },
        { name: 'owner_name', label: 'Assigned Owner', type: 'lookup', standard: true },
        { name: 'notes', label: 'Deal Notes / Strategy', type: 'textarea', standard: true },
        { name: 'created_at', label: 'Created Date', type: 'date', standard: true, readOnly: true },
    ];

    // Helper: CSRF Token
    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    // Helper: Toast Notifications
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
        const num = Number(val || 0);
        return '$' + num.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
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

        // 1. Standard Fields
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

        // 2. Dynamic Custom Fields
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
            const savedTable = localStorage.getItem('dealsync_opps_table_cols');
            if (savedTable) {
                const parsed = JSON.parse(savedTable);
                if (Array.isArray(parsed) && parsed.length > 0) {
                    state.tableVisibleFields = parsed;
                }
            }
            const savedKanban = localStorage.getItem('dealsync_opps_kanban_cols');
            if (savedKanban) {
                const parsedK = JSON.parse(savedKanban);
                if (Array.isArray(parsedK) && parsedK.length > 0) {
                    state.kanbanVisibleFields = parsedK;
                }
            }
        } catch (_) {}
    }

    // Save visible fields to localStorage
    function saveViewConfigsToStorage() {
        try {
            localStorage.setItem('dealsync_opps_table_cols', JSON.stringify(state.tableVisibleFields));
            localStorage.setItem('dealsync_opps_kanban_cols', JSON.stringify(state.kanbanVisibleFields));
        } catch (_) {}
    }

    // Filter opportunities by search, active stage, and universal filter rules
    function getFilteredOpportunities() {
        let results = [...state.opportunities];

        // 1. Stage filter
        if (state.activeStage !== 'all') {
            results = results.filter(o => o.stage === state.activeStage);
        }

        // 2. Text Search
        if (state.searchTerm.trim()) {
            const q = state.searchTerm.toLowerCase().trim();
            results = results.filter(o => {
                const name = (o.name || '').toLowerCase();
                const acc = (o.account_name || '').toLowerCase();
                const po = (o.po_number || '').toLowerCase();
                const stage = (o.stage || '').toLowerCase();
                const owner = (o.owner_name || '').toLowerCase();
                return name.includes(q) || acc.includes(q) || po.includes(q) || stage.includes(q) || owner.includes(q);
            });
        }

        // 3. Universal Filter Rules
        if (state.activeFilters.length > 0) {
            results = results.filter(item => {
                return state.activeFilters.every(rule => {
                    const rawVal = item[rule.field];
                    const val = (rawVal !== undefined && rawVal !== null) ? String(rawVal).toLowerCase() : '';
                    const target = String(rule.value || '').toLowerCase();

                    switch (rule.operator) {
                        case 'equals':
                            return val === target;
                        case 'not_equals':
                            return val !== target;
                        case 'contains':
                            return val.includes(target);
                        case 'greater_than':
                            return Number(rawVal) > Number(rule.value);
                        case 'less_than':
                            return Number(rawVal) < Number(rule.value);
                        case 'is_empty':
                            return val === '';
                        case 'is_not_empty':
                            return val !== '';
                        default:
                            return true;
                    }
                });
            });
        }

        return results;
    }

    // Update Top KPI Cards
    function renderKpis() {
        const opps = state.opportunities;
        const totalDeals = opps.length;

        // Total pipeline value ($)
        const totalPipelineValue = opps.reduce((sum, o) => sum + Number(o.amount || 0), 0);
        const kpiPipelineEl = document.getElementById('kpi-total-pipeline');
        if (kpiPipelineEl) {
            kpiPipelineEl.innerText = formatCurrency(totalPipelineValue);
        }

        const countEl = document.getElementById('kpi-total-deals-count');
        if (countEl) {
            countEl.innerText = `${totalDeals} Active Opportunities`;
        }

        // Weighted forecast ($)
        const weightedForecast = opps.reduce((sum, o) => {
            const amt = Number(o.amount || 0);
            const prob = Number(o.probability || 0);
            return sum + (amt * (prob / 100));
        }, 0);
        const kpiForecastEl = document.getElementById('kpi-weighted-forecast');
        if (kpiForecastEl) {
            kpiForecastEl.innerText = formatCurrency(weightedForecast);
        }

        const realizationRate = totalPipelineValue > 0 ? Math.round((weightedForecast / totalPipelineValue) * 100) : 0;
        const realizationEl = document.getElementById('kpi-realization-rate');
        if (realizationEl) {
            realizationEl.innerText = `${realizationRate}% Expected Realization`;
        }

        // Procurement backed (% with PO)
        const backedCount = opps.filter(o => Boolean(o.po_number || o.purchase_deal_id)).length;
        const backedRate = totalDeals > 0 ? Math.round((backedCount / totalDeals) * 100) : 100;
        const kpiBackedEl = document.getElementById('kpi-procurement-backed');
        if (kpiBackedEl) {
            kpiBackedEl.innerText = `${backedRate}% Coupled`;
        }

        // Avg Win Probability
        const avgProb = totalDeals > 0 ? Math.round(opps.reduce((s, o) => s + Number(o.probability || 0), 0) / totalDeals) : 0;
        const kpiProbEl = document.getElementById('kpi-avg-probability');
        if (kpiProbEl) {
            kpiProbEl.innerText = `${avgProb}%`;
        }
    }

    // Render Stage Filter Chips
    function renderStageChips() {
        const container = document.getElementById('opps-stage-chips-container');
        if (!container) return;

        const allTotal = state.opportunities.reduce((s, o) => s + Number(o.amount || 0), 0);

        let html = `
            <span class="text-xs font-semibold text-zinc-500 shrink-0 mr-1">Pipeline Stages:</span>
            <button
                type="button"
                onclick="window.filterOpportunityStage('all')"
                class="px-3 py-1 rounded-full text-xs font-semibold transition-all shrink-0 cursor-pointer ${
                    state.activeStage === 'all'
                        ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900 shadow-xs'
                        : 'bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300'
                }"
            >
                All Deals (${formatCurrency(allTotal)})
            </button>
        `;

        state.stages.forEach(stage => {
            const stageOpps = state.opportunities.filter(o => o.stage === stage);
            const stageTotal = stageOpps.reduce((s, o) => s + Number(o.amount || 0), 0);
            const isSelected = state.activeStage === stage;

            html += `
                <button
                    type="button"
                    onclick="window.filterOpportunityStage('${stage}')"
                    class="px-3 py-1 rounded-full text-xs font-semibold transition-all shrink-0 cursor-pointer ${
                        isSelected
                            ? 'bg-indigo-600 text-white shadow-xs'
                            : 'bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300'
                    }"
                >
                    ${stage} (${formatCurrency(stageTotal)})
                </button>
            `;
        });

        container.innerHTML = html;
    }

    // Render Active Filter Chips
    function renderActiveFilterChips() {
        const bar = document.getElementById('opps-active-filters-bar');
        const badge = document.getElementById('opps-filter-badge');
        if (!bar) return;

        if (state.activeFilters.length === 0) {
            bar.classList.add('hidden');
            if (badge) badge.classList.add('hidden');
            return;
        }

        if (badge) {
            badge.innerText = state.activeFilters.length;
            badge.classList.remove('hidden');
        }

        const visibleDefs = getVisibleFieldDefinitions();
        const defMap = new Map(visibleDefs.map(d => [d.name, d.label]));

        let html = `<span class="text-xs text-zinc-500 font-medium">Active Filters:</span>`;
        state.activeFilters.forEach(rule => {
            const label = defMap.get(rule.field) || rule.field;
            html += `
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-blue-50 text-blue-800 dark:bg-blue-950/40 dark:text-blue-300 border border-blue-200 dark:border-blue-800 text-xs">
                    <strong>${label}</strong> ${rule.operator.replace('_', ' ')} "${rule.value}"
                    <button type="button" onclick="window.removeOpportunityFilterRule('${rule.id}')" class="text-blue-500 hover:text-blue-700 cursor-pointer">
                        <i data-lucide="x" class="w-3 h-3"></i>
                    </button>
                </span>
            `;
        });

        html += `
            <button type="button" onclick="window.clearAllOpportunityFilters()" class="text-xs text-zinc-500 hover:text-red-600 hover:underline cursor-pointer ml-2">
                Clear All
            </button>
        `;

        bar.innerHTML = html;
        bar.classList.remove('hidden');
        if (window.lucide) lucide.createIcons();
    }

    // Stage Badges Helper
    function getStageBadge(stage) {
        switch (stage) {
            case 'Discovery':
                return `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-sky-50 text-sky-700 border border-sky-200 dark:bg-sky-950/40 dark:text-sky-300 dark:border-sky-800">Discovery</span>`;
            case 'Proposal':
                return `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200 dark:bg-indigo-950/40 dark:text-indigo-300 dark:border-indigo-800">Proposal</span>`;
            case 'Negotiation':
                return `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800">Negotiation</span>`;
            case 'Closed Won':
                return `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800">Closed Won</span>`;
            case 'Closed Lost':
                return `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800">Closed Lost</span>`;
            default:
                return `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-zinc-100 text-zinc-700 border border-zinc-200 dark:bg-zinc-800 dark:text-zinc-300">${stage}</span>`;
        }
    }

    // Render Table View
    function renderTableView() {
        const theadRow = document.getElementById('opps-table-header-row');
        const tbody = document.getElementById('opps-table-tbody');
        const emptyState = document.getElementById('opps-empty-state');
        const summary = document.getElementById('opps-table-summary');
        if (!theadRow || !tbody) return;

        const visibleDefs = getVisibleFieldDefinitions();
        const defMap = new Map(visibleDefs.map(d => [d.name, d]));

        // Filter keys respecting permissions
        const activeCols = state.tableVisibleFields.filter(key => {
            const def = defMap.get(key);
            return def && def.permission !== 'Hidden';
        });

        // 1. Build Header
        let headerHtml = `
            <th class="py-3 px-4 w-10 text-center">
                <input type="checkbox" onchange="window.toggleSelectAllOpps(this.checked)" class="rounded text-indigo-600 focus:ring-indigo-500 cursor-pointer">
            </th>
        `;

        activeCols.forEach(colKey => {
            const def = defMap.get(colKey);
            const label = def ? def.label : colKey;
            headerHtml += `<th class="py-3 px-4">${label}</th>`;
        });

        headerHtml += `<th class="py-3 px-4 text-right">Actions</th>`;
        theadRow.innerHTML = headerHtml;

        // 2. Build Rows
        const opps = getFilteredOpportunities();

        if (summary) {
            summary.innerText = `Showing ${opps.length} of ${state.opportunities.length} opportunities`;
        }

        if (opps.length === 0) {
            tbody.innerHTML = '';
            if (emptyState) emptyState.classList.remove('hidden');
            return;
        }

        if (emptyState) emptyState.classList.add('hidden');

        let rowsHtml = '';
        opps.forEach(opp => {
            rowsHtml += `
                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors group">
                    <td class="py-3 px-4 text-center">
                        <input type="checkbox" data-opp-id="${opp.id}" class="rounded text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                    </td>
            `;

            activeCols.forEach(colKey => {
                const def = defMap.get(colKey);
                let cellVal = opp[colKey];

                if (colKey === 'name') {
                    rowsHtml += `
                        <td class="py-3 px-4 font-semibold text-zinc-900 dark:text-zinc-100">
                            <a href="/opportunities/${opp.id}" class="hover:text-indigo-600 no-underline hover:no-underline flex items-center gap-1.5">
                                <i data-lucide="briefcase" class="w-3.5 h-3.5 text-zinc-400"></i>
                                <span>${cellVal || 'Untitled Deal'}</span>
                            </a>
                        </td>
                    `;
                } else if (colKey === 'account_name') {
                    rowsHtml += `
                        <td class="py-3 px-4 text-zinc-600 dark:text-zinc-300 font-medium">
                            ${cellVal ? `<span class="flex items-center gap-1"><i data-lucide="building-2" class="w-3 h-3 text-zinc-400"></i> ${cellVal}</span>` : '<span class="text-zinc-400 italic">Unlinked</span>'}
                        </td>
                    `;
                } else if (colKey === 'po_number') {
                    if (cellVal) {
                        rowsHtml += `
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-50 text-amber-800 border border-amber-200 dark:bg-amber-950/30 dark:text-amber-300 dark:border-amber-800">
                                    <i data-lucide="shopping-bag" class="w-3 h-3"></i>
                                    <span>${cellVal}</span>
                                </span>
                            </td>
                        `;
                    } else {
                        rowsHtml += `<td class="py-3 px-4 text-zinc-400 italic">—</td>`;
                    }
                } else if (colKey === 'amount') {
                    rowsHtml += `
                        <td class="py-3 px-4 font-bold text-zinc-950 dark:text-zinc-100 text-sm">
                            ${formatCurrency(cellVal, opp.currency || 'USD')}
                        </td>
                    `;
                } else if (colKey === 'stage') {
                    rowsHtml += `<td class="py-3 px-4">${getStageBadge(cellVal)}</td>`;
                } else if (colKey === 'probability') {
                    const prob = Number(cellVal || 0);
                    rowsHtml += `
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-2">
                                <div class="w-14 bg-zinc-200 dark:bg-zinc-700 h-1.5 rounded-full overflow-hidden">
                                    <div class="bg-indigo-600 h-full rounded-full" style="width: ${prob}%"></div>
                                </div>
                                <span class="text-[11px] font-semibold text-zinc-700 dark:text-zinc-300">${prob}%</span>
                            </div>
                        </td>
                    `;
                } else if (colKey === 'expected_close_date') {
                    rowsHtml += `
                        <td class="py-3 px-4 text-zinc-500 whitespace-nowrap">
                            <span class="inline-flex items-center gap-1">
                                <i data-lucide="calendar" class="w-3 h-3 text-zinc-400"></i>
                                ${opp.expected_close_date_formatted || cellVal || 'Not scheduled'}
                            </span>
                        </td>
                    `;
                } else {
                    const display = (cellVal !== undefined && cellVal !== null && cellVal !== '') ? String(cellVal) : '—';
                    rowsHtml += `<td class="py-3 px-4 text-zinc-600 dark:text-zinc-300">${display}</td>`;
                }
            });

            // Actions dropdown / buttons
            rowsHtml += `
                <td class="py-3 px-4 text-right whitespace-nowrap">
                    <div class="inline-flex items-center gap-1 opacity-90 group-hover:opacity-100 transition-opacity">
                        <a
                            href="/opportunities/${opp.id}"
                            class="p-1 rounded text-zinc-500 hover:text-indigo-600 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors"
                            title="View 360"
                        >
                            <i data-lucide="external-link" class="w-4 h-4"></i>
                        </a>
                        <button
                            type="button"
                            onclick="window.openOpportunityModal('edit', ${opp.id})"
                            class="p-1 rounded text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-100 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
                            title="Edit Deal"
                        >
                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                        </button>
                        <button
                            type="button"
                            onclick="window.openOpportunityTransferModal(${opp.id})"
                            class="p-1 rounded text-zinc-500 hover:text-indigo-600 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
                            title="Transfer Owner"
                        >
                            <i data-lucide="user-check" class="w-4 h-4"></i>
                        </button>
                        <button
                            type="button"
                            onclick="window.deleteOpportunityRecord(${opp.id})"
                            class="p-1 rounded text-zinc-500 hover:text-red-600 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
                            title="Delete"
                        >
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </div>
                </td>
            </tr>`;
        });

        tbody.innerHTML = rowsHtml;
        if (window.lucide) lucide.createIcons();
    }

    // Render Kanban Board View
    function renderKanbanView() {
        const board = document.getElementById('opps-kanban-board');
        if (!board) return;

        const opps = getFilteredOpportunities();

        let boardHtml = '';

        state.stages.forEach(stage => {
            const stageOpps = opps.filter(o => o.stage === stage);
            const stageTotal = stageOpps.reduce((sum, o) => sum + Number(o.amount || 0), 0);

            boardHtml += `
                <div
                    class="kanban-col bg-zinc-50/80 dark:bg-zinc-900/60 rounded-xl border border-zinc-200 dark:border-zinc-800 p-3 space-y-3 min-h-[420px] flex flex-col"
                    data-stage="${stage}"
                    ondragover="window.handleKanbanDragOver(event)"
                    ondrop="window.handleKanbanDrop(event, '${stage}')"
                >
                    <!-- Column Header -->
                    <div class="flex items-center justify-between pb-2 border-b border-zinc-200 dark:border-zinc-800 shrink-0">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-zinc-900 dark:text-zinc-100">${stage}</span>
                            <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-zinc-200 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-300">
                                ${stageOpps.length}
                            </span>
                        </div>
                        <div class="text-xs font-bold text-zinc-900 dark:text-zinc-100">
                            ${formatCurrency(stageTotal)}
                        </div>
                    </div>

                    <!-- Cards Container -->
                    <div class="space-y-2.5 flex-1 kanban-cards-wrapper overflow-y-auto max-h-[70vh]">
            `;

            if (stageOpps.length === 0) {
                boardHtml += `
                    <div class="py-8 text-center text-zinc-400 text-xs italic border border-dashed border-zinc-200 dark:border-zinc-800 rounded-lg">
                        No deals in ${stage}
                    </div>
                `;
            } else {
                stageOpps.forEach(opp => {
                    const prob = Number(opp.probability || 0);

                    boardHtml += `
                        <div
                            class="bg-white dark:bg-zinc-800 rounded-xl p-3.5 border border-zinc-200 dark:border-zinc-700 shadow-2xs hover:shadow-md transition-all cursor-grab active:cursor-grabbing space-y-2.5"
                            draggable="true"
                            ondragstart="window.handleKanbanDragStart(event, ${opp.id})"
                        >
                            <div class="flex items-start justify-between gap-2">
                                <a href="/opportunities/${opp.id}" class="text-xs font-bold text-zinc-950 dark:text-zinc-100 hover:text-indigo-600 no-underline hover:no-underline line-clamp-2 leading-snug">
                                    ${opp.name}
                                </a>
                                <span class="shrink-0 px-1.5 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300">
                                    ${prob}% Win
                                </span>
                            </div>

                            <div class="text-[11px] text-zinc-500">
                                Customer: <strong class="text-zinc-800 dark:text-zinc-200">${opp.account_name || 'Unlinked'}</strong>
                            </div>

                            ${opp.po_number ? `
                                <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-800 border border-amber-200 dark:bg-amber-950/30 dark:text-amber-300 dark:border-amber-800">
                                    <i data-lucide="shopping-bag" class="w-3 h-3"></i>
                                    <span>Backed by PO: <strong>${opp.po_number}</strong></span>
                                </div>
                            ` : ''}

                            <div class="flex items-center justify-between pt-1">
                                <span class="text-sm font-extrabold text-zinc-950 dark:text-zinc-100">
                                    ${formatCurrency(opp.amount, opp.currency || 'USD')}
                                </span>
                                <span class="text-[10px] text-zinc-400 flex items-center gap-1">
                                    <i data-lucide="calendar" class="w-3 h-3"></i>
                                    ${opp.expected_close_date || 'No target'}
                                </span>
                            </div>

                            <!-- Stage Advancement Shortcut -->
                            <div class="pt-2 border-t border-zinc-100 dark:border-zinc-700/60 flex items-center justify-between">
                                <span class="text-[10px] text-zinc-400">${opp.owner_name || 'Unassigned'}</span>
                                <div class="flex items-center gap-1">
                                    ${getNextStageButton(opp)}
                                    <button
                                        type="button"
                                        onclick="window.openOpportunityModal('edit', ${opp.id})"
                                        class="p-1 rounded text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 cursor-pointer"
                                        title="Edit"
                                    >
                                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                });
            }

            boardHtml += `
                    </div>
                </div>
            `;
        });

        board.innerHTML = boardHtml;
        if (window.lucide) lucide.createIcons();
    }

    // Helper: Next Stage Button for Kanban Card
    function getNextStageButton(opp) {
        const curStage = opp.stage;
        let nextStage = null;
        if (curStage === 'Discovery') nextStage = 'Proposal';
        else if (curStage === 'Proposal') nextStage = 'Negotiation';
        else if (curStage === 'Negotiation') nextStage = 'Closed Won';

        if (!nextStage) return '';

        return `
            <button
                type="button"
                onclick="window.moveOpportunityStage(${opp.id}, '${nextStage}')"
                class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-zinc-100 hover:bg-indigo-50 hover:text-indigo-600 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-zinc-700 dark:text-zinc-200 transition-colors cursor-pointer"
                title="Advance to ${nextStage}"
            >
                <span>Advance</span>
                <i data-lucide="arrow-right" class="w-3 h-3"></i>
            </button>
        `;
    }

    // Main Render Dispatcher
    function renderMainView() {
        renderKpis();
        renderStageChips();
        renderActiveFilterChips();

        const tableContainer = document.getElementById('opps-table-view-container');
        const kanbanContainer = document.getElementById('opps-kanban-view-container');
        const btnTable = document.getElementById('btn-view-table');
        const btnKanban = document.getElementById('btn-view-kanban');

        if (state.viewMode === 'kanban') {
            if (tableContainer) tableContainer.classList.add('hidden');
            if (kanbanContainer) kanbanContainer.classList.remove('hidden');

            if (btnTable) {
                btnTable.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-md transition-all cursor-pointer text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white';
            }
            if (btnKanban) {
                btnKanban.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-md transition-all cursor-pointer bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white shadow-2xs';
            }

            renderKanbanView();
        } else {
            if (kanbanContainer) kanbanContainer.classList.add('hidden');
            if (tableContainer) tableContainer.classList.remove('hidden');

            if (btnKanban) {
                btnKanban.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-md transition-all cursor-pointer text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white';
            }
            if (btnTable) {
                btnTable.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-md transition-all cursor-pointer bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white shadow-2xs';
            }

            renderTableView();
        }
    }

    // HTML5 Drag & Drop Handlers for Kanban
    window.handleKanbanDragStart = function (e, oppId) {
        e.dataTransfer.setData('text/plain', String(oppId));
        e.dataTransfer.effectAllowed = 'move';
    };

    window.handleKanbanDragOver = function (e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        const col = e.currentTarget;
        col.classList.add('border-indigo-400', 'bg-indigo-50/20');
    };

    window.handleKanbanDrop = function (e, targetStage) {
        e.preventDefault();
        const col = e.currentTarget;
        col.classList.remove('border-indigo-400', 'bg-indigo-50/20');
        const oppId = e.dataTransfer.getData('text/plain');
        if (oppId) {
            window.moveOpportunityStage(Number(oppId), targetStage);
        }
    };

    // Stage Move Handler
    window.moveOpportunityStage = async function (id, newStage) {
        try {
            const res = await fetch(`/api/opportunities/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ stage: newStage }),
            });
            const data = await res.json();
            if (data.success && data.opportunity) {
                const idx = state.opportunities.findIndex(o => o.id === id);
                if (idx !== -1) {
                    state.opportunities[idx] = data.opportunity;
                }
                showNotification(data.message || `Opportunity moved to ${newStage}`);
                renderMainView();
            } else {
                showNotification(data.message || 'Failed to update opportunity stage', 'error');
            }
        } catch (err) {
            console.error('Error moving stage:', err);
            showNotification('Error updating pipeline stage', 'error');
        }
    };

    // Stage Change in Form (auto-adjust probability)
    window.handleStageChangeInForm = function (stage) {
        const probInput = document.getElementById('form-opp-probability');
        const probBadge = document.getElementById('form-opp-probability-badge');
        const stageProbMap = {
            'Discovery': 25,
            'Proposal': 50,
            'Negotiation': 75,
            'Closed Won': 100,
            'Closed Lost': 0,
        };
        const prob = stageProbMap[stage] ?? 50;
        if (probInput) probInput.value = prob;
        if (probBadge) probBadge.innerText = prob + '%';
    };

    // Filter Stage Chip Click
    window.filterOpportunityStage = function (stage) {
        state.activeStage = stage;
        renderMainView();
    };

    // View Switcher: Table vs Kanban
    window.switchOpportunityView = function (mode) {
        state.viewMode = mode;
        renderMainView();
    };

    // Render Dynamic Custom Fields in Modal (Enforcing Permissions)
    function renderCustomFieldsInModal(existingRecord = null) {
        const container = document.getElementById('opp-dynamic-fields-container');
        const section = document.getElementById('opp-dynamic-custom-fields-section');
        if (!container || !section) return;

        container.innerHTML = '';
        let visibleCount = 0;

        state.customFields.forEach(cf => {
            const fName = cf.field_name;
            const perm = getPermission(fName);

            // 1. If Hidden, do not render in form at all
            if (perm === 'Hidden') {
                return;
            }

            visibleCount++;
            const isReadOnly = (perm === 'Read-Only');
            const label = cf.field_label;
            const isRequired = Boolean(cf.is_required);
            const helpText = cf.help_text || '';
            const dataType = (cf.data_type || 'text').toLowerCase();

            // Existing value
            let val = '';
            if (existingRecord) {
                if (existingRecord[fName] !== undefined && existingRecord[fName] !== null) {
                    val = existingRecord[fName];
                } else if (existingRecord.custom_data && existingRecord.custom_data[fName] !== undefined) {
                    val = existingRecord.custom_data[fName];
                }
            } else if (cf.default_value !== null && cf.default_value !== undefined) {
                val = cf.default_value;
            }

            let fieldHtml = `
                <div class="space-y-1">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300">
                            ${label} ${isRequired ? '<span class="text-red-500">*</span>' : ''}
                        </label>
                        ${isReadOnly ? `
                            <span class="inline-flex items-center gap-1 text-[10px] text-amber-600 dark:text-amber-400 font-semibold">
                                <i data-lucide="lock" class="w-3 h-3"></i> Read-Only
                            </span>
                        ` : ''}
                    </div>
            `;

            if (dataType === 'picklist' || dataType === 'dropdown') {
                const options = cf.options ? cf.options.split(',').map(s => s.trim()) : [];
                fieldHtml += `
                    <select
                        name="cf_${fName}"
                        data-cf-name="${fName}"
                        ${isReadOnly ? 'disabled' : ''}
                        ${isRequired && !isReadOnly ? 'required' : ''}
                        class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 ${isReadOnly ? 'bg-zinc-100 dark:bg-zinc-800/50 cursor-not-allowed opacity-75' : 'focus:ring-2 focus:ring-indigo-500'}"
                    >
                        <option value="">-- Select ${label} --</option>
                        ${options.map(opt => `<option value="${opt}" ${String(val) === String(opt) ? 'selected' : ''}>${opt}</option>`).join('')}
                    </select>
                `;
            } else if (dataType === 'number') {
                fieldHtml += `
                    <input
                        type="number"
                        name="cf_${fName}"
                        data-cf-name="${fName}"
                        value="${val !== '' ? val : ''}"
                        ${isReadOnly ? 'readonly' : ''}
                        ${isRequired && !isReadOnly ? 'required' : ''}
                        placeholder="${helpText || label}"
                        class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 ${isReadOnly ? 'bg-zinc-100 dark:bg-zinc-800/50 cursor-not-allowed opacity-75' : 'focus:ring-2 focus:ring-indigo-500'}"
                    >
                `;
            } else if (dataType === 'date') {
                fieldHtml += `
                    <input
                        type="date"
                        name="cf_${fName}"
                        data-cf-name="${fName}"
                        value="${val || ''}"
                        ${isReadOnly ? 'readonly' : ''}
                        ${isRequired && !isReadOnly ? 'required' : ''}
                        class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 ${isReadOnly ? 'bg-zinc-100 dark:bg-zinc-800/50 cursor-not-allowed opacity-75' : 'focus:ring-2 focus:ring-indigo-500'}"
                    >
                `;
            } else if (dataType === 'textarea') {
                fieldHtml += `
                    <textarea
                        name="cf_${fName}"
                        data-cf-name="${fName}"
                        rows="2"
                        ${isReadOnly ? 'readonly' : ''}
                        ${isRequired && !isReadOnly ? 'required' : ''}
                        placeholder="${helpText || label}"
                        class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 ${isReadOnly ? 'bg-zinc-100 dark:bg-zinc-800/50 cursor-not-allowed opacity-75' : 'focus:ring-2 focus:ring-indigo-500'}"
                    >${val || ''}</textarea>
                `;
            } else {
                fieldHtml += `
                    <input
                        type="text"
                        name="cf_${fName}"
                        data-cf-name="${fName}"
                        value="${val || ''}"
                        ${isReadOnly ? 'readonly' : ''}
                        ${isRequired && !isReadOnly ? 'required' : ''}
                        placeholder="${helpText || label}"
                        class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 ${isReadOnly ? 'bg-zinc-100 dark:bg-zinc-800/50 cursor-not-allowed opacity-75' : 'focus:ring-2 focus:ring-indigo-500'}"
                    >
                `;
            }

            if (helpText) {
                fieldHtml += `<p class="text-[10px] text-zinc-400">${helpText}</p>`;
            }
            fieldHtml += `</div>`;

            container.innerHTML += fieldHtml;
        });

        if (visibleCount === 0) {
            section.classList.add('hidden');
        } else {
            section.classList.remove('hidden');
        }

        if (window.lucide) lucide.createIcons();
    }

    // Modal Control: Open Create / Edit Modal
    window.openOpportunityModal = function (mode = 'create', id = null) {
        const modal = document.getElementById('opp-form-modal');
        const form = document.getElementById('opp-record-form');
        const title = document.getElementById('opp-form-modal-title');
        const idInput = document.getElementById('form-opp-id');
        const probBadge = document.getElementById('form-opp-probability-badge');
        if (!modal || !form) return;

        form.reset();

        // Apply field-level permissions on standard inputs
        document.querySelectorAll('#opp-record-form .opp-field-group').forEach(group => {
            const fName = group.getAttribute('data-field-name');
            const perm = getPermission(fName);
            if (perm === 'Hidden') {
                group.classList.add('hidden');
            } else {
                group.classList.remove('hidden');
                const input = group.querySelector('input, select, textarea');
                if (input) {
                    if (perm === 'Read-Only') {
                        input.setAttribute('disabled', 'disabled');
                        input.classList.add('bg-zinc-100', 'cursor-not-allowed', 'opacity-75');
                    } else {
                        input.removeAttribute('disabled');
                        input.classList.remove('bg-zinc-100', 'cursor-not-allowed', 'opacity-75');
                    }
                }
            }
        });

        if (mode === 'edit' && id) {
            const opp = state.opportunities.find(o => o.id === id);
            if (!opp) return;

            state.editingOpp = opp;
            state.isCreateMode = false;
            title.innerText = `Edit Deal — ${opp.name}`;
            idInput.value = opp.id;

            document.getElementById('form-opp-name').value = opp.name || '';
            document.getElementById('form-opp-account-id').value = opp.account_id || '';
            document.getElementById('form-opp-contact-id').value = opp.contact_id || '';
            document.getElementById('form-opp-purchase-deal-id').value = opp.purchase_deal_id || '';
            document.getElementById('form-opp-amount').value = opp.amount || '';
            document.getElementById('form-opp-stage').value = opp.stage || 'Discovery';
            document.getElementById('form-opp-probability').value = opp.probability || 50;
            if (probBadge) probBadge.innerText = (opp.probability || 50) + '%';
            document.getElementById('form-opp-close-date').value = opp.expected_close_date || '';
            document.getElementById('form-opp-assigned-to').value = opp.assigned_to || '';
            document.getElementById('form-opp-notes').value = opp.notes || '';

            renderCustomFieldsInModal(opp);
        } else {
            state.editingOpp = null;
            state.isCreateMode = true;
            title.innerText = 'Create Quick Deal';
            idInput.value = '';
            document.getElementById('form-opp-stage').value = 'Discovery';
            document.getElementById('form-opp-probability').value = 25;
            if (probBadge) probBadge.innerText = '25%';

            renderCustomFieldsInModal(null);
        }

        modal.classList.remove('hidden');
    };

    window.closeOpportunityModal = function () {
        const modal = document.getElementById('opp-form-modal');
        if (modal) modal.classList.add('hidden');
    };

    // Save Opportunity Form (Create or Update)
    window.saveOpportunityForm = async function (e) {
        e.preventDefault();

        const id = document.getElementById('form-opp-id').value;
        const isEdit = Boolean(id);

        const payload = {
            name: document.getElementById('form-opp-name').value,
            account_id: document.getElementById('form-opp-account-id').value,
            contact_id: document.getElementById('form-opp-contact-id').value || null,
            purchase_deal_id: document.getElementById('form-opp-purchase-deal-id').value || null,
            amount: document.getElementById('form-opp-amount').value,
            stage: document.getElementById('form-opp-stage').value,
            probability: document.getElementById('form-opp-probability').value,
            expected_close_date: document.getElementById('form-opp-close-date').value || null,
            assigned_to: document.getElementById('form-opp-assigned-to').value || null,
            notes: document.getElementById('form-opp-notes').value || null,
            custom_fields: {},
        };

        // Gather dynamic custom fields
        document.querySelectorAll('#opp-dynamic-fields-container [data-cf-name]').forEach(el => {
            const cfName = el.getAttribute('data-cf-name');
            payload.custom_fields[cfName] = el.value;
        });

        const url = isEdit ? `/api/opportunities/${id}` : '/api/opportunities';
        const method = isEdit ? 'PUT' : 'POST';

        const btn = document.getElementById('btn-save-opp-form');
        if (btn) btn.disabled = true;

        try {
            const res = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json',
                },
                body: JSON.stringify(payload),
            });
            const data = await res.json();

            if (data.success && data.opportunity) {
                if (isEdit) {
                    const idx = state.opportunities.findIndex(o => o.id === Number(id));
                    if (idx !== -1) {
                        state.opportunities[idx] = data.opportunity;
                    }
                } else {
                    state.opportunities.unshift(data.opportunity);
                }

                showNotification(data.message || 'Opportunity saved successfully!');
                window.closeOpportunityModal();
                renderMainView();
            } else {
                showNotification(data.message || 'Failed to save opportunity', 'error');
            }
        } catch (err) {
            console.error('Error saving opportunity:', err);
            showNotification('An unexpected error occurred while saving', 'error');
        } finally {
            if (btn) btn.disabled = false;
        }
    };

    // Delete Opportunity
    window.deleteOpportunityRecord = async function (id) {
        const opp = state.opportunities.find(o => o.id === id);
        const name = opp ? opp.name : 'this deal';
        if (!confirm(`Are you sure you want to delete opportunity "${name}"? This action cannot be undone.`)) {
            return;
        }

        try {
            const res = await fetch(`/api/opportunities/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json',
                },
            });
            const data = await res.json();
            if (data.success) {
                state.opportunities = state.opportunities.filter(o => o.id !== id);
                showNotification(data.message || 'Opportunity deleted successfully');
                renderMainView();
            } else {
                showNotification(data.message || 'Failed to delete opportunity', 'error');
            }
        } catch (err) {
            console.error('Error deleting opportunity:', err);
            showNotification('Error deleting opportunity', 'error');
        }
    };

    // Transfer Owner Modal Handlers
    window.openOpportunityTransferModal = function (id) {
        const opp = state.opportunities.find(o => o.id === id);
        if (!opp) return;

        state.transferringOpp = opp;
        document.getElementById('transfer-opp-id').value = opp.id;
        document.getElementById('transfer-opp-name-label').innerText = `Reassign ownership for "${opp.name}" (Account: ${opp.account_name}).`;
        if (opp.assigned_to) {
            document.getElementById('transfer-new-owner-id').value = opp.assigned_to;
        }

        document.getElementById('opp-transfer-modal').classList.remove('hidden');
    };

    window.closeOpportunityTransferModal = function () {
        document.getElementById('opp-transfer-modal').classList.add('hidden');
    };

    window.submitOpportunityTransfer = async function () {
        const id = document.getElementById('transfer-opp-id').value;
        const newOwnerId = document.getElementById('transfer-new-owner-id').value;
        if (!newOwnerId) {
            showNotification('Please select an owner to transfer to', 'error');
            return;
        }

        try {
            const res = await fetch(`/api/opportunities/${id}/transfer`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ assigned_to: Number(newOwnerId) }),
            });
            const data = await res.json();
            if (data.success && data.opportunity) {
                const idx = state.opportunities.findIndex(o => o.id === Number(id));
                if (idx !== -1) {
                    state.opportunities[idx] = data.opportunity;
                }
                showNotification(data.message || 'Opportunity reassigned successfully');
                window.closeOpportunityTransferModal();
                renderMainView();
            } else {
                showNotification(data.message || 'Failed to reassign opportunity', 'error');
            }
        } catch (err) {
            console.error('Error transferring opportunity:', err);
            showNotification('Error reassigning owner', 'error');
        }
    };

    // Customize Visible Fields Modal
    window.openOpportunityConfigModal = function () {
        const modal = document.getElementById('opp-config-modal');
        const list = document.getElementById('opp-config-fields-list');
        if (!modal || !list) return;

        const visibleDefs = getVisibleFieldDefinitions();
        const activeList = state.viewMode === 'kanban' ? state.kanbanVisibleFields : state.tableVisibleFields;

        let html = '';
        visibleDefs.forEach(def => {
            const isChecked = activeList.includes(def.name);
            html += `
                <label class="flex items-center justify-between p-2 rounded-lg hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors cursor-pointer border border-zinc-100 dark:border-zinc-800">
                    <div class="flex items-center gap-2">
                        <input
                            type="checkbox"
                            value="${def.name}"
                            class="opp-col-checkbox rounded text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                            ${isChecked ? 'checked' : ''}
                        >
                        <span class="text-xs font-medium text-zinc-800 dark:text-zinc-200">${def.label}</span>
                    </div>
                    ${def.isCustom ? '<span class="text-[10px] text-indigo-600 bg-indigo-50 dark:bg-indigo-950/40 px-1.5 py-0.2 rounded font-semibold">Custom</span>' : ''}
                </label>
            `;
        });

        list.innerHTML = html;
        modal.classList.remove('hidden');
    };

    window.closeOpportunityConfigModal = function () {
        document.getElementById('opp-config-modal').classList.add('hidden');
    };

    window.saveOpportunityFieldConfigs = function () {
        const checked = Array.from(document.querySelectorAll('.opp-col-checkbox:checked')).map(cb => cb.value);
        if (checked.length === 0) {
            showNotification('At least one field must remain visible', 'error');
            return;
        }

        if (state.viewMode === 'kanban') {
            state.kanbanVisibleFields = checked;
        } else {
            state.tableVisibleFields = checked;
        }

        saveViewConfigsToStorage();
        window.closeOpportunityConfigModal();
        renderMainView();
        showNotification('Visible fields configuration saved');
    };

    window.resetOpportunityDefaultFields = function () {
        state.tableVisibleFields = ['name', 'account_name', 'po_number', 'amount', 'stage', 'probability', 'expected_close_date'];
        state.kanbanVisibleFields = ['account_name', 'amount', 'po_number', 'probability', 'expected_close_date'];
        saveViewConfigsToStorage();
        window.closeOpportunityConfigModal();
        renderMainView();
        showNotification('Default columns restored');
    };

    // Universal Filter Modal Handlers
    window.openOpportunityFilterModal = function () {
        const modal = document.getElementById('opp-filter-modal');
        if (!modal) return;
        const container = document.getElementById('opp-filter-rules-container');
        container.innerHTML = '';

        if (state.activeFilters.length === 0) {
            window.addOpportunityFilterRule();
        } else {
            state.activeFilters.forEach(rule => window.addOpportunityFilterRule(rule));
        }

        modal.classList.remove('hidden');
    };

    window.closeOpportunityFilterModal = function () {
        document.getElementById('opp-filter-modal').classList.add('hidden');
    };

    window.addOpportunityFilterRule = function (existingRule = null) {
        const container = document.getElementById('opp-filter-rules-container');
        if (!container) return;

        const id = existingRule ? existingRule.id : 'rule_' + Date.now() + '_' + Math.random().toString(36).substr(2, 4);
        const visibleDefs = getVisibleFieldDefinitions();

        const ruleDiv = document.createElement('div');
        ruleDiv.className = 'opp-filter-rule-row flex items-center gap-2 p-2 rounded-lg bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200 dark:border-zinc-700 text-xs';
        ruleDiv.id = id;

        let optionsHtml = '';
        visibleDefs.forEach(d => {
            const isSel = existingRule && existingRule.field === d.name;
            optionsHtml += `<option value="${d.name}" ${isSel ? 'selected' : ''}>${d.label}</option>`;
        });

        const curOp = existingRule ? existingRule.operator : 'contains';
        const curVal = existingRule ? existingRule.value : '';

        ruleDiv.innerHTML = `
            <select class="rule-field-select px-2.5 py-1.5 rounded-md border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 flex-1">
                ${optionsHtml}
            </select>
            <select class="rule-operator-select px-2.5 py-1.5 rounded-md border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100">
                <option value="contains" ${curOp === 'contains' ? 'selected' : ''}>contains</option>
                <option value="equals" ${curOp === 'equals' ? 'selected' : ''}>equals</option>
                <option value="not_equals" ${curOp === 'not_equals' ? 'selected' : ''}>not equals</option>
                <option value="greater_than" ${curOp === 'greater_than' ? 'selected' : ''}>greater than</option>
                <option value="less_than" ${curOp === 'less_than' ? 'selected' : ''}>less than</option>
                <option value="is_empty" ${curOp === 'is_empty' ? 'selected' : ''}>is empty</option>
                <option value="is_not_empty" ${curOp === 'is_not_empty' ? 'selected' : ''}>is not empty</option>
            </select>
            <input
                type="text"
                placeholder="Value..."
                value="${curVal}"
                class="rule-value-input px-2.5 py-1.5 rounded-md border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 flex-1"
            >
            <button type="button" onclick="document.getElementById('${id}').remove()" class="text-zinc-400 hover:text-red-600 p-1 cursor-pointer">
                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
            </button>
        `;

        container.appendChild(ruleDiv);
        if (window.lucide) lucide.createIcons();
    };

    window.applyOpportunityFilters = function () {
        const rows = document.querySelectorAll('.opp-filter-rule-row');
        const rules = [];

        rows.forEach(r => {
            const field = r.querySelector('.rule-field-select').value;
            const operator = r.querySelector('.rule-operator-select').value;
            const value = r.querySelector('.rule-value-input').value;
            if (field) {
                rules.push({ id: r.id, field, operator, value });
            }
        });

        state.activeFilters = rules;
        window.closeOpportunityFilterModal();
        renderMainView();
    };

    window.clearAllOpportunityFilters = function () {
        state.activeFilters = [];
        window.closeOpportunityFilterModal();
        renderMainView();
    };

    window.removeOpportunityFilterRule = function (id) {
        state.activeFilters = state.activeFilters.filter(r => r.id !== id);
        renderMainView();
    };

    // Export CSV
    window.exportOpportunitiesCsv = function () {
        const opps = getFilteredOpportunities();
        if (opps.length === 0) {
            showNotification('No opportunities to export', 'error');
            return;
        }

        const visibleDefs = getVisibleFieldDefinitions();
        const headers = visibleDefs.map(d => `"${d.label.replace(/"/g, '""')}"`);

        const rows = opps.map(opp => {
            return visibleDefs.map(d => {
                const val = opp[d.name] !== undefined && opp[d.name] !== null ? String(opp[d.name]) : '';
                return `"${val.replace(/"/g, '""')}"`;
            }).join(',');
        });

        const csvContent = 'data:text/csv;charset=utf-8,' + [headers.join(','), ...rows].join('\n');
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement('a');
        link.setAttribute('href', encodedUri);
        link.setAttribute('download', `opportunities_export_${new Date().toISOString().slice(0, 10)}.csv`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        showNotification(`Exported ${opps.length} opportunities to CSV`);
    };

    // Refresh Data from API
    window.refreshOpportunitiesData = async function () {
        try {
            const res = await fetch('/api/opportunities', {
                headers: { 'Accept': 'application/json' },
            });
            const data = await res.json();
            if (Array.isArray(data)) {
                state.opportunities = data;
                renderMainView();
                showNotification('Opportunities refreshed from database');
            }
        } catch (err) {
            console.error('Error refreshing opportunities:', err);
        }
    };

    // Toggle Select All Checkbox
    window.toggleSelectAllOpps = function (checked) {
        document.querySelectorAll('#opps-table-tbody input[type="checkbox"]').forEach(cb => {
            cb.checked = checked;
        });
    };

    // Search Input Listener
    function setupSearchListener() {
        const searchInput = document.getElementById('opps-search-input');
        if (searchInput) {
            searchInput.value = state.searchTerm;
            searchInput.addEventListener('input', (e) => {
                state.searchTerm = e.target.value;
                renderMainView();
            });
        }
    }

    // Main Module Initializer (Called on page load and SPA route transition)
    window.initOpportunitiesModule = function () {
        const root = document.getElementById('opportunities-page-root');
        if (!root) return;

        // Hydrate from window.__OPPORTUNITIES_DATA__ if available
        if (window.__OPPORTUNITIES_DATA__) {
            const h = window.__OPPORTUNITIES_DATA__;
            state.opportunities = Array.isArray(h.initialOpportunities) ? h.initialOpportunities : [];
            state.customFields = Array.isArray(h.customFields) ? h.customFields : [];
            state.fieldPermissions = h.fieldPermissions || {};
            state.accounts = Array.isArray(h.accounts) ? h.accounts : [];
            state.contacts = Array.isArray(h.contacts) ? h.contacts : [];
            state.purchaseDeals = Array.isArray(h.purchaseDeals) ? h.purchaseDeals : [];
            state.users = Array.isArray(h.users) ? h.users : [];
            if (Array.isArray(h.stages) && h.stages.length > 0) {
                state.stages = h.stages;
            }
        }

        loadSavedViewConfigs();
        setupSearchListener();
        renderMainView();
    };

    // Auto-init on script evaluation
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', window.initOpportunitiesModule);
    } else {
        window.initOpportunitiesModule();
    }
})();
