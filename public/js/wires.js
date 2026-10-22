/**
 * DealSync Enterprise CRM — Wire Received Workbench & Reconciliation Engine
 * 1:1 Parity with React Client (WiresView.tsx, CompactDataTable.tsx, UniversalKanban.tsx)
 * Pure Tailwind CSS styling with Direct Bank Feed Intake, UTR References, Invoice Matching & Live Drag-and-Drop
 */

(function () {
    'use strict';

    // Module State
    const state = {
        wires: [],
        accounts: [],
        unpaidPayments: [],
        stages: ['Pending Verification', 'Matched/Reconciled', 'Discrepancy'],
        fieldPermissions: {},
        statusFilter: '', // '' | 'Pending Verification' | 'Matched/Reconciled' | 'Discrepancy'
        accountFilter: '',
        searchTerm: '',
        viewMode: 'table', // 'table' | 'kanban'
        tableVisibleFields: ['utr_reference', 'remitter_name', 'amount', 'date_received', 'status', 'account_name', 'invoice_number'],
        kanbanVisibleFields: ['remitter_name', 'amount', 'date_received', 'sending_bank'],
        reconcilingWireId: null,
        editingWireId: null,
        isLoading: false,
    };

    const ALL_FIELD_DEFINITIONS = [
        { key: 'utr_reference', label: 'UTR Reference Number' },
        { key: 'remitter_name', label: 'Ordering Party / Remitter' },
        { key: 'sending_bank', label: 'Sending Financial Institution' },
        { key: 'receiving_bank_account', label: 'Deposit Account' },
        { key: 'amount', label: 'Wire Amount ($ USD)' },
        { key: 'date_received', label: 'Credit Date' },
        { key: 'status', label: 'Reconciliation Status' },
        { key: 'account_name', label: 'Matched Customer / Vendor' },
        { key: 'invoice_number', label: 'Settled Invoice' },
        { key: 'reconciliation_notes', label: 'Auditor Notes' },
    ];

    // Load persisted column preferences
    try {
        const savedCols = localStorage.getItem('crm_visible_fields_wires_table');
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
            case 'Matched/Reconciled':
            case 'Reconciled':
                return `<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    Matched/Reconciled
                </span>`;
            case 'Pending Verification':
            case 'Pending':
                return `<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                    Pending Verification
                </span>`;
            case 'Discrepancy':
            case 'Disputed':
            default:
                return `<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                    Discrepancy
                </span>`;
        }
    }

    // Filter wires array according to active search and filters
    function getFilteredWires() {
        return state.wires.filter(item => {
            // Status Tab Filter
            if (state.statusFilter) {
                const match1 = (state.statusFilter === 'Matched/Reconciled' && (item.status === 'Matched/Reconciled' || item.status === 'Reconciled'));
                const match2 = (state.statusFilter === 'Pending Verification' && (item.status === 'Pending Verification' || item.status === 'Pending'));
                const match3 = (state.statusFilter === 'Discrepancy' && (item.status === 'Discrepancy' || item.status === 'Disputed'));
                if (!match1 && !match2 && !match3 && item.status !== state.statusFilter) {
                    return false;
                }
            }

            // Account Filter
            if (state.accountFilter && String(item.reconciled_account_id || item.account_id) !== String(state.accountFilter)) {
                return false;
            }

            // Text Search
            if (state.searchTerm) {
                const term = state.searchTerm.toLowerCase();
                const utr = (item.utr_reference || '').toLowerCase();
                const rem = (item.remitter_name || '').toLowerCase();
                const bank = (item.sending_bank || '').toLowerCase();
                const acc = (item.account_name || (item.account && item.account.name) || '').toLowerCase();
                const notes = (item.reconciliation_notes || '').toLowerCase();
                if (!utr.includes(term) && !rem.includes(term) && !bank.includes(term) && !acc.includes(term) && !notes.includes(term)) {
                    return false;
                }
            }

            return true;
        });
    }

    // Live KPI Calculations & UI Updates
    function updateKpiCards() {
        let totalWires = 0;
        let reconciled = 0;
        let pending = 0;
        let discrepancy = 0;
        let countAll = state.wires.length;
        let countMatched = 0;
        let countPending = 0;
        let countDiscrepancy = 0;

        state.wires.forEach(w => {
            const amt = parseFloat(w.amount) || 0;
            totalWires += amt;

            if (w.status === 'Matched/Reconciled' || w.status === 'Reconciled') {
                reconciled += amt;
                countMatched++;
            } else if (w.status === 'Discrepancy' || w.status === 'Disputed') {
                discrepancy += amt;
                countDiscrepancy++;
            } else {
                pending += amt;
                countPending++;
            }
        });

        const kpiWires = document.getElementById('kpi-total-wires');
        const kpiRec = document.getElementById('kpi-reconciled-funds');
        const kpiPen = document.getElementById('kpi-pending-funds');
        const kpiDisc = document.getElementById('kpi-discrepancy-funds');
        const kpiCount = document.getElementById('kpi-wires-count');

        if (kpiWires) kpiWires.textContent = formatCurrency(totalWires);
        if (kpiRec) kpiRec.textContent = formatCurrency(reconciled);
        if (kpiPen) kpiPen.textContent = formatCurrency(pending);
        if (kpiDisc) kpiDisc.textContent = formatCurrency(discrepancy);
        if (kpiCount) kpiCount.textContent = countAll;

        // Update status tab counters
        const cAll = document.getElementById('count-status-all');
        const cMat = document.getElementById('count-status-matched');
        const cPen = document.getElementById('count-status-pending');
        const cDis = document.getElementById('count-status-discrepancy');

        if (cAll) cAll.textContent = countAll;
        if (cMat) cMat.textContent = countMatched;
        if (cPen) cPen.textContent = countPending;
        if (cDis) cDis.textContent = countDiscrepancy;
    }

    // ==========================================
    // VIEW 1: COMPACT TABLE VIEW RENDERER
    // ==========================================
    function renderTableView() {
        const theadRow = document.getElementById('wire-table-header-row');
        const tbody = document.getElementById('wire-table-tbody');
        const emptyState = document.getElementById('wire-empty-state-table');

        if (!theadRow || !tbody) return;

        const filtered = getFilteredWires();

        // 1. Build Header Row based on visible fields
        let headerHtml = '';
        state.tableVisibleFields.forEach(fieldKey => {
            const def = ALL_FIELD_DEFINITIONS.find(d => d.key === fieldKey);
            const label = def ? def.label : fieldKey;
            let alignClass = 'text-left';
            if (fieldKey === 'amount') alignClass = 'text-right';
            headerHtml += `<th class="px-4 py-3 ${alignClass}">${label}</th>`;
        });
        headerHtml += `<th class="px-4 py-3 text-right">Workbench Action</th>`;
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
            const accName = item.account_name || (item.account && item.account.name) || 'Unallocated';
            const invNum = item.invoice_number || (item.payment && item.payment.invoice_number) || null;
            const isReconciled = (item.status === 'Matched/Reconciled' || item.status === 'Reconciled');

            rowsHtml += `<tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors border-b border-zinc-100 dark:border-zinc-800/60">`;

            state.tableVisibleFields.forEach(fieldKey => {
                switch (fieldKey) {
                    case 'utr_reference':
                        rowsHtml += `<td class="px-4 py-3 font-mono font-bold">
                            <button
                                type="button"
                                onclick="window.openWireIntakeModal('edit', '${item.id}')"
                                class="text-amber-600 dark:text-amber-400 font-bold hover:text-amber-800 no-underline hover:no-underline cursor-pointer"
                            >
                                ${item.utr_reference || 'UTR-000'}
                            </button>
                        </td>`;
                        break;
                    case 'remitter_name':
                        rowsHtml += `<td class="px-4 py-3 font-semibold text-zinc-900 dark:text-zinc-100">
                            <span class="truncate block max-w-xs">${item.remitter_name || '—'}</span>
                        </td>`;
                        break;
                    case 'sending_bank':
                        rowsHtml += `<td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">
                            <div class="flex items-center gap-1.5 truncate max-w-[200px]" title="${item.sending_bank}">
                                <i data-lucide="building-2" class="w-3.5 h-3.5 text-zinc-400 shrink-0"></i>
                                <span>${item.sending_bank || '—'}</span>
                            </div>
                        </td>`;
                        break;
                    case 'receiving_bank_account':
                        rowsHtml += `<td class="px-4 py-3 font-mono text-[11px] text-zinc-500">${item.receiving_bank_account || 'CITI-US-MAIN-9482'}</td>`;
                        break;
                    case 'amount':
                        rowsHtml += `<td class="px-4 py-3 text-right font-black text-emerald-600 dark:text-emerald-400">
                            ${formatCurrency(amt, item.currency || 'USD')}
                        </td>`;
                        break;
                    case 'date_received':
                        rowsHtml += `<td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="calendar" class="w-3.5 h-3.5 text-zinc-400"></i>
                                <span>${formatDate(item.date_received)}</span>
                            </div>
                        </td>`;
                        break;
                    case 'status':
                        rowsHtml += `<td class="px-4 py-3">${getStatusBadge(item.status)}</td>`;
                        break;
                    case 'account_name':
                        rowsHtml += `<td class="px-4 py-3 font-semibold ${item.reconciled_account_id ? 'text-zinc-900 dark:text-zinc-100' : 'text-zinc-400'}">
                            ${accName}
                        </td>`;
                        break;
                    case 'invoice_number':
                        rowsHtml += `<td class="px-4 py-3 font-mono">
                            ${invNum ? `
                                <a href="/payments" class="spa-nav-link inline-flex items-center gap-1 font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 no-underline hover:no-underline">
                                    <i data-lucide="file-text" class="w-3 h-3"></i>
                                    <span>${invNum}</span>
                                </a>
                            ` : `<span class="text-zinc-400 italic">Unlinked</span>`}
                        </td>`;
                        break;
                    case 'reconciliation_notes':
                        rowsHtml += `<td class="px-4 py-3 text-zinc-500 dark:text-zinc-400 truncate max-w-[200px]" title="${(item.reconciliation_notes || '').replace(/"/g, '&quot;')}">${item.reconciliation_notes || '—'}</td>`;
                        break;
                    default:
                        rowsHtml += `<td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">${item[fieldKey] || '—'}</td>`;
                        break;
                }
            });

            // Action Buttons
            rowsHtml += `<td class="px-4 py-3 text-right">
                <div class="inline-flex items-center gap-1.5 justify-end">
                    ${!isReconciled ? `
                        <button
                            type="button"
                            onclick="window.openReconcileWireModal('${item.id}')"
                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-500 hover:bg-amber-600 text-white shadow-2xs transition-colors cursor-pointer"
                            title="Reconcile with open invoices"
                        >
                            <i data-lucide="link" class="w-3 h-3"></i>
                            <span>Reconcile</span>
                        </button>
                    ` : `
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-bold text-emerald-700 bg-emerald-50 dark:bg-emerald-950/40">
                            <i data-lucide="check" class="w-3 h-3"></i>
                            <span>Reconciled</span>
                        </span>
                    `}
                    <button
                        type="button"
                        onclick="window.openWireIntakeModal('edit', '${item.id}')"
                        class="p-1.5 rounded-lg text-zinc-500 hover:text-zinc-900 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
                        title="Edit Wire"
                    >
                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                    </button>
                    <button
                        type="button"
                        onclick="window.deleteWire('${item.id}')"
                        class="p-1.5 rounded-lg text-rose-500 hover:text-rose-700 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors cursor-pointer"
                        title="Delete Wire"
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
        const board = document.getElementById('wire-kanban-board');
        if (!board) return;

        const filtered = getFilteredWires();

        const stageColors = {
            'Pending Verification': { header: 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border-amber-200', dot: 'bg-amber-500' },
            'Matched/Reconciled': { header: 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border-emerald-200', dot: 'bg-emerald-500' },
            'Discrepancy': { header: 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border-rose-200', dot: 'bg-rose-500' },
        };

        let kanbanHtml = '';

        state.stages.forEach(stage => {
            const columnItems = filtered.filter(w => {
                if (stage === 'Matched/Reconciled') return w.status === 'Matched/Reconciled' || w.status === 'Reconciled';
                if (stage === 'Pending Verification') return w.status === 'Pending Verification' || w.status === 'Pending';
                if (stage === 'Discrepancy') return w.status === 'Discrepancy' || w.status === 'Disputed';
                return w.status === stage;
            });

            const colTotal = columnItems.reduce((acc, cur) => acc + (parseFloat(cur.amount) || 0), 0);
            const style = stageColors[stage] || stageColors['Pending Verification'];

            kanbanHtml += `
            <div
                class="kanban-column flex flex-col min-w-[320px] bg-zinc-50 dark:bg-zinc-800/40 rounded-2xl p-4 border border-zinc-200/80 dark:border-zinc-800"
                ondragover="window.handleWireDragOver(event)"
                ondragleave="window.handleWireDragLeave(event)"
                ondrop="window.handleWireDrop(event, '${stage}')"
                data-stage="${stage}"
            >
                <!-- Column Header -->
                <div class="flex items-center justify-between pb-3 mb-3 border-b border-zinc-200 dark:border-zinc-700">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full ${style.dot}"></span>
                        <h3 class="text-xs font-bold text-zinc-900 dark:text-zinc-100">${stage}</h3>
                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-white dark:bg-zinc-700 text-zinc-700 dark:text-zinc-200 shadow-2xs">
                            ${columnItems.length}
                        </span>
                    </div>
                    <span class="text-xs font-mono font-bold text-zinc-600 dark:text-zinc-300">
                        ${formatCurrency(colTotal)}
                    </span>
                </div>

                <!-- Cards Container -->
                <div class="kanban-cards-wrapper flex-1 space-y-3 overflow-y-auto min-h-[350px] max-h-[750px] p-0.5">
            `;

            if (columnItems.length === 0) {
                kanbanHtml += `
                    <div class="h-32 border border-dashed border-zinc-300 dark:border-zinc-700 rounded-xl flex items-center justify-center text-xs text-zinc-400">
                        Drop wires here
                    </div>
                `;
            } else {
                columnItems.forEach(item => {
                    const amt = parseFloat(item.amount) || 0;
                    const accName = item.account_name || (item.account && item.account.name) || 'Unallocated Remittance';
                    const isReconciled = (item.status === 'Matched/Reconciled' || item.status === 'Reconciled');

                    kanbanHtml += `
                    <div
                        draggable="true"
                        ondragstart="window.handleWireDragStart(event, '${item.id}')"
                        class="kanban-card group bg-white dark:bg-zinc-900 rounded-xl p-3.5 border border-zinc-200 dark:border-zinc-800 shadow-2xs hover:shadow-md hover:border-amber-400 dark:hover:border-amber-500 transition-all cursor-grab active:cursor-grabbing space-y-2.5"
                        data-id="${item.id}"
                    >
                        <!-- Top Meta: UTR & Bank -->
                        <div class="flex items-center justify-between gap-1">
                            <span class="text-xs font-mono font-bold text-amber-600 dark:text-amber-400">
                                ${item.utr_reference || 'UTR-000'}
                            </span>
                            <span class="text-[11px] font-mono font-bold text-emerald-600 dark:text-emerald-400">
                                ${formatCurrency(amt, item.currency || 'USD')}
                            </span>
                        </div>

                        <!-- Remitter Entity -->
                        <div class="text-xs font-bold text-zinc-900 dark:text-zinc-100 truncate" title="${item.remitter_name}">
                            ${item.remitter_name}
                        </div>

                        <!-- Sending Bank -->
                        <div class="flex items-center gap-1.5 text-[11px] text-zinc-500">
                            <i data-lucide="building-2" class="w-3.5 h-3.5 text-zinc-400 shrink-0"></i>
                            <span class="truncate">${item.sending_bank || '—'}</span>
                        </div>

                        <!-- Account Link / Reconciled Invoice -->
                        <div class="pt-2 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-between text-[11px]">
                            <span class="text-zinc-400 truncate max-w-[170px]" title="${accName}">
                                ${accName}
                            </span>
                            ${!isReconciled ? `
                                <button
                                    type="button"
                                    onclick="window.openReconcileWireModal('${item.id}')"
                                    class="px-2 py-0.5 rounded text-[11px] font-bold bg-amber-500 text-white hover:bg-amber-600 cursor-pointer shadow-2xs"
                                >
                                    Reconcile
                                </button>
                            ` : `
                                <span class="text-emerald-600 font-bold flex items-center gap-1">
                                    <i data-lucide="check-circle-2" class="w-3 h-3"></i>
                                    <span>Matched</span>
                                </span>
                            `}
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
    window.switchWireView = function (mode) {
        state.viewMode = mode;
        const btnTable = document.getElementById('wire-view-btn-table');
        const btnKanban = document.getElementById('wire-view-btn-kanban');
        const contTable = document.getElementById('wire-container-table');
        const contKanban = document.getElementById('wire-container-kanban');

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

    // Status Filter Tabs
    window.switchWireStatusTab = function (status) {
        state.statusFilter = status;
        const tAll = document.getElementById('wire-status-tab-all');
        const tMat = document.getElementById('wire-status-tab-matched');
        const tPen = document.getElementById('wire-status-tab-pending');
        const tDis = document.getElementById('wire-status-tab-discrepancy');

        const activeClass = 'px-2.5 py-1 text-xs font-semibold rounded-md transition-all cursor-pointer bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white shadow-2xs';
        const inactiveClass = 'px-2.5 py-1 text-xs font-medium rounded-md transition-all cursor-pointer text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white';

        if (tAll) tAll.className = (status === '') ? activeClass : inactiveClass;
        if (tMat) tMat.className = (status === 'Matched/Reconciled') ? activeClass : inactiveClass;
        if (tPen) tPen.className = (status === 'Pending Verification') ? activeClass : inactiveClass;
        if (tDis) tDis.className = (status === 'Discrepancy') ? activeClass : inactiveClass;

        if (state.viewMode === 'table') {
            renderTableView();
        } else {
            renderKanbanView();
        }
        updateActiveFilterChips();
    };

    // Live Search
    window.handleWireSearch = function (val) {
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
    let draggedWireId = null;

    window.handleWireDragStart = function (e, id) {
        draggedWireId = id;
        e.dataTransfer.setData('text/plain', id);
        e.dataTransfer.effectAllowed = 'move';
        setTimeout(() => {
            const card = document.querySelector(`.kanban-card[data-id="${id}"]`);
            if (card) card.classList.add('opacity-40');
        }, 0);
    };

    window.handleWireDragOver = function (e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        const col = e.currentTarget;
        if (col) col.classList.add('ring-2', 'ring-amber-500', 'bg-amber-50/20');
    };

    window.handleWireDragLeave = function (e) {
        const col = e.currentTarget;
        if (col) col.classList.remove('ring-2', 'ring-amber-500', 'bg-amber-50/20');
    };

    window.handleWireDrop = async function (e, targetStage) {
        e.preventDefault();
        const col = e.currentTarget;
        if (col) col.classList.remove('ring-2', 'ring-amber-500', 'bg-amber-50/20');

        const id = e.dataTransfer.getData('text/plain') || draggedWireId;
        if (!id) return;

        const wire = state.wires.find(w => String(w.id) === String(id));
        if (!wire || wire.status === targetStage) {
            renderKanbanView();
            return;
        }

        const oldStatus = wire.status;
        wire.status = targetStage;

        renderKanbanView();
        updateKpiCards();

        try {
            const res = await fetch(`/api/wires/${id}/status`, {
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
                showNotification(`Wire moved to "${targetStage}"`);
            } else {
                wire.status = oldStatus;
                renderKanbanView();
                updateKpiCards();
                showNotification(data.message || 'Status update failed', 'error');
            }
        } catch (err) {
            wire.status = oldStatus;
            renderKanbanView();
            updateKpiCards();
            showNotification('Network error while updating status', 'error');
        }
    };

    // ==========================================
    // MODAL 1: RECONCILE WIRE WORKBENCH
    // ==========================================
    window.openReconcileWireModal = function (id) {
        const wire = state.wires.find(w => String(w.id) === String(id));
        if (!wire) return;

        state.reconcilingWireId = id;

        const modal = document.getElementById('reconcile-wire-modal');
        const utrElem = document.getElementById('reconcile-modal-utr');
        const remitterElem = document.getElementById('reconcile-box-remitter');
        const bankElem = document.getElementById('reconcile-box-bank');
        const amtElem = document.getElementById('reconcile-box-amount');
        const accSelect = document.getElementById('reconcile-select-account');
        const paySelect = document.getElementById('reconcile-select-payment');
        const notesInput = document.getElementById('reconcile-input-notes');

        if (utrElem) utrElem.textContent = wire.utr_reference || 'UTR-000';
        if (remitterElem) remitterElem.textContent = wire.remitter_name || '—';
        if (bankElem) bankElem.textContent = wire.sending_bank || '—';
        if (amtElem) amtElem.textContent = formatCurrency(wire.amount, wire.currency || 'USD');

        const initialAccId = wire.reconciled_account_id || wire.account_id || '';
        if (accSelect) accSelect.value = initialAccId;

        // Filter invoice options according to account
        window.handleReconcileAccountChange(initialAccId, wire.reconciled_payment_id || wire.payment_id);

        if (notesInput) notesInput.value = wire.reconciliation_notes || '';

        if (modal) modal.classList.remove('hidden');
    };

    window.closeReconcileWireModal = function () {
        const modal = document.getElementById('reconcile-wire-modal');
        if (modal) modal.classList.add('hidden');
        state.reconcilingWireId = null;
    };

    window.handleReconcileAccountChange = function (accId, selectedPaymentId = '') {
        const paySelect = document.getElementById('reconcile-select-payment');
        if (!paySelect) return;

        const options = paySelect.querySelectorAll('.invoice-option');
        let hasMatches = false;

        options.forEach(opt => {
            const optAcc = opt.getAttribute('data-account');
            if (!accId || optAcc === String(accId)) {
                opt.style.display = '';
                hasMatches = true;
            } else {
                opt.style.display = 'none';
            }
        });

        if (selectedPaymentId) {
            paySelect.value = selectedPaymentId;
        } else {
            paySelect.value = '';
        }
    };

    window.saveReconcileWire = async function (e) {
        e.preventDefault();
        const id = state.reconcilingWireId;
        if (!id) return;

        const accSelect = document.getElementById('reconcile-select-account');
        const paySelect = document.getElementById('reconcile-select-payment');
        const notesInput = document.getElementById('reconcile-input-notes');
        const btnSubmit = document.getElementById('btn-submit-reconcile-wire');

        const accountId = accSelect?.value;
        if (!accountId) {
            showNotification('Please select a matching account organization.', 'error');
            return;
        }

        if (btnSubmit) {
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = `<i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i><span>Reconciling...</span>`;
            if (window.lucide) lucide.createIcons();
        }

        try {
            const res = await fetch(`/api/wires/${id}/reconcile`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    accountId: accountId,
                    paymentId: paySelect?.value || undefined,
                    notes: notesInput?.value || ''
                })
            });
            const data = await res.json();
            if (data.success && data.data) {
                const idx = state.wires.findIndex(w => String(w.id) === String(id));
                if (idx !== -1) {
                    state.wires[idx] = data.data;
                }
                closeReconcileWireModal();
                updateKpiCards();
                if (state.viewMode === 'table') {
                    renderTableView();
                } else {
                    renderKanbanView();
                }
                showNotification(data.message || 'Wire successfully reconciled!');
            } else {
                showNotification(data.message || 'Reconciliation failed', 'error');
            }
        } catch (err) {
            showNotification('Network error while reconciling wire', 'error');
        } finally {
            if (btnSubmit) {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = `<i data-lucide="check-circle-2" class="w-4 h-4"></i><span>Confirm Reconciliation</span>`;
                if (window.lucide) lucide.createIcons();
            }
        }
    };

    // ==========================================
    // MODAL 2: WIRE INTAKE MODAL (CREATE / EDIT)
    // ==========================================
    window.openWireIntakeModal = function (mode = 'create', id = null) {
        const modal = document.getElementById('wire-intake-modal');
        const form = document.getElementById('wire-intake-form');
        const title = document.getElementById('wire-intake-modal-title');
        const idField = document.getElementById('wire-intake-id');

        if (!modal || !form) return;

        state.editingWireId = (mode === 'edit' && id) ? id : null;

        if (mode === 'edit' && id) {
            const item = state.wires.find(w => String(w.id) === String(id));
            if (!item) return;

            if (title) title.textContent = `Edit Wire Intake ${item.utr_reference || ''}`;
            if (idField) idField.value = item.id;

            document.getElementById('wire-input-utr').value = item.utr_reference || '';
            document.getElementById('wire-input-amount').value = item.amount || '';
            document.getElementById('wire-input-remitter').value = item.remitter_name || '';
            document.getElementById('wire-input-sending-bank').value = item.sending_bank || '';
            document.getElementById('wire-input-receiving-bank').value = item.receiving_bank_account || 'CITI-US-MAIN-9482';
            document.getElementById('wire-input-date').value = item.date_received || '';
            document.getElementById('wire-input-status').value = item.status || 'Pending Verification';
            document.getElementById('wire-input-account').value = item.reconciled_account_id || item.account_id || '';
            document.getElementById('wire-input-notes').value = item.reconciliation_notes || '';
        } else {
            if (title) title.textContent = 'Log Bank Wire Intake';
            if (idField) idField.value = '';

            form.reset();
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('wire-input-date').value = today;
            document.getElementById('wire-input-status').value = 'Pending Verification';
            document.getElementById('wire-input-receiving-bank').value = 'CITI-US-MAIN-9482';
        }

        modal.classList.remove('hidden');
    };

    window.closeWireIntakeModal = function () {
        const modal = document.getElementById('wire-intake-modal');
        if (modal) modal.classList.add('hidden');
        state.editingWireId = null;
    };

    window.saveWireIntake = async function (e) {
        e.preventDefault();
        const id = document.getElementById('wire-intake-id')?.value;
        const btnSave = document.getElementById('btn-save-wire-intake');

        const payload = {
            utr_reference: document.getElementById('wire-input-utr')?.value.trim() || undefined,
            remitter_name: document.getElementById('wire-input-remitter')?.value.trim(),
            sending_bank: document.getElementById('wire-input-sending-bank')?.value.trim(),
            receiving_bank_account: document.getElementById('wire-input-receiving-bank')?.value.trim() || 'CITI-US-MAIN-9482',
            amount: parseFloat(document.getElementById('wire-input-amount')?.value),
            date_received: document.getElementById('wire-input-date')?.value,
            status: document.getElementById('wire-input-status')?.value,
            reconciled_account_id: document.getElementById('wire-input-account')?.value || null,
            reconciliation_notes: document.getElementById('wire-input-notes')?.value || undefined,
        };

        if (!payload.remitter_name || !payload.sending_bank) {
            showNotification('Please fill in remitter name and sending bank.', 'error');
            return;
        }

        if (isNaN(payload.amount) || payload.amount <= 0) {
            showNotification('Please enter a valid transfer amount.', 'error');
            return;
        }

        if (btnSave) {
            btnSave.disabled = true;
            btnSave.innerHTML = `<i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i><span>Saving...</span>`;
            if (window.lucide) lucide.createIcons();
        }

        const isEdit = Boolean(id);
        const url = isEdit ? `/api/wires/${id}` : '/api/wires';
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
                    const idx = state.wires.findIndex(w => String(w.id) === String(id));
                    if (idx !== -1) state.wires[idx] = data.data;
                } else {
                    state.wires.unshift(data.data);
                }
                closeWireIntakeModal();
                updateKpiCards();
                if (state.viewMode === 'table') {
                    renderTableView();
                } else {
                    renderKanbanView();
                }
                showNotification(data.message || (isEdit ? 'Wire updated!' : 'Wire logged!'));
            } else {
                showNotification(data.message || 'Operation failed', 'error');
            }
        } catch (err) {
            showNotification('Network error while saving wire', 'error');
        } finally {
            if (btnSave) {
                btnSave.disabled = false;
                btnSave.innerHTML = `<i data-lucide="check" class="w-4 h-4"></i><span>Save Wire Record</span>`;
                if (window.lucide) lucide.createIcons();
            }
        }
    };

    // Delete Wire
    window.deleteWire = async function (id) {
        if (!confirm('Are you sure you want to delete this bank wire from the workbench?')) {
            return;
        }

        try {
            const res = await fetch(`/api/wires/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json'
                }
            });
            const data = await res.json();
            if (data.success) {
                state.wires = state.wires.filter(w => String(w.id) !== String(id));
                updateKpiCards();
                if (state.viewMode === 'table') {
                    renderTableView();
                } else {
                    renderKanbanView();
                }
                showNotification('Wire record removed.');
            } else {
                showNotification(data.message || 'Deletion failed', 'error');
            }
        } catch (err) {
            showNotification('Network error while deleting wire', 'error');
        }
    };

    // ==========================================
    // MODAL 3: CUSTOMIZE VISIBLE COLUMNS MODAL
    // ==========================================
    window.openWireConfigModal = function () {
        const modal = document.getElementById('wire-config-modal');
        const container = document.getElementById('wire-config-checkboxes');
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
                    class="rounded border-zinc-300 text-amber-600 focus:ring-amber-500"
                >
                <span class="font-medium text-zinc-900 dark:text-zinc-100">${def.label}</span>
            </label>
            `;
        });
        container.innerHTML = html;
        modal.classList.remove('hidden');
    };

    window.closeWireConfigModal = function () {
        const modal = document.getElementById('wire-config-modal');
        if (modal) modal.classList.add('hidden');
    };

    window.saveWireConfig = function () {
        const checkboxes = document.querySelectorAll('#wire-config-checkboxes input[type="checkbox"]');
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
            localStorage.setItem('crm_visible_fields_wires_table', JSON.stringify(selected));
        } catch (_) {}

        closeWireConfigModal();
        if (state.viewMode === 'table') {
            renderTableView();
        }
        showNotification('Visible fields updated!');
    };

    // ==========================================
    // MODAL 4: FILTER MODAL & ACTIVE FILTER CHIPS
    // ==========================================
    window.openWireFilterModal = function () {
        const modal = document.getElementById('wire-filter-modal');
        if (!modal) return;
        document.getElementById('filter-wire-status').value = state.statusFilter || '';
        document.getElementById('filter-wire-account').value = state.accountFilter || '';
        modal.classList.remove('hidden');
    };

    window.closeWireFilterModal = function () {
        const modal = document.getElementById('wire-filter-modal');
        if (modal) modal.classList.add('hidden');
    };

    window.applyWireFilters = function () {
        state.statusFilter = document.getElementById('filter-wire-status')?.value || '';
        state.accountFilter = document.getElementById('filter-wire-account')?.value || '';
        closeWireFilterModal();
        updateActiveFilterChips();
        if (state.viewMode === 'table') {
            renderTableView();
        } else {
            renderKanbanView();
        }
    };

    window.resetWireFilters = function () {
        state.statusFilter = '';
        state.accountFilter = '';
        const tAll = document.getElementById('wire-status-tab-all');
        if (tAll) window.switchWireStatusTab('');
        closeWireFilterModal();
        updateActiveFilterChips();
        if (state.viewMode === 'table') {
            renderTableView();
        } else {
            renderKanbanView();
        }
    };

    function updateActiveFilterChips() {
        const chipContainer = document.getElementById('wire-active-filter-chips');
        const badge = document.getElementById('wire-filter-badge');
        if (!chipContainer) return;

        let activeCount = 0;
        let chipsHtml = '';

        if (state.statusFilter) {
            activeCount++;
            chipsHtml += `
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800">
                <span>Status: <strong>${state.statusFilter}</strong></span>
                <button type="button" onclick="state.statusFilter = ''; updateActiveFilterChips(); ${state.viewMode === 'table' ? 'renderTableView()' : 'renderKanbanView()'};" class="hover:text-amber-900 cursor-pointer">×</button>
            </span>
            `;
        }

        if (state.accountFilter) {
            activeCount++;
            const acc = state.accounts.find(a => String(a.id) === String(state.accountFilter));
            const accName = acc ? acc.name : 'Account';
            chipsHtml += `
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800">
                <span>Account: <strong>${accName}</strong></span>
                <button type="button" onclick="state.accountFilter = ''; updateActiveFilterChips(); ${state.viewMode === 'table' ? 'renderTableView()' : 'renderKanbanView()'};" class="hover:text-amber-900 cursor-pointer">×</button>
            </span>
            `;
        }

        if (activeCount > 0) {
            chipsHtml += `
            <button type="button" onclick="window.resetWireFilters()" class="text-xs text-rose-600 hover:text-rose-700 font-bold ml-1 cursor-pointer">
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
    window.exportWiresCsv = function () {
        const items = getFilteredWires();
        if (items.length === 0) {
            showNotification('No wires to export.', 'error');
            return;
        }

        const headers = ['UTR Reference', 'Remitter Name', 'Sending Bank', 'Deposit Account', 'Amount', 'Currency', 'Date Received', 'Status', 'Matched Account', 'Matched Invoice', 'Auditor Notes'];
        const rows = [headers.join(',')];

        items.forEach(w => {
            const amt = parseFloat(w.amount) || 0;
            const accName = w.account_name || (w.account && w.account.name) || '';
            const invNum = w.invoice_number || (w.payment && w.payment.invoice_number) || '';

            const row = [
                `"${w.utr_reference || ''}"`,
                `"${(w.remitter_name || '').replace(/"/g, '""')}"`,
                `"${(w.sending_bank || '').replace(/"/g, '""')}"`,
                `"${w.receiving_bank_account || 'CITI-US-MAIN-9482'}"`,
                amt.toFixed(2),
                `"${w.currency || 'USD'}"`,
                `"${w.date_received || ''}"`,
                `"${w.status || ''}"`,
                `"${accName.replace(/"/g, '""')}"`,
                `"${invNum.replace(/"/g, '""')}"`,
                `"${(w.reconciliation_notes || '').replace(/"/g, '""')}"`
            ];
            rows.push(row.join(','));
        });

        const blob = new Blob([rows.join('\n')], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `wires_workbench_${new Date().toISOString().split('T')[0]}.csv`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
        showNotification('Bank wires exported to CSV!');
    };

    // ==========================================
    // LIFECYCLE & SPA INITIALIZER
    // ==========================================
    window.initWiresModule = function () {
        const root = document.getElementById('wires-page-root');
        if (!root) return;

        // 1. Hydrate state from window.__WIRES_DATA__ if present
        if (window.__WIRES_DATA__) {
            const data = window.__WIRES_DATA__;
            state.wires = data.initialWires || [];
            state.accounts = data.accounts || [];
            state.unpaidPayments = data.unpaidPayments || [];
            state.fieldPermissions = data.fieldPermissions || {};
            if (data.stages && data.stages.length > 0) state.stages = data.stages;
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
        window.initWiresModule();
    });

    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        window.initWiresModule();
    }
})();
