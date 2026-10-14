@extends('layouts.app')

@section('title', 'DealSync — Enterprise Sales & Operations CRM')

@section('content')

<!-- ========================================== -->
<!-- 1. DASHBOARD VIEW (#view-dashboard) -->
<!-- ========================================== -->
<div id="view-dashboard" class="tab-content-panel is-active">
    <!-- Hero Command Header -->
    <div class="dashboard-hero-card">
        <div>
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                <span class="dashboard-telemetry-badge">
                    <span class="dashboard-pulse-dot"></span>
                    System Live • Multi-Tenant RLS Enforced
                </span>
                <span style="font-size: 0.725rem; color: var(--zinc-500); font-weight: 500;">
                    Client IP: 127.0.0.1
                </span>
            </div>
            <h2 class="dashboard-hero-title">
                Executive Command Center
            </h2>
            <p class="dashboard-hero-subtitle">
                Cross-module operational telemetry tracking pipeline velocity, procurement commitments, and receivables settlement.
            </p>
        </div>

        <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
            <button type="button" class="btn btn-secondary btn-sm" onclick="alert('Exporting executive KPI snapshot to CSV...');">
                <i data-lucide="download" style="width: 14px; height: 14px;"></i>
                <span>Export CSV</span>
            </button>
            <button type="button" class="btn btn-primary btn-sm" onclick="openNewRecordModal('opportunities')">
                <i data-lucide="sparkles" style="width: 14px; height: 14px;"></i>
                <span>+ Create Opportunity</span>
            </button>
        </div>
    </div>

    <!-- Persona Filter Bar -->
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.75rem;">
        <div style="display: flex; align-items: center; gap: 0.4rem; background: var(--zinc-100); padding: 3px; border-radius: 10px; border: 1px solid var(--zinc-200);">
            <button type="button" class="persona-pill-btn btn btn-sm" data-persona="exec" onclick="switchPersona('exec')" style="background: #0f172a; color: #ffffff; border-radius: 8px;">
                Executive (CEO / VP)
            </button>
            <button type="button" class="persona-pill-btn btn btn-sm" data-persona="rep" onclick="switchPersona('rep')" style="background: #ffffff; color: #475569; border-radius: 8px;">
                Sales Representative
            </button>
            <button type="button" class="persona-pill-btn btn btn-sm" data-persona="procure" onclick="switchPersona('procure')" style="background: #ffffff; color: #475569; border-radius: 8px;">
                Procurement Specialist
            </button>
        </div>

        <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.78rem; color: var(--zinc-500);">
            <span>Date Range: <strong>Current Quarter (Q4)</strong></span>
            <span>•</span>
            <span>Basis: <strong>Created Date</strong></span>
        </div>
    </div>

    <!-- High Impact KPI Grid (Executive - Default) -->
    <div class="kpi-persona-group dashboard-kpi-grid" data-persona="exec">
        <!-- KPI 1 -->
        <div class="dashboard-kpi-card">
            <div class="dashboard-kpi-header">
                <span>Quota Attainment (Oct)</span>
                <span class="badge badge-green">↑ 84.6%</span>
            </div>
            <div>
                <div class="dashboard-kpi-main-val">$423,000</div>
                <span style="font-size: 0.75rem; color: var(--zinc-500);">Monthly Target: $500,000</span>
            </div>
            <div class="dashboard-progress-track">
                <div class="dashboard-progress-fill" style="width: 84.6%; background: #10b981;"></div>
            </div>
            <div style="display: flex; align-items: center; justify-content: space-between; font-size: 0.75rem; color: var(--zinc-500);">
                <span>$77,000 to reach target</span>
                <span style="color: var(--color-primary); font-weight: 600; cursor: pointer;">View Quotas →</span>
            </div>
        </div>

        <!-- KPI 2 -->
        <div class="dashboard-kpi-card">
            <div class="dashboard-kpi-header">
                <span>Weighted Pipeline</span>
                <span class="badge badge-indigo">28 Deals Active</span>
            </div>
            <div>
                <div class="dashboard-kpi-main-val">$1,420,500</div>
                <span style="font-size: 0.75rem; color: var(--zinc-500);">Gross Pipeline: $3.1M</span>
            </div>
            <div style="height: 18px; display: flex; align-items: flex-end; gap: 4px;">
                <div style="flex: 1; background: #c7d2fe; height: 6px; border-radius: 2px;"></div>
                <div style="flex: 1; background: #a5b4fc; height: 10px; border-radius: 2px;"></div>
                <div style="flex: 1; background: #818cf8; height: 14px; border-radius: 2px;"></div>
                <div style="flex: 1; background: #6366f1; height: 18px; border-radius: 2px;"></div>
            </div>
            <div style="display: flex; align-items: center; justify-content: space-between; font-size: 0.75rem; color: var(--zinc-500);">
                <span>Blended Win Probability: 45.8%</span>
                <span style="color: var(--color-primary); font-weight: 600; cursor: pointer;">Drilldown →</span>
            </div>
        </div>

        <!-- KPI 3 -->
        <div class="dashboard-kpi-card">
            <div class="dashboard-kpi-header">
                <span>Customer Net Retention</span>
                <span class="badge badge-green">↑ 18.2%</span>
            </div>
            <div>
                <div class="dashboard-kpi-main-val">129.4%</div>
                <span style="font-size: 0.75rem; color: var(--zinc-500);">Target: 120% Benchmark</span>
            </div>
            <div class="dashboard-progress-track">
                <div class="dashboard-progress-fill" style="width: 100%; background: #059669;"></div>
            </div>
            <div style="display: flex; align-items: center; justify-content: space-between; font-size: 0.75rem; color: var(--zinc-500);">
                <span>+129.4% NRR Expansion</span>
                <span style="color: var(--color-primary); font-weight: 600; cursor: pointer;">Accounts →</span>
            </div>
        </div>

        <!-- KPI 4 -->
        <div class="dashboard-kpi-card">
            <div class="dashboard-kpi-header">
                <span>Active Accounts Portfolio</span>
                <span class="badge badge-blue">↑ 24.8% YoY</span>
            </div>
            <div>
                <div class="dashboard-kpi-main-val">$48.6M</div>
                <span style="font-size: 0.75rem; color: var(--zinc-500);">Total Contract Value</span>
            </div>
            <div class="dashboard-progress-track">
                <div class="dashboard-progress-fill" style="width: 92%; background: #2563eb;"></div>
            </div>
            <div style="display: flex; align-items: center; justify-content: space-between; font-size: 0.75rem; color: var(--zinc-500);">
                <span>Tier-1 Enterprise: 86%</span>
                <span style="color: var(--color-primary); font-weight: 600; cursor: pointer;">Directory →</span>
            </div>
        </div>
    </div>

    <!-- High Impact KPI Grid (Sales Rep) -->
    <div class="kpi-persona-group dashboard-kpi-grid" data-persona="rep" style="display: none;">
        <div class="dashboard-kpi-card">
            <div class="dashboard-kpi-header">
                <span>My Personal Quota</span>
                <span class="badge badge-green">84.0%</span>
            </div>
            <div class="dashboard-kpi-main-val">$92,400</div>
            <div class="dashboard-progress-track"><div class="dashboard-progress-fill" style="width: 84%; background: #10b981;"></div></div>
            <div style="font-size: 0.75rem; color: var(--zinc-500);">$17,600 to target • Rep: Jordan Lee</div>
        </div>
        <div class="dashboard-kpi-card">
            <div class="dashboard-kpi-header">
                <span>My Active Deals</span>
                <span class="badge badge-indigo">11 Deals</span>
            </div>
            <div class="dashboard-kpi-main-val">$380,000</div>
            <div class="dashboard-progress-track"><div class="dashboard-progress-fill" style="width: 65%; background: #6366f1;"></div></div>
            <div style="font-size: 0.75rem; color: var(--zinc-500);">Avg Deal Size: $34,500</div>
        </div>
        <div class="dashboard-kpi-card">
            <div class="dashboard-kpi-header">
                <span>Calls Logged This Week</span>
                <span class="badge badge-green">+14 vs LW</span>
            </div>
            <div class="dashboard-kpi-main-val">42 Calls</div>
            <div class="dashboard-progress-track"><div class="dashboard-progress-fill" style="width: 84%; background: #10b981;"></div></div>
            <div style="font-size: 0.75rem; color: var(--zinc-500);">Weekly Target: 50 Calls</div>
        </div>
        <div class="dashboard-kpi-card">
            <div class="dashboard-kpi-header">
                <span>Win Conversion Rate</span>
                <span class="badge badge-blue">Top 10%</span>
            </div>
            <div class="dashboard-kpi-main-val">34.2%</div>
            <div class="dashboard-progress-track"><div class="dashboard-progress-fill" style="width: 70%; background: #2563eb;"></div></div>
            <div style="font-size: 0.75rem; color: var(--zinc-500);">Industry Average: 22%</div>
        </div>
    </div>

    <!-- High Impact KPI Grid (Procurement) -->
    <div class="kpi-persona-group dashboard-kpi-grid" data-persona="procure" style="display: none;">
        <div class="dashboard-kpi-card">
            <div class="dashboard-kpi-header">
                <span>Procurement PO Spend</span>
                <span class="badge badge-indigo">Q4 Active</span>
            </div>
            <div class="dashboard-kpi-main-val">$315,200</div>
            <div class="dashboard-progress-track"><div class="dashboard-progress-fill" style="width: 78.8%; background: #6366f1;"></div></div>
            <div style="font-size: 0.75rem; color: var(--zinc-500);">$84,800 under budget • POs: 14</div>
        </div>
        <div class="dashboard-kpi-card">
            <div class="dashboard-kpi-header">
                <span>Pending Deliveries</span>
                <span class="badge badge-amber">In Transit</span>
            </div>
            <div class="dashboard-kpi-main-val">14 Shipments</div>
            <div class="dashboard-progress-track"><div class="dashboard-progress-fill" style="width: 60%; background: #f59e0b;"></div></div>
            <div style="font-size: 0.75rem; color: var(--zinc-500);">2 Customs Held • Avg ETA: 3.2 days</div>
        </div>
        <div class="dashboard-kpi-card">
            <div class="dashboard-kpi-header">
                <span>Settlement Invoices Due</span>
                <span class="badge badge-red">8 Overdue</span>
            </div>
            <div class="dashboard-kpi-main-val">$84,500</div>
            <div class="dashboard-progress-track"><div class="dashboard-progress-fill" style="width: 55%; background: #e11d48;"></div></div>
            <div style="font-size: 0.75rem; color: var(--zinc-500);">Total AP: $142,000 • Net-30 Terms</div>
        </div>
        <div class="dashboard-kpi-card">
            <div class="dashboard-kpi-header">
                <span>Active Supplier Contracts</span>
                <span class="badge badge-green">99.2% SLA</span>
            </div>
            <div class="dashboard-kpi-main-val">28 Vendors</div>
            <div class="dashboard-progress-track"><div class="dashboard-progress-fill" style="width: 95%; background: #10b981;"></div></div>
            <div style="font-size: 0.75rem; color: var(--zinc-500);">All Master MSAs Active</div>
        </div>
    </div>

    <!-- AI Deal Health Insights & Copilot -->
    <div class="dashboard-ai-card">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.85rem;">
            <div style="display: flex; align-items: center; gap: 0.65rem;">
                <div style="width: 32px; height: 32px; border-radius: 9px; background: #4f46e5; color: #ffffff; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="sparkles" style="width: 18px; height: 18px;"></i>
                </div>
                <div>
                    <h3 style="font-size: 0.95rem; font-weight: 700; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                        AI Next-Best-Action &amp; Deal Health Insights
                        <span class="badge badge-indigo" style="font-size: 0.65rem;">Copilot Active</span>
                    </h3>
                    <p style="font-size: 0.775rem; color: #64748b; margin: 2px 0 0 0;">
                        Autonomous pipeline intelligence monitoring deals approaching critical stagnation thresholds.
                    </p>
                </div>
            </div>
            <span style="font-size: 0.75rem; color: var(--zinc-400);">2 Insights</span>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 0.85rem;">
            <!-- Recommendation 1 -->
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1rem; display: flex; flex-direction: column; justify-content: space-between; gap: 0.65rem;">
                <div>
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.25rem;">
                        <span style="font-size: 0.825rem; font-weight: 700; color: #0f172a;">Acme Cloud Infrastructure Expansion</span>
                        <span class="badge badge-amber">9 Days Inactive</span>
                    </div>
                    <div style="font-size: 0.75rem; color: #64748b;">
                        Amount: <strong>$340,000</strong> • Stage: <strong>Negotiation</strong>
                    </div>
                    <p style="font-size: 0.75rem; color: #b45309; margin-top: 0.35rem; background: #fffbeb; padding: 0.35rem 0.55rem; border-radius: 6px; border: 1px solid #fef3c7;">
                        ⚠️ Deal velocity slowed by 35% compared to peer deals. Quote viewed 4 times without signature request.
                    </p>
                </div>
                <div style="display: flex; align-items: center; justify-content: space-between; pt: 0.5rem; border-top: 1px solid #f1f5f9;">
                    <span style="font-size: 0.725rem; color: #475569; font-weight: 500;">Action: Send executive check-in email</span>
                    <button type="button" class="btn btn-primary btn-sm" onclick="alert('Generated context email draft for Acme Cloud Infrastructure!');">
                        <i data-lucide="mail" style="width: 12px; height: 12px;"></i>
                        <span>Draft Email</span>
                    </button>
                </div>
            </div>

            <!-- Recommendation 2 -->
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1rem; display: flex; flex-direction: column; justify-content: space-between; gap: 0.65rem;">
                <div>
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.25rem;">
                        <span style="font-size: 0.825rem; font-weight: 700; color: #0f172a;">Horizon Biotech Diagnostic License</span>
                        <span class="badge badge-blue">High Intent</span>
                    </div>
                    <div style="font-size: 0.75rem; color: #64748b;">
                        Amount: <strong>$185,000</strong> • Stage: <strong>Proposal / Quote</strong>
                    </div>
                    <p style="font-size: 0.75rem; color: #1d4ed8; margin-top: 0.35rem; background: #eff6ff; padding: 0.35rem 0.55rem; border-radius: 6px; border: 1px solid #dbeafe;">
                        💡 Stakeholder reviewed CPQ pricing sheet yesterday. Schedule pricing clarification touchpoint.
                    </p>
                </div>
                <div style="display: flex; align-items: center; justify-content: space-between; pt: 0.5rem; border-top: 1px solid #f1f5f9;">
                    <span style="font-size: 0.725rem; color: #475569; font-weight: 500;">Action: Schedule 15-min pricing call</span>
                    <button type="button" class="btn btn-primary btn-sm" onclick="alert('Scheduled 15-min call on calendar!');">
                        <i data-lucide="phone" style="width: 12px; height: 12px;"></i>
                        <span>Call Now</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Funnel & Cockpit Grid -->
    <div class="cockpit-grid">
        <!-- Pipeline Velocity Funnel -->
        <div class="card" style="padding: 1.25rem;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
                <div>
                    <h3 style="font-size: 0.95rem; font-weight: 700; color: var(--zinc-900); margin: 0;">Pipeline Velocity Funnel</h3>
                    <span style="font-size: 0.75rem; color: var(--zinc-500);">Active sales deal flow progression across stages</span>
                </div>
                <button type="button" class="btn btn-secondary btn-sm" onclick="switchTab('opportunities')">
                    Kanban View →
                </button>
            </div>

            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                <!-- Stage 1 -->
                <div>
                    <div style="display: flex; align-items: center; justify-content: space-between; font-size: 0.78rem; font-weight: 600; margin-bottom: 0.25rem;">
                        <span>1. Prospecting (8 deals)</span>
                        <span>$140,000 • Avg 4d</span>
                    </div>
                    <div class="dashboard-progress-track"><div class="dashboard-progress-fill" style="width: 25%; background: #6366f1;"></div></div>
                </div>

                <!-- Stage 2 -->
                <div>
                    <div style="display: flex; align-items: center; justify-content: space-between; font-size: 0.78rem; font-weight: 600; margin-bottom: 0.25rem;">
                        <span>2. Qualified (9 deals)</span>
                        <span>$365,000 • Avg 8d</span>
                    </div>
                    <div class="dashboard-progress-track"><div class="dashboard-progress-fill" style="width: 50%; background: #4f46e5;"></div></div>
                </div>

                <!-- Stage 3 -->
                <div>
                    <div style="display: flex; align-items: center; justify-content: space-between; font-size: 0.78rem; font-weight: 600; margin-bottom: 0.25rem;">
                        <span>3. Proposal / Quote (6 deals)</span>
                        <span>$480,000 • Avg 11d</span>
                    </div>
                    <div class="dashboard-progress-track"><div class="dashboard-progress-fill" style="width: 70%; background: #2563eb;"></div></div>
                </div>

                <!-- Stage 4 -->
                <div>
                    <div style="display: flex; align-items: center; justify-content: space-between; font-size: 0.78rem; font-weight: 600; margin-bottom: 0.25rem;">
                        <span style="display: flex; align-items: center; gap: 0.35rem;">
                            4. Negotiation (4 deals)
                            <span class="badge badge-amber" style="font-size: 0.6rem; padding: 0.1rem 0.35rem;">Rotting</span>
                        </span>
                        <span>$610,000 • Avg 15d</span>
                    </div>
                    <div class="dashboard-progress-track"><div class="dashboard-progress-fill" style="width: 85%; background: #d97706;"></div></div>
                </div>

                <!-- Stage 5 -->
                <div>
                    <div style="display: flex; align-items: center; justify-content: space-between; font-size: 0.78rem; font-weight: 600; margin-bottom: 0.25rem;">
                        <span>5. Closed Won (14 deals)</span>
                        <span style="color: #059669;">$820,000 • Avg 22d</span>
                    </div>
                    <div class="dashboard-progress-track"><div class="dashboard-progress-fill" style="width: 100%; background: #059669;"></div></div>
                </div>
            </div>
        </div>

        <!-- Daily Action Cockpit & Approvals -->
        <div class="card" style="padding: 1.25rem;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
                <div>
                    <h3 style="font-size: 0.95rem; font-weight: 700; color: var(--zinc-900); margin: 0;">Daily Action Cockpit</h3>
                    <span style="font-size: 0.75rem; color: var(--zinc-500);">Priority tasks &amp; approval workflows</span>
                </div>
                <button type="button" class="btn btn-secondary btn-sm" onclick="switchTab('tasks')">
                    All Tasks →
                </button>
            </div>

            <!-- Task Items -->
            <div style="display: flex; flex-direction: column; gap: 0.6rem; margin-bottom: 1rem;">
                <div style="display: flex; align-items: flex-start; gap: 0.6rem; padding: 0.6rem 0.75rem; background: var(--zinc-50); border: 1px solid var(--zinc-200); border-radius: 10px;">
                    <input type="checkbox" style="margin-top: 3px; cursor: pointer;">
                    <div style="flex: 1; min-width: 0;">
                        <div style="font-size: 0.8rem; font-weight: 600; color: var(--zinc-900);">Contract follow-up call with Ram Enterprises</div>
                        <div style="font-size: 0.7rem; color: #e11d48; font-weight: 700;">● Due 2:00 PM (Today - Overdue)</div>
                    </div>
                    <button type="button" class="btn btn-secondary btn-sm" style="padding: 0.2rem 0.5rem;" onclick="alert('Calling Ram Enterprises...');">Call</button>
                </div>

                <div style="display: flex; align-items: flex-start; gap: 0.6rem; padding: 0.6rem 0.75rem; background: var(--zinc-50); border: 1px solid var(--zinc-200); border-radius: 10px;">
                    <input type="checkbox" style="margin-top: 3px; cursor: pointer;">
                    <div style="flex: 1; min-width: 0;">
                        <div style="font-size: 0.8rem; font-weight: 600; color: var(--zinc-900);">Send updated CPQ price quote to Horizon Biotech</div>
                        <div style="font-size: 0.7rem; color: #4f46e5; font-weight: 600;">● Due 4:30 PM (Today)</div>
                    </div>
                    <button type="button" class="btn btn-secondary btn-sm" style="padding: 0.2rem 0.5rem;" onclick="alert('Dispatching quote...');">Quote</button>
                </div>

                <div style="display: flex; align-items: flex-start; gap: 0.6rem; padding: 0.6rem 0.75rem; background: var(--zinc-50); border: 1px solid var(--zinc-200); border-radius: 10px;">
                    <input type="checkbox" style="margin-top: 3px; cursor: pointer;">
                    <div style="flex: 1; min-width: 0;">
                        <div style="font-size: 0.8rem; font-weight: 600; color: var(--zinc-900);">Prepare quarterly business review deck for SolarEnergix</div>
                        <div style="font-size: 0.7rem; color: var(--zinc-500);">● Due Tomorrow Morning</div>
                    </div>
                    <button type="button" class="btn btn-secondary btn-sm" style="padding: 0.2rem 0.5rem;" onclick="alert('Viewing task details...');">View</button>
                </div>
            </div>

            <!-- Approval Queue Box -->
            <div style="border-top: 1px solid var(--zinc-200); padding-top: 0.75rem;">
                <div style="font-size: 0.75rem; font-weight: 700; color: var(--zinc-700); text-transform: uppercase; margin-bottom: 0.4rem;">
                    Pending Approvals (1)
                </div>
                <div style="padding: 0.65rem 0.75rem; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 10px; display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <div style="font-size: 0.8rem; font-weight: 700; color: #1e3a8a;">New Lead Approval: Delta Health</div>
                        <div style="font-size: 0.7rem; color: #3b82f6;">Valued at $125,000 • Submitter: Marcus Brody</div>
                    </div>
                    <div style="display: flex; gap: 4px;">
                        <button type="button" class="btn btn-success btn-sm" style="padding: 0.25rem 0.5rem;" onclick="alert('Lead Approved!'); this.parentElement.innerHTML='<span class=badge badge-green>Approved</span>';">Approve</button>
                        <button type="button" class="btn btn-danger btn-sm" style="padding: 0.25rem 0.5rem;" onclick="alert('Lead Rejected');">Reject</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- 2. LEADS VIEW (#view-leads) -->
<!-- ========================================== -->
<div id="view-leads" class="tab-content-panel">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
        <div>
            <h2 style="font-size: 1.25rem; font-weight: 800; color: var(--zinc-900); letter-spacing: -0.02em; margin: 0;">
                Inbound Leads Pipeline
            </h2>
            <span style="font-size: 0.775rem; color: var(--zinc-500);">3 Leads active • Atomic 3-way conversion to Account, Contact &amp; Deal</span>
        </div>

        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <button type="button" class="btn btn-secondary btn-sm" onclick="alert('Switched to Kanban View (Static)');">
                <i data-lucide="kanban" style="width: 14px; height: 14px;"></i>
                <span>Kanban</span>
            </button>
            <button type="button" class="btn btn-primary btn-sm" onclick="openNewRecordModal('leads')">
                <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
                <span>+ New Lead</span>
            </button>
        </div>
    </div>

    <div class="card-table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Lead Name</th>
                    <th>Company</th>
                    <th>Job Title</th>
                    <th>Propensity Score</th>
                    <th>Stage Status</th>
                    <th>Email Address</th>
                    <th>Phone</th>
                    <th>Source</th>
                    <th>Assigned Owner</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong style="color: var(--zinc-950); cursor: pointer;" onclick="document.getElementById('record-detail-modal').classList.add('is-open');">
                            Alexander Wright
                        </strong>
                    </td>
                    <td>Aurora Financial Tech</td>
                    <td>VP Operations</td>
                    <td>
                        <span class="badge badge-green" style="font-weight: 700;">88 / 100</span>
                    </td>
                    <td><span class="badge badge-green">Qualified</span></td>
                    <td>alex.wright@aurorafin.com</td>
                    <td>+1 (212) 555-9011</td>
                    <td>LinkedIn</td>
                    <td>Alex Morgan</td>
                    <td style="text-align: right;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('record-detail-modal').classList.add('is-open');">
                            Inspect
                        </button>
                        <button type="button" class="btn btn-primary btn-sm" onclick="alert('Lead converted into Account (Aurora Financial) & Contact (Alexander Wright)!');">
                            Convert
                        </button>
                    </td>
                </tr>

                <tr>
                    <td>
                        <strong style="color: var(--zinc-950); cursor: pointer;" onclick="document.getElementById('record-detail-modal').classList.add('is-open');">
                            Samantha Miller
                        </strong>
                    </td>
                    <td>BioGenix Labs</td>
                    <td>Head of IT Infrastructure</td>
                    <td>
                        <span class="badge badge-amber" style="font-weight: 700;">72 / 100</span>
                    </td>
                    <td><span class="badge badge-blue">Contacted</span></td>
                    <td>smiller@biogenixlab.org</td>
                    <td>+1 (206) 555-3344</td>
                    <td>Web Form</td>
                    <td>Jordan Lee</td>
                    <td style="text-align: right;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('record-detail-modal').classList.add('is-open');">
                            Inspect
                        </button>
                        <button type="button" class="btn btn-primary btn-sm" onclick="alert('Lead converted into Account & Contact!');">
                            Convert
                        </button>
                    </td>
                </tr>

                <tr>
                    <td>
                        <strong style="color: var(--zinc-950); cursor: pointer;" onclick="document.getElementById('record-detail-modal').classList.add('is-open');">
                            Derek Stone
                        </strong>
                    </td>
                    <td>SolarEnergix Grid</td>
                    <td>Director of Automation</td>
                    <td>
                        <span class="badge badge-gray" style="font-weight: 700;">45 / 100</span>
                    </td>
                    <td><span class="badge badge-purple">New</span></td>
                    <td>dstone@solarenergix.com</td>
                    <td>+1 (512) 555-8812</td>
                    <td>Referral</td>
                    <td>Unassigned</td>
                    <td style="text-align: right;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('record-detail-modal').classList.add('is-open');">
                            Inspect
                        </button>
                        <button type="button" class="btn btn-primary btn-sm" onclick="alert('Lead converted into Account & Contact!');">
                            Convert
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- ========================================== -->
<!-- 3. ACCOUNTS VIEW (#view-accounts) -->
<!-- ========================================== -->
<div id="view-accounts" class="tab-content-panel">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
        <div>
            <h2 style="font-size: 1.25rem; font-weight: 800; color: var(--zinc-900); letter-spacing: -0.02em; margin: 0;">
                Accounts Master Directory
            </h2>
            <span style="font-size: 0.775rem; color: var(--zinc-500);">4 Master Accounts • 360° Profile Drawer across Deals, Shipments &amp; Invoices</span>
        </div>

        <button type="button" class="btn btn-primary btn-sm" onclick="openNewRecordModal('accounts')">
            <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
            <span>+ New Account</span>
        </button>
    </div>

    <div class="card-table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Account Name</th>
                    <th>Type</th>
                    <th>Industry Sector</th>
                    <th>Annual Revenue</th>
                    <th>Credit Limit</th>
                    <th>Payment Terms</th>
                    <th>Office Phone</th>
                    <th>Billing Address</th>
                    <th>Owner</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong style="color: var(--zinc-950);">Skyline Cloud Systems</strong></td>
                    <td><span class="badge badge-blue">Customer</span></td>
                    <td>Software &amp; Cloud</td>
                    <td><strong>$12,000,000</strong></td>
                    <td>$75,000</td>
                    <td><span class="badge badge-gray">Net 30</span></td>
                    <td>+1 (415) 890-1200</td>
                    <td>100 Market St, San Francisco, CA</td>
                    <td>Alex Morgan</td>
                    <td style="text-align: right;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('record-detail-modal').classList.add('is-open');">
                            360° Drawer
                        </button>
                    </td>
                </tr>

                <tr>
                    <td><strong style="color: var(--zinc-950);">Vanguard Health Tech</strong></td>
                    <td><span class="badge badge-blue">Customer</span></td>
                    <td>Healthcare Tech</td>
                    <td><strong>$24,000,000</strong></td>
                    <td>$120,000</td>
                    <td><span class="badge badge-gray">Net 45</span></td>
                    <td>+1 (617) 555-0199</td>
                    <td>200 Boylston St, Boston, MA</td>
                    <td>Jordan Lee</td>
                    <td style="text-align: right;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('record-detail-modal').classList.add('is-open');">
                            360° Drawer
                        </button>
                    </td>
                </tr>

                <tr>
                    <td><strong style="color: var(--zinc-950);">Quantum Silicon Components</strong></td>
                    <td><span class="badge badge-indigo">Vendor</span></td>
                    <td>Semiconductors</td>
                    <td><strong>$45,000,000</strong></td>
                    <td>$250,000</td>
                    <td><span class="badge badge-gray">Net 60</span></td>
                    <td>+1 (408) 555-4321</td>
                    <td>800 Innovation Way, San Jose, CA</td>
                    <td>Marcus Brody</td>
                    <td style="text-align: right;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('record-detail-modal').classList.add('is-open');">
                            360° Drawer
                        </button>
                    </td>
                </tr>

                <tr>
                    <td><strong style="color: var(--zinc-950);">Titan Steel &amp; Enclosures</strong></td>
                    <td><span class="badge badge-indigo">Vendor</span></td>
                    <td>Industrial Mfg</td>
                    <td><strong>$18,000,000</strong></td>
                    <td>$80,000</td>
                    <td><span class="badge badge-gray">Net 30</span></td>
                    <td>+1 (312) 555-7890</td>
                    <td>450 Industrial Pkwy, Chicago, IL</td>
                    <td>Marcus Brody</td>
                    <td style="text-align: right;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('record-detail-modal').classList.add('is-open');">
                            360° Drawer
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- ========================================== -->
<!-- 4. CONTACTS VIEW (#view-contacts) -->
<!-- ========================================== -->
<div id="view-contacts" class="tab-content-panel">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
        <div>
            <h2 style="font-size: 1.25rem; font-weight: 800; color: var(--zinc-900); letter-spacing: -0.02em; margin: 0;">
                Stakeholder Network
            </h2>
            <span style="font-size: 0.775rem; color: var(--zinc-500);">4 Stakeholders • Decision Makers, Purchasing &amp; Vendor Representatives</span>
        </div>

        <button type="button" class="btn btn-primary btn-sm" onclick="openNewRecordModal('contacts')">
            <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
            <span>+ New Contact</span>
        </button>
    </div>

    <div class="card-table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Contact Name</th>
                    <th>Linked Account</th>
                    <th>Title / Role</th>
                    <th>Department</th>
                    <th>Influence Role</th>
                    <th>Primary</th>
                    <th>Direct Email</th>
                    <th>Direct Phone</th>
                    <th style="text-align: right;">Quick Reach</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong style="color: var(--zinc-950);">Sarah Jenkins</strong></td>
                    <td>Skyline Cloud Systems</td>
                    <td>VP of Engineering</td>
                    <td>Engineering</td>
                    <td><span class="badge badge-green">Decision Maker</span></td>
                    <td><span style="color: #f59e0b;">⭐ Primary</span></td>
                    <td>sjenkins@skylinecloud.io</td>
                    <td>+1 (415) 890-1201</td>
                    <td style="text-align: right;">
                        <a href="mailto:sjenkins@skylinecloud.io" class="btn btn-secondary btn-sm">Email</a>
                    </td>
                </tr>

                <tr>
                    <td><strong style="color: var(--zinc-950);">Michael Chang</strong></td>
                    <td>Skyline Cloud Systems</td>
                    <td>Head of Procurement</td>
                    <td>Finance</td>
                    <td><span class="badge badge-blue">Purchasing</span></td>
                    <td><span style="color: var(--zinc-400);">—</span></td>
                    <td>mchang@skylinecloud.io</td>
                    <td>+1 (415) 890-1202</td>
                    <td style="text-align: right;">
                        <a href="mailto:mchang@skylinecloud.io" class="btn btn-secondary btn-sm">Email</a>
                    </td>
                </tr>

                <tr>
                    <td><strong style="color: var(--zinc-950);">Elena Rostova</strong></td>
                    <td>Vanguard Health Tech</td>
                    <td>Chief Technology Officer</td>
                    <td>C-Suite</td>
                    <td><span class="badge badge-green">Decision Maker</span></td>
                    <td><span style="color: #f59e0b;">⭐ Primary</span></td>
                    <td>erostova@vanguardhealth.com</td>
                    <td>+1 (617) 555-0198</td>
                    <td style="text-align: right;">
                        <a href="mailto:erostova@vanguardhealth.com" class="btn btn-secondary btn-sm">Email</a>
                    </td>
                </tr>

                <tr>
                    <td><strong style="color: var(--zinc-950);">David Kowalski</strong></td>
                    <td>Quantum Silicon Components</td>
                    <td>Director of Key Accounts</td>
                    <td>Sales</td>
                    <td><span class="badge badge-indigo">Vendor Rep</span></td>
                    <td><span style="color: #f59e0b;">⭐ Primary</span></td>
                    <td>dkowalski@quantumsilicon.com</td>
                    <td>+1 (408) 555-4322</td>
                    <td style="text-align: right;">
                        <a href="mailto:dkowalski@quantumsilicon.com" class="btn btn-secondary btn-sm">Email</a>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- ========================================== -->
<!-- 5. OPPORTUNITIES VIEW (#view-opportunities) -->
<!-- ========================================== -->
<div id="view-opportunities" class="tab-content-panel">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
        <div>
            <h2 style="font-size: 1.25rem; font-weight: 800; color: var(--zinc-900); letter-spacing: -0.02em; margin: 0;">
                Sales Pipeline &amp; Opportunities
            </h2>
            <span style="font-size: 0.775rem; color: var(--zinc-500);">3 Sell-Side Opportunities ($433,000) • Coupled to Procurement Purchase Deals</span>
        </div>

        <button type="button" class="btn btn-primary btn-sm" onclick="openNewRecordModal('opportunities')">
            <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
            <span>+ New Opportunity</span>
        </button>
    </div>

    <div class="card-table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Opportunity Name</th>
                    <th>Client Account</th>
                    <th>Coupled Procurement PO</th>
                    <th>Deal Value</th>
                    <th>Pipeline Stage</th>
                    <th>Win Probability</th>
                    <th>Expected Closing</th>
                    <th>Assigned Rep</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong style="color: var(--zinc-950); cursor: pointer;" onclick="document.getElementById('record-detail-modal').classList.add('is-open');">
                            Skyline Cloud — Phase 2 Compute Cluster
                        </strong>
                    </td>
                    <td>Skyline Cloud Systems</td>
                    <td><span class="badge badge-indigo">PO-882194</span></td>
                    <td><strong>$145,000</strong></td>
                    <td><span class="badge badge-amber">Negotiation</span></td>
                    <td><strong style="color: #059669;">75%</strong></td>
                    <td>In 15 Days</td>
                    <td>Alex Morgan</td>
                    <td style="text-align: right;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('record-detail-modal').classList.add('is-open');">
                            Details
                        </button>
                    </td>
                </tr>

                <tr>
                    <td>
                        <strong style="color: var(--zinc-950); cursor: pointer;" onclick="document.getElementById('record-detail-modal').classList.add('is-open');">
                            Vanguard Health — Diagnostic Gateway Platform
                        </strong>
                    </td>
                    <td>Vanguard Health Tech</td>
                    <td><span class="badge badge-indigo">PO-771205</span></td>
                    <td><strong>$220,000</strong></td>
                    <td><span class="badge badge-blue">Proposal</span></td>
                    <td><strong style="color: #2563eb;">40%</strong></td>
                    <td>In 30 Days</td>
                    <td>Jordan Lee</td>
                    <td style="text-align: right;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('record-detail-modal').classList.add('is-open');">
                            Details
                        </button>
                    </td>
                </tr>

                <tr>
                    <td>
                        <strong style="color: var(--zinc-950); cursor: pointer;" onclick="document.getElementById('record-detail-modal').classList.add('is-open');">
                            Skyline Cloud — Edge Node Expansion
                        </strong>
                    </td>
                    <td>Skyline Cloud Systems</td>
                    <td><span class="badge badge-indigo">PO-882194</span></td>
                    <td><strong>$68,000</strong></td>
                    <td><span class="badge badge-green">Closed Won</span></td>
                    <td><strong style="color: #059669;">100%</strong></td>
                    <td>Won (Oct 08)</td>
                    <td>Alex Morgan</td>
                    <td style="text-align: right;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('record-detail-modal').classList.add('is-open');">
                            Details
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- ========================================== -->
<!-- 6. TASKS VIEW (#view-tasks) -->
<!-- ========================================== -->
<div id="view-tasks" class="tab-content-panel">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
        <div>
            <h2 style="font-size: 1.25rem; font-weight: 800; color: var(--zinc-900); letter-spacing: -0.02em; margin: 0;">
                Tasks Governance &amp; Lightning Activities
            </h2>
            <span style="font-size: 0.775rem; color: var(--zinc-500);">4 Tasks recorded • Filter by status, recurring schedule, or overdue alerts</span>
        </div>

        <button type="button" class="btn btn-primary btn-sm" onclick="openNewRecordModal('tasks')">
            <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
            <span>+ Add New Task</span>
        </button>
    </div>

    <!-- Quick Segmented Filter Tabs -->
    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem; border-bottom: 1px solid var(--zinc-200); padding-bottom: 0.5rem;">
        <button type="button" class="btn btn-sm btn-secondary" style="background: var(--zinc-900); color: #ffffff;">All Tasks (4)</button>
        <button type="button" class="btn btn-sm btn-secondary">Open (3)</button>
        <button type="button" class="btn btn-sm btn-secondary">Pending (1)</button>
        <button type="button" class="btn btn-sm btn-secondary">Recurring (1)</button>
        <button type="button" class="btn btn-sm btn-secondary">Today's (2)</button>
        <button type="button" class="btn btn-sm btn-danger">⚠️ Overdue (2)</button>
    </div>

    <div class="card-table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 40px;">Status</th>
                    <th>Subject</th>
                    <th>Related Record</th>
                    <th>Priority</th>
                    <th>Due Date &amp; Overdue Alert</th>
                    <th>Assigned To</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><input type="checkbox" style="cursor: pointer;"></td>
                    <td><strong>Contract follow-up call with Ram Enterprises</strong></td>
                    <td><span class="badge badge-gray">Deal: Ram Hardware Expansion</span></td>
                    <td><span class="badge badge-red">High</span></td>
                    <td><strong style="color: #e11d48;">⚠️ Today 2:00 PM (Overdue)</strong></td>
                    <td>Alex Morgan</td>
                    <td style="text-align: right;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="alert('Task completed!');">Done</button>
                    </td>
                </tr>

                <tr>
                    <td><input type="checkbox" style="cursor: pointer;"></td>
                    <td><strong>Send updated CPQ price quote to Horizon Biotech</strong></td>
                    <td><span class="badge badge-gray">Contact: Dr. Sarah Vance</span></td>
                    <td><span class="badge badge-blue">Normal</span></td>
                    <td><strong style="color: #4f46e5;">Today 4:30 PM</strong></td>
                    <td>Jordan Lee</td>
                    <td style="text-align: right;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="alert('Task completed!');">Done</button>
                    </td>
                </tr>

                <tr>
                    <td><input type="checkbox" style="cursor: pointer;"></td>
                    <td><strong>Prepare quarterly business review deck for SolarEnergix Grid</strong></td>
                    <td><span class="badge badge-gray">Account: SolarEnergix Grid</span></td>
                    <td><span class="badge badge-blue">Normal</span></td>
                    <td>Tomorrow 10:00 AM</td>
                    <td>Marcus Brody</td>
                    <td style="text-align: right;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="alert('Task completed!');">Done</button>
                    </td>
                </tr>

                <tr>
                    <td><input type="checkbox" style="cursor: pointer;"></td>
                    <td><strong>Reconcile unallocated bank wire UTR7710294822</strong></td>
                    <td><span class="badge badge-gray">Wire: Apex Holdings</span></td>
                    <td><span class="badge badge-red">High</span></td>
                    <td><strong style="color: #e11d48;">⚠️ Yesterday (Overdue)</strong></td>
                    <td>Sarah Chen</td>
                    <td style="text-align: right;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="alert('Task completed!');">Done</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- ========================================== -->
<!-- 7. PURCHASE DEALS VIEW (#view-purchase_deals) -->
<!-- ========================================== -->
<div id="view-purchase_deals" class="tab-content-panel">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
        <div>
            <h2 style="font-size: 1.25rem; font-weight: 800; color: var(--zinc-900); letter-spacing: -0.02em; margin: 0;">
                Buy-Side Procurement &amp; Purchase Deals
            </h2>
            <span style="font-size: 0.775rem; color: var(--zinc-500);">2 Purchase Orders ($72,500) • Supplier agreements &amp; inventory backing</span>
        </div>

        <button type="button" class="btn btn-primary btn-sm" onclick="openNewRecordModal('purchase_deals')">
            <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
            <span>+ Create Purchase Order</span>
        </button>
    </div>

    <div class="card-table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>PO Identifier</th>
                    <th>Order Description</th>
                    <th>Vendor Account</th>
                    <th>Committed Spend</th>
                    <th>Milestone Stage</th>
                    <th>Expected Delivery</th>
                    <th>Agreed Terms</th>
                    <th>Assigned Buyer</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong style="color: var(--color-primary);">PO-882194</strong></td>
                    <td>Batch 48 FPGA Accelerators (120 units)</td>
                    <td>Quantum Silicon Components</td>
                    <td><strong>$54,000 USD</strong></td>
                    <td><span class="badge badge-blue">PO Issued</span></td>
                    <td>In 7 Days</td>
                    <td><span class="badge badge-gray">Net 60</span></td>
                    <td>Marcus Brody</td>
                    <td style="text-align: right;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="alert('PO-882194 details viewed!');">Inspect</button>
                    </td>
                </tr>

                <tr>
                    <td><strong style="color: var(--color-primary);">PO-771205</strong></td>
                    <td>Heavy-Duty 42U Server Racks (15 units)</td>
                    <td>Titan Steel &amp; Enclosures</td>
                    <td><strong>$18,500 USD</strong></td>
                    <td><span class="badge badge-amber">Quote Received</span></td>
                    <td>In 20 Days</td>
                    <td><span class="badge badge-gray">Net 30</span></td>
                    <td>Marcus Brody</td>
                    <td style="text-align: right;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="alert('PO-771205 details viewed!');">Inspect</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- ========================================== -->
<!-- 8. SHIPPING & FREIGHT VIEW (#view-shipping) -->
<!-- ========================================== -->
<div id="view-shipping" class="tab-content-panel">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
        <div>
            <h2 style="font-size: 1.25rem; font-weight: 800; color: var(--zinc-900); letter-spacing: -0.02em; margin: 0;">
                Logistics &amp; Freight Manifests
            </h2>
            <span style="font-size: 0.775rem; color: var(--zinc-500);">2 Shipments active • Inbound vendor deliveries &amp; Outbound customer dispatches</span>
        </div>

        <button type="button" class="btn btn-primary btn-sm" onclick="openNewRecordModal('shipping')">
            <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
            <span>+ New Shipment</span>
        </button>
    </div>

    <div class="card-table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Tracking Code</th>
                    <th>Logistics Type</th>
                    <th>Freight Carrier</th>
                    <th>Milestone Progression</th>
                    <th>Origin Facility</th>
                    <th>Destination Delivery Address</th>
                    <th>ETA</th>
                    <th style="text-align: right;">Live Tracking</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code style="background: var(--zinc-100); padding: 0.2rem 0.4rem; border-radius: 4px; font-weight: 700;">TRK-98421094</code></td>
                    <td><span class="badge badge-blue">Outbound</span></td>
                    <td>FedEx Express</td>
                    <td><span class="badge badge-amber">● In Transit</span></td>
                    <td>Central Hub, Fremont CA</td>
                    <td>100 Market St, San Francisco CA</td>
                    <td>2 Days</td>
                    <td style="text-align: right;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="alert('Tracking status: Out for final delivery tomorrow!');">Live Map</button>
                    </td>
                </tr>

                <tr>
                    <td><code style="background: var(--zinc-100); padding: 0.2rem 0.4rem; border-radius: 4px; font-weight: 700;">TRK-33910245</code></td>
                    <td><span class="badge badge-purple">Inbound</span></td>
                    <td>UPS Freight</td>
                    <td><span class="badge badge-indigo">● Dispatched</span></td>
                    <td>Silicon Valley Fab 3, San Jose CA</td>
                    <td>Acme HQ Assembly Plant, Austin TX</td>
                    <td>5 Days</td>
                    <td style="text-align: right;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="alert('Tracking status: In transit on highway corridor!');">Live Map</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- ========================================== -->
<!-- 9. PAYMENTS & INVOICES VIEW (#view-payments) -->
<!-- ========================================== -->
<div id="view-payments" class="tab-content-panel">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
        <div>
            <h2 style="font-size: 1.25rem; font-weight: 800; color: var(--zinc-900); letter-spacing: -0.02em; margin: 0;">
                AR/AP Financial Ledger &amp; Invoices
            </h2>
            <span style="font-size: 0.775rem; color: var(--zinc-500);">3 Invoices • Accounts Receivable ($113,000) &amp; Accounts Payable ($54,000)</span>
        </div>

        <button type="button" class="btn btn-primary btn-sm" onclick="openNewRecordModal('payments')">
            <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
            <span>+ Create Invoice</span>
        </button>
    </div>

    <div class="card-table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Invoice Reference</th>
                    <th>Type</th>
                    <th>Client / Supplier Account</th>
                    <th>Total Billed</th>
                    <th>Paid Amount</th>
                    <th>Balance Due</th>
                    <th>Aging Status</th>
                    <th>Due Date</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong style="color: var(--zinc-950);">INV-2026-0041</strong></td>
                    <td><span class="badge badge-blue">AR (Client)</span></td>
                    <td>Skyline Cloud Systems</td>
                    <td>$68,000</td>
                    <td><span style="color: #059669; font-weight: 600;">$30,000</span></td>
                    <td><strong style="color: #b45309;">$38,000</strong></td>
                    <td><span class="badge badge-amber">Partially Paid</span></td>
                    <td>In 10 Days</td>
                    <td style="text-align: right;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="alert('Record settlement modal launched!');">Settle</button>
                    </td>
                </tr>

                <tr>
                    <td><strong style="color: var(--zinc-950);">INV-2026-0038</strong></td>
                    <td><span class="badge badge-blue">AR (Client)</span></td>
                    <td>Vanguard Health Tech</td>
                    <td>$45,000</td>
                    <td>$0</td>
                    <td><strong>$45,000</strong></td>
                    <td><span class="badge badge-gray">Sent</span></td>
                    <td>In 15 Days</td>
                    <td style="text-align: right;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="alert('Record settlement modal launched!');">Settle</button>
                    </td>
                </tr>

                <tr>
                    <td><strong style="color: var(--zinc-950);">INV-VEN-9102</strong></td>
                    <td><span class="badge badge-purple">AP (Supplier)</span></td>
                    <td>Quantum Silicon Components</td>
                    <td>$54,000</td>
                    <td>$0</td>
                    <td><strong>$54,000</strong></td>
                    <td><span class="badge badge-gray">Sent</span></td>
                    <td>In 45 Days</td>
                    <td style="text-align: right;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="alert('Record settlement modal launched!');">Settle</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- ========================================== -->
<!-- 10. WIRE RECONCILE VIEW (#view-wires) -->
<!-- ========================================== -->
<div id="view-wires" class="tab-content-panel">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
        <div>
            <h2 style="font-size: 1.25rem; font-weight: 800; color: var(--zinc-900); letter-spacing: -0.02em; margin: 0;">
                Banking Wire Intake &amp; Reconciliation Workbench
            </h2>
            <span style="font-size: 0.775rem; color: var(--zinc-500);">3 Bank Wires ($83,000) • Interactive UTR reference matching directly to invoices</span>
        </div>

        <button type="button" class="btn btn-primary btn-sm" onclick="openNewRecordModal('wires')">
            <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
            <span>+ New Wire Intake</span>
        </button>
    </div>

    <div class="card-table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Bank UTR Reference</th>
                    <th>Remitter Entity</th>
                    <th>Sending Financial Institution</th>
                    <th>Receiving Deposit Account</th>
                    <th>Amount ($ USD)</th>
                    <th>Date Received</th>
                    <th>Reconciliation State</th>
                    <th>Linked Record</th>
                    <th style="text-align: right;">Reconcile Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code style="font-weight: 700; color: #059669;">UTR9982410294</code></td>
                    <td>Skyline Cloud Systems LLC</td>
                    <td>JPMorgan Chase NY</td>
                    <td>CITI-US-MAIN-9482</td>
                    <td><strong>$30,000</strong></td>
                    <td>4 Days Ago</td>
                    <td><span class="badge badge-green">✓ Matched/Reconciled</span></td>
                    <td>INV-2026-0041</td>
                    <td style="text-align: right;">
                        <span class="badge badge-green">Reconciled</span>
                    </td>
                </tr>

                <tr>
                    <td><code style="font-weight: 700; color: var(--zinc-900);">UTR4491029381</code></td>
                    <td>Skyline Cloud Systems LLC</td>
                    <td>JPMorgan Chase NY</td>
                    <td>CITI-US-MAIN-9482</td>
                    <td><strong>$38,000</strong></td>
                    <td>Yesterday</td>
                    <td><span class="badge badge-amber">● Pending Verification</span></td>
                    <td><span style="color: var(--zinc-400);">Unlinked</span></td>
                    <td style="text-align: right;">
                        <button type="button" class="btn btn-primary btn-sm" onclick="alert('Wire matched to INV-2026-0041 remaining balance!');">Match Invoice</button>
                    </td>
                </tr>

                <tr>
                    <td><code style="font-weight: 700; color: var(--zinc-900);">UTR7710294822</code></td>
                    <td>Apex Holdings Group</td>
                    <td>Bank of America NC</td>
                    <td>CITI-US-MAIN-9482</td>
                    <td><strong>$15,000</strong></td>
                    <td>Today</td>
                    <td><span class="badge badge-amber">● Pending Verification</span></td>
                    <td><span style="color: var(--zinc-400);">Unallocated</span></td>
                    <td style="text-align: right;">
                        <button type="button" class="btn btn-primary btn-sm" onclick="alert('Matching wire to open customer accounts...');">Match Invoice</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- ========================================== -->
<!-- 11. SETTINGS & SCHEMA / ADMIN CONSOLE (#view-settings) -->
<!-- ========================================== -->
<div id="view-settings" class="tab-content-panel">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
        <div>
            <h2 style="font-size: 1.25rem; font-weight: 800; color: var(--zinc-900); letter-spacing: -0.02em; margin: 0;">
                Enterprise Settings &amp; Schema Governance
            </h2>
            <span style="font-size: 0.775rem; color: var(--zinc-500);">RBAC, Profiles, Field-Level Security, ProcessStudio &amp; Page Layouts</span>
        </div>
        <span class="badge badge-indigo">Admin Console Mode</span>
    </div>

    <!-- Admin Subtabs -->
    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem; border-bottom: 1px solid var(--zinc-200); padding-bottom: 0.5rem; flex-wrap: wrap;">
        <button type="button" class="btn btn-sm btn-secondary" style="background: var(--zinc-900); color: #ffffff;">Users Directory (4)</button>
        <button type="button" class="btn btn-sm btn-secondary">Roles &amp; Profiles</button>
        <button type="button" class="btn btn-sm btn-secondary">Record Rights (CRUD)</button>
        <button type="button" class="btn btn-sm btn-secondary">Field-Level Security</button>
        <button type="button" class="btn btn-sm btn-secondary">ProcessStudio Canvas</button>
        <button type="button" class="btn btn-sm btn-secondary">Audit Trail (CSV)</button>
    </div>

    <div class="card-table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>User Identity</th>
                    <th>Email Address</th>
                    <th>Organizational Role</th>
                    <th>Security Profile</th>
                    <th>Status</th>
                    <th style="text-align: right;">Delegated Impersonation</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Alex Morgan</strong></td>
                    <td>alex.morgan@acmecorp.com</td>
                    <td>Executive / CEO</td>
                    <td><span class="badge badge-indigo">prof-admin (Administrator)</span></td>
                    <td><span class="badge badge-green">Active</span></td>
                    <td style="text-align: right;">
                        <span style="font-size: 0.75rem; color: var(--zinc-400);">Current User</span>
                    </td>
                </tr>

                <tr>
                    <td><strong>Jordan Lee</strong></td>
                    <td>jordan.lee@acmecorp.com</td>
                    <td>Sales Executive</td>
                    <td><span class="badge badge-blue">prof-sales (Sales Rep)</span></td>
                    <td><span class="badge badge-green">Active</span></td>
                    <td style="text-align: right;">
                        <button type="button" class="btn btn-secondary btn-sm" style="background: #cffafe; color: #0e7490; border-color: #a5f3fc; font-weight: 600;" onclick="alert('Impersonating Jordan Lee (Sales Rep Context)!');">
                            Login as User
                        </button>
                    </td>
                </tr>

                <tr>
                    <td><strong>Marcus Brody</strong></td>
                    <td>marcus.brody@acmecorp.com</td>
                    <td>Procurement Lead</td>
                    <td><span class="badge badge-purple">prof-procurement</span></td>
                    <td><span class="badge badge-green">Active</span></td>
                    <td style="text-align: right;">
                        <button type="button" class="btn btn-secondary btn-sm" style="background: #cffafe; color: #0e7490; border-color: #a5f3fc; font-weight: 600;" onclick="alert('Impersonating Marcus Brody (Procurement Context)!');">
                            Login as User
                        </button>
                    </td>
                </tr>

                <tr>
                    <td><strong>Sarah Chen</strong></td>
                    <td>sarah.finance@acmecorp.com</td>
                    <td>Finance &amp; Billing Lead</td>
                    <td><span class="badge badge-indigo">prof-admin</span></td>
                    <td><span class="badge badge-green">Active</span></td>
                    <td style="text-align: right;">
                        <button type="button" class="btn btn-secondary btn-sm" style="background: #cffafe; color: #0e7490; border-color: #a5f3fc; font-weight: 600;" onclick="alert('Impersonating Sarah Chen (Finance Context)!');">
                            Login as User
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

@endsection
