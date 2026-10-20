@extends('layouts.app')

@php
    $leadTitle = (!empty($lead['company']) ? $lead['company'] . ' | ' : '') . ($lead['first_name'] ?? $lead['name'] ?? 'Lead');
    $status = $lead['status'] ?? 'New';
    $score = (int)($lead['score'] ?? 50);
    $ratingVal = (int)($lead['rating'] ?? 0);
    $createdDate = !empty($lead['created_at']) ? \Carbon\Carbon::parse($lead['created_at'])->format('M d, Y, h:i A') : now()->format('M d, Y, h:i A');
    $updatedDate = !empty($lead['updated_at']) ? \Carbon\Carbon::parse($lead['updated_at'])->format('M d, Y, h:i A') : now()->format('M d, Y, h:i A');
    $ownerName = $lead['assigned_to_name'] ?? 'Alex Morgan';
    $createdByName = $lead['created_by_name'] ?? 'System Admin';
    $updatedByName = $lead['updated_by_name'] ?? 'System Admin';
    $emailOptOut = !empty($lead['email_opt_out']) && $lead['email_opt_out'] !== '0' && $lead['email_opt_out'] !== false;
@endphp

@section('title', $leadTitle . ' — Lead Dynamic View — DealSync CRM')

@section('content')
<!-- Dynamic Lead 360 Detail View Container -->
<div id="lead-show-page-root" class="max-w-[1520px] mx-auto w-full px-4 sm:px-6 py-4 space-y-4 animate-fade-in text-zinc-900 dark:text-zinc-100">

    <!-- Top Breadcrumb Navigation Bar (Exact Match with Target Layout) -->
    <nav class="flex items-center gap-2 text-xs font-semibold text-sky-600 dark:text-sky-400 py-1 px-1">
        <a href="/" class="spa-nav-link text-sky-600 dark:text-sky-400 hover:text-sky-700 hover:underline no-underline">DealSync</a>
        <span class="text-zinc-400 font-normal select-none">&gt;</span>
        <a href="/leads" class="spa-nav-link text-sky-600 dark:text-sky-400 hover:text-sky-700 hover:underline no-underline">Leads</a>
        <span class="text-zinc-400 font-normal select-none">&gt;</span>
        <span class="font-bold text-sky-700 dark:text-sky-300 truncate max-w-xl">
            {{ $leadTitle }}
        </span>
    </nav>

    <!-- Feedback Notification Banner (Dynamic via JS) -->
    <div id="detailFeedbackBanner" class="hidden p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 dark:bg-emerald-950/40 dark:border-emerald-800 dark:text-emerald-300 text-xs font-semibold items-center gap-2">
        <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 shrink-0"></i>
        <span id="detailFeedbackText"></span>
    </div>

    <!-- MAIN TWO-COLUMN CONTENT GRID -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">

        <!-- ==================== LEFT COLUMN: RECORD DETAIL SECTIONS (8 COLS) ==================== -->
        <div class="lg:col-span-8 space-y-4">

            <!-- CARD 1: LEAD DETAIL SECTION (Collapsible Card with Glowing Cyan Border) -->
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-cyan-400 shadow-[0_0_15px_rgba(56,189,248,0.22)] p-6 transition-all duration-200">
                <!-- Section Header -->
                <div class="flex items-center justify-between pb-3.5 border-b border-zinc-100 dark:border-zinc-800 select-none">
                    <button
                        type="button"
                        onclick="window.toggleSection('leadDetailSectionBody', 'leadDetailChevron')"
                        class="flex items-center gap-2 text-zinc-900 dark:text-zinc-100 font-bold text-sm hover:text-cyan-700 dark:hover:text-cyan-300 transition-colors cursor-pointer text-left bg-transparent border-0 p-0"
                    >
                        <i data-lucide="chevron-down" id="leadDetailChevron" class="w-4 h-4 text-zinc-500 transition-transform duration-200"></i>
                        <i data-lucide="file-text" class="w-4 h-4 text-zinc-600 dark:text-zinc-400"></i>
                        <span>Lead Detail</span>
                    </button>

                    <!-- Quick Convert / Action Toolbar -->
                    <div class="flex items-center gap-2">
                        @if(empty($lead['is_converted']))
                            <button
                                type="button"
                                onclick="window.openDetailConvertModal()"
                                class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-semibold rounded-md border border-emerald-300 text-emerald-700 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800 transition-colors cursor-pointer"
                                title="Convert Lead"
                            >
                                <i data-lucide="user-check" class="w-3 h-3 text-emerald-600"></i>
                                <span>Convert</span>
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Section Body: 2-Column Responsive Field Grid -->
                <div id="leadDetailSectionBody" class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-4 pt-4 text-xs transition-all duration-200">

                    <!-- Col 1, Row 1: * First Name -->
                    <div class="flex items-center justify-between border-b border-zinc-100/90 dark:border-zinc-800/60 pb-2 group hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 px-1 rounded transition-colors">
                        <div class="space-y-0.5 min-w-0 pr-2">
                            <label class="text-[11px] font-medium text-zinc-600 dark:text-zinc-400 block">
                                <span class="text-zinc-900 dark:text-zinc-200 font-bold">*</span> First Name
                            </label>
                            <div class="font-bold text-zinc-900 dark:text-zinc-100 text-xs truncate">
                                {{ $lead['first_name'] ?? '—' }}
                            </div>
                        </div>
                        <button
                            type="button"
                            onclick="window.openDetailEditModal('first_name')"
                            class="p-1 rounded text-zinc-400 hover:text-cyan-600 dark:hover:text-cyan-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer shrink-0"
                            title="Edit First Name"
                        >
                            <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>

                    <!-- Col 2, Row 1: * Last Name -->
                    <div class="flex items-center justify-between border-b border-zinc-100/90 dark:border-zinc-800/60 pb-2 group hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 px-1 rounded transition-colors">
                        <div class="space-y-0.5 min-w-0 pr-2">
                            <label class="text-[11px] font-medium text-zinc-600 dark:text-zinc-400 block">
                                <span class="text-zinc-900 dark:text-zinc-200 font-bold">*</span> Last Name
                            </label>
                            <div class="font-bold text-zinc-900 dark:text-zinc-100 text-xs truncate">
                                {{ $lead['last_name'] ?? '—' }}
                            </div>
                        </div>
                        <button
                            type="button"
                            onclick="window.openDetailEditModal('last_name')"
                            class="p-1 rounded text-zinc-400 hover:text-cyan-600 dark:hover:text-cyan-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer shrink-0"
                            title="Edit Last Name"
                        >
                            <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>

                    <!-- Col 1, Row 2: * Company Name -->
                    <div class="flex items-center justify-between border-b border-zinc-100/90 dark:border-zinc-800/60 pb-2 group hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 px-1 rounded transition-colors">
                        <div class="space-y-0.5 min-w-0 pr-2">
                            <label class="text-[11px] font-medium text-zinc-600 dark:text-zinc-400 block">
                                <span class="text-zinc-900 dark:text-zinc-200 font-bold">*</span> Company Name
                            </label>
                            <div class="font-bold text-zinc-900 dark:text-zinc-100 text-xs truncate">
                                {{ $lead['company'] ?? '—' }}
                            </div>
                        </div>
                        <button
                            type="button"
                            onclick="window.openDetailEditModal('company')"
                            class="p-1 rounded text-zinc-400 hover:text-cyan-600 dark:hover:text-cyan-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer shrink-0"
                            title="Edit Company Name"
                        >
                            <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>

                    <!-- Col 2, Row 2: Job Title -->
                    <div class="flex items-center justify-between border-b border-zinc-100/90 dark:border-zinc-800/60 pb-2 group hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 px-1 rounded transition-colors">
                        <div class="space-y-0.5 min-w-0 pr-2">
                            <label class="text-[11px] font-medium text-zinc-600 dark:text-zinc-400 block">
                                Job Title
                            </label>
                            <div class="font-medium text-zinc-900 dark:text-zinc-100 text-xs truncate">
                                {{ !empty($lead['title']) ? $lead['title'] : '' }}
                            </div>
                        </div>
                        <button
                            type="button"
                            onclick="window.openDetailEditModal('title')"
                            class="p-1 rounded text-zinc-400 hover:text-cyan-600 dark:hover:text-cyan-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer shrink-0"
                            title="Edit Job Title"
                        >
                            <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>

                    <!-- Col 1, Row 3: * Email Address -->
                    <div class="flex items-center justify-between border-b border-zinc-100/90 dark:border-zinc-800/60 pb-2 group hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 px-1 rounded transition-colors">
                        <div class="space-y-0.5 min-w-0 pr-2">
                            <label class="text-[11px] font-medium text-zinc-600 dark:text-zinc-400 block">
                                <span class="text-zinc-900 dark:text-zinc-200 font-bold">*</span> Email Address
                            </label>
                            <div class="text-xs truncate">
                                @if(!empty($lead['email']))
                                    <a href="mailto:{{ $lead['email'] }}" class="text-sky-600 dark:text-sky-400 font-semibold hover:underline">
                                        {{ $lead['email'] }}
                                    </a>
                                @else
                                    <span class="text-zinc-400 font-normal italic">—</span>
                                @endif
                            </div>
                        </div>
                        <button
                            type="button"
                            onclick="window.openDetailEditModal('email')"
                            class="p-1 rounded text-zinc-400 hover:text-cyan-600 dark:hover:text-cyan-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer shrink-0"
                            title="Edit Email Address"
                        >
                            <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>

                    <!-- Col 2, Row 3: Email Opt Out -->
                    <div class="flex items-center justify-between border-b border-zinc-100/90 dark:border-zinc-800/60 pb-2 group hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 px-1 rounded transition-colors">
                        <div class="space-y-0.5 min-w-0 pr-2">
                            <label class="text-[11px] font-medium text-zinc-600 dark:text-zinc-400 block">
                                Email Opt Out
                            </label>
                            <div class="flex items-center pt-0.5">
                                <input
                                    type="checkbox"
                                    disabled
                                    {{ $emailOptOut ? 'checked' : '' }}
                                    class="w-4 h-4 rounded border-zinc-300 dark:border-zinc-700 text-cyan-600 focus:ring-cyan-500 cursor-not-allowed"
                                >
                            </div>
                        </div>
                        <button
                            type="button"
                            onclick="window.openDetailEditModal('email_opt_out')"
                            class="p-1 rounded text-zinc-400 hover:text-cyan-600 dark:hover:text-cyan-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer shrink-0"
                            title="Edit Email Opt Out"
                        >
                            <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>

                    <!-- Col 1, Row 4: Phone Number -->
                    <div class="flex items-center justify-between border-b border-zinc-100/90 dark:border-zinc-800/60 pb-2 group hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 px-1 rounded transition-colors">
                        <div class="space-y-0.5 min-w-0 pr-2">
                            <label class="text-[11px] font-medium text-zinc-600 dark:text-zinc-400 block">
                                Phone Number
                            </label>
                            <div class="font-medium text-zinc-900 dark:text-zinc-100 text-xs truncate">
                                {{ !empty($lead['phone']) ? $lead['phone'] : '' }}
                            </div>
                        </div>
                        <button
                            type="button"
                            onclick="window.openDetailEditModal('phone')"
                            class="p-1 rounded text-zinc-400 hover:text-cyan-600 dark:hover:text-cyan-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer shrink-0"
                            title="Edit Phone Number"
                        >
                            <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>

                    <!-- Col 2, Row 4: * Status -->
                    <div class="flex items-center justify-between border-b border-zinc-100/90 dark:border-zinc-800/60 pb-2 group hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 px-1 rounded transition-colors">
                        <div class="space-y-0.5 min-w-0 pr-2">
                            <label class="text-[11px] font-medium text-zinc-600 dark:text-zinc-400 block">
                                <span class="text-zinc-900 dark:text-zinc-200 font-bold">*</span> Status
                            </label>
                            <div class="font-bold text-zinc-900 dark:text-zinc-100 text-xs truncate">
                                {{ $status }}
                            </div>
                        </div>
                        <button
                            type="button"
                            onclick="window.openDetailEditModal('status')"
                            class="p-1 rounded text-zinc-400 hover:text-cyan-600 dark:hover:text-cyan-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer shrink-0"
                            title="Edit Status"
                        >
                            <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>

                    <!-- Col 1, Row 5: Lead Score -->
                    <div class="flex items-center justify-between border-b border-zinc-100/90 dark:border-zinc-800/60 pb-2 group hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 px-1 rounded transition-colors">
                        <div class="space-y-0.5 min-w-0 pr-2">
                            <label class="text-[11px] font-medium text-zinc-600 dark:text-zinc-400 block">
                                Lead Score
                            </label>
                            <div class="font-bold text-zinc-900 dark:text-zinc-100 text-xs truncate">
                                {{ $score }}
                            </div>
                        </div>
                        <button
                            type="button"
                            onclick="window.openDetailEditModal('score')"
                            class="p-1 rounded text-zinc-400 hover:text-cyan-600 dark:hover:text-cyan-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer shrink-0"
                            title="Edit Lead Score"
                        >
                            <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>

                    <!-- Col 2, Row 5: Lead Source -->
                    <div class="flex items-center justify-between border-b border-zinc-100/90 dark:border-zinc-800/60 pb-2 group hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 px-1 rounded transition-colors">
                        <div class="space-y-0.5 min-w-0 pr-2">
                            <label class="text-[11px] font-medium text-zinc-600 dark:text-zinc-400 block">
                                Lead Source
                            </label>
                            <div class="font-bold text-zinc-900 dark:text-zinc-100 text-xs truncate">
                                {{ $lead['source'] ?? 'Inbound Email' }}
                            </div>
                        </div>
                        <button
                            type="button"
                            onclick="window.openDetailEditModal('source')"
                            class="p-1 rounded text-zinc-400 hover:text-cyan-600 dark:hover:text-cyan-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer shrink-0"
                            title="Edit Lead Source"
                        >
                            <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>

                    <!-- Col 1, Row 6: Assigned To -->
                    <div class="flex items-center justify-between border-b border-zinc-100/90 dark:border-zinc-800/60 pb-2 group hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 px-1 rounded transition-colors">
                        <div class="space-y-0.5 min-w-0 pr-2">
                            <label class="text-[11px] font-medium text-zinc-600 dark:text-zinc-400 block">
                                Assigned To
                            </label>
                            <div class="font-bold text-zinc-900 dark:text-zinc-100 text-xs truncate">
                                {{ $ownerName }}
                            </div>
                        </div>
                        <button
                            type="button"
                            onclick="window.openDetailTransferModal()"
                            class="p-1 rounded text-zinc-400 hover:text-cyan-600 dark:hover:text-cyan-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer shrink-0"
                            title="Change Assigned Owner"
                        >
                            <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>

                    <!-- Col 2, Row 6: Rating (Flame Icons with 0/5) -->
                    <div class="flex items-center justify-between border-b border-zinc-100/90 dark:border-zinc-800/60 pb-2 group hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 px-1 rounded transition-colors">
                        <div class="space-y-0.5 min-w-0 pr-2">
                            <label class="text-[11px] font-medium text-zinc-600 dark:text-zinc-400 block">
                                Rating
                            </label>
                            <div class="flex items-center gap-1 text-xs">
                                <div class="flex items-center gap-0.5">
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= $ratingVal)
                                            <span class="text-amber-500 text-sm leading-none">🔥</span>
                                        @else
                                            <span class="text-orange-200 dark:text-orange-950/60 text-sm leading-none opacity-80">🔥</span>
                                        @endif
                                    @endfor
                                </div>
                                <span class="text-zinc-500 dark:text-zinc-400 font-semibold text-[11px] ml-1">({{ $ratingVal }}/5)</span>
                            </div>
                        </div>
                        <button
                            type="button"
                            onclick="window.openDetailEditModal('rating')"
                            class="p-1 rounded text-zinc-400 hover:text-cyan-600 dark:hover:text-cyan-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer shrink-0"
                            title="Edit Rating"
                        >
                            <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>

                    <!-- Dynamic Custom Fields (If Any Configured for Profile) -->
                    @if(!empty($customFields))
                        @foreach($customFields as $cf)
                            @php
                                $fName = $cf->field_name;
                                $perm = $fieldPermissions[$fName] ?? 'Read/Write';
                                if ($perm === 'Hidden') continue;
                                $val = $lead[$fName] ?? ($lead['custom_data'][$fName] ?? null);
                            @endphp
                            <div class="flex items-center justify-between border-b border-zinc-100/90 dark:border-zinc-800/60 pb-2 group hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 px-1 rounded transition-colors">
                                <div class="space-y-0.5 min-w-0 pr-2">
                                    <label class="text-[11px] font-medium text-zinc-600 dark:text-zinc-400 block truncate">
                                        {{ $cf->field_label }}
                                    </label>
                                    <div class="font-medium text-zinc-900 dark:text-zinc-100 text-xs truncate">
                                        {{ !empty($val) ? $val : '—' }}
                                    </div>
                                </div>
                                @if($perm !== 'Read-Only')
                                    <button
                                        type="button"
                                        onclick="window.openDetailEditModal('{{ $fName }}')"
                                        class="p-1 rounded text-zinc-400 hover:text-cyan-600 dark:hover:text-cyan-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer shrink-0"
                                        title="Edit {{ $cf->field_label }}"
                                    >
                                        <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                    </button>
                                @else
                                    <i data-lucide="lock" class="w-3 h-3 text-zinc-300 dark:text-zinc-600 shrink-0"></i>
                                @endif
                            </div>
                        @endforeach
                    @endif

                </div>
            </div>

            <!-- CARD 2: SYSTEM INFORMATION (Collapsible Card with Glowing Cyan Border) -->
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-cyan-400 shadow-[0_0_15px_rgba(56,189,248,0.22)] p-6 transition-all duration-200">
                <!-- Section Header -->
                <div class="flex items-center justify-between pb-3.5 border-b border-zinc-100 dark:border-zinc-800 select-none">
                    <button
                        type="button"
                        onclick="window.toggleSection('systemInfoSectionBody', 'systemInfoChevron')"
                        class="flex items-center gap-2 text-zinc-900 dark:text-zinc-100 font-bold text-sm hover:text-cyan-700 dark:hover:text-cyan-300 transition-colors cursor-pointer text-left bg-transparent border-0 p-0"
                    >
                        <i data-lucide="chevron-down" id="systemInfoChevron" class="w-4 h-4 text-zinc-500 transition-transform duration-200"></i>
                        <i data-lucide="shield-check" class="w-4 h-4 text-zinc-600 dark:text-zinc-400"></i>
                        <span>System Information</span>
                    </button>
                </div>

                <!-- Section Body: 3 Columns Grid (Exact Matching System Information Row from Image) -->
                <div id="systemInfoSectionBody" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-y-4 gap-x-8 pt-4 text-xs transition-all duration-200">

                    <!-- 1. Current Owner with [Change] Button -->
                    <div class="space-y-1">
                        <div class="flex items-center gap-1.5 text-zinc-500 dark:text-zinc-400 text-[11px] font-medium">
                            <i data-lucide="user" class="w-3 h-3 text-zinc-400"></i>
                            <span>Current Owner</span>
                        </div>
                        <div class="flex items-center gap-2 pt-0.5">
                            <span class="font-bold text-zinc-950 dark:text-zinc-100 text-xs">{{ $ownerName }}</span>
                            <button
                                type="button"
                                onclick="window.openDetailTransferModal()"
                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-medium border border-teal-300 dark:border-teal-700 bg-teal-50/60 dark:bg-teal-950/40 text-teal-700 dark:text-teal-300 hover:bg-teal-100 dark:hover:bg-teal-900/50 shadow-2xs transition-colors cursor-pointer"
                                title="Change Lead Owner"
                            >
                                <i data-lucide="refresh-cw" class="w-2.5 h-2.5 text-teal-600 dark:text-teal-400"></i>
                                <span>Change</span>
                            </button>
                        </div>
                    </div>

                    <!-- 2. Created Date & Time -->
                    <div class="space-y-1">
                        <div class="flex items-center gap-1.5 text-zinc-500 dark:text-zinc-400 text-[11px] font-medium">
                            <i data-lucide="calendar" class="w-3 h-3 text-zinc-400"></i>
                            <span>Created Date &amp; Time</span>
                        </div>
                        <div class="font-bold text-zinc-950 dark:text-zinc-100 text-xs pt-0.5">
                            {{ $createdDate }}
                        </div>
                    </div>

                    <!-- 3. Created By -->
                    <div class="space-y-1">
                        <div class="flex items-center gap-1.5 text-zinc-500 dark:text-zinc-400 text-[11px] font-medium">
                            <i data-lucide="user-check" class="w-3 h-3 text-zinc-400"></i>
                            <span>Created By</span>
                        </div>
                        <div class="font-bold text-zinc-950 dark:text-zinc-100 text-xs pt-0.5">
                            {{ $createdByName }}
                        </div>
                    </div>

                    <!-- 4. Last Modified Date & Time -->
                    <div class="space-y-1">
                        <div class="flex items-center gap-1.5 text-zinc-500 dark:text-zinc-400 text-[11px] font-medium">
                            <i data-lucide="clock" class="w-3 h-3 text-zinc-400"></i>
                            <span>Last Modified Date &amp; Time</span>
                        </div>
                        <div class="font-bold text-zinc-950 dark:text-zinc-100 text-xs pt-0.5">
                            {{ $updatedDate }}
                        </div>
                    </div>

                    <!-- 5. Last Modified By -->
                    <div class="space-y-1">
                        <div class="flex items-center gap-1.5 text-zinc-500 dark:text-zinc-400 text-[11px] font-medium">
                            <i data-lucide="user-check" class="w-3 h-3 text-zinc-400"></i>
                            <span>Last Modified By</span>
                        </div>
                        <div class="font-bold text-zinc-950 dark:text-zinc-100 text-xs pt-0.5">
                            {{ $updatedByName }}
                        </div>
                    </div>

                </div>
            </div>

        </div>

        <!-- ==================== RIGHT COLUMN: SUB-TABS & STACKED WIDGETS (4 COLS) ==================== -->
        <div class="lg:col-span-4 space-y-3">

            <!-- 1. TOP SUB-TABS BAR (Exact Matching Design from Screenshot) -->
            <div class="flex items-center gap-1.5 p-1 bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 text-xs font-semibold overflow-x-auto shadow-2xs">
                <!-- Related Tab (Active Dark Slate Pill Button) -->
                <button
                    type="button"
                    onclick="window.switchRightTab('related')"
                    id="subtab-btn-related"
                    class="subtab-item inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-[#334155] text-white shadow-xs text-xs font-bold transition-all cursor-pointer shrink-0"
                >
                    <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                    <span>Related</span>
                </button>

                <!-- Activity Tab -->
                <button
                    type="button"
                    onclick="window.switchRightTab('activity')"
                    id="subtab-btn-activity"
                    class="subtab-item inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 hover:bg-zinc-100 dark:hover:bg-zinc-800 text-xs font-semibold transition-all cursor-pointer shrink-0"
                >
                    <i data-lucide="activity" class="w-3.5 h-3.5"></i>
                    <span>Activity</span>
                </button>

                <!-- Files Tab -->
                <button
                    type="button"
                    onclick="window.switchRightTab('files')"
                    id="subtab-btn-files"
                    class="subtab-item inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 hover:bg-zinc-100 dark:hover:bg-zinc-800 text-xs font-semibold transition-all cursor-pointer shrink-0"
                >
                    <i data-lucide="paperclip" class="w-3.5 h-3.5"></i>
                    <span>Files</span>
                </button>

                <!-- Approval History Tab (Coral/Orange text) -->
                <button
                    type="button"
                    onclick="window.switchRightTab('approvals')"
                    id="subtab-btn-approvals"
                    class="subtab-item inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-orange-600 dark:text-orange-400 hover:bg-orange-50 dark:hover:bg-orange-950/30 text-xs font-semibold leading-tight transition-all cursor-pointer shrink-0"
                >
                    <span>Approval<br class="hidden sm:inline"> History</span>
                </button>

                <!-- Lead History Tab -->
                <button
                    type="button"
                    onclick="window.switchRightTab('history')"
                    id="subtab-btn-history"
                    class="subtab-item inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 hover:bg-zinc-100 dark:hover:bg-zinc-800 text-xs font-semibold leading-tight transition-all cursor-pointer shrink-0"
                >
                    <span>Lead<br class="hidden sm:inline"> History</span>
                </button>
            </div>

            <!-- 2. STACKED CARDS WITH GLOWING CYAN BORDERS (Exact 6 Cards) -->

            <!-- CARD 1: Files (0) -->
            <div id="card-widget-files" class="bg-white dark:bg-zinc-900 rounded-2xl border border-cyan-400 shadow-[0_0_12px_rgba(56,189,248,0.18)] p-3.5 transition-all">
                <div class="flex items-center justify-between gap-2">
                    <!-- Left Pill Badge: Charcoal Files (0) ✓ -->
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-[#334155] text-white text-xs font-bold shadow-2xs">
                        <i data-lucide="paperclip" class="w-3.5 h-3.5"></i>
                        <span>Files ({{ $filesCount ?? 0 }})</span>
                        <span class="text-[10px] text-cyan-300 font-extrabold ml-0.5">✓</span>
                    </div>

                    <!-- Right Controls: + Add, Maximize, Collapse -->
                    <div class="flex items-center gap-1.5">
                        <button
                            type="button"
                            onclick="window.triggerAddFile()"
                            class="inline-flex items-center px-2.5 py-1 rounded-md border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 text-xs font-semibold shadow-2xs transition-colors cursor-pointer"
                        >
                            + Add
                        </button>
                        <button
                            type="button"
                            onclick="window.toggleWidgetExpand('filesCardBody')"
                            class="p-1 rounded text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
                            title="Expand Widget"
                        >
                            <i data-lucide="maximize-2" class="w-3.5 h-3.5"></i>
                        </button>
                        <button
                            type="button"
                            onclick="window.toggleSection('filesCardBody', 'filesChevron')"
                            class="p-1 rounded text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
                            title="Collapse Section"
                        >
                            <i data-lucide="chevron-down" id="filesChevron" class="w-3.5 h-3.5 transition-transform"></i>
                        </button>
                    </div>
                </div>

                <!-- Collapsible Content -->
                <div id="filesCardBody" class="hidden mt-3 pt-3 border-t border-zinc-100 dark:border-zinc-800 text-xs text-zinc-500">
                    <div class="p-4 border-2 border-dashed border-zinc-200 dark:border-zinc-700 rounded-xl text-center space-y-1">
                        <i data-lucide="upload-cloud" class="w-6 h-6 mx-auto text-zinc-400"></i>
                        <p class="text-[11px] font-medium text-zinc-600 dark:text-zinc-300">Drag &amp; drop files here, or click to browse</p>
                    </div>
                </div>
            </div>

            <!-- CARD 2: Notes & Activity (0) -->
            <div id="card-widget-activity" class="bg-white dark:bg-zinc-900 rounded-2xl border border-cyan-400 shadow-[0_0_12px_rgba(56,189,248,0.18)] p-3.5 transition-all">
                <div class="flex items-center justify-between gap-2">
                    <!-- Left Pill Badge: Crimson Notes & Activity (0) ✓ -->
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-[#e11d48] text-white text-xs font-bold shadow-2xs">
                        <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                        <span>Notes &amp; Activity ({{ count($activities ?? []) }})</span>
                        <span class="text-[10px] text-rose-200 font-extrabold ml-0.5">✓</span>
                    </div>

                    <!-- Right Controls: + Log, Maximize, Collapse -->
                    <div class="flex items-center gap-1.5">
                        <button
                            type="button"
                            onclick="window.triggerLogActivity()"
                            class="inline-flex items-center px-2.5 py-1 rounded-md border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 text-xs font-semibold shadow-2xs transition-colors cursor-pointer"
                        >
                            + Log
                        </button>
                        <button
                            type="button"
                            onclick="window.toggleWidgetExpand('activityCardBody')"
                            class="p-1 rounded text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
                            title="Expand Widget"
                        >
                            <i data-lucide="maximize-2" class="w-3.5 h-3.5"></i>
                        </button>
                        <button
                            type="button"
                            onclick="window.toggleSection('activityCardBody', 'activityChevron')"
                            class="p-1 rounded text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
                            title="Collapse Section"
                        >
                            <i data-lucide="chevron-down" id="activityChevron" class="w-3.5 h-3.5 transition-transform"></i>
                        </button>
                    </div>
                </div>

                <!-- Collapsible Content -->
                <div id="activityCardBody" class="hidden mt-3 pt-3 border-t border-zinc-100 dark:border-zinc-800 text-xs text-zinc-500">
                    <p class="text-[11px] text-zinc-400 italic text-center py-2">No activity records logged yet. Click + Log to create a note or task.</p>
                </div>
            </div>

            <!-- CARD 3: Related Lists (0) -->
            <div id="card-widget-related" class="bg-white dark:bg-zinc-900 rounded-2xl border border-cyan-400 shadow-[0_0_12px_rgba(56,189,248,0.18)] p-3.5 transition-all">
                <div class="flex items-center justify-between gap-2">
                    <!-- Left Pill Badge: Teal Related Lists (0) ✓ -->
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-[#0d9488] text-white text-xs font-bold shadow-2xs">
                        <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                        <span>Related Lists (0)</span>
                        <span class="text-[10px] text-teal-200 font-extrabold ml-0.5">✓</span>
                    </div>

                    <!-- Right Controls: Maximize, Collapse -->
                    <div class="flex items-center gap-1.5">
                        <button
                            type="button"
                            onclick="window.toggleWidgetExpand('relatedCardBody')"
                            class="p-1 rounded text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
                            title="Expand Widget"
                        >
                            <i data-lucide="maximize-2" class="w-3.5 h-3.5"></i>
                        </button>
                        <button
                            type="button"
                            onclick="window.toggleSection('relatedCardBody', 'relatedChevron')"
                            class="p-1 rounded text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
                            title="Collapse Section"
                        >
                            <i data-lucide="chevron-down" id="relatedChevron" class="w-3.5 h-3.5 transition-transform"></i>
                        </button>
                    </div>
                </div>

                <!-- Collapsible Content -->
                <div id="relatedCardBody" class="hidden mt-3 pt-3 border-t border-zinc-100 dark:border-zinc-800 text-xs text-zinc-500">
                    <p class="text-[11px] text-zinc-400 italic text-center py-2">No related contacts or opportunities linked.</p>
                </div>
            </div>

            <!-- CARD 4: Approval History (0) (Peach Background Highlight per Screenshot) -->
            <div id="card-widget-approvals" class="bg-[#fff7ed] dark:bg-orange-950/20 rounded-2xl border border-cyan-400 shadow-[0_0_12px_rgba(56,189,248,0.18)] p-3.5 transition-all">
                <div class="flex items-center justify-between gap-2">
                    <!-- Left Pill Badge: Orange Approval History (0) ✓ -->
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-[#ea580c] text-white text-xs font-bold shadow-2xs">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                        <span>Approval History ({{ count($approvals ?? []) }})</span>
                        <span class="text-[10px] text-orange-200 font-extrabold ml-0.5">✓</span>
                    </div>

                    <!-- Right Controls: Maximize, Chevron Right -->
                    <div class="flex items-center gap-1.5">
                        <button
                            type="button"
                            onclick="window.toggleWidgetExpand('approvalCardBody')"
                            class="p-1 rounded text-orange-600 dark:text-orange-400 hover:bg-orange-100 dark:hover:bg-orange-900/40 transition-colors cursor-pointer"
                            title="Expand Widget"
                        >
                            <i data-lucide="maximize-2" class="w-3.5 h-3.5"></i>
                        </button>
                        <button
                            type="button"
                            onclick="window.toggleSection('approvalCardBody', 'approvalChevron')"
                            class="p-1 rounded text-orange-600 dark:text-orange-400 hover:bg-orange-100 dark:hover:bg-orange-900/40 transition-colors cursor-pointer"
                            title="Toggle Approval History"
                        >
                            <i data-lucide="chevron-right" id="approvalChevron" class="w-3.5 h-3.5 transition-transform"></i>
                        </button>
                    </div>
                </div>

                <!-- Collapsible Content -->
                <div id="approvalCardBody" class="hidden mt-3 pt-3 border-t border-orange-200/60 dark:border-orange-900/40 text-xs text-orange-900 dark:text-orange-200">
                    <p class="text-[11px] text-orange-600/80 italic text-center py-2">No active approval processes requested for this lead.</p>
                </div>
            </div>

            <!-- CARD 5: Lead History (0) -->
            <div id="card-widget-history" class="bg-white dark:bg-zinc-900 rounded-2xl border border-cyan-400 shadow-[0_0_12px_rgba(56,189,248,0.18)] p-3.5 transition-all">
                <div class="flex items-center justify-between gap-2">
                    <!-- Left Pill Badge: Blue Lead History (0) ✓ -->
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-[#0284c7] text-white text-xs font-bold shadow-2xs">
                        <i data-lucide="history" class="w-3.5 h-3.5"></i>
                        <span>Lead History ({{ count($auditLogs ?? []) }})</span>
                        <span class="text-[10px] text-sky-200 font-extrabold ml-0.5">✓</span>
                    </div>

                    <!-- Right Controls: Maximize, Chevron Right -->
                    <div class="flex items-center gap-1.5">
                        <button
                            type="button"
                            onclick="window.toggleWidgetExpand('leadHistoryCardBody')"
                            class="p-1 rounded text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
                            title="Expand Widget"
                        >
                            <i data-lucide="maximize-2" class="w-3.5 h-3.5"></i>
                        </button>
                        <button
                            type="button"
                            onclick="window.toggleSection('leadHistoryCardBody', 'leadHistoryChevron')"
                            class="p-1 rounded text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
                            title="Toggle Lead History"
                        >
                            <i data-lucide="chevron-right" id="leadHistoryChevron" class="w-3.5 h-3.5 transition-transform"></i>
                        </button>
                    </div>
                </div>

                <!-- Collapsible Content -->
                <div id="leadHistoryCardBody" class="hidden mt-3 pt-3 border-t border-zinc-100 dark:border-zinc-800 text-xs text-zinc-500">
                    <p class="text-[11px] text-zinc-400 italic text-center py-2">No historical field updates recorded.</p>
                </div>
            </div>

            <!-- CARD 6: Emails (1) -->
            <div id="card-widget-emails" class="bg-white dark:bg-zinc-900 rounded-2xl border border-cyan-400 shadow-[0_0_12px_rgba(56,189,248,0.18)] p-3.5 transition-all">
                <div class="flex items-center justify-between gap-2">
                    <!-- Left Pill Badge: Cyan Emails (1) ✓ -->
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-[#0284c7] text-white text-xs font-bold shadow-2xs">
                        <i data-lucide="mail" class="w-3.5 h-3.5"></i>
                        <span>Emails ({{ $emailCount ?? 1 }})</span>
                        <span class="text-[10px] text-cyan-200 font-extrabold ml-0.5">✓</span>
                    </div>

                    <!-- Right Controls: + Compose, Maximize, Refresh, Collapse -->
                    <div class="flex items-center gap-1.5">
                        <button
                            type="button"
                            onclick="window.openQuickEmailModal()"
                            class="inline-flex items-center px-2.5 py-1 rounded-md border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 text-xs font-semibold shadow-2xs transition-colors cursor-pointer"
                        >
                            + Compose
                        </button>
                        <button
                            type="button"
                            onclick="window.toggleWidgetExpand('emailsCardBody')"
                            class="p-1 rounded text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
                            title="Expand Widget"
                        >
                            <i data-lucide="maximize-2" class="w-3.5 h-3.5"></i>
                        </button>
                        <button
                            type="button"
                            onclick="window.refreshEmailsList()"
                            class="p-1 rounded text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
                            title="Refresh Emails"
                        >
                            <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                        </button>
                        <button
                            type="button"
                            onclick="window.toggleSection('emailsCardBody', 'emailsChevron')"
                            class="p-1 rounded text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
                            title="Collapse Section"
                        >
                            <i data-lucide="chevron-down" id="emailsChevron" class="w-3.5 h-3.5 transition-transform"></i>
                        </button>
                    </div>
                </div>

                <!-- Collapsible Content -->
                <div id="emailsCardBody" class="mt-3 pt-3 border-t border-zinc-100 dark:border-zinc-800 text-xs text-zinc-500">
                    <div class="p-2.5 rounded-xl bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200/80 dark:border-zinc-700/60 flex items-center justify-between">
                        <div class="space-y-0.5 truncate">
                            <div class="font-bold text-zinc-900 dark:text-zinc-100 truncate">Welcome &amp; Overview Follow-up</div>
                            <div class="text-[11px] text-zinc-400 truncate">To: {{ $lead['email'] ?? 'invites@events-techb2b.org' }}</div>
                        </div>
                        <span class="text-[10px] text-zinc-400 shrink-0 font-medium ml-2">Sent</span>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <!-- Modals for Edit, Convert, Transfer, Clone, and Quick Email -->
    @include('leads.partials.show_modals')
</div>

<!-- Safe JSON hydration for SPA Navigation & Detail Actions -->
@php
    $leadDetailPayload = [
        'lead' => $lead,
        'customFields' => $customFields ?? [],
        'fieldPermissions' => !empty($fieldPermissions) ? $fieldPermissions : (object)[],
        'users' => $users ?? [],
        'sources' => $sources ?? [],
        'pageLayout' => $pageLayout ?? null,
    ];
@endphp
<script>
    window.__LEAD_DETAIL_DATA__ = {!! json_encode($leadDetailPayload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};
</script>
<script src="{{ asset('js/lead_detail.js') }}?v=3.0"></script>
@endsection
