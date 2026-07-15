<?php

namespace Modules\InstructorFinance\DTOs;

use Spatie\LaravelData\Data;

class CalculateFeeData extends Data
{
    public function __construct(
        public int $season_id,
        public ?int $centre_id = null,
        public ?int $instructor_id = null,
    ) {}

    public static function rules(): array
    {
        return [
            'season_id' => ['required', 'integer'],
            'centre_id' => ['nullable', 'integer'],
            'instructor_id' => ['nullable', 'integer'],
        ];
    }
}
