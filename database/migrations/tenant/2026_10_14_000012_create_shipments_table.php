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
        Schema::connection('tenant')->create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete();
            $table->foreignId('opportunity_id')->nullable()->constrained('opportunities')->nullOnDelete();
            $table->foreignId('purchase_deal_id')->nullable()->constrained('purchase_deals')->nullOnDelete();
            $table->string('type', 50)->default('Outbound'); // Inbound, Outbound, Drop-ship
            $table->string('carrier', 80); // FedEx, DHL, UPS, Maersk
            $table->string('tracking_number', 100);
            $table->string('status', 50)->default('Booked'); // Booked, Dispatched, In Transit, Customs Clearance, Delivered, Exception
            $table->text('shipping_origin')->nullable();
            $table->text('destination_address')->nullable();
            $table->date('dispatch_date')->nullable();
            $table->date('estimated_delivery_date')->nullable();
            $table->date('actual_delivery_date')->nullable();
            $table->text('notes')->nullable();
            $table->json('custom_data')->nullable();

            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index('tracking_number');
            $table->index('status');
            $table->index('account_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('shipments');
    }
};
