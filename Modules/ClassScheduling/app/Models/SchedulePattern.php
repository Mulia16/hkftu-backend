<?php

namespace Modules\ClassScheduling\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchedulePattern extends Model
{
    protected $table = 'class_scheduling.schedule_patterns';

    protected $fillable = [
        'type',
        'days_of_week',
        'start_time',
        'end_time',
        'overrides',
    ];

    protected function casts(): array
    {
        return [
            'days_of_week' => 'array',
            'overrides' => 'array',
        ];
    }

    public function classes(): HasMany
    {
        return $this->hasMany(CourseClass::class, 'schedule_pattern_id');
    }
}
