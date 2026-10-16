<!-- =========================================================================
     MODAL 1: WIDGET CATALOG LIBRARY (17 Power BI Types with Visuals)
     ========================================================================= -->
<div id="modal-widget-library" class="modal-overlay" style="display: none;" onclick="closeWidgetLibraryModal()">
    <div class="modal-content" style="max-width: 960px; width: 100%; max-height: 90vh; display: flex; flex-direction: column; overflow: hidden; margin: auto;" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div>
                <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--zinc-900); margin: 0;">
                    Widget &amp; Visualization Library (16-Column Canvas)
                </h3>
                <p style="font-size: 0.75rem; color: var(--zinc-500); margin-top: 0.2rem; margin-bottom: 0;">
                    Choose from 17 Power BI &amp; Tableau-grade chart visualizations. Adding to: <strong id="library-target-section-title">Executive Revenue &amp; Quota Attainment</strong>.
                </p>
            </div>
            <button type="button" onclick="closeWidgetLibraryModal()" class="btn btn-ghost btn-sm">
                <i data-lucide="x" style="width: 16px; height: 16px;"></i>
            </button>
        </div>

        <!-- Category Filter Pills -->
        <div style="display: flex; gap: 0.4rem; padding: 0.75rem 1.5rem; border-bottom: 1px solid var(--zinc-100); flex-wrap: wrap; background: var(--zinc-50);">
            <button type="button" class="lib-cat-btn" data-cat="All" onclick="filterCatalog('All')" style="padding: 0.3rem 0.75rem; border-radius: var(--radius-full); font-size: 0.75rem; font-weight: 700; background: #6366f1; color: #ffffff; border: 1px solid var(--zinc-200); cursor: pointer;">All (17)</button>
            <button type="button" class="lib-cat-btn" data-cat="Metrics & Cards" onclick="filterCatalog('Metrics & Cards')" style="padding: 0.3rem 0.75rem; border-radius: var(--radius-full); font-size: 0.75rem; font-weight: 500; background: #ffffff; color: var(--zinc-600); border: 1px solid var(--zinc-200); cursor: pointer;">Metrics &amp; Cards</button>
            <button type="button" class="lib-cat-btn" data-cat="Standard Charts" onclick="filterCatalog('Standard Charts')" style="padding: 0.3rem 0.75rem; border-radius: var(--radius-full); font-size: 0.75rem; font-weight: 500; background: #ffffff; color: var(--zinc-600); border: 1px solid var(--zinc-200); cursor: pointer;">Standard Charts</button>
            <button type="button" class="lib-cat-btn" data-cat="Advanced & Geospatial" onclick="filterCatalog('Advanced & Geospatial')" style="padding: 0.3rem 0.75rem; border-radius: var(--radius-full); font-size: 0.75rem; font-weight: 500; background: #ffffff; color: var(--zinc-600); border: 1px solid var(--zinc-200); cursor: pointer;">Advanced &amp; Geospatial</button>
            <button type="button" class="lib-cat-btn" data-cat="Grids & Filters" onclick="filterCatalog('Grids & Filters')" style="padding: 0.3rem 0.75rem; border-radius: var(--radius-full); font-size: 0.75rem; font-weight: 500; background: #ffffff; color: var(--zinc-600); border: 1px solid var(--zinc-200); cursor: pointer;">Grids &amp; Filters</button>
        </div>

        <!-- Catalog Grid -->
        <div class="modal-body" style="max-height: 60vh; overflow-y: auto; padding: 1.25rem 1.5rem; flex: 1;">
            <div id="catalog-widgets-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 1rem;">
                <!-- 17 Widgets rendered dynamically -->
            </div>
        </div>

        <div class="modal-footer" style="display: flex; justify-content: flex-end; padding: 1rem 1.75rem; border-top: 1px solid var(--zinc-100);">
            <button type="button" onclick="closeWidgetLibraryModal()" class="btn btn-secondary">
                Close
            </button>
        </div>
    </div>
</div>
