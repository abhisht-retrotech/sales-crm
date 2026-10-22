import React from 'react';
import {
  MousePointerClick,
  Zap,
  Calendar,
  Globe,
  Table,
  Layout,
  CheckSquare,
  GitBranch,
  Calculator,
  Edit3,
  PlusSquare,
  Mail,
  Bell,
  Lock,
  GripVertical,
  PanelLeftClose,
  PanelLeftOpen
} from 'lucide-react';

const CATEGORIES = [
  {
    id: 'triggers',
    title: '1. TRIGGERS (START)',
    icon: '⚡',
    color: '#4f46e5',
    items: [
      {
        type: 'trigger',
        subtype: 'button_click',
        title: 'Custom Button Click',
        description: 'Starts when user clicks button on record header',
        category: 'TRIGGER',
        button_label: 'Execute Process',
        condition: "button_click == 'Execute Process'",
        color: '#4f46e5',
        themeBg: '#f5f7ff',
        themeBorder: '#c7d2fe',
        themeText: '#3730a3',
        icon: MousePointerClick
      },
      {
        type: 'trigger',
        subtype: 'record_event',
        title: 'Record Event (Create/Update)',
        description: 'Starts automatically on record creation or field edit',
        category: 'TRIGGER',
        condition: "stage == 'Closed Won'",
        event_type: 'A record is created or updated',
        entity: 'Opportunities',
        color: '#4f46e5',
        themeBg: '#f5f7ff',
        themeBorder: '#c7d2fe',
        themeText: '#3730a3',
        icon: Zap
      },
      {
        type: 'trigger',
        subtype: 'schedule',
        title: 'Scheduled Timer Offset',
        description: 'Starts automatically relative to a date field',
        category: 'TRIGGER',
        condition: 'delay = +24 hours',
        color: '#4f46e5',
        themeBg: '#f5f7ff',
        themeBorder: '#c7d2fe',
        themeText: '#3730a3',
        icon: Calendar
      },
      {
        type: 'trigger',
        subtype: 'webhook',
        title: 'Inbound Webhook API',
        description: 'Starts upon external REST webhook payload',
        category: 'TRIGGER',
        condition: 'POST /api/erp/webhook',
        color: '#4f46e5',
        themeBg: '#f5f7ff',
        themeBorder: '#c7d2fe',
        themeText: '#3730a3',
        icon: Globe
      }
    ]
  },
  {
    id: 'screens',
    title: '2. INTERACTIVE SCREENS',
    icon: '🗂️',
    color: '#0284c7',
    items: [
      {
        type: 'screen',
        subtype: 'data_table',
        title: 'Dynamic Grid Table',
        description: 'Interactive table with add rows & auto calculations',
        category: 'INTERACTIVE SCREEN',
        condition: 'Dynamic Grid (Add Rows Enabled)',
        color: '#0284c7',
        themeBg: '#f0f9ff',
        themeBorder: '#bae6fd',
        themeText: '#0369a1',
        icon: Table
      },
      {
        type: 'screen',
        subtype: 'modal_form',
        title: 'Interactive Form Dialog',
        description: 'Presents guided input fields and prompts to user',
        category: 'INTERACTIVE SCREEN',
        condition: 'Input Form (4 Fields)',
        color: '#0284c7',
        themeBg: '#f0f9ff',
        themeBorder: '#bae6fd',
        themeText: '#0369a1',
        icon: Layout
      },
      {
        type: 'screen',
        subtype: 'approval_prompt',
        title: 'Approval / Decision Prompt',
        description: 'Prompts user to approve/reject with comments',
        category: 'INTERACTIVE SCREEN',
        condition: 'Requires Sign-off & Comments',
        color: '#0284c7',
        themeBg: '#f0f9ff',
        themeBorder: '#bae6fd',
        themeText: '#0369a1',
        icon: CheckSquare
      }
    ]
  },
  {
    id: 'decisions',
    title: '3. LOGIC & DECISIONS',
    icon: '🔀',
    color: '#d97706',
    items: [
      {
        type: 'decision',
        subtype: 'condition',
        title: 'If / Else Branch Condition',
        description: 'Splits flow execution based on field or math rules',
        category: 'DECISION LOGIC',
        condition: 'total_weight > 5000',
        color: '#d97706',
        themeBg: '#fffbeb',
        themeBorder: '#fde68a',
        themeText: '#92400e',
        icon: GitBranch
      },
      {
        type: 'decision',
        subtype: 'formula',
        title: 'Formula & Math Variable',
        description: 'Calculates dynamic variables, sums, or discounts',
        category: 'DECISION LOGIC',
        condition: 'gross_margin = (opp - po) / opp',
        color: '#d97706',
        themeBg: '#fffbeb',
        themeBorder: '#fde68a',
        themeText: '#92400e',
        icon: Calculator
      }
    ]
  },
  {
    id: 'actions',
    title: '4. BACKGROUND ACTIONS',
    icon: '⚙️',
    color: '#059669',
    items: [
      {
        type: 'action',
        subtype: 'update_field',
        title: 'Update Record Field',
        description: 'Silently changes field value (e.g. stage, status)',
        category: 'BACKGROUND ACTION',
        condition: 'Set stage = "Approved"',
        target_field: 'Stage (stage)',
        target_field_key: 'stage',
        target_value: 'Approved',
        mutate_fields: [{ field: 'Stage (stage)', key: 'stage', value: 'Approved' }],
        color: '#059669',
        themeBg: '#f0fdf4',
        themeBorder: '#bbf7d0',
        themeText: '#166534',
        icon: Edit3
      },
      {
        type: 'action',
        subtype: 'create_record',
        title: 'Create Child Record / Task',
        description: 'Inserts new Task, Invoice, or related record',
        category: 'BACKGROUND ACTION',
        target_module: 'Tasks & Action Items (tasks)',
        auto_link_parent: true,
        task_title: 'Follow-up Task: Process Action for {{name}}',
        task_guidance: 'Automated task created by Process Studio workflow.\nPlease review and take appropriate action.',
        priority: 'High Priority',
        due_date_offset: '+1 Business Day',
        assign_to: 'Assigned Owner of Current Record',
        condition: '+ Insert TASKS High',
        color: '#059669',
        themeBg: '#f0fdf4',
        themeBorder: '#bbf7d0',
        themeText: '#166534',
        icon: PlusSquare
      },
      {
        type: 'action',
        subtype: 'send_email',
        title: 'Send Email Template',
        description: 'Dispatches templated email notification',
        category: 'BACKGROUND ACTION',
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
        condition: '✉ To: Record [email]',
        color: '#059669',
        themeBg: '#f0fdf4',
        themeBorder: '#bbf7d0',
        themeText: '#166534',
        icon: Mail
      },
      {
        type: 'action',
        subtype: 'show_toast',
        title: 'Pop-up Toast Alert',
        description: 'Shows confirmation, warning, or success toast',
        category: 'BACKGROUND ACTION',
        toast_message: 'Business process step completed successfully.',
        toast_tone: 'Success (Green)',
        condition: 'Business process step completed successfully.',
        color: '#059669',
        themeBg: '#f0fdf4',
        themeBorder: '#bbf7d0',
        themeText: '#166534',
        icon: Bell
      },
      {
        type: 'action',
        subtype: 'lock_record',
        title: 'Lock / Unlock Record',
        description: 'Enforces admin-only edit lock on the record',
        category: 'BACKGROUND ACTION',
        condition: 'lock_record = true (Read-Only)',
        lock_action: '🔒 Enforce Edit Lock (Make Record Read-Only)',
        lock_action_label: 'Enforce Edit Lock',
        bypass_authority: 'System Administrators Only',
        bypass_authority_badge: 'Admin Only',
        strict_lock: true,
        notice_banner_style: 'Amber Warning ("Record Locked Pending Review")',
        notice_message: 'This record is locked pending executive/process review. Contact your system administrator to request changes.',
        color: '#059669',
        themeBg: '#f0fdf4',
        themeBorder: '#bbf7d0',
        themeText: '#166534',
        icon: Lock
      }
    ]
  }
];

