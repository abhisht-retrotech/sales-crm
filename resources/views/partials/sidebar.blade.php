@php
    $isAdminMode = request()->is('settings*') || request()->is('admin*');
    $currentTenant = app()->bound('currentTenant') ? app('currentTenant') : \App\Models\Tenant::on('mysql')->first();
    $unreadEmailsCount = 3;
@endphp

<!-- Fixed Sidebar: slides out horizontally on hover with fluid text entry (1:1 React Parity) -->
<aside
    id="dealsync-sidebar"
    class="fixed-sidebar-container {{ $isAdminMode ? 'is-admin-mode' : '' }}"
    aria-label="DealSync Main Navigation"
>
    <!-- 1. Brand Header Row (58px height) -->
    <div style="height: 58px; padding: 0 10px; border-bottom: 1px solid var(--border-card, #e2e8f0); display: flex; align-items: center; background: var(--sidebar-bg, #ffffff); flex-shrink: 0;" class="dark:border-zinc-800 dark:bg-zinc-900">
        <!-- Stationary Brand / Logo Icon Slot -->
        <div
            class="sidebar-icon-slot cursor-pointer"
            title="{{ $isAdminMode ? 'DealSync Admin Console' : 'DealSync CRM' }} (Click to pin/unpin)"
            onclick="toggleSidebarPin()"
        >
            @if($currentTenant && !empty($currentTenant->logo_url))
                <img
                    src="{{ $currentTenant->logo_url }}"
                    alt="{{ $currentTenant->name }}"
                    style="width: 28px; height: 28px; border-radius: 7px; object-fit: contain; border: 1px solid var(--zinc-200, #e2e8f0); padding: 2px; box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05); background: #ffffff;"
                />
            @else
                <div style="width: 28px; height: 28px; border-radius: 7px; display: flex; align-items: center; justify-content: center; color: white; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.15);" class="{{ $isAdminMode ? 'bg-gradient-to-b from-indigo-500 to-indigo-700' : 'bg-gradient-to-b from-zinc-800 to-zinc-950 dark:from-indigo-600 dark:to-indigo-800' }}">
                    @if($isAdminMode)
                        <i data-lucide="sliders" class="w-3.5 h-3.5 stroke-[2.2]"></i>
                    @else
                        <i data-lucide="layers" class="w-3.5 h-3.5 stroke-[2.2]"></i>
                    @endif
                </div>
            @endif
        </div>

        <!-- Sliding Brand Text & Pin Action -->
        <div class="sidebar-slide-text" style="padding-right: 0.4rem;">
            <div style="display: flex; align-items: center; gap: 0.45rem; overflow: hidden;">
                <span
                    style="font-size: 0.925rem; font-weight: 700; letter-spacing: -0.03em; color: var(--zinc-950, #09090b); white-space: nowrap; text-overflow: ellipsis; overflow: hidden;"
                    class="dark:text-zinc-100"
                    title="{{ $currentTenant->name ?? 'DealSync' }}"
                >
                    {{ $currentTenant ? explode(' ', $currentTenant->name)[0] : 'DealSync' }}
                </span>
                <span style="font-size: 0.65rem; font-weight: 600; padding: 0.1rem 0.35rem; border-radius: 4px; flex-shrink: 0;" class="{{ $isAdminMode ? 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700' }}">
                    {{ $isAdminMode ? 'ADMIN' : 'CRM' }}
                </span>
            </div>

            <!-- Pin / Unpin Action Button -->
            <button
                id="sidebar-pin-btn"
                type="button"
                onclick="toggleSidebarPin()"
                title="Pin sidebar open (Ctrl+B)"
                style="border-radius: 6px; padding: 0.3rem; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; transition: all 0.15s ease;"
                class="border border-transparent text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:text-zinc-800 dark:hover:text-zinc-200 shrink-0"
            >
                <i id="sidebar-pin-icon" data-lucide="pin" class="w-3.5 h-3.5"></i>
            </button>
        </div>
    </div>

    @if($isAdminMode)
        <!-- ================= ADMIN CONSOLE TOP NAVIGATION & SEARCH ================= -->
        <!-- Return to CRM Button -->
        <div style="padding: 0.6rem 10px 0.2rem 10px;" class="shrink-0">
            <a
                href="{{ url('/') }}"
                title="Return to CRM"
                class="sidebar-nav-btn"
                style="background: #f8fafc; border: 1px solid var(--zinc-200, #e2e8f0); color: var(--zinc-800, #1e293b); font-weight: 600;"
            >
                <div class="sidebar-icon-slot">
                    <i data-lucide="arrow-left" class="w-4 h-4 stroke-[2.4] text-zinc-700 dark:text-zinc-300"></i>
                </div>
                <div class="sidebar-slide-text">
                    <span class="truncate">&larr; Back to CRM</span>
                </div>
            </a>
        </div>

        <!-- Admin Quick Search & Category Controls -->
        <div class="admin-search-container shrink-0">
            <div style="display: flex; align-items: center; gap: 0.35rem;">
                <div class="admin-search-input-wrap" style="flex: 1;">
                    <i data-lucide="search" class="w-3.5 h-3.5 text-zinc-400 shrink-0"></i>
                    <input
                        id="admin-search-input"
                        type="text"
                        placeholder="Search 25 settings... (Ctrl+/)"
                        class="admin-search-input"
                        autocomplete="off"
                    />
                    <button
                        id="admin-search-clear-btn"
                        type="button"
                        style="display: none; border: none; background: transparent; cursor: pointer; padding: 2px; color: var(--zinc-400); align-items: center;"
                        title="Clear search"
                        onclick="clearAdminSearch()"
                    >
                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                    </button>
                </div>

                <!-- Toggle All Categories Button -->
                <button
                    id="admin-toggle-all-btn"
                    type="button"
                    onclick="toggleAllAdminGroups()"
                    title="Collapse all categories"
                    style="width: 32px; height: 32px; border-radius: var(--radius-md, 8px); border: 1px solid var(--zinc-200, #e2e8f0); background: var(--zinc-50, #f8fafc); display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--zinc-600, #475569); flex-shrink: 0; transition: all 0.15s ease;"
                    class="dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400 hover:bg-white dark:hover:bg-zinc-700 hover:border-zinc-300"
                >
                    <i data-lucide="chevrons-up-down" class="w-3.5 h-3.5"></i>
                </button>
            </div>

            <!-- Live Match Counter Feedback -->
            <div id="admin-search-feedback" style="display: none; font-size: 0.68rem; color: var(--zinc-500); margin-top: 0.35rem; padding-left: 0.2rem; justify-content: space-between; align-items: center;">
                <span id="admin-match-count-text">25 matching settings</span>
                <button
                    type="button"
                    onclick="clearAdminSearch()"
                    style="background: none; border: none; color: var(--color-primary, #4f46e5); cursor: pointer; font-size: 0.68rem; padding: 0;"
                >
                    Reset
                </button>
            </div>
        </div>

        <!-- ================= ADMIN CONSOLE CATEGORIZED ACCORDION NAVIGATION ================= -->
        <nav id="admin-nav-container" style="flex: 1; padding: 0.45rem 10px 1rem 10px; overflow-y: auto; overflow-x: hidden;">
            @php
                $adminGroups = [
                    [
                        'id' => 'org',
                        'title' => 'Organization & Identity',
                        'icon' => 'building-2',
                        'items' => [
                            [
                                'id' => 'company_profile',
                                'label' => 'Company Profile & Localization',
                                'icon' => 'building-2',
                                'url' => route('admin.company_profile'),
                                'active' => request()->is('admin/company-profile*'),
                                'keywords' => 'organization branding logo currency theme timezone hours smtp email gateway'
                            ],
                            [
                                'id' => 'security_governance',
                                'label' => 'Security Policies & Governance',
                                'icon' => 'shield-check',
                                'url' => route('admin.security_governance'),
                                'active' => request()->is('admin/security-governance*'),
                                'keywords' => 'security password 2fa mfa session timeout lockout seats governance'
                            ],
                            [
                                'id' => 'tenant_studio',
                                'label' => 'Tenant Studio',
                                'icon' => 'building-2',
                                'badge' => 'Master',
                                'url' => route('admin.tenant_studio'),
                                'active' => request()->is('admin/tenant-studio*'),
                                'keywords' => 'tenants multi-tenant instances provisioning databases isolation'
                            ]
                        ]
                    ],
                    [
                        'id' => 'access',
                        'title' => 'Users & Access Control',
                        'icon' => 'users',
                        'items' => [
                            [
                                'id' => 'users_directory',
                                'label' => 'Users Directory',
                                'icon' => 'users',
                                'url' => route('admin.users_directory'),
                                'active' => request()->is('admin/users*'),
                                'keywords' => 'employees invite seats deactivate offboarding impersonate directory'
                            ],
                            [
                                'id' => 'roles_profiles',
                                'label' => 'Roles & Profiles',
                                'icon' => 'layers',
                                'badge' => 'Rules',
                                'url' => route('admin.roles_permissions'),
                                'active' => request()->is('admin/roles-permissions*'),
                                'keywords' => 'role hierarchy security profiles bulk email daily limit quota permissions'
                            ],
                            [
                                'id' => 'record_rights',
                                'label' => 'Record Rights (CRUD)',
                                'icon' => 'shield',
                                'url' => route('admin.record_rights'),
                                'active' => request()->is('admin/record-rights*'),
                                'keywords' => 'permissions crud create read update delete object matrix'
                            ],
                            [
                                'id' => 'field_rights',
                                'label' => 'Field-Level Rights',
                                'icon' => 'lock',
                                'url' => route('admin.field_rights'),
                                'active' => request()->is('admin/field-rights*'),
                                'keywords' => 'field security read only hidden fields fls'
                            ],
                            [
                                'id' => 'sharing_rules',
                                'label' => 'Sharing Rules Studio',
                                'icon' => 'share-2',
                                'badge' => 'New',
                                'url' => route('admin.sharing_rules'),
                                'active' => request()->is('admin/sharing-rules*'),
                                'keywords' => 'record sharing visibility owd criteria sharing public read'
                            ]
                        ]
                    ],
                    [
                        'id' => 'schema',
                        'title' => 'Schema & Customization',
                        'icon' => 'sliders',
                        'items' => [
                            [
                                'id' => 'object_fields',
                                'label' => 'Object & Field Manager',
                                'icon' => 'sliders',
                                'url' => route('admin.object_fields'),
                                'active' => request()->is('admin/object-fields*'),
                                'keywords' => 'custom fields fields picklists standard objects metadata'
                            ],
                            [
                                'id' => 'custom_objects',
                                'label' => 'Custom Objects Studio',
                                'icon' => 'boxes',
                                'badge' => 'Studio',
                                'url' => route('admin.custom_objects'),
                                'active' => request()->is('admin/custom-objects*'),
                                'keywords' => 'custom tables entities records schema'
                            ],
                            [
                                'id' => 'page_layouts',
                                'label' => 'Page Layouts Designer',
                                'icon' => 'layout-template',
                                'badge' => 'New',
                                'url' => route('admin.page_layouts'),
                                'active' => request()->is('admin/page-layouts*'),
                                'keywords' => 'layouts sections detail view drag drop form'
                            ],
                            [
                                'id' => 'custom_buttons',
                                'label' => 'Custom Buttons Studio',
                                'icon' => 'mouse-pointer-click',
                                'url' => url('/settings?tab=custom_buttons'),
                                'active' => request()->fullUrlIs('*tab=custom_buttons*'),
                                'keywords' => 'actions links webhooks button custom button'
                            ],
                            [
                                'id' => 'dashboard_studio',
                                'label' => 'Dashboard Studio',
                                'icon' => 'layout-dashboard',
                                'badge' => 'Studio',
                                'url' => route('admin.dashboard_studio'),
                                'active' => request()->is('admin/dashboard-studio*'),
                                'keywords' => 'charts metrics kpi widgets analytics dashboard designer'
                            ]
                        ]
                    ],
                    [
                        'id' => 'automation',
                        'title' => 'Automation & Workflows',
                        'icon' => 'workflow',
                        'items' => [
                            [
                                'id' => 'process_studio',
                                'label' => 'ProcessStudio',
                                'icon' => 'workflow',
                                'badge' => 'New',
                                'url' => route('admin.process_studio'),
                                'active' => request()->is('admin/process-studio*'),
                                'keywords' => 'workflows automation triggers actions conditions pipeline'
                            ],
                            [
                                'id' => 'approvals',
                                'label' => 'Approval Workflows',
                                'icon' => 'git-pull-request',
                                'badge' => 'Rules',
                                'url' => route('admin.approval_rules'),
                                'active' => request()->is('admin/approval-rules*'),
                                'keywords' => 'approval process discount approval stages signoff'
                            ],
                            [
                                'id' => 'notifications',
                                'label' => 'Pop-up Notifications',
                                'icon' => 'bell',
                                'badge' => 'Studio',
                                'url' => route('admin.notification_rules'),
                                'active' => request()->is('admin/notification-rules*'),
                                'keywords' => 'broadcasts banners toast alerts announcements'
                            ]
                        ]
                    ],
                    [
                        'id' => 'comms',
                        'title' => 'Communication & Templates',
                        'icon' => 'mail',
                        'items' => [
                            [
                                'id' => 'email_templates',
                                'label' => 'Email Templates Studio',
                                'icon' => 'mail',
                                'url' => url('/settings?tab=email_templates'),
                                'active' => request()->fullUrlIs('*tab=email_templates*'),
                                'keywords' => 'email templates merge tags html email rich text messaging'
                            ],
                            [
                                'id' => 'document_templates',
                                'label' => 'Document & Billing Templates',
                                'icon' => 'file-spreadsheet',
                                'url' => url('/settings?tab=document_templates'),
                                'active' => request()->fullUrlIs('*tab=document_templates*'),
                                'keywords' => 'pdf invoices quotes receipts packing slip billing'
                            ]
                        ]
                    ],
                    [
                        'id' => 'data',
                        'title' => 'Data & Storage',
                        'icon' => 'database',
                        'items' => [
                            [
                                'id' => 'data_studio',
                                'label' => 'Data Management (CSV)',
                                'icon' => 'database',
                                'badge' => 'CSV',
                                'url' => route('admin.data_studio'),
                                'active' => request()->is('admin/data-studio*'),
                                'keywords' => 'import export csv bulk upload data loader'
                            ],
                            [
                                'id' => 'crm_migration',
                                'label' => 'CRM Migration Engine',
                                'icon' => 'rocket',
                                'url' => url('/settings?tab=crm_migration'),
                                'active' => request()->fullUrlIs('*tab=crm_migration*'),
                                'keywords' => 'salesforce hubspot pipedrive migration wizard ingestion'
                            ],
                            [
                                'id' => 'duplicate_rules',
                                'label' => 'Duplicate Detection Rules',
                                'icon' => 'git-merge',
                                'badge' => 'Rules',
                                'url' => route('admin.duplicate_rules'),
                                'active' => request()->is('admin/duplicate-rules*'),
                                'keywords' => 'deduplication merge records matching rules clean'
                            ],
                            [
                                'id' => 'data_backup',
                                'label' => 'Backup & Storage Quotas',
                                'icon' => 'hard-drive',
                                'url' => url('/settings?tab=data_backup'),
                                'active' => request()->fullUrlIs('*tab=data_backup*'),
                                'keywords' => 'database snapshot export zip storage meter s3 backup'
                            ]
                        ]
                    ],
                    [
                        'id' => 'system',
                        'title' => 'Integrations & Governance',
                        'icon' => 'webhook',
                        'items' => [
                            [
                                'id' => 'api_webhooks',
                                'label' => 'API Keys & Webhooks',
                                'icon' => 'webhook',
                                'url' => url('/settings?tab=api_webhooks'),
                                'active' => request()->fullUrlIs('*tab=api_webhooks*'),
                                'keywords' => 'rest api token secret endpoints outbound webhooks integration'
                            ],
                            [
                                'id' => 'global_search',
                                'label' => 'Global Search Settings',
                                'icon' => 'search',
                                'url' => url('/settings?tab=global_search'),
                                'active' => request()->fullUrlIs('*tab=global_search*'),
                                'keywords' => 'indexing search weights fuzzy search searchable objects'
                            ],
                            [
                                'id' => 'audit',
                                'label' => 'Audit Trail & Logs',
                                'icon' => 'shield-check',
                                'badge' => 'CSV',
                                'url' => route('admin.audit_logs'),
                                'active' => request()->is('admin/audit-logs*'),
                                'keywords' => 'audit log login history activity tracking csv export compliance'
                            ]
                        ]
                    ]
                ];
            @endphp

            @foreach($adminGroups as $gIndex => $group)
                @php
                    $groupHasActive = collect($group['items'])->contains('active', true);
                @endphp
                @if($gIndex > 0)
                    <div class="sidebar-category-divider"></div>
                @endif
                <div class="admin-group-section" data-group-id="{{ $group['id'] }}" style="margin-bottom: 0.4rem;">
                    <!-- Accordion Category Header (Expanded Mode) -->
                    <button
                        type="button"
                        class="sidebar-category-accordion {{ $groupHasActive ? 'is-active-parent' : '' }}"
                        data-toggle-group="{{ $group['id'] }}"
                        onclick="toggleAdminGroup('{{ $group['id'] }}')"
                        title="Toggle {{ $group['title'] }}"
                    >
                        <div style="display: flex; align-items: center; gap: 0.45rem; overflow: hidden;">
                            <i data-lucide="{{ $group['icon'] }}" class="w-3.5 h-3.5 stroke-[2.2] shrink-0 opacity-85"></i>
                            <span class="truncate">{{ $group['title'] }}</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.35rem; flex-shrink: 0;">
                            <span style="font-size: 0.625rem; font-weight: 700; padding: 0.05rem 0.35rem; border-radius: 10px;" class="{{ $groupHasActive ? 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700' }}">
                                {{ count($group['items']) }}
                            </span>
                            <i id="chevron-{{ $group['id'] }}" data-lucide="chevron-down" class="w-3.5 h-3.5 stroke-[2.4] opacity-60"></i>
                        </div>
                    </button>

                    <!-- Group Navigation Items -->
                    <div id="items-{{ $group['id'] }}" class="admin-group-items" style="display: flex; flex-direction: column; gap: 2px; padding-left: 2px;">
                        @foreach($group['items'] as $item)
                            <a
                                href="{{ $item['url'] }}"
                                title="{{ $item['label'] }}{{ isset($item['badge']) ? ' (' . $item['badge'] . ')' : '' }}"
                                class="sidebar-nav-btn admin-nav-item {{ $item['active'] ? 'is-active' : '' }}"
                                data-label="{{ strtolower($item['label']) }}"
                                data-keywords="{{ $item['keywords'] ?? '' }}"
                                data-group="{{ strtolower($group['title']) }}"
                            >
                                <!-- Stationary Icon Slot -->
                                <div class="sidebar-icon-slot">
                                    <i
                                        data-lucide="{{ $item['icon'] }}"
                                        class="w-[17px] h-[17px] {{ $item['active'] ? 'text-indigo-600 dark:text-indigo-400 stroke-[2.2]' : 'text-zinc-500 dark:text-zinc-400 stroke-[1.8]' }}"
                                    ></i>
                                    @if(isset($item['badge']))
                                        <span class="sidebar-badge-dot" style="position: absolute; top: 8px; right: 12px; width: 6px; height: 6px; border-radius: 50%; background: var(--color-primary, #4f46e5);"></span>
                                    @endif
                                </div>

                                <!-- Smooth Sliding Text & Badges -->
                                <div class="sidebar-slide-text">
                                    <span class="truncate {{ $item['active'] ? 'font-semibold' : 'font-medium' }}">
                                        {{ $item['label'] }}
                                    </span>
                                    <div style="display: flex; align-items: center; gap: 0.35rem; flex-shrink: 0;">
                                        @if(isset($item['badge']))
                                            <span style="font-size: 0.625rem; font-weight: 700; padding: 0.05rem 0.35rem; border-radius: 4px;" class="{{ $item['active'] ? 'text-indigo-700 dark:text-indigo-400 bg-white dark:bg-zinc-800 border border-indigo-200 dark:border-indigo-800' : 'text-zinc-600 dark:text-zinc-400 bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700' }}">
                                                {{ $item['badge'] }}
                                            </span>
                                        @endif
                                        @if($item['active'])
                                            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400 stroke-[2.4]"></i>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <!-- Empty Search State -->
            <div id="admin-search-empty" style="display: none; padding: 2.5rem 1rem; text-align: center; color: var(--zinc-500);">
                <i data-lucide="search" class="w-6 h-6 stroke-[1.5] mx-auto mb-2 opacity-40"></i>
                <div style="font-size: 0.8125rem; font-weight: 600; color: var(--zinc-800, #1e293b);" class="dark:text-zinc-200">
                    No settings match search
                </div>
                <div style="font-size: 0.72rem; margin-top: 0.25rem; color: var(--zinc-500);">
                    Try searching by feature name, keyword, or synonym
                </div>
                <button
                    type="button"
                    onclick="clearAdminSearch()"
                    style="margin-top: 0.85rem; background: var(--zinc-100, #f1f5f9); border: 1px solid var(--zinc-300, #cbd5e1); border-radius: 6px; padding: 0.35rem 0.85rem; font-size: 0.75rem; font-weight: 600; cursor: pointer; color: var(--zinc-800, #1e293b);"
                    class="dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-200"
                >
                    Clear Search
                </button>
            </div>
        </nav>
    @else
        <!-- ================= REGULAR BUSINESS CRM NAVIGATION (13 ITEMS REACT PARITY) ================= -->
        <nav style="flex: 1; padding: 0.45rem 10px 1rem 10px; overflow-y: auto; overflow-x: hidden;">
            @php
                $crmNavItems = [
                    [
                        'id' => 'emails',
                        'label' => 'Emails',
                        'icon' => 'mail',
                        'url' => route('emails.index'),
                        'active' => request()->is('emails*'),
                        'badge' => $unreadEmailsCount,
                        'badgeCount' => $unreadEmailsCount
                    ],
                    [
                        'id' => 'calendar',
                        'label' => 'Calendar',
                        'icon' => 'calendar',
                        'url' => route('calendar.index'),
                        'active' => request()->is('calendar*')
                    ],
                    [
                        'id' => 'dashboard',
                        'label' => 'Dashboard',
                        'icon' => 'layout-dashboard',
                        'url' => route('dashboard'),
                        'active' => request()->is('/') || request()->is('dashboard*')
                    ],
                    [
                        'id' => 'leads',
                        'label' => 'Leads',
                        'icon' => 'user-check',
                        'url' => route('leads.index'),
                        'active' => request()->is('leads*'),
                        'badge' => '3'
                    ],
                    [
                        'id' => 'accounts',
                        'label' => 'Accounts',
                        'icon' => 'building-2',
                        'url' => route('accounts.index'),
                        'active' => request()->is('accounts*'),
                        'badge' => '4'
                    ],
                    [
                        'id' => 'contacts',
                        'label' => 'Contacts',
                        'icon' => 'users',
                        'url' => route('contacts.index'),
                        'active' => request()->is('contacts*'),
                        'badge' => '4'
                    ],
                    [
                        'id' => 'opportunities',
                        'label' => 'Opportunities',
                        'icon' => 'briefcase',
                        'url' => route('opportunities.index'),
                        'active' => request()->is('opportunities*'),
                        'badge' => '$433k'
                    ],
                    [
                        'id' => 'tasks',
                        'label' => 'Tasks',
                        'icon' => 'check-square',
                        'url' => route('tasks.index'),
                        'active' => request()->is('tasks*'),
                        'badge' => '2 Overdue'
                    ],
                    [
                        'id' => 'purchase_deals',
                        'label' => 'Purchase Deals',
                        'icon' => 'shopping-bag',
                        'url' => route('purchase_deals.index'),
                        'active' => request()->is('purchase-deals*'),
                        'badge' => 'POs'
                    ],
                    [
                        'id' => 'shipping',
                        'label' => 'Shipping & Freight',
                        'icon' => 'truck',
                        'url' => route('shipping.index'),
                        'active' => request()->is('shipping*')
                    ],
                    [
                        'id' => 'documents',
                        'label' => 'Billing & Ledger',
                        'icon' => 'file-spreadsheet',
                        'url' => route('documents.index'),
                        'active' => request()->is('documents*'),
                        'badge' => 'Vault'
                    ],
                    [
                        'id' => 'payments',
                        'label' => 'Invoices & AR/AP',
                        'icon' => 'credit-card',
                        'url' => route('payments.index'),
                        'active' => request()->is('payments*')
                    ],
                    [
                        'id' => 'wires',
                        'label' => 'Wire Reconcile',
                        'icon' => 'landmark',
                        'url' => route('wires.index'),
                        'active' => request()->is('wires*'),
                        'badge' => '2 Pending'
                    ]
                ];
            @endphp

            @foreach($crmNavItems as $item)
                <a
                    href="{{ $item['url'] }}"
                    title="{{ $item['label'] }}"
                    class="sidebar-nav-btn {{ $item['active'] ? 'is-active' : '' }}"
                >
                    <!-- Stationary Icon Slot -->
                    <div class="sidebar-icon-slot">
                        <i
                            data-lucide="{{ $item['icon'] }}"
                            class="w-[17px] h-[17px] {{ $item['active'] ? 'text-indigo-600 dark:text-indigo-400 stroke-[2.2]' : 'text-zinc-500 dark:text-zinc-400 stroke-[1.8]' }}"
                        ></i>
                        @if(!empty($item['badgeCount']))
                            <span class="sidebar-badge-dot" style="position: absolute; top: 8px; right: 12px; width: 7px; height: 7px; border-radius: 50%; background: var(--color-primary, #4f46e5); box-shadow: 0 0 5px var(--color-primary, #4f46e5);"></span>
                        @endif
                    </div>

                    <!-- Smooth Sliding Text Container -->
                    <div class="sidebar-slide-text">
                        <span class="truncate {{ $item['active'] ? 'font-semibold' : 'font-medium' }}">
                            {{ $item['label'] }}
                        </span>
                        <div style="display: flex; align-items: center; gap: 0.35rem; flex-shrink: 0;">
                            @if(!empty($item['badge']))
                                <span
                                    style="font-size: 0.6875rem; font-weight: 700; border-radius: 9999px; padding: 0.08rem 0.45rem; line-height: 1.2;"
                                    class="{{ $item['active'] ? 'bg-indigo-600 text-white' : 'bg-zinc-800 text-white dark:bg-zinc-700' }}"
                                >
                                    {{ $item['badge'] }}
                                </span>
                            @endif
                            @if($item['active'])
                                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400 stroke-[2.2]"></i>
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach
        </nav>
    @endif

    <!-- Bottom Status Footer (48px height) -->
    <div style="height: 48px; padding: 0 10px; border-top: 1px solid var(--border-card, #e2e8f0); font-size: 0.725rem; color: var(--zinc-500, #64748b); display: flex; align-items: center; background: var(--zinc-50, #f8fafc); flex-shrink: 0;" class="dark:border-zinc-800 dark:bg-zinc-900">
        <!-- Stationary Online Dot Slot -->
        <div
            class="sidebar-icon-slot cursor-pointer"
            title="System Status: Connected (Click to pin/unpin)"
            onclick="toggleSidebarPin()"
        >
            <span class="inline-block w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
        </div>

        <!-- Sliding Status Label & Pin Helper -->
        <div class="sidebar-slide-text">
            <span class="font-medium text-zinc-700 dark:text-zinc-300 truncate">
                {{ $isAdminMode ? 'Admin Console' : 'DealSync v2.4' }}
            </span>
            <button
                type="button"
                onclick="toggleSidebarPin()"
                title="Pin sidebar (Ctrl+B)"
                style="background: transparent; border: none; cursor: pointer; padding: 0.2rem 0.4rem; display: inline-flex; align-items: center; gap: 0.3rem; border-radius: 4px; font-size: 0.675rem; font-weight: 600;"
                class="text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100 transition"
            >
                <span id="sidebar-footer-pin-text">Pin</span>
                <i id="sidebar-footer-pin-icon" data-lucide="pin" class="w-3 h-3"></i>
            </button>
        </div>
    </div>
