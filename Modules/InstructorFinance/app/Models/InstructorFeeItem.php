<?php

namespace Modules\InstructorFinance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Auth\Models\User;
use Modules\ClassScheduling\Models\CourseClass;

class InstructorFeeItem extends Model
{
    protected $table = 'instructor_finance.instructor_fee_items';

    protected $fillable = [
        'class_id',
        'instructor_id',
        'fee_rule_id',
        'amount',
        'adjustment',
        'status',
        'calculated_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'adjustment' => 'decimal:2',
            'calculated_at' => 'datetime',
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

    public function feeRule(): BelongsTo
    {
        return $this->belongsTo(InstructorFeeRule::class, 'fee_rule_id');
    }
}
