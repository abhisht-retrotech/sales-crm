@extends('layouts.app')

@section('title', 'Users Directory — DealSync Admin')

@section('content')
<!-- Centered Max-Width Container matching Modern DealSync & React Layout -->
<div class="max-w-[1240px] mx-auto w-full px-4 sm:px-6 lg:px-8 py-5 pb-16 space-y-6 animate-fade-in">

    <!-- USER DETAIL VIEW (Rendered when selectedUserForDetail is set, matching React UserDetailPage.tsx) -->
    <div id="userDetailContainer" class="space-y-6" style="display: none;">
        <!-- 1. TOP NAV & BREADCRUMB -->
        <div class="flex items-center justify-between flex-wrap gap-3 pb-2">
            <button
                type="button"
                onclick="closeUserDetailView()"
                class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-semibold rounded-lg bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-200 border border-zinc-200 dark:border-zinc-700 transition-all cursor-pointer shadow-2xs"
            >
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                <span>Back to User Directory</span>
            </button>

            <div class="flex items-center gap-2 flex-wrap">
                <button
                    type="button"
                    id="detailEditBtn"
                    onclick="detailTriggerEdit()"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-white dark:bg-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-200 border border-zinc-200 dark:border-zinc-700 transition-all cursor-pointer shadow-2xs"
                >
                    <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                    <span>Edit Profile</span>
                </button>

                <button
                    type="button"
                    id="detailLoginBtn"
                    onclick="detailTriggerLogin()"
                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-bold rounded-lg bg-sky-50 text-sky-700 hover:bg-sky-100 border border-sky-200 dark:bg-sky-950/40 dark:text-sky-300 dark:border-sky-800 transition-all cursor-pointer shadow-2xs"
                >
                    <i data-lucide="log-in" class="w-3.5 h-3.5"></i>
                    <span id="detailLoginBtnText">Login as User</span>
                </button>
            </div>
        </div>

        <!-- 2. EXECUTIVE PROFILE HERO CARD & KPI RIBBON -->
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-6 shadow-sm space-y-6">
            <!-- Top Profile Summary Row -->
            <div class="flex items-center justify-between flex-wrap gap-5">
                <div class="flex items-center gap-4">
                    <!-- Avatar badge -->
                    <div class="relative">
                        <div id="detailAvatarBox" class="w-16 h-16 rounded-full bg-gradient-to-tr from-indigo-600 via-indigo-700 to-purple-800 text-white flex items-center justify-center font-extrabold text-xl shadow-md tracking-wider">
                            AM
                        </div>
                        <div
                            id="detailStatusDot"
                            class="absolute bottom-0.5 right-0.5 w-4 h-4 rounded-full border-2 border-white dark:border-zinc-900 bg-emerald-500 shadow-xs"
                        ></div>
                    </div>

                    <div class="space-y-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h1 id="detailFullName" class="text-xl sm:text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-50 m-0">
                                Alex Morgan
                            </h1>
                            <span id="detailStatusPill" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800">
                                ● Active Employee
                            </span>
                            <span id="detailAdminPill" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-200 dark:bg-purple-950/40 dark:text-purple-300 dark:border-purple-800">
                                <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                                Full Admin Rights
                            </span>
                        </div>

                        <!-- Roles & Profile Badges -->
                        <div class="flex items-center gap-2 pt-0.5 flex-wrap">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-200 dark:bg-purple-950/40 dark:text-purple-300 dark:border-purple-800" id="detailRoleBadge">
                                <i data-lucide="shield" class="w-3.5 h-3.5"></i>
                                Executive / CEO
                            </span>
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800" id="detailProfileBadge">
                                <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                                System Administrator
                            </span>
                            <span id="detailReportsToBadge" class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs text-zinc-600 dark:text-zinc-300 bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700">
                                <i data-lucide="building-2" class="w-3.5 h-3.5 text-zinc-400"></i>
                                <span>Reports to: <strong>Board of Directors</strong></span>
                            </span>
                        </div>

                        <!-- Meta dossier pills -->
                        <div class="flex items-center gap-4 pt-1 text-xs text-zinc-500 dark:text-zinc-400 flex-wrap">
                            <span class="inline-flex items-center gap-1.5">
                                <i data-lucide="mail" class="w-3.5 h-3.5 text-zinc-400"></i>
                                <a id="detailEmailLink" href="mailto:alex.morgan@acmecorp.com" class="text-zinc-700 dark:text-zinc-300 hover:text-indigo-600 dark:hover:text-indigo-400 font-medium">alex.morgan@acmecorp.com</a>
                            </span>
                            <span id="detailPhoneSpan" class="inline-flex items-center gap-1.5">
                                <i data-lucide="phone" class="w-3.5 h-3.5 text-zinc-400"></i>
                                <a id="detailPhoneLink" href="tel:+14158901200" class="text-zinc-700 dark:text-zinc-300 hover:text-indigo-600 dark:hover:text-indigo-400 font-medium">+1 (415) 890-1200</a>
                            </span>
                            <span class="inline-flex items-center gap-1.5">
                                <i data-lucide="calendar" class="w-3.5 h-3.5 text-zinc-400"></i>
                                <span id="detailJoinedDate">Joined Oct 12, 2024</span>
                            </span>
                            <span id="detailUserIdBadge" class="font-mono text-[11px] text-zinc-500 dark:text-zinc-400 bg-zinc-100 dark:bg-zinc-800 px-2 py-0.5 rounded border border-zinc-200 dark:border-zinc-700">
                                ID: usr-101
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Quick Governance Badge Pill -->
                <div class="flex flex-col items-end gap-1 px-4 py-3 rounded-xl bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200 dark:border-zinc-700/80 min-w-[180px]">
                    <div class="text-[10px] font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">
                        Governance Status
                    </div>
                    <div class="text-xs font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-1.5">
                        <i data-lucide="shield-check" class="w-4 h-4 text-emerald-500"></i>
                        <span>Compliant &amp; Active</span>
                    </div>
                    <div class="text-[11px] text-zinc-500 dark:text-zinc-400">
                        2FA: Standard • Auth OK
                    </div>
                </div>
            </div>

            <!-- Business Impact KPI Ribbon -->
            <div class="pt-4 border-t border-zinc-100 dark:border-zinc-800/80 grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="bg-zinc-50 dark:bg-zinc-800/50 rounded-xl p-3.5 border border-zinc-200/80 dark:border-zinc-800 space-y-1">
                    <div class="text-xs text-zinc-500 dark:text-zinc-400 font-medium flex items-center gap-1.5">
                        <i data-lucide="dollar-sign" class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400"></i>
                        <span>Pipeline Owned</span>
                    </div>
                    <div id="detailKpiPipeline" class="text-base sm:text-lg font-bold text-zinc-900 dark:text-zinc-100">
                        $1,450,000
                    </div>
                    <div id="detailKpiDeals" class="text-[11px] text-zinc-400 dark:text-zinc-500">
                        Across 12 active deals
                    </div>
                </div>

                <div class="bg-zinc-50 dark:bg-zinc-800/50 rounded-xl p-3.5 border border-zinc-200/80 dark:border-zinc-800 space-y-1">
                    <div class="text-xs text-zinc-500 dark:text-zinc-400 font-medium flex items-center gap-1.5">
                        <i data-lucide="building" class="w-3.5 h-3.5 text-sky-500"></i>
                        <span>Assigned Accounts</span>
                    </div>
                    <div id="detailKpiAccounts" class="text-base sm:text-lg font-bold text-zinc-900 dark:text-zinc-100">
                        18 Accounts
                    </div>
                    <div class="text-[11px] text-zinc-400 dark:text-zinc-500">
                        Enterprise &amp; SMB Accounts
                    </div>
                </div>

                <div class="bg-zinc-50 dark:bg-zinc-800/50 rounded-xl p-3.5 border border-zinc-200/80 dark:border-zinc-800 space-y-1">
                    <div class="text-xs text-zinc-500 dark:text-zinc-400 font-medium flex items-center gap-1.5">
                        <i data-lucide="target" class="w-3.5 h-3.5 text-indigo-500"></i>
                        <span>Assigned Leads</span>
                    </div>
                    <div id="detailKpiLeads" class="text-base sm:text-lg font-bold text-zinc-900 dark:text-zinc-100">
                        34 Leads
                    </div>
                    <div class="text-[11px] text-zinc-400 dark:text-zinc-500">
                        Inbound &amp; outbound pipeline
                    </div>
                </div>

                <div class="bg-zinc-50 dark:bg-zinc-800/50 rounded-xl p-3.5 border border-zinc-200/80 dark:border-zinc-800 space-y-1">
                    <div class="text-xs text-zinc-500 dark:text-zinc-400 font-medium flex items-center gap-1.5">
                        <i data-lucide="check-square" class="w-3.5 h-3.5 text-amber-500"></i>
                        <span>Open Tasks</span>
                    </div>
                    <div id="detailKpiTasks" class="text-base sm:text-lg font-bold text-zinc-900 dark:text-zinc-100">
                        8 Tasks
                    </div>
                    <div class="text-[11px] text-zinc-400 dark:text-zinc-500">
                        Pending operational items
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. ENTERPRISE TAB NAVIGATION BAR -->
        <div class="flex items-center gap-2 border-b border-zinc-200 dark:border-zinc-800 pb-1 overflow-x-auto">
            <button
                type="button"
                id="detailSubtabBtnOverview"
                onclick="switchDetailSubtab('overview')"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-lg text-indigo-600 dark:text-indigo-400 bg-indigo-50/70 dark:bg-indigo-950/30 border-b-2 border-indigo-600 dark:border-indigo-400 transition-all cursor-pointer whitespace-nowrap"
            >
                <i data-lucide="user" class="w-4 h-4"></i>
                <span>Overview &amp; Details</span>
            </button>

            <button
                type="button"
                id="detailSubtabBtnPermissions"
                onclick="switchDetailSubtab('permissions')"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-medium rounded-lg text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white hover:bg-zinc-100 dark:hover:bg-zinc-800 border-b-2 border-transparent transition-all cursor-pointer whitespace-nowrap"
            >
                <i data-lucide="layers" class="w-4 h-4"></i>
                <span>Permissions Matrix</span>
            </button>

            <button
                type="button"
                id="detailSubtabBtnSecurity"
                onclick="switchDetailSubtab('security')"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-medium rounded-lg text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white hover:bg-zinc-100 dark:hover:bg-zinc-800 border-b-2 border-transparent transition-all cursor-pointer whitespace-nowrap"
            >
                <i data-lucide="shield-check" class="w-4 h-4"></i>
                <span>Security &amp; Sessions</span>
            </button>

            <button
                type="button"
                id="detailSubtabBtnAudit"
                onclick="switchDetailSubtab('audit')"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-medium rounded-lg text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white hover:bg-zinc-100 dark:hover:bg-zinc-800 border-b-2 border-transparent transition-all cursor-pointer whitespace-nowrap"
            >
                <i data-lucide="file-text" class="w-4 h-4"></i>
                <span>Audit &amp; Activity Log</span>
            </button>
        </div>

        <!-- TAB 1: OVERVIEW & DETAILS -->
        <div id="detailTabOverview" class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-6 shadow-sm space-y-5">
            <div class="flex items-center justify-between pb-3 border-b border-zinc-100 dark:border-zinc-800">
                <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 m-0">Employee Dossier Information</h3>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-purple-50 text-purple-700 border border-purple-200 dark:bg-purple-950/40 dark:text-purple-300 dark:border-purple-800">Standard Layout</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 text-xs">
                <div>
                    <div class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wider mb-1">Full Legal Name</div>
                    <div id="ovFullName" class="font-semibold text-zinc-900 dark:text-zinc-100 text-sm">Alex Morgan</div>
                </div>

                <div>
                    <div class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wider mb-1">Corporate Email</div>
                    <div id="ovEmail" class="font-semibold text-indigo-600 dark:text-indigo-400 text-sm">alex.morgan@acmecorp.com</div>
                </div>

                <div>
                    <div class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wider mb-1">Direct Phone</div>
                    <div id="ovPhone" class="text-zinc-700 dark:text-zinc-300 text-sm">+1 (415) 890-1200</div>
                </div>

                <div>
                    <div class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wider mb-1">Role in Organization Hierarchy</div>
                    <div id="ovRole" class="font-semibold text-zinc-900 dark:text-zinc-100 text-sm">Executive / CEO</div>
                </div>

                <div>
                    <div class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wider mb-1">Assigned Security Profile</div>
                    <div id="ovProfile" class="font-semibold text-zinc-900 dark:text-zinc-100 text-sm">System Administrator</div>
                </div>

                <div>
                    <div class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wider mb-1">Direct Reporting Manager</div>
                    <div id="ovManager" class="font-semibold text-zinc-900 dark:text-zinc-100 text-sm">Alex Morgan (CEO)</div>
                </div>

                <div>
                    <div class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wider mb-1">Account Access Status</div>
                    <div><span id="ovStatus" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Active</span></div>
                </div>

                <div>
                    <div class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wider mb-1">Ambient Themes &amp; Glow Access</div>
                    <div id="ovThemes" class="text-zinc-700 dark:text-zinc-300 font-medium">Inherit from Profile (✨ Permitted)</div>
                </div>

                <div>
                    <div class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wider mb-1">Tenant Identifier</div>
                    <div class="font-mono text-zinc-600 dark:text-zinc-400">{{ $tenant->subdomain ?? 'tenant-acme-corp' }}</div>
                </div>
            </div>
        </div>

        <!-- TAB 2: PERMISSIONS MATRIX -->
        <div id="detailTabPermissions" class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-6 shadow-sm space-y-4" style="display: none;">
            <div>
                <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 m-0">Record-Level CRUD Permissions Matrix</h3>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 mb-0">
                    Derived from assigned security profile (<span id="permProfileName" class="font-semibold text-zinc-800 dark:text-zinc-200">System Administrator</span>).
                </p>
            </div>

            <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-800">
                <table class="w-full text-left text-xs divide-y divide-zinc-200 dark:divide-zinc-800">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/50 text-[11px] font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        <tr>
                            <th class="py-3 px-4">Module Name</th>
                            <th class="py-3 px-4 text-center">Read</th>
                            <th class="py-3 px-4 text-center">Create</th>
                            <th class="py-3 px-4 text-center">Edit</th>
                            <th class="py-3 px-4 text-center">Delete</th>
                            <th class="py-3 px-4 text-center">Transfer</th>
                            <th class="py-3 px-4">Scope</th>
                        </tr>
                    </thead>
                    <tbody id="permMatrixTbody" class="divide-y divide-zinc-100 dark:divide-zinc-800 text-zinc-900 dark:text-zinc-100">
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB 3: SECURITY & SESSIONS -->
        <div id="detailTabSecurity" class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-6 shadow-sm space-y-5" style="display: none;">
            <div>
                <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 m-0">Security Policies &amp; Active Sessions</h3>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 mb-0">
                    Monitor active browser sessions, IP addresses, and enforcement compliance.
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="bg-zinc-50 dark:bg-zinc-800/50 rounded-xl p-4 border border-zinc-200 dark:border-zinc-700/80 space-y-2">
                    <div class="flex items-center gap-2 font-semibold text-xs text-zinc-900 dark:text-zinc-100">
                        <i data-lucide="key" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"></i>
                        <span>Password Requirements</span>
                    </div>
                    <div class="text-xs text-zinc-600 dark:text-zinc-300 leading-relaxed space-y-1">
                        <div>• Minimum 8 characters</div>
                        <div>• Must include uppercase, number, &amp; special symbol</div>
                        <div>• Expiration: 90 days rotation</div>
                    </div>
                </div>

                <div class="bg-zinc-50 dark:bg-zinc-800/50 rounded-xl p-4 border border-zinc-200 dark:border-zinc-700/80 space-y-2">
                    <div class="flex items-center gap-2 font-semibold text-xs text-zinc-900 dark:text-zinc-100">
                        <i data-lucide="map-pin" class="w-4 h-4 text-sky-500"></i>
                        <span>IP Range Whitelist</span>
                    </div>
                    <div class="text-xs text-zinc-600 dark:text-zinc-300 leading-relaxed space-y-1">
                        <div>• Global Access Permitted</div>
                        <div>• Strict SSL session token hashing</div>
                        <div>• Failed attempts limit: 5 attempts</div>
                    </div>
                </div>
            </div>

            <h4 class="text-xs font-bold text-zinc-900 dark:text-zinc-100 pt-2 mb-0">Active Login Sessions</h4>
            <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-800">
                <table class="w-full text-left text-xs divide-y divide-zinc-200 dark:divide-zinc-800">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/50 text-[11px] font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        <tr>
                            <th class="py-3 px-4">Device &amp; Browser</th>
                            <th class="py-3 px-4">IP Address</th>
                            <th class="py-3 px-4">Location</th>
                            <th class="py-3 px-4">Started</th>
                            <th class="py-3 px-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800 text-zinc-900 dark:text-zinc-100">
                        <tr>
                            <td class="py-3 px-4 font-semibold flex items-center gap-2">
                                <i data-lucide="laptop" class="w-3.5 h-3.5 text-zinc-400"></i>
                                <span>Chrome 122 (Windows 11)</span>
                            </td>
                            <td class="py-3 px-4 font-mono text-[11px] text-zinc-600 dark:text-zinc-400">127.0.0.1 (Localhost)</td>
                            <td class="py-3 px-4 text-zinc-600 dark:text-zinc-300">San Francisco, USA</td>
                            <td class="py-3 px-4 text-zinc-500 dark:text-zinc-400">Today, 10:14 AM</td>
                            <td class="py-3 px-4"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Active Session</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB 4: AUDIT & ACTIVITY LOG -->
        <div id="detailTabAudit" class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-6 shadow-sm space-y-4" style="display: none;">
            <div>
                <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 m-0">Audit Trail &amp; Activity History</h3>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 mb-0">
                    Tamper-evident system actions executed by or performed on this user.
                </p>
            </div>

            <div class="space-y-3">
                <div class="flex items-start gap-3 p-3.5 rounded-xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200 dark:border-zinc-800">
                    <div class="w-8 h-8 rounded-full bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0 border border-indigo-200 dark:border-indigo-800">
                        <i data-lucide="shield-check" class="w-4 h-4"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-xs font-semibold text-zinc-900 dark:text-zinc-100">User Profile Verified</div>
                        <div class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5">Security policies &amp; roles synchronized with organization tenant.</div>
                    </div>
                    <div class="text-[11px] text-zinc-400 dark:text-zinc-500 whitespace-nowrap">10 mins ago</div>
                </div>

                <div class="flex items-start gap-3 p-3.5 rounded-xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200 dark:border-zinc-800">
                    <div class="w-8 h-8 rounded-full bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 border border-emerald-200 dark:border-emerald-800">
                        <i data-lucide="log-in" class="w-4 h-4"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-xs font-semibold text-zinc-900 dark:text-zinc-100">Authenticated Session Initiated</div>
                        <div class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5">Successful credential verification via browser client.</div>
                    </div>
                    <div class="text-[11px] text-zinc-400 dark:text-zinc-500 whitespace-nowrap">2 hours ago</div>
                </div>
            </div>
        </div>
    </div>

    <!-- MAIN DIRECTORY VIEW (Matching React SettingsView.tsx lines 1978-2480) -->
    <div id="mainDirectoryView" class="space-y-6">

        <!-- Top Header Banner & Quota Metering Card -->
        <div class="bg-gradient-to-br from-white via-white to-slate-50 dark:from-zinc-900 dark:via-zinc-900 dark:to-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-5 sm:p-6 shadow-sm space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2.5">
                        <i data-lucide="users" class="w-5 h-5 text-indigo-600 dark:text-indigo-400 shrink-0"></i>
                        <h2 class="text-lg font-bold tracking-tight text-zinc-900 dark:text-zinc-50 m-0">
                            Seat License &amp; Quota Governance
                        </h2>
                        <span class="uppercase text-[10px] font-bold tracking-wider px-2.5 py-0.5 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200 dark:bg-indigo-950/40 dark:text-indigo-300 dark:border-indigo-800 shadow-2xs">
                            Enterprise Tier
                        </span>
                    </div>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 mb-0">
                        Metered capacity, cryptographic team invitations (7-day tokens), and automated user offboarding.
                    </p>
                </div>

                <!-- Subscription Add Seats CTA -->
                <div class="flex items-center gap-2 shrink-0">
                    <button
                        type="button"
                        onclick="openAddSeatsModal()"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm transition-all cursor-pointer"
                    >
                        <i data-lucide="credit-card" class="w-3.5 h-3.5"></i>
                        <span>+ Add Seats ($39/mo)</span>
                    </button>
                </div>
            </div>

            <!-- Capacity Progress Bar -->
            <div class="space-y-1.5">
                <div class="flex items-center justify-between text-xs font-semibold text-zinc-600 dark:text-zinc-300">
                    <span id="quotaAllocatedText">6 of 15 Seats Allocated (40%)</span>
                    <span id="quotaAvailableText" class="text-emerald-600">
                        9 Seats Available
                    </span>
                </div>
                <div class="h-2 w-full bg-slate-200 dark:bg-zinc-800 rounded-full overflow-hidden">
                    <div
                        id="quotaProgressBar"
                        class="h-full bg-emerald-500 rounded-full transition-all duration-500"
                        style="width: 40%;"
                    ></div>
                </div>
            </div>

            <!-- Metrics Breakdown Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-2">
                <div class="bg-white dark:bg-zinc-900 p-3.5 rounded-xl border border-zinc-200/80 dark:border-zinc-800 shadow-2xs space-y-1">
                    <div class="text-[11px] font-medium text-zinc-500 dark:text-zinc-400">Active Users</div>
                    <div id="metricActiveUsers" class="text-lg sm:text-xl font-bold tracking-tight text-emerald-700 dark:text-emerald-400">
                        {{ count($users ?? []) }}
                    </div>
                </div>

                <div class="bg-white dark:bg-zinc-900 p-3.5 rounded-xl border border-zinc-200/80 dark:border-zinc-800 shadow-2xs space-y-1">
                    <div class="text-[11px] font-medium text-zinc-500 dark:text-zinc-400">Pending Invites</div>
                    <div id="metricPendingInvites" class="text-lg sm:text-xl font-bold tracking-tight text-amber-600 dark:text-amber-400">
                        {{ count($invitations ?? []) }}
                    </div>
                </div>

                <div class="bg-white dark:bg-zinc-900 p-3.5 rounded-xl border border-zinc-200/80 dark:border-zinc-800 shadow-2xs space-y-1">
                    <div class="text-[11px] font-medium text-zinc-500 dark:text-zinc-400">Available Seats</div>
                    <div id="metricAvailableSeats" class="text-lg sm:text-xl font-bold tracking-tight text-indigo-600 dark:text-indigo-400">
                        {{ max(0, ($maxSeats ?? 15) - (count($users ?? []) + count($invitations ?? []))) }}
                    </div>
                </div>

                <div class="bg-white dark:bg-zinc-900 p-3.5 rounded-xl border border-zinc-200/80 dark:border-zinc-800 shadow-2xs space-y-1">
                    <div class="text-[11px] font-medium text-zinc-500 dark:text-zinc-400">Inactive Accounts</div>
                    <div id="metricInactiveAccounts" class="text-lg sm:text-xl font-bold tracking-tight text-zinc-400 dark:text-zinc-500">
                        0
                    </div>
                </div>
            </div>
        </div>

        <!-- Sub-Tabs & Action Buttons -->
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div class="inline-flex p-1 bg-zinc-100 dark:bg-zinc-800/80 rounded-xl border border-zinc-200/60 dark:border-zinc-700/60 gap-1">
                <button
                    type="button"
                    id="subtabBtnUsers"
                    onclick="setUserDirectoryTab('users')"
                    class="px-3.5 py-1.5 text-xs font-semibold rounded-lg bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white shadow-xs transition-all cursor-pointer"
                >
                    Active User Directory (<span id="userCountBadge">{{ count($users ?? []) }}</span>)
                </button>
                <button
                    type="button"
                    id="subtabBtnInvitations"
                    onclick="setUserDirectoryTab('invitations')"
                    class="px-3.5 py-1.5 text-xs font-medium rounded-lg text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-all inline-flex items-center gap-1.5 cursor-pointer"
                >
                    <span>Pending Invitations</span>
                    <span id="pendingCountBadge" class="inline-flex items-center px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-amber-500 text-white shadow-2xs">
                        {{ count($invitations ?? []) }}
                    </span>
                </button>
            </div>

            <div class="flex items-center gap-2">
                <button
                    type="button"
                    onclick="openInviteModal()"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-white dark:bg-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-200 border border-zinc-200 dark:border-zinc-700 transition-all cursor-pointer shadow-2xs"
                >
                    <i data-lucide="send" class="w-3.5 h-3.5"></i>
                    <span>Invite Team Member</span>
                </button>
                <button
                    type="button"
                    onclick="openCreateUserModal()"
                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-semibold rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white shadow-xs transition-all cursor-pointer"
                >
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>Add User</span>
                </button>
            </div>
        </div>

        <!-- DIRECTORY VIEW: ACTIVE USERS TABLE -->
        <div id="usersTableCard" class="card-table-wrapper overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 shadow-xs relative">
            <table id="activeUsersTable" class="data-table w-full text-left text-xs divide-y divide-zinc-200 dark:divide-zinc-800 min-w-[1380px] border-separate border-spacing-0">
                <thead class="bg-zinc-50 dark:bg-zinc-800/80 text-[11px] font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                    <tr>
                        <th data-col="identity" data-default-width="240" data-min-width="150" style="width: 240px; min-width: 150px;" class="py-3 px-4 relative select-none border-r border-zinc-200/70 dark:border-zinc-800/80">
                            <span class="truncate block">User Identity</span>
                            <div class="table-col-resizer" title="Drag to resize column (Double-click to reset)"></div>
                        </th>
                        <th data-col="email" data-default-width="220" data-min-width="140" style="width: 220px; min-width: 140px;" class="py-3 px-4 relative select-none border-r border-zinc-200/70 dark:border-zinc-800/80">
                            <span class="truncate block">Email</span>
                            <div class="table-col-resizer" title="Drag to resize column (Double-click to reset)"></div>
                        </th>
                        <th data-col="role" data-default-width="180" data-min-width="120" style="width: 180px; min-width: 120px;" class="py-3 px-4 relative select-none border-r border-zinc-200/70 dark:border-zinc-800/80">
                            <span class="truncate block">Role</span>
                            <div class="table-col-resizer" title="Drag to resize column (Double-click to reset)"></div>
                        </th>
                        <th data-col="profile" data-default-width="190" data-min-width="130" style="width: 190px; min-width: 130px;" class="py-3 px-4 relative select-none border-r border-zinc-200/70 dark:border-zinc-800/80">
                            <span class="truncate block">Security Profile</span>
                            <div class="table-col-resizer" title="Drag to resize column (Double-click to reset)"></div>
                        </th>
                        <th data-col="themes" data-default-width="180" data-min-width="130" style="width: 180px; min-width: 130px;" class="py-3 px-4 relative select-none border-r border-zinc-200/70 dark:border-zinc-800/80">
                            <span class="truncate block">Ambient Themes</span>
                            <div class="table-col-resizer" title="Drag to resize column (Double-click to reset)"></div>
                        </th>
                        <th data-col="status" data-default-width="130" data-min-width="90" style="width: 130px; min-width: 90px;" class="py-3 px-4 relative select-none border-r border-zinc-200/70 dark:border-zinc-800/80">
                            <span class="truncate block">Status</span>
                            <div class="table-col-resizer" title="Drag to resize column (Double-click to reset)"></div>
                        </th>
                        <th data-col="actions" style="width: 240px; min-width: 240px;" class="py-3 px-4 text-right sticky-actions-col sticky right-0 bg-zinc-50 dark:bg-zinc-800 z-20 border-l border-zinc-200 dark:border-zinc-700 shadow-[-4px_0_8px_-2px_rgba(0,0,0,0.08)]">
                            <span>Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody id="activeUsersTbody" class="divide-y divide-zinc-100 dark:divide-zinc-800">
                </tbody>
            </table>
        </div>

        <!-- PENDING INVITATIONS DIRECTORY TABLE -->
        <div id="invitationsTableCard" class="card-table-wrapper overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 shadow-xs relative" style="display: none;">
            <div id="invitationsEmptyState" class="py-16 px-4 text-center space-y-3" style="display: none;">
                <div class="text-3xl">✉️</div>
                <h4 class="text-sm font-semibold text-zinc-800 dark:text-zinc-200 m-0">No Pending Invitations</h4>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 max-w-sm mx-auto">
                    Invite team members with 7-day cryptographic tokens and role-based assignments.
                </p>
                <button
                    type="button"
                    onclick="openInviteModal()"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white shadow-xs cursor-pointer"
                >
                    Invite Team Member
                </button>
            </div>

            <table id="invitationsTable" class="data-table w-full text-left text-xs divide-y divide-zinc-200 dark:divide-zinc-800 min-w-[1380px] border-separate border-spacing-0">
                <thead class="bg-zinc-50 dark:bg-zinc-800/80 text-[11px] font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                    <tr>
                        <th data-col="identity" data-default-width="210" data-min-width="130" style="width: 210px; min-width: 130px;" class="py-3 px-4 relative select-none border-r border-zinc-200/70 dark:border-zinc-800/80">
                            <span class="truncate block">Invited Identity</span>
                            <div class="table-col-resizer" title="Drag to resize column (Double-click to reset)"></div>
                        </th>
                        <th data-col="role" data-default-width="160" data-min-width="110" style="width: 160px; min-width: 110px;" class="py-3 px-4 relative select-none border-r border-zinc-200/70 dark:border-zinc-800/80">
                            <span class="truncate block">Role</span>
                            <div class="table-col-resizer" title="Drag to resize column (Double-click to reset)"></div>
                        </th>
                        <th data-col="profile" data-default-width="180" data-min-width="120" style="width: 180px; min-width: 120px;" class="py-3 px-4 relative select-none border-r border-zinc-200/70 dark:border-zinc-800/80">
                            <span class="truncate block">Security Profile</span>
                            <div class="table-col-resizer" title="Drag to resize column (Double-click to reset)"></div>
                        </th>
                        <th data-col="inviter" data-default-width="150" data-min-width="100" style="width: 150px; min-width: 100px;" class="py-3 px-4 relative select-none border-r border-zinc-200/70 dark:border-zinc-800/80">
                            <span class="truncate block">Invited By</span>
                            <div class="table-col-resizer" title="Drag to resize column (Double-click to reset)"></div>
                        </th>
                        <th data-col="sent_date" data-default-width="130" data-min-width="90" style="width: 130px; min-width: 90px;" class="py-3 px-4 relative select-none border-r border-zinc-200/70 dark:border-zinc-800/80">
                            <span class="truncate block">Sent Date</span>
                            <div class="table-col-resizer" title="Drag to resize column (Double-click to reset)"></div>
                        </th>
                        <th data-col="token_expiry" data-default-width="160" data-min-width="110" style="width: 160px; min-width: 110px;" class="py-3 px-4 relative select-none border-r border-zinc-200/70 dark:border-zinc-800/80">
                            <span class="truncate block">Token Expiry (7 Days)</span>
                            <div class="table-col-resizer" title="Drag to resize column (Double-click to reset)"></div>
                        </th>
                        <th data-col="status" data-default-width="130" data-min-width="90" style="width: 130px; min-width: 90px;" class="py-3 px-4 relative select-none border-r border-zinc-200/70 dark:border-zinc-800/80">
                            <span class="truncate block">Status</span>
                            <div class="table-col-resizer" title="Drag to resize column (Double-click to reset)"></div>
                        </th>
                        <th data-col="actions" style="width: 260px; min-width: 260px;" class="py-3 px-4 text-right sticky-actions-col sticky right-0 bg-zinc-50 dark:bg-zinc-800 z-20 border-l border-zinc-200 dark:border-zinc-700 shadow-[-4px_0_8px_-2px_rgba(0,0,0,0.08)]">
                            <span>Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody id="invitationsTbody" class="divide-y divide-zinc-100 dark:divide-zinc-800">
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ===================================================================== -->
<!-- MODAL 1: CREATE / EDIT USER MODAL -->
<!-- ===================================================================== -->
<div id="userModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black/60 backdrop-blur-xs transition-opacity">
    <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl border border-zinc-200 dark:border-zinc-800 max-w-lg w-full overflow-hidden animate-fade-in">
        <div class="flex items-center justify-between px-5 py-4 border-b border-zinc-100 dark:border-zinc-800">
            <div class="flex items-center gap-2">
                <i data-lucide="users" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"></i>
                <h3 id="userModalTitle" class="text-sm font-bold text-zinc-900 dark:text-zinc-100 m-0">
                    Create New CRM User
                </h3>
            </div>
            <button
                type="button"
                onclick="closeUserModal()"
                class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 text-lg leading-none cursor-pointer"
            >
                ✕
            </button>
        </div>

        <form id="userModalForm" onsubmit="saveUserModal(event)">
            <input type="hidden" id="modalUserId" value="">
            <div class="p-5 space-y-4 text-xs">
                <div>
                    <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Full Name *</label>
                    <input
                        type="text"
                        required
                        class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                        id="modalFullName"
                        placeholder="e.g. Sarah Connor"
                    >
                </div>

                <div>
                    <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Email Address *</label>
                    <input
                        type="email"
                        required
                        class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                        id="modalEmail"
                        placeholder="sconnor@acme.com"
                    >
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Assign Role</label>
                        <select class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none" id="modalRoleId">
                            @if(isset($roles) && count($roles) > 0)
                                @foreach($roles as $r)
                                    <option value="{{ $r->id }}">{{ $r->name }}</option>
                                @endforeach
                            @else
                                <option value="role-ceo">Executive / CEO</option>
                                <option value="role-sales-vp">VP of Sales</option>
                                <option value="role-sales-rep" selected>Sales Executive</option>
                                <option value="role-procurement-mgr">Procurement Lead</option>
                                <option value="role-finance-lead">Finance &amp; Billing Lead</option>
                            @endif
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Assign Security Profile</label>
                        <select class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none" id="modalProfileId">
                            @if(isset($profiles) && count($profiles) > 0)
                                @foreach($profiles as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            @else
                                <option value="prof-admin">System Administrator</option>
                                <option value="prof-sales" selected>Standard Sales Representative</option>
                                <option value="prof-procurement">Procurement Specialist</option>
                                <option value="prof-auditor">Read-Only Compliance Auditor</option>
                            @endif
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">User Status</label>
                        <select class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none" id="modalStatus">
                            <option value="Active" selected>Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Phone (Optional)</label>
                        <input
                            type="text"
                            class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                            id="modalPhone"
                            placeholder="+1 (555) 019-2834"
                        >
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Ambient Themes &amp; Glow Access</label>
                    <select class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none" id="modalSurpriseMe">
                        <option value="inherit" selected>Inherit from Profile (Default)</option>
                        <option value="1">Permitted (✨ On)</option>
                        <option value="0">Locked (Disabled)</option>
                    </select>
                    <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-1 mb-0">
                        Control whether this user can choose and customize ambient workspace themes.
                    </p>
                </div>
            </div>

            <div class="px-5 py-3.5 bg-zinc-50 dark:bg-zinc-800/50 border-t border-zinc-100 dark:border-zinc-800 flex justify-end gap-2">
                <button type="button" onclick="closeUserModal()" class="px-3.5 py-1.5 text-xs font-semibold rounded-lg bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 cursor-pointer shadow-2xs">
                    Cancel
                </button>
                <button type="submit" id="modalSubmitBtn" class="px-4 py-1.5 text-xs font-semibold rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white cursor-pointer shadow-xs">
                    Create User
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ===================================================================== -->
<!-- MODAL 2: TEAM MEMBER INVITATION MODAL -->
<!-- ===================================================================== -->
<div id="inviteModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black/60 backdrop-blur-xs transition-opacity">
    <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl border border-zinc-200 dark:border-zinc-800 max-w-lg w-full overflow-hidden animate-fade-in">
        <div class="flex items-center justify-between px-5 py-4 border-b border-zinc-100 dark:border-zinc-800">
            <div>
                <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2 m-0">
                    <i data-lucide="mail" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"></i>
                    <span>Invite New Team Member</span>
                </h3>
                <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5 mb-0">
                    Send a personalized onboarding invitation with a secure 7-day activation link.
                </p>
            </div>
            <button
                type="button"
                onclick="closeInviteModal()"
                class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 text-lg leading-none cursor-pointer"
            >
                ✕
            </button>
        </div>

        <!-- Form View -->
        <form id="inviteForm" onsubmit="submitInviteForm(event)">
            <div class="p-5 space-y-4 text-xs">
                <!-- Seat Quota Status Pill -->
                <div class="bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200 dark:border-zinc-700/80 rounded-xl p-3 flex justify-between items-center text-xs">
                    <span class="text-zinc-600 dark:text-zinc-300 font-medium">
                        ⚡ Organization License Capacity:
                    </span>
                    <span id="inviteModalRemainingSeats" class="font-bold text-emerald-600">
                        {{ max(0, ($maxSeats ?? 15) - (count($users ?? []) + count($invitations ?? []))) }} Seats Remaining
                    </span>
                </div>

                <div>
                    <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Recipient Work Email *</label>
                    <input
                        type="email"
                        required
                        placeholder="name@company.com"
                        class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                        id="inviteEmail"
                    >
                </div>

                <div>
                    <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Full Name *</label>
                    <input
                        type="text"
                        required
                        placeholder="e.g. Samantha Vance"
                        class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                        id="inviteFullName"
                    >
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Role Assignment</label>
                        <select class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none" id="inviteRole">
                            @if(isset($roles) && count($roles) > 0)
                                @foreach($roles as $r)
                                    <option value="{{ $r->id }}" {{ str_contains(strtolower($r->name), 'sales') ? 'selected' : '' }}>{{ $r->name }}</option>
                                @endforeach
                            @else
                                <option value="role-sales-rep" selected>Sales Executive</option>
                                <option value="role-sales-vp">VP of Sales</option>
                                <option value="role-procurement-mgr">Procurement Lead</option>
                                <option value="role-finance-lead">Finance &amp; Billing Lead</option>
                                <option value="role-ceo">Executive / CEO</option>
                            @endif
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Security Profile</label>
                        <select class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none" id="inviteProfile">
                            @if(isset($profiles) && count($profiles) > 0)
                                @foreach($profiles as $p)
                                    <option value="{{ $p->id }}" {{ str_contains(strtolower($p->name), 'sales') ? 'selected' : '' }}>{{ $p->name }}</option>
                                @endforeach
                            @else
                                <option value="prof-sales" selected>Standard Sales Representative</option>
                                <option value="prof-admin">System Administrator</option>
                                <option value="prof-procurement">Procurement Specialist</option>
                                <option value="prof-auditor">Read-Only Compliance Auditor</option>
                            @endif
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Direct Reporting Manager (Optional)</label>
                    <select class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none" id="inviteManager">
                        <option value="">-- No Direct Manager (Top Level) --</option>
                        @if(isset($users) && count($users) > 0)
                            @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->role_name ?? 'User' }})</option>
                            @endforeach
                        @else
                            <option value="1">Alex Morgan (Executive / CEO)</option>
                        @endif
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Custom Welcome Note (Optional)</label>
                    <textarea
                        rows="3"
                        class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                        id="inviteWelcomeNote"
                        placeholder="Welcome to our sales team! Please click the link below to set your password and get started."
                    ></textarea>
                </div>
            </div>

            <div class="px-5 py-3.5 bg-zinc-50 dark:bg-zinc-800/50 border-t border-zinc-100 dark:border-zinc-800 flex justify-end gap-2">
                <button
                    type="button"
                    class="px-3.5 py-1.5 text-xs font-semibold rounded-lg bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 cursor-pointer shadow-2xs"
                    onclick="closeInviteModal()"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    class="inline-flex items-center gap-1.5 px-4 py-1.5 text-xs font-semibold rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white cursor-pointer shadow-xs"
                >
                    <i data-lucide="send" class="w-3.5 h-3.5"></i>
                    <span>Send Invitation Email</span>
                </button>
            </div>
        </form>

        <!-- Success View -->
        <div id="inviteSuccessScreen" class="p-6 space-y-4 text-xs" style="display: none;">
            <div class="bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-xl p-4 text-center space-y-1">
                <i data-lucide="check-circle-2" class="w-8 h-8 text-emerald-500 mx-auto"></i>
                <div class="text-sm font-bold text-emerald-700 dark:text-emerald-300">
                    Invitation Dispatched Successfully!
                </div>
                <p class="text-xs text-emerald-600 dark:text-emerald-400 m-0">
                    A branded email has been dispatched via your SMTP gateway to <strong id="successInvitedEmail"></strong>.
                </p>
            </div>

            <div class="space-y-1.5">
                <label class="block text-xs font-medium text-zinc-600 dark:text-zinc-300">
                    One-Click Activation URL (Valid for 7 Days)
                </label>
                <div class="flex gap-2">
                    <input
                        type="text"
                        readonly
                        class="w-full font-mono text-xs bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg px-3 py-2 text-zinc-600 dark:text-zinc-300"
                        id="successActivationUrl"
                    >
                    <button
                        type="button"
                        onclick="copyActivationUrl()"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-lg bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 cursor-pointer shadow-2xs whitespace-nowrap"
                    >
                        <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                        <span id="copyUrlBtnText">Copy Link</span>
                    </button>
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button
                    type="button"
                    class="px-4 py-1.5 text-xs font-semibold rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white cursor-pointer shadow-xs"
                    onclick="closeInviteModal()"
                >
                    Done
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ===================================================================== -->
<!-- MODAL 3: USER OFFBOARDING & REASSIGNMENT WIZARD -->
<!-- ===================================================================== -->
<div id="offboardModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black/60 backdrop-blur-xs transition-opacity">
    <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl border border-zinc-200 dark:border-zinc-800 max-w-lg w-full max-h-[90vh] overflow-y-auto animate-fade-in">
        <div class="flex items-center justify-between px-5 py-4 border-b border-zinc-100 dark:border-zinc-800 sticky top-0 bg-white dark:bg-zinc-900 z-10">
            <div>
                <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2 m-0">
                    <i data-lucide="user-x" class="w-4 h-4 text-rose-500"></i>
                    <span>Offboard User &amp; Reassign Records</span>
                </h3>
                <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5 mb-0">
                    Deactivate <strong id="offboardTargetName" class="font-bold text-zinc-800 dark:text-zinc-200">Jordan Lee</strong> and safely reassign their active CRM records.
                </p>
            </div>
            <button
                type="button"
                onclick="closeOffboardModal()"
                class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 text-lg leading-none cursor-pointer"
            >
                ✕
            </button>
        </div>

        <div class="p-5 space-y-5 text-xs">
            <!-- Step 1: Record Inventory Scan -->
            <div class="bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200 dark:border-zinc-700/80 rounded-xl p-4 space-y-3">
                <div class="text-xs font-bold text-zinc-900 dark:text-zinc-100">
                    Step 1: Record Ownership Inventory
                </div>

                <div class="grid grid-cols-2 gap-2.5">
                    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg p-3">
                        <div class="flex items-center gap-1.5 text-zinc-500 text-[11px]">
                            <i data-lucide="users" class="w-3.5 h-3.5 text-indigo-500"></i>
                            <span>Assigned Leads</span>
                        </div>
                        <div id="offboardLeadsCount" class="text-lg font-bold text-zinc-900 dark:text-zinc-100 mt-1">
                            14
                        </div>
                    </div>

                    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg p-3">
                        <div class="flex items-center gap-1.5 text-zinc-500 text-[11px]">
                            <i data-lucide="building" class="w-3.5 h-3.5 text-sky-500"></i>
                            <span>Assigned Accounts</span>
                        </div>
                        <div id="offboardAccountsCount" class="text-lg font-bold text-zinc-900 dark:text-zinc-100 mt-1">
                            8
                        </div>
                    </div>

                    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg p-3">
                        <div class="flex items-center gap-1.5 text-zinc-500 text-[11px]">
                            <i data-lucide="target" class="w-3.5 h-3.5 text-emerald-500"></i>
                            <span>Pipeline Opportunities</span>
                        </div>
                        <div id="offboardOppsCount" class="text-lg font-bold text-zinc-900 dark:text-zinc-100 mt-1">
                            6 ($380,000)
                        </div>
                    </div>

                    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg p-3">
                        <div class="flex items-center gap-1.5 text-zinc-500 text-[11px]">
                            <i data-lucide="check-square" class="w-3.5 h-3.5 text-amber-500"></i>
                            <span>Open Tasks</span>
                        </div>
                        <div id="offboardTasksCount" class="text-lg font-bold text-zinc-900 dark:text-zinc-100 mt-1">
                            5
                        </div>
                    </div>
                </div>

                <div class="text-xs text-zinc-600 dark:text-zinc-300 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg p-2.5 flex justify-between items-center">
                    <span>Total CRM Records at Risk:</span>
                    <strong class="text-rose-600 dark:text-rose-400 font-bold" id="offboardTotalCount">33 records</strong>
                </div>
            </div>

            <!-- Step 2: Destination User Picker -->
            <div class="space-y-1.5">
                <label class="block font-bold text-zinc-900 dark:text-zinc-100 text-xs">
                    Step 2: Successor Team Member Assignment
                </label>
                <p class="text-[11px] text-zinc-500 dark:text-zinc-400 m-0">
                    Select an active colleague to inherit all leads, accounts, pipeline deals, and pending tasks.
                </p>
                <select id="offboardDestinationUser" class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-2 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none font-medium">
                </select>
            </div>

            <!-- Step 3: Deactivation Alert Banner -->
            <div class="bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/80 rounded-xl p-3.5 text-xs text-amber-800 dark:text-amber-300 space-y-1 leading-relaxed">
                <div class="font-bold flex items-center gap-1.5">
                    <i data-lucide="shield-alert" class="w-4 h-4 text-amber-600 dark:text-amber-400"></i>
                    <span>Deactivation &amp; License Reclaim Action:</span>
                </div>
                <div>
                    Executing offboarding will change the user's status to <strong>Inactive</strong>, terminate all active browser sessions, and <strong>instantly reclaim 1 seat license</strong> back to your organization subscription pool.
                </div>
            </div>
        </div>

        <div class="px-5 py-3.5 bg-zinc-50 dark:bg-zinc-800/50 border-t border-zinc-100 dark:border-zinc-800 flex justify-end gap-2 sticky bottom-0 z-10">
            <button
                type="button"
                class="px-3.5 py-1.5 text-xs font-semibold rounded-lg bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 cursor-pointer shadow-2xs"
                onclick="closeOffboardModal()"
            >
                Cancel
            </button>
            <button
                type="button"
                onclick="executeOffboarding()"
                class="inline-flex items-center gap-1.5 px-4 py-1.5 text-xs font-semibold rounded-lg bg-rose-600 hover:bg-rose-700 text-white cursor-pointer shadow-xs"
            >
                <i data-lucide="user-x" class="w-3.5 h-3.5"></i>
                <span>Confirm Offboarding &amp; Reclaim Seat</span>
            </button>
        </div>
    </div>
