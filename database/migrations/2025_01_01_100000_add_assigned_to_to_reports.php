<?php

// database/migrations/2025_01_01_100000_add_assigned_to_to_reports.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('reports', function (Blueprint $t) {
            $t->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('status_updated_at')->nullable();
        });
    }
    public function down(): void {
        Schema::table('reports', function (Blueprint $t) {
            $t->dropConstrainedForeignId('assigned_to');
            $t->dropColumn('status_updated_at');
        });
    }
};
