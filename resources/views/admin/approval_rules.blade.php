@extends('layouts.app')

@section('title', 'Approval Rules — DealSync Admin')

@section('content')
    <div class="admin-page-container animate-fade-in">

        <!-- Header -->
        <div
            style="display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.5rem;">
            <div>
                <h1 style="font-size: 1.6rem; font-weight: 800; color: #2f5d9e; margin: 0;">Multi-Level Approval Workflows
                </h1>
                <p style="font-size: 0.9rem; color: var(--zinc-500); margin: 0.3rem 0 0 0;">
                    Define multi-tier sign-off chains (Level 1 &rarr; Level 2 &rarr; Final) with automated triggers on
                    creation/edits, or manual user "Send for Approval" submissions.
                </p>
            </div>
            <button type="button" onclick="openApprovalModal()" class="btn btn-primary"
                style="display: inline-flex; align-items: center; gap: 0.4rem; background: #8e44ad; border: 0; border-radius: 8px; padding: 0.55rem 1.1rem; white-space: nowrap; cursor: pointer;">
                <i data-lucide="plus" style="width: 16px; height: 16px;"></i>
                <span>Create Approval Workflow</span>
            </button>
        </div>

        <!-- Workflow Card -->
        <div class="card"
            style="padding: 1.5rem 1.9rem; border: 1px solid #fed7aa; border-radius: 14px; position: relative;">
            <div style="display: flex; align-items: center; gap: 0.6rem; flex-wrap: wrap; padding-right: 160px;">
                <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--zinc-950); margin: 0;">New Lead Approval</h3>
                <span class="badge badge-blue" style="border-radius: 999px;">Leads</span>
                <span class="badge badge-yellow"
                    style="border-radius: 999px; display: inline-flex; align-items: center; gap: 0.3rem;">
                    <i data-lucide="zap" style="width: 12px; height: 12px;"></i> Auto on Create
                </span>
                <span class="badge badge-gray"
                    style="border-radius: 999px; display: inline-flex; align-items: center; gap: 0.3rem;">
                    <i data-lucide="bell" style="width: 12px; height: 12px;"></i> + <i data-lucide="mail"
                        style="width: 12px; height: 12px;"></i> Both
                </span>
                <span class="badge badge-green"
                    style="border-radius: 999px; display: inline-flex; align-items: center; gap: 0.3rem;">
                    <i data-lucide="check-circle-2" style="width: 12px; height: 12px;"></i> 1 Approval Action
                </span>
                <span class="badge"
                    style="border-radius: 999px; background: #fee2e2; color: #b91c1c; display: inline-flex; align-items: center; gap: 0.3rem;">
                    <i data-lucide="x-circle" style="width: 12px; height: 12px;"></i> 1 Rejection Action
                </span>
                <span class="badge badge-yellow"
                    style="border-radius: 999px; display: inline-flex; align-items: center; gap: 0.3rem;">
                    <i data-lucide="clock" style="width: 12px; height: 12px;"></i> Auto-Cancels in 24h
                </span>
            </div>

            <div style="font-size: 0.9rem; color: var(--zinc-700); margin-top: 0.7rem;">New Lead Approval Required</div>

            <div style="display: flex; align-items: center; gap: 0.6rem; margin-top: 0.8rem; flex-wrap: wrap;">
                <span style="font-size: 0.75rem; font-weight: 700; color: var(--zinc-950); letter-spacing: 0.03em;">APPROVAL
                    CHAIN (1 LEVELS):</span>
                <span
                    style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.3rem 0.7rem; border: 1px solid var(--zinc-200); border-radius: 8px; background: var(--zinc-100); font-size: 0.85rem;">
                    <strong>Level 1:</strong> Level 1: Team Manager Review
                    <span style="font-size: 0.75rem; color: var(--zinc-700);">Executive / CEO</span>
                </span>
            </div>

            <div style="position: absolute; top: 1.4rem; right: 1.5rem; display: flex; gap: 0.5rem;">
                <button type="button" class="btn btn-secondary btn-sm"
                    style="display: inline-flex; align-items: center; gap: 0.35rem;">
                    <i data-lucide="pencil" style="width: 14px; height: 14px;"></i> Edit
                </button>
                <button type="button" class="btn btn-secondary btn-sm" style="color: #dc2626;">
                    <i data-lucide="trash-2" style="width: 14px; height: 14px;"></i>
                </button>
            </div>
        </div>
    </div>


    <!-- Approval Modal -->
    <div id="approval-modal" class="modal-overlay"
        style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; padding: 1rem;">
        <div class="modal-content animate-fade-in"
            style="max-width: 820px; width: 100%; max-height: 90vh; display: flex; flex-direction: column; overflow: hidden; margin: auto; border-radius: var(--radius-xl); box-shadow: rgba(0,0,0,0.35) 0 25px 50px -12px; background: #fff;">
            <div class="modal-header"
                style="flex-shrink: 0; border-bottom: 1px solid var(--zinc-200); padding: 1.25rem 1.5rem; background: #fff; display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <h3 style="font-size: 1.05rem; font-weight: 600; color: var(--zinc-950); margin: 0;">Create Multi-Level
                        Approval Workflow</h3>
                    <div style="font-size: 0.75rem; color: var(--zinc-500); margin-top: 0.1rem;">Configure trigger rules,
                        multi-tier approver chains, and notification channels</div>
                </div>
                <button type="button" onclick="closeApprovalModal()"
                    style="background: none; border: none; cursor: pointer; font-size: 1.25rem; color: var(--zinc-400);">✕</button>
            </div>

            <form id="approval-form" onsubmit="event.preventDefault(); closeApprovalModal();"
                style="display: flex; flex-direction: column; flex: 1 1 0%; min-height: 0; overflow: hidden;">
                <div class="modal-body"
                    style="flex: 1 1 0%; overflow-y: auto; padding: 1.25rem 1.5rem; display: flex; flex-direction: column; gap: 1.25rem;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                        <div class="form-group">
                            <label class="form-label">Workflow Name *</label>
                            <input required placeholder="e.g. High-Value Deal Sign-Off" class="form-input" type="text">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Target Module (Object) *</label>
                            <select class="form-select">
                                <option value="opportunities">Opportunities (Sales Pipeline)</option>
                                <option value="purchase_deals">Purchase Deals (Procurement)</option>
                                <option value="payments">Payments &amp; Invoices</option>
                                <option value="leads">Leads</option>
                                <option value="shipping">Shipping &amp; Freight</option>
                                <option value="wires">Wires &amp; Bank Reconciliation</option>
                                <option value="accounts">Accounts</option>
                                <option value="contacts">Contacts</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <input placeholder="Governance summary and approval purpose" class="form-input" type="text">
                    </div>

                    <div
                        style="background: var(--zinc-50); padding: 1rem; border-radius: var(--radius-lg); border: 1px solid var(--zinc-200);">
                        <label class="form-label" style="font-weight: 600; color: var(--zinc-900);">Trigger Mechanism
                            *</label>
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.5rem; margin-top: 0.4rem;">
                            @foreach([['manual', 'Manual Trigger ("Send for Approval")'], ['on_create', 'Auto on Record Create'], ['on_update', 'Auto on Record Update/Edit']] as $i => [$v, $l])
                                <label
                                    style="display: flex; align-items: center; gap: 0.45rem; padding: 0.65rem 0.85rem; background: {{ $i == 0 ? '#fff' : 'transparent' }}; border: 1px solid {{ $i == 0 ? 'var(--zinc-900)' : 'var(--zinc-200)' }}; border-radius: var(--radius-md); cursor: pointer; font-size: 0.75rem; font-weight: {{ $i == 0 ? 600 : 500 }};">
                                    <input type="radio" name="triggerType" value="{{ $v }}" {{ $i == 0 ? 'checked' : '' }}>
                                    <span>{{ $l }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div
                        style="background: var(--zinc-50); padding: 1rem; border-radius: var(--radius-lg); border: 1px solid var(--zinc-200);">
                        <label class="form-label" style="font-weight: 600; color: var(--zinc-900);">Approver Notification
                            Channel *</label>
                        <div style="font-size: 0.725rem; color: var(--zinc-500); margin-bottom: 0.5rem;">Configure how
                            approvers are alerted when their review step is reached.</div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.5rem;">
                            @foreach([['both', 'Both (Notification + Email)'], ['notification', 'In-App Notification Only'], ['email', 'Email Alert Only']] as $i => [$v, $l])
                                <label
                                    style="display: flex; align-items: center; gap: 0.45rem; padding: 0.65rem 0.85rem; background: {{ $i == 0 ? '#fff' : 'transparent' }}; border: 1px solid {{ $i == 0 ? 'var(--zinc-900)' : 'var(--zinc-200)' }}; border-radius: var(--radius-md); cursor: pointer; font-size: 0.75rem; font-weight: {{ $i == 0 ? 600 : 500 }};">
                                    <input type="radio" name="notifChan" value="{{ $v }}" {{ $i == 0 ? 'checked' : '' }}>
                                    <span>{{ $l }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <div
                            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <div>
                                <label class="form-label" style="font-weight: 600; color: var(--zinc-900);">Multi-Level
                                    Approval Steps (<span id="level-count">1</span> Levels)</label>
                                <div style="font-size: 0.725rem; color: var(--zinc-500);">Steps execute sequentially from
                                    Level 1 to final level.</div>
                            </div>
                            <button type="button" onclick="addApprovalLevel()" class="btn btn-secondary btn-sm"
                                style="display: inline-flex; align-items: center; gap: 0.25rem;">
                                <i data-lucide="plus" style="width: 12px; height: 12px;"></i>
                                <span>Add Next Level</span>
                            </button>
                        </div>
                        <div id="approval-levels" style="display: flex; flex-direction: column; gap: 0.65rem;">
                            <div class="level-row"
                                style="display: grid; grid-template-columns: 70px 1.5fr 110px 1.5fr 40px; gap: 0.5rem; align-items: center; padding: 0.75rem; background: var(--zinc-50); border-radius: var(--radius-lg); border: 1px solid var(--zinc-200);">
                                <span class="level-label"
                                    style="font-weight: 700; font-size: 0.8125rem; color: var(--zinc-800);">Level 1</span>
                                <input required placeholder="e.g. Line Manager Approval" class="form-input" type="text"
                                    value="Level 1: Team Manager Review">
                                <select class="form-select">
                                    <option value="role">Role</option>
                                    <option value="user">User</option>
                                </select>
                                <select class="form-select">
                                    <option>Executive / CEO</option>
                                    <option>VP of Sales</option>
                                    <option>Sales Executive</option>
                                    <option>Procurement Lead</option>
                                    <option>Finance &amp; Billing Lead</option>
                                </select>
                                <button type="button" onclick="removeApprovalLevel(this)" class="btn btn-secondary btn-sm"
                                    title="Remove Step" style="color: #e11d48; padding: 0.4rem;">
                                    <i data-lucide="trash-2" style="width: 13px; height: 13px;"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div
                        style="background: var(--zinc-50); border: 1px solid var(--zinc-200); border-radius: var(--radius-lg); padding: 1rem; display: flex; flex-direction: column; gap: 0.75rem;">
                        <div
                            style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 0.5rem;">
                            <div>
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <label class="form-label"
                                        style="font-weight: 600; color: var(--zinc-900); margin: 0;">Approval Actions (Final
                                        Sign-off)</label>
                                    <span
                                        style="font-size: 0.675rem; padding: 0.1rem 0.45rem; border-radius: 4px; font-weight: 600; background: #dcfce7; color: #166534; border: 1px solid #86efac;">On
                                        Approved (<span id="approve-count">0</span>)</span>
                                </div>
                                <div style="font-size: 0.725rem; color: var(--zinc-500); margin-top: 0.15rem;">Actions
                                    executed automatically when the final approval level is granted.</div>
                            </div>
                            <div style="display: flex; gap: 0.35rem; flex-wrap: wrap;">
                                <button type="button" onclick="addAction('approve-actions','field')"
                                    class="btn btn-secondary btn-sm"
                                    style="font-size: 0.725rem; padding: 0.25rem 0.55rem;">+ Field Update</button>
                                <button type="button" onclick="addAction('approve-actions','email')"
                                    class="btn btn-secondary btn-sm"
                                    style="font-size: 0.725rem; padding: 0.25rem 0.55rem;">+ Email Alert</button>
                                <button type="button" onclick="addAction('approve-actions','task')"
                                    class="btn btn-secondary btn-sm"
                                    style="font-size: 0.725rem; padding: 0.25rem 0.55rem;">+ Task</button>
                                <button type="button" onclick="addAction('approve-actions','notif')"
                                    class="btn btn-secondary btn-sm"
                                    style="font-size: 0.725rem; padding: 0.25rem 0.55rem;">+ Notification</button>
                            </div>
                        </div>
                        <div id="approve-actions" style="display: flex; flex-direction: column; gap: 0.5rem;"></div>
                    </div>
                    <div
                        style="background: var(--zinc-50); border: 1px solid var(--zinc-200); border-radius: var(--radius-lg); padding: 1rem; display: flex; flex-direction: column; gap: 0.75rem;">
                        <div
                            style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 0.5rem;">
                            <div>
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <label class="form-label"
                                        style="font-weight: 600; color: var(--zinc-900); margin: 0;">Rejection Actions (On
                                        Denial)</label>
                                    <span
                                        style="font-size: 0.675rem; padding: 0.1rem 0.45rem; border-radius: 4px; font-weight: 600; background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5;">On
                                        Rejected (<span id="reject-count">0</span>)</span>
                                </div>
                                <div style="font-size: 0.725rem; color: var(--zinc-500); margin-top: 0.15rem;">Actions
                                    executed automatically if any approver rejects the request.</div>
                            </div>
                            <div style="display: flex; gap: 0.35rem; flex-wrap: wrap;">
                                <button type="button" onclick="addAction('reject-actions','field')"
                                    class="btn btn-secondary btn-sm"
                                    style="font-size: 0.725rem; padding: 0.25rem 0.55rem;">+ Field Update</button>
                                <button type="button" onclick="addAction('reject-actions','email')"
                                    class="btn btn-secondary btn-sm"
                                    style="font-size: 0.725rem; padding: 0.25rem 0.55rem;">+ Email Alert</button>
                                <button type="button" onclick="addAction('reject-actions','task')"
                                    class="btn btn-secondary btn-sm"
                                    style="font-size: 0.725rem; padding: 0.25rem 0.55rem;">+ Task</button>
                                <button type="button" onclick="addAction('reject-actions','notif')"
                                    class="btn btn-secondary btn-sm"
                                    style="font-size: 0.725rem; padding: 0.25rem 0.55rem;">+ Notification</button>
                            </div>
                        </div>
                        <div id="reject-actions" style="display: flex; flex-direction: column; gap: 0.5rem;"></div>
                    </div>
                    <!-- Auto-Expiry -->
                    <div
                        style="background: #fffbeb; border: 1px solid #fef3c7; border-radius: var(--radius-lg); padding: 1.15rem; display: flex; flex-direction: column; gap: 0.85rem;">
                        <div
                            style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                            <div style="display: flex; align-items: center; gap: 0.55rem;">
                                <i data-lucide="clock" style="width: 16px; height: 16px; color: #d97706;"></i>
                                <div>
                                    <span style="font-size: 0.9rem; font-weight: 700; color: #92400e;">Self-Canceling
                                        Auto-Expiry (Stale Request Purge)</span>
                                    <div style="font-size: 0.725rem; color: #b45309; margin-top: 0.1rem;">Forces stale
                                        requests to auto-expire instead of zombie-lingering in someone's queue forever.
                                    </div>
                                </div>
                            </div>
                            <label
                                style="display: inline-flex; align-items: center; gap: 0.45rem; cursor: pointer; font-size: 0.8125rem; font-weight: 600; color: #92400e;">
                                <input type="checkbox" id="expiry-enable" checked
                                    onchange="document.getElementById('expiry-body').style.display = this.checked ? 'flex' : 'none'"
                                    style="width: 16px; height: 16px;">
                                <span>Enable Auto-Expiry</span>
                            </label>
                        </div>

                        <div id="expiry-body" style="display: flex; flex-direction: column; gap: 0.85rem;">
                            <div
                                style="background: #fff; padding: 0.85rem 1rem; border-radius: var(--radius-md); border: 1px solid #fde68a;">
                                <label
                                    style="font-size: 0.785rem; font-weight: 600; color: #92400e; display: block; margin-bottom: 0.45rem;">Expiration
                                    Timeout Period:</label>
                                <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                                    @foreach([[24, '24 Hours (1 Day)'], [48, '48 Hours (2 Days)'], [72, '72 Hours (3 Days - Default)'], [168, '7 Days (168 Hours)']] as [$h, $l])
                                        <button type="button" onclick="setExpiry({{ $h }})" data-hours="{{ $h }}"
                                            class="btn btn-sm expiry-btn {{ $h == 72 ? 'btn-primary' : 'btn-secondary' }}"
                                            style="font-size: 0.75rem; padding: 0.35rem 0.65rem;">{{ $l }}</button>
                                    @endforeach
                                    <div style="display: flex; align-items: center; gap: 0.35rem; margin-left: auto;">
                                        <span style="font-size: 0.75rem; color: #92400e; font-weight: 600;">Custom:</span>
                                        <input id="expiry-hours" min="1" max="720" class="form-input" type="number"
                                            value="72" oninput="setExpiry(this.value)"
                                            style="width: 80px; font-size: 0.75rem; padding: 0.3rem 0.5rem;">
                                        <span style="font-size: 0.75rem; color: #b45309;">hours</span>
                                    </div>
                                </div>
                            </div>

                            <div
                                style="background: var(--zinc-50); border: 1px solid var(--zinc-200); border-radius: var(--radius-lg); padding: 1rem; display: flex; flex-direction: column; gap: 0.75rem;">
                                <div
                                    style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 0.5rem;">
                                    <div>
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <label class="form-label"
                                                style="font-weight: 600; color: var(--zinc-900); margin: 0;">Self-Canceling
                                                Expiry Actions</label>
                                            <span
                                                style="font-size: 0.675rem; padding: 0.1rem 0.45rem; border-radius: 4px; font-weight: 600; background: #fef3c7; color: #92400e; border: 1px solid #fcd34d;">On
                                                Expiry (<span id="expiry-count">0</span>)</span>
                                        </div>
                                        <div style="font-size: 0.725rem; color: var(--zinc-500); margin-top: 0.15rem;">
                                            Executed automatically upon expiration (e.g. status set to 'Expired' + Email
                                            Alert telling submitter to resubmit).</div>
                                    </div>
                                    <div style="display: flex; gap: 0.35rem; flex-wrap: wrap;">
                                        <button type="button" onclick="addAction('expiry-actions','field')"
                                            class="btn btn-secondary btn-sm"
                                            style="font-size: 0.725rem; padding: 0.25rem 0.55rem;">+ Field Update</button>
                                        <button type="button" onclick="addAction('expiry-actions','email')"
                                            class="btn btn-secondary btn-sm"
                                            style="font-size: 0.725rem; padding: 0.25rem 0.55rem;">+ Email Alert</button>
                                        <button type="button" onclick="addAction('expiry-actions','task')"
                                            class="btn btn-secondary btn-sm"
                                            style="font-size: 0.725rem; padding: 0.25rem 0.55rem;">+ Task</button>
                                        <button type="button" onclick="addAction('expiry-actions','notif')"
                                            class="btn btn-secondary btn-sm"
                                            style="font-size: 0.725rem; padding: 0.25rem 0.55rem;">+ Notification</button>
                                    </div>
                                </div>
                                <div id="expiry-actions" style="display: flex; flex-direction: column; gap: 0.5rem;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Record Locking -->
                    <div
                        style="background: #fbfbfe; border: 1px solid #e0e7ff; border-radius: var(--radius-lg); padding: 1.15rem;">
                        <div
                            style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 0.75rem;">
                            <div style="display: flex; align-items: flex-start; gap: 0.55rem;">
                                <i data-lucide="lock"
                                    style="width: 16px; height: 16px; color: #4338ca; margin-top: 0.15rem;"></i>
                                <div>
                                    <span style="font-size: 0.9rem; font-weight: 700; color: #312e81;">Record Locking &amp;
                                        Security Governance</span>
                                    <div
                                        style="font-size: 0.725rem; color: #4b5563; margin-top: 0.15rem; max-width: 540px;">
                                        Lock the record from editing while awaiting sign-off. Once approved, the record is
                                        unlocked. If rejected, it remains permanently locked for standard users.</div>
                                </div>
                            </div>
                            <label
                                style="display: inline-flex; align-items: center; gap: 0.45rem; cursor: pointer; font-size: 0.8125rem; font-weight: 600; color: #3730a3;">
                                <input type="checkbox" style="width: 16px; height: 16px; accent-color: #4f46e5;">
                                <span>Lock Record Until Final Approval</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer"
                    style="flex-shrink: 0; background: #fff; border-top: 1px solid var(--zinc-200); padding: 1rem 1.5rem; display: flex; justify-content: flex-end; gap: 0.5rem;">
                    <button type="button" class="btn btn-secondary" onclick="closeApprovalModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Workflow</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openApprovalModal() {
            const m = document.getElementById('approval-modal');
            m.style.setProperty('display', 'flex', 'important');
            m.style.setProperty('opacity', '1', 'important');
            m.style.setProperty('visibility', 'visible', 'important');
            if (window.lucide) lucide.createIcons();
        }
        function closeApprovalModal() {
            document.getElementById('approval-modal').style.setProperty('display', 'none', 'important');
        }
        function addApprovalLevel() {
            const wrap = document.getElementById('approval-levels');
            const clone = wrap.querySelector('.level-row').cloneNode(true);
            clone.querySelector('input').value = '';
            wrap.appendChild(clone);
            renumberLevels();
            if (window.lucide) lucide.createIcons();
        }
        function removeApprovalLevel(btn) {
            const wrap = document.getElementById('approval-levels');
            if (wrap.children.length > 1) { btn.closest('.level-row').remove(); renumberLevels(); }
        }
        function renumberLevels() {
            const rows = document.querySelectorAll('#approval-levels .level-row');
            rows.forEach((r, i) => r.querySelector('.level-label').textContent = 'Level ' + (i + 1));
            document.getElementById('level-count').textContent = rows.length;
        }

        function addAction(containerId, type) {
            const labels = { field: 'Field Update', email: 'Email Alert', task: 'Task', notif: 'Popup Notification' };
            let body = '';
            if (type === 'field') {
                body = `<div style="display:grid;grid-template-columns:1fr 1fr;gap:0.5rem;">
                            <div><label style="font-size:0.7rem;color:var(--zinc-600);display:block;margin-bottom:0.2rem;">Field Name:</label><input class="form-input" type="text" placeholder="e.g. status, stage" style="font-size:0.75rem;padding:0.35rem 0.5rem;"></div>
                            <div><label style="font-size:0.7rem;color:var(--zinc-600);display:block;margin-bottom:0.2rem;">New Target Value:</label><input class="form-input" type="text" placeholder="e.g. Approved" style="font-size:0.75rem;padding:0.35rem 0.5rem;"></div></div>`;
            } else if (type === 'email' || type === 'notif') {
                body = `<div style="display:grid;grid-template-columns:140px 1fr;gap:0.65rem;">
                            <div><label style="font-size:0.7rem;color:var(--zinc-600);display:block;margin-bottom:0.2rem;font-weight:600;">Recipient:</label><select class="form-select" style="font-size:0.75rem;padding:0.35rem 0.5rem;width:100%;"><option>Submitter</option><option>Record Owner</option><option>Specific Users</option></select></div>
                            <div><label style="font-size:0.7rem;color:var(--zinc-600);display:block;margin-bottom:0.2rem;font-weight:600;">Choose Template *:</label><select class="form-select" style="font-size:0.75rem;padding:0.35rem 0.5rem;width:100%;"><option value="">-- Select Template --</option></select></div></div>`;
            } else {
                body = `<input class="form-input" type="text" placeholder="Task title" style="font-size:0.75rem;padding:0.35rem 0.5rem;">`;
            }
            const div = document.createElement('div');
            div.className = 'action-item';
            div.style.cssText = 'background:#fff;border:1px solid var(--zinc-200);border-radius:var(--radius-md);padding:0.75rem;display:flex;flex-direction:column;gap:0.5rem;';
            div.innerHTML = `<div style="display:flex;justify-content:space-between;align-items:center;">
                        <span style="font-size:0.785rem;font-weight:600;color:var(--zinc-800);">${labels[type]}</span>
                        <button type="button" onclick="removeAction(this)" class="btn btn-ghost btn-sm" style="color:#e11d48;padding:0.2rem;">✕</button></div>${body}`;
            document.getElementById(containerId).appendChild(div);
            updateActionCounts();
        }
        function removeAction(btn) { btn.closest('.action-item').remove(); updateActionCounts(); }
        function updateActionCounts() {
            [['approve-actions', 'approve-count'], ['reject-actions', 'reject-count'], ['expiry-actions', 'expiry-count']].forEach(([c, n]) => {
                const el = document.getElementById(n), box = document.getElementById(c);
                if (el && box) el.textContent = box.children.length;
            });
        }
        function setExpiry(h) {
            document.getElementById('expiry-hours').value = h;
            document.querySelectorAll('.expiry-btn').forEach(b => {
                const on = b.dataset.hours == h;
                b.classList.toggle('btn-primary', on);
                b.classList.toggle('btn-secondary', !on);
            });
        }
    </script>
@endsection