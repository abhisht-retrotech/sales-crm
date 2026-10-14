@extends('layouts.app')

@section('title', 'Dashboard Studio & Layout Designer — DealSync Admin')

@section('content')
<style>
/* 16-Column Responsive Grid System matching React index.css */
.dashboard-grid {
    display: grid !important;
    grid-template-columns: repeat(16, minmax(0, 1fr)) !important;
    gap: 1.25rem !important;
    width: 100% !important;
    box-sizing: border-box !important;
    overflow: hidden !important;
}

.col-span-16 { grid-column: span 16 / span 16 !important; min-width: 0 !important; max-width: 100% !important; overflow: hidden !important; box-sizing: border-box !important; }
.col-span-14 { grid-column: span 14 / span 14 !important; min-width: 0 !important; max-width: 100% !important; overflow: hidden !important; box-sizing: border-box !important; }
.col-span-12 { grid-column: span 12 / span 12 !important; min-width: 0 !important; max-width: 100% !important; overflow: hidden !important; box-sizing: border-box !important; }
.col-span-10 { grid-column: span 10 / span 10 !important; min-width: 0 !important; max-width: 100% !important; overflow: hidden !important; box-sizing: border-box !important; }
.col-span-8  { grid-column: span 8 / span 8 !important; min-width: 0 !important; max-width: 100% !important; overflow: hidden !important; box-sizing: border-box !important; }
.col-span-6  { grid-column: span 6 / span 6 !important; min-width: 0 !important; max-width: 100% !important; overflow: hidden !important; box-sizing: border-box !important; }
.col-span-5  { grid-column: span 5 / span 5 !important; min-width: 0 !important; max-width: 100% !important; overflow: hidden !important; box-sizing: border-box !important; }
.col-span-4  { grid-column: span 4 / span 4 !important; min-width: 0 !important; max-width: 100% !important; overflow: hidden !important; box-sizing: border-box !important; }
.col-span-3  { grid-column: span 3 / span 3 !important; min-width: 0 !important; max-width: 100% !important; overflow: hidden !important; box-sizing: border-box !important; }
.col-span-2  { grid-column: span 2 / span 2 !important; min-width: 0 !important; max-width: 100% !important; overflow: hidden !important; box-sizing: border-box !important; }

@media (max-width: 1360px) {
  .col-span-14, .col-span-12, .col-span-10 { grid-column: span 16 / span 16 !important; }
  .col-span-8, .col-span-6 { grid-column: span 8 / span 8 !important; }
  .col-span-4, .col-span-3, .col-span-2 { grid-column: span 8 / span 8 !important; }
}

@media (max-width: 900px) {
  .col-span-16, .col-span-14, .col-span-12, .col-span-10, .col-span-8, .col-span-6 {
    grid-column: span 16 / span 16 !important;
  }
  .col-span-5, .col-span-4, .col-span-3, .col-span-2 {
    grid-column: span 8 / span 8 !important;
  }
}

@media (max-width: 600px) {
  .dashboard-grid {
    grid-template-columns: 1fr !important;
    gap: 1rem !important;
  }
  .col-span-16, .col-span-14, .col-span-12, .col-span-10, .col-span-8, .col-span-6, .col-span-5, .col-span-4, .col-span-3, .col-span-2 {
    grid-column: span 1 / span 1 !important;
  }
}

.dashboard-section {
    border-radius: var(--radius-xl);
    transition: all 0.2s ease;
    overflow: hidden !important;
    box-sizing: border-box !important;
    width: 100% !important;
    margin-bottom: 1.75rem;
}

.dashboard-section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.75rem 1.25rem;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-xs);
    overflow: hidden !important;
    box-sizing: border-box !important;
    width: 100% !important;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}

.dashboard-section-header:hover {
    background: #ffffff;
    border-color: #6366f1;
    box-shadow: 0 2px 8px -2px rgba(99, 102, 241, 0.12);
}

.widget-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: var(--radius-xl);
    padding: 1.15rem;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
    position: relative;
    box-sizing: border-box !important;
    overflow: hidden !important;
    min-width: 0 !important;
    max-width: 100% !important;
    width: 100% !important;
    transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease, opacity 0.15s ease;
    user-select: none;
    -webkit-user-select: none;
    cursor: grab;
}

.widget-card:active {
    cursor: grabbing;
}

.widget-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.65rem;
    gap: 0.4rem;
    width: 100%;
    min-width: 0;
    overflow: hidden !important;
    box-sizing: border-box;
}

.widget-header-left {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    overflow: hidden !important;
    min-width: 0;
    flex-shrink: 0;
    white-space: nowrap;
}

.widget-header-right {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    overflow: hidden !important;
    flex-shrink: 0;
    white-space: nowrap;
    margin-left: auto;
}

/* In 4-column cards (and 3-col / 2-col) wrap neatly into 2 lines with 100% overflow protection */
.col-span-4 .widget-card-header,
.col-span-3 .widget-card-header,
.col-span-2 .widget-card-header {
    flex-wrap: wrap;
    gap: 0.45rem;
}

.col-span-4 .widget-header-left,
.col-span-3 .widget-header-left,
.col-span-2 .widget-header-left {
    width: 100%;
    justify-content: space-between;
}

.col-span-4 .widget-header-right,
.col-span-3 .widget-header-right,
.col-span-2 .widget-header-right {
    width: 100%;
    margin-left: 0;
    justify-content: space-between;
}

.widget-drag-handle {
    color: #94a3b8;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 3px;
    background: #f1f5f9;
    border-radius: 5px;
    cursor: grab;
    flex-shrink: 0;
    transition: background 0.15s ease, color 0.15s ease;
}

.widget-drag-handle:hover {
    background: #e0e7ff;
    color: #4f46e5;
}

.widget-drag-handle i,
.widget-drag-handle svg {
    pointer-events: none !important;
}

/* Drag and Drop Visual Feedback Styles */
.widget-card.is-dragging {
    opacity: 0.35 !important;
    border: 2px dashed #6366f1 !important;
    cursor: grabbing !important;
    pointer-events: none !important;
    transform: scale(0.98);
}

.widget-card.is-drag-target {
    border: 2px solid #6366f1 !important;
    box-shadow: 0 0 0 4px #e0e7ff, 0 10px 25px -5px rgba(99, 102, 241, 0.25) !important;
    transform: scale(1.02);
}

.dashboard-section {
    border-radius: var(--radius-xl);
    transition: all 0.2s ease;
    overflow: hidden !important;
    box-sizing: border-box !important;
    width: 100% !important;
    margin-bottom: 1.75rem;
}

