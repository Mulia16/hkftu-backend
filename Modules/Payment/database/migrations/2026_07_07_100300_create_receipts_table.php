<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment.receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_no')->unique();
            $table->foreignId('payment_intent_id')->constrained('payment.payment_intents');
            $table->foreignId('enrolment_id')->constrained('enrolment.enrolments');
            $table->decimal('amount', 10, 2);
            $table->timestamp('issued_at');
            $table->foreignId('issued_by')->nullable()->constrained('auth.users');
            $table->string('pdf_file_path')->nullable();
            $table->string('status', 20)->default('issued');
            $table->timestamps();

            $table->index('receipt_no');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment.receipts');
    }
};
