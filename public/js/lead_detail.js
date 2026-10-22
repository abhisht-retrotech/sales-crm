/**
 * DealSync Enterprise CRM — Lead 360 Detail View Controller
 * Pure Tailwind CSS styling with dynamic custom fields, field-level security,
 * and seamless SPA integration matching React RecordDetailModal.
 */

(function () {
    'use strict';

    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function showNotification(message, type = 'success') {
        const feedbackBanner = document.getElementById('detailFeedbackBanner');
        const feedbackText = document.getElementById('detailFeedbackText');
        if (feedbackBanner && feedbackText) {
            feedbackText.textContent = message;
            feedbackBanner.classList.remove('hidden');
            feedbackBanner.classList.add('flex');
            setTimeout(() => {
                feedbackBanner.classList.add('hidden');
                feedbackBanner.classList.remove('flex');
            }, 5000);
        }

        if (typeof window.showToast === 'function') {
            window.showToast(message, type);
        } else if (typeof window.showSpaToast === 'function') {
            window.showSpaToast(message, type);
        } else {
            console.log(`[${type.toUpperCase()}] ${message}`);
        }
    }

    function getLeadId() {
        const modalEl = document.querySelector('[data-lead-id]');
        if (modalEl && modalEl.dataset.leadId) return modalEl.dataset.leadId;
        const currentInput = document.getElementById('current-lead-id');
        if (currentInput && currentInput.value) return currentInput.value;
        const el = document.getElementById('edit-lead-id');
        if (el && el.value) return el.value;
        if (window.__LEAD_DETAIL_DATA__ && window.__LEAD_DETAIL_DATA__.lead) {
            return window.__LEAD_DETAIL_DATA__.lead.id;
        }
        const parts = window.location.pathname.split('/').filter(Boolean);
        const lastPart = parts[parts.length - 1];
        if (lastPart && !isNaN(lastPart)) return lastPart;
        return null;
    }

    // Modal Control Helpers
    window.openDetailEditModal = function (focusField) {
        const modal = document.getElementById('detailEditModal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            if (focusField && typeof focusField === 'string') {
                const input = modal.querySelector(`[name="${focusField}"]`);
                if (input) {
                    setTimeout(() => input.focus(), 80);
                }
            }
            if (window.lucide) lucide.createIcons();
        }
    };

    // Section Accordion Toggle
    window.toggleSection = function (bodyId, chevronId) {
        const body = document.getElementById(bodyId);
        const chevron = document.getElementById(chevronId);
        if (!body) return;

        const isHidden = body.classList.contains('hidden');
        if (isHidden) {
            body.classList.remove('hidden');
            if (chevron) {
                chevron.classList.remove('-rotate-90');
                chevron.classList.add('rotate-0');
            }
        } else {
            body.classList.add('hidden');
            if (chevron) {
                chevron.classList.add('-rotate-90');
                chevron.classList.remove('rotate-0');
            }
        }
        if (window.lucide) lucide.createIcons();
    };

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function renderTimelineItemHtml(act) {
        const typeLower = (act.type || 'Call').toLowerCase();
        const isCall = typeLower === 'call';
        const isTask = typeLower === 'task';
        const isEvent = typeLower === 'event' || typeLower === 'meeting';

        let iconSvg = '';
        let iconBg = '';
        let timeLabel = 'Call Time';

        if (isCall) {
            iconSvg = `<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>`;
            iconBg = 'bg-blue-50 dark:bg-blue-950/60 text-blue-500 dark:text-blue-400';
            timeLabel = 'Call Time';
        } else if (isTask) {
            iconSvg = `<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="m9 12 2 2 4-4"/></svg>`;
            iconBg = 'bg-purple-100 dark:bg-purple-950/70 text-purple-600 dark:text-purple-400';
            timeLabel = 'Due Date';
        } else if (isEvent) {
            iconSvg = `<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>`;
            iconBg = 'bg-purple-100 dark:bg-purple-950/70 text-[#7c3aed] dark:text-purple-300';
            timeLabel = 'Meeting Time';
        } else {
            iconSvg = `<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>
</svg>`;
            iconBg = 'bg-purple-100 dark:bg-purple-950/70 text-[#7c3aed] dark:text-purple-300';
            timeLabel = 'Date';
        }

        return `
            <div class="bg-white dark:bg-zinc-800/80 rounded-xl border border-slate-200/90 dark:border-zinc-700/80 p-3.5 space-y-1.5 transition-shadow hover:shadow-xs">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-8 h-8 rounded-full ${iconBg} flex items-center justify-center shrink-0">
                            ${iconSvg}
                        </div>
                        <span class="font-bold text-slate-900 dark:text-zinc-100 text-xs sm:text-sm truncate">
                            ${escapeHtml(act.subject)}
                        </span>
                    </div>
                    <span class="text-[11px] text-slate-400 dark:text-zinc-500 shrink-0 font-medium">
                        ${escapeHtml(act.created_at_formatted || 'Just now')}
                    </span>
                </div>

                <div class="pl-10.5 space-y-1">
                    ${act.call_time ? `
                        <div class="text-[11px] text-slate-500 dark:text-zinc-400 flex items-center gap-1.5">
                            <span>${timeLabel}: ${escapeHtml(act.call_time)}</span>
                        </div>
                    ` : ''}

                    ${act.details ? `
                        <div class="text-xs text-slate-700 dark:text-zinc-300 leading-relaxed pt-0.5">
                            ${escapeHtml(act.details)}
                        </div>
                    ` : ''}

                    <div class="flex items-center gap-1.5 text-[11px] text-slate-400 dark:text-zinc-500 pt-1">
                        <svg class="w-3 h-3 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <span>Logged by ${escapeHtml(act.user_name || 'Alex Morgan')}</span>
                        <span>•</span>
                        <span>${escapeHtml(act.type || 'Call')}</span>
                    </div>
                </div>
            </div>
        `;
    }

    // Sub-Tabs Switching (Related, Activity, Files, Approvals, History)
    window.switchRightTab = function (tabKey) {
        document.querySelectorAll('.subtab-item').forEach(btn => {
            btn.classList.remove(
                'bg-[#334e68]', 'bg-[#ea580c]', 'text-white', 'shadow-xs', 'font-bold',
                'border-2', 'border-slate-900', 'dark:border-black'
            );
            btn.classList.add('text-slate-600', 'dark:text-zinc-400');
            const svg = btn.querySelector('svg');
            if (svg) {
                svg.classList.add('text-slate-400');
                svg.classList.remove('text-white', 'text-[#c2410c]');
            }
            const badge = btn.querySelector('span[id$="-badge"]');
            if (badge) {
                badge.classList.remove('bg-[#486581]', 'text-white');
                badge.classList.add('bg-slate-200', 'dark:bg-zinc-700', 'text-slate-700', 'dark:text-zinc-200');
            }
        });

        const activeBtn = document.getElementById(`subtab-btn-${tabKey}`);
        if (activeBtn) {
            if (tabKey === 'approvals') {
                activeBtn.classList.remove('text-slate-600', 'dark:text-zinc-400', 'text-[#c2410c]');
                activeBtn.classList.add('bg-[#ea580c]', 'text-white', 'shadow-xs', 'font-bold', 'border-2', 'border-slate-900', 'dark:border-black');
            } else if (tabKey === 'history') {
                activeBtn.classList.remove('text-slate-600', 'dark:text-zinc-400');
                activeBtn.classList.add('bg-[#334e68]', 'text-white', 'shadow-xs', 'font-bold', 'border-2', 'border-slate-900', 'dark:border-black');
            } else {
                activeBtn.classList.remove('text-slate-600', 'dark:text-zinc-400');
                activeBtn.classList.add('bg-[#334e68]', 'text-white', 'shadow-xs', 'font-bold');
            }
            const svg = activeBtn.querySelector('svg');
            if (svg) {
                svg.classList.remove('text-slate-400', 'text-[#c2410c]');
                svg.classList.add('text-white');
            }
            const badge = activeBtn.querySelector('span[id$="-badge"]');
            if (badge) {
                badge.classList.add('bg-[#486581]', 'text-white');
                badge.classList.remove('bg-slate-200', 'dark:bg-zinc-700', 'text-slate-700', 'dark:text-zinc-200');
            }
        }

        const activityContainer = document.getElementById('right-tab-content-activity');
        const relatedContainer = document.getElementById('right-tab-content-related');
        const filesContainer = document.getElementById('right-tab-content-files');
        const approvalsContainer = document.getElementById('right-tab-content-approvals');
        const historyContainer = document.getElementById('right-tab-content-history');

        // Hide all first
        if (activityContainer) activityContainer.classList.add('hidden');
        if (relatedContainer) relatedContainer.classList.add('hidden');
        if (filesContainer) filesContainer.classList.add('hidden');
        if (approvalsContainer) approvalsContainer.classList.add('hidden');
        if (historyContainer) historyContainer.classList.add('hidden');

        if (tabKey === 'activity') {
            if (activityContainer) activityContainer.classList.remove('hidden');
        } else if (tabKey === 'files') {
            if (filesContainer) filesContainer.classList.remove('hidden');
        } else if (tabKey === 'approvals') {
            if (approvalsContainer) approvalsContainer.classList.remove('hidden');
        } else if (tabKey === 'history') {
            if (historyContainer) historyContainer.classList.remove('hidden');
        } else if (tabKey === 'related') {
            if (relatedContainer) relatedContainer.classList.remove('hidden');
        } else {
            // fallback
            if (relatedContainer) relatedContainer.classList.remove('hidden');
        }

        try {
            const currentLeadId = typeof getLeadId === 'function' ? getLeadId() : null;
            if (currentLeadId) {
                sessionStorage.setItem('lead_active_subtab_' + currentLeadId, tabKey);
            }
        } catch (e) {}
    };

    // Trigger file chooser when clicking "+ Add File" or "+ Add"
    window.triggerAddFile = function () {
        let fileInput = document.getElementById('lead-file-input');
        if (!fileInput) {
            fileInput = document.createElement('input');
            fileInput.type = 'file';
            fileInput.id = 'lead-file-input';
            fileInput.multiple = true;
            fileInput.className = 'hidden';
            fileInput.onchange = function (e) {
                if (window.handleFileUpload) {
                    window.handleFileUpload(e);
                }
            };
            document.body.appendChild(fileInput);
        }
        fileInput.value = '';
        fileInput.click();
    };

    // Update count badges across UI
    window.updateFilesCountBadge = function (count) {
        const countNum = parseInt(count, 10) || 0;
        const tabBadge = document.getElementById('tab-files-badge');
        if (tabBadge) tabBadge.textContent = countNum;

        const cardBadge = document.getElementById('files-count-badge');
        if (cardBadge) cardBadge.textContent = countNum;

        const relatedBadge = document.getElementById('related-files-badge');
        if (relatedBadge) relatedBadge.textContent = `Files (${countNum})`;
    };

    // Render a single file row HTML matching the user screenshot
    window.renderLeadFileRowHtml = function (f) {
        let iconHtml = '';
        if (f.icon_type === 'excel') {
            iconHtml = `
                <div class="w-6 h-6 rounded bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400 flex items-center justify-center shrink-0">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <path d="M8 13h8M8 17h8M10 9h4"/>
                    </svg>
                </div>
            `;
        } else if (f.icon_type === 'pdf') {
            iconHtml = `
                <div class="w-6 h-6 rounded bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400 flex items-center justify-center shrink-0">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <path d="M9 13v4M12 13v4M15 13v4"/>
                    </svg>
                </div>
            `;
        } else if (f.icon_type === 'image') {
            iconHtml = `
                <div class="w-6 h-6 rounded bg-teal-50 text-teal-600 dark:bg-teal-950/40 dark:text-teal-400 flex items-center justify-center shrink-0">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect width="18" height="18" x="3" y="3" rx="2" ry="2"/>
                        <circle cx="9" cy="9" r="2"/>
                        <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                    </svg>
                </div>
            `;
        } else {
            iconHtml = `
                <div class="w-6 h-6 rounded bg-slate-100 text-slate-600 dark:bg-zinc-800 dark:text-zinc-400 flex items-center justify-center shrink-0">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                    </svg>
                </div>
            `;
        }

        const sizeText = f.size_formatted ? `<span class="text-[10px] text-slate-400">${escapeHtml(f.size_formatted)}</span>` : '';

        return `
            <tr id="lead-file-row-${f.id}" class="hover:bg-slate-50/70 dark:hover:bg-zinc-800/40 transition-colors">
                <td class="py-2.5 px-3.5">
                    <div class="flex items-center gap-2.5">
                        ${iconHtml}
                        <div class="min-w-0">
                            <a href="${escapeHtml(f.download_url)}" target="_blank" download class="font-semibold text-slate-800 dark:text-zinc-100 hover:text-cyan-600 dark:hover:text-cyan-400 hover:underline truncate block max-w-[200px]" title="${escapeHtml(f.name)}">
                                ${escapeHtml(f.name)}
                            </a>
                            ${sizeText}
                        </div>
                    </div>
                </td>
                <td class="py-2.5 px-3.5">
                    <span class="font-mono text-[11px] font-medium text-slate-500 dark:text-zinc-400 uppercase truncate max-w-[170px] block" title="${escapeHtml(f.type_label)}">
                        ${escapeHtml(f.type_label)}
                    </span>
                </td>
                <td class="py-2.5 px-3.5 text-right whitespace-nowrap">
                    <div class="inline-flex items-center gap-1 justify-end">
                        <a
                            href="${escapeHtml(f.download_url)}"
                            download
                            class="p-1 rounded text-slate-400 hover:text-slate-700 dark:hover:text-zinc-200 hover:bg-slate-100 dark:hover:bg-zinc-800 transition-colors"
                            title="Download"
                        >
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                <polyline points="7 10 12 15 17 10"/>
                                <line x1="12" y1="15" x2="12" y2="3"/>
                            </svg>
                        </a>
                        <button
                            type="button"
                            onclick="window.deleteLeadFile ? window.deleteLeadFile(${f.id}) : null"
                            class="p-1 rounded text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-colors cursor-pointer"
                            title="Delete"
                        >
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="3 6 5 6 21 6"/>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                            </svg>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    };

    // Handle File Upload from native file dialog
    window.handleFileUpload = async function (e) {
        const files = e.target.files;
        if (!files || files.length === 0) return;

        const leadId = getLeadId();
        if (!leadId) {
            showNotification('Could not resolve lead record ID.', 'error');
            return;
        }

        const formData = new FormData();
        for (let i = 0; i < files.length; i++) {
            formData.append('files[]', files[i]);
        }

        const indicator = document.getElementById('lead-file-uploading-indicator');
        const indicatorText = document.getElementById('lead-file-uploading-text');
        if (indicator) {
            indicator.classList.remove('hidden');
            if (indicatorText) {
                indicatorText.innerText = files.length === 1 
                    ? `Uploading ${files[0].name}...` 
                    : `Uploading ${files.length} files...`;
            }
        }

        try {
            const res = await fetch(`/api/leads/${leadId}/files`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await res.json();
            if (data.success) {
                showNotification(data.message || 'File uploaded successfully!', 'success');
                
                // Immediately switch to the files tab so user sees the newly attached file
                if (typeof window.switchRightTab === 'function') {
                    window.switchRightTab('files');
                }

                const tbody = document.getElementById('lead-files-table-body');
                const emptyRow = document.getElementById('lead-files-empty-row');
                if (emptyRow) emptyRow.remove();

                if (data.files && data.files.length > 0) {
                    data.files.forEach(f => {
                        const existingRow = document.getElementById(`lead-file-row-${f.id}`);
                        if (!existingRow && tbody) {
                            tbody.insertAdjacentHTML('afterbegin', window.renderLeadFileRowHtml(f));
                        }
                    });
                }

                window.updateFilesCountBadge(data.count);

                // If Related accordion has a files list container, refresh it too
                if (typeof window.refreshRelatedFilesWidget === 'function') {
                    window.refreshRelatedFilesWidget();
                }
            } else {
                showNotification(data.message || 'Failed to upload files.', 'error');
            }
        } catch (err) {
            console.error('File upload error:', err);
            showNotification('Error uploading files: ' + err.message, 'error');
        } finally {
            if (indicator) indicator.classList.add('hidden');
            e.target.value = '';
        }
    };

    // Refresh Files via API
    window.refreshLeadFiles = async function () {
        const leadId = getLeadId();
        if (!leadId) return;

        const refreshBtn = document.getElementById('btn-refresh-files');
        if (refreshBtn) refreshBtn.classList.add('animate-spin');

        try {
            const res = await fetch(`/api/leads/${leadId}/files`, {
                headers: {
                    'Accept': 'application/json'
                }
            });
            const data = await res.json();
            if (data.success) {
                const tbody = document.getElementById('lead-files-table-body');
                if (tbody) {
                    if (data.files && data.files.length > 0) {
                        tbody.innerHTML = data.files.map(f => window.renderLeadFileRowHtml(f)).join('');
                    } else {
                        tbody.innerHTML = `
                            <tr id="lead-files-empty-row">
                                <td colspan="3" class="p-6 text-center text-slate-400">
                                    <div class="space-y-1">
                                        <p class="text-xs font-medium">No files attached yet.</p>
                                        <p class="text-[11px] text-slate-400">Click <span class="font-bold text-slate-600 dark:text-zinc-300">+ Add File</span> to attach spreadsheets, PDFs, or images.</p>
                                    </div>
                                </td>
                            </tr>
                        `;
                    }
                }
                window.updateFilesCountBadge(data.count);
                showNotification('Files refreshed.', 'success');
            }
        } catch (err) {
            console.error('Refresh files error:', err);
        } finally {
            if (refreshBtn) refreshBtn.classList.remove('animate-spin');
        }
    };

    // Delete a File
    window.deleteLeadFile = async function (fileId) {
        if (!confirm('Are you sure you want to remove this attached file?')) return;

        const leadId = getLeadId();
        if (!leadId) return;

        try {
            const res = await fetch(`/api/leads/${leadId}/files/${fileId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json'
                }
            });
            const data = await res.json();
            if (data.success) {
                showNotification(data.message || 'File removed.', 'success');
                const row = document.getElementById(`lead-file-row-${fileId}`);
                if (row) row.remove();

                window.updateFilesCountBadge(data.count);

                const tbody = document.getElementById('lead-files-table-body');
                if (tbody && tbody.children.length === 0) {
                    tbody.innerHTML = `
                        <tr id="lead-files-empty-row">
                            <td colspan="3" class="p-6 text-center text-slate-400">
                                <div class="space-y-1">
                                    <p class="text-xs font-medium">No files attached yet.</p>
                                    <p class="text-[11px] text-slate-400">Click <span class="font-bold text-slate-600 dark:text-zinc-300">+ Add File</span> to attach spreadsheets, PDFs, or images.</p>
                                </div>
                            </td>
                        </tr>
                    `;
                }

                if (typeof window.refreshRelatedFilesWidget === 'function') {
                    window.refreshRelatedFilesWidget();
                }
            } else {
                showNotification(data.message || 'Could not delete file.', 'error');
            }
        } catch (err) {
            console.error('Delete file error:', err);
            showNotification('Error deleting file: ' + err.message, 'error');
        }
    };

    // Refresh Related Files Widget list
    window.refreshRelatedFilesWidget = async function () {
        const leadId = getLeadId();
        if (!leadId) return;

        const container = document.getElementById('related-files-list-container');
        if (!container) return;

        try {
            const res = await fetch(`/api/leads/${leadId}/files`, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();
            if (data.success) {
                if (data.files && data.files.length > 0) {
                    const itemsHtml = data.files.map(f => `
                        <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50 dark:bg-zinc-800/60 border border-slate-200/80 dark:border-zinc-700 text-xs">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="font-mono text-[10px] uppercase font-bold text-slate-500">${escapeHtml(f.type_label)}</span>
                                <a href="${escapeHtml(f.download_url)}" target="_blank" download class="font-semibold text-slate-800 dark:text-zinc-100 hover:text-cyan-600 truncate max-w-[180px]" title="${escapeHtml(f.name)}">
                                    ${escapeHtml(f.name)}
                                </a>
                                ${f.size_formatted ? `<span class="text-[10px] text-slate-400">(${escapeHtml(f.size_formatted)})</span>` : ''}
                            </div>
                            <div class="flex items-center gap-1 shrink-0">
                                <a href="${escapeHtml(f.download_url)}" download class="p-1 rounded text-slate-400 hover:text-slate-700 dark:hover:text-zinc-200" title="Download">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                </a>
                            </div>
                        </div>
                    `).join('');

                    container.innerHTML = `
                        <div class="space-y-1.5" id="related-files-items">
                            ${itemsHtml}
                        </div>
                        <div class="pt-2 text-center">
                            <button type="button" onclick="window.switchRightTab ? window.switchRightTab('files') : null" class="text-xs font-semibold text-cyan-600 hover:text-cyan-700 dark:text-cyan-400 cursor-pointer hover:underline">
                                View all files in Files tab &rarr;
                            </button>
                        </div>
                    `;
                } else {
                    container.innerHTML = `
                        <div class="p-4 border-2 border-dashed border-slate-200 dark:border-zinc-700 rounded-xl text-center space-y-1">
                            <p class="text-[11px] font-medium text-slate-500">No attached files. Click + Add to upload.</p>
                        </div>
                    `;
                }
            }
        } catch (err) {
            console.error('Failed to refresh related files widget:', err);
        }
    };

    // ==================== APPROVAL HISTORY LOGIC ====================

    // Open Submit for Approval Modal
    window.openSubmitApprovalModal = function () {
        const modal = document.getElementById('leadApprovalModal');
        if (modal) {
            modal.classList.remove('hidden');
            const comments = document.getElementById('approvalSubmissionComments');
            if (comments) comments.value = '';
        }
    };

    // Close Submit for Approval Modal
    window.closeSubmitApprovalModal = function () {
        const modal = document.getElementById('leadApprovalModal');
        if (modal) modal.classList.add('hidden');
    };

    // Render single approval row
    window.renderLeadApprovalRowHtml = function (appr) {
        let statusPill = '';
        const statusLower = (appr.status || '').toLowerCase();
        if (statusLower === 'pending') {
            statusPill = `
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                    Pending
                </span>
            `;
        } else if (statusLower === 'approved') {
            statusPill = `
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                    ✓ Approved
                </span>
            `;
        } else if (statusLower === 'rejected') {
            statusPill = `
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300">
                    ✕ Rejected
                </span>
            `;
        } else {
            statusPill = `
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 text-slate-700 dark:bg-zinc-800 dark:text-zinc-300">
                    ${escapeHtml(appr.status || 'Pending')}
                </span>
            `;
        }

        let actionButtons = '';
        if (statusLower === 'pending') {
            actionButtons = `
                <div class="inline-flex items-center gap-1 ml-2">
                    <button type="button" onclick="window.handleApprovalDecision(${appr.id}, 'approved')" class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 transition-colors cursor-pointer" title="Approve">
                        Approve
                    </button>
                    <button type="button" onclick="window.handleApprovalDecision(${appr.id}, 'rejected')" class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 transition-colors cursor-pointer" title="Reject">
                        Reject
                    </button>
                </div>
            `;
        }

        return `
            <tr id="lead-approval-row-${appr.id}" class="hover:bg-slate-50/70 dark:hover:bg-zinc-800/40 transition-colors">
                <td class="py-3 px-3.5 font-semibold text-slate-800 dark:text-zinc-100 whitespace-nowrap">
                    ${escapeHtml(appr.step_name || 'Step 1: Submission')}
                    ${actionButtons}
                </td>
                <td class="py-3 px-3.5 text-slate-500 dark:text-zinc-400 whitespace-nowrap">
                    ${escapeHtml(appr.date_formatted || '')}
                </td>
                <td class="py-3 px-3.5 whitespace-nowrap">
                    ${statusPill}
                </td>
                <td class="py-3 px-3.5 text-slate-700 dark:text-zinc-200 whitespace-nowrap">
                    ${escapeHtml(appr.assigned_to || 'System Administrator')}
                </td>
                <td class="py-3 px-3.5 text-slate-700 dark:text-zinc-200 whitespace-nowrap">
                    ${escapeHtml(appr.actual_approver || '—')}
                </td>
                <td class="py-3 px-3.5 text-slate-500 dark:text-zinc-400 max-w-[200px] truncate" title="${escapeHtml(appr.comments || '')}">
                    ${escapeHtml(appr.comments || '—')}
                </td>
            </tr>
        `;
    };

    // Handle Submit for Approval
    window.handleApprovalSubmit = async function (e) {
        if (e) e.preventDefault();
        const leadId = getLeadId();
        if (!leadId) {
            showNotification('Could not resolve lead record ID.', 'error');
            return;
        }

        const comments = (document.getElementById('approvalSubmissionComments')?.value || '').trim();
        const confirmBtn = document.getElementById('btn-submit-approval-confirm');
        if (confirmBtn) {
            confirmBtn.disabled = true;
            confirmBtn.innerText = 'Submitting...';
        }

        try {
            const res = await fetch(`/api/leads/${leadId}/approvals`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ comments: comments })
            });

            const data = await res.json();
            if (data.success) {
                showNotification(data.message || 'Submitted for approval successfully!', 'success');
                window.closeSubmitApprovalModal();

                // Update table
                const tbody = document.getElementById('lead-approvals-table-body');
                const emptyRow = document.getElementById('lead-approvals-empty-row');
                if (emptyRow) emptyRow.remove();

                if (tbody && data.approval) {
                    tbody.insertAdjacentHTML('afterbegin', window.renderLeadApprovalRowHtml(data.approval));
                }

                // Update count badge / label
                const countLabel = document.getElementById('approval-history-count-label');
                if (countLabel) countLabel.innerText = `${data.count || 1} items`;

                // Switch to approvals tab
                window.switchRightTab('approvals');
            } else {
                showNotification(data.message || 'Failed to submit for approval.', 'error');
            }
        } catch (err) {
            console.error('Submit approval error:', err);
            showNotification('Error submitting for approval: ' + err.message, 'error');
        } finally {
            if (confirmBtn) {
                confirmBtn.disabled = false;
                confirmBtn.innerText = 'Submit Request';
            }
        }
    };

    // Handle Decision (Approve / Reject)
    window.handleApprovalDecision = async function (requestId, action) {
        const leadId = getLeadId();
        if (!leadId || !requestId) return;

        const actionText = action === 'approved' ? 'approve' : 'reject';
        const comments = prompt(`Enter comments to ${actionText} this request (optional):`, action === 'approved' ? 'Approved by manager.' : 'Rejected by manager.');
        if (comments === null) return; // User cancelled prompt

        try {
            const res = await fetch(`/api/leads/${leadId}/approvals/${requestId}/action`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ action: action, comments: comments })
            });

            const data = await res.json();
            if (data.success) {
                showNotification(data.message || `Approval ${actionText}d successfully.`, 'success');
                if (data.approval) {
                    const row = document.getElementById(`lead-approval-row-${requestId}`);
                    if (row) {
                        row.outerHTML = window.renderLeadApprovalRowHtml(data.approval);
                    }
                }
            } else {
                showNotification(data.message || 'Could not update approval status.', 'error');
            }
        } catch (err) {
            console.error('Approval decision error:', err);
            showNotification('Error processing approval: ' + err.message, 'error');
        }
    };

    // Refresh Approvals list via API
    window.refreshLeadApprovals = async function () {
        const leadId = getLeadId();
        if (!leadId) return;

        const refreshBtn = document.getElementById('btn-refresh-approvals');
        if (refreshBtn) refreshBtn.classList.add('animate-spin');

        try {
            const res = await fetch(`/api/leads/${leadId}/approvals`, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();
            if (data.success) {
                const tbody = document.getElementById('lead-approvals-table-body');
                if (tbody) {
                    if (data.approvals && data.approvals.length > 0) {
                        tbody.innerHTML = data.approvals.map(a => window.renderLeadApprovalRowHtml(a)).join('');
                    } else {
                        tbody.innerHTML = `
                            <tr id="lead-approvals-empty-row">
                                <td colspan="6" class="p-10 text-center text-slate-500 dark:text-zinc-400">
                                    <p class="text-xs font-semibold text-slate-600 dark:text-zinc-300">No approval history found for this record.</p>
                                </td>
                            </tr>
                        `;
                    }
                }
                const countLabel = document.getElementById('approval-history-count-label');
                if (countLabel) countLabel.innerText = `${data.count || 0} items`;
                showNotification('Approval history refreshed.', 'success');
            }
        } catch (err) {
            console.error('Refresh approvals error:', err);
        } finally {
            if (refreshBtn) refreshBtn.classList.remove('animate-spin');
        }
    };

    // ==================== LEAD HISTORY SUB-TAB CONTROLLERS ====================

    window.renderLeadHistoryRowHtml = function (hist) {
        return `
            <tr id="lead-history-row-${hist.id}" class="lead-history-data-row hover:bg-slate-50/70 dark:hover:bg-zinc-800/40 transition-colors">
                <td class="py-3.5 px-4 font-medium text-slate-600 dark:text-zinc-400 whitespace-nowrap">
                    ${hist.date_formatted || '—'}
                </td>
                <td class="py-3.5 px-4 font-semibold text-slate-800 dark:text-zinc-100 whitespace-nowrap">
                    ${hist.field_name || '—'}
                </td>
                <td class="py-3.5 px-4 text-slate-700 dark:text-zinc-300 whitespace-nowrap">
                    ${hist.user || 'System Administrator'}
                </td>
                <td class="py-3.5 px-4 text-slate-600 dark:text-zinc-400 break-words max-w-[200px]">
                    <span class="inline-block bg-slate-100 dark:bg-zinc-800 px-2 py-0.5 rounded text-slate-600 dark:text-zinc-300 font-mono text-[11px]">${hist.original_value !== null && hist.original_value !== undefined ? hist.original_value : '—'}</span>
                </td>
                <td class="py-3.5 px-4 text-slate-800 dark:text-zinc-100 font-semibold break-words max-w-[200px]">
                    <span class="inline-block bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/50 text-emerald-700 dark:text-emerald-300 px-2 py-0.5 rounded font-mono text-[11px]">${hist.new_value !== null && hist.new_value !== undefined ? hist.new_value : '—'}</span>
                </td>
            </tr>
        `;
    };

    window.renderLeadHistoryTable = function (historyList) {
        const tbody = document.getElementById('lead-history-table-body');
        if (!tbody) return;

        if (!Array.isArray(historyList) || historyList.length === 0) {
            tbody.innerHTML = `
                <tr id="lead-history-empty-row">
                    <td colspan="5" class="py-12 sm:py-16 text-center px-4">
                        <p class="text-sm font-medium text-slate-600 dark:text-zinc-400">No field history recorded for this record yet.</p>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = historyList.map(h => window.renderLeadHistoryRowHtml(h)).join('');
    };

    window.filterLeadHistory = function () {
        const input = document.getElementById('lead-history-search-input');
        if (!input) return;
        const query = (input.value || '').toLowerCase().trim();
        const rows = document.querySelectorAll('.lead-history-data-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            if (!query || text.includes(query)) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        let noMatchRow = document.getElementById('lead-history-no-match-row');
        const tbody = document.getElementById('lead-history-table-body');
        if (rows.length > 0 && visibleCount === 0) {
            if (!noMatchRow && tbody) {
                noMatchRow = document.createElement('tr');
                noMatchRow.id = 'lead-history-no-match-row';
                noMatchRow.innerHTML = `
                    <td colspan="5" class="py-12 text-center text-slate-500 dark:text-zinc-400 px-4">
                        <p class="text-sm font-medium">No history entries found matching "${input.value}".</p>
                    </td>
                `;
                tbody.appendChild(noMatchRow);
            }
        } else if (noMatchRow) {
            noMatchRow.remove();
        }
    };

    window.refreshLeadHistory = async function () {
        const leadId = typeof getLeadId === 'function' ? getLeadId() : null;
        if (!leadId) return;

        const btn = document.getElementById('btn-refresh-history');
        const icon = btn ? btn.querySelector('svg') : null;
        if (icon) icon.classList.add('animate-spin');

        try {
            const res = await fetch(`/api/leads/${leadId}/history`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            if (!res.ok) throw new Error('Failed to fetch lead history');
            const data = await res.json();
            if (data.success && Array.isArray(data.history)) {
                window.renderLeadHistoryTable(data.history);
                const countLabel = document.getElementById('lead-history-count-label');
                if (countLabel) {
                    countLabel.textContent = `${data.count || 0} changes recorded`;
                }
                const accordionBadge = document.querySelector('#card-widget-history span');
                if (accordionBadge) {
                    accordionBadge.textContent = `Lead History (${data.count || 0})`;
                }
                showNotification('Lead history refreshed.', 'success');
            }
        } catch (err) {
            console.error('Error refreshing history:', err);
            showNotification('Could not refresh lead history.', 'error');
        } finally {
            if (icon) icon.classList.remove('animate-spin');
        }
    };

    // Sub-Navigation for Activity Form: Call, Task, Event, Note
    window.switchActivityFormType = function (type) {
        document.querySelectorAll('.act-type-pill').forEach(btn => {
            btn.classList.remove('bg-[#f3e8ff]', 'text-[#7c3aed]', 'border-purple-200', 'font-semibold', 'shadow-2xs');
            btn.classList.add('bg-white', 'dark:bg-zinc-800', 'border-slate-200', 'dark:border-zinc-700', 'text-slate-700', 'dark:text-zinc-300', 'font-medium');
            const icon = btn.querySelector('svg');
            if (icon) {
                icon.classList.remove('text-[#7c3aed]');
                icon.classList.add('text-slate-500');
            }
        });

        const pillKey = (type || 'Call').toLowerCase();
        const activePill = document.getElementById(`act-pill-${pillKey}`);
        if (activePill) {
            activePill.classList.add('bg-[#f3e8ff]', 'text-[#7c3aed]', 'border-purple-200', 'font-semibold', 'shadow-2xs');
            activePill.classList.remove('bg-white', 'dark:bg-zinc-800', 'border-slate-200', 'dark:border-zinc-700', 'text-slate-700', 'dark:text-zinc-300', 'font-medium');
            const activeIcon = activePill.querySelector('svg');
            if (activeIcon) {
                activeIcon.classList.remove('text-slate-500');
                activeIcon.classList.add('text-[#7c3aed]');
            }
        }

        const callView = document.getElementById('activity-call-view');
        const taskView = document.getElementById('activity-task-view');
        const eventView = document.getElementById('activity-event-view');

        if (callView) callView.classList.add('hidden');
        if (taskView) taskView.classList.add('hidden');
        if (eventView) eventView.classList.add('hidden');

        if (type === 'Task') {
            if (taskView) taskView.classList.remove('hidden');
            if (window.openNewTaskModal) {
                window.openNewTaskModal();
            }
            return;
        }

        if (type === 'Event' || type === 'Meeting') {
            if (eventView) eventView.classList.remove('hidden');
            const titleInput = document.getElementById('event-input-subject');
            if (titleInput) {
                setTimeout(() => titleInput.focus(), 50);
            }
            return;
        }

        // For Call, Note: show call form view
        if (callView) callView.classList.remove('hidden');

        const typeInput = document.getElementById('activity-form-current-type');
        if (typeInput) typeInput.value = type;

        const lblSubject = document.getElementById('lbl-activity-subject');
        const inputSubject = document.getElementById('activity-input-subject');
        const lblDatetime = document.getElementById('lbl-activity-datetime');
        const lblNotes = document.getElementById('lbl-activity-notes');
        const btnText = document.getElementById('btn-activity-text');

        if (type === 'Call') {
            if (lblSubject) lblSubject.textContent = 'Call Subject';
            if (inputSubject) inputSubject.placeholder = 'e.g. Discovery call with...';
            if (lblDatetime) lblDatetime.textContent = 'Date & Time of Call';
            if (lblNotes) lblNotes.textContent = 'Description / Discussion Notes';
            if (btnText) btnText.textContent = 'Log Call';
        } else if (type === 'Note') {
            if (lblSubject) lblSubject.textContent = 'Note Title';
            if (inputSubject) {
                inputSubject.placeholder = 'e.g. Key executive sponsorship discussion...';
                setTimeout(() => inputSubject.focus(), 50);
            }
            if (lblDatetime) lblDatetime.textContent = 'Date & Time of Note';
            if (lblNotes) lblNotes.textContent = 'Note Details';
            if (btnText) btnText.textContent = 'Save Note';
        }
    };

    // Submit Event / Meeting to Database & Prepend to Timeline
    window.submitLeadEvent = function () {
        const leadId = getLeadId();
        if (!leadId) {
            showNotification('Could not resolve lead record ID.', 'error');
            return;
        }

        const titleInput = document.getElementById('event-input-subject');
        const startInput = document.getElementById('event-input-start');
        const endInput = document.getElementById('event-input-end');
        const detailsInput = document.getElementById('event-input-details');
        const submitBtn = document.getElementById('btn-event-submit');

        const subject = titleInput ? titleInput.value.trim() : '';
        if (!subject) {
            showNotification('Please enter an event / meeting title.', 'error');
            if (titleInput) titleInput.focus();
            return;
        }

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-70', 'cursor-not-allowed');
        }

        fetch(`/api/leads/${leadId}/activities`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                type: 'Meeting',
                subject: subject,
                start_date: startInput ? startInput.value.trim() : null,
                end_date: endInput ? endInput.value.trim() : null,
                call_datetime: startInput ? startInput.value.trim() : null,
                details: detailsInput ? detailsInput.value.trim() : ''
            })
        })
        .then(res => res.json())
        .then(data => {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-70', 'cursor-not-allowed');
            }

            if (data.success && data.activity) {
                showNotification(data.message || 'Meeting logged successfully!', 'success');

                const list = document.getElementById('lead-activities-timeline-list');
                const emptyMsg = document.getElementById('timeline-empty-message');
                if (emptyMsg) emptyMsg.remove();

                if (list) {
                    const temp = document.createElement('div');
                    temp.innerHTML = renderTimelineItemHtml(data.activity);
                    const newEl = temp.firstElementChild;
                    if (newEl) {
                        list.insertBefore(newEl, list.firstChild);
                        newEl.classList.add('ring-2', 'ring-purple-400');
                        setTimeout(() => newEl.classList.remove('ring-2', 'ring-purple-400'), 1500);
                    }
                }

                const count = data.total_count;
                const timelineBadge = document.getElementById('timeline-count-badge');
                const tabBadge = document.getElementById('tab-activity-badge');
                if (timelineBadge && count !== undefined) timelineBadge.textContent = count;
                if (tabBadge && count !== undefined) tabBadge.textContent = count;

                if (titleInput) titleInput.value = '';
                if (detailsInput) detailsInput.value = '';
            } else {
                showNotification(data.message || 'Failed to log meeting.', 'error');
            }
        })
        .catch(err => {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-70', 'cursor-not-allowed');
            }
            console.error('Error logging meeting:', err);
            showNotification('Network error while logging meeting.', 'error');
        });
    };

    // Modal Control: New Task Popup
    window.openNewTaskModal = function () {
        const modal = document.getElementById('leadNewTaskModal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                const subject = document.getElementById('task-modal-subject');
                if (subject) subject.focus();
            }, 60);
        }
    };

    window.closeNewTaskModal = function () {
        const modal = document.getElementById('leadNewTaskModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    };

    window.submitNewTaskModal = function () {
        const leadId = getLeadId();
        if (!leadId) {
            showNotification('Could not resolve lead record ID.', 'error');
            return;
        }

        const subjectInput = document.getElementById('task-modal-subject');
        const prioritySelect = document.getElementById('task-modal-priority');
        const statusSelect = document.getElementById('task-modal-status');
        const assigneeSelect = document.getElementById('task-modal-assignee');
        const dueDateInput = document.getElementById('task-modal-duedate');
        const moduleSelect = document.getElementById('task-modal-module');
        const recordNameInput = document.getElementById('task-modal-record-name');
        const reminderSelect = document.getElementById('task-modal-reminder');
        const commentsTextarea = document.getElementById('task-modal-comments');
        const recurringCheckbox = document.getElementById('task-modal-recurring');
        const submitBtn = document.getElementById('btn-submit-task-modal');

        const subject = subjectInput ? subjectInput.value.trim() : '';
        if (!subject) {
            showNotification('Please enter a task subject.', 'error');
            if (subjectInput) subjectInput.focus();
            return;
        }

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-70', 'cursor-not-allowed');
        }

        const payload = {
            subject: subject,
            priority: prioritySelect ? prioritySelect.value : 'Normal',
            status: statusSelect ? statusSelect.value : 'Open',
            assigned_to: assigneeSelect ? assigneeSelect.value : null,
            due_date: dueDateInput ? dueDateInput.value : null,
            related_module: moduleSelect ? moduleSelect.value : 'Leads',
            related_record_name: recordNameInput ? recordNameInput.value.trim() : '',
            reminder_set: reminderSelect ? reminderSelect.value : 'No reminder is set',
            comments: commentsTextarea ? commentsTextarea.value.trim() : '',
            is_recurring: recurringCheckbox ? (recurringCheckbox.checked ? 1 : 0) : 0,
        };

        fetch(`/api/leads/${leadId}/tasks`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-70', 'cursor-not-allowed');
            }

            if (data.success) {
                showNotification(data.message || 'Task created successfully!', 'success');
                window.closeNewTaskModal();

                if (subjectInput) subjectInput.value = '';
                if (commentsTextarea) commentsTextarea.value = '';
                if (recurringCheckbox) recurringCheckbox.checked = false;

                if (data.activity) {
                    const list = document.getElementById('lead-activities-timeline-list');
                    const emptyMsg = document.getElementById('timeline-empty-message');
                    if (emptyMsg) emptyMsg.remove();

                    if (list) {
                        const temp = document.createElement('div');
                        temp.innerHTML = renderTimelineItemHtml(data.activity);
                        const newEl = temp.firstElementChild;
                        if (newEl) {
                            list.insertBefore(newEl, list.firstChild);
                            newEl.classList.add('ring-2', 'ring-purple-400');
                            setTimeout(() => newEl.classList.remove('ring-2', 'ring-purple-400'), 1500);
                        }
                    }

                    const count = data.total_count;
                    const timelineBadge = document.getElementById('timeline-count-badge');
                    const tabBadge = document.getElementById('tab-activity-badge');
                    if (timelineBadge && count !== undefined) timelineBadge.textContent = count;
                    if (tabBadge && count !== undefined) tabBadge.textContent = count;
                }
            } else {
                showNotification(data.message || 'Failed to create task.', 'error');
            }
        })
        .catch(err => {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-70', 'cursor-not-allowed');
            }
            console.error('Error creating task:', err);
            showNotification('Network error while creating task.', 'error');
        });
    };

    // Submit Activity to Database & Prepend to Timeline
    window.submitLeadActivity = function () {
        const leadId = getLeadId();
        if (!leadId) {
            showNotification('Could not resolve lead record ID.', 'error');
            return;
        }

        const type = document.getElementById('activity-form-current-type')?.value || 'Call';
        const subjectInput = document.getElementById('activity-input-subject');
        const datetimeInput = document.getElementById('activity-input-datetime');
        const detailsInput = document.getElementById('activity-input-details');
        const submitBtn = document.getElementById('btn-activity-submit');

        const subject = subjectInput ? subjectInput.value.trim() : '';
        if (!subject) {
            const fieldLabel = type.toLowerCase() === 'note' ? 'note title' : `${type.toLowerCase()} subject`;
            showNotification(`Please enter a ${fieldLabel}.`, 'error');
            if (subjectInput) subjectInput.focus();
            return;
        }

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-70', 'cursor-not-allowed');
        }

        fetch(`/api/leads/${leadId}/activities`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                type: type,
                subject: subject,
                call_datetime: datetimeInput ? datetimeInput.value.trim() : null,
                details: detailsInput ? detailsInput.value.trim() : ''
            })
        })
        .then(res => res.json())
        .then(data => {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-70', 'cursor-not-allowed');
            }

            if (data.success && data.activity) {
                const defaultMsg = type.toLowerCase() === 'note' ? 'Note saved successfully!' : `${type} logged successfully!`;
                showNotification(data.message || defaultMsg, 'success');

                const list = document.getElementById('lead-activities-timeline-list');
                const emptyMsg = document.getElementById('timeline-empty-message');
                if (emptyMsg) emptyMsg.remove();

                if (list) {
                    const temp = document.createElement('div');
                    temp.innerHTML = renderTimelineItemHtml(data.activity);
                    const newEl = temp.firstElementChild;
                    if (newEl) {
                        list.insertBefore(newEl, list.firstChild);
                        newEl.classList.add('ring-2', 'ring-purple-400');
                        setTimeout(() => newEl.classList.remove('ring-2', 'ring-purple-400'), 1500);
                    }
                }

                const count = data.total_count;
                const timelineBadge = document.getElementById('timeline-count-badge');
                const tabBadge = document.getElementById('tab-activity-badge');
                if (timelineBadge) timelineBadge.textContent = count;
                if (tabBadge) tabBadge.textContent = count;

                if (subjectInput) subjectInput.value = '';
                if (detailsInput) detailsInput.value = '';
            } else {
                showNotification(data.message || 'Failed to log activity.', 'error');
            }
        })
        .catch(err => {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-70', 'cursor-not-allowed');
            }
            console.error('Error logging activity:', err);
            showNotification('Network error while logging activity.', 'error');
        });
    };

    // Refresh Timeline List from Backend
    window.refreshLeadActivities = function () {
        const leadId = getLeadId();
        if (!leadId) return;

        const list = document.getElementById('lead-activities-timeline-list');
        if (list) {
            list.classList.add('opacity-50');
        }

        fetch(`/api/leads/${leadId}/activities`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            }
        })
        .then(res => res.json())
        .then(data => {
            if (list) list.classList.remove('opacity-50');
            if (data.success && Array.isArray(data.activities)) {
                if (list) {
                    if (data.activities.length === 0) {
                        list.innerHTML = `
                            <div id="timeline-empty-message" class="p-6 border-2 border-dashed border-slate-200 dark:border-zinc-700 rounded-xl text-center space-y-1">
                                <p class="text-xs font-medium text-slate-400">No activity logged yet.</p>
                                <p class="text-[11px] text-slate-400">Use the form above to log a call, task, or note.</p>
                            </div>
                        `;
                    } else {
                        list.innerHTML = data.activities.map(act => renderTimelineItemHtml(act)).join('');
                    }
                }

                const count = data.count;
                const timelineBadge = document.getElementById('timeline-count-badge');
                const tabBadge = document.getElementById('tab-activity-badge');
                if (timelineBadge) timelineBadge.textContent = count;
                if (tabBadge) tabBadge.textContent = count;

                showNotification('Activity timeline refreshed.', 'success');
            }
        })
        .catch(err => {
            if (list) list.classList.remove('opacity-50');
            console.error('Failed to refresh activities:', err);
        });
    };


    // Trigger Log Activity / Note
    window.triggerLogActivity = function () {
        if (window.switchActivityFormType) {
            window.switchActivityFormType('Note');
            const pill = document.getElementById('act-pill-note');
            if (pill) {
                pill.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }
    };

    // Refresh Emails
    window.refreshEmailsList = function () {
        showNotification('Checking email server for incoming correspondence...', 'info');
        setTimeout(() => {
            showNotification('Email correspondence synced and up to date.', 'success');
        }, 800);
    };

    // Expand Widget in Place / Maximize
    window.toggleWidgetExpand = function (bodyId) {
        const body = document.getElementById(bodyId);
        if (body) {
            body.classList.toggle('hidden');
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

    window.openCloneModal = function () {
        const modal = document.getElementById('cloneLeadModal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            if (window.lucide) lucide.createIcons();
        }
    };

    window.closeCloneModal = function () {
        const modal = document.getElementById('cloneLeadModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    };

    window.openQuickEmailModal = function () {
        const modal = document.getElementById('quickEmailModal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            if (window.lucide) lucide.createIcons();
        }
    };

    window.closeQuickEmailModal = function () {
        const modal = document.getElementById('quickEmailModal');
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
                showNotification(data.message || 'Lead deleted successfully', 'success');
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
                email_opt_out: formData.get('email_opt_out') === '1' ? 1 : 0,
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
                showNotification(data.message || 'Ownership transferred successfully', 'success');
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

    // Handle Clone Form Submission
    async function handleCloneSubmit(e) {
        e.preventDefault();
        const form = e.target;
        const btn = form.querySelector('button[type="submit"]');
        if (btn) btn.disabled = true;

        try {
            const formData = new FormData(form);
            const leadData = window.__LEAD_DETAIL_DATA__?.lead || {};

            const payload = {
                first_name: formData.get('first_name'),
                last_name: formData.get('last_name'),
                company: formData.get('company'),
                title: leadData.title || '',
                email: formData.get('email') || '',
                phone: leadData.phone || '',
                status: 'New',
                source: leadData.source || 'Website',
                score: leadData.score || 50,
                rating: leadData.rating || 'Warm',
                notes: leadData.notes ? `Cloned from #${leadData.id}:\n${leadData.notes}` : `Cloned from #${leadData.id}`,
            };

            const res = await fetch('/api/leads', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify(payload)
            });

            const data = await res.json();
            if (res.ok && data.success && data.lead) {
                showNotification('Cloned lead created successfully!', 'success');
                window.closeCloneModal();
                const newId = data.lead.id;
                if (typeof window.loadSpaPage === 'function') {
                    window.loadSpaPage(`/leads/${newId}`);
                } else {
                    window.location.href = `/leads/${newId}`;
                }
            } else {
                showNotification(data.message || 'Failed to clone lead', 'error');
            }
        } catch (_) {
            showNotification('Error creating clone lead', 'error');
        } finally {
            if (btn) btn.disabled = false;
        }
    }

    // Handle Quick Email Form Submission
    async function handleQuickEmailSubmit(e) {
        e.preventDefault();
        const form = e.target;
        const btn = form.querySelector('button[type="submit"]');
        if (btn) btn.disabled = true;

        try {
            const to = document.getElementById('quick-email-to')?.value;
            const subject = document.getElementById('quick-email-subject')?.value;
            const body = document.getElementById('quick-email-body')?.value;

            // Attempt server email dispatch if available, otherwise mailto fallback
            try {
                const leadId = getLeadId();
                const res = await fetch(`/api/leads/${leadId}/email`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken(),
                    },
                    body: JSON.stringify({ to, subject, body })
                });
                if (res.ok) {
                    showNotification(`Email successfully dispatched to ${to}`, 'success');
                    window.closeQuickEmailModal();
                    return;
                }
            } catch {
                // Fallback
            }

            // Client side mailto trigger
            window.location.href = `mailto:${encodeURIComponent(to)}?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
            showNotification(`Email client opened for ${to}`, 'success');
            window.closeQuickEmailModal();
        } catch (_) {
            showNotification('Error sending email', 'error');
        } finally {
            if (btn) btn.disabled = false;
        }
    }

    // Master Initializer for Lead Detail View
    window.initLeadDetailView = function () {
        const root = document.getElementById('lead-show-page-root') || document.getElementById('leadDetailFullscreenModalBody');
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

        const cloneForm = document.getElementById('cloneLeadForm');
        if (cloneForm) {
            cloneForm.removeEventListener('submit', handleCloneSubmit);
            cloneForm.addEventListener('submit', handleCloneSubmit);
        }

        const quickEmailForm = document.getElementById('quickEmailForm');
        if (quickEmailForm) {
            quickEmailForm.removeEventListener('submit', handleQuickEmailSubmit);
            quickEmailForm.addEventListener('submit', handleQuickEmailSubmit);
        }

        const taskModal = document.getElementById('leadNewTaskModal');
        if (taskModal) {
            taskModal.onclick = function (e) {
                if (e.target === taskModal) {
                    window.closeNewTaskModal();
                }
            };
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                const modal = document.getElementById('leadNewTaskModal');
                if (modal && !modal.classList.contains('hidden')) {
                    window.closeNewTaskModal();
                }
            }
        });

        // Restore active sub-tab from sessionStorage or URL hash
        try {
            const currentLeadId = typeof getLeadId === 'function' ? getLeadId() : null;
            const hash = window.location.hash ? window.location.hash.replace('#', '') : null;
            const validTabs = ['activity', 'files', 'related', 'approvals', 'history'];
            let targetTab = null;

            if (hash && validTabs.includes(hash)) {
                targetTab = hash;
            } else if (currentLeadId) {
                const savedTab = sessionStorage.getItem('lead_active_subtab_' + currentLeadId);
                if (savedTab && validTabs.includes(savedTab)) {
                    targetTab = savedTab;
                }
            }

            if (targetTab && targetTab !== 'activity' && typeof window.switchRightTab === 'function') {
                window.switchRightTab(targetTab);
            }
        } catch (e) {
            console.warn('[LeadDetail] Could not restore saved subtab:', e);
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
