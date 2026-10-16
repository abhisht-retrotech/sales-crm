        <!-- 2. MODERN COMMAND CENTER COMPONENTS CONFIGURATOR -->
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 1.25rem 1.5rem; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04);">
            <div style="margin-bottom: 1rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <h3 style="font-size: 1rem; font-weight: 700; color: #0f172a; margin: 0;">
                        Modern Command Center Components (Global CRM Experience)
                    </h3>
                    <span style="font-size: 0.6875rem; font-weight: 700; background: #ecfdf5; color: #047857; padding: 0.15rem 0.5rem; border-radius: 9999px; border: 1px solid #a7f3d0;">
                        5 Executive Modules
                    </span>
                </div>
                <p style="font-size: 0.75rem; color: #64748b; margin: 0.2rem 0 0 0;">
                    Toggle and configure the interactive high-impact cards rendered on the Home Dashboard for all reps and executives.
                </p>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 0.85rem;">
                <!-- Module 1: High-Impact KPI Row -->
                <div style="padding: 0.85rem; border-radius: 12px; border: 1px solid #e2e8f0; background: #ffffff;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.35rem;">
                        <span style="font-size: 0.8125rem; font-weight: 700; color: #0f172a;">
                            📊 High-Impact 4-Column KPI Row
                        </span>
                        <input
                            type="checkbox"
                            id="toggle-kpi-row"
                            checked
                            onchange="handleToggleSetting('showKpiRow', this.checked)"
                            style="cursor: pointer;"
                        />
                    </div>
                    <p style="font-size: 0.6875rem; color: #64748b; margin: 0;">
                        Quota Attainment, Weighted Pipeline, NRR, and Active Accounts with sparklines, target bars &amp; click-to-drilldown database popups.
                    </p>
                </div>

                <!-- Module 2: AI Deal Insights -->
                <div style="padding: 0.85rem; border-radius: 12px; border: 1px solid #e2e8f0; background: #ffffff;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.35rem;">
                        <span style="font-size: 0.8125rem; font-weight: 700; color: #0f172a;">
                            ✨ AI Deal Insights Copilot
                        </span>
                        <input
                            type="checkbox"
                            id="toggle-ai-insights"
                            checked
                            onchange="handleToggleAi(this.checked)"
                            style="cursor: pointer;"
                        />
                    </div>
                    <p style="font-size: 0.6875rem; color: #64748b; margin: 0 0 0.5rem 0;">
                        Autonomous stagnation detection with one-click next-best-action execution.
                    </p>
                    <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.6875rem; color: #475569;">
                        <span>Threshold:</span>
                        <input
                            type="number"
                            min="1"
                            max="60"
                            id="ai-threshold-days"
                            value="9"
                            onchange="handleUpdateAiThreshold(this.value)"
                            style="width: 45px; padding: 2px 4px; border: 1px solid #cbd5e1; border-radius: 4px;"
                        />
                        <span>days inactive</span>
                    </div>
                </div>

                <!-- Module 3: Pipeline Velocity Funnel -->
                <div style="padding: 0.85rem; border-radius: 12px; border: 1px solid #e2e8f0; background: #ffffff;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.35rem;">
                        <span style="font-size: 0.8125rem; font-weight: 700; color: #0f172a;">
                            ⚡ Pipeline Velocity Funnel
                        </span>
                        <input
                            type="checkbox"
                            id="toggle-pipeline-funnel"
                            checked
                            onchange="handleToggleSetting('showPipelineFunnel', this.checked)"
                            style="cursor: pointer;"
                        />
                    </div>
                    <p style="font-size: 0.6875rem; color: #64748b; margin: 0;">
                        5-stage interactive waterfall with deal count, average stage velocity, rotting deal warnings, and instant deal inspections.
                    </p>
                </div>

                <!-- Module 4: Daily Action Cockpit -->
                <div style="padding: 0.85rem; border-radius: 12px; border: 1px solid #e2e8f0; background: #ffffff;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.35rem;">
                        <span style="font-size: 0.8125rem; font-weight: 700; color: #0f172a;">
                            🎯 Today's Action Cockpit (7/5 Split)
                        </span>
                        <input
                            type="checkbox"
                            id="toggle-action-cockpit"
                            checked
                            onchange="handleToggleSetting('showActionCockpit', this.checked)"
                            style="cursor: pointer;"
                        />
                    </div>
                    <p style="font-size: 0.6875rem; color: #64748b; margin: 0 0 0.5rem 0;">
                        Daily priorities checklist, in-line one-click approval workflows, and live team celebration ticker.
                    </p>
                    <div style="display: flex; gap: 0.75rem; font-size: 0.6875rem;">
                        <label style="display: flex; align-items: center; gap: 3px; cursor: pointer;">
                            <input
                                type="checkbox"
                                id="toggle-approvals"
                                checked
                                onchange="handleToggleSetting('showApprovalsQueue', this.checked)"
                            />
                            <span>Approvals</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 3px; cursor: pointer;">
                            <input
                                type="checkbox"
                                id="toggle-win-ticker"
                                checked
                                onchange="handleToggleSetting('showWinTicker', this.checked)"
                            />
                            <span>Win Ticker</span>
                        </label>
                    </div>
                </div>

                <!-- Module 5: Persona Switcher -->
                <div style="padding: 0.85rem; border-radius: 12px; border: 1px solid #e2e8f0; background: #ffffff;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.35rem;">
                        <span style="font-size: 0.8125rem; font-weight: 700; color: #0f172a;">
                            👥 Workspace Persona &amp; Role Tabs
                        </span>
                        <input
                            type="checkbox"
                            id="toggle-persona-switcher"
                            checked
                            onchange="handleToggleSetting('showPersonaSwitcher', this.checked)"
                            style="cursor: pointer;"
                        />
                    </div>
                    <p style="font-size: 0.6875rem; color: #64748b; margin: 0;">
                        Allows sales users to toggle views between Sales Executive, Sales Representative, and Procurement Specialist.
                    </p>
                </div>

                <!-- Module 6: Milestone Confetti -->
                <div style="padding: 0.85rem; border-radius: 12px; border: 1px solid #e2e8f0; background: #ffffff;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.35rem;">
                        <span style="font-size: 0.8125rem; font-weight: 700; color: #0f172a;">
                            🎉 100% Quota Milestone Confetti
                        </span>
                        <input
                            type="checkbox"
                            id="toggle-confetti"
                            checked
                            onchange="handleToggleSetting('enableConfettiMilestone', this.checked)"
                            style="cursor: pointer;"
                        />
                    </div>
                    <p style="font-size: 0.6875rem; color: #64748b; margin: 0;">
                        Full-screen celebratory particle confetti celebration effect upon 100% monthly quota attainment.
                    </p>
                </div>
            </div>
        </div>
