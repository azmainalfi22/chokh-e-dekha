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
        // Check and add missing columns to report_media table
        Schema::table('report_media', function (Blueprint $table) {
            if (!Schema::hasColumn('report_media', 'report_id')) {
                $table->foreignId('report_id')->after('id')->constrained()->cascadeOnDelete();
                $table->index('report_id');
            }
            
            if (!Schema::hasColumn('report_media', 'file_path')) {
                $table->string('file_path')->after('report_id');
            }
            
            if (!Schema::hasColumn('report_media', 'original_name')) {
                $table->string('original_name')->nullable()->after('file_path');
            }
            
            if (!Schema::hasColumn('report_media', 'mime_type')) {
                $table->string('mime_type')->nullable()->after('original_name');
            }
            
            if (!Schema::hasColumn('report_media', 'file_size')) {
                $table->unsignedInteger('file_size')->nullable()->after('mime_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Don't drop columns in down() to prevent data loss
        // This is a fix migration, so we keep the structure
    }
};
