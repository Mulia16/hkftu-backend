<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\CourseCatalogue\Models\Notice;

class NoticeSeeder extends Seeder
{
    public function run(): void
    {
        $notices = [
            [
                'title' => 'Summer 2026 Registration Now Open',
                'content' => 'Member priority registration starts 1 June 2026. Public registration opens 8 June 2026. Visit our course catalogue to explore available classes.',
                'type' => 'registration',
                'is_active' => true,
                'published_at' => '2026-05-25 09:00:00',
            ],
            [
                'title' => 'Typhoon Signal No. 8 — Classes Suspended',
                'content' => 'All classes scheduled for today are suspended due to Typhoon Signal No. 8. Affected sessions will be rescheduled. Please check your class schedule for updates.',
                'type' => 'weather',
                'is_active' => true,
                'published_at' => '2026-06-15 07:00:00',
            ],
            [
                'title' => 'New Centre Opening in Tuen Mun',
                'content' => 'HKFTU is pleased to announce the opening of our new training centre in Tuen Mun (NT02). A variety of courses will be available starting Autumn 2026.',
                'type' => 'general',
                'is_active' => true,
                'published_at' => '2026-06-01 10:00:00',
            ],
        ];

        foreach ($notices as $notice) {
            Notice::firstOrCreate(
                ['title' => $notice['title']],
                $notice,
            );
        }
    }
}
