<?php

/**
 * MICS HUB source: database seeders DatabaseSeeder. See docs/file-reference.md for its full responsibility.
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(ReferenceDataSeeder::class);

        if (app()->environment(['local', 'testing'])) {
            $this->call(SchoolDataSeeder::class);
        }
    }
}
