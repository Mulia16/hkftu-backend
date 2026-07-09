<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment.refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrolment_id')->constrained('enrolment.enrolments');
            $table->foreignId('payment_intent_id')->constrained('payment.payment_intents');
            $table->decimal('amount', 10, 2);
            $table->text('reason');
            $table->string('status', 20)->default('requested');
            $table->foreignId('requested_by')->constrained('auth.users');
            $table->foreignId('approved_by')->nullable()->constrained('auth.users');
            $table->text('rejection_reason')->nullable();
            $table->string('gateway_ref')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment.refunds');
    }
};
