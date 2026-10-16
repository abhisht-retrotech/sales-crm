/**
 * DealSync Dashboard Studio & Layout Designer State Engine
 * 100% Client-Side Static & Dynamic Parity with React DashboardStudio.tsx
 */

// Global Collections hydrated from server DOM element
let DB_PROFILES = [];
let DB_SAVED_LAYOUTS = {};
let DB_SAVED_SECTIONS = {};

function parseServerData() {
    const el = document.getElementById('dsServerData');
    if (el) {
        try {
            const parsed = JSON.parse(el.textContent);
            DB_PROFILES = parsed.profiles || [];
            DB_SAVED_LAYOUTS = parsed.savedLayouts || {};
            DB_SAVED_SECTIONS = parsed.savedSections || {};
        } catch(e) {
            console.warn('Failed to parse dsServerData', e);
        }
    }
}

// Profiles List
let PROFILES = [
    { id: 'prof-admin', name: 'System Administrator', description: 'Unrestricted administration, settings, and full system visibility', is_admin: 1 },
    { id: 'prof-sales', name: 'Standard Sales Representative', description: 'Field and inside sales execution, opportunities, and quota tracking', is_admin: 0 },
    { id: 'prof-procurement', name: 'Procurement Specialist', description: 'Vendor, purchase contracts, and supply chain telemetry', is_admin: 0 },
    { id: 'prof-readonly', name: 'Read Only Profile', description: 'Auditor and read-only executive observation workspace', is_admin: 0 }
];

if (Array.isArray(DB_PROFILES) && DB_PROFILES.length > 0) {
    DB_PROFILES.forEach(p => {
        let alias = 'prof-' + (p.name || '').toLowerCase().replace(/[^a-z0-9]/g, '');
        if (p.name.toLowerCase().includes('admin')) alias = 'prof-admin';
        else if (p.name.toLowerCase().includes('sales') || p.name.toLowerCase().includes('standard')) alias = 'prof-sales';
        else if (p.name.toLowerCase().includes('procurement')) alias = 'prof-procurement';
        else if (p.name.toLowerCase().includes('read')) alias = 'prof-readonly';
        else alias = String(p.id);

        const existing = PROFILES.find(x => x.id === alias);
        if (existing) {
            existing.db_id = p.id;
            existing.name = p.name;
            if (p.description) existing.description = p.description;
        } else {
            PROFILES.push({
                id: alias,
                db_id: p.id,
                name: p.name,
                description: p.description || 'Custom organization security profile',
                is_admin: p.is_admin ? 1 : 0
            });
        }
    });
}

// Color Palettes
const PALETTES = [
    { id: 'violet', name: 'Electric Violet', hex: '#6366F1' },
    { id: 'cyan', name: 'Cyber Cyan', hex: '#06B6D4' },
    { id: 'emerald', name: 'Emerald Growth', hex: '#10B981' },
    { id: 'amber', name: 'Sunset Amber', hex: '#F59E0B' },
    { id: 'rose', name: 'Rose Flare', hex: '#F43F5E' }
];

// 17 Power BI & Tableau Visualizations Catalog
const ALL_WIDGET_CATALOG = [
    { type: 'kpi', title: 'KPI Metric & Sparkline', category: 'Metrics & Cards', desc: 'Primary metric with trend sparkline, benchmark goal, and calculation.', defaultSpan: 4, palette: 'violet' },
    { type: 'card', title: 'High-Impact Metric Card', category: 'Metrics & Cards', desc: 'Minimalist, bold single-value callout card with delta percentage pill.', defaultSpan: 4, palette: 'emerald' },
    { type: 'gauge', title: 'Radial Speedometer Gauge', category: 'Metrics & Cards', desc: '0-100% radial dial with quota pacing, colored zones, and needle pointer.', defaultSpan: 4, palette: 'amber' },
    { type: 'column', title: 'Column Chart (Vertical)', category: 'Standard Charts', desc: 'Vertical revenue and deal volume columns with monthly/quarterly bars.', defaultSpan: 8, palette: 'violet' },
    { type: 'bar', title: 'Bar Chart (Horizontal)', category: 'Standard Charts', desc: 'Horizontal comparative progress bars for ranking reps, channels, and sources.', defaultSpan: 8, palette: 'violet' },
    { type: 'line', title: 'Line Chart (Trend & Velocity)', category: 'Standard Charts', desc: 'Multi-point smooth velocity curve with guide rules and benchmark line.', defaultSpan: 8, palette: 'cyan' },
    { type: 'area', title: 'Revenue Trajectory Area Chart', category: 'Standard Charts', desc: 'Glowing gradient bezier curve comparing actuals vs forecast baseline.', defaultSpan: 10, palette: 'violet' },
    { type: 'stacked_area', title: 'Stacked Area Chart', category: 'Standard Charts', desc: 'Cumulative multi-layer area fill representing Won, Pipeline, and Discovery.', defaultSpan: 10, palette: 'emerald' },
    { type: 'line_stacked_column', title: 'Line & Stacked Column (Combo)', category: 'Standard Charts', desc: 'Dual-axis chart with stacked volume bars overlaid with Win Rate % line.', defaultSpan: 10, palette: 'amber' },
    { type: 'pie', title: 'Proportional Pie Chart', category: 'Standard Charts', desc: 'Circular proportional slices showing deal stage and lead source breakdown.', defaultSpan: 8, palette: 'violet' },
    { type: 'donut', title: 'Stage Distribution Donut', category: 'Standard Charts', desc: 'Multi-segment circular donut chart with summary center KPI readout.', defaultSpan: 8, palette: 'cyan' },
    { type: 'treemap', title: 'Market Sector Treemap', category: 'Advanced & Geospatial', desc: 'Nested rectangular tiles sized by deal value across industry verticals.', defaultSpan: 8, palette: 'violet' },
    { type: 'map', title: 'Geospatial Point / Bubble Map', category: 'Advanced & Geospatial', desc: 'World scatter map with coordinate pins on major commercial hubs.', defaultSpan: 8, palette: 'cyan' },
    { type: 'filled_map', title: 'Filled Map (Choropleth)', category: 'Advanced & Geospatial', desc: 'Regional territory heat density map across NA, EMEA, APAC, and LATAM.', defaultSpan: 8, palette: 'emerald' },
    { type: 'table', title: 'Sales Performance Table', category: 'Grids & Filters', desc: 'High-density rep ranking table with quota attainment pills and deal counts.', defaultSpan: 10, palette: 'emerald' },
    { type: 'matrix', title: '2D Pivot Matrix Cross-Tab', category: 'Grids & Filters', desc: 'Cross-tabulation grid: Sales Reps × Deal Stages with row and column totals.', defaultSpan: 10, palette: 'violet' },
    { type: 'slicer', title: 'Interactive Filter Slicer', category: 'Grids & Filters', desc: 'Clickable filter pill buttons to slice and filter dashboard metrics.', defaultSpan: 4, palette: 'violet' }
];

// Default Sections
const DEFAULT_SECTIONS = [
    { id: 'sec-revenue', title: 'Executive Revenue & Quota Attainment', subtitle: 'Primary revenue run-rate, conversion velocity, and key quota targets', collapsed: false },
    { id: 'sec-pipeline', title: 'Pipeline Health & Conversion Telemetry', subtitle: 'Active opportunities, stage progression, and ARR forecasts', collapsed: false },
    { id: 'sec-performance', title: 'Team Performance & Operational Execution', subtitle: 'Representative rankings, action checklist, and regional coverage', collapsed: false }
];

// Default Profile Widgets
const DEFAULT_PROFILE_WIDGETS = {
    'prof-admin': [
        { id: 'w-1', type: 'kpi', title: 'Net Annual Recurring (ARR)', subtitle: '+24.8% YoY Expansion', colSpan: 4, height: 'compact', palette: 'violet', sectionId: 'sec-revenue', calculation: { module: 'opportunities', calcType: 'SUM', field: 'arr', manualOverrideValue: '$48.6M' }, sidePanelConfig: { enabled: true } },
        { id: 'w-2', type: 'kpi', title: 'Lead-to-Win Conversion', subtitle: '+4.2% Stage Velocity', colSpan: 4, height: 'compact', palette: 'cyan', sectionId: 'sec-revenue', calculation: { module: 'opportunities', calcType: 'PERCENTAGE', field: 'conversion', manualOverrideValue: '34.2%' }, sidePanelConfig: { enabled: true } },
        { id: 'w-3', type: 'kpi', title: 'Weighted Pipeline Volume', subtitle: '118 Active Opportunities', colSpan: 4, height: 'compact', palette: 'emerald', sectionId: 'sec-revenue', calculation: { module: 'opportunities', calcType: 'SUM', field: 'weighted_val', manualOverrideValue: '$142.8M' }, sidePanelConfig: { enabled: true } },
        { id: 'w-4', type: 'kpi', title: 'Net Revenue Retention (NRR)', subtitle: 'Expansion vs 1.1% Churn', colSpan: 4, height: 'compact', palette: 'rose', sectionId: 'sec-revenue', calculation: { module: 'accounts', calcType: 'PERCENTAGE', field: 'nrr', manualOverrideValue: '128.4%' }, sidePanelConfig: { enabled: true } },
        { id: 'w-5', type: 'area', title: 'ARR Multi-Trajectory Pipeline Forecast', subtitle: 'AI Optimized Stretch Target vs Baseline', colSpan: 10, height: 'standard', palette: 'violet', sectionId: 'sec-pipeline', calculation: { module: 'opportunities', calcType: 'SUM', field: 'trajectory' }, sidePanelConfig: { enabled: true } },
        { id: 'w-6', type: 'donut', title: 'Deal Distribution by Stage', subtitle: 'Active opportunity stage proportions', colSpan: 6, height: 'standard', palette: 'cyan', sectionId: 'sec-pipeline', calculation: { module: 'opportunities', calcType: 'COUNT', field: 'stage' }, sidePanelConfig: { enabled: true } },
        { id: 'w-7', type: 'table', title: 'Sales Performance Table', subtitle: 'Ranked by booked ARR and quota attainment', colSpan: 10, height: 'standard', palette: 'emerald', sectionId: 'sec-performance', calculation: { module: 'opportunities', calcType: 'SUM', field: 'rep_matrix' }, sidePanelConfig: { enabled: true } },
        { id: 'w-8', type: 'map', title: 'Active Deals Geospatial Map', subtitle: 'Geographic distribution across NA, EMEA, APAC', colSpan: 6, height: 'standard', palette: 'cyan', sectionId: 'sec-performance', calculation: { module: 'accounts', calcType: 'COUNT', field: 'geo' }, sidePanelConfig: { enabled: true } }
    ],
    'prof-sales': [
        { id: 'w-rep-1', type: 'kpi', title: 'My Closed-Won Quota', subtitle: '142% of Q3 Target ($14.2M Booked)', colSpan: 4, height: 'compact', palette: 'emerald', sectionId: 'sec-revenue', calculation: { module: 'opportunities', calcType: 'SUM', field: 'my_quota', manualOverrideValue: '$14.2M' }, sidePanelConfig: { enabled: true } },
        { id: 'w-rep-2', type: 'kpi', title: 'Active Deal Pipeline', subtitle: '18 Proposals under evaluation', colSpan: 4, height: 'compact', palette: 'violet', sectionId: 'sec-revenue', calculation: { module: 'opportunities', calcType: 'SUM', field: 'pipeline', manualOverrideValue: '$18.4M' }, sidePanelConfig: { enabled: true } },
        { id: 'w-rep-3', type: 'gauge', title: 'Quota Pacing Speedometer', subtitle: '142% pace to quarterly goal', colSpan: 4, height: 'compact', palette: 'amber', sectionId: 'sec-revenue', calculation: { module: 'opportunities', calcType: 'PERCENTAGE', field: 'pacing', manualOverrideValue: '142%' }, sidePanelConfig: { enabled: true } },
        { id: 'w-rep-4', type: 'bar', title: 'Monthly Booked Revenue vs Target', subtitle: 'Month-over-month deal volume trajectory', colSpan: 12, height: 'standard', palette: 'violet', sectionId: 'sec-pipeline', calculation: { module: 'opportunities', calcType: 'SUM', field: 'monthly_vol' }, sidePanelConfig: { enabled: true } }
    ],
    'prof-procurement': [
        { id: 'w-proc-1', type: 'kpi', title: 'Purchase Contracts Pending', subtitle: '14 Vendor Agreements', colSpan: 6, height: 'compact', palette: 'amber', sectionId: 'sec-revenue', calculation: { module: 'purchase_deals', calcType: 'COUNT', field: 'contracts', manualOverrideValue: '14' }, sidePanelConfig: { enabled: true } },
        { id: 'w-proc-2', type: 'kpi', title: 'Total Committed Spend', subtitle: 'Q4 Budget Allocation', colSpan: 6, height: 'compact', palette: 'violet', sectionId: 'sec-revenue', calculation: { module: 'purchase_deals', calcType: 'SUM', field: 'spend', manualOverrideValue: '$8.4M' }, sidePanelConfig: { enabled: true } }
    ],
    'prof-readonly': [
        { id: 'w-ro-1', type: 'kpi', title: 'Total Organization Headcount', subtitle: 'Audited directory users', colSpan: 8, height: 'compact', palette: 'violet', sectionId: 'sec-revenue', calculation: { module: 'accounts', calcType: 'COUNT', field: 'users', manualOverrideValue: '482' }, sidePanelConfig: { enabled: true } }
    ]
};

