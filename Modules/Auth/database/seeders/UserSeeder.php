<?php

namespace Modules\Auth\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\Auth\Models\InstructorProfile;
use Modules\Auth\Models\StaffProfile;
use Modules\Auth\Models\User;

class UserSeeder extends Seeder
{
    private const USERS = [
        [
            'name' => 'System Admin',
            'email' => 'admin@hkftu.org',
            'phone' => '+852-2000-0001',
            'role' => 'system_admin',
            'staff_no' => 'SYSADM-001',
            'department' => 'IT',
            'title' => 'System Administrator',
        ],
        [
            'name' => 'Chan Tai Man',
            'email' => 'planner@hkftu.org',
            'phone' => '+852-2000-0002',
            'role' => 'course_planner',
            'staff_no' => 'PLAN-001',
            'department' => 'Course Planning',
            'title' => 'Senior Course Planner',
        ],
        [
            'name' => 'Wong Siu Ming',
            'email' => 'manager@hkftu.org',
            'phone' => '+852-2000-0003',
            'role' => 'centre_manager',
            'staff_no' => 'MGR-001',
            'department' => 'Centre Operations',
            'title' => 'Centre Manager',
        ],
        [
            'name' => 'Lee Wai Han',
            'email' => 'counter@hkftu.org',
            'phone' => '+852-2000-0004',
            'role' => 'counter_staff',
            'staff_no' => 'CTR-001',
            'department' => 'Centre Operations',
            'title' => 'Counter Staff',
        ],
        [
            'name' => 'Lam Ching Wai',
            'email' => 'instructor@hkftu.org',
            'phone' => '+852-2000-0005',
            'role' => 'instructor',
            'staff_no' => 'INS-001',
            'department' => 'Teaching',
            'title' => 'Senior Instructor',
        ],
        [
            'name' => 'Cheung Wai Man',
            'email' => 'instructor2@hkftu.org',
            'phone' => '+852-2000-0007',
            'role' => 'instructor',
            'staff_no' => 'INS-002',
            'department' => 'Teaching',
            'title' => 'Instructor',
        ],
        [
            'name' => 'Tam Siu Fung',
            'email' => 'instructor3@hkftu.org',
            'phone' => '+852-2000-0008',
            'role' => 'instructor',
            'staff_no' => 'INS-003',
            'department' => 'Teaching',
            'title' => 'Instructor',
        ],
        [
            'name' => 'Leung Ka Ming',
            'email' => 'instructor4@hkftu.org',
            'phone' => '+852-2000-0009',
            'role' => 'instructor',
            'staff_no' => 'INS-004',
            'department' => 'Teaching',
            'title' => 'Instructor',
        ],
        [
            'name' => 'Yip Shuk Han',
            'email' => 'instructor5@hkftu.org',
            'phone' => '+852-2000-0010',
            'role' => 'instructor',
            'staff_no' => 'INS-005',
            'department' => 'Teaching',
            'title' => 'Instructor',
        ],
        [
            'name' => 'Ng Mei Ling',
            'email' => 'finance@hkftu.org',
            'phone' => '+852-2000-0006',
            'role' => 'finance_staff',
            'staff_no' => 'FIN-001',
            'department' => 'Finance',
            'title' => 'Finance Officer',
        ],
    ];

    public function run(): void
    {
        $defaultPassword = Hash::make('password');
        $systemCentreId = 0;
        $instructorIndex = 0;

        foreach (self::USERS as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'phone' => $data['phone'],
                    'password' => $defaultPassword,
                    'status' => 'active',
                    'email_verified_at' => now(),
                ],
            );

            setPermissionsTeamId($systemCentreId);
            $user->assignRole($data['role']);

            StaffProfile::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'staff_no' => $data['staff_no'],
                    'department' => $data['department'],
                    'title' => $data['title'],
                    'status' => 'active',
                ],
            );

            if ($data['role'] === 'instructor') {
                $instructorIndex++;
                InstructorProfile::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'instructor_no' => sprintf('INST-%03d', $instructorIndex),
                        'name' => $data['name'],
                        'phone' => $data['phone'],
                        'email' => $data['email'],
                        'bank_name' => 'HSBC',
                        'bank_account_no' => 'encrypted:xxxx',
                        'bank_account_name' => $data['name'],
                        'cheque_payable_to' => $data['name'],
                        'qualifications' => ['TESOL', 'Certificate in Education'],
                        'categories' => ['language', 'business'],
                        'status' => 'active',
                    ],
                );
            }
        }
    }
}
