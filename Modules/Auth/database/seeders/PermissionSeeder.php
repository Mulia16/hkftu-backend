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
    ];

    public function run(): void
    {
        foreach (self::PERMISSIONS as $permission) {
            Permission::firstOrCreate(
                ['code' => $permission['code'], 'guard_name' => 'web'],
                ['name' => $permission['code'], 'module' => $permission['module'], 'action' => $permission['action']],
            );
        }

        $systemAdmin = Role::where('code', 'system_admin')->first();
        $systemAdmin?->syncPermissions(Permission::pluck('name')->all());
    }
}
