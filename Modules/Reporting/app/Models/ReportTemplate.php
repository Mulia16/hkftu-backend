<?php

namespace Modules\Reporting\Models;

use Illuminate\Database\Eloquent\Model;

class ReportTemplate extends Model
{
    protected $table = 'reporting.report_templates';

    protected $fillable = [
        'code',
        'name',
        'group',
        'description',
        'format',
        'query_key',
        'parameters_json',
    ];

    protected function casts(): array
    {
        return [
            'parameters_json' => 'array',
        ];
    }
}
