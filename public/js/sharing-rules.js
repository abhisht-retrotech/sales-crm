/**
 * Enterprise Sharing Rules & Record Security Studio
 * 1-to-1 Parity with React SharingRulesStudio.tsx
 * Pure Tailwind CSS & Zero-Clash Isolated Module
 */
(function () {
    'use strict';

    const MODULE_DISPLAY_NAMES = {
        opportunities: 'Opportunities',
        leads: 'Leads',
        accounts: 'Accounts',
        payments: 'Invoices & Payments',
        purchase_deals: 'Purchase Deals',
        shipments: 'Shipping & Freight'
    };

    const OPERATOR_LABELS = {
        equals: 'Equals (=)',
        not_equals: 'Not Equals (!=)',
        greater_than: 'Greater Than (>)',
        less_than: 'Less Than (<)',
        greater_or_equal: 'Greater or Equal (>=)',
        less_or_equal: 'Less or Equal (<=)',
        contains: 'Contains',
        is_set: 'Is Populated',
        is_empty: 'Is Blank'
    };

    let state = {
        rules: [],
        meta: null,
        loading: false,
        selectedModule: 'all',
        searchFilter: '',
        recalculatingId: null,
        isModalOpen: false,
        editingRule: null,
        formName: '',
        formDescription: '',
        formModule: 'opportunities',
        formRuleType: 'criteria_based',
        formCriteria: [{ field: 'amount', operator: 'greater_or_equal', value: '1000000' }],
        formOwnershipRoleId: '',
        formOwnershipProfileId: '',
        formShareWithType: 'profile',
        formShareWithId: '',
        formAccessLevel: 'read',
        formIsActive: true,
        formSaving: false,
        formError: null
    };

    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function showToast(message) {
        let toastEl = document.getElementById('srStudioToast');
        if (!toastEl) {
            toastEl = document.createElement('div');
            toastEl.id = 'srStudioToast';
            toastEl.className = 'fixed bottom-6 right-6 bg-zinc-950 text-white px-4 py-3 rounded-xl shadow-2xl flex items-center gap-2.5 z-50 text-xs font-medium transition-all duration-300 transform translate-y-10 opacity-0';
            document.body.appendChild(toastEl);
        }

        toastEl.innerHTML = `
            <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>${escapeHtml(message)}</span>
        `;

        toastEl.classList.remove('translate-y-10', 'opacity-0');
        toastEl.classList.add('translate-y-0', 'opacity-100');

        if (window.__srToastTimer) clearTimeout(window.__srToastTimer);
        window.__srToastTimer = setTimeout(() => {
            toastEl.classList.remove('translate-y-0', 'opacity-100');
            toastEl.classList.add('translate-y-10', 'opacity-0');
        }, 3500);
    }

    async function fetchRules() {
        try {
            const res = await fetch('/api/sharing-rules', {
                headers: { 'Accept': 'application/json' }
            });
            const json = await res.json();
            if (json.success && Array.isArray(json.data)) {
                state.rules = json.data;
            } else if (Array.isArray(json)) {
                state.rules = json;
            }
        } catch (e) {
            console.error('Failed to load sharing rules:', e);
        }
    }

    async function fetchMeta() {
        try {
            const res = await fetch('/api/sharing-rules/meta', {
                headers: { 'Accept': 'application/json' }
            });
            const json = await res.json();
            if (json.success && json.data) {
                state.meta = json.data;
            }
        } catch (e) {
            console.error('Failed to load sharing rules meta:', e);
        }
    }

    function renderModuleTabs() {
        const tabsContainer = document.getElementById('srModuleTabs');
        if (!tabsContainer) return;

        const totalRules = state.rules.length;
        const isAllSelected = state.selectedModule === 'all';

        let html = `
            <button
                type="button"
                onclick="window.selectSrModule('all')"
                class="px-3.5 py-1.5 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition-all ${
                    isAllSelected
                        ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900 shadow-sm'
                        : 'text-zinc-600 hover:text-zinc-900 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:text-zinc-200 dark:hover:bg-zinc-800'
                }"
            >
                <span>All Modules</span>
                <span class="text-[10px] px-1.5 py-0.5 rounded-full ${
                    isAllSelected
                        ? 'bg-white/20 text-white dark:bg-black/10 dark:text-zinc-900'
                        : 'bg-zinc-200/70 text-zinc-600 dark:bg-zinc-700/60 dark:text-zinc-400'
                }">
                    ${totalRules}
                </span>
            </button>
        `;

        Object.entries(MODULE_DISPLAY_NAMES).forEach(([modKey, modLabel]) => {
            const count = state.rules.filter(r => r.module === modKey).length;
            const isSelected = state.selectedModule === modKey;

            html += `
                <button
                    type="button"
                    onclick="window.selectSrModule('${modKey}')"
                    class="px-3.5 py-1.5 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition-all whitespace-nowrap ${
                        isSelected
                            ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-600/30'
                            : 'text-zinc-600 hover:text-zinc-900 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:text-zinc-200 dark:hover:bg-zinc-800'
                    }"
                >
                    <span>${escapeHtml(modLabel)}</span>
                    ${count > 0 ? `
                        <span class="text-[10px] px-1.5 py-0.5 rounded-full ${
                            isSelected
                                ? 'bg-white/25 text-white'
                                : 'bg-zinc-200/70 text-zinc-600 dark:bg-zinc-700/60 dark:text-zinc-400'
                        }">
                            ${count}
                        </span>
                    ` : ''}
                </button>
            `;
        });

        tabsContainer.innerHTML = html;
    }

    function renderRulesList() {
        const container = document.getElementById('srRulesContainer');
        if (!container) return;

        renderModuleTabs();

        const filteredRules = state.rules.filter(r => {
            if (state.selectedModule !== 'all' && r.module !== state.selectedModule) return false;
            if (state.searchFilter.trim()) {
                const term = state.searchFilter.toLowerCase();
                const matchName = (r.name || '').toLowerCase().includes(term);
                const matchDesc = (r.description || '').toLowerCase().includes(term);
                const matchTarget = (
                    r.share_with_profile_name ||
                    r.share_with_role_name ||
                    r.share_with_user_name ||
                    ''
                ).toLowerCase().includes(term);
                return matchName || matchDesc || matchTarget;
            }
            return true;
        });

        if (filteredRules.length === 0) {
            const currentModName = state.selectedModule !== 'all' ? (MODULE_DISPLAY_NAMES[state.selectedModule] || state.selectedModule) : '';
            container.innerHTML = `
                <div class="text-center py-16 px-6 bg-white dark:bg-zinc-900 border-2 border-dashed border-zinc-200 dark:border-zinc-800 rounded-2xl shadow-xs transition-colors">
                    <div class="w-12 h-12 mx-auto rounded-xl bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-400 dark:text-zinc-500 mb-3.5">
                        <i data-lucide="lock" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100 mb-1.5">
                        No Sharing Rules Configured ${currentModName ? 'for ' + escapeHtml(currentModName) : ''}
                    </h3>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 max-w-md mx-auto mb-5 leading-relaxed">
                        Create automated rules to grant access to records meeting high deal values, sensitive pipeline stages, or specific account industries.
                    </p>
                    <button
                        type="button"
                        onclick="window.openCreateSharingRuleModal()"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700/70 shadow-xs transition-all"
                    >
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                        <span>Create First Rule</span>
                    </button>
                </div>
            `;
            if (window.lucide) lucide.createIcons();
            return;
        }

        let html = '<div class="flex flex-col gap-3.5">';

        filteredRules.forEach(rule => {
            const isRecalculating = state.recalculatingId === rule.id;
            const targetName =
                rule.share_with_type === 'profile'
                    ? (rule.share_with_profile_name || rule.share_with_id)
                    : rule.share_with_type === 'role'
                    ? (rule.share_with_role_name || rule.share_with_id)
                    : (rule.share_with_user_name || rule.share_with_id);

            const borderAccent = rule.is_active
                ? (rule.access_level === 'read_write' ? 'border-l-emerald-500' : 'border-l-indigo-600')
                : 'border-l-zinc-300 dark:border-l-zinc-700';

            const cardOpacity = rule.is_active ? 'opacity-100' : 'opacity-65';

            html += `
                <div
                    class="bg-white dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800 rounded-xl p-4 lg:p-5 flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-xs hover:shadow-md transition-all border-l-4 ${borderAccent} ${cardOpacity}"
                >
                    <!-- Left: Info & Criteria -->
                    <div class="flex flex-col gap-1.5 flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h4 class="text-sm font-bold text-zinc-950 dark:text-zinc-100 tracking-tight">
                                ${escapeHtml(rule.name)}
                            </h4>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-purple-50 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 border border-purple-200/80 dark:border-purple-800">
                                ${escapeHtml(MODULE_DISPLAY_NAMES[rule.module] || rule.module)}
                            </span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold ${
                                rule.rule_type === 'criteria_based'
                                    ? 'bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200/80 dark:border-blue-800'
                                    : 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800'
                            }">
                                ${rule.rule_type === 'criteria_based' ? 'Criteria-Based' : 'Ownership-Based'}
                            </span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold ${
                                rule.access_level === 'read_write'
                                    ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800'
                                    : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700'
                            }">
                                ${rule.access_level === 'read_write' ? 'Read / Write Access' : 'Read-Only Access'}
                            </span>
                        </div>

                        ${rule.description ? `
                            <p class="text-xs text-zinc-500 dark:text-zinc-400 line-clamp-2">
                                ${escapeHtml(rule.description)}
                            </p>
                        ` : ''}

                        <!-- Conditions Pills / Summary -->
                        <div class="flex items-center gap-1.5 flex-wrap mt-1 text-xs">
                            <span class="text-[11px] font-semibold text-zinc-500 dark:text-zinc-400">
                                When:
                            </span>

                            ${rule.rule_type === 'criteria_based' && Array.isArray(rule.criteria) && rule.criteria.length > 0 ? (
                                rule.criteria.map(c => `
                                    <span class="text-[11px] font-medium bg-zinc-100 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 border border-zinc-200 dark:border-zinc-700 px-2 py-0.5 rounded-md">
                                        <strong class="font-semibold">${escapeHtml(c.field)}</strong> ${(c.operator || '').replace(/_/g, ' ')} ${c.value ? `"${escapeHtml(c.value)}"` : ''}
                                    </span>
                                `).join('')
                            ) : rule.rule_type === 'ownership_based' ? `
                                <span class="text-[11px] font-medium bg-amber-50 dark:bg-amber-950/50 text-amber-800 dark:text-amber-200 border border-amber-200 dark:border-amber-800 px-2 py-0.5 rounded-md">
                                    Owner in ${escapeHtml(rule.ownership_role_name || rule.ownership_profile_name || 'Designated Group')}
                                </span>
                            ` : `
                                <span class="text-[11px] text-zinc-400 italic">All records</span>
                            `}

                            <i data-lucide="arrow-right" class="w-3.5 h-3.5 text-zinc-400 mx-0.5"></i>

                            <span class="text-[11px] font-semibold text-zinc-500 dark:text-zinc-400">
                                Share With:
                            </span>
                            <span class="inline-flex items-center gap-1 text-[11px] font-bold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200/80 dark:border-indigo-800 px-2 py-0.5 rounded-md">
                                <i data-lucide="${rule.share_with_type === 'profile' ? 'lock' : 'user-check'}" class="w-3 h-3"></i>
                                <span>${escapeHtml(targetName)} (${escapeHtml((rule.share_with_type || '').toUpperCase())})</span>
                            </span>
                        </div>
                    </div>

                    <!-- Right: Metrics & Actions -->
                    <div class="flex items-center gap-3 shrink-0 self-end md:self-center pt-2 md:pt-0 border-t md:border-t-0 border-zinc-100 dark:border-zinc-800 w-full md:w-auto justify-end">
                        <!-- Evaluated Shares Count -->
                        <div class="text-right mr-1">
                            <div class="text-sm font-extrabold text-zinc-900 dark:text-zinc-100">
                                ${rule.shared_records_count || 0}
                            </div>
                            <div class="text-[10px] text-zinc-500 dark:text-zinc-400 uppercase tracking-wider font-semibold">
                                Shared Records
                            </div>
                        </div>

                        <!-- Active / Disabled Switch -->
                        <button
                            type="button"
                            onclick="window.toggleSharingRuleActive('${rule.id}')"
                            class="px-2.5 py-1 rounded-md text-[11px] font-bold border transition-colors cursor-pointer ${
                                rule.is_active
                                    ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800'
                                    : 'bg-zinc-100 text-zinc-500 border-zinc-200 dark:bg-zinc-800 dark:text-zinc-400 dark:border-zinc-700'
                            }"
                            title="${rule.is_active ? 'Click to deactivate rule' : 'Click to activate rule'}"
                        >
                            ${rule.is_active ? 'Active' : 'Disabled'}
                        </button>

                        <!-- Recalculate Button -->
                        <button
                            type="button"
                            onclick="window.recalculateSharingRule('${rule.id}')"
                            ${isRecalculating ? 'disabled' : ''}
                            class="w-8 h-8 rounded-lg flex items-center justify-center text-zinc-500 hover:text-zinc-900 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:text-zinc-200 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
                            title="Force Recalculate Shares"
                        >
                            <i data-lucide="refresh-cw" class="w-3.5 h-3.5 ${isRecalculating ? 'animate-spin text-indigo-600' : ''}"></i>
                        </button>

                        <!-- Edit Button -->
                        <button
                            type="button"
                            onclick="window.openEditSharingRuleModal('${rule.id}')"
                            class="w-8 h-8 rounded-lg flex items-center justify-center text-zinc-500 hover:text-indigo-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:text-indigo-400 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
                            title="Edit Rule"
                        >
                            <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                        </button>

                        <!-- Delete Button -->
                        <button
                            type="button"
                            onclick="window.deleteSharingRule('${rule.id}')"
                            class="w-8 h-8 rounded-lg flex items-center justify-center text-zinc-500 hover:text-rose-600 hover:bg-rose-50 dark:text-zinc-400 dark:hover:text-rose-400 dark:hover:bg-rose-950/50 transition-colors cursor-pointer"
                            title="Delete Rule"
                        >
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                </div>
            `;
        });

        html += '</div>';
        container.innerHTML = html;
        if (window.lucide) lucide.createIcons();
    }

    function renderModal() {
        let modalEl = document.getElementById('srStudioModal');
        if (!state.isModalOpen) {
            if (modalEl) modalEl.remove();
            return;
        }

        if (!modalEl) {
            modalEl = document.createElement('div');
            modalEl.id = 'srStudioModal';
            document.body.appendChild(modalEl);
        }

        modalEl.className = 'fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-950/70 backdrop-blur-xs animate-fade-in';
        modalEl.onclick = (e) => {
            if (e.target === modalEl) window.closeSharingRuleModal();
        };

        const availableFields = state.meta?.modules?.[state.formModule] || [];
        const isEditing = Boolean(state.editingRule);

        modalEl.innerHTML = `
            <div
                class="relative w-full max-w-2xl max-h-[92vh] bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl shadow-2xl flex flex-col overflow-hidden text-zinc-900 dark:text-zinc-100"
                onclick="event.stopPropagation()"
            >
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/80 dark:bg-zinc-800/50 flex items-center justify-between shrink-0">
                    <div>
                        <div class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">
                            Record Security Configuration
                        </div>
                        <h3 class="text-base font-bold text-zinc-950 dark:text-zinc-100 mt-0.5">
                            ${isEditing ? `Edit Sharing Rule: ${escapeHtml(state.editingRule.name)}` : 'Create Conditional Sharing Rule'}
                        </h3>
                    </div>
                    <button
                        type="button"
                        onclick="window.closeSharingRuleModal()"
                        class="w-8 h-8 rounded-full flex items-center justify-center text-zinc-400 hover:text-zinc-700 hover:bg-zinc-200/60 dark:hover:text-zinc-200 dark:hover:bg-zinc-700 transition-colors"
                    >
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <!-- Modal Body Form -->
                <form id="srModalForm" onsubmit="window.saveSharingRule(event)" class="flex flex-col flex-1 min-h-0">
                    <div class="p-6 overflow-y-auto flex-1 flex flex-col gap-5">
                        ${state.formError ? `
                            <div class="p-3 bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800 rounded-xl text-xs text-rose-700 dark:text-rose-300 flex items-center gap-2">
                                <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                                <span>${escapeHtml(state.formError)}</span>
                            </div>
                        ` : ''}

                        <!-- Section 1: Basic Information -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">Rule Name *</label>
                                <input
                                    type="text"
                                    id="srFormName"
                                    class="w-full h-9 px-3 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-xs text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-2xs"
                                    placeholder="e.g. Enterprise Deals Shared with Legal"
                                    value="${escapeHtml(state.formName)}"
                                    required
                                />
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">Target CRM Module *</label>
                                <select
                                    id="srFormModule"
                                    onchange="window.handleFormModuleChange(this.value)"
                                    ${isEditing ? 'disabled' : ''}
                                    class="w-full h-9 px-3 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-xs text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-2xs ${isEditing ? 'opacity-65 cursor-not-allowed' : ''}"
                                >
                                    ${Object.entries(MODULE_DISPLAY_NAMES).map(([k, label]) => `
                                        <option value="${k}" ${state.formModule === k ? 'selected' : ''}>${escapeHtml(label)}</option>
                                    `).join('')}
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">Operational Description</label>
                            <input
                                type="text"
                                id="srFormDescription"
                                class="w-full h-9 px-3 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-xs text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-2xs"
                                placeholder="e.g. Shares all high value enterprise opportunities with cross-functional legal and solution architects"
                                value="${escapeHtml(state.formDescription)}"
                            />
                        </div>

                        <!-- Section 2: Rule Type -->
                        <div class="border border-zinc-200 dark:border-zinc-800 rounded-xl p-4 bg-zinc-50/50 dark:bg-zinc-800/30">
                            <label class="block text-xs font-bold text-zinc-900 dark:text-zinc-100 mb-2.5">
                                Rule Evaluation Type
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <label class="flex items-start gap-2.5 p-3 rounded-xl border cursor-pointer transition-all ${
                                    state.formRuleType === 'criteria_based'
                                        ? 'bg-indigo-50/70 border-indigo-500 dark:bg-indigo-950/40 dark:border-indigo-600'
                                        : 'bg-white border-zinc-200 dark:bg-zinc-800 dark:border-zinc-700'
                                }">
                                    <input
                                        type="radio"
                                        name="rule_type"
                                        value="criteria_based"
                                        ${state.formRuleType === 'criteria_based' ? 'checked' : ''}
                                        onchange="window.setFormRuleType('criteria_based')"
                                        class="mt-0.5 text-indigo-600 focus:ring-indigo-500"
                                    />
                                    <div>
                                        <div class="text-xs font-bold text-zinc-900 dark:text-zinc-100">Criteria-Based</div>
                                        <div class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5">
                                            Evaluated against field attributes (e.g. Amount &ge; $1M or Stage = Negotiation)
                                        </div>
                                    </div>
                                </label>

                                <label class="flex items-start gap-2.5 p-3 rounded-xl border cursor-pointer transition-all ${
                                    state.formRuleType === 'ownership_based'
                                        ? 'bg-indigo-50/70 border-indigo-500 dark:bg-indigo-950/40 dark:border-indigo-600'
                                        : 'bg-white border-zinc-200 dark:bg-zinc-800 dark:border-zinc-700'
                                }">
                                    <input
                                        type="radio"
                                        name="rule_type"
                                        value="ownership_based"
                                        ${state.formRuleType === 'ownership_based' ? 'checked' : ''}
                                        onchange="window.setFormRuleType('ownership_based')"
                                        class="mt-0.5 text-indigo-600 focus:ring-indigo-500"
                                    />
                                    <div>
                                        <div class="text-xs font-bold text-zinc-900 dark:text-zinc-100">Ownership-Based</div>
                                        <div class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5">
                                            Shared based on the record owner's Role or Security Profile
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Section 3a: Criteria Builder (if Criteria-Based) -->
                        ${state.formRuleType === 'criteria_based' ? `
                            <div class="border border-zinc-200 dark:border-zinc-800 rounded-xl p-4 bg-white dark:bg-zinc-800/40">
                                <div class="flex items-center justify-between mb-3">
                                    <div>
                                        <div class="text-xs font-bold text-zinc-900 dark:text-zinc-100">
                                            Field Criteria Conditions (ALL Must Match)
                                        </div>
                                        <div class="text-[11px] text-zinc-500 dark:text-zinc-400">
                                            Define the field parameters required to trigger automatic record sharing.
                                        </div>
                                    </div>
                                    <button
                                        type="button"
                                        onclick="window.addCriteriaCondition()"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-zinc-100 dark:bg-zinc-700 hover:bg-zinc-200 dark:hover:bg-zinc-600 text-zinc-700 dark:text-zinc-200 transition-colors"
                                    >
                                        + Add Condition
                                    </button>
                                </div>

                                <div class="flex flex-col gap-2">
                                    ${state.formCriteria.map((cond, idx) => `
                                        <div class="grid grid-cols-[1.2fr_1.2fr_1.4fr_32px] gap-2 items-center bg-zinc-50 dark:bg-zinc-800/70 p-2 rounded-lg border border-zinc-200/80 dark:border-zinc-700">
                                            <!-- Field Selector -->
                                            <select
                                                class="w-full h-8 px-2 rounded-md border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                                onchange="window.updateCriteriaCondition(${idx}, 'field', this.value)"
                                            >
                                                ${availableFields.map(f => `
                                                    <option value="${f.key}" ${cond.field === f.key ? 'selected' : ''}>${escapeHtml(f.label)}</option>
                                                `).join('')}
                                            </select>

                                            <!-- Operator -->
                                            <select
                                                class="w-full h-8 px-2 rounded-md border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                                onchange="window.updateCriteriaCondition(${idx}, 'operator', this.value)"
                                            >
                                                ${Object.entries(OPERATOR_LABELS).map(([op, label]) => `
                                                    <option value="${op}" ${cond.operator === op ? 'selected' : ''}>${escapeHtml(label)}</option>
                                                `).join('')}
                                            </select>

                                            <!-- Value Input -->
                                            ${cond.operator !== 'is_set' && cond.operator !== 'is_empty' ? `
                                                <input
                                                    type="text"
                                                    class="w-full h-8 px-2 rounded-md border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                                    placeholder="Value..."
                                                    value="${escapeHtml(cond.value || '')}"
                                                    oninput="window.updateCriteriaCondition(${idx}, 'value', this.value)"
                                                />
                                            ` : `
                                                <div class="text-[11px] text-zinc-400 italic px-2">
                                                    (No value needed)
                                                </div>
                                            `}

                                            <!-- Remove button -->
                                            <button
                                                type="button"
                                                onclick="window.removeCriteriaCondition(${idx})"
                                                class="w-7 h-7 rounded-md flex items-center justify-center text-zinc-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors"
                                                title="Remove Condition"
                                            >
                                                <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                            </button>
                                        </div>
                                    `).join('')}
                                </div>
                            </div>
                        ` : `
                            <!-- Section 3b: Ownership Selector -->
                            <div class="border border-zinc-200 dark:border-zinc-800 rounded-xl p-4 bg-white dark:bg-zinc-800/40">
                                <div class="text-xs font-bold text-zinc-900 dark:text-zinc-100 mb-3">
                                    Records Owned By Members Of:
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[11px] font-semibold text-zinc-600 dark:text-zinc-400 mb-1">Owner's Security Profile</label>
                                        <select
                                            id="srFormOwnershipProfile"
                                            onchange="state.formOwnershipProfileId = this.value"
                                            class="w-full h-8 px-2.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-xs text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                        >
                                            <option value="">-- Any Profile --</option>
                                            ${(state.meta?.profiles || []).map(p => `
                                                <option value="${p.id}" ${state.formOwnershipProfileId === p.id ? 'selected' : ''}>${escapeHtml(p.name)}</option>
                                            `).join('')}
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-semibold text-zinc-600 dark:text-zinc-400 mb-1">Owner's Hierarchy Role</label>
                                        <select
                                            id="srFormOwnershipRole"
                                            onchange="state.formOwnershipRoleId = this.value"
                                            class="w-full h-8 px-2.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-xs text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                        >
                                            <option value="">-- Any Role --</option>
                                            ${(state.meta?.roles || []).map(r => `
                                                <option value="${r.id}" ${state.formOwnershipRoleId === r.id ? 'selected' : ''}>${escapeHtml(r.name)}</option>
                                            `).join('')}
                                        </select>
                                    </div>
                                </div>
                            </div>
                        `}

                        <!-- Section 4: Share With & Access Level -->
                        <div class="border border-zinc-200 dark:border-zinc-800 rounded-xl p-4 bg-white dark:bg-zinc-800/40">
                            <div class="text-xs font-bold text-zinc-900 dark:text-zinc-100 mb-3">
                                Grant Record Access To:
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-[11px] font-semibold text-zinc-600 dark:text-zinc-400 mb-1">Recipient Type *</label>
                                    <select
                                        id="srFormShareWithType"
                                        onchange="window.handleFormShareWithTypeChange(this.value)"
                                        class="w-full h-8 px-2.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-xs text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                    >
                                        <option value="profile" ${state.formShareWithType === 'profile' ? 'selected' : ''}>Security Profile</option>
                                        <option value="role" ${state.formShareWithType === 'role' ? 'selected' : ''}>User Hierarchy Role</option>
                                        <option value="user" ${state.formShareWithType === 'user' ? 'selected' : ''}>Specific User</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-semibold text-zinc-600 dark:text-zinc-400 mb-1">Target Recipient *</label>
                                    <select
                                        id="srFormShareWithId"
                                        onchange="state.formShareWithId = this.value"
                                        class="w-full h-8 px-2.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-xs text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                    >
                                        ${state.formShareWithType === 'profile' ? (
                                            (state.meta?.profiles || []).map(p => `
                                                <option value="${p.id}" ${state.formShareWithId === p.id ? 'selected' : ''}>${escapeHtml(p.name)}</option>
                                            `).join('')
                                        ) : state.formShareWithType === 'role' ? (
                                            (state.meta?.roles || []).map(r => `
                                                <option value="${r.id}" ${state.formShareWithId === r.id ? 'selected' : ''}>${escapeHtml(r.name)}</option>
                                            `).join('')
                                        ) : (
                                            (state.meta?.users || []).map(u => `
                                                <option value="${u.id}" ${state.formShareWithId === u.id ? 'selected' : ''}>${escapeHtml(u.full_name || u.name)} (${escapeHtml(u.email)})</option>
                                            `).join('')
                                        )}
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-semibold text-zinc-600 dark:text-zinc-400 mb-1">Access Level *</label>
                                    <select
                                        id="srFormAccessLevel"
                                        onchange="state.formAccessLevel = this.value"
                                        class="w-full h-8 px-2.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-xs text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                    >
                                        <option value="read" ${state.formAccessLevel === 'read' ? 'selected' : ''}>Read-Only</option>
                                        <option value="read_write" ${state.formAccessLevel === 'read_write' ? 'selected' : ''}>Read / Write</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Section 5: Active Switch -->
                        <div class="flex items-center gap-2.5 pt-1">
                            <input
                                type="checkbox"
                                id="srFormIsActive"
                                ${state.formIsActive ? 'checked' : ''}
                                onchange="state.formIsActive = this.checked"
                                class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                            />
                            <label for="srFormIsActive" class="text-xs font-semibold text-zinc-800 dark:text-zinc-200 cursor-pointer select-none">
                                Rule is Active (Immediately evaluates and shares matching records)
                            </label>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="px-6 py-3.5 border-t border-zinc-200 dark:border-zinc-800 bg-zinc-50/80 dark:bg-zinc-800/50 flex items-center justify-end gap-2.5 shrink-0">
                        <button
                            type="button"
                            onclick="window.closeSharingRuleModal()"
                            class="px-4 py-2 rounded-xl text-xs font-semibold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-200/60 dark:hover:bg-zinc-700 transition-colors"
                            ${state.formSaving ? 'disabled' : ''}
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            ${state.formSaving ? 'disabled' : ''}
                            class="inline-flex items-center gap-2 px-5 py-2 rounded-xl text-xs font-semibold bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm shadow-indigo-600/30 transition-all ${
                                state.formSaving ? 'opacity-70 cursor-not-allowed' : ''
                            }"
                        >
                            <i data-lucide="${state.formSaving ? 'refresh-cw' : 'check'}" class="w-3.5 h-3.5 ${state.formSaving ? 'animate-spin' : ''}"></i>
                            <span>${state.formSaving ? 'Evaluating...' : isEditing ? 'Update & Recalculate' : 'Create & Evaluate'}</span>
                        </button>
                    </div>
                </form>
            </div>
        `;

        if (window.lucide) lucide.createIcons();
    }

    // Window Public Handlers
    window.selectSrModule = function (mod) {
        state.selectedModule = mod;
        renderRulesList();
    };

    window.handleSrSearchInput = function (val) {
        state.searchFilter = val;
        renderRulesList();
    };

    window.openCreateSharingRuleModal = function () {
        state.editingRule = null;
        state.formName = '';
        state.formDescription = '';
        state.formModule = state.selectedModule !== 'all' ? state.selectedModule : 'opportunities';
        state.formRuleType = 'criteria_based';

        const defaultField = state.meta?.modules?.[state.formModule]?.[0]?.key || 'amount';
        state.formCriteria = [{ field: defaultField, operator: 'greater_or_equal', value: '1000000' }];

        state.formOwnershipRoleId = '';
        state.formOwnershipProfileId = '';
        state.formShareWithType = 'profile';
        state.formShareWithId = state.meta?.profiles?.[0]?.id || '';
        state.formAccessLevel = 'read';
        state.formIsActive = true;
        state.formError = null;
        state.isModalOpen = true;

        renderModal();
    };

    window.openEditSharingRuleModal = function (ruleId) {
        const rule = state.rules.find(r => r.id === ruleId);
        if (!rule) return;

        state.editingRule = rule;
        state.formName = rule.name;
        state.formDescription = rule.description || '';
        state.formModule = rule.module;
        state.formRuleType = rule.rule_type;
        state.formCriteria = Array.isArray(rule.criteria) && rule.criteria.length > 0
            ? JSON.parse(JSON.stringify(rule.criteria))
            : [{ field: 'amount', operator: 'greater_or_equal', value: '1000000' }];
        state.formOwnershipRoleId = rule.ownership_role_id || '';
        state.formOwnershipProfileId = rule.ownership_profile_id || '';
        state.formShareWithType = rule.share_with_type;
        state.formShareWithId = rule.share_with_id;
        state.formAccessLevel = rule.access_level;
        state.formIsActive = Boolean(rule.is_active);
        state.formError = null;
        state.isModalOpen = true;

        renderModal();
    };

    window.closeSharingRuleModal = function () {
        state.isModalOpen = false;
        renderModal();
    };

    window.setFormRuleType = function (type) {
        state.formRuleType = type;
        renderModal();
    };

    window.handleFormModuleChange = function (newMod) {
        state.formModule = newMod;
        const defaultField = state.meta?.modules?.[newMod]?.[0]?.key || 'name';
        state.formCriteria = [{ field: defaultField, operator: 'equals', value: '' }];
        renderModal();
    };

    window.handleFormShareWithTypeChange = function (newType) {
        state.formShareWithType = newType;
        if (newType === 'profile') state.formShareWithId = state.meta?.profiles?.[0]?.id || '';
        else if (newType === 'role') state.formShareWithId = state.meta?.roles?.[0]?.id || '';
        else if (newType === 'user') state.formShareWithId = state.meta?.users?.[0]?.id || '';
        renderModal();
    };

    window.addCriteriaCondition = function () {
        const defaultField = state.meta?.modules?.[state.formModule]?.[0]?.key || 'name';
        state.formCriteria.push({ field: defaultField, operator: 'equals', value: '' });
        renderModal();
    };

    window.removeCriteriaCondition = function (index) {
        if (state.formCriteria.length <= 1) {
            alert('You must have at least one criteria condition.');
            return;
        }
        state.formCriteria.splice(index, 1);
        renderModal();
    };

    window.updateCriteriaCondition = function (index, key, val) {
        if (state.formCriteria[index]) {
            state.formCriteria[index][key] = val;
            if (key === 'operator') {
                renderModal();
            }
        }
    };

    window.saveSharingRule = async function (e) {
        e.preventDefault();
        const nameInput = document.getElementById('srFormName');
        const descInput = document.getElementById('srFormDescription');

        state.formName = nameInput ? nameInput.value.trim() : state.formName;
        state.formDescription = descInput ? descInput.value.trim() : state.formDescription;

        if (!state.formName) {
            state.formError = 'Rule name is required.';
            renderModal();
            return;
        }
        if (!state.formShareWithId) {
            state.formError = 'Target recipient is required.';
            renderModal();
            return;
        }

        state.formSaving = true;
        state.formError = null;
        renderModal();

        const payload = {
            name: state.formName,
            description: state.formDescription,
            module: state.formModule,
            rule_type: state.formRuleType,
            criteria: state.formRuleType === 'criteria_based' ? state.formCriteria : [],
            ownership_role_id: state.formRuleType === 'ownership_based' && state.formOwnershipRoleId ? state.formOwnershipRoleId : null,
            ownership_profile_id: state.formRuleType === 'ownership_based' && state.formOwnershipProfileId ? state.formOwnershipProfileId : null,
            share_with_type: state.formShareWithType,
            share_with_id: state.formShareWithId,
            access_level: state.formAccessLevel,
            is_active: state.formIsActive ? 1 : 0
        };

        const csrfToken = window.INITIAL_SHARING_RULES_DATA?.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content;

        try {
            let res;
            if (state.editingRule) {
                res = await fetch(`/api/sharing-rules/${state.editingRule.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(payload)
                });
            } else {
                res = await fetch('/api/sharing-rules', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(payload)
                });
            }

            const json = await res.json();
            if (!res.ok || json.success === false) {
                throw new Error(json.error || json.message || 'Failed to save sharing rule.');
            }

            showToast(state.editingRule ? `Rule "${state.formName}" updated & evaluated successfully!` : `Sharing Rule "${state.formName}" created & evaluated!`);
            state.isModalOpen = false;
            await fetchRules();
            renderRulesList();
        } catch (err) {
            state.formError = err.message || 'Failed to save sharing rule.';
            renderModal();
        } finally {
            state.formSaving = false;
            renderModal();
        }
    };

    window.toggleSharingRuleActive = async function (ruleId) {
        const rule = state.rules.find(r => r.id === ruleId);
        if (!rule) return;

        const nextState = !rule.is_active;
        const csrfToken = window.INITIAL_SHARING_RULES_DATA?.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content;

        try {
            const res = await fetch(`/api/sharing-rules/${ruleId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ is_active: nextState ? 1 : 0 })
            });

            const json = await res.json();
            if (!res.ok || json.success === false) {
                throw new Error(json.error || 'Failed to toggle rule state.');
            }

            rule.is_active = nextState;
            renderRulesList();
            showToast(`Rule "${rule.name}" is now ${nextState ? 'Active' : 'Disabled'}.`);
        } catch (e) {
            alert(`Error updating rule status: ${e.message}`);
        }
    };

    window.recalculateSharingRule = async function (ruleId) {
        state.recalculatingId = ruleId;
        renderRulesList();

        const csrfToken = window.INITIAL_SHARING_RULES_DATA?.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content;

        try {
            const res = await fetch(`/api/sharing-rules/${ruleId}/recalculate`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            const json = await res.json();
            if (!res.ok || json.success === false) {
                throw new Error(json.error || 'Recalculation failed.');
            }

            showToast(json.message || 'Recalculation complete.');
            await fetchRules();
            renderRulesList();
        } catch (e) {
            alert(`Recalculation error: ${e.message}`);
        } finally {
            state.recalculatingId = null;
            renderRulesList();
        }
    };

    window.deleteSharingRule = async function (ruleId) {
        const rule = state.rules.find(r => r.id === ruleId);
        if (!rule) return;

        if (!window.confirm(`Are you sure you want to delete rule "${rule.name}"? All associated record access grants will be revoked.`)) {
            return;
        }

        const csrfToken = window.INITIAL_SHARING_RULES_DATA?.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content;

        try {
            const res = await fetch(`/api/sharing-rules/${ruleId}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            const json = await res.json();
            if (!res.ok || json.success === false) {
                throw new Error(json.error || 'Failed to delete rule.');
            }

            state.rules = state.rules.filter(r => r.id !== ruleId);
            renderRulesList();
            showToast(`Rule "${rule.name}" deleted.`);
        } catch (e) {
            alert(`Error deleting rule: ${e.message}`);
        }
    };

    // Public Initializer
    window.initSharingRules = function () {
        const container = document.getElementById('srStudioRoot');
        if (!container) return;

        if (window.INITIAL_SHARING_RULES_DATA) {
            if (Array.isArray(window.INITIAL_SHARING_RULES_DATA.rules)) {
                state.rules = window.INITIAL_SHARING_RULES_DATA.rules;
            }
            if (window.INITIAL_SHARING_RULES_DATA.meta) {
                state.meta = window.INITIAL_SHARING_RULES_DATA.meta;
            }
        }

        renderRulesList();

        // Refresh async in background if needed
        if (!state.meta) {
            fetchMeta().then(() => {
                if (state.isModalOpen) renderModal();
            });
        }
        if (!state.rules || state.rules.length === 0) {
            fetchRules().then(() => {
                renderRulesList();
            });
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', window.initSharingRules);
    } else {
        window.initSharingRules();
    }
})();
