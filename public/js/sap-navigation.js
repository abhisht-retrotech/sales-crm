/**
 * DealSync SPA Seamless Router & Browser History (pushState & popstate)
 * Extracted from app.blade.php
 *
 * Handles:
 * - Dynamic view swapping without page reload
 * - Browser Back / Forward navigation (popstate event)
 * - URL changes via history.pushState
 * - Dynamic script re-execution and component re-initialization
 * - Top loading progress bar indicator
 * - Sidebar active navigation indicator synchronization
 */

/**
 * SPA Navigation & Route Registry
 */
const NAVIGATION_REGISTRY = [
    {
        id: 'dashboard',
        title: 'Home Dashboard',
        label: 'Dashboard',
        route: '/dashboard',
        path: '/dashboard',
        icon: 'layout-dashboard',
        order: 1,
        permissions: ['view_dashboard']
    },
    {
        id: 'leads',
        title: 'Leads Directory',
        label: 'Leads',
        route: '/leads',
        path: '/leads',
        icon: 'user-check',
        order: 2,
        permissions: ['view_leads']
    },
    {
        id: 'opportunities',
        title: 'Deals & Opportunities',
        label: 'Opportunities',
        route: '/opportunities',
        path: '/opportunities',
        icon: 'briefcase',
        order: 3,
        permissions: ['view_opportunities']
    },
    {
        id: 'accounts',
        title: 'Accounts & Organizations',
        label: 'Accounts',
        route: '/accounts',
        path: '/accounts',
        icon: 'building-2',
        order: 4,
        permissions: ['view_accounts']
    },
    {
        id: 'contacts',
        title: 'Contacts Directory',
        label: 'Contacts',
        route: '/contacts',
        path: '/contacts',
        icon: 'users',
        order: 5,
        permissions: ['view_contacts']
    },
    {
        id: 'tasks',
        title: 'Tasks & Action Queue',
        label: 'Tasks',
        route: '/tasks',
        path: '/tasks',
        icon: 'check-square',
        order: 6,
        permissions: ['view_tasks']
    },
    {
        id: 'purchase_deals',
        title: 'Purchase Deals & Procurement',
        label: 'Purchase Deals',
        route: '/purchase-deals',
        path: '/purchase-deals',
        icon: 'shopping-bag',
        order: 7,
        permissions: ['view_purchase_deals']
    },
    {
        id: 'shipping',
        title: 'Shipping & Freight Logistics',
        label: 'Shipping & Freight',
        route: '/shipping',
        path: '/shipping',
        icon: 'truck',
        order: 8,
        permissions: ['view_shipping']
    },
    {
        id: 'documents',
        title: 'Billing & Ledger Documents',
        label: 'Billing & Ledger',
        route: '/documents',
        path: '/documents',
        icon: 'file-spreadsheet',
        order: 9,
        permissions: ['view_documents']
    },
    {
        id: 'payments',
        title: 'Invoicing & Settlement Ledger',
        label: 'Invoices & AR/AP',
        route: '/payments',
        path: '/payments',
        icon: 'credit-card',
        order: 10,
        permissions: ['view_payments']
    },
    {
        id: 'wires',
        title: 'Wire Received Workbench',
        label: 'Wire Intake & Reconcile',
        route: '/wires',
        path: '/wires',
        icon: 'landmark',
        order: 11,
        permissions: ['view_wires']
    },
    {
        id: 'company_profile',
        title: 'Company Profile & Branding',
        label: 'Company Profile',
        route: '/admin/company-profile',
        path: '/admin/company-profile',
        icon: 'building-2',
        order: 10,
        permissions: ['admin', 'manage_company_profile']
    },
    {
        id: 'users_directory',
        title: 'Users & Team Directory',
        label: 'Users Directory',
        route: '/admin/users',
        path: '/admin/users',
        icon: 'users',
        order: 11,
        permissions: ['admin', 'manage_users']
    },
    {
        id: 'roles_permissions',
        title: 'Roles & Profiles Studio',
        label: 'Roles & Profiles',
        route: '/admin/roles-profiles',
        path: '/admin/roles-profiles',
        icon: 'shield-check',
        order: 12,
        permissions: ['admin', 'manage_roles']
    },
    {
        id: 'page_layouts',
        title: 'Page Layouts Designer',
        label: 'Page Layouts Designer',
        route: '/admin/page-layouts',
        path: '/admin/page-layouts',
        icon: 'layout-template',
        order: 13,
        permissions: ['admin', 'manage_layouts']
    },
    {
        id: 'custom_buttons',
        title: 'Custom Buttons Studio',
        label: 'Custom Buttons Studio',
        route: '/admin/custom-buttons',
        path: '/admin/custom-buttons',
        icon: 'mouse-pointer-click',
        order: 14,
        permissions: ['admin', 'manage_buttons']
    },
    {
        id: 'dashboard_studio',
        title: 'Dashboard Studio',
        label: 'Dashboard Studio',
        route: '/admin/dashboard-studio',
        path: '/admin/dashboard-studio',
        icon: 'layout-dashboard',
        order: 15,
        permissions: ['admin', 'manage_dashboards']
    },
    {
        id: 'process_studio',
        title: 'ProcessStudio Flow Engine',
        label: 'ProcessStudio',
        route: '/process-studio',
        path: '/process-studio',
        icon: 'workflow',
        order: 16,
        permissions: ['admin', 'manage_processes']
    }
];

