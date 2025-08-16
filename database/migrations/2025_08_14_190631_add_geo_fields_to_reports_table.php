<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            // after 'location' keeps things tidy in your schema
            $table->decimal('latitude', 10, 7)->nullable()->after('location');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->string('place_id', 128)->nullable()->after('longitude');
            $table->string('formatted_address', 255)->nullable()->after('place_id');
            $table->timestamp('geocoded_at')->nullable()->after('formatted_address');

            // basic composite index for faster “nearest” queries
            $table->index(['latitude','longitude'], 'reports_lat_lng_idx');
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropIndex('reports_lat_lng_idx');
            $table->dropColumn(['latitude','longitude','place_id','formatted_address','geocoded_at']);
        });
    }
};
