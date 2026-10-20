<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Contact;
use App\Models\DocumentGovernanceRight;
use App\Models\DocumentLineItem;
use App\Models\DocumentTemplate;
use App\Models\GeneratedDocument;
use App\Models\Opportunity;
use App\Models\Profile;
use App\Models\PurchaseDeal;
use App\Models\Tenant;
use App\Models\TenantUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DocumentController extends Controller
{
    /**
     * Ensure active tenant database connection.
     */
    protected function ensureTenantConnection(?Tenant $tenant = null)
    {
        if (config('database.connections.tenant.database')) {
            return;
        }

        $tenant = $tenant ?? Tenant::where('is_active', true)->first();
        if ($tenant) {
            config(['database.connections.tenant.database' => $tenant->database_name ?? ('crm_' . $tenant->subdomain)]);
            DB::purge('tenant');
            DB::reconnect('tenant');
        }
    }

    /**
     * Ensure default document templates exist for Invoices, Purchase Orders, and Quotations.
     */
    protected function ensureDefaultTemplates(): void
    {
        try {
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
                'discount_type' => 'percentage',
                'default_discount_rate' => 0,
                'enable_tax_summary' => true,
                'tax_mode' => 'split_gst',
                'default_tax_rate' => 18,
                'enable_shipping' => true,
                'enable_round_off' => true,
                'enable_amount_in_words' => true,
                'custom_adjustments' => [],
            ];

            $defaultWatermark = [
                'enabled' => false,
                'type' => 'text',
                'text' => 'CONFIDENTIAL',
                'fontSize' => 54,
                'color' => '#ef4444',
                'opacity' => 0.15,
                'rotation' => -30,
                'position' => 'center',
                'borderStyle' => 'solid',
            ];

            $templates = [
                [
                    'name' => 'Standard Commercial Invoice',
                    'document_type' => 'invoice',
                    'description' => 'Default corporate invoice with tax breakdown and bank wire instructions.',
                    'is_default' => true,
                    'is_active' => true,
                    'paper_size' => 'A4',
                    'orientation' => 'portrait',
                    'margin_size' => 'standard',
                    'font_family' => 'Inter, sans-serif',
                    'primary_color' => '#0f172a',
                    'accent_color' => '#2563eb',
                    'table_theme' => 'zebra',
                    'layout_style' => 'modern_teal',
                    'company_name' => 'DealSync Enterprise Technologies Inc.',
                    'company_address' => "100 Innovation Way, Suite 400\nSilicon Valley, CA 94025\nUnited States",
                    'company_tax_id' => 'US-EIN-94-3829104',
                    'company_phone' => '+1 (800) 555-0199',
                    'company_email' => 'billing@dealsync-crm.com',
                    'company_website' => 'https://dealsync-crm.com',
                    'document_title' => 'COMMERCIAL TAX INVOICE',
                    'watermark_config' => $defaultWatermark,
                    'columns_config' => $defaultColumns,
                    'dynamic_fields_config' => $defaultDynamic,
                    'payment_terms_default' => 'Net 30 Days',
                    'bank_details' => "Bank: Silicon Valley Corporate Bank\nRouting: 121000358\nAccount #: 9845-2018-4491\nSWIFT: SVBKUS6S",
                    'notes_default' => 'Thank you for choosing DealSync. Payments received after 30 days are subject to standard late interest.',
                    'terms_conditions' => "1. Goods/services remain property of seller until paid in full.\n2. Invoices disputed within 7 days of delivery.\n3. Wire transfer fees payable by remitter.",
                    'show_signatory' => true,
                    'signatory_title' => 'VP of Finance & Operations',
                    'signatory_name' => 'Michael Vance, CPA',
                    'show_shipping_address' => true,
                    'save_to_files_destination' => 'files_public',
                    'allow_direct_email' => true,
                    'requires_approval' => false,
                    'created_by' => 'System Admin',
                ],
                [
                    'name' => 'Procurement Purchase Order',
                    'document_type' => 'po',
                    'description' => 'Supplier purchase order authorizing vendor delivery and terms.',
                    'is_default' => true,
                    'is_active' => true,
                    'paper_size' => 'A4',
                    'orientation' => 'portrait',
                    'margin_size' => 'standard',
                    'font_family' => 'Inter, sans-serif',
                    'primary_color' => '#1e1b4b',
                    'accent_color' => '#7c3aed',
                    'table_theme' => 'grid',
                    'layout_style' => 'classic_navy',
                    'company_name' => 'DealSync Enterprise Technologies Inc.',
                    'company_address' => "100 Innovation Way, Suite 400\nSilicon Valley, CA 94025\nUnited States",
                    'company_tax_id' => 'US-EIN-94-3829104',
                    'company_phone' => '+1 (800) 555-0199',
                    'company_email' => 'procurement@dealsync-crm.com',
                    'company_website' => 'https://dealsync-crm.com',
                    'document_title' => 'PURCHASE ORDER',
                    'watermark_config' => $defaultWatermark,
                    'columns_config' => $defaultColumns,
                    'dynamic_fields_config' => $defaultDynamic,
                    'payment_terms_default' => 'Net 45 Days',
                    'bank_details' => null,
                    'notes_default' => 'Please confirm delivery schedule and tracking number within 48 hours of order acceptance.',
                    'terms_conditions' => "1. Supplier agrees to all specifications outlined in PO.\n2. Defective materials subject to immediate return at vendor expense.\n3. Net 45 upon verified dock delivery.",
                    'show_signatory' => true,
                    'signatory_title' => 'Head of Global Procurement',
                    'signatory_name' => 'Samantha Cruz',
                    'show_shipping_address' => true,
                    'save_to_files_destination' => 'files_public',
                    'allow_direct_email' => true,
                    'requires_approval' => true,
                    'created_by' => 'System Admin',
                ],
                [
                    'name' => 'Customer Quotation & Proposal',
                    'document_type' => 'quote',
                    'description' => 'Formal price quotation with line-item discounts and validity clauses.',
                    'is_default' => true,
                    'is_active' => true,
                    'paper_size' => 'A4',
                    'orientation' => 'portrait',
                    'margin_size' => 'standard',
                    'font_family' => 'Inter, sans-serif',
                    'primary_color' => '#064e3b',
                    'accent_color' => '#16a34a',
                    'table_theme' => 'minimal',
                    'layout_style' => 'minimal_slate',
                    'company_name' => 'DealSync Enterprise Technologies Inc.',
                    'company_address' => "100 Innovation Way, Suite 400\nSilicon Valley, CA 94025\nUnited States",
                    'company_tax_id' => 'US-EIN-94-3829104',
                    'company_phone' => '+1 (800) 555-0199',
                    'company_email' => 'sales@dealsync-crm.com',
                    'company_website' => 'https://dealsync-crm.com',
                    'document_title' => 'PRICE QUOTATION & SCOPE',
                    'watermark_config' => $defaultWatermark,
                    'columns_config' => $defaultColumns,
                    'dynamic_fields_config' => $defaultDynamic,
                    'payment_terms_default' => 'Net 15 Days',
                    'bank_details' => null,
                    'notes_default' => 'This quotation is valid for 30 calendar days from the date of issuance.',
                    'terms_conditions' => "1. Pricing guaranteed for 30 days.\n2. Custom software deliverables subject to standard MSA.\n3. Taxes calculated based on customer billing jurisdiction.",
                    'show_signatory' => true,
                    'signatory_title' => 'Enterprise Sales Director',
                    'signatory_name' => 'Alexander Reed',
                    'show_shipping_address' => false,
                    'save_to_files_destination' => 'files_public',
                    'allow_direct_email' => true,
                    'requires_approval' => false,
                    'created_by' => 'System Admin',
                ],
            ];

            foreach ($templates as $t) {
                DocumentTemplate::on('tenant')->create($t);
            }
        } catch (\Throwable $e) {
            // Silently continue if tables exist
        }
    }

    /**
     * Ensure realistic demo documents exist in the ledger.
     */
    protected function ensureDemoDocuments(): void
    {
        try {
            if (GeneratedDocument::on('tenant')->count() > 0) {
                return;
            }

            $accounts = Account::on('tenant')->get()->keyBy('name');
            $templates = DocumentTemplate::on('tenant')->get()->keyBy('document_type');
            $user = TenantUser::on('tenant')->first();
            $userId = $user?->id;
            $userName = $user?->name ?? 'System Admin';

            $demoDocs = [
                [
                    'document_number' => 'INV-2026-001',
                    'document_type' => 'invoice',
                    'status' => 'sent',
                    'is_locked' => false,
                    'account_name' => 'Apex Technologies',
                    'issue_date' => date('Y-m-d', strtotime('-12 days')),
                    'due_date' => date('Y-m-d', strtotime('+18 days')),
                    'payment_terms' => 'Net 30 Days',
                    'currency' => 'USD',
                    'subtotal' => 14250.00,
                    'discount_rate' => 5.0,
                    'discount_amount' => 712.50,
                    'tax_amount' => 1083.00,
                    'shipping_amount' => 0.00,
                    'adjustment_amount' => 0.00,
                    'grand_total' => 14620.50,
                    'paid_amount' => 5000.00,
                    'balance_due' => 9620.50,
                    'notes' => 'Q1 Enterprise Cloud License & Onboarding Package.',
                    'items' => [
                        ['sku' => 'LIC-ENT-01', 'description' => 'DealSync Cloud Enterprise Annual Seat (10 Users)', 'quantity' => 10, 'unit_price' => 1200.00, 'discount_rate' => 5.0, 'tax_rate' => 8.0],
                        ['sku' => 'SRV-ONB-01', 'description' => 'Dedicated Implementation & Custom Data Migration', 'quantity' => 1, 'unit_price' => 2250.00, 'discount_rate' => 5.0, 'tax_rate' => 8.0],
                    ],
                ],
                [
                    'document_number' => 'INV-2026-002',
                    'document_type' => 'invoice',
                    'status' => 'paid',
                    'is_locked' => true,
                    'account_name' => 'Globex International',
                    'issue_date' => date('Y-m-d', strtotime('-25 days')),
                    'due_date' => date('Y-m-d', strtotime('+5 days')),
                    'payment_terms' => 'Net 30 Days',
                    'currency' => 'USD',
                    'subtotal' => 28400.00,
                    'discount_rate' => 0.0,
                    'discount_amount' => 0.00,
                    'tax_amount' => 2272.00,
                    'shipping_amount' => 0.00,
                    'adjustment_amount' => 0.00,
                    'grand_total' => 30672.00,
                    'paid_amount' => 30672.00,
                    'balance_due' => 0.00,
                    'notes' => 'Fully settled via Wire Transfer ref #WT-99214.',
                    'items' => [
                        ['sku' => 'HW-SRV-40', 'description' => 'High-Performance Private Cloud Appliance Cluster', 'quantity' => 2, 'unit_price' => 14200.00, 'discount_rate' => 0.0, 'tax_rate' => 8.0],
                    ],
                ],
                [
                    'document_number' => 'PO-2026-001',
                    'document_type' => 'po',
                    'status' => 'approved',
                    'is_locked' => false,
                    'account_name' => 'Initech Supplies',
                    'issue_date' => date('Y-m-d', strtotime('-8 days')),
                    'due_date' => date('Y-m-d', strtotime('+37 days')),
                    'payment_terms' => 'Net 45 Days',
                    'currency' => 'USD',
                    'subtotal' => 8750.00,
                    'discount_rate' => 0.0,
                    'discount_amount' => 0.00,
                    'tax_amount' => 700.00,
                    'shipping_amount' => 150.00,
                    'adjustment_amount' => 0.00,
                    'grand_total' => 9600.00,
                    'paid_amount' => 0.00,
                    'balance_due' => 9600.00,
                    'notes' => 'Hardware rack accessories and fiber optical transceiver batch.',
                    'items' => [
                        ['sku' => 'CAB-FIB-10', 'description' => '10Gbps OM4 Multimode Fiber Patch Cables (50-pack)', 'quantity' => 5, 'unit_price' => 750.00, 'discount_rate' => 0.0, 'tax_rate' => 8.0],
                        ['sku' => 'SFP-OPT-LR', 'description' => '10GBASE-LR SFP+ Transceiver Modules', 'quantity' => 20, 'unit_price' => 250.00, 'discount_rate' => 0.0, 'tax_rate' => 8.0],
                    ],
                ],
                [
                    'document_number' => 'PO-2026-002',
                    'document_type' => 'po',
                    'status' => 'pending_approval',
                    'is_locked' => false,
                    'account_name' => 'Cyberdyne Systems',
                    'issue_date' => date('Y-m-d', strtotime('-3 days')),
                    'due_date' => date('Y-m-d', strtotime('+42 days')),
                    'payment_terms' => 'Net 45 Days',
                    'currency' => 'USD',
                    'subtotal' => 19600.00,
                    'discount_rate' => 0.0,
                    'discount_amount' => 0.00,
                    'tax_amount' => 1568.00,
                    'shipping_amount' => 250.00,
                    'adjustment_amount' => 0.00,
                    'grand_total' => 21418.00,
                    'paid_amount' => 0.00,
                    'balance_due' => 21418.00,
                    'notes' => 'Awaiting Finance Director sign-off due to threshold policy (> $15k).',
                    'items' => [
                        ['sku' => 'AI-GPU-ACC', 'description' => 'Industrial Machine Learning Accelerator Cards', 'quantity' => 4, 'unit_price' => 4900.00, 'discount_rate' => 0.0, 'tax_rate' => 8.0],
                    ],
                ],
                [
                    'document_number' => 'QT-2026-001',
                    'document_type' => 'quote',
                    'status' => 'issued',
                    'is_locked' => false,
                    'account_name' => 'Wayne Enterprises',
                    'issue_date' => date('Y-m-d', strtotime('-5 days')),
                    'due_date' => date('Y-m-d', strtotime('+25 days')),
                    'payment_terms' => 'Net 30 Days',
                    'currency' => 'USD',
                    'subtotal' => 45000.00,
                    'discount_rate' => 10.0,
                    'discount_amount' => 4500.00,
                    'tax_amount' => 3240.00,
                    'shipping_amount' => 0.00,
                    'adjustment_amount' => 0.00,
                    'grand_total' => 43740.00,
                    'paid_amount' => 0.00,
                    'balance_due' => 43740.00,
                    'notes' => 'Proposal for multi-subsidiary global CRM migration and SLAs.',
                    'items' => [
                        ['sku' => 'CON-ENT-MIG', 'description' => 'Global CRM Migration & Data Hygiene Consulting', 'quantity' => 1, 'unit_price' => 45000.00, 'discount_rate' => 10.0, 'tax_rate' => 8.0],
                    ],
                ],
                [
                    'document_number' => 'INV-2026-003',
                    'document_type' => 'invoice',
                    'status' => 'overdue',
                    'is_locked' => false,
                    'account_name' => 'Stark Industries',
                    'issue_date' => date('Y-m-d', strtotime('-45 days')),
                    'due_date' => date('Y-m-d', strtotime('-15 days')),
                    'payment_terms' => 'Net 30 Days',
                    'currency' => 'USD',
                    'subtotal' => 32100.00,
                    'discount_rate' => 0.0,
                    'discount_amount' => 0.00,
                    'tax_amount' => 2568.00,
                    'shipping_amount' => 0.00,
                    'adjustment_amount' => 0.00,
                    'grand_total' => 34668.00,
                    'paid_amount' => 0.00,
                    'balance_due' => 34668.00,
                    'notes' => 'Overdue invoice; automated reminder notices sent to AP department.',
                    'items' => [
                        ['sku' => 'DEV-API-INT', 'description' => 'Custom ERP Microservices Architecture & Integration', 'quantity' => 1, 'unit_price' => 32100.00, 'discount_rate' => 0.0, 'tax_rate' => 8.0],
                    ],
                ],
            ];

            foreach ($demoDocs as $d) {
                $acc = $accounts->get($d['account_name']) ?? Account::on('tenant')->first();
                $tpl = $templates->get($d['document_type']) ?? DocumentTemplate::on('tenant')->first();

                if (!$tpl) continue;

                $partyDetails = [
                    'client_name' => $acc?->name ?? $d['account_name'],
                    'billing_address' => $acc?->billing_address ?? "Corporate Headquarters\nSuite 500\nNew York, NY",
                    'shipping_address' => $acc?->shipping_address ?? "Distribution Dock B\nNew York, NY",
                ];

                $doc = GeneratedDocument::on('tenant')->create([
                    'template_id' => $tpl->id,
                    'document_number' => $d['document_number'],
                    'document_type' => $d['document_type'],
                    'status' => $d['status'],
                    'is_locked' => $d['is_locked'],
                    'account_id' => $acc?->id,
                    'issue_date' => $d['issue_date'],
                    'due_date' => $d['due_date'],
                    'payment_terms' => $d['payment_terms'],
                    'currency' => $d['currency'],
                    'subtotal' => $d['subtotal'],
                    'discount_type' => 'percentage',
                    'discount_rate' => $d['discount_rate'],
                    'discount_amount' => $d['discount_amount'],
                    'tax_amount' => $d['tax_amount'],
                    'tax_breakdown_json' => [['label' => 'Standard VAT/Sales Tax', 'rate' => 8, 'amount' => $d['tax_amount']]],
                    'shipping_amount' => $d['shipping_amount'],
                    'adjustment_amount' => $d['adjustment_amount'],
                    'grand_total' => $d['grand_total'],
                    'paid_amount' => $d['paid_amount'],
                    'balance_due' => $d['balance_due'],
                    'party_details_json' => $partyDetails,
                    'notes' => $d['notes'],
                    'terms_conditions' => $tpl->terms_conditions,
                    'bank_details' => $tpl->bank_details,
                    'created_by_id' => $userId,
                    'created_by_name' => $userName,
                ]);

                foreach ($d['items'] as $idx => $it) {
                    $itemSubtotal = $it['quantity'] * $it['unit_price'];
                    $itemDiscount = $itemSubtotal * ($it['discount_rate'] / 100);
                    $taxable = $itemSubtotal - $itemDiscount;
                    $itemTax = $taxable * ($it['tax_rate'] / 100);
                    $itemTotal = $taxable + $itemTax;

                    DocumentLineItem::on('tenant')->create([
                        'document_id' => $doc->id,
                        'sort_order' => $idx + 1,
                        'sku' => $it['sku'],
                        'description' => $it['description'],
                        'quantity' => $it['quantity'],
                        'uom' => 'pcs',
                        'unit_price' => $it['unit_price'],
                        'discount_rate' => $it['discount_rate'],
                        'discount_amount' => $itemDiscount,
                        'tax_rate' => $it['tax_rate'],
                        'tax_amount' => $itemTax,
                        'total_amount' => $itemTotal,
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // Silently continue
        }
    }

    /**
     * Resolve current user profile and governance rights.
     */
    protected function resolveGovernanceRights(): array
    {
        $profile = Profile::on('tenant')->first();
        $rights = DocumentGovernanceRight::on('tenant')->where('profile_id', $profile?->id)->first();

        return [
            'allowed_ledger_types' => $rights?->allowed_ledger_types ?? 'all',
            'ledger_record_scope' => $rights?->ledger_record_scope ?? 'all',
            'can_direct_create_in_ledger' => $rights ? (bool)$rights->can_direct_create_in_ledger : true,
            'can_generate_from_opportunity' => $rights ? (bool)$rights->can_generate_from_opportunity : true,
            'can_generate_from_deal' => $rights ? (bool)$rights->can_generate_from_deal : true,
            'profile_name' => $profile?->name ?? 'Administrator',
        ];
    }

    /**
     * Format document entity for JSON and Blade rendering.
     */
    protected function formatDocument(GeneratedDocument $doc): array
    {
        $data = $doc->toArray();
        $data['account_name'] = $doc->account?->name ?? ($doc->party_details_json['client_name'] ?? '—');
        $data['client_name'] = $doc->party_details_json['client_name'] ?? $data['account_name'];
        $data['template_name'] = $doc->template?->name ?? 'Default Template';
        $data['grand_total_formatted'] = '$' . number_format((float)($doc->grand_total ?? 0), 2);
        $data['balance_due_formatted'] = '$' . number_format((float)($doc->balance_due ?? 0), 2);
        $data['paid_amount_formatted'] = '$' . number_format((float)($doc->paid_amount ?? 0), 2);
        $data['issue_date_formatted'] = $doc->issue_date ? date('M d, Y', strtotime($doc->issue_date)) : '—';
        $data['due_date_formatted'] = $doc->due_date ? date('M d, Y', strtotime($doc->due_date)) : '—';
        $data['line_items'] = $doc->lineItems->map(function ($it) {
            $arr = $it->toArray();
            $arr['unit_price_formatted'] = '$' . number_format((float)$it->unit_price, 2);
            $arr['total_amount_formatted'] = '$' . number_format((float)$it->total_amount, 2);
            return $arr;
        })->toArray();

        return $data;
    }

    /**
     * Display the main Billing & Ledger Documents view.
     */
    public function index(Request $request)
    {
        $this->ensureTenantConnection();
        $this->ensureDefaultTemplates();
        $this->ensureDemoDocuments();

        $governance = $this->resolveGovernanceRights();

        $rawDocs = GeneratedDocument::on('tenant')
            ->with(['account', 'contact', 'template', 'lineItems'])
            ->orderBy('id', 'desc')
            ->get();

        $initialDocuments = $rawDocs->map(fn($d) => $this->formatDocument($d))->toArray();

        // Compute KPIs
        $totalInvoiced = 0;
        $totalPO = 0;
        $totalQuote = 0;
        $pendingApprovalCount = 0;
        $totalSettled = 0;

        foreach ($rawDocs as $d) {
            $val = (float)$d->grand_total;
            if ($d->document_type === 'invoice' && $d->status !== 'void') {
                $totalInvoiced += $val;
                $totalSettled += (float)$d->paid_amount;
            } elseif ($d->document_type === 'po' && $d->status !== 'void') {
                $totalPO += $val;
            } elseif ($d->document_type === 'quote' && $d->status !== 'void') {
                $totalQuote += $val;
            }

            if ($d->status === 'pending_approval') {
                $pendingApprovalCount++;
            }
        }

        $kpis = [
            'totalDocs' => $rawDocs->count(),
            'totalInvoiced' => $totalInvoiced,
            'totalInvoicedFormatted' => '$' . number_format($totalInvoiced, 2),
            'totalPO' => $totalPO,
            'totalPOFormatted' => '$' . number_format($totalPO, 2),
            'totalQuote' => $totalQuote,
            'totalQuoteFormatted' => '$' . number_format($totalQuote, 2),
            'pendingApprovalCount' => $pendingApprovalCount,
            'totalSettled' => $totalSettled,
            'totalSettledFormatted' => '$' . number_format($totalSettled, 2),
        ];

        $templates = DocumentTemplate::on('tenant')->where('is_active', true)->get();
        $accounts = Account::on('tenant')->select('id', 'name', 'type', 'billing_address', 'shipping_address')->orderBy('name')->get();
        $contacts = Contact::on('tenant')->select('id', 'account_id', 'first_name', 'last_name', 'email', 'phone')->orderBy('first_name')->get();
        $opportunities = Opportunity::on('tenant')->select('id', 'name', 'amount', 'account_id')->orderBy('name')->get();
        $purchaseDeals = PurchaseDeal::on('tenant')->select('id', 'po_number', 'title', 'amount', 'account_id')->orderBy('po_number')->get();

        return view('documents', [
            'initialDocuments' => $initialDocuments,
            'kpis' => $kpis,
            'governance' => $governance,
            'templates' => $templates,
            'accounts' => $accounts,
            'contacts' => $contacts,
            'opportunities' => $opportunities,
            'purchaseDeals' => $purchaseDeals,
        ]);
    }

    /**
     * Show single document preview.
     */
    public function show($id)
    {
        $this->ensureTenantConnection();
        $doc = GeneratedDocument::on('tenant')
            ->with(['account', 'contact', 'template', 'lineItems'])
            ->findOrFail($id);

        return $this->apiShow($id);
    }

    // ==========================================
    // API METHODS
    // ==========================================

    /**
     * API: List documents with filtering.
     */
    public function apiList(Request $request)
    {
        $this->ensureTenantConnection();
        $query = GeneratedDocument::on('tenant')->with(['account', 'contact', 'template', 'lineItems']);

        if ($request->filled('document_type') && $request->document_type !== 'all') {
            $query->where('document_type', $request->document_type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('account_id')) {
            $query->where('account_id', $request->account_id);
        }
        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($w) use ($q) {
                $w->where('document_number', 'like', "%{$q}%")
                  ->orWhere('notes', 'like', "%{$q}%")
                  ->orWhereHas('account', function ($aw) use ($q) {
                      $aw->where('name', 'like', "%{$q}%");
                  });
            });
        }

        $docs = $query->orderBy('id', 'desc')->get()->map(fn($d) => $this->formatDocument($d));

        return response()->json([
            'success' => true,
            'data' => $docs,
        ]);
    }

    /**
     * API: Retrieve single document with lines and template.
     */
    public function apiShow($id)
    {
        $this->ensureTenantConnection();
        $doc = GeneratedDocument::on('tenant')
            ->with(['account', 'contact', 'template', 'lineItems'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $this->formatDocument($doc),
        ]);
    }

    /**
     * API: Generate new document with line items.
     */
    public function apiStore(Request $request)
    {
        $this->ensureTenantConnection();

        $validated = $request->validate([
            'document_type' => 'required|in:invoice,po,quote',
            'template_id' => 'required|exists:tenant.document_templates,id',
            'account_id' => 'nullable|exists:tenant.accounts,id',
            'contact_id' => 'nullable|exists:tenant.contacts,id',
            'document_number' => 'nullable|string|max:100',
            'issue_date' => 'required|date',
            'due_date' => 'nullable|date',
            'payment_terms' => 'nullable|string|max:60',
            'currency' => 'nullable|string|max:10',
            'discount_rate' => 'nullable|numeric',
            'shipping_amount' => 'nullable|numeric',
            'adjustment_amount' => 'nullable|numeric',
            'notes' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'billing_address' => 'nullable|string',
            'shipping_address' => 'nullable|string',
            'line_items' => 'required|array|min:1',
            'line_items.*.description' => 'required|string',
            'line_items.*.quantity' => 'required|numeric|min:0.01',
            'line_items.*.unit_price' => 'required|numeric|min:0',
            'line_items.*.discount_rate' => 'nullable|numeric',
            'line_items.*.tax_rate' => 'nullable|numeric',
            'line_items.*.sku' => 'nullable|string',
            'line_items.*.hsn_sac_code' => 'nullable|string',
            'line_items.*.uom' => 'nullable|string',
        ]);

        $user = TenantUser::on('tenant')->first();
        $userId = $user?->id;
        $userName = $user?->name ?? 'System Admin';

        // Auto-generate document number if empty
        $docNumber = $validated['document_number'] ?? null;
        if (empty($docNumber)) {
            $prefix = strtoupper($validated['document_type'] === 'invoice' ? 'INV' : ($validated['document_type'] === 'po' ? 'PO' : 'QT'));
            $count = GeneratedDocument::on('tenant')->where('document_type', $validated['document_type'])->count() + 1;
            $docNumber = sprintf('%s-%s-%03d', $prefix, date('Y'), $count);
        }

        $account = !empty($validated['account_id']) ? Account::on('tenant')->find($validated['account_id']) : null;
        $partyDetails = [
            'client_name' => $account?->name ?? ($request->client_name ?: 'Valued Client'),
            'billing_address' => $validated['billing_address'] ?? $account?->billing_address,
            'shipping_address' => $validated['shipping_address'] ?? $account?->shipping_address,
        ];

        // Calculate totals from line items
        $subtotal = 0;
        $totalTax = 0;
        $itemsToInsert = [];

        foreach ($validated['line_items'] as $idx => $item) {
            $qty = (float)$item['quantity'];
            $rate = (float)$item['unit_price'];
            $discRate = (float)($item['discount_rate'] ?? 0);
            $taxRate = (float)($item['tax_rate'] ?? 0);

            $lineSubtotal = $qty * $rate;
            $lineDiscount = $lineSubtotal * ($discRate / 100);
            $taxable = $lineSubtotal - $lineDiscount;
            $lineTax = $taxable * ($taxRate / 100);
            $lineTotal = $taxable + $lineTax;

            $subtotal += $lineSubtotal;
            $totalTax += $lineTax;

            $itemsToInsert[] = [
                'sort_order' => $idx + 1,
                'sku' => $item['sku'] ?? null,
                'description' => $item['description'],
                'hsn_sac_code' => $item['hsn_sac_code'] ?? null,
                'quantity' => $qty,
                'uom' => $item['uom'] ?? 'pcs',
                'unit_price' => $rate,
                'discount_rate' => $discRate,
                'discount_amount' => $lineDiscount,
                'tax_rate' => $taxRate,
                'tax_amount' => $lineTax,
                'total_amount' => $lineTotal,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $docDiscountRate = (float)($validated['discount_rate'] ?? 0);
        $docDiscountAmount = $subtotal * ($docDiscountRate / 100);
        $shipping = (float)($validated['shipping_amount'] ?? 0);
        $adjustment = (float)($validated['adjustment_amount'] ?? 0);
        $grandTotal = ($subtotal - $docDiscountAmount) + $totalTax + $shipping + $adjustment;

        $doc = GeneratedDocument::on('tenant')->create([
            'template_id' => $validated['template_id'],
            'document_number' => $docNumber,
            'document_type' => $validated['document_type'],
            'status' => 'draft',
            'is_locked' => false,
            'account_id' => $validated['account_id'] ?? null,
            'contact_id' => $validated['contact_id'] ?? null,
            'issue_date' => $validated['issue_date'],
            'due_date' => $validated['due_date'] ?? null,
            'payment_terms' => $validated['payment_terms'] ?? 'Net 30 Days',
            'currency' => $validated['currency'] ?? 'USD',
            'subtotal' => $subtotal,
            'discount_type' => 'percentage',
            'discount_rate' => $docDiscountRate,
            'discount_amount' => $docDiscountAmount,
            'tax_amount' => $totalTax,
            'tax_breakdown_json' => [['label' => 'Sales Tax', 'rate' => 8, 'amount' => $totalTax]],
            'shipping_amount' => $shipping,
            'adjustment_amount' => $adjustment,
            'grand_total' => $grandTotal,
            'paid_amount' => 0,
            'balance_due' => $grandTotal,
            'party_details_json' => $partyDetails,
            'notes' => $validated['notes'] ?? null,
            'terms_conditions' => $validated['terms_conditions'] ?? null,
            'created_by_id' => $userId,
            'created_by_name' => $userName,
        ]);

        foreach ($itemsToInsert as $it) {
            $it['document_id'] = $doc->id;
            DocumentLineItem::on('tenant')->create($it);
        }

        return response()->json([
            'success' => true,
            'message' => 'Document generated successfully!',
            'data' => $this->formatDocument($doc->fresh(['account', 'contact', 'template', 'lineItems'])),
        ]);
    }

    /**
     * API: Update document status.
     */
    public function apiUpdateStatus(Request $request, $id)
    {
        $this->ensureTenantConnection();
        $validated = $request->validate([
            'status' => 'required|in:draft,pending_approval,approved,issued,sent,partially_paid,paid,overdue,void',
        ]);

        $doc = GeneratedDocument::on('tenant')->findOrFail($id);
        $doc->status = $validated['status'];

        if ($validated['status'] === 'paid') {
            $doc->paid_amount = $doc->grand_total;
            $doc->balance_due = 0;
            $doc->is_locked = true;
        }

        $doc->save();

        return response()->json([
            'success' => true,
            'message' => "Document status updated to {$validated['status']}",
            'data' => $this->formatDocument($doc->fresh(['account', 'contact', 'template', 'lineItems'])),
        ]);
    }

    /**
     * API: Record a payment against an invoice.
     */
    public function apiRecordPayment(Request $request, $id)
    {
        $this->ensureTenantConnection();
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        $doc = GeneratedDocument::on('tenant')->findOrFail($id);
        $newPaid = (float)$doc->paid_amount + (float)$validated['amount'];
        $newBalance = max(0, (float)$doc->grand_total - $newPaid);

        $doc->paid_amount = $newPaid;
        $doc->balance_due = $newBalance;
        if ($newBalance <= 0) {
            $doc->status = 'paid';
            $doc->is_locked = true;
        } else {
            $doc->status = 'partially_paid';
        }
        $doc->save();

        return response()->json([
            'success' => true,
            'message' => 'Payment recorded successfully!',
            'data' => $this->formatDocument($doc->fresh(['account', 'contact', 'template', 'lineItems'])),
        ]);
    }

    /**
     * API: Delete document if not locked.
     */
    public function apiDestroy($id)
    {
        $this->ensureTenantConnection();
        $doc = GeneratedDocument::on('tenant')->findOrFail($id);

        if ($doc->is_locked) {
            return response()->json([
                'success' => false,
                'message' => 'This document is locked by governance rules and cannot be deleted.',
            ], 403);
        }

        $doc->lineItems()->delete();
        $doc->delete();

        return response()->json([
            'success' => true,
            'message' => 'Document deleted from ledger.',
        ]);
    }

    /**
     * API: Generate next sequential document number.
     */
    public function apiNextNumber($type)
    {
        $this->ensureTenantConnection();
        $prefix = strtoupper($type === 'invoice' ? 'INV' : ($type === 'po' ? 'PO' : 'QT'));
        $count = GeneratedDocument::on('tenant')->where('document_type', $type)->count() + 1;
        $number = sprintf('%s-%s-%03d', $prefix, date('Y'), $count);

        return response()->json([
            'success' => true,
            'next_number' => $number,
        ]);
    }

    /**
     * API: Return current user governance rights.
     */
    public function apiMyGovernanceRights()
    {
        $this->ensureTenantConnection();
        return response()->json([
            'success' => true,
            'data' => $this->resolveGovernanceRights(),
        ]);
    }
}
