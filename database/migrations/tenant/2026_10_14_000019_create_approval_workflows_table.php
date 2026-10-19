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
        // Approval Processes (Header)
        Schema::connection('tenant')->create('approval_processes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->string('module', 60);
            $table->string('trigger_type', 30)->default('manual'); // manual, on_create, on_update
            $table->string('criteria_field', 100)->nullable();
            $table->string('criteria_operator', 30)->nullable();
            $table->text('criteria_value')->nullable();
            $table->string('notification_channel', 30)->default('both'); // notification, email, both
            $table->json('approval_actions')->nullable();
            $table->json('rejection_actions')->nullable();
            $table->boolean('enable_auto_expiry')->default(false);
            $table->unsignedInteger('auto_expiry_hours')->default(72);
            $table->json('expiry_actions')->nullable();
            $table->boolean('lock_record_until_approved')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['module', 'is_active']);
        });

        // Multi-level Approval Steps
        Schema::connection('tenant')->create('approval_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('process_id')->constrained('approval_processes')->cascadeOnDelete();
            $table->unsignedInteger('step_number'); // 1, 2, 3...
            $table->string('step_name', 100);
            $table->string('approver_type', 30)->default('role'); // role, user
            $table->string('approver_id', 60)->nullable();
            $table->string('approver_name', 150)->nullable();
            $table->timestamps();

            $table->index(['process_id', 'step_number']);
        });

        // Approval Requests (Per record runtime instances)
        Schema::connection('tenant')->create('approval_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('process_id')->constrained('approval_processes')->cascadeOnDelete();
            $table->string('module', 60);
            $table->string('record_id', 60);
            $table->unsignedInteger('current_step')->default(1);
            $table->unsignedInteger('total_steps')->default(1);
            $table->string('status', 30)->default('pending'); // pending, approved, rejected, cancelled, recalled, expired
            $table->dateTime('expires_at')->nullable();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('submitter_name', 150);
            $table->text('submission_notes')->nullable();
            $table->timestamps();

            $table->index(['module', 'record_id', 'status']);
        });

        // Approval Actions (Audit log of each decision)
        Schema::connection('tenant')->create('approval_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('approval_requests')->cascadeOnDelete();
            $table->unsignedInteger('step_number');
            $table->string('action', 30); // submitted, approved, rejected
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_name', 150);
            $table->text('comments')->nullable();
            $table->timestamps();

            $table->index('request_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('approval_actions');
        Schema::connection('tenant')->dropIfExists('approval_requests');
        Schema::connection('tenant')->dropIfExists('approval_steps');
        Schema::connection('tenant')->dropIfExists('approval_processes');
    }
};
