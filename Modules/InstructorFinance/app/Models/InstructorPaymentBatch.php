<?php

namespace Modules\InstructorFinance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Auth\Models\User;
use Modules\ClassScheduling\Models\Centre;
use Modules\CourseCatalogue\Models\Season;

class InstructorPaymentBatch extends Model
{
    protected $table = 'instructor_finance.instructor_payment_batches';

    protected $fillable = [
        'season_id',
        'centre_id',
        'total_amount',
        'status',
        'approved_by',
        'payment_date',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'payment_date' => 'date',
        ];
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class, 'season_id');
    }

    public function centre(): BelongsTo
    {
        return $this->belongsTo(Centre::class, 'centre_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function cheques(): HasMany
    {
        return $this->hasMany(ChequeRecord::class, 'payment_batch_id');
    }
}