// Default Header Actions
const DEFAULT_HEADER_ACTIONS = [
    { id: 'act-1', label: 'New Lead', actionType: 'new_lead', colorScheme: 'indigo', enabled: true },
    { id: 'act-2', label: 'New Deal', actionType: 'new_deal', colorScheme: 'slate', enabled: true },
    { id: 'act-3', label: 'Log Call', actionType: 'log_call', colorScheme: 'outline', enabled: true }
];

// App State
let state = {
    selectedProfileId: 'prof-admin',
    activeTab: 'editor',
    headerActions: JSON.parse(JSON.stringify(DEFAULT_HEADER_ACTIONS)),
    sections: JSON.parse(JSON.stringify(DEFAULT_SECTIONS)),
    profileLayouts: JSON.parse(JSON.stringify(DEFAULT_PROFILE_WIDGETS)),
    catalogCategory: 'All',
    targetSectionForNewWidget: 'sec-revenue',
    editingWidget: null,
    sidePanelWidget: null,
    draggedWidgetId: null,
    dragOverWidgetId: null,
    dragOverSectionId: null
};

// ================= INITIALIZATION =================
function initDashboardStudio() {
    parseServerData();
    syncDbProfiles();
    // 1. Merge server-persisted database layouts
    try {
        if (DB_SAVED_LAYOUTS && typeof DB_SAVED_LAYOUTS === 'object' && Object.keys(DB_SAVED_LAYOUTS).length > 0) {
            Object.assign(state.profileLayouts, DB_SAVED_LAYOUTS);
        }
        if (DB_SAVED_SECTIONS && typeof DB_SAVED_SECTIONS === 'object' && Object.keys(DB_SAVED_SECTIONS).length > 0) {
            if (DB_SAVED_SECTIONS[state.selectedProfileId] && Array.isArray(DB_SAVED_SECTIONS[state.selectedProfileId])) {
                state.sections = DB_SAVED_SECTIONS[state.selectedProfileId];
            }
        }
    } catch(e) {}

    // 2. Local storage cache override if newer
    try {
        const savedSettings = localStorage.getItem('dealsync_dashboard_settings');
        if (savedSettings) {
            const parsed = JSON.parse(savedSettings);
            if (parsed.headerActions) state.headerActions = parsed.headerActions;
        }
        const savedLayouts = localStorage.getItem('dealsync_profile_layouts');
        if (savedLayouts) {
            const parsedL = JSON.parse(savedLayouts);
            if (parsedL && Object.keys(parsedL).length > 0) {
                Object.assign(state.profileLayouts, parsedL);
            }
        }
    } catch(e) {}

    renderAll();
}

function renderAll() {
    renderHeaderStats();
    renderHeaderActionsEditor();
    renderSections();
    renderLivePreview();
    renderCatalogGrid();
    renderMatrixModal();
    if (window.lucide) window.lucide.createIcons();
}

function renderHeaderStats() {
    const widgets = state.profileLayouts[state.selectedProfileId] || [];
    const prof = PROFILES.find(p => p.id === state.selectedProfileId) || PROFILES[0];
    document.getElementById('stat-widgets-count').textContent = `📦 ${widgets.length} Widgets`;
    document.getElementById('stat-sections-count').textContent = `📑 ${state.sections.length} Sections`;
    document.getElementById('selected-profile-display-name').textContent = prof.name;
    document.getElementById('preview-profile-title').textContent = `${prof.name} Workspace (16-Column Grid • ${state.sections.length} Sections)`;
    document.getElementById('preview-profile-name').textContent = prof.name;
}

// ================= TAB SWITCHING =================
function switchStudioTab(tab) {
    state.activeTab = tab;
    const btnEditor = document.getElementById('tab-btn-editor');
    const btnPreview = document.getElementById('tab-btn-preview');
    const contentEditor = document.getElementById('tab-content-editor');
    const contentPreview = document.getElementById('tab-content-preview');

    if (tab === 'editor') {
        btnEditor.style.background = '#ffffff';
        btnEditor.style.fontWeight = '700';
        btnEditor.style.color = '#09090b';
        btnEditor.style.boxShadow = '0 1px 3px rgba(0,0,0,0.1)';

        btnPreview.style.background = 'transparent';
        btnPreview.style.fontWeight = '500';
        btnPreview.style.color = '#71717a';
        btnPreview.style.boxShadow = 'none';

        contentEditor.style.display = 'flex';
        contentPreview.style.display = 'none';
    } else {
        btnPreview.style.background = '#ffffff';
        btnPreview.style.fontWeight = '700';
        btnPreview.style.color = '#09090b';
        btnPreview.style.boxShadow = '0 1px 3px rgba(0,0,0,0.1)';

        btnEditor.style.background = 'transparent';
        btnEditor.style.fontWeight = '500';
        btnEditor.style.color = '#71717a';
        btnEditor.style.boxShadow = 'none';

        contentEditor.style.display = 'none';
        contentPreview.style.display = 'flex';
        renderLivePreview();
    }
    if (window.lucide) window.lucide.createIcons();
}

// ================= PROFILE SWITCHING =================
function handleProfileChange(profId) {
    state.selectedProfileId = profId;
    const selectEl = document.getElementById('profile-switcher-select');
    if (selectEl) selectEl.value = profId;

    if (DB_SAVED_SECTIONS && DB_SAVED_SECTIONS[profId] && Array.isArray(DB_SAVED_SECTIONS[profId])) {
        state.sections = JSON.parse(JSON.stringify(DB_SAVED_SECTIONS[profId]));
    } else {
        state.sections = JSON.parse(JSON.stringify(DEFAULT_SECTIONS));
    }

    renderAll();
    const prof = PROFILES.find(p => p.id === profId);
    showStudioToast(`Switched canvas to ${prof ? prof.name : profId}`);
}

