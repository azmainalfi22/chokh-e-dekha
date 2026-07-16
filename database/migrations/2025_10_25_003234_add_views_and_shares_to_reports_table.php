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
        Schema::table('reports', function (Blueprint $table) {
            if (!Schema::hasColumn('reports', 'views_count')) {
                $table->unsignedInteger('views_count')->default(0)->after('comments_count');
            }
            
            if (!Schema::hasColumn('reports', 'shares_count')) {
                $table->unsignedInteger('shares_count')->default(0)->after('views_count');
            }
            
            // Add index for trending queries
            $table->index(['views_count', 'created_at']);
            $table->index(['likes_count', 'comments_count', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropIndex(['views_count', 'created_at']);
            $table->dropIndex(['likes_count', 'comments_count', 'created_at']);
            
            if (Schema::hasColumn('reports', 'shares_count')) {
                $table->dropColumn('shares_count');
            }
            
            if (Schema::hasColumn('reports', 'views_count')) {
                $table->dropColumn('views_count');
            }
        });
    }
};
