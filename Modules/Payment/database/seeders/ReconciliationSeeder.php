<?php

namespace Modules\Payment\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Payment\Models\ReconciliationBatch;
use Modules\Payment\Models\ReconciliationItem;

class ReconciliationSeeder extends Seeder
{
    public function run(): void
    {
        $batch = ReconciliationBatch::create([
            'gateway' => 'razerms',
            'settlement_date' => '2026-07-14',
            'status' => 'draft',
            'total_amount' => 450.00,
            'matched_amount' => 150.00,
            'unmatched_amount' => 300.00,
        ]);

        ReconciliationItem::create([
            'batch_id' => $batch->id,
            'gateway_txn_id' => 'RMS-TXN-001',
            'amount' => 150.00,
            'status' => 'matched',
            'created_at' => now(),
        ]);

        ReconciliationItem::create([
            'batch_id' => $batch->id,
            'gateway_txn_id' => 'RMS-TXN-002',
            'amount' => 200.00,
            'status' => 'unmatched',
            'created_at' => now(),
        ]);

        ReconciliationItem::create([
            'batch_id' => $batch->id,
            'gateway_txn_id' => 'RMS-TXN-003',
            'amount' => 100.00,
            'status' => 'unmatched',
            'created_at' => now(),
        ]);
    }
}
