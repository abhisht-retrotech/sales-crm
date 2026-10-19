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
        // 1. Global Enterprise Search Settings
        Schema::connection('tenant')->create('global_search_settings', function (Blueprint $table) {
            $table->id();
            $table->longText('enabled_modules_json');
            $table->longText('searchable_fields_json');
            $table->boolean('duplicate_precheck_enabled')->default(true);
            $table->string('mask_format', 30)->default('masked'); // 'masked' | 'anonymous'
            $table->boolean('allow_request_access')->default(true);
            $table->boolean('enable_create_precheck')->default(true);
            $table->unsignedInteger('dropdown_limit')->default(6);
            $table->unsignedInteger('full_view_limit')->default(50);
            $table->timestamps();
        });

        // 2. Data Operations Audit Log (Mass Operations & CSV Studio)
        Schema::connection('tenant')->create('data_operation_logs', function (Blueprint $table) {
            $table->id();
            $table->string('operation_type', 50); // 'mass_update', 'mass_transfer', 'csv_import', 'csv_upsert', 'csv_export', 'mass_delete', 'rollback'
            $table->string('module', 60);
            $table->unsignedInteger('records_affected')->default(0);
            $table->text('summary');
            $table->longText('criteria_json')->nullable();
            $table->foreignId('performed_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('performed_by_name', 150)->nullable();
            $table->boolean('is_reverted')->default(false);
            $table->timestamp('reverted_at')->nullable();
            $table->string('reverted_by_name', 150)->nullable();
            $table->longText('success_details_json')->nullable();
            $table->longText('error_details_json')->nullable();
            $table->timestamps();

            $table->index(['module', 'created_at']);
        });

        // 3. Data Operation Snapshots (1-Click Atomic Rollback Engine)
        Schema::connection('tenant')->create('data_operation_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operation_id')->constrained('data_operation_logs')->cascadeOnDelete();
            $table->string('operation_type', 50);
            $table->string('module', 60);
            $table->string('record_id', 60);
            $table->longText('previous_state_json');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['operation_id', 'module']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('data_operation_snapshots');
        Schema::connection('tenant')->dropIfExists('data_operation_logs');
        Schema::connection('tenant')->dropIfExists('global_search_settings');
    }
};
