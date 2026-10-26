<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\{Destination, Place, PlaceCategory, PlaceTip, Region};

class SampleContentSeeder extends Seeder
{
    public function run(): void
    {
        $north = Region::where('slug', 'siem-reap-north')->firstOrFail();
        $south = Region::where('slug', 'south-coast')->firstOrFail();

        $siemReap = Destination::updateOrCreate(['slug' => 'siem-reap'], [
            'region_id'   => $north->id,
            'name'        => ['en' => 'Siem Reap', 'km' => 'សៀមរាប', 'zh' => '暹粒', 'ko' => '시엠립'],
            'tagline'     => ['en' => 'Home of Angkor'],
            'description' => ['en' => 'Gateway to the Angkor temples and the heart of Khmer heritage.'],
            'best_season' => ['en' => 'November to February'],
            'latitude'    => 13.3633,
            'longitude'   => 103.8564,
            'status'      => 'published',
        ]);

        $kohRong = Destination::updateOrCreate(['slug' => 'koh-rong'], [
            'region_id'   => $south->id,
            'name'        => ['en' => 'Koh Rong', 'km' => 'កោះរ៉ុង', 'zh' => '高龙岛', 'ko' => '꼬롱'],
            'tagline'     => ['en' => 'Island of white sand and clear water'],
            'best_season' => ['en' => 'November to April'],
            'status'      => 'published',
        ]);

        $temple = PlaceCategory::where('slug', 'temple')->firstOrFail();
        $beach  = PlaceCategory::where('slug', 'beach')->firstOrFail();

        $places = [
            [$siemReap, $temple, 'angkor-wat',  'Angkor Wat',  'អង្គរវត្ត',    13.4125, 103.8670],
            [$siemReap, $temple, 'bayon-temple', 'Bayon Temple', 'ប្រាសាទបាយ័ន', 13.4412, 103.8590],
            [$siemReap, $temple, 'ta-prohm',    'Ta Prohm',    'ប្រាសាទតាព្រហ្ម', 13.4350, 103.8890],
            [$kohRong,  $beach,  'long-set-beach', 'Long Set Beach', 'ឆ្នេរឡុងសិត', 10.6900, 103.2500],
        ];

        foreach ($places as [$dest, $cat, $slug, $en, $km, $lat, $lng]) {
            $place = Place::updateOrCreate(['slug' => $slug], [
                'destination_id'    => $dest->id,
                'place_category_id' => $cat->id,
                'name'              => ['en' => $en, 'km' => $km],
                'latitude'          => $lat,
                'longitude'         => $lng,
                'radius_m'          => 150,
                'status'            => 'published',
            ]);

            if ($cat->slug === 'temple' && $place->tips()->count() === 0) {
                PlaceTip::create([
                    'place_id' => $place->id,
                    'type'     => 'etiquette',
                    'text'     => ['en' => 'Cover your shoulders and knees. Remove your hat inside sanctuaries.'],
                ]);
            }
        }
    }
}