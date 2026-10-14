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
        Schema::connection('tenant')->create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('type', 50)->default('Customer'); // Customer, Vendor, Partner, Distributor, Prospect
            $table->string('industry', 100)->nullable();
            $table->string('website', 255)->nullable();
            $table->string('phone', 30)->nullable();
            $table->text('billing_address')->nullable();
            $table->text('shipping_address')->nullable();
            $table->string('payment_terms', 60)->nullable(); // Net 30, Net 60, COD, Due on Receipt
            $table->decimal('credit_limit', 15, 2)->default(0);
            $table->decimal('annual_revenue', 15, 2)->default(0);
            $table->string('currency', 10)->default('USD');
            $table->string('assigned_to', 150)->nullable();
            $table->text('notes')->nullable();
            $table->json('custom_data')->nullable(); // Extended custom fields data

            // User assignments and relations
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index('name');
            $table->index('type');
            $table->index('industry');
            $table->index('owner_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('accounts');
    }
};
