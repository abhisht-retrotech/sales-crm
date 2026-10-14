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
        Schema::connection('tenant')->create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('subject', 200);
            $table->string('priority', 30)->default('Normal'); // Low, Normal, High, Urgent
            $table->string('status', 40)->default('Open'); // Open, In Progress, Completed, Waiting, Deferred
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('assigned_to_name', 150)->nullable();
            $table->date('due_date')->nullable();
            $table->text('comments')->nullable();

            // Related CRM Entity (Polymorphic / Dynamic Reference)
            $table->string('related_entity_type', 60)->nullable(); // accounts, leads, opportunities, purchase_deals, shipments, contacts, payments, wires
            $table->unsignedBigInteger('related_entity_id')->nullable();
            $table->string('related_entity_name', 150)->nullable();

            $table->string('reminder_set', 100)->default('No reminder is set');
            $table->boolean('is_recurring')->default(false);
            $table->string('recurrence_pattern', 30)->nullable(); // Daily, Weekly, Monthly

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('created_by_name', 150)->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('updated_by_name', 150)->nullable();
            $table->json('custom_fields')->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index('due_date');
            $table->index(['related_entity_type', 'related_entity_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('tasks');
    }
};
