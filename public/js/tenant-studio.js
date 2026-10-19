/**
 * DealSync - Tenant Management Studio JavaScript
 * Matches React TenantManagementStudio.tsx logic: real-time search, logo upload/preview,
 * auto-slug generation, create/edit branding modal flows.
 */

(function (window, document) {
    'use strict';

    var config = window.TENANT_STUDIO_CONFIG || {
        baseUrl: '/admin/tenant-studio',
        csrfToken: ''
    };

    /**
     * Filter tenants table by search query (matches name or tenant ID)
     */
    function filterTenants() {
        var searchInput = document.getElementById('tenant-search-input');
        var q = (searchInput ? searchInput.value : '').toLowerCase().trim();

        var rows = document.querySelectorAll('#tenants-table-body .tenant-row');
        var visibleCount = 0;
        var totalRows = rows.length;

        rows.forEach(function (row) {
            var name = (row.getAttribute('data-name') || '').toLowerCase();
            var id = (row.getAttribute('data-id') || '').toLowerCase();

            var matches = !q || name.indexOf(q) !== -1 || id.indexOf(q) !== -1;
            if (matches) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        var countElem = document.getElementById('visible-tenant-count');
        if (countElem) {
            countElem.textContent = visibleCount;
        }

        var emptyRow = document.getElementById('no-filter-match-row');
        if (emptyRow) {
            emptyRow.style.display = (visibleCount === 0 && totalRows > 0) ? '' : 'none';
        }
    }

    /**
     * Auto-slugify company name into a suggested tenant ID (tenant-company-name)
     */
    function handleNameSlugInput(val) {
        val = val || '';
        var slug = val
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');

        var domainInput = document.getElementById('new-tenant-domain');
        var emailInput = document.getElementById('new-tenant-email');

        if (domainInput) {
            domainInput.value = slug ? ('tenant-' + slug) : '';
        }
        if (emailInput && slug && (!emailInput.value || emailInput.value.indexOf('@') === -1 || emailInput.value.startsWith('admin@'))) {
            emailInput.value = 'admin@' + slug + '.com';
        }
    }

    /**
     * Handle logo file upload and Base64 conversion
     */
    function handleLogoFileSelect(fileInput, mode) {
        var file = fileInput.files && fileInput.files[0];
        if (!file) return;

        if (!file.type.match(/^image\//)) {
            alert('Please select a valid image file (PNG, JPG, SVG, WebP).');
            return;
        }

        if (file.size > 2 * 1024 * 1024) {
            alert('Image file size must be less than 2 MB.');
            return;
        }

        var reader = new FileReader();
        reader.onload = function (e) {
            var base64 = e.target.result;
            setLogoPreview(mode, base64);
        };
        reader.readAsDataURL(file);
    }

    /**
     * Handle manual logo URL text input
     */
    function handleLogoUrlInput(url, mode) {
        if (!url || !url.trim()) {
            removeLogo(mode);
            return;
        }
        setLogoPreview(mode, url.trim());
    }

    /**
     * Update logo UI preview elements
     */
    function setLogoPreview(mode, url) {
        var hiddenInput = document.getElementById(mode === 'create' ? 'new-tenant-logo-url' : 'edit-tenant-logo-url');
        var previewBox = document.getElementById(mode === 'create' ? 'create-logo-preview-box' : 'edit-logo-preview-box');
        var placeholderBox = document.getElementById(mode === 'create' ? 'create-logo-placeholder-box' : 'edit-logo-placeholder-box');
        var previewImg = document.getElementById(mode === 'create' ? 'create-logo-img-preview' : 'edit-logo-img-preview');
        var urlInput = document.getElementById(mode === 'create' ? 'new-tenant-logo-url-input' : 'edit-tenant-logo-url-input');

        if (hiddenInput) hiddenInput.value = url;
        if (previewImg) previewImg.src = url;
        if (previewBox) previewBox.style.display = 'flex';
        if (placeholderBox) placeholderBox.style.display = 'none';

        if (urlInput && !url.startsWith('data:')) {
            urlInput.value = url;
        }
    }

    /**
     * Clear and remove logo
     */
    function removeLogo(mode) {
        var hiddenInput = document.getElementById(mode === 'create' ? 'new-tenant-logo-url' : 'edit-tenant-logo-url');
        var previewBox = document.getElementById(mode === 'create' ? 'create-logo-preview-box' : 'edit-logo-preview-box');
        var placeholderBox = document.getElementById(mode === 'create' ? 'create-logo-placeholder-box' : 'edit-logo-placeholder-box');
        var previewImg = document.getElementById(mode === 'create' ? 'create-logo-img-preview' : 'edit-logo-img-preview');
        var fileInput = document.getElementById(mode === 'create' ? 'create-logo-file-input' : 'edit-logo-file-input');
        var urlInput = document.getElementById(mode === 'create' ? 'new-tenant-logo-url-input' : 'edit-tenant-logo-url-input');

        if (hiddenInput) hiddenInput.value = '';
        if (previewImg) previewImg.src = '';
        if (fileInput) fileInput.value = '';
        if (urlInput) urlInput.value = '';
        if (previewBox) previewBox.style.display = 'none';
        if (placeholderBox) placeholderBox.style.display = 'flex';
    }

    /**
     * Create Tenant Modal
     */
    function openCreateTenantModal() {
        var modal = document.getElementById('create-tenant-modal');
        if (modal) {
            modal.classList.add('is-open');
            var nameInput = document.getElementById('new-tenant-name');
            if (nameInput) {
                setTimeout(function () {
                    nameInput.focus();
                }, 100);
            }
        }
    }

    function closeCreateTenantModal() {
        var modal = document.getElementById('create-tenant-modal');
        if (modal) modal.classList.remove('is-open');
    }

    /**
     * Edit Tenant Branding Modal
     */
    function openEditTenantModal(tenant) {
        var modal = document.getElementById('edit-tenant-modal');
        var form = document.getElementById('editTenantForm');
        var title = document.getElementById('edit-modal-title');
        var baseUrl = config.baseUrl || '/admin/tenant-studio';

        if (title) title.textContent = 'Edit Company Branding: ' + (tenant.name || '');
        if (form) form.action = baseUrl + '/' + tenant.id;

        var nameInput = document.getElementById('edit-tenant-name');
        var domainInput = document.getElementById('edit-tenant-domain');

        if (nameInput) nameInput.value = tenant.name || '';
        if (domainInput) domainInput.value = tenant.domain || '';

        if (tenant.logo_url) {
            setLogoPreview('edit', tenant.logo_url);
        } else {
            removeLogo('edit');
        }

        if (modal) modal.classList.add('is-open');
    }

    function closeEditTenantModal() {
        var modal = document.getElementById('edit-tenant-modal');
        if (modal) modal.classList.remove('is-open');
    }

    /**
     * Delete Tenant Modal
     */
    function openDeleteTenantModal(tenantId, tenantName, dbName) {
        var modal = document.getElementById('delete-tenant-modal');
        var form = document.getElementById('deleteTenantForm');
        var nameSpan = document.getElementById('delete-tenant-name-span');
        var dbSpan = document.getElementById('delete-tenant-db-span');
        var baseUrl = config.baseUrl || '/admin/tenant-studio';

        if (form) form.action = baseUrl + '/' + tenantId;
        if (nameSpan) nameSpan.textContent = tenantName || 'this tenant';
        if (dbSpan) dbSpan.textContent = dbName || '';

        if (modal) modal.classList.add('is-open');
    }

    function closeDeleteTenantModal() {
        var modal = document.getElementById('delete-tenant-modal');
        if (modal) modal.classList.remove('is-open');
    }

    function handleCreateTenantSubmit(e) {
        var btn = document.getElementById('submitCreateTenantBtn');
        var text = document.getElementById('submitCreateTenantText');
        if (btn) btn.disabled = true;
        if (text) {
            text.innerHTML = '<span class="spinner-border spinner-border-sm" style="display:inline-block; width:12px; height:12px; border:2px solid #fff; border-top-color:transparent; border-radius:50%; animation:spin 0.6s linear infinite; margin-right:6px;"></span> Provisioning...';
        }
    }

    function setupModalListeners() {
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' || e.keyCode === 27) {
                closeCreateTenantModal();
                closeEditTenantModal();
                closeDeleteTenantModal();
            }
        });

        document.querySelectorAll('.modal-overlay').forEach(function (overlay) {
            overlay.addEventListener('click', function (e) {
                if (e.target === overlay) {
                    overlay.classList.remove('is-open');
                }
            });
        });
    }

    function initTenantStudio() {
        setupModalListeners();
        filterTenants();

        if (window.lucide && typeof window.lucide.createIcons === 'function') {
            try {
                window.lucide.createIcons();
            } catch (e) {}
        }
    }

    // Expose on window
    window.filterTenants = filterTenants;
    window.handleNameSlugInput = handleNameSlugInput;
    window.handleLogoFileSelect = handleLogoFileSelect;
    window.handleLogoUrlInput = handleLogoUrlInput;
    window.setLogoPreview = setLogoPreview;
    window.removeLogo = removeLogo;
    window.openCreateTenantModal = openCreateTenantModal;
    window.closeCreateTenantModal = closeCreateTenantModal;
    window.openEditTenantModal = openEditTenantModal;
    window.closeEditTenantModal = closeEditTenantModal;
    window.openDeleteTenantModal = openDeleteTenantModal;
    window.closeDeleteTenantModal = closeDeleteTenantModal;
    window.handleCreateTenantSubmit = handleCreateTenantSubmit;
    window.initTenantStudio = initTenantStudio;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTenantStudio);
    } else {
        initTenantStudio();
    }
})(window, document);
