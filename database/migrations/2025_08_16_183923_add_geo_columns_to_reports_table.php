<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            if (!Schema::hasColumn('reports', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('location');
            }
            if (!Schema::hasColumn('reports', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }
            if (!Schema::hasColumn('reports', 'place_id')) {
                $table->string('place_id', 128)->nullable()->after('longitude');
            }
            if (!Schema::hasColumn('reports', 'formatted_address')) {
                $table->string('formatted_address', 255)->nullable()->after('place_id');
            }
            if (!Schema::hasColumn('reports', 'geocoded_at')) {
                $table->timestamp('geocoded_at')->nullable()->after('formatted_address');
            }

            // Optional helpful indexes
            if (!Schema::hasColumn('reports', 'status')) {
                // ignore if already exists in your schema
            } else {
                $table->index('status');
            }
            if (!Schema::hasColumn('reports', 'city_corporation')) {
                // ignore if already exists
            } else {
                $table->index('city_corporation');
            }
            if (!Schema::hasColumn('reports', 'category')) {
                // ignore if already exists
            } else {
                $table->index('category');
            }
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            if (Schema::hasColumn('reports', 'geocoded_at')) {
                $table->dropColumn('geocoded_at');
            }
            if (Schema::hasColumn('reports', 'formatted_address')) {
                $table->dropColumn('formatted_address');
            }
            if (Schema::hasColumn('reports', 'place_id')) {
                $table->dropColumn('place_id');
            }
            if (Schema::hasColumn('reports', 'longitude')) {
                $table->dropColumn('longitude');
            }
            if (Schema::hasColumn('reports', 'latitude')) {
                $table->dropColumn('latitude');
            }
        });
    }
};
