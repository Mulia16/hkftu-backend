<?php

namespace Modules\Attendance\Services;

use Illuminate\Support\Collection;
use Modules\Attendance\Enums\AttendanceStatus;
use Modules\Attendance\Models\AttendanceRecord;
use Modules\ClassScheduling\Models\ClassSession;
use Modules\ClassScheduling\Models\CourseClass;
use Modules\Enrolment\Models\Enrolment;

class AttendanceService
{
    public function getGrid(CourseClass $class): array
    {
        $sessions = ClassSession::where('class_id', $class->id)
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        $enrolments = Enrolment::where('class_id', $class->id)
            ->where('status', 'confirmed')
            ->with('learner')
            ->get();

        $sessionIds = $sessions->pluck('id');
        $records = AttendanceRecord::whereIn('class_session_id', $sessionIds)->get();

        $grid = [];
        foreach ($enrolments as $enrolment) {
            $row = [
                'enrolment_id' => $enrolment->id,
                'learner' => $enrolment->learner,
                'sessions' => [],
            ];

            foreach ($sessions as $session) {
                $record = $records->first(
                    fn ($r) => $r->class_session_id === $session->id && $r->enrolment_id === $enrolment->id
                );

                $row['sessions'][] = [
                    'session_id' => $session->id,
                    'date' => $session->date->toDateString(),
                    'start_time' => $session->start_time,
                    'end_time' => $session->end_time,
                    'record_id' => $record?->id,
                    'status' => $record?->status,
                    'remarks' => $record?->remarks,
                    'marked_at' => $record?->marked_at?->toIso8601String(),
                ];
            }

            $grid[] = $row;
        }

        return [
            'class' => $class->load('course.subject'),
            'sessions' => $sessions,
            'grid' => $grid,
            'summary' => $this->calculateSummary($enrolments, $records, $sessions),
        ];
    }

    public function batchUpsert(int $classSessionId, array $records, int $markedBy): Collection
    {
        $results = collect();

        foreach ($records as $record) {
            $attendance = AttendanceRecord::updateOrCreate(
                [
                    'class_session_id' => $classSessionId,
                    'enrolment_id' => $record['enrolment_id'],
                ],
                [
                    'status' => $record['status'],
                    'remarks' => $record['remarks'] ?? null,
                    'marked_by' => $markedBy,
                    'marked_at' => now(),
                ]
            );

            $results->push($attendance);
        }

        return $results;
    }

    public function submit(ClassSession $session, int $submittedBy): array
    {
        $enrolments = Enrolment::where('class_id', $session->class_id)
            ->where('status', 'confirmed')
            ->pluck('id');

        $existingRecords = AttendanceRecord::where('class_session_id', $session->id)
            ->whereIn('enrolment_id', $enrolments)
            ->pluck('enrolment_id')
            ->toArray();

        $missing = array_diff($enrolments->toArray(), $existingRecords);

        if (! empty($missing)) {
            foreach ($missing as $enrolmentId) {
                AttendanceRecord::create([
                    'class_session_id' => $session->id,
                    'enrolment_id' => $enrolmentId,
                    'status' => AttendanceStatus::Absent->value,
                    'marked_by' => $submittedBy,
                    'marked_at' => now(),
                ]);
            }
        }

        $session->update(['status' => 'completed']);

        return [
            'session_id' => $session->id,
            'auto_marked_absent' => count($missing),
            'total_records' => $enrolments->count(),
        ];
    }

    public function getLearnerHistory(int $learnerId): array
    {
        $records = AttendanceRecord::whereHas('enrolment', fn ($q) => $q->where('learner_id', $learnerId))
            ->with(['classSession.courseClass.course.subject'])
            ->orderByDesc('created_at')
            ->get();

        $byClass = $records->groupBy(fn ($r) => $r->classSession->class_id);

        $history = [];
        foreach ($byClass as $classId => $classRecords) {
            $first = $classRecords->first();
            $total = $classRecords->count();
            $present = $classRecords->where('status', AttendanceStatus::Present)->count();
            $late = $classRecords->where('status', AttendanceStatus::Late)->count();

            $history[] = [
                'class_id' => $classId,
                'course_name' => $first->classSession->courseClass->course->subject->name ?? '',
                'total_sessions' => $total,
                'present' => $present,
                'late' => $late,
                'absent' => $classRecords->where('status', AttendanceStatus::Absent)->count(),
                'excused' => $classRecords->where('status', AttendanceStatus::Excused)->count(),
                'attendance_rate' => $total > 0 ? round(($present + $late) / $total * 100, 1) : 0,
            ];
        }

        return $history;
    }

    private function calculateSummary($enrolments, $records, $sessions): array
    {
        $totalStudents = $enrolments->count();
        $totalSessions = $sessions->count();

        return [
            'total_students' => $totalStudents,
            'total_sessions' => $totalSessions,
            'total_records' => $records->count(),
            'present' => $records->where('status', AttendanceStatus::Present)->count(),
            'absent' => $records->where('status', AttendanceStatus::Absent)->count(),
            'late' => $records->where('status', AttendanceStatus::Late)->count(),
            'excused' => $records->where('status', AttendanceStatus::Excused)->count(),
        ];
    }
}