.dashboard-section.is-section-drag-target {
    border: 2px dashed #6366f1 !important;
    background: #f5f3ff !important;
    padding: 0.5rem !important;
}
</style>
<div class="animate-fade-in" style="display: flex; flex-direction: column; gap: 1.25rem;">
    <!-- =========================================================================
         TOP STUDIO HEADER (Matches React DashboardStudio.tsx line 1042)
         ========================================================================= -->
    <div style="background: #ffffff; border: 1px solid #e4e4e7; border-radius: 24px; padding: 1.5rem 1.75rem; margin-bottom: 0.5rem; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04); display: flex; flex-direction: column; gap: 1.25rem;">
        <!-- ROW 1: Title & Right-aligned Badges -->
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
            <!-- Left: Icon, Title & 16-Column Canvas Pill -->
            <div style="display: flex; align-items: center; gap: 0.65rem; flex-wrap: wrap;">
                <i data-lucide="layout-dashboard" style="width: 22px; height: 22px; color: #4f46e5;"></i>
                <h1 style="font-size: 1.375rem; font-weight: 800; color: #09090b; letter-spacing: -0.025em; margin: 0;">
                    Dashboard Studio &amp; Layout Designer
                </h1>
                <span style="font-size: 0.75rem; font-weight: 700; color: #9333ea; background: #f3e8ff; padding: 3px 10px; border-radius: 9999px; line-height: 1;">
                    16-Column Canvas
                </span>
            </div>

            <!-- Right: 3 Stats & Feature Badges -->
            <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                <span id="stat-widgets-count" style="font-size: 0.78125rem; font-weight: 600; color: #3f3f46; background: #f4f4f5; border: 1px solid #e4e4e7; border-radius: 6px; padding: 4px 10px; display: inline-flex; align-items: center; gap: 4px;">
                    📦 8 Widgets
                </span>
                <span id="stat-sections-count" style="font-size: 0.78125rem; font-weight: 600; color: #3f3f46; background: #f4f4f5; border: 1px solid #e4e4e7; border-radius: 6px; padding: 4px 10px; display: inline-flex; align-items: center; gap: 4px;">
                    📑 3 Sections
                </span>
                <span style="font-size: 0.78125rem; font-weight: 600; color: #3b82f6; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; padding: 4px 10px; display: inline-flex; align-items: center; gap: 5px;">
                    <i data-lucide="arrow-left-right" style="width: 13px; height: 13px;"></i>
                    <span>Drag &amp; Drop Enabled</span>
                </span>
            </div>
        </div>

        <!-- ROW 2: Subtitle & Reset Defaults Link -->
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem; margin-top: -0.35rem;">
            <p style="font-size: 0.85rem; color: #71717a; margin: 0; line-height: 1.4;">
                Design and rearrange role-specific dashboard layouts, drag widgets, and configure interactive drilldown panels.
            </p>

            <button
                type="button"
                onclick="handleResetProfileLayout()"
                style="background: transparent; border: none; color: #71717a; font-size: 0.8rem; font-weight: 500; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; padding: 2px 4px;"
                title="Reset current profile back to default preset"
            >
                <i data-lucide="rotate-ccw" style="width: 12px; height: 12px;"></i>
                <span>Reset Defaults</span>
            </button>
        </div>

        <!-- ROW 3: Target Profile Card + Segmented Tabs + Save Button -->
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-top: 0.25rem;">
            <!-- Target Profile Picker Box -->
            <div style="position: relative; display: inline-flex; align-items: center; gap: 0.75rem; background: #ffffff; border: 1px solid #e4e4e7; border-radius: 12px; padding: 0.55rem 1rem; min-width: 310px; cursor: pointer;">
                <i data-lucide="shield-check" style="width: 18px; height: 18px; color: #6366f1; flex-shrink: 0;"></i>
                <div style="display: flex; flexDirection: column; line-height: 1.15;">
                    <span style="font-size: 0.72rem; font-weight: 700; color: #52525b;">Target</span>
                    <span style="font-size: 0.72rem; font-weight: 700; color: #52525b;">Profile:</span>
                </div>
                <div id="selected-profile-display-name" style="font-size: 0.925rem; font-weight: 700; color: #09090b; margin-left: 0.35rem; flex: 1;">
                    System Administrator
                </div>
                <i data-lucide="chevron-down" style="width: 16px; height: 16px; color: #09090b; flex-shrink: 0;"></i>

                <!-- Native select for seamless switching -->
                <select
                    id="profile-switcher-select"
                    style="position: absolute; inset: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; font-size: 1rem;"
                    onchange="handleProfileChange(this.value)"
                    title="Select profile"
                >
                    <option value="prof-admin" selected>System Administrator (Admin)</option>
                    <option value="prof-sales">Standard Sales Representative</option>
                    <option value="prof-procurement">Procurement Specialist</option>
                    <option value="prof-readonly">Read Only Profile</option>
                </select>
            </div>

            <!-- Right: Segmented Mode Switcher + Save & Publish -->
            <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                <!-- Segmented Mode Switcher -->
                <div style="display: flex; background: #f4f4f5; padding: 4px; border-radius: 10px; border: 1px solid #e4e4e7; gap: 4px;">
                    <button
                        type="button"
                        id="tab-btn-editor"
                        onclick="switchStudioTab('editor')"
                        style="display: flex; align-items: center; gap: 6px; padding: 0.45rem 1rem; font-size: 0.8125rem; font-weight: 700; color: #09090b; background: #ffffff; border: none; border-radius: 6px; cursor: pointer; box-shadow: 0 1px 3px rgba(0,0,0,0.1); transition: all 0.15s ease;"
                    >
                        <i data-lucide="sliders" style="width: 13px; height: 13px;"></i>
                        <span>Layout Designer</span>
                    </button>

                    <button
                        type="button"
                        id="tab-btn-preview"
                        onclick="switchStudioTab('preview')"
                        style="display: flex; align-items: center; gap: 6px; padding: 0.45rem 1rem; font-size: 0.8125rem; font-weight: 500; color: #71717a; background: transparent; border: none; border-radius: 6px; cursor: pointer; box-shadow: none; transition: all 0.15s ease;"
                    >
                        <i data-lucide="eye" style="width: 14px; height: 14px;"></i>
                        <span>Live Preview</span>
                    </button>
                </div>

                <!-- Primary Save & Publish Button -->
                <button
                    type="button"
                    onclick="handleSaveAndPublish()"
                    style="background: #4f46e5; color: #ffffff; font-weight: 700; font-size: 0.85rem; padding: 0.55rem 1.35rem; border-radius: 10px; border: none; display: inline-flex; align-items: center; gap: 7px; cursor: pointer; box-shadow: 0 1px 2px rgba(79, 70, 229, 0.25); transition: background 0.15s ease;"
                    onmouseenter="this.style.background='#4338ca'"
                    onmouseleave="this.style.background='#4f46e5'"
                >
                    <i data-lucide="save" style="width: 15px; height: 15px;"></i>
                    <span>Save &amp; Publish</span>
                </button>
            </div>
        </div>

        <!-- ROW 4: Action Buttons Row -->
        <div style="display: flex; align-items: center; gap: 0.65rem; flex-wrap: wrap; margin-top: 0.15rem;">
            <button
                type="button"
                onclick="openWidgetLibraryModal()"
                style="background: #ffffff; border: 1px solid #e4e4e7; border-radius: 8px; padding: 0.45rem 0.95rem; font-size: 0.8125rem; font-weight: 600; color: #18181b; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.02);"
            >
                <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
                <span>Add Widget</span>
            </button>

            <button
                type="button"
                onclick="openInsertSectionModal()"
                style="background: #ffffff; border: 1px solid #e4e4e7; border-radius: 8px; padding: 0.45rem 0.95rem; font-size: 0.8125rem; font-weight: 600; color: #18181b; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.02);"
            >
                <i data-lucide="folder-plus" style="width: 14px; height: 14px; color: #6366f1;"></i>
                <span>Insert Section</span>
            </button>

            <button
                type="button"
                onclick="openMatrixModal()"
                style="background: #ffffff; border: 1px solid #e4e4e7; border-radius: 8px; padding: 0.45rem 0.95rem; font-size: 0.8125rem; font-weight: 600; color: #18181b; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.02);"
            >
                <i data-lucide="users" style="width: 14px; height: 14px; color: #52525b;"></i>
                <span>Profile Dashboards Matrix</span>
            </button>

            <button
                type="button"
                onclick="openSettingsModal()"
                style="background: #ffffff; border: 1px solid #e4e4e7; border-radius: 8px; padding: 0.45rem 0.95rem; font-size: 0.8125rem; font-weight: 600; color: #18181b; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.02);"
            >
                <i data-lucide="calendar" style="width: 14px; height: 14px; color: #52525b;"></i>
                <span>Date &amp; Manager Controls</span>
            </button>
        </div>
    </div>

    <!-- Feedback Banner -->
    <div id="studio-feedback-banner" style="display: none; padding: 0.75rem 1.25rem; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: var(--radius-lg); color: #047857; margin-bottom: 0.5rem; align-items: center; gap: 0.5rem; font-size: 0.8125rem; font-weight: 500; animation: fadeInScale 0.25s ease;">
        <i data-lucide="check-circle-2" style="width: 16px; height: 16px;"></i>
        <span id="studio-feedback-text">Operation completed successfully</span>
    </div>

    <!-- =========================================================================
         TAB 1: CANVAS EDITOR VIEW (Matches React DashboardStudio.tsx lines 1445-2178)
         ========================================================================= -->
    <div id="tab-content-editor" style="display: flex; flex-direction: column; gap: 1.5rem;">
        <!-- 1. EXECUTIVE COMMAND HEADER & FAST ACTION BUTTONS CONFIGURATOR -->
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 1.25rem 1.5rem; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem; flex-wrap: wrap; gap: 0.5rem;">
                <div>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <h3 style="font-size: 1rem; font-weight: 700; color: #0f172a; margin: 0;">
                            Top Command Header &amp; Fast Action Buttons
                        </h3>
                        <span style="font-size: 0.6875rem; font-weight: 700; background: #e0e7ff; color: #4338ca; padding: 0.15rem 0.5rem; border-radius: 9999px; border: 1px solid #c7d2fe;">
                            Hero Command Banner
                        </span>
                    </div>
                    <p style="font-size: 0.75rem; color: #64748b; margin: 0.2rem 0 0 0;">
                        Customize the fast action buttons shown at the top of the Home Dashboard. Replace any action (e.g. replace <em>+ Log Call</em> with <em>+ New Opportunity</em>), edit labels, reorder, or customize button styles.
                    </p>
                </div>

                <button
                    type="button"
                    onclick="handleAddHeaderAction()"
                    style="background: #f8fafc; border: 1px solid #cbd5e1; color: #334155; border-radius: 8px; padding: 0.4rem 0.85rem; font-size: 0.75rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 5px;"
                >
                    <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
                    <span>Add Action Button</span>
                </button>
            </div>

            <!-- Visual Preview Pill of Header Buttons -->
            <div style="padding: 0.75rem 1rem; background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 1rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.75rem; color: #64748b; font-weight: 600;">
                    <span>Live Visual Preview:</span>
                </div>
                <div id="preview-header-actions-cluster" style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                    <!-- Injected dynamically -->
                </div>
            </div>

            <!-- Fast Action Buttons List Editor -->
            <div id="header-actions-editor-list" style="display: flex; flex-direction: column; gap: 0.625rem;">
                <!-- Injected dynamically -->
            </div>
        </div>

        <!-- 2. MODERN COMMAND CENTER COMPONENTS CONFIGURATOR -->
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 1.25rem 1.5rem; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04);">
            <div style="margin-bottom: 1rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <h3 style="font-size: 1rem; font-weight: 700; color: #0f172a; margin: 0;">
                        Modern Command Center Components (Global CRM Experience)
                    </h3>
                    <span style="font-size: 0.6875rem; font-weight: 700; background: #ecfdf5; color: #047857; padding: 0.15rem 0.5rem; border-radius: 9999px; border: 1px solid #a7f3d0;">
                        5 Executive Modules
                    </span>
                </div>
                <p style="font-size: 0.75rem; color: #64748b; margin: 0.2rem 0 0 0;">
                    Toggle and configure the interactive high-impact cards rendered on the Home Dashboard for all reps and executives.
                </p>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 0.85rem;">
                <!-- Module 1: High-Impact KPI Row -->
                <div style="padding: 0.85rem; border-radius: 12px; border: 1px solid #e2e8f0; background: #ffffff;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.35rem;">
                        <span style="font-size: 0.8125rem; font-weight: 700; color: #0f172a;">
                            📊 High-Impact 4-Column KPI Row
                        </span>
                        <input
                            type="checkbox"
                            id="toggle-kpi-row"
                            checked
                            onchange="handleToggleSetting('showKpiRow', this.checked)"
                            style="cursor: pointer;"
                        />
                    </div>
                    <p style="font-size: 0.6875rem; color: #64748b; margin: 0;">
                        Quota Attainment, Weighted Pipeline, NRR, and Active Accounts with sparklines, target bars &amp; click-to-drilldown database popups.
                    </p>
                </div>

                <!-- Module 2: AI Deal Insights -->
                <div style="padding: 0.85rem; border-radius: 12px; border: 1px solid #e2e8f0; background: #ffffff;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.35rem;">
                        <span style="font-size: 0.8125rem; font-weight: 700; color: #0f172a;">
                            ✨ AI Deal Insights Copilot
                        </span>
                        <input
                            type="checkbox"
                            id="toggle-ai-insights"
                            checked
                            onchange="handleToggleAi(this.checked)"
                            style="cursor: pointer;"
                        />
                    </div>
                    <p style="font-size: 0.6875rem; color: #64748b; margin: 0 0 0.5rem 0;">
                        Autonomous stagnation detection with one-click next-best-action execution.
                    </p>
                    <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.6875rem; color: #475569;">
                        <span>Threshold:</span>
                        <input
                            type="number"
                            min="1"
                            max="60"
                            id="ai-threshold-days"
                            value="9"
                            onchange="handleUpdateAiThreshold(this.value)"
                            style="width: 45px; padding: 2px 4px; border: 1px solid #cbd5e1; border-radius: 4px;"
                        />
                        <span>days inactive</span>
                    </div>
                </div>

                <!-- Module 3: Pipeline Velocity Funnel -->
                <div style="padding: 0.85rem; border-radius: 12px; border: 1px solid #e2e8f0; background: #ffffff;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.35rem;">
                        <span style="font-size: 0.8125rem; font-weight: 700; color: #0f172a;">
                            ⚡ Pipeline Velocity Funnel
                        </span>
                        <input
                            type="checkbox"
                            id="toggle-pipeline-funnel"
                            checked
                            onchange="handleToggleSetting('showPipelineFunnel', this.checked)"
                            style="cursor: pointer;"
                        />
                    </div>
                    <p style="font-size: 0.6875rem; color: #64748b; margin: 0;">
                        5-stage interactive waterfall with deal count, average stage velocity, rotting deal warnings, and instant deal inspections.
                    </p>
                </div>

                <!-- Module 4: Daily Action Cockpit -->
                <div style="padding: 0.85rem; border-radius: 12px; border: 1px solid #e2e8f0; background: #ffffff;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.35rem;">
                        <span style="font-size: 0.8125rem; font-weight: 700; color: #0f172a;">
                            🎯 Today's Action Cockpit (7/5 Split)
                        </span>
                        <input
                            type="checkbox"
                            id="toggle-action-cockpit"
                            checked
                            onchange="handleToggleSetting('showActionCockpit', this.checked)"
                            style="cursor: pointer;"
                        />
                    </div>
                    <p style="font-size: 0.6875rem; color: #64748b; margin: 0 0 0.5rem 0;">
                        Daily priorities checklist, in-line one-click approval workflows, and live team celebration ticker.
                    </p>
                    <div style="display: flex; gap: 0.75rem; font-size: 0.6875rem;">
                        <label style="display: flex; align-items: center; gap: 3px; cursor: pointer;">
                            <input
                                type="checkbox"
                                id="toggle-approvals"
                                checked
                                onchange="handleToggleSetting('showApprovalsQueue', this.checked)"
                            />
                            <span>Approvals</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 3px; cursor: pointer;">
                            <input
                                type="checkbox"
                                id="toggle-win-ticker"
                                checked
                                onchange="handleToggleSetting('showWinTicker', this.checked)"
                            />
                            <span>Win Ticker</span>
                        </label>
                    </div>
                </div>

                <!-- Module 5: Persona Switcher -->
                <div style="padding: 0.85rem; border-radius: 12px; border: 1px solid #e2e8f0; background: #ffffff;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.35rem;">
                        <span style="font-size: 0.8125rem; font-weight: 700; color: #0f172a;">
                            👥 Workspace Persona &amp; Role Tabs
                        </span>
                        <input
                            type="checkbox"
                            id="toggle-persona-switcher"
                            checked
                            onchange="handleToggleSetting('showPersonaSwitcher', this.checked)"
                            style="cursor: pointer;"
                        />
                    </div>
                    <p style="font-size: 0.6875rem; color: #64748b; margin: 0;">
                        Allows sales users to toggle views between Sales Executive, Sales Representative, and Procurement Specialist.
                    </p>
                </div>

                <!-- Module 6: Milestone Confetti -->
                <div style="padding: 0.85rem; border-radius: 12px; border: 1px solid #e2e8f0; background: #ffffff;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.35rem;">
                        <span style="font-size: 0.8125rem; font-weight: 700; color: #0f172a;">
                            🎉 100% Quota Milestone Confetti
                        </span>
                        <input
                            type="checkbox"
                            id="toggle-confetti"
                            checked
                            onchange="handleToggleSetting('enableConfettiMilestone', this.checked)"
                            style="cursor: pointer;"
                        />
                    </div>
                    <p style="font-size: 0.6875rem; color: #64748b; margin: 0;">
                        Full-screen celebratory particle confetti celebration effect upon 100% monthly quota attainment.
                    </p>
                </div>
            </div>
        </div>

        <!-- SECTIONS LIST ON THE CANVAS (Matches React lines 1809-2176) -->
        <div id="sections-canvas-container" style="display: flex; flex-direction: column; gap: 1.75rem;">
            <!-- Sections rendered dynamically via JavaScript -->
        </div>
    </div>

    <!-- =========================================================================
         TAB 2: LIVE PROFILE PREVIEW TAB (Matches React lines 2179-2424)
         ========================================================================= -->
    <div id="tab-content-preview" style="display: none; flex-direction: column; gap: 1.25rem;">
        <!-- Preview Top Bar -->
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: var(--radius-xl); padding: 1.25rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
            <div>
                <span style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em;">
                    Previewing Active Layout for Profile:
                </span>
                <div id="preview-profile-title" style="font-size: 1.125rem; font-weight: 700; color: #0f172a;">
                    System Administrator Workspace (16-Column Grid • 3 Sections)
                </div>
                <p style="font-size: 0.75rem; color: #64748b; margin: 0.2rem 0 0 0;">
                    Exact multi-section dashboard experience rendered when a user with profile <strong id="preview-profile-name">System Administrator</strong> signs in.
                </p>
            </div>
            <button
                type="button"
                onclick="handleSaveAndPublish()"
                class="btn btn-primary"
                style="display: inline-flex; align-items: center; gap: 6px;"
            >
                <i data-lucide="save" style="width: 14px; height: 14px;"></i>
                <span>Publish This Layout</span>
            </button>
        </div>

        <!-- 1. TOP HERO COMMAND HEADER -->
        <div style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 60%, #1e1b4b 100%); border-radius: 16px; padding: 1.5rem 1.75rem; color: #ffffff; box-shadow: 0 4px 20px -2px rgba(30, 27, 75, 0.35); position: relative; overflow: hidden;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; position: relative; z-index: 1;">
                <div>
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                        <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #a5b4fc;">
                            DealSync Command Center
                        </span>
                        <span style="display: inline-flex; align-items: center; gap: 4px; font-size: 0.6875rem; background: rgba(16, 185, 129, 0.2); color: #6ee7b7; padding: 2px 8px; border-radius: 9999px; border: 1px solid rgba(110, 231, 183, 0.3);">
                            <span style="width: 6px; height: 6px; border-radius: 50%; background: #10b981;"></span>
                            Live Preview Active
                        </span>
                    </div>
                    <h2 style="font-size: 1.5rem; font-weight: 800; margin: 0; letter-spacing: -0.02em;">
                        Good evening, Marcus 👋
                    </h2>
                    <p style="font-size: 0.8125rem; color: #c7d2fe; margin: 0.25rem 0 0 0;">
                        You have <strong style="color: #ffffff;">3 tasks due today</strong> and <strong style="color: #ffffff;">2 deals closing ($185k)</strong> this week.
                    </p>
                </div>

                <!-- Preview Actions Cluster -->
                <div id="hero-preview-buttons-cluster" style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                    <!-- Rendered dynamically -->
                </div>
            </div>
        </div>

        <!-- 2. INTERACTIVE PERSONA & FILTER BAR -->
        <div id="preview-persona-bar" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 0.75rem 1rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <span style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Workspace Persona:</span>
                <div style="display: flex; background: #f1f5f9; padding: 3px; border-radius: 8px; gap: 3px;">
                    <button type="button" class="btn btn-sm" style="background: #ffffff; color: #0f172a; font-weight: 700; padding: 3px 10px; font-size: 0.75rem; border-radius: 6px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); border: none;">Executive</button>
                    <button type="button" class="btn btn-sm" style="background: transparent; color: #64748b; font-weight: 500; padding: 3px 10px; font-size: 0.75rem; border-radius: 6px; border: none;">Sales Rep</button>
                    <button type="button" class="btn btn-sm" style="background: transparent; color: #64748b; font-weight: 500; padding: 3px 10px; font-size: 0.75rem; border-radius: 6px; border: none;">Procurement</button>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 0.65rem;">
                <div style="display: flex; align-items: center; gap: 0.35rem;">
                    <i data-lucide="calendar" style="width: 14px; height: 14px; color: #64748b;"></i>
                    <select class="form-select" style="font-size: 0.75rem; height: 28px; padding: 0 0.5rem;">
                        <option>This Month (October 2026)</option>
                        <option>This Quarter (Q4 2026)</option>
                        <option>This Year (2026)</option>
                    </select>
                </div>

                <div style="display: flex; align-items: center; gap: 0.35rem;">
                    <i data-lucide="users" style="width: 14px; height: 14px; color: #64748b;"></i>
                    <select class="form-select" style="font-size: 0.75rem; height: 28px; padding: 0 0.5rem;">
                        <option>Entire Sales Org (All Reps)</option>
                        <option>North America Enterprise</option>
                        <option>EMEA Strategic</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- 3. HIGH-IMPACT 4-COLUMN KPI ROW -->
        <div id="preview-kpi-row" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem;">
            <!-- KPI 1 -->
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 1.15rem; display: flex; flex-direction: column; justify-content: space-between;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Net ARR Run-Rate</span>
                    <span style="font-size: 0.6875rem; font-weight: 700; color: #059669; background: #ecfdf5; padding: 2px 6px; border-radius: 6px; border: 1px solid #a7f3d0;">↑ +24.8% YoY</span>
                </div>
                <div style="font-size: 1.75rem; font-weight: 800; color: #0f172a; margin: 0.5rem 0;">$48.6M</div>
                <div style="display: flex; align-items: center; justify-content: space-between; font-size: 0.72rem; color: #64748b;">
                    <span>Goal: $45.0M</span>
                    <span style="color: #4f46e5; font-weight: 700;">108% Paced</span>
                </div>
            </div>

            <!-- KPI 2 -->
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 1.15rem; display: flex; flex-direction: column; justify-content: space-between;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Weighted Pipeline</span>
                    <span style="font-size: 0.6875rem; font-weight: 700; color: #0891b2; background: #ecfeff; padding: 2px 6px; border-radius: 6px; border: 1px solid #a5f3fc;">118 Active</span>
                </div>
                <div style="font-size: 1.75rem; font-weight: 800; color: #0f172a; margin: 0.5rem 0;">$142.8M</div>
                <div style="display: flex; align-items: center; justify-content: space-between; font-size: 0.72rem; color: #64748b;">
                    <span>Target: $130.0M</span>
                    <span style="color: #0891b2; font-weight: 700;">+9.8% Safety</span>
                </div>
            </div>

            <!-- KPI 3 -->
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 1.15rem; display: flex; flex-direction: column; justify-content: space-between;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Win Velocity Rate</span>
                    <span style="font-size: 0.6875rem; font-weight: 700; color: #059669; background: #ecfdf5; padding: 2px 6px; border-radius: 6px; border: 1px solid #a7f3d0;">↑ +4.2%</span>
                </div>
                <div style="font-size: 1.75rem; font-weight: 800; color: #0f172a; margin: 0.5rem 0;">34.2%</div>
                <div style="display: flex; align-items: center; justify-content: space-between; font-size: 0.72rem; color: #64748b;">
                    <span>Avg Close: 19 days</span>
                    <span style="color: #059669; font-weight: 700;">Top Decile</span>
                </div>
            </div>

            <!-- KPI 4 -->
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 1.15rem; display: flex; flex-direction: column; justify-content: space-between;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Net Revenue Retention</span>
                    <span style="font-size: 0.6875rem; font-weight: 700; color: #e11d48; background: #fff1f2; padding: 2px 6px; border-radius: 6px; border: 1px solid #fecdd3;">1.1% Churn</span>
                </div>
                <div style="font-size: 1.75rem; font-weight: 800; color: #0f172a; margin: 0.5rem 0;">128.4%</div>
                <div style="display: flex; align-items: center; justify-content: space-between; font-size: 0.72rem; color: #64748b;">
                    <span>Benchmark: 120%</span>
                    <span style="color: #059669; font-weight: 700;">Expansion High</span>
                </div>
            </div>
        </div>

        <!-- 4. AI DEAL INSIGHTS COPILOT & PIPELINE FUNNEL GRID -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 1rem;">
            <!-- AI Copilot Card -->
            <div id="preview-ai-card" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 1.25rem; display: flex; flex-direction: column; gap: 0.85rem;">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <i data-lucide="sparkles" style="width: 18px; height: 18px; color: #4f46e5;"></i>
                        <h3 style="font-size: 0.95rem; font-weight: 700; color: #0f172a; margin: 0;">AI Deal Insights &amp; Stagnation Copilot</h3>
                    </div>
                    <span style="font-size: 0.6875rem; background: #eff6ff; color: #3b82f6; padding: 2px 8px; border-radius: 9999px; font-weight: 700;">2 At Risk</span>
                </div>

                <div style="display: flex; flex-direction: column; gap: 0.65rem;">
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 0.75rem;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                            <div>
                                <span style="font-size: 0.825rem; font-weight: 700; color: #0f172a;">Apex Global Systems</span>
                                <span style="font-size: 0.75rem; color: #64748b; display: block;">$450,000 ARR • Negotiation Stage</span>
                            </div>
                            <span style="font-size: 0.675rem; background: #fef2f2; color: #b91c1c; padding: 2px 6px; border-radius: 4px; font-weight: 700;">14d inactive</span>
                        </div>
                        <p style="font-size: 0.75rem; color: #475569; margin: 0.35rem 0 0.5rem 0;">No executive response since contract redline. Recommended action: send C-level alignment note.</p>
                        <button type="button" class="btn btn-primary btn-sm" style="font-size: 0.72rem; padding: 0.25rem 0.65rem;" onclick="showStudioToast('AI Copilot: Generated and queued executive re-engagement note!')">
                            ⚡ Send Executive Email
                        </button>
                    </div>

                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 0.75rem;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                            <div>
                                <span style="font-size: 0.825rem; font-weight: 700; color: #0f172a;">Novartis Biotech</span>
                                <span style="font-size: 0.75rem; color: #64748b; display: block;">$280,000 ARR • Proposal Review</span>
                            </div>
                            <span style="font-size: 0.675rem; background: #fffbeb; color: #b45309; padding: 2px 6px; border-radius: 4px; font-weight: 700;">11d inactive</span>
                        </div>
                        <p style="font-size: 0.75rem; color: #475569; margin: 0.35rem 0 0.5rem 0;">Procurement requested SOC2 update. Rep has not attached latest certification.</p>
                        <button type="button" class="btn btn-secondary btn-sm" style="font-size: 0.72rem; padding: 0.25rem 0.65rem;" onclick="showStudioToast('AI Copilot: Security packet queued for Rep delivery!')">
                            📞 Schedule Rep Check-in
                        </button>
                    </div>
                </div>
            </div>

            <!-- Pipeline Funnel Waterfall Card -->
            <div id="preview-funnel-card" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 1.25rem; display: flex; flex-direction: column; justify-content: space-between;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <i data-lucide="filter" style="width: 18px; height: 18px; color: #0891b2;"></i>
                        <h3 style="font-size: 0.95rem; font-weight: 700; color: #0f172a; margin: 0;">Enterprise Conversion Funnel</h3>
                    </div>
                    <span style="font-size: 0.6875rem; color: #64748b; font-weight: 600;">Overall Win: 34.2%</span>
                </div>

                <div style="display: flex; flex-direction: column; gap: 0.45rem;">
                    <!-- Discovery -->
                    <div>
                        <div style="display: flex; justify-content: space-between; font-size: 0.75rem; margin-bottom: 2px;">
                            <span style="font-weight: 600; color: #334155;">1. Discovery &amp; Qualification</span>
                            <span style="font-weight: 700; color: #0f172a;">118 Deals ($142.8M)</span>
                        </div>
                        <div style="height: 14px; background: #f1f5f9; border-radius: 4px; overflow: hidden;">
                            <div style="width: 100%; height: 100%; background: #6366f1; border-radius: 4px;"></div>
                        </div>
                    </div>

                    <!-- Proposal -->
                    <div>
                        <div style="display: flex; justify-content: space-between; font-size: 0.75rem; margin-bottom: 2px;">
                            <span style="font-weight: 600; color: #334155;">2. Solution Architecture &amp; Demo</span>
                            <span style="font-weight: 700; color: #0f172a;">84 Deals ($98.4M)</span>
                        </div>
                        <div style="height: 14px; background: #f1f5f9; border-radius: 4px; overflow: hidden;">
                            <div style="width: 71%; height: 100%; background: #06b6d4; border-radius: 4px;"></div>
                        </div>
                    </div>

                    <!-- Quote -->
                    <div>
                        <div style="display: flex; justify-content: space-between; font-size: 0.75rem; margin-bottom: 2px;">
                            <span style="font-weight: 600; color: #334155;">3. Commercial Proposal Submitted</span>
                            <span style="font-weight: 700; color: #0f172a;">52 Deals ($62.1M)</span>
                        </div>
                        <div style="height: 14px; background: #f1f5f9; border-radius: 4px; overflow: hidden;">
                            <div style="width: 44%; height: 100%; background: #3b82f6; border-radius: 4px;"></div>
                        </div>
                    </div>

                    <!-- Negotiation -->
                    <div>
                        <div style="display: flex; justify-content: space-between; font-size: 0.75rem; margin-bottom: 2px;">
                            <span style="font-weight: 600; color: #334155;">4. Legal Review &amp; Redlines</span>
                            <span style="font-weight: 700; color: #0f172a;">38 Deals ($48.6M)</span>
                        </div>
                        <div style="height: 14px; background: #f1f5f9; border-radius: 4px; overflow: hidden;">
                            <div style="width: 32%; height: 100%; background: #f59e0b; border-radius: 4px;"></div>
                        </div>
                    </div>

                    <!-- Closed Won -->
                    <div>
                        <div style="display: flex; justify-content: space-between; font-size: 0.75rem; margin-bottom: 2px;">
                            <span style="font-weight: 600; color: #334155;">5. Closed Won Execution</span>
                            <span style="font-weight: 700; color: #059669;">29 Deals ($36.4M)</span>
                        </div>
                        <div style="height: 14px; background: #f1f5f9; border-radius: 4px; overflow: hidden;">
                            <div style="width: 25%; height: 100%; background: #10b981; border-radius: 4px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 5. CUSTOM 16-COLUMN ANALYTICS SECTIONS IN PREVIEW -->
        <div id="preview-sections-container" style="display: flex; flex-direction: column; gap: 1.5rem; border-top: 1px solid #e2e8f0; padding-top: 1.25rem;">
            <!-- Rendered dynamically -->
        </div>
    </div>
