<?php

namespace Modules\Payment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Auth\Models\User;

class ReconciliationBatch extends Model
{
    protected $table = 'payment.reconciliation_batches';

    protected $fillable = [
        'gateway',
        'settlement_date',
        'file_path',
        'status',
        'total_amount',
        'matched_amount',
        'unmatched_amount',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'settlement_date' => 'date',
            'total_amount' => 'decimal:2',
            'matched_amount' => 'decimal:2',
            'unmatched_amount' => 'decimal:2',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReconciliationItem::class, 'batch_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
