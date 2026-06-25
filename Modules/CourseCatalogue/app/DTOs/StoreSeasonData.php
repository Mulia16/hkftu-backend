<?php

namespace Modules\CourseCatalogue\DTOs;

use Spatie\LaravelData\Data;

class StoreSeasonData extends Data
{
    public function __construct(
        public string $code,
        public string $name,
        public string $start_date,
        public string $end_date,
        public ?string $member_registration_start = null,
        public ?string $public_registration_start = null,
    ) {}

    public static function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:20', 'unique:course_catalogue.seasons,code'],
            'name' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'member_registration_start' => ['nullable', 'date'],
            'public_registration_start' => ['nullable', 'date'],
        ];
    }
}
