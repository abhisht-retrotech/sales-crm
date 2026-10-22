import { MarkerType } from '@xyflow/react';

export const WORKFLOW_PRESETS = {
  // --- APPROVAL PROCESSES (TABLE 1) ---
  'mega-deal-discount': {
    id: 'mega-deal-discount',
    title: 'Mega Deal Commercial Discount',
    subtitle: 'Deals exceeding $250k require Sequential VP & CEO Sign-off with Automatic Record Lock',
    entity: 'Opportunities',
    categoryType: 'Approval Process',
    badgeColor: '#4f46e5',
    nodes: [
      {
        id: 'node-1',
        type: 'processTrigger',
        position: { x: 80, y: 160 },
        data: {
          category: '1. TRIGGER CRITERIA',
          subtype: 'record_event',
          title: 'Mega Deal Discount Request',
          description: 'Triggered when deal value exceeds $250k with custom discount',
          condition: 'amount > $250,000 && discount > 20%'
        }
      },
      {
        id: 'node-lock',
        type: 'processAction',
        position: { x: 80, y: 390 },
        data: {
          category: 'SECURITY GOVERNANCE',
          subtype: 'lock_record',
          title: 'Enforce Record Lock',
          description: 'Locks opportunity from non-admin edits during review',
          condition: 'lock_record = true (Read-Only)',
          icon: 'Lock',
          targetPosition: 'top',
          isTerminal: true
        }
      },
      {
        id: 'node-2',
        type: 'processApprovalStep',
        position: { x: 440, y: 160 },
        data: {
          stepBadge: 'STEP 1: REGIONAL VP',
          approver: 'Regional Sales VP (Role)',
          sla: '24h SLA',
          title: 'Commercial VP Approval',
          condition: 'commercial_margin >= 18%'
        }
      },
      {
        id: 'node-3',
        type: 'processApprovalStep',
        position: { x: 840, y: 160 },
        data: {
          stepBadge: 'STEP 2: EXECUTIVE CEO',
          approver: 'Executive CEO (User)',
          sla: '48h SLA',
          title: 'Executive Sign-Off',
          condition: 'final_contract_cleared == true'
        }
      },
      {
        id: 'node-4',
        type: 'processAction',
        position: { x: 1240, y: 160 },
        data: {
          category: 'FINAL APPROVAL ACTION',
          subtype: 'update_field',
          title: 'Mark Closed Won & Unlock',
          description: 'Record approved, unlocked, and confirmation dispatched',
          condition: "stage -> 'Closed Won' | unlock_record",
          color: '#059669',
          isTerminal: true
        }
      },
      {
        id: 'node-5',
        type: 'processAction',
        position: { x: 640, y: 400 },
        data: {
          category: 'REJECTION ROLLBACK',
          subtype: 'exception',
          title: 'Rollback & Notify Rep',
          description: 'Reverts stage to Discounts Rejected and emails owner',
          condition: "stage -> 'Discounts Rejected' | notify owner",
          color: '#e11d48',
          icon: 'Mail',
          targetPosition: 'top',
          isTerminal: true
        }
      }
    ],
    edges: [
      {
        id: 'edge-1-2',
        source: 'node-1',
        target: 'node-2',
        type: 'smoothstep',
        animated: true,
        style: { stroke: '#4f46e5', strokeWidth: 2.2 },
        markerEnd: { type: MarkerType.ArrowClosed, color: '#4f46e5' },
        label: 'Submit for Sign-off'
      },
      {
        id: 'edge-1-lock',
        source: 'node-1',
        target: 'node-lock',
        type: 'smoothstep',
        animated: false,
        style: { stroke: '#6366f1', strokeWidth: 2, strokeDasharray: '4 4' },
        markerEnd: { type: MarkerType.ArrowClosed, color: '#6366f1' },
        label: 'Auto-Lock'
      },
      {
        id: 'edge-2-3',
        source: 'node-2',
        sourceHandle: 'approved',
        target: 'node-3',
        type: 'smoothstep',
        animated: true,
        style: { stroke: '#059669', strokeWidth: 2.2 },
        markerEnd: { type: MarkerType.ArrowClosed, color: '#059669' },
        label: 'Tier 1 Approved'
      },
      {
        id: 'edge-3-4',
        source: 'node-3',
        sourceHandle: 'approved',
        target: 'node-4',
        type: 'smoothstep',
        animated: true,
        style: { stroke: '#059669', strokeWidth: 2.2 },
        markerEnd: { type: MarkerType.ArrowClosed, color: '#059669' },
        label: 'Tier 2 Approved'
      },
      {
        id: 'edge-2-5',
        source: 'node-2',
        sourceHandle: 'rejected',
        target: 'node-5',
        type: 'smoothstep',
        animated: false,
        style: { stroke: '#e11d48', strokeWidth: 2.2 },
        markerEnd: { type: MarkerType.ArrowClosed, color: '#e11d48' },
        label: 'VP Rejected'
      },
      {
        id: 'edge-3-5',
        source: 'node-3',
        sourceHandle: 'rejected',
        target: 'node-5',
        type: 'smoothstep',
        animated: false,
        style: { stroke: '#e11d48', strokeWidth: 2.2 },
        markerEnd: { type: MarkerType.ArrowClosed, color: '#e11d48' },
        label: 'CEO Rejected'
      }
    ]
  },

  'procurement-po': {
    id: 'procurement-po',
    title: 'High-Spend Procurement PO Sign-Off',
    subtitle: 'Supplier Purchase Orders exceeding $100k require Supply Chain Director Approval',
    entity: 'Purchase Deals',
    categoryType: 'Approval Process',
    badgeColor: '#7c3aed',
    nodes: [
      {
        id: 'node-1',
        type: 'processTrigger',
        position: { x: 80, y: 160 },
        data: {
          category: '1. TRIGGER CRITERIA',
          subtype: 'record_event',
          title: 'High-Value PO Created',
          description: 'Triggered when Purchase Deal total exceeds $100,000 threshold',
          condition: 'amount > $100,000'
        }
      },
      {
        id: 'node-2',
        type: 'processApprovalStep',
        position: { x: 440, y: 160 },
        data: {
          stepBadge: 'STEP 1: SUPPLY CHAIN',
          approver: 'Supply Chain Director (Role)',
          sla: '48h SLA',
          title: 'Vendor Compliance Sign-off',
          condition: 'vendor_iso_compliant == true'
        }
      },
      {
        id: 'node-3',
        type: 'processAction',
        position: { x: 840, y: 160 },
        data: {
          category: 'ON APPROVAL ACTION',
          subtype: 'update_field',
          title: 'Issue PO & Vendor Notification',
          description: 'Marks stage as PO Issued and triggers supplier confirmation',
          condition: "stage -> 'PO Issued' | notify vendor",
          color: '#059669'
        }
      },
      {
        id: 'node-4',
        type: 'processAction',
        position: { x: 1220, y: 160 },
        data: {
          category: 'INTEGRATION API',
          subtype: 'webhook',
          title: 'ERP Procurement Dispatch',
          description: 'Syncs authorized PO into enterprise SAP/Oracle ERP system',
          condition: 'POST /api/erp/procurement/issue',
          color: '#7c3aed',
          icon: 'Globe',
          isTerminal: true
        }
      },
      {
        id: 'node-5',
        type: 'processAction',
        position: { x: 440, y: 390 },
        data: {
          category: 'REJECTION ROLLBACK',
          subtype: 'exception',
          title: 'PO Rejection Rollback',
          description: 'Reverts stage to PO Rejected and alerts procurement buyer',
          condition: "stage -> 'PO Rejected' | alert buyer",
          color: '#e11d48',
          icon: 'AlertCircle',
          targetPosition: 'top',
          isTerminal: true
        }
      }
    ],
    edges: [
      {
        id: 'edge-1-2',
        source: 'node-1',
        target: 'node-2',
        type: 'smoothstep',
        animated: true,
        style: { stroke: '#7c3aed', strokeWidth: 2.2 },
        markerEnd: { type: MarkerType.ArrowClosed, color: '#7c3aed' },
        label: 'Trigger PO Audit'
      },
      {
        id: 'edge-2-3',
        source: 'node-2',
        sourceHandle: 'approved',
        target: 'node-3',
        type: 'smoothstep',
        animated: true,
        style: { stroke: '#059669', strokeWidth: 2.2 },
        markerEnd: { type: MarkerType.ArrowClosed, color: '#059669' },
        label: 'Director Approved'
      },
      {
        id: 'edge-3-4',
        source: 'node-3',
        target: 'node-4',
        type: 'smoothstep',
        animated: true,
        style: { stroke: '#7c3aed', strokeWidth: 2.2 },
        markerEnd: { type: MarkerType.ArrowClosed, color: '#7c3aed' },
        label: 'PO Confirmed'
      },
      {
        id: 'edge-2-5',
        source: 'node-2',
        sourceHandle: 'rejected',
        target: 'node-5',
        type: 'smoothstep',
        animated: false,
        style: { stroke: '#e11d48', strokeWidth: 2.2 },
        markerEnd: { type: MarkerType.ArrowClosed, color: '#e11d48' },
        label: 'PO Rejected'
      }
    ]
  },

  'wire-clearance': {
    id: 'wire-clearance',
    title: 'Bank Wire Discrepancy Clearance',
    subtitle: 'Unallocated and mismatched bank wires require Corporate Controller audit clearance',
    entity: 'Wires',
    categoryType: 'Approval Process',
    badgeColor: '#059669',
    nodes: [
      {
        id: 'node-1',
        type: 'processTrigger',
        position: { x: 80, y: 160 },
        data: {
          category: '1. TRIGGER CRITERIA',
          subtype: 'webhook',
          title: 'Unallocated Wire Ingestion',
          description: 'Triggered when bank statement ingest encounters unallocated funds',
          condition: "status == 'Unallocated'"
        }
      },
      {
        id: 'node-2',
        type: 'processApprovalStep',
        position: { x: 440, y: 160 },
        data: {
          stepBadge: 'STEP 1: CONTROLLER',
          approver: 'Corporate Controller (Role)',
          sla: '12h Priority SLA',
          title: 'SWIFT/UTR Audit Validation',
          condition: 'bank_reference_matched == true'
        }
      },
      {
        id: 'node-3',
        type: 'processAction',
        position: { x: 840, y: 160 },
        data: {
          category: 'FINAL APPROVAL ACTION',
          subtype: 'update_field',
          title: 'Reconcile & General Ledger Post',
          description: 'Sets status to Reconciled and posts credit voucher in accounting',
          condition: "status -> 'Reconciled' | post ledger",
          color: '#059669',
          isTerminal: true
        }
      },
      {
        id: 'node-4',
        type: 'processAction',
        position: { x: 440, y: 390 },
        data: {
          category: 'REJECTION ROLLBACK',
          subtype: 'exception',
          title: 'Flag Investigation & CFO Alert',
          description: 'Marks wire as Flagged Investigation and escalates discrepancy to CFO',
          condition: "status -> 'Flagged Investigation' | alert CFO",
          color: '#e11d48',
          icon: 'AlertCircle',
          targetPosition: 'top',
          isTerminal: true
        }
      }
    ],
    edges: [
      {
        id: 'edge-1-2',
        source: 'node-1',
        target: 'node-2',
        type: 'smoothstep',
        animated: true,
        style: { stroke: '#059669', strokeWidth: 2.2 },
        markerEnd: { type: MarkerType.ArrowClosed, color: '#059669' },
        label: 'Discrepancy Detected'
      },
      {
        id: 'edge-2-3',
        source: 'node-2',
        sourceHandle: 'approved',
        target: 'node-3',
        type: 'smoothstep',
        animated: true,
        style: { stroke: '#059669', strokeWidth: 2.2 },
        markerEnd: { type: MarkerType.ArrowClosed, color: '#059669' },
        label: 'Controller Cleared'
      },
      {
        id: 'edge-2-4',
        source: 'node-2',
        sourceHandle: 'rejected',
        target: 'node-4',
        type: 'smoothstep',
        animated: false,
        style: { stroke: '#e11d48', strokeWidth: 2.2 },
        markerEnd: { type: MarkerType.ArrowClosed, color: '#e11d48' },
        label: 'Unresolved / Fraud Suspect'
      }
    ]
  },

  'air-freight': {
    id: 'air-freight',
    title: 'Expedited Air Freight Dispatch',
    subtitle: 'Priority Air Express shipments require Procurement Lead tariff approval before courier booking',
    entity: 'Shipping',
    categoryType: 'Approval Process',
    badgeColor: '#0284c7',
    nodes: [
      {
        id: 'node-1',
        type: 'processTrigger',
        position: { x: 80, y: 160 },
        data: {
          category: '1. TRIGGER CRITERIA',
          subtype: 'record_event',
          title: 'Air Express Shipment Request',
          description: 'Triggered when carrier is set to priority air freight',
          condition: "carrier == 'Air Express'"
        }
      },
      {
        id: 'node-2',
        type: 'processApprovalStep',
        position: { x: 440, y: 160 },
        data: {
          stepBadge: 'STEP 1: PROCUREMENT',
          approver: 'Procurement Lead (Role)',
          sla: '8h Express SLA',
          title: 'Tariff Rate Sign-off',
          condition: 'freight_cost_variance <= 15%'
        }
      },
      {
        id: 'node-3',
        type: 'processAction',
        position: { x: 840, y: 160 },
        data: {
          category: 'FINAL APPROVAL ACTION',
          subtype: 'update_field',
          title: 'Dispatch & Live Flight SMS',
          description: 'Sets status to In Transit and sends tracking SMS alert',
          condition: "status -> 'In Transit' | send tracking SMS",
          color: '#059669',
          isTerminal: true
        }
      },
      {
        id: 'node-4',
        type: 'processAction',
        position: { x: 440, y: 390 },
        data: {
          category: 'REJECTION ROLLBACK',
          subtype: 'exception',
          title: 'Hold Freight & Alert Logistics',
          description: 'Puts shipment on Hold pending revised cargo weight/dims',
          condition: "status -> 'Hold' | notify logistics lead",
          color: '#e11d48',
          icon: 'AlertCircle',
          targetPosition: 'top',
          isTerminal: true
        }
      }
    ],
    edges: [
      {
        id: 'edge-1-2',
        source: 'node-1',
        target: 'node-2',
        type: 'smoothstep',
        animated: true,
        style: { stroke: '#0284c7', strokeWidth: 2.2 },
        markerEnd: { type: MarkerType.ArrowClosed, color: '#0284c7' },
        label: 'Air Express Staged'
      },
      {
        id: 'edge-2-3',
        source: 'node-2',
        sourceHandle: 'approved',
        target: 'node-3',
        type: 'smoothstep',
        animated: true,
        style: { stroke: '#059669', strokeWidth: 2.2 },
        markerEnd: { type: MarkerType.ArrowClosed, color: '#059669' },
        label: 'Tariff Cleared'
      },
      {
        id: 'edge-2-4',
        source: 'node-2',
        sourceHandle: 'rejected',
        target: 'node-4',
        type: 'smoothstep',
        animated: false,
        style: { stroke: '#e11d48', strokeWidth: 2.2 },
        markerEnd: { type: MarkerType.ArrowClosed, color: '#e11d48' },
        label: 'Tariff Exceeded Budget'
      }
    ]
  },

  // --- PROCESS AUTOMATION WORKFLOWS (TABLE 2) ---
  'cargo-specifications': {
    id: 'cargo-specifications',
    title: 'Cargo Specifications & Heavy Lift',
    subtitle: 'Captures oversize cargo weight & dimensions and conditionally triggers logistics escalation',
    entity: 'Opportunities',
    categoryType: 'Automation Rule',
    badgeColor: '#2563eb',
    nodes: [
      {
        id: 'node-0364-yj1',
        type: 'processTrigger',
        position: { x: 420, y: 190 },
        selected: true,
        data: {
          category: 'TRIGGER',
          subtype: 'button_click',
          title: 'Custom Button Click',
          description: 'Starts when user clicks button on record header',
          button_label: 'Execute Process',
          condition: "button_click == 'Execute Process'",
          color: '#4f46e5'
        }
      }
    ],
    edges: []
  },

  'record-event-flow': {
    id: 'record-event-flow',
    title: 'Record Event (Create/Update)',
    subtitle: 'Starts workflow automatically whenever an Opportunity record is created or modified',
    entity: 'Opportunities',
    categoryType: 'Automation Rule',
    badgeColor: '#2563eb',
    nodes: [
      {
        id: 'node-7525-oiy',
        type: 'processTrigger',
        position: { x: 420, y: 190 },
        selected: true,
        data: {
          category: 'TRIGGER',
          subtype: 'record_event',
          title: 'Record Event (Create/Update)',
          description: 'Starts automatically on record creation or field edit',
          color: '#4f46e5'
        }
      }
    ],
    edges: []
  },

  'dynamic-grid-flow': {
    id: 'dynamic-grid-flow',
    title: 'Dynamic Grid Table',
    subtitle: 'Interactive specification data table with add rows and automatic calculations',
    entity: 'Opportunities',
    categoryType: 'Automation Rule',
    badgeColor: '#0284c7',
    nodes: [
      {
        id: 'node-3686-01n',
        type: 'processScreen',
        position: { x: 420, y: 190 },
        selected: true,
        data: {
          category: 'INTERACTIVE SCREEN',
          subtype: 'data_table',
          title: 'Dynamic Grid Table',
          description: 'Interactive table with add rows & auto calculations',
          modal_title: 'Specification Data Table',
          instructions: 'Please enter child specification line items.',
          allow_add_row: true,
          columns: [
            { header: 'Item Name', key: 'item', type: 'Text (General)' },
            { header: 'Quantity', key: 'qty', type: 'Number (with Auto Total Sum)' },
            { header: 'Gross Weight (KG)', key: 'weight', type: 'Number (with Auto Total Sum)' }
          ],
          color: '#0284c7'
        }
      }
    ],
    edges: []
  },

  'form-dialog-flow': {
    id: 'form-dialog-flow',
    title: 'Interactive Form Dialog',
    subtitle: 'Guided modal input fields and prompt collector for opportunity data intake',
    entity: 'Opportunities',
    categoryType: 'Automation Rule',
    badgeColor: '#0284c7',
    nodes: [
      {
        id: 'node-0324-ba1',
        type: 'processScreen',
        position: { x: 420, y: 190 },
        selected: true,
        data: {
          category: 'INTERACTIVE SCREEN',
          subtype: 'modal_form',
          title: 'Interactive Form Dialog',
          description: 'Presents guided input fields and prompts to user',
          modal_title: 'Opportunity Data Intake Dialog',
          instructions: 'Please enter the requested details before proceeding.',
          submit_button_text: 'Submit & Proceed',
          form_fields: [
            { label: 'Reason / Purpose', name: 'reason', type: 'Text Input', required: true },
            { label: 'Priority Level', name: 'priority_level', type: 'Dropdown (Select)', options: 'Low, Medium, High, Urgent', required: true },
            { label: 'Target Close Date', name: 'target_close_date', type: 'Date Picker', required: true },
            { label: 'Additional Comments', name: 'comments', type: 'Textarea (Long Text)', required: false }
          ],
          color: '#0284c7'
        }
      }
    ],
    edges: []
  },

  'if-else-branch-flow': {
    id: 'if-else-branch-flow',
    title: 'If / Else Branch Condition',
    subtitle: 'Splits flow execution based on field evaluation or mathematical rules',
    entity: 'Opportunities',
    categoryType: 'Automation Rule',
    badgeColor: '#d97706',
    nodes: [
      {
        id: 'node-8406-3ba',
        type: 'processDecision',
        position: { x: 420, y: 190 },
        selected: true,
        data: {
          category: 'DECISION LOGIC',
          subtype: 'condition',
          title: 'If / Else Branch Condition',
          description: 'Splits flow execution based on field or math rules',
          target_field: 'Opportunity Amount (amount)',
          target_field_key: 'amount',
          operator: 'Greater than (>)',
          operator_key: 'greater_than',
          comparison_value: '50000',
          condition: 'If amount greater_than "50000"',
          color: '#d97706'
        }
      }
    ],
    edges: []
  },

  'update-record-flow': {
    id: 'update-record-flow',
    title: 'Update Record Field',
    subtitle: 'Silently mutates record attributes such as stage or status in the background',
    entity: 'Opportunities',
    categoryType: 'Automation Rule',
    badgeColor: '#059669',
    nodes: [
      {
        id: 'node-4783-euc',
        type: 'processAction',
        position: { x: 420, y: 190 },
        selected: true,
        data: {
          category: 'BACKGROUND ACTION',
          subtype: 'update_field',
          title: 'Update Record Field',
          description: 'Silently changes field value (e.g. stage, status)',
          target_field: 'Stage (stage)',
          target_field_key: 'stage',
          target_value: 'Approved',
          mutate_fields: [
            { field: 'Stage (stage)', key: 'stage', value: 'Approved' }
          ],
          condition: 'Set stage = "Approved"',
          color: '#059669'
        }
      }
    ],
    edges: []
  },

  'create-record-flow': {
    id: 'create-record-flow',
    title: 'Create Child Record / Task',
    subtitle: 'Inserts new Task, Invoice, or related record with automated assignment',
    entity: 'Opportunities',
    categoryType: 'Automation Rule',
    badgeColor: '#059669',
    nodes: [
      {
        id: 'node-0883-916',
        type: 'processAction',
        position: { x: 420, y: 190 },
        selected: true,
        data: {
          category: 'BACKGROUND ACTION',
          subtype: 'create_record',
          title: 'Create Child Record / Task',
          description: 'Inserts new Task, Invoice, or related record',
          target_module: 'Tasks & Action Items (tasks)',
          auto_link_parent: true,
          task_title: 'Follow-up Task: Process Action for {{name}}',
          task_guidance: 'Automated task created by Process Studio workflow.\nPlease review and take appropriate action.',
          priority: 'High Priority',
          due_date_offset: '+1 Business Day',
          assign_to: 'Assigned Owner of Current Record',
          condition: '+ Insert TASKS High',
          color: '#059669'
        }
      }
    ],
    edges: []
  },

  'send-email-flow': {
    id: 'send-email-flow',
    title: 'Send Email Template',
    subtitle: 'Dispatches templated email notification with dynamic recipient and variable merging',
    entity: 'Opportunities',
    categoryType: 'Automation Rule',
    badgeColor: '#059669',
    nodes: [
      {
        id: 'node-1541-641',
        type: 'processAction',
        position: { x: 420, y: 190 },
        selected: true,
        data: {
          category: 'BACKGROUND ACTION',
          subtype: 'send_email',
          title: 'Send Email Template',
          description: 'Dispatches templated email notification',
          recipient_source: 'Contact / Customer on Record (Dynamic)',
          record_email_field: 'Primary Email (email)',
          cc_email: '',
          bcc_email: '',
          email_template: '-- Custom Subject & Body (Manual) -',
          subject: 'Notification: Update regarding {{name}}',
          body: 'Hello {{contact_name}},\n\nThis is an automated notification regarding your recent request.',
          send_timing: 'Immediately (Instant trigger upon step)',
          execution_mode: 'Automated Silent Dispatch (Background)',
          send_from_mailbox: 'System Mailer (no-reply@company.com)',
          high_priority_delivery: false,
          attach_flow_pdf: false,
          color: '#059669'
        }
      }
    ],
    edges: []
  },

  'toast-alert-flow': {
    id: 'toast-alert-flow',
    title: 'Pop-up Toast Alert',
    subtitle: 'Displays quick ephemeral confirmation toast to the user interface',
    entity: 'Opportunities',
    categoryType: 'Automation Rule',
    badgeColor: '#059669',
    nodes: [
      {
        id: 'node-2276-7qj',
        type: 'processAction',
        position: { x: 420, y: 190 },
        selected: true,
        data: {
          category: 'BACKGROUND ACTION',
          subtype: 'show_toast',
          title: 'Pop-up Toast Alert',
          description: 'Shows confirmation, warning, or success toast',
          toast_message: 'Business process step completed successfully.',
          condition: 'Business process step completed successfully.',
          toast_tone: 'Success (Green)',
          color: '#059669'
        }
      }
    ],
    edges: []
  },

  'lock-record-flow': {
    id: 'lock-record-flow',
    title: 'Lock / Unlock Record',
    subtitle: 'Enforces admin-only edit lock on the record to prevent standard user modifications',
    entity: 'Opportunities',
    categoryType: 'Automation Rule',
    badgeColor: '#059669',
    nodes: [
      {
        id: 'node-8037-9k8',
        type: 'processAction',
        position: { x: 420, y: 190 },
        selected: true,
        data: {
          category: 'BACKGROUND ACTION',
          subtype: 'lock_record',
          title: 'Lock / Unlock Record',
          description: 'Enforces admin-only edit lock on the record',
          lock_action: '🔒 Enforce Edit Lock (Make Record Read-Only)',
          lock_action_label: 'Enforce Edit Lock',
          bypass_authority: 'System Administrators Only',
          bypass_authority_badge: 'Admin Only',
          strict_lock: true,
          notice_banner_style: 'Amber Warning ("Record Locked Pending Review")',
          notice_message: 'This record is locked pending executive/process review. Contact your system administrator to request changes.',
          color: '#059669'
        }
      }
    ],
    edges: []
  },

  'opp-won-sync': {
    id: 'opp-won-sync',
    title: 'Opportunity Won → Procurement Sync',
    subtitle: 'End-to-End Enterprise Flow: PO Verification, Margin Audit, Auto PO Confirmation, PDF Generation, ERP Webhook Dispatch & Logistics Staging',
    entity: 'Opportunities',
    categoryType: 'Automation Rule',
    badgeColor: '#2563eb',
    nodes: [
      {
        id: 'node-1',
        type: 'processTrigger',
        position: { x: 60, y: 160 },
        data: {
          category: '1. EVENT TRIGGER',
          subtype: 'record_event',
          title: 'Opportunity Stage Change',
          description: 'Triggered automatically when an opportunity is updated to Closed Won state with valid revenue',
          condition: "stage == 'Closed Won' && amount > 0"
        }
      },
      {
        id: 'node-2',
        type: 'processDecision',
        position: { x: 420, y: 160 },
        data: {
          category: '2. PO VERIFICATION',
          subtype: 'condition',
          title: 'Check Linked PO',
          description: 'Evaluates whether a coupled procurement purchase deal (purchase_deal_id) is linked',
          condition: 'purchase_deal_id is present',
          color: '#d97706'
        }
      },
      // Exception Path (When PO is Missing)
      {
        id: 'node-ex-task',
        type: 'processAction',
        position: { x: 420, y: 440 },
        data: {
          category: 'EXCEPTION RECOVERY',
          subtype: 'exception',
          title: 'Create Urgent Procurement Task',
          description: 'Assigns urgent task to Deal Owner & Procurement team to create and link vendor PO',
          condition: "Task: 'Link Supplier PO' (Due: +4h, Priority: Urgent)",
          color: '#e11d48',
          icon: 'AlertCircle',
          targetPosition: 'top'
        }
      },
      {
        id: 'node-ex-alert',
        type: 'processAction',
        position: { x: 420, y: 680 },
        data: {
          category: 'EXCEPTION GOVERNANCE',
          subtype: 'send_email',
          title: 'Alert Deal Owner & Ops Channel',
          description: 'Dispatches high-priority in-app and Slack notification for missing procurement deal',
          condition: "Alert: 'Missing Supplier PO for {opp.name}'",
          color: '#e11d48',
          icon: 'Bell',
          targetPosition: 'top',
          isTerminal: true
        }
      },
      // Healthy Path (PO Linked)
      {
        id: 'node-3',
        type: 'processDecision',
        position: { x: 800, y: 160 },
        data: {
          category: '3. PROFITABILITY CHECK',
          subtype: 'condition',
          title: 'Validate Margin & Terms',
          description: 'Calculates gross deal margin and confirms vendor payment credit terms',
          condition: '(opp.amount - po.amount) / opp.amount >= 15%',
          color: '#0284c7'
        }
      },
      // Margin Warning Sub-branch
      {
        id: 'node-warn-margin',
        type: 'processAction',
        position: { x: 800, y: 440 },
        data: {
          category: 'MARGIN GOVERNANCE',
          subtype: 'update_field',
          title: 'Flag Commercial Review',
          description: 'Flags PO with status Low Margin Review and requests CFO sign-off before ERP release',
          condition: "PO.finance_review -> 'Low Margin Review'",
          color: '#d97706',
          icon: 'AlertTriangle',
          targetPosition: 'top',
          isTerminal: true
        }
      },
      // Confirmed Path
      {
        id: 'node-4',
        type: 'processAction',
        position: { x: 1180, y: 160 },
        data: {
          category: '4. PO CONFIRMATION',
          subtype: 'update_field',
          title: 'Update Procurement Deal',
          description: 'Mutates PO stage to Supplier Confirmed, logs confirmed timestamp, and syncs Sales Order ref #SO-{id}',
          condition: "PO.stage -> 'Supplier Confirmed' | sync SO_ref",
          color: '#059669',
          icon: 'Edit3'
        }
      },
      {
        id: 'node-5',
        type: 'processAction',
        position: { x: 1560, y: 160 },
        data: {
          category: '5. DOCUMENT AUTOMATION',
          subtype: 'document_gen',
          title: 'Auto-Generate PO PDF',
          description: 'Generates official Purchase Order & Proforma Agreement PDF and attaches to both Opportunity and PO',
          condition: "Generate 'Commercial PO & Agreement PDF'",
          color: '#4f46e5',
          icon: 'FileText'
        }
      },
      {
        id: 'node-6',
        type: 'processAction',
        position: { x: 1940, y: 160 },
        data: {
          category: '6. OUTBOUND INTEGRATION',
          subtype: 'webhook',
          title: 'ERP Webhook Outbound Sync',
          description: 'Dispatches signed order & PO line-items payload to SAP / NetSuite ERP endpoint with 3x retry policy',
          condition: 'POST /api/erp/dispatch (Payload: SO + PO Line Items)',
          color: '#7c3aed',
          icon: 'Globe'
        }
      },
      {
        id: 'node-7',
        type: 'processAction',
        position: { x: 2320, y: 160 },
        data: {
          category: '7. LOGISTICS & FULFILLMENT',
          subtype: 'shipment',
          title: 'Initialize Inbound Freight',
          description: 'Creates linked Shipment draft record with status Inbound Cargo Staged and assigns warehouse task',
          condition: "Shipment.status -> 'Staged' | Assign Warehouse Task",
          color: '#0284c7',
          icon: 'Truck'
        }
      },
      {
        id: 'node-8',
        type: 'processAction',
        position: { x: 2700, y: 160 },
        data: {
          category: '8. CUSTOMER & AUDIT',
          subtype: 'send_email',
          title: 'Client Order Confirmation',
          description: 'Sends branded confirmation email with PDF to customer and appends immutable audit event to deal timeline',
          condition: "Send Email: 'Order #SO Confirmed' | Log Timeline",
          color: '#059669',
          icon: 'Mail',
          isTerminal: true
        }
      }
    ],
    edges: [
      {
        id: 'edge-1-2',
        source: 'node-1',
        target: 'node-2',
        type: 'smoothstep',
        animated: true,
        style: { stroke: '#6366f1', strokeWidth: 2.2 },
        markerEnd: { type: MarkerType.ArrowClosed, color: '#6366f1' },
        label: 'Stage: Closed Won'
      },
      {
        id: 'edge-2-3',
        source: 'node-2',
        sourceHandle: 'yes',
        target: 'node-3',
        type: 'smoothstep',
        animated: true,
        style: { stroke: '#059669', strokeWidth: 2.2 },
        markerEnd: { type: MarkerType.ArrowClosed, color: '#059669' },
        label: 'Yes (PO Found)'
      },
      {
        id: 'edge-2-ex',
        source: 'node-2',
        sourceHandle: 'no',
        target: 'node-ex-task',
        type: 'smoothstep',
        animated: false,
        style: { stroke: '#e11d48', strokeWidth: 2.2 },
        markerEnd: { type: MarkerType.ArrowClosed, color: '#e11d48' },
        label: 'No (PO Missing)'
      },
      {
        id: 'edge-ex-alert',
        source: 'node-ex-task',
        target: 'node-ex-alert',
        type: 'smoothstep',
        animated: false,
        style: { stroke: '#e11d48', strokeWidth: 2 },
        markerEnd: { type: MarkerType.ArrowClosed, color: '#e11d48' },
        label: 'Escalate Alert'
      },
      {
        id: 'edge-3-4',
        source: 'node-3',
        sourceHandle: 'yes',
        target: 'node-4',
        type: 'smoothstep',
        animated: true,
        style: { stroke: '#059669', strokeWidth: 2.2 },
        markerEnd: { type: MarkerType.ArrowClosed, color: '#059669' },
        label: 'Margin >= 15% (Healthy)'
      },
      {
        id: 'edge-3-warn',
        source: 'node-3',
        sourceHandle: 'no',
        target: 'node-warn-margin',
        type: 'smoothstep',
        animated: false,
        style: { stroke: '#d97706', strokeWidth: 2.2 },
        markerEnd: { type: MarkerType.ArrowClosed, color: '#d97706' },
        label: 'Margin < 15% (Low)'
      },
      {
        id: 'edge-4-5',
        source: 'node-4',
        target: 'node-5',
        type: 'smoothstep',
        animated: true,
        style: { stroke: '#059669', strokeWidth: 2.2 },
        markerEnd: { type: MarkerType.ArrowClosed, color: '#059669' },
        label: 'PO Confirmed'
      },
      {
        id: 'edge-5-6',
        source: 'node-5',
        target: 'node-6',
        type: 'smoothstep',
        animated: true,
        style: { stroke: '#7c3aed', strokeWidth: 2.2 },
        markerEnd: { type: MarkerType.ArrowClosed, color: '#7c3aed' },
        label: 'Doc Attached'
      },
      {
        id: 'edge-6-7',
        source: 'node-6',
        target: 'node-7',
        type: 'smoothstep',
        animated: true,
        style: { stroke: '#0284c7', strokeWidth: 2.2 },
        markerEnd: { type: MarkerType.ArrowClosed, color: '#0284c7' },
        label: 'ERP 200 OK'
      },
      {
        id: 'edge-7-8',
        source: 'node-7',
        target: 'node-8',
        type: 'smoothstep',
        animated: true,
        style: { stroke: '#10b981', strokeWidth: 2.2 },
        markerEnd: { type: MarkerType.ArrowClosed, color: '#10b981' },
        label: 'Fulfillment Active'
      }
    ]
  },

  'lead-round-robin': {
    id: 'lead-round-robin',
    title: 'Lead Round-Robin Assignment & Scoring',
    subtitle: 'Evaluates inbound leads with AI score propensity and routes to senior reps or nurture queue',
    entity: 'Leads',
    categoryType: 'Automation Rule',
    badgeColor: '#0284c7',
    nodes: [
      {
        id: 'node-1',
        type: 'processTrigger',
        position: { x: 80, y: 160 },
        data: {
          category: '1. TRIGGER ORIGIN',
          subtype: 'record_event',
          title: 'Inbound Lead Created',
          description: 'Triggered when web form or CSV import inserts a new lead',
          condition: 'event == lead.created'
        }
      },
      {
        id: 'node-2',
        type: 'processScreen',
        position: { x: 440, y: 160 },
        data: {
          category: '2. LEAD SCORING',
          subtype: 'formula',
          title: 'AI Propensity Evaluation',
          description: 'Computes propensity score from industry, company size, and budget',
          condition: 'score = AI.predict(lead)'
        }
      },
      {
        id: 'node-3',
        type: 'processDecision',
        position: { x: 840, y: 160 },
        data: {
          category: '3. ROUTING DECISION',
          subtype: 'condition',
          title: 'Enterprise Qualified?',
          description: 'Checks if score is high tier (>= 75)',
          condition: 'lead.score >= 75',
          color: '#d97706'
        }
      },
      {
        id: 'node-4',
        type: 'processAction',
        position: { x: 1220, y: 160 },
        data: {
          category: '4. ASSIGNMENT',
          subtype: 'update_field',
          title: 'Assign to Senior AE',
          description: 'Allocates lead via round-robin to Senior Account Executive team',
          condition: "assigned_to -> 'Senior AE' | send welcome email",
          color: '#059669',
          isTerminal: true
        }
      },
      {
        id: 'node-5',
        type: 'processAction',
        position: { x: 840, y: 390 },
        data: {
          category: '5. NURTURE QUEUE',
          subtype: 'update_field',
          title: 'Assign to SMB Nurture',
          description: 'Enrolls lead into automated 5-day email drip campaign',
          condition: "assigned_to -> 'Marketing Nurture'",
          color: '#2563eb',
          icon: 'Mail',
          targetPosition: 'top',
          isTerminal: true
        }
      }
    ],
    edges: [
      {
        id: 'edge-1-2',
        source: 'node-1',
        target: 'node-2',
        type: 'smoothstep',
        animated: true,
        style: { stroke: '#0284c7', strokeWidth: 2.2 },
        markerEnd: { type: MarkerType.ArrowClosed, color: '#0284c7' },
        label: 'Lead Ingested'
      },
      {
        id: 'edge-2-3',
        source: 'node-2',
        target: 'node-3',
        type: 'smoothstep',
        animated: true,
        style: { stroke: '#0284c7', strokeWidth: 2.2 },
        markerEnd: { type: MarkerType.ArrowClosed, color: '#0284c7' },
        label: 'Score Generated'
      },
      {
        id: 'edge-3-4',
        source: 'node-3',
        sourceHandle: 'yes',
        target: 'node-4',
        type: 'smoothstep',
        animated: true,
        style: { stroke: '#059669', strokeWidth: 2.2 },
        markerEnd: { type: MarkerType.ArrowClosed, color: '#059669' },
        label: 'Score >= 75 (High Priority)'
      },
      {
        id: 'edge-3-5',
        source: 'node-3',
        sourceHandle: 'no',
        target: 'node-5',
        type: 'smoothstep',
        animated: false,
        style: { stroke: '#2563eb', strokeWidth: 2.2 },
        markerEnd: { type: MarkerType.ArrowClosed, color: '#2563eb' },
        label: 'Score < 75 (Standard)'
      }
    ]
  },

  'wire-webhook': {
    id: 'wire-webhook',
    title: 'Banking Wire API Webhook Ingestion',
    subtitle: 'Real-time bank SWIFT/UTR ingestion and matching against pending open invoices',
    entity: 'Wires',
    categoryType: 'Automation Rule',
    badgeColor: '#059669',
    nodes: [
      {
        id: 'node-1',
        type: 'processTrigger',
        position: { x: 80, y: 160 },
        data: {
          category: '1. INBOUND API',
          subtype: 'webhook',
          title: 'Bank Webhook Received',
          description: 'REST API payload received from J.P. Morgan Chase / Citi',
          condition: 'POST /api/wires/webhook'
        }
      },
      {
        id: 'node-2',
        type: 'processDecision',
        position: { x: 440, y: 160 },
        data: {
          category: '2. RECONCILIATION',
          subtype: 'condition',
          title: 'Match UTR & Reference',
          description: 'Finds matching invoice or account ID in CRM database',
          condition: 'invoice_id.is_valid()',
          color: '#d97706'
        }
      },
      {
        id: 'node-3',
        type: 'processAction',
        position: { x: 840, y: 160 },
        data: {
          category: '3. MATCH CONFIRMATION',
          subtype: 'update_field',
          title: 'Credit Invoice & Reconcile',
          description: 'Sets invoice status to Paid and wire status to Matched',
          condition: "invoice.status -> 'Paid' | wire.status -> 'Matched'",
          color: '#059669'
        }
      },
      {
        id: 'node-4',
        type: 'processAction',
        position: { x: 1220, y: 160 },
        data: {
          category: '4. TELEMETRY ALERT',
          subtype: 'show_toast',
          title: 'Finance Slack/Email Alert',
          description: 'Sends real-time notification to Accounts Receivable team',
          condition: 'Dispatch Slack & Email Notification',
          color: '#2563eb',
          icon: 'Mail',
          isTerminal: true
        }
      },
      {
        id: 'node-5',
        type: 'processAction',
        position: { x: 440, y: 390 },
        data: {
          category: '5. UNMATCHED HOLD',
          subtype: 'exception',
          title: 'Route to Unallocated Queue',
          description: 'Puts wire into Unallocated table for manual controller review',
          condition: "status -> 'Unallocated'",
          color: '#e11d48',
          icon: 'AlertCircle',
          targetPosition: 'top',
          isTerminal: true
        }
      }
    ],
    edges: [
      {
        id: 'edge-1-2',
        source: 'node-1',
        target: 'node-2',
        type: 'smoothstep',
        animated: true,
        style: { stroke: '#059669', strokeWidth: 2.2 },
        markerEnd: { type: MarkerType.ArrowClosed, color: '#059669' },
        label: 'Payload Verified'
      },
      {
        id: 'edge-2-3',
        source: 'node-2',
        sourceHandle: 'yes',
        target: 'node-3',
        type: 'smoothstep',
        animated: true,
        style: { stroke: '#059669', strokeWidth: 2.2 },
        markerEnd: { type: MarkerType.ArrowClosed, color: '#059669' },
        label: 'Invoice Match Found'
      },
      {
        id: 'edge-3-4',
        source: 'node-3',
        target: 'node-4',
        type: 'smoothstep',
        animated: true,
        style: { stroke: '#2563eb', strokeWidth: 2.2 },
        markerEnd: { type: MarkerType.ArrowClosed, color: '#2563eb' },
        label: 'Ledger Updated'
      },
      {
        id: 'edge-2-5',
        source: 'node-2',
        sourceHandle: 'no',
        target: 'node-5',
        type: 'smoothstep',
        animated: false,
        style: { stroke: '#e11d48', strokeWidth: 2.2 },
        markerEnd: { type: MarkerType.ArrowClosed, color: '#e11d48' },
        label: 'No Match (Unallocated)'
      }
    ]
  },

  'freight-alerter': {
    id: 'freight-alerter',
    title: 'Logistics Freight Milestone Alerter',
    subtitle: 'Dispatches tracking SMS & email when cargo departs and updates delivery estimates',
    entity: 'Shipping',
    categoryType: 'Automation Rule',
    badgeColor: '#d97706',
    nodes: [
      {
        id: 'node-1',
        type: 'processTrigger',
        position: { x: 80, y: 160 },
        data: {
          category: '1. TIMER OFFSET',
          subtype: 'schedule',
          title: 'Freight Departure Event',
          description: 'Triggered upon cargo vessel/flight departure milestone',
          condition: "shipping_status == 'Departed'"
        }
      },
      {
        id: 'node-2',
        type: 'processDecision',
        position: { x: 440, y: 160 },
        data: {
          category: '2. ROUTE CHECK',
          subtype: 'condition',
          title: 'Carrier Priority Tier',
          description: 'Checks if tracking notifications are enabled for recipient',
          condition: 'recipient_sms_optin == true',
          color: '#d97706'
        }
      },
      {
        id: 'node-3',
        type: 'processAction',
        position: { x: 840, y: 160 },
        data: {
          category: '3. SMS DISPATCH',
          subtype: 'send_email',
          title: 'Dispatch Live Tracking SMS',
          description: 'Sends real-time departure SMS with container tracking link',
          condition: 'SMS -> customer.phone | ETA +3d',
          color: '#059669',
          icon: 'Mail'
        }
      },
      {
        id: 'node-4',
        type: 'processAction',
        position: { x: 1220, y: 160 },
        data: {
          category: '4. ETA REVISION',
          subtype: 'update_field',
          title: 'Update Estimated Delivery',
          description: 'Recalculates delivery ETA according to actual ocean/air transit time',
          condition: 'ETA = departure_date + transit_days',
          color: '#7c3aed',
          isTerminal: true
        }
      }
    ],
    edges: [
      {
        id: 'edge-1-2',
        source: 'node-1',
        target: 'node-2',
        type: 'smoothstep',
        animated: true,
        style: { stroke: '#d97706', strokeWidth: 2.2 },
        markerEnd: { type: MarkerType.ArrowClosed, color: '#d97706' },
        label: 'Cargo Departed'
      },
      {
        id: 'edge-2-3',
        source: 'node-2',
        sourceHandle: 'yes',
        target: 'node-3',
        type: 'smoothstep',
        animated: true,
        style: { stroke: '#059669', strokeWidth: 2.2 },
        markerEnd: { type: MarkerType.ArrowClosed, color: '#059669' },
        label: 'SMS Opt-in Verified'
      },
      {
        id: 'edge-3-4',
        source: 'node-3',
        target: 'node-4',
        type: 'smoothstep',
        animated: true,
        style: { stroke: '#7c3aed', strokeWidth: 2.2 },
        markerEnd: { type: MarkerType.ArrowClosed, color: '#7c3aed' },
        label: 'SMS Dispatched'
      }
    ]
  }
};
