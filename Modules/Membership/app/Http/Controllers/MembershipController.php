<?php

namespace Modules\Membership\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Auth\Models\LearnerProfile;
use Modules\Auth\Models\MemberPricingEligibility;
use Modules\Auth\Models\MemberStatusSnapshot;
use Modules\Auth\Models\MemberVerification;
use Modules\Auth\Services\AuditLogger;

class MembershipController extends Controller
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'learner_profile_id' => 'required|exists:auth.learner_profiles,id',
            'membership_no' => 'nullable|string|max:50',
        ]);

        $profile = LearnerProfile::findOrFail($request->learner_profile_id);
        $membershipNo = $request->membership_no ?? $profile->membership_no;

        $verification = MemberVerification::create([
            'learner_profile_id' => $profile->id,
            'membership_no' => $membershipNo,
            'status' => 'pending',
            'source' => 'mock',
            'request_data' => ['membership_no' => $membershipNo],
        ]);

        $mockResult = $this->mockVerify($membershipNo);

        $verification->update([
            'status' => $mockResult['status'] === 'active' ? 'verified' : 'failed',
            'response_data' => $mockResult,
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
            'failure_reason' => $mockResult['status'] !== 'active' ? $mockResult['message'] : null,
        ]);

        $snapshot = MemberStatusSnapshot::create([
            'learner_profile_id' => $profile->id,
            'membership_no' => $membershipNo,
            'status' => $mockResult['status'],
            'source' => 'mock',
            'raw_response' => $mockResult,
            'verified_by' => $request->user()->id,
        ]);

        $profile->update([
            'membership_no' => $membershipNo,
            'membership_status' => $mockResult['status'],
            'membership_verified_at' => now(),
        ]);

        if ($mockResult['status'] === 'active') {
            $this->updatePricingEligibility($profile, $verification, $mockResult);
        }

        $this->auditLogger->record('membership.verify', 'learner_profile', $profile->id, after: [
            'membership_no' => $membershipNo,
            'status' => $mockResult['status'],
        ]);

        return response()->json(['data' => [
            'verification' => $verification,
            'snapshot' => $snapshot,
            'profile' => $profile->fresh(),
        ]]);
    }

    public function snapshots(Request $request, int $learnerProfileId): JsonResponse
    {
        $snapshots = MemberStatusSnapshot::where('learner_profile_id', $learnerProfileId)
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 25));

        return response()->json(['data' => $snapshots]);
    }

    public function verifications(Request $request, int $learnerProfileId): JsonResponse
    {
        $verifications = MemberVerification::where('learner_profile_id', $learnerProfileId)
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 25));

        return response()->json(['data' => $verifications]);
    }

    private function mockVerify(?string $membershipNo): array
    {
        if (! $membershipNo) {
            return [
                'status' => 'none',
                'member_name' => null,
                'member_type' => null,
                'message' => 'No membership number provided.',
            ];
        }

        $hash = crc32($membershipNo);
        $isActive = ($hash % 3) !== 0;

        return [
            'status' => $isActive ? 'active' : 'expired',
            'member_name' => 'Mock Member ('.$membershipNo.')',
            'member_type' => $isActive ? 'ordinary' : null,
            'expiry_date' => $isActive ? now()->addMonths(6)->toDateString() : now()->subMonths(3)->toDateString(),
            'message' => $isActive ? 'Membership is active.' : 'Membership has expired.',
        ];
    }

    private function updatePricingEligibility(LearnerProfile $profile, MemberVerification $verification, array $mockResult): void
    {
        MemberPricingEligibility::updateOrCreate(
            ['learner_profile_id' => $profile->id, 'is_active' => true],
            [
                'member_verification_id' => $verification->id,
                'member_type' => $mockResult['member_type'] ?? 'ordinary',
                'pricing_rule' => 'member_price',
                'discount_percentage' => 0,
                'is_active' => true,
                'expires_at' => isset($mockResult['expiry_date']) ? $mockResult['expiry_date'] : null,
            ],
        );
    }
}
