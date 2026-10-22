import React from 'react';
import { Handle, Position, useReactFlow } from '@xyflow/react';
import { Trash2, Mail, Lock } from 'lucide-react';

export default function ProcessActionNode({ id, data, selected }) {
  const { deleteElements } = useReactFlow();

  const handleDelete = (e) => {
    e.stopPropagation();
    deleteElements({ nodes: [{ id }] });
  };

  const isSelected = selected;
  const isLock =
    data.subtype === 'lock_record' ||
    data.title?.toLowerCase().includes('lock');
  const isToast =
    (data.subtype === 'show_toast' ||
    data.title?.toLowerCase().includes('toast')) &&
    !isLock;
  const isEmail =
    (data.subtype === 'send_email' ||
    data.title?.toLowerCase().includes('email')) &&
    !isLock &&
    !isToast;
  const isCreateRecord =
    (data.subtype === 'create_record' ||
    data.title?.toLowerCase().includes('create child') ||
    data.title?.toLowerCase().includes('create record')) &&
    !isLock &&
    !isToast &&
    !isEmail;
  const isUpdate =
    (data.subtype === 'update_field' || data.title?.toLowerCase().includes('update')) &&
    !isLock &&
    !isCreateRecord &&
    !isEmail &&
    !isToast;
  const isActionGreen = isUpdate || isCreateRecord || isEmail || isToast || isLock;
  const actionCategory = data.category || (isActionGreen ? 'BACKGROUND ACTION' : 'ACTION');
  const themeColor = data.color || (isActionGreen ? '#059669' : '#4f46e5');

  const targetFieldKey =
    data.target_field_key ||
    (data.target_field ? data.target_field.match(/\(([^)]+)\)/)?.[1] : 'stage') ||
    'stage';
  const targetVal = data.target_value !== undefined ? data.target_value : 'Approved';
  const displayCondition = data.condition || `Set ${targetFieldKey} = "${targetVal}"`;

  const targetModuleUpper = data.target_module
    ? (data.target_module.match(/\(([^)]+)\)/)?.[1] || 'tasks').toUpperCase()
    : 'TASKS';
  const priorityShort = data.priority ? data.priority.split(' ')[0] : 'High';
  const taskTitle = data.task_title !== undefined ? data.task_title : 'Follow-up Task: Process Action for {{name}}';

  const emailFieldKey = data.record_email_field ? (data.record_email_field.match(/\(([^)]+)\)/)?.[1] || 'email') : 'email';
  const emailSubject = data.subject !== undefined ? data.subject : 'Notification: Update regarding {{name}}';

  return (
    <div
      style={{
        minWidth: '260px',
        maxWidth: '280px',
        background: '#ffffff',
        borderRadius: '12px',
        border: isSelected ? '2px solid #818cf8' : '1px solid #cbd5e1',
        boxShadow: isSelected
          ? '0 0 0 3px rgba(99, 102, 241, 0.18), 0 8px 20px -4px rgba(0,0,0,0.08)'
          : '0 2px 8px rgba(0, 0, 0, 0.05)',
        position: 'relative',
        transition: 'border-color 0.15s ease, box-shadow 0.15s ease',
        cursor: 'grab'
      }}
      className="group"
    >
      {/* 4 Connection Handles on top, right, bottom, left matching screenshot */}
      <Handle
        type="target"
        position={Position.Top}
        id="top"
        isConnectable={true}
        style={{
          top: '-5px',
          left: '50%',
          transform: 'translateX(-50%)',
          width: '10px',
          height: '10px',
          background: '#ffffff',
          border: '2px solid #6366f1',
          borderRadius: '50%',
          cursor: 'crosshair',
          zIndex: 20
        }}
      />
      <Handle
        type="source"
        position={Position.Right}
        id="right"
        isConnectable={true}
        style={{
          right: '-5px',
          top: '50%',
          transform: 'translateY(-50%)',
          width: '10px',
          height: '10px',
          background: '#ffffff',
          border: '2px solid #6366f1',
          borderRadius: '50%',
          cursor: 'crosshair',
          zIndex: 20
        }}
      />
      <Handle
        type="source"
        position={Position.Bottom}
        id="bottom"
        isConnectable={true}
        style={{
          bottom: '-5px',
          left: '50%',
          transform: 'translateX(-50%)',
          width: '10px',
          height: '10px',
          background: '#ffffff',
          border: '2px solid #6366f1',
          borderRadius: '50%',
          cursor: 'crosshair',
          zIndex: 20
        }}
      />
      <Handle
        type="target"
        position={Position.Left}
        id="left"
        isConnectable={true}
        style={{
          left: '-5px',
          top: '50%',
          transform: 'translateY(-50%)',
          width: '10px',
          height: '10px',
          background: '#ffffff',
          border: '2px solid #6366f1',
          borderRadius: '50%',
          cursor: 'crosshair',
          zIndex: 20
        }}
      />

      {/* Selection corner indicators matching screenshot when selected */}
      {isSelected && (
        <>
          <span style={{ position: 'absolute', top: '-4px', left: '-4px', width: '7px', height: '7px', background: '#ffffff', border: '2px solid #6366f1', borderRadius: '50%', pointerEvents: 'none', zIndex: 11 }} />
          <span style={{ position: 'absolute', top: '-4px', right: '-4px', width: '7px', height: '7px', background: '#ffffff', border: '2px solid #6366f1', borderRadius: '50%', pointerEvents: 'none', zIndex: 11 }} />
          <span style={{ position: 'absolute', bottom: '-4px', left: '-4px', width: '7px', height: '7px', background: '#ffffff', border: '2px solid #6366f1', borderRadius: '50%', pointerEvents: 'none', zIndex: 11 }} />
          <span style={{ position: 'absolute', bottom: '-4px', right: '-4px', width: '7px', height: '7px', background: '#ffffff', border: '2px solid #6366f1', borderRadius: '50%', pointerEvents: 'none', zIndex: 11 }} />
        </>
      )}

      {/* Node Inner Container */}
      <div style={{ borderRadius: '10px', overflow: 'hidden' }}>
        {/* Node Header: [::: BACKGROUND ACTION] on left, [node-xxxx] on right */}
        <div
          style={{
            background: isActionGreen ? '#f0fdf4' : '#f8fafc',
            padding: '0.5rem 0.75rem',
            borderBottom: isActionGreen ? '1px solid #bbf7d0' : '1px solid #e2e8f0',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'space-between'
          }}
        >
          {/* Left: Grip dots + Category Title */}
          <div style={{ display: 'flex', alignItems: 'center', gap: '0.35rem' }}>
            <div style={{ display: 'flex', gap: '2px', alignItems: 'center' }}>
              <div style={{ display: 'flex', flexDirection: 'column', gap: '2px' }}>
                <span style={{ width: '2px', height: '2px', background: themeColor, borderRadius: '50%' }} />
                <span style={{ width: '2px', height: '2px', background: themeColor, borderRadius: '50%' }} />
                <span style={{ width: '2px', height: '2px', background: themeColor, borderRadius: '50%' }} />
              </div>
              <div style={{ display: 'flex', flexDirection: 'column', gap: '2px' }}>
                <span style={{ width: '2px', height: '2px', background: themeColor, borderRadius: '50%' }} />
                <span style={{ width: '2px', height: '2px', background: themeColor, borderRadius: '50%' }} />
                <span style={{ width: '2px', height: '2px', background: themeColor, borderRadius: '50%' }} />
              </div>
            </div>
            <span
              style={{
                fontSize: '0.675rem',
                fontWeight: 800,
                textTransform: 'uppercase',
                letterSpacing: '0.04em',
                color: themeColor
              }}
            >
              {actionCategory}
            </span>
          </div>

          {/* Right: Node ID (e.g. node-2276-7qj) */}
          <span
            style={{
              fontSize: '0.65rem',
              fontFamily: 'monospace',
              color: '#94a3b8',
              letterSpacing: '-0.02em'
            }}
          >
            {id || (isLock ? 'node-8037-9k8' : (isToast ? 'node-2276-7qj' : (isEmail ? 'node-1541-641' : (isCreateRecord ? 'node-0883-916' : 'node-4783-euc'))))}
          </span>
        </div>

        {/* Node Body */}
        <div style={{ padding: '0.75rem 0.85rem 0.6rem' }}>
          <h4
            style={{
              fontSize: '0.85rem',
              fontWeight: 700,
              color: '#1e293b',
              margin: '0 0 0.25rem 0',
              lineHeight: 1.3,
              minHeight: '1.1rem'
            }}
          >
            {data.title !== undefined
              ? data.title
              : (isLock
                  ? 'Lock / Unlock Record'
                  : (isToast
                      ? 'Pop-up Toast Alert'
                      : (isEmail ? 'Send Email Template' : (isCreateRecord ? 'Create Child Record / Task' : 'Update Record Field'))))}
          </h4>
          <p
            style={{
              fontSize: '0.725rem',
              color: '#64748b',
              margin: 0,
              lineHeight: 1.35,
              minHeight: '1rem',
              wordBreak: 'break-word',
              whiteSpace: 'pre-wrap'
            }}
          >
            {data.description !== undefined
              ? data.description
              : (isLock
                  ? 'Enforces admin-only edit lock on the record'
                  : (isToast
                      ? 'Shows confirmation, warning, or success toast'
                      : (isEmail
                          ? 'Dispatches templated email notification'
                          : (isCreateRecord ? 'Inserts new Task, Invoice, or related record' : 'Silently changes field value (e.g. stage, status)'))))}
          </p>

          {/* Lock / Unlock Record badge card matching screenshot */}
          {isLock && (
            <div
              style={{
                marginTop: '0.6rem',
                padding: '0.45rem 0.6rem',
                background: '#fffbeb',
                border: '1px solid #fde68a',
                borderRadius: '6px',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'space-between',
                gap: '0.5rem'
              }}
            >
              <div
                style={{
                  display: 'flex',
                  alignItems: 'center',
                  gap: '6px',
                  minWidth: 0
                }}
              >
                <Lock size={13} style={{ color: '#d97706', flexShrink: 0 }} />
                <span
                  style={{
                    fontSize: '0.7rem',
                    fontWeight: 700,
                    color: '#b45309',
                    whiteSpace: 'nowrap',
                    overflow: 'hidden',
                    textOverflow: 'ellipsis'
                  }}
                >
                  {data.lock_action_label || (data.lock_action?.includes('Release') ? 'Release Edit Lock' : 'Enforce Edit Lock')}
                </span>
              </div>
              <span
                style={{
                  fontSize: '0.625rem',
                  fontWeight: 700,
                  padding: '2px 8px',
                  borderRadius: '9999px',
                  background: '#ffedd5',
                  color: '#c2410c',
                  whiteSpace: 'nowrap',
                  flexShrink: 0
                }}
              >
                {data.bypass_authority_badge || (data.bypass_authority?.includes('Record Owner') ? 'Owner & Admins' : (data.bypass_authority?.includes('Nobody') ? 'Nobody' : 'Admin Only'))}
              </span>
            </div>
          )}

          {/* Send Email Template badge card matching screenshot */}
          {isEmail && (
            <div
              style={{
                marginTop: '0.6rem',
                padding: '0.45rem 0.6rem',
                background: '#f0fdf4',
                border: '1px solid #bbf7d0',
                borderRadius: '6px',
                display: 'flex',
                flexDirection: 'column',
                gap: '0.25rem'
              }}
            >
              <div
                style={{
                  fontSize: '0.7rem',
                  fontWeight: 700,
                  color: '#059669',
                  display: 'flex',
                  alignItems: 'center',
                  gap: '5px'
                }}
              >
                <Mail size={12} style={{ color: '#059669' }} />
                <span>To: Record [{emailFieldKey}]</span>
              </div>
              <div
                style={{
                  fontSize: '0.6875rem',
                  color: '#475569',
                  overflow: 'hidden',
                  textOverflow: 'ellipsis',
                  whiteSpace: 'nowrap'
                }}
                title={`Subj: ${emailSubject}`}
              >
                <span style={{ fontWeight: 600, color: '#334155' }}>Subj: </span>
                <span>{emailSubject}</span>
              </div>
            </div>
          )}

          {/* Create Child Record badge card */}
          {isCreateRecord && (
            <div
              style={{
                marginTop: '0.6rem',
                padding: '0.45rem 0.6rem',
                background: '#f0fdf4',
                border: '1px solid #bbf7d0',
                borderRadius: '6px',
                display: 'flex',
                flexDirection: 'column',
                gap: '0.25rem'
              }}
            >
              <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
                <span
                  style={{
                    fontSize: '0.7rem',
                    fontWeight: 700,
                    color: '#059669',
                    display: 'flex',
                    alignItems: 'center',
                    gap: '4px'
                  }}
                >
                  <span style={{ fontSize: '0.8rem', lineHeight: 1 }}>+</span> Insert {targetModuleUpper}
                </span>
                <span
                  style={{
                    fontSize: '0.625rem',
                    fontWeight: 700,
                    padding: '1px 7px',
                    borderRadius: '9999px',
                    background: '#dcfce7',
                    color: '#15803d'
                  }}
                >
                  {priorityShort}
                </span>
              </div>
              <div
                style={{
                  fontSize: '0.6875rem',
                  color: '#475569',
                  overflow: 'hidden',
                  textOverflow: 'ellipsis',
                  whiteSpace: 'nowrap'
                }}
                title={taskTitle}
              >
                "{taskTitle}"
              </div>
            </div>
          )}

          {/* Green Condition Line matching screenshot: Set stage = "Approved" */}
          {!isLock && !isCreateRecord && !isEmail && !isToast && displayCondition && (
            <div
              style={{
                marginTop: '0.55rem',
                fontSize: '0.725rem',
                color: '#059669',
                fontWeight: 700,
                fontFamily: 'monospace'
              }}
            >
              {displayCondition}
            </div>
          )}
        </div>

        {/* Node Footer: [subtype] on left, [trash icon] on right */}
        <div
          style={{
            padding: '0.4rem 0.85rem 0.6rem',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'space-between',
            background: '#ffffff'
          }}
        >
          <span
            style={{
              fontSize: '0.6875rem',
              fontFamily: 'monospace',
              color: '#94a3b8'
            }}
          >
            {data.subtype || (isLock ? 'lock_record' : (isToast ? 'show_toast' : (isEmail ? 'send_email' : (isCreateRecord ? 'create_record' : 'update_field'))))}
          </span>

          <button
            type="button"
            onClick={handleDelete}
            style={{
              background: 'transparent',
              border: 'none',
              padding: '2px',
              cursor: 'pointer',
              color: '#94a3b8',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              borderRadius: '4px',
              transition: 'color 0.15s ease'
            }}
            className="hover:text-red-500"
            title="Delete this block"
          >
            <Trash2 size={13} />
          </button>
        </div>
      </div>
    </div>
  );
}
