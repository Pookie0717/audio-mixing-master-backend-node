<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleAndPermissionSeeder::class,
            UserSeeder::class,
            SampleSeeder::class,
            GallarySeeder::class,
            LabelSeeder::class,
            CategorySeeder::class,
            ServiceSeeder::class,
            GiftSeeder::class,
            FavouriteSeeder::class,
            OrderSeeder::class,
            TestimonialSeeder::class,
            EmailSubcriberSeeder::class,
            GiftOrderSeeder::class,
        ]);
    }
}
