@extends('layouts.app')

@section('title', 'Object & Field Manager Studio — DealSync Admin')

@section('content')
<style>
/* ========================================================================= */
/* OBJECT & FIELD MANAGER STUDIO DEDICATED STYLES                            */
/* ========================================================================= */
.of-studio-container {
    --text-primary: var(--zinc-900, #0f172a);
    --text-secondary: var(--zinc-600, #475569);
    --text-tertiary: var(--zinc-500, #64748b);
    --text-muted: var(--zinc-400, #94a3b8);
    max-width: 1200px;
    margin: 0 auto;
    padding-bottom: 3rem;
    color: var(--zinc-900, #0f172a);
}

.of-studio-container * {
    box-sizing: border-box;
}

.form-input, .form-control {
    width: 100%;
    box-sizing: border-box;
    height: 38px;
    background-color: var(--zinc-50, #f8fafc);
    border: 1px solid var(--zinc-200, #e2e8f0);
    border-radius: var(--radius-md, 8px);
    color: var(--zinc-950, #09090b);
    padding: 0.45rem 0.85rem;
    font-size: 0.8125rem;
    font-family: inherit;
    line-height: 1.4;
    outline: none;
    transition: border-color 0.15s ease, background-color 0.15s ease, box-shadow 0.15s ease;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.02);
}

.form-input::placeholder, .form-control::placeholder {
    color: var(--zinc-400, #94a3b8);
    font-size: 0.8125rem;
}

.form-input:hover, .form-control:hover {
    border-color: var(--zinc-300, #cbd5e1);
    background-color: #ffffff;
}

.form-input:focus, .form-control:focus,
.form-input:focus-visible, .form-control:focus-visible {
    background-color: #ffffff !important;
    border-color: var(--color-primary, #4f46e5) !important;
    box-shadow: 0 0 0 3px var(--color-primary-subtle, rgba(79, 70, 229, 0.15)) !important;
    color: var(--zinc-950, #09090b) !important;
}

.form-select {
    width: 100%;
    box-sizing: border-box;
    height: 38px;
    background-color: var(--zinc-50, #f8fafc);
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 0.75rem center;
    background-repeat: no-repeat;
    background-size: 1.15em 1.15em;
    padding: 0.45rem 2.25rem 0.45rem 0.85rem;
    border: 1px solid var(--zinc-200, #e2e8f0);
    border-radius: var(--radius-md, 8px);
    color: var(--zinc-950, #09090b);
    font-size: 0.8125rem;
    font-family: inherit;
    line-height: 1.4;
    outline: none;
    cursor: pointer;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    transition: border-color 0.15s ease, background-color 0.15s ease, box-shadow 0.15s ease;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.02);
}

.form-select:hover {
    border-color: var(--zinc-300, #cbd5e1);
    background-color: #ffffff;
}

.form-select:focus,
.form-select:focus-visible {
    background-color: #ffffff !important;
    border-color: var(--color-primary, #4f46e5) !important;
    box-shadow: 0 0 0 3px var(--color-primary-subtle, rgba(79, 70, 229, 0.15)) !important;
    color: var(--zinc-950, #09090b) !important;
}

.form-select option {
    background: #ffffff;
    color: var(--zinc-900, #09090b);
    padding: 0.5rem 0.75rem;
    font-size: 0.8125rem;
}

.form-label {
    display: block;
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--zinc-800, #1e293b);
    margin-bottom: 0.35rem;
}

.of-subtab-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.45rem 0.95rem;
    border-radius: var(--radius-md, 8px);
    font-size: 0.78125rem;
    font-weight: 600;
    border: none;
    cursor: pointer;
    background: transparent;
    color: var(--zinc-600, #475569);
    transition: all 0.15s ease;
}

.of-subtab-btn:hover {
    background: var(--zinc-100, #f1f5f9);
    color: var(--zinc-950, #09090b);
}

.of-subtab-btn.is-active {
    background: var(--color-primary, #4f46e5);
    color: #ffffff;
}

.data-table th {
    background: var(--zinc-100, #f1f5f9);
    color: var(--zinc-700, #334155);
    font-weight: 600;
    font-size: 0.75rem;
    letter-spacing: 0.02em;
    padding: 0.65rem 0.85rem;
    border-bottom: 1px solid var(--zinc-200, #e2e8f0);
    white-space: nowrap;
}

.data-table td {
    padding: 0.75rem 0.85rem;
    border-bottom: 1px solid var(--zinc-200, #e2e8f0);
    font-size: 0.8125rem;
    color: var(--zinc-900, #0f172a);
    vertical-align: middle;
}

.data-table tbody tr:hover td {
    background-color: var(--zinc-50, #f8fafc);
}

.data-table tbody tr:last-child td {
    border-bottom: none;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(4px); }
    to { opacity: 1; transform: translateY(0); }
}

.animate-fade-in {
    animation: fadeIn 0.15s ease-out forwards;
}
</style>

@include('partials.admin_nav')

<div class="of-studio-container animate-fade-in">
    <!-- Floating Toast Feedback Banner -->
    <div id="ofToast" style="position: fixed; bottom: 24px; right: 24px; background: #09090b; color: #ffffff; padding: 0.75rem 1.25rem; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.3); display: none; align-items: center; gap: 0.6rem; z-index: 999999; font-size: 0.85rem; font-weight: 500;">
        <i id="ofToastIcon" data-lucide="check-circle-2" style="width: 16px; height: 16px; color: #10b981; flex-shrink: 0;"></i>
        <span id="ofToastMsg"></span>
    </div>

    <!-- Top Action Header -->
    <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                <span class="badge badge-indigo" style="font-size: 0.7rem; font-weight: 700; padding: 0.15rem 0.5rem;">Dynamic Schema Architecture</span>
                <span style="font-size: 0.725rem; color: var(--zinc-500, #64748b);">Custom Attributes, Rollup Summaries, Formulas &amp; Global Picklists</span>
            </div>
            <h1 style="font-size: 1.35rem; font-weight: 800; color: var(--zinc-950, #09090b); letter-spacing: -0.02em; margin: 0; line-height: 1.2;">
                Object &amp; Field Manager Studio
            </h1>
            <p style="font-size: 0.8125rem; color: var(--zinc-500, #64748b); margin: 0.25rem 0 0 0;">
                Extend standard CRM objects with 22 dynamic data types including formulas, rollup aggregations, masked PII, ratings, and multi-select picklists.
            </p>
        </div>

        <div style="display: flex; align-items: center; gap: 0.6rem;">
            <button
                type="button"
                onclick="setSubTab('global_picklists')"
                class="btn btn-secondary btn-sm"
                style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.5rem 0.85rem; font-weight: 600;"
            >
                <i data-lucide="list-plus" style="width: 14px; height: 14px;"></i>
                <span>Global Picklists</span>
            </button>
            <button
                type="button"
                onclick="openCreateFieldModal()"
                class="btn btn-primary"
                style="display: inline-flex; align-items: center; gap: 0.45rem; padding: 0.55rem 1.15rem; font-weight: 600;"
            >
                <i data-lucide="plus" style="width: 15px; height: 15px;"></i>
                <span>+ Create Custom Field</span>
            </button>
        </div>
    </div>

    <!-- KPI Summary Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.25rem;">
        <div class="card" style="padding: 1rem; background: var(--bg-card); border: 1px solid var(--border-card); border-radius: var(--radius-lg, 12px);">
            <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500, #64748b); margin-bottom: 0.25rem; text-transform: uppercase; letter-spacing: 0.03em;">STANDARD OBJECTS</div>
            <div style="font-size: 1.625rem; font-weight: 800; color: var(--zinc-950, #09090b); letter-spacing: -0.03em;">10 Entities</div>
            <span style="font-size: 0.7rem; color: var(--zinc-500, #64748b); font-weight: 500;">94 Standard Columns</span>
        </div>

        <div class="card" style="padding: 1rem; background: var(--bg-card); border: 1px solid var(--border-card); border-radius: var(--radius-lg, 12px);">
            <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500, #64748b); margin-bottom: 0.25rem; text-transform: uppercase; letter-spacing: 0.03em;">ACTIVE CUSTOM FIELDS</div>
            <div id="kpiActiveCustomFields" style="font-size: 1.625rem; font-weight: 800; color: var(--color-primary, #4f46e5); letter-spacing: -0.03em;">10 Fields</div>
            <span style="font-size: 0.7rem; color: #059669; font-weight: 600;">Zero Migration Overhead</span>
        </div>

        <div class="card" style="padding: 1rem; background: var(--bg-card); border: 1px solid var(--border-card); border-radius: var(--radius-lg, 12px);">
            <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500, #64748b); margin-bottom: 0.25rem; text-transform: uppercase; letter-spacing: 0.03em;">FORMULA &amp; ROLLUP ENGINES</div>
            <div id="kpiFormulasRollups" style="font-size: 1.625rem; font-weight: 800; color: #7c3aed; letter-spacing: -0.03em;">4 Active</div>
            <span style="font-size: 0.7rem; color: var(--zinc-500, #64748b); font-weight: 500;">Real-Time Recalculation</span>
        </div>

        <div class="card" style="padding: 1rem; background: var(--bg-card); border: 1px solid var(--border-card); border-radius: var(--radius-lg, 12px);">
            <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500, #64748b); margin-bottom: 0.25rem; text-transform: uppercase; letter-spacing: 0.03em;">GLOBAL PICKLISTS</div>
            <div id="kpiGlobalPicklists" style="font-size: 1.625rem; font-weight: 800; color: #0284c7; letter-spacing: -0.03em;">4 Sets</div>
            <span style="font-size: 0.7rem; color: var(--zinc-500, #64748b); font-weight: 500;">Shared Value Sets</span>
        </div>
    </div>

    <!-- Sub-Navigation for Object Schema vs Global Picklist Sets (SettingsView.tsx lines 2896-2916) -->
    <div style="display: flex; gap: 0.5rem; margin-bottom: 1.25rem; border-bottom: 1px solid var(--border-card); padding-bottom: 0.65rem;">
        <button
            type="button"
            id="subTabBtnFields"
            onclick="setSubTab('fields')"
            class="of-subtab-btn is-active"
        >
            <i data-lucide="database" style="width: 14px; height: 14px;"></i>
            <span>Object Fields &amp; Schema</span>
        </button>
        <button
            type="button"
            id="subTabBtnGlobalPicklists"
            onclick="setSubTab('global_picklists')"
            class="of-subtab-btn"
        >
            <i data-lucide="list-plus" style="width: 14px; height: 14px;"></i>
            <span>Global Picklist Value Sets (<span id="subTabGpCount">4</span>)</span>
        </button>
    </div>

    <!-- ===================================================================== -->
    <!-- VIEW 1: OBJECT FIELDS & SCHEMA                                        -->
    <!-- ===================================================================== -->
    <div id="viewFieldsSchema">
        <!-- Object Selector & Actions Bar (SettingsView.tsx lines 2920-2967) -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; background: #ffffff; padding: 1rem 1.25rem; border-radius: var(--radius-xl, 14px); border: 1px solid var(--border-card); box-shadow: var(--shadow-card); flex-wrap: wrap; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 0.85rem;">
                <div style="width: 36px; height: 36px; border-radius: 8px; background: var(--zinc-100, #f1f5f9); display: flex; align-items: center; justify-content: center; color: var(--zinc-800, #1e293b);">
                    <i data-lucide="database" style="width: 18px; height: 18px;"></i>
                </div>
                <div>
                    <div style="font-size: 0.6875rem; font-weight: 700; color: var(--zinc-500, #64748b); text-transform: uppercase; letter-spacing: 0.04em;">
                        Target Object / Entity Schema
                    </div>
                    <select
                        id="selectedModuleForFields"
                        class="form-select"
                        onchange="handleModuleChange(this.value)"
                        style="width: 290px; font-weight: 600; color: var(--zinc-950); margin-top: 0.25rem; font-size: 0.8125rem;"
                    >
                        <!-- Populated dynamically via renderModuleDropdown() -->
                    </select>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 0.65rem;">
                <a
                    href="{{ route('admin.custom_objects') }}"
                    class="btn btn-secondary btn-sm"
                    title="Manage Custom Entities"
                    style="display: inline-flex; align-items: center; gap: 0.35rem; font-weight: 600; padding: 0.45rem 0.75rem;"
                >
                    <i data-lucide="box" style="width: 14px; height: 14px;"></i>
                    <span>Custom Objects Studio</span>
                </a>
                <button
                    type="button"
                    onclick="openCreateFieldModal()"
                    class="btn btn-primary btn-sm"
                    style="display: inline-flex; align-items: center; gap: 0.35rem; font-weight: 600; padding: 0.45rem 0.85rem;"
                >
                    <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
                    <span>Create Custom Field</span>
                </button>
            </div>
        </div>

        <!-- Custom Fields Section (SettingsView.tsx lines 2969-3156) -->
        <div style="margin-bottom: 2rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; flex-wrap: wrap; gap: 0.5rem;">
                <h3 id="customFieldsHeaderTitle" style="font-size: 0.95rem; font-weight: 700; color: var(--zinc-950, #09090b); letter-spacing: -0.02em; margin: 0;">
                    Custom Fields on Opportunities (0)
                </h3>
                <span style="font-size: 0.75rem; color: var(--zinc-500, #64748b);">
                    User-defined attributes with dynamic data types
                </span>
            </div>

            <div class="card" style="padding: 0; overflow: hidden; border: 1px solid var(--border-card); border-radius: var(--radius-lg, 12px);">
                <div style="overflow-x: auto;">
                    <table class="data-table" style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr>
                                <th style="min-width: 180px;">Field Label</th>
                                <th style="min-width: 160px;">Database Key</th>
                                <th style="min-width: 160px;">Data Type</th>
                                <th style="min-width: 220px;">Options &amp; Source</th>
                                <th style="min-width: 100px;">Required</th>
                                <th style="min-width: 180px;">Description</th>
                                <th style="text-align: right; min-width: 130px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="customFieldsTableBody">
                            <!-- Populated dynamically via renderCustomFieldsTable() -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Standard System Fields Section (SettingsView.tsx lines 3158-3238) -->
        <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; flex-wrap: wrap; gap: 0.5rem;">
                <h3 id="standardFieldsHeaderTitle" style="font-size: 0.95rem; font-weight: 700; color: var(--zinc-950, #09090b); letter-spacing: -0.02em; margin: 0;">
                    Standard System Fields on Opportunities (Core CRM Schema)
                </h3>
                <span style="font-size: 0.75rem; color: var(--zinc-500, #64748b);">
                    Managed core attributes
                </span>
            </div>

            <div class="card" style="padding: 0; overflow: hidden; border: 1px solid var(--border-card); border-radius: var(--radius-lg, 12px);">
                <div style="overflow-x: auto;">
                    <table class="data-table" style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr>
                                <th style="min-width: 180px;">Field Label</th>
                                <th style="min-width: 160px;">Database Column</th>
                                <th style="min-width: 140px;">Data Type</th>
                                <th style="min-width: 100px;">Required</th>
                                <th style="min-width: 240px;">Description</th>
                                <th style="text-align: right; min-width: 110px;">Status</th>
                            </tr>
                        </thead>
                        <tbody id="standardFieldsTableBody">
                            <!-- Populated dynamically via renderStandardFieldsTable() -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- VIEW 2: GLOBAL PICKLIST VALUE SETS                                    -->
    <!-- ===================================================================== -->
    <div id="viewGlobalPicklists" style="display: none;">
        <!-- Global Picklist Ribbon -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; background: #ffffff; padding: 1rem 1.25rem; border-radius: var(--radius-xl, 14px); border: 1px solid var(--border-card); box-shadow: var(--shadow-card); flex-wrap: wrap; gap: 1rem;">
            <div>
                <div style="font-size: 0.975rem; font-weight: 700; color: var(--zinc-950, #09090b);">
                    Global Picklist Value Sets
                </div>
                <div style="font-size: 0.785rem; color: var(--zinc-500, #64748b); margin-top: 0.15rem;">
                    Centrally managed, reusable value sets shared across custom picklist fields on any CRM object.
                </div>
            </div>

            <button
                type="button"
                onclick="openCreateGlobalPicklistModal()"
                class="btn btn-primary btn-sm"
                style="display: inline-flex; align-items: center; gap: 0.4rem; font-weight: 600; padding: 0.5rem 0.95rem;"
            >
                <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
                <span>New Global Value Set</span>
            </button>
        </div>

        <!-- Global Picklists Table -->
        <div class="card" style="padding: 0; overflow: hidden; border: 1px solid var(--border-card); border-radius: var(--radius-lg, 12px);">
            <div style="overflow-x: auto;">
                <table class="data-table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th style="min-width: 180px;">Value Set Name</th>
                            <th style="min-width: 160px;">API Identifier</th>
                            <th style="min-width: 120px;">Values Count</th>
                            <th style="min-width: 260px;">Values Preview</th>
                            <th style="min-width: 200px;">Description</th>
                            <th style="text-align: right; min-width: 130px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="globalPicklistsTableBody">
                        <!-- Populated dynamically via renderGlobalPicklistsTable() -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 1: CREATE / EDIT CUSTOM FIELD (SettingsView.tsx lines 3488-3780)    -->
<!-- ========================================================================= -->
<div
    id="customFieldModalBackdrop"
    style="position: fixed; inset: 0; z-index: 99999; display: none; align-items: center; justify-content: center; padding: 1.5rem; background: rgba(9, 9, 11, 0.65); backdrop-filter: blur(5px); overflow-y: auto;"
    onclick="handleCloseModal(event, 'customFieldModalBackdrop')"
>
    <div
        id="customFieldModalDialog"
        style="position: relative; width: 720px; maxWidth: 95vw; maxHeight: 90vh; background: #ffffff; border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35); display: flex; flex-direction: column; overflow: hidden; margin: auto;"
        onclick="event.stopPropagation()"
    >
        <!-- Modal Header -->
        <div style="padding: 1.15rem 1.5rem; border-bottom: 1px solid var(--zinc-200, #e2e8f0); background: var(--zinc-50, #f8fafc); display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <i data-lucide="sliders" style="width: 18px; height: 18px; color: var(--color-primary, #4f46e5);"></i>
                <h3 id="customFieldModalTitle" style="font-size: 1rem; font-weight: 700; color: var(--zinc-950, #09090b); margin: 0;">
                    Create Custom Field
                </h3>
            </div>
            <button
                type="button"
                onclick="closeCustomFieldModal()"
                class="btn btn-ghost btn-sm"
                style="width: 30px; height: 30px; border-radius: 50%; padding: 0; display: flex; align-items: center; justify-content: center; color: var(--zinc-400);"
            >
                <i data-lucide="x" style="width: 16px; height: 16px;"></i>
            </button>
        </div>

        <!-- Modal Body Form -->
        <form id="customFieldForm" onsubmit="handleSaveCustomField(event)" style="display: flex; flex-direction: column; flex: 1; min-height: 0; margin: 0;">
            <div style="padding: 1.25rem 1.75rem; overflow-y: auto; flex: 1; min-height: 0; display: flex; flex-direction: column; gap: 1rem;">
                <!-- Target Object Indicator -->
                <div style="background: var(--zinc-50, #f8fafc); padding: 0.75rem 1rem; border-radius: var(--radius-lg, 10px); border: 1px solid var(--zinc-200, #e2e8f0); font-size: 0.8125rem;">
                    Target Object: <strong id="modalTargetObjectDisplay" style="color: var(--zinc-950, #09090b);">Opportunities</strong>
                </div>

                <!-- Field Label (Display Name) -->
                <div>
                    <label class="form-label">Field Label (Display Name) *</label>
                    <input
                        type="text"
                        id="formFieldLabel"
                        class="form-input"
                        placeholder="e.g. Tax ID, Priority Tier, Delivery Window"
                        required
                        oninput="handleFieldLabelInput(this.value)"
                        onblur="handleFieldLabelBlur(this.value)"
                    >
                </div>

                <!-- Database Field Key -->
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                        <label class="form-label" style="margin: 0;">Database Field Key *</label>
                        <button
                            type="button"
                            id="btnResetFieldKey"
                            onclick="handleResetFieldKey()"
                            style="display: none; background: none; border: none; color: var(--color-primary, #4f46e5); font-size: 0.72rem; cursor: pointer; padding: 0; text-decoration: underline;"
                        >
                            Reset from Label
                        </button>
                    </div>
                    <input
                        type="text"
                        id="formFieldName"
                        class="form-input"
                        placeholder="cf_field_identifier"
                        required
                        oninput="handleFieldNameManualInput(this.value)"
                    >
                    <div style="font-size: 0.725rem; color: var(--zinc-500, #64748b); margin-top: 0.2rem;">
                        Custom attributes use prefix <code style="background: var(--zinc-100); padding: 1px 4px; border-radius: 3px;">cf_</code> for system stability.
                    </div>
                </div>

                <!-- Data Type Selector -->
                <div>
                    <label class="form-label">Data Type (Field Type) *</label>
                    <select
                        id="formDataType"
                        class="form-select"
                        onchange="handleDataTypeChange(this.value)"
                    >
                        <!-- Populated dynamically via renderDataTypes() -->
                    </select>
                </div>

                <!-- CONDITIONAL TYPE PANEL: Dropdown & MultiSelect Values -->
                <div id="panelDropdownOptions" style="display: none; background: var(--zinc-50, #f8fafc); padding: 0.85rem; border-radius: var(--radius-lg, 10px); border: 1px solid var(--zinc-200, #e2e8f0); flex-direction: column; gap: 0.75rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 0.75rem; font-weight: 700; color: var(--zinc-800, #1e293b);">Picklist Values Configuration *</span>
                        <label style="display: flex; align-items: center; gap: 0.35rem; cursor: pointer; font-size: 0.75rem; font-weight: 600; color: var(--zinc-800, #1e293b);">
                            <input
                                type="checkbox"
                                id="formIsMultiSelect"
                                style="width: 14px; height: 14px; accent-color: var(--color-primary, #4f46e5);"
                            >
                            <span>Multi-Select (Choose multiple values)</span>
                        </label>
                    </div>

                    <div>
                        <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-600, #475569); margin-bottom: 0.35rem;">
                            Values Source
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                            <button
                                type="button"
                                id="btnPicklistSourceCustom"
                                onclick="setPicklistSource('custom')"
                                style="padding: 0.5rem; border-radius: 8px; border: 2px solid var(--color-primary, #4f46e5); background: #eff6ff; font-weight: 600; color: var(--color-primary, #4f46e5); font-size: 0.75rem; cursor: pointer; text-align: left;"
                            >
                                ✍️ Custom Values
                                <div style="font-size: 0.675rem; font-weight: 400; color: var(--zinc-500); margin-top: 0.15rem;">
                                    Specific to this single field
                                </div>
                            </button>
                            <button
                                type="button"
                                id="btnPicklistSourceGlobal"
                                onclick="setPicklistSource('global')"
                                style="padding: 0.5rem; border-radius: 8px; border: 1px solid var(--zinc-200, #cbd5e1); background: #ffffff; font-weight: 500; color: var(--zinc-600); font-size: 0.75rem; cursor: pointer; text-align: left;"
                            >
                                🌐 Global Value Set
                                <div style="font-size: 0.675rem; font-weight: 400; color: var(--zinc-500); margin-top: 0.15rem;">
                                    Shared across multiple fields
                                </div>
                            </button>
                        </div>
                    </div>

                    <!-- Custom Values Input -->
                    <div id="panelPicklistCustom">
                        <label class="form-label" style="font-size: 0.725rem;">Picklist Options (Comma-Separated or Line-by-Line)</label>
                        <textarea
                            id="formDropdownOptions"
                            class="form-input"
                            style="height: 75px; font-size: 0.78125rem; resize: vertical;"
                            placeholder="Option 1, Option 2, Option 3"
                        ></textarea>
                    </div>

                    <!-- Global Value Set Select -->
                    <div id="panelPicklistGlobal" style="display: none;">
                        <label class="form-label" style="font-size: 0.725rem;">Select Global Picklist Set</label>
                        <select
                            id="formGlobalPicklistId"
                            class="form-select"
                        >
                            <!-- Populated dynamically -->
                        </select>
                    </div>
                </div>

                <!-- CONDITIONAL TYPE PANEL: AutoNumber -->
                <div id="panelAutoNumber" style="display: none; background: var(--zinc-50); padding: 0.85rem; border-radius: 10px; border: 1px solid var(--zinc-200); flex-direction: column; gap: 0.65rem;">
                    <div style="font-size: 0.75rem; font-weight: 700; color: var(--zinc-800);">Auto Number Format Configuration</div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                        <div>
                            <label class="form-label" style="font-size: 0.725rem;">Prefix (e.g. INV-, PO-)</label>
                            <input type="text" id="formAutonumberPrefix" class="form-input" value="INV-">
                        </div>
                        <div>
                            <label class="form-label" style="font-size: 0.725rem;">Suffix (Optional)</label>
                            <input type="text" id="formAutonumberSuffix" class="form-input" placeholder="-2027">
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                        <div>
                            <label class="form-label" style="font-size: 0.725rem;">Zero Padding (Digits)</label>
                            <input type="number" id="formAutonumberPadding" class="form-input" value="4" min="1" max="10">
                        </div>
                        <div>
                            <label class="form-label" style="font-size: 0.725rem;">Starting Sequence</label>
                            <input type="number" id="formAutonumberStartNum" class="form-input" value="1" min="1">
                        </div>
                    </div>
                </div>

                <!-- CONDITIONAL TYPE PANEL: Rating -->
                <div id="panelRating" style="display: none; background: var(--zinc-50); padding: 0.85rem; border-radius: 10px; border: 1px solid var(--zinc-200); flex-direction: column; gap: 0.65rem;">
                    <div style="font-size: 0.75rem; font-weight: 700; color: var(--zinc-800);">Visual Rating Scale Parameters</div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                        <div>
                            <label class="form-label" style="font-size: 0.725rem;">Maximum Score</label>
                            <select id="formRatingMax" class="form-select">
                                <option value="5">5 Points (★ ★ ★ ★ ★)</option>
                                <option value="10">10 Points (★ 1 to 10)</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label" style="font-size: 0.725rem;">Rating Icon Style</label>
                            <select id="formRatingIcon" class="form-select">
                                <option value="star">★ Gold Stars</option>
                                <option value="heart">♥ Red Hearts</option>
                                <option value="flame">🔥 Flame Burners</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- CONDITIONAL TYPE PANEL: MaskedText -->
                <div id="panelMaskedText" style="display: none; background: var(--zinc-50); padding: 0.85rem; border-radius: 10px; border: 1px solid var(--zinc-200);">
                    <label class="form-label" style="font-size: 0.725rem;">Data Masking Pattern</label>
                    <select id="formMaskedPattern" class="form-select">
                        <option value="last4">Show Last 4 Characters (•••• •••• •••• 1234)</option>
                        <option value="first4">Show First 4 Characters (1234 •••• •••• ••••)</option>
                        <option value="ssn">Social Security Number (•••-••-1234)</option>
                        <option value="all">Fully Masked (••••••••••••)</option>
                    </select>
                </div>

                <!-- Default Value -->
                <div>
                    <label class="form-label">Default Value (Optional)</label>
                    <input
                        type="text"
                        id="formDefaultValue"
                        class="form-input"
                        placeholder="e.g. Standard, 0.00, or Initial Value"
                    >
                </div>

                <!-- Help Text / Tooltip -->
                <div>
                    <label class="form-label">Help Text / Field Tooltip</label>
                    <input
                        type="text"
                        id="formHelpText"
                        class="form-input"
                        placeholder="e.g. Guidance shown to reps when completing record"
                    >
                </div>

                <!-- Required Checkbox -->
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <input
                        type="checkbox"
                        id="formIsRequired"
                        style="width: 16px; height: 16px; accent-color: var(--color-primary, #4f46e5); cursor: pointer;"
                    >
                    <label for="formIsRequired" style="font-size: 0.8125rem; font-weight: 600; color: var(--zinc-900, #09090b); cursor: pointer; margin: 0;">
                        Required Field (Must be populated before record save)
                    </label>
                </div>
            </div>

            <!-- Modal Footer -->
            <div style="padding: 0.85rem 1.5rem; border-top: 1px solid var(--zinc-200, #e2e8f0); background: var(--zinc-50, #f8fafc); display: flex; align-items: center; justify-content: flex-end; gap: 0.5rem; flex-shrink: 0;">
                <button
                    type="button"
                    onclick="closeCustomFieldModal()"
                    class="btn btn-secondary"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    class="btn btn-primary"
                    style="display: flex; align-items: center; gap: 0.4rem; font-weight: 600;"
                >
                    <i data-lucide="check" style="width: 14px; height: 14px;"></i>
                    <span id="btnSaveCustomFieldText">Save Custom Field</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 2: EDIT STANDARD FIELD OVERRIDE (SettingsView.tsx lines 3800-3920)  -->
<!-- ========================================================================= -->
<div
    id="standardFieldModalBackdrop"
    style="position: fixed; inset: 0; z-index: 99999; display: none; align-items: center; justify-content: center; padding: 1.5rem; background: rgba(9, 9, 11, 0.65); backdrop-filter: blur(5px);"
    onclick="handleCloseModal(event, 'standardFieldModalBackdrop')"
>
    <div
        id="standardFieldModalDialog"
        style="position: relative; width: 560px; maxWidth: 95vw; maxHeight: 90vh; background: #ffffff; border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35); display: flex; flex-direction: column; overflow: hidden; margin: auto;"
        onclick="event.stopPropagation()"
    >
        <!-- Modal Header -->
        <div style="padding: 1.15rem 1.5rem; border-bottom: 1px solid var(--zinc-200, #e2e8f0); background: var(--zinc-50, #f8fafc); display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div style="font-size: 0.6875rem; font-weight: 700; color: var(--color-primary, #4f46e5); text-transform: uppercase;">Core CRM Schema</div>
                <h3 id="standardFieldModalTitle" style="font-size: 1rem; font-weight: 700; color: var(--zinc-950, #09090b); margin: 0.15rem 0 0 0;">
                    Customize Standard Field
                </h3>
            </div>
            <button
                type="button"
                onclick="closeStandardFieldModal()"
                class="btn btn-ghost btn-sm"
                style="width: 30px; height: 30px; border-radius: 50%; padding: 0; display: flex; align-items: center; justify-content: center; color: var(--zinc-400);"
            >
                <i data-lucide="x" style="width: 16px; height: 16px;"></i>
            </button>
        </div>

        <form onsubmit="handleSaveStandardFieldOverride(event)" style="display: flex; flex-direction: column; flex: 1; margin: 0;">
            <div style="padding: 1.25rem 1.5rem; display: flex; flex-direction: column; gap: 1rem;">
                <div>
                    <label class="form-label">Database Column Name</label>
                    <input type="text" id="formSfColumnName" class="form-input" disabled style="background: var(--zinc-100); color: var(--zinc-600);">
                </div>

                <div>
                    <label class="form-label">Custom Display Label *</label>
                    <input type="text" id="formSfCustomLabel" class="form-input" required>
                </div>

                <div>
                    <label class="form-label">Custom Help Text / Description</label>
                    <input type="text" id="formSfHelpText" class="form-input">
                </div>

                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <input type="checkbox" id="formSfIsRequired" style="width: 16px; height: 16px; accent-color: var(--color-primary); cursor: pointer;">
                    <label for="formSfIsRequired" style="font-size: 0.8125rem; font-weight: 600; color: var(--zinc-900); cursor: pointer; margin: 0;">
                        Enforce as Required Field
                    </label>
                </div>
            </div>

            <div style="padding: 0.85rem 1.5rem; border-top: 1px solid var(--zinc-200); background: var(--zinc-50); display: flex; align-items: center; justify-content: space-between;">
                <button
                    type="button"
                    onclick="handleResetStandardFieldOverride()"
                    class="btn btn-secondary btn-sm"
                    style="color: #b91c1c;"
                >
                    Reset to Default
                </button>
                <div style="display: flex; gap: 0.5rem;">
                    <button type="button" onclick="closeStandardFieldModal()" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="font-weight: 600;">Save Override</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 3: CREATE / EDIT GLOBAL PICKLIST SET (SettingsView.tsx line 3950+)   -->
<!-- ========================================================================= -->
<div
    id="globalPicklistModalBackdrop"
    style="position: fixed; inset: 0; z-index: 99999; display: none; align-items: center; justify-content: center; padding: 1.5rem; background: rgba(9, 9, 11, 0.65); backdrop-filter: blur(5px);"
    onclick="handleCloseModal(event, 'globalPicklistModalBackdrop')"
>
    <div
        id="globalPicklistModalDialog"
        style="position: relative; width: 620px; maxWidth: 95vw; maxHeight: 90vh; background: #ffffff; border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35); display: flex; flex-direction: column; overflow: hidden; margin: auto;"
        onclick="event.stopPropagation()"
    >
        <!-- Modal Header -->
        <div style="padding: 1.15rem 1.5rem; border-bottom: 1px solid var(--zinc-200, #e2e8f0); background: var(--zinc-50, #f8fafc); display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div style="font-size: 0.6875rem; font-weight: 700; color: var(--color-primary, #4f46e5); text-transform: uppercase;">Shared Picklist Schema</div>
                <h3 id="globalPicklistModalTitle" style="font-size: 1rem; font-weight: 700; color: var(--zinc-950, #09090b); margin: 0.15rem 0 0 0;">
                    New Global Picklist Value Set
                </h3>
            </div>
            <button
                type="button"
                onclick="closeGlobalPicklistModal()"
                class="btn btn-ghost btn-sm"
                style="width: 30px; height: 30px; border-radius: 50%; padding: 0; display: flex; align-items: center; justify-content: center; color: var(--zinc-400);"
            >
                <i data-lucide="x" style="width: 16px; height: 16px;"></i>
            </button>
        </div>

        <form onsubmit="handleSaveGlobalPicklist(event)" style="display: flex; flex-direction: column; flex: 1; margin: 0;">
            <div style="padding: 1.25rem 1.5rem; display: flex; flex-direction: column; gap: 1rem;">
                <div>
                    <label class="form-label">Value Set Name *</label>
                    <input
                        type="text"
                        id="formGpName"
                        class="form-input"
                        placeholder="e.g. Industry Sectors, Global Regions"
                        required
                        oninput="handleGpNameInput(this.value)"
                    >
                </div>

                <div>
                    <label class="form-label">API Identifier *</label>
                    <input
                        type="text"
                        id="formGpApiName"
                        class="form-input"
                        placeholder="industry_sectors"
                        required
                    >
                </div>

                <div>
                    <label class="form-label">Description (Optional)</label>
                    <input
                        type="text"
                        id="formGpDescription"
                        class="form-input"
                        placeholder="e.g. Standard B2B industry vertical classification"
                    >
                </div>

                <div>
                    <label class="form-label">Picklist Values (One Value Per Line or Comma-Separated) *</label>
                    <textarea
                        id="formGpValuesText"
                        class="form-input"
                        style="height: 120px; font-size: 0.78125rem; resize: vertical;"
                        placeholder="Technology &amp; SaaS&#10;Healthcare &amp; Life Sciences&#10;Financial Services"
                        required
                    ></textarea>
                </div>
            </div>

            <div style="padding: 0.85rem 1.5rem; border-top: 1px solid var(--zinc-200); background: var(--zinc-50); display: flex; align-items: center; justify-content: flex-end; gap: 0.5rem;">
                <button type="button" onclick="closeGlobalPicklistModal()" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary" style="font-weight: 600;">Save Value Set</button>
            </div>
        </form>
    </div>
</div>

<script>
// ============================================================================
// OBJECT & FIELD MANAGER STUDIO ENGINE (1-TO-1 REACT SETTINGSVIEW PARITY)
// ============================================================================
const STORAGE_CUSTOM_FIELDS_KEY = 'dealsync_custom_fields';
const STORAGE_GLOBAL_PICKLISTS_KEY = 'dealsync_global_picklists';
const STORAGE_STANDARD_OVERRIDES_KEY = 'dealsync_standard_field_overrides';
const STORAGE_CUSTOM_OBJECTS_KEY = 'dealsync_custom_objects';

const MODULE_OPTIONS = [
    { id: 'opportunities', name: 'Opportunities (Sales Pipeline)' },
    { id: 'leads', name: 'Leads' },
    { id: 'accounts', name: 'Accounts' },
    { id: 'contacts', name: 'Contacts' },
    { id: 'tasks', name: 'Tasks' },
    { id: 'purchase_deals', name: 'Deals (Purchase & Procurement)' },
    { id: 'shipping', name: 'Shipping & Logistics' },
    { id: 'payments', name: 'Payments & Invoicing (AR / AP)' },
    { id: 'wires', name: 'Wire Received & Bank Reconciliation' },
    { id: 'users', name: 'Users (Active User Directory)' }
];

const DATA_TYPES = [
    { value: 'Text', label: 'Text (Single Line)' },
    { value: 'Textarea', label: 'Textarea (Multi-Line)' },
    { value: 'Number', label: 'Number (Numeric / Integer / Decimal)' },
    { value: 'Currency', label: 'Currency ($ USD)' },
    { value: 'Date', label: 'Date (Calendar Selector)' },
    { value: 'Time', label: 'Time (Clock Selector HH:MM)' },
    { value: 'DateTime', label: 'Date & Time (Calendar & Clock Selector)' },
    { value: 'Dropdown', label: 'Dropdown / Picklist (Single Select)' },
    { value: 'MultiSelect', label: 'Multi-Select Picklist (Select Multiple Values)' },
    { value: 'Boolean', label: 'Boolean (Checkbox Yes/No)' },
    { value: 'Email', label: 'Email Address' },
    { value: 'Phone', label: 'Phone Number' },
    { value: 'URL', label: 'URL / Web Link' },
    { value: 'Percentage', label: '% Percentage (Tax, Margin, Commission)' },
    { value: 'Radio', label: '🔘 Radio Buttons (Single Choice Button Group)' },
    { value: 'AutoNumber', label: '🔢 Auto Number (Sequential Unique Tracking ID)' },
    { value: 'RollupSummary', label: '∑ Rollup Summary (Child Object Aggregation)' },
    { value: 'Rating', label: '★ Rating (Visual Score 1–5 / 1–10)' },
    { value: 'MaskedText', label: '🔒 Masked / Encrypted Text (SSN, Bank, Tax ID)' },
    { value: 'ColorPicker', label: '🎨 Color Picker / Status Badge' },
    { value: 'RichText', label: '📝 Rich Text (WYSIWYG HTML Formatted)' },
    { value: 'Lookup', label: '🔗 Lookup Field (Relational Entity Reference)' },
    { value: 'Formula', label: '⚡ Formula Field (Lookup Import / Calculated Field)' }
];

const STANDARD_FIELDS = {
    opportunities: [
        { name: 'name', label: 'Opportunity Name', type: 'Text', required: true, description: 'Deal title or project name' },
        { name: 'account_id', label: 'Account', type: 'Lookup', required: true, description: 'Related corporate account' },
        { name: 'amount', label: 'Amount ($)', type: 'Currency', required: true, description: 'Total contracted deal value' },
        { name: 'stage', label: 'Stage', type: 'Dropdown', required: true, description: 'Pipeline stage' },
        { name: 'probability', label: 'Probability (%)', type: 'Number', required: false, description: 'Win likelihood percentage' },
        { name: 'close_date', label: 'Expected Close Date', type: 'Date', required: true, description: 'Target deal signing date' },
        { name: 'lead_source', label: 'Lead Source', type: 'Dropdown', required: false, description: 'Marketing attribution channel' },
        { name: 'assigned_to', label: 'Assigned Rep', type: 'Lookup', required: false, description: 'Opportunity owner' }
    ],
    leads: [
        { name: 'first_name', label: 'First Name', type: 'Text', required: true, description: 'Lead first name' },
        { name: 'last_name', label: 'Last Name', type: 'Text', required: true, description: 'Lead last name' },
        { name: 'company', label: 'Company Name', type: 'Text', required: true, description: 'Lead company or business' },
        { name: 'title', label: 'Job Title', type: 'Text', required: false, description: 'Job designation' },
        { name: 'email', label: 'Email Address', type: 'Email', required: true, description: 'Primary business email' },
        { name: 'phone', label: 'Phone Number', type: 'Phone', required: false, description: 'Direct contact phone' },
        { name: 'status', label: 'Status', type: 'Dropdown', required: true, description: 'Lead qualification status' },
        { name: 'score', label: 'Lead Score', type: 'Number', required: false, description: '0-100 propensity score' },
        { name: 'source', label: 'Lead Source', type: 'Dropdown', required: false, description: 'Inbound marketing channel' },
        { name: 'assigned_to', label: 'Assigned Rep', type: 'Text', required: false, description: 'Sales rep user assignment' }
    ],
    accounts: [
        { name: 'name', label: 'Account Name', type: 'Text', required: true, description: 'Legal or operating entity name' },
        { name: 'type', label: 'Account Type', type: 'Dropdown', required: true, description: 'Customer, Vendor, Prospect, Partner' },
        { name: 'industry', label: 'Industry Sector', type: 'Dropdown', required: false, description: 'Economic sector' },
        { name: 'annual_revenue', label: 'Annual Revenue ($)', type: 'Currency', required: false, description: 'Annual gross revenue' },
        { name: 'credit_limit', label: 'Credit Limit ($)', type: 'Currency', required: false, description: 'Maximum approved credit' },
        { name: 'website', label: 'Website URL', type: 'URL', required: false, description: 'Corporate website URL' },
        { name: 'phone', label: 'Main Phone', type: 'Phone', required: false, description: 'Corporate switchboard' },
        { name: 'billing_country', label: 'Billing Country', type: 'Dropdown', required: false, description: 'Tax residency country' }
    ],
    contacts: [
        { name: 'first_name', label: 'First Name', type: 'Text', required: true, description: 'Given name' },
        { name: 'last_name', label: 'Last Name', type: 'Text', required: true, description: 'Family name' },
        { name: 'email', label: 'Email Address', type: 'Email', required: true, description: 'Business email' },
        { name: 'phone', label: 'Phone Number', type: 'Phone', required: false, description: 'Direct telephone' },
        { name: 'account_id', label: 'Account', type: 'Lookup', required: true, description: 'Associated company' },
        { name: 'department', label: 'Department', type: 'Text', required: false, description: 'Business unit' }
    ],
    tasks: [
        { name: 'subject', label: 'Subject', type: 'Text', required: true, description: 'Action item headline' },
        { name: 'due_date', label: 'Due Date', type: 'Date', required: true, description: 'Completion deadline' },
        { name: 'priority', label: 'Priority', type: 'Dropdown', required: true, description: 'High, Medium, Low' },
        { name: 'status', label: 'Status', type: 'Dropdown', required: true, description: 'Not Started, In Progress, Completed' },
        { name: 'assigned_to', label: 'Assigned User', type: 'Lookup', required: true, description: 'Responsible team member' }
    ],
    purchase_deals: [
        { name: 'po_number', label: 'PO Reference #', type: 'Text', required: true, description: 'Purchase order reference' },
        { name: 'title', label: 'Deal Title', type: 'Text', required: true, description: 'Procurement transaction title' },
        { name: 'vendor_id', label: 'Vendor', type: 'Lookup', required: true, description: 'Target supplier' },
        { name: 'amount', label: 'Committed Spend ($)', type: 'Currency', required: true, description: 'Approved PO value' },
        { name: 'stage', label: 'Procurement Stage', type: 'Dropdown', required: true, description: 'Procurement workflow stage' }
    ],
    shipping: [
        { name: 'tracking_number', label: 'Tracking #', type: 'Text', required: true, description: 'Waybill tracking number' },
        { name: 'carrier', label: 'Carrier', type: 'Dropdown', required: true, description: 'Logistics provider' },
        { name: 'status', label: 'Status', type: 'Dropdown', required: true, description: 'Current shipment milestone' },
        { name: 'estimated_delivery', label: 'Estimated Delivery', type: 'Date', required: false, description: 'Expected arrival date' }
    ],
    payments: [
        { name: 'invoice_number', label: 'Invoice #', type: 'Text', required: true, description: 'Invoice reference' },
        { name: 'account_id', label: 'Account', type: 'Lookup', required: true, description: 'Billing entity' },
        { name: 'amount', label: 'Invoice Amount ($)', type: 'Currency', required: true, description: 'Total invoice payable' },
        { name: 'status', label: 'Payment Status', type: 'Dropdown', required: true, description: 'Paid, Issued, Overdue' }
    ],
    wires: [
        { name: 'utr_number', label: 'UTR Reference #', type: 'Text', required: true, description: 'Banking transaction UTR code' },
        { name: 'amount', label: 'Wire Amount ($)', type: 'Currency', required: true, description: 'Settled amount' },
        { name: 'bank_name', label: 'Bank Name', type: 'Text', required: true, description: 'Originating financial institution' }
    ],
    users: [
        { name: 'full_name', label: 'Full Name', type: 'Text', required: true, description: 'User display name' },
        { name: 'email', label: 'Email Address', type: 'Email', required: true, description: 'Corporate login email' },
        { name: 'status', label: 'Status', type: 'Dropdown', required: true, description: 'Active or Inactive' }
    ]
};

const SEED_GLOBAL_PICKLISTS = [
    {
        id: 'gp-industries',
        name: 'Industry Sectors',
        api_name: 'industry_sectors',
        description: 'Standard B2B industry vertical classification',
        values: ['Technology & SaaS', 'Healthcare & Life Sciences', 'Manufacturing & Industrial', 'Financial Services', 'Retail & E-commerce', 'Energy & Utilities', 'Telecommunications', 'Transportation & Logistics']
    },
    {
        id: 'gp-regions',
        name: 'Global Regions & Territories',
        api_name: 'global_regions',
        description: 'Sales geographical territories and operating theaters',
        values: ['North America (US/CA)', 'EMEA (Europe/Middle East/Africa)', 'APAC (Asia Pacific)', 'LATAM (Latin America)']
    },
    {
        id: 'gp-tiers',
        name: 'Customer & Partner Tiers',
        api_name: 'customer_tiers',
        description: 'Account priority rating and partnership classification level',
        values: ['Enterprise Tier 1 (VIP)', 'Mid-Market Tier 2', 'Growth Tier 3', 'Strategic Partner', 'Channel Distributor']
    },
    {
        id: 'gp-channels',
        name: 'Inbound Channel Sources',
        api_name: 'channel_sources',
        description: 'Marketing attribution and prospect intake origin channels',
        values: ['Direct Inbound Website', 'Executive Referral', 'Tradeshow & Event', 'Partner Referral', 'Cold Outreach Campaign', 'Organic Search / SEO']
    }
];

const SEED_CUSTOM_FIELDS = [
    {
        id: 'cf-opp-roi',
        module: 'opportunities',
        field_name: 'cf_target_roi',
        field_label: 'Target Return on Investment',
        data_type: 'Percentage',
        percentage_precision: 2,
        is_required: 0,
        default_value: '',
        help_text: 'Estimated ROI percentage for the client proposal'
    },
    {
        id: 'cf-opp-complexity',
        module: 'opportunities',
        field_name: 'cf_deal_complexity',
        field_label: 'Deal Implementation Complexity',
        data_type: 'Dropdown',
        options: 'Standard Turnkey, Moderate Customization, High Enterprise Integration, Mission-Critical',
        is_required: 1,
        default_value: 'Moderate Customization',
        help_text: 'Engineering complexity level'
    },
    {
        id: 'cf-opp-priority',
        module: 'opportunities',
        field_name: 'cf_deal_priority',
        field_label: 'Deal Executive Priority',
        data_type: 'Rating',
        rating_max: 5,
        rating_icon: 'star',
        is_required: 0,
        help_text: 'Executive leadership priority rating (1 to 5 stars)'
    },
    {
        id: 'cf-acc-taxid',
        module: 'accounts',
        field_name: 'cf_tax_id',
        field_label: 'Federal Tax ID / EIN',
        data_type: 'MaskedText',
        masked_pattern: 'last4',
        is_required: 0,
        help_text: 'Corporate tax identification number (Masked PII)'
    },
    {
        id: 'cf-acc-tier',
        module: 'accounts',
        field_name: 'cf_tier_level',
        field_label: 'Strategic Account Tier',
        data_type: 'Dropdown',
        global_picklist_id: 'gp-tiers',
        options: 'Enterprise Tier 1 (VIP), Mid-Market Tier 2, Growth Tier 3, Strategic Partner, Channel Distributor',
        is_required: 1,
        help_text: 'Linked to global account tiers'
    },
    {
        id: 'cf-acc-sentiment',
        module: 'accounts',
        field_name: 'cf_client_sentiment',
        field_label: 'Client Health & Sentiment',
        data_type: 'ColorPicker',
        color_presets: '#10b981,#3b82f6,#f59e0b,#ef4444',
        is_required: 0,
        help_text: 'Color status health indicator'
    },
    {
        id: 'cf-lead-budget',
        module: 'leads',
        field_name: 'cf_budget_approved',
        field_label: 'Budget Approved By Buyer',
        data_type: 'Boolean',
        is_required: 0,
        help_text: 'Whether procurement or executive approved funding'
    },
    {
        id: 'cf-lead-timeframe',
        module: 'leads',
        field_name: 'cf_decision_timeframe',
        field_label: 'Decision Timeframe',
        data_type: 'Dropdown',
        options: 'Immediate (< 30 days), This Quarter, 3-6 Months, Fiscal Year 2027',
        is_required: 0,
        help_text: 'Target purchase window'
    },
    {
        id: 'cf-pd-sla',
        module: 'purchase_deals',
        field_name: 'cf_vendor_sla',
        field_label: 'Vendor SLA Tier',
        data_type: 'Dropdown',
        options: 'Tier 1 - 99.99% Uptime, Tier 2 - 99.9% Uptime, Standard 99.0%, Best Effort',
        is_required: 1,
        help_text: 'Contractual vendor service level agreement'
    },
    {
        id: 'cf-pd-autonumber',
        module: 'purchase_deals',
        field_name: 'cf_po_tracking_code',
        field_label: 'Automated PO Tracking ID',
        data_type: 'AutoNumber',
        autonumber_prefix: 'PO-REQ-',
        autonumber_padding: 5,
        autonumber_start_num: 1001,
        is_required: 0,
        help_text: 'Sequential purchase order requisition code'
    }
];

// App State
let allCustomFields = [];
let globalPicklists = [];
let standardFieldOverrides = {};
let customObjectsList = [];

let activeSubTab = 'fields'; // 'fields' or 'global_picklists'
let selectedModuleForFields = 'opportunities';

let currentEditingField = null;
let currentEditingGp = null;
let currentEditingSf = null;

let isFieldNameManuallyEdited = false;
let currentPicklistSource = 'custom';

function initObjectFieldsStudio() {
    try {
        // Load Custom Fields
        const storedCfs = localStorage.getItem(STORAGE_CUSTOM_FIELDS_KEY);
        if (storedCfs) {
            allCustomFields = JSON.parse(storedCfs);
        } else {
            allCustomFields = [...SEED_CUSTOM_FIELDS];
            localStorage.setItem(STORAGE_CUSTOM_FIELDS_KEY, JSON.stringify(allCustomFields));
        }

        // Load Global Picklists
        const storedGps = localStorage.getItem(STORAGE_GLOBAL_PICKLISTS_KEY);
        if (storedGps) {
            globalPicklists = JSON.parse(storedGps);
        } else {
            globalPicklists = [...SEED_GLOBAL_PICKLISTS];
            localStorage.setItem(STORAGE_GLOBAL_PICKLISTS_KEY, JSON.stringify(globalPicklists));
        }

        // Load Standard Field Overrides
        const storedSfs = localStorage.getItem(STORAGE_STANDARD_OVERRIDES_KEY);
        standardFieldOverrides = storedSfs ? JSON.parse(storedSfs) : {};

        // Load Custom Objects
        const storedCos = localStorage.getItem(STORAGE_CUSTOM_OBJECTS_KEY);
        customObjectsList = storedCos ? JSON.parse(storedCos) : [];
    } catch (e) {
        allCustomFields = [...SEED_CUSTOM_FIELDS];
        globalPicklists = [...SEED_GLOBAL_PICKLISTS];
        standardFieldOverrides = {};
        customObjectsList = [];
    }

    renderModuleDropdown();
    renderDataTypesDropdown();
    renderKpiBadges();
    renderCustomFieldsTable();
    renderStandardFieldsTable();
    renderGlobalPicklistsTable();

    if (window.lucide) lucide.createIcons();
}

function saveCustomFieldsToStorage() {
    try {
        localStorage.setItem(STORAGE_CUSTOM_FIELDS_KEY, JSON.stringify(allCustomFields));
    } catch (e) {
        console.error('Failed to save custom fields', e);
    }
}

function saveGlobalPicklistsToStorage() {
    try {
        localStorage.setItem(STORAGE_GLOBAL_PICKLISTS_KEY, JSON.stringify(globalPicklists));
    } catch (e) {
        console.error('Failed to save global picklists', e);
    }
}

function saveStandardOverridesToStorage() {
    try {
        localStorage.setItem(STORAGE_STANDARD_OVERRIDES_KEY, JSON.stringify(standardFieldOverrides));
    } catch (e) {
        console.error('Failed to save standard field overrides', e);
    }
}

function renderKpiBadges() {
    const kpiCfs = document.getElementById('kpiActiveCustomFields');
    const kpiFormulas = document.getElementById('kpiFormulasRollups');
    const kpiGps = document.getElementById('kpiGlobalPicklists');
    const subTabGpCount = document.getElementById('subTabGpCount');

    const formulaRollupCount = allCustomFields.filter(f => f.data_type === 'Formula' || f.data_type === 'RollupSummary').length;

    if (kpiCfs) kpiCfs.textContent = `${allCustomFields.length} Fields`;
    if (kpiFormulas) kpiFormulas.textContent = `${formulaRollupCount} Active`;
    if (kpiGps) kpiGps.textContent = `${globalPicklists.length} Sets`;
    if (subTabGpCount) subTabGpCount.textContent = globalPicklists.length;
}

function renderModuleDropdown() {
    const select = document.getElementById('selectedModuleForFields');
    if (!select) return;

    let options = MODULE_OPTIONS.map(m => `
        <option value="${m.id}" ${m.id === selectedModuleForFields ? 'selected' : ''}>${escapeHtml(m.name)}</option>
    `);

    if (customObjectsList.length > 0) {
        customObjectsList.forEach(co => {
            const modKey = co.api_name ? `obj_${co.api_name}` : `co_${co.id}`;
            options.push(`
                <option value="${modKey}" ${modKey === selectedModuleForFields ? 'selected' : ''}>🧩 ${escapeHtml(co.label)} (Custom Object)</option>
            `);
        });
    }

    select.innerHTML = options.join('');
}

function renderDataTypesDropdown() {
    const select = document.getElementById('formDataType');
    if (!select) return;

    select.innerHTML = DATA_TYPES.map(dt => `
        <option value="${dt.value}">${escapeHtml(dt.label)}</option>
    `).join('');
}

function setSubTab(tab) {
    activeSubTab = tab;
    const viewFields = document.getElementById('viewFieldsSchema');
    const viewGps = document.getElementById('viewGlobalPicklists');
    const btnFields = document.getElementById('subTabBtnFields');
    const btnGps = document.getElementById('subTabBtnGlobalPicklists');

    if (tab === 'fields') {
        if (viewFields) viewFields.style.display = 'block';
        if (viewGps) viewGps.style.display = 'none';
        if (btnFields) btnFields.className = 'of-subtab-btn is-active';
        if (btnGps) btnGps.className = 'of-subtab-btn';
    } else {
        if (viewFields) viewFields.style.display = 'none';
        if (viewGps) viewGps.style.display = 'block';
        if (btnFields) btnFields.className = 'of-subtab-btn';
        if (btnGps) btnGps.className = 'of-subtab-btn is-active';
    }

    if (window.lucide) lucide.createIcons();
}

function handleModuleChange(newModule) {
    selectedModuleForFields = newModule;
    renderCustomFieldsTable();
    renderStandardFieldsTable();
}

function getModuleLabel(modId) {
    const m = MODULE_OPTIONS.find(x => x.id === modId);
    if (m) return m.name;
    const co = customObjectsList.find(c => c.api_name === modId || `obj_${c.api_name}` === modId || `co_${c.id}` === modId);
    if (co) return `${co.label} (Custom Object)`;
    return modId;
}

// ============================================================================
// CUSTOM FIELDS TABLE RENDERING & ACTIONS
// ============================================================================
function renderCustomFieldsTable() {
    const tbody = document.getElementById('customFieldsTableBody');
    const headerTitle = document.getElementById('customFieldsHeaderTitle');
    if (!tbody) return;

    const modFields = allCustomFields.filter(f => f.module === selectedModuleForFields);
    const modLabel = getModuleLabel(selectedModuleForFields);

    if (headerTitle) {
        headerTitle.textContent = `Custom Fields on ${modLabel} (${modFields.length})`;
    }

    if (modFields.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" style="text-align: center; padding: 3rem; color: var(--zinc-500);">
                    No custom fields configured on <strong>${escapeHtml(modLabel)}</strong> yet. Click "+ Create Custom Field" to add one.
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = modFields.map(cf => {
        const isMulti = cf.data_type === 'MultiSelect' || Boolean(cf.is_multiselect);
        const linkedGp = cf.global_picklist_id ? globalPicklists.find(g => g.id === cf.global_picklist_id) : null;

        // Data type badge color
        let dtBadgeClass = 'badge-gray';
        if (cf.data_type === 'Currency' || cf.data_type === 'RichText') dtBadgeClass = 'badge-green';
        else if (cf.data_type === 'Number' || cf.data_type === 'Percentage' || cf.data_type === 'AutoNumber') dtBadgeClass = 'badge-blue';
        else if (cf.data_type === 'Date' || cf.data_type === 'Time' || cf.data_type === 'DateTime' || cf.data_type === 'Rating' || cf.data_type === 'RollupSummary') dtBadgeClass = 'badge-amber';
        else if (cf.data_type === 'Dropdown' || cf.data_type === 'MultiSelect' || cf.data_type === 'Radio' || cf.data_type === 'ColorPicker') dtBadgeClass = 'badge-purple';
        else if (cf.data_type === 'MaskedText') dtBadgeClass = 'badge-red';

        return `
            <tr>
                <td>
                    <div style="font-weight: 600; color: var(--zinc-950, #09090b);">${escapeHtml(cf.field_label)}</div>
                    ${cf.default_value ? `
                        <div style="font-size: 0.725rem; color: var(--zinc-400); margin-top: 0.1rem;">Default: "${escapeHtml(cf.default_value)}"</div>
                    ` : ''}
                </td>
                <td>
                    <code style="font-size: 0.75rem; background: var(--zinc-100, #f1f5f9); padding: 0.15rem 0.4rem; border-radius: 4px; color: var(--zinc-800, #1e293b); border: 1px solid var(--zinc-200, #e2e8f0); font-family: ui-monospace, SFMono-Regular, monospace;">
                        ${escapeHtml(cf.field_name)}
                    </code>
                </td>
                <td>
                    <div style="display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap;">
                        <span class="badge ${dtBadgeClass}" style="font-size: 0.7rem; font-weight: 600;">
                            ${escapeHtml(cf.data_type)}
                        </span>
                        ${isMulti && cf.data_type !== 'MultiSelect' ? `
                            <span class="badge badge-purple" style="font-size: 0.65rem; font-weight: 600;">
                                Multi-Select
                            </span>
                        ` : ''}
                    </div>
                </td>
                <td>
                    ${linkedGp ? `
                        <div>
                            <span class="badge badge-blue" style="font-size: 0.65rem; margin-bottom: 0.2rem; display: inline-block;">
                                🌐 Global: ${escapeHtml(linkedGp.name)}
                            </span>
                            <div style="font-size: 0.725rem; color: var(--zinc-600); max-width: 240px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                ${escapeHtml(typeof cf.options === 'string' ? cf.options : JSON.stringify(cf.options || ''))}
                            </div>
                        </div>
                    ` : (cf.data_type === 'Dropdown' || cf.data_type === 'MultiSelect' || cf.data_type === 'Radio' || isMulti) ? `
                        <span style="font-size: 0.75rem; color: var(--zinc-600); max-width: 240px; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            ${escapeHtml(typeof cf.options === 'string' ? cf.options : JSON.stringify(cf.options || ''))}
                        </span>
                    ` : cf.data_type === 'AutoNumber' ? `
                        <span style="font-size: 0.75rem; color: #1e40af; font-weight: 600;">
                            ${escapeHtml(cf.autonumber_prefix || '')}${'0'.repeat(cf.autonumber_padding || 4)}${escapeHtml(cf.autonumber_suffix || '')}
                        </span>
                    ` : cf.data_type === 'RollupSummary' ? `
                        <span style="font-size: 0.75rem; color: #92400e; font-weight: 600;">
                            ∑ ${escapeHtml(cf.rollup_type || 'COUNT')} on ${escapeHtml(cf.rollup_child_module || 'records')}
                        </span>
                    ` : cf.data_type === 'Percentage' ? `
                        <span style="font-size: 0.75rem; color: var(--zinc-600);">
                            Precision: ${cf.percentage_precision ?? 2} decimals
                        </span>
                    ` : cf.data_type === 'Rating' ? `
                        <span style="font-size: 0.75rem; color: #d97706; font-weight: 600;">
                            ${cf.rating_icon === 'heart' ? '♥' : (cf.rating_icon === 'flame' ? '🔥' : '★')} Scale 1 to ${cf.rating_max || 5}
                        </span>
                    ` : cf.data_type === 'MaskedText' ? `
                        <span style="font-size: 0.75rem; color: #b91c1c; font-weight: 600;">
                            Pattern: ${escapeHtml(cf.masked_pattern || 'last4')}
                        </span>
                    ` : `
                        <span style="font-size: 0.75rem; color: var(--zinc-400);">Standard ${escapeHtml(cf.data_type)}</span>
                    `}
                </td>
                <td>
                    ${cf.is_required ? `
                        <span class="badge badge-red" style="font-size: 0.65rem; font-weight: 700; background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca;">Required</span>
                    ` : `
                        <span class="badge badge-gray" style="font-size: 0.65rem; font-weight: 500; background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0;">Optional</span>
                    `}
                </td>
                <td>
                    <span style="font-size: 0.75rem; color: var(--zinc-500, #64748b);">${escapeHtml(cf.help_text || '—')}</span>
                </td>
                <td style="text-align: right;">
                    <div style="display: inline-flex; gap: 0.3rem;">
                        <button
                            type="button"
                            onclick="openEditFieldModal('${cf.id}')"
                            class="btn btn-secondary btn-sm"
                            title="Modify Field"
                            style="display: inline-flex; align-items: center; gap: 0.25rem; font-size: 0.72rem; padding: 0.25rem 0.55rem;"
                        >
                            <i data-lucide="edit-2" style="width: 12px; height: 12px;"></i>
                            <span>Edit</span>
                        </button>
                        <button
                            type="button"
                            onclick="handleDeleteField('${cf.id}')"
                            class="btn btn-danger btn-sm"
                            title="Delete Field"
                            style="padding: 0.25rem 0.5rem; color: #dc2626; border-color: rgba(239,68,68,0.3); background: #fef2f2;"
                        >
                            <i data-lucide="trash-2" style="width: 12px; height: 12px;"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');

    if (window.lucide) lucide.createIcons();
}

// ============================================================================
// STANDARD FIELDS TABLE RENDERING & ACTIONS
// ============================================================================
function renderStandardFieldsTable() {
    const tbody = document.getElementById('standardFieldsTableBody');
    const headerTitle = document.getElementById('standardFieldsHeaderTitle');
    if (!tbody) return;

    const stds = STANDARD_FIELDS[selectedModuleForFields] || [];
    const modLabel = getModuleLabel(selectedModuleForFields);

    if (headerTitle) {
        headerTitle.textContent = `Standard System Fields on ${modLabel} (Core CRM Schema)`;
    }

    if (stds.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" style="text-align: center; padding: 2rem; color: var(--zinc-400);">
                    No predefined standard system columns for this custom object.
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = stds.map(sf => {
        const overrideKey = `${selectedModuleForFields}.${sf.name}`;
        const override = standardFieldOverrides[overrideKey];

        const activeLabel = override?.field_label || sf.label;
        const isReq = override !== undefined && override.is_required !== undefined ? Boolean(override.is_required) : Boolean(sf.required);
        const activeDesc = override?.help_text || sf.description;

        return `
            <tr>
                <td>
                    <strong style="color: var(--zinc-950, #09090b); font-weight: 600;">${escapeHtml(activeLabel)}</strong>
                    ${override && override.field_label ? `
                        <span class="badge badge-purple" style="font-size: 0.65rem; margin-left: 0.4rem; font-weight: 600;">Customized</span>
                    ` : ''}
                </td>
                <td>
                    <code style="font-size: 0.75rem; background: var(--zinc-50, #f8fafc); border: 1px solid var(--zinc-200, #e2e8f0); padding: 0.15rem 0.4rem; border-radius: 4px; color: var(--zinc-600, #475569); font-family: ui-monospace, SFMono-Regular, monospace;">
                        ${escapeHtml(sf.name)}
                    </code>
                </td>
                <td>
                    <span class="badge badge-gray" style="font-size: 0.7rem; font-weight: 500;">${escapeHtml(sf.type)}</span>
                </td>
                <td>
                    ${isReq ? `
                        <span class="badge badge-amber" style="font-size: 0.65rem; font-weight: 600;">Required</span>
                    ` : `
                        <span class="badge badge-gray" style="font-size: 0.65rem; font-weight: 500;">Optional</span>
                    `}
                </td>
                <td>
                    <span style="font-size: 0.75rem; color: var(--zinc-500, #64748b);">${escapeHtml(activeDesc)}</span>
                </td>
                <td style="text-align: right;">
                    <button
                        type="button"
                        onclick="openEditStandardFieldModal('${sf.name}', '${escapeHtml(sf.label)}', '${escapeHtml(sf.description)}', ${isReq ? 1 : 0})"
                        class="btn btn-secondary btn-sm"
                        title="Edit Standard Field Override"
                        style="display: inline-flex; align-items: center; gap: 0.25rem; font-size: 0.72rem; padding: 0.25rem 0.55rem;"
                    >
                        <i data-lucide="edit-2" style="width: 12px; height: 12px;"></i>
                        <span>Edit</span>
                    </button>
                </td>
            </tr>
        `;
    }).join('');

    if (window.lucide) lucide.createIcons();
}

// ============================================================================
// GLOBAL PICKLISTS TABLE RENDERING & ACTIONS
// ============================================================================
function renderGlobalPicklistsTable() {
    const tbody = document.getElementById('globalPicklistsTableBody');
    if (!tbody) return;

    if (globalPicklists.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" style="text-align: center; padding: 3.5rem; color: var(--zinc-400);">
                    No global picklist value sets found. Click "+ New Global Value Set" to define one.
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = globalPicklists.map(gp => {
        const vals = Array.isArray(gp.values) ? gp.values : [];

        return `
            <tr>
                <td>
                    <strong style="color: var(--zinc-950, #09090b); font-weight: 600;">${escapeHtml(gp.name)}</strong>
                </td>
                <td>
                    <code style="font-size: 0.75rem; background: var(--zinc-100, #f1f5f9); padding: 0.15rem 0.4rem; border-radius: 4px; color: var(--zinc-800, #1e293b); border: 1px solid var(--zinc-200, #e2e8f0); font-family: ui-monospace, SFMono-Regular, monospace;">
                        ${escapeHtml(gp.api_name)}
                    </code>
                </td>
                <td>
                    <span class="badge badge-blue" style="font-size: 0.7rem; font-weight: 600;">
                        ${vals.length} options
                    </span>
                </td>
                <td>
                    <div style="display: flex; flex-wrap: wrap; gap: 0.25rem; max-width: 380px;">
                        ${vals.slice(0, 4).map(v => `
                            <span style="
                                font-size: 0.7rem;
                                font-weight: 500;
                                padding: 0.1rem 0.35rem;
                                border-radius: 3px;
                                background: var(--zinc-100, #f1f5f9);
                                color: var(--zinc-700, #334155);
                                border: 1px solid var(--zinc-200, #e2e8f0);
                            ">
                                ${escapeHtml(v)}
                            </span>
                        `).join('')}
                        ${vals.length > 4 ? `
                            <span style="font-size: 0.7rem; color: var(--zinc-400); align-self: center;">
                                +${vals.length - 4} more
                            </span>
                        ` : ''}
                    </div>
                </td>
                <td>
                    <span style="font-size: 0.785rem; color: var(--zinc-500, #64748b);">
                        ${escapeHtml(gp.description || '—')}
                    </span>
                </td>
                <td style="text-align: right;">
                    <div style="display: inline-flex; gap: 0.3rem;">
                        <button
                            type="button"
                            onclick="openEditGlobalPicklistModal('${gp.id}')"
                            class="btn btn-secondary btn-sm"
                            style="display: inline-flex; align-items: center; gap: 0.25rem; font-size: 0.72rem; padding: 0.25rem 0.55rem;"
                        >
                            <i data-lucide="edit-2" style="width: 12px; height: 12px;"></i>
                            <span>Edit</span>
                        </button>
                        <button
                            type="button"
                            onclick="handleDeleteGlobalPicklist('${gp.id}')"
                            class="btn btn-danger btn-sm"
                            style="padding: 0.25rem 0.5rem; color: #dc2626; border-color: rgba(239,68,68,0.3); background: #fef2f2;"
                        >
                            <i data-lucide="trash-2" style="width: 12px; height: 12px;"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');

    if (window.lucide) lucide.createIcons();
}

// ============================================================================
// MODAL 1: CUSTOM FIELD CREATE & EDIT LOGIC
// ============================================================================
let labelDebounceTimeout = null;

function formatFieldKeyFromLabel(label) {
    if (!label) return 'cf_';
    const slug = label
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '_')
        .replace(/^_+|_+$/g, '');
    return slug ? `cf_${slug}` : 'cf_';
}

function handleFieldLabelInput(val) {
    if (currentEditingField || isFieldNameManuallyEdited) return;

    if (labelDebounceTimeout) clearTimeout(labelDebounceTimeout);
    labelDebounceTimeout = setTimeout(() => {
        const inputKey = document.getElementById('formFieldName');
        if (inputKey && !isFieldNameManuallyEdited) {
            inputKey.value = formatFieldKeyFromLabel(val);
        }
    }, 350);
}

function handleFieldLabelBlur(val) {
    if (currentEditingField || isFieldNameManuallyEdited) return;
    const inputKey = document.getElementById('formFieldName');
    if (inputKey && !isFieldNameManuallyEdited) {
        inputKey.value = formatFieldKeyFromLabel(val);
    }
}

function handleFieldNameManualInput(val) {
    if (!currentEditingField) {
        isFieldNameManuallyEdited = Boolean(val.trim());
        const btnReset = document.getElementById('btnResetFieldKey');
        if (btnReset) btnReset.style.display = isFieldNameManuallyEdited ? 'inline-block' : 'none';
    }
}

function handleResetFieldKey() {
    isFieldNameManuallyEdited = false;
    const label = document.getElementById('formFieldLabel').value;
    document.getElementById('formFieldName').value = formatFieldKeyFromLabel(label);
    const btnReset = document.getElementById('btnResetFieldKey');
    if (btnReset) btnReset.style.display = 'none';
}

function handleDataTypeChange(type) {
    const pDropdown = document.getElementById('panelDropdownOptions');
    const pAutoNumber = document.getElementById('panelAutoNumber');
    const pRating = document.getElementById('panelRating');
    const pMasked = document.getElementById('panelMaskedText');

    if (pDropdown) pDropdown.style.display = (type === 'Dropdown' || type === 'MultiSelect') ? 'flex' : 'none';
    if (pAutoNumber) pAutoNumber.style.display = (type === 'AutoNumber') ? 'flex' : 'none';
    if (pRating) pRating.style.display = (type === 'Rating') ? 'flex' : 'none';
    if (pMasked) pMasked.style.display = (type === 'MaskedText') ? 'block' : 'none';

    if (type === 'MultiSelect') {
        const chk = document.getElementById('formIsMultiSelect');
        if (chk) { chk.checked = true; chk.disabled = true; }
    } else if (type === 'Dropdown') {
        const chk = document.getElementById('formIsMultiSelect');
        if (chk) { chk.disabled = false; }
    }
}

function setPicklistSource(source) {
    currentPicklistSource = source;
    const btnCustom = document.getElementById('btnPicklistSourceCustom');
    const btnGlobal = document.getElementById('btnPicklistSourceGlobal');
    const panelCustom = document.getElementById('panelPicklistCustom');
    const panelGlobal = document.getElementById('panelPicklistGlobal');

    if (source === 'custom') {
        if (btnCustom) {
            btnCustom.style.border = '2px solid var(--color-primary, #4f46e5)';
            btnCustom.style.background = '#eff6ff';
            btnCustom.style.fontWeight = '600';
            btnCustom.style.color = 'var(--color-primary, #4f46e5)';
        }
        if (btnGlobal) {
            btnGlobal.style.border = '1px solid var(--zinc-200, #cbd5e1)';
            btnGlobal.style.background = '#ffffff';
            btnGlobal.style.fontWeight = '500';
            btnGlobal.style.color = 'var(--zinc-600)';
        }
        if (panelCustom) panelCustom.style.display = 'block';
        if (panelGlobal) panelGlobal.style.display = 'none';
    } else {
        if (btnCustom) {
            btnCustom.style.border = '1px solid var(--zinc-200, #cbd5e1)';
            btnCustom.style.background = '#ffffff';
            btnCustom.style.fontWeight = '500';
            btnCustom.style.color = 'var(--zinc-600)';
        }
        if (btnGlobal) {
            btnGlobal.style.border = '2px solid var(--color-primary, #4f46e5)';
            btnGlobal.style.background = '#eff6ff';
            btnGlobal.style.fontWeight = '600';
            btnGlobal.style.color = 'var(--color-primary, #4f46e5)';
        }
        if (panelCustom) panelCustom.style.display = 'none';
        if (panelGlobal) panelGlobal.style.display = 'block';

        populateGlobalPicklistDropdown();
    }
}

function populateGlobalPicklistDropdown(selectedId = '') {
    const select = document.getElementById('formGlobalPicklistId');
    if (!select) return;

    select.innerHTML = globalPicklists.map(gp => `
        <option value="${gp.id}" ${gp.id === selectedId ? 'selected' : ''}>${escapeHtml(gp.name)} (${(gp.values || []).length} options)</option>
    `).join('');
}

function openCreateFieldModal() {
    currentEditingField = null;
    isFieldNameManuallyEdited = false;

    document.getElementById('customFieldModalTitle').textContent = `Create Custom Field on ${getModuleLabel(selectedModuleForFields)}`;
    document.getElementById('modalTargetObjectDisplay').textContent = getModuleLabel(selectedModuleForFields);
    document.getElementById('btnSaveCustomFieldText').textContent = 'Create Custom Field';

    document.getElementById('formFieldLabel').value = '';
    document.getElementById('formFieldName').value = 'cf_';
    document.getElementById('formFieldName').disabled = false;
    document.getElementById('btnResetFieldKey').style.display = 'none';

    document.getElementById('formDataType').value = 'Text';
    handleDataTypeChange('Text');

    document.getElementById('formDropdownOptions').value = '';
    setPicklistSource('custom');

    document.getElementById('formAutonumberPrefix').value = 'REQ-';
    document.getElementById('formAutonumberSuffix').value = '';
    document.getElementById('formAutonumberPadding').value = 4;
    document.getElementById('formAutonumberStartNum').value = 1;

    document.getElementById('formRatingMax').value = '5';
    document.getElementById('formRatingIcon').value = 'star';
    document.getElementById('formMaskedPattern').value = 'last4';

    document.getElementById('formDefaultValue').value = '';
    document.getElementById('formHelpText').value = '';
    document.getElementById('formIsRequired').checked = false;

    const modal = document.getElementById('customFieldModalBackdrop');
    if (modal) modal.style.display = 'flex';
    if (window.lucide) lucide.createIcons();
}

function openEditFieldModal(fieldId) {
    const cf = allCustomFields.find(f => f.id === fieldId);
    if (!cf) return;

    currentEditingField = cf;
    isFieldNameManuallyEdited = true;

    document.getElementById('customFieldModalTitle').textContent = `Modify Field: ${cf.field_label}`;
    document.getElementById('modalTargetObjectDisplay').textContent = getModuleLabel(cf.module);
    document.getElementById('btnSaveCustomFieldText').textContent = 'Update Custom Field';

    document.getElementById('formFieldLabel').value = cf.field_label;
    document.getElementById('formFieldName').value = cf.field_name;
    document.getElementById('formFieldName').disabled = true; // Key cannot be changed after creation
    document.getElementById('btnResetFieldKey').style.display = 'none';

    document.getElementById('formDataType').value = cf.data_type;
    handleDataTypeChange(cf.data_type);

    if (cf.data_type === 'Dropdown' || cf.data_type === 'MultiSelect') {
        document.getElementById('formDropdownOptions').value = typeof cf.options === 'string' ? cf.options : JSON.stringify(cf.options || '');
        if (cf.global_picklist_id) {
            setPicklistSource('global');
            populateGlobalPicklistDropdown(cf.global_picklist_id);
        } else {
            setPicklistSource('custom');
        }
        document.getElementById('formIsMultiSelect').checked = Boolean(cf.is_multiselect || cf.data_type === 'MultiSelect');
    }

    if (cf.data_type === 'AutoNumber') {
        document.getElementById('formAutonumberPrefix').value = cf.autonumber_prefix || '';
        document.getElementById('formAutonumberSuffix').value = cf.autonumber_suffix || '';
        document.getElementById('formAutonumberPadding').value = cf.autonumber_padding || 4;
        document.getElementById('formAutonumberStartNum').value = cf.autonumber_start_num || 1;
    }

    if (cf.data_type === 'Rating') {
        document.getElementById('formRatingMax').value = String(cf.rating_max || 5);
        document.getElementById('formRatingIcon').value = cf.rating_icon || 'star';
    }

    if (cf.data_type === 'MaskedText') {
        document.getElementById('formMaskedPattern').value = cf.masked_pattern || 'last4';
    }

    document.getElementById('formDefaultValue').value = cf.default_value || '';
    document.getElementById('formHelpText').value = cf.help_text || '';
    document.getElementById('formIsRequired').checked = Boolean(cf.is_required);

    const modal = document.getElementById('customFieldModalBackdrop');
    if (modal) modal.style.display = 'flex';
    if (window.lucide) lucide.createIcons();
}

function closeCustomFieldModal() {
    const modal = document.getElementById('customFieldModalBackdrop');
    if (modal) modal.style.display = 'none';
}

function handleSaveCustomField(e) {
    e.preventDefault();

    const label = document.getElementById('formFieldLabel').value.trim();
    let name = document.getElementById('formFieldName').value.trim();
    const dataType = document.getElementById('formDataType').value;
    const isRequired = document.getElementById('formIsRequired').checked ? 1 : 0;
    const defaultValue = document.getElementById('formDefaultValue').value.trim();
    const helpText = document.getElementById('formHelpText').value.trim();

    if (!name.startsWith('cf_')) {
        name = `cf_${name}`;
    }

    let options = '';
    let globalPicklistId = null;
    let isMulti = 0;

    if (dataType === 'Dropdown' || dataType === 'MultiSelect') {
        isMulti = (dataType === 'MultiSelect' || document.getElementById('formIsMultiSelect').checked) ? 1 : 0;
        if (currentPicklistSource === 'global') {
            globalPicklistId = document.getElementById('formGlobalPicklistId').value;
            const matchedGp = globalPicklists.find(g => g.id === globalPicklistId);
            if (matchedGp && matchedGp.values) {
                options = Array.isArray(matchedGp.values) ? matchedGp.values.join(', ') : matchedGp.values;
            }
        } else {
            options = document.getElementById('formDropdownOptions').value.trim();
        }
    }

    if (currentEditingField) {
        // Update existing field
        const idx = allCustomFields.findIndex(f => f.id === currentEditingField.id);
        if (idx >= 0) {
            allCustomFields[idx] = {
                ...allCustomFields[idx],
                field_label: label,
                data_type: dataType,
                is_required: isRequired,
                default_value: defaultValue,
                help_text: helpText,
                options,
                global_picklist_id: globalPicklistId,
                is_multiselect: isMulti,
                autonumber_prefix: dataType === 'AutoNumber' ? document.getElementById('formAutonumberPrefix').value : undefined,
                autonumber_suffix: dataType === 'AutoNumber' ? document.getElementById('formAutonumberSuffix').value : undefined,
                autonumber_padding: dataType === 'AutoNumber' ? parseInt(document.getElementById('formAutonumberPadding').value) || 4 : undefined,
                autonumber_start_num: dataType === 'AutoNumber' ? parseInt(document.getElementById('formAutonumberStartNum').value) || 1 : undefined,
                rating_max: dataType === 'Rating' ? parseInt(document.getElementById('formRatingMax').value) || 5 : undefined,
                rating_icon: dataType === 'Rating' ? document.getElementById('formRatingIcon').value : undefined,
                masked_pattern: dataType === 'MaskedText' ? document.getElementById('formMaskedPattern').value : undefined
            };
        }
        showToast(`Custom field '${label}' updated successfully!`);
    } else {
        // Create new field
        const newField = {
            id: 'cf-' + Date.now(),
            module: selectedModuleForFields,
            field_name: name,
            field_label: label,
            data_type: dataType,
            is_required: isRequired,
            default_value: defaultValue,
            help_text: helpText,
            options,
            global_picklist_id: globalPicklistId,
            is_multiselect: isMulti,
            autonumber_prefix: dataType === 'AutoNumber' ? document.getElementById('formAutonumberPrefix').value : undefined,
            autonumber_suffix: dataType === 'AutoNumber' ? document.getElementById('formAutonumberSuffix').value : undefined,
            autonumber_padding: dataType === 'AutoNumber' ? parseInt(document.getElementById('formAutonumberPadding').value) || 4 : undefined,
            autonumber_start_num: dataType === 'AutoNumber' ? parseInt(document.getElementById('formAutonumberStartNum').value) || 1 : undefined,
            rating_max: dataType === 'Rating' ? parseInt(document.getElementById('formRatingMax').value) || 5 : undefined,
            rating_icon: dataType === 'Rating' ? document.getElementById('formRatingIcon').value : undefined,
            masked_pattern: dataType === 'MaskedText' ? document.getElementById('formMaskedPattern').value : undefined
        };
        allCustomFields.push(newField);
        showToast(`Custom field '${label}' created successfully!`);
    }

    saveCustomFieldsToStorage();
    closeCustomFieldModal();
    renderKpiBadges();
    renderCustomFieldsTable();
}

function handleDeleteField(fieldId) {
    const cf = allCustomFields.find(f => f.id === fieldId);
    if (!cf) return;

    if (!confirm(`Are you sure you want to delete the custom field '${cf.field_label}' (${cf.field_name})? Any saved values on records will be removed.`)) {
        return;
    }

    allCustomFields = allCustomFields.filter(f => f.id !== fieldId);
    saveCustomFieldsToStorage();
    renderKpiBadges();
    renderCustomFieldsTable();
    showToast(`Field '${cf.field_label}' deleted successfully.`);
}

// ============================================================================
// MODAL 2: STANDARD FIELD OVERRIDE LOGIC
// ============================================================================
function openEditStandardFieldModal(colName, defaultLabel, defaultDesc, isReq) {
    currentEditingSf = { name: colName };
    const overrideKey = `${selectedModuleForFields}.${colName}`;
    const override = standardFieldOverrides[overrideKey];

    document.getElementById('standardFieldModalTitle').textContent = `Customize Standard Column: ${colName}`;
    document.getElementById('formSfColumnName').value = colName;
    document.getElementById('formSfCustomLabel').value = override?.field_label || defaultLabel;
    document.getElementById('formSfHelpText').value = override?.help_text || defaultDesc;
    document.getElementById('formSfIsRequired').checked = override !== undefined && override.is_required !== undefined ? Boolean(override.is_required) : Boolean(isReq);

    const modal = document.getElementById('standardFieldModalBackdrop');
    if (modal) modal.style.display = 'flex';
    if (window.lucide) lucide.createIcons();
}

function closeStandardFieldModal() {
    const modal = document.getElementById('standardFieldModalBackdrop');
    if (modal) modal.style.display = 'none';
}

function handleSaveStandardFieldOverride(e) {
    e.preventDefault();
    if (!currentEditingSf) return;

    const colName = currentEditingSf.name;
    const overrideKey = `${selectedModuleForFields}.${colName}`;
    const customLabel = document.getElementById('formSfCustomLabel').value.trim();
    const helpText = document.getElementById('formSfHelpText').value.trim();
    const isRequired = document.getElementById('formSfIsRequired').checked ? 1 : 0;

    standardFieldOverrides[overrideKey] = {
        field_label: customLabel,
        help_text: helpText,
        is_required: isRequired
    };

    saveStandardOverridesToStorage();
    closeStandardFieldModal();
    renderStandardFieldsTable();
    showToast(`Standard column '${colName}' override saved successfully!`);
}

function handleResetStandardFieldOverride() {
    if (!currentEditingSf) return;
    const colName = currentEditingSf.name;
    const overrideKey = `${selectedModuleForFields}.${colName}`;

    delete standardFieldOverrides[overrideKey];
    saveStandardOverridesToStorage();
    closeStandardFieldModal();
    renderStandardFieldsTable();
    showToast(`Standard column '${colName}' reset to default.`);
}

// ============================================================================
// MODAL 3: GLOBAL PICKLISTS CREATE & EDIT LOGIC
// ============================================================================
function handleGpNameInput(val) {
    if (currentEditingGp) return;
    const slug = val
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '_')
        .replace(/^_+|_+$/g, '');
    const apiInput = document.getElementById('formGpApiName');
    if (apiInput) apiInput.value = slug;
}

function openCreateGlobalPicklistModal() {
    currentEditingGp = null;
    document.getElementById('globalPicklistModalTitle').textContent = 'New Global Picklist Value Set';
    document.getElementById('formGpName').value = '';
    document.getElementById('formGpApiName').value = '';
    document.getElementById('formGpDescription').value = '';
    document.getElementById('formGpValuesText').value = '';

    const modal = document.getElementById('globalPicklistModalBackdrop');
    if (modal) modal.style.display = 'flex';
    if (window.lucide) lucide.createIcons();
}

function openEditGlobalPicklistModal(gpId) {
    const gp = globalPicklists.find(g => g.id === gpId);
    if (!gp) return;

    currentEditingGp = gp;
    document.getElementById('globalPicklistModalTitle').textContent = `Edit Global Value Set: ${gp.name}`;
    document.getElementById('formGpName').value = gp.name;
    document.getElementById('formGpApiName').value = gp.api_name;
    document.getElementById('formGpDescription').value = gp.description || '';
    document.getElementById('formGpValuesText').value = Array.isArray(gp.values) ? gp.values.join('\n') : gp.values;

    const modal = document.getElementById('globalPicklistModalBackdrop');
    if (modal) modal.style.display = 'flex';
    if (window.lucide) lucide.createIcons();
}

function closeGlobalPicklistModal() {
    const modal = document.getElementById('globalPicklistModalBackdrop');
    if (modal) modal.style.display = 'none';
}

function handleSaveGlobalPicklist(e) {
    e.preventDefault();

    const name = document.getElementById('formGpName').value.trim();
    const apiName = document.getElementById('formGpApiName').value.trim();
    const description = document.getElementById('formGpDescription').value.trim();
    const rawValues = document.getElementById('formGpValuesText').value;

    const values = rawValues
        .split(/[\n,]+/)
        .map(v => v.trim())
        .filter(v => v.length > 0);

    if (values.length === 0) {
        alert('Please specify at least one picklist option value.');
        return;
    }

    if (currentEditingGp) {
        const idx = globalPicklists.findIndex(g => g.id === currentEditingGp.id);
        if (idx >= 0) {
            globalPicklists[idx] = {
                ...globalPicklists[idx],
                name,
                api_name: apiName,
                description,
                values
            };
        }
        showToast(`Global Picklist '${name}' updated successfully!`);
    } else {
        const newGp = {
            id: 'gp-' + Date.now(),
            name,
            api_name: apiName,
            description,
            values
        };
        globalPicklists.push(newGp);
        showToast(`Global Picklist '${name}' created successfully!`);
    }

    saveGlobalPicklistsToStorage();
    closeGlobalPicklistModal();
    renderKpiBadges();
    renderGlobalPicklistsTable();
}

function handleDeleteGlobalPicklist(gpId) {
    const gp = globalPicklists.find(g => g.id === gpId);
    if (!gp) return;

    if (!confirm(`Are you sure you want to delete global picklist '${gp.name}'? Existing custom fields using this set will retain their current options snapshot.`)) {
        return;
    }

    globalPicklists = globalPicklists.filter(g => g.id !== gpId);
    saveGlobalPicklistsToStorage();
    renderKpiBadges();
    renderGlobalPicklistsTable();
    showToast(`Global Picklist '${gp.name}' deleted.`);
}

function handleCloseModal(e, modalId) {
    if (e.target.id === modalId) {
        document.getElementById(modalId).style.display = 'none';
    }
}

let toastTimeout = null;
function showToast(msg) {
    const toast = document.getElementById('ofToast');
    const textEl = document.getElementById('ofToastMsg');
    if (!toast || !textEl) return;

    textEl.textContent = msg;
    toast.style.display = 'flex';
    if (window.lucide) lucide.createIcons();

    if (toastTimeout) clearTimeout(toastTimeout);
    toastTimeout = setTimeout(() => {
        toast.style.display = 'none';
    }, 3500);
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

document.addEventListener('DOMContentLoaded', initObjectFieldsStudio);
</script>
@endsection
