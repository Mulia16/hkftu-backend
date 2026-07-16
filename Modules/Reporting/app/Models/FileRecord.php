<?php

namespace Modules\Reporting\Models;

use Illuminate\Database\Eloquent\Model;

class FileRecord extends Model
{
    public $timestamps = false;

    protected $table = 'reporting.files';

    protected $fillable = [
        'storage_key',
        'filename',
        'mime_type',
        'size',
        'checksum',
        'created_at',
    ];
}
