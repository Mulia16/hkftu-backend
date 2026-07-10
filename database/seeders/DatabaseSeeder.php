<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Attendance\Database\Seeders\AttendanceDatabaseSeeder;
use Modules\Auth\Database\Seeders\AuthDatabaseSeeder;
use Modules\Certificate\Database\Seeders\CertificateDatabaseSeeder;
use Modules\ClassScheduling\Database\Seeders\ClassSchedulingDatabaseSeeder;
use Modules\CourseCatalogue\Database\Seeders\CourseCatalogueDatabaseSeeder;
use Modules\Payment\Database\Seeders\PaymentDatabaseSeeder;

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
            PaymentDatabaseSeeder::class,
            CertificateDatabaseSeeder::class,
        ]);
    }
}
