<?php

namespace Modules\Enrolment\DTOs;

use Spatie\LaravelData\Data;

class CancelSeatReservationData extends Data
{
    public function __construct(
        public int $learner_id,
    ) {}

    public static function rules(): array
    {
        return [
            'learner_id' => ['required', 'exists:auth.learner_profiles,id'],
        ];
    }
}