export default function ComponentSidebar({ isOpen, onToggle, onAddNodeClick }) {
  const onDragStart = (event, item) => {
    event.dataTransfer.setData(
      'application/reactflow',
      JSON.stringify({
        type: item.type,
        subtype: item.subtype,
        title: item.title,
        category: item.category,
        description: item.description,
        button_label: item.button_label || 'Execute Process',
        event_type: item.event_type || 'A record is created or updated',
        entity: item.entity || 'Opportunities',
        condition: item.condition,
        lock_action: item.lock_action,
        lock_action_label: item.lock_action_label,
        bypass_authority: item.bypass_authority,
        bypass_authority_badge: item.bypass_authority_badge,
        strict_lock: item.strict_lock,
        notice_banner_style: item.notice_banner_style,
        notice_message: item.notice_message,
        color: item.color,
        icon: item.title.includes('Mail')
          ? 'Mail'
          : item.title.includes('Toast')
          ? 'Bell'
          : item.title.includes('Lock')
          ? 'Lock'
          : item.title.includes('Record')
          ? 'Edit3'
          : item.title.includes('Task')
          ? 'AlertCircle'
          : item.title.includes('Webhook')
          ? 'Globe'
          : 'Edit3'
      })
    );
    event.dataTransfer.effectAllowed = 'move';
  };

  if (!isOpen) {
    return (
      <div
        style={{
          position: 'absolute',
          top: '12px',
          left: '12px',
          zIndex: 30
        }}
      >
        <button
          type="button"
          onClick={onToggle}
          style={{
            background: '#ffffff',
            border: '1px solid #cbd5e1',
            borderRadius: '8px',
            padding: '0.45rem 0.65rem',
            cursor: 'pointer',
            boxShadow: '0 2px 8px rgba(0,0,0,0.08)',
            display: 'flex',
            alignItems: 'center',
            gap: '0.4rem',
            fontSize: '0.75rem',
            fontWeight: 700,
            color: '#334155'
          }}
          className="hover:bg-slate-50"
          title="Open Flow Building Blocks"
        >
          <PanelLeftOpen size={15} color="#4f46e5" />
          <span>Blocks</span>
        </button>
      </div>
    );
  }

  return (
    <aside
      style={{
        width: '270px',
        minWidth: '270px',
        maxWidth: '270px',
        height: '100%',
        background: '#ffffff',
        borderRight: '1px solid #e2e8f0',
        display: 'flex',
        flexDirection: 'column',
        zIndex: 25,
        overflow: 'hidden',
        userSelect: 'none'
      }}
    >
      {/* Top Sidebar Header */}
      <div
        style={{
          padding: '0.85rem 1rem',
          borderBottom: '1px solid #e2e8f0',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'space-between',
          background: '#ffffff'
        }}
      >
        <div>
          <div
            style={{
              fontSize: '0.75rem',
              fontWeight: 800,
              textTransform: 'uppercase',
              letterSpacing: '0.04em',
              color: '#1e293b'
            }}
          >
            FLOW BUILDING BLOCKS
          </div>
          <div style={{ fontSize: '0.675rem', color: '#64748b', marginTop: '2px' }}>
            Drag to canvas or click to add
          </div>
        </div>

        <button
          type="button"
          onClick={onToggle}
          style={{
            background: 'transparent',
            border: 'none',
            cursor: 'pointer',
            color: '#64748b',
            display: 'flex',
            alignItems: 'center',
            padding: '3px',
            borderRadius: '4px'
          }}
          className="hover:bg-slate-100 hover:text-slate-900"
          title="Collapse blocks palette"
        >
          <PanelLeftClose size={16} />
        </button>
      </div>

      {/* Scrollable Blocks List */}
      <div
        style={{
          flex: 1,
          overflowY: 'auto',
          padding: '0.85rem',
          display: 'flex',
          flexDirection: 'column',
          gap: '1.15rem'
        }}
      >
        {CATEGORIES.map((cat) => (
          <div key={cat.id}>
            {/* Category Header */}
            <div
              style={{
                fontSize: '0.6875rem',
                fontWeight: 800,
                letterSpacing: '0.03em',
                color: cat.color,
                marginBottom: '0.45rem',
                display: 'flex',
                alignItems: 'center',
                gap: '0.35rem'
              }}
            >
              <span>{cat.title}</span>
            </div>

            {/* Component Cards */}
            <div style={{ display: 'flex', flexDirection: 'column', gap: '0.4rem' }}>
              {cat.items.map((item, idx) => {
                const IconComp = item.icon;
                return (
                  <div
                    key={idx}
                    draggable
                    onDragStart={(e) => onDragStart(e, item)}
                    onClick={() => onAddNodeClick(item)}
                    style={{
                      background: item.themeBg,
                      border: `1px solid ${item.themeBorder}`,
                      borderRadius: '8px',
                      padding: '0.55rem 0.65rem',
                      display: 'flex',
                      alignItems: 'flex-start',
                      gap: '0.55rem',
                      cursor: 'grab',
                      transition: 'all 0.15s ease'
                    }}
                    className="hover:shadow-sm hover:scale-[1.01] active:cursor-grabbing active:scale-95 group"
                    title={`Drag "${item.title}" to canvas or click to add`}
                  >
                    {/* Left Icon */}
                    <div
                      style={{
                        color: item.themeText,
                        marginTop: '2px',
                        flexShrink: 0
                      }}
                    >
                      <IconComp size={15} strokeWidth={2.4} />
                    </div>

                    {/* Middle Text */}
                    <div style={{ flex: 1, minWidth: 0 }}>
                      <div
                        style={{
                          fontSize: '0.75rem',
                          fontWeight: 700,
                          color: item.themeText,
                          lineHeight: 1.25,
                          marginBottom: '2px'
                        }}
                      >
                        {item.title}
                      </div>
                      <div
                        style={{
                          fontSize: '0.65rem',
                          color: '#64748b',
                          lineHeight: 1.3
                        }}
                      >
                        {item.description}
                      </div>
                    </div>

                    {/* Right Drag Handle (6 dots) */}
                    <div
                      style={{
                        color: item.themeBorder,
                        flexShrink: 0,
                        marginTop: '2px'
                      }}
                      className="group-hover:text-slate-400"
                    >
                      <GripVertical size={13} />
                    </div>
                  </div>
                );
              })}
            </div>
          </div>
        ))}
      </div>
    </aside>
  );
}
