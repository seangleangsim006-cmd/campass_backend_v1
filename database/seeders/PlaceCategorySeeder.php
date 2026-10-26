<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\PlaceCategory;

class PlaceCategorySeeder extends Seeder
{
    public function run(): void
    {
        $orange = '#D9693B'; // culture
        $blue   = '#2A9DB5'; // beach / sea
        $green  = '#3E8E5A'; // nature

        $categories = [
            ['temple',    'culture', $orange, 'temple',    'Temple',    'ប្រាសាទ',       '寺庙',   '사원'],
            ['museum',    'culture', $orange, 'museum',    'Museum',    'សារមន្ទីរ',      '博物馆', '박물관'],
            ['market',    'culture', $orange, 'market',    'Market',    'ផ្សារ',          '市场',   '시장'],
            ['beach',     'beach',   $blue,   'beach',     'Beach',     'ឆ្នេរ',          '海滩',   '해변'],
            ['island',    'beach',   $blue,   'island',    'Island',    'កោះ',            '岛屿',   '섬'],
            ['mountain',  'nature',  $green,  'mountain',  'Mountain',  'ភ្នំ',           '山',     '산'],
            ['waterfall', 'nature',  $green,  'waterfall', 'Waterfall', 'ទឹកធ្លាក់',      '瀑布',   '폭포'],
            ['nature',    'nature',  $green,  'tree',      'Nature',    'ធម្មជាតិ',       '自然',   '자연'],
            ['viewpoint', 'nature',  $green,  'viewpoint', 'Viewpoint', 'ចំណុចទេសភាព',    '观景点', '전망대'],
        ];

        foreach ($categories as $i => [$slug, $group, $color, $icon, $en, $km, $zh, $ko]) {
            PlaceCategory::updateOrCreate(['slug' => $slug], [
                'name'       => compact('en', 'km', 'zh', 'ko'),
                'group'      => $group,
                'color'      => $color,
                'icon'       => $icon,
                'sort_order' => $i + 1,
            ]);
        }
    }
}