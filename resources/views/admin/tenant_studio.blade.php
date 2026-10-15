@extends('layouts.app')

@section('title', 'Tenant Management Studio — DealSync Admin')

@section('content')
<div class="animate-fade-in" style="display: flex; flex-direction: column; gap: 1.25rem;">

    <!-- Flash Alerts -->
    @if(session('success'))
        <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: var(--radius-md); padding: 0.75rem 1rem; display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; color: #065f46; font-size: 0.8125rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <i data-lucide="check-circle-2" style="width: 18px; height: 18px; color: #059669; flex-shrink: 0;"></i>
                <span style="font-weight: 500;">{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" style="background: none; border: none; cursor: pointer; color: #059669; padding: 0.2rem;">
                <i data-lucide="x" style="width: 14px; height: 14px;"></i>
            </button>
        </div>
    @endif

    @if(session('error') || $errors->any())
        <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: var(--radius-md); padding: 0.75rem 1rem; color: #991b1b; font-size: 0.8125rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem; font-weight: 600;">
                <i data-lucide="alert-triangle" style="width: 18px; height: 18px; color: #dc2626; flex-shrink: 0;"></i>
                <span>{{ session('error') ?? 'Please resolve the following errors:' }}</span>
            </div>
            @if($errors->any())
                <ul style="margin: 0.25rem 0 0 1.5rem; padding: 0; font-size: 0.78rem;">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endif

    <!-- Header Bar -->
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--zinc-950); letter-spacing: -0.02em; margin: 0;">
                    Tenant Management Studio
                </h2>
                <span class="badge" style="font-size: 0.675rem; font-weight: 700; padding: 0.2rem 0.55rem; background: #eef2ff; color: #4338ca; border: 1px solid #c7d2fe;">
                    Central Master Architecture
                </span>
            </div>
            <p style="font-size: 0.8125rem; color: var(--zinc-500); margin: 0.25rem 0 0 0;">
                Provision client organizations with dedicated isolated databases, configure company branding, and manage tenant organizations across the CRM ecosystem.
            </p>
        </div>

        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <button
                type="button"
                onclick="window.location.reload();"
                class="btn btn-secondary btn-sm"
                style="display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.8rem; padding: 0.45rem 0.75rem;"
                title="Refresh tenant directory"
            >
                <i data-lucide="refresh-cw" style="width: 13px; height: 13px;"></i>
                <span>Refresh Directory</span>
            </button>

            <button
                type="button"
                onclick="openCreateTenantModal()"
                class="btn btn-primary btn-sm"
                style="display: inline-flex; align-items: center; gap: 0.4rem; background: #4f46e5; border-color: #4338ca; font-weight: 600; padding: 0.45rem 0.95rem; font-size: 0.8125rem; box-shadow: 0 1px 3px rgba(79, 70, 229, 0.3);"
            >
                <i data-lucide="plus-circle" style="width: 15px; height: 15px;"></i>
                <span>Register &amp; Provision Tenant</span>
            </button>
        </div>
    </div>

    <!-- Overview Metric Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
        <!-- Card 1: Total Tenants -->
        <div class="card" style="padding: 1rem 1.25rem; background: #ffffff; border: 1px solid var(--border-card); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.4rem;">
                <span style="font-size: 0.75rem; font-weight: 600; color: var(--zinc-500); text-transform: uppercase; letter-spacing: 0.04em;">
                    Total Tenants
                </span>
                <div style="width: 28px; height: 28px; border-radius: 6px; background: #eef2ff; color: #4f46e5; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="building-2" style="width: 15px; height: 15px;"></i>
                </div>
            </div>
            <div style="font-size: 1.5rem; font-weight: 800; color: var(--zinc-950); letter-spacing: -0.02em;">
                {{ $tenants->count() }}
            </div>
            <div style="font-size: 0.7rem; color: var(--zinc-400); margin-top: 0.2rem;">
                Multi-tenant organizations
            </div>
        </div>

        <!-- Card 2: Active Workspaces -->
        <div class="card" style="padding: 1rem 1.25rem; background: #ffffff; border: 1px solid var(--border-card); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.4rem;">
                <span style="font-size: 0.75rem; font-weight: 600; color: var(--zinc-500); text-transform: uppercase; letter-spacing: 0.04em;">
                    Active Workspaces
                </span>
                <div style="width: 28px; height: 28px; border-radius: 6px; background: #ecfdf5; color: #059669; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="activity" style="width: 15px; height: 15px;"></i>
                </div>
            </div>
            <div style="font-size: 1.5rem; font-weight: 800; color: #059669; letter-spacing: -0.02em;">
                {{ $tenants->where('is_active', true)->count() }}
            </div>
            <div style="font-size: 0.7rem; color: var(--zinc-400); margin-top: 0.2rem;">
                Live and serving users
            </div>
        </div>

        <!-- Card 3: Enterprise Tiers -->
        <div class="card" style="padding: 1rem 1.25rem; background: #ffffff; border: 1px solid var(--border-card); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.4rem;">
                <span style="font-size: 0.75rem; font-weight: 600; color: var(--zinc-500); text-transform: uppercase; letter-spacing: 0.04em;">
                    Enterprise Tier
                </span>
                <div style="width: 28px; height: 28px; border-radius: 6px; background: #faf5ff; color: #9333ea; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="crown" style="width: 15px; height: 15px;"></i>
                </div>
            </div>
            <div style="font-size: 1.5rem; font-weight: 800; color: var(--zinc-950); letter-spacing: -0.02em;">
                {{ $tenants->where('plan_tier', 'enterprise')->count() }}
            </div>
            <div style="font-size: 0.7rem; color: var(--zinc-400); margin-top: 0.2rem;">
                Dedicated high-scale tenants
            </div>
        </div>

        <!-- Card 4: Isolated Databases -->
        <div class="card" style="padding: 1rem 1.25rem; background: #ffffff; border: 1px solid var(--border-card); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.4rem;">
                <span style="font-size: 0.75rem; font-weight: 600; color: var(--zinc-500); text-transform: uppercase; letter-spacing: 0.04em;">
                    Isolated Databases
                </span>
                <div style="width: 28px; height: 28px; border-radius: 6px; background: #f0fdf4; color: #16a34a; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="database" style="width: 15px; height: 15px;"></i>
                </div>
            </div>
            <div style="font-size: 1.5rem; font-weight: 800; color: var(--zinc-950); letter-spacing: -0.02em;">
                {{ $tenants->count() }}
            </div>
            <div style="font-size: 0.7rem; color: var(--zinc-400); margin-top: 0.2rem;">
                100% data partition isolation
            </div>
        </div>
    </div>

    <!-- Search & Filter Controls -->
    <div style="display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: 0.65rem; flex-wrap: wrap; flex: 1;">
            <!-- Search Input -->
            <div style="position: relative; width: 300px; max-width: 100%;">
                <i data-lucide="search" style="width: 14px; height: 14px; color: var(--zinc-400); position: absolute; left: 10px; top: 50%; transform: translateY(-50%);"></i>
                <input
                    type="text"
                    id="tenant-search-input"
                    class="form-input"
                    oninput="filterTenants()"
                    placeholder="Search by name, domain, or DB..."
                    style="padding-left: 32px; font-size: 0.8125rem; height: 36px; width: 100%; border-radius: var(--radius-md); border: 1px solid var(--zinc-200); background: #ffffff;"
                >
            </div>

            <!-- Tier Filter -->
            <select
                id="tenant-tier-filter"
                class="form-select"
                onchange="filterTenants()"
                style="padding: 0.4rem 0.75rem; font-size: 0.8125rem; height: 36px; border-radius: var(--radius-md); border: 1px solid var(--zinc-200); background: #ffffff; color: var(--zinc-700); cursor: pointer;"
            >
                <option value="all">All Plan Tiers</option>
                <option value="enterprise">Enterprise</option>
                <option value="professional">Professional</option>
                <option value="starter">Starter</option>
            </select>

            <!-- Status Filter -->
            <select
                id="tenant-status-filter"
                class="form-select"
                onchange="filterTenants()"
                style="padding: 0.4rem 0.75rem; font-size: 0.8125rem; height: 36px; border-radius: var(--radius-md); border: 1px solid var(--zinc-200); background: #ffffff; color: var(--zinc-700); cursor: pointer;"
            >
                <option value="all">All Statuses</option>
                <option value="active">Active Only</option>
                <option value="inactive">Suspended Only</option>
            </select>
        </div>

        <div style="font-size: 0.8rem; color: var(--zinc-500); font-weight: 500;">
            Showing <strong id="visible-tenant-count">{{ $tenants->count() }}</strong> of <strong>{{ $tenants->count() }}</strong> registered organizations
        </div>
    </div>

    <!-- Tenants Data Table -->
    <div class="card" style="padding: 0; overflow-x: auto; -webkit-overflow-scrolling: touch; border: 1px solid var(--border-card); border-radius: var(--radius-lg); background: #ffffff; box-shadow: var(--shadow-sm);">
        <table class="data-table" id="tenants-table" style="width: 100%; min-width: 980px; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 1px solid var(--zinc-200); background: var(--zinc-50);">
                    <th style="padding: 0.85rem 1rem; text-align: left; font-size: 0.75rem; font-weight: 600; color: var(--zinc-700); text-transform: uppercase; letter-spacing: 0.03em;">Tenant Organization</th>
                    <th style="padding: 0.85rem 0.85rem; text-align: left; font-size: 0.75rem; font-weight: 600; color: var(--zinc-700); text-transform: uppercase; letter-spacing: 0.03em;">Domain / Host</th>
                    <th style="padding: 0.85rem 0.85rem; text-align: left; font-size: 0.75rem; font-weight: 600; color: var(--zinc-700); text-transform: uppercase; letter-spacing: 0.03em;">Isolated Database</th>
                    <th style="padding: 0.85rem 0.85rem; text-align: left; font-size: 0.75rem; font-weight: 600; color: var(--zinc-700); text-transform: uppercase; letter-spacing: 0.03em;">Plan Tier</th>
                    <th style="padding: 0.85rem 0.85rem; text-align: left; font-size: 0.75rem; font-weight: 600; color: var(--zinc-700); text-transform: uppercase; letter-spacing: 0.03em;">Seats</th>
                    <th style="padding: 0.85rem 0.85rem; text-align: left; font-size: 0.75rem; font-weight: 600; color: var(--zinc-700); text-transform: uppercase; letter-spacing: 0.03em;">Status</th>
                    <th style="padding: 0.85rem 0.85rem; text-align: left; font-size: 0.75rem; font-weight: 600; color: var(--zinc-700); text-transform: uppercase; letter-spacing: 0.03em;">Provisioned</th>
                    <th style="padding: 0.85rem 1rem; width: 220px; min-width: 220px; text-align: right; font-size: 0.75rem; font-weight: 600; color: var(--zinc-700); text-transform: uppercase; letter-spacing: 0.03em;">Actions</th>
                </tr>
            </thead>
            <tbody id="tenants-table-body">
                @forelse($tenants as $t)
                    @php
                        $isActiveWorkspace = isset($currentTenant) && $currentTenant->id === $t->id;
                        $initials = strtoupper(substr($t->name, 0, 2));
                        $accentColor = $t->accent_color ?: '#4F46E5';
                    @endphp
                    <tr class="tenant-row" data-name="{{ strtolower($t->name) }}" data-domain="{{ strtolower($t->domain) }}" data-db="{{ strtolower($t->db_name) }}" data-tier="{{ strtolower($t->plan_tier) }}" data-status="{{ $t->is_active ? 'active' : 'inactive' }}" style="border-bottom: 1px solid var(--zinc-100); {{ $isActiveWorkspace ? 'background: rgba(79, 70, 229, 0.03);' : '' }}">
                        <!-- Column 1: Organization -->
                        <td style="padding: 0.85rem 1rem;">
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <div style="width: 38px; height: 38px; border-radius: 10px; background: linear-gradient(135deg, {{ $accentColor }} 0%, #1e1b4b 120%); color: #ffffff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.85rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1); flex-shrink: 0;">
                                    {{ $initials }}
                                </div>
                                <div>
                                    <div style="font-weight: 600; color: var(--zinc-950); display: flex; align-items: center; gap: 0.4rem; font-size: 0.875rem;">
                                        <span class="tenant-title">{{ $t->name }}</span>
                                        @if($isActiveWorkspace)
                                            <span style="font-size: 0.625rem; color: #047857; background: #ecfdf5; border: 1px solid #a7f3d0; padding: 0.08rem 0.4rem; border-radius: 4px; font-weight: 700; display: inline-flex; align-items: center; gap: 0.2rem;">
                                                <i data-lucide="check" style="width: 10px; height: 10px;"></i>
                                                Current Active
                                            </span>
                                        @endif
                                    </div>
                                    <div style="font-size: 0.725rem; color: var(--zinc-400); margin-top: 0.15rem;">
                                        {{ $t->legal_name ?: $t->name }}
                                    </div>
                                </div>
                            </div>
                        </td>

                        <!-- Column 2: Domain -->
                        <td style="padding: 0.85rem 0.85rem;">
                            <div style="display: flex; align-items: center; gap: 0.35rem; font-family: ui-monospace, monospace; font-size: 0.8125rem; color: var(--zinc-800);">
                                <i data-lucide="globe" style="width: 13px; height: 13px; color: var(--zinc-400);"></i>
                                <span>{{ $t->domain }}</span>
                            </div>
                        </td>

                        <!-- Column 3: Database Instance -->
                        <td style="padding: 0.85rem 0.85rem;">
                            <div style="display: flex; flex-direction: column; gap: 0.15rem;">
                                <div style="display: flex; align-items: center; gap: 0.35rem;">
                                    <span style="width: 7px; height: 7px; border-radius: 50%; background: #10b981; display: inline-block;"></span>
                                    <span style="font-family: ui-monospace, monospace; font-size: 0.775rem; color: var(--zinc-800); font-weight: 500;">
                                        {{ $t->db_name }}
                                    </span>
                                </div>
                                <span style="font-size: 0.675rem; color: var(--zinc-400); margin-left: 11px;">
                                    MySQL • Host: {{ $t->db_host ?: '127.0.0.1' }}
                                </span>
                            </div>
                        </td>

                        <!-- Column 4: Plan Tier -->
                        <td style="padding: 0.85rem 0.85rem;">
                            @php
                                $tier = strtolower($t->plan_tier);
                                $tierStyles = [
                                    'enterprise'   => 'background: #f5f3ff; color: #6d28d9; border: 1px solid #ddd6fe;',
                                    'professional' => 'background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe;',
                                    'starter'      => 'background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;',
                                ];
                                $style = $tierStyles[$tier] ?? 'background: #f4f4f5; color: #3f3f46; border: 1px solid #e4e4e7;';
                            @endphp
                            <span style="font-size: 0.725rem; font-weight: 600; padding: 0.15rem 0.5rem; border-radius: 9999px; text-transform: capitalize; {{ $style }}">
                                {{ $t->plan_tier }}
                            </span>
                        </td>

                        <!-- Column 5: Seats -->
                        <td style="padding: 0.85rem 0.85rem;">
                            <div style="display: flex; align-items: center; gap: 0.3rem; font-size: 0.8125rem; color: var(--zinc-700); font-weight: 500;">
                                <i data-lucide="users" style="width: 13px; height: 13px; color: var(--zinc-400);"></i>
                                <span>{{ $t->max_seats }} seats</span>
                            </div>
                        </td>

                        <!-- Column 6: Status -->
                        <td style="padding: 0.85rem 0.85rem;">
                            @if($t->is_active)
                                <span style="display: inline-flex; align-items: center; gap: 0.3rem; font-size: 0.725rem; font-weight: 600; color: #047857; background: #ecfdf5; border: 1px solid #a7f3d0; padding: 0.15rem 0.45rem; border-radius: 9999px;">
                                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #10b981;"></span>
                                    Active
                                </span>
                            @else
                                <span style="display: inline-flex; align-items: center; gap: 0.3rem; font-size: 0.725rem; font-weight: 600; color: #b91c1c; background: #fef2f2; border: 1px solid #fecaca; padding: 0.15rem 0.45rem; border-radius: 9999px;">
                                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #ef4444;"></span>
                                    Suspended
                                </span>
                            @endif
                        </td>

                        <!-- Column 7: Provisioned Date -->
                        <td style="padding: 0.85rem 0.85rem; font-size: 0.775rem; color: var(--zinc-500);">
                            {{ $t->created_at ? $t->created_at->format('M d, Y') : 'N/A' }}
                        </td>

                        <!-- Column 8: Actions -->
                        <td style="padding: 0.85rem 1rem; text-align: right;">
                            <div style="display: inline-flex; align-items: center; gap: 0.4rem;">
                                <!-- Switch Workspace Form -->
                                <form method="POST" action="{{ route('admin.tenant_studio.switch', $t->id) }}" style="margin: 0;">
                                    @csrf
                                    <button
                                        type="submit"
                                        class="btn btn-sm"
                                        title="Switch active workspace to this tenant"
                                        style="display: inline-flex; align-items: center; gap: 0.3rem; font-size: 0.75rem; font-weight: 600; padding: 0.3rem 0.6rem; border-radius: var(--radius-md); background: {{ $isActiveWorkspace ? '#ecfdf5' : '#f1f5f9' }}; color: {{ $isActiveWorkspace ? '#047857' : 'var(--zinc-700)' }}; border: 1px solid {{ $isActiveWorkspace ? '#a7f3d0' : 'var(--zinc-200)' }}; cursor: pointer; transition: all 0.15s;"
                                    >
                                        <i data-lucide="log-in" style="width: 12px; height: 12px;"></i>
                                        <span>{{ $isActiveWorkspace ? 'Active' : 'Enter' }}</span>
                                    </button>
                                </form>

                                <!-- Edit Tenant Button -->
                                <button
                                    type="button"
                                    class="btn btn-ghost btn-sm"
                                    onclick="openEditTenantModal({{ json_encode($t) }})"
                                    title="Edit Tenant Branding &amp; Details"
                                    style="padding: 0.35rem 0.45rem; border-radius: var(--radius-md); color: var(--zinc-600); cursor: pointer;"
                                >
                                    <i data-lucide="edit-3" style="width: 14px; height: 14px;"></i>
                                </button>

                                <!-- Delete Tenant Button -->
                                <button
                                    type="button"
                                    class="btn btn-ghost btn-sm"
                                    onclick="openDeleteTenantModal({{ $t->id }}, '{{ addslashes($t->name) }}', '{{ $t->db_name }}')"
                                    title="Delete Tenant Organization"
                                    style="padding: 0.35rem 0.45rem; border-radius: var(--radius-md); color: #ef4444; cursor: pointer;"
                                >
                                    <i data-lucide="trash-2" style="width: 14px; height: 14px;"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="padding: 3rem 1rem; text-align: center;">
                            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.75rem;">
                                <div style="width: 48px; height: 48px; border-radius: 12px; background: #eef2ff; color: #4f46e5; display: flex; align-items: center; justify-content: center;">
                                    <i data-lucide="building" style="width: 24px; height: 24px;"></i>
                                </div>
                                <div style="font-size: 0.95rem; font-weight: 700; color: var(--zinc-800);">
                                    No Tenant Organizations Registered
                                </div>
                                <p style="font-size: 0.8125rem; color: var(--zinc-500); max-width: 360px; margin: 0;">
                                    No tenant workspaces have been created yet. Click the button below to register and provision your first isolated tenant.
                                </p>
                                <button
                                    type="button"
                                    onclick="openCreateTenantModal()"
                                    class="btn btn-primary btn-sm"
                                    style="margin-top: 0.5rem; background: #4f46e5; border-color: #4338ca; display: inline-flex; align-items: center; gap: 0.4rem;"
                                >
                                    <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
                                    <span>Provision First Tenant</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

