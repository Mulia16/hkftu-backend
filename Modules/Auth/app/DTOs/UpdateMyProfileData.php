<?php

namespace Modules\Auth\DTOs;

use Spatie\LaravelData\Data;

class UpdateMyProfileData extends Data
{
    public function __construct(
        public ?string $phone = null,
        public ?string $email = null,
        public ?string $address = null,
        public ?string $emergency_contact_name = null,
        public ?string $emergency_contact_phone = null,
    ) {}

    public static function rules(): array
    {
        return [
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:30'],
        ];
    }
}
