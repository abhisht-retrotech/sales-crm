/**
 * DealSync Enterprise CRM — Shipping & Freight Logistics Engine
 * 1:1 Parity with React Client (ShippingView.tsx, CompactDataTable.tsx, UniversalKanban.tsx)
 * Pure Tailwind CSS styling with Dynamic Custom Fields & Field-Level Security Permissions
 */

(function () {
    'use strict';

    // Module State
    const state = {
        shipments: [],
        customFields: [],
        fieldPermissions: {},
        accounts: [],
        opportunities: [],
        purchaseDeals: [],
        users: [],
        stages: ['Pending', 'In Transit', 'Customs Clearance', 'Out for Delivery', 'Delivered'],
        carriers: ['FedEx Freight', 'UPS Express', 'DHL Global', 'Maersk Line', 'Expeditors International'],
        activeDirection: 'all', // 'all' | 'Outbound' | 'Inbound'
        activeStatus: 'all',    // 'all' | stage name
        searchTerm: '',
        viewMode: 'cards', // 'cards' | 'kanban' | 'table'
        tableVisibleFields: ['tracking_number', 'carrier', 'type', 'status', 'account_name', 'estimated_delivery_date'],
        kanbanVisibleFields: ['carrier', 'type', 'account_name', 'estimated_delivery_date'],
        activeFilters: [], // array of { id, field, operator, value }
        selectedShipment: null,
        editingShipment: null,
        transferringShipment: null,
        isCreateMode: false,
        isLoading: false,
    };

    const STANDARD_FIELD_DEFINITIONS = [
        { name: 'tracking_number', label: 'Tracking Code', type: 'text', standard: true, required: false },
        { name: 'carrier', label: 'Freight Carrier', type: 'dropdown', standard: true, required: true, options: ['FedEx Freight', 'UPS Express', 'DHL Global', 'Maersk Line', 'Expeditors International'] },
        { name: 'type', label: 'Logistics Direction', type: 'dropdown', standard: true, required: true, options: ['Outbound', 'Inbound', 'Drop-ship'] },
        { name: 'status', label: 'Shipment Status', type: 'dropdown', standard: true, required: true, options: ['Pending', 'In Transit', 'Customs Clearance', 'Out for Delivery', 'Delivered'] },
        { name: 'account_name', label: 'Linked Account', type: 'lookup', standard: true, required: true },
        { name: 'opportunity_name', label: 'Sales Order', type: 'lookup', standard: true },
        { name: 'po_number', label: 'Purchase PO', type: 'lookup', standard: true },
        { name: 'shipping_origin', label: 'Origin Facility', type: 'text', standard: true },
        { name: 'destination_address', label: 'Destination Address', type: 'textarea', standard: true },
        { name: 'estimated_delivery_date', label: 'Est. Delivery Date', type: 'date', standard: true },
        { name: 'dispatch_date', label: 'Dispatch Date', type: 'date', standard: true },
        { name: 'owner_name', label: 'Coordinator / Owner', type: 'lookup', standard: true },
        { name: 'notes', label: 'Manifest Notes', type: 'textarea', standard: true },
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

    // Status Badges Styling
    function getStatusBadge(status) {
        switch (status) {
            case 'Pending':
                return `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-zinc-100 text-zinc-700 border border-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:border-zinc-700">● Pending</span>`;
            case 'In Transit':
                return `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800">● In Transit</span>`;
            case 'Customs Clearance':
                return `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-50 text-purple-700 border border-purple-200 dark:bg-purple-950/40 dark:text-purple-300 dark:border-purple-800">● Customs</span>`;
            case 'Out for Delivery':
                return `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800">● Out for Delivery</span>`;
            case 'Delivered':
                return `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800">● Delivered</span>`;
            default:
                return `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-zinc-100 text-zinc-700 border border-zinc-200">${status}</span>`;
        }
    }

    function getTypeBadge(type) {
        if (type === 'Outbound') {
            return `<span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800">Outbound</span>`;
        } else if (type === 'Inbound') {
            return `<span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800">Inbound</span>`;
        }
        return `<span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-purple-50 text-purple-700 border border-purple-200 dark:bg-purple-950/40 dark:text-purple-300 dark:border-purple-800">${type}</span>`;
    }

    // Filter Logic
    function getFilteredShipments() {
        let list = state.shipments;

        // Direction filter
        if (state.activeDirection && state.activeDirection !== 'all') {
            list = list.filter(s => s.type === state.activeDirection);
        }

        // Status filter
        if (state.activeStatus && state.activeStatus !== 'all') {
            list = list.filter(s => s.status === state.activeStatus);
        }

        // Search term
        if (state.searchTerm.trim()) {
            const q = state.searchTerm.toLowerCase();
            list = list.filter(s => {
                const trkMatch = s.tracking_number && s.tracking_number.toLowerCase().includes(q);
                const carrierMatch = s.carrier && s.carrier.toLowerCase().includes(q);
                const accMatch = s.account_name && s.account_name.toLowerCase().includes(q);
                const originMatch = s.shipping_origin && s.shipping_origin.toLowerCase().includes(q);
                const destMatch = s.destination_address && s.destination_address.toLowerCase().includes(q);
                const notesMatch = s.notes && s.notes.toLowerCase().includes(q);
                return trkMatch || carrierMatch || accMatch || originMatch || destMatch || notesMatch;
            });
        }

        // Advanced filter rules
        if (state.activeFilters.length > 0) {
            list = list.filter(ship => {
                return state.activeFilters.every(rule => {
                    const rawVal = ship[rule.field];
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

    // Render View 1: Manifests / Progress Cards View
    function renderCardsView() {
        const grid = document.getElementById('shipping-cards-grid');
        const emptyState = document.getElementById('shipping-empty-state-cards');
        const summary = document.getElementById('shipping-summary-text');
        if (!grid) return;

        const shipments = getFilteredShipments();

        if (summary) {
            summary.innerText = `Showing ${shipments.length} of ${state.shipments.length} shipments`;
        }

        if (shipments.length === 0) {
            grid.innerHTML = '';
            if (emptyState) emptyState.classList.remove('hidden');
            return;
        }

        if (emptyState) emptyState.classList.add('hidden');

        let html = '';
        shipments.forEach(ship => {
            const currentStep = state.stages.indexOf(ship.status);

            html += `
                <div
                    class="bg-white dark:bg-zinc-900 rounded-2xl p-5 border border-zinc-200 dark:border-zinc-800 shadow-xs hover:shadow-md transition-all flex flex-col justify-between space-y-4 group cursor-pointer"
                    onclick="window.location.href='/shipping/${ship.id}'"
                >
                    <!-- Card Top Header -->
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                                ${getTypeBadge(ship.type)}
                                <span class="font-bold text-sm text-zinc-950 dark:text-white">
                                    ${ship.carrier}
                                </span>
                            </div>
                            <a
                                href="/shipping/${ship.id}"
                                onclick="event.stopPropagation()"
                                class="inline-block font-mono font-bold text-xs px-2.5 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 border border-zinc-200 dark:border-zinc-700 hover:text-indigo-600 no-underline hover:no-underline"
                            >
                                ${ship.tracking_number}
                            </a>
                        </div>
                        <div class="shrink-0">
                            ${getStatusBadge(ship.status)}
                        </div>
                    </div>

                    <!-- Linked Entity Card Box -->
                    <div class="bg-zinc-50 dark:bg-zinc-800/40 rounded-xl p-3 border border-zinc-200/70 dark:border-zinc-800 space-y-1.5 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-zinc-500">Account:</span>
                            <strong class="text-zinc-900 dark:text-zinc-100 font-semibold">${ship.account_name}</strong>
                        </div>
                        ${ship.opportunity_name ? `
                            <div class="flex items-center justify-between">
                                <span class="text-zinc-500">Sales Order:</span>
                                <span class="text-zinc-800 dark:text-zinc-200 font-medium">${ship.opportunity_name}</span>
                            </div>
                        ` : ''}
                        ${ship.po_number ? `
                            <div class="flex items-center justify-between">
                                <span class="text-zinc-500">Purchase PO:</span>
                                <span class="text-zinc-800 dark:text-zinc-200 font-medium font-mono">${ship.po_number}</span>
                            </div>
                        ` : ''}
                    </div>

                    <!-- Progress Stepper (5 Steps) -->
                    <div class="relative pt-2 pb-1">
                        <div class="absolute top-5 left-4 right-4 h-0.5 bg-zinc-200 dark:bg-zinc-700 -z-0"></div>
                        <div class="grid grid-cols-5 gap-1 relative z-10 text-center">
                            ${state.stages.map((st, idx) => {
                                const isDone = currentStep >= idx;
                                const isCurrent = currentStep === idx;
                                return `
                                    <div class="flex flex-col items-center gap-1">
                                        <div class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold ${isDone ? 'bg-zinc-950 dark:bg-zinc-100 text-white dark:text-zinc-900 shadow-2xs' : 'bg-white dark:bg-zinc-800 border-2 border-zinc-300 dark:border-zinc-600 text-zinc-400'}">
                                            ${isDone ? '✓' : idx + 1}
                                        </div>
                                        <span class="text-[9px] ${isCurrent ? 'font-bold text-indigo-600 dark:text-indigo-400' : (isDone ? 'font-semibold text-zinc-800 dark:text-zinc-200' : 'text-zinc-400')} truncate w-full">
                                            ${st}
                                        </span>
                                    </div>
                                `;
                            }).join('')}
                        </div>
                    </div>

                    <!-- Route Details -->
                    <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800 text-xs text-zinc-600 dark:text-zinc-400 space-y-1.5">
                        <div class="flex items-center gap-2">
                            <i data-lucide="map-pin" class="w-3.5 h-3.5 text-zinc-400 shrink-0"></i>
                            <span class="truncate">Origin: <strong class="text-zinc-800 dark:text-zinc-200 font-medium">${ship.shipping_origin || 'Central Hub'}</strong></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i data-lucide="navigation" class="w-3.5 h-3.5 text-zinc-400 shrink-0"></i>
                            <span class="truncate">Dest: <strong class="text-zinc-800 dark:text-zinc-200 font-medium">${ship.destination_address || 'Delivery Site'}</strong></span>
                        </div>
                    </div>

                    <!-- Card Footer Actions -->
                    <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-between" onclick="event.stopPropagation()">
                        <span class="text-[11px] text-zinc-400">
                            Est: <strong>${ship.estimated_delivery_date_formatted || 'Pending'}</strong>
                        </span>
                        <div class="flex items-center gap-2">
                            ${ship.status !== 'In Transit' && ship.status !== 'Delivered' ? `
                                <button
                                    type="button"
                                    onclick="window.quickUpdateShipmentStatus(${ship.id}, 'In Transit')"
                                    class="px-2.5 py-1 text-xs font-semibold rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs cursor-pointer"
                                >
                                    Mark In Transit
                                </button>
                            ` : ''}
                            ${ship.status !== 'Delivered' ? `
                                <button
                                    type="button"
                                    onclick="window.quickUpdateShipmentStatus(${ship.id}, 'Delivered')"
                                    class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white transition-colors shadow-2xs cursor-pointer flex items-center gap-1"
                                >
                                    <i data-lucide="check-circle-2" class="w-3 h-3"></i>
                                    <span>Mark Delivered</span>
                                </button>
                            ` : ''}
                            <button
                                type="button"
                                onclick="window.openShipmentModal('edit', ${ship.id})"
                                class="p-1 rounded text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 cursor-pointer"
                                title="Edit Shipment"
                            >
                                <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });

        grid.innerHTML = html;
        if (window.lucide) lucide.createIcons();
    }

    // Render View 2: Kanban View
    function renderKanbanView() {
        const board = document.getElementById('shipping-kanban-board');
        if (!board) return;

        const shipments = getFilteredShipments();

        let boardHtml = '';

        state.stages.forEach(stage => {
            const stageShips = shipments.filter(s => s.status === stage);

            boardHtml += `
                <div
                    class="kanban-col bg-zinc-50/80 dark:bg-zinc-900/60 rounded-xl border border-zinc-200 dark:border-zinc-800 p-3 space-y-3 min-h-[420px] flex flex-col"
                    data-status="${stage}"
                    ondragover="window.handleShippingDragOver(event)"
                    ondrop="window.handleShippingDrop(event, '${stage}')"
                >
                    <!-- Column Header -->
                    <div class="flex items-center justify-between pb-2 border-b border-zinc-200 dark:border-zinc-800 shrink-0">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-zinc-900 dark:text-zinc-100">${stage}</span>
                            <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-zinc-200 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-300">
                                ${stageShips.length}
                            </span>
                        </div>
                    </div>

                    <!-- Cards Container -->
                    <div class="space-y-2.5 flex-1 kanban-cards-wrapper overflow-y-auto max-h-[70vh]">
            `;

            if (stageShips.length === 0) {
                boardHtml += `
                    <div class="py-8 text-center text-zinc-400 text-xs italic border border-dashed border-zinc-200 dark:border-zinc-800 rounded-lg">
                        No shipments in ${stage}
                    </div>
                `;
            } else {
                stageShips.forEach(ship => {
                    boardHtml += `
                        <div
                            class="bg-white dark:bg-zinc-800 rounded-xl p-3.5 border border-zinc-200 dark:border-zinc-700 shadow-2xs hover:shadow-md transition-all cursor-grab active:cursor-grabbing space-y-2.5"
                            draggable="true"
                            ondragstart="window.handleShippingDragStart(event, ${ship.id})"
                        >
                            <div class="flex items-start justify-between gap-2">
                                <span class="font-mono font-bold text-[10px] px-2 py-0.5 rounded bg-zinc-100 dark:bg-zinc-700 text-zinc-800 dark:text-zinc-200 border border-zinc-200 dark:border-zinc-600 shrink-0">
                                    ${ship.tracking_number}
                                </span>
                                ${getTypeBadge(ship.type)}
                            </div>

                            <a
                                href="/shipping/${ship.id}"
                                class="text-xs font-bold text-zinc-950 dark:text-zinc-100 hover:text-indigo-600 no-underline hover:no-underline line-clamp-1 block"
                            >
                                ${ship.carrier}
                            </a>

                            <div class="text-[11px] text-zinc-500">
                                Account: <strong class="text-zinc-800 dark:text-zinc-200">${ship.account_name}</strong>
                            </div>

                            ${ship.estimated_delivery_date ? `
                                <div class="text-[10px] text-zinc-400 flex items-center gap-1">
                                    <i data-lucide="calendar" class="w-3 h-3"></i>
                                    <span>Est: ${ship.estimated_delivery_date_formatted || ship.estimated_delivery_date}</span>
                                </div>
                            ` : ''}

                            <div class="flex items-center justify-between pt-1 border-t border-zinc-100 dark:border-zinc-700/60 text-[10px] text-zinc-400">
                                <span>${ship.shipping_origin ? ship.shipping_origin.split(',')[0] : 'Origin'}</span>
                                <i data-lucide="arrow-right" class="w-3 h-3 text-zinc-400"></i>
                                <span>${ship.destination_address ? ship.destination_address.split(',')[0] : 'Dest'}</span>
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

    // Render View 3: Table View
    function renderTableView() {
        const theadRow = document.getElementById('shipping-table-header-row');
        const tbody = document.getElementById('shipping-table-tbody');
        const emptyState = document.getElementById('shipping-empty-state-table');
        if (!theadRow || !tbody) return;

        const visibleDefs = getVisibleFieldDefinitions();
        const defMap = new Map(visibleDefs.map(d => [d.name, d]));

        const activeCols = state.tableVisibleFields.filter(key => {
            const def = defMap.get(key);
            return def && def.permission !== 'Hidden';
        });

        // 1. Header
        let headerHtml = `
            <th class="py-3 px-4 w-10 text-center">
                <input type="checkbox" onchange="window.toggleSelectAllShipments(this.checked)" class="rounded text-indigo-600 focus:ring-indigo-500 cursor-pointer">
            </th>
        `;

        activeCols.forEach(colKey => {
            const def = defMap.get(colKey);
            const label = def ? def.label : colKey;
            headerHtml += `<th class="py-3 px-4">${label}</th>`;
        });

        headerHtml += `<th class="py-3 px-4 text-right">Actions</th>`;
        theadRow.innerHTML = headerHtml;

        // 2. Rows
        const shipments = getFilteredShipments();

        if (shipments.length === 0) {
            tbody.innerHTML = '';
            if (emptyState) emptyState.classList.remove('hidden');
            return;
        }

        if (emptyState) emptyState.classList.add('hidden');

        let rowsHtml = '';
        shipments.forEach(ship => {
            rowsHtml += `
                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors group">
                    <td class="py-3 px-4 text-center">
                        <input type="checkbox" data-ship-id="${ship.id}" class="rounded text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                    </td>
            `;

            activeCols.forEach(colKey => {
                let cellVal = ship[colKey];

                if (colKey === 'tracking_number') {
                    rowsHtml += `
                        <td class="py-3 px-4 font-semibold text-zinc-900 dark:text-zinc-100">
                            <a href="/shipping/${ship.id}" class="hover:text-indigo-600 no-underline hover:no-underline flex items-center gap-2">
                                <span class="font-mono font-bold text-[11px] px-2 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-200 border border-zinc-200 dark:border-zinc-700">
                                    ${cellVal}
                                </span>
                            </a>
                        </td>
                    `;
                } else if (colKey === 'carrier') {
                    rowsHtml += `
                        <td class="py-3 px-4 font-semibold text-zinc-900 dark:text-zinc-100 flex items-center gap-1.5">
                            <i data-lucide="truck" class="w-3.5 h-3.5 text-zinc-400"></i>
                            <span>${cellVal}</span>
                        </td>
                    `;
                } else if (colKey === 'type') {
                    rowsHtml += `<td class="py-3 px-4">${getTypeBadge(cellVal)}</td>`;
                } else if (colKey === 'status') {
                    rowsHtml += `<td class="py-3 px-4">${getStatusBadge(cellVal)}</td>`;
                } else if (colKey === 'account_name') {
                    rowsHtml += `
                        <td class="py-3 px-4 text-zinc-600 dark:text-zinc-300 font-medium">
                            <span class="flex items-center gap-1.5">
                                <i data-lucide="building-2" class="w-3.5 h-3.5 text-zinc-400"></i>
                                <span>${cellVal || 'Unlinked'}</span>
                            </span>
                        </td>
                    `;
                } else if (colKey === 'estimated_delivery_date') {
                    rowsHtml += `
                        <td class="py-3 px-4 text-zinc-600 dark:text-zinc-300">
                            ${ship.estimated_delivery_date_formatted || cellVal || 'Pending'}
                        </td>
                    `;
                } else {
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
                            href="/shipping/${ship.id}"
                            class="p-1 rounded text-zinc-500 hover:text-indigo-600 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors"
                            title="View Shipment 360"
                        >
                            <i data-lucide="eye" class="w-4 h-4"></i>
                        </a>
                        <button
                            type="button"
                            onclick="window.openShipmentModal('edit', ${ship.id})"
                            class="p-1 rounded text-zinc-500 hover:text-indigo-600 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
                            title="Edit"
                        >
                            <i data-lucide="pencil" class="w-4 h-4"></i>
                        </button>
                        <button
                            type="button"
                            onclick="window.openShippingTransferModal(${ship.id})"
                            class="p-1 rounded text-zinc-500 hover:text-indigo-600 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
                            title="Transfer Owner"
                        >
                            <i data-lucide="user-check" class="w-4 h-4"></i>
                        </button>
                        <button
                            type="button"
                            onclick="window.deleteShipmentRecord(${ship.id})"
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

    // Update KPI Header Cards
    function updateKpiCards() {
        const ships = state.shipments;
        const inTransit = ships.filter(s => s.status === 'In Transit');
        const customs = ships.filter(s => s.status === 'Customs Clearance');
        const delivered = ships.filter(s => s.status === 'Delivered');

        const elInTransit = document.getElementById('kpi-in-transit-count');
        const elCustoms = document.getElementById('kpi-customs-count');
        const elDelivered = document.getElementById('kpi-delivered-count');

        if (elInTransit) elInTransit.innerText = `${inTransit.length} En Route`;
        if (elCustoms) elCustoms.innerText = `${customs.length} Manifests`;
        if (elDelivered) elDelivered.innerText = `${delivered.length} Delivered`;

        // Update pill counts
        const countAll = document.getElementById('count-all');
        const countOutbound = document.getElementById('count-outbound');
        const countInbound = document.getElementById('count-inbound');

        if (countAll) countAll.innerText = ships.length;
        if (countOutbound) countOutbound.innerText = ships.filter(s => s.type === 'Outbound').length;
        if (countInbound) countInbound.innerText = ships.filter(s => s.type === 'Inbound').length;

        state.stages.forEach(st => {
            const slug = st.toLowerCase().replace(/[^a-z0-9]/g, '-');
            const el = document.getElementById(`count-status-${slug}`);
            if (el) {
                el.innerText = ships.filter(s => s.status === st).length;
            }
        });
    }

    // Switch View (Cards vs Kanban vs Table)
    window.switchShippingView = function (mode) {
        state.viewMode = mode;
        const btnCards = document.getElementById('btn-view-cards');
        const btnKanban = document.getElementById('btn-view-kanban');
        const btnTable = document.getElementById('btn-view-table');

        const viewCards = document.getElementById('view-cards-container');
        const viewKanban = document.getElementById('view-kanban-container');
        const viewTable = document.getElementById('view-table-container');

        const activeClass = 'inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-md transition-all cursor-pointer bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white shadow-2xs';
        const inactiveClass = 'inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-md transition-all cursor-pointer text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white';

        btnCards.className = (mode === 'cards') ? activeClass : inactiveClass;
        btnKanban.className = (mode === 'kanban') ? activeClass : inactiveClass;
        btnTable.className = (mode === 'table') ? activeClass : inactiveClass;

        viewCards.classList.add('hidden');
        viewKanban.classList.add('hidden');
        viewTable.classList.add('hidden');

        if (mode === 'cards') {
            viewCards.classList.remove('hidden');
            renderCardsView();
        } else if (mode === 'kanban') {
            viewKanban.classList.remove('hidden');
            renderKanbanView();
        } else {
            viewTable.classList.remove('hidden');
            renderTableView();
        }

        if (window.lucide) lucide.createIcons();
    };

    // Filter by Direction (All, Outbound, Inbound)
    window.filterByDirection = function (dir) {
        state.activeDirection = dir;
        state.activeStatus = 'all';

        document.querySelectorAll('.ship-filter-pill').forEach(btn => {
            const btnVal = btn.getAttribute('data-filter-val');
            if (btnVal === dir) {
                btn.className = 'ship-filter-pill px-3 py-1 rounded-full font-semibold transition-all cursor-pointer bg-zinc-900 dark:bg-zinc-100 text-white dark:text-zinc-900 shadow-xs';
            } else {
                btn.className = 'ship-filter-pill px-3 py-1 rounded-full font-medium transition-all cursor-pointer bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700';
            }
        });

        // Reset status pills
        document.querySelectorAll('.ship-status-pill').forEach(btn => {
            btn.className = 'ship-status-pill px-3 py-1 rounded-full font-medium transition-all cursor-pointer bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700';
        });

        renderCurrentView();
    };

    // Filter by Status
    window.filterByStatus = function (st) {
        state.activeStatus = st;
        document.querySelectorAll('.ship-status-pill').forEach(btn => {
            const btnVal = btn.getAttribute('data-filter-val');
            if (btnVal === st) {
                btn.className = 'ship-status-pill px-3 py-1 rounded-full font-semibold transition-all cursor-pointer bg-zinc-900 dark:bg-zinc-100 text-white dark:text-zinc-900 shadow-xs';
            } else {
                btn.className = 'ship-status-pill px-3 py-1 rounded-full font-medium transition-all cursor-pointer bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700';
            }
        });

        renderCurrentView();
    };

    function renderCurrentView() {
        if (state.viewMode === 'cards') {
            renderCardsView();
        } else if (state.viewMode === 'kanban') {
            renderKanbanView();
        } else {
            renderTableView();
        }
    }

    // Search Input
    window.handleShippingSearchInput = function (val) {
        state.searchTerm = val;
        renderCurrentView();
    };

    // Kanban Drag and Drop
    window.handleShippingDragStart = function (e, shipId) {
        e.dataTransfer.setData('text/plain', String(shipId));
        e.dataTransfer.effectAllowed = 'move';
    };

    window.handleShippingDragOver = function (e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
    };

    window.handleShippingDrop = async function (e, targetStatus) {
        e.preventDefault();
        const shipId = e.dataTransfer.getData('text/plain');
        if (!shipId) return;

        window.quickUpdateShipmentStatus(shipId, targetStatus);
    };

    // Quick Update Status
    window.quickUpdateShipmentStatus = async function (shipId, newStatus) {
        const ship = state.shipments.find(s => String(s.id) === String(shipId));
        if (!ship || ship.status === newStatus) return;

        const oldStatus = ship.status;
        ship.status = newStatus;
        renderCurrentView();
        updateKpiCards();

        try {
            const res = await fetch(`/api/shipping/${shipId}/status`, {
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
                showNotification(`Shipment ${ship.tracking_number} updated to ${newStatus}!`);
                if (data.shipment) {
                    const idx = state.shipments.findIndex(s => String(s.id) === String(shipId));
                    if (idx !== -1) state.shipments[idx] = data.shipment;
                }
            } else {
                ship.status = oldStatus;
                renderCurrentView();
                updateKpiCards();
                showNotification(data.message || 'Failed to update status', 'error');
            }
        } catch (err) {
            console.error('Error updating shipping status:', err);
            ship.status = oldStatus;
            renderCurrentView();
            updateKpiCards();
            showNotification('Error updating shipment status', 'error');
        }
    };

    // Modal: Open Create / Edit Shipment
    window.openShipmentModal = function (mode, shipId = null) {
        state.isCreateMode = (mode === 'create');
        const modal = document.getElementById('shipment-modal');
        const heading = document.getElementById('modal-shipment-heading');
        const form = document.getElementById('shipment-form');
        const customContainer = document.getElementById('modal-custom-fields-container');
        if (!modal || !form) return;

        form.reset();
        document.getElementById('shipment-id').value = '';

        let ship = null;
        if (shipId) {
            ship = state.shipments.find(s => s.id === shipId);
        }

        if (state.isCreateMode || !ship) {
            heading.innerText = 'New Freight Shipment Manifest';
            const rand = Math.floor(100000 + Math.random() * 900000);
            document.getElementById('field-tracking_number').value = `TRK-${rand}-US`;
            document.getElementById('field-carrier').value = 'FedEx Freight';
            document.getElementById('field-type').value = 'Outbound';
            document.getElementById('field-status').value = 'Pending';
        } else {
            heading.innerText = `Edit Shipment (${ship.tracking_number})`;
            document.getElementById('shipment-id').value = ship.id;
            document.getElementById('field-tracking_number').value = ship.tracking_number || '';
            document.getElementById('field-carrier').value = ship.carrier || 'FedEx Freight';
            document.getElementById('field-type').value = ship.type || 'Outbound';
            document.getElementById('field-status').value = ship.status || 'Pending';
            document.getElementById('field-account_id').value = ship.account_id || '';
            document.getElementById('field-opportunity_id').value = ship.opportunity_id || '';
            document.getElementById('field-purchase_deal_id').value = ship.purchase_deal_id || '';
            document.getElementById('field-shipping_origin').value = ship.shipping_origin || '';
            document.getElementById('field-destination_address').value = ship.destination_address || '';
            document.getElementById('field-dispatch_date').value = ship.dispatch_date ? ship.dispatch_date.split('T')[0] : '';
            document.getElementById('field-estimated_delivery_date').value = ship.estimated_delivery_date ? ship.estimated_delivery_date.split('T')[0] : '';
            document.getElementById('field-assigned_to').value = ship.assigned_to || '';
            document.getElementById('field-notes').value = ship.notes || '';
        }

        // Render Dynamic Custom Fields
        if (customContainer) {
            let customHtml = '';
            state.customFields.forEach(cf => {
                const perm = getPermission(cf.field_name);
                if (perm === 'Hidden') return;

                const isReadOnly = (perm === 'Read-Only');
                const fieldVal = ship ? (ship[cf.field_name] || (ship.custom_data && ship.custom_data[cf.field_name]) || '') : '';

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

            customContainer.innerHTML = customHtml || '<p class="text-xs text-zinc-400 col-span-2 italic">No custom fields configured for Shipments.</p>';
        }

        modal.classList.remove('hidden');
        if (window.lucide) lucide.createIcons();
    };

    window.closeShipmentModal = function () {
        const modal = document.getElementById('shipment-modal');
        if (modal) modal.classList.add('hidden');
    };

    // Modal Submit Handler
    window.handleShipmentSubmit = async function (e) {
        e.preventDefault();
        const shipId = document.getElementById('shipment-id').value;
        const isEdit = Boolean(shipId);

        const payload = {
            tracking_number: document.getElementById('field-tracking_number').value,
            carrier: document.getElementById('field-carrier').value,
            type: document.getElementById('field-type').value,
            status: document.getElementById('field-status').value,
            account_id: document.getElementById('field-account_id').value,
            opportunity_id: document.getElementById('field-opportunity_id').value || null,
            purchase_deal_id: document.getElementById('field-purchase_deal_id').value || null,
            shipping_origin: document.getElementById('field-shipping_origin').value,
            destination_address: document.getElementById('field-destination_address').value,
            dispatch_date: document.getElementById('field-dispatch_date').value || null,
            estimated_delivery_date: document.getElementById('field-estimated_delivery_date').value || null,
            assigned_to: document.getElementById('field-assigned_to').value || null,
            notes: document.getElementById('field-notes').value,
            custom_data: {},
        };

        // Custom fields
        state.customFields.forEach(cf => {
            const perm = getPermission(cf.field_name);
            if (perm === 'Read-Only' || perm === 'Hidden') return;

            const input = document.querySelector(`[name="${cf.field_name}"]`);
            if (input) {
                payload[cf.field_name] = input.value;
                payload.custom_data[cf.field_name] = input.value;
            }
        });

        const btnSave = document.getElementById('btn-save-shipment');
        if (btnSave) {
            btnSave.disabled = true;
            btnSave.innerText = 'Saving...';
        }

        try {
            const url = isEdit ? `/api/shipping/${shipId}` : '/api/shipping';
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
                showNotification(data.message || 'Shipment manifest saved!');
                window.closeShipmentModal();

                if (isEdit) {
                    const idx = state.shipments.findIndex(s => String(s.id) === String(shipId));
                    if (idx !== -1) state.shipments[idx] = data.shipment;
                } else {
                    state.shipments.unshift(data.shipment);
                }

                updateKpiCards();
                renderCurrentView();
            } else {
                showNotification(data.message || 'Failed to save shipment', 'error');
            }
        } catch (err) {
            console.error('Error saving shipment:', err);
            showNotification('Error saving shipment record', 'error');
        } finally {
            if (btnSave) {
                btnSave.disabled = false;
                btnSave.innerText = 'Save Shipment';
            }
        }
    };

    // Delete Record
    window.deleteShipmentRecord = async function (shipId) {
        const ship = state.shipments.find(s => s.id === shipId);
        if (!ship) return;

        if (!confirm(`Are you sure you want to delete shipment manifest ${ship.tracking_number}?`)) {
            return;
        }

        try {
            const res = await fetch(`/api/shipping/${shipId}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
            });
            const data = await res.json();
            if (data.success) {
                showNotification(data.message || `Shipment ${ship.tracking_number} deleted.`);
                state.shipments = state.shipments.filter(s => s.id !== shipId);
                updateKpiCards();
                renderCurrentView();
            } else {
                showNotification(data.message || 'Failed to delete record', 'error');
            }
        } catch (err) {
            console.error('Error deleting shipment:', err);
            showNotification('Error deleting shipment', 'error');
        }
    };

    // Transfer Owner Modal
    window.openShippingTransferModal = function (shipId) {
        const ship = state.shipments.find(s => s.id === shipId);
        if (!ship) return;

        document.getElementById('transfer-shipment-id').value = ship.id;
        document.getElementById('transfer-shipment-name-label').innerText =
            `Transfer ownership of shipment ${ship.tracking_number} (${ship.carrier}). Current Coordinator: ${ship.owner_name || 'Unassigned'}.`;

        const modal = document.getElementById('shipping-transfer-modal');
        if (modal) modal.classList.remove('hidden');
        if (window.lucide) lucide.createIcons();
    };

    window.closeShippingTransferModal = function () {
        const modal = document.getElementById('shipping-transfer-modal');
        if (modal) modal.classList.add('hidden');
    };

    window.submitShippingTransfer = async function () {
        const shipId = document.getElementById('transfer-shipment-id').value;
        const newOwnerId = document.getElementById('transfer-new-shipping-owner-id').value;

        try {
            const res = await fetch(`/api/shipping/${shipId}/transfer`, {
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
                window.closeShippingTransferModal();
                if (data.shipment) {
                    const idx = state.shipments.findIndex(s => String(s.id) === String(shipId));
                    if (idx !== -1) state.shipments[idx] = data.shipment;
                }
                renderCurrentView();
            } else {
                showNotification(data.message || 'Transfer failed', 'error');
            }
        } catch (err) {
            console.error('Error transferring shipment:', err);
            showNotification('Error transferring shipment', 'error');
        }
    };

    // Visible Fields Config Modal
    window.openShippingConfigModal = function () {
        const modal = document.getElementById('shipping-config-modal');
        const list = document.getElementById('shipping-config-fields-list');
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
                        class="config-ship-field-cb rounded text-indigo-600 focus:ring-indigo-500 cursor-pointer"
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

    window.closeShippingConfigModal = function () {
        const modal = document.getElementById('shipping-config-modal');
        if (modal) modal.classList.add('hidden');
    };

    window.saveShippingVisibleFieldsConfig = function () {
        const checkboxes = document.querySelectorAll('.config-ship-field-cb:checked');
        const selectedKeys = Array.from(checkboxes).map(cb => cb.value);

        if (selectedKeys.length === 0) {
            showNotification('Please select at least one field to display.', 'error');
            return;
        }

        if (state.viewMode === 'table') {
            state.tableVisibleFields = selectedKeys;
        } else {
            state.kanbanVisibleFields = selectedKeys;
        }

        window.closeShippingConfigModal();
        renderCurrentView();
        showNotification('View layout customized successfully!');
    };

    window.resetShippingDefaultVisibleFields = function () {
        state.tableVisibleFields = ['tracking_number', 'carrier', 'type', 'status', 'account_name', 'estimated_delivery_date'];
        state.kanbanVisibleFields = ['carrier', 'type', 'account_name', 'estimated_delivery_date'];
        window.closeShippingConfigModal();
        renderCurrentView();
        showNotification('Reset to default field configuration.');
    };

    // Universal Filter Modal
    window.openShippingFilterModal = function () {
        const modal = document.getElementById('shipping-filter-modal');
        const rulesList = document.getElementById('shipping-filter-rules-list');
        if (!modal || !rulesList) return;

        if (state.activeFilters.length === 0) {
            state.activeFilters = [{
                id: Date.now(),
                field: 'carrier',
                operator: 'contains',
                value: 'FedEx',
            }];
        }

        renderShippingFilterModalRules();
        modal.classList.remove('hidden');
        if (window.lucide) lucide.createIcons();
    };

    window.closeShippingFilterModal = function () {
        const modal = document.getElementById('shipping-filter-modal');
        if (modal) modal.classList.add('hidden');
    };

    function renderShippingFilterModalRules() {
        const container = document.getElementById('shipping-filter-rules-list');
        if (!container) return;

        const visibleDefs = getVisibleFieldDefinitions();

        let html = '';
        state.activeFilters.forEach((rule, idx) => {
            html += `
                <div class="flex items-center gap-2 p-2.5 rounded-lg border border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-800/50">
                    <select
                        onchange="window.updateShippingFilterRule(${rule.id}, 'field', this.value)"
                        class="text-xs px-2 py-1.5 rounded-md border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 flex-1"
                    >
                        ${visibleDefs.map(d => `<option value="${d.name}" ${d.name === rule.field ? 'selected' : ''}>${d.label}</option>`).join('')}
                    </select>

                    <select
                        onchange="window.updateShippingFilterRule(${rule.id}, 'operator', this.value)"
                        class="text-xs px-2 py-1.5 rounded-md border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 w-32"
                    >
                        <option value="equals" ${rule.operator === 'equals' ? 'selected' : ''}>Equals</option>
                        <option value="not_equals" ${rule.operator === 'not_equals' ? 'selected' : ''}>Not Equals</option>
                        <option value="contains" ${rule.operator === 'contains' ? 'selected' : ''}>Contains</option>
                        <option value="is_not_empty" ${rule.operator === 'is_not_empty' ? 'selected' : ''}>Has Value</option>
                    </select>

                    <input
                        type="text"
                        value="${rule.value || ''}"
                        placeholder="Value..."
                        oninput="window.updateShippingFilterRule(${rule.id}, 'value', this.value)"
                        class="text-xs px-2 py-1.5 rounded-md border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 flex-1"
                    >

                    <button
                        type="button"
                        onclick="window.removeShippingFilterRuleRow(${rule.id})"
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

    window.addShippingFilterRuleRow = function () {
        const visibleDefs = getVisibleFieldDefinitions();
        const firstField = visibleDefs.length > 0 ? visibleDefs[0].name : 'carrier';
        state.activeFilters.push({
            id: Date.now(),
            field: firstField,
            operator: 'contains',
            value: '',
        });
        renderShippingFilterModalRules();
    };

    window.updateShippingFilterRule = function (ruleId, key, val) {
        const r = state.activeFilters.find(x => x.id === ruleId);
        if (r) {
            r[key] = val;
        }
    };

    window.removeShippingFilterRuleRow = function (ruleId) {
        state.activeFilters = state.activeFilters.filter(x => x.id !== ruleId);
        renderShippingFilterModalRules();
    };

    window.applyShippingFilterRules = function () {
        window.closeShippingFilterModal();
        updateActiveFilterChips();
        renderCurrentView();
        showNotification(`Applied ${state.activeFilters.length} filter condition(s).`);
    };

    window.clearAllShippingFilters = function () {
        state.activeFilters = [];
        updateActiveFilterChips();
        renderCurrentView();
        window.closeShippingFilterModal();
        showNotification('All filters cleared.');
    };

    function updateActiveFilterChips() {
        const container = document.getElementById('active-filter-chips-container');
        const chipsList = document.getElementById('filter-chips-list');
        const badge = document.getElementById('ship-filter-badge');
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
                    <button type="button" onclick="window.removeSingleShippingFilterChip(${rule.id})" class="text-amber-600 hover:text-amber-900 cursor-pointer">
                        <i data-lucide="x" class="w-3 h-3"></i>
                    </button>
                </span>
            `;
        });

        chipsList.innerHTML = html;
        if (window.lucide) lucide.createIcons();
    }

    window.removeSingleShippingFilterChip = function (ruleId) {
        state.activeFilters = state.activeFilters.filter(x => x.id !== ruleId);
        updateActiveFilterChips();
        renderCurrentView();
    };

    // CSV Export
    window.exportShippingCsv = function () {
        const ships = getFilteredShipments();
        if (ships.length === 0) {
            showNotification('No shipments to export.', 'error');
            return;
        }

        const headers = ['Tracking Code', 'Carrier', 'Direction', 'Status', 'Account', 'Sales Order', 'Purchase PO', 'Origin', 'Destination', 'Est Delivery Date'];
        const rows = [headers.join(',')];

        ships.forEach(s => {
            const row = [
                `"${s.tracking_number || ''}"`,
                `"${(s.carrier || '').replace(/"/g, '""')}"`,
                `"${s.type || ''}"`,
                `"${s.status || ''}"`,
                `"${(s.account_name || '').replace(/"/g, '""')}"`,
                `"${(s.opportunity_name || '').replace(/"/g, '""')}"`,
                `"${s.po_number || ''}"`,
                `"${(s.shipping_origin || '').replace(/"/g, '""')}"`,
                `"${(s.destination_address || '').replace(/"/g, '""')}"`,
                `"${s.estimated_delivery_date || ''}"`,
            ];
            rows.push(row.join(','));
        });

        const blob = new Blob([rows.join('\n')], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `shipping_manifests_${new Date().toISOString().split('T')[0]}.csv`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
        showNotification('Shipping manifests exported to CSV!');
    };

    // Checkbox Toggle
    window.toggleSelectAllShipments = function (checked) {
        document.querySelectorAll('#shipping-table-tbody input[type="checkbox"]').forEach(cb => {
            cb.checked = checked;
        });
    };

    // Main Initializer
    window.initShippingModule = function () {
        const root = document.getElementById('shipping-page-root');
        if (!root) return;

        if (window.__SHIPPING_DATA__) {
            const data = window.__SHIPPING_DATA__;
            state.shipments = data.initialShipments || [];
            state.customFields = data.customFields || [];
            state.fieldPermissions = data.fieldPermissions || {};
            state.accounts = data.accounts || [];
            state.opportunities = data.opportunities || [];
            state.purchaseDeals = data.purchaseDeals || [];
            state.users = data.users || [];
            if (data.stages && data.stages.length > 0) state.stages = data.stages;
            if (data.carriers && data.carriers.length > 0) state.carriers = data.carriers;
        }

        updateKpiCards();
        renderCardsView();
        updateActiveFilterChips();

        if (window.lucide) lucide.createIcons();
    };

    document.addEventListener('DOMContentLoaded', () => {
        window.initShippingModule();
    });

    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        window.initShippingModule();
    }
})();