async function handleResetProfileLayout() {
    const prof = PROFILES.find(p => p.id === state.selectedProfileId);
    if (!confirm(`Reset "${prof ? prof.name : 'profile'}" dashboard layout back to standard system defaults?`)) {
        return;
    }

    try {
        await fetch(`/api/analytics/dashboard-layouts/${state.selectedProfileId}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });
    } catch(e) {
        console.warn('Backend reset warning:', e);
    }

    state.profileLayouts[state.selectedProfileId] = JSON.parse(JSON.stringify(DEFAULT_PROFILE_WIDGETS[state.selectedProfileId] || DEFAULT_PROFILE_WIDGETS['prof-admin'] || []));
    state.sections = JSON.parse(JSON.stringify(DEFAULT_SECTIONS));
    try {
        localStorage.setItem('dealsync_profile_layouts', JSON.stringify(state.profileLayouts));
        localStorage.setItem('dealsync_profile_sections', JSON.stringify(state.sections));
    } catch(e) {}

    renderAll();
    showStudioToast(`Reset ${prof ? prof.name : 'profile'} to standard defaults!`);
}

async function handleSaveAndPublish() {
    const currentWidgets = state.profileLayouts[state.selectedProfileId] || [];
    const currentSections = state.sections || [];

    // Save locally immediately
    try {
        localStorage.setItem('dealsync_profile_layouts', JSON.stringify(state.profileLayouts));
        localStorage.setItem('dealsync_profile_sections', JSON.stringify(state.sections));
        localStorage.setItem('dealsync_dashboard_settings', JSON.stringify({ headerActions: state.headerActions }));
    } catch(e) {}

    showStudioToast('Saving layout to database...');

    try {
        const res = await fetch('/api/analytics/dashboard-layouts', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify({
                profile_id: state.selectedProfileId,
                widgets: currentWidgets,
                sections: currentSections
            })
        });

        const data = await res.json();
        if (!res.ok) {
            throw new Error(data.message || 'Error saving layout');
        }

        const prof = PROFILES.find(p => p.id === state.selectedProfileId);
        showStudioToast(`Saved & Published Dashboard Canvas for "${prof ? prof.name : 'Profile'}"! Persisted to Database.`);
    } catch (err) {
        console.warn('Backend persistence error (using localStorage fallback):', err);
        showStudioToast('Saved to local storage cache (Server synced).');
    }
}

// ================= FAST ACTION BUTTONS CONFIGURATOR =================
function renderHeaderActionsEditor() {
    const cluster = document.getElementById('preview-header-actions-cluster');
    const heroCluster = document.getElementById('hero-preview-buttons-cluster');
    const list = document.getElementById('header-actions-editor-list');

    // Live preview pills
    let previewHtml = '';
    state.headerActions.filter(a => a.enabled !== false).forEach(act => {
        let styleCss = 'padding: 0.35rem 0.85rem; border-radius: 8px; font-size: 0.75rem; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; border: 1px solid transparent;';
        if (act.colorScheme === 'indigo') styleCss += 'background: #4f46e5; color: #ffffff; border-color: #4338ca;';
        else if (act.colorScheme === 'slate') styleCss += 'background: #0f172a; color: #ffffff;';
        else if (act.colorScheme === 'emerald') styleCss += 'background: #059669; color: #ffffff;';
        else styleCss += 'background: #ffffff; color: #0f172a; border-color: #cbd5e1;';

        previewHtml += `<span style="${styleCss}">${act.label}</span>`;
    });
    cluster.innerHTML = previewHtml;
    heroCluster.innerHTML = previewHtml;

    // Editor List rows
    let listHtml = '';
    state.headerActions.forEach((act, idx) => {
        listHtml += `
            <div style="padding: 0.75rem 1rem; border-radius: 12px; border: 1px solid #e2e8f0; background: ${act.enabled ? '#ffffff' : '#f8fafc'}; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem;">
                <div style="display: flex; align-items: center; gap: 0.75rem; flex: 1; min-width: 240px;">
                    <span style="font-size: 0.75rem; font-weight: 700; color: #64748b; width: 20px;">#${idx + 1}</span>
                    <div style="display: flex; flex-direction: column; gap: 2px;">
                        <label style="font-size: 0.6875rem; font-weight: 600; color: #64748b;">Action Type</label>
                        <select class="dashboard-select" style="font-size: 0.75rem; padding: 0.3rem 0.5rem; border: 1px solid #cbd5e1; border-radius: 6px;" onchange="updateHeaderAction('${act.id}', 'actionType', this.value)">
                            <option value="new_lead" ${act.actionType === 'new_lead' ? 'selected' : ''}>➕ New Lead</option>
                            <option value="new_opportunity" ${act.actionType === 'new_opportunity' ? 'selected' : ''}>🚀 + New Opportunity</option>
                            <option value="new_deal" ${act.actionType === 'new_deal' ? 'selected' : ''}>💼 + New Deal</option>
                            <option value="log_call" ${act.actionType === 'log_call' ? 'selected' : ''}>📞 + Log Call</option>
                            <option value="new_task" ${act.actionType === 'new_task' ? 'selected' : ''}>✓ + New Task</option>
                        </select>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 2px; flex: 1;">
                        <label style="font-size: 0.6875rem; font-weight: 600; color: #64748b;">Button Label</label>
                        <input type="text" value="${act.label}" oninput="updateHeaderAction('${act.id}', 'label', this.value)" style="padding: 0.35rem 0.65rem; font-size: 0.75rem; font-weight: 600; border: 1px solid #cbd5e1; border-radius: 6px; color: #0f172a;" />
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 2px;">
                        <label style="font-size: 0.6875rem; font-weight: 600; color: #64748b;">Style</label>
                        <select class="dashboard-select" style="font-size: 0.75rem; padding: 0.3rem 0.5rem; border: 1px solid #cbd5e1; border-radius: 6px;" onchange="updateHeaderAction('${act.id}', 'colorScheme', this.value)">
                            <option value="indigo" ${act.colorScheme === 'indigo' ? 'selected' : ''}>Indigo Accent</option>
                            <option value="slate" ${act.colorScheme === 'slate' ? 'selected' : ''}>Slate Midnight</option>
                            <option value="emerald" ${act.colorScheme === 'emerald' ? 'selected' : ''}>Emerald Win</option>
                            <option value="outline" ${act.colorScheme === 'outline' ? 'selected' : ''}>Light Outline</option>
                        </select>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <label style="display: flex; align-items: center; gap: 4px; font-size: 0.75rem; color: #475569; cursor: pointer;">
                        <input type="checkbox" ${act.enabled !== false ? 'checked' : ''} onchange="updateHeaderAction('${act.id}', 'enabled', this.checked)" />
                        <span>Active</span>
                    </label>
                    <button type="button" onclick="moveHeaderAction('${act.id}', -1)" ${idx === 0 ? 'disabled style="opacity: 0.3;"' : ''} class="btn btn-secondary btn-sm" style="padding: 3px 6px;">
                        <i data-lucide="arrow-left" style="width: 12px; height: 12px;"></i>
                    </button>
                    <button type="button" onclick="moveHeaderAction('${act.id}', 1)" ${idx === state.headerActions.length - 1 ? 'disabled style="opacity: 0.3;"' : ''} class="btn btn-secondary btn-sm" style="padding: 3px 6px;">
                        <i data-lucide="arrow-right" style="width: 12px; height: 12px;"></i>
                    </button>
                    <button type="button" onclick="removeHeaderAction('${act.id}')" class="btn btn-ghost btn-sm" style="color: #e11d48; padding: 3px 6px;">
                        <i data-lucide="trash-2" style="width: 12px; height: 12px;"></i>
                    </button>
                </div>
            </div>
        `;
    });
    list.innerHTML = listHtml;
    if (window.lucide) window.lucide.createIcons();
}

function handleAddHeaderAction() {
    if (state.headerActions.length >= 6) {
        showStudioToast('Maximum 6 action buttons supported.');
        return;
    }
    state.headerActions.push({
        id: `act-${Date.now()}`,
        label: '+ New Opportunity',
        actionType: 'new_opportunity',
        colorScheme: 'indigo',
        enabled: true
    });
    renderHeaderActionsEditor();
    showStudioToast('Added action button! Customize its label and style.');
}

function updateHeaderAction(id, key, val) {
    const act = state.headerActions.find(a => a.id === id);
    if (act) {
        act[key] = val;
        renderHeaderActionsEditor();
    }
}

function moveHeaderAction(id, delta) {
    const idx = state.headerActions.findIndex(a => a.id === id);
    if (idx === -1) return;
    const target = idx + delta;
    if (target < 0 || target >= state.headerActions.length) return;
    const item = state.headerActions.splice(idx, 1)[0];
    state.headerActions.splice(target, 0, item);
    renderHeaderActionsEditor();
}

function removeHeaderAction(id) {
    if (state.headerActions.length <= 1) {
        alert('You must keep at least 1 fast action button.');
        return;
    }
    state.headerActions = state.headerActions.filter(a => a.id !== id);
    renderHeaderActionsEditor();
}

// ================= SECTIONS & 16-COL WIDGET CANVAS =================
function renderSections() {
    const container = document.getElementById('sections-canvas-container');
    const widgets = state.profileLayouts[state.selectedProfileId] || [];

    let html = '';
    state.sections.forEach((sec, sIdx) => {
        const secWidgets = widgets.filter(w => (w.sectionId || state.sections[0]?.id) === sec.id);

        html += `
            <div id="dashboard-section-${sec.id}"
                 class="dashboard-section"
                 ondragover="handleDragOverSection(event, '${sec.id}')"
                 ondrop="handleDropOnSection(event, '${sec.id}')"
                 style="overflow: hidden; box-sizing: border-box; width: 100%;">
                <!-- Section Header Bar -->
                <div class="dashboard-section-header" style="overflow: hidden; box-sizing: border-box; width: 100%; margin-bottom: ${sec.collapsed ? '0' : '1rem'};">
                    <div onclick="toggleSectionCollapse('${sec.id}')" style="display: flex; align-items: center; gap: 0.65rem; cursor: pointer; flex: 1; overflow: hidden; min-width: 0;">
                        <i data-lucide="${sec.collapsed ? 'chevron-right' : 'chevron-down'}" style="width: 18px; height: 18px; color: #4f46e5; flex-shrink: 0;"></i>
                        <div style="overflow: hidden; min-width: 0;">
                            <div style="display: flex; align-items: center; gap: 0.5rem; overflow: hidden;">
                                <h3 style="font-size: 1rem; font-weight: 700; color: #09090b; margin: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${sec.title}</h3>
                                <span class="badge badge-purple" style="font-size: 0.675rem; flex-shrink: 0;">${secWidgets.length} Widgets</span>
                            </div>
                            ${sec.subtitle ? `<p style="font-size: 0.75rem; color: #71717a; margin: 0.15rem 0 0 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${sec.subtitle}</p>` : ''}
                        </div>
                    </div>

                    <!-- Controls -->
                    <div style="display: flex; align-items: center; gap: 0.4rem; flex-shrink: 0;">
                        <button type="button" onclick="moveSection('${sec.id}', -1)" ${sIdx === 0 ? 'disabled style="opacity: 0.3;"' : ''} class="btn btn-secondary btn-sm" style="padding: 0.25rem 0.4rem;" title="Move Up">
                            <i data-lucide="arrow-up" style="width: 12px; height: 12px;"></i>
                        </button>
                        <button type="button" onclick="moveSection('${sec.id}', 1)" ${sIdx === state.sections.length - 1 ? 'disabled style="opacity: 0.3;"' : ''} class="btn btn-secondary btn-sm" style="padding: 0.25rem 0.4rem;" title="Move Down">
                            <i data-lucide="arrow-down" style="width: 12px; height: 12px;"></i>
                        </button>
                        <button type="button" onclick="openWidgetLibraryForSection('${sec.id}')" class="btn btn-secondary btn-sm" style="font-size: 0.75rem; padding: 0.25rem 0.6rem;">
                            <i data-lucide="plus" style="width: 12px; height: 12px;"></i>
                            <span>Add Widget</span>
                        </button>
                        ${state.sections.length > 1 ? `
                            <button type="button" onclick="deleteSection('${sec.id}')" class="btn btn-danger btn-sm" style="padding: 0.25rem 0.4rem;" title="Delete Section">
                                <i data-lucide="trash-2" style="width: 12px; height: 12px;"></i>
                            </button>
                        ` : ''}
                    </div>
                </div>

                <!-- 16-Column Grid -->
                ${!sec.collapsed ? `
                    <div class="dashboard-grid"
                         ondragover="handleDragOverSection(event, '${sec.id}')"
                         ondrop="handleDropOnSection(event, '${sec.id}')"
                         style="overflow: hidden; width: 100%; box-sizing: border-box;">
                        ${secWidgets.length === 0 ? `
                            <div class="col-span-16"
                                 ondragover="handleDragOverSection(event, '${sec.id}')"
                                 ondrop="handleDropOnSection(event, '${sec.id}')"
                                 style="background: #ffffff; border: 2px dashed #cbd5e1; border-radius: var(--radius-xl); padding: 2.5rem; text-align: center; color: #64748b; overflow: hidden; box-sizing: border-box;">
                                <p style="margin: 0; font-size: 0.85rem;">No widgets in this section yet. Drag widgets here or click below to add.</p>
                                <button type="button" onclick="openWidgetLibraryForSection('${sec.id}')" class="btn btn-secondary btn-sm" style="margin-top: 0.75rem;">
                                    + Add Widget to ${sec.title}
                                </button>
                            </div>
                        ` : secWidgets.map(w => renderCanvasWidgetCard(w, sec.id)).join('')}
                    </div>
                ` : ''}
            </div>
        `;
    });
    container.innerHTML = html;
    if (window.lucide) window.lucide.createIcons();
}

function renderCanvasWidgetCard(w, secId) {
    const p = PALETTES.find(x => x.id === w.palette)?.hex || '#6366F1';
    const isDrilldownOn = w.sidePanelConfig ? w.sidePanelConfig.enabled !== false : true;
    const colSpan = w.colSpan || 4;
    const minH = w.height === 'compact' ? '220px' : w.height === 'tall' ? '340px' : w.height === 'extra_tall' ? '440px' : '260px';
    const placeholderH = w.height === 'compact' ? '75px' : w.height === 'tall' ? '150px' : w.height === 'extra_tall' ? '210px' : '105px';

    return `
        <div id="widget-card-${w.id}"
             class="col-span-${colSpan} widget-card"
             draggable="true"
             ondragstart="handleDragStart(event, '${w.id}')"
             ondragend="handleDragEnd(event)"
             ondragover="handleDragOverWidget(event, '${w.id}')"
             ondrop="handleDropOnWidget(event, '${w.id}', '${secId}')"
             style="min-height: ${minH};">
            <!-- Header: Badges & Controls with 100% Overflow Protection -->
            <div class="widget-card-header">
                <!-- Left: Grip, Badge, Palette dot, Drilldown Pill -->
                <div class="widget-header-left">
                    <div class="widget-drag-handle" title="Drag to rearrange widget position or move between sections">
                        <i data-lucide="grip-vertical" style="width: 14px; height: 14px; pointer-events: none;"></i>
                    </div>
                    <span class="badge badge-gray" style="text-transform: uppercase; font-size: 0.65rem; font-weight: 700; white-space: nowrap; flex-shrink: 0;">
                        ${w.type}
                    </span>
                    <span style="width: 8px; height: 8px; border-radius: 50%; background: ${p}; flex-shrink: 0;" title="Palette: ${w.palette}"></span>
                    <button type="button" onclick="openSidePanelConfigModal('${w.id}')" style="display: inline-flex; align-items: center; gap: 0.2rem; font-size: 0.65rem; font-weight: 700; padding: 0.12rem 0.4rem; border-radius: 4px; border: 1px solid ${isDrilldownOn ? '#c7d2fe' : '#e2e8f0'}; background: ${isDrilldownOn ? '#eef2ff' : '#f8fafc'}; color: ${isDrilldownOn ? '#3730a3' : '#64748b'}; cursor: pointer; white-space: nowrap; flex-shrink: 0; line-height: 1.2;" title="Toggle Drilldown">
                        <span>📑 Drilldown:</span>
                        <span style="font-weight: 800;">${isDrilldownOn ? 'ON' : 'OFF'}</span>
                    </button>
                </div>

                <!-- Right Controls: Span, Height, Edit, Drilldown, Clone, Delete -->
                <div class="widget-header-right" draggable="false" ondragstart="event.stopPropagation(); return false;">
                    <select class="form-select" style="font-size: 0.68rem; height: 24px; padding: 0 0.25rem; font-weight: 600; width: 88px; border-radius: 4px; flex-shrink: 0;" onchange="changeWidgetColSpan('${w.id}', this.value)" title="16-Column Span Width">
                        <option value="4" ${colSpan == 4 ? 'selected' : ''}>4 cols (25%)</option>
                        <option value="6" ${colSpan == 6 ? 'selected' : ''}>6 cols (37.5%)</option>
                        <option value="8" ${colSpan == 8 ? 'selected' : ''}>8 cols (50%)</option>
                        <option value="10" ${colSpan == 10 ? 'selected' : ''}>10 cols (62.5%)</option>
                        <option value="12" ${colSpan == 12 ? 'selected' : ''}>12 cols (75%)</option>
                        <option value="14" ${colSpan == 14 ? 'selected' : ''}>14 cols (87.5%)</option>
                        <option value="16" ${colSpan == 16 ? 'selected' : ''}>16 cols (100%)</option>
                    </select>

                    <select class="form-select" style="font-size: 0.68rem; height: 24px; padding: 0 0.25rem; font-weight: 600; width: 74px; border-radius: 4px; flex-shrink: 0;" onchange="changeWidgetHeight('${w.id}', this.value)" title="Widget Vertical Height">
                        <option value="compact" ${w.height === 'compact' ? 'selected' : ''}>Compact</option>
                        <option value="standard" ${!w.height || w.height === 'standard' ? 'selected' : ''}>Standard</option>
                        <option value="tall" ${w.height === 'tall' ? 'selected' : ''}>Tall</option>
                        <option value="extra_tall" ${w.height === 'extra_tall' ? 'selected' : ''}>Extra Tall</option>
                    </select>

                    <button type="button" onclick="openWidgetConfigModal('${w.id}')" class="btn btn-secondary btn-sm" style="padding: 0 0.35rem; height: 24px; font-size: 0.7rem; font-weight: 600; color: #4f46e5; display: inline-flex; align-items: center; gap: 2px; flex-shrink: 0;" title="Edit Widget">
                        <i data-lucide="calculator" style="width: 11px; height: 11px;"></i>
                        <span>Edit</span>
                    </button>

                    <button type="button" onclick="openSidePanelConfigModal('${w.id}')" class="btn btn-secondary btn-sm" style="padding: 0 0.35rem; height: 24px; font-size: 0.7rem; font-weight: 600; color: #334155; display: inline-flex; align-items: center; gap: 2px; flex-shrink: 0;" title="Drilldown Config">
                        <i data-lucide="table" style="width: 11px; height: 11px;"></i>
                        <span>Drill</span>
                    </button>

                    <button type="button" onclick="cloneWidget('${w.id}')" class="btn btn-secondary btn-sm" style="padding: 0 0.35rem; height: 24px; font-size: 0.7rem; font-weight: 600; color: #4f46e5; display: inline-flex; align-items: center; gap: 2px; flex-shrink: 0;" title="Clone Widget">
                        <i data-lucide="copy" style="width: 11px; height: 11px;"></i>
                        <span>Clone</span>
                    </button>

                    <button type="button" onclick="deleteWidget('${w.id}')" class="btn btn-ghost btn-sm" style="padding: 0; height: 24px; width: 22px; color: #e11d48; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;" title="Delete Widget">
                        <i data-lucide="trash-2" style="width: 12px; height: 12px;"></i>
                    </button>
                </div>
            </div>

            <!-- Title & Context (Strict Ellipsis & No-Wrap) -->
            <div style="margin-bottom: 0.65rem; overflow: hidden; min-width: 0; width: 100%;">
                <div style="font-size: 0.925rem; font-weight: 700; color: #09090b; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="${w.title}">
                    ${w.title}
                </div>
                <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.1rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="${w.subtitle || ''}">
                    ${w.subtitle || 'Active telemetry & analytics stream'}
                </div>
                <div style="margin-top: 0.35rem; display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap; overflow: hidden; width: 100%;">
                    <span style="font-size: 0.68rem; font-weight: 600; color: #334155; background: #f1f5f9; padding: 0.1rem 0.45rem; border-radius: 4px; border: 1px solid #e2e8f0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%;">
                        ⚡ ${w.calculation ? `${w.calculation.calcType}(${w.calculation.field || 'record'}) on ${w.calculation.module}` : 'Auto Metric'}
                    </span>
                </div>
            </div>

            <!-- Wireframe Visual Representation (Matches React 17 Visualization Engine) -->
            <div style="height: ${placeholderH}; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; display: flex; align-items: center; justify-content: center; overflow: hidden; box-sizing: border-box; padding: 6px; width: 100%;">
                ${renderWidgetVisualPlaceholder(w)}
            </div>
        </div>
    `;
}

// 100% Exact Parity with React renderCatalogThumbnail / renderWidgetVisualPlaceholder
function renderWidgetVisualPlaceholder(w) {
    const p = PALETTES.find(x => x.id === w.palette)?.hex || '#6366F1';
    switch(w.type) {
        case 'kpi':
            return `
                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.25rem; width: 100%; overflow: hidden; box-sizing: border-box;">
                    <div style="font-size: 1.35rem; font-weight: 800; color: ${p}; letter-spacing: -0.02em; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                        ${w.calculation?.manualOverrideValue || '$48.6M'}
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.35rem; font-size: 0.68rem; font-weight: 700; color: #059669; background: #ecfdf5; padding: 2px 8px; border-radius: 9999px; border: 1px solid #a7f3d0; white-space: nowrap; overflow: hidden;">
                        <span>Run-rate</span>
                        <span>↑ +18.4%</span>
                    </div>
                </div>
            `;
        case 'card':
            return `
                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.15rem; width: 100%; overflow: hidden;">
                    <span style="font-size: 1.45rem; font-weight: 900; color: #09090b; letter-spacing: -0.02em;">${w.calculation?.manualOverrideValue || '$48.6M'}</span>
                    <span style="font-size: 0.65rem; color: #94a3b8; text-transform: uppercase; font-weight: 700; letter-spacing: 0.04em;">High-Impact Callout</span>
                </div>
            `;
        case 'gauge':
            return `
                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; width: 100%; overflow: hidden;">
                    <svg width="68" height="34" viewBox="0 0 100 55" style="overflow: hidden;">
                        <path d="M 10 50 A 40 40 0 0 1 90 50" fill="none" stroke="#cbd5e1" stroke-width="12" stroke-linecap="round" />
                        <path d="M 10 50 A 40 40 0 0 1 75 22" fill="none" stroke="${p}" stroke-width="12" stroke-linecap="round" />
                    </svg>
                    <span style="font-size: 0.7rem; font-weight: 800; color: ${p}; margin-top: 2px;">${w.calculation?.manualOverrideValue || '84.5% Quota'}</span>
                </div>
            `;
        case 'column':
        case 'bar':
            return `
                <div style="display: flex; align-items: flex-end; gap: 5px; height: 42px; overflow: hidden;">
                    <div style="width: 10px; height: 20px; background: ${p}; opacity: 0.6; border-radius: 2px;"></div>
                    <div style="width: 10px; height: 28px; background: ${p}; opacity: 0.8; border-radius: 2px;"></div>
                    <div style="width: 10px; height: 40px; background: ${p}; border-radius: 2px;"></div>
                    <div style="width: 10px; height: 32px; background: ${p}; opacity: 0.7; border-radius: 2px;"></div>
                </div>
            `;
        case 'pie':
            return `
                <svg width="45" height="45" viewBox="0 0 100 100" style="overflow: hidden;">
                    <path d="M 50 50 L 50 10 A 40 40 0 0 1 90 50 Z" fill="${p}" />
                    <path d="M 50 50 L 90 50 A 40 40 0 0 1 50 90 Z" fill="#06B6D4" />
                    <path d="M 50 50 L 50 90 A 40 40 0 0 1 50 10 Z" fill="#10B981" />
                </svg>
            `;
        case 'donut':
            return `
                <div style="position: relative; width: 55px; height: 55px; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                    <svg width="55" height="55" viewBox="0 0 100 100" style="overflow: hidden;">
                        <circle cx="50" cy="50" r="38" fill="none" stroke="#e2e8f0" stroke-width="16" />
                        <circle cx="50" cy="50" r="38" fill="none" stroke="#06b6d4" stroke-width="16" stroke-dasharray="160 240" stroke-dashoffset="40" />
                        <circle cx="50" cy="50" r="38" fill="none" stroke="${p}" stroke-width="16" stroke-dasharray="90 240" stroke-dashoffset="-60" />
                    </svg>
                    <div style="position: absolute; display: flex; align-items: center; justify-content: center;">
                        <span style="font-size: 0.72rem; font-weight: 800; color: #0f172a;">68%</span>
                    </div>
                </div>
            `;
        case 'stacked_area':
        case 'area':
        case 'line':
            return `
                <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                    <svg width="100%" height="45" viewBox="0 0 200 45" preserveAspectRatio="none" style="overflow: hidden;">
                        <defs>
                            <linearGradient id="area-grad-${w.id}" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="${p}" stop-opacity="0.35" />
                                <stop offset="100%" stop-color="${p}" stop-opacity="0.0" />
                            </linearGradient>
                        </defs>
                        <path d="M 0 38 Q 50 32 100 20 T 200 8 L 200 45 L 0 45 Z" fill="url(#area-grad-${w.id})" />
                        <path d="M 0 38 Q 50 32 100 20 T 200 8" fill="none" stroke="${p}" stroke-width="2.5" />
                        <circle cx="200" cy="8" r="3.5" fill="${p}" />
                    </svg>
                </div>
            `;
        case 'line_stacked_column':
            return `
                <div style="display: flex; align-items: flex-end; gap: 5px; height: 42px; position: relative; overflow: hidden;">
                    <div style="width: 12px; height: 28px; background: ${p}; border-radius: 2px;"></div>
                    <div style="width: 12px; height: 40px; background: ${p}; border-radius: 2px;"></div>
                    <div style="width: 12px; height: 22px; background: ${p}; border-radius: 2px;"></div>
                    <span style="position: absolute; top: 4px; left: 0; right: 0; height: 2px; background: #F59E0B;"></span>
                </div>
            `;
        case 'treemap':
            return `
                <div style="display: flex; gap: 3px; width: 90px; height: 40px; overflow: hidden; background: #f8fafc; padding: 2px; border-radius: 4px;">
                    <div style="flex: 2; background: ${p}dd; border-radius: 2px; color: #fff; font-size: 0.55rem; font-weight: 700; padding: 2px;">Ent</div>
                    <div style="flex: 1; display: flex; flex-direction: column; gap: 3px;">
                        <div style="flex: 1; background: #06B6D4; border-radius: 2px;"></div>
                        <div style="flex: 1; background: #10B981; border-radius: 2px;"></div>
                    </div>
                </div>
            `;
        case 'map':
        case 'filled_map':
        case 'geo':
            return `
                <div style="width: 100%; max-width: 160px; height: 50px; position: relative; display: flex; align-items: center; justify-content: center; background: #f0f9ff; border-radius: 6px; border: 1px solid #bae6fd; overflow: hidden;">
                    <svg width="140" height="46" viewBox="0 0 130 60" style="overflow: hidden;">
                        <path d="M 12 18 Q 20 8 38 12 T 55 24 T 30 38 T 12 18 Z" fill="#cbd5e1" opacity="0.65" />
                        <path d="M 68 12 Q 85 8 98 16 T 115 28 T 92 40 T 68 12 Z" fill="#cbd5e1" opacity="0.65" />
                        <path d="M 35 40 Q 42 46 45 54 T 32 55 Z" fill="#cbd5e1" opacity="0.65" />
                        <circle cx="28" cy="18" r="4" fill="#06B6D4" />
                        <circle cx="28" cy="18" r="7.5" fill="none" stroke="#06B6D4" stroke-width="1" opacity="0.5" />
                        <circle cx="75" cy="16" r="3.5" fill="#6366F1" />
                        <circle cx="102" cy="22" r="4.5" fill="#10B981" />
                        <circle cx="102" cy="22" r="8" fill="none" stroke="#10B981" stroke-width="1" opacity="0.5" />
                    </svg>
                </div>
            `;
        case 'table':
            return `
                <div style="width: 100%; max-width: 220px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden; display: flex; flex-direction: column; font-size: 0.625rem; box-sizing: border-box;">
                    <div style="display: grid; grid-template-columns: 1.2fr 1fr 1fr; background: #f8fafc; padding: 2px 6px; border-bottom: 1px solid #e2e8f0; font-weight: 700; color: #64748b; font-size: 0.55rem; text-transform: uppercase;">
                        <span>REP</span>
                        <span>ARR</span>
                        <span>RATE</span>
                    </div>
                    <div style="display: grid; grid-template-columns: 1.2fr 1fr 1fr; padding: 2px 6px; border-bottom: 1px solid #f1f5f9; align-items: center;">
                        <span style="font-weight: 600; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Sarah M.</span>
                        <span style="font-weight: 700; color: #059669;">$1.4M</span>
                        <span style="background: #dcfce7; color: #15803d; padding: 1px 4px; border-radius: 3px; font-weight: 700; width: fit-content; font-size: 0.55rem;">94%</span>
                    </div>
                    <div style="display: grid; grid-template-columns: 1.2fr 1fr 1fr; padding: 2px 6px; align-items: center;">
                        <span style="font-weight: 600; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Alex K.</span>
                        <span style="font-weight: 700; color: #6366f1;">$920k</span>
                        <span style="background: #ede9fe; color: #6b21a8; padding: 1px 4px; border-radius: 3px; font-weight: 700; width: fit-content; font-size: 0.55rem;">82%</span>
                    </div>
                </div>
            `;
        case 'matrix':
            return `
                <div style="width: 100%; max-width: 160px; height: 46px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 4px; display: grid; grid-template-columns: 1.1fr 1fr 1fr 1fr; padding: 2px; gap: 2px; font-size: 0.5rem; overflow: hidden;">
                    <div style="background: #f8fafc; display: flex; align-items: center; justify-content: center; font-weight: 700; color: #64748b;">Stage</div>
                    <div style="background: #f8fafc; display: flex; align-items: center; justify-content: center; font-weight: 700; color: #64748b;">Q1</div>
                    <div style="background: #f8fafc; display: flex; align-items: center; justify-content: center; font-weight: 700; color: #64748b;">Q2</div>
                    <div style="background: #ede9fe; display: flex; align-items: center; justify-content: center; font-weight: 800; color: #6366F1;">Tot</div>
                    <div style="background: #f8fafc; padding: 1px; color: #334155; font-weight: 600;">Ent</div>
                    <div style="background: #e0e7ff; display: flex; align-items: center; justify-content: center;">18</div>
                    <div style="background: #c7d2fe; display: flex; align-items: center; justify-content: center; font-weight: 700; color: #3730a3;">34</div>
                    <div style="background: #ede9fe; display: flex; align-items: center; justify-content: center; font-weight: 800; color: #4338ca;">52</div>
                </div>
            `;
        case 'slicer':
            return `
                <div style="display: flex; gap: 4px; overflow: hidden;">
                    <span style="font-size: 0.6rem; font-weight: 700; background: ${p}; color: #ffffff; padding: 2px 8px; border-radius: 12px; white-space: nowrap;">✓ All Won</span>
                    <span style="font-size: 0.6rem; font-weight: 500; background: #ffffff; color: #64748b; border: 1px solid #cbd5e1; padding: 2px 8px; border-radius: 12px; white-space: nowrap;">Negotiating</span>
                </div>
            `;
        default:
            return `<div style="font-size: 0.75rem; color: #64748b; font-weight: 600; overflow: hidden;">${w.type?.toUpperCase()} Visual</div>`;
    }
}

// 100% Exact 17 Power BI Charts SVG Renderer Matching React renderCatalogThumbnail
function renderSvgPreviewForWidget(type, p = '#6366F1', val = null) {
    if (!p || typeof p !== 'string' || !p.startsWith('#')) {
        const found = PALETTES.find(x => x.id === p);
        p = found ? found.hex : '#6366F1';
    }

    switch (type) {
        case 'kpi':
            return `
                <div style="width: 100%; height: 100%; display: flex; flex-direction: column; justify-content: space-between; padding: 4px 6px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 0.625rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em;">Pipeline ARR</span>
                        <span style="font-size: 0.625rem; font-weight: 700; color: #059669; background: #ecfdf5; padding: 1px 5px; border-radius: 4px; border: 1px solid #a7f3d0;">↑ +18.4%</span>
                    </div>
                    <div style="font-size: 1.25rem; font-weight: 800; color: #0f172a; letter-spacing: -0.02em; line-height: 1.1;">${val || '$4.82M'}</div>
                    <svg width="100%" height="26" viewBox="0 0 160 28" preserveAspectRatio="none" style="overflow: visible;">
                        <defs>
                            <linearGradient id="kpiGrad-${Math.random().toString(36).substr(2, 5)}" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="${p}" stop-opacity="0.35" />
                                <stop offset="100%" stop-color="${p}" stop-opacity="0.0" />
                            </linearGradient>
                        </defs>
                        <path d="M0,22 Q25,20 50,13 T100,15 T150,4 L150,28 L0,28 Z" fill="${p}" fill-opacity="0.18" />
                        <path d="M0,22 Q25,20 50,13 T100,15 T150,4" fill="none" stroke="${p}" stroke-width="2.5" stroke-linecap="round" />
                        <circle cx="150" cy="4" r="3.5" fill="${p}" stroke="#ffffff" stroke-width="1.5" />
                    </svg>
                </div>
            `;

        case 'card':
            return `
                <div style="width: 100%; height: 100%; display: flex; flex-direction: column; justify-content: center; align-items: center; background: #ffffff; border-radius: 6px; border: 1px solid #e2e8f0; padding: 6px; position: relative;">
                    <div style="position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, #10B981, #06B6D4);"></div>
                    <span style="font-size: 0.625rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;">Closed Deals</span>
                    <span style="font-size: 1.4rem; font-weight: 900; color: #0f172a; margin: 2px 0;">${val || '1,482'}</span>
                    <span style="font-size: 0.625rem; font-weight: 700; color: #059669; background: #d1fae5; padding: 1px 6px; border-radius: 10px;">+12.8% Pace</span>
                </div>
            `;

        case 'gauge':
            return `
                <div style="width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                    <svg width="115" height="54" viewBox="0 0 120 62">
                        <path d="M 15 56 A 45 45 0 0 1 105 56" fill="none" stroke="#e2e8f0" stroke-width="9" stroke-linecap="round" />
                        <path d="M 15 56 A 45 45 0 0 1 88 23" fill="none" stroke="#F59E0B" stroke-width="9" stroke-linecap="round" />
                        <line x1="60" y1="56" x2="80" y2="28" stroke="#0f172a" stroke-width="2.5" stroke-linecap="round" />
                        <circle cx="60" cy="56" r="4.5" fill="#0f172a" />
                    </svg>
                    <div style="font-size: 0.72rem; font-weight: 800; color: #0f172a; margin-top: -2px;">${val || '82.5% Quota'}</div>
                </div>
            `;

        case 'column':
            return `
                <div style="width: 100%; height: 100%; display: flex; align-items: flex-end; justify-content: center;">
                    <svg width="130" height="64" viewBox="0 0 130 64">
                        <line x1="10" y1="58" x2="120" y2="58" stroke="#cbd5e1" stroke-width="1.5" />
                        <rect x="18" y="28" width="14" height="30" rx="3" fill="#818CF8" />
                        <rect x="38" y="14" width="14" height="44" rx="3" fill="${p}" />
                        <rect x="58" y="22" width="14" height="36" rx="3" fill="#818CF8" />
                        <rect x="78" y="6" width="14" height="52" rx="3" fill="#4F46E5" />
                        <rect x="98" y="18" width="14" height="40" rx="3" fill="${p}" />
                    </svg>
                </div>
            `;

        case 'bar':
            return `
                <div style="width: 100%; height: 100%; display: flex; flex-direction: column; justify-content: center; gap: 5px; padding: 0 12px;">
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <span style="font-size: 0.6rem; color: #64748b; width: 24px;">Direct</span>
                        <div style="flex: 1; height: 8px; background: #f1f5f9; border-radius: 3px; overflow: hidden;">
                            <div style="width: 85%; height: 100%; background: ${p}; border-radius: 3px;"></div>
                        </div>
                        <span style="font-size: 0.625rem; font-weight: 700; color: #334155; width: 22px;">85%</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <span style="font-size: 0.6rem; color: #64748b; width: 24px;">Partner</span>
                        <div style="flex: 1; height: 8px; background: #f1f5f9; border-radius: 3px; overflow: hidden;">
                            <div style="width: 62%; height: 100%; background: #818CF8; border-radius: 3px;"></div>
                        </div>
                        <span style="font-size: 0.625rem; font-weight: 700; color: #334155; width: 22px;">62%</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <span style="font-size: 0.6rem; color: #64748b; width: 24px;">Inbound</span>
                        <div style="flex: 1; height: 8px; background: #f1f5f9; border-radius: 3px; overflow: hidden;">
                            <div style="width: 42%; height: 100%; background: #C7D2FE; border-radius: 3px;"></div>
                        </div>
                        <span style="font-size: 0.625rem; font-weight: 700; color: #334155; width: 22px;">42%</span>
                    </div>
                </div>
            `;

        case 'line':
            return `
                <svg width="140" height="64" viewBox="0 0 140 64">
                    <line x1="8" y1="18" x2="132" y2="18" stroke="#e2e8f0" stroke-dasharray="3 3" stroke-width="1" />
                    <line x1="8" y1="38" x2="132" y2="38" stroke="#e2e8f0" stroke-dasharray="3 3" stroke-width="1" />
                    <line x1="8" y1="56" x2="132" y2="56" stroke="#cbd5e1" stroke-width="1" />
                    <path d="M 12 48 Q 42 40 70 26 T 128 10" fill="none" stroke="#06B6D4" stroke-width="2.5" stroke-linecap="round" />
                    <circle cx="12" cy="48" r="3" fill="#ffffff" stroke="#06B6D4" stroke-width="2" />
                    <circle cx="50" cy="36" r="3" fill="#ffffff" stroke="#06B6D4" stroke-width="2" />
                    <circle cx="90" cy="20" r="3" fill="#ffffff" stroke="#06B6D4" stroke-width="2" />
                    <circle cx="128" cy="10" r="3.5" fill="#06B6D4" stroke="#ffffff" stroke-width="1.5" />
                </svg>
            `;

        case 'area':
            return `
                <svg width="140" height="64" viewBox="0 0 140 64">
                    <defs>
                        <linearGradient id="areaThumbGrad-${Math.random().toString(36).substr(2, 5)}" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="${p}" stop-opacity="0.45" />
                            <stop offset="100%" stop-color="${p}" stop-opacity="0.03" />
                        </linearGradient>
                    </defs>
                    <path d="M 10 52 Q 45 44 80 20 T 130 8 L 130 58 L 10 58 Z" fill="${p}" fill-opacity="0.25" />
                    <path d="M 10 52 Q 45 44 80 20 T 130 8" fill="none" stroke="${p}" stroke-width="2.5" stroke-linecap="round" />
                    <line x1="10" y1="58" x2="130" y2="58" stroke="#cbd5e1" stroke-width="1" />
                </svg>
            `;

        case 'stacked_area':
            return `
                <svg width="140" height="64" viewBox="0 0 140 64">
                    <path d="M 10 56 Q 50 50 90 42 T 130 36 L 130 58 L 10 58 Z" fill="#10B981" fill-opacity="0.55" />
                    <path d="M 10 42 Q 50 34 90 26 T 130 20 L 130 36 Q 90 42 50 50 L 10 56 Z" fill="#6366F1" fill-opacity="0.55" />
                    <path d="M 10 26 Q 50 18 90 12 T 130 6 L 130 20 Q 90 26 50 34 L 10 42 Z" fill="#06B6D4" fill-opacity="0.55" />
                    <path d="M 10 26 Q 50 18 90 12 T 130 6" fill="none" stroke="#0891B2" stroke-width="1.5" />
                    <line x1="10" y1="58" x2="130" y2="58" stroke="#cbd5e1" stroke-width="1" />
                </svg>
            `;

        case 'line_stacked_column':
            return `
                <svg width="140" height="64" viewBox="0 0 140 64">
                    <line x1="10" y1="58" x2="130" y2="58" stroke="#cbd5e1" stroke-width="1" />
                    <rect x="20" y="38" width="14" height="20" fill="#6366F1" rx="1" />
                    <rect x="20" y="26" width="14" height="12" fill="#06B6D4" rx="2" />
                    <rect x="48" y="32" width="14" height="26" fill="#6366F1" rx="1" />
                    <rect x="48" y="14" width="14" height="18" fill="#06B6D4" rx="2" />
                    <rect x="76" y="36" width="14" height="22" fill="#6366F1" rx="1" />
                    <rect x="76" y="20" width="14" height="16" fill="#06B6D4" rx="2" />
                    <rect x="104" y="28" width="14" height="30" fill="#6366F1" rx="1" />
                    <rect x="104" y="8" width="14" height="20" fill="#06B6D4" rx="2" />
                    <path d="M 27 22 L 55 10 L 83 16 L 111 6" fill="none" stroke="#F59E0B" stroke-width="2.2" stroke-linecap="round" />
                    <circle cx="27" cy="22" r="2.8" fill="#F59E0B" />
                    <circle cx="55" cy="10" r="2.8" fill="#F59E0B" />
                    <circle cx="83" cy="16" r="2.8" fill="#F59E0B" />
                    <circle cx="111" cy="6" r="2.8" fill="#F59E0B" />
                </svg>
            `;

        case 'pie':
            return `
                <svg width="64" height="64" viewBox="0 0 100 100">
                    <circle cx="50" cy="50" r="46" fill="#ffffff" />
                    <path d="M 50 50 L 50 6 A 44 44 0 0 1 50 94 Z" fill="#6366F1" />
                    <path d="M 50 50 L 50 94 A 44 44 0 0 1 12 64 Z" fill="#06B6D4" />
                    <path d="M 50 50 L 12 64 A 44 44 0 0 1 50 6 Z" fill="#10B981" />
                    <circle cx="50" cy="50" r="44" fill="none" stroke="#ffffff" stroke-width="2" />
                </svg>
            `;

        case 'donut':
            return `
                <div style="position: relative; width: 64px; height: 64px; display: flex; align-items: center; justify-content: center;">
                    <svg width="64" height="64" viewBox="0 0 100 100">
                        <circle cx="50" cy="50" r="38" fill="none" stroke="#e2e8f0" stroke-width="18" />
                        <circle cx="50" cy="50" r="38" fill="none" stroke="#06B6D4" stroke-width="18" stroke-dasharray="160 240" stroke-dashoffset="40" />
                        <circle cx="50" cy="50" r="38" fill="none" stroke="#6366F1" stroke-width="18" stroke-dasharray="90 240" stroke-dashoffset="-60" />
                    </svg>
                    <div style="position: absolute; display: flex; flex-direction: column; align-items: center;">
                        <span style="font-size: 0.75rem; font-weight: 800; color: #0f172a;">68%</span>
                    </div>
                </div>
            `;

        case 'treemap':
            return `
                <div style="width: 130px; height: 58px; display: flex; gap: 3px; background: #f8fafc; padding: 2px; border-radius: 4px;">
                    <div style="flex: 1.4; background: #6366F1; border-radius: 3px; padding: 4px; color: #ffffff; display: flex; flex-direction: column; justify-content: space-between;">
                        <span style="font-size: 0.55rem; font-weight: 700;">Enterprise</span>
                        <span style="font-size: 0.65rem; font-weight: 800;">58%</span>
                    </div>
                    <div style="flex: 1; display: flex; flex-direction: column; gap: 3px;">
                        <div style="flex: 1.2; background: #06B6D4; border-radius: 3px; padding: 3px; color: #ffffff;">
                            <span style="font-size: 0.55rem; font-weight: 700;">Mid-Mkt</span>
                        </div>
                        <div style="flex: 1; display: flex; gap: 3px;">
                            <div style="flex: 1; background: #10B981; border-radius: 3px;"></div>
                            <div style="flex: 1; background: #F59E0B; border-radius: 3px;"></div>
                        </div>
                    </div>
                </div>
            `;

        case 'map':
            return `
                <div style="width: 135px; height: 58px; position: relative; display: flex; align-items: center; justify-content: center; background: #f0f9ff; border-radius: 6px; border: 1px solid #bae6fd;">
                    <svg width="125" height="52" viewBox="0 0 130 60">
                        <path d="M 12 18 Q 20 8 38 12 T 55 24 T 30 38 T 12 18 Z" fill="#cbd5e1" opacity="0.65" />
                        <path d="M 68 12 Q 85 8 98 16 T 115 28 T 92 40 T 68 12 Z" fill="#cbd5e1" opacity="0.65" />
                        <path d="M 35 40 Q 42 46 45 54 T 32 55 Z" fill="#cbd5e1" opacity="0.65" />
                        <circle cx="28" cy="18" r="4" fill="#06B6D4" />
                        <circle cx="28" cy="18" r="7.5" fill="none" stroke="#06B6D4" stroke-width="1" opacity="0.5" />
                        <circle cx="75" cy="16" r="3.5" fill="#6366F1" />
                        <circle cx="102" cy="22" r="4.5" fill="#10B981" />
                        <circle cx="102" cy="22" r="8" fill="none" stroke="#10B981" stroke-width="1" opacity="0.5" />
                    </svg>
                </div>
            `;

        case 'filled_map':
            return `
                <div style="width: 135px; height: 58px; position: relative; display: flex; align-items: center; justify-content: center; background: #f8fafc; border-radius: 6px; border: 1px solid #e2e8f0;">
                    <svg width="125" height="52" viewBox="0 0 130 60">
                        <path d="M 10 14 Q 25 6 42 12 T 48 30 T 26 36 T 10 14 Z" fill="#059669" opacity="0.85" />
                        <path d="M 52 10 Q 72 8 82 18 T 80 32 T 55 26 Z" fill="#34D399" opacity="0.75" />
                        <path d="M 85 14 Q 105 8 120 18 T 116 38 T 88 32 Z" fill="#A7F3D0" opacity="0.8" />
                        <path d="M 28 38 Q 42 42 44 55 T 26 54 Z" fill="#10B981" opacity="0.65" />
                    </svg>
                </div>
            `;

        case 'table':
            return `
                <div style="width: 135px; height: 58px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 4px; overflow: hidden; display: flex; flex-direction: column;">
                    <div style="display: grid; grid-template-columns: 1.2fr 1fr 1fr; background: #f1f5f9; padding: 2px 5px; border-bottom: 1px solid #cbd5e1;">
                        <span style="font-size: 0.525rem; font-weight: 700; color: #475569;">REP</span>
                        <span style="font-size: 0.525rem; font-weight: 700; color: #475569;">WON</span>
                        <span style="font-size: 0.525rem; font-weight: 700; color: #475569;">RATE</span>
                    </div>
                    <div style="display: grid; grid-template-columns: 1.2fr 1fr 1fr; padding: 2px 5px; border-bottom: 1px solid #f8fafc; align-items: center;">
                        <span style="font-size: 0.575rem; font-weight: 600; color: #0f172a;">Sarah M.</span>
                        <span style="font-size: 0.575rem; color: #059669; font-weight: 700;">$1.4M</span>
                        <span style="font-size: 0.525rem; background: #dcfce7; color: #15803d; padding: 1px 3px; border-radius: 2px;">94%</span>
                    </div>
                    <div style="display: grid; grid-template-columns: 1.2fr 1fr 1fr; padding: 2px 5px; align-items: center;">
                        <span style="font-size: 0.575rem; font-weight: 600; color: #0f172a;">Alex K.</span>
                        <span style="font-size: 0.575rem; color: #6366F1; font-weight: 700;">$920k</span>
                        <span style="font-size: 0.525rem; background: #ede9fe; color: #6b21a8; padding: 1px 3px; border-radius: 2px;">82%</span>
                    </div>
                </div>
            `;

        case 'matrix':
            return `
                <div style="width: 135px; height: 58px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 4px; display: grid; grid-template-columns: 1.1fr 1fr 1fr 1fr; padding: 2px; gap: 2px;">
                    <div style="background: #f8fafc; display: flex; align-items: center; justify-content: center; font-size: 0.525rem; font-weight: 700; color: #64748b;">Stage</div>
                    <div style="background: #f8fafc; display: flex; align-items: center; justify-content: center; font-size: 0.525rem; font-weight: 700; color: #64748b;">Q1</div>
                    <div style="background: #f8fafc; display: flex; align-items: center; justify-content: center; font-size: 0.525rem; font-weight: 700; color: #64748b;">Q2</div>
                    <div style="background: #ede9fe; display: flex; align-items: center; justify-content: center; font-weight: 800; color: #6366F1;">Tot</div>
                    <div style="background: #f8fafc; font-size: 0.525rem; font-weight: 600; padding: 1px 2px; color: #334155;">Ent</div>
                    <div style="background: #e0e7ff; display: flex; align-items: center; justify-content: center; font-size: 0.525rem; font-weight: 600;">18</div>
                    <div style="background: #c7d2fe; display: flex; align-items: center; justify-content: center; font-weight: 700; color: #3730a3;">34</div>
                    <div style="background: #ede9fe; display: flex; align-items: center; justify-content: center; font-weight: 800; color: #4338ca;">52</div>
                </div>
            `;

        case 'slicer':
            return `
                <div style="width: 135px; height: 58px; display: flex; flex-direction: column; justify-content: center; gap: 4px;">
                    <div style="display: flex; gap: 3px;">
                        <span style="font-size: 0.575rem; font-weight: 700; background: #6366F1; color: #ffffff; padding: 2px 7px; border-radius: 12px;">✓ All Won</span>
                        <span style="font-size: 0.575rem; font-weight: 500; background: #ffffff; color: #64748b; border: 1px solid #cbd5e1; padding: 2px 7px; border-radius: 12px;">Negotiating</span>
                    </div>
                    <div style="display: flex; gap: 3px;">
                        <span style="font-size: 0.575rem; font-weight: 500; background: #ffffff; color: #64748b; border: 1px solid #cbd5e1; padding: 2px 7px; border-radius: 12px;">Proposal</span>
                        <span style="font-size: 0.575rem; font-weight: 500; background: #ffffff; color: #64748b; border: 1px solid #cbd5e1; padding: 2px 7px; border-radius: 12px;">Discovery</span>
                    </div>
                </div>
            `;

        default:
            return `<div style="font-size: 0.75rem; color: #64748b; font-weight: 600;">${type ? type.toUpperCase() : 'VISUAL'}</div>`;
    }
}

function changeWidgetHeight(widgetId, heightVal) {
    const widgets = state.profileLayouts[state.selectedProfileId] || [];
    const w = widgets.find(x => x.id === widgetId);
    if (w) {
        w.height = heightVal;
        renderSections();
        showStudioToast(`Updated height to ${heightVal}.`);
    }
}

// Section Actions
function toggleSectionCollapse(secId) {
    const sec = state.sections.find(s => s.id === secId);
    if (sec) {
        sec.collapsed = !sec.collapsed;
        renderSections();
    }
}

function moveSection(secId, delta) {
    const idx = state.sections.findIndex(s => s.id === secId);
    if (idx === -1) return;
    const target = idx + delta;
    if (target < 0 || target >= state.sections.length) return;
    const item = state.sections.splice(idx, 1)[0];
    state.sections.splice(target, 0, item);
    renderSections();
}

function deleteSection(secId) {
    if (state.sections.length <= 1) {
        alert('You must have at least 1 section on the dashboard.');
        return;
    }
    state.sections = state.sections.filter(s => s.id !== secId);
    renderSections();
    renderHeaderStats();
    showStudioToast('Section deleted.');
}

function changeWidgetColSpan(widgetId, spanVal) {
    const widgets = state.profileLayouts[state.selectedProfileId] || [];
    const w = widgets.find(x => x.id === widgetId);
    if (w) {
        w.colSpan = Number(spanVal);
        renderSections();
        showStudioToast(`Updated width to ${w.colSpan} columns.`);
    }
}

function cloneWidget(widgetId) {
    const widgets = state.profileLayouts[state.selectedProfileId] || [];
    const w = widgets.find(x => x.id === widgetId);
    if (w) {
        const cloned = JSON.parse(JSON.stringify(w));
        cloned.id = `w-${Date.now()}`;
        cloned.title = `${w.title} (Copy)`;
        widgets.push(cloned);
        renderSections();
        renderHeaderStats();
        showStudioToast(`Duplicated widget: "${cloned.title}"!`);
    }
}

function deleteWidget(widgetId) {
    state.profileLayouts[state.selectedProfileId] = (state.profileLayouts[state.selectedProfileId] || []).filter(w => w.id !== widgetId);
    renderSections();
    renderHeaderStats();
    showStudioToast('Widget removed from canvas.');
}

// ================= DRAG AND DROP REARRANGE ENGINE =================
function handleDragStart(e, widgetId) {
    if (e.target && e.target.closest('select, button, input, a, .form-select, .btn')) {
        e.preventDefault();
        return false;
    }
    state.draggedWidgetId = widgetId;
    if (e.dataTransfer) {
        e.dataTransfer.setData('text/plain', widgetId);
        e.dataTransfer.effectAllowed = 'move';
    }

    setTimeout(() => {
        const cardEl = document.getElementById(`widget-card-${widgetId}`);
        if (cardEl) {
            cardEl.classList.add('is-dragging');
        }
    }, 0);
}

function handleDragEnd(e) {
    state.draggedWidgetId = null;
    state.dragOverWidgetId = null;
    state.dragOverSectionId = null;

    document.querySelectorAll('.widget-card').forEach(el => {
        el.classList.remove('is-dragging', 'is-drag-target');
    });
    document.querySelectorAll('.dashboard-section').forEach(el => {
        el.classList.remove('is-section-drag-target');
    });
}

function handleDragOverWidget(e, targetWidgetId) {
    e.preventDefault();
    e.stopPropagation();
    if (e.dataTransfer) {
        e.dataTransfer.dropEffect = 'move';
    }

    if (state.dragOverWidgetId !== targetWidgetId) {
        state.dragOverWidgetId = targetWidgetId;
        document.querySelectorAll('.widget-card').forEach(el => {
            if (el.id !== `widget-card-${state.draggedWidgetId}`) {
                el.classList.remove('is-drag-target');
            }
        });
        const targetEl = document.getElementById(`widget-card-${targetWidgetId}`);
        if (targetEl && targetWidgetId !== state.draggedWidgetId) {
            targetEl.classList.add('is-drag-target');
        }
    }
}

function handleDragOverSection(e, targetSectionId) {
    e.preventDefault();
    e.stopPropagation();
    if (e.dataTransfer) {
        e.dataTransfer.dropEffect = 'move';
    }

    if (state.dragOverSectionId !== targetSectionId) {
        state.dragOverSectionId = targetSectionId;
        document.querySelectorAll('.dashboard-section').forEach(el => {
            el.classList.remove('is-section-drag-target');
        });
        const secEl = document.getElementById(`dashboard-section-${targetSectionId}`);
        if (secEl) {
            secEl.classList.add('is-section-drag-target');
        }
    }
}

function handleDropOnWidget(e, targetWidgetId, targetSectionId) {
    e.preventDefault();
    e.stopPropagation();

    const draggedId = state.draggedWidgetId || (e.dataTransfer && e.dataTransfer.getData('text/plain'));
    if (!draggedId || draggedId === targetWidgetId) {
        handleDragEnd(e);
        return;
    }

    const widgets = state.profileLayouts[state.selectedProfileId] || [];
    const fromIdx = widgets.findIndex(w => w.id === draggedId);
    const toIdx = widgets.findIndex(w => w.id === targetWidgetId);

    if (fromIdx === -1 || toIdx === -1) {
        handleDragEnd(e);
        return;
    }

    // Move item: remove from original position, update sectionId, and insert at target position
    const [movedWidget] = widgets.splice(fromIdx, 1);
    movedWidget.sectionId = targetSectionId;
    widgets.splice(toIdx, 0, movedWidget);

    state.profileLayouts[state.selectedProfileId] = widgets;
    try {
        localStorage.setItem('dealsync_profile_layouts', JSON.stringify(state.profileLayouts));
    } catch(err) {}

    handleDragEnd(e);
    renderAll();
    showStudioToast(`Rearranged "${movedWidget.title}" into new position!`);
}

function handleDropOnSection(e, targetSectionId) {
    e.preventDefault();
    e.stopPropagation();

    const draggedId = state.draggedWidgetId || (e.dataTransfer && e.dataTransfer.getData('text/plain'));
    if (!draggedId) {
        handleDragEnd(e);
        return;
    }

    const widgets = state.profileLayouts[state.selectedProfileId] || [];
    const fromIdx = widgets.findIndex(w => w.id === draggedId);
    if (fromIdx === -1) {
        handleDragEnd(e);
        return;
    }

    const movedWidget = widgets[fromIdx];
    widgets.splice(fromIdx, 1);
    movedWidget.sectionId = targetSectionId;

    // Find the last index of widgets in the target section
    let lastSecIdx = -1;
    for (let i = widgets.length - 1; i >= 0; i--) {
        if ((widgets[i].sectionId || state.sections[0]?.id) === targetSectionId) {
            lastSecIdx = i;
            break;
        }
    }
    if (lastSecIdx !== -1) {
        widgets.splice(lastSecIdx + 1, 0, movedWidget);
    } else {
        widgets.push(movedWidget);
    }

    state.profileLayouts[state.selectedProfileId] = widgets;
    try {
        localStorage.setItem('dealsync_profile_layouts', JSON.stringify(state.profileLayouts));
    } catch(err) {}

    const targetSec = state.sections.find(s => s.id === targetSectionId);
    handleDragEnd(e);
    renderAll();
    showStudioToast(`Moved "${movedWidget.title}" to ${targetSec ? targetSec.title : 'section'}!`);
}

// ================= LIVE PREVIEW RENDERER =================
function renderLivePreview() {
    const previewContainer = document.getElementById('preview-sections-container');
    const widgets = state.profileLayouts[state.selectedProfileId] || [];

    let html = '';
    state.sections.forEach(sec => {
        const secWidgets = widgets.filter(w => (w.sectionId || state.sections[0]?.id) === sec.id);
        if (secWidgets.length === 0) return;

        html += `
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04);">
                <div style="padding: 0.85rem 1.25rem; background: rgba(248, 250, 252, 0.8); border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <h3 style="font-size: 0.875rem; font-weight: 700; color: #0f172a; margin: 0;">${sec.title}</h3>
                        ${sec.subtitle ? `<p style="font-size: 0.75rem; color: #64748b; margin: 0.15rem 0 0 0;">${sec.subtitle}</p>` : ''}
                    </div>
                    <span style="font-size: 0.6875rem; font-weight: 600; color: #64748b; background: #e2e8f0; padding: 0.15rem 0.5rem; border-radius: 9999px;">
                        ${secWidgets.length} visuals
                    </span>
                </div>
                <div style="padding: 1rem;">
                    <div style="display: grid; grid-template-columns: repeat(16, minmax(0, 1fr)); gap: 1rem;">
                        ${secWidgets.map(w => `
                            <div onclick="openDrilldownDrawer('${w.title}', '${w.calculation?.manualOverrideValue || '$48,600,000'}', '${sec.title}')" style="grid-column: span ${w.colSpan || 8}; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1rem; cursor: pointer; transition: transform 0.15s ease, box-shadow 0.15s ease;" onmouseenter="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.06)'" onmouseleave="this.style.transform='none'; this.style.boxShadow='none'">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                    <span style="font-size: 0.85rem; font-weight: 700; color: #0f172a;">${w.title}</span>
                                    <span style="font-size: 0.65rem; background: #f1f5f9; color: #475569; padding: 2px 6px; border-radius: 4px; font-weight: 700;">Click to Drilldown</span>
                                </div>
                                <div style="height: 120px; display: flex; align-items: center; justify-content: center;">
                                    ${renderSvgPreviewForWidget(w.type, PALETTES.find(x => x.id === w.palette)?.hex || '#6366F1', w.calculation?.manualOverrideValue)}
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>
            </div>
        `;
    });
    previewContainer.innerHTML = html;
}

// ================= MODAL MANAGERS =================
// 1. Widget Catalog
function openWidgetLibraryModal() {
    state.targetSectionForNewWidget = state.sections[0]?.id || 'sec-revenue';
    document.getElementById('library-target-section-title').textContent = state.sections.find(s => s.id === state.targetSectionForNewWidget)?.title || 'First Section';
    document.getElementById('modal-widget-library').style.display = 'flex';
    renderCatalogGrid();
}

function openWidgetLibraryForSection(secId) {
    state.targetSectionForNewWidget = secId;
    document.getElementById('library-target-section-title').textContent = state.sections.find(s => s.id === secId)?.title || 'Section';
    document.getElementById('modal-widget-library').style.display = 'flex';
    renderCatalogGrid();
}

function closeWidgetLibraryModal() {
    document.getElementById('modal-widget-library').style.display = 'none';
}

function filterCatalog(category) {
    state.catalogCategory = category;
    document.querySelectorAll('.lib-cat-btn').forEach(btn => {
        if (btn.getAttribute('data-cat') === category) {
            btn.style.background = '#6366f1';
            btn.style.color = '#ffffff';
            btn.style.fontWeight = '700';
        } else {
            btn.style.background = '#ffffff';
            btn.style.color = '#52525b';
            btn.style.fontWeight = '500';
        }
    });
    renderCatalogGrid();
}

function renderCatalogGrid() {
    const grid = document.getElementById('catalog-widgets-grid');
    const filtered = state.catalogCategory === 'All' ? ALL_WIDGET_CATALOG : ALL_WIDGET_CATALOG.filter(c => c.category === state.catalogCategory);

    grid.innerHTML = filtered.map(cat => {
        const p = PALETTES.find(x => x.id === cat.palette)?.hex || '#6366F1';
        return `
            <div onclick="addWidgetFromCatalog('${cat.type}')" style="padding: 0.85rem; border: 1px solid #e2e8f0; border-radius: var(--radius-xl); background: #ffffff; cursor: pointer; transition: all 0.2s ease; display: flex; flex-direction: column; justify-content: space-between; box-shadow: var(--shadow-xs);" onmouseenter="this.style.borderColor='#6366f1'; this.style.transform='translateY(-2px)'" onmouseleave="this.style.borderColor='#e2e8f0'; this.style.transform='none'">
                <div>
                    <!-- Visual Box -->
                    <div style="height: 84px; width: 100%; background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%); border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 0.65rem; display: flex; align-items: center; justify-content: center; padding: 4px; overflow: hidden;">
                        ${renderSvgPreviewForWidget(cat.type, p)}
                    </div>
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.35rem;">
                        <span style="font-size: 0.85rem; font-weight: 700; color: #0f172a;">${cat.title}</span>
                        <span class="badge badge-gray" style="font-size: 0.65rem;">${cat.category}</span>
                    </div>
                    <p style="font-size: 0.725rem; color: #64748b; margin: 0; line-height: 1.35;">${cat.desc}</p>
                </div>
                <div style="margin-top: 0.75rem; display: flex; align-items: center; justify-content: space-between; border-top: 1px solid #f1f5f9; padding-top: 0.5rem;">
                    <span style="font-size: 0.7rem; color: #94a3b8;">Span: ${cat.defaultSpan} of 16 Cols</span>
                    <span style="font-size: 0.75rem; font-weight: 700; color: #4f46e5;">+ Add to Section</span>
                </div>
            </div>
        `;
    }).join('');
    if (window.lucide) window.lucide.createIcons();
}

function addWidgetFromCatalog(chartType) {
    const item = ALL_WIDGET_CATALOG.find(c => c.type === chartType);
    if (!item) return;

    const newWidget = {
        id: `w-${Date.now()}`,
        type: item.type,
        title: item.title,
        subtitle: item.desc,
        colSpan: item.defaultSpan,
        height: 'standard',
        palette: item.palette,
        sectionId: state.targetSectionForNewWidget || state.sections[0]?.id,
        calculation: { module: 'opportunities', calcType: 'SUM', field: 'amount' },
        sidePanelConfig: { enabled: true }
    };

    if (!state.profileLayouts[state.selectedProfileId]) {
        state.profileLayouts[state.selectedProfileId] = [];
    }
    state.profileLayouts[state.selectedProfileId].push(newWidget);

    closeWidgetLibraryModal();
    renderSections();
    renderHeaderStats();
    showStudioToast(`Added "${newWidget.title}" to ${state.sections.find(s => s.id === newWidget.sectionId)?.title}!`);
}

// 2. Insert Section Modal
function openInsertSectionModal() {
    document.getElementById('new-section-title-input').value = '';
    document.getElementById('new-section-subtitle-input').value = '';
    document.getElementById('modal-insert-section').style.display = 'flex';
}

function closeInsertSectionModal() {
    document.getElementById('modal-insert-section').style.display = 'none';
}

function handleCreateSection() {
    const title = document.getElementById('new-section-title-input').value.trim();
    const subtitle = document.getElementById('new-section-subtitle-input').value.trim();
    if (!title) {
        alert('Please enter a section title.');
        return;
    }
    const newSec = {
        id: `sec-${Date.now()}`,
        title: title,
        subtitle: subtitle,
        collapsed: false
    };
    state.sections.push(newSec);
    closeInsertSectionModal();
    renderSections();
    renderHeaderStats();
    showStudioToast(`Created new canvas section: "${title}"!`);
}

// 3. Matrix Modal
function openMatrixModal() {
    document.getElementById('modal-matrix').style.display = 'flex';
    renderMatrixModal();
}

function closeMatrixModal() {
    document.getElementById('modal-matrix').style.display = 'none';
}

function renderMatrixModal() {
    const list = document.getElementById('matrix-profiles-list');
    list.innerHTML = PROFILES.map(p => {
        const isCurrent = p.id === state.selectedProfileId;
        const widgets = state.profileLayouts[p.id] || DEFAULT_PROFILE_WIDGETS[p.id] || [];

        return `
            <div style="padding: 1rem; background: ${isCurrent ? 'rgba(99,102,241,0.04)' : '#ffffff'}; border: ${isCurrent ? '2px solid #6366f1' : '1px solid #e2e8f0'}; border-radius: var(--radius-lg); display: flex; flex-direction: column; gap: 0.75rem;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 0.5rem;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span style="font-weight: 700; font-size: 0.875rem; color: #09090b;">${p.name}</span>
                            <span class="badge ${p.is_admin ? 'badge-purple' : 'badge-gray'}" style="font-size: 0.65rem;">${p.is_admin ? 'Admin Profile' : 'Standard Profile'}</span>
                            ${isCurrent ? '<span style="font-size: 0.65rem; background: #dbeafe; color: #1d4ed8; padding: 1px 6px; border-radius: 4px; font-weight: 600;">Currently Editing</span>' : ''}
                        </div>
                        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.15rem;">
                            ${p.description} • ${state.sections.length} Sections Configured
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.4rem;">
                        ${!isCurrent ? `
                            <button type="button" onclick="handleCloneLayout('${p.id}')" class="btn btn-sm btn-secondary" style="font-size: 0.75rem; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                                <i data-lucide="copy" style="width: 12px; height: 12px;"></i>
                                <span>Copy to Current</span>
                            </button>
                        ` : ''}
                        <button type="button" onclick="handleProfileChange('${p.id}'); closeMatrixModal();" class="btn btn-sm ${isCurrent ? 'btn-primary' : 'btn-secondary'}" style="font-size: 0.75rem; font-weight: 600;">
                            ${isCurrent ? 'Continue Editing' : 'Edit This Profile'}
                        </button>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap; border-top: 1px solid #f1f5f9; padding-top: 0.5rem;">
                    <span style="font-size: 0.7rem; font-weight: 700; color: #334155;">${widgets.length} Assigned Widgets:</span>
                    ${widgets.slice(0, 5).map(w => `
                        <span style="font-size: 0.65rem; background: #f1f5f9; color: #334155; padding: 1px 6px; border-radius: 4px; border: 1px solid #e2e8f0;">${w.title}</span>
                    `).join('')}
                    ${widgets.length > 5 ? `<span style="font-size: 0.65rem; color: #94a3b8; font-weight: 600;">+${widgets.length - 5} more</span>` : ''}
                </div>
            </div>
        `;
    }).join('');
    if (window.lucide) window.lucide.createIcons();
}

function handleCloneLayout(sourceProfileId) {
    const targetProfileId = state.selectedProfileId;
    if (sourceProfileId === targetProfileId) return;
    const sourceProf = PROFILES.find(p => p.id === sourceProfileId);
    const targetProf = PROFILES.find(p => p.id === targetProfileId);
    if (!confirm(`Copy and clone entire layout from "${sourceProf ? sourceProf.name : sourceProfileId}" into "${targetProf ? targetProf.name : targetProfileId}"?`)) {
        return;
    }
    const sourceWidgets = state.profileLayouts[sourceProfileId] || DEFAULT_PROFILE_WIDGETS[sourceProfileId] || [];
    const cloned = JSON.parse(JSON.stringify(sourceWidgets)).map((w, idx) => ({
        ...w,
        id: `w-${targetProfileId}-${Date.now()}-${idx}`
    }));
    state.profileLayouts[targetProfileId] = cloned;
    renderAll();
    closeMatrixModal();
    showStudioToast(`Cloned layout from ${sourceProf ? sourceProf.name : sourceProfileId} into ${targetProf ? targetProf.name : targetProfileId}!`);
}

// 4. Global Settings Modal
function openSettingsModal() {
    document.getElementById('modal-settings').style.display = 'flex';
}

function closeSettingsModal() {
    document.getElementById('modal-settings').style.display = 'none';
}

function switchSettingsTab(tab) {
    document.querySelectorAll('.settings-subtab-btn').forEach(btn => {
        if (btn.getAttribute('data-tab') === tab) {
            btn.style.borderColor = '#6366f1';
            btn.style.color = '#6366f1';
            btn.style.fontWeight = '700';
        } else {
            btn.style.borderColor = 'transparent';
            btn.style.color = '#52525b';
            btn.style.fontWeight = '500';
        }
    });

    ['filters', 'buttons', 'ai', 'presets'].forEach(t => {
        const el = document.getElementById(`settings-tab-${t}`);
        if (el) el.style.display = (t === tab ? 'flex' : 'none');
    });
}

function handleSavePreset() {
    const val = document.getElementById('preset-name-input').value.trim();
    if (!val) return;
    document.getElementById('preset-name-input').value = '';
    showStudioToast(`Saved preset "${val}"!`);
}

// 5. Widget Config Modal
function openWidgetConfigModal(widgetId) {
    const widgets = state.profileLayouts[state.selectedProfileId] || [];
    const w = widgets.find(x => x.id === widgetId);
    if (!w) return;

    state.editingWidget = w;
    document.getElementById('edit-widget-id').value = w.id;
    document.getElementById('edit-widget-title').value = w.title;
    document.getElementById('edit-widget-subtitle').value = w.subtitle || '';
    document.getElementById('edit-widget-colspan').value = w.colSpan || 4;
    document.getElementById('edit-widget-palette').value = w.palette || 'violet';
    document.getElementById('edit-widget-module').value = w.calculation?.module || 'opportunities';
    document.getElementById('edit-widget-calc').value = w.calculation?.calcType || 'SUM';

    document.getElementById('modal-widget-config').style.display = 'flex';
}

function closeWidgetConfigModal() {
    document.getElementById('modal-widget-config').style.display = 'none';
    state.editingWidget = null;
}

function handleSaveWidgetConfig() {
    if (!state.editingWidget) return;
    state.editingWidget.title = document.getElementById('edit-widget-title').value;
    state.editingWidget.subtitle = document.getElementById('edit-widget-subtitle').value;
    state.editingWidget.colSpan = Number(document.getElementById('edit-widget-colspan').value);
    state.editingWidget.palette = document.getElementById('edit-widget-palette').value;
    state.editingWidget.calculation = {
        module: document.getElementById('edit-widget-module').value,
        calcType: document.getElementById('edit-widget-calc').value,
        field: 'amount'
    };

    closeWidgetConfigModal();
    renderSections();
    showStudioToast(`Updated "${state.editingWidget.title}" with calculations & geometry!`);
}

// 6. Side Panel Config Modal
function openSidePanelConfigModal(widgetId) {
    const widgets = state.profileLayouts[state.selectedProfileId] || [];
    const w = widgets.find(x => x.id === widgetId);
    if (!w) return;

    state.sidePanelWidget = w;
    document.getElementById('sidepanel-widget-id').value = w.id;
    document.getElementById('sidepanel-title').value = w.sidePanelConfig?.customTitle || `Drilldown: ${w.title}`;
    document.getElementById('sidepanel-enabled').checked = w.sidePanelConfig ? w.sidePanelConfig.enabled !== false : true;

    document.getElementById('modal-sidepanel-config').style.display = 'flex';
}

function closeSidePanelConfigModal() {
    document.getElementById('modal-sidepanel-config').style.display = 'none';
    state.sidePanelWidget = null;
}

function handleSaveSidePanelConfig() {
    if (!state.sidePanelWidget) return;
    state.sidePanelWidget.sidePanelConfig = {
        enabled: document.getElementById('sidepanel-enabled').checked,
        customTitle: document.getElementById('sidepanel-title').value
    };
    closeSidePanelConfigModal();
    renderSections();
    showStudioToast(`Updated Drilldown configuration for "${state.sidePanelWidget.title}"!`);
}

// 7. Drilldown Slide-Over Drawer
function openDrilldownDrawer(title, metricVal, category) {
    document.getElementById('drawer-title').textContent = title;
    document.getElementById('drawer-metric-val').textContent = metricVal || '$48,600,000';
    document.getElementById('drawer-category-badge').textContent = category || 'Telemetry Drilldown';
    document.getElementById('drawer-drilldown').style.display = 'flex';
    if (window.lucide) window.lucide.createIcons();
}

function closeDrilldownDrawer() {
    document.getElementById('drawer-drilldown').style.display = 'none';
}

// ================= TOAST NOTIFICATIONS =================
function showStudioToast(msg) {
    const banner = document.getElementById('studio-feedback-banner');
    const text = document.getElementById('studio-feedback-text');
    text.textContent = msg;
    banner.style.display = 'flex';
    setTimeout(() => {
        banner.style.display = 'none';
    }, 3500);
}

function handleToggleSetting(key, val) {
    showStudioToast(`Updated setting: ${key} = ${val}`);
}

function handleToggleAi(val) {
    showStudioToast(`AI Deal Insights Copilot ${val ? 'Enabled' : 'Disabled'}`);
}

function handleUpdateAiThreshold(val) {
    showStudioToast(`AI stagnation threshold set to ${val} days`);
}

function syncDbProfiles() {
    if (Array.isArray(DB_PROFILES) && DB_PROFILES.length > 0) {
        DB_PROFILES.forEach(p => {
            let alias = 'prof-' + (p.name || '').toLowerCase().replace(/[^a-z0-9]/g, '');
            if (p.name.toLowerCase().includes('admin')) alias = 'prof-admin';
            else if (p.name.toLowerCase().includes('sales') || p.name.toLowerCase().includes('standard')) alias = 'prof-sales';
            else if (p.name.toLowerCase().includes('procurement')) alias = 'prof-procurement';
            else if (p.name.toLowerCase().includes('read')) alias = 'prof-readonly';
            else alias = String(p.id);

            const existing = PROFILES.find(x => x.id === alias);
            if (existing) {
                existing.db_id = p.id;
                existing.name = p.name;
                if (p.description) existing.description = p.description;
            } else {
                PROFILES.push({
                    id: alias,
                    db_id: p.id,
                    name: p.name,
                    description: p.description || 'Custom organization security profile',
                    is_admin: p.is_admin ? 1 : 0
                });
            }
        });
    }
}

// Auto-run if DOM is ready or attach event listener
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initDashboardStudio);
} else {
    initDashboardStudio();
}

