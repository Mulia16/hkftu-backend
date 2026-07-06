<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Attendance\Database\Seeders\AttendanceDatabaseSeeder;
use Modules\Auth\Database\Seeders\AuthDatabaseSeeder;
use Modules\ClassScheduling\Database\Seeders\ClassSchedulingDatabaseSeeder;
use Modules\CourseCatalogue\Database\Seeders\CourseCatalogueDatabaseSeeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AuthDatabaseSeeder::class,
            CourseCatalogueDatabaseSeeder::class,
            ClassSchedulingDatabaseSeeder::class,
            NoticeSeeder::class,
            AttendanceDatabaseSeeder::class,
        ]);
    }
}
