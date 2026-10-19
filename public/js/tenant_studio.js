/**
 * DealSync - Tenant Management Studio JavaScript (Alias for tenant-studio.js)
 */

(function () {
    // If tenant-studio.js is already loaded, window.initTenantStudio is available
    if (typeof window.initTenantStudio === 'function') {
        return;
    }

    // Otherwise load tenant-studio.js dynamically
    var script = document.createElement('script');
    script.src = (window.TENANT_STUDIO_ASSET_PATH || '/js/tenant-studio.js') + '?v=1.2';
    script.async = false;
    document.head.appendChild(script);
})();
