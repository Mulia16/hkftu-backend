<?php

namespace Modules\CourseCatalogue\DTOs;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class UpdateSubjectData extends Data
{
    public function __construct(
        public string|Optional $name,
        public float|Optional $tuition_fee,
        public float|Optional $total_hours,
        public float|Optional $lesson_hours,
        public ?float $material_fee = null,
        public ?float $instructor_fee_default = null,
        public ?array $prerequisites = null,
        public ?bool $certificate_eligible = null,
        public ?string $status = null,
        public ?array $category_ids = null,
    ) {}

    public static function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'tuition_fee' => ['sometimes', 'numeric', 'min:0'],
            'material_fee' => ['nullable', 'numeric', 'min:0'],
            'instructor_fee_default' => ['nullable', 'numeric', 'min:0'],
            'total_hours' => ['sometimes', 'numeric', 'min:0.5'],
            'lesson_hours' => ['sometimes', 'numeric', 'min:0.5'],
            'prerequisites' => ['nullable', 'array'],
            'prerequisites.*' => ['string', 'max:100'],
            'certificate_eligible' => ['nullable', 'boolean'],
            'status' => ['nullable', 'in:draft,active,inactive'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['exists:course_catalogue.categories,id'],
        ];
    }
}
