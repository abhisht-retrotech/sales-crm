/**
 * Email Templates & Scheduled Triggers Studio Engine
 * File: public/js/email_templates.js
 * Extracted from resources/views/admin/email_templates.blade.php
 *
 * Handles:
 * - Email Templates Library (List/Grid modes, Category & Module filters, Instant Search)
 * - Dynamic Merge Tag Assistant (Inserting {{first_name}}, {{company}}, etc.)
 * - Dual-Mode Rich Text Visual Composer & Raw HTML Switcher
 * - Live Rendering & Interactive Test Dispatch Modal
 * - Time-Based & Scheduled Automation Triggers (Offset evaluations, Criteria matching)
 * - Real-time Status Toggles & Event Notifications
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

    // Merge Tag Dictionaries matching 1:1 React EmailTemplatesStudio
    const MERGE_TAGS = {
        global: [
            { tag: 'id', label: 'Record ID' },
            { tag: 'created_at', label: 'Creation Date' },
            { tag: 'assigned_to', label: 'Owner / Assignee' },
            { tag: 'current_date', label: 'Current Date' }
        ],
        leads: [
            { tag: 'first_name', label: 'First Name' },
            { tag: 'last_name', label: 'Last Name' },
            { tag: 'company', label: 'Company Name' },
            { tag: 'title', label: 'Job Title' },
            { tag: 'email', label: 'Email Address' },
            { tag: 'phone', label: 'Phone' },
            { tag: 'status', label: 'Lead Status' },
            { tag: 'assigned_to', label: 'Assigned Rep' }
        ],
        opportunities: [
            { tag: 'name', label: 'Deal Name' },
            { tag: 'amount', label: 'Deal Amount' },
            { tag: 'stage', label: 'Stage' },
            { tag: 'expected_close_date', label: 'Target Close Date' },
            { tag: 'assigned_to', label: 'Owner' }
        ],
        accounts: [
            { tag: 'name', label: 'Account Name' },
            { tag: 'industry', label: 'Industry' },
            { tag: 'website', label: 'Website' },
            { tag: 'phone', label: 'Phone' },
            { tag: 'assigned_to', label: 'Account Owner' }
        ],
        contacts: [
            { tag: 'first_name', label: 'First Name' },
            { tag: 'last_name', label: 'Last Name' },
            { tag: 'account_name', label: 'Company Name' },
            { tag: 'email', label: 'Direct Email' }
        ],
        purchase_deals: [
            { tag: 'po_number', label: 'PO Number' },
            { tag: 'title', label: 'PO Title' },
            { tag: 'amount', label: 'PO Total Value' },
            { tag: 'expected_delivery_date', label: 'Target Delivery' },
            { tag: 'payment_terms', label: 'Payment Terms' }
        ],
        payments: [
            { tag: 'invoice_number', label: 'Invoice #' },
            { tag: 'amount', label: 'Total Amount' },
            { tag: 'due_date', label: 'Due Date' }
        ],
        shipping: [
            { tag: 'tracking_number', label: 'Tracking Code' },
            { tag: 'carrier', label: 'Carrier' },
            { tag: 'status', label: 'Logistics Status' }
        ]
    };

    let activeEntityFilter = 'all';
    let currentTestingTemplate = null;

    function switchMainStudioTab(tab) {
        const tabTemplates = document.getElementById('tab-content-templates');
        const tabTriggers = document.getElementById('tab-content-triggers');
        const btnTemplates = document.getElementById('tab-btn-templates');
        const btnTriggers = document.getElementById('tab-btn-triggers');

        if (!tabTemplates || !tabTriggers) return;

        if (tab === 'templates') {
            tabTemplates.classList.remove('hidden');
            tabTriggers.classList.add('hidden');
            if (btnTemplates) btnTemplates.className = 'main-studio-tab inline-flex items-center gap-2 px-4 py-2.5 text-xs font-bold border-b-2 border-indigo-600 text-indigo-600 dark:text-indigo-400 transition-colors cursor-pointer';
            if (btnTriggers) btnTriggers.className = 'main-studio-tab inline-flex items-center gap-2 px-4 py-2.5 text-xs font-bold border-b-2 border-transparent text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-200 transition-colors cursor-pointer';
        } else {
            tabTemplates.classList.add('hidden');
            tabTriggers.classList.remove('hidden');
            if (btnTriggers) btnTriggers.className = 'main-studio-tab inline-flex items-center gap-2 px-4 py-2.5 text-xs font-bold border-b-2 border-indigo-600 text-indigo-600 dark:text-indigo-400 transition-colors cursor-pointer';
            if (btnTemplates) btnTemplates.className = 'main-studio-tab inline-flex items-center gap-2 px-4 py-2.5 text-xs font-bold border-b-2 border-transparent text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-200 transition-colors cursor-pointer';
        }

        if (window.lucide) lucide.createIcons();
    }

    function setEntityFilter(entity, btn) {
        activeEntityFilter = entity;
        document.querySelectorAll('.entity-filter-btn').forEach(b => {
            b.classList.remove('bg-white', 'dark:bg-zinc-700', 'text-zinc-900', 'dark:text-white', 'shadow-xs');
            b.classList.add('text-zinc-600', 'dark:text-zinc-400');
        });
        if (btn) {
            btn.classList.add('bg-white', 'dark:bg-zinc-700', 'text-zinc-900', 'dark:text-white', 'shadow-xs');
            btn.classList.remove('text-zinc-600', 'dark:text-zinc-400');
        }
        filterEmailTemplates();
    }

    function setViewMode(mode) {
        const listView = document.getElementById('templates-list-view');
        const gridView = document.getElementById('templates-grid-view');
        const listBtn = document.getElementById('view-mode-list-btn');
        const gridBtn = document.getElementById('view-mode-grid-btn');

        if (!listView || !gridView) return;

        if (mode === 'list') {
            listView.classList.remove('hidden');
            gridView.classList.add('hidden');
            if (listBtn) listBtn.className = 'p-1.5 rounded-md bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white shadow-xs cursor-pointer';
            if (gridBtn) gridBtn.className = 'p-1.5 rounded-md text-zinc-500 hover:text-zinc-900 dark:hover:text-white cursor-pointer';
        } else {
            listView.classList.add('hidden');
            gridView.classList.remove('hidden');
            if (gridBtn) gridBtn.className = 'p-1.5 rounded-md bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white shadow-xs cursor-pointer';
            if (listBtn) listBtn.className = 'p-1.5 rounded-md text-zinc-500 hover:text-zinc-900 dark:hover:text-white cursor-pointer';
        }
        if (window.lucide) lucide.createIcons();
    }

    function filterEmailTemplates() {
        const query = (document.getElementById('template-search-input')?.value || '').toLowerCase().trim();
        const category = document.getElementById('template-category-filter')?.value || 'all';

        const elements = document.querySelectorAll('.template-row, .template-card');
        elements.forEach(el => {
            const elEntity = el.dataset.entity;
            const elCat = el.dataset.category;
            const elName = el.dataset.name || '';
            const elSub = el.dataset.subject || '';

            const matchesEntity = (activeEntityFilter === 'all' || elEntity === activeEntityFilter || elEntity === 'global');
            const matchesCategory = (category === 'all' || elCat === category);
            const matchesQuery = (!query || elName.includes(query) || elSub.includes(query));

            if (matchesEntity && matchesCategory && matchesQuery) {
                el.style.display = '';
            } else {
                el.style.display = 'none';
            }
        });
    }

    function clearTemplateSearch() {
        const input = document.getElementById('template-search-input');
        if (input) {
            input.value = '';
            filterEmailTemplates();
        }
    }

    function filterTriggerRules() {
        const query = (document.getElementById('rule-search-input')?.value || '').toLowerCase().trim();
        const module = document.getElementById('rule-module-filter')?.value || 'all';

        document.querySelectorAll('.rule-row').forEach(row => {
            const rMod = row.dataset.module;
            const rName = row.dataset.name || '';

            const matchesModule = (module === 'all' || rMod === module);
            const matchesQuery = (!query || rName.includes(query));

            if (matchesModule && matchesQuery) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    function updateMergeTagAssistant() {
        const entity = document.getElementById('form-tmpl-entity')?.value || 'leads';
        const tags = (MERGE_TAGS[entity] || []).concat(MERGE_TAGS.global || []);
        const container = document.getElementById('merge-tags-container');
        if (!container) return;

        container.innerHTML = tags.map(t => `
            <button
                type="button"
                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-mono font-medium bg-white dark:bg-zinc-800 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 transition-colors shadow-2xs cursor-pointer"
                onclick="insertMergeTag('{{${t.tag}}}')"
                title="Click to insert into email body or subject"
            >
                <span>{{${t.tag}}}</span>
            </button>
        `).join('');
    }

    function insertMergeTag(tag) {
        const subjectInput = document.getElementById('form-tmpl-subject');
        const visualCanvas = document.getElementById('visual-canvas');
        const htmlCanvas = document.getElementById('html-canvas');

        // If subject is focused, insert into subject
        if (document.activeElement === subjectInput && subjectInput) {
            const start = subjectInput.selectionStart || 0;
            const end = subjectInput.selectionEnd || 0;
            const val = subjectInput.value;
            subjectInput.value = val.substring(0, start) + tag + val.substring(end);
            subjectInput.focus();
            subjectInput.setSelectionRange(start + tag.length, start + tag.length);
        } else if (htmlCanvas && !htmlCanvas.classList.contains('hidden')) {
            const start = htmlCanvas.selectionStart || 0;
            const end = htmlCanvas.selectionEnd || 0;
            const val = htmlCanvas.value;
            htmlCanvas.value = val.substring(0, start) + tag + val.substring(end);
            htmlCanvas.focus();
        } else if (visualCanvas) {
            // Insert into visual contenteditable
            visualCanvas.focus();
            document.execCommand('insertText', false, tag);
        }

        if (window.showSpaToast) {
            window.showSpaToast(`Inserted merge tag ${tag}`, 'info');
        }
    }

    function toggleEditorMode(mode) {
        const visualCanvas = document.getElementById('visual-canvas');
        const htmlCanvas = document.getElementById('html-canvas');
        const visualBtn = document.getElementById('editor-mode-visual-btn');
        const htmlBtn = document.getElementById('editor-mode-html-btn');
        const toolbar = document.getElementById('rich-editor-toolbar');

        if (!visualCanvas || !htmlCanvas) return;

        if (mode === 'html') {
            htmlCanvas.value = visualCanvas.innerHTML;
            visualCanvas.classList.add('hidden');
            htmlCanvas.classList.remove('hidden');
            if (toolbar) toolbar.classList.add('opacity-40', 'pointer-events-none');
            if (htmlBtn) htmlBtn.className = 'px-2 py-0.5 rounded font-bold bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white shadow-xs cursor-pointer';
            if (visualBtn) visualBtn.className = 'px-2 py-0.5 rounded font-bold text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white cursor-pointer';
        } else {
            visualCanvas.innerHTML = htmlCanvas.value;
            htmlCanvas.classList.add('hidden');
            visualCanvas.classList.remove('hidden');
            if (toolbar) toolbar.classList.remove('opacity-40', 'pointer-events-none');
            if (visualBtn) visualBtn.className = 'px-2 py-0.5 rounded font-bold bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white shadow-xs cursor-pointer';
            if (htmlBtn) htmlBtn.className = 'px-2 py-0.5 rounded font-bold text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white cursor-pointer';
        }
    }

    function execEditorCommand(command, value = null) {
        const visualCanvas = document.getElementById('visual-canvas');
        if (visualCanvas) {
            visualCanvas.focus();
            document.execCommand(command, false, value);
        }
    }

    function insertLinkPrompt() {
        const url = prompt('Enter link URL (e.g. https://example.com):');
        if (url) {
            execEditorCommand('createLink', url);
        }
    }

    // Modal Control & Form Handling
    function openCreateTemplateModal() {
        const titleEl = document.getElementById('tmpl-modal-title');
        if (titleEl) titleEl.innerText = 'Create Email Template';

        const formId = document.getElementById('form-tmpl-id');
        const formName = document.getElementById('form-tmpl-name');
        const formCat = document.getElementById('form-tmpl-category');
        const formEntity = document.getElementById('form-tmpl-entity');
        const formSubject = document.getElementById('form-tmpl-subject');
        const visualCanvas = document.getElementById('visual-canvas');
        const htmlCanvas = document.getElementById('html-canvas');
        const formActive = document.getElementById('form-tmpl-active');

        if (formId) formId.value = '';
        if (formName) formName.value = '';
        if (formCat) formCat.value = 'sales';
        if (formEntity) formEntity.value = 'leads';
        if (formSubject) formSubject.value = 'Welcome to DealSync, {{first_name}} — Next Steps for {{company}}';
        if (visualCanvas) visualCanvas.innerHTML = '<p>Hi <strong>{{first_name}}</strong>,</p><p>Thank you for expressing interest in DealSync CRM. We are excited about the prospect of partnering with <strong>{{company}}</strong>.</p><p>Warm regards,<br/><strong>{{assigned_to}}</strong></p>';
        if (htmlCanvas && visualCanvas) htmlCanvas.value = visualCanvas.innerHTML;
        if (formActive) formActive.checked = true;

        updateMergeTagAssistant();

        const modal = document.getElementById('template-modal');
        if (modal) {
            modal.style.display = 'flex';
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    }

    function openEditTemplateModal(tmpl) {
        const titleEl = document.getElementById('tmpl-modal-title');
        if (titleEl) titleEl.innerText = 'Edit Email Template';

        const formId = document.getElementById('form-tmpl-id');
        const formName = document.getElementById('form-tmpl-name');
        const formCat = document.getElementById('form-tmpl-category');
        const formEntity = document.getElementById('form-tmpl-entity');
        const formSubject = document.getElementById('form-tmpl-subject');
        const visualCanvas = document.getElementById('visual-canvas');
        const htmlCanvas = document.getElementById('html-canvas');
        const formActive = document.getElementById('form-tmpl-active');

        if (formId) formId.value = tmpl.id;
        if (formName) formName.value = tmpl.name;
        if (formCat) formCat.value = tmpl.category || 'general';
        if (formEntity) formEntity.value = tmpl.entity_type;
        if (formSubject) formSubject.value = tmpl.subject;
        if (visualCanvas) visualCanvas.innerHTML = tmpl.body_html || '';
        if (htmlCanvas) htmlCanvas.value = tmpl.body_html || '';
        if (formActive) formActive.checked = Boolean(tmpl.is_active);

        updateMergeTagAssistant();

        const modal = document.getElementById('template-modal');
        if (modal) {
            modal.style.display = 'flex';
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    }

    function closeTemplateModal() {
        const modal = document.getElementById('template-modal');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    async function handleTemplateFormSubmit(e) {
        e.preventDefault();
        const id = document.getElementById('form-tmpl-id')?.value;
        const btn = document.getElementById('save-template-btn');
        if (btn) {
            btn.disabled = true;
            btn.innerText = 'Saving...';
        }

        const htmlCanvas = document.getElementById('html-canvas');
        const visualCanvas = document.getElementById('visual-canvas');
        const bodyHtml = (htmlCanvas && !htmlCanvas.classList.contains('hidden'))
            ? htmlCanvas.value
            : (visualCanvas ? visualCanvas.innerHTML : '');

        const payload = {
            name: document.getElementById('form-tmpl-name')?.value || '',
            category: document.getElementById('form-tmpl-category')?.value || 'sales',
            entity_type: document.getElementById('form-tmpl-entity')?.value || 'leads',
            subject: document.getElementById('form-tmpl-subject')?.value || '',
            body_html: bodyHtml,
            body_text: bodyHtml.replace(/<[^>]*>?/gm, ''),
            is_active: document.getElementById('form-tmpl-active')?.checked ?? true
        };

        try {
            const url = id ? `/admin/email-templates/${id}` : '/admin/email-templates';
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
                closeTemplateModal();
                if (window.notifyEmailTemplateEvent) {
                    window.notifyEmailTemplateEvent(id ? 'updated' : 'created', payload.name);
                } else if (window.showSpaToast) {
                    window.showSpaToast(data.message, 'success');
                }
                setTimeout(() => window.location.reload(), 700);
            } else {
                if (window.showSpaToast) window.showSpaToast(data.message || 'Error saving template', 'error');
            }
        } catch (err) {
            if (window.showSpaToast) window.showSpaToast('Network error saving template', 'error');
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.innerText = 'Save Email Template';
            }
        }
    }

    async function toggleTemplateActive(id, btn) {
        try {
            const isCurrentlyActive = btn.innerText.includes('Active');
            const res = await fetch(`/admin/email-templates/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                },
                body: JSON.stringify({ is_active: !isCurrentlyActive })
            });
            const data = await res.json();
            if (data.success) {
                const isActive = !isCurrentlyActive;
                btn.innerText = isActive ? 'Active' : 'Inactive';
                btn.className = `inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold transition-colors cursor-pointer ${
                    isActive
                        ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800'
                        : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700'
                }`;
                if (window.showSpaToast) window.showSpaToast(`Template status changed to ${btn.innerText}`, 'success');
            }
        } catch (err) {
            if (window.showSpaToast) window.showSpaToast('Failed to toggle template status', 'error');
        }
    }

    async function cloneTemplate(id) {
        try {
            const res = await fetch(`/admin/email-templates/${id}/clone`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                }
            });
            const data = await res.json();
            if (data.success) {
                if (window.notifyEmailTemplateEvent) {
                    window.notifyEmailTemplateEvent('cloned', data.data.name);
                } else if (window.showSpaToast) {
                    window.showSpaToast(data.message, 'success');
                }
                setTimeout(() => window.location.reload(), 700);
            }
        } catch (err) {
            if (window.showSpaToast) window.showSpaToast('Failed to clone template', 'error');
        }
    }

    async function deleteTemplate(id) {
        if (!confirm('Are you sure you want to delete this email template?')) return;
        try {
            const res = await fetch(`/admin/email-templates/${id}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                }
            });
            const data = await res.json();
            if (data.success) {
                document.querySelectorAll(`[data-id="${id}"]`).forEach(el => el.remove());
                if (window.notifyEmailTemplateEvent) {
                    window.notifyEmailTemplateEvent('deleted', 'Selected Template');
                } else if (window.showSpaToast) {
                    window.showSpaToast(data.message, 'success');
                }
            }
        } catch (err) {
            if (window.showSpaToast) window.showSpaToast('Failed to delete template', 'error');
        }
    }

    // Test Send Modal & Real-time Render
    async function openTestSendModal(tmpl) {
        currentTestingTemplate = tmpl;
        const subTitleEl = document.getElementById('test-modal-subtitle');
        if (subTitleEl) subTitleEl.innerText = `Simulate recipient delivery for "${tmpl.name}"`;

        try {
            const res = await fetch(`/admin/email-templates/${tmpl.id}/render`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                },
                body: JSON.stringify({ recordData: {} })
            });
            const data = await res.json();
            if (data.success) {
                const subPreview = document.getElementById('test-preview-subject');
                const bodyPreview = document.getElementById('test-preview-body');
                if (subPreview) subPreview.innerText = data.data.subject;
                if (bodyPreview) bodyPreview.innerHTML = data.data.body_html;
            }
        } catch (_) {
            const subPreview = document.getElementById('test-preview-subject');
            const bodyPreview = document.getElementById('test-preview-body');
            if (subPreview) subPreview.innerText = tmpl.subject;
            if (bodyPreview) bodyPreview.innerHTML = tmpl.body_html;
        }

        const modal = document.getElementById('test-send-modal');
        if (modal) {
            modal.style.display = 'flex';
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    }

    function closeTestSendModal() {
        const modal = document.getElementById('test-send-modal');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    async function dispatchTestEmail() {
        const to = document.getElementById('test-send-email-to')?.value || '';
        const btn = document.getElementById('confirm-test-send-btn');
        if (btn) {
            btn.disabled = true;
            btn.innerText = 'Dispatching...';
        }

        try {
            const res = await fetch('/admin/email-templates/send', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                },
                body: JSON.stringify({
                    to: to,
                    subject: document.getElementById('test-preview-subject')?.innerText || '',
                    body_html: document.getElementById('test-preview-body')?.innerHTML || ''
                })
            });
            const data = await res.json();
            if (data.success) {
                closeTestSendModal();
                if (window.notifyEmailTemplateEvent) {
                    window.notifyEmailTemplateEvent('test_sent', currentTestingTemplate?.name || 'Template');
                } else if (window.showSpaToast) {
                    window.showSpaToast(data.message, 'success');
                }
            }
        } catch (err) {
            if (window.showSpaToast) window.showSpaToast('Failed to dispatch test email', 'error');
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = `<i data-lucide="send" class="w-3.5 h-3.5"></i><span>Send Test Email</span>`;
            }
            if (window.lucide) lucide.createIcons();
        }
    }

    // Automation Rules Functions
    function openCreateRuleModal() {
        const titleEl = document.getElementById('rule-modal-title');
        if (titleEl) titleEl.innerText = 'Create Automated Email Trigger';

        const idEl = document.getElementById('form-rule-id');
        const nameEl = document.getElementById('form-rule-name');
        const modEl = document.getElementById('form-rule-module');
        const offValEl = document.getElementById('form-rule-offset-val');
        const offUnitEl = document.getElementById('form-rule-offset-unit');
        const offDirEl = document.getElementById('form-rule-offset-dir');
        const dateEl = document.getElementById('form-rule-date-field');
        const critFieldEl = document.getElementById('form-rule-crit-field');
        const critOpEl = document.getElementById('form-rule-crit-op');
        const critValEl = document.getElementById('form-rule-crit-val');
        const recipEl = document.getElementById('form-rule-recipient-type');
        const ccEl = document.getElementById('form-rule-cc');

        if (idEl) idEl.value = '';
        if (nameEl) nameEl.value = '';
        if (modEl) modEl.value = 'opportunities';
        if (offValEl) offValEl.value = '2';
        if (offUnitEl) offUnitEl.value = 'days';
        if (offDirEl) offDirEl.value = 'before';
        if (dateEl) dateEl.value = 'expected_close_date';
        if (critFieldEl) critFieldEl.value = 'stage';
        if (critOpEl) critOpEl.value = 'equals';
        if (critValEl) critValEl.value = 'Proposal / Quote';
        if (recipEl) recipEl.value = 'record_owner';
        if (ccEl) ccEl.value = '';

        const modal = document.getElementById('rule-modal');
        if (modal) {
            modal.style.display = 'flex';
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    }

    function openEditRuleModal(rule) {
        const titleEl = document.getElementById('rule-modal-title');
        if (titleEl) titleEl.innerText = 'Edit Automated Email Trigger';

        const idEl = document.getElementById('form-rule-id');
        const nameEl = document.getElementById('form-rule-name');
        const modEl = document.getElementById('form-rule-module');
        const tmplIdEl = document.getElementById('form-rule-template-id');
        const offValEl = document.getElementById('form-rule-offset-val');
        const offUnitEl = document.getElementById('form-rule-offset-unit');
        const offDirEl = document.getElementById('form-rule-offset-dir');
        const dateEl = document.getElementById('form-rule-date-field');
        const critFieldEl = document.getElementById('form-rule-crit-field');
        const critOpEl = document.getElementById('form-rule-crit-op');
        const critValEl = document.getElementById('form-rule-crit-val');
        const recipEl = document.getElementById('form-rule-recipient-type');
        const ccEl = document.getElementById('form-rule-cc');

        if (idEl) idEl.value = rule.id;
        if (nameEl) nameEl.value = rule.name;
        if (modEl) modEl.value = rule.module;
        if (tmplIdEl) tmplIdEl.value = rule.email_template_id;
        if (offValEl) offValEl.value = rule.offset_value || 2;
        if (offUnitEl) offUnitEl.value = rule.offset_unit || 'days';
        if (offDirEl) offDirEl.value = rule.offset_direction || 'before';
        if (dateEl) dateEl.value = rule.date_field || 'expected_close_date';
        if (critFieldEl) critFieldEl.value = rule.criteria_field || '';
        if (critOpEl) critOpEl.value = rule.criteria_operator || 'equals';
        if (critValEl) critValEl.value = rule.criteria_value || '';
        if (recipEl) recipEl.value = rule.recipient_type || 'record_owner';
        if (ccEl) ccEl.value = rule.cc_emails || '';

        const modal = document.getElementById('rule-modal');
        if (modal) {
            modal.style.display = 'flex';
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    }

    function closeRuleModal() {
        const modal = document.getElementById('rule-modal');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    async function handleRuleFormSubmit(e) {
        e.preventDefault();
        const id = document.getElementById('form-rule-id')?.value;
        const btn = document.getElementById('save-rule-btn');
        if (btn) {
            btn.disabled = true;
            btn.innerText = 'Saving...';
        }

        const payload = {
            name: document.getElementById('form-rule-name')?.value || '',
            module: document.getElementById('form-rule-module')?.value || 'opportunities',
            email_template_id: document.getElementById('form-rule-template-id')?.value || '',
            offset_value: parseInt(document.getElementById('form-rule-offset-val')?.value || '2', 10),
            offset_unit: document.getElementById('form-rule-offset-unit')?.value || 'days',
            offset_direction: document.getElementById('form-rule-offset-dir')?.value || 'before',
            date_field: document.getElementById('form-rule-date-field')?.value || 'expected_close_date',
            criteria_field: document.getElementById('form-rule-crit-field')?.value || '',
            criteria_operator: document.getElementById('form-rule-crit-op')?.value || 'equals',
            criteria_value: document.getElementById('form-rule-crit-val')?.value || '',
            recipient_type: document.getElementById('form-rule-recipient-type')?.value || 'record_owner',
            cc_emails: document.getElementById('form-rule-cc')?.value || '',
            is_active: true
        };

        try {
            const url = id ? `/admin/email-templates/rules/${id}` : '/admin/email-templates/rules';
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
                closeRuleModal();
                if (window.showSpaToast) window.showSpaToast(data.message, 'success');
                setTimeout(() => window.location.reload(), 700);
            } else {
                if (window.showSpaToast) window.showSpaToast(data.message || 'Error saving rule', 'error');
            }
        } catch (err) {
            if (window.showSpaToast) window.showSpaToast('Network error saving rule', 'error');
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.innerText = 'Save Automation Trigger';
            }
        }
    }

    async function toggleEmailRuleActive(id, btn) {
        try {
            const res = await fetch(`/admin/email-templates/rules/${id}/toggle`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                }
            });
            const data = await res.json();
            if (data.success) {
                const isActive = Boolean(data.data.is_active);
                btn.innerText = isActive ? 'Active' : 'Inactive';
                btn.className = `inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold transition-colors cursor-pointer ${
                    isActive
                        ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800'
                        : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700'
                }`;
                if (window.showSpaToast) window.showSpaToast(data.message, 'success');
            }
        } catch (err) {
            if (window.showSpaToast) window.showSpaToast('Failed to toggle rule state', 'error');
        }
    }

    async function deleteEmailRule(id) {
        if (!confirm('Are you sure you want to delete this automated trigger rule?')) return;
        try {
            const res = await fetch(`/admin/email-templates/rules/${id}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                }
            });
            const data = await res.json();
            if (data.success) {
                document.querySelector(`.rule-row[data-id="${id}"]`)?.remove();
                if (window.showSpaToast) window.showSpaToast(data.message, 'success');
            }
        } catch (err) {
            if (window.showSpaToast) window.showSpaToast('Failed to delete rule', 'error');
        }
    }

    async function evaluateScheduledEmailTriggers() {
        if (window.showSpaToast) window.showSpaToast('Evaluating scheduled time triggers...', 'info');
        try {
            const res = await fetch('/admin/email-templates/rules/evaluate-scheduled', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                }
            });
            const data = await res.json();
            if (data.success) {
                if (window.notifyEmailTemplateEvent) {
                    window.notifyEmailTemplateEvent('evaluated', `${data.processed} Triggers Checked`, {
                        sound: 'fanfare'
                    });
                } else if (window.showSpaToast) {
                    window.showSpaToast(data.message, 'success');
                }
            }
        } catch (err) {
            if (window.showSpaToast) window.showSpaToast('Evaluation request completed.', 'info');
        }
    }

    function triggerRuleEvaluationTest(rule) {
        if (window.showNotification) {
            window.showNotification({
                type: 'info',
                title: `Automated Trigger: ${rule.name}`,
                message: `Offset: ${rule.offset_value} ${rule.offset_unit} ${rule.offset_direction} ${rule.date_field}. Criteria matched successfully!`,
                module: (rule.module || 'SYSTEM').toUpperCase(),
                actions: ['view_details', 'download'],
                data: {
                    rule_id: rule.id,
                    rule_name: rule.name,
                    module: rule.module,
                    offset: `${rule.offset_value} ${rule.offset_unit} ${rule.offset_direction}`,
                    date_field: rule.date_field,
                    criteria: `${rule.criteria_field} ${rule.criteria_operator} ${rule.criteria_value}`,
                    timestamp: new Date().toISOString()
                }
            });
        }
    }

    function initEmailTemplatesStudio() {
        if (window.lucide) {
            lucide.createIcons();
        }
        updateMergeTagAssistant();
        filterEmailTemplates();
    }

    // Expose all interactive functions to window for onclick handlers & SPA
    window.MERGE_TAGS = MERGE_TAGS;
    window.switchMainStudioTab = switchMainStudioTab;
    window.setEntityFilter = setEntityFilter;
    window.setViewMode = setViewMode;
    window.filterEmailTemplates = filterEmailTemplates;
    window.clearTemplateSearch = clearTemplateSearch;
    window.filterTriggerRules = filterTriggerRules;
    window.updateMergeTagAssistant = updateMergeTagAssistant;
    window.insertMergeTag = insertMergeTag;
    window.toggleEditorMode = toggleEditorMode;
    window.execEditorCommand = execEditorCommand;
    window.insertLinkPrompt = insertLinkPrompt;
    window.openCreateTemplateModal = openCreateTemplateModal;
    window.openEditTemplateModal = openEditTemplateModal;
    window.closeTemplateModal = closeTemplateModal;
    window.handleTemplateFormSubmit = handleTemplateFormSubmit;
    window.toggleTemplateActive = toggleTemplateActive;
    window.cloneTemplate = cloneTemplate;
    window.deleteTemplate = deleteTemplate;
    window.openTestSendModal = openTestSendModal;
    window.closeTestSendModal = closeTestSendModal;
    window.dispatchTestEmail = dispatchTestEmail;
    window.openCreateRuleModal = openCreateRuleModal;
    window.openEditRuleModal = openEditRuleModal;
    window.closeRuleModal = closeRuleModal;
    window.handleRuleFormSubmit = handleRuleFormSubmit;
    window.toggleEmailRuleActive = toggleEmailRuleActive;
    window.deleteEmailRule = deleteEmailRule;
    window.evaluateScheduledEmailTriggers = evaluateScheduledEmailTriggers;
    window.triggerRuleEvaluationTest = triggerRuleEvaluationTest;
    window.initEmailTemplatesStudio = initEmailTemplatesStudio;

    // Auto-init on load or if already ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initEmailTemplatesStudio);
    } else {
        initEmailTemplatesStudio();
    }
})();
