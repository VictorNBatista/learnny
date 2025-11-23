<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate([
            'name'     => 'Victor Hugo',
            'username' => 'victornbatista',
            'email'    => 'victor@email.com',
            'password' => Hash::make('Victor@123'),
            'contact'  => '62999764714',
            'photo_url' => 'https://imgur.com/uNVSBbx.png'
        ]);
    }
}