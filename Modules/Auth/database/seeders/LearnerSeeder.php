<?php

namespace Modules\Auth\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Auth\Models\LearnerProfile;
use Modules\Auth\Models\User;

class LearnerSeeder extends Seeder
{
    public function run(): void
    {
        $learners = [
            [
                'email' => 'learner1@example.com',
                'name' => 'Chan Siu Ming',
                'profile' => [
                    'name_en' => 'Chan Siu Ming',
                    'name_zh' => '陳小明',
                    'id_type' => 'HKID',
                    'dob' => '1990-05-15',
                    'gender' => 'male',
                    'phone' => '+852-9123-4567',
                    'email' => 'learner1@example.com',
                    'membership_no' => 'M20260001',
                    'membership_status' => 'active',
                ],
            ],
            [
                'email' => 'learner2@example.com',
                'name' => 'Wong Lai Kwan',
                'profile' => [
                    'name_en' => 'Wong Lai Kwan',
                    'name_zh' => '黃麗群',
                    'id_type' => 'HKID',
                    'dob' => '1985-11-22',
                    'gender' => 'female',
                    'phone' => '+852-9234-5678',
                    'email' => 'learner2@example.com',
                    'membership_no' => 'M20260002',
                    'membership_status' => 'active',
                ],
            ],
            [
                'email' => 'learner3@example.com',
                'name' => 'Lee Wai Man',
                'profile' => [
                    'name_en' => 'Lee Wai Man',
                    'name_zh' => '李偉文',
                    'id_type' => 'HKID',
                    'dob' => '1992-03-08',
                    'gender' => 'male',
                    'phone' => '+852-9345-6789',
                    'email' => 'learner3@example.com',
                    'membership_no' => null,
                    'membership_status' => 'none',
                ],
            ],
        ];

        $defaultPassword = \Illuminate\Support\Facades\Hash::make('password');

        foreach ($learners as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => $defaultPassword,
                    'status' => 'active',
                    'email_verified_at' => now(),
                ],
            );

            $user->assignRole('public_learner');

            LearnerProfile::firstOrCreate(
                ['user_id' => $user->id],
                $data['profile'],
            );
        }
    }
}
