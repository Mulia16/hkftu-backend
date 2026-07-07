<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment.payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_intent_id')->constrained('payment.payment_intents');
            $table->string('gateway_txn_id')->nullable();
            $table->string('status', 20)->default('pending');
            $table->string('payment_proof')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('auth.users');
            $table->timestamp('approved_at')->nullable();
            $table->text('reject_reason')->nullable();
            $table->jsonb('raw_callback_json')->nullable();
            $table->boolean('verified')->default(false);
            $table->timestamp('received_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('gateway_txn_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment.payment_transactions');
    }
};
