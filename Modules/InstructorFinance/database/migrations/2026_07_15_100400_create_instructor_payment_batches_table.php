<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instructor_finance.instructor_payment_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->nullable()->constrained('course_catalogue.seasons');
            $table->foreignId('centre_id')->nullable()->constrained('class_scheduling.centres');
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->string('status', 20)->default('draft');
            $table->foreignId('approved_by')->nullable()->constrained('auth.users');
            $table->date('payment_date')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instructor_finance.instructor_payment_batches');
    }
};
