<?php

namespace Modules\CourseCatalogue\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\CourseCatalogue\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $tree = [
            [
                'code' => 'COMPUTER',
                'name_en' => 'Computer & IT',
                'name_zh' => '電腦及資訊科技',
                'sort_order' => 1,
                'children' => [
                    ['code' => 'COMPUTER_OFFICE', 'name_en' => 'Microsoft Office', 'name_zh' => 'Microsoft Office', 'sort_order' => 1],
                    ['code' => 'COMPUTER_PROG',   'name_en' => 'Programming',       'name_zh' => '程式設計',            'sort_order' => 2],
                    ['code' => 'COMPUTER_DESIGN',  'name_en' => 'Design & Graphics', 'name_zh' => '設計及圖像',          'sort_order' => 3],
                ],
            ],
            [
                'code' => 'LANGUAGE',
                'name_en' => 'Languages',
                'name_zh' => '語言',
                'sort_order' => 2,
                'children' => [
                    ['code' => 'LANGUAGE_ENG', 'name_en' => 'English',            'name_zh' => '英語',   'sort_order' => 1],
                    ['code' => 'LANGUAGE_MAN', 'name_en' => 'Mandarin Chinese',   'name_zh' => '普通話', 'sort_order' => 2],
                    ['code' => 'LANGUAGE_JPN', 'name_en' => 'Japanese',           'name_zh' => '日語',   'sort_order' => 3],
                    ['code' => 'LANGUAGE_KOR', 'name_en' => 'Korean',             'name_zh' => '韓語',   'sort_order' => 4],
                ],
            ],
            [
                'code' => 'HEALTH',
                'name_en' => 'Health & Wellness',
                'name_zh' => '健康及保健',
                'sort_order' => 3,
                'children' => [
                    ['code' => 'HEALTH_FITNESS', 'name_en' => 'Yoga & Fitness', 'name_zh' => '瑜伽及健身', 'sort_order' => 1],
                    ['code' => 'HEALTH_FIRSTAID', 'name_en' => 'First Aid',     'name_zh' => '急救',       'sort_order' => 2],
                    ['code' => 'HEALTH_BEAUTY',   'name_en' => 'Beauty & Spa',  'name_zh' => '美容及水療', 'sort_order' => 3],
                ],
            ],
            [
                'code' => 'COOKING',
                'name_en' => 'Cooking & Culinary',
                'name_zh' => '烹飪',
                'sort_order' => 4,
                'children' => [
                    ['code' => 'COOKING_CHINESE',  'name_en' => 'Chinese Cuisine',  'name_zh' => '中式烹飪', 'sort_order' => 1],
                    ['code' => 'COOKING_WESTERN',  'name_en' => 'Western Cuisine',  'name_zh' => '西式烹飪', 'sort_order' => 2],
                    ['code' => 'COOKING_PASTRY',   'name_en' => 'Baking & Pastry',  'name_zh' => '烘焙',     'sort_order' => 3],
                ],
            ],
            [
                'code' => 'BUSINESS',
                'name_en' => 'Business & Management',
                'name_zh' => '商業及管理',
                'sort_order' => 5,
                'children' => [
                    ['code' => 'BUSINESS_ACCT', 'name_en' => 'Accounting & Finance',  'name_zh' => '會計及財務', 'sort_order' => 1],
                    ['code' => 'BUSINESS_MGMT', 'name_en' => 'Project Management',    'name_zh' => '項目管理',   'sort_order' => 2],
                    ['code' => 'BUSINESS_HR',   'name_en' => 'Human Resources',       'name_zh' => '人力資源',   'sort_order' => 3],
                ],
            ],
            [
                'code' => 'ARTS',
                'name_en' => 'Arts & Crafts',
                'name_zh' => '藝術及手工藝',
                'sort_order' => 6,
                'children' => [
                    ['code' => 'ARTS_PAINTING', 'name_en' => 'Painting & Drawing', 'name_zh' => '繪畫',  'sort_order' => 1],
                    ['code' => 'ARTS_MUSIC',    'name_en' => 'Music',              'name_zh' => '音樂',  'sort_order' => 2],
                    ['code' => 'ARTS_PHOTO',    'name_en' => 'Photography',        'name_zh' => '攝影',  'sort_order' => 3],
                ],
            ],
            [
                'code' => 'ELDERLY',
                'name_en' => 'Elderly & Senior',
                'name_zh' => '長者課程',
                'sort_order' => 7,
                'children' => [],
            ],
            [
                'code' => 'CEF',
                'name_en' => 'CEF Approved',
                'name_zh' => 'CEF認可課程',
                'sort_order' => 8,
                'children' => [],
            ],
        ];

        foreach ($tree as $parentData) {
            $children = $parentData['children'];
            unset($parentData['children']);

            $parent = Category::firstOrCreate(
                ['code' => $parentData['code']],
                array_merge($parentData, ['parent_id' => null])
            );

            foreach ($children as $childData) {
                Category::firstOrCreate(
                    ['code' => $childData['code']],
                    array_merge($childData, ['parent_id' => $parent->id])
                );
            }
        }
    }
}
