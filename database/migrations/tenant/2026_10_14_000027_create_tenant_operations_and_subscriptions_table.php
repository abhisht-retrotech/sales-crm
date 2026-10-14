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
        // 1. Team Member Invitations & Cryptographic Tokens
        Schema::connection('tenant')->create('user_invitations', function (Blueprint $table) {
            $table->id();
            $table->string('email', 150);
            $table->string('full_name', 150);
            $table->foreignId('role_id')->nullable()->constrained('roles')->nullOnDelete();
            $table->foreignId('profile_id')->nullable()->constrained('profiles')->nullOnDelete();
            $table->foreignId('invited_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('token', 128)->unique();
            $table->string('status', 30)->default('pending'); // 'pending' | 'accepted' | 'expired' | 'revoked'
            $table->timestamp('expires_at');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();

            $table->index(['email', 'status']);
        });

        // 2. Tenant Data Backups & Disaster Recovery Metadata
        Schema::connection('tenant')->create('tenant_backups', function (Blueprint $table) {
            $table->id();
            $table->string('file_name', 255);
            $table->unsignedBigInteger('file_size_bytes')->default(0);
            $table->unsignedInteger('record_count')->default(0);
            $table->longText('modules_json');
            $table->string('status', 30)->default('completed'); // 'completed' | 'in_progress' | 'failed'
            $table->string('created_by', 100)->default('System Admin');
            $table->timestamps();

            $table->index('status');
        });

        // 3. Scoped REST API Keys
        Schema::connection('tenant')->create('tenant_api_keys', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('key_prefix', 20);
            $table->string('key_hash', 128)->unique();
            $table->longText('scopes'); // JSON array of scopes
            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('created_by', 100)->default('System Admin');
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('is_active');
        });

        // 4. Outbound Webhooks Engine
        Schema::connection('tenant')->create('tenant_webhooks', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->text('target_url');
            $table->string('secret_key', 128);
            $table->longText('event_types'); // JSON array of event types
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('failure_count')->default(0);
            $table->timestamps();

            $table->index('is_active');
        });

        // 5. Webhook Delivery Logs
        Schema::connection('tenant')->create('webhook_delivery_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('webhook_id')->constrained('tenant_webhooks')->cascadeOnDelete();
            $table->string('event_type', 100);
            $table->longText('payload_json');
            $table->unsignedSmallInteger('status_code')->nullable();
            $table->longText('response_body')->nullable();
            $table->unsignedInteger('latency_ms')->default(0);
            $table->string('status', 30); // 'success' | 'failed'
            $table->timestamps();

            $table->index(['webhook_id', 'created_at']);
        });

        // 6. SaaS Subscriptions & Per-User Monthly Billing
        Schema::connection('tenant')->create('tenant_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('plan_tier', 40)->default('professional'); // 'starter' | 'professional' | 'enterprise'
            $table->unsignedInteger('seat_count')->default(5);
            $table->decimal('unit_price_monthly', 10, 2)->default(39.00);
            $table->string('billing_cycle', 20)->default('monthly'); // 'monthly' | 'annual'
            $table->string('status', 30)->default('active'); // 'active' | 'trialing' | 'past_due' | 'canceled'
            $table->date('current_period_start');
            $table->date('current_period_end');
            $table->string('payment_method_brand', 30)->default('Visa');
            $table->string('payment_method_last4', 10)->default('4242');
            $table->boolean('auto_renew')->default(true);
            $table->timestamps();
        });

        // 7. Subscription Invoices Ledger
        Schema::connection('tenant')->create('tenant_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained('tenant_subscriptions')->cascadeOnDelete();
            $table->string('invoice_number', 80)->unique();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 10)->default('USD');
            $table->unsignedInteger('seats_purchased')->default(0);
            $table->unsignedInteger('seat_count_after')->default(0);
            $table->text('description');
            $table->string('status', 30)->default('paid'); // 'paid' | 'open' | 'void'
            $table->string('payment_method_last4', 10)->default('4242');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['subscription_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('tenant_invoices');
        Schema::connection('tenant')->dropIfExists('tenant_subscriptions');
        Schema::connection('tenant')->dropIfExists('webhook_delivery_logs');
        Schema::connection('tenant')->dropIfExists('tenant_webhooks');
        Schema::connection('tenant')->dropIfExists('tenant_api_keys');
        Schema::connection('tenant')->dropIfExists('tenant_backups');
        Schema::connection('tenant')->dropIfExists('user_invitations');
    }
};
