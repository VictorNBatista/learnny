<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Professor;
use App\Models\Subject;
use Illuminate\Support\Facades\Hash;

class ProfessorSeeder extends Seeder
{
    public function run(): void
    {
        $faker = \Faker\Factory::create('pt_BR');

        $subjectIds = Subject::inRandomOrder()->take(3)->pluck('id');

        $professor = Professor::create([
            'name' => $faker->name,
            'email' => $faker->unique()->safeEmail,
            'password' => Hash::make('SenhaSegura123!'), // senha simulando os requisitos
            'photo_url' => $faker->optional()->imageUrl(),
            'contact' => $faker->phoneNumber,
            'biography' => $faker->paragraph,
            'price' => $faker->randomFloat(2, 50, 300), // valor entre R$50,00 e R$300,00
        ]);

        // Vincula o professor com as matérias
        $professor->subjects()->attach($subjectIds);
    }
}
