<?php

namespace Modules\Auth\Http\Middleware;

use App\Support\ApiError;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireMfa
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return ApiError::respond('UNAUTHENTICATED', 'Authentication required.', 401);
        }

        if ($user->mfa_enabled && ! $this->mfaVerified($request)) {
            return ApiError::respond(
                'MFA_REQUIRED',
                'Multi-factor authentication is required for this action.',
                403,
            );
        }

        return $next($request);
    }

    private function mfaVerified(Request $request): bool
    {
        return (bool) $request->header('X-MFA-Verified', false);
    }
}
