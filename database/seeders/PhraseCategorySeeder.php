<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\PhraseCategory;

class PhraseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['greetings', 'Greetings',            'ការស្វាគមន៍',      '问候',     '인사'],
            ['food',      'Food & Drink',         'អាហារ និងភេសជ្ជៈ', '餐饮',     '음식'],
            ['shopping',  'Shopping & Bargaining', 'ទិញទំនិញ',        '购物砍价', '쇼핑'],
            ['transport', 'Transport',            'ការធ្វើដំណើរ',     '交通',     '교통'],
            ['hotel',     'Hotel',                'សណ្ឋាគារ',         '酒店',     '호텔'],
            ['emergency', 'Emergency',            'អាសន្ន',           '紧急情况', '긴급 상황'],
        ];

        foreach ($categories as $i => [$slug, $en, $km, $zh, $ko]) {
            PhraseCategory::updateOrCreate(['slug' => $slug], [
                'name'       => compact('en', 'km', 'zh', 'ko'),
                'sort_order' => $i + 1,
            ]);
        }
    }
}