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
        // This tells Laravel to run the AdminSeeder when you seed the DB
        $this->call([
            AdminSeeder::class,
        ]);
    }
}
