<?php

namespace Modules\InstructorFinance\Services;

use Illuminate\Support\Collection;
use Modules\ClassScheduling\Models\ClassSession;
use Modules\ClassScheduling\Models\CourseClass;
use Modules\Enrolment\Models\Enrolment;
use Modules\InstructorFinance\Models\InstructorFeeItem;
use Modules\InstructorFinance\Models\InstructorFeeRule;

class InstructorFeeCalculationService
{
    public function calculateForSeason(int $seasonId, ?int $centreId = null, ?int $instructorId = null): Collection
    {
        $query = CourseClass::whereHas('course', fn ($q) => $q->where('season_id', $seasonId))
            ->whereNotNull('instructor_id');

        if ($centreId) {
            $query->where('centre_id', $centreId);
        }

        if ($instructorId) {
            $query->where('instructor_id', $instructorId);
        }

        $classes = $query->with(['course.subject', 'course'])->get();
        $items = collect();

        foreach ($classes as $class) {
            $item = $this->calculateForClass($class);
            if ($item) {
                $items->push($item);
            }
        }

        return $items;
    }

    public function calculateForClass(CourseClass $class): ?InstructorFeeItem
    {
        if (!$class->instructor_id) {
            return null;
        }

        $existing = InstructorFeeItem::where('class_id', $class->id)
            ->where('instructor_id', $class->instructor_id)
            ->first();

        if ($existing && $existing->status !== 'calculated') {
            return $existing;
        }

        $rule = $this->findMatchingRule($class);
        $amount = $this->computeAmount($class, $rule);

        $data = [
            'class_id' => $class->id,
            'instructor_id' => $class->instructor_id,
            'fee_rule_id' => $rule?->id,
            'amount' => $amount,
            'status' => 'calculated',
            'calculated_at' => now(),
        ];

        return InstructorFeeItem::updateOrCreate(
            ['class_id' => $class->id, 'instructor_id' => $class->instructor_id],
            $data
        );
    }

    private function findMatchingRule(CourseClass $class): ?InstructorFeeRule
    {
        $courseId = $class->course_id;
        $subjectId = $class->course?->subject_id;

        $rule = InstructorFeeRule::where('course_id', $courseId)
            ->where('effective_from', '<=', now())
            ->orderByDesc('effective_from')
            ->first();

        if ($rule) return $rule;

        if ($subjectId) {
            $rule = InstructorFeeRule::where('subject_id', $subjectId)
                ->where('effective_from', '<=', now())
                ->orderByDesc('effective_from')
                ->first();

            if ($rule) return $rule;
        }

        return InstructorFeeRule::whereNull('subject_id')
            ->whereNull('course_id')
            ->where('effective_from', '<=', now())
            ->orderByDesc('effective_from')
            ->first();
    }

    private function computeAmount(CourseClass $class, ?InstructorFeeRule $rule): float
    {
        if (!$rule) {
            return (float) ($class->course?->subject?->instructor_fee_default ?? 0);
        }

        return match ($rule->rate_type) {
            'per_session' => (float) $rule->amount * $this->getSessionCount($class),
            'per_hour' => (float) $rule->amount * (float) ($class->course?->subject?->total_hours ?? 0),
            'flat' => (float) $rule->amount,
            'per_student' => (float) $rule->amount * $this->getEnrolmentCount($class),
            default => (float) $rule->amount,
        };
    }

    private function getSessionCount(CourseClass $class): int
    {
        return ClassSession::where('class_id', $class->id)->count();
    }

    private function getEnrolmentCount(CourseClass $class): int
    {
        return Enrolment::where('class_id', $class->id)
            ->where('status', 'confirmed')
            ->count();
    }
}
