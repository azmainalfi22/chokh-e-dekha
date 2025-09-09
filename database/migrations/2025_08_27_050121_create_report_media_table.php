<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('report_media', function (Blueprint $table) {
      $table->id();
      $table->foreignId('report_id')->constrained()->cascadeOnDelete();
      $table->string('file_path');           // e.g. reports/abc123.jpg (relative to disk root)
      $table->string('original_name')->nullable();
      $table->string('mime_type')->nullable();
      $table->unsignedInteger('file_size')->nullable();
      $table->timestamps();
      $table->index('report_id');
    });
  }
  public function down(): void {
    Schema::dropIfExists('report_media');
  }
};
