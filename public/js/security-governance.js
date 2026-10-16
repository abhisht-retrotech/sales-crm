/**
 * Security & Access Governance Studio JavaScript Engine
 * Page: resources/views/admin/security_governance.blade.php
 * File: public/js/security-governance.js
 *
 * Controls:
 * - Session Inactivity Auto-Timeouts & Warning Notifications
 * - Password Complexity & Lifecycle Rules + Real-Time Validator
 * - Brute-Force Protections & Account Lockout Simulation
 * - 2FA Tenant-Wide Policies (Optional, Admin-Only, Zero-Trust All)
 * - Seat Quotas, Live Capacity Metering & Instant License Provisioning
 * - Billing & Invoice History Ledger
 */

// ============================================================================
// STATE INITIALIZATION
// ============================================================================
window.currentPolicies = window.currentPolicies || {};
window.currentInvoices = window.currentInvoices || [];
let additionalSeatsCount = 1;
let feedbackTimeout = null;

function initSecurityGovernance() {
    const initialPolicies = window.INITIAL_POLICIES || {};
    const initialSubscription = window.INITIAL_SUBSCRIPTION || {};
    const initialStats = window.INITIAL_STATS || {};

    window.currentPolicies = {
        plan_tier: initialPolicies.plan_tier || initialSubscription.plan_tier || 'enterprise',
        max_seats: parseInt(initialPolicies.max_seats || initialSubscription.seat_count || initialStats.max_seats || 15, 10),
        unit_price_monthly: parseFloat(initialSubscription.unit_price_monthly || 39),
        session_timeout_minutes: parseInt(initialPolicies.session_timeout_minutes !== undefined ? initialPolicies.session_timeout_minutes : 60, 10),
        allow_self_password_change: initialPolicies.allow_self_password_change !== undefined ? Boolean(initialPolicies.allow_self_password_change) : true,
        password_min_length: parseInt(initialPolicies.password_min_length !== undefined ? initialPolicies.password_min_length : 8, 10),
        password_expiry_days: parseInt(initialPolicies.password_expiry_days !== undefined ? initialPolicies.password_expiry_days : 0, 10),
        password_require_uppercase: initialPolicies.password_require_uppercase !== undefined ? Boolean(initialPolicies.password_require_uppercase) : true,
        password_require_numbers: initialPolicies.password_require_numbers !== undefined ? Boolean(initialPolicies.password_require_numbers) : true,
        password_require_special: initialPolicies.password_require_special !== undefined ? Boolean(initialPolicies.password_require_special) : true,
        max_failed_login_attempts: parseInt(initialPolicies.max_failed_login_attempts !== undefined ? initialPolicies.max_failed_login_attempts : 5, 10),
        lockout_duration_minutes: parseInt(initialPolicies.lockout_duration_minutes !== undefined ? initialPolicies.lockout_duration_minutes : 30, 10),
        two_factor_policy: initialPolicies.two_factor_policy || 'optional',
        active_users: parseInt(initialStats.active_users !== undefined ? initialStats.active_users : 4, 10),
        pending_invitations: parseInt(initialStats.pending_invitations !== undefined ? initialStats.pending_invitations : 2, 10),
        payment_method_brand: initialSubscription.payment_method_brand || 'Visa',
        payment_method_last4: initialSubscription.payment_method_last4 || '4242'
    };

    window.currentInvoices = Array.isArray(window.INITIAL_INVOICES) ? [...window.INITIAL_INVOICES] : [];

    populateFormValues();
    updateCapacityMeters();
    initPasswordSimulator();
    initInactivityHelper();

    if (window.lucide) {
        lucide.createIcons();
    }
}

