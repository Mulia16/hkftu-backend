<?php

namespace Modules\Payment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Auth\Models\User;
use Modules\Enrolment\Models\Enrolment;

class CouponRedemption extends Model
{
    protected $table = 'payment.coupon_redemptions';

    protected $fillable = [
        'coupon_code_id',
        'enrolment_id',
        'payment_intent_id',
        'amount_discounted',
        'redeemed_by',
        'redeemed_at',
        'voided_at',
    ];

    protected function casts(): array
    {
        return [
            'amount_discounted' => 'decimal:2',
            'redeemed_at' => 'datetime',
            'voided_at' => 'datetime',
        ];
    }

    public function couponCode(): BelongsTo
    {
        return $this->belongsTo(CouponCode::class, 'coupon_code_id');
    }

    public function enrolment(): BelongsTo
    {
        return $this->belongsTo(Enrolment::class, 'enrolment_id');
    }

    public function paymentIntent(): BelongsTo
    {
        return $this->belongsTo(PaymentIntent::class, 'payment_intent_id');
    }

    public function redeemer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'redeemed_by');
    }
}
