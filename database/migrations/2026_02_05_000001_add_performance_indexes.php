<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Add performance indexes to key tables
     */
    public function up(): void
    {
        // Use raw SQL for conditional index creation (MySQL)
        $driver = Schema::getConnection()->getDriverName();
        
        if ($driver === 'mysql') {
            // Reports table indexes
            DB::statement('CREATE INDEX IF NOT EXISTS reports_status_created_at_index ON reports (status, created_at)');
            DB::statement('CREATE INDEX IF NOT EXISTS reports_user_id_created_at_index ON reports (user_id, created_at)');
            DB::statement('CREATE INDEX IF NOT EXISTS reports_city_status_index ON reports (city_corporation, status)');
            DB::statement('CREATE INDEX IF NOT EXISTS reports_category_status_index ON reports (category, status)');
            DB::statement('CREATE INDEX IF NOT EXISTS reports_likes_count_index ON reports (likes_count)');
            DB::statement('CREATE INDEX IF NOT EXISTS reports_comments_count_index ON reports (comments_count)');
            
            // Report likes unique constraint
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS report_likes_user_report_unique ON report_likes (user_id, report_id)');
            
            // Report comments indexes
            DB::statement('CREATE INDEX IF NOT EXISTS report_comments_user_id_index ON report_comments (user_id)');
            DB::statement('CREATE INDEX IF NOT EXISTS report_comments_parent_id_index ON report_comments (parent_id)');
            
            // Users admin index
            DB::statement('CREATE INDEX IF NOT EXISTS users_is_admin_index ON users (is_admin)');
        } else {
            // For other databases, use Schema builder without conditionals
            Schema::table('reports', function (Blueprint $table) {
                $table->index(['status', 'created_at'], 'reports_status_created_at_index');
                $table->index(['user_id', 'created_at'], 'reports_user_id_created_at_index');
                $table->index(['city_corporation', 'status'], 'reports_city_status_index');
                $table->index(['category', 'status'], 'reports_category_status_index');
                $table->index('likes_count', 'reports_likes_count_index');
                $table->index('comments_count', 'reports_comments_count_index');
            });

            Schema::table('report_likes', function (Blueprint $table) {
                $table->unique(['user_id', 'report_id'], 'report_likes_user_report_unique');
            });

            Schema::table('report_comments', function (Blueprint $table) {
                $table->index('user_id', 'report_comments_user_id_index');
                $table->index('parent_id', 'report_comments_parent_id_index');
            });

            Schema::table('users', function (Blueprint $table) {
                $table->index('is_admin', 'users_is_admin_index');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropIndex('reports_status_created_at_index');
            $table->dropIndex('reports_user_id_created_at_index');
            $table->dropIndex('reports_city_status_index');
            $table->dropIndex('reports_category_status_index');
            $table->dropIndex('reports_likes_count_index');
            $table->dropIndex('reports_comments_count_index');
        });

        Schema::table('report_likes', function (Blueprint $table) {
            $table->dropUnique('report_likes_user_report_unique');
        });

        Schema::table('report_comments', function (Blueprint $table) {
            $table->dropIndex('report_comments_user_id_index');
            $table->dropIndex('report_comments_parent_id_index');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_is_admin_index');
        });
    }
};