</div>

<!-- =========================================================================
     MODAL 1: WIDGET CATALOG LIBRARY (17 Power BI Types with Visuals)
     ========================================================================= -->
<div id="modal-widget-library" class="modal-overlay" style="display: none;" onclick="closeWidgetLibraryModal()">
    <div class="modal-content" style="max-width: 960px; width: 100%; max-height: 90vh; display: flex; flex-direction: column; overflow: hidden; margin: auto;" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div>
                <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--zinc-900); margin: 0;">
                    Widget &amp; Visualization Library (16-Column Canvas)
                </h3>
                <p style="font-size: 0.75rem; color: var(--zinc-500); margin-top: 0.2rem; margin-bottom: 0;">
                    Choose from 17 Power BI &amp; Tableau-grade chart visualizations. Adding to: <strong id="library-target-section-title">Executive Revenue &amp; Quota Attainment</strong>.
                </p>
            </div>
            <button type="button" onclick="closeWidgetLibraryModal()" class="btn btn-ghost btn-sm">
                <i data-lucide="x" style="width: 16px; height: 16px;"></i>
            </button>
        </div>

        <!-- Category Filter Pills -->
        <div style="display: flex; gap: 0.4rem; padding: 0.75rem 1.5rem; border-bottom: 1px solid var(--zinc-100); flex-wrap: wrap; background: var(--zinc-50);">
            <button type="button" class="lib-cat-btn" data-cat="All" onclick="filterCatalog('All')" style="padding: 0.3rem 0.75rem; border-radius: var(--radius-full); font-size: 0.75rem; font-weight: 700; background: #6366f1; color: #ffffff; border: 1px solid var(--zinc-200); cursor: pointer;">All (17)</button>
            <button type="button" class="lib-cat-btn" data-cat="Metrics & Cards" onclick="filterCatalog('Metrics & Cards')" style="padding: 0.3rem 0.75rem; border-radius: var(--radius-full); font-size: 0.75rem; font-weight: 500; background: #ffffff; color: var(--zinc-600); border: 1px solid var(--zinc-200); cursor: pointer;">Metrics &amp; Cards</button>
            <button type="button" class="lib-cat-btn" data-cat="Standard Charts" onclick="filterCatalog('Standard Charts')" style="padding: 0.3rem 0.75rem; border-radius: var(--radius-full); font-size: 0.75rem; font-weight: 500; background: #ffffff; color: var(--zinc-600); border: 1px solid var(--zinc-200); cursor: pointer;">Standard Charts</button>
            <button type="button" class="lib-cat-btn" data-cat="Advanced & Geospatial" onclick="filterCatalog('Advanced & Geospatial')" style="padding: 0.3rem 0.75rem; border-radius: var(--radius-full); font-size: 0.75rem; font-weight: 500; background: #ffffff; color: var(--zinc-600); border: 1px solid var(--zinc-200); cursor: pointer;">Advanced &amp; Geospatial</button>
            <button type="button" class="lib-cat-btn" data-cat="Grids & Filters" onclick="filterCatalog('Grids & Filters')" style="padding: 0.3rem 0.75rem; border-radius: var(--radius-full); font-size: 0.75rem; font-weight: 500; background: #ffffff; color: var(--zinc-600); border: 1px solid var(--zinc-200); cursor: pointer;">Grids &amp; Filters</button>
        </div>

        <!-- Catalog Grid -->
        <div class="modal-body" style="max-height: 60vh; overflow-y: auto; padding: 1.25rem 1.5rem; flex: 1;">
            <div id="catalog-widgets-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 1rem;">
                <!-- 17 Widgets rendered dynamically -->
            </div>
        </div>

        <div class="modal-footer" style="display: flex; justify-content: flex-end; padding: 1rem 1.75rem; border-top: 1px solid var(--zinc-100);">
            <button type="button" onclick="closeWidgetLibraryModal()" class="btn btn-secondary">
                Close
            </button>
        </div>
    </div>
