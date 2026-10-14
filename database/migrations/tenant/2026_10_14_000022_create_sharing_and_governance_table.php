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
        // Sharing Rules
        Schema::connection('tenant')->create('sharing_rules', function (Blueprint $table) {
            $table->id();
            $table->string('module', 60);
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->string('rule_type', 30)->default('criteria_based'); // criteria_based, ownership_based
            $table->json('criteria_json')->nullable();
            $table->foreignId('ownership_role_id')->nullable()->constrained('roles')->nullOnDelete();
            $table->foreignId('ownership_profile_id')->nullable()->constrained('profiles')->nullOnDelete();
            $table->string('share_with_type', 30); // profile, role, user
            $table->string('share_with_id', 60);
            $table->string('access_level', 30)->default('read'); // read, read_write
            $table->boolean('is_active')->default(true);
            $table->string('created_by', 100)->default('System Admin');
            $table->timestamps();

            $table->index(['module', 'is_active']);
        });

        // Record Shares
        Schema::connection('tenant')->create('record_shares', function (Blueprint $table) {
            $table->id();
            $table->string('module', 60);
            $table->string('record_id', 60);
            $table->string('shared_with_type', 30); // profile, role, user
            $table->string('shared_with_id', 60);
            $table->string('access_level', 30)->default('read');
            $table->string('source', 30)->default('rule'); // rule, manual
            $table->foreignId('rule_id')->nullable()->constrained('sharing_rules')->nullOnDelete();
            $table->dateTime('expires_at')->nullable();
            $table->string('created_by', 100)->default('System Admin');
            $table->timestamps();

            $table->index(['module', 'record_id']);
        });

        // Field History Tracking
        Schema::connection('tenant')->create('field_history_configs', function (Blueprint $table) {
            $table->id();
            $table->string('module', 60)->unique();
            $table->json('tracked_fields');
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });

        Schema::connection('tenant')->create('field_history', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type', 60);
            $table->string('entity_id', 60);
            $table->string('field_name', 100);
            $table->string('field_label', 150);
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->foreignId('changed_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('changed_by_name', 150)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['entity_type', 'entity_id']);
        });

        // Enterprise Duplicate Rule Framework & Merge Studio
        Schema::connection('tenant')->create('duplicate_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->string('source_module', 60);
            $table->string('target_module', 60);
            $table->string('match_logic', 10)->default('OR'); // 'AND' | 'OR'
            $table->string('action', 30)->default('Alert'); // 'Block' | 'Alert' | 'Report'
            $table->boolean('is_active')->default(true);
            $table->string('auto_merge_strategy', 40)->default('manual_only'); // 'manual_only' | 'keep_oldest_cascade' | 'keep_newest'
            $table->string('created_by', 100)->default('System Admin');
            $table->timestamps();

            $table->index(['source_module', 'is_active']);
        });

        Schema::connection('tenant')->create('duplicate_rule_conditions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rule_id')->constrained('duplicate_rules')->cascadeOnDelete();
            $table->string('source_field', 100);
            $table->string('target_field', 100);
            $table->string('match_type', 30)->default('Exact'); // 'Exact' | 'Fuzzy'
            $table->string('fuzzy_algorithm', 40)->nullable(); // 'Levenshtein' | 'Soundex'
            $table->unsignedInteger('fuzzy_threshold')->default(80);
            $table->string('normalization', 40)->default('none'); // 'none' | 'phone_digits' | 'url_domain' | 'email_clean' | 'email_domain' | 'strip_specials' | 'lowercase_trim'
            $table->timestamps();

            $table->index('rule_id');
        });

        Schema::connection('tenant')->create('duplicate_record_sets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rule_id')->constrained('duplicate_rules')->cascadeOnDelete();
            $table->string('status', 30)->default('Pending'); // 'Pending' | 'Merged' | 'Ignored'
            $table->string('source_module', 60);
            $table->string('target_module', 60);
            $table->string('primary_record_id', 60);
            $table->string('secondary_record_id', 60);
            $table->unsignedInteger('match_score')->default(100);
            $table->longText('match_details_json')->nullable();
            $table->timestamp('detected_at')->useCurrent();
            $table->timestamp('merged_at')->nullable();
            $table->string('merged_by', 100)->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index(['source_module', 'primary_record_id']);
        });

        Schema::connection('tenant')->create('duplicate_exception_domains', function (Blueprint $table) {
            $table->id();
            $table->string('domain', 150)->unique();
            $table->text('description')->nullable();
            $table->string('created_by', 100)->default('System Admin');
            $table->timestamps();

            $table->index('domain');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('duplicate_exception_domains');
        Schema::connection('tenant')->dropIfExists('duplicate_record_sets');
        Schema::connection('tenant')->dropIfExists('duplicate_rule_conditions');
        Schema::connection('tenant')->dropIfExists('duplicate_rules');
        Schema::connection('tenant')->dropIfExists('field_history');
        Schema::connection('tenant')->dropIfExists('field_history_configs');
        Schema::connection('tenant')->dropIfExists('record_shares');
        Schema::connection('tenant')->dropIfExists('sharing_rules');
    }
};
