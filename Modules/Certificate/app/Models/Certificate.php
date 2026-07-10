<?php

namespace Modules\Certificate\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Auth\Models\User;
use Modules\Enrolment\Models\Enrolment;

class Certificate extends Model
{
    protected $table = 'certificate.certificates';

    protected $fillable = [
        'certificate_no',
        'enrolment_id',
        'template_id',
        'issued_at',
        'issued_by',
        'pdf_file_path',
        'status',
        'reprint_reason',
        'reprint_fee',
        'learner_reprint_reason',
        'reprinted_by',
        'reprinted_at',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'datetime',
            'reprinted_at' => 'datetime',
        ];
    }

    public function enrolment(): BelongsTo
    {
        return $this->belongsTo(Enrolment::class, 'enrolment_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(CertificateTemplate::class, 'template_id');
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function reprinter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reprinted_by');
    }
}
