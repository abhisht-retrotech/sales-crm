/**
 * Custom Objects Studio — DealSync Enterprise (100% Tailwind CSS & React 1:1 Parity)
 * Features:
 * - 24+ Icon Studio with live search filter
 * - Dynamic Schema Provisioning with __c API Names
 * - Configured Objects list with real-time record count badges
 * - Record Workspace with live search, pagination, and empty states
 * - 4-Tab Salesforce Record Detail Modal (Details, Related Lists, Activity Timeline, System Info)
 * - Safe Delete Confirmation with typed name verification
 * - Dynamic Script Fallback & SPA Navigation Integration
 */

(function () {
    'use strict';

    // 24 Available Custom Object Icons (Matching React CustomObjectsManager.tsx)
    const AVAILABLE_ICONS = [
        { name: 'Box', label: 'Box / Asset', category: 'Assets' },
        { name: 'Package', label: 'Package / Product', category: 'Assets' },
        { name: 'HardDrive', label: 'Hardware / Server', category: 'Assets' },
        { name: 'Cpu', label: 'Device / IoT / Chip', category: 'Assets' },
        { name: 'Truck', label: 'Fleet / Logistics', category: 'Operations' },
        { name: 'Compass', label: 'Route / Location', category: 'Operations' },
        { name: 'Building2', label: 'Facility / Property', category: 'Operations' },
        { name: 'Wrench', label: 'Maintenance / Service', category: 'Operations' },
        { name: 'Briefcase', label: 'Project / Engagement', category: 'Business' },
        { name: 'FileText', label: 'Contract / Document', category: 'Business' },
        { name: 'Folder', label: 'Case / Dossier', category: 'Business' },
        { name: 'ShoppingBag', label: 'Store / Retail / Order', category: 'Business' },
        { name: 'CreditCard', label: 'Subscription / Billing', category: 'Finance' },
        { name: 'Tag', label: 'Pricing / Campaign', category: 'Finance' },
        { name: 'Shield', label: 'Insurance / Warranty', category: 'Governance' },
        { name: 'Key', label: 'License / Access Token', category: 'Governance' },
        { name: 'Award', label: 'Certificate / Milestone', category: 'Governance' },
        { name: 'Users', label: 'Stakeholders / Crew', category: 'People' },
        { name: 'Calendar', label: 'Booking / Schedule', category: 'Operations' },
        { name: 'Activity', label: 'Telemetry / Metrics', category: 'Data' },
        { name: 'Layers', label: 'Tier / Classification', category: 'Data' },
        { name: 'Zap', label: 'Energy / Fast Track', category: 'Utility' },
        { name: 'Globe', label: 'Region / Territory', category: 'Operations' },
        { name: 'Bookmark', label: 'Standard Catalog', category: 'Assets' },
    ];

    // SVG dictionary for crisp, instant icon rendering
    const ICON_SVGS = {
        box: '<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>',
        package: '<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>',
        harddrive: '<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" x2="2" y1="12" y2="12"/><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/><line x1="6" x2="6.01" y1="16" y2="16"/><line x1="10" x2="10.01" y1="16" y2="16"/></svg>',
        cpu: '<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="16" height="16" x="4" y="4" rx="2"/><rect width="6" height="6" x="9" y="9" rx="1"/><path d="M15 2v2"/><path d="M15 20v2"/><path d="M2 15h2"/><path d="M2 9h2"/><path d="M20 15h2"/><path d="M20 9h2"/><path d="M9 2v2"/><path d="M9 20v2"/></svg>',
        truck: '<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/></svg>',
        compass: '<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/></svg>',
        building2: '<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/><path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/><path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/><path d="M10 6h4"/><path d="M10 10h4"/><path d="M10 14h4"/><path d="M10 18h4"/></svg>',
        wrench: '<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>',
        briefcase: '<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 20V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/><rect width="20" height="14" x="2" y="6" rx="2"/></svg>',
        filetext: '<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/></svg>',
        folder: '<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 20a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.9a2 2 0 0 1-1.69-.9L9.6 3.9A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2Z"/></svg>',
        shoppingbag: '<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>',
        creditcard: '<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>',
        tag: '<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z"/><circle cx="7.5" cy="7.5" r=".5" fill="currentColor"/></svg>',
        shield: '<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/></svg>',
        key: '<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="7.5" cy="15.5" r="5.5"/><path d="m21 2-9.6 9.6"/><path d="m15.5 7.5 3 3L22 7l-3-3"/></svg>',
        award: '<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/></svg>',
        users: '<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
        calendar: '<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/></svg>',
        activity: '<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-2.48a2 2 0 0 0-1.93 1.46l-2.35 8.36a.25.25 0 0 1-.48 0L9.22 2.18a.25.25 0 0 0-.48 0l-2.35 8.36A2 2 0 0 1 4.46 12H2"/></svg>',
        layers: '<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83Z"/><path d="m22 12.5-9.17 4.16a2 2 0 0 1-1.66 0L2 12.5"/><path d="m22 17.5-9.17 4.16a2 2 0 0 1-1.66 0L2 17.5"/></svg>',
        zap: '<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46l-1.92 6.02A1 1 0 0 0 13 10h7a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46l1.92-6.02A1 1 0 0 0 11 14z"/></svg>',
        globe: '<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>',
        bookmark: '<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m19 21-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16z"/></svg>',
    };

    function renderIconSvg(iconName) {
        const key = (iconName || 'box').toLowerCase();
        return ICON_SVGS[key] || ICON_SVGS.box;
    }

    // State container
    const state = {
        customObjects: [],
        selectedObject: null,
        loadingObjects: false,

        // Records Workspace
        records: [],
        loadingRecords: false,
        recordSearch: '',
        recordPage: 1,
        recordLimit: 10,
        recordTotalPages: 1,
        recordTotalCount: 0,

        // Icon Studio Modal
        selectedIcon: 'Box',
        iconSearch: '',

        // Modals
        isCreateModalOpen: false,
        isRecordModalOpen: false,
        deleteConfirmObject: null,
        detailRecord: null,
        detailTab: 'details',
        isEditingDetail: false,

        // Feedback toast
        feedbackMessage: null,
        feedbackTimer: null,
    };

    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function showFeedback(msg) {
        state.feedbackMessage = msg;
        renderFeedbackToast();
        if (state.feedbackTimer) clearTimeout(state.feedbackTimer);
        state.feedbackTimer = setTimeout(() => {
            state.feedbackMessage = null;
            renderFeedbackToast();
        }, 4500);
    }

    // --- Core API Helpers ---
    async function apiFetch(endpoint, options = {}) {
        const headers = {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
            ...(options.headers || {})
        };
        const res = await fetch(endpoint, { credentials: 'same-origin', ...options, headers });
        if (!res.ok) {
            let errorMsg = `Server error (${res.status})`;
            try {
                const json = await res.json();
                if (json.message) errorMsg = json.message;
            } catch (e) {}
            throw new Error(errorMsg);
        }
        return res.json();
    }

    async function loadObjects(autoSelectFirst = false) {
        state.loadingObjects = true;
        renderObjectsList();
        try {
            const data = await apiFetch('/api/custom-objects');
            state.customObjects = Array.isArray(data) ? data : [];

            if (state.selectedObject) {
                const stillExists = state.customObjects.find(o => String(o.id) === String(state.selectedObject.id));
                state.selectedObject = stillExists || null;
            }

            if (!state.selectedObject && autoSelectFirst && state.customObjects.length > 0) {
                state.selectedObject = state.customObjects[0];
            }

            if (state.selectedObject) {
                await loadRecords(state.selectedObject.id, state.recordPage, state.recordSearch);
            } else {
                state.records = [];
                state.recordTotalCount = 0;
            }
        } catch (err) {
            console.error('Failed to load custom objects:', err);
        } finally {
            state.loadingObjects = false;
            renderObjectsList();
            renderRecordWorkspace();
        }
    }

    async function loadRecords(objectId, page = 1, search = '') {
        state.loadingRecords = true;
        renderRecordWorkspace();
        try {
            const q = new URLSearchParams();
            q.append('page', String(page));
            q.append('limit', String(state.recordLimit));
            if (search.trim()) q.append('search', search.trim());

            const res = await apiFetch(`/api/custom-objects/${encodeURIComponent(objectId)}/records?${q.toString()}`);
            state.records = res.records || [];
            state.recordPage = res.page || 1;
            state.recordTotalPages = res.totalPages || 1;
            state.recordTotalCount = res.totalCount || 0;
        } catch (err) {
            console.error('Failed to load custom object records:', err);
            state.records = [];
        } finally {
            state.loadingRecords = false;
            renderRecordWorkspace();
        }
    }

    // --- DOM Elements & Rendering ---
    function getRoot() {
        return document.getElementById('coStudioRoot');
    }

    function renderFeedbackToast() {
        const container = document.getElementById('coFeedbackContainer');
        if (!container) return;
        if (!state.feedbackMessage) {
            container.innerHTML = '';
            container.classList.add('hidden');
            return;
        }
        container.classList.remove('hidden');
        container.innerHTML = `
            <div class="flex items-center gap-2 p-3.5 mb-5 text-xs font-semibold text-emerald-800 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-xl shadow-xs transition-all animate-fade-in">
                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <span class="flex-1">${escapeHtml(state.feedbackMessage)}</span>
                <button type="button" onclick="window.dismissCoFeedback()" class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-900 dark:hover:text-emerald-100 p-0.5">✕</button>
            </div>
        `;
    }

    window.dismissCoFeedback = function () {
        state.feedbackMessage = null;
        renderFeedbackToast();
    };

    function renderObjectsList() {
        const countSpan = document.getElementById('coConfiguredCount');
        if (countSpan) countSpan.textContent = `(${state.customObjects.length})`;

        const container = document.getElementById('coObjectsList');
        if (!container) return;

        if (state.loadingObjects && state.customObjects.length === 0) {
            container.innerHTML = `
                <div class="py-12 text-center text-zinc-400 dark:text-zinc-500 text-xs">
                    <svg class="w-6 h-6 mx-auto mb-2 animate-spin text-zinc-400 dark:text-zinc-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                    Loading custom schemas...
                </div>
            `;
            return;
        }

        if (state.customObjects.length === 0) {
            container.innerHTML = `
                <div class="p-8 text-center bg-zinc-50 dark:bg-zinc-800/50 rounded-xl border border-dashed border-zinc-200 dark:border-zinc-700/80">
                    <div class="w-10 h-10 mx-auto mb-3 rounded-xl bg-zinc-100 dark:bg-zinc-800 text-zinc-400 dark:text-zinc-500 flex items-center justify-center">
                        ${renderIconSvg('box')}
                    </div>
                    <div class="text-sm font-semibold text-zinc-800 dark:text-zinc-200">No custom objects yet</div>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 mb-4 leading-relaxed">
                        Create your first custom entity like Assets, Licenses, or Delivery Routes.
                    </p>
                    <button type="button" onclick="window.openCreateObjectModal()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm transition-colors">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        <span>Create Object</span>
                    </button>
                </div>
            `;
            return;
        }

        let html = '<div class="space-y-2">';
        state.customObjects.forEach(obj => {
            const isSelected = state.selectedObject && String(state.selectedObject.id) === String(obj.id);
            const activeCardClasses = isSelected
                ? 'border-zinc-950 dark:border-indigo-500 bg-zinc-50/90 dark:bg-zinc-800/90 shadow-sm'
                : 'border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 hover:border-zinc-300 dark:hover:border-zinc-700';

            const activeIconWrap = isSelected
                ? 'bg-zinc-900 text-white dark:bg-indigo-600 dark:text-white'
                : 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300';

            html += `
                <div class="group relative p-3.5 rounded-xl border transition-all cursor-pointer ${activeCardClasses}" onclick="window.selectCustomObject('${escapeHtml(obj.id)}')">
                    <div class="flex items-start justify-between gap-2 mb-1.5">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 transition-colors ${activeIconWrap}">
                                ${renderIconSvg(obj.icon)}
                            </div>
                            <div class="min-w-0">
                                <div class="text-xs font-bold text-zinc-900 dark:text-zinc-100 truncate">
                                    ${escapeHtml(obj.label)}
                                </div>
                                <div class="text-[11px] font-mono text-zinc-500 dark:text-zinc-400 truncate">
                                    ${escapeHtml(obj.api_name)}
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-1 shrink-0">
                            <button type="button"
                                    onclick="event.stopPropagation(); window.openDeleteObjectModal('${escapeHtml(obj.id)}')"
                                    title="Delete ${escapeHtml(obj.label)} Schema"
                                    class="p-1 text-zinc-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded transition-colors">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                            </button>
                            <svg class="w-4 h-4 text-zinc-400 dark:text-zinc-500 ${isSelected ? 'text-zinc-900 dark:text-white' : ''}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                        </div>
                    </div>

                    ${obj.description ? `<p class="text-[11px] text-zinc-600 dark:text-zinc-400 mb-2 line-clamp-2 leading-relaxed">${escapeHtml(obj.description)}</p>` : ''}

                    <div class="flex items-center gap-2 text-[11px]">
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded font-semibold bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                            ${obj.record_count ?? 0} records
                        </span>
                        <span class="text-zinc-300 dark:text-zinc-700">•</span>
                        <span class="text-zinc-500 dark:text-zinc-400 truncate">
                            Primary: <code class="font-mono text-zinc-700 dark:text-zinc-300">${escapeHtml(obj.primary_field || 'name')}</code>
                        </span>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        container.innerHTML = html;
    }

    function renderRecordWorkspace() {
        const container = document.getElementById('coRecordWorkspace');
        if (!container) return;

        if (!state.selectedObject) {
            container.innerHTML = `
                <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 p-12 text-center text-zinc-400 dark:text-zinc-500">
                    <div class="w-12 h-12 mx-auto mb-3 rounded-2xl bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-400 dark:text-zinc-500">
                        ${renderIconSvg('box')}
                    </div>
                    <h3 class="text-sm font-semibold text-zinc-800 dark:text-zinc-200">No Custom Object Selected</h3>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 max-w-sm mx-auto">
                        Choose a configured entity from the left sidebar to view, manage, and provision records under its schema.
                    </p>
                </div>
            `;
            return;
        }

        const obj = state.selectedObject;
        const pluralTitle = obj.plural_label || `${obj.label}s`;

        let html = `
            <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 p-5 shadow-xs">
                <!-- Workspace Header -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-zinc-200 dark:border-zinc-800 mb-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100">
                                ${escapeHtml(pluralTitle)} Records
                            </h2>
                            <span class="text-[11px] font-mono px-2 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700">
                                ${escapeHtml(obj.api_name)}
                            </span>
                        </div>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                            Total ${state.recordTotalCount} records registered under this schema
                        </p>
                    </div>

                    <div class="flex items-center gap-2 flex-wrap">
                        <button type="button"
                                onclick="window.openDeleteObjectModal('${escapeHtml(obj.id)}')"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-rose-600 dark:text-rose-400 hover:text-rose-700 dark:hover:text-rose-300 bg-rose-50 dark:bg-rose-950/40 hover:bg-rose-100 dark:hover:bg-rose-900/60 border border-rose-200 dark:border-rose-900 rounded-lg transition-colors"
                                title="Delete ${escapeHtml(obj.label)} Schema">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                            <span>Delete Object</span>
                        </button>

                        <a href="/admin/page-layouts?module=${encodeURIComponent(obj.api_name)}"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-zinc-700 dark:text-zinc-200 bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 border border-zinc-300 dark:border-zinc-700 rounded-lg transition-colors"
                           title="Customize ${escapeHtml(obj.label)} Page Layout">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
                            <span>Page Layout</span>
                        </a>

                        <button type="button"
                                onclick="window.openCreateRecordModal()"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm transition-colors">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            <span>New ${escapeHtml(obj.label)}</span>
                        </button>
                    </div>
                </div>

                <!-- Search & Refresh Toolbar -->
                <div class="flex items-center gap-2 mb-4">
                    <div class="relative flex-1">
                        <svg class="w-4 h-4 text-zinc-400 dark:text-zinc-500 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text"
                               id="coRecordSearchInput"
                               placeholder="Search ${escapeHtml(pluralTitle)}..."
                               value="${escapeHtml(state.recordSearch)}"
                               oninput="window.handleRecordSearch(this.value)"
                               class="w-full pl-9 pr-3 py-1.5 text-xs bg-zinc-50 dark:bg-zinc-800/80 border border-zinc-300 dark:border-zinc-700 rounded-lg text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all" />
                    </div>
                    <button type="button"
                            onclick="window.refreshRecords()"
                            class="p-2 text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-100 bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 border border-zinc-300 dark:border-zinc-700 rounded-lg transition-colors"
                            title="Refresh records">
                        <svg class="w-3.5 h-3.5 ${state.loadingRecords ? 'animate-spin' : ''}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 21h5v-5"/></svg>
                    </button>
                </div>
        `;

        if (state.loadingRecords && state.records.length === 0) {
            html += `
                <div class="py-14 text-center text-zinc-400 dark:text-zinc-500 text-xs">
                    <svg class="w-6 h-6 mx-auto mb-2 animate-spin text-zinc-400 dark:text-zinc-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                    Loading ${escapeHtml(obj.label)} records...
                </div>
            `;
        } else if (state.records.length === 0) {
            html += `
                <div class="p-10 text-center bg-zinc-50 dark:bg-zinc-800/40 rounded-xl border border-dashed border-zinc-200 dark:border-zinc-700/80">
                    <div class="w-10 h-10 mx-auto mb-3 rounded-xl bg-zinc-100 dark:bg-zinc-800 text-zinc-400 dark:text-zinc-500 flex items-center justify-center">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/></svg>
                    </div>
                    <div class="text-sm font-semibold text-zinc-800 dark:text-zinc-200">
                        No records found in ${escapeHtml(obj.label)}
                    </div>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 mb-4 leading-relaxed">
                        ${state.recordSearch ? 'Try a different search keyword.' : 'Create your first record to begin tracking.'}
                    </p>
                    <button type="button"
                            onclick="window.openCreateRecordModal()"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm transition-colors">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        <span>Create ${escapeHtml(obj.label)} Record</span>
                    </button>
                </div>
            `;
        } else {
            html += `
                <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-800">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-zinc-50 dark:bg-zinc-800/80 border-b border-zinc-200 dark:border-zinc-800 text-zinc-500 dark:text-zinc-400 font-semibold uppercase tracking-wider text-[11px]">
                                <th class="py-2.5 px-3.5">Record Name (${escapeHtml(obj.primary_field || 'name')})</th>
                                <th class="py-2.5 px-3.5">Record ID</th>
                                <th class="py-2.5 px-3.5">Created By</th>
                                <th class="py-2.5 px-3.5">Created At</th>
                                <th class="py-2.5 px-3.5">Last Modified</th>
                                <th class="py-2.5 px-3.5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800 bg-white dark:bg-zinc-900">
            `;

            state.records.forEach(rec => {
                const createdAtStr = rec.created_at ? new Date(rec.created_at).toLocaleDateString() : '—';
                const updatedAtStr = rec.updated_at ? new Date(rec.updated_at).toLocaleDateString() : '—';

                html += `
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/60 transition-colors">
                        <td class="py-2.5 px-3.5">
                            <button type="button"
                                    onclick="window.openRecordDetailModal('${escapeHtml(rec.id)}')"
                                    class="inline-flex items-center gap-1.5 font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 transition-colors text-left">
                                <span>${escapeHtml(rec.name)}</span>
                                <svg class="w-3 h-3 text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                            </button>
                        </td>
                        <td class="py-2.5 px-3.5 font-mono text-[11px] text-zinc-500 dark:text-zinc-400">
                            #${escapeHtml(rec.id)}
                        </td>
                        <td class="py-2.5 px-3.5 text-zinc-700 dark:text-zinc-300 font-medium">
                            ${escapeHtml(rec.created_by || 'System Admin')}
                        </td>
                        <td class="py-2.5 px-3.5 text-zinc-500 dark:text-zinc-400">
                            ${createdAtStr}
                        </td>
                        <td class="py-2.5 px-3.5 text-zinc-500 dark:text-zinc-400">
                            ${updatedAtStr}
                        </td>
                        <td class="py-2.5 px-3.5 text-right">
                            <button type="button"
                                    onclick="window.openRecordDetailModal('${escapeHtml(rec.id)}')"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-semibold text-zinc-700 dark:text-zinc-200 bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 rounded border border-zinc-300 dark:border-zinc-700 transition-colors">
                                Open Record Page
                            </button>
                        </td>
                    </tr>
                `;
            });

            html += `
                        </tbody>
                    </table>
                </div>
            `;

            // Pagination Controls
            if (state.recordTotalPages > 1) {
                html += `
                    <div class="flex items-center justify-between gap-4 mt-4 pt-3 border-t border-zinc-200 dark:border-zinc-800 text-xs text-zinc-500 dark:text-zinc-400">
                        <div>
                            Page <span class="font-bold text-zinc-800 dark:text-zinc-200">${state.recordPage}</span> of <span class="font-bold text-zinc-800 dark:text-zinc-200">${state.recordTotalPages}</span> (${state.recordTotalCount} records)
                        </div>
                        <div class="flex items-center gap-1.5">
                            <button type="button"
                                    ${state.recordPage <= 1 ? 'disabled' : ''}
                                    onclick="window.goToRecordPage(${state.recordPage - 1})"
                                    class="px-2.5 py-1 text-xs font-semibold rounded bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 border border-zinc-300 dark:border-zinc-700 disabled:opacity-50 disabled:pointer-events-none transition-colors">
                                Previous
                            </button>
                            <button type="button"
                                    ${state.recordPage >= state.recordTotalPages ? 'disabled' : ''}
                                    onclick="window.goToRecordPage(${state.recordPage + 1})"
                                    class="px-2.5 py-1 text-xs font-semibold rounded bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 border border-zinc-300 dark:border-zinc-700 disabled:opacity-50 disabled:pointer-events-none transition-colors">
                                Next
                            </button>
                        </div>
                    </div>
                `;
            }
        }

        html += '</div>';
        container.innerHTML = html;
    }

    // --- Global Handlers Exposed for UI ---
    window.selectCustomObject = function (id) {
        const found = state.customObjects.find(o => String(o.id) === String(id));
        if (!found) return;
        state.selectedObject = found;
        state.recordPage = 1;
        state.recordSearch = '';
        renderObjectsList();
        loadRecords(found.id, 1, '');
    };

    window.refreshObjects = function () {
        loadObjects(false);
    };

    window.refreshRecords = function () {
        if (!state.selectedObject) return;
        loadRecords(state.selectedObject.id, state.recordPage, state.recordSearch);
    };

    let recordSearchDebounce = null;
    window.handleRecordSearch = function (val) {
        state.recordSearch = val;
        if (recordSearchDebounce) clearTimeout(recordSearchDebounce);
        recordSearchDebounce = setTimeout(() => {
            if (!state.selectedObject) return;
            state.recordPage = 1;
            loadRecords(state.selectedObject.id, 1, state.recordSearch);
        }, 250);
    };

    window.goToRecordPage = function (p) {
        if (!state.selectedObject || p < 1 || p > state.recordTotalPages) return;
        state.recordPage = p;
        loadRecords(state.selectedObject.id, p, state.recordSearch);
    };

    // --- MODAL: CREATE CUSTOM OBJECT ---
    window.openCreateObjectModal = function () {
        state.isCreateModalOpen = true;
        state.selectedIcon = 'Box';
        state.iconSearch = '';
        renderCreateObjectModal();
    };

    window.closeCreateObjectModal = function () {
        state.isCreateModalOpen = false;
        const modal = document.getElementById('coCreateObjectModal');
        if (modal) modal.remove();
    };

    function renderCreateObjectModal() {
        let existing = document.getElementById('coCreateObjectModal');
        if (existing) existing.remove();

        const filteredIcons = AVAILABLE_ICONS.filter(ic => {
            if (!state.iconSearch.trim()) return true;
            const q = state.iconSearch.toLowerCase();
            return ic.name.toLowerCase().includes(q) || ic.label.toLowerCase().includes(q) || ic.category.toLowerCase().includes(q);
        });

        const modal = document.createElement('div');
        modal.id = 'coCreateObjectModal';
        modal.className = 'fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-950/60 backdrop-blur-xs animate-fade-in';
        modal.innerHTML = `
            <div class="relative w-full max-w-xl max-h-[90vh] bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xl flex flex-col overflow-hidden">
                <!-- Header -->
                <div class="flex items-center justify-between px-6 py-4 border-b border-zinc-200 dark:border-zinc-800">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                            ${renderIconSvg('box')}
                        </div>
                        <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">
                            Create New Custom Object
                        </h3>
                    </div>
                    <button type="button" onclick="window.closeCreateObjectModal()" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 text-lg">✕</button>
                </div>

                <!-- Form Content -->
                <form id="coCreateObjectForm" onsubmit="window.handleCreateObjectSubmit(event)" class="flex flex-col flex-1 min-h-0">
                    <div class="flex-1 overflow-y-auto px-6 py-5 space-y-4">
                        <!-- Singular Label -->
                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                                Singular Label <span class="text-rose-500">*</span>
                            </label>
                            <input type="text"
                                   id="coFieldLabel"
                                   required
                                   placeholder="e.g. Asset, Warranty, License, Inspection, Route"
                                   oninput="window.handleSingularLabelChange(this.value)"
                                   class="w-full px-3 py-2 text-xs bg-zinc-50 dark:bg-zinc-800/80 border border-zinc-300 dark:border-zinc-700 rounded-lg text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                            <p class="text-[11px] text-zinc-400 dark:text-zinc-500 mt-1">
                                The singular label shown in navigation and headers.
                            </p>
                        </div>

                        <!-- Plural Label -->
                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                                Plural Label
                            </label>
                            <input type="text"
                                   id="coFieldPluralLabel"
                                   placeholder="e.g. Assets, Warranties, Licenses, Inspections, Routes"
                                   class="w-full px-3 py-2 text-xs bg-zinc-50 dark:bg-zinc-800/80 border border-zinc-300 dark:border-zinc-700 rounded-lg text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                        </div>

                        <!-- API Name -->
                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                                API Name (Schema Identifier)
                            </label>
                            <input type="text"
                                   id="coFieldApiName"
                                   required
                                   placeholder="Asset__c"
                                   class="w-full px-3 py-2 text-xs font-mono bg-zinc-50 dark:bg-zinc-800/80 border border-zinc-300 dark:border-zinc-700 rounded-lg text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                            <p class="text-[11px] text-zinc-400 dark:text-zinc-500 mt-1">
                                Suffix <code class="text-indigo-600 dark:text-indigo-400 font-mono">__c</code> will be enforced automatically if omitted.
                            </p>
                        </div>

                        <!-- Primary Record Field Name -->
                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                                Primary Record Field Name
                            </label>
                            <input type="text"
                                   id="coFieldPrimaryField"
                                   value="name"
                                   placeholder="name"
                                   class="w-full px-3 py-2 text-xs font-mono bg-zinc-50 dark:bg-zinc-800/80 border border-zinc-300 dark:border-zinc-700 rounded-lg text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                            <p class="text-[11px] text-zinc-400 dark:text-zinc-500 mt-1">
                                The default field representing record names (default: <code class="font-mono">name</code>).
                            </p>
                        </div>

                        <!-- 24+ Icon Studio -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                    Icon Selection
                                </label>
                                <div class="flex items-center gap-1.5 text-xs text-zinc-500 dark:text-zinc-400">
                                    <span>Selected:</span>
                                    <span id="coSelectedIconBadge" class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-zinc-900 text-white dark:bg-indigo-600 font-semibold text-[11px]">
                                        ${renderIconSvg(state.selectedIcon)}
                                        <span id="coSelectedIconName">${escapeHtml(state.selectedIcon)}</span>
                                    </span>
                                </div>
                            </div>

                            <!-- Quick Icon Search -->
                            <div class="relative mb-2">
                                <svg class="w-3.5 h-3.5 text-zinc-400 absolute left-2.5 top-1/2 -translate-y-1/2 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                <input type="text"
                                       id="coIconSearchInput"
                                       placeholder="Search 24+ icons (e.g. warranty, tool, server, logistics, tag, key)..."
                                       value="${escapeHtml(state.iconSearch)}"
                                       oninput="window.handleIconSearch(this.value)"
                                       class="w-full pl-8 pr-7 py-1.5 text-xs bg-zinc-50 dark:bg-zinc-800/80 border border-zinc-300 dark:border-zinc-700 rounded-lg text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                                ${state.iconSearch ? `
                                    <button type="button" onclick="window.handleIconSearch('')" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-600 text-xs">✕</button>
                                ` : ''}
                            </div>

                            <!-- Icon Grid -->
                            <div id="coIconGrid" class="grid grid-cols-2 sm:grid-cols-3 gap-1.5 p-2 max-h-40 overflow-y-auto bg-zinc-50 dark:bg-zinc-800/50 rounded-xl border border-zinc-200 dark:border-zinc-700">
                                ${renderIconButtons(filteredIcons)}
                            </div>
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                                Description
                            </label>
                            <textarea id="coFieldDescription"
                                      rows="2"
                                      placeholder="Purpose and business context of this custom object..."
                                      class="w-full px-3 py-2 text-xs bg-zinc-50 dark:bg-zinc-800/80 border border-zinc-300 dark:border-zinc-700 rounded-lg text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="flex items-center justify-end gap-2.5 px-6 py-4 border-t border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900/50">
                        <button type="button"
                                onclick="window.closeCreateObjectModal()"
                                class="px-3.5 py-1.5 text-xs font-semibold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-lg transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                id="coCreateSubmitBtn"
                                class="inline-flex items-center gap-1.5 px-4 py-1.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm transition-colors">
                            <span>Create Object</span>
                        </button>
                    </div>
                </form>
            </div>
        `;
        document.body.appendChild(modal);
    }

    function renderIconButtons(iconsList) {
        if (iconsList.length === 0) {
            return `<div class="col-span-full py-4 text-center text-xs text-zinc-400">No icons matching "${escapeHtml(state.iconSearch)}"</div>`;
        }
        return iconsList.map(item => {
            const isSelected = state.selectedIcon.toLowerCase() === item.name.toLowerCase();
            const btnClass = isSelected
                ? 'bg-zinc-900 text-white dark:bg-indigo-600 dark:text-white border-zinc-900 dark:border-indigo-600 shadow-xs'
                : 'bg-white dark:bg-zinc-900 text-zinc-700 dark:text-zinc-300 border-zinc-200 dark:border-zinc-700 hover:border-zinc-400 dark:hover:border-zinc-600';
            return `
                <button type="button"
                        onclick="window.selectIcon('${escapeHtml(item.name)}')"
                        title="${escapeHtml(item.label)} (${escapeHtml(item.category)})"
                        class="flex items-center gap-2 p-1.5 rounded-lg border text-xs font-medium transition-all text-left truncate ${btnClass}">
                    <span class="shrink-0 ${isSelected ? 'text-indigo-300 dark:text-white' : 'text-zinc-500 dark:text-zinc-400'}">
                        ${renderIconSvg(item.name)}
                    </span>
                    <span class="truncate text-[11px]">${escapeHtml(item.name)}</span>
                </button>
            `;
        }).join('');
    }

    window.selectIcon = function (name) {
        state.selectedIcon = name;
        const badge = document.getElementById('coSelectedIconBadge');
        if (badge) {
            badge.innerHTML = `${renderIconSvg(name)}<span>${escapeHtml(name)}</span>`;
        }
        const grid = document.getElementById('coIconGrid');
        if (grid) {
            const filtered = AVAILABLE_ICONS.filter(ic => {
                if (!state.iconSearch.trim()) return true;
                const q = state.iconSearch.toLowerCase();
                return ic.name.toLowerCase().includes(q) || ic.label.toLowerCase().includes(q) || ic.category.toLowerCase().includes(q);
            });
            grid.innerHTML = renderIconButtons(filtered);
        }
    };

    window.handleIconSearch = function (q) {
        state.iconSearch = q;
        const searchInput = document.getElementById('coIconSearchInput');
        if (searchInput && searchInput.value !== q) searchInput.value = q;
        const grid = document.getElementById('coIconGrid');
        if (grid) {
            const filtered = AVAILABLE_ICONS.filter(ic => {
                if (!q.trim()) return true;
                const qLower = q.toLowerCase();
                return ic.name.toLowerCase().includes(qLower) || ic.label.toLowerCase().includes(qLower) || ic.category.toLowerCase().includes(qLower);
            });
            grid.innerHTML = renderIconButtons(filtered);
        }
    };

    window.handleSingularLabelChange = function (val) {
        const pluralInput = document.getElementById('coFieldPluralLabel');
        const apiNameInput = document.getElementById('coFieldApiName');

        if (pluralInput && (!pluralInput.dataset.touched || !pluralInput.value)) {
            pluralInput.value = val ? `${val}s` : '';
        }
        if (apiNameInput && (!apiNameInput.dataset.touched || !apiNameInput.value)) {
            const slug = val.trim().replace(/[^a-zA-Z0-9_]/g, '_');
            apiNameInput.value = slug ? `${slug}__c` : '';
        }
    };

    window.handleCreateObjectSubmit = async function (e) {
        e.preventDefault();
        const label = document.getElementById('coFieldLabel')?.value.trim();
        const pluralLabel = document.getElementById('coFieldPluralLabel')?.value.trim();
        const apiName = document.getElementById('coFieldApiName')?.value.trim();
        const primaryField = document.getElementById('coFieldPrimaryField')?.value.trim();
        const description = document.getElementById('coFieldDescription')?.value.trim();

        if (!label) return;

        const btn = document.getElementById('coCreateSubmitBtn');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = `<svg class="w-3.5 h-3.5 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg><span>Creating Schema...</span>`;
        }

        try {
            const created = await apiFetch('/api/custom-objects', {
                method: 'POST',
                body: JSON.stringify({
                    label,
                    plural_label: pluralLabel || undefined,
                    api_name: apiName || undefined,
                    primary_field: primaryField || undefined,
                    icon: state.selectedIcon || 'Box',
                    description: description || undefined,
                })
            });

            window.closeCreateObjectModal();
            showFeedback(`Custom Object '${created.label}' (${created.api_name}) created successfully!`);
            await loadObjects(false);
            window.selectCustomObject(created.id);
        } catch (err) {
            alert(`Error creating custom object: ${err.message}`);
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = `<span>Create Object</span>`;
            }
        }
    };

    // --- MODAL: CREATE RECORD UNDER CUSTOM OBJECT ---
    window.openCreateRecordModal = function (prefillName = '', prefillAttrs = {}) {
        if (!state.selectedObject) return;
        state.isRecordModalOpen = true;
        renderCreateRecordModal(prefillName, prefillAttrs);
    };

    window.closeCreateRecordModal = function () {
        state.isRecordModalOpen = false;
        const modal = document.getElementById('coCreateRecordModal');
        if (modal) modal.remove();
    };

    function renderCreateRecordModal(prefillName = '', prefillAttrs = {}) {
        let existing = document.getElementById('coCreateRecordModal');
        if (existing) existing.remove();

        const obj = state.selectedObject;
        const modal = document.createElement('div');
        modal.id = 'coCreateRecordModal';
        modal.className = 'fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-950/60 backdrop-blur-xs animate-fade-in';
        modal.innerHTML = `
            <div class="relative w-full max-w-md bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xl flex flex-col overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-zinc-200 dark:border-zinc-800">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        </div>
                        <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">
                            New ${escapeHtml(obj.label)} Record
                        </h3>
                    </div>
                    <button type="button" onclick="window.closeCreateRecordModal()" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 text-lg">✕</button>
                </div>

                <form onsubmit="window.handleCreateRecordSubmit(event)" class="flex flex-col flex-1 min-h-0">
                    <div class="px-6 py-5 space-y-4">
                        <div class="p-3 bg-zinc-50 dark:bg-zinc-800/60 rounded-xl border border-zinc-200 dark:border-zinc-700/80 text-xs">
                            Schema: <strong class="text-zinc-900 dark:text-zinc-100">${escapeHtml(obj.label)}</strong>
                            <code class="font-mono text-zinc-500 dark:text-zinc-400">(${escapeHtml(obj.api_name)})</code>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                                Record Name / ${escapeHtml(obj.primary_field || 'name')} <span class="text-rose-500">*</span>
                            </label>
                            <input type="text"
                                   id="coRecordNameInput"
                                   required
                                   value="${escapeHtml(prefillName)}"
                                   placeholder="e.g. ${escapeHtml(obj.label)} #001"
                                   class="w-full px-3 py-2 text-xs bg-zinc-50 dark:bg-zinc-800/80 border border-zinc-300 dark:border-zinc-700 rounded-lg text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                                Description / Summary (Optional)
                            </label>
                            <textarea id="coRecordNotesInput"
                                      rows="3"
                                      placeholder="Enter record overview or notes..."
                                      class="w-full px-3 py-2 text-xs bg-zinc-50 dark:bg-zinc-800/80 border border-zinc-300 dark:border-zinc-700 rounded-lg text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500">${escapeHtml(prefillAttrs.notes || '')}</textarea>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2.5 px-6 py-4 border-t border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900/50">
                        <button type="button"
                                onclick="window.closeCreateRecordModal()"
                                class="px-3.5 py-1.5 text-xs font-semibold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-lg transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                id="coSaveRecordSubmitBtn"
                                class="inline-flex items-center gap-1.5 px-4 py-1.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm transition-colors">
                            <span>Save Record</span>
                        </button>
                    </div>
                </form>
            </div>
        `;
        document.body.appendChild(modal);
    }

    window.handleCreateRecordSubmit = async function (e) {
        e.preventDefault();
        if (!state.selectedObject) return;
        const name = document.getElementById('coRecordNameInput')?.value.trim();
        const notes = document.getElementById('coRecordNotesInput')?.value.trim();
        if (!name) return;

        const btn = document.getElementById('coSaveRecordSubmitBtn');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = `<svg class="w-3.5 h-3.5 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg><span>Saving Record...</span>`;
        }

        try {
            await apiFetch(`/api/custom-objects/${encodeURIComponent(state.selectedObject.id)}/records`, {
                method: 'POST',
                body: JSON.stringify({ name, notes })
            });

            window.closeCreateRecordModal();
            showFeedback(`New record created in ${state.selectedObject.label}!`);
            await loadRecords(state.selectedObject.id, 1, state.recordSearch);
            await loadObjects(false);
        } catch (err) {
            alert(`Error creating record: ${err.message}`);
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = `<span>Save Record</span>`;
            }
        }
    };

    // --- MODAL: DELETE CUSTOM OBJECT CONFIRMATION ---
    window.openDeleteObjectModal = function (id) {
        const found = state.customObjects.find(o => String(o.id) === String(id));
        if (!found) return;
        state.deleteConfirmObject = found;
        renderDeleteObjectModal();
    };

    window.closeDeleteObjectModal = function () {
        state.deleteConfirmObject = null;
        const modal = document.getElementById('coDeleteObjectModal');
        if (modal) modal.remove();
    };

    function renderDeleteObjectModal() {
        let existing = document.getElementById('coDeleteObjectModal');
        if (existing) existing.remove();

        const obj = state.deleteConfirmObject;
        if (!obj) return;

        const modal = document.createElement('div');
        modal.id = 'coDeleteObjectModal';
        modal.className = 'fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-950/60 backdrop-blur-xs animate-fade-in';
        modal.innerHTML = `
            <div class="relative w-full max-w-md bg-white dark:bg-zinc-900 rounded-2xl border border-rose-200 dark:border-rose-900/80 shadow-2xl flex flex-col overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-rose-100 dark:border-rose-950/60 bg-rose-50/70 dark:bg-rose-950/40">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-rose-100 dark:bg-rose-900/80 text-rose-600 dark:text-rose-300 flex items-center justify-center">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        </div>
                        <h3 class="text-sm font-bold text-rose-900 dark:text-rose-200">
                            Delete Custom Object: ${escapeHtml(obj.label)}
                        </h3>
                    </div>
                    <button type="button" onclick="window.closeDeleteObjectModal()" class="text-rose-600 dark:text-rose-400 hover:text-rose-900 text-lg">✕</button>
                </div>

                <form onsubmit="window.handleDeleteObjectSubmit(event)" class="flex flex-col flex-1 min-h-0">
                    <div class="px-6 py-5 space-y-4">
                        <div class="p-3.5 bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-900/80 rounded-xl text-xs text-rose-900 dark:text-rose-200 leading-relaxed">
                            <strong class="font-bold block mb-1">Warning: This action is permanent and cannot be undone!</strong>
                            <ul class="list-disc list-inside space-y-0.5 text-[11px] opacity-90">
                                <li>The schema <code class="font-mono font-bold">${escapeHtml(obj.api_name)}</code> will be completely removed.</li>
                                <li><strong>${obj.record_count ?? 0} record(s)</strong> stored in this object will be permanently erased.</li>
                                <li>All custom field definitions and customized page layouts for this object will be wiped.</li>
                            </ul>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                                Type <strong>${escapeHtml(obj.api_name)}</strong> or <strong>${escapeHtml(obj.label)}</strong> to confirm:
                            </label>
                            <input type="text"
                                   id="coDeleteConfirmInput"
                                   required
                                   placeholder="${escapeHtml(obj.api_name)}"
                                   oninput="window.handleDeleteConfirmInputChange(this.value)"
                                   class="w-full px-3 py-2 text-xs font-mono bg-zinc-50 dark:bg-zinc-800/80 border border-zinc-300 dark:border-zinc-700 rounded-lg text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-rose-500" />
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2.5 px-6 py-4 border-t border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900/50">
                        <button type="button"
                                onclick="window.closeDeleteObjectModal()"
                                class="px-3.5 py-1.5 text-xs font-semibold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-lg transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                id="coDeleteConfirmBtn"
                                disabled
                                class="inline-flex items-center gap-1.5 px-4 py-1.5 text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 disabled:opacity-40 disabled:pointer-events-none rounded-lg shadow-sm transition-colors">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                            <span>Permanently Delete</span>
                        </button>
                    </div>
                </form>
            </div>
        `;
        document.body.appendChild(modal);
    }

    window.handleDeleteConfirmInputChange = function (val) {
        const obj = state.deleteConfirmObject;
        if (!obj) return;
        const btn = document.getElementById('coDeleteConfirmBtn');
        if (!btn) return;

        const valLower = val.trim().toLowerCase();
        const matches = (valLower === obj.api_name.toLowerCase() || valLower === obj.label.toLowerCase());
        btn.disabled = !matches;
    };

    window.handleDeleteObjectSubmit = async function (e) {
        e.preventDefault();
        const obj = state.deleteConfirmObject;
        if (!obj) return;

        const btn = document.getElementById('coDeleteConfirmBtn');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = `<svg class="w-3.5 h-3.5 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg><span>Deleting Schema & Records...</span>`;
        }

        try {
            await apiFetch(`/api/custom-objects/${encodeURIComponent(obj.id)}`, { method: 'DELETE' });
            window.closeDeleteObjectModal();
            showFeedback(`Custom Object '${obj.label}' (${obj.api_name}) and all associated records have been permanently deleted.`);

            if (state.selectedObject && String(state.selectedObject.id) === String(obj.id)) {
                state.selectedObject = null;
                state.records = [];
            }
            await loadObjects(true);
        } catch (err) {
            alert(`Error deleting custom object: ${err.message}`);
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = `<span>Permanently Delete</span>`;
            }
        }
    };

    // --- MODAL: RECORD DETAIL (4-TAB SALESFORCE RECORD PAGE) ---
    window.openRecordDetailModal = async function (recordId) {
        if (!state.selectedObject) return;
        try {
            const res = await apiFetch(`/api/custom-objects/${encodeURIComponent(state.selectedObject.id)}/records/${encodeURIComponent(recordId)}`);
            state.detailRecord = res.data || res;
            state.detailTab = 'details';
            state.isEditingDetail = false;
            renderRecordDetailModal();
        } catch (err) {
            alert(`Failed to load record details: ${err.message}`);
        }
    };

    window.closeRecordDetailModal = function () {
        state.detailRecord = null;
        state.isEditingDetail = false;
        const modal = document.getElementById('coRecordDetailModal');
        if (modal) modal.remove();
        if (state.selectedObject) {
            loadRecords(state.selectedObject.id, state.recordPage, state.recordSearch);
        }
    };

    function renderRecordDetailModal() {
        let existing = document.getElementById('coRecordDetailModal');
        if (existing) existing.remove();

        const obj = state.selectedObject;
        const rec = state.detailRecord;
        if (!obj || !rec) return;

        const modal = document.createElement('div');
        modal.id = 'coRecordDetailModal';
        modal.className = 'fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-950/60 backdrop-blur-xs animate-fade-in';

        modal.innerHTML = `
            <div class="relative w-full max-w-3xl max-h-[90vh] bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xl flex flex-col overflow-hidden">
                <!-- Top Header & Actions -->
                <div class="flex items-center justify-between px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900/60">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center shrink-0 shadow-sm">
                            ${renderIconSvg(obj.icon)}
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs uppercase tracking-wider font-semibold text-zinc-500 dark:text-zinc-400">
                                    ${escapeHtml(obj.label)}
                                </span>
                                <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-zinc-200 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                                    #${escapeHtml(rec.id)}
                                </span>
                            </div>
                            <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100 leading-tight">
                                ${escapeHtml(rec.name)}
                            </h2>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button"
                                onclick="window.toggleEditDetail()"
                                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold ${state.isEditingDetail ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-200' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200'} hover:bg-zinc-200 dark:hover:bg-zinc-700 border border-zinc-300 dark:border-zinc-700 rounded-lg transition-colors">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                            <span>${state.isEditingDetail ? 'Viewing Mode' : 'Edit'}</span>
                        </button>

                        <button type="button"
                                onclick="window.cloneCurrentRecord()"
                                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-zinc-700 dark:text-zinc-200 bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 border border-zinc-300 dark:border-zinc-700 rounded-lg transition-colors"
                                title="Clone Record">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>
                            <span>Clone</span>
                        </button>

                        <button type="button"
                                onclick="window.deleteCurrentRecord()"
                                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/40 hover:bg-rose-100 dark:hover:bg-rose-900/60 border border-rose-200 dark:border-rose-900 rounded-lg transition-colors"
                                title="Delete Record">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/></svg>
                            <span>Delete</span>
                        </button>

                        <button type="button" onclick="window.closeRecordDetailModal()" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 text-lg p-1 ml-1">✕</button>
                    </div>
                </div>

                <!-- 4 Tabs Bar -->
                <div class="flex items-center gap-6 px-6 border-b border-zinc-200 dark:border-zinc-800 text-xs font-semibold">
                    <button type="button"
                            onclick="window.switchDetailTab('details')"
                            class="py-3 border-b-2 transition-colors ${state.detailTab === 'details' ? 'border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400' : 'border-transparent text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-200'}">
                        Record Details
                    </button>
                    <button type="button"
                            onclick="window.switchDetailTab('related')"
                            class="py-3 border-b-2 transition-colors ${state.detailTab === 'related' ? 'border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400' : 'border-transparent text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-200'}">
                        Related Lists (0)
                    </button>
                    <button type="button"
                            onclick="window.switchDetailTab('activity')"
                            class="py-3 border-b-2 transition-colors ${state.detailTab === 'activity' ? 'border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400' : 'border-transparent text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-200'}">
                        Activity Timeline
                    </button>
                    <button type="button"
                            onclick="window.switchDetailTab('system')"
                            class="py-3 border-b-2 transition-colors ${state.detailTab === 'system' ? 'border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400' : 'border-transparent text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-200'}">
                        System Information
                    </button>
                </div>

                <!-- Tab Body Content -->
                <div class="flex-1 overflow-y-auto p-6">
                    ${renderDetailTabContent()}
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    }

    function renderDetailTabContent() {
        const obj = state.selectedObject;
        const rec = state.detailRecord;
        if (!rec) return '';

        if (state.detailTab === 'details') {
            return `
                <form onsubmit="window.saveDetailChanges(event)" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                                ${escapeHtml(obj.label)} Name (${escapeHtml(obj.primary_field || 'name')})
                            </label>
                            ${state.isEditingDetail ? `
                                <input type="text"
                                       id="coEditRecordName"
                                       required
                                       value="${escapeHtml(rec.name)}"
                                       class="w-full px-3 py-2 text-xs bg-zinc-50 dark:bg-zinc-800/80 border border-zinc-300 dark:border-zinc-700 rounded-lg text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                            ` : `
                                <div class="px-3 py-2 text-xs font-bold bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200 dark:border-zinc-700/80 rounded-lg text-zinc-900 dark:text-zinc-100">
                                    ${escapeHtml(rec.name)}
                                </div>
                            `}
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                                Record Owner / Created By
                            </label>
                            <div class="px-3 py-2 text-xs bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200 dark:border-zinc-700/80 rounded-lg text-zinc-700 dark:text-zinc-300">
                                ${escapeHtml(rec.created_by || 'System Admin')}
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                            Notes / Description
                        </label>
                        ${state.isEditingDetail ? `
                            <textarea id="coEditRecordNotes"
                                      rows="3"
                                      class="w-full px-3 py-2 text-xs bg-zinc-50 dark:bg-zinc-800/80 border border-zinc-300 dark:border-zinc-700 rounded-lg text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">${escapeHtml(rec.notes || '')}</textarea>
                        ` : `
                            <div class="px-3 py-2 text-xs bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200 dark:border-zinc-700/80 rounded-lg text-zinc-800 dark:text-zinc-200 whitespace-pre-wrap min-h-16">
                                ${escapeHtml(rec.notes || 'No description or notes provided for this record.')}
                            </div>
                        `}
                    </div>

                    <!-- Dynamic Custom Attributes Block -->
                    <div class="pt-2 border-t border-zinc-200 dark:border-zinc-800">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-zinc-600 dark:text-zinc-400">
                                Extensible Attributes (JSON)
                            </span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            ${renderCustomAttributesList(rec)}
                        </div>
                    </div>

                    ${state.isEditingDetail ? `
                        <div class="flex justify-end pt-3">
                            <button type="submit"
                                    id="coSaveDetailBtn"
                                    class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm transition-colors">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                                <span>Save Changes</span>
                            </button>
                        </div>
                    ` : ''}
                </form>
            `;
        }

        if (state.detailTab === 'related') {
            return `
                <div class="space-y-4">
                    <div class="p-8 text-center bg-zinc-50 dark:bg-zinc-800/40 rounded-xl border border-dashed border-zinc-200 dark:border-zinc-700/80">
                        <svg class="w-8 h-8 mx-auto mb-2 text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 15 6 6m-6-6v4.8m0-4.8h4.8"/><path d="M9 19.8V15m0 0H4.2M9 15l-6 6"/><path d="M15 4.2V9m0 0h4.8M15 9l6-6"/><path d="M9 4.2V9m0 0H4.2M9 9 3 3"/></svg>
                        <h4 class="text-xs font-bold text-zinc-800 dark:text-zinc-200">No Related Records Configured</h4>
                        <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-1 max-w-sm mx-auto">
                            Link child entities, uploaded documents, or related contacts to this record in Page Layout Studio.
                        </p>
                    </div>
                </div>
            `;
        }

        if (state.detailTab === 'activity') {
            return `
                <div class="space-y-4">
                    <div class="flex items-center gap-2 pb-3 border-b border-zinc-200 dark:border-zinc-800">
                        <button type="button" onclick="showFeedback('Call logged for ${escapeHtml(rec.name)}!')" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 text-zinc-800 dark:text-zinc-200 border border-zinc-300 dark:border-zinc-700">
                            Log a Call
                        </button>
                        <button type="button" onclick="showFeedback('New Task assigned to ${escapeHtml(rec.name)}!')" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 text-zinc-800 dark:text-zinc-200 border border-zinc-300 dark:border-zinc-700">
                            New Task
                        </button>
                    </div>

                    <div class="space-y-3 pl-4 border-l-2 border-indigo-200 dark:border-indigo-900/60 ml-2">
                        <div class="relative">
                            <div class="absolute -left-[21px] top-1 w-2.5 h-2.5 rounded-full bg-indigo-600"></div>
                            <div class="text-xs font-semibold text-zinc-800 dark:text-zinc-200">Record Created</div>
                            <div class="text-[11px] text-zinc-500">${rec.created_at ? new Date(rec.created_at).toLocaleString() : 'Recently'} by ${escapeHtml(rec.created_by || 'System Admin')}</div>
                        </div>
                        <div class="relative">
                            <div class="absolute -left-[21px] top-1 w-2.5 h-2.5 rounded-full bg-emerald-600"></div>
                            <div class="text-xs font-semibold text-zinc-800 dark:text-zinc-200">Active State Verified</div>
                            <div class="text-[11px] text-zinc-500">Record validated under multi-tenant dynamic schema.</div>
                        </div>
                    </div>
                </div>
            `;
        }

        if (state.detailTab === 'system') {
            return `
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                    <div class="p-3.5 bg-zinc-50 dark:bg-zinc-800/60 rounded-xl border border-zinc-200 dark:border-zinc-700/80 space-y-2">
                        <div class="text-zinc-500 dark:text-zinc-400 font-semibold uppercase text-[10px]">Record Identifier</div>
                        <div class="font-mono text-zinc-900 dark:text-zinc-100 font-bold">#${escapeHtml(rec.id)}</div>
                    </div>

                    <div class="p-3.5 bg-zinc-50 dark:bg-zinc-800/60 rounded-xl border border-zinc-200 dark:border-zinc-700/80 space-y-2">
                        <div class="text-zinc-500 dark:text-zinc-400 font-semibold uppercase text-[10px]">Schema API Name</div>
                        <div class="font-mono text-zinc-900 dark:text-zinc-100 font-bold">${escapeHtml(obj.api_name)}</div>
                    </div>

                    <div class="p-3.5 bg-zinc-50 dark:bg-zinc-800/60 rounded-xl border border-zinc-200 dark:border-zinc-700/80 space-y-2">
                        <div class="text-zinc-500 dark:text-zinc-400 font-semibold uppercase text-[10px]">Created Date</div>
                        <div class="text-zinc-900 dark:text-zinc-100">${rec.created_at ? new Date(rec.created_at).toLocaleString() : '—'}</div>
                    </div>

                    <div class="p-3.5 bg-zinc-50 dark:bg-zinc-800/60 rounded-xl border border-zinc-200 dark:border-zinc-700/80 space-y-2">
                        <div class="text-zinc-500 dark:text-zinc-400 font-semibold uppercase text-[10px]">Last Modified Date</div>
                        <div class="text-zinc-900 dark:text-zinc-100">${rec.updated_at ? new Date(rec.updated_at).toLocaleString() : '—'}</div>
                    </div>
                </div>
            `;
        }
        return '';
    }

    function renderCustomAttributesList(rec) {
        const standardKeys = new Set(['id', 'custom_object_id', 'name', 'notes', 'created_by', 'updated_by', 'created_at', 'updated_at']);
        const customEntries = Object.entries(rec).filter(([k]) => !standardKeys.has(k));

        if (customEntries.length === 0) {
            return `
                <div class="col-span-full py-2 text-[11px] text-zinc-400 dark:text-zinc-500 italic">
                    No additional custom attributes stored. Extra attributes added via API or integrations appear here automatically.
                </div>
            `;
        }

        return customEntries.map(([k, v]) => `
            <div class="p-2.5 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg border border-zinc-200 dark:border-zinc-700/80">
                <div class="text-[10px] font-mono uppercase text-zinc-400 dark:text-zinc-500 truncate">${escapeHtml(k)}</div>
                <div class="text-xs font-semibold text-zinc-800 dark:text-zinc-200 truncate mt-0.5">${escapeHtml(typeof v === 'object' ? JSON.stringify(v) : String(v))}</div>
            </div>
        `).join('');
    }

    window.switchDetailTab = function (tab) {
        state.detailTab = tab;
        renderRecordDetailModal();
    };

    window.toggleEditDetail = function () {
        state.isEditingDetail = !state.isEditingDetail;
        renderRecordDetailModal();
    };

    window.saveDetailChanges = async function (e) {
        e.preventDefault();
        if (!state.selectedObject || !state.detailRecord) return;
        const name = document.getElementById('coEditRecordName')?.value.trim();
        const notes = document.getElementById('coEditRecordNotes')?.value.trim();
        if (!name) return;

        const btn = document.getElementById('coSaveDetailBtn');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = `<svg class="w-3.5 h-3.5 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg><span>Saving...</span>`;
        }

        try {
            const res = await apiFetch(`/api/custom-objects/${encodeURIComponent(state.selectedObject.id)}/records/${encodeURIComponent(state.detailRecord.id)}`, {
                method: 'PUT',
                body: JSON.stringify({ name, notes })
            });

            state.detailRecord = res.data || res;
            state.isEditingDetail = false;
            showFeedback(`Record '${name}' updated successfully!`);
            renderRecordDetailModal();
            loadRecords(state.selectedObject.id, state.recordPage, state.recordSearch);
        } catch (err) {
            alert(`Error updating record: ${err.message}`);
            if (btn) btn.disabled = false;
        }
    };

    window.cloneCurrentRecord = function () {
        if (!state.detailRecord) return;
        const rec = state.detailRecord;
        const cloneName = `${rec.name || 'Record'} (Copy)`;
        window.closeRecordDetailModal();
        window.openCreateRecordModal(cloneName, { notes: rec.notes || '' });
    };

    window.deleteCurrentRecord = async function () {
        if (!state.selectedObject || !state.detailRecord) return;
        const recName = state.detailRecord.name;
        if (!confirm(`Are you sure you want to permanently delete record '${recName}'?`)) return;

        try {
            await apiFetch(`/api/custom-objects/${encodeURIComponent(state.selectedObject.id)}/records/${encodeURIComponent(state.detailRecord.id)}`, {
                method: 'DELETE'
            });
            window.closeRecordDetailModal();
            showFeedback(`Record '${recName}' deleted.`);
            loadRecords(state.selectedObject.id, state.recordPage, state.recordSearch);
            loadObjects(false);
        } catch (err) {
            alert(`Error deleting record: ${err.message}`);
        }
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

    // --- Main Initializer ---
    window.initCustomObjects = function () {
        const root = getRoot();
        if (!root) return;

        // Read initial SSR data if present
        const ssrDataTag = document.getElementById('coServerData');
        if (ssrDataTag) {
            try {
                const parsed = JSON.parse(ssrDataTag.textContent || '[]');
                if (Array.isArray(parsed) && parsed.length > 0) {
                    state.customObjects = parsed;
                    if (!state.selectedObject) {
                        state.selectedObject = parsed[0];
                    }
                }
            } catch (e) {
                console.error('Error parsing coServerData:', e);
            }
        }

        renderFeedbackToast();
        renderObjectsList();
        renderRecordWorkspace();

        if (state.selectedObject) {
            loadRecords(state.selectedObject.id, 1, '');
        } else {
            loadObjects(true);
        }
    };

    // Auto-init on script evaluation if DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', window.initCustomObjects);
    } else {
        window.initCustomObjects();
    }
})();
