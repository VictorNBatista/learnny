<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(SubjectSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(ProfessorSeeder::class);
        $this->call(AdminSeeder::class);
    }
}