<header id="dealsync-header" class="sticky top-0 z-30 flex h-14 w-full items-center justify-between border-b backdrop-blur-md px-3 sm:px-6 transition-colors shadow-2xs" style="background: var(--header-bg, #312e81) !important; border-color: var(--border-card, #e2e8f0) !important;">
    <!-- Left Region: Sidebar Pin/Collapse Toggle & Tenant Switcher -->
    <div class="flex items-center gap-2 sm:gap-3">
        <!-- Sidebar Pin / Collapse Toggle Button -->
        <button
            id="header-sidebar-toggle-btn"
            type="button"
            onclick="toggleSidebarPin()"
            title="Toggle Sidebar Pin (Ctrl+B)"
            class="inline-flex items-center justify-center rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 p-2 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 hover:text-zinc-950 dark:hover:text-white transition shadow-2xs cursor-pointer"
        >
            <i id="header-sidebar-toggle-icon" data-lucide="panel-left-close" class="w-4 h-4"></i>
        </button>

        <!-- Tenant Label & Selector -->
        <div class="flex items-center gap-1.5 text-zinc-500 dark:text-zinc-400">
            <i data-lucide="building-2" class="w-4 h-4 text-zinc-400 dark:text-zinc-500 shrink-0"></i>
            <span class="hidden md:inline text-xs font-semibold tracking-wide text-zinc-500 dark:text-zinc-400">
                Tenant:
            </span>
        </div>

        @php
            $headerTenants = \App\Models\Tenant::on('mysql')->where('is_active', true)->get();
        @endphp
        <select
            id="header-tenant-select"
            class="text-xs font-semibold text-zinc-900 dark:text-zinc-100 bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg px-2.5 py-1.5 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:outline-none transition cursor-pointer shadow-2xs"
            onchange="window.location.search = '?tenant_id=' + this.value"
        >
            @foreach($headerTenants as $ht)
                <option value="{{ $ht->id }}" {{ (isset($currentTenant) && $currentTenant->id === $ht->id) ? 'selected' : '' }}>
                    {{ $ht->name }}
                </option>
            @endforeach
        </select>

        <!-- Refresh Data Trigger -->
        <button
            type="button"
            onclick="showToast('Data refreshed successfully'); if(window.lucide) lucide.createIcons();"
            class="hidden sm:inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-medium text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:text-zinc-950 dark:hover:text-zinc-100 transition cursor-pointer"
            title="Reload Data"
        >
            <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
            <span>Refresh</span>
        </button>

        @if(request()->is('admin*'))
            <div class="hidden sm:flex items-center gap-2">
                <span class="text-zinc-300 dark:text-zinc-700">•</span>
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold" style="background: var(--color-primary-subtle, #eef2ff); color: var(--color-primary-text, #3730a3); border: 1px solid var(--color-primary-border, #c7d2fe);">
                    ⚙️ Admin Console
                </span>
            </div>
        @endif
    </div>

    <!-- Center Region: Global Search Bar -->
    @if(!request()->is('admin*'))
        <div class="flex-1 max-w-md mx-3 sm:mx-6 hidden md:block">
            <div class="relative flex items-center">
                <i data-lucide="search" class="w-4 h-4 text-zinc-400 absolute left-3 pointer-events-none"></i>
                <input
                    id="global-search-input"
                    type="text"
                    placeholder="Search across all modules & records... (Live Filter)"
                    autocomplete="off"
                    class="w-full rounded-full border border-zinc-200 dark:border-zinc-700 bg-zinc-100/70 dark:bg-zinc-800/60 py-1.5 pl-9 pr-14 text-xs text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:bg-white dark:focus:bg-zinc-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition shadow-2xs"
                >
                <kbd class="absolute right-2.5 rounded border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-1.5 py-0.5 text-[10px] font-semibold text-zinc-400 dark:text-zinc-500 pointer-events-none">
                    Ctrl+K
                </kbd>
            </div>
        </div>
    @else
        <div class="flex-1"></div>
    @endif

    <!-- Right Region: Quick Actions, Theme, Notifications & User Profile -->
    <div class="flex items-center gap-2 sm:gap-2.5">
        <!-- Quick Create Button -->
        <button
            type="button"
            onclick="openNewRecordModal()"
            class="hidden lg:inline-flex items-center gap-1.5 rounded-lg text-white text-xs font-semibold px-3 py-1.5 shadow-2xs hover:shadow transition cursor-pointer"
            style="background: var(--color-primary, #4f46e5); border: 1px solid var(--color-primary-hover, #4338ca);"
            title="Quick Create Lead, Account, Deal or Task"
        >
            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
            <span>New Record</span>
        </button>

        <!-- Ambient Theme Pill Button -->
        <button
            type="button"
            onclick="openThemeModal()"
            class="inline-flex items-center gap-1.5 rounded-full border border-amber-200/90 dark:border-amber-900/60 bg-amber-50/80 dark:bg-amber-950/40 px-3 py-1 text-xs font-semibold text-amber-800 dark:text-amber-300 hover:bg-amber-100 dark:hover:bg-amber-900/60 transition shadow-2xs cursor-pointer"
            title="Ambient Theme & Glow Customization Studio"
        >
            <i data-lucide="sparkles" class="w-3.5 h-3.5 text-amber-500"></i>
            <span class="hidden sm:inline">Ambient Themes</span>
        </button>

        <!-- Dark / Light Mode Toggle Button -->
        <button
            type="button"
            onclick="toggleDarkMode()"
            class="inline-flex items-center justify-center rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 p-2 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 hover:text-zinc-950 dark:hover:text-white transition shadow-2xs cursor-pointer"
            title="Toggle Dark / Light Mode"
        >
            <i data-lucide="moon" class="w-4 h-4 dark:hidden"></i>
            <i data-lucide="sun" class="w-4 h-4 hidden dark:block text-amber-400"></i>
        </button>

        <!-- Notification Bell Hub -->
        <div class="relative" id="header-notif-container">
            <button
                id="notif-dropdown-btn"
                type="button"
                class="relative inline-flex items-center justify-center rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 p-2 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 hover:text-zinc-950 dark:hover:text-white transition shadow-2xs cursor-pointer"
                title="Approval Alerts & Notifications"
                onclick="toggleNotifDropdown(event)"
            >
                <i data-lucide="bell" class="w-4 h-4"></i>
                <span id="notif-badge-pill" class="absolute -top-1 -right-1 bg-rose-500 text-white text-[10px] font-bold rounded-full h-4 min-w-[16px] px-1 flex items-center justify-center ring-2 ring-white dark:ring-zinc-900">
                    3
                </span>
            </button>

            <!-- Floating Notification Menu -->
            <div id="notif-dropdown-menu" class="hidden absolute right-0 mt-2 w-80 sm:w-96 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 shadow-xl z-50 overflow-hidden text-zinc-900 dark:text-zinc-100">
                <div class="px-4 py-3 border-b border-zinc-100 dark:border-zinc-800 flex items-center justify-between bg-zinc-50 dark:bg-zinc-800/50">
                    <div class="font-semibold text-xs text-zinc-900 dark:text-zinc-100">
                        Notifications &amp; Approval Alerts
                    </div>
                    <button
                        type="button"
                        onclick="markAllNotificationsRead()"
                        class="text-[11px] font-medium text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200 flex items-center gap-1 cursor-pointer"
                    >
                        <i data-lucide="check-check" class="w-3 h-3"></i>
                        <span>Mark all read</span>
                    </button>
                </div>

                <div class="max-h-80 overflow-y-auto divide-y divide-zinc-100 dark:divide-zinc-800" id="notif-items-list">
                    <!-- Notification 1 -->
                    <div class="notif-item p-3 bg-sky-50/70 dark:bg-sky-950/30 hover:bg-sky-100/60 dark:hover:bg-sky-900/30 cursor-pointer transition" onclick="markSingleNotifRead(this)">
                        <div class="flex items-start justify-between gap-2 mb-1">
                            <div class="flex items-center gap-1.5">
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-amber-100 dark:bg-amber-900/50 text-amber-800 dark:text-amber-300">
                                    Email
                                </span>
                                <span class="font-semibold text-xs text-zinc-900 dark:text-zinc-100">
                                    Purchase Contract Approval
                                </span>
                            </div>
                            <span class="unread-dot w-2 h-2 rounded-full bg-blue-600 shrink-0 mt-1"></span>
                        </div>
                        <p class="text-[11px] text-zinc-600 dark:text-zinc-400 leading-relaxed">
                            PO-88291 for Apex Logistics requires executive sign-off before dispatch.
                        </p>
                        <span class="text-[10px] text-zinc-400 mt-1 block">10:45 AM</span>
                    </div>

                    <!-- Notification 2 -->
                    <div class="notif-item p-3 bg-sky-50/70 dark:bg-sky-950/30 hover:bg-sky-100/60 dark:hover:bg-sky-900/30 cursor-pointer transition" onclick="markSingleNotifRead(this)">
                        <div class="flex items-start justify-between gap-2 mb-1">
                            <div class="flex items-center gap-1.5">
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-blue-100 dark:bg-blue-900/50 text-blue-800 dark:text-blue-300">
                                    Alert
                                </span>
                                <span class="font-semibold text-xs text-zinc-900 dark:text-zinc-100">
                                    Deal Stage Updated
                                </span>
                            </div>
                            <span class="unread-dot w-2 h-2 rounded-full bg-blue-600 shrink-0 mt-1"></span>
                        </div>
                        <p class="text-[11px] text-zinc-600 dark:text-zinc-400 leading-relaxed">
                            Skyline Cloud Systems advanced to Closed Won ($125,000).
                        </p>
                        <span class="text-[10px] text-zinc-400 mt-1 block">09:15 AM</span>
                    </div>

                    <!-- Notification 3 -->
                    <div class="notif-item p-3 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 cursor-pointer transition" onclick="markSingleNotifRead(this)">
                        <div class="flex items-start justify-between gap-2 mb-1">
                            <div class="flex items-center gap-1.5">
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-emerald-100 dark:bg-emerald-900/50 text-emerald-800 dark:text-emerald-300">
                                    Treasury
                                </span>
                                <span class="font-semibold text-xs text-zinc-900 dark:text-zinc-100">
                                    Wire Transfer Reconciled
                                </span>
                            </div>
                        </div>
                        <p class="text-[11px] text-zinc-600 dark:text-zinc-400 leading-relaxed">
                            UTR4491029381 confirmed ($38,000) from Skyline Cloud Systems.
                        </p>
                        <span class="text-[10px] text-zinc-400 mt-1 block">Yesterday, 4:20 PM</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Interactive User Profile Trigger & Menu -->
        <div class="relative" id="user-profile-menu-container">
            <button
                type="button"
                id="user-profile-trigger"
                onclick="toggleUserDropdown(event)"
                class="flex items-center gap-2 rounded-full border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 p-1 pr-2.5 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition shadow-2xs cursor-pointer"
                title="Account Settings & Profile"
            >
                <div class="w-7 h-7 rounded-full bg-gradient-to-tr from-indigo-600 to-violet-600 text-white font-bold text-xs flex items-center justify-center shadow-xs">
                    {{ Auth::guard('super')->check() ? 'SA' : 'AM' }}
                </div>

                @php
                    $authUser = Auth::guard('super')->user() ?? Auth::guard('tenant')->user() ?? Auth::guard('web')->user() ?? Auth::user();
                    $isSuper = Auth::guard('super')->check();

                    $isTenantAdmin = false;
                    if ($isSuper) {
                        $isTenantAdmin = true;
                    } elseif ($authUser instanceof \App\Models\TenantUser) {
                        $isTenantAdmin = $authUser->isAdmin();
                    } elseif ($authUser && method_exists($authUser, 'isAdmin')) {
                        $isTenantAdmin = $authUser->isAdmin();
                    } elseif ($authUser && !empty($authUser->id)) {
                        try {
                            $tu = \App\Models\TenantUser::on('tenant')->find($authUser->id);
                            $isTenantAdmin = $tu?->isAdmin() ?? false;
                        } catch (\Throwable $e) {}
                    }

                    $roleTitle = $isSuper ? 'Central Super Admin' : ($authUser?->role_name ?? ($isTenantAdmin ? 'Tenant Administrator' : 'Team Member'));
                    $displayName = $authUser?->name ?? 'Administrator';
                    $displayEmail = $authUser?->email ?? 'admin@dealsync.com';
                @endphp
                <div class="hidden sm:flex flex-col text-left leading-tight">
                    <span class="text-xs font-semibold text-zinc-900 dark:text-zinc-100">
                        {{ $displayName }}
                    </span>
                    <span class="text-[10px] text-zinc-500 dark:text-zinc-400">
                        {{ $roleTitle }}
                    </span>
                </div>

                <i data-lucide="chevron-down" class="w-3 h-3 text-zinc-400"></i>
            </button>

            <!-- User Account Dropdown -->
            <div id="user-profile-menu" class="hidden absolute right-0 mt-2 w-64 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 shadow-xl z-50 p-1.5 overflow-hidden text-xs text-zinc-900 dark:text-zinc-100">
                <div class="px-3 py-2.5 border-b border-zinc-100 dark:border-zinc-800 mb-1">
                    <div class="font-semibold text-xs text-zinc-900 dark:text-zinc-100">
                        {{ $displayName }}
                    </div>
                    <div class="text-[11px] text-zinc-500 dark:text-zinc-400 truncate">
                        {{ $displayEmail }}
                    </div>
                    <div class="mt-1.5">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800">
                            {{ $roleTitle }}
                        </span>
                    </div>
                </div>

                <!-- 1. My Profile & Settings -->
                <button
                    type="button"
                    onclick="openUserProfileModal(); closeUserDropdown();"
                    class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition text-left cursor-pointer font-medium"
                >
                    <i data-lucide="user" class="w-4 h-4 text-zinc-500"></i>
                    <span>My Profile &amp; Settings</span>
                </button>

                <!-- 2. Setting & Schema (Admin Console) -->
                @if($isTenantAdmin)
                <a
                    href="{{ url('/settings') }}"
                    class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition text-left cursor-pointer font-medium"
                >
                    <div class="flex items-center gap-2.5">
                        <i data-lucide="sliders" class="w-4 h-4 text-indigo-600"></i>
                        <span>Setting &amp; Schema</span>
                    </div>
                    <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">Admin</span>
                </a>
                @endif

                <!-- 3. Ambient Themes & Glow -->
                <button
                    type="button"
                    onclick="openThemeModal(); closeUserDropdown();"
                    class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition text-left cursor-pointer font-medium"
                >
                    <div class="flex items-center gap-2.5">
                        <i data-lucide="palette" class="w-4 h-4 text-amber-500"></i>
                        <span>Ambient Themes &amp; Glow</span>
                    </div>
                </button>

                <div class="my-1 border-t border-zinc-100 dark:border-zinc-800"></div>

                <!-- 4. Logout / Login Controls -->
                @if(Auth::guard('super')->check())
                    <form method="POST" action="{{ route('super_admin.logout') }}" class="m-0">
                        @csrf
                        <button
                            type="submit"
                            class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition text-left cursor-pointer font-semibold"
                        >
                            <i data-lucide="log-out" class="w-4 h-4"></i>
                            <span>Logout Super Admin</span>
                        </button>
                    </form>
                @elseif(Auth::guard('web')->check() || Auth::check())
                    <form method="POST" action="{{ route('tenant.logout') }}" class="m-0">
                        @csrf
                        <button
                            type="submit"
                            class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition text-left cursor-pointer font-semibold"
                        >
                            <i data-lucide="log-out" class="w-4 h-4"></i>
                            <span>Logout Workspace</span>
                        </button>
                    </form>
                @else
                    <a
                        href="{{ route('tenant.login') }}"
                        class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 transition text-left cursor-pointer font-semibold"
                    >
                        <i data-lucide="shield-check" class="w-4 h-4"></i>
                        <span>Sign In</span>
                    </a>
                @endif
            </div>
        </div>
    </div>
</header>

<script>
    function toggleUserDropdown(e) {
        if (e) e.stopPropagation();
        var menu = document.getElementById('user-profile-menu');
        if (menu) {
            menu.classList.toggle('hidden');
        }
    }

    function closeUserDropdown() {
        var menu = document.getElementById('user-profile-menu');
        if (menu) menu.classList.add('hidden');
    }

    function toggleNotifDropdown(e) {
        if (e) e.stopPropagation();
        var menu = document.getElementById('notif-dropdown-menu');
        if (menu) {
            menu.classList.toggle('hidden');
        }
    }

    function markAllNotificationsRead() {
        document.querySelectorAll('#notif-items-list .notif-item').forEach(function(item) {
            item.classList.remove('bg-sky-50/70', 'dark:bg-sky-950/30');
        });
        document.querySelectorAll('#notif-items-list .unread-dot').forEach(function(dot) {
            dot.classList.add('hidden');
        });
        var badge = document.getElementById('notif-badge-pill');
        if (badge) badge.classList.add('hidden');
        showToast('All notifications marked as read');
    }

    function markSingleNotifRead(item) {
        item.classList.remove('bg-sky-50/70', 'dark:bg-sky-950/30');
        var dot = item.querySelector('.unread-dot');
        if (dot) dot.classList.add('hidden');
    }

    document.addEventListener('click', function(e) {
        var userContainer = document.getElementById('user-profile-menu-container');
        var userMenu = document.getElementById('user-profile-menu');
        if (userContainer && userMenu && !userContainer.contains(e.target)) {
            userMenu.classList.add('hidden');
        }

        var notifContainer = document.getElementById('header-notif-container');
        var notifMenu = document.getElementById('notif-dropdown-menu');
        if (notifContainer && notifMenu && !notifContainer.contains(e.target)) {
            notifMenu.classList.add('hidden');
        }
    });
</script>
