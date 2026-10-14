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
        Schema::connection('tenant')->create('contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->string('account_name', 150)->nullable();
            $table->string('first_name', 80);
            $table->string('last_name', 80);
            $table->string('email', 150)->index();
            $table->string('phone', 30)->nullable();
            $table->string('title', 100)->nullable();
            $table->string('department', 100)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->string('contact_role', 60)->default('Decision Maker'); // Decision Maker, Influencer, Buyer, Technical
            $table->boolean('email_opt_out')->default(false);
            $table->text('notes')->nullable();
            $table->json('custom_data')->nullable();

            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['account_id', 'is_primary']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('contacts');
    }
};
