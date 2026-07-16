<?php

namespace Modules\Payment\Database\Seeders;

use Illuminate\Database\Seeder;

class PaymentDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(PaymentRecordSeeder::class);
        $this->call(ReconciliationSeeder::class);
    }
}
