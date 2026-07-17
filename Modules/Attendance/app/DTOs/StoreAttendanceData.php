<?php

namespace Modules\Attendance\DTOs;

use Modules\Attendance\Enums\AttendanceStatus;
use Spatie\LaravelData\Data;

class StoreAttendanceData extends Data
{
    public function __construct(
        public int $class_session_id,
        public int $enrolment_id,
        public AttendanceStatus $status = AttendanceStatus::Absent,
        public ?string $remarks = null,
    ) {}

    public static function rules(): array
    {
        return [
            'class_session_id' => ['required', 'exists:class_scheduling.class_sessions,id'],
            'enrolment_id' => ['required', 'integer'],
            'status' => ['required', 'in:present,absent,late,excused'],
            'remarks' => ['nullable', 'string', 'max:500'],
        ];
    }
}
