<?php

namespace Modules\Certificate\DTOs;

use Spatie\LaravelData\Data;

class IssueCertificateData extends Data
{
    public function __construct(
        public int $class_id,
        public int $template_id,
        public bool $override = false,
        public ?string $override_reason = null,
    ) {}

    public static function rules(): array
    {
        return [
            'class_id' => ['required', 'integer'],
            'template_id' => ['required', 'integer'],
            'override' => ['sometimes', 'boolean'],
            'override_reason' => ['nullable', 'string', 'max:500'],
        ];
    }
}
