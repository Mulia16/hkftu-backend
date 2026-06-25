<?php

namespace Modules\CourseCatalogue\DTOs;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class UpdateSeasonData extends Data
{
    public function __construct(
        public string|Optional $code,
        public string|Optional $name,
        public string|Optional $start_date,
        public string|Optional $end_date,
        public ?string $member_registration_start = null,
        public ?string $public_registration_start = null,
    ) {}

    public static function rules(): array
    {
        return [
            'code' => ['sometimes', 'string', 'max:20', 'unique:course_catalogue.seasons,code'],
            'name' => ['sometimes', 'string', 'max:255'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date', 'after:start_date'],
            'member_registration_start' => ['nullable', 'date'],
            'public_registration_start' => ['nullable', 'date'],
        ];
    }
}
