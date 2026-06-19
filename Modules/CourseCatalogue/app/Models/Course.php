<?php

namespace Modules\CourseCatalogue\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    protected $table = 'course_catalogue.courses';

    protected $fillable = [
        'season_id',
        'subject_id',
        'course_code',
        'page_no',
        'status',
        'publish_at',
    ];

    protected function casts(): array
    {
        return [
            'publish_at' => 'datetime',
        ];
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function classes(): HasMany
    {
        return $this->hasMany(\Modules\ClassScheduling\Models\CourseClass::class, 'course_id');
    }
}
