<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('disclosures', function (Blueprint $table) {
            $table->id();
            $table->string('alias_id')->unique();
            $table->string('channel', 32)->nullable(); // web, sms, whatsapp
            $table->unsignedTinyInteger('risk_score')->nullable();
            $table->string('status', 32)->default('open');
            $table->foreignId('report_id')->nullable()->constrained('reports')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disclosures');
    }
};


