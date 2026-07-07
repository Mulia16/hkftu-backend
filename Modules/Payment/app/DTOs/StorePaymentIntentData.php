<?php

namespace Modules\Payment\DTOs;

use Spatie\LaravelData\Data;

class StorePaymentIntentData extends Data
{
    public function __construct(
        public int $enrolment_id,
        public string $method = 'manual_transfer',
    ) {}

    public static function rules(): array
    {
        return [
            'enrolment_id' => ['required', 'integer'],
            'method' => ['required', 'in:manual_transfer,razerms'],
        ];
    }
}