</div>

<!-- =========================================================================
     MODAL 2: INSERT NEW SECTION MODAL
     ========================================================================= -->
<div id="modal-insert-section" class="modal-overlay" style="display: none;" onclick="closeInsertSectionModal()">
    <div class="modal-content" style="max-width: 480px; width: 100%;" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <i data-lucide="folder-plus" style="width: 18px; height: 18px; color: #4f46e5;"></i>
                <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--zinc-950); margin: 0;">
                    Insert New Canvas Section
                </h3>
            </div>
            <button type="button" onclick="closeInsertSectionModal()" class="btn btn-ghost btn-sm">
                <i data-lucide="x" style="width: 16px; height: 16px;"></i>
            </button>
        </div>

        <div class="modal-body" style="padding: 1.25rem; display: flex; flex-direction: column; gap: 1rem;">
            <p style="font-size: 0.8rem; color: var(--zinc-600); margin: 0;">
                Divide this profile's dashboard into designated sections (e.g. "Pipeline Velocity", "Revenue &amp; Quotas", "Operations").
            </p>
            <div class="form-group" style="margin: 0;">
                <label class="form-label" style="font-weight: 600;">Section Title *</label>
                <input
                    type="text"
                    id="new-section-title-input"
                    class="form-input"
                    placeholder="e.g. Pipeline Health &amp; Conversion Telemetry"
                />
            </div>
            <div class="form-group" style="margin: 0;">
                <label class="form-label" style="font-weight: 600;">Section Subtitle (Optional)</label>
                <input
                    type="text"
                    id="new-section-subtitle-input"
                    class="form-input"
                    placeholder="e.g. Stage drop-offs, lead scores, and velocity benchmarks"
                />
            </div>
        </div>

        <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 0.5rem; padding: 1rem 1.25rem; border-top: 1px solid var(--zinc-100);">
            <button type="button" onclick="closeInsertSectionModal()" class="btn btn-secondary">
                Cancel
            </button>
            <button type="button" onclick="handleCreateSection()" class="btn btn-primary">
                Create Section
            </button>
        </div>
    </div>
