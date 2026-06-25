<?php

namespace Modules\Auth\DTOs;

use Modules\Auth\Models\User;
use Spatie\LaravelData\Data;

class AuthenticatedUserData extends Data
{
    public function __construct(
        public string $publicId,
        public string $name,
        public string $email,
        /** @var string[] */
        public array $roles,
        /** @var string[] */
        public array $permissions,
        public ?string $token = null,
    ) {}

    public static function fromUser(User $user, ?string $token = null): self
    {
        return new self(
            publicId: $user->public_id,
            name: $user->name,
            email: $user->email,
            roles: $user->getRoleNames()->all(),
            permissions: $user->getAllPermissions()->pluck('name')->all(),
            token: $token,
        );
    }
}
