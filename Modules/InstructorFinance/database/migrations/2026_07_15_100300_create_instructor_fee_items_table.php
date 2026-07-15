<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instructor_finance.instructor_fee_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('class_scheduling.classes');
            $table->foreignId('instructor_id')->constrained('auth.users');
            $table->foreignId('fee_rule_id')->nullable()->constrained('instructor_finance.instructor_fee_rules');
            $table->decimal('amount', 10, 2);
            $table->decimal('adjustment', 10, 2)->default(0);
            $table->string('status', 20)->default('calculated');
            $table->timestamp('calculated_at');
            $table->timestamps();

            $table->index('status');
            $table->index('instructor_id');
            $table->unique(['class_id', 'instructor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instructor_finance.instructor_fee_items');
    }
};
