<?php

namespace Modules\Reporting\Models;

use Illuminate\Database\Eloquent\Model;

class ExportJob extends Model
{
    protected $table = 'reporting.export_jobs';

    protected $fillable = [
        'export_type',
        'parameters_json',
        'status',
        'file_path',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'parameters_json' => 'array',
        ];
    }
}
