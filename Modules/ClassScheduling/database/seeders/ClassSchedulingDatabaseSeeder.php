<?php

namespace Modules\ClassScheduling\Database\Seeders;

use Illuminate\Database\Seeder;

class ClassSchedulingDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CentreSeeder::class,
            ClassroomSeeder::class,
            HolidaySeeder::class,
        ]);
    }
}
