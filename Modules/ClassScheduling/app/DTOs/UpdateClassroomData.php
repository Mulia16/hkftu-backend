<?php

namespace Modules\ClassScheduling\DTOs;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class UpdateClassroomData extends Data
{
    public function __construct(
        public string|Optional $code,
        public string|Optional $name,
        public int|Optional $capacity,
        public ?array $facilities_json = null,
        public ?string $status = null,
    ) {}

    public static function rules(): array
    {
        return [
            'code' => ['sometimes', 'string', 'max:20'],
            'name' => ['sometimes', 'string', 'max:255'],
            'capacity' => ['sometimes', 'integer', 'min:1'],
            'facilities_json' => ['nullable', 'array'],
            'status' => ['nullable', 'in:active,inactive'],
        ];
    }
}
