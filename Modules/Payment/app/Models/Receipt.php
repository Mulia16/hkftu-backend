<?php

namespace Modules\Payment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Auth\Models\User;
use Modules\Enrolment\Models\Enrolment;

class Receipt extends Model
{
    protected $table = 'payment.receipts';

    protected $fillable = [
        'receipt_no',
        'payment_intent_id',
        'enrolment_id',
        'amount',
        'issued_at',
        'issued_by',
        'pdf_file_path',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'issued_at' => 'datetime',
        ];
    }

    public function paymentIntent(): BelongsTo
    {
        return $this->belongsTo(PaymentIntent::class, 'payment_intent_id');
    }

    public function enrolment(): BelongsTo
    {
        return $this->belongsTo(Enrolment::class, 'enrolment_id');
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
}
