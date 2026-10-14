@extends('layouts.app')

@section('title', 'Sharing Rules Studio — DealSync Admin')

@section('content')
<style>
/* ========================================================================= */
/* SHARING RULES & RECORD SECURITY STUDIO DEDICATED STYLES                   */
/* ========================================================================= */
.sr-studio-container {
    --text-primary: var(--zinc-900, #0f172a);
    --text-secondary: var(--zinc-600, #475569);
    --text-tertiary: var(--zinc-500, #64748b);
    --text-muted: var(--zinc-400, #94a3b8);
    max-width: 1200px;
    margin: 0 auto;
    padding-bottom: 3rem;
    color: var(--zinc-900, #0f172a);
}

.sr-studio-container * {
    box-sizing: border-box;
}

.form-input, .form-control {
    width: 100%;
    box-sizing: border-box;
    height: 38px;
    background-color: var(--zinc-50, #f8fafc);
    border: 1px solid var(--zinc-200, #e2e8f0);
    border-radius: var(--radius-md, 8px);
    color: var(--zinc-950, #09090b);
    padding: 0.45rem 0.85rem;
    font-size: 0.8125rem;
    font-family: inherit;
    line-height: 1.4;
    outline: none;
    transition: border-color 0.15s ease, background-color 0.15s ease, box-shadow 0.15s ease;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.02);
}

.form-input::placeholder, .form-control::placeholder {
    color: var(--zinc-400, #94a3b8);
    font-size: 0.8125rem;
}

.form-input:hover, .form-control:hover {
    border-color: var(--zinc-300, #cbd5e1);
    background-color: #ffffff;
}

.form-input:focus, .form-control:focus,
.form-input:focus-visible, .form-control:focus-visible {
    background-color: #ffffff !important;
    border-color: var(--color-primary, #4f46e5) !important;
    box-shadow: 0 0 0 3px var(--color-primary-subtle, rgba(79, 70, 229, 0.15)) !important;
    color: var(--zinc-950, #09090b) !important;
}

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
    color: var(--zinc-950, #09090b) !important;
}

.form-select option {
    background: #ffffff;
    color: var(--zinc-900, #09090b);
    padding: 0.5rem 0.75rem;
    font-size: 0.8125rem;
}

.form-label {
    display: block;
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--zinc-800, #1e293b);
    margin-bottom: 0.35rem;
}

.sr-tab-btn {
    padding: 0.4rem 0.85rem;
    border-radius: 6px;
    font-size: 0.78125rem;
    font-weight: 500;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    background: transparent;
    color: var(--zinc-600, #475569);
    transition: all 0.15s ease;
    white-space: nowrap;
}

.sr-tab-btn:hover {
    background: var(--zinc-100, #f1f5f9);
    color: var(--zinc-950, #09090b);
}

.sr-tab-btn.is-active {
    background: var(--zinc-900, #0f172a);
    color: #ffffff;
    font-weight: 700;
}

.sr-tab-badge {
    font-size: 0.65rem;
    padding: 1px 5px;
    border-radius: 4px;
    font-weight: 600;
    background: var(--zinc-100, #f1f5f9);
    color: var(--zinc-600, #475569);
}

.sr-tab-btn.is-active .sr-tab-badge {
    background: rgba(255, 255, 255, 0.2);
    color: #ffffff;
}

.rule-card {
    background: #ffffff;
    border: 1px solid var(--zinc-200, #e2e8f0);
    border-radius: 10px;
    padding: 1rem 1.25rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
    transition: all 0.15s ease;
}

.rule-card:hover {
    box-shadow: 0 4px 12px -2px rgba(0, 0, 0, 0.05);
    border-color: var(--zinc-300, #cbd5e1);
}

.spin {
    animation: spinAnimation 1s linear infinite;
}

@keyframes spinAnimation {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(4px); }
    to { opacity: 1; transform: translateY(0); }
}

.animate-fade-in {
    animation: fadeIn 0.15s ease-out forwards;
}
</style>

@include('partials.admin_nav')

<div class="sr-studio-container animate-fade-in">
    <!-- Floating Toast Feedback Banner -->
    <div id="srToast" style="position: fixed; bottom: 24px; right: 24px; background: #09090b; color: #ffffff; padding: 0.75rem 1.25rem; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.3); display: none; align-items: center; gap: 0.6rem; z-index: 999999; font-size: 0.85rem; font-weight: 500;">
        <i id="srToastIcon" data-lucide="check-circle-2" style="width: 16px; height: 16px; color: #10b981; flex-shrink: 0;"></i>
        <span id="srToastMsg"></span>
    </div>

    <!-- Header Hero Banner (1-to-1 Parity with SharingRulesStudio.tsx lines 293-343) -->
    <div style="background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 100%); border: 1px solid #dbeafe; border-radius: var(--radius-xl, 14px); padding: 1.5rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.25rem;">
        <div style="display: flex; align-items: flex-start; gap: 0.85rem; max-width: 750px;">
            <div style="width: 42px; height: 42px; border-radius: 10px; background: var(--color-primary, #4f46e5); color: #ffffff; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 4px 10px rgba(99,102,241,0.3);">
                <i data-lucide="shield-check" style="width: 22px; height: 22px;"></i>
            </div>
            <div>
                <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                    <h1 style="font-size: 1.25rem; font-weight: 800; color: var(--zinc-950, #09090b); letter-spacing: -0.02em; margin: 0; line-height: 1.3;">
                        Enterprise Sharing Rules &amp; Record Security Studio
                    </h1>
                    <span class="badge badge-purple" style="font-size: 0.6875rem; font-weight: 700; padding: 0.15rem 0.5rem;">
                        Phase 1 &amp; 3 Engine Active
                    </span>
                </div>
                <p style="font-size: 0.8125rem; color: var(--zinc-600, #475569); margin: 0.35rem 0 0 0; line-height: 1.45;">
                    Conditionally grant record visibility to cross-functional profiles, roles, or users when records meet specific business criteria (e.g. <em>Deal Amount &ge; $1M</em>, <em>Stage = Negotiation</em>, or <em>Industry = Healthcare</em>).
                </p>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 0.6rem;">
            <button
                type="button"
                onclick="handleRecalculateAllRules()"
                class="btn btn-secondary btn-sm"
                style="display: inline-flex; align-items: center; gap: 0.45rem; padding: 0.5rem 0.85rem; font-weight: 600;"
            >
                <i id="recalcAllIcon" data-lucide="refresh-cw" style="width: 14px; height: 14px;"></i>
                <span>Recalculate All</span>
            </button>
            <button
                type="button"
                onclick="handleOpenCreateModal()"
                class="btn btn-primary"
                style="display: inline-flex; align-items: center; gap: 0.45rem; padding: 0.55rem 1.15rem; font-weight: 600;"
            >
                <i data-lucide="plus" style="width: 16px; height: 16px;"></i>
                <span>New Sharing Rule</span>
            </button>
        </div>
    </div>

    <!-- KPI Summary Grid (4 Cards) -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.25rem;">
        <div class="card" style="padding: 1rem; background: var(--bg-card); border: 1px solid var(--border-card); border-radius: var(--radius-lg, 12px);">
            <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500, #64748b); margin-bottom: 0.25rem; text-transform: uppercase; letter-spacing: 0.03em;">ACTIVE SHARING RULES</div>
            <div id="kpiActiveRules" style="font-size: 1.625rem; font-weight: 800; color: var(--color-primary, #4f46e5); letter-spacing: -0.03em;">4 Rules</div>
            <span style="font-size: 0.7rem; color: #059669; font-weight: 600;">100% Evaluated &amp; Active</span>
        </div>

        <div class="card" style="padding: 1rem; background: var(--bg-card); border: 1px solid var(--border-card); border-radius: var(--radius-lg, 12px);">
            <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500, #64748b); margin-bottom: 0.25rem; text-transform: uppercase; letter-spacing: 0.03em;">CRITERIA-BASED</div>
            <div id="kpiCriteriaRules" style="font-size: 1.625rem; font-weight: 800; color: #0284c7; letter-spacing: -0.03em;">3 Rules</div>
            <span style="font-size: 0.7rem; color: var(--zinc-600, #475569); font-weight: 500;">Deal Size &ge; $1M, Spend &ge; $500K</span>
        </div>

        <div class="card" style="padding: 1rem; background: var(--bg-card); border: 1px solid var(--border-card); border-radius: var(--radius-lg, 12px);">
            <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500, #64748b); margin-bottom: 0.25rem; text-transform: uppercase; letter-spacing: 0.03em;">OWNERSHIP-BASED</div>
            <div id="kpiOwnershipRules" style="font-size: 1.625rem; font-weight: 800; color: #7c3aed; letter-spacing: -0.03em;">1 Rule</div>
            <span style="font-size: 0.7rem; color: var(--zinc-600, #475569); font-weight: 500;">Role Hierarchy Cross-Sharing</span>
        </div>

        <div class="card" style="padding: 1rem; background: var(--bg-card); border: 1px solid var(--border-card); border-radius: var(--radius-lg, 12px);">
            <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500, #64748b); margin-bottom: 0.25rem; text-transform: uppercase; letter-spacing: 0.03em;">SHARED GRANTS GRANTED</div>
            <div id="kpiSharedCount" style="font-size: 1.625rem; font-weight: 800; color: #059669; letter-spacing: -0.03em;">176 Records</div>
            <span style="font-size: 0.7rem; color: var(--zinc-600, #475569); font-weight: 500;">Zero Authorization Collisions</span>
        </div>
    </div>

    <!-- Module Filter Tabs & Search Toolbar -->
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem; border-bottom: 1px solid var(--zinc-200, #e2e8f0); padding-bottom: 0.75rem; margin-bottom: 1.25rem;">
        <!-- Module Tabs -->
        <div id="srModuleTabsContainer" style="display: flex; align-items: center; gap: 0.35rem; overflow-x: auto; padding-bottom: 2px;">
            <!-- Populated dynamically via renderModuleTabs() -->
        </div>

        <!-- Search Input -->
        <div style="position: relative; width: 260px;">
            <i data-lucide="search" style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); width: 14px; height: 14px; color: var(--zinc-400, #94a3b8); pointer-events: none;"></i>
            <input
                type="text"
                id="srSearchInput"
                class="form-input"
                placeholder="Search rules..."
                oninput="handleSearchChange(this.value)"
                style="padding-left: 2.25rem; padding-right: 2rem; height: 34px; font-size: 0.78125rem; border-radius: 8px;"
            >
            <button
                type="button"
                id="srClearSearchBtn"
                onclick="handleClearSearch()"
                style="position: absolute; right: 0.6rem; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--zinc-400); display: none; padding: 0.2rem;"
            >
                <i data-lucide="x" style="width: 14px; height: 14px;"></i>
            </button>
        </div>
    </div>

    <!-- Rules Directory List Cards -->
    <div id="rulesListContainer" style="display: flex; flex-direction: column; gap: 0.75rem;">
        <!-- Populated dynamically via renderRulesList() -->
    </div>

    <!-- Empty State Container -->
    <div id="emptyRulesContainer" style="display: none; text-align: center; padding: 3.5rem 1.5rem; background: var(--zinc-50, #f8fafc); border: 1.5px dashed var(--zinc-300, #cbd5e1); border-radius: var(--radius-xl, 14px); color: var(--zinc-500);">
        <i data-lucide="lock" style="width: 38px; height: 38px; margin: 0 auto 0.75rem auto; color: var(--zinc-400); display: block;"></i>
        <h3 id="emptyTitle" style="font-size: 1rem; font-weight: 700; color: var(--zinc-800, #1e293b); margin: 0 0 0.35rem 0;">
            No Sharing Rules Configured
        </h3>
        <p style="font-size: 0.8125rem; max-width: 480px; margin: 0 auto 1.25rem auto; line-height: 1.45; color: var(--zinc-600);">
            Create automated rules to grant access to records meeting high deal values, sensitive pipeline stages, or specific account industries.
        </p>
        <button
            type="button"
            onclick="handleOpenCreateModal()"
            class="btn btn-secondary btn-sm"
            style="display: inline-flex; align-items: center; gap: 0.4rem;"
        >
            <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
            <span>Create First Rule</span>
        </button>
    </div>
</div>

<!-- ========================================================================= -->
<!-- CREATE / EDIT SHARING RULE MODAL                                          -->
<!-- ========================================================================= -->
<div
    id="srModalBackdrop"
    style="position: fixed; inset: 0; z-index: 99999; display: none; align-items: center; justify-content: center; padding: 1.25rem; background: rgba(9, 9, 11, 0.65); backdrop-filter: blur(5px);"
    onclick="handleCloseModal(event)"
>
    <div
        id="srModalDialog"
        style="position: relative; width: 740px; maxWidth: 95vw; maxHeight: 90vh; background: #ffffff; border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35); display: flex; flex-direction: column; overflow: hidden;"
        onclick="event.stopPropagation()"
    >
        <!-- Modal Header -->
        <div style="padding: 1.15rem 1.5rem; border-bottom: 1px solid var(--zinc-200, #e2e8f0); background: var(--zinc-50, #f8fafc); display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div style="font-size: 0.6875rem; font-weight: 700; color: var(--color-primary, #4f46e5); text-transform: uppercase; letter-spacing: 0.04em;">
                    Record Security Configuration
                </div>
                <h3 id="srModalTitle" style="font-size: 1.15rem; font-weight: 800; color: var(--zinc-950, #09090b); margin: 0.15rem 0 0 0;">
                    Create Conditional Sharing Rule
                </h3>
            </div>
            <button
                type="button"
                onclick="closeRuleModal()"
                class="btn btn-ghost btn-sm"
                style="width: 32px; height: 32px; border-radius: 50%; padding: 0; display: flex; align-items: center; justify-content: center; color: var(--zinc-500);"
            >
                <i data-lucide="x" style="width: 16px; height: 16px;"></i>
            </button>
        </div>

        <!-- Modal Body Form -->
        <form id="srRuleForm" onsubmit="handleSaveRule(event)" style="display: flex; flex-direction: column; flex: 1; min-height: 0;">
            <div style="padding: 1.25rem 1.5rem; overflow-y: auto; flex: 1; display: flex; flex-direction: column; gap: 1.15rem;">
                <!-- Error Banner -->
                <div id="srModalError" style="display: none; padding: 0.65rem 0.85rem; background: #fef2f2; border: 1px solid #fecaca; border-radius: 6px; color: #b91c1c; font-size: 0.78125rem; align-items: center; gap: 0.5rem;">
                    <i data-lucide="alert-circle" style="width: 15px; height: 15px; flex-shrink: 0;"></i>
                    <span id="srModalErrorText"></span>
                </div>

                <!-- Section 1: Basic Information -->
                <div style="display: grid; grid-template-columns: 1.4fr 1fr; gap: 1rem;">
                    <div>
                        <label class="form-label">Rule Name *</label>
                        <input
                            type="text"
                            id="formRuleName"
                            class="form-input"
                            placeholder="e.g. Enterprise Deals Shared with Legal"
                            required
                        >
                    </div>

                    <div>
                        <label class="form-label">Target CRM Module *</label>
                        <select
                            id="formRuleModule"
                            class="form-select"
                            onchange="handleModuleSelectChange(this.value)"
                        >
                            <!-- Populated dynamically -->
                        </select>
                    </div>
                </div>

                <div>
                    <label class="form-label">Operational Description</label>
                    <input
                        type="text"
                        id="formRuleDescription"
                        class="form-input"
                        placeholder="e.g. Shares all high value enterprise opportunities with cross-functional legal and solution architects"
                    >
                </div>

                <!-- Section 2: Rule Type Selector -->
                <div style="border: 1px solid var(--zinc-200, #e2e8f0); border-radius: 10px; padding: 1rem; background: #fafafa;">
                    <label class="form-label" style="font-weight: 700; margin-bottom: 0.5rem;">
                        Rule Evaluation Type
                    </label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                        <label
                            id="ruleTypeLabelCriteria"
                            style="display: flex; align-items: flex-start; gap: 0.5rem; padding: 0.65rem 0.85rem; border-radius: 8px; background: #eff6ff; border: 1.5px solid var(--color-primary, #4f46e5); cursor: pointer;"
                        >
                            <input
                                type="radio"
                                name="formRuleType"
                                value="criteria_based"
                                checked
                                onchange="handleRuleTypeRadioChange('criteria_based')"
                                style="margin-top: 3px;"
                            >
                            <div>
                                <div style="font-size: 0.8125rem; font-weight: 700; color: var(--zinc-900, #09090b);">
                                    Criteria-Based
                                </div>
                                <div style="font-size: 0.7rem; color: var(--zinc-500, #64748b); margin-top: 2px;">
                                    Evaluated against field attributes (e.g. Amount &ge; $1M or Stage = Negotiation)
                                </div>
                            </div>
                        </label>

                        <label
                            id="ruleTypeLabelOwnership"
                            style="display: flex; align-items: flex-start; gap: 0.5rem; padding: 0.65rem 0.85rem; border-radius: 8px; background: #ffffff; border: 1.5px solid var(--zinc-200, #e2e8f0); cursor: pointer;"
                        >
                            <input
                                type="radio"
                                name="formRuleType"
                                value="ownership_based"
                                onchange="handleRuleTypeRadioChange('ownership_based')"
                                style="margin-top: 3px;"
                            >
                            <div>
                                <div style="font-size: 0.8125rem; font-weight: 700; color: var(--zinc-900, #09090b);">
                                    Ownership-Based
                                </div>
                                <div style="font-size: 0.7rem; color: var(--zinc-500, #64748b); margin-top: 2px;">
                                    Shared based on the record owner's Role or Security Profile
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Section 3a: Criteria Builder (if Criteria-Based) -->
                <div id="sectionCriteriaBuilder" style="border: 1px solid var(--zinc-200, #e2e8f0); border-radius: 10px; padding: 1rem; background: #ffffff;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
                        <div>
                            <div style="font-size: 0.8125rem; font-weight: 700; color: var(--zinc-900, #09090b);">
                                Field Criteria Conditions (ALL Must Match)
                            </div>
                            <div style="font-size: 0.7rem; color: var(--zinc-500, #64748b);">
                                Define the field parameters required to trigger automatic record sharing.
                            </div>
                        </div>
                        <button
                            type="button"
                            onclick="handleAddCondition()"
                            class="btn btn-secondary btn-sm"
                            style="font-size: 0.725rem; padding: 0.25rem 0.55rem; font-weight: 600;"
                        >
                            + Add Condition
                        </button>
                    </div>

                    <div id="criteriaConditionsContainer" style="display: flex; flex-direction: column; gap: 0.5rem;">
                        <!-- Populated dynamically via renderCriteriaConditions() -->
                    </div>
                </div>

                <!-- Section 3b: Ownership Selector (if Ownership-Based) -->
                <div id="sectionOwnershipBuilder" style="display: none; border: 1px solid var(--zinc-200, #e2e8f0); border-radius: 10px; padding: 1rem; background: #ffffff;">
                    <div style="font-size: 0.8125rem; font-weight: 700; color: var(--zinc-900, #09090b); margin-bottom: 0.75rem;">
                        Records Owned By Members Of:
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                        <div>
                            <label class="form-label" style="font-size: 0.725rem;">Owner's Security Profile</label>
                            <select
                                id="formOwnershipProfileId"
                                class="form-select"
                                style="height: 38px; font-size: 0.78125rem;"
                            >
                                <!-- Populated dynamically -->
                            </select>
                        </div>

                        <div>
                            <label class="form-label" style="font-size: 0.725rem;">Owner's Hierarchy Role</label>
                            <select
                                id="formOwnershipRoleId"
                                class="form-select"
                                style="height: 38px; font-size: 0.78125rem;"
                            >
                                <!-- Populated dynamically -->
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Section 4: Share With & Access Level -->
                <div style="border: 1px solid var(--zinc-200, #e2e8f0); border-radius: 10px; padding: 1rem; background: #ffffff;">
                    <div style="font-size: 0.8125rem; font-weight: 700; color: var(--zinc-900, #09090b); margin-bottom: 0.75rem;">
                        Grant Record Access To:
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1.5fr 1fr; gap: 0.75rem;">
                        <div>
                            <label class="form-label" style="font-size: 0.725rem;">Recipient Type *</label>
                            <select
                                id="formShareWithType"
                                class="form-select"
                                style="height: 38px; font-size: 0.78125rem;"
                                onchange="handleShareWithTypeChange(this.value)"
                            >
                                <option value="profile">Security Profile</option>
                                <option value="role">User Hierarchy Role</option>
                                <option value="user">Specific User</option>
                            </select>
                        </div>

                        <div>
                            <label class="form-label" style="font-size: 0.725rem;">Target Recipient *</label>
                            <select
                                id="formShareWithId"
                                class="form-select"
                                style="height: 38px; font-size: 0.78125rem;"
                            >
                                <!-- Populated dynamically -->
                            </select>
                        </div>

                        <div>
                            <label class="form-label" style="font-size: 0.725rem;">Access Level *</label>
                            <select
                                id="formAccessLevel"
                                class="form-select"
                                style="height: 38px; font-size: 0.78125rem;"
                            >
                                <option value="read">Read-Only</option>
                                <option value="read_write">Read / Write</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Section 5: Active Switch -->
                <div style="display: flex; align-items: center; gap: 0.6rem; margin-top: 0.25rem;">
                    <input
                        type="checkbox"
                        id="formRuleActive"
                        checked
                        style="cursor: pointer; width: 16px; height: 16px; accent-color: var(--color-primary, #4f46e5);"
                    >
                    <label for="formRuleActive" style="font-size: 0.8125rem; font-weight: 600; color: var(--zinc-900, #09090b); cursor: pointer; margin: 0;">
                        Rule is Active (Immediately evaluates and shares matching records)
                    </label>
                </div>
            </div>

            <!-- Modal Footer -->
            <div style="padding: 0.85rem 1.5rem; border-top: 1px solid var(--zinc-200, #e2e8f0); background: var(--zinc-50, #f8fafc); display: flex; align-items: center; justify-content: flex-end; gap: 0.5rem; flex-shrink: 0;">
                <button
                    type="button"
                    onclick="closeRuleModal()"
                    class="btn btn-secondary"
                    id="btnModalCancel"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    id="btnModalSubmit"
                    class="btn btn-primary"
                    style="display: flex; align-items: center; gap: 0.4rem; font-weight: 600;"
                >
                    <i id="btnModalSubmitIcon" data-lucide="check" style="width: 14px; height: 14px;"></i>
                    <span id="btnModalSubmitText">Create &amp; Evaluate</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// ============================================================================
// ENTERPRISE SHARING RULES STUDIO STATE ENGINE (1-TO-1 REACT PARITY)
// ============================================================================
const STORAGE_SHARING_RULES_KEY = 'dealsync_sharing_rules';
const STORAGE_PROFILES_KEY = 'dealsync_profiles';

const MODULE_DISPLAY_NAMES = {
    opportunities: 'Opportunities',
    leads: 'Leads',
    accounts: 'Accounts',
    payments: 'Invoices & Payments',
    purchase_deals: 'Purchase Deals',
    shipments: 'Shipping & Freight'
};

const OPERATOR_LABELS = {
    equals: 'Equals (=)',
    not_equals: 'Not Equals (!=)',
    greater_than: 'Greater Than (>)',
    less_than: 'Less Than (<)',
    greater_or_equal: 'Greater or Equal (>=)',
    less_or_equal: 'Less or Equal (<=)',
    contains: 'Contains',
    is_set: 'Is Populated',
    is_empty: 'Is Blank'
};

const MODULE_CRITERIA_FIELDS = {
    opportunities: [
        { key: 'name', label: 'Opportunity Name', type: 'string' },
        { key: 'amount', label: 'Deal Amount ($)', type: 'number' },
        { key: 'stage', label: 'Stage', type: 'select', options: ['Discovery', 'Proposal', 'Negotiation', 'Closed Won', 'Closed Lost'] },
        { key: 'probability', label: 'Win Probability (%)', type: 'number' },
        { key: 'lead_source', label: 'Lead Source', type: 'string' },
        { key: 'expected_close_date', label: 'Expected Close Date', type: 'date' }
    ],
    leads: [
        { key: 'full_name', label: 'Lead Name', type: 'string' },
        { key: 'company', label: 'Company / Organization', type: 'string' },
        { key: 'status', label: 'Status', type: 'select', options: ['New', 'Contacted', 'Qualified', 'Converted', 'Lost'] },
        { key: 'score', label: 'Propensity Score (0-100)', type: 'number' },
        { key: 'source', label: 'Lead Source', type: 'string' },
        { key: 'email', label: 'Email Address', type: 'string' }
    ],
    accounts: [
        { key: 'name', label: 'Account Name', type: 'string' },
        { key: 'type', label: 'Account Type', type: 'select', options: ['Customer', 'Prospect', 'Vendor', 'Partner'] },
        { key: 'industry', label: 'Industry Sector', type: 'string' },
        { key: 'annual_revenue', label: 'Annual Revenue ($)', type: 'number' },
        { key: 'credit_limit', label: 'Credit Limit ($)', type: 'number' }
    ],
    payments: [
        { key: 'amount', label: 'Invoiced Amount ($)', type: 'number' },
        { key: 'paid_amount', label: 'Paid / Collected ($)', type: 'number' },
        { key: 'status', label: 'Payment Status', type: 'select', options: ['Paid', 'Partially Paid', 'Issued', 'Overdue'] },
        { key: 'type', label: 'Payment Type', type: 'string' }
    ],
    purchase_deals: [
        { key: 'po_number', label: 'PO Reference #', type: 'string' },
        { key: 'title', label: 'PO Title', type: 'string' },
        { key: 'amount', label: 'Committed Spend ($)', type: 'number' },
        { key: 'stage', label: 'Procurement Stage', type: 'select', options: ['Draft', 'RFQ Sent', 'Quote Received', 'Under Approval', 'PO Issued', 'Completed'] }
    ],
    shipments: [
        { key: 'tracking_number', label: 'Tracking #', type: 'string' },
        { key: 'carrier', label: 'Carrier', type: 'string' },
        { key: 'status', label: 'Status', type: 'select', options: ['Pending', 'In Transit', 'Customs Clearance', 'Out for Delivery', 'Delivered'] }
    ]
};

const SEED_PROFILES = [
    { id: 'prof-admin', name: 'System Administrator', description: 'Unrestricted administrative access to all records and settings' },
    { id: 'prof-sales', name: 'Standard Sales Representative', description: 'Read/Write access to Leads, Accounts, and Opportunities' },
    { id: 'prof-procurement', name: 'Procurement Specialist', description: 'Read/Write access to Vendors and Purchase Deals' },
    { id: 'prof-finance', name: 'Corporate Finance & Controller', description: 'Read/Write access to Invoices and Bank Reconciliations' },
    { id: 'prof-auditor', name: 'Read-Only Compliance Auditor', description: 'Read-only access across all records with no editing rights' }
];

const SEED_ROLES = [
    { id: 'role-ceo', name: 'Executive / CEO' },
    { id: 'role-vp-sales', name: 'VP of Global Sales' },
    { id: 'role-sales-exec', name: 'Sales Executive' },
    { id: 'role-procurement-dir', name: 'Procurement Director' },
    { id: 'role-finance-mgr', name: 'Finance Manager' }
];

const SEED_USERS = [
    { id: 'usr-101', full_name: 'Alex Morgan', email: 'alex.morgan@acmecorp.com', role_id: 'role-ceo', profile_id: 'prof-admin' },
    { id: 'usr-102', full_name: 'Sarah Connor', email: 'sarah.connor@acmecorp.com', role_id: 'role-vp-sales', profile_id: 'prof-sales' },
    { id: 'usr-103', full_name: 'David Chen', email: 'david.chen@acmecorp.com', role_id: 'role-procurement-dir', profile_id: 'prof-procurement' },
    { id: 'usr-104', full_name: 'Elena Rostova', email: 'elena.rostova@acmecorp.com', role_id: 'role-finance-mgr', profile_id: 'prof-finance' },
    { id: 'usr-105', full_name: 'Marcus Vance', email: 'marcus.vance@acmecorp.com', role_id: 'role-sales-exec', profile_id: 'prof-sales' }
];

const SEED_SHARING_RULES = [
    {
        id: 'rule-mega-deals',
        tenant_id: 'tenant-default',
        module: 'opportunities',
        name: 'Mega Deals Procurement Sync',
        description: 'Automatically grants procurement specialists read-only visibility into pipeline opportunities exceeding $1,000,000 for early vendor alignment.',
        rule_type: 'criteria_based',
        criteria: [
            { field: 'amount', operator: 'greater_or_equal', value: '1000000' }
        ],
        share_with_type: 'profile',
        share_with_id: 'prof-procurement',
        share_with_profile_name: 'Procurement Specialist',
        access_level: 'read',
        is_active: 1,
        shared_records_count: 48,
        created_at: new Date().toISOString()
    },
    {
        id: 'rule-enterprise-accounts',
        tenant_id: 'tenant-default',
        module: 'accounts',
        name: 'Enterprise Customer Account Visibility',
        description: 'Shares top-tier enterprise customer accounts ($5M+ annual revenue) with Executive leadership for strategic relationship management.',
        rule_type: 'criteria_based',
        criteria: [
            { field: 'type', operator: 'equals', value: 'Customer' },
            { field: 'annual_revenue', operator: 'greater_or_equal', value: '5000000' }
        ],
        share_with_type: 'role',
        share_with_id: 'role-ceo',
        share_with_role_name: 'Executive / CEO',
        access_level: 'read_write',
        is_active: 1,
        shared_records_count: 36,
        created_at: new Date().toISOString()
    },
    {
        id: 'rule-procurement-audit',
        tenant_id: 'tenant-default',
        module: 'purchase_deals',
        name: 'High-Value PO Spend Fiscal Audit',
        description: 'Automatically mirrors all purchase deals with committed spend of $500,000+ to corporate finance for proactive budget approval.',
        rule_type: 'criteria_based',
        criteria: [
            { field: 'amount', operator: 'greater_or_equal', value: '500000' }
        ],
        share_with_type: 'profile',
        share_with_id: 'prof-finance',
        share_with_profile_name: 'Corporate Finance & Controller',
        access_level: 'read',
        is_active: 1,
        shared_records_count: 28,
        created_at: new Date().toISOString()
    },
    {
        id: 'rule-sales-cross-collab',
        tenant_id: 'tenant-default',
        module: 'leads',
        name: 'Cross-Territory Sales Lead Collaboration',
        description: 'Shares high-propensity leads owned by standard sales reps with regional sales executives for joint enterprise pitching.',
        rule_type: 'ownership_based',
        ownership_profile_id: 'prof-sales',
        ownership_profile_name: 'Standard Sales Representative',
        ownership_role_id: '',
        share_with_type: 'role',
        share_with_id: 'role-sales-exec',
        share_with_role_name: 'Sales Executive',
        access_level: 'read_write',
        is_active: 1,
        shared_records_count: 64,
        created_at: new Date().toISOString()
    }
];

// App State
let rules = [];
let profiles = [];
let roles = [...SEED_ROLES];
let users = [...SEED_USERS];

let selectedModule = 'all';
let searchFilter = '';
let editingRuleId = null;

// Form criteria conditions
let currentFormCriteria = [];

function initSharingRulesStudio() {
    try {
        // Load profiles from storage or seeds
        const storedProfiles = localStorage.getItem(STORAGE_PROFILES_KEY);
        profiles = storedProfiles ? JSON.parse(storedProfiles) : [...SEED_PROFILES];

        // Load sharing rules from storage or seeds
        const storedRules = localStorage.getItem(STORAGE_SHARING_RULES_KEY);
        if (storedRules) {
            rules = JSON.parse(storedRules);
        } else {
            rules = [...SEED_SHARING_RULES];
            localStorage.setItem(STORAGE_SHARING_RULES_KEY, JSON.stringify(rules));
        }
    } catch (e) {
        profiles = [...SEED_PROFILES];
        rules = [...SEED_SHARING_RULES];
    }

    renderModuleTabs();
    renderKpiBadges();
    renderRulesList();
    if (window.lucide) lucide.createIcons();
}

function saveRulesToStorage() {
    try {
        localStorage.setItem(STORAGE_SHARING_RULES_KEY, JSON.stringify(rules));
    } catch (e) {
        console.error('Failed to save sharing rules to localStorage', e);
    }
}

function renderKpiBadges() {
    const activeRules = rules.filter(r => r.is_active).length;
    const criteriaRules = rules.filter(r => r.rule_type === 'criteria_based').length;
    const ownershipRules = rules.filter(r => r.rule_type === 'ownership_based').length;
    const totalSharedRecords = rules.reduce((acc, r) => acc + (r.is_active ? (r.shared_records_count || 0) : 0), 0);

    const elActive = document.getElementById('kpiActiveRules');
    const elCriteria = document.getElementById('kpiCriteriaRules');
    const elOwnership = document.getElementById('kpiOwnershipRules');
    const elShared = document.getElementById('kpiSharedCount');

    if (elActive) elActive.textContent = `${activeRules} Rules`;
    if (elCriteria) elCriteria.textContent = `${criteriaRules} Rules`;
    if (elOwnership) elOwnership.textContent = `${ownershipRules} Rules`;
    if (elShared) elShared.textContent = `${totalSharedRecords} Records`;
}

function renderModuleTabs() {
    const container = document.getElementById('srModuleTabsContainer');
    if (!container) return;

    let html = `
        <button
            type="button"
            onclick="setModuleFilter('all')"
            class="sr-tab-btn ${selectedModule === 'all' ? 'is-active' : ''}"
        >
            <span>All Modules</span>
            <span class="sr-tab-badge">${rules.length}</span>
        </button>
    `;

    Object.entries(MODULE_DISPLAY_NAMES).forEach(([modKey, modLabel]) => {
        const count = rules.filter(r => r.module === modKey).length;
        const isSelected = selectedModule === modKey;

        html += `
            <button
                type="button"
                onclick="setModuleFilter('${modKey}')"
                class="sr-tab-btn ${isSelected ? 'is-active' : ''}"
            >
                <span>${escapeHtml(modLabel)}</span>
                ${count > 0 ? `<span class="sr-tab-badge">${count}</span>` : ''}
            </button>
        `;
    });

    container.innerHTML = html;
}

function setModuleFilter(mod) {
    selectedModule = mod;
    renderModuleTabs();
    renderRulesList();
}

function handleSearchChange(query) {
    searchFilter = query;
    const clearBtn = document.getElementById('srClearSearchBtn');
    if (clearBtn) clearBtn.style.display = query ? 'block' : 'none';
    renderRulesList();
}

function handleClearSearch() {
    searchFilter = '';
    const input = document.getElementById('srSearchInput');
    if (input) input.value = '';
    const clearBtn = document.getElementById('srClearSearchBtn');
    if (clearBtn) clearBtn.style.display = 'none';
    renderRulesList();
}

function getFilteredRules() {
    return rules.filter(r => {
        if (selectedModule !== 'all' && r.module !== selectedModule) return false;
        if (searchFilter.trim()) {
            const term = searchFilter.toLowerCase().trim();
            const matchName = r.name.toLowerCase().includes(term);
            const matchDesc = (r.description || '').toLowerCase().includes(term);
            const target = (r.share_with_profile_name || r.share_with_role_name || r.share_with_user_name || '').toLowerCase();
            const matchTarget = target.includes(term);
            return matchName || matchDesc || matchTarget;
        }
        return true;
    });
}

function renderRulesList() {
    const container = document.getElementById('rulesListContainer');
    const emptyBox = document.getElementById('emptyRulesContainer');
    const emptyTitle = document.getElementById('emptyTitle');
    if (!container || !emptyBox) return;

    const filtered = getFilteredRules();

    if (filtered.length === 0) {
        container.style.display = 'none';
        emptyBox.style.display = 'block';
        if (emptyTitle) {
            emptyTitle.textContent = selectedModule !== 'all'
                ? `No Sharing Rules Configured for ${MODULE_DISPLAY_NAMES[selectedModule] || selectedModule}`
                : 'No Sharing Rules Configured';
        }
        if (window.lucide) lucide.createIcons();
        return;
    }

    container.style.display = 'flex';
    emptyBox.style.display = 'none';

    container.innerHTML = filtered.map(rule => {
        const isRw = rule.access_level === 'read_write';
        const targetName =
            rule.share_with_type === 'profile'
                ? rule.share_with_profile_name || getProfileName(rule.share_with_id)
                : rule.share_with_type === 'role'
                ? rule.share_with_role_name || getRoleName(rule.share_with_id)
                : rule.share_with_user_name || getUserName(rule.share_with_id);

        const leftBorderColor = rule.is_active
            ? (isRw ? '#10b981' : 'var(--color-primary, #4f46e5)')
            : '#cbd5e1';

        return `
            <div
                class="rule-card"
                style="
                    border-left: 4px solid ${leftBorderColor};
                    opacity: ${rule.is_active ? '1' : '0.65'};
                "
            >
                <!-- Left: Info & Criteria -->
                <div style="display: flex; flex-direction: column; gap: 0.4rem; flex: 1; min-width: 0;">
                    <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                        <h4 style="font-size: 0.95rem; font-weight: 800; color: var(--zinc-950, #09090b); margin: 0; letter-spacing: -0.01em;">
                            ${escapeHtml(rule.name)}
                        </h4>
                        <span class="badge badge-purple" style="font-size: 0.675rem; font-weight: 700;">
                            ${escapeHtml(MODULE_DISPLAY_NAMES[rule.module] || rule.module)}
                        </span>
                        <span class="badge ${rule.rule_type === 'criteria_based' ? 'badge-blue' : 'badge-amber'}" style="font-size: 0.65rem; font-weight: 700;">
                            ${rule.rule_type === 'criteria_based' ? 'Criteria-Based' : 'Ownership-Based'}
                        </span>
                        <span class="badge ${isRw ? 'badge-emerald' : 'badge-gray'}" style="font-size: 0.65rem; font-weight: 700; ${isRw ? 'background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0;' : 'background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0;'}">
                            ${isRw ? '✓ Read / Write Access' : '🔒 Read-Only Access'}
                        </span>
                    </div>

                    ${rule.description ? `
                        <p style="font-size: 0.75rem; color: var(--zinc-500, #64748b); margin: 0; line-height: 1.4;">
                            ${escapeHtml(rule.description)}
                        </p>
                    ` : ''}

                    <!-- Conditions Summary Pills -->
                    <div style="display: flex; align-items: center; gap: 0.4rem; flex-wrap: wrap; margin-top: 0.2rem;">
                        <span style="font-size: 0.6875rem; font-weight: 600; color: var(--zinc-500, #64748b);">
                            When:
                        </span>
                        ${rule.rule_type === 'criteria_based' && Array.isArray(rule.criteria) && rule.criteria.length > 0 ? (
                            rule.criteria.map(c => `
                                <span style="
                                    font-size: 0.6875rem;
                                    font-weight: 600;
                                    background: var(--zinc-100, #f1f5f9);
                                    color: var(--zinc-800, #1e293b);
                                    border: 1px solid var(--zinc-300, #cbd5e1);
                                    padding: 1px 6px;
                                    border-radius: 4px;
                                ">
                                    <strong>${escapeHtml(c.field)}</strong> ${escapeHtml((c.operator || '').replace(/_/g, ' '))} ${c.value ? `"${escapeHtml(c.value)}"` : ''}
                                </span>
                            `).join('')
                        ) : rule.rule_type === 'ownership_based' ? `
                            <span style="font-size: 0.6875rem; background: #fef3c7; color: #92400e; border: 1px solid #fde68a; padding: 1px 6px; border-radius: 4px; font-weight: 600;">
                                Owner in ${escapeHtml(rule.ownership_role_name || rule.ownership_profile_name || 'Designated Group')}
                            </span>
                        ` : `
                            <span style="font-size: 0.6875rem; color: var(--zinc-400);">All records</span>
                        `}

                        <i data-lucide="arrow-right" style="width: 12px; height: 12px; color: var(--zinc-400);"></i>

                        <span style="font-size: 0.6875rem; font-weight: 600; color: var(--zinc-500, #64748b);">
                            Share With:
                        </span>
                        <span style="
                            font-size: 0.6875rem;
                            font-weight: 700;
                            background: var(--color-primary-subtle, #eef2ff);
                            color: var(--color-primary, #4f46e5);
                            border: 1px solid var(--color-primary-border, #c7d2fe);
                            padding: 1px 6px;
                            border-radius: 4px;
                            display: inline-flex;
                            align-items: center;
                            gap: 3px;
                        ">
                            <i data-lucide="${rule.share_with_type === 'profile' ? 'lock' : (rule.share_with_type === 'role' ? 'users' : 'user-check')}" style="width: 11px; height: 11px;"></i>
                            <span>${escapeHtml(targetName)} (${escapeHtml((rule.share_with_type || '').toUpperCase())})</span>
                        </span>
                    </div>
                </div>

                <!-- Right: Metrics & Actions -->
                <div style="display: flex; align-items: center; gap: 0.75rem; flex-shrink: 0;">
                    <!-- Evaluated Shares Count -->
                    <div style="text-align: right; margin-right: 0.25rem;">
                        <div style="font-size: 0.875rem; font-weight: 800; color: var(--zinc-900, #09090b);">
                            ${rule.is_active ? (rule.shared_records_count || 0) : 0}
                        </div>
                        <div style="font-size: 0.65rem; color: var(--zinc-500, #64748b); text-transform: uppercase; letter-spacing: 0.03em; font-weight: 600;">
                            Shared Records
                        </div>
                    </div>

                    <!-- Active / Inactive Switch Pill -->
                    <button
                        type="button"
                        onclick="handleToggleRuleActive('${rule.id}')"
                        style="
                            padding: 0.25rem 0.55rem;
                            border-radius: 4px;
                            font-size: 0.6875rem;
                            font-weight: 700;
                            border: 1px solid;
                            cursor: pointer;
                            background: ${rule.is_active ? '#dcfce7' : 'var(--zinc-100, #f1f5f9)'};
                            border-color: ${rule.is_active ? '#86efac' : 'var(--zinc-300, #cbd5e1)'};
                            color: ${rule.is_active ? '#15803d' : 'var(--zinc-500, #64748b)'};
                        "
                        title="${rule.is_active ? 'Click to deactivate rule' : 'Click to activate rule'}"
                    >
                        ${rule.is_active ? 'Active' : 'Disabled'}
                    </button>

                    <!-- Recalculate Rule Button -->
                    <button
                        type="button"
                        id="recalcBtn_${rule.id}"
                        onclick="handleRecalculateSingleRule('${rule.id}')"
                        class="btn btn-ghost btn-sm"
                        style="width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center; color: var(--zinc-600);"
                        title="Force Recalculate Shares"
                    >
                        <i id="recalcIcon_${rule.id}" data-lucide="refresh-cw" style="width: 14px; height: 14px;"></i>
                    </button>

                    <!-- Edit Button -->
                    <button
                        type="button"
                        onclick="handleOpenEditModal('${rule.id}')"
                        class="btn btn-ghost btn-sm"
                        style="width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center; color: var(--zinc-600);"
                        title="Edit Rule"
                    >
                        <i data-lucide="edit-2" style="width: 14px; height: 14px;"></i>
                    </button>

                    <!-- Delete Button -->
                    <button
                        type="button"
                        onclick="handleDeleteRule('${rule.id}')"
                        class="btn btn-ghost btn-sm"
                        style="width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center; color: #ef4444;"
                        title="Delete Rule"
                    >
                        <i data-lucide="trash-2" style="width: 14px; height: 14px;"></i>
                    </button>
                </div>
            </div>
        `;
    }).join('');

    if (window.lucide) lucide.createIcons();
}

function getProfileName(id) {
    const p = profiles.find(x => x.id === id);
    return p ? p.name : id;
}

function getRoleName(id) {
    const r = roles.find(x => x.id === id);
    return r ? r.name : id;
}

function getUserName(id) {
    const u = users.find(x => x.id === id);
    return u ? u.full_name : id;
}

function handleToggleRuleActive(id) {
    const rule = rules.find(r => r.id === id);
    if (!rule) return;

    rule.is_active = rule.is_active ? 0 : 1;
    saveRulesToStorage();
    renderKpiBadges();
    renderRulesList();
    showToast(`Rule "${rule.name}" is now ${rule.is_active ? 'Active' : 'Disabled'}.`);
}

function handleRecalculateSingleRule(id) {
    const rule = rules.find(r => r.id === id);
    if (!rule) return;

    const icon = document.getElementById(`recalcIcon_${id}`);
    if (icon) icon.classList.add('spin');

    setTimeout(() => {
        if (icon) icon.classList.remove('spin');
        // Simulate recalculation variation
        if (rule.is_active) {
            rule.shared_records_count = Math.max(12, (rule.shared_records_count || 30) + Math.floor(Math.random() * 5) - 2);
        }
        saveRulesToStorage();
        renderKpiBadges();
        renderRulesList();
        showToast(`Recalculation complete for "${rule.name}". Evaluated ${rule.shared_records_count} active record shares.`);
    }, 400);
}

function handleRecalculateAllRules() {
    const icon = document.getElementById('recalcAllIcon');
    if (icon) icon.classList.add('spin');

    setTimeout(() => {
        if (icon) icon.classList.remove('spin');
        rules.forEach(r => {
            if (r.is_active) {
                r.shared_records_count = Math.max(10, (r.shared_records_count || 25) + Math.floor(Math.random() * 4) - 1);
            }
        });
        saveRulesToStorage();
        renderKpiBadges();
        renderRulesList();
        showToast(`All ${rules.length} sharing rules recalculated successfully! Zero security collisions.`);
    }, 600);
}

function handleDeleteRule(id) {
    const rule = rules.find(r => r.id === id);
    if (!rule) return;

    if (!confirm(`Are you sure you want to delete sharing rule "${rule.name}"? All associated record access grants will be revoked immediately.`)) {
        return;
    }

    rules = rules.filter(r => r.id !== id);
    saveRulesToStorage();
    renderModuleTabs();
    renderKpiBadges();
    renderRulesList();
    showToast(`Sharing Rule "${rule.name}" deleted.`);
}

// ============================================================================
// MODAL & CONDITION BUILDER LOGIC
// ============================================================================
function handleOpenCreateModal() {
    editingRuleId = null;
    document.getElementById('srModalTitle').textContent = 'Create Conditional Sharing Rule';
    document.getElementById('btnModalSubmitText').textContent = 'Create & Evaluate';
    document.getElementById('srModalError').style.display = 'none';

    // Reset Form
    document.getElementById('formRuleName').value = '';
    document.getElementById('formRuleDescription').value = '';
    document.getElementById('formRuleActive').checked = true;

    // Populate Modules Select
    populateModuleSelect('opportunities');
    handleRuleTypeRadioChange('criteria_based');

    // Default Criteria
    currentFormCriteria = [
        { field: 'amount', operator: 'greater_or_equal', value: '1000000' }
    ];
    renderCriteriaConditions();

    // Populate Recipients
    populateOwnershipDropdowns();
    document.getElementById('formShareWithType').value = 'profile';
    populateShareWithIdDropdown('profile');
    document.getElementById('formAccessLevel').value = 'read';

    openRuleModal();
}

function handleOpenEditModal(id) {
    const rule = rules.find(r => r.id === id);
    if (!rule) return;

    editingRuleId = id;
    document.getElementById('srModalTitle').textContent = `Edit Sharing Rule: ${rule.name}`;
    document.getElementById('btnModalSubmitText').textContent = 'Update & Recalculate';
    document.getElementById('srModalError').style.display = 'none';

    // Populate Form
    document.getElementById('formRuleName').value = rule.name;
    document.getElementById('formRuleDescription').value = rule.description || '';
    document.getElementById('formRuleActive').checked = Boolean(rule.is_active);

    populateModuleSelect(rule.module);
    handleRuleTypeRadioChange(rule.rule_type);

    currentFormCriteria = Array.isArray(rule.criteria) && rule.criteria.length > 0
        ? JSON.parse(JSON.stringify(rule.criteria))
        : [{ field: 'amount', operator: 'greater_or_equal', value: '1000000' }];
    renderCriteriaConditions();

    populateOwnershipDropdowns(rule.ownership_profile_id, rule.ownership_role_id);
    document.getElementById('formShareWithType').value = rule.share_with_type;
    populateShareWithIdDropdown(rule.share_with_type, rule.share_with_id);
    document.getElementById('formAccessLevel').value = rule.access_level;

    openRuleModal();
}

function openRuleModal() {
    const modal = document.getElementById('srModalBackdrop');
    if (modal) modal.style.display = 'flex';
    if (window.lucide) lucide.createIcons();
}

function closeRuleModal() {
    const modal = document.getElementById('srModalBackdrop');
    if (modal) modal.style.display = 'none';
}

function handleCloseModal(e) {
    if (e.target.id === 'srModalBackdrop') {
        closeRuleModal();
    }
}

function populateModuleSelect(selectedKey) {
    const select = document.getElementById('formRuleModule');
    if (!select) return;

    select.innerHTML = Object.entries(MODULE_DISPLAY_NAMES).map(([k, label]) => `
        <option value="${k}" ${k === selectedKey ? 'selected' : ''}>${escapeHtml(label)}</option>
    `).join('');
}

function handleModuleSelectChange(newMod) {
    const defaultField = MODULE_CRITERIA_FIELDS[newMod]?.[0]?.key || 'name';
    currentFormCriteria = [
        { field: defaultField, operator: 'equals', value: '' }
    ];
    renderCriteriaConditions();
}

function handleRuleTypeRadioChange(type) {
    const isCriteria = type === 'criteria_based';
    const criteriaSection = document.getElementById('sectionCriteriaBuilder');
    const ownershipSection = document.getElementById('sectionOwnershipBuilder');
    const criteriaLabel = document.getElementById('ruleTypeLabelCriteria');
    const ownershipLabel = document.getElementById('ruleTypeLabelOwnership');

    if (criteriaSection) criteriaSection.style.display = isCriteria ? 'block' : 'none';
    if (ownershipSection) ownershipSection.style.display = isCriteria ? 'none' : 'block';

    if (criteriaLabel) {
        criteriaLabel.style.background = isCriteria ? '#eff6ff' : '#ffffff';
        criteriaLabel.style.borderColor = isCriteria ? 'var(--color-primary, #4f46e5)' : 'var(--zinc-200, #e2e8f0)';
    }
    if (ownershipLabel) {
        ownershipLabel.style.background = !isCriteria ? '#eff6ff' : '#ffffff';
        ownershipLabel.style.borderColor = !isCriteria ? 'var(--color-primary, #4f46e5)' : 'var(--zinc-200, #e2e8f0)';
    }

    const radios = document.getElementsByName('formRuleType');
    radios.forEach(r => {
        if (r.value === type) r.checked = true;
    });
}

function handleAddCondition() {
    const mod = document.getElementById('formRuleModule').value || 'opportunities';
    const defaultField = MODULE_CRITERIA_FIELDS[mod]?.[0]?.key || 'name';
    currentFormCriteria.push({ field: defaultField, operator: 'equals', value: '' });
    renderCriteriaConditions();
}

function handleRemoveCondition(index) {
    if (currentFormCriteria.length <= 1) {
        alert('You must have at least one criteria condition.');
        return;
    }
    currentFormCriteria.splice(index, 1);
    renderCriteriaConditions();
}

function handleConditionFieldChange(index, newField) {
    if (currentFormCriteria[index]) {
        currentFormCriteria[index].field = newField;
        currentFormCriteria[index].value = '';
    }
    renderCriteriaConditions();
}

function handleConditionOperatorChange(index, newOp) {
    if (currentFormCriteria[index]) {
        currentFormCriteria[index].operator = newOp;
    }
    renderCriteriaConditions();
}

function handleConditionValueChange(index, val) {
    if (currentFormCriteria[index]) {
        currentFormCriteria[index].value = val;
    }
}

function renderCriteriaConditions() {
    const container = document.getElementById('criteriaConditionsContainer');
    if (!container) return;

    const mod = document.getElementById('formRuleModule')?.value || 'opportunities';
    const availableFields = MODULE_CRITERIA_FIELDS[mod] || [];

    container.innerHTML = currentFormCriteria.map((cond, idx) => {
        const noValNeeded = cond.operator === 'is_set' || cond.operator === 'is_empty';

        return `
            <div style="display: grid; grid-template-columns: 1.2fr 1.2fr 1.4fr 34px; gap: 0.45rem; align-items: center; background: var(--zinc-50, #f8fafc); padding: 0.45rem; border-radius: 6px; border: 1px solid var(--zinc-200, #e2e8f0);">
                <!-- Field -->
                <select
                    class="form-select"
                    style="height: 32px; font-size: 0.75rem; padding-right: 1.8rem;"
                    onchange="handleConditionFieldChange(${idx}, this.value)"
                >
                    ${availableFields.map(f => `
                        <option value="${f.key}" ${f.key === cond.field ? 'selected' : ''}>${escapeHtml(f.label)}</option>
                    `).join('')}
                </select>

                <!-- Operator -->
                <select
                    class="form-select"
                    style="height: 32px; font-size: 0.75rem; padding-right: 1.8rem;"
                    onchange="handleConditionOperatorChange(${idx}, this.value)"
                >
                    ${Object.entries(OPERATOR_LABELS).map(([op, label]) => `
                        <option value="${op}" ${op === cond.operator ? 'selected' : ''}>${escapeHtml(label)}</option>
                    `).join('')}
                </select>

                <!-- Value -->
                ${!noValNeeded ? `
                    <input
                        type="text"
                        class="form-input"
                        style="height: 32px; font-size: 0.75rem;"
                        placeholder="Value..."
                        value="${escapeHtml(cond.value || '')}"
                        oninput="handleConditionValueChange(${idx}, this.value)"
                    >
                ` : `
                    <div style="font-size: 0.725rem; color: var(--zinc-400); font-style: italic; padding-left: 0.5rem;">
                        (No value needed)
                    </div>
                `}

                <!-- Remove Button -->
                <button
                    type="button"
                    onclick="handleRemoveCondition(${idx})"
                    class="btn btn-ghost btn-sm"
                    style="width: 28px; height: 28px; padding: 0; display: flex; align-items: center; justify-content: center; color: var(--zinc-400);"
                    title="Remove Condition"
                >
                    <i data-lucide="x" style="width: 14px; height: 14px;"></i>
                </button>
            </div>
        `;
    }).join('');

    if (window.lucide) lucide.createIcons();
}

function populateOwnershipDropdowns(selectedProfileId = '', selectedRoleId = '') {
    const profSelect = document.getElementById('formOwnershipProfileId');
    const roleSelect = document.getElementById('formOwnershipRoleId');

    if (profSelect) {
        profSelect.innerHTML = `
            <option value="">-- Any Profile --</option>
            ${profiles.map(p => `
                <option value="${p.id}" ${p.id === selectedProfileId ? 'selected' : ''}>${escapeHtml(p.name)}</option>
            `).join('')}
        `;
    }

    if (roleSelect) {
        roleSelect.innerHTML = `
            <option value="">-- Any Role --</option>
            ${roles.map(r => `
                <option value="${r.id}" ${r.id === selectedRoleId ? 'selected' : ''}>${escapeHtml(r.name)}</option>
            `).join('')}
        `;
    }
}

function handleShareWithTypeChange(type) {
    populateShareWithIdDropdown(type);
}

function populateShareWithIdDropdown(type, selectedId = '') {
    const select = document.getElementById('formShareWithId');
    if (!select) return;

    if (type === 'profile') {
        select.innerHTML = profiles.map(p => `
            <option value="${p.id}" ${p.id === selectedId ? 'selected' : ''}>${escapeHtml(p.name)}</option>
        `).join('');
    } else if (type === 'role') {
        select.innerHTML = roles.map(r => `
            <option value="${r.id}" ${r.id === selectedId ? 'selected' : ''}>${escapeHtml(r.name)}</option>
        `).join('');
    } else if (type === 'user') {
        select.innerHTML = users.map(u => `
            <option value="${u.id}" ${u.id === selectedId ? 'selected' : ''}>${escapeHtml(u.full_name)} (${escapeHtml(u.email)})</option>
        `).join('');
    }
}

function handleSaveRule(e) {
    e.preventDefault();

    const name = document.getElementById('formRuleName').value.trim();
    if (!name) {
        showModalError('Rule name is required.');
        return;
    }

    const module = document.getElementById('formRuleModule').value;
    const description = document.getElementById('formRuleDescription').value.trim();
    const ruleType = document.querySelector('input[name="formRuleType"]:checked')?.value || 'criteria_based';

    const shareWithType = document.getElementById('formShareWithType').value;
    const shareWithId = document.getElementById('formShareWithId').value;
    if (!shareWithId) {
        showModalError('Target recipient is required.');
        return;
    }

    const accessLevel = document.getElementById('formAccessLevel').value;
    const isActive = document.getElementById('formRuleActive').checked ? 1 : 0;

    let ownershipProfileId = '';
    let ownershipProfileName = '';
    let ownershipRoleId = '';
    let ownershipRoleName = '';

    if (ruleType === 'ownership_based') {
        ownershipProfileId = document.getElementById('formOwnershipProfileId').value;
        if (ownershipProfileId) ownershipProfileName = getProfileName(ownershipProfileId);
        ownershipRoleId = document.getElementById('formOwnershipRoleId').value;
        if (ownershipRoleId) ownershipRoleName = getRoleName(ownershipRoleId);
    }

    let shareWithProfileName = '';
    let shareWithRoleName = '';
    let shareWithUserName = '';

    if (shareWithType === 'profile') shareWithProfileName = getProfileName(shareWithId);
    else if (shareWithType === 'role') shareWithRoleName = getRoleName(shareWithId);
    else if (shareWithType === 'user') shareWithUserName = getUserName(shareWithId);

    // Disable submit button during evaluation
    const submitBtn = document.getElementById('btnModalSubmit');
    const submitText = document.getElementById('btnModalSubmitText');
    submitBtn.disabled = true;
    submitText.textContent = 'Evaluating...';

    setTimeout(() => {
        submitBtn.disabled = false;

        if (editingRuleId) {
            const idx = rules.findIndex(r => r.id === editingRuleId);
            if (idx >= 0) {
                rules[idx] = {
                    ...rules[idx],
                    name,
                    description,
                    module,
                    rule_type: ruleType,
                    criteria: ruleType === 'criteria_based' ? [...currentFormCriteria] : [],
                    ownership_profile_id: ownershipProfileId,
                    ownership_profile_name: ownershipProfileName,
                    ownership_role_id: ownershipRoleId,
                    ownership_role_name: ownershipRoleName,
                    share_with_type: shareWithType,
                    share_with_id: shareWithId,
                    share_with_profile_name: shareWithProfileName,
                    share_with_role_name: shareWithRoleName,
                    share_with_user_name: shareWithUserName,
                    access_level: accessLevel,
                    is_active: isActive,
                    shared_records_count: isActive ? Math.max(14, (rules[idx].shared_records_count || 24)) : 0
                };
            }
            showToast(`Rule "${name}" updated & evaluated successfully!`);
        } else {
            const newRule = {
                id: 'rule-' + Date.now(),
                tenant_id: 'tenant-default',
                name,
                description,
                module,
                rule_type: ruleType,
                criteria: ruleType === 'criteria_based' ? [...currentFormCriteria] : [],
                ownership_profile_id: ownershipProfileId,
                ownership_profile_name: ownershipProfileName,
                ownership_role_id: ownershipRoleId,
                ownership_role_name: ownershipRoleName,
                share_with_type: shareWithType,
                share_with_id: shareWithId,
                share_with_profile_name: shareWithProfileName,
                share_with_role_name: shareWithRoleName,
                share_with_user_name: shareWithUserName,
                access_level: accessLevel,
                is_active: isActive,
                shared_records_count: isActive ? Math.floor(Math.random() * 30) + 18 : 0,
                created_at: new Date().toISOString()
            };
            rules.unshift(newRule);
            showToast(`Sharing Rule "${name}" created & evaluated!`);
        }

        saveRulesToStorage();
        closeRuleModal();
        renderModuleTabs();
        renderKpiBadges();
        renderRulesList();
    }, 300);
}

function showModalError(msg) {
    const banner = document.getElementById('srModalError');
    const textEl = document.getElementById('srModalErrorText');
    if (banner && textEl) {
        textEl.textContent = msg;
        banner.style.display = 'flex';
        if (window.lucide) lucide.createIcons();
    }
}

let toastTimeout = null;
function showToast(msg) {
    const toast = document.getElementById('srToast');
    const textEl = document.getElementById('srToastMsg');
    if (!toast || !textEl) return;

    textEl.textContent = msg;
    toast.style.display = 'flex';
    if (window.lucide) lucide.createIcons();

    if (toastTimeout) clearTimeout(toastTimeout);
    toastTimeout = setTimeout(() => {
        toast.style.display = 'none';
    }, 3500);
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

document.addEventListener('DOMContentLoaded', initSharingRulesStudio);
</script>
@endsection
