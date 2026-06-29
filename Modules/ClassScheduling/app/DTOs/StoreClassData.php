<?php

namespace Modules\ClassScheduling\DTOs;

use Spatie\LaravelData\Data;

class StoreClassData extends Data
{
    public function __construct(
        public int $season_id,
        public int $subject_id,
        public string $class_code,
        public int $centre_id,
        public ?int $classroom_id,
        public int $capacity,
        public ?int $min_students,
        public string $start_date,
        public string $end_date,
        public ?int $instructor_id = null,
        public ?array $schedule_pattern = null,
    ) {}

    public static function rules(): array
    {
        return [
            'season_id' => ['required', 'exists:course_catalogue.seasons,id'],
            'subject_id' => ['required', 'exists:course_catalogue.subjects,id'],
            'class_code' => ['required', 'string', 'max:30', 'unique:class_scheduling.classes,class_code'],
            'centre_id' => ['required', 'exists:class_scheduling.centres,id'],
            'classroom_id' => ['nullable', 'exists:class_scheduling.classrooms,id'],
            'capacity' => ['required', 'integer', 'min:1'],
            'min_students' => ['nullable', 'integer', 'min:1'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'instructor_id' => ['nullable', 'exists:auth.users,id'],
            'schedule_pattern' => ['nullable', 'array'],
            'schedule_pattern.type' => ['required_with:schedule_pattern', 'in:weekly,one_off'],
            'schedule_pattern.days_of_week' => ['nullable', 'array'],
            'schedule_pattern.days_of_week.*' => ['integer', 'between:0,6'],
            'schedule_pattern.start_time' => ['required_with:schedule_pattern', 'date_format:H:i'],
            'schedule_pattern.end_time' => ['required_with:schedule_pattern', 'date_format:H:i', 'after:schedule_pattern.start_time'],
            'schedule_pattern.overrides' => ['nullable', 'array'],
        ];
    }
}
