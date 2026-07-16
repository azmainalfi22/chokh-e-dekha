<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('survey_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained('surveys')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('city_corporation')->nullable();
            $table->string('ward')->nullable();
            $table->json('answers'); // map question_id => value
            $table->timestamps();
            $table->index(['survey_id','created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_responses');
    }
};


