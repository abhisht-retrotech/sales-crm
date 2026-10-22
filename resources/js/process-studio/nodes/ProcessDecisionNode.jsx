import React from 'react';
import { Handle, Position, useReactFlow } from '@xyflow/react';
import { Trash2 } from 'lucide-react';

export default function ProcessDecisionNode({ id, data, selected }) {
  const { deleteElements } = useReactFlow();

  const handleDelete = (e) => {
    e.stopPropagation();
    deleteElements({ nodes: [{ id }] });
  };

  const isSelected = selected;
  const targetFieldKey = data.target_field_key || (data.target_field ? data.target_field.match(/\(([^)]+)\)/)?.[1] : 'amount') || 'amount';
  const operatorKey = data.operator_key || 'greater_than';
  const comparisonVal = data.comparison_value !== undefined ? data.comparison_value : '50000';
  const conditionText = data.condition || `If ${targetFieldKey} ${operatorKey} "${comparisonVal}"`;

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

      {/* Node Inner Container */}
      <div style={{ borderRadius: '10px', overflow: 'hidden' }}>
        {/* Node Header: [::: DECISION LOGIC] on left, [node-xxxx] on right */}
        <div
          style={{
            background: '#fffdf0',
            padding: '0.5rem 0.75rem',
            borderBottom: '1px solid #fef08a',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'space-between'
          }}
        >
          {/* Left: Grip dots + DECISION LOGIC */}
          <div style={{ display: 'flex', alignItems: 'center', gap: '0.35rem' }}>
            <div style={{ display: 'flex', gap: '2px', alignItems: 'center' }}>
              <div style={{ display: 'flex', flexDirection: 'column', gap: '2px' }}>
                <span style={{ width: '2px', height: '2px', background: '#d97706', borderRadius: '50%' }} />
                <span style={{ width: '2px', height: '2px', background: '#d97706', borderRadius: '50%' }} />
                <span style={{ width: '2px', height: '2px', background: '#d97706', borderRadius: '50%' }} />
              </div>
              <div style={{ display: 'flex', flexDirection: 'column', gap: '2px' }}>
                <span style={{ width: '2px', height: '2px', background: '#d97706', borderRadius: '50%' }} />
                <span style={{ width: '2px', height: '2px', background: '#d97706', borderRadius: '50%' }} />
                <span style={{ width: '2px', height: '2px', background: '#d97706', borderRadius: '50%' }} />
              </div>
            </div>
            <span
              style={{
                fontSize: '0.675rem',
                fontWeight: 800,
                textTransform: 'uppercase',
                letterSpacing: '0.04em',
                color: '#d97706'
              }}
            >
              {data.category || 'DECISION LOGIC'}
            </span>
          </div>

          {/* Right: Node ID (e.g. node-8406-3ba) */}
          <span
            style={{
              fontSize: '0.65rem',
              fontFamily: 'monospace',
              color: '#94a3b8',
              letterSpacing: '-0.02em'
            }}
          >
            {id || 'node-8406-3ba'}
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
            {data.title !== undefined ? data.title : 'If / Else Branch Condition'}
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
              : 'Splits flow execution based on field or math rules'}
          </p>

          {/* Condition Box matching screenshot: If amount greater_than "50000" */}
          <div
            style={{
              marginTop: '0.55rem',
              display: 'block',
              background: '#fefce8',
              border: '1px solid #fef08a',
              borderRadius: '6px',
              padding: '0.35rem 0.6rem',
              fontSize: '0.725rem',
              color: '#854d0e',
              fontWeight: 600,
              fontFamily: 'monospace'
            }}
          >
            {conditionText}
          </div>
        </div>

        {/* Node Footer: [condition] on left, [trash icon] on right */}
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
            {data.subtype || 'condition'}
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
