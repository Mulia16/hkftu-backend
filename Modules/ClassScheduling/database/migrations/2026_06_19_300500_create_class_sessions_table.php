<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_scheduling.class_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('class_scheduling.classes')->cascadeOnDelete();
            $table->unsignedSmallInteger('session_no');
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->foreignId('classroom_id')->nullable()->constrained('class_scheduling.classrooms')->nullOnDelete();
            $table->foreignId('instructor_id')->nullable()->constrained('auth.users')->nullOnDelete();
            $table->enum('status', ['scheduled', 'completed', 'cancelled'])->default('scheduled');
            $table->timestamps();

            $table->unique(['class_id', 'session_no']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_scheduling.class_sessions');
    }
};
