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
        // Profile Permissions (Record-level CRUD & Scope)
        Schema::connection('tenant')->create('profile_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained('profiles')->cascadeOnDelete();
            $table->string('module', 60);
            $table->boolean('can_create')->default(true);
            $table->boolean('can_read')->default(true);
            $table->boolean('can_update')->default(true);
            $table->boolean('can_delete')->default(false);
            $table->boolean('can_transfer')->default(false);
            $table->string('record_scope', 30)->default('All'); // 'All', 'Own', 'Team'
            $table->timestamps();

            $table->unique(['profile_id', 'module']);
        });

        // Field Permissions (Field-level Read/Write, Read-Only, Hidden)
        Schema::connection('tenant')->create('field_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained('profiles')->cascadeOnDelete();
            $table->string('module', 60);
            $table->string('field_name', 100);
            $table->string('permission', 30)->default('Read/Write'); // 'Read/Write', 'Read-Only', 'Hidden'
            $table->timestamps();

            $table->unique(['profile_id', 'module', 'field_name']);
        });

        // Audit Logs (IP and activity tracking)
        Schema::connection('tenant')->create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 60);
            $table->string('entity_type', 60);
            $table->string('entity_id', 60)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('details')->nullable();
            $table->timestamps();

            $table->index(['entity_type', 'entity_id']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('audit_logs');
        Schema::connection('tenant')->dropIfExists('field_permissions');
        Schema::connection('tenant')->dropIfExists('profile_permissions');
    }
};
