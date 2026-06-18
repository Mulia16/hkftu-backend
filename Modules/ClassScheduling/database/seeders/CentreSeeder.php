<?php

namespace Modules\ClassScheduling\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\ClassScheduling\Models\Centre;

class CentreSeeder extends Seeder
{
    public function run(): void
    {
        $centres = [
            [
                'code' => 'HKI01',
                'name' => 'HKFTU CEC — Central',
                'district' => 'Hong Kong Island',
                'address' => '8/F, Fu Tak Commercial Building, 365 Hennessy Road, Wan Chai, Hong Kong',
                'phone' => '2893 3993',
                'opening_hours' => [
                    'Mon-Fri' => '09:00–18:00',
                    'Sat' => '09:00–13:00',
                    'Sun' => 'Closed',
                ],
                'status' => 'active',
            ],
            [
                'code' => 'HKI02',
                'name' => 'HKFTU CEC — North Point',
                'district' => 'Hong Kong Island',
                'address' => '3/F, Hiu Ming Building, 83 Java Road, North Point, Hong Kong',
                'phone' => '2570 9388',
                'opening_hours' => [
                    'Mon-Fri' => '09:00–18:00',
                    'Sat' => '09:00–13:00',
                    'Sun' => 'Closed',
                ],
                'status' => 'active',
            ],
            [
                'code' => 'KLN01',
                'name' => 'HKFTU CEC — Mong Kok',
                'district' => 'Kowloon',
                'address' => '6/F, Workers Club Building, 1 Mong Kok Road, Mong Kok, Kowloon',
                'phone' => '2395 5678',
                'opening_hours' => [
                    'Mon-Fri' => '09:00–18:00',
                    'Sat' => '09:00–13:00',
                    'Sun' => 'Closed',
                ],
                'status' => 'active',
            ],
            [
                'code' => 'KLN02',
                'name' => 'HKFTU CEC — Kwun Tong',
                'district' => 'Kowloon',
                'address' => '5/F, Trade Union Building, 12 Hoi Yuen Road, Kwun Tong, Kowloon',
                'phone' => '2343 9988',
                'opening_hours' => [
                    'Mon-Fri' => '09:00–18:00',
                    'Sat' => '09:00–13:00',
                    'Sun' => 'Closed',
                ],
                'status' => 'active',
            ],
            [
                'code' => 'NT01',
                'name' => 'HKFTU CEC — Sha Tin',
                'district' => 'New Territories',
                'address' => '4/F, Sha Tin Trade Union Centre, 21 Yuen Wo Road, Sha Tin, NT',
                'phone' => '2647 7788',
                'opening_hours' => [
                    'Mon-Fri' => '09:00–18:00',
                    'Sat' => '09:00–13:00',
                    'Sun' => 'Closed',
                ],
                'status' => 'active',
            ],
            [
                'code' => 'NT02',
                'name' => 'HKFTU CEC — Tuen Mun',
                'district' => 'New Territories',
                'address' => '2/F, Tuen Mun Labour Centre, 2 Tuen Hi Road, Tuen Mun, NT',
                'phone' => '2461 3388',
                'opening_hours' => [
                    'Mon-Fri' => '09:00–18:00',
                    'Sat' => '09:00–13:00',
                    'Sun' => 'Closed',
                ],
                'status' => 'active',
            ],
        ];

        foreach ($centres as $data) {
            Centre::firstOrCreate(['code' => $data['code']], $data);
        }
    }
}
