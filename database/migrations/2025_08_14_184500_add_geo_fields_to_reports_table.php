<?php

// database/migrations/XXXX_XX_XX_XXXXXX_add_geo_fields_to_reports_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable()->after('location');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->string('place_id', 191)->nullable()->after('longitude');
            $table->string('formatted_address')->nullable()->after('place_id');

            // Helpful for “nearest” queries
            $table->index(['latitude','longitude'], 'reports_lat_lng_idx');
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropIndex('reports_lat_lng_idx');
            $table->dropColumn(['latitude','longitude','place_id','formatted_address']);
        });
    }
};
