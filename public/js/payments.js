/**
 * DealSync Enterprise CRM — Invoicing & Settlement Ledger Engine
 * 1:1 Parity with React Client (PaymentsView.tsx, CompactDataTable.tsx, UniversalKanban.tsx)
 * Pure Tailwind CSS styling with Accounts Receivable, Accounts Payable, Partial Settlements & Live Drag-and-Drop
 */

(function () {
    'use strict';

    // Module State
    const state = {
        payments: [],
        accounts: [],
        stages: ['Draft', 'Issued', 'Partially Paid', 'Paid', 'Overdue'],
        categories: ['Accounts Receivable', 'Accounts Payable'],
        fieldPermissions: {},
        categoryFilter: '', // '' | 'Accounts Receivable' | 'Accounts Payable'
        statusFilter: '',
        accountFilter: '',
        searchTerm: '',
        viewMode: 'table', // 'table' | 'kanban'
        tableVisibleFields: ['invoice_number', 'account_name', 'type', 'amount', 'paid_amount', 'status', 'due_date'],
        kanbanVisibleFields: ['account_name', 'amount', 'paid_amount', 'due_date'],
        recordingPaymentId: null,
        editingPaymentId: null,
        isLoading: false,
    };

    const ALL_FIELD_DEFINITIONS = [
        { key: 'invoice_number', label: 'Invoice Number' },
        { key: 'account_name', label: 'Billed Account' },
        { key: 'type', label: 'Category' },
        { key: 'amount', label: 'Invoice Total' },
        { key: 'paid_amount', label: 'Paid / Settled' },
        { key: 'balance', label: 'Balance Due' },
        { key: 'status', label: 'Settlement Status' },
        { key: 'due_date', label: 'Maturity Due Date' },
        { key: 'issue_date', label: 'Issue Date' },
        { key: 'notes', label: 'Terms / Notes' }
    ];

    // Load persisted column preferences
    try {
        const savedCols = localStorage.getItem('crm_visible_fields_payments_table');
        if (savedCols) {
            state.tableVisibleFields = JSON.parse(savedCols);
        }
    } catch (_) {}

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

    // Currency Formatter
    function formatCurrency(val, currency = 'USD') {
        const num = parseFloat(val) || 0;
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: currency || 'USD',
            maximumFractionDigits: 2,
            minimumFractionDigits: 2,
        }).format(num);
    }

    // Format Date (YYYY-MM-DD or readable)
    function formatDate(dateStr) {
        if (!dateStr) return '—';
        const d = new Date(dateStr);
        if (isNaN(d.getTime())) return dateStr;
        return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    }

    // Status Badge Component
    function getStatusBadge(status) {
        switch (status) {
            case 'Paid':
                return `<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    Paid
                </span>`;
            case 'Partially Paid':
                return `<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-50 text-purple-700 border border-purple-200 dark:bg-purple-950/40 dark:text-purple-300 dark:border-purple-800">
                    <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                    Partially Paid
                </span>`;
            case 'Issued':
                return `<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                    Issued
                </span>`;
            case 'Overdue':
                return `<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                    Overdue
                </span>`;
            case 'Draft':
            default:
                return `<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-zinc-100 text-zinc-700 border border-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:border-zinc-700">
                    <span class="w-1.5 h-1.5 rounded-full bg-zinc-400"></span>
                    Draft
                </span>`;
        }
    }

    // Category Badge Component
    function getCategoryBadge(type) {
        const isAr = (type === 'Accounts Receivable' || type === 'Receivable');
        if (isAr) {
            return `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200/80 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800">
                <i data-lucide="arrow-down-left" class="w-3 h-3 text-blue-600"></i>
                AR (Client)
            </span>`;
        } else {
            return `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-50 text-amber-800 border border-amber-200/80 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800">
                <i data-lucide="arrow-up-right" class="w-3 h-3 text-amber-600"></i>
                AP (Vendor)
            </span>`;
        }
    }

    // Filter payments array according to active search and filters
    function getFilteredPayments() {
        return state.payments.filter(item => {
            // Category Segment Filter
            if (state.categoryFilter) {
                const isAr = item.type === 'Accounts Receivable' || item.type === 'Receivable';
                if (state.categoryFilter === 'Accounts Receivable' && !isAr) return false;
                if (state.categoryFilter === 'Accounts Payable' && isAr) return false;
            }

            // Status Filter
            if (state.statusFilter && item.status !== state.statusFilter) {
                return false;
            }

            // Account Filter
            if (state.accountFilter && String(item.account_id) !== String(state.accountFilter)) {
                return false;
            }

            // Text Search
            if (state.searchTerm) {
                const term = state.searchTerm.toLowerCase();
                const num = (item.invoice_number || '').toLowerCase();
                const acc = (item.account_name || (item.account && item.account.name) || '').toLowerCase();
                const notes = (item.notes || '').toLowerCase();
                if (!num.includes(term) && !acc.includes(term) && !notes.includes(term)) {
                    return false;
                }
            }

            return true;
        });
    }

    // Live KPI Calculations & UI Updates
    function updateKpiCards() {
        let totalInvoiced = 0;
        let totalCollected = 0;
        let outstandingAR = 0;
        let outstandingAP = 0;
        let countAll = state.payments.length;
        let countAr = 0;
        let countAp = 0;

        state.payments.forEach(p => {
            const amt = parseFloat(p.amount) || 0;
            const paid = parseFloat(p.paid_amount) || 0;
            const bal = Math.max(0, amt - paid);
            const isAr = (p.type === 'Accounts Receivable' || p.type === 'Receivable');

            totalInvoiced += amt;
            totalCollected += paid;

            if (isAr) {
                outstandingAR += bal;
                countAr++;
            } else {
                outstandingAP += bal;
                countAp++;
            }
        });

        // Update KPI card numbers
        const kpiInv = document.getElementById('kpi-total-invoiced');
        const kpiCol = document.getElementById('kpi-total-collected');
        const kpiAr = document.getElementById('kpi-outstanding-ar');
        const kpiAp = document.getElementById('kpi-outstanding-ap');
        const kpiEntries = document.getElementById('kpi-entries-count');

        if (kpiInv) kpiInv.textContent = formatCurrency(totalInvoiced);
        if (kpiCol) kpiCol.textContent = formatCurrency(totalCollected);
        if (kpiAr) kpiAr.textContent = formatCurrency(outstandingAR);
        if (kpiAp) kpiAp.textContent = formatCurrency(outstandingAP);
        if (kpiEntries) kpiEntries.textContent = countAll;

        // Update category pills counters
        const cAll = document.getElementById('count-cat-all');
        const cAr = document.getElementById('count-cat-ar');
        const cAp = document.getElementById('count-cat-ap');

        if (cAll) cAll.textContent = countAll;
        if (cAr) cAr.textContent = countAr;
        if (cAp) cAp.textContent = countAp;
    }

    // ==========================================
    // VIEW 1: COMPACT TABLE VIEW RENDERER
    // ==========================================
    function renderTableView() {
        const theadRow = document.getElementById('pay-table-header-row');
        const tbody = document.getElementById('pay-table-tbody');
        const emptyState = document.getElementById('pay-empty-state-table');

        if (!theadRow || !tbody) return;

        const filtered = getFilteredPayments();

        // 1. Build Header Row based on visible fields
        let headerHtml = '';
        state.tableVisibleFields.forEach(fieldKey => {
            const def = ALL_FIELD_DEFINITIONS.find(d => d.key === fieldKey);
            const label = def ? def.label : fieldKey;
            let alignClass = 'text-left';
            if (fieldKey === 'amount' || fieldKey === 'paid_amount' || fieldKey === 'balance') {
                alignClass = 'text-right';
            }
            headerHtml += `<th class="px-4 py-3 ${alignClass}">${label}</th>`;
        });
        headerHtml += `<th class="px-4 py-3 text-right">Quick Actions</th>`;
        theadRow.innerHTML = headerHtml;

        // 2. Build Rows
        if (filtered.length === 0) {
            tbody.innerHTML = '';
            if (emptyState) emptyState.classList.remove('hidden');
            return;
        }

        if (emptyState) emptyState.classList.add('hidden');

        let rowsHtml = '';
        filtered.forEach(item => {
            const amt = parseFloat(item.amount) || 0;
            const paid = parseFloat(item.paid_amount) || 0;
            const bal = Math.max(0, amt - paid);
            const accName = item.account_name || (item.account && item.account.name) || 'Unknown Account';
            const curr = item.currency || 'USD';

            // Overdue check
            const isOverdue = item.status === 'Overdue' || (item.due_date && new Date(item.due_date) < new Date() && item.status !== 'Paid');

            rowsHtml += `<tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors border-b border-zinc-100 dark:border-zinc-800/60">`;

            state.tableVisibleFields.forEach(fieldKey => {
                switch (fieldKey) {
                    case 'invoice_number':
                        rowsHtml += `<td class="px-4 py-3 font-mono font-bold text-zinc-950 dark:text-zinc-100">
                            <button
                                type="button"
                                onclick="window.openQuickInvoiceModal('edit', '${item.id}')"
                                class="text-indigo-600 dark:text-indigo-400 font-bold hover:text-indigo-800 no-underline hover:no-underline cursor-pointer"
                            >
                                ${item.invoice_number || 'INV-000'}
                            </button>
                        </td>`;
                        break;
                    case 'account_name':
                        rowsHtml += `<td class="px-4 py-3 font-semibold text-zinc-900 dark:text-zinc-100">
                            <span class="truncate block max-w-xs">${accName}</span>
                        </td>`;
                        break;
                    case 'type':
                        rowsHtml += `<td class="px-4 py-3">${getCategoryBadge(item.type)}</td>`;
                        break;
                    case 'amount':
                        rowsHtml += `<td class="px-4 py-3 text-right font-bold text-zinc-950 dark:text-white">
                            ${formatCurrency(amt, curr)}
                        </td>`;
                        break;
                    case 'paid_amount':
                        rowsHtml += `<td class="px-4 py-3 text-right font-bold text-emerald-600 dark:text-emerald-400">
                            ${formatCurrency(paid, curr)}
                        </td>`;
                        break;
                    case 'balance':
                        rowsHtml += `<td class="px-4 py-3 text-right font-bold ${bal > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-zinc-400'}">
                            ${formatCurrency(bal, curr)}
                        </td>`;
                        break;
                    case 'status':
                        rowsHtml += `<td class="px-4 py-3">${getStatusBadge(item.status)}</td>`;
                        break;
                    case 'due_date':
                        rowsHtml += `<td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">
                            <div class="flex items-center gap-1.5 ${isOverdue ? 'text-rose-600 font-semibold' : ''}">
                                <i data-lucide="calendar" class="w-3.5 h-3.5 ${isOverdue ? 'text-rose-500' : 'text-zinc-400'}"></i>
                                <span>${formatDate(item.due_date)}</span>
                            </div>
                        </td>`;
                        break;
                    case 'issue_date':
                        rowsHtml += `<td class="px-4 py-3 text-zinc-500 dark:text-zinc-400">${formatDate(item.issue_date)}</td>`;
                        break;
                    case 'notes':
                        rowsHtml += `<td class="px-4 py-3 text-zinc-500 dark:text-zinc-400 truncate max-w-[200px]" title="${(item.notes || '').replace(/"/g, '&quot;')}">${item.notes || '—'}</td>`;
                        break;
                    default:
                        rowsHtml += `<td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">${item[fieldKey] || '—'}</td>`;
                        break;
                }
            });

            // Action Buttons
            rowsHtml += `<td class="px-4 py-3 text-right">
                <div class="inline-flex items-center gap-1 justify-end">
                    <button
                        type="button"
                        onclick="window.openRecordPaymentModal('${item.id}')"
                        class="p-1.5 rounded-lg text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 transition-colors cursor-pointer"
                        title="Record Payment Settlement"
                    >
                        <i data-lucide="dollar-sign" class="w-4 h-4"></i>
                    </button>
                    <button
                        type="button"
                        onclick="window.openQuickInvoiceModal('edit', '${item.id}')"
                        class="p-1.5 rounded-lg text-zinc-500 hover:text-zinc-900 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
                        title="Edit Invoice"
                    >
                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                    </button>
                    <button
                        type="button"
                        onclick="window.deletePayment('${item.id}')"
                        class="p-1.5 rounded-lg text-rose-500 hover:text-rose-700 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors cursor-pointer"
                        title="Delete Invoice"
                    >
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </div>
            </td>`;

            rowsHtml += `</tr>`;
        });

        tbody.innerHTML = rowsHtml;

        if (window.lucide) lucide.createIcons();
    }

    // ==========================================
    // VIEW 2: UNIVERSAL KANBAN VIEW RENDERER
    // ==========================================
    function renderKanbanView() {
        const board = document.getElementById('pay-kanban-board');
        if (!board) return;

        const filtered = getFilteredPayments();

        const stageColors = {
            'Draft': { header: 'bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 border-zinc-300', dot: 'bg-zinc-400' },
            'Issued': { header: 'bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 border-blue-200', dot: 'bg-blue-500' },
            'Partially Paid': { header: 'bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 border-purple-200', dot: 'bg-purple-500' },
            'Paid': { header: 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border-emerald-200', dot: 'bg-emerald-500' },
            'Overdue': { header: 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border-rose-200', dot: 'bg-rose-500' },
        };

        let kanbanHtml = '';

        state.stages.forEach(stage => {
            const columnItems = filtered.filter(p => p.status === stage);
            const colTotal = columnItems.reduce((acc, cur) => acc + (parseFloat(cur.amount) || 0), 0);
            const style = stageColors[stage] || stageColors['Draft'];

            kanbanHtml += `
            <div
                class="kanban-column flex flex-col min-w-[280px] bg-zinc-50 dark:bg-zinc-800/40 rounded-2xl p-3 border border-zinc-200/80 dark:border-zinc-800"
                ondragover="window.handleKanbanDragOver(event)"
                ondragleave="window.handleKanbanDragLeave(event)"
                ondrop="window.handleKanbanDrop(event, '${stage}')"
                data-stage="${stage}"
            >
                <!-- Column Header -->
                <div class="flex items-center justify-between pb-2 mb-2 border-b border-zinc-200 dark:border-zinc-700">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full ${style.dot}"></span>
                        <h3 class="text-xs font-bold text-zinc-900 dark:text-zinc-100">${stage}</h3>
                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-white dark:bg-zinc-700 text-zinc-700 dark:text-zinc-200 shadow-2xs">
                            ${columnItems.length}
                        </span>
                    </div>
                    <span class="text-[11px] font-mono font-bold text-zinc-500 dark:text-zinc-400">
                        ${formatCurrency(colTotal)}
                    </span>
                </div>

                <!-- Cards Container -->
                <div class="kanban-cards-wrapper flex-1 space-y-2.5 overflow-y-auto min-h-[320px] max-h-[750px] p-0.5">
            `;

            if (columnItems.length === 0) {
                kanbanHtml += `
                    <div class="h-28 border border-dashed border-zinc-300 dark:border-zinc-700 rounded-xl flex items-center justify-center text-xs text-zinc-400">
                        Drop invoices here
                    </div>
                `;
            } else {
                columnItems.forEach(item => {
                    const amt = parseFloat(item.amount) || 0;
                    const paid = parseFloat(item.paid_amount) || 0;
                    const bal = Math.max(0, amt - paid);
                    const accName = item.account_name || (item.account && item.account.name) || 'Unknown Account';
                    const curr = item.currency || 'USD';
                    const percent = amt > 0 ? Math.min(100, Math.round((paid / amt) * 100)) : 0;
                    const isAr = (item.type === 'Accounts Receivable' || item.type === 'Receivable');

                    kanbanHtml += `
                    <div
                        draggable="true"
                        ondragstart="window.handleKanbanDragStart(event, '${item.id}')"
                        class="kanban-card group bg-white dark:bg-zinc-900 rounded-xl p-3 border border-zinc-200 dark:border-zinc-800 shadow-2xs hover:shadow-md hover:border-indigo-400 dark:hover:border-indigo-500 transition-all cursor-grab active:cursor-grabbing space-y-2"
                        data-id="${item.id}"
                    >
                        <!-- Top Meta -->
                        <div class="flex items-center justify-between gap-1">
                            <span class="text-xs font-mono font-bold text-indigo-600 dark:text-indigo-400">
                                ${item.invoice_number || 'INV-000'}
                            </span>
                            <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded ${isAr ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-amber-50 text-amber-700 border border-amber-200'}">
                                ${isAr ? 'AR' : 'AP'}
                            </span>
                        </div>

                        <!-- Account Name -->
                        <div class="text-xs font-bold text-zinc-900 dark:text-zinc-100 truncate" title="${accName}">
                            ${accName}
                        </div>

                        <!-- Financials -->
                        <div class="flex items-center justify-between text-xs pt-1 border-t border-zinc-100 dark:border-zinc-800">
                            <span class="text-zinc-500">Invoiced:</span>
                            <strong class="font-bold text-zinc-950 dark:text-white">${formatCurrency(amt, curr)}</strong>
                        </div>

                        ${stage === 'Partially Paid' ? `
                            <div class="space-y-1">
                                <div class="flex justify-between text-[11px] text-zinc-500">
                                    <span>Paid: <strong class="text-emerald-600">${formatCurrency(paid, curr)}</strong></span>
                                    <span>${percent}%</span>
                                </div>
                                <div class="w-full h-1.5 rounded-full bg-zinc-200 dark:bg-zinc-700 overflow-hidden">
                                    <div class="h-full bg-emerald-500 rounded-full" style="width: ${percent}%;"></div>
                                </div>
                            </div>
                        ` : ''}

                        <!-- Due Date & Actions Footer -->
                        <div class="flex items-center justify-between pt-1.5 text-[11px] text-zinc-400">
                            <div class="flex items-center gap-1">
                                <i data-lucide="calendar" class="w-3 h-3 text-zinc-400"></i>
                                <span>${formatDate(item.due_date)}</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <button
                                    type="button"
                                    onclick="window.openRecordPaymentModal('${item.id}')"
                                    class="p-1 rounded text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950/40"
                                    title="Settle"
                                >
                                    <i data-lucide="dollar-sign" class="w-3.5 h-3.5"></i>
                                </button>
                                <button
                                    type="button"
                                    onclick="window.openQuickInvoiceModal('edit', '${item.id}')"
                                    class="p-1 rounded text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800"
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

            kanbanHtml += `
                </div>
            </div>
            `;
        });

        board.innerHTML = kanbanHtml;

        if (window.lucide) lucide.createIcons();
    }

    // Switch between Table and Kanban Views
    window.switchPaymentView = function (mode) {
        state.viewMode = mode;
        const btnTable = document.getElementById('pay-view-btn-table');
        const btnKanban = document.getElementById('pay-view-btn-kanban');
        const contTable = document.getElementById('pay-container-table');
        const contKanban = document.getElementById('pay-container-kanban');

        if (mode === 'table') {
            if (btnTable) {
                btnTable.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-md transition-all cursor-pointer bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white shadow-2xs';
            }
            if (btnKanban) {
                btnKanban.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-md transition-all cursor-pointer text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white';
            }
            if (contTable) contTable.classList.remove('hidden');
            if (contKanban) contKanban.classList.add('hidden');
            renderTableView();
        } else {
            if (btnKanban) {
                btnKanban.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-md transition-all cursor-pointer bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white shadow-2xs';
            }
            if (btnTable) {
                btnTable.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-md transition-all cursor-pointer text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white';
            }
            if (contTable) contTable.classList.add('hidden');
            if (contKanban) contKanban.classList.remove('hidden');
            renderKanbanView();
        }
    };

    // Category Tabs: All, AR, AP
    window.switchPaymentCategoryTab = function (cat) {
        state.categoryFilter = cat;
        const tAll = document.getElementById('pay-cat-tab-all');
        const tAr = document.getElementById('pay-cat-tab-ar');
        const tAp = document.getElementById('pay-cat-tab-ap');

        const activeClass = 'px-2.5 py-1 text-xs font-semibold rounded-md transition-all cursor-pointer bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white shadow-2xs';
        const inactiveClass = 'px-2.5 py-1 text-xs font-medium rounded-md transition-all cursor-pointer text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white';

        if (tAll) tAll.className = (cat === '') ? activeClass : inactiveClass;
        if (tAr) tAr.className = (cat === 'Accounts Receivable') ? activeClass : inactiveClass;
        if (tAp) tAp.className = (cat === 'Accounts Payable') ? activeClass : inactiveClass;

        if (state.viewMode === 'table') {
            renderTableView();
        } else {
            renderKanbanView();
        }
        updateActiveFilterChips();
    };

    // Live Search
    window.handlePaymentSearch = function (val) {
        state.searchTerm = val.trim();
        if (state.viewMode === 'table') {
            renderTableView();
        } else {
            renderKanbanView();
        }
    };

    // ==========================================
    // KANBAN HTML5 DRAG & DROP HANDLERS
    // ==========================================
    let draggedPaymentId = null;

    window.handleKanbanDragStart = function (e, id) {
        draggedPaymentId = id;
        e.dataTransfer.setData('text/plain', id);
        e.dataTransfer.effectAllowed = 'move';
        setTimeout(() => {
            const card = document.querySelector(`.kanban-card[data-id="${id}"]`);
            if (card) card.classList.add('opacity-40');
        }, 0);
    };

    window.handleKanbanDragOver = function (e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        const col = e.currentTarget;
        if (col) col.classList.add('ring-2', 'ring-indigo-500', 'bg-indigo-50/20');
    };

    window.handleKanbanDragLeave = function (e) {
        const col = e.currentTarget;
        if (col) col.classList.remove('ring-2', 'ring-indigo-500', 'bg-indigo-50/20');
    };

    window.handleKanbanDrop = async function (e, targetStage) {
        e.preventDefault();
        const col = e.currentTarget;
        if (col) col.classList.remove('ring-2', 'ring-indigo-500', 'bg-indigo-50/20');

        const id = e.dataTransfer.getData('text/plain') || draggedPaymentId;
        if (!id) return;

        const payment = state.payments.find(p => String(p.id) === String(id));
        if (!payment || payment.status === targetStage) {
            renderKanbanView();
            return;
        }

        const oldStatus = payment.status;
        payment.status = targetStage;
        if (targetStage === 'Paid') {
            payment.paid_amount = payment.amount;
        }

        renderKanbanView();
        updateKpiCards();

        try {
            const res = await fetch(`/api/payments/${id}/status`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ status: targetStage })
            });
            const data = await res.json();
            if (data.success) {
                showNotification(`Invoice moved to "${targetStage}"`);
            } else {
                payment.status = oldStatus;
                renderKanbanView();
                updateKpiCards();
                showNotification(data.message || 'Status update failed', 'error');
            }
        } catch (err) {
            payment.status = oldStatus;
            renderKanbanView();
            updateKpiCards();
            showNotification('Network error while updating status', 'error');
        }
    };

    // ==========================================
    // MODAL 1: RECORD SETTLEMENT PAYMENT DIALOG
    // ==========================================
    window.openRecordPaymentModal = function (id) {
        const payment = state.payments.find(p => String(p.id) === String(id));
        if (!payment) return;

        state.recordingPaymentId = id;

        const amt = parseFloat(payment.amount) || 0;
        const paid = parseFloat(payment.paid_amount) || 0;
        const bal = Math.max(0, amt - paid);
        const accName = payment.account_name || (payment.account && payment.account.name) || 'Unknown Account';
        const curr = payment.currency || 'USD';

        const modal = document.getElementById('record-payment-modal');
        const numElem = document.getElementById('settle-modal-invoice-num');
        const accElem = document.getElementById('settle-box-account');
        const totalElem = document.getElementById('settle-box-total');
        const balElem = document.getElementById('settle-box-balance');
        const inputAmount = document.getElementById('settle-input-amount');
        const inputNotes = document.getElementById('settle-input-notes');

        if (numElem) numElem.textContent = payment.invoice_number || 'INV-000';
        if (accElem) accElem.textContent = accName;
        if (totalElem) totalElem.textContent = formatCurrency(amt, curr);
        if (balElem) balElem.textContent = formatCurrency(bal, curr);
        if (inputAmount) {
            inputAmount.value = bal > 0 ? bal : '';
            inputAmount.max = bal > 0 ? bal : '';
            setTimeout(() => inputAmount.focus(), 100);
        }
        if (inputNotes) inputNotes.value = '';

        if (modal) modal.classList.remove('hidden');
    };

    window.closeRecordPaymentModal = function () {
        const modal = document.getElementById('record-payment-modal');
        if (modal) modal.classList.add('hidden');
        state.recordingPaymentId = null;
    };

    window.saveRecordPayment = async function (e) {
        e.preventDefault();
        const id = state.recordingPaymentId;
        if (!id) return;

        const inputAmount = document.getElementById('settle-input-amount');
        const inputNotes = document.getElementById('settle-input-notes');
        const btnSubmit = document.getElementById('btn-submit-settle-pay');

        const payAmt = parseFloat(inputAmount?.value);
        if (isNaN(payAmt) || payAmt <= 0) {
            showNotification('Please enter a valid settlement amount.', 'error');
            return;
        }

        if (btnSubmit) {
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = `<i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i><span>Recording...</span>`;
            if (window.lucide) lucide.createIcons();
        }

        try {
            const res = await fetch(`/api/payments/${id}/record-payment`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    paymentAmount: payAmt,
                    notes: inputNotes?.value || ''
                })
            });
            const data = await res.json();
            if (data.success && data.data) {
                // Update local state
                const idx = state.payments.findIndex(p => String(p.id) === String(id));
                if (idx !== -1) {
                    state.payments[idx] = data.data;
                }
                closeRecordPaymentModal();
                updateKpiCards();
                if (state.viewMode === 'table') {
                    renderTableView();
                } else {
                    renderKanbanView();
                }
                showNotification(data.message || 'Payment settlement recorded!');
            } else {
                showNotification(data.message || 'Settlement failed', 'error');
            }
        } catch (err) {
            showNotification('Network error while recording settlement', 'error');
        } finally {
            if (btnSubmit) {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = `<i data-lucide="check" class="w-4 h-4"></i><span>Confirm Settlement</span>`;
                if (window.lucide) lucide.createIcons();
            }
        }
    };

    // ==========================================
    // MODAL 2: QUICK INVOICE MODAL (CREATE / EDIT)
    // ==========================================
    window.openQuickInvoiceModal = function (mode = 'create', id = null) {
        const modal = document.getElementById('quick-invoice-modal');
        const form = document.getElementById('quick-invoice-form');
        const title = document.getElementById('quick-inv-modal-title');
        const idField = document.getElementById('quick-inv-id');

        if (!modal || !form) return;

        state.editingPaymentId = (mode === 'edit' && id) ? id : null;

        if (mode === 'edit' && id) {
            const item = state.payments.find(p => String(p.id) === String(id));
            if (!item) return;

            if (title) title.textContent = `Edit Invoice ${item.invoice_number || ''}`;
            if (idField) idField.value = item.id;

            document.getElementById('quick-inv-number').value = item.invoice_number || '';
            document.getElementById('quick-inv-type').value = item.type || 'Accounts Receivable';
            document.getElementById('quick-inv-account').value = item.account_id || '';
            document.getElementById('quick-inv-amount').value = item.amount || '';
            document.getElementById('quick-inv-paid').value = item.paid_amount || 0;
            document.getElementById('quick-inv-status').value = item.status || 'Draft';
            document.getElementById('quick-inv-currency').value = item.currency || 'USD';
            document.getElementById('quick-inv-issue-date').value = item.issue_date || '';
            document.getElementById('quick-inv-due-date').value = item.due_date || '';
            document.getElementById('quick-inv-notes').value = item.notes || '';
        } else {
            if (title) title.textContent = 'Quick Invoice Entry';
            if (idField) idField.value = '';

            form.reset();
            const today = new Date().toISOString().split('T')[0];
            const in30Days = new Date(Date.now() + 30 * 86400000).toISOString().split('T')[0];
            document.getElementById('quick-inv-issue-date').value = today;
            document.getElementById('quick-inv-due-date').value = in30Days;
            document.getElementById('quick-inv-status').value = 'Issued';
            document.getElementById('quick-inv-currency').value = 'USD';
            document.getElementById('quick-inv-paid').value = '0';
        }

        modal.classList.remove('hidden');
    };

    window.closeQuickInvoiceModal = function () {
        const modal = document.getElementById('quick-invoice-modal');
        if (modal) modal.classList.add('hidden');
        state.editingPaymentId = null;
    };

    window.saveQuickInvoice = async function (e) {
        e.preventDefault();
        const id = document.getElementById('quick-inv-id')?.value;
        const btnSave = document.getElementById('btn-save-quick-inv');

        const payload = {
            invoice_number: document.getElementById('quick-inv-number')?.value.trim() || undefined,
            type: document.getElementById('quick-inv-type')?.value,
            account_id: document.getElementById('quick-inv-account')?.value,
            amount: parseFloat(document.getElementById('quick-inv-amount')?.value),
            paid_amount: parseFloat(document.getElementById('quick-inv-paid')?.value) || 0,
            status: document.getElementById('quick-inv-status')?.value,
            currency: document.getElementById('quick-inv-currency')?.value || 'USD',
            issue_date: document.getElementById('quick-inv-issue-date')?.value || undefined,
            due_date: document.getElementById('quick-inv-due-date')?.value || undefined,
            notes: document.getElementById('quick-inv-notes')?.value || undefined,
        };

        if (!payload.account_id) {
            showNotification('Please select a billed account organization.', 'error');
            return;
        }

        if (isNaN(payload.amount) || payload.amount <= 0) {
            showNotification('Please enter a valid invoice amount.', 'error');
            return;
        }

        if (btnSave) {
            btnSave.disabled = true;
            btnSave.innerHTML = `<i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i><span>Saving...</span>`;
            if (window.lucide) lucide.createIcons();
        }

        const isEdit = Boolean(id);
        const url = isEdit ? `/api/payments/${id}` : '/api/payments';
        const method = isEdit ? 'PUT' : 'POST';

        try {
            const res = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (data.success && data.data) {
                if (isEdit) {
                    const idx = state.payments.findIndex(p => String(p.id) === String(id));
                    if (idx !== -1) state.payments[idx] = data.data;
                } else {
                    state.payments.unshift(data.data);
                }
                closeQuickInvoiceModal();
                updateKpiCards();
                if (state.viewMode === 'table') {
                    renderTableView();
                } else {
                    renderKanbanView();
                }
                showNotification(data.message || (isEdit ? 'Invoice updated!' : 'Invoice created!'));
            } else {
                showNotification(data.message || 'Operation failed', 'error');
            }
        } catch (err) {
            showNotification('Network error while saving invoice', 'error');
        } finally {
            if (btnSave) {
                btnSave.disabled = false;
                btnSave.innerHTML = `<i data-lucide="check" class="w-4 h-4"></i><span>Save Invoice</span>`;
                if (window.lucide) lucide.createIcons();
            }
        }
    };

    // Delete Invoice
    window.deletePayment = async function (id) {
        if (!confirm('Are you sure you want to delete this invoice record from the ledger? This action cannot be undone.')) {
            return;
        }

        try {
            const res = await fetch(`/api/payments/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json'
                }
            });
            const data = await res.json();
            if (data.success) {
                state.payments = state.payments.filter(p => String(p.id) !== String(id));
                updateKpiCards();
                if (state.viewMode === 'table') {
                    renderTableView();
                } else {
                    renderKanbanView();
                }
                showNotification('Invoice removed from ledger.');
            } else {
                showNotification(data.message || 'Deletion failed', 'error');
            }
        } catch (err) {
            showNotification('Network error while deleting invoice', 'error');
        }
    };

    // ==========================================
    // MODAL 3: CUSTOMIZE VISIBLE COLUMNS MODAL
    // ==========================================
    window.openPaymentConfigModal = function () {
        const modal = document.getElementById('payment-config-modal');
        const container = document.getElementById('payment-config-checkboxes');
        if (!modal || !container) return;

        let html = '';
        ALL_FIELD_DEFINITIONS.forEach(def => {
            const checked = state.tableVisibleFields.includes(def.key) ? 'checked' : '';
            html += `
            <label class="flex items-center gap-2.5 p-2 rounded-lg hover:bg-zinc-50 dark:hover:bg-zinc-800 cursor-pointer text-xs">
                <input
                    type="checkbox"
                    value="${def.key}"
                    ${checked}
                    class="rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500"
                >
                <span class="font-medium text-zinc-900 dark:text-zinc-100">${def.label}</span>
            </label>
            `;
        });
        container.innerHTML = html;
        modal.classList.remove('hidden');
    };

    window.closePaymentConfigModal = function () {
        const modal = document.getElementById('payment-config-modal');
        if (modal) modal.classList.add('hidden');
    };

    window.savePaymentConfig = function () {
        const checkboxes = document.querySelectorAll('#payment-config-checkboxes input[type="checkbox"]');
        const selected = [];
        checkboxes.forEach(cb => {
            if (cb.checked) selected.push(cb.value);
        });

        if (selected.length === 0) {
            showNotification('Please select at least one visible field.', 'error');
            return;
        }

        state.tableVisibleFields = selected;
        try {
            localStorage.setItem('crm_visible_fields_payments_table', JSON.stringify(selected));
        } catch (_) {}

        closePaymentConfigModal();
        if (state.viewMode === 'table') {
            renderTableView();
        }
        showNotification('Visible fields updated!');
    };

    // ==========================================
    // MODAL 4: FILTER MODAL & ACTIVE FILTER CHIPS
    // ==========================================
    window.openPaymentFilterModal = function () {
        const modal = document.getElementById('payment-filter-modal');
        if (!modal) return;
        document.getElementById('filter-pay-status').value = state.statusFilter || '';
        document.getElementById('filter-pay-account').value = state.accountFilter || '';
        modal.classList.remove('hidden');
    };

    window.closePaymentFilterModal = function () {
        const modal = document.getElementById('payment-filter-modal');
        if (modal) modal.classList.add('hidden');
    };

    window.applyPaymentFilters = function () {
        state.statusFilter = document.getElementById('filter-pay-status')?.value || '';
        state.accountFilter = document.getElementById('filter-pay-account')?.value || '';
        closePaymentFilterModal();
        updateActiveFilterChips();
        if (state.viewMode === 'table') {
            renderTableView();
        } else {
            renderKanbanView();
        }
    };

    window.resetPaymentFilters = function () {
        state.statusFilter = '';
        state.accountFilter = '';
        state.categoryFilter = '';
        const tAll = document.getElementById('pay-cat-tab-all');
        if (tAll) window.switchPaymentCategoryTab('');
        closePaymentFilterModal();
        updateActiveFilterChips();
        if (state.viewMode === 'table') {
            renderTableView();
        } else {
            renderKanbanView();
        }
    };

    function updateActiveFilterChips() {
        const chipContainer = document.getElementById('pay-active-filter-chips');
        const badge = document.getElementById('pay-filter-badge');
        if (!chipContainer) return;

        let activeCount = 0;
        let chipsHtml = '';

        if (state.statusFilter) {
            activeCount++;
            chipsHtml += `
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200 dark:bg-indigo-950/40 dark:text-indigo-300 dark:border-indigo-800">
                <span>Status: <strong>${state.statusFilter}</strong></span>
                <button type="button" onclick="state.statusFilter = ''; updateActiveFilterChips(); ${state.viewMode === 'table' ? 'renderTableView()' : 'renderKanbanView()'};" class="hover:text-indigo-900 cursor-pointer">×</button>
            </span>
            `;
        }

        if (state.accountFilter) {
            activeCount++;
            const acc = state.accounts.find(a => String(a.id) === String(state.accountFilter));
            const accName = acc ? acc.name : 'Account';
            chipsHtml += `
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200 dark:bg-indigo-950/40 dark:text-indigo-300 dark:border-indigo-800">
                <span>Account: <strong>${accName}</strong></span>
                <button type="button" onclick="state.accountFilter = ''; updateActiveFilterChips(); ${state.viewMode === 'table' ? 'renderTableView()' : 'renderKanbanView()'};" class="hover:text-indigo-900 cursor-pointer">×</button>
            </span>
            `;
        }

        if (activeCount > 0) {
            chipsHtml += `
            <button type="button" onclick="window.resetPaymentFilters()" class="text-xs text-rose-600 hover:text-rose-700 font-bold ml-1 cursor-pointer">
                Clear all
            </button>
            `;
            chipContainer.innerHTML = chipsHtml;
            chipContainer.classList.remove('hidden');
            if (badge) {
                badge.textContent = activeCount;
                badge.classList.remove('hidden');
            }
        } else {
            chipContainer.innerHTML = '';
            chipContainer.classList.add('hidden');
            if (badge) badge.classList.add('hidden');
        }
    }

    // ==========================================
    // EXPORT TO CSV
    // ==========================================
    window.exportPaymentsCsv = function () {
        const items = getFilteredPayments();
        if (items.length === 0) {
            showNotification('No invoices to export.', 'error');
            return;
        }

        const headers = ['Invoice Number', 'Account', 'Category', 'Total Amount', 'Paid Amount', 'Balance Due', 'Currency', 'Status', 'Issue Date', 'Due Date', 'Notes'];
        const rows = [headers.join(',')];

        items.forEach(p => {
            const amt = parseFloat(p.amount) || 0;
            const paid = parseFloat(p.paid_amount) || 0;
            const bal = Math.max(0, amt - paid);
            const accName = p.account_name || (p.account && p.account.name) || '';

            const row = [
                `"${p.invoice_number || ''}"`,
                `"${accName.replace(/"/g, '""')}"`,
                `"${p.type || ''}"`,
                amt.toFixed(2),
                paid.toFixed(2),
                bal.toFixed(2),
                `"${p.currency || 'USD'}"`,
                `"${p.status || ''}"`,
                `"${p.issue_date || ''}"`,
                `"${p.due_date || ''}"`,
                `"${(p.notes || '').replace(/"/g, '""')}"`
            ];
            rows.push(row.join(','));
        });

        const blob = new Blob([rows.join('\n')], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `payments_ledger_${new Date().toISOString().split('T')[0]}.csv`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
        showNotification('Invoices & settlement ledger exported to CSV!');
    };

    // ==========================================
    // LIFECYCLE & SPA INITIALIZER
    // ==========================================
    window.initPaymentsModule = function () {
        const root = document.getElementById('payments-page-root');
        if (!root) return;

        // 1. Hydrate state from window.__PAYMENTS_DATA__ if present
        if (window.__PAYMENTS_DATA__) {
            const data = window.__PAYMENTS_DATA__;
            state.payments = data.initialPayments || [];
            state.accounts = data.accounts || [];
            state.fieldPermissions = data.fieldPermissions || {};
            if (data.stages && data.stages.length > 0) state.stages = data.stages;
            if (data.categories && data.categories.length > 0) state.categories = data.categories;
        }

        // 2. Refresh KPIs, Table/Kanban and filters
        updateKpiCards();
        if (state.viewMode === 'table') {
            renderTableView();
        } else {
            renderKanbanView();
        }
        updateActiveFilterChips();

        // 3. Icons
        if (window.lucide) lucide.createIcons();
    };

    // Self-register on DOMContentLoaded & instantaneous execution if already ready
    document.addEventListener('DOMContentLoaded', () => {
        window.initPaymentsModule();
    });

    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        window.initPaymentsModule();
    }
})();
