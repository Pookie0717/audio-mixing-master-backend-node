<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'first_name' => 'Ali',
            'last_name' => 'Jawaid',
            'email' => 'admin@gmail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('admin'),
        ])->assignRole('admin');

        User::create([
            'first_name' => 'Engineer',
            'last_name' => 'AJ',
            'email' => 'engineer@gmail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('admin'),
        ])->assignRole('engineer');

        User::create([
            'first_name' => 'User',
            'last_name' => '1',
            'email' => 'alijawaidofficial.pk@gmail.com',
            'email_verified_at' => now(),
            'phone_number' => '12345678',
            'password' => Hash::make('12345678'),
        ])->assignRole('user');
    }
}
