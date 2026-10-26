import React, { useState } from 'react';
import {
  Workflow,
  ChevronDown,
  Check,
  Play,
  Save,
  CheckCircle2,
  X,
  Minus,
  Plus,
  PanelLeft,
  Sliders,
  ExternalLink
} from 'lucide-react';
import { WORKFLOW_PRESETS } from '../data/presets';

export default function FlowHeader({
  activePresetKey,
  activePreset,
  switchWorkflow,
  nodes,
  isSimulatorOpen,
  setIsSimulatorOpen,
  isBlocksOpen,
  setIsBlocksOpen,
  isInspectorOpen,
  setIsInspectorOpen,
  handleSaveFlow,
  saveToast,
  onZoomIn,
  onZoomOut,
  onZoomReset,
  zoomLevel = 100,
  flowStatus = 'Active',
  setFlowStatus
}) {
  const [isPresetDropdownOpen, setIsPresetDropdownOpen] = useState(false);

  // Compute stats for subtitle: Target: Opportunities • X Screens • Y Decisions • Z Actions
  const screensCount = nodes.filter((n) => n.type === 'processScreen').length;
  const decisionsCount = nodes.filter((n) => n.type === 'processDecision').length;
  const actionsCount = nodes.filter((n) => n.type === 'processAction' || n.type === 'processApprovalStep').length;

  const isApprovalCategory = activePreset.categoryType === 'Approval Process';
  const backUrl = isApprovalCategory ? '/admin/approval-rules' : '/admin/process-studio';

  return (
    <>
      <header
        style={{
          background: '#ffffff',
          borderBottom: '1px solid #e2e8f0',
          padding: '0.55rem 1.15rem',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'space-between',
          zIndex: 40,
          boxShadow: '0 1px 2px rgba(0,0,0,0.03)'
        }}
      >
        {/* Left: Branding & Dynamic Subtitle */}
        <div style={{ display: 'flex', alignItems: 'center', gap: '0.75rem' }}>
          {/* Blue Flow Icon Badge */}
          <div
            style={{
              width: '32px',
              height: '32px',
              borderRadius: '8px',
              background: '#2563eb',
              color: '#ffffff',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              boxShadow: '0 2px 5px rgba(37, 99, 235, 0.3)',
              cursor: 'pointer'
            }}
            onClick={() => setIsPresetDropdownOpen(!isPresetDropdownOpen)}
            title="Switch Workflow Preset"
          >
            <Workflow size={17} />
          </div>

          <div>
            <div style={{ display: 'flex', alignItems: 'center', gap: '0.45rem' }}>
              <button
                type="button"
                onClick={() => setIsPresetDropdownOpen(!isPresetDropdownOpen)}
                style={{
                  background: 'transparent',
                  border: 'none',
                  padding: 0,
                  fontSize: '0.925rem',
                  fontWeight: 800,
                  color: '#0f172a',
                  cursor: 'pointer',
                  display: 'inline-flex',
                  alignItems: 'center',
                  gap: '0.35rem'
                }}
                className="hover:text-indigo-600"
              >
                <span>{activePreset.title}</span>
                <ChevronDown size={14} color="#64748b" />
              </button>

              {/* Status Badge */}
              <span
                style={{
                  fontSize: '0.625rem',
                  fontWeight: 700,
                  padding: '2px 7px',
                  borderRadius: '9999px',
                  background: flowStatus === 'Active' ? '#dcfce7' : '#fee2e2',
                  color: flowStatus === 'Active' ? '#15803d' : '#b91c1c',
                  display: 'inline-flex',
                  alignItems: 'center',
                  gap: '4px'
                }}
              >
                <span style={{ width: '5px', height: '5px', borderRadius: '50%', background: flowStatus === 'Active' ? '#16a34a' : '#ef4444' }} />
                <span>{flowStatus}</span>
              </span>
            </div>

            {/* Subtitle breakdown */}
            <div style={{ fontSize: '0.6875rem', color: '#64748b', display: 'flex', alignItems: 'center', gap: '0.35rem', marginTop: '1px' }}>
              <span>Target: <strong>{activePreset.entity || 'Opportunities'}</strong></span>
              <span>•</span>
              <span style={{ color: '#0284c7' }}>{screensCount} Screens</span>
              <span>•</span>
              <span style={{ color: '#d97706' }}>{decisionsCount} Decisions</span>
              <span>•</span>
              <span style={{ color: '#059669' }}>{actionsCount} Actions</span>
            </div>
          </div>

          {/* Workflow Preset Selector Dropdown */}
          {isPresetDropdownOpen && (
            <div
              style={{
                position: 'absolute',
                top: '55px',
                left: '20px',
                width: '420px',
                background: '#ffffff',
                borderRadius: '12px',
                border: '1px solid #cbd5e1',
                boxShadow: '0 16px 40px rgba(0,0,0,0.15)',
                zIndex: 9999,
                overflow: 'hidden'
              }}
            >
              <div
                style={{
                  padding: '0.65rem 0.85rem',
                  background: '#f8fafc',
                  borderBottom: '1px solid #e2e8f0',
                  fontSize: '0.725rem',
                  fontWeight: 700,
                  color: '#475569',
                  textTransform: 'uppercase'
                }}
              >
                Select Workflow Process
              </div>

              <div style={{ maxHeight: '380px', overflowY: 'auto', padding: '0.4rem' }}>
                <div style={{ fontSize: '0.65rem', fontWeight: 800, color: '#0284c7', padding: '0.35rem 0.5rem', textTransform: 'uppercase' }}>
                  Process Automation Rules
                </div>
                {['cargo-specifications', 'record-event-flow', 'dynamic-grid-flow', 'form-dialog-flow', 'if-else-branch-flow', 'update-record-flow', 'create-record-flow', 'send-email-flow', 'toast-alert-flow', 'lock-record-flow', 'opp-won-sync', 'lead-round-robin', 'wire-webhook', 'freight-alerter'].map((key) => {
                  const item = WORKFLOW_PRESETS[key];
                  if (!item) return null;
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

                <div style={{ height: '1px', background: '#e2e8f0', margin: '0.4rem 0' }} />

                <div style={{ fontSize: '0.65rem', fontWeight: 800, color: '#7c3aed', padding: '0.35rem 0.5rem', textTransform: 'uppercase' }}>
                  Approval Processes
                </div>
                {['lead-qualification-approval', 'mega-deal-discount', 'procurement-po', 'wire-clearance', 'air-freight'].map((key) => {
                  const item = WORKFLOW_PRESETS[key];
                  if (!item) return null;
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
              </div>
            </div>
          )}
        </div>

        {/* Right Controls Matching Reference Image */}
        <div style={{ display: 'flex', alignItems: 'center', gap: '0.6rem' }}>
          {/* Zoom Buttons [ - 100% + ] */}
          <div
            style={{
              display: 'inline-flex',
              alignItems: 'center',
              background: '#f8fafc',
              border: '1px solid #cbd5e1',
              borderRadius: '8px',
              padding: '2px'
            }}
          >
            <button
              type="button"
              onClick={onZoomOut}
              style={{
                background: 'transparent',
                border: 'none',
                padding: '0.3rem 0.45rem',
                cursor: 'pointer',
                color: '#64748b',
                display: 'flex',
                alignItems: 'center'
              }}
              className="hover:text-slate-900"
              title="Zoom out"
            >
              <Minus size={13} />
            </button>

            <span
              onClick={onZoomReset}
              style={{
                fontSize: '0.72rem',
                fontWeight: 700,
                color: '#334155',
                padding: '0 0.35rem',
                cursor: 'pointer'
              }}
              title="Reset to 100%"
            >
              {Math.round(zoomLevel)}%
            </span>

            <button
              type="button"
              onClick={onZoomIn}
              style={{
                background: 'transparent',
                border: 'none',
                padding: '0.3rem 0.45rem',
                cursor: 'pointer',
                color: '#64748b',
                display: 'flex',
                alignItems: 'center'
              }}
              className="hover:text-slate-900"
              title="Zoom in"
            >
              <Plus size={13} />
            </button>
          </div>

          {/* Simulate Flow Button */}
          <button
            type="button"
            onClick={() => setIsSimulatorOpen(!isSimulatorOpen)}
            style={{
              display: 'inline-flex',
              alignItems: 'center',
              gap: '0.35rem',
              padding: '0.42rem 0.75rem',
              borderRadius: '8px',
              fontSize: '0.78rem',
              fontWeight: 700,
              background: '#ffffff',
              color: '#4f46e5',
              border: '1px solid #c7d2fe',
              cursor: 'pointer',
              boxShadow: '0 1px 2px rgba(0,0,0,0.04)'
            }}
            className="hover:bg-indigo-50"
          >
            <Play size={13} fill="#4f46e5" />
            <span>Simulate Flow</span>
          </button>

          {/* Active Dropdown */}
          <select
            value={flowStatus}
            onChange={(e) => setFlowStatus(e.target.value)}
            style={{
              padding: '0.42rem 0.65rem',
              borderRadius: '8px',
              fontSize: '0.78rem',
              fontWeight: 600,
              background: '#f8fafc',
              color: '#0f172a',
              border: '1px solid #cbd5e1',
              cursor: 'pointer'
            }}
          >
            <option value="Active">Active</option>
            <option value="Draft">Draft</option>
            <option value="Paused">Paused</option>
          </select>

          {/* Save & Publish Button (Purple) */}
          <button
            type="button"
            onClick={handleSaveFlow}
            style={{
              display: 'inline-flex',
              alignItems: 'center',
              gap: '0.4rem',
              padding: '0.42rem 0.85rem',
              borderRadius: '8px',
              fontSize: '0.78rem',
              fontWeight: 700,
              background: 'linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%)',
              color: '#ffffff',
              border: 'none',
              cursor: 'pointer',
              boxShadow: '0 2px 6px rgba(124, 58, 237, 0.35)'
            }}
            className="hover:opacity-95"
          >
            <Save size={13} />
            <span>Save &amp; Publish</span>
          </button>

          {/* Blocks Toggle Button */}
          <button
            type="button"
            onClick={() => setIsBlocksOpen(!isBlocksOpen)}
            style={{
              display: 'inline-flex',
              alignItems: 'center',
              gap: '0.35rem',
              padding: '0.42rem 0.65rem',
              borderRadius: '8px',
              fontSize: '0.78rem',
              fontWeight: 600,
              background: isBlocksOpen ? '#ede9fe' : '#ffffff',
              color: isBlocksOpen ? '#6d28d9' : '#475569',
              border: isBlocksOpen ? '1px solid #ddd6fe' : '1px solid #cbd5e1',
              cursor: 'pointer'
            }}
            title="Toggle Flow Building Blocks sidebar"
          >
            <PanelLeft size={13} />
            <span>Blocks</span>
          </button>

          {/* Inspector Toggle Button */}
          <button
            type="button"
            onClick={() => setIsInspectorOpen(!isInspectorOpen)}
            style={{
              display: 'inline-flex',
              alignItems: 'center',
              gap: '0.35rem',
              padding: '0.42rem 0.65rem',
              borderRadius: '8px',
              fontSize: '0.78rem',
              fontWeight: 600,
              background: isInspectorOpen ? '#ede9fe' : '#ffffff',
              color: isInspectorOpen ? '#6d28d9' : '#475569',
              border: isInspectorOpen ? '1px solid #ddd6fe' : '1px solid #cbd5e1',
              cursor: 'pointer'
            }}
            title="Toggle Node Inspector"
          >
            <Sliders size={13} />
            <span>Inspector</span>
          </button>

          {/* Close X Button */}
          <a
            href={backUrl}
            style={{
              width: '30px',
              height: '30px',
              borderRadius: '8px',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              color: '#64748b',
              textDecoration: 'none',
              border: '1px solid #e2e8f0',
              background: '#ffffff'
            }}
            className="hover:bg-slate-100 hover:text-slate-900"
            title="Close studio"
          >
            <X size={16} />
          </a>
        </div>
      </header>

      {/* Save Toast Notification */}
      {saveToast && (
        <div
          style={{
            position: 'fixed',
            top: '65px',
            right: '20px',
            background: '#059669',
            color: '#ffffff',
            padding: '0.6rem 1rem',
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
          <span>Workflow "{activePreset.title}" published successfully!</span>
        </div>
      )}
    </>
  );
}
