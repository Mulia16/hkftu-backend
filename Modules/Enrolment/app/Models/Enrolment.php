<?php

namespace Modules\Enrolment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Auth\Models\LearnerProfile;
use Modules\Auth\Models\User;
use Modules\ClassScheduling\Models\CourseClass;

class Enrolment extends Model
{
    protected $table = 'enrolment.enrolments';

    protected $fillable = [
        'class_id',
        'learner_id',
        'reservation_id',
        'status',
        'channel',
        'price_snapshot_json',
        'member_snapshot_json',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'price_snapshot_json' => 'array',
            'member_snapshot_json' => 'array',
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

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(SeatReservation::class, 'reservation_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
