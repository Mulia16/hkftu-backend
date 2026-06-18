<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_catalogue.subjects', function (Blueprint $table) {
            $table->id();
            $table->string('subject_code')->unique();
            $table->string('name');
            $table->decimal('tuition_fee', 10, 2)->default(0);
            $table->decimal('material_fee', 10, 2)->default(0);
            $table->decimal('instructor_fee_default', 10, 2)->nullable();
            $table->decimal('total_hours', 6, 2);
            $table->decimal('lesson_hours', 4, 2);
            $table->string('status')->default('draft');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_catalogue.subjects');
    }
};