// ============================================================================
// FORM POPULATION & CAPACITY METERS
// ============================================================================
function populateFormValues() {
    const cp = window.currentPolicies;
    const setSafeText = (id, val) => {
        const el = document.getElementById(id);
        if (el) el.textContent = val;
    };
    const setSafeVal = (id, val) => {
        const el = document.getElementById(id);
        if (el) el.value = val;
    };
    const setSafeCheck = (id, val) => {
        const el = document.getElementById(id);
        if (el) el.checked = Boolean(val);
    };

    setSafeText('headerPlanTier', cp.plan_tier);
    setSafeText('planTierDisplay', (cp.plan_tier || 'enterprise').toUpperCase());
    setSafeText('unitPriceDisplay', cp.unit_price_monthly);
    setSafeVal('superAdminSeatsInput', cp.max_seats);
    setSafeText('stripeTier', `${capitalize(cp.plan_tier)} Plan`);
    setSafeText('stripePrice', `$${cp.unit_price_monthly} USD / user / mo`);

    setSafeVal('sessionTimeoutMinutes', cp.session_timeout_minutes);
    setSafeCheck('allowSelfPasswordChange', cp.allow_self_password_change);

    setSafeVal('passwordMinLength', cp.password_min_length);
    setSafeVal('passwordExpiryDays', cp.password_expiry_days);
    setSafeCheck('passwordRequireUppercase', cp.password_require_uppercase);
    setSafeCheck('passwordRequireNumbers', cp.password_require_numbers);
    setSafeCheck('passwordRequireSpecial', cp.password_require_special);

    setSafeVal('maxFailedLoginAttempts', cp.max_failed_login_attempts);
    setSafeVal('lockoutDurationMinutes', cp.lockout_duration_minutes);

    selectTwoFactorPolicy(cp.two_factor_policy || 'optional');
}

function updateCapacityMeters() {
    const cp = window.currentPolicies;
    const allocated = (cp.active_users || 0) + (cp.pending_invitations || 0);
    const max = cp.max_seats || 15;
    const available = Math.max(0, max - allocated);
    const percentage = max > 0 ? Math.min(100, Math.round((allocated / max) * 100)) : 0;

    const setSafeText = (id, val) => {
        const el = document.getElementById(id);
        if (el) el.textContent = val;
    };

    setSafeText('statActive', cp.active_users || 0);
    setSafeText('statInvites', cp.pending_invitations || 0);
    setSafeText('statAvail', available);

    setSafeText('allocatedProgressLabel', `${allocated} of ${max} Seats Allocated (${percentage}%)`);

    const statusLabel = document.getElementById('capacityStatusLabel');
    const bar = document.getElementById('capacityProgressBar');
    if (bar) bar.style.width = `${percentage}%`;

    if (statusLabel && bar) {
        if (percentage >= 90) {
            statusLabel.textContent = 'Critical Capacity';
            statusLabel.style.color = '#ef4444';
            bar.style.background = '#ef4444';
        } else if (percentage >= 75) {
            statusLabel.textContent = 'Approaching Limit';
            statusLabel.style.color = '#f59e0b';
            bar.style.background = '#f59e0b';
        } else {
            statusLabel.textContent = 'Healthy Capacity';
            statusLabel.style.color = '#10b981';
            bar.style.background = 'var(--color-primary, #4f46e5)';
        }
    }

    const modalCapVal = document.getElementById('modalCurrentCapacityVal');
    if (modalCapVal) modalCapVal.textContent = `${max} Seats`;
}

