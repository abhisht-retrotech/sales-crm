import React, { useState, useCallback, useMemo, useEffect } from 'react';
import {
  ReactFlow,
  Background,
  Controls,
  MiniMap,
  Panel,
  Handle,
  Position,
  MarkerType,
  BackgroundVariant,
  useNodesState,
  useEdgesState,
  addEdge
} from '@xyflow/react';
import {
  Workflow,
  Plus,
  Play,
  Save,
  Trash2,
  CheckCircle2,
  AlertCircle,
  HelpCircle,
  MousePointerClick,
  Zap,
  Calendar,
  Globe,
  Layout,
  Table,
  CheckSquare,
  GitBranch,
  Calculator,
  Edit3,
  Layers,
  FileText,
  Mail,
  Bell,
  Lock,
  ArrowRight,
  Maximize2,
  Sparkles,
  ShieldCheck,
  ChevronRight,
  X,
  Sliders,
  Activity,
  RotateCcw,
  ChevronDown,
  Check,
  Clock,
  UserCheck,
  Shield,
  ExternalLink,
  RefreshCw,
  FolderTree
} from 'lucide-react';

// =========================================================================
// CUSTOM REACT FLOW NODE COMPONENTS
// =========================================================================

// 1. Process Trigger Node
const ProcessTriggerNode = ({ data, selected }) => {
  const isButtonClick = data.subtype === 'button_click';
  const isTimer = data.subtype === 'schedule';
  const isWebhook = data.subtype === 'webhook';

  const IconComp = isButtonClick
    ? MousePointerClick
    : isTimer
    ? Calendar
    : isWebhook
    ? Globe
    : Zap;

  return (
    <div
      style={{
        minWidth: '260px',
        maxWidth: '300px',
        background: '#ffffff',
        borderRadius: '14px',
        border: selected ? '2px solid #4f46e5' : '1px solid #cbd5e1',
        boxShadow: selected
          ? '0 0 0 4px rgba(99, 102, 241, 0.2), 0 10px 25px -5px rgba(0,0,0,0.1)'
          : '0 4px 14px rgba(0, 0, 0, 0.05)',
        overflow: 'hidden',
        transition: 'all 0.15s ease'
      }}
      className="group hover:border-indigo-500"
    >
      {/* Node Header */}
      <div
        style={{
          background: 'linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%)',
          padding: '0.65rem 0.85rem',
          borderBottom: '1px solid #c7d2fe',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'space-between'
        }}
      >
        <div style={{ display: 'flex', alignItems: 'center', gap: '0.45rem' }}>
          <div
            style={{
              width: '24px',
              height: '24px',
              borderRadius: '6px',
              background: '#4f46e5',
              color: '#ffffff',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              boxShadow: '0 1px 3px rgba(79, 70, 229, 0.3)'
            }}
          >
            <IconComp size={13} strokeWidth={2.4} />
          </div>
          <div>
            <div style={{ fontSize: '0.65rem', fontWeight: 800, textTransform: 'uppercase', letterSpacing: '0.05em', color: '#4338ca' }}>
              {data.category || 'TRIGGER ORIGIN'}
            </div>
          </div>
        </div>
        <span
          style={{
            fontSize: '0.625rem',
            fontWeight: 700,
            background: '#ffffff',
            color: '#4338ca',
            padding: '2px 6px',
            borderRadius: '9999px',
            border: '1px solid #c7d2fe'
          }}
        >
          {data.subtype || 'Trigger'}
        </span>
      </div>

      {/* Node Body */}
      <div style={{ padding: '0.75rem 0.85rem' }}>
        <h4 style={{ fontSize: '0.85rem', fontWeight: 700, color: '#0f172a', margin: '0 0 0.25rem 0' }}>
          {data.title || 'Workflow Trigger'}
        </h4>
        <p style={{ fontSize: '0.725rem', color: '#64748b', margin: '0 0 0.5rem 0', lineHeight: 1.35 }}>
          {data.description || 'Starts automatically on trigger event'}
        </p>

        {/* Rule Condition Pill */}
        {data.condition && (
          <div
            style={{
              display: 'inline-flex',
              alignItems: 'center',
              gap: '4px',
              background: '#f8fafc',
              border: '1px solid #e2e8f0',
              padding: '0.2rem 0.5rem',
              borderRadius: '6px',
              fontSize: '0.6875rem',
              fontFamily: 'monospace',
              color: '#334155',
              fontWeight: 600
            }}
          >
            <span style={{ color: '#6366f1' }}>Rule:</span> {data.condition}
          </div>
        )}
      </div>

      {/* Source Handle */}
      <Handle
        type="source"
        position={Position.Right}
        id="source"
        style={{
          width: '10px',
          height: '10px',
          background: '#4f46e5',
          border: '2px solid #ffffff',
          right: '-5px'
        }}
      />
    </div>
  );
};

// 2. Process Approval Step Node (Dedicated for Approval Workflows)
const ProcessApprovalStepNode = ({ data, selected }) => {
  return (
    <div
      style={{
        minWidth: '280px',
        maxWidth: '320px',
        background: '#ffffff',
        borderRadius: '14px',
        border: selected ? '2px solid #7c3aed' : '1px solid #cbd5e1',
        boxShadow: selected
          ? '0 0 0 4px rgba(124, 58, 237, 0.2), 0 10px 25px -5px rgba(0,0,0,0.1)'
          : '0 4px 14px rgba(0, 0, 0, 0.05)',
        overflow: 'hidden',
        position: 'relative',
        transition: 'all 0.15s ease'
      }}
      className="group hover:border-purple-500"
    >
      <Handle
        type="target"
        position={Position.Left}
        id="target"
        style={{
          width: '10px',
          height: '10px',
          background: '#7c3aed',
          border: '2px solid #ffffff',
          left: '-5px'
        }}
      />

      {/* Header */}
      <div
        style={{
          background: 'linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%)',
          padding: '0.65rem 0.85rem',
          borderBottom: '1px solid #ddd6fe',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'space-between'
        }}
      >
        <div style={{ display: 'flex', alignItems: 'center', gap: '0.45rem' }}>
          <div
            style={{
              width: '24px',
              height: '24px',
              borderRadius: '6px',
              background: '#7c3aed',
              color: '#ffffff',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              boxShadow: '0 1px 3px rgba(124, 58, 237, 0.3)'
            }}
          >
            <UserCheck size={13} strokeWidth={2.4} />
          </div>
          <div>
            <div style={{ fontSize: '0.65rem', fontWeight: 800, textTransform: 'uppercase', letterSpacing: '0.05em', color: '#6d28d9' }}>
              {data.stepBadge || 'APPROVAL STEP'}
            </div>
          </div>
        </div>

        {/* SLA Timeout Chip */}
        {data.sla && (
          <span
            style={{
              fontSize: '0.625rem',
              fontWeight: 700,
              background: '#ffffff',
              color: '#6d28d9',
              padding: '2px 6px',
              borderRadius: '9999px',
              border: '1px solid #ddd6fe',
              display: 'inline-flex',
              alignItems: 'center',
              gap: '3px'
            }}
          >
            <Clock size={10} />
            {data.sla}
          </span>
        )}
      </div>

      {/* Body */}
      <div style={{ padding: '0.75rem 0.85rem' }}>
        <h4 style={{ fontSize: '0.85rem', fontWeight: 700, color: '#0f172a', margin: '0 0 0.25rem 0' }}>
          {data.title || 'Tier Approver Review'}
        </h4>

        {/* Approver Role Pill */}
        <div
          style={{
            display: 'flex',
            alignItems: 'center',
            gap: '0.4rem',
            background: '#faf5ff',
            border: '1px solid #e9d5ff',
            padding: '0.35rem 0.55rem',
            borderRadius: '8px',
            marginTop: '0.35rem',
            marginBottom: '0.5rem'
          }}
        >
          <Shield size={12} color="#7c3aed" />
          <div style={{ fontSize: '0.725rem', color: '#581c87', fontWeight: 600 }}>
            <span style={{ color: '#9333ea', fontWeight: 700 }}>Approver: </span>
            {data.approver || 'Regional Sales VP'}
          </div>
        </div>

        {/* Condition Check */}
        {data.condition && (
          <div
            style={{
              fontSize: '0.6875rem',
              color: '#64748b',
              background: '#f8fafc',
              border: '1px solid #e2e8f0',
              padding: '0.2rem 0.45rem',
              borderRadius: '6px',
              fontFamily: 'monospace'
            }}
          >
            <span style={{ color: '#7c3aed', fontWeight: 700 }}>Criteria: </span>
            {data.condition}
          </div>
        )}
      </div>

      {/* Decision Branch Footer */}
      <div
        style={{
          borderTop: '1px dashed #e2e8f0',
          padding: '0.35rem 0.85rem',
          display: 'flex',
          justifyContent: 'space-between',
          fontSize: '0.65rem',
          fontWeight: 700,
          background: '#fafafa'
        }}
      >
        <span style={{ color: '#059669', display: 'flex', alignItems: 'center', gap: '2px' }}>
          <Check size={11} strokeWidth={2.6} /> Approved &rarr;
        </span>
        <span style={{ color: '#e11d48', display: 'flex', alignItems: 'center', gap: '2px' }}>
          <X size={11} strokeWidth={2.6} /> Rejected &darr;
        </span>
      </div>

      {/* Handles */}
      <Handle
        type="source"
        position={Position.Right}
        id="approved"
        style={{
          width: '10px',
          height: '10px',
          background: '#059669',
          border: '2px solid #ffffff',
          right: '-5px',
          top: '55%'
        }}
      />
      <Handle
        type="source"
        position={Position.Bottom}
        id="rejected"
        style={{
          width: '10px',
          height: '10px',
          background: '#e11d48',
          border: '2px solid #ffffff',
          bottom: '-5px',
          left: '50%'
        }}
      />
    </div>
  );
};

