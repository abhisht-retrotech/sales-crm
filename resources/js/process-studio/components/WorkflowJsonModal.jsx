import React, { useState } from 'react';
import { X, Copy, Check, Download, FileCode } from 'lucide-react';

export default function WorkflowJsonModal({
  isOpen,
  onClose,
  activePreset,
  nodes,
  edges
}) {
  const [copied, setCopied] = useState(false);

  if (!isOpen) return null;

  const flowPayload = {
    workflow_id: activePreset.id,
    title: activePreset.title,
    entity: activePreset.entity,
    categoryType: activePreset.categoryType,
    nodes_count: nodes.length,
    edges_count: edges.length,
    exported_at: new Date().toISOString(),
    nodes,
    edges
  };

  const jsonString = JSON.stringify(flowPayload, null, 2);

  const handleCopy = () => {
    navigator.clipboard.writeText(jsonString);
    setCopied(true);
    setTimeout(() => setCopied(false), 2000);
  };

  const handleDownload = () => {
    const blob = new Blob([jsonString], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `${activePreset.id}-workflow.json`;
    a.click();
    URL.revokeObjectURL(url);
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
          width: '640px',
          background: '#ffffff',
          borderRadius: '16px',
          border: '1px solid #e2e8f0',
          boxShadow: '0 20px 45px rgba(0,0,0,0.2)',
          padding: '1.5rem',
          maxHeight: '85vh',
          display: 'flex',
          flexDirection: 'column'
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
            <FileCode size={18} color="#4f46e5" />
            <div>
              <h3 style={{ margin: 0, fontSize: '1.05rem', fontWeight: 800, color: '#0f172a' }}>
                Workflow JSON Schema
              </h3>
              <div style={{ fontSize: '0.7rem', color: '#64748b' }}>
                {activePreset.title} ({nodes.length} nodes, {edges.length} edges)
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

        <pre
          style={{
            flex: 1,
            background: '#09090b',
            color: '#38bdf8',
            borderRadius: '10px',
            padding: '1rem',
            fontSize: '0.725rem',
            fontFamily: 'monospace',
            overflowY: 'auto',
            maxHeight: '360px',
            margin: 0
          }}
        >
          {jsonString}
        </pre>

        <div style={{ display: 'flex', justifyContent: 'flex-end', gap: '0.5rem', marginTop: '1rem' }}>
          <button
            type="button"
            onClick={handleCopy}
            style={{
              display: 'inline-flex',
              alignItems: 'center',
              gap: '0.4rem',
              padding: '0.45rem 0.85rem',
              borderRadius: '8px',
              border: '1px solid #cbd5e1',
              background: '#f8fafc',
              fontSize: '0.75rem',
              fontWeight: 600,
              cursor: 'pointer'
            }}
          >
            {copied ? <Check size={14} color="#059669" /> : <Copy size={14} />}
            <span>{copied ? 'Copied to Clipboard' : 'Copy JSON'}</span>
          </button>

          <button
            type="button"
            onClick={handleDownload}
            style={{
              display: 'inline-flex',
              alignItems: 'center',
              gap: '0.4rem',
              padding: '0.45rem 0.85rem',
              borderRadius: '8px',
              border: 'none',
              background: '#4f46e5',
              color: '#ffffff',
              fontSize: '0.75rem',
              fontWeight: 700,
              cursor: 'pointer'
            }}
          >
            <Download size={14} />
            <span>Download .json</span>
          </button>
        </div>
      </div>
    </div>
  );
}
