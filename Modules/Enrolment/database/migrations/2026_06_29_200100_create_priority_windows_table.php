<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrolment.priority_windows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->constrained('course_catalogue.seasons')->cascadeOnDelete();
            $table->foreignId('course_id')->nullable()->references('id')->on('course_catalogue.courses')->nullOnDelete();
            $table->foreignId('class_id')->nullable()->references('id')->on('class_scheduling.classes')->nullOnDelete();
            $table->string('channel')->comment('online_member, online_public, counter, proxy');
            $table->string('eligibility_rule')->nullable()->comment('member, public, returning_student, senior');
            $table->timestamp('start_at');
            $table->timestamp('end_at');
            $table->timestamps();

            $table->index('season_id');
            $table->index('course_id');
            $table->index('class_id');
            $table->index('channel');
            $table->index(['start_at', 'end_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrolment.priority_windows');
    }
};
