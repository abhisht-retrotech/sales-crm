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
        // Logical Files Entity
        Schema::connection('tenant')->create('files', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->string('mime_type', 100);
            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('created_by_name', 150)->default('System Admin');
            $table->unsignedBigInteger('latest_version_id')->nullable();
            $table->timestamps();
        });

        // File Binary Versions
        Schema::connection('tenant')->create('file_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_id')->constrained('files')->cascadeOnDelete();
            $table->unsignedInteger('version_number')->default(1);
            $table->string('storage_key', 255);
            $table->string('original_filename', 255);
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size_bytes');
            $table->string('checksum', 100)->nullable();
            $table->foreignId('uploaded_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('uploaded_by_name', 150)->default('System Admin');
            $table->timestamps();

            $table->index(['file_id', 'version_number']);
        });

        // File Associations with Records (Polymorphic M-N)
        Schema::connection('tenant')->create('file_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_id')->constrained('files')->cascadeOnDelete();
            $table->string('entity_type', 60);
            $table->string('entity_id', 60);
            $table->string('visibility', 30)->default('all');
            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['file_id', 'entity_type', 'entity_id']);
        });

        // Private Folder Shares
        Schema::connection('tenant')->create('private_folder_shares', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type', 60);
            $table->string('entity_id', 60);
            $table->string('share_type', 30); // owner, profile, user
            $table->string('share_target_id', 60);
            $table->string('share_target_name', 150)->nullable();
            $table->foreignId('granted_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['entity_type', 'entity_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('private_folder_shares');
        Schema::connection('tenant')->dropIfExists('file_links');
        Schema::connection('tenant')->dropIfExists('file_versions');
        Schema::connection('tenant')->dropIfExists('files');
    }
};
