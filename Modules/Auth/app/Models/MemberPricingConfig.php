<?php

namespace Modules\Auth\Models;

use Illuminate\Database\Eloquent\Model;

class MemberPricingConfig extends Model
{
    protected $table = 'auth.member_pricing_config';

    protected $fillable = [
        'default_percentage',
        'by_type',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'default_percentage' => 'decimal:2',
            'by_type' => 'array',
        ];
    }

    public static function current(): self
    {
        return static::query()->orderBy('id')->firstOrCreate([], [
            'default_percentage' => (float) config('membership.member_discount_percentage.default', 0),
            'by_type' => [],
        ]);
    }

    public function percentageFor(?string $memberType): float
    {
        $byType = $this->by_type ?? [];

        if ($memberType !== null && array_key_exists($memberType, $byType)) {
            return (float) $byType[$memberType];
        }

        return (float) $this->default_percentage;
    }
}
