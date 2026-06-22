<?php

namespace Modules\ClassScheduling\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\ClassScheduling\Models\ClassSession;
use Modules\ClassScheduling\Models\CourseClass;
use Modules\ClassScheduling\Models\SchedulePattern;

class SessionGeneratorService
{
    public function generate(CourseClass $class): int
    {
        ClassSession::where('class_id', $class->id)->delete();

        $pattern = $class->schedulePattern;

        if (! $pattern) {
            return 0;
        }

        $holidays = $this->getHolidayDates();

        $sessions = $pattern->type === 'weekly'
            ? $this->generateWeekly($class, $pattern, $holidays)
            : $this->generateOneOff($class, $pattern);

        $count = 0;
        foreach ($sessions as $index => $session) {
            ClassSession::create([
                'class_id' => $class->id,
                'session_no' => $index + 1,
                'date' => $session['date'],
                'start_time' => $pattern->start_time,
                'end_time' => $pattern->end_time,
                'classroom_id' => $class->classroom_id,
                'instructor_id' => $class->instructor_id,
                'status' => 'scheduled',
            ]);
            $count++;
        }

        return $count;
    }

    private function getHolidayDates(): array
    {
        return DB::table('class_scheduling.holidays')
            ->pluck('date')
            ->map(fn ($d) => (string) $d)
            ->toArray();
    }

    private function generateWeekly(CourseClass $class, SchedulePattern $pattern, array $holidays): array
    {
        $days = $pattern->days_of_week ?? [];
        $overrideDates = collect($pattern->overrides ?? [])->pluck('date')->toArray();

        $sessions = [];
        $current = Carbon::parse($class->start_date);
        $end = Carbon::parse($class->end_date);

        while ($current->lte($end)) {
            $dayOfWeek = $current->dayOfWeek;
            $dateStr = $current->toDateString();

            if (in_array($dayOfWeek, $days)
                && ! in_array($dateStr, $overrideDates)
                && ! in_array($dateStr, $holidays)
            ) {
                $sessions[] = ['date' => $dateStr];
            }

            $current->addDay();
        }

        return $sessions;
    }

    private function generateOneOff(CourseClass $class, SchedulePattern $pattern): array
    {
        $sessions = [];

        foreach ($pattern->overrides ?? [] as $override) {
            if (isset($override['date'])) {
                $sessions[] = ['date' => $override['date']];
            }
        }

        return $sessions;
    }
}
