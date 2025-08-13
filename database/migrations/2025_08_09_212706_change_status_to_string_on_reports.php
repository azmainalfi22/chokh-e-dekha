<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->string('status', 20)->default('pending')->change();
        });
    }

    public function down(): void
    {
        // If you want to go back to ENUM, specify the exact set you had:
        // Schema::table('reports', function (Blueprint $table) {
        //     DB::statement("ALTER TABLE reports MODIFY status ENUM('pending','in_progress','resolved','rejected') NOT NULL DEFAULT 'pending'");
        // });
    }
};
