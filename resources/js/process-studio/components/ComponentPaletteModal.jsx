import React from 'react';
import {
  X,
  UserCheck,
  Zap,
  GitBranch,
  Table,
  Edit3,
  Lock,
  Globe,
  FileText,
  Truck
} from 'lucide-react';

export default function ComponentPaletteModal({
  isOpen,
  onClose,
  onAddNode
}) {
  if (!isOpen) return null;

  return (
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
      onClick={onClose}
    >
      <div
        style={{
          width: '580px',
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
        <div
          style={{
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'space-between',
            marginBottom: '1.25rem',
            borderBottom: '1px solid #e2e8f0',
            paddingBottom: '0.75rem'
          }}
        >
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
            onClick={onClose}
            style={{ background: 'transparent', border: 'none', cursor: 'pointer', color: '#94a3b8' }}
          >
            <X size={18} />
          </button>
        </div>

        {/* Component Categories Grid */}
        <div style={{ display: 'flex', flexDirection: 'column', gap: '0.75rem' }}>
          {/* 1. Approval Tier Step */}
          <div
            onClick={() =>
              onAddNode(
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
              onAddNode(
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
              onAddNode(
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
              onAddNode(
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
              onAddNode(
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

          {/* 6. Document Generation */}
          <div
            onClick={() =>
              onAddNode(
                'action',
                'document_gen',
                'Action: Auto-Generate PDF Document',
                'DOCUMENT AUTOMATION',
                "Generate 'Commercial PO & Agreement PDF'",
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
              <FileText size={18} />
            </div>
            <div>
              <div style={{ fontWeight: 700, fontSize: '0.85rem', color: '#312e81' }}>Action: Document Generation</div>
              <div style={{ fontSize: '0.725rem', color: '#4338ca' }}>Compiles PDF contract or PO and auto-attaches to records</div>
            </div>
          </div>

          {/* 7. Outbound REST Webhook */}
          <div
            onClick={() =>
              onAddNode(
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

          {/* 8. Logistics Staging */}
          <div
            onClick={() =>
              onAddNode(
                'action',
                'shipment',
                'Action: Initialize Inbound Freight',
                'LOGISTICS & FULFILLMENT',
                "Shipment.status -> 'Staged'",
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
              <Truck size={18} />
            </div>
            <div>
              <div style={{ fontWeight: 700, fontSize: '0.85rem', color: '#0369a1' }}>Action: Logistics Milestone Staging</div>
              <div style={{ fontSize: '0.725rem', color: '#0284c7' }}>Creates shipment draft and warehouse fulfillment tasks</div>
            </div>
          </div>

          {/* 9. Lock Action */}
          <div
            onClick={() =>
              onAddNode(
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
        </div>
      </div>
    </div>
  );
}
