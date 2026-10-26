<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\EmergencyContact;

class EmergencyContactSeeder extends Seeder
{
    public function run(): void
    {
        // destination_id = null means nationwide
        $contacts = [
            ['police',    '117', ['en' => 'Police',    'km' => 'ប៉ូលិស']],
            ['ambulance', '119', ['en' => 'Ambulance', 'km' => 'រថយន្តសង្គ្រោះ']],
            ['other',     '118', ['en' => 'Fire brigade', 'km' => 'អគ្គិសនី'.'']],
        ];

        foreach ($contacts as [$type, $phone, $name]) {
            EmergencyContact::updateOrCreate(
                ['type' => $type, 'phone' => $phone, 'destination_id' => null],
                ['name' => $name]
            );
        }
    }
}