</div>

<!-- ===================================================================== -->
<!-- MODAL 4: PUBLIC SELF-ACTIVATION PREVIEW MODAL -->
<!-- ===================================================================== -->
<div id="previewInviteModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black/60 backdrop-blur-xs transition-opacity">
    <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl border border-zinc-200 dark:border-zinc-800 max-w-md w-full overflow-hidden animate-fade-in">
        <div class="px-5 py-4 bg-gradient-to-r from-indigo-900 to-violet-900 text-white flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <i data-lucide="sparkles" class="w-4 h-4 text-indigo-300"></i>
                    <h3 class="text-sm font-bold text-white m-0">
                        DealSync CRM — Candidate Activation
                    </h3>
                </div>
                <div class="text-[11px] text-indigo-200 mt-0.5">
                    Previewing candidate invitation experience (7-day token verification)
                </div>
            </div>
            <button
                type="button"
                onclick="closePreviewInviteModal()"
                class="text-indigo-200 hover:text-white text-lg leading-none cursor-pointer"
            >
                ✕
            </button>
        </div>

        <div class="p-5 space-y-4 text-xs">
            <div class="bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-xl p-3 flex items-center gap-2.5">
                <i data-lucide="shield-check" class="w-5 h-5 text-emerald-600 shrink-0"></i>
                <div>
                    <div class="text-xs font-bold text-emerald-800 dark:text-emerald-300">Valid Cryptographic Token</div>
                    <div class="text-[11px] text-emerald-600 dark:text-emerald-400">Organization invitation verified.</div>
                </div>
            </div>

            <div class="space-y-0.5">
                <div class="text-[11px] text-zinc-400">Invited Candidate:</div>
                <div id="previewCandidateName" class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Samantha Vance</div>
                <div id="previewCandidateEmail" class="text-xs text-zinc-600 dark:text-zinc-400">samantha.vance@acmecorp.com</div>
            </div>

            <div>
                <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Create Enterprise Password</label>
                <input type="password" class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 px-3 py-2 text-xs text-zinc-500 cursor-not-allowed" value="••••••••••••" readonly>
            </div>

            <div class="bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200 dark:border-zinc-700 rounded-xl p-3 space-y-1.5 text-[11px] text-zinc-600 dark:text-zinc-300">
                <div class="font-semibold text-zinc-800 dark:text-zinc-200">Password Policy Requirements:</div>
                <div class="grid grid-cols-2 gap-1 text-emerald-600 dark:text-emerald-400">
                    <span>✓ Minimum 8 chars</span>
                    <span>✓ 1+ Uppercase letter</span>
                    <span>✓ 1+ Numeric digit</span>
                    <span>✓ 1+ Special character</span>
                </div>
            </div>
        </div>

        <div class="px-5 py-3.5 bg-zinc-50 dark:bg-zinc-800/50 border-t border-zinc-100 dark:border-zinc-800 flex justify-end gap-2">
            <button type="button" onclick="closePreviewInviteModal()" class="px-3.5 py-1.5 text-xs font-semibold rounded-lg bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 cursor-pointer shadow-2xs">
                Close Preview
            </button>
            <button type="button" onclick="showFeedback('Candidate self-activation simulated!')" class="px-4 py-1.5 text-xs font-semibold rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white cursor-pointer shadow-xs">
                Activate Account (Simulation)
            </button>
        </div>
    </div>
