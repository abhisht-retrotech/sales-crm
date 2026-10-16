@extends('layouts.app')

@section('title', 'Tenant Management Studio — DealSync Admin')

@section('content')
<!-- Centered Max-Width Container matching React Layout with generous left/right margins -->
<div class="max-w-[1240px] mx-auto w-full px-4 sm:px-6 lg:px-8 py-5 space-y-5 animate-fade-in">

    <!-- Header Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <div class="flex items-center gap-2">
                <h2 class="tenant-title text-xl font-bold tracking-tight m-0" style="color: var(--text-primary, #09090b);">
                    Tenant Management Studio
                </h2>
                <span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2.5 py-0.5 rounded-full bg-purple-50 text-purple-700 border border-purple-200 dark:bg-purple-950/40 dark:text-purple-300 dark:border-purple-800">
                    Master Tenant Only
                </span>
            </div>
            <p class="text-[13px] mt-1 mb-0 opacity-75" style="color: var(--text-primary, #09090b);">
                Provision client organizations with dedicated isolated databases, configure custom branding, and manage multi-tenant access.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <button
                type="button"
                onclick="window.location.reload();"
                class="inline-flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-lg border shadow-sm transition-colors cursor-pointer"
                style="background-color: var(--table-bg, #ffffff); border-color: var(--table-border, #e2e8f0); color: var(--text-primary, #09090b);"
                title="Refresh tenant directory"
            >
                <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                <span>Refresh</span>
            </button>

            <button
                type="button"
                onclick="openCreateTenantModal()"
                class="inline-flex items-center gap-1.5 text-xs font-semibold px-3.5 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white border border-indigo-700 shadow-sm transition-colors cursor-pointer"
            >
                <i data-lucide="plus" class="w-3.5 h-3.5 stroke-[2.5]"></i>
                <span>Register New Tenant</span>
            </button>
        </div>
    </div>

    <!-- Flash Alerts / Feedback Banner -->
    @if(session('success'))
        <div class="flex items-center justify-between gap-3 px-4 py-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-[13px] font-medium shadow-sm">
            <div class="flex items-center gap-2">
                <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-800 p-1 rounded-md transition-colors cursor-pointer">
                <i data-lucide="x" class="w-3.5 h-3.5"></i>
            </button>
        </div>
    @endif

    @if(session('error') || $errors->any())
        <div class="p-4 rounded-xl bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-300 text-[13px] font-medium shadow-sm space-y-2">
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 text-red-600 dark:text-red-400 shrink-0"></i>
                    <span class="font-semibold">{{ session('error') ?? 'Please resolve the following errors:' }}</span>
                </div>
                <button type="button" onclick="this.parentElement.parentElement.remove()" class="text-red-600 hover:text-red-800 p-1 rounded-md cursor-pointer">
                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                </button>
            </div>
            @if($errors->any())
                <ul class="list-disc list-inside text-xs space-y-0.5 pl-4 text-red-700 dark:text-red-400">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endif

    <!-- Directory Controls -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="relative w-full sm:w-80">
            <i data-lucide="search" class="w-3.5 h-3.5 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
            <input
                type="text"
                id="tenant-search-input"
                oninput="filterTenants()"
                placeholder="Search tenant name or ID..."
                class="w-full pl-9 pr-3 py-1.5 h-9 text-xs rounded-lg border focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-sm transition-all"
                style="background-color: var(--table-bg, #ffffff); border-color: var(--table-border, #e2e8f0); color: var(--text-primary, #09090b);"
            >
        </div>

        <div class="text-xs font-medium opacity-80" style="color: var(--text-primary, #09090b);">
            Showing <strong id="visible-tenant-count" class="font-bold">{{ $tenants->count() }}</strong> of <strong class="font-bold">{{ $tenants->count() }}</strong> registered {{ $tenants->count() === 1 ? 'tenant' : 'tenants' }}
        </div>
    </div>

    <!-- Tenants Data Table (React-Matched 6-Column Compact Clean Card) -->
    <div class="card card-table-wrapper rounded-xl shadow-sm overflow-hidden" style="background-color: var(--table-bg, #ffffff); border: 1px solid var(--table-border, #e2e8f0); color: var(--text-primary, #09090b);">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[840px] data-table" id="tenants-table">
                <thead>
                    <tr class="border-b text-[11px] font-semibold uppercase tracking-wider" style="background-color: var(--table-bg, #ffffff); border-bottom-color: var(--table-border, #e2e8f0); color: var(--text-secondary, #64748b);">
                        <th class="py-3 px-4" style="border-bottom-color: var(--table-border, #e2e8f0); border-right-color: var(--table-border, #e2e8f0);">Tenant Organization</th>
                        <th class="py-3 px-3.5" style="border-bottom-color: var(--table-border, #e2e8f0); border-right-color: var(--table-border, #e2e8f0);">Tenant ID</th>
                        <th class="py-3 px-3.5" style="border-bottom-color: var(--table-border, #e2e8f0); border-right-color: var(--table-border, #e2e8f0);">Users</th>
                        <th class="py-3 px-3.5" style="border-bottom-color: var(--table-border, #e2e8f0); border-right-color: var(--table-border, #e2e8f0);">Provisioned Date</th>
                        <th class="py-3 px-3.5" style="border-bottom-color: var(--table-border, #e2e8f0); border-right-color: var(--table-border, #e2e8f0);">Type</th>
                        <th class="py-3 px-4 text-right w-52" style="border-bottom-color: var(--table-border, #e2e8f0);">Actions</th>
                    </tr>
                </thead>
                <tbody id="tenants-table-body" class="text-xs" style="color: var(--text-primary, #09090b);">
                    @forelse($tenants as $t)
                        @php
                            $isActiveWorkspace = isset($currentTenant) && $currentTenant->id === $t->id;
                            $isMaster = (bool)($t->is_master ?? false) || $t->id === 1 || in_array(strtolower($t->domain), ['master', 'tenant-acme-corp', 'dealsync.local']);
                            $initials = strtoupper(substr($t->name, 0, 2));
                        @endphp
                        <tr class="tenant-row transition-colors hover:bg-zinc-50/50 dark:hover:bg-zinc-800/40 {{ $isActiveWorkspace ? 'bg-indigo-50/20' : '' }}" style="border-bottom: 1px solid var(--table-border, #e2e8f0); color: var(--text-primary, #09090b);" data-name="{{ strtolower($t->name) }}" data-id="{{ strtolower($t->domain) }}">
                            <!-- Column 1: Organization & Logo -->
                            <td class="py-3 px-4" style="border-bottom-color: var(--table-border, #e2e8f0);">
                                <div class="flex items-center gap-3">
                                    @if(!empty($t->logo_url))
                                        <img
                                            src="{{ $t->logo_url }}"
                                            alt="{{ $t->name }} Logo"
                                            class="w-9 h-9 rounded-lg object-contain bg-white dark:bg-zinc-800 border p-0.5 shadow-sm shrink-0"
                                            style="border-color: var(--table-border, #e2e8f0);"
                                        />
                                    @else
                                        <div
                                            class="w-9 h-9 rounded-lg flex items-center justify-center font-bold text-xs shrink-0 border shadow-sm {{ $isMaster ? 'bg-gradient-to-br from-indigo-500 to-purple-600 text-white' : 'bg-zinc-100 dark:bg-zinc-800' }}"
                                            style="border-color: var(--table-border, #e2e8f0); {{ !$isMaster ? 'color: var(--text-primary, #09090b);' : '' }}"
                                        >
                                            {{ $initials }}
                                        </div>
                                    @endif

                                    <div>
                                        <div class="flex items-center gap-1.5 font-semibold text-xs" style="color: var(--text-primary, #09090b);">
                                            <span class="tenant-title font-semibold text-xs" style="color: var(--text-primary, #09090b);">{{ $t->name }}</span>
                                            @if($isActiveWorkspace)
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                                    Active Workspace
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-[11px] opacity-70 mt-0.5" style="color: var(--text-primary, #09090b);">
                                            @if(!empty($t->logo_url))
                                                <span class="text-indigo-600 dark:text-indigo-400 font-medium inline-flex items-center gap-1">✨ Custom Logo Set</span>
                                            @else
                                                Default Brand Icon
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Column 2: Tenant ID -->
                            <td class="py-3 px-3.5" style="border-bottom-color: var(--table-border, #e2e8f0);">
                                <span class="font-mono text-xs px-2 py-0.5 rounded border" style="background-color: var(--table-bg, #ffffff); border-color: var(--table-border, #e2e8f0); color: var(--text-primary, #09090b);">
                                    {{ $t->domain }}
                                </span>
                            </td>

                            <!-- Column 3: Users -->
                            <td class="py-3 px-3.5" style="border-bottom-color: var(--table-border, #e2e8f0);">
                                <span class="inline-flex items-center gap-1 text-xs px-2.5 py-0.5 rounded-full border font-medium" style="background-color: var(--table-bg, #ffffff); border-color: var(--table-border, #e2e8f0); color: var(--text-primary, #09090b);">
                                    <i data-lucide="users" class="w-3 h-3 opacity-60"></i>
                                    <span>{{ $t->max_seats ?: 1 }} users</span>
                                </span>
                            </td>

                            <!-- Column 4: Provisioned Date -->
                            <td class="py-3 px-3.5 text-xs opacity-80" style="border-bottom-color: var(--table-border, #e2e8f0); color: var(--text-primary, #09090b);">
                                <div class="flex items-center gap-1.5">
                                    <i data-lucide="calendar" class="w-3 h-3 opacity-60"></i>
                                    <span>{{ $t->created_at ? $t->created_at->format('n/j/Y') : 'Initial' }}</span>
                                </div>
                            </td>

                            <!-- Column 5: Type -->
                            <td class="py-3 px-3.5" style="border-bottom-color: var(--table-border, #e2e8f0);">
                                @if($isMaster)
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2 py-0.5 rounded-full bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800">
                                        👑 Master Tenant
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[11px] font-medium px-2 py-0.5 rounded-full bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                        Client Org
                                    </span>
                                @endif
                            </td>

                            <!-- Column 6: Actions -->
                            <td class="py-3 px-4 text-right w-52" style="border-bottom-color: var(--table-border, #e2e8f0);">
                                <div class="inline-flex items-center justify-end gap-1.5">
                                    <button
                                        type="button"
                                        onclick="openEditTenantModal({{ json_encode($t) }})"
                                        class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-md border shadow-sm transition-colors cursor-pointer"
                                        style="background-color: var(--table-bg, #ffffff); border-color: var(--table-border, #e2e8f0); color: var(--text-primary, #09090b);"
                                        title="Edit Company Name and Logo"
                                    >
                                        <i data-lucide="edit-2" class="w-3 h-3"></i>
                                        <span>Edit Branding</span>
                                    </button>

                                    @if(!$isActiveWorkspace)
                                        <form method="POST" action="{{ route('admin.tenant_studio.switch', $t->id) }}" class="m-0 inline-block">
                                            @csrf
                                            <button
                                                type="submit"
                                                class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-md border shadow-sm transition-colors cursor-pointer opacity-90 hover:opacity-100"
                                                style="background-color: var(--table-bg, #ffffff); border-color: var(--table-border, #e2e8f0); color: var(--text-primary, #09090b);"
                                                title="Switch to {{ $t->name }} workspace"
                                            >
                                                <i data-lucide="external-link" class="w-3 h-3"></i>
                                                <span>Switch</span>
                                            </button>
                                        </form>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-1 text-emerald-700 dark:text-emerald-400">
                                            <i data-lucide="check" class="w-3 h-3"></i> Current
                                        </span>
                                    @endif

                                    <button
                                        type="button"
                                        onclick="openDeleteTenantModal({{ $t->id }}, '{{ addslashes($t->name) }}', '{{ $t->db_name }}')"
                                        class="inline-flex items-center justify-center p-1 text-zinc-400 hover:text-red-600 rounded transition-colors cursor-pointer"
                                        title="Delete Tenant Organization"
                                    >
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-xs opacity-60" style="color: var(--text-primary, #09090b);">
                                No tenant organizations match your search query.
                            </td>
                        </tr>
                    @endforelse

                    <!-- Filter Match Empty Row -->
                    <tr id="no-filter-match-row" style="display: none;">
                        <td colspan="6" class="py-10 text-center text-xs opacity-60" style="color: var(--text-primary, #09090b);">
                            No tenant organizations match your search query.
                        </td>
                    </tr>
               </tbody>
           </table>
       </div>
   </div>

</div>

<!-- ==================== REGISTER & PROVISION NEW TENANT MODAL ==================== -->
<div id="create-tenant-modal" class="modal-overlay">
    <div class="rounded-2xl shadow-2xl border max-w-lg w-full max-h-[90vh] overflow-y-auto" style="background-color: var(--table-bg, #ffffff); border-color: var(--table-border, #e2e8f0); color: var(--text-primary, #09090b);">
        <!-- Header -->
        <div class="px-6 py-4 border-b flex items-center justify-between sticky top-0 backdrop-blur z-10" style="background-color: var(--table-bg, #ffffff); border-color: var(--table-border, #e2e8f0); color: var(--text-primary, #09090b);">
            <div class="flex items-center gap-2">
                <i data-lucide="building-2" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"></i>
                <h3 class="text-sm font-bold m-0" style="color: var(--text-primary, #09090b);">Register New Tenant Organization</h3>
            </div>
            <button type="button" onclick="closeCreateTenantModal()" class="text-zinc-400 hover:text-zinc-600 p-1 rounded-md cursor-pointer transition-colors">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('admin.tenant_studio.store') }}" id="createTenantForm" onsubmit="handleCreateTenantSubmit(event)">
            @csrf

            <div class="p-6 space-y-4">
                <!-- Automated Provisioning Info Callout -->
                <div class="bg-purple-50 dark:bg-purple-950/40 border border-purple-200 dark:border-purple-800 rounded-xl p-3 text-xs text-purple-900 dark:text-purple-200 flex items-start gap-2.5 leading-relaxed">
                    <i data-lucide="sparkles" class="w-4 h-4 text-purple-600 dark:text-purple-400 shrink-0 mt-0.5"></i>
                    <div>
                        <strong>Automated Organization Provisioning:</strong> Registering this company entity will automatically generate its isolated database workspace, seed default Security Profiles (System Administrator, Sales, Procurement), baseline Roles, default Page Layouts, and create its initial Administrator user.
                    </div>
                </div>

                <!-- Organization Name -->
                <div>
                    <label class="block text-xs font-semibold text-zinc-800 dark:text-zinc-200 mb-1">
                        Company / Organization Entity Name *
                    </label>
                    <input
                        type="text"
                        name="name"
                        id="new-tenant-name"
                        required
                        placeholder="e.g. Stark Industries or Apex Logistics"
                        oninput="handleNameSlugInput(this.value)"
                        class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-sm"
                    >
                </div>

                <!-- Unique Tenant ID -->
                <div>
                    <label class="block text-xs font-semibold text-zinc-800 dark:text-zinc-200 mb-1">
                        <span>Unique Tenant ID (System Identifier)</span>
                        <span class="text-[11px] text-zinc-400 font-normal ml-1">(Auto-generated, editable)</span>
                    </label>
                    <input
                        type="text"
                        name="domain"
                        id="new-tenant-domain"
                        required
                        placeholder="tenant-company-name"
                        class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/80 text-zinc-900 dark:text-white font-mono placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-sm"
                    >
                </div>

                <!-- Company Logo (Branding) -->
                <div>
                    <label class="block text-xs font-semibold text-zinc-800 dark:text-zinc-200 mb-1">
                        Company Logo (Branding)
                    </label>

                    <div
                        onclick="document.getElementById('create-logo-file-input').click()"
                        class="border-2 border-dashed border-zinc-200 dark:border-zinc-700 hover:border-indigo-500 rounded-xl p-4 text-center bg-zinc-50 dark:bg-zinc-800/50 cursor-pointer transition-colors"
                    >
                        <input
                            type="file"
                            id="create-logo-file-input"
                            class="hidden"
                            accept="image/png,image/jpeg,image/svg+xml,image/webp"
                            onchange="handleLogoFileSelect(this, 'create')"
                        >

                        <div id="create-logo-preview-box" style="display: none;" class="flex-col items-center gap-2">
                            <img
                                id="create-logo-img-preview"
                                src=""
                                alt="Logo Preview"
                                class="max-h-16 max-w-[180px] object-contain rounded-md bg-white dark:bg-zinc-800 p-1 border border-zinc-200 dark:border-zinc-700"
                            >
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold">✓ Logo Selected</span>
                                <button
                                    type="button"
                                    onclick="event.stopPropagation(); removeLogo('create')"
                                    class="text-[11px] text-red-600 dark:text-red-400 hover:underline px-1.5 py-0.5 rounded cursor-pointer"
                                >
                                    Remove
                                </button>
                            </div>
                        </div>

                        <div id="create-logo-placeholder-box" class="flex flex-col items-center gap-1.5">
                            <div class="w-9 h-9 rounded-full bg-indigo-50 dark:bg-indigo-950/60 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                                <i data-lucide="upload" class="w-4 h-4"></i>
                            </div>
                            <div class="text-xs font-semibold text-zinc-800 dark:text-zinc-200">
                                Click to upload company logo
                            </div>
                            <div class="text-[11px] text-zinc-400">
                                PNG, JPG, SVG, or WebP (max 2 MB)
                            </div>
                        </div>
                    </div>

                    <!-- Alternative URL Input -->
                    <div class="mt-2 flex items-center gap-2">
                        <span class="text-[11px] text-zinc-400 whitespace-nowrap">or paste Image URL:</span>
                        <input
                            type="url"
                            id="new-tenant-logo-url-input"
                            placeholder="https://example.com/logo.png"
                            oninput="handleLogoUrlInput(this.value, 'create')"
                            class="flex-1 px-2.5 py-1 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white placeholder-zinc-400 focus:outline-none focus:ring-1 focus:ring-indigo-500 shadow-sm"
                        >
                    </div>

                    <input type="hidden" name="logo_url" id="new-tenant-logo-url">
                </div>

                <!-- Primary Administrator Details -->
                <div class="border-t border-zinc-100 dark:border-zinc-800 pt-3">
                    <div class="font-semibold text-xs text-zinc-900 dark:text-white mb-2 flex items-center gap-1.5">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400"></i>
                        <span>Primary Tenant Administrator</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                Admin Full Name *
                            </label>
                            <input
                                type="text"
                                name="admin_name"
                                required
                                value="John Smith"
                                placeholder="John Smith"
                                class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white shadow-sm"
                            >
                        </div>

                        <div>
                            <label class="block text-[11px] font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                Admin Email Address *
                            </label>
                            <input
                                type="email"
                                name="admin_email"
                                id="new-tenant-email"
                                required
                                placeholder="admin@company.com"
                                class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white shadow-sm"
                            >
                        </div>
                    </div>

                    <div class="mt-2">
                        <label class="block text-[11px] font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            Phone Number (Optional)
                        </label>
                        <input
                            type="text"
                            name="admin_phone"
                            placeholder="+1 (555) 019-2834"
                            class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white shadow-sm"
                        >
                    </div>
                </div>

                <!-- Hidden Defaults to Satisfy TenantProvisioner -->
                <input type="hidden" name="admin_password" value="AdminPass@2026!">
                <input type="hidden" name="plan_tier" value="enterprise">
                <input type="hidden" name="max_seats" value="15">
                <input type="hidden" name="branding_theme" value="apple">
                <input type="hidden" name="accent_color" value="#4F46E5">
            </div>

            <!-- Footer -->
            <div class="border-t px-6 py-3.5 flex items-center justify-end gap-2 rounded-b-2xl" style="background-color: var(--table-bg, #ffffff); border-color: var(--table-border, #e2e8f0);">
                <button
                    type="button"
                    onclick="closeCreateTenantModal()"
                    class="px-3.5 py-1.5 text-xs font-medium rounded-lg border shadow-sm transition-colors cursor-pointer"
                    style="background-color: var(--table-bg, #ffffff); border-color: var(--table-border, #e2e8f0); color: var(--text-primary, #09090b);"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    id="submitCreateTenantBtn"
                    class="inline-flex items-center gap-1.5 px-4 py-1.5 text-xs font-semibold rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white border border-indigo-700 shadow-sm transition-colors cursor-pointer"
                >
                    <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                    <span id="submitCreateTenantText">Register &amp; Provision Tenant</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ==================== EDIT TENANT BRANDING MODAL ==================== -->
