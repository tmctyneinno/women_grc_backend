<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class TimezoneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = database_path('seeders/sql/timezone.sql');

        if (!File::exists($path)) {
            $this->command->error('Timezone SQL file not found!');
            return;
        }

       try {
            DB::unprepared(File::get($path));
            $this->command->info('Timezones seeded successfully.');
        } catch (\Throwable $e) {
            $this->command->warn('Timezones already exist. Skipping seeding.');
        }
    }
}
