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
        if (Schema::connection('tenant')->hasTable('users') && !Schema::connection('tenant')->hasColumn('users', 'manager_id')) {
            Schema::connection('tenant')->table('users', function (Blueprint $table) {
                $table->foreignId('manager_id')->nullable()->after('profile_id')->constrained('users')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::connection('tenant')->hasTable('users') && Schema::connection('tenant')->hasColumn('users', 'manager_id')) {
            Schema::connection('tenant')->table('users', function (Blueprint $table) {
                $table->dropForeign(['manager_id']);
                $table->dropColumn('manager_id');
            });
        }
    }
};
