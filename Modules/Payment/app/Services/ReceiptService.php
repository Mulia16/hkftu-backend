<?php

namespace Modules\Payment\Services;

use Illuminate\Support\Facades\DB;

class ReceiptService
{
    public static function generateReceiptNumber(): string
    {
        $year = now()->format('Y');

        return DB::transaction(function () use ($year) {
            $last = DB::table('payment.receipts')
                ->where('receipt_no', 'like', "RCP-{$year}-%")
                ->orderByDesc('receipt_no')
                ->value('receipt_no');

            if ($last) {
                $seq = (int) substr($last, -5) + 1;
            } else {
                $seq = 1;
            }

            return sprintf('RCP-%s-%05d', $year, $seq);
        });
    }
}
