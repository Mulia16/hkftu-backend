<?php

namespace Modules\Auth\DTOs;

use Spatie\LaravelData\Data;

class StoreDependentData extends Data
{
    public function __construct(
        public ?int $learner_profile_id = null,
        public ?string $name_en = null,
        public ?string $name_zh = null,
        public ?string $relationship = null,
    ) {}

    public static function rules(): array
    {
        return [
            'learner_profile_id' => ['nullable', 'integer'],
            'name_en' => ['required_without:learner_profile_id', 'string', 'max:255'],
            'name_zh' => ['nullable', 'string', 'max:255'],
            'relationship' => ['nullable', 'in:parent,guardian,other'],
        ];
    }
}