</div>

<!-- ===================================================================== -->
<!-- MODAL 5: SUBSCRIPTION ADD SEATS PURCHASE MODAL -->
<!-- ===================================================================== -->
<div id="addSeatsModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black/60 backdrop-blur-xs transition-opacity">
    <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl border border-zinc-200 dark:border-zinc-800 max-w-md w-full overflow-hidden animate-fade-in">
        <div class="flex items-center justify-between px-5 py-4 border-b border-zinc-100 dark:border-zinc-800">
            <div class="flex items-center gap-2">
                <i data-lucide="credit-card" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"></i>
                <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 m-0">
                    Add Enterprise Seat Licenses
                </h3>
            </div>
            <button
                type="button"
                onclick="closeAddSeatsModal()"
                class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 text-lg leading-none cursor-pointer"
            >
                ✕
            </button>
        </div>

        <div class="p-5 space-y-4 text-xs">
            <div class="bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200 dark:border-zinc-700 rounded-xl p-3.5 space-y-1">
                <div class="text-[11px] text-zinc-400">Current Organization Plan:</div>
                <div class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Enterprise Tier ($39 / seat / mo)</div>
                <div class="text-[11px] text-zinc-500">Currently provisioned: <strong id="currentSeatsLabel">{{ $maxSeats ?? 15 }} seats</strong></div>
            </div>

            <div>
                <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">New Total Seat Allocation</label>
                <div class="flex items-center gap-2">
                    <button type="button" class="w-9 h-9 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200 font-bold hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors cursor-pointer" onclick="adjustSeatCount(-1)">-</button>
                    <input type="number" id="newSeatsCountInput" value="{{ ($maxSeats ?? 15) + 2 }}" min="6" max="100" class="flex-1 text-center font-bold text-base h-9 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 focus:ring-2 focus:ring-indigo-500 focus:outline-none" oninput="updateAddSeatsCost()">
                    <button type="button" class="w-9 h-9 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200 font-bold hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors cursor-pointer" onclick="adjustSeatCount(1)">+</button>
                </div>
            </div>

            <div class="bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-800 rounded-xl p-3.5 flex justify-between items-center">
                <div>
                    <div class="text-xs text-indigo-700 dark:text-indigo-300 font-semibold">Monthly Billing Increase:</div>
                    <div class="text-[11px] text-indigo-500">Prorated on next billing cycle</div>
                </div>
                <div id="seatsCostIncrease" class="text-base font-bold text-indigo-800 dark:text-indigo-200">
                    +$78.00 / mo
                </div>
            </div>
        </div>

        <div class="px-5 py-3.5 bg-zinc-50 dark:bg-zinc-800/50 border-t border-zinc-100 dark:border-zinc-800 flex justify-end gap-2">
            <button type="button" onclick="closeAddSeatsModal()" class="px-3.5 py-1.5 text-xs font-semibold rounded-lg bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 cursor-pointer shadow-2xs">
                Cancel
            </button>
            <button type="button" onclick="confirmAddSeats()" class="px-4 py-1.5 text-xs font-semibold rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white cursor-pointer shadow-xs">
                Confirm &amp; Provision Seats
            </button>
        </div>
    </div>
</div>

<!-- FLOATING FEEDBACK BANNER -->
<div id="feedbackBanner" class="hidden fixed bottom-6 right-6 bg-zinc-900 text-white px-4 py-3 rounded-xl shadow-lg items-center gap-2 text-xs font-medium z-50 animate-fade-in border border-zinc-800">
    <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-400 shrink-0"></i>
    <span id="feedbackBannerText">Operation successful!</span>
</div>

<!-- Initial Data Hydration from Controller -->
<script>
    window.INITIAL_USERS = @json($users ?? []);
    window.INITIAL_INVITATIONS = @json($invitations ?? []);
    window.INITIAL_ROLES = @json($roles ?? []);
    window.INITIAL_PROFILES = @json($profiles ?? []);
    window.INITIAL_MAX_SEATS = {{ (int)($maxSeats ?? 15) }};
</script>

<!-- Dedicated Isolated External Script -->
<script src="{{ asset('js/users-directory.js') }}?v=1.2"></script>
<script>
    if (typeof initUsersDirectory === 'function') {
        initUsersDirectory();
    }
</script>
@endsection
