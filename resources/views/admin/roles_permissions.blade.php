@extends('layouts.app')

@section('title', 'Roles & Profiles — DealSync Admin')

@section('content')
<!-- Centered Max-Width Container matching Modern DealSync & React Layout -->
<div class="max-w-[1380px] mx-auto w-full px-4 sm:px-6 lg:px-8 py-5 pb-16 space-y-6 animate-fade-in">

    <!-- 1. TOP HEADER BAR & BREADCRUMBS -->
    <div class="flex items-center justify-between flex-wrap gap-4 pb-2 border-b border-zinc-200/80 dark:border-zinc-800">
        <div>
            <div class="flex items-center gap-2 text-xs text-zinc-500 dark:text-zinc-400 font-medium mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">DealSync</a>
                <span>/</span>
                <span class="text-zinc-700 dark:text-zinc-300">Security &amp; Governance</span>
                <span>/</span>
                <span class="text-indigo-600 dark:text-indigo-400 font-semibold">Roles &amp; Profiles</span>
            </div>
            <div class="flex items-center gap-3">
                <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-50 m-0">
                    Roles Hierarchy &amp; Security Profiles
                </h1>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200 dark:bg-indigo-950/40 dark:text-indigo-300 dark:border-indigo-800">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-600 animate-pulse"></span>
                    Governance Engine
                </span>
            </div>
            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 max-w-2xl m-0">
                Configure vertical management reporting chains, record sharing visibility, and granular profile access policies across your enterprise workspace.
            </p>
        </div>

        <!-- Quick Governance Navigation Links -->
        <div class="flex items-center gap-2 flex-wrap">
            <a
                href="{{ route('admin.record_rights') }}"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg bg-white dark:bg-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-200 border border-zinc-200 dark:border-zinc-700 transition-all cursor-pointer shadow-2xs"
            >
                <i data-lucide="shield" class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400"></i>
                <span>Record Rights (CRUD)</span>
            </a>
            <a
                href="{{ route('admin.field_rights') }}"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg bg-white dark:bg-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-200 border border-zinc-200 dark:border-zinc-700 transition-all cursor-pointer shadow-2xs"
            >
                <i data-lucide="lock" class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400"></i>
                <span>Field-Level Rights</span>
            </a>
            <a
                href="{{ route('admin.users_directory') }}"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg bg-white dark:bg-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-200 border border-zinc-200 dark:border-zinc-700 transition-all cursor-pointer shadow-2xs"
            >
                <i data-lucide="users" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400"></i>
                <span>Users Directory</span>
            </a>
        </div>
    </div>

    <!-- 2. MAIN 2-COLUMN PROPORTIONAL GRID (1-TO-1 REACT SETTINGSVIEW.TSX) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">

        <!-- ================================================================= -->
        <!-- COLUMN 1: ROLES HIERARCHY -->
        <!-- ================================================================= -->
        <div class="space-y-4">
            <!-- Column Header Card -->
            <div class="flex items-center justify-between p-4 bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-xs">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400 flex items-center justify-center shrink-0 shadow-2xs">
                        <i data-lucide="layers" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 m-0 tracking-tight">
                                Roles Hierarchy
                            </h2>
                            <span id="rolesCountBadge" class="inline-flex items-center px-2 py-0.2 rounded-full text-[11px] font-bold bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700">
                                {{ count($roles ?? []) }}
                            </span>
                        </div>
                        <div class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5">
                            Reporting chain &amp; record sharing scope
                        </div>
                    </div>
                </div>

                <button
                    type="button"
                    onclick="handleOpenCreateRole()"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white shadow-xs transition-all cursor-pointer"
                >
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>Create Role</span>
                </button>
            </div>

            <!-- Dynamic Roles Cards List Container -->
            <div id="rolesListContainer" class="space-y-3">
                <!-- Populated dynamically via renderRolesList() -->
            </div>
        </div>

        <!-- ================================================================= -->
        <!-- COLUMN 2: SECURITY PROFILES -->
        <!-- ================================================================= -->
        <div class="space-y-4">
            <!-- Column Header Card -->
            <div class="flex items-center justify-between p-4 bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-xs">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 dark:bg-purple-950/40 dark:text-purple-400 flex items-center justify-center shrink-0 shadow-2xs">
                        <i data-lucide="shield" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 m-0 tracking-tight">
                                Security Profiles
                            </h2>
                            <span id="profilesCountBadge" class="inline-flex items-center px-2 py-0.2 rounded-full text-[11px] font-bold bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700">
                                {{ count($profiles ?? []) }}
                            </span>
                        </div>
                        <div class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5">
                            CRUD access, login restrictions &amp; calendar sources
                        </div>
                    </div>
                </div>

                <button
                    type="button"
                    onclick="handleOpenCreateProfile()"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white shadow-xs transition-all cursor-pointer"
                >
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>Create Profile</span>
                </button>
            </div>

            <!-- Dynamic Profiles Cards List Container -->
            <div id="profilesListContainer" class="space-y-3">
                <!-- Populated dynamically via renderProfilesList() -->
            </div>
        </div>

    </div>
</div>

