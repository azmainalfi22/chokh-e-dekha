<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->unsignedTinyInteger('escalation_level')->default(0)->after('priority');
            $table->timestamp('escalated_at')->nullable()->after('escalation_level');
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn(['escalation_level','escalated_at']);
        });
    }
};


