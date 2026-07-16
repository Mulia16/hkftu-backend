<?php

namespace Modules\Auth\DTOs;

use Spatie\LaravelData\Data;

class RegisterRequestData extends Data
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public ?string $phone,
    ) {}

    public static function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
            'phone' => ['nullable', 'string', 'max:30'],
        ];
    }
}
