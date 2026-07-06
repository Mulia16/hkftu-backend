<?php

namespace Modules\Attendance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Auth\Models\User;
use Modules\ClassScheduling\Models\ClassSession;
use Modules\Enrolment\Models\Enrolment;

class AttendanceRecord extends Model
{
    protected $table = 'attendance.attendance_records';

    protected $fillable = [
        'class_session_id',
        'enrolment_id',
        'status',
        'marked_by',
        'marked_at',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'marked_at' => 'datetime',
        ];
    }

    public function classSession(): BelongsTo
    {
        return $this->belongsTo(ClassSession::class, 'class_session_id');
    }

    public function enrolment(): BelongsTo
    {
        return $this->belongsTo(Enrolment::class, 'enrolment_id');
    }

    public function marker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by');
    }
}
