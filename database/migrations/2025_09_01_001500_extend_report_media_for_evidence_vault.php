<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('report_media', function (Blueprint $table) {
            if (!Schema::hasColumn('report_media', 'sha256')) {
                $table->string('sha256', 64)->nullable();
            }
            if (!Schema::hasColumn('report_media', 'provenance')) {
                $table->json('provenance')->nullable();
            }
            if (!Schema::hasColumn('report_media', 'redactions')) {
                $table->json('redactions')->nullable();
            }
        });

        Schema::create('evidence_access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_media_id')->constrained('report_media')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 32); // viewed, downloaded, redacted
            $table->string('ip')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
            $table->index(['report_media_id','created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evidence_access_logs');
        Schema::table('report_media', function (Blueprint $table) {
            $drop = [];
            foreach (['sha256','provenance','redactions'] as $col) {
                if (Schema::hasColumn('report_media', $col)) { $drop[] = $col; }
            }
            if ($drop) { $table->dropColumn($drop); }
        });
    }
};


