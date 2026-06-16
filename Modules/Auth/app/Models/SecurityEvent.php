<?php

namespace Modules\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecurityEvent extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'auth.security_events';

    protected $fillable = [
        'user_id',
        'event_type',
        'ip',
        'user_agent',
        'severity',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
