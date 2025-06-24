<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::insert([
            [
                'name' => 'Uncategorized',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Mixing Services',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Mastering Services',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Mixing & Mastering Services',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
