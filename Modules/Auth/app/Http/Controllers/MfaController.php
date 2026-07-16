<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiError;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PragmaRX\Google2FA\Google2FA;

class MfaController extends Controller
{
    public function enable(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->mfa_enabled) {
            return ApiError::respond('MFA_ALREADY_ENABLED', 'MFA is already enabled.', 422);
        }

        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();
        $qrCodeUrl = $google2fa->getQRCodeUrl('HKFTU', $user->email, $secret);

        $user->update([
            'mfa_secret' => encrypt($secret),
        ]);

        return response()->json([
            'data' => [
                'secret' => $secret,
                'qr_code_url' => $qrCodeUrl,
                'message' => 'Scan QR code with authenticator app, then verify with POST /auth/mfa/verify',
            ],
        ]);
    }

    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = $request->user();

        if (!$user->mfa_secret) {
            return ApiError::respond('MFA_NOT_SETUP', 'MFA has not been set up. Call enable first.', 422);
        }

        $google2fa = new Google2FA();
        $secret = decrypt($user->mfa_secret);
        $valid = $google2fa->verifyKey($secret, $request->input('code'));

        if (!$valid) {
            return ApiError::respond('MFA_INVALID_CODE', 'Invalid MFA code.', 422);
        }

        if (!$user->mfa_enabled) {
            $user->update(['mfa_enabled' => true]);
        }

        return response()->json([
            'data' => [
                'mfa_enabled' => true,
                'message' => 'MFA verified and enabled successfully.',
            ],
        ]);
    }

    public function disable(Request $request): JsonResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = $request->user();

        if (!$user->mfa_enabled) {
            return ApiError::respond('MFA_NOT_ENABLED', 'MFA is not enabled.', 422);
        }

        $google2fa = new Google2FA();
        $secret = decrypt($user->mfa_secret);
        $valid = $google2fa->verifyKey($secret, $request->input('code'));

        if (!$valid) {
            return ApiError::respond('MFA_INVALID_CODE', 'Invalid MFA code.', 422);
        }

        $user->update([
            'mfa_enabled' => false,
            'mfa_secret' => null,
        ]);

        return response()->json([
            'data' => [
                'mfa_enabled' => false,
                'message' => 'MFA disabled successfully.',
            ],
        ]);
    }
}
