import React, { useState } from 'react';
import { X, Play, CheckCircle2, AlertTriangle, AlertCircle, ArrowRight, RefreshCw, Zap } from 'lucide-react';
import { SAMPLE_OPPORTUNITIES } from '../data/sampleRecords';

export default function TestRecordModal({
  isOpen,
  onClose,
  activePreset
}) {
  const [selectedOppId, setSelectedOppId] = useState(1);
  const [isRunning, setIsRunning] = useState(false);
  const [testResult, setTestResult] = useState(null);

  if (!isOpen) return null;

  const currentOpp = SAMPLE_OPPORTUNITIES.find((o) => o.id === Number(selectedOppId)) || SAMPLE_OPPORTUNITIES[0];

  const handleExecuteDryRun = () => {
    setIsRunning(true);
    setTestResult(null);

    setTimeout(() => {
      setIsRunning(false);
      if (currentOpp.has_po) {
        if (currentOpp.margin_percent >= 15) {
          setTestResult({
            status: 'success',
            title: 'Procurement Sync Executed Successfully (100% Complete)',
            badgeColor: '#059669',
            steps: [
              '1. Opportunity Trigger: stage == "Closed Won" (Verified: Revenue $' + currentOpp.amount.toLocaleString() + ')',
              '2. PO Verification: Linked ' + currentOpp.po_number + ' ("' + currentOpp.vendor_name + '") confirmed.',
              '3. Margin Check: Gross margin ' + currentOpp.margin_percent + '% is >= 15% threshold (Healthy).',
              '4. Record Mutation: ' + currentOpp.po_number + ' stage updated to "Supplier Confirmed". Linked #SO-' + currentOpp.opportunity_number + '.',
              '5. Document Generator: Compiled "Commercial_PO_' + currentOpp.po_number + '.pdf" and attached to files.',
              '6. ERP Dispatch: POST /api/erp/dispatch returned HTTP 200 (ERP Ref: #ERP-' + Math.floor(10000 + Math.random() * 90000) + ').',
              '7. Logistics Staging: Linked Shipment draft #SHP-901 staged for warehouse pickup.',
              '8. Customer Communication: Confirmation email dispatched to ' + currentOpp.customer_email + '.'
            ]
          });
        } else {
          setTestResult({
            status: 'warning',
            title: 'Flagged for Commercial Director Review (Margin < 15%)',
            badgeColor: '#d97706',
            steps: [
              '1. Opportunity Trigger: stage == "Closed Won" (Revenue $' + currentOpp.amount.toLocaleString() + ')',
              '2. PO Verification: Linked ' + currentOpp.po_number + ' found.',
              '3. Margin Check: Gross margin ' + currentOpp.margin_percent + '% is below 15.00% threshold.',
              '4. Governance Review: ' + currentOpp.po_number + ' set to "Low Margin Review". Approval notification sent to CFO.',
              '5. Action Halted: ERP dispatch suspended until commercial approval is signed.'
            ]
          });
        }
      } else {
        setTestResult({
          status: 'error',
          title: 'Exception Branch Triggered: Missing Linked PO',
          badgeColor: '#e11d48',
          steps: [
            '1. Opportunity Trigger: stage == "Closed Won" (Revenue $' + currentOpp.amount.toLocaleString() + ')',
            '2. PO Verification: purchase_deal_id is NULL. No coupled procurement deal found!',
            '3. Exception Action: High-Priority Task created for ' + currentOpp.assigned_to + ' (SLA: 4 hours).',
            '4. Notification: Push alert & Slack ops ping dispatched.',
            '5. Governance Hold: Opportunity fulfillment status set to "On Hold - PO Required".'
          ]
        });
      }
    }, 900);
  };

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
          width: '620px',
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
            marginBottom: '1rem',
            borderBottom: '1px solid #e2e8f0',
            paddingBottom: '0.75rem'
          }}
        >
          <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
            <Zap size={18} color="#0284c7" />
            <div>
              <h3 style={{ margin: 0, fontSize: '1.05rem', fontWeight: 800, color: '#0f172a' }}>
                Test Dry-Run Against Live Opportunity Record
              </h3>
              <div style={{ fontSize: '0.7rem', color: '#64748b' }}>
                Execute the "{activePreset.title}" rule against sample database entities
              </div>
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

        {/* Record Selector */}
        <div style={{ marginBottom: '1rem' }}>
          <label style={{ display: 'block', fontSize: '0.725rem', fontWeight: 700, color: '#475569', marginBottom: '0.35rem' }}>
            SELECT OPPORTUNITY RECORD
          </label>
          <select
            value={selectedOppId}
            onChange={(e) => {
              setSelectedOppId(e.target.value);
              setTestResult(null);
            }}
            style={{
              width: '100%',
              padding: '0.5rem 0.75rem',
              borderRadius: '8px',
              border: '1px solid #cbd5e1',
              fontSize: '0.8rem',
              fontWeight: 600,
              background: '#f8fafc'
            }}
          >
            {SAMPLE_OPPORTUNITIES.map((opp) => (
              <option key={opp.id} value={opp.id}>
                {opp.name} (${opp.amount.toLocaleString()}) — {opp.has_po ? `PO Linked (${opp.po_number})` : 'NO PO'}
              </option>
            ))}
          </select>
        </div>

        {/* Selected Record Card */}
        <div
          style={{
            background: '#f8fafc',
            border: '1px solid #e2e8f0',
            borderRadius: '10px',
            padding: '0.85rem',
            marginBottom: '1rem'
          }}
        >
          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '0.4rem' }}>
            <span style={{ fontWeight: 800, fontSize: '0.85rem', color: '#0f172a' }}>
              {currentOpp.name}
            </span>
            <span
              style={{
                fontSize: '0.625rem',
                fontWeight: 700,
                padding: '2px 6px',
                borderRadius: '4px',
                background: '#dcfce7',
                color: '#166534'
              }}
            >
              {currentOpp.stage}
            </span>
          </div>

          <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '0.4rem', fontSize: '0.725rem', color: '#475569' }}>
            <div>Account: <strong>{currentOpp.account_name}</strong></div>
            <div>Deal Amount: <strong>${currentOpp.amount.toLocaleString()}</strong></div>
            <div>Linked PO: <strong>{currentOpp.po_number || 'None (Missing)'}</strong></div>
            <div>Supplier: <strong>{currentOpp.vendor_name || 'N/A'}</strong></div>
            <div>Margin: <strong>{currentOpp.margin_percent}%</strong></div>
            <div>Owner: <strong>{currentOpp.assigned_to}</strong></div>
          </div>
        </div>

        {/* Execute Button */}
        <div style={{ marginBottom: '1rem' }}>
          <button
            type="button"
            onClick={handleExecuteDryRun}
            disabled={isRunning}
            style={{
              width: '100%',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              gap: '0.45rem',
              padding: '0.6rem',
              borderRadius: '8px',
              border: 'none',
              background: '#0284c7',
              color: '#ffffff',
              fontSize: '0.8rem',
              fontWeight: 700,
              cursor: isRunning ? 'not-allowed' : 'pointer',
              boxShadow: '0 2px 6px rgba(2, 132, 199, 0.35)'
            }}
          >
            {isRunning ? (
              <>
                <RefreshCw size={14} className="animate-spin" />
                <span>Evaluating Workflow Rules & Branching...</span>
              </>
            ) : (
              <>
                <Play size={14} fill="#ffffff" />
                <span>Execute Dry-Run Simulation</span>
              </>
            )}
          </button>
        </div>

        {/* Results Stream */}
        {testResult && (
          <div
            style={{
              border: `1px solid ${testResult.status === 'success' ? '#a7f3d0' : testResult.status === 'warning' ? '#fde68a' : '#fecdd3'}`,
              background: testResult.status === 'success' ? '#f0fdf4' : testResult.status === 'warning' ? '#fffbeb' : '#fff1f2',
              borderRadius: '10px',
              padding: '0.85rem'
            }}
          >
            <div style={{ display: 'flex', alignItems: 'center', gap: '0.4rem', marginBottom: '0.5rem' }}>
              {testResult.status === 'success' ? (
                <CheckCircle2 size={16} color="#059669" />
              ) : testResult.status === 'warning' ? (
                <AlertTriangle size={16} color="#d97706" />
              ) : (
                <AlertCircle size={16} color="#e11d48" />
              )}
              <span style={{ fontWeight: 800, fontSize: '0.85rem', color: testResult.badgeColor }}>
                {testResult.title}
              </span>
            </div>

            <div style={{ display: 'flex', flexDirection: 'column', gap: '0.35rem' }}>
              {testResult.steps.map((step, idx) => (
                <div
                  key={idx}
                  style={{
                    fontSize: '0.725rem',
                    color: '#334155',
                    padding: '0.3rem 0.5rem',
                    background: '#ffffff',
                    borderRadius: '6px',
                    border: '1px solid rgba(0,0,0,0.06)'
                  }}
                >
                  {step}
                </div>
              ))}
            </div>
          </div>
        )}
      </div>
    </div>
  );
}
