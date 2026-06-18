<?php

namespace Modules\ClassScheduling\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\ClassScheduling\Models\Centre;
use Modules\ClassScheduling\Models\Classroom;

class ClassroomSeeder extends Seeder
{
    public function run(): void
    {
        $classroomsByCentre = [
            'HKI01' => [
                ['code' => 'R01', 'name' => 'Training Room A', 'capacity' => 25, 'facilities_json' => ['whiteboard', 'projector', 'air_conditioning']],
                ['code' => 'R02', 'name' => 'Training Room B', 'capacity' => 20, 'facilities_json' => ['whiteboard', 'projector', 'air_conditioning']],
                ['code' => 'LAB1', 'name' => 'Computer Lab',   'capacity' => 20, 'facilities_json' => ['computers', 'projector', 'air_conditioning', 'internet']],
            ],
            'HKI02' => [
                ['code' => 'R01', 'name' => 'Training Room A', 'capacity' => 20, 'facilities_json' => ['whiteboard', 'projector', 'air_conditioning']],
                ['code' => 'R02', 'name' => 'Training Room B', 'capacity' => 15, 'facilities_json' => ['whiteboard', 'air_conditioning']],
            ],
            'KLN01' => [
                ['code' => 'R01', 'name' => 'Training Room A', 'capacity' => 30, 'facilities_json' => ['whiteboard', 'projector', 'air_conditioning']],
                ['code' => 'R02', 'name' => 'Training Room B', 'capacity' => 25, 'facilities_json' => ['whiteboard', 'projector', 'air_conditioning']],
                ['code' => 'R03', 'name' => 'Training Room C', 'capacity' => 20, 'facilities_json' => ['whiteboard', 'air_conditioning']],
                ['code' => 'LAB1', 'name' => 'Computer Lab',   'capacity' => 24, 'facilities_json' => ['computers', 'projector', 'air_conditioning', 'internet']],
            ],
            'KLN02' => [
                ['code' => 'R01', 'name' => 'Training Room A', 'capacity' => 25, 'facilities_json' => ['whiteboard', 'projector', 'air_conditioning']],
                ['code' => 'R02', 'name' => 'Training Room B', 'capacity' => 20, 'facilities_json' => ['whiteboard', 'air_conditioning']],
                ['code' => 'LAB1', 'name' => 'Computer Lab',   'capacity' => 20, 'facilities_json' => ['computers', 'projector', 'air_conditioning', 'internet']],
            ],
            'NT01' => [
                ['code' => 'R01', 'name' => 'Training Room A', 'capacity' => 30, 'facilities_json' => ['whiteboard', 'projector', 'air_conditioning']],
                ['code' => 'R02', 'name' => 'Training Room B', 'capacity' => 25, 'facilities_json' => ['whiteboard', 'projector', 'air_conditioning']],
                ['code' => 'LAB1', 'name' => 'Computer Lab',   'capacity' => 25, 'facilities_json' => ['computers', 'projector', 'air_conditioning', 'internet']],
            ],
            'NT02' => [
                ['code' => 'R01', 'name' => 'Training Room A', 'capacity' => 25, 'facilities_json' => ['whiteboard', 'projector', 'air_conditioning']],
                ['code' => 'R02', 'name' => 'Training Room B', 'capacity' => 20, 'facilities_json' => ['whiteboard', 'air_conditioning']],
            ],
        ];

        $centres = Centre::whereIn('code', array_keys($classroomsByCentre))->pluck('id', 'code');

        foreach ($classroomsByCentre as $centreCode => $classrooms) {
            $centreId = $centres[$centreCode] ?? null;
            if (! $centreId) {
                continue;
            }

            foreach ($classrooms as $data) {
                Classroom::firstOrCreate(
                    ['centre_id' => $centreId, 'code' => $data['code']],
                    array_merge($data, ['centre_id' => $centreId, 'status' => 'active'])
                );
            }
        }
    }
}
