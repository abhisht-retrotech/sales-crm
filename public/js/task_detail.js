/**
 * DealSync Enterprise CRM — Task 360 Detail View Controller
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

    function getTaskId() {
        if (window.__TASK_DETAIL_DATA__ && window.__TASK_DETAIL_DATA__.task) {
            return window.__TASK_DETAIL_DATA__.task.id;
        }
        const parts = window.location.pathname.split('/');
        return parts[parts.length - 1];
    }

    // Segmented Tab Switching
    window.switchTaskDetailTab = function (tabId) {
        const tabs = ['details', 'context', 'schedule'];

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

    // Toggle Task Status (Completed <-> Open)
    window.toggleDetailTaskStatus = async function (newStatus) {
        const taskId = getTaskId();
        try {
            const res = await fetch(`/api/tasks/${taskId}/status`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify({ status: newStatus }),
            });
            const data = await res.json();
            if (data.success) {
                showNotification(data.message || `Task marked as ${newStatus}`);
                if (typeof window.loadSpaPage === 'function') {
                    window.loadSpaPage(`/tasks/${taskId}`, false);
                } else {
                    window.location.reload();
                }
            } else {
                showNotification(data.message || 'Failed to update status', 'error');
            }
        } catch (err) {
            console.error('Error toggling status:', err);
            showNotification('Error updating task status', 'error');
        }
    };

    // Delete Current Task
    window.deleteCurrentTask = async function () {
        const taskId = getTaskId();
        if (!confirm('Are you sure you want to delete this task? This action cannot be undone.')) {
            return;
        }

        try {
            const res = await fetch(`/api/tasks/${taskId}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
            });
            const data = await res.json();
            if (data.success) {
                showNotification(data.message || 'Task deleted successfully.');
                if (typeof window.loadSpaPage === 'function') {
                    window.loadSpaPage('/tasks', true);
                } else {
                    window.location.href = '/tasks';
                }
            } else {
                showNotification(data.message || 'Failed to delete task', 'error');
            }
        } catch (err) {
            console.error('Error deleting task:', err);
            showNotification('Error deleting task', 'error');
        }
    };

    // Edit Modal Trigger in Show Page
    window.openTaskDetailEditModal = function () {
        const taskId = getTaskId();
        if (typeof window.openTaskModal === 'function') {
            window.openTaskModal('edit', Number(taskId));
        } else {
            window.location.href = `/tasks?edit=${taskId}`;
        }
    };

    // Transfer Modal Trigger in Show Page
    window.openTaskDetailTransferModal = function () {
        const taskId = getTaskId();
        const newAssignee = prompt('Enter the User ID of the new team member to assign to:');
        if (!newAssignee) return;

        fetch(`/api/tasks/${taskId}/transfer`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
            },
            body: JSON.stringify({ assigned_to: Number(newAssignee) }),
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message || 'Task reassigned successfully');
                if (typeof window.loadSpaPage === 'function') {
                    window.loadSpaPage(`/tasks/${taskId}`, false);
                } else {
                    window.location.reload();
                }
            } else {
                showNotification(data.message || 'Transfer failed', 'error');
            }
        })
        .catch(err => {
            console.error('Transfer error:', err);
            showNotification('Error reassigning task', 'error');
        });
    };

    // Module Initializer
    window.initTaskDetailView = function () {
        const root = document.getElementById('task-show-page-root');
        if (!root) return;
        if (window.lucide) lucide.createIcons();
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', window.initTaskDetailView);
    } else {
        window.initTaskDetailView();
    }
})();
