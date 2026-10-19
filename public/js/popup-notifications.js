/**
 * DealSync Enterprise PopUp Toast & Audio Notification Engine (1:1 React CRM Parity)
 * Zero-dependency Web Audio API sound synthesis & Tailwind CSS toast container
 */

(function () {
    // 1. Audio Chime Synthesizer (Zero-dependency Web Audio API)
    window.playChimeAlert = function (soundType = 'chime') {
        try {
            const AudioCtx = window.AudioContext || window.webkitAudioContext;
            if (!AudioCtx) return;
            const ctx = new AudioCtx();
            const now = ctx.currentTime;

            if (soundType === 'fanfare') {
                // Triumphant ascending fanfare chord
                const notes = [523.25, 659.25, 783.99, 1046.50]; // C5, E5, G5, C6
                notes.forEach((freq, idx) => {
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    const t = now + (idx * 0.12);
                    osc.type = 'triangle';
                    osc.frequency.setValueAtTime(freq, t);
                    gain.gain.setValueAtTime(0, t);
                    gain.gain.linearRampToValueAtTime(0.2, t + 0.04);
                    gain.gain.exponentialRampToValueAtTime(0.001, t + 0.6);
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.start(t);
                    osc.stop(t + 0.65);
                });
                return;
            }

            if (soundType === 'alert') {
                // Warning / Alert double ping
                [880, 700].forEach((freq, idx) => {
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    const t = now + (idx * 0.15);
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(freq, t);
                    gain.gain.setValueAtTime(0, t);
                    gain.gain.linearRampToValueAtTime(0.3, t + 0.03);
                    gain.gain.exponentialRampToValueAtTime(0.001, t + 0.5);
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.start(t);
                    osc.stop(t + 0.55);
                });
                return;
            }

            // Default harmonious 3-tone chime (D5 -> A5 -> C#6)
            // Tone 1: D5 (587.33 Hz)
            const osc1 = ctx.createOscillator();
            const gain1 = ctx.createGain();
            osc1.type = 'sine';
            osc1.frequency.setValueAtTime(587.33, now);
            gain1.gain.setValueAtTime(0, now);
            gain1.gain.linearRampToValueAtTime(0.25, now + 0.04);
            gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.8);
            osc1.connect(gain1);
            gain1.connect(ctx.destination);
            osc1.start(now);
            osc1.stop(now + 0.85);

            // Tone 2: A5 (880 Hz, +180ms)
            const osc2 = ctx.createOscillator();
            const gain2 = ctx.createGain();
            osc2.type = 'sine';
            osc2.frequency.setValueAtTime(880, now + 0.18);
            gain2.gain.setValueAtTime(0, now + 0.18);
            gain2.gain.linearRampToValueAtTime(0.35, now + 0.22);
            gain2.gain.exponentialRampToValueAtTime(0.001, now + 1.2);
            osc2.connect(gain2);
            gain2.connect(ctx.destination);
            osc2.start(now + 0.18);
            osc2.stop(now + 1.25);

            // Tone 3: C#6 (1108.73 Hz, +360ms)
            const osc3 = ctx.createOscillator();
            const gain3 = ctx.createGain();
            osc3.type = 'sine';
            osc3.frequency.setValueAtTime(1108.73, now + 0.36);
            gain3.gain.setValueAtTime(0, now + 0.36);
            gain3.gain.linearRampToValueAtTime(0.2, now + 0.4);
            gain3.gain.exponentialRampToValueAtTime(0.001, now + 1.5);
            osc3.connect(gain3);
            gain3.connect(ctx.destination);
            osc3.start(now + 0.36);
            osc3.stop(now + 1.55);
        } catch (err) {
            console.warn('Audio chime playback was prevented by browser policy:', err);
        }
    };

    // 2. Tone Styling & Icon Configuration (Tailwind CSS 1:1 React Parity)
    const TONE_CONFIG = {
        success: {
            containerClass: 'bg-emerald-50/95 dark:bg-emerald-950/90 border-emerald-300 dark:border-emerald-800 text-emerald-950 dark:text-emerald-50',
            titleClass: 'text-emerald-900 dark:text-emerald-100',
            bodyClass: 'text-emerald-800/90 dark:text-emerald-200/90',
            icon: 'check-circle-2',
            iconColor: 'text-emerald-600 dark:text-emerald-400',
            progressBar: 'bg-emerald-500',
            badgeClass: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800',
            chimeSound: 'chime'
        },
        error: {
            containerClass: 'bg-red-50/95 dark:bg-red-950/90 border-red-300 dark:border-red-800 text-red-950 dark:text-red-50',
            titleClass: 'text-red-900 dark:text-red-100',
            bodyClass: 'text-red-800/90 dark:text-red-200/90',
            icon: 'x-circle',
            iconColor: 'text-red-600 dark:text-red-400',
            progressBar: 'bg-red-500',
            badgeClass: 'bg-red-100 text-red-800 dark:bg-red-900/60 dark:text-red-300 border-red-200 dark:border-red-800',
            chimeSound: 'alert'
        },
        warning: {
            containerClass: 'bg-amber-50/95 dark:bg-amber-950/90 border-amber-300 dark:border-amber-800 text-amber-950 dark:text-amber-50',
            titleClass: 'text-amber-900 dark:text-amber-100',
            bodyClass: 'text-amber-800/90 dark:text-amber-200/90',
            icon: 'alert-triangle',
            iconColor: 'text-amber-600 dark:text-amber-400',
            progressBar: 'bg-amber-500',
            badgeClass: 'bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-300 border-amber-200 dark:border-amber-800',
            chimeSound: 'alert'
        },
        info: {
            containerClass: 'bg-blue-50/95 dark:bg-blue-950/90 border-blue-300 dark:border-blue-800 text-blue-950 dark:text-blue-50',
            titleClass: 'text-blue-900 dark:text-blue-100',
            bodyClass: 'text-blue-800/90 dark:text-blue-200/90',
            icon: 'info',
            iconColor: 'text-blue-600 dark:text-blue-400',
            progressBar: 'bg-blue-500',
            badgeClass: 'bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-300 border-blue-200 dark:border-blue-800',
            chimeSound: 'chime'
        }
    };

    // 3. Action Buttons Definitions
    const ACTION_DEFINITIONS = {
        view_record: {
            label: 'View Record',
            icon: 'external-link',
            class: 'bg-white hover:bg-zinc-50 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-800 dark:text-zinc-100 border-zinc-300 dark:border-zinc-700'
        },
        view_details: {
            label: 'View Details',
            icon: 'file-text',
            class: 'bg-white hover:bg-indigo-50 dark:bg-zinc-800 dark:hover:bg-indigo-950 text-indigo-700 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800'
        },
        download: {
            label: 'Download',
            icon: 'download',
            class: 'bg-white hover:bg-teal-50 dark:bg-zinc-800 dark:hover:bg-teal-950 text-teal-700 dark:text-teal-300 border-teal-200 dark:border-teal-800'
        },
        snooze: {
            label: 'Snooze',
            icon: 'clock',
            class: 'bg-white hover:bg-zinc-100 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-600 dark:text-zinc-300 border-zinc-300 dark:border-zinc-600'
        },
        approve: {
            label: 'Approve',
            icon: 'check',
            class: 'bg-emerald-600 hover:bg-emerald-700 text-white border-emerald-700 font-semibold shadow-sm'
        },
        reject: {
            label: 'Reject',
            icon: 'ban',
            class: 'bg-red-600 hover:bg-red-700 text-white border-red-700 font-semibold shadow-sm'
        },
        retry: {
            label: 'Retry',
            icon: 'refresh-cw',
            class: 'bg-white hover:bg-red-50 dark:bg-zinc-800 dark:hover:bg-red-950 text-red-600 dark:text-red-400 border-red-200 dark:border-red-800'
        },
        undo: {
            label: 'Undo',
            icon: 'undo-2',
            class: 'bg-white hover:bg-sky-50 dark:bg-zinc-800 dark:hover:bg-sky-950 text-sky-700 dark:text-sky-300 border-sky-200 dark:border-sky-800'
        }
    };

    // Multi-position container cache
    function getContainer(position = 'top-right') {
        const id = `dealsync-toasts-${position}`;
        let container = document.getElementById(id);
        if (!container) {
            container = document.createElement('div');
            container.id = id;
            container.setAttribute('aria-live', 'polite');

            let posClasses = '';
            switch (position) {
                case 'top-center':
                    posClasses = 'fixed top-5 left-1/2 -translate-x-1/2 z-[20000] flex flex-col items-center pointer-events-none';
                    break;
                case 'bottom-right':
                    posClasses = 'fixed bottom-5 right-5 z-[20000] flex flex-col items-end pointer-events-none';
                    break;
                case 'bottom-left':
                    posClasses = 'fixed bottom-5 left-5 z-[20000] flex flex-col items-start pointer-events-none';
                    break;
                case 'top-right':
                default:
                    posClasses = 'fixed top-5 right-5 z-[20000] flex flex-col items-end pointer-events-none';
                    break;
            }

            container.className = posClasses;
            document.body.appendChild(container);
        }
        return container;
    }

    // 4. Core Toast Spawn Function
    window.showNotification = function (options = {}) {
        // If string passed as first param
        if (typeof options === 'string') {
            options = { message: options, title: 'Notification', type: 'info' };
        }

        const type = (options.type && TONE_CONFIG[options.type]) ? options.type : 'info';
        const cfg = TONE_CONFIG[type];
        const title = options.title || (type.charAt(0).toUpperCase() + type.slice(1));
        const message = options.message || '';
        const module = options.module ? options.module.toUpperCase() : null;
        const position = options.position || 'top-right';
        const duration = options.duration_ms !== undefined ? options.duration_ms : 5500;
        const actions = Array.isArray(options.actions) ? options.actions : [];
        const payloadData = options.data || { title, message, type, module, timestamp: new Date().toISOString() };
        const id = `toast-${Date.now()}-${Math.random().toString(36).substring(2, 7)}`;

        // Play chime sound if enabled
        if (options.sound !== false) {
            playChimeAlert(cfg.chimeSound);
        }

        const container = getContainer(position);

        // Toast DOM Element with Tailwind CSS
        const toast = document.createElement('div');
        toast.id = id;
        toast.className = `w-[380px] max-w-[calc(100vw-2rem)] rounded-xl border shadow-xl shadow-black/10 backdrop-blur-md overflow-hidden relative mb-3 pointer-events-auto transition-all duration-300 select-none animate-in fade-in slide-in-from-right-4 ${cfg.containerClass}`;
        toast.style.animation = 'toastSlideInRight 0.28s cubic-bezier(0.16, 1, 0.3, 1)';

        let isPaused = false;
        let remaining = duration;
        let startTime = Date.now();
        let timer = null;

        // Top Progress Bar
        let progressBarHtml = '';
        if (duration > 0) {
            progressBarHtml = `
                <div class="toast-progress-bar absolute top-0 left-0 h-[3.5px] ${cfg.progressBar} w-full transition-none" style="width: 100%;"></div>
            `;
        }

        // Action Buttons HTML
        let actionsHtml = '';
        if (actions.length > 0) {
            actionsHtml = `
                <div class="flex flex-wrap items-center gap-1.5 mt-2.5 ml-6">
                    ${actions.map(actKey => {
                        const def = ACTION_DEFINITIONS[actKey] || {
                            label: actKey.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase()),
                            icon: 'external-link',
                            class: 'bg-white hover:bg-zinc-50 text-zinc-700 border-zinc-300'
                        };
                        return `
                            <button
                                type="button"
                                data-action="${actKey}"
                                class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-medium rounded-md border shadow-xs transition-all duration-150 hover:-translate-y-0.5 active:translate-y-0 ${def.class}"
                            >
                                <i data-lucide="${def.icon}" class="w-3 h-3"></i>
                                <span>${def.label}</span>
                            </button>
                        `;
                    }).join('')}
                </div>
            `;
        }

        toast.innerHTML = `
            ${progressBarHtml}
            <div class="p-3.5">
                <div class="flex items-start justify-between gap-2">
                    <div class="flex items-center gap-2 flex-1 min-w-0">
                        <i data-lucide="${cfg.icon}" class="w-4 h-4 flex-shrink-0 ${cfg.iconColor}"></i>
                        <h4 class="text-[13px] font-bold ${cfg.titleClass} truncate m-0">${escapeHtml(title)}</h4>
                    </div>
                    <div class="flex items-center gap-1.5 flex-shrink-0">
                        ${module ? `<span class="text-[9.5px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded border ${cfg.badgeClass}">${escapeHtml(module)}</span>` : ''}
                        <button type="button" class="toast-dismiss-btn p-1 rounded-md text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 hover:bg-black/5 dark:hover:bg-white/10 transition-colors">
                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                </div>
                <p class="text-[12px] leading-relaxed ${cfg.bodyClass} m-0 mt-1 ml-6 font-normal">
                    ${escapeHtml(message)}
                </p>
                ${actionsHtml}
            </div>
        `;

        container.prepend(toast);

        if (window.lucide) {
            lucide.createIcons({ root: toast });
        }

        // Timer with pause-on-hover
        const progressBarEl = toast.querySelector('.toast-progress-bar');

        const dismiss = () => {
            if (timer) clearInterval(timer);
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(20px)';
            toast.style.transition = 'all 0.25s ease';
            setTimeout(() => {
                toast.remove();
            }, 250);
        };

        toast.querySelector('.toast-dismiss-btn').addEventListener('click', (e) => {
            e.stopPropagation();
            dismiss();
        });

        // Hover pause logic
        toast.addEventListener('mouseenter', () => {
            isPaused = true;
            remaining = Math.max(0, remaining - (Date.now() - startTime));
        });

        toast.addEventListener('mouseleave', () => {
            isPaused = false;
            startTime = Date.now();
        });

        if (duration > 0) {
            const interval = 40;
            timer = setInterval(() => {
                if (isPaused) return;
                const elapsed = Date.now() - startTime;
                const currentRemaining = Math.max(0, remaining - elapsed);
                const pct = (currentRemaining / duration) * 100;

                if (progressBarEl) {
                    progressBarEl.style.width = `${pct}%`;
                }

                if (currentRemaining <= 0) {
                    clearInterval(timer);
                    dismiss();
                }
            }, interval);
        }

        // Action Buttons Handling
        toast.querySelectorAll('button[data-action]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const actionKey = btn.dataset.action;

                if (typeof options.onAction === 'function') {
                    options.onAction(actionKey, payloadData);
                }

                if (actionKey === 'snooze') {
                    dismiss();
                    setTimeout(() => {
                        window.showNotification({
                            type: 'warning',
                            title: `⏰ Snoozed Reminder: ${title}`,
                            message: message,
                            module: module,
                            actions: ['view_details', 'snooze'],
                            data: payloadData,
                            duration_ms: 7000
                        });
                    }, 15000);
                    return;
                }

                if (actionKey === 'download') {
                    try {
                        const jsonStr = JSON.stringify(payloadData, null, 2);
                        const blob = new Blob([jsonStr], { type: 'application/json' });
                        const url = URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = `notification-${Date.now()}.json`;
                        a.click();
                        URL.revokeObjectURL(url);
                    } catch (err) {
                        console.error('Download error:', err);
                    }
                    return;
                }

                if (actionKey === 'view_details') {
                    window.showNotificationDetailsModal({
                        title: title,
                        message: message,
                        type: type,
                        module: module || 'System Object',
                        operation: options.operation || 'MUTATION',
                        timestamp: new Date().toISOString(),
                        data: payloadData
                    });
                    return;
                }

                if (actionKey === 'view_record') {
                    const recordUrl = options.recordUrl || (options.data && options.data.url) || null;
                    if (recordUrl) {
                        window.location.href = recordUrl;
                    } else {
                        dismiss();
                    }
                    return;
                }

                dismiss();
            });
        });

        return id;
    };

    // 5. Operation Diagnostic & Event Details Modal (Tailwind CSS)
    window.showNotificationDetailsModal = function (details) {
        const existingModal = document.getElementById('notification-details-modal');
        if (existingModal) existingModal.remove();

        const modal = document.createElement('div');
        modal.id = 'notification-details-modal';
        modal.className = 'fixed inset-0 bg-black/60 backdrop-blur-sm z-[25000] flex items-center justify-center p-4 animate-in fade-in duration-200';

        const jsonFormatted = details.data ? JSON.stringify(details.data, null, 2) : '// No additional record payload attached';

        modal.innerHTML = `
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl shadow-2xl max-w-xl w-full max-h-[85vh] flex flex-col overflow-hidden animate-in zoom-in-95 duration-200">
                <!-- Modal Header -->
                <div class="flex items-center justify-between px-5 py-4 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900/50">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-lg bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                            <i data-lucide="terminal" class="w-4 h-4"></i>
                        </div>
                        <h3 class="text-sm font-bold text-zinc-900 dark:text-white m-0">Operation Diagnostic &amp; Event Details</h3>
                    </div>
                    <button type="button" class="close-modal-btn text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 p-1 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-5 overflow-y-auto space-y-4 text-xs">
                    <!-- Metadata Summary Grid -->
                    <div class="grid grid-cols-2 gap-3 p-3.5 bg-zinc-50 dark:bg-zinc-800/50 rounded-xl border border-zinc-200 dark:border-zinc-800">
                        <div>
                            <div class="text-[10px] font-bold uppercase tracking-wider text-zinc-500">Operation Type</div>
                            <div class="text-xs font-bold text-zinc-900 dark:text-white mt-0.5">${escapeHtml(details.operation || 'MUTATION')}</div>
                        </div>
                        <div>
                            <div class="text-[10px] font-bold uppercase tracking-wider text-zinc-500">Target Entity / Module</div>
                            <div class="text-xs font-bold text-zinc-900 dark:text-white mt-0.5">${escapeHtml(details.module || 'System Object')}</div>
                        </div>
                        <div>
                            <div class="text-[10px] font-bold uppercase tracking-wider text-zinc-500">Notification Tone</div>
                            <div class="text-xs font-bold capitalize mt-0.5 ${details.type === 'error' ? 'text-red-600' : (details.type === 'warning' ? 'text-amber-600' : 'text-emerald-600')}">${escapeHtml(details.type || 'info')}</div>
                        </div>
                        <div>
                            <div class="text-[10px] font-bold uppercase tracking-wider text-zinc-500">Event Timestamp</div>
                            <div class="text-xs font-semibold text-zinc-600 dark:text-zinc-300 mt-0.5">${new Date(details.timestamp || Date.now()).toLocaleTimeString()} (${new Date().toLocaleDateString()})</div>
                        </div>
                    </div>

                    <!-- Message Banner -->
                    <div>
                        <div class="text-[11px] font-semibold text-zinc-600 dark:text-zinc-400 mb-1">Notification Message</div>
                        <div class="p-3 bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200 dark:border-zinc-800 rounded-lg text-zinc-800 dark:text-zinc-200">
                            <strong>${escapeHtml(details.title)}</strong> &mdash; ${escapeHtml(details.message)}
                        </div>
                    </div>

                    <!-- Payload Inspector with Copy -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-[11px] font-semibold text-zinc-600 dark:text-zinc-400">Record Payload / Metadata Context</span>
                            <button type="button" id="copy-json-btn" class="inline-flex items-center gap-1 text-[11px] font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 cursor-pointer">
                                <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                                <span id="copy-text">Copy JSON</span>
                            </button>
                        </div>
                        <pre class="bg-zinc-950 text-emerald-400 p-3.5 rounded-xl text-[11px] font-mono overflow-x-auto max-h-56 m-0 border border-zinc-800">${escapeHtml(jsonFormatted)}</pre>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="px-5 py-3 border-t border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900/50 flex justify-end">
                    <button type="button" class="close-modal-btn px-4 py-1.5 text-xs font-medium bg-zinc-200 dark:bg-zinc-800 hover:bg-zinc-300 dark:hover:bg-zinc-700 text-zinc-800 dark:text-zinc-100 rounded-lg transition-colors">
                        Close Diagnostic
                    </button>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        if (window.lucide) {
            lucide.createIcons({ root: modal });
        }

        // Close handlers
        modal.querySelectorAll('.close-modal-btn').forEach(btn => {
            btn.addEventListener('click', () => modal.remove());
        });

        modal.addEventListener('click', (e) => {
            if (e.target === modal) modal.remove();
        });

        // Copy button handler
        const copyBtn = modal.querySelector('#copy-json-btn');
        if (copyBtn) {
            copyBtn.addEventListener('click', () => {
                navigator.clipboard.writeText(jsonFormatted).then(() => {
                    const textEl = modal.querySelector('#copy-text');
                    if (textEl) {
                        textEl.innerText = 'Copied!';
                        setTimeout(() => {
                            textEl.innerText = 'Copy JSON';
                        }, 2000);
                    }
                });
            });
        }
    };

    // 6. Global showToast adapter (backward compatible)
    window.showToast = function (message, type = 'success', options = {}) {
        if (typeof options === 'string') {
            options = { title: options };
        }
        return window.showNotification({
            message: message,
            type: type,
            title: options.title || (type === 'error' ? 'Error' : (type === 'warning' ? 'Warning' : 'Success')),
            actions: options.actions || ['view_details'],
            module: options.module || null,
            duration_ms: options.duration || 5500,
            sound: options.sound !== undefined ? options.sound : true,
            data: options.data || null
        });
    };

    // 7. triggerCrmNotification (1:1 React Parity)
    window.triggerCrmNotification = function (options = {}) {
        const operation = options.operation || 'MUTATION';
        const module = options.module || 'Record';
        const type = options.customType || options.type || 'success';
        const title = options.customTitle || options.title || `${operation} Successful`;
        const message = options.customMessage || options.message || `The ${operation.toLowerCase()} operation on ${module} completed.`;
        const actions = options.customActions || options.actions || ['view_record', 'view_details'];

        return window.showNotification({
            type: type,
            title: title,
            message: message,
            module: module,
            actions: actions,
            duration_ms: options.duration_ms || 5500,
            position: options.position || 'top-right',
            data: options.recordData || options.data || null,
            onAction: options.onAction
        });
    };

    function escapeHtml(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = String(str);
        return div.innerHTML;
    }

    // Keyframes for smooth entry
    const styleTag = document.createElement('style');
    styleTag.innerHTML = `
        @keyframes toastSlideInRight {
            from { transform: translateX(110%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes toastSlideInLeft {
            from { transform: translateX(-110%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes toastSlideInDown {
            from { transform: translateY(-110%); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    `;
    document.head.appendChild(styleTag);
})();
