<?php

namespace Modules\CourseCatalogue\DTOs;

use Spatie\LaravelData\Data;

class StoreSubjectData extends Data
{
    public function __construct(
        public string $name,
        public float $tuition_fee,
        public float $total_hours,
        public float $lesson_hours,
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
            'name' => ['required', 'string', 'max:255'],
            'tuition_fee' => ['required', 'numeric', 'min:0'],
            'material_fee' => ['nullable', 'numeric', 'min:0'],
            'instructor_fee_default' => ['nullable', 'numeric', 'min:0'],
            'total_hours' => ['required', 'numeric', 'min:0.5'],
            'lesson_hours' => ['required', 'numeric', 'min:0.5'],
            'prerequisites' => ['nullable', 'array'],
            'prerequisites.*' => ['string', 'max:100'],
            'certificate_eligible' => ['nullable', 'boolean'],
            'status' => ['nullable', 'in:draft,active,inactive'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['exists:course_catalogue.categories,id'],
        ];
    }
}
