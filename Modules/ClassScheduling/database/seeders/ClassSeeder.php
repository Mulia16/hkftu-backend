<?php

namespace Modules\ClassScheduling\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\ClassScheduling\Models\Centre;
use Modules\ClassScheduling\Models\Classroom;
use Modules\ClassScheduling\Models\CourseClass;
use Modules\ClassScheduling\Models\SchedulePattern;
use Modules\ClassScheduling\Services\SessionGeneratorService;
use Modules\CourseCatalogue\Models\Course;
use Modules\Auth\Models\User;

class ClassSeeder extends Seeder
{
    public function run(): void
    {
        $courses = Course::with('subject')->where('status', 'published')->get();
        $centres = Centre::where('status', 'active')->get();
        $instructor = User::whereHas('roles', fn ($q) => $q->where('name', 'instructor'))->first();

        if ($courses->isEmpty() || $centres->isEmpty()) {
            return;
        }

        $patterns = [
            SchedulePattern::create([
                'type' => 'weekly',
                'days_of_week' => [3],
                'start_time' => '19:00',
                'end_time' => '21:00',
            ]),
            SchedulePattern::create([
                'type' => 'weekly',
                'days_of_week' => [6],
                'start_time' => '10:00',
                'end_time' => '12:00',
            ]),
            SchedulePattern::create([
                'type' => 'weekly',
                'days_of_week' => [0],
                'start_time' => '14:00',
                'end_time' => '16:00',
            ]),
        ];

        $sessionGenerator = app(SessionGeneratorService::class);

        foreach ($courses->take(5) as $i => $course) {
            $centre = $centres[$i % $centres->count()];
            $classroom = Classroom::where('centre_id', $centre->id)->first();
            $pattern = $patterns[$i % count($patterns)];

            $class = CourseClass::firstOrCreate(
                ['class_code' => sprintf('CLS-%s-%02d', $course->course_code, 1)],
                [
                    'course_id' => $course->id,
                    'schedule_pattern_id' => $pattern->id,
                    'centre_id' => $centre->id,
                    'classroom_id' => $classroom?->id,
                    'capacity' => $classroom?->capacity ?? 30,
                    'min_students' => 5,
                    'start_date' => '2026-07-01',
                    'end_date' => '2026-09-30',
                    'instructor_id' => $instructor?->id,
                    'status' => 'published',
                ],
            );

            $sessionGenerator->generate($class);
        }
    }
}
