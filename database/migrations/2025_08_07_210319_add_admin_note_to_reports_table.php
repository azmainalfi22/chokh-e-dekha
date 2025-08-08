<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
// database/migrations/xxxx_xx_xx_xxxxxx_add_admin_note_to_reports_table.php
public function up(): void
{
    Schema::table('reports', function (Blueprint $table) {
        $table->text('admin_note')->nullable()->after('status');
    });
}

public function down(): void
{
    Schema::table('reports', function (Blueprint $table) {
        $table->dropColumn('admin_note');
    });
}

};
