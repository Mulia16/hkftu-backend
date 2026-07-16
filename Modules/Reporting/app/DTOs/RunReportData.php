<?php

namespace Modules\Reporting\DTOs;

use Spatie\LaravelData\Data;

class RunReportData extends Data
{
    public function __construct(
        public int $template_id,
        public ?array $parameters = null,
    ) {}

    public static function rules(): array
    {
        return [
            'template_id' => ['required', 'integer'],
            'parameters' => ['nullable', 'array'],
        ];
    }
}