</div>

<!-- =========================================================================
     MODAL 3: PROFILE-WISE DASHBOARDS ASSIGNMENT MATRIX
     ========================================================================= -->
<div id="modal-matrix" class="modal-overlay" style="display: none;" onclick="closeMatrixModal()">
    <div class="modal-content" style="max-width: 780px; width: 100%; max-height: 90vh; display: flex; flex-direction: column;" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <i data-lucide="users" style="width: 18px; height: 18px; color: #4f46e5;"></i>
                <div>
                    <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--zinc-950); margin: 0;">
                        Profile-Wise Dashboard Layouts &amp; Assignments
                    </h3>
                    <p style="font-size: 0.75rem; color: var(--zinc-500); margin: 0.15rem 0 0 0;">
                        Each security profile has its own independently configured 16-column dashboard page with sections.
                    </p>
                </div>
            </div>
            <button type="button" onclick="closeMatrixModal()" class="btn btn-ghost btn-sm">
                <i data-lucide="x" style="width: 16px; height: 16px;"></i>
            </button>
        </div>

        <div class="modal-body" style="max-height: 65vh; overflow-y: auto; padding: 1.25rem; flex: 1;">
            <div id="matrix-profiles-list" style="display: flex; flex-direction: column; gap: 0.85rem;">
                <!-- Profiles rendered dynamically -->
            </div>
        </div>

        <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 0.5rem; padding: 1rem 1.75rem; border-top: 1px solid var(--zinc-100);">
            <button type="button" onclick="closeMatrixModal()" class="btn btn-secondary">
                Close
            </button>
        </div>
    </div>
