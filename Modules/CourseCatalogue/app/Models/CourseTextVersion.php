<?php

namespace Modules\CourseCatalogue\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Auth\Models\User;
use Modules\CourseCatalogue\Enums\CourseTextStatus;

class CourseTextVersion extends Model
{
    protected $table = 'course_catalogue.course_text_versions';

    protected $fillable = [
        'subject_id',
        'version_no',
        'content_html',
        'status',
        'approved_by',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => CourseTextStatus::class,
            'published_at' => 'datetime',
        ];
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
