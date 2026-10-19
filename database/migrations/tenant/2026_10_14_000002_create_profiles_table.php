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
        Schema::connection('tenant')->create('profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->text('ip_whitelist')->nullable();
            $table->string('login_time_start', 10)->nullable();
            $table->string('login_time_end', 10)->nullable();
            $table->string('login_days', 100)->nullable();
            $table->boolean('surprise_me_enabled')->default(true);
            $table->boolean('is_admin')->default(false);
            $table->timestamps();

            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('profiles');
    }
};
