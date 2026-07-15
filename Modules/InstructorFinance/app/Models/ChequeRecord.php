<?php

namespace Modules\InstructorFinance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Auth\Models\User;

class ChequeRecord extends Model
{
    protected $table = 'instructor_finance.cheque_records';

    protected $fillable = [
        'payment_batch_id',
        'instructor_id',
        'cheque_no',
        'payee',
        'amount',
        'printed_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'printed_at' => 'datetime',
        ];
    }

    public function paymentBatch(): BelongsTo
    {
        return $this->belongsTo(InstructorPaymentBatch::class, 'payment_batch_id');
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }
}
