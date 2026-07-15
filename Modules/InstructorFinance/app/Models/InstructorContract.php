<?php

namespace Modules\InstructorFinance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Auth\Models\User;
use Modules\ClassScheduling\Models\CourseClass;

class InstructorContract extends Model
{
    protected $table = 'instructor_finance.instructor_contracts';

    protected $fillable = [
        'class_id',
        'instructor_id',
        'template_id',
        'file_path',
        'status',
        'signed_at',
    ];

    protected function casts(): array
    {
        return [
            'signed_at' => 'datetime',
        ];
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(CourseClass::class, 'class_id');
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }
}
