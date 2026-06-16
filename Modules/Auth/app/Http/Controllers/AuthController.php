<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiError;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Modules\Auth\DTOs\AuthenticatedUserData;
use Modules\Auth\DTOs\LoginRequestData;
use Modules\Auth\Models\User;
use Modules\Auth\Services\AuditLogger;
use Modules\Auth\Services\SecurityEventLogger;

class AuthController extends Controller
{
    public function __construct(
        private SecurityEventLogger $securityEvents,
        private AuditLogger $auditLogger,
    ) {
    }

    public function login(LoginRequestData $data)
    {
        $user = User::where('email', $data->email)->first();

        if (! $user || ! Hash::check($data->password, $user->password)) {
            $this->securityEvents->record('login_failed', 'warning', $user?->id);

            return ApiError::respond(
                code: 'INVALID_CREDENTIALS',
                message: 'Email or password is incorrect.',
                status: 401,
            );
        }

        $user->forceFill(['last_login_at' => now()])->save();

        $token = $user->createToken('api')->plainTextToken;

        $this->securityEvents->record('login_success', 'info', $user->id);

        return response()->json([
            'data' => AuthenticatedUserData::fromUser($user, $token),
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        $user->currentAccessToken()->delete();

        $this->auditLogger->record(
            action: 'auth.logout',
            resourceType: 'user',
            resourceId: $user->id,
            actorUserId: $user->id,
        );

        return response()->json(['data' => ['message' => 'Logged out.']]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'data' => AuthenticatedUserData::fromUser($request->user()),
        ]);
    }
}
