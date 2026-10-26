<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\User;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@khmercompass.test'],
            [
                'name'               => 'Super Admin',
                'password'           => 'ChangeMe123!',   // hashed automatically by the cast
                'email_verified_at'  => now(),
                'preferred_language' => 'en',
                'country_code'       => 'KH',
            ]
        );

        $admin->syncRoles(['super_admin']);
    }
}