<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiError;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\Auth\DTOs\AuthenticatedUserData;
use Modules\Auth\DTOs\LoginRequestData;
use Modules\Auth\DTOs\UpdateUserProfileData;
use Modules\Auth\Models\User;
use Modules\Auth\Services\AuditLogger;
use Modules\Auth\Services\SecurityEventLogger;

class AuthController extends Controller
{
    public function __construct(
        private SecurityEventLogger $securityEvents,
        private AuditLogger $auditLogger,
    ) {}

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

    public function updateProfile(UpdateUserProfileData $data)
    {
        $user = request()->user();

        $before = $user->only(array_keys($data->toArray()));
        $user->update($data->toArray());

        $this->auditLogger->record(
            action: 'user.profile_update',
            resourceType: 'user',
            resourceId: $user->id,
            before: $before,
            after: $data->toArray(),
        );

        return response()->json([
            'data' => AuthenticatedUserData::fromUser($user),
        ]);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json(['data' => ['message' => 'If the email exists, a reset link has been sent.']]);
        }

        $token = Str::random(64);

        \DB::table('auth.password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            ['token' => Hash::make($token), 'created_at' => now()],
        );

        $this->securityEvents->record('password_reset_requested', 'info', $user->id);

        return response()->json(['data' => [
            'message' => 'If the email exists, a reset link has been sent.',
            'token' => $token,
        ]]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $record = \DB::table('auth.password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (! $record || ! Hash::check($request->token, $record->token)) {
            return ApiError::respond('INVALID_TOKEN', 'Invalid or expired reset token.', 422);
        }

        if (now()->diffInMinutes($record->created_at) > 60) {
            \DB::table('auth.password_reset_tokens')->where('email', $request->email)->delete();

            return ApiError::respond('TOKEN_EXPIRED', 'Reset token has expired.', 422);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $user->update(['password' => Hash::make($request->password)]);

        \DB::table('auth.password_reset_tokens')->where('email', $request->email)->delete();

        $this->securityEvents->record('password_reset_completed', 'info', $user->id);

        return response()->json(['data' => ['message' => 'Password has been reset.']]);
    }
}
