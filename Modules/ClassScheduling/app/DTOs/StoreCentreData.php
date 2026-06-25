<?php

namespace Modules\ClassScheduling\DTOs;

use Spatie\LaravelData\Data;

class StoreCentreData extends Data
{
    public function __construct(
        public string $code,
        public string $name,
        public string $district,
        public string $address,
        public ?string $phone = null,
        public ?array $opening_hours = null,
        public ?string $status = null,
    ) {}

    public static function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:20', 'unique:class_scheduling.centres,code'],
            'name' => ['required', 'string', 'max:255'],
            'district' => ['required', 'string', 'max:100'],
            'address' => ['required', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:20'],
            'opening_hours' => ['nullable', 'array'],
            'status' => ['nullable', 'in:active,inactive'],
        ];
    }
}
