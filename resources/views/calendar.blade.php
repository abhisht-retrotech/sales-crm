@extends('layouts.app')

@section('title', 'Calendar & Multi-Object Scheduling — DealSync Enterprise CRM')

@section('content')
<div class="calendar-workstation animate-fade-in" style="margin: -1rem -1rem 0 -1rem; height: calc(100vh - 66px); display: flex; flex-direction: column; overflow: hidden; background: #f8fafc; color: #0f172a;" class="dark:bg-zinc-950 dark:text-zinc-100">

    <!-- 1. TOP HEADER CONTROLS BAR (Clean Light Enterprise Aesthetic) -->
    <div style="display: flex; alignItems: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem; background: #ffffff; padding: 0.75rem 1.25rem; border-bottom: 1px solid var(--border-card, #e2e8f0); flex-shrink: 0;" class="dark:bg-zinc-900 dark:border-zinc-800">
        
        <!-- Left: Navigation & Date Title -->
        <div style="display: flex; align-items: center; gap: 0.85rem;">
            <!-- Reload Button -->
            <button
                type="button"
                onclick="refreshCalendar()"
                id="cal-refresh-btn"
                title="Refresh calendar events"
                style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0.45rem; cursor: pointer; display: flex; align-items: center; justify-content: center; color: #475569; transition: all 0.15s ease;"
                class="dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-300"
            >
                <i data-lucide="refresh-cw" id="cal-refresh-spinner" style="width: 15px; height: 15px;"></i>
            </button>

            <!-- View Switcher Dropdown [ 📅 ▾ ] exactly as per React CRM -->
            <div style="position: relative;">
                <button
                    type="button"
                    onclick="toggleViewDropdown()"
                    id="view-dropdown-trigger"
                    style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0.4rem 0.65rem; cursor: pointer; display: inline-flex; align-items: center; gap: 0.4rem; color: #0f172a; font-size: 0.825rem; font-weight: 600; box-shadow: 0 1px 2px rgba(0,0,0,0.03);"
                    class="dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100"
                >
                    <i data-lucide="calendar" style="width: 16px; height: 16px; color: #0284c7;"></i>
                    <span id="current-view-name" style="font-size: 0.8rem;">Month</span>
                    <i data-lucide="chevron-down" style="width: 14px; height: 14px; color: #64748b;"></i>
                </button>

                <!-- View Switcher Dropdown Popover -->
                <div
                    id="view-dropdown-menu"
                    style="display: none; position: absolute; top: 100%; left: 0; margin-top: 4px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); min-width: 130px; z-index: 200; overflow: hidden; padding: 4px 0;"
                    class="dark:bg-zinc-800 dark:border-zinc-700"
                >
                    @foreach(['month' => 'Month', 'week' => 'Week', 'day' => 'Day', 'table' => 'Table'] as $vKey => $vLabel)
                        <div
                            onclick="switchCalendarView('{{ $vKey }}', '{{ $vLabel }}')"
                            id="view-opt-{{ $vKey }}"
                            class="cal-view-option {{ $vKey === 'month' ? 'active' : '' }}"
                            style="padding: 0.5rem 1rem; font-size: 0.8125rem; cursor: pointer; display: flex; align-items: center; justify-content: space-between; color: {{ $vKey === 'month' ? '#0284c7' : '#334155' }}; background: {{ $vKey === 'month' ? '#f0f9ff' : 'transparent' }}; font-weight: {{ $vKey === 'month' ? '600' : '400' }};"
                        >
                            <span>{{ $vLabel }}</span>
                            <i data-lucide="check" id="check-{{ $vKey }}" style="width: 14px; height: 14px; color: #0284c7; display: {{ $vKey === 'month' ? 'inline' : 'none' }};"></i>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Previous / Today / Next Date Navigator -->
            <div style="display: inline-flex; align-items: center; border: 1px solid #cbd5e1; border-radius: 6px; background: #ffffff; overflow: hidden;" class="dark:bg-zinc-800 dark:border-zinc-700">
                <button
                    type="button"
                    onclick="navigatePeriod(-1)"
                    title="Previous period"
                    style="background: transparent; border: none; border-right: 1px solid #e2e8f0; padding: 0.35rem 0.55rem; cursor: pointer; color: #475569; display: flex; align-items: center;"
                    class="dark:border-zinc-700 dark:text-zinc-300"
                >
                    <i data-lucide="chevron-left" style="width: 16px; height: 16px;"></i>
                </button>
                <button
                    type="button"
                    onclick="navigateToday()"
                    style="background: transparent; border: none; border-right: 1px solid #e2e8f0; padding: 0.35rem 0.75rem; cursor: pointer; fontSize: 0.8rem; font-weight: 600; color: #334155;"
                    class="dark:border-zinc-700 dark:text-zinc-200"
                >
                    Today
                </button>
                <button
                    type="button"
                    onclick="navigatePeriod(1)"
                    title="Next period"
                    style="background: transparent; border: none; padding: 0.35rem 0.55rem; cursor: pointer; color: #475569; display: flex; align-items: center;"
                    class="dark:text-zinc-300"
                >
                    <i data-lucide="chevron-right" style="width: 16px; height: 16px;"></i>
                </button>
            </div>

            <!-- Current Period Header Title -->
            <h2 id="calendar-header-title" style="font-size: 1.15rem; font-weight: 700; margin: 0; color: #0f172a; letter-spacing: -0.01em;" class="dark:text-zinc-100">
                October 2026
            </h2>
        </div>

        <!-- Right: Search, Layers Filter & New Event Button -->
        <div style="display: flex; align-items: center; gap: 0.65rem; flex-wrap: wrap;">
            
            <!-- Quick Search Input -->
            <div style="position: relative;">
                <i data-lucide="search" style="position: absolute; left: 9px; top: 50%; transform: translateY(-50%); width: 14px; height: 14px; color: #94a3b8;"></i>
                <input
                    type="text"
                    id="cal-search-input"
                    oninput="filterCalendarBySearch(this.value)"
                    placeholder="Search events..."
                    style="padding-left: 28px; padding-right: 12px; padding-top: 0.38rem; padding-bottom: 0.38rem; font-size: 0.8rem; border-radius: 6px; border: 1px solid #cbd5e1; background: #ffffff; color: #0f172a; width: 160px;"
                    class="dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100"
                />
            </div>

            <!-- Active Layers Multi-Select Picklist Dropdown -->
            <div style="position: relative;">
                <button
                    type="button"
                    onclick="toggleLayersDropdown()"
                    id="layers-dropdown-trigger"
                    style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0.4rem 0.75rem; font-size: 0.8rem; font-weight: 600; color: #334155; cursor: pointer; display: inline-flex; align-items: center; gap: 0.45rem; box-shadow: 0 1px 2px rgba(0,0,0,0.03);"
                    class="dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-200"
                >
                    <i data-lucide="filter" style="width: 14px; height: 14px; color: #6366f1;"></i>
                    <span id="layers-label">Active Layers ({{ count($sources) }}/{{ count($sources) }})</span>
                    <i data-lucide="chevron-down" style="width: 14px; height: 14px; color: #94a3b8;"></i>
                </button>

                <!-- Layers Dropdown Popover -->
                <div
                    id="layers-dropdown-menu"
                    style="display: none; position: absolute; top: 100%; right: 0; margin-top: 4px; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 10px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1); width: 270px; z-index: 200; overflow: hidden;"
                    class="dark:bg-zinc-800 dark:border-zinc-700"
                >
                    <div style="padding: 0.65rem 0.85rem; border-bottom: 1px solid #e2e8f0; background: #f8fafc; display: flex; align-items: center; justify-content: space-between;" class="dark:bg-zinc-850 dark:border-zinc-700">
                        <span style="font-size: 0.75rem; font-weight: 700; color: #334155;" class="dark:text-zinc-200">
                            Calendar Object Layers
                        </span>
                        <div style="display: flex; gap: 0.4rem; font-size: 0.7rem;">
                            <button type="button" onclick="selectAllLayers()" style="background: none; border: none; color: #2563eb; cursor: pointer; padding: 0; font-weight: 600;">All</button>
                            <span style="color: #cbd5e1;">•</span>
                            <button type="button" onclick="clearAllLayers()" style="background: none; border: none; color: #64748b; cursor: pointer; padding: 0;">Clear</button>
                        </div>
                    </div>

                    <div style="max-height: 250px; overflow-y: auto; padding: 0.35rem;">
                        @foreach($sources as $src)
                            <div
                                onclick="toggleSingleLayer('{{ $src['id'] }}')"
                                style="display: flex; align-items: center; justify-content: space-between; padding: 0.4rem 0.55rem; border-radius: 6px; cursor: pointer; font-size: 0.775rem; color: #1e293b;"
                                class="dark:text-zinc-200 hover:bg-slate-50 dark:hover:bg-zinc-700"
                            >
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <input
                                        type="checkbox"
                                        id="chk-{{ $src['id'] }}"
                                        checked
                                        onclick="event.stopPropagation(); toggleSingleLayer('{{ $src['id'] }}')"
                                        style="width: 15px; height: 15px; accent-color: {{ $src['color_code'] }}; cursor: pointer;"
                                    />
                                    <span style="width: 9px; height: 9px; border-radius: 50%; background: {{ $src['color_code'] }}; flex-shrink: 0;"></span>
                                    <span>{{ $src['object_label'] }}</span>
                                </div>
                                <span style="font-size: 0.7rem; color: #64748b; background: #f1f5f9; padding: 1px 6px; border-radius: 10px;" class="dark:bg-zinc-700 dark:text-zinc-300">
                                    {{ $sourceCounts[$src['id']] ?? 0 }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- New Event / Meeting Button -->
            <button
                type="button"
                onclick="openNewEventModal()"
                style="background: #0284c7; color: #ffffff; border: none; border-radius: 6px; padding: 0.45rem 0.85rem; font-size: 0.8rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 0.4rem; box-shadow: 0 1px 2px rgba(2, 132, 199, 0.2);"
            >
                <i data-lucide="plus" style="width: 15px; height: 15px; stroke-width: 2.4;"></i>
                <span>New Event</span>
            </button>
        </div>
    </div>

    <!-- 2. MAIN CALENDAR VIEWPORT -->
    <div style="flex: 1; min-height: 0; background: #ffffff; overflow: hidden; display: flex; flex-direction: column;" class="dark:bg-zinc-900">

        <!-- VIEW A: MONTH VIEW (STRICT 7-COLUMN EQUAL WIDTH GRID) -->
        <div id="cal-view-month" style="display: flex; flex-direction: column; flex: 1; width: 100%; min-width: 0; overflow-y: auto;">
            <!-- Weekday Headers -->
            <div style="display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); width: 100%; border-bottom: 1px solid #e2e8f0; background: #f8fafc;" class="dark:bg-zinc-800/80 dark:border-zinc-700">
                @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $wDay)
                    <div style="padding: 0.55rem; text-align: center; font-size: 0.75rem; font-weight: 700; color: #475569;" class="dark:text-zinc-300">
                        {{ $wDay }}
                    </div>
                @endforeach
            </div>

            <!-- Month Calendar Grid (35 cells: Sep 27 to Oct 31) -->
            <div id="month-grid-cells" style="display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); width: 100%; flex: 1;">
                <!-- Dynamically populated by JS for exact date handling -->
            </div>
        </div>

        <!-- VIEW B: WEEK VIEW (7 DAYS HOURLY GRID 8 AM - 6 PM) -->
        <div id="cal-view-week" style="display: none; flex-direction: column; flex: 1; overflow-y: auto; width: 100%; min-width: 0;">
            <!-- Week Day Headers Row -->
            <div style="display: grid; grid-template-columns: 70px repeat(7, minmax(0, 1fr)); width: 100%; border-bottom: 1px solid #e2e8f0; background: #f8fafc; position: sticky; top: 0; z-index: 10;" class="dark:bg-zinc-800/90 dark:border-zinc-700">
                <div style="padding: 0.6rem; text-align: center; font-size: 0.725rem; color: #64748b; font-weight: 600; border-right: 1px solid #e2e8f0;" class="dark:border-zinc-700">
                    Time
                </div>
                @foreach([['Sun', '11'], ['Mon', '12'], ['Tue', '13'], ['Wed', '14'], ['Thu', '15', true], ['Fri', '16'], ['Sat', '17']] as $col)
                    <div style="padding: 0.5rem; text-align: center; border-right: 1px solid #e2e8f0; background: {{ !empty($col[2]) ? '#eff6ff' : 'transparent' }};" class="dark:border-zinc-700">
                        <div style="font-size: 0.725rem; color: #64748b; font-weight: 600;">{{ $col[0] }}</div>
                        <div style="font-size: 0.9rem; font-weight: 700; color: {{ !empty($col[2]) ? '#2563eb' : '#0f172a' }};" class="dark:text-zinc-100">
                            {{ $col[1] }}
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Week Hourly Rows -->
            <div id="week-grid-rows" style="display: flex; flex-direction: column; flex: 1; width: 100%; min-width: 0;">
                <!-- Populated by JS -->
            </div>
        </div>

        <!-- VIEW C: DAY VIEW (HOURLY TIMELINE 8 AM - 7 PM) -->
        <div id="cal-view-day" style="display: none; flex-direction: column; flex: 1; overflow-y: auto;">
            <div id="day-grid-rows" style="display: flex; flex-direction: column; flex: 1;">
                <!-- Populated by JS -->
            </div>
        </div>

        <!-- VIEW D: TABLE / AGENDA VIEW -->
        <div id="cal-view-table" style="display: none; flex-direction: column; flex: 1; overflow-y: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.8rem;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; color: #475569; font-weight: 600;" class="dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-300">
                        <th style="padding: 0.65rem 1rem;">Date &amp; Time</th>
                        <th style="padding: 0.65rem 1rem;">Event Title</th>
                        <th style="padding: 0.65rem 1rem;">Source Layer</th>
                        <th style="padding: 0.65rem 1rem;">Linked CRM Record</th>
                        <th style="padding: 0.65rem 1rem;">Location / Details</th>
                        <th style="padding: 0.65rem 1rem; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody id="cal-table-tbody">
                    <!-- Populated by JS -->
                </tbody>
            </table>
        </div>

    </div>

    <!-- 3. EVENT DETAIL MODAL (Light / Dark Adaptive) -->
    <div
        id="cal-detail-modal-overlay"
        style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(3px); z-index: 10000; align-items: center; justify-content: center; padding: 1.25rem;"
    >
        <div
            style="max-width: 480px; width: 100%; max-height: 88vh; background: #ffffff; color: #0f172a; border: 1px solid #e2e8f0; border-radius: 12px; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.15);"
            class="dark:bg-zinc-900 dark:border-zinc-700 dark:text-zinc-100"
        >
            <!-- Modal Header -->
            <div id="cal-detail-header" style="padding: 1rem 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; align-items: flex-start; justify-content: space-between; background: rgba(139, 92, 246, 0.08); flex-shrink: 0;">
                <div style="display: flex; align-items: center; gap: 0.65rem;">
                    <div id="cal-detail-icon-box" style="width: 32px; height: 32px; border-radius: 8px; background: rgba(139, 92, 246, 0.2); color: #8b5cf6; display: flex; align-items: center; justify-content: center;">
                        <i data-lucide="video" id="cal-detail-icon" style="width: 16px; height: 16px;"></i>
                    </div>
                    <div>
                        <span id="cal-detail-source-label" style="font-size: 0.7rem; font-weight: 700; color: #8b5cf6; text-transform: uppercase; letter-spacing: 0.05em;">
                            Meetings &amp; Demos
                        </span>
                        <h3 id="cal-detail-title" style="font-size: 1.05rem; font-weight: 700; margin: 2px 0 0 0; color: #0f172a;" class="dark:text-zinc-100">
                            Technical Architecture Committee Demo
                        </h3>
                    </div>
                </div>
                <button type="button" onclick="closeEventDetailModal()" style="background: transparent; border: none; color: #94a3b8; cursor: pointer; padding: 4px;">
                    <i data-lucide="x" style="width: 18px; height: 18px;"></i>
                </button>
            </div>

            <!-- Modal Body Details -->
            <div style="padding: 1.25rem; display: flex; flex-direction: column; gap: 0.85rem; font-size: 0.825rem; overflow-y: auto; flex: 1; min-height: 0;">
                <!-- Date/Time -->
                <div style="display: flex; align-items: center; gap: 0.5rem; color: #334155;" class="dark:text-zinc-300">
                    <i data-lucide="clock" style="width: 15px; height: 15px; color: #64748b; flex-shrink: 0;"></i>
                    <span id="cal-detail-datetime">Thursday, Oct 15, 2026 • 2:00 PM – 3:00 PM</span>
                </div>

                <!-- Video / URL -->
                <div id="cal-detail-location-row" style="display: flex; align-items: center; gap: 0.5rem; color: #334155;" class="dark:text-zinc-300">
                    <i data-lucide="map-pin" style="width: 15px; height: 15px; color: #64748b; flex-shrink: 0;"></i>
                    <span id="cal-detail-location">https://meet.google.com/dsk-qmwe-opt</span>
                </div>

                <!-- Linked CRM Record -->
                <div id="cal-detail-linked-row" style="display: flex; align-items: center; gap: 0.5rem; background: #f8fafc; padding: 0.5rem 0.75rem; border-radius: 6px; border: 1px solid #e2e8f0;" class="dark:bg-zinc-800 dark:border-zinc-700">
                    <i data-lucide="briefcase" style="width: 15px; height: 15px; color: #64748b; flex-shrink: 0;"></i>
                    <span style="color: #64748b;">Linked Record:</span>
                    <strong id="cal-detail-linked-name" style="color: #0f172a;" class="dark:text-zinc-100">Sarah Jenkins (Acme Corporation)</strong>
                </div>

                <!-- Attendees -->
                <div id="cal-detail-attendees-row" style="display: flex; flex-direction: column; gap: 0.35rem;">
                    <div style="display: flex; align-items: center; gap: 0.4rem; color: #64748b;">
                        <i data-lucide="users" style="width: 14px; height: 14px;"></i>
                        <span>Attendees:</span>
                    </div>
                    <div id="cal-detail-attendees-list" style="display: flex; flex-wrap: wrap; gap: 0.3rem; margin-left: 1.25rem;">
                        <!-- Injected -->
                    </div>
                </div>

                <!-- Description -->
                <div id="cal-detail-desc" style="padding: 0.65rem; background: #f8fafc; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 0.8rem; line-height: 1.5; color: #475569;" class="dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-300">
                    Review single-tenant database isolation, SOC2 Type II compliance pack, and legacy Salesforce migration pathways.
                </div>
            </div>

            <!-- Modal Footer -->
            <div style="padding: 0.75rem 1.25rem; background: #fafafa; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;" class="dark:bg-zinc-800 dark:border-zinc-700">
                <button type="button" onclick="deleteCurrentEvent()" class="btn btn-ghost btn-sm" style="color: #ef4444; font-size: 0.75rem;">
                    <i data-lucide="trash-2" style="width: 13px; height: 13px;"></i>
                    <span>Remove Event</span>
                </button>
                <button type="button" onclick="closeEventDetailModal()" class="btn btn-secondary btn-sm" style="font-size: 0.75rem;">
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- 4. NEW EVENT / MEETING MODAL -->
    <div
        id="cal-new-modal-overlay"
        style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(3px); z-index: 10001; align-items: center; justify-content: center; padding: 1.25rem;"
    >
        <div
            style="max-width: 540px; width: 100%; max-height: 90vh; background: #ffffff; border-radius: 12px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); display: flex; flex-direction: column; overflow: hidden;"
            class="dark:bg-zinc-900 dark:text-zinc-100"
        >
            <div style="padding: 0.85rem 1.25rem; border-bottom: 1px solid #e2e8f0; background: #fafafa; display: flex; justify-content: space-between; align-items: center;" class="dark:bg-zinc-800 dark:border-zinc-700">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <i data-lucide="calendar" style="width: 17px; height: 17px; color: #0284c7;"></i>
                    <h3 style="margin: 0; font-size: 0.95rem; font-weight: 700; color: #0f172a;" class="dark:text-zinc-100">Schedule New Event / Meeting</h3>
                </div>
                <button type="button" onclick="closeNewEventModal()" class="btn btn-ghost btn-sm">
                    <i data-lucide="x" style="width: 16px; height: 16px;"></i>
                </button>
            </div>

            <form onsubmit="handleCreateEvent(event)" style="display: flex; flex-direction: column; flex: 1; min-height: 0;">
                <div style="padding: 1.25rem; display: flex; flex-direction: column; gap: 0.85rem; overflow-y: auto; flex: 1;">
                    <div>
                        <label class="form-label" style="font-size: 0.75rem; font-weight: 600;">Event Title *</label>
                        <input type="text" id="new-evt-title" required placeholder="e.g. Solution Discovery Alignment Sync" class="form-input" style="width: 100%; height: 32px;" />
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                        <div>
                            <label class="form-label" style="font-size: 0.75rem; font-weight: 600;">Event Type</label>
                            <select id="new-evt-type" class="form-input" style="width: 100%; height: 32px;">
                                <option value="cal-src-meetings">Meeting / Demo</option>
                                <option value="cal-src-tasks">CRM Task Deadline</option>
                                <option value="cal-src-deals">Opportunity Target Close</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label" style="font-size: 0.75rem; font-weight: 600;">Date</label>
                            <input type="date" id="new-evt-date" required value="2026-10-15" class="form-input" style="width: 100%; height: 32px;" />
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                        <div>
                            <label class="form-label" style="font-size: 0.75rem; font-weight: 600;">Start Time</label>
                            <input type="time" id="new-evt-start" value="10:00" class="form-input" style="width: 100%; height: 32px;" />
                        </div>
                        <div>
                            <label class="form-label" style="font-size: 0.75rem; font-weight: 600;">End Time</label>
                            <input type="time" id="new-evt-end" value="10:45" class="form-input" style="width: 100%; height: 32px;" />
                        </div>
                    </div>

                    <div>
                        <label class="form-label" style="font-size: 0.75rem; font-weight: 600;">Meeting Location / Video URL</label>
                        <input type="text" id="new-evt-location" value="https://meet.google.com/new" class="form-input" style="width: 100%; height: 32px;" />
                    </div>

                    <div>
                        <label class="form-label" style="font-size: 0.75rem; font-weight: 600;">Link to CRM Entity</label>
                        <input type="text" id="new-evt-linked" placeholder="e.g. Acme Corporation (Lead)" class="form-input" style="width: 100%; height: 32px;" />
                    </div>

                    <div>
                        <label class="form-label" style="font-size: 0.75rem; font-weight: 600;">Attendees (Comma Separated)</label>
                        <input type="text" id="new-evt-attendees" placeholder="alex@dealsync.app, client@example.com" class="form-input" style="width: 100%; height: 32px;" />
                    </div>

                    <div>
                        <label class="form-label" style="font-size: 0.75rem; font-weight: 600;">Notes &amp; Agenda</label>
                        <textarea id="new-evt-notes" rows="2" class="form-input" placeholder="Topics for discussion..." style="width: 100%;"></textarea>
                    </div>
                </div>

                <div style="padding: 0.75rem 1.25rem; background: #fafafa; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 0.5rem;" class="dark:bg-zinc-800 dark:border-zinc-700">
                    <button type="button" onclick="closeNewEventModal()" class="btn btn-secondary btn-sm">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm" style="background: #0284c7; border-color: #0284c7;">Create Event</button>
                </div>
            </form>
        </div>
    </div>

