<!-- =========================================================================
     MODAL 5: WIDGET CONFIGURATION MODAL (Edit Widget)
     ========================================================================= -->
<div id="modal-widget-config" class="modal-overlay" style="display: none;" onclick="closeWidgetConfigModal()">
    <div class="modal-content" style="max-width: 580px; width: 100%;" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <i data-lucide="calculator" style="width: 18px; height: 18px; color: #4f46e5;"></i>
                <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--zinc-950); margin: 0;">
                    Configure Widget Data &amp; Calculation
                </h3>
            </div>
            <button type="button" onclick="closeWidgetConfigModal()" class="btn btn-ghost btn-sm">
                <i data-lucide="x" style="width: 16px; height: 16px;"></i>
            </button>
        </div>

        <div class="modal-body" style="padding: 1.25rem; display: flex; flex-direction: column; gap: 1rem;">
            <input type="hidden" id="edit-widget-id" />
            <div class="form-group" style="margin: 0;">
                <label class="form-label" style="font-weight: 600;">Widget Title</label>
                <input type="text" id="edit-widget-title" class="form-input" />
            </div>
            <div class="form-group" style="margin: 0;">
                <label class="form-label" style="font-weight: 600;">Subtitle / Context</label>
                <input type="text" id="edit-widget-subtitle" class="form-input" />
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                <div class="form-group" style="margin: 0;">
                    <label class="form-label" style="font-weight: 600;">16-Col Span Width</label>
                    <select id="edit-widget-colspan" class="form-select">
                        <option value="4">4 cols (25%)</option>
                        <option value="6">6 cols (37.5%)</option>
                        <option value="8">8 cols (50%)</option>
                        <option value="10">10 cols (62.5%)</option>
                        <option value="12">12 cols (75%)</option>
                        <option value="14">14 cols (87.5%)</option>
                        <option value="16">16 cols (100%)</option>
                    </select>
                </div>
                <div class="form-group" style="margin: 0;">
                    <label class="form-label" style="font-weight: 600;">Color Palette</label>
                    <select id="edit-widget-palette" class="form-select">
                        <option value="violet">Electric Violet (#6366F1)</option>
                        <option value="cyan">Cyber Cyan (#06B6D4)</option>
                        <option value="emerald">Emerald Growth (#10B981)</option>
                        <option value="amber">Sunset Amber (#F59E0B)</option>
                        <option value="rose">Rose Flare (#F43F5E)</option>
                    </select>
                </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                <div class="form-group" style="margin: 0;">
                    <label class="form-label" style="font-weight: 600;">Module Source</label>
                    <select id="edit-widget-module" class="form-select">
                        <option value="opportunities">Opportunities (Deals)</option>
                        <option value="leads">Leads</option>
                        <option value="accounts">Accounts</option>
                        <option value="payments">Payments &amp; AR</option>
                    </select>
                </div>
                <div class="form-group" style="margin: 0;">
                    <label class="form-label" style="font-weight: 600;">Calculation Aggregation</label>
                    <select id="edit-widget-calc" class="form-select">
                        <option value="SUM">SUM (Total Value)</option>
                        <option value="COUNT">COUNT (Volume)</option>
                        <option value="AVG">AVG (Average)</option>
                        <option value="PERCENTAGE">PERCENTAGE (%)</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 0.5rem; padding: 1rem 1.25rem; border-top: 1px solid var(--zinc-100);">
            <button type="button" onclick="closeWidgetConfigModal()" class="btn btn-secondary">
                Cancel
            </button>
            <button type="button" onclick="handleSaveWidgetConfig()" class="btn btn-primary">
                Update Widget
            </button>
        </div>
    </div>
</div>
