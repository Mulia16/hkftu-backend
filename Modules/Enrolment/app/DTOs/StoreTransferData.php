<?php

namespace Modules\Enrolment\DTOs;

use Spatie\LaravelData\Data;

class StoreTransferData extends Data
{
    public function __construct(
        public int $enrolment_id,
        public int $new_class_id,
        public ?string $reason = null,
    ) {}

    public static function rules(): array
    {
        return [
            'enrolment_id' => ['required', 'integer'],
            'new_class_id' => ['required', 'integer'],
            'reason' => ['nullable', 'string', 'max:500'],
        ];
    }
}
