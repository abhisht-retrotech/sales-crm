/**
 * DealSync Enterprise CRM — Purchase Deal 360 Detail View Controller
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

    function getDealId() {
        if (window.__PURCHASE_DEAL_DETAIL_DATA__ && window.__PURCHASE_DEAL_DETAIL_DATA__.purchaseDeal) {
            return window.__PURCHASE_DEAL_DETAIL_DATA__.purchaseDeal.id;
        }
        const parts = window.location.pathname.split('/');
        return parts[parts.length - 1];
    }

    // Segmented Tab Switching
    window.switchPurchaseDealTab = function (tabId) {
        const tabs = ['overview', 'customfields', 'opportunities'];

        tabs.forEach(t => {
            const btn = document.getElementById(`tab-btn-${t}`);
            const pane = document.getElementById(`tab-pane-${t}`);

            if (t === tabId) {
                if (btn) {
                    btn.className = 'tab-btn active inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all cursor-pointer bg-white dark:bg-zinc-700 text-zinc-950 dark:text-white shadow-2xs';
                }
                if (pane) pane.classList.remove('hidden');
            } else {
                if (btn) {
                    btn.className = 'tab-btn inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-medium transition-all cursor-pointer text-zinc-600 dark:text-zinc-400 hover:text-zinc-950 dark:hover:text-white';
                }
                if (pane) pane.classList.add('hidden');
            }
        });

        if (window.lucide) lucide.createIcons();
    };

    // Stage Advancement Shortcut Action
    window.advanceCurrentPurchaseDealStage = async function (nextStage) {
        const dealId = getDealId();
        try {
            const res = await fetch(`/api/purchase-deals/${dealId}/stage`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify({ stage: nextStage }),
            });
            const data = await res.json();
            if (data.success) {
                showNotification(data.message || `Advanced to ${nextStage}!`);
                if (typeof window.loadSpaPage === 'function') {
                    window.loadSpaPage(`/purchase-deals/${dealId}`, false);
                } else {
                    window.location.reload();
                }
            } else {
                showNotification(data.message || 'Failed to update stage', 'error');
            }
        } catch (err) {
            console.error('Error advancing stage:', err);
            showNotification('Error advancing procurement stage', 'error');
        }
    };

    // Delete Current Purchase Deal
    window.deleteCurrentPurchaseDeal = async function () {
        const dealId = getDealId();
        if (!confirm('Are you sure you want to delete this purchase deal? This will cancel the supplier commitment.')) {
            return;
        }

        try {
            const res = await fetch(`/api/purchase-deals/${dealId}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
            });
            const data = await res.json();
            if (data.success) {
                showNotification(data.message || 'Purchase deal deleted.');
                if (typeof window.loadSpaPage === 'function') {
                    window.loadSpaPage('/purchase-deals', true);
                } else {
                    window.location.href = '/purchase-deals';
                }
            } else {
                showNotification(data.message || 'Failed to delete purchase deal', 'error');
            }
        } catch (err) {
            console.error('Error deleting deal:', err);
            showNotification('Error deleting purchase deal', 'error');
        }
    };

    // Open Edit Modal for Current Deal
    window.openEditCurrentDealModal = function () {
        const dealId = getDealId();
        if (typeof window.openPurchaseDealModal === 'function') {
            window.openPurchaseDealModal('edit', dealId);
        } else {
            window.location.href = `/purchase-deals`;
        }
    };

    // Detail View Initializer
    window.initPurchaseDealDetailView = function () {
        const root = document.getElementById('purchase-deal-show-page-root');
        if (!root) return;
        if (window.lucide) lucide.createIcons();
    };

    document.addEventListener('DOMContentLoaded', () => {
        window.initPurchaseDealDetailView();
    });

    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        window.initPurchaseDealDetailView();
    }
})();
