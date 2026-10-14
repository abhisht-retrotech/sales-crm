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
        // Page Layouts
        Schema::connection('tenant')->create('page_layouts', function (Blueprint $table) {
            $table->id();
            $table->string('module', 60);
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->boolean('is_default')->default(false);
            $table->longText('layout_json');
            $table->string('created_by', 100)->default('System Admin');
            $table->string('updated_by', 100)->default('System Admin');
            $table->timestamps();

            $table->index(['module', 'is_default']);
        });

        // Profile Layout Assignments
        Schema::connection('tenant')->create('profile_layout_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained('profiles')->cascadeOnDelete();
            $table->string('module', 60);
            $table->foreignId('layout_id')->constrained('page_layouts')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['profile_id', 'module']);
        });

        // Dashboard Studio Layouts
        Schema::connection('tenant')->create('dashboard_layouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->unique()->constrained('profiles')->cascadeOnDelete();
            $table->longText('widgets');
            $table->longText('sections');
            $table->string('updated_by', 100)->default('System Admin');
            $table->timestamps();
        });

        // Tenant Settings (Key-Value Configuration)
        Schema::connection('tenant')->create('tenant_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->longText('value')->nullable();
            $table->timestamps();
        });

        // Multi-Currency
        Schema::connection('tenant')->create('tenant_currencies', function (Blueprint $table) {
            $table->id();
            $table->string('currency_code', 10)->unique();
            $table->string('currency_symbol', 10);
            $table->string('currency_name', 60);
            $table->decimal('exchange_rate', 15, 6)->default(1.0);
            $table->boolean('is_base')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // View Configurations (Kanban vs Table)
        Schema::connection('tenant')->create('view_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('module', 60);
            $table->string('view_type', 30); // table, kanban
            $table->json('visible_fields');
            $table->timestamps();

            $table->unique(['module', 'view_type']);
        });

        // Standard System Field Overrides
        Schema::connection('tenant')->create('standard_field_overrides', function (Blueprint $table) {
            $table->id();
            $table->string('module', 60);
            $table->string('field_name', 100);
            $table->string('field_label', 150)->nullable();
            $table->boolean('is_required')->nullable();
            $table->text('options')->nullable();
            $table->text('help_text')->nullable();
            $table->timestamp('updated_at')->useCurrent();

            $table->unique(['module', 'field_name']);
        });

        // Company Profile & Corporate Branding Identity
        Schema::connection('tenant')->create('company_profile', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('legal_name', 150)->nullable();
            $table->string('tax_id', 60)->nullable();
            $table->string('registration_number', 60)->nullable();
            $table->string('industry', 100)->nullable();
            $table->string('website', 255)->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('billing_email', 150)->nullable();
            $table->string('support_email', 150)->nullable();
            $table->text('address_street')->nullable();
            $table->string('address_city', 100)->nullable();
            $table->string('address_state', 100)->nullable();
            $table->string('address_postal_code', 30)->nullable();
            $table->string('address_country', 10)->default('US');
            
            // Regional & Localization
            $table->string('currency_code', 10)->default('USD');
            $table->string('currency_symbol', 10)->default('$');
            $table->string('currency_position', 20)->default('prefix');
            $table->string('time_zone', 60)->default('America/New_York');
            $table->string('date_format', 30)->default('YYYY-MM-DD');
            $table->string('time_format', 10)->default('12h');
            $table->string('number_format', 30)->default('comma_dot');
            $table->unsignedSmallInteger('fiscal_year_start_month')->default(1);
            $table->string('working_days', 100)->default('Mon,Tue,Wed,Thu,Fri');
            $table->string('working_hours_start', 10)->default('09:00');
            $table->string('working_hours_end', 10)->default('18:00');
            $table->boolean('allow_self_password_change')->default(true);

            // SMTP Gateway & Outbound Email
            $table->string('email_provider', 40)->default('custom');
            $table->string('smtp_host', 150)->nullable();
            $table->unsignedInteger('smtp_port')->default(587);
            $table->string('smtp_user', 150)->nullable();
            $table->text('smtp_pass')->nullable();
            $table->boolean('smtp_secure')->default(false);
            $table->string('smtp_status', 30)->default('unconfigured');
            $table->timestamp('smtp_last_tested_at')->nullable();
            $table->string('email_from_name', 150)->nullable();
            $table->string('email_from_address', 150)->nullable();
            $table->string('email_reply_to', 150)->nullable();
            $table->string('sender_identity_policy', 40)->default('org_default');
            $table->boolean('enforce_legal_footer')->default(true);
            $table->longText('legal_footer_html')->nullable();

            // Branding & Design System
            $table->text('logo_url')->nullable();
            $table->string('branding_theme', 30)->default('apple');
            $table->string('accent_color', 20)->default('#4F46E5');
            $table->string('page_bg_color', 20)->default('#fafafa');
            $table->string('font_color', 20)->default('#09090b');
            $table->string('header_bg_color', 20)->default('#ffffff');
            $table->string('sidebar_bg_color', 20)->default('#ffffff');

            // Security Policies
            $table->string('plan_tier', 30)->default('enterprise');
            $table->unsignedInteger('max_seats')->default(15);
            $table->unsignedInteger('max_storage_mb')->default(10240);
            $table->unsignedInteger('max_records')->default(100000);
            $table->unsignedInteger('session_timeout_minutes')->default(60);
            $table->unsignedInteger('password_min_length')->default(8);
            $table->boolean('password_require_special')->default(true);
            $table->boolean('password_require_numbers')->default(true);
            $table->boolean('password_require_uppercase')->default(true);
            $table->unsignedInteger('password_expiry_days')->default(0);
            $table->unsignedInteger('max_failed_login_attempts')->default(5);
            $table->unsignedInteger('lockout_duration_minutes')->default(30);
            $table->string('two_factor_policy', 30)->default('optional');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('company_profile');
        Schema::connection('tenant')->dropIfExists('standard_field_overrides');
        Schema::connection('tenant')->dropIfExists('view_configurations');
        Schema::connection('tenant')->dropIfExists('tenant_currencies');
        Schema::connection('tenant')->dropIfExists('tenant_settings');
        Schema::connection('tenant')->dropIfExists('dashboard_layouts');
        Schema::connection('tenant')->dropIfExists('profile_layout_assignments');
        Schema::connection('tenant')->dropIfExists('page_layouts');
    }
};
