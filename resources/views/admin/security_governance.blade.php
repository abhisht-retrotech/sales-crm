@extends('layouts.app')

@section('title', 'Security & Access Governance Studio — DealSync Admin')

@section('content')
<style>
/* ========================================================================= */
/* SECURITY & GOVERNANCE STUDIO DEDICATED STYLES                              */
/* ========================================================================= */
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

.form-input {
    width: 100%;
    box-sizing: border-box;
    height: 38px;
    background-color: var(--zinc-50, #f8fafc);
    border: 1px solid var(--zinc-200, #e2e8f0);
    border-radius: var(--radius-md, 8px);
    padding: 0.45rem 0.85rem;
    font-size: 0.8125rem;
    color: var(--zinc-950, #09090b);
    font-family: inherit;
    outline: none;
    transition: border-color 0.15s ease, background-color 0.15s ease, box-shadow 0.15s ease;
}

.form-input:hover {
    border-color: var(--zinc-300, #cbd5e1);
    background-color: #ffffff;
}

.form-input:focus {
    background-color: #ffffff !important;
    border-color: var(--color-primary, #4f46e5) !important;
    box-shadow: 0 0 0 3px var(--color-primary-subtle, rgba(79, 70, 229, 0.15)) !important;
}

.radio-card {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 0.85rem 1.15rem;
    border-radius: var(--radius-lg, 12px);
    border: 1px solid var(--zinc-200, #e2e8f0);
    background: #ffffff;
    cursor: pointer;
    transition: all 0.15s ease;
}

.radio-card:hover {
    border-color: var(--zinc-300, #cbd5e1);
    background-color: var(--zinc-50, #f8fafc);
}

.radio-card.active {
    border: 2px solid var(--color-primary, #4f46e5) !important;
    background-color: var(--color-primary-subtle, #eef2ff) !important;
}

.quick-seat-btn {
    padding: 0.5rem;
    border-radius: 8px;
    border: 1px solid var(--zinc-300, #cbd5e1);
    background-color: transparent;
    color: var(--zinc-700, #334155);
    font-weight: 600;
    font-size: 0.85rem;
    cursor: pointer;
    transition: all 0.15s ease;
}

.quick-seat-btn:hover {
    border-color: var(--color-primary, #4f46e5);
    background-color: rgba(79, 70, 229, 0.04);
}

.quick-seat-btn.active {
    border: 2px solid var(--color-primary, #4f46e5) !important;
    background-color: rgba(79, 70, 229, 0.08) !important;
    color: var(--color-primary, #4f46e5) !important;
}

.stepper-btn {
    width: 30px;
    height: 30px;
    border-radius: 6px;
    border: 1px solid var(--zinc-300, #cbd5e1);
    background-color: #ffffff;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--zinc-700, #334155);
    transition: all 0.15s ease;
}

.stepper-btn:hover:not(:disabled) {
    background-color: var(--zinc-100, #f1f5f9);
    border-color: var(--zinc-400, #94a3b8);
}

.stepper-btn:disabled {
    opacity: 0.45;
    cursor: not-allowed;
}
</style>


<div style="max-width: 1000px; margin: 0 auto; padding-bottom: 3rem;" class="animate-fade-in">
    <!-- Studio Header (SecurityGovernanceStudio.tsx line 196) -->
    <div style="margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--zinc-950); display: flex; align-items: center; gap: 0.6rem; margin: 0;">
                <i data-lucide="shield-check" style="width: 22px; height: 22px; color: var(--color-primary);"></i>
                <span>Security &amp; Access Governance Studio</span>
            </h2>
            <p style="font-size: 0.85rem; color: var(--zinc-500); margin: 0.25rem 0 0 0;">
                Configure inactivity session timeouts, password complexity rules, brute-force protections, 2FA policies, and seat limits.
            </p>
        </div>
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <span class="badge badge-purple" style="font-size: 0.75rem; padding: 0.3rem 0.65rem;">
                Tenant: <strong>tenant-acme-corp</strong>
            </span>
            <span class="badge badge-blue" style="font-size: 0.75rem; padding: 0.3rem 0.65rem; text-transform: capitalize;">
                Tier: <strong id="headerPlanTier">enterprise</strong>
            </span>
        </div>
    </div>

    <!-- Feedback Banner (line 216) -->
    <div id="feedbackBanner" style="display: none; padding: 0.85rem 1.25rem; border-radius: var(--radius-lg); margin-bottom: 1.5rem; align-items: center; gap: 0.6rem; font-size: 0.825rem; font-weight: 500;">
        <i id="feedbackIcon" data-lucide="check-circle-2" style="width: 16px; height: 16px;"></i>
        <span id="feedbackText"></span>
    </div>

    <!-- Card 1: Seat License Quotas & Capacity Metering (line 236) -->
    <div style="background: var(--bg-card); border: 1px solid var(--border-card); border-radius: var(--radius-xl); padding: 1.5rem; box-shadow: var(--shadow-sm); margin-bottom: 1.75rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.75rem;">
            <div>
                <h3 style="font-size: 1rem; font-weight: 600; color: var(--zinc-900); display: flex; align-items: center; gap: 0.5rem; margin: 0;">
                    <i data-lucide="users" style="width: 18px; height: 18px; color: var(--color-primary);"></i>
                    <span>Seat License Quotas &amp; Subscription Metering</span>
                </h3>
                <p style="font-size: 0.8rem; color: var(--zinc-500); margin: 0.2rem 0 0 0;">
                    Allocated team member seats against tenant subscription plan (<span id="planTierDisplay">ENTERPRISE</span> @ $<span id="unitPriceDisplay">39</span>/user/mo).
                </p>
            </div>

            <div style="display: flex; gap: 0.65rem; align-items: center; flex-wrap: wrap;">
                <div style="display: flex; gap: 0.4rem; align-items: center; font-size: 0.75rem; color: var(--zinc-600); background: var(--zinc-100); padding: 0.25rem 0.65rem; border-radius: 6px;">
                    <span>Active: <strong id="statActive">4</strong></span>
                    <span style="color: var(--zinc-400);">•</span>
                    <span>Invites: <strong id="statInvites">2</strong></span>
                    <span style="color: var(--zinc-400);">•</span>
                    <span style="color: #047857; font-weight: 600;">Avail: <strong id="statAvail">9</strong></span>
                </div>

                <button
                    type="button"
                    onclick="handleOpenInvoices()"
                    class="btn btn-secondary btn-sm"
                    style="display: flex; align-items: center; gap: 0.35rem; font-size: 0.75rem; padding: 0.35rem 0.65rem;"
                    title="View billing invoices"
                >
                    <i data-lucide="receipt" style="width: 14px; height: 14px;"></i>
                    <span>Invoices</span>
                </button>

                <button
                    type="button"
                    onclick="openAddSeatsModal()"
                    class="btn btn-primary btn-sm"
                    style="display: flex; align-items: center; gap: 0.35rem; font-size: 0.75rem; padding: 0.35rem 0.75rem; font-weight: 600;"
                >
                    <i data-lucide="credit-card" style="width: 14px; height: 14px;"></i>
                    <span>+ Add More Seats</span>
                </button>
            </div>
        </div>

        <!-- Progress Bar (line 288) -->
        <div style="margin-bottom: 1.25rem;">
            <div style="display: flex; justify-content: space-between; font-size: 0.75rem; margin-bottom: 0.4rem; font-weight: 600;">
                <span id="allocatedProgressLabel" style="color: var(--zinc-700);">
                    6 of 15 Seats Allocated (40%)
                </span>
                <span id="capacityStatusLabel" style="color: #10b981;">
                    Healthy Capacity
                </span>
            </div>
            <div style="height: 10px; background: var(--zinc-200); border-radius: 9999px; overflow: hidden;">
                <div
                    id="capacityProgressBar"
                    style="width: 40%; height: 100%; background: var(--color-primary); border-radius: 9999px; transition: width 0.3s ease, background 0.3s ease;"
                ></div>
            </div>
        </div>

        <!-- Subscription Plan details stripe (line 314) -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 0.75rem; padding: 0.75rem 1rem; background-color: var(--zinc-50); border-radius: var(--radius-lg); border: 1px solid var(--zinc-200); font-size: 0.75rem; color: var(--zinc-600);">
            <div>
                <span style="color: var(--zinc-400); display: block;">Subscription Tier</span>
                <strong style="color: var(--zinc-900); text-transform: capitalize; font-size: 0.85rem;" id="stripeTier">Enterprise Plan</strong>
            </div>
            <div>
                <span style="color: var(--zinc-400); display: block;">Seat License Fee</span>
                <strong style="color: var(--zinc-900); font-size: 0.85rem;" id="stripePrice">$39 USD / user / mo</strong>
            </div>
            <div>
                <span style="color: var(--zinc-400); display: block;">Billing Cadence</span>
                <strong style="color: var(--zinc-900); font-size: 0.85rem;">Monthly Recurring</strong>
            </div>
            <div>
                <span style="color: var(--zinc-400); display: block;">Card On File</span>
                <strong style="color: var(--zinc-900); font-size: 0.85rem; display: flex; align-items: center; gap: 4px;">
                    <i data-lucide="credit-card" style="width: 13px; height: 13px; color: var(--color-primary);"></i>
                    <span>Visa •••• 4242</span>
                </strong>
            </div>
        </div>

        <!-- Super Admin Manual Quota Override (line 348) -->
        <div style="background: linear-gradient(135deg, rgba(124, 58, 237, 0.05) 0%, rgba(79, 70, 229, 0.05) 100%); border: 1px solid rgba(124, 58, 237, 0.25); border-radius: var(--radius-lg); padding: 1rem 1.25rem; margin-top: 1rem; display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;">
            <div>
                <div style="font-size: 0.85rem; font-weight: 600; color: #6d28d9; display: flex; align-items: center; gap: 0.4rem;">
                    <i data-lucide="shield-check" style="width: 16px; height: 16px;"></i>
                    <span>Platform Super Admin Manual Quota Override (Master Tenant)</span>
                </div>
                <div style="font-size: 0.75rem; color: #7c3aed; margin-top: 0.15rem;">
                    Contractual override: directly modify total seat capacity without triggering a payment transaction.
                </div>
            </div>

            <div style="display: flex; gap: 0.65rem; align-items: center;">
                <input
                    type="number"
                    id="superAdminSeatsInput"
                    min="1"
                    max="500"
                    class="form-input"
                    style="width: 100px; font-weight: 700; font-size: 0.9rem; text-align: center; height: 36px;"
                    value="15"
                >
                <span style="font-size: 0.75rem; color: #6d28d9; font-weight: 500;">Seats</span>
                <button
                    type="button"
                    onclick="handleSaveSeatsOnly()"
                    id="saveOverrideBtn"
                    class="btn btn-secondary btn-sm"
                    style="padding: 0.35rem 0.75rem; font-size: 0.75rem; font-weight: 600; height: 36px;"
                >
                    Save Override
                </button>
            </div>
        </div>
    </div>

    <!-- MAIN FORM (line 424) -->
    <form id="governanceForm" onsubmit="handleSavePolicies(event)" style="display: flex; flex-direction: column; gap: 1.75rem;">
        <!-- Card 2: Inactivity Session Auto-Timeout (line 426) -->
        <div style="background: var(--bg-card); border: 1px solid var(--border-card); border-radius: var(--radius-xl); padding: 1.5rem; box-shadow: var(--shadow-sm);">
            <h3 style="font-size: 1rem; font-weight: 600; color: var(--zinc-900); display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.35rem; margin-top: 0;">
                <i data-lucide="clock" style="width: 18px; height: 18px; color: var(--color-primary);"></i>
                <span>Session Inactivity Auto-Timeout</span>
            </h3>
            <p style="font-size: 0.8rem; color: var(--zinc-500); margin-bottom: 1.25rem; margin-top: 0;">
                Automatically invalidate browser sessions and log users out after a prolonged period of inactivity to prevent unauthorized physical terminal access.
            </p>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.25rem;">
                <div class="form-group" style="margin: 0;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.8125rem;">Auto-Logout After Inactivity</label>
                    <select class="form-select" id="sessionTimeoutMinutes">
                        <option value="15">15 minutes (High Security / Financial standard)</option>
                        <option value="30">30 minutes</option>
                        <option value="60" selected>1 hour (Recommended Enterprise default)</option>
                        <option value="120">2 hours</option>
                        <option value="240">4 hours</option>
                        <option value="480">8 hours (Full working day)</option>
                        <option value="0">Never (Disable inactivity timeout)</option>
                    </select>
                    <span style="font-size: 0.7rem; color: var(--zinc-500); margin-top: 0.3rem; display: block;">
                        Monitors user keyboard, mouse movement, and touch events on client before triggering security timeout.
                    </span>
                </div>

                <div class="form-group" style="margin: 0;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.8125rem;">Self-Service Password Changes</label>
                    <div style="margin-top: 0.5rem;">
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input type="checkbox" id="allowSelfPasswordChange" checked>
                            <span style="font-size: 0.825rem; color: var(--zinc-900); font-weight: 500;">
                                Allow users to change their own password in "My Profile"
                            </span>
                        </label>
                        <span style="font-size: 0.7rem; color: var(--zinc-500); margin-top: 0.2rem; display: block;">
                            When disabled, non-admin users must request password updates from the organization administrator.
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 3: Password Complexity & Expiration Governance (line 484) -->
        <div style="background: var(--bg-card); border: 1px solid var(--border-card); border-radius: var(--radius-xl); padding: 1.5rem; box-shadow: var(--shadow-sm);">
            <h3 style="font-size: 1rem; font-weight: 600; color: var(--zinc-900); display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.35rem; margin-top: 0;">
                <i data-lucide="key" style="width: 18px; height: 18px; color: var(--color-primary);"></i>
                <span>Password Complexity &amp; Lifecycle Governance</span>
            </h3>
            <p style="font-size: 0.8rem; color: var(--zinc-500); margin-bottom: 1.25rem; margin-top: 0;">
                Enforce statutory password strength parameters across user self-activation and password change dialogs.
            </p>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.25rem; margin-bottom: 1.25rem;">
                <div class="form-group" style="margin: 0;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.8125rem;">Minimum Character Length</label>
                    <select class="form-select" id="passwordMinLength">
                        <option value="8" selected>8 characters (Standard)</option>
                        <option value="10">10 characters (Recommended)</option>
                        <option value="12">12 characters (High Security)</option>
                        <option value="16">16 characters (Enterprise NIST)</option>
                    </select>
                </div>

                <div class="form-group" style="margin: 0;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.8125rem;">Password Expiration Lifecycle</label>
                    <select class="form-select" id="passwordExpiryDays">
                        <option value="0" selected>Never expire (Modern NIST recommendation)</option>
                        <option value="30">Every 30 days</option>
                        <option value="60">Every 60 days</option>
                        <option value="90">Every 90 days (Quarterly)</option>
                        <option value="180">Every 180 days (Semi-annual)</option>
                        <option value="365">Every 365 days (Annual)</option>
                    </select>
                </div>
            </div>

            <div style="background: var(--zinc-50); border: 1px solid var(--zinc-200); border-radius: var(--radius-lg); padding: 1rem 1.25rem;">
                <label class="form-label" style="margin-bottom: 0.6rem; display: block; font-weight: 600; font-size: 0.8125rem;">
                    Required Character Types
                </label>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 0.75rem;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.825rem;">
                        <input type="checkbox" id="passwordRequireUppercase" checked>
                        <span>Require uppercase letter (A-Z)</span>
                    </label>

                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.825rem;">
                        <input type="checkbox" id="passwordRequireNumbers" checked>
                        <span>Require numeric digit (0-9)</span>
                    </label>

                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.825rem;">
                        <input type="checkbox" id="passwordRequireSpecial" checked>
                        <span>Require special symbol (!@#$%^&amp;*)</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Card 4: Brute-Force Account Lockout Guard (line 572) -->
        <div style="background: var(--bg-card); border: 1px solid var(--border-card); border-radius: var(--radius-xl); padding: 1.5rem; box-shadow: var(--shadow-sm);">
            <h3 style="font-size: 1rem; font-weight: 600; color: var(--zinc-900); display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.35rem; margin-top: 0;">
                <i data-lucide="lock" style="width: 18px; height: 18px; color: var(--color-primary);"></i>
                <span>Brute-Force Account Lockout Guard</span>
            </h3>
            <p style="font-size: 0.8rem; color: var(--zinc-500); margin-bottom: 1.25rem; margin-top: 0;">
                Temporarily lock employee accounts after consecutive failed password attempts to thwart automated dictionary attacks.
            </p>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.25rem;">
                <div class="form-group" style="margin: 0;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.8125rem;">Max Failed Attempts Before Lockout</label>
                    <select class="form-select" id="maxFailedLoginAttempts">
                        <option value="3">3 consecutive attempts (Strict)</option>
                        <option value="5" selected>5 consecutive attempts (Recommended)</option>
                        <option value="10">10 consecutive attempts (Lenient)</option>
                        <option value="0">Disabled (No lockout)</option>
                    </select>
                </div>

                <div class="form-group" style="margin: 0;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.8125rem;">Temporary Lockout Duration</label>
                    <select class="form-select" id="lockoutDurationMinutes">
                        <option value="15">15 minutes</option>
                        <option value="30" selected>30 minutes (Standard)</option>
                        <option value="60">60 minutes</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Card 5: Two-Factor Authentication (2FA) Policy (line 618) -->
        <div style="background: var(--bg-card); border: 1px solid var(--border-card); border-radius: var(--radius-xl); padding: 1.5rem; box-shadow: var(--shadow-sm);">
            <h3 style="font-size: 1rem; font-weight: 600; color: var(--zinc-900); display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.35rem; margin-top: 0;">
                <i data-lucide="smartphone" style="width: 18px; height: 18px; color: var(--color-primary);"></i>
                <span>Tenant-Wide Two-Factor Authentication (2FA) Policy</span>
            </h3>
            <p style="font-size: 0.8rem; color: var(--zinc-500); margin-bottom: 1.25rem; margin-top: 0;">
                Specify whether TOTP authenticator verification (Google Authenticator, Microsoft Authenticator) is optional or mandated.
            </p>

            <div style="display: flex; flex-direction: column; gap: 0.85rem;" id="twoFactorRadioGroup">
                <label
                    id="radioCardOptional"
                    class="radio-card active"
                    onclick="selectTwoFactorPolicy('optional')"
                >
                    <input
                        type="radio"
                        name="twoFactorPolicy"
                        value="optional"
                        checked
                        style="margin-top: 0.2rem;"
                    >
                    <div>
                        <div style="font-size: 0.85rem; font-weight: 600; color: var(--color-primary-text, #3730a3);">
                            Optional for All Users
                        </div>
                        <div style="font-size: 0.75rem; color: var(--zinc-500); margin-top: 0.15rem;">
                            Individual team members can optionally configure 2FA in their personal "My Profile" settings.
                        </div>
                    </div>
                </label>

                <label
                    id="radioCardAdmin"
                    class="radio-card"
                    onclick="selectTwoFactorPolicy('admin_enforced')"
                >
                    <input
                        type="radio"
                        name="twoFactorPolicy"
                        value="admin_enforced"
                        style="margin-top: 0.2rem;"
                    >
                    <div>
                        <div style="font-size: 0.85rem; font-weight: 600; color: var(--zinc-900);">
                            Mandatory for Administrators Only
                        </div>
                        <div style="font-size: 0.75rem; color: var(--zinc-500); margin-top: 0.15rem;">
                            Users assigned to Admin security profiles must configure 2FA before accessing the Administration Console.
                        </div>
                    </div>
                </label>

                <label
                    id="radioCardAll"
                    class="radio-card"
                    onclick="selectTwoFactorPolicy('all_enforced')"
                >
                    <input
                        type="radio"
                        name="twoFactorPolicy"
                        value="all_enforced"
                        style="margin-top: 0.2rem;"
                    >
                    <div>
                        <div style="font-size: 0.85rem; font-weight: 600; color: var(--zinc-900);">
                            Mandatory for All Tenant Users (Zero Trust)
                        </div>
                        <div style="font-size: 0.75rem; color: var(--zinc-500); margin-top: 0.15rem;">
                            All existing and newly invited employees are prompted to complete 2FA setup immediately upon login.
                        </div>
                    </div>
                </label>
            </div>
        </div>

        <!-- Action Bar (line 690) -->
        <div style="display: flex; justify-content: flex-end; gap: 0.75rem; padding-top: 1rem; border-top: 1px solid var(--zinc-200);">
            <button
                type="submit"
                id="savePoliciesBtn"
                class="btn btn-primary"
                style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.65rem 1.5rem; font-weight: 600;"
            >
                <i data-lucide="save" style="width: 16px; height: 16px;"></i>
                <span>Save Security Governance Policies</span>
            </button>
        </div>
    </form>
</div>

<!-- ===================================================================== -->
<!-- MODAL: ADD SEATS PURCHASE MODAL (AddSeatsModal.tsx 1-to-1 Parity)      -->
<!-- ===================================================================== -->
<div id="addSeatsModal" class="modal-overlay" style="z-index: 1200; display: none;">
    <div class="modal-content animate-fade-in" style="max-width: 560px; overflow: hidden; display: flex; flex-direction: column;">
        <!-- Modal Header -->
        <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-card); display: flex; align-items: center; justify-content: space-between; background: linear-gradient(135deg, rgba(79, 70, 229, 0.04) 0%, rgba(124, 58, 237, 0.04) 100%);">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div style="width: 38px; height: 38px; border-radius: 10px; background-color: rgba(79, 70, 229, 0.1); color: var(--color-primary); display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="credit-card" style="width: 20px; height: 20px;"></i>
                </div>
                <div>
                    <h3 id="addSeatsModalTitle" style="margin: 0; font-size: 1.1rem; font-weight: 600; color: var(--zinc-900);">
                        Add User Seats
                    </h3>
                    <p style="margin: 0; font-size: 0.75rem; color: var(--zinc-500);">
                        <span id="modalPlanTierLabel">ENTERPRISE</span> Plan • $<span id="modalUnitPriceLabel">39</span>/user/month
                    </p>
                </div>
            </div>
            <button
                type="button"
                onclick="closeAddSeatsModal()"
                style="background: none; border: none; cursor: pointer; color: var(--zinc-400); padding: 4px; border-radius: 6px; font-size: 1.2rem;"
            >
                ✕
            </button>
        </div>

        <!-- Modal Body -->
        <div class="modal-body" style="padding: 1.5rem; max-height: 80vh; overflow-y: auto;">
            <!-- Error Banner -->
            <div id="modalErrorBanner" style="display: none; background-color: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1.25rem; font-size: 0.85rem; align-items: center; gap: 0.5rem;">
                <i data-lucide="alert-triangle" style="width: 16px; height: 16px;"></i>
                <span id="modalErrorText"></span>
            </div>

            <!-- Configuration State (Form) -->
            <div id="seatsConfigState">
                <!-- Current Status banner -->
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.75rem 1rem; background-color: rgba(79, 70, 229, 0.04); border-radius: 10px; border: 1px solid rgba(79, 70, 229, 0.15); margin-bottom: 1.25rem;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <i data-lucide="users" style="width: 16px; height: 16px; color: var(--color-primary);"></i>
                        <span style="font-size: 0.85rem; font-weight: 500; color: var(--zinc-700);">Current Team Capacity:</span>
                    </div>
                    <span id="modalCurrentCapacityVal" style="font-size: 0.9rem; font-weight: 700; color: var(--color-primary);">
                        15 Seats
                    </span>
                </div>

                <!-- Quick Select Buttons -->
                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--zinc-600); margin-bottom: 0.5rem;">
                    Select Additional Seats:
                </label>
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.5rem; margin-bottom: 1rem;">
                    <button type="button" class="quick-seat-btn active" id="quickAdd1" onclick="handleQuickAdd(1)">+1 Seat</button>
                    <button type="button" class="quick-seat-btn" id="quickAdd3" onclick="handleQuickAdd(3)">+3 Seats</button>
                    <button type="button" class="quick-seat-btn" id="quickAdd5" onclick="handleQuickAdd(5)">+5 Seats</button>
                    <button type="button" class="quick-seat-btn" id="quickAdd10" onclick="handleQuickAdd(10)">+10 Seats</button>
                </div>

                <!-- Stepper Input -->
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.5rem 1rem; border: 1px solid var(--zinc-200); border-radius: 10px; margin-bottom: 1.25rem; background-color: var(--zinc-50);">
                    <span style="font-size: 0.85rem; font-weight: 500; color: var(--zinc-700);">Custom Quantity:</span>
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <button type="button" class="stepper-btn" id="stepperMinusBtn" onclick="handleStepper(-1)" disabled>
                            <i data-lucide="minus" style="width: 14px; height: 14px;"></i>
                        </button>
                        <input
                            type="number"
                            id="additionalSeatsInput"
                            min="1"
                            max="500"
                            value="1"
                            class="form-input"
                            style="width: 60px; text-align: center; font-weight: 700; font-size: 1rem; height: 32px; padding: 2px;"
                            oninput="handleCustomQuantityChange(this.value)"
                        >
                        <button type="button" class="stepper-btn" onclick="handleStepper(1)">
                            <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
                        </button>
                    </div>
                </div>

                <!-- Financial Calculation Box -->
                <div style="background-color: var(--zinc-50); border: 1px solid var(--zinc-200); border-radius: 12px; padding: 1rem; margin-bottom: 1.25rem;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.4rem; font-size: 0.8rem; color: var(--zinc-500);">
                        <span>Price per User License:</span>
                        <span id="modalCalcPricePerSeat">$39.00 / month</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.4rem; font-size: 0.8rem; color: var(--zinc-500);">
                        <span>Additional Seats:</span>
                        <span style="font-weight: 600; color: var(--zinc-800);" id="modalCalcAddSeats">+1</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.4rem; font-size: 0.8rem; color: var(--zinc-500);">
                        <span>New Total Capacity:</span>
                        <strong style="color: var(--color-primary);" id="modalCalcNewTotal">16 Total Seats</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-top: 1px solid var(--zinc-200); padding-top: 0.6rem; margin-top: 0.4rem; font-size: 0.95rem;">
                        <strong style="color: var(--zinc-900);">Due Today (Instant Charge):</strong>
                        <strong style="font-size: 1.15rem; color: var(--color-primary);" id="modalCalcDueToday">
                            $39.00 USD
                        </strong>
                    </div>
                </div>

                <!-- Mock Payment Details -->
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: flex; align-items: center; gap: 4px; font-size: 0.8rem; font-weight: 600; color: var(--zinc-600); margin-bottom: 0.4rem;">
                        <i data-lucide="lock" style="width: 12px; height: 12px;"></i>
                        <span>Payment Method (Encrypted):</span>
                    </label>
                    <div style="display: flex; gap: 0.5rem;">
                        <input
                            type="text"
                            id="demoCardNumber"
                            value="4242 •••• •••• 4242"
                            class="form-input"
                            style="flex: 2;"
                            placeholder="Card Number"
                        >
                        <input
                            type="text"
                            id="demoCardExp"
                            value="12/28"
                            class="form-input"
                            style="width: 75px; text-align: center;"
                            placeholder="MM/YY"
                        >
                        <input
                            type="text"
                            id="demoCardCvc"
                            value="999"
                            class="form-input"
                            style="width: 65px; text-align: center;"
                            placeholder="CVC"
                        >
                    </div>
                    <div style="display: flex; align-items: center; gap: 4px; margin-top: 6px; font-size: 0.72rem; color: var(--zinc-500);">
                        <i data-lucide="shield-check" style="width: 13px; height: 13px; color: #059669;"></i>
                        <span>Charges will reflect on your monthly subscription invoice with immediate seat provisioning.</span>
                    </div>
                </div>

                <!-- Footer Action Buttons -->
                <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                    <button
                        type="button"
                        onclick="closeAddSeatsModal()"
                        class="btn btn-secondary"
                        style="padding: 0.6rem 1rem; font-size: 0.85rem;"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        id="confirmPurchaseBtn"
                        onclick="executeSeatPurchase()"
                        class="btn btn-primary"
                        style="padding: 0.6rem 1.25rem; font-size: 0.85rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;"
                    >
                        <i data-lucide="credit-card" style="width: 15px; height: 15px;"></i>
                        <span id="confirmPurchaseBtnText">Confirm &amp; Pay $39.00</span>
                    </button>
                </div>
            </div>

            <!-- Receipt State (Success View) -->
            <div id="seatsReceiptState" style="display: none; text-align: center; padding: 1rem 0;">
                <div style="width: 56px; height: 56px; border-radius: 50%; background-color: #ecfdf5; color: #059669; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem auto;">
                    <i data-lucide="check-circle-2" style="width: 32px; height: 32px;"></i>
                </div>
                <h4 style="margin: 0 0 0.5rem 0; font-size: 1.2rem; font-weight: 700; color: var(--zinc-900);">
                    Capacity Successfully Expanded!
                </h4>
                <p style="color: var(--zinc-500); font-size: 0.875rem; margin: 0 0 1.5rem 0;">
                    Your organization can now invite and provision up to <strong id="receiptTotalSeatsVal">16 team members</strong>.
                </p>

                <!-- Receipt Summary Card -->
                <div style="background-color: var(--zinc-50); border: 1px solid var(--zinc-200); border-radius: 12px; padding: 1.25rem; text-align: left; margin-bottom: 1.5rem;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-size: 0.8rem; color: var(--zinc-500);">
                        <span>Invoice Number</span>
                        <strong style="color: var(--zinc-900);" id="receiptInvoiceNum">INV-2024-003</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-size: 0.8rem; color: var(--zinc-500);">
                        <span>Seats Added</span>
                        <strong style="color: #059669;" id="receiptSeatsAdded">+1 User Licenses</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-size: 0.8rem; color: var(--zinc-500);">
                        <span>Total Authorized Capacity</span>
                        <strong style="color: var(--color-primary);" id="receiptNewCapacity">16 Seats</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-size: 0.8rem; color: var(--zinc-500);">
                        <span>Amount Paid</span>
                        <strong style="color: var(--zinc-900);" id="receiptAmountPaid">$39.00 USD</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.8rem; color: var(--zinc-500); border-top: 1px dashed var(--zinc-200); padding-top: 0.5rem; margin-top: 0.5rem;">
                        <span>Payment Method</span>
                        <span style="display: flex; align-items: center; gap: 4px; color: var(--zinc-800); font-weight: 500;">
                            <i data-lucide="credit-card" style="width: 14px; height: 14px; color: var(--color-primary);"></i>
                            <span>Visa •••• 4242</span>
                        </span>
                    </div>
                </div>

                <button
                    type="button"
                    onclick="closeAddSeatsModal()"
                    class="btn btn-primary"
                    style="width: 100%; padding: 0.65rem 1.25rem; font-weight: 600; font-size: 0.9rem;"
                >
                    Done &amp; Return to Workspace
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ===================================================================== -->
<!-- MODAL: INVOICES HISTORY MODAL (SecurityGovernanceStudio.tsx line 734)  -->
<!-- ===================================================================== -->
<div id="invoicesModal" class="modal-overlay" style="z-index: 1200; display: none;">
    <div class="modal-content animate-fade-in" style="max-width: 680px; overflow: hidden;">
        <div class="modal-header" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-card); display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <i data-lucide="receipt" style="width: 18px; height: 18px; color: var(--color-primary);"></i>
                <h3 style="margin: 0; font-size: 1.05rem; font-weight: 600; color: var(--zinc-900);">Billing &amp; Invoice History</h3>
            </div>
            <button
                type="button"
                onclick="closeInvoicesModal()"
                style="background: none; border: none; cursor: pointer; color: var(--zinc-400); font-size: 1.2rem;"
            >
                ✕
            </button>
        </div>

        <div class="modal-body" style="padding: 1.25rem; max-height: 60vh; overflow-y: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 0.8rem;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--border-card); color: var(--zinc-500); text-align: left;">
                        <th style="padding: 0.5rem;">Invoice #</th>
                        <th style="padding: 0.5rem;">Description</th>
                        <th style="padding: 0.5rem;">Seats</th>
                        <th style="padding: 0.5rem;">Amount</th>
                        <th style="padding: 0.5rem;">Status</th>
                        <th style="padding: 0.5rem;">Date</th>
                    </tr>
                </thead>
                <tbody id="invoicesTbody">
                    <!-- Populated dynamically via renderInvoicesTable() -->
                </tbody>
            </table>
        </div>

        <div class="modal-footer" style="padding: 1rem 1.5rem; border-top: 1px solid var(--border-card); display: flex; justify-content: flex-end;">
            <button
                type="button"
                onclick="closeInvoicesModal()"
                class="btn btn-secondary btn-sm"
                style="padding: 0.4rem 0.9rem;"
            >
                Close
            </button>
        </div>
    </div>
</div>

<script>
// ============================================================================
// SECURITY & ACCESS GOVERNANCE STUDIO JAVASCRIPT STATE ENGINE
// ============================================================================
const STORAGE_POLICIES_KEY = 'dealsync_tenant_security_policies';
const STORAGE_QUOTA_KEY = 'dealsync_tenant_seats_quota';
const STORAGE_INVOICES_KEY = 'dealsync_tenant_invoices';

const DEFAULT_POLICIES = {
    plan_tier: 'enterprise',
    max_seats: 15,
    unit_price_monthly: 39,
    session_timeout_minutes: 60,
    allow_self_password_change: true,
    password_min_length: 8,
    password_expiry_days: 0,
    password_require_uppercase: true,
    password_require_numbers: true,
    password_require_special: true,
    max_failed_login_attempts: 5,
    lockout_duration_minutes: 30,
    two_factor_policy: 'optional',
    active_users: 4,
    pending_invitations: 2
};

const DEFAULT_INVOICES = [
    {
        id: 'inv-1',
        invoice_number: 'INV-2024-001',
        description: 'Initial Enterprise Subscription (10 Seats)',
        seats_purchased: 10,
        amount: 390.00,
        status: 'PAID',
        created_at: '2024-10-01'
    },
    {
        id: 'inv-2',
        invoice_number: 'INV-2024-002',
        description: 'Capacity Add-On (+5 Seats Expansion)',
        seats_purchased: 5,
        amount: 195.00,
        status: 'PAID',
        created_at: '2024-10-15'
    }
];

let currentPolicies = { ...DEFAULT_POLICIES };
let additionalSeatsCount = 1;

function initSecurityGovernance() {
    try {
        const stored = localStorage.getItem(STORAGE_POLICIES_KEY);
        if (stored) {
            currentPolicies = { ...DEFAULT_POLICIES, ...JSON.parse(stored) };
        }
        const quotaStored = localStorage.getItem(STORAGE_QUOTA_KEY);
        if (quotaStored) {
            currentPolicies.max_seats = parseInt(quotaStored, 10) || 15;
        }
    } catch (e) {
        currentPolicies = { ...DEFAULT_POLICIES };
    }

    populateFormValues();
    updateCapacityMeters();

    if (window.lucide) {
        lucide.createIcons();
    }
}

function persistPolicies() {
    localStorage.setItem(STORAGE_POLICIES_KEY, JSON.stringify(currentPolicies));
    localStorage.setItem(STORAGE_QUOTA_KEY, String(currentPolicies.max_seats));
}

function getStoredInvoices() {
    try {
        const stored = localStorage.getItem(STORAGE_INVOICES_KEY);
        if (stored) {
            return JSON.parse(stored);
        }
    } catch (e) {}
    return [...DEFAULT_INVOICES];
}

function saveInvoices(invoices) {
    localStorage.setItem(STORAGE_INVOICES_KEY, JSON.stringify(invoices));
}

function populateFormValues() {
    document.getElementById('headerPlanTier').textContent = currentPolicies.plan_tier;
    document.getElementById('planTierDisplay').textContent = currentPolicies.plan_tier.toUpperCase();
    document.getElementById('unitPriceDisplay').textContent = currentPolicies.unit_price_monthly;
    document.getElementById('superAdminSeatsInput').value = currentPolicies.max_seats;
    document.getElementById('stripeTier').textContent = `${capitalize(currentPolicies.plan_tier)} Plan`;
    document.getElementById('stripePrice').textContent = `$${currentPolicies.unit_price_monthly} USD / user / mo`;

    document.getElementById('sessionTimeoutMinutes').value = currentPolicies.session_timeout_minutes;
    document.getElementById('allowSelfPasswordChange').checked = Boolean(currentPolicies.allow_self_password_change);

    document.getElementById('passwordMinLength').value = currentPolicies.password_min_length;
    document.getElementById('passwordExpiryDays').value = currentPolicies.password_expiry_days;
    document.getElementById('passwordRequireUppercase').checked = Boolean(currentPolicies.password_require_uppercase);
    document.getElementById('passwordRequireNumbers').checked = Boolean(currentPolicies.password_require_numbers);
    document.getElementById('passwordRequireSpecial').checked = Boolean(currentPolicies.password_require_special);

    document.getElementById('maxFailedLoginAttempts').value = currentPolicies.max_failed_login_attempts;
    document.getElementById('lockoutDurationMinutes').value = currentPolicies.lockout_duration_minutes;

    selectTwoFactorPolicy(currentPolicies.two_factor_policy || 'optional');
}

function updateCapacityMeters() {
    const allocated = (currentPolicies.active_users || 4) + (currentPolicies.pending_invitations || 2);
    const max = currentPolicies.max_seats || 15;
    const available = Math.max(0, max - allocated);
    const percentage = Math.min(100, Math.round((allocated / max) * 100));

    document.getElementById('statActive').textContent = currentPolicies.active_users || 4;
    document.getElementById('statInvites').textContent = currentPolicies.pending_invitations || 2;
    document.getElementById('statAvail').textContent = available;

    document.getElementById('allocatedProgressLabel').textContent = `${allocated} of ${max} Seats Allocated (${percentage}%)`;

    const statusLabel = document.getElementById('capacityStatusLabel');
    const bar = document.getElementById('capacityProgressBar');
    bar.style.width = `${percentage}%`;

    if (percentage >= 90) {
        statusLabel.textContent = 'Critical Capacity';
        statusLabel.style.color = '#ef4444';
        bar.style.background = '#ef4444';
    } else if (percentage >= 75) {
        statusLabel.textContent = 'Approaching Limit';
        statusLabel.style.color = '#f59e0b';
        bar.style.background = '#f59e0b';
    } else {
        statusLabel.textContent = 'Healthy Capacity';
        statusLabel.style.color = '#10b981';
        bar.style.background = 'var(--color-primary)';
    }

    document.getElementById('modalCurrentCapacityVal').textContent = `${max} Seats`;
}

function selectTwoFactorPolicy(policy) {
    currentPolicies.two_factor_policy = policy;

    const cards = [
        { id: 'optional', el: document.getElementById('radioCardOptional') },
        { id: 'admin_enforced', el: document.getElementById('radioCardAdmin') },
        { id: 'all_enforced', el: document.getElementById('radioCardAll') }
    ];

    cards.forEach(c => {
        if (!c.el) return;
        const input = c.el.querySelector('input');
        const title = c.el.querySelector('div > div:first-child');
        if (c.id === policy) {
            c.el.classList.add('active');
            if (title) title.style.color = 'var(--color-primary-text, #3730a3)';
            if (input) input.checked = true;
        } else {
            c.el.classList.remove('active');
            if (title) title.style.color = 'var(--zinc-900)';
            if (input) input.checked = false;
        }
    });
}

function handleSavePolicies(e) {
    e.preventDefault();
    const btn = document.getElementById('savePoliciesBtn');
    btn.disabled = true;
    btn.innerHTML = `<i data-lucide="refresh-cw" class="animate-spin" style="width: 16px; height: 16px;"></i> <span>Saving Policies...</span>`;
    if (window.lucide) lucide.createIcons();

    setTimeout(() => {
        currentPolicies.session_timeout_minutes = parseInt(document.getElementById('sessionTimeoutMinutes').value, 10);
        currentPolicies.allow_self_password_change = document.getElementById('allowSelfPasswordChange').checked;
        currentPolicies.password_min_length = parseInt(document.getElementById('passwordMinLength').value, 10);
        currentPolicies.password_expiry_days = parseInt(document.getElementById('passwordExpiryDays').value, 10);
        currentPolicies.password_require_uppercase = document.getElementById('passwordRequireUppercase').checked;
        currentPolicies.password_require_numbers = document.getElementById('passwordRequireNumbers').checked;
        currentPolicies.password_require_special = document.getElementById('passwordRequireSpecial').checked;
        currentPolicies.max_failed_login_attempts = parseInt(document.getElementById('maxFailedLoginAttempts').value, 10);
        currentPolicies.lockout_duration_minutes = parseInt(document.getElementById('lockoutDurationMinutes').value, 10);

        persistPolicies();
        btn.disabled = false;
        btn.innerHTML = `<i data-lucide="save" style="width: 16px; height: 16px;"></i> <span>Save Security Governance Policies</span>`;
        if (window.lucide) lucide.createIcons();

        showFeedback('Tenant security policies and seat allocations saved successfully!', 'success');
    }, 400);
}

function handleSaveSeatsOnly() {
    const input = document.getElementById('superAdminSeatsInput');
    const newSeats = parseInt(input.value, 10);
    const allocated = (currentPolicies.active_users || 4) + (currentPolicies.pending_invitations || 2);

    if (isNaN(newSeats) || newSeats < allocated) {
        showFeedback(`Seat count cannot be lower than currently allocated seats (${allocated}).`, 'error');
        return;
    }

    const btn = document.getElementById('saveOverrideBtn');
    btn.disabled = true;
    btn.textContent = 'Saving...';

    setTimeout(() => {
        currentPolicies.max_seats = newSeats;
        persistPolicies();
        updateCapacityMeters();
        btn.disabled = false;
        btn.textContent = 'Save Override';
        showFeedback(`Tenant seat limit updated to ${newSeats} seats successfully!`, 'success');
    }, 300);
}

// ============================================================================
// ADD SEATS MODAL (1-TO-1 REACT PARITY)
// ============================================================================
function openAddSeatsModal() {
    additionalSeatsCount = 1;
    document.getElementById('seatsConfigState').style.display = 'block';
    document.getElementById('seatsReceiptState').style.display = 'none';
    document.getElementById('modalErrorBanner').style.display = 'none';
    document.getElementById('addSeatsModalTitle').textContent = 'Add User Seats';
    document.getElementById('modalPlanTierLabel').textContent = currentPolicies.plan_tier.toUpperCase();
    document.getElementById('modalUnitPriceLabel').textContent = currentPolicies.unit_price_monthly;
    document.getElementById('modalCurrentCapacityVal').textContent = `${currentPolicies.max_seats || 15} Seats`;

    syncSeatsModalCalculation();
    document.getElementById('addSeatsModal').style.display = 'flex';
    if (window.lucide) lucide.createIcons();
}

function closeAddSeatsModal() {
    document.getElementById('addSeatsModal').style.display = 'none';
}

function handleQuickAdd(count) {
    additionalSeatsCount = count;
    [1, 3, 5, 10].forEach(n => {
        const btn = document.getElementById(`quickAdd${n}`);
        if (btn) {
            if (n === count) btn.classList.add('active');
            else btn.classList.remove('active');
        }
    });
    syncSeatsModalCalculation();
}

function handleStepper(delta) {
    additionalSeatsCount = Math.max(1, additionalSeatsCount + delta);
    [1, 3, 5, 10].forEach(n => {
        const btn = document.getElementById(`quickAdd${n}`);
        if (btn) {
            if (n === additionalSeatsCount) btn.classList.add('active');
            else btn.classList.remove('active');
        }
    });
    syncSeatsModalCalculation();
}

function handleCustomQuantityChange(val) {
    const num = parseInt(val, 10) || 1;
    additionalSeatsCount = Math.max(1, num);
    syncSeatsModalCalculation();
}

function syncSeatsModalCalculation() {
    const unitPrice = currentPolicies.unit_price_monthly || 39;
    const currentSeats = currentPolicies.max_seats || 15;
    const newTotal = currentSeats + additionalSeatsCount;
    const totalCharge = (additionalSeatsCount * unitPrice).toFixed(2);

    document.getElementById('additionalSeatsInput').value = additionalSeatsCount;
    document.getElementById('stepperMinusBtn').disabled = additionalSeatsCount <= 1;
    document.getElementById('modalCalcPricePerSeat').textContent = `$${unitPrice.toFixed(2)} / month`;
    document.getElementById('modalCalcAddSeats').textContent = `+${additionalSeatsCount}`;
    document.getElementById('modalCalcNewTotal').textContent = `${newTotal} Total Seats`;
    document.getElementById('modalCalcDueToday').textContent = `$${totalCharge} USD`;
    document.getElementById('confirmPurchaseBtnText').textContent = `Confirm & Pay $${totalCharge}`;
}

function executeSeatPurchase() {
    if (additionalSeatsCount < 1) {
        showModalError('Please select at least 1 additional seat.');
        return;
    }

    const btn = document.getElementById('confirmPurchaseBtn');
    btn.disabled = true;
    btn.innerHTML = `<i data-lucide="refresh-cw" class="animate-spin" style="width: 15px; height: 15px;"></i> <span>Processing Payment...</span>`;
    if (window.lucide) lucide.createIcons();

    setTimeout(() => {
        const prevTotal = currentPolicies.max_seats || 15;
        const newTotal = prevTotal + additionalSeatsCount;
        const unitPrice = currentPolicies.unit_price_monthly || 39;
        const amountPaid = (additionalSeatsCount * unitPrice);
        const cardVal = document.getElementById('demoCardNumber').value;
        const last4 = cardVal.slice(-4) || '4242';

        // 1. Update State & Storage
        currentPolicies.max_seats = newTotal;
        persistPolicies();
        updateCapacityMeters();
        document.getElementById('superAdminSeatsInput').value = newTotal;

        // 2. Generate and persist new invoice
        const invoiceNum = `INV-2024-${String(Math.floor(100 + Math.random() * 900))}`;
        const newInvoice = {
            id: `inv-${Date.now()}`,
            invoice_number: invoiceNum,
            description: `Capacity Add-On (+${additionalSeatsCount} Seats Expansion)`,
            seats_purchased: additionalSeatsCount,
            amount: amountPaid,
            status: 'PAID',
            created_at: new Date().toISOString().split('T')[0]
        };

        const invoicesList = getStoredInvoices();
        invoicesList.unshift(newInvoice);
        saveInvoices(invoicesList);

        // 3. Switch modal to receipt state
        document.getElementById('seatsConfigState').style.display = 'none';
        document.getElementById('seatsReceiptState').style.display = 'block';
        document.getElementById('addSeatsModalTitle').textContent = 'Payment Receipt';
        document.getElementById('receiptTotalSeatsVal').textContent = `${newTotal} team members`;
        document.getElementById('receiptInvoiceNum').textContent = invoiceNum;
        document.getElementById('receiptSeatsAdded').textContent = `+${additionalSeatsCount} User Licenses`;
        document.getElementById('receiptNewCapacity').textContent = `${newTotal} Seats`;
        document.getElementById('receiptAmountPaid').textContent = `$${amountPaid.toFixed(2)} USD`;

        btn.disabled = false;
        btn.innerHTML = `<i data-lucide="credit-card" style="width: 15px; height: 15px;"></i> <span id="confirmPurchaseBtnText">Confirm &amp; Pay</span>`;
        if (window.lucide) lucide.createIcons();

        showFeedback(`Added ${additionalSeatsCount} seats. New capacity: ${newTotal} seats.`, 'success');
    }, 700);
}

function showModalError(msg) {
    const banner = document.getElementById('modalErrorBanner');
    document.getElementById('modalErrorText').textContent = msg;
    banner.style.display = 'flex';
    if (window.lucide) lucide.createIcons();
}

// ============================================================================
// INVOICES MODAL
// ============================================================================
function handleOpenInvoices() {
    renderInvoicesTable();
    document.getElementById('invoicesModal').style.display = 'flex';
}

function closeInvoicesModal() {
    document.getElementById('invoicesModal').style.display = 'none';
}

function renderInvoicesTable() {
    const list = getStoredInvoices();
    const tbody = document.getElementById('invoicesTbody');
    if (!tbody) return;

    if (list.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" style="text-align: center; padding: 2rem; color: var(--zinc-500);">
                    No billing invoices found for this organization.
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = list.map(inv => `
        <tr style="border-bottom: 1px solid var(--zinc-100);">
            <td style="padding: 0.65rem 0.5rem; font-weight: 600; color: var(--zinc-900);">${inv.invoice_number}</td>
            <td style="padding: 0.65rem 0.5rem; color: var(--zinc-700);">${inv.description}</td>
            <td style="padding: 0.65rem 0.5rem; font-weight: 600; color: #047857;">+${inv.seats_purchased}</td>
            <td style="padding: 0.65rem 0.5rem; font-weight: 600; color: var(--zinc-900);">$${Number(inv.amount).toFixed(2)}</td>
            <td style="padding: 0.65rem 0.5rem;">
                <span style="padding: 2px 8px; border-radius: 9999px; font-size: 0.7rem; font-weight: 600; background-color: #ecfdf5; color: #059669;">
                    ${inv.status}
                </span>
            </td>
            <td style="padding: 0.65rem 0.5rem; color: var(--zinc-500);">${inv.created_at}</td>
        </tr>
    `).join('');
}

// ============================================================================
// FEEDBACK BANNER
// ============================================================================
let feedbackTimeout = null;
function showFeedback(msg, type = 'success') {
    const banner = document.getElementById('feedbackBanner');
    const textEl = document.getElementById('feedbackText');
    const iconEl = document.getElementById('feedbackIcon');

    textEl.textContent = msg;
    if (type === 'success') {
        banner.style.background = '#ecfdf5';
        banner.style.border = '1px solid #a7f3d0';
        banner.style.color = '#047857';
        iconEl.setAttribute('data-lucide', 'check-circle-2');
    } else {
        banner.style.background = '#fef2f2';
        banner.style.border = '1px solid #fecaca';
        banner.style.color = '#b91c1c';
        iconEl.setAttribute('data-lucide', 'alert-circle');
    }

    banner.style.display = 'flex';
    if (window.lucide) lucide.createIcons();

    if (feedbackTimeout) clearTimeout(feedbackTimeout);
    feedbackTimeout = setTimeout(() => {
        banner.style.display = 'none';
    }, 4000);
}

function capitalize(s) {
    if (!s) return '';
    return s.charAt(0).toUpperCase() + s.slice(1);
}

// Close modals when clicking overlay background or pressing ESC
window.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeAddSeatsModal();
        closeInvoicesModal();
    }
});

document.addEventListener('DOMContentLoaded', initSecurityGovernance);
</script>
@endsection
