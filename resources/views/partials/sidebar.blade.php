@php
    $isAdminMode = request()->is('settings*') || request()->is('admin*');
@endphp

<!-- Layout Spacer -->
<div id="dealsync-sidebar-spacer" class="sidebar-spacer is-pinned" aria-hidden="true"></div>

<!-- Slide-out & Pinned Sidebar -->
<aside id="dealsync-sidebar" class="fixed-sidebar-container is-pinned {{ $isAdminMode ? 'is-admin-mode' : '' }}">
    <!-- Brand Header -->
    <div class="sidebar-brand-row" style="height: 58px; padding: 0 10px; border-bottom: 1px solid var(--border-card); display: flex; align-items: center; background: var(--sidebar-bg, #ffffff); flex-shrink: 0;">
        <div class="sidebar-icon-slot">
            @if($isAdminMode)
                <div style="width: 28px; height: 28px; border-radius: 7px; background: linear-gradient(180deg, var(--color-primary-hover, #6366f1) 0%, var(--color-primary, #4338ca) 100%); display: flex; align-items: center; justify-content: center; color: white; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.15);">
                    <i data-lucide="sliders" style="width: 14px; height: 14px;"></i>
                </div>
            @else
                <div style="width: 28px; height: 28px; border-radius: 7px; background: linear-gradient(180deg, #27272a 0%, #09090b 100%); display: flex; align-items: center; justify-content: center; color: white; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.15);">
                    <i data-lucide="layers" style="width: 14px; height: 14px;"></i>
                </div>
            @endif
        </div>
        <div class="sidebar-slide-text" style="padding-right: 0.4rem;">
            <div style="display: flex; align-items: center; gap: 0.45rem; overflow: hidden;">
                <span style="font-size: 0.925rem; font-weight: 700; letter-spacing: -0.03em; color: var(--zinc-950); white-space: nowrap; text-overflow: ellipsis; overflow: hidden;">
                    DealSync
                </span>
                <span style="font-size: 0.65rem; font-weight: 600; background: {{ $isAdminMode ? 'var(--color-primary-subtle, #eef2ff)' : 'var(--zinc-100)' }}; color: {{ $isAdminMode ? 'var(--color-primary-text, #4338ca)' : 'var(--zinc-600)' }}; padding: 0.1rem 0.35rem; border-radius: 4px; border: 1px solid {{ $isAdminMode ? 'var(--color-primary-border, #c7d2fe)' : 'var(--zinc-200)' }}; flex-shrink: 0;">
                    {{ $isAdminMode ? 'ADMIN' : 'CRM' }}
                </span>
            </div>
            <!-- Pin / Unpin Toggle -->
            <button id="sidebar-pin-btn" type="button" style="border: none; background: transparent; padding: 0.3rem; border-radius: 6px; cursor: pointer; display: flex; align-items: center; justify-content: center; color: var(--color-primary);" title="Pin sidebar open (Ctrl+B)">
                <i data-lucide="pin" style="width: 15px; height: 15px;"></i>
            </button>
        </div>
    </div>

    @if($isAdminMode)
        <!-- ================= RETURN TO CRM BUTTON (ADMIN MODE) ================= -->
        <div style="padding: 0.6rem 10px 0.2rem 10px;">
            <a href="{{ url('/') }}" class="sidebar-nav-btn" title="Return to CRM" style="background: #f8fafc; border: 1px solid var(--zinc-200); color: var(--zinc-800); font-weight: 600; text-decoration: none; display: flex; align-items: center; gap: 0.5rem;">
                <div class="sidebar-icon-slot">
                    <i data-lucide="arrow-left" style="width: 16px; height: 16px; color: var(--zinc-700);"></i>
                </div>
                <div class="sidebar-slide-text">
                    <span>&larr; Back to CRM</span>
                </div>
            </a>
        </div>

        <!-- ================= ADMIN CONSOLE SUB-NAVIGATION (1-TO-1 REACT PARITY) ================= -->
        <nav style="flex: 1; padding: 0.65rem 10px; overflow-y: auto; overflow-x: hidden;">
            <div class="sidebar-category-header">
                Settings &amp; Schema
            </div>

            <!-- 1. Tenant Studio (Master Tenant) -->
            <a href="{{ route('admin.tenant_studio') }}" class="sidebar-nav-btn {{ request()->is('admin/tenant-studio*') ? 'is-active' : '' }}" title="Tenant Studio (Master)">
                <div class="sidebar-icon-slot">
                    <i data-lucide="building-2" style="width: 17px; height: 17px;"></i>
                </div>
                <div class="sidebar-slide-text">
                    <span style="overflow: hidden; text-overflow: ellipsis;">Tenant Studio</span>
                    <span class="badge badge-purple" style="font-size: 0.625rem; padding: 0.05rem 0.35rem;">Master</span>
                </div>
            </a>

            <!-- 2. Dashboard Studio (Canvas Designer) -->
            <a href="{{ route('admin.dashboard_studio') }}" class="sidebar-nav-btn {{ request()->is('admin/dashboard-studio*') ? 'is-active' : '' }}" title="Dashboard Studio (Designer)">
                <div class="sidebar-icon-slot">
                    <i data-lucide="layout-dashboard" style="width: 17px; height: 17px;"></i>
                </div>
                <div class="sidebar-slide-text">
                    <span style="overflow: hidden; text-overflow: ellipsis;">Dashboard Studio</span>
                    <span class="badge badge-purple" style="font-size: 0.625rem; padding: 0.05rem 0.35rem;">Studio</span>
                </div>
            </a>

            <!-- 3. Company Profile & Localization -->
            <a href="{{ route('admin.company_profile') }}" class="sidebar-nav-btn {{ request()->is('admin/company-profile*') ? 'is-active' : '' }}" title="Company Profile & Localization">
                <div class="sidebar-icon-slot">
                    <i data-lucide="building" style="width: 17px; height: 17px;"></i>
                </div>
                <div class="sidebar-slide-text">
                    <span style="overflow: hidden; text-overflow: ellipsis;">Company Profile</span>
                    <span class="badge badge-blue" style="font-size: 0.625rem; padding: 0.05rem 0.35rem;">New</span>
                </div>
            </a>

            <!-- 4. Users Directory -->
            <a href="{{ route('admin.users_directory') }}" class="sidebar-nav-btn {{ request()->is('admin/users*') ? 'is-active' : '' }}" title="Users Directory">
                <div class="sidebar-icon-slot">
                    <i data-lucide="users" style="width: 17px; height: 17px;"></i>
                </div>
                <div class="sidebar-slide-text">
                    <span style="overflow: hidden; text-overflow: ellipsis;">Users Directory</span>
                </div>
            </a>

            <!-- 5. Security & Governance -->
            <a href="{{ route('admin.security_governance') }}" class="sidebar-nav-btn {{ request()->is('admin/security-governance*') ? 'is-active' : '' }}" title="Security & Governance">
                <div class="sidebar-icon-slot">
                    <i data-lucide="shield-check" style="width: 17px; height: 17px;"></i>
                </div>
                <div class="sidebar-slide-text">
                    <span style="overflow: hidden; text-overflow: ellipsis;">Security &amp; Governance</span>
                    <span class="badge badge-green" style="font-size: 0.625rem; padding: 0.05rem 0.35rem;">New</span>
                </div>
            </a>

            <!-- 6. Roles & Profiles -->
            <a href="{{ route('admin.roles_permissions') }}" class="sidebar-nav-btn {{ request()->is('admin/roles-permissions*') ? 'is-active' : '' }}" title="Roles & Profiles">
                <div class="sidebar-icon-slot">
                    <i data-lucide="layers" style="width: 17px; height: 17px;"></i>
                </div>
                <div class="sidebar-slide-text">
                    <span style="overflow: hidden; text-overflow: ellipsis;">Roles &amp; Profiles</span>
                    <span class="badge badge-purple" style="font-size: 0.625rem; padding: 0.05rem 0.35rem;">Rules</span>
                </div>
            </a>

            <!-- 7. Record Rights (CRUD) -->
            <a href="{{ route('admin.record_rights') }}" class="sidebar-nav-btn {{ request()->is('admin/record-rights*') ? 'is-active' : '' }}" title="Record Rights (CRUD)">
                <div class="sidebar-icon-slot">
                    <i data-lucide="shield" style="width: 17px; height: 17px;"></i>
                </div>
                <div class="sidebar-slide-text">
                    <span style="overflow: hidden; text-overflow: ellipsis;">Record Rights (CRUD)</span>
                </div>
            </a>

            <!-- 8. Field-Level Rights -->
            <a href="{{ route('admin.field_rights') }}" class="sidebar-nav-btn {{ request()->is('admin/field-rights*') ? 'is-active' : '' }}" title="Field-Level Rights">
                <div class="sidebar-icon-slot">
                    <i data-lucide="lock" style="width: 17px; height: 17px;"></i>
                </div>
                <div class="sidebar-slide-text">
                    <span style="overflow: hidden; text-overflow: ellipsis;">Field-Level Rights</span>
                </div>
            </a>

            <!-- 9. Sharing Rules Studio -->
            <a href="{{ route('admin.sharing_rules') }}" class="sidebar-nav-btn {{ request()->is('admin/sharing-rules*') ? 'is-active' : '' }}" title="Sharing Rules Studio">
                <div class="sidebar-icon-slot">
                    <i data-lucide="share-2" style="width: 17px; height: 17px;"></i>
                </div>
                <div class="sidebar-slide-text">
                    <span style="overflow: hidden; text-overflow: ellipsis;">Sharing Rules Studio</span>
                    <span class="badge badge-indigo" style="font-size: 0.625rem; padding: 0.05rem 0.35rem;">New</span>
                </div>
            </a>

            <!-- 10. Object & Field Manager -->
            <a href="{{ route('admin.object_fields') }}" class="sidebar-nav-btn {{ request()->is('admin/object-fields*') ? 'is-active' : '' }}" title="Object & Field Manager">
                <div class="sidebar-icon-slot">
                    <i data-lucide="sliders" style="width: 17px; height: 17px;"></i>
                </div>
                <div class="sidebar-slide-text">
                    <span style="overflow: hidden; text-overflow: ellipsis;">Object &amp; Fields</span>
                </div>
            </a>

            <!-- 11. Custom Objects Studio -->
            <a href="{{ route('admin.custom_objects') }}" class="sidebar-nav-btn {{ request()->is('admin/custom-objects*') ? 'is-active' : '' }}" title="Custom Objects Studio">
                <div class="sidebar-icon-slot">
                    <i data-lucide="box" style="width: 17px; height: 17px;"></i>
                </div>
                <div class="sidebar-slide-text">
                    <span style="overflow: hidden; text-overflow: ellipsis;">Custom Objects</span>
                    <span class="badge badge-gray" style="font-size: 0.625rem; padding: 0.05rem 0.35rem;">Studio</span>
                </div>
            </a>

            <!-- 12. ProcessStudio Flows -->
            <a href="{{ route('admin.process_studio') }}" class="sidebar-nav-btn {{ request()->is('admin/process-studio*') ? 'is-active' : '' }}" title="ProcessStudio">
                <div class="sidebar-icon-slot">
                    <i data-lucide="workflow" style="width: 17px; height: 17px;"></i>
                </div>
                <div class="sidebar-slide-text">
                    <span style="overflow: hidden; text-overflow: ellipsis;">ProcessStudio</span>
                    <span class="badge badge-green" style="font-size: 0.625rem; padding: 0.05rem 0.35rem;">New</span>
                </div>
            </a>

            <!-- 13. Page Layouts Designer -->
            <a href="{{ route('admin.page_layouts') }}" class="sidebar-nav-btn {{ request()->is('admin/page-layouts*') ? 'is-active' : '' }}" title="Page Layouts Designer">
                <div class="sidebar-icon-slot">
                    <i data-lucide="layout-template" style="width: 17px; height: 17px;"></i>
                </div>
                <div class="sidebar-slide-text">
                    <span style="overflow: hidden; text-overflow: ellipsis;">Page Layouts</span>
                    <span class="badge badge-indigo" style="font-size: 0.625rem; padding: 0.05rem 0.35rem;">New</span>
                </div>
            </a>

            <!-- 14. Custom Buttons Studio -->
            <a href="{{ url('/settings?tab=custom_buttons') }}" class="sidebar-nav-btn {{ request()->get('tab') === 'custom_buttons' ? 'is-active' : '' }}" title="Custom Buttons Studio">
                <div class="sidebar-icon-slot">
                    <i data-lucide="mouse-pointer-click" style="width: 17px; height: 17px;"></i>
                </div>
                <div class="sidebar-slide-text">
                    <span style="overflow: hidden; text-overflow: ellipsis;">Custom Buttons</span>
                    <span class="badge badge-purple" style="font-size: 0.625rem; padding: 0.05rem 0.35rem;">New</span>
                </div>
            </a>

            <!-- 15. Email Templates Studio -->
            <a href="{{ url('/settings?tab=email_templates') }}" class="sidebar-nav-btn {{ request()->get('tab') === 'email_templates' ? 'is-active' : '' }}" title="Email Templates Studio">
                <div class="sidebar-icon-slot">
                    <i data-lucide="mail" style="width: 17px; height: 17px;"></i>
                </div>
                <div class="sidebar-slide-text">
                    <span style="overflow: hidden; text-overflow: ellipsis;">Email Templates</span>
                    <span class="badge badge-blue" style="font-size: 0.625rem; padding: 0.05rem 0.35rem;">New</span>
                </div>
            </a>

            <!-- 16. Document & Billing Templates -->
            <a href="{{ url('/settings?tab=document_templates') }}" class="sidebar-nav-btn {{ request()->get('tab') === 'document_templates' ? 'is-active' : '' }}" title="Document & Billing Templates">
                <div class="sidebar-icon-slot">
                    <i data-lucide="file-spreadsheet" style="width: 17px; height: 17px;"></i>
                </div>
                <div class="sidebar-slide-text">
                    <span style="overflow: hidden; text-overflow: ellipsis;">Doc Templates</span>
                    <span class="badge badge-indigo" style="font-size: 0.625rem; padding: 0.05rem 0.35rem;">New</span>
                </div>
            </a>

            <!-- 17. Duplicate Rules & Merge -->
            <a href="{{ route('admin.duplicate_rules') }}" class="sidebar-nav-btn {{ request()->is('admin/duplicate-rules*') ? 'is-active' : '' }}" title="Duplicate Rules & Merge">
                <div class="sidebar-icon-slot">
                    <i data-lucide="git-merge" style="width: 17px; height: 17px;"></i>
                </div>
                <div class="sidebar-slide-text">
                    <span style="overflow: hidden; text-overflow: ellipsis;">Duplicate Rules</span>
                    <span class="badge badge-blue" style="font-size: 0.625rem; padding: 0.05rem 0.35rem;">Rules</span>
                </div>
            </a>

            <!-- 18. Global Search Settings -->
            <a href="{{ url('/settings?tab=global_search') }}" class="sidebar-nav-btn {{ request()->get('tab') === 'global_search' ? 'is-active' : '' }}" title="Global Search Settings">
                <div class="sidebar-icon-slot">
                    <i data-lucide="search" style="width: 17px; height: 17px;"></i>
                </div>
                <div class="sidebar-slide-text">
                    <span style="overflow: hidden; text-overflow: ellipsis;">Global Search</span>
                    <span class="badge badge-gray" style="font-size: 0.625rem; padding: 0.05rem 0.35rem;">New</span>
                </div>
            </a>

            <!-- 19. Data Management (CSV) -->
            <a href="{{ route('admin.data_studio') }}" class="sidebar-nav-btn {{ request()->is('admin/data-studio*') ? 'is-active' : '' }}" title="Data Management (CSV)">
                <div class="sidebar-icon-slot">
                    <i data-lucide="database" style="width: 17px; height: 17px;"></i>
                </div>
                <div class="sidebar-slide-text">
                    <span style="overflow: hidden; text-overflow: ellipsis;">Data Management</span>
                </div>
            </a>

            <!-- 20. CRM Migration Engine -->
            <a href="{{ url('/settings?tab=crm_migration') }}" class="sidebar-nav-btn {{ request()->get('tab') === 'crm_migration' ? 'is-active' : '' }}" title="CRM Migration Engine">
                <div class="sidebar-icon-slot">
                    <i data-lucide="rocket" style="width: 17px; height: 17px;"></i>
                </div>
                <div class="sidebar-slide-text">
                    <span style="overflow: hidden; text-overflow: ellipsis;">CRM Migration</span>
                    <span class="badge badge-purple" style="font-size: 0.625rem; padding: 0.05rem 0.35rem;">New</span>
                </div>
            </a>

            <!-- 21. Backup & Storage Quotas -->
            <a href="{{ url('/settings?tab=data_backup') }}" class="sidebar-nav-btn {{ request()->get('tab') === 'data_backup' ? 'is-active' : '' }}" title="Backup & Storage Quotas">
                <div class="sidebar-icon-slot">
                    <i data-lucide="hard-drive" style="width: 17px; height: 17px;"></i>
                </div>
                <div class="sidebar-slide-text">
                    <span style="overflow: hidden; text-overflow: ellipsis;">Backup &amp; Storage</span>
                    <span class="badge badge-blue" style="font-size: 0.625rem; padding: 0.05rem 0.35rem;">New</span>
                </div>
            </a>

            <!-- 22. API Keys & Webhooks -->
            <a href="{{ url('/settings?tab=api_webhooks') }}" class="sidebar-nav-btn {{ request()->get('tab') === 'api_webhooks' ? 'is-active' : '' }}" title="API Keys & Webhooks">
                <div class="sidebar-icon-slot">
                    <i data-lucide="webhook" style="width: 17px; height: 17px;"></i>
                </div>
                <div class="sidebar-slide-text">
                    <span style="overflow: hidden; text-overflow: ellipsis;">API &amp; Webhooks</span>
                    <span class="badge badge-amber" style="font-size: 0.625rem; padding: 0.05rem 0.35rem;">New</span>
                </div>
            </a>

            <!-- 23. Approval Workflows -->
            <a href="{{ route('admin.approval_rules') }}" class="sidebar-nav-btn {{ request()->is('admin/approval-rules*') ? 'is-active' : '' }}" title="Approval Workflows">
                <div class="sidebar-icon-slot">
                    <i data-lucide="git-pull-request" style="width: 17px; height: 17px;"></i>
                </div>
                <div class="sidebar-slide-text">
                    <span style="overflow: hidden; text-overflow: ellipsis;">Approval Rules</span>
                    <span class="badge badge-amber" style="font-size: 0.625rem; padding: 0.05rem 0.35rem;">Rules</span>
                </div>
            </a>

            <!-- 24. Notification Rules -->
            <a href="{{ route('admin.notification_rules') }}" class="sidebar-nav-btn {{ request()->is('admin/notification-rules*') ? 'is-active' : '' }}" title="Notification Rules">
                <div class="sidebar-icon-slot">
                    <i data-lucide="bell" style="width: 17px; height: 17px;"></i>
                </div>
                <div class="sidebar-slide-text">
                    <span style="overflow: hidden; text-overflow: ellipsis;">Notifications</span>
                    <span class="badge badge-purple" style="font-size: 0.625rem; padding: 0.05rem 0.35rem;">Studio</span>
                </div>
            </a>

            <!-- 25. Audit Trail -->
            <a href="{{ route('admin.audit_logs') }}" class="sidebar-nav-btn {{ request()->is('admin/audit-logs*') ? 'is-active' : '' }}" title="Audit Trail">
                <div class="sidebar-icon-slot">
                    <i data-lucide="file-text" style="width: 17px; height: 17px;"></i>
                </div>
                <div class="sidebar-slide-text">
                    <span style="overflow: hidden; text-overflow: ellipsis;">Audit Trail</span>
                    <span class="badge badge-gray" style="font-size: 0.625rem; padding: 0.05rem 0.35rem;">CSV</span>
                </div>
            </a>

            <!-- 26. Settings Studio (Master Workspace) -->
            <a href="{{ url('/settings') }}" class="sidebar-nav-btn {{ request()->is('settings*') && !request()->has('tab') ? 'is-active' : '' }}" title="Settings & Schema Workspace" style="margin-top: 0.5rem; border-top: 1px dashed var(--zinc-200); padding-top: 0.6rem;">
                <div class="sidebar-icon-slot">
                    <i data-lucide="sliders" style="width: 17px; height: 17px;"></i>
                </div>
                <div class="sidebar-slide-text">
                    <span style="overflow: hidden; text-overflow: ellipsis;">Settings Studio</span>
                    <span class="badge badge-indigo" style="font-size: 0.625rem; padding: 0.05rem 0.35rem;">Master</span>
                </div>
            </a>
        </nav>
    @else
        <!-- ================= REGULAR BUSINESS CRM NAVIGATION (1-TO-1 REACT PARITY) ================= -->
        <nav style="flex: 1; padding: 0.75rem 10px; overflow-y: auto; overflow-x: hidden;">
            <div class="sidebar-category-header">Operations &amp; Sales</div>

            <!-- 1. Dashboard -->
            <a href="{{ url('/') }}" class="sidebar-nav-btn {{ request()->is('/') || request()->is('dashboard*') ? 'is-active' : '' }}" title="Dashboard">
                <div class="sidebar-icon-slot">
                    <i data-lucide="layout-dashboard" style="width: 18px; height: 18px;"></i>
                </div>
                <div class="sidebar-slide-text">
                    <span class="nav-label">Dashboard</span>
                </div>
            </a>

            <!-- 2. Leads -->
            <a href="{{ url('/leads') }}" class="sidebar-nav-btn {{ request()->is('leads*') ? 'is-active' : '' }}" title="Leads">
                <div class="sidebar-icon-slot">
                    <i data-lucide="user-check" style="width: 18px; height: 18px;"></i>
                </div>
                <div class="sidebar-slide-text">
                    <span class="nav-label">Leads</span>
                    <span class="badge badge-purple" style="font-size: 0.65rem; padding: 0.1rem 0.4rem;">3</span>
                </div>
            </a>

            <!-- 3. Accounts -->
            <a href="{{ url('/accounts') }}" class="sidebar-nav-btn {{ request()->is('accounts*') ? 'is-active' : '' }}" title="Accounts">
                <div class="sidebar-icon-slot">
                    <i data-lucide="building-2" style="width: 18px; height: 18px;"></i>
                </div>
                <div class="sidebar-slide-text">
                    <span class="nav-label">Accounts</span>
                    <span class="badge badge-gray" style="font-size: 0.65rem; padding: 0.1rem 0.4rem;">4</span>
                </div>
            </a>

            <!-- 4. Contacts -->
            <a href="{{ url('/contacts') }}" class="sidebar-nav-btn {{ request()->is('contacts*') ? 'is-active' : '' }}" title="Contacts">
                <div class="sidebar-icon-slot">
                    <i data-lucide="users" style="width: 18px; height: 18px;"></i>
                </div>
                <div class="sidebar-slide-text">
                    <span class="nav-label">Contacts</span>
                    <span class="badge badge-green" style="font-size: 0.65rem; padding: 0.1rem 0.4rem;">4</span>
                </div>
            </a>

            <!-- 5. Opportunities -->
            <a href="{{ url('/opportunities') }}" class="sidebar-nav-btn {{ request()->is('opportunities*') ? 'is-active' : '' }}" title="Opportunities">
                <div class="sidebar-icon-slot">
                    <i data-lucide="briefcase" style="width: 18px; height: 18px;"></i>
                </div>
                <div class="sidebar-slide-text">
                    <span class="nav-label">Opportunities</span>
                    <span class="badge badge-blue" style="font-size: 0.65rem; padding: 0.1rem 0.4rem;">$433k</span>
                </div>
            </a>

            <!-- 6. Tasks -->
            <a href="{{ url('/tasks') }}" class="sidebar-nav-btn {{ request()->is('tasks*') ? 'is-active' : '' }}" title="Tasks">
                <div class="sidebar-icon-slot">
                    <i data-lucide="check-square" style="width: 18px; height: 18px;"></i>
                </div>
                <div class="sidebar-slide-text">
                    <span class="nav-label">Tasks</span>
                    <span class="badge badge-red" style="font-size: 0.65rem; padding: 0.1rem 0.4rem; font-weight: 700;">2 Overdue</span>
                </div>
            </a>

            <div class="sidebar-category-header" style="margin-top: 0.75rem;">Procurement &amp; Logistics</div>

            <!-- 7. Purchase Deals -->
            <a href="{{ url('/purchase-deals') }}" class="sidebar-nav-btn {{ request()->is('purchase-deals*') ? 'is-active' : '' }}" title="Purchase Deals">
                <div class="sidebar-icon-slot">
                    <i data-lucide="shopping-bag" style="width: 18px; height: 18px;"></i>
                </div>
                <div class="sidebar-slide-text">
                    <span class="nav-label">Purchase Deals</span>
                    <span class="badge badge-indigo" style="font-size: 0.65rem; padding: 0.1rem 0.4rem;">POs</span>
                </div>
            </a>

            <!-- 8. Shipping & Freight -->
            <a href="{{ url('/shipping') }}" class="sidebar-nav-btn {{ request()->is('shipping*') ? 'is-active' : '' }}" title="Shipping & Freight">
                <div class="sidebar-icon-slot">
                    <i data-lucide="truck" style="width: 18px; height: 18px;"></i>
                </div>
                <div class="sidebar-slide-text">
                    <span class="nav-label">Shipping &amp; Freight</span>
                </div>
            </a>

            <div class="sidebar-category-header" style="margin-top: 0.75rem;">Finance &amp; Banking</div>

            <!-- 9. Billing & Ledger -->
            <a href="{{ url('/documents') }}" class="sidebar-nav-btn {{ request()->is('documents*') ? 'is-active' : '' }}" title="Billing & Ledger">
                <div class="sidebar-icon-slot">
                    <i data-lucide="file-spreadsheet" style="width: 18px; height: 18px;"></i>
                </div>
                <div class="sidebar-slide-text">
                    <span class="nav-label">Billing &amp; Ledger</span>
                    <span class="badge badge-indigo" style="font-size: 0.65rem; padding: 0.1rem 0.4rem;">Vault</span>
                </div>
            </a>

            <!-- 10. Invoices & AR/AP -->
            <a href="{{ url('/payments') }}" class="sidebar-nav-btn {{ request()->is('payments*') ? 'is-active' : '' }}" title="Invoices & AR/AP">
                <div class="sidebar-icon-slot">
                    <i data-lucide="credit-card" style="width: 18px; height: 18px;"></i>
                </div>
                <div class="sidebar-slide-text">
                    <span class="nav-label">Invoices &amp; AR/AP</span>
                </div>
            </a>

            <!-- 11. Wire Reconcile -->
            <a href="{{ url('/wires') }}" class="sidebar-nav-btn {{ request()->is('wires*') ? 'is-active' : '' }}" title="Wire Reconcile">
                <div class="sidebar-icon-slot">
                    <i data-lucide="landmark" style="width: 18px; height: 18px;"></i>
                </div>
                <div class="sidebar-slide-text">
                    <span class="nav-label">Wire Reconcile</span>
                    <span class="badge badge-amber" style="font-size: 0.65rem; padding: 0.1rem 0.4rem;">2 Pending</span>
                </div>
            </a>

            <div class="sidebar-category-header" style="margin-top: 0.75rem;">System Governance &amp; Rules</div>

            <!-- 12. Settings & Schema -->
            <a href="{{ url('/settings') }}" class="sidebar-nav-btn {{ request()->is('settings*') ? 'is-active' : '' }}" title="Settings & Schema">
                <div class="sidebar-icon-slot">
                    <i data-lucide="sliders" style="width: 18px; height: 18px;"></i>
                </div>
                <div class="sidebar-slide-text">
                    <span class="nav-label">Settings &amp; Schema</span>
                    <span class="badge badge-gray" style="font-size: 0.65rem; padding: 0.1rem 0.4rem;">Admin</span>
                </div>
            </a>
        </nav>
    @endif

    <!-- Bottom Status Footer -->
    <div style="height: 48px; padding: 0 10px; border-top: 1px solid var(--border-card); font-size: 0.725rem; color: var(--zinc-500); display: flex; align-items: center; background: var(--zinc-50); flex-shrink: 0;">
        <div class="sidebar-icon-slot" style="width: 36px; min-width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;">
            <span style="color: #10b981; font-weight: 700; font-size: 0.8rem;">●</span>
        </div>
        <div class="sidebar-slide-text" style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
            <span style="font-weight: 600; color: var(--zinc-700);">
                {{ $isAdminMode ? 'Admin Console' : 'DealSync v2.4' }}
            </span>
            <span style="font-size: 0.65rem; color: #10b981; font-weight: 600;">Connected</span>
        </div>
    </div>
</aside>
