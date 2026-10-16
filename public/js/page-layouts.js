/**
 * Page Layouts Designer Studio — DealSync Enterprise
 * 1:1 Pure Tailwind CSS Parity with React PageLayoutDesigner.tsx
 * Full HTML5 Drag-and-Drop for Sections, Palette Fields, and Placed Fields
 */

(function () {
    'use strict';

    const CORE_OBJECTS = [
        { id: 'leads', label: 'Leads' },
        { id: 'accounts', label: 'Accounts' },
        { id: 'contacts', label: 'Contacts' },
        { id: 'opportunities', label: 'Opportunities' },
        { id: 'tasks', label: 'Tasks' },
        { id: 'purchase_deals', label: 'Purchase Deals' },
        { id: 'shipping', label: 'Shipping & Freight' },
        { id: 'payments', label: 'Payments & Invoices' },
        { id: 'wires', label: 'Wire Reconciliation' },
        { id: 'users', label: 'Users' }
    ];

    const STANDARD_FIELDS_MAP = {
        leads: [
            { name: 'first_name', label: 'First Name', type: 'Text', required: true },
            { name: 'last_name', label: 'Last Name', type: 'Text', required: true },
            { name: 'company', label: 'Company Name', type: 'Text', required: true },
            { name: 'title', label: 'Job Title', type: 'Text' },
            { name: 'email', label: 'Email Address', type: 'Email', required: true },
            { name: 'phone', label: 'Phone Number', type: 'Phone' },
            { name: 'status', label: 'Status', type: 'Dropdown', required: true },
            { name: 'score', label: 'Lead Score', type: 'Number' },
            { name: 'source', label: 'Lead Source', type: 'Dropdown' },
            { name: 'assigned_to', label: 'Assigned To', type: 'Text' }
        ],
        accounts: [
            { name: 'name', label: 'Account Name', type: 'Text', required: true },
            { name: 'type', label: 'Account Type', type: 'Dropdown', required: true },
            { name: 'industry', label: 'Industry', type: 'Text' },
            { name: 'website', label: 'Website URL', type: 'URL' },
            { name: 'phone', label: 'Phone', type: 'Phone' },
            { name: 'billing_address', label: 'Billing Address', type: 'Textarea' },
            { name: 'shipping_address', label: 'Shipping Address', type: 'Textarea' },
            { name: 'payment_terms', label: 'Payment Terms', type: 'Dropdown' },
            { name: 'credit_limit', label: 'Credit Limit', type: 'Currency' },
            { name: 'annual_revenue', label: 'Annual Revenue', type: 'Currency' }
        ],
        contacts: [
            { name: 'first_name', label: 'First Name', type: 'Text', required: true },
            { name: 'last_name', label: 'Last Name', type: 'Text', required: true },
            { name: 'account_id', label: 'Account (Company)', type: 'Lookup' },
            { name: 'title', label: 'Job Title', type: 'Text' },
            { name: 'department', label: 'Department', type: 'Text' },
            { name: 'email', label: 'Email', type: 'Email', required: true },
            { name: 'phone', label: 'Phone', type: 'Phone' },
            { name: 'contact_role', label: 'Buying Role', type: 'Dropdown' },
            { name: 'is_primary', label: 'Primary Contact', type: 'Boolean' }
        ],
        opportunities: [
            { name: 'name', label: 'Opportunity Name', type: 'Text', required: true },
            { name: 'account_id', label: 'Customer Account', type: 'Dropdown', required: true },
            { name: 'purchase_deal_id', label: 'Procurement PO Deal', type: 'Dropdown', required: true },
            { name: 'stage', label: 'Pipeline Stage', type: 'Dropdown', required: true },
            { name: 'amount', label: 'Deal Amount', type: 'Currency', required: true },
            { name: 'probability', label: 'Win Probability (%)', type: 'Number' },
            { name: 'expected_close_date', label: 'Target Close Date', type: 'Date' }
        ],
        purchase_deals: [
            { name: 'po_number', label: 'PO Number', type: 'Text', required: true },
            { name: 'title', label: 'PO Title / Description', type: 'Text', required: true },
            { name: 'vendor_id', label: 'Supplier / Vendor', type: 'Dropdown', required: true },
            { name: 'amount', label: 'PO Total Spend', type: 'Currency', required: true },
            { name: 'stage', label: 'Procurement Stage', type: 'Dropdown', required: true },
            { name: 'expected_delivery_date', label: 'Target Delivery Date', type: 'Date' }
        ],
        shipping: [
            { name: 'tracking_number', label: 'Tracking Number', type: 'Text', required: true },
            { name: 'carrier', label: 'Freight Carrier', type: 'Dropdown', required: true },
            { name: 'type', label: 'Direction', type: 'Dropdown', required: true },
            { name: 'status', label: 'Shipment Status', type: 'Dropdown', required: true },
            { name: 'account_id', label: 'Associated Account', type: 'Dropdown', required: true },
            { name: 'shipping_origin', label: 'Origin / Dispatch Facility', type: 'Text' },
            { name: 'destination_address', label: 'Destination Address', type: 'Textarea' },
            { name: 'estimated_delivery_date', label: 'Estimated Delivery Date', type: 'Date' }
        ],
        payments: [
            { name: 'invoice_number', label: 'Invoice #', type: 'Text', required: true },
            { name: 'amount', label: 'Payment Amount', type: 'Currency', required: true },
            { name: 'status', label: 'Payment Status', type: 'Dropdown', required: true },
            { name: 'payment_method', label: 'Payment Method', type: 'Dropdown', required: true },
            { name: 'reference_number', label: 'Bank / Wire Reference', type: 'Text' },
            { name: 'payment_date', label: 'Payment Date', type: 'Date' }
        ],
        wires: [
            { name: 'wire_reference', label: 'Wire MT103 Ref', type: 'Text', required: true },
            { name: 'amount', label: 'Wire Transfer Amount', type: 'Currency', required: true },
            { name: 'status', label: 'Reconciliation Status', type: 'Dropdown', required: true },
            { name: 'sender_bank', label: 'Originating Bank', type: 'Text' },
            { name: 'recipient_bank', label: 'Beneficiary Bank', type: 'Text' },
            { name: 'cleared_date', label: 'Value / Clearance Date', type: 'Date' }
        ],
        tasks: [
            { name: 'subject', label: 'Subject / Task Title', type: 'Text', required: true },
            { name: 'due_date', label: 'Due Date', type: 'Date', required: true },
            { name: 'status', label: 'Status', type: 'Dropdown', required: true },
            { name: 'priority', label: 'Priority', type: 'Dropdown', required: true },
            { name: 'assigned_to', label: 'Assigned User', type: 'Dropdown', required: true },
            { name: 'description', label: 'Notes / Details', type: 'Textarea' }
        ],
        users: [
            { name: 'name', label: 'Full Name', type: 'Text', required: true },
            { name: 'email', label: 'Work Email Address', type: 'Email', required: true },
            { name: 'role', label: 'Organization Role', type: 'Dropdown', required: true },
            { name: 'profile_id', label: 'Security Profile', type: 'Dropdown', required: true },
            { name: 'department', label: 'Department', type: 'Text' },
            { name: 'phone', label: 'Direct Phone', type: 'Phone' }
        ]
    };

    // Global Designer State
    window.plState = {
        selectedModule: 'leads',
        customObjects: [],
        profiles: [],
        layouts: [],
        selectedLayoutId: '',
        profileAssignments: {},
        currentLayoutName: '',
        currentLayoutDesc: '',
        currentLayoutDefinition: null,
        designerTab: 'fields', // 'fields' | 'dependent_fields' | 'validation_rules' | 'related' | 'components' | 'activities'
        fieldSearch: '',
        fieldCategoryFilter: 'all', // 'all' | 'standard' | 'custom'
        allAvailableFields: [],
        availableCustomButtons: [],
        fieldHistoryConfig: null,
        loading: false,
        saving: false,
        feedback: null,
        editingSectionId: null,
        editingSectionTitle: '',
        // Drag-and-Drop state
        draggingItem: null, // { type: 'available'|'placed'|'section', field?: any, sourceSectionId?: string, sourceIndex?: number }
        dragOverSectionId: null,
        dragOverFieldIndex: null,
        dragOverSectionHeaderIndex: null,
        // Modals state
        isNewLayoutModalOpen: false,
        newLayoutName: '',
        newLayoutDesc: '',
        newLayoutCloneFrom: '',
        isAssignmentModalOpen: false,
        workingAssignments: {},
        isNewSectionModalOpen: false,
        newSectionTitle: '',
        newSectionColumns: 2,
        isFieldHistoryModalOpen: false,
        workingTrackedFields: [],
        fieldHistorySearch: '',
        visibilityModalTarget: null, // { type: 'field'|'section', sectionId: string, fieldName?: string, targetLabel: string, initialRule?: any }
        editingRelatedFields: null, // { relation: string, label: string, visibleFields: string[], sortField?: string, sortDirection?: 'asc'|'desc' }
    };

    function escapeHtml(str) {
        if (!str && str !== 0) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    function setFeedback(msg, duration = 3500) {
        window.plState.feedback = msg;
        renderHeader();
        if (msg) {
            setTimeout(() => {
                if (window.plState.feedback === msg) {
                    window.plState.feedback = null;
                    renderHeader();
                }
            }, duration);
        }
    }

    function getObjectDetailTitle(module) {
        const found = CORE_OBJECTS.find(o => o.id === module);
        if (found) return found.label;
        const co = window.plState.customObjects.find(c => c.api_name === module);
        if (co) return co.label;
        return module.charAt(0).toUpperCase() + module.slice(1).replace(/_/g, ' ');
    }

    function computeAvailableFields(module) {
        const standard = STANDARD_FIELDS_MAP[module] || [
            { name: 'name', label: 'Record Name', type: 'Text', required: true },
            { name: 'status', label: 'Status', type: 'Dropdown', required: true },
            { name: 'assigned_to', label: 'Owner', type: 'Lookup' },
            { name: 'created_at', label: 'Created At', type: 'Date' }
        ];

        let combined = standard.map(f => ({ ...f, isCustom: false }));

        if (window.serverCustomFields && Array.isArray(window.serverCustomFields)) {
            const customs = window.serverCustomFields.filter(f => f.module === module).map(f => ({
                name: f.api_name,
                label: f.label,
                type: f.type || 'Text',
                isCustom: true,
                options: f.options || []
            }));
            combined = [...combined, ...customs];
        }

        window.plState.allAvailableFields = combined;
        return combined;
    }

    function getUnusedFields() {
        const placedFieldNames = new Set();
        if (window.plState.currentLayoutDefinition && window.plState.currentLayoutDefinition.sections) {
            window.plState.currentLayoutDefinition.sections.forEach(sec => {
                (sec.fields || []).forEach(f => placedFieldNames.add(f.name));
            });
        }

        return window.plState.allAvailableFields.filter(f => {
            if (placedFieldNames.has(f.name)) return false;
            if (window.plState.fieldCategoryFilter === 'standard' && f.isCustom) return false;
            if (window.plState.fieldCategoryFilter === 'custom' && !f.isCustom) return false;
            if (window.plState.fieldSearch) {
                const q = window.plState.fieldSearch.toLowerCase();
                return f.label.toLowerCase().includes(q) || f.name.toLowerCase().includes(q);
            }
            return true;
        });
    }

    function selectLayout(layout) {
        if (!layout) return;
        window.plState.selectedLayoutId = layout.id;
        window.plState.currentLayoutName = layout.name || 'Default Layout';
        window.plState.currentLayoutDesc = layout.description || '';
        
        let def = layout.layout;
        if (typeof def === 'string') {
            try { def = JSON.parse(def); } catch (e) { def = null; }
        }
        if (!def || !def.sections) {
            def = {
                sections: [
                    {
                        id: 'sec-general',
                        title: getObjectDetailTitle(window.plState.selectedModule) + ' Information',
                        columns: 2,
                        collapsible: true,
                        defaultCollapsed: false,
                        fields: (window.plState.allAvailableFields || []).slice(0, 6).map(f => ({
                            name: f.name,
                            label: f.label,
                            type: f.type,
                            required: Boolean(f.required),
                            readOnly: false
                        }))
                    }
                ],
                showSystemInfo: true,
                dependentFields: [],
                validationRules: [],
                relatedLists: [],
                components: {
                    sidebarOrder: ['files', 'emails', 'activity', 'related', 'approvals'],
                    systemButtons: {},
                    customButtons: []
                },
                activitySettings: {
                    allowedTypes: ['Call', 'Task', 'Meeting', 'Note'],
                    defaultType: 'Call'
                }
            };
        }

        // Ensure showSystemInfo flag and section alignment
        const hasSysInfo = def.sections.some(s => s.id === 'sec-system-info' || s.title?.toLowerCase().includes('system information'));
        if (def.showSystemInfo !== false && !hasSysInfo) {
            def.sections.push({
                id: 'sec-system-info',
                title: 'System Information',
                columns: 2,
                collapsible: true,
                defaultCollapsed: false,
                fields: []
            });
        }

        window.plState.currentLayoutDefinition = JSON.parse(JSON.stringify(def));
        renderApp();
    }

    async function loadLayoutsForModule(mod) {
        window.plState.loading = true;
        renderHeader();
        try {
            const res = await fetch(`/api/page-layouts/${encodeURIComponent(mod)}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!res.ok) throw new Error('Failed to load layouts: ' + res.statusText);
            const data = await res.json();
            const list = data.layouts || [];
            window.plState.layouts = list;
            window.plState.profileAssignments = data.assignments || {};

            computeAvailableFields(mod);

            // Fetch Field History Config
            try {
                const fhRes = await fetch(`/api/field-history/config/${encodeURIComponent(mod)}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (fhRes.ok) {
                    window.plState.fieldHistoryConfig = await fhRes.json();
                }
            } catch (err) {
                console.warn('Field history fetch err:', err);
            }

            if (list.length > 0) {
                const defaultL = list.find(l => l.is_default) || list[0];
                selectLayout(defaultL);
            } else {
                window.plState.selectedLayoutId = '';
                window.plState.currentLayoutDefinition = null;
                renderApp();
            }
        } catch (err) {
            console.error('Error loading page layouts:', err);
            alert('Error loading page layouts: ' + err.message);
        } finally {
            window.plState.loading = false;
            renderHeader();
        }
    }

    // =========================================================================
    // HTML5 DRAG AND DROP HANDLERS (EXACT REACT REPLICATION)
    // =========================================================================

    window.plOnDragStartAvailable = function (e, fieldName) {
        const field = window.plState.allAvailableFields.find(f => f.name === fieldName);
        if (!field) return;
        window.plState.draggingItem = { type: 'available', field: field };
        e.dataTransfer.setData('text/plain', field.name);
        e.dataTransfer.effectAllowed = 'copyMove';
        // Add subtle opacity to source element
        if (e.currentTarget) e.currentTarget.style.opacity = '0.5';
    };

    window.plOnDragStartPlaced = function (e, secId, fIdx) {
        const sec = window.plState.currentLayoutDefinition?.sections?.find(s => s.id === secId);
        const field = sec?.fields?.[fIdx];
        if (!field) return;
        window.plState.draggingItem = {
            type: 'placed',
            field: field,
            sourceSectionId: secId,
            sourceIndex: fIdx
        };
        e.dataTransfer.setData('text/plain', field.name);
        e.dataTransfer.effectAllowed = 'move';
        if (e.currentTarget) e.currentTarget.style.opacity = '0.5';
    };

    window.plOnDragStartSection = function (e, sIdx) {
        window.plState.draggingItem = { type: 'section', sourceIndex: sIdx };
        e.dataTransfer.setData('text/plain', 'section_' + sIdx);
        e.dataTransfer.effectAllowed = 'move';
    };

    window.plOnDragEnd = function (e) {
        if (e.currentTarget) e.currentTarget.style.opacity = '1';
        window.plState.draggingItem = null;
        window.plState.dragOverSectionId = null;
        window.plState.dragOverFieldIndex = null;
        window.plState.dragOverSectionHeaderIndex = null;
        // Clean drop styles
        document.querySelectorAll('.pl-drop-active').forEach(el => el.classList.remove('pl-drop-active', 'border-indigo-500', 'bg-indigo-50/20'));
        document.querySelectorAll('.pl-field-drop-active').forEach(el => el.classList.remove('pl-field-drop-active', 'ring-2', 'ring-indigo-500'));
    };

    window.plOnDragOverSection = function (e, secId) {
        if (!window.plState.draggingItem) return;
        if (window.plState.draggingItem.type === 'section') return;
        const sec = window.plState.currentLayoutDefinition?.sections?.find(s => s.id === secId);
        const isSysInfo = sec?.id === 'sec-system-info' || sec?.title?.toLowerCase().includes('system information');
        if (isSysInfo) return; // System information section cannot accept custom fields

        e.preventDefault();
        e.dataTransfer.dropEffect = 'copy';

        if (window.plState.dragOverSectionId !== secId) {
            window.plState.dragOverSectionId = secId;
            const container = document.getElementById('sec_body_' + secId);
            if (container) {
                document.querySelectorAll('.pl-drop-active').forEach(el => el.classList.remove('pl-drop-active', 'border-indigo-500', 'bg-indigo-50/20'));
                container.classList.add('pl-drop-active', 'border-indigo-500', 'bg-indigo-50/20');
            }
        }
    };

    window.plOnDragLeaveSection = function (e, secId) {
        if (e.currentTarget.contains(e.relatedTarget)) return;
        if (window.plState.dragOverSectionId === secId) {
            window.plState.dragOverSectionId = null;
            const container = document.getElementById('sec_body_' + secId);
            if (container) container.classList.remove('pl-drop-active', 'border-indigo-500', 'bg-indigo-50/20');
        }
    };

    window.plOnDropOnSection = function (e, targetSectionId) {
        e.preventDefault();
        const item = window.plState.draggingItem;
        if (!item || !window.plState.currentLayoutDefinition) return;

        const sec = window.plState.currentLayoutDefinition.sections.find(s => s.id === targetSectionId);
        const isSysInfo = sec?.id === 'sec-system-info' || sec?.title?.toLowerCase().includes('system information');
        if (isSysInfo) return;

        if (item.type === 'available' && item.field) {
            handleAddFieldToSection(item.field, targetSectionId);
        } else if (item.type === 'placed' && item.field && item.sourceSectionId) {
            if (item.sourceSectionId !== targetSectionId) {
                // Move from sourceSection to targetSection
                const updatedSections = window.plState.currentLayoutDefinition.sections.map(s => {
                    if (s.id === item.sourceSectionId) {
                        return { ...s, fields: s.fields.filter(f => f.name !== item.field.name) };
                    }
                    if (s.id === targetSectionId) {
                        if (s.fields.some(f => f.name === item.field.name)) return s;
                        return { ...s, fields: [...s.fields, item.field] };
                    }
                    return s;
                });
                window.plState.currentLayoutDefinition.sections = updatedSections;
            }
        }

        window.plState.draggingItem = null;
        window.plState.dragOverSectionId = null;
        window.plState.dragOverFieldIndex = null;
        renderFieldsTab();
    };

    window.plOnDragOverField = function (e, secId, targetIdx) {
        if (!window.plState.draggingItem) return;
        if (window.plState.draggingItem.type === 'section') return;
        e.preventDefault();
        e.stopPropagation();

        window.plState.dragOverSectionId = secId;
        window.plState.dragOverFieldIndex = targetIdx;

        const el = document.getElementById(`field_card_${secId}_${targetIdx}`);
        if (el) {
            document.querySelectorAll('.pl-field-drop-active').forEach(c => c.classList.remove('pl-field-drop-active', 'ring-2', 'ring-indigo-500'));
            el.classList.add('pl-field-drop-active', 'ring-2', 'ring-indigo-500');
        }
    };

    window.plOnDropOnField = function (e, targetSectionId, targetIndex) {
        e.preventDefault();
        e.stopPropagation();
        const item = window.plState.draggingItem;
        if (!item || !window.plState.currentLayoutDefinition) return;

        if (item.type === 'available' && item.field) {
            const fieldToAdd = {
                name: item.field.name,
                label: item.field.label,
                type: item.field.type,
                required: false,
                readOnly: false
            };
            const updatedSections = window.plState.currentLayoutDefinition.sections.map(s => {
                if (s.id === targetSectionId) {
                    if (s.fields.some(f => f.name === fieldToAdd.name)) return s;
                    const fields = [...s.fields];
                    fields.splice(targetIndex, 0, fieldToAdd);
                    return { ...s, fields };
                }
                return s;
            });
            window.plState.currentLayoutDefinition.sections = updatedSections;
        } else if (item.type === 'placed' && item.field && item.sourceSectionId) {
            const fieldToMove = item.field;
            const updatedSections = window.plState.currentLayoutDefinition.sections.map(s => {
                if (s.id === item.sourceSectionId && s.id === targetSectionId) {
                    const fields = [...s.fields];
                    const [removed] = fields.splice(item.sourceIndex, 1);
                    fields.splice(targetIndex, 0, removed);
                    return { ...s, fields };
                }
                if (s.id === item.sourceSectionId) {
                    return { ...s, fields: s.fields.filter(f => f.name !== fieldToMove.name) };
                }
                if (s.id === targetSectionId) {
                    if (s.fields.some(f => f.name === fieldToMove.name)) return s;
                    const fields = [...s.fields];
                    fields.splice(targetIndex, 0, fieldToMove);
                    return { ...s, fields };
                }
                return s;
            });
            window.plState.currentLayoutDefinition.sections = updatedSections;
        }

        window.plState.draggingItem = null;
        window.plState.dragOverSectionId = null;
        window.plState.dragOverFieldIndex = null;
        renderFieldsTab();
    };

    window.plOnDragOverSectionHeader = function (e, targetSectionIdx) {
        if (!window.plState.draggingItem || window.plState.draggingItem.type !== 'section') return;
        e.preventDefault();
        e.stopPropagation();
        window.plState.dragOverSectionHeaderIndex = targetSectionIdx;
    };

    window.plOnDropSection = function (e, targetSectionIndex) {
        e.preventDefault();
        e.stopPropagation();
        const item = window.plState.draggingItem;
        if (!item || item.type !== 'section' || item.sourceIndex === undefined || !window.plState.currentLayoutDefinition) return;

        const sections = [...window.plState.currentLayoutDefinition.sections];
        const [removed] = sections.splice(item.sourceIndex, 1);
        sections.splice(targetSectionIndex, 0, removed);
        window.plState.currentLayoutDefinition.sections = sections;
        window.plState.draggingItem = null;
        window.plState.dragOverSectionHeaderIndex = null;
        renderFieldsTab();
    };

    function handleAddFieldToSection(field, secId) {
        if (!window.plState.currentLayoutDefinition) return;
        const updated = window.plState.currentLayoutDefinition.sections.map(sec => {
            if (sec.id === secId) {
                if (sec.fields.some(f => f.name === field.name)) return sec;
                return {
                    ...sec,
                    fields: [
                        ...sec.fields,
                        {
                            name: field.name,
                            label: field.label,
                            type: field.type,
                            required: Boolean(field.required),
                            readOnly: false
                        }
                    ]
                };
            }
            return sec;
        });
        window.plState.currentLayoutDefinition.sections = updated;
        renderFieldsTab();
    }

    function handleRemoveField(secId, fieldName) {
        if (!window.plState.currentLayoutDefinition) return;
        const updated = window.plState.currentLayoutDefinition.sections.map(sec => {
            if (sec.id === secId) {
                return { ...sec, fields: sec.fields.filter(f => f.name !== fieldName) };
            }
            return sec;
        });
        window.plState.currentLayoutDefinition.sections = updated;
        renderFieldsTab();
    }

    function handleMoveField(secId, fIdx, dir) {
        if (!window.plState.currentLayoutDefinition) return;
        const targetIdx = dir === 'up' ? fIdx - 1 : fIdx + 1;
        const updated = window.plState.currentLayoutDefinition.sections.map(sec => {
            if (sec.id === secId) {
                if (targetIdx < 0 || targetIdx >= sec.fields.length) return sec;
                const fields = [...sec.fields];
                const tmp = fields[fIdx];
                fields[fIdx] = fields[targetIdx];
                fields[targetIdx] = tmp;
                return { ...sec, fields };
            }
            return sec;
        });
        window.plState.currentLayoutDefinition.sections = updated;
        renderFieldsTab();
    }

    function handleMoveSection(sIdx, dir) {
        if (!window.plState.currentLayoutDefinition) return;
        const sections = [...window.plState.currentLayoutDefinition.sections];
        const targetIdx = dir === 'up' ? sIdx - 1 : sIdx + 1;
        if (targetIdx < 0 || targetIdx >= sections.length) return;
        const tmp = sections[sIdx];
        sections[sIdx] = sections[targetIdx];
        sections[targetIdx] = tmp;
        window.plState.currentLayoutDefinition.sections = sections;
        renderFieldsTab();
    }

    function handleRemoveSection(secId) {
        if (!window.plState.currentLayoutDefinition) return;
        const targetSec = window.plState.currentLayoutDefinition.sections.find(s => s.id === secId);
        const isSysInfo = secId === 'sec-system-info' || targetSec?.title?.toLowerCase().includes('system information');
        const confirmMsg = isSysInfo
            ? 'Hide and remove "System Information" section from this layout? (You can restore it anytime with the System Info button above).'
            : 'Remove this section? Any fields inside will be returned to the Available Fields palette.';
        if (!confirm(confirmMsg)) return;

        window.plState.currentLayoutDefinition.sections = window.plState.currentLayoutDefinition.sections.filter(s => s.id !== secId);
        if (isSysInfo) {
            window.plState.currentLayoutDefinition.showSystemInfo = false;
        }
        renderFieldsTab();
    }

    function handleToggleSectionProp(secId, prop) {
        if (!window.plState.currentLayoutDefinition) return;
        const updated = window.plState.currentLayoutDefinition.sections.map(sec => {
            if (sec.id === secId) {
                if (prop === 'collapsible') {
                    return { ...sec, collapsible: sec.collapsible !== false ? false : true };
                }
                return { ...sec, defaultCollapsed: !sec.defaultCollapsed };
            }
            return sec;
        });
        window.plState.currentLayoutDefinition.sections = updated;
        renderFieldsTab();
    }

    function handleToggleFieldProp(secId, fieldName, prop) {
        if (!window.plState.currentLayoutDefinition) return;
        const updated = window.plState.currentLayoutDefinition.sections.map(sec => {
            if (sec.id === secId) {
                return {
                    ...sec,
                    fields: sec.fields.map(f => {
                        if (f.name === fieldName) {
                            return { ...f, [prop]: !f[prop] };
                        }
                        return f;
                    })
                };
            }
            return sec;
        });
        window.plState.currentLayoutDefinition.sections = updated;
        renderFieldsTab();
    }

    function handleSetSectionColumns(secId, cols) {
        if (!window.plState.currentLayoutDefinition) return;
        const updated = window.plState.currentLayoutDefinition.sections.map(sec => {
            if (sec.id === secId) {
                return { ...sec, columns: cols };
            }
            return sec;
        });
        window.plState.currentLayoutDefinition.sections = updated;
        renderFieldsTab();
    }

    function handleToggleSystemInfo() {
        if (!window.plState.currentLayoutDefinition) return;
        const hasSection = window.plState.currentLayoutDefinition.sections.some(
            s => s.id === 'sec-system-info' || s.title?.toLowerCase().includes('system information')
        );
        const currentlyVisible = window.plState.currentLayoutDefinition.showSystemInfo !== false && hasSection;

        if (!currentlyVisible) {
            const updatedSections = hasSection
                ? [...window.plState.currentLayoutDefinition.sections]
                : [
                    ...window.plState.currentLayoutDefinition.sections,
                    {
                        id: 'sec-system-info',
                        title: 'System Information',
                        columns: 2,
                        collapsible: true,
                        defaultCollapsed: false,
                        fields: []
                    }
                ];
            window.plState.currentLayoutDefinition.showSystemInfo = true;
            window.plState.currentLayoutDefinition.sections = updatedSections;
        } else {
            window.plState.currentLayoutDefinition.showSystemInfo = false;
            window.plState.currentLayoutDefinition.sections = window.plState.currentLayoutDefinition.sections.filter(
                s => s.id !== 'sec-system-info' && !s.title?.toLowerCase().includes('system information')
            );
        }
        renderFieldsTab();
        renderHeader();
    }

    function handleToggleSystemInfoField(secId, fieldKey) {
        if (!window.plState.currentLayoutDefinition) return;
        const updated = window.plState.currentLayoutDefinition.sections.map(sec => {
            if (sec.id === secId) {
                const currentVis = sec.systemInfoFieldsVisibility || {};
                const isCurrentlyVisible = currentVis[fieldKey] !== false;
                return {
                    ...sec,
                    systemInfoFieldsVisibility: {
                        ...currentVis,
                        [fieldKey]: !isCurrentlyVisible
                    }
                };
            }
            return sec;
        });
        window.plState.currentLayoutDefinition.sections = updated;
        renderFieldsTab();
    }

    // =========================================================================
    // CRUD API HANDLERS
    // =========================================================================

    async function handleSaveLayout() {
        if (!window.plState.selectedLayoutId || !window.plState.currentLayoutDefinition) return;
        window.plState.saving = true;
        renderHeader();
        try {
            const res = await fetch(`/api/page-layouts/${encodeURIComponent(window.plState.selectedModule)}/${encodeURIComponent(window.plState.selectedLayoutId)}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                },
                body: JSON.stringify({
                    name: window.plState.currentLayoutName.trim(),
                    description: window.plState.currentLayoutDesc.trim(),
                    layout: window.plState.currentLayoutDefinition
                })
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Error saving layout');
            setFeedback('Page layout saved successfully!');
            await loadLayoutsForModule(window.plState.selectedModule);
        } catch (err) {
            console.error('Error saving page layout:', err);
            alert('Error saving layout: ' + err.message);
        } finally {
            window.plState.saving = false;
            renderHeader();
        }
    }

    async function handleSetDefault() {
        if (!window.plState.selectedLayoutId) return;
        try {
            const res = await fetch(`/api/page-layouts/${encodeURIComponent(window.plState.selectedModule)}/${encodeURIComponent(window.plState.selectedLayoutId)}/set-default`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                }
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Failed to set default layout');
            setFeedback('Set as default layout.');
            await loadLayoutsForModule(window.plState.selectedModule);
        } catch (err) {
            console.error('Error setting default layout:', err);
            alert('Error setting default layout: ' + err.message);
        }
    }

    async function handleDeleteLayout() {
        if (!window.plState.selectedLayoutId) return;
        const cur = window.plState.layouts.find(l => l.id === window.plState.selectedLayoutId);
        if (!confirm(`Are you sure you want to delete the layout "${cur?.name}"?`)) return;

        window.plState.saving = true;
        renderHeader();
        try {
            const res = await fetch(`/api/page-layouts/${encodeURIComponent(window.plState.selectedModule)}/${encodeURIComponent(window.plState.selectedLayoutId)}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                }
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Failed to delete layout');
            setFeedback('Layout deleted.');
            await loadLayoutsForModule(window.plState.selectedModule);
        } catch (err) {
            console.error('Error deleting layout:', err);
            alert('Error deleting layout: ' + err.message);
        } finally {
            window.plState.saving = false;
            renderHeader();
        }
    }

    async function handleSaveAssignments() {
        window.plState.saving = true;
        try {
            const res = await fetch(`/api/page-layouts/${encodeURIComponent(window.plState.selectedModule)}/assignments`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                },
                body: JSON.stringify({ assignments: window.plState.workingAssignments })
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Failed to save assignments');
            window.plState.isAssignmentModalOpen = false;
            setFeedback('Profile layout assignments updated!');
            await loadLayoutsForModule(window.plState.selectedModule);
        } catch (err) {
            console.error('Error saving assignments:', err);
            alert('Error saving assignments: ' + err.message);
        } finally {
            window.plState.saving = false;
            renderHeader();
        }
    }

    // =========================================================================
    // RENDERING FUNCTIONS (1:1 TAILWIND CSS REPLICATION OF REACT UI)
    // =========================================================================

    function renderHeader() {
        const root = document.getElementById('plHeaderControls');
        if (!root) return;

        const curLayout = window.plState.layouts.find(l => l.id === window.plState.selectedLayoutId);
        const isDefault = Boolean(curLayout?.is_default);
        const assignedCount = curLayout?.assigned_profiles_count || 0;

        const isFilesPrivateAssigned = window.plState.currentLayoutDefinition?.components?.filesPrivateAssigned === true;
        const isApprovalHistoryAssigned = window.plState.currentLayoutDefinition?.components?.approvalHistoryAssigned === true;
        const isFieldHistoryAssigned = (window.plState.currentLayoutDefinition?.components?.fieldHistoryAssigned !== false);
        const trackedCount = window.plState.fieldHistoryConfig?.tracked_fields?.length || 0;

        root.innerHTML = `
            <!-- Unified Compact Control Header -->
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-3 shadow-sm flex flex-wrap items-center justify-between gap-3">
                
                <!-- Left: Studio Title & Target Object Selector -->
                <div class="flex items-center gap-3.5 flex-wrap">
                    <!-- Title & Icon -->
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-600 to-indigo-800 flex items-center justify-center text-white shadow-sm flex-shrink-0">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <rect width="18" height="18" x="3" y="3" rx="2"/>
                                <path d="M3 9h18"/>
                                <path d="M9 21V9"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm font-bold text-zinc-950 dark:text-zinc-50 leading-tight">
                                Page Layouts Designer
                            </div>
                            <div class="text-[11px] text-zinc-500 dark:text-zinc-400 leading-tight">
                                Layout Engine • Multi-Profile Fields &amp; Rules
                            </div>
                        </div>
                    </div>

                    <div class="h-6 w-px bg-zinc-200 dark:bg-zinc-700 hidden sm:block"></div>

                    <!-- Target Object Selector -->
                    <div class="flex items-center gap-1.5">
                        <span class="text-[11.5px] font-semibold text-zinc-500 uppercase tracking-wider">
                            TARGET OBJECT:
                        </span>
                        <select id="plModuleSelect" class="bg-zinc-50 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 rounded-md text-xs font-semibold px-2.5 py-1 text-zinc-900 dark:text-zinc-100 min-w-[170px] focus:ring-1 focus:ring-indigo-500 focus:outline-none">
                            <optgroup label="Standard CRM Objects">
                                ${CORE_OBJECTS.map(obj => `
                                    <option value="${obj.id}" ${window.plState.selectedModule === obj.id ? 'selected' : ''}>${obj.label}</option>
                                `).join('')}
                            </optgroup>
                            ${window.plState.customObjects.length > 0 ? `
                                <optgroup label="Custom Objects (__c)">
                                    ${window.plState.customObjects.map(co => `
                                        <option value="${co.api_name}" ${window.plState.selectedModule === co.api_name ? 'selected' : ''}>${co.label} (${co.api_name})</option>
                                    `).join('')}
                                </optgroup>
                            ` : ''}
                        </select>
                    </div>

                    <div class="h-6 w-px bg-zinc-200 dark:bg-zinc-700 hidden md:block"></div>

                    <!-- Active Layout Switcher & Status -->
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-[11.5px] font-semibold text-zinc-500 uppercase tracking-wider">
                            ACTIVE LAYOUT:
                        </span>
                        <select id="plLayoutSelect" class="bg-zinc-50 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 rounded-md text-xs font-semibold px-2.5 py-1 text-zinc-900 dark:text-zinc-100 min-w-[190px] focus:ring-1 focus:ring-indigo-500 focus:outline-none">
                            ${window.plState.layouts.map(l => `
                                <option value="${l.id}" ${window.plState.selectedLayoutId === l.id ? 'selected' : ''}>
                                    ${escapeHtml(l.name)} ${l.is_default ? '★ (Default)' : ''}
                                </option>
                            `).join('')}
                        </select>

                        ${isDefault ? `
                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-800 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 px-2 py-0.5 rounded">
                                <svg class="w-2.5 h-2.5 fill-current" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                <span>Default</span>
                            </span>
                        ` : `
                            <button type="button" id="plSetDefaultBtn" class="inline-flex items-center gap-1 text-xs font-medium text-zinc-600 dark:text-zinc-300 hover:text-indigo-600 border border-zinc-300 dark:border-zinc-700 rounded px-2 py-0.5 bg-zinc-50 dark:bg-zinc-800" title="Make this the default layout">
                                <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                <span>Make Default</span>
                            </button>
                        `}

                        <span class="text-[11px] font-medium text-zinc-600 dark:text-zinc-400 bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 px-2 py-0.5 rounded">
                            ${assignedCount} Profile${assignedCount === 1 ? '' : 's'} Assigned
                        </span>

                        <!-- Fast Toggles -->
                        <button type="button" id="plToggleFilesPrivateBtn" class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded border transition-colors ${isFilesPrivateAssigned ? 'bg-purple-50 dark:bg-purple-950/40 border-purple-300 dark:border-purple-800 text-purple-700 dark:text-purple-300' : 'bg-zinc-100 dark:bg-zinc-800 border-zinc-300 dark:border-zinc-700 text-zinc-500'}">
                            <svg class="w-2.5 h-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            <span>Files Private: ${isFilesPrivateAssigned ? 'Assigned' : 'Unassigned (Hidden)'}</span>
                        </button>

                        <button type="button" id="plToggleApprovalHistoryBtn" class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded border transition-colors ${isApprovalHistoryAssigned ? 'bg-orange-50 dark:bg-orange-950/40 border-orange-300 dark:border-orange-800 text-orange-700 dark:text-orange-300' : 'bg-zinc-100 dark:bg-zinc-800 border-zinc-300 dark:border-zinc-700 text-zinc-500'}">
                            <svg class="w-2.5 h-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
                            <span>Approval History: ${isApprovalHistoryAssigned ? 'Assigned' : 'Unassigned (Hidden)'}</span>
                        </button>

                        <button type="button" id="plOpenFieldHistoryBtn" class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded border transition-colors ${isFieldHistoryAssigned ? 'bg-sky-50 dark:bg-sky-950/40 border-sky-300 dark:border-sky-800 text-sky-700 dark:text-sky-300' : 'bg-zinc-100 dark:bg-zinc-800 border-zinc-300 dark:border-zinc-700 text-zinc-500'}">
                            <svg class="w-2.5 h-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 14 14"/></svg>
                            <span>Field History: ${trackedCount} fields ${isFieldHistoryAssigned ? '(Assigned)' : '(Hidden)'}</span>
                        </button>
                    </div>
                </div>

                <!-- Right: Action Buttons -->
                <div class="flex items-center gap-1.5 flex-wrap">
                    <button type="button" id="plSetFieldHistoryBtn" class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1.5 rounded-lg border border-sky-300 dark:border-sky-800 bg-sky-50 dark:bg-sky-950/40 text-sky-700 dark:text-sky-300 hover:bg-sky-100">
                        <svg class="w-3.5 h-3.5 text-sky-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 14 14"/></svg>
                        <span>Set Field History Tracking</span>
                    </button>

                    <button type="button" id="plOpenAssignmentsModalBtn" class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-750">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        <span>Profile Assignments</span>
                    </button>

                    <button type="button" id="plCloneLayoutBtn" class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50" title="Clone active layout structure">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>
                        <span>Clone</span>
                    </button>

                    <button type="button" id="plNewLayoutBtn" class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        <span>New Layout</span>
                    </button>

                    ${window.plState.layouts.length > 1 ? `
                        <button type="button" id="plDeleteLayoutBtn" class="text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/40 p-1.5 rounded-lg transition-colors" title="Delete this layout">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                        </button>
                    ` : ''}

                    <button type="button" id="plSaveLayoutBtn" ${window.plState.saving ? 'disabled' : ''} class="inline-flex items-center justify-center gap-1.5 text-xs font-semibold px-3.5 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm min-w-[105px]">
                        ${window.plState.saving ? `
                            <svg class="w-3.5 h-3.5 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                            <span>Saving...</span>
                        ` : `
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                            <span>Save Layout</span>
                        `}
                    </button>
                </div>
            </div>

            <!-- Feedback Banner -->
            ${window.plState.feedback ? `
                <div class="mt-2.5 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-300 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 px-3.5 py-2 rounded-lg flex items-center gap-2 text-xs font-semibold animate-fade-in">
                    <svg class="w-4 h-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    <span>${escapeHtml(window.plState.feedback)}</span>
                </div>
            ` : ''}
        `;

        // Wire Header Event Listeners
        document.getElementById('plModuleSelect')?.addEventListener('change', (e) => {
            window.plState.selectedModule = e.target.value;
            loadLayoutsForModule(e.target.value);
        });

        document.getElementById('plLayoutSelect')?.addEventListener('change', (e) => {
            const l = window.plState.layouts.find(x => x.id === e.target.value);
            if (l) selectLayout(l);
        });

        document.getElementById('plSetDefaultBtn')?.addEventListener('click', handleSetDefault);
        document.getElementById('plDeleteLayoutBtn')?.addEventListener('click', handleDeleteLayout);
        document.getElementById('plSaveLayoutBtn')?.addEventListener('click', handleSaveLayout);

        document.getElementById('plNewLayoutBtn')?.addEventListener('click', () => {
            window.plState.newLayoutName = '';
            window.plState.newLayoutDesc = '';
            window.plState.newLayoutCloneFrom = '';
            window.plState.isNewLayoutModalOpen = true;
            renderModals();
        });

        document.getElementById('plCloneLayoutBtn')?.addEventListener('click', () => {
            window.plState.newLayoutName = window.plState.currentLayoutName + ' Copy';
            window.plState.newLayoutDesc = window.plState.currentLayoutDesc;
            window.plState.newLayoutCloneFrom = window.plState.selectedLayoutId;
            window.plState.isNewLayoutModalOpen = true;
            renderModals();
        });

        document.getElementById('plOpenAssignmentsModalBtn')?.addEventListener('click', () => {
            window.plState.workingAssignments = { ...window.plState.profileAssignments };
            window.plState.isAssignmentModalOpen = true;
            renderModals();
        });

        document.getElementById('plSetFieldHistoryBtn')?.addEventListener('click', openFieldHistoryModal);
        document.getElementById('plOpenFieldHistoryBtn')?.addEventListener('click', openFieldHistoryModal);

        document.getElementById('plToggleFilesPrivateBtn')?.addEventListener('click', () => {
            if (!window.plState.currentLayoutDefinition) return;
            const cur = window.plState.currentLayoutDefinition.components?.filesPrivateAssigned === true;
            window.plState.currentLayoutDefinition.components = window.plState.currentLayoutDefinition.components || {};
            window.plState.currentLayoutDefinition.components.filesPrivateAssigned = !cur;
            renderHeader();
        });

        document.getElementById('plToggleApprovalHistoryBtn')?.addEventListener('click', () => {
            if (!window.plState.currentLayoutDefinition) return;
            const cur = window.plState.currentLayoutDefinition.components?.approvalHistoryAssigned === true;
            window.plState.currentLayoutDefinition.components = window.plState.currentLayoutDefinition.components || {};
            window.plState.currentLayoutDefinition.components.approvalHistoryAssigned = !cur;
            renderHeader();
        });
    }

    function openFieldHistoryModal() {
        window.plState.workingTrackedFields = [...(window.plState.fieldHistoryConfig?.tracked_fields || [])];
        window.plState.fieldHistorySearch = '';
        window.plState.isFieldHistoryModalOpen = true;
        renderModals();
    }

    function renderSubTabsStrip() {
        const root = document.getElementById('plSubTabsStrip');
        if (!root) return;

        const tabs = [
            {
                id: 'fields',
                label: 'Layout Canvas & Sections',
                icon: '<svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="4" y1="21" x2="4" y2="14"/><line x1="4" y1="10" x2="4" y2="3"/><line x1="12" y1="21" x2="12" y2="12"/><line x1="12" y1="8" x2="12" y2="3"/><line x1="20" y1="21" x2="20" y2="16"/><line x1="20" y1="12" x2="20" y2="3"/><line x1="1" y1="14" x2="7" y2="14"/><line x1="9" y1="8" x2="15" y2="8"/><line x1="17" y1="16" x2="23" y2="16"/></svg>'
            },
            {
                id: 'dependent_fields',
                label: 'Dependent Fields',
                count: window.plState.currentLayoutDefinition?.dependentFields?.length || 0,
                icon: '<svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="18" r="3"/><circle cx="6" cy="6" r="3"/><circle cx="18" cy="6" r="3"/><path d="M18 9v1a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V9"/><path d="M12 12v3"/></svg>'
            },
            {
                id: 'validation_rules',
                label: 'Validation Rules',
                count: window.plState.currentLayoutDefinition?.validationRules?.length || 0,
                icon: '<svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>'
            },
            {
                id: 'related',
                label: 'Related Lists',
                count: window.plState.currentLayoutDefinition?.relatedLists?.filter(r => r.enabled)?.length || 0,
                icon: '<svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/><path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/><path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/><path d="M10 6h4"/><path d="M10 10h4"/><path d="M10 14h4"/><path d="M10 18h4"/></svg>'
            },
            {
                id: 'components',
                label: 'Workspace Arrangement',
                icon: '<svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>'
            },
            {
                id: 'activities',
                label: 'Activity Composer',
                icon: '<svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>'
            }
        ];

        root.innerHTML = `
            <div class="flex items-center gap-1 bg-zinc-100 dark:bg-zinc-800 p-1 rounded-lg border border-zinc-200 dark:border-zinc-700 flex-wrap">
                ${tabs.map(tab => {
                    const isSel = window.plState.designerTab === tab.id;
                    return `
                        <button type="button" data-tab="${tab.id}" class="pl-subtab-btn inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-semibold transition-all ${isSel ? 'bg-white dark:bg-zinc-900 text-zinc-950 dark:text-zinc-50 shadow-sm border border-zinc-200/80 dark:border-zinc-700' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-200 hover:bg-zinc-200/50 dark:hover:bg-zinc-750'}">
                            ${tab.icon}
                            <span>${tab.label}</span>
                            ${tab.count !== undefined && tab.count > 0 ? `
                                <span class="text-[10px] px-1.5 py-0.2 rounded-full font-bold ${isSel ? 'bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300' : 'bg-zinc-200 dark:bg-zinc-700 text-zinc-600 dark:text-zinc-300'}">
                                    ${tab.count}
                                </span>
                            ` : ''}
                        </button>
                    `;
                }).join('')}
            </div>
        `;

        root.querySelectorAll('.pl-subtab-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                window.plState.designerTab = btn.getAttribute('data-tab');
                renderSubTabsStrip();
                renderTabContent();
            });
        });
    }

    function renderTabContent() {
        const root = document.getElementById('plTabContent');
        if (!root) return;

        if (!window.plState.currentLayoutDefinition) {
            root.innerHTML = `
                <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-12 text-center text-zinc-500">
                    No layout selected. Please choose or create a layout above.
                </div>
            `;
            return;
        }

        switch (window.plState.designerTab) {
            case 'fields':
                renderFieldsTab();
                break;
            case 'dependent_fields':
                renderDependentFieldsTab();
                break;
            case 'validation_rules':
                renderValidationRulesTab();
                break;
            case 'related':
                renderRelatedListsTab();
                break;
            case 'components':
                renderComponentsTab();
                break;
            case 'activities':
                renderActivitiesTab();
                break;
        }
    }

    // =========================================================================
    // TAB 1: FIELDS CANVAS & AVAILABLE PALETTE (FULL DRAG AND DROP)
    // =========================================================================

    function renderFieldsTab() {
        const root = document.getElementById('plTabContent');
        if (!root) return;

        const unusedFields = getUnusedFields();
        const def = window.plState.currentLayoutDefinition;
        if (!def) return;

        let totalPlacedFields = 0;
        (def.sections || []).forEach(s => totalPlacedFields += (s.fields || []).length);
        const isSystemInfoVisible = def.showSystemInfo !== false && def.sections.some(s => s.id === 'sec-system-info' || s.title?.toLowerCase().includes('system information'));

        root.innerHTML = `
            <div class="grid grid-cols-1 lg:grid-cols-[270px_1fr] gap-3.5 items-start">
                
                <!-- Left Palette: Available / Unused Fields -->
                <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-3 shadow-sm flex flex-col gap-2.5 lg:sticky lg:top-4 max-h-[calc(100vh-160px)]">
                    
                    <!-- Palette Header -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-1.5">
                            <span class="text-xs font-bold text-zinc-950 dark:text-zinc-50">Available Fields</span>
                            <span class="text-[10px] font-semibold text-zinc-600 dark:text-zinc-400 bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 px-1.5 py-0.5 rounded-full">
                                ${unusedFields.length}
                            </span>
                        </div>
                        <span class="text-[11px] text-zinc-400">⠿ Drag or Add</span>
                    </div>

                    <!-- Filter Pills: All | Standard | Custom -->
                    <div class="flex gap-1 bg-zinc-100 dark:bg-zinc-800 p-0.5 rounded-md">
                        ${['all', 'standard', 'custom'].map(cat => `
                            <button type="button" data-cat="${cat}" class="pl-cat-btn flex-1 py-1 text-[11px] font-semibold rounded capitalize transition-all ${window.plState.fieldCategoryFilter === cat ? 'bg-white dark:bg-zinc-900 text-zinc-950 dark:text-zinc-50 shadow-xs' : 'text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200'}">
                                ${cat}
                            </button>
                        `).join('')}
                    </div>

                    <!-- Field Search -->
                    <div class="relative">
                        <svg class="w-3.5 h-3.5 absolute left-2 top-2 text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text" id="plFieldSearchInput" placeholder="Filter fields..." value="${escapeHtml(window.plState.fieldSearch)}" class="w-full pl-7 pr-6 py-1 text-xs bg-zinc-50 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 rounded-md focus:ring-1 focus:ring-indigo-500 focus:outline-none text-zinc-900 dark:text-zinc-100">
                        ${window.plState.fieldSearch ? `
                            <button type="button" id="plClearSearchBtn" class="absolute right-2 top-1.5 text-zinc-400 hover:text-zinc-600">
                                <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            </button>
                        ` : ''}
                    </div>

                    <!-- Draggable Unused Fields List -->
                    <div class="flex flex-col gap-1.5 overflow-y-auto max-h-[calc(100vh-280px)] pr-0.5" id="plUnusedFieldsList">
                        ${unusedFields.length === 0 ? `
                            <div class="py-6 text-center text-xs text-zinc-400">
                                ${window.plState.allAvailableFields.length === 0 ? 'No fields loaded.' : 'All matching fields placed on layout.'}
                            </div>
                        ` : unusedFields.map(f => `
                            <div draggable="true"
                                 ondragstart="window.plOnDragStartAvailable(event, '${f.name}')"
                                 ondragend="window.plOnDragEnd(event)"
                                 class="p-2 bg-zinc-50 dark:bg-zinc-850 hover:bg-indigo-50/50 dark:hover:bg-indigo-950/30 border border-zinc-200 dark:border-zinc-750 hover:border-indigo-400 dark:hover:border-indigo-600 rounded-md flex items-center justify-between text-xs cursor-grab select-none transition-all"
                                 title="Drag to section canvas or use dropdown to add">
                                <div class="flex items-center gap-1.5 overflow-hidden">
                                    <svg class="w-3.5 h-3.5 text-zinc-400 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="12" r="1"/><circle cx="9" cy="5" r="1"/><circle cx="9" cy="19" r="1"/><circle cx="15" cy="12" r="1"/><circle cx="15" cy="5" r="1"/><circle cx="15" cy="19" r="1"/></svg>
                                    <div class="overflow-hidden">
                                        <div class="font-semibold text-zinc-900 dark:text-zinc-100 truncate text-[12px]">
                                            ${escapeHtml(f.label)}
                                        </div>
                                        <div class="text-[10px] text-zinc-500 font-mono flex items-center gap-1">
                                            <span>${escapeHtml(f.name)}</span>
                                            ${f.isCustom ? `
                                                <span class="text-[9px] font-semibold text-indigo-700 dark:text-indigo-300 bg-indigo-100 dark:bg-indigo-950 px-1 py-0.2 rounded">Custom</span>
                                            ` : ''}
                                        </div>
                                    </div>
                                </div>

                                <!-- Quick Add Dropdown -->
                                <select data-field-name="${f.name}" class="pl-quick-add-select text-[10px] py-0.5 px-1 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 rounded text-zinc-700 dark:text-zinc-200 max-w-[85px] flex-shrink-0 focus:outline-none">
                                    <option value="" disabled selected>+ Add...</option>
                                    ${def.sections.filter(s => s.id !== 'sec-system-info' && !s.title?.toLowerCase().includes('system information')).map(sec => `
                                        <option value="${sec.id}">${escapeHtml(sec.title)}</option>
                                    `).join('')}
                                </select>
                            </div>
                        `).join('')}
                    </div>
                </div>

                <!-- Right Canvas: Sections & Placed Fields -->
                <div class="flex flex-col gap-3">
                    
                    <!-- Canvas Sub-Header -->
                    <div class="flex justify-between items-center px-1 flex-wrap gap-2">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-bold text-zinc-900 dark:text-zinc-50">Layout Sections Canvas</span>
                            <span class="text-xs text-zinc-500 dark:text-zinc-400">
                                (${def.sections.length} Section${def.sections.length === 1 ? '' : 's'} • ${totalPlacedFields} Field${totalPlacedFields === 1 ? '' : 's'} Placed)
                            </span>
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="button" id="plToggleSysInfoBtn" class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-lg border transition-all ${isSystemInfoVisible ? 'bg-indigo-50 dark:bg-indigo-950/40 border-indigo-200 dark:border-indigo-800 text-indigo-700 dark:text-indigo-300' : 'bg-zinc-50 dark:bg-zinc-800 border-dashed border-zinc-300 dark:border-zinc-700 text-zinc-600 dark:text-zinc-400'}" title="${isSystemInfoVisible ? 'Click to hide System Information' : 'Click to show System Information'}">
                                <svg class="w-3.5 h-3.5 ${isSystemInfoVisible ? 'text-indigo-600' : 'text-zinc-400'}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
                                <span>System Info: <strong>${isSystemInfoVisible ? 'Visible 👁️' : 'Hidden ✕'}</strong></span>
                            </button>

                            <button type="button" id="plAddSectionBtn" class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 hover:bg-zinc-50">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                <span>Add Section</span>
                            </button>
                        </div>
                    </div>

                    <!-- Live Record Detail Header Actions Strip Preview -->
                    <div class="bg-slate-50 dark:bg-zinc-850 border border-slate-200 dark:border-zinc-800 rounded-lg p-2.5 flex items-center justify-between flex-wrap gap-2">
                        <div class="flex items-center gap-2">
                            <span class="text-[11px] font-bold text-zinc-500 uppercase tracking-wider">Header Actions Strip:</span>
                            <span class="text-[11px] font-medium bg-zinc-200 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-300 px-2 py-0.5 rounded">Standard: Edit • Clone • Print • Delete</span>
                        </div>
                        <div class="flex items-center gap-1.5 flex-wrap">
                            ${(() => {
                                const assignedIds = def.components?.customButtons || [];
                                const btns = assignedIds.map(id => window.plState.availableCustomButtons.find(b => b.id === id)).filter(Boolean);
                                if (btns.length === 0) {
                                    return '<span class="text-xs text-zinc-400 italic">No custom buttons assigned to header yet.</span>';
                                }
                                return btns.map(b => `
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-semibold bg-indigo-600 text-white">
                                        <span>${escapeHtml(b.label)}</span>
                                    </span>
                                `).join('');
                            })()}
                        </div>
                    </div>

                    <!-- Sections Mapping -->
                    <div class="flex flex-col gap-3" id="plSectionsContainer">
                        ${def.sections.map((sec, sIdx) => {
                            const isSysInfo = sec.id === 'sec-system-info' || sec.title?.toLowerCase().includes('system information');
                            const isEditingTitle = window.plState.editingSectionId === sec.id;

                            return `
                                <div id="sec_card_${sec.id}"
                                     ondragover="window.plOnDragOverSection(event, '${sec.id}')"
                                     ondragleave="window.plOnDragLeaveSection(event, '${sec.id}')"
                                     ondrop="window.plOnDropOnSection(event, '${sec.id}')"
                                     class="bg-white dark:bg-zinc-900 border ${isSysInfo ? 'border-indigo-200 dark:border-indigo-900/60' : 'border-zinc-200 dark:border-zinc-800'} rounded-xl p-3 shadow-xs flex flex-col gap-2.5 transition-all">
                                    
                                    <!-- Section Header -->
                                    <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-2 flex-wrap gap-2">
                                        <div class="flex items-center gap-2">
                                            <!-- Section Drag Handle -->
                                            <div draggable="true"
                                                 ondragstart="window.plOnDragStartSection(event, ${sIdx})"
                                                 ondragover="window.plOnDragOverSectionHeader(event, ${sIdx})"
                                                 ondrop="window.plOnDropSection(event, ${sIdx})"
                                                 class="cursor-grab text-zinc-400 hover:text-zinc-600 flex items-center"
                                                 title="Drag section to reorder">
                                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="12" r="1"/><circle cx="9" cy="5" r="1"/><circle cx="9" cy="19" r="1"/><circle cx="15" cy="12" r="1"/><circle cx="15" cy="5" r="1"/><circle cx="15" cy="19" r="1"/></svg>
                                            </div>

                                            ${isSysInfo ? `
                                                <svg class="w-4 h-4 text-indigo-600 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
                                            ` : ''}

                                            ${isEditingTitle ? `
                                                <div class="flex items-center gap-1">
                                                    <input type="text" id="sec_title_input_${sec.id}" value="${escapeHtml(window.plState.editingSectionTitle)}" class="text-xs px-2 py-0.5 bg-zinc-50 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 rounded focus:outline-none">
                                                    <button type="button" data-save-title="${sec.id}" class="p-1 text-emerald-600 hover:bg-emerald-50 rounded">
                                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                                                    </button>
                                                    <button type="button" data-cancel-title="${sec.id}" class="p-1 text-zinc-400 hover:bg-zinc-100 rounded">
                                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                                    </button>
                                                </div>
                                            ` : `
                                                <div class="flex items-center gap-1.5">
                                                    <span class="font-bold text-xs text-zinc-950 dark:text-zinc-50">${escapeHtml(sec.title)}</span>
                                                    ${isSysInfo ? `
                                                        <span class="text-[9.5px] font-bold text-indigo-700 dark:text-indigo-300 bg-indigo-100 dark:bg-indigo-950/60 px-1.5 py-0.2 rounded uppercase">System Audit</span>
                                                    ` : ''}
                                                    <button type="button" data-edit-title="${sec.id}" class="text-zinc-400 hover:text-zinc-600 p-0.5 rounded" title="Rename section">
                                                        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                                                    </button>
                                                </div>
                                            `}

                                            <!-- Column Layout Switcher (1-col vs 2-col vs 3-col) -->
                                            <div class="inline-flex bg-zinc-100 dark:bg-zinc-800 p-0.5 rounded">
                                                ${[1, 2, 3].map(c => `
                                                    <button type="button" data-set-cols="${sec.id}" data-cols="${c}" class="px-1.5 py-0.2 text-[10px] font-semibold rounded ${sec.columns === c ? 'bg-white dark:bg-zinc-900 text-zinc-950 dark:text-zinc-50 shadow-xs' : 'text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200'}" title="${c} Column Layout">
                                                        ${c}-Col
                                                    </button>
                                                `).join('')}
                                            </div>

                                            ${!isSysInfo ? `
                                                <span class="text-[11px] text-zinc-400">(${sec.fields.length} field${sec.fields.length === 1 ? '' : 's'})</span>
                                            ` : ''}
                                        </div>

                                        <!-- Section Config Controls -->
                                        <div class="flex items-center gap-2.5 flex-wrap">
                                            <label class="inline-flex items-center gap-1 text-[11px] text-zinc-600 dark:text-zinc-400 cursor-pointer">
                                                <input type="checkbox" data-sec-collapsible="${sec.id}" ${sec.collapsible !== false ? 'checked' : ''} class="w-3 h-3 accent-indigo-600">
                                                <span>Collapsible</span>
                                            </label>

                                            ${sec.collapsible !== false ? `
                                                <label class="inline-flex items-center gap-1 text-[11px] text-zinc-600 dark:text-zinc-400 cursor-pointer">
                                                    <input type="checkbox" data-sec-collapsed="${sec.id}" ${sec.defaultCollapsed ? 'checked' : ''} class="w-3 h-3 accent-indigo-600">
                                                    <span>Default Collapsed</span>
                                                </label>
                                            ` : ''}

                                            <button type="button" data-sec-visibility="${sec.id}" class="inline-flex items-center gap-1 text-[11px] px-1.5 py-0.5 rounded border transition-colors ${sec.visibilityRule?.enabled ? 'bg-blue-50 dark:bg-blue-950/40 border-blue-200 text-blue-700 dark:text-blue-300' : 'bg-zinc-100 dark:bg-zinc-800 border-zinc-200 dark:border-zinc-700 text-zinc-600 dark:text-zinc-400'}" title="Configure conditional visibility rule for this section">
                                                <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                                <span>${sec.visibilityRule?.enabled ? 'Rule Active' : 'Visibility'}</span>
                                            </button>

                                            <div class="flex items-center gap-0.5">
                                                <button type="button" data-move-sec="${sIdx}" data-dir="up" ${sIdx === 0 ? 'disabled' : ''} class="p-1 text-zinc-400 hover:text-zinc-700 disabled:opacity-30" title="Move Section Up">
                                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m18 15-6-6-6 6"/></svg>
                                                </button>
                                                <button type="button" data-move-sec="${sIdx}" data-dir="down" ${sIdx === def.sections.length - 1 ? 'disabled' : ''} class="p-1 text-zinc-400 hover:text-zinc-700 disabled:opacity-30" title="Move Section Down">
                                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
                                                </button>
                                                <button type="button" data-remove-sec="${sec.id}" class="p-1 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/40 rounded" title="${isSysInfo ? 'Hide System Info' : 'Remove Section'}">
                                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/></svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Section Body (Drop Target) -->
                                    <div id="sec_body_${sec.id}" class="transition-all rounded-lg p-1.5 min-h-[60px]">
                                        ${isSysInfo ? `
                                            <!-- System Audit Attributes Preview -->
                                            <div class="flex flex-col gap-2">
                                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-${sec.columns || 2} gap-2">
                                                    ${[
                                                        { key: 'owner', label: 'Current Owner', api: 'assigned_to / owner', icon: '👤', desc: 'Active assigned user with ownership transfer action' },
                                                        { key: 'created_at', label: 'Created Date & Time', api: 'created_at', icon: '📅', desc: 'Formatted record creation timestamp' },
                                                        { key: 'created_by', label: 'Created By', api: 'created_by', icon: '👤', desc: 'Creator user full name or System Admin' },
                                                        { key: 'updated_at', label: 'Last Modified Date & Time', api: 'updated_at', icon: '⏰', desc: 'Last record update timestamp' },
                                                        { key: 'updated_by', label: 'Last Modified By', api: 'updated_by', icon: '👤', desc: 'User who last modified the record' },
                                                        { key: 'id', label: 'Record ID', api: 'id', icon: '🆔', desc: 'Unique system record identifier' }
                                                    ].map(auditField => {
                                                        const isFieldVisible = (sec.systemInfoFieldsVisibility?.[auditField.key] ?? true) !== false;
                                                        return `
                                                            <div class="p-2 ${isFieldVisible ? 'bg-white dark:bg-zinc-800 border-zinc-200 dark:border-zinc-700' : 'bg-zinc-50 dark:bg-zinc-850 border-dashed border-zinc-300 dark:border-zinc-800 opacity-65'} border rounded-md flex flex-col gap-1 transition-all">
                                                                <div class="flex items-center justify-between gap-1.5">
                                                                    <div class="flex items-center gap-1.5 min-w-0">
                                                                        <span class="text-xs font-semibold ${isFieldVisible ? 'text-zinc-900 dark:text-zinc-100' : 'text-zinc-500'}">
                                                                            ${auditField.icon} ${auditField.label}
                                                                        </span>
                                                                        <span class="text-[10px] text-zinc-400 font-mono">(${auditField.api})</span>
                                                                    </div>
                                                                    <button type="button" data-toggle-sys-field="${auditField.key}" data-sec-id="${sec.id}" class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[11px] font-semibold border ${isFieldVisible ? 'bg-emerald-50 dark:bg-emerald-950/40 border-emerald-300 text-emerald-700 dark:text-emerald-300' : 'bg-zinc-100 dark:bg-zinc-800 border-zinc-200 text-zinc-500'}">
                                                                        <span>${isFieldVisible ? 'Visible 👁️' : 'Hidden ✕'}</span>
                                                                    </button>
                                                                </div>
                                                                <div class="text-[10.5px] text-zinc-500 dark:text-zinc-400">${auditField.desc}</div>
                                                            </div>
                                                        `;
                                                    }).join('')}
                                                </div>
                                                <div class="text-[11px] text-zinc-600 dark:text-zinc-400 bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200 dark:border-zinc-700/60 rounded p-2 flex items-center gap-2">
                                                    <svg class="w-3.5 h-3.5 text-zinc-500 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
                                                    <span><strong>Field Visibility Governance:</strong> Configure which system fields appear on detail pages using the Eye toggles above.</span>
                                                </div>
                                            </div>
                                        ` : sec.fields.length === 0 ? `
                                            <div class="p-6 text-center border-2 border-dashed border-zinc-200 dark:border-zinc-800 rounded-lg text-zinc-400 text-xs">
                                                Drag fields from the left palette or select "+ Add..." to place attributes into this section.
                                            </div>
                                        ` : `
                                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-${sec.columns || 2} gap-2">
                                                ${sec.fields.map((f, fIdx) => `
                                                    <div id="field_card_${sec.id}_${fIdx}"
                                                         draggable="true"
                                                         ondragstart="window.plOnDragStartPlaced(event, '${sec.id}', ${fIdx})"
                                                         ondragover="window.plOnDragOverField(event, '${sec.id}', ${fIdx})"
                                                         ondrop="window.plOnDropOnField(event, '${sec.id}', ${fIdx})"
                                                         ondragend="window.plOnDragEnd(event)"
                                                         class="p-2 bg-zinc-50 dark:bg-zinc-850 border border-zinc-200 dark:border-zinc-750 hover:border-zinc-350 rounded-md flex flex-col gap-1.5 cursor-grab select-none transition-all">
                                                        
                                                        <!-- Top Row: Grip, Label, API Name, Move/Remove -->
                                                        <div class="flex items-center justify-between">
                                                            <div class="flex items-center gap-1.5 overflow-hidden">
                                                                <svg class="w-3 h-3 text-zinc-400 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="12" r="1"/><circle cx="9" cy="5" r="1"/><circle cx="9" cy="19" r="1"/><circle cx="15" cy="12" r="1"/><circle cx="15" cy="5" r="1"/><circle cx="15" cy="19" r="1"/></svg>
                                                                <div class="overflow-hidden">
                                                                    <span class="font-semibold text-xs text-zinc-900 dark:text-zinc-100 truncate">${escapeHtml(f.label)}</span>
                                                                    <span class="text-[10px] text-zinc-400 font-mono ml-1">(${escapeHtml(f.name)})</span>
                                                                </div>
                                                            </div>

                                                            <div class="flex items-center gap-0.5 flex-shrink-0">
                                                                <button type="button" data-move-field="${sec.id}" data-idx="${fIdx}" data-dir="up" ${fIdx === 0 ? 'disabled' : ''} class="p-0.5 text-zinc-400 hover:text-zinc-700 disabled:opacity-30" title="Move Field Up">
                                                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m18 15-6-6-6 6"/></svg>
                                                                </button>
                                                                <button type="button" data-move-field="${sec.id}" data-idx="${fIdx}" data-dir="down" ${fIdx === sec.fields.length - 1 ? 'disabled' : ''} class="p-0.5 text-zinc-400 hover:text-zinc-700 disabled:opacity-30" title="Move Field Down">
                                                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
                                                                </button>
                                                                <button type="button" data-remove-field="${sec.id}" data-field-name="${f.name}" class="p-0.5 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/40 rounded" title="Remove Field">
                                                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                                                </button>
                                                            </div>
                                                        </div>

                                                        <!-- Bottom Micro-Row: Required, Read-Only, Visibility -->
                                                        <div class="flex items-center justify-between text-[11px] border-t border-zinc-200 dark:border-zinc-750 pt-1 flex-wrap gap-2">
                                                            <div class="flex items-center gap-2.5">
                                                                <label class="inline-flex items-center gap-1 cursor-pointer">
                                                                    <input type="checkbox" data-field-prop="required" data-sec-id="${sec.id}" data-field-name="${f.name}" ${f.required ? 'checked' : ''} class="w-3 h-3 accent-red-600">
                                                                    <span class="${f.required ? 'text-red-600 font-bold' : 'text-zinc-500'}">Required</span>
                                                                </label>

                                                                <label class="inline-flex items-center gap-1 cursor-pointer">
                                                                    <input type="checkbox" data-field-prop="readOnly" data-sec-id="${sec.id}" data-field-name="${f.name}" ${f.readOnly ? 'checked' : ''} class="w-3 h-3 accent-blue-600">
                                                                    <span class="${f.readOnly ? 'text-blue-600 font-bold' : 'text-zinc-500'}">Read-Only</span>
                                                                </label>
                                                            </div>

                                                            <button type="button" data-field-visibility="${sec.id}" data-field-name="${f.name}" data-field-label="${escapeHtml(f.label)}" class="inline-flex items-center gap-1 text-[10.5px] px-1 py-0.2 rounded border transition-colors ${f.visibilityRule?.enabled ? 'bg-blue-50 dark:bg-blue-950/40 border-blue-200 text-blue-700 dark:text-blue-300' : 'border-transparent text-zinc-400 hover:text-zinc-600'}" title="Configure field-level visibility rule">
                                                                <svg class="w-2.5 h-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                                                <span>${f.visibilityRule?.enabled ? 'Rule Active' : 'Visibility'}</span>
                                                            </button>
                                                        </div>
                                                    </div>
                                                `).join('')}
                                            </div>
                                        `}
                                    </div>
                                </div>
                            `;
                        }).join('')}
                    </div>
                </div>
            </div>
        `;

        // Wire Event Listeners in Fields Tab
        root.querySelectorAll('.pl-cat-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                window.plState.fieldCategoryFilter = btn.getAttribute('data-cat');
                renderFieldsTab();
            });
        });

        const searchInp = document.getElementById('plFieldSearchInput');
        searchInp?.addEventListener('input', (e) => {
            window.plState.fieldSearch = e.target.value;
            renderFieldsTab();
        });

        document.getElementById('plClearSearchBtn')?.addEventListener('click', () => {
            window.plState.fieldSearch = '';
            renderFieldsTab();
        });

        root.querySelectorAll('.pl-quick-add-select').forEach(sel => {
            sel.addEventListener('change', (e) => {
                const targetSecId = e.target.value;
                const fieldName = sel.getAttribute('data-field-name');
                const field = window.plState.allAvailableFields.find(f => f.name === fieldName);
                if (field && targetSecId) {
                    handleAddFieldToSection(field, targetSecId);
                }
            });
        });

        document.getElementById('plToggleSysInfoBtn')?.addEventListener('click', handleToggleSystemInfo);
        document.getElementById('plAddSectionBtn')?.addEventListener('click', () => {
            window.plState.newSectionTitle = def.sections.length === 0 ? getObjectDetailTitle(window.plState.selectedModule) + ' Information' : '';
            window.plState.newSectionColumns = 2;
            window.plState.isNewSectionModalOpen = true;
            renderModals();
        });

        // Section header inline edits
        root.querySelectorAll('[data-edit-title]').forEach(btn => {
            btn.addEventListener('click', () => {
                const secId = btn.getAttribute('data-edit-title');
                const sec = def.sections.find(s => s.id === secId);
                window.plState.editingSectionId = secId;
                window.plState.editingSectionTitle = sec?.title || '';
                renderFieldsTab();
                document.getElementById('sec_title_input_' + secId)?.focus();
            });
        });

        root.querySelectorAll('[data-save-title]').forEach(btn => {
            btn.addEventListener('click', () => {
                const secId = btn.getAttribute('data-save-title');
                const inp = document.getElementById('sec_title_input_' + secId);
                if (inp && inp.value.trim()) {
                    const sec = def.sections.find(s => s.id === secId);
                    if (sec) sec.title = inp.value.trim();
                }
                window.plState.editingSectionId = null;
                renderFieldsTab();
            });
        });

        root.querySelectorAll('[data-cancel-title]').forEach(btn => {
            btn.addEventListener('click', () => {
                window.plState.editingSectionId = null;
                renderFieldsTab();
            });
        });

        root.querySelectorAll('[data-set-cols]').forEach(btn => {
            btn.addEventListener('click', () => {
                const secId = btn.getAttribute('data-set-cols');
                const cols = parseInt(btn.getAttribute('data-cols'), 10);
                handleSetSectionColumns(secId, cols);
            });
        });

        root.querySelectorAll('[data-sec-collapsible]').forEach(chk => {
            chk.addEventListener('change', () => {
                handleToggleSectionProp(chk.getAttribute('data-sec-collapsible'), 'collapsible');
            });
        });

        root.querySelectorAll('[data-sec-collapsed]').forEach(chk => {
            chk.addEventListener('change', () => {
                handleToggleSectionProp(chk.getAttribute('data-sec-collapsed'), 'defaultCollapsed');
            });
        });

        root.querySelectorAll('[data-move-sec]').forEach(btn => {
            btn.addEventListener('click', () => {
                const sIdx = parseInt(btn.getAttribute('data-move-sec'), 10);
                const dir = btn.getAttribute('data-dir');
                handleMoveSection(sIdx, dir);
            });
        });

        root.querySelectorAll('[data-remove-sec]').forEach(btn => {
            btn.addEventListener('click', () => {
                handleRemoveSection(btn.getAttribute('data-remove-sec'));
            });
        });

        root.querySelectorAll('[data-move-field]').forEach(btn => {
            btn.addEventListener('click', () => {
                const secId = btn.getAttribute('data-move-field');
                const fIdx = parseInt(btn.getAttribute('data-idx'), 10);
                const dir = btn.getAttribute('data-dir');
                handleMoveField(secId, fIdx, dir);
            });
        });

        root.querySelectorAll('[data-remove-field]').forEach(btn => {
            btn.addEventListener('click', () => {
                const secId = btn.getAttribute('data-remove-field');
                const fieldName = btn.getAttribute('data-field-name');
                handleRemoveField(secId, fieldName);
            });
        });

        root.querySelectorAll('[data-field-prop]').forEach(chk => {
            chk.addEventListener('change', () => {
                const prop = chk.getAttribute('data-field-prop');
                const secId = chk.getAttribute('data-sec-id');
                const fieldName = chk.getAttribute('data-field-name');
                handleToggleFieldProp(secId, fieldName, prop);
            });
        });

        root.querySelectorAll('[data-toggle-sys-field]').forEach(btn => {
            btn.addEventListener('click', () => {
                const key = btn.getAttribute('data-toggle-sys-field');
                const secId = btn.getAttribute('data-sec-id');
                handleToggleSystemInfoField(secId, key);
            });
        });

        // Visibility Rule Modals Trigger
        root.querySelectorAll('[data-sec-visibility]').forEach(btn => {
            btn.addEventListener('click', () => {
                const secId = btn.getAttribute('data-sec-visibility');
                const sec = def.sections.find(s => s.id === secId);
                window.plState.visibilityModalTarget = {
                    type: 'section',
                    sectionId: secId,
                    targetLabel: 'Section: ' + (sec?.title || secId),
                    initialRule: sec?.visibilityRule
                };
                renderModals();
            });
        });

        root.querySelectorAll('[data-field-visibility]').forEach(btn => {
            btn.addEventListener('click', () => {
                const secId = btn.getAttribute('data-field-visibility');
                const fieldName = btn.getAttribute('data-field-name');
                const fieldLabel = btn.getAttribute('data-field-label');
                const sec = def.sections.find(s => s.id === secId);
                const field = sec?.fields?.find(f => f.name === fieldName);
                window.plState.visibilityModalTarget = {
                    type: 'field',
                    sectionId: secId,
                    fieldName: fieldName,
                    targetLabel: fieldLabel + ' (' + fieldName + ')',
                    initialRule: field?.visibilityRule
                };
                renderModals();
            });
        });
    }

    // =========================================================================
    // TAB 2: DEPENDENT FIELDS STUDIO (EXACT REACT REPLICATION)
    // =========================================================================

    function renderDependentFieldsTab() {
        const root = document.getElementById('plTabContent');
        if (!root) return;
        const def = window.plState.currentLayoutDefinition;
        const rules = def.dependentFields || [];

        root.innerHTML = `
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-4 shadow-sm flex flex-col gap-3">
                <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-3 flex-wrap gap-2">
                    <div>
                        <h3 class="text-sm font-bold text-zinc-950 dark:text-zinc-50">Dependent Fields Studio</h3>
                        <p class="text-xs text-zinc-500">Configure picklist dependencies where values in a controlling field filter selectable choices in a dependent field.</p>
                    </div>
                    <button type="button" id="plNewDepRuleBtn" class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        <span>New Dependent Rule</span>
                    </button>
                </div>

                ${rules.length === 0 ? `
                    <div class="py-12 text-center text-zinc-400 text-xs">
                        No dependent picklist rules configured yet. Click "New Dependent Rule" to create one.
                    </div>
                ` : `
                    <div class="flex flex-col gap-2.5">
                        ${rules.map(r => `
                            <div class="p-3 bg-zinc-50 dark:bg-zinc-850 border border-zinc-200 dark:border-zinc-750 rounded-lg flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="18" r="3"/><circle cx="6" cy="6" r="3"/><circle cx="18" cy="6" r="3"/><path d="M18 9v1a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V9"/><path d="M12 12v3"/></svg>
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                                            <span>Controlling: <strong class="text-indigo-600">${escapeHtml(r.controllingField)}</strong></span>
                                            <span>➔</span>
                                            <span>Dependent: <strong class="text-purple-600">${escapeHtml(r.dependentField)}</strong></span>
                                        </div>
                                        <div class="text-[11px] text-zinc-500 mt-0.5">
                                            ${Object.keys(r.mapping || {}).length} parent options mapped
                                        </div>
                                    </div>
                                </div>
                                <button type="button" data-del-dep="${r.id}" class="text-red-600 hover:bg-red-50 dark:hover:bg-red-950/40 p-1.5 rounded-lg" title="Delete rule">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/></svg>
                                </button>
                            </div>
                        `).join('')}
                    </div>
                `}
            </div>
        `;

        document.getElementById('plNewDepRuleBtn')?.addEventListener('click', () => {
            const picklistFields = window.plState.allAvailableFields.filter(f => f.type === 'Dropdown' || f.type === 'Select');
            const ctrl = picklistFields[0]?.name || 'status';
            const dep = picklistFields[1]?.name || 'source';
            const newRule = {
                id: 'dep_' + Date.now(),
                controllingField: ctrl,
                dependentField: dep,
                mapping: {
                    'New': ['Inbound Website', 'Referral'],
                    'Qualified': ['Direct Call', 'Partner']
                }
            };
            def.dependentFields = def.dependentFields || [];
            def.dependentFields.push(newRule);
            renderDependentFieldsTab();
            renderSubTabsStrip();
        });

        root.querySelectorAll('[data-del-dep]').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.getAttribute('data-del-dep');
                def.dependentFields = def.dependentFields.filter(r => r.id !== id);
                renderDependentFieldsTab();
                renderSubTabsStrip();
            });
        });
    }

    // =========================================================================
    // TAB 3: VALIDATION RULES STUDIO (EXACT REACT REPLICATION)
    // =========================================================================

    function renderValidationRulesTab() {
        const root = document.getElementById('plTabContent');
        if (!root) return;
        const def = window.plState.currentLayoutDefinition;
        const rules = def.validationRules || [];

        root.innerHTML = `
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-4 shadow-sm flex flex-col gap-3">
                <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-3 flex-wrap gap-2">
                    <div>
                        <h3 class="text-sm font-bold text-zinc-950 dark:text-zinc-50">Validation Rules Studio</h3>
                        <p class="text-xs text-zinc-500">Prevent saving records when custom criteria fail. Define triggers, target field conditions, and user-facing error messages.</p>
                    </div>
                    <button type="button" id="plNewValRuleBtn" class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        <span>New Validation Rule</span>
                    </button>
                </div>

                ${rules.length === 0 ? `
                    <div class="py-12 text-center text-zinc-400 text-xs">
                        No validation rules defined. Click "New Validation Rule" to create custom constraints.
                    </div>
                ` : `
                    <div class="flex flex-col gap-2.5">
                        ${rules.map(r => `
                            <div class="p-3 bg-zinc-50 dark:bg-zinc-850 border border-zinc-200 dark:border-zinc-750 rounded-lg flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-950 text-amber-700 dark:text-amber-300 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                                            <span>${escapeHtml(r.name)}</span>
                                            <span class="text-[10px] px-1.5 py-0.2 rounded ${r.enabled ? 'bg-emerald-100 text-emerald-800' : 'bg-zinc-200 text-zinc-600'}">
                                                ${r.enabled ? 'Active' : 'Disabled'}
                                            </span>
                                        </div>
                                        <div class="text-[11px] text-zinc-500 mt-0.5">
                                            Error message: "${escapeHtml(r.errorMessage)}"
                                        </div>
                                    </div>
                                </div>
                                <button type="button" data-del-val="${r.id}" class="text-red-600 hover:bg-red-50 dark:hover:bg-red-950/40 p-1.5 rounded-lg" title="Delete rule">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/></svg>
                                </button>
                            </div>
                        `).join('')}
                    </div>
                `}
            </div>
        `;

        document.getElementById('plNewValRuleBtn')?.addEventListener('click', () => {
            const newRule = {
                id: 'val_' + Date.now(),
                name: 'Required Field on Closing',
                description: 'Mandatory field validation',
                enabled: true,
                errorMessage: 'This field cannot be empty before advancing to this stage.',
                targetField: window.plState.allAvailableFields[0]?.name || 'status',
                targetOperator: 'is_empty'
            };
            def.validationRules = def.validationRules || [];
            def.validationRules.push(newRule);
            renderValidationRulesTab();
            renderSubTabsStrip();
        });

        root.querySelectorAll('[data-del-val]').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.getAttribute('data-del-val');
                def.validationRules = def.validationRules.filter(r => r.id !== id);
                renderValidationRulesTab();
                renderSubTabsStrip();
            });
        });
    }

    // =========================================================================
    // TAB 4: RELATED LISTS CONFIGURATION (EXACT REACT REPLICATION)
    // =========================================================================

    function renderRelatedListsTab() {
        const root = document.getElementById('plTabContent');
        if (!root) return;
        const def = window.plState.currentLayoutDefinition;
        const lists = def.relatedLists || [];

        root.innerHTML = `
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-4 shadow-sm flex flex-col gap-3.5">
                <div>
                    <h3 class="text-sm font-bold text-zinc-950 dark:text-zinc-50">Related Lists Configuration</h3>
                    <p class="text-xs text-zinc-500">Enable, disable, relocate, and reorder child relationship modules for this layout.</p>
                </div>

                ${lists.length === 0 ? `
                    <div class="py-12 text-center text-zinc-400 text-xs">
                        No child relationships defined for this object.
                    </div>
                ` : `
                    <div class="flex flex-col gap-2">
                        ${lists.map((rl, idx) => `
                            <div class="p-2.5 ${rl.enabled ? 'bg-zinc-50 dark:bg-zinc-850 border-zinc-200 dark:border-zinc-750' : 'bg-zinc-50/50 dark:bg-zinc-900/40 border-zinc-100 dark:border-zinc-850 opacity-60'} border rounded-lg flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2.5">
                                    <input type="checkbox" data-toggle-rl="${rl.relation}" ${rl.enabled ? 'checked' : ''} class="w-3.5 h-3.5 accent-indigo-600">
                                    <div>
                                        <div class="text-xs font-bold text-zinc-900 dark:text-zinc-100">${escapeHtml(rl.label)}</div>
                                        <div class="text-[10px] text-zinc-400 font-mono">Key: ${escapeHtml(rl.relation)}</div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2.5">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-[11px] text-zinc-500">Location:</span>
                                        <select data-rl-location="${rl.relation}" ${!rl.enabled ? 'disabled' : ''} class="text-xs py-0.5 px-2 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 rounded text-zinc-800 dark:text-zinc-200">
                                            <option value="right_sidebar" ${rl.location === 'right_sidebar' ? 'selected' : ''}>Right Sidebar Card</option>
                                            <option value="main_bottom" ${rl.location === 'main_bottom' ? 'selected' : ''}>Main Bottom Section</option>
                                        </select>
                                    </div>

                                    <div class="flex items-center gap-0.5">
                                        <button type="button" data-move-rl="${idx}" data-dir="up" ${idx === 0 ? 'disabled' : ''} class="p-1 text-zinc-400 hover:text-zinc-700 disabled:opacity-30">
                                            <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m18 15-6-6-6 6"/></svg>
                                        </button>
                                        <button type="button" data-move-rl="${idx}" data-dir="down" ${idx === lists.length - 1 ? 'disabled' : ''} class="p-1 text-zinc-400 hover:text-zinc-700 disabled:opacity-30">
                                            <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                `}
            </div>
        `;

        root.querySelectorAll('[data-toggle-rl]').forEach(chk => {
            chk.addEventListener('change', () => {
                const rel = chk.getAttribute('data-toggle-rl');
                const target = lists.find(r => r.relation === rel);
                if (target) target.enabled = chk.checked;
                renderRelatedListsTab();
                renderSubTabsStrip();
            });
        });

        root.querySelectorAll('[data-rl-location]').forEach(sel => {
            sel.addEventListener('change', () => {
                const rel = sel.getAttribute('data-rl-location');
                const target = lists.find(r => r.relation === rel);
                if (target) target.location = sel.value;
            });
        });

        root.querySelectorAll('[data-move-rl]').forEach(btn => {
            btn.addEventListener('click', () => {
                const idx = parseInt(btn.getAttribute('data-move-rl'), 10);
                const dir = btn.getAttribute('data-dir');
                const targetIdx = dir === 'up' ? idx - 1 : idx + 1;
                if (targetIdx < 0 || targetIdx >= lists.length) return;
                const tmp = lists[idx];
                lists[idx] = lists[targetIdx];
                lists[targetIdx] = tmp;
                renderRelatedListsTab();
            });
        });
    }

    // =========================================================================
    // TAB 5: WORKSPACE ARRANGEMENT (EXACT REACT REPLICATION)
    // =========================================================================

    function renderComponentsTab() {
        const root = document.getElementById('plTabContent');
        if (!root) return;
        const def = window.plState.currentLayoutDefinition;
        const comp = def.components || {};
        const isFilesPrivateAssigned = comp.filesPrivateAssigned === true;
        const isApprovalHistoryAssigned = comp.approvalHistoryAssigned === true;

        root.innerHTML = `
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-4 shadow-sm flex flex-col gap-4">
                <div>
                    <h3 class="text-sm font-bold text-zinc-950 dark:text-zinc-50">Component &amp; Workspace Arrangement</h3>
                    <p class="text-xs text-zinc-500">Configure Modal Header title fields, Files Private vault assignment, profile visibility rules, and right sidebar stacking order.</p>
                </div>

                <!-- Record Detail Header & Title Configuration -->
                <div class="p-3.5 bg-zinc-50 dark:bg-zinc-850 border border-zinc-200 dark:border-zinc-750 rounded-xl flex flex-col gap-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-blue-600 text-white flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="4" y1="21" x2="4" y2="14"/><line x1="4" y1="10" x2="4" y2="3"/><line x1="12" y1="21" x2="12" y2="12"/><line x1="12" y1="8" x2="12" y2="3"/><line x1="20" y1="21" x2="20" y2="16"/><line x1="20" y1="12" x2="20" y2="3"/></svg>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-zinc-900 dark:text-zinc-100">Record Detail Header &amp; Title Configuration</div>
                            <div class="text-[11px] text-zinc-500">Specify which fields compose the top header title and breadcrumbs for this object.</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 bg-white dark:bg-zinc-900 p-3 rounded-lg border border-zinc-200 dark:border-zinc-750">
                        <div>
                            <label class="block text-[11px] font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Primary Title Field *</label>
                            <select id="plHeaderPrimaryField" class="w-full text-xs p-1.5 bg-zinc-50 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 rounded text-zinc-800 dark:text-zinc-200">
                                <option value="">Default (Auto Record Name)</option>
                                ${window.plState.allAvailableFields.map(f => `
                                    <option value="${f.name}" ${def.headerConfig?.primary_field === f.name ? 'selected' : ''}>${escapeHtml(f.label)} (${f.name})</option>
                                `).join('')}
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Secondary Field (Optional)</label>
                            <select id="plHeaderSecondaryField" class="w-full text-xs p-1.5 bg-zinc-50 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 rounded text-zinc-800 dark:text-zinc-200">
                                <option value="">(None)</option>
                                ${window.plState.allAvailableFields.map(f => `
                                    <option value="${f.name}" ${def.headerConfig?.secondary_field === f.name ? 'selected' : ''}>${escapeHtml(f.label)} (${f.name})</option>
                                `).join('')}
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Field Separator</label>
                            <select id="plHeaderSeparator" class="w-full text-xs p-1.5 bg-zinc-50 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 rounded text-zinc-800 dark:text-zinc-200">
                                <option value=" + " ${def.headerConfig?.separator === ' + ' ? 'selected' : ''}>Plus sign (' + ')</option>
                                <option value=" • " ${def.headerConfig?.separator === ' • ' ? 'selected' : ''}>Bullet dot (' • ')</option>
                                <option value=" - " ${def.headerConfig?.separator === ' - ' ? 'selected' : ''}>Hyphen (' - ')</option>
                                <option value=" | " ${def.headerConfig?.separator === ' | ' ? 'selected' : ''}>Pipe (' | ')</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Files Private Vault Studio -->
                <div class="p-3.5 ${isFilesPrivateAssigned ? 'bg-purple-50/50 dark:bg-purple-950/20 border-purple-200 dark:border-purple-900/50' : 'bg-zinc-50 dark:bg-zinc-850 border-zinc-200 dark:border-zinc-750'} border rounded-xl flex flex-col gap-2.5">
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg ${isFilesPrivateAssigned ? 'bg-purple-600' : 'bg-zinc-500'} text-white flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            </div>
                            <div>
                                <div class="text-xs font-bold ${isFilesPrivateAssigned ? 'text-purple-900 dark:text-purple-100' : 'text-zinc-900 dark:text-zinc-100'}">Files Private (Confidential Attachment Vault)</div>
                                <div class="text-[11px] ${isFilesPrivateAssigned ? 'text-purple-700 dark:text-purple-300' : 'text-zinc-500'}">Default is unassigned and hidden from all users. When active, only permitted profiles or owner have visibility.</div>
                            </div>
                        </div>

                        <button type="button" id="plCompToggleFilesPrivate" class="text-xs font-semibold px-3 py-1.5 rounded-lg border ${isFilesPrivateAssigned ? 'bg-purple-600 text-white border-purple-700' : 'bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 border-zinc-300'}">
                            ${isFilesPrivateAssigned ? 'Assigned (Active)' : 'Unassigned (Hidden)'}
                        </button>
                    </div>
                </div>

                <!-- Approval History Component -->
                <div class="p-3.5 ${isApprovalHistoryAssigned ? 'bg-orange-50/50 dark:bg-orange-950/20 border-orange-200 dark:border-orange-900/50' : 'bg-zinc-50 dark:bg-zinc-850 border-zinc-200 dark:border-zinc-750'} border rounded-xl flex flex-col gap-2.5">
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg ${isApprovalHistoryAssigned ? 'bg-orange-600' : 'bg-zinc-500'} text-white flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
                            </div>
                            <div>
                                <div class="text-xs font-bold ${isApprovalHistoryAssigned ? 'text-orange-900 dark:text-orange-100' : 'text-zinc-900 dark:text-zinc-100'}">Approval History Component</div>
                                <div class="text-[11px] ${isApprovalHistoryAssigned ? 'text-orange-700 dark:text-orange-300' : 'text-zinc-500'}">Displays multi-level approval history audit trail, step remarks, and inline approval/rejection action buttons.</div>
                            </div>
                        </div>

                        <button type="button" id="plCompToggleApprovalHistory" class="text-xs font-semibold px-3 py-1.5 rounded-lg border ${isApprovalHistoryAssigned ? 'bg-orange-600 text-white border-orange-700' : 'bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 border-zinc-300'}">
                            ${isApprovalHistoryAssigned ? 'Assigned (Active)' : 'Unassigned (Hidden)'}
                        </button>
                    </div>
                </div>

                <!-- Right Sidebar Stacking Order -->
                <div class="flex flex-col gap-2 max-w-xl">
                    <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200">Right Sidebar Component Stacking Order:</span>
                    ${(comp.sidebarOrder || ['files', 'emails', 'activity', 'related', 'approvals']).map((itemKey, idx) => {
                        const labels = {
                            files: 'Files & Attachments',
                            emails: 'Emails & Correspondence',
                            activity: 'Notes & Activity',
                            related: 'Related Lists',
                            approvals: 'Approval History',
                            files_private: 'Files Private (Vault)',
                            field_history: 'Field History Audit'
                        };
                        return `
                            <div class="p-2.5 bg-zinc-50 dark:bg-zinc-850 border border-zinc-200 dark:border-zinc-750 rounded-lg flex items-center justify-between">
                                <span class="text-xs font-semibold text-zinc-800 dark:text-zinc-200">Position #${idx + 1}: ${labels[itemKey] || itemKey}</span>
                                <div class="flex items-center gap-0.5">
                                    <button type="button" data-move-comp="${idx}" data-dir="up" ${idx === 0 ? 'disabled' : ''} class="p-1 text-zinc-400 hover:text-zinc-700 disabled:opacity-30">
                                        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m18 15-6-6-6 6"/></svg>
                                    </button>
                                    <button type="button" data-move-comp="${idx}" data-dir="down" ${idx === (comp.sidebarOrder?.length || 5) - 1 ? 'disabled' : ''} class="p-1 text-zinc-400 hover:text-zinc-700 disabled:opacity-30">
                                        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
                                    </button>
                                </div>
                            </div>
                        `;
                    }).join('')}
                </div>
            </div>
        `;

        document.getElementById('plHeaderPrimaryField')?.addEventListener('change', (e) => {
            def.headerConfig = def.headerConfig || {};
            def.headerConfig.primary_field = e.target.value;
        });

        document.getElementById('plHeaderSecondaryField')?.addEventListener('change', (e) => {
            def.headerConfig = def.headerConfig || {};
            def.headerConfig.secondary_field = e.target.value;
        });

        document.getElementById('plHeaderSeparator')?.addEventListener('change', (e) => {
            def.headerConfig = def.headerConfig || {};
            def.headerConfig.separator = e.target.value;
        });

        document.getElementById('plCompToggleFilesPrivate')?.addEventListener('click', () => {
            def.components = def.components || {};
            def.components.filesPrivateAssigned = !isFilesPrivateAssigned;
            renderComponentsTab();
            renderHeader();
        });

        document.getElementById('plCompToggleApprovalHistory')?.addEventListener('click', () => {
            def.components = def.components || {};
            def.components.approvalHistoryAssigned = !isApprovalHistoryAssigned;
            renderComponentsTab();
            renderHeader();
        });

        root.querySelectorAll('[data-move-comp]').forEach(btn => {
            btn.addEventListener('click', () => {
                const idx = parseInt(btn.getAttribute('data-move-comp'), 10);
                const dir = btn.getAttribute('data-dir');
                const targetIdx = dir === 'up' ? idx - 1 : idx + 1;
                def.components = def.components || {};
                def.components.sidebarOrder = def.components.sidebarOrder || ['files', 'emails', 'activity', 'related', 'approvals'];
                if (targetIdx < 0 || targetIdx >= def.components.sidebarOrder.length) return;
                const tmp = def.components.sidebarOrder[idx];
                def.components.sidebarOrder[idx] = def.components.sidebarOrder[targetIdx];
                def.components.sidebarOrder[targetIdx] = tmp;
                renderComponentsTab();
            });
        });
    }

    // =========================================================================
    // TAB 6: ACTIVITY COMPOSER SETTINGS (EXACT REACT REPLICATION)
    // =========================================================================

    function renderActivitiesTab() {
        const root = document.getElementById('plTabContent');
        if (!root) return;
        const def = window.plState.currentLayoutDefinition;
        const act = def.activitySettings || { allowedTypes: ['Call', 'Task', 'Meeting', 'Note'], defaultType: 'Call' };

        root.innerHTML = `
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-4 shadow-sm flex flex-col gap-3.5">
                <div>
                    <h3 class="text-sm font-bold text-zinc-950 dark:text-zinc-50">Activity Composer Configuration</h3>
                    <p class="text-xs text-zinc-500">Configure which activity tabs (Calls, Tasks, Events, Notes) are enabled and their sequence for this layout.</p>
                </div>

                <div class="flex flex-col gap-2 max-w-xl">
                    <span class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Enabled Activity Types &amp; Sequence:</span>
                    ${(act.allowedTypes || ['Call', 'Task', 'Meeting', 'Note']).map((type, idx) => {
                        const isDefault = act.defaultType === type;
                        return `
                            <div class="p-2.5 bg-zinc-50 dark:bg-zinc-850 border border-zinc-200 dark:border-zinc-750 rounded-lg flex items-center justify-between">
                                <div class="flex items-center gap-2.5">
                                    <span class="text-xs font-bold text-zinc-900 dark:text-zinc-100">
                                        ${type === 'Call' ? '📞 Log a Call' : type === 'Task' ? '☑️ New Task' : type === 'Meeting' ? '📅 New Event / Meeting' : '📝 Add Note'}
                                    </span>
                                    ${isDefault ? `
                                        <span class="text-[10px] font-semibold text-emerald-800 bg-emerald-100 px-1.5 py-0.2 rounded">Default Active Tab</span>
                                    ` : ''}
                                </div>

                                <div class="flex items-center gap-2">
                                    ${!isDefault ? `
                                        <button type="button" data-set-default-act="${type}" class="text-xs text-zinc-600 hover:text-indigo-600">
                                            Set Default
                                        </button>
                                    ` : ''}

                                    <div class="flex items-center gap-0.5">
                                        <button type="button" data-move-act="${idx}" data-dir="up" ${idx === 0 ? 'disabled' : ''} class="p-1 text-zinc-400 hover:text-zinc-700 disabled:opacity-30">
                                            <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m18 15-6-6-6 6"/></svg>
                                        </button>
                                        <button type="button" data-move-act="${idx}" data-dir="down" ${idx === (act.allowedTypes?.length || 4) - 1 ? 'disabled' : ''} class="p-1 text-zinc-400 hover:text-zinc-700 disabled:opacity-30">
                                            <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        `;
                    }).join('')}
                </div>
            </div>
        `;

        root.querySelectorAll('[data-set-default-act]').forEach(btn => {
            btn.addEventListener('click', () => {
                def.activitySettings = def.activitySettings || { allowedTypes: ['Call', 'Task', 'Meeting', 'Note'], defaultType: 'Call' };
                def.activitySettings.defaultType = btn.getAttribute('data-set-default-act');
                renderActivitiesTab();
            });
        });

        root.querySelectorAll('[data-move-act]').forEach(btn => {
            btn.addEventListener('click', () => {
                const idx = parseInt(btn.getAttribute('data-move-act'), 10);
                const dir = btn.getAttribute('data-dir');
                def.activitySettings = def.activitySettings || { allowedTypes: ['Call', 'Task', 'Meeting', 'Note'], defaultType: 'Call' };
                const types = [...def.activitySettings.allowedTypes];
                const targetIdx = dir === 'up' ? idx - 1 : idx + 1;
                if (targetIdx < 0 || targetIdx >= types.length) return;
                const tmp = types[idx];
                types[idx] = types[targetIdx];
                types[targetIdx] = tmp;
                def.activitySettings.allowedTypes = types;
                renderActivitiesTab();
            });
        });
    }

    // =========================================================================
    // MODALS RENDERING (NEW LAYOUT, ASSIGNMENTS, ADD SECTION, FIELD HISTORY, VISIBILITY)
    // =========================================================================

    function renderModals() {
        let container = document.getElementById('plModalsRoot');
        if (!container) {
            container = document.createElement('div');
            container.id = 'plModalsRoot';
            document.body.appendChild(container);
        }

        let html = '';

        // 1. New / Clone Layout Modal
        if (window.plState.isNewLayoutModalOpen) {
            html += `
                <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
                    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl w-full max-w-md shadow-2xl p-5 flex flex-col gap-4 animate-scale-in">
                        <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-3">
                            <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">
                                ${window.plState.newLayoutCloneFrom ? 'Clone Page Layout' : 'Create New Page Layout'}
                            </h3>
                            <button type="button" id="plCloseNewLayoutModal" class="text-zinc-400 hover:text-zinc-600">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            </button>
                        </div>

                        <form id="plNewLayoutForm" class="flex flex-col gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Layout Name *</label>
                                <input type="text" id="plNewLayoutNameInp" required value="${escapeHtml(window.plState.newLayoutName)}" placeholder="e.g. Sales Executive Layout" class="w-full text-xs px-3 py-2 bg-zinc-50 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 rounded-lg text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Description</label>
                                <textarea id="plNewLayoutDescInp" rows="2" placeholder="Optional layout description" class="w-full text-xs px-3 py-2 bg-zinc-50 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 rounded-lg text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-indigo-500">${escapeHtml(window.plState.newLayoutDesc)}</textarea>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Clone Sections &amp; Fields From</label>
                                <select id="plNewLayoutCloneSelect" class="w-full text-xs px-3 py-2 bg-zinc-50 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 rounded-lg text-zinc-900 dark:text-zinc-100 focus:outline-none">
                                    <option value="">Start Empty / Default</option>
                                    ${window.plState.layouts.map(l => `
                                        <option value="${l.id}" ${window.plState.newLayoutCloneFrom === l.id ? 'selected' : ''}>${escapeHtml(l.name)}</option>
                                    `).join('')}
                                </select>
                            </div>

                            <div class="flex items-center justify-end gap-2 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                                <button type="button" id="plCancelNewLayoutBtn" class="text-xs px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50">Cancel</button>
                                <button type="submit" class="text-xs font-semibold px-4 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm">Create Layout</button>
                            </div>
                        </form>
                    </div>
                </div>
            `;
        }

        // 2. Profile Layout Assignments Matrix Modal
        if (window.plState.isAssignmentModalOpen) {
            html += `
                <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
                    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl w-full max-w-2xl shadow-2xl p-5 flex flex-col gap-4 animate-scale-in max-h-[85vh]">
                        <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-3">
                            <div>
                                <h3 class="text-sm font-bold text-zinc-950 dark:text-zinc-50">Profile Page Layout Assignments</h3>
                                <p class="text-xs text-zinc-500">Map security profiles to specific layouts for ${getObjectDetailTitle(window.plState.selectedModule)}.</p>
                            </div>
                            <button type="button" id="plCloseAssignModal" class="text-zinc-400 hover:text-zinc-600">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            </button>
                        </div>

                        <div class="overflow-y-auto max-h-[55vh] flex flex-col gap-2">
                            ${window.plState.profiles.map(p => {
                                const curVal = window.plState.workingAssignments[p.id] || window.plState.selectedLayoutId;
                                return `
                                    <div class="p-2.5 bg-zinc-50 dark:bg-zinc-850 border border-zinc-200 dark:border-zinc-750 rounded-lg flex items-center justify-between gap-3">
                                        <div>
                                            <div class="text-xs font-bold text-zinc-900 dark:text-zinc-100">${escapeHtml(p.name)}</div>
                                            <div class="text-[11px] text-zinc-500">${escapeHtml(p.description || 'Standard profile')}</div>
                                        </div>
                                        <select data-profile-assign="${p.id}" class="text-xs px-2.5 py-1.5 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 rounded-lg text-zinc-800 dark:text-zinc-200 min-w-[200px] focus:outline-none">
                                            ${window.plState.layouts.map(l => `
                                                <option value="${l.id}" ${curVal == l.id ? 'selected' : ''}>
                                                    ${escapeHtml(l.name)} ${l.is_default ? '★ (Default)' : ''}
                                                </option>
                                            `).join('')}
                                        </select>
                                    </div>
                                `;
                            }).join('')}
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                            <button type="button" id="plCancelAssignBtn" class="text-xs px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50">Cancel</button>
                            <button type="button" id="plSaveAssignBtn" class="text-xs font-semibold px-4 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm">Save Assignments</button>
                        </div>
                    </div>
                </div>
            `;
        }

        // 3. Add Section Modal
        if (window.plState.isNewSectionModalOpen) {
            html += `
                <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
                    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl w-full max-w-md shadow-2xl p-5 flex flex-col gap-4 animate-scale-in">
                        <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-3">
                            <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Add Layout Section</h3>
                            <button type="button" id="plCloseAddSecModal" class="text-zinc-400 hover:text-zinc-600">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            </button>
                        </div>

                        <form id="plAddSectionForm" class="flex flex-col gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Section Title *</label>
                                <input type="text" id="plSecTitleInp" required value="${escapeHtml(window.plState.newSectionTitle)}" placeholder="e.g. Additional Information" class="w-full text-xs px-3 py-2 bg-zinc-50 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 rounded-lg text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Column Layout</label>
                                <div class="grid grid-cols-3 gap-2">
                                    ${[1, 2, 3].map(c => `
                                        <button type="button" data-select-cols="${c}" class="py-2 text-xs font-semibold rounded-lg border text-center transition-all ${window.plState.newSectionColumns === c ? 'bg-indigo-50 dark:bg-indigo-950/60 border-indigo-500 text-indigo-700 dark:text-indigo-300' : 'bg-zinc-50 dark:bg-zinc-800 border-zinc-200 dark:border-zinc-700 text-zinc-600'}">
                                            ${c} Column${c > 1 ? 's' : ''}
                                        </button>
                                    `).join('')}
                                </div>
                            </div>

                            <div class="flex items-center justify-end gap-2 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                                <button type="button" id="plCancelAddSecBtn" class="text-xs px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50">Cancel</button>
                                <button type="submit" class="text-xs font-semibold px-4 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm">Add Section</button>
                            </div>
                        </form>
                    </div>
                </div>
            `;
        }

        // 4. Field History Tracking Modal
        if (window.plState.isFieldHistoryModalOpen) {
            const avail = window.plState.allAvailableFields || [];
            const filtered = avail.filter(f => {
                if (!window.plState.fieldHistorySearch) return true;
                const q = window.plState.fieldHistorySearch.toLowerCase();
                return f.label.toLowerCase().includes(q) || f.name.toLowerCase().includes(q);
            });

            html += `
                <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
                    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl w-full max-w-xl shadow-2xl p-5 flex flex-col gap-4 animate-scale-in max-h-[85vh]">
                        <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-3">
                            <div>
                                <h3 class="text-sm font-bold text-zinc-950 dark:text-zinc-50">Field History Tracking</h3>
                                <p class="text-xs text-zinc-500">Track changes to up to 20 fields for ${getObjectDetailTitle(window.plState.selectedModule)}.</p>
                            </div>
                            <button type="button" id="plCloseFhModal" class="text-zinc-400 hover:text-zinc-600">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            </button>
                        </div>

                        <div class="flex items-center justify-between text-xs">
                            <span class="text-zinc-500">Select fields to track (<strong>${window.plState.workingTrackedFields.length}/20</strong> selected):</span>
                            <input type="text" id="plFhSearchInput" placeholder="Filter attributes..." value="${escapeHtml(window.plState.fieldHistorySearch)}" class="px-2.5 py-1 text-xs bg-zinc-50 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 rounded-lg text-zinc-800 dark:text-zinc-200 focus:outline-none">
                        </div>

                        <div class="overflow-y-auto max-h-[50vh] grid grid-cols-1 sm:grid-cols-2 gap-2 p-1">
                            ${filtered.map(f => {
                                const isChecked = window.plState.workingTrackedFields.includes(f.name);
                                return `
                                    <label class="p-2 ${isChecked ? 'bg-indigo-50 dark:bg-indigo-950/40 border-indigo-200 dark:border-indigo-800 font-semibold text-indigo-900 dark:text-indigo-200' : 'bg-zinc-50 dark:bg-zinc-850 border-zinc-200 dark:border-zinc-750 text-zinc-700 dark:text-zinc-300'} border rounded-md flex items-center gap-2 text-xs cursor-pointer">
                                        <input type="checkbox" data-fh-field="${f.name}" ${isChecked ? 'checked' : ''} class="w-3.5 h-3.5 accent-indigo-600">
                                        <span class="truncate">${escapeHtml(f.label)} (${f.name})</span>
                                    </label>
                                `;
                            }).join('')}
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                            <button type="button" id="plCancelFhBtn" class="text-xs px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50">Cancel</button>
                            <button type="button" id="plSaveFhBtn" class="text-xs font-semibold px-4 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm">Save Tracking Config</button>
                        </div>
                    </div>
                </div>
            `;
        }

        // 5. Visibility Rule Modal
        if (window.plState.visibilityModalTarget) {
            const tgt = window.plState.visibilityModalTarget;
            const initialRule = tgt.initialRule || { enabled: false, matchType: 'ALL', conditions: [] };
            const conditions = initialRule.conditions?.length ? initialRule.conditions : [
                { field: window.plState.allAvailableFields[0]?.name || 'status', operator: 'equals', value: '' }
            ];

            html += `
                <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
                    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl w-full max-w-lg shadow-2xl p-5 flex flex-col gap-4 animate-scale-in max-h-[85vh]">
                        <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-3">
                            <div>
                                <h3 class="text-sm font-bold text-zinc-950 dark:text-zinc-50">Visibility Rule: ${escapeHtml(tgt.targetLabel)}</h3>
                                <p class="text-xs text-zinc-500">Show or hide conditionally based on record values.</p>
                            </div>
                            <button type="button" id="plCloseVisModal" class="text-zinc-400 hover:text-zinc-600">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            </button>
                        </div>

                        <div class="flex flex-col gap-3">
                            <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-zinc-800 dark:text-zinc-200">
                                <input type="checkbox" id="plVisEnabled" ${initialRule.enabled ? 'checked' : ''} class="w-3.5 h-3.5 accent-indigo-600">
                                <span>Enable Conditional Visibility for this target</span>
                            </label>

                            <div class="flex items-center gap-2 text-xs">
                                <span class="text-zinc-500">Condition matching logic:</span>
                                <select id="plVisMatchType" class="text-xs px-2 py-1 bg-zinc-50 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 rounded text-zinc-800 dark:text-zinc-200">
                                    <option value="ALL" ${initialRule.matchType === 'ALL' ? 'selected' : ''}>Match ALL Conditions (AND)</option>
                                    <option value="ANY" ${initialRule.matchType === 'ANY' ? 'selected' : ''}>Match ANY Condition (OR)</option>
                                </select>
                            </div>

                            <div class="flex flex-col gap-2 max-h-[40vh] overflow-y-auto" id="plVisConditionsList">
                                ${conditions.map((cond, cIdx) => `
                                    <div class="p-2.5 bg-zinc-50 dark:bg-zinc-850 border border-zinc-200 dark:border-zinc-750 rounded-lg flex items-center gap-2">
                                        <select class="pl-vis-field flex-1 text-xs py-1 px-2 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 rounded">
                                            ${window.plState.allAvailableFields.map(f => `
                                                <option value="${f.name}" ${cond.field === f.name ? 'selected' : ''}>${escapeHtml(f.label)}</option>
                                            `).join('')}
                                        </select>
                                        <select class="pl-vis-op w-32 text-xs py-1 px-2 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 rounded">
                                            <option value="equals" ${cond.operator === 'equals' ? 'selected' : ''}>Equals</option>
                                            <option value="not_equals" ${cond.operator === 'not_equals' ? 'selected' : ''}>Not Equals</option>
                                            <option value="contains" ${cond.operator === 'contains' ? 'selected' : ''}>Contains</option>
                                            <option value="is_set" ${cond.operator === 'is_set' ? 'selected' : ''}>Is Set</option>
                                            <option value="is_empty" ${cond.operator === 'is_empty' ? 'selected' : ''}>Is Empty</option>
                                        </select>
                                        <input type="text" value="${escapeHtml(cond.value || '')}" placeholder="Value..." class="pl-vis-val flex-1 text-xs py-1 px-2 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 rounded">
                                    </div>
                                `).join('')}
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                            <button type="button" id="plCancelVisBtn" class="text-xs px-3 py-1.5 rounded-lg border border-zinc-300 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50">Cancel</button>
                            <button type="button" id="plSaveVisBtn" class="text-xs font-semibold px-4 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm">Apply Rule</button>
                        </div>
                    </div>
                </div>
            `;
        }

        container.innerHTML = html;

        // Wire Modals Listeners
        document.getElementById('plCloseNewLayoutModal')?.addEventListener('click', () => {
            window.plState.isNewLayoutModalOpen = false;
            renderModals();
        });
        document.getElementById('plCancelNewLayoutBtn')?.addEventListener('click', () => {
            window.plState.isNewLayoutModalOpen = false;
            renderModals();
        });
        document.getElementById('plNewLayoutForm')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const name = document.getElementById('plNewLayoutNameInp')?.value.trim();
            const desc = document.getElementById('plNewLayoutDescInp')?.value.trim();
            const cloneFrom = document.getElementById('plNewLayoutCloneSelect')?.value;
            if (!name) return;

            window.plState.saving = true;
            try {
                const res = await fetch(`/api/page-layouts/${encodeURIComponent(window.plState.selectedModule)}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken()
                    },
                    body: JSON.stringify({
                        name: name,
                        description: desc,
                        clone_from_id: cloneFrom || undefined
                    })
                });
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'Error creating layout');
                window.plState.isNewLayoutModalOpen = false;
                setFeedback(`New layout "${name}" created successfully!`);
                await loadLayoutsForModule(window.plState.selectedModule);
            } catch (err) {
                console.error('Error creating layout:', err);
                alert('Error: ' + err.message);
            } finally {
                window.plState.saving = false;
                renderModals();
            }
        });

        document.getElementById('plCloseAssignModal')?.addEventListener('click', () => {
            window.plState.isAssignmentModalOpen = false;
            renderModals();
        });
        document.getElementById('plCancelAssignBtn')?.addEventListener('click', () => {
            window.plState.isAssignmentModalOpen = false;
            renderModals();
        });
        container.querySelectorAll('[data-profile-assign]').forEach(sel => {
            sel.addEventListener('change', (e) => {
                const pId = sel.getAttribute('data-profile-assign');
                window.plState.workingAssignments[pId] = e.target.value;
            });
        });
        document.getElementById('plSaveAssignBtn')?.addEventListener('click', handleSaveAssignments);

        document.getElementById('plCloseAddSecModal')?.addEventListener('click', () => {
            window.plState.isNewSectionModalOpen = false;
            renderModals();
        });
        document.getElementById('plCancelAddSecBtn')?.addEventListener('click', () => {
            window.plState.isNewSectionModalOpen = false;
            renderModals();
        });
        container.querySelectorAll('[data-select-cols]').forEach(btn => {
            btn.addEventListener('click', () => {
                window.plState.newSectionColumns = parseInt(btn.getAttribute('data-select-cols'), 10);
                renderModals();
            });
        });
        document.getElementById('plAddSectionForm')?.addEventListener('submit', (e) => {
            e.preventDefault();
            const title = document.getElementById('plSecTitleInp')?.value.trim();
            if (!title || !window.plState.currentLayoutDefinition) return;
            const newSec = {
                id: 'sec_' + Date.now(),
                title: title,
                columns: window.plState.newSectionColumns || 2,
                collapsible: true,
                defaultCollapsed: false,
                fields: []
            };
            window.plState.currentLayoutDefinition.sections.push(newSec);
            window.plState.isNewSectionModalOpen = false;
            renderModals();
            renderFieldsTab();
        });

        document.getElementById('plCloseFhModal')?.addEventListener('click', () => {
            window.plState.isFieldHistoryModalOpen = false;
            renderModals();
        });
        document.getElementById('plCancelFhBtn')?.addEventListener('click', () => {
            window.plState.isFieldHistoryModalOpen = false;
            renderModals();
        });
        document.getElementById('plFhSearchInput')?.addEventListener('input', (e) => {
            window.plState.fieldHistorySearch = e.target.value;
            renderModals();
        });
        container.querySelectorAll('[data-fh-field]').forEach(chk => {
            chk.addEventListener('change', () => {
                const fName = chk.getAttribute('data-fh-field');
                if (chk.checked) {
                    if (window.plState.workingTrackedFields.length >= 20) {
                        alert('You can track up to 20 fields maximum per module.');
                        chk.checked = false;
                        return;
                    }
                    window.plState.workingTrackedFields.push(fName);
                } else {
                    window.plState.workingTrackedFields = window.plState.workingTrackedFields.filter(x => x !== fName);
                }
                renderModals();
            });
        });
        document.getElementById('plSaveFhBtn')?.addEventListener('click', async () => {
            try {
                const res = await fetch(`/api/field-history/config/${encodeURIComponent(window.plState.selectedModule)}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken()
                    },
                    body: JSON.stringify({
                        tracked_fields: window.plState.workingTrackedFields,
                        enabled: true
                    })
                });
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'Error saving field history');
                window.plState.fieldHistoryConfig = data.config || data;
                window.plState.isFieldHistoryModalOpen = false;
                setFeedback('Field history tracking saved successfully!');
                renderModals();
                renderHeader();
            } catch (err) {
                console.error('Error saving field history:', err);
                alert('Error: ' + err.message);
            }
        });

        document.getElementById('plCloseVisModal')?.addEventListener('click', () => {
            window.plState.visibilityModalTarget = null;
            renderModals();
        });
        document.getElementById('plCancelVisBtn')?.addEventListener('click', () => {
            window.plState.visibilityModalTarget = null;
            renderModals();
        });
        document.getElementById('plSaveVisBtn')?.addEventListener('click', () => {
            const tgt = window.plState.visibilityModalTarget;
            if (!tgt || !window.plState.currentLayoutDefinition) return;

            const isEnabled = document.getElementById('plVisEnabled')?.checked;
            const matchType = document.getElementById('plVisMatchType')?.value || 'ALL';

            const conditions = [];
            const rows = container.querySelectorAll('#plVisConditionsList > div');
            rows.forEach(r => {
                const f = r.querySelector('.pl-vis-field')?.value;
                const op = r.querySelector('.pl-vis-op')?.value;
                const v = r.querySelector('.pl-vis-val')?.value;
                if (f) conditions.push({ field: f, operator: op, value: v });
            });

            const rule = isEnabled ? { enabled: true, matchType: matchType, conditions: conditions } : undefined;

            if (tgt.type === 'section') {
                const sec = window.plState.currentLayoutDefinition.sections.find(s => s.id === tgt.sectionId);
                if (sec) sec.visibilityRule = rule;
            } else if (tgt.type === 'field' && tgt.fieldName) {
                const sec = window.plState.currentLayoutDefinition.sections.find(s => s.id === tgt.sectionId);
                const f = sec?.fields?.find(x => x.name === tgt.fieldName);
                if (f) f.visibilityRule = rule;
            }

            window.plState.visibilityModalTarget = null;
            renderModals();
            renderFieldsTab();
        });
    }

    function renderApp() {
        renderHeader();
        renderSubTabsStrip();
        renderTabContent();
        renderModals();
    }

    // =========================================================================
    // INITIALIZATION & SSR HYDRATION
    // =========================================================================

    function initPageLayouts() {
        const root = document.getElementById('plStudioRoot');
        if (!root) return;

        // Read SSR payload if available
        const payloadScript = document.getElementById('plServerData');
        if (payloadScript) {
            try {
                const data = JSON.parse(payloadScript.textContent);
                window.plState.selectedModule = data.initialModule || 'leads';
                window.plState.customObjects = data.customObjects || [];
                window.plState.profiles = data.profiles || [];
                window.plState.layouts = data.layouts || [];
                window.plState.profileAssignments = data.assignments || {};
                computeAvailableFields(window.plState.selectedModule);

                if (window.plState.layouts.length > 0) {
                    const defaultL = window.plState.layouts.find(l => l.is_default) || window.plState.layouts[0];
                    selectLayout(defaultL);
                } else {
                    loadLayoutsForModule(window.plState.selectedModule);
                }
                return;
            } catch (err) {
                console.warn('Failed to parse plServerData, falling back to API:', err);
            }
        }

        loadLayoutsForModule(window.plState.selectedModule);
    }

    window.initPageLayouts = initPageLayouts;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPageLayouts);
    } else {
        initPageLayouts();
    }

})();
