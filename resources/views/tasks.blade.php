@extends('layouts.app')

@section('title', 'Tasks & Action Queue — DealSync Enterprise CRM')

@section('content')
<!-- Centered Max-Width Container matching React page-wrapper (max-width: 1240px, padding: 2.25rem 2.5rem) -->
<div id="tasks-page-root" class="max-w-[1240px] mx-auto w-full px-6 sm:px-10 py-7 space-y-6 animate-fade-in">
    <!-- Top Action Header -->
    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4 pb-2 border-b border-zinc-200 dark:border-zinc-800">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center shadow-sm shrink-0">
                <i data-lucide="check-square" class="w-5 h-5"></i>
            </div>
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 dark:bg-indigo-950/40 dark:text-indigo-300 dark:border-indigo-800">
                        Standard CRM Object
                    </span>
                    <span id="task-total-badge" class="px-2 py-0.2 rounded-full text-[11px] font-bold bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                        {{ $counts['all'] ?? count($initialTasks ?? []) }} tasks
                    </span>
                </div>
                <h1 class="text-2xl font-bold tracking-tight text-zinc-950 dark:text-zinc-100">
                    Tasks &amp; Action Queue
                </h1>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5 max-w-2xl">
                    Standard CRM action items, schedules, recurring follow-ups, and notifications. Synced dynamically with custom fields and field-level security permissions.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 flex-wrap shrink-0">
            <!-- View Switcher (Table vs Kanban) -->
            <div class="inline-flex items-center p-0.5 rounded-lg bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700">
                <button
                    type="button"
                    id="btn-view-table"
                    onclick="window.switchTaskView('table')"
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-md transition-all cursor-pointer bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white shadow-2xs"
                >
                    <i data-lucide="table" class="w-3.5 h-3.5"></i>
                    <span>Table</span>
                </button>
                <button
                    type="button"
                    id="btn-view-kanban"
                    onclick="window.switchTaskView('kanban')"
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-md transition-all cursor-pointer text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white"
                >
                    <i data-lucide="kanban" class="w-3.5 h-3.5"></i>
                    <span>Kanban</span>
                </button>
            </div>

            <!-- Customize Visible Fields -->
            <button
                type="button"
                onclick="window.openTaskConfigModal()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs cursor-pointer"
                title="Customize Visible Fields"
            >
                <i data-lucide="sliders" class="w-3.5 h-3.5 text-zinc-500"></i>
                <span>Customize</span>
            </button>

            <!-- Filter Button -->
            <button
                type="button"
                onclick="window.openTaskFilterModal()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs cursor-pointer"
                title="Filter Tasks"
            >
                <i data-lucide="filter" class="w-3.5 h-3.5 text-zinc-500"></i>
                <span>Filter</span>
                <span id="tasks-filter-badge" class="hidden px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-blue-600 text-white">0</span>
            </button>

            <!-- Export CSV Button -->
            <button
                type="button"
                onclick="window.exportTasksCsv()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs cursor-pointer"
            >
                <i data-lucide="download" class="w-3.5 h-3.5 text-zinc-500"></i>
                <span>Export CSV</span>
            </button>

            <!-- New Task Primary Action -->
            <button
                type="button"
                onclick="window.openTaskModal('create')"
                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm transition-colors cursor-pointer"
            >
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>New Task</span>
            </button>
        </div>
    </div>

    <!-- Overdue Alert Banner (React Parity) -->
    <div id="tasks-overdue-banner" class="{{ ($counts['overdue'] ?? 0) > 0 ? '' : 'hidden' }} p-3.5 rounded-xl bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-900/40 text-red-900 dark:text-red-300 flex items-center justify-between flex-wrap gap-3">
        <div class="flex items-center gap-2.5">
            <i data-lucide="alert-triangle" class="w-5 h-5 text-red-600 dark:text-red-400 shrink-0"></i>
            <div>
                <strong id="overdue-banner-title" class="text-xs font-bold">
                    {{ $counts['overdue'] ?? 0 }} Urgent Action Items Past Deadline
                </strong>
                <p class="text-[11px] text-red-700 dark:text-red-400 mt-0.5">
                    Tasks past their target completion date require immediate rep attention or deadline rescheduling.
                </p>
            </div>
        </div>
        <button
            type="button"
            onclick="window.filterTaskTab('overdue')"
            class="px-3 py-1 rounded-lg bg-white dark:bg-zinc-900 border border-red-300 dark:border-red-800 text-red-700 dark:text-red-300 text-xs font-bold hover:bg-red-50 dark:hover:bg-red-950/40 transition-colors cursor-pointer shadow-2xs"
        >
            View Overdue Tasks
        </button>
    </div>

    <!-- Quick Filter Tabs (Pill Buttons matching React TasksView) -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1" id="tasks-tab-chips-container">
        <!-- Rendered dynamically by tasks.js with counts for: All Tasks, Open, Pending, Recurring, Today's, Overdue -->
    </div>

    <!-- Toolbar: Search Box & Refresh -->
    <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 p-3 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 shadow-xs">
        <div class="relative flex-1 max-w-md">
            <i data-lucide="search" class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
            <input
                type="text"
                id="tasks-search-input"
                placeholder="Search tasks by subject, comments, assignee, related record..."
                class="w-full pl-9 pr-8 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
            >
            <button
                type="button"
                id="tasks-search-clear-btn"
                onclick="window.clearTaskSearch()"
                class="hidden absolute right-2.5 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 cursor-pointer"
            >
                <i data-lucide="x" class="w-3.5 h-3.5"></i>
            </button>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <button
                type="button"
                onclick="window.refreshTasksData()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors cursor-pointer"
                title="Reload Tasks"
            >
                <i data-lucide="refresh-cw" class="w-3.5 h-3.5 text-zinc-500"></i>
                <span>Refresh</span>
            </button>
        </div>
    </div>

    <!-- Active Filter Chips Row -->
    <div id="tasks-active-filters-bar" class="hidden flex items-center gap-2 flex-wrap text-xs">
        <!-- Rendered dynamically by tasks.js -->
    </div>

    <!-- MAIN VIEW 1: COMPACT RESIZABLE TABLE VIEW -->
    <div id="tasks-table-view-container" class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-zinc-50 dark:bg-zinc-800/60 border-b border-zinc-200 dark:border-zinc-800 text-zinc-500 dark:text-zinc-400 uppercase tracking-wider text-[11px] font-semibold select-none">
                    <tr id="tasks-table-header-row">
                        <!-- Rendered dynamically by tasks.js based on visible fields -->
                    </tr>
                </thead>
                <tbody id="tasks-table-tbody" class="divide-y divide-zinc-200 dark:divide-zinc-800 text-zinc-800 dark:text-zinc-200">
                    <!-- Rendered dynamically by tasks.js -->
                </tbody>
            </table>
        </div>

        <!-- Empty State Container -->
        <div id="tasks-empty-state" class="hidden py-14 px-4 text-center">
            <div class="w-12 h-12 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center mx-auto text-zinc-400 mb-3">
                <i data-lucide="check-square" class="w-6 h-6"></i>
            </div>
            <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">No tasks found</h3>
            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 max-w-sm mx-auto">
                No CRM tasks match your active filter tab or search criteria. Try modifying your search or create a new action item.
            </p>
            <button
                type="button"
                onclick="window.openTaskModal('create')"
                class="mt-4 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-xs cursor-pointer"
            >
                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                <span>Add Task</span>
            </button>
        </div>

        <!-- Table Footer / Count -->
        <div class="px-4 py-2.5 bg-zinc-50 dark:bg-zinc-800/40 border-t border-zinc-200 dark:border-zinc-800 flex items-center justify-between text-xs text-zinc-500 dark:text-zinc-400">
            <span id="tasks-table-summary">Showing 0 tasks</span>
            <div class="flex items-center gap-3">
                <span class="text-[11px]">Security Profile: <strong>{{ $currentUserProfile ?? 'Administrator' }}</strong></span>
            </div>
        </div>
    </div>

    <!-- MAIN VIEW 2: KANBAN BOARD VIEW -->
    <div id="tasks-kanban-view-container" class="hidden">
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4 items-start" id="tasks-kanban-board">
            <!-- 5 Columns rendered dynamically: Open, In Progress, Waiting on someone else, Deferred, Completed -->
        </div>
    </div>
