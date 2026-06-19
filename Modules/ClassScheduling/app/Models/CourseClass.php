<?php

namespace Modules\ClassScheduling\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Auth\Models\User;
use Modules\CourseCatalogue\Models\Course;

class CourseClass extends Model
{
    use SoftDeletes;

    protected $table = 'class_scheduling.classes';

    protected $fillable = [
        'course_id',
        'schedule_pattern_id',
        'class_code',
        'centre_id',
        'classroom_id',
        'capacity',
        'min_students',
        'start_date',
        'end_date',
        'instructor_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function schedulePattern(): BelongsTo
    {
        return $this->belongsTo(SchedulePattern::class);
    }

    public function centre(): BelongsTo
    {
        return $this->belongsTo(Centre::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(ClassSession::class, 'class_id');
    }

    public function clashResults(): HasMany
    {
        return $this->hasMany(ClashCheckResult::class, 'class_id');
    }
}
