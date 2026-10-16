        <!-- 1. EXECUTIVE COMMAND HEADER & FAST ACTION BUTTONS CONFIGURATOR -->
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 1.25rem 1.5rem; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem; flex-wrap: wrap; gap: 0.5rem;">
                <div>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <h3 style="font-size: 1rem; font-weight: 700; color: #0f172a; margin: 0;">
                            Top Command Header &amp; Fast Action Buttons
                        </h3>
                        <span style="font-size: 0.6875rem; font-weight: 700; background: #e0e7ff; color: #4338ca; padding: 0.15rem 0.5rem; border-radius: 9999px; border: 1px solid #c7d2fe;">
                            Hero Command Banner
                        </span>
                    </div>
                    <p style="font-size: 0.75rem; color: #64748b; margin: 0.2rem 0 0 0;">
                        Customize the fast action buttons shown at the top of the Home Dashboard. Replace any action (e.g. replace <em>+ Log Call</em> with <em>+ New Opportunity</em>), edit labels, reorder, or customize button styles.
                    </p>
                </div>

                <button
                    type="button"
                    onclick="handleAddHeaderAction()"
                    style="background: #f8fafc; border: 1px solid #cbd5e1; color: #334155; border-radius: 8px; padding: 0.4rem 0.85rem; font-size: 0.75rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 5px;"
                >
                    <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
                    <span>Add Action Button</span>
                </button>
            </div>

            <!-- Visual Preview Pill of Header Buttons -->
            <div style="padding: 0.75rem 1rem; background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 1rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.75rem; color: #64748b; font-weight: 600;">
                    <span>Live Visual Preview:</span>
                </div>
                <div id="preview-header-actions-cluster" style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                    <!-- Injected dynamically -->
                </div>
            </div>

            <!-- Fast Action Buttons List Editor -->
            <div id="header-actions-editor-list" style="display: flex; flex-direction: column; gap: 0.625rem;">
                <!-- Injected dynamically -->
            </div>
        </div>

