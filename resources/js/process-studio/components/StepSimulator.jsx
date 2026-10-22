import React, { useState, useEffect } from 'react';
import { Activity, Play, Pause, RotateCcw, X, CheckCircle2, AlertTriangle, AlertCircle, ArrowRight } from 'lucide-react';
import { SAMPLE_OPPORTUNITIES } from '../data/sampleRecords';

export default function StepSimulator({
  isOpen,
  onClose,
  nodes,
  activePresetKey,
  simulationStep,
  setSimulationStep,
  simulationLogs,
  setSimulationLogs
}) {
  const [selectedScenario, setSelectedScenario] = useState('happy_path');
  const [isAutoPlaying, setIsAutoPlaying] = useState(false);

  const isOppWon = activePresetKey === 'opp-won-sync';

  // Opp Won specific scenario definitions
  const OPP_WON_SCENARIOS = {
    happy_path: {
      title: 'Scenario 1: Standard Closed Won Deal (Linked PO & 32% Margin)',
      record: SAMPLE_OPPORTUNITIES[0],
      pathNodes: ['node-1', 'node-2', 'node-3', 'node-4', 'node-5', 'node-6', 'node-7', 'node-8'],
      steps: [
        {
          nodeId: 'node-1',
          title: 'Opportunity Stage Change',
          type: 'trigger',
          text: '[Trigger] Opportunity "Apex Supply Chain ($145,000.00)" transitioned to Closed Won. Evaluated amount > 0 -> TRUE.'
        },
        {
          nodeId: 'node-2',
          title: 'Check Linked PO',
          type: 'decision',
          text: '[PO Check] Linked Purchase Deal PO-8842 ("Foxconn Global Technology", $98,000.00) verified. Branch YES taken.'
        },
        {
          nodeId: 'node-3',
          title: 'Validate Margin & Terms',
          type: 'decision',
          text: '[Margin Audit] Gross Margin is 32.41% (>= 15.00% threshold). Net Terms: 30 days. Branch YES taken.'
        },
        {
          nodeId: 'node-4',
          title: 'Update Procurement Deal',
          type: 'action',
          text: '[Record Mutation] Purchase Deal PO-8842 stage updated to "Supplier Confirmed". Linked Sales Order ref #SO-1094.'
        },
        {
          nodeId: 'node-5',
          title: 'Auto-Generate PO PDF',
          type: 'action',
          text: '[Doc Gen] Generated "PO_Confirmation_SO1094.pdf" (SHA-256 verified) and auto-attached to Opportunity & PO records.'
        },
        {
          nodeId: 'node-6',
          title: 'ERP Webhook Outbound Sync',
          type: 'action',
          text: '[Outbound REST] POST /api/erp/dispatch dispatched -> 200 OK (ERP System Ref: #ERP-99201).'
        },
        {
          nodeId: 'node-7',
          title: 'Initialize Inbound Freight',
          type: 'action',
          text: '[Logistics] Created linked Shipment draft #SHP-4019 (Status: "Inbound Cargo Staged"). Warehouse QA task dispatched.'
        },
        {
          nodeId: 'node-8',
          title: 'Client Order Confirmation',
          type: 'action',
          text: '[Communication] Order confirmation email with attached PDF sent to logistics@apexsupply.com. Audit log entry recorded.'
        }
      ]
    },
    missing_po: {
      title: 'Scenario 2: Missing PO Exception Path (purchase_deal_id is NULL)',
      record: SAMPLE_OPPORTUNITIES[1],
      pathNodes: ['node-1', 'node-2', 'node-ex-task', 'node-ex-alert'],
      steps: [
        {
          nodeId: 'node-1',
          title: 'Opportunity Stage Change',
          type: 'trigger',
          text: '[Trigger] Opportunity "Vanguard Dynamics ($85,000.00)" transitioned to Closed Won.'
        },
        {
          nodeId: 'node-2',
          title: 'Check Linked PO',
          type: 'decision',
          text: '[PO Check] Evaluated purchase_deal_id: NULL. No coupled procurement deal found. Branch NO (Exception) taken!'
        },
        {
          nodeId: 'node-ex-task',
          title: 'Create Urgent Procurement Task',
          type: 'exception',
          text: '[Exception Task] Created High-Priority Task for Sarah Jenkins: "Link Supplier PO for Closed Won Deal OPP-1102" (SLA: 4h).'
        },
        {
          nodeId: 'node-ex-alert',
          title: 'Alert Deal Owner & Ops Channel',
          type: 'exception',
          text: '[Alert Dispatch] Push notification & Slack channel alert sent. Opportunity status locked to "Pending Procurement Link".'
        }
      ]
    },
    low_margin: {
      title: 'Scenario 3: Low Margin Review Flag (Gross Margin 7.1% < 15%)',
      record: SAMPLE_OPPORTUNITIES[2],
      pathNodes: ['node-1', 'node-2', 'node-3', 'node-warn-margin'],
      steps: [
        {
          nodeId: 'node-1',
          title: 'Opportunity Stage Change',
          type: 'trigger',
          text: '[Trigger] Opportunity "Global Steel Consortium ($210,000.00)" transitioned to Closed Won.'
        },
        {
          nodeId: 'node-2',
          title: 'Check Linked PO',
          type: 'decision',
          text: '[PO Check] Linked Purchase Deal PO-9102 ($195,000.00) verified. Branch YES taken.'
        },
        {
          nodeId: 'node-3',
          title: 'Validate Margin & Terms',
          type: 'decision',
          text: '[Margin Audit] Gross Margin is 7.14% (< 15.00% minimum). Profitability check FAILED. Branch NO (Review Flag) taken!'
        },
        {
          nodeId: 'node-warn-margin',
          title: 'Flag Commercial Review',
          type: 'action',
          text: '[Governance Review] PO-9102 marked as "Low Margin Review". Approval request sent to CFO & Commercial Director. ERP sync suspended.'
        }
      ]
    }
  };

  const currentScenario = OPP_WON_SCENARIOS[selectedScenario] || OPP_WON_SCENARIOS.happy_path;
  const totalScenarioSteps = isOppWon ? currentScenario.steps.length : nodes.length;

  const handleNextStep = () => {
    if (isOppWon) {
      if (simulationStep >= currentScenario.steps.length) {
        setSimulationStep(0);
        setSimulationLogs([{ time: new Date().toLocaleTimeString(), text: 'Simulation reset to idle state.' }]);
        setIsAutoPlaying(false);
        return;
      }
      const nextIndex = simulationStep + 1;
      setSimulationStep(nextIndex);
      const stepData = currentScenario.steps[nextIndex - 1];
      setSimulationLogs((prev) => [
        ...prev,
        {
          time: new Date().toLocaleTimeString(),
          text: stepData.text,
          nodeTitle: stepData.title,
          type: stepData.type
        }
      ]);
    } else {
      if (nodes.length === 0) return;
      const nextStep = (simulationStep + 1) % (nodes.length + 1);
      setSimulationStep(nextStep);

      if (nextStep === 0) {
        setSimulationLogs([{ time: new Date().toLocaleTimeString(), text: 'Simulation reset to idle state.' }]);
        setIsAutoPlaying(false);
      } else {
        const activeNode = nodes[nextStep - 1];
        const logMsg =
          activeNode.type === 'processApprovalStep'
            ? `[Step ${nextStep}] Approval Tier: "${activeNode.data.title}" approved by ${activeNode.data.approver || 'Approver'} (SLA Verified).`
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
    }
  };

  const handleReset = () => {
    setSimulationStep(0);
    setSimulationLogs([]);
    setIsAutoPlaying(false);
  };

  // Auto-play timer effect
  useEffect(() => {
    let timer;
    if (isAutoPlaying) {
      if (simulationStep >= totalScenarioSteps) {
        setIsAutoPlaying(false);
      } else {
        timer = setTimeout(() => {
          handleNextStep();
        }, 1200);
      }
    }
    return () => clearTimeout(timer);
  }, [isAutoPlaying, simulationStep, totalScenarioSteps]);

  if (!isOpen) return null;

  return (
    <div
      style={{
        position: 'absolute',
        top: '1rem',
        right: '1rem',
        width: '380px',
        background: 'rgba(255, 255, 255, 0.98)',
        backdropFilter: 'blur(10px)',
        borderRadius: '16px',
        border: '1px solid #cbd5e1',
        boxShadow: '0 16px 40px rgba(0,0,0,0.14)',
        padding: '1.15rem',
        zIndex: 50,
        maxHeight: 'calc(100vh - 85px)',
        display: 'flex',
        flexDirection: 'column'
      }}
    >
      {/* Header */}
      <div
        style={{
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'space-between',
          marginBottom: '0.75rem',
          borderBottom: '1px solid #e2e8f0',
          paddingBottom: '0.5rem'
        }}
      >
        <div style={{ display: 'flex', alignItems: 'center', gap: '0.45rem' }}>
          <div
            style={{
              width: '26px',
              height: '26px',
              borderRadius: '6px',
              background: '#fef3c7',
              color: '#d97706',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center'
            }}
          >
            <Activity size={15} />
          </div>
          <div>
            <span style={{ fontWeight: 800, fontSize: '0.875rem', color: '#0f172a' }}>
              Live Step Simulator
            </span>
            <div style={{ fontSize: '0.65rem', color: '#64748b' }}>
              Multi-scenario visual execution trace
            </div>
          </div>
        </div>
        <button
          type="button"
          onClick={onClose}
          style={{ background: 'transparent', border: 'none', cursor: 'pointer', color: '#94a3b8' }}
        >
          <X size={15} />
        </button>
      </div>

      {/* Scenario Selector for opp-won-sync */}
      {isOppWon && (
        <div style={{ marginBottom: '0.85rem' }}>
          <label style={{ display: 'block', fontSize: '0.6875rem', fontWeight: 800, color: '#475569', marginBottom: '0.35rem', textTransform: 'uppercase' }}>
            Choose Test Scenario
          </label>
          <select
            value={selectedScenario}
            onChange={(e) => {
              setSelectedScenario(e.target.value);
              handleReset();
            }}
            style={{
              width: '100%',
              padding: '0.45rem 0.65rem',
              borderRadius: '8px',
              border: '1px solid #cbd5e1',
              fontSize: '0.75rem',
              fontWeight: 600,
              background: '#f8fafc',
              color: '#0f172a',
              cursor: 'pointer'
            }}
          >
            <option value="happy_path">✅ Happy Path (Linked PO & 32% Margin)</option>
            <option value="missing_po">🚨 Exception Path (Missing PO & Alert)</option>
            <option value="low_margin">⚠️ Warning Path (Low Margin Review &lt; 15%)</option>
          </select>

          {/* Test Record Info Box */}
          <div
            style={{
              marginTop: '0.45rem',
              padding: '0.45rem 0.65rem',
              borderRadius: '6px',
              background: '#f1f5f9',
              border: '1px solid #e2e8f0',
              fontSize: '0.6875rem',
              color: '#334155'
            }}
          >
            <div>
              <strong>Record:</strong> {currentScenario.record.name}
            </div>
            <div style={{ display: 'flex', justifyContent: 'space-between', marginTop: '2px' }}>
              <span>Value: <strong>${currentScenario.record.amount.toLocaleString()}</strong></span>
              <span>PO: <strong>{currentScenario.record.po_number || 'None'}</strong></span>
              <span>Margin: <strong>{currentScenario.record.margin_percent}%</strong></span>
            </div>
          </div>
        </div>
      )}

      {/* Control Buttons */}
      <div style={{ display: 'flex', gap: '0.4rem', marginBottom: '0.75rem' }}>
        <button
          type="button"
          onClick={handleNextStep}
          style={{
            flex: 1,
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            gap: '0.35rem',
            padding: '0.45rem',
            borderRadius: '8px',
            background: '#d97706',
            color: '#ffffff',
            border: 'none',
            fontSize: '0.75rem',
            fontWeight: 700,
            cursor: 'pointer',
            boxShadow: '0 1px 3px rgba(217, 119, 6, 0.4)'
          }}
        >
          <Play size={12} fill="#ffffff" />
          <span>Next Step ({simulationStep}/{totalScenarioSteps})</span>
        </button>

        <button
          type="button"
          onClick={() => setIsAutoPlaying(!isAutoPlaying)}
          style={{
            padding: '0.45rem 0.65rem',
            borderRadius: '8px',
            background: isAutoPlaying ? '#ef4444' : '#059669',
            color: '#ffffff',
            border: 'none',
            fontSize: '0.72rem',
            fontWeight: 700,
            cursor: 'pointer',
            display: 'flex',
            alignItems: 'center',
            gap: '0.3rem'
          }}
          title={isAutoPlaying ? 'Pause Auto Run' : 'Auto Play Simulation'}
        >
          {isAutoPlaying ? <Pause size={12} /> : <Play size={12} />}
          <span>{isAutoPlaying ? 'Pause' : 'Auto'}</span>
        </button>

        <button
          type="button"
          onClick={handleReset}
          style={{
            padding: '0.45rem 0.65rem',
            borderRadius: '8px',
            background: '#f1f5f9',
            border: '1px solid #cbd5e1',
            fontSize: '0.72rem',
            cursor: 'pointer',
            color: '#475569'
          }}
          title="Reset simulation"
        >
          <RotateCcw size={12} />
        </button>
      </div>

      {/* Telemetry Log Terminal */}
      <div style={{ flex: 1, display: 'flex', flexDirection: 'column', minHeight: '180px' }}>
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '0.3rem' }}>
          <span style={{ fontSize: '0.65rem', fontWeight: 800, color: '#64748b', textTransform: 'uppercase' }}>
            Execution Telemetry Stream
          </span>
          <span style={{ fontSize: '0.6rem', color: '#10b981', fontFamily: 'monospace' }}>
            ● ONLINE
          </span>
        </div>

        <div
          style={{
            background: '#09090b',
            color: '#22c55e',
            borderRadius: '8px',
            padding: '0.75rem',
            fontSize: '0.6875rem',
            fontFamily: 'monospace',
            flex: 1,
            maxHeight: '220px',
            overflowY: 'auto',
            border: '1px solid #27272a'
          }}
        >
          {simulationLogs.length === 0 ? (
            <div style={{ color: '#71717a' }}>
              Press "Next Step" or "Auto" to trace execution telemetry...
            </div>
          ) : (
            simulationLogs.map((log, idx) => (
              <div key={idx} style={{ marginBottom: '6px', lineHeight: 1.4 }}>
                <span style={{ color: '#60a5fa' }}>[{log.time}]</span>{' '}
                <span
                  style={{
                    color: log.type === 'exception' ? '#f43f5e' : log.type === 'decision' ? '#fbbf24' : '#22c55e'
                  }}
                >
                  {log.text}
                </span>
              </div>
            ))
          )}
        </div>
      </div>
    </div>
  );
}
