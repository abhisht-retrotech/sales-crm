@extends('layouts.app')

@section('title', 'Dashboard — DealSync Enterprise CRM')

@section('content')
<div class="dashboard-root" style="max-width: 1280px; margin: 0 auto; display: flex; flex-direction: column; gap: 1.25rem; padding-bottom: 2.5rem;">
    <!-- 1. HERO COMMAND HEADER -->
    <div class="dashboard-hero-card">
        <div>
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.35rem;">
                <span class="dashboard-telemetry-badge">
                    <span class="dashboard-pulse-dot"></span>
                    Live Telemetry Active
                </span>
                <span style="font-size: 0.75rem; color: #64748b;">Updated 2m ago</span>
            </div>
            <h1 class="dashboard-hero-title">
                Good morning, Marcus! 👋
            </h1>
            <p class="dashboard-hero-subtitle">
                You have <strong style="color: #0f172a; font-weight: 700;">3 tasks due today</strong>, <strong style="color: #4f46e5; font-weight: 700;">2 deals closing this week ($185k)</strong>, and <strong style="color: #d97706; font-weight: 700;">1 pending approval</strong> waiting on your review.
            </p>
        </div>

        <!-- Quick Action Buttons -->
        <div class="dashboard-actions-cluster">
            <button type="button" class="dashboard-btn dashboard-btn-indigo" onclick="openNewRecordModal('leads')">
                <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
                <span>New Lead</span>
            </button>
            <button type="button" class="dashboard-btn dashboard-btn-slate" onclick="openNewRecordModal('opportunities')">
                <i data-lucide="trending-up" style="width: 14px; height: 14px;"></i>
                <span>New Deal</span>
            </button>
            <button type="button" class="dashboard-btn dashboard-btn-outline" onclick="showToast('Sales Call Logger initiated!')">
                <i data-lucide="phone" style="width: 14px; height: 14px;"></i>
                <span>Log Call</span>
            </button>
        </div>
    </div>

    <!-- 2. INTERACTIVE PERSONA & FILTER BAR -->
    <div class="dashboard-filter-bar">
        <div style="display: flex; align-items: center; gap: 0.65rem; flex-wrap: wrap;">
            <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: #64748b;">
                Workspace Role:
            </span>
            <div class="dashboard-role-tabs">
                <button type="button" class="dashboard-role-tab active" data-persona="exec" onclick="switchPersona('exec')">
                    Sales Executive
                </button>
                <button type="button" class="dashboard-role-tab" data-persona="rep" onclick="switchPersona('rep')">
                    Sales Representative
                </button>
                <button type="button" class="dashboard-role-tab" data-persona="procure" onclick="switchPersona('procure')">
                    Procurement Specialist
                </button>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
            <select class="dashboard-select" id="dash-date-range" onchange="showToast('Date range filter applied: ' + this.options[this.selectedIndex].text)">
                <option value="this_month">This Month (October 2026)</option>
                <option value="this_quarter">This Quarter (Q4 2026)</option>
                <option value="this_year">Year to Date (2026)</option>
                <option value="all_time">All Time</option>
            </select>
            <span style="font-size: 0.75rem; color: #cbd5e1;">|</span>
            <select class="dashboard-select" id="dash-scope-mode" onchange="showToast('Scope filter applied: ' + this.options[this.selectedIndex].text)">
                <option value="team">Team: Entire Organization</option>
                <option value="member">My Direct Reports</option>
                <option value="self">My Individual Numbers</option>
            </select>
        </div>
    </div>

    <!-- 3. HIGH-IMPACT 4-COLUMN KPI ROW (EXECUTIVE - DEFAULT) -->
    <div class="kpi-persona-group dashboard-kpi-grid" data-persona="exec">
        <!-- KPI 1: Monthly Quota Progress -->
        <div class="dashboard-kpi-card">
            <div class="dashboard-kpi-header">
                <span>Quota Attainment (Oct)</span>
                <span class="dashboard-badge-pill dashboard-badge-emerald">↑ 84.6%</span>
            </div>
            <div class="dashboard-kpi-value-row">
                <div class="dashboard-kpi-main-val">$423,000</div>
                <span class="dashboard-kpi-sub-val">Goal: $500,000</span>
            </div>
            <div class="dashboard-progress-track">
                <div class="dashboard-progress-fill" style="width: 84.6%; background: #10b981;"></div>
            </div>
            <div class="dashboard-kpi-footer">
                <span>$77,000 to target</span>
                <a href="{{ url('/opportunities') }}" class="dashboard-link-action" style="text-decoration: none;">
                    View Quotas <i data-lucide="arrow-up-right" style="width: 12px; height: 12px;"></i>
                </a>
            </div>
        </div>

        <!-- KPI 2: Weighted Pipeline -->
        <div class="dashboard-kpi-card">
            <div class="dashboard-kpi-header">
                <span>Weighted Pipeline</span>
                <span class="dashboard-badge-pill dashboard-badge-indigo">28 Deals Active</span>
            </div>
            <div class="dashboard-kpi-value-row">
                <div class="dashboard-kpi-main-val">$1,420,500</div>
                <span class="dashboard-kpi-sub-val">Gross: $3.1M</span>
            </div>
            <div style="height: 24px; display: flex; align-items: flex-end; gap: 4px;">
                <div style="flex: 1; background: #c7d2fe; height: 8px; border-radius: 2px 2px 0 0;"></div>
                <div style="flex: 1; background: #a5b4fc; height: 12px; border-radius: 2px 2px 0 0;"></div>
                <div style="flex: 1; background: #818cf8; height: 16px; border-radius: 2px 2px 0 0;"></div>
                <div style="flex: 1; background: #6366f1; height: 20px; border-radius: 2px 2px 0 0;"></div>
                <div style="flex: 1; background: #4f46e5; height: 24px; border-radius: 2px 2px 0 0;"></div>
            </div>
            <div class="dashboard-kpi-footer">
                <span>Win probability blended: 45.8%</span>
                <a href="{{ url('/opportunities') }}" class="dashboard-link-action" style="text-decoration: none;">
                    Drilldown <i data-lucide="arrow-up-right" style="width: 12px; height: 12px;"></i>
                </a>
            </div>
        </div>

        <!-- KPI 3: Customer Net Retention -->
        <div class="dashboard-kpi-card">
            <div class="dashboard-kpi-header">
                <span>Customer Net Retention</span>
                <span class="dashboard-badge-pill dashboard-badge-emerald">↑ 18.2%</span>
            </div>
            <div class="dashboard-kpi-value-row">
                <div class="dashboard-kpi-main-val">129.4%</div>
                <span class="dashboard-kpi-sub-val">Target: 120%</span>
            </div>
            <div style="height: 24px; display: flex; align-items: center;">
                <svg width="100%" height="20" viewBox="0 0 100 20" preserveAspectRatio="none" style="overflow: visible;">
                    <path fill="none" stroke="#10b981" stroke-width="2.5" d="M0,15 Q25,14 50,9 T100,2" />
                    <path fill="rgba(16, 185, 129, 0.15)" d="M0,15 Q25,14 50,9 T100,2 L100,20 L0,20 Z" />
                </svg>
            </div>
            <div class="dashboard-kpi-footer">
                <span>+129.4% NRR Expansion</span>
                <a href="{{ url('/accounts') }}" class="dashboard-link-action" style="text-decoration: none;">
                    Drilldown <i data-lucide="arrow-up-right" style="width: 12px; height: 12px;"></i>
                </a>
            </div>
        </div>

        <!-- KPI 4: Active Accounts Portfolio -->
        <div class="dashboard-kpi-card">
            <div class="dashboard-kpi-header">
                <span>Active Accounts Portfolio</span>
                <span class="dashboard-badge-pill dashboard-badge-blue">↑ 24.8% YoY</span>
            </div>
            <div class="dashboard-kpi-value-row">
                <div class="dashboard-kpi-main-val">$48.6M</div>
                <span class="dashboard-kpi-sub-val">Goal: $45.0M</span>
            </div>
            <div style="height: 24px; display: flex; align-items: center;">
                <svg width="100%" height="20" viewBox="0 0 100 20" preserveAspectRatio="none" style="overflow: visible;">
                    <path fill="none" stroke="#2563eb" stroke-width="2.5" d="M0,17 Q30,12 60,8 T100,3" />
                    <path fill="rgba(37, 99, 235, 0.15)" d="M0,17 Q30,12 60,8 T100,3 L100,20 L0,20 Z" />
                </svg>
            </div>
            <div class="dashboard-kpi-footer">
                <span>94 Managed Key Clients</span>
                <a href="{{ url('/accounts') }}" class="dashboard-link-action" style="text-decoration: none;">
                    Drilldown <i data-lucide="arrow-up-right" style="width: 12px; height: 12px;"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- 3B. HIGH-IMPACT KPI ROW (SALES REP - ON TOGGLE) -->
    <div class="kpi-persona-group dashboard-kpi-grid" data-persona="rep" style="display: none;">
        <div class="dashboard-kpi-card">
            <div class="dashboard-kpi-header">
                <span>My Personal Quota</span>
                <span class="dashboard-badge-pill dashboard-badge-emerald">↑ 84.0%</span>
            </div>
            <div class="dashboard-kpi-value-row">
                <div class="dashboard-kpi-main-val">$92,400</div>
                <span class="dashboard-kpi-sub-val">Goal: $110,000</span>
            </div>
            <div class="dashboard-progress-track">
                <div class="dashboard-progress-fill" style="width: 84%; background: #10b981;"></div>
            </div>
            <div class="dashboard-kpi-footer">
                <span>$17,600 to target</span>
                <span class="dashboard-link-action">Drilldown <i data-lucide="arrow-up-right" style="width: 12px; height: 12px;"></i></span>
            </div>
        </div>

        <div class="dashboard-kpi-card">
            <div class="dashboard-kpi-header">
                <span>My Active Deals</span>
                <span class="dashboard-badge-pill dashboard-badge-indigo">11 Deals</span>
            </div>
            <div class="dashboard-kpi-value-row">
                <div class="dashboard-kpi-main-val">$380,000</div>
                <span class="dashboard-kpi-sub-val">Pipeline</span>
            </div>
            <div style="height: 24px; display: flex; align-items: flex-end; gap: 4px;">
                <div style="flex: 1; background: #c7d2fe; height: 8px; border-radius: 2px 2px 0 0;"></div>
                <div style="flex: 1; background: #a5b4fc; height: 12px; border-radius: 2px 2px 0 0;"></div>
                <div style="flex: 1; background: #818cf8; height: 18px; border-radius: 2px 2px 0 0;"></div>
                <div style="flex: 1; background: #6366f1; height: 24px; border-radius: 2px 2px 0 0;"></div>
            </div>
            <div class="dashboard-kpi-footer">
                <span>Avg Deal: $34,500</span>
                <span class="dashboard-link-action">Drilldown <i data-lucide="arrow-up-right" style="width: 12px; height: 12px;"></i></span>
            </div>
        </div>

        <div class="dashboard-kpi-card">
            <div class="dashboard-kpi-header">
                <span>Calls Logged This Week</span>
                <span class="dashboard-badge-pill dashboard-badge-emerald">↑ +14 vs LW</span>
            </div>
            <div class="dashboard-kpi-value-row">
                <div class="dashboard-kpi-main-val">42 Calls</div>
                <span class="dashboard-kpi-sub-val">Goal: 50 Calls</span>
            </div>
            <div style="height: 24px; display: flex; align-items: center;">
                <svg width="100%" height="20" viewBox="0 0 100 20" preserveAspectRatio="none" style="overflow: visible;">
                    <path fill="none" stroke="#10b981" stroke-width="2.5" d="M0,16 Q25,12 50,7 T100,2" />
                    <path fill="rgba(16, 185, 129, 0.15)" d="M0,16 Q25,12 50,7 T100,2 L100,20 L0,20 Z" />
                </svg>
            </div>
            <div class="dashboard-kpi-footer">
                <span>8 calls to weekly target</span>
                <span class="dashboard-link-action">Drilldown <i data-lucide="arrow-up-right" style="width: 12px; height: 12px;"></i></span>
            </div>
        </div>

        <div class="dashboard-kpi-card">
            <div class="dashboard-kpi-header">
                <span>Win Conversion Rate</span>
                <span class="dashboard-badge-pill dashboard-badge-blue">↑ Top 10%</span>
            </div>
            <div class="dashboard-kpi-value-row">
                <div class="dashboard-kpi-main-val">38.2%</div>
                <span class="dashboard-kpi-sub-val">Team: 31.4%</span>
            </div>
            <div style="height: 24px; display: flex; align-items: center;">
                <svg width="100%" height="20" viewBox="0 0 100 20" preserveAspectRatio="none" style="overflow: visible;">
                    <path fill="none" stroke="#2563eb" stroke-width="2.5" d="M0,15 Q30,11 60,7 T100,3" />
                    <path fill="rgba(37, 99, 235, 0.15)" d="M0,15 Q30,11 60,7 T100,3 L100,20 L0,20 Z" />
                </svg>
            </div>
            <div class="dashboard-kpi-footer">
                <span>12.4 days avg cycle</span>
                <span class="dashboard-link-action">Drilldown <i data-lucide="arrow-up-right" style="width: 12px; height: 12px;"></i></span>
            </div>
        </div>
    </div>

    <!-- 3C. HIGH-IMPACT KPI ROW (PROCUREMENT - ON TOGGLE) -->
    <div class="kpi-persona-group dashboard-kpi-grid" data-persona="procure" style="display: none;">
        <div class="dashboard-kpi-card">
            <div class="dashboard-kpi-header">
                <span>Procurement PO Spend</span>
                <span class="dashboard-badge-pill dashboard-badge-indigo">Q4 Active</span>
            </div>
            <div class="dashboard-kpi-value-row">
                <div class="dashboard-kpi-main-val">$315,200</div>
                <span class="dashboard-kpi-sub-val">Budget: $400k</span>
            </div>
            <div class="dashboard-progress-track">
                <div class="dashboard-progress-fill" style="width: 78.8%; background: #6366f1;"></div>
            </div>
            <div class="dashboard-kpi-footer">
                <span>$84,800 under budget</span>
                <span class="dashboard-link-action">Drilldown <i data-lucide="arrow-up-right" style="width: 12px; height: 12px;"></i></span>
            </div>
        </div>

        <div class="dashboard-kpi-card">
            <div class="dashboard-kpi-header">
                <span>Pending Deliveries</span>
                <span class="dashboard-badge-pill dashboard-badge-amber">In Transit</span>
            </div>
            <div class="dashboard-kpi-value-row">
                <div class="dashboard-kpi-main-val">14 Shipments</div>
                <span class="dashboard-kpi-sub-val">2 Customs Held</span>
            </div>
            <div style="height: 24px; display: flex; align-items: flex-end; gap: 4px;">
                <div style="flex: 1; background: #fde68a; height: 10px; border-radius: 2px 2px 0 0;"></div>
                <div style="flex: 1; background: #f59e0b; height: 16px; border-radius: 2px 2px 0 0;"></div>
                <div style="flex: 1; background: #d97706; height: 24px; border-radius: 2px 2px 0 0;"></div>
            </div>
            <div class="dashboard-kpi-footer">
                <span>Avg ETA: 3.2 days</span>
                <span class="dashboard-link-action">Drilldown <i data-lucide="arrow-up-right" style="width: 12px; height: 12px;"></i></span>
            </div>
        </div>

        <div class="dashboard-kpi-card">
            <div class="dashboard-kpi-header">
                <span>Settlement Invoices Due</span>
                <span class="dashboard-badge-pill dashboard-badge-rose">8 Overdue</span>
            </div>
            <div class="dashboard-kpi-value-row">
                <div class="dashboard-kpi-main-val">$84,500</div>
                <span class="dashboard-kpi-sub-val">Total: $142k</span>
            </div>
            <div style="height: 24px; display: flex; align-items: center;">
                <svg width="100%" height="20" viewBox="0 0 100 20" preserveAspectRatio="none" style="overflow: visible;">
                    <path fill="none" stroke="#e11d48" stroke-width="2.5" d="M0,18 Q30,12 60,6 T100,2" />
                    <path fill="rgba(225, 29, 72, 0.15)" d="M0,18 Q30,12 60,6 T100,2 L100,20 L0,20 Z" />
                </svg>
            </div>
            <div class="dashboard-kpi-footer">
                <span>Net-30 Terms</span>
                <span class="dashboard-link-action">Drilldown <i data-lucide="arrow-up-right" style="width: 12px; height: 12px;"></i></span>
            </div>
        </div>

        <div class="dashboard-kpi-card">
            <div class="dashboard-kpi-header">
                <span>Active Supplier Contracts</span>
                <span class="dashboard-badge-pill dashboard-badge-emerald">99.2% SLA</span>
            </div>
            <div class="dashboard-kpi-value-row">
                <div class="dashboard-kpi-main-val">28 Vendors</div>
                <span class="dashboard-kpi-sub-val">Tier-1 Managed</span>
            </div>
            <div style="height: 24px; display: flex; align-items: center;">
                <svg width="100%" height="20" viewBox="0 0 100 20" preserveAspectRatio="none" style="overflow: visible;">
                    <path fill="none" stroke="#10b981" stroke-width="2.5" d="M0,14 Q30,12 60,8 T100,2" />
                    <path fill="rgba(16, 185, 129, 0.15)" d="M0,14 Q30,12 60,8 T100,2 L100,20 L0,20 Z" />
                </svg>
            </div>
            <div class="dashboard-kpi-footer">
                <span>All Master MSAs Active</span>
                <span class="dashboard-link-action">Drilldown <i data-lucide="arrow-up-right" style="width: 12px; height: 12px;"></i></span>
            </div>
        </div>
    </div>

    <!-- 4. AI DEAL INSIGHTS & NEXT-BEST-ACTION CARD -->
    <div class="dashboard-ai-card">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
            <div style="display: flex; align-items: center; gap: 0.625rem;">
                <div style="width: 28px; height: 28px; border-radius: 8px; background: #4f46e5; color: #ffffff; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="sparkles" style="width: 16px; height: 16px;"></i>
                </div>
                <div>
                    <h3 style="font-size: 0.875rem; font-weight: 700; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                        AI Next-Best-Action &amp; Deal Health Insights
                        <span style="font-size: 0.625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; background: #e0e7ff; color: #4338ca; padding: 0.15rem 0.5rem; border-radius: 9999px; border: 1px solid #c7d2fe;">
                            Copilot Active
                        </span>
                    </h3>
                    <p style="font-size: 0.75rem; color: #64748b; margin: 0.15rem 0 0 0;">
                        Autonomous pipeline intelligence monitoring deals approaching critical stagnation thresholds.
                    </p>
                </div>
            </div>

            <button type="button" onclick="this.closest('.dashboard-ai-card').style.display='none'" style="color: #94a3b8; background: none; border: none; padding: 4px; cursor: pointer; border-radius: 6px;" title="Dismiss insights">
                <i data-lucide="x" style="width: 16px; height: 16px;"></i>
            </button>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 0.75rem; margin-top: 0.5rem;">
            <!-- Recommendation 1 -->
            <div style="padding: 0.85rem; border-radius: 12px; background: #f8fafc; border: 1px solid #e2e8f0; display: flex; flex-direction: column; justify-content: space-between; gap: 0.6rem;">
                <div>
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.25rem;">
                        <span style="font-size: 0.8125rem; font-weight: 700; color: #0f172a;">Acme Cloud Infrastructure Expansion</span>
                        <span class="dashboard-badge-pill dashboard-badge-amber">9 Days Inactive</span>
                    </div>
                    <div style="font-size: 0.75rem; color: #64748b; margin-bottom: 0.35rem;">
                        Acme Corp • $340,000 • Negotiation
                    </div>
                    <p style="font-size: 0.75rem; color: #475569; margin: 0; line-height: 1.4;">
                        No contact logged in 9 days. Deal velocity slowed by 35% compared to peer deals.
                    </p>
                </div>

                <div style="display: flex; align-items: center; justify-content: space-between; padding-top: 0.5rem; border-top: 1px solid #e2e8f0;">
                    <span style="font-size: 0.75rem; color: #334155; font-weight: 600;">Action: Send executive check-in email</span>
                    <button type="button" class="btn btn-primary btn-sm" onclick="showToast('Executive check-in email dispatched to Acme Corp!')">
                        <i data-lucide="mail" style="width: 12px; height: 12px;"></i>
                        <span>Send Email</span>
                    </button>
                </div>
            </div>

            <!-- Recommendation 2 -->
            <div style="padding: 0.85rem; border-radius: 12px; background: #f8fafc; border: 1px solid #e2e8f0; display: flex; flex-direction: column; justify-content: space-between; gap: 0.6rem;">
                <div>
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.25rem;">
                        <span style="font-size: 0.8125rem; font-weight: 700; color: #0f172a;">Horizon Biotech Diagnostic License</span>
                        <span class="dashboard-badge-pill dashboard-badge-blue">High Intent</span>
                    </div>
                    <div style="font-size: 0.75rem; color: #64748b; margin-bottom: 0.35rem;">
                        Horizon Biotech • $185,000 • Proposal / Quote
                    </div>
                    <p style="font-size: 0.75rem; color: #475569; margin: 0; line-height: 1.4;">
                        Quote viewed 4 times without signature request. High purchase intent detected.
                    </p>
                </div>

                <div style="display: flex; align-items: center; justify-content: space-between; padding-top: 0.5rem; border-top: 1px solid #e2e8f0;">
                    <span style="font-size: 0.75rem; color: #334155; font-weight: 600;">Action: Schedule 15-min pricing call</span>
                    <button type="button" class="btn btn-primary btn-sm" onclick="showToast('Pricing call touchpoint scheduled!')">
                        <i data-lucide="phone" style="width: 12px; height: 12px;"></i>
                        <span>Call Now</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 5. INTERACTIVE PIPELINE VELOCITY FUNNEL -->
    <div class="dashboard-funnel-card">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
            <div>
                <h3 style="font-size: 0.95rem; font-weight: 700; color: #0f172a; margin: 0;">
                    Pipeline Velocity Funnel
                </h3>
                <p style="font-size: 0.75rem; color: #64748b; margin: 0.15rem 0 0 0;">
                    Deal flow speed, stage drop-offs, and critical bottlenecks.
                </p>
            </div>
            <a href="{{ url('/opportunities') }}" class="dashboard-link-action" style="font-size: 0.75rem; text-decoration: none;">
                View Kanban Board →
            </a>
        </div>

        <div class="dashboard-funnel-grid">
            <!-- Stage 1 -->
            <div class="dashboard-stage-card" onclick="inspectStage('Prospecting', '$140k', 'Omni Retailers POS Expansion', 'Initial discovery scheduled for Thursday 10:00 AM')">
                <div class="dashboard-stage-name">1. Prospecting</div>
                <div class="dashboard-stage-value">$140,000</div>
                <div class="dashboard-stage-meta">8 deals • Avg 4d</div>
                <div class="dashboard-progress-track" style="margin-top: 0.5rem;">
                    <div class="dashboard-progress-fill" style="width: 25%; background: #6366f1;"></div>
                </div>
            </div>

            <!-- Stage 2 -->
            <div class="dashboard-stage-card" onclick="inspectStage('Qualified', '$365k', 'Starlight Media Streaming Engine', 'Technical proof-of-concept verified by lead architect')">
                <div class="dashboard-stage-name">2. Qualified</div>
                <div class="dashboard-stage-value">$365,000</div>
                <div class="dashboard-stage-meta">9 deals • Avg 8d</div>
                <div class="dashboard-progress-track" style="margin-top: 0.5rem;">
                    <div class="dashboard-progress-fill" style="width: 50%; background: #4f46e5;"></div>
                </div>
            </div>

            <!-- Stage 3 -->
            <div class="dashboard-stage-card" onclick="inspectStage('Proposal / Quote', '$480k', 'Horizon Biotech Diagnostic License', 'Price quote sent; awaiting procurement approval')">
                <div class="dashboard-stage-name">3. Proposal / Quote</div>
                <div class="dashboard-stage-value">$480,000</div>
                <div class="dashboard-stage-meta">6 deals • Avg 11d</div>
                <div class="dashboard-progress-track" style="margin-top: 0.5rem;">
                    <div class="dashboard-progress-fill" style="width: 70%; background: #2563eb;"></div>
                </div>
            </div>

            <!-- Stage 4 -->
            <div class="dashboard-stage-card active" onclick="inspectStage('Negotiation', '$610k', 'Acme Cloud Infrastructure Expansion', 'Ready for verbal confirmation. Expected close Friday.')">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div class="dashboard-stage-name">4. Negotiation</div>
                    <span class="dashboard-badge-pill dashboard-badge-amber">Rotting</span>
                </div>
                <div class="dashboard-stage-value">$610,000</div>
                <div class="dashboard-stage-meta">4 deals • Avg 15d</div>
                <div class="dashboard-progress-track" style="margin-top: 0.5rem;">
                    <div class="dashboard-progress-fill" style="width: 85%; background: #d97706;"></div>
                </div>
            </div>

            <!-- Stage 5 -->
            <div class="dashboard-stage-card won-active" onclick="inspectStage('Closed Won', '$820k', 'Apex Supply Chain International', 'Won by Marcus Brody 2 hours ago. Onboarding started.')">
                <div class="dashboard-stage-name" style="color: #065f46;">5. Closed Won</div>
                <div class="dashboard-stage-value" style="color: #047857;">$820,000</div>
                <div class="dashboard-stage-meta" style="color: #059669;">14 deals • Avg 22d</div>
                <div class="dashboard-progress-track" style="margin-top: 0.5rem;">
                    <div class="dashboard-progress-fill" style="width: 100%; background: #10b981;"></div>
                </div>
            </div>
        </div>

        <!-- Stage Inspector Drawer Trigger -->
        <div id="funnel-stage-inspector" class="dashboard-stage-inspector">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <span class="dashboard-badge-pill dashboard-badge-amber">Stage 4: Negotiation</span>
                <span style="font-weight: 700; color: #0f172a;">Acme Cloud Infrastructure Expansion ($340k)</span>
                <span style="color: #64748b;">• Ready for verbal confirmation. Expected close Friday.</span>
            </div>
            <a href="{{ url('/opportunities') }}" class="dashboard-link-action" style="text-decoration: none;">
                Inspect in Opportunities →
            </a>
        </div>
    </div>

    <!-- 6. "MY DAILY COCKPIT" SPLIT GRID (7/5) -->
    <div class="dashboard-cockpit-grid">
        <!-- Tasks Due Today (7 Cols) -->
        <div class="dashboard-cockpit-card">
            <div>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
                    <div>
                        <h3 style="font-size: 0.95rem; font-weight: 700; color: #0f172a; margin: 0;">
                            Tasks Due Today
                        </h3>
                        <p style="font-size: 0.75rem; color: #64748b; margin: 0.15rem 0 0 0;">
                            3 actionable items requiring your execution before EOD.
                        </p>
                    </div>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="openNewRecordModal('tasks')">
                        <i data-lucide="plus" style="width: 13px; height: 13px;"></i>
                        <span>Add Task</span>
                    </button>
                </div>

                <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                    <!-- Task 1 -->
                    <div style="padding: 0.75rem 0.85rem; border-radius: 12px; border: 1px solid #e2e8f0; background: #ffffff; display: flex; align-items: center; justify-content: space-between; gap: 0.75rem;">
                        <div style="display: flex; align-items: center; gap: 0.65rem; min-width: 0;">
                            <input type="checkbox" onchange="this.closest('div').parentElement.style.opacity = this.checked ? '0.5' : '1'" style="cursor: pointer; width: 15px; height: 15px; border-radius: 4px;">
                            <div style="min-width: 0;">
                                <div style="font-size: 0.8125rem; font-weight: 600; color: #0f172a; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    Contract follow-up call with Ram Enterprises
                                </div>
                                <div style="font-size: 0.725rem; color: #b45309; font-weight: 600;">
                                    ● Due 2:00 PM (Today) <span style="color: #64748b; font-weight: 400;">• Deal: Ram Enterprise Hardware Expansion</span>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="showToast('Calling Ram Enterprises...')">
                            <i data-lucide="phone" style="width: 12px; height: 12px;"></i>
                            <span>Call Now</span>
                        </button>
                    </div>

                    <!-- Task 2 -->
                    <div style="padding: 0.75rem 0.85rem; border-radius: 12px; border: 1px solid #e2e8f0; background: #ffffff; display: flex; align-items: center; justify-content: space-between; gap: 0.75rem;">
                        <div style="display: flex; align-items: center; gap: 0.65rem; min-width: 0;">
                            <input type="checkbox" onchange="this.closest('div').parentElement.style.opacity = this.checked ? '0.5' : '1'" style="cursor: pointer; width: 15px; height: 15px; border-radius: 4px;">
                            <div style="min-width: 0;">
                                <div style="font-size: 0.8125rem; font-weight: 600; color: #0f172a; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    Send updated CPQ price quote to Horizon Biotech
                                </div>
                                <div style="font-size: 0.725rem; color: #4338ca; font-weight: 600;">
                                    ● Due 4:30 PM (Today) <span style="color: #64748b; font-weight: 400;">• Contact: Dr. Sarah Vance</span>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="showToast('Dispatching updated CPQ quote...')">
                            <i data-lucide="file-spreadsheet" style="width: 12px; height: 12px;"></i>
                            <span>Send Quote</span>
                        </button>
                    </div>

                    <!-- Task 3 -->
                    <div style="padding: 0.75rem 0.85rem; border-radius: 12px; border: 1px solid #e2e8f0; background: #ffffff; display: flex; align-items: center; justify-content: space-between; gap: 0.75rem;">
                        <div style="display: flex; align-items: center; gap: 0.65rem; min-width: 0;">
                            <input type="checkbox" onchange="this.closest('div').parentElement.style.opacity = this.checked ? '0.5' : '1'" style="cursor: pointer; width: 15px; height: 15px; border-radius: 4px;">
                            <div style="min-width: 0;">
                                <div style="font-size: 0.8125rem; font-weight: 600; color: #0f172a; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    Prepare quarterly business review deck for SolarEnergix Grid
                                </div>
                                <div style="font-size: 0.725rem; color: #64748b;">
                                    ● Due Tomorrow morning <span style="color: #64748b;">• Account: SolarEnergix Grid</span>
                                </div>
                            </div>
                        </div>
                        <a href="{{ url('/tasks') }}" class="btn btn-secondary btn-sm" style="text-decoration: none;">
                            <span>Details</span>
                        </a>
                    </div>
                </div>
            </div>

            <div style="margin-top: 1rem; pt: 0.75rem; border-top: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 0.75rem; color: #64748b;">3 of 3 tasks remaining today</span>
                <a href="{{ url('/tasks') }}" class="dashboard-link-action" style="font-size: 0.75rem; text-decoration: none;">
                    View All Tasks →
                </a>
            </div>
        </div>

        <!-- Pending Approvals & Win Ticker (5 Cols) -->
        <div class="dashboard-cockpit-card">
            <div>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
                    <div>
                        <h3 style="font-size: 0.95rem; font-weight: 700; color: #0f172a; margin: 0;">
                            Pending Approvals
                        </h3>
                        <p style="font-size: 0.75rem; color: #64748b; margin: 0.15rem 0 0 0;">
                            Discounts &amp; contracts awaiting sign-off.
                        </p>
                    </div>
                    <span class="dashboard-badge-pill dashboard-badge-amber">1 Pending</span>
                </div>

                <!-- Approval Item -->
                <div style="padding: 0.85rem; border-radius: 12px; background: #fffbeb; border: 1px solid #fde68a; margin-bottom: 1rem;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.35rem;">
                        <span style="font-size: 0.8125rem; font-weight: 700; color: #92400e;">New Lead Approval: Delta Health</span>
                        <span style="font-size: 0.6875rem; font-weight: 600; color: #b45309;">Level 1 Review</span>
                    </div>
                    <div style="font-size: 0.75rem; color: #78350f; margin-bottom: 0.65rem;">
                        Submitter: <strong>Marcus Brody</strong> • Est Value: <strong>$125,000</strong>
                    </div>
                    <div style="display: flex; gap: 0.5rem;">
                        <button type="button" class="btn btn-sm" style="flex: 1; background: #059669; color: #ffffff; border: none;" onclick="showToast('Workflow approved successfully!'); this.parentElement.parentElement.style.opacity='0.4';">
                            ✓ Approve
                        </button>
                        <button type="button" class="btn btn-sm btn-secondary" style="flex: 1; border-color: #fde68a;" onclick="showToast('Workflow rejected.'); this.parentElement.parentElement.style.display='none';">
                            ✕ Reject
                        </button>
                    </div>
                </div>

                <!-- Recent Wins Ticker -->
                <div style="border-top: 1px solid #f1f5f9; padding-top: 0.75rem;">
                    <div style="font-size: 0.725rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: #64748b; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.35rem;">
                        <i data-lucide="trending-up" style="width: 13px; height: 13px; color: #10b981;"></i>
                        Recent Team Wins (Last 24h)
                    </div>
                    <div style="padding: 0.65rem 0.75rem; border-radius: 10px; background: #ecfdf5; border: 1px solid #a7f3d0; display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <div style="font-size: 0.785rem; font-weight: 700; color: #065f46;">Apex Supply Chain International</div>
                            <div style="font-size: 0.7rem; color: #047857;">Closed Won by Marcus Brody • 2h ago</div>
                        </div>
                        <span style="font-size: 0.85rem; font-weight: 800; color: #047857;">+$85k</span>
                    </div>
                </div>
            </div>

            <div style="margin-top: 1rem; pt: 0.75rem; border-top: 1px solid #f1f5f9; text-align: center;">
                <span style="font-size: 0.725rem; color: #64748b;">Automated Multi-Step Governance Enforced</span>
            </div>
        </div>
    </div>
