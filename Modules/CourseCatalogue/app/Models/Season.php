<?php

namespace Modules\CourseCatalogue\Models;

use Illuminate\Database\Eloquent\Model;

class Season extends Model
{
    protected $table = 'course_catalogue.seasons';

    protected $fillable = [
        'code',
        'name',
        'start_date',
        'end_date',
        'member_registration_start',
        'public_registration_start',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'member_registration_start' => 'datetime',
            'public_registration_start' => 'datetime',
        ];
    }
}
