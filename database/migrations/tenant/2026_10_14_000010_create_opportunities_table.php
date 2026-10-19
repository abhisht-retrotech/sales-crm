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
        Schema::connection('tenant')->create('opportunities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete();
            // Direct Link between Sales Opportunity and Procurement Purchase Deal
            $table->foreignId('purchase_deal_id')->constrained('purchase_deals')->restrictOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->string('name', 200);
            $table->decimal('amount', 15, 2);
            $table->string('currency', 10)->default('USD');
            $table->string('stage', 60); // Prospecting, Qualification, Proposal, Negotiation, Closed Won, Closed Lost
            $table->unsignedInteger('probability')->default(50); // 0 to 100 %
            $table->date('expected_close_date')->nullable();
            $table->text('notes')->nullable();
            $table->json('custom_data')->nullable();

            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index('stage');
            $table->index(['account_id', 'stage']);
            $table->index('purchase_deal_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('opportunities');
    }
};
