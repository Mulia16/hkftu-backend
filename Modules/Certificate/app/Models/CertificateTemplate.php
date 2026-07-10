<?php

namespace Modules\Certificate\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CertificateTemplate extends Model
{
    protected $table = 'certificate.certificate_templates';

    protected $fillable = [
        'code',
        'name',
        'file_path',
        'variables_json',
        'status',
        'version_no',
    ];

    protected function casts(): array
    {
        return [
            'variables_json' => 'array',
            'version_no' => 'integer',
        ];
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class, 'template_id');
    }
}
