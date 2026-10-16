@extends('layouts.app')

@section('title', 'Company Profile & Localization — DealSync Admin')

@section('content')
<!-- Centered Max-Width Container matching Modern DealSync Layout -->
<div class="max-w-[1240px] mx-auto w-full px-4 sm:px-6 lg:px-8 py-5 pb-16 space-y-6 animate-fade-in">

    <!-- Top Studio Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-zinc-200 dark:border-zinc-800">
        <div>
            <div class="flex items-center gap-2">
                <i data-lucide="building-2" class="w-5 h-5 text-indigo-600 dark:text-indigo-400 shrink-0"></i>
                <h2 class="text-xl font-bold tracking-tight m-0" style="color: var(--text-primary, #09090b);">
                    Company Profile &amp; Localization
                </h2>
            </div>
            <p class="text-[13px] mt-1 mb-0 opacity-75" style="color: var(--text-primary, #09090b);">
                Manage organization legal info, branding, headquarters address, regional localization, and multi-currency exchange rates.
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1 rounded-full bg-purple-50 text-purple-700 border border-purple-200 dark:bg-purple-950/40 dark:text-purple-300 dark:border-purple-800 shadow-2xs">
                Tenant: <strong id="header-tenant-id-badge" class="font-bold">{{ $companyProfile->name ?? ($tenant->name ?? 'Workspace') }}</strong>
            </span>
            <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1 rounded-full bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800 shadow-2xs">
                Base: <strong id="header-base-currency-badge" class="font-bold">{{ $companyProfile->currency_code ?? 'USD' }} ({{ $companyProfile->currency_symbol ?? '$' }})</strong>
            </span>
        </div>
    </div>

    <!-- Alert / Feedback Banner -->
    <div id="studio-feedback-banner" class="hidden items-center gap-2.5 px-4 py-3 rounded-xl text-xs font-semibold shadow-sm transition-all border">
        <i id="studio-feedback-icon" data-lucide="check-circle-2" class="w-4 h-4 shrink-0"></i>
        <span id="studio-feedback-text">Settings updated successfully!</span>
    </div>

    <!-- 4-Tab Segmented Navigation Header -->
    <div class="flex items-center gap-2 border-b border-zinc-200 dark:border-zinc-800 pb-1 overflow-x-auto">
        <button type="button" class="cp-tab-btn is-active inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold rounded-lg text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white hover:bg-zinc-100 dark:hover:bg-zinc-800 border-b-2 border-transparent transition-all cursor-pointer whitespace-nowrap [&.is-active]:text-indigo-600 [&.is-active]:border-indigo-600 dark:[&.is-active]:text-indigo-400 dark:[&.is-active]:border-indigo-400 [&.is-active]:bg-indigo-50/50 dark:[&.is-active]:bg-indigo-950/30" id="tab-nav-profile" onclick="switchCpTab('profile')">
            <i data-lucide="building-2" class="w-4 h-4"></i>
            <span>Company Profile &amp; Legal</span>
        </button>

        <button type="button" class="cp-tab-btn inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold rounded-lg text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white hover:bg-zinc-100 dark:hover:bg-zinc-800 border-b-2 border-transparent transition-all cursor-pointer whitespace-nowrap [&.is-active]:text-indigo-600 [&.is-active]:border-indigo-600 dark:[&.is-active]:text-indigo-400 dark:[&.is-active]:border-indigo-400 [&.is-active]:bg-indigo-50/50 dark:[&.is-active]:bg-indigo-950/30" id="tab-nav-regional" onclick="switchCpTab('regional')">
            <i data-lucide="globe" class="w-4 h-4"></i>
            <span>Regional &amp; Localization</span>
        </button>

        <button type="button" class="cp-tab-btn inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold rounded-lg text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white hover:bg-zinc-100 dark:hover:bg-zinc-800 border-b-2 border-transparent transition-all cursor-pointer whitespace-nowrap [&.is-active]:text-indigo-600 [&.is-active]:border-indigo-600 dark:[&.is-active]:text-indigo-400 dark:[&.is-active]:border-indigo-400 [&.is-active]:bg-indigo-50/50 dark:[&.is-active]:bg-indigo-950/30" id="tab-nav-currency" onclick="switchCpTab('currency')">
            <i data-lucide="coins" class="w-4 h-4"></i>
            <span>Multi-Currency &amp; Exchange Rates</span>
            <span id="tab-currencies-count" class="text-[11px] px-1.5 py-0.5 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 font-bold border border-zinc-200 dark:border-zinc-700">
                6
            </span>
        </button>

        <button type="button" class="cp-tab-btn inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold rounded-lg text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white hover:bg-zinc-100 dark:hover:bg-zinc-800 border-b-2 border-transparent transition-all cursor-pointer whitespace-nowrap [&.is-active]:text-indigo-600 [&.is-active]:border-indigo-600 dark:[&.is-active]:text-indigo-400 dark:[&.is-active]:border-indigo-400 [&.is-active]:bg-indigo-50/50 dark:[&.is-active]:bg-indigo-950/30" id="tab-nav-email" onclick="switchCpTab('email')">
            <i data-lucide="mail" class="w-4 h-4"></i>
            <span>Email &amp; Mailbox Sync Gateway</span>
            <span id="tab-smtp-status-badge" class="text-[11px] px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 font-semibold capitalize">
                verified
            </span>
        </button>
    </div>

    <!-- ============================================================== -->
    <!-- TAB 1: COMPANY PROFILE & LEGAL IDENTITY                        -->
    <!-- ============================================================== -->
    <div id="tab-pane-profile" class="flex flex-col gap-6">
        <form onsubmit="handleSaveOrganizationProfile(event)" class="flex flex-col gap-6">
            <!-- Box 1: Legal Entity & Corporate Identification -->
            <div class="card card-table-wrapper rounded-xl p-5 sm:p-6 shadow-sm border space-y-4" style="background-color: var(--table-bg, #ffffff); border-color: var(--table-border, #e2e8f0); color: var(--text-primary, #09090b);">
                <div>
                    <h3 class="text-sm font-bold flex items-center gap-2 m-0" style="color: var(--text-primary, #09090b);">
                        <i data-lucide="building-2" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"></i>
                        <span>Legal Entity &amp; Corporate Identification</span>
                    </h3>
                    <p class="text-xs mt-1 mb-0 opacity-75" style="color: var(--text-primary, #09090b);">
                        Official registered details used on tax invoices, purchase contracts, and regulatory filings.
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Organization Display Name *</label>
                        <input type="text" id="cp-input-name" required class="w-full h-9 px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-xs" placeholder="e.g. Acme Enterprise Tech">
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Legal / Corporate Registered Name</label>
                        <input type="text" id="cp-input-legal-name" class="w-full h-9 px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-xs" placeholder="e.g. Acme Corporation Global Inc.">
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Tax ID / VAT / GST Number</label>
                        <input type="text" id="cp-input-tax-id" class="w-full h-9 px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-xs" placeholder="e.g. US-EIN-987654321 or GB999999973">
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Company Registration / Business No.</label>
                        <input type="text" id="cp-input-reg-number" class="w-full h-9 px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-xs" placeholder="e.g. CR-09871234 or Delaware #71249">
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Industry / Vertical</label>
                        <input type="text" id="cp-input-industry" class="w-full h-9 px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-xs" placeholder="e.g. Enterprise Software &amp; Cloud">
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Corporate Website</label>
                        <input type="url" id="cp-input-website" class="w-full h-9 px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-xs" placeholder="https://example.com">
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Corporate Phone</label>
                        <input type="tel" id="cp-input-phone" class="w-full h-9 px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-xs" placeholder="+1 (555) 019-2834">
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Billing &amp; Finance Email</label>
                        <input type="email" id="cp-input-billing-email" class="w-full h-9 px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-xs" placeholder="billing@example.com">
                    </div>

                    <div class="space-y-1.5 sm:col-span-2 lg:col-span-1">
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Support &amp; Operations Email</label>
                        <input type="email" id="cp-input-support-email" class="w-full h-9 px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-xs" placeholder="support@example.com">
                    </div>
                </div>
            </div>

            <!-- Box 2: Headquarters Physical Address -->
            <div class="card card-table-wrapper rounded-xl p-5 sm:p-6 shadow-sm border space-y-4" style="background-color: var(--table-bg, #ffffff); border-color: var(--table-border, #e2e8f0); color: var(--text-primary, #09090b);">
                <div>
                    <h3 class="text-sm font-bold flex items-center gap-2 m-0" style="color: var(--text-primary, #09090b);">
                        <i data-lucide="map-pin" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"></i>
                        <span>Headquarters Physical Address</span>
                    </h3>
                    <p class="text-xs mt-1 mb-0 opacity-75" style="color: var(--text-primary, #09090b);">
                        Primary operating location printed on dispatch orders and billing invoices.
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="space-y-1.5 sm:col-span-2 lg:col-span-4">
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Street Address</label>
                        <input type="text" id="cp-input-address-street" class="w-full h-9 px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-xs" placeholder="100 Technology Park, Suite 400">
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">City</label>
                        <input type="text" id="cp-input-address-city" class="w-full h-9 px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-xs" placeholder="San Francisco">
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">State / Province</label>
                        <input type="text" id="cp-input-address-state" class="w-full h-9 px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-xs" placeholder="CA">
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Postal / Zip Code</label>
                        <input type="text" id="cp-input-address-postal" class="w-full h-9 px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-xs" placeholder="94107">
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Country</label>
                        <input type="text" id="cp-input-address-country" class="w-full h-9 px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-xs" placeholder="United States">
                    </div>
                </div>
            </div>

            <!-- Box 3: Brand Logo Upload Box -->
            <div class="card card-table-wrapper rounded-xl p-5 sm:p-6 shadow-sm border space-y-4" style="background-color: var(--table-bg, #ffffff); border-color: var(--table-border, #e2e8f0); color: var(--text-primary, #09090b);">
                <div>
                    <h3 class="text-sm font-bold flex items-center gap-2 m-0" style="color: var(--text-primary, #09090b);">
                        <i data-lucide="image" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"></i>
                        <span>Company Brand Logo</span>
                    </h3>
                    <p class="text-xs mt-1 mb-0 opacity-75" style="color: var(--text-primary, #09090b);">
                        Featured on the sidebar, top navigation, email receipts, and exported PDF invoices.
                    </p>
                </div>

                <div class="flex items-center gap-6 flex-wrap">
                    <div id="cp-logo-preview-box" class="w-24 h-24 rounded-xl border-2 border-dashed border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50 flex items-center justify-center relative overflow-hidden shrink-0 shadow-2xs">
                        <i id="cp-logo-placeholder-icon" data-lucide="building-2" class="w-7 h-7 text-zinc-400"></i>
                        <img id="cp-logo-img" src="" alt="Brand Logo" class="hidden w-full h-full object-contain p-1.5">
                        <button type="button" id="cp-logo-remove-btn" onclick="removeLogo()" class="hidden absolute top-1 right-1 bg-black/75 hover:bg-black text-white rounded-full w-5 h-5 text-[10px] cursor-pointer items-center justify-center transition-colors shadow-sm">✕</button>
                    </div>

                    <div class="flex-1 min-w-[260px] space-y-2.5">
                        <div>
                            <input type="file" id="cp-logo-file-input" onchange="handleLogoFileUpload(event)" accept="image/png,image/jpeg,image/svg+xml,image/webp" class="hidden">
                            <button type="button" onclick="document.getElementById('cp-logo-file-input').click()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg border shadow-sm transition-colors cursor-pointer" style="background-color: var(--table-bg, #ffffff); border-color: var(--table-border, #e2e8f0); color: var(--text-primary, #09090b);">
                                <i data-lucide="upload" class="w-3.5 h-3.5"></i>
                                <span>Upload Image File (PNG, SVG, JPG, WebP)</span>
                            </button>
                        </div>
                        <div>
                            <input type="url" id="cp-input-logo-url" oninput="updateLogoFromUrl(this.value)" class="w-full h-9 px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-xs" placeholder="Or paste public Logo Image URL (https://...)">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Box 4: Company Brand Colors & Theme Styling -->
            <div class="card card-table-wrapper rounded-xl p-5 sm:p-6 shadow-sm border space-y-4" style="background-color: var(--table-bg, #ffffff); border-color: var(--table-border, #e2e8f0); color: var(--text-primary, #09090b);">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-bold flex items-center gap-2 m-0" style="color: var(--text-primary, #09090b);">
                            <i data-lucide="palette" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"></i>
                            <span>Company Brand Colors &amp; Appearance</span>
                        </h3>
                        <p class="text-xs mt-1 mb-0 opacity-75" style="color: var(--text-primary, #09090b);">
                            Configure your company brand colors across the CRM. Change page canvas background, primary typography font color, top header navigation, and side panel navigation.
                        </p>
                    </div>

                    <button type="button" onclick="handleResetColorsToDefault()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg border shadow-sm transition-colors cursor-pointer shrink-0" style="background-color: var(--table-bg, #ffffff); border-color: var(--table-border, #e2e8f0); color: var(--text-primary, #09090b);" title="Restore default system colors">
                        <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                        <span>Restore Defaults</span>
                    </button>
                </div>

                <!-- 6 Core Brand & Surface Appearance Configuration Cards (100% Match React AppearanceSurfaceCard) -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                    
                    <!-- 1. Page Background -->
                    <div class="cp-surface-card rounded-xl p-4 border border-zinc-200 dark:border-zinc-700 bg-zinc-50/70 dark:bg-zinc-800/40 flex flex-col gap-3 shadow-2xs" id="surface-card-page">
                        <div class="flex justify-between items-center flex-wrap gap-2">
                            <label class="text-xs font-bold text-zinc-900 dark:text-zinc-100">Page Background</label>
                            <div class="flex items-center gap-2">
                                <span id="cp-label-page-bg" class="text-[11px] text-zinc-500 font-mono">#FAFAFA</span>
                                <div class="cp-mode-switch inline-flex bg-zinc-200/80 dark:bg-zinc-700/80 rounded-md p-0.5 gap-0.5">
                                    <button type="button" id="btn-mode-color-page" onclick="toggleSurfaceMode('page', 'color')" class="cp-surface-mode-btn inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-semibold rounded text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 transition-all cursor-pointer [&.is-active]:bg-white dark:[&.is-active]:bg-zinc-900 [&.is-active]:text-zinc-900 dark:[&.is-active]:text-white [&.is-active]:shadow-xs is-active">
                                        <i data-lucide="palette" class="w-3 h-3"></i> <span>Color</span>
                                    </button>
                                    <button type="button" id="btn-mode-image-page" onclick="toggleSurfaceMode('page', 'image')" class="cp-surface-mode-btn inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-semibold rounded text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 transition-all cursor-pointer [&.is-active]:bg-white dark:[&.is-active]:bg-zinc-900 [&.is-active]:text-zinc-900 dark:[&.is-active]:text-white [&.is-active]:shadow-xs">
                                        <i data-lucide="image" class="w-3 h-3"></i> <span>Image</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Color Mode Pane -->
                        <div id="pane-color-page" class="space-y-2">
                            <div class="flex items-center gap-2">
                                <input type="color" id="cp-color-page-bg" value="#fafafa" oninput="updatePageBg(this.value)" class="w-10 h-9 p-0.5 rounded-lg border border-zinc-300 dark:border-zinc-700 cursor-pointer bg-white dark:bg-zinc-800 shrink-0">
                                <input type="text" id="cp-text-page-bg" value="#fafafa" oninput="updatePageBg(this.value)" class="flex-1 h-9 px-3 py-1.5 text-xs font-mono rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-xs" placeholder="#FAFAFA">
                            </div>
                            <div class="flex gap-1.5 flex-wrap">
                                <button type="button" class="cp-color-preset-btn text-[11px] font-medium px-2 py-0.5 rounded border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-indigo-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all cursor-pointer shadow-2xs" onclick="updatePageBg('#fafafa')">Snow</button>
                                <button type="button" class="cp-color-preset-btn text-[11px] font-medium px-2 py-0.5 rounded border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-indigo-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all cursor-pointer shadow-2xs" onclick="updatePageBg('#ffffff')">White</button>
                                <button type="button" class="cp-color-preset-btn text-[11px] font-medium px-2 py-0.5 rounded border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-indigo-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all cursor-pointer shadow-2xs" onclick="updatePageBg('#fbfbfa')">Warm</button>
                                <button type="button" class="cp-color-preset-btn text-[11px] font-medium px-2 py-0.5 rounded border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-indigo-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all cursor-pointer shadow-2xs" onclick="updatePageBg('#f1f5f9')">Slate</button>
                                <button type="button" class="cp-color-preset-btn text-[11px] font-medium px-2 py-0.5 rounded border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-indigo-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all cursor-pointer shadow-2xs" onclick="updatePageBg('#0f172a')">Dark</button>
                            </div>
                        </div>

                        <!-- Image Mode Pane -->
                        <div id="pane-image-page" class="hidden flex-col gap-2">
                            <div class="flex gap-1.5 items-center">
                                <input type="text" id="cp-img-url-page" class="flex-1 h-8 px-2.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white placeholder-zinc-400 focus:outline-none focus:ring-1 focus:ring-indigo-500" placeholder="Paste Image URL..." oninput="updateSurfaceImage('page', { imageUrl: this.value })">
                                <input type="file" id="cp-file-page" accept="image/*" class="hidden" onchange="handleSurfaceFileUpload('page', event)">
                                <button type="button" onclick="document.getElementById('cp-file-page').click()" class="inline-flex items-center gap-1 h-8 px-2.5 text-xs font-medium rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 cursor-pointer transition-colors shadow-2xs shrink-0">
                                    <i data-lucide="upload" class="w-3 h-3"></i> <span>Upload</span>
                                </button>
                            </div>
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="text-[10px] text-zinc-500 font-semibold">Presets:</span>
                                <button type="button" class="cp-wp-preset-btn text-[10px] font-medium px-2 py-0.5 rounded border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-indigo-500 hover:text-indigo-600 transition-all cursor-pointer shadow-2xs" onclick="applyWallpaperPreset('page', 'Geometric')">Geometric</button>
                                <button type="button" class="cp-wp-preset-btn text-[10px] font-medium px-2 py-0.5 rounded border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-indigo-500 hover:text-indigo-600 transition-all cursor-pointer shadow-2xs" onclick="applyWallpaperPreset('page', 'Silk Wave')">Silk Wave</button>
                                <button type="button" class="cp-wp-preset-btn text-[10px] font-medium px-2 py-0.5 rounded border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-indigo-500 hover:text-indigo-600 transition-all cursor-pointer shadow-2xs" onclick="applyWallpaperPreset('page', 'Slate Mesh')">Slate Mesh</button>
                                <button type="button" class="cp-wp-preset-btn text-[10px] font-medium px-2 py-0.5 rounded border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-indigo-500 hover:text-indigo-600 transition-all cursor-pointer shadow-2xs" onclick="applyWallpaperPreset('page', 'Obsidian')">Obsidian</button>
                                <button type="button" class="cp-wp-preset-btn text-[10px] font-medium px-2 py-0.5 rounded border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-indigo-500 hover:text-indigo-600 transition-all cursor-pointer shadow-2xs" onclick="applyWallpaperPreset('page', 'Studio Clean')">Studio Clean</button>
                            </div>
                            <div class="grid grid-cols-2 gap-2 p-2 bg-white dark:bg-zinc-800/80 rounded-lg border border-zinc-200 dark:border-zinc-700 text-[10px]">
                                <div>
                                    <div class="flex justify-between text-zinc-600 dark:text-zinc-400 mb-1">
                                        <span class="font-semibold">Opacity</span>
                                        <span id="label-opacity-page" class="font-mono">100%</span>
                                    </div>
                                    <input type="range" min="10" max="100" value="100" id="slider-opacity-page" oninput="updateSurfaceImage('page', { opacity: Number(this.value) })" class="w-full h-1 bg-zinc-200 dark:bg-zinc-700 rounded-lg appearance-none cursor-pointer accent-indigo-600">
                                </div>
                                <div>
                                    <div class="flex justify-between text-zinc-600 dark:text-zinc-400 mb-1">
                                        <span class="font-semibold">Brightness</span>
                                        <span id="label-brightness-page" class="font-mono">100%</span>
                                    </div>
                                    <input type="range" min="50" max="150" value="100" id="slider-brightness-page" oninput="updateSurfaceImage('page', { brightness: Number(this.value) })" class="w-full h-1 bg-zinc-200 dark:bg-zinc-700 rounded-lg appearance-none cursor-pointer accent-indigo-600">
                                </div>
                                <div>
                                    <div class="flex justify-between text-zinc-600 dark:text-zinc-400 mb-1">
                                        <span class="font-semibold">Contrast</span>
                                        <span id="label-contrast-page" class="font-mono">100%</span>
                                    </div>
                                    <input type="range" min="50" max="150" value="100" id="slider-contrast-page" oninput="updateSurfaceImage('page', { contrast: Number(this.value) })" class="w-full h-1 bg-zinc-200 dark:bg-zinc-700 rounded-lg appearance-none cursor-pointer accent-indigo-600">
                                </div>
                                <div>
                                    <div class="flex justify-between text-zinc-600 dark:text-zinc-400 mb-1">
                                        <span class="font-semibold">Softness</span>
                                        <span id="label-softness-page" class="font-mono">0px</span>
                                    </div>
                                    <input type="range" min="0" max="20" value="0" id="slider-softness-page" oninput="updateSurfaceImage('page', { softness: Number(this.value) })" class="w-full h-1 bg-zinc-200 dark:bg-zinc-700 rounded-lg appearance-none cursor-pointer accent-indigo-600">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Font / Typography Color -->
                    <div class="cp-surface-card rounded-xl p-4 border border-zinc-200 dark:border-zinc-700 bg-zinc-50/70 dark:bg-zinc-800/40 flex flex-col gap-3 shadow-2xs">
                        <div class="flex justify-between items-center">
                            <label class="text-xs font-bold text-zinc-900 dark:text-zinc-100">Font / Text Color</label>
                            <span id="cp-label-font-color" class="text-[11px] text-zinc-500 font-mono">#09090B</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="color" id="cp-color-font-color" value="#09090b" oninput="updateFontColor(this.value)" class="w-10 h-9 p-0.5 rounded-lg border border-zinc-300 dark:border-zinc-700 cursor-pointer bg-white dark:bg-zinc-800 shrink-0">
                            <input type="text" id="cp-text-font-color" value="#09090b" oninput="updateFontColor(this.value)" class="flex-1 h-9 px-3 py-1.5 text-xs font-mono rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-xs" placeholder="#09090B">
                        </div>
                        <div class="flex gap-1.5 flex-wrap">
                            <button type="button" class="cp-color-preset-btn text-[11px] font-medium px-2 py-0.5 rounded border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-indigo-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all cursor-pointer shadow-2xs" onclick="updateFontColor('#09090b')">Obsidian</button>
                            <button type="button" class="cp-color-preset-btn text-[11px] font-medium px-2 py-0.5 rounded border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-indigo-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all cursor-pointer shadow-2xs" onclick="updateFontColor('#1e293b')">Charcoal</button>
                            <button type="button" class="cp-color-preset-btn text-[11px] font-medium px-2 py-0.5 rounded border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-indigo-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all cursor-pointer shadow-2xs" onclick="updateFontColor('#0f172a')">Navy</button>
                            <button type="button" class="cp-color-preset-btn text-[11px] font-medium px-2 py-0.5 rounded border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-indigo-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all cursor-pointer shadow-2xs" onclick="updateFontColor('#1e1b4b')">Indigo</button>
                            <button type="button" class="cp-color-preset-btn text-[11px] font-medium px-2 py-0.5 rounded border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-indigo-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all cursor-pointer shadow-2xs" onclick="updateFontColor('#f8fafc')">Light</button>
                        </div>
                    </div>

                    <!-- 3. Header Bar -->
                    <div class="cp-surface-card rounded-xl p-4 border border-zinc-200 dark:border-zinc-700 bg-zinc-50/70 dark:bg-zinc-800/40 flex flex-col gap-3 shadow-2xs" id="surface-card-header">
                        <div class="flex justify-between items-center flex-wrap gap-2">
                            <label class="text-xs font-bold text-zinc-900 dark:text-zinc-100">Header Bar</label>
                            <div class="flex items-center gap-2">
                                <span id="cp-label-header-bg" class="text-[11px] text-zinc-500 font-mono">#FFFFFF</span>
                                <div class="cp-mode-switch inline-flex bg-zinc-200/80 dark:bg-zinc-700/80 rounded-md p-0.5 gap-0.5">
                                    <button type="button" id="btn-mode-color-header" onclick="toggleSurfaceMode('header', 'color')" class="cp-surface-mode-btn inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-semibold rounded text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 transition-all cursor-pointer [&.is-active]:bg-white dark:[&.is-active]:bg-zinc-900 [&.is-active]:text-zinc-900 dark:[&.is-active]:text-white [&.is-active]:shadow-xs is-active">
                                        <i data-lucide="palette" class="w-3 h-3"></i> <span>Color</span>
                                    </button>
                                    <button type="button" id="btn-mode-image-header" onclick="toggleSurfaceMode('header', 'image')" class="cp-surface-mode-btn inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-semibold rounded text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 transition-all cursor-pointer [&.is-active]:bg-white dark:[&.is-active]:bg-zinc-900 [&.is-active]:text-zinc-900 dark:[&.is-active]:text-white [&.is-active]:shadow-xs">
                                        <i data-lucide="image" class="w-3 h-3"></i> <span>Image</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Color Mode Pane -->
                        <div id="pane-color-header" class="space-y-2">
                            <div class="flex items-center gap-2">
                                <input type="color" id="cp-color-header-bg" value="#ffffff" oninput="updateHeaderBg(this.value)" class="w-10 h-9 p-0.5 rounded-lg border border-zinc-300 dark:border-zinc-700 cursor-pointer bg-white dark:bg-zinc-800 shrink-0">
                                <input type="text" id="cp-text-header-bg" value="#ffffff" oninput="updateHeaderBg(this.value)" class="flex-1 h-9 px-3 py-1.5 text-xs font-mono rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-xs" placeholder="#FFFFFF">
                            </div>
                            <div class="flex gap-1.5 flex-wrap">
                                <button type="button" class="cp-color-preset-btn text-[11px] font-medium px-2 py-0.5 rounded border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-indigo-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all cursor-pointer shadow-2xs" onclick="updateHeaderBg('#ffffff')">White</button>
                                <button type="button" class="cp-color-preset-btn text-[11px] font-medium px-2 py-0.5 rounded border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-indigo-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all cursor-pointer shadow-2xs" onclick="updateHeaderBg('#f8fafc')">Ice Gray</button>
                                <button type="button" class="cp-color-preset-btn text-[11px] font-medium px-2 py-0.5 rounded border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-indigo-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all cursor-pointer shadow-2xs" onclick="updateHeaderBg('#0f172a')">Navy</button>
                                <button type="button" class="cp-color-preset-btn text-[11px] font-medium px-2 py-0.5 rounded border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-indigo-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all cursor-pointer shadow-2xs" onclick="updateHeaderBg('#312e81')">Indigo</button>
                                <button type="button" class="cp-color-preset-btn text-[11px] font-medium px-2 py-0.5 rounded border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-indigo-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all cursor-pointer shadow-2xs" onclick="updateHeaderBg('#18181b')">Slate</button>
                            </div>
                        </div>

                        <!-- Image Mode Pane -->
                        <div id="pane-image-header" class="hidden flex-col gap-2">
                            <div class="flex gap-1.5 items-center">
                                <input type="text" id="cp-img-url-header" class="flex-1 h-8 px-2.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white placeholder-zinc-400 focus:outline-none focus:ring-1 focus:ring-indigo-500" placeholder="Paste Image URL..." oninput="updateSurfaceImage('header', { imageUrl: this.value })">
                                <input type="file" id="cp-file-header" accept="image/*" class="hidden" onchange="handleSurfaceFileUpload('header', event)">
                                <button type="button" onclick="document.getElementById('cp-file-header').click()" class="inline-flex items-center gap-1 h-8 px-2.5 text-xs font-medium rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 cursor-pointer transition-colors shadow-2xs shrink-0">
                                    <i data-lucide="upload" class="w-3 h-3"></i> <span>Upload</span>
                                </button>
                            </div>
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="text-[10px] text-zinc-500 font-semibold">Presets:</span>
                                <button type="button" class="cp-wp-preset-btn text-[10px] font-medium px-2 py-0.5 rounded border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-indigo-500 hover:text-indigo-600 transition-all cursor-pointer shadow-2xs" onclick="applyWallpaperPreset('header', 'Geometric')">Geometric</button>
                                <button type="button" class="cp-wp-preset-btn text-[10px] font-medium px-2 py-0.5 rounded border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-indigo-500 hover:text-indigo-600 transition-all cursor-pointer shadow-2xs" onclick="applyWallpaperPreset('header', 'Silk Wave')">Silk Wave</button>
                                <button type="button" class="cp-wp-preset-btn text-[10px] font-medium px-2 py-0.5 rounded border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-indigo-500 hover:text-indigo-600 transition-all cursor-pointer shadow-2xs" onclick="applyWallpaperPreset('header', 'Slate Mesh')">Slate Mesh</button>
                                <button type="button" class="cp-wp-preset-btn text-[10px] font-medium px-2 py-0.5 rounded border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-indigo-500 hover:text-indigo-600 transition-all cursor-pointer shadow-2xs" onclick="applyWallpaperPreset('header', 'Obsidian')">Obsidian</button>
                                <button type="button" class="cp-wp-preset-btn text-[10px] font-medium px-2 py-0.5 rounded border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-indigo-500 hover:text-indigo-600 transition-all cursor-pointer shadow-2xs" onclick="applyWallpaperPreset('header', 'Studio Clean')">Studio Clean</button>
                            </div>
                            <div class="grid grid-cols-2 gap-2 p-2 bg-white dark:bg-zinc-800/80 rounded-lg border border-zinc-200 dark:border-zinc-700 text-[10px]">
                                <div>
                                    <div class="flex justify-between text-zinc-600 dark:text-zinc-400 mb-1">
                                        <span class="font-semibold">Opacity</span>
                                        <span id="label-opacity-header" class="font-mono">100%</span>
                                    </div>
                                    <input type="range" min="10" max="100" value="100" id="slider-opacity-header" oninput="updateSurfaceImage('header', { opacity: Number(this.value) })" class="w-full h-1 bg-zinc-200 dark:bg-zinc-700 rounded-lg appearance-none cursor-pointer accent-indigo-600">
                                </div>
                                <div>
                                    <div class="flex justify-between text-zinc-600 dark:text-zinc-400 mb-1">
                                        <span class="font-semibold">Brightness</span>
                                        <span id="label-brightness-header" class="font-mono">100%</span>
                                    </div>
                                    <input type="range" min="50" max="150" value="100" id="slider-brightness-header" oninput="updateSurfaceImage('header', { brightness: Number(this.value) })" class="w-full h-1 bg-zinc-200 dark:bg-zinc-700 rounded-lg appearance-none cursor-pointer accent-indigo-600">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 4. Side Panel (Navigation) -->
                    <div class="cp-surface-card rounded-xl p-4 border border-zinc-200 dark:border-zinc-700 bg-zinc-50/70 dark:bg-zinc-800/40 flex flex-col gap-3 shadow-2xs" id="surface-card-sidebar">
                        <div class="flex justify-between items-center flex-wrap gap-2">
                            <label class="text-xs font-bold text-zinc-900 dark:text-zinc-100">Side Panel (Navigation)</label>
                            <div class="flex items-center gap-2">
                                <span id="cp-label-sidebar-bg" class="text-[11px] text-zinc-500 font-mono">#FFFFFF</span>
                                <div class="cp-mode-switch inline-flex bg-zinc-200/80 dark:bg-zinc-700/80 rounded-md p-0.5 gap-0.5">
                                    <button type="button" id="btn-mode-color-sidebar" onclick="toggleSurfaceMode('sidebar', 'color')" class="cp-surface-mode-btn inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-semibold rounded text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 transition-all cursor-pointer [&.is-active]:bg-white dark:[&.is-active]:bg-zinc-900 [&.is-active]:text-zinc-900 dark:[&.is-active]:text-white [&.is-active]:shadow-xs is-active">
                                        <i data-lucide="palette" class="w-3 h-3"></i> <span>Color</span>
                                    </button>
                                    <button type="button" id="btn-mode-image-sidebar" onclick="toggleSurfaceMode('sidebar', 'image')" class="cp-surface-mode-btn inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-semibold rounded text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 transition-all cursor-pointer [&.is-active]:bg-white dark:[&.is-active]:bg-zinc-900 [&.is-active]:text-zinc-900 dark:[&.is-active]:text-white [&.is-active]:shadow-xs">
                                        <i data-lucide="image" class="w-3 h-3"></i> <span>Image</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Color Mode Pane -->
                        <div id="pane-color-sidebar" class="space-y-2">
                            <div class="flex items-center gap-2">
                                <input type="color" id="cp-color-sidebar-bg" value="#ffffff" oninput="updateSidebarBg(this.value)" class="w-10 h-9 p-0.5 rounded-lg border border-zinc-300 dark:border-zinc-700 cursor-pointer bg-white dark:bg-zinc-800 shrink-0">
                                <input type="text" id="cp-text-sidebar-bg" value="#ffffff" oninput="updateSidebarBg(this.value)" class="flex-1 h-9 px-3 py-1.5 text-xs font-mono rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-xs" placeholder="#FFFFFF">
                            </div>
                            <div class="flex gap-1.5 flex-wrap">
                                <button type="button" class="cp-color-preset-btn text-[11px] font-medium px-2 py-0.5 rounded border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-indigo-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all cursor-pointer shadow-2xs" onclick="updateSidebarBg('#ffffff')">White</button>
                                <button type="button" class="cp-color-preset-btn text-[11px] font-medium px-2 py-0.5 rounded border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-indigo-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all cursor-pointer shadow-2xs" onclick="updateSidebarBg('#f4f4f5')">Zinc</button>
                                <button type="button" class="cp-color-preset-btn text-[11px] font-medium px-2 py-0.5 rounded border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-indigo-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all cursor-pointer shadow-2xs" onclick="updateSidebarBg('#0f172a')">Slate</button>
                                <button type="button" class="cp-color-preset-btn text-[11px] font-medium px-2 py-0.5 rounded border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-indigo-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all cursor-pointer shadow-2xs" onclick="updateSidebarBg('#18181b')">Graphite</button>
                                <button type="button" class="cp-color-preset-btn text-[11px] font-medium px-2 py-0.5 rounded border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-indigo-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all cursor-pointer shadow-2xs" onclick="updateSidebarBg('#1e293b')">Navy</button>
                            </div>
                        </div>

                        <!-- Image Mode Pane -->
                        <div id="pane-image-sidebar" class="hidden flex-col gap-2">
                            <div class="flex gap-1.5 items-center">
                                <input type="text" id="cp-img-url-sidebar" class="flex-1 h-8 px-2.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white placeholder-zinc-400 focus:outline-none focus:ring-1 focus:ring-indigo-500" placeholder="Paste Image URL..." oninput="updateSurfaceImage('sidebar', { imageUrl: this.value })">
                                <input type="file" id="cp-file-sidebar" accept="image/*" class="hidden" onchange="handleSurfaceFileUpload('sidebar', event)">
                                <button type="button" onclick="document.getElementById('cp-file-sidebar').click()" class="inline-flex items-center gap-1 h-8 px-2.5 text-xs font-medium rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 cursor-pointer transition-colors shadow-2xs shrink-0">
                                    <i data-lucide="upload" class="w-3 h-3"></i> <span>Upload</span>
                                </button>
                            </div>
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="text-[10px] text-zinc-500 font-semibold">Presets:</span>
                                <button type="button" class="cp-wp-preset-btn text-[10px] font-medium px-2 py-0.5 rounded border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-indigo-500 hover:text-indigo-600 transition-all cursor-pointer shadow-2xs" onclick="applyWallpaperPreset('sidebar', 'Geometric')">Geometric</button>
                                <button type="button" class="cp-wp-preset-btn text-[10px] font-medium px-2 py-0.5 rounded border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-indigo-500 hover:text-indigo-600 transition-all cursor-pointer shadow-2xs" onclick="applyWallpaperPreset('sidebar', 'Silk Wave')">Silk Wave</button>
                                <button type="button" class="cp-wp-preset-btn text-[10px] font-medium px-2 py-0.5 rounded border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-indigo-500 hover:text-indigo-600 transition-all cursor-pointer shadow-2xs" onclick="applyWallpaperPreset('sidebar', 'Slate Mesh')">Slate Mesh</button>
                                <button type="button" class="cp-wp-preset-btn text-[10px] font-medium px-2 py-0.5 rounded border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-indigo-500 hover:text-indigo-600 transition-all cursor-pointer shadow-2xs" onclick="applyWallpaperPreset('sidebar', 'Obsidian')">Obsidian</button>
                                <button type="button" class="cp-wp-preset-btn text-[10px] font-medium px-2 py-0.5 rounded border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-indigo-500 hover:text-indigo-600 transition-all cursor-pointer shadow-2xs" onclick="applyWallpaperPreset('sidebar', 'Studio Clean')">Studio Clean</button>
                            </div>
                            <div class="grid grid-cols-2 gap-2 p-2 bg-white dark:bg-zinc-800/80 rounded-lg border border-zinc-200 dark:border-zinc-700 text-[10px]">
                                <div>
                                    <div class="flex justify-between text-zinc-600 dark:text-zinc-400 mb-1">
                                        <span class="font-semibold">Opacity</span>
                                        <span id="label-opacity-sidebar" class="font-mono">100%</span>
                                    </div>
                                    <input type="range" min="10" max="100" value="100" id="slider-opacity-sidebar" oninput="updateSurfaceImage('sidebar', { opacity: Number(this.value) })" class="w-full h-1 bg-zinc-200 dark:bg-zinc-700 rounded-lg appearance-none cursor-pointer accent-indigo-600">
                                </div>
                                <div>
                                    <div class="flex justify-between text-zinc-600 dark:text-zinc-400 mb-1">
                                        <span class="font-semibold">Brightness</span>
                                        <span id="label-brightness-sidebar" class="font-mono">100%</span>
                                    </div>
                                    <input type="range" min="50" max="150" value="100" id="slider-brightness-sidebar" oninput="updateSurfaceImage('sidebar', { brightness: Number(this.value) })" class="w-full h-1 bg-zinc-200 dark:bg-zinc-700 rounded-lg appearance-none cursor-pointer accent-indigo-600">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 5. Table Background Color -->
                    <div class="cp-surface-card rounded-xl p-4 border border-zinc-200 dark:border-zinc-700 bg-zinc-50/70 dark:bg-zinc-800/40 flex flex-col gap-3 shadow-2xs" id="surface-card-table">
                        <div class="flex justify-between items-center flex-wrap gap-2">
                            <label class="text-xs font-bold text-zinc-900 dark:text-zinc-100">Table Background</label>
                            <div class="flex items-center gap-2">
                                <span id="cp-label-table-bg" class="text-[11px] text-zinc-500 font-mono">#FFFFFF</span>
                                <div class="cp-mode-switch inline-flex bg-zinc-200/80 dark:bg-zinc-700/80 rounded-md p-0.5 gap-0.5">
                                    <button type="button" id="btn-mode-color-table" onclick="toggleSurfaceMode('table', 'color')" class="cp-surface-mode-btn inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-semibold rounded text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 transition-all cursor-pointer [&.is-active]:bg-white dark:[&.is-active]:bg-zinc-900 [&.is-active]:text-zinc-900 dark:[&.is-active]:text-white [&.is-active]:shadow-xs is-active">
                                        <i data-lucide="palette" class="w-3 h-3"></i> <span>Color</span>
                                    </button>
                                    <button type="button" id="btn-mode-image-table" onclick="toggleSurfaceMode('table', 'image')" class="cp-surface-mode-btn inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-semibold rounded text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 transition-all cursor-pointer [&.is-active]:bg-white dark:[&.is-active]:bg-zinc-900 [&.is-active]:text-zinc-900 dark:[&.is-active]:text-white [&.is-active]:shadow-xs">
                                        <i data-lucide="image" class="w-3 h-3"></i> <span>Image</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Color Mode Pane -->
                        <div id="pane-color-table" class="space-y-2">
                            <div class="flex items-center gap-2">
                                <input type="color" id="cp-color-table-bg" value="#ffffff" oninput="updateTableBg(this.value)" class="w-10 h-9 p-0.5 rounded-lg border border-zinc-300 dark:border-zinc-700 cursor-pointer bg-white dark:bg-zinc-800 shrink-0">
                                <input type="text" id="cp-text-table-bg" value="#ffffff" oninput="updateTableBg(this.value)" class="flex-1 h-9 px-3 py-1.5 text-xs font-mono rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-xs" placeholder="#FFFFFF">
                            </div>
                            <div class="flex gap-1.5 flex-wrap">
                                <button type="button" class="cp-color-preset-btn text-[11px] font-medium px-2 py-0.5 rounded border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-indigo-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all cursor-pointer shadow-2xs" onclick="updateTableBg('#ffffff')">White</button>
                                <button type="button" class="cp-color-preset-btn text-[11px] font-medium px-2 py-0.5 rounded border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-indigo-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all cursor-pointer shadow-2xs" onclick="updateTableBg('#fafafa')">Snow</button>
                                <button type="button" class="cp-color-preset-btn text-[11px] font-medium px-2 py-0.5 rounded border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-indigo-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all cursor-pointer shadow-2xs" onclick="updateTableBg('#f8fafc')">Soft Slate</button>
                                <button type="button" class="cp-color-preset-btn text-[11px] font-medium px-2 py-0.5 rounded border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-indigo-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all cursor-pointer shadow-2xs" onclick="updateTableBg('#141416')">Charcoal</button>
                                <button type="button" class="cp-color-preset-btn text-[11px] font-medium px-2 py-0.5 rounded border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-indigo-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all cursor-pointer shadow-2xs" onclick="updateTableBg('#0f172a')">Dark Navy</button>
                            </div>
                        </div>

                        <!-- Image Mode Pane -->
                        <div id="pane-image-table" class="hidden flex-col gap-2">
                            <div class="flex gap-1.5 items-center">
                                <input type="text" id="cp-img-url-table" class="flex-1 h-8 px-2.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white placeholder-zinc-400 focus:outline-none focus:ring-1 focus:ring-indigo-500" placeholder="Paste Image URL..." oninput="updateSurfaceImage('table', { imageUrl: this.value })">
                                <input type="file" id="cp-file-table" accept="image/*" class="hidden" onchange="handleSurfaceFileUpload('table', event)">
                                <button type="button" onclick="document.getElementById('cp-file-table').click()" class="inline-flex items-center gap-1 h-8 px-2.5 text-xs font-medium rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 cursor-pointer transition-colors shadow-2xs shrink-0">
                                    <i data-lucide="upload" class="w-3 h-3"></i> <span>Upload</span>
                                </button>
                            </div>
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="text-[10px] text-zinc-500 font-semibold">Presets:</span>
                                <button type="button" class="cp-wp-preset-btn text-[10px] font-medium px-2 py-0.5 rounded border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-indigo-500 hover:text-indigo-600 transition-all cursor-pointer shadow-2xs" onclick="applyWallpaperPreset('table', 'Geometric')">Geometric</button>
                                <button type="button" class="cp-wp-preset-btn text-[10px] font-medium px-2 py-0.5 rounded border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-indigo-500 hover:text-indigo-600 transition-all cursor-pointer shadow-2xs" onclick="applyWallpaperPreset('table', 'Silk Wave')">Silk Wave</button>
                                <button type="button" class="cp-wp-preset-btn text-[10px] font-medium px-2 py-0.5 rounded border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-indigo-500 hover:text-indigo-600 transition-all cursor-pointer shadow-2xs" onclick="applyWallpaperPreset('table', 'Slate Mesh')">Slate Mesh</button>
                                <button type="button" class="cp-wp-preset-btn text-[10px] font-medium px-2 py-0.5 rounded border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-indigo-500 hover:text-indigo-600 transition-all cursor-pointer shadow-2xs" onclick="applyWallpaperPreset('table', 'Obsidian')">Obsidian</button>
                                <button type="button" class="cp-wp-preset-btn text-[10px] font-medium px-2 py-0.5 rounded border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-indigo-500 hover:text-indigo-600 transition-all cursor-pointer shadow-2xs" onclick="applyWallpaperPreset('table', 'Studio Clean')">Studio Clean</button>
                            </div>
                            <div class="grid grid-cols-2 gap-2 p-2 bg-white dark:bg-zinc-800/80 rounded-lg border border-zinc-200 dark:border-zinc-700 text-[10px]">
                                <div>
                                    <div class="flex justify-between text-zinc-600 dark:text-zinc-400 mb-1">
                                        <span class="font-semibold">Opacity</span>
                                        <span id="label-opacity-table" class="font-mono">100%</span>
                                    </div>
                                    <input type="range" min="10" max="100" value="100" id="slider-opacity-table" oninput="updateSurfaceImage('table', { opacity: Number(this.value) })" class="w-full h-1 bg-zinc-200 dark:bg-zinc-700 rounded-lg appearance-none cursor-pointer accent-indigo-600">
                                </div>
                                <div>
                                    <div class="flex justify-between text-zinc-600 dark:text-zinc-400 mb-1">
                                        <span class="font-semibold">Brightness</span>
                                        <span id="label-brightness-table" class="font-mono">100%</span>
                                    </div>
                                    <input type="range" min="50" max="150" value="100" id="slider-brightness-table" oninput="updateSurfaceImage('table', { brightness: Number(this.value) })" class="w-full h-1 bg-zinc-200 dark:bg-zinc-700 rounded-lg appearance-none cursor-pointer accent-indigo-600">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 6. Table Border Color -->
                    <div class="cp-surface-card rounded-xl p-4 border border-zinc-200 dark:border-zinc-700 bg-zinc-50/70 dark:bg-zinc-800/40 flex flex-col gap-3 shadow-2xs">
                        <div class="flex justify-between items-center">
                            <label class="text-xs font-bold text-zinc-900 dark:text-zinc-100">Table Border Color</label>
                            <span id="cp-label-table-border" class="text-[11px] text-zinc-500 font-mono">#E2E8F0</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="color" id="cp-color-table-border" value="#e2e8f0" oninput="updateTableBorder(this.value)" class="w-10 h-9 p-0.5 rounded-lg border border-zinc-300 dark:border-zinc-700 cursor-pointer bg-white dark:bg-zinc-800 shrink-0">
                            <input type="text" id="cp-text-table-border" value="#e2e8f0" oninput="updateTableBorder(this.value)" class="flex-1 h-9 px-3 py-1.5 text-xs font-mono rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-xs" placeholder="#E2E8F0">
                        </div>
                        <div class="flex gap-1.5 flex-wrap">
                            <button type="button" class="cp-color-preset-btn text-[11px] font-medium px-2 py-0.5 rounded border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-indigo-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all cursor-pointer shadow-2xs" onclick="updateTableBorder('#e2e8f0')">Light Slate</button>
                            <button type="button" class="cp-color-preset-btn text-[11px] font-medium px-2 py-0.5 rounded border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-indigo-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all cursor-pointer shadow-2xs" onclick="updateTableBorder('#e4e4e7')">Zinc Border</button>
                            <button type="button" class="cp-color-preset-btn text-[11px] font-medium px-2 py-0.5 rounded border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-indigo-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all cursor-pointer shadow-2xs" onclick="updateTableBorder('#d4d4d8')">Crisp Gray</button>
                            <button type="button" class="cp-color-preset-btn text-[11px] font-medium px-2 py-0.5 rounded border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-indigo-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all cursor-pointer shadow-2xs" onclick="updateTableBorder('#c7d2fe')">Soft Indigo</button>
                            <button type="button" class="cp-color-preset-btn text-[11px] font-medium px-2 py-0.5 rounded border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-indigo-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all cursor-pointer shadow-2xs" onclick="updateTableBorder('#27272a')">Dark Border</button>
                        </div>
                    </div>

                </div>

                <!-- Primary Brand Accent & Swatches -->
                <div class="border-t border-zinc-200 dark:border-zinc-700 pt-5 space-y-3">
                    @php
                        $palettes = [
                            ['id' => 'violet', 'name' => 'Electric Violet', 'hex' => '#4F46E5'],
                            ['id' => 'emerald', 'name' => 'Emerald Growth', 'hex' => '#10B981'],
                            ['id' => 'amber', 'name' => 'Sunset Amber', 'hex' => '#F59E0B'],
                            ['id' => 'rose', 'name' => 'Rose Flare', 'hex' => '#F43F5E'],
                            ['id' => 'cyan', 'name' => 'Cyber Cyan', 'hex' => '#06B6D4'],
                            ['id' => 'slate', 'name' => 'Slate Tech', 'hex' => '#475569'],
                            ['id' => 'obsidian', 'name' => 'Obsidian Dark', 'hex' => '#09090B'],
                        ];
                        $activeAccent = strtolower($companyProfile->accent_color ?? '#06B6D4');
                        $currentPaletteName = 'Cyber Cyan';
                        foreach ($palettes as $p) {
                            if (strtolower($p['hex']) === $activeAccent) {
                                $currentPaletteName = $p['name'];
                                break;
                            }
                        }
                    @endphp
                    <div class="flex justify-between items-center flex-wrap gap-2">
                        <div class="text-xs font-bold text-zinc-900 dark:text-zinc-100">
                            Primary Brand Accent: <span id="cp-accent-name-display" style="color: {{ $companyProfile->accent_color ?? '#06B6D4' }}; font-weight: 800;">{{ $currentPaletteName }} ({{ $companyProfile->accent_color ?? '#06B6D4' }})</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="color" id="cp-color-accent" value="{{ $companyProfile->accent_color ?? '#06B6D4' }}" oninput="selectAccent(this.value)" class="w-8 h-7 p-0.5 rounded border border-zinc-300 dark:border-zinc-700 cursor-pointer bg-white shrink-0">
                            <input type="text" id="cp-text-accent" value="{{ $companyProfile->accent_color ?? '#06B6D4' }}" oninput="selectAccent(this.value)" class="w-24 h-7 px-2 text-xs font-mono rounded border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white shadow-2xs">
                        </div>
                    </div>
                    <div class="flex gap-2.5 flex-wrap items-center" id="cp-accent-swatches-container">
                        @foreach($palettes as $p)
                            @php
                                $isSelected = strtolower($p['hex']) === $activeAccent;
                            @endphp
                            <button
                                type="button"
                                class="cp-swatch-btn"
                                data-hex="{{ $p['hex'] }}"
                                onclick="selectAccent('{{ $p['hex'] }}')"
                                style="width: 34px; height: 34px; border-radius: 50%; background: {{ $p['hex'] }}; border: {{ $isSelected ? '3px solid #ffffff' : '2px solid transparent' }}; outline: {{ $isSelected ? ('2.5px solid ' . $p['hex']) : 'none' }}; cursor: pointer; transition: all 0.15s ease; transform: {{ $isSelected ? 'scale(1.1)' : 'scale(1)' }}; box-shadow: 0 1px 3px rgba(0,0,0,0.12);"
                                title="{{ $p['name'] }} ({{ $p['hex'] }})">
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Live Interactive Miniature Workspace Appearance Preview -->
                <div class="mt-6 p-4 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-100 dark:bg-zinc-800/50">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 mb-2.5">
                        Live Workspace Appearance Preview
                    </div>
                    <div class="rounded-lg overflow-hidden border border-zinc-300 dark:border-zinc-700 shadow-sm flex h-32">
                        <!-- Mini Sidebar -->
                        <div id="mini-preview-sidebar" class="w-12 bg-white border-r border-black/10 p-2 flex flex-col gap-1.5 items-center relative overflow-hidden shrink-0">
                            <div id="mini-sidebar-img-layer" class="hidden absolute inset-0 bg-cover pointer-events-none"></div>
                            <div id="mini-preview-sidebar-dot" class="w-4 h-4 rounded bg-indigo-600 relative z-10"></div>
                            <div class="w-3.5 h-1 rounded bg-zinc-400/30 mt-1 relative z-10"></div>
                            <div class="w-3.5 h-1 rounded bg-zinc-400/30 relative z-10"></div>
                            <div class="w-3.5 h-1 rounded bg-zinc-400/30 relative z-10"></div>
                        </div>

                        <!-- Right Side: Mini Header & Canvas -->
                        <div class="flex-1 flex flex-col min-w-0">
                            <!-- Mini Header -->
                            <div id="mini-preview-header" class="h-6 bg-white border-b border-black/10 px-2 flex items-center justify-between relative overflow-hidden shrink-0">
                                <div id="mini-header-img-layer" class="hidden absolute inset-0 bg-cover pointer-events-none"></div>
                                <span id="mini-preview-header-title" class="text-[9px] font-bold text-zinc-900 truncate relative z-10">Acme Enterprise Tech</span>
                                <div id="mini-preview-header-accent" class="w-3 h-3 rounded-full bg-indigo-600 relative z-10 shrink-0"></div>
                            </div>

                            <!-- Mini Canvas -->
                            <div id="mini-preview-canvas" class="flex-1 bg-zinc-50 p-2 flex items-center justify-between gap-2 relative overflow-hidden">
                                <div id="mini-canvas-img-layer" class="hidden absolute inset-0 bg-cover pointer-events-none"></div>
                                
                                <div class="relative z-10 min-w-0">
                                    <div id="mini-preview-canvas-title" class="text-[10px] font-extrabold text-zinc-900 leading-tight truncate">
                                        Company Dashboard
                                    </div>
                                    <div id="mini-preview-canvas-subtitle" class="text-[8px] text-zinc-900 opacity-70 mt-0.5 truncate">
                                        Custom brand styling active
                                    </div>
                                </div>

                                <!-- Mini Data Table Card -->
                                <div id="mini-preview-table-card" class="relative z-10 bg-white border border-zinc-200 rounded-md p-1.5 shadow-xs flex flex-col gap-0.5 min-w-[130px] overflow-hidden">
                                    <div id="mini-table-img-layer" class="hidden absolute inset-0 bg-cover pointer-events-none"></div>
                                    <div id="mini-table-header-row" class="relative z-10 flex justify-between text-[6.5px] font-bold text-zinc-900 border-b border-zinc-200 pb-0.5">
                                        <span>Account</span>
                                        <span>Stage</span>
                                        <span>Value</span>
                                    </div>
                                    <div class="relative z-10 flex justify-between text-[6px] text-zinc-900 opacity-85">
                                        <span>Acme Inc</span>
                                        <span id="mini-table-row1-stage" class="text-indigo-600 font-bold">Won</span>
                                        <span>$24,500</span>
                                    </div>
                                    <div class="relative z-10 flex justify-between text-[6px] text-zinc-900 opacity-85">
                                        <span>Global Logistics</span>
                                        <span class="text-emerald-600 font-bold">Closed</span>
                                        <span>$18,200</span>
                                    </div>
                                </div>

                                <button type="button" id="mini-preview-canvas-btn" class="relative z-10 bg-indigo-600 text-white rounded px-2 py-1 text-[9px] font-bold cursor-default shrink-0">
                                    + Action
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Profile -->
            <div class="flex justify-end">
                <button type="submit" class="inline-flex items-center gap-2 text-xs font-semibold px-5 py-2.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white border border-indigo-700 shadow-sm transition-colors cursor-pointer">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Save Organization Profile</span>
                </button>
            </div>
        </form>
    </div>

    <!-- ============================================================== -->
    <!-- TAB 2: REGIONAL & LOCALIZATION                                 -->
    <!-- ============================================================== -->
    <div id="tab-pane-regional" class="hidden flex-col gap-6">
        <form onsubmit="handleSaveRegionalSettings(event)" class="flex flex-col gap-6">
            <!-- Box 1: Time Zone & Real-Time Clock -->
            <div class="card card-table-wrapper rounded-xl p-5 sm:p-6 shadow-sm border space-y-4" style="background-color: var(--table-bg, #ffffff); border-color: var(--table-border, #e2e8f0); color: var(--text-primary, #09090b);">
                <div>
                    <h3 class="text-sm font-bold flex items-center gap-2 m-0" style="color: var(--text-primary, #09090b);">
                        <i data-lucide="clock" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"></i>
                        <span>Organization Time Zone &amp; Real-Time Clock</span>
                    </h3>
                    <p class="text-xs mt-1 mb-0 opacity-75" style="color: var(--text-primary, #09090b);">
                        Sets the reference time for timestamps, SLA calculations, approval timeouts, and audit logs.
                    </p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-center">
                    <div class="lg:col-span-2 space-y-1.5">
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Default Organization Time Zone</label>
                        <select id="cp-select-timezone" class="w-full h-9 px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-xs cursor-pointer" onchange="updateTimeZone(this.value)">
                            <!-- Populated via JS -->
                        </select>
                        <div class="text-[11px] text-zinc-400">
                            Standard IANA Time Zone definition.
                        </div>
                    </div>

                    <!-- Live Clock Card -->
                    <div class="p-4 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/40 text-center shadow-2xs">
                        <div id="cp-clock-label" class="text-[11px] text-zinc-500 dark:text-zinc-400 uppercase tracking-wider font-semibold">
                            Live Time in UTC
                        </div>
                        <div id="cp-live-clock" class="text-2xl font-extrabold text-indigo-600 dark:text-indigo-400 mt-1 font-mono tracking-tight">
                            --:--:--
                        </div>
                    </div>
                </div>
            </div>

            <!-- Box 2: Date, Time & Number Formatting Standards -->
            <div class="card card-table-wrapper rounded-xl p-5 sm:p-6 shadow-sm border space-y-4" style="background-color: var(--table-bg, #ffffff); border-color: var(--table-border, #e2e8f0); color: var(--text-primary, #09090b);">
                <div>
                    <h3 class="text-sm font-bold flex items-center gap-2 m-0" style="color: var(--text-primary, #09090b);">
                        <i data-lucide="sliders" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"></i>
                        <span>Date, Time &amp; Number Formatting Standards</span>
                    </h3>
                    <p class="text-xs mt-1 mb-0 opacity-75" style="color: var(--text-primary, #09090b);">
                        Consistent formatting across table grids, deal amounts, exports, and Kanban views.
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    <!-- Date Format -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Date Format</label>
                        <select id="cp-select-date-format" class="w-full h-9 px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-xs cursor-pointer" onchange="updateFormatPreviews()">
                            <option value="YYYY-MM-DD">YYYY-MM-DD (ISO Standard)</option>
                            <option value="DD/MM/YYYY">DD/MM/YYYY (UK / Europe / India)</option>
                            <option value="MM/DD/YYYY">MM/DD/YYYY (US Standard)</option>
                        </select>
                        <div class="text-[11px] text-zinc-500 dark:text-zinc-400">
                            Preview: <strong id="cp-preview-date-format" class="text-zinc-800 dark:text-zinc-200 font-semibold">2026-10-13</strong>
                        </div>
                    </div>

                    <!-- Time Format -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Time Format</label>
                        <select id="cp-select-time-format" class="w-full h-9 px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-xs cursor-pointer" onchange="updateFormatPreviews()">
                            <option value="12h">12-Hour (e.g. 02:30 PM)</option>
                            <option value="24h">24-Hour (e.g. 14:30)</option>
                        </select>
                        <div class="text-[11px] text-zinc-500 dark:text-zinc-400">
                            Preview: <strong id="cp-preview-time-format" class="text-zinc-800 dark:text-zinc-200 font-semibold">02:30 PM</strong>
                        </div>
                    </div>

                    <!-- Number Format -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Number Separators</label>
                        <select id="cp-select-number-format" class="w-full h-9 px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-xs cursor-pointer" onchange="updateFormatPreviews()">
                            <option value="comma_dot">1,234.56 (US / UK Standard)</option>
                            <option value="dot_comma">1.234,56 (European Standard)</option>
                            <option value="space_comma">1 234,56 (Nordic / French)</option>
                        </select>
                        <div class="text-[11px] text-zinc-500 dark:text-zinc-400">
                            Preview: <strong id="cp-preview-number-format" class="text-zinc-800 dark:text-zinc-200 font-semibold">$1,250,000.75</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Box 3: Fiscal Year & Operating Hours -->
            <div class="card card-table-wrapper rounded-xl p-5 sm:p-6 shadow-sm border space-y-4" style="background-color: var(--table-bg, #ffffff); border-color: var(--table-border, #e2e8f0); color: var(--text-primary, #09090b);">
                <div>
                    <h3 class="text-sm font-bold flex items-center gap-2 m-0" style="color: var(--text-primary, #09090b);">
                        <i data-lucide="calendar" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"></i>
                        <span>Fiscal Year &amp; Operating Hours</span>
                    </h3>
                    <p class="text-xs mt-1 mb-0 opacity-75" style="color: var(--text-primary, #09090b);">
                        Defines quarterly reporting cycles, business day schedules, and office hours.
                    </p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-4">
                    <!-- Fiscal Month -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Fiscal Year Starts In</label>
                        <select id="cp-select-fiscal-month" class="w-full h-9 px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-xs cursor-pointer" onchange="updateFiscalQuarterText(this.value)">
                            <!-- Populated via JS -->
                        </select>
                        <div id="cp-fiscal-q1-note" class="text-[11px] text-zinc-500">
                            Q1 begins January 1st.
                        </div>
                    </div>

                    <!-- Working Days Checkboxes -->
                    <div class="lg:col-span-2 space-y-1.5">
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Business Working Days</label>
                        <div id="cp-working-days-container" class="flex gap-2 flex-wrap pt-0.5">
                            <!-- Populated via JS -->
                        </div>
                    </div>
                </div>

                <!-- Operating Hours -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 pt-2 border-t border-zinc-200 dark:border-zinc-700">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Business Hours Start</label>
                        <input type="time" id="cp-input-work-start" class="w-full h-9 px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-xs" value="09:00">
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Business Hours End</label>
                        <input type="time" id="cp-input-work-end" class="w-full h-9 px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-xs" value="18:00">
                    </div>
                </div>
            </div>

            <!-- Box 4: Security & Password Governance -->
            <div class="card card-table-wrapper rounded-xl p-5 sm:p-6 shadow-sm border space-y-4" style="background-color: var(--table-bg, #ffffff); border-color: var(--table-border, #e2e8f0); color: var(--text-primary, #09090b);">
                <div>
                    <h3 class="text-sm font-bold flex items-center gap-2 m-0" style="color: var(--text-primary, #09090b);">
                        <i data-lucide="shield" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"></i>
                        <span>Security &amp; Password Governance</span>
                    </h3>
                    <p class="text-xs mt-1 mb-0 opacity-75" style="color: var(--text-primary, #09090b);">
                        Control whether employees and non-admin team members have the autonomy to change their own passwords.
                    </p>
                </div>

                <div id="cp-password-policy-box" class="flex items-start gap-3.5 p-4 bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200 dark:border-zinc-700 rounded-xl shadow-2xs">
                    <input type="checkbox" id="cp-check-allow-pwd" onchange="updatePasswordPolicyDisplay(this.checked)" class="w-4 h-4 mt-0.5 rounded cursor-pointer accent-indigo-600">
                    <label for="cp-check-allow-pwd" class="cursor-pointer flex-1 select-none">
                        <div class="font-semibold text-xs text-zinc-900 dark:text-zinc-100">
                            Allow Non-Admin Users to Change Password
                        </div>
                        <div id="cp-password-policy-desc" class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5 leading-relaxed">
                            Enabled: Team members can independently update their login credentials from My Profile &amp; Preferences.
                        </div>
                    </label>
                </div>
            </div>

            <!-- Submit Regional -->
            <div class="flex justify-end">
                <button type="submit" class="inline-flex items-center gap-2 text-xs font-semibold px-5 py-2.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white border border-indigo-700 shadow-sm transition-colors cursor-pointer">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Save Regional &amp; Localization Settings</span>
                </button>
            </div>
        </form>
    </div>

    <!-- ============================================================== -->
    <!-- TAB 3: MULTI-CURRENCY & EXCHANGE RATES                         -->
    <!-- ============================================================== -->
    <div id="tab-pane-currency" class="hidden flex-col gap-6">
        <!-- Base Currency Banner -->
        <div class="rounded-2xl p-6 bg-gradient-to-br from-zinc-900 to-zinc-950 text-white flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 shadow-md border border-zinc-800">
            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-zinc-400">
                    Primary Organization Base Currency
                </div>
                <div class="text-2xl sm:text-3xl font-extrabold mt-1 flex items-center gap-3">
                    <span id="cp-base-currency-display">USD ($)</span>
                    <span class="inline-flex items-center text-xs font-semibold px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                        Base 1.0
                    </span>
                </div>
                <div class="text-xs text-zinc-400 mt-1">
                    All pipeline summaries, Kanban totals, and financial roll-ups aggregate into this base currency.
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <button type="button" onclick="openAddCurrencyModal()" class="inline-flex items-center gap-1.5 text-xs font-semibold px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white border border-indigo-700 shadow-sm transition-colors cursor-pointer">
                    <i data-lucide="plus" class="w-4 h-4 stroke-[2.5]"></i>
                    <span>Add World Currency</span>
                </button>
            </div>
        </div>

        <!-- Active World Currencies & Exchange Rates Table -->
        <div class="card card-table-wrapper rounded-xl p-5 sm:p-6 shadow-sm border space-y-4" style="background-color: var(--table-bg, #ffffff); border-color: var(--table-border, #e2e8f0); color: var(--text-primary, #09090b);">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h3 class="text-sm font-bold m-0" style="color: var(--text-primary, #09090b);">
                        Configured Currencies &amp; Exchange Rates
                    </h3>
                    <p class="text-xs mt-1 mb-0 opacity-75" style="color: var(--text-primary, #09090b);">
                        Exchange rates indicate how many units of <strong id="cp-table-base-code-label">USD</strong> equal 1 unit of the target currency.
                    </p>
                </div>

                <button type="button" onclick="renderCurrenciesTable(); showToast('Exchange rates table refreshed!');" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg border shadow-sm transition-colors cursor-pointer shrink-0" style="background-color: var(--table-bg, #ffffff); border-color: var(--table-border, #e2e8f0); color: var(--text-primary, #09090b);">
                    <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                    <span>Refresh Rates</span>
                </button>
            </div>

            <div class="overflow-x-auto rounded-lg border" style="border-color: var(--table-border, #e2e8f0);">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b text-zinc-500 dark:text-zinc-400 font-semibold text-[11px] uppercase tracking-wider" style="border-color: var(--table-border, #e2e8f0); background-color: var(--table-bg, #ffffff);">
                            <th class="py-3 px-4">Currency</th>
                            <th class="py-3 px-4">Symbol</th>
                            <th class="py-3 px-4">Name</th>
                            <th class="py-3 px-4">Exchange Rate (to <span id="cp-th-base-code">USD</span>)</th>
                            <th class="py-3 px-4">Type</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="cp-currencies-tbody" class="divide-y" style="border-color: var(--table-border, #e2e8f0);">
                        <!-- Populated dynamically via JS -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Real-Time Interactive Currency Converter Simulator -->
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/40 p-5 space-y-3 shadow-2xs">
            <div>
                <h4 class="text-xs font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2 m-0">
                    <i data-lucide="arrow-right-left" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"></i>
                    <span>Interactive Exchange Rate Calculator &amp; Simulator</span>
                </h4>
                <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-1 mb-0">
                    Test live conversions between configured currencies using real-time tenant exchange rates.
                </p>
            </div>

            <div class="flex items-center gap-4 flex-wrap pt-2">
                <div class="w-36 space-y-1">
                    <label class="block text-[11px] font-semibold text-zinc-700 dark:text-zinc-300">Amount</label>
                    <input type="number" id="cp-calc-amount" class="w-full h-9 px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-xs" value="1000" oninput="calculateLiveConversion()">
                </div>

                <div class="w-44 space-y-1">
                    <label class="block text-[11px] font-semibold text-zinc-700 dark:text-zinc-300">From</label>
                    <select id="cp-calc-from-curr" class="w-full h-9 px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-xs cursor-pointer" onchange="calculateLiveConversion()">
                        <!-- Populated via JS -->
                    </select>
                </div>

                <div class="pt-5 text-zinc-400">
                    <i data-lucide="arrow-right-left" class="w-4 h-4"></i>
                </div>

                <div class="w-44 space-y-1">
                    <label class="block text-[11px] font-semibold text-zinc-700 dark:text-zinc-300">To</label>
                    <select id="cp-calc-to-curr" class="w-full h-9 px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-xs cursor-pointer" onchange="calculateLiveConversion()">
                        <!-- Populated via JS -->
                    </select>
                </div>

                <div class="bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 rounded-xl px-5 py-2.5 min-w-[200px] shadow-xs mt-4">
                    <div class="text-[10px] text-zinc-500 dark:text-zinc-400 uppercase font-bold tracking-wider">
                        Converted Result
                    </div>
                    <div id="cp-calc-result-display" class="text-xl font-extrabold text-indigo-600 dark:text-indigo-400 font-mono mt-0.5">
                        $1,085.00
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- TAB 4: OUTBOUND EMAIL & SMTP GATEWAY INFRASTRUCTURE            -->
    <!-- ============================================================== -->
    <div id="tab-pane-email" class="hidden flex-col gap-6">
        <form onsubmit="handleSaveEmailSettings(event)" class="flex flex-col gap-6">
            <!-- Card 1: Cloud Provider Presets -->
            <div class="card card-table-wrapper rounded-xl p-5 sm:p-6 shadow-sm border space-y-4" style="background-color: var(--table-bg, #ffffff); border-color: var(--table-border, #e2e8f0); color: var(--text-primary, #09090b);">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-bold flex items-center gap-2 m-0" style="color: var(--text-primary, #09090b);">
                            <i data-lucide="server" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"></i>
                            <span>Outbound Mail Provider Presets</span>
                        </h3>
                        <p class="text-xs mt-1 mb-0 opacity-75" style="color: var(--text-primary, #09090b);">
                            Select your enterprise delivery gateway to auto-fill verified host, port, and security parameters.
                        </p>
                    </div>

                    <span id="cp-smtp-status-badge-inline" class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800 shadow-2xs shrink-0">
                        Gateway Status: <strong class="capitalize" id="cp-smtp-status-text">Verified</strong>
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5" id="cp-email-presets-container">
                    <!-- Populated via JS -->
                </div>
            </div>

            <!-- Card 2: SMTP Connection & Authentication Credentials -->
            <div class="card card-table-wrapper rounded-xl p-5 sm:p-6 shadow-sm border space-y-4" style="background-color: var(--table-bg, #ffffff); border-color: var(--table-border, #e2e8f0); color: var(--text-primary, #09090b);">
                <div>
                    <h3 class="text-sm font-bold flex items-center gap-2 m-0" style="color: var(--text-primary, #09090b);">
                        <i data-lucide="key" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"></i>
                        <span>SMTP Connection &amp; Authentication</span>
                    </h3>
                    <p class="text-xs mt-1 mb-0 opacity-75" style="color: var(--text-primary, #09090b);">
                        Configure your mail server credentials. All credentials are isolated per tenant in the database.
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div class="space-y-1.5 sm:col-span-2">
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">SMTP Relay Host *</label>
                        <input type="text" id="cp-smtp-host" required placeholder="e.g. smtp.sendgrid.net or smtp.office365.com" class="w-full h-9 px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-xs">
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">SMTP Port *</label>
                        <div class="flex items-center gap-2">
                            <input type="number" id="cp-smtp-port" required class="w-24 h-9 px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-xs" value="587">
                            <div class="flex items-center gap-1">
                                <button type="button" class="px-2 py-1 text-xs font-medium rounded border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 hover:bg-white text-zinc-700 dark:text-zinc-300 cursor-pointer shadow-2xs" onclick="setPort(587)">587</button>
                                <button type="button" class="px-2 py-1 text-xs font-medium rounded border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 hover:bg-white text-zinc-700 dark:text-zinc-300 cursor-pointer shadow-2xs" onclick="setPort(465)">465</button>
                                <button type="button" class="px-2 py-1 text-xs font-medium rounded border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 hover:bg-white text-zinc-700 dark:text-zinc-300 cursor-pointer shadow-2xs" onclick="setPort(25)">25</button>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-1.5 sm:col-span-2 lg:col-span-3">
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Security Protocol</label>
                        <div class="flex items-center gap-5 pt-1">
                            <label class="inline-flex items-center gap-2 text-xs font-medium cursor-pointer text-zinc-800 dark:text-zinc-200">
                                <input type="radio" name="cpSmtpSecure" id="cp-proto-starttls" checked onchange="toggleSmtpSecure(false)" class="accent-indigo-600">
                                <span>STARTTLS (Port 587 / 25 - Recommended)</span>
                            </label>
                            <label class="inline-flex items-center gap-2 text-xs font-medium cursor-pointer text-zinc-800 dark:text-zinc-200">
                                <input type="radio" name="cpSmtpSecure" id="cp-proto-ssl" onchange="toggleSmtpSecure(true)" class="accent-indigo-600">
                                <span>SSL/TLS (Port 465)</span>
                            </label>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">SMTP Username / API Key Username</label>
                        <input type="text" id="cp-smtp-user" placeholder="e.g. apikey or user@domain.com" class="w-full h-9 px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-xs">
                    </div>

                    <div class="space-y-1.5 sm:col-span-2">
                        <label class="flex items-center justify-between text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                            <span>SMTP Password / API Key Secret</span>
                            <span id="cp-smtp-pwd-configured-badge" class="inline-flex items-center text-[10px] px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 font-semibold">
                                Configured
                            </span>
                        </label>
                        <div class="relative">
                            <input type="password" id="cp-smtp-pass" placeholder="•••••••• (Leave blank to keep existing password)" class="w-full h-9 pl-3 pr-10 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-xs">
                            <button type="button" onclick="toggleShowSmtpPass()" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-600 p-1 cursor-pointer">
                                <i id="cp-smtp-eye-icon" data-lucide="eye" class="w-4 h-4"></i>
                            </button>
                        </div>
                        <span class="text-[11px] text-zinc-500 block">
                            For SendGrid, use <code>apikey</code> as username and your SendGrid API key as password. For Google Workspace, generate an App Password.
                        </span>
                    </div>
                </div>
            </div>

            <!-- Card 3: Sender Identity & Outbound Routing Policies -->
            <div class="card card-table-wrapper rounded-xl p-5 sm:p-6 shadow-sm border space-y-4" style="background-color: var(--table-bg, #ffffff); border-color: var(--table-border, #e2e8f0); color: var(--text-primary, #09090b);">
                <div>
                    <h3 class="text-sm font-bold flex items-center gap-2 m-0" style="color: var(--text-primary, #09090b);">
                        <i data-lucide="shield-check" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"></i>
                        <span>Sender Identity &amp; Routing Policy</span>
                    </h3>
                    <p class="text-xs mt-1 mb-0 opacity-75" style="color: var(--text-primary, #09090b);">
                        Define the default outbound headers, display names, and individual sales rep override policies.
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Default From Display Name *</label>
                        <input type="text" id="cp-email-from-name" required placeholder="e.g. Acme Sales Team or Acme Corp" class="w-full h-9 px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-xs">
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Default From Email Address *</label>
                        <input type="email" id="cp-email-from-address" required placeholder="e.g. sales@acmecorp.com" class="w-full h-9 px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-xs">
                    </div>

                    <div class="space-y-1.5 sm:col-span-2 lg:col-span-1">
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Reply-To Address (Optional)</label>
                        <input type="email" id="cp-email-reply-to" placeholder="e.g. support@acmecorp.com (Leave blank to use From Address)" class="w-full h-9 px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-xs">
                    </div>
                </div>

                <div class="p-4 bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200 dark:border-zinc-700 rounded-xl space-y-3">
                    <label class="block text-xs font-semibold text-zinc-800 dark:text-zinc-200">
                        Outbound Sender Identity Policy
                    </label>

                    <div class="space-y-2.5">
                        <label class="cp-radio-card flex items-start gap-3 p-3 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 hover:border-zinc-300 hover:bg-zinc-50/50 cursor-pointer transition-all has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50/40 dark:has-[:checked]:bg-indigo-950/20 has-[:checked]:ring-1 has-[:checked]:ring-indigo-600">
                            <input type="radio" name="senderIdentityPolicy" id="cp-policy-org-default" value="org_default" checked class="mt-0.5 accent-indigo-600">
                            <div>
                                <div class="text-xs font-bold text-zinc-900 dark:text-zinc-100">
                                    Strict Tenant Gateway (Highest Deliverability)
                                </div>
                                <div class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5">
                                    All outbound emails dispatch strictly from the organization tenant address. The individual sales rep’s email is placed in the <code>Reply-To</code> header.
                                </div>
                            </div>
                        </label>

                        <label class="cp-radio-card flex items-start gap-3 p-3 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 hover:border-zinc-300 hover:bg-zinc-50/50 cursor-pointer transition-all has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50/40 dark:has-[:checked]:bg-indigo-950/20 has-[:checked]:ring-1 has-[:checked]:ring-indigo-600">
                            <input type="radio" name="senderIdentityPolicy" id="cp-policy-user-sender" value="user_as_sender" class="mt-0.5 accent-indigo-600">
                            <div>
                                <div class="text-xs font-bold text-zinc-900 dark:text-zinc-100">
                                    Delegated Rep Identity (Sender Override)
                                </div>
                                <div class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5">
                                    Emails use the logged-in rep's profile name and email address as <code>From</code> (requires your mail server / SPF domain to permit authenticated relay on rep domains).
                                </div>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Card 4: Legal Compliance Email Footer Studio -->
            <div class="card card-table-wrapper rounded-xl p-5 sm:p-6 shadow-sm border space-y-4" style="background-color: var(--table-bg, #ffffff); border-color: var(--table-border, #e2e8f0); color: var(--text-primary, #09090b);">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-bold flex items-center gap-2 m-0" style="color: var(--text-primary, #09090b);">
                            <i data-lucide="file-text" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"></i>
                            <span>Legal Compliance Email Footer Studio</span>
                        </h3>
                        <p class="text-xs mt-1 mb-0 opacity-75" style="color: var(--text-primary, #09090b);">
                            Enforce statutory physical address disclosures and unsubscribe links on all outbound communications (CAN-SPAM, GDPR, CASL).
                        </p>
                    </div>

                    <button type="button" onclick="handleAutoGenerateLegalFooter()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg border shadow-sm transition-colors cursor-pointer shrink-0" style="background-color: var(--table-bg, #ffffff); border-color: var(--table-border, #e2e8f0); color: var(--text-primary, #09090b);" title="Generate HTML footer from organization profile, legal name, and headquarters address">
                        <i data-lucide="sparkles" class="w-3.5 h-3.5 text-purple-600 dark:text-purple-400"></i>
                        <span>Auto-Populate from Profile</span>
                    </button>
                </div>

                <div>
                    <label class="inline-flex items-center gap-2.5 cursor-pointer">
                        <input type="checkbox" id="cp-enforce-footer-check" checked class="w-4 h-4 rounded accent-indigo-600">
                        <span class="text-xs font-semibold text-zinc-900 dark:text-zinc-100">
                            Enforce footer appending on all system and automated outbound emails
                        </span>
                    </label>
                </div>

                <!-- Toggle between Edit and Preview -->
                <div class="flex items-center justify-between border-b border-zinc-200 dark:border-zinc-700 pb-2 flex-wrap gap-2">
                    <div class="flex items-center gap-1 bg-zinc-100 dark:bg-zinc-800 p-1 rounded-lg">
                        <button type="button" id="cp-footer-btn-edit" class="cp-mode-btn px-2.5 py-1 rounded-md text-xs font-semibold text-zinc-500 hover:text-zinc-900 transition-all cursor-pointer [&.is-active]:bg-white dark:[&.is-active]:bg-zinc-900 [&.is-active]:text-zinc-950 dark:[&.is-active]:text-white [&.is-active]:shadow-xs" onclick="setLegalFooterMode('edit')">
                            HTML Source Editor
                        </button>
                        <button type="button" id="cp-footer-btn-preview" class="cp-mode-btn is-active px-2.5 py-1 rounded-md text-xs font-semibold text-zinc-500 hover:text-zinc-900 transition-all cursor-pointer [&.is-active]:bg-white dark:[&.is-active]:bg-zinc-900 [&.is-active]:text-zinc-950 dark:[&.is-active]:text-white [&.is-active]:shadow-xs" onclick="setLegalFooterMode('preview')">
                            Live Render Preview
                        </button>
                    </div>

                    <div class="text-[11px] text-zinc-400 font-mono">
                        Tokens: <code>@{{company_name}}</code>, <code>@{{company_address}}</code>, <code>@{{unsubscribe_link}}</code>
                    </div>
                </div>

                <!-- Textarea for Edit Mode -->
                <div id="cp-footer-edit-pane" class="hidden">
                    <textarea id="cp-legal-footer-html" class="w-full p-3 font-mono text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-xs resize-y leading-relaxed" rows="6" oninput="updateFooterPreviewRender()" placeholder="<div style='font-size: 11px; color: #666;'>&copy; 2026 Acme Corp. All rights reserved. <br/><a href='@{{unsubscribe_link}}'>Unsubscribe</a></div>"></textarea>
                </div>

                <!-- Container for Preview Mode -->
                <div id="cp-footer-preview-pane" class="p-4 bg-white dark:bg-zinc-800/80 rounded-lg border border-zinc-300 dark:border-zinc-700 min-h-[120px] shadow-2xs">
                    <div class="text-[11px] text-zinc-400 border-b border-dashed border-zinc-200 dark:border-zinc-700 pb-2 mb-3">
                        Preview as rendered at email bottom:
                    </div>
                    <div id="cp-footer-rendered-output"></div>
                </div>
            </div>

            <!-- Card 5: DNS Deliverability Advisor -->
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/40 p-5 sm:p-6 space-y-4 shadow-2xs">
                <div>
                    <h3 class="text-sm font-bold flex items-center gap-2 m-0" style="color: var(--text-primary, #09090b);">
                        <i data-lucide="globe" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"></i>
                        <span>DNS Deliverability Advisor (SPF, DKIM &amp; DMARC)</span>
                    </h3>
                    <p class="text-xs mt-1 mb-0 opacity-75" style="color: var(--text-primary, #09090b);">
                        Add these DNS TXT records to your domain provider (Cloudflare, Route53, GoDaddy) for maximum inbox delivery and spam avoidance.
                    </p>
                </div>

                <div class="space-y-3" id="cp-dns-records-container">
                    <!-- Populated via JS -->
                </div>
            </div>

            <!-- Card 6: Interactive Verification Test Delivery Sandbox -->
            <div class="card card-table-wrapper rounded-xl p-5 sm:p-6 shadow-sm border space-y-4" style="background-color: var(--table-bg, #ffffff); border-color: var(--table-border, #e2e8f0); color: var(--text-primary, #09090b);">
                <div>
                    <h3 class="text-sm font-bold flex items-center gap-2 m-0" style="color: var(--text-primary, #09090b);">
                        <i data-lucide="send" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"></i>
                        <span>Interactive Verification Test Delivery Sandbox</span>
                    </h3>
                    <p class="text-xs mt-1 mb-0 opacity-75" style="color: var(--text-primary, #09090b);">
                        Send an end-to-end diagnostic test dispatch to verify authentication, TLS encryption, and deliverability before saving.
                    </p>
                </div>

                <div class="flex items-end gap-3 flex-wrap">
                    <div class="flex-1 min-w-[260px] space-y-1.5">
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Recipient Test Email Address *</label>
                        <input type="email" id="cp-test-recipient-email" class="w-full h-9 px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-xs" placeholder="admin@yourcompany.com" value="admin@acme.com">
                    </div>

                    <button type="button" id="cp-btn-test-delivery" onclick="handleSendTestDelivery()" class="inline-flex items-center gap-2 text-xs font-semibold px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white border border-indigo-700 shadow-sm transition-colors cursor-pointer shrink-0">
                        <i data-lucide="send" class="w-4 h-4"></i>
                        <span id="cp-test-delivery-btn-text">Send Verification Test Email</span>
                    </button>
                </div>

                <!-- Test Result Diagnostic Output -->
                <div id="cp-test-result-box" class="hidden p-4 rounded-xl border space-y-2 transition-all">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <i id="cp-test-result-icon" data-lucide="check-circle-2" class="w-4 h-4"></i>
                            <span id="cp-test-result-title" class="font-bold text-xs"></span>
                        </div>
                        <span id="cp-test-latency-badge" class="inline-flex items-center text-[10px] font-semibold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800">
                            ⚡ Handshake Latency: 142 ms
                        </span>
                    </div>
                    <div id="cp-test-result-details" class="text-xs leading-relaxed font-mono"></div>
                </div>
            </div>

            <!-- Card 7: Centralized Inbound 2-Way Mailbox Sync (Zero-Config for End-Users) -->
            <div class="card card-table-wrapper rounded-xl p-5 sm:p-6 shadow-sm border space-y-4" style="background-color: var(--table-bg, #ffffff); border-color: var(--table-border, #e2e8f0); color: var(--text-primary, #09090b);">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-bold flex items-center gap-2 m-0" style="color: var(--text-primary, #09090b);">
                            <i data-lucide="arrow-right-left" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"></i>
                            <span>Centralized Inbound 2-Way Mailbox Sync</span>
                        </h3>
                        <p class="text-xs mt-1 mb-0 opacity-75" style="color: var(--text-primary, #09090b);">
                            Centralized sync infrastructure. End users are never required to manually configure IMAP or mailbox credentials.
                        </p>
                    </div>

                    <span class="inline-flex items-center text-xs font-semibold px-3 py-1 rounded-full bg-purple-50 text-purple-700 border border-purple-200 dark:bg-purple-950/40 dark:text-purple-300 dark:border-purple-800 shadow-2xs shrink-0">
                        Zero-Config for Reps
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Sync Protocol Relay</label>
                        <select id="cp-inbound-protocol" class="w-full h-9 px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-xs cursor-pointer" onchange="state.emailSettings.inbound_protocol = this.value">
                            <option value="graph">Microsoft 365 Graph API (Exchange Online)</option>
                            <option value="google">Google Workspace Domain-Wide Relay</option>
                            <option value="imap">Secure IMAP TLS (Port 993 Central Relay)</option>
                        </select>
                        <div class="text-[11px] text-zinc-500">
                            Connects to the enterprise tenant once; reads correspondence for all registered sales reps automatically.
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Sync Polling Frequency</label>
                        <select id="cp-inbound-frequency" class="w-full h-9 px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-xs cursor-pointer" onchange="state.emailSettings.inbound_frequency = this.value">
                            <option value="5m">Every 5 Minutes (Standard Enterprise)</option>
                            <option value="15m">Every 15 Minutes</option>
                            <option value="realtime">Real-Time Webhook Event Push</option>
                        </select>
                        <div class="text-[11px] text-zinc-500">
                            Background scheduler checks mailbox changes and updates Lead/Contact activity feeds.
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-zinc-50 dark:bg-zinc-800/40 rounded-xl border border-zinc-200 dark:border-zinc-700 space-y-3">
                    <label class="flex items-center gap-2.5 cursor-pointer text-xs text-zinc-800 dark:text-zinc-200">
                        <input type="checkbox" id="cp-inbound-auto-tag" checked onchange="state.emailSettings.inbound_auto_tag_leads = this.checked" class="w-4 h-4 rounded accent-indigo-600">
                        <span class="font-semibold">Automatic CRM Lead &amp; Contact Matching</span>
                        <span class="text-zinc-500 text-[11px]">— Matches incoming sender emails against database and tags correspondence to customer timelines.</span>
                    </label>
                    <label class="flex items-center gap-2.5 cursor-pointer text-xs text-zinc-800 dark:text-zinc-200">
                        <input type="checkbox" id="cp-inbound-sync-enabled" checked onchange="state.emailSettings.inbound_sync_enabled = this.checked" class="w-4 h-4 rounded accent-indigo-600">
                        <span class="font-semibold">Enable Inbound 2-Way Background Polling Worker</span>
                    </label>
                </div>

                <!-- Test Handshake Button & Result -->
                <div class="flex items-center gap-3 flex-wrap pt-2">
                    <button type="button" id="cp-btn-test-inbound" onclick="handleTestInboundHandshake()" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-medium rounded-lg border shadow-sm transition-colors cursor-pointer shrink-0" style="background-color: var(--table-bg, #ffffff); border-color: var(--table-border, #e2e8f0); color: var(--text-primary, #09090b);">
                        <i id="cp-inbound-spin-icon" data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                        <span id="cp-inbound-btn-text">Test Mailbox Sync Handshake</span>
                    </button>
                    <div id="cp-inbound-result-badge" class="hidden items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold">
                        <i id="cp-inbound-result-icon" data-lucide="check-circle-2" class="w-4 h-4 shrink-0"></i>
                        <span id="cp-inbound-result-text"></span>
                    </div>
                </div>
            </div>

            <!-- Form Actions Footer -->
            <div class="flex justify-end gap-3 pt-3 border-t border-zinc-200 dark:border-zinc-800">
                <button type="submit" class="inline-flex items-center gap-2 text-xs font-semibold px-5 py-2.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white border border-indigo-700 shadow-sm transition-colors cursor-pointer">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Save Gateway Settings</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL: ADD / EDIT CURRENCY                                     -->
<!-- ============================================================== -->
<div id="currency-modal" class="modal-overlay">
    <div class="rounded-2xl shadow-2xl border max-w-lg w-full max-h-[90vh] overflow-y-auto animate-fade-in" style="background-color: var(--table-bg, #ffffff); border-color: var(--table-border, #e2e8f0); color: var(--text-primary, #09090b);">
        <div class="px-6 py-4 border-b flex items-center justify-between sticky top-0 backdrop-blur z-10" style="background-color: var(--table-bg, #ffffff); border-color: var(--table-border, #e2e8f0);">
            <div class="flex items-center gap-2">
                <i data-lucide="coins" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"></i>
                <h3 id="currency-modal-title" class="text-sm font-bold m-0" style="color: var(--text-primary, #09090b);">
                    Add Active World Currency
                </h3>
            </div>
            <button type="button" onclick="closeCurrencyModal()" class="text-zinc-400 hover:text-zinc-600 p-1 rounded-md cursor-pointer transition-colors">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form onsubmit="handleSaveCurrencyForm(event)">
            <div class="p-6 space-y-4">
                <input type="hidden" id="modal-currency-edit-id" value="">

                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Currency Code (3-letter ISO) *</label>
                    <input type="text" id="modal-curr-code" required maxlength="5" class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white uppercase placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-xs" placeholder="e.g. EUR, GBP, JPY, CAD" oninput="handleModalCodeInput(this.value)">
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Currency Symbol *</label>
                    <input type="text" id="modal-curr-symbol" required class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-xs" placeholder="e.g. $, €, £, ¥, ₹">
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Currency Display Name</label>
                    <input type="text" id="modal-curr-name" class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-xs" placeholder="e.g. Euro, British Pound, Singapore Dollar">
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Exchange Rate Relative to Base (<span id="modal-curr-base-code-label">USD</span>) *</label>
                    <input type="number" id="modal-curr-rate" step="0.000001" min="0.000001" required class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-xs" placeholder="e.g. 1.0800" oninput="document.getElementById('modal-rate-note').textContent = '1 unit = ' + this.value + ' ' + (state.tenant.currency_code || 'USD')">
                    <div id="modal-rate-note" class="text-[11px] text-zinc-500">
                        1 unit of this currency = 1.00 USD.
                    </div>
                </div>

                <div class="pt-1">
                    <label class="flex items-center gap-2 cursor-pointer text-xs font-medium text-zinc-800 dark:text-zinc-200">
                        <input type="checkbox" id="modal-curr-active" checked class="w-4 h-4 rounded accent-indigo-600">
                        <span>Active for record transactions</span>
                    </label>
                </div>
            </div>

            <div class="border-t px-6 py-3.5 flex items-center justify-end gap-2 rounded-b-2xl" style="background-color: var(--table-bg, #ffffff); border-color: var(--table-border, #e2e8f0);">
                <button type="button" onclick="closeCurrencyModal()" class="px-3.5 py-1.5 text-xs font-medium rounded-lg border shadow-sm transition-colors cursor-pointer" style="background-color: var(--table-bg, #ffffff); border-color: var(--table-border, #e2e8f0); color: var(--text-primary, #09090b);">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-1.5 text-xs font-semibold rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white border border-indigo-700 shadow-sm transition-colors cursor-pointer">
                    <span id="modal-currency-submit-label">Save Currency</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL: CONFIRM BASE CURRENCY REBASE                            -->
<!-- ============================================================== -->
<div id="confirm-base-modal" class="modal-overlay">
    <div class="rounded-2xl shadow-2xl border max-w-lg w-full max-h-[90vh] overflow-y-auto animate-fade-in" style="background-color: var(--table-bg, #ffffff); border-color: var(--table-border, #e2e8f0); color: var(--text-primary, #09090b);">
        <div class="px-6 py-4 border-b flex items-center justify-between sticky top-0 backdrop-blur z-10" style="background-color: var(--table-bg, #ffffff); border-color: var(--table-border, #e2e8f0);">
            <div class="flex items-center gap-2">
                <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-600 dark:text-amber-400"></i>
                <h3 class="text-sm font-bold m-0" style="color: var(--text-primary, #09090b);">
                    Confirm Base Currency Change
                </h3>
            </div>
            <button type="button" onclick="closeConfirmBaseModal()" class="text-zinc-400 hover:text-zinc-600 p-1 rounded-md cursor-pointer transition-colors">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <div class="p-6 space-y-4">
            <div class="bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 rounded-xl p-4 text-xs text-amber-900 dark:text-amber-200 flex items-start gap-3 leading-relaxed">
                <i data-lucide="alert-circle" class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5"></i>
                <div>
                    <strong>Important Multi-Currency Notice:</strong>
                    <p class="mt-1 mb-0 leading-relaxed">
                        You are changing the organization base accounting currency from <strong id="confirm-old-base-name">USD</strong> to <strong id="confirm-new-base-name">EUR (€)</strong>.
                    </p>
                    <p class="mt-2 mb-0 leading-relaxed text-amber-800 dark:text-amber-300">
                        All existing exchange rates will be automatically rebased to <strong id="confirm-new-base-code">EUR</strong>, and future pipeline rollup totals will be calculated in this currency.
                    </p>
                </div>
            </div>
        </div>

        <div class="border-t px-6 py-3.5 flex items-center justify-end gap-2 rounded-b-2xl" style="background-color: var(--table-bg, #ffffff); border-color: var(--table-border, #e2e8f0);">
            <button type="button" onclick="closeConfirmBaseModal()" class="px-3.5 py-1.5 text-xs font-medium rounded-lg border shadow-sm transition-colors cursor-pointer" style="background-color: var(--table-bg, #ffffff); border-color: var(--table-border, #e2e8f0); color: var(--text-primary, #09090b);">
                Cancel
            </button>
            <button type="button" id="confirm-base-action-btn" onclick="executeSetBaseCurrency()" class="px-4 py-1.5 text-xs font-semibold rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white border border-indigo-700 shadow-sm transition-colors cursor-pointer">
                Confirm &amp; Switch Base
            </button>
        </div>
    </div>
</div>

@php
    $initialTenantData = array_merge(
        $tenant ? $tenant->toArray() : [],
        $companyProfile ? $companyProfile->toArray() : []
    );
    $initialTenantData['id'] = $tenant ? (string)$tenant->id : 'tenant-1';
    $initialTenantData['name'] = $companyProfile->name ?? ($tenant ? $tenant->name : 'Workspace');
    if (isset($companyProfile->appearance_settings)) {
        $initialTenantData['appearance_settings'] = is_string($companyProfile->appearance_settings) 
            ? json_decode($companyProfile->appearance_settings, true) 
            : $companyProfile->appearance_settings;
    }
    $initialTenantData['table_bg_color'] = $companyProfile->table_bg_color ?? '#ffffff';
    $initialTenantData['table_border_color'] = $companyProfile->table_border_color ?? '#e2e8f0';

    $initialEmailData = [
        'email_provider'         => $companyProfile->email_provider ?: 'custom',
        'smtp_host'              => $companyProfile->smtp_host ?: '',
        'smtp_port'              => $companyProfile->smtp_port ? (int)$companyProfile->smtp_port : 587,
        'smtp_user'              => $companyProfile->smtp_user ?: '',
        'smtp_pass'              => $companyProfile->smtp_pass ?: '',
        'has_smtp_pass'          => !empty($companyProfile->smtp_pass),
        'smtp_secure'            => (bool)$companyProfile->smtp_secure,
        'smtp_status'            => $companyProfile->smtp_status ?: 'unconfigured',
        'smtp_last_tested_at'    => $companyProfile->smtp_last_tested_at ? (is_string($companyProfile->smtp_last_tested_at) ? $companyProfile->smtp_last_tested_at : $companyProfile->smtp_last_tested_at->toISOString()) : null,
        'email_from_name'        => $companyProfile->email_from_name ?: ($companyProfile->name ?: ($tenant->name ?? '')),
        'email_from_address'     => $companyProfile->email_from_address ?: ($companyProfile->billing_email ?: ''),
        'email_reply_to'         => $companyProfile->email_reply_to ?: '',
        'sender_identity_policy' => $companyProfile->sender_identity_policy ?: 'org_default',
        'enforce_legal_footer'   => (bool)$companyProfile->enforce_legal_footer,
        'legal_footer_html'      => $companyProfile->legal_footer_html ?: '',
        'inbound_protocol'       => $companyProfile->inbound_protocol ?: 'graph',
        'inbound_frequency'      => $companyProfile->inbound_frequency ?: '5m',
        'inbound_auto_tag_leads' => $companyProfile->inbound_auto_tag_leads ?? true,
        'inbound_sync_enabled'   => $companyProfile->inbound_sync_enabled ?? true,
    ];
@endphp

<!-- Initial Hydration Data for Company Profile Studio -->
<script>
    window.BACKEND_TENANT_DATA = {!! json_encode($initialTenantData) !!};
    window.BACKEND_CURRENCIES = {!! json_encode($currencies ?? []) !!};
    window.BACKEND_EMAIL_SETTINGS = {!! json_encode($initialEmailData) !!};
    window.CSRF_TOKEN = '{{ csrf_token() }}';
</script>

<!-- Dedicated External Page Script -->
<script src="{{ asset('js/company-profile.js') }}?v=1.4"></script>
<script>
    if (typeof initCompanyProfileStudio === 'function') {
        initCompanyProfileStudio();
    }
</script>
@endsection
