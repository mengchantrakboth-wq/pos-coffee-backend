<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class, // user need role to login
            UserSeeder::class, // need user 1 role to login
            VariantSeeder::class, // need variant to create product
            CustomerSeeder::class, //walk-in customer need to create order
            TableSeeder::class, // need table to create order
        ]);
    }
}
