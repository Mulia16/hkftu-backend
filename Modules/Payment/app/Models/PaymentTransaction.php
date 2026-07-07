<?php

namespace Modules\Payment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Auth\Models\User;

class PaymentTransaction extends Model
{
    protected $table = 'payment.payment_transactions';

    protected $fillable = [
        'payment_intent_id',
        'gateway_txn_id',
        'status',
        'payment_proof',
        'approved_by',
        'approved_at',
        'reject_reason',
        'raw_callback_json',
        'verified',
        'received_at',
    ];

    protected function casts(): array
    {
        return [
            'raw_callback_json' => 'array',
            'verified' => 'boolean',
            'approved_at' => 'datetime',
            'received_at' => 'datetime',
        ];
    }

    public function paymentIntent(): BelongsTo
    {
        return $this->belongsTo(PaymentIntent::class, 'payment_intent_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
