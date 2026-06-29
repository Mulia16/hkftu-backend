<?php

namespace Modules\Enrolment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Auth\Models\LearnerProfile;
use Modules\ClassScheduling\Models\CourseClass;

class Waitlist extends Model
{
    protected $table = 'enrolment.waitlists';

    protected $fillable = [
        'class_id',
        'learner_id',
        'position',
        'status',
        'offered_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'offered_at' => 'datetime',
            'expires_at' => 'datetime',
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
}
