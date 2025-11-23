<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subject;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            'Matemática',
            'Português',
            'Física',
            'Química',
            'Biologia',
            'História',
            'Geografia',
            'Inglês',
            'Redação',
            'Literatura',
            'Filosofia',
            'Sociologia',
            'Programação',
        ];

        foreach ($subjects as $name) {
            Subject::firstOrCreate(['name' => $name]);
        }
    }
}