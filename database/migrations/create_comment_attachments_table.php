<?php
// =====================================================
// FILE: database/migrations/xxxx_xx_xx_create_comment_attachments_table.php
// =====================================================

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
        Schema::create('comment_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comment_id')->constrained('comments')->onDelete('cascade');
            $table->string('filename');
            $table->string('original_name');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size'); // File size in bytes
            $table->string('path'); // Storage path
            $table->string('disk', 20)->default('public'); // Storage disk
            $table->json('metadata')->nullable(); // Additional file metadata
            $table->timestamps();
            
            // Indexes
            $table->index('comment_id', 'idx_comment_attachments_comment');
            $table->index('mime_type', 'idx_comment_attachments_mime');
            $table->index('created_at', 'idx_comment_attachments_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comment_attachments');
    }
};