<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payment.reconciliation_batches', function (Blueprint $table) {
            $table->id();
            $table->string('gateway')->default('razerms');
            $table->date('settlement_date');
            $table->string('file_path')->nullable();
            $table->string('status', 20)->default('draft');
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('matched_amount', 12, 2)->default(0);
            $table->decimal('unmatched_amount', 12, 2)->default(0);
            $table->foreignId('created_by')->nullable()->constrained('auth.users');
            $table->timestamps();
            $table->index('status');
        });

        Schema::create('payment.reconciliation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('payment.reconciliation_batches');
            $table->string('gateway_txn_id');
            $table->decimal('amount', 10, 2);
            $table->foreignId('matched_payment_id')->nullable()->constrained('payment.payment_transactions');
            $table->string('status', 20)->default('unmatched');
            $table->text('exception_reason')->nullable();
            $table->timestamp('created_at');
            $table->index('status');
            $table->index('gateway_txn_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment.reconciliation_items');
        Schema::dropIfExists('payment.reconciliation_batches');
    }
};
