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
        Schema::connection('tenant')->create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->foreignId('role_id')->nullable()->constrained('roles')->nullOnDelete();
            $table->foreignId('profile_id')->nullable()->constrained('profiles')->nullOnDelete();
            $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('phone', 30)->nullable();
            $table->string('mobile_phone', 30)->nullable();
            $table->string('avatar', 255)->nullable();
            $table->text('avatar_url')->nullable();
            $table->string('nickname', 100)->nullable();
            $table->string('job_title', 100)->nullable();
            $table->string('department', 100)->nullable();
            $table->text('bio')->nullable();
            $table->text('email_signature')->nullable();
            $table->string('time_zone_override', 80)->nullable();
            $table->json('notification_preferences_json')->nullable();
            $table->string('linkedin_url', 255)->nullable();
            $table->string('twitter_url', 255)->nullable();
            $table->string('github_url', 255)->nullable();
            $table->string('website_url', 255)->nullable();
            $table->string('theme_mode', 20)->default('light');
            $table->string('accent_color', 20)->default('#4F46E5');
            $table->string('status', 30)->default('Active'); // Active, Inactive, Suspended
            $table->boolean('surprise_me_enabled')->nullable();
            $table->json('custom_fields')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->index('status');
        });

        // Add self-referencing foreign key on roles table now that users/roles are present
        Schema::connection('tenant')->table('roles', function (Blueprint $table) {
            $table->foreign('reports_to_role_id')->references('id')->on('roles')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('tenant')->table('roles', function (Blueprint $table) {
            $table->dropForeign(['reports_to_role_id']);
        });
        Schema::connection('tenant')->dropIfExists('users');
    }
};
