<?php

namespace Modules\ClassScheduling\DTOs;

use Spatie\LaravelData\Data;

class StoreClassroomData extends Data
{
    public function __construct(
        public string $code,
        public string $name,
        public int $capacity,
        public ?array $facilities_json = null,
        public ?string $status = null,
    ) {}

    public static function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:20'],
            'name' => ['required', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'min:1'],
            'facilities_json' => ['nullable', 'array'],
            'status' => ['nullable', 'in:active,inactive'],
        ];
    }
}
