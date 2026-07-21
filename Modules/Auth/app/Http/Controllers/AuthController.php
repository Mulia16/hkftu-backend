<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiError;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\Auth\DTOs\AuthenticatedUserData;
use Modules\Auth\DTOs\LoginRequestData;
use Modules\Auth\DTOs\RegisterRequestData;
use Modules\Auth\DTOs\UpdateUserProfileData;
use Modules\Auth\Models\LearnerProfile;
use Modules\Auth\Models\User;
use Modules\Auth\Services\AuditLogger;
use Modules\Auth\Services\SecurityEventLogger;
use Modules\Notification\Services\NotificationService;

class AuthController extends Controller
{
    public function __construct(
        private SecurityEventLogger $securityEvents,
        private AuditLogger $auditLogger,
        private NotificationService $notifications,
    ) {}

    public function register(RegisterRequestData $data)
    {
        $existingUser = User::where('email', $data->email)->first();

        if ($existingUser) {
            return ApiError::respond(
                code: 'EMAIL_EXISTS',
                message: 'An account with this email already exists.',
                status: 422,
                fieldErrors: ['email' => ['This email is already registered.']],
            );
        }

        $user = User::create([
            'name' => $data->name,
            'email' => $data->email,
            'phone' => $data->phone,
            'password' => $data->password,
            'status' => 'active',
        ]);

        $user->assignRole('public_learner');

        LearnerProfile::create([
            'user_id' => $user->id,
            'name_en' => $data->name,
            'phone' => $data->phone,
            'email' => $data->email,
            'membership_status' => 'none',
            'status' => 'active',
        ]);

        $token = $user->createToken('api')->plainTextToken;

        $this->securityEvents->record('register_success', 'info', $user->id);
        $this->auditLogger->record(
            action: 'auth.register',
            resourceType: 'user',
            resourceId: $user->id,
            actorUserId: $user->id,
            after: ['name' => $user->name, 'email' => $user->email],
        );

        $responseData = AuthenticatedUserData::fromUser($user, $token)->toArray();

        return response()->json(['data' => $responseData], 201);
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

        $responseData = AuthenticatedUserData::fromUser($user, $token)->toArray();
        $responseData['mfa_required'] = (bool) $user->mfa_enabled;

        return response()->json([
            'data' => $responseData,
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

        $resetUrl = rtrim(config('app.frontend_url'), '/').'/reset-password?'.http_build_query([
            'token' => $token,
            'email' => $user->email,
        ]);

        $this->notifications->send(
            channel: 'email',
            recipient: $user->email,
            subject: 'Password Reset',
            body: "We received a request to reset your password. "
                ."Click the link below to choose a new password. This link expires in 60 minutes.\n\n"
                .$resetUrl."\n\n"
                .'If you did not request this, you can safely ignore this email.',
            relatedType: 'user',
            relatedId: $user->id,
        );

        $this->securityEvents->record('password_reset_requested', 'info', $user->id);

        return response()->json(['data' => [
            'message' => 'If the email exists, a reset link has been sent.',
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