</div>

<script>
    function switchPersona(persona) {
        document.querySelectorAll('.dashboard-role-tab').forEach(tab => {
            if (tab.getAttribute('data-persona') === persona) {
                tab.classList.add('active');
            } else {
                tab.classList.remove('active');
            }
        });

        document.querySelectorAll('.kpi-persona-group').forEach(group => {
            if (group.getAttribute('data-persona') === persona) {
                group.style.display = 'grid';
            } else {
                group.style.display = 'none';
            }
        });

        if (persona === 'exec') showToast('Switched to Sales Executive View');
        else if (persona === 'rep') showToast('Switched to Sales Representative View');
        else showToast('Switched to Procurement Specialist View');
    }

    function inspectStage(stage, val, deal, note) {
        document.querySelectorAll('.dashboard-stage-card').forEach(c => c.classList.remove('active'));
        event.currentTarget.classList.add('active');
        const inspector = document.getElementById('funnel-stage-inspector');
        if (inspector) {
            inspector.innerHTML = `
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <span class="dashboard-badge-pill dashboard-badge-indigo">${stage}</span>
                    <span style="font-weight: 700; color: #0f172a;">${deal} (${val})</span>
                    <span style="color: #64748b;">• ${note}</span>
                </div>
                <a href="{{ url('/opportunities') }}" class="dashboard-link-action" style="text-decoration: none;">
                    Inspect in Opportunities →
                </a>
            `;
        }
    }
</script>
@endsection
