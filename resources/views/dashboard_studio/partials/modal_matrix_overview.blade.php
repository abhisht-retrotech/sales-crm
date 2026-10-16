<!-- =========================================================================
     MODAL 3: PROFILE-WISE DASHBOARDS ASSIGNMENT MATRIX
     ========================================================================= -->
<div id="modal-matrix" class="modal-overlay" style="display: none;" onclick="closeMatrixModal()">
    <div class="modal-content" style="max-width: 780px; width: 100%; max-height: 90vh; display: flex; flex-direction: column;" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <i data-lucide="users" style="width: 18px; height: 18px; color: #4f46e5;"></i>
                <div>
                    <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--zinc-950); margin: 0;">
                        Profile-Wise Dashboard Layouts &amp; Assignments
                    </h3>
                    <p style="font-size: 0.75rem; color: var(--zinc-500); margin: 0.15rem 0 0 0;">
                        Each security profile has its own independently configured 16-column dashboard page with sections.
                    </p>
                </div>
            </div>
            <button type="button" onclick="closeMatrixModal()" class="btn btn-ghost btn-sm">
                <i data-lucide="x" style="width: 16px; height: 16px;"></i>
            </button>
        </div>

        <div class="modal-body" style="max-height: 65vh; overflow-y: auto; padding: 1.25rem; flex: 1;">
            <div id="matrix-profiles-list" style="display: flex; flex-direction: column; gap: 0.85rem;">
                <!-- Profiles rendered dynamically -->
            </div>
        </div>

        <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 0.5rem; padding: 1rem 1.75rem; border-top: 1px solid var(--zinc-100);">
            <button type="button" onclick="closeMatrixModal()" class="btn btn-secondary">
                Close
            </button>
        </div>
    </div>
</div>
