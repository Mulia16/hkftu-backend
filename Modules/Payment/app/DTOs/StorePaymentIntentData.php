<?php

namespace Modules\Payment\DTOs;

use Spatie\LaravelData\Data;

class StorePaymentIntentData extends Data
{
    public function __construct(
        public ?int $enrolment_id = null,
        public ?int $reservation_id = null,
        public string $method = 'manual_transfer',
    ) {}

    public static function rules(): array
    {
        return [
            'enrolment_id' => ['nullable', 'integer', 'required_without:reservation_id'],
            'reservation_id' => ['nullable', 'integer', 'required_without:enrolment_id'],
            'method' => ['required', 'in:manual_transfer,razerms'],
        ];
    }
}
