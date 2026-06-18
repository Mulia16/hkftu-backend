<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_catalogue.subject_categories', function (Blueprint $table) {
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('category_id');

            $table->primary(['subject_id', 'category_id']);

            $table->foreign('subject_id')
                ->references('id')
                ->on('course_catalogue.subjects')
                ->cascadeOnDelete();

            $table->foreign('category_id')
                ->references('id')
                ->on('course_catalogue.categories')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_catalogue.subject_categories');
    }
};