</div>

<!-- MODAL 1: CREATE / EDIT TASK MODAL -->
<div id="task-form-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-xs animate-fade-in">
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xl w-full max-w-2xl overflow-hidden max-h-[90vh] flex flex-col">
        <!-- Modal Header -->
        <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between shrink-0 bg-zinc-50/50 dark:bg-zinc-800/50">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 flex items-center justify-center">
                    <i data-lucide="check-square" class="w-4 h-4"></i>
                </div>
                <div>
                    <h2 id="task-form-modal-title" class="text-base font-bold text-zinc-950 dark:text-zinc-100">
                        New Task
                    </h2>
                    <p class="text-[11px] text-zinc-500">Schedule CRM action items, follow-ups, and notifications</p>
                </div>
            </div>
            <button
                type="button"
                onclick="window.closeTaskModal()"
                class="w-7 h-7 rounded-lg text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800 flex items-center justify-center transition-colors cursor-pointer"
            >
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- Modal Form Body -->
        <form id="task-record-form" onsubmit="window.saveTaskForm(event)" class="overflow-y-auto p-6 space-y-5 flex-1">
            <input type="hidden" id="form-task-id" value="">

            <!-- SECTION: STANDARD TASK DETAILS -->
            <div class="space-y-4">
                <div class="flex items-center justify-between pb-1 border-b border-zinc-100 dark:border-zinc-800">
                    <span class="text-xs font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider">Task Information</span>
                    <span class="text-[10px] text-zinc-400">Fields marked * are mandatory</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Subject -->
                    <div class="sm:col-span-2 task-field-group" data-field-name="subject">
                        <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            Subject <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="form-task-subject"
                            required
                            placeholder="e.g. Executive Sign-off Review with CTO"
                            class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                    </div>

                    <!-- Priority -->
                    <div class="task-field-group" data-field-name="priority">
                        <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            Priority <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="form-task-priority"
                            required
                            class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                            <option value="Normal">Normal</option>
                            <option value="High">High</option>
                            <option value="Urgent">Urgent</option>
                            <option value="Low">Low</option>
                        </select>
                    </div>

                    <!-- Status -->
                    <div class="task-field-group" data-field-name="status">
                        <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            Status <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="form-task-status"
                            required
                            class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                            <option value="Open">Open</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Waiting on someone else">Waiting on someone else</option>
                            <option value="Deferred">Deferred</option>
                            <option value="Completed">Completed</option>
                        </select>
                    </div>

                    <!-- Assigned Owner -->
                    <div class="task-field-group" data-field-name="assigned_to">
                        <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            Assigned To
                        </label>
                        <select
                            id="form-task-assigned-to"
                            class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                            <option value="">-- Unassigned --</option>
                            @foreach($users ?? [] as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Due Date -->
                    <div class="task-field-group" data-field-name="due_date">
                        <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            Due Date
                        </label>
                        <input
                            type="date"
                            id="form-task-due-date"
                            class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                    </div>

                    <!-- Related Entity Type -->
                    <div class="task-field-group" data-field-name="related_entity_type">
                        <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            Related Record Type
                        </label>
                        <select
                            id="form-task-rel-type"
                            onchange="window.handleRelatedTypeChange(this.value)"
                            class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                            <option value="">-- None (Standalone Task) --</option>
                            <option value="Account">Customer Account</option>
                            <option value="Opportunity">Sales Opportunity</option>
                            <option value="Lead">Lead Record</option>
                            <option value="Contact">Contact Stakeholder</option>
                        </select>
                    </div>

                    <!-- Related Record Name / Autocomplete -->
                    <div class="task-field-group" data-field-name="related_entity_name">
                        <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            Related Record Name
                        </label>
                        <input
                            type="text"
                            id="form-task-rel-name"
                            placeholder="e.g. Skyline Cloud Systems"
                            class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                        <input type="hidden" id="form-task-rel-id" value="">
                    </div>

                    <!-- Recurring Checkbox & Pattern -->
                    <div class="sm:col-span-2 p-3 rounded-xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200 dark:border-zinc-700 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <input
                                type="checkbox"
                                id="form-task-is-recurring"
                                onchange="document.getElementById('form-task-recurrence-wrap').style.display = this.checked ? 'block' : 'none'"
                                class="rounded text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                            >
                            <label for="form-task-is-recurring" class="text-xs font-semibold text-zinc-900 dark:text-zinc-100 cursor-pointer flex items-center gap-1.5">
                                <i data-lucide="repeat" class="w-3.5 h-3.5 text-teal-600"></i>
                                <span>Recurring Series Task</span>
                            </label>
                        </div>
                        <div id="form-task-recurrence-wrap" style="display: none;">
                            <select
                                id="form-task-recurrence-pattern"
                                class="px-2.5 py-1 text-xs rounded-md border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100"
                            >
                                <option value="Daily">Daily</option>
                                <option value="Weekly" selected>Weekly</option>
                                <option value="Monthly">Monthly</option>
                            </select>
                        </div>
                    </div>

                    <!-- Comments / Description -->
                    <div class="sm:col-span-2 task-field-group" data-field-name="comments">
                        <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            Comments / Action Context
                        </label>
                        <textarea
                            id="form-task-comments"
                            rows="2"
                            placeholder="Milestone details, agenda items, follow-up instructions..."
                            class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        ></textarea>
                    </div>
                </div>
            </div>

            <!-- SECTION: DYNAMIC CUSTOM FIELDS (Permission Enforced) -->
            <div id="task-dynamic-custom-fields-section" class="space-y-4 pt-3 border-t border-zinc-200 dark:border-zinc-800">
                <div class="flex items-center justify-between pb-1 border-b border-zinc-100 dark:border-zinc-800">
                    <span class="text-xs font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider">Dynamic Custom Fields</span>
                    <span class="text-[10px] text-zinc-400">Field-Level Security Active</span>
                </div>

                <div id="task-dynamic-fields-container" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Populated dynamically by tasks.js respecting Hidden / Read-Only / Read/Write permissions -->
                </div>
            </div>

            <!-- Modal Action Buttons -->
            <div class="pt-4 border-t border-zinc-200 dark:border-zinc-800 flex items-center justify-end gap-2 shrink-0">
                <button
                    type="button"
                    onclick="window.closeTaskModal()"
                    class="px-4 py-2 text-xs font-semibold rounded-lg border border-zinc-300 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    id="btn-save-task-form"
                    class="px-4 py-2 text-xs font-semibold rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white transition-colors cursor-pointer shadow-xs"
                >
                    Save Task
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 2: CUSTOMIZE VISIBLE FIELDS MODAL -->
<div id="task-config-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-xs animate-fade-in">
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xl w-full max-w-md overflow-hidden flex flex-col">
        <div class="px-5 py-4 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between bg-zinc-50/50 dark:bg-zinc-800/50">
            <div class="flex items-center gap-2">
                <i data-lucide="sliders" class="w-4 h-4 text-indigo-600"></i>
                <h3 class="text-sm font-bold text-zinc-950 dark:text-zinc-100">Customize Visible Fields</h3>
            </div>
            <button type="button" onclick="window.closeTaskConfigModal()" class="text-zinc-400 hover:text-zinc-600 cursor-pointer">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
        <div class="p-5 overflow-y-auto max-h-[60vh] space-y-3" id="task-config-fields-list">
            <!-- Populated dynamically by tasks.js -->
        </div>
        <div class="px-5 py-3 border-t border-zinc-200 dark:border-zinc-800 flex items-center justify-between bg-zinc-50 dark:bg-zinc-800/40">
            <button type="button" onclick="window.resetTaskDefaultFields()" class="text-xs text-indigo-600 hover:underline cursor-pointer font-medium">
                Reset Defaults
            </button>
            <div class="flex items-center gap-2">
                <button type="button" onclick="window.closeTaskConfigModal()" class="px-3 py-1.5 text-xs font-semibold rounded-lg border border-zinc-300 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 cursor-pointer">
                    Cancel
                </button>
                <button type="button" onclick="window.saveTaskFieldConfigs()" class="px-3.5 py-1.5 text-xs font-semibold rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white cursor-pointer shadow-xs">
                    Apply Fields
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL 3: UNIVERSAL FILTER MODAL -->
<div id="task-filter-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-xs animate-fade-in">
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xl w-full max-w-lg overflow-hidden flex flex-col">
        <div class="px-5 py-4 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between bg-zinc-50/50 dark:bg-zinc-800/50">
            <div class="flex items-center gap-2">
                <i data-lucide="filter" class="w-4 h-4 text-blue-600"></i>
                <h3 class="text-sm font-bold text-zinc-950 dark:text-zinc-100">Filter Tasks</h3>
            </div>
            <button type="button" onclick="window.closeTaskFilterModal()" class="text-zinc-400 hover:text-zinc-600 cursor-pointer">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
        <div class="p-5 space-y-3 overflow-y-auto max-h-[60vh]">
            <div id="task-filter-rules-container" class="space-y-2.5">
                <!-- Filter rules added here dynamically -->
            </div>
            <button
                type="button"
                onclick="window.addTaskFilterRule()"
                class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600 hover:text-indigo-700 pt-1 cursor-pointer"
            >
                <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i>
                <span>+ Add Condition</span>
            </button>
        </div>
        <div class="px-5 py-3 border-t border-zinc-200 dark:border-zinc-800 flex items-center justify-between bg-zinc-50 dark:bg-zinc-800/40">
            <button type="button" onclick="window.clearAllTaskFilters()" class="text-xs text-red-600 hover:underline cursor-pointer font-medium">
                Clear Filters
            </button>
            <div class="flex items-center gap-2">
                <button type="button" onclick="window.closeTaskFilterModal()" class="px-3 py-1.5 text-xs font-semibold rounded-lg border border-zinc-300 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 cursor-pointer">
                    Cancel
                </button>
                <button type="button" onclick="window.applyTaskFilters()" class="px-3.5 py-1.5 text-xs font-semibold rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white cursor-pointer shadow-xs">
                    Apply Filter
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL 4: TRANSFER ASSIGNEE MODAL -->
<div id="task-transfer-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-xs animate-fade-in">
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xl w-full max-w-sm overflow-hidden p-5 space-y-4">
        <div class="flex items-center gap-2.5 text-indigo-600">
            <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-950/50 flex items-center justify-center">
                <i data-lucide="user-check" class="w-4 h-4"></i>
            </div>
            <div>
                <h3 class="text-sm font-bold text-zinc-950 dark:text-zinc-100">Transfer Task Assignee</h3>
                <p class="text-[11px] text-zinc-500">Reassign task action item</p>
            </div>
        </div>
        <input type="hidden" id="transfer-task-id" value="">
        <p class="text-xs text-zinc-600 dark:text-zinc-400" id="transfer-task-subject-label">
            Select a team member to take ownership of this task.
        </p>
        <div>
            <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300 mb-1">New Assignee</label>
            <select id="transfer-new-assignee-id" class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                @foreach($users ?? [] as $u)
                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-center justify-end gap-2 pt-2">
            <button type="button" onclick="window.closeTaskTransferModal()" class="px-3 py-1.5 text-xs font-semibold rounded-lg border border-zinc-300 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 cursor-pointer">
                Cancel
            </button>
            <button type="button" onclick="window.submitTaskTransfer()" class="px-3.5 py-1.5 text-xs font-semibold rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white cursor-pointer shadow-xs">
                Confirm Reassign
            </button>
        </div>
    </div>
</div>

<!-- Safe Blade JSON Hydration with Zero Bracket/Parentheses Nesting Issues -->
@php
    $tasksHydrationData = [
        'initialTasks' => $initialTasks ?? [],
        'counts' => $counts ?? [],
        'customFields' => $customFields ?? [],
        'fieldPermissions' => $fieldPermissions ?? (object)[],
        'users' => $users ?? [],
        'accounts' => $accounts ?? [],
        'leads' => $leads ?? [],
        'opportunities' => $opportunities ?? [],
        'priorities' => $priorities ?? ['Low', 'Normal', 'High', 'Urgent'],
        'statuses' => $statuses ?? ['Open', 'In Progress', 'Waiting on someone else', 'Deferred', 'Completed'],
        'currentUserProfile' => $currentUserProfile ?? 'Administrator',
    ];
@endphp
<script>
    window.__TASKS_DATA__ = {!! json_encode($tasksHydrationData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};
</script>
<script src="{{ asset('js/tasks.js') }}?v=1.0"></script>
@endsection
