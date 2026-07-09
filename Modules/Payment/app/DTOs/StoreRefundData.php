<?php

namespace Modules\Payment\DTOs;

use Spatie\LaravelData\Data;

class StoreRefundData extends Data
{
    public function __construct(
        public int $enrolment_id,
        public float $amount,
        public string $reason,
    ) {}

    public static function rules(): array
    {
        return [
            'enrolment_id' => ['required', 'integer'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reason' => ['required', 'string', 'max:500'],
        ];
    }
}