let isSpaNavigating = false;
let currentSpaUrl = typeof window !== 'undefined' ? window.location.href : '';


function showSpaProgress(percent) {
    const bar = document.getElementById('dealsync-spa-progress');
    if (!bar) return;
    bar.style.opacity = '1';
    bar.style.width = percent + '%';
}

function finishSpaProgress() {
    const bar = document.getElementById('dealsync-spa-progress');
    if (!bar) return;
    bar.style.width = '100%';
    setTimeout(() => {
        bar.style.opacity = '0';
        setTimeout(() => {
            bar.style.width = '0%';
        }, 200);
    }, 150);
}

async function loadSpaPage(url, push = true) {
    if (isSpaNavigating) return;

    const targetObj = new URL(url, window.location.origin);

    // If user clicked a link to the exact current page, skip re-fetching
    if (push) {
        const currentObj = new URL(currentSpaUrl, window.location.origin);
        if (currentObj.pathname === targetObj.pathname && currentObj.search === targetObj.search) {
            return;
        }
    }

    isSpaNavigating = true;
    showSpaProgress(30);

    try {
        const response = await fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-DealSync-SPA': 'true'
            }
        });

        if (!response.ok) {
            window.location.href = url;
            return;
        }

        showSpaProgress(70);
        const htmlText = await response.text();
        const parser = new DOMParser();
        const newDoc = parser.parseFromString(htmlText, 'text/html');

        // 1. Update Title
        if (newDoc.title) {
            document.title = newDoc.title;
        }

        // 2. Swap Main Workspace View
        const currentMain = document.querySelector('main.content-body') || document.querySelector('main');
        const newMain = newDoc.querySelector('main.content-body') || newDoc.querySelector('main');

        if (currentMain && newMain) {
            currentMain.innerHTML = newMain.innerHTML;
            if (newMain.className) {
                currentMain.className = newMain.className;
            }

            // Execute any <script> tags inside the new main view
            const scripts = Array.from(currentMain.querySelectorAll('script'));
            for (const oldScript of scripts) {
                const newScript = document.createElement('script');
                Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                newScript.appendChild(document.createTextNode(oldScript.innerHTML));
                oldScript.parentNode.replaceChild(newScript, oldScript);
            }
        } else {
            window.location.href = url;
            return;
        }

        // 3. Update Sidebar & Spacer if Mode Changed between CRM and Admin
        const currentSidebar = document.getElementById('dealsync-sidebar');
        const newSidebar = newDoc.getElementById('dealsync-sidebar');
        const currentSpacer = document.getElementById('dealsync-sidebar-spacer');
        const newSpacer = newDoc.getElementById('dealsync-sidebar-spacer');

        const wasAdmin = currentSidebar?.classList.contains('is-admin-mode');
        const isNowAdmin = newSidebar?.classList.contains('is-admin-mode');

        if (wasAdmin !== isNowAdmin && currentSidebar && newSidebar) {
            currentSidebar.className = newSidebar.className;
            currentSidebar.innerHTML = newSidebar.innerHTML;
            if (currentSpacer && newSpacer) {
                currentSpacer.className = newSpacer.className;
            }

            // Re-run admin sidebar initialization if transitioning to admin mode
            if (isNowAdmin && typeof window.initAdminSidebar === 'function') {
                window.initAdminSidebar();
            }
        }

        // 4. Update Header Badges (e.g. Admin Console indicator)
        const currentHeaderLeft = document.querySelector('header .flex.items-center.gap-2');
        const newHeaderLeft = newDoc.querySelector('header .flex.items-center.gap-2');
        if (currentHeaderLeft && newHeaderLeft) {
            currentHeaderLeft.innerHTML = newHeaderLeft.innerHTML;
        }

        // 4.1 Synchronize Dynamic Tenant CSS Variables & Styles
        const newTenantStyles = newDoc.getElementById('dealsync-tenant-root-styles');
        const curTenantStyles = document.getElementById('dealsync-tenant-root-styles');
        if (newTenantStyles && curTenantStyles) {
            curTenantStyles.textContent = newTenantStyles.textContent;
        }

        // 5. Update Sidebar Active State
        updateSidebarActiveStates(targetObj.href);

        // 6. Update Browser History State
        if (push) {
            window.history.pushState({ path: targetObj.href }, '', targetObj.href);
        }
        currentSpaUrl = targetObj.href;

        // 7. Re-initialize Dynamic Page Components
        if (window.lucide) {
            lucide.createIcons();
        }
        if (typeof initUniversalDataTables === 'function') {
            initUniversalDataTables();
        }
        if (typeof initPurchaseDealsModule === 'function') {
            initPurchaseDealsModule();
        }
        if (typeof initPurchaseDealDetailView === 'function') {
            initPurchaseDealDetailView();
        }
        if (typeof initShippingModule === 'function') {
            initShippingModule();
        } else if (document.getElementById('shipping-page-root')) {
            const shipScript = document.createElement('script');
            shipScript.src = '/js/shipping.js?v=1.0';
            shipScript.onload = () => {
                if (typeof initShippingModule === 'function') initShippingModule();
            };
            document.body.appendChild(shipScript);
        }
        if (typeof initShipmentDetailView === 'function') {
            initShipmentDetailView();
        } else if (document.getElementById('shipment-show-page-root')) {
            const shipDetailScript = document.createElement('script');
            shipDetailScript.src = '/js/shipment_detail.js?v=1.0';
            shipDetailScript.onload = () => {
                if (typeof initShipmentDetailView === 'function') initShipmentDetailView();
            };
            document.body.appendChild(shipDetailScript);
        }
        if (typeof initDocumentsModule === 'function') {
            initDocumentsModule();
        } else if (document.getElementById('documents-page-root')) {
            const docScript = document.createElement('script');
            docScript.src = '/js/documents.js?v=1.0';
            docScript.onload = () => {
                if (typeof initDocumentsModule === 'function') initDocumentsModule();
            };
            document.body.appendChild(docScript);
        }
        if (typeof initPaymentsModule === 'function') {
            initPaymentsModule();
        } else if (document.getElementById('payments-page-root')) {
            const payScript = document.createElement('script');
            payScript.src = '/js/payments.js?v=1.0';
            payScript.onload = () => {
                if (typeof initPaymentsModule === 'function') initPaymentsModule();
            };
            document.body.appendChild(payScript);
        }
        if (typeof initWiresModule === 'function') {
            initWiresModule();
        } else if (document.getElementById('wires-page-root')) {
            const wireScript = document.createElement('script');
            wireScript.src = '/js/wires.js?v=1.0';
            wireScript.onload = () => {
                if (typeof initWiresModule === 'function') initWiresModule();
            };
            document.body.appendChild(wireScript);
        }
        if (typeof initLeadsModule === 'function') {
            initLeadsModule();
        } else if (document.getElementById('leads-page-root')) {
            const leadScript = document.createElement('script');
            leadScript.src = '/js/leads.js?v=2.0';
            leadScript.onload = () => {
                if (typeof initLeadsModule === 'function') initLeadsModule();
            };
            document.body.appendChild(leadScript);
        }
        if (typeof initLeadDetailView === 'function') {
            initLeadDetailView();
        } else if (document.getElementById('lead-show-page-root')) {
            const leadDetailScript = document.createElement('script');
            leadDetailScript.src = '/js/lead_detail.js?v=2.0';
            leadDetailScript.onload = () => {
                if (typeof initLeadDetailView === 'function') initLeadDetailView();
            };
            document.body.appendChild(leadDetailScript);
        }
        if (typeof initUsersDirectory === 'function') {
            initUsersDirectory();
        }
        if (typeof initCompanyProfileStudio === 'function') {
            initCompanyProfileStudio();
        }
        if (typeof initRolesProfiles === 'function') {
            initRolesProfiles();
        }
        if (typeof initRecordRights === 'function') {
            initRecordRights();
        }
        if (typeof initFieldRights === 'function') {
            initFieldRights();
        }
        if (typeof initSharingRules === 'function') {
            initSharingRules();
        } else if (document.getElementById('srStudioRoot')) {
            const srScript = document.createElement('script');
            srScript.src = '/js/sharing-rules.js?v=2.0';
            srScript.onload = () => {
                if (typeof initSharingRules === 'function') initSharingRules();
            };
            document.body.appendChild(srScript);
        }

        if (typeof initObjectFields === 'function') {
            initObjectFields();
        } else if (document.getElementById('ofStudioRoot')) {
            const ofScript = document.createElement('script');
            ofScript.src = '/js/object-fields.js?v=2.0';
            ofScript.onload = () => {
                if (typeof initObjectFields === 'function') initObjectFields();
            };
            document.body.appendChild(ofScript);
        }

        if (typeof initCustomObjects === 'function') {
            initCustomObjects();
        } else if (document.getElementById('coStudioRoot')) {
            const coScript = document.createElement('script');
            coScript.src = '/js/custom-objects.js?v=2.0';
            coScript.onload = () => {
                if (typeof initCustomObjects === 'function') initCustomObjects();
            };
            document.body.appendChild(coScript);
        }

        if (typeof initPageLayouts === 'function') {
            initPageLayouts();
        } else if (document.getElementById('plStudioRoot')) {
            const plScript = document.createElement('script');
            plScript.src = '/js/page-layouts.js?v=2.0';
            plScript.onload = () => {
                if (typeof initPageLayouts === 'function') initPageLayouts();
            };
            document.body.appendChild(plScript);
        }

        if (typeof initCustomButtonsStudio === 'function') {
            initCustomButtonsStudio();
        } else if (document.getElementById('cbStudioRoot')) {
            const cbScript = document.createElement('script');
            cbScript.src = '/js/custom-buttons.js?v=2.0';
            cbScript.onload = () => {
                if (typeof initCustomButtonsStudio === 'function') initCustomButtonsStudio();
            };
            document.body.appendChild(cbScript);
        }

        if (typeof initDashboardStudio === 'function') {
            initDashboardStudio();
        } else if (document.getElementById('dsStudioRoot') || document.querySelector('.dashboard-grid')) {
            const dsScript = document.createElement('script');
            dsScript.src = '/js/dashboard-studio.js?v=2.0';
            dsScript.onload = () => {
                if (typeof initDashboardStudio === 'function') initDashboardStudio();
            };
            document.body.appendChild(dsScript);
        }

        if (typeof applyTenantCustomColors === 'function') {
            const rootStyle = getComputedStyle(document.documentElement);
            const curTableBg = rootStyle.getPropertyValue('--table-bg').trim();
            const curTableBorder = rootStyle.getPropertyValue('--table-border').trim();
            const curFontColor = rootStyle.getPropertyValue('--text-primary').trim();
            if (curTableBg || curTableBorder || curFontColor) {
                applyTenantCustomColors({
                    table_bg_color: curTableBg,
                    table_border_color: curTableBorder,
                    font_color: curFontColor
                });
            }
        }

        // Scroll window and workspace to top
        window.scrollTo({ top: 0, behavior: 'instant' });
        const mainWrapper = document.getElementById('main-content-wrapper');
        if (mainWrapper) mainWrapper.scrollTop = 0;

        showSpaProgress(100);
        finishSpaProgress();
    } catch (err) {
        console.error('SPA navigation error, falling back:', err);
        window.location.href = url;
    } finally {
        isSpaNavigating = false;
    }
}

