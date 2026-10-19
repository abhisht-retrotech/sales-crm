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
        Schema::connection('mysql')->create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('legal_name', 200)->nullable();
            $table->string('domain')->unique();
            $table->string('db_host')->default('127.0.0.1');
            $table->unsignedInteger('db_port')->default(3306);
            $table->string('db_name')->unique();
            $table->string('db_username')->default('root');
            $table->text('db_password')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('plan_tier', 50)->default('enterprise');
            $table->unsignedInteger('max_seats')->default(15);
            $table->string('branding_theme', 50)->default('apple');
            $table->string('accent_color', 30)->default('#4F46E5');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql')->dropIfExists('tenants');
    }
};
