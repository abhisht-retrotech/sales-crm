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
        Schema::connection('tenant')->create('purchase_deals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->string('po_number', 80)->unique();
            $table->string('title', 200);
            $table->decimal('amount', 15, 2);
            $table->string('currency', 10)->default('USD');
            $table->string('stage', 60); // Draft, PO Issued, Supplier Confirmed, In Production, Shipped, Delivered, Closed
            $table->date('expected_delivery_date')->nullable();
            $table->string('payment_terms', 60)->nullable();
            $table->text('notes')->nullable();
            $table->json('custom_data')->nullable();

            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index('stage');
            $table->index('account_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('purchase_deals');
    }
};
