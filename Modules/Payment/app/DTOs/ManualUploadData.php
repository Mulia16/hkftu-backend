<?php

namespace Modules\Payment\DTOs;

use Spatie\LaravelData\Data;

class ManualUploadData extends Data
{
    public function __construct(
        public int $payment_intent_id,
        public $payment_proof,
    ) {}

    public static function rules(): array
    {
        return [
            'payment_intent_id' => ['required', 'integer'],
            'payment_proof' => ['required', 'image', 'max:5120'],
        ];
    }
}
