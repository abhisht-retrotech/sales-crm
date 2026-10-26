/**
 * DealSync Enterprise CRM — Leads & Intake Module
 * 1:1 Parity with React Client (LeadsView.tsx, CompactDataTable.tsx, UniversalKanban.tsx)
 * Pure Tailwind CSS styling with Dynamic Custom Fields & Field-Level Security Permissions
 */

(function () {
    'use strict';

    // Base Field Definitions matching React LeadsView.tsx LEAD_FIELD_DEFINITIONS (lines 30-50)
    const BASE_FIELD_DEFINITIONS = [
        { name: 'name', label: 'Full Name' },
        { name: 'first_name', label: 'First Name' },
        { name: 'last_name', label: 'Last Name' },
        { name: 'company', label: 'Company Name' },
        { name: 'title', label: 'Job Title' },
        { name: 'score', label: 'Lead Score', type: 'number' },
        { name: 'status', label: 'Status', options: ['New', 'Contacted', 'Working', 'Qualified', 'Lost'] },
        { name: 'email', label: 'Email Address' },
        { name: 'email_opt_out', label: 'Email Opt Out', type: 'boolean' },
        { name: 'phone', label: 'Phone Number' },
        {
            name: 'source',
            label: 'Lead Source',
            options: ['Direct Inbound', 'Website', 'Referral', 'Cold Outreach', 'Partner', 'Trade Show / Event', 'LinkedIn', 'Google Ads', 'Other']
        },
        { name: 'assigned_to', label: 'Owner' },
        { name: 'created_at', label: 'Created Date' },
        { name: 'updated_at', label: 'Last Modified' },
        { name: 'notes', label: 'Qualification Notes', type: 'textarea' }
    ];

    const LEAD_STAGES = ['New', 'Contacted', 'Working', 'Qualified', 'Lost'];

    const STAGE_COLOR_MAP = {
        New: '#2563eb',       // blue-600
        Contacted: '#d97706', // amber-600
        Working: '#4b5563',   // gray-600
        Qualified: '#059669', // emerald-600
        Unqualified: '#6b7280', // zinc-500
        Lost: '#dc2626'       // red-600
    };

    function getStageColor(stage) {
        if (!stage) return '#2563eb';
        if (STAGE_COLOR_MAP[stage]) return STAGE_COLOR_MAP[stage];
        const colors = ['#2563eb', '#0d9488', '#059669', '#d97706', '#7c3aed', '#db2777', '#4b5563', '#ea580c', '#0284c7'];
        let hash = 0;
        for (let i = 0; i < stage.length; i++) {
            hash = stage.charCodeAt(i) + ((hash << 5) - hash);
        }
        return colors[Math.abs(hash) % colors.length];
    }

    // Module State
    const state = {
        leads: [],
        loading: false,
        searchTerm: '',
        viewMode: 'table', // 'table' | 'kanban'
        activeFilters: [], // array of { id, field, operator, value }
        selectedRecord: null,
        isConfigModalOpen: false,
        isFilterModalOpen: false,
        isBulkWizardOpen: false,
        tableVisibleFields: ['name', 'company', 'score', 'status', 'email', 'assigned_to'],
        kanbanVisibleFields: ['company', 'score', 'email', 'assigned_to'],
        convertingLead: null,
        transferringLead: null,
        editingLead: null,
        isSubmitting: false,
        feedback: null,
        canSendBulkEmail: true,
        selectedLeadIds: [],
        customFields: [],
        fieldPermissions: {},
        profilePermissions: {
            can_read: true,
            can_create: true,
            can_update: true,
            can_delete: true,
            can_transfer: true,
            record_scope: 'All',
        },
        users: [],
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
        sortField: null,
        sortDirection: null,
        colWidths: {},
        isResizing: false,
        lastResizeTime: 0,
        stages: ['New', 'Contacted', 'Working', 'Qualified', 'Lost'],
        standardOverrides: {},
        pageLayout: null,
    };

    // Helper: CSRF Header
    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    // Helper: Safe HTML escape
    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // Helper: Get Field Permission
    function getPermission(fieldName) {
        if (state.fieldPermissions && state.fieldPermissions[fieldName]) {
            return state.fieldPermissions[fieldName];
        }
        return 'Read/Write';
    }

    // Get Merged Field Definitions
    function getAllFieldDefinitions() {
        const defs = BASE_FIELD_DEFINITIONS.map(d => {
            const copy = { ...d };
            if (state.standardOverrides && state.standardOverrides[d.name]) {
                const ov = state.standardOverrides[d.name];
                if (ov.field_label) copy.label = ov.field_label;
                if (ov.options) {
                    copy.options = typeof ov.options === 'string' ? ov.options.split(',').map(s => s.trim()).filter(Boolean) : ov.options;
                }
            }
            if (d.name === 'status' && state.stages && state.stages.length > 0) {
                copy.options = [...state.stages];
            }
            if (d.name === 'source' && state.sources && state.sources.length > 0) {
                copy.options = [...state.sources];
            }
            return copy;
        });

        // Append custom fields
        (state.customFields || []).forEach(cf => {
            const fName = cf.field_name;
            if (!defs.some(d => d.name === fName)) {
                defs.push({
                    name: fName,
                    label: cf.field_label || fName,
                    type: (cf.data_type || 'text').toLowerCase(),
                    options: cf.options ? cf.options.split(',').map(s => s.trim()) : [],
                    isCustom: true,
                });
            }
        });

        return defs.filter(d => getPermission(d.name) !== 'Hidden');
    }

    // Load View Config from LocalStorage
    function loadSavedViewConfig() {
        try {
            const saved = localStorage.getItem('crm_view_config_leads');
            if (saved) {
                const parsed = JSON.parse(saved);
                if (parsed.viewMode === 'table' || parsed.viewMode === 'kanban') {
                    state.viewMode = parsed.viewMode;
                }
                if (Array.isArray(parsed.tableVisibleFields) && parsed.tableVisibleFields.length > 0) {
                    state.tableVisibleFields = parsed.tableVisibleFields;
                }
                if (Array.isArray(parsed.kanbanVisibleFields) && parsed.kanbanVisibleFields.length > 0) {
                    state.kanbanVisibleFields = parsed.kanbanVisibleFields;
                }
                if (parsed.colWidths && typeof parsed.colWidths === 'object') {
                    state.colWidths = { ...state.colWidths, ...parsed.colWidths };
                }
            }
        } catch (_) { }

        try {
            const widths = localStorage.getItem('crm_col_widths_leads');
            if (widths) {
                const parsedWidths = JSON.parse(widths);
                if (parsedWidths && typeof parsedWidths === 'object') {
                    state.colWidths = { ...state.colWidths, ...parsedWidths };
                }
            }
        } catch (_) { }
    }

    // Save View Config to LocalStorage
    function persistViewConfig() {
        try {
            localStorage.setItem('crm_view_config_leads', JSON.stringify({
                viewMode: state.viewMode,
                tableVisibleFields: state.tableVisibleFields,
                kanbanVisibleFields: state.kanbanVisibleFields,
                colWidths: state.colWidths,
            }));
        } catch (_) { }

        try {
            localStorage.setItem('crm_col_widths_leads', JSON.stringify(state.colWidths));
        } catch (_) { }
    }

    // Persist customized column widths to backend database for 100% permanence across refreshes and devices
    function saveColumnWidthsToDatabase(widths) {
        if (!widths || typeof widths !== 'object') return;
        try {
            fetch('/api/leads/column-widths', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ col_widths: widths })
            })
            .then(res => res.json())
            .then(data => {
                // Column widths persisted in database successfully
            })
            .catch(err => {
                console.warn('[Leads] Database column widths persistence notice:', err);
            });
        } catch (err) {
            console.warn('[Leads] Error calling saveColumnWidthsToDatabase:', err);
        }
    }

    // Persist customized visible fields (sequence & selection) to backend database
    function saveViewFieldsToDatabase(fields, viewMode = 'table') {
        if (!fields || !Array.isArray(fields) || fields.length === 0) return;
        try {
            fetch('/api/leads/view-fields', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ fields: fields, view_mode: viewMode })
            })
            .then(res => res.json())
            .then(data => {
                // Visible fields persisted in database successfully
            })
            .catch(err => {
                console.warn('[Leads] Database view fields persistence notice:', err);
            });
        } catch (err) {
            console.warn('[Leads] Error calling saveViewFieldsToDatabase:', err);
        }
    }

    // Show Feedback Alert Banner matching React Lines 193-210
    function setFeedback(msg) {
        state.feedback = msg;
        const banner = document.getElementById('leads-feedback-banner');
        const textEl = document.getElementById('leads-feedback-message');
        if (!banner || !textEl) return;

        if (msg) {
            textEl.textContent = msg;
            banner.classList.remove('hidden');
            banner.classList.add('flex');
            setTimeout(() => {
                if (state.feedback === msg) {
                    setFeedback(null);
                }
            }, 5000);
        } else {
            banner.classList.add('hidden');
            banner.classList.remove('flex');
            textEl.textContent = '';
        }
    }

    // Universal Filter Evaluation Engine (Matching UniversalFilterModal.tsx evaluateFilterRule)
    function evaluateFilterRule(lead, rule, fieldDef) {
        if (!rule.field) return true;

        let itemValue = lead[rule.field];
        if (rule.field === 'name' && (itemValue === undefined || itemValue === null)) {
            itemValue = `${lead.first_name || ''} ${lead.last_name || ''}`.trim();
        }
        if (itemValue === undefined && lead.custom_data) {
            itemValue = lead.custom_data[rule.field];
        }

        if (itemValue === undefined || itemValue === null) {
            itemValue = '';
        }

        const strItem = String(itemValue).toLowerCase().trim();
        const strRule = String(rule.value || '').toLowerCase().trim();

        switch (rule.operator) {
            case 'equals':
                return strItem === strRule;
            case 'not_equals':
                return strItem !== strRule;
            case 'contains':
                return strItem.includes(strRule);
            case 'not_contains':
                return !strItem.includes(strRule);
            case 'starts_with':
                return strItem.startsWith(strRule);
            case 'greater_than': {
                const numItem = parseFloat(String(itemValue).replace(/[^0-9.-]+/g, ''));
                const numRule = parseFloat(rule.value);
                if (isNaN(numItem) || isNaN(numRule)) return false;
                return numItem > numRule;
            }
            case 'less_than': {
                const numItem = parseFloat(String(itemValue).replace(/[^0-9.-]+/g, ''));
                const numRule = parseFloat(rule.value);
                if (isNaN(numItem) || isNaN(numRule)) return false;
                return numItem < numRule;
            }
            case 'greater_equal': {
                const numItem = parseFloat(String(itemValue).replace(/[^0-9.-]+/g, ''));
                const numRule = parseFloat(rule.value);
                if (isNaN(numItem) || isNaN(numRule)) return false;
                return numItem >= numRule;
            }
            case 'less_equal': {
                const numItem = parseFloat(String(itemValue).replace(/[^0-9.-]+/g, ''));
                const numRule = parseFloat(rule.value);
                if (isNaN(numItem) || isNaN(numRule)) return false;
                return numItem <= numRule;
            }
            case 'is_empty':
                return itemValue === '' || itemValue === null || itemValue === undefined;
            case 'is_not_empty':
                return itemValue !== '' && itemValue !== null && itemValue !== undefined;
            default:
                return true;
        }
    }

    // Filter Leads based on Search Term & Active Filter Rules
    function getFilteredLeads() {
        let results = [...state.leads];

        // 1. Search Term
        if (state.searchTerm) {
            const term = state.searchTerm.toLowerCase().trim();
            results = results.filter(lead => {
                const name = (lead.name || `${lead.first_name || ''} ${lead.last_name || ''}`).toLowerCase();
                const company = (lead.company || '').toLowerCase();
                const email = (lead.email || '').toLowerCase();
                const title = (lead.title || '').toLowerCase();
                const phone = (lead.phone || '').toLowerCase();
                const owner = (lead.assigned_to_name || lead.assigned_user?.name || '').toLowerCase();
                return name.includes(term) ||
                    company.includes(term) ||
                    email.includes(term) ||
                    title.includes(term) ||
                    phone.includes(term) ||
                    owner.includes(term);
            });
        }

        // 2. Active Filter Rules
        if (state.activeFilters.length > 0) {
            const allDefs = getAllFieldDefinitions();
            results = results.filter(lead => {
                return state.activeFilters.every(rule => {
                    const def = allDefs.find(f => f.name === rule.field);
                    return evaluateFilterRule(lead, rule, def);
                });
            });
        }

        // 3. Sorting (Table view)
        // 3-State Cycle: Only sort when sortField is active AND sortDirection is 'asc' or 'desc'.
        // If sortDirection is null, return original backend ordering (normal form).
        if (state.sortField && (state.sortDirection === 'asc' || state.sortDirection === 'desc')) {
            const sf = state.sortField;
            const dir = state.sortDirection === 'desc' ? -1 : 1;
            results.sort((a, b) => {
                let va = a[sf];
                let vb = b[sf];
                if (sf === 'name') {
                    va = a.name || `${a.first_name || ''} ${a.last_name || ''}`;
                    vb = b.name || `${b.first_name || ''} ${b.last_name || ''}`;
                }
                if (va === undefined || va === null) va = '';
                if (vb === undefined || vb === null) vb = '';

                if (typeof va === 'number' && typeof vb === 'number') {
                    return (va - vb) * dir;
                }
                return String(va).localeCompare(String(vb), undefined, { numeric: true, sensitivity: 'base' }) * dir;
            });
        }

        return results;
    }

    // Refresh Leads from Backend
    async function fetchLeads(showLoader = true) {
        if (showLoader) {
            state.loading = true;
            renderLoading(true);
        }

        try {
            const params = new URLSearchParams();
            if (state.searchTerm) params.append('search', state.searchTerm);

            const res = await fetch(`/api/leads?${params.toString()}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (res.ok) {
                const data = await res.json();
                state.leads = Array.isArray(data) ? data : (data.leads || []);
                if (data.col_widths && typeof data.col_widths === 'object' && Object.keys(data.col_widths).length > 0) {
                    state.colWidths = { ...state.colWidths, ...data.col_widths };
                }
                if (Array.isArray(data.table_visible_fields) && data.table_visible_fields.length > 0) {
                    state.tableVisibleFields = data.table_visible_fields;
                }
                if (Array.isArray(data.kanban_visible_fields) && data.kanban_visible_fields.length > 0) {
                    state.kanbanVisibleFields = data.kanban_visible_fields;
                }
                if (data.profile_permissions) {
                    state.profilePermissions = Object.assign({}, state.profilePermissions, data.profile_permissions);
                }
                if (data.field_permissions) {
                    state.fieldPermissions = Object.assign({}, state.fieldPermissions, data.field_permissions);
                }
                if (Array.isArray(data.stages) && data.stages.length > 0) {
                    state.stages = data.stages;
                }
                if (Array.isArray(data.sources)) {
                    state.sources = data.sources;
                }
                if (data.standard_overrides && typeof data.standard_overrides === 'object') {
                    state.standardOverrides = data.standard_overrides;
                }
                if (data.page_layout) {
                    state.pageLayout = data.page_layout;
                }
                if (Array.isArray(data.custom_fields)) {
                    state.customFields = data.custom_fields;
                }
                renderCurrentViews();
            }
        } catch (err) {
            console.error('[Leads] Fetch error:', err);
        } finally {
            state.loading = false;
            renderLoading(false);
        }
    }

    // Render Loading State
    function renderLoading(isLoading) {
        const indicator = document.getElementById('leads-loading-indicator');
        if (indicator) {
            if (isLoading) {
                indicator.classList.remove('hidden');
            } else {
                indicator.classList.add('hidden');
            }
        }
    }

    // Render Active Filter Chips (Matching React ActiveFilterChips)
    function renderActiveFilterChips() {
        const container = document.getElementById('leads-active-filter-chips-container');
        const badge = document.getElementById('leads-filter-badge');
        const filterBtn = document.getElementById('leads-filter-btn');
        if (!container) return;

        const count = state.activeFilters.length;
        if (badge) {
            if (count > 0) {
                badge.textContent = count;
                badge.classList.remove('hidden');
                if (filterBtn) {
                    filterBtn.classList.add('border-blue-300', 'bg-blue-50/70', 'text-blue-700', 'dark:border-blue-800', 'dark:bg-blue-950/40', 'dark:text-blue-300');
                }
            } else {
                badge.classList.add('hidden');
                if (filterBtn) {
                    filterBtn.classList.remove('border-blue-300', 'bg-blue-50/70', 'text-blue-700', 'dark:border-blue-800', 'dark:bg-blue-950/40', 'dark:text-blue-300');
                }
            }
        }

        if (count === 0) {
            container.classList.add('hidden');
            container.innerHTML = '';
            return;
        }

        container.classList.remove('hidden');
        const defs = getAllFieldDefinitions();

        const chipsHtml = state.activeFilters.map(rule => {
            const def = defs.find(d => d.name === rule.field);
            const label = def ? def.label : rule.field;
            const opDisplay = rule.operator.replace('_', ' ');

            return `
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-blue-50 text-blue-800 border border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800 shadow-2xs">
                    <span class="font-semibold text-blue-900 dark:text-blue-200">${escapeHtml(label)}</span>
                    <span class="text-blue-600 dark:text-blue-400 font-normal">${escapeHtml(opDisplay)}</span>
                    <span class="font-bold text-zinc-900 dark:text-zinc-100">"${escapeHtml(rule.value)}"</span>
                    <button type="button" data-remove-id="${rule.id}" class="remove-chip-btn text-blue-500 hover:text-blue-800 dark:hover:text-blue-200 ml-0.5 cursor-pointer font-bold">
                        ✕
                    </button>
                </span>
            `;
        }).join('');

        container.innerHTML = `
            <span class="text-xs font-semibold text-zinc-500 dark:text-zinc-400">Active Filters:</span>
            ${chipsHtml}
            <button type="button" id="clear-all-filter-chips-btn" class="text-xs text-rose-600 dark:text-rose-400 hover:underline font-semibold ml-1 cursor-pointer no-underline hover:no-underline">
                Clear all
            </button>
        `;

        container.querySelectorAll('.remove-chip-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.getAttribute('data-remove-id');
                state.activeFilters = state.activeFilters.filter(r => r.id !== id);
                renderActiveFilterChips();
                renderCurrentViews();
            });
        });

        const clearBtn = document.getElementById('clear-all-filter-chips-btn');
        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                state.activeFilters = [];
                renderActiveFilterChips();
                renderCurrentViews();
            });
        }
    }

    // Render Selected Leads Action Banner (Matching React lines 353-398)
    function renderSelectedActionBanner() {
        const banner = document.getElementById('leads-selected-action-banner');
        const countLabel = document.getElementById('leads-selected-count-label');
        const sendLabel = document.getElementById('leads-bulk-send-label');
        if (!banner) return;

        const count = state.selectedLeadIds.length;
        if (state.canSendBulkEmail && count > 0) {
            banner.classList.remove('hidden');
            banner.classList.add('flex');
            if (countLabel) countLabel.textContent = `${count} lead${count > 1 ? 's' : ''} selected`;
            if (sendLabel) sendLabel.textContent = `Send Bulk Email (${count})`;
        } else {
            banner.classList.add('hidden');
            banner.classList.remove('flex');
        }
    }

    // Default column widths matching target screenshot
    const DEFAULT_COL_WIDTHS = {
        name: 170,
        company: 180,
        score: 110,
        status: 110,
        email: 220,
        assigned_to: 160
    };

    // Format Table Cell Value (Pixel-perfect parity with target layout)
    function formatTableCell(lead, colKey) {
        const rawVal = lead[colKey] !== undefined ? lead[colKey] : (lead.custom_data ? lead.custom_data[colKey] : null);
        const resolvedName = lead[`${colKey}_name`] || (lead.custom_data && lead.custom_data[`${colKey}_name`]) || lead[`${colKey}_display`];
        const val = (resolvedName !== undefined && resolvedName !== null) ? resolvedName : rawVal;

        // Name / First Name
        if (colKey === 'name' || colKey === 'first_name') {
            const fullName = lead.name || `${lead.first_name || ''} ${lead.last_name || ''}`.trim() || lead.company || 'Unnamed Lead';
            return `
                <div class="flex items-center gap-2">
                    <span
                        onclick="event.stopPropagation(); window.openLeadDetailFullscreenModal('${lead.id}')"
                        class="font-bold text-slate-800 dark:text-zinc-100 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors truncate cursor-pointer hover:underline"
                        title="View Lead 360 Fullscreen"
                    >
                        ${escapeHtml(fullName)}
                    </span>
                    ${lead.is_converted ? `
                        <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 shrink-0">
                            Converted
                        </span>
                    ` : ''}
                </div>
            `;
        }

        // Company Name
        if (colKey === 'company') {
            const companyName = val || '—';
            return `<span class="text-slate-700 dark:text-zinc-300 text-xs truncate block" title="${escapeHtml(companyName)}">${escapeHtml(companyName)}</span>`;
        }

        // Lead Score
        if (colKey === 'score') {
            const score = Number(val);
            if (isNaN(score)) return `<span class="text-zinc-300 dark:text-zinc-600">—</span>`;
            if (score >= 80) {
                return `
                    <span class="inline-flex items-center justify-center min-w-[32px] h-5 px-2 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-300 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-700">
                        ${score}
                    </span>
                `;
            }
            return `
                <span class="inline-flex items-center justify-center min-w-[32px] h-5 px-2 rounded-full text-xs font-bold bg-amber-50 text-slate-800 border border-amber-300 dark:bg-amber-950/40 dark:text-amber-200 dark:border-amber-700">
                    ${score}
                </span>
            `;
        }

        // Status
        if (colKey === 'status') {
            const valStr = String(val || 'New');
            const color = getStageColor(valStr);
            return `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold" style="background-color: ${color}18; color: ${color}; border: 1px solid ${color}35;">${escapeHtml(valStr)}</span>`;
        }

        // Email Address
        if (colKey === 'email') {
            if (!val) return `<span class="text-zinc-300 dark:text-zinc-600">—</span>`;
            return `
                <span class="text-slate-600 dark:text-zinc-400 text-xs truncate block max-w-[210px]" title="${escapeHtml(val)}">
                    ${escapeHtml(val)}
                </span>
            `;
        }

        // Assigned Owner
        if (colKey === 'assigned_to' || colKey === 'owner') {
            const ownerVal = lead.assigned_to_name || lead.assigned_user?.name || (val ? `User #${val}` : 'Unassigned');
            const initial = String(ownerVal).charAt(0).toUpperCase();

            let avatarStyle = 'bg-sky-100 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300';
            if (ownerVal === 'Unassigned' || ownerVal.startsWith('usr-') || ownerVal.startsWith('User #')) {
                avatarStyle = 'bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-zinc-400';
            } else if (initial === 'M') {
                avatarStyle = 'bg-slate-800 text-white dark:bg-zinc-700 dark:text-zinc-100';
            } else if (initial === 'J' || initial === 'A') {
                avatarStyle = 'bg-sky-100 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300';
            }

            return `
                <div class="inline-flex items-center gap-2 text-slate-800 dark:text-zinc-200">
                    <span class="w-5 h-5 rounded-full ${avatarStyle} text-[10px] font-bold flex items-center justify-center shrink-0">
                        ${initial}
                    </span>
                    <span class="truncate max-w-[140px] text-xs font-medium" title="${escapeHtml(ownerVal)}">${escapeHtml(ownerVal)}</span>
                </div>
            `;
        }

        // Phone
        if (colKey === 'phone') {
            if (!val) return `<span class="text-zinc-300 dark:text-zinc-600">—</span>`;
            return `
                <span class="text-sky-700 dark:text-sky-400 font-mono text-[11px]">
                    ${escapeHtml(val)}
                </span>
            `;
        }

        // Null / undefined fallback
        if (val === null || val === undefined || val === '') {
            return `<span class="text-zinc-300 dark:text-zinc-600">—</span>`;
        }

        return `<span class="truncate block text-xs" title="${escapeHtml(val)}">${escapeHtml(val)}</span>`;
    }

    // High-performance, mathematically synchronized Column Edge Resizer Engine
    function initLeadColumnResize(container) {
        const table = container.querySelector('#leads-main-data-table') || container.querySelector('table');
        if (!table) return;

        const resizers = container.querySelectorAll('.lead-col-resizer, .table-col-resizer');
        resizers.forEach(resizer => {
            const th = resizer.closest('th');
            if (!th) return;

            const colName = resizer.getAttribute('data-col-name') || th.getAttribute('data-col-name');
            if (!colName) return;

            resizer.addEventListener('mousedown', (e) => {
                if (e.button !== 0) return; // Only primary mouse button

                e.preventDefault();
                e.stopPropagation();

                state.isResizing = true;
                const startX = e.clientX;
                const startColWidth = th.getBoundingClientRect().width;
                const startTableWidth = table.getBoundingClientRect().width;
                const colEl = table.querySelector(`col[data-col-name="${colName}"]`);

                resizer.classList.add('is-resizing');
                document.body.classList.add('is-resizing-lead-col');

                function onMouseMove(moveEvent) {
                    if (!state.isResizing) return;
                    moveEvent.preventDefault();

                    const deltaX = moveEvent.clientX - startX;
                    const newColWidth = Math.max(65, Math.round(startColWidth + deltaX));
                    const actualDelta = newColWidth - startColWidth;

                    // 1. Update this column width on th and col
                    th.style.width = newColWidth + 'px';
                    if (colEl) {
                        colEl.style.width = newColWidth + 'px';
                    }

                    // 2. Synchronously update table width so other columns don't compress or drift
                    const containerWidth = container.clientWidth || 0;
                    const newTableWidth = Math.max(startTableWidth + actualDelta, containerWidth);
                    table.style.width = newTableWidth + 'px';
                }

                function onMouseUp(upEvent) {
                    if (!state.isResizing) return;
                    upEvent.preventDefault();
                    upEvent.stopPropagation();

                    resizer.classList.remove('is-resizing');
                    document.body.classList.remove('is-resizing-lead-col');

                    document.removeEventListener('mousemove', onMouseMove);
                    document.removeEventListener('mouseup', onMouseUp);

                    // Final accurate width
                    const finalWidth = Math.max(65, Math.round(th.getBoundingClientRect().width));
                    state.colWidths[colName] = finalWidth;

                    // Persist to localStorage in both keys for 100% permanence
                    try {
                        localStorage.setItem('crm_col_widths_leads', JSON.stringify(state.colWidths));
                    } catch (_) {}
                    persistViewConfig();

                    // Persist to database so refreshing the page preserves custom widths
                    saveColumnWidthsToDatabase(state.colWidths);

                    // Stamp time and keep isResizing true momentarily to prevent accidental sort click
                    state.lastResizeTime = Date.now();
                    setTimeout(() => {
                        state.isResizing = false;
                    }, 350);
                }

                document.addEventListener('mousemove', onMouseMove);
                document.addEventListener('mouseup', onMouseUp);
            });

            // Double click to reset to default column width
            resizer.addEventListener('dblclick', (e) => {
                e.preventDefault();
                e.stopPropagation();
                delete state.colWidths[colName];
                try {
                    localStorage.setItem('crm_col_widths_leads', JSON.stringify(state.colWidths));
                } catch (_) {}
                persistViewConfig();
                saveColumnWidthsToDatabase(state.colWidths);
                renderTableView(getFilteredLeads());
            });
        });
    }

    // Render Compact Data Table (Exact 1:1 Parity with Target Screenshot)
    function renderTableView(filteredLeads) {
        const container = document.getElementById('leads-table-container');
        if (!container) return;

        const allDefs = getAllFieldDefinitions();
        const activeDefs = state.tableVisibleFields
            .map(k => allDefs.find(d => d.name === k))
            .filter(Boolean);

        if (filteredLeads.length === 0) {
            container.innerHTML = `
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-12 text-center border border-cyan-400 shadow-[0_0_18px_rgba(56,189,248,0.22)]">
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">
                        No leads matching current search or filters.
                    </p>
                </div>
            `;
            return;
        }

        const isAllSelected = filteredLeads.length > 0 && filteredLeads.every(l => state.selectedLeadIds.includes(String(l.id)));

        // Calculate total table width to ensure 100% stable layout
        let calculatedTotalWidth = 56; // Checkbox column
        activeDefs.forEach(col => {
            const w = state.colWidths[col.name] || DEFAULT_COL_WIDTHS[col.name] || 150;
            calculatedTotalWidth += w;
        });

        let html = `
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-cyan-400 shadow-[0_0_18px_rgba(56,189,248,0.22)] overflow-hidden transition-all">
                <div class="w-full overflow-x-auto">
                    <table
                        id="leads-main-data-table"
                        data-no-universal="true"
                        class="text-left border-collapse table-fixed data-table"
                        style="width: ${calculatedTotalWidth}px; min-width: 100%; table-layout: fixed;"
                    >
                        <colgroup>
                            <col style="width: 56px; min-width: 56px; max-width: 56px;">
                            ${activeDefs.map(col => {
                                const w = state.colWidths[col.name] || DEFAULT_COL_WIDTHS[col.name] || 150;
                                return `<col data-col-name="${col.name}" style="width: ${w}px;">`;
                            }).join('')}
                        </colgroup>
                        <thead>
                            <tr class="bg-white dark:bg-zinc-900 border-b border-slate-100 dark:border-zinc-800 text-xs font-bold text-slate-800 dark:text-zinc-200">
                                <!-- Master Checkbox Column -->
                                <th class="w-[56px] min-w-[56px] max-w-[56px] pl-5 pr-2 py-3.5 sticky top-0 z-10 bg-white dark:bg-zinc-900 border-r border-slate-100 dark:border-zinc-800">
                                    <div class="flex items-center">
                                        <input
                                            type="checkbox"
                                            id="leads-master-select-chk"
                                            ${isAllSelected ? 'checked' : ''}
                                            class="w-4 h-4 rounded border-slate-300 dark:border-zinc-700 text-indigo-600 focus:ring-0 cursor-pointer"
                                            title="Select all records"
                                        >
                                    </div>
                                </th>
        `;

        // Column Headers (with 3-state sorting indicator and draggable resizer)
        activeDefs.forEach((col, cIdx) => {
            const width = state.colWidths[col.name] || DEFAULT_COL_WIDTHS[col.name] || 150;
            const isSorted = state.sortField === col.name && (state.sortDirection === 'asc' || state.sortDirection === 'desc');
            const isLastCol = cIdx === activeDefs.length - 1;
            const borderR = isLastCol ? '' : 'border-r border-slate-100 dark:border-zinc-800';

            // Sorting Icon: Ascending (↑), Descending (↓), or Normal (∨)
            let sortIconHtml = `
                <svg class="w-3.5 h-3.5 text-slate-400 shrink-0 select-none stroke-[2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                </svg>
            `;
            if (isSorted) {
                if (state.sortDirection === 'asc') {
                    sortIconHtml = `
                        <span class="inline-flex items-center justify-center w-4 h-4 rounded text-xs font-black text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/60 ml-1 shrink-0" title="Sorted: Ascending">
                            ↑
                        </span>
                    `;
                } else if (state.sortDirection === 'desc') {
                    sortIconHtml = `
                        <span class="inline-flex items-center justify-center w-4 h-4 rounded text-xs font-black text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/60 ml-1 shrink-0" title="Sorted: Descending">
                            ↓
                        </span>
                    `;
                }
            }

            html += `
                <th
                    style="width: ${width}px;"
                    class="relative py-3.5 px-4 sticky top-0 z-10 bg-white dark:bg-zinc-900 ${borderR} select-none cursor-pointer header-sort-th no-sort text-xs font-bold text-slate-800 dark:text-zinc-200"
                    data-col-name="${col.name}"
                    title="Click to sort by ${col.label} (1st: Ascending, 2nd: Descending, 3rd: Normal)"
                >
                    <div class="flex items-center justify-between gap-1 pointer-events-none pr-1">
                        <span class="truncate">${col.label}</span>
                        ${sortIconHtml}
                    </div>
                    <!-- Draggable Resizer Edge Handle -->
                    <div
                        class="lead-col-resizer table-col-resizer"
                        data-col-name="${col.name}"
                        title="Drag column edge to resize (Double-click to reset)"
                    ></div>
                </th>
            `;
        });

        html += `
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-zinc-800/80 text-xs">
        `;

        // Table Rows
        filteredLeads.forEach((lead, idx) => {
            const isSelected = state.selectedLeadIds.includes(String(lead.id));
            const rowBg = isSelected
                ? 'bg-blue-50/40 dark:bg-blue-950/20'
                : 'hover:bg-slate-50/70 dark:hover:bg-zinc-800/40';

            html += `
                <tr
                    class="h-11 transition-colors cursor-pointer ${rowBg} lead-table-row"
                    data-lead-id="${lead.id}"
                >
                    <!-- Index & Checkbox Cell (e.g. 1 [ ]) -->
                    <td
                        class="w-[56px] min-w-[56px] max-w-[56px] pl-5 pr-2 py-2 border-r border-slate-100 dark:border-zinc-800"
                        onclick="event.stopPropagation()"
                    >
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-slate-700 dark:text-zinc-400 min-w-[14px] font-medium text-left">${idx + 1}</span>
                            <input
                                type="checkbox"
                                data-row-id="${lead.id}"
                                ${isSelected ? 'checked' : ''}
                                class="lead-row-select-chk w-4 h-4 rounded border-slate-300 dark:border-zinc-700 text-indigo-600 focus:ring-0 cursor-pointer"
                            >
                        </div>
                    </td>
            `;

            // Data Cells
            activeDefs.forEach((col, cIdx) => {
                const isLastCol = cIdx === activeDefs.length - 1;
                const borderR = isLastCol ? '' : 'border-r border-slate-100 dark:border-zinc-800';

                html += `
                    <td
                        class="px-4 py-2 ${borderR} text-xs align-middle truncate"
                    >
                        ${formatTableCell(lead, col.name)}
                    </td>
                `;
            });

            html += `</tr>`;
        });

        html += `
                        </tbody>
                    </table>
                </div>
                <!-- Table Footer (Exact Parity with Target Screenshot) -->
                <div class="flex flex-col sm:flex-row items-center justify-between px-5 py-3 border-t border-slate-100 dark:border-zinc-800 text-xs text-slate-500 dark:text-zinc-400 gap-2 bg-white dark:bg-zinc-900">
                    <div>
                        Showing <span class="font-bold text-slate-800 dark:text-zinc-200">${filteredLeads.length}</span> records
                    </div>
                    <div class="flex items-center gap-2 text-slate-400 dark:text-zinc-500">
                        <span>Tip: Drag column edges to resize</span>
                        <span>•</span>
                        <span>Click header to sort</span>
                    </div>
                </div>
            </div>
        `;

        container.innerHTML = html;

        // 1. Initialize Column Edge Resizers
        initLeadColumnResize(container);

        // 2. Header Sort Event Listeners (with strict protection against triggering on resize)
        container.querySelectorAll('.header-sort-th').forEach(th => {
            th.addEventListener('click', (e) => {
                // NEVER sort if resizing is active or occurred within the last 350ms
                if (state.isResizing) return;
                if (Date.now() - (state.lastResizeTime || 0) < 350) return;
                if (e.target.closest('.lead-col-resizer') || e.target.closest('.table-col-resizer')) return;

                const col = th.getAttribute('data-col-name');
                if (!col) return;

                // 3-State Cycle: Ascending -> Descending -> Normal (backend data order)
                if (state.sortField === col) {
                    if (state.sortDirection === 'asc') {
                        state.sortDirection = 'desc';
                    } else if (state.sortDirection === 'desc') {
                        state.sortField = null;
                        state.sortDirection = null;
                    } else {
                        state.sortDirection = 'asc';
                    }
                } else {
                    state.sortField = col;
                    state.sortDirection = 'asc';
                }
                renderTableView(getFilteredLeads());
            });
        });

        // Row Click: opens fullscreen lead detail modal
        container.querySelectorAll('.lead-table-row').forEach(row => {
            row.addEventListener('click', (e) => {
                if (e.target.closest('input[type="checkbox"]') || e.target.closest('button') || e.target.closest('a')) return;
                const id = row.getAttribute('data-lead-id');
                if (id) {
                    window.openLeadDetailFullscreenModal(id);
                }
            });
        });

        // Master Checkbox
        const masterChk = document.getElementById('leads-master-select-chk');
        if (masterChk) {
            masterChk.addEventListener('change', (e) => {
                if (e.target.checked) {
                    state.selectedLeadIds = filteredLeads.map(l => String(l.id));
                } else {
                    state.selectedLeadIds = [];
                }
                renderTableView(filteredLeads);
                renderSelectedActionBanner();
            });
        }

        // Row Checkboxes
        container.querySelectorAll('.lead-row-select-chk').forEach(chk => {
            chk.addEventListener('change', (e) => {
                const id = chk.getAttribute('data-row-id');
                if (e.target.checked) {
                    if (!state.selectedLeadIds.includes(id)) state.selectedLeadIds.push(id);
                } else {
                    state.selectedLeadIds = state.selectedLeadIds.filter(x => x !== id);
                }
                renderTableView(filteredLeads);
                renderSelectedActionBanner();
            });
        });

        if (window.lucide) lucide.createIcons();
    }

    // Render Universal Kanban Board with Premium Horizontal X-Axis Slider
    function renderKanbanView(filteredLeads) {
        const container = document.getElementById('leads-kanban-container');
        if (!container) return;

        const allDefs = getAllFieldDefinitions();
        const activeDefs = state.kanbanVisibleFields
            .map(k => allDefs.find(d => d.name === k))
            .filter(Boolean);

        const stages = (state.stages && state.stages.length > 0) ? state.stages : LEAD_STAGES;

        let html = `
            <div class="space-y-3 animate-fade-in">
                <!-- Top Kanban Slider Controls & Quick Jump Bar -->
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 p-3 px-4 rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/90 dark:border-zinc-800 shadow-xs">
                    <!-- Left: Pipeline Stage Pills Navigation -->
                    <div class="flex items-center gap-1.5 overflow-x-auto max-w-full pb-1 sm:pb-0 scrollbar-none">
                        <span class="text-xs font-bold text-slate-500 dark:text-zinc-400 mr-1 flex items-center gap-1.5 shrink-0">
                            <i data-lucide="sliders-horizontal" class="w-3.5 h-3.5 text-cyan-500"></i>
                            <span>Pipeline Stages:</span>
                        </span>
                        ${stages.map(stage => {
                            const count = filteredLeads.filter(l => (l.status || 'New') === stage).length;
                            const color = getStageColor(stage);
                            return `
                                <button
                                    type="button"
                                    data-target-stage="${escapeHtml(stage)}"
                                    class="kanban-stage-jump-btn inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 hover:bg-slate-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-slate-700 dark:text-zinc-200 transition-all shrink-0 cursor-pointer border border-transparent hover:border-slate-300 dark:hover:border-zinc-600"
                                    title="Slide directly to ${escapeHtml(stage)}"
                                >
                                    <span style="background-color: ${color};" class="w-2 h-2 rounded-full shrink-0"></span>
                                    <span>${escapeHtml(stage)}</span>
                                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-white dark:bg-zinc-900 text-slate-600 dark:text-zinc-300 shadow-2xs">${count}</span>
                                </button>
                            `;
                        }).join('')}
                    </div>

                    <!-- Right: X-Axis Slider Navigation Controls -->
                    <div class="flex items-center gap-2.5 shrink-0 self-end sm:self-auto">
                        <span class="text-[11px] font-medium text-slate-400 dark:text-zinc-500 hidden md:inline">
                            X-Axis Slider • ${stages.length} Stages
                        </span>
                        <div class="inline-flex items-center p-0.5 rounded-xl bg-slate-100 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700">
                            <button
                                type="button"
                                id="kanban-slide-left-btn"
                                class="p-1.5 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-white dark:text-zinc-400 dark:hover:text-white dark:hover:bg-zinc-700 shadow-2xs transition-all cursor-pointer"
                                title="Slide Left along X-axis"
                            >
                                <i data-lucide="chevron-left" class="w-4 h-4"></i>
                            </button>
                            <div class="w-px h-4 bg-slate-200 dark:bg-zinc-700 mx-0.5"></div>
                            <button
                                type="button"
                                id="kanban-slide-right-btn"
                                class="p-1.5 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-white dark:text-zinc-400 dark:hover:text-white dark:hover:bg-zinc-700 shadow-2xs transition-all cursor-pointer"
                                title="Slide Right along X-axis"
                            >
                                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Horizontal X-Axis Slider Viewport (Strictly Single Row, No Wrapping Downwards) -->
                <div
                    id="kanban-slider-viewport"
                    class="kanban-slider-viewport w-full overflow-x-auto overflow-y-hidden pb-4 pt-1 flex flex-nowrap items-start gap-4 select-none scroll-smooth rounded-2xl"
                >
        `;

        stages.forEach((stage, stageIdx) => {
            const stageLeads = filteredLeads.filter(l => (l.status || 'New') === stage);
            const stageColor = getStageColor(stage);

            html += `
                <div
                    id="kanban-col-${stage.toLowerCase().replace(/[^a-z0-9]/g, '-')}"
                    class="kanban-column w-[320px] min-w-[320px] max-w-[320px] shrink-0 bg-slate-100/70 dark:bg-zinc-800/40 rounded-2xl p-3.5 border border-slate-200/80 dark:border-zinc-800 flex flex-col gap-2.5 min-h-[540px] kanban-col-droppable shadow-2xs"
                    data-stage="${stage}"
                >
                    <!-- Column Header -->
                    <div class="flex items-center justify-between pb-2 border-b border-slate-200 dark:border-zinc-700/60">
                        <div class="flex items-center gap-2">
                            <span style="background-color: ${stageColor};" class="w-2.5 h-2.5 rounded-full shrink-0 shadow-xs"></span>
                            <span class="font-bold text-xs text-slate-900 dark:text-zinc-100 tracking-tight">
                                ${stage}
                            </span>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-white text-slate-700 dark:bg-zinc-700 dark:text-zinc-200 shadow-2xs border border-slate-200/60 dark:border-zinc-600">
                            ${stageLeads.length}
                        </span>
                    </div>

                    <!-- Cards Container (Independent Vertical Scroll per Column) -->
                    <div class="flex flex-col gap-2 flex-1 kanban-cards-wrapper overflow-y-auto max-h-[calc(100vh-280px)] pr-1" data-stage="${stage}">
            `;

            if (stageLeads.length === 0) {
                html += `
                    <div class="py-14 text-center text-slate-400 dark:text-zinc-500 text-xs italic flex flex-col items-center justify-center gap-1.5">
                        <i data-lucide="inbox" class="w-6 h-6 stroke-1 text-slate-300 dark:text-zinc-600"></i>
                        <span>No leads in ${stage}</span>
                    </div>
                `;
            } else {
                stageLeads.forEach(lead => {
                    const fullName = lead.name || `${lead.first_name || ''} ${lead.last_name || ''}`.trim() || lead.company || `#${String(lead.id).substring(0, 8)}`;
                    const hasNextStage = stageIdx < stages.length - 1;
                    const nextStage = hasNextStage ? stages[stageIdx + 1] : null;

                    html += `
                        <div
                            class="bg-white dark:bg-zinc-900 rounded-xl p-3 border border-slate-200 dark:border-zinc-800 shadow-2xs hover:shadow-md hover:border-cyan-400/60 dark:hover:border-cyan-500/60 transition-all ${state.profilePermissions.can_update ? 'cursor-grab active:cursor-grabbing' : 'cursor-pointer'} kanban-card relative group"
                            draggable="${state.profilePermissions.can_update ? 'true' : 'false'}"
                            data-lead-id="${lead.id}"
                            data-lead-stage="${stage}"
                        >
                            <!-- Primary Card Title -->
                            <div class="text-xs font-bold text-slate-900 dark:text-zinc-100 mb-1.5 leading-snug hover:text-cyan-600 transition-colors">
                                ${escapeHtml(fullName)}
                            </div>

                            <!-- Visible Fields Body -->
                            <div class="flex flex-col gap-1 text-[11px] text-slate-600 dark:text-zinc-400">
                    `;

                    activeDefs.forEach(def => {
                        if (def.name === 'name' || def.name === 'status' || def.name === 'id') return;

                        let val = lead[def.name];
                        if (val === undefined && lead.custom_data) {
                            val = lead.custom_data[def.name];
                        }
                        const resolved = lead[`${def.name}_name`] || (lead.custom_data && lead.custom_data[`${def.name}_name`]) || lead[`${def.name}_display`];
                        if (resolved) {
                            val = resolved;
                        }
                        if (val === undefined || val === null || val === '') return;

                        // Formatting by field
                        let formattedVal = escapeHtml(String(val));
                        if (def.name === 'score') {
                            const sc = Number(val);
                            const scCol = sc >= 75 ? 'text-emerald-700 bg-emerald-50 dark:bg-emerald-950/40' : sc >= 50 ? 'text-amber-700 bg-amber-50 dark:bg-amber-950/40' : 'text-slate-600 bg-slate-100 dark:bg-zinc-800';
                            formattedVal = `<span class="px-1.5 py-0.2 rounded font-bold text-[10px] ${scCol}">${sc}</span>`;
                        } else if (def.name === 'company') {
                            formattedVal = `<strong class="text-slate-800 dark:text-zinc-200">${escapeHtml(val)}</strong>`;
                        }

                        html += `
                            <div class="flex justify-between items-center gap-2">
                                <span class="text-slate-400 dark:text-zinc-500 text-[10px]">${escapeHtml(def.label)}:</span>
                                <span class="truncate max-w-[140px] text-right font-medium">${formattedVal}</span>
                            </div>
                        `;
                    });

                    html += `
                            </div>

                            <!-- Card Footer: Quick Move Trigger -->
                            <div class="mt-2.5 pt-2 border-t border-slate-100 dark:border-zinc-800 flex items-center justify-between" onclick="event.stopPropagation()">
                                <div class="inline-flex items-center gap-1">
                                    ${(!lead.is_converted && state.profilePermissions.can_update) ? `
                                        <button
                                            type="button"
                                            onclick="window.openConvertModal('${lead.id}')"
                                            class="p-1 rounded hover:bg-emerald-50 dark:hover:bg-emerald-950/50 text-emerald-600 transition-colors"
                                            title="Convert Lead"
                                        >
                                            <i data-lucide="arrow-right-left" class="w-3 h-3"></i>
                                        </button>
                                    ` : ''}
                                    ${state.profilePermissions.can_transfer ? `
                                        <button
                                            type="button"
                                            onclick="window.openTransferModal('${lead.id}')"
                                            class="p-1 rounded hover:bg-slate-100 dark:hover:bg-zinc-800 text-slate-400 hover:text-slate-700 transition-colors"
                                            title="Transfer Owner"
                                        >
                                            <i data-lucide="user-check" class="w-3 h-3"></i>
                                        </button>
                                    ` : ''}
                                </div>
                    `;

                    if (hasNextStage && state.profilePermissions.can_update) {
                        html += `
                            <button
                                type="button"
                                onclick="window.advanceLeadStage('${lead.id}', '${nextStage}')"
                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold text-slate-700 dark:text-zinc-300 bg-slate-100 hover:bg-slate-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 transition-colors no-underline hover:no-underline"
                                title="Advance to ${nextStage}"
                            >
                                <span>Move Next</span>
                                <i data-lucide="chevron-right" class="w-2.5 h-2.5"></i>
                            </button>
                        `;
                    }

                    html += `
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

        html += `
                </div>

                <!-- Bottom Slider Info Footer -->
                <div class="flex items-center justify-between px-4 py-2.5 rounded-xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 text-xs text-slate-500 dark:text-zinc-400">
                    <div class="flex items-center gap-1.5">
                        <i data-lucide="arrow-left-right" class="w-3.5 h-3.5 text-cyan-500"></i>
                        <span>All <strong>${stages.length}</strong> pipeline stages in single horizontal X-axis slider (no downward wrapping)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span>Tip: Drag board background or use buttons to slide horizontally</span>
                    </div>
                </div>
            </div>
        `;

        container.innerHTML = html;

        // X-Axis Slider Navigation Controls
        const viewport = container.querySelector('#kanban-slider-viewport');
        const btnLeft = container.querySelector('#kanban-slide-left-btn');
        const btnRight = container.querySelector('#kanban-slide-right-btn');

        if (btnLeft && viewport) {
            btnLeft.addEventListener('click', () => {
                viewport.scrollBy({ left: -340, behavior: 'smooth' });
            });
        }
        if (btnRight && viewport) {
            btnRight.addEventListener('click', () => {
                viewport.scrollBy({ left: 340, behavior: 'smooth' });
            });
        }

        // Quick Stage Jump Navigation
        container.querySelectorAll('.kanban-stage-jump-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const targetStage = btn.getAttribute('data-target-stage');
                const targetCol = container.querySelector(`.kanban-column[data-stage="${targetStage}"]`);
                if (targetCol && viewport) {
                    const colOffset = targetCol.offsetLeft - viewport.offsetLeft;
                    viewport.scrollTo({ left: colOffset - 8, behavior: 'smooth' });
                }
            });
        });

        // Mouse Drag / Grab to Slide on X-axis
        if (viewport) {
            let isDown = false;
            let startX = 0;
            let scrollLeft = 0;

            viewport.addEventListener('mousedown', (e) => {
                if (e.target.closest('.kanban-card') || e.target.closest('button') || e.target.closest('input')) return;
                isDown = true;
                viewport.style.cursor = 'grabbing';
                startX = e.pageX - viewport.offsetLeft;
                scrollLeft = viewport.scrollLeft;
            });

            window.addEventListener('mouseup', () => {
                isDown = false;
                if (viewport) viewport.style.cursor = '';
            });

            viewport.addEventListener('mousemove', (e) => {
                if (!isDown) return;
                e.preventDefault();
                const x = e.pageX - viewport.offsetLeft;
                const walk = (x - startX) * 1.5;
                viewport.scrollLeft = scrollLeft - walk;
            });

            // Wheel event horizontal translation
            viewport.addEventListener('wheel', (e) => {
                const cardWrapper = e.target.closest('.kanban-cards-wrapper');
                const isVerticalScroll = Math.abs(e.deltaY) > Math.abs(e.deltaX);
                if (isVerticalScroll) {
                    if (!cardWrapper || e.shiftKey) {
                        e.preventDefault();
                        viewport.scrollLeft += e.deltaY;
                    } else {
                        const atTop = cardWrapper.scrollTop <= 2 && e.deltaY < 0;
                        const atBottom = cardWrapper.scrollTop + cardWrapper.clientHeight >= cardWrapper.scrollHeight - 2 && e.deltaY > 0;
                        if (atTop || atBottom) {
                            e.preventDefault();
                            viewport.scrollLeft += e.deltaY;
                        }
                    }
                }
            }, { passive: false });
        }

        // Card Click: navigates to lead detail
        container.querySelectorAll('.kanban-card').forEach(card => {
            card.addEventListener('click', (e) => {
                if (card.dataset.dragged === 'true' || e.target.closest('button') || e.target.closest('a')) return;
                const id = card.getAttribute('data-lead-id');
                if (id) {
                    window.openLeadDetailFullscreenModal(id);
                }
            });

            // HTML5 Drag & Drop (Only if profile has can_update)
            if (state.profilePermissions.can_update) {
                card.addEventListener('dragstart', (e) => {
                    e.dataTransfer.setData('text/plain', card.getAttribute('data-lead-id'));
                    card.classList.add('opacity-50');
                });
                card.addEventListener('dragend', () => {
                    card.classList.remove('opacity-50');
                });
            } else {
                card.setAttribute('draggable', 'false');
            }
        });

        // Droppable Columns
        container.querySelectorAll('.kanban-col-droppable').forEach(col => {
            col.addEventListener('dragover', (e) => {
                e.preventDefault();
                col.classList.add('ring-2', 'ring-cyan-500/50');
            });
            col.addEventListener('dragleave', () => {
                col.classList.remove('ring-2', 'ring-cyan-500/50');
            });
            col.addEventListener('drop', async (e) => {
                e.preventDefault();
                col.classList.remove('ring-2', 'ring-cyan-500/50');
                const leadId = e.dataTransfer.getData('text/plain');
                const targetStage = col.getAttribute('data-stage');
                if (leadId && targetStage) {
                    await window.advanceLeadStage(leadId, targetStage);
                }
            });
        });

        if (window.lucide) lucide.createIcons();
    }

    // Render Current Views based on viewMode
    function renderCurrentViews() {
        const filtered = getFilteredLeads();
        const tableContainer = document.getElementById('leads-table-container');
        const kanbanContainer = document.getElementById('leads-kanban-container');
        const btnTable = document.getElementById('btn-lead-view-table');
        const btnKanban = document.getElementById('btn-lead-view-kanban');

        if (state.viewMode === 'table') {
            if (tableContainer) tableContainer.classList.remove('hidden');
            if (kanbanContainer) kanbanContainer.classList.add('hidden');
            if (btnTable) {
                btnTable.className = 'flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-md bg-white text-zinc-950 shadow-xs dark:bg-zinc-700 dark:text-zinc-100 cursor-pointer transition-all no-underline hover:no-underline';
            }
            if (btnKanban) {
                btnKanban.className = 'flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-md text-zinc-500 hover:text-zinc-950 dark:text-zinc-400 dark:hover:text-zinc-100 cursor-pointer transition-all no-underline hover:no-underline';
            }
            renderTableView(filtered);
        } else {
            if (tableContainer) tableContainer.classList.add('hidden');
            if (kanbanContainer) kanbanContainer.classList.remove('hidden');
            if (btnKanban) {
                btnKanban.className = 'flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-md bg-white text-zinc-950 shadow-xs dark:bg-zinc-700 dark:text-zinc-100 cursor-pointer transition-all no-underline hover:no-underline';
            }
            if (btnTable) {
                btnTable.className = 'flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-md text-zinc-500 hover:text-zinc-950 dark:text-zinc-400 dark:hover:text-zinc-100 cursor-pointer transition-all no-underline hover:no-underline';
            }
            renderKanbanView(filtered);
        }

        renderActiveFilterChips();
        renderSelectedActionBanner();
    }

    // ==================== GLOBAL WINDOW API METHODS ====================

    // Switch View Mode
    window.switchLeadsViewMode = function (mode) {
        state.viewMode = mode;
        persistViewConfig();
        renderCurrentViews();
    };

    // Deselect All Leads
    window.deselectAllLeads = function () {
        state.selectedLeadIds = [];
        renderCurrentViews();
    };

    // Advance / Change Lead Stage
    window.advanceLeadStage = async function (id, newStage) {
        if (!state.profilePermissions.can_update) {
            alert('Unauthorized: Your security profile does not have permission to update leads.');
            return;
        }
        if (getPermission('status') === 'Read-Only') {
            alert('Lead Status stage is configured as Read-Only for your profile.');
            return;
        }

        try {
            // Optimistic update
            const lead = state.leads.find(l => String(l.id) === String(id));
            if (lead) lead.status = newStage;
            renderCurrentViews();

            const res = await fetch(`/api/leads/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                },
                body: JSON.stringify({ status: newStage })
            });

            if (res.ok) {
                const data = await res.json();
                if (data.lead && lead) {
                    Object.assign(lead, data.lead);
                }
            }
        } catch (err) {
            console.error('[Leads] Error updating stage:', err);
            await fetchLeads(false);
        }
    };

    // ==================== FULLSCREEN LEAD DETAIL 360 MODAL ====================

    window.openLeadDetailFullscreenModal = async function (id) {
        if (!id) return;
        const modal = document.getElementById('leadDetailFullscreenModal');
        const modalBody = document.getElementById('leadDetailFullscreenModalBody');
        if (!modal || !modalBody) {
            window.location.href = `/leads/${id}`;
            return;
        }

        // Show modal immediately with clean loading screen
        modalBody.innerHTML = `
            <div class="flex flex-col items-center justify-center min-h-screen w-full bg-[#f8fafc] dark:bg-zinc-950 p-8 select-none">
                <div class="inline-flex items-center gap-3 px-6 py-3.5 rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 shadow-xl">
                    <svg class="w-5 h-5 animate-spin text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
                    </svg>
                    <span class="text-xs font-semibold text-slate-700 dark:text-zinc-200">Loading Lead Detail 360...</span>
                </div>
            </div>
        `;
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');

        // Update URL to /leads/{id} without full page reload
        try {
            history.pushState({ leadModalId: id }, '', `/leads/${id}`);
        } catch (_) {}

        try {
            const res = await fetch(`/leads/${id}?modal=1`, {
                headers: {
                    'Accept': 'text/html',
                    'X-Modal': '1',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (res.ok) {
                const html = await res.text();
                modalBody.innerHTML = html;

                // Execute any scripts in injected HTML
                modalBody.querySelectorAll('script').forEach(oldScript => {
                    const newScript = document.createElement('script');
                    Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                    newScript.appendChild(document.createTextNode(oldScript.innerHTML));
                    oldScript.parentNode.replaceChild(newScript, oldScript);
                });

                // Re-initialize Lucide Icons
                if (window.lucide) lucide.createIcons();

                // Re-initialize Lead Detail View scripts and form bindings
                if (typeof window.initLeadDetailView === 'function') {
                    window.initLeadDetailView();
                } else if (!document.querySelector('script[src*="lead_detail.js"]')) {
                    const scr = document.createElement('script');
                    scr.src = '/js/lead_detail.js?v=4.3';
                    document.body.appendChild(scr);
                }
            } else {
                modalBody.innerHTML = `
                    <div class="p-8 text-center min-h-screen flex flex-col items-center justify-center bg-[#f8fafc] dark:bg-zinc-950">
                        <div class="max-w-md p-6 bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-xl space-y-3">
                            <p class="text-rose-600 text-xs font-bold">Failed to load lead record details.</p>
                            <button type="button" onclick="window.closeLeadDetailModal()" class="px-4 py-2 text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-800 rounded-xl transition-colors cursor-pointer">Close Fullscreen</button>
                        </div>
                    </div>
                `;
            }
        } catch (err) {
            console.error('[Leads] Error opening lead detail modal:', err);
            modalBody.innerHTML = `
                <div class="p-8 text-center min-h-screen flex flex-col items-center justify-center bg-[#f8fafc] dark:bg-zinc-950">
                    <div class="max-w-md p-6 bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-xl space-y-3">
                        <p class="text-rose-600 text-xs font-bold">A network error occurred while loading lead record.</p>
                        <button type="button" onclick="window.closeLeadDetailModal()" class="px-4 py-2 text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-800 rounded-xl transition-colors cursor-pointer">Close Fullscreen</button>
                    </div>
                </div>
            `;
        }
    };

    window.closeLeadDetailModal = function () {
        const modal = document.getElementById('leadDetailFullscreenModal');
        if (modal) {
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        // Restore URL back to /leads
        if (window.location.pathname.startsWith('/leads/')) {
            try {
                history.pushState(null, '', '/leads');
            } catch (_) {}
        }
    };

    // Close modal on Escape key if child modal is not open
    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            const modal = document.getElementById('leadDetailFullscreenModal');
            if (modal && !modal.classList.contains('hidden')) {
                const childModal = modal.querySelector('#detailEditModal:not(.hidden), #detailConvertModal:not(.hidden), #detailTransferModal:not(.hidden), #quickEmailModal:not(.hidden)');
                if (!childModal) {
                    window.closeLeadDetailModal();
                }
            }
        }
    });

    // Handle browser Back / Forward history navigation
    window.addEventListener('popstate', (e) => {
        const modal = document.getElementById('leadDetailFullscreenModal');
        if (window.location.pathname === '/leads') {
            if (modal && !modal.classList.contains('hidden')) {
                modal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
        } else if (window.location.pathname.startsWith('/leads/')) {
            const parts = window.location.pathname.split('/').filter(Boolean);
            const id = parts[1];
            if (id && modal) {
                window.openLeadDetailFullscreenModal(id);
            }
        }
    });

    // ==================== RECORD DETAIL MODAL ====================

    window.openRecordDetailModal = function (record) {
        if (!record) return;
        const id = record.id;
        if (id) {
            window.openLeadDetailFullscreenModal(id);
            return;
        }
        state.selectedRecord = record;
        const modal = document.getElementById('recordDetailModal');
        if (!modal) return;

        const fullName = record.name || `${record.first_name || ''} ${record.last_name || ''}`.trim() || 'Lead Details';
        const initial = fullName.charAt(0).toUpperCase() || 'L';

        document.getElementById('recordDetailAvatar').textContent = initial;
        document.getElementById('recordDetailTitle').textContent = fullName;
        document.getElementById('recordDetailSubtitle').textContent = record.company || 'No Company';

        // Badges
        const statusEl = document.getElementById('recordDetailStatusBadge');
        if (statusEl) {
            statusEl.textContent = record.status || 'New';
            let col = 'bg-blue-50 text-blue-700 border border-blue-200';
            if (record.status === 'Qualified') col = 'bg-emerald-50 text-emerald-700 border border-emerald-200';
            else if (record.status === 'Contacted') col = 'bg-amber-50 text-amber-700 border border-amber-200';
            else if (record.status === 'Lost') col = 'bg-rose-50 text-rose-700 border border-rose-200';
            statusEl.className = `text-[11px] px-2 py-0.5 rounded-full font-semibold ${col}`;
        }

        const scoreEl = document.getElementById('recordDetailScoreBadge');
        if (scoreEl) {
            const sc = Number(record.score || 0);
            scoreEl.textContent = `Score: ${sc}/100`;
            scoreEl.className = `text-[11px] px-2 py-0.5 rounded-full font-bold ${sc >= 75 ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : sc >= 50 ? 'bg-amber-50 text-amber-800 border border-amber-200' : 'bg-zinc-100 text-zinc-700 border border-zinc-200'}`;
        }

        // Body Content
        const bodyEl = document.getElementById('recordDetailBody');
        if (bodyEl) {
            const ownerName = record.assigned_to_name || record.assigned_user?.name || (record.assigned_to ? `User #${record.assigned_to}` : 'Unassigned');

            // Build Contact & Identification Fields
            let contactFieldsHtml = '';
            const contactFields = [
                { name: 'first_name', label: 'First Name', val: record.first_name || '—' },
                { name: 'last_name', label: 'Last Name', val: record.last_name || '—' },
                { name: 'company', label: 'Company', val: record.company || '—' },
                { name: 'title', label: 'Job Title', val: record.title || '—' },
                {
                    name: 'email',
                    label: 'Email Address',
                    val: record.email ? `<a href="mailto:${escapeHtml(record.email)}" class="text-blue-600 hover:underline">${escapeHtml(record.email)}</a>` : '—',
                    isHtml: true
                },
                {
                    name: 'phone',
                    label: 'Phone',
                    val: record.phone ? `<a href="tel:${escapeHtml(record.phone)}" class="text-zinc-700 dark:text-zinc-300 font-mono">${escapeHtml(record.phone)}</a>` : '—',
                    isHtml: true
                }
            ];

            contactFields.forEach(f => {
                const perm = getPermission(f.name);
                if (perm === 'Hidden') return;
                const lockHtml = perm === 'Read-Only' ? '<span class="text-[10px] text-amber-600 dark:text-amber-400 font-semibold ml-1">🔒</span>' : '';
                contactFieldsHtml += `
                    <div>
                        <span class="text-zinc-400 block text-[11px]">${escapeHtml(f.label)} ${lockHtml}</span>
                        <span class="font-medium text-zinc-800 dark:text-zinc-200">${f.isHtml ? f.val : escapeHtml(f.val)}</span>
                    </div>
                `;
            });

            // Build Qualification & Assignment Fields
            let qualFieldsHtml = '';
            const qualFields = [
                { name: 'status', label: 'Lead Status', val: record.status || 'New' },
                { name: 'source', label: 'Lead Source', val: record.source || 'Website' },
                { name: 'assigned_to', label: 'Assigned Owner', val: ownerName },
                { name: 'rating', label: 'Priority Rating', val: record.rating || 'Warm' }
            ];

            qualFields.forEach(f => {
                const perm = getPermission(f.name);
                if (perm === 'Hidden') return;
                const lockHtml = perm === 'Read-Only' ? '<span class="text-[10px] text-amber-600 dark:text-amber-400 font-semibold ml-1">🔒</span>' : '';
                qualFieldsHtml += `
                    <div>
                        <span class="text-zinc-400 block text-[11px]">${escapeHtml(f.label)} ${lockHtml}</span>
                        <span class="font-medium text-zinc-800 dark:text-zinc-200">${escapeHtml(f.val)}</span>
                    </div>
                `;
            });

            // Notes field
            let notesHtml = '';
            if (getPermission('notes') !== 'Hidden') {
                const notesLock = getPermission('notes') === 'Read-Only' ? '<span class="text-[10px] text-amber-600 dark:text-amber-400 font-semibold ml-1">🔒</span>' : '';
                notesHtml = `
                    <div class="col-span-2">
                        <span class="text-zinc-400 block text-[11px]">Qualification Notes ${notesLock}</span>
                        <p class="text-xs text-zinc-700 dark:text-zinc-300 bg-zinc-50 dark:bg-zinc-800/60 p-2.5 rounded-lg border border-zinc-200 dark:border-zinc-700/60 mt-1 whitespace-pre-wrap">
                            ${escapeHtml(record.notes || 'No qualification notes recorded yet.')}
                        </p>
                    </div>
                `;
            }

            // Dynamic Custom Fields
            let customFieldsHtml = '';
            const visibleCustomFields = (state.customFields || []).filter(cf => getPermission(cf.field_name) !== 'Hidden');
            if (visibleCustomFields.length > 0) {
                visibleCustomFields.forEach(cf => {
                    const fName = cf.field_name;
                    const perm = getPermission(fName);
                    const lockHtml = perm === 'Read-Only' ? '<span class="text-[10px] text-amber-600 dark:text-amber-400 font-semibold ml-1">🔒</span>' : '';
                    let val = record[fName] !== undefined ? record[fName] : (record.custom_data ? record.custom_data[fName] : '—');
                    customFieldsHtml += `
                        <div>
                            <span class="text-zinc-400 block text-[11px]">${escapeHtml(cf.field_label || fName)} ${lockHtml}</span>
                            <span class="font-medium text-zinc-800 dark:text-zinc-200">${escapeHtml(val || '—')}</span>
                        </div>
                    `;
                });
            }

            bodyEl.innerHTML = `
                <!-- Section 1: Contact & Company Overview -->
                <div class="space-y-3">
                    <h4 class="text-xs font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider pb-1 border-b border-zinc-200 dark:border-zinc-800 flex items-center gap-1.5">
                        <i data-lucide="building-2" class="w-3.5 h-3.5 text-zinc-500"></i>
                        <span>Contact &amp; Identification</span>
                    </h4>
                    <div class="grid grid-cols-2 gap-3 text-xs">
                        ${contactFieldsHtml}
                    </div>
                </div>

                <!-- Section 2: Qualification & Assignment -->
                <div class="space-y-3">
                    <h4 class="text-xs font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider pb-1 border-b border-zinc-200 dark:border-zinc-800 flex items-center gap-1.5">
                        <i data-lucide="target" class="w-3.5 h-3.5 text-zinc-500"></i>
                        <span>Qualification &amp; Assignment</span>
                    </h4>
                    <div class="grid grid-cols-2 gap-3 text-xs">
                        ${qualFieldsHtml}
                        ${notesHtml}
                    </div>
                </div>

                ${customFieldsHtml ? `
                <!-- Section 3: Dynamic Custom Fields -->
                <div class="space-y-3">
                    <h4 class="text-xs font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider pb-1 border-b border-zinc-200 dark:border-zinc-800 flex items-center gap-1.5">
                        <i data-lucide="layers" class="w-3.5 h-3.5 text-zinc-500"></i>
                        <span>Custom Attributes</span>
                    </h4>
                    <div class="grid grid-cols-2 gap-3 text-xs">
                        ${customFieldsHtml}
                    </div>
                </div>
                ` : ''}
            `;
        }

        // Actions in footer with strict profile permissions
        const leftActionEl = document.getElementById('recordDetailActionLeft');
        if (leftActionEl) {
            let actionsHtml = '';
            if (!record.is_converted && state.profilePermissions.can_update) {
                actionsHtml += `
                    <button
                        type="button"
                        onclick="window.closeRecordDetailModal(); window.openConvertModal('${record.id}');"
                        class="px-3 py-1.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-xs inline-flex items-center gap-1.5 cursor-pointer no-underline hover:no-underline"
                    >
                        <i data-lucide="arrow-right-left" class="w-3.5 h-3.5"></i>
                        <span>Convert Lead</span>
                    </button>
                `;
            }
            if (state.profilePermissions.can_transfer) {
                actionsHtml += `
                    <button
                        type="button"
                        onclick="window.closeRecordDetailModal(); window.openTransferModal('${record.id}');"
                        class="px-2.5 py-1.5 text-xs font-semibold text-zinc-700 dark:text-zinc-200 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-700 rounded-lg inline-flex items-center gap-1.5 cursor-pointer no-underline hover:no-underline"
                    >
                        <i data-lucide="user-check" class="w-3.5 h-3.5"></i>
                        <span>Transfer</span>
                    </button>
                `;
            }
            if (state.profilePermissions.can_update) {
                actionsHtml += `
                    <button
                        type="button"
                        onclick="window.closeRecordDetailModal(); window.openLeadModal('edit', '${record.id}');"
                        class="px-2.5 py-1.5 text-xs font-semibold text-zinc-700 dark:text-zinc-200 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-700 rounded-lg inline-flex items-center gap-1.5 cursor-pointer no-underline hover:no-underline"
                    >
                        <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                        <span>Edit</span>
                    </button>
                `;
            }
            if (state.profilePermissions.can_delete) {
                actionsHtml += `
                    <button
                        type="button"
                        onclick="window.closeRecordDetailModal(); window.deleteLead('${record.id}');"
                        class="px-2.5 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-lg inline-flex items-center gap-1 cursor-pointer no-underline hover:no-underline"
                    >
                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                        <span>Delete</span>
                    </button>
                `;
            }
            leftActionEl.innerHTML = actionsHtml;
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        if (window.lucide) lucide.createIcons();
    };

    window.closeRecordDetailModal = function () {
        state.selectedRecord = null;
        const modal = document.getElementById('recordDetailModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    };

    // ==================== LEAD ADD / EDIT MODAL ====================

    /**
     * Render the Lead Add/Edit form dynamically using the active PageLayout definition.
     * Controlled by http://retrotech.localhost:8000/admin/page-layouts
     */
    window.renderLeadFormFromLayout = function (mode, editingLead = null) {
        const container = document.getElementById('lead-form-sections-container');
        if (!container) return;

        // Resolve layout definition from state.pageLayout
        let layoutDef = null;
        if (state.pageLayout) {
            layoutDef = state.pageLayout.layout_json || state.pageLayout.layout;
            if (typeof layoutDef === 'string') {
                try { layoutDef = JSON.parse(layoutDef); } catch (e) { layoutDef = null; }
            }
        }

        // Fallback default sections if no layout or empty sections
        let sections = (layoutDef && Array.isArray(layoutDef.sections) && layoutDef.sections.length > 0)
            ? layoutDef.sections
            : [
                {
                    id: 'sec-core',
                    title: 'Core Lead Identification',
                    columns: 2,
                    collapsible: true,
                    fields: [
                        { name: 'first_name', label: 'First Name', type: 'Text', required: true },
                        { name: 'last_name', label: 'Last Name', type: 'Text', required: true },
                        { name: 'company', label: 'Company Name', type: 'Text', required: true },
                        { name: 'title', label: 'Job Title', type: 'Text' },
                        { name: 'email', label: 'Email Address', type: 'Email', required: true },
                        { name: 'phone', label: 'Phone Number', type: 'Phone' }
                    ]
                },
                {
                    id: 'sec-qualification',
                    title: 'Status & Qualification',
                    columns: 2,
                    collapsible: true,
                    fields: [
                        { name: 'status', label: 'Status Stage', type: 'Dropdown', required: true },
                        { name: 'source', label: 'Lead Source', type: 'Dropdown' },
                        { name: 'score', label: 'Lead Score', type: 'Number' },
                        { name: 'rating', label: 'Priority Rating', type: 'Dropdown' },
                        { name: 'assigned_to', label: 'Assigned Owner', type: 'Lookup' },
                        { name: 'notes', label: 'Qualification Notes', type: 'Textarea' }
                    ]
                }
            ];

        // Filter sections: in create mode, hide system information
        const isCreate = (mode === 'create');
        if (isCreate) {
            sections = sections.filter(s => s.id !== 'sec-system-info' && !s.title?.toLowerCase().includes('system information'));
        }

        let html = '';

        sections.forEach((sec, sIdx) => {
            const secTitle = sec.title || `Section ${sIdx + 1}`;
            const isCollapsible = Boolean(sec.collapsible);
            const is1Col = (Number(sec.columns) === 1);
            const gridClass = is1Col ? 'grid grid-cols-1 gap-3.5' : 'grid grid-cols-1 sm:grid-cols-2 gap-3.5';
            const secContentId = `sec-content-${sec.id || sIdx}`;

            // Build Section Fields
            let fieldsHtml = '';
            (sec.fields || []).forEach(f => {
                const fName = typeof f === 'string' ? f : f.name;
                if (!fName) return;

                const perm = getPermission(fName);
                if (perm === 'Hidden') return;

                const isSysField = ['id', 'created_at', 'updated_at', 'created_by'].includes(fName);
                if (isCreate && isSysField) return;

                const override = state.standardOverrides && state.standardOverrides[fName];
                const cfDef = (state.customFields || []).find(c => c.field_name === fName);

                const isReadOnly = (perm === 'Read-Only') || Boolean(f.readOnly) || isSysField;
                const isRequired = !isReadOnly && (Boolean(f.required) || (override && override.is_required !== undefined ? Boolean(override.is_required) : false) || (cfDef && Boolean(cfDef.is_required)));

                // Resolve Field Label
                let fLabel = (override && override.field_label)
                    ? override.field_label
                    : (f.label || (cfDef && (cfDef.field_label || cfDef.label)) || fName.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()));

                // Resolve Field Value
                let val = '';
                if (!isCreate && editingLead) {
                    if (editingLead[fName] !== undefined && editingLead[fName] !== null) {
                        val = editingLead[fName];
                    } else if (editingLead.custom_data && editingLead.custom_data[fName] !== undefined && editingLead.custom_data[fName] !== null) {
                        val = editingLead.custom_data[fName];
                    }
                } else if (isCreate) {
                    if (fName === 'score') val = 50;
                    else if (fName === 'rating') val = 'Warm';
                    else if (fName === 'status') val = (state.stages && state.stages.length > 0) ? state.stages[0] : 'New';
                    else if (fName === 'source') val = (state.sources && state.sources.length > 0) ? state.sources[0] : 'Website';
                    else if (cfDef && cfDef.default_value) val = cfDef.default_value;
                }

                // Help Text
                const helpText = (override && override.help_text) || f.help_text || (cfDef && cfDef.help_text);

                // Styling
                const baseInputClass = "w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-1 focus:ring-zinc-400 focus:outline-none transition-colors";
                const readOnlyInputClass = "w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-zinc-100 dark:bg-zinc-800/80 text-zinc-600 dark:text-zinc-400 cursor-not-allowed opacity-80";
                const inputClass = isReadOnly ? readOnlyInputClass : baseInputClass;

                let widgetHtml = '';
                let isFullSpan = false;

                // 1. System Field Rendering
                if (isSysField) {
                    let displayVal = val || 'Auto-generated';
                    if (fName === 'created_at' || fName === 'updated_at') {
                        if (val) displayVal = new Date(val).toLocaleString();
                    }
                    widgetHtml = `<div class="px-3 py-1.5 text-xs rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 font-mono">${escapeHtml(displayVal)}</div>`;
                }
                // 2. Standard Status
                else if (fName === 'status') {
                    const currentStages = (state.stages && state.stages.length > 0) ? state.stages : ['New', 'Contacted', 'Working', 'Qualified', 'Lost'];
                    const allStages = [...currentStages];
                    if (val && !allStages.includes(val)) allStages.push(val);
                    widgetHtml = `
                        <select id="lead-input-status" name="status" class="${inputClass}" ${isReadOnly ? 'disabled' : ''} ${isRequired ? 'required' : ''}>
                            ${allStages.map(s => `<option value="${escapeHtml(s)}" ${String(val) === String(s) ? 'selected' : ''}>${escapeHtml(s)}</option>`).join('')}
                        </select>
                    `;
                }
                // 3. Standard Source
                else if (fName === 'source') {
                    const currentSources = (state.sources && state.sources.length > 0) ? state.sources : ['Website', 'Referral', 'Cold Outreach', 'Partner', 'Direct Inbound', 'Trade Show / Event', 'LinkedIn', 'Google Ads', 'Other'];
                    const allSources = [...currentSources];
                    if (val && !allSources.includes(val)) allSources.push(val);
                    widgetHtml = `
                        <select id="lead-input-source" name="source" class="${inputClass}" ${isReadOnly ? 'disabled' : ''} ${isRequired ? 'required' : ''}>
                            ${allSources.map(s => `<option value="${escapeHtml(s)}" ${String(val) === String(s) ? 'selected' : ''}>${escapeHtml(s)}</option>`).join('')}
                        </select>
                    `;
                }
                // 4. Standard Rating
                else if (fName === 'rating') {
                    widgetHtml = `
                        <select id="lead-input-rating" name="rating" class="${inputClass}" ${isReadOnly ? 'disabled' : ''} ${isRequired ? 'required' : ''}>
                            <option value="Hot" ${val === 'Hot' ? 'selected' : ''}>Hot (Immediate follow-up)</option>
                            <option value="Warm" ${val === 'Warm' || !val ? 'selected' : ''}>Warm (Nurture in progress)</option>
                            <option value="Cold" ${val === 'Cold' ? 'selected' : ''}>Cold (Low responsiveness)</option>
                        </select>
                    `;
                }
                // 5. Standard Score
                else if (fName === 'score') {
                    widgetHtml = `<input type="number" min="0" max="100" id="lead-input-score" name="score" value="${escapeHtml(val !== '' ? val : 50)}" placeholder="50" class="${inputClass}" ${isReadOnly ? 'readonly disabled' : ''} ${isRequired ? 'required' : ''}>`;
                }
                // 6. Standard Assigned To (User Lookup)
                else if (fName === 'assigned_to') {
                    isFullSpan = true;
                    const usersList = state.users || [];
                    widgetHtml = `
                        <select id="lead-input-assigned_to" name="assigned_to" class="${inputClass}" ${isReadOnly ? 'disabled' : ''} ${isRequired ? 'required' : ''}>
                            <option value="">Unassigned</option>
                            ${usersList.map(u => `<option value="${u.id}" ${String(val) === String(u.id) ? 'selected' : ''}>${escapeHtml(u.name)} (${escapeHtml(u.email || '')})</option>`).join('')}
                        </select>
                    `;
                }
                // 7. Standard Notes (Textarea)
                else if (fName === 'notes') {
                    isFullSpan = true;
                    widgetHtml = `<textarea id="lead-input-notes" name="notes" rows="2" placeholder="Enter context or customer requirements..." class="${inputClass}" ${isReadOnly ? 'readonly disabled' : ''} ${isRequired ? 'required' : ''}>${escapeHtml(val)}</textarea>`;
                }
                // 8. Other Standard Fields (first_name, last_name, company, title, email, phone)
                else if (['first_name', 'last_name', 'company', 'title', 'email', 'phone'].includes(fName)) {
                    const inputType = fName === 'email' ? 'email' : (fName === 'phone' ? 'tel' : 'text');
                    const ph = fName === 'first_name' ? 'e.g. Sarah' : (fName === 'last_name' ? 'e.g. Jenkins' : (fName === 'company' ? 'e.g. Acme Corporation' : (fName === 'title' ? 'e.g. VP of Enterprise Technology' : (fName === 'email' ? 's.jenkins@acmecorp.io' : '+1 (512) 890-4412'))));
                    widgetHtml = `<input type="${inputType}" id="lead-input-${fName}" name="${fName}" value="${escapeHtml(val)}" placeholder="${ph}" class="${inputClass}" ${isReadOnly ? 'readonly disabled' : ''} ${isRequired ? 'required' : ''}>`;
                }
                // 9. Custom Fields / Generic Fields
                else {
                    const dt = ((cfDef && cfDef.data_type) || f.type || 'text').toLowerCase();
                    const isUserLookup = (cfDef && cfDef.lookup_module === 'users') || dt === 'user' || fName.includes('approved') || fName.includes('approver') || fLabel.toLowerCase().includes('approved');

                    if (isUserLookup && state.users && state.users.length > 0) {
                        widgetHtml = `
                            <select name="${escapeHtml(fName)}" data-custom-field="true" class="${inputClass}" ${isReadOnly ? 'disabled' : ''} ${isRequired ? 'required' : ''}>
                                <option value="">Select User / Approver...</option>
                                ${state.users.map(u => `<option value="${u.id}" ${String(val) === String(u.id) ? 'selected' : ''}>${escapeHtml(u.name)} (${escapeHtml(u.email || '')})</option>`).join('')}
                            </select>
                        `;
                    } else if (dt === 'dropdown' || dt === 'select') {
                        let opts = [];
                        if (cfDef && cfDef.options) {
                            opts = Array.isArray(cfDef.options) ? cfDef.options : cfDef.options.split(',').map(s => s.trim()).filter(Boolean);
                        } else if (f.options) {
                            opts = Array.isArray(f.options) ? f.options : f.options.split(',').map(s => s.trim()).filter(Boolean);
                        }
                        widgetHtml = `
                            <select name="${escapeHtml(fName)}" data-custom-field="true" class="${inputClass}" ${isReadOnly ? 'disabled' : ''} ${isRequired ? 'required' : ''}>
                                <option value="">Select ${escapeHtml(fLabel)}...</option>
                                ${opts.map(o => `<option value="${escapeHtml(o)}" ${String(val) === String(o) ? 'selected' : ''}>${escapeHtml(o)}</option>`).join('')}
                            </select>
                        `;
                    } else if (dt === 'rating') {
                        widgetHtml = `
                            <select name="${escapeHtml(fName)}" data-custom-field="true" class="${inputClass}" ${isReadOnly ? 'disabled' : ''} ${isRequired ? 'required' : ''}>
                                <option value="">None</option>
                                ${[1, 2, 3, 4, 5].map(star => `<option value="${star}" ${Number(val) === star ? 'selected' : ''}>${'★'.repeat(star)} (${star} Stars)</option>`).join('')}
                            </select>
                        `;
                    } else if (dt === 'boolean') {
                        const isChecked = (val === true || val === 1 || val === '1' || val === 'true' || val === 'yes');
                        widgetHtml = `
                            <label class="flex items-center gap-2 pt-1 cursor-pointer">
                                <input type="checkbox" name="${escapeHtml(fName)}" data-custom-field="true" data-type="boolean" ${isChecked ? 'checked' : ''} ${isReadOnly ? 'disabled' : ''} class="w-4 h-4 rounded border-zinc-300 dark:border-zinc-700 text-blue-600 focus:ring-1 focus:ring-blue-500">
                                <span class="text-xs text-zinc-700 dark:text-zinc-300">Yes / Active</span>
                            </label>
                        `;
                    } else if (dt === 'number' || dt === 'currency') {
                        widgetHtml = `<input type="number" step="any" name="${escapeHtml(fName)}" data-custom-field="true" value="${escapeHtml(val !== null && val !== undefined ? val : '')}" placeholder="0" class="${inputClass}" ${isReadOnly ? 'readonly disabled' : ''} ${isRequired ? 'required' : ''}>`;
                    } else if (dt === 'date') {
                        widgetHtml = `<input type="date" name="${escapeHtml(fName)}" data-custom-field="true" value="${escapeHtml(val || '')}" class="${inputClass}" ${isReadOnly ? 'readonly disabled' : ''} ${isRequired ? 'required' : ''}>`;
                    } else if (dt === 'datetime' || dt === 'datetime-local') {
                        widgetHtml = `<input type="datetime-local" name="${escapeHtml(fName)}" data-custom-field="true" value="${escapeHtml(val || '')}" class="${inputClass}" ${isReadOnly ? 'readonly disabled' : ''} ${isRequired ? 'required' : ''}>`;
                    } else if (dt === 'time') {
                        widgetHtml = `<input type="time" name="${escapeHtml(fName)}" data-custom-field="true" value="${escapeHtml(val || '')}" class="${inputClass}" ${isReadOnly ? 'readonly disabled' : ''} ${isRequired ? 'required' : ''}>`;
                    } else if (dt === 'maskedtext') {
                        widgetHtml = `
                            <div class="relative">
                                <input type="password" name="${escapeHtml(fName)}" data-custom-field="true" value="${escapeHtml(val || '')}" placeholder="••••••••" class="${inputClass}" ${isReadOnly ? 'readonly disabled' : ''} ${isRequired ? 'required' : ''}>
                                <button type="button" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 text-xs cursor-pointer" onclick="const inp=this.previousElementSibling; inp.type=inp.type==='password'?'text':'password';">👁</button>
                            </div>
                        `;
                    } else if (dt === 'textarea') {
                        isFullSpan = true;
                        widgetHtml = `<textarea rows="2" name="${escapeHtml(fName)}" data-custom-field="true" class="${inputClass}" ${isReadOnly ? 'readonly disabled' : ''} ${isRequired ? 'required' : ''}>${escapeHtml(val || '')}</textarea>`;
                    } else {
                        const inputType = dt === 'email' ? 'email' : (dt === 'phone' ? 'tel' : (dt === 'url' ? 'url' : 'text'));
                        widgetHtml = `<input type="${inputType}" name="${escapeHtml(fName)}" data-custom-field="true" value="${escapeHtml(val || '')}" class="${inputClass}" ${isReadOnly ? 'readonly disabled' : ''} ${isRequired ? 'required' : ''}>`;
                    }
                }

                fieldsHtml += `
                    <div class="space-y-1 ${isFullSpan ? 'col-span-full' : ''}">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                ${escapeHtml(fLabel)}
                                ${isRequired ? '<span class="text-rose-500">*</span>' : ''}
                            </label>
                            ${isReadOnly && !isSysField ? '<span class="field-perm-lock text-[10px] text-amber-600 dark:text-amber-400 font-semibold">🔒 Read-Only</span>' : ''}
                        </div>
                        ${widgetHtml}
                        ${helpText ? `<p class="field-help-text text-[10px] text-zinc-400 mt-0.5">${escapeHtml(helpText)}</p>` : ''}
                    </div>
                `;
            });

            if (fieldsHtml) {
                html += `
                    <div class="space-y-3" id="sec-wrapper-${sec.id || sIdx}">
                        <div class="flex items-center justify-between pb-1 border-b border-zinc-200 dark:border-zinc-800 ${isCollapsible ? 'cursor-pointer select-none' : ''}" 
                             ${isCollapsible ? `onclick="const c = document.getElementById('${secContentId}'); const ch = this.querySelector('.sec-chevron'); if(c) { c.classList.toggle('hidden'); if(ch) ch.classList.toggle('rotate-180'); }"` : ''}>
                            <h4 class="text-xs font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider flex items-center gap-1.5">
                                <i data-lucide="layout" class="w-3.5 h-3.5 text-zinc-500"></i>
                                <span>${escapeHtml(secTitle)}</span>
                            </h4>
                            ${isCollapsible ? `
                                <button type="button" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 transition-transform">
                                    <svg class="sec-chevron w-3.5 h-3.5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </button>
                            ` : ''}
                        </div>
                        <div id="${secContentId}" class="${gridClass}">
                            ${fieldsHtml}
                        </div>
                    </div>
                `;
            }
        });

        container.innerHTML = html || '<div class="py-6 text-center text-zinc-400 text-xs">No fields configured in this layout.</div>';

        if (window.lucide) {
            window.lucide.createIcons();
        }
    };

    window.openLeadModal = function (mode, id = null) {
        if (mode === 'create' && !state.profilePermissions.can_create) {
            alert('Unauthorized: Your security profile does not have permission to create leads.');
            return;
        }
        if (mode === 'edit' && !state.profilePermissions.can_update) {
            alert('Unauthorized: Your security profile does not have permission to edit leads.');
            return;
        }

        state.editingLead = id ? state.leads.find(l => String(l.id) === String(id)) : null;
        const modal = document.getElementById('leadFormModal');
        const titleEl = document.getElementById('leadFormModalTitle');
        const form = document.getElementById('leadRecordForm');
        if (!modal || !form) return;

        form.reset();

        if (mode === 'edit' && state.editingLead) {
            const l = state.editingLead;
            titleEl.textContent = `Edit Lead: ${l.first_name || ''} ${l.last_name || ''}`;
            const idInput = document.getElementById('lead-input-id');
            if (idInput) idInput.value = l.id;
        } else {
            titleEl.textContent = 'Add Lead';
            const idInput = document.getElementById('lead-input-id');
            if (idInput) idInput.value = '';
        }

        // Dynamically render form sections and fields using active PageLayout
        window.renderLeadFormFromLayout(mode, state.editingLead);

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    };

    window.closeLeadModal = function () {
        const modal = document.getElementById('leadFormModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    };

    // Form Submit Handler for Add / Edit
    const leadForm = document.getElementById('leadRecordForm');
    if (leadForm) {
        leadForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const id = document.getElementById('lead-input-id')?.value;
            const payload = {};

            const standardKeys = ['first_name', 'last_name', 'company', 'title', 'email', 'phone', 'status', 'source', 'score', 'rating', 'assigned_to', 'notes'];
            standardKeys.forEach(k => {
                const el = document.getElementById(`lead-input-${k}`) || leadForm.querySelector(`[name="${k}"]:not([data-custom-field="true"])`);
                if (el) {
                    if (k === 'score') payload[k] = parseInt(el.value, 10) || 0;
                    else if (k === 'assigned_to') payload[k] = el.value || null;
                    else payload[k] = el.value ? el.value.trim() : '';
                }
            });

            // Dynamic custom fields
            const customData = {};
            const customInputs = leadForm.querySelectorAll('[data-custom-field="true"]');
            customInputs.forEach(inp => {
                if (!inp.disabled) {
                    if (inp.type === 'checkbox') {
                        customData[inp.name] = inp.checked ? 1 : 0;
                    } else if (inp.type === 'number') {
                        customData[inp.name] = inp.value !== '' ? Number(inp.value) : null;
                    } else {
                        customData[inp.name] = inp.value;
                    }
                }
            });
            payload.custom_data = customData;

            try {
                const url = id ? `/api/leads/${id}` : '/api/leads';
                const method = id ? 'PUT' : 'POST';

                const res = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken()
                    },
                    body: JSON.stringify(payload)
                });

                if (res.ok) {
                    const data = await res.json();
                    window.closeLeadModal();
                    setFeedback(id ? 'Lead updated successfully!' : `Lead '${payload.first_name || ''} ${payload.last_name || ''}' created successfully!`);
                    await fetchLeads(false);
                } else {
                    const errData = await res.json();
                    alert(`Error: ${errData.message || 'Failed to save lead record.'}`);
                }
            } catch (err) {
                console.error('[Leads] Error saving lead:', err);
                alert('A network error occurred while saving the lead.');
            }
        });
    }

    // ==================== CONVERT LEAD MODAL ====================

    window.openConvertModal = function (id) {
        if (!state.profilePermissions.can_update) {
            alert('Unauthorized: Your security profile does not have permission to convert leads.');
            return;
        }
        const lead = state.leads.find(l => String(l.id) === String(id));
        if (!lead) return;

        state.convertingLead = lead;
        const modal = document.getElementById('convertLeadModal');
        if (!modal) return;

        document.getElementById('convert-lead-id').value = lead.id;
        document.getElementById('convertLeadModalTitle').textContent = `Convert Lead: ${lead.first_name || ''} ${lead.last_name || ''}`;
        document.getElementById('convert-preview-account').textContent = `${lead.company || 'Unnamed Account'} (Customer)`;
        document.getElementById('convert-preview-contact').textContent = `${lead.first_name || ''} ${lead.last_name || ''} (${lead.email || 'no email'})`;
        document.getElementById('convert-input-notes').value = lead.notes || '';

        // Reset Opp
        const oppChk = document.getElementById('convert-chk-create-opp');
        const oppFields = document.getElementById('convert-opp-fields');
        if (oppChk) oppChk.checked = false;
        if (oppFields) oppFields.classList.add('hidden');
        document.getElementById('convert-input-opp-name').value = `${lead.company || lead.last_name} — New Business`;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    };

    window.closeConvertModal = function () {
        state.convertingLead = null;
        const modal = document.getElementById('convertLeadModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    };

    // Toggle Opp Fields in Convert Modal
    const oppCheckbox = document.getElementById('convert-chk-create-opp');
    if (oppCheckbox) {
        oppCheckbox.addEventListener('change', (e) => {
            const oppFields = document.getElementById('convert-opp-fields');
            if (oppFields) {
                if (e.target.checked) oppFields.classList.remove('hidden');
                else oppFields.classList.add('hidden');
            }
        });
    }

    // Convert Form Submit
    const convertForm = document.getElementById('convertLeadForm');
    if (convertForm) {
        convertForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (!state.convertingLead) return;

            const submitBtn = document.getElementById('convert-submit-btn');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Converting...';
            }

            const id = state.convertingLead.id;
            const oppChk = document.getElementById('convert-chk-create-opp');
            const payload = {
                notes: document.getElementById('convert-input-notes').value.trim(),
                create_opportunity: oppChk ? oppChk.checked : false,
                opportunity_name: document.getElementById('convert-input-opp-name').value.trim(),
                opportunity_amount: parseFloat(document.getElementById('convert-input-opp-amount').value) || 50000.00
            };

            try {
                const res = await fetch(`/api/leads/${id}/convert`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken()
                    },
                    body: JSON.stringify(payload)
                });

                if (res.ok) {
                    const data = await res.json();
                    window.closeConvertModal();
                    setFeedback(`Lead converted successfully! Generated Account (${data.accountId ? data.accountId.substring(0, 8) : ''}) and Contact (${data.contactId ? data.contactId.substring(0, 8) : ''}).`);
                    await fetchLeads(false);
                } else {
                    const errData = await res.json();
                    alert(`Conversion failed: ${errData.message || 'Unknown server error.'}`);
                }
            } catch (err) {
                console.error('[Leads] Conversion error:', err);
                alert('Conversion failed due to network error.');
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Confirm Conversion';
                }
            }
        });
    }

    // ==================== TRANSFER LEAD MODAL ====================

    window.openTransferModal = function (id) {
        if (!state.profilePermissions.can_transfer) {
            alert('Unauthorized: Your security profile does not have permission to transfer leads.');
            return;
        }
        const lead = state.leads.find(l => String(l.id) === String(id));
        if (!lead) return;

        state.transferringLead = lead;
        const modal = document.getElementById('transferLeadModal');
        if (!modal) return;

        document.getElementById('transfer-lead-id').value = lead.id;
        document.getElementById('transferLeadTitle').textContent = `Transfer: ${lead.first_name || ''} ${lead.last_name || ''}`;
        const ownerName = lead.assigned_to_name || lead.assigned_user?.name || (lead.assigned_to ? `User #${lead.assigned_to}` : 'Unassigned');
        document.getElementById('transferCurrentOwner').textContent = ownerName;

        // Populate users dropdown
        const select = document.getElementById('transferTargetUser');
        if (select) {
            select.innerHTML = '<option value="">-- Choose New Assignee --</option>' +
                state.users.map(u => `<option value="${u.id}">${escapeHtml(u.name)} (${escapeHtml(u.email)})</option>`).join('');
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    };

    window.closeTransferModal = function () {
        state.transferringLead = null;
        const modal = document.getElementById('transferLeadModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    };

    const transferForm = document.getElementById('transferLeadForm');
    if (transferForm) {
        transferForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (!state.transferringLead) return;

            const targetUserId = document.getElementById('transferTargetUser').value;
            if (!targetUserId) {
                alert('Please select a new owner user.');
                return;
            }

            const targetUserObj = state.users.find(u => String(u.id) === String(targetUserId));
            const newOwnerName = targetUserObj ? targetUserObj.name : 'Selected User';

            try {
                const res = await fetch(`/api/leads/${state.transferringLead.id}/transfer`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken()
                    },
                    body: JSON.stringify({
                        targetUserId: targetUserId,
                        note: document.getElementById('transferNote').value.trim()
                    })
                });

                if (res.ok) {
                    window.closeTransferModal();
                    setFeedback(`Lead ownership transferred to ${newOwnerName}`);
                    await fetchLeads(false);
                } else {
                    const errData = await res.json();
                    alert(`Transfer failed: ${errData.message || 'Unknown error.'}`);
                }
            } catch (err) {
                console.error('[Leads] Transfer error:', err);
                alert('Transfer failed due to a network error.');
            }
        });
    }

    // ==================== DELETE LEAD ====================

    window.deleteLead = async function (id) {
        if (!state.profilePermissions.can_delete) {
            alert('Unauthorized: Your security profile does not have permission to delete leads.');
            return;
        }
        const lead = state.leads.find(l => String(l.id) === String(id));
        const name = lead ? `${lead.first_name || ''} ${lead.last_name || ''}`.trim() : 'this lead';
        if (!confirm(`Are you sure you want to delete lead '${name}'?`)) return;

        try {
            const res = await fetch(`/api/leads/${id}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                }
            });

            if (res.ok) {
                setFeedback(`Lead '${name}' was deleted successfully.`);
                state.selectedLeadIds = state.selectedLeadIds.filter(x => x !== String(id));
                await fetchLeads(false);
            } else {
                alert('Failed to delete lead record.');
            }
        } catch (err) {
            console.error('[Leads] Delete error:', err);
            alert('A network error occurred while deleting the lead.');
        }
    };

    // ==================== VIEW CONFIG MODAL (1:1 TARGET SCREENSHOT) ====================

    // Render individual field item matching screenshot
    function renderCustomizeFieldRow(item, seq) {
        const isChecked = Boolean(item.checked);
        const badgeHtml = isChecked
            ? `<span class="order-badge inline-flex items-center justify-center min-w-[28px] h-6 px-1.5 rounded-md text-xs font-bold bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800/80 shrink-0">#${seq}</span>`
            : `<span class="order-badge inline-flex items-center justify-center min-w-[28px] h-6 px-1.5 rounded-md text-xs font-bold bg-slate-100 dark:bg-zinc-800 text-slate-400 dark:text-zinc-500 border border-slate-200 dark:border-zinc-700 shrink-0">—</span>`;

        const badgeType = item.isCustom
            ? `<span class="field-type-badge text-[10px] font-semibold px-1.5 py-0.2 rounded bg-purple-50 text-purple-700 border border-purple-200 dark:bg-purple-950/40 dark:text-purple-300" style="${isChecked ? '' : 'opacity: 0.6;'}">Custom</span>`
            : `<span class="field-type-badge text-[10px] font-semibold px-1.5 py-0.2 rounded bg-indigo-50 text-indigo-700 border border-indigo-200 dark:bg-indigo-950/40 dark:text-indigo-300" style="${isChecked ? '' : 'opacity: 0.6;'}">System</span>`;

        return `
            <div
                class="customize-field-row flex items-center justify-between p-3 rounded-xl border border-slate-200/90 dark:border-zinc-800 bg-white dark:bg-zinc-900 shadow-2xs hover:border-slate-300 dark:hover:border-zinc-700 transition-all select-none group"
                draggable="true"
                data-field-name="${item.name}"
            >
                <!-- Left: Grip Handle + Order Badge + Checkbox + Field Info -->
                <div class="flex items-center gap-3 min-w-0">
                    <div class="drag-handle p-1 text-slate-400 hover:text-slate-600 dark:hover:text-zinc-300 cursor-grab active:cursor-grabbing shrink-0" title="Drag to reorder row">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                            <circle cx="9" cy="5" r="1.5"/>
                            <circle cx="9" cy="12" r="1.5"/>
                            <circle cx="9" cy="19" r="1.5"/>
                            <circle cx="15" cy="5" r="1.5"/>
                            <circle cx="15" cy="12" r="1.5"/>
                            <circle cx="15" cy="19" r="1.5"/>
                        </svg>
                    </div>

                    ${badgeHtml}

                    <input
                        type="checkbox"
                        data-field="${item.name}"
                        ${isChecked ? 'checked' : ''}
                        class="customize-field-chk w-4 h-4 rounded border-slate-300 dark:border-zinc-700 text-blue-600 accent-blue-600 focus:ring-0 cursor-pointer shrink-0"
                    >

                    <div class="flex flex-col min-w-0">
                        <div class="flex items-center gap-1.5">
                            <span class="text-xs font-bold ${isChecked ? 'text-slate-900 dark:text-zinc-100' : 'text-slate-400 dark:text-zinc-500'} field-label truncate">${escapeHtml(item.label)}</span>
                            ${badgeType}
                        </div>
                        <span class="text-[11px] font-mono ${isChecked ? 'text-slate-400 dark:text-zinc-500' : 'text-slate-300 dark:text-zinc-600'} field-name-code truncate">${escapeHtml(item.name)}</span>
                    </div>
                </div>

                <!-- Right: Quick Positioning Buttons (Top, Up, Down, Bottom) -->
                <div class="flex items-center gap-0.5 shrink-0 opacity-70 group-hover:opacity-100 transition-opacity">
                    <button
                        type="button"
                        class="btn-move-top p-1 rounded hover:bg-slate-100 dark:hover:bg-zinc-800 text-slate-400 hover:text-slate-700 dark:hover:text-zinc-200 transition-colors cursor-pointer"
                        title="Move to Top"
                    >
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 4h14M12 20V9m-4 4 4-4 4 4"/>
                        </svg>
                    </button>
                    <button
                        type="button"
                        class="btn-move-up p-1 rounded hover:bg-slate-100 dark:hover:bg-zinc-800 text-slate-400 hover:text-slate-700 dark:hover:text-zinc-200 transition-colors cursor-pointer"
                        title="Move Up"
                    >
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 19V5m-5 5 5-5 5 5"/>
                        </svg>
                    </button>
                    <button
                        type="button"
                        class="btn-move-down p-1 rounded hover:bg-slate-100 dark:hover:bg-zinc-800 text-slate-400 hover:text-slate-700 dark:hover:text-zinc-200 transition-colors cursor-pointer"
                        title="Move Down"
                    >
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m-5-5 5 5 5-5"/>
                        </svg>
                    </button>
                    <button
                        type="button"
                        class="btn-move-bottom p-1 rounded hover:bg-slate-100 dark:hover:bg-zinc-800 text-slate-400 hover:text-slate-700 dark:hover:text-zinc-200 transition-colors cursor-pointer"
                        title="Move to Bottom"
                    >
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 20h14M12 4v11m-4-4 4 4 4-4"/>
                        </svg>
                    </button>
                </div>
            </div>
        `;
    }

    function updateCustomizeModalStats() {
        const list = document.getElementById('view-config-fields-list');
        const checkedCountEl = document.getElementById('customize-checked-count');
        const totalCountEl = document.getElementById('customize-total-count');
        if (!list) return;

        const rows = Array.from(list.querySelectorAll('.customize-field-row'));
        let seq = 1;
        let checkedCount = 0;

        rows.forEach(row => {
            const chk = row.querySelector('.customize-field-chk');
            const badge = row.querySelector('.order-badge');
            const labelEl = row.querySelector('.field-label');
            const codeEl = row.querySelector('.field-name-code');
            const typeBadge = row.querySelector('.field-type-badge');
            if (!chk || !badge) return;

            if (chk.checked) {
                checkedCount++;
                badge.textContent = `#${seq}`;
                badge.className = 'order-badge inline-flex items-center justify-center min-w-[28px] h-6 px-1.5 rounded-md text-xs font-bold bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800/80 shrink-0';
                seq++;
                if (labelEl) labelEl.className = 'text-xs font-bold text-slate-900 dark:text-zinc-100 field-label truncate';
                if (codeEl) codeEl.className = 'text-[11px] font-mono text-slate-400 dark:text-zinc-500 field-name-code truncate';
                if (typeBadge) typeBadge.style.opacity = '1';
            } else {
                badge.textContent = '—';
                badge.className = 'order-badge inline-flex items-center justify-center min-w-[28px] h-6 px-1.5 rounded-md text-xs font-bold bg-slate-100 dark:bg-zinc-800 text-slate-400 dark:text-zinc-500 border border-slate-200 dark:border-zinc-700 shrink-0';
                if (labelEl) labelEl.className = 'text-xs font-medium text-slate-400 dark:text-zinc-500 field-label truncate';
                if (codeEl) codeEl.className = 'text-[11px] font-mono text-slate-300 dark:text-zinc-600 field-name-code truncate';
                if (typeBadge) typeBadge.style.opacity = '0.5';
            }
        });

        if (checkedCountEl) checkedCountEl.textContent = checkedCount;
        if (totalCountEl) totalCountEl.textContent = rows.length;
    }

    let customizeDraggedRow = null;

    function bindCustomizeRowEvents(list) {
        if (!list) return;

        list.querySelectorAll('.customize-field-row').forEach(row => {
            // Drag and drop listeners
            row.addEventListener('dragstart', (e) => {
                customizeDraggedRow = row;
                row.classList.add('is-dragging');
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', row.getAttribute('data-field-name') || '');
            });

            row.addEventListener('dragover', (e) => {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
                if (!customizeDraggedRow || customizeDraggedRow === row) return;

                const rect = row.getBoundingClientRect();
                const midY = rect.top + rect.height / 2;
                if (e.clientY < midY) {
                    row.classList.add('drag-over-top');
                    row.classList.remove('drag-over-bottom');
                } else {
                    row.classList.add('drag-over-bottom');
                    row.classList.remove('drag-over-top');
                }
            });

            row.addEventListener('dragleave', () => {
                row.classList.remove('drag-over-top', 'drag-over-bottom');
            });

            row.addEventListener('drop', (e) => {
                e.preventDefault();
                row.classList.remove('drag-over-top', 'drag-over-bottom');
                if (!customizeDraggedRow || customizeDraggedRow === row) return;

                const rect = row.getBoundingClientRect();
                const midY = rect.top + rect.height / 2;
                if (e.clientY < midY) {
                    list.insertBefore(customizeDraggedRow, row);
                } else {
                    list.insertBefore(customizeDraggedRow, row.nextSibling);
                }
                updateCustomizeModalStats();
            });

            row.addEventListener('dragend', () => {
                row.classList.remove('is-dragging');
                list.querySelectorAll('.customize-field-row').forEach(r => r.classList.remove('drag-over-top', 'drag-over-bottom'));
                customizeDraggedRow = null;
                updateCustomizeModalStats();
            });

            // Checkbox change
            const chk = row.querySelector('.customize-field-chk');
            if (chk) {
                chk.addEventListener('change', () => {
                    updateCustomizeModalStats();
                });
            }

            // Quick arrow positioning buttons
            const btnTop = row.querySelector('.btn-move-top');
            if (btnTop) {
                btnTop.addEventListener('click', () => {
                    list.prepend(row);
                    updateCustomizeModalStats();
                });
            }
            const btnUp = row.querySelector('.btn-move-up');
            if (btnUp) {
                btnUp.addEventListener('click', () => {
                    if (row.previousElementSibling) {
                        list.insertBefore(row, row.previousElementSibling);
                        updateCustomizeModalStats();
                    }
                });
            }
            const btnDown = row.querySelector('.btn-move-down');
            if (btnDown) {
                btnDown.addEventListener('click', () => {
                    if (row.nextElementSibling) {
                        list.insertBefore(row.nextElementSibling, row);
                        updateCustomizeModalStats();
                    }
                });
            }
            const btnBottom = row.querySelector('.btn-move-bottom');
            if (btnBottom) {
                btnBottom.addEventListener('click', () => {
                    list.appendChild(row);
                    updateCustomizeModalStats();
                });
            }
        });
    }

    window.openCustomizeModal = function () {
        const modal = document.getElementById('viewConfigModal');
        const list = document.getElementById('view-config-fields-list');
        if (!modal || !list) return;

        const isTable = state.viewMode === 'table';
        const activeKeys = isTable ? state.tableVisibleFields : state.kanbanVisibleFields;
        const allDefs = getAllFieldDefinitions();

        // Put active keys first in order, then inactive
        const ordered = [];
        activeKeys.forEach(k => {
            const d = allDefs.find(x => x.name === k);
            if (d) ordered.push({ ...d, checked: true });
        });
        allDefs.forEach(d => {
            if (!activeKeys.includes(d.name)) {
                ordered.push({ ...d, checked: false });
            }
        });

        let seq = 1;
        list.innerHTML = ordered.map(item => {
            const rowHtml = renderCustomizeFieldRow(item, item.checked ? seq : 0);
            if (item.checked) seq++;
            return rowHtml;
        }).join('');

        bindCustomizeRowEvents(list);
        updateCustomizeModalStats();

        // Search toolbar
        const searchInput = document.getElementById('customize-fields-search-input');
        if (searchInput) {
            searchInput.value = '';
            searchInput.oninput = (e) => {
                const q = e.target.value.toLowerCase().trim();
                list.querySelectorAll('.customize-field-row').forEach(row => {
                    const label = (row.querySelector('.field-label')?.textContent || '').toLowerCase();
                    const name = (row.getAttribute('data-field-name') || '').toLowerCase();
                    if (!q || label.includes(q) || name.includes(q)) {
                        row.classList.remove('hidden');
                    } else {
                        row.classList.add('hidden');
                    }
                });
            };
        }

        const selectAllBtn = document.getElementById('customize-select-all-btn');
        if (selectAllBtn) {
            selectAllBtn.onclick = () => {
                list.querySelectorAll('.customize-field-chk').forEach(c => c.checked = true);
                updateCustomizeModalStats();
            };
        }

        const deselectAllBtn = document.getElementById('customize-deselect-all-btn');
        if (deselectAllBtn) {
            deselectAllBtn.onclick = () => {
                list.querySelectorAll('.customize-field-chk').forEach(c => c.checked = false);
                updateCustomizeModalStats();
            };
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    };

    window.closeCustomizeModal = function () {
        const modal = document.getElementById('viewConfigModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    };

    window.resetViewConfigDefaults = function () {
        if (state.viewMode === 'table') {
            state.tableVisibleFields = ['name', 'company', 'score', 'status', 'email', 'assigned_to'];
        } else {
            state.kanbanVisibleFields = ['company', 'score', 'email', 'assigned_to'];
        }
        persistViewConfig();
        saveViewFieldsToDatabase(state.viewMode === 'table' ? state.tableVisibleFields : state.kanbanVisibleFields, state.viewMode);
        window.openCustomizeModal();
        renderCurrentViews();
    };

    window.saveViewConfig = function () {
        const list = document.getElementById('view-config-fields-list');
        if (!list) return;

        const checkedFields = [];
        list.querySelectorAll('.customize-field-chk:checked').forEach(input => {
            const f = input.getAttribute('data-field');
            if (f) checkedFields.push(f);
        });

        if (checkedFields.length === 0) {
            alert('Please select at least one field to display in table view.');
            return;
        }

        const isTable = state.viewMode === 'table';
        if (isTable) {
            state.tableVisibleFields = checkedFields;
        } else {
            state.kanbanVisibleFields = checkedFields;
        }

        // 1. Persist to localStorage
        persistViewConfig();

        // 2. Persist to backend database for 100% permanence across refreshes and devices
        saveViewFieldsToDatabase(checkedFields, state.viewMode);

        // 3. Close modal & re-render view
        window.closeCustomizeModal();
        renderCurrentViews();
        setFeedback(`Table columns successfully updated: ${checkedFields.length} columns active.`);
    };

    // ==================== UNIVERSAL FILTER MODAL ====================

    window.openUniversalFilterModal = function () {
        const modal = document.getElementById('universalFilterModal');
        const container = document.getElementById('filter-rules-container');
        if (!modal || !container) return;

        container.innerHTML = '';
        if (state.activeFilters.length === 0) {
            window.addFilterRuleRow();
        } else {
            state.activeFilters.forEach(rule => {
                window.addFilterRuleRow(rule);
            });
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    };

    window.closeUniversalFilterModal = function () {
        const modal = document.getElementById('universalFilterModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    };

    window.addFilterRuleRow = function (existingRule = null) {
        const container = document.getElementById('filter-rules-container');
        if (!container) return;

        const ruleId = existingRule ? existingRule.id : `rule_${Date.now()}_${Math.random().toString(36).substring(2, 6)}`;
        const defs = getAllFieldDefinitions();

        const row = document.createElement('div');
        row.className = 'grid grid-cols-12 gap-2 items-center p-2 rounded-lg bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200 dark:border-zinc-700/60 filter-rule-row';
        row.setAttribute('data-rule-id', ruleId);

        row.innerHTML = `
            <!-- Field Selector -->
            <div class="col-span-4">
                <select class="w-full px-2 py-1 text-xs rounded-md border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 rule-field-select">
                    ${defs.map(d => `<option value="${d.name}" ${existingRule && existingRule.field === d.name ? 'selected' : ''}>${escapeHtml(d.label)}</option>`).join('')}
                </select>
            </div>

            <!-- Operator Selector -->
            <div class="col-span-3">
                <select class="w-full px-2 py-1 text-xs rounded-md border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 rule-operator-select">
                    <option value="contains" ${existingRule && existingRule.operator === 'contains' ? 'selected' : ''}>contains</option>
                    <option value="equals" ${existingRule && existingRule.operator === 'equals' ? 'selected' : ''}>equals</option>
                    <option value="not_equals" ${existingRule && existingRule.operator === 'not_equals' ? 'selected' : ''}>not equals</option>
                    <option value="starts_with" ${existingRule && existingRule.operator === 'starts_with' ? 'selected' : ''}>starts with</option>
                    <option value="greater_than" ${existingRule && existingRule.operator === 'greater_than' ? 'selected' : ''}>&gt; greater than</option>
                    <option value="less_than" ${existingRule && existingRule.operator === 'less_than' ? 'selected' : ''}>&lt; less than</option>
                    <option value="greater_equal" ${existingRule && existingRule.operator === 'greater_equal' ? 'selected' : ''}>&gt;= greater or equal</option>
                    <option value="less_equal" ${existingRule && existingRule.operator === 'less_equal' ? 'selected' : ''}>&lt;= less or equal</option>
                    <option value="is_empty" ${existingRule && existingRule.operator === 'is_empty' ? 'selected' : ''}>is empty</option>
                    <option value="is_not_empty" ${existingRule && existingRule.operator === 'is_not_empty' ? 'selected' : ''}>is not empty</option>
                </select>
            </div>

            <!-- Value Input -->
            <div class="col-span-4">
                <input
                    type="text"
                    value="${existingRule ? escapeHtml(existingRule.value) : ''}"
                    placeholder="Value..."
                    class="w-full px-2 py-1 text-xs rounded-md border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 rule-value-input"
                >
            </div>

            <!-- Delete Row Button -->
            <div class="col-span-1 text-right">
                <button type="button" class="p-1 text-zinc-400 hover:text-rose-600 cursor-pointer remove-rule-row-btn" title="Remove rule">
                    ✕
                </button>
            </div>
        `;

        row.querySelector('.remove-rule-row-btn').addEventListener('click', () => {
            row.remove();
        });

        container.appendChild(row);
    };

    window.clearAllFilters = function () {
        state.activeFilters = [];
        window.closeUniversalFilterModal();
        renderActiveFilterChips();
        renderCurrentViews();
    };

    window.applyFilterRules = function () {
        const container = document.getElementById('filter-rules-container');
        if (!container) return;

        const rules = [];
        container.querySelectorAll('.filter-rule-row').forEach(row => {
            const field = row.querySelector('.rule-field-select').value;
            const operator = row.querySelector('.rule-operator-select').value;
            const value = row.querySelector('.rule-value-input').value.trim();
            const id = row.getAttribute('data-rule-id');

            if (operator === 'is_empty' || operator === 'is_not_empty' || value !== '') {
                rules.push({ id, field, operator, value });
            }
        });

        state.activeFilters = rules;
        window.closeUniversalFilterModal();
        renderActiveFilterChips();
        renderCurrentViews();
    };

    // ==================== BULK EMAIL WIZARD MODAL ====================

    window.openBulkWizardModal = function (onlySelected = false) {
        const modal = document.getElementById('bulkEmailWizardModal');
        const countEl = document.getElementById('bulk-wizard-recipient-count');
        if (!modal) return;

        const count = onlySelected && state.selectedLeadIds.length > 0
            ? state.selectedLeadIds.length
            : state.leads.length;

        if (countEl) countEl.textContent = `${count} Target Recipient${count > 1 ? 's' : ''} Selected`;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    };

    window.closeBulkWizardModal = function () {
        const modal = document.getElementById('bulkEmailWizardModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    };

    const bulkEmailForm = document.getElementById('bulkEmailForm');
    if (bulkEmailForm) {
        bulkEmailForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const subject = document.getElementById('bulk-email-subject').value.trim();
            const count = state.selectedLeadIds.length > 0 ? state.selectedLeadIds.length : state.leads.length;
            window.closeBulkWizardModal();
            setFeedback(`Bulk email broadcast initiated for ${count} lead recipients with subject "${subject}".`);
            state.selectedLeadIds = [];
            renderCurrentViews();
        });
    }

    // ==================== SEARCH INPUT LISTENER ====================

    function setupSearchInput() {
        const searchInput = document.getElementById('leads-search-input');
        if (searchInput) {
            let debounceTimer = null;
            searchInput.addEventListener('input', (e) => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    state.searchTerm = e.target.value.trim();
                    renderCurrentViews();
                }, 150);
            });
        }
    }

    // ==================== HYDRATION & INITIALIZATION ====================

    window.initLeadsModule = function () {
        const root = document.getElementById('leads-page-root');
        if (!root) return;

        // Hydrate initial data from server script tag
        if (window.__LEADS_DATA__) {
            const hd = window.__LEADS_DATA__;
            if (Array.isArray(hd.initialLeads)) {
                state.leads = hd.initialLeads;
            }
            if (Array.isArray(hd.customFields)) {
                state.customFields = hd.customFields;
            }
            if (hd.fieldPermissions) {
                state.fieldPermissions = hd.fieldPermissions;
            }
            if (hd.profilePermissions) {
                state.profilePermissions = Object.assign({}, state.profilePermissions, hd.profilePermissions);
            }
            if (Array.isArray(hd.users)) {
                state.users = hd.users;
            }
            if (Array.isArray(hd.sources)) {
                state.sources = hd.sources;
            }
            if (hd.standardOverrides && typeof hd.standardOverrides === 'object') {
                state.standardOverrides = hd.standardOverrides;
            }
            if (hd.pageLayout) {
                state.pageLayout = hd.pageLayout;
            }
            if (Array.isArray(hd.stages) && hd.stages.length > 0) {
                state.stages = hd.stages;
            }
            if (hd.canSendBulkEmail !== undefined) {
                state.canSendBulkEmail = Boolean(hd.canSendBulkEmail);
            }
            if (hd.colWidths && typeof hd.colWidths === 'object' && Object.keys(hd.colWidths).length > 0) {
                state.colWidths = { ...state.colWidths, ...hd.colWidths };
            }
            if (Array.isArray(hd.tableVisibleFields) && hd.tableVisibleFields.length > 0) {
                state.tableVisibleFields = hd.tableVisibleFields;
            }
            if (Array.isArray(hd.kanbanVisibleFields) && hd.kanbanVisibleFields.length > 0) {
                state.kanbanVisibleFields = hd.kanbanVisibleFields;
            }
        }

        loadSavedViewConfig();

        // Database-stored preferences take ultimate priority across browser sessions/refreshes
        if (window.__LEADS_DATA__) {
            const hd = window.__LEADS_DATA__;
            if (hd.colWidths && typeof hd.colWidths === 'object' && Object.keys(hd.colWidths).length > 0) {
                state.colWidths = { ...state.colWidths, ...hd.colWidths };
            }
            if (Array.isArray(hd.tableVisibleFields) && hd.tableVisibleFields.length > 0) {
                state.tableVisibleFields = hd.tableVisibleFields;
            }
            if (Array.isArray(hd.kanbanVisibleFields) && hd.kanbanVisibleFields.length > 0) {
                state.kanbanVisibleFields = hd.kanbanVisibleFields;
            }
        }

        setupSearchInput();
        renderCurrentViews();
        if (window.lucide) lucide.createIcons();

        // Auto-open fullscreen modal if URL indicates a lead record
        try {
            const urlParams = new URLSearchParams(window.location.search);
            const queryLeadId = urlParams.get('lead_id');
            if (queryLeadId) {
                window.openLeadDetailFullscreenModal(queryLeadId);
            } else if (window.location.pathname.startsWith('/leads/')) {
                const parts = window.location.pathname.split('/').filter(Boolean);
                const id = parts[1];
                if (id && id !== 'create') {
                    window.openLeadDetailFullscreenModal(id);
                }
            }
        } catch (_) {}
    };

    // Auto-boot on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', window.initLeadsModule);
    } else {
        window.initLeadsModule();
    }
})();
