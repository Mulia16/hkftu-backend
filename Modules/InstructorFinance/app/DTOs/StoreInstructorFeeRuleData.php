<?php

namespace Modules\InstructorFinance\DTOs;

use Spatie\LaravelData\Data;

class StoreInstructorFeeRuleData extends Data
{
    public function __construct(
        public ?int $subject_id = null,
        public ?int $course_id = null,
        public string $rate_type = 'per_session',
        public float $amount = 0,
        public string $effective_from = '',
        public ?array $rules_json = null,
    ) {}

    public static function rules(): array
    {
        return [
            'subject_id' => ['nullable', 'integer'],
            'course_id' => ['nullable', 'integer'],
            'rate_type' => ['required', 'string', 'in:per_session,per_hour,flat,per_student'],
            'amount' => ['required', 'numeric', 'min:0'],
            'effective_from' => ['required', 'date'],
            'rules_json' => ['nullable', 'array'],
        ];
    }
}
