<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            SuperAdminSeeder::class,
            RegionSeeder::class,
            PlaceCategorySeeder::class,
            PhraseCategorySeeder::class,
            PhraseSeeder::class,
            EmergencyContactSeeder::class,
            SampleContentSeeder::class,   // remove for production
        ]);
    }
}