// 3. Process Decision Node
const ProcessDecisionNode = ({ data, selected }) => {
  return (
    <div
      style={{
        minWidth: '260px',
        maxWidth: '300px',
        background: '#ffffff',
        borderRadius: '14px',
        border: selected ? '2px solid #d97706' : '1px solid #cbd5e1',
        boxShadow: selected
          ? '0 0 0 4px rgba(217, 119, 6, 0.2), 0 10px 25px -5px rgba(0,0,0,0.1)'
          : '0 4px 14px rgba(0, 0, 0, 0.05)',
        overflow: 'hidden',
        position: 'relative',
        transition: 'all 0.15s ease'
      }}
      className="group hover:border-amber-500"
    >
      <Handle
        type="target"
        position={Position.Left}
        id="target"
        style={{
          width: '10px',
          height: '10px',
          background: '#d97706',
          border: '2px solid #ffffff',
          left: '-5px'
        }}
      />

      {/* Node Header */}
      <div
        style={{
          background: 'linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%)',
          padding: '0.65rem 0.85rem',
          borderBottom: '1px solid #fde68a',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'space-between'
        }}
      >
        <div style={{ display: 'flex', alignItems: 'center', gap: '0.45rem' }}>
          <div
            style={{
              width: '24px',
              height: '24px',
              borderRadius: '6px',
              background: '#d97706',
              color: '#ffffff',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              boxShadow: '0 1px 3px rgba(217, 119, 6, 0.3)'
            }}
          >
            <GitBranch size={13} strokeWidth={2.4} />
          </div>
          <div>
            <div style={{ fontSize: '0.65rem', fontWeight: 800, textTransform: 'uppercase', letterSpacing: '0.05em', color: '#b45309' }}>
              {data.category || 'DECISION LOGIC'}
            </div>
          </div>
        </div>
        <span
          style={{
            fontSize: '0.625rem',
            fontWeight: 700,
            background: '#ffffff',
            color: '#b45309',
            padding: '2px 6px',
            borderRadius: '9999px',
            border: '1px solid #fde68a'
          }}
        >
          Branch
        </span>
      </div>

      {/* Node Body */}
      <div style={{ padding: '0.75rem 0.85rem' }}>
        <h4 style={{ fontSize: '0.85rem', fontWeight: 700, color: '#0f172a', margin: '0 0 0.25rem 0' }}>
          {data.title || 'Condition Check'}
        </h4>
        <p style={{ fontSize: '0.725rem', color: '#64748b', margin: '0 0 0.5rem 0', lineHeight: 1.35 }}>
          {data.description || 'Evaluates condition before branching'}
        </p>

        {data.condition && (
          <div
            style={{
              display: 'inline-flex',
              alignItems: 'center',
              gap: '4px',
              background: '#fffbeb',
              border: '1px solid #fde68a',
              padding: '0.2rem 0.5rem',
              borderRadius: '6px',
              fontSize: '0.6875rem',
              fontFamily: 'monospace',
              color: '#92400e',
              fontWeight: 600
            }}
          >
            <span style={{ color: '#d97706' }}>Eval:</span> {data.condition}
          </div>
        )}
      </div>

      {/* Branch Indicators */}
      <div
        style={{
          borderTop: '1px dashed #e2e8f0',
          padding: '0.35rem 0.85rem',
          display: 'flex',
          justifyContent: 'space-between',
          fontSize: '0.65rem',
          fontWeight: 700,
          background: '#fafafa'
        }}
      >
        <span style={{ color: '#059669' }}>▶ Yes &rarr;</span>
        <span style={{ color: '#e11d48' }}>▼ No (Else)</span>
      </div>

      {/* Outgoing Handles */}
      <Handle
        type="source"
        position={Position.Right}
        id="yes"
        style={{
          width: '10px',
          height: '10px',
          background: '#059669',
          border: '2px solid #ffffff',
          right: '-5px',
          top: '55%'
        }}
      />
      <Handle
        type="source"
        position={Position.Bottom}
        id="no"
        style={{
          width: '10px',
          height: '10px',
          background: '#e11d48',
          border: '2px solid #ffffff',
          bottom: '-5px',
          left: '50%'
        }}
      />
    </div>
  );
};

// 4. Process Screen Node (Interactive Human Screen)
const ProcessScreenNode = ({ data, selected }) => {
  return (
    <div
      style={{
        minWidth: '270px',
        maxWidth: '310px',
        background: '#ffffff',
        borderRadius: '14px',
        border: selected ? '2px solid #0284c7' : '1px solid #cbd5e1',
        boxShadow: selected
          ? '0 0 0 4px rgba(2, 132, 199, 0.2), 0 10px 25px -5px rgba(0,0,0,0.1)'
          : '0 4px 14px rgba(0, 0, 0, 0.05)',
        overflow: 'hidden',
        position: 'relative',
        transition: 'all 0.15s ease'
      }}
      className="group hover:border-sky-500"
    >
      <Handle
        type="target"
        position={Position.Left}
        id="target"
        style={{
          width: '10px',
          height: '10px',
          background: '#0284c7',
          border: '2px solid #ffffff',
          left: '-5px'
        }}
      />

      <div
        style={{
          background: 'linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%)',
          padding: '0.65rem 0.85rem',
          borderBottom: '1px solid #bae6fd',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'space-between'
        }}
      >
        <div style={{ display: 'flex', alignItems: 'center', gap: '0.45rem' }}>
          <div
            style={{
              width: '24px',
              height: '24px',
              borderRadius: '6px',
              background: '#0284c7',
              color: '#ffffff',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              boxShadow: '0 1px 3px rgba(2, 132, 199, 0.3)'
            }}
          >
            <Table size={13} strokeWidth={2.4} />
          </div>
          <div>
            <div style={{ fontSize: '0.65rem', fontWeight: 800, textTransform: 'uppercase', letterSpacing: '0.05em', color: '#0369a1' }}>
              {data.category || 'HUMAN SCREEN'}
            </div>
          </div>
        </div>
        <span
          style={{
            fontSize: '0.625rem',
            fontWeight: 700,
            background: '#ffffff',
            color: '#0369a1',
            padding: '2px 6px',
            borderRadius: '9999px',
            border: '1px solid #bae6fd'
          }}
        >
          {data.subtype || 'Interactive'}
        </span>
      </div>

      <div style={{ padding: '0.75rem 0.85rem' }}>
        <h4 style={{ fontSize: '0.85rem', fontWeight: 700, color: '#0f172a', margin: '0 0 0.25rem 0' }}>
          {data.title || 'Interactive Screen'}
        </h4>
        <p style={{ fontSize: '0.725rem', color: '#64748b', margin: '0 0 0.5rem 0', lineHeight: 1.35 }}>
          {data.description || 'Prompts user input and calculations'}
        </p>

        {data.condition && (
          <div
            style={{
              display: 'inline-flex',
              alignItems: 'center',
              gap: '4px',
              background: '#f0f9ff',
              border: '1px solid #bae6fd',
              padding: '0.2rem 0.5rem',
              borderRadius: '6px',
              fontSize: '0.6875rem',
              fontFamily: 'monospace',
              color: '#0369a1',
              fontWeight: 600
            }}
          >
            <span style={{ color: '#0284c7' }}>Screen:</span> {data.condition}
          </div>
        )}
      </div>

      <Handle
        type="source"
        position={Position.Right}
        id="source"
        style={{
          width: '10px',
          height: '10px',
          background: '#0284c7',
          border: '2px solid #ffffff',
          right: '-5px'
        }}
      />
    </div>
  );
};

