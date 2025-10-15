<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http; 
use Illuminate\Support\Facades\Log;  

class MoodleService
{
    public function provisionUser(User $user, string $plainTextPassword): void
    {
        $moodleUrl = config('services.moodle.url');
        $token = config('services.moodle.token');

        $nameParts = explode(' ', $user->name, 2);
        $firstName = $nameParts[0];
        $lastName = $nameParts[1] ?? ' ';

        $usersToCreate = [
            [
                'username' => $user->username, // Certifique-se que o model User tem o campo username
                'password' => $plainTextPassword,
                'firstname' => $firstName,
                'lastname' => $lastName,
                'email' => $user->email,
                'auth' => 'manual',
                'lang' => 'pt_br',
            ]
        ];

        $response = Http::asForm()->post("{$moodleUrl}/webservice/rest/server.php", [
            'wstoken' => $token,
            'wsfunction' => 'core_user_create_users',
            'moodlewsrestformat' => 'json',
            'users' => $usersToCreate,
        ]);

        if ($response->successful() && !$response->json('exception')) {
            $moodleUser = $response->json()[0];
            $user->moodle_id = $moodleUser['id']; // Certifique-se que a tabela users tem a coluna moodle_id
            $user->save();
            Log::info('Usuário provisionado no Moodle com sucesso.', ['user_id' => $user->id, 'moodle_id' => $moodleUser['id']]);
        } else {
            Log::error('Falha ao provisionar usuário no Moodle.', ['user_id' => $user->id, 'response' => $response->body()]);
        }
    }
}