</div>

<!-- JAVASCRIPT FOR REACTIVE CALENDAR (PARITY WITH CalendarView.tsx) -->
<script>
    const rawCalendarEvents = @json($events);
    const calendarSources = @json($sources);
    let activeCalendarEvents = [...rawCalendarEvents];

    let currentCalView = 'month';
    let currentSelectedEventId = null;
    let activeLayersSet = new Set(calendarSources.map(s => s.id));
    let calSearchQuery = '';

    // Month dates for October 2026
    let currentYear = 2026;
    let currentMonth = 9; // 0-indexed: 9 = October

    document.addEventListener('DOMContentLoaded', () => {
        renderCalendar();
        if (typeof lucide !== 'undefined') lucide.createIcons();
    });

    // Main Render Function
    function renderCalendar() {
        if (currentCalView === 'month') {
            renderMonthView();
        } else if (currentCalView === 'week') {
            renderWeekView();
        } else if (currentCalView === 'day') {
            renderDayView();
        } else if (currentCalView === 'table') {
            renderTableView();
        }
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    // Filter events by active layers and search query
    function getFilteredEvents() {
        return activeCalendarEvents.filter(evt => {
            if (!activeLayersSet.has(evt.source_id)) return false;
            if (calSearchQuery) {
                const q = calSearchQuery.toLowerCase();
                const mTitle = (evt.title || '').toLowerCase().includes(q);
                const mSubtitle = (evt.subtitle || '').toLowerCase().includes(q);
                const mRelated = (evt.related_entity_name || '').toLowerCase().includes(q);
                if (!mTitle && !mSubtitle && !mRelated) return false;
            }
            return true;
        });
    }

    // 1. Render Month Grid
    function renderMonthView() {
        const container = document.getElementById('month-grid-cells');
        if (!container) return;
        container.innerHTML = '';

        const filtered = getFilteredEvents();

        // October 2026 starts on Thursday (index 4) and has 31 days
        // We render 35 cells: Sep 27..30 (4 days) + Oct 1..31 (31 days)
        const days = [];
        // Spill days from September
        for (let d = 27; d <= 30; d++) {
            days.push({ day: d, isCurrentMonth: false, dateStr: `2026-09-${d}` });
        }
        // October days
        for (let d = 1; d <= 31; d++) {
            const pad = d < 10 ? '0' + d : d;
            days.push({ day: d, isCurrentMonth: true, isToday: (d === 15), dateStr: `2026-10-${pad}` });
        }

        days.forEach((cell, idx) => {
            const cellEvents = filtered.filter(e => e.date === cell.dateStr);

            const cellDiv = document.createElement('div');
            cellDiv.style.cssText = `
                border-right: ${(idx + 1) % 7 === 0 ? 'none' : '1px solid #f1f5f9'};
                border-bottom: 1px solid #f1f5f9;
                padding: 0.45rem;
                background: ${cell.isToday ? '#eff6ff' : (cell.isCurrentMonth ? '#ffffff' : '#fafafa')};
                display: flex;
                flex-direction: column;
                min-height: 105px;
                min-width: 0;
                width: 100%;
                box-sizing: border-box;
                overflow: hidden;
            `;

            // Day Number Row
            let dayHeaderHtml = `
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.3rem;">
                    <span style="font-size: 0.75rem; font-weight: ${cell.isToday ? '700' : (cell.isCurrentMonth ? '600' : '400')}; color: ${cell.isToday ? '#ffffff' : (cell.isCurrentMonth ? '#1e293b' : '#94a3b8')}; background: ${cell.isToday ? '#2563eb' : 'transparent'}; width: ${cell.isToday ? '22px' : 'auto'}; height: ${cell.isToday ? '22px' : 'auto'}; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                        ${cell.day}
                    </span>
                    <button type="button" onclick="openNewEventModal('${cell.dateStr}')" style="background: transparent; border: none; color: #94a3b8; cursor: pointer; padding: 1px; opacity: 0.7;" title="Schedule on this day">
                        <i data-lucide="plus" style="width: 12px; height: 12px;"></i>
                    </button>
                </div>
            `;

            // Event Chips
            let chipsHtml = `<div style="display: flex; flex-direction: column; gap: 3px; flex: 1; min-width: 0; width: 100%; overflow: hidden;">`;
            cellEvents.slice(0, 3).forEach(evt => {
                chipsHtml += `
                    <div
                        onclick="showEventDetail('${evt.id}')"
                        title="${evt.title} (${evt.source_label})"
                        style="font-size: 0.7rem; padding: 0.2rem 0.4rem; border-radius: 4px; background: ${evt.color_code}14; border: 1px solid ${evt.color_code}30; border-left: 3px solid ${evt.color_code}; color: #0f172a; display: flex; align-items: center; gap: 0.35rem; cursor: pointer; min-width: 0; width: 100%; box-sizing: border-box; overflow: hidden; line-height: 1.25; font-weight: 500;"
                    >
                        <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; min-width: 0; flex: 1;">
                            ${evt.title}
                        </span>
                    </div>
                `;
            });

            if (cellEvents.length > 3) {
                chipsHtml += `
                    <div onclick="switchCalendarView('day', 'Day')" style="font-size: 0.675rem; color: #0284c7; cursor: pointer; font-weight: 700; padding: 1px 3px;">
                        +${cellEvents.length - 3} more...
                    </div>
                `;
            }
            chipsHtml += `</div>`;

            cellDiv.innerHTML = dayHeaderHtml + chipsHtml;
            container.appendChild(cellDiv);
        });
    }

    // 2. Render Week View
    function renderWeekView() {
        const container = document.getElementById('week-grid-rows');
        if (!container) return;
        container.innerHTML = '';

        const filtered = getFilteredEvents();
        const hours = [8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18];
        const weekDates = ['2026-10-11', '2026-10-12', '2026-10-13', '2026-10-14', '2026-10-15', '2026-10-16', '2026-10-17'];

        hours.forEach(hour => {
            const hourLabel = `${hour % 12 === 0 ? 12 : hour % 12} ${hour >= 12 ? 'PM' : 'AM'}`;
            const rowDiv = document.createElement('div');
            rowDiv.style.cssText = 'display: grid; grid-template-columns: 70px repeat(7, minmax(0, 1fr)); width: 100%; min-height: 52px; border-bottom: 1px solid #f1f5f9;';

            let rowHtml = `<div style="padding: 0.35rem 0.5rem; text-align: right; font-size: 0.7rem; color: #94a3b8; border-right: 1px solid #e2e8f0; flex-shrink: 0;">${hourLabel}</div>`;

            weekDates.forEach(dateStr => {
                const hourEvents = filtered.filter(e => {
                    if (e.date !== dateStr) return false;
                    const h = new Date(e.start_time).getHours();
                    return h === hour;
                });

                let cellContent = '';
                hourEvents.forEach(evt => {
                    cellContent += `
                        <div onclick="showEventDetail('${evt.id}')" style="font-size: 0.7rem; padding: 0.2rem 0.4rem; border-radius: 4px; background: ${evt.color_code}20; border-left: 3px solid ${evt.color_code}; color: #0f172a; margin-bottom: 2px; cursor: pointer; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-weight: 600;">
                            ${evt.title}
                        </div>
                    `;
                });

                rowHtml += `<div style="border-right: 1px solid #f1f5f9; padding: 2px 4px; overflow: hidden;">${cellContent}</div>`;
            });

            rowDiv.innerHTML = rowHtml;
            container.appendChild(rowDiv);
        });
    }

    // 3. Render Day View
    function renderDayView() {
        const container = document.getElementById('day-grid-rows');
        if (!container) return;
        container.innerHTML = '';

        const filtered = getFilteredEvents().filter(e => e.date === '2026-10-15');
        const hours = [8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18];

        hours.forEach(hour => {
            const hourLabel = `${hour % 12 === 0 ? 12 : hour % 12} ${hour >= 12 ? 'PM' : 'AM'}`;
            const rowDiv = document.createElement('div');
            rowDiv.style.cssText = 'display: grid; grid-template-columns: 80px 1fr; min-height: 60px; border-bottom: 1px solid #f1f5f9;';

            const hourEvents = filtered.filter(e => new Date(e.start_time).getHours() === hour);
            let eventsHtml = '';
            hourEvents.forEach(evt => {
                eventsHtml += `
                    <div onclick="showEventDetail('${evt.id}')" style="padding: 0.5rem 0.85rem; border-radius: 6px; background: ${evt.color_code}15; border-left: 4px solid ${evt.color_code}; margin-bottom: 4px; cursor: pointer;">
                        <div style="font-weight: 700; font-size: 0.85rem; color: #0f172a;">${evt.title}</div>
                        <div style="font-size: 0.75rem; color: #64748b;">${evt.subtitle || ''} • ${evt.related_entity_name || ''}</div>
                    </div>
                `;
            });

            rowDiv.innerHTML = `
                <div style="padding: 0.5rem; text-align: right; font-size: 0.725rem; color: #94a3b8; border-right: 1px solid #e2e8f0;">${hourLabel}</div>
                <div style="padding: 0.4rem 0.75rem;">${eventsHtml}</div>
            `;
            container.appendChild(rowDiv);
        });
    }

    // 4. Render Table View
    function renderTableView() {
        const tbody = document.getElementById('cal-table-tbody');
        if (!tbody) return;
        const filtered = getFilteredEvents();

        if (filtered.length === 0) {
            tbody.innerHTML = `<tr><td colspan="6" style="padding: 3rem; text-align: center; color: #94a3b8;">No events found matching active filters.</td></tr>`;
            return;
        }

        tbody.innerHTML = filtered.map(evt => `
            <tr onclick="showEventDetail('${evt.id}')" style="border-bottom: 1px solid #f1f5f9; cursor: pointer;" class="hover:bg-slate-50 dark:hover:bg-zinc-800">
                <td style="padding: 0.65rem 1rem; color: #334155; white-space: nowrap;">
                    <div style="font-weight: 600;">${evt.date}</div>
                    <div style="font-size: 0.725rem; color: #94a3b8;">${evt.is_all_day ? 'All-Day' : (evt.start_time ? new Date(evt.start_time).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : '')}</div>
                </td>
                <td style="padding: 0.65rem 1rem;">
                    <div style="font-weight: 600; color: #0f172a;">${evt.title}</div>
                    <div style="font-size: 0.725rem; color: #64748b;">${evt.description || ''}</div>
                </td>
                <td style="padding: 0.65rem 1rem;">
                    <span style="font-size: 0.725rem; font-weight: 600; padding: 0.2rem 0.55rem; border-radius: 12px; background: ${evt.color_code}15; color: ${evt.color_code}; border: 1px solid ${evt.color_code}30; display: inline-flex; align-items: center; gap: 0.3rem;">
                        <span style="width: 6px; height: 6px; border-radius: 50%; background: ${evt.color_code};"></span>
                        ${evt.source_label}
                    </span>
                </td>
                <td style="padding: 0.65rem 1rem; color: #2563eb; font-weight: 500;">
                    ${evt.related_entity_name ? `🔗 ${evt.related_entity_name}` : '—'}
                </td>
                <td style="padding: 0.65rem 1rem; color: #64748b; font-size: 0.75rem;">
                    ${evt.subtitle || '—'}
                </td>
                <td style="padding: 0.65rem 1rem; text-align: right;">
                    <button type="button" onclick="event.stopPropagation(); showEventDetail('${evt.id}')" class="btn btn-secondary btn-sm" style="font-size: 0.725rem; padding: 0.2rem 0.5rem;">
                        Details
                    </button>
                </td>
            </tr>
        `).join('');
    }

    // Switch Calendar View
    function switchCalendarView(viewKey, label) {
        currentCalView = viewKey;
        document.getElementById('current-view-name').textContent = label;
        document.getElementById('view-dropdown-menu').style.display = 'none';

        // Toggle active styles in dropdown
        document.querySelectorAll('.cal-view-option').forEach(el => {
            el.style.color = '#334155';
            el.style.background = 'transparent';
            el.style.fontWeight = '400';
        });
        const activeOpt = document.getElementById('view-opt-' + viewKey);
        if (activeOpt) {
            activeOpt.style.color = '#0284c7';
            activeOpt.style.background = '#f0f9ff';
            activeOpt.style.fontWeight = '600';
        }
        document.querySelectorAll('[id^="check-"]').forEach(chk => chk.style.display = 'none');
        const chkIcon = document.getElementById('check-' + viewKey);
        if (chkIcon) chkIcon.style.display = 'inline';

        // Toggle Views
        ['month', 'week', 'day', 'table'].forEach(v => {
            const el = document.getElementById('cal-view-' + v);
            if (el) el.style.display = (v === viewKey) ? 'flex' : 'none';
        });

        renderCalendar();
    }

    // View Dropdown Toggle
    function toggleViewDropdown() {
        const menu = document.getElementById('view-dropdown-menu');
        menu.style.display = (menu.style.display === 'none') ? 'block' : 'none';
    }

    // Layers Dropdown Toggle
    function toggleLayersDropdown() {
        const menu = document.getElementById('layers-dropdown-menu');
        menu.style.display = (menu.style.display === 'none') ? 'block' : 'none';
    }

    function toggleSingleLayer(srcId) {
        if (activeLayersSet.has(srcId)) {
            activeLayersSet.delete(srcId);
            const chk = document.getElementById('chk-' + srcId);
            if (chk) chk.checked = false;
        } else {
            activeLayersSet.add(srcId);
            const chk = document.getElementById('chk-' + srcId);
            if (chk) chk.checked = true;
        }
        updateLayersLabel();
        renderCalendar();
    }

    function selectAllLayers() {
        calendarSources.forEach(s => {
            activeLayersSet.add(s.id);
            const chk = document.getElementById('chk-' + s.id);
            if (chk) chk.checked = true;
        });
        updateLayersLabel();
        renderCalendar();
    }

    function clearAllLayers() {
        activeLayersSet.clear();
        calendarSources.forEach(s => {
            const chk = document.getElementById('chk-' + s.id);
            if (chk) chk.checked = false;
        });
        updateLayersLabel();
        renderCalendar();
    }

    function updateLayersLabel() {
        document.getElementById('layers-label').textContent = `Active Layers (${activeLayersSet.size}/${calendarSources.length})`;
    }

    function filterCalendarBySearch(val) {
        calSearchQuery = val.trim();
        renderCalendar();
    }

    // Date Navigation
    function navigatePeriod(step) {
        if (step > 0) {
            document.getElementById('calendar-header-title').textContent = 'November 2026';
        } else {
            document.getElementById('calendar-header-title').textContent = 'September 2026';
        }
        renderCalendar();
    }

    function navigateToday() {
        document.getElementById('calendar-header-title').textContent = 'October 2026';
        renderCalendar();
    }

    function refreshCalendar() {
        const spinner = document.getElementById('cal-refresh-spinner');
        if (spinner) spinner.classList.add('animate-spin');
        setTimeout(() => {
            if (spinner) spinner.classList.remove('animate-spin');
            renderCalendar();
        }, 600);
    }

    // Show Event Detail Modal
    function showEventDetail(id) {
        const evt = activeCalendarEvents.find(e => e.id === id);
        if (!evt) return;

        currentSelectedEventId = id;
        document.getElementById('cal-detail-title').textContent = evt.title;
        document.getElementById('cal-detail-source-label').textContent = evt.source_label;
        document.getElementById('cal-detail-source-label').style.color = evt.color_code;
        document.getElementById('cal-detail-icon-box').style.color = evt.color_code;
        document.getElementById('cal-detail-datetime').textContent = `${evt.date} • ${evt.is_all_day ? 'All-Day' : (evt.start_time ? new Date(evt.start_time).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : '')}`;
        document.getElementById('cal-detail-location').textContent = evt.subtitle || 'No location specified';
        document.getElementById('cal-detail-linked-name').textContent = evt.related_entity_name || 'None';
        document.getElementById('cal-detail-desc').textContent = evt.description || 'No additional notes provided.';

        const attendeesContainer = document.getElementById('cal-detail-attendees-list');
        if (evt.attendee_emails && evt.attendee_emails.length > 0) {
            attendeesContainer.innerHTML = evt.attendee_emails.map(em => `<span style="background: #f1f5f9; color: #334155; padding: 0.15rem 0.45rem; border-radius: 12px; font-size: 0.75rem;">${em}</span>`).join('');
        } else {
            attendeesContainer.innerHTML = '<span style="color: #94a3b8;">None</span>';
        }

        document.getElementById('cal-detail-modal-overlay').style.display = 'flex';
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function closeEventDetailModal() {
        document.getElementById('cal-detail-modal-overlay').style.display = 'none';
    }

    function deleteCurrentEvent() {
        if (!currentSelectedEventId) return;
        activeCalendarEvents = activeCalendarEvents.filter(e => e.id !== currentSelectedEventId);
        closeEventDetailModal();
        renderCalendar();
    }

    // New Event Modal
    function openNewEventModal(dateStr = '2026-10-15') {
        document.getElementById('new-evt-date').value = dateStr;
        document.getElementById('cal-new-modal-overlay').style.display = 'flex';
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function closeNewEventModal() {
        document.getElementById('cal-new-modal-overlay').style.display = 'none';
    }

    function handleCreateEvent(event) {
        event.preventDefault();
        const title = document.getElementById('new-evt-title').value;
        const date = document.getElementById('new-evt-date').value;
        const type = document.getElementById('new-evt-type').value;
        const start = document.getElementById('new-evt-start').value;
        const location = document.getElementById('new-evt-location').value;
        const linked = document.getElementById('new-evt-linked').value;

        const srcConfig = calendarSources.find(s => s.id === type) || calendarSources[0];

        const newEvt = {
            id: 'evt-' + Date.now(),
            title: title,
            subtitle: location,
            date: date,
            start_time: `${date}T${start}:00`,
            is_all_day: false,
            source_id: srcConfig.id,
            source_label: srcConfig.object_label,
            color_code: srcConfig.color_code,
            icon_name: srcConfig.icon_name,
            related_entity_name: linked,
            description: document.getElementById('new-evt-notes').value
        };

        activeCalendarEvents.push(newEvt);
        closeNewEventModal();
        renderCalendar();
    }

    // Close popovers when clicking outside
    document.addEventListener('click', (e) => {
        const vBtn = document.getElementById('view-dropdown-trigger');
        const vMenu = document.getElementById('view-dropdown-menu');
        if (vBtn && vMenu && !vBtn.contains(e.target) && !vMenu.contains(e.target)) {
            vMenu.style.display = 'none';
        }

        const lBtn = document.getElementById('layers-dropdown-trigger');
        const lMenu = document.getElementById('layers-dropdown-menu');
        if (lBtn && lMenu && !lBtn.contains(e.target) && !lMenu.contains(e.target)) {
            lMenu.style.display = 'none';
        }
    });
</script>
@endsection
