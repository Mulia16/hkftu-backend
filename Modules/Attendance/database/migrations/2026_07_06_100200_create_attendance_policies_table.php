<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance.attendance_policies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('course_type')->nullable();
            $table->integer('min_percentage')->default(75);
            $table->boolean('exam_required')->default(false);
            $table->jsonb('rules_json')->nullable();
            $table->date('effective_from')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance.attendance_policies');
    }
};
