<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('title');                  // Title of the report
            $table->text('description');              // Detailed description
            $table->string('category');               // Garbage, Road, etc.
            $table->string('location')->nullable();   // Optional location
            $table->string('photo')->nullable();      // Uploaded photo (stored as path)
            $table->enum('status', ['pending', 'resolved'])->default('pending'); // Status
            $table->timestamps();                     // created_at, updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
