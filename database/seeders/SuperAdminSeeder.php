<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['name' => env('SUPER_ADMIN_NAME', 'admin')],
            [
                'password' => Hash::make(env('SUPER_ADMIN_PASSWORD', 'password')),
                'role' => 'super_admin',
            ],
        );
    }
}
