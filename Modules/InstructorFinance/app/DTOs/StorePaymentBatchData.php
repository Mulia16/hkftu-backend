<?php

namespace Modules\InstructorFinance\DTOs;

use Spatie\LaravelData\Data;

class StorePaymentBatchData extends Data
{
    public function __construct(
        public ?int $season_id = null,
        public ?int $centre_id = null,
        public array $fee_item_ids = [],
    ) {}

    public static function rules(): array
    {
        return [
            'season_id' => ['nullable', 'integer'],
            'centre_id' => ['nullable', 'integer'],
            'fee_item_ids' => ['required', 'array', 'min:1'],
            'fee_item_ids.*' => ['integer'],
        ];
    }
}
