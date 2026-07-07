<?php

namespace Modules\Payment\DTOs;

use Spatie\LaravelData\Data;

class VerifyPaymentData extends Data
{
    public function __construct(
        public string $action,
        public ?string $reject_reason = null,
    ) {}

    public static function rules(): array
    {
        return [
            'action' => ['required', 'in:approve,reject'],
            'reject_reason' => ['required_if:action,reject', 'nullable', 'string', 'max:500'],
        ];
    }
}
