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
        // User Notifications (In-app and email logs)
        Schema::connection('tenant')->create('user_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title', 150);
            $table->text('message');
            $table->string('channel', 30)->default('in_app'); // in_app, email, both
            $table->boolean('read')->default(false);
            $table->string('link_module', 60)->nullable();
            $table->string('link_id', 60)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'read']);
        });

        // Pop-up Notification Rules
        Schema::connection('tenant')->create('notification_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('operation', 40); // CREATE, UPDATE, DELETE, TRANSFER, CANCEL, REJECT, APPROVE, CUSTOM
            $table->string('module', 60);
            $table->string('type', 30)->default('success'); // success, error, warning, info
            $table->string('title', 150);
            $table->text('message_template');
            $table->json('actions')->nullable();
            $table->unsignedInteger('duration_ms')->default(5000);
            $table->string('position', 30)->default('top-right');
            $table->boolean('is_active')->default(true);
            $table->string('trigger_type', 30)->default('immediate');
            $table->string('date_field', 60)->nullable();
            $table->string('offset_direction', 20)->nullable();
            $table->integer('offset_value')->default(0);
            $table->string('offset_unit', 20)->default('hours');
            $table->string('criteria_field', 60)->nullable();
            $table->string('criteria_operator', 30)->nullable();
            $table->text('criteria_value')->nullable();
            $table->string('recipient_type', 40)->default('record_owner');
            $table->json('recipient_ids')->nullable();
            $table->json('recipient_profile_ids')->nullable();
            $table->timestamps();

            $table->index(['operation', 'module', 'is_active']);
        });

        // Notification Rule Executions tracking
        Schema::connection('tenant')->create('notification_rule_executions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rule_id')->constrained('notification_rules')->cascadeOnDelete();
            $table->string('module', 60);
            $table->string('record_id', 60);
            $table->timestamp('executed_at');

            $table->index(['rule_id', 'record_id']);
        });

        // Email Templates
        Schema::connection('tenant')->create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('category', 60)->default('general');
            $table->string('entity_type', 60);
            $table->string('subject', 200);
            $table->longText('body_html');
            $table->text('body_text')->nullable();
            $table->json('attached_file_ids')->nullable();
            $table->string('dynamic_file_sources', 60)->default('none');
            $table->json('dynamic_file_keywords')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('created_by', 100)->nullable();
            $table->timestamps();

            $table->index(['entity_type', 'is_active']);
        });

        // Email Automation Rules
        Schema::connection('tenant')->create('email_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('module', 60);
            $table->foreignId('email_template_id')->constrained('email_templates')->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->string('trigger_type', 30)->default('scheduled');
            $table->string('date_field', 60)->nullable();
            $table->string('offset_direction', 20)->default('before');
            $table->integer('offset_value')->default(0);
            $table->string('offset_unit', 20)->default('hours');
            $table->string('criteria_field', 60)->nullable();
            $table->string('criteria_operator', 30)->default('equals');
            $table->text('criteria_value')->nullable();
            $table->string('recipient_type', 40)->default('record_owner');
            $table->json('recipient_ids')->nullable();
            $table->json('recipient_profile_ids')->nullable();
            $table->text('cc_emails')->nullable();
            $table->timestamps();

            $table->index(['module', 'is_active']);
        });

        // Email Rule Executions
        Schema::connection('tenant')->create('email_rule_executions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rule_id')->constrained('email_rules')->cascadeOnDelete();
            $table->string('module', 60);
            $table->string('record_id', 60);
            $table->timestamp('executed_at');

            $table->index(['rule_id', 'record_id']);
        });

        // Custom Buttons & Actions Framework
        Schema::connection('tenant')->create('custom_buttons', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type', 60);
            $table->string('name', 100);
            $table->string('label', 150);
            $table->string('icon', 50)->default('Mail');
            $table->string('style_variant', 30)->default('secondary'); // primary, secondary, success, indigo, purple, amber, danger
            $table->string('action_type', 50); // send_email, url_redirect, workflow_trigger, quick_action, add_weight_dims, open_weight_dims
            $table->json('action_config');
            $table->string('placement', 40)->default('record_header'); // record_header, list_view, both
            $table->json('visibility_conditions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0);
            $table->timestamps();

            $table->index(['entity_type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('custom_buttons');
        Schema::connection('tenant')->dropIfExists('email_rule_executions');
        Schema::connection('tenant')->dropIfExists('email_rules');
        Schema::connection('tenant')->dropIfExists('email_templates');
        Schema::connection('tenant')->dropIfExists('notification_rule_executions');
        Schema::connection('tenant')->dropIfExists('notification_rules');
        Schema::connection('tenant')->dropIfExists('user_notifications');
    }
};
