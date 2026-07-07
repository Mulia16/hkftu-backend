<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment.payment_intents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrolment_id')->constrained('enrolment.enrolments');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('HKD');
            $table->string('method', 30)->default('manual_transfer');
            $table->string('status', 20)->default('pending');
            $table->string('gateway', 30)->nullable();
            $table->string('gateway_intent_id')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('gateway_intent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment.payment_intents');
    }
};
