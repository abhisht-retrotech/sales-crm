/**
 * Object & Field Manager Studio
 * 1-to-1 Parity with React SettingsView (Tab 5) & LeadConversionFieldMappingStudio
 * Pure Tailwind CSS Architecture, Zero Inline CSS, SPA-safe isolated IIFE
 */
(function () {
    'use strict';

    let state = {
        activeSubTab: 'fields', // 'fields' | 'global_picklists' | 'lead_mapping'
        selectedModule: 'leads',
        moduleOptions: [],
        dataTypes: [],
        standardFields: {},
        standardOverrides: {},
        customFields: [],
        globalPicklists: [],
        leadMappingTab: 'accounts', // 'accounts' | 'contacts' | 'opportunities'
        leadMeta: {
            leadFields: [],
            accountFields: [],
            contactFields: [],
            opportunityFields: [],
            mappings: {}
        },
        editingField: null,
        editingStandardField: null,
        editingGlobalPicklist: null,
        isFieldManualName: false,
        isGpManualApiName: false
    };

    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    function showToast(message, type = 'success') {
        const toast = document.getElementById('ofToast');
        const msgEl = document.getElementById('ofToastMsg');
        const iconEl = document.getElementById('ofToastIcon');
        if (!toast || !msgEl) return;

        msgEl.textContent = message;
        if (type === 'error') {
            toast.className = 'fixed bottom-6 right-6 z-[99999] flex items-center gap-2.5 px-4 py-3 rounded-xl bg-red-600 text-white shadow-xl shadow-red-600/20 text-xs font-semibold animate-fade-in';
            if (iconEl) iconEl.setAttribute('data-lucide', 'alert-circle');
        } else if (type === 'info') {
            toast.className = 'fixed bottom-6 right-6 z-[99999] flex items-center gap-2.5 px-4 py-3 rounded-xl bg-zinc-900 dark:bg-zinc-100 text-white dark:text-zinc-900 shadow-xl text-xs font-semibold animate-fade-in';
            if (iconEl) iconEl.setAttribute('data-lucide', 'info');
        } else {
            toast.className = 'fixed bottom-6 right-6 z-[99999] flex items-center gap-2.5 px-4 py-3 rounded-xl bg-emerald-600 text-white shadow-xl shadow-emerald-600/20 text-xs font-semibold animate-fade-in';
            if (iconEl) iconEl.setAttribute('data-lucide', 'check-circle-2');
        }

        toast.classList.remove('hidden');
        if (window.lucide) window.lucide.createIcons();

        if (window._ofToastTimeout) clearTimeout(window._ofToastTimeout);
        window._ofToastTimeout = setTimeout(() => {
            toast.classList.add('hidden');
        }, 3500);
    }

    function formatFieldKeyFromLabel(label) {
        const slug = (label || '')
            .trim()
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '_')
            .replace(/^_+|_+$/g, '')
            .slice(0, 30);
        return slug ? `cf_${slug}` : '';
    }

    function formatGpApiKeyFromName(name) {
        const slug = (name || '')
            .trim()
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '_')
            .replace(/^_+|_+$/g, '')
            .slice(0, 30);
        return slug ? `gp_${slug}` : '';
    }

    function getModuleLabel(moduleId) {
        const opt = state.moduleOptions.find(m => m.id === moduleId);
        return opt ? opt.name : (moduleId.charAt(0).toUpperCase() + moduleId.slice(1));
    }

    // -------------------------------------------------------------
    // INITIALIZATION & SSR DATA EXTRACTION
    // -------------------------------------------------------------
    function initObjectFields() {
        const serverDataEl = document.getElementById('ofServerData');
        if (serverDataEl) {
            try {
                const data = JSON.parse(serverDataEl.textContent);
                state.moduleOptions = data.moduleOptions || [];
                state.dataTypes = data.dataTypes || [];
                state.standardFields = data.standardFields || {};
                state.globalPicklists = data.globalPicklists || [];
                state.customFields = data.customFields || [];

                // Convert standardOverrides array to hash { 'leads.field_name': override }
                const overridesMap = {};
                (data.standardOverrides || []).forEach(o => {
                    overridesMap[`${o.module}.${o.field_name}`] = o;
                });
                state.standardOverrides = overridesMap;
            } catch (err) {
                console.error('[ObjectFields] Error parsing server data:', err);
            }
        }

        // Populate Module Selector
        renderModuleSelector();

        // Bind Subtabs
        bindSubTabNavigation();

        // Render Initial Views
        updateKpis();
        renderCustomFieldsTable();
        renderStandardFieldsTable();
        renderGlobalPicklistsTable();

        // Preload Lead Mappings if mapping tab selected
        loadLeadMappingsData();

        // Bind Dynamic Forms & Inputs
        bindFormEvents();

        if (window.lucide) window.lucide.createIcons();
    }

    function updateKpis() {
        const kpiCf = document.getElementById('kpiActiveCustomFields');
        const kpiFr = document.getElementById('kpiFormulasRollups');
        const kpiGp = document.getElementById('kpiGlobalPicklists');
        const gpSubCount = document.getElementById('subTabGpCount');

        if (kpiCf) kpiCf.textContent = `${state.customFields.length} Fields`;

        const frCount = state.customFields.filter(f => f.data_type === 'Formula' || f.data_type === 'RollupSummary').length;
        if (kpiFr) kpiFr.textContent = `${frCount} Active`;

        if (kpiGp) kpiGp.textContent = `${state.globalPicklists.length} Sets`;
        if (gpSubCount) gpSubCount.textContent = state.globalPicklists.length;
    }

    function renderModuleSelector() {
        const select = document.getElementById('selectedModuleForFields');
        if (!select) return;

        select.innerHTML = '';
        state.moduleOptions.forEach(opt => {
            const option = document.createElement('option');
            option.value = opt.id;
            option.textContent = opt.name;
            if (opt.id === state.selectedModule) option.selected = true;
            select.appendChild(option);
        });

        select.onchange = function () {
            state.selectedModule = this.value;
            renderCustomFieldsTable();
            renderStandardFieldsTable();
        };
    }

    // -------------------------------------------------------------
    // SUBTAB SWITCHING
    // -------------------------------------------------------------
    function setSubTab(tab) {
        state.activeSubTab = tab;

        const btnFields = document.getElementById('subTabBtnFields');
        const btnGp = document.getElementById('subTabBtnGlobalPicklists');
        const btnLm = document.getElementById('subTabBtnLeadMapping');

        const viewFields = document.getElementById('viewFieldsSchema');
        const viewGp = document.getElementById('viewGlobalPicklists');
        const viewLm = document.getElementById('viewLeadMapping');

        // Reset tab styles
        [btnFields, btnGp, btnLm].forEach(btn => {
            if (!btn) return;
            btn.className = 'inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-semibold text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors';
        });

        if (viewFields) viewFields.classList.add('hidden');
        if (viewGp) viewGp.classList.add('hidden');
        if (viewLm) viewLm.classList.add('hidden');

        if (tab === 'fields') {
            if (btnFields) btnFields.className = 'inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-indigo-600 text-white shadow-xs';
            if (viewFields) viewFields.classList.remove('hidden');
            renderCustomFieldsTable();
            renderStandardFieldsTable();
        } else if (tab === 'global_picklists') {
            if (btnGp) btnGp.className = 'inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-indigo-600 text-white shadow-xs';
            if (viewGp) viewGp.classList.remove('hidden');
            renderGlobalPicklistsTable();
        } else if (tab === 'lead_mapping') {
            if (btnLm) btnLm.className = 'inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-indigo-600 text-white shadow-xs';
            if (viewLm) viewLm.classList.remove('hidden');
            renderLeadMappingStudio();
        }

        if (window.lucide) window.lucide.createIcons();
    }

    function bindSubTabNavigation() {
        const btnFields = document.getElementById('subTabBtnFields');
        const btnGp = document.getElementById('subTabBtnGlobalPicklists');
        const btnLm = document.getElementById('subTabBtnLeadMapping');

        if (btnFields) btnFields.onclick = () => setSubTab('fields');
        if (btnGp) btnGp.onclick = () => setSubTab('global_picklists');
        if (btnLm) btnLm.onclick = () => setSubTab('lead_mapping');
    }

    // -------------------------------------------------------------
    // VIEW 1: CUSTOM FIELDS TABLE
    // -------------------------------------------------------------
    function renderCustomFieldsTable() {
        const titleEl = document.getElementById('customFieldsHeaderTitle');
        const tbody = document.getElementById('customFieldsTableBody');
        if (!tbody) return;

        const currentModFields = state.customFields.filter(f => f.module === state.selectedModule);
        if (titleEl) {
            titleEl.textContent = `Custom Fields on ${getModuleLabel(state.selectedModule)} (${currentModFields.length})`;
        }

        if (currentModFields.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="py-12 text-center text-xs text-zinc-400 dark:text-zinc-500">
                        No custom fields defined on ${getModuleLabel(state.selectedModule)}. Click "+ Create Custom Field" to extend this entity.
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = currentModFields.map(cf => {
            const isMulti = cf.data_type === 'MultiSelect' || cf.is_multiselect;
            let optionsPreview = '';

            if (cf.global_picklist_id && cf.global_picklist) {
                optionsPreview = `
                    <div class="flex items-center gap-1.5">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                            🌐 Global: ${escapeHtml(cf.global_picklist.name)}
                        </span>
                    </div>
                `;
            } else if (['Dropdown', 'MultiSelect', 'Radio'].includes(cf.data_type) || isMulti) {
                optionsPreview = `<span class="text-xs text-zinc-600 dark:text-zinc-400 font-mono truncate max-w-[220px] block" title="${escapeHtml(cf.options || '')}">${escapeHtml(cf.options || '—')}</span>`;
            } else if (cf.data_type === 'AutoNumber') {
                const pad = cf.autonumber_padding || 4;
                optionsPreview = `<span class="text-xs font-semibold text-blue-600 dark:text-blue-400 font-mono">${escapeHtml(cf.autonumber_prefix || '')}${'0'.repeat(pad)}${escapeHtml(cf.autonumber_suffix || '')}</span>`;
            } else if (cf.data_type === 'RollupSummary') {
                optionsPreview = `<span class="text-xs font-medium text-amber-700 dark:text-amber-400">∑ ${escapeHtml(cf.rollup_type || 'COUNT')} on ${escapeHtml(cf.rollup_child_module || 'records')}</span>`;
            } else if (cf.data_type === 'Formula') {
                optionsPreview = `<span class="text-xs font-mono text-purple-600 dark:text-purple-400 truncate max-w-[200px] block">${escapeHtml(cf.formula_mode === 'lookup_import' ? `${cf.formula_source_field} ➔ ${cf.formula_target_field}` : (cf.formula_expression || 'Custom formula'))}</span>`;
            } else if (cf.data_type === 'Percentage') {
                optionsPreview = `<span class="text-xs text-zinc-500">Precision: ${cf.percentage_precision ?? 2} decimals</span>`;
            } else if (cf.data_type === 'Rating') {
                const iconChar = cf.rating_icon === 'heart' ? '♥' : cf.rating_icon === 'flame' ? '🔥' : '★';
                optionsPreview = `<span class="text-xs text-amber-500 font-semibold">${iconChar} Scale 1 to ${cf.rating_max || 5}</span>`;
            } else if (cf.data_type === 'MaskedText') {
                optionsPreview = `<span class="text-xs font-mono text-rose-600 dark:text-rose-400">Pattern: ${escapeHtml(cf.masked_pattern || 'last4')}</span>`;
            } else {
                optionsPreview = `<span class="text-xs text-zinc-400">Standard ${escapeHtml(cf.data_type)}</span>`;
            }

            return `
                <tr class="hover:bg-zinc-50/70 dark:hover:bg-zinc-800/40 transition-colors border-b border-zinc-100 dark:border-zinc-800/80">
                    <td class="py-3 px-4">
                        <strong class="text-xs font-bold text-zinc-900 dark:text-zinc-100">${escapeHtml(cf.field_label)}</strong>
                    </td>
                    <td class="py-3 px-4">
                        <code class="text-[11px] font-mono px-2 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700">${escapeHtml(cf.field_name)}</code>
                    </td>
                    <td class="py-3 px-4">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-zinc-100 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-200">
                            ${escapeHtml(cf.data_type)}${isMulti ? ' (Multi)' : ''}
                        </span>
                    </td>
                    <td class="py-3 px-4">
                        ${optionsPreview}
                    </td>
                    <td class="py-3 px-4">
                        ${cf.is_required
                            ? '<span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-rose-50 text-rose-700 dark:bg-rose-950/50 dark:text-rose-300 border border-rose-200 dark:border-rose-900">Required</span>'
                            : '<span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">Optional</span>'
                        }
                    </td>
                    <td class="py-3 px-4 max-w-xs">
                        <span class="text-xs text-zinc-500 dark:text-zinc-400 truncate block">${escapeHtml(cf.help_text || '—')}</span>
                    </td>
                    <td class="py-3 px-4 text-right">
                        <div class="inline-flex items-center gap-1.5">
                            <button
                                type="button"
                                onclick="window.openEditFieldModal(${cf.id})"
                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-semibold bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-800 dark:text-zinc-200 transition-colors"
                                title="Edit Field"
                            >
                                <i data-lucide="edit-2" class="w-3 h-3"></i>
                                <span>Edit</span>
                            </button>
                            <button
                                type="button"
                                onclick="window.handleDeleteField(${cf.id})"
                                class="inline-flex items-center justify-center p-1.5 rounded-md text-xs text-red-600 hover:bg-red-50 dark:hover:bg-red-950/40 transition-colors"
                                title="Delete Field"
                            >
                                <i data-lucide="trash-2" class="w-3 h-3"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');

        if (window.lucide) window.lucide.createIcons();
    }

    // -------------------------------------------------------------
    // VIEW 1: STANDARD SYSTEM FIELDS TABLE
    // -------------------------------------------------------------
    function renderStandardFieldsTable() {
        const titleEl = document.getElementById('standardFieldsHeaderTitle');
        const tbody = document.getElementById('standardFieldsTableBody');
        if (!tbody) return;

        const stdList = state.standardFields[state.selectedModule] || [];
        if (titleEl) {
            titleEl.textContent = `Standard System Fields on ${getModuleLabel(state.selectedModule)} (Core CRM Schema)`;
        }

        if (stdList.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="py-8 text-center text-xs text-zinc-400 dark:text-zinc-500">
                        No standard fields documented for ${getModuleLabel(state.selectedModule)}.
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = stdList.map(sf => {
            const override = state.standardOverrides[`${state.selectedModule}.${sf.name}`];
            const activeLabel = override?.field_label || sf.label;
            const isReq = override ? Boolean(override.is_required) : Boolean(sf.required);
            const activeDesc = override?.help_text || sf.description;
            const activeOptions = override?.options;

            return `
                <tr class="hover:bg-zinc-50/70 dark:hover:bg-zinc-800/40 transition-colors border-b border-zinc-100 dark:border-zinc-800/80">
                    <td class="py-3 px-4">
                        <div class="flex items-center gap-2">
                            <strong class="text-xs font-semibold text-zinc-900 dark:text-zinc-100">${escapeHtml(activeLabel)}</strong>
                            ${override && override.field_label ? `
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-700 dark:bg-purple-950 dark:text-purple-300">
                                    Customized
                                </span>
                            ` : ''}
                        </div>
                    </td>
                    <td class="py-3 px-4">
                        <code class="text-[11px] font-mono px-2 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700">${escapeHtml(sf.name)}</code>
                    </td>
                    <td class="py-3 px-4">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                            ${escapeHtml(sf.type)}
                        </span>
                        ${activeOptions ? `<div class="text-[11px] text-zinc-500 truncate max-w-[200px] mt-0.5 font-mono">${escapeHtml(activeOptions)}</div>` : ''}
                    </td>
                    <td class="py-3 px-4">
                        ${isReq
                            ? '<span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300 border border-amber-200 dark:border-amber-900">Required</span>'
                            : '<span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">Optional</span>'
                        }
                    </td>
                    <td class="py-3 px-4 max-w-xs">
                        <span class="text-xs text-zinc-500 dark:text-zinc-400 truncate block">${escapeHtml(activeDesc || '—')}</span>
                    </td>
                    <td class="py-3 px-4 text-right">
                        <button
                            type="button"
                            onclick="window.openEditStandardFieldModal('${escapeHtml(sf.name)}')"
                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-semibold bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-800 dark:text-zinc-200 transition-colors"
                            title="Edit Standard Field"
                        >
                            <i data-lucide="edit-2" class="w-3 h-3"></i>
                            <span>Edit</span>
                        </button>
                    </td>
                </tr>
            `;
        }).join('');

        if (window.lucide) window.lucide.createIcons();
    }

    // -------------------------------------------------------------
    // VIEW 2: GLOBAL PICKLISTS TABLE
    // -------------------------------------------------------------
    function renderGlobalPicklistsTable() {
        const tbody = document.getElementById('globalPicklistsTableBody');
        if (!tbody) return;

        if (state.globalPicklists.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="py-14 text-center text-xs text-zinc-400 dark:text-zinc-500">
                        No global picklist value sets found. Click "+ New Global Value Set" to define one.
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = state.globalPicklists.map(gp => {
            const vals = Array.isArray(gp.values) ? gp.values : [];
            const previewChips = vals.slice(0, 4).map(v => `
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-zinc-100 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 border border-zinc-200 dark:border-zinc-700">
                    ${escapeHtml(v)}
                </span>
            `).join('');

            const moreChip = vals.length > 4 ? `
                <span class="text-[11px] font-medium text-zinc-400 self-center">+${vals.length - 4} more</span>
            ` : '';

            return `
                <tr class="hover:bg-zinc-50/70 dark:hover:bg-zinc-800/40 transition-colors border-b border-zinc-100 dark:border-zinc-800/80">
                    <td class="py-3 px-4">
                        <strong class="text-xs font-bold text-zinc-900 dark:text-zinc-100">${escapeHtml(gp.name)}</strong>
                    </td>
                    <td class="py-3 px-4">
                        <code class="text-[11px] font-mono px-2 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700">${escapeHtml(gp.api_name)}</code>
                    </td>
                    <td class="py-3 px-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                            ${vals.length} options
                        </span>
                    </td>
                    <td class="py-3 px-4">
                        <div class="flex flex-wrap gap-1 max-w-sm">
                            ${previewChips}
                            ${moreChip}
                        </div>
                    </td>
                    <td class="py-3 px-4 max-w-xs">
                        <span class="text-xs text-zinc-500 dark:text-zinc-400 truncate block">${escapeHtml(gp.description || '—')}</span>
                    </td>
                    <td class="py-3 px-4 text-right">
                        <div class="inline-flex items-center gap-1.5">
                            <button
                                type="button"
                                onclick="window.openEditGlobalPicklistModal(${gp.id})"
                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-semibold bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-800 dark:text-zinc-200 transition-colors"
                                title="Edit Value Set"
                            >
                                <i data-lucide="edit-2" class="w-3 h-3"></i>
                                <span>Edit</span>
                            </button>
                            <button
                                type="button"
                                onclick="window.handleDeleteGlobalPicklist(${gp.id})"
                                class="inline-flex items-center justify-center p-1.5 rounded-md text-xs text-red-600 hover:bg-red-50 dark:hover:bg-red-950/40 transition-colors"
                                title="Delete Value Set"
                            >
                                <i data-lucide="trash-2" class="w-3 h-3"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');

        if (window.lucide) window.lucide.createIcons();
    }

    // -------------------------------------------------------------
    // VIEW 3: LEAD CONVERSION FIELD MAPPING STUDIO
    // -------------------------------------------------------------
    async function loadLeadMappingsData() {
        try {
            const res = await fetch('/api/lead-mappings', {
                headers: { 'Accept': 'application/json' }
            });
            if (res.ok) {
                const data = await res.json();
                state.leadMeta.leadFields = data.leadFields || [];
                state.leadMeta.accountFields = data.accountFields || [];
                state.leadMeta.contactFields = data.contactFields || [];
                state.leadMeta.opportunityFields = data.opportunityFields || [];

                const mapObj = {};
                (data.mappings || []).forEach(m => {
                    mapObj[`${m.source_field_name}_${m.target_module}`] = m;
                });
                state.leadMeta.mappings = mapObj;
            }
        } catch (err) {
            console.error('[ObjectFields] Error loading lead mappings:', err);
        }
    }

    function isTypeCompatible(srcType, tgtType) {
        if (!srcType || !tgtType) return false;
        if (srcType === tgtType) return true;
        if (['Text', 'Textarea'].includes(tgtType) && ['Text', 'Textarea', 'Phone', 'Email', 'URL', 'Dropdown', 'Radio', 'Rating', 'AutoNumber'].includes(srcType)) return true;
        if (['Number', 'Currency', 'Percentage'].includes(tgtType) && ['Number', 'Currency', 'Percentage', 'Rating'].includes(srcType)) return true;
        if (tgtType === 'DateTime' && srcType === 'Date') return true;
        if (tgtType === 'Date' && srcType === 'DateTime') return true;
        if (['Dropdown', 'Radio'].includes(tgtType) && ['Dropdown', 'Radio', 'Text'].includes(srcType)) return true;
        return false;
    }

    function setLeadMappingTab(targetTab) {
        state.leadMappingTab = targetTab;
        renderLeadMappingStudio();
    }

    function renderLeadMappingStudio() {
        const root = document.getElementById('viewLeadMapping');
        if (!root) return;

        const targetTab = state.leadMappingTab;
        let targetFields = [];
        if (targetTab === 'accounts') targetFields = state.leadMeta.accountFields;
        else if (targetTab === 'contacts') targetFields = state.leadMeta.contactFields;
        else if (targetTab === 'opportunities') targetFields = state.leadMeta.opportunityFields;

        const leadFields = state.leadMeta.leadFields || [];
        const customLeadFields = leadFields.filter(f => !f.is_standard);
        const standardLeadFields = leadFields.filter(f => f.is_standard);

        const mappedCount = leadFields.filter(lf => Boolean(state.leadMeta.mappings[`${lf.field_name}_${targetTab}`])).length;
        const targetLabel = targetTab.charAt(0).toUpperCase() + targetTab.slice(1);

        // Header and Target Tabs
        const tabActiveCls = 'inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold bg-indigo-600 text-white shadow-sm';
        const tabInactiveCls = 'inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold bg-white dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-700/60 transition-colors';

        let html = `
            <!-- Ribbon Banner -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-6 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-5 transition-colors">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-900 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0 shadow-xs">
                        <i data-lucide="git-merge" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2.5 flex-wrap">
                            <h2 class="text-lg font-extrabold text-zinc-950 dark:text-zinc-100 tracking-tight">Lead Conversion Field Mapping</h2>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700">
                                ${mappedCount} of ${leadFields.length} Mapped to ${targetLabel}
                            </span>
                        </div>
                        <p class="text-xs text-zinc-600 dark:text-zinc-400 mt-1">
                            Configure where lead fields copy when sales reps convert a qualified Lead into an Account, Contact, or Opportunity.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2.5 shrink-0">
                    <button
                        type="button"
                        onclick="window.handleAutoMatchLeadMappings()"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold bg-purple-50 dark:bg-purple-950/50 hover:bg-purple-100 dark:hover:bg-purple-900/50 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800 transition-colors"
                        title="Auto-match compatible fields"
                    >
                        <i data-lucide="sparkles" class="w-3.5 h-3.5 text-purple-600"></i>
                        <span>Auto-Match Fields</span>
                    </button>
                    <button
                        type="button"
                        onclick="window.handleSaveLeadMappings()"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white shadow-xs transition-colors"
                    >
                        <i data-lucide="save" class="w-3.5 h-3.5"></i>
                        <span>Save Mappings</span>
                    </button>
                </div>
            </div>

            <!-- Target Module Selector Tabs -->
            <div class="flex items-center gap-2 border-b border-zinc-200 dark:border-zinc-800 pb-3">
                <button
                    type="button"
                    onclick="window.setLeadMappingTab('accounts')"
                    class="${targetTab === 'accounts' ? tabActiveCls : tabInactiveCls}"
                >
                    <i data-lucide="building-2" class="w-4 h-4"></i>
                    <span>Accounts (${targetFieldsCount('accounts')})</span>
                </button>
                <button
                    type="button"
                    onclick="window.setLeadMappingTab('contacts')"
                    class="${targetTab === 'contacts' ? tabActiveCls : tabInactiveCls}"
                >
                    <i data-lucide="users" class="w-4 h-4"></i>
                    <span>Contacts (${targetFieldsCount('contacts')})</span>
                </button>
                <button
                    type="button"
                    onclick="window.setLeadMappingTab('opportunities')"
                    class="${targetTab === 'opportunities' ? tabActiveCls : tabInactiveCls}"
                >
                    <i data-lucide="briefcase" class="w-4 h-4"></i>
                    <span>Opportunities (${targetFieldsCount('opportunities')})</span>
                </button>
            </div>

            <!-- Custom Lead Fields Section -->
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-zinc-950 dark:text-zinc-100">
                            Custom Lead Fields (${customLeadFields.length})
                        </h3>
                        <p class="text-xs text-zinc-500">User-defined lead attributes mapped to ${targetLabel}</p>
                    </div>
                </div>

                <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden shadow-xs">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-zinc-50 dark:bg-zinc-800/60 border-b border-zinc-200 dark:border-zinc-800 text-[11px] font-bold text-zinc-500 uppercase tracking-wider">
                                <th class="py-3 px-4 w-1/3">Source Lead Field</th>
                                <th class="py-3 px-4 w-10 text-center"></th>
                                <th class="py-3 px-4 w-1/3">Target ${targetLabel} Field</th>
                                <th class="py-3 px-4 w-1/4">Type Compatibility</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/80">
                            ${customLeadFields.length === 0 ? `
                                <tr>
                                    <td colspan="4" class="py-8 text-center text-xs text-zinc-400">
                                        No custom lead fields found. Create custom fields on Leads to enable conversion mapping.
                                    </td>
                                </tr>
                            ` : customLeadFields.map(lf => renderMappingRow(lf, targetTab, targetFields)).join('')}
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Standard System Lead Fields Section -->
            <div class="space-y-3 pt-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-zinc-950 dark:text-zinc-100">
                            Standard Lead Fields (${standardLeadFields.length})
                        </h3>
                        <p class="text-xs text-zinc-500">Core system attributes with automated conversion bindings</p>
                    </div>
                </div>

                <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden shadow-xs">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-zinc-50 dark:bg-zinc-800/60 border-b border-zinc-200 dark:border-zinc-800 text-[11px] font-bold text-zinc-500 uppercase tracking-wider">
                                <th class="py-3 px-4 w-1/3">Source Lead Field</th>
                                <th class="py-3 px-4 w-10 text-center"></th>
                                <th class="py-3 px-4 w-1/3">Target ${targetLabel} Field</th>
                                <th class="py-3 px-4 w-1/4">Type Compatibility</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/80">
                            ${standardLeadFields.map(lf => renderMappingRow(lf, targetTab, targetFields)).join('')}
                        </tbody>
                    </table>
                </div>
            </div>
        `;

        root.innerHTML = html;
        if (window.lucide) window.lucide.createIcons();
    }

    function targetFieldsCount(module) {
        if (module === 'accounts') return (state.leadMeta.accountFields || []).length;
        if (module === 'contacts') return (state.leadMeta.contactFields || []).length;
        if (module === 'opportunities') return (state.leadMeta.opportunityFields || []).length;
        return 0;
    }

    function renderMappingRow(lf, targetTab, targetFields) {
        const key = `${lf.field_name}_${targetTab}`;
        const mapping = state.leadMeta.mappings[key];
        const isMapped = Boolean(mapping && mapping.target_field_name);
        const targetFieldName = mapping ? mapping.target_field_name : '';
        const targetField = targetFields.find(tf => tf.field_name === targetFieldName);

        const isLocked = lf.is_locked && (
            (lf.field_name === 'company' && targetTab === 'accounts') ||
            (['first_name', 'last_name', 'email'].includes(lf.field_name) && targetTab === 'contacts')
        );

        let compatBadge = '';
        if (isMapped) {
            const tgtType = targetField ? targetField.data_type : (mapping.target_data_type || lf.data_type);
            const ok = isTypeCompatible(lf.data_type, tgtType);
            if (ok) {
                compatBadge = `
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                        <i data-lucide="check" class="w-3 h-3 text-emerald-600"></i>
                        <span>Compatible (${lf.data_type} ➔ ${tgtType})</span>
                    </span>
                `;
            } else {
                compatBadge = `
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                        <i data-lucide="alert-circle" class="w-3 h-3 text-amber-600"></i>
                        <span>Type Mismatch (${lf.data_type} ➔ ${tgtType})</span>
                    </span>
                `;
            }
        } else {
            compatBadge = `<span class="text-xs text-zinc-400">Unmapped</span>`;
        }

        return `
            <tr class="hover:bg-zinc-50/70 dark:hover:bg-zinc-800/40 transition-colors">
                <td class="py-3 px-4">
                    <div class="flex items-center gap-2">
                        <strong class="text-xs font-bold text-zinc-900 dark:text-zinc-100">${escapeHtml(lf.field_label)}</strong>
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-mono bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700">${escapeHtml(lf.field_name)}</span>
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-blue-50 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300">${escapeHtml(lf.data_type)}</span>
                    </div>
                </td>
                <td class="py-3 px-4 text-center text-zinc-400">
                    <i data-lucide="arrow-right" class="w-4 h-4 mx-auto"></i>
                </td>
                <td class="py-3 px-4">
                    ${isLocked ? `
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold text-zinc-800 dark:text-zinc-200">${escapeHtml(targetField ? targetField.field_label : targetFieldName)}</span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-zinc-200 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-200">
                                Required by System
                            </span>
                        </div>
                    ` : `
                        <div class="flex items-center gap-2">
                            <select
                                class="w-full text-xs font-medium bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg px-2.5 py-1.5 text-zinc-900 dark:text-zinc-100 outline-none focus:border-indigo-500"
                                onchange="window.handleMappingChange('${escapeHtml(lf.field_name)}', this.value)"
                            >
                                <option value="">-- None (Do not copy) --</option>
                                ${targetFields.map(tf => `
                                    <option value="${escapeHtml(tf.field_name)}" ${tf.field_name === targetFieldName ? 'selected' : ''}>
                                        ${escapeHtml(tf.field_label)} (${escapeHtml(tf.data_type)})
                                    </option>
                                `).join('')}
                            </select>
                            ${!isMapped && !lf.is_standard ? `
                                <button
                                    type="button"
                                    onclick="window.handleCreateTargetField('${escapeHtml(lf.id)}')"
                                    class="shrink-0 px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-blue-50 hover:bg-blue-100 dark:bg-blue-950/60 dark:hover:bg-blue-900/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800 transition-colors"
                                    title="Create this custom field on target entity"
                                >
                                    Create in Target
                                </button>
                            ` : ''}
                        </div>
                    `}
                </td>
                <td class="py-3 px-4">
                    ${compatBadge}
                </td>
            </tr>
        `;
    }

    function handleMappingChange(sourceFieldName, targetFieldName) {
        const targetTab = state.leadMappingTab;
        const key = `${sourceFieldName}_${targetTab}`;
        const leadField = state.leadMeta.leadFields.find(f => f.field_name === sourceFieldName);
        if (!leadField) return;

        let targetFields = [];
        if (targetTab === 'accounts') targetFields = state.leadMeta.accountFields;
        else if (targetTab === 'contacts') targetFields = state.leadMeta.contactFields;
        else if (targetTab === 'opportunities') targetFields = state.leadMeta.opportunityFields;

        if (!targetFieldName) {
            delete state.leadMeta.mappings[key];
        } else {
            const targetField = targetFields.find(tf => tf.field_name === targetFieldName);
            if (targetField) {
                state.leadMeta.mappings[key] = {
                    source_field_name: leadField.field_name,
                    source_field_label: leadField.field_label,
                    source_data_type: leadField.data_type,
                    target_module: targetTab,
                    target_field_name: targetField.field_name,
                    target_field_label: targetField.field_label,
                    target_data_type: targetField.data_type,
                    is_active: 1
                };
            }
        }

        renderLeadMappingStudio();
    }

    async function handleAutoMatchLeadMappings() {
        const targetTab = state.leadMappingTab;
        try {
            const res = await fetch('/api/lead-mappings/auto-match', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                },
                body: JSON.stringify({ targetModule: targetTab })
            });

            if (res.ok) {
                const data = await res.json();
                const proposed = data.proposed || [];
                if (proposed.length === 0) {
                    showToast(`No unmapped matching fields found on ${targetTab}.`, 'info');
                    return;
                }

                let newCount = 0;
                proposed.forEach(p => {
                    const key = `${p.source_field_name}_${targetTab}`;
                    if (!state.leadMeta.mappings[key]) {
                        state.leadMeta.mappings[key] = {
                            source_field_name: p.source_field_name,
                            source_field_label: p.source_field_label,
                            source_data_type: p.source_data_type,
                            target_module: targetTab,
                            target_field_name: p.target_field_name,
                            target_field_label: p.target_field_label,
                            target_data_type: p.target_data_type,
                            is_active: 1
                        };
                        newCount++;
                    }
                });

                showToast(`Auto-matched ${newCount} fields for ${targetTab}! Click "Save Mappings" to persist.`, 'success');
                renderLeadMappingStudio();
            } else {
                showToast('Auto-match failed.', 'error');
            }
        } catch (err) {
            showToast(`Auto-match error: ${err.message}`, 'error');
        }
    }

    async function handleSaveLeadMappings() {
        try {
            const mappingsList = Object.values(state.leadMeta.mappings);
            const res = await fetch('/api/lead-mappings', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                },
                body: JSON.stringify({ mappings: mappingsList })
            });

            if (res.ok) {
                showToast('Lead conversion field mappings saved successfully!', 'success');
            } else {
                showToast('Error saving lead conversion mappings.', 'error');
            }
        } catch (err) {
            showToast(`Error: ${err.message}`, 'error');
        }
    }

    async function handleCreateTargetField(leadFieldId) {
        const targetTab = state.leadMappingTab;
        try {
            const res = await fetch('/api/lead-mappings/create-target-field', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                },
                body: JSON.stringify({ targetModule: targetTab, leadFieldId })
            });

            if (res.ok) {
                showToast(`Created target field on ${targetTab}!`, 'success');
                await loadLeadMappingsData();
                renderLeadMappingStudio();
            } else {
                showToast('Failed to create target field.', 'error');
            }
        } catch (err) {
            showToast(`Error: ${err.message}`, 'error');
        }
    }

    // -------------------------------------------------------------
    // MODAL 1: CREATE / EDIT CUSTOM FIELD
    // -------------------------------------------------------------
    function openCreateFieldModal() {
        state.editingField = null;
        state.isFieldManualName = false;

        document.getElementById('customFieldModalTitle').textContent = `Create Custom Field on ${getModuleLabel(state.selectedModule)}`;
        document.getElementById('cfModalTargetModuleBadge').textContent = getModuleLabel(state.selectedModule);

        // Reset form inputs
        document.getElementById('cfFieldLabel').value = '';
        document.getElementById('cfFieldName').value = '';
        document.getElementById('cfDataType').value = 'Text';
        document.getElementById('cfIsRequired').checked = false;
        document.getElementById('cfDefaultValue').value = '';
        document.getElementById('cfHelpText').value = '';

        // Dynamic panels
        handleDataTypePanel('Text');

        // Populate global picklist options selector
        populateGlobalPicklistSelector();

        openModalDialog('customFieldModalBackdrop');
    }

    function openEditFieldModal(fieldId) {
        const field = state.customFields.find(f => f.id === fieldId);
        if (!field) return;

        state.editingField = field;
        state.isFieldManualName = true;

        document.getElementById('customFieldModalTitle').textContent = `Edit Custom Field: ${field.field_label}`;
        document.getElementById('cfModalTargetModuleBadge').textContent = getModuleLabel(field.module);

        document.getElementById('cfFieldLabel').value = field.field_label || '';
        document.getElementById('cfFieldName').value = field.field_name || '';
        document.getElementById('cfDataType').value = field.data_type || 'Text';
        document.getElementById('cfIsRequired').checked = Boolean(field.is_required);
        document.getElementById('cfDefaultValue').value = field.default_value || '';
        document.getElementById('cfHelpText').value = field.help_text || '';

        handleDataTypePanel(field.data_type || 'Text');
        populateGlobalPicklistSelector(field.global_picklist_id);

        if (field.global_picklist_id) {
            setPicklistSource('global');
        } else {
            setPicklistSource('custom');
            if (document.getElementById('cfDropdownOptions')) {
                document.getElementById('cfDropdownOptions').value = field.options || '';
            }
        }

        // Set type-specific values
        if (field.data_type === 'Percentage') {
            if (document.getElementById('cfPercentagePrecision')) document.getElementById('cfPercentagePrecision').value = field.percentage_precision ?? 2;
            if (document.getElementById('cfPercentageMin')) document.getElementById('cfPercentageMin').value = field.percentage_min ?? 0;
            if (document.getElementById('cfPercentageMax')) document.getElementById('cfPercentageMax').value = field.percentage_max ?? 100;
        } else if (field.data_type === 'Rating') {
            if (document.getElementById('cfRatingMax')) document.getElementById('cfRatingMax').value = field.rating_max || 5;
            if (document.getElementById('cfRatingIcon')) document.getElementById('cfRatingIcon').value = field.rating_icon || 'star';
        } else if (field.data_type === 'AutoNumber') {
            if (document.getElementById('cfAutonumberPrefix')) document.getElementById('cfAutonumberPrefix').value = field.autonumber_prefix || '';
            if (document.getElementById('cfAutonumberSuffix')) document.getElementById('cfAutonumberSuffix').value = field.autonumber_suffix || '';
            if (document.getElementById('cfAutonumberPadding')) document.getElementById('cfAutonumberPadding').value = field.autonumber_padding || 4;
            if (document.getElementById('cfAutonumberStartNum')) document.getElementById('cfAutonumberStartNum').value = field.autonumber_start_num || 1;
        } else if (field.data_type === 'RollupSummary') {
            if (document.getElementById('cfRollupChildModule')) document.getElementById('cfRollupChildModule').value = field.rollup_child_module || 'opportunities';
            if (document.getElementById('cfRollupType')) document.getElementById('cfRollupType').value = field.rollup_type || 'COUNT';
            if (document.getElementById('cfRollupTargetField')) document.getElementById('cfRollupTargetField').value = field.rollup_target_field || 'amount';
        } else if (field.data_type === 'MaskedText') {
            if (document.getElementById('cfMaskedPattern')) document.getElementById('cfMaskedPattern').value = field.masked_pattern || 'last4';
        }

        openModalDialog('customFieldModalBackdrop');
    }

    function handleDataTypePanel(type) {
        const pPicklist = document.getElementById('cfPanelPicklist');
        const pLookup = document.getElementById('cfPanelLookup');
        const pFormula = document.getElementById('cfPanelFormula');
        const pPercentage = document.getElementById('cfPanelPercentage');
        const pRating = document.getElementById('cfPanelRating');
        const pAutoNumber = document.getElementById('cfPanelAutoNumber');
        const pRollup = document.getElementById('cfPanelRollup');
        const pMasked = document.getElementById('cfPanelMasked');

        [pPicklist, pLookup, pFormula, pPercentage, pRating, pAutoNumber, pRollup, pMasked].forEach(p => {
            if (p) p.classList.add('hidden');
        });

        if (['Dropdown', 'MultiSelect', 'Radio'].includes(type)) {
            if (pPicklist) pPicklist.classList.remove('hidden');
        } else if (type === 'Lookup') {
            if (pLookup) pLookup.classList.remove('hidden');
        } else if (type === 'Formula') {
            if (pFormula) pFormula.classList.remove('hidden');
        } else if (type === 'Percentage') {
            if (pPercentage) pPercentage.classList.remove('hidden');
        } else if (type === 'Rating') {
            if (pRating) pRating.classList.remove('hidden');
        } else if (type === 'AutoNumber') {
            if (pAutoNumber) pAutoNumber.classList.remove('hidden');
        } else if (type === 'RollupSummary') {
            if (pRollup) pRollup.classList.remove('hidden');
        } else if (type === 'MaskedText') {
            if (pMasked) pMasked.classList.remove('hidden');
        }
    }

    function setPicklistSource(source) {
        const btnCustom = document.getElementById('cfBtnSourceCustom');
        const btnGlobal = document.getElementById('cfBtnSourceGlobal');
        const boxCustom = document.getElementById('cfBoxCustomValues');
        const boxGlobal = document.getElementById('cfBoxGlobalValues');

        if (source === 'global') {
            if (btnGlobal) btnGlobal.className = 'px-3 py-1.5 rounded-lg text-xs font-bold bg-indigo-600 text-white shadow-xs';
            if (btnCustom) btnCustom.className = 'px-3 py-1.5 rounded-lg text-xs font-semibold bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 hover:bg-zinc-200 transition-colors';
            if (boxCustom) boxCustom.classList.add('hidden');
            if (boxGlobal) boxGlobal.classList.remove('hidden');
        } else {
            if (btnCustom) btnCustom.className = 'px-3 py-1.5 rounded-lg text-xs font-bold bg-indigo-600 text-white shadow-xs';
            if (btnGlobal) btnGlobal.className = 'px-3 py-1.5 rounded-lg text-xs font-semibold bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 hover:bg-zinc-200 transition-colors';
            if (boxGlobal) boxGlobal.classList.add('hidden');
            if (boxCustom) boxCustom.classList.remove('hidden');
        }
    }

    function populateGlobalPicklistSelector(selectedId = null) {
        const select = document.getElementById('cfGlobalPicklistSelect');
        if (!select) return;

        select.innerHTML = '<option value="">-- Select Global Picklist Set --</option>';
        state.globalPicklists.forEach(gp => {
            const opt = document.createElement('option');
            opt.value = gp.id;
            opt.textContent = `${gp.name} (${(gp.values || []).length} options)`;
            if (String(gp.id) === String(selectedId)) opt.selected = true;
            select.appendChild(opt);
        });
    }

    async function handleSaveCustomField(e) {
        if (e) e.preventDefault();

        const label = document.getElementById('cfFieldLabel')?.value.trim();
        let fieldName = document.getElementById('cfFieldName')?.value.trim();
        const dataType = document.getElementById('cfDataType')?.value;
        const isRequired = document.getElementById('cfIsRequired')?.checked || false;
        const defaultValue = document.getElementById('cfDefaultValue')?.value || '';
        const helpText = document.getElementById('cfHelpText')?.value || '';

        if (!label) {
            showToast('Field label is required.', 'error');
            return;
        }

        if (!fieldName || fieldName === 'cf_') {
            fieldName = formatFieldKeyFromLabel(label);
        } else if (!fieldName.startsWith('cf_')) {
            fieldName = `cf_${fieldName}`;
        }

        const isEditing = Boolean(state.editingField);
        const module = isEditing ? state.editingField.module : state.selectedModule;

        const payload = {
            module,
            fieldLabel: label,
            fieldName,
            dataType,
            isRequired,
            defaultValue,
            helpText
        };

        // Picklist config
        if (['Dropdown', 'MultiSelect', 'Radio'].includes(dataType)) {
            const isGlobal = !document.getElementById('cfBoxGlobalValues')?.classList.contains('hidden');
            if (isGlobal) {
                const gpId = document.getElementById('cfGlobalPicklistSelect')?.value;
                payload.global_picklist_id = gpId || null;
                const matchedGp = state.globalPicklists.find(gp => String(gp.id) === String(gpId));
                if (matchedGp) {
                    payload.options = Array.isArray(matchedGp.values) ? matchedGp.values.join(', ') : matchedGp.values;
                }
            } else {
                payload.global_picklist_id = null;
                payload.options = document.getElementById('cfDropdownOptions')?.value || '';
            }
        } else if (dataType === 'Percentage') {
            payload.percentage_precision = parseInt(document.getElementById('cfPercentagePrecision')?.value || 2, 10);
            payload.percentage_min = document.getElementById('cfPercentageMin')?.value !== '' ? parseFloat(document.getElementById('cfPercentageMin').value) : null;
            payload.percentage_max = document.getElementById('cfPercentageMax')?.value !== '' ? parseFloat(document.getElementById('cfPercentageMax').value) : null;
        } else if (dataType === 'Rating') {
            payload.rating_max = parseInt(document.getElementById('cfRatingMax')?.value || 5, 10);
            payload.rating_icon = document.getElementById('cfRatingIcon')?.value || 'star';
        } else if (dataType === 'AutoNumber') {
            payload.autonumber_prefix = document.getElementById('cfAutonumberPrefix')?.value || '';
            payload.autonumber_suffix = document.getElementById('cfAutonumberSuffix')?.value || '';
            payload.autonumber_padding = parseInt(document.getElementById('cfAutonumberPadding')?.value || 4, 10);
            payload.autonumber_start_num = parseInt(document.getElementById('cfAutonumberStartNum')?.value || 1, 10);
        } else if (dataType === 'RollupSummary') {
            payload.rollup_child_module = document.getElementById('cfRollupChildModule')?.value || 'opportunities';
            payload.rollup_type = document.getElementById('cfRollupType')?.value || 'COUNT';
            payload.rollup_target_field = document.getElementById('cfRollupTargetField')?.value || 'amount';
        } else if (dataType === 'MaskedText') {
            payload.masked_pattern = document.getElementById('cfMaskedPattern')?.value || 'last4';
        }

        try {
            const url = isEditing ? `/api/settings/fields/${state.editingField.id}` : '/api/settings/fields';
            const method = isEditing ? 'PUT' : 'POST';

            const res = await fetch(url, {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                },
                body: JSON.stringify(payload)
            });

            if (res.ok) {
                const data = await res.json();
                closeModalDialog('customFieldModalBackdrop');
                showToast(isEditing ? `Field '${label}' updated successfully!` : `Field '${label}' created successfully!`, 'success');

                // Reload Custom Fields
                await reloadCustomFields();
            } else {
                const errData = await res.json().catch(() => ({}));
                showToast(`Error saving field: ${errData.message || res.statusText}`, 'error');
            }
        } catch (err) {
            showToast(`Error: ${err.message}`, 'error');
        }
    }

    async function handleDeleteField(fieldId) {
        const field = state.customFields.find(f => f.id === fieldId);
        if (!field) return;

        if (!confirm(`Are you sure you want to permanently delete custom field '${field.field_label}' (${field.field_name})? Any data stored in this column will be lost.`)) {
            return;
        }

        try {
            const res = await fetch(`/api/settings/fields/${fieldId}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                }
            });

            if (res.ok) {
                showToast(`Field '${field.field_label}' deleted.`, 'success');
                await reloadCustomFields();
            } else {
                showToast('Failed to delete field.', 'error');
            }
        } catch (err) {
            showToast(`Error: ${err.message}`, 'error');
        }
    }

    async function reloadCustomFields() {
        try {
            const res = await fetch('/api/settings/fields', {
                headers: { 'Accept': 'application/json' }
            });
            if (res.ok) {
                state.customFields = await res.json();
                updateKpis();
                renderCustomFieldsTable();
            }
        } catch (err) {
            console.error('[ObjectFields] Error reloading fields:', err);
        }
    }

    // -------------------------------------------------------------
    // MODAL 2: EDIT STANDARD FIELD OVERRIDE
    // -------------------------------------------------------------
    function openEditStandardFieldModal(fieldName) {
        const stdList = state.standardFields[state.selectedModule] || [];
        const sf = stdList.find(f => f.name === fieldName);
        if (!sf) return;

        state.editingStandardField = sf;
        const override = state.standardOverrides[`${state.selectedModule}.${sf.name}`];

        document.getElementById('sfModalTitle').textContent = `Customize Standard Field: ${sf.label}`;
        document.getElementById('sfModalColumn').textContent = sf.name;
        document.getElementById('sfModalDataType').textContent = sf.type;

        document.getElementById('sfFieldLabel').value = override?.field_label || sf.label;
        document.getElementById('sfIsRequired').checked = override ? Boolean(override.is_required) : Boolean(sf.required);
        document.getElementById('sfHelpText').value = override?.help_text || sf.description || '';
        document.getElementById('sfOptions').value = override?.options || '';

        openModalDialog('standardFieldModalBackdrop');
    }

    async function handleSaveStandardFieldOverride(e) {
        if (e) e.preventDefault();
        if (!state.editingStandardField) return;

        const sf = state.editingStandardField;
        const fieldLabel = document.getElementById('sfFieldLabel')?.value.trim();
        const isRequired = document.getElementById('sfIsRequired')?.checked || false;
        const helpText = document.getElementById('sfHelpText')?.value || '';
        const options = document.getElementById('sfOptions')?.value || '';

        const payload = {
            module: state.selectedModule,
            fieldName: sf.name,
            fieldLabel: fieldLabel || sf.label,
            isRequired,
            helpText,
            options
        };

        try {
            const res = await fetch('/api/settings/standard-fields', {
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
                state.standardOverrides[`${state.selectedModule}.${sf.name}`] = data.override;
                closeModalDialog('standardFieldModalBackdrop');
                showToast(`Standard field '${sf.name}' updated successfully!`, 'success');
                renderStandardFieldsTable();
            } else {
                showToast('Error saving standard field override.', 'error');
            }
        } catch (err) {
            showToast(`Error: ${err.message}`, 'error');
        }
    }

    // -------------------------------------------------------------
    // MODAL 3: CREATE / EDIT GLOBAL PICKLIST VALUE SET
    // -------------------------------------------------------------
    function openCreateGlobalPicklistModal() {
        state.editingGlobalPicklist = null;
        state.isGpManualApiName = false;

        document.getElementById('gpModalTitle').textContent = 'Create Global Picklist Value Set';
        document.getElementById('gpName').value = '';
        document.getElementById('gpApiName').value = '';
        document.getElementById('gpDescription').value = '';
        document.getElementById('gpValues').value = '';

        openModalDialog('globalPicklistModalBackdrop');
    }

    function openEditGlobalPicklistModal(gpId) {
        const gp = state.globalPicklists.find(g => g.id === gpId);
        if (!gp) return;

        state.editingGlobalPicklist = gp;
        state.isGpManualApiName = true;

        document.getElementById('gpModalTitle').textContent = `Edit Value Set: ${gp.name}`;
        document.getElementById('gpName').value = gp.name || '';
        document.getElementById('gpApiName').value = gp.api_name || '';
        document.getElementById('gpDescription').value = gp.description || '';

        const vals = Array.isArray(gp.values) ? gp.values.join('\n') : (gp.values || '');
        document.getElementById('gpValues').value = vals;

        openModalDialog('globalPicklistModalBackdrop');
    }

    async function handleSaveGlobalPicklist(e) {
        if (e) e.preventDefault();

        const name = document.getElementById('gpName')?.value.trim();
        let apiName = document.getElementById('gpApiName')?.value.trim();
        const description = document.getElementById('gpDescription')?.value || '';
        const valuesText = document.getElementById('gpValues')?.value || '';

        if (!name) {
            showToast('Value set name is required.', 'error');
            return;
        }

        if (!valuesText.trim()) {
            showToast('Please enter at least one picklist value.', 'error');
            return;
        }

        if (!apiName) {
            apiName = formatGpApiKeyFromName(name);
        }

        const isEditing = Boolean(state.editingGlobalPicklist);
        const payload = {
            name,
            api_name: apiName,
            description,
            values: valuesText
        };

        try {
            const url = isEditing ? `/api/global-picklists/${state.editingGlobalPicklist.id}` : '/api/global-picklists';
            const method = isEditing ? 'PUT' : 'POST';

            const res = await fetch(url, {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                },
                body: JSON.stringify(payload)
            });

            if (res.ok) {
                closeModalDialog('globalPicklistModalBackdrop');
                showToast(isEditing ? `Global picklist '${name}' updated!` : `Global picklist '${name}' created!`, 'success');
                await reloadGlobalPicklists();
            } else {
                const errData = await res.json().catch(() => ({}));
                showToast(`Error saving value set: ${errData.message || res.statusText}`, 'error');
            }
        } catch (err) {
            showToast(`Error: ${err.message}`, 'error');
        }
    }

    async function handleDeleteGlobalPicklist(gpId) {
        const gp = state.globalPicklists.find(g => g.id === gpId);
        if (!gp) return;

        if (!confirm(`Are you sure you want to delete global picklist '${gp.name}'? Existing custom fields using this set will retain their options snapshot.`)) {
            return;
        }

        try {
            const res = await fetch(`/api/global-picklists/${gpId}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                }
            });

            if (res.ok) {
                showToast(`Global picklist '${gp.name}' deleted.`, 'success');
                await reloadGlobalPicklists();
            } else {
                showToast('Failed to delete global picklist.', 'error');
            }
        } catch (err) {
            showToast(`Error: ${err.message}`, 'error');
        }
    }

    async function reloadGlobalPicklists() {
        try {
            const res = await fetch('/api/global-picklists', {
                headers: { 'Accept': 'application/json' }
            });
            if (res.ok) {
                state.globalPicklists = await res.json();
                updateKpis();
                renderGlobalPicklistsTable();
            }
        } catch (err) {
            console.error('[ObjectFields] Error reloading global picklists:', err);
        }
    }

    // -------------------------------------------------------------
    // MODAL DIALOG CONTROLLERS
    // -------------------------------------------------------------
    function openModalDialog(backdropId) {
        const backdrop = document.getElementById(backdropId);
        if (!backdrop) return;
        backdrop.classList.remove('hidden');
        backdrop.classList.add('flex');
        document.body.style.overflow = 'hidden';
        if (window.lucide) window.lucide.createIcons();
    }

    function closeModalDialog(backdropId) {
        const backdrop = document.getElementById(backdropId);
        if (!backdrop) return;
        backdrop.classList.add('hidden');
        backdrop.classList.remove('flex');
        document.body.style.overflow = '';
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // -------------------------------------------------------------
    // FORM EVENTS & DYNAMIC INPUT BINDINGS
    // -------------------------------------------------------------
    function bindFormEvents() {
        const root = document.getElementById('ofStudioRoot');
        if (!root || root._eventsBound) return;
        root._eventsBound = true;

        // Label input debounce to auto-populate Database Field Key
        const labelInput = document.getElementById('cfFieldLabel');
        const nameInput = document.getElementById('cfFieldName');
        if (labelInput && nameInput) {
            labelInput.addEventListener('input', function () {
                if (!state.isFieldManualName) {
                    nameInput.value = formatFieldKeyFromLabel(this.value);
                }
            });
            nameInput.addEventListener('input', function () {
                state.isFieldManualName = true;
            });
        }

        // Global Picklist Name debounce
        const gpNameInput = document.getElementById('gpName');
        const gpApiInput = document.getElementById('gpApiName');
        if (gpNameInput && gpApiInput) {
            gpNameInput.addEventListener('input', function () {
                if (!state.isGpManualApiName) {
                    gpApiInput.value = formatGpApiKeyFromName(this.value);
                }
            });
            gpApiInput.addEventListener('input', function () {
                state.isGpManualApiName = true;
            });
        }

        // Data Type change
        const dataTypeSelect = document.getElementById('cfDataType');
        if (dataTypeSelect) {
            dataTypeSelect.addEventListener('change', function () {
                handleDataTypePanel(this.value);
            });
        }

        // Forms submission bindings
        const cfForm = document.getElementById('customFieldForm');
        if (cfForm) cfForm.addEventListener('submit', handleSaveCustomField);

        const sfForm = document.getElementById('standardFieldForm');
        if (sfForm) sfForm.addEventListener('submit', handleSaveStandardFieldOverride);

        const gpForm = document.getElementById('globalPicklistForm');
        if (gpForm) gpForm.addEventListener('submit', handleSaveGlobalPicklist);
    }

    // Auto-run if DOM already loaded (e.g. SPA injected) or defer to DOMContentLoaded
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        if (document.getElementById('ofStudioRoot')) {
            initObjectFields();
        }
    } else {
        document.addEventListener('DOMContentLoaded', initObjectFields);
    }

    // -------------------------------------------------------------
    // EXPOSE WINDOW API FOR TEMPLATE EVENT HANDLERS & SPA NAVIGATION
    // -------------------------------------------------------------
    window.initObjectFields = initObjectFields;
    window.setSubTab = setSubTab;
    window.openCreateFieldModal = openCreateFieldModal;
    window.openEditFieldModal = openEditFieldModal;
    window.handleDeleteField = handleDeleteField;
    window.closeModalDialog = closeModalDialog;
    window.openEditStandardFieldModal = openEditStandardFieldModal;
    window.openCreateGlobalPicklistModal = openCreateGlobalPicklistModal;
    window.openEditGlobalPicklistModal = openEditGlobalPicklistModal;
    window.handleDeleteGlobalPicklist = handleDeleteGlobalPicklist;
    window.setPicklistSource = setPicklistSource;
    window.setLeadMappingTab = setLeadMappingTab;
    window.handleMappingChange = handleMappingChange;
    window.handleAutoMatchLeadMappings = handleAutoMatchLeadMappings;
    window.handleSaveLeadMappings = handleSaveLeadMappings;
    window.handleCreateTargetField = handleCreateTargetField;

})();
