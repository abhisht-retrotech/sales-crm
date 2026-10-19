/**
 * DealSync Enterprise CRM — Contact 360 Detail View Controller
 * Pure Tailwind CSS styling with dynamic custom fields & field-level security permissions
 */

(function () {
    'use strict';

    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function showNotification(message, type = 'success') {
        if (typeof window.showToast === 'function') {
            window.showToast(message, type);
        } else if (typeof window.showSpaToast === 'function') {
            window.showSpaToast(message, type);
        } else {
            console.log(`[${type.toUpperCase()}] ${message}`);
        }
    }

    function getContactId() {
        const el = document.getElementById('edit-contact-id');
        if (el && el.value) return el.value;
        if (window.__CONTACT_DETAIL_DATA__ && window.__CONTACT_DETAIL_DATA__.contact) {
            return window.__CONTACT_DETAIL_DATA__.contact.id;
        }
        const parts = window.location.pathname.split('/');
        return parts[parts.length - 1];
    }

    // Segmented Tab Switching
    window.switchContactTab = function (tabId) {
        const tabs = ['overview', 'sales', 'purchases'];

        tabs.forEach(t => {
            const btn = document.getElementById(`contact-tab-btn-${t}`);
            const content = document.getElementById(`contact-tab-content-${t}`);

            if (t === tabId) {
                if (btn) {
                    btn.className = 'px-4 py-1.5 text-xs font-semibold rounded-lg bg-white dark:bg-zinc-700 text-zinc-950 dark:text-zinc-100 shadow-xs cursor-pointer whitespace-nowrap';
                }
                if (content) content.classList.remove('hidden');
            } else {
                if (btn) {
                    btn.className = 'px-4 py-1.5 text-xs font-medium rounded-lg text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-200 cursor-pointer whitespace-nowrap';
                }
                if (content) content.classList.add('hidden');
            }
        });

        if (window.lucide) lucide.createIcons();
    };

    // Modal Triggers
    window.openContactDetailEditModal = function () {
        const modal = document.getElementById('contact-detail-edit-modal');
        if (modal) {
            modal.classList.remove('hidden');
            if (window.lucide) lucide.createIcons();
        }
    };

    window.closeContactDetailEditModal = function () {
        const modal = document.getElementById('contact-detail-edit-modal');
        if (modal) modal.classList.add('hidden');
    };

    window.openContactDetailTransferModal = function () {
        const modal = document.getElementById('contact-detail-transfer-modal');
        if (modal) {
            modal.classList.remove('hidden');
            if (window.lucide) lucide.createIcons();
        }
    };

    window.closeContactDetailTransferModal = function () {
        const modal = document.getElementById('contact-detail-transfer-modal');
        if (modal) modal.classList.add('hidden');
    };

    // Submit Edit
    window.submitContactDetailEdit = async function (e) {
        e.preventDefault();
        const contactId = getContactId();

        const payload = {
            first_name: document.getElementById('detail-field-first_name')?.value,
            last_name: document.getElementById('detail-field-last_name')?.value,
            email: document.getElementById('detail-field-email')?.value,
            phone: document.getElementById('detail-field-phone')?.value,
            title: document.getElementById('detail-field-title')?.value,
            department: document.getElementById('detail-field-department')?.value,
            account_id: document.getElementById('detail-field-account_id')?.value,
            contact_role: document.getElementById('detail-field-role')?.value,
            notes: document.getElementById('detail-field-notes')?.value,
            custom_fields: {},
        };

        const cfInputs = document.querySelectorAll('.contact-detail-custom-field-input');
        cfInputs.forEach(input => {
            const fName = input.getAttribute('data-cf-name');
            if (fName && !input.disabled) {
                payload.custom_fields[fName] = input.value;
            }
        });

        try {
            const res = await fetch(`/api/contacts/${contactId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(payload)
            });

            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Failed to update contact');

            showNotification(data.message || 'Contact updated successfully!', 'success');
            window.closeContactDetailEditModal();

            if (typeof window.loadSpaPage === 'function') {
                window.loadSpaPage(`/contacts/${contactId}`, false);
            } else {
                window.location.reload();
            }
        } catch (err) {
            showNotification(err.message, 'error');
        }
    };

    // Submit Transfer
    window.submitContactDetailTransfer = async function (e) {
        e.preventDefault();
        const contactId = getContactId();
        const select = document.getElementById('detail-transfer-user-select');
        const userId = select ? select.value : null;

        if (!userId) {
            showNotification('Please select a team member', 'error');
            return;
        }

        try {
            const res = await fetch(`/api/contacts/${contactId}/transfer`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ assigned_to: userId })
            });

            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Transfer failed');

            showNotification(data.message || 'Contact reassigned!', 'success');
            window.closeContactDetailTransferModal();

            if (typeof window.loadSpaPage === 'function') {
                window.loadSpaPage(`/contacts/${contactId}`, false);
            } else {
                window.location.reload();
            }
        } catch (err) {
            showNotification(err.message, 'error');
        }
    };

    // Delete Contact
    window.deleteCurrentContact = async function () {
        const contactId = getContactId();
        if (!confirm('Are you sure you want to delete this contact? This action can be reviewed by an administrator.')) {
            return;
        }

        try {
            const res = await fetch(`/api/contacts/${contactId}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Failed to delete');

            showNotification(data.message || 'Contact deleted successfully.', 'success');

            if (typeof window.loadSpaPage === 'function') {
                window.loadSpaPage('/contacts', true);
            } else {
                window.location.href = '/contacts';
            }
        } catch (err) {
            showNotification(err.message, 'error');
        }
    };

    // Module Initializer
    window.initContactDetailView = function () {
        const root = document.getElementById('contact-show-page-root');
        if (!root) return;
        if (window.lucide) lucide.createIcons();
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', window.initContactDetailView);
    } else {
        window.initContactDetailView();
    }
})();
