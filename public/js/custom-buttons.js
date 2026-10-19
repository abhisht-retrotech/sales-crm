/**
 * Custom Buttons & Actions Studio — DealSync Client Controller
 * 1-to-1 Parity with React CustomButtonsStudio.tsx
 * Pure Tailwind CSS styling with zero ad-hoc inline styles.
 */

(function () {
    'use strict';

    // Module definitions
    const MODULE_OPTIONS = [
        { id: 'leads', name: 'Leads' },
        { id: 'accounts', name: 'Accounts' },
        { id: 'contacts', name: 'Contacts' },
        { id: 'opportunities', name: 'Opportunities' },
        { id: 'purchase_deals', name: 'Purchase Deals' },
        { id: 'shipping', name: 'Shipping & Freight' },
        { id: 'payments', name: 'Invoices & Payments' },
        { id: 'wires', name: 'Wire Reconciliation' }
    ];

    // Icon definitions with Lucide icon keys
    const AVAILABLE_ICONS = [
        { id: 'Mail', label: 'Mail / Envelope', lucide: 'mail' },
        { id: 'Box', label: 'Cargo Box (Wt. & Dims)', lucide: 'box' },
        { id: 'Package', label: 'Package / Carton', lucide: 'package' },
        { id: 'Scale', label: 'Scale / Weight', lucide: 'scale' },
        { id: 'ExternalLink', label: 'External Link', lucide: 'external-link' },
        { id: 'Globe', label: 'Web Globe', lucide: 'globe' },
        { id: 'Zap', label: 'Zap / Action', lucide: 'zap' },
        { id: 'Send', label: 'Send Paper Plane', lucide: 'send' },
        { id: 'FileText', label: 'Document', lucide: 'file-text' },
        { id: 'CheckCircle', label: 'Checkmark', lucide: 'check-circle' },
        { id: 'UserCheck', label: 'User Verify', lucide: 'user-check' },
        { id: 'Phone', label: 'Phone', lucide: 'phone' },
        { id: 'Share2', label: 'Share', lucide: 'share-2' },
        { id: 'Calendar', label: 'Calendar', lucide: 'calendar' },
        { id: 'Download', label: 'Download', lucide: 'download' },
        { id: 'Layers', label: 'In-App Quick Action', lucide: 'layers' }
    ];

    // Style variants with exact Tailwind CSS classes
    const STYLE_VARIANTS = [
        { id: 'primary', label: 'Primary (Accent Blue)', btnClass: 'bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white border border-blue-700 shadow-xs' },
        { id: 'secondary', label: 'Secondary (Subtle Slate)', btnClass: 'bg-slate-100 hover:bg-slate-200 active:bg-slate-300 text-slate-800 border border-slate-300 dark:bg-zinc-800 dark:hover:bg-zinc-700 dark:text-zinc-200 dark:border-zinc-700 shadow-xs' },
        { id: 'success', label: 'Success (Emerald Green)', btnClass: 'bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white border border-emerald-700 shadow-xs' },
        { id: 'indigo', label: 'Indigo (Royal Purple)', btnClass: 'bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white border border-indigo-700 shadow-xs' },
        { id: 'purple', label: 'Violet (Amethyst)', btnClass: 'bg-purple-600 hover:bg-purple-700 active:bg-purple-800 text-white border border-purple-700 shadow-xs' },
        { id: 'amber', label: 'Amber (Warm Gold)', btnClass: 'bg-amber-600 hover:bg-amber-700 active:bg-amber-800 text-white border border-amber-700 shadow-xs' },
        { id: 'danger', label: 'Danger (Crimson Red)', btnClass: 'bg-red-600 hover:bg-red-700 active:bg-red-800 text-white border border-red-700 shadow-xs' }
    ];

    const MERGE_TAGS_MAP = {
        leads: ['id', 'first_name', 'last_name', 'company', 'email', 'phone', 'status', 'assigned_to'],
        accounts: ['id', 'name', 'type', 'industry', 'website', 'phone', 'payment_terms', 'assigned_to'],
        contacts: ['id', 'first_name', 'last_name', 'account_id', 'email', 'phone', 'title'],
        opportunities: ['id', 'name', 'amount', 'stage', 'expected_close_date'],
        purchase_deals: ['id', 'po_number', 'title', 'amount', 'stage', 'expected_delivery_date'],
        shipping: ['id', 'tracking_number', 'carrier', 'status', 'shipping_origin'],
        payments: ['id', 'invoice_number', 'amount', 'due_date', 'status'],
        wires: ['id', 'utr_reference', 'remitter_name', 'amount', 'sending_bank']
    };

    // State
    let buttons = [];
    let templates = [];
    let notificationRules = [];
    let customObjects = [];
    let processFlows = [];
    let selectedEntity = 'all';
    let searchQuery = '';
    let viewMode = 'list'; // 'list' or 'grid'

    // Modal & Form State
    let isModalOpen = false;
    let editingButton = null;
    let formEntity = 'leads';
    let formLabel = '';
    let formName = '';
    let formIcon = 'Mail';
    let formStyle = 'indigo';
    let formPlacement = 'record_header';
    let formIsActive = true;
    let formActionType = 'send_email';
    let actionTemplateId = '';
    let actionExecMode = 'composer';
    let actionRecipientField = 'email';
    let actionUrlTemplate = 'https://example.com/portal?id={{id}}';
    let actionOpenTarget = '_blank';
    let actionTriggerType = 'field_update';
    let actionUpdateField = 'status';
    let actionUpdateValue = 'Qualified';
    let actionWebhookUrl = '';
    let actionSuccessMsg = 'Action completed successfully.';
    let actionQuickActionComponent = 'add_weight_dims';
    let visibilityConditions = [];
    let availableEntityFields = [];
    let isSaving = false;
    let feedbackTimer = null;

    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    function getLucideIconName(name) {
        const item = AVAILABLE_ICONS.find(i => i.id === name || i.lucide === name?.toLowerCase());
        return item ? item.lucide : 'mail';
    }

    function getStyleObj(styleVariant) {
        return STYLE_VARIANTS.find(s => s.id === styleVariant) || STYLE_VARIANTS[1];
    }

    function getModuleName(entityType) {
        const standard = MODULE_OPTIONS.find(m => m.id === entityType);
        if (standard) return standard.name;
        const custom = customObjects.find(co => co.api_name === entityType);
        if (custom) return custom.label_plural || custom.label_singular || entityType;
        return entityType;
    }

    function showFeedback(message) {
        const banner = document.getElementById('cbFeedbackBanner');
        const text = document.getElementById('cbFeedbackText');
        if (!banner || !text) return;

        text.textContent = message;
        banner.classList.remove('hidden');
        banner.classList.add('flex');

        if (feedbackTimer) clearTimeout(feedbackTimer);
        feedbackTimer = setTimeout(() => {
            banner.classList.add('hidden');
            banner.classList.remove('flex');
        }, 3500);
    }

    function getActionDetail(btn) {
        const cfg = btn.action_config || {};
        if (btn.action_type === 'send_email') {
            const tmpl = templates.find(t => String(t.id) === String(cfg.template_id));
            return tmpl ? `Template: ${tmpl.name}` : 'Send Email Template';
        } else if (btn.action_type === 'url_redirect') {
            return cfg.url_template || 'Redirect URL';
        } else if (btn.action_type === 'workflow_trigger') {
            if (cfg.trigger_type === 'webhook') {
                return cfg.webhook_url ? `Webhook: ${cfg.webhook_url}` : 'Webhook Execution';
            }
            if (cfg.field_updates) {
                const keys = Object.keys(cfg.field_updates);
                if (keys.length > 0) {
                    return `Update ${keys[0]} &rarr; "${cfg.field_updates[keys[0]]}"`;
                }
            }
            return 'Field Update Action';
        } else if (btn.action_type === 'quick_action' || btn.action_type === 'add_weight_dims') {
            const comp = cfg.component || 'add_weight_dims';
            if (comp.startsWith('process_flow:')) {
                const flowId = comp.replace('process_flow:', '');
                const f = processFlows.find(p => String(p.id) === String(flowId));
                return f ? `Process: ${f.name}` : `Process Flow (${flowId})`;
            }
            if (comp === 'add_weight_dims') {
                return 'Weight & Dimensions Specification Studio';
            }
            return `In-App Action: ${comp}`;
        }
        return 'Custom Action';
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

    function getFilteredButtons() {
        return buttons.filter(b => {
            if (selectedEntity !== 'all' && b.entity_type !== selectedEntity) return false;
            if (searchQuery.trim()) {
                const q = searchQuery.toLowerCase();
                const label = (b.label || '').toLowerCase();
                const name = (b.name || '').toLowerCase();
                return label.includes(q) || name.includes(q);
            }
            return true;
        });
    }

    // =========================================================================
    // RENDERING FUNCTIONS
    // =========================================================================

    function renderMain() {
        const container = document.getElementById('cbMainContent');
        if (!container) return;

        const filtered = getFilteredButtons();

        // Update count badge
        const countBadge = document.getElementById('cbButtonCount');
        if (countBadge) {
            countBadge.textContent = `${filtered.length} button${filtered.length !== 1 ? 's' : ''} configured`;
        }

        // Empty state
        if (filtered.length === 0) {
            container.innerHTML = `
                <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-12 text-center shadow-xs">
                    <div class="w-12 h-12 mx-auto rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-400 dark:text-zinc-500 flex items-center justify-center mb-3">
                        <i data-lucide="mouse-pointer-click" class="w-6 h-6 stroke-[1.8]"></i>
                    </div>
                    <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 mb-1">
                        No Custom Buttons Configured
                    </h3>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 max-w-md mx-auto mb-5 leading-relaxed">
                        Create custom buttons that appear directly on record headers to trigger emails, web links, or workflow automation.
                    </p>
                    <button
                        type="button"
                        onclick="window.handleOpenCreateButtonModal && window.handleOpenCreateButtonModal()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold shadow-xs transition-colors cursor-pointer"
                    >
                        <i data-lucide="plus" class="w-3.5 h-3.5 stroke-[2.2]"></i>
                        <span>Create First Button</span>
                    </button>
                </div>
            `;
            if (window.lucide) lucide.createIcons();
            return;
        }

        if (viewMode === 'list') {
            renderListView(filtered, container);
        } else {
            renderGridView(filtered, container);
        }

        if (window.lucide) {
            lucide.createIcons();
        }
    }

    function renderListView(filtered, container) {
        let rowsHtml = '';
        filtered.forEach((btn, idx) => {
            const moduleName = getModuleName(btn.entity_type);
            const styleObj = getStyleObj(btn.style_variant);
            const iconLucide = getLucideIconName(btn.icon);
            const actionDetail = getActionDetail(btn);

            let conditions = [];
            if (Array.isArray(btn.visibility_conditions)) {
                conditions = btn.visibility_conditions;
            } else if (typeof btn.visibility_conditions === 'string') {
                try { conditions = JSON.parse(btn.visibility_conditions); } catch (e) {}
            }

            const conditionTooltip = conditions.length > 0
                ? `Visible when: ${conditions.map(c => `${c.field} ${c.operator} "${c.value}"`).join(', ')}`
                : 'Always visible on record header';

            let actionTypeBadge = '';
            if (btn.action_type === 'send_email') {
                actionTypeBadge = `
                    <span class="inline-flex items-center gap-1.5 text-indigo-700 dark:text-indigo-400 font-semibold text-xs">
                        <i data-lucide="mail" class="w-3.5 h-3.5"></i>
                        <span>Email Dispatch</span>
                    </span>`;
            } else if (btn.action_type === 'url_redirect') {
                actionTypeBadge = `
                    <span class="inline-flex items-center gap-1.5 text-emerald-700 dark:text-emerald-400 font-semibold text-xs">
                        <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                        <span>Web Redirect</span>
                    </span>`;
            } else if (btn.action_type === 'workflow_trigger') {
                actionTypeBadge = `
                    <span class="inline-flex items-center gap-1.5 text-amber-700 dark:text-amber-400 font-semibold text-xs">
                        <i data-lucide="zap" class="w-3.5 h-3.5"></i>
                        <span>Workflow Action</span>
                    </span>`;
            } else {
                actionTypeBadge = `
                    <span class="inline-flex items-center gap-1.5 text-sky-700 dark:text-sky-400 font-semibold text-xs">
                        <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                        <span>In-App Quick Action</span>
                    </span>`;
            }

            rowsHtml += `
                <tr class="border-b border-zinc-100 dark:border-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors">
                    <!-- Button Name & Style Preview -->
                    <td class="px-3.5 py-3 align-middle">
                        <div class="flex items-center gap-3">
                            <button
                                type="button"
                                onclick="window.handleEditCustomButton(${btn.id})"
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-md cursor-pointer transition-transform hover:scale-[1.02] ${styleObj.btnClass}"
                                title="Edit button '${escapeHtml(btn.label)}'"
                            >
                                <i data-lucide="${iconLucide}" class="w-3 h-3 stroke-[2.2]"></i>
                                <span>${escapeHtml(btn.label)}</span>
                            </button>
                            <div class="flex flex-col">
                                <span
                                    onclick="window.handleEditCustomButton(${btn.id})"
                                    class="font-semibold text-xs text-zinc-900 dark:text-zinc-100 cursor-pointer hover:text-indigo-600 dark:hover:text-indigo-400"
                                >
                                    ${escapeHtml(btn.label)}
                                </span>
                                <span class="text-[11px] text-zinc-400 dark:text-zinc-500 font-mono">
                                    ${escapeHtml(btn.name)}
                                </span>
                            </div>
                        </div>
                    </td>

                    <!-- Target Module -->
                    <td class="px-3 py-3 align-middle whitespace-nowrap">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200/80 dark:border-blue-900">
                            ${escapeHtml(moduleName)}
                        </span>
                    </td>

                    <!-- Action Type -->
                    <td class="px-3 py-3 align-middle whitespace-nowrap">
                        ${actionTypeBadge}
                    </td>

                    <!-- Action Details / Target -->
                    <td class="px-3 py-3 align-middle max-w-xs">
                        <div class="text-xs text-zinc-600 dark:text-zinc-400 truncate" title="${escapeHtml(actionDetail)}">
                            ${actionDetail}
                        </div>
                    </td>

                    <!-- Visibility & Rules -->
                    <td class="px-3 py-3 align-middle whitespace-nowrap">
                        ${conditions.length > 0 ? `
                            <span
                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-800 dark:bg-amber-950/50 dark:text-amber-300 border border-amber-200 dark:border-amber-800 cursor-help"
                                title="${escapeHtml(conditionTooltip)}"
                            >
                                <i data-lucide="sliders-horizontal" class="w-3 h-3 stroke-[2]"></i>
                                <span>Conditional (${conditions.length})</span>
                            </span>
                        ` : `
                            <span class="text-xs text-zinc-400 dark:text-zinc-500">
                                Always Visible
                            </span>
                        `}
                    </td>

                    <!-- Placement -->
                    <td class="px-3 py-3 align-middle whitespace-nowrap">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700">
                            Record Header
                        </span>
                    </td>

                    <!-- Status Active/Disabled Toggle -->
                    <td class="px-3 py-3 align-middle text-center whitespace-nowrap">
                        <button
                            type="button"
                            onclick="window.handleToggleCustomButton(${btn.id})"
                            class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold cursor-pointer transition-all ${
                                btn.is_active
                                    ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 hover:bg-emerald-100'
                                    : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-200'
                            }"
                            title="Click to toggle status"
                        >
                            ${btn.is_active ? '● Active' : '○ Disabled'}
                        </button>
                    </td>

                    <!-- Action Buttons -->
                    <td class="px-3.5 py-3 align-middle text-right whitespace-nowrap">
                        <div class="inline-flex items-center gap-1.5">
                            <button
                                type="button"
                                onclick="window.handleEditCustomButton(${btn.id})"
                                class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded-md border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 cursor-pointer shadow-2xs transition-colors"
                                title="Edit Button"
                            >
                                <i data-lucide="edit-2" class="w-3 h-3"></i>
                                <span>Edit</span>
                            </button>
                            <button
                                type="button"
                                onclick="window.handleDeleteCustomButton(${btn.id})"
                                class="p-1 rounded-md text-zinc-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/50 cursor-pointer transition-colors"
                                title="Delete Button"
                            >
                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });

        container.innerHTML = `
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden shadow-xs">
                <div class="overflow-x-auto w-full">
                    <table class="w-full border-collapse text-left text-xs">
                        <thead>
                            <tr class="bg-zinc-50 dark:bg-zinc-800/60 border-b border-zinc-200 dark:border-zinc-800 text-[11px] font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider whitespace-nowrap">
                                <th class="px-3.5 py-2.5">Button Name &amp; Visual Style</th>
                                <th class="px-3 py-2.5">Target Module</th>
                                <th class="px-3 py-2.5">Action Type</th>
                                <th class="px-3 py-2.5">Action Details / Target</th>
                                <th class="px-3 py-2.5">Visibility &amp; Rules</th>
                                <th class="px-3 py-2.5">Placement</th>
                                <th class="px-3 py-2.5 text-center">Status</th>
                                <th class="px-3.5 py-2.5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${rowsHtml}
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    }

    function renderGridView(filtered, container) {
        let cardsHtml = '';
        filtered.forEach(btn => {
            const moduleName = getModuleName(btn.entity_type);
            const styleObj = getStyleObj(btn.style_variant);
            const iconLucide = getLucideIconName(btn.icon);
            const actionDetail = getActionDetail(btn);

            let conditions = [];
            if (Array.isArray(btn.visibility_conditions)) {
                conditions = btn.visibility_conditions;
            } else if (typeof btn.visibility_conditions === 'string') {
                try { conditions = JSON.parse(btn.visibility_conditions); } catch (e) {}
            }

            let actionTypeTitle = 'Workflow Action';
            let actionTypeIcon = 'zap';
            let actionTypeColor = 'text-amber-600 dark:text-amber-400';

            if (btn.action_type === 'send_email') {
                actionTypeTitle = 'Email Dispatch';
                actionTypeIcon = 'mail';
                actionTypeColor = 'text-indigo-600 dark:text-indigo-400';
            } else if (btn.action_type === 'url_redirect') {
                actionTypeTitle = 'Web Link / Redirect';
                actionTypeIcon = 'external-link';
                actionTypeColor = 'text-emerald-600 dark:text-emerald-400';
            } else if (btn.action_type === 'quick_action' || btn.action_type === 'add_weight_dims') {
                actionTypeTitle = 'In-App Quick Action';
                actionTypeIcon = 'layers';
                actionTypeColor = 'text-sky-600 dark:text-sky-400';
            }

            cardsHtml += `
                <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-4 flex flex-col justify-between gap-3.5 shadow-xs hover:border-zinc-300 dark:hover:border-zinc-700 transition-all">
                    <div>
                        <!-- Card Top Bar: Module, Visibility, Status -->
                        <div class="flex items-center justify-between gap-2 mb-2.5">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200/80 dark:border-blue-900">
                                    ${escapeHtml(moduleName)}
                                </span>
                                ${conditions.length > 0 ? `
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-800 dark:bg-amber-950/50 dark:text-amber-300 border border-amber-200 dark:border-amber-800" title="Visible when: ${escapeHtml(conditions.map(c => `${c.field} ${c.operator} "${c.value}"`).join(', '))}">
                                        Conditional (${conditions.length})
                                    </span>
                                ` : ''}
                            </div>
                            <button
                                type="button"
                                onclick="window.handleToggleCustomButton(${btn.id})"
                                class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold cursor-pointer ${
                                    btn.is_active
                                        ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800'
                                        : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700'
                                }"
                            >
                                ${btn.is_active ? 'Active' : 'Disabled'}
                            </button>
                        </div>

                        <!-- Live Look & Feel Box -->
                        <div class="p-3.5 bg-zinc-50 dark:bg-zinc-800/60 rounded-xl border border-zinc-200 dark:border-zinc-700/60 flex flex-col items-center justify-center gap-2 mb-3">
                            <button
                                type="button"
                                onclick="window.handleEditCustomButton(${btn.id})"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-md cursor-pointer transition-transform hover:scale-[1.02] ${styleObj.btnClass}"
                                title="Edit button '${escapeHtml(btn.label)}'"
                            >
                                <i data-lucide="${iconLucide}" class="w-3.5 h-3.5 stroke-[2.2]"></i>
                                <span>${escapeHtml(btn.label)}</span>
                            </button>
                            <span class="text-[11px] text-zinc-400 dark:text-zinc-500 font-mono">
                                API Name: ${escapeHtml(btn.name)}
                            </span>
                        </div>

                        <!-- Action Description -->
                        <div class="text-xs">
                            <div class="flex items-center gap-1.5 font-semibold text-zinc-900 dark:text-zinc-100 mb-0.5">
                                <i data-lucide="${actionTypeIcon}" class="w-3.5 h-3.5 ${actionTypeColor}"></i>
                                <span>${actionTypeTitle}</span>
                            </div>
                            <div class="text-zinc-500 dark:text-zinc-400 text-[11px] truncate" title="${escapeHtml(actionDetail)}">
                                ${actionDetail}
                            </div>
                        </div>
                    </div>

                    <!-- Card Footer: Header Action & Edit/Delete -->
                    <div class="flex items-center justify-between pt-2.5 border-t border-zinc-100 dark:border-zinc-800 text-xs">
                        <span class="text-zinc-400 dark:text-zinc-500 text-[11px]">
                            Header Action
                        </span>
                        <div class="inline-flex items-center gap-1">
                            <button
                                type="button"
                                onclick="window.handleEditCustomButton(${btn.id})"
                                class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded-md border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 cursor-pointer shadow-2xs"
                            >
                                <i data-lucide="edit-2" class="w-3 h-3"></i>
                                <span>Edit</span>
                            </button>
                            <button
                                type="button"
                                onclick="window.handleDeleteCustomButton(${btn.id})"
                                class="p-1 rounded-md text-zinc-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/50 cursor-pointer"
                                title="Delete Button"
                            >
                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });

        container.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                ${cardsHtml}
            </div>
        `;
    }

    // =========================================================================
    // MODAL BUILDER (1-to-1 REACT PARITY)
    // =========================================================================

    function renderModal() {
        const root = document.getElementById('cbModalsRoot');
        if (!root) return;

        if (!isModalOpen) {
            root.innerHTML = '';
            return;
        }

        const previewStyleObj = getStyleObj(formStyle);
        const previewLucide = getLucideIconName(formIcon);
        const currentModuleName = getModuleName(formEntity);

        // Filter templates for current entity or global
        const availableTemplates = templates.filter(t => t.entity_type === formEntity || t.entity_type === 'global');

        // Fields for current entity (from MERGE_TAGS_MAP or custom fields)
        const entityMergeTags = MERGE_TAGS_MAP[formEntity] || ['id', 'name', 'status'];

        root.innerHTML = `
            <div
                class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4 animate-fade-in"
                id="cbModalOverlay"
                onclick="window.handleModalBackdropClick(event)"
            >
                <div
                    class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl w-full max-w-3xl max-h-[92vh] flex flex-col shadow-2xl overflow-hidden animate-scale-in"
                    onclick="event.stopPropagation()"
                >
                    <!-- Header -->
                    <div class="px-5 py-4 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/70 dark:bg-zinc-800/40 flex items-center justify-between shrink-0">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300 flex items-center justify-center shrink-0">
                                <i data-lucide="mouse-pointer-click" class="w-4 h-4 stroke-[2.2]"></i>
                            </div>
                            <div>
                                <h3 class="text-sm sm:text-base font-bold text-zinc-950 dark:text-zinc-100 m-0">
                                    ${editingButton ? `Edit Button: ${escapeHtml(editingButton.label)}` : 'New Custom Button & Action'}
                                </h3>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400 m-0 mt-0.5">
                                    Configure action logic, look & feel, and target record behavior
                                </p>
                            </div>
                        </div>
                        <button
                            type="button"
                            onclick="window.handleCloseModal()"
                            class="p-1 rounded-lg text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800 cursor-pointer"
                        >
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <!-- Form Body -->
                    <form id="cbModalForm" onsubmit="window.handleSaveCustomButton(event)" class="flex-1 overflow-y-auto flex flex-col">
                        <div class="p-5 flex-1 flex flex-col gap-4">
                            <!-- Live Preview Box -->
                            <div class="p-4 bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200 dark:border-zinc-700/60 rounded-xl flex items-center justify-between gap-4 flex-wrap">
                                <div>
                                    <div class="text-[10px] font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider mb-1.5">
                                        Live Button Preview on Record Header:
                                    </div>
                                    <button
                                        type="button"
                                        id="cbModalLivePreviewBtn"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-md shadow-xs cursor-default ${previewStyleObj.btnClass}"
                                    >
                                        <i data-lucide="${previewLucide}" id="cbModalLivePreviewIcon" class="w-3.5 h-3.5 stroke-[2.2]"></i>
                                        <span id="cbModalLivePreviewLabel">${escapeHtml(formLabel || 'Button Label')}</span>
                                    </button>
                                </div>
                                <div class="text-right">
                                    <span id="cbModalLivePreviewModuleBadge" class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200/80 dark:border-blue-900">
                                        ${escapeHtml(currentModuleName)}
                                    </span>
                                </div>
                            </div>

                            <!-- Row 1: Target Entity, Label, Name -->
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                                        Target Entity *
                                    </label>
                                    <select
                                        id="cbFormEntity"
                                        class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-1.5 text-xs text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                                        onchange="window.handleFormEntityChange(this.value)"
                                    >
                                        ${MODULE_OPTIONS.map(m => `<option value="${m.id}" ${m.id === formEntity ? 'selected' : ''}>${m.name}</option>`).join('')}
                                        ${customObjects.map(co => `<option value="${co.api_name}" ${co.api_name === formEntity ? 'selected' : ''}>${co.label_plural || co.label_singular}</option>`).join('')}
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                                        Button Label *
                                    </label>
                                    <input
                                        type="text"
                                        id="cbFormLabel"
                                        required
                                        placeholder="e.g. Send Welcome Kit"
                                        value="${escapeHtml(formLabel)}"
                                        oninput="window.handleFormLabelInput(this.value)"
                                        class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-1.5 text-xs text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                                    />
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                                        API Name (Identifier)
                                    </label>
                                    <input
                                        type="text"
                                        id="cbFormName"
                                        placeholder="e.g. send_welcome_kit"
                                        value="${escapeHtml(formName)}"
                                        oninput="window.handleFormNameInput(this.value)"
                                        class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-1.5 text-xs font-mono text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                                    />
                                </div>
                            </div>

                            <!-- Row 2: Icon Picker, Color Scheme, Active Checkbox -->
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
                                <div>
                                    <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                                        Icon
                                    </label>
                                    <select
                                        id="cbFormIcon"
                                        class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-1.5 text-xs text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                                        onchange="window.handleFormIconChange(this.value)"
                                    >
                                        ${AVAILABLE_ICONS.map(i => `<option value="${i.id}" ${i.id === formIcon ? 'selected' : ''}>${i.label}</option>`).join('')}
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                                        Color Scheme
                                    </label>
                                    <select
                                        id="cbFormStyle"
                                        class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-1.5 text-xs text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                                        onchange="window.handleFormStyleChange(this.value)"
                                    >
                                        ${STYLE_VARIANTS.map(s => `<option value="${s.id}" ${s.id === formStyle ? 'selected' : ''}>${s.label}</option>`).join('')}
                                    </select>
                                </div>

                                <div class="flex items-center h-8.5 pb-0.5">
                                    <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-semibold text-zinc-800 dark:text-zinc-200">
                                        <input
                                            type="checkbox"
                                            id="cbFormIsActive"
                                            ${formIsActive ? 'checked' : ''}
                                            onchange="window.handleFormIsActiveChange(this.checked)"
                                            class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-zinc-300 dark:border-zinc-700"
                                        />
                                        <span>Active</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Section: Action Type & Execution Flow -->
                            <div class="border border-zinc-200 dark:border-zinc-800 rounded-xl p-4 bg-zinc-50/40 dark:bg-zinc-800/30">
                                <label class="block text-xs font-bold text-zinc-900 dark:text-zinc-100 mb-2.5">
                                    Action Type &amp; Execution Flow *
                                </label>

                                <!-- 4 Action Cards -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5 mb-3.5">
                                    <!-- Send Email -->
                                    <div
                                        onclick="window.handleActionTypeSelect('send_email')"
                                        class="p-3 rounded-lg border cursor-pointer transition-all ${
                                            formActionType === 'send_email'
                                                ? 'border-indigo-500 bg-indigo-50/70 dark:bg-indigo-950/40 ring-1 ring-indigo-500/40'
                                                : 'border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 hover:border-zinc-300'
                                        }"
                                    >
                                        <div class="flex items-center gap-1.5 font-bold text-xs text-indigo-700 dark:text-indigo-400 mb-1">
                                            <i data-lucide="mail" class="w-3.5 h-3.5"></i>
                                            <span>Send Email</span>
                                        </div>
                                        <p class="m-0 text-[11px] text-zinc-500 dark:text-zinc-400 leading-snug">
                                            Dispatches an Email Template with attached CRM repository files.
                                        </p>
                                    </div>

                                    <!-- URL Redirect -->
                                    <div
                                        onclick="window.handleActionTypeSelect('url_redirect')"
                                        class="p-3 rounded-lg border cursor-pointer transition-all ${
                                            formActionType === 'url_redirect'
                                                ? 'border-emerald-500 bg-emerald-50/70 dark:bg-emerald-950/40 ring-1 ring-emerald-500/40'
                                                : 'border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 hover:border-zinc-300'
                                        }"
                                    >
                                        <div class="flex items-center gap-1.5 font-bold text-xs text-emerald-700 dark:text-emerald-400 mb-1">
                                            <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                            <span>Web Link / URL</span>
                                        </div>
                                        <p class="m-0 text-[11px] text-zinc-500 dark:text-zinc-400 leading-snug">
                                            Opens an external portal or internal web link with dynamic record merge tags.
                                        </p>
                                    </div>

                                    <!-- Workflow Trigger -->
                                    <div
                                        onclick="window.handleActionTypeSelect('workflow_trigger')"
                                        class="p-3 rounded-lg border cursor-pointer transition-all ${
                                            formActionType === 'workflow_trigger'
                                                ? 'border-amber-500 bg-amber-50/70 dark:bg-amber-950/40 ring-1 ring-amber-500/40'
                                                : 'border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 hover:border-zinc-300'
                                        }"
                                    >
                                        <div class="flex items-center gap-1.5 font-bold text-xs text-amber-700 dark:text-amber-400 mb-1">
                                            <i data-lucide="zap" class="w-3.5 h-3.5"></i>
                                            <span>Workflow Trigger</span>
                                        </div>
                                        <p class="m-0 text-[11px] text-zinc-500 dark:text-zinc-400 leading-snug">
                                            Executes automatic field updates or calls a secure webhook endpoint.
                                        </p>
                                    </div>

                                    <!-- In-App Quick Action -->
                                    <div
                                        onclick="window.handleActionTypeSelect('quick_action')"
                                        class="p-3 rounded-lg border cursor-pointer transition-all ${
                                            formActionType === 'quick_action' || formActionType === 'add_weight_dims'
                                                ? 'border-sky-500 bg-sky-50/70 dark:bg-sky-950/40 ring-1 ring-sky-500/40'
                                                : 'border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 hover:border-zinc-300'
                                        }"
                                    >
                                        <div class="flex items-center gap-1.5 font-bold text-xs text-sky-700 dark:text-sky-400 mb-1">
                                            <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                                            <span>In-App Quick Action</span>
                                        </div>
                                        <p class="m-0 text-[11px] text-zinc-500 dark:text-zinc-400 leading-snug">
                                            Launches an in-app interactive modal or specialized studio workflow.
                                        </p>
                                    </div>
                                </div>

                                <!-- Action Specific Details Panels -->
                                <div id="cbActionConfigPanel" class="pt-3 border-t border-zinc-200 dark:border-zinc-750">
                                    ${renderActionConfigDetails(availableTemplates, entityMergeTags)}
                                </div>
                            </div>

                            <!-- Section: Visibility Conditions -->
                            <div class="border border-zinc-200 dark:border-zinc-800 rounded-xl p-4 bg-zinc-50/40 dark:bg-zinc-800/30">
                                <div class="flex items-center justify-between gap-3 mb-2.5">
                                    <div>
                                        <label class="block text-xs font-bold text-zinc-900 dark:text-zinc-100 m-0">
                                            Visibility Conditions (Optional)
                                        </label>
                                        <p class="m-0 mt-0.5 text-[11px] text-zinc-500 dark:text-zinc-400">
                                            Control when this button appears on record headers (e.g. only show when <code class="font-mono text-zinc-700 dark:text-zinc-300">stage = Closed Won</code>). If empty, the button is always shown.
                                        </p>
                                    </div>
                                    <button
                                        type="button"
                                        onclick="window.handleAddCondition()"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-md border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 cursor-pointer shadow-2xs shrink-0"
                                    >
                                        <i data-lucide="plus" class="w-3 h-3"></i>
                                        <span>Add Condition</span>
                                    </button>
                                </div>

                                <div id="cbConditionsList" class="space-y-2">
                                    ${renderVisibilityConditionsRows()}
                                </div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="px-5 py-3.5 border-t border-zinc-200 dark:border-zinc-800 bg-zinc-50/70 dark:bg-zinc-800/40 flex items-center justify-between shrink-0">
                            <button
                                type="button"
                                onclick="window.handleCloseModal()"
                                class="px-3.5 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 text-xs font-semibold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 cursor-pointer transition-colors"
                            >
                                Cancel
                            </button>

                            <button
                                type="submit"
                                id="cbModalSubmitBtn"
                                ${isSaving ? 'disabled' : ''}
                                class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-xs cursor-pointer transition-colors disabled:opacity-60"
                            >
                                <span>${isSaving ? 'Saving...' : (editingButton ? 'Update Custom Button' : 'Save Custom Button')}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        `;

        if (window.lucide) {
            lucide.createIcons();
        }
    }

    function renderActionConfigDetails(availableTemplates, entityMergeTags) {
        if (formActionType === 'send_email') {
            return `
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                            Linked Email Template *
                        </label>
                        <select
                            id="cbActionTemplateId"
                            required
                            class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-1.5 text-xs text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                            onchange="window.handleActionTemplateChange(this.value)"
                        >
                            <option value="">&mdash; Select an Email Template &mdash;</option>
                            ${availableTemplates.map(t => `
                                <option value="${t.id}" ${String(t.id) === String(actionTemplateId) ? 'selected' : ''}>
                                    ${escapeHtml(t.name)} (${(t.entity_type || 'GLOBAL').toUpperCase()})
                                </option>
                            `).join('')}
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                            Execution Behavior
                        </label>
                        <select
                            id="cbActionExecMode"
                            class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-1.5 text-xs text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                            onchange="window.handleActionExecModeChange(this.value)"
                        >
                            <option value="composer" ${actionExecMode === 'composer' ? 'selected' : ''}>Preview Composer (Recommended)</option>
                            <option value="instant" ${actionExecMode === 'instant' ? 'selected' : ''}>Instant One-Click Send</option>
                        </select>
                    </div>
                </div>
            `;
        } else if (formActionType === 'url_redirect') {
            return `
                <div class="space-y-3">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300 m-0">
                                URL Template *
                            </label>
                            <div class="relative">
                                <button
                                    type="button"
                                    onclick="window.toggleUrlMergeTagsDropdown()"
                                    class="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-semibold rounded-md border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 cursor-pointer shadow-2xs"
                                >
                                    <i data-lucide="sparkles" class="w-3 h-3 text-emerald-600 dark:text-emerald-400"></i>
                                    <span>Insert Tag</span>
                                    <i data-lucide="chevron-down" class="w-3 h-3 text-zinc-400"></i>
                                </button>
                                <div
                                    id="cbUrlMergeTagsDropdown"
                                    class="hidden absolute right-0 top-full mt-1 w-52 max-h-48 overflow-y-auto bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg shadow-lg z-50 p-1.5 space-y-0.5"
                                >
                                    ${entityMergeTags.map(tag => `
                                        <button
                                            type="button"
                                            onclick="window.handleInsertUrlTag('${tag}')"
                                            class="w-full text-left px-2 py-1 rounded text-xs font-mono text-zinc-800 dark:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-700 cursor-pointer"
                                        >
                                            {{${tag}}}
                                        </button>
                                    `).join('')}
                                </div>
                            </div>
                        </div>
                        <input
                            type="text"
                            id="cbActionUrlTemplate"
                            required
                            placeholder="e.g. https://portal.client.com/track?id={{id}}&company={{company}}"
                            value="${escapeHtml(actionUrlTemplate)}"
                            oninput="window.handleActionUrlTemplateInput(this.value)"
                            class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-1.5 text-xs font-mono text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                            Target Destination Window
                        </label>
                        <select
                            id="cbActionOpenTarget"
                            class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-1.5 text-xs text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                            onchange="window.handleActionOpenTargetChange(this.value)"
                        >
                            <option value="_blank" ${actionOpenTarget === '_blank' ? 'selected' : ''}>New Browser Tab (_blank)</option>
                            <option value="_self" ${actionOpenTarget === '_self' ? 'selected' : ''}>Current Window (_self)</option>
                            <option value="modal_iframe" ${actionOpenTarget === 'modal_iframe' ? 'selected' : ''}>DealSync Embedded Modal Window</option>
                        </select>
                    </div>
                </div>
            `;
        } else if (formActionType === 'workflow_trigger') {
            return `
                <div class="space-y-3">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                                Trigger Action
                            </label>
                            <select
                                id="cbActionTriggerType"
                                class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-1.5 text-xs text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                                onchange="window.handleActionTriggerTypeChange(this.value)"
                            >
                                <option value="field_update" ${actionTriggerType === 'field_update' ? 'selected' : ''}>Update Record Field</option>
                                <option value="webhook" ${actionTriggerType === 'webhook' ? 'selected' : ''}>Call External Webhook</option>
                            </select>
                        </div>

                        ${actionTriggerType === 'field_update' ? `
                            <div>
                                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                                    Field to Update *
                                </label>
                                <select
                                    id="cbActionUpdateField"
                                    class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-1.5 text-xs text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                                    onchange="window.handleActionUpdateFieldChange(this.value)"
                                >
                                    ${(availableEntityFields.length > 0 ? availableEntityFields : (MERGE_TAGS_MAP[formEntity] || ['status', 'stage']).map(f => ({ name: f, label: f.replace(/_/g, ' ') }))).map(f => `
                                        <option value="${f.name}" ${f.name === actionUpdateField ? 'selected' : ''}>
                                            ${escapeHtml(f.label)} (${f.name})
                                        </option>
                                    `).join('')}
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                                    New Value *
                                </label>
                                <input
                                    type="text"
                                    id="cbActionUpdateValue"
                                    required
                                    placeholder="e.g. Qualified"
                                    value="${escapeHtml(actionUpdateValue)}"
                                    oninput="window.handleActionUpdateValueInput(this.value)"
                                    class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-1.5 text-xs text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                                />
                            </div>
                        ` : `
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                                    Webhook Endpoint URL *
                                </label>
                                <input
                                    type="url"
                                    id="cbActionWebhookUrl"
                                    required
                                    placeholder="https://hooks.zapier.com/..."
                                    value="${escapeHtml(actionWebhookUrl)}"
                                    oninput="window.handleActionWebhookUrlInput(this.value)"
                                    class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-1.5 text-xs font-mono text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                                />
                            </div>
                        `}
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300 m-0">
                                Success Confirmation Toast
                            </label>
                            <span class="text-[11px] text-zinc-400 dark:text-zinc-500">
                                Select from Pop-up Notification studio or customize
                            </span>
                        </div>
                        <select
                            id="cbActionToastPresetSelect"
                            class="w-full mb-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-1.5 text-xs text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                            onchange="window.handleToastPresetChange(this.value)"
                        >
                            <optgroup label="Standard Confirmation Toasts">
                                <option value="std_action_completed" ${actionSuccessMsg === 'Action completed successfully.' ? 'selected' : ''}>Action completed successfully.</option>
                                <option value="std_record_updated" ${actionSuccessMsg === 'Record status updated successfully.' ? 'selected' : ''}>Record status updated successfully.</option>
                                <option value="std_wf_executed" ${actionSuccessMsg === 'Workflow executed successfully.' ? 'selected' : ''}>Workflow executed successfully.</option>
                            </optgroup>
                            ${notificationRules.length > 0 ? `
                                <optgroup label="Pop-up Notification Studio Templates">
                                    ${notificationRules.map(rule => `
                                        <option value="rule_${rule.id}" ${actionSuccessMsg === (rule.message_template || rule.title || rule.name) ? 'selected' : ''}>
                                            ${escapeHtml(rule.name)} (${rule.module}) &mdash; &ldquo;${escapeHtml((rule.title || rule.message_template || '').slice(0, 45))}&rdquo;
                                        </option>
                                    `).join('')}
                                </optgroup>
                            ` : ''}
                            <option value="custom">-- Custom Toast Message --</option>
                        </select>
                        <input
                            type="text"
                            id="cbActionSuccessMsg"
                            placeholder="e.g. Record status updated to Qualified."
                            value="${escapeHtml(actionSuccessMsg)}"
                            oninput="window.handleActionSuccessMsgInput(this.value)"
                            class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-1.5 text-xs text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                        />
                    </div>
                </div>
            `;
        } else {
            // In-App Quick Action
            return `
                <div class="space-y-3">
                    <div class="p-3 rounded-lg bg-sky-50 dark:bg-sky-950/40 border border-sky-200 dark:border-sky-800 text-xs text-sky-800 dark:text-sky-300 flex items-start gap-2.5">
                        <i data-lucide="layers" class="w-5 h-5 text-sky-600 shrink-0 mt-0.5"></i>
                        <div>
                            <strong class="font-bold">Specialized In-App Action Studio</strong>
                            <div class="text-[11px] text-sky-700 dark:text-sky-400 mt-0.5 leading-snug">
                                Executes a native DealSync interactive studio or specialized modal workflow directly inside the record view without leaving the page.
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                            Target In-App Component / Studio Action *
                        </label>
                        <select
                            id="cbActionQuickActionComponent"
                            required
                            class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-1.5 text-xs text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                            onchange="window.handleActionQuickActionComponentChange(this.value)"
                        >
                            <optgroup label="Native Specialized Studios">
                                <option value="add_weight_dims" ${actionQuickActionComponent === 'add_weight_dims' ? 'selected' : ''}>Weight &amp; Dimensions Specification Studio (Cargo Tables)</option>
                            </optgroup>
                            ${processFlows.length > 0 ? `
                                <optgroup label="ProcessStudio Business Process Flows">
                                    ${processFlows
                                        .filter(f => f.status === 'active' && (f.entity_type === formEntity || f.entity_type === 'all'))
                                        .map(f => `
                                            <option value="process_flow:${f.id}" ${actionQuickActionComponent === `process_flow:${f.id}` ? 'selected' : ''}>
                                                ${escapeHtml(f.name)}
                                            </option>
                                        `).join('')}
                                </optgroup>
                            ` : ''}
                        </select>
                        <p class="m-0 mt-1 text-[11px] text-zinc-400 dark:text-zinc-500">
                            Select a specialized native studio or any active business process built in <strong>ProcessStudio</strong>.
                        </p>
                    </div>
                </div>
            `;
        }
    }

    function renderVisibilityConditionsRows() {
        if (visibilityConditions.length === 0) {
            return `
                <div class="p-3 bg-zinc-50 dark:bg-zinc-800/40 border border-dashed border-zinc-200 dark:border-zinc-700 rounded-lg text-xs text-zinc-500 dark:text-zinc-400 flex items-center justify-between gap-2 flex-wrap">
                    <span>No visibility filters configured. Button is visible on all records of this module.</span>
                    ${formEntity === 'opportunities' ? `
                        <button
                            type="button"
                            onclick="window.handleQuickCondition('stage', 'equals', 'Closed Won')"
                            class="px-2 py-1 rounded text-[11px] font-semibold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 hover:bg-indigo-100 cursor-pointer"
                        >
                            + Only when Stage = Closed Won
                        </button>
                    ` : ''}
                </div>
            `;
        }

        return visibilityConditions.map((cond, idx) => `
            <div class="grid grid-cols-1 sm:grid-cols-[1.5fr_1.2fr_1.5fr_auto] gap-2 items-center">
                <div>
                    <input
                        type="text"
                        placeholder="Field (e.g. stage, status)"
                        value="${escapeHtml(cond.field)}"
                        oninput="window.handleConditionFieldInput(${idx}, this.value)"
                        class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-1 text-xs text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                    />
                </div>
                <div>
                    <select
                        onchange="window.handleConditionOperatorChange(${idx}, this.value)"
                        class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-1 text-xs text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                    >
                        <option value="equals" ${cond.operator === 'equals' ? 'selected' : ''}>Equals</option>
                        <option value="not_equals" ${cond.operator === 'not_equals' ? 'selected' : ''}>Does not equal</option>
                        <option value="contains" ${cond.operator === 'contains' ? 'selected' : ''}>Contains</option>
                        <option value="is_not_empty" ${cond.operator === 'is_not_empty' ? 'selected' : ''}>Is not empty</option>
                    </select>
                </div>
                <div>
                    <input
                        type="text"
                        placeholder="Target Value"
                        value="${escapeHtml(cond.value)}"
                        ${cond.operator === 'is_not_empty' ? 'disabled' : ''}
                        oninput="window.handleConditionValueInput(${idx}, this.value)"
                        class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-1 text-xs text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 disabled:opacity-50"
                    />
                </div>
                <div>
                    <button
                        type="button"
                        onclick="window.handleRemoveCondition(${idx})"
                        class="p-1.5 text-zinc-400 hover:text-red-600 dark:hover:text-red-400 rounded-md hover:bg-red-50 dark:hover:bg-red-950/40 cursor-pointer"
                        title="Remove condition"
                    >
                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                    </button>
                </div>
            </div>
        `).join('');
    }

    function updateLivePreviewInModal() {
        const previewBtn = document.getElementById('cbModalLivePreviewBtn');
        const previewIcon = document.getElementById('cbModalLivePreviewIcon');
        const previewLabel = document.getElementById('cbModalLivePreviewLabel');
        const previewBadge = document.getElementById('cbModalLivePreviewModuleBadge');

        if (!previewBtn || !previewIcon || !previewLabel) return;

        const styleObj = getStyleObj(formStyle);
        const iconLucide = getLucideIconName(formIcon);

        // Reset class to styleObj.btnClass
        previewBtn.className = `inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-md shadow-xs cursor-default ${styleObj.btnClass}`;
        previewIcon.setAttribute('data-lucide', iconLucide);
        previewLabel.textContent = formLabel || 'Button Label';

        if (previewBadge) {
            previewBadge.textContent = getModuleName(formEntity);
        }

        if (window.lucide) {
            lucide.createIcons();
        }
    }

    // =========================================================================
    // EVENT HANDLERS & WINDOW EXPORTS
    // =========================================================================

    window.handleOpenCreateButtonModal = function () {
        editingButton = null;
        formEntity = selectedEntity !== 'all' ? selectedEntity : 'leads';
        formLabel = '';
        formName = '';
        formIcon = 'Mail';
        formStyle = 'indigo';
        formPlacement = 'record_header';
        formIsActive = true;
        formActionType = 'send_email';
        actionQuickActionComponent = 'add_weight_dims';
        visibilityConditions = [];

        const entityTmpl = templates.find(t => t.entity_type === formEntity || t.entity_type === 'global');
        actionTemplateId = entityTmpl ? String(entityTmpl.id) : '';
        actionExecMode = 'composer';
        actionRecipientField = 'email';
        actionUrlTemplate = 'https://example.com/portal?id={{id}}';
        actionOpenTarget = '_blank';
        actionTriggerType = 'field_update';
        actionUpdateField = 'status';
        actionUpdateValue = 'Qualified';
        actionWebhookUrl = '';
        actionSuccessMsg = 'Action completed successfully.';

        isModalOpen = true;
        renderModal();
    };

    window.handleEditCustomButton = function (buttonId) {
        const btn = buttons.find(b => Number(b.id) === Number(buttonId));
        if (!btn) return;

        editingButton = btn;
        formEntity = btn.entity_type;
        formLabel = btn.label;
        formName = btn.name;
        formIcon = btn.icon || 'Mail';
        formStyle = btn.style_variant || 'secondary';
        formPlacement = btn.placement || 'record_header';
        formIsActive = Boolean(btn.is_active);

        const cfg = btn.action_config || {};
        if (btn.action_type === 'add_weight_dims' || btn.action_type === 'quick_action') {
            formActionType = 'quick_action';
            actionQuickActionComponent = cfg.component || 'add_weight_dims';
        } else {
            formActionType = btn.action_type;
        }

        let vis = btn.visibility_conditions;
        if (typeof vis === 'string') {
            try { vis = JSON.parse(vis); } catch (e) { vis = []; }
        }
        visibilityConditions = Array.isArray(vis) ? vis : [];

        actionTemplateId = cfg.template_id ? String(cfg.template_id) : '';
        actionExecMode = cfg.execution_mode || 'composer';
        actionRecipientField = cfg.default_recipient_field || 'email';
        actionUrlTemplate = cfg.url_template || '';
        actionOpenTarget = cfg.open_target || '_blank';
        actionTriggerType = cfg.trigger_type || 'field_update';

        if (cfg.field_updates) {
            const keys = Object.keys(cfg.field_updates);
            if (keys.length > 0) {
                actionUpdateField = keys[0];
                actionUpdateValue = String(cfg.field_updates[keys[0]]);
            }
        } else {
            actionUpdateField = 'status';
            actionUpdateValue = '';
        }

        actionWebhookUrl = cfg.webhook_url || '';
        actionSuccessMsg = cfg.success_message || 'Action completed successfully.';

        isModalOpen = true;
        renderModal();
    };

    window.handleCloseModal = function () {
        isModalOpen = false;
        renderModal();
    };

    window.handleModalBackdropClick = function (e) {
        if (e.target.id === 'cbModalOverlay') {
            window.handleCloseModal();
        }
    };

    window.handleFormEntityChange = function (val) {
        formEntity = val;
        // Update template defaults
        const entityTmpl = templates.find(t => t.entity_type === formEntity || t.entity_type === 'global');
        actionTemplateId = entityTmpl ? String(entityTmpl.id) : '';
        updateLivePreviewInModal();
        // Re-render action config to update merge tags and templates
        const configPanel = document.getElementById('cbActionConfigPanel');
        if (configPanel) {
            const availableTemplates = templates.filter(t => t.entity_type === formEntity || t.entity_type === 'global');
            const entityMergeTags = MERGE_TAGS_MAP[formEntity] || ['id', 'name', 'status'];
            configPanel.innerHTML = renderActionConfigDetails(availableTemplates, entityMergeTags);
            if (window.lucide) lucide.createIcons();
        }
    };

    window.handleFormLabelInput = function (val) {
        formLabel = val;
        if (!editingButton) {
            formName = val.toLowerCase().replace(/[^a-z0-9_]+/g, '_').replace(/^_+|_+$/g, '');
            const nameInput = document.getElementById('cbFormName');
            if (nameInput) nameInput.value = formName;
        }
        updateLivePreviewInModal();
    };

    window.handleFormNameInput = function (val) {
        formName = val;
    };

    window.handleFormIconChange = function (val) {
        formIcon = val;
        updateLivePreviewInModal();
    };

    window.handleFormStyleChange = function (val) {
        formStyle = val;
        updateLivePreviewInModal();
    };

    window.handleFormIsActiveChange = function (checked) {
        formIsActive = checked;
    };

    window.handleActionTypeSelect = function (type) {
        formActionType = type;
        if (type === 'quick_action') {
            formIcon = 'Layers';
            const iconSelect = document.getElementById('cbFormIcon');
            if (iconSelect) iconSelect.value = 'Layers';
        }
        renderModal();
    };

    window.handleActionTemplateChange = function (val) { actionTemplateId = val; };
    window.handleActionExecModeChange = function (val) { actionExecMode = val; };
    window.handleActionUrlTemplateInput = function (val) { actionUrlTemplate = val; };
    window.handleActionOpenTargetChange = function (val) { actionOpenTarget = val; };
    window.handleActionTriggerTypeChange = function (val) {
        actionTriggerType = val;
        const configPanel = document.getElementById('cbActionConfigPanel');
        if (configPanel) {
            const availableTemplates = templates.filter(t => t.entity_type === formEntity || t.entity_type === 'global');
            const entityMergeTags = MERGE_TAGS_MAP[formEntity] || ['id', 'name', 'status'];
            configPanel.innerHTML = renderActionConfigDetails(availableTemplates, entityMergeTags);
            if (window.lucide) lucide.createIcons();
        }
    };
    window.handleActionUpdateFieldChange = function (val) { actionUpdateField = val; };
    window.handleActionUpdateValueInput = function (val) { actionUpdateValue = val; };
    window.handleActionWebhookUrlInput = function (val) { actionWebhookUrl = val; };
    window.handleActionSuccessMsgInput = function (val) { actionSuccessMsg = val; };
    window.handleActionQuickActionComponentChange = function (val) { actionQuickActionComponent = val; };

    window.handleToastPresetChange = function (val) {
        const msgInput = document.getElementById('cbActionSuccessMsg');
        if (val === 'std_action_completed') {
            actionSuccessMsg = 'Action completed successfully.';
        } else if (val === 'std_record_updated') {
            actionSuccessMsg = 'Record status updated successfully.';
        } else if (val === 'std_wf_executed') {
            actionSuccessMsg = 'Workflow executed successfully.';
        } else if (val.startsWith('rule_')) {
            const ruleId = val.replace('rule_', '');
            const rule = notificationRules.find(r => String(r.id) === String(ruleId));
            if (rule) {
                actionSuccessMsg = rule.message_template || rule.title || rule.name;
            }
        }
        if (msgInput) msgInput.value = actionSuccessMsg;
    };

    window.toggleUrlMergeTagsDropdown = function () {
        const dd = document.getElementById('cbUrlMergeTagsDropdown');
        if (dd) dd.classList.toggle('hidden');
    };

    window.handleInsertUrlTag = function (tag) {
        actionUrlTemplate += `{{${tag}}}`;
        const input = document.getElementById('cbActionUrlTemplate');
        if (input) input.value = actionUrlTemplate;
        const dd = document.getElementById('cbUrlMergeTagsDropdown');
        if (dd) dd.classList.add('hidden');
    };

    window.handleAddCondition = function () {
        visibilityConditions.push({
            field: formEntity === 'opportunities' ? 'stage' : 'status',
            operator: 'equals',
            value: formEntity === 'opportunities' ? 'Closed Won' : 'Qualified'
        });
        const list = document.getElementById('cbConditionsList');
        if (list) {
            list.innerHTML = renderVisibilityConditionsRows();
            if (window.lucide) lucide.createIcons();
        }
    };

    window.handleQuickCondition = function (field, operator, value) {
        visibilityConditions.push({ field, operator, value });
        const list = document.getElementById('cbConditionsList');
        if (list) {
            list.innerHTML = renderVisibilityConditionsRows();
            if (window.lucide) lucide.createIcons();
        }
    };

    window.handleRemoveCondition = function (index) {
        visibilityConditions.splice(index, 1);
        const list = document.getElementById('cbConditionsList');
        if (list) {
            list.innerHTML = renderVisibilityConditionsRows();
            if (window.lucide) lucide.createIcons();
        }
    };

    window.handleConditionFieldInput = function (index, val) {
        if (visibilityConditions[index]) visibilityConditions[index].field = val;
    };

    window.handleConditionOperatorChange = function (index, val) {
        if (visibilityConditions[index]) {
            visibilityConditions[index].operator = val;
            const list = document.getElementById('cbConditionsList');
            if (list) {
                list.innerHTML = renderVisibilityConditionsRows();
                if (window.lucide) lucide.createIcons();
            }
        }
    };

    window.handleConditionValueInput = function (index, val) {
        if (visibilityConditions[index]) visibilityConditions[index].value = val;
    };

    // Toggle Active Status
    window.handleToggleCustomButton = async function (buttonId) {
        const btn = buttons.find(b => Number(b.id) === Number(buttonId));
        if (!btn) return;

        const nextActive = !btn.is_active;
        try {
            const res = await fetch(`/api/custom-buttons/${buttonId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                },
                body: JSON.stringify({ is_active: nextActive })
            });

            if (!res.ok) throw new Error('Toggle request failed');
            btn.is_active = nextActive;
            showFeedback(`Button "${btn.label}" ${nextActive ? 'enabled' : 'disabled'}.`);
            renderMain();
        } catch (err) {
            alert(`Toggle failed: ${err.message}`);
        }
    };

    // Delete Button
    window.handleDeleteCustomButton = async function (buttonId) {
        const btn = buttons.find(b => Number(b.id) === Number(buttonId));
        if (!btn) return;

        if (!confirm(`Are you sure you want to delete button "${btn.label}"?`)) {
            return;
        }

        try {
            const res = await fetch(`/api/custom-buttons/${buttonId}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                }
            });

            if (!res.ok) throw new Error('Delete request failed');
            buttons = buttons.filter(b => Number(b.id) !== Number(buttonId));
            showFeedback(`Custom button "${btn.label}" deleted.`);
            renderMain();
        } catch (err) {
            alert(`Delete failed: ${err.message}`);
        }
    };

    // Save Button
    window.handleSaveCustomButton = async function (e) {
        e.preventDefault();
        if (!formLabel.trim()) {
            alert('Button label is required.');
            return;
        }

        let config = {};
        if (formActionType === 'send_email') {
            config = {
                template_id: actionTemplateId,
                execution_mode: actionExecMode,
                default_recipient_field: actionRecipientField
            };
        } else if (formActionType === 'url_redirect') {
            config = {
                url_template: actionUrlTemplate.trim(),
                open_target: actionOpenTarget
            };
        } else if (formActionType === 'workflow_trigger') {
            config = {
                trigger_type: actionTriggerType,
                field_updates: actionTriggerType === 'field_update' ? { [actionUpdateField || 'status']: actionUpdateValue } : undefined,
                webhook_url: actionTriggerType === 'webhook' ? actionWebhookUrl.trim() : undefined,
                success_message: actionSuccessMsg.trim() || undefined
            };
        } else if (formActionType === 'quick_action' || formActionType === 'add_weight_dims') {
            config = {
                component: actionQuickActionComponent,
                default_weight_unit: 'LBS',
                default_dim_unit: 'INCH',
                description: actionQuickActionComponent === 'add_weight_dims'
                    ? 'Opens Weight & Dimensions table specification studio'
                    : 'Launches in-app quick action'
            };
        }

        const payload = {
            entity_type: formEntity,
            name: (formName || formLabel.toLowerCase().replace(/[^a-z0-9_]+/g, '_')).trim(),
            label: formLabel.trim(),
            icon: formIcon,
            style_variant: formStyle,
            action_type: formActionType === 'add_weight_dims' ? 'quick_action' : formActionType,
            action_config: config,
            placement: formPlacement,
            visibility_conditions: visibilityConditions,
            is_active: formIsActive ? 1 : 0
        };

        isSaving = true;
        const submitBtn = document.getElementById('cbModalSubmitBtn');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Saving...';
        }

        try {
            let res;
            if (editingButton) {
                res = await fetch(`/api/custom-buttons/${editingButton.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken()
                    },
                    body: JSON.stringify(payload)
                });
            } else {
                res = await fetch('/api/custom-buttons', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken()
                    },
                    body: JSON.stringify(payload)
                });
            }

            const data = await res.json();
            if (!res.ok || (data && data.success === false)) {
                throw new Error(data.message || 'Operation failed');
            }

            const savedBtn = data.data || data.button || data;

            if (editingButton) {
                const idx = buttons.findIndex(b => Number(b.id) === Number(editingButton.id));
                if (idx !== -1) buttons[idx] = savedBtn;
                showFeedback(`Button "${formLabel}" updated successfully.`);
            } else {
                buttons.unshift(savedBtn);
                showFeedback(`Button "${formLabel}" created successfully.`);
            }

            window.handleCloseModal();
            renderMain();
        } catch (err) {
            alert(`Save failed: ${err.message}`);
        } finally {
            isSaving = false;
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = editingButton ? 'Update Custom Button' : 'Save Custom Button';
            }
        }
    };

    // =========================================================================
    // INITIALIZATION
    // =========================================================================

    function initCustomButtonsStudio() {
        const root = document.getElementById('cbStudioRoot');
        if (!root) return;

        // Hydrate from SSR data script
        const serverDataScript = document.getElementById('cbServerData');
        if (serverDataScript) {
            try {
                const hydrated = JSON.parse(serverDataScript.textContent);
                buttons = Array.isArray(hydrated.buttons) ? hydrated.buttons : [];
                templates = Array.isArray(hydrated.templates) ? hydrated.templates : [];
                notificationRules = Array.isArray(hydrated.notificationRules) ? hydrated.notificationRules : [];
                customObjects = Array.isArray(hydrated.customObjects) ? hydrated.customObjects : [];
                processFlows = Array.isArray(hydrated.processFlows) ? hydrated.processFlows : [];
                selectedEntity = hydrated.selectedEntity || 'all';
            } catch (err) {
                console.warn('CustomButtonsStudio hydration error:', err);
            }
        }

        // Attach Search input
        const searchInput = document.getElementById('cbSearchInput');
        if (searchInput) {
            searchInput.value = searchQuery;
            searchInput.oninput = function () {
                searchQuery = this.value;
                renderMain();
            };
        }

        // Attach Module Select
        const moduleSelect = document.getElementById('cbModuleSelect');
        if (moduleSelect) {
            moduleSelect.value = selectedEntity;
            moduleSelect.onchange = function () {
                selectedEntity = this.value;
                renderMain();
            };
        }

        // Attach View Mode Switcher
        const listBtn = document.getElementById('cbViewListBtn');
        const gridBtn = document.getElementById('cbViewGridBtn');

        function updateViewModeButtons() {
            if (!listBtn || !gridBtn) return;
            if (viewMode === 'list') {
                listBtn.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-md transition-all cursor-pointer bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 shadow-xs';
                gridBtn.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-md transition-all cursor-pointer text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-200';
            } else {
                gridBtn.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-md transition-all cursor-pointer bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 shadow-xs';
                listBtn.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-md transition-all cursor-pointer text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-200';
            }
        }

        if (listBtn) {
            listBtn.onclick = function () {
                viewMode = 'list';
                updateViewModeButtons();
                renderMain();
            };
        }

        if (gridBtn) {
            gridBtn.onclick = function () {
                viewMode = 'grid';
                updateViewModeButtons();
                renderMain();
            };
        }

        updateViewModeButtons();
        renderMain();
    }

    // Export globally for SPA router
    window.initCustomButtonsStudio = initCustomButtonsStudio;

    // Auto-init on load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCustomButtonsStudio);
    } else {
        initCustomButtonsStudio();
    }
})();
