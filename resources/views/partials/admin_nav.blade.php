<!-- Admin Governance & Rules Studio Header Navigation -->
<div style="background: var(--bg-card); border: 1px solid var(--border-card); border-radius: var(--radius-lg); padding: 0.75rem 1rem; margin-bottom: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem; flex-wrap: wrap; gap: 0.75rem;">
        <div style="display: flex; align-items: center; gap: 0.6rem;">
            <div style="width: 28px; height: 28px; border-radius: 7px; background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 0.8rem; font-weight: 700;">
                <i data-lucide="shield-check" style="width: 15px; height: 15px;"></i>
            </div>
            <div>
                <span style="font-size: 0.85rem; font-weight: 800; color: var(--zinc-900); letter-spacing: -0.01em;">Enterprise Administration &amp; Rules Studio</span>
                <span class="badge badge-purple" style="font-size: 0.65rem; margin-left: 0.4rem; font-weight: 700;">SOC2 Type II</span>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <a href="{{ url('/') }}" class="btn btn-secondary btn-sm" style="font-size: 0.725rem; padding: 0.25rem 0.65rem;">
                <i data-lucide="arrow-left" style="width: 13px; height: 13px;"></i>
                <span>Return to CRM Workspace</span>
            </a>
            <a href="{{ url('/settings') }}" class="btn btn-secondary btn-sm" style="font-size: 0.725rem; padding: 0.25rem 0.65rem;">
                <i data-lucide="sliders" style="width: 13px; height: 13px;"></i>
                <span>Settings Console</span>
            </a>
        </div>
    </div>

    <!-- Horizontal Subtabs (Ordered 1-to-1 matching React Sidebar & Studios) -->
    <div style="display: flex; align-items: center; gap: 0.35rem; overflow-x: auto; padding-bottom: 0.25rem; border-top: 1px solid var(--zinc-100); padding-top: 0.6rem;">
        <!-- 1. Tenant Studio (Super Admin Exclusive) -->
        @if(\Illuminate\Support\Facades\Auth::guard('super')->check())
        <a href="{{ route('admin.tenant_studio') }}" class="btn btn-sm {{ request()->routeIs('admin.tenant_studio') ? 'btn-primary' : 'btn-secondary' }}" style="font-size: 0.725rem; padding: 0.25rem 0.65rem; white-space: nowrap; border-radius: 999px;">
            <i data-lucide="building-2" style="width: 12px; height: 12px; margin-right: 4px;"></i>
            Tenant Studio (Master)
        </a>
        @endif

        <!-- 2. Dashboard Studio -->
        <a href="{{ route('admin.dashboard_studio') }}" class="btn btn-sm {{ request()->routeIs('admin.dashboard_studio') ? 'btn-primary' : 'btn-secondary' }}" style="font-size: 0.725rem; padding: 0.25rem 0.65rem; white-space: nowrap; border-radius: 999px;">
            <i data-lucide="layout-dashboard" style="width: 12px; height: 12px; margin-right: 4px;"></i>
            Dashboard Studio
        </a>

        <!-- 3. Company Profile & Localization -->
        <a href="{{ route('admin.company_profile') }}" class="btn btn-sm {{ request()->routeIs('admin.company_profile') ? 'btn-primary' : 'btn-secondary' }}" style="font-size: 0.725rem; padding: 0.25rem 0.65rem; white-space: nowrap; border-radius: 999px;">
            <i data-lucide="building" style="width: 12px; height: 12px; margin-right: 4px;"></i>
            Company Profile
        </a>

        <!-- 4. Users Directory -->
        <a href="{{ route('admin.users_directory') }}" class="btn btn-sm {{ request()->routeIs('admin.users_directory') || request()->routeIs('admin.users') ? 'btn-primary' : 'btn-secondary' }}" style="font-size: 0.725rem; padding: 0.25rem 0.65rem; white-space: nowrap; border-radius: 999px;">
            <i data-lucide="users" style="width: 12px; height: 12px; margin-right: 4px;"></i>
            Users Directory
        </a>

        <!-- 5. Security & Governance -->
        <a href="{{ route('admin.security_governance') }}" class="btn btn-sm {{ request()->routeIs('admin.security_governance') ? 'btn-primary' : 'btn-secondary' }}" style="font-size: 0.725rem; padding: 0.25rem 0.65rem; white-space: nowrap; border-radius: 999px;">
            <i data-lucide="shield-check" style="width: 12px; height: 12px; margin-right: 4px;"></i>
            Security &amp; Governance
        </a>

        <!-- 6. Roles & Profiles -->
        <a href="{{ route('admin.roles_permissions') }}" class="btn btn-sm {{ request()->routeIs('admin.roles_permissions') ? 'btn-primary' : 'btn-secondary' }}" style="font-size: 0.725rem; padding: 0.25rem 0.65rem; white-space: nowrap; border-radius: 999px;">
            <i data-lucide="layers" style="width: 12px; height: 12px; margin-right: 4px;"></i>
            Roles &amp; Profiles
        </a>

        <!-- 7. Record Rights (CRUD) -->
        <a href="{{ route('admin.record_rights') }}" class="btn btn-sm {{ request()->routeIs('admin.record_rights') ? 'btn-primary' : 'btn-secondary' }}" style="font-size: 0.725rem; padding: 0.25rem 0.65rem; white-space: nowrap; border-radius: 999px;">
            <i data-lucide="shield" style="width: 12px; height: 12px; margin-right: 4px;"></i>
            Record Rights (CRUD)
        </a>

        <!-- 8. Field-Level Rights (FLS) -->
        <a href="{{ route('admin.field_rights') }}" class="btn btn-sm {{ request()->routeIs('admin.field_rights') ? 'btn-primary' : 'btn-secondary' }}" style="font-size: 0.725rem; padding: 0.25rem 0.65rem; white-space: nowrap; border-radius: 999px;">
            <i data-lucide="lock" style="width: 12px; height: 12px; margin-right: 4px;"></i>
            Field-Level Rights (FLS)
        </a>

        <!-- 9. Sharing Rules -->
        <a href="{{ route('admin.sharing_rules') }}" class="btn btn-sm {{ request()->routeIs('admin.sharing_rules') ? 'btn-primary' : 'btn-secondary' }}" style="font-size: 0.725rem; padding: 0.25rem 0.65rem; white-space: nowrap; border-radius: 999px;">
            <i data-lucide="share-2" style="width: 12px; height: 12px; margin-right: 4px;"></i>
            Sharing Rules
        </a>

        <!-- 10. Object & Fields -->
        <a href="{{ route('admin.object_fields') }}" class="btn btn-sm {{ request()->routeIs('admin.object_fields') ? 'btn-primary' : 'btn-secondary' }}" style="font-size: 0.725rem; padding: 0.25rem 0.65rem; white-space: nowrap; border-radius: 999px;">
            <i data-lucide="sliders" style="width: 12px; height: 12px; margin-right: 4px;"></i>
            Object &amp; Fields
        </a>

        <!-- 11. Custom Objects -->
        <a href="{{ route('admin.custom_objects') }}" class="btn btn-sm {{ request()->routeIs('admin.custom_objects') ? 'btn-primary' : 'btn-secondary' }}" style="font-size: 0.725rem; padding: 0.25rem 0.65rem; white-space: nowrap; border-radius: 999px;">
            <i data-lucide="box" style="width: 12px; height: 12px; margin-right: 4px;"></i>
            Custom Objects
        </a>

        <!-- 12. ProcessStudio -->
        <a href="{{ route('admin.process_studio') }}" class="btn btn-sm {{ request()->routeIs('admin.process_studio') ? 'btn-primary' : 'btn-secondary' }}" style="font-size: 0.725rem; padding: 0.25rem 0.65rem; white-space: nowrap; border-radius: 999px;">
            <i data-lucide="workflow" style="width: 12px; height: 12px; margin-right: 4px;"></i>
            ProcessStudio
        </a>

        <!-- 13. Page Layouts -->
        <a href="{{ route('admin.page_layouts') }}" class="btn btn-sm {{ request()->routeIs('admin.page_layouts') ? 'btn-primary' : 'btn-secondary' }}" style="font-size: 0.725rem; padding: 0.25rem 0.65rem; white-space: nowrap; border-radius: 999px;">
            <i data-lucide="layout-template" style="width: 12px; height: 12px; margin-right: 4px;"></i>
            Page Layouts
        </a>

        <!-- 13.5 Custom Buttons -->
        <a href="{{ route('admin.custom_buttons') }}" class="btn btn-sm {{ request()->routeIs('admin.custom_buttons') ? 'btn-primary' : 'btn-secondary' }}" style="font-size: 0.725rem; padding: 0.25rem 0.65rem; white-space: nowrap; border-radius: 999px;">
            <i data-lucide="mouse-pointer-click" style="width: 12px; height: 12px; margin-right: 4px;"></i>
            Custom Buttons
        </a>

        <!-- 14. Duplicate Rules -->
        <a href="{{ route('admin.duplicate_rules') }}" class="btn btn-sm {{ request()->routeIs('admin.duplicate_rules') ? 'btn-primary' : 'btn-secondary' }}" style="font-size: 0.725rem; padding: 0.25rem 0.65rem; white-space: nowrap; border-radius: 999px;">
            <i data-lucide="git-merge" style="width: 12px; height: 12px; margin-right: 4px;"></i>
            Duplicate Rules
        </a>

        <!-- 15. Data Management -->
        <a href="{{ route('admin.data_studio') }}" class="btn btn-sm {{ request()->routeIs('admin.data_studio') ? 'btn-primary' : 'btn-secondary' }}" style="font-size: 0.725rem; padding: 0.25rem 0.65rem; white-space: nowrap; border-radius: 999px;">
            <i data-lucide="database" style="width: 12px; height: 12px; margin-right: 4px;"></i>
            Data Management
        </a>

        <!-- 16. Approval Workflows -->
        <a href="{{ route('admin.approval_rules') }}" class="btn btn-sm {{ request()->routeIs('admin.approval_rules') ? 'btn-primary' : 'btn-secondary' }}" style="font-size: 0.725rem; padding: 0.25rem 0.65rem; white-space: nowrap; border-radius: 999px;">
            <i data-lucide="git-pull-request" style="width: 12px; height: 12px; margin-right: 4px;"></i>
            Approval Workflows
        </a>

        <!-- 17. Notification Rules -->
        <a href="{{ route('admin.notification_rules') }}" class="btn btn-sm {{ request()->routeIs('admin.notification_rules') ? 'btn-primary' : 'btn-secondary' }}" style="font-size: 0.725rem; padding: 0.25rem 0.65rem; white-space: nowrap; border-radius: 999px;">
            <i data-lucide="bell" style="width: 12px; height: 12px; margin-right: 4px;"></i>
            Notification Rules
        </a>

        <!-- 18. Email Templates Studio -->
        <a href="{{ route('admin.email_templates') }}" class="btn btn-sm {{ request()->routeIs('admin.email_templates*') ? 'btn-primary' : 'btn-secondary' }}" style="font-size: 0.725rem; padding: 0.25rem 0.65rem; white-space: nowrap; border-radius: 999px;">
            <i data-lucide="mail" style="width: 12px; height: 12px; margin-right: 4px;"></i>
            Email Templates
        </a>

        <!-- 19. Audit Trail -->
        <a href="{{ route('admin.audit_logs') }}" class="btn btn-sm {{ request()->routeIs('admin.audit_logs') ? 'btn-primary' : 'btn-secondary' }}" style="font-size: 0.725rem; padding: 0.25rem 0.65rem; white-space: nowrap; border-radius: 999px;">
            <i data-lucide="file-text" style="width: 12px; height: 12px; margin-right: 4px;"></i>
            Audit Trail
        </a>
    </div>
</div>
