/**
 * DealSync Enterprise CRM — Leads & Intake Module
 * 1:1 Parity with React Client (LeadsView.tsx, CompactDataTable.tsx, UniversalKanban.tsx)
 * Pure Tailwind CSS styling with Dynamic Custom Fields & Field-Level Security Permissions
 */

(function () {
    'use strict';

    // Module State
    const state = {
        leads: [],
        customFields: [],
        fieldPermissions: {},
        users: [],
        stages: ['New', 'Contacted', 'Qualified', 'Lost'],
        sources: [
            'Direct Inbound',
            'Website',
            'Referral',
            'Cold Outreach',
            'Partner',
            'Trade Show / Event',
            'LinkedIn',
            'Google Ads',
            'Other'
        ],
        activeStage: 'all',
        searchTerm: '',
        viewMode: 'table', // 'table' | 'kanban'
        selectedLeadIds: new Set(),
        tableVisibleFields: ['name', 'company', 'score', 'status', 'email', 'phone', 'assigned_to'],
        kanbanVisibleFields: ['company', 'score', 'email', 'assigned_to'],
        activeFilters: [], // array of { field, operator, value }
        convertingLead: null,
        transferringLead: null,
        editingLead: null,
        isCreateMode: false,
        isLoading: false,
    };

    const STANDARD_FIELD_DEFINITIONS = [
        { name: 'name', label: 'Full Name', type: 'text', standard: true },
        { name: 'first_name', label: 'First Name', type: 'text', standard: true, required: true },
        { name: 'last_name', label: 'Last Name', type: 'text', standard: true, required: true },
        { name: 'company', label: 'Company Name', type: 'text', standard: true, required: true },
        { name: 'title', label: 'Job Title', type: 'text', standard: true },
        { name: 'score', label: 'Lead Score', type: 'number', standard: true },
        { name: 'status', label: 'Status', type: 'dropdown', standard: true, options: ['New', 'Contacted', 'Qualified', 'Lost'] },
        { name: 'email', label: 'Email Address', type: 'email', standard: true },
        { name: 'phone', label: 'Phone Number', type: 'phone', standard: true },
        {
            name: 'source',
            label: 'Lead Source',
            type: 'dropdown',
            standard: true,
            options: ['Direct Inbound', 'Website', 'Referral', 'Cold Outreach', 'Partner', 'Trade Show / Event', 'LinkedIn', 'Google Ads', 'Other']
        },
        { name: 'rating', label: 'Rating', type: 'dropdown', standard: true, options: ['Hot', 'Warm', 'Cold'] },
        { name: 'lead_value', label: 'Estimated Value', type: 'text', standard: true },
        { name: 'assigned_to', label: 'Owner / Assigned To', type: 'lookup', standard: true },
        { name: 'notes', label: 'Qualification Notes', type: 'textarea', standard: true },
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

    // Load Saved View Config from LocalStorage
    function loadSavedViewConfig() {
        try {
            const saved = localStorage.getItem('dealsync_leads_view_config');
            if (saved) {
                const parsed = JSON.parse(saved);
                if (Array.isArray(parsed.tableVisibleFields) && parsed.tableVisibleFields.length > 0) {
                    state.tableVisibleFields = parsed.tableVisibleFields;
                }
                if (Array.isArray(parsed.kanbanVisibleFields) && parsed.kanbanVisibleFields.length > 0) {
                    state.kanbanVisibleFields = parsed.kanbanVisibleFields;
                }
            }
        } catch (_) {}
    }

    // Save View Config to LocalStorage
    function saveViewConfig() {
        try {
            localStorage.setItem('dealsync_leads_view_config', JSON.stringify({
                tableVisibleFields: state.tableVisibleFields,
                kanbanVisibleFields: state.kanbanVisibleFields,
            }));
        } catch (_) {}
    }

    // Filter Leads based on active stage, search term, and filter rules
    function getFilteredLeads() {
        let results = [...state.leads];

        // 1. Stage Filter Chip
        if (state.activeStage && state.activeStage !== 'all') {
            results = results.filter(l => (l.status || '').toLowerCase() === state.activeStage.toLowerCase());
        }

        // 2. Search Term
        if (state.searchTerm) {
            const term = state.searchTerm.toLowerCase();
            results = results.filter(l => {
                const name = (l.name || `${l.first_name || ''} ${l.last_name || ''}`).toLowerCase();
                const company = (l.company || '').toLowerCase();
                const email = (l.email || '').toLowerCase();
                const phone = (l.phone || '').toLowerCase();
                const title = (l.title || '').toLowerCase();
                const source = (l.source || '').toLowerCase();
                return name.includes(term) ||
                    company.includes(term) ||
                    email.includes(term) ||
                    phone.includes(term) ||
                    title.includes(term) ||
                    source.includes(term);
            });
        }

        // 3. Active Custom Filters
        if (state.activeFilters && state.activeFilters.length > 0) {
            results = results.filter(lead => {
                return state.activeFilters.every(rule => {
                    const val = lead[rule.field] !== undefined ? lead[rule.field] : (lead.custom_data ? lead.custom_data[rule.field] : undefined);
                    const target = rule.value;
                    if (val === undefined || val === null) return false;

                    switch (rule.operator) {
                        case 'equals':
                            return String(val).toLowerCase() === String(target).toLowerCase();
                        case 'not_equals':
                            return String(val).toLowerCase() !== String(target).toLowerCase();
                        case 'contains':
                            return String(val).toLowerCase().includes(String(target).toLowerCase());
                        case 'greater_than':
                            return Number(val) > Number(target);
                        case 'less_than':
                            return Number(val) < Number(target);
                        default:
                            return true;
                    }
                });
            });
        }

        return results;
    }

    // Refresh Leads Data via AJAX API
    async function fetchLeads(showLoader = true) {
        if (showLoader) {
            state.isLoading = true;
            renderLoadingState(true);
        }

        try {
            const params = new URLSearchParams();
            if (state.searchTerm) params.append('search', state.searchTerm);
            if (state.activeStage && state.activeStage !== 'all') params.append('status', state.activeStage);

            const res = await fetch(`/api/leads?${params.toString()}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            if (res.ok) {
                const data = await res.json();
                state.leads = Array.isArray(data) ? data : (data.leads || []);
                renderAll();
            }
        } catch (err) {
            console.error('[Leads Module] Error fetching leads:', err);
        } finally {
            state.isLoading = false;
            renderLoadingState(false);
        }
    }

    // Render Stage Filter Chips with Live Counts
    function renderStageChips() {
        const container = document.getElementById('leads-stage-chips-container');
        if (!container) return;

        // Calculate count for each stage
        const totalCount = state.leads.length;
        const counts = {
            New: state.leads.filter(l => l.status === 'New').length,
            Contacted: state.leads.filter(l => l.status === 'Contacted').length,
            Qualified: state.leads.filter(l => l.status === 'Qualified').length,
            Lost: state.leads.filter(l => l.status === 'Lost').length,
        };

        const stagesList = [
            { key: 'all', label: 'All Leads', count: totalCount },
            { key: 'New', label: 'New', count: counts.New },
            { key: 'Contacted', label: 'Contacted', count: counts.Contacted },
            { key: 'Qualified', label: 'Qualified', count: counts.Qualified },
            { key: 'Lost', label: 'Lost', count: counts.Lost },
        ];

        container.innerHTML = stagesList.map(st => {
            const isActive = state.activeStage.toLowerCase() === st.key.toLowerCase();
            const activeClasses = isActive
                ? 'bg-zinc-900 text-white font-semibold dark:bg-zinc-100 dark:text-zinc-950 shadow-xs'
                : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700 font-medium';

            return `
                <button
                    type="button"
                    data-stage="${st.key}"
                    class="leads-stage-chip-btn inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs transition-colors cursor-pointer ${activeClasses}"
                >
                    <span>${st.label}</span>
                    <span class="inline-flex items-center justify-center px-1.5 py-0.2 rounded-full text-[10px] font-bold ${isActive ? 'bg-white/20 text-white dark:bg-zinc-950/20 dark:text-zinc-950' : 'bg-zinc-200 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300'}">
                        ${st.count}
                    </span>
                </button>
            `;
        }).join('');

        // Attach Click Listeners
        container.querySelectorAll('.leads-stage-chip-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const stage = btn.getAttribute('data-stage');
                state.activeStage = stage;
                renderStageChips();
                renderViews();
            });
        });
    }

    // Render Filter Chips Bar
    function renderActiveFilterChips() {
        const bar = document.getElementById('leads-active-filters-bar');
        if (!bar) return;

        if (state.activeFilters.length === 0) {
            bar.classList.add('hidden');
            bar.innerHTML = '';
            return;
        }

        bar.classList.remove('hidden');
        const fields = getVisibleFieldDefinitions();

        const chipsHtml = state.activeFilters.map((f, idx) => {
            const fDef = fields.find(fd => fd.name === f.field);
            const label = fDef ? fDef.label : f.field;
            return `
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800">
                    <span class="text-blue-500 font-semibold">${label}</span>
                    <span class="text-blue-400">${f.operator.replace('_', ' ')}</span>
                    <span class="font-bold text-blue-900 dark:text-blue-100">"${f.value}"</span>
                    <button type="button" data-filter-idx="${idx}" class="remove-filter-btn text-blue-400 hover:text-blue-700 ml-1">✕</button>
                </span>
            `;
        }).join('');

        bar.innerHTML = `
            <div class="flex items-center gap-2 flex-wrap text-xs">
                <span class="text-zinc-500 dark:text-zinc-400 font-semibold">Active Filters:</span>
                ${chipsHtml}
                <button type="button" id="clear-all-filters-btn" class="text-xs text-rose-600 hover:text-rose-700 font-semibold underline ml-1 cursor-pointer">
                    Clear All
                </button>
            </div>
        `;

        bar.querySelectorAll('.remove-filter-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const idx = parseInt(btn.getAttribute('data-filter-idx'), 10);
                state.activeFilters.splice(idx, 1);
                renderActiveFilterChips();
                renderViews();
                updateFilterBtnBadge();
            });
        });

        const clearBtn = document.getElementById('clear-all-filters-btn');
        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                state.activeFilters = [];
                renderActiveFilterChips();
                renderViews();
                updateFilterBtnBadge();
            });
        }
    }

    function updateFilterBtnBadge() {
        const badge = document.getElementById('leads-filter-badge');
        if (!badge) return;
        if (state.activeFilters.length > 0) {
            badge.textContent = state.activeFilters.length;
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }
    }

    // Render Bulk Action Bar
    function renderBulkActionBar() {
        const bar = document.getElementById('leads-bulk-action-bar');
        if (!bar) return;

        const count = state.selectedLeadIds.size;
        if (count > 0 && state.viewMode === 'table') {
            bar.classList.remove('hidden');
            const countLabel = document.getElementById('leads-selected-count-label');
            if (countLabel) countLabel.textContent = `${count} lead${count > 1 ? 's' : ''} selected`;
        } else {
            bar.classList.add('hidden');
        }
    }

    // Helper: Format Cell Value
    function formatCellValue(lead, fieldDef) {
        const fName = fieldDef.name;
        const perm = fieldDef.permission || getPermission(fName);

        if (perm === 'Hidden') {
            return `<span class="text-zinc-400 italic text-[11px]">[Hidden]</span>`;
        }

        let val = lead[fName];
        if (val === undefined && lead.custom_data) {
            val = lead.custom_data[fName];
        }

        if (val === null || val === undefined || val === '') {
            return `<span class="text-zinc-400 dark:text-zinc-500">—</span>`;
        }

        // Special renderers by field name or type
        if (fName === 'name') {
            const fullName = lead.name || `${lead.first_name || ''} ${lead.last_name || ''}`.trim() || 'Unnamed Lead';
            const initial = fullName.charAt(0).toUpperCase() || 'L';
            return `
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 font-bold text-xs flex items-center justify-center shrink-0">
                        ${initial}
                    </div>
                    <div>
                        <div class="font-bold text-zinc-900 dark:text-zinc-100 hover:text-indigo-600 transition-colors">${fullName}</div>
                        ${lead.title ? `<div class="text-[11px] text-zinc-500 dark:text-zinc-400">${lead.title}</div>` : ''}
                    </div>
                </div>
            `;
        }

        if (fName === 'status') {
            const statusColors = {
                New: 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800',
                Contacted: 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800',
                Qualified: 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800',
                Lost: 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800',
            };
            const col = statusColors[val] || 'bg-zinc-100 text-zinc-700 border-zinc-200 dark:bg-zinc-800 dark:text-zinc-300';
            return `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold border ${col}">${val}</span>`;
        }

        if (fName === 'score') {
            const num = parseInt(val, 10) || 0;
            let scoreClass = 'text-emerald-600 bg-emerald-50 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800';
            if (num < 40) {
                scoreClass = 'text-rose-600 bg-rose-50 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800';
            } else if (num < 70) {
                scoreClass = 'text-amber-600 bg-amber-50 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800';
            }
            return `
                <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md font-bold text-xs border ${scoreClass}">
                    <span>${num}</span>
                    <span class="text-[10px] opacity-75">/ 100</span>
                </div>
            `;
        }

        if (fName === 'email') {
            return `
                <a href="mailto:${val}" class="text-indigo-600 dark:text-indigo-400 hover:underline inline-flex items-center gap-1">
                    <i data-lucide="mail" class="w-3 h-3"></i>
                    <span>${val}</span>
                </a>
            `;
        }

        if (fName === 'phone') {
            return `
                <a href="tel:${val}" class="text-zinc-700 dark:text-zinc-300 hover:text-indigo-600 inline-flex items-center gap-1">
                    <i data-lucide="phone" class="w-3 h-3"></i>
                    <span>${val}</span>
                </a>
            `;
        }

        if (fName === 'assigned_to') {
            const ownerName = lead.assigned_to_name || lead.assigned_user?.name || (val ? `User #${val}` : 'Unassigned');
            return `
                <span class="inline-flex items-center gap-1.5 text-xs text-zinc-700 dark:text-zinc-300 font-medium">
                    <i data-lucide="user" class="w-3 h-3 text-zinc-400"></i>
                    <span>${ownerName}</span>
                </span>
            `;
        }

        if (fName === 'source') {
            return `<span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">${val}</span>`;
        }

        // Custom field data types
        const type = fieldDef.type || '';
        if (type === 'rating') {
            const stars = parseInt(val, 10) || 0;
            return `
                <div class="flex items-center text-amber-400 text-xs gap-0.5" title="${stars} / 5">
                    ${'★'.repeat(Math.min(5, stars))}${'☆'.repeat(Math.max(0, 5 - stars))}
                </div>
            `;
        }

        if (type === 'dropdown') {
            return `<span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-100 dark:bg-indigo-950/40 dark:text-indigo-300 dark:border-indigo-900">${val}</span>`;
        }

        return `<span class="text-zinc-800 dark:text-zinc-200 text-xs">${val}</span>`;
    }

    // Render Compact Table View
    function renderTableView(filteredLeads) {
        const tableContainer = document.getElementById('leads-table-view');
        if (!tableContainer) return;

        const allDefs = getVisibleFieldDefinitions();
        // Visible columns selected by user
        const activeDefs = allDefs.filter(d => state.tableVisibleFields.includes(d.name));

        if (filteredLeads.length === 0) {
            tableContainer.innerHTML = `
                <div class="p-12 text-center bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800">
                    <div class="w-12 h-12 mx-auto rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-400 mb-3">
                        <i data-lucide="search-x" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">No leads found</h3>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 max-w-sm mx-auto">
                        No leads match your active search or filter criteria. Try adjusting your query or add a new lead.
                    </p>
                    <button type="button" onclick="window.openLeadModal('create')" class="mt-4 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold inline-flex items-center gap-1.5 cursor-pointer">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                        <span>Add Lead</span>
                    </button>
                </div>
            `;
            if (window.lucide) lucide.createIcons();
            return;
        }

        const isAllSelected = filteredLeads.length > 0 && filteredLeads.every(l => state.selectedLeadIds.has(String(l.id)));

        let html = `
            <div class="overflow-x-auto bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-xs">
                <table class="w-full text-left border-collapse" id="leads-main-table">
                    <thead>
                        <tr class="border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/80 dark:bg-zinc-800/40 text-[11px] font-bold text-zinc-600 dark:text-zinc-400 uppercase tracking-wider">
                            <th class="w-10 px-3 py-3 text-center">
                                <input type="checkbox" id="leads-select-all-chk" class="rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer" ${isAllSelected ? 'checked' : ''}>
                            </th>
        `;

        activeDefs.forEach(def => {
            html += `<th class="px-3.5 py-3 font-bold whitespace-nowrap">${def.label}</th>`;
        });

        html += `
                            <th class="px-3 py-3 text-right whitespace-nowrap font-bold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800 text-xs">
        `;

        filteredLeads.forEach(lead => {
            const isSelected = state.selectedLeadIds.has(String(lead.id));
            const isConverted = Boolean(lead.is_converted);

            html += `
                <tr class="hover:bg-zinc-50/80 dark:hover:bg-zinc-800/50 transition-colors group cursor-pointer ${isSelected ? 'bg-indigo-50/40 dark:bg-indigo-950/20' : ''}" data-lead-id="${lead.id}">
                    <td class="px-3 py-2.5 text-center" onclick="event.stopPropagation()">
                        <input type="checkbox" data-lead-id="${lead.id}" class="lead-row-chk rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer" ${isSelected ? 'checked' : ''}>
                    </td>
            `;

            activeDefs.forEach(def => {
                html += `
                    <td class="px-3.5 py-2.5" onclick="window.navigateToLeadDetail('${lead.id}')">
                        ${formatCellValue(lead, def)}
                    </td>
                `;
            });

            // Action Buttons
            html += `
                    <td class="px-3 py-2.5 text-right whitespace-nowrap" onclick="event.stopPropagation()">
                        <div class="inline-flex items-center gap-1 opacity-90 group-hover:opacity-100">
            `;

            if (!isConverted) {
                html += `
                    <button
                        type="button"
                        onclick="window.openConvertModal('${lead.id}')"
                        class="p-1.5 rounded-md hover:bg-emerald-50 text-emerald-600 dark:hover:bg-emerald-950/50 dark:text-emerald-400 transition-colors"
                        title="Convert Lead to Account & Contact"
                    >
                        <i data-lucide="arrow-right-left" class="w-3.5 h-3.5"></i>
                    </button>
                `;
            } else {
                html += `
                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300">
                        <i data-lucide="check-check" class="w-3 h-3"></i> Converted
                    </span>
                `;
            }

            html += `
                            <button
                                type="button"
                                onclick="window.openTransferModal('${lead.id}')"
                                class="p-1.5 rounded-md hover:bg-zinc-100 text-zinc-500 hover:text-zinc-800 dark:hover:bg-zinc-800 dark:hover:text-zinc-200 transition-colors"
                                title="Transfer Ownership"
                            >
                                <i data-lucide="user-check" class="w-3.5 h-3.5"></i>
                            </button>
                            <button
                                type="button"
                                onclick="window.openLeadModal('edit', '${lead.id}')"
                                class="p-1.5 rounded-md hover:bg-zinc-100 text-zinc-500 hover:text-indigo-600 dark:hover:bg-zinc-800 dark:hover:text-indigo-400 transition-colors"
                                title="Edit Lead"
                            >
                                <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                            </button>
                            <button
                                type="button"
                                onclick="window.deleteLead('${lead.id}')"
                                class="p-1.5 rounded-md hover:bg-rose-50 text-zinc-400 hover:text-rose-600 dark:hover:bg-rose-950/50 dark:hover:text-rose-400 transition-colors"
                                title="Delete Lead"
                            >
                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });

        html += `
                    </tbody>
                </table>
            </div>
        `;

        tableContainer.innerHTML = html;

        // Select All Handler
        const selectAllChk = document.getElementById('leads-select-all-chk');
        if (selectAllChk) {
            selectAllChk.addEventListener('change', (e) => {
                const checked = e.target.checked;
                filteredLeads.forEach(l => {
                    if (checked) {
                        state.selectedLeadIds.add(String(l.id));
                    } else {
                        state.selectedLeadIds.delete(String(l.id));
                    }
                });
                renderTableView(filteredLeads);
                renderBulkActionBar();
            });
        }

        // Row Checkboxes
        tableContainer.querySelectorAll('.lead-row-chk').forEach(chk => {
            chk.addEventListener('change', (e) => {
                const id = chk.getAttribute('data-lead-id');
                if (e.target.checked) {
                    state.selectedLeadIds.add(id);
                } else {
                    state.selectedLeadIds.delete(id);
                }
                renderBulkActionBar();
            });
        });

        if (window.lucide) lucide.createIcons();
    }

    // Render Universal Kanban View
    function renderKanbanView(filteredLeads) {
        const kanbanContainer = document.getElementById('leads-kanban-view');
        if (!kanbanContainer) return;

        const allDefs = getVisibleFieldDefinitions();
        const activeDefs = allDefs.filter(d => state.kanbanVisibleFields.includes(d.name) && d.name !== 'name' && d.name !== 'status');

        const stageConfigs = [
            {
                key: 'New',
                label: 'New',
                badgeColor: 'bg-blue-100 text-blue-800 border-blue-200 dark:bg-blue-950/60 dark:text-blue-300 dark:border-blue-900',
                headerBorder: 'border-blue-500',
            },
            {
                key: 'Contacted',
                label: 'Contacted',
                badgeColor: 'bg-amber-100 text-amber-800 border-amber-200 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-900',
                headerBorder: 'border-amber-500',
            },
            {
                key: 'Qualified',
                label: 'Qualified',
                badgeColor: 'bg-emerald-100 text-emerald-800 border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-900',
                headerBorder: 'border-emerald-500',
            },
            {
                key: 'Lost',
                label: 'Lost',
                badgeColor: 'bg-rose-100 text-rose-800 border-rose-200 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-900',
                headerBorder: 'border-rose-500',
            },
        ];

        let html = `<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-start">`;

        stageConfigs.forEach(col => {
            const colLeads = filteredLeads.filter(l => (l.status || '').toLowerCase() === col.key.toLowerCase());

            html += `
                <div class="bg-zinc-100/70 dark:bg-zinc-800/40 rounded-xl p-3 border border-zinc-200/80 dark:border-zinc-800 flex flex-col gap-3 min-h-[500px]" data-stage-column="${col.key}">
                    <!-- Column Header -->
                    <div class="flex items-center justify-between pb-2 border-b border-zinc-200 dark:border-zinc-700/60">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full ${col.headerBorder.replace('border-', 'bg-')}"></span>
                            <h3 class="text-xs font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider">${col.label}</h3>
                        </div>
                        <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full text-[11px] font-bold ${col.badgeColor}">
                            ${colLeads.length}
                        </span>
                    </div>

                    <!-- Cards Container -->
                    <div class="flex flex-col gap-2.5 flex-1 kanban-cards-droppable" data-stage="${col.key}">
            `;

            if (colLeads.length === 0) {
                html += `
                    <div class="flex flex-col items-center justify-center h-32 rounded-lg border border-dashed border-zinc-300 dark:border-zinc-700 text-zinc-400 text-xs">
                        <span>No leads</span>
                    </div>
                `;
            } else {
                colLeads.forEach(lead => {
                    const fullName = lead.name || `${lead.first_name || ''} ${lead.last_name || ''}`.trim() || 'Unnamed';
                    const isConverted = Boolean(lead.is_converted);

                    html += `
                        <div
                            class="bg-white dark:bg-zinc-900 rounded-lg p-3.5 border border-zinc-200 dark:border-zinc-800 shadow-2xs hover:shadow-md transition-all cursor-grab active:cursor-grabbing group kanban-card relative"
                            draggable="true"
                            data-lead-id="${lead.id}"
                            onclick="window.navigateToLeadDetail('${lead.id}')"
                        >
                            <!-- Top Row: Name & Quick Stage Menu -->
                            <div class="flex items-start justify-between gap-2 mb-1.5">
                                <div class="font-bold text-zinc-950 dark:text-zinc-100 text-xs hover:text-indigo-600 transition-colors">
                                    ${fullName}
                                </div>
                                <div class="flex items-center gap-1" onclick="event.stopPropagation()">
                                    ${!isConverted ? `
                                        <button
                                            type="button"
                                            onclick="window.openConvertModal('${lead.id}')"
                                            class="p-1 rounded text-zinc-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950/40"
                                            title="Convert Lead"
                                        >
                                            <i data-lucide="arrow-right-left" class="w-3.5 h-3.5"></i>
                                        </button>
                                    ` : `
                                        <span class="text-emerald-600" title="Converted">
                                            <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i>
                                        </span>
                                    `}
                                </div>
                            </div>

                            <!-- Company -->
                            <div class="text-[11px] font-semibold text-zinc-600 dark:text-zinc-400 mb-2">
                                ${lead.company || '—'}
                            </div>

                            <!-- Configured Visible Fields inside Card -->
                            <div class="space-y-1 my-2 pt-2 border-t border-zinc-100 dark:border-zinc-800 text-[11px]">
                    `;

                    activeDefs.forEach(def => {
                        html += `
                            <div class="flex items-center justify-between text-zinc-500 dark:text-zinc-400">
                                <span class="text-[10px] font-medium">${def.label}:</span>
                                <div>${formatCellValue(lead, def)}</div>
                            </div>
                        `;
                    });

                    html += `
                            </div>

                            <!-- Card Footer: Stage Mover Buttons -->
                            <div class="flex items-center justify-between mt-2 pt-2 border-t border-zinc-100 dark:border-zinc-800 text-[10px]" onclick="event.stopPropagation()">
                                <span class="text-zinc-400">Move:</span>
                                <div class="flex items-center gap-1">
                    `;

                    // Quick buttons for stages other than current
                    stageConfigs.forEach(targetSt => {
                        if (targetSt.key !== col.key) {
                            html += `
                                <button
                                    type="button"
                                    onclick="window.quickChangeStage('${lead.id}', '${targetSt.key}')"
                                    class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 transition-colors cursor-pointer"
                                    title="Move to ${targetSt.label}"
                                >
                                    ${targetSt.label.substring(0, 3)}
                                </button>
                            `;
                        }
                    });

                    html += `
                                </div>
                            </div>
                        </div>
                    `;
                });
            }

            html += `
                    </div>
                </div>
            `;
        });

        html += `</div>`;
        kanbanContainer.innerHTML = html;

        // Attach HTML5 Drag & Drop Listeners
        attachKanbanDragDrop();

        if (window.lucide) lucide.createIcons();
    }

    // HTML5 Drag and Drop for Kanban Columns
    function attachKanbanDragDrop() {
        const cards = document.querySelectorAll('.kanban-card');
        const droppables = document.querySelectorAll('.kanban-cards-droppable');

        cards.forEach(card => {
            card.addEventListener('dragstart', (e) => {
                e.dataTransfer.setData('text/plain', card.getAttribute('data-lead-id'));
                card.classList.add('opacity-50');
            });
            card.addEventListener('dragend', () => {
                card.classList.remove('opacity-50');
            });
        });

        droppables.forEach(zone => {
            zone.addEventListener('dragover', (e) => {
                e.preventDefault();
                zone.classList.add('bg-indigo-50/50', 'dark:bg-indigo-950/20');
            });
            zone.addEventListener('dragleave', () => {
                zone.classList.remove('bg-indigo-50/50', 'dark:bg-indigo-950/20');
            });
            zone.addEventListener('drop', async (e) => {
                e.preventDefault();
                zone.classList.remove('bg-indigo-50/50', 'dark:bg-indigo-950/20');
                const leadId = e.dataTransfer.getData('text/plain');
                const targetStage = zone.getAttribute('data-stage');
                if (leadId && targetStage) {
                    await window.quickChangeStage(leadId, targetStage);
                }
            });
        });
    }

    // Render Active View (Table vs Kanban)
    function renderViews() {
        const filtered = getFilteredLeads();

        const countEl = document.getElementById('leads-records-count-label');
        if (countEl) countEl.innerHTML = `Showing <strong>${filtered.length}</strong> records`;

        const tableContainer = document.getElementById('leads-table-view');
        const kanbanContainer = document.getElementById('leads-kanban-view');
        const btnTable = document.getElementById('btn-lead-view-table');
        const btnKanban = document.getElementById('btn-lead-view-kanban');

        if (state.viewMode === 'kanban') {
            if (tableContainer) tableContainer.classList.add('hidden');
            if (kanbanContainer) kanbanContainer.classList.remove('hidden');

            if (btnKanban) {
                btnKanban.className = 'flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-md bg-white text-zinc-950 shadow-xs dark:bg-zinc-800 dark:text-zinc-100 cursor-pointer';
            }
            if (btnTable) {
                btnTable.className = 'flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-md text-zinc-500 hover:text-zinc-950 dark:text-zinc-400 dark:hover:text-zinc-100 cursor-pointer';
            }
            renderKanbanView(filtered);
        } else {
            if (tableContainer) tableContainer.classList.remove('hidden');
            if (kanbanContainer) kanbanContainer.classList.add('hidden');

            if (btnTable) {
                btnTable.className = 'flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-md bg-white text-zinc-950 shadow-xs dark:bg-zinc-800 dark:text-zinc-100 cursor-pointer';
            }
            if (btnKanban) {
                btnKanban.className = 'flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-md text-zinc-500 hover:text-zinc-950 dark:text-zinc-400 dark:hover:text-zinc-100 cursor-pointer';
            }
            renderTableView(filtered);
        }

        renderBulkActionBar();
    }

    function renderLoadingState(isLoading) {
        const loader = document.getElementById('leads-loading-indicator');
        if (loader) {
            if (isLoading) loader.classList.remove('hidden');
            else loader.classList.add('hidden');
        }
    }

    function renderAll() {
        renderStageChips();
        renderActiveFilterChips();
        renderViews();
    }

    // Navigate to dedicated Lead 360 view page
    window.navigateToLeadDetail = function (leadId) {
        const url = `/leads/${leadId}`;
        if (typeof window.loadSpaPage === 'function') {
            window.loadSpaPage(url);
        } else {
            window.location.href = url;
        }
    };

    // Quick Change Lead Stage API
    window.quickChangeStage = async function (leadId, newStage) {
        try {
            const res = await fetch(`/api/leads/${leadId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify({ status: newStage })
            });

            if (res.ok) {
                const data = await res.json();
                // Update local state
                const idx = state.leads.findIndex(l => String(l.id) === String(leadId));
                if (idx !== -1) {
                    state.leads[idx].status = newStage;
                }
                showNotification(`Lead moved to ${newStage}`, 'success');
                renderAll();
            } else {
                const err = await res.json();
                showNotification(err.message || 'Failed to update stage', 'error');
            }
        } catch (e) {
            showNotification('Error updating lead status', 'error');
        }
    };

    // Open Lead Modal (Create or Edit)
    window.openLeadModal = function (mode, leadId = null) {
        state.isCreateMode = (mode === 'create');
        state.editingLead = leadId ? state.leads.find(l => String(l.id) === String(leadId)) : null;

        const modal = document.getElementById('leadFormModal');
        if (!modal) return;

        const titleEl = document.getElementById('leadFormModalTitle');
        const form = document.getElementById('leadRecordForm');
        if (!form) return;

        form.reset();

        const lead = state.editingLead || {};

        if (titleEl) {
            titleEl.textContent = state.isCreateMode ? 'Add New Lead' : `Edit Lead: ${lead.name || lead.first_name || 'Lead'}`;
        }

        // Fill Standard Fields
        const idInput = document.getElementById('lead-input-id');
        if (idInput) idInput.value = lead.id || '';

        const fNameInput = document.getElementById('lead-input-first_name');
        if (fNameInput) fNameInput.value = lead.first_name || '';

        const lNameInput = document.getElementById('lead-input-last_name');
        if (lNameInput) lNameInput.value = lead.last_name || '';

        const compInput = document.getElementById('lead-input-company');
        if (compInput) compInput.value = lead.company || '';

        const titleInput = document.getElementById('lead-input-title');
        if (titleInput) titleInput.value = lead.title || '';

        const emailInput = document.getElementById('lead-input-email');
        if (emailInput) emailInput.value = lead.email || '';

        const phoneInput = document.getElementById('lead-input-phone');
        if (phoneInput) phoneInput.value = lead.phone || '';

        const statusSelect = document.getElementById('lead-input-status');
        if (statusSelect) statusSelect.value = lead.status || 'New';

        const sourceSelect = document.getElementById('lead-input-source');
        if (sourceSelect) sourceSelect.value = lead.source || 'Website';

        const scoreInput = document.getElementById('lead-input-score');
        if (scoreInput) scoreInput.value = lead.score !== undefined ? lead.score : 50;

        const ratingSelect = document.getElementById('lead-input-rating');
        if (ratingSelect) ratingSelect.value = lead.rating || 'Warm';

        const notesInput = document.getElementById('lead-input-notes');
        if (notesInput) notesInput.value = lead.notes || '';

        const ownerSelect = document.getElementById('lead-input-assigned_to');
        if (ownerSelect) {
            ownerSelect.innerHTML = `
                <option value="">Unassigned</option>
                ${state.users.map(u => `
                    <option value="${u.id}" ${lead.assigned_to == u.id ? 'selected' : ''}>${u.name} (${u.email})</option>
                `).join('')}
            `;
        }

        // Enforce Standard Field Permissions (Read-Only / Hidden)
        const standardInputs = [
            { id: 'lead-input-first_name', name: 'first_name' },
            { id: 'lead-input-last_name', name: 'last_name' },
            { id: 'lead-input-company', name: 'company' },
            { id: 'lead-input-title', name: 'title' },
            { id: 'lead-input-email', name: 'email' },
            { id: 'lead-input-phone', name: 'phone' },
            { id: 'lead-input-status', name: 'status' },
            { id: 'lead-input-source', name: 'source' },
            { id: 'lead-input-score', name: 'score' },
            { id: 'lead-input-rating', name: 'rating' },
            { id: 'lead-input-notes', name: 'notes' },
            { id: 'lead-input-assigned_to', name: 'assigned_to' },
        ];

        standardInputs.forEach(item => {
            const el = document.getElementById(item.id);
            if (!el) return;
            const container = el.closest('.form-group-item') || el.parentElement;
            const perm = getPermission(item.name);

            if (perm === 'Hidden') {
                if (container) container.classList.add('hidden');
                el.disabled = true;
            } else if (perm === 'Read-Only') {
                if (container) container.classList.remove('hidden');
                el.disabled = true;
                el.classList.add('bg-zinc-100', 'dark:bg-zinc-800', 'cursor-not-allowed', 'opacity-75');
            } else {
                if (container) container.classList.remove('hidden');
                el.disabled = false;
                el.classList.remove('bg-zinc-100', 'dark:bg-zinc-800', 'cursor-not-allowed', 'opacity-75');
            }
        });

        // Dynamic Custom Fields Container
        const customFieldsContainer = document.getElementById('lead-dynamic-custom-fields-container');
        if (customFieldsContainer) {
            const customData = lead.custom_data || {};
            const visibleCustomFields = state.customFields.filter(cf => getPermission(cf.field_name) !== 'Hidden');

            if (visibleCustomFields.length === 0) {
                customFieldsContainer.innerHTML = `
                    <div class="col-span-full py-2 text-zinc-400 text-xs italic">
                        No additional custom fields configured for Leads.
                    </div>
                `;
            } else {
                customFieldsContainer.innerHTML = visibleCustomFields.map(cf => {
                    const perm = getPermission(cf.field_name);
                    const isReadOnly = (perm === 'Read-Only');
                    const val = customData[cf.field_name] !== undefined ? customData[cf.field_name] : (cf.default_value || '');
                    const readOnlyAttrs = isReadOnly ? 'disabled readonly class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-zinc-100 dark:bg-zinc-800 cursor-not-allowed opacity-75"' : 'class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-1 focus:ring-indigo-500 focus:outline-none"';

                    let inputHtml = '';
                    const type = (cf.data_type || 'Text').toLowerCase();

                    if (type === 'dropdown' || type === 'select') {
                        const opts = cf.options ? cf.options.split(',').map(s => s.trim()) : [];
                        inputHtml = `
                            <select name="cf_${cf.field_name}" data-custom-field="${cf.field_name}" ${readOnlyAttrs}>
                                <option value="">Select option...</option>
                                ${opts.map(opt => `<option value="${opt}" ${String(val) === String(opt) ? 'selected' : ''}>${opt}</option>`).join('')}
                            </select>
                        `;
                    } else if (type === 'textarea') {
                        inputHtml = `
                            <textarea name="cf_${cf.field_name}" data-custom-field="${cf.field_name}" rows="2" ${readOnlyAttrs} placeholder="${cf.help_text || ''}">${val}</textarea>
                        `;
                    } else if (type === 'number') {
                        inputHtml = `
                            <input type="number" name="cf_${cf.field_name}" data-custom-field="${cf.field_name}" value="${val}" ${readOnlyAttrs} placeholder="${cf.help_text || ''}">
                        `;
                    } else if (type === 'rating') {
                        inputHtml = `
                            <select name="cf_${cf.field_name}" data-custom-field="${cf.field_name}" ${readOnlyAttrs}>
                                <option value="">None</option>
                                ${[1, 2, 3, 4, 5].map(star => `<option value="${star}" ${parseInt(val, 10) === star ? 'selected' : ''}>${'★'.repeat(star)} (${star} Stars)</option>`).join('')}
                            </select>
                        `;
                    } else {
                        inputHtml = `
                            <input type="text" name="cf_${cf.field_name}" data-custom-field="${cf.field_name}" value="${val}" ${readOnlyAttrs} placeholder="${cf.help_text || ''}">
                        `;
                    }

                    return `
                        <div class="space-y-1">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                    ${cf.field_label}
                                    ${cf.is_required ? '<span class="text-rose-500">*</span>' : ''}
                                </label>
                                ${isReadOnly ? '<span class="text-[10px] text-zinc-400 font-medium inline-flex items-center gap-0.5"><i data-lucide="lock" class="w-2.5 h-2.5"></i> Read-Only</span>' : ''}
                            </div>
                            ${inputHtml}
                            ${cf.help_text ? `<p class="text-[10px] text-zinc-400">${cf.help_text}</p>` : ''}
                        </div>
                    `;
                }).join('');
            }
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        if (window.lucide) lucide.createIcons();
    };

    window.closeLeadModal = function () {
        const modal = document.getElementById('leadFormModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
        state.editingLead = null;
    };

    // Save Lead Form Handler (Create or Update)
    async function handleLeadFormSubmit(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) submitBtn.disabled = true;

        try {
            const formData = new FormData(form);
            const leadId = document.getElementById('lead-input-id')?.value;
            const isEdit = Boolean(leadId);

            const payload = {
                first_name: formData.get('first_name'),
                last_name: formData.get('last_name'),
                company: formData.get('company'),
                title: formData.get('title'),
                email: formData.get('email'),
                phone: formData.get('phone'),
                status: formData.get('status'),
                source: formData.get('source'),
                score: formData.get('score') ? parseInt(formData.get('score'), 10) : 50,
                rating: formData.get('rating'),
                notes: formData.get('notes'),
                assigned_to: formData.get('assigned_to') || null,
                custom_fields: {},
            };

            // Extract dynamic custom field inputs
            form.querySelectorAll('[data-custom-field]').forEach(el => {
                const fName = el.getAttribute('data-custom-field');
                if (fName && !el.disabled) {
                    payload.custom_fields[fName] = el.value;
                }
            });

            const url = isEdit ? `/api/leads/${leadId}` : '/api/leads';
            const method = isEdit ? 'PUT' : 'POST';

            const res = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify(payload)
            });

            const data = await res.json();

            if (res.ok && data.success) {
                showNotification(data.message || 'Lead saved successfully!', 'success');
                window.closeLeadModal();
                await fetchLeads(false);
            } else {
                showNotification(data.message || 'Failed to save lead', 'error');
            }
        } catch (err) {
            console.error(err);
            showNotification('Error saving lead. Please check your network.', 'error');
        } finally {
            if (submitBtn) submitBtn.disabled = false;
        }
    }

    // Delete Lead
    window.deleteLead = async function (leadId) {
        if (!confirm('Are you sure you want to delete this lead? This action cannot be undone.')) {
            return;
        }

        try {
            const res = await fetch(`/api/leads/${leadId}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                }
            });

            const data = await res.json();
            if (res.ok && data.success) {
                showNotification(data.message || 'Lead deleted', 'success');
                state.selectedLeadIds.delete(String(leadId));
                state.leads = state.leads.filter(l => String(l.id) !== String(leadId));
                renderAll();
            } else {
                showNotification(data.message || 'Failed to delete lead', 'error');
            }
        } catch (err) {
            showNotification('Error deleting lead', 'error');
        }
    };

    // Bulk Delete
    window.deleteSelectedLeads = async function () {
        const ids = Array.from(state.selectedLeadIds);
        if (ids.length === 0) return;

        if (!confirm(`Are you sure you want to delete ${ids.length} selected lead(s)?`)) {
            return;
        }

        try {
            for (const id of ids) {
                await fetch(`/api/leads/${id}`, {
                    method: 'DELETE',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': getCsrfToken() }
                });
            }
            showNotification(`${ids.length} leads deleted successfully`, 'success');
            state.selectedLeadIds.clear();
            await fetchLeads(true);
        } catch (_) {
            showNotification('Error deleting selected leads', 'error');
        }
    };

    // Open Convert Lead Modal
    window.openConvertModal = function (leadId) {
        const lead = state.leads.find(l => String(l.id) === String(leadId));
        if (!lead) return;

        state.convertingLead = lead;
        const modal = document.getElementById('convertLeadModal');
        if (!modal) return;

        const titleEl = document.getElementById('convertLeadTitle');
        if (titleEl) {
            titleEl.textContent = `Convert Lead: ${lead.first_name} ${lead.last_name}`;
        }

        const previewAcc = document.getElementById('convert-preview-account');
        if (previewAcc) previewAcc.textContent = lead.company || `${lead.first_name} ${lead.last_name}`;

        const previewContact = document.getElementById('convert-preview-contact');
        if (previewContact) previewContact.textContent = `${lead.first_name} ${lead.last_name} (${lead.email || 'No email'})`;

        const notesInput = document.getElementById('convert-input-notes');
        if (notesInput) notesInput.value = lead.notes || '';

        const oppNameInput = document.getElementById('convert-input-opp-name');
        if (oppNameInput) oppNameInput.value = `${lead.company || lead.first_name} — Deal`;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        if (window.lucide) lucide.createIcons();
    };

    window.closeConvertModal = function () {
        const modal = document.getElementById('convertLeadModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
        state.convertingLead = null;
    };

    // Handle Confirm Conversion
    async function handleConvertSubmit(e) {
        e.preventDefault();
        if (!state.convertingLead) return;

        const form = e.target;
        const btn = form.querySelector('button[type="submit"]');
        if (btn) btn.disabled = true;

        try {
            const createOpp = document.getElementById('convert-chk-create-opp')?.checked || false;
            const oppName = document.getElementById('convert-input-opp-name')?.value;
            const oppAmount = document.getElementById('convert-input-opp-amount')?.value;

            const res = await fetch(`/api/leads/${state.convertingLead.id}/convert`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify({
                    create_opportunity: createOpp,
                    opportunity_name: oppName,
                    opportunity_amount: oppAmount ? parseFloat(oppAmount) : 50000,
                })
            });

            const data = await res.json();
            if (res.ok && data.success) {
                showNotification(data.message || 'Lead converted successfully!', 'success');
                window.closeConvertModal();
                await fetchLeads(false);
            } else {
                showNotification(data.message || 'Conversion failed', 'error');
            }
        } catch (err) {
            showNotification('Conversion error occurred', 'error');
        } finally {
            if (btn) btn.disabled = false;
        }
    }

    // Open Transfer Lead Ownership Modal
    window.openTransferModal = function (leadId) {
        const lead = state.leads.find(l => String(l.id) === String(leadId));
        if (!lead) return;

        state.transferringLead = lead;
        const modal = document.getElementById('transferLeadModal');
        if (!modal) return;

        const title = document.getElementById('transferLeadTitle');
        if (title) title.textContent = `Transfer Lead: ${lead.name || lead.first_name} (${lead.company || ''})`;

        const currentOwner = document.getElementById('transferCurrentOwner');
        if (currentOwner) currentOwner.textContent = lead.assigned_to_name || 'Unassigned';

        const userSelect = document.getElementById('transferTargetUser');
        if (userSelect) {
            userSelect.innerHTML = state.users.map(u => `
                <option value="${u.id}" ${lead.assigned_to == u.id ? 'selected' : ''}>${u.name} (${u.email})</option>
            `).join('');
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        if (window.lucide) lucide.createIcons();
    };

    window.closeTransferModal = function () {
        const modal = document.getElementById('transferLeadModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
        state.transferringLead = null;
    };

    // Handle Transfer Submit
    async function handleTransferSubmit(e) {
        e.preventDefault();
        if (!state.transferringLead) return;

        const form = e.target;
        const btn = form.querySelector('button[type="submit"]');
        if (btn) btn.disabled = true;

        try {
            const targetUserId = document.getElementById('transferTargetUser')?.value;

            const res = await fetch(`/api/leads/${state.transferringLead.id}/transfer`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify({ targetUserId })
            });

            const data = await res.json();
            if (res.ok && data.success) {
                showNotification(data.message || 'Ownership transferred', 'success');
                window.closeTransferModal();
                await fetchLeads(false);
            } else {
                showNotification(data.message || 'Transfer failed', 'error');
            }
        } catch (_) {
            showNotification('Error during transfer', 'error');
        } finally {
            if (btn) btn.disabled = false;
        }
    }

    // Open Customize Columns Modal
    window.openCustomizeModal = function () {
        const modal = document.getElementById('customizeColumnsModal');
        if (!modal) return;

        const container = document.getElementById('customize-fields-checkbox-list');
        if (!container) return;

        const allDefs = getVisibleFieldDefinitions();
        const activeSet = new Set(state.viewMode === 'table' ? state.tableVisibleFields : state.kanbanVisibleFields);

        container.innerHTML = allDefs.map(def => {
            const isChecked = activeSet.has(def.name);
            return `
                <label class="flex items-center justify-between p-2 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer border border-transparent hover:border-zinc-200 dark:hover:border-zinc-700">
                    <div class="flex items-center gap-2.5">
                        <input
                            type="checkbox"
                            value="${def.name}"
                            class="customize-col-chk rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                            ${isChecked ? 'checked' : ''}
                        >
                        <span class="text-xs font-semibold text-zinc-900 dark:text-zinc-100">${def.label}</span>
                    </div>
                    <span class="text-[10px] uppercase font-bold px-1.5 py-0.5 rounded ${def.isCustom ? 'bg-purple-100 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300' : 'bg-zinc-200 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300'}">
                        ${def.isCustom ? 'Custom' : 'Standard'}
                    </span>
                </label>
            `;
        }).join('');

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    };

    window.closeCustomizeModal = function () {
        const modal = document.getElementById('customizeColumnsModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    };

    window.saveCustomizeFields = function () {
        const checked = [];
        document.querySelectorAll('.customize-col-chk:checked').forEach(el => {
            checked.push(el.value);
        });

        if (checked.length === 0) {
            showNotification('At least one field must remain visible', 'error');
            return;
        }

        if (state.viewMode === 'table') {
            state.tableVisibleFields = checked;
        } else {
            state.kanbanVisibleFields = checked;
        }

        saveViewConfig();
        window.closeCustomizeModal();
        renderViews();
        showNotification('View preferences updated', 'success');
    };

    // Open Filter Modal
    window.openFilterModal = function () {
        const modal = document.getElementById('leadsFilterModal');
        if (!modal) return;

        const fieldSelect = document.getElementById('filter-rule-field');
        if (fieldSelect) {
            const defs = getVisibleFieldDefinitions();
            fieldSelect.innerHTML = defs.map(d => `<option value="${d.name}">${d.label}</option>`).join('');
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    };

    window.closeFilterModal = function () {
        const modal = document.getElementById('leadsFilterModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    };

    window.addFilterRule = function () {
        const field = document.getElementById('filter-rule-field')?.value;
        const operator = document.getElementById('filter-rule-operator')?.value || 'contains';
        const val = document.getElementById('filter-rule-value')?.value || '';

        if (!val.trim()) {
            showNotification('Please enter a filter value', 'error');
            return;
        }

        state.activeFilters.push({ field, operator, value: val.trim() });
        window.closeFilterModal();
        renderActiveFilterChips();
        renderViews();
        updateFilterBtnBadge();
        showNotification('Filter applied', 'success');
    };

    // Switch View Mode (Table vs Kanban)
    window.switchLeadsViewMode = function (mode) {
        state.viewMode = mode;
        renderViews();
    };

    // Export Leads as CSV
    window.exportLeadsCsv = function () {
        const filtered = getFilteredLeads();
        if (filtered.length === 0) {
            showNotification('No records to export', 'error');
            return;
        }

        const visibleDefs = getVisibleFieldDefinitions().filter(d => state.tableVisibleFields.includes(d.name));
        const headers = visibleDefs.map(d => `"${d.label.replace(/"/g, '""')}"`);

        const rows = filtered.map(lead => {
            return visibleDefs.map(d => {
                let v = lead[d.name] !== undefined ? lead[d.name] : (lead.custom_data ? lead.custom_data[d.name] : '');
                if (v === null || v === undefined) v = '';
                return `"${String(v).replace(/"/g, '""')}"`;
            }).join(',');
        });

        const csvContent = [headers.join(','), ...rows].join('\n');
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `leads_export_${new Date().toISOString().substring(0, 10)}.csv`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
        showNotification('CSV export downloaded', 'success');
    };

    // Main Initializer
    window.initLeadsModule = function () {
        const root = document.getElementById('leads-page-root');
        if (!root) return;

        // Initialize state from window.__LEADS_DATA__ if available
        if (window.__LEADS_DATA__) {
            const serverData = window.__LEADS_DATA__;
            state.leads = Array.isArray(serverData.initialLeads) ? [...serverData.initialLeads] : [];
            state.customFields = Array.isArray(serverData.customFields) ? [...serverData.customFields] : [];
            state.fieldPermissions = serverData.fieldPermissions || {};
            state.users = Array.isArray(serverData.users) ? [...serverData.users] : [];
            state.stages = Array.isArray(serverData.stages) ? serverData.stages : state.stages;
            state.sources = Array.isArray(serverData.sources) ? serverData.sources : state.sources;
        }

        loadSavedViewConfig();

        // Bind Search Input with debouncing
        const searchInput = document.getElementById('leads-search-input');
        if (searchInput) {
            let debounceTimer = null;
            searchInput.value = state.searchTerm;
            searchInput.addEventListener('input', (e) => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    state.searchTerm = e.target.value.trim();
                    renderViews();
                }, 200);
            });
        }

        // Bind Forms
        const leadForm = document.getElementById('leadRecordForm');
        if (leadForm) {
            leadForm.removeEventListener('submit', handleLeadFormSubmit);
            leadForm.addEventListener('submit', handleLeadFormSubmit);
        }

        const convertForm = document.getElementById('convertLeadForm');
        if (convertForm) {
            convertForm.removeEventListener('submit', handleConvertSubmit);
            convertForm.addEventListener('submit', handleConvertSubmit);
        }

        const transferForm = document.getElementById('transferLeadForm');
        if (transferForm) {
            transferForm.removeEventListener('submit', handleTransferSubmit);
            transferForm.addEventListener('submit', handleTransferSubmit);
        }

        // Render Initial UI
        renderAll();

        if (window.lucide) {
            lucide.createIcons();
        }
    };

    // Auto-initialize when document is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', window.initLeadsModule);
    } else {
        window.initLeadsModule();
    }
})();