</aside>

@if($isAdminMode)
    <!-- Client-Side Admin Accordion & Live Filter Script -->
    <script>
        (function initAdminSidebar() {
            const ADMIN_GROUPS_KEY = 'dealsync_admin_collapsed_groups';
            let collapsedGroups = {};
            try {
                collapsedGroups = JSON.parse(localStorage.getItem(ADMIN_GROUPS_KEY) || '{}');
            } catch(e) {
                collapsedGroups = {};
            }

            // Apply saved group states
            function applyGroupStates() {
                document.querySelectorAll('.admin-group-section').forEach(section => {
                    const groupId = section.getAttribute('data-group-id');
                    const items = document.getElementById('items-' + groupId);
                    const chevron = document.getElementById('chevron-' + groupId);
                    const isCollapsed = Boolean(collapsedGroups[groupId]);

                    if (items) {
                        items.style.display = isCollapsed ? 'none' : 'flex';
                    }
                    if (chevron) {
                        chevron.setAttribute('data-lucide', isCollapsed ? 'chevron-right' : 'chevron-down');
                    }
                });
                if (window.lucide) lucide.createIcons();
            }

            window.toggleAdminGroup = function(groupId) {
                collapsedGroups[groupId] = !collapsedGroups[groupId];
                try {
                    localStorage.setItem(ADMIN_GROUPS_KEY, JSON.stringify(collapsedGroups));
                } catch(e) {}
                applyGroupStates();
            };

            window.toggleAllAdminGroups = function() {
                const groupSections = document.querySelectorAll('.admin-group-section');
                const anyOpen = Array.from(groupSections).some(s => {
                    const gId = s.getAttribute('data-group-id');
                    return !collapsedGroups[gId];
                });

                groupSections.forEach(s => {
                    const gId = s.getAttribute('data-group-id');
                    collapsedGroups[gId] = anyOpen;
                });

                try {
                    localStorage.setItem(ADMIN_GROUPS_KEY, JSON.stringify(collapsedGroups));
                } catch(e) {}
                applyGroupStates();
            };

            // Live Search Filter
            const searchInput = document.getElementById('admin-search-input');
            const clearBtn = document.getElementById('admin-search-clear-btn');
            const feedback = document.getElementById('admin-search-feedback');
            const countText = document.getElementById('admin-match-count-text');
            const emptyState = document.getElementById('admin-search-empty');

            function filterAdminItems(query) {
                const q = (query || '').trim().toLowerCase();
                if (clearBtn) clearBtn.style.display = q ? 'inline-flex' : 'none';

                let totalMatches = 0;
                let matchedCategories = 0;

                document.querySelectorAll('.admin-group-section').forEach(section => {
                    const groupId = section.getAttribute('data-group-id');
                    const groupItems = section.querySelectorAll('.admin-nav-item');
                    const accordionBtn = section.querySelector('.sidebar-category-accordion');
                    const itemsWrap = document.getElementById('items-' + groupId);
                    let groupMatchCount = 0;

                    groupItems.forEach(item => {
                        const label = item.getAttribute('data-label') || '';
                        const keywords = item.getAttribute('data-keywords') || '';
                        const groupTitle = item.getAttribute('data-group') || '';
                        const matches = !q || label.includes(q) || keywords.includes(q) || groupTitle.includes(q);

                        if (matches) {
                            item.style.display = 'flex';
                            groupMatchCount++;
                            totalMatches++;
                        } else {
                            item.style.display = 'none';
                        }
                    });

                    if (!q) {
                        // Reset to standard accordion state
                        section.style.display = '';
                        if (itemsWrap) itemsWrap.style.display = collapsedGroups[groupId] ? 'none' : 'flex';
                    } else if (groupMatchCount > 0) {
                        section.style.display = '';
                        if (itemsWrap) itemsWrap.style.display = 'flex'; // Auto-expand matching groups
                        matchedCategories++;
                    } else {
                        section.style.display = 'none';
                    }
                });

                if (q) {
                    if (feedback) {
                        feedback.style.display = 'flex';
                        if (countText) {
                            countText.textContent = `${totalMatches} matching ${totalMatches === 1 ? 'setting' : 'settings'} in ${matchedCategories} categories`;
                        }
                    }
                    if (emptyState) {
                        emptyState.style.display = totalMatches === 0 ? 'block' : 'none';
                    }
                } else {
                    if (feedback) feedback.style.display = 'none';
                    if (emptyState) emptyState.style.display = 'none';
                    applyGroupStates();
                }

                if (window.lucide) lucide.createIcons();
            }

            window.clearAdminSearch = function() {
                if (searchInput) {
                    searchInput.value = '';
                    filterAdminItems('');
                    searchInput.focus();
                }
            };

            searchInput?.addEventListener('input', (e) => {
                filterAdminItems(e.target.value);
            });

            // Shortcut Ctrl+/ or Cmd+/ to focus search and expand sidebar
            document.addEventListener('keydown', (e) => {
                if ((e.ctrlKey || e.metaKey) && e.key === '/') {
                    e.preventDefault();
                    const sidebar = document.getElementById('dealsync-sidebar');
                    if (sidebar && !sidebar.classList.contains('is-expanded')) {
                        if (typeof toggleSidebarPin === 'function') {
                            toggleSidebarPin();
                        }
                    }
                    setTimeout(() => searchInput?.focus(), 100);
                }
            });

            // Initialize accordion states on load
            applyGroupStates();
        })();
    </script>
@endif
