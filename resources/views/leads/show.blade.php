@extends('layouts.app')

@section('title', ($lead['name'] ?? 'Lead') . ' — Lead 360 View — DealSync CRM')

@section('content')
<!-- Centered Max-Width Container matching React page-wrapper (max-width: 1240px, padding: 2.25rem 2.5rem) -->
<div id="lead-show-page-root" class="max-w-[1240px] mx-auto w-full px-6 sm:px-10 py-7 space-y-6 animate-fade-in">
    <!-- Breadcrumb & Back Action -->
    <div class="flex items-center justify-between gap-4 flex-wrap">
        <div class="flex items-center gap-2 text-xs">
            <a href="/leads" class="spa-nav-link inline-flex items-center gap-1.5 font-semibold text-zinc-500 hover:text-indigo-600 dark:text-zinc-400 dark:hover:text-indigo-400 transition-colors">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Leads Directory</span>
            </a>
            <span class="text-zinc-300 dark:text-zinc-700">/</span>
            <span class="text-zinc-600 dark:text-zinc-400 font-medium">{{ $lead['company'] ?? 'Account' }}</span>
            <span class="text-zinc-300 dark:text-zinc-700">/</span>
            <span class="text-zinc-950 dark:text-zinc-100 font-bold">{{ $lead['name'] }}</span>
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
            <!-- Left: Avatar, Name, Company, Badges -->
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white font-extrabold text-xl flex items-center justify-center shrink-0 shadow-md">
                    {{ strtoupper(substr($lead['first_name'] ?? 'L', 0, 1)) }}
                </div>
                <div class="space-y-1">
                    <div class="flex items-center gap-3 flex-wrap">
                        <h1 class="text-2xl font-extrabold text-zinc-950 dark:text-white tracking-tight">
                            {{ $lead['name'] }}
                        </h1>
                        <!-- Status Badge -->
                        @php
                            $status = $lead['status'] ?? 'New';
                            $statusColors = [
                                'New' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800',
                                'Contacted' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800',
                                'Qualified' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800',
                                'Lost' => 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800',
                            ];
                            $statusClass = $statusColors[$status] ?? 'bg-zinc-100 text-zinc-700 border-zinc-200';
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $statusClass }}">
                            {{ $status }}
                        </span>

                        <!-- Propensity Score Badge -->
                        @php
                            $score = (int)($lead['score'] ?? 50);
                            $scoreClass = $score >= 70
                                ? 'text-emerald-700 bg-emerald-50 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800'
                                : ($score >= 40
                                    ? 'text-amber-700 bg-amber-50 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800'
                                    : 'text-rose-700 bg-rose-50 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800');
                        @endphp
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-bold border {{ $scoreClass }}">
                            <i data-lucide="target" class="w-3.5 h-3.5"></i>
                            <span>Score: {{ $score }}/100</span>
                        </span>

                        <!-- Rating Badge -->
                        @if(!empty($lead['rating']))
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-200 dark:bg-purple-950/40 dark:text-purple-300 dark:border-purple-800">
                                <i data-lucide="zap" class="w-3 h-3 text-purple-500"></i>
                                <span>{{ $lead['rating'] }}</span>
                            </span>
                        @endif
                    </div>

                    <div class="text-xs text-zinc-600 dark:text-zinc-400 flex items-center gap-3 flex-wrap">
                        <span class="font-semibold text-zinc-900 dark:text-zinc-200 flex items-center gap-1">
                            <i data-lucide="building-2" class="w-3.5 h-3.5 text-zinc-400"></i>
                            {{ $lead['company'] ?? '—' }}
                        </span>
                        @if(!empty($lead['title']))
                            <span>•</span>
                            <span>{{ $lead['title'] }}</span>
                        @endif
                        @if(!empty($lead['email']))
                            <span>•</span>
                            <a href="mailto:{{ $lead['email'] }}" class="text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1">
                                <i data-lucide="mail" class="w-3 h-3"></i>
                                <span>{{ $lead['email'] }}</span>
                            </a>
                        @endif
                        @if(!empty($lead['phone']))
                            <span>•</span>
                            <a href="tel:{{ $lead['phone'] }}" class="hover:text-indigo-600 flex items-center gap-1">
                                <i data-lucide="phone" class="w-3 h-3"></i>
                                <span>{{ $lead['phone'] }}</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right: Action Buttons -->
            <div class="flex items-center gap-2 flex-wrap shrink-0">
                @if(empty($lead['is_converted']))
                    <button
                        type="button"
                        onclick="window.openDetailConvertModal()"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-sm transition-colors cursor-pointer"
                    >
                        <i data-lucide="arrow-right-left" class="w-3.5 h-3.5"></i>
                        <span>Convert Lead</span>
                    </button>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800 text-xs font-bold">
                        <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600"></i>
                        <span>Converted</span>
                    </span>
                @endif

                <button
                    type="button"
                    onclick="window.openDetailTransferModal()"
                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs cursor-pointer"
                    title="Transfer lead ownership"
                >
                    <i data-lucide="user-check" class="w-3.5 h-3.5 text-zinc-500"></i>
                    <span>Transfer</span>
                </button>

                <button
                    type="button"
                    onclick="window.openDetailEditModal()"
                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs cursor-pointer"
                >
                    <i data-lucide="edit-2" class="w-3.5 h-3.5 text-zinc-500"></i>
                    <span>Edit</span>
                </button>

                <button
                    type="button"
                    onclick="window.deleteCurrentLead()"
                    class="p-2 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-400 hover:text-rose-600 hover:border-rose-300 dark:hover:bg-zinc-700 transition-colors shadow-2xs cursor-pointer"
                    title="Delete Lead"
                >
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                </button>
            </div>
        </div>

        <!-- KPI Ribbon -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mt-6 pt-5 border-t border-zinc-100 dark:border-zinc-800/80 text-xs">
            <div>
                <span class="text-[11px] font-medium text-zinc-500 dark:text-zinc-400 block mb-0.5">Assigned Owner</span>
                <span class="font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-1">
                    <i data-lucide="user" class="w-3.5 h-3.5 text-indigo-500"></i>
                    <span>{{ $lead['assigned_to_name'] ?? 'Unassigned' }}</span>
                </span>
            </div>

            <div>
                <span class="text-[11px] font-medium text-zinc-500 dark:text-zinc-400 block mb-0.5">Estimated Value</span>
                <span class="font-bold text-zinc-900 dark:text-zinc-100">
                    {{ $lead['lead_value'] ?? '$50,000' }}
                </span>
            </div>

            <div>
                <span class="text-[11px] font-medium text-zinc-500 dark:text-zinc-400 block mb-0.5">Lead Source</span>
                <span class="font-semibold text-zinc-800 dark:text-zinc-200">
                    {{ $lead['source'] ?? 'Website' }}
                </span>
            </div>

            <div>
                <span class="text-[11px] font-medium text-zinc-500 dark:text-zinc-400 block mb-0.5">Rating Priority</span>
                <span class="font-semibold text-purple-700 dark:text-purple-300">
                    {{ $lead['rating'] ?? 'Warm' }}
                </span>
            </div>

            <div>
                <span class="text-[11px] font-medium text-zinc-500 dark:text-zinc-400 block mb-0.5">Stage</span>
                <span class="font-semibold text-zinc-900 dark:text-zinc-100">
                    {{ $lead['status'] ?? 'New' }}
                </span>
            </div>

            <div>
                <span class="text-[11px] font-medium text-zinc-500 dark:text-zinc-400 block mb-0.5">Created Date</span>
                <span class="font-medium text-zinc-600 dark:text-zinc-400">
                    {{ \Carbon\Carbon::parse($lead['created_at'] ?? now())->format('M d, Y') }}
                </span>
            </div>
        </div>
    </div>

    <!-- Main Content 2-Column Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Left Column: Primary Record Details & Dynamic Custom Fields (8 cols) -->
        <div class="lg:col-span-8 space-y-6">
            <!-- 1. Lead Information -->
            <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 border border-zinc-200 dark:border-zinc-800 shadow-xs space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-zinc-100 dark:border-zinc-800">
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-md bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                            <i data-lucide="user" class="w-3.5 h-3.5"></i>
                        </div>
                        <h3 class="text-sm font-bold text-zinc-950 dark:text-zinc-100">Contact &amp; Identification Details</h3>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <span class="text-zinc-500 dark:text-zinc-400 font-medium block mb-1">First Name</span>
                        <div class="p-2.5 rounded-lg bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200/80 dark:border-zinc-700/60 font-semibold text-zinc-900 dark:text-zinc-100">
                            {{ $lead['first_name'] ?? '—' }}
                        </div>
                    </div>

                    <div>
                        <span class="text-zinc-500 dark:text-zinc-400 font-medium block mb-1">Last Name</span>
                        <div class="p-2.5 rounded-lg bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200/80 dark:border-zinc-700/60 font-semibold text-zinc-900 dark:text-zinc-100">
                            {{ $lead['last_name'] ?? '—' }}
                        </div>
                    </div>

                    <div>
                        <span class="text-zinc-500 dark:text-zinc-400 font-medium block mb-1">Company / Operating Business</span>
                        <div class="p-2.5 rounded-lg bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200/80 dark:border-zinc-700/60 font-semibold text-zinc-900 dark:text-zinc-100">
                            {{ $lead['company'] ?? '—' }}
                        </div>
                    </div>

                    <div>
                        <span class="text-zinc-500 dark:text-zinc-400 font-medium block mb-1">Job Designation</span>
                        <div class="p-2.5 rounded-lg bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200/80 dark:border-zinc-700/60 font-semibold text-zinc-900 dark:text-zinc-100">
                            {{ $lead['title'] ?? '—' }}
                        </div>
                    </div>

                    <div>
                        <span class="text-zinc-500 dark:text-zinc-400 font-medium block mb-1">Direct Email Address</span>
                        <div class="p-2.5 rounded-lg bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200/80 dark:border-zinc-700/60 font-semibold text-indigo-600 dark:text-indigo-400 flex items-center justify-between">
                            <span>{{ $lead['email'] ?? '—' }}</span>
                            @if(!empty($lead['email']))
                                <a href="mailto:{{ $lead['email'] }}" class="text-indigo-500 hover:text-indigo-700">
                                    <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                </a>
                            @endif
                        </div>
                    </div>

                    <div>
                        <span class="text-zinc-500 dark:text-zinc-400 font-medium block mb-1">Phone Number</span>
                        <div class="p-2.5 rounded-lg bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200/80 dark:border-zinc-700/60 font-semibold text-zinc-900 dark:text-zinc-100 flex items-center justify-between">
                            <span>{{ $lead['phone'] ?? '—' }}</span>
                            @if(!empty($lead['phone']))
                                <a href="tel:{{ $lead['phone'] }}" class="text-zinc-500 hover:text-indigo-600">
                                    <i data-lucide="phone-call" class="w-3.5 h-3.5"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Dynamic Custom Fields Section (Permissions Enforced) -->
            <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 border border-zinc-200 dark:border-zinc-800 shadow-xs space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-zinc-100 dark:border-zinc-800">
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-md bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center">
                            <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                        </div>
                        <h3 class="text-sm font-bold text-zinc-950 dark:text-zinc-100">Dynamic Custom Fields</h3>
                    </div>
                    <span class="text-[11px] text-zinc-400 font-medium">Field-Level Security Active</span>
                </div>

                @php
                    $visibleCustomFields = collect($customFields ?? [])->filter(function($cf) use ($fieldPermissions) {
                        return ($fieldPermissions[$cf->field_name] ?? 'Read/Write') !== 'Hidden';
                    });
                @endphp

                @if($visibleCustomFields->isEmpty())
                    <div class="py-6 text-center text-zinc-400 text-xs italic">
                        No dynamic custom fields configured or allowed for your security profile.
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        @foreach($visibleCustomFields as $cf)
                            @php
                                $fName = $cf->field_name;
                                $perm = $fieldPermissions[$fName] ?? 'Read/Write';
                                $isReadOnly = ($perm === 'Read-Only');
                                $val = $lead[$fName] ?? ($lead['custom_data'][$fName] ?? null);
                            @endphp
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-zinc-500 dark:text-zinc-400 font-medium">{{ $cf->field_label }}</span>
                                    @if($isReadOnly)
                                        <span class="text-[10px] text-zinc-400 font-semibold inline-flex items-center gap-0.5">
                                            <i data-lucide="lock" class="w-2.5 h-2.5"></i> Read-Only
                                        </span>
                                    @endif
                                </div>

                                <div class="p-2.5 rounded-lg bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200/80 dark:border-zinc-700/60 font-semibold text-zinc-900 dark:text-zinc-100">
                                    @if(is_null($val) || $val === '')
                                        <span class="text-zinc-400 font-normal italic">Not set</span>
                                    @elseif(strtolower($cf->data_type) === 'rating')
                                        @php $stars = (int)$val; @endphp
                                        <div class="flex items-center text-amber-400 gap-0.5" title="{{ $stars }}/5 Stars">
                                            {{ str_repeat('★', min(5, $stars)) }}{{ str_repeat('☆', max(0, 5 - $stars)) }}
                                            <span class="ml-1.5 text-zinc-500 font-medium text-[11px]">({{ $stars }} Stars)</span>
                                        </div>
                                    @elseif(strtolower($cf->data_type) === 'dropdown' || strtolower($cf->data_type) === 'select')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300">
                                            {{ $val }}
                                        </span>
                                    @else
                                        <span>{{ $val }}</span>
                                    @endif
                                </div>
                                @if(!empty($cf->help_text))
                                    <span class="text-[10px] text-zinc-400 mt-0.5 block">{{ $cf->help_text }}</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- 3. Qualification Notes -->
            <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 border border-zinc-200 dark:border-zinc-800 shadow-xs space-y-3">
                <div class="flex items-center gap-2 pb-2 border-b border-zinc-100 dark:border-zinc-800">
                    <i data-lucide="file-text" class="w-4 h-4 text-zinc-400"></i>
                    <h3 class="text-sm font-bold text-zinc-950 dark:text-zinc-100">Qualification &amp; Discovery Notes</h3>
                </div>
                <div class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200/60 dark:border-zinc-800 text-xs text-zinc-700 dark:text-zinc-300 leading-relaxed whitespace-pre-wrap">
                    {{ $lead['notes'] ?? 'No qualification notes recorded yet. Click Edit to add discovery context.' }}
                </div>
            </div>

            <!-- 4. System Governance & Audit Information -->
            <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 border border-zinc-200 dark:border-zinc-800 shadow-xs space-y-3">
                <div class="flex items-center gap-2 pb-2 border-b border-zinc-100 dark:border-zinc-800">
                    <i data-lucide="shield-check" class="w-4 h-4 text-zinc-400"></i>
                    <h3 class="text-sm font-bold text-zinc-950 dark:text-zinc-100">System Information &amp; Audit Trail</h3>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
                    <div>
                        <span class="text-zinc-400 block mb-0.5">Lead Record ID</span>
                        <span class="font-mono font-bold text-zinc-800 dark:text-zinc-200">#{{ $lead['id'] }}</span>
                    </div>
                    <div>
                        <span class="text-zinc-400 block mb-0.5">Conversion State</span>
                        <span class="font-bold {{ !empty($lead['is_converted']) ? 'text-emerald-600' : 'text-zinc-600' }}">
                            {{ !empty($lead['is_converted']) ? 'Converted to Account' : 'Inbound Lead (Unconverted)' }}
                        </span>
                    </div>
                    <div>
                        <span class="text-zinc-400 block mb-0.5">Created At</span>
                        <span class="font-medium text-zinc-700 dark:text-zinc-300">
                            {{ \Carbon\Carbon::parse($lead['created_at'] ?? now())->toDateTimeString() }}
                        </span>
                    </div>
                    <div>
                        <span class="text-zinc-400 block mb-0.5">Last Modified</span>
                        <span class="font-medium text-zinc-700 dark:text-zinc-300">
                            {{ \Carbon\Carbon::parse($lead['updated_at'] ?? now())->diffForHumans() }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Sidebar Actions & Context (4 cols) -->
        <div class="lg:col-span-4 space-y-6">
            <!-- 1. Atomic Lead Conversion Card -->
            <div class="bg-white dark:bg-zinc-900 rounded-2xl p-5 border border-zinc-200 dark:border-zinc-800 shadow-xs space-y-3">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                        <i data-lucide="arrow-right-left" class="w-4 h-4"></i>
                    </div>
                    <h3 class="text-sm font-bold text-zinc-950 dark:text-zinc-100">Atomic Lead Conversion</h3>
                </div>

                @if(empty($lead['is_converted']))
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 leading-relaxed">
                        When this lead is ready, instantly convert it into a permanent <strong>Account</strong> and <strong>Contact</strong> record.
                    </p>

                    <div class="p-3 rounded-xl bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200/80 dark:border-zinc-700/60 text-xs space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="text-zinc-500">Target Account:</span>
                            <span class="font-bold text-zinc-900 dark:text-zinc-100">{{ $lead['company'] ?? 'Account' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-zinc-500">Primary Contact:</span>
                            <span class="font-bold text-zinc-900 dark:text-zinc-100">{{ $lead['name'] }}</span>
                        </div>
                    </div>

                    <button
                        type="button"
                        onclick="window.openDetailConvertModal()"
                        class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-xl shadow-xs transition-colors cursor-pointer inline-flex items-center justify-center gap-2"
                    >
                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                        <span>Convert Lead Now</span>
                    </button>
                @else
                    <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/60 space-y-2">
                        <div class="flex items-center gap-2 text-emerald-800 dark:text-emerald-200 font-bold text-xs">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600"></i>
                            <span>Lead Converted Successfully</span>
                        </div>
                        <p class="text-xs text-emerald-700 dark:text-emerald-300">
                            Core CRM records were atomically generated from this lead:
                        </p>
                        <div class="text-xs space-y-1 pt-1 font-semibold text-emerald-900 dark:text-emerald-100">
                            <div>• Account ID: #{{ $lead['converted_account_id'] ?? 'Linked' }}</div>
                            <div>• Contact ID: #{{ $lead['converted_contact_id'] ?? 'Linked' }}</div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- 2. Lead Owner Card -->
            <div class="bg-white dark:bg-zinc-900 rounded-2xl p-5 border border-zinc-200 dark:border-zinc-800 shadow-xs space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider">Assigned Owner</span>
                    <button
                        type="button"
                        onclick="window.openDetailTransferModal()"
                        class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline cursor-pointer"
                    >
                        Transfer
                    </button>
                </div>

                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-950/80 text-indigo-700 dark:text-indigo-300 font-bold text-sm flex items-center justify-center shrink-0">
                        {{ strtoupper(substr($lead['assigned_to_name'] ?? 'U', 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <div class="font-bold text-xs text-zinc-900 dark:text-zinc-100 truncate">
                            {{ $lead['assigned_to_name'] ?? 'Unassigned' }}
                        </div>
                        <div class="text-[11px] text-zinc-500 dark:text-zinc-400">
                            {{ $lead['assigned_user']['email'] ?? 'Sales Representative' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Quick Actions -->
            <div class="bg-white dark:bg-zinc-900 rounded-2xl p-5 border border-zinc-200 dark:border-zinc-800 shadow-xs space-y-3">
                <span class="text-xs font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider block">Quick Outreach</span>
                <div class="space-y-2 text-xs">
                    @if(!empty($lead['email']))
                        <a
                            href="mailto:{{ $lead['email'] }}"
                            class="w-full flex items-center justify-between p-2.5 rounded-xl border border-zinc-200 dark:border-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors text-zinc-700 dark:text-zinc-300 font-semibold"
                        >
                            <div class="flex items-center gap-2">
                                <i data-lucide="mail" class="w-4 h-4 text-indigo-500"></i>
                                <span>Send Direct Email</span>
                            </div>
                            <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400"></i>
                        </a>
                    @endif

                    @if(!empty($lead['phone']))
                        <a
                            href="tel:{{ $lead['phone'] }}"
                            class="w-full flex items-center justify-between p-2.5 rounded-xl border border-zinc-200 dark:border-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors text-zinc-700 dark:text-zinc-300 font-semibold"
                        >
                            <div class="flex items-center gap-2">
                                <i data-lucide="phone" class="w-4 h-4 text-emerald-500"></i>
                                <span>Call Contact</span>
                            </div>
                            <i data-lucide="chevron-right" class="w-4 h-4 text-zinc-400"></i>
                        </a>
                    @endif

                    <a
                        href="https://www.google.com/search?q={{ urlencode(($lead['company'] ?? '') . ' ' . ($lead['name'] ?? '')) }}"
                        target="_blank"
                        rel="noreferrer"
                        class="w-full flex items-center justify-between p-2.5 rounded-xl border border-zinc-200 dark:border-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors text-zinc-700 dark:text-zinc-300 font-semibold"
                    >
                        <div class="flex items-center gap-2">
                            <i data-lucide="globe" class="w-4 h-4 text-blue-500"></i>
                            <span>Search Corporate Profile</span>
                        </div>
                        <i data-lucide="external-link" class="w-3.5 h-3.5 text-zinc-400"></i>
                    </a>
                </div>
            </div>

            <!-- 4. Touchpoint Timeline -->
            <div class="bg-white dark:bg-zinc-900 rounded-2xl p-5 border border-zinc-200 dark:border-zinc-800 shadow-xs space-y-4">
                <span class="text-xs font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider block">Qualification Timeline</span>
                <div class="space-y-4 pl-2 text-xs relative before:absolute before:left-3.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-zinc-200 dark:before:bg-zinc-800">
                    <div class="flex items-start gap-3 relative">
                        <div class="w-4 h-4 rounded-full bg-emerald-500 ring-4 ring-white dark:ring-zinc-900 shrink-0 mt-0.5"></div>
                        <div>
                            <div class="font-bold text-zinc-900 dark:text-zinc-100">Lead Captured &amp; Propensity Scored</div>
                            <div class="text-[11px] text-zinc-500">Via {{ $lead['source'] ?? 'Inbound Website' }} • Score: {{ $lead['score'] ?? 50 }}</div>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 relative">
                        <div class="w-4 h-4 rounded-full bg-indigo-500 ring-4 ring-white dark:ring-zinc-900 shrink-0 mt-0.5"></div>
                        <div>
                            <div class="font-bold text-zinc-900 dark:text-zinc-100">Assigned to {{ $lead['assigned_to_name'] ?? 'Sales Rep' }}</div>
                            <div class="text-[11px] text-zinc-500">Ownership allocated for qualification</div>
                        </div>
                    </div>

                    @if(!empty($lead['is_converted']))
                        <div class="flex items-start gap-3 relative">
                            <div class="w-4 h-4 rounded-full bg-emerald-600 ring-4 ring-white dark:ring-zinc-900 shrink-0 mt-0.5"></div>
                            <div>
                                <div class="font-bold text-emerald-600 dark:text-emerald-400">Converted into Customer Account</div>
                                <div class="text-[11px] text-zinc-500">Ready for Opportunity Pipeline</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Modals for Edit, Convert, and Transfer -->
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
    ];
@endphp
<script>
    window.__LEAD_DETAIL_DATA__ = {!! json_encode($leadDetailPayload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};
</script>
<script src="{{ asset('js/lead_detail.js') }}?v=1.0"></script>
@endsection
