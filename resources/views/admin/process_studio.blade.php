@extends('layouts.app')

@section('title', 'ProcessStudio Automation Rules — DealSync Admin')

@section('content')
    <!-- Header Card -->
    <div class="card" style="padding: 1.5rem 2rem; margin-bottom: 1rem; border: 1px solid #fecdd3; border-radius: 14px;">
        <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
            <div
                style="width: 60px; height: 60px; border-radius: 14px; background: linear-gradient(135deg, #4f46e5, #0ea5e9); display: flex; align-items: center; justify-content: center; color: #fff;">
                <i data-lucide="workflow" style="width: 28px; height: 28px;"></i>
            </div>
            <div>
                <div style="display: flex; align-items: center; gap: 0.6rem; flex-wrap: wrap;">
                    <h1 style="font-size: 1.6rem; font-weight: 800; color: #2f5d9e; margin: 0;">ProcessStudio</h1>
                    <span class="badge badge-blue"
                        style="font-size: 0.75rem; font-weight: 700; border-radius: 999px;">Unified Business Process
                        Designer</span>
                </div>
                <p style="font-size: 0.9rem; color: var(--zinc-500); margin: 0.4rem 0 0 0;">
                    Design hybrid enterprise workflows seamlessly mixing human-in-the-loop interactive screens, conditional
                    logic, and zero-touch background automation.
                </p>
            </div>
        </div>
        <a href="{{ url('/process-studio') }}" class="btn btn-primary"
            style="margin-top: 1rem; display: inline-flex; align-items: center; gap: 0.4rem; text-decoration: none; background: #8e44ad; border-radius: 8px; padding: 0.55rem 1.1rem;">
            <i data-lucide="plus" style="width: 16px; height: 16px;"></i>
            <span>Create Business Process</span>
        </a>
    </div>

    <!-- Stats Cards -->
    <div
        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
        @foreach([
                ['cpu', '3', 'Total Business Processes', '#eef2ff'],
                ['layout-template', '2', 'Interactive Screen Flows (Human)', '#eff6ff'],
                ['zap', '1', '100% Background Automations', '#ecfdf5'],
                ['circle-check', '3', 'Active & Enforced Processes', '#ecfdf5'],
            ] as [$icon, $num, $label, $bg])
            <div class="card"
                style="padding: 1rem 1.25rem; display: flex; align-items: center; gap: 0.9rem; border: 1px solid #fed7aa; border-radius: 14px;">
                <div
                    style="width: 44px; height: 44px; border-radius: 10px; background: {{ $bg }}; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="{{ $icon }}" style="width: 20px; height: 20px;"></i>
                </div>
                <div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: var(--zinc-950); line-height: 1;">{{ $num }}</div>
                    <div style="font-size: 0.8rem; color: var(--zinc-700);">{{ $label }}</div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Filters Toolbar -->
    <div class="card"
        style="margin-bottom: 1rem; padding: 0.9rem 1.1rem; display: flex; gap: 0.75rem; align-items: center; justify-content: space-between; flex-wrap: wrap; border: 1px solid #fecdd3; border-radius: 14px;">
        <div style="display: flex; align-items: center; gap: 0.75rem; flex: 1; flex-wrap: wrap;">
            <select id="flt-module" onchange="applyFlowFilters()"
                style="height: 40px; padding: 0 0.9rem; border-radius: 8px; border: 1px solid var(--zinc-300); background: var(--bg-card); color: var(--text-primary); font-size: 0.9rem;">
                <option value="">All Modules</option>
                <option value="opportunities">Opportunities</option>
            </select>

            <select id="flt-trigger" onchange="applyFlowFilters()"
                style="height: 40px; padding: 0 0.9rem; border-radius: 8px; border: 1px solid var(--zinc-300); background: var(--bg-card); color: var(--text-primary); font-size: 0.9rem;">
                <option value="">All Triggers</option>
                <option value="button click">Button Click</option>
                <option value="record event">Record Event</option>
            </select>

            <div style="position: relative; flex: 1; max-width: 420px; min-width: 220px;">
                <i data-lucide="search"
                    style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); width: 15px; height: 15px; color: var(--zinc-400);"></i>
                <input type="text" id="flows-search-input" placeholder="Search business processes..."
                    oninput="applyFlowFilters()"
                    style="width: 100%; height: 40px; padding: 0 0.75rem 0 2.25rem; font-size: 0.9rem; border-radius: 8px; border: 1px solid var(--zinc-300); background: var(--bg-card); color: var(--text-primary);">
            </div>
        </div>

        <div style="display: flex; gap: 2px; background: #f5f3ff; padding: 3px; border-radius: 8px;">
            <button type="button" id="view-list-btn" onclick="setFlowView('list')"
                style="display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.35rem 0.8rem; border: 0; border-radius: 6px; background: #8e44ad; color: #fff; font-size: 0.85rem; cursor: pointer;">
                <i data-lucide="list" style="width: 14px; height: 14px;"></i> List
            </button>
            <button type="button" id="view-cards-btn" onclick="setFlowView('cards')"
                style="display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.35rem 0.8rem; border: 0; border-radius: 6px; background: transparent; color: #8e44ad; font-size: 0.85rem; cursor: pointer;">
                <i data-lucide="layout-grid" style="width: 14px; height: 14px;"></i> Cards
            </button>
        </div>
    </div>

    <!-- Process Flows Table -->
    <div class="card-table-wrapper" id="flows-list-view" style="border: 1px solid #fed7aa; border-radius: 14px;">
        <table class="data-table" id="process-flows-table">
            <thead>
                <tr>
                    <th style="min-width: 320px;">PROCESS NAME &amp; DESCRIPTION</th>
                    <th>TARGET OBJECT</th>
                    <th>TRIGGER ORIGIN</th>
                    <th>FLOW ARCHITECTURE</th>
                    <th>EXECUTION PARADIGM</th>
                </tr>
            </thead>
            <tbody>
                @forelse($flows as $f)
                    @php
                    $nodes = is_array($f->nodes) ? $f->nodes : (json_decode($f->nodes, true) ?? []);
                        $types = collect($nodes)->pluck('type');
                        $screens = $types->filter(fn($t) => str_contains($t, 'screen'))->count();
                        $decisions = $types->filter(fn($t) => str_contains($t, 'decision') || str_contains($t, 'condition'))->count();
                        $actions = max(count($nodes) - 1 - $screens - $decisions, 0);
                        $flowKey = (is_array($f->trigger_config) ? $f->trigger_config : (json_decode($f->trigger_config, true) ?? []))['flow_key'] ?? $f->id;
                        $trig = match ($f->trigger_type) { 'schedule' => 'Scheduled Timer', 'record_event' => 'Record Event', default => 'Button Click'};
                        $para = $screens > 0 ? 'Hybrid (Screen + Auto)' : '100% Background Automation';
                    @endphp
                    <tr class="flow-row" data-module="{{ strtolower($f->entity_type) }}" data-trigger="{{ strtolower($trig) }}">
                        <td>
                            <a href="{{ url('/process-studios?flow=' . $flowKey . '&id=' . $f->id) }}"
                                style="text-decoration: none; color: var(--zinc-950);">
                                <strong style="font-size: 0.9rem;">{{ $f->name }}</strong>
                            </a>
                            <div style="font-size: 0.8rem; color: var(--zinc-700); margin-top: 2px;">
                                {{ $f->description ?? $f->category }}</div>
                        </td>
                        <td><span class="badge badge-blue">{{ strtolower($f->entity_type) }}</span></td>
                        <td>{{ $trig }}</td>
                        <td>
                            <span class="badge badge-blue">{{ $screens }} Screens</span>
                            <span class="badge badge-yellow">{{ $decisions }} Decisions</span>
                            <span class="badge badge-green">{{ $actions }} Actions</span>
                        </td>
                        <td><span class="badge {{ $screens > 0 ? 'badge-indigo' : 'badge-green' }}">{{ $para }}</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align:center; padding: 1.5rem;">No process flows found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection