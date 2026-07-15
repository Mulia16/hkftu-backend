<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instructor_finance.instructor_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('class_scheduling.classes');
            $table->foreignId('instructor_id')->constrained('auth.users');
            $table->string('template_id')->nullable();
            $table->string('file_path')->nullable();
            $table->string('status', 20)->default('draft');
            $table->timestamp('signed_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('instructor_id');
            $table->unique(['class_id', 'instructor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instructor_finance.instructor_contracts');
    }
};
