<?php

namespace Modules\InstructorFinance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\CourseCatalogue\Models\Course;
use Modules\CourseCatalogue\Models\Subject;

class InstructorFeeRule extends Model
{
    protected $table = 'instructor_finance.instructor_fee_rules';

    protected $fillable = [
        'subject_id',
        'course_id',
        'rate_type',
        'amount',
        'effective_from',
        'rules_json',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'effective_from' => 'date',
            'rules_json' => 'array',
        ];
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }
}
