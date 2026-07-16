<?php

namespace Modules\Notification\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    public $timestamps = false;

    protected $table = 'public.notification_logs';

    protected $fillable = [
        'channel',
        'recipient',
        'subject',
        'body',
        'status',
        'error_message',
        'related_type',
        'related_id',
        'sent_at',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }
}
