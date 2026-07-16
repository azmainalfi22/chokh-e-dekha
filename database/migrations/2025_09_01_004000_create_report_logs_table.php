<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('report_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained('reports')->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 64); // status_changed, sla_breach, assigned, note_added
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->index(['report_id','created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_logs');
    }
};


