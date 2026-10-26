<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\Region;

class RegionSeeder extends Seeder
{
    public function run(): void
    {
        $regions = [
            ['slug' => 'siem-reap-north', 'sort_order' => 1, 'name' => [
                'en' => 'Siem Reap & North', 'km' => 'សៀមរាប និងភាគខាងជើង',
                'zh' => '暹粒及北部', 'ko' => '시엠립 및 북부',
            ]],
            ['slug' => 'phnom-penh-center', 'sort_order' => 2, 'name' => [
                'en' => 'Phnom Penh & Center', 'km' => 'ភ្នំពេញ និងភាគកណ្តាល',
                'zh' => '金边及中部', 'ko' => '프놈펜 및 중부',
            ]],
            ['slug' => 'south-coast', 'sort_order' => 3, 'name' => [
                'en' => 'South Coast', 'km' => 'ឆ្នេរសមុទ្រខាងត្បូង',
                'zh' => '南部海岸', 'ko' => '남부 해안',
            ]],
            ['slug' => 'east-northeast', 'sort_order' => 4, 'name' => [
                'en' => 'East & Northeast', 'km' => 'ភាគខាងកើត និងឦសាន',
                'zh' => '东部及东北部', 'ko' => '동부 및 북동부',
            ]],
        ];

        foreach ($regions as $region) {
            Region::updateOrCreate(['slug' => $region['slug']], $region);
        }
    }
}