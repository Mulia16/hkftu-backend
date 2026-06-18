<?php

namespace Modules\CourseCatalogue\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\CourseCatalogue\Models\Category;
use Modules\CourseCatalogue\Models\Subject;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            [
                'subject_code' => 'S001',
                'name' => 'Microsoft Excel — Basic',
                'tuition_fee' => 680,
                'material_fee' => 50,
                'instructor_fee_default' => 350,
                'total_hours' => 12,
                'lesson_hours' => 2,
                'status' => 'active',
                'category_codes' => ['COMPUTER_OFFICE', 'CEF'],
            ],
            [
                'subject_code' => 'S002',
                'name' => 'Microsoft Excel — Advanced',
                'tuition_fee' => 880,
                'material_fee' => 50,
                'instructor_fee_default' => 400,
                'total_hours' => 15,
                'lesson_hours' => 2.5,
                'status' => 'active',
                'category_codes' => ['COMPUTER_OFFICE'],
            ],
            [
                'subject_code' => 'S003',
                'name' => 'Python Programming for Beginners',
                'tuition_fee' => 1200,
                'material_fee' => 80,
                'instructor_fee_default' => 500,
                'total_hours' => 18,
                'lesson_hours' => 3,
                'status' => 'active',
                'category_codes' => ['COMPUTER_PROG'],
            ],
            [
                'subject_code' => 'S004',
                'name' => 'Business English Communication',
                'tuition_fee' => 980,
                'material_fee' => 60,
                'instructor_fee_default' => 450,
                'total_hours' => 20,
                'lesson_hours' => 2,
                'status' => 'active',
                'category_codes' => ['LANGUAGE_ENG'],
            ],
            [
                'subject_code' => 'S005',
                'name' => 'Conversational Mandarin (Elementary)',
                'tuition_fee' => 780,
                'material_fee' => 40,
                'instructor_fee_default' => 380,
                'total_hours' => 15,
                'lesson_hours' => 1.5,
                'status' => 'active',
                'category_codes' => ['LANGUAGE_MAN'],
            ],
            [
                'subject_code' => 'S006',
                'name' => 'Yoga for Beginners',
                'tuition_fee' => 580,
                'material_fee' => 0,
                'instructor_fee_default' => 300,
                'total_hours' => 10,
                'lesson_hours' => 2,
                'status' => 'active',
                'category_codes' => ['HEALTH_FITNESS'],
            ],
            [
                'subject_code' => 'S007',
                'name' => 'Standard First Aid Certificate',
                'tuition_fee' => 480,
                'material_fee' => 30,
                'instructor_fee_default' => 280,
                'total_hours' => 8,
                'lesson_hours' => 4,
                'status' => 'active',
                'category_codes' => ['HEALTH_FIRSTAID', 'CEF'],
            ],
            [
                'subject_code' => 'S008',
                'name' => 'Cantonese Home Cooking',
                'tuition_fee' => 680,
                'material_fee' => 120,
                'instructor_fee_default' => 350,
                'total_hours' => 12,
                'lesson_hours' => 2,
                'status' => 'active',
                'category_codes' => ['COOKING_CHINESE'],
            ],
            [
                'subject_code' => 'S009',
                'name' => 'Western Baking — Bread & Pastry',
                'tuition_fee' => 780,
                'material_fee' => 150,
                'instructor_fee_default' => 380,
                'total_hours' => 12,
                'lesson_hours' => 3,
                'status' => 'active',
                'category_codes' => ['COOKING_PASTRY'],
            ],
            [
                'subject_code' => 'S010',
                'name' => 'Digital Photography Fundamentals',
                'tuition_fee' => 880,
                'material_fee' => 0,
                'instructor_fee_default' => 420,
                'total_hours' => 15,
                'lesson_hours' => 3,
                'status' => 'draft',
                'category_codes' => ['ARTS_PHOTO'],
            ],
        ];

        $categoryMap = Category::whereIn('code', collect($subjects)->pluck('category_codes')->flatten()->unique()->values()->all())
            ->pluck('id', 'code');

        foreach ($subjects as $data) {
            $categoryCodes = $data['category_codes'];
            unset($data['category_codes']);

            $subject = Subject::firstOrCreate(
                ['subject_code' => $data['subject_code']],
                $data
            );

            $categoryIds = collect($categoryCodes)
                ->map(fn ($code) => $categoryMap[$code] ?? null)
                ->filter()
                ->values()
                ->all();

            $subject->categories()->sync($categoryIds);
        }
    }
}
