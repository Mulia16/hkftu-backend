<?php

namespace Modules\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataAccessLog extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'auth.data_access_logs';

    protected $fillable = [
        'actor_user_id',
        'resource_type',
        'resource_id',
        'action',
        'ip',
        'user_agent',
    ];

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
