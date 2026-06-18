<?php

namespace Modules\CourseCatalogue\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\CourseCatalogue\Models\Season;

class SeasonSeeder extends Seeder
{
    public function run(): void
    {
        $seasons = [
            [
                'code' => '2026Q3',
                'name' => 'Summer 2026',
                'start_date' => '2026-07-01',
                'end_date' => '2026-09-30',
                'member_registration_start' => '2026-06-01 09:00:00',
                'public_registration_start' => '2026-06-08 09:00:00',
            ],
            [
                'code' => '2026Q4',
                'name' => 'Autumn 2026',
                'start_date' => '2026-10-01',
                'end_date' => '2026-12-31',
                'member_registration_start' => '2026-09-01 09:00:00',
                'public_registration_start' => '2026-09-08 09:00:00',
            ],
            [
                'code' => '2027Q1',
                'name' => 'Spring 2027',
                'start_date' => '2027-01-01',
                'end_date' => '2027-03-31',
                'member_registration_start' => '2026-12-01 09:00:00',
                'public_registration_start' => '2026-12-08 09:00:00',
            ],
        ];

        foreach ($seasons as $data) {
            Season::firstOrCreate(['code' => $data['code']], $data);
        }
    }
}