function updateSidebarActiveStates(targetUrl) {
    const targetPath = new URL(targetUrl, window.location.origin).pathname;

    document.querySelectorAll('#dealsync-sidebar .sidebar-nav-btn').forEach(btn => {
        const href = btn.getAttribute('href');
        if (!href) return;
        const btnPath = new URL(href, window.location.origin).pathname;

        let isMatch = false;
        if (btnPath === '/' || btnPath === '/dashboard') {
            isMatch = (targetPath === '/' || targetPath === '/dashboard');
        } else {
            isMatch = (targetPath === btnPath || targetPath.startsWith(btnPath + '/'));
        }

        const icon = btn.querySelector('.sidebar-icon-slot i');
        const textSpan = btn.querySelector('.sidebar-slide-text > span');
        const rightWrap = btn.querySelector('.sidebar-slide-text > div');
        let chevron = rightWrap?.querySelector('[data-lucide="chevron-right"]');

        if (isMatch) {
            btn.classList.add('is-active');
            if (icon) {
                icon.classList.remove('text-zinc-500', 'dark:text-zinc-400', 'stroke-[1.8]');
                icon.classList.add('text-indigo-600', 'dark:text-indigo-400', 'stroke-[2.2]');
            }
            if (textSpan) {
                textSpan.classList.remove('font-medium');
                textSpan.classList.add('font-semibold');
            }
            if (!chevron && rightWrap) {
                chevron = document.createElement('i');
                chevron.setAttribute('data-lucide', 'chevron-right');
                chevron.className = 'w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400 stroke-[2.2]';
                rightWrap.appendChild(chevron);
            }
        } else {
            btn.classList.remove('is-active');
            if (icon) {
                icon.classList.remove('text-indigo-600', 'dark:text-indigo-400', 'stroke-[2.2]');
                icon.classList.add('text-zinc-500', 'dark:text-zinc-400', 'stroke-[1.8]');
            }
            if (textSpan) {
                textSpan.classList.remove('font-semibold');
                textSpan.classList.add('font-medium');
            }
            if (chevron) {
                chevron.remove();
            }
        }
    });

    // Admin Accordions highlight
    document.querySelectorAll('.admin-group-section').forEach(section => {
        const accordionBtn = section.querySelector('.sidebar-category-accordion');
        const hasActive = section.querySelector('.admin-nav-item.is-active') !== null;
        if (accordionBtn) {
            if (hasActive) {
                accordionBtn.classList.add('is-active-parent');
                const gId = section.getAttribute('data-group-id');
                const itemsDiv = document.getElementById('items-' + gId);
                if (itemsDiv) itemsDiv.style.display = 'flex';
                const chev = document.getElementById('chevron-' + gId);
                if (chev) chev.setAttribute('data-lucide', 'chevron-down');
            } else {
                accordionBtn.classList.remove('is-active-parent');
            }
        }
    });

    if (window.lucide) lucide.createIcons();
}

