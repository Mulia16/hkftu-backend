<?php

namespace Modules\ClassScheduling\Services;

use Modules\ClassScheduling\Models\ClassSession;
use Modules\ClassScheduling\Models\ClashCheckResult;
use Modules\ClassScheduling\Models\CourseClass;

class ClashCheckService
{
    public function run(CourseClass $class): array
    {
        ClashCheckResult::where('class_id', $class->id)->delete();

        $results = [];

        $results = array_merge($results, $this->checkClassroomDoubleBooking($class));
        $results = array_merge($results, $this->checkInstructorDoubleBooking($class));
        $results = array_merge($results, $this->checkCapacityVsRoom($class));
        $results = array_merge($results, $this->checkDurationMismatch($class));
        $results = array_merge($results, $this->checkPageNoMissing($class));
        $results = array_merge($results, $this->checkRegistrationAfterClassStart($class));

        foreach ($results as $result) {
            ClashCheckResult::create([
                'class_id'   => $class->id,
                'severity'   => $result['severity'],
                'check_type' => $result['check_type'],
                'message'    => $result['message'],
            ]);
        }

        return $results;
    }

    private function checkClassroomDoubleBooking(CourseClass $class): array
    {
        if (! $class->classroom_id) {
            return [];
        }

        $sessions = ClassSession::where('class_id', $class->id)->get();

        foreach ($sessions as $session) {
            $conflict = ClassSession::where('classroom_id', $session->classroom_id)
                ->where('date', $session->date)
                ->where('class_id', '!=', $class->id)
                ->where('status', '!=', 'cancelled')
                ->where('start_time', '<', $session->end_time)
                ->where('end_time', '>', $session->start_time)
                ->exists();

            if ($conflict) {
                return [[
                    'severity'   => 'error',
                    'check_type' => 'classroom_double_booking',
                    'message'    => "Classroom is already booked on {$session->date} {$session->start_time}–{$session->end_time}.",
                ]];
            }
        }

        return [];
    }

    private function checkInstructorDoubleBooking(CourseClass $class): array
    {
        if (! $class->instructor_id) {
            return [];
        }

        $sessions = ClassSession::where('class_id', $class->id)->get();

        foreach ($sessions as $session) {
            $conflict = ClassSession::where('instructor_id', $session->instructor_id)
                ->where('date', $session->date)
                ->where('class_id', '!=', $class->id)
                ->where('status', '!=', 'cancelled')
                ->where('start_time', '<', $session->end_time)
                ->where('end_time', '>', $session->start_time)
                ->exists();

            if ($conflict) {
                return [[
                    'severity'   => 'error',
                    'check_type' => 'instructor_double_booking',
                    'message'    => "Instructor is already assigned to another class on {$session->date} {$session->start_time}–{$session->end_time}.",
                ]];
            }
        }

        return [];
    }

    private function checkCapacityVsRoom(CourseClass $class): array
    {
        if (! $class->classroom_id) {
            return [];
        }

        $class->load('classroom');

        if ($class->classroom && $class->capacity > $class->classroom->capacity) {
            return [[
                'severity'   => 'warning',
                'check_type' => 'capacity_exceeds_room',
                'message'    => "Class capacity ({$class->capacity}) exceeds classroom capacity ({$class->classroom->capacity}).",
            ]];
        }

        return [];
    }

    private function checkPageNoMissing(CourseClass $class): array
    {
        $class->loadMissing('course');

        if ($class->course && is_null($class->course->page_no)) {
            return [[
                'severity'   => 'warning',
                'check_type' => 'page_no_missing',
                'message'    => 'Course has no brochure page number assigned. Required before brochure export.',
            ]];
        }

        return [];
    }

    private function checkRegistrationAfterClassStart(CourseClass $class): array
    {
        $class->loadMissing('course.season');

        $season = $class->course?->season;

        if (! $season || ! $season->public_registration_start) {
            return [];
        }

        if ($season->public_registration_start > $class->start_date) {
            return [[
                'severity'   => 'warning',
                'check_type' => 'registration_after_class_start',
                'message'    => "Season public registration opens ({$season->public_registration_start}) after class start date ({$class->start_date}).",
            ]];
        }

        return [];
    }

    private function checkDurationMismatch(CourseClass $class): array
    {
        $class->load('course.subject', 'sessions');

        $subject = $class->course?->subject;

        if (! $subject) {
            return [];
        }

        $sessionCount = $class->sessions->count();
        $lessonHours = (float) $subject->lesson_hours;
        $totalHours = (float) $subject->total_hours;

        if ($sessionCount > 0 && abs(($sessionCount * $lessonHours) - $totalHours) > 0.01) {
            return [[
                'severity'   => 'warning',
                'check_type' => 'duration_mismatch',
                'message'    => "Generated sessions ({$sessionCount} × {$lessonHours}h = ".round($sessionCount * $lessonHours, 2)."h) do not match subject total hours ({$totalHours}h).",
            ]];
        }

        return [];
    }
}
