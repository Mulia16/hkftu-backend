<?php

namespace Modules\Certificate\Database\Seeders;

use Illuminate\Database\Seeder;

class CertificateDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(CertificateRecordSeeder::class);
    }
}
