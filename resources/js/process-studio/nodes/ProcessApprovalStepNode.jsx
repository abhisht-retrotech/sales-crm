import React from 'react';
import { Handle, Position } from '@xyflow/react';
import { UserCheck, Clock, Shield, Check, X } from 'lucide-react';

export default function ProcessApprovalStepNode({ data, selected }) {
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
        transition: 'all 0.15s ease',
        cursor: 'grab'
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
}
