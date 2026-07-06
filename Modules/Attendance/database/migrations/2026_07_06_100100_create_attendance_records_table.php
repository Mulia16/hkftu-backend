<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance.attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_session_id')->constrained('class_scheduling.class_sessions');
            $table->foreignId('enrolment_id')->constrained('enrolment.enrolments');
            $table->string('status', 20)->default('absent');
            $table->foreignId('marked_by')->nullable()->constrained('auth.users');
            $table->timestamp('marked_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(['class_session_id', 'enrolment_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance.attendance_records');
    }
};
