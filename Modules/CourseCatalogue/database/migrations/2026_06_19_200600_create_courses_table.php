<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_catalogue.courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->constrained('course_catalogue.seasons')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('course_catalogue.subjects')->cascadeOnDelete();
            $table->string('course_code', 30)->unique();
            $table->unsignedSmallInteger('page_no')->nullable();
            $table->enum('status', ['draft', 'review', 'approved', 'published', 'archived'])->default('draft');
            $table->timestamp('publish_at')->nullable();
            $table->timestamps();

            $table->unique(['season_id', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_catalogue.courses');
    }
};
