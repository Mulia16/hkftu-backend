<?php

namespace Modules\Attendance\DTOs;

use Spatie\LaravelData\Data;

class BatchAttendanceData extends Data
{
    public function __construct(
        public int $class_session_id,
        /** @var array<int, array{enrolment_id: int, status: string, remarks?: string}> */
        public array $records,
    ) {}

    public static function rules(): array
    {
        return [
            'class_session_id' => ['required', 'exists:class_scheduling.class_sessions,id'],
            'records' => ['required', 'array', 'min:1'],
            'records.*.enrolment_id' => ['required', 'exists:enrolment.enrolments,id'],
            'records.*.status' => ['required', 'in:present,absent,late,excused'],
            'records.*.remarks' => ['nullable', 'string', 'max:500'],
        ];
    }
}
