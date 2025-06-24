<?php

namespace Database\Seeders;

use App\Models\Gallary;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GallarySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Gallary::insert([
            [
                'image' => 'gallary-images/1.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'image' => 'gallary-images/2.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'image' => 'gallary-images/3.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'image' => 'gallary-images/4.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'image' => 'gallary-images/5.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'image' => 'gallary-images/6.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
