@php
    $leadTitle = (!empty($lead['company']) ? $lead['company'] . ' | ' : '') . ($lead['first_name'] ?? $lead['name'] ?? 'Lead');
    $status = $lead['status'] ?? 'New';
    $score = (int)($lead['score'] ?? 50);
    $ratingVal = (int)($lead['rating'] ?? 0);
    $createdDate = !empty($lead['created_at']) ? \Carbon\Carbon::parse($lead['created_at'])->format('M d, Y, h:i A') : now()->format('M d, Y, h:i A');
    $updatedDate = !empty($lead['updated_at']) ? \Carbon\Carbon::parse($lead['updated_at'])->format('M d, Y, h:i A') : now()->format('M d, Y, h:i A');
    $ownerName = $lead['assigned_to_name'] ?? ($lead['assigned_user']['name'] ?? 'Alex Morgan');
    $createdByName = $lead['created_by_name'] ?? 'System Admin';
    $updatedByName = $lead['updated_by_name'] ?? 'System Admin';
    $emailOptOut = !empty($lead['email_opt_out']) && $lead['email_opt_out'] !== '0' && $lead['email_opt_out'] !== false;
    $filesCount = $filesCount ?? 0;
    $activitiesCount = count($activities ?? []);
    $approvalsCount = count($approvals ?? []);
    $historyCount = count($auditLogs ?? []);
    $emailCount = $emailCount ?? 1;

    $custom = $lead['custom_data'] ?? [];
    if (is_string($custom)) {
        $custom = json_decode($custom, true) ?: [];
    }
    $approvalStatus = $lead['approval_status'] ?? 'Pending';
    $isApproved = !empty($lead['is_approved']) && $lead['is_approved'] !== '0' && $lead['is_approved'] !== false;
    $isRejected = !empty($lead['is_rejected']) && $lead['is_rejected'] !== '0' && $lead['is_rejected'] !== false;
    $canApproveOrReject = $canApproveOrReject ?? false;

    // Resolve PageLayout Sections for Dynamic Layout Control
    $layoutJson = $pageLayout->layout_json ?? ($pageLayout->layout ?? null);
    if (is_string($layoutJson)) {
        $layoutJson = json_decode($layoutJson, true) ?: [];
    }
    $layoutSections = $layoutJson['sections'] ?? [];

    $customFieldsMap = collect($customFields ?? [])->keyBy('field_name');
    $standardOverridesMap = collect($standardOverrides ?? [])->keyBy('field_name');

    // Default fallback if no layout sections defined
    if (empty($layoutSections)) {
        $layoutSections = [
            [
                'id' => 'sec-primary',
                'title' => 'Lead Detail',
                'columns' => 2,
                'collapsible' => true,
                'fields' => [
                    ['name' => 'first_name', 'label' => 'First Name', 'required' => true],
                    ['name' => 'last_name', 'label' => 'Last Name', 'required' => true],
                    ['name' => 'company', 'label' => 'Company Name', 'required' => true],
                    ['name' => 'title', 'label' => 'Job Title'],
                    ['name' => 'email', 'label' => 'Email Address', 'required' => true],
                    ['name' => 'email_opt_out', 'label' => 'Email Opt Out'],
                    ['name' => 'phone', 'label' => 'Phone Number'],
                    ['name' => 'status', 'label' => 'Status', 'required' => true],
                    ['name' => 'score', 'label' => 'Lead Score'],
                    ['name' => 'source', 'label' => 'Lead Source'],
                    ['name' => 'assigned_to', 'label' => 'Assigned To'],
                    ['name' => 'rating', 'label' => 'Rating'],
                    ['name' => 'notes', 'label' => 'Notes'],
                ]
            ],
            [
                'id' => 'sec-system-info',
                'title' => 'System Information',
                'columns' => 3,
                'collapsible' => true,
                'fields' => [
                    ['name' => 'assigned_to', 'label' => 'Current Owner', 'readOnly' => true],
                    ['name' => 'created_at', 'label' => 'Created Date & Time', 'readOnly' => true],
                    ['name' => 'created_by', 'label' => 'Created By', 'readOnly' => true],
                    ['name' => 'updated_at', 'label' => 'Last Modified Date & Time', 'readOnly' => true],
                    ['name' => 'updated_by', 'label' => 'Last Modified By', 'readOnly' => true],
                ]
            ]
        ];
    }
@endphp

