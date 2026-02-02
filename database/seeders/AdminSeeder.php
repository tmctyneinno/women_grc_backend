<?php

namespace Database\Seeders;
use App\Models\Admin;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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
                'email'     => 'admin@wgrcfp.com',
                'password'  => Hash::make('password'), // change this!
                'role'      => 'admin',
                'is_active' => true,
            ]
        );
    }
}
