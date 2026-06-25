<?php

namespace Modules\CourseCatalogue\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\CourseCatalogue\Models\Course;
use Modules\CourseCatalogue\Models\Season;
use Modules\CourseCatalogue\Models\Subject;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $season = Season::where('code', '2026Q3')->first();
        if (! $season) {
            return;
        }

        $subjects = Subject::where('status', 'active')->get();

        foreach ($subjects as $i => $subject) {
            Course::firstOrCreate(
                ['season_id' => $season->id, 'subject_id' => $subject->id],
                [
                    'course_code' => sprintf('C-%s-%s', $season->code, $subject->subject_code),
                    'page_no' => $i + 1,
                    'status' => 'published',
                    'publish_at' => '2026-05-25 09:00:00',
                ],
            );
        }
    }
}
