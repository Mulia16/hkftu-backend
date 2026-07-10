<?php

namespace Modules\Certificate\Services;

use Illuminate\Support\Facades\DB;
use Modules\Attendance\Models\AttendancePolicy;
use Modules\Attendance\Models\AttendanceRecord;
use Modules\Enrolment\Models\Enrolment;

class CertificateEligibilityService
{
    public function calculateEligibility(int $classId): array
    {
        $enrolments = Enrolment::where('class_id', $classId)
            ->where('status', 'confirmed')
            ->with(['learner', 'courseClass.course.subject'])
            ->get();

        $policy = $this->getPolicy($classId);
        $minPercentage = $policy?->min_percentage ?? 75;

        $results = [];

        foreach ($enrolments as $enrolment) {
            $totalSessions = DB::table('class_scheduling.class_sessions')
                ->where('class_id', $classId)
                ->count();

            if ($totalSessions === 0) {
                $results[] = [
                    'enrolment_id' => $enrolment->id,
                    'learner' => $enrolment->learner,
                    'eligible' => false,
                    'reason' => 'No sessions found',
                    'attendance_rate' => 0,
                ];

                continue;
            }

            $attendanceStats = AttendanceRecord::whereHas('classSession', fn ($q) => $q->where('class_id', $classId))
                ->where('enrolment_id', $enrolment->id)
                ->selectRaw("
                    COUNT(*) as total,
                    COUNT(CASE WHEN status = 'present' THEN 1 END) as present,
                    COUNT(CASE WHEN status = 'late' THEN 1 END) as late
                ")
                ->first();

            $attended = ($attendanceStats->present ?? 0) + ($attendanceStats->late ?? 0);
            $rate = $totalSessions > 0 ? round($attended / $totalSessions * 100, 1) : 0;
            $eligible = $rate >= $minPercentage;

            $results[] = [
                'enrolment_id' => $enrolment->id,
                'learner' => $enrolment->learner,
                'total_sessions' => $totalSessions,
                'attended' => $attended,
                'attendance_rate' => $rate,
                'min_required' => $minPercentage,
                'eligible' => $eligible,
                'reason' => $eligible ? null : "Attendance {$rate}% below minimum {$minPercentage}%",
            ];
        }

        return $results;
    }

    public function isEligible(int $enrolmentId): bool
    {
        $enrolment = Enrolment::findOrFail($enrolmentId);
        $results = $this->calculateEligibility($enrolment->class_id);

        foreach ($results as $result) {
            if ($result['enrolment_id'] === $enrolmentId) {
                return $result['eligible'];
            }
        }

        return false;
    }

    private function getPolicy(int $classId): ?AttendancePolicy
    {
        return AttendancePolicy::where('is_active', true)
            ->orderByDesc('id')
            ->first();
    }
}
