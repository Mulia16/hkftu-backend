<?php

namespace Modules\Payment\DTOs;

use Spatie\LaravelData\Data;

class StoreReconciliationData extends Data
{
    public function __construct(
        public string $gateway,
        public string $settlement_date,
        public array $items,
    ) {}

    public static function defaults(): array
    {
        return [
            'gateway' => 'razerms',
        ];
    }

    public static function rules(): array
    {
        return [
            'gateway' => ['string'],
            'settlement_date' => ['required', 'date'],
            'items' => ['required', 'array'],
            'items.*.gateway_txn_id' => ['required', 'string'],
            'items.*.amount' => ['required', 'numeric'],
        ];
    }
}
