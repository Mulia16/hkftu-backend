<?php

namespace Modules\ClassScheduling\DTOs;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class UpdateCentreData extends Data
{
    public function __construct(
        public string|Optional $code,
        public string|Optional $name,
        public string|Optional $district,
        public string|Optional $address,
        public ?string $phone = null,
        public ?array $opening_hours = null,
        public ?string $status = null,
    ) {}

    public static function rules(): array
    {
        return [
            'code' => ['sometimes', 'string', 'max:20', 'unique:class_scheduling.centres,code'],
            'name' => ['sometimes', 'string', 'max:255'],
            'district' => ['sometimes', 'string', 'max:100'],
            'address' => ['sometimes', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:20'],
            'opening_hours' => ['nullable', 'array'],
            'status' => ['nullable', 'in:active,inactive'],
        ];
    }
}
