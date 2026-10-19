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
        if (Schema::connection('mysql')->hasTable('tenants') && !Schema::connection('mysql')->hasColumn('tenants', 'logo_url')) {
            Schema::connection('mysql')->table('tenants', function (Blueprint $table) {
                $table->longText('logo_url')->nullable()->after('name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::connection('mysql')->hasTable('tenants') && Schema::connection('mysql')->hasColumn('tenants', 'logo_url')) {
            Schema::connection('mysql')->table('tenants', function (Blueprint $table) {
                $table->dropColumn('logo_url');
            });
        }
    }
};