<div id="edit-tenant-modal" class="modal-overlay">
    <div class="rounded-2xl shadow-2xl border max-w-md w-full" style="background-color: var(--table-bg, #ffffff); border-color: var(--table-border, #e2e8f0); color: var(--text-primary, #09090b);">
        <div class="px-6 py-4 border-b flex items-center justify-between" style="background-color: var(--table-bg, #ffffff); border-color: var(--table-border, #e2e8f0); color: var(--text-primary, #09090b);">
            <div class="flex items-center gap-2">
                <i data-lucide="edit-2" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"></i>
                <h3 id="edit-modal-title" class="text-sm font-bold m-0" style="color: var(--text-primary, #09090b);">Edit Company Branding</h3>
            </div>
            <button type="button" onclick="closeEditTenantModal()" class="text-zinc-400 hover:text-zinc-600 p-1 rounded-md cursor-pointer transition-colors">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form id="editTenantForm" method="POST" action="">
            @csrf
            @method('PUT')

            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-zinc-800 dark:text-zinc-200 mb-1">
                        Company / Organization Name *
                    </label>
                    <input
                        type="text"
                        name="name"
                        id="edit-tenant-name"
                        required
                        class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-sm"
                    >
                </div>

                <input type="hidden" name="domain" id="edit-tenant-domain">

                <div>
                    <label class="block text-xs font-semibold text-zinc-800 dark:text-zinc-200 mb-1">
                        Company Logo (Upload or Replace)
                    </label>

                    <div
                        onclick="document.getElementById('edit-logo-file-input').click()"
                        class="border-2 border-dashed border-zinc-200 dark:border-zinc-700 hover:border-indigo-500 rounded-xl p-4 text-center bg-zinc-50 dark:bg-zinc-800/50 cursor-pointer transition-colors"
                    >
                        <input
                            type="file"
                            id="edit-logo-file-input"
                            class="hidden"
                            accept="image/png,image/jpeg,image/svg+xml,image/webp"
                            onchange="handleLogoFileSelect(this, 'edit')"
                        >

                        <div id="edit-logo-preview-box" style="display: none;" class="flex-col items-center gap-2">
                            <img
                                id="edit-logo-img-preview"
                                src=""
                                alt="Logo Preview"
                                class="max-h-16 max-w-[180px] object-contain rounded-md bg-white dark:bg-zinc-800 p-1 border border-zinc-200 dark:border-zinc-700"
                            >
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold">Click to replace</span>
                                <button
                                    type="button"
                                    onclick="event.stopPropagation(); removeLogo('edit')"
                                    class="text-[11px] text-red-600 dark:text-red-400 hover:underline px-1.5 py-0.5 rounded cursor-pointer"
                                >
                                    Remove Logo
                                </button>
                            </div>
                        </div>

                        <div id="edit-logo-placeholder-box" class="flex flex-col items-center gap-1.5">
                            <div class="w-9 h-9 rounded-full bg-indigo-50 dark:bg-indigo-950/60 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                                <i data-lucide="upload" class="w-4 h-4"></i>
                            </div>
                            <div class="text-xs font-semibold text-zinc-800 dark:text-zinc-200">
                                Click to upload new logo
                            </div>
                            <div class="text-[11px] text-zinc-400">
                                PNG, JPG, SVG, or WebP
                            </div>
                        </div>
                    </div>

                    <!-- Alternative URL Input -->
                    <div class="mt-2 flex items-center gap-2">
                        <span class="text-[11px] text-zinc-400 whitespace-nowrap">or Image URL:</span>
                        <input
                            type="url"
                            id="edit-tenant-logo-url-input"
                            placeholder="https://example.com/logo.png"
                            oninput="handleLogoUrlInput(this.value, 'edit')"
                            class="flex-1 px-2.5 py-1 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white placeholder-zinc-400 focus:outline-none focus:ring-1 focus:ring-indigo-500 shadow-sm"
                        >
                    </div>

                    <input type="hidden" name="logo_url" id="edit-tenant-logo-url">
                </div>
            </div>

            <div class="border-t px-6 py-3.5 flex items-center justify-end gap-2 rounded-b-2xl" style="background-color: var(--table-bg, #ffffff); border-color: var(--table-border, #e2e8f0);">
                <button
                    type="button"
                    onclick="closeEditTenantModal()"
                    class="px-3.5 py-1.5 text-xs font-medium rounded-lg border shadow-sm transition-colors cursor-pointer"
                    style="background-color: var(--table-bg, #ffffff); border-color: var(--table-border, #e2e8f0); color: var(--text-primary, #09090b);"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    class="px-4 py-1.5 text-xs font-semibold rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white border border-indigo-700 shadow-sm transition-colors cursor-pointer"
                >
                    Save Branding Changes
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ==================== DELETE TENANT MODAL ==================== -->
<div id="delete-tenant-modal" class="modal-overlay">
    <div class="rounded-2xl shadow-2xl border max-w-sm w-full" style="background-color: var(--table-bg, #ffffff); border-color: var(--table-border, #e2e8f0); color: var(--text-primary, #09090b);">
        <div class="px-5 py-4 border-b flex items-center justify-between" style="background-color: var(--table-bg, #ffffff); border-color: var(--table-border, #e2e8f0); color: var(--text-primary, #09090b);">
            <div class="flex items-center gap-2">
                <i data-lucide="alert-octagon" class="w-4 h-4 text-red-600"></i>
                <h3 class="text-sm font-bold m-0" style="color: var(--text-primary, #09090b);">Confirm Tenant Removal</h3>
            </div>
            <button type="button" onclick="closeDeleteTenantModal()" class="text-zinc-400 hover:text-zinc-600 p-1 rounded-md cursor-pointer">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form id="deleteTenantForm" method="POST" action="" class="p-5 space-y-4">
            @csrf
            @method('DELETE')

            <p class="text-xs text-zinc-600 dark:text-zinc-400 leading-relaxed m-0">
                Are you sure you want to remove <strong id="delete-tenant-name-span" class="text-zinc-950 dark:text-white font-bold"></strong>? This will revoke access for all members under this organization.
            </p>

            <div class="bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800 rounded-xl p-3 text-xs text-red-800 dark:text-red-300">
                <label class="flex items-start gap-2 cursor-pointer">
                    <input
                        type="checkbox"
                        name="drop_database"
                        value="1"
                        class="w-4 h-4 accent-red-600 rounded cursor-pointer mt-0.5"
                    >
                    <span>
                        <strong>Also permanently DROP isolated MySQL database:</strong>
                        <code id="delete-tenant-db-span" class="text-[11px] bg-red-100 dark:bg-red-900/60 px-1 py-0.5 rounded font-mono block mt-1"></code>
                    </span>
                </label>
            </div>

            <div class="border-t pt-3 flex items-center justify-end gap-2" style="border-color: var(--table-border, #e2e8f0);">
                <button
                    type="button"
                    onclick="closeDeleteTenantModal()"
                    class="px-3 py-1.5 text-xs font-medium rounded-lg border shadow-sm transition-colors cursor-pointer"
                    style="background-color: var(--table-bg, #ffffff); border-color: var(--table-border, #e2e8f0); color: var(--text-primary, #09090b);"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    class="px-3.5 py-1.5 text-xs font-semibold rounded-lg bg-red-600 hover:bg-red-700 text-white border border-red-700 shadow-sm transition-colors cursor-pointer"
                >
                    Yes, Delete Tenant
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modular Tenant Studio JavaScript Configuration & Asset -->
<script>
    window.TENANT_STUDIO_CONFIG = {
        baseUrl: '{{ url("/admin/tenant-studio") }}',
        csrfToken: '{{ csrf_token() }}'
    };
    window.TENANT_STUDIO_ASSET_PATH = '{{ asset("js/tenant-studio.js") }}';
</script>
<script src="{{ asset('js/tenant-studio.js') }}?v=1.3"></script>
<script>
    if (typeof initTenantStudio === 'function') {
        initTenantStudio();
    }
</script>
@endsection
