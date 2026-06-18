<?php

namespace Modules\CourseCatalogue\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends Model
{
    use SoftDeletes;

    protected $table = 'course_catalogue.subjects';

    protected $fillable = [
        'subject_code',
        'name',
        'tuition_fee',
        'material_fee',
        'instructor_fee_default',
        'total_hours',
        'lesson_hours',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'tuition_fee' => 'decimal:2',
            'material_fee' => 'decimal:2',
            'instructor_fee_default' => 'decimal:2',
            'total_hours' => 'decimal:2',
            'lesson_hours' => 'decimal:2',
        ];
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            Category::class,
            'course_catalogue.subject_categories',
            'subject_id',
            'category_id'
        );
    }
}
