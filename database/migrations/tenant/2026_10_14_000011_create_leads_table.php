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
        Schema::connection('tenant')->create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 80);
            $table->string('last_name', 80);
            $table->string('email', 150)->index();
            $table->string('phone', 30)->nullable();
            $table->string('company', 150);
            $table->string('title', 100)->nullable();
            $table->string('source', 80)->default('Website'); // Website, Referral, Cold Call, Trade Show, LinkedIn
            $table->unsignedInteger('score')->default(50);
            $table->string('status', 60)->default('New'); // New, Contacted, Qualified, Unqualified, Converted
            $table->string('lead_value', 30)->nullable();
            $table->string('rating', 30)->default('Warm'); // Hot, Warm, Cold
            $table->text('notes')->nullable();
            $table->boolean('email_opt_out')->default(false);

            // Lead Conversion Relationships
            $table->boolean('is_converted')->default(false);
            $table->foreignId('converted_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('converted_contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->foreignId('converted_opportunity_id')->nullable()->constrained('opportunities')->nullOnDelete();

            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->json('custom_data')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index(['status', 'assigned_to']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('leads');
    }
};
