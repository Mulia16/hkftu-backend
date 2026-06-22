<?php

namespace Modules\ClassScheduling\Models;

use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $table = 'class_scheduling.holidays';

    protected $fillable = ['date', 'name', 'type'];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }
}
