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
        // 1. CRM Migration Batches (HubSpot, Salesforce, Zoho, Custom CSV intake)
        Schema::connection('tenant')->create('migration_batches', function (Blueprint $table) {
            $table->id();
            $table->string('source_crm', 40); // 'hubspot' | 'salesforce' | 'zoho' | 'custom'
            $table->string('batch_name', 150);
            $table->string('status', 30)->default('completed'); // 'completed' | 'reverted' | 'failed'
            $table->unsignedInteger('total_records')->default(0);
            $table->unsignedInteger('successful_records')->default(0);
            $table->unsignedInteger('failed_records')->default(0);
            $table->longText('entity_counts_json'); // JSON { "accounts": 150, "contacts": 400 }
            $table->longText('stage_mappings_json')->nullable(); // JSON { "HubSpot Won": "Closed Won" }
            $table->longText('success_details_json')->nullable();
            $table->foreignId('performed_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('performed_by_name', 150)->default('System Admin');
            $table->timestamp('reverted_at')->nullable();
            $table->string('reverted_by_name', 150)->nullable();
            $table->timestamps();

            $table->index('status');
        });

        // 2. Legacy ID Translation Mappings
        Schema::connection('tenant')->create('migration_id_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('migration_batch_id')->constrained('migration_batches')->cascadeOnDelete();
            $table->string('source_crm', 40);
            $table->string('entity_type', 60);
            $table->string('legacy_id', 150);
            $table->string('dealsync_id', 150);
            $table->timestamps();

            $table->index(['migration_batch_id', 'legacy_id']);
            $table->index(['source_crm', 'entity_type', 'legacy_id']);
        });

        // 3. Row-level Migration Error Logs
        Schema::connection('tenant')->create('migration_error_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('migration_batch_id')->constrained('migration_batches')->cascadeOnDelete();
            $table->string('entity_type', 60);
            $table->unsignedInteger('row_index');
            $table->string('legacy_id', 150)->nullable();
            $table->longText('raw_data_json');
            $table->text('failure_reason');
            $table->timestamps();

            $table->index('migration_batch_id');
        });

        // 4. Migration Records Tracker (for 1-click atomic rollback)
        Schema::connection('tenant')->create('migration_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('migration_batch_id')->constrained('migration_batches')->cascadeOnDelete();
            $table->string('entity_type', 60);
            $table->string('record_id', 60);
            $table->timestamps();

            $table->index('migration_batch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('migration_records');
        Schema::connection('tenant')->dropIfExists('migration_error_logs');
        Schema::connection('tenant')->dropIfExists('migration_id_mappings');
        Schema::connection('tenant')->dropIfExists('migration_batches');
    }
};
