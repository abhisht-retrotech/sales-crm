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
        Schema::connection('tenant')->create('wires_received', function (Blueprint $table) {
            $table->id();
            $table->string('utr_reference', 100)->unique();
            $table->string('remitter_name', 150);
            $table->string('sending_bank', 120);
            $table->string('receiving_bank_account', 80);
            $table->decimal('amount', 15, 2);
            $table->string('currency', 10)->default('USD');
            $table->date('date_received');
            $table->string('status', 40)->default('Pending'); // Pending, Reconciled, Disputed, Unmatched

            // Reconciliation Links
            $table->foreignId('reconciled_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('reconciled_payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->foreignId('reconciled_opportunity_id')->nullable()->constrained('opportunities')->nullOnDelete();
            $table->foreignId('reconciled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reconciled_at')->nullable();
            $table->text('reconciliation_notes')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index('utr_reference');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('wires_received');
    }
};
