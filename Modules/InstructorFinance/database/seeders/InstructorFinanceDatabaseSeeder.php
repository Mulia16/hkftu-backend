<?php

namespace Modules\InstructorFinance\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Auth\Models\User;
use Modules\ClassScheduling\Models\CourseClass;
use Modules\CourseCatalogue\Models\Subject;
use Modules\InstructorFinance\Models\InstructorContract;
use Modules\InstructorFinance\Models\InstructorFeeRule;

class InstructorFinanceDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedFeeRules();
        $this->seedContracts();
    }

    private function seedFeeRules(): void
    {
        $subjects = Subject::all();

        foreach ($subjects as $subject) {
            InstructorFeeRule::firstOrCreate(
                ['subject_id' => $subject->id, 'rate_type' => 'per_session'],
                [
                    'amount' => $subject->instructor_fee_default ?? 350,
                    'effective_from' => '2026-01-01',
                ]
            );
        }

        InstructorFeeRule::firstOrCreate(
            ['subject_id' => null, 'course_id' => null, 'rate_type' => 'per_session'],
            [
                'amount' => 350,
                'effective_from' => '2026-01-01',
            ]
        );
    }

    private function seedContracts(): void
    {
        $instructors = User::whereHas('roles', fn ($q) => $q->where('name', 'instructor'))
            ->orderBy('id')
            ->get();

        foreach ($instructors as $instructor) {
            $classes = CourseClass::where('instructor_id', $instructor->id)->get();

            foreach ($classes as $class) {
                InstructorContract::firstOrCreate(
                    ['class_id' => $class->id, 'instructor_id' => $instructor->id],
                    ['status' => 'signed', 'signed_at' => now()->subDays(30)]
                );
            }
        }
    }
}
