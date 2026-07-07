<?php

namespace Modules\Payment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\Enrolment\Models\Enrolment;

class PaymentIntent extends Model
{
    protected $table = 'payment.payment_intents';

    protected $fillable = [
        'enrolment_id',
        'amount',
        'currency',
        'method',
        'status',
        'gateway',
        'gateway_intent_id',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'expires_at' => 'datetime',
        ];
    }

    public function enrolment(): BelongsTo
    {
        return $this->belongsTo(Enrolment::class, 'enrolment_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class, 'payment_intent_id');
    }

    public function receipt(): HasOne
    {
        return $this->hasOne(Receipt::class, 'payment_intent_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}
