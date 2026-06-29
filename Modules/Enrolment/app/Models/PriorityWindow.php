<?php

namespace Modules\Enrolment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\ClassScheduling\Models\CourseClass;
use Modules\CourseCatalogue\Models\Course;
use Modules\CourseCatalogue\Models\Season;

class PriorityWindow extends Model
{
    protected $table = 'enrolment.priority_windows';

    protected $fillable = [
        'season_id',
        'course_id',
        'class_id',
        'channel',
        'eligibility_rule',
        'start_at',
        'end_at',
    ];

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
        ];
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function courseClass(): BelongsTo
    {
        return $this->belongsTo(CourseClass::class, 'class_id');
    }

    public function isActive(): bool
    {
        $now = now();

        return $now->between($this->start_at, $this->end_at);
    }
}
