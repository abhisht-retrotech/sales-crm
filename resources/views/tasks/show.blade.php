@extends('layouts.app')

@section('title', ($task['subject'] ?? 'Task') . ' — Task 360° Profile — DealSync CRM')

@section('content')
<!-- Centered Max-Width Container matching React page-wrapper (max-width: 1240px, padding: 2.25rem 2.5rem) -->
<div id="task-show-page-root" class="max-w-[1240px] mx-auto w-full px-6 sm:px-10 py-7 space-y-6 animate-fade-in">
    <!-- Breadcrumb & Back Action -->
    <div class="flex items-center justify-between gap-4 flex-wrap">
        <div class="flex items-center gap-2 text-xs">
            <a href="/tasks" class="spa-nav-link inline-flex items-center gap-1.5 font-semibold text-zinc-500 hover:text-indigo-600 dark:text-zinc-400 dark:hover:text-indigo-400 transition-colors">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Tasks &amp; Action Queue</span>
            </a>
            <span class="text-zinc-300 dark:text-zinc-700">/</span>
            <span class="text-zinc-600 dark:text-zinc-400 font-medium">{{ $task['related_entity_name'] ?? 'Task' }}</span>
            <span class="text-zinc-300 dark:text-zinc-700">/</span>
            <span class="text-zinc-950 dark:text-zinc-100 font-bold truncate max-w-xs">{{ $task['subject'] }}</span>
        </div>

        <div class="flex items-center gap-2">
            <span class="text-xs text-zinc-500 dark:text-zinc-400">Security Profile:</span>
            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-zinc-100 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-200 border border-zinc-200 dark:border-zinc-700">
                {{ $currentUserProfile ?? 'Administrator' }}
            </span>
        </div>
    </div>

    <!-- Hero Header Card -->
    <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 border border-zinc-200 dark:border-zinc-800 shadow-xs relative overflow-hidden">
        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-6 relative z-10">
            <!-- Left: Icon, Subject, Status, Priority, Badges -->
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white font-extrabold text-xl flex items-center justify-center shrink-0 shadow-md">
                    <i data-lucide="check-square" class="w-7 h-7"></i>
                </div>
                <div class="space-y-1.5">
                    <div class="flex items-center gap-3 flex-wrap">
                        <h1 class="text-2xl font-extrabold text-zinc-950 dark:text-white tracking-tight {{ ($task['status'] ?? '') === 'Completed' ? 'line-through text-zinc-400' : '' }}">
                            {{ $task['subject'] }}
                        </h1>

                        <!-- Status Badge -->
                        @php
                            $status = $task['status'] ?? 'Open';
                            $statusBadges = [
                                'Completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800',
                                'In Progress' => 'bg-sky-50 text-sky-700 border-sky-200 dark:bg-sky-950/40 dark:text-sky-300 dark:border-sky-800',
                                'Waiting on someone else' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800',
                                'Deferred' => 'bg-zinc-100 text-zinc-700 border-zinc-200 dark:bg-zinc-800 dark:text-zinc-300',
                                'Open' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800',
                            ];
                            $statusClass = $statusBadges[$status] ?? 'bg-zinc-100 text-zinc-700';
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $statusClass }}">
                            ● {{ $status }}
                        </span>

                        <!-- Priority Badge -->
                        @php
                            $prio = $task['priority'] ?? 'Normal';
                            $prioBadges = [
                                'Urgent' => 'bg-red-50 text-red-700 border-red-200 dark:bg-red-950/40 dark:text-red-300 dark:border-red-800',
                                'High' => 'bg-orange-50 text-orange-700 border-orange-200 dark:bg-orange-950/40 dark:text-orange-300 dark:border-orange-800',
                                'Normal' => 'bg-zinc-100 text-zinc-700 border-zinc-200 dark:bg-zinc-800 dark:text-zinc-300',
                                'Low' => 'bg-zinc-50 text-zinc-500 border-zinc-200',
                            ];
                            $prioClass = $prioBadges[$prio] ?? 'bg-zinc-100 text-zinc-700';
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-bold border {{ $prioClass }}">
                            {{ $prio }} Priority
                        </span>

                        @if(!empty($task['is_recurring']))
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-xs font-bold bg-teal-50 text-teal-800 border border-teal-200 dark:bg-teal-950/40 dark:text-teal-300">
                                <i data-lucide="repeat" class="w-3.5 h-3.5"></i>
                                <span>Recurring: {{ $task['recurrence_pattern'] ?? 'Weekly' }}</span>
                            </span>
                        @endif
                    </div>

                    <div class="text-xs text-zinc-600 dark:text-zinc-400 flex items-center gap-3 flex-wrap">
                        @if(!empty($task['related_entity_name']))
                            <span class="font-semibold text-zinc-900 dark:text-zinc-200 flex items-center gap-1">
                                <i data-lucide="folder" class="w-3.5 h-3.5 text-zinc-400"></i>
                                <span>{{ $task['related_entity_name'] }}</span>
                                @if(!empty($task['related_entity_type']))
                                    <span class="text-zinc-400 font-normal">({{ $task['related_entity_type'] }})</span>
                                @endif
                            </span>
                            <span>•</span>
                        @endif

                        <div class="flex items-center gap-1.5 text-zinc-700 dark:text-zinc-300">
                            <span class="text-zinc-400 font-medium">Assigned To:</span>
                            <span class="w-4 h-4 rounded-full bg-indigo-100 text-indigo-700 font-bold text-[9px] inline-flex items-center justify-center">
                                {{ strtoupper(substr($task['assigned_to_name'] ?? 'U', 0, 1)) }}
                            </span>
                            <span class="font-semibold">{{ $task['assigned_to_name'] ?? 'Unassigned' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Action Buttons & Deadline -->
            <div class="flex flex-col sm:items-end gap-3 shrink-0">
                <div class="text-right">
                    <div class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wider">TARGET DUE DATE</div>
                    <div class="text-2xl font-extrabold {{ !empty($task['is_overdue']) ? 'text-red-600' : 'text-zinc-950 dark:text-white' }} tracking-tight">
                        {{ $task['due_date_formatted'] }}
                    </div>
                    @if(!empty($task['is_overdue']))
                        <span class="text-[11px] font-bold text-red-600 bg-red-50 dark:bg-red-950/50 px-2 py-0.5 rounded mt-0.5 inline-block">
                            ⚠️ Action Overdue
                        </span>
                    @endif
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    <!-- Toggle Completion Button -->
                    @if(($task['status'] ?? '') === 'Completed')
                        <button
                            type="button"
                            onclick="window.toggleDetailTaskStatus('Open')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs cursor-pointer"
                        >
                            <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                            <span>Mark as Open</span>
                        </button>
                    @else
                        <button
                            type="button"
                            onclick="window.toggleDetailTaskStatus('Completed')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-xs transition-colors cursor-pointer"
                        >
                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                            <span>Mark Completed</span>
                        </button>
                    @endif

                    <button
                        type="button"
                        onclick="window.openTaskDetailEditModal()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs cursor-pointer"
                    >
                        <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                        <span>Edit Task</span>
                    </button>

                    <button
                        type="button"
                        onclick="window.openTaskDetailTransferModal()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs cursor-pointer"
                    >
                        <i data-lucide="user-check" class="w-3.5 h-3.5"></i>
                        <span>Transfer</span>
                    </button>

                    <button
                        type="button"
                        onclick="window.deleteCurrentTask()"
                        class="p-2 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-zinc-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/40 transition-colors shadow-2xs cursor-pointer"
                        title="Delete Task"
                    >
                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Segmented Tabs Navigation -->
    <div class="flex items-center gap-1.5 p-1 rounded-xl bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 overflow-x-auto">
        <button
            type="button"
            onclick="window.switchTaskDetailTab('details')"
            id="tab-btn-details"
            class="tab-btn active inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all cursor-pointer bg-white dark:bg-zinc-700 text-zinc-950 dark:text-white shadow-2xs"
        >
            <i data-lucide="info" class="w-3.5 h-3.5"></i>
            <span>Task Details &amp; Custom Fields</span>
        </button>

        <button
            type="button"
            onclick="window.switchTaskDetailTab('context')"
            id="tab-btn-context"
            class="tab-btn inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-medium transition-all cursor-pointer text-zinc-600 dark:text-zinc-400 hover:text-zinc-950 dark:hover:text-white"
        >
            <i data-lucide="folder" class="w-3.5 h-3.5"></i>
            <span>Linked Record Context</span>
        </button>

        <button
            type="button"
            onclick="window.switchTaskDetailTab('schedule')"
            id="tab-btn-schedule"
            class="tab-btn inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-medium transition-all cursor-pointer text-zinc-600 dark:text-zinc-400 hover:text-zinc-950 dark:hover:text-white"
        >
            <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
            <span>Recurrence &amp; System Metadata</span>
        </button>
    </div>

    <!-- TAB PANE 1: DETAILS & DYNAMIC CUSTOM FIELDS -->
    <div id="tab-pane-details" class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
            <!-- Left 2 Cols: Standard Details, Custom Fields, Description -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Standard Details Section -->
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 border border-zinc-200 dark:border-zinc-800 shadow-xs space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-zinc-100 dark:border-zinc-800">
                        <div class="flex items-center gap-2">
                            <i data-lucide="check-square" class="w-4 h-4 text-indigo-600"></i>
                            <h3 class="text-sm font-bold text-zinc-950 dark:text-zinc-100">Task Information</h3>
                        </div>
                        <span class="text-[11px] text-zinc-400">Created: {{ $task['created_at_formatted'] }}</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div class="p-3 rounded-xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-100 dark:border-zinc-800">
                            <span class="text-zinc-400 block mb-1 font-medium">Subject</span>
                            <span class="font-bold text-zinc-900 dark:text-zinc-100">{{ $task['subject'] }}</span>
                        </div>

                        <div class="p-3 rounded-xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-100 dark:border-zinc-800">
                            <span class="text-zinc-400 block mb-1 font-medium">Status</span>
                            <span class="font-bold text-zinc-900 dark:text-zinc-100">{{ $task['status'] }}</span>
                        </div>

                        <div class="p-3 rounded-xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-100 dark:border-zinc-800">
                            <span class="text-zinc-400 block mb-1 font-medium">Priority</span>
                            <span class="font-bold text-zinc-900 dark:text-zinc-100">{{ $task['priority'] }}</span>
                        </div>

                        <div class="p-3 rounded-xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-100 dark:border-zinc-800">
                            <span class="text-zinc-400 block mb-1 font-medium">Target Due Date</span>
                            <span class="font-bold text-zinc-900 dark:text-zinc-100">{{ $task['due_date_formatted'] }}</span>
                        </div>

                        <div class="p-3 rounded-xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-100 dark:border-zinc-800">
                            <span class="text-zinc-400 block mb-1 font-medium">Assigned Representative</span>
                            <span class="font-bold text-zinc-900 dark:text-zinc-100">{{ $task['assigned_to_name'] }}</span>
                        </div>

                        <div class="p-3 rounded-xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-100 dark:border-zinc-800">
                            <span class="text-zinc-400 block mb-1 font-medium">Related Entity</span>
                            <span class="font-bold text-zinc-900 dark:text-zinc-100">
                                {{ $task['related_entity_name'] ?: 'None (Standalone Task)' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Dynamic Custom Fields Section (Field-Level Permissions Enforced) -->
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 border border-zinc-200 dark:border-zinc-800 shadow-xs space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-zinc-100 dark:border-zinc-800">
                        <div class="flex items-center gap-2">
                            <i data-lucide="sliders" class="w-4 h-4 text-indigo-600"></i>
                            <h3 class="text-sm font-bold text-zinc-950 dark:text-zinc-100">Dynamic Custom Fields</h3>
                        </div>
                        <span class="text-[11px] text-zinc-500 font-medium">Security Permissions Enforced</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        @php
                            $renderedCustomCount = 0;
                        @endphp
                        @foreach($customFields ?? [] as $cf)
                            @php
                                $fName = $cf->field_name;
                                $perm = $fieldPermissions[$fName] ?? 'Read/Write';
                                if ($perm === 'Hidden') continue;
                                $renderedCustomCount++;
                                $val = $task[$fName] ?? ($task['custom_fields'][$fName] ?? null);
                            @endphp
                            <div class="p-3 rounded-xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-100 dark:border-zinc-800">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-zinc-400 font-medium">{{ $cf->field_label }}</span>
                                    @if($perm === 'Read-Only')
                                        <span class="inline-flex items-center gap-1 text-[10px] text-amber-600 dark:text-amber-400 font-semibold">
                                            <i data-lucide="lock" class="w-3 h-3"></i> Read-Only
                                        </span>
                                    @endif
                                </div>
                                <span class="font-bold text-zinc-900 dark:text-zinc-100">
                                    {{ $val !== null && $val !== '' ? $val : '—' }}
                                </span>
                                @if(!empty($cf->help_text))
                                    <p class="text-[10px] text-zinc-400 mt-0.5">{{ $cf->help_text }}</p>
                                @endif
                            </div>
                        @endforeach

                        @if($renderedCustomCount === 0)
                            <div class="col-span-2 py-4 text-center text-zinc-400 italic">
                                No custom fields visible under your current profile permissions.
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Comments & Action Context -->
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 border border-zinc-200 dark:border-zinc-800 shadow-xs space-y-3">
                    <div class="flex items-center gap-2 pb-2 border-b border-zinc-100 dark:border-zinc-800">
                        <i data-lucide="message-square" class="w-4 h-4 text-zinc-500"></i>
                        <h3 class="text-sm font-bold text-zinc-950 dark:text-zinc-100">Comments &amp; Action Notes</h3>
                    </div>
                    <p class="text-xs text-zinc-600 dark:text-zinc-300 leading-relaxed whitespace-pre-wrap">
                        {{ $task['comments'] ?: 'No additional instructions or comments recorded for this task.' }}
                    </p>
                </div>
            </div>

            <!-- Right 1 Col: Quick Status & Reminder Card -->
            <div class="space-y-6">
                <!-- Status & Urgency Card -->
                <div class="bg-white dark:bg-zinc-900 rounded-2xl p-5 border border-zinc-200 dark:border-zinc-800 shadow-xs space-y-3">
                    <div class="flex items-center justify-between pb-2 border-b border-zinc-100 dark:border-zinc-800">
                        <span class="text-xs font-bold text-zinc-900 dark:text-zinc-100">Queue State</span>
                        <span class="text-[10px] font-bold text-indigo-700 bg-indigo-50 dark:bg-indigo-950/40 px-2 py-0.5 rounded">
                            Action Queue
                        </span>
                    </div>

                    <div class="space-y-2 text-xs">
                        <div>
                            <span class="text-zinc-400 block font-medium">Reminder Setting:</span>
                            <span class="font-bold text-zinc-900 dark:text-zinc-100">{{ $task['reminder_set'] ?? 'No reminder set' }}</span>
                        </div>
                        <div>
                            <span class="text-zinc-400 block font-medium">Recurring Series:</span>
                            <span class="font-bold text-zinc-900 dark:text-zinc-100">
                                {{ !empty($task['is_recurring']) ? 'Yes (' . ($task['recurrence_pattern'] ?? 'Weekly') . ')' : 'No (Single Task)' }}
                            </span>
                        </div>
                        <div>
                            <span class="text-zinc-400 block font-medium">Created By:</span>
                            <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $task['created_by_name'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB PANE 2: LINKED RECORD CONTEXT -->
    <div id="tab-pane-context" class="hidden space-y-6">
        <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 border border-zinc-200 dark:border-zinc-800 shadow-xs space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-zinc-100 dark:border-zinc-800">
                <div class="flex items-center gap-2">
                    <i data-lucide="folder" class="w-4 h-4 text-indigo-600"></i>
                    <h3 class="text-sm font-bold text-zinc-950 dark:text-zinc-100">Parent CRM Record Context</h3>
                </div>
                <span class="text-xs text-zinc-400">{{ $task['related_entity_type'] ?? 'Standalone' }}</span>
            </div>

            @if(!empty($task['related_entity_name']))
                <div class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-100 dark:border-zinc-800 space-y-2 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-zinc-400 font-medium">Entity Type:</span>
                        <span class="font-bold text-zinc-900 dark:text-zinc-100">{{ $task['related_entity_type'] }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-zinc-400 font-medium">Record Name:</span>
                        <span class="font-bold text-indigo-600">{{ $task['related_entity_name'] }}</span>
                    </div>
                </div>
            @else
                <div class="py-8 text-center text-zinc-400 text-xs italic">
                    This task is a standalone action item and is not coupled to any specific CRM account or deal.
                </div>
            @endif
        </div>
    </div>

    <!-- TAB PANE 3: SCHEDULE & SYSTEM METADATA -->
    <div id="tab-pane-schedule" class="hidden space-y-6">
        <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 border border-zinc-200 dark:border-zinc-800 shadow-xs space-y-4 text-xs">
            <div class="flex items-center gap-2 pb-3 border-b border-zinc-100 dark:border-zinc-800">
                <i data-lucide="clock" class="w-4 h-4 text-zinc-500"></i>
                <h3 class="text-sm font-bold text-zinc-950 dark:text-zinc-100">Schedule &amp; System Metadata</h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="p-3 rounded-xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-100 dark:border-zinc-800">
                    <span class="text-zinc-400 block mb-1">Created Date</span>
                    <span class="font-bold text-zinc-900 dark:text-zinc-100">{{ $task['created_at_formatted'] }}</span>
                </div>
                <div class="p-3 rounded-xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-100 dark:border-zinc-800">
                    <span class="text-zinc-400 block mb-1">Created By</span>
                    <span class="font-bold text-zinc-900 dark:text-zinc-100">{{ $task['created_by_name'] }}</span>
                </div>
                <div class="p-3 rounded-xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-100 dark:border-zinc-800">
                    <span class="text-zinc-400 block mb-1">Recurrence Frequency</span>
                    <span class="font-bold text-zinc-900 dark:text-zinc-100">{{ $task['recurrence_pattern'] ?? 'None' }}</span>
                </div>
                <div class="p-3 rounded-xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-100 dark:border-zinc-800">
                    <span class="text-zinc-400 block mb-1">Reminder Config</span>
                    <span class="font-bold text-zinc-900 dark:text-zinc-100">{{ $task['reminder_set'] }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Task Show Hydration Data -->
@php
    $taskDetailHydrationData = [
        'task' => $task ?? [],
        'customFields' => $customFields ?? [],
        'fieldPermissions' => $fieldPermissions ?? (object)[],
        'users' => $users ?? [],
        'priorities' => $priorities ?? [],
        'statuses' => $statuses ?? [],
        'currentUserProfile' => $currentUserProfile ?? 'Administrator',
    ];
@endphp
<script>
    window.__TASK_DETAIL_DATA__ = {!! json_encode($taskDetailHydrationData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};
</script>
<script src="{{ asset('js/task_detail.js') }}?v=1.0"></script>
@endsection
