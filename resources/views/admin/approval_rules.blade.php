@extends('layouts.app')

@section('title', 'Approval Rules — DealSync Admin')

@section('content')
    <div class="admin-page-container animate-fade-in" style="max-width: 1200px; margin: 0 auto; padding: 1.5rem 1rem;">

        <!-- Floating Toast Notification -->
        <div id="toastNotification" class="fixed bottom-6 right-6 z-[99999] hidden items-center gap-2.5 px-4 py-3 rounded-xl shadow-xl text-xs font-semibold animate-fade-in text-white" style="transition: all 0.25s ease;">
            <i id="toastIcon" data-lucide="check-circle-2" style="width: 16px; height: 16px;"></i>
            <span id="toastMessage">Action completed successfully</span>
        </div>

        <!-- Header -->
        <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.5rem;">
            <div>
                <h1 style="font-size: 1.6rem; font-weight: 800; color: #2f5d9e; margin: 0;">
                    Multi-Level Approval Workflows
                </h1>
                <p style="font-size: 0.9rem; color: var(--zinc-500); margin: 0.3rem 0 0 0;">
                    Define multi-tier sign-off chains (Level 1 &rarr; Level 2 &rarr; Final) with automated triggers on creation/edits, or manual user "Send for Approval" submissions.
                </p>
            </div>
            <button type="button" onclick="openApprovalModal()" class="btn btn-primary"
                style="display: inline-flex; align-items: center; gap: 0.4rem; background: #8e44ad; border: 0; border-radius: 8px; padding: 0.55rem 1.1rem; white-space: nowrap; cursor: pointer;">
                <i data-lucide="plus" style="width: 16px; height: 16px;"></i>
                <span>Create Approval Workflow</span>
            </button>
        </div>

        <!-- Workflows List Container -->
        <div id="approval-rules-list" style="display: flex; flex-direction: column; gap: 1.25rem;">
            @forelse($approvalRules ?? [] as $rule)
                @php
                    $moduleColors = [
                        'leads'          => 'badge-blue',
                        'opportunities'  => 'badge-purple',
                        'purchase_deals' => 'badge-yellow',
                        'payments'       => 'badge-green',
                        'shipping'       => 'badge-blue',
                        'wires'          => 'badge-purple',
                        'accounts'       => 'badge-green',
                        'contacts'       => 'badge-blue',
                    ];
                    $modBadge = $moduleColors[$rule->module] ?? 'badge-blue';
                    $stepsCount = $rule->steps->count();
                    $approveActionsCount = is_array($rule->approval_actions) ? count($rule->approval_actions) : 0;
                    $rejectActionsCount = is_array($rule->rejection_actions) ? count($rule->rejection_actions) : 0;
                @endphp

                <div class="card rule-workflow-card" id="rule-card-{{ $rule->id }}"
                    style="padding: 1.5rem 1.9rem; border: 1px solid {{ $rule->is_active ? '#fed7aa' : 'var(--zinc-200)' }}; border-radius: 14px; position: relative; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.05); transition: all 0.2s ease;">
                    
                    <!-- Badges Header -->
                    <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; padding-right: 170px;">
                        <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--zinc-950); margin: 0;">{{ $rule->name }}</h3>
                        
                        <!-- Module Badge -->
                        <span class="badge {{ $modBadge }}" style="border-radius: 999px;">
                            {{ ucfirst(str_replace('_', ' ', $rule->module)) }}
                        </span>

                        <!-- Trigger Badge -->
                        @if($rule->trigger_type === 'on_create')
                            <span class="badge badge-yellow" style="border-radius: 999px; display: inline-flex; align-items: center; gap: 0.3rem;">
                                <i data-lucide="zap" style="width: 12px; height: 12px;"></i> Auto on Create
                            </span>
                        @elseif($rule->trigger_type === 'on_update')
                            <span class="badge badge-blue" style="border-radius: 999px; display: inline-flex; align-items: center; gap: 0.3rem;">
                                <i data-lucide="refresh-cw" style="width: 12px; height: 12px;"></i> Auto on Update
                            </span>
                        @else
                            <span class="badge badge-gray" style="border-radius: 999px; display: inline-flex; align-items: center; gap: 0.3rem;">
                                <i data-lucide="send" style="width: 12px; height: 12px;"></i> Manual Trigger
                            </span>
                        @endif

                        <!-- Criteria Filter Badge -->
                        @if(!empty($rule->criteria_field) && !empty($rule->criteria_value))
                            <span class="badge" style="border-radius: 999px; background: #e0f2fe; color: #0369a1; display: inline-flex; align-items: center; gap: 0.3rem;">
                                <i data-lucide="filter" style="width: 12px; height: 12px;"></i> Submitter: {{ $rule->criteria_value }}
                            </span>
                        @endif

                        <!-- Channel Badge -->
                        <span class="badge badge-gray" style="border-radius: 999px; display: inline-flex; align-items: center; gap: 0.3rem;">
                            @if($rule->notification_channel === 'both')
                                <i data-lucide="bell" style="width: 12px; height: 12px;"></i> + <i data-lucide="mail" style="width: 12px; height: 12px;"></i> Both
                            @elseif($rule->notification_channel === 'email')
                                <i data-lucide="mail" style="width: 12px; height: 12px;"></i> Email Only
                            @else
                                <i data-lucide="bell" style="width: 12px; height: 12px;"></i> In-App Only
                            @endif
                        </span>

                        <!-- Actions Count Badges -->
                        <span class="badge badge-green" style="border-radius: 999px; display: inline-flex; align-items: center; gap: 0.3rem;">
                            <i data-lucide="check-circle-2" style="width: 12px; height: 12px;"></i> {{ $approveActionsCount }} Approval Action{{ $approveActionsCount != 1 ? 's' : '' }}
                        </span>

                        <span class="badge" style="border-radius: 999px; background: #fee2e2; color: #b91c1c; display: inline-flex; align-items: center; gap: 0.3rem;">
                            <i data-lucide="x-circle" style="width: 12px; height: 12px;"></i> {{ $rejectActionsCount }} Rejection Action{{ $rejectActionsCount != 1 ? 's' : '' }}
                        </span>

                        <!-- Expiry Badge -->
                        @if($rule->enable_auto_expiry)
                            <span class="badge badge-yellow" style="border-radius: 999px; display: inline-flex; align-items: center; gap: 0.3rem;">
                                <i data-lucide="clock" style="width: 12px; height: 12px;"></i> Auto-Cancels in {{ $rule->auto_expiry_hours }}h
                            </span>
                        @endif

                        <!-- Record Lock Badge -->
                        @if($rule->lock_record_until_approved)
                            <span class="badge" style="border-radius: 999px; background: #e0e7ff; color: #4338ca; display: inline-flex; align-items: center; gap: 0.3rem;">
                                <i data-lucide="lock" style="width: 12px; height: 12px;"></i> Record Locked
                            </span>
                        @endif

                        <!-- Status Badge (Clickable to toggle) -->
                        <button type="button" onclick="toggleApprovalRule({{ $rule->id }})" id="status-badge-{{ $rule->id }}"
                            class="badge cursor-pointer"
                            style="border: 0; border-radius: 999px; font-weight: 700; cursor: pointer; transition: opacity 0.2s; {{ $rule->is_active ? 'background: #dcfce7; color: #15803d;' : 'background: #f4f4f5; color: #71717a;' }}"
                            title="Click to toggle Active / Inactive status">
                            {{ $rule->is_active ? '● Active' : '○ Inactive' }}
                        </button>
                    </div>

                    <!-- Description -->
                    <div style="font-size: 0.875rem; color: var(--zinc-700); margin-top: 0.7rem; line-height: 1.45;">
                        {{ $rule->description ?: 'No workflow description provided.' }}
                    </div>

                    <!-- Approval Chain Steps -->
                    <div style="display: flex; align-items: center; gap: 0.6rem; margin-top: 0.9rem; flex-wrap: wrap;">
                        <span style="font-size: 0.75rem; font-weight: 700; color: var(--zinc-950); letter-spacing: 0.03em;">
                            APPROVAL CHAIN ({{ $stepsCount }} LEVEL{{ $stepsCount != 1 ? 'S' : '' }}):
                        </span>
                        @if($stepsCount > 0)
                            @foreach($rule->steps as $step)
                                <span style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.3rem 0.65rem; border: 1px solid var(--zinc-200); border-radius: 8px; background: var(--zinc-100); font-size: 0.8rem;">
                                    <strong>Level {{ $step->step_number }}:</strong> {{ $step->step_name }}
                                    <span style="font-size: 0.75rem; color: var(--zinc-600);">({{ ucfirst($step->approver_type) }}: {{ $step->approver_name ?: 'Any' }})</span>
                                </span>
                            @endforeach
                        @else
                            <span style="font-size: 0.8rem; color: var(--zinc-400); font-style: italic;">No levels defined yet</span>
                        @endif
                    </div>

                    <!-- Action Buttons (Edit & Delete) -->
                    <div style="position: absolute; top: 1.4rem; right: 1.5rem; display: flex; gap: 0.45rem;">
                        <button type="button" onclick="editApprovalRule({{ $rule->id }})" class="btn btn-secondary btn-sm"
                            style="display: inline-flex; align-items: center; gap: 0.35rem; cursor: pointer;"
                            title="Edit Approval Workflow">
                            <i data-lucide="pencil" style="width: 14px; height: 14px;"></i>
                            <span>Edit</span>
                        </button>
                        <button type="button" onclick="deleteApprovalRule({{ $rule->id }}, '{{ addslashes($rule->name) }}')" class="btn btn-secondary btn-sm"
                            style="color: #dc2626; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; padding: 0.45rem 0.65rem;"
                            title="Delete Approval Workflow">
                            <i data-lucide="trash-2" style="width: 14px; height: 14px;"></i>
                        </button>
                    </div>
                </div>
            @empty
                <!-- Empty State -->
                <div id="no-rules-card" class="card" style="padding: 3rem 1.5rem; text-align: center; border: 2px dashed var(--zinc-300); border-radius: 16px; background: var(--zinc-50);">
                    <div style="width: 48px; height: 48px; border-radius: 50%; background: #ede9fe; color: #8e44ad; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                        <i data-lucide="git-pull-request" style="width: 24px; height: 24px;"></i>
                    </div>
                    <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--zinc-900); margin: 0 0 0.4rem;">No Approval Workflows Configured</h3>
                    <p style="font-size: 0.875rem; color: var(--zinc-500); max-width: 480px; margin: 0 auto 1.25rem;">
                        Create multi-tier sign-off workflows for leads, deals, or invoices to enforce manager review and governance.
                    </p>
                    <button type="button" onclick="openApprovalModal()" class="btn btn-primary"
                        style="display: inline-flex; align-items: center; gap: 0.4rem; background: #8e44ad; border: 0; border-radius: 8px; padding: 0.55rem 1.1rem; cursor: pointer;">
                        <i data-lucide="plus" style="width: 16px; height: 16px;"></i>
                        <span>Create Approval Workflow</span>
                    </button>
                </div>
            @endforelse
        </div>
    </div>


    <!-- Approval Modal (Create / Edit) -->
    <div id="approval-modal" class="modal-overlay"
        style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.55); backdrop-filter: blur(2px); z-index: 1000; align-items: center; justify-content: center; padding: 1rem;">
        <div class="modal-content animate-fade-in"
            style="max-width: 820px; width: 100%; max-height: 90vh; display: flex; flex-direction: column; overflow: hidden; margin: auto; border-radius: var(--radius-xl); box-shadow: rgba(0,0,0,0.35) 0 25px 50px -12px; background: #fff;">
            
            <!-- Modal Header -->
            <div class="modal-header"
                style="flex-shrink: 0; border-bottom: 1px solid var(--zinc-200); padding: 1.25rem 1.5rem; background: #fff; display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <h3 id="modal-title" style="font-size: 1.1rem; font-weight: 700; color: var(--zinc-950); margin: 0;">
                        Create Multi-Level Approval Workflow
                    </h3>
                    <div style="font-size: 0.75rem; color: var(--zinc-500); margin-top: 0.15rem;">
                        Configure trigger rules, multi-tier approver chains, and notification channels
                    </div>
                </div>
                <button type="button" onclick="closeApprovalModal()"
                    style="background: none; border: none; cursor: pointer; font-size: 1.25rem; color: var(--zinc-400); line-height: 1; padding: 0.2rem;">✕</button>
            </div>

            <!-- Modal Form -->
            <form id="approval-form" onsubmit="handleApprovalFormSubmit(event)"
                style="display: flex; flex-direction: column; flex: 1 1 0%; min-height: 0; overflow: hidden;">
                
                <input type="hidden" id="rule-id" name="id" value="">

                <div class="modal-body"
                    style="flex: 1 1 0%; overflow-y: auto; padding: 1.25rem 1.5rem; display: flex; flex-direction: column; gap: 1.25rem;">
                    
                    <!-- Row 1: Workflow Name & Module -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                        <div class="form-group">
                            <label class="form-label" style="font-weight: 600; font-size: 0.8125rem;">Workflow Name *</label>
                            <input id="rule-name" required placeholder="e.g. High-Value Deal Sign-Off" class="form-input" type="text" style="font-size: 0.8125rem;">
                        </div>
                        <div class="form-group">
                            <label class="form-label" style="font-weight: 600; font-size: 0.8125rem;">Target Module (Object) *</label>
                            <select id="rule-module" class="form-select" style="font-size: 0.8125rem;">
                                <option value="leads">Leads</option>
                                <option value="opportunities">Opportunities (Sales Pipeline)</option>
                                <option value="purchase_deals">Purchase Deals (Procurement)</option>
                                <option value="payments">Payments &amp; Invoices</option>
                                <option value="shipping">Shipping &amp; Freight</option>
                                <option value="wires">Wires &amp; Bank Reconciliation</option>
                                <option value="accounts">Accounts</option>
                                <option value="contacts">Contacts</option>
                            </select>
                        </div>
                    </div>

                    <!-- Row 2: Description -->
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600; font-size: 0.8125rem;">Description</label>
                        <input id="rule-description" placeholder="Governance summary and approval purpose" class="form-input" type="text" style="font-size: 0.8125rem;">
                    </div>

                    <!-- Row 3: Trigger Mechanism -->
                    <div style="background: var(--zinc-50); padding: 1rem; border-radius: var(--radius-lg); border: 1px solid var(--zinc-200);">
                        <label class="form-label" style="font-weight: 600; color: var(--zinc-900); font-size: 0.8125rem;">Trigger Mechanism *</label>
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.5rem; margin-top: 0.4rem;">
                            @foreach([['manual', 'Manual Trigger ("Send for Approval")'], ['on_create', 'Auto on Record Create'], ['on_update', 'Auto on Record Update/Edit']] as $i => [$v, $l])
                                <label style="display: flex; align-items: center; gap: 0.45rem; padding: 0.65rem 0.85rem; background: #fff; border: 1px solid var(--zinc-200); border-radius: var(--radius-md); cursor: pointer; font-size: 0.75rem; font-weight: 500;">
                                    <input type="radio" name="triggerType" value="{{ $v }}" {{ $i == 0 ? 'checked' : '' }}>
                                    <span>{{ $l }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Row 3.5: Entry Criteria / Submitter Filter -->
                    <div style="background: var(--zinc-50); padding: 1rem; border-radius: var(--radius-lg); border: 1px solid var(--zinc-200);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <div>
                                <label class="form-label" style="font-weight: 600; color: var(--zinc-900); font-size: 0.8125rem; margin: 0;">
                                    Entry Criteria / Submitter Role Filter (Optional)
                                </label>
                                <div style="font-size: 0.725rem; color: var(--zinc-500); margin-top: 0.1rem;">
                                    Specify when this workflow should trigger (e.g. only when a Sales User creates a lead).
                                </div>
                            </div>
                        </div>
                        <div style="display: grid; grid-template-columns: 1.2fr 1fr 1.5fr; gap: 0.5rem; align-items: center;">
                            <div>
                                <label style="font-size: 0.7rem; color: var(--zinc-600); display: block; margin-bottom: 0.2rem; font-weight: 600;">Field / Property:</label>
                                <select id="rule-criteria-field" class="form-select" onchange="handleCriteriaFieldChange(this.value)" style="font-size: 0.8125rem;">
                                    <option value="">-- All Records (No Filter) --</option>
                                    <option value="creator_role">Submitter Role (e.g. Sales User)</option>
                                    <option value="creator_profile">Submitter Profile</option>
                                    <option value="status">Lead Status</option>
                                    <option value="rating">Rating</option>
                                    <option value="source">Lead Source</option>
                                </select>
                            </div>
                            <div>
                                <label style="font-size: 0.7rem; color: var(--zinc-600); display: block; margin-bottom: 0.2rem; font-weight: 600;">Operator:</label>
                                <select id="rule-criteria-operator" class="form-select" style="font-size: 0.8125rem;">
                                    <option value="equals">Equals (=)</option>
                                    <option value="not_equals">Does Not Equal (!=)</option>
                                    <option value="contains">Contains</option>
                                </select>
                            </div>
                            <div>
                                <label style="font-size: 0.7rem; color: var(--zinc-600); display: block; margin-bottom: 0.2rem; font-weight: 600;">Target Value:</label>
                                <div id="criteria-value-wrapper">
                                    <input id="rule-criteria-value" class="form-input" placeholder="e.g. Sales User" type="text" style="font-size: 0.8125rem;">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Row 4: Notification Channel -->
                    <div style="background: var(--zinc-50); padding: 1rem; border-radius: var(--radius-lg); border: 1px solid var(--zinc-200);">
                        <label class="form-label" style="font-weight: 600; color: var(--zinc-900); font-size: 0.8125rem;">Approver Notification Channel *</label>
                        <div style="font-size: 0.725rem; color: var(--zinc-500); margin-bottom: 0.5rem;">Configure how approvers are alerted when their review step is reached.</div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.5rem;">
                            @foreach([['both', 'Both (Notification + Email)'], ['notification', 'In-App Notification Only'], ['email', 'Email Alert Only']] as $i => [$v, $l])
                                <label style="display: flex; align-items: center; gap: 0.45rem; padding: 0.65rem 0.85rem; background: #fff; border: 1px solid var(--zinc-200); border-radius: var(--radius-md); cursor: pointer; font-size: 0.75rem; font-weight: 500;">
                                    <input type="radio" name="notifChan" value="{{ $v }}" {{ $i == 0 ? 'checked' : '' }}>
                                    <span>{{ $l }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Row 5: Multi-Level Approval Steps -->
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <div>
                                <label class="form-label" style="font-weight: 600; color: var(--zinc-900); font-size: 0.8125rem;">
                                    Multi-Level Approval Steps (<span id="level-count">1</span> Levels)
                                </label>
                                <div style="font-size: 0.725rem; color: var(--zinc-500);">Steps execute sequentially from Level 1 to final level.</div>
                            </div>
                            <button type="button" onclick="addApprovalLevel()" class="btn btn-secondary btn-sm"
                                style="display: inline-flex; align-items: center; gap: 0.25rem; cursor: pointer;">
                                <i data-lucide="plus" style="width: 12px; height: 12px;"></i>
                                <span>Add Next Level</span>
                            </button>
                        </div>
                        <div id="approval-levels" style="display: flex; flex-direction: column; gap: 0.65rem;">
                            <!-- Populated dynamically via JS -->
                        </div>
                    </div>

                    <!-- Row 6: Approval Actions (Final Sign-off) -->
                    <div style="background: var(--zinc-50); border: 1px solid var(--zinc-200); border-radius: var(--radius-lg); padding: 1rem; display: flex; flex-direction: column; gap: 0.75rem;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 0.5rem;">
                            <div>
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <label class="form-label" style="font-weight: 600; color: var(--zinc-900); margin: 0; font-size: 0.8125rem;">
                                        Approval Actions (Final Sign-off)
                                    </label>
                                    <span style="font-size: 0.675rem; padding: 0.1rem 0.45rem; border-radius: 4px; font-weight: 600; background: #dcfce7; color: #166534; border: 1px solid #86efac;">
                                        On Approved (<span id="approve-count">0</span>)
                                    </span>
                                </div>
                                <div style="font-size: 0.725rem; color: var(--zinc-500); margin-top: 0.15rem;">
                                    Actions executed automatically when the final approval level is granted.
                                </div>
                            </div>
                            <div style="display: flex; gap: 0.35rem; flex-wrap: wrap;">
                                <button type="button" onclick="addAction('approve-actions','field')" class="btn btn-secondary btn-sm" style="font-size: 0.725rem; padding: 0.25rem 0.55rem; cursor: pointer;">+ Field Update</button>
                                <button type="button" onclick="addAction('approve-actions','email')" class="btn btn-secondary btn-sm" style="font-size: 0.725rem; padding: 0.25rem 0.55rem; cursor: pointer;">+ Email Alert</button>
                                <button type="button" onclick="addAction('approve-actions','task')" class="btn btn-secondary btn-sm" style="font-size: 0.725rem; padding: 0.25rem 0.55rem; cursor: pointer;">+ Task</button>
                                <button type="button" onclick="addAction('approve-actions','notif')" class="btn btn-secondary btn-sm" style="font-size: 0.725rem; padding: 0.25rem 0.55rem; cursor: pointer;">+ Notification</button>
                            </div>
                        </div>
                        <div id="approve-actions" style="display: flex; flex-direction: column; gap: 0.5rem;"></div>
                    </div>

                    <!-- Row 7: Rejection Actions (On Denial) -->
                    <div style="background: var(--zinc-50); border: 1px solid var(--zinc-200); border-radius: var(--radius-lg); padding: 1rem; display: flex; flex-direction: column; gap: 0.75rem;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 0.5rem;">
                            <div>
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <label class="form-label" style="font-weight: 600; color: var(--zinc-900); margin: 0; font-size: 0.8125rem;">
                                        Rejection Actions (On Denial)
                                    </label>
                                    <span style="font-size: 0.675rem; padding: 0.1rem 0.45rem; border-radius: 4px; font-weight: 600; background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5;">
                                        On Rejected (<span id="reject-count">0</span>)
                                    </span>
                                </div>
                                <div style="font-size: 0.725rem; color: var(--zinc-500); margin-top: 0.15rem;">
                                    Actions executed automatically if any approver rejects the request.
                                </div>
                            </div>
                            <div style="display: flex; gap: 0.35rem; flex-wrap: wrap;">
                                <button type="button" onclick="addAction('reject-actions','field')" class="btn btn-secondary btn-sm" style="font-size: 0.725rem; padding: 0.25rem 0.55rem; cursor: pointer;">+ Field Update</button>
                                <button type="button" onclick="addAction('reject-actions','email')" class="btn btn-secondary btn-sm" style="font-size: 0.725rem; padding: 0.25rem 0.55rem; cursor: pointer;">+ Email Alert</button>
                                <button type="button" onclick="addAction('reject-actions','task')" class="btn btn-secondary btn-sm" style="font-size: 0.725rem; padding: 0.25rem 0.55rem; cursor: pointer;">+ Task</button>
                                <button type="button" onclick="addAction('reject-actions','notif')" class="btn btn-secondary btn-sm" style="font-size: 0.725rem; padding: 0.25rem 0.55rem; cursor: pointer;">+ Notification</button>
                            </div>
                        </div>
                        <div id="reject-actions" style="display: flex; flex-direction: column; gap: 0.5rem;"></div>
                    </div>

                    <!-- Row 8: Auto-Expiry -->
                    <div style="background: #fffbeb; border: 1px solid #fef3c7; border-radius: var(--radius-lg); padding: 1.15rem; display: flex; flex-direction: column; gap: 0.85rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                            <div style="display: flex; align-items: center; gap: 0.55rem;">
                                <i data-lucide="clock" style="width: 16px; height: 16px; color: #d97706;"></i>
                                <div>
                                    <span style="font-size: 0.9rem; font-weight: 700; color: #92400e;">
                                        Self-Canceling Auto-Expiry (Stale Request Purge)
                                    </span>
                                    <div style="font-size: 0.725rem; color: #b45309; margin-top: 0.1rem;">
                                        Forces stale requests to auto-expire instead of zombie-lingering in someone's queue.
                                    </div>
                                </div>
                            </div>
                            <label style="display: inline-flex; align-items: center; gap: 0.45rem; cursor: pointer; font-size: 0.8125rem; font-weight: 600; color: #92400e;">
                                <input type="checkbox" id="expiry-enable" checked
                                    onchange="document.getElementById('expiry-body').style.display = this.checked ? 'flex' : 'none'"
                                    style="width: 16px; height: 16px;">
                                <span>Enable Auto-Expiry</span>
                            </label>
                        </div>

                        <div id="expiry-body" style="display: flex; flex-direction: column; gap: 0.85rem;">
                            <div style="background: #fff; padding: 0.85rem 1rem; border-radius: var(--radius-md); border: 1px solid #fde68a;">
                                <label style="font-size: 0.785rem; font-weight: 600; color: #92400e; display: block; margin-bottom: 0.45rem;">
                                    Expiration Timeout Period:
                                </label>
                                <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                                    @foreach([[24, '24 Hours (1 Day)'], [48, '48 Hours (2 Days)'], [72, '72 Hours (3 Days - Default)'], [168, '7 Days (168 Hours)']] as [$h, $l])
                                        <button type="button" onclick="setExpiry({{ $h }})" data-hours="{{ $h }}"
                                            class="btn btn-sm expiry-btn {{ $h == 72 ? 'btn-primary' : 'btn-secondary' }}"
                                            style="font-size: 0.75rem; padding: 0.35rem 0.65rem; cursor: pointer;">{{ $l }}</button>
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

                            <div style="background: var(--zinc-50); border: 1px solid var(--zinc-200); border-radius: var(--radius-lg); padding: 1rem; display: flex; flex-direction: column; gap: 0.75rem;">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 0.5rem;">
                                    <div>
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <label class="form-label" style="font-weight: 600; color: var(--zinc-900); margin: 0; font-size: 0.8125rem;">
                                                Self-Canceling Expiry Actions
                                            </label>
                                            <span style="font-size: 0.675rem; padding: 0.1rem 0.45rem; border-radius: 4px; font-weight: 600; background: #fef3c7; color: #92400e; border: 1px solid #fcd34d;">
                                                On Expiry (<span id="expiry-count">0</span>)
                                            </span>
                                        </div>
                                        <div style="font-size: 0.725rem; color: var(--zinc-500); margin-top: 0.15rem;">
                                            Executed automatically upon expiration (e.g. status set to 'Expired').
                                        </div>
                                    </div>
                                    <div style="display: flex; gap: 0.35rem; flex-wrap: wrap;">
                                        <button type="button" onclick="addAction('expiry-actions','field')" class="btn btn-secondary btn-sm" style="font-size: 0.725rem; padding: 0.25rem 0.55rem; cursor: pointer;">+ Field Update</button>
                                        <button type="button" onclick="addAction('expiry-actions','email')" class="btn btn-secondary btn-sm" style="font-size: 0.725rem; padding: 0.25rem 0.55rem; cursor: pointer;">+ Email Alert</button>
                                        <button type="button" onclick="addAction('expiry-actions','task')" class="btn btn-secondary btn-sm" style="font-size: 0.725rem; padding: 0.25rem 0.55rem; cursor: pointer;">+ Task</button>
                                        <button type="button" onclick="addAction('expiry-actions','notif')" class="btn btn-secondary btn-sm" style="font-size: 0.725rem; padding: 0.25rem 0.55rem; cursor: pointer;">+ Notification</button>
                                    </div>
                                </div>
                                <div id="expiry-actions" style="display: flex; flex-direction: column; gap: 0.5rem;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Row 9: Record Locking & Active Status -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                        <div style="background: #fbfbfe; border: 1px solid #e0e7ff; border-radius: var(--radius-lg); padding: 1rem;">
                            <label style="display: inline-flex; align-items: flex-start; gap: 0.65rem; cursor: pointer;">
                                <input type="checkbox" id="lock-record-checkbox" style="width: 16px; height: 16px; accent-color: #4f46e5; margin-top: 0.15rem;">
                                <div>
                                    <span style="font-size: 0.8125rem; font-weight: 700; color: #312e81; display: block;">Lock Record Until Final Approval</span>
                                    <span style="font-size: 0.725rem; color: #4b5563; display: block; margin-top: 0.15rem;">
                                        Lock record from editing while awaiting sign-off.
                                    </span>
                                </div>
                            </label>
                        </div>
                        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: var(--radius-lg); padding: 1rem;">
                            <label style="display: inline-flex; align-items: flex-start; gap: 0.65rem; cursor: pointer;">
                                <input type="checkbox" id="is-active-checkbox" checked style="width: 16px; height: 16px; accent-color: #16a34a; margin-top: 0.15rem;">
                                <div>
                                    <span style="font-size: 0.8125rem; font-weight: 700; color: #166534; display: block;">Workflow Active</span>
                                    <span style="font-size: 0.725rem; color: #15803d; display: block; margin-top: 0.15rem;">
                                        Rule will immediately evaluate according to its trigger mechanism.
                                    </span>
                                </div>
                            </label>
                        </div>
                    </div>

                </div>

                <!-- Modal Footer -->
                <div class="modal-footer"
                    style="flex-shrink: 0; background: #fff; border-top: 1px solid var(--zinc-200); padding: 1rem 1.5rem; display: flex; justify-content: flex-end; gap: 0.5rem;">
                    <button type="button" class="btn btn-secondary" onclick="closeApprovalModal()" style="cursor: pointer;">Cancel</button>
                    <button type="submit" id="modal-submit-btn" class="btn btn-primary" style="cursor: pointer;">Create Workflow</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Page Scripts -->
    <script>
        // Server Hydrated Data
        window.APPROVAL_RULES_MAP = @json(($approvalRules ?? collect())->keyBy('id'));
        window.ROLES_LIST = @json($roles ?? []);
        window.PROFILES_LIST = @json($profiles ?? []);
        window.USERS_LIST = @json($users ?? []);
        window.CSRF_TOKEN = window.CSRF_TOKEN || '{{ csrf_token() }}';
        var CSRF_TOKEN = window.CSRF_TOKEN;

        function handleCriteriaFieldChange(field, currentVal = '') {
            const wrapper = document.getElementById('criteria-value-wrapper');
            if (!wrapper) return;

            if (field === 'creator_role') {
                let html = '<select id="rule-criteria-value" class="form-select" style="font-size:0.8125rem;">';
                const seen = new Set();
                const roles = (window.ROLES_LIST && window.ROLES_LIST.length > 0)
                    ? window.ROLES_LIST
                    : [{ name: 'Sales User' }, { name: 'Sales Manager' }, { name: 'Sales Representative' }];
                roles.forEach(r => {
                    if (!seen.has(r.name)) {
                        seen.add(r.name);
                        const sel = (currentVal && currentVal.toLowerCase() === r.name.toLowerCase()) || (!currentVal && r.name === 'Sales User') ? 'selected' : '';
                        html += `<option value="${escapeHtml(r.name)}" ${sel}>${escapeHtml(r.name)}</option>`;
                    }
                });
                html += '</select>';
                wrapper.innerHTML = html;
            } else if (field === 'creator_profile') {
                let html = '<select id="rule-criteria-value" class="form-select" style="font-size:0.8125rem;">';
                const seen = new Set();
                const profiles = (window.PROFILES_LIST && window.PROFILES_LIST.length > 0)
                    ? window.PROFILES_LIST
                    : [{ name: 'Sales User' }, { name: 'Sales Manager' }, { name: 'Standard User' }];
                profiles.forEach(p => {
                    if (!seen.has(p.name)) {
                        seen.add(p.name);
                        const sel = (currentVal && currentVal.toLowerCase() === p.name.toLowerCase()) || (!currentVal && p.name === 'Sales User') ? 'selected' : '';
                        html += `<option value="${escapeHtml(p.name)}" ${sel}>${escapeHtml(p.name)}</option>`;
                    }
                });
                html += '</select>';
                wrapper.innerHTML = html;
            } else {
                wrapper.innerHTML = `<input id="rule-criteria-value" class="form-input" placeholder="e.g. Sales User" type="text" value="${escapeHtml(currentVal)}" style="font-size: 0.8125rem;">`;
            }
        }

        // Toast Feedback System
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toastNotification');
            const toastMsg = document.getElementById('toastMessage');
            const toastIcon = document.getElementById('toastIcon');
            if (!toast) return;

            toastMsg.textContent = message;
            if (type === 'error') {
                toast.style.background = '#dc2626';
                if (toastIcon) toastIcon.setAttribute('data-lucide', 'alert-circle');
            } else {
                toast.style.background = '#16a34a';
                if (toastIcon) toastIcon.setAttribute('data-lucide', 'check-circle-2');
            }

            toast.classList.remove('hidden');
            toast.classList.add('flex');
            if (window.lucide) lucide.createIcons();

            clearTimeout(window._toastTimer);
            window._toastTimer = setTimeout(() => {
                toast.classList.remove('flex');
                toast.classList.add('hidden');
            }, 3500);
        }

        // Open Modal for Create or Edit
        function openApprovalModal(rule = null) {
            const m = document.getElementById('approval-modal');
            const form = document.getElementById('approval-form');
            const titleEl = document.getElementById('modal-title');
            const submitBtn = document.getElementById('modal-submit-btn');

            if (!rule) {
                // CREATE MODE
                titleEl.textContent = 'Create Multi-Level Approval Workflow';
                submitBtn.textContent = 'Create Workflow';
                document.getElementById('rule-id').value = '';
                document.getElementById('rule-name').value = '';
                document.getElementById('rule-module').value = 'leads';
                document.getElementById('rule-description').value = '';

                // Radios
                const trRadio = document.querySelector('input[name="triggerType"][value="on_create"]');
                if (trRadio) trRadio.checked = true;
                const ncRadio = document.querySelector('input[name="notifChan"][value="both"]');
                if (ncRadio) ncRadio.checked = true;

                // Criteria (Default: Sales User Submitter Role)
                const critField = document.getElementById('rule-criteria-field');
                if (critField) critField.value = 'creator_role';
                handleCriteriaFieldChange('creator_role', 'Sales User');
                const critOp = document.getElementById('rule-criteria-operator');
                if (critOp) critOp.value = 'equals';

                // Levels
                const levelsWrap = document.getElementById('approval-levels');
                levelsWrap.innerHTML = '';
                addApprovalLevel({
                    step_name: 'Level 1: Sales Manager Review',
                    approver_type: 'manager',
                    approver_name: 'Sales Manager'
                });

                // Clear actions
                document.getElementById('approve-actions').innerHTML = '';
                document.getElementById('reject-actions').innerHTML = '';
                document.getElementById('expiry-actions').innerHTML = '';
                updateActionCounts();

                // Expiry
                document.getElementById('expiry-enable').checked = true;
                document.getElementById('expiry-body').style.display = 'flex';
                setExpiry(72);

                // Checkboxes
                document.getElementById('lock-record-checkbox').checked = true;
                document.getElementById('is-active-checkbox').checked = true;
            } else {
                // EDIT MODE
                titleEl.textContent = 'Edit Approval Workflow: ' + rule.name;
                submitBtn.textContent = 'Update Workflow';
                document.getElementById('rule-id').value = rule.id;
                document.getElementById('rule-name').value = rule.name || '';
                document.getElementById('rule-module').value = rule.module || 'leads';
                document.getElementById('rule-description').value = rule.description || '';

                // Trigger radio
                const trRadio = document.querySelector(`input[name="triggerType"][value="${rule.trigger_type || 'manual'}"]`);
                if (trRadio) trRadio.checked = true;

                // Notification channel radio
                const ncRadio = document.querySelector(`input[name="notifChan"][value="${rule.notification_channel || 'both'}"]`);
                if (ncRadio) ncRadio.checked = true;

                // Criteria
                const critField = document.getElementById('rule-criteria-field');
                if (critField) critField.value = rule.criteria_field || '';
                handleCriteriaFieldChange(rule.criteria_field || '', rule.criteria_value || '');
                const critOp = document.getElementById('rule-criteria-operator');
                if (critOp) critOp.value = rule.criteria_operator || 'equals';

                // Levels
                const levelsWrap = document.getElementById('approval-levels');
                levelsWrap.innerHTML = '';
                const steps = rule.steps && rule.steps.length > 0 ? rule.steps : [
                    { step_name: 'Level 1: Manager Review', approver_type: 'role', approver_name: 'Executive / CEO' }
                ];
                steps.forEach(st => addApprovalLevel(st));

                // Actions: Approval
                const appWrap = document.getElementById('approve-actions');
                appWrap.innerHTML = '';
                if (Array.isArray(rule.approval_actions)) {
                    rule.approval_actions.forEach(act => addAction('approve-actions', act.type || 'field', act));
                }

                // Actions: Rejection
                const rejWrap = document.getElementById('reject-actions');
                rejWrap.innerHTML = '';
                if (Array.isArray(rule.rejection_actions)) {
                    rule.rejection_actions.forEach(act => addAction('reject-actions', act.type || 'field', act));
                }

                // Expiry
                const expiryEnable = !!rule.enable_auto_expiry;
                document.getElementById('expiry-enable').checked = expiryEnable;
                document.getElementById('expiry-body').style.display = expiryEnable ? 'flex' : 'none';
                setExpiry(rule.auto_expiry_hours || 72);

                const expWrap = document.getElementById('expiry-actions');
                expWrap.innerHTML = '';
                if (Array.isArray(rule.expiry_actions)) {
                    rule.expiry_actions.forEach(act => addAction('expiry-actions', act.type || 'field', act));
                }
                updateActionCounts();

                // Checkboxes
                document.getElementById('lock-record-checkbox').checked = !!rule.lock_record_until_approved;
                document.getElementById('is-active-checkbox').checked = rule.is_active !== false && rule.is_active !== 0;
            }

            m.style.setProperty('display', 'flex', 'important');
            m.style.setProperty('opacity', '1', 'important');
            m.style.setProperty('visibility', 'visible', 'important');
            if (window.lucide) lucide.createIcons();
        }

        function closeApprovalModal() {
            document.getElementById('approval-modal').style.setProperty('display', 'none', 'important');
        }

        // Edit Approval Rule (Triggered by Edit button on card)
        async function editApprovalRule(id) {
            let rule = window.APPROVAL_RULES_MAP ? window.APPROVAL_RULES_MAP[id] : null;

            if (!rule) {
                try {
                    const res = await fetch(`/admin/approval-rules/${id}`, {
                        headers: { 'Accept': 'application/json' }
                    });
                    const json = await res.json();
                    if (json && json.success && json.data) {
                        rule = json.data;
                    }
                } catch (e) {
                    console.error('Failed to fetch approval rule details:', e);
                }
            }

            if (!rule) {
                showToast('Could not load workflow details', 'error');
                return;
            }

            openApprovalModal(rule);
        }

        // Delete Approval Rule (Triggered by Trash button on card)
        async function deleteApprovalRule(id, name) {
            if (!confirm(`Are you sure you want to delete approval workflow "${name}"?\nThis action cannot be undone.`)) {
                return;
            }

            try {
                const res = await fetch(`/admin/approval-rules/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json',
                    }
                });

                const data = await res.json();
                if (res.ok && data.success) {
                    showToast(data.message || 'Approval workflow deleted successfully.', 'success');
                    
                    // Animate and remove card from DOM
                    const card = document.getElementById(`rule-card-${id}`);
                    if (card) {
                        card.style.transition = 'all 0.3s ease';
                        card.style.opacity = '0';
                        card.style.transform = 'scale(0.96)';
                        setTimeout(() => {
                            card.remove();
                            delete window.APPROVAL_RULES_MAP[id];
                            
                            // If no cards left, reload to show empty state
                            const remaining = document.querySelectorAll('.rule-workflow-card');
                            if (remaining.length === 0) {
                                window.location.reload();
                            }
                        }, 300);
                    } else {
                        window.location.reload();
                    }
                } else {
                    showToast(data.message || 'Failed to delete approval workflow.', 'error');
                }
            } catch (err) {
                console.error(err);
                showToast('An error occurred while deleting the workflow.', 'error');
            }
        }

        // Toggle Approval Rule Active/Inactive
        async function toggleApprovalRule(id) {
            try {
                const res = await fetch(`/admin/approval-rules/${id}/toggle`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json',
                    }
                });

                const data = await res.json();
                if (res.ok && data.success) {
                    showToast(data.message || 'Status updated', 'success');
                    const badge = document.getElementById(`status-badge-${id}`);
                    if (badge && data.data) {
                        const active = !!data.data.is_active;
                        badge.textContent = active ? '● Active' : '○ Inactive';
                        badge.style.background = active ? '#dcfce7' : '#f4f4f5';
                        badge.style.color = active ? '#15803d' : '#71717a';
                        
                        const card = document.getElementById(`rule-card-${id}`);
                        if (card) {
                            card.style.borderColor = active ? '#fed7aa' : 'var(--zinc-200)';
                        }
                    }
                } else {
                    showToast(data.message || 'Failed to toggle status.', 'error');
                }
            } catch (err) {
                console.error(err);
                showToast('Error toggling workflow status.', 'error');
            }
        }

        // Add Step Level Row
        function addApprovalLevel(stepData = null) {
            const wrap = document.getElementById('approval-levels');
            const currentCount = wrap.querySelectorAll('.level-row').length;
            const newIndex = currentCount + 1;

            const stepNameVal = stepData ? (stepData.step_name || '') : `Level ${newIndex}: Sales Manager Review`;
            const approverType = stepData ? (stepData.approver_type || 'manager') : 'manager';
            const selectedApprover = stepData ? (stepData.approver_name || '') : '';

            const div = document.createElement('div');
            div.className = 'level-row';
            div.style.cssText = 'display: grid; grid-template-columns: 70px 1.5fr 150px 1.5fr 40px; gap: 0.5rem; align-items: center; padding: 0.75rem; background: var(--zinc-50); border-radius: var(--radius-lg); border: 1px solid var(--zinc-200);';
            
            div.innerHTML = `
                <span class="level-label" style="font-weight: 700; font-size: 0.8125rem; color: var(--zinc-800);">Level ${newIndex}</span>
                <input required placeholder="e.g. Sales Manager Review" class="form-input step-name-input" type="text" value="${escapeHtml(stepNameVal)}" style="font-size: 0.8125rem;">
                <select class="form-select step-type-select" onchange="handleApproverTypeChange(this)" style="font-size: 0.8125rem;">
                    <option value="manager" ${approverType === 'manager' ? 'selected' : ''}>Direct / Sales Manager</option>
                    <option value="role" ${approverType === 'role' ? 'selected' : ''}>Role</option>
                    <option value="profile" ${approverType === 'profile' ? 'selected' : ''}>Security Profile</option>
                    <option value="user" ${approverType === 'user' ? 'selected' : ''}>Specific User</option>
                </select>
                <select class="form-select step-target-select" style="font-size: 0.8125rem;">
                    <!-- Populated based on type -->
                </select>
                <button type="button" onclick="removeApprovalLevel(this)" class="btn btn-secondary btn-sm"
                    title="Remove Step" style="color: #e11d48; padding: 0.4rem; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="trash-2" style="width: 13px; height: 13px;"></i>
                </button>
            `;

            wrap.appendChild(div);

            const typeSelect = div.querySelector('.step-type-select');
            populateStepTargetSelect(div.querySelector('.step-target-select'), approverType, selectedApprover);

            renumberLevels();
            if (window.lucide) lucide.createIcons();
        }

        function handleApproverTypeChange(selectEl) {
            const row = selectEl.closest('.level-row');
            const targetSelect = row.querySelector('.step-target-select');
            populateStepTargetSelect(targetSelect, selectEl.value);
        }

        function populateStepTargetSelect(selectEl, type, selectedVal = '') {
            selectEl.innerHTML = '';
            if (type === 'manager') {
                const mgrOptions = [
                    'Sales Manager',
                    "Submitter's Direct Manager (Sales Manager)",
                    "Submitter's Line Manager"
                ];
                mgrOptions.forEach(m => {
                    const opt = document.createElement('option');
                    opt.value = m;
                    opt.textContent = m;
                    if (selectedVal && selectedVal.toLowerCase() === m.toLowerCase()) {
                        opt.selected = true;
                    }
                    selectEl.appendChild(opt);
                });
            } else if (type === 'profile') {
                if (window.PROFILES_LIST && window.PROFILES_LIST.length > 0) {
                    const seen = new Set();
                    window.PROFILES_LIST.forEach(p => {
                        if (!seen.has(p.name)) {
                            seen.add(p.name);
                            const opt = document.createElement('option');
                            opt.value = p.name;
                            opt.textContent = p.name;
                            if (selectedVal && (selectedVal === p.name || selectedVal == p.id)) {
                                opt.selected = true;
                            }
                            selectEl.appendChild(opt);
                        }
                    });
                } else {
                    const fallbackProfiles = ['Sales Manager', 'Sales User', 'System Administrator', 'Standard User'];
                    fallbackProfiles.forEach(p => {
                        const opt = document.createElement('option');
                        opt.value = p;
                        opt.textContent = p;
                        if (selectedVal === p) opt.selected = true;
                        selectEl.appendChild(opt);
                    });
                }
            } else if (type === 'user') {
                if (window.USERS_LIST && window.USERS_LIST.length > 0) {
                    window.USERS_LIST.forEach(u => {
                        const opt = document.createElement('option');
                        opt.value = u.name;
                        opt.textContent = `${u.name} (${u.email})`;
                        if (selectedVal && (selectedVal === u.name || selectedVal == u.id)) {
                            opt.selected = true;
                        }
                        selectEl.appendChild(opt);
                    });
                } else {
                    const opt = document.createElement('option');
                    opt.value = 'System Administrator';
                    opt.textContent = 'System Administrator';
                    selectEl.appendChild(opt);
                }
            } else {
                // Role type (default)
                if (window.ROLES_LIST && window.ROLES_LIST.length > 0) {
                    const seen = new Set();
                    window.ROLES_LIST.forEach(r => {
                        if (!seen.has(r.name)) {
                            seen.add(r.name);
                            const opt = document.createElement('option');
                            opt.value = r.name;
                            opt.textContent = r.name;
                            if (selectedVal && (selectedVal === r.name || selectedVal == r.id)) {
                                opt.selected = true;
                            }
                            selectEl.appendChild(opt);
                        }
                    });
                } else {
                    const fallbackRoles = ['Sales Manager', 'Sales User', 'Executive / CEO', 'VP of Sales', 'Procurement Lead', 'Finance & Billing Lead'];
                    fallbackRoles.forEach(r => {
                        const opt = document.createElement('option');
                        opt.value = r;
                        opt.textContent = r;
                        if (selectedVal === r) opt.selected = true;
                        selectEl.appendChild(opt);
                    });
                }
            }
        }

        function removeApprovalLevel(btn) {
            const wrap = document.getElementById('approval-levels');
            if (wrap.querySelectorAll('.level-row').length > 1) {
                btn.closest('.level-row').remove();
                renumberLevels();
            } else {
                showToast('Workflow must have at least one approval level.', 'error');
            }
        }

        function renumberLevels() {
            const rows = document.querySelectorAll('#approval-levels .level-row');
            rows.forEach((r, i) => {
                r.querySelector('.level-label').textContent = 'Level ' + (i + 1);
            });
            document.getElementById('level-count').textContent = rows.length;
        }

        // Actions Handler
        function addAction(containerId, type, existingData = null) {
            const labels = { field: 'Field Update', email: 'Email Alert', task: 'Task', notif: 'Popup Notification' };
            let body = '';

            const fieldNameVal = existingData ? (existingData.field || '') : '';
            const fieldValueVal = existingData ? (existingData.value || '') : '';
            const recipientVal = existingData ? (existingData.recipient || 'Submitter') : 'Submitter';
            const templateVal = existingData ? (existingData.template || '') : '';
            const taskTitleVal = existingData ? (existingData.title || existingData.task || '') : '';

            if (type === 'field') {
                body = `<div style="display:grid;grid-template-columns:1fr 1fr;gap:0.5rem;">
                            <div><label style="font-size:0.7rem;color:var(--zinc-600);display:block;margin-bottom:0.2rem;">Field Name:</label>
                                <input class="form-input act-field-name" type="text" value="${escapeHtml(fieldNameVal)}" placeholder="e.g. status, stage" style="font-size:0.75rem;padding:0.35rem 0.5rem;">
                            </div>
                            <div><label style="font-size:0.7rem;color:var(--zinc-600);display:block;margin-bottom:0.2rem;">New Target Value:</label>
                                <input class="form-input act-field-val" type="text" value="${escapeHtml(fieldValueVal)}" placeholder="e.g. Approved" style="font-size:0.75rem;padding:0.35rem 0.5rem;">
                            </div>
                        </div>`;
            } else if (type === 'email' || type === 'notif') {
                let templateOpts = '<option value="">-- Select Template --</option>';
                if (window.EMAIL_TEMPLATES_LIST && window.EMAIL_TEMPLATES_LIST.length > 0) {
                    window.EMAIL_TEMPLATES_LIST.forEach(t => {
                        const sel = templateVal == t.id || templateVal === t.name ? 'selected' : '';
                        templateOpts += `<option value="${escapeHtml(t.id)}" ${sel}>${escapeHtml(t.name)}</option>`;
                    });
                }
                body = `<div style="display:grid;grid-template-columns:140px 1fr;gap:0.65rem;">
                            <div><label style="font-size:0.7rem;color:var(--zinc-600);display:block;margin-bottom:0.2rem;font-weight:600;">Recipient:</label>
                                <select class="form-select act-recipient" style="font-size:0.75rem;padding:0.35rem 0.5rem;width:100%;">
                                    <option ${recipientVal === 'Submitter' ? 'selected' : ''}>Submitter</option>
                                    <option ${recipientVal === 'Record Owner' ? 'selected' : ''}>Record Owner</option>
                                    <option ${recipientVal === 'Specific Users' ? 'selected' : ''}>Specific Users</option>
                                </select>
                            </div>
                            <div><label style="font-size:0.7rem;color:var(--zinc-600);display:block;margin-bottom:0.2rem;font-weight:600;">Choose Template *:</label>
                                <select class="form-select act-template" style="font-size:0.75rem;padding:0.35rem 0.5rem;width:100%;">
                                    ${templateOpts}
                                </select>
                            </div>
                        </div>`;
            } else {
                body = `<input class="form-input act-task-title" type="text" value="${escapeHtml(taskTitleVal)}" placeholder="Task title" style="font-size:0.75rem;padding:0.35rem 0.5rem;">`;
            }

            const div = document.createElement('div');
            div.className = 'action-item';
            div.setAttribute('data-action-type', type);
            div.style.cssText = 'background:#fff;border:1px solid var(--zinc-200);border-radius:var(--radius-md);padding:0.75rem;display:flex;flex-direction:column;gap:0.5rem;';
            div.innerHTML = `<div style="display:flex;justify-content:space-between;align-items:center;">
                        <span style="font-size:0.785rem;font-weight:600;color:var(--zinc-800);">${labels[type]}</span>
                        <button type="button" onclick="removeAction(this)" class="btn btn-ghost btn-sm" style="color:#e11d48;padding:0.2rem;cursor:pointer;">✕</button></div>${body}`;
            
            document.getElementById(containerId).appendChild(div);
            updateActionCounts();
        }

        function removeAction(btn) {
            btn.closest('.action-item').remove();
            updateActionCounts();
        }

        function updateActionCounts() {
            [['approve-actions', 'approve-count'], ['reject-actions', 'reject-count'], ['expiry-actions', 'expiry-count']].forEach(([c, n]) => {
                const el = document.getElementById(n), box = document.getElementById(c);
                if (el && box) el.textContent = box.children.length;
            });
        }

        function setExpiry(h) {
            const expInput = document.getElementById('expiry-hours');
            if (expInput) expInput.value = h;
            document.querySelectorAll('.expiry-btn').forEach(b => {
                const on = b.dataset.hours == h;
                b.classList.toggle('btn-primary', on);
                b.classList.toggle('btn-secondary', !on);
            });
        }

        function serializeActions(containerId) {
            const box = document.getElementById(containerId);
            if (!box) return [];
            const actions = [];
            box.querySelectorAll('.action-item').forEach(item => {
                const type = item.getAttribute('data-action-type');
                if (type === 'field') {
                    const fName = item.querySelector('.act-field-name')?.value || '';
                    const fVal = item.querySelector('.act-field-val')?.value || '';
                    if (fName) actions.push({ type: 'field', field: fName, value: fVal });
                } else if (type === 'email' || type === 'notif') {
                    const rec = item.querySelector('.act-recipient')?.value || '';
                    const tmpl = item.querySelector('.act-template')?.value || '';
                    actions.push({ type: type, recipient: rec, template: tmpl });
                } else if (type === 'task') {
                    const task = item.querySelector('.act-task-title')?.value || '';
                    if (task) actions.push({ type: 'task', title: task });
                }
            });
            return actions;
        }

        // Form Submission (Create or Update)
        async function handleApprovalFormSubmit(e) {
            e.preventDefault();
            const submitBtn = document.getElementById('modal-submit-btn');
            if (submitBtn) submitBtn.disabled = true;

            try {
                const ruleId = document.getElementById('rule-id').value;
                const isEdit = !!ruleId;

                const name = document.getElementById('rule-name').value.trim();
                const module = document.getElementById('rule-module').value;
                const description = document.getElementById('rule-description').value.trim();

                const triggerTypeEl = document.querySelector('input[name="triggerType"]:checked');
                const trigger_type = triggerTypeEl ? triggerTypeEl.value : 'manual';

                const notifChanEl = document.querySelector('input[name="notifChan"]:checked');
                const notification_channel = notifChanEl ? notifChanEl.value : 'both';

                const criteria_field = document.getElementById('rule-criteria-field')?.value || null;
                const criteria_operator = document.getElementById('rule-criteria-operator')?.value || 'equals';
                const criteria_value = document.getElementById('rule-criteria-value')?.value || null;

                // Collect Steps
                const steps = [];
                document.querySelectorAll('#approval-levels .level-row').forEach((row, i) => {
                    const sName = row.querySelector('.step-name-input')?.value.trim() || `Level ${i + 1}`;
                    const sType = row.querySelector('.step-type-select')?.value || 'role';
                    const sTarget = row.querySelector('.step-target-select')?.value || '';
                    steps.push({
                        step_name: sName,
                        approver_type: sType,
                        approver_name: sTarget,
                        step_number: i + 1,
                    });
                });

                if (steps.length === 0) {
                    showToast('Please define at least one approval level step.', 'error');
                    if (submitBtn) submitBtn.disabled = false;
                    return;
                }

                // Collect Actions
                const approval_actions = serializeActions('approve-actions');
                const rejection_actions = serializeActions('reject-actions');
                const expiry_actions = serializeActions('expiry-actions');

                // Expiry
                const enable_auto_expiry = document.getElementById('expiry-enable').checked;
                const auto_expiry_hours = parseInt(document.getElementById('expiry-hours').value, 10) || 72;

                // Checkboxes
                const lock_record_until_approved = document.getElementById('lock-record-checkbox').checked;
                const is_active = document.getElementById('is-active-checkbox').checked;

                const payload = {
                    name,
                    module,
                    description,
                    trigger_type,
                    notification_channel,
                    criteria_field,
                    criteria_operator,
                    criteria_value,
                    steps,
                    approval_actions,
                    rejection_actions,
                    enable_auto_expiry,
                    auto_expiry_hours,
                    expiry_actions,
                    lock_record_until_approved,
                    is_active,
                };

                const url = isEdit ? `/admin/approval-rules/${ruleId}` : '/admin/approval-rules';
                const method = isEdit ? 'PUT' : 'POST';

                const res = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                    },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();
                if (res.ok && data.success) {
                    showToast(data.message || (isEdit ? 'Workflow updated successfully!' : 'Workflow created successfully!'), 'success');
                    closeApprovalModal();
                    setTimeout(() => window.location.reload(), 400);
                } else {
                    const errMsg = data.message || (data.errors ? Object.values(data.errors).flat().join(', ') : 'Failed to save workflow.');
                    showToast(errMsg, 'error');
                }
            } catch (err) {
                console.error(err);
                showToast('An error occurred while saving the workflow.', 'error');
            } finally {
                if (submitBtn) submitBtn.disabled = false;
            }
        }

        function escapeHtml(str) {
            if (!str) return '';
            return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        }
    </script>
@endsection