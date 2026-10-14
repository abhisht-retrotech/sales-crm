<header class="app-header" style="height: 58px; background: var(--header-bg, #ffffff); border-bottom: 1px solid var(--border-card); padding: 0 2rem; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 30; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.02);">
    <!-- Left side: Sidebar Toggle & Tenant Switcher -->
    <div style="display: flex; align-items: center; gap: 0.75rem;">
        <!-- Sidebar Pin / Collapse Toggle -->
        <button
            id="header-sidebar-toggle-btn"
            type="button"
            onclick="toggleSidebarPin()"
            title="Unpin Sidebar - Slide-Out on Hover (Ctrl+B)"
            style="display: inline-flex; align-items: center; justify-content: center; padding: 0.4rem; border-radius: var(--radius-md); border: 1px solid var(--zinc-200); background: var(--zinc-50); color: var(--zinc-600); cursor: pointer; transition: all 0.15s ease;"
            onmouseenter="this.style.background='var(--zinc-100)'; this.style.color='var(--zinc-950)';"
            onmouseleave="this.style.background='var(--zinc-50)'; this.style.color='var(--zinc-600)';"
        >
            <i id="header-sidebar-toggle-icon" data-lucide="panel-left-close" style="width: 16px; height: 16px;"></i>
        </button>

        <!-- Tenant Label & Selector -->
        <div style="display: flex; align-items: center; gap: 0.4rem; color: var(--zinc-500);">
            <i data-lucide="building-2" style="width: 15px; height: 15px;"></i>
            <span style="font-size: 0.75rem; font-weight: 600; color: var(--zinc-500); letter-spacing: 0.01em;">
                Tenant:
            </span>
        </div>

        <select
            id="header-tenant-select"
            class="form-select"
            style="padding: 0.35rem 0.75rem; font-size: 0.8125rem; font-weight: 600; background: var(--zinc-50); border: 1px solid var(--zinc-200); color: var(--zinc-950); border-radius: var(--radius-md); cursor: pointer;"
            onchange="handleHeaderTenantChange(this.value)"
        >
            <option value="tenant-acme" selected>Acme Enterprise Tech</option>
            <option value="tenant-apex">Apex Global Logistics</option>
            <option value="tenant-skyline">Skyline Cloud Systems</option>
        </select>

        <!-- Refresh Button -->
        <button
            type="button"
            onclick="showToast('Data refreshed successfully');"
            class="btn btn-ghost btn-sm"
            title="Reload Data"
            style="display: flex; align-items: center; gap: 0.35rem; color: var(--zinc-600); padding: 0.35rem 0.65rem; border-radius: var(--radius-md); border: 1px solid transparent; background: transparent; cursor: pointer; transition: background 0.15s ease;"
            onmouseenter="this.style.background='var(--zinc-100)'; this.style.color='var(--zinc-950)';"
            onmouseleave="this.style.background='transparent'; this.style.color='var(--zinc-600)';"
        >
            <i data-lucide="refresh-cw" style="width: 13px; height: 13px;"></i>
            <span style="font-size: 0.8125rem; font-weight: 500;">Refresh</span>
        </button>

        @if(request()->is('admin*'))
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-left: 0.25rem;">
                <span style="color: var(--zinc-300);">•</span>
                <span class="badge badge-purple" style="font-size: 0.725rem; font-weight: 600;">
                    ⚙️ Admin &amp; Schema Console
                </span>
            </div>
        @endif
    </div>

    <!-- Center Region: Global Enterprise Search Bar (Hidden when in Admin Console mode) -->
    @if(!request()->is('admin*'))
        <div style="flex: 1; display: flex; justify-content: center; padding: 0 1rem;">
            <div class="header-search-bar" style="position: relative;">
                <i data-lucide="search" style="width: 15px; height: 15px; color: var(--zinc-400);"></i>
                <input id="global-search-input" type="text" placeholder="Search across all modules &amp; records... (Live Filter)" autocomplete="off">
                <span style="font-size: 0.65rem; font-weight: 600; background: var(--zinc-200); color: var(--zinc-600); padding: 0.1rem 0.35rem; border-radius: 4px; border: 1px solid var(--zinc-300);">
                    Ctrl+K
                </span>
            </div>
        </div>
    @else
        <div style="flex: 1;"></div>
    @endif

    <!-- Right side: Ambient Themes, Notification bell, User info -->
    <div style="display: flex; align-items: center; gap: 0.85rem;">
        <!-- Ambient Theme & Glow Customization Trigger -->
        <button
            type="button"
            onclick="openThemeModal()"
            class="btn btn-secondary btn-sm"
            style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.35rem 0.75rem; font-size: 0.75rem; font-weight: 600; border-radius: var(--radius-full, 9999px); border: 1px solid var(--zinc-200); background: rgba(255, 255, 255, 0.85); color: var(--zinc-700); cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.04); transition: all 0.15s ease;"
            title="Ambient Theme &amp; Glow Customization Studio"
            onmouseenter="this.style.background='#fef3c7'; this.style.borderColor='#fde68a'; this.style.color='#b45309';"
            onmouseleave="this.style.background='rgba(255, 255, 255, 0.85)'; this.style.borderColor='var(--zinc-200)'; this.style.color='var(--zinc-700)';"
        >
            <i data-lucide="sparkles" style="width: 14px; height: 14px; color: #f59e0b;"></i>
            <span>Ambient Themes</span>
        </button>

        <!-- Notification Bell & Approval Alert Hub -->
        <div style="position: relative;" id="header-notif-container">
            <button
                id="notif-dropdown-btn"
                type="button"
                class="btn btn-ghost btn-sm"
                style="position: relative; padding: 0.4rem; border-radius: var(--radius-full, 9999px); color: var(--zinc-950); background: transparent; cursor: pointer; border: none; display: flex; align-items: center; justify-content: center;"
                title="Approval Alerts &amp; Notifications"
                onclick="toggleNotifDropdown(event)"
            >
                <i data-lucide="bell" style="width: 16px; height: 16px;"></i>
                <span id="notif-badge-pill" style="position: absolute; top: -2px; right: -2px; background: #ef4444; color: #ffffff; font-size: 0.625rem; font-weight: 700; min-width: 16px; height: 16px; border-radius: 8px; display: flex; align-items: center; justify-content: center; padding: 0 3px; border: 2px solid #ffffff;">
                    3
                </span>
            </button>

            <!-- Floating Notification Dropdown -->
            <div id="notif-dropdown-menu" style="display: none; position: absolute; right: 0; top: calc(100% + 8px); width: 360px; background: #ffffff; border-radius: var(--radius-lg); box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1); border: 1px solid var(--zinc-200); z-index: 100; overflow: hidden;">
                <div style="padding: 0.75rem 1rem; border-bottom: 1px solid var(--zinc-200); display: flex; justify-content: space-between; align-items: center; background: var(--zinc-50);">
                    <div style="font-weight: 600; font-size: 0.8125rem; color: var(--zinc-900);">
                        Notifications &amp; Approval Alerts
                    </div>
                    <button
                        type="button"
                        onclick="markAllNotificationsRead()"
                        style="background: none; border: none; color: var(--zinc-500); font-size: 0.7rem; cursor: pointer; display: flex; align-items: center; gap: 0.25rem;"
                    >
                        <i data-lucide="check-check" style="width: 12px; height: 12px;"></i>
                        <span>Mark all read</span>
                    </button>
                </div>

                <div style="max-height: 340px; overflow-y: auto;" id="notif-items-list">
                    <!-- Notification 1 -->
                    <div class="notif-item" onclick="markSingleNotifRead(this)" style="padding: 0.75rem 1rem; border-bottom: 1px solid var(--zinc-100); background: #f0f9ff; cursor: pointer; transition: background 0.15s;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.2rem;">
                            <div style="display: flex; align-items: center; gap: 0.35rem;">
                                <span class="badge badge-amber" style="font-size: 0.625rem; padding: 0.1rem 0.35rem; display: inline-flex; align-items: center; gap: 0.2rem;">
                                    <i data-lucide="mail" style="width: 9px; height: 9px;"></i>
                                    <span>Email</span>
                                </span>
                                <span style="font-weight: 600; font-size: 0.75rem; color: var(--zinc-900);">
                                    Purchase Contract Approval Required
                                </span>
                            </div>
                            <span class="unread-dot" style="width: 6px; height: 6px; border-radius: 50%; background: #2563eb; flex-shrink: 0; margin-top: 4px;"></span>
                        </div>
                        <div style="font-size: 0.725rem; color: var(--zinc-600); line-height: 1.4;">
                            PO-88291 for Apex Logistics requires executive sign-off before vendor dispatch.
                        </div>
                        <div style="font-size: 0.65rem; color: var(--zinc-400); margin-top: 0.3rem;">
                            10:45 AM
                        </div>
                    </div>

                    <!-- Notification 2 -->
                    <div class="notif-item" onclick="markSingleNotifRead(this)" style="padding: 0.75rem 1rem; border-bottom: 1px solid var(--zinc-100); background: #f0f9ff; cursor: pointer; transition: background 0.15s;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.2rem;">
                            <div style="display: flex; align-items: center; gap: 0.35rem;">
                                <span class="badge badge-blue" style="font-size: 0.625rem; padding: 0.1rem 0.35rem; display: inline-flex; align-items: center; gap: 0.2rem;">
                                    <i data-lucide="bell" style="width: 9px; height: 9px;"></i>
                                    <span>Alert</span>
                                </span>
                                <span style="font-weight: 600; font-size: 0.75rem; color: var(--zinc-900);">
                                    High Priority Deal Stage Updated
                                </span>
                            </div>
                            <span class="unread-dot" style="width: 6px; height: 6px; border-radius: 50%; background: #2563eb; flex-shrink: 0; margin-top: 4px;"></span>
                        </div>
                        <div style="font-size: 0.725rem; color: var(--zinc-600); line-height: 1.4;">
                            Skyline Cloud Systems advanced to Closed Won ($125,000).
                        </div>
                        <div style="font-size: 0.65rem; color: var(--zinc-400); margin-top: 0.3rem;">
                            09:15 AM
                        </div>
                    </div>

                    <!-- Notification 3 -->
                    <div class="notif-item" onclick="markSingleNotifRead(this)" style="padding: 0.75rem 1rem; border-bottom: 1px solid var(--zinc-100); background: #ffffff; cursor: pointer; transition: background 0.15s;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.2rem;">
                            <div style="display: flex; align-items: center; gap: 0.35rem;">
                                <span class="badge badge-blue" style="font-size: 0.625rem; padding: 0.1rem 0.35rem; display: inline-flex; align-items: center; gap: 0.2rem;">
                                    <i data-lucide="bell" style="width: 9px; height: 9px;"></i>
                                    <span>Alert</span>
                                </span>
                                <span style="font-weight: 600; font-size: 0.75rem; color: var(--zinc-900);">
                                    Wire Transfer Reconciled
                                </span>
                            </div>
                        </div>
                        <div style="font-size: 0.725rem; color: var(--zinc-600); line-height: 1.4;">
                            UTR4491029381 confirmed by Treasury ($38,000) from Skyline Cloud Systems.
                        </div>
                        <div style="font-size: 0.65rem; color: var(--zinc-400); margin-top: 0.3rem;">
                            Yesterday, 4:20 PM
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Interactive User Profile Trigger & Menu -->
        <div style="position: relative;" id="user-profile-menu-container">
            <button
                type="button"
                id="user-profile-trigger"
                onclick="toggleUserDropdown(event)"
                style="display: flex; align-items: center; gap: 0.55rem; padding: 0.25rem 0.45rem; border-radius: var(--radius-lg); background: transparent; border: 1px solid transparent; cursor: pointer; transition: background 0.15s;"
                onmouseenter="this.style.background='var(--zinc-100)'"
                onmouseleave="if(document.getElementById('user-profile-menu')?.style.display !== 'block') this.style.background='transparent'"
                title="Account Settings &amp; Profile"
            >
                <div style="width: 30px; height: 30px; border-radius: 50%; background: var(--zinc-900); color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700; letter-spacing: -0.02em; box-shadow: 0 1px 2px rgba(0,0,0,0.1);">
                    AD
                </div>

                <div style="display: flex; flex-direction: column; text-align: left;">
                    <div style="display: flex; align-items: center; gap: 0.35rem;">
                        <span style="font-size: 0.8125rem; font-weight: 600; color: var(--zinc-950); line-height: 1.2;">
                            Admin User
                        </span>
                    </div>
                    <span style="font-size: 0.6875rem; color: var(--zinc-500); line-height: 1.2;">
                        Tenant Default
                    </span>
                </div>

                <i data-lucide="chevron-down" style="width: 12px; height: 12px; color: var(--zinc-400);"></i>
            </button>

            <!-- User Account Dropdown -->
            <div id="user-profile-menu" style="display: none; position: absolute; right: 0; top: calc(100% + 6px); width: 240px; background: #ffffff; border-radius: var(--radius-lg); box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.08); border: 1px solid var(--zinc-200); z-index: 100; padding: 0.4rem; overflow: hidden;">
                <div style="padding: 0.65rem 0.75rem; border-bottom: 1px solid var(--zinc-100); margin-bottom: 0.35rem;">
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.5rem;">
                        <div style="font-weight: 600; font-size: 0.8125rem; color: var(--zinc-900);">
                            Admin User
                        </div>
                    </div>
                    <div style="font-size: 0.7rem; color: var(--zinc-500); overflow: hidden; text-overflow: ellipsis;">
                        admin@acme.com
                    </div>
                    <div style="margin-top: 0.35rem;">
                        <span class="badge badge-purple" style="font-size: 0.625rem;">
                            System Administrator
                        </span>
                    </div>
                </div>

                <!-- 1. My Profile & Settings -->
                <button
                    type="button"
                    onclick="openUserProfileModal(); closeUserDropdown();"
                    style="width: 100%; display: flex; align-items: center; gap: 0.5rem; padding: 0.55rem 0.75rem; border: none; background: transparent; cursor: pointer; border-radius: var(--radius-md); font-size: 0.8rem; color: var(--zinc-800); font-weight: 500; text-align: left;"
                    onmouseenter="this.style.background='var(--zinc-100)'"
                    onmouseleave="this.style.background='transparent'"
                >
                    <i data-lucide="user" style="width: 14px; height: 14px; color: var(--zinc-600);"></i>
                    <span>My Profile &amp; Settings</span>
                </button>

                <!-- 2. Setting & Schema (Admin Console) -->
                <a
                    href="{{ url('/settings') }}"
                    style="width: 100%; display: flex; align-items: center; gap: 0.5rem; padding: 0.55rem 0.75rem; border: none; background: transparent; cursor: pointer; border-radius: var(--radius-md); font-size: 0.8rem; color: var(--zinc-800); font-weight: 500; text-align: left; text-decoration: none;"
                    onmouseenter="this.style.background='var(--zinc-100)'"
                    onmouseleave="this.style.background='transparent'"
                >
                    <i data-lucide="sliders" style="width: 14px; height: 14px; color: #4f46e5;"></i>
                    <div style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
                        <span>Setting &amp; Schema</span>
                        <span class="badge badge-purple" style="font-size: 0.625rem; padding: 0.05rem 0.35rem;">Admin</span>
                    </div>
                </a>

                <!-- 3. Ambient Themes & Glow -->
                <button
                    type="button"
                    onclick="openThemeModal(); closeUserDropdown();"
                    style="width: 100%; display: flex; align-items: center; gap: 0.5rem; padding: 0.55rem 0.75rem; border: none; background: transparent; cursor: pointer; border-radius: var(--radius-md); font-size: 0.8rem; color: var(--zinc-800); font-weight: 500; text-align: left;"
                    onmouseenter="this.style.background='var(--zinc-100)'"
                    onmouseleave="this.style.background='transparent'"
                >
                    <i data-lucide="palette" style="width: 14px; height: 14px; color: #f59e0b;"></i>
                    <div style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
                        <span>Ambient Themes &amp; Glow</span>
                    </div>
                </button>
            </div>
        </div>
    </div>
