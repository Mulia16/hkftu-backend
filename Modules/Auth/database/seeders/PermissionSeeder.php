<?php

namespace Modules\Auth\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Auth\Models\Permission;
use Modules\Auth\Models\Role;

class PermissionSeeder extends Seeder
{
    private const PERMISSIONS = [
        ['code' => 'system.manage-roles', 'module' => 'system', 'action' => 'manage-roles'],
        ['code' => 'system.manage-users', 'module' => 'system', 'action' => 'manage-users'],
        ['code' => 'system.view-audit-logs', 'module' => 'system', 'action' => 'view-audit-logs'],
        ['code' => 'course.create-subject', 'module' => 'course', 'action' => 'create-subject'],
        ['code' => 'course.edit-subject', 'module' => 'course', 'action' => 'edit-subject'],
        ['code' => 'course.delete-subject', 'module' => 'course', 'action' => 'delete-subject'],
        ['code' => 'course.publish-class', 'module' => 'course', 'action' => 'publish-class'],
        ['code' => 'enrolment.create', 'module' => 'enrolment', 'action' => 'create'],
        ['code' => 'enrolment.counter', 'module' => 'enrolment', 'action' => 'counter'],
        ['code' => 'enrolment.view', 'module' => 'enrolment', 'action' => 'view'],
        ['code' => 'payment.approve-refund', 'module' => 'payment', 'action' => 'approve-refund'],
        ['code' => 'payment.view', 'module' => 'payment', 'action' => 'view'],
        ['code' => 'payment.reconcile', 'module' => 'payment', 'action' => 'reconcile'],
        ['code' => 'attendance.record', 'module' => 'attendance', 'action' => 'record'],
        ['code' => 'attendance.view', 'module' => 'attendance', 'action' => 'view'],
        ['code' => 'certificate.issue', 'module' => 'certificate', 'action' => 'issue'],
        ['code' => 'certificate.view', 'module' => 'certificate', 'action' => 'view'],
        ['code' => 'instructor.manage', 'module' => 'instructor', 'action' => 'manage'],
        ['code' => 'instructor.calculate-fee', 'module' => 'instructor', 'action' => 'calculate-fee'],
        ['code' => 'report.view', 'module' => 'report', 'action' => 'view'],
        ['code' => 'report.export', 'module' => 'report', 'action' => 'export'],
        ['code' => 'student.view-full-id', 'module' => 'student', 'action' => 'view-full-id'],
        ['code' => 'student.manage', 'module' => 'student', 'action' => 'manage'],
    ];

    private const ROLE_PERMISSIONS = [
        'system_admin' => '*',
        'centre_manager' => [
            'course.create-subject', 'course.edit-subject', 'course.publish-class',
            'enrolment.create', 'enrolment.counter', 'enrolment.view',
            'payment.approve-refund', 'payment.view',
            'attendance.record', 'attendance.view',
            'certificate.issue', 'certificate.view',
            'instructor.manage', 'report.view', 'report.export',
            'student.manage', 'student.view-full-id',
        ],
        'course_planner' => [
            'course.create-subject', 'course.edit-subject', 'course.delete-subject', 'course.publish-class',
            'instructor.manage', 'report.view',
        ],
        'counter_staff' => [
            'enrolment.counter', 'enrolment.view',
            'attendance.record', 'attendance.view',
            'certificate.view', 'student.manage',
        ],
        'finance_staff' => [
            'payment.approve-refund', 'payment.view', 'payment.reconcile',
            'report.view', 'report.export',
        ],
        'instructor' => [
            'attendance.record', 'attendance.view',
        ],
        'public_learner' => [
            'enrolment.create', 'enrolment.view',
        ],
        'hkftu_member' => [
            'enrolment.create', 'enrolment.view',
        ],
    ];

    public function run(): void
    {
        foreach (self::PERMISSIONS as $permission) {
            Permission::firstOrCreate(
                ['code' => $permission['code'], 'guard_name' => 'web'],
                ['name' => $permission['code'], 'module' => $permission['module'], 'action' => $permission['action']],
            );
        }

        foreach (self::ROLE_PERMISSIONS as $roleCode => $permissions) {
            $role = Role::where('code', $roleCode)->first();
            if (! $role) {
                continue;
            }

            if ($permissions === '*') {
                $role->syncPermissions(Permission::pluck('name')->all());
            } else {
                $role->syncPermissions($permissions);
            }
        }
    }
}
