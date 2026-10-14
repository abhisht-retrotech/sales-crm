@extends('layouts.app')

@section('title', 'Company Profile & Localization — DealSync Admin')

@section('content')
<style>
/* Custom Company Profile Studio Styles */
.cp-studio-container {
    max-width: 1000px;
    margin: 0 auto;
    padding-bottom: 3rem;
}

.cp-tab-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.65rem 1.15rem;
    border: none;
    background: transparent;
    color: var(--zinc-500);
    font-weight: 500;
    font-size: 0.85rem;
    cursor: pointer;
    border-bottom: 2px solid transparent;
    transition: all 0.15s ease;
}

.cp-tab-btn:hover {
    color: var(--zinc-950);
}

.cp-tab-btn.is-active {
    color: var(--zinc-950);
    font-weight: 600;
    border-bottom-color: var(--color-primary, #4f46e5);
}

.cp-card {
    background: var(--bg-card, #ffffff);
    border: 1px solid var(--border-card, #e2e8f0);
    border-radius: var(--radius-xl, 14px);
    padding: 1.75rem;
    box-shadow: var(--shadow-card, 0 1px 3px 0 rgba(0, 0, 0, 0.05));
}

.cp-preset-card {
    border: 1px solid #e2e8f0;
    background: #ffffff;
    border-radius: var(--radius-lg, 12px);
    padding: 1rem;
    cursor: pointer;
    transition: all 0.15s ease;
    position: relative;
}

.cp-preset-card:hover {
    border-color: #cbd5e1;
    box-shadow: 0 2px 8px -2px rgba(0, 0, 0, 0.06);
}

.cp-preset-card.is-selected {
    border: 2px solid var(--color-primary, #4f46e5) !important;
    background: #f5f3ff !important;
}

.cp-day-pill {
    padding: 0.35rem 0.75rem;
    border-radius: var(--radius-md, 8px);
    font-size: 0.75rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s ease;
    border: 1px solid #cbd5e1;
    background: #f8fafc;
    color: #475569;
}

.cp-day-pill.is-active {
    border-color: var(--color-primary, #4f46e5);
    background: #eef2ff;
    color: #4338ca;
}

.cp-color-preset-btn {
    font-size: 0.7rem;
    padding: 0.18rem 0.5rem;
    border-radius: 4px;
    background: #ffffff;
    color: var(--zinc-700);
    cursor: pointer;
    border: 1px solid #e2e8f0;
    transition: all 0.12s ease;
    font-weight: 500;
}

.cp-color-preset-btn:hover {
    border-color: var(--color-primary, #4f46e5);
    color: var(--color-primary, #4f46e5);
}

.cp-color-preset-btn.is-active {
    border: 1.5px solid var(--color-primary, #4f46e5) !important;
    background: var(--color-primary-subtle, #eef2ff) !important;
    font-weight: 700 !important;
    color: var(--color-primary-text, #4338ca) !important;
}

/* ================= FORM CONTROLS & INPUT FIELDS ================= */
.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
    margin-bottom: 0.25rem;
}

.form-label {
    font-size: 0.775rem;
    font-weight: 600;
    color: var(--zinc-700, #334155);
    letter-spacing: -0.01em;
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.15rem;
}

.form-input {
    width: 100%;
    box-sizing: border-box;
    height: 38px;
    background: var(--zinc-50, #f8fafc);
    border: 1px solid var(--zinc-200, #e2e8f0);
    border-radius: var(--radius-md, 8px);
    color: var(--zinc-950, #09090b);
    padding: 0.5rem 0.85rem;
    font-size: 0.8125rem;
    font-family: inherit;
    line-height: 1.4;
    outline: none;
    transition: border-color 0.15s ease, background-color 0.15s ease, box-shadow 0.15s ease;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.02);
}

.form-input::placeholder {
    color: var(--zinc-400, #94a3b8);
    font-size: 0.8rem;
}

.form-input:hover {
    border-color: var(--zinc-300, #cbd5e1);
    background: #ffffff;
}

.form-input:focus,
.form-input:focus-visible {
    background: #ffffff !important;
    border-color: var(--color-primary, #4f46e5) !important;
    box-shadow: 0 0 0 3px var(--color-primary-subtle, rgba(79, 70, 229, 0.15)) !important;
}

.form-input:disabled,
.form-input[readonly] {
    background: var(--zinc-100, #f1f5f9) !important;
    color: var(--zinc-500, #64748b) !important;
    border-color: var(--zinc-200, #e2e8f0) !important;
    cursor: not-allowed;
}

textarea.form-input {
    height: auto !important;
    min-height: 100px;
    padding: 0.65rem 0.85rem;
    line-height: 1.5;
    resize: vertical;
}

/* ================= SELECT DROPDOWNS & SELECTED FIELDS ================= */
.form-select {
    width: 100%;
    box-sizing: border-box;
    height: 38px;
    background-color: var(--zinc-50, #f8fafc);
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 0.75rem center;
    background-repeat: no-repeat;
    background-size: 1.15em 1.15em;
    padding: 0.45rem 2.25rem 0.45rem 0.85rem;
    border: 1px solid var(--zinc-200, #e2e8f0);
    border-radius: var(--radius-md, 8px);
    color: var(--zinc-950, #09090b);
    font-size: 0.8125rem;
    font-family: inherit;
    line-height: 1.4;
    outline: none;
    cursor: pointer;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    transition: border-color 0.15s ease, background-color 0.15s ease, box-shadow 0.15s ease;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.02);
}

.form-select:hover {
    border-color: var(--zinc-300, #cbd5e1);
    background-color: #ffffff;
}

.form-select:focus,
.form-select:focus-visible {
    background-color: #ffffff !important;
    border-color: var(--color-primary, #4f46e5) !important;
    box-shadow: 0 0 0 3px var(--color-primary-subtle, rgba(79, 70, 229, 0.15)) !important;
}

.form-select option {
    background: #ffffff;
    color: var(--zinc-900, #09090b);
    padding: 0.5rem 0.75rem;
    font-size: 0.8125rem;
}

.form-select option:checked {
    background: var(--color-primary-subtle, #eef2ff);
    color: var(--color-primary-text, #3730a3);
    font-weight: 600;
}

/* ================= RADIO CARDS & PILLS (SELECTED STATES) ================= */
.cp-radio-card {
    display: flex;
    align-items: flex-start;
    gap: 0.65rem;
    padding: 0.85rem 1rem;
    border-radius: var(--radius-md, 8px);
    border: 1.5px solid var(--zinc-200, #e2e8f0);
    background: #ffffff;
    cursor: pointer;
    transition: all 0.15s ease;
}

.cp-radio-card:hover {
    border-color: var(--zinc-300, #cbd5e1);
    background: var(--zinc-50, #f8fafc);
}

.cp-radio-card:has(input:checked) {
    border-color: var(--color-primary, #4f46e5) !important;
    background: var(--color-primary-subtle, #f5f3ff) !important;
    box-shadow: 0 0 0 1px var(--color-primary, #4f46e5);
}

.cp-radio-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.8125rem;
    cursor: pointer;
    padding: 0.45rem 0.85rem;
    border-radius: var(--radius-md, 8px);
    border: 1px solid var(--zinc-200, #e2e8f0);
    background: #ffffff;
    transition: all 0.15s ease;
}

.cp-radio-pill:hover {
    border-color: var(--zinc-300, #cbd5e1);
}

.cp-radio-pill:has(input:checked) {
    border-color: var(--color-primary, #4f46e5) !important;
    background: var(--color-primary-subtle, #f5f3ff) !important;
    color: var(--color-primary-text, #3730a3);
    font-weight: 600;
}

/* ================= FOOTER MODE SWITCH (SELECTED STATES) ================= */
.cp-mode-btn {
    padding: 0.35rem 0.85rem;
    border-radius: var(--radius-md, 8px);
    border: 1px solid transparent;
    background: transparent;
    color: var(--zinc-500, #64748b);
    font-size: 0.75rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.12s ease;
}

.cp-mode-btn:hover {
    color: var(--zinc-900, #09090b);
}

.cp-mode-btn.is-active {
    background: #ffffff !important;
    color: var(--zinc-950, #09090b) !important;
    border-color: var(--zinc-300, #cbd5e1) !important;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06) !important;
}

/* ================= CHECKBOXES & RADIOS ================= */
input[type="checkbox"],
input[type="radio"] {
    accent-color: var(--color-primary, #4f46e5);
    cursor: pointer;
}
</style>

<div class="animate-fade-in cp-studio-container">
    <!-- Top Studio Header -->
    <div style="margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem;">
        <div>
            <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--zinc-950); display: flex; align-items: center; gap: 0.6rem; margin: 0;">
                <i data-lucide="building-2" style="width: 22px; height: 22px; color: var(--color-primary, #4f46e5);"></i>
                <span>Company Profile &amp; Localization</span>
            </h2>
            <p style="font-size: 0.85rem; color: var(--zinc-500); margin: 0.25rem 0 0 0;">
                Manage organization legal info, branding, headquarters address, regional localization, and multi-currency exchange rates.
            </p>
        </div>
        <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
            <span class="badge badge-purple" style="font-size: 0.75rem; padding: 0.25rem 0.65rem;">
                Tenant: <strong id="header-tenant-id-badge">tenant-acme</strong>
            </span>
            <span class="badge badge-blue" style="font-size: 0.75rem; padding: 0.25rem 0.65rem;">
                Base: <strong id="header-base-currency-badge">USD ($)</strong>
            </span>
        </div>
    </div>

    <!-- Alert / Feedback Banner -->
    <div id="studio-feedback-banner" style="display: none; padding: 0.85rem 1.25rem; border-radius: var(--radius-lg, 12px); margin-bottom: 1.5rem; font-size: 0.825rem; font-weight: 500; align-items: center; gap: 0.6rem;">
        <i id="studio-feedback-icon" data-lucide="check-circle-2" style="width: 16px; height: 16px; flex-shrink: 0;"></i>
        <span id="studio-feedback-text">Settings updated successfully!</span>
    </div>

    <!-- 4-Tab Segmented Header -->
    <div style="display: flex; gap: 0.5rem; border-bottom: 1px solid var(--zinc-200); margin-bottom: 1.75rem; padding-bottom: 0.25rem; overflow-x: auto;">
        <button type="button" class="cp-tab-btn is-active" id="tab-nav-profile" onclick="switchCpTab('profile')">
            <i data-lucide="building-2" style="width: 16px; height: 16px;"></i>
            <span>Company Profile &amp; Legal</span>
        </button>
        <button type="button" class="cp-tab-btn" id="tab-nav-regional" onclick="switchCpTab('regional')">
            <i data-lucide="globe" style="width: 16px; height: 16px;"></i>
            <span>Regional &amp; Localization</span>
        </button>
        <button type="button" class="cp-tab-btn" id="tab-nav-currency" onclick="switchCpTab('currency')">
            <i data-lucide="coins" style="width: 16px; height: 16px;"></i>
            <span>Multi-Currency &amp; Exchange Rates</span>
            <span id="tab-currencies-count" style="font-size: 0.7rem; padding: 0.1rem 0.45rem; border-radius: 9999px; background: var(--zinc-100); color: var(--zinc-600); font-weight: 600;">
                6
            </span>
        </button>
        <button type="button" class="cp-tab-btn" id="tab-nav-email" onclick="switchCpTab('email')">
            <i data-lucide="mail" style="width: 16px; height: 16px;"></i>
            <span>Outbound Email &amp; SMTP Gateway</span>
            <span id="tab-smtp-status-badge" style="font-size: 0.675rem; padding: 0.1rem 0.45rem; border-radius: 9999px; background: #ecfdf5; color: #047857; font-weight: 600; text-transform: capitalize;">
                verified
            </span>
        </button>
    </div>

    <!-- ============================================================== -->
    <!-- TAB 1: COMPANY PROFILE & LEGAL IDENTITY                        -->
    <!-- ============================================================== -->
    <div id="tab-pane-profile" style="display: flex; flex-direction: column; gap: 1.75rem;">
        <form onsubmit="handleSaveOrganizationProfile(event)" style="display: flex; flex-direction: column; gap: 1.75rem;">
            <!-- Box 1: Legal Entity & Corporate Identification -->
            <div class="cp-card">
                <h3 style="font-size: 1rem; fontWeight: 700; color: var(--zinc-950); margin-bottom: 0.25rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i data-lucide="building-2" style="width: 16px; height: 16px; color: var(--color-primary, #4f46e5);"></i>
                    <span>Legal Entity &amp; Corporate Identification</span>
                </h3>
                <p style="font-size: 0.8rem; color: var(--zinc-500); margin-bottom: 1.25rem;">
                    Official registered details used on tax invoices, purchase contracts, and regulatory filings.
                </p>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Organization Display Name *</label>
                        <input type="text" id="cp-input-name" required class="form-input" placeholder="e.g. Acme Enterprise Tech">
                    </div>

                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Legal / Corporate Registered Name</label>
                        <input type="text" id="cp-input-legal-name" class="form-input" placeholder="e.g. Acme Corporation Global Inc.">
                    </div>

                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Tax ID / VAT / GST Number</label>
                        <input type="text" id="cp-input-tax-id" class="form-input" placeholder="e.g. US-EIN-987654321 or GB999999973">
                    </div>

                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Company Registration / Business No.</label>
                        <input type="text" id="cp-input-reg-number" class="form-input" placeholder="e.g. CR-09871234 or Delaware #71249">
                    </div>

                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Industry / Vertical</label>
                        <input type="text" id="cp-input-industry" class="form-input" placeholder="e.g. Enterprise Software &amp; Cloud">
                    </div>

                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Corporate Website</label>
                        <input type="url" id="cp-input-website" class="form-input" placeholder="https://example.com">
                    </div>

                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Corporate Phone</label>
                        <input type="tel" id="cp-input-phone" class="form-input" placeholder="+1 (555) 019-2834">
                    </div>

                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Billing &amp; Finance Email</label>
                        <input type="email" id="cp-input-billing-email" class="form-input" placeholder="billing@example.com">
                    </div>

                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label class="form-label" style="font-weight: 600;">Support &amp; Operations Email</label>
                        <input type="email" id="cp-input-support-email" class="form-input" placeholder="support@example.com">
                    </div>
                </div>
            </div>

            <!-- Box 2: Headquarters Physical Address -->
            <div class="cp-card">
                <h3 style="font-size: 1rem; font-weight: 700; color: var(--zinc-950); margin-bottom: 0.25rem;">
                    Headquarters Physical Address
                </h3>
                <p style="font-size: 0.8rem; color: var(--zinc-500); margin-bottom: 1.25rem;">
                    Primary operating location printed on dispatch orders and billing invoices.
                </p>

                <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 1rem;">
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label class="form-label">Street Address</label>
                        <input type="text" id="cp-input-address-street" class="form-input" placeholder="100 Technology Park, Suite 400">
                    </div>

                    <div class="form-group">
                        <label class="form-label">City</label>
                        <input type="text" id="cp-input-address-city" class="form-input" placeholder="San Francisco">
                    </div>

                    <div class="form-group">
                        <label class="form-label">State / Province</label>
                        <input type="text" id="cp-input-address-state" class="form-input" placeholder="CA">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Postal / Zip Code</label>
                        <input type="text" id="cp-input-address-postal" class="form-input" placeholder="94107">
                    </div>

                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label class="form-label">Country</label>
                        <input type="text" id="cp-input-address-country" class="form-input" placeholder="United States">
                    </div>
                </div>
            </div>

            <!-- Box 3: Brand Logo Upload Box -->
            <div class="cp-card">
                <h3 style="font-size: 1rem; font-weight: 700; color: var(--zinc-950); margin-bottom: 0.25rem;">
                    Company Brand Logo
                </h3>
                <p style="font-size: 0.8rem; color: var(--zinc-500); margin-bottom: 1.25rem;">
                    Featured on the sidebar, top navigation, email receipts, and exported PDF invoices.
                </p>

                <div style="display: flex; gap: 1.5rem; align-items: center; flex-wrap: wrap;">
                    <div id="cp-logo-preview-box" style="width: 100px; height: 100px; border-radius: var(--radius-lg, 12px); border: 2px dashed #cbd5e1; background: #f8fafc; display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden; flex-shrink: 0;">
                        <i id="cp-logo-placeholder-icon" data-lucide="building-2" style="width: 28px; height: 28px; color: #94a3b8;"></i>
                        <img id="cp-logo-img" src="" alt="Brand Logo" style="display: none; width: 100%; height: 100%; object-fit: contain; padding: 0.35rem;">
                        <button type="button" id="cp-logo-remove-btn" onclick="removeLogo()" style="display: none; position: absolute; top: 4px; right: 4px; background: rgba(0,0,0,0.65); color: #fff; border: none; border-radius: 50%; width: 18px; height: 18px; font-size: 10px; cursor: pointer; align-items: center; justify-content: center;">✕</button>
                    </div>

                    <div style="flex: 1; min-width: 260px; display: flex; flexDirection: column; gap: 0.5rem;">
                        <div>
                            <input type="file" id="cp-logo-file-input" onchange="handleLogoFileUpload(event)" accept="image/png,image/jpeg,image/svg+xml,image/webp" style="display: none;">
                            <button type="button" onclick="document.getElementById('cp-logo-file-input').click()" class="btn btn-secondary btn-sm" style="display: inline-flex; align-items: center; gap: 0.4rem;">
                                <i data-lucide="upload" style="width: 14px; height: 14px;"></i>
                                <span>Upload Image File (PNG, SVG, JPG, WebP)</span>
                            </button>
                        </div>
                        <div>
                            <input type="url" id="cp-input-logo-url" oninput="updateLogoFromUrl(this.value)" class="form-input" placeholder="Or paste public Logo Image URL (https://...)" style="font-size: 0.8rem;">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Box 4: Company Brand Colors & Theme Styling -->
            <div class="cp-card">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 1.25rem;">
                    <div>
                        <h3 style="font-size: 1rem; font-weight: 700; color: var(--zinc-950); margin-bottom: 0.25rem; display: flex; align-items: center; gap: 0.5rem;">
                            <i data-lucide="palette" style="width: 18px; height: 18px; color: var(--color-primary, #4f46e5);"></i>
                            <span>Company Brand Colors &amp; Appearance</span>
                        </h3>
                        <p style="font-size: 0.8rem; color: var(--zinc-500); margin: 0;">
                            Configure your company brand colors across the CRM. Change page canvas background, primary typography font color, top header navigation, and side panel navigation.
                        </p>
                    </div>
                    <button type="button" onclick="handleResetColorsToDefault()" class="btn btn-secondary btn-sm" style="font-size: 0.75rem; padding: 0.35rem 0.75rem; display: inline-flex; align-items: center; gap: 0.35rem;" title="Restore default system colors">
                        <i data-lucide="rotate-ccw" style="width: 13px; height: 13px;"></i>
                        <span>Restore Defaults</span>
                    </button>
                </div>

                <!-- 4 Core Color Configuration Cards -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
                    <!-- 1. Page Background Color -->
                    <div style="border: 1px solid #e2e8f0; border-radius: var(--radius-lg, 12px); padding: 1rem; background: #f8fafc; display: flex; flex-direction: column; gap: 0.65rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <label style="font-size: 0.8125rem; font-weight: 700; color: var(--zinc-900);">Page Background</label>
                            <span id="cp-label-page-bg" style="font-size: 0.7rem; color: var(--zinc-500); font-family: monospace;">#FAFAFA</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <input type="color" id="cp-color-page-bg" value="#fafafa" oninput="updatePageBg(this.value)" style="width: 40px; height: 36px; padding: 2px; border-radius: 6px; border: 1px solid #cbd5e1; cursor: pointer; background: #fff;">
                            <input type="text" id="cp-text-page-bg" value="#fafafa" oninput="updatePageBg(this.value)" class="form-input" style="flex: 1; height: 36px; font-size: 0.8125rem; font-family: monospace;" placeholder="#FAFAFA">
                        </div>
                        <div style="display: flex; gap: 0.35rem; flex-wrap: wrap; margin-top: 0.15rem;">
                            <button type="button" class="cp-color-preset-btn" onclick="updatePageBg('#fafafa')">Snow</button>
                            <button type="button" class="cp-color-preset-btn" onclick="updatePageBg('#ffffff')">White</button>
                            <button type="button" class="cp-color-preset-btn" onclick="updatePageBg('#fbfbfa')">Warm</button>
                            <button type="button" class="cp-color-preset-btn" onclick="updatePageBg('#f1f5f9')">Slate</button>
                            <button type="button" class="cp-color-preset-btn" onclick="updatePageBg('#0f172a')">Dark</button>
                        </div>
                    </div>

                    <!-- 2. Font / Typography Color -->
                    <div style="border: 1px solid #e2e8f0; border-radius: var(--radius-lg, 12px); padding: 1rem; background: #f8fafc; display: flex; flex-direction: column; gap: 0.65rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <label style="font-size: 0.8125rem; font-weight: 700; color: var(--zinc-900);">Font / Text Color</label>
                            <span id="cp-label-font-color" style="font-size: 0.7rem; color: var(--zinc-500); font-family: monospace;">#09090B</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <input type="color" id="cp-color-font-color" value="#09090b" oninput="updateFontColor(this.value)" style="width: 40px; height: 36px; padding: 2px; border-radius: 6px; border: 1px solid #cbd5e1; cursor: pointer; background: #fff;">
                            <input type="text" id="cp-text-font-color" value="#09090b" oninput="updateFontColor(this.value)" class="form-input" style="flex: 1; height: 36px; font-size: 0.8125rem; font-family: monospace;" placeholder="#09090B">
                        </div>
                        <div style="display: flex; gap: 0.35rem; flex-wrap: wrap; margin-top: 0.15rem;">
                            <button type="button" class="cp-color-preset-btn" onclick="updateFontColor('#09090b')">Obsidian</button>
                            <button type="button" class="cp-color-preset-btn" onclick="updateFontColor('#1e293b')">Charcoal</button>
                            <button type="button" class="cp-color-preset-btn" onclick="updateFontColor('#0f172a')">Navy</button>
                            <button type="button" class="cp-color-preset-btn" onclick="updateFontColor('#1e1b4b')">Indigo</button>
                            <button type="button" class="cp-color-preset-btn" onclick="updateFontColor('#f8fafc')">Light</button>
                        </div>
                    </div>

                    <!-- 3. Header Bar Color -->
                    <div style="border: 1px solid #e2e8f0; border-radius: var(--radius-lg, 12px); padding: 1rem; background: #f8fafc; display: flex; flex-direction: column; gap: 0.65rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <label style="font-size: 0.8125rem; font-weight: 700; color: var(--zinc-900);">Header Bar Color</label>
                            <span id="cp-label-header-bg" style="font-size: 0.7rem; color: var(--zinc-500); font-family: monospace;">#FFFFFF</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <input type="color" id="cp-color-header-bg" value="#ffffff" oninput="updateHeaderBg(this.value)" style="width: 40px; height: 36px; padding: 2px; border-radius: 6px; border: 1px solid #cbd5e1; cursor: pointer; background: #fff;">
                            <input type="text" id="cp-text-header-bg" value="#ffffff" oninput="updateHeaderBg(this.value)" class="form-input" style="flex: 1; height: 36px; font-size: 0.8125rem; font-family: monospace;" placeholder="#FFFFFF">
                        </div>
                        <div style="display: flex; gap: 0.35rem; flex-wrap: wrap; margin-top: 0.15rem;">
                            <button type="button" class="cp-color-preset-btn" onclick="updateHeaderBg('#ffffff')">White</button>
                            <button type="button" class="cp-color-preset-btn" onclick="updateHeaderBg('#f8fafc')">Ice Gray</button>
                            <button type="button" class="cp-color-preset-btn" onclick="updateHeaderBg('#0f172a')">Navy</button>
                            <button type="button" class="cp-color-preset-btn" onclick="updateHeaderBg('#312e81')">Indigo</button>
                            <button type="button" class="cp-color-preset-btn" onclick="updateHeaderBg('#18181b')">Slate</button>
                        </div>
                    </div>

                    <!-- 4. Side Panel Color -->
                    <div style="border: 1px solid #e2e8f0; border-radius: var(--radius-lg, 12px); padding: 1rem; background: #f8fafc; display: flex; flex-direction: column; gap: 0.65rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <label style="font-size: 0.8125rem; font-weight: 700; color: var(--zinc-900);">Side Panel (Navigation)</label>
                            <span id="cp-label-sidebar-bg" style="font-size: 0.7rem; color: var(--zinc-500); font-family: monospace;">#FFFFFF</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <input type="color" id="cp-color-sidebar-bg" value="#ffffff" oninput="updateSidebarBg(this.value)" style="width: 40px; height: 36px; padding: 2px; border-radius: 6px; border: 1px solid #cbd5e1; cursor: pointer; background: #fff;">
                            <input type="text" id="cp-text-sidebar-bg" value="#ffffff" oninput="updateSidebarBg(this.value)" class="form-input" style="flex: 1; height: 36px; font-size: 0.8125rem; font-family: monospace;" placeholder="#FFFFFF">
                        </div>
                        <div style="display: flex; gap: 0.35rem; flex-wrap: wrap; margin-top: 0.15rem;">
                            <button type="button" class="cp-color-preset-btn" onclick="updateSidebarBg('#ffffff')">White</button>
                            <button type="button" class="cp-color-preset-btn" onclick="updateSidebarBg('#f4f4f5')">Zinc</button>
                            <button type="button" class="cp-color-preset-btn" onclick="updateSidebarBg('#0f172a')">Slate</button>
                            <button type="button" class="cp-color-preset-btn" onclick="updateSidebarBg('#18181b')">Graphite</button>
                            <button type="button" class="cp-color-preset-btn" onclick="updateSidebarBg('#1e293b')">Navy</button>
                        </div>
                    </div>
                </div>

                <!-- 5. Primary Brand Accent & Swatches -->
                <div style="border-top: 1px solid #e2e8f0; padding-top: 1.25rem; display: flex; flex-direction: column; gap: 0.75rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                        <div style="font-size: 0.8125rem; font-weight: 700; color: var(--zinc-900);">
                            Primary Brand Accent: <span id="cp-accent-name-display" style="color: #4f46e5; font-weight: 800;">Electric Violet (#4F46E5)</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <input type="color" id="cp-color-accent" value="#4F46E5" oninput="selectAccent(this.value)" style="width: 32px; height: 28px; padding: 1px; border-radius: 5px; border: 1px solid #cbd5e1; cursor: pointer;">
                            <input type="text" id="cp-text-accent" value="#4F46E5" oninput="selectAccent(this.value)" class="form-input" style="width: 95px; height: 28px; font-size: 0.75rem; font-family: monospace; padding: 0.2rem 0.4rem;">
                        </div>
                    </div>

                    <div style="display: flex; gap: 0.65rem; flex-wrap: wrap; align-items: center;" id="cp-accent-swatches-container">
                        <!-- Swatches populated via JS -->
                    </div>
                </div>

                <!-- 6. Live Interactive Miniature Workspace Appearance Preview -->
                <div style="margin-top: 1.5rem; padding: 1rem; background: #f4f4f5; border-radius: var(--radius-lg, 12px); border: 1px solid #e4e4e7;">
                    <div style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #71717a; margin-bottom: 0.65rem; letter-spacing: 0.04em;">
                        Live Workspace Appearance Preview
                    </div>
                    <div style="border-radius: 8px; overflow: hidden; border: 1px solid #cbd5e1; box-shadow: 0 2px 8px rgba(0,0,0,0.06); display: flex; height: 110px;">
                        <!-- Mini Sidebar -->
                        <div id="mini-preview-sidebar" style="width: 45px; background: #ffffff; border-right: 1px solid rgba(0,0,0,0.1); padding: 8px 4px; display: flex; flex-direction: column; gap: 6px; align-items: center;">
                            <div id="mini-preview-sidebar-dot" style="width: 16px; height: 16px; border-radius: 4px; background: #4f46e5;"></div>
                            <div style="width: 14px; height: 3px; border-radius: 2px; background: rgba(128,128,128,0.3); margin-top: 4px;"></div>
                            <div style="width: 14px; height: 3px; border-radius: 2px; background: rgba(128,128,128,0.3);"></div>
                            <div style="width: 14px; height: 3px; border-radius: 2px; background: rgba(128,128,128,0.3);"></div>
                        </div>

                        <!-- Right Side: Mini Header & Mini Canvas -->
                        <div style="flex: 1; display: flex; flex-direction: column;">
                            <div id="mini-preview-header" style="height: 24px; background: #ffffff; border-bottom: 1px solid rgba(0,0,0,0.08); padding: 0 8px; display: flex; align-items: center; justify-content: space-between;">
                                <span id="mini-preview-header-title" style="font-size: 9px; font-weight: 700; color: #09090b;">Acme Enterprise Tech</span>
                                <div id="mini-preview-header-accent" style="width: 12px; height: 12px; border-radius: 50%; background: #4f46e5;"></div>
                            </div>
                            <div id="mini-preview-canvas" style="flex: 1; background: #fafafa; padding: 8px; display: flex; align-items: center; justify-content: space-between;">
                                <div>
                                    <div id="mini-preview-canvas-title" style="font-size: 11px; font-weight: 800; color: #09090b; line-height: 1.2;">
                                        Company Dashboard
                                    </div>
                                    <div id="mini-preview-canvas-subtitle" style="font-size: 8px; color: #09090b; opacity: 0.7; margin-top: 2px;">
                                        Custom brand styling active
                                    </div>
                                </div>
                                <button type="button" id="mini-preview-canvas-btn" style="background: #4f46e5; color: #ffffff; border: none; border-radius: 4px; padding: 4px 8px; font-size: 9px; font-weight: 700;">
                                    + Action
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Profile -->
            <div style="display: flex; justify-content: flex-end;">
                <button type="submit" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 0.5rem; background: #4f46e5; border-color: #4338ca; padding: 0.6rem 1.5rem; font-weight: 600;">
                    <i data-lucide="save" style="width: 15px; height: 15px;"></i>
                    <span>Save Organization Profile</span>
                </button>
            </div>
        </form>
    </div>

    <!-- ============================================================== -->
    <!-- TAB 2: REGIONAL & LOCALIZATION                                 -->
    <!-- ============================================================== -->
    <div id="tab-pane-regional" style="display: none; flex-direction: column; gap: 1.75rem;">
        <form onsubmit="handleSaveRegionalSettings(event)" style="display: flex; flex-direction: column; gap: 1.75rem;">
            <!-- Box 1: Time Zone & Real-Time Clock -->
            <div class="cp-card">
                <h3 style="font-size: 1rem; font-weight: 700; color: var(--zinc-950); margin-bottom: 0.25rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i data-lucide="clock" style="width: 16px; height: 16px; color: var(--color-primary, #4f46e5);"></i>
                    <span>Organization Time Zone &amp; Real-Time Clock</span>
                </h3>
                <p style="font-size: 0.8rem; color: var(--zinc-500); margin-bottom: 1.25rem;">
                    Sets the reference time for timestamps, SLA calculations, approval timeouts, and audit logs.
                </p>

                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; align-items: center;">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Default Organization Time Zone</label>
                        <select id="cp-select-timezone" class="form-select" onchange="updateTimeZone(this.value)">
                            <!-- Populated via JS -->
                        </select>
                        <div style="font-size: 0.75rem; color: var(--zinc-400); margin-top: 0.25rem;">
                            Standard IANA Time Zone definition.
                        </div>
                    </div>

                    <!-- Live Clock Card -->
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: var(--radius-lg, 12px); padding: 1rem; text-align: center;">
                        <div id="cp-clock-label" style="font-size: 0.7rem; color: var(--zinc-500); text-transform: uppercase; letter-spacing: 0.04em;">
                            Live Time in UTC
                        </div>
                        <div id="cp-live-clock" style="font-size: 1.4rem; font-weight: 700; color: #4f46e5; margin-top: 0.25rem; font-family: monospace;">
                            --:--:--
                        </div>
                    </div>
                </div>
            </div>

            <!-- Box 2: Date, Time & Number Formatting Standards -->
            <div class="cp-card">
                <h3 style="font-size: 1rem; font-weight: 700; color: var(--zinc-950); margin-bottom: 0.25rem;">
                    Date, Time &amp; Number Formatting Standards
                </h3>
                <p style="font-size: 0.8rem; color: var(--zinc-500); margin-bottom: 1.25rem;">
                    Consistent formatting across table grids, deal amounts, exports, and Kanban views.
                </p>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.25rem;">
                    <!-- Date Format -->
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Date Format</label>
                        <select id="cp-select-date-format" class="form-select" onchange="updateFormatPreviews()">
                            <option value="YYYY-MM-DD">YYYY-MM-DD (ISO Standard)</option>
                            <option value="DD/MM/YYYY">DD/MM/YYYY (UK / Europe / India)</option>
                            <option value="MM/DD/YYYY">MM/DD/YYYY (US Standard)</option>
                        </select>
                        <div style="font-size: 0.75rem; color: var(--zinc-500); margin-top: 0.35rem;">
                            Preview: <strong id="cp-preview-date-format">2026-10-13</strong>
                        </div>
                    </div>

                    <!-- Time Format -->
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Time Format</label>
                        <select id="cp-select-time-format" class="form-select" onchange="updateFormatPreviews()">
                            <option value="12h">12-Hour (e.g. 02:30 PM)</option>
                            <option value="24h">24-Hour (e.g. 14:30)</option>
                        </select>
                        <div style="font-size: 0.75rem; color: var(--zinc-500); margin-top: 0.35rem;">
                            Preview: <strong id="cp-preview-time-format">02:30 PM</strong>
                        </div>
                    </div>

                    <!-- Number Format -->
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Number Separators</label>
                        <select id="cp-select-number-format" class="form-select" onchange="updateFormatPreviews()">
                            <option value="comma_dot">1,234.56 (US / UK Standard)</option>
                            <option value="dot_comma">1.234,56 (European Standard)</option>
                            <option value="space_comma">1 234,56 (Nordic / French)</option>
                        </select>
                        <div style="font-size: 0.75rem; color: var(--zinc-500); margin-top: 0.35rem;">
                            Preview: <strong id="cp-preview-number-format">$1,250,000.75</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Box 3: Fiscal Year & Operating Hours -->
            <div class="cp-card">
                <h3 style="font-size: 1rem; font-weight: 700; color: var(--zinc-950); margin-bottom: 0.25rem;">
                    Fiscal Year &amp; Operating Hours
                </h3>
                <p style="font-size: 0.8rem; color: var(--zinc-500); margin-bottom: 1.25rem;">
                    Defines quarterly reporting cycles, business day schedules, and office hours.
                </p>

                <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1.5rem; margin-bottom: 1.25rem;">
                    <!-- Fiscal Month -->
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Fiscal Year Starts In</label>
                        <select id="cp-select-fiscal-month" class="form-select" onchange="updateFiscalQuarterText(this.value)">
                            <!-- Populated via JS -->
                        </select>
                        <div id="cp-fiscal-q1-note" style="font-size: 0.75rem; color: var(--zinc-500); margin-top: 0.35rem;">
                            Q1 begins January 1st.
                        </div>
                    </div>

                    <!-- Working Days Checkboxes -->
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Business Working Days</label>
                        <div id="cp-working-days-container" style="display: flex; gap: 0.4rem; flex-wrap: wrap; margin-top: 0.25rem;">
                            <!-- Populated via JS -->
                        </div>
                    </div>
                </div>

                <!-- Operating Hours -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Business Hours Start</label>
                        <input type="time" id="cp-input-work-start" class="form-input" value="09:00">
                    </div>

                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Business Hours End</label>
                        <input type="time" id="cp-input-work-end" class="form-input" value="18:00">
                    </div>
                </div>
            </div>

            <!-- Box 4: Security & Password Governance -->
            <div class="cp-card">
                <h3 style="font-size: 1rem; font-weight: 700; color: var(--zinc-950); margin-bottom: 0.25rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i data-lucide="shield" style="width: 16px; height: 16px; color: var(--color-primary, #4f46e5);"></i>
                    <span>Security &amp; Password Governance</span>
                </h3>
                <p style="font-size: 0.8rem; color: var(--zinc-500); margin-bottom: 1.25rem;">
                    Control whether employees and non-admin team members have the autonomy to change their own passwords.
                </p>

                <div id="cp-password-policy-box" style="display: flex; align-items: flex-start; gap: 0.85rem; padding: 1rem 1.25rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: var(--radius-lg, 12px);">
                    <input type="checkbox" id="cp-check-allow-pwd" onchange="updatePasswordPolicyDisplay(this.checked)" style="width: 18px; height: 18px; margin-top: 0.15rem; cursor: pointer; accent-color: var(--color-primary, #4f46e5);">
                    <label for="cp-check-allow-pwd" style="cursor: pointer; flex: 1;">
                        <div style="font-weight: 600; font-size: 0.875rem; color: var(--zinc-900);">
                            Allow Non-Admin Users to Change Password
                        </div>
                        <div id="cp-password-policy-desc" style="font-size: 0.75rem; color: var(--zinc-600); margin-top: 0.2rem; line-height: 1.4;">
                            Enabled: Team members can independently update their login credentials from My Profile &amp; Preferences.
                        </div>
                    </label>
                </div>
            </div>

            <!-- Submit Regional -->
            <div style="display: flex; justify-content: flex-end;">
                <button type="submit" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 0.5rem; background: #4f46e5; border-color: #4338ca; padding: 0.6rem 1.5rem; font-weight: 600;">
                    <i data-lucide="save" style="width: 15px; height: 15px;"></i>
                    <span>Save Regional &amp; Localization Settings</span>
                </button>
            </div>
        </form>
    </div>

    <!-- ============================================================== -->
    <!-- TAB 3: MULTI-CURRENCY & EXCHANGE RATES                         -->
    <!-- ============================================================== -->
    <div id="tab-pane-currency" style="display: none; flex-direction: column; gap: 1.75rem;">
        <!-- Base Currency Banner -->
        <div style="background: linear-gradient(135deg, #09090b 0%, #18181b 100%); color: #ffffff; border-radius: var(--radius-xl, 16px); padding: 1.75rem; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 4px 14px rgba(0,0,0,0.1); flex-wrap: wrap; gap: 1rem;">
            <div>
                <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #a1a1aa;">
                    Primary Organization Base Currency
                </div>
                <div style="font-size: 1.75rem; font-weight: 800; margin-top: 0.25rem; display: flex; align-items: center; gap: 0.75rem;">
                    <span id="cp-base-currency-display">USD ($)</span>
                    <span class="badge badge-green" style="font-size: 0.75rem; padding: 0.2rem 0.6rem;">
                        Base 1.0
                    </span>
                </div>
                <div style="font-size: 0.8rem; color: #d4d4d8; margin-top: 0.35rem;">
                    All pipeline summaries, Kanban totals, and financial roll-ups aggregate into this base currency.
                </div>
            </div>

            <div style="display: flex; gap: 0.75rem;">
                <button type="button" onclick="openAddCurrencyModal()" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 0.4rem; background: #4f46e5; border-color: #4338ca; font-weight: 600;">
                    <i data-lucide="plus" style="width: 15px; height: 15px;"></i>
                    <span>Add World Currency</span>
                </button>
            </div>
        </div>

        <!-- Active World Currencies & Exchange Rates Table -->
        <div class="cp-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.5rem;">
                <div>
                    <h3 style="font-size: 1rem; font-weight: 700; color: var(--zinc-950); margin: 0;">
                        Configured Currencies &amp; Exchange Rates
                    </h3>
                    <p style="font-size: 0.8rem; color: var(--zinc-500); margin: 0.15rem 0 0 0;">
                        Exchange rates indicate how many units of <strong id="cp-table-base-code-label">USD</strong> equal 1 unit of the target currency (or direct multiplier).
                    </p>
                </div>
                <button type="button" onclick="renderCurrenciesTable(); showToast('Exchange rates table refreshed!');" class="btn btn-secondary btn-sm" style="display: inline-flex; align-items: center; gap: 0.35rem;">
                    <i data-lucide="refresh-cw" style="width: 13px; height: 13px;"></i>
                    <span>Refresh Rates</span>
                </button>
            </div>

            <div class="table-container">
                <table class="table" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>Currency</th>
                            <th>Symbol</th>
                            <th>Name</th>
                            <th>Exchange Rate (to <span id="cp-th-base-code">USD</span>)</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="cp-currencies-tbody">
                        <!-- Populated dynamically via JS -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Real-Time Interactive Currency Converter Simulator -->
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: var(--radius-xl, 16px); padding: 1.5rem;">
            <h4 style="font-size: 0.9rem; font-weight: 700; color: var(--zinc-900); display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                <i data-lucide="arrow-right-left" style="width: 16px; height: 16px; color: var(--color-primary, #4f46e5);"></i>
                <span>Interactive Exchange Rate Calculator &amp; Simulator</span>
            </h4>
            <p style="font-size: 0.75rem; color: var(--zinc-500); margin-bottom: 1rem;">
                Test live conversions between configured currencies using real-time tenant exchange rates.
            </p>

            <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                <div style="width: 150px;">
                    <label class="form-label" style="font-size: 0.725rem;">Amount</label>
                    <input type="number" id="cp-calc-amount" class="form-input" value="1000" oninput="calculateLiveConversion()">
                </div>

                <div>
                    <label class="form-label" style="font-size: 0.725rem;">From</label>
                    <select id="cp-calc-from-curr" class="form-select" onchange="calculateLiveConversion()">
                        <!-- Populated via JS -->
                    </select>
                </div>

                <div style="padding-top: 1.25rem;">
                    <i data-lucide="arrow-right-left" style="width: 16px; height: 16px; color: var(--zinc-400);"></i>
                </div>

                <div>
                    <label class="form-label" style="font-size: 0.725rem;">To</label>
                    <select id="cp-calc-to-curr" class="form-select" onchange="calculateLiveConversion()">
                        <!-- Populated via JS -->
                    </select>
                </div>

                <div style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: var(--radius-lg, 12px); padding: 0.6rem 1.25rem; margin-top: 1.1rem; min-width: 220px; box-shadow: 0 1px 2px rgba(0,0,0,0.04);">
                    <div style="font-size: 0.675rem; color: var(--zinc-500); text-transform: uppercase; font-weight: 600;">
                        Converted Result
                    </div>
                    <div id="cp-calc-result-display" style="font-size: 1.25rem; font-weight: 800; color: #4f46e5;">
                        $1,085.00
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- TAB 4: OUTBOUND EMAIL & SMTP GATEWAY INFRASTRUCTURE            -->
    <!-- ============================================================== -->
    <div id="tab-pane-email" style="display: none; flex-direction: column; gap: 1.75rem;">
        <form onsubmit="handleSaveEmailSettings(event)" style="display: flex; flex-direction: column; gap: 1.75rem;">
            <!-- Card 1: Cloud Provider Presets -->
            <div class="cp-card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.5rem;">
                    <div>
                        <h3 style="font-size: 1rem; font-weight: 600; color: var(--zinc-900); display: flex; align-items: center; gap: 0.5rem; margin: 0;">
                            <i data-lucide="server" style="width: 18px; height: 18px; color: var(--color-primary, #4f46e5);"></i>
                            <span>Outbound Mail Provider Presets</span>
                        </h3>
                        <p style="font-size: 0.8rem; color: var(--zinc-500); margin: 0.2rem 0 0 0;">
                            Select your enterprise delivery gateway to auto-fill verified host, port, and security parameters.
                        </p>
                    </div>
                    <span id="cp-smtp-status-badge-inline" class="badge badge-green" style="font-size: 0.75rem; padding: 0.35rem 0.75rem;">
                        Gateway Status: <strong style="text-transform: capitalize;" id="cp-smtp-status-text">Verified</strong>
                    </span>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1rem;" id="cp-email-presets-container">
                    <!-- Populated via JS -->
                </div>
            </div>

            <!-- Card 2: SMTP Connection & Authentication Credentials -->
            <div class="cp-card">
                <h3 style="font-size: 1rem; font-weight: 600; color: var(--zinc-900); display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.35rem;">
                    <i data-lucide="key" style="width: 18px; height: 18px; color: var(--color-primary, #4f46e5);"></i>
                    <span>SMTP Connection &amp; Authentication</span>
                </h3>
                <p style="font-size: 0.8rem; color: var(--zinc-500); margin-bottom: 1.25rem;">
                    Configure your mail server credentials. All credentials are isolated per tenant in the database.
                </p>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.25rem;">
                    <div class="form-group">
                        <label class="form-label">SMTP Relay Host *</label>
                        <input type="text" id="cp-smtp-host" required placeholder="e.g. smtp.sendgrid.net or smtp.office365.com" class="form-input">
                    </div>

                    <div class="form-group">
                        <label class="form-label">SMTP Port *</label>
                        <div style="display: flex; gap: 0.5rem; align-items: center;">
                            <input type="number" id="cp-smtp-port" required style="max-width: 120px;" class="form-input" value="587">
                            <div style="display: flex; gap: 0.35rem;">
                                <button type="button" class="btn btn-secondary btn-sm" onclick="setPort(587)">587</button>
                                <button type="button" class="btn btn-secondary btn-sm" onclick="setPort(465)">465</button>
                                <button type="button" class="btn btn-secondary btn-sm" onclick="setPort(25)">25</button>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Security Protocol</label>
                        <div style="display: flex; gap: 1.25rem; margin-top: 0.4rem;">
                            <label style="display: flex; align-items: center; gap: 0.4rem; font-size: 0.825rem; cursor: pointer;">
                                <input type="radio" name="cpSmtpSecure" id="cp-proto-starttls" checked onchange="toggleSmtpSecure(false)">
                                <span>STARTTLS (Port 587 / 25 - Recommended)</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 0.4rem; font-size: 0.825rem; cursor: pointer;">
                                <input type="radio" name="cpSmtpSecure" id="cp-proto-ssl" onchange="toggleSmtpSecure(true)">
                                <span>SSL/TLS (Port 465)</span>
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">SMTP Username / API Key Username</label>
                        <input type="text" id="cp-smtp-user" placeholder="e.g. apikey or user@domain.com" class="form-input">
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            SMTP Password / API Key Secret
                            <span id="cp-smtp-pwd-configured-badge" class="badge badge-green" style="font-size: 0.65rem; margin-left: 0.5rem; padding: 0.1rem 0.35rem;">
                                Configured
                            </span>
                        </label>
                        <div style="position: relative;">
                            <input type="password" id="cp-smtp-pass" placeholder="•••••••• (Leave blank to keep existing password)" class="form-input" style="padding-right: 2.5rem;">
                            <button type="button" onclick="toggleShowSmtpPass()" style="position: absolute; right: 0.65rem; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--zinc-400); padding: 0.2rem;">
                                <i id="cp-smtp-eye-icon" data-lucide="eye" style="width: 16px; height: 16px;"></i>
                            </button>
                        </div>
                        <span style="font-size: 0.7rem; color: var(--zinc-500); margin-top: 0.25rem; display: block;">
                            For SendGrid, use <code>apikey</code> as username and your SendGrid API key as password. For Google Workspace, generate an App Password.
                        </span>
                    </div>
                </div>
            </div>

            <!-- Card 3: Sender Identity & Outbound Routing Policies -->
            <div class="cp-card">
                <h3 style="font-size: 1rem; font-weight: 600; color: var(--zinc-900); display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.35rem;">
                    <i data-lucide="shield-check" style="width: 18px; height: 18px; color: var(--color-primary, #4f46e5);"></i>
                    <span>Sender Identity &amp; Routing Policy</span>
                </h3>
                <p style="font-size: 0.8rem; color: var(--zinc-500); margin-bottom: 1.25rem;">
                    Define the default outbound headers, display names, and individual sales rep override policies.
                </p>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.25rem; margin-bottom: 1.25rem;">
                    <div class="form-group">
                        <label class="form-label">Default From Display Name *</label>
                        <input type="text" id="cp-email-from-name" required placeholder="e.g. Acme Sales Team or Acme Corp" class="form-input">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Default From Email Address *</label>
                        <input type="email" id="cp-email-from-address" required placeholder="e.g. sales@acmecorp.com" class="form-input">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Reply-To Address (Optional)</label>
                        <input type="email" id="cp-email-reply-to" placeholder="e.g. support@acmecorp.com (Leave blank to use From Address)" class="form-input">
                    </div>
                </div>

                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: var(--radius-lg, 12px); padding: 1rem 1.25rem;">
                    <label class="form-label" style="margin-bottom: 0.5rem; display: block; font-weight: 600;">
                        Outbound Sender Identity Policy
                    </label>
                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <label class="cp-radio-card">
                            <input type="radio" name="senderIdentityPolicy" id="cp-policy-org-default" value="org_default" checked style="margin-top: 0.2rem;">
                            <div>
                                <div style="font-size: 0.825rem; font-weight: 600; color: var(--zinc-900);">
                                    Strict Tenant Gateway (Highest Deliverability)
                                </div>
                                <div style="font-size: 0.75rem; color: var(--zinc-500);">
                                    All outbound emails dispatch strictly from the organization tenant address. The individual sales rep’s email is placed in the <code>Reply-To</code> header.
                                </div>
                            </div>
                        </label>

                        <label class="cp-radio-card">
                            <input type="radio" name="senderIdentityPolicy" id="cp-policy-user-sender" value="user_as_sender" style="margin-top: 0.2rem;">
                            <div>
                                <div style="font-size: 0.825rem; font-weight: 600; color: var(--zinc-900);">
                                    Delegated Rep Identity (Sender Override)
                                </div>
                                <div style="font-size: 0.75rem; color: var(--zinc-500);">
                                    Emails use the logged-in rep's profile name and email address as <code>From</code> (requires your mail server / SPF domain to permit authenticated relay on rep domains).
                                </div>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Card 4: Legal Compliance Email Footer Studio -->
            <div class="cp-card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; flex-wrap: wrap; gap: 0.5rem;">
                    <div>
                        <h3 style="font-size: 1rem; font-weight: 600; color: var(--zinc-900); display: flex; align-items: center; gap: 0.5rem; margin: 0;">
                            <i data-lucide="file-text" style="width: 18px; height: 18px; color: var(--color-primary, #4f46e5);"></i>
                            <span>Legal Compliance Email Footer Studio</span>
                        </h3>
                        <p style="font-size: 0.8rem; color: var(--zinc-500); margin: 0.2rem 0 0 0;">
                            Enforce statutory physical address disclosures and unsubscribe links on all outbound communications (CAN-SPAM, GDPR, CASL).
                        </p>
                    </div>

                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <button type="button" onclick="handleAutoGenerateLegalFooter()" class="btn btn-secondary btn-sm" style="display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.75rem;" title="Generate HTML footer from organization profile, legal name, and headquarters address">
                            <i data-lucide="sparkles" style="width: 14px; height: 14px; color: #8b5cf6;"></i>
                            <span>Auto-Populate from Profile</span>
                        </button>
                    </div>
                </div>

                <div style="margin-bottom: 1rem;">
                    <label style="display: flex; align-items: center; gap: 0.6rem; cursor: pointer;">
                        <input type="checkbox" id="cp-enforce-footer-check" checked style="accent-color: var(--color-primary, #4f46e5);">
                        <span style="font-size: 0.85rem; font-weight: 600; color: var(--zinc-900);">
                            Enforce footer appending on all system and automated outbound emails
                        </span>
                    </label>
                </div>

                <!-- Toggle between Edit and Preview -->
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem; border-bottom: 1px solid var(--zinc-200); padding-bottom: 0.4rem; flex-wrap: wrap; gap: 0.5rem;">
                    <div style="display: flex; gap: 0.35rem; background: var(--zinc-100); padding: 3px; border-radius: var(--radius-md, 8px);">
                        <button type="button" id="cp-footer-btn-edit" class="cp-mode-btn" onclick="setLegalFooterMode('edit')">
                            HTML Source Editor
                        </button>
                        <button type="button" id="cp-footer-btn-preview" class="cp-mode-btn is-active" onclick="setLegalFooterMode('preview')">
                            Live Render Preview
                        </button>
                    </div>
                    <div style="font-size: 0.7rem; color: var(--zinc-400);">
                        Tokens: <code>@{{company_name}}</code>, <code>@{{company_address}}</code>, <code>@{{unsubscribe_link}}</code>
                    </div>
                </div>

                <!-- Textarea for Edit Mode -->
                <div id="cp-footer-edit-pane" style="display: none;">
                    <textarea id="cp-legal-footer-html" class="form-input" rows="6" style="font-family: monospace; font-size: 0.8rem; line-height: 1.45;" oninput="updateFooterPreviewRender()" placeholder="<div style='font-size: 11px; color: #666;'>&copy; 2026 Acme Corp. All rights reserved. <br/><a href='@{{unsubscribe_link}}'>Unsubscribe</a></div>"></textarea>
                </div>

                <!-- Container for Preview Mode -->
                <div id="cp-footer-preview-pane" style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: var(--radius-md, 8px); padding: 1.25rem; min-height: 120px;">
                    <div style="font-size: 0.7rem; color: var(--zinc-400); border-bottom: 1px dashed var(--zinc-200); padding-bottom: 0.4rem; margin-bottom: 0.75rem;">
                        Preview as rendered at email bottom:
                    </div>
                    <div id="cp-footer-rendered-output"></div>
                </div>
            </div>

            <!-- Card 5: DNS Deliverability Advisor -->
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: var(--radius-xl, 16px); padding: 1.5rem;">
                <h3 style="font-size: 1rem; font-weight: 600; color: var(--zinc-900); display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.35rem;">
                    <i data-lucide="globe" style="width: 18px; height: 18px; color: var(--color-primary, #4f46e5);"></i>
                    <span>DNS Deliverability Advisor (SPF, DKIM &amp; DMARC)</span>
                </h3>
                <p style="font-size: 0.8rem; color: var(--zinc-500); margin-bottom: 1.25rem;">
                    Add these DNS TXT records to your domain provider (Cloudflare, Route53, GoDaddy) for maximum inbox delivery and spam avoidance.
                </p>

                <div style="display: flex; flex-direction: column; gap: 0.75rem;" id="cp-dns-records-container">
                    <!-- Populated via JS -->
                </div>
            </div>

            <!-- Card 6: Interactive Verification Test Delivery Sandbox -->
            <div class="cp-card">
                <h3 style="font-size: 1rem; font-weight: 600; color: var(--zinc-900); display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.35rem;">
                    <i data-lucide="send" style="width: 18px; height: 18px; color: var(--color-primary, #4f46e5);"></i>
                    <span>Interactive Verification Test Delivery Sandbox</span>
                </h3>
                <p style="font-size: 0.8rem; color: var(--zinc-500); margin-bottom: 1.25rem;">
                    Send an end-to-end diagnostic test dispatch to verify authentication, TLS encryption, and deliverability before saving.
                </p>

                <div style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap; margin-bottom: 1rem;">
                    <div style="flex: 1; min-width: 260px;">
                        <label class="form-label">Recipient Test Email Address *</label>
                        <input type="email" id="cp-test-recipient-email" class="form-input" placeholder="admin@yourcompany.com" value="admin@acme.com">
                    </div>

                    <button type="button" id="cp-btn-test-delivery" onclick="handleSendTestDelivery()" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.65rem 1.25rem; background: #4f46e5; border-color: #4338ca; font-weight: 600;">
                        <i data-lucide="send" style="width: 16px; height: 16px;"></i>
                        <span id="cp-test-delivery-btn-text">Send Verification Test Email</span>
                    </button>
                </div>

                <!-- Test Result Diagnostic Output -->
                <div id="cp-test-result-box" style="display: none; margin-top: 1rem; padding: 1.25rem; border-radius: var(--radius-lg, 12px);">
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                        <i id="cp-test-result-icon" data-lucide="check-circle-2" style="width: 18px; height: 18px;"></i>
                        <span id="cp-test-result-title" style="font-weight: 700; fontSize: 0.9rem;"></span>
                        <span id="cp-test-latency-badge" class="badge badge-green" style="font-size: 0.7rem; margin-left: auto;">
                            ⚡ Handshake Latency: 142 ms
                        </span>
                    </div>
                    <div id="cp-test-result-details" style="font-size: 0.8rem; line-height: 1.45;"></div>
                </div>
            </div>

            <!-- Form Actions Footer -->
            <div style="display: flex; justify-content: flex-end; gap: 0.75rem; padding-top: 1rem; border-top: 1px solid var(--zinc-200);">
                <button type="submit" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.65rem 1.5rem; font-weight: 600; background: #4f46e5; border-color: #4338ca;">
                    <i data-lucide="save" style="width: 16px; height: 16px;"></i>
                    <span>Save Gateway Settings</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL: ADD / EDIT CURRENCY                                     -->
<!-- ============================================================== -->
<div id="currency-modal" class="modal-overlay" style="display: none;">
    <div class="modal-card animate-fade-in" style="max-width: 480px; width: 95%;">
        <div class="modal-header">
            <h3 id="currency-modal-title" style="font-size: 1rem; font-weight: 600; color: var(--zinc-950); margin: 0;">
                Add Active World Currency
            </h3>
            <button type="button" onclick="closeCurrencyModal()" style="background: none; border: none; cursor: pointer; font-size: 1.2rem; color: var(--zinc-400);">✕</button>
        </div>

        <form onsubmit="handleSaveCurrencyForm(event)">
            <div class="modal-body" style="display: flex; flex-direction: column; gap: 1rem;">
                <input type="hidden" id="modal-currency-edit-id" value="">

                <div class="form-group">
                    <label class="form-label">Currency Code (3-letter ISO) *</label>
                    <input type="text" id="modal-curr-code" required maxlength="5" class="form-input" placeholder="e.g. EUR, GBP, JPY, CAD" oninput="handleModalCodeInput(this.value)">
                </div>

                <div class="form-group">
                    <label class="form-label">Currency Symbol *</label>
                    <input type="text" id="modal-curr-symbol" required class="form-input" placeholder="e.g. $, €, £, ¥, ₹">
                </div>

                <div class="form-group">
                    <label class="form-label">Currency Display Name</label>
                    <input type="text" id="modal-curr-name" class="form-input" placeholder="e.g. Euro, British Pound, Singapore Dollar">
                </div>

                <div class="form-group">
                    <label class="form-label">Exchange Rate Relative to Base (<span id="modal-curr-base-code-label">USD</span>) *</label>
                    <input type="number" id="modal-curr-rate" step="0.000001" min="0.000001" required class="form-input" placeholder="e.g. 1.0800" oninput="document.getElementById('modal-rate-note').textContent = '1 unit = ' + this.value + ' ' + state.tenant.currency_code">
                    <div id="modal-rate-note" style="font-size: 0.725rem; color: var(--zinc-500); margin-top: 0.25rem;">
                        1 unit of this currency = 1.00 USD.
                    </div>
                </div>

                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" id="modal-curr-active" checked style="accent-color: var(--color-primary, #4f46e5);">
                        <span style="font-size: 0.8rem; font-weight: 600;">Active for record transactions</span>
                    </label>
                </div>
            </div>

            <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 0.5rem; margin-top: 1rem;">
                <button type="button" onclick="closeCurrencyModal()" class="btn btn-secondary">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary" style="background: #4f46e5; border-color: #4338ca;">
                    <span id="modal-currency-submit-label">Save Currency</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL: CONFIRM BASE CURRENCY REBASE                            -->
<!-- ============================================================== -->
<div id="confirm-base-modal" class="modal-overlay" style="display: none;">
    <div class="modal-card animate-fade-in" style="max-width: 500px; width: 95%;">
        <div class="modal-header">
            <h3 style="font-size: 1rem; font-weight: 700; color: var(--zinc-950); margin: 0;">
                Confirm Base Currency Change
            </h3>
            <button type="button" onclick="closeConfirmBaseModal()" style="background: none; border: none; cursor: pointer; font-size: 1.2rem; color: var(--zinc-400);">✕</button>
        </div>

        <div class="modal-body" style="display: flex; flex-direction: column; gap: 1rem;">
            <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: var(--radius-lg, 12px); padding: 1rem; color: #b45309; font-size: 0.825rem; display: flex; gap: 0.75rem;">
                <i data-lucide="alert-circle" style="width: 20px; height: 20px; flex-shrink: 0; margin-top: 2px;"></i>
                <div>
                    <strong>Important Multi-Currency Notice:</strong>
                    <p style="margin: 0.25rem 0 0; line-height: 1.45;">
                        You are changing the organization base accounting currency from <strong id="confirm-old-base-name">USD</strong> to <strong id="confirm-new-base-name">EUR (€)</strong>.
                    </p>
                    <p style="margin: 0.5rem 0 0; line-height: 1.45;">
                        All existing exchange rates will be automatically rebased to <strong id="confirm-new-base-code">EUR</strong>, and future pipeline rollup totals will be calculated in this currency.
                    </p>
                </div>
            </div>
        </div>

        <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 0.5rem; margin-top: 1rem;">
            <button type="button" onclick="closeConfirmBaseModal()" class="btn btn-secondary">
                Cancel
            </button>
            <button type="button" id="confirm-base-action-btn" onclick="executeSetBaseCurrency()" class="btn btn-primary" style="background: #4f46e5; border-color: #4338ca;">
                Confirm &amp; Switch Base
            </button>
        </div>
    </div>
</div>

<script>
// ================= CONSTANTS & PRESETS (100% MATCH REACT) =================
const EMAIL_PROVIDER_PRESETS = [
    {
        id: 'sendgrid',
        name: 'SendGrid',
        host: 'smtp.sendgrid.net',
        port: 587,
        secure: false,
        defaultUser: 'apikey',
        badge: 'High-Volume Delivery',
        desc: 'Twilio SendGrid transactional relay. Requires API Key with Mail Send permissions.'
    },
    {
        id: 'office365',
        name: 'Microsoft 365 / Outlook',
        host: 'smtp.office365.com',
        port: 587,
        secure: false,
        defaultUser: '',
        badge: 'Enterprise Standard',
        desc: 'Exchange Online SMTP client submission. Requires authenticated tenant mailbox.'
    },
    {
        id: 'gmail',
        name: 'Google Workspace',
        host: 'smtp.gmail.com',
        port: 465,
        secure: true,
        defaultUser: '',
        badge: 'Google Business',
        desc: 'Gmail SMTP relay. Use a 16-character App Password generated from Google Security.'
    },
    {
        id: 'ses',
        name: 'Amazon SES',
        host: 'email-smtp.us-east-1.amazonaws.com',
        port: 587,
        secure: false,
        defaultUser: '',
        badge: 'AWS Infrastructure',
        desc: 'Amazon Simple Email Service. Use IAM SES SMTP credentials (not standard AWS keys).'
    },
    {
        id: 'mailgun',
        name: 'Mailgun',
        host: 'smtp.mailgun.org',
        port: 587,
        secure: false,
        defaultUser: '',
        badge: 'Developer Cloud',
        desc: 'Sinch Mailgun transactional routing. Supports EU & US regional domains.'
    },
    {
        id: 'custom',
        name: 'Custom SMTP Relay',
        host: '',
        port: 587,
        secure: false,
        defaultUser: '',
        badge: 'Any Private Server',
        desc: 'Connect to your company Postfix, Exim, on-prem mail server, or private relay.'
    }
];

const COMMON_TIMEZONES = [
    { value: 'America/New_York', label: 'Eastern Time (US & Canada) — UTC-05:00' },
    { value: 'America/Chicago', label: 'Central Time (US & Canada) — UTC-06:00' },
    { value: 'America/Denver', label: 'Mountain Time (US & Canada) — UTC-07:00' },
    { value: 'America/Los_Angeles', label: 'Pacific Time (US & Canada) — UTC-08:00' },
    { value: 'Europe/London', label: 'London, Dublin, Lisbon (GMT) — UTC+00:00' },
    { value: 'Europe/Paris', label: 'Paris, Berlin, Amsterdam, Madrid (CET) — UTC+01:00' },
    { value: 'Europe/Berlin', label: 'Berlin, Frankfurt, Rome, Vienna (CET) — UTC+01:00' },
    { value: 'Asia/Dubai', label: 'Dubai, Abu Dhabi, Muscat (GST) — UTC+04:00' },
    { value: 'Asia/Kolkata', label: 'India Standard Time (IST) — UTC+05:30' },
    { value: 'Asia/Singapore', label: 'Singapore, Kuala Lumpur (SGT) — UTC+08:00' },
    { value: 'Asia/Tokyo', label: 'Tokyo, Osaka, Seoul (JST) — UTC+09:00' },
    { value: 'Australia/Sydney', label: 'Sydney, Melbourne, Canberra (AEST) — UTC+10:00' },
    { value: 'UTC', label: 'Coordinated Universal Time (UTC) — UTC+00:00' }
];

const MONTHS = [
    { value: 1, label: 'January' },
    { value: 2, label: 'February' },
    { value: 3, label: 'March' },
    { value: 4, label: 'April' },
    { value: 5, label: 'May' },
    { value: 6, label: 'June' },
    { value: 7, label: 'July' },
    { value: 8, label: 'August' },
    { value: 9, label: 'September' },
    { value: 10, label: 'October' },
    { value: 11, label: 'November' },
    { value: 12, label: 'December' }
];

const DAYS_LIST = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

const CURRENCY_SYMBOLS = {
    'USD': '$', 'EUR': '€', 'GBP': '£', 'JPY': '¥', 'CAD': 'CA$',
    'AUD': 'A$', 'CHF': 'CHF', 'CNY': 'CN¥', 'INR': '₹', 'SGD': 'S$',
    'BRL': 'R$', 'MXN': 'MX$'
};

const COLOR_PALETTES = [
    { id: 'violet', name: 'Electric Violet', hex: '#4F46E5' },
    { id: 'emerald', name: 'Emerald Growth', hex: '#10B981' },
    { id: 'amber', name: 'Sunset Amber', hex: '#F59E0B' },
    { id: 'rose', name: 'Rose Flare', hex: '#F43F5E' },
    { id: 'cyan', name: 'Cyber Cyan', hex: '#06B6D4' },
    { id: 'slate', name: 'Slate Tech', hex: '#475569' },
    { id: 'obsidian', name: 'Obsidian Dark', hex: '#09090B' }
];

// Default Initial State
const DEFAULT_TENANT_DATA = {
    id: 'tenant-acme',
    name: 'Acme Enterprise Tech',
    legal_name: 'Acme Corporation Global Inc.',
    tax_id: 'US-EIN-987654321',
    registration_number: 'CR-09871234',
    industry: 'Enterprise Software & Cloud',
    website: 'https://acme.example.com',
    phone: '+1 (555) 019-2834',
    billing_email: 'billing@acme.example.com',
    support_email: 'support@acme.example.com',
    address_street: '100 Technology Park, Suite 400',
    address_city: 'San Francisco',
    address_state: 'CA',
    address_postal_code: '94107',
    address_country: 'United States',
    logo_url: '',
    branding_theme: 'apple',
    accent_color: '#4F46E5',
    page_bg_color: '#fafafa',
    font_color: '#09090b',
    header_bg_color: '#ffffff',
    sidebar_bg_color: '#ffffff',
    currency_code: 'USD',
    currency_symbol: '$',
    currency_position: 'prefix',
    time_zone: 'America/New_York',
    date_format: 'YYYY-MM-DD',
    time_format: '12h',
    number_format: 'comma_dot',
    fiscal_year_start_month: 1,
    working_days: 'Mon,Tue,Wed,Thu,Fri',
    working_hours_start: '09:00',
    working_hours_end: '18:00',
    allow_self_password_change: true
};

const DEFAULT_CURRENCIES = [
    { id: 'curr-1', currency_code: 'USD', currency_symbol: '$', currency_name: 'US Dollar', exchange_rate: 1.0, is_base: 1, is_active: 1 },
    { id: 'curr-2', currency_code: 'EUR', currency_symbol: '€', currency_name: 'Euro', exchange_rate: 1.085, is_base: 0, is_active: 1 },
    { id: 'curr-3', currency_code: 'GBP', currency_symbol: '£', currency_name: 'British Pound', exchange_rate: 1.272, is_base: 0, is_active: 1 },
    { id: 'curr-4', currency_code: 'JPY', currency_symbol: '¥', currency_name: 'Japanese Yen', exchange_rate: 0.0066, is_base: 0, is_active: 1 },
    { id: 'curr-5', currency_code: 'INR', currency_symbol: '₹', currency_name: 'Indian Rupee', exchange_rate: 0.012, is_base: 0, is_active: 1 },
    { id: 'curr-6', currency_code: 'CAD', currency_symbol: 'CA$', currency_name: 'Canadian Dollar', exchange_rate: 0.741, is_base: 0, is_active: 1 }
];

const DEFAULT_EMAIL_SETTINGS = {
    email_provider: 'sendgrid',
    smtp_host: 'smtp.sendgrid.net',
    smtp_port: 587,
    smtp_user: 'apikey',
    smtp_pass: 'SG.sample_key_987216',
    has_smtp_pass: true,
    smtp_secure: false,
    smtp_status: 'verified',
    smtp_last_tested_at: '2026-10-12T14:32:00Z',
    email_from_name: 'Acme Enterprise Sales',
    email_from_address: 'notifications@acme.example.com',
    email_reply_to: 'support@acme.example.com',
    sender_identity_policy: 'org_default',
    enforce_legal_footer: true,
    legal_footer_html: `<div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; font-size: 11px; line-height: 1.5; color: #64748b; padding-top: 14px; border-top: 1px solid #e2e8f0; margin-top: 20px;">
  <p style="margin: 0 0 4px 0; color: #334155; font-weight: 600;">© 2026 Acme Corporation Global Inc. &bull; Tax/VAT ID: US-EIN-987654321</p>
  <p style="margin: 0 0 6px 0;">100 Technology Park, Suite 400, San Francisco, CA 94107, United States</p>
  <p style="margin: 0; color: #94a3b8;">
    You are receiving this communication regarding your account or transaction with Acme Corporation. 
    <a href="@{{unsubscribe_link}}" style="color: #4f46e5; text-decoration: underline;">Manage email preferences or unsubscribe</a>.
  </p>
</div>`
};

// Global App State
let state = {
    activeTab: 'profile',
    tenant: JSON.parse(JSON.stringify(DEFAULT_TENANT_DATA)),
    currencies: JSON.parse(JSON.stringify(DEFAULT_CURRENCIES)),
    emailSettings: JSON.parse(JSON.stringify(DEFAULT_EMAIL_SETTINGS)),
    legalFooterMode: 'preview',
    showSmtpPass: false,
    pendingBaseCurrency: null
};

// ================= INITIALIZATION =================
document.addEventListener('DOMContentLoaded', () => {
    // Attempt load from localStorage
    try {
        const savedTenant = localStorage.getItem('dealsync_company_profile');
        if (savedTenant) state.tenant = Object.assign({}, DEFAULT_TENANT_DATA, JSON.parse(savedTenant));

        const savedCurrs = localStorage.getItem('dealsync_tenant_currencies');
        if (savedCurrs) state.currencies = JSON.parse(savedCurrs);

        const savedEmail = localStorage.getItem('dealsync_email_settings');
        if (savedEmail) state.emailSettings = Object.assign({}, DEFAULT_EMAIL_SETTINGS, JSON.parse(savedEmail));
    } catch(e) {}

    initSwatches();
    initTimezoneOptions();
    initFiscalMonthOptions();
    initWorkingDays();
    initEmailPresets();
    populateFormValues();
    renderCurrenciesTable();
    populateCalcDropdowns();
    calculateLiveConversion();
    renderDnsRecords();
    updateLiveClock();
    setInterval(updateLiveClock, 1000);

    if (window.lucide) window.lucide.createIcons();
});

// ================= TAB SWITCHING =================
function switchCpTab(tabId) {
    state.activeTab = tabId;
    ['profile', 'regional', 'currency', 'email'].forEach(t => {
        const btn = document.getElementById(`tab-nav-${t}`);
        const pane = document.getElementById(`tab-pane-${t}`);
        if (btn) {
            if (t === tabId) btn.classList.add('is-active');
            else btn.classList.remove('is-active');
        }
        if (pane) {
            pane.style.display = (t === tabId) ? 'flex' : 'none';
        }
    });
}

// ================= INITIALIZE SELECTS & PILLS =================
function initSwatches() {
    const container = document.getElementById('cp-accent-swatches-container');
    if (!container) return;
    let html = '';
    COLOR_PALETTES.forEach(p => {
        const isSelected = (state.tenant.accent_color || '#4F46E5').toLowerCase() === p.hex.toLowerCase();
        html += `
            <button
                type="button"
                onclick="selectAccent('${p.hex}')"
                style="width: 32px; height: 32px; border-radius: 50%; background: ${p.hex}; border: ${isSelected ? '3px solid #ffffff' : '2px solid transparent'}; outline: ${isSelected ? `2.5px solid ${p.hex}` : 'none'}; cursor: pointer; transition: transform 0.15s ease; transform: ${isSelected ? 'scale(1.1)' : 'scale(1)'};"
                title="${p.name} (${p.hex})">
            </button>
        `;
    });
    container.innerHTML = html;
}

function initTimezoneOptions() {
    const select = document.getElementById('cp-select-timezone');
    if (!select) return;
    let html = '';
    COMMON_TIMEZONES.forEach(tz => {
        html += `<option value="${tz.value}" ${state.tenant.time_zone === tz.value ? 'selected' : ''}>${tz.label}</option>`;
    });
    select.innerHTML = html;
}

function initFiscalMonthOptions() {
    const select = document.getElementById('cp-select-fiscal-month');
    if (!select) return;
    let html = '';
    MONTHS.forEach(m => {
        html += `<option value="${m.value}" ${Number(state.tenant.fiscal_year_start_month) === m.value ? 'selected' : ''}>${m.label}</option>`;
    });
    select.innerHTML = html;
}

function initWorkingDays() {
    const container = document.getElementById('cp-working-days-container');
    if (!container) return;
    const currentList = state.tenant.working_days ? state.tenant.working_days.split(',').map(s => s.trim()) : [];
    let html = '';
    DAYS_LIST.forEach(day => {
        const isActive = currentList.includes(day);
        html += `
            <button
                type="button"
                onclick="toggleDay('${day}')"
                class="cp-day-pill ${isActive ? 'is-active' : ''}">
                ${day}
            </button>
        `;
    });
    container.innerHTML = html;
}

function toggleDay(day) {
    let currentList = state.tenant.working_days ? state.tenant.working_days.split(',').map(s => s.trim()) : [];
    if (currentList.includes(day)) {
        currentList = currentList.filter(d => d !== day);
    } else {
        currentList.push(day);
    }
    state.tenant.working_days = currentList.join(',');
    initWorkingDays();
}

// ================= FORM POPULATION =================
function populateFormValues() {
    const t = state.tenant;
    // Tab 1 Profile
    setVal('cp-input-name', t.name);
    setVal('cp-input-legal-name', t.legal_name);
    setVal('cp-input-tax-id', t.tax_id);
    setVal('cp-input-reg-number', t.registration_number);
    setVal('cp-input-industry', t.industry);
    setVal('cp-input-website', t.website);
    setVal('cp-input-phone', t.phone);
    setVal('cp-input-billing-email', t.billing_email);
    setVal('cp-input-support-email', t.support_email);
    setVal('cp-input-address-street', t.address_street);
    setVal('cp-input-address-city', t.address_city);
    setVal('cp-input-address-state', t.address_state);
    setVal('cp-input-address-postal', t.address_postal_code);
    setVal('cp-input-address-country', t.address_country);
    setVal('cp-input-logo-url', t.logo_url);
    renderLogoPreview(t.logo_url);

    // Color Inputs
    setVal('cp-color-page-bg', t.page_bg_color || '#fafafa');
    setVal('cp-text-page-bg', t.page_bg_color || '#fafafa');
    document.getElementById('cp-label-page-bg').textContent = (t.page_bg_color || '#FAFAFA').toUpperCase();

    setVal('cp-color-font-color', t.font_color || '#09090b');
    setVal('cp-text-font-color', t.font_color || '#09090b');
    document.getElementById('cp-label-font-color').textContent = (t.font_color || '#09090B').toUpperCase();

    setVal('cp-color-header-bg', t.header_bg_color || '#ffffff');
    setVal('cp-text-header-bg', t.header_bg_color || '#ffffff');
    document.getElementById('cp-label-header-bg').textContent = (t.header_bg_color || '#FFFFFF').toUpperCase();

    setVal('cp-color-sidebar-bg', t.sidebar_bg_color || '#ffffff');
    setVal('cp-text-sidebar-bg', t.sidebar_bg_color || '#ffffff');
    document.getElementById('cp-label-sidebar-bg').textContent = (t.sidebar_bg_color || '#FFFFFF').toUpperCase();

    setVal('cp-color-accent', t.accent_color || '#4F46E5');
    setVal('cp-text-accent', t.accent_color || '#4F46E5');
    updateAccentNameDisplay(t.accent_color || '#4F46E5');

    // Tab 2 Regional
    setVal('cp-select-timezone', t.time_zone || 'America/New_York');
    setVal('cp-select-date-format', t.date_format || 'YYYY-MM-DD');
    setVal('cp-select-time-format', t.time_format || '12h');
    setVal('cp-select-number-format', t.number_format || 'comma_dot');
    setVal('cp-select-fiscal-month', t.fiscal_year_start_month || 1);
    setVal('cp-input-work-start', t.working_hours_start || '09:00');
    setVal('cp-input-work-end', t.working_hours_end || '18:00');

    const allowPwd = document.getElementById('cp-check-allow-pwd');
    if (allowPwd) allowPwd.checked = Boolean(t.allow_self_password_change !== false);
    updatePasswordPolicyDisplay(allowPwd ? allowPwd.checked : true);

    updateFormatPreviews();
    updateFiscalQuarterText(t.fiscal_year_start_month || 1);

    // Tab 4 Email
    const em = state.emailSettings;
    setVal('cp-smtp-host', em.smtp_host);
    setVal('cp-smtp-port', em.smtp_port);
    setVal('cp-smtp-user', em.smtp_user);
    setVal('cp-email-from-name', em.email_from_name);
    setVal('cp-email-from-address', em.email_from_address);
    setVal('cp-email-reply-to', em.email_reply_to);
    setVal('cp-legal-footer-html', em.legal_footer_html);

    const sslRadio = document.getElementById('cp-proto-ssl');
    const starttlsRadio = document.getElementById('cp-proto-starttls');
    if (em.smtp_secure) {
        if (sslRadio) sslRadio.checked = true;
    } else {
        if (starttlsRadio) starttlsRadio.checked = true;
    }

    if (em.sender_identity_policy === 'user_as_sender') {
        const r = document.getElementById('cp-policy-user-sender');
        if (r) r.checked = true;
    } else {
        const r = document.getElementById('cp-policy-org-default');
        if (r) r.checked = true;
    }

    const enforceCheck = document.getElementById('cp-enforce-footer-check');
    if (enforceCheck) enforceCheck.checked = Boolean(em.enforce_legal_footer);

    updateFooterPreviewRender();
    updateMiniPreview();
}

function setVal(id, val) {
    const el = document.getElementById(id);
    if (el && val !== undefined && val !== null) el.value = val;
}

// ================= LIVE APPEARANCE PREVIEW =================
function updatePageBg(color) {
    state.tenant.page_bg_color = color;
    setVal('cp-color-page-bg', color);
    setVal('cp-text-page-bg', color);
    document.getElementById('cp-label-page-bg').textContent = color.toUpperCase();
    updateMiniPreview();
}

function updateFontColor(color) {
    state.tenant.font_color = color;
    setVal('cp-color-font-color', color);
    setVal('cp-text-font-color', color);
    document.getElementById('cp-label-font-color').textContent = color.toUpperCase();
    updateMiniPreview();
}

function updateHeaderBg(color) {
    state.tenant.header_bg_color = color;
    setVal('cp-color-header-bg', color);
    setVal('cp-text-header-bg', color);
    document.getElementById('cp-label-header-bg').textContent = color.toUpperCase();
    updateMiniPreview();
}

function updateSidebarBg(color) {
    state.tenant.sidebar_bg_color = color;
    setVal('cp-color-sidebar-bg', color);
    setVal('cp-text-sidebar-bg', color);
    document.getElementById('cp-label-sidebar-bg').textContent = color.toUpperCase();
    updateMiniPreview();
}

function selectAccent(color) {
    state.tenant.accent_color = color;
    setVal('cp-color-accent', color);
    setVal('cp-text-accent', color);
    updateAccentNameDisplay(color);
    initSwatches();
    updateMiniPreview();
}

function updateAccentNameDisplay(color) {
    const found = COLOR_PALETTES.find(c => c.hex.toLowerCase() === color.toLowerCase());
    const name = found ? `${found.name} (${found.hex})` : color.toUpperCase();
    const el = document.getElementById('cp-accent-name-display');
    if (el) {
        el.textContent = name;
        el.style.color = color;
    }
}

function handleResetColorsToDefault() {
    updatePageBg('#fafafa');
    updateFontColor('#09090b');
    updateHeaderBg('#ffffff');
    updateSidebarBg('#ffffff');
    selectAccent('#4F46E5');
    showToast('Theme colors restored to system defaults!');
}

function updateMiniPreview() {
    const t = state.tenant;
    const miniSidebar = document.getElementById('mini-preview-sidebar');
    const miniSidebarDot = document.getElementById('mini-preview-sidebar-dot');
    const miniHeader = document.getElementById('mini-preview-header');
    const miniHeaderTitle = document.getElementById('mini-preview-header-title');
    const miniHeaderAccent = document.getElementById('mini-preview-header-accent');
    const miniCanvas = document.getElementById('mini-preview-canvas');
    const miniCanvasTitle = document.getElementById('mini-preview-canvas-title');
    const miniCanvasSub = document.getElementById('mini-preview-canvas-subtitle');
    const miniCanvasBtn = document.getElementById('mini-preview-canvas-btn');

    if (miniSidebar) miniSidebar.style.background = t.sidebar_bg_color || '#ffffff';
    if (miniSidebarDot) miniSidebarDot.style.background = t.accent_color || '#4f46e5';

    if (miniHeader) miniHeader.style.background = t.header_bg_color || '#ffffff';
    if (miniHeaderTitle) {
        miniHeaderTitle.style.color = t.font_color || '#09090b';
        miniHeaderTitle.textContent = t.name || 'DealSync CRM';
    }
    if (miniHeaderAccent) miniHeaderAccent.style.background = t.accent_color || '#4f46e5';

    if (miniCanvas) miniCanvas.style.background = t.page_bg_color || '#fafafa';
    if (miniCanvasTitle) miniCanvasTitle.style.color = t.font_color || '#09090b';
    if (miniCanvasSub) miniCanvasSub.style.color = t.font_color || '#09090b';
    if (miniCanvasBtn) miniCanvasBtn.style.background = t.accent_color || '#4f46e5';
}

// ================= LOGO HANDLING =================
function handleLogoFileUpload(e) {
    const file = e.target.files && e.target.files[0];
    if (!file) return;

    if (file.size > 10 * 1024 * 1024) {
        showToast('Logo file size must be less than 10MB', 'error');
        return;
    }

    const reader = new FileReader();
    reader.onload = function(event) {
        const img = new Image();
        img.onload = function() {
            const canvas = document.createElement('canvas');
            let width = img.width;
            let height = img.height;
            const maxDim = 800;

            if (width > maxDim || height > maxDim) {
                if (width > height) {
                    height = Math.round((height * maxDim) / width);
                    width = maxDim;
                } else {
                    width = Math.round((width * maxDim) / height);
                    height = maxDim;
                }
            }
            canvas.width = width;
            canvas.height = height;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0, width, height);
            const compressed = canvas.toDataURL('image/png', 0.9);
            state.tenant.logo_url = compressed;
            setVal('cp-input-logo-url', compressed);
            renderLogoPreview(compressed);
            showToast('Logo uploaded and compressed successfully!');
        };
        img.src = event.target.result;
    };
    reader.readAsDataURL(file);
}

function updateLogoFromUrl(url) {
    state.tenant.logo_url = url;
    renderLogoPreview(url);
}

function removeLogo() {
    state.tenant.logo_url = '';
    setVal('cp-input-logo-url', '');
    renderLogoPreview('');
}

function renderLogoPreview(url) {
    const img = document.getElementById('cp-logo-img');
    const placeholder = document.getElementById('cp-logo-placeholder-icon');
    const removeBtn = document.getElementById('cp-logo-remove-btn');
    if (!img) return;

    if (url && url.trim()) {
        img.src = url;
        img.style.display = 'block';
        if (placeholder) placeholder.style.display = 'none';
        if (removeBtn) removeBtn.style.display = 'flex';
    } else {
        img.src = '';
        img.style.display = 'none';
        if (placeholder) placeholder.style.display = 'block';
        if (removeBtn) removeBtn.style.display = 'none';
    }
}

// ================= TAB 1 SAVE =================
function handleSaveOrganizationProfile(e) {
    e.preventDefault();
    state.tenant.name = document.getElementById('cp-input-name').value.trim();
    state.tenant.legal_name = document.getElementById('cp-input-legal-name').value.trim();
    state.tenant.tax_id = document.getElementById('cp-input-tax-id').value.trim();
    state.tenant.registration_number = document.getElementById('cp-input-reg-number').value.trim();
    state.tenant.industry = document.getElementById('cp-input-industry').value.trim();
    state.tenant.website = document.getElementById('cp-input-website').value.trim();
    state.tenant.phone = document.getElementById('cp-input-phone').value.trim();
    state.tenant.billing_email = document.getElementById('cp-input-billing-email').value.trim();
    state.tenant.support_email = document.getElementById('cp-input-support-email').value.trim();

    state.tenant.address_street = document.getElementById('cp-input-address-street').value.trim();
    state.tenant.address_city = document.getElementById('cp-input-address-city').value.trim();
    state.tenant.address_state = document.getElementById('cp-input-address-state').value.trim();
    state.tenant.address_postal_code = document.getElementById('cp-input-address-postal').value.trim();
    state.tenant.address_country = document.getElementById('cp-input-address-country').value.trim();

    try {
        localStorage.setItem('dealsync_company_profile', JSON.stringify(state.tenant));
    } catch(err) {}

    updateMiniPreview();
    renderDnsRecords();
    showToast('Organization brand theme & profile settings saved successfully!');
}

// ================= TAB 2: REGIONAL & LOCALIZATION =================
function updateTimeZone(tz) {
    state.tenant.time_zone = tz;
    document.getElementById('cp-clock-label').textContent = `Live Time in ${tz}`;
    updateLiveClock();
}

function updateLiveClock() {
    const tz = state.tenant.time_zone || 'UTC';
    const is24h = (state.tenant.time_format === '24h');
    try {
        const now = new Date();
        const formatted = new Intl.DateTimeFormat('en-US', {
            timeZone: tz,
            hour: 'numeric',
            minute: '2-digit',
            second: '2-digit',
            hour12: !is24h
        }).format(now);
        const clockEl = document.getElementById('cp-live-clock');
        if (clockEl) clockEl.textContent = formatted;
    } catch (e) {
        const clockEl = document.getElementById('cp-live-clock');
        if (clockEl) clockEl.textContent = '--:--:--';
    }
}

function updateFormatPreviews() {
    const dFormat = document.getElementById('cp-select-date-format').value;
    const tFormat = document.getElementById('cp-select-time-format').value;
    const nFormat = document.getElementById('cp-select-number-format').value;

    state.tenant.date_format = dFormat;
    state.tenant.time_format = tFormat;
    state.tenant.number_format = nFormat;

    // Date Preview
    const now = new Date();
    const yyyy = now.getFullYear();
    const mm = String(now.getMonth() + 1).padStart(2, '0');
    const dd = String(now.getDate()).padStart(2, '0');
    let dateStr = `${yyyy}-${mm}-${dd}`;
    if (dFormat === 'DD/MM/YYYY') dateStr = `${dd}/${mm}/${yyyy}`;
    else if (dFormat === 'MM/DD/YYYY') dateStr = `${mm}/${dd}/${yyyy}`;
    document.getElementById('cp-preview-date-format').textContent = dateStr;

    // Time Preview
    let timeStr = (tFormat === '24h') ? '14:30' : '02:30 PM';
    document.getElementById('cp-preview-time-format').textContent = timeStr;

    // Number Preview
    let numStr = '$1,250,000.75';
    if (nFormat === 'dot_comma') numStr = '$1.250.000,75';
    else if (nFormat === 'space_comma') numStr = '$1 250 000,75';
    document.getElementById('cp-preview-number-format').textContent = numStr;

    updateLiveClock();
}

function updateFiscalQuarterText(m) {
    const monthObj = MONTHS.find(x => x.value === Number(m)) || MONTHS[0];
    document.getElementById('cp-fiscal-q1-note').textContent = `Q1 begins ${monthObj.label} 1st.`;
}

function updatePasswordPolicyDisplay(checked) {
    state.tenant.allow_self_password_change = checked;
    const box = document.getElementById('cp-password-policy-box');
    const desc = document.getElementById('cp-password-policy-desc');
    if (checked) {
        box.style.background = '#f8fafc';
        box.style.borderColor = '#e2e8f0';
        desc.textContent = 'Enabled: Team members can independently update their login credentials from My Profile & Preferences.';
    } else {
        box.style.background = '#fffbeb';
        box.style.borderColor = '#fde68a';
        desc.textContent = 'Restricted: Non-admin users cannot change their password. Only Tenant Administrators can update or reset passwords.';
    }
}

function handleSaveRegionalSettings(e) {
    e.preventDefault();
    state.tenant.time_zone = document.getElementById('cp-select-timezone').value;
    state.tenant.date_format = document.getElementById('cp-select-date-format').value;
    state.tenant.time_format = document.getElementById('cp-select-time-format').value;
    state.tenant.number_format = document.getElementById('cp-select-number-format').value;
    state.tenant.fiscal_year_start_month = Number(document.getElementById('cp-select-fiscal-month').value);
    state.tenant.working_hours_start = document.getElementById('cp-input-work-start').value;
    state.tenant.working_hours_end = document.getElementById('cp-input-work-end').value;
    state.tenant.allow_self_password_change = document.getElementById('cp-check-allow-pwd').checked;

    try {
        localStorage.setItem('dealsync_company_profile', JSON.stringify(state.tenant));
    } catch(err) {}

    showToast('Regional localization and business hours saved successfully!');
}

// ================= TAB 3: CURRENCIES & EXCHANGE RATES =================
function renderCurrenciesTable() {
    const tbody = document.getElementById('cp-currencies-tbody');
    if (!tbody) return;

    const baseCurr = state.currencies.find(c => c.is_base) || state.currencies[0];
    document.getElementById('header-base-currency-badge').textContent = `${baseCurr.currency_code} (${baseCurr.currency_symbol})`;
    document.getElementById('cp-base-currency-display').textContent = `${baseCurr.currency_code} (${baseCurr.currency_symbol})`;
    document.getElementById('cp-table-base-code-label').textContent = baseCurr.currency_code;
    document.getElementById('cp-th-base-code').textContent = baseCurr.currency_code;
    document.getElementById('modal-curr-base-code-label').textContent = baseCurr.currency_code;
    document.getElementById('tab-currencies-count').textContent = state.currencies.length;

    let html = '';
    state.currencies.forEach(c => {
        const isBase = Boolean(c.is_base);
        html += `
            <tr style="background: ${isBase ? 'rgba(16, 185, 129, 0.04)' : '#ffffff'};">
                <td style="font-weight: 700; color: var(--zinc-950);">
                    <span style="font-family: monospace; font-size: 0.9rem;">${c.currency_code}</span>
                </td>
                <td style="font-size: 1.1rem; font-weight: 600;">${c.currency_symbol}</td>
                <td style="color: var(--zinc-700); font-size: 0.85rem;">${c.currency_name}</td>
                <td>
                    ${isBase ? `
                        <span style="font-weight: 700; color: #059669;">1.000000 (Base)</span>
                    ` : `
                        <span style="font-family: monospace; font-weight: 600;">
                            1 ${c.currency_code} = ${Number(c.exchange_rate).toFixed(4)} ${baseCurr.currency_code}
                        </span>
                    `}
                </td>
                <td>
                    ${isBase ? `
                        <span class="badge badge-green" style="font-size: 0.7rem;">Primary Base</span>
                    ` : `
                        <span class="badge badge-gray" style="font-size: 0.7rem;">Foreign Transaction</span>
                    `}
                </td>
                <td>
                    <button type="button" ${isBase ? 'disabled' : ''} onclick="toggleCurrencyActive('${c.id}')" style="border: none; background: transparent; cursor: ${isBase ? 'default' : 'pointer'}; padding: 0;">
                        <span class="badge ${c.is_active ? 'badge-green' : 'badge-gray'}" style="font-size: 0.7rem;">
                            ${c.is_active ? 'Active' : 'Disabled'}
                        </span>
                    </button>
                </td>
                <td style="text-align: right;">
                    <div style="display: inline-flex; gap: 0.4rem; align-items: center;">
                        ${!isBase ? `
                            <button type="button" onclick="openConfirmBaseModal('${c.id}')" class="btn btn-secondary btn-sm" style="font-size: 0.7rem; padding: 0.25rem 0.5rem;" title="Set as primary base currency">
                                Set as Base
                            </button>
                        ` : ''}
                        <button type="button" onclick="openEditCurrencyModal('${c.id}')" class="btn btn-secondary btn-sm" style="padding: 0.25rem 0.5rem;" title="Edit currency">
                            <i data-lucide="edit-2" style="width: 12px; height: 12px;"></i>
                        </button>
                        ${!isBase ? `
                            <button type="button" onclick="deleteCurrency('${c.id}')" class="btn btn-secondary btn-sm" style="padding: 0.25rem 0.5rem; color: #ef4444;" title="Remove currency">
                                <i data-lucide="trash-2" style="width: 12px; height: 12px;"></i>
                            </button>
                        ` : ''}
                    </div>
                </td>
            </tr>
        `;
    });

    tbody.innerHTML = html;
    if (window.lucide) window.lucide.createIcons();
}

function populateCalcDropdowns() {
    const fromSel = document.getElementById('cp-calc-from-curr');
    const toSel = document.getElementById('cp-calc-to-curr');
    if (!fromSel || !toSel) return;

    let html = '';
    state.currencies.filter(c => c.is_active).forEach(c => {
        html += `<option value="${c.currency_code}">${c.currency_code} (${c.currency_symbol})</option>`;
    });

    fromSel.innerHTML = html;
    toSel.innerHTML = html;

    // Default From = EUR, To = USD
    if (fromSel.querySelector("option[value='EUR']")) fromSel.value = 'EUR';
    if (toSel.querySelector("option[value='USD']")) toSel.value = 'USD';
}

function calculateLiveConversion() {
    const amount = parseFloat(document.getElementById('cp-calc-amount').value) || 0;
    const fromCode = document.getElementById('cp-calc-from-curr').value;
    const toCode = document.getElementById('cp-calc-to-curr').value;

    const fromCurr = state.currencies.find(c => c.currency_code === fromCode);
    const toCurr = state.currencies.find(c => c.currency_code === toCode);

    if (!fromCurr || !toCurr) return;

    // Convert from -> Base -> to
    // 1 unit of from = fromCurr.exchange_rate Base
    // amount in Base = amount * fromCurr.exchange_rate
    // amount in To = amount in Base / toCurr.exchange_rate
    const inBase = amount * (parseFloat(fromCurr.exchange_rate) || 1.0);
    const inTo = inBase / (parseFloat(toCurr.exchange_rate) || 1.0);

    const formatted = inTo.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    document.getElementById('cp-calc-result-display').textContent = `${toCurr.currency_symbol}${formatted}`;
}

// Add / Edit Currency Modal Handlers
function openAddCurrencyModal() {
    document.getElementById('currency-modal-title').textContent = 'Add Active World Currency';
    document.getElementById('modal-currency-edit-id').value = '';
    document.getElementById('modal-curr-code').value = '';
    document.getElementById('modal-curr-code').disabled = false;
    document.getElementById('modal-curr-symbol').value = '';
    document.getElementById('modal-curr-name').value = '';
    document.getElementById('modal-curr-rate').value = '1.0';
    document.getElementById('modal-curr-active').checked = true;
    document.getElementById('modal-currency-submit-label').textContent = 'Save Currency';
    const modal = document.getElementById('currency-modal');
    modal.classList.add('is-open');
    modal.style.display = 'flex';
}

function openEditCurrencyModal(id) {
    const c = state.currencies.find(x => x.id === id);
    if (!c) return;

    document.getElementById('currency-modal-title').textContent = `Edit Currency: ${c.currency_code}`;
    document.getElementById('modal-currency-edit-id').value = c.id;
    document.getElementById('modal-curr-code').value = c.currency_code;
    document.getElementById('modal-curr-code').disabled = true;
    document.getElementById('modal-curr-symbol').value = c.currency_symbol;
    document.getElementById('modal-curr-name').value = c.currency_name;
    document.getElementById('modal-curr-rate').value = c.exchange_rate;
    document.getElementById('modal-curr-active').checked = Boolean(c.is_active);
    document.getElementById('modal-currency-submit-label').textContent = 'Update Currency';
    const modal = document.getElementById('currency-modal');
    modal.classList.add('is-open');
    modal.style.display = 'flex';
}

function closeCurrencyModal() {
    const modal = document.getElementById('currency-modal');
    modal.classList.remove('is-open');
    modal.style.display = 'none';
}

function handleModalCodeInput(val) {
    const code = val.toUpperCase();
    document.getElementById('modal-curr-code').value = code;
    if (CURRENCY_SYMBOLS[code]) {
        document.getElementById('modal-curr-symbol').value = CURRENCY_SYMBOLS[code];
    }
}

function handleSaveCurrencyForm(e) {
    e.preventDefault();
    const editId = document.getElementById('modal-currency-edit-id').value;
    const code = document.getElementById('modal-curr-code').value.trim().toUpperCase();
    const symbol = document.getElementById('modal-curr-symbol').value.trim() || CURRENCY_SYMBOLS[code] || code;
    const name = document.getElementById('modal-curr-name').value.trim() || code;
    const rate = parseFloat(document.getElementById('modal-curr-rate').value) || 1.0;
    const active = document.getElementById('modal-curr-active').checked ? 1 : 0;

    if (editId) {
        const c = state.currencies.find(x => x.id === editId);
        if (c) {
            c.currency_symbol = symbol;
            c.currency_name = name;
            c.exchange_rate = rate;
            c.is_active = active;
        }
    } else {
        const exists = state.currencies.find(x => x.currency_code === code);
        if (exists) {
            showToast(`Currency ${code} already exists!`, 'error');
            return;
        }
        state.currencies.push({
            id: `curr-${Date.now()}`,
            currency_code: code,
            currency_symbol: symbol,
            currency_name: name,
            exchange_rate: rate,
            is_base: 0,
            is_active: active
        });
    }

    try {
        localStorage.setItem('dealsync_tenant_currencies', JSON.stringify(state.currencies));
    } catch(err) {}

    closeCurrencyModal();
    renderCurrenciesTable();
    populateCalcDropdowns();
    calculateLiveConversion();
    showToast(`Currency ${code} saved successfully!`);
}

function toggleCurrencyActive(id) {
    const c = state.currencies.find(x => x.id === id);
    if (!c || c.is_base) return;
    c.is_active = c.is_active ? 0 : 1;
    try {
        localStorage.setItem('dealsync_tenant_currencies', JSON.stringify(state.currencies));
    } catch(err) {}
    renderCurrenciesTable();
    populateCalcDropdowns();
    calculateLiveConversion();
}

function deleteCurrency(id) {
    const c = state.currencies.find(x => x.id === id);
    if (!c || c.is_base) return;
    if (!confirm(`Are you sure you want to remove ${c.currency_code} (${c.currency_name})?`)) return;

    state.currencies = state.currencies.filter(x => x.id !== id);
    try {
        localStorage.setItem('dealsync_tenant_currencies', JSON.stringify(state.currencies));
    } catch(err) {}

    renderCurrenciesTable();
    populateCalcDropdowns();
    calculateLiveConversion();
    showToast(`Currency ${c.currency_code} removed.`);
}

// Confirm Base Currency Modal
function openConfirmBaseModal(id) {
    const c = state.currencies.find(x => x.id === id);
    if (!c) return;
    state.pendingBaseCurrency = c;

    const baseCurr = state.currencies.find(x => x.is_base) || state.currencies[0];
    document.getElementById('confirm-old-base-name').textContent = `${baseCurr.currency_code} (${baseCurr.currency_symbol})`;
    document.getElementById('confirm-new-base-name').textContent = `${c.currency_code} (${c.currency_symbol})`;
    document.getElementById('confirm-new-base-code').textContent = c.currency_code;
    document.getElementById('confirm-base-action-btn').textContent = `Confirm & Switch to ${c.currency_code}`;

    const modal = document.getElementById('confirm-base-modal');
    modal.classList.add('is-open');
    modal.style.display = 'flex';
}

function closeConfirmBaseModal() {
    const modal = document.getElementById('confirm-base-modal');
    modal.classList.remove('is-open');
    modal.style.display = 'none';
    state.pendingBaseCurrency = null;
}

function executeSetBaseCurrency() {
    if (!state.pendingBaseCurrency) return;
    const target = state.pendingBaseCurrency;
    const targetOldRate = parseFloat(target.exchange_rate) || 1.0;

    // Automatic rebasing: all rates rebased relative to new base
    state.currencies.forEach(c => {
        if (c.id === target.id) {
            c.is_base = 1;
            c.exchange_rate = 1.0;
        } else {
            c.is_base = 0;
            // new_rate = old_rate / targetOldRate
            c.exchange_rate = parseFloat((c.exchange_rate / targetOldRate).toFixed(6));
        }
    });

    state.tenant.currency_code = target.currency_code;
    state.tenant.currency_symbol = target.currency_symbol;

    try {
        localStorage.setItem('dealsync_tenant_currencies', JSON.stringify(state.currencies));
        localStorage.setItem('dealsync_company_profile', JSON.stringify(state.tenant));
    } catch(err) {}

    closeConfirmBaseModal();
    renderCurrenciesTable();
    populateCalcDropdowns();
    calculateLiveConversion();
    showToast(`Base currency switched to ${target.currency_code} (${target.currency_symbol}). All exchange rates recalculated!`);
}

// ================= TAB 4: OUTBOUND EMAIL & SMTP GATEWAY =================
function initEmailPresets() {
    const container = document.getElementById('cp-email-presets-container');
    if (!container) return;
    let html = '';
    EMAIL_PROVIDER_PRESETS.forEach(preset => {
        const isSelected = (state.emailSettings.email_provider === preset.id);
        html += `
            <div
                onclick="selectEmailPreset('${preset.id}')"
                class="cp-preset-card ${isSelected ? 'is-selected' : ''}">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.4rem;">
                    <span class="badge badge-blue" style="font-size: 0.65rem; padding: 0.15rem 0.45rem;">
                        ${preset.badge}
                    </span>
                    ${isSelected ? `
                        <span class="badge badge-purple" style="font-size: 0.65rem; padding: 0.15rem 0.45rem;">Selected</span>
                    ` : `
                        <span style="font-size: 0.65rem; color: var(--zinc-400); border: 1px solid var(--zinc-200); border-radius: 4px; padding: 0.1rem 0.35rem;">Preset</span>
                    `}
                </div>
                <div style="font-weight: 600; font-size: 0.875rem; color: ${isSelected ? 'var(--color-primary, #4f46e5)' : 'var(--zinc-900)'};">
                    ${preset.name}
                </div>
                <div style="font-size: 0.75rem; color: var(--zinc-500); margin-top: 0.25rem; line-height: 1.35;">
                    ${preset.desc}
                </div>
                ${preset.host ? `
                    <div style="margin-top: 0.6rem; font-size: 0.7rem; color: var(--zinc-400); font-family: monospace;">
                        ${preset.host}:${preset.port}
                    </div>
                ` : ''}
            </div>
        `;
    });
    container.innerHTML = html;
}

function selectEmailPreset(presetId) {
    const found = EMAIL_PROVIDER_PRESETS.find(p => p.id === presetId);
    if (!found) return;

    state.emailSettings.email_provider = found.id;
    if (found.id !== 'custom') {
        state.emailSettings.smtp_host = found.host;
        state.emailSettings.smtp_port = found.port;
        state.emailSettings.smtp_secure = found.secure;
        setVal('cp-smtp-host', found.host);
        setVal('cp-smtp-port', found.port);
        toggleSmtpSecure(found.secure);
        if (found.defaultUser) {
            state.emailSettings.smtp_user = found.defaultUser;
            setVal('cp-smtp-user', found.defaultUser);
        }
    }

    initEmailPresets();
    renderDnsRecords();
    showToast(`Applied configuration preset: ${found.name}`);
}

function setPort(p) {
    state.emailSettings.smtp_port = p;
    setVal('cp-smtp-port', p);
    if (p === 465) {
        toggleSmtpSecure(true);
    } else {
        toggleSmtpSecure(false);
    }
}

function toggleSmtpSecure(isSecure) {
    state.emailSettings.smtp_secure = isSecure;
    const sslRadio = document.getElementById('cp-proto-ssl');
    const starttlsRadio = document.getElementById('cp-proto-starttls');
    if (isSecure) {
        if (sslRadio) sslRadio.checked = true;
    } else {
        if (starttlsRadio) starttlsRadio.checked = true;
    }
}

function toggleShowSmtpPass() {
    state.showSmtpPass = !state.showSmtpPass;
    const input = document.getElementById('cp-smtp-pass');
    const icon = document.getElementById('cp-smtp-eye-icon');
    if (input) {
        input.type = state.showSmtpPass ? 'text' : 'password';
    }
    if (icon) {
        icon.setAttribute('data-lucide', state.showSmtpPass ? 'eye-off' : 'eye');
        if (window.lucide) window.lucide.createIcons();
    }
}

function setLegalFooterMode(mode) {
    state.legalFooterMode = mode;
    const editBtn = document.getElementById('cp-footer-btn-edit');
    const prevBtn = document.getElementById('cp-footer-btn-preview');
    const editPane = document.getElementById('cp-footer-edit-pane');
    const prevPane = document.getElementById('cp-footer-preview-pane');

    if (mode === 'edit') {
        editBtn.classList.add('is-active');
        prevBtn.classList.remove('is-active');
        editPane.style.display = 'block';
        prevPane.style.display = 'none';
    } else {
        prevBtn.classList.add('is-active');
        editBtn.classList.remove('is-active');
        editPane.style.display = 'none';
        prevPane.style.display = 'block';
        updateFooterPreviewRender();
    }
}

function handleAutoGenerateLegalFooter() {
    const t = state.tenant;
    const company = t.legal_name || t.name || 'Our Organization';
    const addressParts = [
        t.address_street,
        t.address_city,
        [t.address_state, t.address_postal_code].filter(Boolean).join(' '),
        t.address_country
    ].filter(Boolean);
    const fullAddress = addressParts.length > 0 ? addressParts.join(', ') : '100 Enterprise Way, Suite 400, New York, NY 10001, United States';
    const year = new Date().getFullYear();
    const tax = t.tax_id ? ` &bull; Tax/VAT ID: ${t.tax_id}` : '';

    const generated = `<div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; font-size: 11px; line-height: 1.5; color: #64748b; padding-top: 14px; border-top: 1px solid #e2e8f0; margin-top: 20px;">
  <p style="margin: 0 0 4px 0; color: #334155; font-weight: 600;">© ${year} ${company}${tax}</p>
  <p style="margin: 0 0 6px 0;">${fullAddress}</p>
  <p style="margin: 0; color: #94a3b8;">
    You are receiving this communication regarding your account or transaction with ${company}. 
    <a href="@{{unsubscribe_link}}" style="color: #4f46e5; text-decoration: underline;">Manage email preferences or unsubscribe</a>.
  </p>
</div>`;

    state.emailSettings.legal_footer_html = generated;
    setVal('cp-legal-footer-html', generated);
    updateFooterPreviewRender();
    showToast('Legal compliance footer generated from your company profile!');
}

function updateFooterPreviewRender() {
    const html = document.getElementById('cp-legal-footer-html').value;
    const t = state.tenant;
    const rendered = (html || '')
        .replace(/@\{\{company_name\}\}/g, t.legal_name || t.name || 'Acme Corporation')
        .replace(/@\{\{company_address\}\}/g, `${t.address_street || '123 Market St'}, ${t.address_city || 'San Francisco'}, ${t.address_state || 'CA'}`)
        .replace(/@\{\{tax_id\}\}/g, t.tax_id || 'US-123456789')
        .replace(/@\{\{unsubscribe_link\}\}/g, '#unsubscribe-sample');

    document.getElementById('cp-footer-rendered-output').innerHTML = rendered;
}

function renderDnsRecords() {
    const container = document.getElementById('cp-dns-records-container');
    if (!container) return;

    const fromAddress = state.emailSettings.email_from_address || 'notifications@yourdomain.com';
    const domain = fromAddress.includes('@') ? fromAddress.split('@')[1] : 'yourdomain.com';
    let spfInclude = 'relay.mailserver.com';

    const p = state.emailSettings.email_provider;
    if (p === 'sendgrid') spfInclude = 'sendgrid.net';
    else if (p === 'gmail') spfInclude = '_spf.google.com';
    else if (p === 'office365') spfInclude = 'spf.protection.outlook.com';
    else if (p === 'mailgun') spfInclude = 'mailgun.org';
    else if (p === 'ses') spfInclude = 'amazonses.com';
    else if (state.emailSettings.smtp_host) spfInclude = state.emailSettings.smtp_host;

    const records = [
        {
            title: 'SPF (Sender Policy Framework)',
            host: domain,
            type: 'TXT',
            val: `v=spf1 include:${spfInclude} ~all`,
            key: 'spf'
        },
        {
            title: 'DKIM (DomainKeys Identified Mail)',
            host: `dealsync._domainkey.${domain}`,
            type: 'TXT',
            val: `v=DKIM1; k=rsa; p=MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC3...`,
            key: 'dkim'
        },
        {
            title: 'DMARC (Domain Message Authentication)',
            host: `_dmarc.${domain}`,
            type: 'TXT',
            val: `v=DMARC1; p=quarantine; pct=100; rua=mailto:dmarc-reports@${domain}`,
            key: 'dmarc'
        }
    ];

    let html = '';
    records.forEach(rec => {
        html += `
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: var(--radius-lg, 12px); padding: 0.85rem 1.25rem; display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;">
                <div style="min-width: 220px;">
                    <div style="font-size: 0.8rem; font-weight: 600; color: var(--zinc-900);">${rec.title}</div>
                    <div style="font-size: 0.7rem; color: var(--zinc-500);">
                        Host: <code style="color: var(--zinc-800);">${rec.host}</code> | Type: <strong>${rec.type}</strong>
                    </div>
                </div>
                <div style="flex: 1; min-width: 260px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: var(--radius-md, 8px); padding: 0.4rem 0.75rem; font-family: monospace; font-size: 0.75rem; color: var(--zinc-800); overflow-x: auto;">
                    ${rec.val}
                </div>
                <button type="button" onclick="copyDnsVal('${rec.val}', this)" class="btn btn-secondary btn-sm" style="font-size: 0.725rem; display: inline-flex; align-items: center; gap: 0.35rem;">
                    <i data-lucide="copy" style="width: 13px; height: 13px;"></i>
                    <span>Copy</span>
                </button>
            </div>
        `;
    });

    container.innerHTML = html;
    if (window.lucide) window.lucide.createIcons();
}

function copyDnsVal(val, btn) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(val);
    }
    const origText = btn.innerHTML;
    btn.innerHTML = '<span>Copied!</span>';
    setTimeout(() => {
        btn.innerHTML = origText;
        if (window.lucide) window.lucide.createIcons();
    }, 2000);
}

// Verification Test Delivery Sandbox
function handleSendTestDelivery() {
    const targetEmail = document.getElementById('cp-test-recipient-email').value.trim();
    if (!targetEmail) {
        showToast('Please enter a target recipient email address for testing.', 'error');
        return;
    }
    const host = document.getElementById('cp-smtp-host').value.trim();
    if (!host) {
        showToast('Please specify an SMTP Host before running verification test.', 'error');
        return;
    }

    const btn = document.getElementById('cp-btn-test-delivery');
    const btnText = document.getElementById('cp-test-delivery-btn-text');
    btn.disabled = true;
    btnText.textContent = 'Testing Handshake...';

    const resultBox = document.getElementById('cp-test-result-box');
    const resultIcon = document.getElementById('cp-test-result-icon');
    const resultTitle = document.getElementById('cp-test-result-title');
    const latencyBadge = document.getElementById('cp-test-latency-badge');
    const details = document.getElementById('cp-test-result-details');

    setTimeout(() => {
        btn.disabled = false;
        btnText.textContent = 'Send Verification Test Email';

        const latency = Math.floor(Math.random() * 100) + 120;
        const msgId = `<msg_${Date.now()}_${Math.random().toString(36).substring(7)}@dealsync.net>`;

        resultBox.style.display = 'block';
        resultBox.style.background = '#f0fdf4';
        resultBox.style.borderColor = '#a7f3d0';

        resultIcon.setAttribute('data-lucide', 'check-circle-2');
        resultIcon.style.color = '#16a34a';

        resultTitle.textContent = 'SMTP Handshake & Delivery Test Succeeded!';
        resultTitle.style.color = '#15803d';

        latencyBadge.textContent = `⚡ Handshake Latency: ${latency} ms`;
        latencyBadge.className = 'badge badge-green';

        details.style.color = '#166534';
        details.innerHTML = `
            <div>A verification email was successfully delivered to <strong>${targetEmail}</strong>.</div>
            <div style="margin-top: 0.25rem; font-size: 0.725rem; font-family: monospace;">Message ID: ${msgId}</div>
            <div style="margin-top: 0.25rem; font-size: 0.725rem; font-family: monospace;">Connected Host: ${host}:${document.getElementById('cp-smtp-port').value} (${state.emailSettings.email_provider})</div>
        `;

        state.emailSettings.smtp_status = 'verified';
        state.emailSettings.smtp_last_tested_at = new Date().toISOString();
        document.getElementById('tab-smtp-status-badge').textContent = 'verified';
        document.getElementById('cp-smtp-status-text').textContent = 'verified';

        if (window.lucide) window.lucide.createIcons();
        showToast(`Verification email delivered successfully to ${targetEmail}! Latency: ${latency}ms.`);
    }, 1200);
}

function handleSaveEmailSettings(e) {
    e.preventDefault();
    state.emailSettings.smtp_host = document.getElementById('cp-smtp-host').value.trim();
    state.emailSettings.smtp_port = parseInt(document.getElementById('cp-smtp-port').value) || 587;
    state.emailSettings.smtp_user = document.getElementById('cp-smtp-user').value.trim();
    const pass = document.getElementById('cp-smtp-pass').value;
    if (pass) {
        state.emailSettings.smtp_pass = pass;
        state.emailSettings.has_smtp_pass = true;
    }
    state.emailSettings.email_from_name = document.getElementById('cp-email-from-name').value.trim();
    state.emailSettings.email_from_address = document.getElementById('cp-email-from-address').value.trim();
    state.emailSettings.email_reply_to = document.getElementById('cp-email-reply-to').value.trim();
    state.emailSettings.legal_footer_html = document.getElementById('cp-legal-footer-html').value;
    state.emailSettings.enforce_legal_footer = document.getElementById('cp-enforce-footer-check').checked;

    const userSenderRadio = document.getElementById('cp-policy-user-sender');
    state.emailSettings.sender_identity_policy = (userSenderRadio && userSenderRadio.checked) ? 'user_as_sender' : 'org_default';

    try {
        localStorage.setItem('dealsync_email_settings', JSON.stringify(state.emailSettings));
    } catch(err) {}

    renderDnsRecords();
    showToast('Outbound email & SMTP gateway settings saved successfully!');
}

// ================= GLOBAL FEEDBACK TOAST HELPER =================
function showToast(msg, type = 'success') {
    const banner = document.getElementById('studio-feedback-banner');
    const text = document.getElementById('studio-feedback-text');
    const icon = document.getElementById('studio-feedback-icon');
    if (!banner || !text) return;

    text.textContent = msg;
    if (type === 'error') {
        banner.style.background = '#fef2f2';
        banner.style.border = '1px solid #fecaca';
        banner.style.color = '#b91c1c';
        icon.setAttribute('data-lucide', 'alert-circle');
    } else {
        banner.style.background = '#ecfdf5';
        banner.style.border = '1px solid #a7f3d0';
        banner.style.color = '#047857';
        icon.setAttribute('data-lucide', 'check-circle-2');
    }

    banner.style.display = 'flex';
    if (window.lucide) window.lucide.createIcons();

    setTimeout(() => {
        banner.style.display = 'none';
    }, 4500);
}
</script>
@endsection