// ============================================================================
// 2FA POLICY SELECTOR
// ============================================================================
function selectTwoFactorPolicy(policy) {
    if (!window.currentPolicies) window.currentPolicies = {};
    window.currentPolicies.two_factor_policy = policy;

    const cards = [
        { id: 'optional', el: document.getElementById('radioCardOptional') },
        { id: 'admin_enforced', el: document.getElementById('radioCardAdmin') },
        { id: 'all_enforced', el: document.getElementById('radioCardAll') }
    ];

    cards.forEach(c => {
        if (!c.el) return;
        const input = c.el.querySelector('input');
        const title = c.el.querySelector('div > div:first-child');
        if (c.id === policy) {
            c.el.classList.add('active');
            if (title) title.style.color = 'var(--color-primary-text, #3730a3)';
            if (input) input.checked = true;
        } else {
            c.el.classList.remove('active');
            if (title) title.style.color = 'var(--zinc-900, #09090b)';
            if (input) input.checked = false;
        }
    });

    const badge = document.getElementById('activeTwoFactorBadge');
    if (badge) {
        if (policy === 'all_enforced') {
            badge.textContent = 'Zero-Trust (All Users)';
            badge.className = 'badge badge-green';
        } else if (policy === 'admin_enforced') {
            badge.textContent = 'Privileged Only (Admins)';
            badge.className = 'badge badge-purple';
        } else {
            badge.textContent = 'Optional (Standard)';
            badge.className = 'badge badge-blue';
        }
    }
}

// ============================================================================
// POLICIES PERSISTENCE & OVERRIDE
// ============================================================================
async function handleSavePolicies(e) {
    if (e && e.preventDefault) e.preventDefault();
    const btn = document.getElementById('savePoliciesBtn');
    if (!btn) return;

    const originalContent = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = `<i data-lucide="refresh-cw" class="animate-spin" style="width: 16px; height: 16px;"></i> <span>Saving Policies...</span>`;
    if (window.lucide) lucide.createIcons();

    const payload = {
        session_timeout_minutes: parseInt(document.getElementById('sessionTimeoutMinutes')?.value || 60, 10),
        allow_self_password_change: document.getElementById('allowSelfPasswordChange')?.checked ?? true,
        password_min_length: parseInt(document.getElementById('passwordMinLength')?.value || 8, 10),
        password_expiry_days: parseInt(document.getElementById('passwordExpiryDays')?.value || 0, 10),
        password_require_uppercase: document.getElementById('passwordRequireUppercase')?.checked ?? true,
        password_require_numbers: document.getElementById('passwordRequireNumbers')?.checked ?? true,
        password_require_special: document.getElementById('passwordRequireSpecial')?.checked ?? true,
        max_failed_login_attempts: parseInt(document.getElementById('maxFailedLoginAttempts')?.value || 5, 10),
        lockout_duration_minutes: parseInt(document.getElementById('lockoutDurationMinutes')?.value || 30, 10),
        two_factor_policy: window.currentPolicies.two_factor_policy || 'optional',
        _token: window.CSRF_TOKEN || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    };

    try {
        const response = await fetch('/admin/security-governance/save', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': payload._token
            },
            body: JSON.stringify(payload)
        });

        const result = await response.json();
        if (response.ok && result.success) {
            Object.assign(window.currentPolicies, result.data || payload);
            showFeedback(result.message || 'Tenant security policies and seat allocations saved successfully!', 'success');
            if (typeof window.showToast === 'function') {
                window.showToast('Security & Governance policies updated');
            }
        } else {
            showFeedback(result.message || 'Failed to save security policies.', 'error');
        }
    } catch (err) {
        console.error('Error saving policies:', err);
        showFeedback('Network error while saving security policies.', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalContent;
        if (window.lucide) lucide.createIcons();
    }
}

