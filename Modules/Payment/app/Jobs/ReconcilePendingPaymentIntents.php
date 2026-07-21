<?php

namespace Modules\Payment\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\Payment\Models\PaymentIntent;
use Modules\Payment\Models\PaymentTransaction;
use Modules\Payment\Services\PaymentService;

class ReconcilePendingPaymentIntents implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(PaymentService $paymentService): void
    {
        $stale = PaymentIntent::where('status', 'pending')
            ->whereNotNull('gateway')
            ->where('expires_at', '<=', now())
            ->get();

        $reconciled = 0;
        $released = 0;

        foreach ($stale as $intent) {
            $paidTransaction = PaymentTransaction::where('payment_intent_id', $intent->id)
                ->where('status', 'paid')
                ->where('verified', true)
                ->first();

            if ($paidTransaction) {
                $paymentService->confirmGatewayPayment($intent, $paidTransaction->gateway_txn_id ?? 'reconciled');
                $reconciled++;

                continue;
            }

            $paymentService->releaseHungIntent($intent);
            $released++;
        }

        if ($reconciled > 0 || $released > 0) {
            Log::info("Payment reconciliation: {$reconciled} finalised, {$released} released.");
        }
    }
}
