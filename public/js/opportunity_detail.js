/**
 * DealSync Enterprise CRM — Opportunity 360 Detail View Controller
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

    function getOpportunityId() {
        if (window.__OPPORTUNITY_DETAIL_DATA__ && window.__OPPORTUNITY_DETAIL_DATA__.opportunity) {
            return window.__OPPORTUNITY_DETAIL_DATA__.opportunity.id;
        }
        const parts = window.location.pathname.split('/');
        return parts[parts.length - 1];
    }

    // Segmented Tab Switching
    window.switchOpportunityTab = function (tabId) {
        const tabs = ['overview', 'procurement', 'finance', 'tasks'];

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
    window.advanceCurrentOpportunityStage = async function (nextStage) {
        const oppId = getOpportunityId();
        try {
            const res = await fetch(`/api/opportunities/${oppId}`, {
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
                    window.loadSpaPage(`/opportunities/${oppId}`, false);
                } else {
                    window.location.reload();
                }
            } else {
                showNotification(data.message || 'Failed to update stage', 'error');
            }
        } catch (err) {
            console.error('Error advancing stage:', err);
            showNotification('Error advancing pipeline stage', 'error');
        }
    };

    // Delete Current Opportunity
    window.deleteCurrentOpportunity = async function () {
        const oppId = getOpportunityId();
        if (!confirm('Are you sure you want to delete this opportunity? This will remove it from the revenue pipeline.')) {
            return;
        }

        try {
            const res = await fetch(`/api/opportunities/${oppId}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
            });
            const data = await res.json();
            if (data.success) {
                showNotification(data.message || 'Opportunity deleted successfully.');
                if (typeof window.loadSpaPage === 'function') {
                    window.loadSpaPage('/opportunities', true);
                } else {
                    window.location.href = '/opportunities';
                }
            } else {
                showNotification(data.message || 'Failed to delete opportunity', 'error');
            }
        } catch (err) {
            console.error('Error deleting opportunity:', err);
            showNotification('Error deleting opportunity', 'error');
        }
    };

    // Edit Modal Trigger in Show Page
    window.openOpportunityDetailEditModal = function () {
        const oppId = getOpportunityId();
        if (typeof window.openOpportunityModal === 'function') {
            window.openOpportunityModal('edit', Number(oppId));
        } else {
            // Direct redirect to index with edit modal or reload
            window.location.href = `/opportunities?edit=${oppId}`;
        }
    };

    // Transfer Modal Trigger in Show Page
    window.openOpportunityDetailTransferModal = function () {
        const oppId = getOpportunityId();
        const newOwner = prompt('Enter the User ID of the new owner to transfer to:');
        if (!newOwner) return;

        fetch(`/api/opportunities/${oppId}/transfer`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
            },
            body: JSON.stringify({ assigned_to: Number(newOwner) }),
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message || 'Opportunity reassigned successfully');
                if (typeof window.loadSpaPage === 'function') {
                    window.loadSpaPage(`/opportunities/${oppId}`, false);
                } else {
                    window.location.reload();
                }
            } else {
                showNotification(data.message || 'Transfer failed', 'error');
            }
        })
        .catch(err => {
            console.error('Transfer error:', err);
            showNotification('Error reassigning opportunity', 'error');
        });
    };

    // Module Initializer
    window.initOpportunityDetailView = function () {
        const root = document.getElementById('opportunity-show-page-root');
        if (!root) return;
        if (window.lucide) lucide.createIcons();
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', window.initOpportunityDetailView);
    } else {
        window.initOpportunityDetailView();
    }
})();
