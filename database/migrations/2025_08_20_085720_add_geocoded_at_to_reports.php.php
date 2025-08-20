<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('reports','geocoded_at')) {
            Schema::table('reports', function (Blueprint $table) {
                $table->timestamp('geocoded_at')->nullable()->after('formatted_address');
                $table->index('geocoded_at');
            });
        }
    }
    public function down(): void
    {
        if (Schema::hasColumn('reports','geocoded_at')) {
            Schema::table('reports', function (Blueprint $table) {
                $table->dropIndex(['geocoded_at']);
                $table->dropColumn('geocoded_at');
            });
        }
    }
};
