<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::connection('tenant')->hasTable('leads')) {
            Schema::connection('tenant')->table('leads', function (Blueprint $table) {
                if (!Schema::connection('tenant')->hasColumn('leads', 'approval_status')) {
                    $table->string('approval_status', 50)->default('Pending')->after('rating');
                }
                if (!Schema::connection('tenant')->hasColumn('leads', 'is_approved')) {
                    $table->boolean('is_approved')->default(false)->after('approval_status');
                }
                if (!Schema::connection('tenant')->hasColumn('leads', 'is_rejected')) {
                    $table->boolean('is_rejected')->default(false)->after('is_approved');
                }
                if (!Schema::connection('tenant')->hasColumn('leads', 'approved_by')) {
                    $table->foreignId('approved_by')->nullable()->after('is_rejected')->constrained('users')->nullOnDelete();
                }
                if (!Schema::connection('tenant')->hasColumn('leads', 'rejected_by')) {
                    $table->foreignId('rejected_by')->nullable()->after('approved_by')->constrained('users')->nullOnDelete();
                }
                if (!Schema::connection('tenant')->hasColumn('leads', 'approved_at')) {
                    $table->timestamp('approved_at')->nullable()->after('rejected_by');
                }
                if (!Schema::connection('tenant')->hasColumn('leads', 'rejected_at')) {
                    $table->timestamp('rejected_at')->nullable()->after('approved_at');
                }
                if (!Schema::connection('tenant')->hasColumn('leads', 'approval_notes')) {
                    $table->text('approval_notes')->nullable()->after('rejected_at');
                }
            });

            // Migrate any existing approval data stored in custom_data JSON into native columns
            try {
                $leads = DB::connection('tenant')->table('leads')->get();
                foreach ($leads as $l) {
                    $cData = json_decode($l->custom_data ?? '[]', true) ?: [];
                    $updates = [];

                    if (isset($cData['is_approved'])) {
                        $updates['is_approved'] = !empty($cData['is_approved']) && $cData['is_approved'] !== '0' && $cData['is_approved'] !== false;
                    }
                    if (isset($cData['is_rejected'])) {
                        $updates['is_rejected'] = !empty($cData['is_rejected']) && $cData['is_rejected'] !== '0' && $cData['is_rejected'] !== false;
                    }
                    if (isset($cData['approval_status'])) {
                        $updates['approval_status'] = (string)$cData['approval_status'];
                    }
                    if (isset($cData['approved_by']) && is_numeric($cData['approved_by'])) {
                        $updates['approved_by'] = (int)$cData['approved_by'];
                        $updates['approved_at'] = now();
                    }
                    if (isset($cData['rejected_by']) && is_numeric($cData['rejected_by'])) {
                        $updates['rejected_by'] = (int)$cData['rejected_by'];
                        $updates['rejected_at'] = now();
                    }
                    if (isset($cData['approval_notes'])) {
                        $updates['approval_notes'] = (string)$cData['approval_notes'];
                    }

                    // Remove dynamic custom field keys from custom_data
                    unset(
                        $cData['is_approved'],
                        $cData['is_rejected'],
                        $cData['approval_status'],
                        $cData['approved_by'],
                        $cData['rejected_by'],
                        $cData['approval_notes'],
                        $cData['cf_is_approved']
                    );
                    $updates['custom_data'] = json_encode($cData);

                    DB::connection('tenant')->table('leads')->where('id', $l->id)->update($updates);
                }
            } catch (\Throwable $e) {
                // Ignore data migration errors if table is fresh
            }

            // Remove approval-related dynamic fields from custom_fields table
            try {
                DB::connection('tenant')->table('custom_fields')
                    ->where('module', 'leads')
                    ->whereIn('field_name', [
                        'is_approved',
                        'is_rejected',
                        'approval_status',
                        'approved_by',
                        'rejected_by',
                        'approval_notes',
                        'cf_is_approved',
                    ])
                    ->delete();
            } catch (\Throwable $e) {}

            // Remove any field permissions for these fields
            try {
                if (Schema::connection('tenant')->hasTable('field_permissions')) {
                    DB::connection('tenant')->table('field_permissions')
                        ->where('module', 'leads')
                        ->whereIn('field_name', [
                            'is_approved',
                            'is_rejected',
                            'approval_status',
                            'approved_by',
                            'rejected_by',
                            'approval_notes',
                            'cf_is_approved',
                        ])
                        ->delete();
                }
            } catch (\Throwable $e) {}
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::connection('tenant')->hasTable('leads')) {
            Schema::connection('tenant')->table('leads', function (Blueprint $table) {
                if (Schema::connection('tenant')->hasColumn('leads', 'approved_by')) {
                    $table->dropForeign(['approved_by']);
                }
                if (Schema::connection('tenant')->hasColumn('leads', 'rejected_by')) {
                    $table->dropForeign(['rejected_by']);
                }
                $table->dropColumn([
                    'approval_status',
                    'is_approved',
                    'is_rejected',
                    'approved_by',
                    'rejected_by',
                    'approved_at',
                    'rejected_at',
                    'approval_notes',
                ]);
            });
        }
    }
};
