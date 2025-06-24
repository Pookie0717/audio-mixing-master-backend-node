<?php

namespace Database\Seeders;

use App\Models\Sample;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SampleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Sample::insert([
            [
                'name' => 'Hip Hop Sample',
                'before_audio' => 'sample-audios/before-1.mp3',
                'after_audio' => 'sample-audios/after-1.mp3',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'R&B Sample',
                'before_audio' => 'sample-audios/before-2.mp3',
                'after_audio' => 'sample-audios/after-2.mp3',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pop/Rock Sample',
                'before_audio' => 'sample-audios/before-3.mp3',
                'after_audio' => 'sample-audios/after-3.mp3',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Metal Rock Sample',
                'before_audio' => 'sample-audios/before-4.mp3',
                'after_audio' => 'sample-audios/after-4.mp3',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