</div>

<!-- =========================================================================
     MODAL 4: GLOBAL SETTINGS & CONTROLS MODAL
     ========================================================================= -->
<div id="modal-settings" class="modal-overlay" style="display: none;" onclick="closeSettingsModal()">
    <div class="modal-content" style="max-width: 680px; width: 100%; max-height: 90vh; display: flex; flex-direction: column;" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <i data-lucide="calendar" style="width: 18px; height: 18px; color: #4f46e5;"></i>
                <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--zinc-950); margin: 0;">
                    Dashboard Global Settings &amp; Customization
                </h3>
            </div>
            <button type="button" onclick="closeSettingsModal()" class="btn btn-ghost btn-sm">
                <i data-lucide="x" style="width: 16px; height: 16px;"></i>
            </button>
        </div>

        <!-- Tabs Bar inside Settings Modal -->
        <div style="display: flex; gap: 4px; border-bottom: 1px solid var(--border-subtle); padding: 0 1.25rem; background: var(--zinc-50);">
            <button type="button" class="settings-subtab-btn" data-tab="filters" onclick="switchSettingsTab('filters')" style="padding: 0.65rem 0.85rem; font-size: 0.78rem; font-weight: 700; border: none; border-bottom: 2px solid var(--color-primary, #6366f1); background: transparent; color: var(--color-primary, #6366f1); cursor: pointer;">Filters &amp; Dates</button>
            <button type="button" class="settings-subtab-btn" data-tab="buttons" onclick="switchSettingsTab('buttons')" style="padding: 0.65rem 0.85rem; font-size: 0.78rem; font-weight: 500; border: none; border-bottom: 2px solid transparent; background: transparent; color: var(--zinc-600); cursor: pointer;">Header Buttons</button>
            <button type="button" class="settings-subtab-btn" data-tab="ai" onclick="switchSettingsTab('ai')" style="padding: 0.65rem 0.85rem; font-size: 0.78rem; font-weight: 500; border: none; border-bottom: 2px solid transparent; background: transparent; color: var(--zinc-600); cursor: pointer;">AI Deal Insights</button>
            <button type="button" class="settings-subtab-btn" data-tab="presets" onclick="switchSettingsTab('presets')" style="padding: 0.65rem 0.85rem; font-size: 0.78rem; font-weight: 500; border: none; border-bottom: 2px solid transparent; background: transparent; color: var(--zinc-600); cursor: pointer;">Layout Presets</button>
        </div>

        <div class="modal-body" style="display: flex; flex-direction: column; gap: 1.25rem; padding: 1.25rem; overflow-y: auto; flex: 1;">
            <!-- SUBTAB 1: FILTERS & DATES -->
            <div id="settings-tab-filters" style="display: flex; flex-direction: column; gap: 1rem;">
                <div style="padding: 0.85rem; border: 1px solid var(--border-subtle); border-radius: var(--radius-md); background: var(--zinc-50);">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                        <label style="font-weight: 600; font-size: 0.85rem; color: var(--zinc-900); cursor: pointer;">
                            Enable Universal Date Range Filtering
                        </label>
                        <input type="checkbox" checked style="cursor: pointer;" />
                    </div>
                    <p style="font-size: 0.75rem; color: var(--zinc-500); margin: 0;">
                        Enables preset date filters (Today, Yesterday, This Week, Last Week, This Month, Last Month, This Quarter, This Year, All Time).
                    </p>
                </div>

                <div style="padding: 0.85rem; border: 1px solid var(--border-subtle); border-radius: var(--radius-md); background: var(--zinc-50);">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                        <label style="font-weight: 600; font-size: 0.85rem; color: var(--zinc-900); cursor: pointer;">
                            Enable Secondary Date Basis Filter (Closed Date, Wire Date, Paid Date)
                        </label>
                        <input type="checkbox" checked style="cursor: pointer;" />
                    </div>
                    <p style="font-size: 0.75rem; color: var(--zinc-500); margin: 0;">
                        Allows users to anchor metrics by Deal Closed Date, Wire / Payment Received Date, or Invoice Paid Date.
                    </p>
                </div>

                <div class="form-group" style="margin: 0;">
                    <label class="form-label" style="font-weight: 600;">Default Date Preset for New Sessions</label>
                    <select class="form-select">
                        <option>This Month (Recommended)</option>
                        <option>This Quarter</option>
                        <option>This Year</option>
                        <option>All Time</option>
                    </select>
                </div>
            </div>

            <!-- SUBTAB 2: HEADER BUTTONS -->
            <div id="settings-tab-buttons" style="display: none; flex-direction: column; gap: 1rem;">
                <p style="font-size: 0.8rem; color: var(--zinc-600); margin: 0;">
                    Configure up to 6 fast action buttons displayed across the CRM Command Header.
                </p>
                <div style="padding: 0.85rem; background: var(--zinc-50); border-radius: var(--radius-md); border: 1px solid var(--border-subtle); font-size: 0.8125rem;">
                    Fast action buttons can be quickly added, removed and ordered directly in the Layout Designer canvas at the top of the page.
                </div>
            </div>

            <!-- SUBTAB 3: AI DEAL INSIGHTS -->
            <div id="settings-tab-ai" style="display: none; flex-direction: column; gap: 1rem;">
                <div style="padding: 0.85rem; border: 1px solid var(--border-subtle); border-radius: var(--radius-md); background: var(--zinc-50);">
                    <label style="font-weight: 700; font-size: 0.85rem; color: var(--zinc-900); display: block; margin-bottom: 0.5rem;">
                        AI Autonomous Deal Stagnation Detection
                    </label>
                    <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.8125rem;">
                        <span>Alert when deal stays inactive for more than</span>
                        <input type="number" value="9" min="1" max="60" style="width: 50px; padding: 2px 6px; border: 1px solid var(--zinc-300); border-radius: 4px;" />
                        <span>days in stage.</span>
                    </div>
                </div>
            </div>

            <!-- SUBTAB 4: PRESETS -->
            <div id="settings-tab-presets" style="display: none; flex-direction: column; gap: 1rem;">
                <p style="font-size: 0.78rem; color: var(--zinc-600); margin: 0;">
                    Save and load named dashboard layout presets across security profiles:
                </p>
                <div style="padding: 0.85rem; border: 1px solid var(--border-subtle); border-radius: var(--radius-md); background: var(--zinc-50);">
                    <label style="font-size: 0.8rem; font-weight: 700; color: var(--zinc-900); display: block; margin-bottom: 0.4rem;">
                        Save Current Layout as New Preset
                    </label>
                    <div style="display: flex; gap: 0.5rem;">
                        <input type="text" id="preset-name-input" placeholder="e.g. Q4 Sprint Push Layout" class="form-input" style="flex: 1; font-size: 0.78rem;" />
                        <button type="button" onclick="handleSavePreset()" class="btn btn-primary btn-sm">Save Preset</button>
                    </div>
                </div>
                <div>
                    <label style="font-size: 0.8rem; font-weight: 700; color: var(--zinc-900); display: block; margin-bottom: 0.4rem;">
                        Saved Preset Templates
                    </label>
                    <div style="display: flex; flex-direction: column; gap: 0.4rem;">
                        <div style="padding: 0.65rem 0.85rem; border: 1px solid var(--border-subtle); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: space-between;">
                            <span style="font-size: 0.8rem; font-weight: 600; color: var(--zinc-800);">Executive Revenue Cockpit <span style="font-size: 0.68rem; color: #4f46e5;">(Default)</span></span>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="showStudioToast('Preset Executive Revenue Cockpit applied!')">Apply Layout</button>
                        </div>
                        <div style="padding: 0.65rem 0.85rem; border: 1px solid var(--border-subtle); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: space-between;">
                            <span style="font-size: 0.8rem; font-weight: 600; color: var(--zinc-800);">Sales Rep High-Velocity Sprint</span>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="showStudioToast('Preset Sales Rep High-Velocity Sprint applied!')">Apply Layout</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 0.5rem; padding: 1rem 1.25rem; border-top: 1px solid var(--zinc-100);">
            <button type="button" onclick="closeSettingsModal()" class="btn btn-secondary">
                Cancel
            </button>
            <button type="button" onclick="closeSettingsModal(); showStudioToast('Dashboard settings published successfully!');" class="btn btn-primary">
                Save Settings
            </button>
        </div>
    </div>
