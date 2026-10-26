import React from 'react';
import {
  X,
  Trash2,
  PanelRightClose,
  Globe,
  FileText,
  Truck,
  AlertCircle,
  Bell,
  Sliders,
  CheckCircle2,
  GitBranch,
  ArrowRight,
  Clock,
  Layers,
  Plus,
  Mail,
  Send,
  UserCheck,
  Lock
} from 'lucide-react';

export default function NodeInspector({
  isOpen,
  onClose,
  selectedNode,
  nodes = [],
  updateNodeData,
  deleteNode,
  onConnectNextNode
}) {
  if (!isOpen) return null;

  const node = selectedNode || nodes[0];
  if (!node) {
    return (
      <aside
        style={{
          width: '320px',
          minWidth: '320px',
          height: '100%',
          background: '#ffffff',
          borderLeft: '1px solid #e2e8f0',
          display: 'flex',
          flexDirection: 'column',
          alignItems: 'center',
          justifyContent: 'center',
          padding: '2rem',
          color: '#64748b',
          textAlign: 'center',
          zIndex: 25
        }}
      >
        <Sliders size={28} color="#cbd5e1" style={{ marginBottom: '0.75rem' }} />
        <h4 style={{ margin: 0, fontSize: '0.9rem', color: '#1e293b', fontWeight: 700 }}>
          No Node Selected
        </h4>
        <p style={{ fontSize: '0.75rem', marginTop: '4px' }}>
          Click any block on the canvas to configure its settings and criteria.
        </p>
      </aside>
    );
  }

  const { data = {}, type, id } = node || {};

  const isDecision =
    type === 'processDecision' ||
    data.subtype === 'condition' ||
    data.title?.toLowerCase().includes('branch') ||
    data.title?.toLowerCase().includes('if / else');
  const isTrigger = type === 'processTrigger';
  const isScreen = type === 'processScreen';
  const isFormDialog =
    data.subtype === 'modal_form' ||
    data.subtype === 'form_dialog' ||
    data.title?.toLowerCase().includes('form');
  const isDataTable =
    (data.subtype === 'data_table' ||
    data.title?.toLowerCase().includes('grid') ||
    data.title?.toLowerCase().includes('table') ||
    (isScreen && data.subtype !== 'form_dialog' && data.subtype !== 'modal_form' && data.subtype !== 'approval_prompt')) &&
    !isFormDialog;
  const isButtonClick = data.subtype === 'button_click' || data.title?.toLowerCase().includes('custom button');
  const isRecordEvent = data.subtype === 'record_event' || data.title?.toLowerCase().includes('record event') || data.title?.toLowerCase().includes('create/update') || (!data.subtype && isTrigger && !isButtonClick);
  const isLock =
    data.subtype === 'lock_record' ||
    data.title?.toLowerCase().includes('lock');
  const isToast =
    (data.subtype === 'show_toast' ||
    data.title?.toLowerCase().includes('toast')) &&
    !isLock;
  const isWebhook = data.subtype === 'webhook';
  const isDocGen = data.subtype === 'document_gen';
  const isShipment = data.subtype === 'shipment';
  const isEmail =
    (data.subtype === 'send_email' ||
    data.title?.toLowerCase().includes('send email') ||
    data.title?.toLowerCase().includes('email template') ||
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
    (data.subtype === 'update_field' ||
    data.title?.toLowerCase().includes('update record') ||
    data.title?.toLowerCase().includes('update field')) &&
    !isLock &&
    !isToast &&
    !isCreateRecord &&
    !isEmail;

  const isApproval =
    type === 'processApprovalStep' ||
    data.subtype === 'approval_step' ||
    data.subtype === 'approvalStep' ||
    data.subtype === 'approval_prompt' ||
    data.title?.toLowerCase().includes('sign-off') ||
    data.title?.toLowerCase().includes('approval');

  // Available target nodes to connect to
  const availableTargetNodes = nodes.filter((n) => n.id !== id);

  // Form Field Helpers for Interactive Form Dialog
  const defaultFormFields = [
    { label: 'Reason / Purpose', name: 'reason', type: 'Text Input', required: true },
    { label: 'Priority Level', name: 'priority_level', type: 'Dropdown (Select)', options: 'Low, Medium, High, Urgent', required: true },
    { label: 'Target Close Date', name: 'target_close_date', type: 'Date Picker', required: true },
    { label: 'Additional Comments', name: 'comments', type: 'Textarea (Long Text)', required: false }
  ];

  const formFields = Array.isArray(data.form_fields) && data.form_fields.length > 0 ? data.form_fields : defaultFormFields;

  const handleUpdateFormField = (idx, field, val) => {
    const updated = formFields.map((f, i) => (i === idx ? { ...f, [field]: val } : f));
    updateNodeData('form_fields', updated);
  };

  const handleAddFormField = () => {
    const newIdx = formFields.length + 1;
    const updated = [
      ...formFields,
      { label: `Field #${newIdx}`, name: `field_${newIdx}`, type: 'Text Input', required: false }
    ];
    updateNodeData('form_fields', updated);
  };

  const handleDeleteFormField = (idx) => {
    if (formFields.length <= 1) return;
    const updated = formFields.filter((_, i) => i !== idx);
    updateNodeData('form_fields', updated);
  };

  // Table Column Helpers for Dynamic Grid Table
  const defaultColumns = [
    { header: 'Item Name', key: 'item', type: 'Text (General)' },
    { header: 'Quantity', key: 'qty', type: 'Number (with Auto Total Sum)' },
    { header: 'Gross Weight (KG)', key: 'weight', type: 'Number (with Auto Total Sum)' }
  ];

  const columns = Array.isArray(data.columns) && data.columns.length > 0 ? data.columns : defaultColumns;

  const handleUpdateColumn = (idx, field, val) => {
    const updated = columns.map((c, i) => (i === idx ? { ...c, [field]: val } : c));
    updateNodeData('columns', updated);
  };

  const handleAddColumn = () => {
    const newIdx = columns.length + 1;
    const updated = [
      ...columns,
      { header: `Column #${newIdx}`, key: `col_${newIdx}`, type: 'Text (General)' }
    ];
    updateNodeData('columns', updated);
  };

  const handleDeleteColumn = (idx) => {
    if (columns.length <= 1) return;
    const updated = columns.filter((_, i) => i !== idx);
    updateNodeData('columns', updated);
  };

  const handleLoadColumnPreset = (presetType) => {
    if (presetType === 'pricing') {
      updateNodeData('columns', [
        { header: 'Item Name', key: 'item', type: 'Text (General)' },
        { header: 'Quantity', key: 'qty', type: 'Number (with Auto Total Sum)' },
        { header: 'Unit Price ($)', key: 'unit_price', type: 'Currency ($ USD)' },
        { header: 'Line Total ($)', key: 'line_total', type: 'Currency ($ USD)' }
      ]);
    } else if (presetType === 'cargo') {
      updateNodeData('columns', [
        { header: 'Item Name', key: 'item', type: 'Text (General)' },
        { header: 'Quantity', key: 'qty', type: 'Number (with Auto Total Sum)' },
        { header: 'Gross Weight (KG)', key: 'weight', type: 'Number (with Auto Total Sum)' },
        { header: 'Dimensions (LxWxH)', key: 'dims', type: 'Text (General)' }
      ]);
    } else if (presetType === 'tasks') {
      updateNodeData('columns', [
        { header: 'Task Name', key: 'task', type: 'Text General' },
        { header: 'Assigned Role', key: 'assigned', type: 'Text General' },
        { header: 'Completed', key: 'completed', type: 'Checkbox Boolean' }
      ]);
    }
  };

  // Field Mutation Helpers for Update Record Field
  const defaultMutateFields = [
    { field: 'Stage (stage)', key: 'stage', value: 'Approved' }
  ];

  const mutateFields =
    Array.isArray(data.mutate_fields) && data.mutate_fields.length > 0
      ? data.mutate_fields
      : defaultMutateFields;

  const handleUpdateMutateField = (idx, prop, val) => {
    const updated = mutateFields.map((f, i) => {
      if (i === idx) {
        if (prop === 'field') {
          const match = val.match(/\(([^)]+)\)/);
          const key = match ? match[1] : 'stage';
          return { ...f, field: val, key };
        }
        return { ...f, [prop]: val };
      }
      return f;
    });
    updateNodeData('mutate_fields', updated);
    if (updated[0]) {
      updateNodeData('target_field', updated[0].field);
      updateNodeData('target_field_key', updated[0].key);
      updateNodeData('target_value', updated[0].value);
      updateNodeData('condition', `Set ${updated[0].key} = "${updated[0].value}"`);
    }
  };

  const handleAddMutateField = () => {
    const updated = [
      ...mutateFields,
      { field: 'Status (status)', key: 'status', value: 'Active' }
    ];
    updateNodeData('mutate_fields', updated);
  };

  const handleDeleteMutateField = (idx) => {
    if (mutateFields.length <= 1) return;
    const updated = mutateFields.filter((_, i) => i !== idx);
    updateNodeData('mutate_fields', updated);
  };

  const nodeTitleFallback =
    data.title ||
    (isApproval
      ? 'Approval Step Review'
      : isLock
      ? 'Lock / Unlock Record'
      : isToast
      ? 'Pop-up Toast Alert'
      : isEmail
      ? 'Send Email Template'
      : isCreateRecord
      ? 'Create Child Record / Task'
      : isUpdate
      ? 'Update Record Field'
      : isFormDialog
      ? 'Interactive Form Dialog'
      : isDataTable
      ? 'Dynamic Grid Table'
      : isDecision
      ? 'If / Else Branch Condition'
      : 'Custom Button Click');

  const nodeHeaderColor = isApproval
    ? '#7c3aed'
    : (isDataTable || isFormDialog || isDecision || isUpdate || isCreateRecord || isEmail || isToast || isLock)
    ? '#0284c7'
    : '#4f46e5';

  return (
    <aside
      style={{
        width: '320px',
        minWidth: '320px',
        maxWidth: '320px',
        height: '100%',
        background: '#ffffff',
        borderLeft: '1px solid #e2e8f0',
        display: 'flex',
        flexDirection: 'column',
        zIndex: 25,
        overflow: 'hidden',
        userSelect: 'none'
      }}
    >
      {/* Header Matching Screenshot */}
      <div
        style={{
          padding: '0.85rem 1rem',
          borderBottom: '1px solid #e2e8f0',
          display: 'flex',
          alignItems: 'flex-start',
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
            NODE INSPECTOR &amp; SETTINGS
          </div>
          <div style={{ fontSize: '0.6875rem', color: '#64748b', marginTop: '2px' }}>
            Configuring: <strong style={{ color: nodeHeaderColor }}>{nodeTitleFallback}</strong>
          </div>
        </div>

        <button
          type="button"
          onClick={onClose}
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
          title="Collapse Inspector"
        >
          <PanelRightClose size={16} />
        </button>
      </div>

      {/* Form Body */}
      <div
        style={{
          flex: 1,
          overflowY: 'auto',
          padding: '1rem',
          display: 'flex',
          flexDirection: 'column',
          gap: '1rem'
        }}
      >
        {/* Node Title */}
        <div>
          <label style={{ display: 'block', fontSize: '0.725rem', fontWeight: 700, color: '#334155', marginBottom: '0.35rem' }}>
            Node Title *
          </label>
          <input
            type="text"
            value={data.title !== undefined ? data.title : (isLock ? 'Lock / Unlock Record' : (isToast ? 'Pop-up Toast Alert' : (isEmail ? 'Send Email Template' : (isCreateRecord ? 'Create Child Record / Task' : ''))))}
            onChange={(e) => updateNodeData('title', e.target.value)}
            style={{
              width: '100%',
              padding: '0.45rem 0.65rem',
              borderRadius: '8px',
              border: '1px solid #cbd5e1',
              fontSize: '0.8rem',
              fontWeight: 600,
              color: '#0f172a'
            }}
          />
        </div>

        {/* Step Description / User Guidance */}
        <div>
          <label style={{ display: 'block', fontSize: '0.725rem', fontWeight: 700, color: '#334155', marginBottom: '0.35rem' }}>
            Step Description / User Guidance
          </label>
          <textarea
            rows={3}
            value={data.description !== undefined ? data.description : (isLock ? 'Enforces admin-only edit lock on the record' : (isToast ? 'Shows confirmation, warning, or success toast' : (isEmail ? 'Dispatches templated email notification' : (isCreateRecord ? 'Inserts new Task, Invoice, or related record' : ''))))}
            onChange={(e) => updateNodeData('description', e.target.value)}
            style={{
              width: '100%',
              padding: '0.45rem 0.65rem',
              borderRadius: '8px',
              border: '1px solid #cbd5e1',
              fontSize: '0.75rem',
              color: '#334155',
              resize: 'none'
            }}
          />
        </div>

        {/* Interactive Form Dialog Config Box (Exact match from screenshot) */}
        {isFormDialog && (
          <div
            style={{
              background: '#f8faff',
              border: '1px solid #bae6fd',
              borderRadius: '10px',
              padding: '0.85rem'
            }}
          >
            <div
              style={{
                fontSize: '0.775rem',
                fontWeight: 700,
                color: '#0284c7',
                marginBottom: '0.65rem'
              }}
            >
              Interactive Form Dialog Config
            </div>

            {/* Modal Window Title */}
            <div style={{ marginBottom: '0.65rem' }}>
              <label
                style={{
                  display: 'block',
                  fontSize: '0.725rem',
                  fontWeight: 600,
                  color: '#374151',
                  marginBottom: '0.35rem'
                }}
              >
                Modal Window Title
              </label>
              <input
                type="text"
                value={data.modal_title !== undefined ? data.modal_title : 'Opportunity Data Intake Dialog'}
                onChange={(e) => updateNodeData('modal_title', e.target.value)}
                placeholder="Opportunity Data Intake Dialog"
                style={{
                  width: '100%',
                  padding: '0.45rem 0.65rem',
                  borderRadius: '6px',
                  border: '1px solid #cbd5e1',
                  fontSize: '0.775rem',
                  fontWeight: 600,
                  color: '#1e293b',
                  background: '#ffffff'
                }}
              />
            </div>

            {/* User Instructions */}
            <div style={{ marginBottom: '0.65rem' }}>
              <label
                style={{
                  display: 'block',
                  fontSize: '0.725rem',
                  fontWeight: 600,
                  color: '#374151',
                  marginBottom: '0.35rem'
                }}
              >
                User Instructions
              </label>
              <textarea
                rows={2}
                value={data.instructions !== undefined ? data.instructions : 'Please enter the requested details before proceeding.'}
                onChange={(e) => updateNodeData('instructions', e.target.value)}
                placeholder="Please enter the requested details before proceeding."
                style={{
                  width: '100%',
                  padding: '0.45rem 0.65rem',
                  borderRadius: '6px',
                  border: '1px solid #cbd5e1',
                  fontSize: '0.75rem',
                  color: '#334155',
                  background: '#ffffff',
                  resize: 'none'
                }}
              />
            </div>

            {/* Submit Button Text */}
            <div style={{ marginBottom: '0.75rem' }}>
              <label
                style={{
                  display: 'block',
                  fontSize: '0.725rem',
                  fontWeight: 600,
                  color: '#374151',
                  marginBottom: '0.35rem'
                }}
              >
                Submit Button Text
              </label>
              <input
                type="text"
                value={data.submit_button_text !== undefined ? data.submit_button_text : 'Submit & Proceed'}
                onChange={(e) => updateNodeData('submit_button_text', e.target.value)}
                placeholder="Submit & Proceed"
                style={{
                  width: '100%',
                  padding: '0.45rem 0.65rem',
                  borderRadius: '6px',
                  border: '1px solid #cbd5e1',
                  fontSize: '0.775rem',
                  fontWeight: 600,
                  color: '#1e293b',
                  background: '#ffffff'
                }}
              />
            </div>

            {/* Form Input Fields (X) header & + Add Field button */}
            <div
              style={{
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'space-between',
                paddingTop: '0.5rem',
                borderTop: '1px dashed #e2e8f0',
                marginBottom: '0.6rem'
              }}
            >
              <span style={{ fontSize: '0.75rem', fontWeight: 700, color: '#0284c7', textTransform: 'uppercase' }}>
                FORM INPUT FIELDS ({formFields.length})
              </span>
              <button
                type="button"
                onClick={handleAddFormField}
                style={{
                  display: 'inline-flex',
                  alignItems: 'center',
                  gap: '2px',
                  background: '#ffffff',
                  border: '1px solid #bae6fd',
                  color: '#0284c7',
                  borderRadius: '6px',
                  padding: '0.2rem 0.5rem',
                  fontSize: '0.6875rem',
                  fontWeight: 600,
                  cursor: 'pointer'
                }}
                className="hover:bg-sky-50"
              >
                + Add Field
              </button>
            </div>

            {/* List of Input Field Cards */}
            <div style={{ display: 'flex', flexDirection: 'column', gap: '0.5rem' }}>
              {formFields.map((field, idx) => (
                <div
                  key={idx}
                  style={{
                    background: '#ffffff',
                    border: '1px solid #e2e8f0',
                    borderRadius: '8px',
                    padding: '0.6rem 0.75rem',
                    boxShadow: '0 1px 2px rgba(0,0,0,0.03)'
                  }}
                >
                  {/* Card Header: #X: Label and red trash */}
                  <div
                    style={{
                      display: 'flex',
                      alignItems: 'center',
                      justifyContent: 'space-between',
                      marginBottom: '0.45rem'
                    }}
                  >
                    <span style={{ fontSize: '0.725rem', fontWeight: 700, color: '#1e293b' }}>
                      #{idx + 1}: {field.label || `Field ${idx + 1}`}
                    </span>
                    <button
                      type="button"
                      onClick={() => handleDeleteFormField(idx)}
                      style={{
                        background: 'transparent',
                        border: 'none',
                        color: '#f87171',
                        cursor: 'pointer',
                        padding: '2px',
                        display: 'flex',
                        alignItems: 'center'
                      }}
                      className="hover:text-red-600"
                      title="Delete field"
                    >
                      <Trash2 size={13} />
                    </button>
                  </div>

                  {/* Inputs: Field Label & Variable (Name) */}
                  <div
                    style={{
                      display: 'grid',
                      gridTemplateColumns: '1fr 1fr',
                      gap: '0.5rem',
                      marginBottom: '0.45rem'
                    }}
                  >
                    <div>
                      <label
                        style={{
                          display: 'block',
                          fontSize: '0.65rem',
                          fontWeight: 600,
                          color: '#64748b',
                          marginBottom: '0.2rem'
                        }}
                      >
                        Field Label
                      </label>
                      <input
                        type="text"
                        value={field.label || ''}
                        onChange={(e) => handleUpdateFormField(idx, 'label', e.target.value)}
                        style={{
                          width: '100%',
                          padding: '0.35rem 0.5rem',
                          borderRadius: '5px',
                          border: '1px solid #cbd5e1',
                          fontSize: '0.725rem',
                          fontWeight: 600,
                          color: '#1e293b'
                        }}
                      />
                    </div>
                    <div>
                      <label
                        style={{
                          display: 'block',
                          fontSize: '0.65rem',
                          fontWeight: 600,
                          color: '#64748b',
                          marginBottom: '0.2rem'
                        }}
                      >
                        Variable (Name)
                      </label>
                      <input
                        type="text"
                        value={field.name || ''}
                        onChange={(e) => handleUpdateFormField(idx, 'name', e.target.value)}
                        style={{
                          width: '100%',
                          padding: '0.35rem 0.5rem',
                          borderRadius: '5px',
                          border: '1px solid #cbd5e1',
                          fontSize: '0.725rem',
                          fontFamily: 'monospace',
                          color: '#1e293b'
                        }}
                      />
                    </div>
                  </div>

                  {/* Input Type and Required checkbox */}
                  <div
                    style={{
                      display: 'flex',
                      alignItems: 'center',
                      justifyContent: 'space-between',
                      gap: '0.5rem',
                      marginBottom: field.type?.includes('Dropdown') ? '0.45rem' : 0
                    }}
                  >
                    <div style={{ flex: 1 }}>
                      <label
                        style={{
                          display: 'block',
                          fontSize: '0.65rem',
                          fontWeight: 600,
                          color: '#64748b',
                          marginBottom: '0.2rem'
                        }}
                      >
                        Input Type
                      </label>
                      <select
                        value={field.type || 'Text Input'}
                        onChange={(e) => handleUpdateFormField(idx, 'type', e.target.value)}
                        style={{
                          width: '100%',
                          padding: '0.35rem 0.5rem',
                          borderRadius: '5px',
                          border: '1px solid #cbd5e1',
                          fontSize: '0.725rem',
                          background: '#ffffff',
                          color: '#1e293b'
                        }}
                      >
                        <option value="Text Input">Text Input</option>
                        <option value="Dropdown (Select)">Dropdown (Select)</option>
                        <option value="Textarea (Long Text)">Textarea (Long Text)</option>
                        <option value="Number Input">Number Input</option>
                        <option value="Date Picker">Date Picker</option>
                        <option value="Checkbox (Boolean)">Checkbox (Boolean)</option>
                        <option value="File Upload">File Upload</option>
                      </select>
                    </div>

                    <label
                      style={{
                        display: 'inline-flex',
                        alignItems: 'center',
                        gap: '0.3rem',
                        cursor: 'pointer',
                        fontSize: '0.7rem',
                        fontWeight: 600,
                        color: '#334155',
                        marginTop: '1rem'
                      }}
                    >
                      <input
                        type="checkbox"
                        checked={field.required !== false}
                        onChange={(e) => handleUpdateFormField(idx, 'required', e.target.checked)}
                        style={{ accentColor: '#0284c7', width: '13px', height: '13px', cursor: 'pointer' }}
                      />
                      <span>Required</span>
                    </label>
                  </div>

                  {/* If Dropdown type, show Dropdown Options input */}
                  {field.type?.includes('Dropdown') && (
                    <div>
                      <label
                        style={{
                          display: 'block',
                          fontSize: '0.65rem',
                          fontWeight: 600,
                          color: '#64748b',
                          marginBottom: '0.2rem'
                        }}
                      >
                        Dropdown Options (comma-separated)
                      </label>
                      <input
                        type="text"
                        value={field.options || ''}
                        onChange={(e) => handleUpdateFormField(idx, 'options', e.target.value)}
                        placeholder="Low, Medium, High, Urgent"
                        style={{
                          width: '100%',
                          padding: '0.35rem 0.5rem',
                          borderRadius: '5px',
                          border: '1px solid #cbd5e1',
                          fontSize: '0.725rem',
                          color: '#1e293b'
                        }}
                      />
                    </div>
                  )}
                </div>
              ))}
            </div>
          </div>
        )}

        {/* Dynamic Grid Table Config Box (Exact match from screenshot) */}
        {isDataTable && (
          <div
            style={{
              background: '#f8faff',
              border: '1px solid #bae6fd',
              borderRadius: '10px',
              padding: '0.85rem'
            }}
          >
            <div
              style={{
                fontSize: '0.775rem',
                fontWeight: 700,
                color: '#0284c7',
                marginBottom: '0.65rem'
              }}
            >
              Dynamic Grid Table Config
            </div>

            {/* Modal Window Title */}
            <div style={{ marginBottom: '0.65rem' }}>
              <label
                style={{
                  display: 'block',
                  fontSize: '0.725rem',
                  fontWeight: 600,
                  color: '#374151',
                  marginBottom: '0.35rem'
                }}
              >
                Modal Window Title
              </label>
              <input
                type="text"
                value={data.modal_title !== undefined ? data.modal_title : 'Specification Data Table'}
                onChange={(e) => updateNodeData('modal_title', e.target.value)}
                placeholder="Specification Data Table"
                style={{
                  width: '100%',
                  padding: '0.45rem 0.65rem',
                  borderRadius: '6px',
                  border: '1px solid #cbd5e1',
                  fontSize: '0.775rem',
                  fontWeight: 600,
                  color: '#1e293b',
                  background: '#ffffff'
                }}
              />
            </div>

            {/* Instructions Text */}
            <div style={{ marginBottom: '0.65rem' }}>
              <label
                style={{
                  display: 'block',
                  fontSize: '0.725rem',
                  fontWeight: 600,
                  color: '#374151',
                  marginBottom: '0.35rem'
                }}
              >
                Instructions Text
              </label>
              <textarea
                rows={2}
                value={data.instructions !== undefined ? data.instructions : 'Please enter child specification line items.'}
                onChange={(e) => updateNodeData('instructions', e.target.value)}
                placeholder="Please enter child specification line items."
                style={{
                  width: '100%',
                  padding: '0.45rem 0.65rem',
                  borderRadius: '6px',
                  border: '1px solid #cbd5e1',
                  fontSize: '0.75rem',
                  color: '#334155',
                  background: '#ffffff',
                  resize: 'none'
                }}
              />
            </div>

            {/* Allow user to click [+ Add Row] */}
            <div style={{ marginBottom: '0.75rem' }}>
              <label
                style={{
                  display: 'flex',
                  alignItems: 'center',
                  gap: '0.45rem',
                  cursor: 'pointer',
                  fontSize: '0.75rem',
                  fontWeight: 600,
                  color: '#0284c7'
                }}
              >
                <input
                  type="checkbox"
                  checked={data.allow_add_row !== false}
                  onChange={(e) => updateNodeData('allow_add_row', e.target.checked)}
                  style={{
                    accentColor: '#0284c7',
                    width: '14px',
                    height: '14px',
                    cursor: 'pointer'
                  }}
                />
                <span style={{ color: '#1e293b' }}>Allow user to click [+ Add Row]</span>
              </label>
            </div>

            {/* Table Columns (X) header and + Add Column button */}
            <div
              style={{
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'space-between',
                paddingTop: '0.5rem',
                borderTop: '1px dashed #e2e8f0',
                marginBottom: '0.4rem'
              }}
            >
              <span style={{ fontSize: '0.75rem', fontWeight: 700, color: '#334155' }}>
                Table Columns ({columns.length})
              </span>
              <button
                type="button"
                onClick={handleAddColumn}
                style={{
                  display: 'inline-flex',
                  alignItems: 'center',
                  gap: '2px',
                  background: '#ffffff',
                  border: '1px solid #bae6fd',
                  color: '#0284c7',
                  borderRadius: '6px',
                  padding: '0.2rem 0.5rem',
                  fontSize: '0.6875rem',
                  fontWeight: 600,
                  cursor: 'pointer'
                }}
                className="hover:bg-sky-50"
              >
                + Add Column
              </button>
            </div>

            {/* Load Column Preset */}
            <div style={{ marginBottom: '0.65rem' }}>
              <span style={{ fontSize: '0.675rem', color: '#64748b', display: 'block', marginBottom: '0.3rem' }}>
                Load Column Preset:
              </span>
              <div style={{ display: 'flex', flexWrap: 'wrap', gap: '0.35rem' }}>
                <button
                  type="button"
                  onClick={() => handleLoadColumnPreset('pricing')}
                  style={{
                    background: '#ffffff',
                    border: '1px solid #cbd5e1',
                    borderRadius: '12px',
                    padding: '0.15rem 0.55rem',
                    fontSize: '0.675rem',
                    color: '#475569',
                    cursor: 'pointer',
                    fontWeight: 500
                  }}
                  className="hover:bg-slate-50"
                >
                  Line-Items &amp; Pricing
                </button>
                <button
                  type="button"
                  onClick={() => handleLoadColumnPreset('cargo')}
                  style={{
                    background: '#ffffff',
                    border: '1px solid #cbd5e1',
                    borderRadius: '12px',
                    padding: '0.15rem 0.55rem',
                    fontSize: '0.675rem',
                    color: '#475569',
                    cursor: 'pointer',
                    fontWeight: 500
                  }}
                  className="hover:bg-slate-50"
                >
                  Logistics / Cargo
                </button>
                <button
                  type="button"
                  onClick={() => handleLoadColumnPreset('tasks')}
                  style={{
                    background: '#ffffff',
                    border: '1px solid #cbd5e1',
                    borderRadius: '12px',
                    padding: '0.15rem 0.55rem',
                    fontSize: '0.675rem',
                    color: '#475569',
                    cursor: 'pointer',
                    fontWeight: 500
                  }}
                  className="hover:bg-slate-50"
                >
                  Task Checklist
                </button>
              </div>
            </div>

            {/* Columns Cards */}
            <div style={{ display: 'flex', flexDirection: 'column', gap: '0.5rem' }}>
              {columns.map((col, idx) => (
                <div
                  key={idx}
                  style={{
                    background: '#ffffff',
                    border: '1px solid #e2e8f0',
                    borderRadius: '8px',
                    padding: '0.6rem 0.75rem',
                    boxShadow: '0 1px 2px rgba(0,0,0,0.03)'
                  }}
                >
                  {/* Column Header & Trash */}
                  <div
                    style={{
                      display: 'flex',
                      alignItems: 'center',
                      justifyContent: 'space-between',
                      marginBottom: '0.45rem'
                    }}
                  >
                    <span style={{ fontSize: '0.725rem', fontWeight: 700, color: '#1e293b' }}>
                      Column #{idx + 1}
                    </span>
                    <button
                      type="button"
                      onClick={() => handleDeleteColumn(idx)}
                      style={{
                        background: 'transparent',
                        border: 'none',
                        color: '#f87171',
                        cursor: 'pointer',
                        padding: '2px',
                        display: 'flex',
                        alignItems: 'center'
                      }}
                      className="hover:text-red-600"
                      title="Delete column"
                    >
                      <Trash2 size={13} />
                    </button>
                  </div>

                  {/* Inputs: Column Header & Field Key */}
                  <div
                    style={{
                      display: 'grid',
                      gridTemplateColumns: '1fr 1fr',
                      gap: '0.5rem',
                      marginBottom: '0.45rem'
                    }}
                  >
                    <div>
                      <label
                        style={{
                          display: 'block',
                          fontSize: '0.65rem',
                          fontWeight: 600,
                          color: '#64748b',
                          marginBottom: '0.2rem'
                        }}
                      >
                        Column Header
                      </label>
                      <input
                        type="text"
                        value={col.header || ''}
                        onChange={(e) => handleUpdateColumn(idx, 'header', e.target.value)}
                        style={{
                          width: '100%',
                          padding: '0.35rem 0.5rem',
                          borderRadius: '5px',
                          border: '1px solid #cbd5e1',
                          fontSize: '0.725rem',
                          fontWeight: 600,
                          color: '#1e293b'
                        }}
                      />
                    </div>
                    <div>
                      <label
                        style={{
                          display: 'block',
                          fontSize: '0.65rem',
                          fontWeight: 600,
                          color: '#64748b',
                          marginBottom: '0.2rem'
                        }}
                      >
                        Field Key
                      </label>
                      <input
                        type="text"
                        value={col.key || ''}
                        onChange={(e) => handleUpdateColumn(idx, 'key', e.target.value)}
                        style={{
                          width: '100%',
                          padding: '0.35rem 0.5rem',
                          borderRadius: '5px',
                          border: '1px solid #cbd5e1',
                          fontSize: '0.725rem',
                          fontFamily: 'monospace',
                          color: '#1e293b'
                        }}
                      />
                    </div>
                  </div>

                  {/* Data Type */}
                  <div>
                    <label
                      style={{
                        display: 'block',
                        fontSize: '0.65rem',
                        fontWeight: 600,
                        color: '#64748b',
                        marginBottom: '0.2rem'
                      }}
                    >
                      Data Type
                    </label>
                    <select
                      value={col.type || 'Text (General)'}
                      onChange={(e) => handleUpdateColumn(idx, 'type', e.target.value)}
                      style={{
                        width: '100%',
                        padding: '0.35rem 0.5rem',
                        borderRadius: '5px',
                        border: '1px solid #cbd5e1',
                        fontSize: '0.725rem',
                        background: '#ffffff',
                        color: '#1e293b'
                      }}
                    >
                      <option value="Text (General)">Text (General)</option>
                      <option value="Number (with Auto Total Sum)">Number (with Auto Total Sum)</option>
                      <option value="Currency ($ USD)">Currency ($ USD)</option>
                      <option value="Date Picker">Date Picker</option>
                      <option value="Dropdown Select">Dropdown Select</option>
                      <option value="Checkbox (Boolean)">Checkbox (Boolean)</option>
                    </select>
                  </div>
                </div>
              ))}
            </div>
          </div>
        )}

        {/* Custom Button Appearance Box (Exact match from screenshot) */}
        {isButtonClick && (
          <div
            style={{
              background: '#f8faff',
              border: '1px solid #c7d2fe',
              borderRadius: '10px',
              padding: '0.85rem'
            }}
          >
            <div
              style={{
                fontSize: '0.775rem',
                fontWeight: 700,
                color: '#4338ca',
                marginBottom: '0.65rem'
              }}
            >
              Custom Button Appearance
            </div>

            <div>
              <label
                style={{
                  display: 'block',
                  fontSize: '0.725rem',
                  fontWeight: 600,
                  color: '#374151',
                  marginBottom: '0.35rem'
                }}
              >
                Button Header Label
              </label>
              <input
                type="text"
                value={data.button_label !== undefined ? data.button_label : 'Execute Process'}
                onChange={(e) => {
                  updateNodeData('button_label', e.target.value);
                  updateNodeData('condition', `button_click == '${e.target.value}'`);
                }}
                placeholder="Execute Process"
                style={{
                  width: '100%',
                  padding: '0.45rem 0.65rem',
                  borderRadius: '6px',
                  border: '1px solid #cbd5e1',
                  fontSize: '0.775rem',
                  fontWeight: 600,
                  color: '#1e293b',
                  background: '#ffffff'
                }}
              />
            </div>
          </div>
        )}

        {/* Pop-up Confirmation Toast Box (Exact match from screenshot) */}
        {isToast && (
          <div
            style={{
              background: '#f0fdf4',
              border: '1px solid #bbf7d0',
              borderRadius: '10px',
              padding: '0.85rem'
            }}
          >
            <div style={{ fontSize: '0.725rem', fontWeight: 800, color: '#166534', marginBottom: '0.5rem' }}>
              Pop-up Confirmation Toast
            </div>

            <div style={{ marginBottom: '0.65rem' }}>
              <label style={{ display: 'block', fontSize: '0.6875rem', fontWeight: 700, color: '#374151', marginBottom: '0.25rem' }}>
                Toast Alert Message
              </label>
              <textarea
                rows={3}
                value={data.condition || 'Cargo logged. Gross weight exceeds 5,000 KG — automatically escalated to Logistics Approval.'}
                onChange={(e) => updateNodeData('condition', e.target.value)}
                style={{
                  width: '100%',
                  padding: '0.45rem 0.6rem',
                  borderRadius: '6px',
                  border: '1px solid #cbd5e1',
                  fontSize: '0.725rem',
                  background: '#ffffff',
                  color: '#1f2937',
                  resize: 'none'
                }}
              />
            </div>

            <div>
              <label style={{ display: 'block', fontSize: '0.6875rem', fontWeight: 700, color: '#374151', marginBottom: '0.25rem' }}>
                Tone / Color
              </label>
              <select
                value={data.tone || 'Warning (Amber)'}
                onChange={(e) => updateNodeData('tone', e.target.value)}
                style={{
                  width: '100%',
                  padding: '0.4rem 0.6rem',
                  borderRadius: '6px',
                  border: '1px solid #cbd5e1',
                  fontSize: '0.75rem',
                  background: '#ffffff',
                  color: '#1f2937'
                }}
              >
                <option value="Warning (Amber)">Warning (Amber)</option>
                <option value="Success (Green)">Success (Green)</option>
                <option value="Information (Blue)">Information (Blue)</option>
                <option value="Critical (Red)">Critical (Red)</option>
              </select>
            </div>
          </div>
        )}

        {/* If / Else Decision Rule Box (Exact match from screenshot) */}
        {isDecision && (
          <div
            style={{
              background: '#fffdf5',
              border: '1px solid #fde68a',
              borderRadius: '10px',
              padding: '0.85rem'
            }}
          >
            <div
              style={{
                fontSize: '0.775rem',
                fontWeight: 700,
                color: '#b45309',
                marginBottom: '0.65rem'
              }}
            >
              If / Else Decision Rule
            </div>

            {/* Target Evaluated Field */}
            <div style={{ marginBottom: '0.65rem' }}>
              <label
                style={{
                  display: 'block',
                  fontSize: '0.725rem',
                  fontWeight: 600,
                  color: '#374151',
                  marginBottom: '0.35rem'
                }}
              >
                Target Evaluated Field
              </label>
              <select
                value={data.target_field || 'Opportunity Amount (amount)'}
                onChange={(e) => {
                  const val = e.target.value;
                  const match = val.match(/\(([^)]+)\)/);
                  const fieldKey = match ? match[1] : 'amount';
                  const opKey = data.operator_key || 'greater_than';
                  const compVal = data.comparison_value !== undefined ? data.comparison_value : '50000';
                  updateNodeData('target_field', val);
                  updateNodeData('target_field_key', fieldKey);
                  updateNodeData('condition', `If ${fieldKey} ${opKey} "${compVal}"`);
                }}
                style={{
                  width: '100%',
                  padding: '0.45rem 0.65rem',
                  borderRadius: '6px',
                  border: '1px solid #cbd5e1',
                  fontSize: '0.775rem',
                  fontWeight: 500,
                  color: '#1e293b',
                  background: '#ffffff'
                }}
              >
                <option value="Opportunity Amount (amount)">Opportunity Amount (amount)</option>
                <option value="Deal Stage (stage)">Deal Stage (stage)</option>
                <option value="Discount % (discount)">Discount % (discount)</option>
                <option value="Expected Revenue (revenue)">Expected Revenue (revenue)</option>
                <option value="Probability % (probability)">Probability % (probability)</option>
                <option value="Lead Source (source)">Lead Source (source)</option>
                <option value="Account Industry (industry)">Account Industry (industry)</option>
                <option value="Cargo Weight (weight)">Cargo Weight (weight)</option>
              </select>
            </div>

            {/* Comparison Operator */}
            <div style={{ marginBottom: '0.65rem' }}>
              <label
                style={{
                  display: 'block',
                  fontSize: '0.725rem',
                  fontWeight: 600,
                  color: '#374151',
                  marginBottom: '0.35rem'
                }}
              >
                Comparison Operator
              </label>
              <select
                value={data.operator || 'Greater than (>)'}
                onChange={(e) => {
                  const val = e.target.value;
                  const opMap = {
                    'Greater than (>)': 'greater_than',
                    'Greater than or equal (>=)': 'greater_than_or_equal',
                    'Less than (<)': 'less_than',
                    'Less than or equal (<=)': 'less_than_or_equal',
                    'Equals (==)': 'equals',
                    'Not equals (!=)': 'not_equals',
                    'Contains text': 'contains',
                    'Is set (not empty)': 'is_set'
                  };
                  const opKey = opMap[val] || 'greater_than';
                  const fieldKey = data.target_field_key || 'amount';
                  const compVal = data.comparison_value !== undefined ? data.comparison_value : '50000';
                  updateNodeData('operator', val);
                  updateNodeData('operator_key', opKey);
                  updateNodeData('condition', `If ${fieldKey} ${opKey} "${compVal}"`);
                }}
                style={{
                  width: '100%',
                  padding: '0.45rem 0.65rem',
                  borderRadius: '6px',
                  border: '1px solid #cbd5e1',
                  fontSize: '0.775rem',
                  fontWeight: 500,
                  color: '#1e293b',
                  background: '#ffffff'
                }}
              >
                <option value="Greater than (>)">Greater than (&gt;)</option>
                <option value="Greater than or equal (>=)">Greater than or equal (&gt;=)</option>
                <option value="Less than (<)">Less than (&lt;)</option>
                <option value="Less than or equal (<=)">Less than or equal (&lt;=)</option>
                <option value="Equals (==)">Equals (==)</option>
                <option value="Not equals (!=)">Not equals (!=)</option>
                <option value="Contains text">Contains text</option>
                <option value="Is set (not empty)">Is set (not empty)</option>
              </select>
            </div>

            {/* Comparison Value */}
            <div>
              <label
                style={{
                  display: 'block',
                  fontSize: '0.725rem',
                  fontWeight: 600,
                  color: '#374151',
                  marginBottom: '0.35rem'
                }}
              >
                Comparison Value
              </label>
              <input
                type="text"
                value={data.comparison_value !== undefined ? data.comparison_value : '50000'}
                onChange={(e) => {
                  const val = e.target.value;
                  const fieldKey = data.target_field_key || 'amount';
                  const opKey = data.operator_key || 'greater_than';
                  updateNodeData('comparison_value', val);
                  updateNodeData('condition', `If ${fieldKey} ${opKey} "${val}"`);
                }}
                placeholder="50000"
                style={{
                  width: '100%',
                  padding: '0.45rem 0.65rem',
                  borderRadius: '6px',
                  border: '1px solid #cbd5e1',
                  fontSize: '0.775rem',
                  fontWeight: 500,
                  color: '#1e293b',
                  background: '#ffffff'
                }}
              />
            </div>
          </div>
        )}

        {/* Automatic Record Field Update Box (Exact match from screenshot) */}
        {isUpdate && (
          <div
            style={{
              background: '#f0fdf4',
              border: '1px solid #86efac',
              borderRadius: '10px',
              padding: '0.85rem'
            }}
          >
            {/* Header with Title & + Add Field button */}
            <div
              style={{
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'space-between',
                marginBottom: '0.35rem'
              }}
            >
              <div
                style={{
                  fontSize: '0.775rem',
                  fontWeight: 700,
                  color: '#166534'
                }}
              >
                Automatic Record Field Update
              </div>
              <button
                type="button"
                onClick={handleAddMutateField}
                style={{
                  display: 'inline-flex',
                  alignItems: 'center',
                  gap: '2px',
                  background: '#ffffff',
                  border: '1px solid #86efac',
                  color: '#166534',
                  borderRadius: '6px',
                  padding: '0.2rem 0.5rem',
                  fontSize: '0.6875rem',
                  fontWeight: 600,
                  cursor: 'pointer'
                }}
                className="hover:bg-emerald-50"
              >
                + Add Field
              </button>
            </div>

            {/* Subtitle / Helper text */}
            <p
              style={{
                fontSize: '0.7rem',
                color: '#166534',
                margin: '0 0 0.65rem 0',
                lineHeight: 1.35
              }}
            >
              Modify one or more fields on the current record. Use static text or click form variable tokens to map inputs dynamically.
            </p>

            {/* Field Mutation Cards */}
            <div style={{ display: 'flex', flexDirection: 'column', gap: '0.5rem' }}>
              {mutateFields.map((f, idx) => (
                <div
                  key={idx}
                  style={{
                    background: '#ffffff',
                    border: '1px solid #bbf7d0',
                    borderRadius: '8px',
                    padding: '0.65rem 0.75rem',
                    boxShadow: '0 1px 2px rgba(0,0,0,0.03)'
                  }}
                >
                  <div
                    style={{
                      display: 'flex',
                      alignItems: 'center',
                      justifyContent: 'space-between',
                      marginBottom: '0.35rem'
                    }}
                  >
                    <label
                      style={{
                        display: 'block',
                        fontSize: '0.7rem',
                        fontWeight: 600,
                        color: '#374151'
                      }}
                    >
                      Field #{idx + 1} to Mutate
                    </label>
                    {mutateFields.length > 1 && (
                      <button
                        type="button"
                        onClick={() => handleDeleteMutateField(idx)}
                        style={{
                          background: 'transparent',
                          border: 'none',
                          color: '#f87171',
                          cursor: 'pointer',
                          padding: '2px'
                        }}
                        title="Delete field"
                      >
                        <Trash2 size={13} />
                      </button>
                    )}
                  </div>

                  {/* Dropdown select */}
                  <select
                    value={f.field || 'Stage (stage)'}
                    onChange={(e) => handleUpdateMutateField(idx, 'field', e.target.value)}
                    style={{
                      width: '100%',
                      padding: '0.45rem 0.65rem',
                      borderRadius: '6px',
                      border: '1px solid #cbd5e1',
                      fontSize: '0.775rem',
                      color: '#1e293b',
                      background: '#ffffff'
                    }}
                  >
                    <option value="Stage (stage)">Stage (stage)</option>
                    <option value="Status (status)">Status (status)</option>
                    <option value="Deal Amount (amount)">Deal Amount (amount)</option>
                    <option value="Probability % (probability)">Probability % (probability)</option>
                    <option value="Next Step (next_step)">Next Step (next_step)</option>
                    <option value="Owner Assignment (owner_id)">Owner Assignment (owner_id)</option>
                    <option value="Approval State (approval_status)">Approval State (approval_status)</option>
                  </select>

                  {/* Target Value Input */}
                  <div style={{ marginTop: '0.45rem' }}>
                    <label
                      style={{
                        display: 'block',
                        fontSize: '0.65rem',
                        fontWeight: 600,
                        color: '#64748b',
                        marginBottom: '0.25rem'
                      }}
                    >
                      Target Value (Static or Dynamic Variable)
                    </label>
                    <input
                      type="text"
                      value={f.value !== undefined ? f.value : 'Approved'}
                      onChange={(e) => handleUpdateMutateField(idx, 'value', e.target.value)}
                      placeholder="Approved"
                      style={{
                        width: '100%',
                        padding: '0.45rem 0.65rem',
                        borderRadius: '6px',
                        border: '1px solid #cbd5e1',
                        fontSize: '0.775rem',
                        color: '#1e293b',
                        background: '#ffffff'
                      }}
                    />
                  </div>
                </div>
              ))}
            </div>
          </div>
        )}

        {/* Create Child Record / Task Settings (Exact match from screenshot) */}
        {isCreateRecord && (
          <div style={{ display: 'flex', flexDirection: 'column', gap: '0.85rem' }}>
            {/* Section Header: + Create Child Record / Task Settings */}
            <div
              style={{
                fontSize: '0.775rem',
                fontWeight: 700,
                color: '#059669',
                display: 'flex',
                alignItems: 'center',
                gap: '0.35rem',
                marginTop: '0.15rem'
              }}
            >
              <span style={{ fontSize: '0.9rem', lineHeight: 1 }}>+</span>
              <span>Create Child Record / Task Settings</span>
            </div>

            {/* Card 1: 1. Target Module & Association (Green Card) */}
            <div
              style={{
                background: '#f0fdf4',
                border: '1px solid #bbf7d0',
                borderRadius: '8px',
                padding: '0.75rem 0.85rem'
              }}
            >
              <div
                style={{
                  display: 'flex',
                  alignItems: 'center',
                  gap: '0.45rem',
                  marginBottom: '0.65rem'
                }}
              >
                <Layers size={15} style={{ color: '#059669' }} />
                <span
                  style={{
                    fontSize: '0.75rem',
                    fontWeight: 700,
                    color: '#166534'
                  }}
                >
                  1. Target Module &amp; Association
                </span>
              </div>

              {/* Target Module Dropdown */}
              <div style={{ marginBottom: '0.65rem' }}>
                <label
                  style={{
                    display: 'block',
                    fontSize: '0.7rem',
                    fontWeight: 600,
                    color: '#374151',
                    marginBottom: '0.3rem'
                  }}
                >
                  Target Module to Create
                </label>
                <select
                  value={data.target_module || 'Tasks & Action Items (tasks)'}
                  onChange={(e) => updateNodeData('target_module', e.target.value)}
                  style={{
                    width: '100%',
                    padding: '0.45rem 0.65rem',
                    borderRadius: '6px',
                    border: '1px solid #cbd5e1',
                    fontSize: '0.75rem',
                    fontWeight: 500,
                    color: '#1e293b',
                    background: '#ffffff'
                  }}
                >
                  <option value="Tasks & Action Items (tasks)">Tasks &amp; Action Items (tasks)</option>
                  <option value="Invoices & Billing (invoices)">Invoices &amp; Billing (invoices)</option>
                  <option value="Quotes & Commercial Estimates (quotes)">Quotes &amp; Commercial Estimates (quotes)</option>
                  <option value="Support Tickets (tickets)">Support Tickets (tickets)</option>
                  <option value="Activity Notes & Logs (notes)">Activity Notes &amp; Logs (notes)</option>
                </select>
              </div>

              {/* Checkbox: Automatically link child record to active parent */}
              <label
                style={{
                  display: 'flex',
                  alignItems: 'flex-start',
                  gap: '0.45rem',
                  cursor: 'pointer',
                  margin: 0
                }}
              >
                <input
                  type="checkbox"
                  checked={data.auto_link_parent !== false}
                  onChange={(e) => updateNodeData('auto_link_parent', e.target.checked)}
                  style={{
                    accentColor: '#059669',
                    marginTop: '2px',
                    cursor: 'pointer'
                  }}
                />
                <span
                  style={{
                    fontSize: '0.715rem',
                    color: '#374151',
                    fontWeight: 500,
                    lineHeight: 1.35
                  }}
                >
                  Automatically link child record to active parent (opportunities)
                </span>
              </label>
            </div>

            {/* Card 2: 2. Title & Task Guidance (Blue Card) */}
            <div
              style={{
                background: '#f0f9ff',
                border: '1px solid #bae6fd',
                borderRadius: '8px',
                padding: '0.75rem 0.85rem'
              }}
            >
              <div
                style={{
                  display: 'flex',
                  alignItems: 'center',
                  gap: '0.45rem',
                  marginBottom: '0.65rem'
                }}
              >
                <FileText size={15} style={{ color: '#0284c7' }} />
                <span
                  style={{
                    fontSize: '0.75rem',
                    fontWeight: 700,
                    color: '#0369a1'
                  }}
                >
                  2. Title &amp; Task Guidance
                </span>
              </div>

              {/* Task / Record Title * */}
              <div style={{ marginBottom: '0.65rem' }}>
                <label
                  style={{
                    display: 'block',
                    fontSize: '0.7rem',
                    fontWeight: 600,
                    color: '#374151',
                    marginBottom: '0.3rem'
                  }}
                >
                  Task / Record Title *
                </label>
                <input
                  type="text"
                  value={data.task_title !== undefined ? data.task_title : 'Follow-up Task: Process Action for {{name}}'}
                  onChange={(e) => updateNodeData('task_title', e.target.value)}
                  style={{
                    width: '100%',
                    padding: '0.45rem 0.65rem',
                    borderRadius: '6px',
                    border: '1px solid #cbd5e1',
                    fontSize: '0.75rem',
                    fontWeight: 500,
                    color: '#1e293b',
                    background: '#ffffff'
                  }}
                />
              </div>

              {/* Description & Actionable Guidance */}
              <div style={{ marginBottom: '0.65rem' }}>
                <label
                  style={{
                    display: 'block',
                    fontSize: '0.7rem',
                    fontWeight: 600,
                    color: '#374151',
                    marginBottom: '0.3rem'
                  }}
                >
                  Description &amp; Actionable Guidance
                </label>
                <textarea
                  rows={3}
                  value={
                    data.task_guidance !== undefined
                      ? data.task_guidance
                      : 'Automated task created by Process Studio workflow.\nPlease review and take appropriate action.'
                  }
                  onChange={(e) => updateNodeData('task_guidance', e.target.value)}
                  style={{
                    width: '100%',
                    padding: '0.45rem 0.65rem',
                    borderRadius: '6px',
                    border: '1px solid #cbd5e1',
                    fontSize: '0.715rem',
                    color: '#334155',
                    background: '#ffffff',
                    resize: 'none',
                    lineHeight: 1.35
                  }}
                />
              </div>

              {/* Insert Dynamic Merge Variables: */}
              <div>
                <label
                  style={{
                    display: 'block',
                    fontSize: '0.675rem',
                    fontWeight: 600,
                    color: '#0284c7',
                    marginBottom: '0.35rem'
                  }}
                >
                  Insert Dynamic Merge Variables:
                </label>
                <div style={{ display: 'flex', flexWrap: 'wrap', gap: '0.35rem' }}>
                  {['+ {{name}}', '+ {{amount}}', '+ {{stage}}', '+ {{company}}'].map((v) => {
                    const cleanVar = v.replace('+', '').trim();
                    return (
                      <button
                        key={v}
                        type="button"
                        onClick={() => {
                          const curr = data.task_title !== undefined ? data.task_title : 'Follow-up Task: Process Action for {{name}}';
                          updateNodeData('task_title', `${curr} ${cleanVar}`);
                        }}
                        style={{
                          background: '#e0f2fe',
                          border: '1px solid #7dd3fc',
                          color: '#0284c7',
                          borderRadius: '6px',
                          padding: '2px 8px',
                          fontSize: '0.6875rem',
                          fontWeight: 600,
                          cursor: 'pointer',
                          lineHeight: '1.4'
                        }}
                        className="hover:bg-sky-100"
                        title={`Append ${cleanVar} to Title`}
                      >
                        {v}
                      </button>
                    );
                  })}
                </div>
              </div>
            </div>

            {/* Card 3: 3. Assignment, Priority & Due Date (Amber Card) */}
            <div
              style={{
                background: '#fffbeb',
                border: '1px solid #fde68a',
                borderRadius: '8px',
                padding: '0.75rem 0.85rem'
              }}
            >
              <div
                style={{
                  display: 'flex',
                  alignItems: 'center',
                  gap: '0.45rem',
                  marginBottom: '0.65rem'
                }}
              >
                <Clock size={15} style={{ color: '#d97706' }} />
                <span
                  style={{
                    fontSize: '0.75rem',
                    fontWeight: 700,
                    color: '#92400e'
                  }}
                >
                  3. Assignment, Priority &amp; Due Date
                </span>
              </div>

              {/* Two columns: Priority Level and Due Date Offset */}
              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '0.5rem', marginBottom: '0.65rem' }}>
                <div>
                  <label
                    style={{
                      display: 'block',
                      fontSize: '0.7rem',
                      fontWeight: 600,
                      color: '#374151',
                      marginBottom: '0.3rem'
                    }}
                  >
                    Priority Level
                  </label>
                  <select
                    value={data.priority || 'High Priority'}
                    onChange={(e) => updateNodeData('priority', e.target.value)}
                    style={{
                      width: '100%',
                      padding: '0.45rem 0.55rem',
                      borderRadius: '6px',
                      border: '1px solid #cbd5e1',
                      fontSize: '0.725rem',
                      fontWeight: 500,
                      color: '#1e293b',
                      background: '#ffffff'
                    }}
                  >
                    <option value="High Priority">High Priority</option>
                    <option value="Urgent Priority">Urgent Priority</option>
                    <option value="Medium Priority">Medium Priority</option>
                    <option value="Low Priority">Low Priority</option>
                  </select>
                </div>

                <div>
                  <label
                    style={{
                      display: 'block',
                      fontSize: '0.7rem',
                      fontWeight: 600,
                      color: '#374151',
                      marginBottom: '0.3rem'
                    }}
                  >
                    Due Date Offset
                  </label>
                  <select
                    value={data.due_date_offset || '+1 Business Day'}
                    onChange={(e) => updateNodeData('due_date_offset', e.target.value)}
                    style={{
                      width: '100%',
                      padding: '0.45rem 0.55rem',
                      borderRadius: '6px',
                      border: '1px solid #cbd5e1',
                      fontSize: '0.725rem',
                      fontWeight: 500,
                      color: '#1e293b',
                      background: '#ffffff'
                    }}
                  >
                    <option value="+1 Business Day">+1 Business Day</option>
                    <option value="Immediate (Same Day)">Immediate (Same Day)</option>
                    <option value="+2 Business Days">+2 Business Days</option>
                    <option value="+3 Business Days">+3 Business Days</option>
                    <option value="+1 Week">+1 Week</option>
                  </select>
                </div>
              </div>

              {/* Assign Task To */}
              <div>
                <label
                  style={{
                    display: 'block',
                    fontSize: '0.7rem',
                    fontWeight: 600,
                    color: '#374151',
                    marginBottom: '0.3rem'
                  }}
                >
                  Assign Task To
                </label>
                <select
                  value={data.assign_to || 'Assigned Owner of Current Record'}
                  onChange={(e) => updateNodeData('assign_to', e.target.value)}
                  style={{
                    width: '100%',
                    padding: '0.45rem 0.65rem',
                    borderRadius: '6px',
                    border: '1px solid #cbd5e1',
                    fontSize: '0.75rem',
                    fontWeight: 500,
                    color: '#1e293b',
                    background: '#ffffff'
                  }}
                >
                  <option value="Assigned Owner of Current Record">Assigned Owner of Current Record</option>
                  <option value="Process Initiator / Current User">Process Initiator / Current User</option>
                  <option value="Sales Team Queue">Sales Team Queue</option>
                  <option value="Specific Administrator">Specific Administrator</option>
                </select>
              </div>
            </div>
          </div>
        )}

        {/* Send Email Configuration (Exact match from screenshot) */}
        {isEmail && (
          <div style={{ display: 'flex', flexDirection: 'column', gap: '0.85rem' }}>
            {/* Section Header: ✉ Send Email Configuration */}
            <div
              style={{
                fontSize: '0.775rem',
                fontWeight: 700,
                color: '#059669',
                display: 'flex',
                alignItems: 'center',
                gap: '0.4rem',
                marginTop: '0.15rem'
              }}
            >
              <Mail size={15} style={{ color: '#059669' }} />
              <span>Send Email Configuration</span>
            </div>

            {/* Card 1: 1. WHOM TO SEND? (Recipient Target) [Green Card] */}
            <div
              style={{
                background: '#f0fdf4',
                border: '1px solid #bbf7d0',
                borderRadius: '8px',
                padding: '0.75rem 0.85rem'
              }}
            >
              <div
                style={{
                  display: 'flex',
                  alignItems: 'center',
                  gap: '0.45rem',
                  marginBottom: '0.65rem'
                }}
              >
                <UserCheck size={15} style={{ color: '#059669' }} />
                <span
                  style={{
                    fontSize: '0.75rem',
                    fontWeight: 700,
                    color: '#166534'
                  }}
                >
                  1. WHOM TO SEND? (Recipient Target)
                </span>
              </div>

              {/* Recipient Source Dropdown */}
              <div style={{ marginBottom: '0.65rem' }}>
                <label
                  style={{
                    display: 'block',
                    fontSize: '0.7rem',
                    fontWeight: 600,
                    color: '#374151',
                    marginBottom: '0.3rem'
                  }}
                >
                  Recipient Source
                </label>
                <select
                  value={data.recipient_source || 'Contact / Customer on Record (Dynamic)'}
                  onChange={(e) => updateNodeData('recipient_source', e.target.value)}
                  style={{
                    width: '100%',
                    padding: '0.45rem 0.65rem',
                    borderRadius: '6px',
                    border: '1px solid #cbd5e1',
                    fontSize: '0.75rem',
                    fontWeight: 500,
                    color: '#1e293b',
                    background: '#ffffff'
                  }}
                >
                  <option value="Contact / Customer on Record (Dynamic)">Contact / Customer on Record (Dynamic)</option>
                  <option value="Assigned Record Owner">Assigned Record Owner</option>
                  <option value="Specific Static Email Address">Specific Static Email Address</option>
                  <option value="Custom Field Email (Dynamic)">Custom Field Email (Dynamic)</option>
                </select>
              </div>

              {/* Record Email Field Dropdown */}
              <div style={{ marginBottom: '0.65rem' }}>
                <label
                  style={{
                    display: 'block',
                    fontSize: '0.7rem',
                    fontWeight: 600,
                    color: '#374151',
                    marginBottom: '0.3rem'
                  }}
                >
                  Record Email Field
                </label>
                <select
                  value={data.record_email_field || 'Primary Email (email)'}
                  onChange={(e) => updateNodeData('record_email_field', e.target.value)}
                  style={{
                    width: '100%',
                    padding: '0.45rem 0.65rem',
                    borderRadius: '6px',
                    border: '1px solid #cbd5e1',
                    fontSize: '0.75rem',
                    fontWeight: 500,
                    color: '#1e293b',
                    background: '#ffffff'
                  }}
                >
                  <option value="Primary Email (email)">Primary Email (email)</option>
                  <option value="Billing Contact Email (billing_email)">Billing Contact Email (billing_email)</option>
                  <option value="Secondary Email (alt_email)">Secondary Email (alt_email)</option>
                </select>
              </div>

              {/* CC and BCC optional inputs */}
              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '0.5rem' }}>
                <div>
                  <label
                    style={{
                      display: 'block',
                      fontSize: '0.7rem',
                      fontWeight: 600,
                      color: '#374151',
                      marginBottom: '0.3rem'
                    }}
                  >
                    CC (Optional)
                  </label>
                  <input
                    type="text"
                    value={data.cc_email || ''}
                    placeholder="e.g. team@co.com"
                    onChange={(e) => updateNodeData('cc_email', e.target.value)}
                    style={{
                      width: '100%',
                      padding: '0.45rem 0.55rem',
                      borderRadius: '6px',
                      border: '1px solid #cbd5e1',
                      fontSize: '0.725rem',
                      fontWeight: 500,
                      color: '#1e293b',
                      background: '#ffffff'
                    }}
                  />
                </div>
                <div>
                  <label
                    style={{
                      display: 'block',
                      fontSize: '0.7rem',
                      fontWeight: 600,
                      color: '#374151',
                      marginBottom: '0.3rem'
                    }}
                  >
                    BCC (Optional)
                  </label>
                  <input
                    type="text"
                    value={data.bcc_email || ''}
                    placeholder="e.g. audit@co.com"
                    onChange={(e) => updateNodeData('bcc_email', e.target.value)}
                    style={{
                      width: '100%',
                      padding: '0.45rem 0.55rem',
                      borderRadius: '6px',
                      border: '1px solid #cbd5e1',
                      fontSize: '0.725rem',
                      fontWeight: 500,
                      color: '#1e293b',
                      background: '#ffffff'
                    }}
                  />
                </div>
              </div>
            </div>

            {/* Card 2: 2. WHAT TO SEND? (Content & Template) [Blue Card] */}
            <div
              style={{
                background: '#f0f9ff',
                border: '1px solid #bae6fd',
                borderRadius: '8px',
                padding: '0.75rem 0.85rem'
              }}
            >
              <div
                style={{
                  display: 'flex',
                  alignItems: 'center',
                  gap: '0.45rem',
                  marginBottom: '0.65rem'
                }}
              >
                <FileText size={15} style={{ color: '#0284c7' }} />
                <span
                  style={{
                    fontSize: '0.75rem',
                    fontWeight: 700,
                    color: '#0369a1'
                  }}
                >
                  2. WHAT TO SEND? (Content &amp; Template)
                </span>
              </div>

              {/* Choose Existing Email Template */}
              <div style={{ marginBottom: '0.65rem' }}>
                <label
                  style={{
                    display: 'block',
                    fontSize: '0.7rem',
                    fontWeight: 600,
                    color: '#374151',
                    marginBottom: '0.3rem'
                  }}
                >
                  Choose Existing Email Template
                </label>
                <select
                  value={data.email_template || '-- Custom Subject & Body (Manual) -'}
                  onChange={(e) => updateNodeData('email_template', e.target.value)}
                  style={{
                    width: '100%',
                    padding: '0.45rem 0.65rem',
                    borderRadius: '6px',
                    border: '1px solid #cbd5e1',
                    fontSize: '0.75rem',
                    fontWeight: 500,
                    color: '#1e293b',
                    background: '#ffffff'
                  }}
                >
                  <option value="-- Custom Subject & Body (Manual) -">-- Custom Subject &amp; Body (Manual) -</option>
                  <option value="Deal Won - Client Welcome Notification">Deal Won - Client Welcome Notification</option>
                  <option value="Approval Request Reminder">Approval Request Reminder</option>
                  <option value="Payment Overdue Notice">Payment Overdue Notice</option>
                  <option value="Commercial Proposal Summary">Commercial Proposal Summary</option>
                </select>
              </div>

              {/* Subject Line * */}
              <div style={{ marginBottom: '0.65rem' }}>
                <label
                  style={{
                    display: 'block',
                    fontSize: '0.7rem',
                    fontWeight: 600,
                    color: '#374151',
                    marginBottom: '0.3rem'
                  }}
                >
                  Subject Line *
                </label>
                <input
                  type="text"
                  value={data.subject !== undefined ? data.subject : 'Notification: Update regarding {{name}}'}
                  onChange={(e) => updateNodeData('subject', e.target.value)}
                  style={{
                    width: '100%',
                    padding: '0.45rem 0.65rem',
                    borderRadius: '6px',
                    border: '1px solid #cbd5e1',
                    fontSize: '0.75rem',
                    fontWeight: 500,
                    color: '#1e293b',
                    background: '#ffffff'
                  }}
                />
              </div>

              {/* Message Body */}
              <div style={{ marginBottom: '0.65rem' }}>
                <label
                  style={{
                    display: 'block',
                    fontSize: '0.7rem',
                    fontWeight: 600,
                    color: '#374151',
                    marginBottom: '0.3rem'
                  }}
                >
                  Message Body
                </label>
                <textarea
                  rows={4}
                  value={
                    data.body !== undefined
                      ? data.body
                      : 'Hello {{contact_name}},\n\nThis is an automated notification regarding your recent request.'
                  }
                  onChange={(e) => updateNodeData('body', e.target.value)}
                  style={{
                    width: '100%',
                    padding: '0.45rem 0.65rem',
                    borderRadius: '6px',
                    border: '1px solid #cbd5e1',
                    fontSize: '0.715rem',
                    color: '#334155',
                    background: '#ffffff',
                    resize: 'none',
                    lineHeight: 1.35
                  }}
                />
              </div>

              {/* Insert Dynamic Merge Variables */}
              <div>
                <label
                  style={{
                    display: 'block',
                    fontSize: '0.675rem',
                    fontWeight: 600,
                    color: '#0284c7',
                    marginBottom: '0.35rem'
                  }}
                >
                  Insert Dynamic Merge Variables:
                </label>
                <div style={{ display: 'flex', flexWrap: 'wrap', gap: '0.35rem' }}>
                  {['+ {{name}}', '+ {{amount}}', '+ {{stage}}', '+ {{status}}', '+ {{company}}'].map((v) => {
                    const cleanVar = v.replace('+', '').trim();
                    return (
                      <button
                        key={v}
                        type="button"
                        onClick={() => {
                          const curr = data.subject !== undefined ? data.subject : 'Notification: Update regarding {{name}}';
                          updateNodeData('subject', `${curr} ${cleanVar}`);
                        }}
                        style={{
                          background: '#e0f2fe',
                          border: '1px solid #7dd3fc',
                          color: '#0284c7',
                          borderRadius: '6px',
                          padding: '2px 8px',
                          fontSize: '0.6875rem',
                          fontWeight: 600,
                          cursor: 'pointer',
                          lineHeight: '1.4'
                        }}
                        className="hover:bg-sky-100"
                        title={`Append ${cleanVar} to Subject`}
                      >
                        {v}
                      </button>
                    );
                  })}
                </div>
              </div>
            </div>

            {/* Card 3: 3. WHEN TO SEND? (Timing & Trigger Mode) [Amber Card] */}
            <div
              style={{
                background: '#fffbeb',
                border: '1px solid #fde68a',
                borderRadius: '8px',
                padding: '0.75rem 0.85rem'
              }}
            >
              <div
                style={{
                  display: 'flex',
                  alignItems: 'center',
                  gap: '0.45rem',
                  marginBottom: '0.65rem'
                }}
              >
                <Clock size={15} style={{ color: '#d97706' }} />
                <span
                  style={{
                    fontSize: '0.75rem',
                    fontWeight: 700,
                    color: '#92400e'
                  }}
                >
                  3. WHEN TO SEND? (Timing &amp; Trigger Mode)
                </span>
              </div>

              {/* Send Timing */}
              <div style={{ marginBottom: '0.65rem' }}>
                <label
                  style={{
                    display: 'block',
                    fontSize: '0.7rem',
                    fontWeight: 600,
                    color: '#374151',
                    marginBottom: '0.3rem'
                  }}
                >
                  Send Timing
                </label>
                <select
                  value={data.send_timing || 'Immediately (Instant trigger upon step)'}
                  onChange={(e) => updateNodeData('send_timing', e.target.value)}
                  style={{
                    width: '100%',
                    padding: '0.45rem 0.65rem',
                    borderRadius: '6px',
                    border: '1px solid #cbd5e1',
                    fontSize: '0.75rem',
                    fontWeight: 500,
                    color: '#1e293b',
                    background: '#ffffff'
                  }}
                >
                  <option value="Immediately (Instant trigger upon step)">Immediately (Instant trigger upon step)</option>
                  <option value="Delay 15 Minutes">Delay 15 Minutes</option>
                  <option value="Delay 1 Hour">Delay 1 Hour</option>
                  <option value="Delay 1 Business Day">Delay 1 Business Day</option>
                </select>
              </div>

              {/* Execution Mode */}
              <div>
                <label
                  style={{
                    display: 'block',
                    fontSize: '0.7rem',
                    fontWeight: 600,
                    color: '#374151',
                    marginBottom: '0.3rem'
                  }}
                >
                  Execution Mode
                </label>
                <select
                  value={data.execution_mode || 'Automated Silent Dispatch (Background)'}
                  onChange={(e) => updateNodeData('execution_mode', e.target.value)}
                  style={{
                    width: '100%',
                    padding: '0.45rem 0.65rem',
                    borderRadius: '6px',
                    border: '1px solid #cbd5e1',
                    fontSize: '0.75rem',
                    fontWeight: 500,
                    color: '#1e293b',
                    background: '#ffffff'
                  }}
                >
                  <option value="Automated Silent Dispatch (Background)">Automated Silent Dispatch (Background)</option>
                  <option value="Manual Review before Dispatch">Manual Review before Dispatch</option>
                  <option value="Draft in Outlook / Gmail">Draft in Outlook / Gmail</option>
                </select>
              </div>
            </div>

            {/* Card 4: 4. HOW TO SEND? (Sender & Delivery Settings) [Purple Card] */}
            <div
              style={{
                background: '#faf5ff',
                border: '1px solid #e9d5ff',
                borderRadius: '8px',
                padding: '0.75rem 0.85rem'
              }}
            >
              <div
                style={{
                  display: 'flex',
                  alignItems: 'center',
                  gap: '0.45rem',
                  marginBottom: '0.65rem'
                }}
              >
                <Send size={15} style={{ color: '#7e22ce' }} />
                <span
                  style={{
                    fontSize: '0.75rem',
                    fontWeight: 700,
                    color: '#6b21a8'
                  }}
                >
                  4. HOW TO SEND? (Sender &amp; Delivery Settings)
                </span>
              </div>

              {/* Send From Mailbox */}
              <div style={{ marginBottom: '0.65rem' }}>
                <label
                  style={{
                    display: 'block',
                    fontSize: '0.7rem',
                    fontWeight: 600,
                    color: '#374151',
                    marginBottom: '0.3rem'
                  }}
                >
                  Send From Mailbox
                </label>
                <select
                  value={data.send_from_mailbox || 'System Mailer (no-reply@company.com)'}
                  onChange={(e) => updateNodeData('send_from_mailbox', e.target.value)}
                  style={{
                    width: '100%',
                    padding: '0.45rem 0.65rem',
                    borderRadius: '6px',
                    border: '1px solid #cbd5e1',
                    fontSize: '0.75rem',
                    fontWeight: 500,
                    color: '#1e293b',
                    background: '#ffffff'
                  }}
                >
                  <option value="System Mailer (no-reply@company.com)">System Mailer (no-reply@company.com)</option>
                  <option value="Assigned User Mailbox (OAuth Connected)">Assigned User Mailbox (OAuth Connected)</option>
                  <option value="Sales Desk (sales@company.com)">Sales Desk (sales@company.com)</option>
                  <option value="Customer Support (support@company.com)">Customer Support (support@company.com)</option>
                </select>
              </div>

              {/* Checkboxes */}
              <div style={{ display: 'flex', flexDirection: 'column', gap: '0.45rem' }}>
                <label
                  style={{
                    display: 'flex',
                    alignItems: 'flex-start',
                    gap: '0.45rem',
                    cursor: 'pointer',
                    margin: 0
                  }}
                >
                  <input
                    type="checkbox"
                    checked={data.high_priority_delivery === true}
                    onChange={(e) => updateNodeData('high_priority_delivery', e.target.checked)}
                    style={{
                      accentColor: '#7e22ce',
                      marginTop: '2px',
                      cursor: 'pointer'
                    }}
                  />
                  <span
                    style={{
                      fontSize: '0.715rem',
                      color: '#374151',
                      fontWeight: 500,
                      lineHeight: 1.35
                    }}
                  >
                    High Priority Delivery Flag (Urgent marker)
                  </span>
                </label>

                <label
                  style={{
                    display: 'flex',
                    alignItems: 'flex-start',
                    gap: '0.45rem',
                    cursor: 'pointer',
                    margin: 0
                  }}
                >
                  <input
                    type="checkbox"
                    checked={data.attach_flow_pdf === true}
                    onChange={(e) => updateNodeData('attach_flow_pdf', e.target.checked)}
                    style={{
                      accentColor: '#7e22ce',
                      marginTop: '2px',
                      cursor: 'pointer'
                    }}
                  />
                  <span
                    style={{
                      fontSize: '0.715rem',
                      color: '#374151',
                      fontWeight: 500,
                      lineHeight: 1.35
                    }}
                  >
                    Attach Automated Flow Summary &amp; Line-Items PDF
                  </span>
                </label>
              </div>
            </div>
          </div>
        )}

        {/* Pop-up Confirmation Toast (Exact match from screenshot) */}
        {isToast && (
          <div
            style={{
              background: '#f0fdf4',
              border: '1px solid #bbf7d0',
              borderRadius: '8px',
              padding: '0.75rem 0.85rem'
            }}
          >
            <div
              style={{
                fontSize: '0.75rem',
                fontWeight: 700,
                color: '#166534',
                marginBottom: '0.65rem'
              }}
            >
              Pop-up Confirmation Toast
            </div>

            {/* Toast Alert Message */}
            <div style={{ marginBottom: '0.65rem' }}>
              <label
                style={{
                  display: 'block',
                  fontSize: '0.7rem',
                  fontWeight: 600,
                  color: '#374151',
                  marginBottom: '0.35rem'
                }}
              >
                Toast Alert Message
              </label>
              <textarea
                rows={2}
                value={
                  data.toast_message !== undefined
                    ? data.toast_message
                    : (data.condition || 'Business process step completed successfully.')
                }
                onChange={(e) => {
                  updateNodeData('toast_message', e.target.value);
                  updateNodeData('condition', e.target.value);
                }}
                style={{
                  width: '100%',
                  padding: '0.45rem 0.65rem',
                  borderRadius: '6px',
                  border: '1px solid #cbd5e1',
                  fontSize: '0.75rem',
                  color: '#1e293b',
                  background: '#ffffff',
                  resize: 'none',
                  lineHeight: 1.35
                }}
              />
            </div>

            {/* Tone / Color */}
            <div>
              <label
                style={{
                  display: 'block',
                  fontSize: '0.7rem',
                  fontWeight: 600,
                  color: '#374151',
                  marginBottom: '0.35rem'
                }}
              >
                Tone / Color
              </label>
              <select
                value={data.toast_tone || 'Success (Green)'}
                onChange={(e) => updateNodeData('toast_tone', e.target.value)}
                style={{
                  width: '100%',
                  padding: '0.45rem 0.65rem',
                  borderRadius: '6px',
                  border: '1px solid #cbd5e1',
                  fontSize: '0.75rem',
                  fontWeight: 500,
                  color: '#1e293b',
                  background: '#ffffff'
                }}
              >
                <option value="Success (Green)">Success (Green)</option>
                <option value="Warning (Amber)">Warning (Amber)</option>
                <option value="Error (Red)">Error (Red)</option>
                <option value="Information (Blue)">Information (Blue)</option>
              </select>
            </div>
          </div>
        )}

        {/* Lock / Unlock Record Configuration (Exact match from screenshot) */}
        {isLock && (
          <div style={{ display: 'flex', flexDirection: 'column', gap: '0.85rem' }}>
            <div
              style={{
                fontSize: '0.78rem',
                fontWeight: 700,
                color: '#059669',
                display: 'flex',
                alignItems: 'center',
                gap: '0.4rem',
                marginTop: '0.15rem'
              }}
            >
              <Lock size={15} color="#059669" />
              <span>Lock / Unlock Record Configuration</span>
            </div>

            {/* 1. Lock Action & Behavior */}
            <div
              style={{
                background: '#f0fdf4',
                border: '1px solid #bbf7d0',
                borderRadius: '8px',
                padding: '0.75rem 0.85rem'
              }}
            >
              <div
                style={{
                  fontSize: '0.725rem',
                  fontWeight: 700,
                  color: '#166534',
                  marginBottom: '0.55rem',
                  display: 'flex',
                  alignItems: 'center',
                  gap: '6px'
                }}
              >
                <span>1. Lock Action &amp; Behavior</span>
              </div>

              <div>
                <label
                  style={{
                    display: 'block',
                    fontSize: '0.7rem',
                    fontWeight: 600,
                    color: '#374151',
                    marginBottom: '0.35rem'
                  }}
                >
                  Record Lock Action
                </label>
                <select
                  value={data.lock_action || '🔒 Enforce Edit Lock (Make Record Read-Only)'}
                  onChange={(e) => {
                    const val = e.target.value;
                    updateNodeData('lock_action', val);
                    const label = val.includes('Release') ? 'Release Edit Lock' : 'Enforce Edit Lock';
                    updateNodeData('lock_action_label', label);
                  }}
                  style={{
                    width: '100%',
                    padding: '0.45rem 0.65rem',
                    borderRadius: '6px',
                    border: '1px solid #cbd5e1',
                    fontSize: '0.75rem',
                    fontWeight: 600,
                    color: '#1e293b',
                    background: '#ffffff'
                  }}
                >
                  <option value="🔒 Enforce Edit Lock (Make Record Read-Only)">🔒 Enforce Edit Lock (Make Record Read-Only)</option>
                  <option value="🔓 Release Edit Lock (Unlock Record)">🔓 Release Edit Lock (Unlock Record)</option>
                  <option value="⏱️ Temporary Lock (Pending Review)">⏱️ Temporary Lock (Pending Review)</option>
                </select>
              </div>

              <div
                style={{
                  marginTop: '0.6rem',
                  border: '1px dashed #86efac',
                  borderRadius: '6px',
                  padding: '0.55rem 0.65rem',
                  background: 'rgba(255, 255, 255, 0.7)',
                  color: '#15803d',
                  fontSize: '0.6875rem',
                  lineHeight: '1.4'
                }}
              >
                Lock Mode: Prevents standard users and reps from modifying fields, changing pipeline stages, or deleting line-items until an approved unlock step or admin intervention occurs.
              </div>
            </div>

            {/* 2. Permissions & Bypass Authority */}
            <div
              style={{
                background: '#fffbeb',
                border: '1px solid #fde68a',
                borderRadius: '8px',
                padding: '0.75rem 0.85rem'
              }}
            >
              <div
                style={{
                  fontSize: '0.725rem',
                  fontWeight: 700,
                  color: '#92400e',
                  marginBottom: '0.55rem',
                  display: 'flex',
                  alignItems: 'center',
                  gap: '6px'
                }}
              >
                <UserCheck size={13} style={{ color: '#d97706' }} />
                <span>2. Permissions &amp; Bypass Authority</span>
              </div>

              <div>
                <label
                  style={{
                    display: 'block',
                    fontSize: '0.7rem',
                    fontWeight: 600,
                    color: '#374151',
                    marginBottom: '0.35rem'
                  }}
                >
                  Who can edit while locked?
                </label>
                <select
                  value={data.bypass_authority || 'System Administrators Only'}
                  onChange={(e) => {
                    const val = e.target.value;
                    updateNodeData('bypass_authority', val);
                    const badge = val.includes('Owner') ? 'Owner & Admins' : (val.includes('Nobody') ? 'Nobody' : 'Admin Only');
                    updateNodeData('bypass_authority_badge', badge);
                  }}
                  style={{
                    width: '100%',
                    padding: '0.45rem 0.65rem',
                    borderRadius: '6px',
                    border: '1px solid #cbd5e1',
                    fontSize: '0.75rem',
                    fontWeight: 500,
                    color: '#1e293b',
                    background: '#ffffff'
                  }}
                >
                  <option value="System Administrators Only">System Administrators Only</option>
                  <option value="Record Owner & System Administrators">Record Owner & System Administrators</option>
                  <option value="Approvers & System Administrators">Approvers & System Administrators</option>
                  <option value="Nobody (Strict Freeze)">Nobody (Strict Freeze)</option>
                </select>
              </div>

              <label
                style={{
                  display: 'flex',
                  alignItems: 'center',
                  gap: '0.45rem',
                  cursor: 'pointer',
                  marginTop: '0.6rem',
                  marginBottom: 0
                }}
              >
                <input
                  type="checkbox"
                  checked={data.strict_lock !== undefined ? data.strict_lock : true}
                  onChange={(e) => updateNodeData('strict_lock', e.target.checked)}
                  style={{
                    accentColor: '#2563eb',
                    cursor: 'pointer'
                  }}
                />
                <span
                  style={{
                    fontSize: '0.715rem',
                    color: '#1e293b',
                    fontWeight: 600,
                    lineHeight: 1.35
                  }}
                >
                  Strict Lock: Block pipeline stage movements
                </span>
              </label>
            </div>

            {/* 3. Lock Banner & User Notice */}
            <div
              style={{
                background: '#f8fafc',
                border: '1px solid #e2e8f0',
                borderRadius: '8px',
                padding: '0.75rem 0.85rem'
              }}
            >
              <div
                style={{
                  fontSize: '0.725rem',
                  fontWeight: 700,
                  color: '#334155',
                  marginBottom: '0.55rem',
                  display: 'flex',
                  alignItems: 'center',
                  gap: '6px'
                }}
              >
                <AlertCircle size={13} style={{ color: '#64748b' }} />
                <span>3. Lock Banner &amp; User Notice</span>
              </div>

              <div style={{ marginBottom: '0.55rem' }}>
                <label
                  style={{
                    display: 'block',
                    fontSize: '0.7rem',
                    fontWeight: 600,
                    color: '#374151',
                    marginBottom: '0.35rem'
                  }}
                >
                  Notice Banner Style
                </label>
                <select
                  value={data.notice_banner_style || 'Amber Warning ("Record Locked Pending Review")'}
                  onChange={(e) => updateNodeData('notice_banner_style', e.target.value)}
                  style={{
                    width: '100%',
                    padding: '0.45rem 0.65rem',
                    borderRadius: '6px',
                    border: '1px solid #cbd5e1',
                    fontSize: '0.75rem',
                    fontWeight: 500,
                    color: '#1e293b',
                    background: '#ffffff'
                  }}
                >
                  <option value="Amber Warning (&quot;Record Locked Pending Review&quot;)">Amber Warning ("Record Locked Pending Review")</option>
                  <option value="Red Danger (&quot;Record Frozen - Read Only&quot;)">Red Danger ("Record Frozen - Read Only")</option>
                  <option value="Blue Information (&quot;Locked for Processing&quot;)">Blue Information ("Locked for Processing")</option>
                  <option value="Silent (No Banner Notification)">Silent (No Banner Notification)</option>
                </select>
              </div>

              <div>
                <label
                  style={{
                    display: 'block',
                    fontSize: '0.7rem',
                    fontWeight: 600,
                    color: '#374151',
                    marginBottom: '0.35rem'
                  }}
                >
                  Lock Reason / Notice Message
                </label>
                <textarea
                  rows={3}
                  value={
                    data.notice_message !== undefined
                      ? data.notice_message
                      : 'This record is locked pending executive/process review. Contact your system administrator to request changes.'
                  }
                  onChange={(e) => updateNodeData('notice_message', e.target.value)}
                  style={{
                    width: '100%',
                    padding: '0.45rem 0.65rem',
                    borderRadius: '6px',
                    border: '1px solid #cbd5e1',
                    fontSize: '0.725rem',
                    color: '#334155',
                    background: '#ffffff',
                    resize: 'none',
                    lineHeight: 1.35
                  }}
                />
              </div>
            </div>
          </div>
        )}

        {/* Webhook Configuration Box */}
        {isWebhook && (
          <div
            style={{
              background: '#f5f3ff',
              border: '1px solid #ddd6fe',
              borderRadius: '10px',
              padding: '0.85rem'
            }}
          >
            <div style={{ fontSize: '0.725rem', fontWeight: 800, color: '#581c87', marginBottom: '0.5rem' }}>
              REST API Dispatch Settings
            </div>

            <div style={{ marginBottom: '0.5rem' }}>
              <label style={{ display: 'block', fontSize: '0.6875rem', fontWeight: 700, color: '#4c1d95', marginBottom: '0.25rem' }}>
                Endpoint URL
              </label>
              <input
                type="text"
                value={data.condition || 'POST /api/erp/dispatch'}
                onChange={(e) => updateNodeData('condition', e.target.value)}
                style={{
                  width: '100%',
                  padding: '0.4rem 0.6rem',
                  borderRadius: '6px',
                  border: '1px solid #ddd6fe',
                  fontSize: '0.725rem',
                  fontFamily: 'monospace',
                  background: '#ffffff'
                }}
              />
            </div>
            <div style={{ fontSize: '0.675rem', color: '#6d28d9' }}>
              Payload: <code>JSON (PO, SO, Amount, Vendor, Timestamp)</code>
            </div>
          </div>
        )}

        {/* Multi-Tier Approval Step Config Box */}
        {isApproval && (
          <div
            style={{
              background: '#faf5ff',
              border: '1px solid #d8b4fe',
              borderRadius: '10px',
              padding: '0.85rem'
            }}
          >
            <div
              style={{
                fontSize: '0.775rem',
                fontWeight: 700,
                color: '#7c3aed',
                marginBottom: '0.65rem',
                display: 'flex',
                alignItems: 'center',
                gap: '0.4rem'
              }}
            >
              <UserCheck size={14} color="#7c3aed" />
              <span>Approval Step Governance</span>
            </div>

            {/* Step Badge / Hierarchy Level */}
            <div style={{ marginBottom: '0.65rem' }}>
              <label
                style={{
                  display: 'block',
                  fontSize: '0.7rem',
                  fontWeight: 600,
                  color: '#374151',
                  marginBottom: '0.35rem'
                }}
              >
                Approval Step Badge / Level
              </label>
              <input
                type="text"
                value={data.stepBadge !== undefined ? data.stepBadge : 'STEP 1: MANAGER / ADMIN'}
                onChange={(e) => updateNodeData('stepBadge', e.target.value)}
                placeholder="STEP 1: MANAGER / ADMIN"
                style={{
                  width: '100%',
                  padding: '0.45rem 0.65rem',
                  borderRadius: '6px',
                  border: '1px solid #cbd5e1',
                  fontSize: '0.75rem',
                  fontWeight: 600,
                  color: '#6d28d9',
                  background: '#ffffff'
                }}
              />
            </div>

            {/* Authorized Approver Role / Persona */}
            <div style={{ marginBottom: '0.65rem' }}>
              <label
                style={{
                  display: 'block',
                  fontSize: '0.7rem',
                  fontWeight: 600,
                  color: '#374151',
                  marginBottom: '0.35rem'
                }}
              >
                Authorized Approver Persona
              </label>
              <select
                value={data.approver || 'Direct Manager or Administrator'}
                onChange={(e) => updateNodeData('approver', e.target.value)}
                style={{
                  width: '100%',
                  padding: '0.45rem 0.65rem',
                  borderRadius: '6px',
                  border: '1px solid #cbd5e1',
                  fontSize: '0.75rem',
                  fontWeight: 600,
                  color: '#1e293b',
                  background: '#ffffff',
                  cursor: 'pointer'
                }}
              >
                <option value="Direct Manager or Administrator">Direct Manager or Administrator</option>
                <option value="Direct Manager Only">Direct Manager Only</option>
                <option value="System Administrator Only">System Administrator Only</option>
                <option value="Regional Sales VP">Regional Sales VP</option>
                <option value="Commercial Finance Director">Commercial Finance Director</option>
                <option value="Executive Committee">Executive Committee</option>
              </select>
            </div>

            {/* SLA Review Threshold */}
            <div style={{ marginBottom: '0.65rem' }}>
              <label
                style={{
                  display: 'block',
                  fontSize: '0.7rem',
                  fontWeight: 600,
                  color: '#374151',
                  marginBottom: '0.35rem'
                }}
              >
                SLA Review Threshold
              </label>
              <select
                value={data.sla || '12h SLA'}
                onChange={(e) => updateNodeData('sla', e.target.value)}
                style={{
                  width: '100%',
                  padding: '0.45rem 0.65rem',
                  borderRadius: '6px',
                  border: '1px solid #cbd5e1',
                  fontSize: '0.75rem',
                  color: '#1e293b',
                  background: '#ffffff',
                  cursor: 'pointer'
                }}
              >
                <option value="4h SLA">4h SLA (Urgent)</option>
                <option value="12h SLA">12h SLA (Standard)</option>
                <option value="24h SLA">24h SLA (1 Business Day)</option>
                <option value="48h SLA">48h SLA (2 Business Days)</option>
                <option value="72h SLA">72h SLA (Auto Escalate)</option>
              </select>
            </div>

            {/* Security & Authorization Rule */}
            <div style={{ marginBottom: '0.65rem' }}>
              <label
                style={{
                  display: 'block',
                  fontSize: '0.7rem',
                  fontWeight: 600,
                  color: '#374151',
                  marginBottom: '0.35rem'
                }}
              >
                Security &amp; Authorization Rule
              </label>
              <input
                type="text"
                value={data.condition !== undefined ? data.condition : 'user.role in [Manager, Admin] || is_direct_manager'}
                onChange={(e) => updateNodeData('condition', e.target.value)}
                placeholder="user.role in [Manager, Admin] || is_direct_manager"
                style={{
                  width: '100%',
                  padding: '0.45rem 0.65rem',
                  borderRadius: '6px',
                  border: '1px solid #cbd5e1',
                  fontSize: '0.725rem',
                  fontFamily: 'monospace',
                  color: '#334155',
                  background: '#ffffff'
                }}
              />
            </div>

            {/* Behavior on Approval & Rejection Pills */}
            <div style={{ display: 'flex', flexDirection: 'column', gap: '0.4rem', marginTop: '0.5rem' }}>
              <div style={{ padding: '0.4rem 0.6rem', borderRadius: '6px', background: '#ecfdf5', border: '1px solid #a7f3d0', fontSize: '0.6875rem', color: '#065f46' }}>
                <strong>Approved Action:</strong> Sets <code>approval_status = Approved</code>, unlocks record edits &amp; task creations.
              </div>
              <div style={{ padding: '0.4rem 0.6rem', borderRadius: '6px', background: '#fff1f2', border: '1px solid #fecdd3', fontSize: '0.6875rem', color: '#9f1239' }}>
                <strong>Rejected Action:</strong> Sets <code>approval_status = Rejected</code>, maintains record &amp; task lock.
              </div>
            </div>
          </div>
        )}

        {/* Outgoing Connections Matching Screenshot */}
        <div>
          <label style={{ display: 'block', fontSize: '0.6875rem', fontWeight: 800, color: '#475569', textTransform: 'uppercase', letterSpacing: '0.04em', marginBottom: '0.45rem' }}>
            OUTGOING CONNECTIONS
          </label>
          <select
            defaultValue=""
            onChange={(e) => {
              if (e.target.value && onConnectNextNode) {
                onConnectNextNode(id, e.target.value);
                e.target.value = '';
              }
            }}
            style={{
              width: '100%',
              padding: '0.45rem 0.65rem',
              borderRadius: '6px',
              border: '1px solid #cbd5e1',
              fontSize: '0.75rem',
              fontWeight: 500,
              background: '#ffffff',
              color: '#334155',
              cursor: 'pointer'
            }}
          >
            <option value="" disabled>
              + Connect to next node...
            </option>
            {availableTargetNodes.map((target) => (
              <option key={target.id} value={target.id}>
                &rarr; {target.data?.title || target.id}
              </option>
            ))}
          </select>
        </div>

        {/* Delete Node Option */}
        <div style={{ marginTop: 'auto', paddingTop: '1rem', borderTop: '1px solid #f1f5f9' }}>
          <button
            type="button"
            onClick={deleteNode}
            style={{
              width: '100%',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              gap: '0.4rem',
              padding: '0.5rem',
              borderRadius: '8px',
              background: '#fef2f2',
              color: '#dc2626',
              border: '1px solid #fecdd3',
              fontSize: '0.75rem',
              fontWeight: 600,
              cursor: 'pointer'
            }}
            className="hover:bg-red-100"
          >
            <Trash2 size={13} />
            <span>Delete Selected Node</span>
          </button>
        </div>
      </div>
    </aside>
  );
}
