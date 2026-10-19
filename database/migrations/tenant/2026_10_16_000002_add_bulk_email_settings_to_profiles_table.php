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
        if (Schema::connection('tenant')->hasTable('profiles')) {
            Schema::connection('tenant')->table('profiles', function (Blueprint $table) {
                if (!Schema::connection('tenant')->hasColumn('profiles', 'bulk_email_daily_limit')) {
                    $table->integer('bulk_email_daily_limit')->default(500);
                }
                if (!Schema::connection('tenant')->hasColumn('profiles', 'can_send_bulk_email')) {
                    $table->boolean('can_send_bulk_email')->default(false);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::connection('tenant')->hasTable('profiles')) {
            Schema::connection('tenant')->table('profiles', function (Blueprint $table) {
                if (Schema::connection('tenant')->hasColumn('profiles', 'can_send_bulk_email')) {
                    $table->dropColumn('can_send_bulk_email');
                }
                if (Schema::connection('tenant')->hasColumn('profiles', 'bulk_email_daily_limit')) {
                    $table->dropColumn('bulk_email_daily_limit');
                }
            });
        }
    }
};
