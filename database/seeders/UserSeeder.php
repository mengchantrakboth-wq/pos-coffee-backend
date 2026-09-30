<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['username' => 'admin'],
            [
                'full_name'     => 'Super Admin',
                'email'         => 'admin@example.com',
                'phone'         => '012345678',
                'password_hash' => Hash::make('123456'),
                'role_id'       => 1,
            ]
        );
    }
}
