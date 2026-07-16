<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->timestamp('assigned_at')->nullable()->after('assigned_to');
            $table->timestamp('sla_due_at')->nullable()->after('status_updated_at');
            $table->string('priority', 20)->nullable()->after('status');

            $table->index('sla_due_at');
            $table->index('priority');
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropIndex(['sla_due_at']);
            $table->dropIndex(['priority']);
            $table->dropColumn(['assigned_at','sla_due_at','priority']);
        });
    }
};