</header>

<script>
    function toggleUserDropdown(e) {
        if (e) e.stopPropagation();
        var menu = document.getElementById('user-profile-menu');
        if (menu) {
            menu.style.display = (menu.style.display === 'none' || menu.style.display === '') ? 'block' : 'none';
        }
    }

    function closeUserDropdown() {
        var menu = document.getElementById('user-profile-menu');
        if (menu) menu.style.display = 'none';
    }

    function toggleNotifDropdown(e) {
        if (e) e.stopPropagation();
        var menu = document.getElementById('notif-dropdown-menu');
        if (menu) {
            menu.style.display = (menu.style.display === 'none' || menu.style.display === '') ? 'block' : 'none';
        }
    }

    function markAllNotificationsRead() {
        document.querySelectorAll('#notif-items-list .notif-item').forEach(function(item) {
            item.style.background = '#ffffff';
        });
        document.querySelectorAll('#notif-items-list .unread-dot').forEach(function(dot) {
            dot.style.display = 'none';
        });
        var badge = document.getElementById('notif-badge-pill');
        if (badge) badge.style.display = 'none';
        showToast('All notifications marked as read');
    }

    function markSingleNotifRead(item) {
        item.style.background = '#ffffff';
        var dot = item.querySelector('.unread-dot');
        if (dot) dot.style.display = 'none';
    }

    document.addEventListener('click', function(e) {
        var userContainer = document.getElementById('user-profile-menu-container');
        var userMenu = document.getElementById('user-profile-menu');
        if (userContainer && userMenu && !userContainer.contains(e.target)) {
            userMenu.style.display = 'none';
        }

        var notifContainer = document.getElementById('header-notif-container');
        var notifMenu = document.getElementById('notif-dropdown-menu');
        if (notifContainer && notifMenu && !notifContainer.contains(e.target)) {
            notifMenu.style.display = 'none';
        }
    });
</script>
