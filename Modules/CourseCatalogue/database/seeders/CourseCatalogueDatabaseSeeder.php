<?php

namespace Modules\CourseCatalogue\Database\Seeders;

use Illuminate\Database\Seeder;

class CourseCatalogueDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SeasonSeeder::class,
            CategorySeeder::class,
            SubjectSeeder::class,
        ]);
    }
}
