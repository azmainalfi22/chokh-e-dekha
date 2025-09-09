<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->unsignedInteger('likes_count')->default(0)->after('status');
            $table->unsignedInteger('comments_count')->default(0)->after('likes_count');
            
            $table->index('likes_count');
            $table->index('comments_count');
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropIndex(['likes_count']);
            $table->dropIndex(['comments_count']);
            $table->dropColumn(['likes_count', 'comments_count']);
        });
    }
};