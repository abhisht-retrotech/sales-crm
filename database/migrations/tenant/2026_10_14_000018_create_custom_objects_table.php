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
        // Custom Object Definitions
        Schema::connection('tenant')->create('custom_objects', function (Blueprint $table) {
            $table->id();
            $table->string('api_name', 100)->unique();
            $table->string('label', 150);
            $table->string('plural_label', 150);
            $table->text('description')->nullable();
            $table->string('primary_field', 100)->default('name');
            $table->string('icon', 50)->default('Box');
            $table->string('created_by', 100)->default('System Admin');
            $table->timestamps();
        });

        // Custom Object Records
        Schema::connection('tenant')->create('custom_object_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('custom_object_id')->constrained('custom_objects')->cascadeOnDelete();
            $table->string('name', 200);
            $table->json('attributes_json');
            $table->string('created_by', 100)->default('System Admin');
            $table->string('updated_by', 100)->default('System Admin');
            $table->timestamps();

            $table->index('custom_object_id');
        });

        // Relationship Definitions for Related Lists
        Schema::connection('tenant')->create('relationship_definitions', function (Blueprint $table) {
            $table->id();
            $table->string('parent_object', 100);
            $table->string('child_object', 100);
            $table->string('relation_name', 100);
            $table->string('label', 150);
            $table->string('foreign_key', 100);
            $table->timestamps();

            $table->unique(['parent_object', 'relation_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('relationship_definitions');
        Schema::connection('tenant')->dropIfExists('custom_object_records');
        Schema::connection('tenant')->dropIfExists('custom_objects');
    }
};
