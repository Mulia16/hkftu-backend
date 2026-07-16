<?php

namespace Modules\Payment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReconciliationItem extends Model
{
    public $timestamps = false;

    protected $table = 'payment.reconciliation_items';

    protected $fillable = [
        'batch_id',
        'gateway_txn_id',
        'amount',
        'matched_payment_id',
        'status',
        'exception_reason',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ReconciliationBatch::class, 'batch_id');
    }

    public function matchedPayment(): BelongsTo
    {
        return $this->belongsTo(PaymentTransaction::class, 'matched_payment_id');
    }
}
