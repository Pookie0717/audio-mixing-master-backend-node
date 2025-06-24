<?php

namespace Database\Seeders;

use App\Models\Gift;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Gift::insert([
            [
                "image" => "gift-images/0.png",
                "name" => "Eid Gift",
                "price" => 100,
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "image" => "gift-images/0.png",
                "name" => "Free Shipping",
                "price" => 50,
                "created_at" => now(),
                "updated_at" => now(),
            ],
        ]);
    }
}
