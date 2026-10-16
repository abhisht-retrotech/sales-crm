    <!-- =========================================================================
         TAB 2: LIVE PROFILE PREVIEW TAB (Matches React lines 2179-2424)
         ========================================================================= -->
    <div id="tab-content-preview" style="display: none; flex-direction: column; gap: 1.25rem;">
        <!-- Preview Top Bar -->
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: var(--radius-xl); padding: 1.25rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
            <div>
                <span style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em;">
                    Previewing Active Layout for Profile:
                </span>
                <div id="preview-profile-title" style="font-size: 1.125rem; font-weight: 700; color: #0f172a;">
                    System Administrator Workspace (16-Column Grid • 3 Sections)
                </div>
                <p style="font-size: 0.75rem; color: #64748b; margin: 0.2rem 0 0 0;">
                    Exact multi-section dashboard experience rendered when a user with profile <strong id="preview-profile-name">System Administrator</strong> signs in.
                </p>
            </div>
            <button
                type="button"
                onclick="handleSaveAndPublish()"
                class="btn btn-primary"
                style="display: inline-flex; align-items: center; gap: 6px;"
            >
                <i data-lucide="save" style="width: 14px; height: 14px;"></i>
                <span>Publish This Layout</span>
            </button>
        </div>

        <!-- 1. TOP HERO COMMAND HEADER -->
        <div style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 60%, #1e1b4b 100%); border-radius: 16px; padding: 1.5rem 1.75rem; color: #ffffff; box-shadow: 0 4px 20px -2px rgba(30, 27, 75, 0.35); position: relative; overflow: hidden;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; position: relative; z-index: 1;">
                <div>
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                        <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #a5b4fc;">
                            DealSync Command Center
                        </span>
                        <span style="display: inline-flex; align-items: center; gap: 4px; font-size: 0.6875rem; background: rgba(16, 185, 129, 0.2); color: #6ee7b7; padding: 2px 8px; border-radius: 9999px; border: 1px solid rgba(110, 231, 183, 0.3);">
                            <span style="width: 6px; height: 6px; border-radius: 50%; background: #10b981;"></span>
                            Live Preview Active
                        </span>
                    </div>
                    <h2 style="font-size: 1.5rem; font-weight: 800; margin: 0; letter-spacing: -0.02em;">
                        Good evening, Marcus 👋
                    </h2>
                    <p style="font-size: 0.8125rem; color: #c7d2fe; margin: 0.25rem 0 0 0;">
                        You have <strong style="color: #ffffff;">3 tasks due today</strong> and <strong style="color: #ffffff;">2 deals closing ($185k)</strong> this week.
                    </p>
                </div>

                <!-- Preview Actions Cluster -->
                <div id="hero-preview-buttons-cluster" style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                    <!-- Rendered dynamically -->
                </div>
            </div>
        </div>

        <!-- 2. INTERACTIVE PERSONA & FILTER BAR -->
        <div id="preview-persona-bar" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 0.75rem 1rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <span style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Workspace Persona:</span>
                <div style="display: flex; background: #f1f5f9; padding: 3px; border-radius: 8px; gap: 3px;">
                    <button type="button" class="btn btn-sm" style="background: #ffffff; color: #0f172a; font-weight: 700; padding: 3px 10px; font-size: 0.75rem; border-radius: 6px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); border: none;">Executive</button>
                    <button type="button" class="btn btn-sm" style="background: transparent; color: #64748b; font-weight: 500; padding: 3px 10px; font-size: 0.75rem; border-radius: 6px; border: none;">Sales Rep</button>
                    <button type="button" class="btn btn-sm" style="background: transparent; color: #64748b; font-weight: 500; padding: 3px 10px; font-size: 0.75rem; border-radius: 6px; border: none;">Procurement</button>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 0.65rem;">
                <div style="display: flex; align-items: center; gap: 0.35rem;">
                    <i data-lucide="calendar" style="width: 14px; height: 14px; color: #64748b;"></i>
                    <select class="form-select" style="font-size: 0.75rem; height: 28px; padding: 0 0.5rem;">
                        <option>This Month (October 2026)</option>
                        <option>This Quarter (Q4 2026)</option>
                        <option>This Year (2026)</option>
                    </select>
                </div>

                <div style="display: flex; align-items: center; gap: 0.35rem;">
                    <i data-lucide="users" style="width: 14px; height: 14px; color: #64748b;"></i>
                    <select class="form-select" style="font-size: 0.75rem; height: 28px; padding: 0 0.5rem;">
                        <option>Entire Sales Org (All Reps)</option>
                        <option>North America Enterprise</option>
                        <option>EMEA Strategic</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- 3. HIGH-IMPACT 4-COLUMN KPI ROW -->
        <div id="preview-kpi-row" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem;">
            <!-- KPI 1 -->
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 1.15rem; display: flex; flex-direction: column; justify-content: space-between;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Net ARR Run-Rate</span>
                    <span style="font-size: 0.6875rem; font-weight: 700; color: #059669; background: #ecfdf5; padding: 2px 6px; border-radius: 6px; border: 1px solid #a7f3d0;">↑ +24.8% YoY</span>
                </div>
                <div style="font-size: 1.75rem; font-weight: 800; color: #0f172a; margin: 0.5rem 0;">$48.6M</div>
                <div style="display: flex; align-items: center; justify-content: space-between; font-size: 0.72rem; color: #64748b;">
                    <span>Goal: $45.0M</span>
                    <span style="color: #4f46e5; font-weight: 700;">108% Paced</span>
                </div>
            </div>

            <!-- KPI 2 -->
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 1.15rem; display: flex; flex-direction: column; justify-content: space-between;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Weighted Pipeline</span>
                    <span style="font-size: 0.6875rem; font-weight: 700; color: #0891b2; background: #ecfeff; padding: 2px 6px; border-radius: 6px; border: 1px solid #a5f3fc;">118 Active</span>
                </div>
                <div style="font-size: 1.75rem; font-weight: 800; color: #0f172a; margin: 0.5rem 0;">$142.8M</div>
                <div style="display: flex; align-items: center; justify-content: space-between; font-size: 0.72rem; color: #64748b;">
                    <span>Target: $130.0M</span>
                    <span style="color: #0891b2; font-weight: 700;">+9.8% Safety</span>
                </div>
            </div>

            <!-- KPI 3 -->
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 1.15rem; display: flex; flex-direction: column; justify-content: space-between;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Win Velocity Rate</span>
                    <span style="font-size: 0.6875rem; font-weight: 700; color: #059669; background: #ecfdf5; padding: 2px 6px; border-radius: 6px; border: 1px solid #a7f3d0;">↑ +4.2%</span>
                </div>
                <div style="font-size: 1.75rem; font-weight: 800; color: #0f172a; margin: 0.5rem 0;">34.2%</div>
                <div style="display: flex; align-items: center; justify-content: space-between; font-size: 0.72rem; color: #64748b;">
                    <span>Avg Close: 19 days</span>
                    <span style="color: #059669; font-weight: 700;">Top Decile</span>
                </div>
            </div>

            <!-- KPI 4 -->
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 1.15rem; display: flex; flex-direction: column; justify-content: space-between;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Net Revenue Retention</span>
                    <span style="font-size: 0.6875rem; font-weight: 700; color: #e11d48; background: #fff1f2; padding: 2px 6px; border-radius: 6px; border: 1px solid #fecdd3;">1.1% Churn</span>
                </div>
                <div style="font-size: 1.75rem; font-weight: 800; color: #0f172a; margin: 0.5rem 0;">128.4%</div>
                <div style="display: flex; align-items: center; justify-content: space-between; font-size: 0.72rem; color: #64748b;">
                    <span>Benchmark: 120%</span>
                    <span style="color: #059669; font-weight: 700;">Expansion High</span>
                </div>
            </div>
        </div>

        <!-- 4. AI DEAL INSIGHTS COPILOT & PIPELINE FUNNEL GRID -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 1rem;">
            <!-- AI Copilot Card -->
            <div id="preview-ai-card" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 1.25rem; display: flex; flex-direction: column; gap: 0.85rem;">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <i data-lucide="sparkles" style="width: 18px; height: 18px; color: #4f46e5;"></i>
                        <h3 style="font-size: 0.95rem; font-weight: 700; color: #0f172a; margin: 0;">AI Deal Insights &amp; Stagnation Copilot</h3>
                    </div>
                    <span style="font-size: 0.6875rem; background: #eff6ff; color: #3b82f6; padding: 2px 8px; border-radius: 9999px; font-weight: 700;">2 At Risk</span>
                </div>

                <div style="display: flex; flex-direction: column; gap: 0.65rem;">
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 0.75rem;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                            <div>
                                <span style="font-size: 0.825rem; font-weight: 700; color: #0f172a;">Apex Global Systems</span>
                                <span style="font-size: 0.75rem; color: #64748b; display: block;">$450,000 ARR • Negotiation Stage</span>
                            </div>
                            <span style="font-size: 0.675rem; background: #fef2f2; color: #b91c1c; padding: 2px 6px; border-radius: 4px; font-weight: 700;">14d inactive</span>
                        </div>
                        <p style="font-size: 0.75rem; color: #475569; margin: 0.35rem 0 0.5rem 0;">No executive response since contract redline. Recommended action: send C-level alignment note.</p>
                        <button type="button" class="btn btn-primary btn-sm" style="font-size: 0.72rem; padding: 0.25rem 0.65rem;" onclick="showStudioToast('AI Copilot: Generated and queued executive re-engagement note!')">
                            ⚡ Send Executive Email
                        </button>
                    </div>

                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 0.75rem;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                            <div>
                                <span style="font-size: 0.825rem; font-weight: 700; color: #0f172a;">Novartis Biotech</span>
                                <span style="font-size: 0.75rem; color: #64748b; display: block;">$280,000 ARR • Proposal Review</span>
                            </div>
                            <span style="font-size: 0.675rem; background: #fffbeb; color: #b45309; padding: 2px 6px; border-radius: 4px; font-weight: 700;">11d inactive</span>
                        </div>
                        <p style="font-size: 0.75rem; color: #475569; margin: 0.35rem 0 0.5rem 0;">Procurement requested SOC2 update. Rep has not attached latest certification.</p>
                        <button type="button" class="btn btn-secondary btn-sm" style="font-size: 0.72rem; padding: 0.25rem 0.65rem;" onclick="showStudioToast('AI Copilot: Security packet queued for Rep delivery!')">
                            📞 Schedule Rep Check-in
                        </button>
                    </div>
                </div>
            </div>

            <!-- Pipeline Funnel Waterfall Card -->
            <div id="preview-funnel-card" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 1.25rem; display: flex; flex-direction: column; justify-content: space-between;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <i data-lucide="filter" style="width: 18px; height: 18px; color: #0891b2;"></i>
                        <h3 style="font-size: 0.95rem; font-weight: 700; color: #0f172a; margin: 0;">Enterprise Conversion Funnel</h3>
                    </div>
                    <span style="font-size: 0.6875rem; color: #64748b; font-weight: 600;">Overall Win: 34.2%</span>
                </div>

                <div style="display: flex; flex-direction: column; gap: 0.45rem;">
                    <!-- Discovery -->
                    <div>
                        <div style="display: flex; justify-content: space-between; font-size: 0.75rem; margin-bottom: 2px;">
                            <span style="font-weight: 600; color: #334155;">1. Discovery &amp; Qualification</span>
                            <span style="font-weight: 700; color: #0f172a;">118 Deals ($142.8M)</span>
                        </div>
                        <div style="height: 14px; background: #f1f5f9; border-radius: 4px; overflow: hidden;">
                            <div style="width: 100%; height: 100%; background: #6366f1; border-radius: 4px;"></div>
                        </div>
                    </div>

                    <!-- Proposal -->
                    <div>
                        <div style="display: flex; justify-content: space-between; font-size: 0.75rem; margin-bottom: 2px;">
                            <span style="font-weight: 600; color: #334155;">2. Solution Architecture &amp; Demo</span>
                            <span style="font-weight: 700; color: #0f172a;">84 Deals ($98.4M)</span>
                        </div>
                        <div style="height: 14px; background: #f1f5f9; border-radius: 4px; overflow: hidden;">
                            <div style="width: 71%; height: 100%; background: #06b6d4; border-radius: 4px;"></div>
                        </div>
                    </div>

                    <!-- Quote -->
                    <div>
                        <div style="display: flex; justify-content: space-between; font-size: 0.75rem; margin-bottom: 2px;">
                            <span style="font-weight: 600; color: #334155;">3. Commercial Proposal Submitted</span>
                            <span style="font-weight: 700; color: #0f172a;">52 Deals ($62.1M)</span>
                        </div>
                        <div style="height: 14px; background: #f1f5f9; border-radius: 4px; overflow: hidden;">
                            <div style="width: 44%; height: 100%; background: #3b82f6; border-radius: 4px;"></div>
                        </div>
                    </div>

                    <!-- Negotiation -->
                    <div>
                        <div style="display: flex; justify-content: space-between; font-size: 0.75rem; margin-bottom: 2px;">
                            <span style="font-weight: 600; color: #334155;">4. Legal Review &amp; Redlines</span>
                            <span style="font-weight: 700; color: #0f172a;">38 Deals ($48.6M)</span>
                        </div>
                        <div style="height: 14px; background: #f1f5f9; border-radius: 4px; overflow: hidden;">
                            <div style="width: 32%; height: 100%; background: #f59e0b; border-radius: 4px;"></div>
                        </div>
                    </div>

                    <!-- Closed Won -->
                    <div>
                        <div style="display: flex; justify-content: space-between; font-size: 0.75rem; margin-bottom: 2px;">
                            <span style="font-weight: 600; color: #334155;">5. Closed Won Execution</span>
                            <span style="font-weight: 700; color: #059669;">29 Deals ($36.4M)</span>
                        </div>
                        <div style="height: 14px; background: #f1f5f9; border-radius: 4px; overflow: hidden;">
                            <div style="width: 25%; height: 100%; background: #10b981; border-radius: 4px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 5. CUSTOM 16-COLUMN ANALYTICS SECTIONS IN PREVIEW -->
        <div id="preview-sections-container" style="display: flex; flex-direction: column; gap: 1.5rem; border-top: 1px solid #e2e8f0; padding-top: 1.25rem;">
            <!-- Rendered dynamically -->
        </div>
    </div>
</div>
