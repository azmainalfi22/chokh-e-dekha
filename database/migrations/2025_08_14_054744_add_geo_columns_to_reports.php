<?php

// database/migrations/xxxx_xx_xx_xxxxxx_add_geo_columns_to_reports.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('reports', function (Blueprint $t) {
            $t->double('latitude', 10, 7)->nullable()->index();
            $t->double('longitude', 10, 7)->nullable()->index();
            $t->string('formatted_address', 255)->nullable();
            $t->string('place_id', 128)->nullable()->index();
            $t->timestamp('geocoded_at')->nullable();
        });
    }
    public function down(): void {
        Schema::table('reports', function (Blueprint $t) {
            $t->dropColumn(['latitude','longitude','formatted_address','place_id','geocoded_at']);
        });
    }
};
