<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('reports', function (Blueprint $table) {
            // Only add if they don't already exist
            $table->index('city_corporation', 'reports_city_idx');
            $table->index('category', 'reports_category_idx');
            // Optional fulltext (MySQL 5.7+/8.0+) for better search
            // $table->fullText(['title','description'], 'reports_title_desc_ft');
        });
    }
    public function down(): void {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropIndex('reports_city_idx');
            $table->dropIndex('reports_category_idx');
            // $table->dropFullText('reports_title_desc_ft');
        });
    }
};
