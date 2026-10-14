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
        Schema::connection('tenant')->create('custom_fields', function (Blueprint $table) {
            $table->id();
            $table->string('module', 60);
            $table->string('field_name', 100);
            $table->string('field_label', 150);
            $table->string('data_type', 50); // text, number, currency, date, datetime, picklist, multiselect, textarea, checkbox, formula, autonumber, rollup, rating, percentage, radio, masked, color, richtext
            $table->text('options')->nullable();
            $table->boolean('is_required')->default(false);
            $table->text('default_value')->nullable();
            $table->text('help_text')->nullable();
            $table->string('lookup_module', 60)->nullable();
            $table->string('lookup_display_field', 100)->nullable();
            $table->string('formula_mode', 30)->nullable();
            $table->string('formula_source_field', 100)->nullable();
            $table->string('formula_target_field', 100)->nullable();
            $table->text('formula_expression')->nullable();
            $table->string('formula_return_type', 30)->nullable();
            $table->foreignId('global_picklist_id')->nullable()->constrained('global_picklists')->nullOnDelete();
            $table->boolean('is_multiselect')->default(false);
            $table->string('autonumber_prefix', 30)->nullable();
            $table->string('autonumber_suffix', 30)->nullable();
            $table->unsignedInteger('autonumber_padding')->default(4);
            $table->unsignedInteger('autonumber_start_num')->default(1);
            $table->unsignedInteger('autonumber_next_val')->default(1);
            $table->string('rollup_child_module', 60)->nullable();
            $table->string('rollup_relationship_field', 100)->nullable();
            $table->string('rollup_type', 30)->nullable();
            $table->string('rollup_target_field', 100)->nullable();
            $table->json('rollup_filter_json')->nullable();
            $table->unsignedInteger('rating_max')->default(5);
            $table->string('rating_icon', 30)->default('star');
            $table->unsignedInteger('percentage_precision')->default(2);
            $table->decimal('percentage_min', 8, 2)->nullable();
            $table->decimal('percentage_max', 8, 2)->nullable();
            $table->string('radio_layout', 30)->default('horizontal');
            $table->string('masked_pattern', 30)->default('last4');
            $table->text('color_presets')->nullable();
            $table->unsignedInteger('rich_text_height')->default(160);
            $table->timestamps();

            $table->index(['module', 'field_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('custom_fields');
    }
};
