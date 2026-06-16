<?php

namespace Modules\Auth\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Auth\Models\Role;

class RoleSeeder extends Seeder
{
    private const ROLES = [
        'public_learner',
        'hkftu_member',
        'counter_staff',
        'course_planner',
        'centre_manager',
        'instructor',
        'finance_staff',
        'system_admin',
        'ai_operations',
    ];

    public function run(): void
    {
        foreach (self::ROLES as $code) {
            Role::firstOrCreate(
                ['code' => $code, 'guard_name' => 'web'],
                ['name' => $code, 'scope_type' => 'global'],
            );
        }
    }
}