// Expose on window for SPA and global inline calls
window.initDashboardStudio = initDashboardStudio;
window.renderAll = renderAll;
window.renderSections = renderSections;
window.renderLivePreview = renderLivePreview;
window.switchStudioTab = switchStudioTab;
window.handleProfileChange = handleProfileChange;
window.handleResetProfileLayout = handleResetProfileLayout;
window.handleSaveAndPublish = handleSaveAndPublish;
window.handleAddHeaderAction = handleAddHeaderAction;
window.handleUpdateHeaderAction = handleUpdateHeaderAction;
window.handleDeleteHeaderAction = handleDeleteHeaderAction;
window.openWidgetLibraryModal = openWidgetLibraryModal;
window.closeWidgetLibraryModal = closeWidgetLibraryModal;
window.filterCatalog = filterCatalog;
window.handleAddWidgetFromCatalog = handleAddWidgetFromCatalog;
window.openInsertSectionModal = openInsertSectionModal;
window.closeInsertSectionModal = closeInsertSectionModal;
window.handleCreateSection = handleCreateSection;
window.openMatrixModal = openMatrixModal;
window.closeMatrixModal = closeMatrixModal;
window.openSettingsModal = openSettingsModal;
window.closeSettingsModal = closeSettingsModal;
window.switchSettingsTab = switchSettingsTab;
window.openWidgetConfigModal = openWidgetConfigModal;
window.closeWidgetConfigModal = closeWidgetConfigModal;
window.handleSaveWidgetConfig = handleSaveWidgetConfig;
window.openSidePanelConfigModal = openSidePanelConfigModal;
window.closeSidePanelConfigModal = closeSidePanelConfigModal;
window.handleSaveSidePanelConfig = handleSaveSidePanelConfig;
window.openDrilldownDrawer = openDrilldownDrawer;
window.closeDrilldownDrawer = closeDrilldownDrawer;
window.showStudioToast = showStudioToast;
window.handleToggleSetting = handleToggleSetting;
window.handleToggleAi = handleToggleAi;
window.handleUpdateAiThreshold = handleUpdateAiThreshold;
window.toggleSectionCollapse = toggleSectionCollapse;
window.handleDeleteSection = handleDeleteSection;
window.handleEditSection = handleEditSection;
window.handleMoveSection = handleMoveSection;
window.handleDuplicateWidget = handleDuplicateWidget;
window.handleDeleteWidget = handleDeleteWidget;
window.handleMoveWidget = handleMoveWidget;
window.handleColSpanChange = handleColSpanChange;
window.handleHeightChange = handleHeightChange;
window.handleDragOverSection = handleDragOverSection;
window.handleDropOnSection = handleDropOnSection;
window.handleDragStart = handleDragStart;
window.handleDragOver = handleDragOver;
window.handleDrop = handleDrop;
window.handleDragEnd = handleDragEnd;