<!-- ==================== REGISTER & PROVISION NEW TENANT MODAL ==================== -->
<div id="create-tenant-modal" class="modal-overlay">
    <div class="modal-card" style="max-width: 600px; width: 95%; max-height: 92vh; overflow-y: auto;">
        <!-- Header -->
        <div style="padding: 1.15rem 1.5rem; border-bottom: 1px solid var(--zinc-200); display: flex; align-items: center; justify-content: space-between; background: #ffffff; position: sticky; top: 0; z-index: 10;">
            <div style="display: flex; align-items: center; gap: 0.6rem;">
                <div style="width: 32px; height: 32px; border-radius: 8px; background: #eef2ff; color: #4f46e5; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="building-2" style="width: 17px; height: 17px;"></i>
                </div>
                <div>
                    <h3 style="font-size: 1rem; font-weight: 700; color: var(--zinc-900); margin: 0;">Register &amp; Provision Tenant</h3>
                    <p style="font-size: 0.725rem; color: var(--zinc-500); margin: 0;">Creates dedicated MySQL schema and boots CRM workspace</p>
                </div>
            </div>
            <button type="button" onclick="closeCreateTenantModal()" style="background: none; border: none; cursor: pointer; color: var(--zinc-400); padding: 0.25rem;">
                <i data-lucide="x" style="width: 18px; height: 18px;"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('admin.tenant_studio.store') }}" id="createTenantForm" onsubmit="handleCreateTenantSubmit(event)" style="padding: 1.25rem 1.5rem; display: flex; flex-direction: column; gap: 1.1rem;">
            @csrf

            <!-- Automated Provisioning Info Callout -->
            <div style="background: #f5f3ff; border: 1px solid #ddd6fe; border-radius: var(--radius-md); padding: 0.75rem 0.85rem; font-size: 0.78rem; color: #5b21b6; display: flex; align-items: flex-start; gap: 0.5rem; line-height: 1.45;">
                <i data-lucide="sparkles" style="width: 16px; height: 16px; flex-shrink: 0; margin-top: 2px;"></i>
                <div>
                    <strong>Automated Isolated Provisioning:</strong> Registering will automatically generate an isolated MySQL database (`crm_{name}_{hash}`), run all tenant migrations, seed roles, permissions, currencies, and initialize the primary administrator user.
                </div>
            </div>

            <!-- Organization Name -->
            <div class="form-group">
                <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--zinc-800); margin-bottom: 0.35rem;">
                    Organization / Company Name *
                </label>
                <input
                    type="text"
                    name="name"
                    id="new-tenant-name"
                    required
                    placeholder="e.g. Apex Global Logistics"
                    oninput="handleNameSlugInput(this.value)"
                    style="width: 100%; padding: 0.55rem 0.75rem; border-radius: 8px; border: 1px solid var(--zinc-300); font-size: 0.8125rem;"
                >
            </div>

            <!-- Domain / Subdomain & Legal Name -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                <div class="form-group">
                    <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--zinc-800); margin-bottom: 0.35rem;">
                        Domain / Subdomain Identifier *
                    </label>
                    <input
                        type="text"
                        name="domain"
                        id="new-tenant-domain"
                        required
                        placeholder="apex.crm.local"
                        style="width: 100%; padding: 0.55rem 0.75rem; border-radius: 8px; border: 1px solid var(--zinc-300); font-family: ui-monospace, monospace; font-size: 0.8rem; background: var(--zinc-50);"
                    >
                </div>
                <div class="form-group">
                    <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--zinc-800); margin-bottom: 0.35rem;">
                        Legal Business Name
                    </label>
                    <input
                        type="text"
                        name="legal_name"
                        id="new-tenant-legal-name"
                        placeholder="Apex Global Logistics Inc."
                        style="width: 100%; padding: 0.55rem 0.75rem; border-radius: 8px; border: 1px solid var(--zinc-300); font-size: 0.8125rem;"
                    >
                </div>
            </div>

            <!-- Primary Tenant Administrator Account -->
            <div style="border: 1px solid #e2e8f0; border-radius: var(--radius-md); padding: 0.85rem; background: #f8fafc;">
                <div style="font-weight: 600; font-size: 0.8125rem; color: var(--zinc-900); margin-bottom: 0.6rem; display: flex; align-items: center; gap: 0.35rem;">
                    <i data-lucide="user-check" style="width: 15px; height: 15px; color: #4f46e5;"></i>
                    <span>Initial Tenant Administrator User</span>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.65rem;">
                    <div>
                        <label style="display: block; font-size: 0.725rem; font-weight: 600; color: var(--zinc-700); margin-bottom: 0.25rem;">Admin Full Name</label>
                        <input
                            type="text"
                            name="admin_name"
                            value="System Administrator"
                            style="width: 100%; padding: 0.45rem 0.65rem; border-radius: 6px; border: 1px solid var(--zinc-300); font-size: 0.8rem; background: #ffffff;"
                        >
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.725rem; font-weight: 600; color: var(--zinc-700); margin-bottom: 0.25rem;">Admin Email Address *</label>
                        <input
                            type="email"
                            name="admin_email"
                            id="new-tenant-email"
                            required
                            placeholder="admin@apex.com"
                            style="width: 100%; padding: 0.45rem 0.65rem; border-radius: 6px; border: 1px solid var(--zinc-300); font-size: 0.8rem; background: #ffffff;"
                        >
                    </div>
                </div>

                <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                        <label style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-700);">Admin Password *</label>
                        <button
                            type="button"
                            onclick="generateRandomPassword()"
                            style="background: none; border: none; font-size: 0.7rem; color: #4f46e5; font-weight: 600; cursor: pointer; padding: 0;"
                        >
                            <i data-lucide="key" style="width: 11px; height: 11px; display: inline;"></i> Generate Strong Password
                        </button>
                    </div>
                    <div style="position: relative;">
                        <input
                            type="text"
                            name="admin_password"
                            id="new-tenant-password"
                            required
                            value="AdminPass@2026!"
                            style="width: 100%; padding: 0.45rem 0.65rem; border-radius: 6px; border: 1px solid var(--zinc-300); font-size: 0.8rem; font-family: ui-monospace, monospace; background: #ffffff;"
                        >
                    </div>
                </div>
            </div>

            <!-- Plan Tier & Max User Seats -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                <div class="form-group">
                    <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--zinc-800); margin-bottom: 0.35rem;">Plan Tier</label>
                    <select
                        name="plan_tier"
                        class="form-select"
                        style="width: 100%; padding: 0.5rem 0.75rem; border-radius: 8px; border: 1px solid var(--zinc-300); font-size: 0.8125rem; background: #ffffff;"
                    >
                        <option value="enterprise" selected>Enterprise (Unlimited Objects &amp; Rules)</option>
                        <option value="professional">Professional (Standard Sales Engine)</option>
                        <option value="starter">Starter (Basic CRM)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--zinc-800); margin-bottom: 0.35rem;">Max Allowed Seats</label>
                    <input
                        type="number"
                        name="max_seats"
                        min="1"
                        max="500"
                        value="15"
                        style="width: 100%; padding: 0.5rem 0.75rem; border-radius: 8px; border: 1px solid var(--zinc-300); font-size: 0.8125rem;"
                    >
                </div>
            </div>

            <!-- Branding Theme & Accent Color -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                <div class="form-group">
                    <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--zinc-800); margin-bottom: 0.35rem;">Branding Theme</label>
                    <select
                        name="branding_theme"
                        class="form-select"
                        style="width: 100%; padding: 0.5rem 0.75rem; border-radius: 8px; border: 1px solid var(--zinc-300); font-size: 0.8125rem; background: #ffffff;"
                    >
                        <option value="apple" selected>Apple Style (Rounded, clean typography)</option>
                        <option value="revolut">Revolut Style (Sharp high-contrast cards)</option>
                        <option value="minimal">Minimalist Monochrome</option>
                    </select>
                </div>

                <div class="form-group">
                    <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--zinc-800); margin-bottom: 0.35rem;">Primary Accent Color</label>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <input
                            type="color"
                            name="accent_color"
                            id="new-tenant-accent-color"
                            value="#4F46E5"
                            style="width: 38px; height: 36px; padding: 0; border: 1px solid var(--zinc-300); border-radius: 6px; cursor: pointer;"
                            onchange="document.getElementById('new-accent-text').value = this.value"
                        >
                        <input
                            type="text"
                            id="new-accent-text"
                            value="#4F46E5"
                            style="flex: 1; padding: 0.45rem 0.65rem; border-radius: 6px; border: 1px solid var(--zinc-300); font-size: 0.8rem; font-family: ui-monospace, monospace;"
                            oninput="document.getElementById('new-tenant-accent-color').value = this.value"
                        >
                    </div>
                </div>
            </div>

            <!-- Footer Actions -->
            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 0.65rem; padding-top: 0.85rem; border-top: 1px solid var(--zinc-200);">
                <button type="button" onclick="closeCreateTenantModal()" class="btn btn-secondary btn-sm">
                    Cancel
                </button>
                <button
                    type="submit"
                    id="submitCreateTenantBtn"
                    class="btn btn-primary btn-sm"
                    style="background: #4f46e5; border-color: #4338ca; display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.5rem 1rem; font-weight: 600;"
                >
                    <i data-lucide="sparkles" style="width: 14px; height: 14px;"></i>
                    <span id="submitCreateTenantText">Register &amp; Provision Tenant</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ==================== EDIT TENANT MODAL ==================== -->
