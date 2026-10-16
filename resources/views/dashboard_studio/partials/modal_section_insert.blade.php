<!-- =========================================================================
     MODAL 2: INSERT NEW SECTION MODAL
     ========================================================================= -->
<div id="modal-insert-section" class="modal-overlay" style="display: none;" onclick="closeInsertSectionModal()">
    <div class="modal-content" style="max-width: 480px; width: 100%;" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <i data-lucide="folder-plus" style="width: 18px; height: 18px; color: #4f46e5;"></i>
                <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--zinc-950); margin: 0;">
                    Insert New Canvas Section
                </h3>
            </div>
            <button type="button" onclick="closeInsertSectionModal()" class="btn btn-ghost btn-sm">
                <i data-lucide="x" style="width: 16px; height: 16px;"></i>
            </button>
        </div>

        <div class="modal-body" style="padding: 1.25rem; display: flex; flex-direction: column; gap: 1rem;">
            <p style="font-size: 0.8rem; color: var(--zinc-600); margin: 0;">
                Divide this profile's dashboard into designated sections (e.g. "Pipeline Velocity", "Revenue &amp; Quotas", "Operations").
            </p>
            <div class="form-group" style="margin: 0;">
                <label class="form-label" style="font-weight: 600;">Section Title *</label>
                <input
                    type="text"
                    id="new-section-title-input"
                    class="form-input"
                    placeholder="e.g. Pipeline Health &amp; Conversion Telemetry"
                />
            </div>
            <div class="form-group" style="margin: 0;">
                <label class="form-label" style="font-weight: 600;">Section Subtitle (Optional)</label>
                <input
                    type="text"
                    id="new-section-subtitle-input"
                    class="form-input"
                    placeholder="e.g. Stage drop-offs, lead scores, and velocity benchmarks"
                />
            </div>
        </div>

        <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 0.5rem; padding: 1rem 1.25rem; border-top: 1px solid var(--zinc-100);">
            <button type="button" onclick="closeInsertSectionModal()" class="btn btn-secondary">
                Cancel
            </button>
            <button type="button" onclick="handleCreateSection()" class="btn btn-primary">
                Create Section
            </button>
        </div>
    </div>
</div>
