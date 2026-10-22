@extends('layouts.app')

@section('title', ($contact['name'] ?? 'Contact') . ' — Contact 360° Profile — DealSync CRM')

@section('content')
<!-- Centered Max-Width Container matching React page-wrapper (max-width: 1240px, padding: 2.25rem 2.5rem) -->
<div id="contact-show-page-root" class="max-w-[1240px] mx-auto w-full px-6 sm:px-10 py-7 space-y-6 animate-fade-in">
    <!-- Breadcrumb & Back Action -->
    <div class="flex items-center justify-between gap-4 flex-wrap">
        <div class="flex items-center gap-2 text-xs">
            <a href="/contacts" class="spa-nav-link inline-flex items-center gap-1.5 font-semibold text-zinc-500 hover:text-indigo-600 dark:text-zinc-400 dark:hover:text-indigo-400 transition-colors">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Contacts Directory</span>
            </a>
            <span class="text-zinc-300 dark:text-zinc-700">/</span>
            <span class="text-zinc-600 dark:text-zinc-400 font-medium">{{ $contact['account_name'] ?? 'Account' }}</span>
            <span class="text-zinc-300 dark:text-zinc-700">/</span>
            <span class="text-zinc-950 dark:text-zinc-100 font-bold">{{ $contact['name'] }}</span>
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
            <!-- Left: Avatar, Name, Role, Badges -->
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white font-extrabold text-xl flex items-center justify-center shrink-0 shadow-md">
                    {{ strtoupper(substr($contact['first_name'] ?? 'C', 0, 1)) }}
                </div>
                <div class="space-y-1.5">
                    <div class="flex items-center gap-3 flex-wrap">
                        <h1 class="text-2xl font-extrabold text-zinc-950 dark:text-white tracking-tight">
                            {{ $contact['name'] }}
                        </h1>
                        <!-- Role Badge -->
                        @php
                            $role = $contact['contact_role'] ?? 'Decision Maker';
                            $roleBadges = [
                                'Decision Maker' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800',
                                'Champion' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800',
                                'Influencer' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800',
                                'Evaluator' => 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/40 dark:text-purple-300 dark:border-purple-800',
                            ];
                            $roleClass = $roleBadges[$role] ?? 'bg-zinc-100 text-zinc-700 border-zinc-200';
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $roleClass }}">
                            {{ $role }}
                        </span>

                        @if(!empty($contact['is_primary']))
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                                <span>⭐ Primary Contact</span>
                            </span>
                        @endif

                        @if(!empty($contact['title']))
                            <span class="text-xs text-zinc-500 font-medium">
                                {{ $contact['title'] }}
                            </span>
                        @endif
                    </div>

                    <div class="text-xs text-zinc-600 dark:text-zinc-400 flex items-center gap-3 flex-wrap">
                        @if(!empty($contact['account_name']) && $contact['account_name'] !== 'Unlinked')
                            <a href="/accounts/{{ $contact['account_id'] ?? '' }}" class="font-semibold text-zinc-900 dark:text-zinc-200 hover:text-indigo-600 flex items-center gap-1">
                                <i data-lucide="building-2" class="w-3.5 h-3.5 text-zinc-400"></i>
                                <span>{{ $contact['account_name'] }}</span>
                            </a>
                            <span>•</span>
                        @endif

                        @if(!empty($contact['email']))
                            <a href="mailto:{{ $contact['email'] }}" class="text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1">
                                <i data-lucide="mail" class="w-3.5 h-3.5"></i>
                                <span>{{ $contact['email'] }}</span>
                            </a>
                            <span>•</span>
                        @endif

                        @if(!empty($contact['phone']))
                            <a href="tel:{{ $contact['phone'] }}" class="hover:text-indigo-600 flex items-center gap-1">
                                <i data-lucide="phone" class="w-3.5 h-3.5"></i>
                                <span>{{ $contact['phone'] }}</span>
                            </a>
                            <span>•</span>
                        @endif

                        <div class="flex items-center gap-1.5 text-zinc-700 dark:text-zinc-300">
                            <span class="text-zinc-400 font-medium">Owner:</span>
                            <span class="w-4 h-4 rounded-full bg-blue-100 text-blue-700 font-bold text-[9px] inline-flex items-center justify-center">
                                {{ strtoupper(substr($contact['owner_name'] ?? 'U', 0, 1)) }}
                            </span>
                            <span class="font-semibold">{{ $contact['owner_name'] ?? 'Unassigned' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Action Buttons -->
            <div class="flex items-center gap-2 flex-wrap shrink-0">
                <button
                    type="button"
                    onclick="window.openContactDetailEditModal()"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs cursor-pointer"
                >
                    <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                    <span>Edit Contact</span>
                </button>

                <button
                    type="button"
                    onclick="window.openContactDetailTransferModal()"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-2xs cursor-pointer"
                >
                    <i data-lucide="user-check" class="w-3.5 h-3.5"></i>
                    <span>Transfer Owner</span>
                </button>

                <button
                    type="button"
                    onclick="window.deleteCurrentContact()"
                    class="p-2 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-zinc-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/40 transition-colors shadow-2xs cursor-pointer"
                    title="Delete Contact"
                >
                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Segmented Tabs Bar -->
    <div class="flex items-center gap-1.5 p-1 rounded-xl bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 overflow-x-auto">
        <button
            type="button"
            onclick="window.switchContactTab('overview')"
            id="contact-tab-btn-overview"
            class="px-4 py-1.5 text-xs font-semibold rounded-lg bg-white dark:bg-zinc-700 text-zinc-950 dark:text-zinc-100 shadow-xs cursor-pointer whitespace-nowrap"
        >
            Stakeholder Overview
        </button>
        <button
            type="button"
            onclick="window.switchContactTab('sales')"
            id="contact-tab-btn-sales"
            class="px-4 py-1.5 text-xs font-medium rounded-lg text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-200 cursor-pointer whitespace-nowrap"
        >
            Sales Deals ({{ count($opportunities ?? []) }})
        </button>
        <button
            type="button"
            onclick="window.switchContactTab('purchases')"
            id="contact-tab-btn-purchases"
            class="px-4 py-1.5 text-xs font-medium rounded-lg text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-200 cursor-pointer whitespace-nowrap"
        >
            PO Deals ({{ count($purchaseDeals ?? []) }})
        </button>
    </div>

    <!-- TAB 1: OVERVIEW -->
    <div id="contact-tab-content-overview" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Organization & Direct Reachability -->
            <div class="bg-white dark:bg-zinc-900 rounded-xl p-5 border border-zinc-200 dark:border-zinc-800 shadow-xs space-y-4">
                <h3 class="text-xs font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider pb-2 border-b border-zinc-100 dark:border-zinc-800 flex items-center gap-2">
                    <i data-lucide="building-2" class="w-4 h-4 text-indigo-600"></i> Organization Reachability
                </h3>
                <div class="space-y-3 text-xs">
                    <div>
                        <div class="text-[11px] font-semibold text-zinc-400">Account Organization</div>
                        <div class="text-zinc-800 dark:text-zinc-200 font-bold mt-0.5">
                            {{ $contact['account_name'] ?? 'Unlinked Account' }}
                        </div>
                    </div>
                    <div>
                        <div class="text-[11px] font-semibold text-zinc-400">Department &amp; Functional Area</div>
                        <div class="text-zinc-800 dark:text-zinc-200 font-medium mt-0.5">
                            {{ $contact['department'] ?? 'General Corporate' }}
                        </div>
                    </div>
                    <div>
                        <div class="text-[11px] font-semibold text-zinc-400">Buying Influence Authority</div>
                        <div class="text-zinc-800 dark:text-zinc-200 font-medium mt-0.5">
                            {{ $contact['contact_role'] ?? 'Decision Maker' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dynamic Custom Fields Section with Field-Level Security -->
            <div class="bg-white dark:bg-zinc-900 rounded-xl p-5 border border-zinc-200 dark:border-zinc-800 shadow-xs space-y-4">
                <div class="flex items-center justify-between pb-2 border-b border-zinc-100 dark:border-zinc-800">
                    <h3 class="text-xs font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider flex items-center gap-2">
                        <i data-lucide="sliders" class="w-4 h-4 text-indigo-600"></i> Dynamic Custom Fields
                    </h3>
                    <span class="text-[11px] text-zinc-400">Field-level permissions active</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    @forelse($customFields ?? [] as $cf)
                        @php
                            $fName = $cf->field_name;
                            $perm = $fieldPermissions[$fName] ?? 'Read/Write';
                            if ($perm === 'Hidden') continue;
                            $val = $contact[$fName] ?? ($contact['custom_data'][$fName] ?? null);
                        @endphp
                        <div class="p-3 rounded-lg bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200 dark:border-zinc-700/60 flex flex-col justify-between">
                            <div class="flex items-center justify-between text-[11px] text-zinc-400">
                                <span>{{ $cf->field_label }}</span>
                                @if($perm === 'Read-Only')
                                    <span class="text-[9px] text-amber-600 font-bold">🔒 Read-Only</span>
                                @endif
                            </div>
                            <div class="font-bold text-xs text-zinc-900 dark:text-zinc-100 mt-1">
                                {{ $val ?: '—' }}
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full text-zinc-400 text-xs italic">
                            No dynamic custom fields configured for contacts module.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        @if(!empty($contact['notes']))
            <!-- Notes -->
            <div class="bg-white dark:bg-zinc-900 rounded-xl p-5 border border-zinc-200 dark:border-zinc-800 shadow-xs space-y-2">
                <h3 class="text-xs font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider flex items-center gap-2">
                    <i data-lucide="align-left" class="w-4 h-4 text-indigo-600"></i> Stakeholder Notes &amp; History
                </h3>
                <p class="text-xs text-zinc-700 dark:text-zinc-300 whitespace-pre-wrap">
                    {{ $contact['notes'] }}
                </p>
            </div>
        @endif
    </div>

    <!-- TAB 2: SALES DEALS -->
    <div id="contact-tab-content-sales" class="hidden space-y-3">
        @forelse($opportunities ?? [] as $o)
            <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 border border-zinc-200 dark:border-zinc-800 shadow-xs flex items-center justify-between gap-4 text-xs">
                <div>
                    <div class="font-bold text-zinc-900 dark:text-zinc-100">{{ $o->name }}</div>
                    <div class="text-zinc-500 text-[11px] mt-0.5">
                        Target Close: {{ $o->expected_close_date ?? '—' }} • Probability: {{ $o->probability ?? 0 }}%
                    </div>
                </div>
                <div class="text-right">
                    <div class="font-bold text-zinc-900 dark:text-zinc-100 text-sm">${{ number_format($o->amount ?? 0, 0) }}</div>
                    <span class="inline-block mt-0.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                        {{ $o->stage ?? 'Open Deal' }}
                    </span>
                </div>
            </div>
        @empty
            <div class="bg-white dark:bg-zinc-900 rounded-xl p-12 text-center border border-zinc-200 dark:border-zinc-800 text-zinc-400 text-xs">
                No sales opportunities currently linked to this stakeholder.
            </div>
        @endforelse
    </div>

    <!-- TAB 3: PO DEALS -->
    <div id="contact-tab-content-purchases" class="hidden space-y-3">
        @forelse($purchaseDeals ?? [] as $p)
            <div class="bg-white dark:bg-zinc-900 rounded-xl p-4 border border-zinc-200 dark:border-zinc-800 shadow-xs flex items-center justify-between gap-4 text-xs">
                <div>
                    <div class="font-bold text-zinc-900 dark:text-zinc-100">{{ $p->po_number ?? 'PO' }}: {{ $p->title }}</div>
                    <div class="text-zinc-500 text-[11px] mt-0.5">
                        Target Delivery: {{ $p->expected_delivery_date ?? '—' }}
                    </div>
                </div>
                <div class="text-right">
                    <div class="font-bold text-zinc-900 dark:text-zinc-100 text-sm">${{ number_format($p->amount ?? 0, 0) }}</div>
                    <span class="inline-block mt-0.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                        {{ $p->stage ?? 'Active PO' }}
                    </span>
                </div>
            </div>
        @empty
            <div class="bg-white dark:bg-zinc-900 rounded-xl p-12 text-center border border-zinc-200 dark:border-zinc-800 text-zinc-400 text-xs">
                No purchase orders linked to this contact.
            </div>
        @endforelse
    </div>

    <!-- ========================================== -->
    <!-- EDIT MODAL FOR CONTACT SHOW PAGE            -->
    <!-- ========================================== -->
    <div id="contact-detail-edit-modal" class="fixed inset-0 z-50 hidden bg-zinc-950/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
        <div class="bg-white dark:bg-zinc-900 rounded-2xl max-w-2xl w-full border border-zinc-200 dark:border-zinc-800 shadow-2xl overflow-hidden animate-scale-up my-8">
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between">
                <h3 class="text-sm font-bold text-zinc-950 dark:text-zinc-100">Edit Contact: {{ $contact['name'] }}</h3>
                <button type="button" onclick="window.closeContactDetailEditModal()" class="text-zinc-400 hover:text-zinc-600 cursor-pointer">✕</button>
            </div>
            <form onsubmit="window.submitContactDetailEdit(event)" class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
                <input type="hidden" id="edit-contact-id" value="{{ $contact['id'] }}">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div class="space-y-1">
                        <label class="font-semibold text-zinc-700 dark:text-zinc-300">First Name *</label>
                        <input type="text" id="detail-field-first_name" value="{{ $contact['first_name'] }}" required class="w-full px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                    </div>
                    <div class="space-y-1">
                        <label class="font-semibold text-zinc-700 dark:text-zinc-300">Last Name *</label>
                        <input type="text" id="detail-field-last_name" value="{{ $contact['last_name'] }}" required class="w-full px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                    </div>
                    <div class="space-y-1">
                        <label class="font-semibold text-zinc-700 dark:text-zinc-300">Email Address *</label>
                        <input type="email" id="detail-field-email" value="{{ $contact['email'] }}" required class="w-full px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                    </div>
                    <div class="space-y-1">
                        <label class="font-semibold text-zinc-700 dark:text-zinc-300">Phone Number</label>
                        <input type="text" id="detail-field-phone" value="{{ $contact['phone'] ?? '' }}" class="w-full px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                    </div>
                    <div class="space-y-1">
                        <label class="font-semibold text-zinc-700 dark:text-zinc-300">Job Title</label>
                        <input type="text" id="detail-field-title" value="{{ $contact['title'] ?? '' }}" class="w-full px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                    </div>
                    <div class="space-y-1">
                        <label class="font-semibold text-zinc-700 dark:text-zinc-300">Department</label>
                        <input type="text" id="detail-field-department" value="{{ $contact['department'] ?? '' }}" class="w-full px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                    </div>
                    <div class="space-y-1">
                        <label class="font-semibold text-zinc-700 dark:text-zinc-300">Account Organization</label>
                        <select id="detail-field-account_id" class="w-full px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                            <option value="">Select Account...</option>
                            @foreach($accounts ?? [] as $acc)
                                <option value="{{ $acc->id }}" {{ ($contact['account_id'] ?? '') == $acc->id ? 'selected' : '' }}>{{ $acc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="font-semibold text-zinc-700 dark:text-zinc-300">Buying Role *</label>
                        <select id="detail-field-role" required class="w-full px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                            @foreach($roles ?? ['Decision Maker', 'Champion', 'Influencer', 'Evaluator'] as $r)
                                <option value="{{ $r }}" {{ ($contact['contact_role'] ?? '') === $r ? 'selected' : '' }}>{{ $r }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-2 space-y-1">
                        <label class="font-semibold text-zinc-700 dark:text-zinc-300">Notes &amp; Context</label>
                        <textarea id="detail-field-notes" rows="2" class="w-full px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">{{ $contact['notes'] ?? '' }}</textarea>
                    </div>
                </div>

                <!-- Dynamic Custom Fields in Edit Modal -->
                @if(!empty($customFields))
                    <div class="pt-3 border-t border-zinc-200 dark:border-zinc-800">
                        <div class="font-bold text-xs text-zinc-900 dark:text-zinc-100 mb-2">Dynamic Custom Fields</div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                            @foreach($customFields as $cf)
                                @php
                                    $fName = $cf->field_name;
                                    $perm = $fieldPermissions[$fName] ?? 'Read/Write';
                                    if ($perm === 'Hidden') continue;
                                    $val = $contact[$fName] ?? ($contact['custom_data'][$fName] ?? '');
                                    $isRo = ($perm === 'Read-Only');
                                @endphp
                                <div class="space-y-1">
                                    <label class="font-semibold text-zinc-700 dark:text-zinc-300 flex items-center justify-between">
                                        <span>{{ $cf->field_label }}</span>
                                        @if($isRo)<span class="text-[9px] text-amber-600 font-bold">🔒 Read-Only</span>@endif
                                    </label>
                                    <input
                                        type="text"
                                        data-cf-name="{{ $fName }}"
                                        value="{{ $val }}"
                                        {{ $isRo ? 'disabled' : '' }}
                                        class="contact-detail-custom-field-input w-full px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 {{ $isRo ? 'opacity-60 bg-zinc-100 cursor-not-allowed' : '' }}"
                                    >
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-zinc-200 dark:border-zinc-800">
                    <button type="button" onclick="window.closeContactDetailEditModal()" class="px-3.5 py-1.5 text-xs font-semibold text-zinc-600 hover:bg-zinc-100 rounded-lg cursor-pointer">Cancel</button>
                    <button type="submit" class="px-4 py-1.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm cursor-pointer">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- TRANSFER MODAL FOR CONTACT SHOW PAGE        -->
    <!-- ========================================== -->
    <div id="contact-detail-transfer-modal" class="fixed inset-0 z-50 hidden bg-zinc-950/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white dark:bg-zinc-900 rounded-2xl max-w-md w-full border border-zinc-200 dark:border-zinc-800 shadow-2xl overflow-hidden animate-scale-up">
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between">
                <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Transfer Contact Owner</h3>
                <button type="button" onclick="window.closeContactDetailTransferModal()" class="text-zinc-400 hover:text-zinc-600 cursor-pointer">✕</button>
            </div>
            <form onsubmit="window.submitContactDetailTransfer(event)" class="p-6 space-y-4">
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Select New Representative</label>
                    <select id="detail-transfer-user-select" required class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                        <option value="">Choose team member...</option>
                        @foreach($users ?? [] as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center justify-end gap-3 pt-3 border-t border-zinc-200 dark:border-zinc-800">
                    <button type="button" onclick="window.closeContactDetailTransferModal()" class="px-3.5 py-1.5 text-xs font-semibold text-zinc-600 hover:bg-zinc-100 rounded-lg cursor-pointer">Cancel</button>
                    <button type="submit" class="px-4 py-1.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm cursor-pointer">Transfer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Safe JSON Hydration -->
@php
    $contactDetailData = [
        'contact' => $contact ?? [],
        'customFields' => $customFields ?? [],
        'fieldPermissions' => !empty($fieldPermissions) ? $fieldPermissions : (object)[],
        'accounts' => $accounts ?? [],
        'users' => $users ?? [],
    ];
@endphp
<script>
    window.__CONTACT_DETAIL_DATA__ = {!! json_encode($contactDetailData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};
</script>
<script src="{{ asset('js/contact_detail.js') }}?v=1.0"></script>
@endsection
