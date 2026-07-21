<?php

namespace Modules\Certificate\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Auth\Models\User;

class CertificateReprintRequest extends Model
{
    protected $table = 'certificate.certificate_reprint_requests';

    protected $fillable = [
        'certificate_id',
        'requested_by',
        'reason',
        'status',
        'requested_at',
        'processed_by',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'requested_at' => 'datetime',
            'processed_at' => 'datetime',
        ];
    }

    public function certificate(): BelongsTo
    {
        return $this->belongsTo(Certificate::class, 'certificate_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
