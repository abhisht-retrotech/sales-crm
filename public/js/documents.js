/**
 * DealSync Enterprise CRM — Billing & Ledger Documents Controller
 * Pure Tailwind CSS styling with dynamic calculations, status updates, print, and Kanban drag-drop.
 */

(function () {
    'use strict';

    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function showNotification(message, type = 'success') {
        if (typeof window.showToast === 'function') {
            window.showToast(message, type);
        } else if (typeof window.showSpaToast === 'function') {
            window.showSpaToast(message, type);
        } else {
            console.log(`[${type.toUpperCase()}] ${message}`);
        }
    }

    const ALL_COLUMNS = [
        { key: 'document_number', label: 'Document #' },
        { key: 'document_type', label: 'Type' },
        { key: 'account_name', label: 'Account Organization' },
        { key: 'issue_date', label: 'Issue Date' },
        { key: 'due_date', label: 'Due Date' },
        { key: 'grand_total', label: 'Grand Total' },
        { key: 'balance_due', label: 'Balance Due' },
        { key: 'status', label: 'Status' },
        { key: 'governance', label: 'Governance' },
        { key: 'actions', label: 'Actions' }
    ];

    const DEFAULT_TABLE_COLUMNS = [
        'document_number',
        'document_type',
        'account_name',
        'issue_date',
        'due_date',
        'grand_total',
        'balance_due',
        'status',
        'governance',
        'actions'
    ];

    const KANBAN_STAGES = [
        'draft',
        'pending_approval',
        'issued',
        'sent',
        'partially_paid',
        'paid',
        'overdue',
        'void'
    ];

    const STAGE_LABELS = {
        draft: 'Draft',
        pending_approval: 'Pending Approval',
        issued: 'Issued',
        sent: 'Sent',
        partially_paid: 'Partially Paid',
        paid: 'Paid',
        overdue: 'Overdue',
        void: 'Void'
    };

    // State container
    const state = {
        documents: [],
        templates: [],
        accounts: [],
        contacts: [],
        kpis: {},
        governance: {},
        viewMode: 'table',
        selectedType: 'all',
        searchTerm: '',
        statusFilter: '',
        accountFilter: '',
        tableVisibleColumns: DEFAULT_TABLE_COLUMNS,
        modalLineItems: [],
        activePreviewDoc: null,
    };

    // Hydrate State
    function hydrateState() {
        const data = window.__DOCUMENTS_DATA__ || {};
        state.documents = Array.isArray(data.initialDocuments) ? [...data.initialDocuments] : [];
        state.templates = Array.isArray(data.templates) ? [...data.templates] : [];
        state.accounts = Array.isArray(data.accounts) ? [...data.accounts] : [];
        state.contacts = Array.isArray(data.contacts) ? [...data.contacts] : [];
        state.kpis = data.kpis || {};
        state.governance = data.governance || {};

        try {
            const savedCols = localStorage.getItem('crm_visible_fields_documents_table');
            if (savedCols) {
                const parsed = JSON.parse(savedCols);
                if (Array.isArray(parsed) && parsed.length > 0) {
                    state.tableVisibleColumns = parsed;
                }
            }
        } catch (_) {}
    }

    // Filter Documents
    function getFilteredDocuments() {
        return state.documents.filter(doc => {
            if (state.selectedType !== 'all' && doc.document_type !== state.selectedType) {
                return false;
            }
            if (state.statusFilter && doc.status !== state.statusFilter) {
                return false;
            }
            if (state.accountFilter && String(doc.account_id) !== String(state.accountFilter)) {
                return false;
            }
            if (state.searchTerm) {
                const q = state.searchTerm.toLowerCase();
                const docNum = (doc.document_number || '').toLowerCase();
                const accName = (doc.account_name || '').toLowerCase();
                const notes = (doc.notes || '').toLowerCase();
                if (!docNum.includes(q) && !accName.includes(q) && !notes.includes(q)) {
                    return false;
                }
            }
            return true;
        });
    }

    // Type Badge Helper
    function getTypeBadge(type) {
        if (type === 'invoice') {
            return `<span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800">INVOICE</span>`;
        }
        if (type === 'po') {
            return `<span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200 dark:bg-purple-950/40 dark:text-purple-300 dark:border-purple-800">PURCHASE ORDER</span>`;
        }
        return `<span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800">QUOTATION</span>`;
    }

    // Status Badge Helper
    function getStatusBadge(status) {
        const badges = {
            draft: 'bg-zinc-100 text-zinc-700 border-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:border-zinc-700',
            pending_approval: 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800',
            approved: 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-950/40 dark:text-indigo-300 dark:border-indigo-800',
            issued: 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800',
            sent: 'bg-sky-50 text-sky-700 border-sky-200 dark:bg-sky-950/40 dark:text-sky-300 dark:border-sky-800',
            partially_paid: 'bg-fuchsia-50 text-fuchsia-700 border-fuchsia-200 dark:bg-fuchsia-950/40 dark:text-fuchsia-300 dark:border-fuchsia-800',
            paid: 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800',
            overdue: 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800',
            void: 'bg-zinc-100 text-zinc-500 border-zinc-200 dark:bg-zinc-800 dark:text-zinc-400 dark:border-zinc-700',
        };
        const cls = badges[status] || 'bg-zinc-100 text-zinc-700 border-zinc-200';
        const label = STAGE_LABELS[status] || status;
        return `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold border ${cls}">● ${label}</span>`;
    }

    // Render View 1: Compact Table
    function renderTableView() {
        const theadRow = document.getElementById('doc-table-header-row');
        const tbody = document.getElementById('doc-table-tbody');
        const emptyState = document.getElementById('doc-empty-state-table');
        if (!theadRow || !tbody) return;

        const visibleCols = ALL_COLUMNS.filter(c => state.tableVisibleColumns.includes(c.key));

        // Header
        let thHtml = '';
        visibleCols.forEach(c => {
            const isRight = ['grand_total', 'balance_due', 'actions'].includes(c.key);
            thHtml += `<th class="py-3 px-4 ${isRight ? 'text-right' : ''}">${c.label}</th>`;
        });
        theadRow.innerHTML = thHtml;

        // Rows
        const docs = getFilteredDocuments();

        if (docs.length === 0) {
            tbody.innerHTML = '';
            if (emptyState) emptyState.classList.remove('hidden');
            return;
        }

        if (emptyState) emptyState.classList.add('hidden');

        let rowsHtml = '';
        docs.forEach(doc => {
            rowsHtml += `<tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors cursor-pointer group" onclick="window.openDocumentPreviewModal(${doc.id})">`;

            visibleCols.forEach(col => {
                if (col.key === 'document_number') {
                    rowsHtml += `
                        <td class="py-3 px-4 font-mono font-bold text-zinc-900 dark:text-zinc-100">
                            <span class="px-2 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 group-hover:text-indigo-600 transition-colors no-underline hover:no-underline">
                                ${doc.document_number}
                            </span>
                        </td>
                    `;
                } else if (col.key === 'document_type') {
                    rowsHtml += `<td class="py-3 px-4">${getTypeBadge(doc.document_type)}</td>`;
                } else if (col.key === 'account_name') {
                    rowsHtml += `
                        <td class="py-3 px-4">
                            <div class="font-semibold text-zinc-900 dark:text-zinc-100 line-clamp-1">${doc.account_name}</div>
                            <div class="text-[10px] text-zinc-400">Tpl: ${doc.template_name || 'Standard'}</div>
                        </td>
                    `;
                } else if (col.key === 'issue_date') {
                    rowsHtml += `<td class="py-3 px-4 text-zinc-600 dark:text-zinc-400">${doc.issue_date_formatted || doc.issue_date}</td>`;
                } else if (col.key === 'due_date') {
                    rowsHtml += `<td class="py-3 px-4 text-zinc-600 dark:text-zinc-400">${doc.due_date_formatted || doc.due_date || '—'}</td>`;
                } else if (col.key === 'grand_total') {
                    rowsHtml += `<td class="py-3 px-4 text-right font-bold text-zinc-950 dark:text-white">${doc.grand_total_formatted || ('$' + Number(doc.grand_total).toFixed(2))}</td>`;
                } else if (col.key === 'balance_due') {
                    const balance = Number(doc.balance_due || 0);
                    const isDue = balance > 0 && doc.status !== 'void';
                    rowsHtml += `
                        <td class="py-3 px-4 text-right font-semibold ${isDue ? 'text-red-600 dark:text-red-400' : 'text-zinc-400'}">
                            ${doc.balance_due_formatted || ('$' + balance.toFixed(2))}
                        </td>
                    `;
                } else if (col.key === 'status') {
                    rowsHtml += `<td class="py-3 px-4">${getStatusBadge(doc.status)}</td>`;
                } else if (col.key === 'governance') {
                    rowsHtml += `
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-1.5">
                                ${doc.is_locked ? `<span title="Locked by governance" class="text-amber-600"><i data-lucide="lock" class="w-3.5 h-3.5"></i></span>` : ''}
                                <span title="Vault Archive" class="text-indigo-600"><i data-lucide="archive" class="w-3.5 h-3.5"></i></span>
                            </div>
                        </td>
                    `;
                } else if (col.key === 'actions') {
                    rowsHtml += `
                        <td class="py-3 px-4 text-right" onclick="event.stopPropagation()">
                            <div class="flex items-center justify-end gap-1">
                                <button type="button" onclick="window.openDocumentPreviewModal(${doc.id})" class="p-1 rounded text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 cursor-pointer" title="Preview">
                                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                </button>
                                <button type="button" onclick="window.openDocumentPreviewModal(${doc.id}, true)" class="p-1 rounded text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 cursor-pointer" title="Print">
                                    <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                </button>
                                ${!doc.is_locked ? `
                                    <button type="button" onclick="window.deleteDocument(${doc.id})" class="p-1 rounded text-red-400 hover:text-red-600 cursor-pointer" title="Delete">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                ` : ''}
                            </div>
                        </td>
                    `;
                }
            });

            rowsHtml += `</tr>`;
        });

        tbody.innerHTML = rowsHtml;
        if (window.lucide) lucide.createIcons();
    }

    // Render View 2: Universal Kanban
    function renderKanbanView() {
        const board = document.getElementById('doc-kanban-board');
        if (!board) return;

        const docs = getFilteredDocuments();
        let boardHtml = '';

        KANBAN_STAGES.forEach(stage => {
            const stageDocs = docs.filter(d => d.status === stage);

            boardHtml += `
                <div
                    class="bg-zinc-50/80 dark:bg-zinc-900/60 rounded-xl border border-zinc-200 dark:border-zinc-800 p-3 space-y-3 min-h-[450px] flex flex-col"
                    ondragover="event.preventDefault()"
                    ondrop="window.handleDocumentDrop(event, '${stage}')"
                >
                    <div class="flex items-center justify-between pb-2 border-b border-zinc-200 dark:border-zinc-800 shrink-0">
                        <span class="text-xs font-bold text-zinc-900 dark:text-zinc-100">${STAGE_LABELS[stage]}</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-zinc-200 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-300">
                            ${stageDocs.length}
                        </span>
                    </div>

                    <div class="space-y-2.5 flex-1 overflow-y-auto max-h-[70vh]">
            `;

            if (stageDocs.length === 0) {
                boardHtml += `
                    <div class="py-8 text-center text-zinc-400 text-xs italic border border-dashed border-zinc-200 dark:border-zinc-800 rounded-lg">
                        Empty
                    </div>
                `;
            } else {
                stageDocs.forEach(d => {
                    boardHtml += `
                        <div
                            class="bg-white dark:bg-zinc-800 rounded-xl p-3 border border-zinc-200 dark:border-zinc-700 shadow-2xs hover:shadow-md transition-all cursor-grab active:cursor-grabbing space-y-2"
                            draggable="true"
                            ondragstart="window.handleDocumentDragStart(event, ${d.id})"
                            onclick="window.openDocumentPreviewModal(${d.id})"
                        >
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-mono font-bold text-[10px] px-2 py-0.5 rounded bg-zinc-100 dark:bg-zinc-700 text-zinc-800 dark:text-zinc-200 border border-zinc-200 dark:border-zinc-600">
                                    ${d.document_number}
                                </span>
                                ${getTypeBadge(d.document_type)}
                            </div>

                            <div class="text-xs font-semibold text-zinc-900 dark:text-zinc-100 line-clamp-1">
                                ${d.account_name}
                            </div>

                            <div class="flex items-center justify-between pt-1 border-t border-zinc-100 dark:border-zinc-700/60 text-xs">
                                <span class="font-bold text-zinc-950 dark:text-white">${d.grand_total_formatted}</span>
                                <span class="text-[10px] text-zinc-400">Due: ${d.due_date_formatted || '—'}</span>
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

    // Refresh KPI Counts
    function updateCounts() {
        const counts = {
            all: state.documents.length,
            invoice: state.documents.filter(d => d.document_type === 'invoice').length,
            po: state.documents.filter(d => d.document_type === 'po').length,
            quote: state.documents.filter(d => d.document_type === 'quote').length,
        };

        const elAll = document.getElementById('count-type-all');
        const elInv = document.getElementById('count-type-invoice');
        const elPo = document.getElementById('count-type-po');
        const elQt = document.getElementById('count-type-quote');

        if (elAll) elAll.textContent = counts.all;
        if (elInv) elInv.textContent = counts.invoice;
        if (elPo) elPo.textContent = counts.po;
        if (elQt) elQt.textContent = counts.quote;
    }

    // Switch View Mode (Table vs Kanban)
    window.switchDocumentView = function (mode) {
        state.viewMode = mode;
        const btnTable = document.getElementById('doc-view-btn-table');
        const btnKanban = document.getElementById('doc-view-btn-kanban');
        const containerTable = document.getElementById('doc-container-table');
        const containerKanban = document.getElementById('doc-container-kanban');

        if (mode === 'table') {
            if (btnTable) {
                btnTable.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-md transition-all cursor-pointer bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white shadow-2xs';
            }
            if (btnKanban) {
                btnKanban.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-md transition-all cursor-pointer text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white';
            }
            if (containerTable) containerTable.classList.remove('hidden');
            if (containerKanban) containerKanban.classList.add('hidden');
            renderTableView();
        } else {
            if (btnKanban) {
                btnKanban.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-md transition-all cursor-pointer bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white shadow-2xs';
            }
            if (btnTable) {
                btnTable.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-md transition-all cursor-pointer text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white';
            }
            if (containerKanban) containerKanban.classList.remove('hidden');
            if (containerTable) containerTable.classList.add('hidden');
            renderKanbanView();
        }

        if (window.lucide) lucide.createIcons();
    };

    // Switch Type Filter Tabs
    window.switchDocumentTypeTab = function (type) {
        state.selectedType = type;
        ['all', 'invoice', 'po', 'quote'].forEach(t => {
            const btn = document.getElementById(`doc-type-tab-${t}`);
            if (btn) {
                if (t === type) {
                    btn.className = 'px-2.5 py-1 text-xs font-semibold rounded-md transition-all cursor-pointer bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white shadow-2xs';
                } else {
                    btn.className = 'px-2.5 py-1 text-xs font-medium rounded-md transition-all cursor-pointer text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white';
                }
            }
        });

        if (state.viewMode === 'table') {
            renderTableView();
        } else {
            renderKanbanView();
        }
    };

    // Handle Search
    window.handleDocumentSearch = function (val) {
        state.searchTerm = val.trim();
        if (state.viewMode === 'table') {
            renderTableView();
        } else {
            renderKanbanView();
        }
    };

    // Drag and Drop in Kanban
    let draggedDocId = null;
    window.handleDocumentDragStart = function (e, id) {
        draggedDocId = id;
        e.dataTransfer.setData('text/plain', String(id));
    };

    window.handleDocumentDrop = async function (e, newStatus) {
        e.preventDefault();
        const id = draggedDocId;
        if (!id) return;

        const doc = state.documents.find(d => String(d.id) === String(id));
        if (!doc || doc.status === newStatus) return;

        const oldStatus = doc.status;
        doc.status = newStatus;
        renderKanbanView();

        try {
            const res = await fetch(`/api/documents/${id}/status`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify({ status: newStatus }),
            });
            const data = await res.json();
            if (data.success) {
                showNotification(`Document moved to ${STAGE_LABELS[newStatus] || newStatus}`);
            } else {
                doc.status = oldStatus;
                renderKanbanView();
                showNotification(data.message || 'Failed to update status', 'error');
            }
        } catch (err) {
            console.error('Status update failed:', err);
            doc.status = oldStatus;
            renderKanbanView();
            showNotification('Error updating status', 'error');
        }
    };

    // ==========================================
    // MODAL: GENERATE DOCUMENT
    // ==========================================

    window.openGenerateDocumentModal = function (type = 'invoice') {
        const modal = document.getElementById('generate-document-modal');
        if (!modal) return;

        document.getElementById('gen-doc-type').value = type;
        window.handleGenerateDocTypeChange(type);

        // Fetch Next Sequential Number
        fetch(`/api/documents/next-number/${type}`)
            .then(res => res.json())
            .then(data => {
                if (data.success && data.next_number) {
                    document.getElementById('gen-doc-number').value = data.next_number;
                }
            })
            .catch(() => {});

        // Initialize with 1 default line item
        state.modalLineItems = [
            { sku: 'SRV-001', description: 'Enterprise Implementation & Integration', quantity: 1, unit_price: 1500, tax_rate: 8, discount_rate: 0 }
        ];
        renderModalLineItems();

        modal.classList.remove('hidden');
        if (window.lucide) lucide.createIcons();
    };

    window.closeGenerateDocumentModal = function () {
        const modal = document.getElementById('generate-document-modal');
        if (modal) modal.classList.add('hidden');
    };

    window.handleGenerateDocTypeChange = function (type) {
        const select = document.getElementById('gen-doc-template');
        if (!select) return;

        Array.from(select.options).forEach(opt => {
            const t = opt.getAttribute('data-type');
            if (t === type) {
                opt.hidden = false;
                opt.selected = true;
            } else {
                opt.hidden = true;
            }
        });
    };

    window.handleGenerateAccountChange = function (accId) {
        const select = document.getElementById('gen-doc-account');
        const selectedOpt = select?.options[select.selectedIndex];
        if (selectedOpt) {
            const billing = selectedOpt.getAttribute('data-billing') || '';
            const shipping = selectedOpt.getAttribute('data-shipping') || '';
            const bInput = document.getElementById('gen-doc-billing-addr');
            const sInput = document.getElementById('gen-doc-shipping-addr');
            if (bInput && billing) bInput.value = billing;
            if (sInput && shipping) sInput.value = shipping;
        }
    };

    function renderModalLineItems() {
        const tbody = document.getElementById('gen-doc-items-tbody');
        if (!tbody) return;

        let html = '';
        state.modalLineItems.forEach((it, idx) => {
            const qty = Number(it.quantity) || 1;
            const rate = Number(it.unit_price) || 0;
            const disc = Number(it.discount_rate) || 0;
            const tax = Number(it.tax_rate) || 0;

            const sub = qty * rate;
            const lineDisc = sub * (disc / 100);
            const lineTax = (sub - lineDisc) * (tax / 100);
            const total = (sub - lineDisc) + lineTax;

            html += `
                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40">
                    <td class="py-2 px-3">
                        <input type="text" value="${it.description || ''}" oninput="window.updateLineItem(${idx}, 'description', this.value)" placeholder="Description" required class="w-full text-xs rounded border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-2 py-1">
                    </td>
                    <td class="py-2 px-2">
                        <input type="text" value="${it.sku || ''}" oninput="window.updateLineItem(${idx}, 'sku', this.value)" placeholder="SKU" class="w-full text-xs font-mono rounded border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-2 py-1">
                    </td>
                    <td class="py-2 px-2 text-right">
                        <input type="number" step="any" min="0.01" value="${qty}" oninput="window.updateLineItem(${idx}, 'quantity', this.value)" required class="w-full text-right text-xs rounded border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-2 py-1">
                    </td>
                    <td class="py-2 px-2 text-right">
                        <input type="number" step="any" min="0" value="${rate}" oninput="window.updateLineItem(${idx}, 'unit_price', this.value)" required class="w-full text-right text-xs rounded border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-2 py-1">
                    </td>
                    <td class="py-2 px-2 text-right">
                        <input type="number" step="any" min="0" max="100" value="${tax}" oninput="window.updateLineItem(${idx}, 'tax_rate', this.value)" class="w-full text-right text-xs rounded border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-2 py-1">
                    </td>
                    <td class="py-2 px-2 text-right">
                        <input type="number" step="any" min="0" max="100" value="${disc}" oninput="window.updateLineItem(${idx}, 'discount_rate', this.value)" class="w-full text-right text-xs rounded border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-2 py-1">
                    </td>
                    <td class="py-2 px-3 text-right font-bold text-zinc-900 dark:text-zinc-100">
                        $${total.toFixed(2)}
                    </td>
                    <td class="py-2 px-2 text-center">
                        <button type="button" onclick="window.removeLineItemRow(${idx})" class="p-1 rounded text-red-400 hover:text-red-600 cursor-pointer">
                            <i data-lucide="trash" class="w-3.5 h-3.5"></i>
                        </button>
                    </td>
                </tr>
            `;
        });

        tbody.innerHTML = html;
        if (window.lucide) lucide.createIcons();
        window.recomputeModalTotals();
    }

    window.addLineItemRow = function () {
        state.modalLineItems.push({
            sku: '',
            description: '',
            quantity: 1,
            unit_price: 100,
            tax_rate: 8,
            discount_rate: 0
        });
        renderModalLineItems();
    };

    window.removeLineItemRow = function (idx) {
        if (state.modalLineItems.length <= 1) {
            showNotification('At least one line item is required', 'warning');
            return;
        }
        state.modalLineItems.splice(idx, 1);
        renderModalLineItems();
    };

    window.updateLineItem = function (idx, field, val) {
        if (state.modalLineItems[idx]) {
            state.modalLineItems[idx][field] = val;
            window.recomputeModalTotals();
        }
    };

    window.recomputeModalTotals = function () {
        let subtotal = 0;
        let totalTax = 0;

        state.modalLineItems.forEach(it => {
            const qty = Number(it.quantity) || 0;
            const rate = Number(it.unit_price) || 0;
            const disc = Number(it.discount_rate) || 0;
            const tax = Number(it.tax_rate) || 0;

            const lineSub = qty * rate;
            const lineDisc = lineSub * (disc / 100);
            const lineTax = (lineSub - lineDisc) * (tax / 100);

            subtotal += lineSub;
            totalTax += lineTax;
        });

        const docDiscRate = Number(document.getElementById('gen-doc-discount-rate')?.value) || 0;
        const orderDisc = subtotal * (docDiscRate / 100);
        const shipping = Number(document.getElementById('gen-doc-shipping-amount')?.value) || 0;
        const grandTotal = (subtotal - orderDisc) + totalTax + shipping;

        const elSub = document.getElementById('gen-summary-subtotal');
        const elDisc = document.getElementById('gen-summary-discount');
        const elTax = document.getElementById('gen-summary-tax');
        const elGrand = document.getElementById('gen-summary-grandtotal');

        if (elSub) elSub.textContent = '$' + subtotal.toFixed(2);
        if (elDisc) elDisc.textContent = '-$' + orderDisc.toFixed(2);
        if (elTax) elTax.textContent = '$' + totalTax.toFixed(2);
        if (elGrand) elGrand.textContent = '$' + grandTotal.toFixed(2);
    };

    window.saveGenerateDocument = async function (e) {
        e.preventDefault();
        const submitBtn = document.getElementById('btn-submit-generate-doc');

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="inline-block animate-spin mr-1">↻</span> Generating...';
        }

        try {
            const payload = {
                document_type: document.getElementById('gen-doc-type').value,
                template_id: document.getElementById('gen-doc-template').value,
                document_number: document.getElementById('gen-doc-number').value.trim() || null,
                account_id: document.getElementById('gen-doc-account').value || null,
                issue_date: document.getElementById('gen-doc-issue-date').value,
                due_date: document.getElementById('gen-doc-due-date').value || null,
                payment_terms: document.getElementById('gen-doc-payment-terms').value,
                currency: document.getElementById('gen-doc-currency').value,
                discount_rate: document.getElementById('gen-doc-discount-rate').value || 0,
                shipping_amount: document.getElementById('gen-doc-shipping-amount').value || 0,
                billing_address: document.getElementById('gen-doc-billing-addr').value,
                shipping_address: document.getElementById('gen-doc-shipping-addr').value,
                notes: document.getElementById('gen-doc-notes').value,
                line_items: state.modalLineItems.map(it => ({
                    description: it.description,
                    sku: it.sku,
                    quantity: parseFloat(it.quantity) || 1,
                    unit_price: parseFloat(it.unit_price) || 0,
                    tax_rate: parseFloat(it.tax_rate) || 0,
                    discount_rate: parseFloat(it.discount_rate) || 0,
                })),
            };

            const res = await fetch('/api/documents', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify(payload),
            });

            const data = await res.json();
            if (data.success) {
                showNotification(data.message || 'Document successfully generated!');
                state.documents.unshift(data.data);
                window.closeGenerateDocumentModal();
                updateCounts();
                if (state.viewMode === 'table') {
                    renderTableView();
                } else {
                    renderKanbanView();
                }
                window.openDocumentPreviewModal(data.data.id);
            } else {
                showNotification(data.message || 'Failed to generate document', 'error');
            }
        } catch (err) {
            console.error('Error generating document:', err);
            showNotification('Error generating document', 'error');
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i data-lucide="check" class="w-4 h-4"></i><span>Generate &amp; Save Document</span>';
                if (window.lucide) lucide.createIcons();
            }
        }
    };

    // ==========================================
    // MODAL: DOCUMENT PREVIEW & PDF VIEWER
    // ==========================================

    window.openDocumentPreviewModal = function (docId, autoPrint = false) {
        const doc = state.documents.find(d => String(d.id) === String(docId));
        if (!doc) return;

        state.activePreviewDoc = doc;

        document.getElementById('prev-modal-docnumber').textContent = doc.document_number;
        document.getElementById('prev-modal-type-badge').textContent = doc.document_type.toUpperCase();

        document.getElementById('prev-sheet-title').textContent = doc.document_type === 'invoice' ? 'COMMERCIAL TAX INVOICE' : (doc.document_type === 'po' ? 'PURCHASE ORDER' : 'PRICE QUOTATION');
        document.getElementById('prev-sheet-number').textContent = doc.document_number;
        document.getElementById('prev-sheet-status').innerHTML = getStatusBadge(doc.status);
        document.getElementById('prev-sheet-client').textContent = doc.account_name;
        document.getElementById('prev-sheet-billing-addr').textContent = doc.party_details_json?.billing_address || 'Standard Client Billing Office';
        document.getElementById('prev-sheet-issue-date').textContent = doc.issue_date_formatted || doc.issue_date;
        document.getElementById('prev-sheet-due-date').textContent = doc.due_date_formatted || doc.due_date || '—';
        document.getElementById('prev-sheet-terms').textContent = doc.payment_terms || 'Net 30 Days';

        // Line Items
        const itemsTbody = document.getElementById('prev-sheet-items-tbody');
        if (itemsTbody) {
            const items = doc.line_items || [];
            let itHtml = '';
            items.forEach((it, idx) => {
                itHtml += `
                    <tr>
                        <td class="py-2 px-3 text-zinc-400 font-mono text-[11px]">${idx + 1}</td>
                        <td class="py-2 px-3">
                            <div class="font-bold text-zinc-900">${it.description}</div>
                            ${it.sku ? `<span class="text-[10px] text-zinc-400 font-mono">SKU: ${it.sku}</span>` : ''}
                        </td>
                        <td class="py-2 px-3 text-right font-medium">${it.quantity}</td>
                        <td class="py-2 px-3 text-right font-mono">$${Number(it.unit_price).toFixed(2)}</td>
                        <td class="py-2 px-3 text-right text-zinc-500">${it.tax_rate}%</td>
                        <td class="py-2 px-3 text-right font-bold text-zinc-950 font-mono">$${Number(it.total_amount).toFixed(2)}</td>
                    </tr>
                `;
            });
            itemsTbody.innerHTML = itHtml;
        }

        // Totals
        document.getElementById('prev-sheet-subtotal').textContent = '$' + Number(doc.subtotal || 0).toFixed(2);
        document.getElementById('prev-sheet-tax').textContent = '$' + Number(doc.tax_amount || 0).toFixed(2);
        document.getElementById('prev-sheet-shipping').textContent = '$' + Number(doc.shipping_amount || 0).toFixed(2);
        document.getElementById('prev-sheet-grandtotal').textContent = '$' + Number(doc.grand_total || 0).toFixed(2);
        document.getElementById('prev-sheet-paid').textContent = '$' + Number(doc.paid_amount || 0).toFixed(2);
        document.getElementById('prev-sheet-balance').textContent = '$' + Number(doc.balance_due || 0).toFixed(2);
        document.getElementById('prev-sheet-notes').textContent = doc.notes || '';
        document.getElementById('prev-sheet-creator').textContent = doc.created_by_name || 'System Admin';

        // Payment Button visibility
        const payBtn = document.getElementById('prev-btn-record-payment');
        if (payBtn) {
            if (doc.document_type === 'invoice' && Number(doc.balance_due) > 0 && doc.status !== 'void') {
                payBtn.classList.remove('hidden');
            } else {
                payBtn.classList.add('hidden');
            }
        }

        const modal = document.getElementById('document-preview-modal');
        if (modal) modal.classList.remove('hidden');
        if (window.lucide) lucide.createIcons();

        if (autoPrint) {
            setTimeout(() => window.printDocumentPreview(), 300);
        }
    };

    window.closeDocumentPreviewModal = function () {
        const modal = document.getElementById('document-preview-modal');
        if (modal) modal.classList.add('hidden');
    };

    window.printDocumentPreview = function () {
        window.print();
    };

    window.promptRecordPayment = async function () {
        const doc = state.activePreviewDoc;
        if (!doc) return;

        const maxBalance = Number(doc.balance_due) || 0;
        const input = prompt(`Enter payment amount to record against ${doc.document_number} (Balance Due: $${maxBalance.toFixed(2)}):`, maxBalance);
        if (!input) return;

        const amount = parseFloat(input);
        if (isNaN(amount) || amount <= 0) {
            showNotification('Invalid payment amount entered', 'warning');
            return;
        }

        try {
            const res = await fetch(`/api/documents/${doc.id}/record-payment`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify({ amount }),
            });
            const data = await res.json();
            if (data.success) {
                showNotification('Payment successfully recorded!');
                const idx = state.documents.findIndex(d => d.id === doc.id);
                if (idx !== -1) {
                    state.documents[idx] = data.data;
                }
                window.openDocumentPreviewModal(doc.id);
                if (state.viewMode === 'table') {
                    renderTableView();
                } else {
                    renderKanbanView();
                }
            } else {
                showNotification(data.message || 'Payment recording failed', 'error');
            }
        } catch (err) {
            console.error('Error recording payment:', err);
            showNotification('Error recording payment', 'error');
        }
    };

    window.deleteDocument = async function (id) {
        if (!confirm('Are you sure you want to delete this document from the ledger? This action cannot be undone.')) {
            return;
        }

        try {
            const res = await fetch(`/api/documents/${id}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
            });
            const data = await res.json();
            if (data.success) {
                showNotification('Document deleted from ledger.');
                state.documents = state.documents.filter(d => d.id !== id);
                updateCounts();
                if (state.viewMode === 'table') {
                    renderTableView();
                } else {
                    renderKanbanView();
                }
            } else {
                showNotification(data.message || 'Failed to delete document', 'error');
            }
        } catch (err) {
            console.error('Error deleting document:', err);
            showNotification('Error deleting document', 'error');
        }
    };

    // ==========================================
    // MODAL: CUSTOMIZE COLUMNS
    // ==========================================

    window.openDocumentConfigModal = function () {
        const modal = document.getElementById('document-config-modal');
        const container = document.getElementById('document-config-checkboxes');
        if (!modal || !container) return;

        let html = '';
        ALL_COLUMNS.forEach(col => {
            const isChecked = state.tableVisibleColumns.includes(col.key);
            html += `
                <label class="flex items-center gap-2 p-2 rounded-lg hover:bg-zinc-50 dark:hover:bg-zinc-800 cursor-pointer text-xs">
                    <input type="checkbox" data-col-key="${col.key}" ${isChecked ? 'checked' : ''} class="rounded text-indigo-600 focus:ring-indigo-500">
                    <span class="font-medium text-zinc-800 dark:text-zinc-200">${col.label}</span>
                </label>
            `;
        });
        container.innerHTML = html;

        modal.classList.remove('hidden');
    };

    window.closeDocumentConfigModal = function () {
        const modal = document.getElementById('document-config-modal');
        if (modal) modal.classList.add('hidden');
    };

    window.saveDocumentConfig = function () {
        const checkboxes = document.querySelectorAll('#document-config-checkboxes input[data-col-key]');
        const newCols = [];
        checkboxes.forEach(cb => {
            if (cb.checked) newCols.push(cb.getAttribute('data-col-key'));
        });

        if (newCols.length === 0) {
            showNotification('At least one column must be visible', 'warning');
            return;
        }

        state.tableVisibleColumns = newCols;
        try {
            localStorage.setItem('crm_visible_fields_documents_table', JSON.stringify(newCols));
        } catch (_) {}

        window.closeDocumentConfigModal();
        renderTableView();
        showNotification('Columns configuration saved!');
    };

    // ==========================================
    // MODAL: FILTER MODAL
    // ==========================================

    window.openDocumentFilterModal = function () {
        const modal = document.getElementById('document-filter-modal');
        if (!modal) return;
        document.getElementById('filter-doc-status').value = state.statusFilter;
        document.getElementById('filter-doc-account').value = state.accountFilter;
        modal.classList.remove('hidden');
    };

    window.closeDocumentFilterModal = function () {
        const modal = document.getElementById('document-filter-modal');
        if (modal) modal.classList.add('hidden');
    };

    window.applyDocumentFilters = function () {
        state.statusFilter = document.getElementById('filter-doc-status').value;
        state.accountFilter = document.getElementById('filter-doc-account').value;

        const badge = document.getElementById('doc-filter-badge');
        let count = 0;
        if (state.statusFilter) count++;
        if (state.accountFilter) count++;

        if (badge) {
            if (count > 0) {
                badge.textContent = count;
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        }

        window.closeDocumentFilterModal();
        if (state.viewMode === 'table') {
            renderTableView();
        } else {
            renderKanbanView();
        }
    };

    window.resetDocumentFilters = function () {
        state.statusFilter = '';
        state.accountFilter = '';
        const badge = document.getElementById('doc-filter-badge');
        if (badge) badge.classList.add('hidden');
        window.closeDocumentFilterModal();
        if (state.viewMode === 'table') {
            renderTableView();
        } else {
            renderKanbanView();
        }
        showNotification('Filters cleared');
    };

    // Export CSV
    window.exportDocumentsCsv = function () {
        const docs = getFilteredDocuments();
        if (docs.length === 0) {
            showNotification('No records to export', 'warning');
            return;
        }

        const headers = ['Document Number', 'Type', 'Account', 'Issue Date', 'Due Date', 'Grand Total', 'Paid Amount', 'Balance Due', 'Status', 'Notes'];
        const rows = docs.map(d => [
            `"${d.document_number}"`,
            `"${d.document_type}"`,
            `"${(d.account_name || '').replace(/"/g, '""')}"`,
            `"${d.issue_date || ''}"`,
            `"${d.due_date || ''}"`,
            `"${d.grand_total || 0}"`,
            `"${d.paid_amount || 0}"`,
            `"${d.balance_due || 0}"`,
            `"${d.status || ''}"`,
            `"${(d.notes || '').replace(/"/g, '""')}"`
        ]);

        const csvContent = 'data:text/csv;charset=utf-8,' + [headers.join(','), ...rows.map(e => e.join(','))].join('\n');
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement('a');
        link.setAttribute('href', encodedUri);
        link.setAttribute('download', `billing_ledger_export_${new Date().toISOString().slice(0, 10)}.csv`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        showNotification('Exported ledger to CSV');
    };

    // Initialize Module
    window.initDocumentsModule = function () {
        const root = document.getElementById('documents-page-root');
        if (!root) return;

        hydrateState();
        updateCounts();
        renderTableView();

        if (window.lucide) lucide.createIcons();
    };

    document.addEventListener('DOMContentLoaded', () => {
        window.initDocumentsModule();
    });

    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        window.initDocumentsModule();
    }
})();