</div>

<!-- =========================================================================
     MODAL 5: WIDGET CONFIGURATION MODAL (Edit Widget)
     ========================================================================= -->
<div id="modal-widget-config" class="modal-overlay" style="display: none;" onclick="closeWidgetConfigModal()">
    <div class="modal-content" style="max-width: 580px; width: 100%;" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <i data-lucide="calculator" style="width: 18px; height: 18px; color: #4f46e5;"></i>
                <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--zinc-950); margin: 0;">
                    Configure Widget Data &amp; Calculation
                </h3>
            </div>
            <button type="button" onclick="closeWidgetConfigModal()" class="btn btn-ghost btn-sm">
                <i data-lucide="x" style="width: 16px; height: 16px;"></i>
            </button>
        </div>

        <div class="modal-body" style="padding: 1.25rem; display: flex; flex-direction: column; gap: 1rem;">
            <input type="hidden" id="edit-widget-id" />
            <div class="form-group" style="margin: 0;">
                <label class="form-label" style="font-weight: 600;">Widget Title</label>
                <input type="text" id="edit-widget-title" class="form-input" />
            </div>
            <div class="form-group" style="margin: 0;">
                <label class="form-label" style="font-weight: 600;">Subtitle / Context</label>
                <input type="text" id="edit-widget-subtitle" class="form-input" />
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                <div class="form-group" style="margin: 0;">
                    <label class="form-label" style="font-weight: 600;">16-Col Span Width</label>
                    <select id="edit-widget-colspan" class="form-select">
                        <option value="4">4 cols (25%)</option>
                        <option value="6">6 cols (37.5%)</option>
                        <option value="8">8 cols (50%)</option>
                        <option value="10">10 cols (62.5%)</option>
                        <option value="12">12 cols (75%)</option>
                        <option value="14">14 cols (87.5%)</option>
                        <option value="16">16 cols (100%)</option>
                    </select>
                </div>
                <div class="form-group" style="margin: 0;">
                    <label class="form-label" style="font-weight: 600;">Color Palette</label>
                    <select id="edit-widget-palette" class="form-select">
                        <option value="violet">Electric Violet (#6366F1)</option>
                        <option value="cyan">Cyber Cyan (#06B6D4)</option>
                        <option value="emerald">Emerald Growth (#10B981)</option>
                        <option value="amber">Sunset Amber (#F59E0B)</option>
                        <option value="rose">Rose Flare (#F43F5E)</option>
                    </select>
                </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                <div class="form-group" style="margin: 0;">
                    <label class="form-label" style="font-weight: 600;">Module Source</label>
                    <select id="edit-widget-module" class="form-select">
                        <option value="opportunities">Opportunities (Deals)</option>
                        <option value="leads">Leads</option>
                        <option value="accounts">Accounts</option>
                        <option value="payments">Payments &amp; AR</option>
                    </select>
                </div>
                <div class="form-group" style="margin: 0;">
                    <label class="form-label" style="font-weight: 600;">Calculation Aggregation</label>
                    <select id="edit-widget-calc" class="form-select">
                        <option value="SUM">SUM (Total Value)</option>
                        <option value="COUNT">COUNT (Volume)</option>
                        <option value="AVG">AVG (Average)</option>
                        <option value="PERCENTAGE">PERCENTAGE (%)</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 0.5rem; padding: 1rem 1.25rem; border-top: 1px solid var(--zinc-100);">
            <button type="button" onclick="closeWidgetConfigModal()" class="btn btn-secondary">
                Cancel
            </button>
            <button type="button" onclick="handleSaveWidgetConfig()" class="btn btn-primary">
                Update Widget
            </button>
        </div>
    </div>
</div>

<!-- =========================================================================
     MODAL 6: SIDE PANEL (DRILLDOWN DRAWER) STUDIO CONFIG MODAL
     ========================================================================= -->
<div id="modal-sidepanel-config" class="modal-overlay" style="display: none;" onclick="closeSidePanelConfigModal()">
    <div class="modal-content" style="max-width: 580px; width: 100%;" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <i data-lucide="table" style="width: 18px; height: 18px; color: #4f46e5;"></i>
                <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--zinc-950); margin: 0;">
                    Raw Data Drilldown Popup Configurator
                </h3>
            </div>
            <button type="button" onclick="closeSidePanelConfigModal()" class="btn btn-ghost btn-sm">
                <i data-lucide="x" style="width: 16px; height: 16px;"></i>
            </button>
        </div>

        <div class="modal-body" style="padding: 1.25rem; display: flex; flex-direction: column; gap: 1rem;">
            <input type="hidden" id="sidepanel-widget-id" />
            <p style="font-size: 0.8rem; color: var(--zinc-600); margin: 0;">
                Configure the slide-over drawer that displays raw contributing records when users click this visualization.
            </p>
            <div style="padding: 0.85rem; border: 1px solid var(--border-subtle); border-radius: var(--radius-md); background: var(--zinc-50);">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <label style="font-weight: 700; font-size: 0.85rem; color: var(--zinc-900);">
                        Enable Click-to-Drilldown Popup
                    </label>
                    <input type="checkbox" id="sidepanel-enabled" checked style="cursor: pointer;" />
                </div>
            </div>
            <div class="form-group" style="margin: 0;">
                <label class="form-label" style="font-weight: 600;">Custom Popup Title</label>
                <input type="text" id="sidepanel-title" class="form-input" placeholder="e.g. Detailed Contributing Records" />
            </div>
            <div style="padding: 0.85rem; border: 1px solid var(--border-subtle); border-radius: var(--radius-md); background: #ffffff;">
                <label style="font-size: 0.8rem; font-weight: 700; color: var(--zinc-900); display: block; margin-bottom: 0.4rem;">
                    Included Raw Data Columns
                </label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; font-size: 0.75rem;">
                    <label style="display: flex; align-items: center; gap: 4px;"><input type="checkbox" checked /> Deal Name</label>
                    <label style="display: flex; align-items: center; gap: 4px;"><input type="checkbox" checked /> Amount / ARR</label>
                    <label style="display: flex; align-items: center; gap: 4px;"><input type="checkbox" checked /> Pipeline Stage</label>
                    <label style="display: flex; align-items: center; gap: 4px;"><input type="checkbox" checked /> Opportunity Owner</label>
                    <label style="display: flex; align-items: center; gap: 4px;"><input type="checkbox" checked /> Close Date</label>
                    <label style="display: flex; align-items: center; gap: 4px;"><input type="checkbox" checked /> Lead Source</label>
                </div>
            </div>
        </div>

        <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 0.5rem; padding: 1rem 1.25rem; border-top: 1px solid var(--zinc-100);">
            <button type="button" onclick="closeSidePanelConfigModal()" class="btn btn-secondary">
                Cancel
            </button>
            <button type="button" onclick="handleSaveSidePanelConfig()" class="btn btn-primary">
                Save Drilldown Setup
            </button>
        </div>
    </div>
</div>

<!-- =========================================================================
     SLIDE-OVER DRAWER: DRILLDOWN RECORDS (Live Preview Click)
     ========================================================================= -->
<div id="drawer-drilldown" class="modal-overlay" style="display: none; background: rgba(0,0,0,0.35); justify-content: flex-end;" onclick="closeDrilldownDrawer()">
    <div style="width: 520px; max-width: 100%; height: 100%; background: #ffffff; box-shadow: -4px 0 25px rgba(0,0,0,0.15); display: flex; flex-direction: column; overflow: hidden; animation: slideInRight 0.25s ease;" onclick="event.stopPropagation()">
        <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--zinc-200); display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
            <div>
                <span id="drawer-category-badge" style="font-size: 0.6875rem; font-weight: 700; color: #4f46e5; background: #e0e7ff; padding: 2px 8px; border-radius: 9999px;">
                    Telemetry Drilldown
                </span>
                <h3 id="drawer-title" style="font-size: 1.125rem; font-weight: 800; color: #0f172a; margin: 0.35rem 0 0 0;">
                    Contributing Deal Records
                </h3>
            </div>
            <button type="button" onclick="closeDrilldownDrawer()" class="btn btn-ghost btn-sm">
                <i data-lucide="x" style="width: 18px; height: 18px;"></i>
            </button>
        </div>

        <div style="padding: 1.25rem 1.5rem; overflow-y: auto; flex: 1; display: flex; flex-direction: column; gap: 1.25rem;">
            <!-- Summary Metric Card -->
            <div style="background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%); color: #ffffff; border-radius: 12px; padding: 1.25rem;">
                <span style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; letter-spacing: 0.05em; color: #c7d2fe;">Active Cohort Total</span>
                <div id="drawer-metric-val" style="font-size: 1.85rem; font-weight: 900; margin: 0.25rem 0;">$48,600,000</div>
                <p id="drawer-desc" style="font-size: 0.75rem; color: #e0e7ff; margin: 0;">Verified CRM production database records pacing toward annual targets.</p>
            </div>

            <!-- Records Table -->
            <div>
                <h4 style="font-size: 0.85rem; font-weight: 700; color: #0f172a; margin: 0 0 0.5rem 0;">
                    Contributing Enterprise Records (Top 5)
                </h4>
                <div style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.75rem; text-align: left;">
                        <thead style="background: #f1f5f9; border-bottom: 1px solid #cbd5e1;">
                            <tr>
                                <th style="padding: 6px 10px; font-weight: 700; color: #475569;">Deal / Account</th>
                                <th style="padding: 6px 10px; font-weight: 700; color: #475569;">ARR</th>
                                <th style="padding: 6px 10px; font-weight: 700; color: #475569;">Stage</th>
                                <th style="padding: 6px 10px; font-weight: 700; color: #475569;">Owner</th>
                            </tr>
                        </thead>
                        <tbody style="divide-y divide-slate-100;">
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 8px 10px; font-weight: 600; color: #0f172a;">Apex Global Cloud</td>
                                <td style="padding: 8px 10px; color: #059669; font-weight: 700;">$1,850,000</td>
                                <td style="padding: 8px 10px;"><span style="background: #dbeafe; color: #1e40af; padding: 1px 6px; border-radius: 4px; font-weight: 600;">Negotiation</span></td>
                                <td style="padding: 8px 10px; color: #475569;">Sarah Miller</td>
                            </tr>
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 8px 10px; font-weight: 600; color: #0f172a;">Vertex BioTech Hub</td>
                                <td style="padding: 8px 10px; color: #059669; font-weight: 700;">$940,000</td>
                                <td style="padding: 8px 10px;"><span style="background: #fef3c7; color: #92400e; padding: 1px 6px; border-radius: 4px; font-weight: 600;">Proposal</span></td>
                                <td style="padding: 8px 10px; color: #475569;">Alex Rivera</td>
                            </tr>
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 8px 10px; font-weight: 600; color: #0f172a;">OmniCorp Systems</td>
                                <td style="padding: 8px 10px; color: #059669; font-weight: 700;">$780,000</td>
                                <td style="padding: 8px 10px;"><span style="background: #dcfce7; color: #166534; padding: 1px 6px; border-radius: 4px; font-weight: 600;">Closed Won</span></td>
                                <td style="padding: 8px 10px; color: #475569;">David Vance</td>
                            </tr>
                            <tr>
                                <td style="padding: 8px 10px; font-weight: 600; color: #0f172a;">Helios Energy Corp</td>
                                <td style="padding: 8px 10px; color: #059669; font-weight: 700;">$620,000</td>
                                <td style="padding: 8px 10px;"><span style="background: #e0e7ff; color: #3730a3; padding: 1px 6px; border-radius: 4px; font-weight: 600;">Discovery</span></td>
                                <td style="padding: 8px 10px; color: #475569;">Elena Rostova</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--zinc-200); background: #f8fafc; display: flex; justify-content: flex-end;">
            <button type="button" onclick="closeDrilldownDrawer()" class="btn btn-secondary">
                Close Inspector
            </button>
        </div>
    </div>
</div>

<!-- =========================================================================
     JAVASCRIPT LOGIC & STATE ENGINE (100% Client-Side Static Parity)
     ========================================================================= -->
<script>
// Profiles List
const PROFILES = [
    { id: 'prof-admin', name: 'System Administrator', description: 'Unrestricted administration, settings, and full system visibility', is_admin: 1 },
    { id: 'prof-sales', name: 'Standard Sales Representative', description: 'Field and inside sales execution, opportunities, and quota tracking', is_admin: 0 },
    { id: 'prof-procurement', name: 'Procurement Specialist', description: 'Vendor, purchase contracts, and supply chain telemetry', is_admin: 0 },
    { id: 'prof-readonly', name: 'Read Only Profile', description: 'Auditor and read-only executive observation workspace', is_admin: 0 }
];

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
document.addEventListener('DOMContentLoaded', () => {
    // Attempt load from localStorage
    try {
        const savedSettings = localStorage.getItem('dealsync_dashboard_settings');
        if (savedSettings) {
            const parsed = JSON.parse(savedSettings);
            if (parsed.headerActions) state.headerActions = parsed.headerActions;
        }
        const savedLayouts = localStorage.getItem('dealsync_profile_layouts');
        if (savedLayouts) {
            state.profileLayouts = JSON.parse(savedLayouts);
        }
    } catch(e) {}

    renderAll();
});

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
    document.getElementById('profile-switcher-select').value = profId;
    renderAll();
    showStudioToast(`Switched canvas to ${PROFILES.find(p => p.id === profId)?.name}`);
}

function handleResetProfileLayout() {
    const prof = PROFILES.find(p => p.id === state.selectedProfileId);
    state.profileLayouts[state.selectedProfileId] = JSON.parse(JSON.stringify(DEFAULT_PROFILE_WIDGETS[state.selectedProfileId] || []));
    state.sections = JSON.parse(JSON.stringify(DEFAULT_SECTIONS));
    localStorage.setItem('dealsync_profile_layouts', JSON.stringify(state.profileLayouts));
    renderAll();
    showStudioToast(`Reset ${prof.name} to standard defaults!`);
}

function handleSaveAndPublish() {
    localStorage.setItem('dealsync_profile_layouts', JSON.stringify(state.profileLayouts));
    localStorage.setItem('dealsync_dashboard_settings', JSON.stringify({ headerActions: state.headerActions }));
    showStudioToast('Saved & Published Dashboard Canvas successfully across all users!');
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
                    <button type="button" onclick="handleProfileChange('${p.id}'); closeMatrixModal();" class="btn btn-sm ${isCurrent ? 'btn-primary' : 'btn-secondary'}" style="font-size: 0.75rem; font-weight: 600;">
                        ${isCurrent ? 'Continue Editing' : 'Edit This Profile'}
                    </button>
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
</script>
@endsection
