<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Document & Billing Templates (Invoice, PO, Quote Designer)
        Schema::connection('tenant')->create('document_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('document_type', 30); // 'invoice' | 'po' | 'quote'
            $table->text('description')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);

            // Page Layout & Geometry
            $table->string('paper_size', 20)->default('A4'); // 'A4' | 'Letter'
            $table->string('orientation', 20)->default('portrait'); // 'portrait' | 'landscape'
            $table->string('margin_size', 20)->default('standard'); // 'compact' | 'standard' | 'spacious'

            // Design Theme & Typography
            $table->string('font_family', 100)->default('Inter, sans-serif');
            $table->string('primary_color', 30)->default('#0f172a');
            $table->string('accent_color', 30)->default('#2563eb');
            $table->string('table_theme', 30)->default('zebra'); // 'zebra' | 'grid' | 'borderless' | 'minimal'
            $table->string('layout_style', 40)->default('modern_teal'); // 'modern_teal' | 'classic_navy' | 'minimal_slate' | 'bold_stripe' | 'compact_grid'

            // Branding & Header
            $table->text('logo_url')->nullable();
            $table->string('logo_position', 20)->default('left'); // 'left' | 'center' | 'right'
            $table->unsignedInteger('logo_height')->default(48);
            $table->string('company_name', 150)->nullable();
            $table->text('company_address')->nullable();
            $table->string('company_tax_id', 60)->nullable();
            $table->string('company_phone', 40)->nullable();
            $table->string('company_email', 150)->nullable();
            $table->string('company_website', 255)->nullable();
            $table->string('document_title', 150);

            // Dynamic Configurations
            $table->longText('watermark_config')->nullable(); // JSON DocumentWatermarkConfig
            $table->longText('columns_config')->nullable(); // JSON DocumentColumnConfig[]
            $table->longText('dynamic_fields_config')->nullable(); // JSON DocumentDynamicFieldsConfig
            $table->string('payment_terms_default', 60)->default('Net 30');
            $table->text('bank_details')->nullable();
            $table->text('qr_code_url')->nullable();
            $table->text('notes_default')->nullable();
            $table->text('terms_conditions')->nullable();

            // Signatory & Authorizations
            $table->boolean('show_signatory')->default(true);
            $table->string('signatory_title', 100)->default('Authorized Signatory');
            $table->string('signatory_name', 150)->nullable();
            $table->text('signature_image_url')->nullable();
            $table->boolean('show_shipping_address')->default(true);

            // Governance & Archival
            $table->longText('agreement_pages')->nullable(); // JSON DocumentAgreementPage[]
            $table->string('save_to_files_destination', 40)->default('files_public'); // 'none' | 'files_public' | 'files_private'
            $table->boolean('allow_direct_email')->default(true);
            $table->boolean('requires_approval')->default(false);
            $table->foreignId('approval_process_id')->nullable()->constrained('approval_processes')->nullOnDelete();

            $table->string('created_by', 100)->default('System Admin');
            $table->timestamps();

            $table->index(['document_type', 'is_active']);
            $table->index(['document_type', 'is_default']);
        });

        // 2. Generated Documents (Transaction Ledger)
        Schema::connection('tenant')->create('generated_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('document_templates')->restrictOnDelete();
            $table->string('document_number', 100)->unique();
            $table->string('document_type', 30); // 'invoice' | 'po' | 'quote'
            $table->string('status', 40)->default('draft'); // 'draft', 'pending_approval', 'approved', 'issued', 'sent', 'partially_paid', 'paid', 'overdue', 'void'
            $table->boolean('is_locked')->default(false);

            // Associated Entities
            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->string('related_entity_type', 60)->nullable(); // opportunities, purchase_deals, payments, shipments
            $table->unsignedBigInteger('related_entity_id')->nullable();

            // File Archival Reference
            $table->foreignId('file_id')->nullable()->constrained('files')->nullOnDelete();
            $table->string('file_visibility', 30)->default('all');

            // Dates & Payment Terms
            $table->date('issue_date');
            $table->date('due_date')->nullable();
            $table->date('expected_delivery_date')->nullable();
            $table->string('payment_terms', 60)->nullable();

            // Financial Calculations
            $table->string('currency', 10)->default('USD');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->string('discount_type', 20)->default('percentage'); // 'percentage' | 'flat'
            $table->decimal('discount_rate', 8, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->json('tax_breakdown_json')->nullable();
            $table->decimal('shipping_amount', 15, 2)->default(0);
            $table->decimal('adjustment_amount', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('balance_due', 15, 2)->default(0);
            $table->string('amount_in_words', 255)->nullable();

            // Snapshot Party & Payment Information
            $table->json('party_details_json')->nullable();
            $table->text('notes')->nullable();
            $table->text('terms_conditions')->nullable();
            $table->text('bank_details')->nullable();

            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('created_by_name', 150)->default('System Admin');
            $table->timestamps();

            $table->index(['document_type', 'status']);
            $table->index(['related_entity_type', 'related_entity_id']);
        });

        // 3. Document Line Items
        Schema::connection('tenant')->create('document_line_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('generated_documents')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('item_type', 30)->default('product'); // 'product' | 'service' | 'fee'
            $table->string('sku', 100)->nullable();
            $table->text('description');
            $table->string('hsn_sac_code', 50)->nullable();
            $table->decimal('quantity', 12, 4)->default(1);
            $table->string('uom', 30)->default('pcs');
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('discount_rate', 8, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_rate', 8, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->json('custom_fields_json')->nullable();
            $table->timestamps();

            $table->index(['document_id', 'sort_order']);
        });

        // 4. Document Snapshots (Frozen historic render copies)
        Schema::connection('tenant')->create('document_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('generated_documents')->cascadeOnDelete();
            $table->longText('template_snapshot_json');
            $table->longText('document_data_json');
            $table->longText('line_items_json');
            $table->longText('rendered_html')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique('document_id');
        });

        // 5. Document Governance Rights per Profile
        Schema::connection('tenant')->create('document_governance_rights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained('profiles')->cascadeOnDelete();
            $table->boolean('can_generate_from_opportunity')->default(true);
            $table->string('opportunity_required_stage', 60)->default('Closed Won');
            $table->text('opportunity_condition_json')->nullable();
            $table->boolean('can_generate_from_deal')->default(true);
            $table->string('deal_required_stage', 60)->default('Closed Won');
            $table->text('deal_condition_json')->nullable();
            $table->boolean('can_direct_create_in_ledger')->default(false);
            $table->string('allowed_ledger_types', 40)->default('all'); // 'all' | 'invoices_quotes' | 'purchase_orders' | 'none'
            $table->string('ledger_record_scope', 30)->default('all'); // 'all' | 'team' | 'own' | 'none'
            $table->timestamps();

            $table->unique('profile_id');
        });

        // 6. ProcessStudio - Unified Business Process Designer & Orchestration
        Schema::connection('tenant')->create('process_flows', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->string('category', 60)->default('general'); // 'general' | 'sales' | 'procurement' | 'finance' | 'logistics'
            $table->string('entity_type', 60);
            $table->string('trigger_type', 40); // 'button_click' | 'record_event' | 'schedule' | 'webhook'
            $table->json('trigger_config');
            $table->longText('nodes'); // JSON ProcessFlowNode[]
            $table->longText('edges'); // JSON ProcessFlowEdge[]
            $table->string('status', 30)->default('active'); // 'active' | 'draft' | 'disabled'
            $table->unsignedInteger('version')->default(1);
            $table->string('created_by', 100)->default('System Admin');
            $table->string('updated_by', 100)->default('System Admin');
            $table->timestamps();

            $table->index(['entity_type', 'status']);
            $table->index(['trigger_type', 'status']);
        });

        Schema::connection('tenant')->create('process_flow_executions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flow_id')->constrained('process_flows')->cascadeOnDelete();
            $table->string('entity_type', 60);
            $table->string('record_id', 60)->nullable();
            $table->string('trigger_type', 40);
            $table->string('status', 30); // 'running', 'completed', 'failed'
            $table->string('current_node_id', 100)->nullable();
            $table->longText('state_data')->nullable(); // JSON
            $table->longText('logs')->nullable(); // JSON
            $table->string('executed_by', 100)->default('System Admin');
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();

            $table->index(['flow_id', 'status']);
        });

        // 7. Dedicated Cargo Weight & Dimensions (Wt. & Dims) Multi-Table Specification Engine
        Schema::connection('tenant')->create('weight_dims_specs', function (Blueprint $table) {
            $table->id();
            $table->string('parent_type', 60); // 'purchase_deals', 'opportunities', 'shipments'
            $table->string('parent_id', 60);
            $table->string('title', 150);
            $table->string('weight_unit', 10)->default('KG'); // 'KG' | 'LBS'
            $table->string('dim_unit', 10)->default('CM'); // 'CM' | 'INCH'
            $table->longText('rows_json'); // JSON WeightDimsRow[]
            $table->unsignedInteger('total_packages')->default(0);
            $table->decimal('total_weight', 15, 2)->default(0);
            $table->decimal('total_cbm', 15, 4)->default(0);
            $table->text('notes')->nullable();
            $table->text('pickup_address')->nullable();
            $table->string('pickup_country', 60)->nullable();
            $table->string('created_by', 100)->default('System Admin');
            $table->string('updated_by', 100)->default('System Admin');
            $table->timestamps();

            $table->index(['parent_type', 'parent_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('weight_dims_specs');
        Schema::connection('tenant')->dropIfExists('process_flow_executions');
        Schema::connection('tenant')->dropIfExists('process_flows');
        Schema::connection('tenant')->dropIfExists('document_governance_rights');
        Schema::connection('tenant')->dropIfExists('document_snapshots');
        Schema::connection('tenant')->dropIfExists('document_line_items');
        Schema::connection('tenant')->dropIfExists('generated_documents');
        Schema::connection('tenant')->dropIfExists('document_templates');
    }
};
