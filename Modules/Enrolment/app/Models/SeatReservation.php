<?php

namespace Modules\Enrolment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Auth\Models\LearnerProfile;
use Modules\ClassScheduling\Models\CourseClass;

class SeatReservation extends Model
{
    protected $table = 'enrolment.seat_reservations';

    protected $fillable = [
        'class_id',
        'learner_id',
        'channel',
        'status',
        'expires_at',
        'idempotency_key',
        'amount_snapshot_json',
        'eligibility_snapshot_json',
        'ip',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'amount_snapshot_json' => 'array',
            'eligibility_snapshot_json' => 'array',
        ];
    }

    public function courseClass(): BelongsTo
    {
        return $this->belongsTo(CourseClass::class, 'class_id');
    }

    public function learner(): BelongsTo
    {
        return $this->belongsTo(LearnerProfile::class, 'learner_id');
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && ! $this->isExpired();
    }
}
