<?php

namespace App\Services\Admin;

use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Models\DocumentTemplate;
use App\Models\DocumentGovernanceRight;
use App\Models\Profile;

class AdminDocumentTemplateService
{
    use AdminTenantTrait;

    public function documentTemplates(Request $request)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);
        $this->ensureDefaultDocumentTemplates($tenant);
        $this->ensureDefaultDocumentGovernanceRights($tenant);

        $query = DocumentTemplate::on('tenant')->orderBy('document_type')->orderByDesc('is_default')->orderBy('name');

        if ($request->filled('document_type') && $request->input('document_type') !== 'all') {
            $query->where('document_type', $request->input('document_type'));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('search')) {
            $s = '%' . strtolower($request->input('search')) . '%';
            $query->where(function ($q) use ($s) {
                $q->whereRaw('LOWER(name) LIKE ?', [$s])
                  ->orWhereRaw('LOWER(document_title) LIKE ?', [$s])
                  ->orWhereRaw('LOWER(description) LIKE ?', [$s]);
            });
        }

        $templates = $query->get();

        // Load profiles and governance rights
        $profiles = Profile::on('tenant')->orderBy('name')->get();
        $rightsMap = DocumentGovernanceRight::on('tenant')->get()->keyBy('profile_id');

        $governanceRights = $profiles->map(function ($p) use ($rightsMap) {
            $right = $rightsMap->get($p->id);
            return [
                'profile_id'                    => (string) $p->id,
                'profile_name'                  => $p->name,
                'is_admin'                      => (bool) ($p->is_admin ?? false),
                'can_generate_from_opportunity' => $right ? (bool) $right->can_generate_from_opportunity : true,
                'opportunity_required_stage'    => $right->opportunity_required_stage ?? 'Closed Won',
                'opportunity_condition_json'    => $right->opportunity_condition_json ?? null,
                'can_generate_from_deal'        => $right ? (bool) $right->can_generate_from_deal : true,
                'deal_required_stage'           => $right->deal_required_stage ?? 'Closed Won',
                'deal_condition_json'           => $right->deal_condition_json ?? null,
                'can_direct_create_in_ledger'   => $right ? (bool) $right->can_direct_create_in_ledger : ($p->is_admin ? true : false),
                'allowed_ledger_types'          => $right->allowed_ledger_types ?? 'all',
                'ledger_record_scope'           => $right->ledger_record_scope ?? 'all',
            ];
        });

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success'           => true,
                'data'              => $templates,
                'governance_rights' => $governanceRights,
            ]);
        }

        return view('admin.document_templates', compact('templates', 'profiles', 'governanceRights', 'tenant'));
    }

    public function getDocumentTemplatesApi(Request $request)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);
        $this->ensureDefaultDocumentTemplates($tenant);

        $query = DocumentTemplate::on('tenant')->orderBy('document_type')->orderByDesc('is_default')->orderBy('name');

        if ($request->filled('document_type') && $request->input('document_type') !== 'all') {
            $query->where('document_type', $request->input('document_type'));
        }

        if ($request->has('is_active')) {
            $query->where('is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN));
        }

        return response()->json([
            'success' => true,
            'data'    => $query->get(),
        ]);
    }

    public function getDocumentTemplateApi(Request $request, $id)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);

        $template = DocumentTemplate::on('tenant')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $template,
        ]);
    }

    public function storeDocumentTemplateApi(Request $request)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);

        $validated = $request->validate([
            'name'                      => 'required|string|max:150',
            'document_type'             => 'required|string|in:invoice,po,quote',
            'description'               => 'nullable|string',
            'is_default'                => 'boolean',
            'is_active'                 => 'boolean',
            'paper_size'                => 'nullable|string|max:20',
            'orientation'               => 'nullable|string|max:20',
            'margin_size'               => 'nullable|string|max:20',
            'font_family'               => 'nullable|string|max:100',
            'primary_color'             => 'nullable|string|max:30',
            'accent_color'              => 'nullable|string|max:30',
            'table_theme'               => 'nullable|string|max:30',
            'layout_style'              => 'nullable|string|max:40',
            'logo_url'                  => 'nullable|string',
            'logo_position'             => 'nullable|string|max:20',
            'logo_height'               => 'nullable|integer',
            'company_name'              => 'nullable|string|max:150',
            'company_address'           => 'nullable|string',
            'company_tax_id'            => 'nullable|string|max:60',
            'company_phone'             => 'nullable|string|max:40',
            'company_email'             => 'nullable|string|max:150',
            'company_website'           => 'nullable|string|max:255',
            'document_title'            => 'nullable|string|max:150',
            'watermark_config'          => 'nullable',
            'columns_config'            => 'nullable',
            'dynamic_fields_config'     => 'nullable',
            'payment_terms_default'     => 'nullable|string|max:60',
            'bank_details'              => 'nullable|string',
            'qr_code_url'               => 'nullable|string',
            'notes_default'             => 'nullable|string',
            'terms_conditions'          => 'nullable|string',
            'show_signatory'            => 'boolean',
            'signatory_title'           => 'nullable|string|max:100',
            'signatory_name'            => 'nullable|string|max:150',
            'signature_image_url'       => 'nullable|string',
            'show_shipping_address'     => 'boolean',
            'agreement_pages'           => 'nullable',
            'save_to_files_destination' => 'nullable|string|max:40',
            'allow_direct_email'        => 'boolean',
            'requires_approval'         => 'boolean',
            'approval_process_id'       => 'nullable|integer',
        ]);

        if (empty($validated['document_title'])) {
            $validated['document_title'] = $validated['document_type'] === 'invoice'
                ? 'TAX INVOICE'
                : ($validated['document_type'] === 'po' ? 'PURCHASE ORDER' : 'PRICE QUOTATION');
        }

        if (!empty($validated['is_default'])) {
            DocumentTemplate::on('tenant')
                ->where('document_type', $validated['document_type'])
                ->update(['is_default' => false]);
        }

        $validated['created_by'] = auth()->user()->name ?? 'System Admin';

        $template = DocumentTemplate::on('tenant')->create($validated);

        return response()->json([
            'success' => true,
            'message' => "Document template '{$template->name}' created successfully!",
            'data'    => $template,
        ], 201);
    }

    public function updateDocumentTemplateApi(Request $request, $id)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);

        $template = DocumentTemplate::on('tenant')->findOrFail($id);

        $validated = $request->validate([
            'name'                      => 'sometimes|required|string|max:150',
            'document_type'             => 'sometimes|required|string|in:invoice,po,quote',
            'description'               => 'nullable|string',
            'is_default'                => 'boolean',
            'is_active'                 => 'boolean',
            'paper_size'                => 'nullable|string|max:20',
            'orientation'               => 'nullable|string|max:20',
            'margin_size'               => 'nullable|string|max:20',
            'font_family'               => 'nullable|string|max:100',
            'primary_color'             => 'nullable|string|max:30',
            'accent_color'              => 'nullable|string|max:30',
            'table_theme'               => 'nullable|string|max:30',
            'layout_style'              => 'nullable|string|max:40',
            'logo_url'                  => 'nullable|string',
            'logo_position'             => 'nullable|string|max:20',
            'logo_height'               => 'nullable|integer',
            'company_name'              => 'nullable|string|max:150',
            'company_address'           => 'nullable|string',
            'company_tax_id'            => 'nullable|string|max:60',
            'company_phone'             => 'nullable|string|max:40',
            'company_email'             => 'nullable|string|max:150',
            'company_website'           => 'nullable|string|max:255',
            'document_title'            => 'nullable|string|max:150',
            'watermark_config'          => 'nullable',
            'columns_config'            => 'nullable',
            'dynamic_fields_config'     => 'nullable',
            'payment_terms_default'     => 'nullable|string|max:60',
            'bank_details'              => 'nullable|string',
            'qr_code_url'               => 'nullable|string',
            'notes_default'             => 'nullable|string',
            'terms_conditions'          => 'nullable|string',
            'show_signatory'            => 'boolean',
            'signatory_title'           => 'nullable|string|max:100',
            'signatory_name'            => 'nullable|string|max:150',
            'signature_image_url'       => 'nullable|string',
            'show_shipping_address'     => 'boolean',
            'agreement_pages'           => 'nullable',
            'save_to_files_destination' => 'nullable|string|max:40',
            'allow_direct_email'        => 'boolean',
            'requires_approval'         => 'boolean',
            'approval_process_id'       => 'nullable|integer',
        ]);

        if (!empty($validated['is_default'])) {
            $docType = $validated['document_type'] ?? $template->document_type;
            DocumentTemplate::on('tenant')
                ->where('document_type', $docType)
                ->where('id', '!=', $template->id)
                ->update(['is_default' => false]);
        }

        $template->update($validated);

        return response()->json([
            'success' => true,
            'message' => "Document template '{$template->name}' updated successfully!",
            'data'    => $template->fresh(),
        ]);
    }

    public function setDefaultDocumentTemplateApi(Request $request, $id)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);

        $template = DocumentTemplate::on('tenant')->findOrFail($id);

        DocumentTemplate::on('tenant')
            ->where('document_type', $template->document_type)
            ->where('id', '!=', $template->id)
            ->update(['is_default' => false]);

        $template->update(['is_default' => true]);

        return response()->json([
            'success' => true,
            'message' => "'{$template->name}' is now the default " . strtoupper($template->document_type) . " template.",
            'data'    => $template->fresh(),
        ]);
    }

    public function cloneDocumentTemplateApi(Request $request, $id)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);

        $source = DocumentTemplate::on('tenant')->findOrFail($id);

        $cloneData = $source->replicate(['is_default'])->toArray();
        $cloneData['name'] = "Copy of {$source->name}";
        $cloneData['is_default'] = false;
        $cloneData['created_by'] = auth()->user()->name ?? 'System Admin';

        $cloned = DocumentTemplate::on('tenant')->create($cloneData);

        return response()->json([
            'success' => true,
            'message' => "Created copy of '{$source->name}'",
            'data'    => $cloned,
        ]);
    }

    public function deleteDocumentTemplateApi(Request $request, $id)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);

        $template = DocumentTemplate::on('tenant')->findOrFail($id);

        if ($template->is_default) {
            return response()->json([
                'success' => false,
                'message' => "Cannot delete default template. Please designate another template as default first.",
            ], 422);
        }

        $template->delete();

        return response()->json([
            'success' => true,
            'message' => "Template deleted successfully",
        ]);
    }

    public function getDocumentGovernanceRightsApi(Request $request)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);
        $this->ensureDefaultDocumentGovernanceRights($tenant);

        $profiles = Profile::on('tenant')->orderBy('name')->get();
        $rightsMap = DocumentGovernanceRight::on('tenant')->get()->keyBy('profile_id');

        $data = $profiles->map(function ($p) use ($rightsMap) {
            $right = $rightsMap->get($p->id);
            return [
                'profile_id'                    => (string) $p->id,
                'profile_name'                  => $p->name,
                'is_admin'                      => (bool) ($p->is_admin ?? false),
                'can_generate_from_opportunity' => $right ? (bool) $right->can_generate_from_opportunity : true,
                'opportunity_required_stage'    => $right->opportunity_required_stage ?? 'Closed Won',
                'opportunity_condition_json'    => $right->opportunity_condition_json ?? null,
                'can_generate_from_deal'        => $right ? (bool) $right->can_generate_from_deal : true,
                'deal_required_stage'           => $right->deal_required_stage ?? 'Closed Won',
                'deal_condition_json'           => $right->deal_condition_json ?? null,
                'can_direct_create_in_ledger'   => $right ? (bool) $right->can_direct_create_in_ledger : ($p->is_admin ? true : false),
                'allowed_ledger_types'          => $right->allowed_ledger_types ?? 'all',
                'ledger_record_scope'           => $right->ledger_record_scope ?? 'all',
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    public function getMyDocumentGovernanceRights(Request $request)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);

        $user = auth()->user();
        $profileId = $user->profile_id ?? null;

        $right = $profileId ? DocumentGovernanceRight::on('tenant')->where('profile_id', $profileId)->first() : null;

        $isAdmin = ($user->role ?? '') === 'admin' || ($user->profile->is_admin ?? false);

        return response()->json([
            'success' => true,
            'data'    => [
                'profile_id'                    => (string) ($profileId ?? '1'),
                'profile_name'                  => $user->profile->name ?? 'Standard User',
                'is_admin'                      => $isAdmin,
                'can_generate_from_opportunity' => $right ? (bool) $right->can_generate_from_opportunity : true,
                'opportunity_required_stage'    => $right->opportunity_required_stage ?? 'Closed Won',
                'opportunity_condition_json'    => $right->opportunity_condition_json ?? null,
                'can_generate_from_deal'        => $right ? (bool) $right->can_generate_from_deal : true,
                'deal_required_stage'           => $right->deal_required_stage ?? 'Closed Won',
                'deal_condition_json'           => $right->deal_condition_json ?? null,
                'can_direct_create_in_ledger'   => $right ? (bool) $right->can_direct_create_in_ledger : $isAdmin,
                'allowed_ledger_types'          => $right->allowed_ledger_types ?? 'all',
                'ledger_record_scope'           => $right->ledger_record_scope ?? 'all',
            ],
        ]);
    }

    public function updateDocumentGovernanceRightApi(Request $request, $profileId)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);

        $validated = $request->validate([
            'can_generate_from_opportunity' => 'nullable|boolean',
            'opportunity_required_stage'    => 'nullable|string|max:60',
            'opportunity_condition_json'    => 'nullable|string',
            'can_generate_from_deal'        => 'nullable|boolean',
            'deal_required_stage'           => 'nullable|string|max:60',
            'deal_condition_json'           => 'nullable|string',
            'can_direct_create_in_ledger'   => 'nullable|boolean',
            'allowed_ledger_types'          => 'nullable|string|in:all,invoices_quotes,purchase_orders,none',
            'ledger_record_scope'           => 'nullable|string|in:all,team,own,none',
        ]);

        $right = DocumentGovernanceRight::on('tenant')->updateOrCreate(
            ['profile_id' => $profileId],
            $validated
        );

        return response()->json([
            'success' => true,
            'message' => 'Document governance rights updated successfully',
            'data'    => $right,
        ]);
    }

    public function ensureDefaultDocumentTemplates(?Tenant $tenant = null): void
    {
        $this->ensureTenantConnection($tenant);

        if (DocumentTemplate::on('tenant')->count() > 0) {
            return;
        }

        $defaultColumns = [
            ['key' => 'item_number', 'label' => '#', 'visible' => true, 'align' => 'center', 'width' => '5%'],
            ['key' => 'sku', 'label' => 'Item / SKU', 'visible' => true, 'align' => 'left', 'width' => '15%'],
            ['key' => 'description', 'label' => 'Description', 'visible' => true, 'align' => 'left', 'width' => '33%'],
            ['key' => 'hsn_sac_code', 'label' => 'HSN/SAC', 'visible' => true, 'align' => 'center', 'width' => '9%'],
            ['key' => 'quantity', 'label' => 'Qty', 'visible' => true, 'align' => 'right', 'width' => '8%'],
            ['key' => 'unit_price', 'label' => 'Rate', 'visible' => true, 'align' => 'right', 'width' => '10%'],
            ['key' => 'discount_rate', 'label' => 'Disc %', 'visible' => true, 'align' => 'right', 'width' => '7%'],
            ['key' => 'tax_rate', 'label' => 'Tax %', 'visible' => true, 'align' => 'right', 'width' => '6%'],
            ['key' => 'total_amount', 'label' => 'Amount', 'visible' => true, 'align' => 'right', 'width' => '12%'],
        ];

        $defaultDynamic = [
            'enable_order_discount' => true,
            'discount_type'         => 'percentage',
            'default_discount_rate' => 0,
            'enable_tax_summary'    => true,
            'tax_mode'              => 'split_gst',
            'default_tax_rate'      => 18,
            'enable_shipping'       => true,
            'enable_round_off'      => true,
            'enable_amount_in_words'=> true,
            'custom_adjustments'    => [],
        ];

        $defaultWatermark = [
            'enabled'     => false,
            'type'        => 'text',
            'text'        => 'CONFIDENTIAL',
            'fontSize'    => 54,
            'color'       => '#ef4444',
            'opacity'     => 0.15,
            'rotation'    => -30,
            'position'    => 'center',
            'borderStyle' => 'solid',
        ];

        $defaultAgreement = [
            [
                'id'                       => 'agree-default-1',
                'title'                    => 'Standard Terms & Conditions',
                'content_html'             => '<h4>1. Scope & Execution</h4><p>This document constitutes a binding commercial agreement under prevailing enterprise regulations.</p>',
                'require_client_signature' => true,
            ]
        ];

        $templates = [
            [
                'name'                      => 'Modern Executive Teal Invoice',
                'document_type'             => 'invoice',
                'description'               => 'Recommended executive billing template with teal accents and dual dock cards.',
                'is_default'                => true,
                'is_active'                 => true,
                'paper_size'                => 'A4',
                'orientation'               => 'portrait',
                'margin_size'               => 'standard',
                'font_family'               => 'Inter, sans-serif',
                'primary_color'             => '#0f172a',
                'accent_color'              => '#0d9488',
                'table_theme'               => 'zebra',
                'layout_style'              => 'modern_teal',
                'logo_url'                  => '',
                'logo_position'             => 'left',
                'logo_height'               => 48,
                'company_name'              => 'DealSync Enterprise Solutions',
                'company_address'           => '100 Silicon Vista, Suite 400, San Jose, CA 95110',
                'company_tax_id'            => 'US-EIN-94-2819201',
                'company_phone'             => '+1 (555) 492-0199',
                'company_email'             => 'billing@dealsync.io',
                'company_website'           => 'https://dealsync.io',
                'document_title'            => 'TAX INVOICE',
                'watermark_config'          => $defaultWatermark,
                'columns_config'            => $defaultColumns,
                'dynamic_fields_config'     => $defaultDynamic,
                'payment_terms_default'     => 'Net 30',
                'bank_details'              => "Silicon Valley Commercial Bank\nA/C Name: DealSync Enterprise\nA/C: 9820-4102-9841\nIFSC/Routing: SVCB0002910",
                'qr_code_url'               => '',
                'notes_default'             => 'Thank you for your partnership! Wire transfer remittance preferred.',
                'terms_conditions'          => 'Payment due within thirty (30) days from issue date. Late interest accrues at 1.5% per month.',
                'show_signatory'            => true,
                'signatory_title'           => 'Chief Financial Officer',
                'signatory_name'            => 'Finance Directorate',
                'signature_image_url'       => '',
                'show_shipping_address'     => true,
                'agreement_pages'           => $defaultAgreement,
                'save_to_files_destination' => 'files_public',
                'allow_direct_email'        => true,
                'requires_approval'         => false,
                'created_by'                => 'System Administrator',
            ],
            [
                'name'                      => 'Corporate Classic Navy PO',
                'document_type'             => 'po',
                'description'               => 'Formal structured procurement purchase order with navy headers and grid lines.',
                'is_default'                => true,
                'is_active'                 => true,
                'paper_size'                => 'A4',
                'orientation'               => 'portrait',
                'margin_size'               => 'standard',
                'font_family'               => 'Roboto, sans-serif',
                'primary_color'             => '#0f172a',
                'accent_color'              => '#1e3a8a',
                'table_theme'               => 'grid',
                'layout_style'              => 'classic_navy',
                'logo_url'                  => '',
                'logo_position'             => 'left',
                'logo_height'               => 48,
                'company_name'              => 'DealSync Global Procurement',
                'company_address'           => '100 Silicon Vista, Suite 400, San Jose, CA 95110',
                'company_tax_id'            => 'US-EIN-94-2819201',
                'company_phone'             => '+1 (555) 492-0199',
                'company_email'             => 'procurement@dealsync.io',
                'company_website'           => 'https://dealsync.io',
                'document_title'            => 'PURCHASE ORDER',
                'watermark_config'          => $defaultWatermark,
                'columns_config'            => $defaultColumns,
                'dynamic_fields_config'     => $defaultDynamic,
                'payment_terms_default'     => 'Net 45',
                'bank_details'              => "Silicon Valley Commercial Bank\nA/C Name: DealSync Enterprise\nA/C: 9820-4102-9841",
                'qr_code_url'               => '',
                'notes_default'             => 'Please confirm dispatch schedule within 48 hours. Items subject to dock inspection.',
                'terms_conditions'          => 'Goods must conform to strict ISO 9001 quality specifications. Defective lots returned at vendor expense.',
                'show_signatory'            => true,
                'signatory_title'           => 'Procurement Director',
                'signatory_name'            => 'Global Supply Chain',
                'signature_image_url'       => '',
                'show_shipping_address'     => true,
                'agreement_pages'           => $defaultAgreement,
                'save_to_files_destination' => 'files_public',
                'allow_direct_email'        => true,
                'requires_approval'         => false,
                'created_by'                => 'System Administrator',
            ],
            [
                'name'                      => 'Executive Minimal Quotation',
                'document_type'             => 'quote',
                'description'               => 'High-impact commercial price quotation with clean typography and purple accents.',
                'is_default'                => true,
                'is_active'                 => true,
                'paper_size'                => 'A4',
                'orientation'               => 'portrait',
                'margin_size'               => 'spacious',
                'font_family'               => 'Inter, sans-serif',
                'primary_color'             => '#18181b',
                'accent_color'              => '#7c3aed',
                'table_theme'               => 'minimal',
                'layout_style'              => 'minimal_slate',
                'logo_url'                  => '',
                'logo_position'             => 'left',
                'logo_height'               => 48,
                'company_name'              => 'DealSync Commercial Solutions',
                'company_address'           => '100 Silicon Vista, Suite 400, San Jose, CA 95110',
                'company_tax_id'            => 'US-EIN-94-2819201',
                'company_phone'             => '+1 (555) 492-0199',
                'company_email'             => 'sales@dealsync.io',
                'company_website'           => 'https://dealsync.io',
                'document_title'            => 'PRICE QUOTATION',
                'watermark_config'          => $defaultWatermark,
                'columns_config'            => $defaultColumns,
                'dynamic_fields_config'     => $defaultDynamic,
                'payment_terms_default'     => 'Due on Receipt',
                'bank_details'              => "Silicon Valley Commercial Bank\nA/C Name: DealSync Enterprise\nA/C: 9820-4102-9841",
                'qr_code_url'               => '',
                'notes_default'             => 'Commercial estimate valid for 30 calendar days from quotation date.',
                'terms_conditions'          => 'Scope changes after sign-off will be quoted as additional change orders.',
                'show_signatory'            => true,
                'signatory_title'           => 'VP of Strategic Accounts',
                'signatory_name'            => 'Commercial Directorate',
                'signature_image_url'       => '',
                'show_shipping_address'     => true,
                'agreement_pages'           => $defaultAgreement,
                'save_to_files_destination' => 'files_public',
                'allow_direct_email'        => true,
                'requires_approval'         => false,
                'created_by'                => 'System Administrator',
            ],
        ];

        foreach ($templates as $t) {
            DocumentTemplate::on('tenant')->create($t);
        }
    }

    public function ensureDefaultDocumentGovernanceRights(?Tenant $tenant = null): void
    {
        $this->ensureTenantConnection($tenant);

        $profiles = Profile::on('tenant')->get();

        foreach ($profiles as $p) {
            if (DocumentGovernanceRight::on('tenant')->where('profile_id', $p->id)->exists()) {
                continue;
            }

            $isAdmin = (bool) ($p->is_admin ?? false);

            DocumentGovernanceRight::on('tenant')->create([
                'profile_id'                    => $p->id,
                'can_generate_from_opportunity' => true,
                'opportunity_required_stage'    => 'Closed Won',
                'opportunity_condition_json'    => json_encode(['field' => 'stage', 'operator' => 'equals', 'value' => 'Closed Won']),
                'can_generate_from_deal'        => true,
                'deal_required_stage'           => 'Closed Won',
                'deal_condition_json'           => json_encode(['field' => 'stage', 'operator' => 'equals', 'value' => 'Closed Won']),
                'can_direct_create_in_ledger'   => $isAdmin,
                'allowed_ledger_types'          => $isAdmin ? 'all' : 'invoices_quotes',
                'ledger_record_scope'           => $isAdmin ? 'all' : 'team',
            ]);
        }
    }
}
