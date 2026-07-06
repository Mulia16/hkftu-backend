<?php

namespace Modules\Attendance\DTOs;

use Spatie\LaravelData\Data;

class UpdateAttendanceData extends Data
{
    public function __construct(
        public string $status,
        public ?string $remarks = null,
    ) {}

    public static function rules(): array
    {
        return [
            'status' => ['required', 'in:present,absent,late,excused'],
            'remarks' => ['nullable', 'string', 'max:500'],
        ];
    }
}
