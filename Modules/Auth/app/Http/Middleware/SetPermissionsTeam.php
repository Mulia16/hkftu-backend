<?php

namespace Modules\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Auth\Models\StaffProfile;

/**
 * Spatie's "teams" feature requires a team id to be set before any role/permission
 * query. Centre id 0 is the sentinel for system-wide roles (system_admin, ai_operations)
 * that are not bound to a single centre.
 */
class SetPermissionsTeam
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        $centreId = $user
            ? StaffProfile::where('user_id', $user->id)->value('centre_id') ?? 0
            : 0;

        setPermissionsTeamId($centreId);

        return $next($request);
    }
}
