<?php

namespace Modules\Auth\DTOs;

use Spatie\LaravelData\Data;

class UpdateUserProfileData extends Data
{
    public function __construct(
        public ?string $name = null,
        public ?string $phone = null,
    ) {}

    public static function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
        ];
    }
}
