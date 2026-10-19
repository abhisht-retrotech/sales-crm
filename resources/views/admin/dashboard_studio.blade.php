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

/* Page Wrapper & Centered Studio Container (Exact React 1:1 Parity) */
.page-wrapper,
.ds-studio-root {
    max-width: 1240px !important;
    margin-left: auto !important;
    margin-right: auto !important;
    width: 100% !important;
    box-sizing: border-box !important;
}

@media (max-width: 768px) {
    .page-wrapper,
    .ds-studio-root {
        padding-left: 0.5rem !important;
        padding-right: 0.5rem !important;
    }
}
</style>

<div id="dsStudioRoot" class="page-wrapper max-w-[1240px] mx-auto w-full animate-fade-in ds-studio-root" style="max-width: 1240px; margin: 0 auto; width: 100%; display: flex; flex-direction: column; gap: 1.25rem; padding-bottom: 3rem;">
    {{-- 1. TOP STUDIO HEADER --}}
    @include('dashboard_studio.partials.top_header')

    {{-- TAB 1: CANVAS EDITOR VIEW --}}
    <div id="tab-content-editor" style="display: flex; flex-direction: column; gap: 1.5rem;">
        @include('dashboard_studio.partials.command_header_config')
        @include('dashboard_studio.partials.command_center_modules')
        @include('dashboard_studio.partials.canvas_sections')
    </div>

    {{-- TAB 2: LIVE PROFILE PREVIEW TAB --}}
    @include('dashboard_studio.partials.live_preview')
</div>

{{-- MODALS & DRAWERS --}}
@include('dashboard_studio.partials.modal_widget_library')
@include('dashboard_studio.partials.modal_section_insert')
@include('dashboard_studio.partials.modal_matrix_overview')
@include('dashboard_studio.partials.modal_settings')
@include('dashboard_studio.partials.modal_widget_config')
@include('dashboard_studio.partials.modal_side_panel')
@include('dashboard_studio.partials.drawer_drilldown')

{{-- Server hydration data for client state engine --}}
<script id="dsServerData" type="application/json">
{
    "profiles": @json($profiles ?? []),
    "savedLayouts": @json($savedLayouts ?? (object)[]),
    "savedSections": @json($savedSections ?? (object)[])
}
</script>
<script src="{{ asset('js/dashboard-studio.js') }}?v=2.0"></script>
@endsection
