<?php

namespace Modules\Auth\DTOs;

use Spatie\LaravelData\Data;

class LoginRequestData extends Data
{
    public function __construct(
        public string $email,
        public string $password,
    ) {
    }

    public static function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }
}
