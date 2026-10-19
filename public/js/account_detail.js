/**
 * DealSync Enterprise CRM — Account 360 Detail View Controller
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

    function getAccountId() {
        const el = document.getElementById('edit-account-id');
        if (el && el.value) return el.value;
        if (window.__ACCOUNT_DETAIL_DATA__ && window.__ACCOUNT_DETAIL_DATA__.account) {
            return window.__ACCOUNT_DETAIL_DATA__.account.id;
        }
        const parts = window.location.pathname.split('/');
        return parts[parts.length - 1];
    }

    // Segmented Tab Switching
    window.switchDetailTab = function (tabId) {
        const tabs = ['overview', 'contacts', 'sales', 'purchases', 'shipping', 'finance'];

        tabs.forEach(t => {
            const btn = document.getElementById(`tab-btn-${t}`);
            const content = document.getElementById(`tab-content-${t}`);

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
    window.openAccountDetailEditModal = function () {
        const modal = document.getElementById('account-detail-edit-modal');
        if (modal) {
            modal.classList.remove('hidden');
            if (window.lucide) lucide.createIcons();
        }
    };

    window.closeAccountDetailEditModal = function () {
        const modal = document.getElementById('account-detail-edit-modal');
        if (modal) modal.classList.add('hidden');
    };

    window.openAccountDetailTransferModal = function () {
        const modal = document.getElementById('account-detail-transfer-modal');
        if (modal) {
            modal.classList.remove('hidden');
            if (window.lucide) lucide.createIcons();
        }
    };

    window.closeAccountDetailTransferModal = function () {
        const modal = document.getElementById('account-detail-transfer-modal');
        if (modal) modal.classList.add('hidden');
    };

    // Submit Edit Account
    window.submitAccountDetailEdit = async function (e) {
        e.preventDefault();
        const accountId = getAccountId();

        const payload = {
            name: document.getElementById('detail-field-name')?.value,
            type: document.getElementById('detail-field-type')?.value,
            industry: document.getElementById('detail-field-industry')?.value,
            website: document.getElementById('detail-field-website')?.value,
            phone: document.getElementById('detail-field-phone')?.value,
            credit_limit: document.getElementById('detail-field-credit_limit')?.value,
            annual_revenue: document.getElementById('detail-field-annual_revenue')?.value,
            payment_terms: document.getElementById('detail-field-payment_terms')?.value,
            assigned_to: document.getElementById('detail-field-assigned_to')?.value,
            billing_address: document.getElementById('detail-field-billing_address')?.value,
            shipping_address: document.getElementById('detail-field-shipping_address')?.value,
            notes: document.getElementById('detail-field-notes')?.value,
            custom_fields: {},
        };

        // Extract custom fields inputs
        const cfInputs = document.querySelectorAll('.detail-custom-field-input');
        cfInputs.forEach(input => {
            const fName = input.getAttribute('data-cf-name');
            if (fName && !input.disabled) {
                payload.custom_fields[fName] = input.value;
            }
        });

        try {
            const res = await fetch(`/api/accounts/${accountId}`, {
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
            if (!res.ok) throw new Error(data.message || 'Failed to update account');

            showNotification(data.message || 'Account updated successfully!', 'success');
            window.closeAccountDetailEditModal();

            // Reload SPA view
            if (typeof window.loadSpaPage === 'function') {
                window.loadSpaPage(`/accounts/${accountId}`, false);
            } else {
                window.location.reload();
            }
        } catch (err) {
            showNotification(err.message, 'error');
        }
    };

    // Submit Ownership Transfer
    window.submitAccountDetailTransfer = async function (e) {
        e.preventDefault();
        const accountId = getAccountId();
        const select = document.getElementById('detail-transfer-user-select');
        const userId = select ? select.value : null;

        if (!userId) {
            showNotification('Please select a team member', 'error');
            return;
        }

        try {
            const res = await fetch(`/api/accounts/${accountId}/transfer`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ owner_id: userId })
            });

            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Transfer failed');

            showNotification(data.message || 'Ownership transferred!', 'success');
            window.closeAccountDetailTransferModal();

            if (typeof window.loadSpaPage === 'function') {
                window.loadSpaPage(`/accounts/${accountId}`, false);
            } else {
                window.location.reload();
            }
        } catch (err) {
            showNotification(err.message, 'error');
        }
    };

    // Delete Current Account
    window.deleteCurrentAccount = async function () {
        const accountId = getAccountId();
        if (!confirm('Are you sure you want to delete this organization account? This action can be reviewed by an administrator.')) {
            return;
        }

        try {
            const res = await fetch(`/api/accounts/${accountId}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Failed to delete');

            showNotification(data.message || 'Account deleted successfully.', 'success');

            if (typeof window.loadSpaPage === 'function') {
                window.loadSpaPage('/accounts', true);
            } else {
                window.location.href = '/accounts';
            }
        } catch (err) {
            showNotification(err.message, 'error');
        }
    };

    // Module Initializer
    window.initAccountDetailView = function () {
        const root = document.getElementById('account-show-page-root');
        if (!root) return;
        if (window.lucide) lucide.createIcons();
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', window.initAccountDetailView);
    } else {
        window.initAccountDetailView();
    }
})();
