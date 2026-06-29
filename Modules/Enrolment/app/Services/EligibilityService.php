<?php

namespace Modules\Enrolment\Services;

use Modules\Auth\Models\LearnerProfile;
use Modules\Auth\Models\MemberPricingEligibility;
use Modules\ClassScheduling\Models\CourseClass;
use Modules\Enrolment\Models\Enrolment;
use Modules\Enrolment\Models\PriorityWindow;

class EligibilityService
{
    public function check(CourseClass $class, LearnerProfile $learner, string $channel): array
    {
        $reasons = [];
        $allowed = true;

        $windowResult = $this->checkRegistrationWindow($class, $channel, $learner);
        if (! $windowResult['allowed']) {
            $allowed = false;
            $reasons[] = $windowResult['reason'];
        }

        $duplicateResult = $this->checkDuplicateEnrolment($class, $learner);
        if (! $duplicateResult['allowed']) {
            $allowed = false;
            $reasons[] = $duplicateResult['reason'];
        }

        $classStatusResult = $this->checkClassStatus($class);
        if (! $classStatusResult['allowed']) {
            $allowed = false;
            $reasons[] = $classStatusResult['reason'];
        }

        $pricing = $this->resolvePricing($class, $learner, $channel);

        return [
            'allowed' => $allowed,
            'reasons' => $reasons,
            'pricing' => $pricing,
            'channel' => $channel,
            'window' => $windowResult['window'] ?? null,
        ];
    }

    private function checkRegistrationWindow(CourseClass $class, string $channel, LearnerProfile $learner): array
    {
        $course = $class->course;

        if (! $course) {
            return ['allowed' => true, 'window' => null];
        }

        $now = now();

        $classWindow = PriorityWindow::where('class_id', $class->id)
            ->where('channel', $channel)
            ->where('start_at', '<=', $now)
            ->where('end_at', '>=', $now)
            ->first();

        if ($classWindow) {
            return $this->evaluateWindowEligibility($classWindow, $learner);
        }

        $courseWindow = PriorityWindow::where('course_id', $course->id)
            ->where('channel', $channel)
            ->where('start_at', '<=', $now)
            ->where('end_at', '>=', $now)
            ->first();

        if ($courseWindow) {
            return $this->evaluateWindowEligibility($courseWindow, $learner);
        }

        $seasonWindow = PriorityWindow::where('season_id', $course->season_id)
            ->whereNull('course_id')
            ->whereNull('class_id')
            ->where('channel', $channel)
            ->where('start_at', '<=', $now)
            ->where('end_at', '>=', $now)
            ->first();

        if ($seasonWindow) {
            return $this->evaluateWindowEligibility($seasonWindow, $learner);
        }

        $season = $course->season;

        if ($season && $season->public_registration_start && $now->lt($season->public_registration_start)) {
            return [
                'allowed' => false,
                'reason' => 'Registration has not opened yet. Public registration starts on '.$season->public_registration_start->toDateString().'.',
                'window' => null,
            ];
        }

        return ['allowed' => true, 'window' => null];
    }

    private function evaluateWindowEligibility(PriorityWindow $window, LearnerProfile $learner): array
    {
        if (! $window->eligibility_rule) {
            return ['allowed' => true, 'window' => $window];
        }

        return match ($window->eligibility_rule) {
            'member' => $this->checkMemberEligibility($window, $learner),
            'returning_student' => $this->checkReturningStudentEligibility($window, $learner),
            default => ['allowed' => true, 'window' => $window],
        };
    }

    private function checkMemberEligibility(PriorityWindow $window, LearnerProfile $learner): array
    {
        if ($learner->membership_status === 'active') {
            return ['allowed' => true, 'window' => $window];
        }

        return [
            'allowed' => false,
            'reason' => 'This registration window is for HKFTU members only. Please verify your membership first.',
            'window' => $window,
        ];
    }

    private function checkReturningStudentEligibility(PriorityWindow $window, LearnerProfile $learner): array
    {
        $hasPrevious = Enrolment::where('learner_id', $learner->id)
            ->whereIn('status', ['confirmed', 'completed'])
            ->exists();

        if ($hasPrevious) {
            return ['allowed' => true, 'window' => $window];
        }

        return [
            'allowed' => false,
            'reason' => 'This registration window is for returning students only.',
            'window' => $window,
        ];
    }

    private function checkDuplicateEnrolment(CourseClass $class, LearnerProfile $learner): array
    {
        $existing = Enrolment::where('class_id', $class->id)
            ->where('learner_id', $learner->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();

        if ($existing) {
            return [
                'allowed' => false,
                'reason' => 'You are already enrolled in this class.',
            ];
        }

        return ['allowed' => true];
    }

    private function checkClassStatus(CourseClass $class): array
    {
        if ($class->status !== 'published') {
            return [
                'allowed' => false,
                'reason' => 'This class is not available for registration.',
            ];
        }

        if ($class->start_date && $class->start_date->isPast()) {
            return [
                'allowed' => false,
                'reason' => 'This class has already started.',
            ];
        }

        return ['allowed' => true];
    }

    private function resolvePricing(CourseClass $class, LearnerProfile $learner, string $channel): array
    {
        $subject = $class->course?->subject;

        $basePrice = $subject ? (float) $subject->tuition_fee : 0;
        $materialFee = $subject ? (float) $subject->material_fee : 0;

        $memberDiscount = 0;

        if ($learner->membership_status === 'active') {
            $eligibility = MemberPricingEligibility::where('learner_profile_id', $learner->id)
                ->where('is_active', true)
                ->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->first();

            if ($eligibility && $eligibility->discount_percentage > 0) {
                $memberDiscount = $basePrice * ($eligibility->discount_percentage / 100);
            }
        }

        return [
            'currency' => 'HKD',
            'base_price' => $basePrice,
            'material_fee' => $materialFee,
            'member_discount' => $memberDiscount,
            'total' => $basePrice - $memberDiscount + $materialFee,
        ];
    }
}
