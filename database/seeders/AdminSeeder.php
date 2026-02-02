<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Admin::updateOrCreate(
            ['email' => 'admin@example.com'], // prevents duplicates
            [
                'name'      => 'Super Admin',
                'email'     => 'admin@example.com',
                'password'  => Hash::make('password'), // change this!
                'role'      => 'admin',
                'is_active' => true,
            ]
        );
    }
}