// 5. Process Action Node (Update, Lock, Email, Webhook, Exception)
const ProcessActionNode = ({ data, selected }) => {
  const isWebhook = data.subtype === 'webhook';
  const isLock = data.subtype === 'lock_record';
  const isEmail = data.subtype === 'send_email';
  const isException = data.subtype === 'exception' || data.color === '#ef4444' || data.color === '#e11d48';

  const theme = isException
    ? {
        color: '#e11d48',
        bgGradient: 'linear-gradient(135deg, #fff1f2 0%, #ffe4e6 100%)',
        borderLight: '#fecdd3',
        iconBg: '#e11d48',
        Icon: AlertCircle
      }
    : isWebhook
    ? {
        color: '#7c3aed',
        bgGradient: 'linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%)',
        borderLight: '#ddd6fe',
        iconBg: '#7c3aed',
        Icon: Globe
      }
    : isLock
    ? {
        color: '#6366f1',
        bgGradient: 'linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%)',
        borderLight: '#c7d2fe',
        iconBg: '#6366f1',
        Icon: Lock
      }
    : isEmail
    ? {
        color: '#2563eb',
        bgGradient: 'linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%)',
        borderLight: '#bfdbfe',
        iconBg: '#2563eb',
        Icon: Mail
      }
    : {
        color: '#059669',
        bgGradient: 'linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%)',
        borderLight: '#a7f3d0',
        iconBg: '#059669',
        Icon: Edit3
      };

  const IconComp = data.icon === 'Mail' ? Mail : data.icon === 'Globe' ? Globe : data.icon === 'Lock' ? Lock : theme.Icon;

  return (
    <div
      style={{
        minWidth: '260px',
        maxWidth: '300px',
        background: '#ffffff',
        borderRadius: '14px',
        border: selected ? `2px solid ${theme.color}` : '1px solid #cbd5e1',
        boxShadow: selected
          ? `0 0 0 4px ${theme.borderLight}, 0 10px 25px -5px rgba(0,0,0,0.1)`
          : '0 4px 14px rgba(0, 0, 0, 0.05)',
        overflow: 'hidden',
        transition: 'all 0.15s ease'
      }}
      className="group hover:border-emerald-500"
    >
      <Handle
        type="target"
        position={data.targetPosition === 'top' ? Position.Top : Position.Left}
        id="target"
        style={{
          width: '10px',
          height: '10px',
          background: theme.color,
          border: '2px solid #ffffff',
          ...(data.targetPosition === 'top' ? { top: '-5px', left: '50%' } : { left: '-5px' })
        }}
      />

      <div
        style={{
          background: theme.bgGradient,
          padding: '0.65rem 0.85rem',
          borderBottom: `1px solid ${theme.borderLight}`,
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'space-between'
        }}
      >
        <div style={{ display: 'flex', alignItems: 'center', gap: '0.45rem' }}>
          <div
            style={{
              width: '24px',
              height: '24px',
              borderRadius: '6px',
              background: theme.iconBg,
              color: '#ffffff',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              boxShadow: `0 1px 3px rgba(0,0,0,0.2)`
            }}
          >
            <IconComp size={13} strokeWidth={2.4} />
          </div>
          <div>
            <div style={{ fontSize: '0.65rem', fontWeight: 800, textTransform: 'uppercase', letterSpacing: '0.05em', color: theme.color }}>
              {data.category || 'RECORD ACTION'}
            </div>
          </div>
        </div>
        <span
          style={{
            fontSize: '0.625rem',
            fontWeight: 700,
            background: '#ffffff',
            color: theme.color,
            padding: '2px 6px',
            borderRadius: '9999px',
            border: `1px solid ${theme.borderLight}`
          }}
        >
          {data.subtype || 'Action'}
        </span>
      </div>

      <div style={{ padding: '0.75rem 0.85rem' }}>
        <h4 style={{ fontSize: '0.85rem', fontWeight: 700, color: '#0f172a', margin: '0 0 0.25rem 0' }}>
          {data.title || 'Action Execution'}
        </h4>
        <p style={{ fontSize: '0.725rem', color: '#64748b', margin: '0 0 0.5rem 0', lineHeight: 1.35 }}>
          {data.description || 'Executes background action logic'}
        </p>

        {data.condition && (
          <div
            style={{
              display: 'inline-flex',
              alignItems: 'center',
              gap: '4px',
              background: '#f8fafc',
              border: '1px solid #e2e8f0',
              padding: '0.2rem 0.5rem',
              borderRadius: '6px',
              fontSize: '0.6875rem',
              fontFamily: 'monospace',
              color: '#334155',
              fontWeight: 600
            }}
          >
            <span style={{ color: theme.color }}>Action:</span> {data.condition}
          </div>
        )}
      </div>

      {!data.isTerminal && (
        <Handle
          type="source"
          position={Position.Right}
          id="source"
          style={{
            width: '10px',
            height: '10px',
            background: theme.color,
            border: '2px solid #ffffff',
            right: '-5px'
          }}
        />
      )}
    </div>
  );
};

