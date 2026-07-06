<?php

namespace Modules\Attendance\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttendanceRecordSeeder extends Seeder
{
    public function run(): void
    {
        $learners = DB::table('auth.learner_profiles')->pluck('id');
        $classes = DB::table('class_scheduling.classes')->pluck('id');

        if ($learners->isEmpty() || $classes->isEmpty()) {
            return;
        }

        $statuses = ['present', 'present', 'present', 'absent', 'late', 'excused'];
        $now = now();

        foreach ($classes->take(3) as $classId) {
            foreach ($learners as $learnerId) {
                $enrolmentId = DB::table('enrolment.enrolments')->insertGetId([
                    'class_id' => $classId,
                    'learner_id' => $learnerId,
                    'status' => 'confirmed',
                    'channel' => 'counter',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $sessions = DB::table('class_scheduling.class_sessions')
                    ->where('class_id', $classId)
                    ->orderBy('date')
                    ->limit(5)
                    ->get();

                foreach ($sessions as $session) {
                    DB::table('attendance.attendance_records')->insert([
                        'class_session_id' => $session->id,
                        'enrolment_id' => $enrolmentId,
                        'status' => $statuses[array_rand($statuses)],
                        'marked_by' => 1,
                        'marked_at' => $now,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }

        DB::table('attendance.attendance_policies')->insert([
            [
                'name' => 'Standard 75%',
                'course_type' => null,
                'min_percentage' => 75,
                'exam_required' => false,
                'rules_json' => null,
                'effective_from' => null,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Certificate Course 80%',
                'course_type' => 'certificate',
                'min_percentage' => 80,
                'exam_required' => true,
                'rules_json' => null,
                'effective_from' => null,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