<div id="edit-tenant-modal" class="modal-overlay">
    <div class="modal-card" style="max-width: 520px; width: 95%;">
        <div style="padding: 1.15rem 1.5rem; border-bottom: 1px solid var(--zinc-200); display: flex; align-items: center; justify-content: space-between; background: #ffffff;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <i data-lucide="edit-2" style="width: 16px; height: 16px; color: #4f46e5;"></i>
                <h3 id="edit-modal-title" style="font-size: 0.95rem; font-weight: 700; color: var(--zinc-900); margin: 0;">Edit Tenant Organization</h3>
            </div>
            <button type="button" onclick="closeEditTenantModal()" style="background: none; border: none; cursor: pointer; color: var(--zinc-400); padding: 0.25rem;">
                <i data-lucide="x" style="width: 18px; height: 18px;"></i>
            </button>
        </div>

        <form id="editTenantForm" method="POST" action="" style="padding: 1.25rem 1.5rem; display: flex; flex-direction: column; gap: 1rem;">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--zinc-800); margin-bottom: 0.35rem;">Organization Name *</label>
                <input
                    type="text"
                    name="name"
                    id="edit-tenant-name"
                    required
                    style="width: 100%; padding: 0.5rem 0.75rem; border-radius: 8px; border: 1px solid var(--zinc-300); font-size: 0.8125rem;"
                >
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                <div class="form-group">
                    <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--zinc-800); margin-bottom: 0.35rem;">Domain / Subdomain *</label>
                    <input
                        type="text"
                        name="domain"
                        id="edit-tenant-domain"
                        required
                        style="width: 100%; padding: 0.5rem 0.75rem; border-radius: 8px; border: 1px solid var(--zinc-300); font-family: ui-monospace, monospace; font-size: 0.8rem;"
                    >
                </div>
                <div class="form-group">
                    <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--zinc-800); margin-bottom: 0.35rem;">Legal Name</label>
                    <input
                        type="text"
                        name="legal_name"
                        id="edit-tenant-legal"
                        style="width: 100%; padding: 0.5rem 0.75rem; border-radius: 8px; border: 1px solid var(--zinc-300); font-size: 0.8125rem;"
                    >
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                <div class="form-group">
                    <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--zinc-800); margin-bottom: 0.35rem;">Plan Tier</label>
                    <select
                        name="plan_tier"
                        id="edit-tenant-tier"
                        class="form-select"
                        style="width: 100%; padding: 0.5rem 0.75rem; border-radius: 8px; border: 1px solid var(--zinc-300); font-size: 0.8125rem; background: #ffffff;"
                    >
                        <option value="enterprise">Enterprise</option>
                        <option value="professional">Professional</option>
                        <option value="starter">Starter</option>
                    </select>
                </div>
                <div class="form-group">
                    <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--zinc-800); margin-bottom: 0.35rem;">Max User Seats</label>
                    <input
                        type="number"
                        name="max_seats"
                        id="edit-tenant-seats"
                        min="1"
                        max="500"
                        style="width: 100%; padding: 0.5rem 0.75rem; border-radius: 8px; border: 1px solid var(--zinc-300); font-size: 0.8125rem;"
                    >
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                <div class="form-group">
                    <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--zinc-800); margin-bottom: 0.35rem;">Branding Theme</label>
                    <select
                        name="branding_theme"
                        id="edit-tenant-theme"
                        class="form-select"
                        style="width: 100%; padding: 0.5rem 0.75rem; border-radius: 8px; border: 1px solid var(--zinc-300); font-size: 0.8125rem; background: #ffffff;"
                    >
                        <option value="apple">Apple Style</option>
                        <option value="revolut">Revolut Style</option>
                        <option value="minimal">Minimal</option>
                    </select>
                </div>
                <div class="form-group">
                    <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--zinc-800); margin-bottom: 0.35rem;">Accent Color</label>
                    <div style="display: flex; align-items: center; gap: 0.4rem;">
                        <input
                            type="color"
                            id="edit-tenant-accent-picker"
                            style="width: 36px; height: 34px; padding: 0; border: 1px solid var(--zinc-300); border-radius: 6px; cursor: pointer;"
                            onchange="document.getElementById('edit-tenant-accent').value = this.value"
                        >
                        <input
                            type="text"
                            name="accent_color"
                            id="edit-tenant-accent"
                            style="flex: 1; padding: 0.45rem 0.65rem; border-radius: 6px; border: 1px solid var(--zinc-300); font-size: 0.8rem; font-family: ui-monospace, monospace;"
                            oninput="document.getElementById('edit-tenant-accent-picker').value = this.value"
                        >
                    </div>
                </div>
            </div>

            <div class="form-group" style="padding-top: 0.35rem;">
                <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.8125rem; font-weight: 500; color: var(--zinc-800); cursor: pointer;">
                    <input
                        type="checkbox"
                        name="is_active"
                        id="edit-tenant-active"
                        value="1"
                        style="width: 16px; height: 16px; accent-color: #4f46e5; cursor: pointer;"
                    >
                    <span>Organization is active &amp; accessible by members</span>
                </label>
            </div>

            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 0.65rem; padding-top: 0.75rem; border-top: 1px solid var(--zinc-200);">
                <button type="button" onclick="closeEditTenantModal()" class="btn btn-secondary btn-sm">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary btn-sm" style="background: #4f46e5; border-color: #4338ca;">
                    Save Organization Changes
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ==================== DELETE TENANT MODAL ==================== -->
<div id="delete-tenant-modal" class="modal-overlay">
    <div class="modal-card" style="max-width: 440px; width: 95%;">
        <div style="padding: 1.15rem 1.5rem; border-bottom: 1px solid var(--zinc-200); display: flex; align-items: center; justify-content: space-between; background: #ffffff;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <i data-lucide="alert-octagon" style="width: 18px; height: 18px; color: #ef4444;"></i>
                <h3 style="font-size: 0.95rem; font-weight: 700; color: var(--zinc-900); margin: 0;">Confirm Tenant Removal</h3>
            </div>
            <button type="button" onclick="closeDeleteTenantModal()" style="background: none; border: none; cursor: pointer; color: var(--zinc-400); padding: 0.25rem;">
                <i data-lucide="x" style="width: 18px; height: 18px;"></i>
            </button>
        </div>

        <form id="deleteTenantForm" method="POST" action="" style="padding: 1.25rem 1.5rem; display: flex; flex-direction: column; gap: 1rem;">
            @csrf
            @method('DELETE')

            <p style="font-size: 0.8125rem; color: var(--zinc-600); line-height: 1.5; margin: 0;">
                Are you sure you want to remove <strong id="delete-tenant-name-span" style="color: var(--zinc-950);"></strong>? This will revoke access for all members under this organization.
            </p>

            <div style="background: #fef2f2; border: 1px solid #fee2e2; border-radius: var(--radius-md); padding: 0.75rem; font-size: 0.78rem; color: #991b1b;">
                <label style="display: flex; align-items: flex-start; gap: 0.5rem; cursor: pointer;">
                    <input
                        type="checkbox"
                        name="drop_database"
                        value="1"
                        style="width: 16px; height: 16px; accent-color: #dc2626; cursor: pointer; margin-top: 2px;"
                    >
                    <span>
                        <strong>Also permanently DROP isolated MySQL database:</strong>
                        <code id="delete-tenant-db-span" style="font-size: 0.75rem; background: #fee2e2; padding: 0.1rem 0.3rem; border-radius: 4px; display: block; margin-top: 0.25rem;"></code>
                    </span>
                </label>
            </div>

            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 0.65rem; padding-top: 0.75rem; border-top: 1px solid var(--zinc-200);">
                <button type="button" onclick="closeDeleteTenantModal()" class="btn btn-secondary btn-sm">
                    Cancel
                </button>
                <button type="submit" class="btn btn-sm" style="background: #dc2626; color: #ffffff; border: none; font-weight: 600; padding: 0.45rem 0.85rem; border-radius: var(--radius-md); cursor: pointer;">
                    Yes, Delete Tenant
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Real-time table filter
    function filterTenants() {
        var q = (document.getElementById('tenant-search-input')?.value || '').toLowerCase().trim();
        var tierFilter = (document.getElementById('tenant-tier-filter')?.value || 'all').toLowerCase();
        var statusFilter = (document.getElementById('tenant-status-filter')?.value || 'all').toLowerCase();
        var rows = document.querySelectorAll('#tenants-table-body .tenant-row');
        var visibleCount = 0;

        rows.forEach(function(row) {
            var name = (row.getAttribute('data-name') || '');
            var domain = (row.getAttribute('data-domain') || '');
            var db = (row.getAttribute('data-db') || '');
            var tier = (row.getAttribute('data-tier') || '');
            var status = (row.getAttribute('data-status') || '');

            var matchesQuery = !q || name.indexOf(q) !== -1 || domain.indexOf(q) !== -1 || db.indexOf(q) !== -1;
            var matchesTier = (tierFilter === 'all') || (tier === tierFilter);
            var matchesStatus = (statusFilter === 'all') || (status === statusFilter);

            if (matchesQuery && matchesTier && matchesStatus) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        var countElem = document.getElementById('visible-tenant-count');
        if (countElem) countElem.textContent = visibleCount;
    }

    // Modal Helpers
    function openCreateTenantModal() {
        var modal = document.getElementById('create-tenant-modal');
        if (modal) modal.classList.add('is-open');
    }

    function closeCreateTenantModal() {
        var modal = document.getElementById('create-tenant-modal');
        if (modal) modal.classList.remove('is-open');
    }

    function handleNameSlugInput(val) {
        var slug = val.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
        var domainInput = document.getElementById('new-tenant-domain');
        var emailInput = document.getElementById('new-tenant-email');
        if (domainInput && slug) {
            domainInput.value = slug + '.dealsync.local';
        }
        if (emailInput && slug) {
            emailInput.value = 'admin@' + slug + '.com';
        }
    }

    function generateRandomPassword() {
        var chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789!@#$%&*';
        var pwd = '';
        for (var i = 0; i < 14; i++) {
            pwd += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        document.getElementById('new-tenant-password').value = pwd;
    }

    function handleCreateTenantSubmit(e) {
        var btn = document.getElementById('submitCreateTenantBtn');
        var text = document.getElementById('submitCreateTenantText');
        if (btn) btn.disabled = true;
        if (text) text.innerHTML = '<span class="spinner-border spinner-border-sm" style="display:inline-block; width:12px; height:12px; border:2px solid #fff; border-top-color:transparent; border-radius:50%; animation:spin 0.6s linear infinite; margin-right:6px;"></span> Provisioning MySQL DB &amp; Migrations...';
    }

    // Edit Modal
    function openEditTenantModal(tenant) {
        var modal = document.getElementById('edit-tenant-modal');
        var form = document.getElementById('editTenantForm');
        var title = document.getElementById('edit-modal-title');

        if (title) title.textContent = 'Edit Tenant: ' + tenant.name;
        if (form) form.action = '{{ url("/admin/tenant-studio") }}/' + tenant.id;

        document.getElementById('edit-tenant-name').value = tenant.name || '';
        document.getElementById('edit-tenant-domain').value = tenant.domain || '';
        document.getElementById('edit-tenant-legal').value = tenant.legal_name || '';
        document.getElementById('edit-tenant-tier').value = tenant.plan_tier || 'enterprise';
        document.getElementById('edit-tenant-seats').value = tenant.max_seats || 15;
        document.getElementById('edit-tenant-theme').value = tenant.branding_theme || 'apple';
        
        var accent = tenant.accent_color || '#4F46E5';
        document.getElementById('edit-tenant-accent').value = accent;
        document.getElementById('edit-tenant-accent-picker').value = accent;

        document.getElementById('edit-tenant-active').checked = Boolean(tenant.is_active);

        if (modal) modal.classList.add('is-open');
    }

    function closeEditTenantModal() {
        var modal = document.getElementById('edit-tenant-modal');
        if (modal) modal.classList.remove('is-open');
    }

    // Delete Modal
    function openDeleteTenantModal(id, name, dbName) {
        var modal = document.getElementById('delete-tenant-modal');
        var form = document.getElementById('deleteTenantForm');
        
        if (form) form.action = '{{ url("/admin/tenant-studio") }}/' + id;
        document.getElementById('delete-tenant-name-span').textContent = name;
        document.getElementById('delete-tenant-db-span').textContent = dbName;

        if (modal) modal.classList.add('is-open');
    }

    function closeDeleteTenantModal() {
        var modal = document.getElementById('delete-tenant-modal');
        if (modal) modal.classList.remove('is-open');
    }
</script>
@endsection