async function handleSaveSeatsOnly() {
    const input = document.getElementById('superAdminSeatsInput');
    if (!input) return;

    const newSeats = parseInt(input.value, 10);
    const cp = window.currentPolicies;
    const allocated = (cp.active_users || 0) + (cp.pending_invitations || 0);

    if (isNaN(newSeats) || newSeats < allocated) {
        showFeedback(`Seat count cannot be lower than currently allocated seats (${allocated}).`, 'error');
        return;
    }

    const btn = document.getElementById('saveOverrideBtn');
    if (!btn) return;
    const originalText = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'Saving...';

    const token = window.CSRF_TOKEN || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    try {
        const response = await fetch('/admin/security-governance/override-seats', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({
                max_seats: newSeats,
                _token: token
            })
        });

        const result = await response.json();
        if (response.ok && result.success) {
            cp.max_seats = newSeats;
            updateCapacityMeters();
            showFeedback(`Tenant seat limit updated to ${newSeats} seats successfully!`, 'success');
            if (typeof window.showToast === 'function') {
                window.showToast(`Seat quota overridden: ${newSeats} seats`);
            }
        } else {
            showFeedback(result.message || 'Failed to update seat limit.', 'error');
        }
    } catch (err) {
        console.error('Error overriding seats:', err);
        showFeedback('Network error while overriding seats.', 'error');
    } finally {
        btn.disabled = false;
        btn.textContent = originalText;
    }
}

// ============================================================================
// ADD SEATS MODAL (BILLING & PROVISIONING)
// ============================================================================
function openAddSeatsModal() {
    additionalSeatsCount = 1;
    const cp = window.currentPolicies;

    const configState = document.getElementById('seatsConfigState');
    const receiptState = document.getElementById('seatsReceiptState');
    const errorBanner = document.getElementById('modalErrorBanner');
    const modal = document.getElementById('addSeatsModal');

    if (configState) configState.style.display = 'block';
    if (receiptState) receiptState.style.display = 'none';
    if (errorBanner) errorBanner.style.display = 'none';

    const setSafeText = (id, val) => {
        const el = document.getElementById(id);
        if (el) el.textContent = val;
    };

    setSafeText('addSeatsModalTitle', 'Add User Seats');
    setSafeText('modalPlanTierLabel', (cp.plan_tier || 'enterprise').toUpperCase());
    setSafeText('modalUnitPriceLabel', cp.unit_price_monthly || 39);
    setSafeText('modalCurrentCapacityVal', `${cp.max_seats || 15} Seats`);

    syncSeatsModalCalculation();
    if (modal) modal.style.display = 'flex';
    if (window.lucide) lucide.createIcons();
}

function closeAddSeatsModal() {
    const modal = document.getElementById('addSeatsModal');
    if (modal) modal.style.display = 'none';
}

function handleQuickAdd(count) {
    additionalSeatsCount = count;
    [1, 3, 5, 10].forEach(n => {
        const btn = document.getElementById(`quickAdd${n}`);
        if (btn) {
            if (n === count) btn.classList.add('active');
            else btn.classList.remove('active');
        }
    });
    syncSeatsModalCalculation();
}

function handleStepper(delta) {
    additionalSeatsCount = Math.max(1, additionalSeatsCount + delta);
    [1, 3, 5, 10].forEach(n => {
        const btn = document.getElementById(`quickAdd${n}`);
        if (btn) {
            if (n === additionalSeatsCount) btn.classList.add('active');
            else btn.classList.remove('active');
        }
    });
    syncSeatsModalCalculation();
}

function handleCustomQuantityChange(val) {
    const num = parseInt(val, 10) || 1;
    additionalSeatsCount = Math.max(1, num);
    syncSeatsModalCalculation();
}

function syncSeatsModalCalculation() {
    const cp = window.currentPolicies;
    const unitPrice = cp.unit_price_monthly || 39;
    const currentSeats = cp.max_seats || 15;
    const newTotal = currentSeats + additionalSeatsCount;
    const totalCharge = (additionalSeatsCount * unitPrice).toFixed(2);

    const input = document.getElementById('additionalSeatsInput');
    if (input) input.value = additionalSeatsCount;

    const minusBtn = document.getElementById('stepperMinusBtn');
    if (minusBtn) minusBtn.disabled = additionalSeatsCount <= 1;

    const setSafeText = (id, val) => {
        const el = document.getElementById(id);
        if (el) el.textContent = val;
    };

    setSafeText('modalCalcPricePerSeat', `$${unitPrice.toFixed(2)} / month`);
    setSafeText('modalCalcAddSeats', `+${additionalSeatsCount}`);
    setSafeText('modalCalcNewTotal', `${newTotal} Total Seats`);
    setSafeText('modalCalcDueToday', `$${totalCharge} USD`);
    setSafeText('confirmPurchaseBtnText', `Confirm & Pay $${totalCharge}`);
}

