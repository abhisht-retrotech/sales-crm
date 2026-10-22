/**
 * Document & Billing Templates Studio Engine
 * File: public/js/document_templates.js
 *
 * 1:1 Parity with React DocumentTemplatesStudio.tsx
 * - Dual Mode: Templates Catalog & Profile Governance Rights
 * - Visual Split-Screen WYSIWYG Document Designer (Invoice, PO, Quote)
 * - 9 Comprehensive Configuration Tabs (Layout, Branding, Styling, Columns, Watermark, Calculations, Banking, Agreement, Governance)
 * - Real-Time Interactive Paper Sheet Live Preview (A4 & Letter, Zoom 50%-150%, Preset Data Switcher)
 * - Tailwind CSS Aesthetics
 */

(function () {
    'use strict';

    // Guard against multiple declarations
    if (window.__DOC_TEMPLATES_STUDIO_INITIALIZED__) {
        // If already initialized in window, re-run mount
        if (typeof window.initDocumentTemplatesStudio === 'function') {
            window.initDocumentTemplatesStudio();
        }
        return;
    }
    window.__DOC_TEMPLATES_STUDIO_INITIALIZED__ = true;

    // Helper: Safely resolve CSRF Token
    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        if (meta && meta.getAttribute('content')) {
            return meta.getAttribute('content');
        }
        if (window.CSRF_TOKEN) {
            return window.CSRF_TOKEN;
        }
        return '';
    }

    // Helper: Toast Notifications
    function notify(message, type = 'success') {
        if (typeof window.showSpaToast === 'function') {
            window.showSpaToast(message, type);
            return;
        }
        const banner = document.getElementById('doc-studio-feedback');
        if (!banner) return;
        banner.textContent = message;
        banner.className = `px-3.5 py-1.5 rounded-lg text-xs font-semibold shadow-sm transition-all duration-200 ${
            type === 'success' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' : 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200 dark:border-rose-800'
        }`;
        banner.classList.remove('hidden');
        setTimeout(() => {
            banner.classList.add('hidden');
        }, 3500);
    }

    // Font Options matching React
    const FONT_OPTIONS = [
        { value: 'Inter, sans-serif', label: 'Inter (Modern & Clean)' },
        { value: 'Roboto, sans-serif', label: 'Roboto (Standard Corporate)' },
        { value: '"Playfair Display", Georgia, serif', label: 'Playfair Display (Executive Serif)' },
        { value: '"JetBrains Mono", monospace', label: 'JetBrains Mono (Technical / Engineering)' }
    ];

    // Color Palette Presets matching React
    const COLOR_PRESETS = [
        { primary: '#0f172a', accent: '#2563eb', label: 'Classic Corporate Blue' },
        { primary: '#0f172a', accent: '#059669', label: 'Emerald Procurement' },
        { primary: '#1e1b4b', accent: '#7c3aed', label: 'Executive Purple' },
        { primary: '#18181b', accent: '#e11d48', label: 'Ruby Minimal' },
        { primary: '#022c22', accent: '#0d9488', label: 'Teal Caustics' },
        { primary: '#1e293b', accent: '#ea580c', label: 'Amber Industrial' }
    ];

    // Default sample line items for live preview
    const SAMPLE_PREVIEWS = {
        invoice: {
            party: {
                billed_to: { name: 'Apex Industrial Dynamics Ltd', address: '450 Tech Parkway, Suite 200, San Jose, CA', tax_id: 'US-EIN-94-2819201', contact: 'David Thorne' },
                shipped_to: { name: 'Apex Industrial Dynamics Ltd', address: 'Dock Bay 4, 450 Tech Parkway, San Jose, CA' }
            },
            items: [
                { num: 1, sku: 'SRV-CLOUD-ENT', desc: 'Enterprise Cloud Hybrid Platform (Annual License)', hsn: '9983.13', qty: 1, rate: 4200.0, disc: 5, tax: 18, total: 4708.2 },
                { num: 2, sku: 'HW-NODE-RTX', desc: 'High-Performance Workstation Hardware Compute Node', hsn: '8471.50', qty: 2, rate: 1850.0, disc: 0, tax: 18, total: 4366.0 }
            ],
            notes: 'Payment is due within thirty (30) days from issue date. Thank you for your partnership!'
        },
        po: {
            party: {
                billed_to: { name: 'DealSync Global Procurement', address: '100 Silicon Vista, Suite 400, San Jose, CA', tax_id: 'US-EIN-94-2819201', contact: 'Alex Morgan' },
                shipped_to: { name: 'Receiving Dock & Warehouse 3', address: '880 Logistics Loop, Fremont, CA 94538' }
            },
            items: [
                { num: 1, sku: 'COMP-CHIP-A1', desc: 'Embedded Microcontroller Units (Tray of 500)', hsn: '8542.31', qty: 4, rate: 1250.0, disc: 2.5, tax: 18, total: 5752.5 },
                { num: 2, sku: 'CBL-FIBER-10M', desc: 'Industrial Grade Armored Optical Fiber 10m', hsn: '8544.70', qty: 20, rate: 45.0, disc: 0, tax: 18, total: 1062.0 }
            ],
            notes: 'Please confirm dispatch schedule within 48 hours. Items subject to QA dock inspection.'
        },
        quote: {
            party: {
                billed_to: { name: 'Quantum Silicon Components', address: '120 Tech Boulevard, Austin, TX 78701', tax_id: 'US-TAX-55291', contact: 'Dr. Elena Rostova' },
                shipped_to: { name: 'Quantum Silicon Components', address: '120 Tech Boulevard, Austin, TX 78701' }
            },
            items: [
                { num: 1, sku: 'SOW-ENG-DEV', desc: 'Custom ASIC Firmware & Driver Integration Package', hsn: '9983.14', qty: 80, rate: 120.0, disc: 10, tax: 18, total: 10195.2 },
                { num: 2, sku: 'SUPP-24-7', desc: 'Dedicated 24/7 SLA Engineering Support Tier (Q4)', hsn: '9983.15', qty: 1, rate: 3000.0, disc: 0, tax: 18, total: 3540.0 }
            ],
            notes: 'Commercial estimate valid for 30 calendar days from quotation date.'
        }
    };

    // State Container
    const state = {
        templates: [],
        governanceRights: [],
        profiles: [],
        studioSection: 'templates', // 'templates' | 'governance'
        selectedTypeFilter: 'all',  // 'all' | 'invoice' | 'po' | 'quote'
        searchQuery: '',
        isEditing: false,
        activeTemplate: null,
        activeDesignerTab: 'layout', // 'layout' | 'branding' | 'styling' | 'columns' | 'watermark' | 'dynamic' | 'banking' | 'agreement' | 'governance'
        previewZoom: 0.85,
        previewSampleType: 'invoice',
        saving: false,
        savingGovProfileId: null
    };

    // Initialize Studio
    window.initDocumentTemplatesStudio = function () {
        const root = document.getElementById('doc-studio-root');
        if (!root) return;

        // Populate initial data from server payload if present
        if (window.__DOC_TEMPLATES_DATA__) {
            state.templates = Array.isArray(window.__DOC_TEMPLATES_DATA__.templates) ? window.__DOC_TEMPLATES_DATA__.templates : [];
            state.governanceRights = Array.isArray(window.__DOC_TEMPLATES_DATA__.governanceRights) ? window.__DOC_TEMPLATES_DATA__.governanceRights : [];
            state.profiles = Array.isArray(window.__DOC_TEMPLATES_DATA__.profiles) ? window.__DOC_TEMPLATES_DATA__.profiles : [];
        }

        renderApp();
        if (state.templates.length === 0) {
            loadTemplates();
        }
    };

    // Load templates from API
    async function loadTemplates() {
        try {
            let url = '/api/document-templates';
            if (state.selectedTypeFilter !== 'all') {
                url += `?document_type=${encodeURIComponent(state.selectedTypeFilter)}`;
            }
            const res = await fetch(url, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();
            if (data.success && Array.isArray(data.data)) {
                state.templates = data.data;
                renderApp();
            }
        } catch (err) {
            console.error('Failed to load document templates:', err);
        }
    }

    // Load governance rights from API
    async function loadGovernanceRights() {
        try {
            const res = await fetch('/api/document-templates/governance-rights', {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();
            if (data.success && Array.isArray(data.data)) {
                state.governanceRights = data.data;
                renderGovernanceTable();
            }
        } catch (err) {
            console.error('Failed to load governance rights:', err);
        }
    }

    // Main App Renderer
    function renderApp() {
        const root = document.getElementById('doc-studio-root');
        if (!root) return;

        if (state.isEditing && state.activeTemplate) {
            renderDesigner();
        } else {
            renderCatalog();
        }

        if (window.lucide && typeof window.lucide.createIcons === 'function') {
            window.lucide.createIcons();
        }
    }

    // =========================================================================
    // CATALOG VIEW (Templates List + Profile Governance Tab)
    // =========================================================================

    function renderCatalog() {
        const root = document.getElementById('doc-studio-root');
        if (!root) return;

        // KPI Counts
        const totalCount = state.templates.length;
        const invoiceCount = state.templates.filter(t => t.document_type === 'invoice').length;
        const poCount = state.templates.filter(t => t.document_type === 'po').length;
        const quoteCount = state.templates.filter(t => t.document_type === 'quote').length;

        // Filtered Templates
        const filtered = state.templates.filter(t => {
            if (state.selectedTypeFilter !== 'all' && t.document_type !== state.selectedTypeFilter) {
                return false;
            }
            if (!state.searchQuery.trim()) return true;
            const q = state.searchQuery.toLowerCase();
            return (
                (t.name && t.name.toLowerCase().includes(q)) ||
                (t.document_title && t.document_title.toLowerCase().includes(q)) ||
                (t.description && t.description.toLowerCase().includes(q)) ||
                (t.document_type && t.document_type.toLowerCase().includes(q))
            );
        });

        root.innerHTML = `
            <div class="space-y-6">
                <!-- Top Header & KPI Stats -->
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-2">
                    <div>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-blue-500 text-white flex items-center justify-center shadow-md shadow-indigo-500/20">
                                <i data-lucide="file-spreadsheet" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Document &amp; Billing Templates Studio</h1>
                                <p class="text-sm text-slate-500 dark:text-slate-400">Design branded PDF layouts for Tax Invoices, Purchase Orders, and Price Quotations.</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <button id="btn-new-invoice" class="inline-flex items-center gap-2 px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors cursor-pointer">
                            <i data-lucide="plus" class="w-4 h-4"></i> New Invoice
                        </button>
                        <button id="btn-new-po" class="inline-flex items-center gap-2 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors cursor-pointer">
                            <i data-lucide="plus" class="w-4 h-4"></i> New PO
                        </button>
                        <button id="btn-new-quote" class="inline-flex items-center gap-2 px-3.5 py-2 bg-purple-600 hover:bg-purple-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors cursor-pointer">
                            <i data-lucide="plus" class="w-4 h-4"></i> New Quote
                        </button>
                    </div>
                </div>

                <!-- KPI Metric Pills -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-between">
                        <div>
                            <span class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Templates</span>
                            <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1">${totalCount}</div>
                        </div>
                        <div class="p-2.5 bg-slate-100 dark:bg-slate-800 rounded-lg text-slate-600 dark:text-slate-300">
                            <i data-lucide="layers" class="w-5 h-5"></i>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-between">
                        <div>
                            <span class="text-xs font-medium text-blue-600 dark:text-blue-400 uppercase tracking-wider">Invoices</span>
                            <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1">${invoiceCount}</div>
                        </div>
                        <div class="p-2.5 bg-blue-50 dark:bg-blue-950/50 rounded-lg text-blue-600 dark:text-blue-400">
                            <i data-lucide="receipt" class="w-5 h-5"></i>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-between">
                        <div>
                            <span class="text-xs font-medium text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Purchase Orders</span>
                            <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1">${poCount}</div>
                        </div>
                        <div class="p-2.5 bg-emerald-50 dark:bg-emerald-950/50 rounded-lg text-emerald-600 dark:text-emerald-400">
                            <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-between">
                        <div>
                            <span class="text-xs font-medium text-purple-600 dark:text-purple-400 uppercase tracking-wider">Price Quotes</span>
                            <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1">${quoteCount}</div>
                        </div>
                        <div class="p-2.5 bg-purple-50 dark:bg-purple-950/50 rounded-lg text-purple-600 dark:text-purple-400">
                            <i data-lucide="calculator" class="w-5 h-5"></i>
                        </div>
                    </div>
                </div>

                <!-- Navigation Tabs: Templates vs Governance -->
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                    <div class="flex items-center gap-2">
                        <button id="tab-nav-templates" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all cursor-pointer ${
                            state.studioSection === 'templates'
                                ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900 shadow-sm'
                                : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'
                        }">
                            <i data-lucide="file-text" class="w-4 h-4 inline-block mr-1.5"></i> Document Templates
                        </button>
                        <button id="tab-nav-governance" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all cursor-pointer ${
                            state.studioSection === 'governance'
                                ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900 shadow-sm'
                                : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'
                        }">
                            <i data-lucide="shield-check" class="w-4 h-4 inline-block mr-1.5"></i> Security Profiles &amp; Governance
                        </button>
                    </div>

                    <div id="doc-studio-feedback" class="hidden"></div>
                </div>

                <!-- SECTION 1: TEMPLATES CATALOG -->
                <div id="catalog-section" class="${state.studioSection === 'templates' ? '' : 'hidden'} space-y-4">
                    <!-- Filters and Search Bar -->
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-3 bg-white dark:bg-slate-900 p-3 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
                        <div class="flex items-center gap-1.5 bg-slate-100 dark:bg-slate-800/80 p-1 rounded-lg w-full sm:w-auto">
                            ${[
                                { id: 'all', label: 'All Templates' },
                                { id: 'invoice', label: 'Invoices' },
                                { id: 'po', label: 'Purchase Orders' },
                                { id: 'quote', label: 'Quotes' }
                            ].map(filter => `
                                <button data-filter="${filter.id}" class="filter-type-btn flex-1 sm:flex-initial px-3 py-1.5 rounded-md text-xs font-semibold transition-all cursor-pointer ${
                                    state.selectedTypeFilter === filter.id
                                        ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-xs'
                                        : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200'
                                }">
                                    ${filter.label}
                                </button>
                            `).join('')}
                        </div>

                        <div class="relative w-full sm:w-72">
                            <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
                            <input
                                id="catalog-search-input"
                                type="text"
                                value="${state.searchQuery}"
                                placeholder="Search templates..."
                                class="w-full pl-9 pr-3 py-1.5 text-xs bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                        </div>
                    </div>

                    <!-- Template Cards Grid -->
                    ${filtered.length === 0 ? `
                        <div class="text-center py-16 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800">
                            <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 mb-3">
                                <i data-lucide="file-x" class="w-6 h-6"></i>
                            </div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white">No document templates found</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Try clearing your search query or create a new branded template.</p>
                        </div>
                    ` : `
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                            ${filtered.map(tmpl => {
                                const typeBadge = tmpl.document_type === 'invoice'
                                    ? '<span class="px-2 py-0.5 rounded text-[10px] font-bold tracking-wide uppercase bg-blue-100 text-blue-700 dark:bg-blue-950/70 dark:text-blue-300">Invoice</span>'
                                    : (tmpl.document_type === 'po'
                                        ? '<span class="px-2 py-0.5 rounded text-[10px] font-bold tracking-wide uppercase bg-emerald-100 text-emerald-700 dark:bg-emerald-950/70 dark:text-emerald-300">Purchase Order</span>'
                                        : '<span class="px-2 py-0.5 rounded text-[10px] font-bold tracking-wide uppercase bg-purple-100 text-purple-700 dark:bg-purple-950/70 dark:text-purple-300">Price Quote</span>');

                                const isDefault = Boolean(tmpl.is_default);

                                return `
                                    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm hover:shadow-md transition-all flex flex-col justify-between group">
                                        <div>
                                            <!-- Card Header -->
                                            <div class="flex items-start justify-between gap-2 pb-3 border-b border-slate-100 dark:border-slate-800">
                                                <div class="flex items-center gap-2">
                                                    ${typeBadge}
                                                    ${isDefault ? `
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/70 dark:text-amber-300">
                                                            ★ DEFAULT
                                                        </span>
                                                    ` : `
                                                        <button data-action="set-default" data-id="${tmpl.id}" class="text-[11px] font-medium text-slate-400 hover:text-amber-600 cursor-pointer">
                                                            Make Default
                                                        </button>
                                                    `}
                                                </div>

                                                <span class="inline-flex items-center gap-1 text-[11px] font-medium ${tmpl.is_active ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400'}">
                                                    <span class="w-1.5 h-1.5 rounded-full ${tmpl.is_active ? 'bg-emerald-500' : 'bg-slate-400'}"></span>
                                                    ${tmpl.is_active ? 'Active' : 'Draft'}
                                                </span>
                                            </div>

                                            <!-- Card Title & Description -->
                                            <div class="mt-3">
                                                <h3 class="text-base font-bold text-slate-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                                    ${tmpl.name}
                                                </h3>
                                                <div class="text-xs font-semibold text-slate-400 dark:text-slate-500 mt-0.5 tracking-wider uppercase">
                                                    ${tmpl.document_title || 'DOCUMENT'}
                                                </div>
                                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-2 line-clamp-2">
                                                    ${tmpl.description || 'No description provided.'}
                                                </p>
                                            </div>

                                            <!-- Specs Badges -->
                                            <div class="flex flex-wrap gap-1.5 mt-4">
                                                <span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-[10px] font-medium">
                                                    ${tmpl.paper_size || 'A4'} • ${tmpl.orientation || 'Portrait'}
                                                </span>
                                                <span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-[10px] font-medium">
                                                    Theme: ${(tmpl.layout_style || 'modern_teal').replace('_', ' ')}
                                                </span>
                                                ${tmpl.requires_approval ? `
                                                    <span class="px-2 py-0.5 rounded bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300 text-[10px] font-semibold">
                                                        🔒 Approval Required
                                                    </span>
                                                ` : ''}
                                            </div>
                                        </div>

                                        <!-- Card Footer Actions -->
                                        <div class="mt-5 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                                            <button data-action="customize" data-id="${tmpl.id}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-900 hover:bg-slate-800 text-white dark:bg-white dark:text-slate-900 text-xs font-semibold rounded-lg transition-colors cursor-pointer">
                                                <i data-lucide="edit-2" class="w-3.5 h-3.5"></i> Customize Layout
                                            </button>

                                            <div class="flex items-center gap-1">
                                                <button data-action="clone" data-id="${tmpl.id}" title="Duplicate Template" class="p-1.5 text-slate-500 hover:text-slate-700 dark:hover:text-slate-200 rounded-md hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer">
                                                    <i data-lucide="copy" class="w-4 h-4"></i>
                                                </button>
                                                <button data-action="delete" data-id="${tmpl.id}" title="Delete Template" class="p-1.5 text-rose-500 hover:text-rose-700 dark:hover:text-rose-300 rounded-md hover:bg-rose-50 dark:hover:bg-rose-950/40 cursor-pointer">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                `;
                            }).join('')}
                        </div>
                    `}
                </div>

                <!-- SECTION 2: PROFILE GOVERNANCE RIGHTS -->
                <div id="governance-section" class="${state.studioSection === 'governance' ? '' : 'hidden'} space-y-4">
                    <div class="bg-blue-50/70 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-900/60 rounded-xl p-4 text-xs text-blue-800 dark:text-blue-300 flex items-start gap-3">
                        <i data-lucide="info" class="w-4 h-4 mt-0.5 text-blue-600 dark:text-blue-400 shrink-0"></i>
                        <div>
                            <strong class="font-semibold">Profile-Level Document Generation Security Matrix:</strong>
                            Control which user security profiles have authorization to generate formal Tax Invoices, Purchase Orders, or create ledger records directly without underlying deals.
                        </div>
                    </div>

                    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead>
                                    <tr class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 font-semibold uppercase tracking-wider">
                                        <th class="p-4 w-60">Security Profile</th>
                                        <th class="p-4">Opportunity Generation</th>
                                        <th class="p-4">Purchase Deal Generation</th>
                                        <th class="p-4 w-44">Ledger Direct Create</th>
                                        <th class="p-4 w-64">Ledger View Scope</th>
                                        <th class="p-4 w-28 text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody id="governance-table-body" class="divide-y divide-slate-100 dark:divide-slate-800">
                                    <!-- Rendered dynamically -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        `;

        bindCatalogEvents();
        if (state.studioSection === 'governance') {
            renderGovernanceTable();
        }
    }

    // Bind Catalog & Governance Click Handlers
    function bindCatalogEvents() {
        const root = document.getElementById('doc-studio-root');
        if (!root) return;

        // Section Tabs
        document.getElementById('tab-nav-templates')?.addEventListener('click', () => {
            state.studioSection = 'templates';
            renderCatalog();
            if (window.lucide) window.lucide.createIcons();
        });

        document.getElementById('tab-nav-governance')?.addEventListener('click', () => {
            state.studioSection = 'governance';
            renderCatalog();
            if (window.lucide) window.lucide.createIcons();
            if (state.governanceRights.length === 0) {
                loadGovernanceRights();
            }
        });

        // Create Template Buttons
        document.getElementById('btn-new-invoice')?.addEventListener('click', () => handleCreateNew('invoice'));
        document.getElementById('btn-new-po')?.addEventListener('click', () => handleCreateNew('po'));
        document.getElementById('btn-new-quote')?.addEventListener('click', () => handleCreateNew('quote'));

        // Type Filters
        root.querySelectorAll('.filter-type-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                state.selectedTypeFilter = btn.getAttribute('data-filter') || 'all';
                renderCatalog();
                if (window.lucide) window.lucide.createIcons();
            });
        });

        // Search Input
        const searchInput = document.getElementById('catalog-search-input');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                state.searchQuery = e.target.value;
                renderCatalog();
                const reacquired = document.getElementById('catalog-search-input');
                if (reacquired) {
                    reacquired.focus();
                    reacquired.selectionStart = reacquired.selectionEnd = reacquired.value.length;
                }
                if (window.lucide) window.lucide.createIcons();
            });
        }

        // Card Actions
        root.querySelectorAll('[data-action]').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                const action = btn.getAttribute('data-action');
                const id = btn.getAttribute('data-id');
                const targetTmpl = state.templates.find(t => String(t.id) === String(id));
                if (!targetTmpl) return;

                if (action === 'customize') {
                    handleOpenDesigner(targetTmpl);
                } else if (action === 'set-default') {
                    await handleSetDefault(targetTmpl);
                } else if (action === 'clone') {
                    await handleClone(targetTmpl);
                } else if (action === 'delete') {
                    await handleDelete(targetTmpl);
                }
            });
        });
    }

    // Render Governance Table Rows
    function renderGovernanceTable() {
        const tbody = document.getElementById('governance-table-body');
        if (!tbody) return;

        if (state.governanceRights.length === 0) {
            tbody.innerHTML = `<tr><td colspan="6" class="p-8 text-center text-slate-400">Loading profile governance rights...</td></tr>`;
            return;
        }

        tbody.innerHTML = state.governanceRights.map(gov => {
            const isSaving = String(state.savingGovProfileId) === String(gov.profile_id);

            return `
                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition-colors">
                    <!-- Profile Info -->
                    <td class="p-4">
                        <div class="flex items-center gap-2 font-bold text-slate-900 dark:text-white">
                            <i data-lucide="shield" class="w-4 h-4 text-slate-400"></i>
                            <span>${gov.profile_name}</span>
                        </div>
                        <span class="inline-block mt-1 px-2 py-0.5 rounded text-[10px] font-bold ${
                            gov.is_admin ? 'bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300'
                        }">
                            ${gov.is_admin ? 'SYSTEM ADMIN' : 'STANDARD ROLE'}
                        </span>
                    </td>

                    <!-- Opportunity Generation -->
                    <td class="p-4">
                        <label class="inline-flex items-center gap-2 font-semibold text-slate-800 dark:text-slate-200 cursor-pointer">
                            <input
                                type="checkbox"
                                data-gov-profile="${gov.profile_id}"
                                data-gov-field="can_generate_from_opportunity"
                                ${gov.can_generate_from_opportunity ? 'checked' : ''}
                                class="w-4 h-4 text-blue-600 rounded border-slate-300 dark:border-slate-700 focus:ring-blue-500"
                            />
                            <span>Generate Invoice / Quote</span>
                        </label>
                        <div class="mt-2 text-[11px] text-slate-500">
                            Required Stage: <strong class="text-slate-700 dark:text-slate-300">${gov.opportunity_required_stage || 'Closed Won'}</strong>
                        </div>
                    </td>

                    <!-- Purchase Deal Generation -->
                    <td class="p-4">
                        <label class="inline-flex items-center gap-2 font-semibold text-slate-800 dark:text-slate-200 cursor-pointer">
                            <input
                                type="checkbox"
                                data-gov-profile="${gov.profile_id}"
                                data-gov-field="can_generate_from_deal"
                                ${gov.can_generate_from_deal ? 'checked' : ''}
                                class="w-4 h-4 text-blue-600 rounded border-slate-300 dark:border-slate-700 focus:ring-blue-500"
                            />
                            <span>Generate Purchase Order</span>
                        </label>
                        <div class="mt-2 text-[11px] text-slate-500">
                            Required Stage: <strong class="text-slate-700 dark:text-slate-300">${gov.deal_required_stage || 'Closed Won'}</strong>
                        </div>
                    </td>

                    <!-- Ledger Direct Create -->
                    <td class="p-4">
                        <label class="inline-flex items-center gap-2 font-semibold text-slate-800 dark:text-slate-200 cursor-pointer">
                            <input
                                type="checkbox"
                                data-gov-profile="${gov.profile_id}"
                                data-gov-field="can_direct_create_in_ledger"
                                ${gov.can_direct_create_in_ledger ? 'checked' : ''}
                                class="w-4 h-4 text-blue-600 rounded border-slate-300 dark:border-slate-700 focus:ring-blue-500"
                            />
                            <span>Direct Creation</span>
                        </label>
                        <div class="text-[11px] text-slate-400 mt-1">Finance / Ledger Ops</div>
                    </td>

                    <!-- Ledger View Scope -->
                    <td class="p-4 space-y-2">
                        <div>
                            <span class="block text-[10px] uppercase font-semibold text-slate-400 mb-1">Doc Types:</span>
                            <select
                                data-gov-profile="${gov.profile_id}"
                                data-gov-field="allowed_ledger_types"
                                class="w-full px-2.5 py-1 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-md text-slate-800 dark:text-slate-200"
                            >
                                <option value="all" ${gov.allowed_ledger_types === 'all' ? 'selected' : ''}>All Documents (Finance)</option>
                                <option value="invoices_quotes" ${gov.allowed_ledger_types === 'invoices_quotes' ? 'selected' : ''}>Invoices &amp; Quotes (Sales)</option>
                                <option value="purchase_orders" ${gov.allowed_ledger_types === 'purchase_orders' ? 'selected' : ''}>Purchase Orders (Procurement)</option>
                                <option value="none" ${gov.allowed_ledger_types === 'none' ? 'selected' : ''}>None (Hidden)</option>
                            </select>
                        </div>

                        <div>
                            <span class="block text-[10px] uppercase font-semibold text-slate-400 mb-1">Visibility:</span>
                            <select
                                data-gov-profile="${gov.profile_id}"
                                data-gov-field="ledger_record_scope"
                                class="w-full px-2.5 py-1 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-md text-slate-800 dark:text-slate-200"
                            >
                                <option value="all" ${gov.ledger_record_scope === 'all' ? 'selected' : ''}>All Records (Global)</option>
                                <option value="team" ${gov.ledger_record_scope === 'team' ? 'selected' : ''}>Team Records</option>
                                <option value="own" ${gov.ledger_record_scope === 'own' ? 'selected' : ''}>Own Documents Only</option>
                                <option value="none" ${gov.ledger_record_scope === 'none' ? 'selected' : ''}>None</option>
                            </select>
                        </div>
                    </td>

                    <!-- Action / Status -->
                    <td class="p-4 text-center">
                        ${isSaving ? `
                            <span class="text-xs font-semibold text-blue-600 animate-pulse">Saving...</span>
                        ` : `
                            <span class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400 text-xs font-semibold">
                                <i data-lucide="check-circle" class="w-4 h-4"></i> Active
                            </span>
                        `}
                    </td>
                </tr>
            `;
        }).join('');

        // Bind governance inputs
        tbody.querySelectorAll('[data-gov-field]').forEach(el => {
            el.addEventListener('change', async () => {
                const profileId = el.getAttribute('data-gov-profile');
                const field = el.getAttribute('data-gov-field');
                const val = el.type === 'checkbox' ? el.checked : el.value;
                await updateGovernanceRight(profileId, { [field]: val });
            });
        });

        if (window.lucide) window.lucide.createIcons();
    }

    async function updateGovernanceRight(profileId, changes) {
        state.savingGovProfileId = profileId;
        renderGovernanceTable();

        try {
            const res = await fetch(`/api/document-templates/governance-rights/${encodeURIComponent(profileId)}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                },
                body: JSON.stringify(changes)
            });
            const data = await res.json();
            if (data.success) {
                // Update local list
                state.governanceRights = state.governanceRights.map(g =>
                    String(g.profile_id) === String(profileId) ? { ...g, ...changes } : g
                );
                notify('Security governance rights updated successfully!');
            } else {
                notify(data.message || 'Failed to update governance', 'error');
            }
        } catch (err) {
            notify(err.message || 'Network error updating governance', 'error');
        } finally {
            state.savingGovProfileId = null;
            renderGovernanceTable();
        }
    }

    // Handlers for Set Default, Clone, Delete
    async function handleSetDefault(tmpl) {
        try {
            const res = await fetch(`/api/document-templates/${encodeURIComponent(tmpl.id)}/set-default`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                }
            });
            const data = await res.json();
            if (data.success) {
                notify(`"${tmpl.name}" is now the default ${tmpl.document_type.toUpperCase()} template.`);
                await loadTemplates();
            } else {
                notify(data.message || 'Failed to set default', 'error');
            }
        } catch (err) {
            notify(err.message || 'Error setting default', 'error');
        }
    }

    async function handleClone(tmpl) {
        try {
            const res = await fetch(`/api/document-templates/${encodeURIComponent(tmpl.id)}/clone`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                }
            });
            const data = await res.json();
            if (data.success) {
                notify(`Created duplicate of "${tmpl.name}"`);
                await loadTemplates();
            } else {
                notify(data.message || 'Failed to clone template', 'error');
            }
        } catch (err) {
            notify(err.message || 'Error cloning template', 'error');
        }
    }

    async function handleDelete(tmpl) {
        if (!confirm(`Are you sure you want to delete template "${tmpl.name}"?`)) return;

        try {
            const res = await fetch(`/api/document-templates/${encodeURIComponent(tmpl.id)}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                }
            });
            const data = await res.json();
            if (data.success) {
                notify('Template deleted successfully');
                await loadTemplates();
            } else {
                notify(data.message || 'Failed to delete template', 'error');
            }
        } catch (err) {
            notify(err.message || 'Error deleting template', 'error');
        }
    }

    // =========================================================================
    // VISUAL DESIGNER VIEW (Left Configuration + Right WYSIWYG Paper Preview)
    // =========================================================================

    function handleOpenDesigner(tmpl) {
        state.activeTemplate = JSON.parse(JSON.stringify(tmpl));
        state.previewSampleType = tmpl.document_type || 'invoice';
        state.isEditing = true;
        state.activeDesignerTab = 'layout';
        renderApp();
    }

    function handleCreateNew(type = 'invoice') {
        const defaultCols = [
            { key: 'item_number', label: '#', visible: true, align: 'center', width: '5%' },
            { key: 'sku', label: 'Item / SKU', visible: true, align: 'left', width: '15%' },
            { key: 'description', label: 'Description', visible: true, align: 'left', width: '33%' },
            { key: 'hsn_sac_code', label: 'HSN/SAC', visible: true, align: 'center', width: '9%' },
            { key: 'quantity', label: 'Qty', visible: true, align: 'right', width: '8%' },
            { key: 'unit_price', label: 'Rate', visible: true, align: 'right', width: '10%' },
            { key: 'discount_rate', label: 'Disc %', visible: true, align: 'right', width: '7%' },
            { key: 'tax_rate', label: 'Tax %', visible: true, align: 'right', width: '6%' },
            { key: 'total_amount', label: 'Amount', visible: true, align: 'right', width: '12%' }
        ];

        state.activeTemplate = {
            id: null,
            name: `New ${type.toUpperCase()} Template`,
            document_type: type,
            description: `Custom branded template for ${type}s`,
            is_default: false,
            is_active: true,
            paper_size: 'A4',
            orientation: 'portrait',
            margin_size: 'standard',
            show_shipping_address: true,
            font_family: 'Inter, sans-serif',
            primary_color: '#0f172a',
            accent_color: type === 'invoice' ? '#0d9488' : type === 'po' ? '#2563eb' : '#7c3aed',
            table_theme: 'zebra',
            layout_style: type === 'invoice' ? 'modern_teal' : type === 'po' ? 'classic_navy' : 'minimal_slate',
            logo_url: '',
            logo_position: 'left',
            logo_height: 48,
            company_name: 'DealSync Enterprise',
            company_address: '100 Silicon Vista, Suite 400, San Jose, CA 95110',
            company_tax_id: 'US-EIN-94-2819201',
            company_phone: '+1 (555) 492-0199',
            company_email: 'billing@dealsync.io',
            company_website: 'https://dealsync.io',
            document_title: type === 'invoice' ? 'TAX INVOICE' : type === 'po' ? 'PURCHASE ORDER' : 'PRICE QUOTATION',
            columns_config: defaultCols,
            dynamic_fields_config: {
                enable_order_discount: true,
                discount_type: 'percentage',
                default_discount_rate: 0,
                enable_tax_summary: true,
                tax_mode: 'split_gst',
                default_tax_rate: 18,
                enable_shipping: true,
                enable_round_off: true,
                enable_amount_in_words: true,
                custom_adjustments: []
            },
            watermark_config: {
                enabled: false,
                type: 'text',
                text: 'CONFIDENTIAL',
                fontSize: 54,
                color: '#ef4444',
                opacity: 0.15,
                rotation: -30,
                position: 'center',
                borderStyle: 'solid'
            },
            payment_terms_default: 'Net 30',
            bank_details: "Silicon Valley Commercial Bank\nA/C Name: DealSync Enterprise\nA/C: 9820-4102-9841\nIFSC/Routing: SVCB0002910",
            qr_code_url: '',
            notes_default: 'Thank you for partnering with us!',
            terms_conditions: 'Payment due within specified credit terms. Late fees apply.',
            show_signatory: true,
            signatory_title: 'Chief Financial Officer',
            signatory_name: 'Finance Directorate',
            signature_image_url: '',
            agreement_pages: [
                {
                    id: 'agree-' + Date.now(),
                    title: 'Standard Terms & Conditions',
                    content_html: '<h4>1. Scope & Execution</h4><p>This document governs the terms of commercial execution agreed upon between the parties.</p>',
                    require_client_signature: true
                }
            ],
            save_to_files_destination: 'files_public',
            allow_direct_email: true,
            requires_approval: false,
            approval_process_id: null
        };

        state.previewSampleType = type;
        state.isEditing = true;
        state.activeDesignerTab = 'layout';
        renderApp();
    }

    function renderDesigner() {
        const root = document.getElementById('doc-studio-root');
        if (!root) return;

        const tmpl = state.activeTemplate;
        if (!tmpl) return;

        const tabs = [
            { id: 'layout', label: 'Layout', icon: 'sliders' },
            { id: 'branding', label: 'Branding', icon: 'building-2' },
            { id: 'styling', label: 'Theme', icon: 'palette' },
            { id: 'columns', label: 'Columns', icon: 'columns' },
            { id: 'watermark', label: 'Watermark', icon: 'stamp' },
            { id: 'dynamic', label: 'Calculations', icon: 'calculator' },
            { id: 'banking', label: 'Banking', icon: 'landmark' },
            { id: 'agreement', label: 'Agreement', icon: 'file-check' },
            { id: 'governance', label: 'Governance', icon: 'shield-check' }
        ];

        root.innerHTML = `
            <div class="flex flex-col h-[calc(100vh-100px)] min-h-[700px] bg-slate-100 dark:bg-slate-950 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
                <!-- Designer Top Header -->
                <div class="bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 px-5 py-3.5 flex items-center justify-between shrink-0 z-10">
                    <div class="flex items-center gap-3">
                        <button id="btn-back-catalog" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-200 cursor-pointer shadow-xs transition-colors">
                            <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Catalog
                        </button>

                        <div class="h-5 w-px bg-slate-200 dark:bg-slate-700"></div>

                        <div class="flex items-center gap-2">
                            <div class="p-1.5 bg-blue-50 dark:bg-blue-950/60 rounded-md text-blue-600 dark:text-blue-400">
                                <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                            </div>
                            <h2 class="text-base font-bold text-slate-900 dark:text-white">${tmpl.name || 'Untitled Template'}</h2>
                            ${tmpl.is_default ? `
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/70 dark:text-amber-300">
                                    ★ DEFAULT ${tmpl.document_type.toUpperCase()}
                                </span>
                            ` : ''}
                        </div>
                    </div>

                    <!-- Header Right Controls -->
                    <div class="flex items-center gap-3">
                        <div id="doc-studio-feedback" class="hidden"></div>

                        <button id="btn-toggle-default" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold border cursor-pointer transition-colors ${
                            tmpl.is_default
                                ? 'bg-amber-50 text-amber-800 border-amber-300 dark:bg-amber-950/50 dark:text-amber-300 dark:border-amber-800'
                                : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:bg-slate-50'
                        }">
                            <i data-lucide="star" class="w-3.5 h-3.5 ${tmpl.is_default ? 'fill-amber-500 text-amber-500' : ''}"></i>
                            ${tmpl.is_default ? 'Default Template' : 'Set as Default'}
                        </button>

                        <button id="btn-save-template" class="inline-flex items-center gap-2 px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow-sm cursor-pointer transition-colors">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span>${state.saving ? 'Saving...' : 'Save Template'}</span>
                        </button>
                    </div>
                </div>

                <!-- Split Workspace -->
                <div class="flex flex-1 overflow-hidden">
                    <!-- LEFT COLUMN: CONFIGURATION CONTROLS (440px) -->
                    <div class="w-[440px] min-w-[380px] max-w-[480px] bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 flex flex-col shrink-0 overflow-hidden">
                        <!-- Configuration Tabs Bar -->
                        <div class="flex items-center gap-1 p-2 bg-slate-50 dark:bg-slate-950/60 border-b border-slate-200 dark:border-slate-800 overflow-x-auto scrollbar-thin">
                            ${tabs.map(tab => `
                                <button data-tab="${tab.id}" class="tab-btn-designer flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium whitespace-nowrap transition-all cursor-pointer ${
                                    state.activeDesignerTab === tab.id
                                        ? 'bg-white dark:bg-slate-800 text-blue-600 dark:text-blue-400 font-bold shadow-xs'
                                        : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 hover:bg-slate-200/50 dark:hover:bg-slate-800/50'
                                }">
                                    <i data-lucide="${tab.icon}" class="w-3.5 h-3.5"></i> ${tab.label}
                                </button>
                            `).join('')}
                        </div>

                        <!-- Configuration Tab Body (Scrollable) -->
                        <div id="designer-tab-content" class="flex-1 overflow-y-auto p-5 space-y-5 text-xs text-slate-700 dark:text-slate-300">
                            <!-- Injected by renderDesignerTabContent() -->
                        </div>
                    </div>

                    <!-- RIGHT COLUMN: LIVE WYSIWYG PREVIEW -->
                    <div class="flex-1 bg-slate-200/60 dark:bg-slate-950 flex flex-col overflow-hidden relative">
                        <!-- Preview Toolbar -->
                        <div class="bg-white/95 dark:bg-slate-900/95 backdrop-blur-sm border-b border-slate-200 dark:border-slate-800 px-4 py-2 flex items-center justify-between shrink-0 z-10">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Sample Data:</span>
                                <select id="preview-sample-selector" class="px-2.5 py-1 text-xs font-semibold bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-slate-200">
                                    <option value="invoice" ${state.previewSampleType === 'invoice' ? 'selected' : ''}>Cloud Infrastructure Invoice ($9,074)</option>
                                    <option value="po" ${state.previewSampleType === 'po' ? 'selected' : ''}>Electronic Components PO ($6,814)</option>
                                    <option value="quote" ${state.previewSampleType === 'quote' ? 'selected' : ''}>ASIC Engineering Quotation ($13,735)</option>
                                </select>
                            </div>

                            <!-- Zoom and Actions -->
                            <div class="flex items-center gap-1.5">
                                <button id="btn-zoom-out" title="Zoom Out" class="p-1.5 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded border border-slate-200 dark:border-slate-700 cursor-pointer">
                                    <i data-lucide="zoom-out" class="w-3.5 h-3.5"></i>
                                </button>
                                <span id="preview-zoom-label" class="text-xs font-bold text-slate-700 dark:text-slate-300 min-w-10 text-center">
                                    ${Math.round(state.previewZoom * 100)}%
                                </span>
                                <button id="btn-zoom-in" title="Zoom In" class="p-1.5 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded border border-slate-200 dark:border-slate-700 cursor-pointer">
                                    <i data-lucide="zoom-in" class="w-3.5 h-3.5"></i>
                                </button>
                                <button id="btn-zoom-reset" title="Reset Zoom (Fit)" class="p-1.5 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded border border-slate-200 dark:border-slate-700 cursor-pointer ml-1">
                                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                                </button>
                                <button id="btn-print-preview" title="Print Document" class="p-1.5 text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-950/50 rounded border border-blue-200 dark:border-blue-800 cursor-pointer ml-2">
                                    <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Scrollable Paper Container -->
                        <div class="flex-1 overflow-y-auto p-8 flex flex-col items-center gap-8" id="preview-scroll-viewport">
                            <div id="preview-zoom-wrapper" style="transform: scale(${state.previewZoom}); transform-origin: top center; transition: transform 0.15s ease;">
                                <!-- Live Document Paper Sheet Rendered Here -->
                                <div id="live-paper-sheet"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        bindDesignerEvents();
        renderDesignerTabContent();
        renderLivePaperSheet();
    }

    // Bind Designer Top & General Events
    function bindDesignerEvents() {
        document.getElementById('btn-back-catalog')?.addEventListener('click', () => {
            state.isEditing = false;
            state.activeTemplate = null;
            renderCatalog();
            if (window.lucide) window.lucide.createIcons();
        });

        document.getElementById('btn-toggle-default')?.addEventListener('click', () => {
            if (!state.activeTemplate) return;
            state.activeTemplate.is_default = !state.activeTemplate.is_default;
            renderDesigner();
            if (window.lucide) window.lucide.createIcons();
        });

        document.getElementById('btn-save-template')?.addEventListener('click', saveActiveTemplate);

        // Tab Switching
        document.querySelectorAll('.tab-btn-designer').forEach(btn => {
            btn.addEventListener('click', () => {
                state.activeDesignerTab = btn.getAttribute('data-tab');
                renderDesigner();
                if (window.lucide) window.lucide.createIcons();
            });
        });

        // Zoom Controls
        document.getElementById('btn-zoom-in')?.addEventListener('click', () => {
            state.previewZoom = Math.min(1.5, state.previewZoom + 0.1);
            updateZoom();
        });

        document.getElementById('btn-zoom-out')?.addEventListener('click', () => {
            state.previewZoom = Math.max(0.5, state.previewZoom - 0.1);
            updateZoom();
        });

        document.getElementById('btn-zoom-reset')?.addEventListener('click', () => {
            state.previewZoom = 0.85;
            updateZoom();
        });

        document.getElementById('btn-print-preview')?.addEventListener('click', () => {
            window.print();
        });

        document.getElementById('preview-sample-selector')?.addEventListener('change', (e) => {
            state.previewSampleType = e.target.value;
            renderLivePaperSheet();
        });
    }

    function updateZoom() {
        const wrapper = document.getElementById('preview-zoom-wrapper');
        const label = document.getElementById('preview-zoom-label');
        if (wrapper) wrapper.style.transform = `scale(${state.previewZoom})`;
        if (label) label.textContent = `${Math.round(state.previewZoom * 100)}%`;
    }

    // Save Active Template
    async function saveActiveTemplate() {
        if (!state.activeTemplate) return;
        state.saving = true;
        const btn = document.getElementById('btn-save-template');
        if (btn) btn.innerHTML = `<span class="animate-spin inline-block mr-1">↻</span> Saving...`;

        try {
            const isNew = !state.activeTemplate.id;
            const url = isNew ? '/api/document-templates' : `/api/document-templates/${encodeURIComponent(state.activeTemplate.id)}`;
            const method = isNew ? 'POST' : 'PUT';

            const res = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                },
                body: JSON.stringify(state.activeTemplate)
            });

            const data = await res.json();
            if (data.success) {
                notify('Document template saved successfully!');
                if (data.data) {
                    state.activeTemplate = data.data;
                }
                await loadTemplates();
                renderDesigner();
            } else {
                notify(data.message || 'Failed to save template', 'error');
            }
        } catch (err) {
            notify(err.message || 'Error saving template', 'error');
        } finally {
            state.saving = false;
            if (btn) btn.innerHTML = `<i data-lucide="check" class="w-4 h-4"></i> Save Template`;
            if (window.lucide) window.lucide.createIcons();
        }
    }

    // =========================================================================
    // 9 CONFIGURATION TABS IMPLEMENTATION
    // =========================================================================

    function renderDesignerTabContent() {
        const container = document.getElementById('designer-tab-content');
        if (!container) return;

        const tmpl = state.activeTemplate;
        if (!tmpl) return;

        const tab = state.activeDesignerTab;

        if (tab === 'layout') {
            container.innerHTML = `
                <div class="space-y-4">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Page Geometry &amp; Document Type</h3>

                    <div>
                        <label class="block font-semibold mb-1 text-slate-700 dark:text-slate-300">Template Name</label>
                        <input type="text" id="inp-tmpl-name" value="${tmpl.name || ''}" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white" />
                    </div>

                    <div>
                        <label class="block font-semibold mb-1 text-slate-700 dark:text-slate-300">Document Type</label>
                        <select id="sel-tmpl-type" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                            <option value="invoice" ${tmpl.document_type === 'invoice' ? 'selected' : ''}>📄 Tax Invoice (Billing / Receivables)</option>
                            <option value="po" ${tmpl.document_type === 'po' ? 'selected' : ''}>📦 Purchase Order (Procurement / Vendors)</option>
                            <option value="quote" ${tmpl.document_type === 'quote' ? 'selected' : ''}>💼 Price Quotation / Estimate (Sales)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold mb-1.5 text-slate-700 dark:text-slate-300">Design Layout Architecture</label>
                        <div class="space-y-2">
                            ${[
                                { id: 'modern_teal', name: 'Modern Executive Teal', desc: 'Teal accents, solid dark header, dual rounded dock cards, clean totals block.', color: '#0d9488' },
                                { id: 'classic_navy', name: 'Classic Corporate Navy', desc: 'Navy header bar (#1e3a8a), structured box borders, formal corporate look.', color: '#1e3a8a' },
                                { id: 'minimal_slate', name: 'Clean Minimalist Slate', desc: 'Subtle hairline rules, slate typography, airy modern whitespace.', color: '#475569' },
                                { id: 'bold_stripe', name: 'Industrial Bold Stripe', desc: 'Left vertical brand stripe, high-contrast dark cards, prominent status pills.', color: '#ea580c' },
                                { id: 'compact_grid', name: 'Compact Dense Grid', desc: 'High-density line item table, boxed borders, ideal for 20+ item orders.', color: '#0f172a' }
                            ].map(l => `
                                <label class="flex items-start gap-3 p-3 rounded-lg border cursor-pointer transition-all ${
                                    (tmpl.layout_style || 'modern_teal') === l.id
                                        ? 'border-blue-500 bg-blue-50/40 dark:bg-blue-950/30'
                                        : 'border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50'
                                }">
                                    <input type="radio" name="layout_style" value="${l.id}" ${(tmpl.layout_style || 'modern_teal') === l.id ? 'checked' : ''} class="mt-1 text-blue-600" />
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <span class="w-3 h-3 rounded-full" style="background-color: ${l.color}"></span>
                                            <strong class="text-xs text-slate-900 dark:text-white">${l.name}</strong>
                                        </div>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">${l.desc}</p>
                                    </div>
                                </label>
                            `).join('')}
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold mb-1 text-slate-700 dark:text-slate-300">Paper Size</label>
                            <select id="sel-paper-size" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                                <option value="A4" ${tmpl.paper_size === 'A4' ? 'selected' : ''}>A4 (210 × 297 mm)</option>
                                <option value="Letter" ${tmpl.paper_size === 'Letter' ? 'selected' : ''}>US Letter (8.5 × 11 in)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold mb-1 text-slate-700 dark:text-slate-300">Orientation</label>
                            <select id="sel-orientation" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                                <option value="portrait" ${tmpl.orientation === 'portrait' ? 'selected' : ''}>Portrait (Vertical)</option>
                                <option value="landscape" ${tmpl.orientation === 'landscape' ? 'selected' : ''}>Landscape (Horizontal)</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold mb-1 text-slate-700 dark:text-slate-300">Margins</label>
                        <select id="sel-margin-size" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                            <option value="compact" ${tmpl.margin_size === 'compact' ? 'selected' : ''}>Compact (32px)</option>
                            <option value="standard" ${tmpl.margin_size === 'standard' ? 'selected' : ''}>Standard (48px)</option>
                            <option value="spacious" ${tmpl.margin_size === 'spacious' ? 'selected' : ''}>Spacious (64px)</option>
                        </select>
                    </div>

                    <div class="pt-2 border-t border-slate-100 dark:border-slate-800 space-y-2">
                        <label class="flex items-center gap-2 cursor-pointer font-medium">
                            <input type="checkbox" id="chk-shipping-addr" ${Boolean(tmpl.show_shipping_address) ? 'checked' : ''} class="w-4 h-4 text-blue-600 rounded" />
                            <span>Show Shipping / Receiving Dock Address Card</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer font-medium">
                            <input type="checkbox" id="chk-direct-email" ${Boolean(tmpl.allow_direct_email) ? 'checked' : ''} class="w-4 h-4 text-blue-600 rounded" />
                            <span>Allow Direct Email Dispatch to Clients</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer font-medium">
                            <input type="checkbox" id="chk-requires-approval" ${Boolean(tmpl.requires_approval) ? 'checked' : ''} class="w-4 h-4 text-blue-600 rounded" />
                            <span>Require Formal Manager Approval Before Dispatch</span>
                        </label>
                    </div>
                </div>
            `;

            document.getElementById('inp-tmpl-name')?.addEventListener('input', (e) => {
                tmpl.name = e.target.value;
                const headerTitle = document.querySelector('h2.text-base');
                if (headerTitle) headerTitle.textContent = tmpl.name;
            });

            document.getElementById('sel-tmpl-type')?.addEventListener('change', (e) => {
                tmpl.document_type = e.target.value;
                state.previewSampleType = tmpl.document_type;
                tmpl.document_title = tmpl.document_type === 'invoice' ? 'TAX INVOICE' : tmpl.document_type === 'po' ? 'PURCHASE ORDER' : 'PRICE QUOTATION';
                renderLivePaperSheet();
            });

            container.querySelectorAll('input[name="layout_style"]').forEach(r => {
                r.addEventListener('change', (e) => {
                    tmpl.layout_style = e.target.value;
                    renderDesignerTabContent();
                    renderLivePaperSheet();
                });
            });

            document.getElementById('sel-paper-size')?.addEventListener('change', (e) => {
                tmpl.paper_size = e.target.value;
                renderLivePaperSheet();
            });

            document.getElementById('sel-orientation')?.addEventListener('change', (e) => {
                tmpl.orientation = e.target.value;
                renderLivePaperSheet();
            });

            document.getElementById('sel-margin-size')?.addEventListener('change', (e) => {
                tmpl.margin_size = e.target.value;
                renderLivePaperSheet();
            });

            document.getElementById('chk-shipping-addr')?.addEventListener('change', (e) => {
                tmpl.show_shipping_address = e.target.checked;
                renderLivePaperSheet();
            });

            document.getElementById('chk-direct-email')?.addEventListener('change', (e) => {
                tmpl.allow_direct_email = e.target.checked;
            });

            document.getElementById('chk-requires-approval')?.addEventListener('change', (e) => {
                tmpl.requires_approval = e.target.checked;
            });
        }
        else if (tab === 'branding') {
            container.innerHTML = `
                <div class="space-y-4">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Organization Branding &amp; Legal Entity</h3>

                    <div>
                        <label class="block font-semibold mb-1 text-slate-700 dark:text-slate-300">Company Legal Name</label>
                        <input type="text" id="inp-comp-name" value="${tmpl.company_name || ''}" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white" />
                    </div>

                    <div>
                        <label class="block font-semibold mb-1 text-slate-700 dark:text-slate-300">Logo Image URL</label>
                        <div class="flex gap-2">
                            <input type="text" id="inp-logo-url" value="${tmpl.logo_url || ''}" placeholder="https://example.com/logo.png" class="flex-1 px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white" />
                            <label class="px-3 py-1.5 bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-xs font-semibold cursor-pointer hover:bg-slate-200">
                                Upload
                                <input type="file" id="inp-logo-file" accept="image/*" class="hidden" />
                            </label>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold mb-1 text-slate-700 dark:text-slate-300">Logo Alignment</label>
                            <select id="sel-logo-pos" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                                <option value="left" ${tmpl.logo_position === 'left' ? 'selected' : ''}>Left</option>
                                <option value="center" ${tmpl.logo_position === 'center' ? 'selected' : ''}>Center</option>
                                <option value="right" ${tmpl.logo_position === 'right' ? 'selected' : ''}>Right</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold mb-1 text-slate-700 dark:text-slate-300">Logo Height: <span id="lbl-logo-height">${tmpl.logo_height || 48}px</span></label>
                            <input type="range" id="rng-logo-height" min="24" max="96" value="${tmpl.logo_height || 48}" class="w-full" />
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold mb-1 text-slate-700 dark:text-slate-300">Company Address</label>
                        <textarea id="inp-comp-addr" rows="2" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">${tmpl.company_address || ''}</textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold mb-1 text-slate-700 dark:text-slate-300">Tax ID / GSTIN / EIN</label>
                            <input type="text" id="inp-comp-taxid" value="${tmpl.company_tax_id || ''}" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white" />
                        </div>
                        <div>
                            <label class="block font-semibold mb-1 text-slate-700 dark:text-slate-300">Billing Phone</label>
                            <input type="text" id="inp-comp-phone" value="${tmpl.company_phone || ''}" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold mb-1 text-slate-700 dark:text-slate-300">Billing Email</label>
                            <input type="text" id="inp-comp-email" value="${tmpl.company_email || ''}" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white" />
                        </div>
                        <div>
                            <label class="block font-semibold mb-1 text-slate-700 dark:text-slate-300">Company Website</label>
                            <input type="text" id="inp-comp-web" value="${tmpl.company_website || ''}" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white" />
                        </div>
                    </div>
                </div>
            `;

            const bindLive = (id, prop) => {
                document.getElementById(id)?.addEventListener('input', (e) => {
                    tmpl[prop] = e.target.value;
                    renderLivePaperSheet();
                });
            };

            bindLive('inp-comp-name', 'company_name');
            bindLive('inp-logo-url', 'logo_url');
            bindLive('inp-comp-addr', 'company_address');
            bindLive('inp-comp-taxid', 'company_tax_id');
            bindLive('inp-comp-phone', 'company_phone');
            bindLive('inp-comp-email', 'company_email');
            bindLive('inp-comp-web', 'company_website');

            document.getElementById('sel-logo-pos')?.addEventListener('change', (e) => {
                tmpl.logo_position = e.target.value;
                renderLivePaperSheet();
            });

            document.getElementById('rng-logo-height')?.addEventListener('input', (e) => {
                tmpl.logo_height = parseInt(e.target.value);
                const lbl = document.getElementById('lbl-logo-height');
                if (lbl) lbl.textContent = `${tmpl.logo_height}px`;
                renderLivePaperSheet();
            });

            document.getElementById('inp-logo-file')?.addEventListener('change', (e) => {
                const file = e.target.files?.[0];
                if (!file) return;
                const reader = new FileReader();
                reader.onload = (ev) => {
                    tmpl.logo_url = ev.target?.result;
                    const urlInput = document.getElementById('inp-logo-url');
                    if (urlInput) urlInput.value = tmpl.logo_url;
                    renderLivePaperSheet();
                };
                reader.readAsDataURL(file);
            });
        }
        else if (tab === 'styling') {
            container.innerHTML = `
                <div class="space-y-4">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Color Palettes, Typography &amp; Themes</h3>

                    <div>
                        <label class="block font-semibold mb-1 text-slate-700 dark:text-slate-300">Document Title</label>
                        <input type="text" id="inp-doc-title" value="${tmpl.document_title || ''}" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white" />
                    </div>

                    <div>
                        <label class="block font-semibold mb-1 text-slate-700 dark:text-slate-300">Font Family</label>
                        <select id="sel-font-family" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                            ${FONT_OPTIONS.map(f => `
                                <option value="${f.value}" ${(tmpl.font_family || '').includes(f.value.split(',')[0]) ? 'selected' : ''}>${f.label}</option>
                            `).join('')}
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold mb-2 text-slate-700 dark:text-slate-300">Curated Color Presets</label>
                        <div class="grid grid-cols-2 gap-2">
                            ${COLOR_PRESETS.map((p, idx) => `
                                <button type="button" data-preset-idx="${idx}" class="preset-color-btn p-2 rounded-lg border border-slate-200 dark:border-slate-700 hover:border-blue-400 flex items-center gap-2 text-left cursor-pointer transition-all">
                                    <span class="w-4 h-4 rounded-full border border-black/10 shrink-0" style="background-color: ${p.accent}"></span>
                                    <span class="text-[11px] font-semibold text-slate-700 dark:text-slate-300 truncate">${p.label}</span>
                                </button>
                            `).join('')}
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 pt-2">
                        <div>
                            <label class="block font-semibold mb-1 text-slate-700 dark:text-slate-300">Primary Color (Headers)</label>
                            <div class="flex items-center gap-2">
                                <input type="color" id="inp-pri-color-picker" value="${tmpl.primary_color || '#0f172a'}" class="w-8 h-8 rounded border-0 cursor-pointer" />
                                <input type="text" id="inp-pri-color" value="${tmpl.primary_color || '#0f172a'}" class="w-full px-2 py-1 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded text-xs font-mono" />
                            </div>
                        </div>
                        <div>
                            <label class="block font-semibold mb-1 text-slate-700 dark:text-slate-300">Accent Color (Highlights)</label>
                            <div class="flex items-center gap-2">
                                <input type="color" id="inp-acc-color-picker" value="${tmpl.accent_color || '#2563eb'}" class="w-8 h-8 rounded border-0 cursor-pointer" />
                                <input type="text" id="inp-acc-color" value="${tmpl.accent_color || '#2563eb'}" class="w-full px-2 py-1 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded text-xs font-mono" />
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold mb-1 text-slate-700 dark:text-slate-300">Table Theme</label>
                        <select id="sel-table-theme" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                            <option value="zebra" ${tmpl.table_theme === 'zebra' ? 'selected' : ''}>Zebra Striped Rows (Alternating White/Slate)</option>
                            <option value="grid" ${tmpl.table_theme === 'grid' ? 'selected' : ''}>Full Boxed Grid Lines (Spreadsheet Style)</option>
                            <option value="minimal" ${tmpl.table_theme === 'minimal' ? 'selected' : ''}>Minimal Hairline Dividers (Clean Modern)</option>
                            <option value="borderless" ${tmpl.table_theme === 'borderless' ? 'selected' : ''}>Borderless Flow</option>
                        </select>
                    </div>
                </div>
            `;

            document.getElementById('inp-doc-title')?.addEventListener('input', (e) => {
                tmpl.document_title = e.target.value;
                renderLivePaperSheet();
            });

            document.getElementById('sel-font-family')?.addEventListener('change', (e) => {
                tmpl.font_family = e.target.value;
                renderLivePaperSheet();
            });

            container.querySelectorAll('.preset-color-btn').forEach(b => {
                b.addEventListener('click', () => {
                    const idx = parseInt(b.getAttribute('data-preset-idx'));
                    const preset = COLOR_PRESETS[idx];
                    if (!preset) return;
                    tmpl.primary_color = preset.primary;
                    tmpl.accent_color = preset.accent;
                    renderDesignerTabContent();
                    renderLivePaperSheet();
                });
            });

            const bindColor = (pickerId, textId, prop) => {
                const picker = document.getElementById(pickerId);
                const text = document.getElementById(textId);
                picker?.addEventListener('input', (e) => {
                    tmpl[prop] = e.target.value;
                    if (text) text.value = e.target.value;
                    renderLivePaperSheet();
                });
                text?.addEventListener('input', (e) => {
                    tmpl[prop] = e.target.value;
                    if (picker) picker.value = e.target.value;
                    renderLivePaperSheet();
                });
            };

            bindColor('inp-pri-color-picker', 'inp-pri-color', 'primary_color');
            bindColor('inp-acc-color-picker', 'inp-acc-color', 'accent_color');

            document.getElementById('sel-table-theme')?.addEventListener('change', (e) => {
                tmpl.table_theme = e.target.value;
                renderLivePaperSheet();
            });
        }
        else if (tab === 'columns') {
            const cols = Array.isArray(tmpl.columns_config) ? tmpl.columns_config : [];

            container.innerHTML = `
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Line Item Table Columns</h3>
                            <p class="text-[11px] text-slate-500">Configure visibility, width %, headers, and ordering.</p>
                        </div>
                        <button id="btn-add-column" class="px-2.5 py-1 bg-blue-50 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400 border border-blue-200 dark:border-blue-800 rounded-lg text-xs font-semibold hover:bg-blue-100 cursor-pointer">
                            + Add Column
                        </button>
                    </div>

                    <div class="space-y-2">
                        ${cols.map((col, idx) => `
                            <div class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200 dark:border-slate-700/60 space-y-2">
                                <div class="flex items-center justify-between">
                                    <label class="flex items-center gap-2 font-semibold text-slate-800 dark:text-slate-200 cursor-pointer">
                                        <input type="checkbox" data-col-idx="${idx}" data-col-action="toggle" ${col.visible !== false ? 'checked' : ''} class="w-4 h-4 text-blue-600 rounded" />
                                        <span>${col.label || col.key}</span>
                                    </label>
                                    <div class="flex items-center gap-1">
                                        <button data-col-idx="${idx}" data-col-action="move-up" class="p-1 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer" title="Move Up">▲</button>
                                        <button data-col-idx="${idx}" data-col-action="move-down" class="p-1 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer" title="Move Down">▼</button>
                                        ${col.isCustom ? `
                                            <button data-col-idx="${idx}" data-col-action="delete" class="p-1 text-rose-500 hover:text-rose-700 cursor-pointer" title="Delete">✕</button>
                                        ` : ''}
                                    </div>
                                </div>

                                <div class="grid grid-cols-3 gap-2">
                                    <div>
                                        <label class="block text-[10px] text-slate-400">Header Label</label>
                                        <input type="text" data-col-idx="${idx}" data-col-field="label" value="${col.label || ''}" class="w-full px-2 py-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded text-xs" />
                                    </div>
                                    <div>
                                        <label class="block text-[10px] text-slate-400">Alignment</label>
                                        <select data-col-idx="${idx}" data-col-field="align" class="w-full px-2 py-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded text-xs">
                                            <option value="left" ${col.align === 'left' ? 'selected' : ''}>Left</option>
                                            <option value="center" ${col.align === 'center' ? 'selected' : ''}>Center</option>
                                            <option value="right" ${col.align === 'right' ? 'selected' : ''}>Right</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] text-slate-400">Width (%)</label>
                                        <input type="text" data-col-idx="${idx}" data-col-field="width" value="${col.width || '10%'}" class="w-full px-2 py-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded text-xs" />
                                    </div>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>
            `;

            container.querySelectorAll('[data-col-action]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const action = btn.getAttribute('data-col-action');
                    const idx = parseInt(btn.getAttribute('data-col-idx'));

                    if (action === 'move-up' && idx > 0) {
                        const temp = cols[idx];
                        cols[idx] = cols[idx - 1];
                        cols[idx - 1] = temp;
                    } else if (action === 'move-down' && idx < cols.length - 1) {
                        const temp = cols[idx];
                        cols[idx] = cols[idx + 1];
                        cols[idx + 1] = temp;
                    } else if (action === 'delete') {
                        cols.splice(idx, 1);
                    } else if (action === 'toggle') {
                        cols[idx].visible = btn.checked;
                    }

                    tmpl.columns_config = cols;
                    renderDesignerTabContent();
                    renderLivePaperSheet();
                });
            });

            container.querySelectorAll('[data-col-field]').forEach(input => {
                input.addEventListener('input', (e) => {
                    const idx = parseInt(input.getAttribute('data-col-idx'));
                    const field = input.getAttribute('data-col-field');
                    if (cols[idx]) {
                        cols[idx][field] = e.target.value;
                        renderLivePaperSheet();
                    }
                });
            });

            document.getElementById('btn-add-column')?.addEventListener('click', () => {
                const customNum = cols.filter(c => c.isCustom).length + 1;
                cols.push({
                    key: 'custom_col_' + Date.now(),
                    label: 'Custom Field ' + customNum,
                    visible: true,
                    align: 'left',
                    width: '12%',
                    isCustom: true,
                    defaultValue: 'Spec ' + customNum
                });
                tmpl.columns_config = cols;
                renderDesignerTabContent();
                renderLivePaperSheet();
            });
        }
        else if (tab === 'watermark') {
            const wm = tmpl.watermark_config || {
                enabled: false,
                type: 'text',
                text: 'CONFIDENTIAL',
                fontSize: 54,
                color: '#ef4444',
                opacity: 0.15,
                rotation: -30,
                position: 'center',
                borderStyle: 'solid'
            };

            container.innerHTML = `
                <div class="space-y-4">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Authentic Security Watermark</h3>

                    <label class="flex items-center gap-2 p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200 dark:border-slate-700 cursor-pointer">
                        <input type="checkbox" id="chk-wm-enabled" ${wm.enabled ? 'checked' : ''} class="w-4 h-4 text-blue-600 rounded" />
                        <div>
                            <strong class="text-slate-900 dark:text-white block">Enable Watermark Overlay</strong>
                            <span class="text-[11px] text-slate-500">Render diagonal draft, confidential, or custom security stamp.</span>
                        </div>
                    </label>

                    <div>
                        <label class="block font-semibold mb-1 text-slate-700 dark:text-slate-300">Watermark Text</label>
                        <input type="text" id="inp-wm-text" value="${wm.text || 'CONFIDENTIAL'}" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white uppercase font-bold" />
                    </div>

                    <div>
                        <span class="block text-slate-400 text-[10px] uppercase font-semibold mb-1">Quick Presets:</span>
                        <div class="flex flex-wrap gap-1.5">
                            ${['DRAFT', 'CONFIDENTIAL', 'PAID', 'ORIGINAL', 'PROFORMA', 'CANCELLED'].map(preset => `
                                <button type="button" data-wm-preset="${preset}" class="px-2 py-1 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded text-[11px] font-semibold cursor-pointer">
                                    ${preset}
                                </button>
                            `).join('')}
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold mb-1 text-slate-700 dark:text-slate-300">Watermark Color</label>
                            <input type="color" id="inp-wm-color" value="${wm.color || '#ef4444'}" class="w-full h-8 rounded border-0 cursor-pointer" />
                        </div>
                        <div>
                            <label class="block font-semibold mb-1 text-slate-700 dark:text-slate-300">Placement</label>
                            <select id="sel-wm-pos" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                                <option value="center" ${wm.position === 'center' ? 'selected' : ''}>🎯 Single Centered</option>
                                <option value="repeat" ${wm.position === 'repeat' ? 'selected' : ''}>🔲 Repeat Grid Pattern</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between font-semibold mb-1 text-slate-700 dark:text-slate-300">
                            <span>Opacity Transparency</span>
                            <span id="lbl-wm-opacity">${Math.round((wm.opacity || 0.15) * 100)}%</span>
                        </div>
                        <input type="range" id="rng-wm-opacity" min="5" max="50" value="${Math.round((wm.opacity || 0.15) * 100)}" class="w-full" />
                    </div>

                    <div>
                        <div class="flex justify-between font-semibold mb-1 text-slate-700 dark:text-slate-300">
                            <span>Rotation Angle</span>
                            <span id="lbl-wm-rot">${wm.rotation || -30}°</span>
                        </div>
                        <input type="range" id="rng-wm-rot" min="-90" max="90" value="${wm.rotation || -30}" class="w-full" />
                    </div>
                </div>
            `;

            document.getElementById('chk-wm-enabled')?.addEventListener('change', (e) => {
                wm.enabled = e.target.checked;
                tmpl.watermark_config = wm;
                renderLivePaperSheet();
            });

            document.getElementById('inp-wm-text')?.addEventListener('input', (e) => {
                wm.text = e.target.value.toUpperCase();
                tmpl.watermark_config = wm;
                renderLivePaperSheet();
            });

            container.querySelectorAll('[data-wm-preset]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const text = btn.getAttribute('data-wm-preset');
                    wm.text = text;
                    const inp = document.getElementById('inp-wm-text');
                    if (inp) inp.value = text;
                    tmpl.watermark_config = wm;
                    renderLivePaperSheet();
                });
            });

            document.getElementById('inp-wm-color')?.addEventListener('input', (e) => {
                wm.color = e.target.value;
                tmpl.watermark_config = wm;
                renderLivePaperSheet();
            });

            document.getElementById('sel-wm-pos')?.addEventListener('change', (e) => {
                wm.position = e.target.value;
                tmpl.watermark_config = wm;
                renderLivePaperSheet();
            });

            document.getElementById('rng-wm-opacity')?.addEventListener('input', (e) => {
                wm.opacity = parseFloat((parseInt(e.target.value) / 100).toFixed(2));
                const lbl = document.getElementById('lbl-wm-opacity');
                if (lbl) lbl.textContent = `${Math.round(wm.opacity * 100)}%`;
                tmpl.watermark_config = wm;
                renderLivePaperSheet();
            });

            document.getElementById('rng-wm-rot')?.addEventListener('input', (e) => {
                wm.rotation = parseInt(e.target.value);
                const lbl = document.getElementById('lbl-wm-rot');
                if (lbl) lbl.textContent = `${wm.rotation}°`;
                tmpl.watermark_config = wm;
                renderLivePaperSheet();
            });
        }
        else if (tab === 'dynamic') {
            const dyn = tmpl.dynamic_fields_config || {
                enable_order_discount: true,
                discount_type: 'percentage',
                default_discount_rate: 0,
                enable_tax_summary: true,
                tax_mode: 'split_gst',
                default_tax_rate: 18,
                enable_shipping: true,
                enable_round_off: true,
                enable_amount_in_words: true,
                custom_adjustments: []
            };

            const adjustments = Array.isArray(dyn.custom_adjustments) ? dyn.custom_adjustments : [];

            container.innerHTML = `
                <div class="space-y-4">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Financial Computations &amp; Adjustments</h3>

                    <div class="space-y-2 p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200 dark:border-slate-700">
                        <label class="flex items-center gap-2 cursor-pointer font-medium">
                            <input type="checkbox" id="chk-order-disc" ${dyn.enable_order_discount ? 'checked' : ''} class="w-4 h-4 text-blue-600 rounded" />
                            <span>Enable Document Order Discount</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer font-medium">
                            <input type="checkbox" id="chk-shipping" ${dyn.enable_shipping ? 'checked' : ''} class="w-4 h-4 text-blue-600 rounded" />
                            <span>Enable Shipping &amp; Logistics Freight Charge</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer font-medium">
                            <input type="checkbox" id="chk-words" ${dyn.enable_amount_in_words ? 'checked' : ''} class="w-4 h-4 text-blue-600 rounded" />
                            <span>Show "Amount in Words" Summary</span>
                        </label>
                    </div>

                    <div>
                        <label class="block font-semibold mb-1 text-slate-700 dark:text-slate-300">Tax Calculation Mode</label>
                        <select id="sel-tax-mode" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                            <option value="split_gst" ${dyn.tax_mode === 'split_gst' ? 'selected' : ''}>Dual CGST + SGST Split (e.g. 9% + 9%)</option>
                            <option value="single_tax" ${dyn.tax_mode === 'single_tax' ? 'selected' : ''}>Single Combined Tax / VAT</option>
                            <option value="off" ${dyn.tax_mode === 'off' ? 'selected' : ''}>Off (Tax Exempt / Hide Tax Rows)</option>
                        </select>
                    </div>

                    ${dyn.tax_mode !== 'off' ? `
                        <div>
                            <label class="block font-semibold mb-1 text-slate-700 dark:text-slate-300">Default Tax Rate (%)</label>
                            <input type="number" id="inp-tax-rate" value="${dyn.default_tax_rate || 18}" class="w-24 px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white" />
                        </div>
                    ` : ''}

                    <!-- Custom Adjustments (Credit/Debit Notes) -->
                    <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
                        <div class="flex items-center justify-between mb-2">
                            <div>
                                <h4 class="font-bold text-slate-900 dark:text-white">Custom Financial Adjustments</h4>
                                <span class="text-[11px] text-slate-500">Credit Notes (-), Debit Notes (+), Surcharges</span>
                            </div>
                            <button id="btn-add-adj" class="px-2 py-1 bg-blue-50 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400 rounded text-xs font-semibold cursor-pointer">
                                + Add Field
                            </button>
                        </div>

                        ${adjustments.length === 0 ? `
                            <div class="p-3 bg-slate-50 dark:bg-slate-800/40 rounded-lg text-center text-slate-400 text-xs">
                                No custom adjustments configured.
                            </div>
                        ` : `
                            <div class="space-y-2">
                                ${adjustments.map((adj, idx) => `
                                    <div class="p-2.5 bg-slate-50 dark:bg-slate-800/60 rounded-lg border border-slate-200 dark:border-slate-700 flex items-center gap-2">
                                        <input type="text" data-adj-idx="${idx}" data-adj-field="name" value="${adj.name || ''}" placeholder="Adjustment Label" class="flex-1 px-2 py-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded text-xs" />
                                        <select data-adj-idx="${idx}" data-adj-field="operation" class="px-2 py-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded text-xs">
                                            <option value="subtract" ${adj.operation === 'subtract' ? 'selected' : ''}>- Subtract</option>
                                            <option value="add" ${adj.operation === 'add' ? 'selected' : ''}>+ Add</option>
                                        </select>
                                        <button data-adj-idx="${idx}" data-adj-action="delete" class="p-1 text-rose-500 hover:text-rose-700 cursor-pointer">✕</button>
                                    </div>
                                `).join('')}
                            </div>
                        `}
                    </div>
                </div>
            `;

            document.getElementById('chk-order-disc')?.addEventListener('change', (e) => {
                dyn.enable_order_discount = e.target.checked;
                tmpl.dynamic_fields_config = dyn;
                renderLivePaperSheet();
            });

            document.getElementById('chk-shipping')?.addEventListener('change', (e) => {
                dyn.enable_shipping = e.target.checked;
                tmpl.dynamic_fields_config = dyn;
                renderLivePaperSheet();
            });

            document.getElementById('chk-words')?.addEventListener('change', (e) => {
                dyn.enable_amount_in_words = e.target.checked;
                tmpl.dynamic_fields_config = dyn;
                renderLivePaperSheet();
            });

            document.getElementById('sel-tax-mode')?.addEventListener('change', (e) => {
                dyn.tax_mode = e.target.value;
                tmpl.dynamic_fields_config = dyn;
                renderDesignerTabContent();
                renderLivePaperSheet();
            });

            document.getElementById('inp-tax-rate')?.addEventListener('input', (e) => {
                dyn.default_tax_rate = parseFloat(e.target.value) || 0;
                tmpl.dynamic_fields_config = dyn;
                renderLivePaperSheet();
            });

            document.getElementById('btn-add-adj')?.addEventListener('click', () => {
                adjustments.push({
                    id: 'adj-' + Date.now(),
                    name: 'Credit Note / Rebate',
                    operation: 'subtract',
                    type: 'flat',
                    value: 0
                });
                dyn.custom_adjustments = adjustments;
                tmpl.dynamic_fields_config = dyn;
                renderDesignerTabContent();
                renderLivePaperSheet();
            });

            container.querySelectorAll('[data-adj-action="delete"]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const idx = parseInt(btn.getAttribute('data-adj-idx'));
                    adjustments.splice(idx, 1);
                    dyn.custom_adjustments = adjustments;
                    tmpl.dynamic_fields_config = dyn;
                    renderDesignerTabContent();
                    renderLivePaperSheet();
                });
            });

            container.querySelectorAll('[data-adj-field]').forEach(inp => {
                inp.addEventListener('input', (e) => {
                    const idx = parseInt(inp.getAttribute('data-adj-idx'));
                    const f = inp.getAttribute('data-adj-field');
                    if (adjustments[idx]) {
                        adjustments[idx][f] = e.target.value;
                        tmpl.dynamic_fields_config = dyn;
                        renderLivePaperSheet();
                    }
                });
            });
        }
        else if (tab === 'banking') {
            container.innerHTML = `
                <div class="space-y-4">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Remittance Details &amp; Signatures</h3>

                    <div>
                        <label class="block font-semibold mb-1 text-slate-700 dark:text-slate-300">Default Payment Terms</label>
                        <input type="text" id="inp-pay-terms" value="${tmpl.payment_terms_default || 'Net 30'}" placeholder="e.g. Net 30, Due on Receipt" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white" />
                    </div>

                    <div>
                        <label class="block font-semibold mb-1 text-slate-700 dark:text-slate-300">Bank Wire / Remittance Details</label>
                        <textarea id="inp-bank-details" rows="3" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white font-mono text-xs">${tmpl.bank_details || ''}</textarea>
                    </div>

                    <div>
                        <label class="block font-semibold mb-1 text-slate-700 dark:text-slate-300">Default Notes / Client Message</label>
                        <textarea id="inp-notes-default" rows="2" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">${tmpl.notes_default || ''}</textarea>
                    </div>

                    <div>
                        <label class="block font-semibold mb-1 text-slate-700 dark:text-slate-300">Terms &amp; Conditions (Fine Print)</label>
                        <textarea id="inp-terms-cond" rows="2" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">${tmpl.terms_conditions || ''}</textarea>
                    </div>

                    <div class="pt-2 border-t border-slate-100 dark:border-slate-800 space-y-3">
                        <label class="flex items-center gap-2 cursor-pointer font-medium">
                            <input type="checkbox" id="chk-show-signatory" ${Boolean(tmpl.show_signatory) ? 'checked' : ''} class="w-4 h-4 text-blue-600 rounded" />
                            <span>Show Authorized Signatory Block</span>
                        </label>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-semibold mb-1 text-slate-700 dark:text-slate-300">Signatory Title</label>
                                <input type="text" id="inp-sign-title" value="${tmpl.signatory_title || 'Chief Financial Officer'}" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white" />
                            </div>
                            <div>
                                <label class="block font-semibold mb-1 text-slate-700 dark:text-slate-300">Signatory Name</label>
                                <input type="text" id="inp-sign-name" value="${tmpl.signatory_name || 'Finance Directorate'}" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white" />
                            </div>
                        </div>
                    </div>
                </div>
            `;

            const bindLive = (id, prop) => {
                document.getElementById(id)?.addEventListener('input', (e) => {
                    tmpl[prop] = e.target.value;
                    renderLivePaperSheet();
                });
            };

            bindLive('inp-pay-terms', 'payment_terms_default');
            bindLive('inp-bank-details', 'bank_details');
            bindLive('inp-notes-default', 'notes_default');
            bindLive('inp-terms-cond', 'terms_conditions');
            bindLive('inp-sign-title', 'signatory_title');
            bindLive('inp-sign-name', 'signatory_name');

            document.getElementById('chk-show-signatory')?.addEventListener('change', (e) => {
                tmpl.show_signatory = e.target.checked;
                renderLivePaperSheet();
            });
        }
        else if (tab === 'agreement') {
            const pages = Array.isArray(tmpl.agreement_pages) ? tmpl.agreement_pages : [];

            container.innerHTML = `
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Multi-Page Contract Appendices</h3>
                            <p class="text-[11px] text-slate-500">Append formal agreement clauses and SLA annexures.</p>
                        </div>
                        <button id="btn-add-agree-page" class="px-2.5 py-1 bg-blue-50 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400 rounded-lg text-xs font-semibold cursor-pointer">
                            + Add Page
                        </button>
                    </div>

                    ${pages.length === 0 ? `
                        <div class="p-4 bg-slate-50 dark:bg-slate-800/40 rounded-xl text-center text-slate-400 text-xs">
                            No attached contract pages. Click "+ Add Page" to append terms.
                        </div>
                    ` : `
                        <div class="space-y-3">
                            ${pages.map((p, idx) => `
                                <div class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200 dark:border-slate-700 space-y-2">
                                    <div class="flex items-center justify-between">
                                        <strong class="text-xs text-slate-900 dark:text-white">Appendix Page ${idx + 2}</strong>
                                        <button data-page-idx="${idx}" data-page-action="delete" class="text-rose-500 hover:text-rose-700 text-xs cursor-pointer">Delete Page</button>
                                    </div>
                                    <input type="text" data-page-idx="${idx}" data-page-field="title" value="${p.title || ''}" placeholder="Page Title" class="w-full px-2.5 py-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded text-xs font-semibold" />
                                    <textarea data-page-idx="${idx}" data-page-field="content_html" rows="4" placeholder="HTML or text terms..." class="w-full px-2.5 py-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded text-xs">${p.content_html || ''}</textarea>
                                    <label class="flex items-center gap-2 text-[11px] cursor-pointer">
                                        <input type="checkbox" data-page-idx="${idx}" data-page-field="require_client_signature" ${p.require_client_signature ? 'checked' : ''} class="w-3.5 h-3.5 text-blue-600 rounded" />
                                        <span>Require Client Sign-Off Block</span>
                                    </label>
                                </div>
                            `).join('')}
                        </div>
                    `}
                </div>
            `;

            document.getElementById('btn-add-agree-page')?.addEventListener('click', () => {
                pages.push({
                    id: 'agree-' + Date.now(),
                    title: `Attachment Page ${pages.length + 1}: Service Level Agreement`,
                    content_html: '<h4>1. Scope & Execution</h4><p>Enter your contractual terms, warranties, or SLA requirements here.</p>',
                    require_client_signature: true
                });
                tmpl.agreement_pages = pages;
                renderDesignerTabContent();
                renderLivePaperSheet();
            });

            container.querySelectorAll('[data-page-action="delete"]').forEach(b => {
                b.addEventListener('click', () => {
                    const idx = parseInt(b.getAttribute('data-page-idx'));
                    pages.splice(idx, 1);
                    tmpl.agreement_pages = pages;
                    renderDesignerTabContent();
                    renderLivePaperSheet();
                });
            });

            container.querySelectorAll('[data-page-field]').forEach(input => {
                input.addEventListener('input', (e) => {
                    const idx = parseInt(input.getAttribute('data-page-idx'));
                    const f = input.getAttribute('data-page-field');
                    if (pages[idx]) {
                        pages[idx][f] = input.type === 'checkbox' ? input.checked : input.value;
                        tmpl.agreement_pages = pages;
                        renderLivePaperSheet();
                    }
                });
            });
        }
        else if (tab === 'governance') {
            container.innerHTML = `
                <div class="space-y-4">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Template Governance &amp; Archival</h3>

                    <div>
                        <label class="block font-semibold mb-1 text-slate-700 dark:text-slate-300">File Archival Destination</label>
                        <select id="sel-file-dest" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white">
                            <option value="files_public" ${tmpl.save_to_files_destination === 'files_public' ? 'selected' : ''}>Public Documents Folder</option>
                            <option value="files_private" ${tmpl.save_to_files_destination === 'files_private' ? 'selected' : ''}>Secure Restricted Vault</option>
                            <option value="none" ${tmpl.save_to_files_destination === 'none' ? 'selected' : ''}>Do Not Auto-Archive to Files</option>
                        </select>
                    </div>

                    <div class="p-3 bg-blue-50/70 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-900/60 rounded-xl space-y-1 text-xs text-blue-900 dark:text-blue-300">
                        <strong class="font-bold">Enterprise Archival Policy:</strong>
                        <p class="text-[11px] text-blue-700 dark:text-blue-400">
                            When generated, an immutable frozen snapshot copy of the exact HTML and calculation data will be recorded in the ledger table.
                        </p>
                    </div>
                </div>
            `;

            document.getElementById('sel-file-dest')?.addEventListener('change', (e) => {
                tmpl.save_to_files_destination = e.target.value;
            });
        }
    }

    // =========================================================================
    // WYSIWYG LIVE PREVIEW SHEET RENDERER
    // =========================================================================

    function renderLivePaperSheet() {
        const sheet = document.getElementById('live-paper-sheet');
        if (!sheet) return;

        const tmpl = state.activeTemplate;
        if (!tmpl) return;

        const sample = SAMPLE_PREVIEWS[state.previewSampleType] || SAMPLE_PREVIEWS.invoice;
        const visibleCols = (Array.isArray(tmpl.columns_config) ? tmpl.columns_config : []).filter(c => c.visible !== false);
        const dyn = tmpl.dynamic_fields_config || {};
        const wm = tmpl.watermark_config || {};
        const agreementPages = Array.isArray(tmpl.agreement_pages) ? tmpl.agreement_pages : [];

        // Calculation Figures
        const subtotal = sample.items.reduce((acc, it) => acc + (it.qty * it.rate * (1 - (it.disc || 0) / 100)), 0);
        const orderDisc = dyn.enable_order_discount ? (dyn.discount_type === 'flat' ? (dyn.default_discount_rate || 0) : subtotal * ((dyn.default_discount_rate || 0) / 100)) : 0;
        const taxable = Math.max(0, subtotal - orderDisc);
        const taxRate = dyn.tax_mode === 'off' ? 0 : (dyn.default_tax_rate || 18);
        const taxAmount = dyn.tax_mode === 'off' ? 0 : (taxable * (taxRate / 100));
        const shippingAmount = dyn.enable_shipping ? 50.0 : 0;
        const grandTotal = taxable + taxAmount + shippingAmount;

        const primaryColor = tmpl.primary_color || '#0f172a';
        const accentColor = tmpl.accent_color || '#0d9488';
        const layoutStyle = tmpl.layout_style || 'modern_teal';
        const tableTheme = tmpl.table_theme || 'zebra';

        // Sheet Paper Dimension Classes
        const isLetter = tmpl.paper_size === 'Letter';
        const isLandscape = tmpl.orientation === 'landscape';

        let widthPx = isLandscape ? (isLetter ? 1056 : 1123) : (isLetter ? 816 : 794);
        let paddingPx = tmpl.margin_size === 'compact' ? 32 : (tmpl.margin_size === 'spacious' ? 64 : 48);

        sheet.innerHTML = `
            <div class="space-y-8">
                <!-- PAGE 1: PRIMARY DOCUMENT SHEET -->
                <div
                    class="bg-white text-slate-900 rounded-sm shadow-2xl relative transition-all overflow-hidden"
                    style="
                        width: ${widthPx}px;
                        min-height: ${isLandscape ? 794 : 1123}px;
                        padding: ${paddingPx}px;
                        font-family: ${tmpl.font_family || 'Inter, sans-serif'};
                    "
                >
                    <!-- WATERMARK OVERLAY -->
                    ${wm.enabled ? `
                        <div class="absolute inset-0 pointer-events-none select-none z-10 flex items-center justify-center overflow-hidden" style="opacity: ${wm.opacity || 0.15};">
                            <div style="transform: rotate(${wm.rotation || -30}deg);">
                                <span class="font-black uppercase tracking-widest px-8 py-3 rounded-lg" style="
                                    font-size: ${wm.fontSize || 54}px;
                                    color: ${wm.color || '#ef4444'};
                                    border: 4px solid ${wm.color || '#ef4444'};
                                ">
                                    ${wm.text || 'CONFIDENTIAL'}
                                </span>
                            </div>
                        </div>
                    ` : ''}

                    <!-- TOP BRAND STRIPE FOR CLASSIC NAVY -->
                    ${layoutStyle === 'classic_navy' ? `
                        <div class="h-1.5 w-full rounded mb-5" style="background-color: ${primaryColor};"></div>
                    ` : ''}

                    <!-- HEADER ROW -->
                    <div class="flex items-start justify-between pb-5 mb-6 ${layoutStyle === 'bold_stripe' ? 'border-l-4 pl-4' : 'border-b'}" style="border-color: ${layoutStyle === 'bold_stripe' ? accentColor : '#e2e8f0'};">
                        <!-- Left: Logo & Company Info -->
                        <div class="max-w-[55%]">
                            ${tmpl.logo_url ? `
                                <img src="${tmpl.logo_url}" alt="Company Logo" class="object-contain mb-2" style="height: ${tmpl.logo_height || 48}px; max-height: 80px;" />
                            ` : `
                                <div class="flex items-center gap-2 mb-2">
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center font-extrabold text-white text-sm" style="background-color: ${accentColor};">
                                        ${(tmpl.company_name || 'DS').slice(0, 2).toUpperCase()}
                                    </div>
                                    <span class="text-lg font-extrabold" style="color: ${primaryColor};">
                                        ${tmpl.company_name || 'Company Name'}
                                    </span>
                                </div>
                            `}

                            ${tmpl.logo_url ? `
                                <div class="text-base font-extrabold mb-1" style="color: ${primaryColor};">
                                    ${tmpl.company_name || 'Company Name'}
                                </div>
                            ` : ''}

                            <div class="text-[11px] text-slate-500 leading-relaxed">
                                ${tmpl.company_address ? `<div>${tmpl.company_address}</div>` : ''}
                                ${tmpl.company_tax_id ? `<div><strong>Tax ID / GSTIN:</strong> ${tmpl.company_tax_id}</div>` : ''}
                                <div>
                                    ${tmpl.company_email ? `<span>${tmpl.company_email}</span>` : ''}
                                    ${tmpl.company_email && tmpl.company_phone ? ` • ` : ''}
                                    ${tmpl.company_phone ? `<span>${tmpl.company_phone}</span>` : ''}
                                </div>
                            </div>
                        </div>

                        <!-- Right: Document Title & Meta -->
                        <div class="text-right">
                            <div class="inline-block px-4 py-1.5 rounded text-sm font-extrabold tracking-wider uppercase mb-3 text-white" style="background-color: ${accentColor};">
                                ${tmpl.document_title || (state.previewSampleType === 'invoice' ? 'TAX INVOICE' : state.previewSampleType === 'po' ? 'PURCHASE ORDER' : 'PRICE QUOTATION')}
                            </div>

                            <table class="ml-auto text-[11px] text-slate-600">
                                <tbody>
                                    <tr>
                                        <td class="font-semibold pr-3 text-right">Doc #:</td>
                                        <td class="font-bold text-slate-900">${state.previewSampleType === 'invoice' ? 'INV-2026-001' : state.previewSampleType === 'po' ? 'PO-2026-042' : 'QT-2026-105'}</td>
                                    </tr>
                                    <tr>
                                        <td class="font-semibold pr-3 text-right">Date:</td>
                                        <td>${new Date().toISOString().slice(0, 10)}</td>
                                    </tr>
                                    <tr>
                                        <td class="font-semibold pr-3 text-right">Terms:</td>
                                        <td class="font-semibold">${tmpl.payment_terms_default || 'Net 30'}</td>
                                    </tr>
                                    <tr>
                                        <td class="font-semibold pr-3 text-right">Due Date:</td>
                                        <td>${new Date(Date.now() + 30 * 86400000).toISOString().slice(0, 10)}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- PARTY CARDS (BILLED TO / SHIPPED TO) -->
                    <div class="grid ${Boolean(tmpl.show_shipping_address) ? 'grid-cols-2' : 'grid-cols-1'} gap-4 mb-6">
                        <div class="p-3.5 rounded-lg border text-[11px] leading-relaxed ${
                            layoutStyle === 'modern_teal' ? 'bg-teal-50/50 border-teal-200' : 'bg-slate-50 border-slate-200'
                        }">
                            <div class="text-[10px] font-bold uppercase tracking-wider mb-1" style="color: ${accentColor};">
                                ${state.previewSampleType === 'po' ? 'SUPPLIER / VENDOR:' : 'BILLED TO:'}
                            </div>
                            <div class="text-xs font-bold text-slate-900">${sample.party.billed_to.name}</div>
                            <div>${sample.party.billed_to.address}</div>
                            <div><strong>Attn:</strong> ${sample.party.billed_to.contact}</div>
                            <div><strong>Tax ID:</strong> ${sample.party.billed_to.tax_id}</div>
                        </div>

                        ${Boolean(tmpl.show_shipping_address) ? `
                            <div class="p-3.5 rounded-lg border text-[11px] leading-relaxed ${
                                layoutStyle === 'modern_teal' ? 'bg-teal-50/50 border-teal-200' : 'bg-slate-50 border-slate-200'
                            }">
                                <div class="text-[10px] font-bold uppercase tracking-wider mb-1" style="color: ${accentColor};">
                                    ${state.previewSampleType === 'po' ? 'SHIP TO / RECEIVING DOCK:' : 'SHIPPED TO:'}
                                </div>
                                <div class="text-xs font-bold text-slate-900">${sample.party.shipped_to.name}</div>
                                <div>${sample.party.shipped_to.address}</div>
                                <div><strong>Delivery:</strong> Standard Fulfillment</div>
                            </div>
                        ` : ''}
                    </div>

                    <!-- LINE ITEMS TABLE -->
                    <table class="w-full text-[11px] mb-6 border-collapse">
                        <thead>
                            <tr class="text-white" style="background-color: ${primaryColor};">
                                ${visibleCols.map(c => `
                                    <th class="p-2.5 font-bold uppercase tracking-wider text-[10px]" style="text-align: ${c.align || 'left'}; width: ${c.width};">
                                        ${c.label}
                                    </th>
                                `).join('')}
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            ${sample.items.map((row, idx) => `
                                <tr class="${tableTheme === 'zebra' && idx % 2 === 1 ? 'bg-slate-50' : 'bg-white'}">
                                    ${visibleCols.map(c => {
                                        let val = '';
                                        if (c.key === 'item_number') val = row.num;
                                        else if (c.key === 'sku') val = `<strong>${row.sku}</strong>`;
                                        else if (c.key === 'description') val = row.desc;
                                        else if (c.key === 'hsn_sac_code') val = row.hsn;
                                        else if (c.key === 'quantity') val = row.qty;
                                        else if (c.key === 'unit_price') val = `$${row.rate.toFixed(2)}`;
                                        else if (c.key === 'discount_rate') val = row.disc ? `${row.disc}%` : '-';
                                        else if (c.key === 'tax_rate') val = `${row.tax}%`;
                                        else if (c.key === 'total_amount') val = `<strong>$${row.total.toFixed(2)}</strong>`;
                                        else val = c.defaultValue || '-';

                                        return `<td class="p-2.5 align-top" style="text-align: ${c.align || 'left'};">${val}</td>`;
                                    }).join('')}
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>

                    <!-- TOTALS & BANKING BLOCK -->
                    <div class="flex justify-between items-start gap-6 mb-6">
                        <!-- Left: Banking & Notes -->
                        <div class="w-1/2 text-[11px] space-y-2">
                            ${tmpl.bank_details ? `
                                <div class="p-2.5 bg-slate-50 rounded border border-slate-200 text-[10px]">
                                    <strong class="text-slate-900 block mb-0.5">Bank Wire Instructions:</strong>
                                    <pre class="font-mono text-slate-600 whitespace-pre-wrap">${tmpl.bank_details}</pre>
                                </div>
                            ` : ''}

                            ${tmpl.notes_default ? `
                                <div class="text-slate-600">
                                    <strong>Notes:</strong> ${tmpl.notes_default}
                                </div>
                            ` : ''}

                            ${tmpl.terms_conditions ? `
                                <div class="text-[10px] text-slate-400">
                                    <strong>Terms:</strong> ${tmpl.terms_conditions}
                                </div>
                            ` : ''}
                        </div>

                        <!-- Right: Financial Summary -->
                        <div class="w-5/12">
                            <table class="w-full text-xs">
                                <tbody class="divide-y divide-slate-100">
                                    <tr>
                                        <td class="py-1 text-slate-500">Subtotal:</td>
                                        <td class="py-1 text-right font-semibold">$${subtotal.toFixed(2)}</td>
                                    </tr>

                                    ${dyn.enable_order_discount && orderDisc > 0 ? `
                                        <tr>
                                            <td class="py-1 text-slate-500">Discount:</td>
                                            <td class="py-1 text-right font-semibold text-emerald-600">-$${orderDisc.toFixed(2)}</td>
                                        </tr>
                                    ` : ''}

                                    ${dyn.tax_mode !== 'off' ? `
                                        ${dyn.tax_mode === 'split_gst' ? `
                                            <tr>
                                                <td class="py-1 text-slate-500">CGST (${(taxRate / 2).toFixed(1)}%):</td>
                                                <td class="py-1 text-right font-semibold">$${(taxAmount / 2).toFixed(2)}</td>
                                            </tr>
                                            <tr>
                                                <td class="py-1 text-slate-500">SGST (${(taxRate / 2).toFixed(1)}%):</td>
                                                <td class="py-1 text-right font-semibold">$${(taxAmount / 2).toFixed(2)}</td>
                                            </tr>
                                        ` : `
                                            <tr>
                                                <td class="py-1 text-slate-500">Tax / VAT (${taxRate}%):</td>
                                                <td class="py-1 text-right font-semibold">$${taxAmount.toFixed(2)}</td>
                                            </tr>
                                        `}
                                    ` : ''}

                                    ${dyn.enable_shipping ? `
                                        <tr>
                                            <td class="py-1 text-slate-500">Shipping &amp; Freight:</td>
                                            <td class="py-1 text-right font-semibold">$${shippingAmount.toFixed(2)}</td>
                                        </tr>
                                    ` : ''}

                                    <tr class="border-t-2" style="border-color: ${accentColor};">
                                        <td class="py-2 text-sm font-extrabold text-slate-900">Grand Total:</td>
                                        <td class="py-2 text-sm font-extrabold text-right" style="color: ${accentColor};">$${grandTotal.toFixed(2)}</td>
                                    </tr>
                                </tbody>
                            </table>

                            ${dyn.enable_amount_in_words ? `
                                <div class="mt-2 p-1.5 bg-slate-50 rounded text-[10px] italic text-slate-500 text-right">
                                    Amount in Words: Nine Thousand Seventy-Four US Dollars Only
                                </div>
                            ` : ''}
                        </div>
                    </div>

                    <!-- SIGNATORY BLOCK -->
                    ${Boolean(tmpl.show_signatory) ? `
                        <div class="flex justify-end mt-8">
                            <div class="text-center w-52 border-t border-slate-300 pt-2 text-[11px]">
                                <div class="h-8 flex items-center justify-center italic text-slate-400 font-serif">
                                    Authorized Signatory
                                </div>
                                <div class="font-bold text-slate-900">${tmpl.signatory_name || 'Chief Financial Officer'}</div>
                                <div class="text-[10px] text-slate-500">${tmpl.signatory_title || 'Authorized Signatory'}</div>
                            </div>
                        </div>
                    ` : ''}

                    <div class="absolute bottom-4 right-8 text-[10px] text-slate-400">
                        Page 1 of ${agreementPages.length + 1}
                    </div>
                </div>

                <!-- PAGE 2+: AGREEMENT ATTACHMENT PAGES -->
                ${agreementPages.map((page, idx) => `
                    <div class="flex items-center justify-center gap-3 text-xs font-semibold text-slate-400 uppercase tracking-widest my-2">
                        <span class="h-px bg-slate-300 flex-1"></span>
                        <span>Page Break • ${page.title || 'Appendix Page'}</span>
                        <span class="h-px bg-slate-300 flex-1"></span>
                    </div>

                    <div
                        class="bg-white text-slate-900 rounded-sm shadow-2xl relative transition-all overflow-hidden"
                        style="
                            width: ${widthPx}px;
                            min-height: ${isLandscape ? 794 : 1123}px;
                            padding: ${paddingPx}px;
                            font-family: ${tmpl.font_family || 'Inter, sans-serif'};
                        "
                    >
                        <div class="flex justify-between border-b border-slate-200 pb-3 mb-6 text-[10px] text-slate-500">
                            <span>${tmpl.company_name || 'Company Name'} • ${tmpl.document_title || 'DOCUMENT'}</span>
                            <span>Ref #: ${state.previewSampleType === 'invoice' ? 'INV-2026-001' : state.previewSampleType === 'po' ? 'PO-2026-042' : 'QT-2026-105'}</span>
                        </div>

                        <h2 class="text-base font-extrabold mb-4 pb-2 border-b-2" style="border-color: ${accentColor}; color: ${primaryColor};">
                            ${page.title}
                        </h2>

                        <div class="text-xs leading-relaxed text-slate-700 min-h-[400px]">
                            ${page.content_html || '<p>Terms and conditions content...</p>'}
                        </div>

                        ${page.require_client_signature ? `
                            <div class="mt-12 pt-5 border-t border-slate-200">
                                <h4 class="text-xs font-bold text-slate-900 mb-4">Formal Client Acceptance &amp; Countersignature</h4>
                                <div class="grid grid-cols-2 gap-8 text-[11px] text-slate-600">
                                    <div class="border-t border-dashed border-slate-300 pt-2">
                                        <strong>Authorized Representative (Client)</strong>
                                        <div class="mt-1">Name: _______________________________</div>
                                        <div class="mt-1">Date: _______________________________</div>
                                    </div>
                                    <div class="border-t border-dashed border-slate-300 pt-2">
                                        <strong>Authorized Signatory (DealSync)</strong>
                                        <div class="mt-1">Name: ${tmpl.signatory_name || 'Finance Directorate'}</div>
                                        <div class="mt-1">Date: ${new Date().toISOString().slice(0, 10)}</div>
                                    </div>
                                </div>
                            </div>
                        ` : ''}

                        <div class="absolute bottom-4 right-8 text-[10px] text-slate-400">
                            Page ${idx + 2} of ${agreementPages.length + 1}
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
    }

})();
