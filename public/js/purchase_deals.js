/**
 * DealSync Enterprise CRM — Purchase Deals & Procurement Engine
 * 1:1 Parity with React Client (PurchaseDealsView.tsx, CompactDataTable.tsx, UniversalKanban.tsx)
 * Pure Tailwind CSS styling with Dynamic Custom Fields & Field-Level Security Permissions
 */

(function () {
    'use strict';

    // Module State
    const state = {
        deals: [],
        customFields: [],
        fieldPermissions: {},
        accounts: [],
        contacts: [],
        opportunities: [],
        users: [],
        stages: ['Draft', 'RFQ Sent', 'Quote Received', 'PO Issued', 'In Production', 'Completed'],
        activeStage: 'all',
        searchTerm: '',
        viewMode: 'table', // 'table' | 'kanban'
        tableVisibleFields: ['po_number', 'vendor_name', 'amount', 'stage', 'expected_delivery_date', 'payment_terms'],
        kanbanVisibleFields: ['vendor_name', 'amount', 'expected_delivery_date', 'payment_terms'],
        activeFilters: [], // array of { id, field, operator, value }
        selectedDeal: null,
        editingDeal: null,
        transferringDeal: null,
        isCreateMode: false,
        isLoading: false,
    };

    const STANDARD_FIELD_DEFINITIONS = [
        { name: 'po_number', label: 'PO Number', type: 'text', standard: true, required: false },
        { name: 'title', label: 'PO Order Title', type: 'text', standard: true, required: true },
        { name: 'vendor_name', label: 'Supplier / Vendor', type: 'lookup', standard: true, required: true },
        { name: 'amount', label: 'PO Total Spend', type: 'currency', standard: true, required: true },
        { name: 'currency', label: 'PO Currency', type: 'dropdown', standard: true, options: ['USD', 'EUR', 'GBP', 'INR', 'CAD', 'AUD', 'JPY', 'AED', 'SGD', 'CHF'] },
        { name: 'stage', label: 'Procurement Stage', type: 'dropdown', standard: true, required: true, options: ['Draft', 'RFQ Sent', 'Quote Received', 'PO Issued', 'In Production', 'Completed'] },
        { name: 'expected_delivery_date', label: 'Target Delivery Date', type: 'date', standard: true },
        { name: 'payment_terms', label: 'Payment Terms', type: 'dropdown', standard: true, options: ['Net 15', 'Net 30', 'Net 45', 'Net 60', 'Advance Wire', 'Cash on Delivery'] },
        { name: 'owner_name', label: 'Assigned Buyer', type: 'lookup', standard: true },
        { name: 'notes', label: 'Order Notes & Instructions', type: 'textarea', standard: true },
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
                    type: cf.data_type || 'text',
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

    // Stage Badges Styling
    function getStageBadge(stage) {
        switch (stage) {
            case 'Draft':
                return `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-zinc-100 text-zinc-700 border border-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:border-zinc-700">● Draft</span>`;
            case 'RFQ Sent':
                return `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800">● RFQ Sent</span>`;
            case 'Quote Received':
                return `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800">● Quote Received</span>`;
            case 'PO Issued':
                return `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-50 text-purple-700 border border-purple-200 dark:bg-purple-950/40 dark:text-purple-300 dark:border-purple-800">● PO Issued</span>`;
            case 'In Production':
                return `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-sky-50 text-sky-700 border border-sky-200 dark:bg-sky-950/40 dark:text-sky-300 dark:border-sky-800">● In Production</span>`;
            case 'Completed':
                return `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800">● Completed</span>`;
            default:
                return `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-zinc-100 text-zinc-700 border border-zinc-200">${stage}</span>`;
        }
    }

    // Filter Logic
    function getFilteredDeals() {
        let list = state.deals;

        // Stage filter
        if (state.activeStage && state.activeStage !== 'all') {
            list = list.filter(d => d.stage === state.activeStage);
        }

        // Search term
        if (state.searchTerm.trim()) {
            const q = state.searchTerm.toLowerCase();
            list = list.filter(d => {
                const poMatch = d.po_number && d.po_number.toLowerCase().includes(q);
                const titleMatch = d.title && d.title.toLowerCase().includes(q);
                const vendorMatch = d.vendor_name && d.vendor_name.toLowerCase().includes(q);
                const stageMatch = d.stage && d.stage.toLowerCase().includes(q);
                const notesMatch = d.notes && d.notes.toLowerCase().includes(q);
                return poMatch || titleMatch || vendorMatch || stageMatch || notesMatch;
            });
        }

        // Advanced filter rules
        if (state.activeFilters.length > 0) {
            list = list.filter(deal => {
                return state.activeFilters.every(rule => {
                    const rawVal = deal[rule.field];
                    const targetVal = rule.value;

                    if (rawVal === undefined || rawVal === null) {
                        return rule.operator === 'is_empty';
                    }

                    const strRaw = String(rawVal).toLowerCase();
                    const strTarget = String(targetVal || '').toLowerCase();

                    switch (rule.operator) {
                        case 'equals':
                            return strRaw === strTarget;
                        case 'not_equals':
                            return strRaw !== strTarget;
                        case 'contains':
                            return strRaw.includes(strTarget);
                        case 'not_contains':
                            return !strRaw.includes(strTarget);
                        case 'starts_with':
                            return strRaw.startsWith(strTarget);
                        case 'ends_with':
                            return strRaw.endsWith(strTarget);
                        case 'greater_than':
                            return Number(rawVal) > Number(targetVal);
                        case 'less_than':
                            return Number(rawVal) < Number(targetVal);
                        case 'is_empty':
                            return !rawVal || strRaw === '';
                        case 'is_not_empty':
                            return Boolean(rawVal) && strRaw !== '';
                        default:
                            return true;
                    }
                });
            });
        }

        return list;
    }

    // Render Table View
    function renderTableView() {
        const theadRow = document.getElementById('po-table-header-row');
        const tbody = document.getElementById('po-table-tbody');
        const emptyState = document.getElementById('po-empty-state');
        const summary = document.getElementById('po-table-summary');
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
                <input type="checkbox" onchange="window.toggleSelectAllDeals(this.checked)" class="rounded text-indigo-600 focus:ring-indigo-500 cursor-pointer">
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
        const deals = getFilteredDeals();

        if (summary) {
            summary.innerText = `Showing ${deals.length} of ${state.deals.length} purchase orders`;
        }

        if (deals.length === 0) {
            tbody.innerHTML = '';
            if (emptyState) emptyState.classList.remove('hidden');
            return;
        }

        if (emptyState) emptyState.classList.add('hidden');

        let rowsHtml = '';
        deals.forEach(deal => {
            rowsHtml += `
                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors group">
                    <td class="py-3 px-4 text-center">
                        <input type="checkbox" data-deal-id="${deal.id}" class="rounded text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                    </td>
            `;

            activeCols.forEach(colKey => {
                const def = defMap.get(colKey);
                let cellVal = deal[colKey];

                if (colKey === 'po_number') {
                    rowsHtml += `
                        <td class="py-3 px-4 font-semibold text-zinc-900 dark:text-zinc-100">
                            <a href="/purchase-deals/${deal.id}" class="hover:text-indigo-600 no-underline hover:no-underline flex items-center gap-2">
                                <span class="font-mono font-bold text-[11px] px-2 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-200 border border-zinc-200 dark:border-zinc-700">
                                    ${cellVal || 'PO-DRAFT'}
                                </span>
                                <span class="font-semibold text-zinc-900 dark:text-zinc-100 hover:text-indigo-600 truncate max-w-[200px]" title="${deal.title}">
                                    ${deal.title || 'Untitled Deal'}
                                </span>
                            </a>
                        </td>
                    `;
                } else if (colKey === 'title') {
                    rowsHtml += `
                        <td class="py-3 px-4 font-semibold text-zinc-900 dark:text-zinc-100">
                            <a href="/purchase-deals/${deal.id}" class="hover:text-indigo-600 no-underline hover:no-underline">
                                ${cellVal || 'Untitled Deal'}
                            </a>
                        </td>
                    `;
                } else if (colKey === 'vendor_name' || colKey === 'account_name') {
                    rowsHtml += `
                        <td class="py-3 px-4 text-zinc-600 dark:text-zinc-300 font-medium">
                            <span class="flex items-center gap-1.5">
                                <i data-lucide="building-2" class="w-3.5 h-3.5 text-zinc-400"></i>
                                <span>${cellVal || 'Unlinked Vendor'}</span>
                            </span>
                        </td>
                    `;
                } else if (colKey === 'amount') {
                    rowsHtml += `
                        <td class="py-3 px-4 font-bold text-zinc-950 dark:text-zinc-100 text-sm">
                            ${formatCurrency(cellVal, deal.currency || 'USD')}
                        </td>
                    `;
                } else if (colKey === 'stage') {
                    rowsHtml += `<td class="py-3 px-4">${getStageBadge(cellVal)}</td>`;
                } else if (colKey === 'expected_delivery_date') {
                    rowsHtml += `
                        <td class="py-3 px-4 text-zinc-600 dark:text-zinc-300 font-medium">
                            <span class="flex items-center gap-1.5">
                                <i data-lucide="calendar" class="w-3.5 h-3.5 text-zinc-400"></i>
                                <span>${deal.expected_delivery_date_formatted || cellVal || 'Pending'}</span>
                            </span>
                        </td>
                    `;
                } else if (colKey === 'payment_terms') {
                    rowsHtml += `
                        <td class="py-3 px-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-zinc-100 text-zinc-800 border border-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:border-zinc-700">
                                ${cellVal || 'Net 30'}
                            </span>
                        </td>
                    `;
                } else if (colKey === 'owner_name' || colKey === 'assigned_to_name') {
                    rowsHtml += `
                        <td class="py-3 px-4 text-zinc-600 dark:text-zinc-300">
                            ${cellVal ? `<span class="flex items-center gap-1.5"><i data-lucide="user" class="w-3.5 h-3.5 text-zinc-400"></i> ${cellVal}</span>` : '<span class="text-zinc-400 italic">Unassigned</span>'}
                        </td>
                    `;
                } else {
                    // Custom Field or fallback
                    rowsHtml += `
                        <td class="py-3 px-4 text-zinc-600 dark:text-zinc-300 font-medium">
                            ${cellVal ? String(cellVal) : '<span class="text-zinc-400 italic">—</span>'}
                        </td>
                    `;
                }
            });

            // Actions Cell
            rowsHtml += `
                <td class="py-3 px-4 text-right">
                    <div class="flex items-center justify-end gap-1.5 opacity-90 group-hover:opacity-100 transition-opacity">
                        <a
                            href="/purchase-deals/${deal.id}"
                            class="p-1 rounded text-zinc-500 hover:text-indigo-600 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors"
                            title="View Deal 360"
                        >
                            <i data-lucide="eye" class="w-4 h-4"></i>
                        </a>
                        <button
                            type="button"
                            onclick="window.openPurchaseDealModal('edit', ${deal.id})"
                            class="p-1 rounded text-zinc-500 hover:text-indigo-600 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
                            title="Edit"
                        >
                            <i data-lucide="pencil" class="w-4 h-4"></i>
                        </button>
                        <button
                            type="button"
                            onclick="window.openPurchaseDealTransferModal(${deal.id})"
                            class="p-1 rounded text-zinc-500 hover:text-indigo-600 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
                            title="Transfer Buyer"
                        >
                            <i data-lucide="user-check" class="w-4 h-4"></i>
                        </button>
                        <button
                            type="button"
                            onclick="window.deletePurchaseDealRecord(${deal.id})"
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
        const board = document.getElementById('po-kanban-board');
        if (!board) return;

        const deals = getFilteredDeals();

        let boardHtml = '';

        state.stages.forEach(stage => {
            const stageDeals = deals.filter(d => d.stage === stage);
            const stageTotal = stageDeals.reduce((sum, d) => sum + Number(d.amount || 0), 0);

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
                                ${stageDeals.length}
                            </span>
                        </div>
                        <div class="text-xs font-bold text-zinc-900 dark:text-zinc-100">
                            ${formatCurrency(stageTotal)}
                        </div>
                    </div>

                    <!-- Cards Container -->
                    <div class="space-y-2.5 flex-1 kanban-cards-wrapper overflow-y-auto max-h-[70vh]">
            `;

            if (stageDeals.length === 0) {
                boardHtml += `
                    <div class="py-8 text-center text-zinc-400 text-xs italic border border-dashed border-zinc-200 dark:border-zinc-800 rounded-lg">
                        No orders in ${stage}
                    </div>
                `;
            } else {
                stageDeals.forEach(deal => {
                    boardHtml += `
                        <div
                            class="bg-white dark:bg-zinc-800 rounded-xl p-3.5 border border-zinc-200 dark:border-zinc-700 shadow-2xs hover:shadow-md transition-all cursor-grab active:cursor-grabbing space-y-2.5"
                            draggable="true"
                            ondragstart="window.handleKanbanDragStart(event, ${deal.id})"
                        >
                            <div class="flex items-start justify-between gap-2">
                                <span class="font-mono font-bold text-[10px] px-2 py-0.5 rounded bg-zinc-100 dark:bg-zinc-700 text-zinc-800 dark:text-zinc-200 border border-zinc-200 dark:border-zinc-600 shrink-0">
                                    ${deal.po_number || 'PO-DRAFT'}
                                </span>
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-zinc-100 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300">
                                    ${deal.payment_terms || 'Net 30'}
                                </span>
                            </div>

                            <a href="/purchase-deals/${deal.id}" class="text-xs font-bold text-zinc-950 dark:text-zinc-100 hover:text-indigo-600 no-underline hover:no-underline line-clamp-2 leading-snug">
                                ${deal.title}
                            </a>

                            <div class="text-[11px] text-zinc-500 flex items-center gap-1">
                                <i data-lucide="building-2" class="w-3 h-3 text-zinc-400"></i>
                                <span>Supplier: <strong class="text-zinc-800 dark:text-zinc-200">${deal.vendor_name || deal.account_name || 'Unlinked'}</strong></span>
                            </div>

                            ${deal.expected_delivery_date ? `
                                <div class="text-[11px] text-zinc-500 flex items-center gap-1">
                                    <i data-lucide="calendar" class="w-3 h-3 text-zinc-400"></i>
                                    <span>Target: <strong class="text-zinc-700 dark:text-zinc-300">${deal.expected_delivery_date_formatted || deal.expected_delivery_date}</strong></span>
                                </div>
                            ` : ''}

                            <div class="flex items-center justify-between pt-1 border-t border-zinc-100 dark:border-zinc-700/60">
                                <span class="text-sm font-extrabold text-zinc-950 dark:text-zinc-100">
                                    ${formatCurrency(deal.amount, deal.currency || 'USD')}
                                </span>
                                <span class="text-[10px] text-zinc-400 flex items-center gap-1">
                                    <i data-lucide="user" class="w-3 h-3"></i>
                                    <span>${deal.owner_name || 'Unassigned'}</span>
                                </span>
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

    // Update KPI Header Cards
    function updateKpiCards() {
        const deals = state.deals;
        const totalCommitted = deals.reduce((sum, d) => sum + Number(d.amount || 0), 0);
        const inProduction = deals.filter(d => d.stage === 'In Production');
        const productionTotal = inProduction.reduce((sum, d) => sum + Number(d.amount || 0), 0);
        const delivered = deals.filter(d => d.stage === 'Completed');
        const deliveredTotal = delivered.reduce((sum, d) => sum + Number(d.amount || 0), 0);

        const elCommitted = document.getElementById('kpi-committed-spend');
        const elCommittedCount = document.getElementById('kpi-committed-count');
        const elProduction = document.getElementById('kpi-production-spend');
        const elProductionCount = document.getElementById('kpi-production-count');
        const elDelivered = document.getElementById('kpi-delivered-spend');
        const elDeliveredCount = document.getElementById('kpi-delivered-count');

        if (elCommitted) elCommitted.innerText = formatCurrency(totalCommitted);
        if (elCommittedCount) elCommittedCount.innerText = `${deals.length} Orders`;
        if (elProduction) elProduction.innerText = formatCurrency(productionTotal);
        if (elProductionCount) elProductionCount.innerText = `${inProduction.length} Orders`;
        if (elDelivered) elDelivered.innerText = formatCurrency(deliveredTotal);
        if (elDeliveredCount) elDeliveredCount.innerText = `${delivered.length} Completed`;

        // Update stage pill counters
        const countAll = document.getElementById('count-stage-all');
        if (countAll) countAll.innerText = deals.length;

        state.stages.forEach(st => {
            const slug = st.toLowerCase().replace(/[^a-z0-9]/g, '-');
            const el = document.getElementById(`count-stage-${slug}`);
            if (el) {
                el.innerText = deals.filter(d => d.stage === st).length;
            }
        });
    }

    // Switch View (Table vs Kanban)
    window.switchPurchaseDealView = function (mode) {
        state.viewMode = mode;
        const btnTable = document.getElementById('btn-view-table');
        const btnKanban = document.getElementById('btn-view-kanban');
        const viewTable = document.getElementById('view-table-container');
        const viewKanban = document.getElementById('view-kanban-container');

        if (mode === 'table') {
            btnTable.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-md transition-all cursor-pointer bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white shadow-2xs';
            btnKanban.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-md transition-all cursor-pointer text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white';
            viewTable.classList.remove('hidden');
            viewKanban.classList.add('hidden');
            renderTableView();
        } else {
            btnKanban.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-md transition-all cursor-pointer bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white shadow-2xs';
            btnTable.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-md transition-all cursor-pointer text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white';
            viewKanban.classList.remove('hidden');
            viewTable.classList.add('hidden');
            renderKanbanView();
        }
        if (window.lucide) lucide.createIcons();
    };

    // Stage Pill Filter Click
    window.filterByStage = function (stage) {
        state.activeStage = stage;
        document.querySelectorAll('.po-stage-pill').forEach(btn => {
            const btnStage = btn.getAttribute('data-stage');
            if (btnStage === stage) {
                btn.className = 'po-stage-pill px-3 py-1 rounded-full font-semibold transition-all cursor-pointer bg-zinc-900 dark:bg-zinc-100 text-white dark:text-zinc-900 shadow-xs';
            } else {
                btn.className = 'po-stage-pill px-3 py-1 rounded-full font-medium transition-all cursor-pointer bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700';
            }
        });

        if (state.viewMode === 'table') {
            renderTableView();
        } else {
            renderKanbanView();
        }
    };

    // Live Search Input Handler
    window.handleSearchInput = function (val) {
        state.searchTerm = val;
        if (state.viewMode === 'table') {
            renderTableView();
        } else {
            renderKanbanView();
        }
    };

    // Kanban Drag and Drop
    window.handleKanbanDragStart = function (e, dealId) {
        e.dataTransfer.setData('text/plain', String(dealId));
        e.dataTransfer.effectAllowed = 'move';
    };

    window.handleKanbanDragOver = function (e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
    };

    window.handleKanbanDrop = async function (e, targetStage) {
        e.preventDefault();
        const dealId = e.dataTransfer.getData('text/plain');
        if (!dealId) return;

        const deal = state.deals.find(d => String(d.id) === String(dealId));
        if (!deal || deal.stage === targetStage) return;

        const oldStage = deal.stage;
        deal.stage = targetStage;
        renderKanbanView();
        updateKpiCards();

        try {
            const res = await fetch(`/api/purchase-deals/${dealId}/stage`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify({ stage: targetStage }),
            });
            const data = await res.json();
            if (data.success) {
                showNotification(`Order ${deal.po_number} moved to ${targetStage}!`);
                if (data.purchase_deal) {
                    const idx = state.deals.findIndex(d => String(d.id) === String(dealId));
                    if (idx !== -1) state.deals[idx] = data.purchase_deal;
                }
            } else {
                deal.stage = oldStage;
                renderKanbanView();
                updateKpiCards();
                showNotification(data.message || 'Failed to update stage', 'error');
            }
        } catch (err) {
            console.error('Error updating stage:', err);
            deal.stage = oldStage;
            renderKanbanView();
            updateKpiCards();
            showNotification('Error updating procurement stage', 'error');
        }
    };

    // Modal: Open Create / Edit PO Deal
    window.openPurchaseDealModal = function (mode, dealId = null) {
        state.isCreateMode = (mode === 'create');
        const modal = document.getElementById('purchase-deal-modal');
        const heading = document.getElementById('modal-deal-heading');
        const form = document.getElementById('purchase-deal-form');
        const customContainer = document.getElementById('modal-custom-fields-container');
        if (!modal || !form) return;

        form.reset();
        document.getElementById('deal-id').value = '';

        let deal = null;
        if (dealId) {
            deal = state.deals.find(d => d.id === dealId);
        }

        if (state.isCreateMode || !deal) {
            heading.innerText = 'Create Purchase Order Deal';
            // Auto generate preview PO number
            const year = new Date().getFullYear();
            const rand = Math.floor(1000 + Math.random() * 9000);
            document.getElementById('field-po_number').value = `PO-${year}-${rand}`;
            document.getElementById('field-currency').value = 'USD';
            document.getElementById('field-stage').value = 'Draft';
            document.getElementById('field-payment_terms').value = 'Net 30';
        } else {
            heading.innerText = `Edit Purchase Deal (${deal.po_number})`;
            document.getElementById('deal-id').value = deal.id;
            document.getElementById('field-po_number').value = deal.po_number || '';
            document.getElementById('field-title').value = deal.title || '';
            document.getElementById('field-account_id').value = deal.account_id || '';
            document.getElementById('field-contact_id').value = deal.contact_id || '';
            document.getElementById('field-amount').value = deal.amount || '';
            document.getElementById('field-currency').value = deal.currency || 'USD';
            document.getElementById('field-stage').value = deal.stage || 'Draft';
            document.getElementById('field-expected_delivery_date').value = deal.expected_delivery_date ? deal.expected_delivery_date.split('T')[0] : '';
            document.getElementById('field-payment_terms').value = deal.payment_terms || 'Net 30';
            document.getElementById('field-notes').value = deal.notes || '';
            document.getElementById('field-assigned_to').value = deal.assigned_to || '';

            // Backed Opportunity
            if (deal.linked_opportunities && deal.linked_opportunities.length > 0) {
                document.getElementById('field-opportunity_id').value = deal.linked_opportunities[0].id;
            }
        }

        // Render Dynamic Custom Fields with Permissions
        if (customContainer) {
            let customHtml = '';
            state.customFields.forEach(cf => {
                const perm = getPermission(cf.field_name);
                if (perm === 'Hidden') return;

                const isReadOnly = (perm === 'Read-Only');
                const fieldVal = deal ? (deal[cf.field_name] || (deal.custom_data && deal.custom_data[cf.field_name]) || '') : '';

                customHtml += `
                    <div>
                        <label class="block text-xs font-semibold text-zinc-800 dark:text-zinc-200 mb-1">
                            ${cf.field_label}
                            ${cf.is_required ? '<span class="text-red-500">*</span>' : ''}
                            ${isReadOnly ? '<span class="ml-1 px-1.5 py-0.2 rounded text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">Read-Only</span>' : ''}
                        </label>
                `;

                if (cf.data_type === 'picklist') {
                    const opts = cf.options ? cf.options.split(',').map(s => s.trim()) : [];
                    customHtml += `
                        <select
                            name="${cf.field_name}"
                            ${isReadOnly ? 'disabled' : ''}
                            ${cf.is_required ? 'required' : ''}
                            class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 ${isReadOnly ? 'opacity-60 cursor-not-allowed' : ''}"
                        >
                            <option value="">Select ${cf.field_label}...</option>
                            ${opts.map(opt => `<option value="${opt}" ${String(fieldVal) === String(opt) ? 'selected' : ''}>${opt}</option>`).join('')}
                        </select>
                    `;
                } else if (cf.data_type === 'number') {
                    customHtml += `
                        <input
                            type="number"
                            step="any"
                            name="${cf.field_name}"
                            value="${fieldVal}"
                            ${isReadOnly ? 'disabled' : ''}
                            ${cf.is_required ? 'required' : ''}
                            placeholder="Enter ${cf.field_label}"
                            class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 ${isReadOnly ? 'opacity-60 cursor-not-allowed' : ''}"
                        >
                    `;
                } else if (cf.data_type === 'date') {
                    customHtml += `
                        <input
                            type="date"
                            name="${cf.field_name}"
                            value="${fieldVal ? fieldVal.split('T')[0] : ''}"
                            ${isReadOnly ? 'disabled' : ''}
                            ${cf.is_required ? 'required' : ''}
                            class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 ${isReadOnly ? 'opacity-60 cursor-not-allowed' : ''}"
                        >
                    `;
                } else if (cf.data_type === 'textarea') {
                    customHtml += `
                        <textarea
                            name="${cf.field_name}"
                            rows="2"
                            ${isReadOnly ? 'disabled' : ''}
                            ${cf.is_required ? 'required' : ''}
                            placeholder="Enter ${cf.field_label}"
                            class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 ${isReadOnly ? 'opacity-60 cursor-not-allowed' : ''}"
                        >${fieldVal}</textarea>
                    `;
                } else {
                    customHtml += `
                        <input
                            type="text"
                            name="${cf.field_name}"
                            value="${fieldVal}"
                            ${isReadOnly ? 'disabled' : ''}
                            ${cf.is_required ? 'required' : ''}
                            placeholder="Enter ${cf.field_label}"
                            class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 ${isReadOnly ? 'opacity-60 cursor-not-allowed' : ''}"
                        >
                    `;
                }

                if (cf.help_text) {
                    customHtml += `<p class="text-[10px] text-zinc-400 mt-0.5">${cf.help_text}</p>`;
                }
                customHtml += `</div>`;
            });

            customContainer.innerHTML = customHtml || '<p class="text-xs text-zinc-400 col-span-2 italic">No custom fields configured for Purchase Deals.</p>';
        }

        modal.classList.remove('hidden');
        if (window.lucide) lucide.createIcons();
    };

    window.closePurchaseDealModal = function () {
        const modal = document.getElementById('purchase-deal-modal');
        if (modal) modal.classList.add('hidden');
    };

    // Modal: Submit Form Handler
    window.handlePurchaseDealSubmit = async function (e) {
        e.preventDefault();
        const dealId = document.getElementById('deal-id').value;
        const isEdit = Boolean(dealId);

        const payload = {
            po_number: document.getElementById('field-po_number').value,
            title: document.getElementById('field-title').value,
            account_id: document.getElementById('field-account_id').value,
            contact_id: document.getElementById('field-contact_id').value || null,
            opportunity_id: document.getElementById('field-opportunity_id').value || null,
            amount: document.getElementById('field-amount').value,
            currency: document.getElementById('field-currency').value,
            stage: document.getElementById('field-stage').value,
            expected_delivery_date: document.getElementById('field-expected_delivery_date').value || null,
            payment_terms: document.getElementById('field-payment_terms').value,
            notes: document.getElementById('field-notes').value,
            assigned_to: document.getElementById('field-assigned_to').value || null,
            custom_data: {},
        };

        // Collect custom fields
        state.customFields.forEach(cf => {
            const perm = getPermission(cf.field_name);
            if (perm === 'Read-Only' || perm === 'Hidden') return;

            const input = document.querySelector(`[name="${cf.field_name}"]`);
            if (input) {
                payload[cf.field_name] = input.value;
                payload.custom_data[cf.field_name] = input.value;
            }
        });

        const btnSave = document.getElementById('btn-save-deal');
        if (btnSave) {
            btnSave.disabled = true;
            btnSave.innerText = 'Saving...';
        }

        try {
            const url = isEdit ? `/api/purchase-deals/${dealId}` : '/api/purchase-deals';
            const method = isEdit ? 'PUT' : 'POST';

            const res = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify(payload),
            });

            const data = await res.json();
            if (data.success) {
                showNotification(data.message || 'Purchase deal saved successfully!');
                window.closePurchaseDealModal();

                if (isEdit) {
                    const idx = state.deals.findIndex(d => String(d.id) === String(dealId));
                    if (idx !== -1) state.deals[idx] = data.purchase_deal;
                } else {
                    state.deals.unshift(data.purchase_deal);
                }

                updateKpiCards();
                if (state.viewMode === 'table') {
                    renderTableView();
                } else {
                    renderKanbanView();
                }
            } else {
                showNotification(data.message || 'Failed to save purchase deal', 'error');
            }
        } catch (err) {
            console.error('Error saving deal:', err);
            showNotification('Error saving purchase deal record', 'error');
        } finally {
            if (btnSave) {
                btnSave.disabled = false;
                btnSave.innerText = 'Save Purchase Deal';
            }
        }
    };

    // Delete Record
    window.deletePurchaseDealRecord = async function (dealId) {
        const deal = state.deals.find(d => d.id === dealId);
        if (!deal) return;

        if (!confirm(`Are you sure you want to delete purchase order ${deal.po_number}?`)) {
            return;
        }

        try {
            const res = await fetch(`/api/purchase-deals/${dealId}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
            });
            const data = await res.json();
            if (data.success) {
                showNotification(data.message || `PO ${deal.po_number} deleted.`);
                state.deals = state.deals.filter(d => d.id !== dealId);
                updateKpiCards();
                if (state.viewMode === 'table') {
                    renderTableView();
                } else {
                    renderKanbanView();
                }
            } else {
                showNotification(data.message || 'Failed to delete record', 'error');
            }
        } catch (err) {
            console.error('Error deleting deal:', err);
            showNotification('Error deleting purchase deal', 'error');
        }
    };

    // Transfer Owner Modal
    window.openPurchaseDealTransferModal = function (dealId) {
        const deal = state.deals.find(d => d.id === dealId);
        if (!deal) return;

        document.getElementById('transfer-deal-id').value = deal.id;
        document.getElementById('transfer-deal-name-label').innerText =
            `Transfer ownership of PO ${deal.po_number} (${deal.title}). Current Buyer: ${deal.owner_name || 'Unassigned'}.`;

        const modal = document.getElementById('po-transfer-modal');
        if (modal) modal.classList.remove('hidden');
        if (window.lucide) lucide.createIcons();
    };

    window.closePurchaseDealTransferModal = function () {
        const modal = document.getElementById('po-transfer-modal');
        if (modal) modal.classList.add('hidden');
    };

    window.submitPurchaseDealTransfer = async function () {
        const dealId = document.getElementById('transfer-deal-id').value;
        const newOwnerId = document.getElementById('transfer-new-owner-id').value;

        try {
            const res = await fetch(`/api/purchase-deals/${dealId}/transfer`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify({ assigned_to: newOwnerId }),
            });
            const data = await res.json();
            if (data.success) {
                showNotification(data.message || 'Owner reassigned successfully!');
                window.closePurchaseDealTransferModal();
                if (data.purchase_deal) {
                    const idx = state.deals.findIndex(d => String(d.id) === String(dealId));
                    if (idx !== -1) state.deals[idx] = data.purchase_deal;
                }
                if (state.viewMode === 'table') {
                    renderTableView();
                } else {
                    renderKanbanView();
                }
            } else {
                showNotification(data.message || 'Transfer failed', 'error');
            }
        } catch (err) {
            console.error('Error transferring deal:', err);
            showNotification('Error transferring purchase deal', 'error');
        }
    };

    // Visible Fields Config Modal
    window.openPurchaseDealConfigModal = function () {
        const modal = document.getElementById('po-config-modal');
        const list = document.getElementById('config-fields-list');
        if (!modal || !list) return;

        const visibleDefs = getVisibleFieldDefinitions();
        const activeFields = (state.viewMode === 'table') ? state.tableVisibleFields : state.kanbanVisibleFields;

        let html = '';
        visibleDefs.forEach(f => {
            const isChecked = activeFields.includes(f.name);
            html += `
                <label class="flex items-center gap-2 p-2 rounded-lg hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors cursor-pointer text-xs">
                    <input
                        type="checkbox"
                        value="${f.name}"
                        ${isChecked ? 'checked' : ''}
                        class="config-field-cb rounded text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                    >
                    <span class="font-medium text-zinc-800 dark:text-zinc-200">${f.label}</span>
                    ${f.isCustom ? '<span class="ml-auto text-[10px] text-zinc-400 font-normal">Custom</span>' : ''}
                </label>
            `;
        });

        list.innerHTML = html;
        modal.classList.remove('hidden');
        if (window.lucide) lucide.createIcons();
    };

    window.closePurchaseDealConfigModal = function () {
        const modal = document.getElementById('po-config-modal');
        if (modal) modal.classList.add('hidden');
    };

    window.saveVisibleFieldsConfig = function () {
        const checkboxes = document.querySelectorAll('.config-field-cb:checked');
        const selectedKeys = Array.from(checkboxes).map(cb => cb.value);

        if (selectedKeys.length === 0) {
            showNotification('Please select at least one field to display.', 'error');
            return;
        }

        if (state.viewMode === 'table') {
            state.tableVisibleFields = selectedKeys;
            renderTableView();
        } else {
            state.kanbanVisibleFields = selectedKeys;
            renderKanbanView();
        }

        window.closePurchaseDealConfigModal();
        showNotification('View layout customized successfully!');
    };

    window.resetDefaultVisibleFields = function () {
        state.tableVisibleFields = ['po_number', 'vendor_name', 'amount', 'stage', 'expected_delivery_date', 'payment_terms'];
        state.kanbanVisibleFields = ['vendor_name', 'amount', 'expected_delivery_date', 'payment_terms'];
        window.closePurchaseDealConfigModal();
        if (state.viewMode === 'table') {
            renderTableView();
        } else {
            renderKanbanView();
        }
        showNotification('Reset to default field configuration.');
    };

    // Universal Filter Modal
    window.openPurchaseDealFilterModal = function () {
        const modal = document.getElementById('po-filter-modal');
        const rulesList = document.getElementById('filter-rules-list');
        if (!modal || !rulesList) return;

        if (state.activeFilters.length === 0) {
            state.activeFilters = [{
                id: Date.now(),
                field: 'stage',
                operator: 'equals',
                value: 'PO Issued',
            }];
        }

        renderFilterModalRules();
        modal.classList.remove('hidden');
        if (window.lucide) lucide.createIcons();
    };

    window.closePurchaseDealFilterModal = function () {
        const modal = document.getElementById('po-filter-modal');
        if (modal) modal.classList.add('hidden');
    };

    function renderFilterModalRules() {
        const container = document.getElementById('filter-rules-list');
        if (!container) return;

        const visibleDefs = getVisibleFieldDefinitions();

        let html = '';
        state.activeFilters.forEach((rule, idx) => {
            html += `
                <div class="flex items-center gap-2 p-2.5 rounded-lg border border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-800/50">
                    <select
                        onchange="window.updateFilterRule(${rule.id}, 'field', this.value)"
                        class="text-xs px-2 py-1.5 rounded-md border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 flex-1"
                    >
                        ${visibleDefs.map(d => `<option value="${d.name}" ${d.name === rule.field ? 'selected' : ''}>${d.label}</option>`).join('')}
                    </select>

                    <select
                        onchange="window.updateFilterRule(${rule.id}, 'operator', this.value)"
                        class="text-xs px-2 py-1.5 rounded-md border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 w-32"
                    >
                        <option value="equals" ${rule.operator === 'equals' ? 'selected' : ''}>Equals</option>
                        <option value="not_equals" ${rule.operator === 'not_equals' ? 'selected' : ''}>Not Equals</option>
                        <option value="contains" ${rule.operator === 'contains' ? 'selected' : ''}>Contains</option>
                        <option value="greater_than" ${rule.operator === 'greater_than' ? 'selected' : ''}>Greater Than</option>
                        <option value="less_than" ${rule.operator === 'less_than' ? 'selected' : ''}>Less Than</option>
                        <option value="is_not_empty" ${rule.operator === 'is_not_empty' ? 'selected' : ''}>Has Value</option>
                    </select>

                    <input
                        type="text"
                        value="${rule.value || ''}"
                        placeholder="Value..."
                        oninput="window.updateFilterRule(${rule.id}, 'value', this.value)"
                        class="text-xs px-2 py-1.5 rounded-md border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 flex-1"
                    >

                    <button
                        type="button"
                        onclick="window.removeFilterRuleRow(${rule.id})"
                        class="p-1 rounded text-zinc-400 hover:text-red-600 transition-colors cursor-pointer"
                        title="Remove rule"
                    >
                        <i data-lucide="trash" class="w-3.5 h-3.5"></i>
                    </button>
                </div>
            `;
        });

        container.innerHTML = html;
        if (window.lucide) lucide.createIcons();
    }

    window.addFilterRuleRow = function () {
        const visibleDefs = getVisibleFieldDefinitions();
        const firstField = visibleDefs.length > 0 ? visibleDefs[0].name : 'stage';
        state.activeFilters.push({
            id: Date.now(),
            field: firstField,
            operator: 'contains',
            value: '',
        });
        renderFilterModalRules();
    };

    window.updateFilterRule = function (ruleId, key, val) {
        const r = state.activeFilters.find(x => x.id === ruleId);
        if (r) {
            r[key] = val;
        }
    };

    window.removeFilterRuleRow = function (ruleId) {
        state.activeFilters = state.activeFilters.filter(x => x.id !== ruleId);
        renderFilterModalRules();
    };

    window.applyFilterRules = function () {
        window.closePurchaseDealFilterModal();
        updateActiveFilterChips();
        if (state.viewMode === 'table') {
            renderTableView();
        } else {
            renderKanbanView();
        }
        showNotification(`Applied ${state.activeFilters.length} filter condition(s).`);
    };

    window.clearAllFilters = function () {
        state.activeFilters = [];
        updateActiveFilterChips();
        if (state.viewMode === 'table') {
            renderTableView();
        } else {
            renderKanbanView();
        }
        window.closePurchaseDealFilterModal();
        showNotification('All filters cleared.');
    };

    function updateActiveFilterChips() {
        const container = document.getElementById('active-filter-chips-container');
        const chipsList = document.getElementById('filter-chips-list');
        const badge = document.getElementById('po-filter-badge');
        if (!container || !chipsList) return;

        if (state.activeFilters.length === 0) {
            container.classList.add('hidden');
            if (badge) badge.classList.add('hidden');
            return;
        }

        container.classList.remove('hidden');
        if (badge) {
            badge.classList.remove('hidden');
            badge.innerText = state.activeFilters.length;
        }

        const visibleDefs = getVisibleFieldDefinitions();
        const defMap = new Map(visibleDefs.map(d => [d.name, d]));

        let html = '';
        state.activeFilters.forEach(rule => {
            const def = defMap.get(rule.field);
            const label = def ? def.label : rule.field;
            html += `
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-900 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800">
                    <strong>${label}</strong>
                    <span class="text-amber-600 dark:text-amber-400 font-semibold">${rule.operator}</span>
                    <span>"${rule.value || ''}"</span>
                    <button type="button" onclick="window.removeSingleFilterChip(${rule.id})" class="text-amber-600 hover:text-amber-900 cursor-pointer">
                        <i data-lucide="x" class="w-3 h-3"></i>
                    </button>
                </span>
            `;
        });

        chipsList.innerHTML = html;
        if (window.lucide) lucide.createIcons();
    }

    window.removeSingleFilterChip = function (ruleId) {
        state.activeFilters = state.activeFilters.filter(x => x.id !== ruleId);
        updateActiveFilterChips();
        if (state.viewMode === 'table') {
            renderTableView();
        } else {
            renderKanbanView();
        }
    };

    // CSV Export Action
    window.exportPurchaseDealsCsv = function () {
        const deals = getFilteredDeals();
        if (deals.length === 0) {
            showNotification('No purchase deals to export.', 'error');
            return;
        }

        const visibleDefs = getVisibleFieldDefinitions();
        const headers = ['PO Number', 'Title', 'Supplier', 'Amount', 'Currency', 'Stage', 'Target Delivery', 'Payment Terms', 'Assigned Buyer'];

        const rows = [headers.join(',')];

        deals.forEach(d => {
            const row = [
                `"${d.po_number || ''}"`,
                `"${(d.title || '').replace(/"/g, '""')}"`,
                `"${(d.vendor_name || d.account_name || '').replace(/"/g, '""')}"`,
                d.amount || 0,
                `"${d.currency || 'USD'}"`,
                `"${d.stage || ''}"`,
                `"${d.expected_delivery_date || ''}"`,
                `"${d.payment_terms || ''}"`,
                `"${(d.owner_name || '').replace(/"/g, '""')}"`,
            ];
            rows.push(row.join(','));
        });

        const blob = new Blob([rows.join('\n')], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `purchase_deals_ledger_${new Date().toISOString().split('T')[0]}.csv`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
        showNotification('Purchase deals exported to CSV!');
    };

    // Global Checkbox Selection
    window.toggleSelectAllDeals = function (checked) {
        document.querySelectorAll('#po-table-tbody input[type="checkbox"]').forEach(cb => {
            cb.checked = checked;
        });
    };

    // Main Module Initializer
    window.initPurchaseDealsModule = function () {
        const root = document.getElementById('purchase-deals-page-root');
        if (!root) return;

        // Hydrate from window.__PURCHASE_DEALS_DATA__
        if (window.__PURCHASE_DEALS_DATA__) {
            const data = window.__PURCHASE_DEALS_DATA__;
            state.deals = data.initialPurchaseDeals || [];
            state.customFields = data.customFields || [];
            state.fieldPermissions = data.fieldPermissions || {};
            state.accounts = data.accounts || [];
            state.contacts = data.contacts || [];
            state.opportunities = data.opportunities || [];
            state.users = data.users || [];
            if (data.stages && data.stages.length > 0) {
                state.stages = data.stages;
            }
        }

        updateKpiCards();
        renderTableView();
        updateActiveFilterChips();

        if (window.lucide) lucide.createIcons();
    };

    // Auto-boot if root is present on standard page load
    document.addEventListener('DOMContentLoaded', () => {
        window.initPurchaseDealsModule();
    });

    // In case DOM is already ready
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        window.initPurchaseDealsModule();
    }
})();
