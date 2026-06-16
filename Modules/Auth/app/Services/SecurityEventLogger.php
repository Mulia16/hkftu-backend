<?php

namespace Modules\Auth\Services;

use Illuminate\Http\Request;
use Modules\Auth\Models\SecurityEvent;

class SecurityEventLogger
{
    public function __construct(private Request $request)
    {
    }

    public function record(
        string $eventType,
        string $severity = 'info',
        ?int $userId = null,
    ): SecurityEvent {
        return SecurityEvent::create([
            'user_id' => $userId,
            'event_type' => $eventType,
            'ip' => $this->request->ip(),
            'user_agent' => $this->request->userAgent(),
            'severity' => $severity,
        ]);
    }
}
