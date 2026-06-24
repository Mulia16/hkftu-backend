<?php

namespace Modules\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberStatusSnapshot extends Model
{
    protected $table = 'auth.member_status_snapshots';

    protected $fillable = [
        'learner_profile_id',
        'membership_no',
        'status',
        'source',
        'raw_response',
        'verified_by',
    ];

    protected function casts(): array
    {
        return [
            'raw_response' => 'array',
        ];
    }

    public function learnerProfile(): BelongsTo
    {
        return $this->belongsTo(LearnerProfile::class);
    }
}
