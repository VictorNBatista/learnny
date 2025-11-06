<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Utiliza firstOrCreate para evitar criar duplicatas se o seeder for rodado novamente
        // Busca pelo email, se não encontrar, cria com os dados do segundo array
        Admin::firstOrCreate(
            ['email' => 'admin@email.com'],
            [
                'name' => 'Administrador Learnny',
                'password' => Hash::make('admin1'),
            ]
        );
    }
}