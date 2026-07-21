<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;
use Modules\Auth\Models\DependentProfile;
use Modules\Auth\Models\User;

class Ownership
{
    public const STAFF_ROLES = [
        'system_admin',
        'centre_manager',
        'counter_staff',
        'finance_staff',
        'course_planner',
    ];

    public static function isStaff(?User $user): bool
    {
        return $user !== null && $user->hasAnyRole(self::STAFF_ROLES);
    }

    public static function ownedLearnerIds(User $user): array
    {
        $ids = [];

        if ($ownId = $user->learnerProfile?->id) {
            $ids[] = $ownId;
        }

        $dependentIds = DependentProfile::where('guardian_user_id', $user->id)
            ->pluck('learner_profile_id')
            ->filter()
            ->all();

        return array_values(array_unique(array_merge($ids, $dependentIds)));
    }

    public static function ownsLearner(?User $user, ?int $learnerId): bool
    {
        if ($user === null || $learnerId === null) {
            return false;
        }

        return in_array($learnerId, self::ownedLearnerIds($user), true);
    }

    public static function canAccessLearner(?User $user, ?int $learnerId): bool
    {
        return self::isStaff($user) || self::ownsLearner($user, $learnerId);
    }

    public static function forbidden(string $message = 'You do not have access to this resource.'): JsonResponse
    {
        return ApiError::respond('FORBIDDEN', $message, 403);
    }
}