// Global SPA Click Interceptor
document.addEventListener('click', (e) => {
    const link = e.target.closest('a');
    if (!link) return;

    const href = link.getAttribute('href');
    if (!href) return;

    // Skip anchor tags, javascript calls, external targets, downloads
    if (href.startsWith('#') || href.startsWith('javascript:') || link.target === '_blank' || link.hasAttribute('download')) {
        return;
    }

    // Skip if modifier keys pressed
    if (e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) {
        return;
    }

    // Check if origin matches
    const targetUrl = new URL(link.href, window.location.origin);
    if (targetUrl.origin !== window.location.origin) {
        return;
    }

    // Skip login/logout/api
    if (targetUrl.pathname.includes('/logout') || targetUrl.pathname.includes('/login') || targetUrl.pathname.startsWith('/api/')) {
        return;
    }

    if (typeof closeUserDropdown === 'function') closeUserDropdown();

    e.preventDefault();
    loadSpaPage(targetUrl.href, true);
});

// Browser Back / Forward Handling
window.addEventListener('popstate', () => {
    loadSpaPage(window.location.href, false);
});

// Expose on window for programmatic calls and external triggers
if (typeof window !== 'undefined') {
    window.NAVIGATION_REGISTRY = NAVIGATION_REGISTRY;
    window.SPA_ROUTES = NAVIGATION_REGISTRY;
    window.loadSpaPage = loadSpaPage;
    window.updateSidebarActiveStates = updateSidebarActiveStates;
    window.showSpaProgress = showSpaProgress;
    window.finishSpaProgress = finishSpaProgress;
}

if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        NAVIGATION_REGISTRY,
        SPA_ROUTES: NAVIGATION_REGISTRY,
        loadSpaPage,
        updateSidebarActiveStates,
        showSpaProgress,
        finishSpaProgress
    };
}