async function executeSeatPurchase() {
    if (additionalSeatsCount < 1) {
        showModalError('Please select at least 1 additional seat.');
        return;
    }

    const btn = document.getElementById('confirmPurchaseBtn');
    if (!btn) return;
    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = `<i data-lucide="refresh-cw" class="animate-spin" style="width: 15px; height: 15px;"></i> <span>Processing Payment...</span>`;
    if (window.lucide) lucide.createIcons();

    const cardVal = document.getElementById('demoCardNumber')?.value || '';
    const last4 = cardVal.replace(/\D/g, '').slice(-4) || '4242';
    const token = window.CSRF_TOKEN || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    try {
        const response = await fetch('/admin/security-governance/add-seats', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({
                additional_seats: additionalSeatsCount,
                payment_method_brand: 'Visa',
                payment_method_last4: last4,
                _token: token
            })
        });

        const result = await response.json();
        if (response.ok && result.success) {
            const data = result.data;
            const newTotal = data.new_seat_count;
            const amountPaid = data.charge_amount;
            const invoice = data.invoice;

            // 1. Update State
            window.currentPolicies.max_seats = newTotal;
            updateCapacityMeters();
            const seatsInput = document.getElementById('superAdminSeatsInput');
            if (seatsInput) seatsInput.value = newTotal;

            // 2. Prepend invoice to list
            if (invoice) {
                window.currentInvoices.unshift(invoice);
            }

            // 3. Switch modal to receipt state
            const configState = document.getElementById('seatsConfigState');
            const receiptState = document.getElementById('seatsReceiptState');
            if (configState) configState.style.display = 'none';
            if (receiptState) receiptState.style.display = 'block';

            const setSafeText = (id, val) => {
                const el = document.getElementById(id);
                if (el) el.textContent = val;
            };

            setSafeText('addSeatsModalTitle', 'Payment Receipt');
            setSafeText('receiptTotalSeatsVal', `${newTotal} team members`);
            setSafeText('receiptInvoiceNum', invoice ? invoice.invoice_number : 'INV-2026');
            setSafeText('receiptSeatsAdded', `+${additionalSeatsCount} User Licenses`);
            setSafeText('receiptNewCapacity', `${newTotal} Seats`);
            setSafeText('receiptAmountPaid', `$${Number(amountPaid).toFixed(2)} USD`);

            showFeedback(`Added ${additionalSeatsCount} seats. New capacity: ${newTotal} seats.`, 'success');
            if (typeof window.showToast === 'function') {
                window.showToast(`Purchased +${additionalSeatsCount} Seats successfully!`);
            }
        } else {
            showModalError(result.message || 'Payment processing failed.');
        }
    } catch (err) {
        console.error('Error adding seats:', err);
        showModalError('Payment transaction failed due to a network error.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
        if (window.lucide) lucide.createIcons();
    }
}

function showModalError(msg) {
    const banner = document.getElementById('modalErrorBanner');
    const textEl = document.getElementById('modalErrorText');
    if (textEl) textEl.textContent = msg;
    if (banner) banner.style.display = 'flex';
    if (window.lucide) lucide.createIcons();
}

// ============================================================================
// INVOICES MODAL
// ============================================================================
async function handleOpenInvoices() {
    renderInvoicesTable();
    const modal = document.getElementById('invoicesModal');
    if (modal) modal.style.display = 'flex';
    if (window.lucide) lucide.createIcons();

    try {
        const response = await fetch('/admin/security-governance/invoices', {
            headers: { 'Accept': 'application/json' }
        });
        const result = await response.json();
        if (response.ok && result.success && Array.isArray(result.data)) {
            window.currentInvoices = result.data;
            renderInvoicesTable();
        }
    } catch (err) {
        console.error('Error fetching invoices:', err);
    }
}

