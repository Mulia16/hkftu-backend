<?php

namespace Modules\Enrolment\DTOs;

use Spatie\LaravelData\Data;

class StoreCounterEnrolmentData extends Data
{
    public function __construct(
        public int $learner_id,
        public int $class_id,
        public string $payment_method = 'counter_cash',
        public ?string $notes = null,
    ) {}

    public static function rules(): array
    {
        return [
            'learner_id' => ['required', 'integer'],
            'class_id' => ['required', 'integer'],
            'payment_method' => ['required', 'in:counter_cash,counter_cheque'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
