<?php

namespace Modules\ClassScheduling\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Classroom extends Model
{
    protected $table = 'class_scheduling.classrooms';

    protected $fillable = [
        'centre_id',
        'code',
        'name',
        'capacity',
        'facilities_json',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'facilities_json' => 'array',
            'capacity' => 'integer',
        ];
    }

    public function centre(): BelongsTo
    {
        return $this->belongsTo(Centre::class, 'centre_id');
    }
}