function closeInvoicesModal() {
    const modal = document.getElementById('invoicesModal');
    if (modal) modal.style.display = 'none';
}

function renderInvoicesTable() {
    const tbody = document.getElementById('invoicesTbody');
    if (!tbody) return;

    if (!window.currentInvoices || window.currentInvoices.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" style="text-align: center; padding: 2rem; color: var(--zinc-500);">
                    No billing invoices found for this organization.
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = window.currentInvoices.map(inv => {
        const dateStr = inv.paid_at || inv.created_at || '';
        const formattedDate = dateStr ? String(dateStr).split('T')[0] : 'Today';
        const statusUpper = (inv.status || 'PAID').toUpperCase();
        return `
            <tr style="border-bottom: 1px solid var(--zinc-100);">
                <td style="padding: 0.65rem 0.5rem; font-weight: 600; color: var(--zinc-900); font-family: monospace;">${inv.invoice_number || ''}</td>
                <td style="padding: 0.65rem 0.5rem; color: var(--zinc-700);">${inv.description || 'Subscription License'}</td>
                <td style="padding: 0.65rem 0.5rem; font-weight: 600; color: #047857;">+${inv.seats_purchased || 0}</td>
                <td style="padding: 0.65rem 0.5rem; font-weight: 600; color: var(--zinc-900);">$${Number(inv.amount || 0).toFixed(2)}</td>
                <td style="padding: 0.65rem 0.5rem;">
                    <span style="padding: 2px 8px; border-radius: 9999px; font-size: 0.7rem; font-weight: 600; background-color: #ecfdf5; color: #059669;">
                        ${statusUpper}
                    </span>
                </td>
                <td style="padding: 0.65rem 0.5rem; color: var(--zinc-500); font-size: 0.75rem;">${formattedDate}</td>
            </tr>
        `;
    }).join('');
}

// ============================================================================
// LIVE PASSWORD COMPLEXITY SIMULATOR & TESTER
// ============================================================================
function initPasswordSimulator() {
    const testInput = document.getElementById('simPasswordInput');
    if (!testInput) return;

    testInput.addEventListener('input', () => {
        evaluatePasswordAgainstRules(testInput.value);
    });

    // Re-evaluate whenever rules change
    ['passwordMinLength', 'passwordRequireUppercase', 'passwordRequireNumbers', 'passwordRequireSpecial'].forEach(id => {
        const el = document.getElementById(id);
        el?.addEventListener('change', () => {
            evaluatePasswordAgainstRules(testInput.value);
        });
    });

    evaluatePasswordAgainstRules(testInput.value);
}

function evaluatePasswordAgainstRules(pwd) {
    const minLength = parseInt(document.getElementById('passwordMinLength')?.value || 8, 10);
    const reqUpper = document.getElementById('passwordRequireUppercase')?.checked ?? true;
    const reqNumber = document.getElementById('passwordRequireNumbers')?.checked ?? true;
    const reqSpecial = document.getElementById('passwordRequireSpecial')?.checked ?? true;

    const hasLen = (pwd || '').length >= minLength;
    const hasUpper = /[A-Z]/.test(pwd || '');
    const hasNumber = /[0-9]/.test(pwd || '');
    const hasSpecial = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(pwd || '');

    updateCheckItem('pwdCheckLen', hasLen, `At least ${minLength} characters`);
    updateCheckItem('pwdCheckUpper', !reqUpper || hasUpper, reqUpper ? 'Contains uppercase letter (A-Z)' : 'Uppercase optional');
    updateCheckItem('pwdCheckNumber', !reqNumber || hasNumber, reqNumber ? 'Contains number (0-9)' : 'Number optional');
    updateCheckItem('pwdCheckSpecial', !reqSpecial || hasSpecial, reqSpecial ? 'Contains special symbol' : 'Symbol optional');

    // Calculate score
    let score = 0;
    if (hasLen) score += 25;
    if (!reqUpper || hasUpper) score += 25;
    if (!reqNumber || hasNumber) score += 25;
    if (!reqSpecial || hasSpecial) score += 25;

    const bar = document.getElementById('pwdStrengthBar');
    const label = document.getElementById('pwdStrengthLabel');

    if (bar && label) {
        bar.style.width = `${score}%`;
        if (pwd.length === 0) {
            bar.style.width = '0%';
            label.textContent = 'Enter sample password to test against policies';
            label.style.color = 'var(--zinc-500)';
            bar.style.background = 'var(--zinc-300)';
        } else if (score < 50) {
            label.textContent = 'Weak Password — Does not meet policy';
            label.style.color = '#ef4444';
            bar.style.background = '#ef4444';
        } else if (score < 100) {
            label.textContent = 'Partial Match — Missing required parameters';
            label.style.color = '#f59e0b';
            bar.style.background = '#f59e0b';
        } else {
            label.textContent = 'Policy Compliant — Enterprise Strong';
            label.style.color = '#10b981';
            bar.style.background = '#10b981';
        }
    }
}

function updateCheckItem(id, passed, labelText) {
    const el = document.getElementById(id);
    if (!el) return;
    el.innerHTML = `
        <span style="display: inline-flex; align-items: center; justify-content: center; width: 16px; height: 16px; border-radius: 50%; font-size: 10px; font-weight: bold; margin-right: 6px; background-color: ${passed ? '#ecfdf5' : '#fef2f2'}; color: ${passed ? '#059669' : '#dc2626'};">
            ${passed ? '✓' : '✕'}
        </span>
        <span style="color: ${passed ? 'var(--zinc-800)' : 'var(--zinc-400)'};">${labelText}</span>
    `;
}

// ============================================================================
// INACTIVITY AUTO-TIMEOUT HELPER & SIMULATOR
// ============================================================================
function initInactivityHelper() {
    const sel = document.getElementById('sessionTimeoutMinutes');
    const hint = document.getElementById('inactivityTimelineHint');

    function updateInactivityPreview() {
        if (!sel || !hint) return;
        const val = parseInt(sel.value, 10);
        if (val === 0) {
            hint.innerHTML = '<strong style="color: #f59e0b;">Timeout Inactive:</strong> Browser sessions remain authenticated indefinitely until manual logout.';
        } else {
            const warnTime = Math.max(1, val - 2);
            hint.innerHTML = `Warning toast shown at <strong>${warnTime} min</strong> of inactivity. Secure token purged at <strong>${val} min</strong>.`;
        }
    }

    sel?.addEventListener('change', updateInactivityPreview);
    updateInactivityPreview();
}

function testInactivityWarningToast() {
    if (typeof window.showToast === 'function') {
        window.showToast('Security Alert: Your session will expire in 2 minutes due to inactivity. Move mouse to remain logged in.');
    } else {
        alert('Security Alert: Your session will expire in 2 minutes due to inactivity.');
    }
}

// ============================================================================
// BRUTE FORCE SIMULATOR & RESET ATTEMPTS
// ============================================================================
let simulatedFailedCount = 0;
function simulateFailedLoginAttempt() {
    const max = parseInt(document.getElementById('maxFailedLoginAttempts')?.value || 5, 10);
    const duration = parseInt(document.getElementById('lockoutDurationMinutes')?.value || 30, 10);
    const statusEl = document.getElementById('bruteForceSimStatus');

    if (max === 0) {
        if (statusEl) statusEl.innerHTML = '<span style="color: #64748b;">Lockout protection is currently disabled.</span>';
        return;
    }

    simulatedFailedCount++;
    if (simulatedFailedCount >= max) {
        if (statusEl) {
            statusEl.innerHTML = `<span style="color: #ef4444; font-weight: 600;">⚠️ Account LOCKED for ${duration} minutes! (Reached ${simulatedFailedCount}/${max} failed attempts)</span>`;
        }
        if (typeof window.showToast === 'function') {
            window.showToast(`IP Rate Limited: Account locked for ${duration} minutes.`, 'error');
        }
    } else {
        if (statusEl) {
            statusEl.innerHTML = `<span style="color: #f59e0b;">Attempt ${simulatedFailedCount}/${max} failed. Warning issued to client.</span>`;
        }
    }
}

function resetBruteForceSimulation() {
    simulatedFailedCount = 0;
    const statusEl = document.getElementById('bruteForceSimStatus');
    if (statusEl) {
        statusEl.innerHTML = '<span style="color: #10b981;">0 active lockout locks. All test accounts clear.</span>';
    }
    if (typeof window.showToast === 'function') {
        window.showToast('Lockout simulation reset.');
    }
}

// ============================================================================
// FEEDBACK BANNER & HELPERS
// ============================================================================
function showFeedback(msg, type = 'success') {
    const banner = document.getElementById('feedbackBanner');
    const textEl = document.getElementById('feedbackText');
    const iconEl = document.getElementById('feedbackIcon');

    if (!banner || !textEl || !iconEl) return;

    textEl.textContent = msg;
    if (type === 'success') {
        banner.style.background = '#ecfdf5';
        banner.style.border = '1px solid #a7f3d0';
        banner.style.color = '#047857';
        iconEl.setAttribute('data-lucide', 'check-circle-2');
    } else {
        banner.style.background = '#fef2f2';
        banner.style.border = '1px solid #fecaca';
        banner.style.color = '#b91c1c';
        iconEl.setAttribute('data-lucide', 'alert-circle');
    }

    banner.style.display = 'flex';
    if (window.lucide) lucide.createIcons();

    if (feedbackTimeout) clearTimeout(feedbackTimeout);
    feedbackTimeout = setTimeout(() => {
        banner.style.display = 'none';
    }, 4500);
}

function capitalize(s) {
    if (!s) return '';
    return s.charAt(0).toUpperCase() + s.slice(1);
}

// Keyboard ESC listener
window.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeAddSeatsModal();
        closeInvoicesModal();
    }
});

