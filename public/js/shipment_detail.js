/**
 * DealSync Enterprise CRM — Shipping & Freight Logistics 360 Detail View Controller
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

    function getShipmentId() {
        if (window.__SHIPMENT_DETAIL_DATA__ && window.__SHIPMENT_DETAIL_DATA__.shipment) {
            return window.__SHIPMENT_DETAIL_DATA__.shipment.id;
        }
        const parts = window.location.pathname.split('/');
        return parts[parts.length - 1];
    }

    // Segmented Tab Switching
    window.switchShipmentTab = function (tabId) {
        const tabs = ['overview', 'logistics', 'customfields', 'linked'];

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

    // Fast Status Advancement Shortcut Action
    window.advanceCurrentShipmentStatus = async function (nextStatus) {
        const shipmentId = getShipmentId();
        try {
            const res = await fetch(`/api/shipping/${shipmentId}/status`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify({ status: nextStatus }),
            });
            const data = await res.json();
            if (data.success) {
                showNotification(data.message || `Consignment advanced to ${nextStatus}!`);
                if (typeof window.loadSpaPage === 'function') {
                    window.loadSpaPage(`/shipping/${shipmentId}`, false);
                } else {
                    window.location.reload();
                }
            } else {
                showNotification(data.message || 'Failed to update consignment status', 'error');
            }
        } catch (err) {
            console.error('Error advancing shipment status:', err);
            showNotification('Error updating logistics status', 'error');
        }
    };

    // Open External Carrier Tracking Portal
    window.openCarrierPortal = function (carrier, tracking) {
        if (!tracking) {
            showNotification('No tracking number available', 'warning');
            return;
        }

        carrier = (carrier || '').toLowerCase();
        let targetUrl = '';

        if (carrier.includes('fedex')) {
            targetUrl = `https://www.fedex.com/fedextrack/?trknbr=${encodeURIComponent(tracking)}`;
        } else if (carrier.includes('ups')) {
            targetUrl = `https://www.ups.com/track?tracknum=${encodeURIComponent(tracking)}`;
        } else if (carrier.includes('dhl')) {
            targetUrl = `https://www.dhl.com/en/express/tracking.html?AWB=${encodeURIComponent(tracking)}`;
        } else if (carrier.includes('maersk')) {
            targetUrl = `https://www.maersk.com/tracking/${encodeURIComponent(tracking)}`;
        } else {
            targetUrl = `https://www.google.com/search?q=${encodeURIComponent(carrier + ' tracking ' + tracking)}`;
        }

        window.open(targetUrl, '_blank', 'noopener,noreferrer');
    };

    // Edit Modal Management
    window.openEditCurrentShipmentModal = function () {
        const modal = document.getElementById('edit-shipment-modal');
        if (modal) {
            modal.classList.remove('hidden');
            if (window.lucide) lucide.createIcons();
        }
    };

    window.closeEditCurrentShipmentModal = function () {
        const modal = document.getElementById('edit-shipment-modal');
        if (modal) {
            modal.classList.add('hidden');
        }
    };

    // Save Updated Shipment
    window.saveCurrentShipment = async function (e) {
        e.preventDefault();
        const shipmentId = getShipmentId();
        const saveBtn = document.getElementById('btn-save-current-shipment');

        if (saveBtn) {
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span class="inline-block animate-spin mr-1">↻</span> Saving...';
        }

        try {
            const payload = {
                carrier: document.getElementById('edit-ship-carrier')?.value || '',
                tracking_number: document.getElementById('edit-ship-tracking')?.value || '',
                type: document.getElementById('edit-ship-type')?.value || 'Outbound',
                status: document.getElementById('edit-ship-status')?.value || 'Pending',
                dispatch_date: document.getElementById('edit-ship-dispatch-date')?.value || null,
                estimated_delivery_date: document.getElementById('edit-ship-delivery-date')?.value || null,
                shipping_origin: document.getElementById('edit-ship-origin')?.value || null,
                destination_address: document.getElementById('edit-ship-destination')?.value || null,
                assigned_to: document.getElementById('edit-ship-assigned')?.value || null,
                notes: document.getElementById('edit-ship-notes')?.value || null,
                custom_data: {},
            };

            // Collect dynamic custom fields
            document.querySelectorAll('[data-cf-key]').forEach(el => {
                const key = el.getAttribute('data-cf-key');
                if (!el.disabled && !el.readOnly) {
                    if (el.tagName === 'SELECT' && (el.value === '1' || el.value === '0')) {
                        payload.custom_data[key] = (el.value === '1');
                    } else if (el.type === 'number') {
                        payload.custom_data[key] = el.value !== '' ? parseFloat(el.value) : null;
                    } else {
                        payload.custom_data[key] = el.value;
                    }
                }
            });

            const res = await fetch(`/api/shipping/${shipmentId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify(payload),
            });

            const data = await res.json();
            if (data.success) {
                showNotification(data.message || 'Shipment details successfully updated!');
                window.closeEditCurrentShipmentModal();
                if (typeof window.loadSpaPage === 'function') {
                    window.loadSpaPage(`/shipping/${shipmentId}`, false);
                } else {
                    window.location.reload();
                }
            } else {
                showNotification(data.message || 'Failed to update shipment', 'error');
            }
        } catch (err) {
            console.error('Error saving shipment:', err);
            showNotification('Error saving consignment changes', 'error');
        } finally {
            if (saveBtn) {
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i data-lucide="check" class="w-4 h-4"></i><span>Save Changes</span>';
                if (window.lucide) lucide.createIcons();
            }
        }
    };

    // Delete Current Shipment
    window.deleteCurrentShipment = async function () {
        const shipmentId = getShipmentId();
        if (!confirm('Are you sure you want to delete this freight consignment record? This action cannot be undone.')) {
            return;
        }

        try {
            const res = await fetch(`/api/shipping/${shipmentId}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
            });
            const data = await res.json();
            if (data.success) {
                showNotification(data.message || 'Shipment deleted.');
                if (typeof window.loadSpaPage === 'function') {
                    window.loadSpaPage('/shipping', true);
                } else {
                    window.location.href = '/shipping';
                }
            } else {
                showNotification(data.message || 'Failed to delete shipment', 'error');
            }
        } catch (err) {
            console.error('Error deleting shipment:', err);
            showNotification('Error deleting freight record', 'error');
        }
    };

    // Detail View Initializer
    window.initShipmentDetailView = function () {
        const root = document.getElementById('shipment-show-page-root');
        if (!root) return;
        if (window.lucide) lucide.createIcons();
    };

    document.addEventListener('DOMContentLoaded', () => {
        window.initShipmentDetailView();
    });

    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        window.initShipmentDetailView();
    }
})();
