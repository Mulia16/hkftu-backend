<?php

namespace Modules\Payment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\CourseCatalogue\Models\Season;

class CouponCampaign extends Model
{
    protected $table = 'payment.coupon_campaigns';

    protected $fillable = [
        'code',
        'name',
        'season_id',
        'discount_type',
        'value',
        'valid_from',
        'valid_to',
        'rules_json',
        'max_usage',
        'used_count',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'rules_json' => 'array',
            'valid_from' => 'datetime',
            'valid_to' => 'datetime',
            'used_count' => 'integer',
            'max_usage' => 'integer',
        ];
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class, 'season_id');
    }

    public function codes(): HasMany
    {
        return $this->hasMany(CouponCode::class, 'campaign_id');
    }

    public function isAvailable(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }
        if ($this->valid_from && now()->lt($this->valid_from)) {
            return false;
        }
        if ($this->valid_to && now()->gt($this->valid_to)) {
            return false;
        }
        if ($this->max_usage !== null && $this->used_count >= $this->max_usage) {
            return false;
        }

        return true;
    }

    public function calculateDiscount(float $baseAmount): float
    {
        return match ($this->discount_type) {
            'percentage' => $baseAmount * ($this->value / 100),
            'fixed' => min($this->value, $baseAmount),
            'full_subsidy' => $baseAmount,
            default => 0,
        };
    }
}