// Auto-initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('governanceForm')) {
        initSecurityGovernance();
    }
});

// Expose on window for inline blade onclick triggers and SPA navigation
window.initSecurityGovernance = initSecurityGovernance;
window.populateFormValues = populateFormValues;
window.updateCapacityMeters = updateCapacityMeters;
window.selectTwoFactorPolicy = selectTwoFactorPolicy;
window.handleSavePolicies = handleSavePolicies;
window.handleSaveSeatsOnly = handleSaveSeatsOnly;
window.openAddSeatsModal = openAddSeatsModal;
window.closeAddSeatsModal = closeAddSeatsModal;
window.handleQuickAdd = handleQuickAdd;
window.handleStepper = handleStepper;
window.handleCustomQuantityChange = handleCustomQuantityChange;
window.syncSeatsModalCalculation = syncSeatsModalCalculation;
window.executeSeatPurchase = executeSeatPurchase;
window.showModalError = showModalError;
window.handleOpenInvoices = handleOpenInvoices;
window.closeInvoicesModal = closeInvoicesModal;
window.renderInvoicesTable = renderInvoicesTable;
window.testInactivityWarningToast = testInactivityWarningToast;
window.simulateFailedLoginAttempt = simulateFailedLoginAttempt;
window.resetBruteForceSimulation = resetBruteForceSimulation;
window.evaluatePasswordAgainstRules = evaluatePasswordAgainstRules;
window.showFeedback = showFeedback;
