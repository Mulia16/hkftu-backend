<?php

namespace Modules\CourseCatalogue\DTOs;

use Spatie\LaravelData\Data;

class StoreCategoryData extends Data
{
    public function __construct(
        public string $name_en,
        public string $name_zh,
        public ?string $code = null,
        public ?int $parent_id = null,
        public ?int $sort_order = null,
    ) {}

    public static function rules(): array
    {
        return [
            'parent_id' => ['nullable', 'exists:course_catalogue.categories,id'],
            'code' => ['nullable', 'string', 'max:50', 'unique:course_catalogue.categories,code'],
            'name_en' => ['required', 'string', 'max:255'],
            'name_zh' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
