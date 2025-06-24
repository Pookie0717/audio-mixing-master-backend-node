<?php

namespace Database\Seeders;

use App\Models\Label;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LabelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Label::insert([
            [
                "name" => "Best Deal",
                "created_at" => now(),
                "updated_at" => now(),
            ],
        ]);
    }
}
