import React, { useState, useCallback, useMemo, useRef } from 'react';
import {
  ReactFlow,
  Background,
  Controls,
  MiniMap,
  Panel,
  MarkerType,
  BackgroundVariant,
  useNodesState,
  useEdgesState,
  addEdge,
  ReactFlowProvider,
  useReactFlow,
  ConnectionMode
} from '@xyflow/react';

import { nodeTypes } from './nodes';
import { WORKFLOW_PRESETS } from './data/presets';
import FlowHeader from './components/FlowHeader';
import ComponentSidebar from './components/ComponentSidebar';
import NodeInspector from './components/NodeInspector';
import StepSimulator from './components/StepSimulator';
import WorkflowJsonModal from './components/WorkflowJsonModal';
import TestRecordModal from './components/TestRecordModal';

function ProcessStudioFlowInner({ initialFlowKey = '' }) {
  const reactFlowWrapper = useRef(null);
  const { screenToFlowPosition, zoomIn, zoomOut, zoomTo, getZoom } = useReactFlow();

  // Parse query parameter ?flow=... or prop
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
      if (document.referrer && document.referrer.includes('approval')) {
        return 'mega-deal-discount';
      }
    } catch (e) {}
    return 'lock-record-flow';
  };

  const [activePresetKey, setActivePresetKey] = useState(getInitialFlowKey);
  const activePreset = WORKFLOW_PRESETS[activePresetKey] || WORKFLOW_PRESETS['lock-record-flow'] || WORKFLOW_PRESETS['opp-won-sync'];
  const [flowStatus, setFlowStatus] = useState('Active');
  const [zoomLevel, setZoomLevel] = useState(100);

  // Helper to load workflow from localStorage (v2 format) or fallback to preset
  const loadInitialNodesAndEdges = (key) => {
    try {
      const saved = localStorage.getItem(`dealsync_flow_v2_${key}`);
      if (saved) {
        const parsed = JSON.parse(saved);
        if (parsed.nodes && parsed.edges && Array.isArray(parsed.nodes) && parsed.nodes.length > 0) {
          return { nodes: parsed.nodes, edges: parsed.edges };
        }
      }
    } catch (e) {}
    const preset = WORKFLOW_PRESETS[key] || WORKFLOW_PRESETS['lock-record-flow'] || WORKFLOW_PRESETS['opp-won-sync'];
    return { nodes: preset.nodes, edges: preset.edges };
  };

  const initialConfig = useMemo(() => loadInitialNodesAndEdges(activePresetKey), [activePresetKey]);

  // Flow State with React Flow hooks
  const [nodes, setNodes, onNodesChange] = useNodesState(initialConfig.nodes);
  const [edges, setEdges, onEdgesChange] = useEdgesState(initialConfig.edges);

  // UI Interactive States (Blocks & Inspector open by default matching screenshot)
  const [isBlocksOpen, setIsBlocksOpen] = useState(true);
  const [isInspectorOpen, setIsInspectorOpen] = useState(true);
  const [selectedNodeId, setSelectedNodeId] = useState(() => {
    if (initialConfig.nodes && initialConfig.nodes.length === 1) {
      return initialConfig.nodes[0].id;
    }
    return null;
  });

  const selectedNode = useMemo(() => {
    return nodes.find((n) => n.id === selectedNodeId) || null;
  }, [nodes, selectedNodeId]);

  const [isSimulatorOpen, setIsSimulatorOpen] = useState(false);
  const [isJsonModalOpen, setIsJsonModalOpen] = useState(false);
  const [isTestModalOpen, setIsTestModalOpen] = useState(false);
  const [saveToast, setSaveToast] = useState(false);

  // Simulator step & logs
  const [simulationStep, setSimulationStep] = useState(0);
  const [simulationLogs, setSimulationLogs] = useState([]);

  // Switch Workflow Presets
  const switchWorkflow = (key, forceReset = false) => {
    if (!WORKFLOW_PRESETS[key]) return;
    setActivePresetKey(key);
    if (forceReset) {
      try {
        localStorage.removeItem(`dealsync_flow_v2_${key}`);
      } catch (e) {}
    }
    const { nodes: newNodes, edges: newEdges } = forceReset
      ? { nodes: WORKFLOW_PRESETS[key].nodes, edges: WORKFLOW_PRESETS[key].edges }
      : loadInitialNodesAndEdges(key);

    setNodes(newNodes);
    setEdges(newEdges);
    setSelectedNodeId(newNodes.length === 1 ? newNodes[0].id : null);
    setSimulationStep(0);
    setSimulationLogs([]);

    // Update browser URL query without full reload
    try {
      const url = new URL(window.location);
      url.searchParams.set('flow', key);
      window.history.pushState({}, '', url);
    } catch (e) {}
  };

  // Connect Edges handler
  const onConnect = useCallback(
    (params) => {
      const sourceNode = nodes.find((n) => n.id === params.source);
      const strokeColor =
        sourceNode?.type === 'processTrigger'
          ? '#4f46e5'
          : sourceNode?.type === 'processScreen'
          ? '#0284c7'
          : sourceNode?.type === 'processDecision'
          ? '#d97706'
          : '#059669';

      setEdges((eds) =>
        addEdge(
          {
            ...params,
            type: 'smoothstep',
            animated: true,
            style: { stroke: strokeColor, strokeWidth: 2.2 },
            markerEnd: { type: MarkerType.ArrowClosed, color: strokeColor }
          },
          eds
        )
      );
    },
    [nodes, setEdges]
  );

  // Connect Next Node via Inspector Dropdown
  const handleConnectNextNode = (sourceId, targetId) => {
    const sourceNode = nodes.find((n) => n.id === sourceId);
    const targetNode = nodes.find((n) => n.id === targetId);

    let sourceHandle = 'right';
    let targetHandle = 'left';
    if (sourceNode && targetNode) {
      const dx = (targetNode.position?.x ?? 0) - (sourceNode.position?.x ?? 0);
      const dy = (targetNode.position?.y ?? 0) - (sourceNode.position?.y ?? 0);
      if (Math.abs(dy) > Math.abs(dx)) {
        sourceHandle = dy > 0 ? 'bottom' : 'top';
        targetHandle = dy > 0 ? 'top' : 'bottom';
      } else {
        sourceHandle = dx > 0 ? 'right' : 'left';
        targetHandle = dx > 0 ? 'left' : 'right';
      }
    }

    const strokeColor =
      sourceNode?.type === 'processTrigger'
        ? '#4f46e5'
        : sourceNode?.type === 'processScreen'
        ? '#0284c7'
        : sourceNode?.type === 'processDecision'
        ? '#d97706'
        : '#059669';

    const newEdge = {
      id: `edge-${sourceId}-${targetId}-${Date.now()}`,
      source: sourceId,
      target: targetId,
      sourceHandle,
      targetHandle,
      type: 'smoothstep',
      animated: true,
      style: { stroke: strokeColor, strokeWidth: 2.2 },
      markerEnd: { type: MarkerType.ArrowClosed, color: strokeColor }
    };
    setEdges((eds) => addEdge(newEdge, eds));
  };

  // Node selection handlers
  const onNodeClick = useCallback((event, node) => {
    setSelectedNodeId(node.id);
    setIsInspectorOpen(true);
  }, []);

  const onPaneClick = useCallback(() => {
    setSelectedNodeId(null);
  }, []);

  // HTML5 Drag and Drop handlers for dropping components from palette onto canvas
  const onDragOver = useCallback((event) => {
    event.preventDefault();
    event.dataTransfer.dropEffect = 'move';
  }, []);

  const onDrop = useCallback(
    (event) => {
      event.preventDefault();

      const reactFlowData = event.dataTransfer.getData('application/reactflow');
      if (!reactFlowData) return;

      try {
        const item = JSON.parse(reactFlowData);

        // Calculate flow canvas coordinates from screen position
        const position = screenToFlowPosition({
          x: event.clientX,
          y: event.clientY
        });

        const newId = `node-${Math.floor(1000 + Math.random() * 9000).toString(16)}-${Math.random().toString(36).substring(2, 5)}`;
        const newNode = {
          id: newId,
          type:
            item.type === 'approvalStep'
              ? 'processApprovalStep'
              : item.type === 'trigger'
              ? 'processTrigger'
              : item.type === 'decision'
              ? 'processDecision'
              : item.type === 'screen'
              ? 'processScreen'
              : 'processAction',
          position,
          data: {
            category: item.category || 'ACTION',
            subtype: item.subtype || 'action',
            title: item.title || 'New Component',
            description: item.description || (item.subtype === 'record_event' ? 'Starts automatically on record creation or field edit' : 'Starts when user clicks button on record header'),
            button_label: item.button_label || (item.subtype === 'button_click' ? 'Execute Process' : undefined),
            event_type: item.event_type || (item.subtype === 'record_event' ? 'A record is created or updated' : undefined),
            entity: item.entity || (item.subtype === 'record_event' ? 'Opportunities' : undefined),
            modal_title:
              item.subtype === 'data_table'
                ? 'Specification Data Table'
                : (item.subtype === 'modal_form' || item.subtype === 'form_dialog')
                ? 'Opportunity Data Intake Dialog'
                : undefined,
            instructions:
              item.subtype === 'data_table'
                ? 'Please enter child specification line items.'
                : (item.subtype === 'modal_form' || item.subtype === 'form_dialog')
                ? 'Please enter the requested details before proceeding.'
                : undefined,
            submit_button_text:
              (item.subtype === 'modal_form' || item.subtype === 'form_dialog')
                ? 'Submit & Proceed'
                : undefined,
            allow_add_row: item.subtype === 'data_table' ? true : undefined,
            columns: item.subtype === 'data_table' ? [
              { header: 'Item Name', key: 'item', type: 'Text (General)' },
              { header: 'Quantity', key: 'qty', type: 'Number (with Auto Total Sum)' },
              { header: 'Gross Weight (KG)', key: 'weight', type: 'Number (with Auto Total Sum)' }
            ] : undefined,
            form_fields: (item.subtype === 'modal_form' || item.subtype === 'form_dialog') ? [
              { label: 'Reason / Purpose', name: 'reason', type: 'Text Input', required: true },
              { label: 'Priority Level', name: 'priority_level', type: 'Dropdown (Select)', options: 'Low, Medium, High, Urgent', required: true },
              { label: 'Target Close Date', name: 'target_close_date', type: 'Date Picker', required: true },
              { label: 'Additional Comments', name: 'comments', type: 'Textarea (Long Text)', required: false }
            ] : undefined,
            target_field:
              (item.type === 'decision' || item.subtype === 'condition')
                ? (item.target_field || 'Opportunity Amount (amount)')
                : (item.subtype === 'update_field' ? (item.target_field || 'Stage (stage)') : undefined),
            target_field_key:
              (item.type === 'decision' || item.subtype === 'condition')
                ? (item.target_field_key || 'amount')
                : (item.subtype === 'update_field' ? (item.target_field_key || 'stage') : undefined),
            target_value:
              item.subtype === 'update_field' ? (item.target_value || 'Approved') : undefined,
            mutate_fields:
              item.subtype === 'update_field' ? (item.mutate_fields || [{ field: 'Stage (stage)', key: 'stage', value: 'Approved' }]) : undefined,
            target_module:
              item.subtype === 'create_record'
                ? (item.target_module || 'Tasks & Action Items (tasks)')
                : undefined,
            auto_link_parent:
              item.subtype === 'create_record'
                ? (item.auto_link_parent !== undefined ? item.auto_link_parent : true)
                : undefined,
            task_title:
              item.subtype === 'create_record'
                ? (item.task_title || 'Follow-up Task: Process Action for {{name}}')
                : undefined,
            task_guidance:
              item.subtype === 'create_record'
                ? (item.task_guidance || 'Automated task created by Process Studio workflow.\nPlease review and take appropriate action.')
                : undefined,
            priority:
              item.subtype === 'create_record'
                ? (item.priority || 'High Priority')
                : undefined,
            due_date_offset:
              item.subtype === 'create_record'
                ? (item.due_date_offset || '+1 Business Day')
                : undefined,
            assign_to:
              item.subtype === 'create_record'
                ? (item.assign_to || 'Assigned Owner of Current Record')
                : undefined,
            recipient_source:
              item.subtype === 'send_email'
                ? (item.recipient_source || 'Contact / Customer on Record (Dynamic)')
                : undefined,
            record_email_field:
              item.subtype === 'send_email'
                ? (item.record_email_field || 'Primary Email (email)')
                : undefined,
            cc_email: item.subtype === 'send_email' ? (item.cc_email || '') : undefined,
            bcc_email: item.subtype === 'send_email' ? (item.bcc_email || '') : undefined,
            email_template:
              item.subtype === 'send_email'
                ? (item.email_template || '-- Custom Subject & Body (Manual) -')
                : undefined,
            subject:
              item.subtype === 'send_email'
                ? (item.subject || 'Notification: Update regarding {{name}}')
                : undefined,
            body:
              item.subtype === 'send_email'
                ? (item.body || 'Hello {{contact_name}},\n\nThis is an automated notification regarding your recent request.')
                : undefined,
            send_timing:
              item.subtype === 'send_email'
                ? (item.send_timing || 'Immediately (Instant trigger upon step)')
                : undefined,
            execution_mode:
              item.subtype === 'send_email'
                ? (item.execution_mode || 'Automated Silent Dispatch (Background)')
                : undefined,
            send_from_mailbox:
              item.subtype === 'send_email'
                ? (item.send_from_mailbox || 'System Mailer (no-reply@company.com)')
                : undefined,
            high_priority_delivery:
              item.subtype === 'send_email' ? Boolean(item.high_priority_delivery) : undefined,
            attach_flow_pdf:
              item.subtype === 'send_email' ? Boolean(item.attach_flow_pdf) : undefined,
            toast_message:
              item.subtype === 'show_toast'
                ? (item.toast_message || 'Business process step completed successfully.')
                : undefined,
            toast_tone:
              item.subtype === 'show_toast'
                ? (item.toast_tone || 'Success (Green)')
                : undefined,
            lock_action:
              item.subtype === 'lock_record'
                ? (item.lock_action || '🔒 Enforce Edit Lock (Make Record Read-Only)')
                : undefined,
            lock_action_label:
              item.subtype === 'lock_record'
                ? (item.lock_action_label || 'Enforce Edit Lock')
                : undefined,
            bypass_authority:
              item.subtype === 'lock_record'
                ? (item.bypass_authority || 'System Administrators Only')
                : undefined,
            bypass_authority_badge:
              item.subtype === 'lock_record'
                ? (item.bypass_authority_badge || 'Admin Only')
                : undefined,
            strict_lock:
              item.subtype === 'lock_record'
                ? (item.strict_lock !== undefined ? item.strict_lock : true)
                : undefined,
            notice_banner_style:
              item.subtype === 'lock_record'
                ? (item.notice_banner_style || 'Amber Warning ("Record Locked Pending Review")')
                : undefined,
            notice_message:
              item.subtype === 'lock_record'
                ? (item.notice_message || 'This record is locked pending executive/process review. Contact your system administrator to request changes.')
                : undefined,
            operator: (item.type === 'decision' || item.subtype === 'condition') ? (item.operator || 'Greater than (>)') : undefined,
            operator_key: (item.type === 'decision' || item.subtype === 'condition') ? (item.operator_key || 'greater_than') : undefined,
            comparison_value: (item.type === 'decision' || item.subtype === 'condition') ? (item.comparison_value || '50000') : undefined,
            condition:
              item.condition ||
              ((item.type === 'decision' || item.subtype === 'condition')
                ? 'If amount greater_than "50000"'
                : (item.subtype === 'update_field' ? 'Set stage = "Approved"' : (item.subtype === 'create_record' ? '+ Insert TASKS High' : (item.subtype === 'send_email' ? '✉ To: Record [email]' : (item.subtype === 'show_toast' ? 'Business process step completed successfully.' : (item.subtype === 'lock_record' ? 'lock_record = true (Read-Only)' : '')))))),
            color: item.color || '#4f46e5',
            icon: item.icon,
            stepBadge: item.stepBadge || undefined
          }
        };

        // Automatically connect new dropped node from the selected or last existing node
        setNodes((nds) => {
          const sourceNode =
            (selectedNodeId && nds.find((n) => n.id === selectedNodeId)) ||
            (nds.length > 0 ? nds[nds.length - 1] : null);

          if (sourceNode && sourceNode.id !== newId) {
            const dx = position.x - (sourceNode.position?.x ?? 0);
            const dy = position.y - (sourceNode.position?.y ?? 0);

            let sourceHandle = 'right';
            let targetHandle = 'left';

            if (Math.abs(dy) > Math.abs(dx)) {
              if (dy > 0) {
                sourceHandle = 'bottom';
                targetHandle = 'top';
              } else {
                sourceHandle = 'top';
                targetHandle = 'bottom';
              }
            } else {
              if (dx > 0) {
                sourceHandle = 'right';
                targetHandle = 'left';
              } else {
                sourceHandle = 'left';
                targetHandle = 'right';
              }
            }

            const strokeColor =
              sourceNode.type === 'processTrigger'
                ? '#4f46e5'
                : sourceNode.type === 'processScreen'
                ? '#0284c7'
                : sourceNode.type === 'processDecision'
                ? '#d97706'
                : '#059669';

            const newEdge = {
              id: `edge-${sourceNode.id}-${newId}-${Date.now()}`,
              source: sourceNode.id,
              target: newId,
              sourceHandle,
              targetHandle,
              type: 'smoothstep',
              animated: true,
              style: { stroke: strokeColor, strokeWidth: 2.2 },
              markerEnd: { type: MarkerType.ArrowClosed, color: strokeColor }
            };

            setEdges((eds) => addEdge(newEdge, eds));
          }

          return nds.concat(newNode);
        });

        setSelectedNodeId(newNode.id);
        setIsInspectorOpen(true);
      } catch (err) {
        console.error('Failed to parse dropped node item:', err);
      }
    },
    [screenToFlowPosition, setNodes, setEdges, selectedNodeId, setSelectedNodeId]
  );

  // Update node data from Inspector in real time
  const updateSelectedNodeData = useCallback(
    (key, value) => {
      if (!selectedNodeId) return;
      setNodes((nds) =>
        nds.map((node) => {
          if (node.id === selectedNodeId) {
            return {
              ...node,
              data: {
                ...node.data,
                [key]: value
              }
            };
          }
          return node;
        })
      );
    },
    [selectedNodeId, setNodes]
  );

  // Delete Node handler
  const handleDeleteSelectedNode = () => {
    if (!selectedNodeId) return;
    setNodes((nds) => nds.filter((n) => n.id !== selectedNodeId));
    setEdges((eds) => eds.filter((e) => e.source !== selectedNodeId && e.target !== selectedNodeId));
    setSelectedNodeId(null);
  };

  // Add Node from click-to-add
  const handleAddNodeFromClick = (item) => {
    const newId = `node-${Math.floor(1000 + Math.random() * 9000).toString(16)}-${Math.random().toString(36).substring(2, 5)}`;
    const newNode = {
      id: newId,
      type:
        item.type === 'approvalStep'
          ? 'processApprovalStep'
          : item.type === 'trigger'
          ? 'processTrigger'
          : item.type === 'decision'
          ? 'processDecision'
          : item.type === 'screen'
          ? 'processScreen'
          : 'processAction',
      position: { x: 380 + Math.random() * 60, y: 200 + Math.random() * 60 },
      data: {
        category: item.category,
        subtype: item.subtype,
        title: item.title,
        description: item.description,
        button_label: item.button_label || (item.subtype === 'button_click' ? 'Execute Process' : undefined),
        event_type: item.event_type || (item.subtype === 'record_event' ? 'A record is created or updated' : undefined),
        entity: item.entity || (item.subtype === 'record_event' ? 'Opportunities' : undefined),
        modal_title:
          item.subtype === 'data_table'
            ? 'Specification Data Table'
            : (item.subtype === 'modal_form' || item.subtype === 'form_dialog')
            ? 'Opportunity Data Intake Dialog'
            : undefined,
        instructions:
          item.subtype === 'data_table'
            ? 'Please enter child specification line items.'
            : (item.subtype === 'modal_form' || item.subtype === 'form_dialog')
            ? 'Please enter the requested details before proceeding.'
            : undefined,
        submit_button_text:
          (item.subtype === 'modal_form' || item.subtype === 'form_dialog')
            ? 'Submit & Proceed'
            : undefined,
        allow_add_row: item.subtype === 'data_table' ? true : undefined,
        columns: item.subtype === 'data_table' ? [
          { header: 'Item Name', key: 'item', type: 'Text (General)' },
          { header: 'Quantity', key: 'qty', type: 'Number (with Auto Total Sum)' },
          { header: 'Gross Weight (KG)', key: 'weight', type: 'Number (with Auto Total Sum)' }
        ] : undefined,
        form_fields: (item.subtype === 'modal_form' || item.subtype === 'form_dialog') ? [
          { label: 'Reason / Purpose', name: 'reason', type: 'Text Input', required: true },
          { label: 'Priority Level', name: 'priority_level', type: 'Dropdown (Select)', options: 'Low, Medium, High, Urgent', required: true },
          { label: 'Target Close Date', name: 'target_close_date', type: 'Date Picker', required: true },
          { label: 'Additional Comments', name: 'comments', type: 'Textarea (Long Text)', required: false }
        ] : undefined,
        target_field:
          (item.type === 'decision' || item.subtype === 'condition')
            ? (item.target_field || 'Opportunity Amount (amount)')
            : (item.subtype === 'update_field' ? (item.target_field || 'Stage (stage)') : undefined),
        target_field_key:
          (item.type === 'decision' || item.subtype === 'condition')
            ? (item.target_field_key || 'amount')
            : (item.subtype === 'update_field' ? (item.target_field_key || 'stage') : undefined),
        target_value:
          item.subtype === 'update_field' ? (item.target_value || 'Approved') : undefined,
        mutate_fields:
          item.subtype === 'update_field' ? (item.mutate_fields || [{ field: 'Stage (stage)', key: 'stage', value: 'Approved' }]) : undefined,
        target_module:
          item.subtype === 'create_record'
            ? (item.target_module || 'Tasks & Action Items (tasks)')
            : undefined,
        auto_link_parent:
          item.subtype === 'create_record'
            ? (item.auto_link_parent !== undefined ? item.auto_link_parent : true)
            : undefined,
        task_title:
          item.subtype === 'create_record'
            ? (item.task_title || 'Follow-up Task: Process Action for {{name}}')
            : undefined,
        task_guidance:
          item.subtype === 'create_record'
            ? (item.task_guidance || 'Automated task created by Process Studio workflow.\nPlease review and take appropriate action.')
            : undefined,
        priority:
          item.subtype === 'create_record'
            ? (item.priority || 'High Priority')
            : undefined,
        due_date_offset:
          item.subtype === 'create_record'
            ? (item.due_date_offset || '+1 Business Day')
            : undefined,
        assign_to:
          item.subtype === 'create_record'
            ? (item.assign_to || 'Assigned Owner of Current Record')
            : undefined,
        recipient_source:
          item.subtype === 'send_email'
            ? (item.recipient_source || 'Contact / Customer on Record (Dynamic)')
            : undefined,
        record_email_field:
          item.subtype === 'send_email'
            ? (item.record_email_field || 'Primary Email (email)')
            : undefined,
        cc_email: item.subtype === 'send_email' ? (item.cc_email || '') : undefined,
        bcc_email: item.subtype === 'send_email' ? (item.bcc_email || '') : undefined,
        email_template:
          item.subtype === 'send_email'
            ? (item.email_template || '-- Custom Subject & Body (Manual) -')
            : undefined,
        subject:
          item.subtype === 'send_email'
            ? (item.subject || 'Notification: Update regarding {{name}}')
            : undefined,
        body:
          item.subtype === 'send_email'
            ? (item.body || 'Hello {{contact_name}},\n\nThis is an automated notification regarding your recent request.')
            : undefined,
        send_timing:
          item.subtype === 'send_email'
            ? (item.send_timing || 'Immediately (Instant trigger upon step)')
            : undefined,
        execution_mode:
          item.subtype === 'send_email'
            ? (item.execution_mode || 'Automated Silent Dispatch (Background)')
            : undefined,
        send_from_mailbox:
          item.subtype === 'send_email'
            ? (item.send_from_mailbox || 'System Mailer (no-reply@company.com)')
            : undefined,
        high_priority_delivery:
          item.subtype === 'send_email' ? Boolean(item.high_priority_delivery) : undefined,
        attach_flow_pdf:
          item.subtype === 'send_email' ? Boolean(item.attach_flow_pdf) : undefined,
        toast_message:
          item.subtype === 'show_toast'
            ? (item.toast_message || 'Business process step completed successfully.')
            : undefined,
        toast_tone:
          item.subtype === 'show_toast'
            ? (item.toast_tone || 'Success (Green)')
            : undefined,
        lock_action:
          item.subtype === 'lock_record'
            ? (item.lock_action || '🔒 Enforce Edit Lock (Make Record Read-Only)')
            : undefined,
        lock_action_label:
          item.subtype === 'lock_record'
            ? (item.lock_action_label || 'Enforce Edit Lock')
            : undefined,
        bypass_authority:
          item.subtype === 'lock_record'
            ? (item.bypass_authority || 'System Administrators Only')
            : undefined,
        bypass_authority_badge:
          item.subtype === 'lock_record'
            ? (item.bypass_authority_badge || 'Admin Only')
            : undefined,
        strict_lock:
          item.subtype === 'lock_record'
            ? (item.strict_lock !== undefined ? item.strict_lock : true)
            : undefined,
        notice_banner_style:
          item.subtype === 'lock_record'
            ? (item.notice_banner_style || 'Amber Warning ("Record Locked Pending Review")')
            : undefined,
        notice_message:
          item.subtype === 'lock_record'
            ? (item.notice_message || 'This record is locked pending executive/process review. Contact your system administrator to request changes.')
            : undefined,
        operator: (item.type === 'decision' || item.subtype === 'condition') ? (item.operator || 'Greater than (>)') : undefined,
        operator_key: (item.type === 'decision' || item.subtype === 'condition') ? (item.operator_key || 'greater_than') : undefined,
        comparison_value: (item.type === 'decision' || item.subtype === 'condition') ? (item.comparison_value || '50000') : undefined,
        condition:
          item.condition ||
          ((item.type === 'decision' || item.subtype === 'condition')
            ? 'If amount greater_than "50000"'
            : (item.subtype === 'update_field' ? 'Set stage = "Approved"' : (item.subtype === 'create_record' ? '+ Insert TASKS High' : (item.subtype === 'send_email' ? '✉ To: Record [email]' : (item.subtype === 'show_toast' ? 'Business process step completed successfully.' : (item.subtype === 'lock_record' ? 'lock_record = true (Read-Only)' : '')))))),
        color: item.color,
        icon: item.icon,
        stepBadge: item.stepBadge || undefined
      }
    };

    setNodes((nds) => {
      const lastNode = nds[nds.length - 1];
      const sourceNode = (selectedNodeId && nds.find((n) => n.id === selectedNodeId)) || lastNode;

      if (sourceNode && sourceNode.id !== newId) {
        const strokeColor =
          sourceNode.type === 'processTrigger'
            ? '#4f46e5'
            : sourceNode.type === 'processScreen'
            ? '#0284c7'
            : sourceNode.type === 'processDecision'
            ? '#d97706'
            : '#059669';

        const newEdge = {
          id: `edge-${sourceNode.id}-${newId}-${Date.now()}`,
          source: sourceNode.id,
          target: newId,
          sourceHandle: 'right',
          targetHandle: 'left',
          type: 'smoothstep',
          animated: true,
          style: { stroke: strokeColor, strokeWidth: 2.2 },
          markerEnd: { type: MarkerType.ArrowClosed, color: strokeColor }
        };

        setEdges((eds) => addEdge(newEdge, eds));
      }

      return nds.concat(newNode);
    });

    setSelectedNodeId(newNode.id);
    setIsInspectorOpen(true);
  };

  // Zoom handlers
  const handleZoomIn = () => {
    zoomIn({ duration: 200 });
    setTimeout(() => {
      try {
        setZoomLevel(Math.round(getZoom() * 100));
      } catch (e) {}
    }, 220);
  };

  const handleZoomOut = () => {
    zoomOut({ duration: 200 });
    setTimeout(() => {
      try {
        setZoomLevel(Math.round(getZoom() * 100));
      } catch (e) {}
    }, 220);
  };

  const handleZoomReset = () => {
    zoomTo(1, { duration: 200 });
    setZoomLevel(100);
  };

  // Save changes to localStorage & show toast
  const handleSaveFlow = () => {
    try {
      localStorage.setItem(
        `dealsync_flow_v2_${activePresetKey}`,
        JSON.stringify({ nodes, edges, savedAt: new Date().toISOString() })
      );
    } catch (e) {}
    setSaveToast(true);
    setTimeout(() => setSaveToast(false), 3000);
  };

  const nodesWithSelection = useMemo(() => {
    return nodes.map((n) => ({
      ...n,
      selected: n.id === selectedNodeId
    }));
  }, [nodes, selectedNodeId]);

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
      {/* 1. Top Command Header matching screenshot */}
      <FlowHeader
        activePresetKey={activePresetKey}
        activePreset={activePreset}
        switchWorkflow={switchWorkflow}
        nodes={nodes}
        isSimulatorOpen={isSimulatorOpen}
        setIsSimulatorOpen={setIsSimulatorOpen}
        isBlocksOpen={isBlocksOpen}
        setIsBlocksOpen={setIsBlocksOpen}
        isInspectorOpen={isInspectorOpen}
        setIsInspectorOpen={setIsInspectorOpen}
        handleSaveFlow={handleSaveFlow}
        saveToast={saveToast}
        onZoomIn={handleZoomIn}
        onZoomOut={handleZoomOut}
        onZoomReset={handleZoomReset}
        zoomLevel={zoomLevel}
        flowStatus={flowStatus}
        setFlowStatus={setFlowStatus}
      />

      {/* 2. Middle Row: [Left Blocks Sidebar] | [Canvas Zone] | [Right Inspector Sidebar] */}
      <div
        style={{
          flex: 1,
          display: 'flex',
          flexDirection: 'row',
          position: 'relative',
          width: '100%',
          height: 'calc(100vh - 54px)',
          overflow: 'hidden'
        }}
      >
        {/* Left Side: FLOW BUILDING BLOCKS Sidebar matching reference screenshot */}
        <ComponentSidebar
          isOpen={isBlocksOpen}
          onToggle={() => setIsBlocksOpen(!isBlocksOpen)}
          onAddNodeClick={handleAddNodeFromClick}
        />

        {/* Center: React Flow Canvas Drop Zone */}
        <div
          ref={reactFlowWrapper}
          onDragOver={onDragOver}
          onDrop={onDrop}
          style={{ flex: 1, position: 'relative', height: '100%', width: '100%' }}
        >
          <ReactFlow
            nodes={nodesWithSelection}
            edges={edges}
            onNodesChange={onNodesChange}
            onEdgesChange={onEdgesChange}
            onConnect={onConnect}
            nodeTypes={nodeTypes}
            onNodeClick={onNodeClick}
            onPaneClick={onPaneClick}
            nodesDraggable={true}
            nodesConnectable={true}
            connectionMode={ConnectionMode.Loose}
            elementsSelectable={true}
            selectNodesOnDrag={false}
            snapToGrid={true}
            snapGrid={[15, 15]}
            nodeDragThreshold={1}
            panOnDrag={[1, 2]}
            fitView
            fitViewOptions={{ padding: 0.3 }}
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
          </ReactFlow>

          {/* Live Step Simulator Drawer (Floating Overlay) */}
          <StepSimulator
            isOpen={isSimulatorOpen}
            onClose={() => setIsSimulatorOpen(false)}
            nodes={nodes}
            activePresetKey={activePresetKey}
            simulationStep={simulationStep}
            setSimulationStep={setSimulationStep}
            simulationLogs={simulationLogs}
            setSimulationLogs={setSimulationLogs}
          />
        </div>

        {/* Right Side: NODE INSPECTOR & SETTINGS matching reference screenshot */}
        <NodeInspector
          isOpen={isInspectorOpen}
          onClose={() => setIsInspectorOpen(false)}
          selectedNode={selectedNode}
          nodes={nodes}
          updateNodeData={updateSelectedNodeData}
          deleteNode={handleDeleteSelectedNode}
          onConnectNextNode={handleConnectNextNode}
        />
      </div>

      {/* JSON Schema Modal */}
      <WorkflowJsonModal
        isOpen={isJsonModalOpen}
        onClose={() => setIsJsonModalOpen(false)}
        activePreset={activePreset}
        nodes={nodes}
        edges={edges}
      />

      {/* Dry-Run Against Live Record Modal */}
      <TestRecordModal
        isOpen={isTestModalOpen}
        onClose={() => setIsTestModalOpen(false)}
        activePreset={activePreset}
      />
    </div>
  );
}

export default function ProcessStudioFlow(props) {
  return (
    <ReactFlowProvider>
      <ProcessStudioFlowInner {...props} />
    </ReactFlowProvider>
  );
}
