<?php

namespace Modules\Enrolment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Auth\Models\User;
use Modules\ClassScheduling\Models\CourseClass;

class Transfer extends Model
{
    protected $table = 'enrolment.transfers';

    protected $fillable = [
        'old_enrolment_id',
        'new_class_id',
        'new_enrolment_id',
        'price_difference',
        'status',
        'reason',
        'requested_by',
        'approved_by',
        'rejection_reason',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'price_difference' => 'decimal:2',
            'completed_at' => 'datetime',
        ];
    }

    public function oldEnrolment(): BelongsTo
    {
        return $this->belongsTo(Enrolment::class, 'old_enrolment_id');
    }

    public function newEnrolment(): BelongsTo
    {
        return $this->belongsTo(Enrolment::class, 'new_enrolment_id');
    }

    public function newClass(): BelongsTo
    {
        return $this->belongsTo(CourseClass::class, 'new_class_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
