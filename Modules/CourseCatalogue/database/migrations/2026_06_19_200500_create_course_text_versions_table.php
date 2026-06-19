<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_catalogue.course_text_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('course_catalogue.subjects')->cascadeOnDelete();
            $table->unsignedSmallInteger('version_no');
            $table->text('content_html');
            $table->enum('status', ['draft', 'review', 'approved', 'published', 'archived'])->default('draft');
            $table->foreignId('approved_by')->nullable()->constrained('auth.users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->unique(['subject_id', 'version_no']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_catalogue.course_text_versions');
    }
};
