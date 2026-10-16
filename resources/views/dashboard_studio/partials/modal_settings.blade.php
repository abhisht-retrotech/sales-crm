<!-- =========================================================================
     MODAL 4: GLOBAL SETTINGS & CONTROLS MODAL
     ========================================================================= -->
<div id="modal-settings" class="modal-overlay" style="display: none;" onclick="closeSettingsModal()">
    <div class="modal-content" style="max-width: 680px; width: 100%; max-height: 90vh; display: flex; flex-direction: column;" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <i data-lucide="calendar" style="width: 18px; height: 18px; color: #4f46e5;"></i>
                <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--zinc-950); margin: 0;">
                    Dashboard Global Settings &amp; Customization
                </h3>
            </div>
            <button type="button" onclick="closeSettingsModal()" class="btn btn-ghost btn-sm">
                <i data-lucide="x" style="width: 16px; height: 16px;"></i>
            </button>
        </div>

        <!-- Tabs Bar inside Settings Modal -->
        <div style="display: flex; gap: 4px; border-bottom: 1px solid var(--border-subtle); padding: 0 1.25rem; background: var(--zinc-50);">
            <button type="button" class="settings-subtab-btn" data-tab="filters" onclick="switchSettingsTab('filters')" style="padding: 0.65rem 0.85rem; font-size: 0.78rem; font-weight: 700; border: none; border-bottom: 2px solid var(--color-primary, #6366f1); background: transparent; color: var(--color-primary, #6366f1); cursor: pointer;">Filters &amp; Dates</button>
            <button type="button" class="settings-subtab-btn" data-tab="buttons" onclick="switchSettingsTab('buttons')" style="padding: 0.65rem 0.85rem; font-size: 0.78rem; font-weight: 500; border: none; border-bottom: 2px solid transparent; background: transparent; color: var(--zinc-600); cursor: pointer;">Header Buttons</button>
            <button type="button" class="settings-subtab-btn" data-tab="ai" onclick="switchSettingsTab('ai')" style="padding: 0.65rem 0.85rem; font-size: 0.78rem; font-weight: 500; border: none; border-bottom: 2px solid transparent; background: transparent; color: var(--zinc-600); cursor: pointer;">AI Deal Insights</button>
            <button type="button" class="settings-subtab-btn" data-tab="presets" onclick="switchSettingsTab('presets')" style="padding: 0.65rem 0.85rem; font-size: 0.78rem; font-weight: 500; border: none; border-bottom: 2px solid transparent; background: transparent; color: var(--zinc-600); cursor: pointer;">Layout Presets</button>
        </div>

        <div class="modal-body" style="display: flex; flex-direction: column; gap: 1.25rem; padding: 1.25rem; overflow-y: auto; flex: 1;">
            <!-- SUBTAB 1: FILTERS & DATES -->
            <div id="settings-tab-filters" style="display: flex; flex-direction: column; gap: 1rem;">
                <div style="padding: 0.85rem; border: 1px solid var(--border-subtle); border-radius: var(--radius-md); background: var(--zinc-50);">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                        <label style="font-weight: 600; font-size: 0.85rem; color: var(--zinc-900); cursor: pointer;">
                            Enable Universal Date Range Filtering
                        </label>
                        <input type="checkbox" checked style="cursor: pointer;" />
                    </div>
                    <p style="font-size: 0.75rem; color: var(--zinc-500); margin: 0;">
                        Enables preset date filters (Today, Yesterday, This Week, Last Week, This Month, Last Month, This Quarter, This Year, All Time).
                    </p>
                </div>

                <div style="padding: 0.85rem; border: 1px solid var(--border-subtle); border-radius: var(--radius-md); background: var(--zinc-50);">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                        <label style="font-weight: 600; font-size: 0.85rem; color: var(--zinc-900); cursor: pointer;">
                            Enable Secondary Date Basis Filter (Closed Date, Wire Date, Paid Date)
                        </label>
                        <input type="checkbox" checked style="cursor: pointer;" />
                    </div>
                    <p style="font-size: 0.75rem; color: var(--zinc-500); margin: 0;">
                        Allows users to anchor metrics by Deal Closed Date, Wire / Payment Received Date, or Invoice Paid Date.
                    </p>
                </div>

                <div class="form-group" style="margin: 0;">
                    <label class="form-label" style="font-weight: 600;">Default Date Preset for New Sessions</label>
                    <select class="form-select">
                        <option>This Month (Recommended)</option>
                        <option>This Quarter</option>
                        <option>This Year</option>
                        <option>All Time</option>
                    </select>
                </div>
            </div>

            <!-- SUBTAB 2: HEADER BUTTONS -->
            <div id="settings-tab-buttons" style="display: none; flex-direction: column; gap: 1rem;">
                <p style="font-size: 0.8rem; color: var(--zinc-600); margin: 0;">
                    Configure up to 6 fast action buttons displayed across the CRM Command Header.
                </p>
                <div style="padding: 0.85rem; background: var(--zinc-50); border-radius: var(--radius-md); border: 1px solid var(--border-subtle); font-size: 0.8125rem;">
                    Fast action buttons can be quickly added, removed and ordered directly in the Layout Designer canvas at the top of the page.
                </div>
            </div>

            <!-- SUBTAB 3: AI DEAL INSIGHTS -->
            <div id="settings-tab-ai" style="display: none; flex-direction: column; gap: 1rem;">
                <div style="padding: 0.85rem; border: 1px solid var(--border-subtle); border-radius: var(--radius-md); background: var(--zinc-50);">
                    <label style="font-weight: 700; font-size: 0.85rem; color: var(--zinc-900); display: block; margin-bottom: 0.5rem;">
                        AI Autonomous Deal Stagnation Detection
                    </label>
                    <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.8125rem;">
                        <span>Alert when deal stays inactive for more than</span>
                        <input type="number" value="9" min="1" max="60" style="width: 50px; padding: 2px 6px; border: 1px solid var(--zinc-300); border-radius: 4px;" />
                        <span>days in stage.</span>
                    </div>
                </div>
            </div>

            <!-- SUBTAB 4: PRESETS -->
            <div id="settings-tab-presets" style="display: none; flex-direction: column; gap: 1rem;">
                <p style="font-size: 0.78rem; color: var(--zinc-600); margin: 0;">
                    Save and load named dashboard layout presets across security profiles:
                </p>
                <div style="padding: 0.85rem; border: 1px solid var(--border-subtle); border-radius: var(--radius-md); background: var(--zinc-50);">
                    <label style="font-size: 0.8rem; font-weight: 700; color: var(--zinc-900); display: block; margin-bottom: 0.4rem;">
                        Save Current Layout as New Preset
                    </label>
                    <div style="display: flex; gap: 0.5rem;">
                        <input type="text" id="preset-name-input" placeholder="e.g. Q4 Sprint Push Layout" class="form-input" style="flex: 1; font-size: 0.78rem;" />
                        <button type="button" onclick="handleSavePreset()" class="btn btn-primary btn-sm">Save Preset</button>
                    </div>
                </div>
                <div>
                    <label style="font-size: 0.8rem; font-weight: 700; color: var(--zinc-900); display: block; margin-bottom: 0.4rem;">
                        Saved Preset Templates
                    </label>
                    <div style="display: flex; flex-direction: column; gap: 0.4rem;">
                        <div style="padding: 0.65rem 0.85rem; border: 1px solid var(--border-subtle); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: space-between;">
                            <span style="font-size: 0.8rem; font-weight: 600; color: var(--zinc-800);">Executive Revenue Cockpit <span style="font-size: 0.68rem; color: #4f46e5;">(Default)</span></span>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="showStudioToast('Preset Executive Revenue Cockpit applied!')">Apply Layout</button>
                        </div>
                        <div style="padding: 0.65rem 0.85rem; border: 1px solid var(--border-subtle); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: space-between;">
                            <span style="font-size: 0.8rem; font-weight: 600; color: var(--zinc-800);">Sales Rep High-Velocity Sprint</span>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="showStudioToast('Preset Sales Rep High-Velocity Sprint applied!')">Apply Layout</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 0.5rem; padding: 1rem 1.25rem; border-top: 1px solid var(--zinc-100);">
            <button type="button" onclick="closeSettingsModal()" class="btn btn-secondary">
                Cancel
            </button>
            <button type="button" onclick="closeSettingsModal(); showStudioToast('Dashboard settings published successfully!');" class="btn btn-primary">
                Save Settings
            </button>
        </div>
    </div>
</div>
