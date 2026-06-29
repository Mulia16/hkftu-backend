<?php

namespace Modules\Enrolment\DTOs;

use Spatie\LaravelData\Data;

class StoreEnrolmentData extends Data
{
    public function __construct(
        public int $class_id,
        public int $learner_id,
        public string $channel,
        public ?int $reservation_id = null,
    ) {}

    public static function rules(): array
    {
        return [
            'class_id' => ['required', 'exists:class_scheduling.classes,id'],
            'learner_id' => ['required', 'exists:auth.learner_profiles,id'],
            'channel' => ['required', 'in:online_member,online_public,counter,proxy,manual'],
            'reservation_id' => ['nullable', 'exists:enrolment.seat_reservations,id'],
        ];
    }
}
