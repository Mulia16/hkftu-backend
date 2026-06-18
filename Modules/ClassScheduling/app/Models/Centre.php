<?php

namespace Modules\ClassScheduling\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Centre extends Model
{
    protected $table = 'class_scheduling.centres';

    protected $fillable = [
        'code',
        'name',
        'district',
        'address',
        'phone',
        'opening_hours',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'opening_hours' => 'array',
        ];
    }

    public function classrooms(): HasMany
    {
        return $this->hasMany(Classroom::class, 'centre_id');
    }
}
