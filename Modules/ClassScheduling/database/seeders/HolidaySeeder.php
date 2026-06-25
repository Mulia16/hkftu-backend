<?php

namespace Modules\ClassScheduling\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\ClassScheduling\Models\Holiday;

class HolidaySeeder extends Seeder
{
    public function run(): void
    {
        $holidays = [
            ['date' => '2026-07-01', 'name' => 'Hong Kong SAR Establishment Day', 'type' => 'public'],
            ['date' => '2026-09-25', 'name' => 'Day after Mid-Autumn Festival', 'type' => 'public'],
            ['date' => '2026-10-01', 'name' => 'National Day', 'type' => 'public'],
            ['date' => '2026-10-29', 'name' => 'Chung Yeung Festival', 'type' => 'public'],
            ['date' => '2026-12-25', 'name' => 'Christmas Day', 'type' => 'public'],
            ['date' => '2026-12-26', 'name' => 'Boxing Day', 'type' => 'public'],
        ];

        foreach ($holidays as $data) {
            Holiday::firstOrCreate(['date' => $data['date']], $data);
        }
    }
}
