<?php

namespace Modules\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberVerification extends Model
{
    protected $table = 'auth.member_verifications';

    protected $fillable = [
        'learner_profile_id',
        'membership_no',
        'status',
        'source',
        'request_data',
        'response_data',
        'verified_by',
        'failure_reason',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'request_data' => 'array',
            'response_data' => 'array',
            'verified_at' => 'datetime',
        ];
    }

    public function learnerProfile(): BelongsTo
    {
        return $this->belongsTo(LearnerProfile::class);
    }
}