// =========================================================================
// PRESET WORKFLOWS REGISTRY (ALL APPROVAL & AUTOMATION WORKFLOWS)
// =========================================================================

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
  'opp-won-sync': {
    id: 'opp-won-sync',
    title: 'Opportunity Won → Procurement Sync',
    subtitle: 'Auto updates vendor Purchase Orders and syncs ERP webhook upon sales closing',
    entity: 'Opportunities',
    categoryType: 'Automation Rule',
    badgeColor: '#2563eb',
    nodes: [
      {
        id: 'node-1',
        type: 'processTrigger',
        position: { x: 80, y: 160 },
        data: {
          category: '1. EVENT TRIGGER',
          subtype: 'record_event',
          title: 'Opportunity Stage Change',
          description: 'Triggered when opportunity enters Closed Won state',
          condition: "stage == 'Closed Won'"
        }
      },
      {
        id: 'node-2',
        type: 'processDecision',
        position: { x: 440, y: 160 },
        data: {
          category: '2. DECISION LOGIC',
          subtype: 'condition',
          title: 'Check Linked PO',
          description: 'Evaluates procurement deal coupling',
          condition: 'purchase_deal_id is present',
          color: '#d97706'
        }
      },
      {
        id: 'node-3',
        type: 'processAction',
        position: { x: 840, y: 160 },
        data: {
          category: '3. RECORD UPDATE',
          subtype: 'update_field',
          title: 'Update Procurement Deal',
          description: 'PO stage mutated to Confirmed',
          condition: "PO.stage -> 'Confirmed'",
          color: '#059669',
          icon: 'Edit3'
        }
      },
      {
        id: 'node-4',
        type: 'processAction',
        position: { x: 1220, y: 160 },
        data: {
          category: '4. OUTBOUND API',
          subtype: 'webhook',
          title: 'ERP Webhook Sync',
          description: 'Dispatches payload to ERP queue',
          condition: 'POST /api/erp/dispatch',
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
          category: '5. EXCEPTION HANDLING',
          subtype: 'exception',
          title: 'Alert Deal Owner & Task',
          description: 'Dispatches task when PO is missing',
          condition: "Send 'PO Required' Alert",
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
        style: { stroke: '#6366f1', strokeWidth: 2.2 },
        markerEnd: { type: MarkerType.ArrowClosed, color: '#6366f1' },
        label: 'Event Fired'
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
        id: 'edge-3-4',
        source: 'node-3',
        target: 'node-4',
        type: 'smoothstep',
        animated: true,
        style: { stroke: '#7c3aed', strokeWidth: 2.2 },
        markerEnd: { type: MarkerType.ArrowClosed, color: '#7c3aed' },
        label: 'PO Updated'
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
        label: 'No (PO Missing)'
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
          condition: "Dispatch Slack & Email Notification",
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

// =========================================================================
// MAIN PROCESS STUDIO FLOW ENGINE COMPONENT
// =========================================================================

export default function ProcessStudioFlow({ initialFlowKey = '' }) {
  // Parse query parameter ?flow=...
  const getInitialFlowKey = () => {
    if (initialFlowKey && WORKFLOW_PRESETS[initialFlowKey]) {
      return initialFlowKey;
    }
    try {
      const params = new URLSearchParams(window.location.search);
      const flowParam = params.get('flow');
      if (flowParam && WORKFLOW_PRESETS[flowParam]) {
        return flowParam;
      }
      // If coming from approvals or contains approval keyword
      if (document.referrer && document.referrer.includes('approval')) {
        return 'mega-deal-discount';
      }
    } catch (e) {
      // fallback
    }
    return 'mega-deal-discount';
  };

  const [activePresetKey, setActivePresetKey] = useState(getInitialFlowKey);
  const activePreset = WORKFLOW_PRESETS[activePresetKey] || WORKFLOW_PRESETS['mega-deal-discount'];

  // Flow State
  const [nodes, setNodes, onNodesChange] = useNodesState(activePreset.nodes);
  const [edges, setEdges, onEdgesChange] = useEdgesState(activePreset.edges);

  // Sync when preset key changes
  const switchWorkflow = (key) => {
    if (!WORKFLOW_PRESETS[key]) return;
    setActivePresetKey(key);
    const preset = WORKFLOW_PRESETS[key];
    setNodes(preset.nodes);
    setEdges(preset.edges);
    setSelectedNode(null);
    setSimulationStep(0);
    setSimulationLogs([]);

    // Update browser URL query without full reload
    try {
      const url = new URL(window.location);
      url.searchParams.set('flow', key);
      window.history.pushState({}, '', url);
    } catch (e) {}
  };

  // Connect edges
  const onConnect = useCallback(
    (params) =>
      setEdges((eds) =>
        addEdge(
          {
            ...params,
            type: 'smoothstep',
            animated: true,
            style: { stroke: '#4f46e5', strokeWidth: 2.2 },
            markerEnd: { type: MarkerType.ArrowClosed, color: '#4f46e5' }
          },
          eds
        )
      ),
    [setEdges]
  );

  // Registered node types map
  const nodeTypes = useMemo(
    () => ({
      processTrigger: ProcessTriggerNode,
      processApprovalStep: ProcessApprovalStepNode,
      processDecision: ProcessDecisionNode,
      processScreen: ProcessScreenNode,
      processAction: ProcessActionNode
    }),
    []
  );

  // UI Interactive States
  const [selectedNode, setSelectedNode] = useState(null);
  const [isSimulatorOpen, setIsSimulatorOpen] = useState(false);
  const [isPaletteOpen, setIsPaletteOpen] = useState(false);
  const [isPresetDropdownOpen, setIsPresetDropdownOpen] = useState(false);
  const [saveToast, setSaveToast] = useState(false);

  // Flow Simulator Steps
  const [simulationStep, setSimulationStep] = useState(0);
  const [simulationLogs, setSimulationLogs] = useState([]);

  // Node selection handler
  const onNodeClick = useCallback((event, node) => {
    setSelectedNode(node);
  }, []);

  const onPaneClick = useCallback(() => {
    setSelectedNode(null);
  }, []);

  // Update node data from inspector
  const updateSelectedNodeData = (key, value) => {
    if (!selectedNode) return;
    setNodes((nds) =>
      nds.map((node) => {
        if (node.id === selectedNode.id) {
          const updated = {
            ...node,
            data: {
              ...node.data,
              [key]: value
            }
          };
          setSelectedNode(updated);
          return updated;
        }
        return node;
      })
    );
  };

  // Add Node from Palette
  const handleAddNode = (type, subtype, title, category, condition, color, stepBadge = '') => {
    const newId = `node-${Date.now()}`;
    const newNode = {
      id: newId,
      type: type === 'approvalStep' ? 'processApprovalStep' : type === 'trigger' ? 'processTrigger' : type === 'decision' ? 'processDecision' : type === 'screen' ? 'processScreen' : 'processAction',
      position: { x: 300 + Math.random() * 80, y: 220 + Math.random() * 80 },
      data: {
        category,
        subtype,
        title,
        description: `Custom node added on canvas`,
        condition,
        color,
        stepBadge: stepBadge || undefined
      }
    };
    setNodes((nds) => [...nds, newNode]);
    setIsPaletteOpen(false);
    setSelectedNode(newNode);
  };

  // Save changes feedback
  const handleSaveFlow = () => {
    setSaveToast(true);
    setTimeout(() => setSaveToast(false), 3000);
  };

  // Step-by-Step Simulation Logic
  const handleRunNextSimStep = () => {
    if (nodes.length === 0) return;
    const nextStep = (simulationStep + 1) % (nodes.length + 1);
    setSimulationStep(nextStep);

    if (nextStep === 0) {
      setSimulationLogs((prev) => [
        { time: new Date().toLocaleTimeString(), text: 'Simulation reset to idle state.' }
      ]);
    } else {
      const activeNode = nodes[nextStep - 1];
      const logMsg =
        activeNode.type === 'processApprovalStep'
          ? `[Step ${nextStep}] Approval Tier: ${activeNode.data.title} approved by ${activeNode.data.approver || 'Approver'} (SLA Verified).`
          : activeNode.type === 'processTrigger'
          ? `[Step ${nextStep}] Event triggered: "${activeNode.data.title}" evaluating (${activeNode.data.condition || 'valid'}).`
          : activeNode.type === 'processDecision'
          ? `[Step ${nextStep}] Decision evaluation passed: "${activeNode.data.condition || 'Condition true'}" -> Branch YES taken.`
          : activeNode.type === 'processScreen'
          ? `[Step ${nextStep}] Interactive Screen rendered: "${activeNode.data.title}" submitted by user.`
          : `[Step ${nextStep}] Action Executed: "${activeNode.data.title}" -> ${activeNode.data.condition || 'Applied'}.`;

      setSimulationLogs((prev) => [
        ...prev,
        { time: new Date().toLocaleTimeString(), text: logMsg, nodeTitle: activeNode.data.title }
      ]);
    }
  };

  // Determine back link URL
  const isApprovalCategory = activePreset.categoryType === 'Approval Process';
  const backUrl = isApprovalCategory ? '/admin/approval-rules' : '/admin/process-studio';
  const backLabel = isApprovalCategory ? 'Approval Workflows' : 'ProcessStudio Rules';

  return (
    <div
      style={{
        width: '100%',
        height: '100vh',
        display: 'flex',
        flexDirection: 'column',
        background: '#f8fafc',
        fontFamily: "'Inter', sans-serif",
        overflow: 'hidden'
      }}
    >
      {/* =========================================================================
          TOP COMMAND & WORKFLOW NAVIGATION HEADER
          ========================================================================= */}
      <header
        style={{
          background: '#ffffff',
          borderBottom: '1px solid #e2e8f0',
          padding: '0.65rem 1.25rem',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'space-between',
          zIndex: 40,
          boxShadow: '0 1px 3px rgba(0,0,0,0.04)'
        }}
      >
        {/* Left: Branding & Workflow Dropdown Selector */}
        <div style={{ display: 'flex', alignItems: 'center', gap: '0.85rem' }}>
          <a
            href={backUrl}
            style={{
              display: 'inline-flex',
              alignItems: 'center',
              gap: '0.4rem',
              color: '#475569',
              textDecoration: 'none',
              fontSize: '0.8rem',
              fontWeight: 600,
              padding: '0.35rem 0.65rem',
              borderRadius: '8px',
              border: '1px solid #e2e8f0',
              background: '#ffffff',
              transition: 'all 0.15s ease'
            }}
            className="hover:bg-slate-100 hover:text-slate-900"
            title={`Return to ${backLabel}`}
          >
            <ChevronRight size={14} style={{ transform: 'rotate(180deg)' }} />
            <span>Back to {backLabel}</span>
          </a>

          <div style={{ width: '1px', height: '22px', background: '#e2e8f0' }} />

          {/* Workflow Preset Switcher Dropdown */}
          <div style={{ position: 'relative' }}>
            <button
              type="button"
              onClick={() => setIsPresetDropdownOpen(!isPresetDropdownOpen)}
              style={{
                display: 'flex',
                alignItems: 'center',
                gap: '0.6rem',
                background: '#f8fafc',
                border: '1px solid #cbd5e1',
                padding: '0.4rem 0.85rem',
                borderRadius: '10px',
                cursor: 'pointer',
                textAlign: 'left'
              }}
              className="hover:border-indigo-400"
            >
              <div
                style={{
                  width: '24px',
                  height: '24px',
                  borderRadius: '6px',
                  background: activePreset.badgeColor || '#4f46e5',
                  color: '#fff',
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center'
                }}
              >
                <Workflow size={13} />
              </div>
              <div>
                <div style={{ display: 'flex', alignItems: 'center', gap: '0.4rem' }}>
                  <span style={{ fontWeight: 800, fontSize: '0.875rem', color: '#0f172a' }}>
                    {activePreset.title}
                  </span>
                  <span
                    style={{
                      fontSize: '0.625rem',
                      fontWeight: 700,
                      padding: '1px 6px',
                      borderRadius: '4px',
                      background: isApprovalCategory ? '#ede9fe' : '#e0f2fe',
                      color: isApprovalCategory ? '#6d28d9' : '#0369a1',
                      border: `1px solid ${isApprovalCategory ? '#ddd6fe' : '#bae6fd'}`
                    }}
                  >
                    {activePreset.categoryType}
                  </span>
                  <span
                    style={{
                      fontSize: '0.625rem',
                      fontWeight: 600,
                      padding: '1px 5px',
                      borderRadius: '4px',
                      background: '#f1f5f9',
                      color: '#475569'
                    }}
                  >
                    {activePreset.entity}
                  </span>
                </div>
                <div style={{ fontSize: '0.7rem', color: '#64748b', maxWidth: '420px', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                  {activePreset.subtitle}
                </div>
              </div>
              <ChevronDown size={14} color="#64748b" style={{ marginLeft: '0.5rem' }} />
            </button>

            {/* Dropdown Menu */}
            {isPresetDropdownOpen && (
              <div
                style={{
                  position: 'absolute',
                  top: '105%',
                  left: 0,
                  width: '380px',
                  background: '#ffffff',
                  borderRadius: '12px',
                  border: '1px solid #cbd5e1',
                  boxShadow: '0 12px 30px rgba(0,0,0,0.12)',
                  zIndex: 9999,
                  overflow: 'hidden'
                }}
              >
                <div style={{ padding: '0.65rem 0.85rem', background: '#f8fafc', borderBottom: '1px solid #e2e8f0', fontSize: '0.725rem', fontWeight: 700, color: '#475569', textTransform: 'uppercase' }}>
                  Select Process / Approval Workflow
                </div>

                <div style={{ maxHeight: '360px', overflowY: 'auto', padding: '0.4rem' }}>
                  <div style={{ fontSize: '0.65rem', fontWeight: 800, color: '#7c3aed', padding: '0.35rem 0.5rem', textTransform: 'uppercase' }}>
                    Approval Processes (Tier Sign-offs)
                  </div>
                  {['mega-deal-discount', 'procurement-po', 'wire-clearance', 'air-freight'].map((key) => {
                    const item = WORKFLOW_PRESETS[key];
                    const isSelected = activePresetKey === key;
                    return (
                      <div
                        key={key}
                        onClick={() => {
                          switchWorkflow(key);
                          setIsPresetDropdownOpen(false);
                        }}
                        style={{
                          padding: '0.5rem 0.75rem',
                          borderRadius: '8px',
                          cursor: 'pointer',
                          background: isSelected ? '#f5f3ff' : 'transparent',
                          border: isSelected ? '1px solid #ddd6fe' : '1px solid transparent',
                          marginBottom: '2px',
                          display: 'flex',
                          alignItems: 'center',
                          justifyContent: 'space-between'
                        }}
                        className="hover:bg-slate-50"
                      >
                        <div>
                          <div style={{ fontWeight: 700, fontSize: '0.8rem', color: isSelected ? '#6d28d9' : '#1e293b' }}>
                            {item.title}
                          </div>
                          <div style={{ fontSize: '0.68rem', color: '#64748b' }}>
                            {item.entity} • {item.nodes.length} Nodes
                          </div>
                        </div>
                        {isSelected && <Check size={14} color="#7c3aed" />}
                      </div>
                    );
                  })}

                  <div style={{ height: '1px', background: '#e2e8f0', margin: '0.4rem 0' }} />

                  <div style={{ fontSize: '0.65rem', fontWeight: 800, color: '#0284c7', padding: '0.35rem 0.5rem', textTransform: 'uppercase' }}>
                    Process Automation Rules
                  </div>
                  {['opp-won-sync', 'lead-round-robin', 'wire-webhook', 'freight-alerter'].map((key) => {
                    const item = WORKFLOW_PRESETS[key];
                    const isSelected = activePresetKey === key;
                    return (
                      <div
                        key={key}
                        onClick={() => {
                          switchWorkflow(key);
                          setIsPresetDropdownOpen(false);
                        }}
                        style={{
                          padding: '0.5rem 0.75rem',
                          borderRadius: '8px',
                          cursor: 'pointer',
                          background: isSelected ? '#f0f9ff' : 'transparent',
                          border: isSelected ? '1px solid #bae6fd' : '1px solid transparent',
                          marginBottom: '2px',
                          display: 'flex',
                          alignItems: 'center',
                          justifyContent: 'space-between'
                        }}
                        className="hover:bg-slate-50"
                      >
                        <div>
                          <div style={{ fontWeight: 700, fontSize: '0.8rem', color: isSelected ? '#0369a1' : '#1e293b' }}>
                            {item.title}
                          </div>
                          <div style={{ fontSize: '0.68rem', color: '#64748b' }}>
                            {item.entity} • {item.nodes.length} Nodes
                          </div>
                        </div>
                        {isSelected && <Check size={14} color="#0284c7" />}
                      </div>
                    );
                  })}
                </div>
              </div>
            )}
          </div>
        </div>

        {/* Right: Actions (Add Node, Simulator, Save) */}
        <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
          {/* Quick Palette Button */}
          <button
            type="button"
            onClick={() => setIsPaletteOpen(!isPaletteOpen)}
            style={{
              display: 'inline-flex',
              alignItems: 'center',
              gap: '0.35rem',
              padding: '0.4rem 0.75rem',
              borderRadius: '8px',
              fontSize: '0.78rem',
              fontWeight: 600,
              background: '#f1f5f9',
              color: '#334155',
              border: '1px solid #cbd5e1',
              cursor: 'pointer'
            }}
            className="hover:bg-slate-200"
          >
            <Plus size={14} />
            <span>+ Add Component</span>
          </button>

          {/* Flow Simulator Button */}
          <button
            type="button"
            onClick={() => setIsSimulatorOpen(!isSimulatorOpen)}
            style={{
              display: 'inline-flex',
              alignItems: 'center',
              gap: '0.35rem',
              padding: '0.4rem 0.75rem',
              borderRadius: '8px',
              fontSize: '0.78rem',
              fontWeight: 600,
              background: isSimulatorOpen ? '#fef3c7' : '#ffffff',
              color: isSimulatorOpen ? '#92400e' : '#475569',
              border: isSimulatorOpen ? '1px solid #fde68a' : '1px solid #cbd5e1',
              cursor: 'pointer'
            }}
            className="hover:bg-amber-50"
          >
            <Play size={13} fill={isSimulatorOpen ? '#92400e' : 'transparent'} />
            <span>Step Simulator</span>
          </button>

          {/* Reset Flow */}
          <button
            type="button"
            onClick={() => switchWorkflow(activePresetKey)}
            style={{
              padding: '0.4rem 0.6rem',
              borderRadius: '8px',
              fontSize: '0.78rem',
              background: '#ffffff',
              color: '#475569',
              border: '1px solid #cbd5e1',
              cursor: 'pointer'
            }}
            title="Reset to default preset layout"
          >
            <RotateCcw size={13} />
          </button>

          {/* Save Button */}
          <button
            type="button"
            onClick={handleSaveFlow}
            style={{
              display: 'inline-flex',
              alignItems: 'center',
              gap: '0.4rem',
              padding: '0.4rem 0.9rem',
              borderRadius: '8px',
              fontSize: '0.78rem',
              fontWeight: 700,
              background: '#4f46e5',
              color: '#ffffff',
              border: 'none',
              cursor: 'pointer',
              boxShadow: '0 1px 3px rgba(79, 70, 229, 0.4)'
            }}
            className="hover:bg-indigo-700"
          >
            <Save size={13} />
            <span>Save Flow</span>
          </button>
        </div>
      </header>

      {/* Save Toast Notification */}
      {saveToast && (
        <div
          style={{
            position: 'fixed',
            top: '70px',
            right: '25px',
            background: '#059669',
            color: '#ffffff',
            padding: '0.65rem 1rem',
            borderRadius: '10px',
            fontSize: '0.8rem',
            fontWeight: 600,
            display: 'flex',
            alignItems: 'center',
            gap: '0.5rem',
            boxShadow: '0 8px 24px rgba(0,0,0,0.15)',
            zIndex: 99999
          }}
        >
          <CheckCircle2 size={16} />
          <span>Workflow "{activePreset.title}" configuration saved successfully!</span>
        </div>
      )}

      {/* =========================================================================
          REACT FLOW CANVAS WRAPPER (FULL SCREEN DYNAMIC CANVAS)
          ========================================================================= */}
      <div style={{ flex: 1, position: 'relative', width: '100%', height: 'calc(100vh - 58px)' }}>
        <ReactFlow
          nodes={nodes}
          edges={edges}
          onNodesChange={onNodesChange}
          onEdgesChange={onEdgesChange}
          onConnect={onConnect}
          nodeTypes={nodeTypes}
          onNodeClick={onNodeClick}
          onPaneClick={onPaneClick}
          fitView
          fitViewOptions={{ padding: 0.35 }}
          minZoom={0.2}
          maxZoom={1.8}
        >
          {/* Background Grid Pattern */}
          <Background variant={BackgroundVariant.Dots} gap={20} size={1.2} color="#cbd5e1" />

          {/* Navigation Controls */}
          <Controls position="bottom-right" />

          {/* MiniMap */}
          <MiniMap
            position="bottom-left"
            nodeStrokeColor="#4f46e5"
            nodeColor="#e0e7ff"
            nodeBorderRadius={6}
            style={{
              borderRadius: '10px',
              overflow: 'hidden',
              boxShadow: '0 4px 14px rgba(0,0,0,0.08)',
              border: '1px solid #cbd5e1'
            }}
          />

          {/* Top Canvas Information Panel */}
          <Panel position="top-left">
            <div
              style={{
                background: 'rgba(255, 255, 255, 0.92)',
                backdropFilter: 'blur(8px)',
                padding: '0.65rem 0.95rem',
                borderRadius: '12px',
                border: '1px solid #e2e8f0',
                boxShadow: '0 4px 14px rgba(0,0,0,0.05)',
                maxWidth: '420px'
              }}
            >
              <div style={{ display: 'flex', alignItems: 'center', gap: '0.45rem', marginBottom: '0.2rem' }}>
                <span style={{ width: '8px', height: '8px', borderRadius: '50%', background: '#059669', display: 'inline-block' }} />
                <span style={{ fontSize: '0.78rem', fontWeight: 800, color: '#0f172a' }}>
                  {activePreset.title}
                </span>
                <span style={{ fontSize: '0.65rem', color: '#64748b' }}>({nodes.length} Nodes)</span>
              </div>
              <p style={{ margin: 0, fontSize: '0.72rem', color: '#475569', lineHeight: 1.35 }}>
                {activePreset.subtitle}
              </p>
            </div>
          </Panel>
        </ReactFlow>

        {/* =========================================================================
            SIMULATOR OVERLAY DRAWER
            ========================================================================= */}
        {isSimulatorOpen && (
          <div
            style={{
              position: 'absolute',
              top: '1rem',
              right: selectedNode ? '370px' : '1rem',
              width: '340px',
              background: 'rgba(255, 255, 255, 0.96)',
              backdropFilter: 'blur(10px)',
              borderRadius: '14px',
              border: '1px solid #cbd5e1',
              boxShadow: '0 12px 30px rgba(0,0,0,0.12)',
              padding: '1rem',
              zIndex: 50,
              transition: 'right 0.2s ease'
            }}
          >
            <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: '0.75rem', borderBottom: '1px solid #e2e8f0', paddingBottom: '0.5rem' }}>
              <div style={{ display: 'flex', alignItems: 'center', gap: '0.4rem' }}>
                <Activity size={15} color="#d97706" />
                <span style={{ fontWeight: 800, fontSize: '0.85rem', color: '#0f172a' }}>
                  Live Step Simulator
                </span>
              </div>
              <button
                type="button"
                onClick={() => setIsSimulatorOpen(false)}
                style={{ background: 'transparent', border: 'none', cursor: 'pointer', color: '#94a3b8' }}
              >
                <X size={14} />
              </button>
            </div>

            <div style={{ fontSize: '0.75rem', color: '#475569', marginBottom: '0.75rem' }}>
              Step through the workflow logic node-by-node to inspect criteria evaluation and decision branching:
            </div>

            <div style={{ display: 'flex', gap: '0.4rem', marginBottom: '0.75rem' }}>
              <button
                type="button"
                onClick={handleRunNextSimStep}
                style={{
                  flex: 1,
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                  gap: '0.35rem',
                  padding: '0.4rem',
                  borderRadius: '8px',
                  background: '#d97706',
                  color: '#ffffff',
                  border: 'none',
                  fontSize: '0.75rem',
                  fontWeight: 700,
                  cursor: 'pointer'
                }}
              >
                <Play size={12} fill="#ffffff" />
                <span>Next Step ({simulationStep}/{nodes.length})</span>
              </button>

              <button
                type="button"
                onClick={() => {
                  setSimulationStep(0);
                  setSimulationLogs([]);
                }}
                style={{
                  padding: '0.4rem 0.65rem',
                  borderRadius: '8px',
                  background: '#f1f5f9',
                  border: '1px solid #cbd5e1',
                  fontSize: '0.72rem',
                  cursor: 'pointer'
                }}
              >
                Reset
              </button>
            </div>

            {/* Simulation Log Stream */}
            <div
              style={{
                background: '#09090b',
                color: '#22c55e',
                borderRadius: '8px',
                padding: '0.65rem',
                fontSize: '0.6875rem',
                fontFamily: 'monospace',
                maxHeight: '160px',
                overflowY: 'auto'
              }}
            >
              {simulationLogs.length === 0 ? (
                <div style={{ color: '#71717a' }}>Press "Next Step" to trace execution telemetry...</div>
              ) : (
                simulationLogs.map((log, idx) => (
                  <div key={idx} style={{ marginBottom: '4px' }}>
                    <span style={{ color: '#60a5fa' }}>[{log.time}]</span> {log.text}
                  </div>
                ))
              )}
            </div>
          </div>
        )}

        {/* =========================================================================
            NODE INSPECTOR DRAWER (RIGHT PANEL)
            ========================================================================= */}
        {selectedNode && (
          <div
            style={{
              position: 'absolute',
              top: '1rem',
              right: '1rem',
              width: '340px',
              background: '#ffffff',
              borderRadius: '16px',
              border: '1px solid #cbd5e1',
              boxShadow: '0 14px 35px rgba(0,0,0,0.15)',
              padding: '1.25rem',
              zIndex: 50,
              maxHeight: 'calc(100vh - 90px)',
              overflowY: 'auto'
            }}
          >
            <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: '1rem', borderBottom: '1px solid #e2e8f0', paddingBottom: '0.65rem' }}>
              <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
                <Sliders size={16} color="#4f46e5" />
                <h3 style={{ margin: 0, fontSize: '0.95rem', fontWeight: 800, color: '#0f172a' }}>
                  Node Inspector
                </h3>
              </div>
              <button
                type="button"
                onClick={() => setSelectedNode(null)}
                style={{ background: 'transparent', border: 'none', cursor: 'pointer', color: '#94a3b8' }}
              >
                <X size={16} />
              </button>
            </div>

            <div style={{ display: 'flex', flexDirection: 'column', gap: '0.85rem' }}>
              <div>
                <label style={{ display: 'block', fontSize: '0.725rem', fontWeight: 700, color: '#475569', marginBottom: '0.25rem' }}>
                  NODE TITLE
                </label>
                <input
                  type="text"
                  value={selectedNode.data.title || ''}
                  onChange={(e) => updateSelectedNodeData('title', e.target.value)}
                  style={{
                    width: '100%',
                    padding: '0.45rem 0.65rem',
                    borderRadius: '8px',
                    border: '1px solid #cbd5e1',
                    fontSize: '0.8125rem'
                  }}
                />
              </div>

              <div>
                <label style={{ display: 'block', fontSize: '0.725rem', fontWeight: 700, color: '#475569', marginBottom: '0.25rem' }}>
                  DESCRIPTION
                </label>
                <textarea
                  rows={2}
                  value={selectedNode.data.description || ''}
                  onChange={(e) => updateSelectedNodeData('description', e.target.value)}
                  style={{
                    width: '100%',
                    padding: '0.45rem 0.65rem',
                    borderRadius: '8px',
                    border: '1px solid #cbd5e1',
                    fontSize: '0.75rem',
                    resize: 'none'
                  }}
                />
              </div>

              {/* Approver Role if Step */}
              {selectedNode.type === 'processApprovalStep' && (
                <div>
                  <label style={{ display: 'block', fontSize: '0.725rem', fontWeight: 700, color: '#475569', marginBottom: '0.25rem' }}>
                    APPROVER ASSIGNMENT
                  </label>
                  <input
                    type="text"
                    value={selectedNode.data.approver || ''}
                    onChange={(e) => updateSelectedNodeData('approver', e.target.value)}
                    style={{
                      width: '100%',
                      padding: '0.45rem 0.65rem',
                      borderRadius: '8px',
                      border: '1px solid #cbd5e1',
                      fontSize: '0.8125rem'
                    }}
                  />
                </div>
              )}

              {/* SLA Hours if Step */}
              {selectedNode.type === 'processApprovalStep' && (
                <div>
                  <label style={{ display: 'block', fontSize: '0.725rem', fontWeight: 700, color: '#475569', marginBottom: '0.25rem' }}>
                    SLA AUTO-EXPIRY
                  </label>
                  <input
                    type="text"
                    value={selectedNode.data.sla || ''}
                    onChange={(e) => updateSelectedNodeData('sla', e.target.value)}
                    style={{
                      width: '100%',
                      padding: '0.45rem 0.65rem',
                      borderRadius: '8px',
                      border: '1px solid #cbd5e1',
                      fontSize: '0.8125rem'
                    }}
                  />
                </div>
              )}

              <div>
                <label style={{ display: 'block', fontSize: '0.725rem', fontWeight: 700, color: '#475569', marginBottom: '0.25rem' }}>
                  CRITERIA / LOGIC FORMULA
                </label>
                <input
                  type="text"
                  value={selectedNode.data.condition || ''}
                  onChange={(e) => updateSelectedNodeData('condition', e.target.value)}
                  style={{
                    width: '100%',
                    padding: '0.45rem 0.65rem',
                    borderRadius: '8px',
                    border: '1px solid #cbd5e1',
                    fontSize: '0.75rem',
                    fontFamily: 'monospace',
                    background: '#f8fafc'
                  }}
                />
              </div>

              <div style={{ marginTop: '0.5rem', display: 'flex', gap: '0.5rem' }}>
                <button
                  type="button"
                  onClick={() => {
                    setNodes((nds) => nds.filter((n) => n.id !== selectedNode.id));
                    setSelectedNode(null);
                  }}
                  style={{
                    flex: 1,
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    gap: '0.35rem',
                    padding: '0.45rem',
                    borderRadius: '8px',
                    background: '#fef2f2',
                    color: '#dc2626',
                    border: '1px solid #fecdd3',
                    fontSize: '0.75rem',
                    fontWeight: 600,
                    cursor: 'pointer'
                  }}
                >
                  <Trash2 size={13} />
                  <span>Delete Node</span>
                </button>
              </div>
            </div>
          </div>
        )}

        {/* =========================================================================
            ADD COMPONENT PALETTE MODAL
            ========================================================================= */}
        {isPaletteOpen && (
          <div
            style={{
              position: 'fixed',
              top: 0,
              left: 0,
              width: '100vw',
              height: '100vh',
              background: 'rgba(15, 23, 42, 0.45)',
              backdropFilter: 'blur(4px)',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              zIndex: 99999
            }}
            onClick={() => setIsPaletteOpen(false)}
          >
            <div
              style={{
                width: '560px',
                background: '#ffffff',
                borderRadius: '16px',
                border: '1px solid #e2e8f0',
                boxShadow: '0 20px 45px rgba(0,0,0,0.2)',
                padding: '1.5rem',
                maxHeight: '85vh',
                overflowY: 'auto'
              }}
              onClick={(e) => e.stopPropagation()}
            >
              <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: '1.25rem', borderBottom: '1px solid #e2e8f0', paddingBottom: '0.75rem' }}>
                <div>
                  <h3 style={{ margin: 0, fontSize: '1.1rem', fontWeight: 800, color: '#0f172a' }}>
                    Insert Flow Component
                  </h3>
                  <div style={{ fontSize: '0.75rem', color: '#64748b', marginTop: '2px' }}>
                    Choose an approval tier, trigger, human screen, decision, or background action:
                  </div>
                </div>
                <button
                  type="button"
                  onClick={() => setIsPaletteOpen(false)}
                  style={{ background: 'transparent', border: 'none', cursor: 'pointer', color: '#94a3b8' }}
                >
                  <X size={18} />
                </button>
              </div>

              {/* Component Categories Grid */}
              <div style={{ display: 'flex', flexDirection: 'column', gap: '0.85rem' }}>
                {/* 1. Approval Tier Step */}
                <div
                  onClick={() =>
                    handleAddNode(
                      'approvalStep',
                      'tier_review',
                      'Level 1: Manager Sign-off',
                      'APPROVAL STEP',
                      'amount >= $50,000',
                      '#7c3aed',
                      'TIER REVIEW'
                    )
                  }
                  style={{
                    padding: '0.75rem 1rem',
                    borderRadius: '10px',
                    border: '1px solid #ddd6fe',
                    background: '#f5f3ff',
                    cursor: 'pointer',
                    display: 'flex',
                    alignItems: 'center',
                    gap: '0.75rem'
                  }}
                  className="hover:shadow-sm transition-all"
                >
                  <div style={{ width: '34px', height: '34px', borderRadius: '8px', background: '#7c3aed', color: '#fff', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                    <UserCheck size={18} />
                  </div>
                  <div>
                    <div style={{ fontWeight: 700, fontSize: '0.85rem', color: '#581c87' }}>Approval Step: Hierarchical Sign-off</div>
                    <div style={{ fontSize: '0.725rem', color: '#7c3aed' }}>Sequential manager tier with approved & rejected branch routes</div>
                  </div>
                </div>

                {/* 2. Event Trigger */}
                <div
                  onClick={() =>
                    handleAddNode(
                      'trigger',
                      'record_event',
                      'Record Event: On Create/Update',
                      'EVENT TRIGGER',
                      "status == 'Pending'",
                      '#4f46e5'
                    )
                  }
                  style={{
                    padding: '0.75rem 1rem',
                    borderRadius: '10px',
                    border: '1px solid #c7d2fe',
                    background: '#eef2ff',
                    cursor: 'pointer',
                    display: 'flex',
                    alignItems: 'center',
                    gap: '0.75rem'
                  }}
                  className="hover:shadow-sm transition-all"
                >
                  <div style={{ width: '34px', height: '34px', borderRadius: '8px', background: '#4f46e5', color: '#fff', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                    <Zap size={18} />
                  </div>
                  <div>
                    <div style={{ fontWeight: 700, fontSize: '0.85rem', color: '#312e81' }}>Trigger: Record Event Trigger</div>
                    <div style={{ fontSize: '0.725rem', color: '#4338ca' }}>Fires automatically upon entity create, stage transition, or update</div>
                  </div>
                </div>

                {/* 3. Decision Logic */}
                <div
                  onClick={() =>
                    handleAddNode(
                      'decision',
                      'condition',
                      'Decision: If / Else Branch',
                      'DECISION LOGIC',
                      'amount > 100000',
                      '#d97706'
                    )
                  }
                  style={{
                    padding: '0.75rem 1rem',
                    borderRadius: '10px',
                    border: '1px solid #fde68a',
                    background: '#fffbeb',
                    cursor: 'pointer',
                    display: 'flex',
                    alignItems: 'center',
                    gap: '0.75rem'
                  }}
                  className="hover:shadow-sm transition-all"
                >
                  <div style={{ width: '34px', height: '34px', borderRadius: '8px', background: '#d97706', color: '#fff', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                    <GitBranch size={18} />
                  </div>
                  <div>
                    <div style={{ fontWeight: 700, fontSize: '0.85rem', color: '#78350f' }}>Decision: If / Else Branch</div>
                    <div style={{ fontSize: '0.725rem', color: '#b45309' }}>Splits execution flow based on criteria (Yes vs No)</div>
                  </div>
                </div>

                {/* 4. Interactive Screen */}
                <div
                  onClick={() =>
                    handleAddNode(
                      'screen',
                      'data_table',
                      'Interactive Screen: Line-Items Grid',
                      'HUMAN SCREEN',
                      'Columns: Product, Qty, Price',
                      '#0284c7'
                    )
                  }
                  style={{
                    padding: '0.75rem 1rem',
                    borderRadius: '10px',
                    border: '1px solid #bae6fd',
                    background: '#f0f9ff',
                    cursor: 'pointer',
                    display: 'flex',
                    alignItems: 'center',
                    gap: '0.75rem'
                  }}
                  className="hover:shadow-sm transition-all"
                >
                  <div style={{ width: '34px', height: '34px', borderRadius: '8px', background: '#0284c7', color: '#fff', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                    <Table size={18} />
                  </div>
                  <div>
                    <div style={{ fontWeight: 700, fontSize: '0.85rem', color: '#0369a1' }}>Human Screen: Interactive Table</div>
                    <div style={{ fontSize: '0.725rem', color: '#0284c7' }}>Presents dynamic grid table for human reviewer data entry</div>
                  </div>
                </div>

                {/* 5. Field Mutation Action */}
                <div
                  onClick={() =>
                    handleAddNode(
                      'action',
                      'update_field',
                      'Action: Mutate Record Field',
                      'RECORD UPDATE',
                      "stage = 'Approved'",
                      '#059669'
                    )
                  }
                  style={{
                    padding: '0.75rem 1rem',
                    borderRadius: '10px',
                    border: '1px solid #a7f3d0',
                    background: '#f0fdf4',
                    cursor: 'pointer',
                    display: 'flex',
                    alignItems: 'center',
                    gap: '0.75rem'
                  }}
                  className="hover:shadow-sm transition-all"
                >
                  <div style={{ width: '34px', height: '34px', borderRadius: '8px', background: '#059669', color: '#fff', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                    <Edit3 size={18} />
                  </div>
                  <div>
                    <div style={{ fontWeight: 700, fontSize: '0.85rem', color: '#064e3b' }}>Action: Mutate Record Field</div>
                    <div style={{ fontSize: '0.725rem', color: '#047857' }}>Silently modifies target object stage, status, or attributes</div>
                  </div>
                </div>

                {/* 6. Lock Action */}
                <div
                  onClick={() =>
                    handleAddNode(
                      'action',
                      'lock_record',
                      'Action: Enforce Edit Lock',
                      'SECURITY GOVERNANCE',
                      'lock_record = true',
                      '#6366f1'
                    )
                  }
                  style={{
                    padding: '0.75rem 1rem',
                    borderRadius: '10px',
                    border: '1px solid #c7d2fe',
                    background: '#eef2ff',
                    cursor: 'pointer',
                    display: 'flex',
                    alignItems: 'center',
                    gap: '0.75rem'
                  }}
                  className="hover:shadow-sm transition-all"
                >
                  <div style={{ width: '34px', height: '34px', borderRadius: '8px', background: '#6366f1', color: '#fff', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                    <Lock size={18} />
                  </div>
                  <div>
                    <div style={{ fontWeight: 700, fontSize: '0.85rem', color: '#312e81' }}>Action: Lock / Unlock Record</div>
                    <div style={{ fontSize: '0.725rem', color: '#4338ca' }}>Prevents unauthorized edits during review cycles</div>
                  </div>
                </div>

                {/* 7. Outbound REST Webhook */}
                <div
                  onClick={() =>
                    handleAddNode(
                      'action',
                      'webhook',
                      'Action: Outbound REST Webhook',
                      'OUTBOUND API',
                      'POST /api/erp/dispatch',
                      '#7c3aed'
                    )
                  }
                  style={{
                    padding: '0.75rem 1rem',
                    borderRadius: '10px',
                    border: '1px solid #ddd6fe',
                    background: '#f5f3ff',
                    cursor: 'pointer',
                    display: 'flex',
                    alignItems: 'center',
                    gap: '0.75rem'
                  }}
                  className="hover:shadow-sm transition-all"
                >
                  <div style={{ width: '34px', height: '34px', borderRadius: '8px', background: '#7c3aed', color: '#fff', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                    <Globe size={18} />
                  </div>
                  <div>
                    <div style={{ fontWeight: 700, fontSize: '0.85rem', color: '#4c1d95' }}>Action: Outbound REST API</div>
                    <div style={{ fontSize: '0.725rem', color: '#6d28d9' }}>Dispatches JSON payload to third-party ERP/accounting endpoints</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}
