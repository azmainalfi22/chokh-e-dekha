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
        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->uuid('id')->primary();

                // What class triggered the notification
                $table->string('type');

                // Which model this notification belongs to (e.g. User, Admin, etc.)
                $table->morphs('notifiable');

                // Payload
                $table->json('data'); // ✅ json instead of text (easier to query/filter)

                // Status
                $table->boolean('is_read')->default(false); // ✅ explicit flag
                $table->timestamp('read_at')->nullable();

                $table->timestamps();

                // Index for faster queries
                $table->index(['notifiable_id', 'notifiable_type']);
                $table->index('is_read');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
