<?php

namespace Modules\Enrolment\DTOs;

use Spatie\LaravelData\Data;

class StoreWaitlistData extends Data
{
    public function __construct(
        public int $class_id,
        public int $learner_id,
    ) {}

    public static function rules(): array
    {
        return [
            'class_id' => ['required', 'exists:class_scheduling.classes,id'],
            'learner_id' => ['required', 'exists:auth.learner_profiles,id'],
        ];
    }
}
