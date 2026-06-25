<?php

namespace Modules\CourseCatalogue\DTOs;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class UpdateCategoryData extends Data
{
    public function __construct(
        public string|Optional $code,
        public string|Optional $name_en,
        public string|Optional $name_zh,
        public ?int $parent_id = null,
        public ?int $sort_order = null,
    ) {}

    public static function rules(): array
    {
        return [
            'parent_id' => ['nullable', 'exists:course_catalogue.categories,id'],
            'code' => ['sometimes', 'string', 'max:50', 'unique:course_catalogue.categories,code'],
            'name_en' => ['sometimes', 'string', 'max:255'],
            'name_zh' => ['sometimes', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
