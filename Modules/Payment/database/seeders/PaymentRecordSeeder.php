<?php

namespace Modules\Payment\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentRecordSeeder extends Seeder
{
    public function run(): void
    {
        $enrolments = DB::table('enrolment.enrolments')->where('status', 'confirmed')->get();

        if ($enrolments->isEmpty()) {
            return;
        }

        $now = now();
        $statuses = ['pending', 'paid', 'paid', 'paid', 'failed'];

        foreach ($enrolments->take(5) as $enrolment) {
            $status = $statuses[array_rand($statuses)];

            $intentId = DB::table('payment.payment_intents')->insertGetId([
                'enrolment_id' => $enrolment->id,
                'amount' => 800,
                'currency' => 'HKD',
                'method' => 'manual_transfer',
                'status' => $status,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $txnStatus = $status === 'paid' ? 'paid' : ($status === 'failed' ? 'failed' : 'pending');

            DB::table('payment.payment_transactions')->insert([
                'payment_intent_id' => $intentId,
                'status' => $txnStatus,
                'approved_by' => $txnStatus !== 'pending' ? 1 : null,
                'approved_at' => $txnStatus !== 'pending' ? $now : null,
                'reject_reason' => $txnStatus === 'failed' ? 'Blur image, please re-upload' : null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            if ($status === 'paid') {
                DB::table('payment.receipts')->insert([
                    'receipt_no' => sprintf('RCP-2026-%05d', $intentId),
                    'payment_intent_id' => $intentId,
                    'enrolment_id' => $enrolment->id,
                    'amount' => 800,
                    'issued_at' => $now,
                    'issued_by' => 1,
                    'status' => 'issued',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
