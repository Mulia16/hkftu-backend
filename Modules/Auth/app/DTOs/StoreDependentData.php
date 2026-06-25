<?php

namespace Modules\Auth\DTOs;

use Spatie\LaravelData\Data;

class StoreDependentData extends Data
{
    public function __construct(
        public int $learner_profile_id,
        public ?string $relationship = null,
    ) {}

    public static function rules(): array
    {
        return [
            'learner_profile_id' => ['required', 'exists:auth.learner_profiles,id'],
            'relationship' => ['nullable', 'in:parent,guardian,other'],
        ];
    }
}
