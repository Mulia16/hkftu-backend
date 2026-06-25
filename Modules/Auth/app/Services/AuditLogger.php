<?php

namespace Modules\Auth\Services;

use Illuminate\Http\Request;
use Modules\Auth\Models\AuditLog;

class AuditLogger
{
    public function __construct(private Request $request) {}

    public function record(
        string $action,
        string $resourceType,
        string|int|null $resourceId = null,
        ?array $before = null,
        ?array $after = null,
        ?int $actorUserId = null,
    ): AuditLog {
        return AuditLog::create([
            'actor_user_id' => $actorUserId ?? $this->request->user()?->id,
            'action' => $action,
            'resource_type' => $resourceType,
            'resource_id' => $resourceId !== null ? (string) $resourceId : null,
            'before_json' => $before,
            'after_json' => $after,
            'ip' => $this->request->ip(),
        ]);
    }
}
