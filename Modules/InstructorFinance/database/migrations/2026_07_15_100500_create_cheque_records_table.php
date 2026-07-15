<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instructor_finance.cheque_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_batch_id')->constrained('instructor_finance.instructor_payment_batches');
            $table->foreignId('instructor_id')->constrained('auth.users');
            $table->string('cheque_no')->nullable();
            $table->string('payee');
            $table->decimal('amount', 10, 2);
            $table->timestamp('printed_at')->nullable();
            $table->string('status', 20)->default('draft');
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instructor_finance.cheque_records');
    }
};
