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
        Schema::connection('tenant')->create('global_picklists', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('api_name', 100)->unique();
            $table->text('description')->nullable();
            $table->json('values_json');
            $table->timestamps();

            $table->index('api_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('global_picklists');
    }
};
