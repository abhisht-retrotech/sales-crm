/**
 * DealSync Enterprise CRM — Tasks & Action Queue Engine
 * 1:1 Parity with React Client (TasksView.tsx, CreateTaskModal.tsx, CompactDataTable.tsx)
 * Pure Tailwind CSS styling with Dynamic Custom Fields & Field-Level Security Permissions
 */

(function () {
    'use strict';

    // Module State
    const state = {
        tasks: [],
        counts: {
            all: 0,
            open: 0,
            pending: 0,
            recurring: 0,
            today: 0,
            overdue: 0,
        },
        customFields: [],
        fieldPermissions: {},
        users: [],
        accounts: [],
        leads: [],
        opportunities: [],
        priorities: ['Low', 'Normal', 'High', 'Urgent'],
        statuses: ['Open', 'In Progress', 'Waiting on someone else', 'Deferred', 'Completed'],
        activeFilterTab: 'all', // 'all' | 'open' | 'pending' | 'recurring' | 'today' | 'overdue'
        searchTerm: '',
        viewMode: 'table', // 'table' | 'kanban'
        tableVisibleFields: ['subject', 'priority', 'status', 'due_date', 'assigned_to', 'related_entity_name'],
        kanbanVisibleFields: ['priority', 'due_date', 'assigned_to', 'related_entity_name'],
        activeFilters: [], // array of { id, field, operator, value }
        selectedTask: null,
        editingTask: null,
        transferringTask: null,
        isCreateMode: false,
        isLoading: false,
    };

    const STANDARD_FIELD_DEFINITIONS = [
        { name: 'subject', label: 'Subject', type: 'text', standard: true, required: true },
        { name: 'priority', label: 'Priority', type: 'dropdown', standard: true, required: true, options: ['Low', 'Normal', 'High', 'Urgent'] },
        { name: 'status', label: 'Status', type: 'dropdown', standard: true, required: true, options: ['Open', 'In Progress', 'Waiting on someone else', 'Deferred', 'Completed'] },
        { name: 'due_date', label: 'Due Date', type: 'date', standard: true },
        { name: 'assigned_to', label: 'Assigned To', type: 'lookup', standard: true },
        { name: 'related_entity_name', label: 'Related Record', type: 'lookup', standard: true },
        { name: 'related_entity_type', label: 'Related Object', type: 'dropdown', standard: true, options: ['Account', 'Opportunity', 'Lead', 'Contact'] },
        { name: 'comments', label: 'Comments / Notes', type: 'textarea', standard: true },
        { name: 'reminder_set', label: 'Reminder', type: 'text', standard: true },
        { name: 'is_recurring', label: 'Recurring Series', type: 'boolean', standard: true },
        { name: 'recurrence_pattern', label: 'Recurrence Pattern', type: 'dropdown', standard: true, options: ['Daily', 'Weekly', 'Monthly'] },
        { name: 'created_by_name', label: 'Created By', type: 'text', standard: true, readOnly: true },
        { name: 'created_at', label: 'Created Date', type: 'date', standard: true, readOnly: true },
    ];

    const KANBAN_COLUMNS = [
        { id: 'Open', label: 'Open', color: '#2563eb' },
        { id: 'In Progress', label: 'In Progress', color: '#0284c7' },
        { id: 'Waiting on someone else', label: 'Waiting on someone else', color: '#d97706' },
        { id: 'Deferred', label: 'Deferred', color: '#64748b' },
        { id: 'Completed', label: 'Completed', color: '#059669' },
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
            const savedTable = localStorage.getItem('dealsync_tasks_table_cols');
            if (savedTable) {
                const parsed = JSON.parse(savedTable);
                if (Array.isArray(parsed) && parsed.length > 0) {
                    state.tableVisibleFields = parsed;
                }
            }
            const savedKanban = localStorage.getItem('dealsync_tasks_kanban_cols');
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
            localStorage.setItem('dealsync_tasks_table_cols', JSON.stringify(state.tableVisibleFields));
            localStorage.setItem('dealsync_tasks_kanban_cols', JSON.stringify(state.kanbanVisibleFields));
        } catch (_) {}
    }

    // Filter tasks by active tab, text search, and dynamic filter rules
    function getFilteredTasks() {
        let results = [...state.tasks];
        const today = new Date().toISOString().split('T')[0];

        // 1. Tab Filter
        if (state.activeFilterTab === 'open') {
            results = results.filter(t => t.status !== 'Completed');
        } else if (state.activeFilterTab === 'pending') {
            results = results.filter(t => ['Open', 'In Progress', 'Waiting on someone else', 'Deferred'].includes(t.status));
        } else if (state.activeFilterTab === 'recurring') {
            results = results.filter(t => Boolean(t.is_recurring));
        } else if (state.activeFilterTab === 'today') {
            results = results.filter(t => t.due_date === today);
        } else if (state.activeFilterTab === 'overdue') {
            results = results.filter(t => t.due_date && t.due_date < today && t.status !== 'Completed');
        }

        // 2. Text Search
        if (state.searchTerm.trim()) {
            const q = state.searchTerm.toLowerCase().trim();
            results = results.filter(t => {
                const subj = (t.subject || '').toLowerCase();
                const comm = (t.comments || '').toLowerCase();
                const assign = (t.assigned_to_name || '').toLowerCase();
                const rel = (t.related_entity_name || '').toLowerCase();
                const prio = (t.priority || '').toLowerCase();
                const st = (t.status || '').toLowerCase();
                return subj.includes(q) || comm.includes(q) || assign.includes(q) || rel.includes(q) || prio.includes(q) || st.includes(q);
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

    // Recompute live counts based on active full tasks array
    function recomputeCounts() {
        const today = new Date().toISOString().split('T')[0];
        const allList = state.tasks;

        state.counts = {
            all: allList.length,
            open: allList.filter(t => t.status !== 'Completed').length,
            pending: allList.filter(t => ['Open', 'In Progress', 'Waiting on someone else', 'Deferred'].includes(t.status)).length,
            recurring: allList.filter(t => Boolean(t.is_recurring)).length,
            today: allList.filter(t => t.due_date === today).length,
            overdue: allList.filter(t => t.due_date && t.due_date < today && t.status !== 'Completed').length,
        };

        const totalBadge = document.getElementById('task-total-badge');
        if (totalBadge) {
            totalBadge.innerText = `${state.counts.all} tasks`;
        }

        // Overdue banner
        const overdueBanner = document.getElementById('tasks-overdue-banner');
        const overdueTitle = document.getElementById('overdue-banner-title');
        if (overdueBanner) {
            if (state.counts.overdue > 0) {
                overdueBanner.classList.remove('hidden');
                if (overdueTitle) {
                    overdueTitle.innerText = `${state.counts.overdue} Urgent Action Items Past Deadline`;
                }
            } else {
                overdueBanner.classList.add('hidden');
            }
        }
    }

    // Render Quick Filter Tabs (Pill Buttons matching React TasksView)
    function renderFilterTabs() {
        const container = document.getElementById('tasks-tab-chips-container');
        if (!container) return;

        const tabs = [
            { key: 'all', label: 'All Tasks', count: state.counts.all },
            { key: 'open', label: 'Open', count: state.counts.open },
            { key: 'pending', label: 'Pending', count: state.counts.pending },
            { key: 'recurring', label: 'Recurring', count: state.counts.recurring },
            { key: 'today', label: "Today's", count: state.counts.today },
            { key: 'overdue', label: 'Overdue', count: state.counts.overdue, isAlert: true },
        ];

        let html = `
            <span class="text-xs font-semibold text-zinc-500 shrink-0 mr-1">Queue Filter:</span>
        `;

        tabs.forEach(tab => {
            const isActive = state.activeFilterTab === tab.key;
            const isAlert = tab.isAlert && tab.count > 0;

            let btnClass = 'bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300';
            let badgeClass = 'bg-zinc-200 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-300';

            if (isActive) {
                if (isAlert) {
                    btnClass = 'bg-red-50 text-red-700 border border-red-300 dark:bg-red-950/40 dark:text-red-300 dark:border-red-800 shadow-xs font-bold';
                    badgeClass = 'bg-red-200 text-red-800 dark:bg-red-900/60 dark:text-red-200';
                } else {
                    btnClass = 'bg-indigo-600 text-white shadow-xs font-bold';
                    badgeClass = 'bg-indigo-800 text-indigo-100';
                }
            } else if (isAlert) {
                btnClass = 'bg-red-50/60 text-red-600 hover:bg-red-100 dark:bg-red-950/20 dark:text-red-400 border border-red-200 dark:border-red-900/40';
                badgeClass = 'bg-red-100 text-red-700 dark:bg-red-950/50 dark:text-red-300';
            }

            html += `
                <button
                    type="button"
                    onclick="window.filterTaskTab('${tab.key}')"
                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs transition-all shrink-0 cursor-pointer ${btnClass}"
                >
                    ${isAlert ? '<i data-lucide="alert-triangle" class="w-3.5 h-3.5 text-red-600"></i>' : ''}
                    <span>${tab.label}</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold ${badgeClass}">
                        ${tab.count}
                    </span>
                </button>
            `;
        });

        container.innerHTML = html;
        if (window.lucide) lucide.createIcons();
    }

    // Render Active Filter Chips
    function renderActiveFilterChips() {
        const bar = document.getElementById('tasks-active-filters-bar');
        const badge = document.getElementById('tasks-filter-badge');
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
                    <button type="button" onclick="window.removeTaskFilterRule('${rule.id}')" class="text-blue-500 hover:text-blue-700 cursor-pointer">
                        <i data-lucide="x" class="w-3 h-3"></i>
                    </button>
                </span>
            `;
        });

        html += `
            <button type="button" onclick="window.clearAllTaskFilters()" class="text-xs text-zinc-500 hover:text-red-600 hover:underline cursor-pointer ml-2">
                Clear All
            </button>
        `;

        bar.innerHTML = html;
        bar.classList.remove('hidden');
        if (window.lucide) lucide.createIcons();
    }

    // Priority Badges Helper
    function getPriorityBadge(priority) {
        switch (priority) {
            case 'Urgent':
                return `<span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-red-50 text-red-700 border border-red-200 dark:bg-red-950/40 dark:text-red-300 dark:border-red-800">Urgent</span>`;
            case 'High':
                return `<span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-orange-50 text-orange-700 border border-orange-200 dark:bg-orange-950/40 dark:text-orange-300 dark:border-orange-800">High</span>`;
            case 'Normal':
                return `<span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-zinc-100 text-zinc-700 border border-zinc-200 dark:bg-zinc-800 dark:text-zinc-300">Normal</span>`;
            case 'Low':
                return `<span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-zinc-50 text-zinc-500 border border-zinc-200 dark:bg-zinc-800 dark:text-zinc-400">Low</span>`;
            default:
                return `<span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-zinc-100 text-zinc-700">${priority}</span>`;
        }
    }

    // Status Badges Helper
    function getStatusBadge(status) {
        switch (status) {
            case 'Completed':
                return `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800">● Completed</span>`;
            case 'In Progress':
                return `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-sky-50 text-sky-700 border border-sky-200 dark:bg-sky-950/40 dark:text-sky-300 dark:border-sky-800">● In Progress</span>`;
            case 'Waiting on someone else':
                return `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800">● Waiting</span>`;
            case 'Deferred':
                return `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-zinc-100 text-zinc-700 border border-zinc-200 dark:bg-zinc-800 dark:text-zinc-300">● Deferred</span>`;
            case 'Open':
            default:
                return `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800">● Open</span>`;
        }
    }

    // Render Table View
    function renderTableView() {
        const theadRow = document.getElementById('tasks-table-header-row');
        const tbody = document.getElementById('tasks-table-tbody');
        const emptyState = document.getElementById('tasks-empty-state');
        const summary = document.getElementById('tasks-table-summary');
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
                <input type="checkbox" onchange="window.toggleSelectAllTasks(this.checked)" class="rounded text-indigo-600 focus:ring-indigo-500 cursor-pointer">
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
        const tasks = getFilteredTasks();
        const today = new Date().toISOString().split('T')[0];

        if (summary) {
            summary.innerText = `Showing ${tasks.length} of ${state.tasks.length} tasks (${state.activeFilterTab} view)`;
        }

        if (tasks.length === 0) {
            tbody.innerHTML = '';
            if (emptyState) emptyState.classList.remove('hidden');
            return;
        }

        if (emptyState) emptyState.classList.add('hidden');

        let rowsHtml = '';
        tasks.forEach(task => {
            const isCompleted = task.status === 'Completed';
            const isOverdue = Boolean(task.due_date && task.due_date < today && !isCompleted);

            rowsHtml += `
                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors group ${isCompleted ? 'bg-zinc-50/40 dark:bg-zinc-900/30' : ''}">
                    <td class="py-3 px-4 text-center">
                        <input type="checkbox" data-task-id="${task.id}" class="rounded text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                    </td>
            `;

            activeCols.forEach(colKey => {
                const def = defMap.get(colKey);
                let cellVal = task[colKey];

                if (colKey === 'subject') {
                    rowsHtml += `
                        <td class="py-3 px-4 font-semibold text-zinc-900 dark:text-zinc-100">
                            <div class="flex items-center gap-2.5">
                                <input
                                    type="checkbox"
                                    ${isCompleted ? 'checked' : ''}
                                    onclick="window.quickToggleTaskStatus(${task.id}, event)"
                                    title="${isCompleted ? 'Mark as Open' : 'Mark as Completed'}"
                                    class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 cursor-pointer shrink-0"
                                >
                                <a
                                    href="/tasks/${task.id}"
                                    class="hover:text-indigo-600 no-underline hover:no-underline flex items-center gap-1.5 ${isCompleted ? 'line-through text-zinc-400 dark:text-zinc-500' : ''}"
                                >
                                    <span>${cellVal || 'Untitled Task'}</span>
                                </a>
                                ${Boolean(task.is_recurring) ? `
                                    <span title="Recurring Task Series (${task.recurrence_pattern || 'Weekly'})" class="text-teal-600 dark:text-teal-400 flex items-center shrink-0">
                                        <i data-lucide="repeat" class="w-3 h-3"></i>
                                    </span>
                                ` : ''}
                            </div>
                        </td>
                    `;
                } else if (colKey === 'priority') {
                    rowsHtml += `<td class="py-3 px-4">${getPriorityBadge(cellVal || 'Normal')}</td>`;
                } else if (colKey === 'status') {
                    rowsHtml += `<td class="py-3 px-4">${getStatusBadge(cellVal || 'Open')}</td>`;
                } else if (colKey === 'due_date') {
                    if (cellVal) {
                        rowsHtml += `
                            <td class="py-3 px-4 whitespace-nowrap">
                                <div class="flex items-center gap-1.5 ${isOverdue ? 'text-red-600 dark:text-red-400 font-bold' : 'text-zinc-600 dark:text-zinc-300'}">
                                    <i data-lucide="calendar" class="w-3.5 h-3.5 ${isOverdue ? 'text-red-600' : 'text-zinc-400'}"></i>
                                    <span>${task.due_date_formatted || cellVal}</span>
                                    ${isOverdue ? `
                                        <span class="w-4 h-4 rounded-full bg-red-100 dark:bg-red-950/60 text-red-600 text-[10px] font-extrabold flex items-center justify-center shrink-0" title="Overdue">!</span>
                                    ` : ''}
                                </div>
                            </td>
                        `;
                    } else {
                        rowsHtml += `<td class="py-3 px-4 text-zinc-400 italic">—</td>`;
                    }
                } else if (colKey === 'assigned_to' || colKey === 'assigned_to_name') {
                    const name = task.assigned_to_name || task.assigned_to || 'Unassigned';
                    rowsHtml += `
                        <td class="py-3 px-4 text-zinc-700 dark:text-zinc-300">
                            <div class="flex items-center gap-1.5">
                                <span class="w-5 h-5 rounded-full bg-indigo-100 text-indigo-700 font-bold text-[10px] flex items-center justify-center shrink-0">
                                    ${name.charAt(0).toUpperCase()}
                                </span>
                                <span class="font-medium">${name}</span>
                            </div>
                        </td>
                    `;
                } else if (colKey === 'related_entity_name') {
                    if (cellVal) {
                        let linkUrl = '#';
                        const type = (task.related_entity_type || '').toLowerCase();
                        if (task.related_entity_id) {
                            if (type.includes('account')) linkUrl = `/accounts/${task.related_entity_id}`;
                            else if (type.includes('opp')) linkUrl = `/opportunities/${task.related_entity_id}`;
                            else if (type.includes('lead')) linkUrl = `/leads/${task.related_entity_id}`;
                            else if (type.includes('contact')) linkUrl = `/contacts/${task.related_entity_id}`;
                        }

                        rowsHtml += `
                            <td class="py-3 px-4">
                                <a href="${linkUrl}" class="text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1">
                                    <i data-lucide="folder" class="w-3 h-3 text-zinc-400"></i>
                                    <span class="font-medium">${cellVal}</span>
                                    ${task.related_entity_type ? `<span class="text-[10px] text-zinc-400">(${task.related_entity_type})</span>` : ''}
                                </a>
                            </td>
                        `;
                    } else {
                        rowsHtml += `<td class="py-3 px-4 text-zinc-400 italic">—</td>`;
                    }
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
                            href="/tasks/${task.id}"
                            class="p-1 rounded text-zinc-500 hover:text-indigo-600 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors"
                            title="View Task 360"
                        >
                            <i data-lucide="external-link" class="w-4 h-4"></i>
                        </a>
                        <button
                            type="button"
                            onclick="window.openTaskModal('edit', ${task.id})"
                            class="p-1 rounded text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-100 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
                            title="Edit Task"
                        >
                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                        </button>
                        <button
                            type="button"
                            onclick="window.openTaskTransferModal(${task.id})"
                            class="p-1 rounded text-zinc-500 hover:text-indigo-600 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
                            title="Transfer Assignee"
                        >
                            <i data-lucide="user-check" class="w-4 h-4"></i>
                        </button>
                        <button
                            type="button"
                            onclick="window.deleteTaskRecord(${task.id})"
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
        const board = document.getElementById('tasks-kanban-board');
        if (!board) return;

        const tasks = getFilteredTasks();
        const today = new Date().toISOString().split('T')[0];

        let boardHtml = '';

        KANBAN_COLUMNS.forEach(col => {
            const colTasks = tasks.filter(t => t.status === col.id);

            boardHtml += `
                <div
                    class="kanban-col bg-zinc-50/80 dark:bg-zinc-900/60 rounded-xl border border-zinc-200 dark:border-zinc-800 p-3 space-y-3 min-h-[420px] flex flex-col"
                    data-status="${col.id}"
                    ondragover="window.handleTaskKanbanDragOver(event)"
                    ondrop="window.handleTaskKanbanDrop(event, '${col.id}')"
                >
                    <!-- Column Header -->
                    <div class="flex items-center justify-between pb-2 border-b border-zinc-200 dark:border-zinc-800 shrink-0">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full" style="background-color: ${col.color};"></span>
                            <span class="text-xs font-bold text-zinc-900 dark:text-zinc-100">${col.label}</span>
                        </div>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-zinc-200 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-300">
                            ${colTasks.length}
                        </span>
                    </div>

                    <!-- Cards Container -->
                    <div class="space-y-2.5 flex-1 kanban-cards-wrapper overflow-y-auto max-h-[70vh]">
            `;

            if (colTasks.length === 0) {
                boardHtml += `
                    <div class="py-8 text-center text-zinc-400 text-xs italic border border-dashed border-zinc-200 dark:border-zinc-800 rounded-lg">
                        No tasks in ${col.label}
                    </div>
                `;
            } else {
                colTasks.forEach(task => {
                    const isOverdue = Boolean(task.due_date && task.due_date < today && task.status !== 'Completed');
                    const assigneeName = task.assigned_to_name || task.assigned_to || 'Unassigned';

                    boardHtml += `
                        <div
                            class="bg-white dark:bg-zinc-800 rounded-xl p-3.5 border ${isOverdue ? 'border-red-300 dark:border-red-800' : 'border-zinc-200 dark:border-zinc-700'} shadow-2xs hover:shadow-md transition-all cursor-grab active:cursor-grabbing space-y-2.5"
                            draggable="true"
                            ondragstart="window.handleTaskKanbanDragStart(event, ${task.id})"
                        >
                            <div class="flex items-start justify-between gap-2">
                                ${getPriorityBadge(task.priority || 'Normal')}
                                ${Boolean(task.is_recurring) ? `
                                    <span title="Recurring Task Series" class="text-teal-600 dark:text-teal-400">
                                        <i data-lucide="repeat" class="w-3 h-3"></i>
                                    </span>
                                ` : ''}
                            </div>

                            <a href="/tasks/${task.id}" class="text-xs font-bold text-zinc-950 dark:text-zinc-100 hover:text-indigo-600 no-underline hover:no-underline line-clamp-2 leading-snug">
                                ${task.subject}
                            </a>

                            ${task.related_entity_name ? `
                                <div class="text-[11px] text-indigo-600 dark:text-indigo-400 font-medium flex items-center gap-1">
                                    <i data-lucide="folder" class="w-3 h-3 text-zinc-400"></i>
                                    <span class="truncate">${task.related_entity_name}</span>
                                </div>
                            ` : ''}

                            ${task.comments ? `
                                <p class="text-[11px] text-zinc-500 line-clamp-2">
                                    ${task.comments}
                                </p>
                            ` : ''}

                            <div class="pt-2 border-t border-zinc-100 dark:border-zinc-700/60 flex items-center justify-between text-[11px]">
                                <div class="flex items-center gap-1 ${isOverdue ? 'text-red-600 font-bold' : 'text-zinc-500'}">
                                    <i data-lucide="calendar" class="w-3 h-3"></i>
                                    <span>${task.due_date || 'No date'}</span>
                                    ${isOverdue ? '<span class="text-red-600 font-extrabold">!</span>' : ''}
                                </div>

                                <div class="flex items-center gap-1.5">
                                    <div
                                        class="w-4 h-4 rounded-full bg-indigo-100 text-indigo-700 font-bold text-[9px] flex items-center justify-center shrink-0"
                                        title="${assigneeName}"
                                    >
                                        ${assigneeName.charAt(0).toUpperCase()}
                                    </div>
                                    <button
                                        type="button"
                                        onclick="window.openTaskModal('edit', ${task.id})"
                                        class="p-0.5 rounded text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 cursor-pointer"
                                        title="Edit Task"
                                    >
                                        <i data-lucide="edit-3" class="w-3 h-3"></i>
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

    // Main Render Dispatcher
    function renderMainView() {
        recomputeCounts();
        renderFilterTabs();
        renderActiveFilterChips();

        const tableContainer = document.getElementById('tasks-table-view-container');
        const kanbanContainer = document.getElementById('tasks-kanban-view-container');
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
    window.handleTaskKanbanDragStart = function (e, taskId) {
        e.dataTransfer.setData('text/plain', String(taskId));
        e.dataTransfer.effectAllowed = 'move';
    };

    window.handleTaskKanbanDragOver = function (e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        const col = e.currentTarget;
        col.classList.add('border-indigo-400', 'bg-indigo-50/20');
    };

    window.handleTaskKanbanDrop = function (e, targetStatus) {
        e.preventDefault();
        const col = e.currentTarget;
        col.classList.remove('border-indigo-400', 'bg-indigo-50/20');
        const taskId = e.dataTransfer.getData('text/plain');
        if (taskId) {
            window.updateTaskStatusDirect(Number(taskId), targetStatus);
        }
    };

    // Quick Status Update
    window.updateTaskStatusDirect = async function (id, newStatus) {
        try {
            const res = await fetch(`/api/tasks/${id}/status`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ status: newStatus }),
            });
            const data = await res.json();
            if (data.success && data.task) {
                const idx = state.tasks.findIndex(t => t.id === id);
                if (idx !== -1) {
                    state.tasks[idx] = data.task;
                }
                if (data.counts) state.counts = data.counts;
                showNotification(`Task marked as ${newStatus}`);
                renderMainView();
            } else {
                showNotification(data.message || 'Failed to update task status', 'error');
            }
        } catch (err) {
            console.error('Error updating status:', err);
            showNotification('Error updating task status', 'error');
        }
    };

    // Quick Checkbox Toggle (Open <-> Completed)
    window.quickToggleTaskStatus = async function (id, e) {
        if (e) e.stopPropagation();
        const task = state.tasks.find(t => t.id === id);
        const nextStatus = task && task.status === 'Completed' ? 'Open' : 'Completed';
        await window.updateTaskStatusDirect(id, nextStatus);
    };

    // Filter Tab Click
    window.filterTaskTab = function (tabKey) {
        state.activeFilterTab = tabKey;
        renderMainView();
    };

    // View Switcher: Table vs Kanban
    window.switchTaskView = function (mode) {
        state.viewMode = mode;
        renderMainView();
    };

    // Related Entity Type Change in Modal Form
    window.handleRelatedTypeChange = function (type) {
        const nameInput = document.getElementById('form-task-rel-name');
        if (!nameInput) return;
        nameInput.placeholder = type ? `Lookup ${type} name...` : 'Related Record Name';
    };

    // Render Dynamic Custom Fields in Modal (Enforcing Permissions)
    function renderCustomFieldsInModal(existingRecord = null) {
        const container = document.getElementById('task-dynamic-fields-container');
        const section = document.getElementById('task-dynamic-custom-fields-section');
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
                } else if (existingRecord.custom_fields && existingRecord.custom_fields[fName] !== undefined) {
                    val = existingRecord.custom_fields[fName];
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
    window.openTaskModal = function (mode = 'create', id = null) {
        const modal = document.getElementById('task-form-modal');
        const form = document.getElementById('task-record-form');
        const title = document.getElementById('task-form-modal-title');
        const idInput = document.getElementById('form-task-id');
        if (!modal || !form) return;

        form.reset();

        // Apply field-level permissions on standard inputs
        document.querySelectorAll('#task-record-form .task-field-group').forEach(group => {
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

        const recWrap = document.getElementById('form-task-recurrence-wrap');

        if (mode === 'edit' && id) {
            const task = state.tasks.find(t => t.id === id);
            if (!task) return;

            state.editingTask = task;
            state.isCreateMode = false;
            title.innerText = `Edit Task — ${task.subject}`;
            idInput.value = task.id;

            document.getElementById('form-task-subject').value = task.subject || '';
            document.getElementById('form-task-priority').value = task.priority || 'Normal';
            document.getElementById('form-task-status').value = task.status || 'Open';
            document.getElementById('form-task-assigned-to').value = task.assigned_to || '';
            document.getElementById('form-task-due-date').value = task.due_date || '';
            document.getElementById('form-task-rel-type').value = task.related_entity_type || '';
            document.getElementById('form-task-rel-name').value = task.related_entity_name || '';
            document.getElementById('form-task-rel-id').value = task.related_entity_id || '';
            document.getElementById('form-task-comments').value = task.comments || '';

            const isRec = Boolean(task.is_recurring);
            document.getElementById('form-task-is-recurring').checked = isRec;
            if (recWrap) recWrap.style.display = isRec ? 'block' : 'none';
            if (task.recurrence_pattern) {
                document.getElementById('form-task-recurrence-pattern').value = task.recurrence_pattern;
            }

            renderCustomFieldsInModal(task);
        } else {
            state.editingTask = null;
            state.isCreateMode = true;
            title.innerText = 'New Task';
            idInput.value = '';
            document.getElementById('form-task-priority').value = 'Normal';
            document.getElementById('form-task-status').value = 'Open';
            document.getElementById('form-task-is-recurring').checked = false;
            if (recWrap) recWrap.style.display = 'none';

            renderCustomFieldsInModal(null);
        }

        modal.classList.remove('hidden');
    };

    window.closeTaskModal = function () {
        const modal = document.getElementById('task-form-modal');
        if (modal) modal.classList.add('hidden');
    };

    // Save Task Form (Create or Update)
    window.saveTaskForm = async function (e) {
        e.preventDefault();

        const id = document.getElementById('form-task-id').value;
        const isEdit = Boolean(id);

        const isRecurring = document.getElementById('form-task-is-recurring').checked;
        const payload = {
            subject: document.getElementById('form-task-subject').value,
            priority: document.getElementById('form-task-priority').value,
            status: document.getElementById('form-task-status').value,
            assigned_to: document.getElementById('form-task-assigned-to').value || null,
            due_date: document.getElementById('form-task-due-date').value || null,
            related_entity_type: document.getElementById('form-task-rel-type').value || null,
            related_entity_name: document.getElementById('form-task-rel-name').value || null,
            related_entity_id: document.getElementById('form-task-rel-id').value || null,
            is_recurring: isRecurring,
            recurrence_pattern: isRecurring ? document.getElementById('form-task-recurrence-pattern').value : null,
            comments: document.getElementById('form-task-comments').value || null,
            custom_fields: {},
        };

        // Gather dynamic custom fields
        document.querySelectorAll('#task-dynamic-fields-container [data-cf-name]').forEach(el => {
            const cfName = el.getAttribute('data-cf-name');
            payload.custom_fields[cfName] = el.value;
        });

        const url = isEdit ? `/api/tasks/${id}` : '/api/tasks';
        const method = isEdit ? 'PUT' : 'POST';

        const btn = document.getElementById('btn-save-task-form');
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

            if (data.success && data.task) {
                if (isEdit) {
                    const idx = state.tasks.findIndex(t => t.id === Number(id));
                    if (idx !== -1) {
                        state.tasks[idx] = data.task;
                    }
                } else {
                    state.tasks.unshift(data.task);
                }

                if (data.counts) state.counts = data.counts;
                showNotification(data.message || 'Task saved successfully!');
                window.closeTaskModal();
                renderMainView();
            } else {
                showNotification(data.message || 'Failed to save task', 'error');
            }
        } catch (err) {
            console.error('Error saving task:', err);
            showNotification('An unexpected error occurred while saving task', 'error');
        } finally {
            if (btn) btn.disabled = false;
        }
    };

    // Delete Task
    window.deleteTaskRecord = async function (id) {
        const task = state.tasks.find(t => t.id === id);
        const name = task ? task.subject : 'this task';
        if (!confirm(`Are you sure you want to delete task "${name}"?`)) {
            return;
        }

        try {
            const res = await fetch(`/api/tasks/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json',
                },
            });
            const data = await res.json();
            if (data.success) {
                state.tasks = state.tasks.filter(t => t.id !== id);
                if (data.counts) state.counts = data.counts;
                showNotification(data.message || 'Task deleted successfully');
                renderMainView();
            } else {
                showNotification(data.message || 'Failed to delete task', 'error');
            }
        } catch (err) {
            console.error('Error deleting task:', err);
            showNotification('Error deleting task', 'error');
        }
    };

    // Transfer Assignee Modal Handlers
    window.openTaskTransferModal = function (id) {
        const task = state.tasks.find(t => t.id === id);
        if (!task) return;

        state.transferringTask = task;
        document.getElementById('transfer-task-id').value = task.id;
        document.getElementById('transfer-task-subject-label').innerText = `Reassign task "${task.subject}".`;
        if (task.assigned_to) {
            document.getElementById('transfer-new-assignee-id').value = task.assigned_to;
        }

        document.getElementById('task-transfer-modal').classList.remove('hidden');
    };

    window.closeTaskTransferModal = function () {
        document.getElementById('task-transfer-modal').classList.add('hidden');
    };

    window.submitTaskTransfer = async function () {
        const id = document.getElementById('transfer-task-id').value;
        const newAssigneeId = document.getElementById('transfer-new-assignee-id').value;
        if (!newAssigneeId) {
            showNotification('Please select a team member to reassign to', 'error');
            return;
        }

        try {
            const res = await fetch(`/api/tasks/${id}/transfer`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ assigned_to: Number(newAssigneeId) }),
            });
            const data = await res.json();
            if (data.success && data.task) {
                const idx = state.tasks.findIndex(t => t.id === Number(id));
                if (idx !== -1) {
                    state.tasks[idx] = data.task;
                }
                showNotification(data.message || 'Task reassigned successfully');
                window.closeTaskTransferModal();
                renderMainView();
            } else {
                showNotification(data.message || 'Failed to reassign task', 'error');
            }
        } catch (err) {
            console.error('Error transferring task:', err);
            showNotification('Error reassigning task', 'error');
        }
    };

    // Customize Visible Fields Modal
    window.openTaskConfigModal = function () {
        const modal = document.getElementById('task-config-modal');
        const list = document.getElementById('task-config-fields-list');
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
                            class="task-col-checkbox rounded text-indigo-600 focus:ring-indigo-500 cursor-pointer"
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

    window.closeTaskConfigModal = function () {
        document.getElementById('task-config-modal').classList.add('hidden');
    };

    window.saveTaskFieldConfigs = function () {
        const checked = Array.from(document.querySelectorAll('.task-col-checkbox:checked')).map(cb => cb.value);
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
        window.closeTaskConfigModal();
        renderMainView();
        showNotification('Visible fields configuration saved');
    };

    window.resetTaskDefaultFields = function () {
        state.tableVisibleFields = ['subject', 'priority', 'status', 'due_date', 'assigned_to', 'related_entity_name'];
        state.kanbanVisibleFields = ['priority', 'due_date', 'assigned_to', 'related_entity_name'];
        saveViewConfigsToStorage();
        window.closeTaskConfigModal();
        renderMainView();
        showNotification('Default columns restored');
    };

    // Universal Filter Modal Handlers
    window.openTaskFilterModal = function () {
        const modal = document.getElementById('task-filter-modal');
        if (!modal) return;
        const container = document.getElementById('task-filter-rules-container');
        container.innerHTML = '';

        if (state.activeFilters.length === 0) {
            window.addTaskFilterRule();
        } else {
            state.activeFilters.forEach(rule => window.addTaskFilterRule(rule));
        }

        modal.classList.remove('hidden');
    };

    window.closeTaskFilterModal = function () {
        document.getElementById('task-filter-modal').classList.add('hidden');
    };

    window.addTaskFilterRule = function (existingRule = null) {
        const container = document.getElementById('task-filter-rules-container');
        if (!container) return;

        const id = existingRule ? existingRule.id : 'rule_' + Date.now() + '_' + Math.random().toString(36).substr(2, 4);
        const visibleDefs = getVisibleFieldDefinitions();

        const ruleDiv = document.createElement('div');
        ruleDiv.className = 'task-filter-rule-row flex items-center gap-2 p-2 rounded-lg bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200 dark:border-zinc-700 text-xs';
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

    window.applyTaskFilters = function () {
        const rows = document.querySelectorAll('.task-filter-rule-row');
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
        window.closeTaskFilterModal();
        renderMainView();
    };

    window.clearAllTaskFilters = function () {
        state.activeFilters = [];
        window.closeTaskFilterModal();
        renderMainView();
    };

    window.removeTaskFilterRule = function (id) {
        state.activeFilters = state.activeFilters.filter(r => r.id !== id);
        renderMainView();
    };

    // Export CSV
    window.exportTasksCsv = function () {
        const tasks = getFilteredTasks();
        if (tasks.length === 0) {
            showNotification('No tasks to export', 'error');
            return;
        }

        const visibleDefs = getVisibleFieldDefinitions();
        const headers = visibleDefs.map(d => `"${d.label.replace(/"/g, '""')}"`);

        const rows = tasks.map(task => {
            return visibleDefs.map(d => {
                const val = task[d.name] !== undefined && task[d.name] !== null ? String(task[d.name]) : '';
                return `"${val.replace(/"/g, '""')}"`;
            }).join(',');
        });

        const csvContent = 'data:text/csv;charset=utf-8,' + [headers.join(','), ...rows].join('\n');
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement('a');
        link.setAttribute('href', encodedUri);
        link.setAttribute('download', `tasks_export_${new Date().toISOString().slice(0, 10)}.csv`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        showNotification(`Exported ${tasks.length} tasks to CSV`);
    };

    // Refresh Data from API
    window.refreshTasksData = async function () {
        try {
            const res = await fetch('/api/tasks?filter=' + encodeURIComponent(state.activeFilterTab), {
                headers: { 'Accept': 'application/json' },
            });
            const data = await res.json();
            if (data && data.tasks) {
                state.tasks = data.tasks;
                if (data.counts) state.counts = data.counts;
                renderMainView();
                showNotification('Tasks refreshed from database');
            }
        } catch (err) {
            console.error('Error refreshing tasks:', err);
        }
    };

    // Search Box Handlers
    window.clearTaskSearch = function () {
        state.searchTerm = '';
        const searchInput = document.getElementById('tasks-search-input');
        const clearBtn = document.getElementById('tasks-search-clear-btn');
        if (searchInput) searchInput.value = '';
        if (clearBtn) clearBtn.classList.add('hidden');
        renderMainView();
    };

    function setupSearchListener() {
        const searchInput = document.getElementById('tasks-search-input');
        const clearBtn = document.getElementById('tasks-search-clear-btn');
        if (searchInput) {
            searchInput.value = state.searchTerm;
            searchInput.addEventListener('input', (e) => {
                state.searchTerm = e.target.value;
                if (clearBtn) {
                    if (state.searchTerm) clearBtn.classList.remove('hidden');
                    else clearBtn.classList.add('hidden');
                }
                renderMainView();
            });
        }
    }

    // Toggle Select All Checkbox
    window.toggleSelectAllTasks = function (checked) {
        document.querySelectorAll('#tasks-table-tbody input[type="checkbox"]').forEach(cb => {
            cb.checked = checked;
        });
    };

    // Main Module Initializer (Called on page load and SPA route transition)
    window.initTasksModule = function () {
        const root = document.getElementById('tasks-page-root');
        if (!root) return;

        // Hydrate from window.__TASKS_DATA__ if available
        if (window.__TASKS_DATA__) {
            const h = window.__TASKS_DATA__;
            state.tasks = Array.isArray(h.initialTasks) ? h.initialTasks : [];
            state.counts = h.counts || state.counts;
            state.customFields = Array.isArray(h.customFields) ? h.customFields : [];
            state.fieldPermissions = h.fieldPermissions || {};
            state.users = Array.isArray(h.users) ? h.users : [];
            state.accounts = Array.isArray(h.accounts) ? h.accounts : [];
            state.leads = Array.isArray(h.leads) ? h.leads : [];
            state.opportunities = Array.isArray(h.opportunities) ? h.opportunities : [];
            if (Array.isArray(h.priorities)) state.priorities = h.priorities;
            if (Array.isArray(h.statuses)) state.statuses = h.statuses;
        }

        loadSavedViewConfigs();
        setupSearchListener();
        renderMainView();
    };

    // Auto-init on script evaluation
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', window.initTasksModule);
    } else {
        window.initTasksModule();
    }
})();