<!-- ===================================================================== -->
<!-- MODAL 1: CREATE / EDIT ROLE MODAL                                      -->
<!-- ===================================================================== -->
<div id="roleModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black/60 backdrop-blur-xs transition-opacity">
    <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl border border-zinc-200 dark:border-zinc-800 max-w-md w-full overflow-hidden animate-fade-in">
        <div class="flex items-center justify-between px-5 py-4 border-b border-zinc-100 dark:border-zinc-800">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400 flex items-center justify-center">
                    <i data-lucide="layers" class="w-4 h-4"></i>
                </div>
                <h3 id="roleModalTitle" class="text-sm font-bold text-zinc-900 dark:text-zinc-100 m-0">
                    Create New Role
                </h3>
            </div>
            <button
                type="button"
                onclick="closeRoleModal()"
                class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 text-lg leading-none cursor-pointer p-1"
            >
                ✕
            </button>
        </div>

        <form id="roleForm" onsubmit="handleSaveRole(event)">
            <div class="p-5 space-y-4 text-xs">
                <div>
                    <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Role Title *</label>
                    <input
                        type="text"
                        id="roleNameInput"
                        required
                        placeholder="e.g. Regional Sales Director"
                        class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/80 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all"
                    >
                </div>

                <div>
                    <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Reports To Role</label>
                    <select
                        id="roleReportsToSelect"
                        class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/80 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all cursor-pointer"
                    >
                        <option value="">-- Top Level (No Parent Role) --</option>
                    </select>
                    <p class="text-[11px] text-zinc-400 dark:text-zinc-500 mt-1 mb-0">
                        Defines data visibility scope. Managers inherit visibility of subordinates' records.
                    </p>
                </div>

                <div>
                    <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Role Description</label>
                    <input
                        type="text"
                        id="roleDescInput"
                        placeholder="Organizational authority and scope description"
                        class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/80 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all"
                    >
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 px-5 py-3.5 bg-zinc-50 dark:bg-zinc-800/60 border-t border-zinc-100 dark:border-zinc-800">
                <button
                    type="button"
                    onclick="closeRoleModal()"
                    class="px-3.5 py-1.5 text-xs font-semibold rounded-lg text-zinc-700 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-all cursor-pointer"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    id="roleSubmitBtn"
                    class="px-4 py-1.5 text-xs font-semibold rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white shadow-xs transition-all cursor-pointer"
                >
                    Create Role
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ===================================================================== -->
<!-- MODAL 2: CREATE / EDIT SECURITY PROFILE MODAL                          -->
<!-- ===================================================================== -->
<div id="profileModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black/60 backdrop-blur-xs transition-opacity">
    <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl border border-zinc-200 dark:border-zinc-800 max-w-lg w-full overflow-hidden animate-fade-in">
        <div class="flex items-center justify-between px-5 py-4 border-b border-zinc-100 dark:border-zinc-800">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 dark:bg-purple-950/40 dark:text-purple-400 flex items-center justify-center">
                    <i data-lucide="shield" class="w-4 h-4"></i>
                </div>
                <h3 id="profileModalTitle" class="text-sm font-bold text-zinc-900 dark:text-zinc-100 m-0">
                    Create Security Profile
                </h3>
            </div>
            <button
                type="button"
                onclick="closeProfileModal()"
                class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 text-lg leading-none cursor-pointer p-1"
            >
                ✕
            </button>
        </div>

        <form id="profileForm" onsubmit="handleSaveProfile(event)">
            <div class="p-5 space-y-4 text-xs max-h-[75vh] overflow-y-auto">
                <div>
                    <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Profile Name *</label>
                    <input
                        type="text"
                        id="profileNameInput"
                        required
                        placeholder="e.g. Operations Specialist"
                        class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/80 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all"
                    >
                </div>

                <div>
                    <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Description</label>
                    <input
                        type="text"
                        id="profileDescInput"
                        placeholder="Role and access scope description"
                        class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/80 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all"
                    >
                </div>

                <!-- IP Whitelist Card -->
                <div class="p-3.5 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50/70 dark:bg-zinc-800/40 space-y-1.5">
                    <label class="block font-semibold text-zinc-900 dark:text-zinc-100">
                        Allowed IP Range / Whitelist (Login Security)
                    </label>
                    <input
                        type="text"
                        id="profileIpWhitelistInput"
                        placeholder="e.g. 127.0.0.1, 192.168.1.1-192.168.1.254, 10.0.0.0/24"
                        class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 font-mono text-[11px] focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all"
                    >
                    <div class="text-[11px] text-zinc-400 dark:text-zinc-500 leading-normal">
                        Supports single IPs, hyphen ranges, and CIDR subnets. <em>Leave blank to allow login from any IP address.</em>
                    </div>
                </div>

                <!-- Login Time Window Card -->
                <div class="p-3.5 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50/70 dark:bg-zinc-800/40 space-y-2">
                    <label class="block font-semibold text-zinc-900 dark:text-zinc-100">
                        Allowed Login Hours Window
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] text-zinc-500 dark:text-zinc-400 mb-1">Start Time</label>
                            <input
                                type="time"
                                id="profileLoginTimeStartInput"
                                class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:border-indigo-500 transition-all"
                            >
                        </div>
                        <div>
                            <label class="block text-[11px] text-zinc-500 dark:text-zinc-400 mb-1">End Time</label>
                            <input
                                type="time"
                                id="profileLoginTimeEndInput"
                                class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:border-indigo-500 transition-all"
                            >
                        </div>
                    </div>
                    <div class="text-[11px] text-zinc-400 dark:text-zinc-500 leading-normal">
                        Users on this profile can only sign in during these hours. <em>Leave blank to allow login 24/7.</em>
                    </div>
                </div>

                <!-- Ambient Themes Toggle -->
                <div class="p-3.5 rounded-xl border border-purple-200 dark:border-purple-900/60 bg-purple-50/50 dark:bg-purple-950/20">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input
                            type="checkbox"
                            id="profileSurpriseMeCheckbox"
                            checked
                            class="mt-0.5 rounded text-purple-600 focus:ring-purple-500 w-4 h-4 cursor-pointer"
                        >
                        <div>
                            <div class="font-bold text-xs text-purple-900 dark:text-purple-300 flex items-center gap-1.5">
                                <i data-lucide="sparkles" class="w-3.5 h-3.5 text-purple-600 dark:text-purple-400"></i>
                                <span>Enable Ambient Themes &amp; Glow Customization Studio</span>
                            </div>
                            <div class="text-[11px] text-purple-700/80 dark:text-purple-300/70 mt-1 leading-normal">
                                Allows users assigned to this profile to activate atmospheric ambient themes (Warm Honey-Gold Glow, Ocean Teal Caustics, Cyberpunk Neon) and particle effects.
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 px-5 py-3.5 bg-zinc-50 dark:bg-zinc-800/60 border-t border-zinc-100 dark:border-zinc-800">
                <button
                    type="button"
                    onclick="closeProfileModal()"
                    class="px-3.5 py-1.5 text-xs font-semibold rounded-lg text-zinc-700 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-all cursor-pointer"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    id="profileSubmitBtn"
                    class="px-4 py-1.5 text-xs font-semibold rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white shadow-xs transition-all cursor-pointer"
                >
                    Save Profile Changes
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ===================================================================== -->
<!-- MODAL 3: CALENDAR SOURCES STUDIO MODAL                                -->
<!-- ===================================================================== -->
<div id="calendarSourcesModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black/60 backdrop-blur-xs transition-opacity">
    <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl border border-zinc-200 dark:border-zinc-800 max-w-lg w-full overflow-hidden animate-fade-in">
        <div class="flex items-center justify-between px-5 py-4 border-b border-zinc-100 dark:border-zinc-800">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400 flex items-center justify-center">
                    <i data-lucide="calendar" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 m-0">
                        Profile Calendar Sources
                    </h3>
                    <div class="text-[11px] text-zinc-500 dark:text-zinc-400">
                        Configuring for: <strong id="calSourcesProfileName" class="text-zinc-900 dark:text-zinc-100">Standard User</strong>
                    </div>
                </div>
            </div>
            <button
                type="button"
                onclick="closeCalendarSourcesModal()"
                class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 text-lg leading-none cursor-pointer p-1"
            >
                ✕
            </button>
        </div>

        <div class="p-5 space-y-3 max-h-[70vh] overflow-y-auto">
            <p class="text-xs text-zinc-500 dark:text-zinc-400 m-0 leading-relaxed">
                Choose which CRM record types appear as synchronized schedule events on this profile's calendar workspace.
            </p>

            <div id="calSourcesList" class="space-y-2.5 pt-1">
                <!-- Rendered dynamically -->
            </div>
        </div>

        <div class="flex items-center justify-end gap-2 px-5 py-3.5 bg-zinc-50 dark:bg-zinc-800/60 border-t border-zinc-100 dark:border-zinc-800">
            <button
                type="button"
                onclick="closeCalendarSourcesModal()"
                class="px-3.5 py-1.5 text-xs font-semibold rounded-lg text-zinc-700 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-all cursor-pointer"
            >
                Cancel
            </button>
            <button
                type="button"
                onclick="handleSaveCalendarSources()"
                class="px-4 py-1.5 text-xs font-semibold rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white shadow-xs transition-all cursor-pointer"
            >
                Save Sources
            </button>
        </div>
    </div>
</div>

<!-- ===================================================================== -->
<!-- FEEDBACK TOAST BANNER                                                 -->
<!-- ===================================================================== -->
<div id="feedbackBanner" class="hidden fixed bottom-5 right-5 z-50 items-center gap-2.5 px-4 py-3 rounded-xl shadow-lg text-xs font-semibold animate-fade-in">
    <div id="feedbackIconContainer"></div>
    <span id="feedbackText">Notification</span>
</div>

<!-- Global Hydration Payload & Script Load -->
<script>
    window.INITIAL_ROLES = @json($roles ?? []);
    window.INITIAL_PROFILES = @json($profiles ?? []);
    window.CSRF_TOKEN = '{{ csrf_token() }}';
</script>
<script src="{{ asset('js/roles-profiles.js') }}?v=1.0"></script>
@endsection
