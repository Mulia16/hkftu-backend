<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_scheduling.classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('course_catalogue.courses')->cascadeOnDelete();
            $table->foreignId('schedule_pattern_id')->nullable()->constrained('class_scheduling.schedule_patterns')->nullOnDelete();
            $table->string('class_code', 30)->unique();
            $table->foreignId('centre_id')->constrained('class_scheduling.centres');
            $table->foreignId('classroom_id')->nullable()->constrained('class_scheduling.classrooms')->nullOnDelete();
            $table->unsignedSmallInteger('capacity');
            $table->unsignedSmallInteger('min_students')->default(1);
            $table->date('start_date');
            $table->date('end_date');
            $table->foreignId('instructor_id')->nullable()->constrained('auth.users')->nullOnDelete();
            $table->enum('status', ['draft', 'published', 'cancelled', 'completed'])->default('draft');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_scheduling.classes');
    }
};
