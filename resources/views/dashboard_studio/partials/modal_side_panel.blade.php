<!-- =========================================================================
     MODAL 6: SIDE PANEL (DRILLDOWN DRAWER) STUDIO CONFIG MODAL
     ========================================================================= -->
<div id="modal-sidepanel-config" class="modal-overlay" style="display: none;" onclick="closeSidePanelConfigModal()">
    <div class="modal-content" style="max-width: 580px; width: 100%;" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <i data-lucide="table" style="width: 18px; height: 18px; color: #4f46e5;"></i>
                <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--zinc-950); margin: 0;">
                    Raw Data Drilldown Popup Configurator
                </h3>
            </div>
            <button type="button" onclick="closeSidePanelConfigModal()" class="btn btn-ghost btn-sm">
                <i data-lucide="x" style="width: 16px; height: 16px;"></i>
            </button>
        </div>

        <div class="modal-body" style="padding: 1.25rem; display: flex; flex-direction: column; gap: 1rem;">
            <input type="hidden" id="sidepanel-widget-id" />
            <p style="font-size: 0.8rem; color: var(--zinc-600); margin: 0;">
                Configure the slide-over drawer that displays raw contributing records when users click this visualization.
            </p>
            <div style="padding: 0.85rem; border: 1px solid var(--border-subtle); border-radius: var(--radius-md); background: var(--zinc-50);">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <label style="font-weight: 700; font-size: 0.85rem; color: var(--zinc-900);">
                        Enable Click-to-Drilldown Popup
                    </label>
                    <input type="checkbox" id="sidepanel-enabled" checked style="cursor: pointer;" />
                </div>
            </div>
            <div class="form-group" style="margin: 0;">
                <label class="form-label" style="font-weight: 600;">Custom Popup Title</label>
                <input type="text" id="sidepanel-title" class="form-input" placeholder="e.g. Detailed Contributing Records" />
            </div>
            <div style="padding: 0.85rem; border: 1px solid var(--border-subtle); border-radius: var(--radius-md); background: #ffffff;">
                <label style="font-size: 0.8rem; font-weight: 700; color: var(--zinc-900); display: block; margin-bottom: 0.4rem;">
                    Included Raw Data Columns
                </label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; font-size: 0.75rem;">
                    <label style="display: flex; align-items: center; gap: 4px;"><input type="checkbox" checked /> Deal Name</label>
                    <label style="display: flex; align-items: center; gap: 4px;"><input type="checkbox" checked /> Amount / ARR</label>
                    <label style="display: flex; align-items: center; gap: 4px;"><input type="checkbox" checked /> Pipeline Stage</label>
                    <label style="display: flex; align-items: center; gap: 4px;"><input type="checkbox" checked /> Opportunity Owner</label>
                    <label style="display: flex; align-items: center; gap: 4px;"><input type="checkbox" checked /> Close Date</label>
                    <label style="display: flex; align-items: center; gap: 4px;"><input type="checkbox" checked /> Lead Source</label>
                </div>
            </div>
        </div>

        <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 0.5rem; padding: 1rem 1.25rem; border-top: 1px solid var(--zinc-100);">
            <button type="button" onclick="closeSidePanelConfigModal()" class="btn btn-secondary">
                Cancel
            </button>
            <button type="button" onclick="handleSaveSidePanelConfig()" class="btn btn-primary">
                Save Drilldown Setup
            </button>
        </div>
    </div>
</div>
