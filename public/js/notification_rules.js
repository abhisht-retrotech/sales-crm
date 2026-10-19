/**
 * Pop-up Notification Rules Studio Engine
 * File: public/js/notification_rules.js
 * Extracted from resources/views/admin/notification_rules.blade.php
 *
 * Handles:
 * - Tone Filtering (All, Success, Warning, Error, Info)
 * - Real-time Rule Search & Counter Sync
 * - Audio Chime Synthesizer & Desktop Toast Previews
 * - Rule Modal Form Management (Create / Edit / Live Preview)
 * - Active State Toggling & Rule Deletion via AJAX
 * - Multi-tenancy CSRF-protected JSON REST calls
 */

(function () {
    'use strict';

    // Helper: Safely resolve CSRF Token
    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        if (meta && meta.getAttribute('content')) {
            return meta.getAttribute('content');
        }
        if (window.CSRF_TOKEN) {
            return window.CSRF_TOKEN;
        }
        return '';
    }

    let activeToneFilter = 'all';

    function setToneFilter(tone, btn) {
        activeToneFilter = tone;
        document.querySelectorAll('.tone-filter-btn').forEach(b => {
            b.classList.remove('bg-white', 'dark:bg-zinc-700', 'text-zinc-900', 'dark:text-white', 'shadow-xs');
            b.classList.add('text-zinc-600', 'dark:text-zinc-300');
        });
        if (btn) {
            btn.classList.add('bg-white', 'dark:bg-zinc-700', 'text-zinc-900', 'dark:text-white', 'shadow-xs');
            btn.classList.remove('text-zinc-600', 'dark:text-zinc-300');
        }
        filterNotificationRules();
    }

    function filterNotificationRules() {
        const query = (document.getElementById('notif-search-input')?.value || '').toLowerCase().trim();
        const rows = document.querySelectorAll('.rule-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const tone = row.dataset.tone || 'info';
            const text = row.innerText.toLowerCase();

            const matchesTone = (activeToneFilter === 'all' || tone === activeToneFilter);
            const matchesQuery = (!query || text.includes(query));

            if (matchesTone && matchesQuery) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        const label = document.getElementById('rule-count-label');
        if (label) {
            label.innerHTML = `Showing <strong class="text-zinc-900 dark:text-white">${visibleCount}</strong> Rules`;
        }
    }

    function testPlayAudioChime() {
        if (window.playChimeAlert) {
            window.playChimeAlert('fanfare');
        }
        if (window.showNotification) {
            window.showNotification({
                type: 'success',
                title: '🔔 Audio Broadcast Synthesizer Active',
                message: 'Harmonious Web Audio API chime tone generated with zero latency.',
                module: 'SYSTEM',
                actions: ['view_details', 'snooze'],
                data: {
                    event: 'AUDIO_CHIME_TEST',
                    frequencies: ['523.25 Hz', '659.25 Hz', '783.99 Hz', '1046.50 Hz'],
                    driver: 'WebAudio_Synthesizer',
                    status: 'SUCCESS'
                }
            });
        }
    }

    function triggerRuleTest(rule) {
        if (!rule) return;
        if (window.showNotification) {
            window.showNotification({
                type: rule.type || 'info',
                title: rule.title || rule.name,
                message: (rule.message_template || 'Automated trigger completed.').replace('{record_name}', 'Acme Cloud Platform Expansion ($120k)'),
                module: rule.module || 'SYSTEM',
                actions: rule.actions || ['view_record', 'view_details'],
                duration_ms: rule.duration_ms || 5500,
                position: rule.position || 'top-right',
                data: {
                    rule_id: rule.id,
                    rule_name: rule.name,
                    operation: rule.operation,
                    module: rule.module,
                    timestamp: new Date().toISOString(),
                    sample_record: {
                        id: 'rec-' + Math.floor(Math.random() * 9000 + 1000),
                        name: 'Acme Cloud Platform Expansion',
                        amount: '$120,000.00',
                        stage: 'Closed Won'
                    }
                }
            });
        }
    }

    async function toggleRuleActive(id, btn) {
        try {
            const res = await fetch(`/admin/notification-rules/${id}/toggle`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                }
            });
            const data = await res.json();
            if (data.success && data.data) {
                const isActive = Boolean(data.data.is_active);
                btn.innerText = isActive ? 'Active' : 'Inactive';
                btn.className = `inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold transition-colors cursor-pointer ${
                    isActive
                        ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800'
                        : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700'
                }`;
                if (window.showToast) {
                    window.showToast(data.message, 'success');
                }
            }
        } catch (err) {
            if (window.showToast) {
                window.showToast('Failed to toggle rule state', 'error');
            }
        }
    }

    async function deleteRule(id) {
        if (!confirm('Are you sure you want to delete this notification rule?')) return;
        try {
            const res = await fetch(`/admin/notification-rules/${id}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                }
            });
            const data = await res.json();
            if (data.success) {
                const row = document.querySelector(`.rule-row[data-id="${id}"]`);
                if (row) row.remove();
                if (window.showToast) {
                    window.showToast(data.message, 'success');
                }
                filterNotificationRules();
            }
        } catch (err) {
            if (window.showToast) {
                window.showToast('Failed to delete rule', 'error');
            }
        }
    }

    function openCreateRuleModal() {
        const modal = document.getElementById('rule-modal');
        if (!modal) return;

        const titleEl = document.getElementById('modal-title');
        if (titleEl) titleEl.innerText = 'Create Pop-up Notification Rule';

        const formRuleId = document.getElementById('form-rule-id');
        if (formRuleId) formRuleId.value = '';

        const formRuleName = document.getElementById('form-rule-name');
        if (formRuleName) formRuleName.value = '';

        const formModule = document.getElementById('form-module');
        if (formModule) formModule.value = 'opportunities';

        const formOperation = document.getElementById('form-operation');
        if (formOperation) formOperation.value = 'UPDATE';

        const formCriteria = document.getElementById('form-criteria-value');
        if (formCriteria) formCriteria.value = "stage == 'Closed Won'";

        const toneRadio = document.querySelector('input[name="tone"][value="success"]');
        if (toneRadio) toneRadio.checked = true;

        const formTitle = document.getElementById('form-title');
        if (formTitle) formTitle.value = '🎉 Mega Deal Won Celebration';

        const formMsg = document.getElementById('form-message');
        if (formMsg) formMsg.value = 'Opportunity "{record_name}" over $100,000 has closed won!';

        const formDur = document.getElementById('form-duration');
        if (formDur) formDur.value = '5500';

        const formPos = document.getElementById('form-position');
        if (formPos) formPos.value = 'top-right';

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        updateModalLivePreview();
    }

    function openEditRuleModal(rule) {
        if (!rule) return;
        const modal = document.getElementById('rule-modal');
        if (!modal) return;

        const titleEl = document.getElementById('modal-title');
        if (titleEl) titleEl.innerText = 'Edit Pop-up Notification Rule';

        const formRuleId = document.getElementById('form-rule-id');
        if (formRuleId) formRuleId.value = rule.id || '';

        const formRuleName = document.getElementById('form-rule-name');
        if (formRuleName) formRuleName.value = rule.name || '';

        const formModule = document.getElementById('form-module');
        if (formModule) formModule.value = rule.module || 'all';

        const formOperation = document.getElementById('form-operation');
        if (formOperation) formOperation.value = rule.operation || 'UPDATE';

        const formCriteria = document.getElementById('form-criteria-value');
        if (formCriteria) formCriteria.value = rule.criteria_value || '';

        const toneRadio = document.querySelector(`input[name="tone"][value="${rule.type}"]`);
        if (toneRadio) toneRadio.checked = true;

        const formTitle = document.getElementById('form-title');
        if (formTitle) formTitle.value = rule.title || '';

        const formMsg = document.getElementById('form-message');
        if (formMsg) formMsg.value = rule.message_template || '';

        const formDur = document.getElementById('form-duration');
        if (formDur) formDur.value = String(rule.duration_ms || 5500);

        const formPos = document.getElementById('form-position');
        if (formPos) formPos.value = rule.position || 'top-right';

        // Check action buttons
        const actions = Array.isArray(rule.actions) ? rule.actions : [];
        document.querySelectorAll('input[name="action_item"]').forEach(cb => {
            cb.checked = actions.includes(cb.value);
        });

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        updateModalLivePreview();
    }

    function closeRuleModal() {
        const modal = document.getElementById('rule-modal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    function updateModalLivePreview() {
        const previewEl = document.getElementById('modal-live-preview-toast');
        if (!previewEl) return;

        const tone = document.querySelector('input[name="tone"]:checked')?.value || 'success';
        const title = document.getElementById('form-title')?.value || 'Notification Title';
        const message = document.getElementById('form-message')?.value || 'This is a sample live preview of how the notification pop-up will appear.';
        const module = (document.getElementById('form-module')?.value || 'OPPORTUNITIES').toUpperCase();

        const selectedActions = [];
        document.querySelectorAll('input[name="action_item"]:checked').forEach(cb => selectedActions.push(cb.value));

        let toneClasses = 'bg-emerald-50 border-emerald-300 text-emerald-950';
        let barColor = 'bg-emerald-500';
        let iconName = 'check-circle-2';
        let iconColor = 'text-emerald-600';
        let badgeClasses = 'bg-emerald-100 text-emerald-800 border-emerald-200';

        if (tone === 'error') {
            toneClasses = 'bg-red-50 border-red-300 text-red-950';
            barColor = 'bg-red-500';
            iconName = 'x-circle';
            iconColor = 'text-red-600';
            badgeClasses = 'bg-red-100 text-red-800 border-red-200';
        } else if (tone === 'warning') {
            toneClasses = 'bg-amber-50 border-amber-300 text-amber-950';
            barColor = 'bg-amber-500';
            iconName = 'alert-triangle';
            iconColor = 'text-amber-600';
            badgeClasses = 'bg-amber-100 text-amber-800 border-amber-200';
        } else if (tone === 'info') {
            toneClasses = 'bg-blue-50 border-blue-300 text-blue-950';
            barColor = 'bg-blue-500';
            iconName = 'info';
            iconColor = 'text-blue-600';
            badgeClasses = 'bg-blue-100 text-blue-800 border-blue-200';
        }

        previewEl.className = `w-[380px] max-w-full rounded-xl border shadow-lg overflow-hidden relative transition-all ${toneClasses}`;
        previewEl.innerHTML = `
            <div class="absolute top-0 left-0 h-[3.5px] ${barColor} w-2/3"></div>
            <div class="p-3.5">
                <div class="flex items-start justify-between gap-2">
                    <div class="flex items-center gap-2 flex-1 min-w-0">
                        <i data-lucide="${iconName}" class="w-4 h-4 flex-shrink-0 ${iconColor}"></i>
                        <h4 class="text-[13px] font-bold truncate m-0">${escapeHtml(title)}</h4>
                    </div>
                    <div class="flex items-center gap-1.5 flex-shrink-0">
                        <span class="text-[9.5px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded border ${badgeClasses}">${escapeHtml(module)}</span>
                        <span class="text-zinc-400 p-0.5 cursor-pointer" onclick="closeRuleModal()"><i data-lucide="x" class="w-3.5 h-3.5"></i></span>
                    </div>
                </div>
                <p class="text-[12px] leading-relaxed m-0 mt-1 ml-6 opacity-90">
                    ${escapeHtml(message.replace('{record_name}', 'Acme Cloud Expansion ($120k)'))}
                </p>
                <div class="flex flex-wrap items-center gap-1.5 mt-2.5 ml-6">
                    ${selectedActions.map(act => `
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[10.5px] font-medium bg-white text-zinc-700 border border-zinc-300 rounded shadow-xs">
                            ${act.replace(/_/g, ' ')}
                        </span>
                    `).join('')}
                </div>
            </div>
        `;

        if (window.lucide) {
            lucide.createIcons({ root: previewEl });
        }
    }

    async function handleRuleFormSubmit(e) {
        if (e && e.preventDefault) e.preventDefault();
        const id = document.getElementById('form-rule-id')?.value;
        const submitBtn = document.getElementById('save-rule-btn');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerText = 'Saving...';
        }

        const actions = [];
        document.querySelectorAll('input[name="action_item"]:checked').forEach(cb => actions.push(cb.value));

        const payload = {
            name: document.getElementById('form-rule-name')?.value || '',
            module: document.getElementById('form-module')?.value || 'opportunities',
            operation: document.getElementById('form-operation')?.value || 'UPDATE',
            criteria_value: document.getElementById('form-criteria-value')?.value || '',
            type: document.querySelector('input[name="tone"]:checked')?.value || 'success',
            title: document.getElementById('form-title')?.value || '',
            message_template: document.getElementById('form-message')?.value || '',
            duration_ms: parseInt(document.getElementById('form-duration')?.value || '5500', 10),
            position: document.getElementById('form-position')?.value || 'top-right',
            actions: actions,
            is_active: true
        };

        try {
            const url = id ? `/admin/notification-rules/${id}` : '/admin/notification-rules';
            const method = id ? 'PUT' : 'POST';

            const res = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                },
                body: JSON.stringify(payload)
            });

            const data = await res.json();
            if (data.success) {
                if (window.showToast) {
                    window.showToast(data.message, 'success');
                }
                closeRuleModal();
                setTimeout(() => window.location.reload(), 600);
            } else {
                if (window.showToast) {
                    window.showToast(data.message || 'Validation error saving rule', 'error');
                }
            }
        } catch (err) {
            if (window.showToast) {
                window.showToast('Network error saving rule', 'error');
            }
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerText = 'Save Notification Rule';
            }
        }
    }

    function escapeHtml(str) {
        if (!str) return '';
        const d = document.createElement('div');
        d.textContent = String(str);
        return d.innerHTML;
    }

    function initNotificationRulesStudio() {
        if (window.lucide) {
            lucide.createIcons();
        }
        updateModalLivePreview();
        filterNotificationRules();
    }

    // Expose functions globally for inline HTML event handlers (onclick, onsubmit, oninput)
    window.setToneFilter = setToneFilter;
    window.filterNotificationRules = filterNotificationRules;
    window.testPlayAudioChime = testPlayAudioChime;
    window.triggerRuleTest = triggerRuleTest;
    window.toggleRuleActive = toggleRuleActive;
    window.deleteRule = deleteRule;
    window.openCreateRuleModal = openCreateRuleModal;
    window.openEditRuleModal = openEditRuleModal;
    window.closeRuleModal = closeRuleModal;
    window.updateModalLivePreview = updateModalLivePreview;
    window.handleRuleFormSubmit = handleRuleFormSubmit;
    window.escapeHtml = escapeHtml;
    window.initNotificationRulesStudio = initNotificationRulesStudio;

    // Auto-init on load or if DOM already ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initNotificationRulesStudio);
    } else {
        initNotificationRulesStudio();
    }
})();
