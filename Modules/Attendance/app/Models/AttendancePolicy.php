<?php

namespace Modules\Attendance\Models;

use Illuminate\Database\Eloquent\Model;

class AttendancePolicy extends Model
{
    protected $table = 'attendance.attendance_policies';

    protected $fillable = [
        'name',
        'course_type',
        'min_percentage',
        'exam_required',
        'rules_json',
        'effective_from',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'rules_json' => 'array',
            'exam_required' => 'boolean',
            'is_active' => 'boolean',
            'effective_from' => 'date',
            'min_percentage' => 'integer',
        ];
    }
}
