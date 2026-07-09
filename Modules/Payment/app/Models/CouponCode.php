<?php

namespace Modules\Payment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Auth\Models\User;

class CouponCode extends Model
{
    protected $table = 'payment.coupon_codes';

    protected $fillable = [
        'campaign_id',
        'code',
        'code_hash',
        'status',
        'assigned_to',
        'usage_limit',
        'used_count',
    ];

    protected function casts(): array
    {
        return [
            'used_count' => 'integer',
            'usage_limit' => 'integer',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(CouponCampaign::class, 'campaign_id');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(CouponRedemption::class, 'coupon_code_id');
    }

    public function isUsable(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }
        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }
}