<div data-lead-id="{{ $lead['id'] ?? $rawLead->id }}" class="w-full min-h-screen bg-[#f8fafc] dark:bg-zinc-950 text-slate-800 dark:text-zinc-100 flex flex-col font-sans select-text">
    <input type="hidden" id="current-lead-id" value="{{ $lead['id'] ?? $rawLead->id }}">
    <input type="hidden" id="lead-is-approved" value="{{ $isApproved ? '1' : '0' }}">
    <input type="hidden" id="lead-can-approve" value="{{ $canApproveOrReject ? '1' : '0' }}">

    <!-- 1. TOP FULLSCREEN HEADER BAR (Exact 1:1 Match with Target Screenshot) -->
    <header class="sticky top-0 z-40 w-full bg-white dark:bg-zinc-900 border-b border-slate-200 dark:border-zinc-800 px-4 sm:px-6 py-2.5 shadow-2xs">
        <div class="max-w-[1680px] mx-auto flex items-center justify-between gap-4">

            <!-- Left Group: Back Button + LEADS Badge + Lead Title + Status Pill + Approval Status -->
            <div class="flex items-center gap-3 min-w-0">
                <button
                    type="button"
                    onclick="window.closeLeadDetailModal ? window.closeLeadDetailModal() : (window.location.href='/leads')"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-slate-700 dark:text-zinc-200 hover:bg-slate-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs cursor-pointer shrink-0"
                    title="Return to Leads Table"
                >
                    <svg class="w-3.5 h-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6"/>
                    </svg>
                    <span>Back to Leads</span>
                </button>

                <!-- LEADS Purple Tag Badge -->
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-extrabold uppercase bg-purple-100 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 shrink-0">
                    LEADS
                </span>

                <!-- Lead Title -->
                <h1 id="lead-detail-header-title" class="text-sm sm:text-base font-bold text-slate-900 dark:text-zinc-100 truncate tracking-tight">
                    {{ $leadTitle }}
                </h1>

                <!-- Status Pill (e.g. • New) -->
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-zinc-300 border border-slate-200/80 dark:border-zinc-700/80 shrink-0">
                    <span class="text-slate-400 dark:text-zinc-500">•</span>
                    <span id="lead-detail-header-status">{{ $status }}</span>
                </span>

                <!-- Approval Status Pill -->
                <div id="header-approval-pill-container" class="shrink-0">
                    @if($isApproved)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700 shadow-2xs" title="Governance Status: Approved{{ !empty($lead['approved_by_name']) ? ' by ' . $lead['approved_by_name'] : '' }}">
                        <svg class="w-3.5 h-3.5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>Approved</span>
                    </span>
                    @elseif($isRejected)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-300 dark:border-rose-700 shadow-2xs" title="Governance Status: Rejected{{ !empty($lead['rejected_by_name']) ? ' by ' . $lead['rejected_by_name'] : '' }}">
                        <svg class="w-3.5 h-3.5 text-rose-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        <span>Rejected</span>
                    </span>
                    @else
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-300 dark:border-amber-700 shadow-2xs" title="Governance Status: {{ $approvalStatus }}">
                        <svg class="w-3.5 h-3.5 text-amber-600 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10" stroke-dasharray="32" stroke-dashoffset="12"/></svg>
                        <span>{{ $approvalStatus ?: 'Pending Approval' }}</span>
                    </span>
                    @endif
                </div>
            </div>

            <!-- Right Action Buttons: Top Approval Actions, Change Owner, Share, Convert, More, Close -->
            <div class="flex items-center gap-2 shrink-0">

                <!-- TOP APPROVAL BUTTONS (MANAGER OR ADMIN ONLY) -->
                @if($canApproveOrReject)
                <div id="header-approval-buttons" class="flex items-center gap-1.5 pr-2 border-r border-slate-200 dark:border-zinc-700">
                    @if(!$isApproved)
                    <button
                        type="button"
                        id="btn-header-approve-lead"
                        onclick="window.handleLeadApprovalAction ? window.handleLeadApprovalAction('approved') : null"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all shadow-xs hover:shadow cursor-pointer"
                        title="Approve this lead (Unlocks task execution)"
                    >
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                        <span>Approve</span>
                    </button>
                    @endif

                    @if(!$isRejected)
                    <button
                        type="button"
                        id="btn-header-reject-lead"
                        onclick="window.handleLeadApprovalAction ? window.handleLeadApprovalAction('rejected') : null"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition-all shadow-xs hover:shadow cursor-pointer"
                        title="Reject this lead"
                    >
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <line x1="18" y1="6" x2="6" y2="18"/>
                            <line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                        <span>Reject</span>
                    </button>
                    @endif
                </div>
                @endif

                @if($profilePermissions['can_transfer'] ?? true)
                <button
                    type="button"
                    onclick="window.openDetailTransferModal ? window.openDetailTransferModal() : null"
                    class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-slate-700 dark:text-zinc-200 hover:bg-slate-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs cursor-pointer"
                    title="Change Owner"
                >
                    <svg class="w-3.5 h-3.5 text-blue-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                    <span>Change Owner</span>
                </button>
                @endif

                <button
                    type="button"
                    onclick="window.triggerShareLead ? window.triggerShareLead() : alert('Share link copied to clipboard!')"
                    class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-slate-700 dark:text-zinc-200 hover:bg-slate-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs cursor-pointer"
                    title="Share Lead"
                >
                    <svg class="w-3.5 h-3.5 text-sky-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
                    </svg>
                    <span>Share</span>
                </button>

                @if($profilePermissions['can_update'] ?? true)
                <!-- Top Lead Edit Action Controls (Inline Editing Mode) -->
                <div id="top-lead-edit-controls" class="inline-flex items-center gap-1.5">
                    <!-- Normal View State: Edit Button -->
                    <button
                        type="button"
                        id="btn-top-lead-edit"
                        onclick="window.enableAllSectionsInlineEdit ? window.enableAllSectionsInlineEdit() : null"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-slate-700 dark:text-zinc-200 hover:bg-slate-50 dark:hover:bg-zinc-700 hover:text-indigo-600 transition-colors shadow-2xs cursor-pointer"
                        title="Edit Lead Record"
                    >
                        <svg class="w-3.5 h-3.5 text-indigo-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                        <span>Edit</span>
                    </button>

                    <!-- Edit State: Cancel All and Save All (Initially Hidden) -->
                    <div id="top-lead-edit-actions" class="hidden items-center gap-1.5">
                        <button
                            type="button"
                            onclick="window.cancelAllSectionsInlineEdit ? window.cancelAllSectionsInlineEdit() : null"
                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-slate-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-700 transition-colors shadow-2xs cursor-pointer"
                            title="Cancel All Changes"
                        >
                            <svg class="w-3.5 h-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            <span>Cancel</span>
                        </button>
                        <button
                            type="button"
                            id="btn-top-lead-save-all"
                            onclick="window.saveAllSectionsInlineEdit ? window.saveAllSectionsInlineEdit() : null"
                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-colors shadow-xs cursor-pointer"
                            title="Save All Changes"
                        >
                            <svg class="w-3.5 h-3.5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                            <span>Save</span>
                        </button>
                    </div>
                </div>
                @endif

                @if(($profilePermissions['can_update'] ?? true) && empty($lead['is_converted']))
                <!-- Green Convert Button -->
                <button
                    type="button"
                    onclick="window.openDetailConvertModal ? window.openDetailConvertModal() : null"
                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-colors shadow-xs cursor-pointer"
                    title="Convert into Account &amp; Contact"
                >
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>
                    </svg>
                    <span>Convert</span>
                </button>
                @endif

                <!-- More Options Button -->
                <button
                    type="button"
                    class="p-1.5 rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-500 hover:text-slate-800 dark:hover:text-zinc-100 hover:bg-slate-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs cursor-pointer"
                    title="More Actions"
                >
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/><circle cx="5" cy="12" r="1"/>
                    </svg>
                </button>

                <!-- Fullscreen Modal Close Button (✕) -->
                <button
                    type="button"
                    onclick="window.closeLeadDetailModal ? window.closeLeadDetailModal() : (window.location.href='/leads')"
                    class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-zinc-200 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer text-sm font-bold ml-0.5"
                    title="Close Fullscreen View (Esc)"
                >
                    ✕
                </button>
            </div>
        </div>
    </header>

    <!-- 2. BREADCRUMB BAR (Exact Parity with Target Screenshot) -->
    <div class="w-full bg-[#f8fafc] dark:bg-zinc-950 px-4 sm:px-6 py-2 border-b border-slate-100 dark:border-zinc-800/80">
        <div class="max-w-[1680px] mx-auto flex items-center gap-2 text-xs font-semibold text-slate-500 dark:text-zinc-400">
            <a href="/" class="hover:text-blue-600 transition-colors no-underline">DealSync</a>
            <span class="text-slate-400 font-normal select-none">&gt;</span>
            <a href="/leads" onclick="window.closeLeadDetailModal ? (event.preventDefault(), window.closeLeadDetailModal()) : null" class="hover:text-blue-600 transition-colors no-underline">Leads</a>
            <span class="text-slate-400 font-normal select-none">&gt;</span>
            <span class="text-slate-700 dark:text-zinc-200 font-bold truncate max-w-xl">{{ $leadTitle }}</span>
        </div>
    </div>

    <!-- 2.5 WORKFLOW APPROVAL GOVERNANCE BANNER -->
    <div id="leadApprovalWorkflowBanner" class="max-w-[1680px] mx-auto w-full px-4 sm:px-6 mt-3">
        @if(!$isApproved)
            @if($isRejected)
            <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-900 dark:bg-rose-950/40 dark:border-rose-900 dark:text-rose-200 text-xs flex items-center justify-between gap-3 shadow-2xs">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-rose-100 dark:bg-rose-900/60 flex items-center justify-center text-rose-600 dark:text-rose-400 shrink-0 font-bold">
                        ✕
                    </div>
                    <div>
                        <div class="font-bold text-slate-900 dark:text-white text-xs">
                            Lead Rejected
                            @if(!empty($lead['rejected_by_name']))
                                by {{ $lead['rejected_by_name'] }}
                            @endif
                            @if(!empty($lead['rejected_at']))
                                <span class="text-[10px] text-rose-600/80 font-normal">({{ $lead['rejected_at'] }})</span>
                            @endif
                        </div>
                        <div class="text-[11px] text-rose-700 dark:text-rose-300">
                            Tasks and operational workflows on this lead remain locked.
                            {{ !empty($lead['approval_notes']) ? 'Notes: ' . $lead['approval_notes'] : '' }}
                        </div>
                    </div>
                </div>
                @if($canApproveOrReject)
                <button type="button" onclick="window.handleLeadApprovalAction ? window.handleLeadApprovalAction('approved') : null" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition-colors shrink-0 shadow-xs cursor-pointer">
                    Re-evaluate &amp; Approve
                </button>
                @endif
            </div>
            @else
            <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 dark:bg-amber-950/40 dark:border-amber-900 dark:text-amber-200 text-xs flex items-center justify-between gap-3 shadow-2xs">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-900/60 flex items-center justify-center text-amber-600 dark:text-amber-400 shrink-0 font-bold">
                        ⏳
                    </div>
                    <div>
                        <div class="font-bold text-slate-900 dark:text-white text-xs">
                            Lead {{ $approvalStatus }} — Task Execution Locked
                        </div>
                        <div class="text-[11px] text-amber-700 dark:text-amber-300">
                            Per multi-level workflow governance, tasks cannot be executed until all required approval steps are completed.
                            @if(!empty($lead['approval_notes']))
                                <span class="block mt-0.5 text-amber-800 dark:text-amber-200 font-medium">{{ $lead['approval_notes'] }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                @if($canApproveOrReject)
                <div class="flex items-center gap-2 shrink-0">
                    <button type="button" onclick="window.handleLeadApprovalAction ? window.handleLeadApprovalAction('approved') : null" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition-colors shadow-xs cursor-pointer">
                        ✓ Approve Step
                    </button>
                    <button type="button" onclick="window.handleLeadApprovalAction ? window.handleLeadApprovalAction('rejected') : null" class="px-3 py-1.5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs transition-colors shadow-xs cursor-pointer">
                        ✕ Reject
                    </button>
                </div>
                @else
                <span class="inline-flex items-center px-2.5 py-1 rounded-md bg-amber-100 dark:bg-amber-900/50 text-amber-800 dark:text-amber-300 font-bold text-[11px] shrink-0">
                    Awaiting Approval
                </span>
                @endif
            </div>
            @endif
        @else
            <div class="p-3 rounded-xl bg-emerald-50/70 border border-emerald-200/80 text-emerald-900 dark:bg-emerald-950/30 dark:border-emerald-800/60 dark:text-emerald-200 text-xs flex items-center justify-between gap-2 shadow-2xs">
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 rounded-md bg-emerald-100 dark:bg-emerald-900/60 flex items-center justify-center text-emerald-600 dark:text-emerald-400 shrink-0 font-bold text-xs">
                        ✓
                    </div>
                    <div>
                        <span class="font-bold text-slate-900 dark:text-white">Lead Approved</span>
                        @if(!empty($lead['approved_by_name']))
                            <span class="text-[11px] text-emerald-800 dark:text-emerald-300 font-medium">by {{ $lead['approved_by_name'] }}</span>
                        @endif
                        @if(!empty($lead['approved_at']))
                            <span class="text-[10px] text-emerald-700/80 dark:text-emerald-400">({{ $lead['approved_at'] }})</span>
                        @endif
                        <span class="text-[11px] text-emerald-700 dark:text-emerald-400 ml-1.5">Governance workflow satisfied. All task scheduling and pipeline operations are unlocked.</span>
                        @if(!empty($lead['approval_notes']))
                            <div class="text-[10px] text-emerald-800/80 dark:text-emerald-300 mt-0.5">Audit: {{ $lead['approval_notes'] }}</div>
                        @endif
                    </div>
                </div>
                @if($canApproveOrReject)
                <button type="button" onclick="window.handleLeadApprovalAction ? window.handleLeadApprovalAction('rejected') : null" class="px-2.5 py-1 rounded border border-rose-200 hover:bg-rose-100 text-rose-700 dark:border-rose-800 dark:text-rose-300 font-semibold text-[11px] transition-colors shrink-0 cursor-pointer">
                    Revoke / Reject
                </button>
                @endif
            </div>
        @endif
    </div>

    <!-- Feedback Banner (If Any Action Taken) -->
    <div id="detailFeedbackBanner" class="hidden max-w-[1680px] mx-auto w-full px-4 sm:px-6 mt-3">
        <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 dark:bg-emerald-950/40 dark:border-emerald-800 dark:text-emerald-300 text-xs font-semibold flex items-center gap-2 shadow-2xs">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <span id="detailFeedbackText">Changes saved successfully.</span>
        </div>
    </div>

    <!-- 3. MAIN TWO-COLUMN CONTENT WORKSPACE (Col 8 + Col 4) -->
    <main class="flex-1 max-w-[1680px] mx-auto w-full px-4 sm:px-6 py-5">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            <!-- ==================== LEFT COLUMN: RECORD CARDS (8 COLS) ==================== -->
            <div class="lg:col-span-8 space-y-4">
                @foreach($layoutSections as $secIndex => $sec)
                    @php
                        $secId = $sec['id'] ?? 'sec-' . $secIndex;
                        $secTitle = $sec['title'] ?? 'Section ' . ($secIndex + 1);
                        $secColumns = (int)($sec['columns'] ?? 2);
                        $isCollapsible = !isset($sec['collapsible']) || (bool)$sec['collapsible'];
                        $defaultCollapsed = !empty($sec['defaultCollapsed']);
                        $secFields = $sec['fields'] ?? [];
                        $isSystemSection = ($secId === 'sec-system-info') || (stripos($secTitle, 'System Information') !== false);
                        
                        $bodyId = 'sectionBody_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $secId);
                        $chevronId = 'sectionChevron_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $secId);

                        $gridColsClass = 'grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-4';
                        if ($secColumns === 1) {
                            $gridColsClass = 'grid-cols-1 gap-y-4';
                        } elseif ($secColumns === 3 || $isSystemSection) {
                            $gridColsClass = 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-y-5 gap-x-8';
                        }
                    @endphp

                    <!-- LAYOUT SECTION CARD: {{ $secTitle }} -->
                    <section
                        id="lead-section-{{ $secId }}"
                        data-section-id="{{ $secId }}"
                        class="lead-detail-section bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200/90 dark:border-zinc-800 shadow-xs p-6 transition-all duration-200"
                    >
                        <!-- Section Header -->
                        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-zinc-800 select-none gap-3">
                            @if($isCollapsible)
                            <button
                                type="button"
                                onclick="window.toggleSection ? window.toggleSection('{{ $bodyId }}', '{{ $chevronId }}') : null"
                                class="flex items-center gap-2 text-slate-900 dark:text-zinc-100 font-bold text-sm hover:text-blue-600 transition-colors cursor-pointer text-left bg-transparent border-0 p-0"
                            >
                                <svg id="{{ $chevronId }}" class="w-4 h-4 text-slate-400 transition-transform duration-200 {{ $defaultCollapsed ? '-rotate-90' : 'rotate-0' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/>
                                </svg>
                                @if($isSystemSection)
                                <svg class="w-4 h-4 text-slate-500 dark:text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                                </svg>
                                @else
                                <svg class="w-4 h-4 text-slate-500 dark:text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/>
                                </svg>
                                @endif
                                <span>{{ $secTitle }}</span>
                            </button>
                            @else
                            <div class="flex items-center gap-2 text-slate-900 dark:text-zinc-100 font-bold text-sm">
                                <svg class="w-4 h-4 text-slate-500 dark:text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/>
                                </svg>
                                <span>{{ $secTitle }}</span>
                            </div>
                            @endif

                            <!-- Right Corner Section Action Buttons: Save and Cancel in Edit Mode, Edit in View Mode -->
                            @if(!$isSystemSection && ($profilePermissions['can_update'] ?? true))
                            <div class="flex items-center gap-2" data-section-action-wrapper="{{ $secId }}">
                                <!-- View Mode: Edit Button -->
                                <button
                                    type="button"
                                    onclick="window.enableSectionInlineEdit ? window.enableSectionInlineEdit('{{ $secId }}') : null"
                                    class="section-btn-edit inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold text-slate-700 dark:text-zinc-200 bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 hover:bg-slate-50 dark:hover:bg-zinc-700 hover:text-indigo-600 transition-colors shadow-2xs cursor-pointer"
                                    title="Edit {{ $secTitle }}"
                                >
                                    <svg class="w-3.5 h-3.5 text-indigo-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                                    <span>Edit</span>
                                </button>

                                <!-- Edit Mode: Cancel and Save Buttons (Initially Hidden) -->
                                <div class="section-edit-actions hidden flex items-center gap-1.5">
                                    <button
                                        type="button"
                                        onclick="window.cancelSectionInlineEdit ? window.cancelSectionInlineEdit('{{ $secId }}') : null"
                                        class="section-btn-cancel inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold text-slate-600 dark:text-zinc-300 bg-white dark:bg-zinc-800 border border-slate-300 dark:border-zinc-700 hover:bg-slate-100 dark:hover:bg-zinc-700 transition-colors shadow-2xs cursor-pointer"
                                        title="Cancel Changes"
                                    >
                                        <svg class="w-3.5 h-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                        <span>Cancel</span>
                                    </button>
                                    <button
                                        type="button"
                                        onclick="window.saveSectionInlineEdit ? window.saveSectionInlineEdit('{{ $secId }}') : null"
                                        class="section-btn-save inline-flex items-center gap-1 px-3 py-1 rounded-lg text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 transition-colors shadow-xs cursor-pointer"
                                        title="Save Changes"
                                    >
                                        <svg class="w-3.5 h-3.5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                                        <span>Save</span>
                                    </button>
                                </div>
                            </div>
                            @endif
                        </div>

                        <!-- Section Body -->
                        <div id="{{ $bodyId }}" class="grid {{ $gridColsClass }} pt-4 text-xs {{ $defaultCollapsed ? 'hidden' : '' }}">

                            @if($isSystemSection)
                                <!-- 1. Current Owner with [Change] Button -->
                                <div class="space-y-1">
                                    <div class="flex items-center gap-1.5 text-slate-500 dark:text-zinc-400 text-[11px] font-medium">
                                        <svg class="w-3.5 h-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                        <span>Current Owner</span>
                                    </div>
                                    <div class="flex items-center gap-2 pt-0.5">
                                        <span class="font-bold text-slate-900 dark:text-zinc-100 text-xs">{{ $ownerName }}</span>
                                        @if($profilePermissions['can_transfer'] ?? true)
                                        <button
                                            type="button"
                                            onclick="window.openDetailTransferModal ? window.openDetailTransferModal() : null"
                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold border border-teal-300 dark:border-teal-700 bg-teal-50/70 dark:bg-teal-950/40 text-teal-700 dark:text-teal-300 hover:bg-teal-100 transition-colors shadow-2xs cursor-pointer"
                                            title="Change Lead Owner"
                                        >
                                            <svg class="w-2.5 h-2.5 text-teal-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                                            <span>Change</span>
                                        </button>
                                        @endif
                                    </div>
                                </div>

                                <!-- 2. Created Date & Time -->
                                <div class="space-y-1">
                                    <div class="flex items-center gap-1.5 text-slate-500 dark:text-zinc-400 text-[11px] font-medium">
                                        <svg class="w-3.5 h-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                        <span>Created Date &amp; Time</span>
                                    </div>
                                    <div class="font-bold text-slate-900 dark:text-zinc-100 text-xs pt-0.5">
                                        {{ $createdDate }}
                                    </div>
                                </div>

                                <!-- 3. Created By -->
                                <div class="space-y-1">
                                    <div class="flex items-center gap-1.5 text-slate-500 dark:text-zinc-400 text-[11px] font-medium">
                                        <svg class="w-3.5 h-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                                        <span>Created By</span>
                                    </div>
                                    <div class="font-bold text-slate-900 dark:text-zinc-100 text-xs pt-0.5">
                                        {{ $createdByName }}
                                    </div>
                                </div>

                                <!-- 4. Last Modified Date & Time -->
                                <div class="space-y-1">
                                    <div class="flex items-center gap-1.5 text-slate-500 dark:text-zinc-400 text-[11px] font-medium">
                                        <svg class="w-3.5 h-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                        <span>Last Modified Date &amp; Time</span>
                                    </div>
                                    <div class="font-bold text-slate-900 dark:text-zinc-100 text-xs pt-0.5">
                                        {{ $updatedDate }}
                                    </div>
                                </div>

                                <!-- 5. Last Modified By -->
                                <div class="space-y-1">
                                    <div class="flex items-center gap-1.5 text-slate-500 dark:text-zinc-400 text-[11px] font-medium">
                                        <svg class="w-3.5 h-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                                        <span>Last Modified By</span>
                                    </div>
                                    <div class="font-bold text-slate-900 dark:text-zinc-100 text-xs pt-0.5">
                                        {{ $updatedByName }}
                                    </div>
                                </div>

                                <!-- 6. Record ID -->
                                <div class="space-y-1">
                                    <div class="flex items-center gap-1.5 text-slate-500 dark:text-zinc-400 text-[11px] font-medium">
                                        <svg class="w-3.5 h-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7V4h16v3M9 20h6M12 4v16"/></svg>
                                        <span>Record ID</span>
                                    </div>
                                    <div class="font-mono text-xs font-semibold text-slate-700 dark:text-zinc-300 pt-0.5">
                                        #{{ $lead['id'] ?? $rawLead->id }}
                                    </div>
                                </div>

                            @else
                                <!-- Dynamic Fields in Section -->
                                @foreach($secFields as $f)
                                    @php
                                        $fName = is_array($f) ? ($f['name'] ?? '') : $f;
                                        if (empty($fName)) continue;

                                        $perm = $fieldPermissions[$fName] ?? 'Read/Write';
                                        if ($perm === 'Hidden') continue;

                                        $isRo = ($perm === 'Read-Only') || !empty($f['readOnly']);
                                        $override = $standardOverridesMap[$fName] ?? null;
                                        $cf = $customFieldsMap[$fName] ?? null;

                                        $isReq = !empty($f['required']) || (!empty($override->is_required));
                                        $label = $override->field_label ?? ($f['label'] ?? ($cf->field_label ?? ucwords(str_replace('_', ' ', $fName))));
                                        $isFullSpan = ($secColumns === 1) || in_array($fName, ['notes', 'description']) || ($cf && in_array($cf->data_type, ['textarea', 'rich_text'])) || (!empty($f['col_span']) && ($f['col_span'] === 'full' || (int)$f['col_span'] === 3));
                                        $colSpanClass = (!$isFullSpan && !empty($f['col_span']) && (int)$f['col_span'] === 2 && $secColumns === 3) ? 'sm:col-span-2 lg:col-span-2' : '';
                                        $inputClass = "w-full px-2.5 py-1.5 text-xs rounded-lg border border-slate-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-2xs transition-colors";
                                    @endphp

                                    <div
                                        class="lead-field-wrapper flex items-start justify-between border-b border-slate-100 dark:border-zinc-800/80 pb-2.5 group hover:bg-slate-50/50 dark:hover:bg-zinc-800/30 px-1 rounded transition-colors {{ $isFullSpan ? 'col-span-full' : $colSpanClass }}"
                                        data-field-name="{{ $fName }}"
                                        data-field-label="{{ $label }}"
                                        data-is-required="{{ $isReq ? '1' : '0' }}"
                                    >
                                        <div class="flex-1 min-w-0 pr-2 space-y-0.5">
                                            <label class="text-[11px] font-medium text-slate-500 dark:text-zinc-400 block truncate">
                                                @if($isReq)<span class="text-rose-500 font-bold mr-0.5">*</span>@endif
                                                {{ $label }}
                                            </label>

                                            <!-- 1. Display View (read-only) -->
                                            <div class="lead-field-display" data-field-display="{{ $fName }}">
                                                @if($fName === 'first_name')
                                                    <div class="font-bold text-slate-900 dark:text-zinc-100 text-xs truncate">{{ $lead['first_name'] ?? '—' }}</div>
                                                @elseif($fName === 'last_name')
                                                    <div class="font-bold text-slate-900 dark:text-zinc-100 text-xs truncate">{{ $lead['last_name'] ?? '—' }}</div>
                                                @elseif($fName === 'company')
                                                    <div class="font-bold text-slate-900 dark:text-zinc-100 text-xs truncate">{{ $lead['company'] ?? '—' }}</div>
                                                @elseif($fName === 'title')
                                                    <div class="font-medium text-slate-800 dark:text-zinc-200 text-xs truncate">{{ $lead['title'] ?? '—' }}</div>
                                                @elseif($fName === 'email')
                                                    <div class="text-xs truncate">
                                                        @if(!empty($lead['email']))
                                                            <a href="mailto:{{ $lead['email'] }}" class="text-sky-600 hover:text-sky-700 font-semibold hover:underline">{{ $lead['email'] }}</a>
                                                        @else
                                                            <span class="text-slate-400 italic">—</span>
                                                        @endif
                                                    </div>
                                                @elseif($fName === 'email_opt_out')
                                                    <div class="flex items-center pt-0.5">
                                                        <input type="checkbox" disabled {{ $emailOptOut ? 'checked' : '' }} class="w-4 h-4 rounded border-slate-300 dark:border-zinc-700 text-blue-600 cursor-not-allowed">
                                                    </div>
                                                @elseif($fName === 'phone')
                                                    <div class="font-medium text-slate-800 dark:text-zinc-200 text-xs truncate">
                                                        @if(!empty($lead['phone']))
                                                            <a href="tel:{{ $lead['phone'] }}" class="hover:underline">{{ $lead['phone'] }}</a>
                                                        @else
                                                            <span class="text-slate-400 italic">—</span>
                                                        @endif
                                                    </div>
                                                @elseif($fName === 'status')
                                                    <div class="font-bold text-slate-900 dark:text-zinc-100 text-xs truncate">{{ $status }}</div>
                                                @elseif($fName === 'score')
                                                    <div class="font-bold text-slate-900 dark:text-zinc-100 text-xs truncate">{{ $score }}</div>
                                                @elseif($fName === 'source')
                                                    <div class="font-bold text-slate-900 dark:text-zinc-100 text-xs truncate">{{ $lead['source'] ?? '—' }}</div>
                                                @elseif($fName === 'assigned_to')
                                                    <div class="font-bold text-slate-900 dark:text-zinc-100 text-xs truncate">{{ $ownerName }}</div>
                                                @elseif($fName === 'rating')
                                                    <div class="flex items-center gap-1 text-xs">
                                                        <div class="flex items-center gap-0.5 select-none">
                                                            @for($i = 1; $i <= 5; $i++)
                                                                @if($i <= $ratingVal)
                                                                    <span class="text-amber-500 text-sm leading-none">🔥</span>
                                                                @else
                                                                    <span class="text-orange-200 dark:text-orange-950/60 text-sm leading-none opacity-80">🔥</span>
                                                                @endif
                                                            @endfor
                                                        </div>
                                                        <span class="text-slate-400 font-semibold text-[11px] ml-1">({{ $ratingVal }}/5)</span>
                                                    </div>
                                                @elseif($fName === 'notes')
                                                    <div class="text-xs text-slate-700 dark:text-zinc-300 whitespace-pre-wrap leading-relaxed">
                                                        {{ !empty($lead['notes']) ? $lead['notes'] : '—' }}
                                                    </div>
                                                @elseif($fName === 'id')
                                                    <div class="font-mono text-xs text-slate-600 dark:text-zinc-400">#{{ $lead['id'] ?? $rawLead->id }}</div>
                                                @elseif($fName === 'created_at')
                                                    <div class="font-bold text-slate-900 dark:text-zinc-100 text-xs">{{ $createdDate }}</div>
                                                @elseif($fName === 'created_by')
                                                    <div class="font-bold text-slate-900 dark:text-zinc-100 text-xs">{{ $createdByName }}</div>
                                                @elseif($fName === 'updated_at')
                                                    <div class="font-bold text-slate-900 dark:text-zinc-100 text-xs">{{ $updatedDate }}</div>
                                                @elseif($fName === 'updated_by')
                                                    <div class="font-bold text-slate-900 dark:text-zinc-100 text-xs">{{ $updatedByName }}</div>
                                                @else
                                                    @php
                                                        $val = $lead[$fName] ?? ($lead['custom_data'][$fName] ?? null);
                                                        $resolvedName = $lead[$fName . '_name'] ?? ($lead['custom_data'][$fName . '_name'] ?? ($lead[$fName . '_display'] ?? ($lead['custom_data'][$fName . '_display'] ?? null)));
                                                        $displayVal = !empty($resolvedName) ? $resolvedName : $val;
                                                        $dt = strtolower($cf->data_type ?? 'text');
                                                    @endphp

                                                    @if($dt === 'boolean')
                                                        @php $isBoolChecked = !empty($displayVal) && ($displayVal === true || $displayVal === 1 || $displayVal === '1' || $displayVal === 'true' || $displayVal === 'yes'); @endphp
                                                        <div class="flex items-center pt-0.5">
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold {{ $isBoolChecked ? 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' : 'bg-slate-100 dark:bg-zinc-800 text-slate-500' }}">
                                                                {{ $isBoolChecked ? 'Yes / Active' : 'No / Inactive' }}
                                                            </span>
                                                        </div>
                                                    @elseif($dt === 'rating')
                                                        @php $rNum = (int)$displayVal; @endphp
                                                        <div class="flex items-center gap-0.5 text-amber-500 text-xs select-none">
                                                            @for($si = 1; $si <= 5; $si++)
                                                                <span class="{{ $si <= $rNum ? 'text-amber-500' : 'text-slate-300 dark:text-zinc-700' }}">★</span>
                                                            @endfor
                                                            <span class="text-slate-400 text-[10px] ml-1">({{ $rNum }}/5)</span>
                                                        </div>
                                                    @elseif($dt === 'maskedtext')
                                                        <div class="font-mono text-xs text-slate-600 dark:text-zinc-400">••••••••</div>
                                                    @elseif($dt === 'textarea')
                                                        <div class="text-xs text-slate-700 dark:text-zinc-300 whitespace-pre-wrap leading-relaxed">{{ !empty($displayVal) ? $displayVal : '—' }}</div>
                                                    @else
                                                        <div class="font-medium text-slate-900 dark:text-zinc-100 text-xs truncate">
                                                            {{ !empty($displayVal) ? $displayVal : '—' }}
                                                        </div>
                                                    @endif
                                                @endif
                                            </div>

                                            <!-- 2. Inline Input View (editable, hidden by default) -->
                                            @if(!$isRo && !in_array($fName, ['id', 'created_at', 'updated_at', 'created_by', 'updated_by']))
                                            <div class="lead-field-input hidden pt-1" data-field-input="{{ $fName }}">
                                                @if($fName === 'first_name')
                                                    <input type="text" name="first_name" data-lead-input="first_name" value="{{ $lead['first_name'] ?? '' }}" class="{{ $inputClass }}" placeholder="First Name" {{ $isReq ? 'required' : '' }}>
                                                @elseif($fName === 'last_name')
                                                    <input type="text" name="last_name" data-lead-input="last_name" value="{{ $lead['last_name'] ?? '' }}" class="{{ $inputClass }}" placeholder="Last Name" {{ $isReq ? 'required' : '' }}>
                                                @elseif($fName === 'company')
                                                    <input type="text" name="company" data-lead-input="company" value="{{ $lead['company'] ?? '' }}" class="{{ $inputClass }}" placeholder="Company Name" {{ $isReq ? 'required' : '' }}>
                                                @elseif($fName === 'title')
                                                    <input type="text" name="title" data-lead-input="title" value="{{ $lead['title'] ?? '' }}" class="{{ $inputClass }}" placeholder="Job Title">
                                                @elseif($fName === 'email')
                                                    <input type="email" name="email" data-lead-input="email" value="{{ $lead['email'] ?? '' }}" class="{{ $inputClass }}" placeholder="Email Address" {{ $isReq ? 'required' : '' }}>
                                                @elseif($fName === 'phone')
                                                    <input type="tel" name="phone" data-lead-input="phone" value="{{ $lead['phone'] ?? '' }}" class="{{ $inputClass }}" placeholder="Phone Number">
                                                @elseif($fName === 'status')
                                                    <select name="status" data-lead-input="status" class="{{ $inputClass }}">
                                                        @foreach($stages ?? ['New', 'Contacted', 'Working', 'Qualified', 'Lost'] as $st)
                                                            <option value="{{ $st }}" {{ ($lead['status'] ?? '') === $st ? 'selected' : '' }}>{{ $st }}</option>
                                                        @endforeach
                                                    </select>
                                                @elseif($fName === 'score')
                                                    <input type="number" name="score" min="0" max="100" data-lead-input="score" value="{{ $lead['score'] ?? 50 }}" class="{{ $inputClass }}">
                                                @elseif($fName === 'source')
                                                    <select name="source" data-lead-input="source" class="{{ $inputClass }}">
                                                        <option value="">Select Source...</option>
                                                        @foreach($sources ?? [] as $src)
                                                            <option value="{{ $src }}" {{ ($lead['source'] ?? '') === $src ? 'selected' : '' }}>{{ $src }}</option>
                                                        @endforeach
                                                    </select>
                                                @elseif($fName === 'assigned_to')
                                                    <select name="assigned_to" data-lead-input="assigned_to" class="{{ $inputClass }}">
                                                        <option value="">Select Owner...</option>
                                                        @foreach($users ?? [] as $u)
                                                            <option value="{{ $u->id }}" {{ (string)($lead['assigned_to'] ?? '') === (string)$u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                                                        @endforeach
                                                    </select>
                                                @elseif($fName === 'rating')
                                                    <select name="rating" data-lead-input="rating" class="{{ $inputClass }}">
                                                        <option value="">Select Rating...</option>
                                                        <option value="Hot" {{ strtolower($lead['rating'] ?? '') === 'hot' || (int)($lead['rating'] ?? 0) >= 4 ? 'selected' : '' }}>🔥 Hot (High Priority)</option>
                                                        <option value="Warm" {{ strtolower($lead['rating'] ?? '') === 'warm' || (int)($lead['rating'] ?? 0) === 3 ? 'selected' : '' }}>⚡ Warm (Medium Priority)</option>
                                                        <option value="Cold" {{ strtolower($lead['rating'] ?? '') === 'cold' || ((int)($lead['rating'] ?? 0) > 0 && (int)($lead['rating'] ?? 0) <= 2) ? 'selected' : '' }}>❄ Cold (Low Priority)</option>
                                                    </select>
                                                @elseif($fName === 'email_opt_out')
                                                    <label class="flex items-center gap-2 cursor-pointer pt-0.5">
                                                        <input type="checkbox" name="email_opt_out" value="1" data-lead-input="email_opt_out" {{ $emailOptOut ? 'checked' : '' }} class="w-4 h-4 rounded border-slate-300 dark:border-zinc-700 text-indigo-600 focus:ring-indigo-500">
                                                        <span class="text-xs font-semibold text-slate-700 dark:text-zinc-300">Opt Out of Marketing Emails</span>
                                                    </label>
                                                @elseif($fName === 'notes' || $fName === 'description')
                                                    <textarea name="{{ $fName }}" rows="3" data-lead-input="{{ $fName }}" class="{{ $inputClass }}" placeholder="Enter notes...">{{ $lead[$fName] ?? '' }}</textarea>
                                                @else
                                                    {{-- Custom Field inputs --}}
                                                    @php
                                                        $val = $lead[$fName] ?? ($lead['custom_data'][$fName] ?? '');
                                                        $dt = strtolower($cf->data_type ?? 'text');
                                                        $mapping = $cf ? app(\App\Services\DynamicFields\DynamicFieldMappingService::class)->resolveTargetMapping($cf, 'leads') : null;
                                                    @endphp

                                                    @if($mapping && $mapping['target_module'] === 'users' && !empty($users))
                                                        <select name="cf_{{ $fName }}" data-custom-field="{{ $fName }}" data-lead-input="{{ $fName }}" class="{{ $inputClass }}">
                                                            <option value="">Select User...</option>
                                                            @foreach($users as $u)
                                                                <option value="{{ $u->id }}" {{ (string)$val === (string)$u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    @elseif($dt === 'dropdown' || $dt === 'select')
                                                        @php $opts = $cf && $cf->options ? array_map('trim', explode(',', $cf->options)) : []; @endphp
                                                        <select name="cf_{{ $fName }}" data-custom-field="{{ $fName }}" data-lead-input="{{ $fName }}" class="{{ $inputClass }}">
                                                            <option value="">Select...</option>
                                                            @foreach($opts as $opt)
                                                                <option value="{{ $opt }}" {{ (string)$val === (string)$opt ? 'selected' : '' }}>{{ $opt }}</option>
                                                            @endforeach
                                                        </select>
                                                    @elseif($dt === 'rating')
                                                        <select name="cf_{{ $fName }}" data-custom-field="{{ $fName }}" data-lead-input="{{ $fName }}" class="{{ $inputClass }}">
                                                            <option value="">None</option>
                                                            @for($star = 1; $star <= 5; $star++)
                                                                <option value="{{ $star }}" {{ (int)$val === $star ? 'selected' : '' }}>{{ str_repeat('★', $star) }} ({{ $star }} Stars)</option>
                                                            @endfor
                                                        </select>
                                                    @elseif($dt === 'boolean')
                                                        <label class="flex items-center gap-2 cursor-pointer pt-0.5">
                                                            <input type="checkbox" name="cf_{{ $fName }}" value="1" data-custom-field="{{ $fName }}" data-lead-input="{{ $fName }}" {{ !empty($val) && $val != '0' && $val !== 'false' ? 'checked' : '' }} class="w-4 h-4 rounded border-slate-300 dark:border-zinc-700 text-indigo-600 focus:ring-indigo-500">
                                                            <span class="text-xs font-semibold text-slate-700 dark:text-zinc-300">{{ $label }}</span>
                                                        </label>
                                                    @elseif($dt === 'number' || $dt === 'currency' || $dt === 'decimal' || $dt === 'integer')
                                                        <input type="number" step="any" name="cf_{{ $fName }}" data-custom-field="{{ $fName }}" data-lead-input="{{ $fName }}" value="{{ $val }}" class="{{ $inputClass }}">
                                                    @elseif($dt === 'date')
                                                        <input type="date" name="cf_{{ $fName }}" data-custom-field="{{ $fName }}" data-lead-input="{{ $fName }}" value="{{ $val }}" class="{{ $inputClass }}">
                                                    @elseif($dt === 'datetime')
                                                        <input type="datetime-local" name="cf_{{ $fName }}" data-custom-field="{{ $fName }}" data-lead-input="{{ $fName }}" value="{{ $val }}" class="{{ $inputClass }}">
                                                    @elseif($dt === 'time')
                                                        <input type="time" name="cf_{{ $fName }}" data-custom-field="{{ $fName }}" data-lead-input="{{ $fName }}" value="{{ $val }}" class="{{ $inputClass }}">
                                                    @elseif($dt === 'textarea' || $dt === 'rich_text')
                                                        <textarea rows="3" name="cf_{{ $fName }}" data-custom-field="{{ $fName }}" data-lead-input="{{ $fName }}" class="{{ $inputClass }}">{{ $val }}</textarea>
                                                    @elseif($dt === 'maskedtext')
                                                        <input type="password" name="cf_{{ $fName }}" data-custom-field="{{ $fName }}" data-lead-input="{{ $fName }}" value="{{ $val }}" class="{{ $inputClass }}">
                                                    @else
                                                        <input type="text" name="cf_{{ $fName }}" data-custom-field="{{ $fName }}" data-lead-input="{{ $fName }}" value="{{ $val }}" class="{{ $inputClass }}">
                                                    @endif
                                                @endif
                                            </div>
                                            @endif
                                        </div>

                                        <!-- Edit button or Read-Only Lock -->
                                        @if(($profilePermissions['can_update'] ?? true) && !$isRo && !in_array($fName, ['id', 'created_at', 'updated_at', 'created_by', 'updated_by']))
                                            @if($fName === 'assigned_to')
                                                <button
                                                    type="button"
                                                    onclick="window.openDetailTransferModal ? window.openDetailTransferModal() : null"
                                                    class="lead-field-pencil p-1 rounded text-slate-300 group-hover:text-slate-600 dark:group-hover:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer shrink-0 mt-0.5"
                                                    title="Change Assigned Owner"
                                                >
                                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                                                </button>
                                            @else
                                                <button
                                                    type="button"
                                                    onclick="window.enableSectionInlineEdit ? window.enableSectionInlineEdit('{{ $secId }}', '{{ $fName }}') : null"
                                                    class="lead-field-pencil p-1 rounded text-slate-300 group-hover:text-slate-600 dark:group-hover:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer shrink-0 mt-0.5"
                                                    title="Edit {{ $label }}"
                                                >
                                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                                                </button>
                                            @endif
                                        @elseif($isRo && !in_array($fName, ['id', 'created_at', 'updated_at', 'created_by', 'updated_by']))
                                            <span class="lead-field-lock p-1 text-slate-400 select-none text-[11px] mt-0.5" title="Field is Read-Only">🔒</span>
                                        @endif
                                    </div>
                                @endforeach
                            @endif

                        </div>
                    </section>
                @endforeach

                @php
                    $allLayoutFieldNames = [];
                    foreach ($layoutSections as $s) {
                        foreach ($s['fields'] ?? [] as $fl) {
                            $allLayoutFieldNames[] = is_array($fl) ? ($fl['name'] ?? '') : $fl;
                        }
                    }
                    $remainingCustomFields = collect($customFields ?? [])->filter(function($cf) use ($fieldPermissions, $allLayoutFieldNames) {
                        $fName = $cf->field_name;
                        $p = $fieldPermissions[$fName] ?? 'Read/Write';
                        return $p !== 'Hidden' && !in_array($fName, $allLayoutFieldNames);
                    });
                @endphp
                @if($remainingCustomFields->isNotEmpty())
                    <!-- ADDITIONAL CUSTOM FIELDS SECTION CARD -->
                    <section
                        id="lead-section-sec-custom-fields"
                        data-section-id="sec-custom-fields"
                        class="lead-detail-section bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200/90 dark:border-zinc-800 shadow-xs p-6 transition-all duration-200"
                    >
                        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-zinc-800 select-none gap-3">
                            <div class="flex items-center gap-2 text-slate-900 dark:text-zinc-100 font-bold text-sm">
                                <svg class="w-4 h-4 text-slate-500 dark:text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 2H2v10l9.29 9.29c.94.94 2.48.94 3.42 0l6.58-6.58c.94-.94.94-2.48 0-3.42L12 2Z"/>
                                    <path d="M7 7h.01"/>
                                </svg>
                                <span>Additional Custom Information</span>
                            </div>

                            @if($profilePermissions['can_update'] ?? true)
                            <div class="flex items-center gap-2" data-section-action-wrapper="sec-custom-fields">
                                <button
                                    type="button"
                                    onclick="window.enableSectionInlineEdit ? window.enableSectionInlineEdit('sec-custom-fields') : null"
                                    class="section-btn-edit inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold text-slate-700 dark:text-zinc-200 bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 hover:bg-slate-50 dark:hover:bg-zinc-700 hover:text-indigo-600 transition-colors shadow-2xs cursor-pointer"
                                    title="Edit Custom Fields"
                                >
                                    <svg class="w-3.5 h-3.5 text-indigo-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                                    <span>Edit</span>
                                </button>
                                <div class="section-edit-actions hidden flex items-center gap-1.5">
                                    <button
                                        type="button"
                                        onclick="window.cancelSectionInlineEdit ? window.cancelSectionInlineEdit('sec-custom-fields') : null"
                                        class="section-btn-cancel inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold text-slate-600 dark:text-zinc-300 bg-white dark:bg-zinc-800 border border-slate-300 dark:border-zinc-700 hover:bg-slate-100 dark:hover:bg-zinc-700 transition-colors shadow-2xs cursor-pointer"
                                        title="Cancel Changes"
                                    >
                                        <svg class="w-3.5 h-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                        <span>Cancel</span>
                                    </button>
                                    <button
                                        type="button"
                                        onclick="window.saveSectionInlineEdit ? window.saveSectionInlineEdit('sec-custom-fields') : null"
                                        class="section-btn-save inline-flex items-center gap-1 px-3 py-1 rounded-lg text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 transition-colors shadow-xs cursor-pointer"
                                        title="Save Changes"
                                    >
                                        <svg class="w-3.5 h-3.5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                                        <span>Save</span>
                                    </button>
                                </div>
                            </div>
                            @endif
                        </div>

                        <div id="sectionBody_sec_custom_fields" class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-4 pt-4 text-xs">
                            @foreach($remainingCustomFields as $cf)
                                @php
                                    $fName = $cf->field_name;
                                    $perm = $fieldPermissions[$fName] ?? 'Read/Write';
                                    $isRo = ($perm === 'Read-Only');
                                    $label = $cf->field_label ?? ucwords(str_replace('_', ' ', $fName));
                                    $val = $lead[$fName] ?? ($lead['custom_data'][$fName] ?? '');
                                    $resolvedName = $lead[$fName . '_name'] ?? ($lead['custom_data'][$fName . '_name'] ?? ($lead[$fName . '_display'] ?? ($lead['custom_data'][$fName . '_display'] ?? null)));
                                    $displayVal = !empty($resolvedName) ? $resolvedName : $val;
                                    $dt = strtolower($cf->data_type ?? 'text');
                                    $isFullSpan = in_array($dt, ['textarea', 'rich_text']);
                                    $inputClass = "w-full px-2.5 py-1.5 text-xs rounded-lg border border-slate-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-2xs transition-colors";
                                    $mapping = app(\App\Services\DynamicFields\DynamicFieldMappingService::class)->resolveTargetMapping($cf, 'leads');
                                @endphp

                                <div
                                    class="lead-field-wrapper flex items-start justify-between border-b border-slate-100 dark:border-zinc-800/80 pb-2.5 group hover:bg-slate-50/50 dark:hover:bg-zinc-800/30 px-1 rounded transition-colors {{ $isFullSpan ? 'col-span-full' : '' }}"
                                    data-field-name="{{ $fName }}"
                                    data-field-label="{{ $label }}"
                                    data-is-required="0"
                                >
                                    <div class="flex-1 min-w-0 pr-2 space-y-0.5">
                                        <label class="text-[11px] font-medium text-slate-500 dark:text-zinc-400 block truncate">
                                            {{ $label }}
                                        </label>

                                        <!-- Display View -->
                                        <div class="lead-field-display" data-field-display="{{ $fName }}">
                                            @if($dt === 'boolean')
                                                @php $isBoolChecked = !empty($displayVal) && ($displayVal === true || $displayVal === 1 || $displayVal === '1' || $displayVal === 'true' || $displayVal === 'yes'); @endphp
                                                <div class="flex items-center pt-0.5">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold {{ $isBoolChecked ? 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' : 'bg-slate-100 dark:bg-zinc-800 text-slate-500' }}">
                                                        {{ $isBoolChecked ? 'Yes / Active' : 'No / Inactive' }}
                                                    </span>
                                                </div>
                                            @elseif($dt === 'rating')
                                                @php $rNum = (int)$displayVal; @endphp
                                                <div class="flex items-center gap-0.5 text-amber-500 text-xs select-none">
                                                    @for($si = 1; $si <= 5; $si++)
                                                        <span class="{{ $si <= $rNum ? 'text-amber-500' : 'text-slate-300 dark:text-zinc-700' }}">★</span>
                                                    @endfor
                                                    <span class="text-slate-400 text-[10px] ml-1">({{ $rNum }}/5)</span>
                                                </div>
                                            @elseif($dt === 'maskedtext')
                                                <div class="font-mono text-xs text-slate-600 dark:text-zinc-400">••••••••</div>
                                            @elseif($dt === 'textarea')
                                                <div class="text-xs text-slate-700 dark:text-zinc-300 whitespace-pre-wrap leading-relaxed">{{ !empty($displayVal) ? $displayVal : '—' }}</div>
                                            @else
                                                <div class="font-medium text-slate-900 dark:text-zinc-100 text-xs truncate">
                                                    {{ !empty($displayVal) ? $displayVal : '—' }}
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Inline Input View -->
                                        @if(!$isRo)
                                        <div class="lead-field-input hidden pt-1" data-field-input="{{ $fName }}">
                                            @if($mapping && $mapping['target_module'] === 'users' && !empty($users))
                                                <select name="cf_{{ $fName }}" data-custom-field="{{ $fName }}" data-lead-input="{{ $fName }}" class="{{ $inputClass }}">
                                                    <option value="">Select User...</option>
                                                    @foreach($users as $u)
                                                        <option value="{{ $u->id }}" {{ (string)$val === (string)$u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                                                    @endforeach
                                                </select>
                                            @elseif($dt === 'dropdown' || $dt === 'select')
                                                @php $opts = $cf && $cf->options ? array_map('trim', explode(',', $cf->options)) : []; @endphp
                                                <select name="cf_{{ $fName }}" data-custom-field="{{ $fName }}" data-lead-input="{{ $fName }}" class="{{ $inputClass }}">
                                                    <option value="">Select...</option>
                                                    @foreach($opts as $opt)
                                                        <option value="{{ $opt }}" {{ (string)$val === (string)$opt ? 'selected' : '' }}>{{ $opt }}</option>
                                                    @endforeach
                                                </select>
                                            @elseif($dt === 'rating')
                                                <select name="cf_{{ $fName }}" data-custom-field="{{ $fName }}" data-lead-input="{{ $fName }}" class="{{ $inputClass }}">
                                                    <option value="">None</option>
                                                    @for($star = 1; $star <= 5; $star++)
                                                        <option value="{{ $star }}" {{ (int)$val === $star ? 'selected' : '' }}>{{ str_repeat('★', $star) }} ({{ $star }} Stars)</option>
                                                    @endfor
                                                </select>
                                            @elseif($dt === 'boolean')
                                                <label class="flex items-center gap-2 cursor-pointer pt-0.5">
                                                    <input type="checkbox" name="cf_{{ $fName }}" value="1" data-custom-field="{{ $fName }}" data-lead-input="{{ $fName }}" {{ !empty($val) && $val != '0' && $val !== 'false' ? 'checked' : '' }} class="w-4 h-4 rounded border-slate-300 dark:border-zinc-700 text-indigo-600 focus:ring-indigo-500">
                                                    <span class="text-xs font-semibold text-slate-700 dark:text-zinc-300">{{ $label }}</span>
                                                </label>
                                            @elseif($dt === 'number' || $dt === 'currency' || $dt === 'decimal' || $dt === 'integer')
                                                <input type="number" step="any" name="cf_{{ $fName }}" data-custom-field="{{ $fName }}" data-lead-input="{{ $fName }}" value="{{ $val }}" class="{{ $inputClass }}">
                                            @elseif($dt === 'date')
                                                <input type="date" name="cf_{{ $fName }}" data-custom-field="{{ $fName }}" data-lead-input="{{ $fName }}" value="{{ $val }}" class="{{ $inputClass }}">
                                            @elseif($dt === 'datetime')
                                                <input type="datetime-local" name="cf_{{ $fName }}" data-custom-field="{{ $fName }}" data-lead-input="{{ $fName }}" value="{{ $val }}" class="{{ $inputClass }}">
                                            @elseif($dt === 'time')
                                                <input type="time" name="cf_{{ $fName }}" data-custom-field="{{ $fName }}" data-lead-input="{{ $fName }}" value="{{ $val }}" class="{{ $inputClass }}">
                                            @elseif($dt === 'textarea' || $dt === 'rich_text')
                                                <textarea rows="3" name="cf_{{ $fName }}" data-custom-field="{{ $fName }}" data-lead-input="{{ $fName }}" class="{{ $inputClass }}">{{ $val }}</textarea>
                                            @elseif($dt === 'maskedtext')
                                                <input type="password" name="cf_{{ $fName }}" data-custom-field="{{ $fName }}" data-lead-input="{{ $fName }}" value="{{ $val }}" class="{{ $inputClass }}">
                                            @else
                                                <input type="text" name="cf_{{ $fName }}" data-custom-field="{{ $fName }}" data-lead-input="{{ $fName }}" value="{{ $val }}" class="{{ $inputClass }}">
                                            @endif
                                        </div>
                                        @endif
                                    </div>

                                    @if(!$isRo)
                                        <button
                                            type="button"
                                            onclick="window.enableSectionInlineEdit ? window.enableSectionInlineEdit('sec-custom-fields', '{{ $fName }}') : null"
                                            class="lead-field-pencil p-1 rounded text-slate-300 group-hover:text-slate-600 dark:group-hover:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer shrink-0 mt-0.5"
                                            title="Edit {{ $label }}"
                                        >
                                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                                        </button>
                                    @else
                                        <span class="lead-field-lock p-1 text-slate-400 select-none text-[11px] mt-0.5" title="Field is Read-Only">🔒</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>

            <!-- ==================== RIGHT COLUMN: SUB-TABS & STACKED ACCORDIONS (4 COLS) ==================== -->
            <div class="lg:col-span-4 space-y-3.5">

                <!-- 1. TOP SUB-TABS BAR (Exact Match: Related, Activity 2, Files, Approval History, Lead History) -->
                <div class="flex items-center gap-1.5 p-1 bg-white dark:bg-zinc-900 rounded-xl border border-slate-200/90 dark:border-zinc-800 text-xs font-semibold overflow-x-auto shadow-2xs">
                    <button
                        type="button"
                        onclick="window.switchRightTab ? window.switchRightTab('related') : null"
                        id="subtab-btn-related"
                        class="subtab-item inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-slate-600 dark:text-zinc-400 hover:text-slate-900 hover:bg-slate-100 dark:hover:bg-zinc-800 text-xs font-semibold transition-all cursor-pointer shrink-0"
                    >
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect width="7" height="7" x="3" y="3" rx="1"/>
                            <rect width="7" height="7" x="14" y="3" rx="1"/>
                            <rect width="7" height="7" x="14" y="14" rx="1"/>
                            <rect width="7" height="7" x="3" y="14" rx="1"/>
                        </svg>
                        <span>Related</span>
                    </button>

                    <button
                        type="button"
                        onclick="window.switchRightTab ? window.switchRightTab('activity') : null"
                        id="subtab-btn-activity"
                        class="subtab-item inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#334e68] text-white shadow-xs text-xs font-bold transition-all cursor-pointer shrink-0"
                    >
                        <svg class="w-3.5 h-3.5 text-white" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/>
                        </svg>
                        <span>Activity</span>
                        <span id="tab-activity-badge" class="bg-[#486581] text-white text-[10px] px-1.5 py-0.2 rounded-full font-bold ml-0.5">{{ $activitiesCount }}</span>
                    </button>

                    <button
                        type="button"
                        onclick="window.switchRightTab ? window.switchRightTab('files') : null"
                        id="subtab-btn-files"
                        class="subtab-item inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-slate-600 dark:text-zinc-400 hover:text-slate-900 hover:bg-slate-100 dark:hover:bg-zinc-800 text-xs font-semibold transition-all cursor-pointer shrink-0"
                    >
                        <svg class="w-3.5 h-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l8.57-8.57A4 4 0 1 1 18 8.84l-8.59 8.57a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
                        <span>Files</span>
                        <span id="tab-files-badge" class="bg-slate-200 dark:bg-zinc-700 text-slate-700 dark:text-zinc-200 text-[10px] px-1.5 py-0.2 rounded-full font-bold ml-0.5">{{ $filesCount }}</span>
                    </button>

                    <button
                        type="button"
                        onclick="window.switchRightTab ? window.switchRightTab('approvals') : null"
                        id="subtab-btn-approvals"
                        class="subtab-item inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-slate-600 dark:text-zinc-400 hover:text-slate-900 hover:bg-slate-100 dark:hover:bg-zinc-800 text-xs font-semibold transition-all cursor-pointer shrink-0 text-center"
                    >
                        <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            <path d="m9 12 2 2 4-4"/>
                        </svg>
                        <span class="leading-tight text-center">Approval<br class="hidden sm:inline"> History</span>
                    </button>

                    <button
                        type="button"
                        onclick="window.switchRightTab ? window.switchRightTab('history') : null"
                        id="subtab-btn-history"
                        class="subtab-item inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-slate-600 dark:text-zinc-400 hover:text-slate-900 hover:bg-slate-100 dark:hover:bg-zinc-800 text-xs font-semibold transition-all cursor-pointer shrink-0 text-center"
                    >
                        <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                            <path d="M3 3v5h5"/>
                            <path d="M12 7v5l4 2"/>
                        </svg>
                        <span class="leading-tight text-center">Lead<br class="hidden sm:inline"> History</span>
                    </button>
                </div>

                <!-- ==================== ACTIVITY TAB CONTENT (EXACT 1:1 MATCH OF USER SCREENSHOT) ==================== -->
                <div id="right-tab-content-activity" class="space-y-4">

                    <!-- CARD 1: ACTIVITY LOGGING FORM CARD (Log a Call / New Task / New Event / Add Note) -->
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200/90 dark:border-zinc-800 shadow-xs p-4 sm:p-5 space-y-4">
                        
                        <!-- Form Sub-Navigation Pills -->
                        <div class="flex items-center gap-2 overflow-x-auto pb-1 select-none">
                            <button
                                type="button"
                                onclick="window.switchActivityFormType ? window.switchActivityFormType('Call') : null"
                                id="act-pill-call"
                                class="act-type-pill inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#f3e8ff] text-[#7c3aed] border border-purple-200 dark:bg-purple-950/50 dark:border-purple-800 dark:text-purple-300 font-semibold text-xs shadow-2xs cursor-pointer transition-all shrink-0"
                            >
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                                </svg>
                                <span>Log a Call</span>
                            </button>

                            <button
                                type="button"
                                onclick="window.switchActivityFormType ? window.switchActivityFormType('Task') : null"
                                id="act-pill-task"
                                class="act-type-pill inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 text-slate-700 dark:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-700 font-medium text-xs cursor-pointer transition-all shrink-0"
                            >
                                <svg class="w-3.5 h-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect width="18" height="18" x="3" y="3" rx="2"/><path d="m9 12 2 2 4-4"/>
                                </svg>
                                <span>New Task</span>
                            </button>

                            <button
                                type="button"
                                onclick="window.switchActivityFormType ? window.switchActivityFormType('Event') : null"
                                id="act-pill-event"
                                class="act-type-pill inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 text-slate-700 dark:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-700 font-medium text-xs cursor-pointer transition-all shrink-0"
                            >
                                <svg class="w-3.5 h-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                                </svg>
                                <span>New Event</span>
                            </button>

                            <button
                                type="button"
                                onclick="window.switchActivityFormType ? window.switchActivityFormType('Note') : null"
                                id="act-pill-note"
                                class="act-type-pill inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 text-slate-700 dark:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-700 font-medium text-xs cursor-pointer transition-all shrink-0"
                            >
                                <svg class="w-3.5 h-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>
                                </svg>
                                <span>Add Note</span>
                            </button>
                        </div>

                        <!-- Divider bar beneath sub-navigation -->
                        <div class="h-0.5 bg-slate-200/60 dark:bg-zinc-800 rounded-full my-2"></div>

                        <!-- View 1: Call Logging Form (Default) -->
                        <div id="activity-call-view" class="space-y-3 pt-1">
                            <input type="hidden" id="activity-form-current-type" value="Call">

                            <!-- Two Columns: Subject & Date/Time -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                <div>
                                    <label id="lbl-activity-subject" class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 mb-1.5">
                                        Call Subject
                                    </label>
                                    <input
                                        type="text"
                                        id="activity-input-subject"
                                        placeholder="e.g. Discovery call with..."
                                        class="w-full text-xs px-3 py-2 rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-zinc-100 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-purple-500 transition-colors"
                                    >
                                </div>

                                <div>
                                    <label id="lbl-activity-datetime" class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 mb-1.5">
                                        Date &amp; Time of Call
                                    </label>
                                    <div class="relative">
                                        <input
                                            type="text"
                                            id="activity-input-datetime"
                                            value="{{ now()->format('d-m-Y H:i') }}"
                                            placeholder="DD-MM-YYYY HH:mm"
                                            class="w-full text-xs px-3 py-2 pr-9 rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-purple-500 transition-colors"
                                        >
                                        <div class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Row 2: Description / Discussion Notes -->
                            <div>
                                <label id="lbl-activity-notes" class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 mb-1.5">
                                    Description / Discussion Notes
                                </label>
                                <textarea
                                    id="activity-input-details"
                                    rows="3"
                                    placeholder="Key details, discussion points, or action items..."
                                    class="w-full text-xs px-3 py-2 rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-zinc-100 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-purple-500 transition-colors resize-y"
                                ></textarea>
                            </div>

                            <!-- Action Row (Log Call Button Bottom Right) -->
                            <div class="flex items-center justify-end pt-1">
                                <button
                                    type="button"
                                    onclick="window.submitLeadActivity ? window.submitLeadActivity() : null"
                                    id="btn-activity-submit"
                                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-[#7c3aed] hover:bg-[#6d28d9] text-white text-xs font-semibold shadow-xs cursor-pointer transition-colors"
                                >
                                    <svg id="btn-activity-icon" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                                    </svg>
                                    <span id="btn-activity-text">Log Call</span>
                                </button>
                            </div>
                        </div>

                        <!-- View 2: Task Creation Prompt (Governance Aware) -->
                        <div id="activity-task-view" class="hidden space-y-3 pt-1">
                            @if(!$isApproved)
                            <div class="border-2 border-dashed border-amber-300 dark:border-amber-700/60 rounded-2xl p-6 sm:p-7 flex flex-col items-center justify-center text-center bg-amber-50/50 dark:bg-amber-950/20">
                                <div class="w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center shadow-md shadow-amber-500/25 mb-3.5">
                                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
                                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                    </svg>
                                </div>
                                <h4 class="text-sm sm:text-base font-bold text-amber-950 dark:text-amber-200 mb-1.5">
                                    Task Execution Locked
                                </h4>
                                <p class="text-xs text-amber-700 dark:text-amber-400 max-w-sm mb-5 leading-relaxed">
                                    This lead is currently <strong>{{ $approvalStatus }}</strong>. Per governance workflow, tasks cannot be added or executed until an Administrator or Manager approves this lead.
                                </p>
                                @if($canApproveOrReject)
                                <button
                                    type="button"
                                    onclick="window.handleLeadApprovalAction ? window.handleLeadApprovalAction('approved') : null"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md hover:shadow-lg transition-all cursor-pointer"
                                >
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <polyline points="20 6 9 17 4 12"/>
                                    </svg>
                                    <span>Approve Lead to Unlock Tasks</span>
                                </button>
                                @else
                                <span class="px-4 py-2 rounded-xl bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-300 font-bold text-xs">
                                    Waiting for Manager / Administrator Approval
                                </span>
                                @endif
                            </div>
                            @else
                            <div class="border-2 border-dashed border-slate-200 dark:border-zinc-700 rounded-2xl p-6 sm:p-7 flex flex-col items-center justify-center text-center bg-slate-50/40 dark:bg-zinc-800/20">
                                <!-- Indigo rounded icon square with checkmark -->
                                <div class="w-12 h-12 rounded-2xl bg-[#4f46e5] text-white flex items-center justify-center shadow-md shadow-indigo-500/25 mb-3.5">
                                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <rect width="18" height="18" x="3" y="3" rx="2"/>
                                        <path d="m9 12 2 2 4-4"/>
                                    </svg>
                                </div>

                                <h4 class="text-sm sm:text-base font-bold text-slate-900 dark:text-zinc-100 mb-1.5">
                                    Create Task for this Record
                                </h4>

                                <p class="text-xs text-slate-500 dark:text-zinc-400 max-w-sm mb-5 leading-relaxed">
                                    Open the full Task creation popup with priority, status, assignee, reminders, and recurring scheduling.
                                </p>

                                <button
                                    type="button"
                                    onclick="window.openNewTaskModal ? window.openNewTaskModal() : null"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#7e42d7] hover:bg-[#6d28d9] text-white text-xs font-bold shadow-md hover:shadow-lg transition-all cursor-pointer"
                                >
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                                    </svg>
                                    <span>Add New Task</span>
                                </button>
                            </div>
                            @endif
                        </div>

                        <!-- View 3: New Event Form (Exact 1:1 Match of User Screenshot) -->
                        <div id="activity-event-view" class="hidden space-y-3 pt-1">
                            <!-- Field 1: Event / Meeting Title * -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 mb-1.5">
                                    Event / Meeting Title <span class="text-slate-700 dark:text-zinc-300 font-bold">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="event-input-subject"
                                    placeholder="e.g. Product Architecture Demo &amp; Onboarding"
                                    class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-zinc-100 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-purple-500 transition-colors"
                                >
                            </div>

                            <!-- Row 2: Start Date & Time * and End Date & Time * (2 cols) -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 mb-1.5">
                                        Start Date &amp; Time <span class="text-slate-700 dark:text-zinc-300 font-bold">*</span>
                                    </label>
                                    <div class="relative">
                                        <input
                                            type="text"
                                            id="event-input-start"
                                            value="{{ now()->format('d-m-Y H:i') }}"
                                            placeholder="DD-MM-YYYY HH:mm"
                                            class="w-full text-xs px-3.5 py-2.5 pr-9 rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-purple-500 transition-colors cursor-pointer"
                                        >
                                        <div class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 dark:text-zinc-400 pointer-events-none">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                                            </svg>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 mb-1.5">
                                        End Date &amp; Time <span class="text-slate-700 dark:text-zinc-300 font-bold">*</span>
                                    </label>
                                    <div class="relative">
                                        <input
                                            type="text"
                                            id="event-input-end"
                                            value="{{ now()->addHour()->format('d-m-Y H:i') }}"
                                            placeholder="DD-MM-YYYY HH:mm"
                                            class="w-full text-xs px-3.5 py-2.5 pr-9 rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-purple-500 transition-colors cursor-pointer"
                                        >
                                        <div class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 dark:text-zinc-400 pointer-events-none">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Row 3: Description / Discussion Notes -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 mb-1.5">
                                    Description / Discussion Notes
                                </label>
                                <textarea
                                    id="event-input-details"
                                    rows="3"
                                    placeholder="Key details, discussion points, or action items..."
                                    class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-zinc-100 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-purple-500 transition-colors resize-y"
                                ></textarea>
                            </div>

                            <!-- Row 4: Log Meeting Button (Right aligned) -->
                            <div class="flex items-center justify-end pt-1">
                                <button
                                    type="button"
                                    onclick="window.submitLeadEvent ? window.submitLeadEvent() : null"
                                    id="btn-event-submit"
                                    class="inline-flex items-center gap-2 px-4.5 py-2.5 rounded-xl bg-[#7e42d7] hover:bg-[#6d28d9] text-white text-xs font-bold shadow-xs hover:shadow-md cursor-pointer transition-all"
                                >
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
                                    </svg>
                                    <span>Log Meeting</span>
                                </button>
                            </div>
                        </div>

                    </div>

                    <!-- CARD 2: ACTIVITY HISTORY & TIMELINE CARD -->
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200/90 dark:border-zinc-800 shadow-xs p-4 sm:p-5 space-y-3.5">
                        
                        <!-- Header -->
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-zinc-800 select-none">
                            <div class="flex items-center gap-2">
                                <div class="w-5 h-5 rounded-full flex items-center justify-center text-purple-600 dark:text-purple-400">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                                    </svg>
                                </div>
                                <h3 class="text-xs sm:text-sm font-bold text-slate-800 dark:text-zinc-100">
                                    Activity History &amp; Timeline
                                </h3>
                                <span id="timeline-count-badge" class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#f3e8ff] text-[#7c3aed] dark:bg-purple-950/60 dark:text-purple-300">
                                    {{ $activitiesCount }}
                                </span>
                            </div>

                            <button
                                type="button"
                                onclick="window.refreshLeadActivities ? window.refreshLeadActivities() : null"
                                class="p-1 rounded text-slate-400 hover:text-slate-600 dark:hover:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
                                title="Refresh Timeline"
                            >
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 21h5v-5"/>
                                </svg>
                            </button>
                        </div>

                        <!-- Timeline Items List -->
                        <div id="lead-activities-timeline-list" class="space-y-3">
                            @forelse($activities as $act)
                                @php
                                    $actType = $act->type ?: 'Call';
                                    $typeLower = strtolower($actType);
                                    $isCall = $typeLower === 'call';
                                    $isTask = $typeLower === 'task';
                                    $isEvent = $typeLower === 'event' || $typeLower === 'meeting';
                                    $isNote = $typeLower === 'note';

                                    if ($act->start_date && $act->end_date) {
                                        $callTimeStr = \Carbon\Carbon::parse($act->start_date)->format('M d, Y, h:i A') . ' - ' . \Carbon\Carbon::parse($act->end_date)->format('h:i A');
                                    } elseif ($act->start_date) {
                                        $callTimeStr = \Carbon\Carbon::parse($act->start_date)->format('M d, Y, h:i A');
                                    } elseif ($act->activity_date) {
                                        $callTimeStr = \Carbon\Carbon::parse($act->activity_date)->format('M d, Y');
                                    } else {
                                        $callTimeStr = null;
                                    }

                                    $timeLabel = $isTask ? 'Due Date' : ($isEvent ? 'Meeting Time' : ($isNote ? 'Date' : 'Call Time'));
                                    $createdAtStr = $act->created_at ? \Carbon\Carbon::parse($act->created_at)->format('m/d/y, h:i A') : now()->format('m/d/y, h:i A');
                                    $performerName = $act->user?->name ?? ($ownerName ?? 'Alex Morgan');
                                    
                                    if ($isTask) {
                                        $iconBg = 'bg-purple-100 dark:bg-purple-950/70 text-purple-600 dark:text-purple-400';
                                    } elseif ($isEvent) {
                                        $iconBg = 'bg-purple-100 dark:bg-purple-950/70 text-[#7c3aed] dark:text-purple-300';
                                    } elseif ($isNote) {
                                        $iconBg = 'bg-purple-100 dark:bg-purple-950/70 text-[#7c3aed] dark:text-purple-300';
                                    } else {
                                        $iconBg = 'bg-blue-50 dark:bg-blue-950/60 text-blue-500 dark:text-blue-400';
                                    }
                                @endphp
                                <div class="bg-white dark:bg-zinc-800/80 rounded-xl border border-slate-200/90 dark:border-zinc-700/80 p-3.5 space-y-1.5 transition-shadow hover:shadow-xs">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            <div class="w-8 h-8 rounded-full {{ $iconBg }} flex items-center justify-center shrink-0">
                                                @if($isCall)
                                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                                                    </svg>
                                                @elseif($isTask)
                                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <rect width="18" height="18" x="3" y="3" rx="2"/><path d="m9 12 2 2 4-4"/>
                                                    </svg>
                                                @elseif($isEvent)
                                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                                                    </svg>
                                                @else
                                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>
                                                    </svg>
                                                @endif
                                            </div>
                                            <span class="font-bold text-slate-900 dark:text-zinc-100 text-xs sm:text-sm truncate">
                                                {{ $act->subject }}
                                            </span>
                                        </div>
                                        <span class="text-[11px] text-slate-400 dark:text-zinc-500 shrink-0 font-medium">
                                            {{ $createdAtStr }}
                                        </span>
                                    </div>

                                    <div class="pl-10.5 space-y-1">
                                        @if($callTimeStr)
                                            <div class="text-[11px] text-slate-500 dark:text-zinc-400 flex items-center gap-1.5">
                                                <span>{{ $timeLabel }}: {{ $callTimeStr }}</span>
                                            </div>
                                        @endif

                                        @if(!empty($act->details))
                                            <div class="text-xs text-slate-700 dark:text-zinc-300 leading-relaxed pt-0.5">
                                                {{ $act->details }}
                                            </div>
                                        @endif

                                        <div class="flex items-center gap-1.5 text-[11px] text-slate-400 dark:text-zinc-500 pt-1">
                                            <svg class="w-3 h-3 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                                            </svg>
                                            <span>Logged by {{ $performerName }}</span>
                                            <span>•</span>
                                            <span>{{ $actType }}</span>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div id="timeline-empty-message" class="p-6 border-2 border-dashed border-slate-200 dark:border-zinc-700 rounded-xl text-center space-y-1">
                                    <p class="text-xs font-medium text-slate-400">No activity logged yet.</p>
                                    <p class="text-[11px] text-slate-400">Use the form above to log a call, task, or note.</p>
                                </div>
                            @endforelse
                        </div>

                    </div>

                </div>

                <!-- ==================== FILES TAB CONTENT (1:1 MATCH OF USER SCREENSHOT) ==================== -->
                <div id="right-tab-content-files" class="hidden space-y-4">
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200/90 dark:border-zinc-800 shadow-xs p-4 sm:p-5 space-y-4">
                        
                        <!-- Header: Icon + Files & Attachments + Badge + Add File Button + Refresh Button -->
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-zinc-800">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg bg-purple-50 dark:bg-purple-950/40 text-[#7c3aed] dark:text-purple-400 flex items-center justify-center">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                        <polyline points="14 2 14 8 20 8"/>
                                        <line x1="16" y1="13" x2="8" y2="13"/>
                                        <line x1="16" y1="17" x2="8" y2="17"/>
                                        <polyline points="10 9 9 9 8 9"/>
                                    </svg>
                                </div>
                                <h3 class="text-sm font-bold text-slate-800 dark:text-zinc-100">Files &amp; Attachments</h3>
                                <span id="files-count-badge" class="px-2 py-0.5 text-xs font-bold rounded-full bg-purple-100 text-[#7c3aed] dark:bg-purple-950/60 dark:text-purple-300">
                                    {{ $filesCount }}
                                </span>
                            </div>

                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    onclick="window.triggerAddFile ? window.triggerAddFile() : null"
                                    id="btn-add-file-header"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 hover:bg-slate-50 dark:hover:bg-zinc-700 text-slate-700 dark:text-zinc-200 text-xs font-semibold shadow-2xs transition-all cursor-pointer hover:border-slate-300"
                                >
                                    <span class="text-slate-500 font-bold">+</span>
                                    <span>Add File</span>
                                </button>

                                <button
                                    type="button"
                                    onclick="window.refreshLeadFiles ? window.refreshLeadFiles() : null"
                                    id="btn-refresh-files"
                                    class="p-1.5 rounded-lg border border-slate-200 dark:border-zinc-700 text-slate-400 hover:text-slate-600 dark:hover:text-zinc-300 hover:bg-slate-50 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
                                    title="Refresh Files"
                                >
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                                        <path d="M3 3v5h5"/>
                                        <path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/>
                                        <path d="M16 21h5v-5"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Hidden Native File Input (triggers native Open dialog) -->
                        <input
                            type="file"
                            id="lead-file-input"
                            multiple
                            class="hidden"
                            onchange="window.handleFileUpload ? window.handleFileUpload(event) : null"
                        >

                        <!-- Uploading Status Banner (hidden by default) -->
                        <div id="lead-file-uploading-indicator" class="hidden p-3 rounded-xl bg-purple-50 dark:bg-purple-950/40 border border-purple-200 dark:border-purple-800 flex items-center justify-between text-xs text-purple-700 dark:text-purple-300 animate-pulse">
                            <div class="flex items-center gap-2">
                                <svg class="animate-spin w-4 h-4 text-purple-600" viewBox="0 0 24 24" fill="none">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                <span id="lead-file-uploading-text" class="font-medium">Uploading files...</span>
                            </div>
                            <span class="text-[11px] text-purple-500 font-semibold">Please wait</span>
                        </div>

                        <!-- Files Table -->
                        <div class="overflow-hidden rounded-xl border border-slate-200/90 dark:border-zinc-800 bg-white dark:bg-zinc-900 shadow-2xs">
                            <table class="w-full text-left border-collapse text-xs">
                                <thead>
                                    <tr class="bg-slate-50/90 dark:bg-zinc-800/60 text-slate-500 dark:text-zinc-400 font-semibold border-b border-slate-200 dark:border-zinc-800 select-none">
                                        <th class="py-2.5 px-3.5">Title &amp; Document Name</th>
                                        <th class="py-2.5 px-3.5">Type</th>
                                        <th class="py-2.5 px-3.5 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="lead-files-table-body" class="divide-y divide-slate-100 dark:divide-zinc-800/80">
                                    @forelse ($files ?? [] as $f)
                                        <tr id="lead-file-row-{{ $f['id'] }}" class="hover:bg-slate-50/70 dark:hover:bg-zinc-800/40 transition-colors">
                                            <td class="py-2.5 px-3.5">
                                                <div class="flex items-center gap-2.5">
                                                    @if ($f['icon_type'] === 'excel')
                                                        <div class="w-6 h-6 rounded bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                                                <polyline points="14 2 14 8 20 8"/>
                                                                <path d="M8 13h8M8 17h8M10 9h4"/>
                                                            </svg>
                                                        </div>
                                                    @elseif ($f['icon_type'] === 'pdf')
                                                        <div class="w-6 h-6 rounded bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400 flex items-center justify-center shrink-0">
                                                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                                                <polyline points="14 2 14 8 20 8"/>
                                                                <path d="M9 13v4M12 13v4M15 13v4"/>
                                                            </svg>
                                                        </div>
                                                    @elseif ($f['icon_type'] === 'image')
                                                        <div class="w-6 h-6 rounded bg-teal-50 text-teal-600 dark:bg-teal-950/40 dark:text-teal-400 flex items-center justify-center shrink-0">
                                                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                                <rect width="18" height="18" x="3" y="3" rx="2" ry="2"/>
                                                                <circle cx="9" cy="9" r="2"/>
                                                                <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                                                            </svg>
                                                        </div>
                                                    @else
                                                        <div class="w-6 h-6 rounded bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-zinc-400 flex items-center justify-center shrink-0">
                                                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                                                <polyline points="14 2 14 8 20 8"/>
                                                            </svg>
                                                        </div>
                                                    @endif
                                                    <div class="min-w-0">
                                                        <a href="{{ $f['download_url'] }}" target="_blank" download class="font-semibold text-slate-800 dark:text-zinc-100 hover:text-cyan-600 dark:hover:text-cyan-400 hover:underline truncate block max-w-[200px]" title="{{ $f['name'] }}">
                                                            {{ $f['name'] }}
                                                        </a>
                                                        @if (!empty($f['size_formatted']))
                                                            <span class="text-[10px] text-slate-400">{{ $f['size_formatted'] }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="py-2.5 px-3.5">
                                                <span class="font-mono text-[11px] font-medium text-slate-500 dark:text-zinc-400 uppercase truncate max-w-[170px] block" title="{{ $f['type_label'] }}">
                                                    {{ $f['type_label'] }}
                                                </span>
                                            </td>
                                            <td class="py-2.5 px-3.5 text-right whitespace-nowrap">
                                                <div class="inline-flex items-center gap-1 justify-end">
                                                    <a
                                                        href="{{ $f['download_url'] }}"
                                                        download
                                                        class="p-1 rounded text-slate-400 hover:text-slate-700 dark:hover:text-zinc-200 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors"
                                                        title="Download"
                                                    >
                                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                                            <polyline points="7 10 12 15 17 10"/>
                                                            <line x1="12" y1="15" x2="12" y2="3"/>
                                                        </svg>
                                                    </a>
                                                    <button
                                                        type="button"
                                                        onclick="window.deleteLeadFile ? window.deleteLeadFile({{ $f['id'] }}) : null"
                                                        class="p-1 rounded text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-colors cursor-pointer"
                                                        title="Delete"
                                                    >
                                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <polyline points="3 6 5 6 21 6"/>
                                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr id="lead-files-empty-row">
                                            <td colspan="3" class="p-6 text-center text-slate-400">
                                                <div class="space-y-1">
                                                    <p class="text-xs font-medium">No files attached yet.</p>
                                                    <p class="text-[11px] text-slate-400">Click <span class="font-bold text-slate-600 dark:text-zinc-300">+ Add File</span> to attach spreadsheets, PDFs, or images.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>

                <!-- ==================== APPROVAL HISTORY TAB CONTENT (1:1 MATCH OF USER SCREENSHOT) ==================== -->
                <div id="right-tab-content-approvals" class="hidden space-y-4">

                    <!-- Card 1: Peach/Orange Themed Header Banner Card -->
                    <div class="rounded-2xl p-4 sm:p-5 bg-[#fff7ed] dark:bg-orange-950/20 border border-orange-200/90 dark:border-orange-900/40 shadow-xs flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3 min-w-0">
                            <!-- Orange Square with Shield Check Icon -->
                            <div class="w-10 h-10 rounded-xl bg-[#ea580c] text-white flex items-center justify-center shrink-0 shadow-xs">
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                                    <path d="m9 12 2 2 4-4"/>
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-sm sm:text-base font-bold text-[#7c2d12] dark:text-orange-200 leading-snug">Approval History</h3>
                                <p class="text-xs text-orange-800/80 dark:text-orange-300/80 font-medium mt-0.5">
                                    <span id="approval-history-count-label">{{ $approvalsCount }} items</span> • Sorted by Is Pending • Updated a few seconds ago
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <button
                                type="button"
                                onclick="window.openSubmitApprovalModal ? window.openSubmitApprovalModal() : null"
                                id="btn-submit-approval-header"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-orange-300 dark:border-orange-700 bg-white dark:bg-zinc-800 hover:bg-orange-50 dark:hover:bg-zinc-700 text-orange-800 dark:text-orange-200 text-xs font-semibold shadow-2xs transition-all cursor-pointer hover:border-orange-400"
                            >
                                <span class="text-orange-600 font-bold">+</span>
                                <span>Submit for Approval</span>
                            </button>

                            <button
                                type="button"
                                onclick="window.refreshLeadApprovals ? window.refreshLeadApprovals() : null"
                                id="btn-refresh-approvals"
                                class="p-1.5 rounded-lg border border-orange-200 dark:border-zinc-700 text-orange-600 dark:text-orange-400 hover:bg-orange-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
                                title="Refresh Approvals"
                            >
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                                    <path d="M3 3v5h5"/>
                                    <path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/>
                                    <path d="M16 21h5v-5"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Card 2: Approval History Table (Matches Screenshot Headers & Format) -->
                    <div class="overflow-x-auto rounded-2xl border border-slate-200/90 dark:border-zinc-800 bg-white dark:bg-zinc-900 shadow-2xs">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-slate-50/90 dark:bg-zinc-800/60 text-slate-600 dark:text-zinc-300 font-semibold border-b border-slate-200 dark:border-zinc-800 select-none">
                                    <th class="py-3 px-3.5 whitespace-nowrap">Step Name</th>
                                    <th class="py-3 px-3.5 whitespace-nowrap">Date</th>
                                    <th class="py-3 px-3.5 whitespace-nowrap">Status</th>
                                    <th class="py-3 px-3.5 whitespace-nowrap">Assigned To</th>
                                    <th class="py-3 px-3.5 whitespace-nowrap">Actual Approver</th>
                                    <th class="py-3 px-3.5">Comments</th>
                                </tr>
                            </thead>
                            <tbody id="lead-approvals-table-body" class="divide-y divide-slate-100 dark:divide-zinc-800/80">
                                @forelse ($approvals ?? [] as $appr)
                                    <tr id="lead-approval-row-{{ $appr['id'] }}" class="hover:bg-slate-50/70 dark:hover:bg-zinc-800/40 transition-colors">
                                        <td class="py-3 px-3.5 font-semibold text-slate-800 dark:text-zinc-100 whitespace-nowrap">
                                            {{ $appr['step_name'] }}
                                            @if (strtolower($appr['status']) === 'pending')
                                                <div class="inline-flex items-center gap-1 ml-2">
                                                    <button type="button" onclick="window.handleApprovalDecision({{ $appr['id'] }}, 'approved')" class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 transition-colors cursor-pointer" title="Approve">
                                                        Approve
                                                    </button>
                                                    <button type="button" onclick="window.handleApprovalDecision({{ $appr['id'] }}, 'rejected')" class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 transition-colors cursor-pointer" title="Reject">
                                                        Reject
                                                    </button>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="py-3 px-3.5 text-slate-500 dark:text-zinc-400 whitespace-nowrap">
                                            {{ $appr['date_formatted'] }}
                                        </td>
                                        <td class="py-3 px-3.5 whitespace-nowrap">
                                            @if (strtolower($appr['status']) === 'pending')
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                                    Pending
                                                </span>
                                            @elseif (strtolower($appr['status']) === 'approved')
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                                    ✓ Approved
                                                </span>
                                            @elseif (strtolower($appr['status']) === 'rejected')
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300">
                                                    ✕ Rejected
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 text-slate-700 dark:bg-zinc-800 dark:text-zinc-300">
                                                    {{ $appr['status'] }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-3.5 text-slate-700 dark:text-zinc-200 whitespace-nowrap">
                                            {{ $appr['assigned_to'] }}
                                        </td>
                                        <td class="py-3 px-3.5 text-slate-700 dark:text-zinc-200 whitespace-nowrap">
                                            {{ $appr['actual_approver'] }}
                                        </td>
                                        <td class="py-3 px-3.5 text-slate-500 dark:text-zinc-400 max-w-[200px] truncate" title="{{ $appr['comments'] }}">
                                            {{ $appr['comments'] }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr id="lead-approvals-empty-row">
                                        <td colspan="6" class="p-10 text-center text-slate-500 dark:text-zinc-400">
                                            <p class="text-xs font-semibold text-slate-600 dark:text-zinc-300">No approval history found for this record.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>

                <!-- ==================== LEAD HISTORY TAB CONTENT (1:1 MATCH OF USER SCREENSHOT) ==================== -->
                <div id="right-tab-content-history" class="hidden space-y-4">

                    <!-- Card 1: Sky-Blue Themed Header Banner Card -->
                    <div class="rounded-2xl p-4 sm:p-5 bg-[#f0f9ff] dark:bg-sky-950/20 border border-sky-200 dark:border-sky-800/60 shadow-xs space-y-3">
                        <div class="flex items-center gap-3">
                            <!-- Sky-Blue Square Badge with History Clock Icon -->
                            <div class="w-10 h-10 rounded-xl bg-[#0284c7] text-white flex items-center justify-center shrink-0 shadow-xs">
                                <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                                    <path d="M3 3v5h5"/>
                                    <path d="M12 7v5l4 2"/>
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-base sm:text-lg font-bold text-[#0369a1] dark:text-sky-300 leading-tight">Lead History</h3>
                                <p class="text-xs text-sky-700/90 dark:text-sky-300/80 font-medium mt-0.5">
                                    <span id="lead-history-count-label">{{ $historyCount ?? 0 }} changes recorded</span> • Tracked fields audit trail
                                </p>
                            </div>
                        </div>

                        <!-- Search Bar & Refresh Button Row -->
                        <div class="flex items-center gap-2.5 pt-0.5">
                            <div class="relative flex-1">
                                <input
                                    type="text"
                                    id="lead-history-search-input"
                                    oninput="window.filterLeadHistory ? window.filterLeadHistory() : null"
                                    placeholder="Search history changes..."
                                    class="w-full bg-white dark:bg-zinc-900 text-xs sm:text-sm text-slate-800 dark:text-zinc-100 placeholder-slate-400 dark:placeholder-zinc-500 px-3.5 py-2 rounded-xl border border-sky-200 dark:border-sky-800 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500 shadow-2xs transition-all"
                                />
                            </div>
                            <button
                                type="button"
                                onclick="window.refreshLeadHistory ? window.refreshLeadHistory() : null"
                                id="btn-refresh-history"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white dark:bg-zinc-900 text-xs sm:text-sm font-semibold text-slate-700 dark:text-zinc-200 border border-sky-200 dark:border-sky-800 hover:bg-sky-50 dark:hover:bg-zinc-800 shadow-2xs transition-all cursor-pointer shrink-0"
                                title="Refresh Lead History"
                            >
                                <svg class="w-3.5 h-3.5 text-sky-600 dark:text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                                    <path d="M3 3v5h5"/>
                                    <path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/>
                                    <path d="M16 21h5v-5"/>
                                </svg>
                                <span>Refresh</span>
                            </button>
                        </div>
                    </div>

                    <!-- Card 2: Lead History Table Card -->
                    <div class="rounded-2xl border border-pink-200/60 dark:border-zinc-800 bg-white dark:bg-zinc-900 shadow-2xs overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse text-xs">
                                <thead>
                                    <tr class="bg-slate-50/90 dark:bg-zinc-800/60 text-[#334e68] dark:text-zinc-200 font-bold border-b border-slate-200 dark:border-zinc-800 select-none">
                                        <th class="py-3.5 px-4 whitespace-nowrap leading-tight">Date &<br>Time</th>
                                        <th class="py-3.5 px-4 whitespace-nowrap leading-tight">Field Name</th>
                                        <th class="py-3.5 px-4 whitespace-nowrap leading-tight">User</th>
                                        <th class="py-3.5 px-4 whitespace-nowrap leading-tight">Original<br>Value</th>
                                        <th class="py-3.5 px-4 whitespace-nowrap leading-tight">New<br>Value</th>
                                    </tr>
                                </thead>
                                <tbody id="lead-history-table-body" class="divide-y divide-slate-100 dark:divide-zinc-800/80">
                                    @forelse ($leadHistory ?? [] as $hist)
                                        <tr id="lead-history-row-{{ $hist['id'] }}" class="lead-history-data-row hover:bg-slate-50/70 dark:hover:bg-zinc-800/40 transition-colors">
                                            <td class="py-3.5 px-4 font-medium text-slate-600 dark:text-zinc-400 whitespace-nowrap">
                                                {{ $hist['date_formatted'] }}
                                            </td>
                                            <td class="py-3.5 px-4 font-semibold text-slate-800 dark:text-zinc-100 whitespace-nowrap">
                                                {{ $hist['field_name'] }}
                                            </td>
                                            <td class="py-3.5 px-4 text-slate-700 dark:text-zinc-300 whitespace-nowrap">
                                                {{ $hist['user'] }}
                                            </td>
                                            <td class="py-3.5 px-4 text-slate-600 dark:text-zinc-400 break-words max-w-[200px]">
                                                <span class="inline-block bg-slate-100 dark:bg-zinc-800 px-2 py-0.5 rounded text-slate-600 dark:text-zinc-300 font-mono text-[11px]">{{ $hist['original_value'] }}</span>
                                            </td>
                                            <td class="py-3.5 px-4 text-slate-800 dark:text-zinc-100 font-semibold break-words max-w-[200px]">
                                                <span class="inline-block bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/50 text-emerald-700 dark:text-emerald-300 px-2 py-0.5 rounded font-mono text-[11px]">{{ $hist['new_value'] }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr id="lead-history-empty-row">
                                            <td colspan="5" class="py-12 sm:py-16 text-center px-4">
                                                <p class="text-sm font-medium text-slate-600 dark:text-zinc-400">No field history recorded for this record yet.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

                <!-- ==================== RELATED TAB CONTENT (6 STACKED ACCORDIONS) ==================== -->
                <div id="right-tab-content-related" class="hidden space-y-3.5">

                    <!-- Card 1: Files (0) -->
                    <div id="card-widget-files" class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200/90 dark:border-zinc-800 shadow-2xs p-3.5 transition-all">
                        <div class="flex items-center justify-between gap-2">
                            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-[#334155] text-white text-xs font-bold shadow-2xs">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l8.57-8.57A4 4 0 1 1 18 8.84l-8.59 8.57a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
                                <span id="related-files-badge">Files ({{ $filesCount }})</span>
                                <span class="text-[10px] text-slate-300 font-extrabold ml-0.5">✓</span>
                            </div>

                            <div class="flex items-center gap-1.5">
                                <button
                                    type="button"
                                    onclick="window.triggerAddFile ? window.triggerAddFile() : null"
                                    class="inline-flex items-center px-2.5 py-1 rounded-md border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-700 dark:text-zinc-200 hover:bg-slate-50 text-xs font-semibold shadow-2xs transition-colors cursor-pointer"
                                >
                                    + Add
                                </button>
                                <button type="button" class="p-1 rounded text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors cursor-pointer" title="Expand Widget">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 3 21 3 21 9"/><polyline points="9 21 3 21 3 15"/><line x1="21" y1="3" x2="14" y2="10"/><line x1="3" y1="21" x2="10" y2="14"/></svg>
                                </button>
                                <button
                                    type="button"
                                    onclick="window.toggleSection ? window.toggleSection('filesCardBody', 'filesChevron') : null"
                                    class="p-1 rounded text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors cursor-pointer"
                                >
                                    <svg id="filesChevron" class="w-3.5 h-3.5 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
                                </button>
                            </div>
                        </div>

                        <div id="filesCardBody" class="hidden mt-3 pt-3 border-t border-slate-100 dark:border-zinc-800 text-xs text-slate-500 space-y-2">
                            <div id="related-files-list-container">
                                @if (count($files ?? []) > 0)
                                    <div class="space-y-1.5" id="related-files-items">
                                        @foreach ($files as $f)
                                            <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50 dark:bg-zinc-800/60 border border-slate-200/80 dark:border-zinc-700 text-xs">
                                                <div class="flex items-center gap-2 min-w-0">
                                                    <span class="font-mono text-[10px] uppercase font-bold text-slate-500">{{ $f['type_label'] }}</span>
                                                    <a href="{{ $f['download_url'] }}" target="_blank" download class="font-semibold text-slate-800 dark:text-zinc-100 hover:text-cyan-600 truncate max-w-[180px]" title="{{ $f['name'] }}">
                                                        {{ $f['name'] }}
                                                    </a>
                                                    @if (!empty($f['size_formatted']))
                                                        <span class="text-[10px] text-slate-400">({{ $f['size_formatted'] }})</span>
                                                    @endif
                                                </div>
                                                <div class="flex items-center gap-1 shrink-0">
                                                    <a href="{{ $f['download_url'] }}" download class="p-1 rounded text-slate-400 hover:text-slate-700 dark:hover:text-zinc-200" title="Download">
                                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                                    </a>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="pt-2 text-center">
                                        <button type="button" onclick="window.switchRightTab ? window.switchRightTab('files') : null" class="text-xs font-semibold text-cyan-600 hover:text-cyan-700 dark:text-cyan-400 cursor-pointer hover:underline">
                                            View all files in Files tab &rarr;
                                        </button>
                                    </div>
                                @else
                                    <div class="p-4 border-2 border-dashed border-slate-200 dark:border-zinc-700 rounded-xl text-center space-y-1">
                                        <p class="text-[11px] font-medium text-slate-500">No attached files. Click + Add to upload.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Card 2: Notes & Activity (0) -->
                    <div id="card-widget-activity" class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200/90 dark:border-zinc-800 shadow-2xs p-3.5 transition-all">
                        <div class="flex items-center justify-between gap-2">
                            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-[#e11d48] text-white text-xs font-bold shadow-2xs">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                <span>Notes &amp; Activity ({{ $activitiesCount }})</span>
                                <span class="text-[10px] text-rose-200 font-extrabold ml-0.5">✓</span>
                            </div>

                            <div class="flex items-center gap-1.5">
                                <button
                                    type="button"
                                    onclick="window.triggerLogActivity ? window.triggerLogActivity() : null"
                                    class="inline-flex items-center px-2.5 py-1 rounded-md border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-700 dark:text-zinc-200 hover:bg-slate-50 text-xs font-semibold shadow-2xs transition-colors cursor-pointer"
                                >
                                    + Log
                                </button>
                                <button type="button" class="p-1 rounded text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors cursor-pointer">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 3 21 3 21 9"/><polyline points="9 21 3 21 3 15"/><line x1="21" y1="3" x2="14" y2="10"/><line x1="3" y1="21" x2="10" y2="14"/></svg>
                                </button>
                                <button
                                    type="button"
                                    onclick="window.toggleSection ? window.toggleSection('activityCardBody', 'activityChevron') : null"
                                    class="p-1 rounded text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors cursor-pointer"
                                >
                                    <svg id="activityChevron" class="w-3.5 h-3.5 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
                                </button>
                            </div>
                        </div>

                        <div id="activityCardBody" class="hidden mt-3 pt-3 border-t border-slate-100 dark:border-zinc-800 text-xs text-slate-500">
                            <p class="text-[11px] text-slate-400 italic text-center py-2">No activity records logged yet. Click + Log to create a note or task.</p>
                        </div>
                    </div>

                    <!-- Card 3: Related Lists (0) -->
                    <div id="card-widget-related" class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200/90 dark:border-zinc-800 shadow-2xs p-3.5 transition-all">
                        <div class="flex items-center justify-between gap-2">
                            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-[#0d9488] text-white text-xs font-bold shadow-2xs">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>
                                <span>Related Lists (0)</span>
                                <span class="text-[10px] text-teal-200 font-extrabold ml-0.5">✓</span>
                            </div>

                            <div class="flex items-center gap-1.5">
                                <button type="button" class="p-1 rounded text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors cursor-pointer">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 3 21 3 21 9"/><polyline points="9 21 3 21 3 15"/><line x1="21" y1="3" x2="14" y2="10"/><line x1="3" y1="21" x2="10" y2="14"/></svg>
                                </button>
                                <button
                                    type="button"
                                    onclick="window.toggleSection ? window.toggleSection('relatedCardBody', 'relatedChevron') : null"
                                    class="p-1 rounded text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors cursor-pointer"
                                >
                                    <svg id="relatedChevron" class="w-3.5 h-3.5 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
                                </button>
                            </div>
                        </div>

                        <div id="relatedCardBody" class="hidden mt-3 pt-3 border-t border-slate-100 dark:border-zinc-800 text-xs text-slate-500">
                            <p class="text-[11px] text-slate-400 italic text-center py-2">No related contacts or opportunities linked.</p>
                        </div>
                    </div>

                    <!-- Card 4: Approval History (0) -->
                    <div id="card-widget-approvals" class="bg-[#fff7ed] dark:bg-orange-950/20 rounded-2xl border border-slate-200/90 dark:border-orange-900/40 shadow-2xs p-3.5 transition-all">
                        <div class="flex items-center justify-between gap-2">
                            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-[#ea580c] text-white text-xs font-bold shadow-2xs">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                                <span>Approval History ({{ $approvalsCount }})</span>
                                <span class="text-[10px] text-orange-200 font-extrabold ml-0.5">✓</span>
                            </div>

                            <div class="flex items-center gap-1.5">
                                <button type="button" class="p-1 rounded text-orange-600 hover:bg-orange-100 transition-colors cursor-pointer" title="Edit">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                                </button>
                                <button
                                    type="button"
                                    onclick="window.toggleSection ? window.toggleSection('approvalCardBody', 'approvalChevron') : null"
                                    class="p-1 rounded text-orange-600 hover:bg-orange-100 transition-colors cursor-pointer"
                                >
                                    <svg id="approvalChevron" class="w-3.5 h-3.5 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/></svg>
                                </button>
                            </div>
                        </div>

                        <div id="approvalCardBody" class="hidden mt-3 pt-3 border-t border-orange-200/60 dark:border-orange-900/40 text-xs text-orange-900 dark:text-orange-200">
                            <p class="text-[11px] text-orange-600/80 italic text-center py-2">No active approval processes requested for this lead.</p>
                        </div>
                    </div>

                    <!-- Card 5: Lead History (0) -->
                    <div id="card-widget-history" class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200/90 dark:border-zinc-800 shadow-2xs p-3.5 transition-all">
                        <div class="flex items-center justify-between gap-2">
                            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-[#0284c7] text-white text-xs font-bold shadow-2xs">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                <span>Lead History ({{ $historyCount }})</span>
                                <span class="text-[10px] text-sky-200 font-extrabold ml-0.5">✓</span>
                            </div>

                            <div class="flex items-center gap-1.5">
                                <button type="button" class="p-1 rounded text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors cursor-pointer" title="Edit">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                                </button>
                                <button
                                    type="button"
                                    onclick="window.toggleSection ? window.toggleSection('leadHistoryCardBody', 'leadHistoryChevron') : null"
                                    class="p-1 rounded text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors cursor-pointer"
                                >
                                    <svg id="leadHistoryChevron" class="w-3.5 h-3.5 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/></svg>
                                </button>
                            </div>
                        </div>

                        <div id="leadHistoryCardBody" class="hidden mt-3 pt-3 border-t border-slate-100 dark:border-zinc-800 text-xs text-slate-500">
                            <p class="text-[11px] text-slate-400 italic text-center py-2">No historical field updates recorded.</p>
                        </div>
                    </div>

                    <!-- Card 6: Emails (1) -->
                    <div id="card-widget-emails" class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200/90 dark:border-zinc-800 shadow-2xs p-3.5 transition-all">
                        <div class="flex items-center justify-between gap-2">
                            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-[#0284c7] text-white text-xs font-bold shadow-2xs">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                                <span>Emails ({{ $emailCount }})</span>
                                <span class="text-[10px] text-cyan-200 font-extrabold ml-0.5">✓</span>
                            </div>

                            <div class="flex items-center gap-1.5">
                                <button
                                    type="button"
                                    onclick="window.openQuickEmailModal ? window.openQuickEmailModal() : null"
                                    class="inline-flex items-center px-2.5 py-1 rounded-md border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-700 dark:text-zinc-200 hover:bg-slate-50 text-xs font-semibold shadow-2xs transition-colors cursor-pointer"
                                >
                                    + Compose
                                </button>
                                <button type="button" class="p-1 rounded text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors cursor-pointer" title="Expand Widget">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 3 21 3 21 9"/><polyline points="9 21 3 21 3 15"/><line x1="21" y1="3" x2="14" y2="10"/><line x1="3" y1="21" x2="10" y2="14"/></svg>
                                </button>
                                <button
                                    type="button"
                                    onclick="window.refreshEmailsList ? window.refreshEmailsList() : null"
                                    class="p-1 rounded text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors cursor-pointer"
                                    title="Refresh Emails"
                                >
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                                </button>
                                <button
                                    type="button"
                                    onclick="window.toggleSection ? window.toggleSection('emailsCardBody', 'emailsChevron') : null"
                                    class="p-1 rounded text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors cursor-pointer"
                                >
                                    <svg id="emailsChevron" class="w-3.5 h-3.5 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
                                </button>
                            </div>
                        </div>

                        <div id="emailsCardBody" class="mt-3 pt-3 border-t border-slate-100 dark:border-zinc-800 text-xs text-slate-500">
                            <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-zinc-800/60 border border-slate-200/80 dark:border-zinc-700/60 flex items-center justify-between">
                                <div class="space-y-0.5 truncate">
                                    <div class="font-bold text-slate-900 dark:text-zinc-100 truncate">Welcome &amp; Overview Follow-up</div>
                                    <div class="text-[11px] text-slate-400 truncate">To: {{ $lead['email'] ?? 'invites@events-techb2b.org' }}</div>
                                </div>
                                <span class="text-[10px] text-slate-400 shrink-0 font-medium ml-2">Sent</span>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

        </div>
    </main>

    <!-- NEW TASK MODAL (Exact 1:1 Match with media_1792521156097.png) -->
    <div id="leadNewTaskModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
        <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl border border-slate-200/90 dark:border-zinc-800 w-full max-w-xl overflow-hidden my-auto animate-in fade-in zoom-in-95 duration-150">
            
            <!-- Modal Header -->
            <div class="flex items-start justify-between p-5 border-b border-slate-100 dark:border-zinc-800">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-[#4f46e5] text-white flex items-center justify-center shadow-xs shrink-0">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <rect width="18" height="18" x="3" y="3" rx="2"/>
                            <path d="m9 12 2 2 4-4"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-zinc-100 leading-tight">
                            New Task
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
                            Create and assign an actionable task, follow-up, or reminder.
                        </p>
                    </div>
                </div>

                <button
                    type="button"
                    onclick="window.closeNewTaskModal ? window.closeNewTaskModal() : null"
                    class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-zinc-200 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
                    title="Close"
                >
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>

            <!-- Modal Form Body -->
            <form id="leadNewTaskForm" onsubmit="event.preventDefault(); window.submitNewTaskModal ? window.submitNewTaskModal() : null;" class="p-5 space-y-4">
                
                <!-- Field 1: Subject * -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 mb-1.5">
                        Subject <span class="text-rose-500 font-bold">*</span>
                    </label>
                    <input
                        type="text"
                        id="task-modal-subject"
                        placeholder="e.g. Follow up on proposal pricing, Client onboarding call"
                        required
                        class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-zinc-100 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-purple-500 transition-colors"
                    >
                </div>

                <!-- Row 2: Priority & Status (2 cols) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 mb-1.5">
                            Priority
                        </label>
                        <div class="relative">
                            <select
                                id="task-modal-priority"
                                class="w-full text-xs px-3.5 py-2.5 pr-8 rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-purple-500 transition-colors appearance-none cursor-pointer"
                            >
                                <option value="Normal" selected>Normal</option>
                                <option value="High">High</option>
                                <option value="Low">Low</option>
                                <option value="Urgent">Urgent</option>
                            </select>
                            <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 mb-1.5">
                            Status
                        </label>
                        <div class="relative">
                            <select
                                id="task-modal-status"
                                class="w-full text-xs px-3.5 py-2.5 pr-8 rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-purple-500 transition-colors appearance-none cursor-pointer"
                            >
                                <option value="Open" selected>Open</option>
                                <option value="In Progress">In Progress</option>
                                <option value="Completed">Completed</option>
                                <option value="Deferred">Deferred</option>
                                <option value="Waiting on someone else">Waiting on someone else</option>
                            </select>
                            <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 3: Assigned To (Assignee) & Due Date (2 cols) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 mb-1.5">
                            Assigned To (Assignee)
                        </label>
                        <div class="relative">
                            <select
                                id="task-modal-assignee"
                                class="w-full text-xs px-3.5 py-2.5 pr-8 rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-purple-500 transition-colors appearance-none cursor-pointer"
                            >
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}" {{ (!empty($lead['assigned_to']) && $lead['assigned_to'] == $u->id) || stripos($u->name, 'Alex') !== false ? 'selected' : '' }}>
                                        {{ $u->name }} {{ stripos($u->name, 'Alex') !== false ? '(Executive / CEO)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 mb-1.5">
                            Due Date
                        </label>
                        <div class="relative">
                            <input
                                type="date"
                                id="task-modal-duedate"
                                value="{{ now()->addDays(2)->format('Y-m-d') }}"
                                class="w-full text-xs px-3.5 py-2.5 pr-9 rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-purple-500 transition-colors cursor-pointer"
                            >
                            <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 4: Related Module & Related Record Name (2 cols) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 mb-1.5">
                            Related Module
                        </label>
                        <div class="relative">
                            <select
                                id="task-modal-module"
                                class="w-full text-xs px-3.5 py-2.5 pr-8 rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-purple-500 transition-colors appearance-none cursor-pointer"
                            >
                                <option value="Leads" selected>Leads</option>
                                <option value="Accounts">Accounts</option>
                                <option value="Contacts">Contacts</option>
                                <option value="Opportunities">Opportunities</option>
                            </select>
                            <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 mb-1.5">
                            Related Record Name
                        </label>
                        <div class="relative">
                            <input
                                type="text"
                                id="task-modal-record-name"
                                value="{{ $lead['first_name'] ?? $lead['name'] ?? $lead['company'] ?? 'SaaS' }}"
                                placeholder="Record name"
                                class="w-full text-xs px-3.5 py-2.5 pr-14 rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-purple-500 transition-colors"
                            >
                            <div class="absolute right-2.5 top-1/2 -translate-y-1/2 flex items-center gap-1.5 text-slate-400">
                                <button
                                    type="button"
                                    onclick="document.getElementById('task-modal-record-name').value = ''"
                                    class="p-0.5 hover:text-slate-600 dark:hover:text-zinc-200 transition-colors cursor-pointer"
                                    title="Clear"
                                >
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                </button>
                                <button
                                    type="button"
                                    class="p-0.5 hover:text-slate-600 dark:hover:text-zinc-200 transition-colors cursor-pointer"
                                    title="Search record"
                                >
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 5: Reminder Alert -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 mb-1.5">
                        Reminder Alert
                    </label>
                    <div class="relative">
                        <select
                            id="task-modal-reminder"
                            class="w-full text-xs px-3.5 py-2.5 pr-8 rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-purple-500 transition-colors appearance-none cursor-pointer"
                        >
                            <option value="No reminder is set" selected>No reminder is set</option>
                            <option value="At time of due date">At time of due date</option>
                            <option value="15 minutes before">15 minutes before</option>
                            <option value="30 minutes before">30 minutes before</option>
                            <option value="1 hour before">1 hour before</option>
                            <option value="1 day before">1 day before</option>
                        </select>
                        <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
                        </div>
                    </div>
                </div>

                <!-- Row 6: Comments / Description -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-zinc-300 mb-1.5">
                        Comments / Description
                    </label>
                    <textarea
                        id="task-modal-comments"
                        rows="3"
                        placeholder="Add task notes, discussion points, or customer instructions..."
                        class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-900 dark:text-zinc-100 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-purple-500 transition-colors resize-y"
                    ></textarea>
                </div>

                <!-- Row 7: Create Recurring Series -->
                <div class="p-3 rounded-xl border border-slate-200 dark:border-zinc-700 bg-slate-50/60 dark:bg-zinc-800/40 flex items-center gap-2.5 select-none">
                    <input
                        type="checkbox"
                        id="task-modal-recurring"
                        class="w-4 h-4 rounded text-purple-600 focus:ring-purple-500 border-slate-300 cursor-pointer"
                    >
                    <svg class="w-4 h-4 text-indigo-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="m17 2 4 4-4 4"/><path d="M3 11v-1a4 4 0 0 1 4-4h14"/><path d="m7 22-4-4 4-4"/><path d="M21 13v1a4 4 0 0 1-4 4H3"/>
                    </svg>
                    <label for="task-modal-recurring" class="text-xs font-semibold text-slate-700 dark:text-zinc-300 cursor-pointer">
                        Create Recurring Series
                    </label>
                </div>

                <!-- Footer Actions -->
                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-zinc-800">
                    <button
                        type="button"
                        onclick="window.closeNewTaskModal ? window.closeNewTaskModal() : null"
                        class="px-4 py-2 rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-slate-700 dark:text-zinc-200 hover:bg-slate-50 dark:hover:bg-zinc-700 text-xs font-semibold transition-colors cursor-pointer shadow-2xs"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        onclick="window.submitNewTaskModal ? window.submitNewTaskModal() : null"
                        id="btn-submit-task-modal"
                        class="px-5 py-2 rounded-xl bg-[#7e42d7] hover:bg-[#6d28d9] text-white text-xs font-bold shadow-md hover:shadow-lg transition-all cursor-pointer"
                    >
                        Create Task
                    </button>
                </div>
            </form>

        </div>
    </div>

    <!-- Modal: Submit Record for Approval Modal -->
    <div id="leadApprovalModal" class="hidden fixed inset-0 z-[100000] bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-2xl max-w-md w-full overflow-hidden animate-in fade-in zoom-in duration-200">
            <div class="flex items-center justify-between px-5 py-3.5 bg-orange-50/80 dark:bg-orange-950/40 border-b border-orange-100 dark:border-orange-900/40">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-[#ea580c] text-white flex items-center justify-center shadow-2xs">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            <path d="m9 12 2 2 4-4"/>
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-[#7c2d12] dark:text-orange-200">Submit Record for Approval</h3>
                </div>
                <button type="button" onclick="window.closeSubmitApprovalModal()" class="text-slate-400 hover:text-slate-700 dark:hover:text-zinc-200 text-lg font-bold cursor-pointer">&times;</button>
            </div>
            <form id="submitApprovalForm" onsubmit="window.handleApprovalSubmit(event)" class="p-5 space-y-4 text-xs">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 dark:text-zinc-400 mb-1">Approval Workflow</label>
                    <input type="text" readonly value="Lead Qualification & Discount Approval" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 text-slate-700 dark:text-zinc-300 font-medium">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 dark:text-zinc-400 mb-1">Submission Comments / Justification</label>
                    <textarea id="approvalSubmissionComments" rows="3" required placeholder="Add any comments or justification for the approver..." class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 focus:ring-2 focus:ring-orange-500 focus:outline-hidden"></textarea>
                </div>
                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-zinc-800">
                    <button type="button" onclick="window.closeSubmitApprovalModal()" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-zinc-700 text-slate-600 dark:text-zinc-300 hover:bg-slate-100 text-xs font-semibold cursor-pointer">Cancel</button>
                    <button type="submit" id="btn-submit-approval-confirm" class="px-4 py-2 rounded-xl bg-[#ea580c] hover:bg-[#c2410c] text-white font-bold text-xs shadow-xs cursor-pointer">Submit Request</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Sub-modals for inline actions (Edit, Convert, Transfer, Quick Email) -->
    @include('leads.partials.show_modals')
</div>
