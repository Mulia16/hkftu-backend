<?php

namespace Modules\CourseCatalogue\DTOs;

use Spatie\LaravelData\Data;

class StoreNoticeData extends Data
{
    public function __construct(
        public string $title,
        public string $content,
        public ?string $type = null,
        public ?bool $is_active = null,
        public ?string $published_at = null,
    ) {}

    public static function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'type' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
        ];
    }
}
