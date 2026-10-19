/**
 * DealSync Enterprise CRM — Lead 360 Detail View Controller
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

    function getLeadId() {
        const el = document.getElementById('edit-lead-id');
        if (el && el.value) return el.value;
        if (window.__LEAD_DETAIL_DATA__ && window.__LEAD_DETAIL_DATA__.lead) {
            return window.__LEAD_DETAIL_DATA__.lead.id;
        }
        const parts = window.location.pathname.split('/');
        return parts[parts.length - 1];
    }

    // Modal Triggers
    window.openDetailEditModal = function () {
        const modal = document.getElementById('detailEditModal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            if (window.lucide) lucide.createIcons();
        }
    };

    window.closeDetailEditModal = function () {
        const modal = document.getElementById('detailEditModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    };

    window.openDetailConvertModal = function () {
        const modal = document.getElementById('detailConvertModal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            if (window.lucide) lucide.createIcons();
        }
    };

    window.closeDetailConvertModal = function () {
        const modal = document.getElementById('detailConvertModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    };

    window.openDetailTransferModal = function () {
        const modal = document.getElementById('detailTransferModal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            if (window.lucide) lucide.createIcons();
        }
    };

    window.closeDetailTransferModal = function () {
        const modal = document.getElementById('detailTransferModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    };

    // Delete Current Lead
    window.deleteCurrentLead = async function () {
        const leadId = getLeadId();
        if (!confirm('Are you sure you want to permanently delete this lead? This action cannot be undone.')) {
            return;
        }

        try {
            const res = await fetch(`/api/leads/${leadId}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                }
            });
            const data = await res.json();
            if (res.ok && data.success) {
                showNotification(data.message || 'Lead deleted', 'success');
                if (typeof window.loadSpaPage === 'function') {
                    window.loadSpaPage('/leads');
                } else {
                    window.location.href = '/leads';
                }
            } else {
                showNotification(data.message || 'Failed to delete lead', 'error');
            }
        } catch (_) {
            showNotification('Error deleting lead', 'error');
        }
    };

    // Handle Edit Form Submission
    async function handleDetailEditSubmit(e) {
        e.preventDefault();
        const form = e.target;
        const btn = form.querySelector('button[type="submit"]');
        if (btn) btn.disabled = true;

        try {
            const leadId = getLeadId();
            const formData = new FormData(form);

            const payload = {
                first_name: formData.get('first_name'),
                last_name: formData.get('last_name'),
                company: formData.get('company'),
                title: formData.get('title'),
                email: formData.get('email'),
                phone: formData.get('phone'),
                status: formData.get('status'),
                source: formData.get('source'),
                score: formData.get('score') ? parseInt(formData.get('score'), 10) : 50,
                rating: formData.get('rating'),
                notes: formData.get('notes'),
                custom_fields: {},
            };

            form.querySelectorAll('[data-custom-field]').forEach(el => {
                const fName = el.getAttribute('data-custom-field');
                if (fName && !el.disabled) {
                    payload.custom_fields[fName] = el.value;
                }
            });

            const res = await fetch(`/api/leads/${leadId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify(payload)
            });

            const data = await res.json();
            if (res.ok && data.success) {
                showNotification(data.message || 'Lead updated successfully', 'success');
                window.closeDetailEditModal();
                // Reload the page view via SPA navigation or reload
                if (typeof window.loadSpaPage === 'function') {
                    window.loadSpaPage(`/leads/${leadId}`);
                } else {
                    window.location.reload();
                }
            } else {
                showNotification(data.message || 'Failed to update lead', 'error');
            }
        } catch (_) {
            showNotification('Error updating lead details', 'error');
        } finally {
            if (btn) btn.disabled = false;
        }
    }

    // Handle Convert Form Submission
    async function handleDetailConvertSubmit(e) {
        e.preventDefault();
        const form = e.target;
        const btn = form.querySelector('button[type="submit"]');
        if (btn) btn.disabled = true;

        try {
            const leadId = getLeadId();
            const createOpp = document.getElementById('detail-chk-create-opp')?.checked || false;
            const oppName = document.getElementById('detail-input-opp-name')?.value;
            const oppAmount = document.getElementById('detail-input-opp-amount')?.value;

            const res = await fetch(`/api/leads/${leadId}/convert`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify({
                    create_opportunity: createOpp,
                    opportunity_name: oppName,
                    opportunity_amount: oppAmount ? parseFloat(oppAmount) : 50000,
                })
            });

            const data = await res.json();
            if (res.ok && data.success) {
                showNotification(data.message || 'Lead converted successfully!', 'success');
                window.closeDetailConvertModal();
                if (typeof window.loadSpaPage === 'function') {
                    window.loadSpaPage(`/leads/${leadId}`);
                } else {
                    window.location.reload();
                }
            } else {
                showNotification(data.message || 'Conversion failed', 'error');
            }
        } catch (_) {
            showNotification('Error converting lead', 'error');
        } finally {
            if (btn) btn.disabled = false;
        }
    }

    // Handle Transfer Form Submission
    async function handleDetailTransferSubmit(e) {
        e.preventDefault();
        const form = e.target;
        const btn = form.querySelector('button[type="submit"]');
        if (btn) btn.disabled = true;

        try {
            const leadId = getLeadId();
            const targetUserId = document.getElementById('detailTransferUserSelect')?.value;

            const res = await fetch(`/api/leads/${leadId}/transfer`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify({ targetUserId })
            });

            const data = await res.json();
            if (res.ok && data.success) {
                showNotification(data.message || 'Ownership transferred', 'success');
                window.closeDetailTransferModal();
                if (typeof window.loadSpaPage === 'function') {
                    window.loadSpaPage(`/leads/${leadId}`);
                } else {
                    window.location.reload();
                }
            } else {
                showNotification(data.message || 'Transfer failed', 'error');
            }
        } catch (_) {
            showNotification('Error transferring lead', 'error');
        } finally {
            if (btn) btn.disabled = false;
        }
    }

    window.initLeadDetailView = function () {
        const root = document.getElementById('lead-show-page-root');
        if (!root) return;

        // Bind Forms
        const editForm = document.getElementById('detailEditForm');
        if (editForm) {
            editForm.removeEventListener('submit', handleDetailEditSubmit);
            editForm.addEventListener('submit', handleDetailEditSubmit);
        }

        const convertForm = document.getElementById('detailConvertForm');
        if (convertForm) {
            convertForm.removeEventListener('submit', handleDetailConvertSubmit);
            convertForm.addEventListener('submit', handleDetailConvertSubmit);
        }

        const transferForm = document.getElementById('detailTransferForm');
        if (transferForm) {
            transferForm.removeEventListener('submit', handleDetailTransferSubmit);
            transferForm.addEventListener('submit', handleDetailTransferSubmit);
        }

        if (window.lucide) {
            lucide.createIcons();
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', window.initLeadDetailView);
    } else {
        window.initLeadDetailView();
    }
})();
