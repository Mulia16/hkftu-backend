<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instructor_finance.instructor_fee_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->nullable()->constrained('course_catalogue.subjects');
            $table->foreignId('course_id')->nullable()->constrained('course_catalogue.courses');
            $table->string('rate_type', 20);
            $table->decimal('amount', 10, 2);
            $table->date('effective_from');
            $table->jsonb('rules_json')->nullable();
            $table->timestamps();

            $table->index('rate_type');
            $table->index('effective_from');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instructor_finance.instructor_fee_rules');
    }
};
