<?php

namespace Modules\Payment\Services;

use Illuminate\Support\Collection;
use Modules\Payment\DTOs\StoreReconciliationData;
use Modules\Payment\Models\PaymentTransaction;
use Modules\Payment\Models\ReconciliationBatch;
use Modules\Payment\Models\ReconciliationItem;

class ReconciliationService
{
    public function createBatch(StoreReconciliationData $data, int $userId): ReconciliationBatch
    {
        $totalAmount = collect($data->items)->sum('amount');

        $batch = ReconciliationBatch::create([
            'gateway' => $data->gateway,
            'settlement_date' => $data->settlement_date,
            'status' => 'draft',
            'total_amount' => $totalAmount,
            'unmatched_amount' => $totalAmount,
            'created_by' => $userId,
        ]);

        foreach ($data->items as $item) {
            ReconciliationItem::create([
                'batch_id' => $batch->id,
                'gateway_txn_id' => $item['gateway_txn_id'],
                'amount' => $item['amount'],
                'status' => 'unmatched',
                'created_at' => now(),
            ]);
        }

        return $batch->load('items');
    }

    public function autoMatch(int $batchId): ReconciliationBatch
    {
        $batch = ReconciliationBatch::with('items')->findOrFail($batchId);
        $matchedAmount = 0;
        $unmatchedAmount = 0;

        foreach ($batch->items as $item) {
            if ($item->status === 'matched') {
                $matchedAmount += (float) $item->amount;
                continue;
            }

            $payment = PaymentTransaction::where('gateway_txn_id', $item->gateway_txn_id)->first();

            if ($payment) {
                $item->update([
                    'matched_payment_id' => $payment->id,
                    'status' => 'matched',
                ]);
                $matchedAmount += (float) $item->amount;
            } else {
                $unmatchedAmount += (float) $item->amount;
            }
        }

        $batch->update([
            'matched_amount' => $matchedAmount,
            'unmatched_amount' => $unmatchedAmount,
        ]);

        return $batch->fresh('items');
    }

    public function getExceptions(int $batchId): Collection
    {
        return ReconciliationItem::where('batch_id', $batchId)
            ->whereIn('status', ['unmatched', 'exception'])
            ->get();
    }

    public function closeBatch(int $batchId): ReconciliationBatch
    {
        $batch = ReconciliationBatch::findOrFail($batchId);
        $batch->update(['status' => 'closed']);

        return $batch->fresh('items');
    }
}
