<?php

namespace Modules\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberPricingEligibility extends Model
{
    protected $table = 'auth.member_pricing_eligibilities';

    protected $fillable = [
        'learner_profile_id',
        'member_verification_id',
        'member_type',
        'pricing_rule',
        'discount_percentage',
        'applicable_seasons',
        'applicable_centres',
        'applicable_course_types',
        'is_active',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'discount_percentage' => 'decimal:2',
            'applicable_seasons' => 'array',
            'applicable_centres' => 'array',
            'applicable_course_types' => 'array',
            'is_active' => 'boolean',
            'expires_at' => 'datetime',
        ];
    }

    public function learnerProfile(): BelongsTo
    {
        return $this->belongsTo(LearnerProfile::class);
    }

    public function memberVerification(): BelongsTo
    {
        return $this->belongsTo(MemberVerification::class);
    }
}
