<?php

namespace Modules\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsentRecord extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'auth.consent_records';

    protected $fillable = [
        'user_id',
        'consent_type',
        'granted',
        'ip',
        'user_agent',
        'revoke_reason',
    ];

    protected function casts(): array
    {
        return [
            'granted' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
