<?php

namespace Modules\Notification\Services;

use Illuminate\Database\Eloquent\Builder;
use Modules\Notification\Models\NotificationLog;

class NotificationService
{
    public function send(
        string $channel,
        string $recipient,
        string $subject,
        string $body,
        ?string $relatedType = null,
        ?int $relatedId = null,
    ): NotificationLog {
        return NotificationLog::create([
            'channel' => $channel,
            'recipient' => $recipient,
            'subject' => $subject,
            'body' => $body,
            'status' => 'sent',
            'related_type' => $relatedType,
            'related_id' => $relatedId,
            'sent_at' => now(),
            'created_at' => now(),
        ]);
    }

    public function getLogs(?string $channel = null, ?string $status = null): Builder
    {
        return NotificationLog::query()
            ->when($channel, fn ($q) => $q->where('channel', $channel))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderByDesc('created_at');
    }
}
