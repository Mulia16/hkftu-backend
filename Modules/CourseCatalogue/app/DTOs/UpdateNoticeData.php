<?php

namespace Modules\CourseCatalogue\DTOs;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class UpdateNoticeData extends Data
{
    public function __construct(
        public string|Optional $title,
        public string|Optional $content,
        public ?string $type = null,
        public ?bool $is_active = null,
        public ?string $published_at = null,
    ) {}

    public static function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'content' => ['sometimes', 'string'],
            'type' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
        ];
    }
}
