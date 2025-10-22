<?php

namespace App\Services;

use App\Repositories\UserRepository;
use App\Services\MoodleService; // <-- IMPORTANTE: Garanta que este 'use' está aqui
use Illuminate\Support\Facades\Log;

class UserService
{
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }


    public function createUser(array $data)
    {
        // 1. Guarda a senha original que o Moodle precisa.
        $plainTextPassword = $data['password'];

        // 2. Cria o usuário no banco de dados do Learnny.
        $user = $this->userRepository->create($data);

        // 3. Se a criação no Learnny foi bem-sucedida, tenta criar no Moodle.
        if ($user) {
            try {
                // Chama o MoodleService diretamente.
                app(MoodleService::class)->provisionUser($user, $plainTextPassword);
            } catch (\Exception $e) {
                // Se a chamada ao Moodle falhar, registra o erro.
                // O usuário no Learnny JÁ FOI CRIADO. Podemos decidir o que fazer depois.
                Log::critical('Usuário criado no Learnny, mas falhou ao provisionar no Moodle.', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $user;
    }
    
    // Cole aqui seus outros métodos (listUsers, findById, etc.)
    public function listUsers()
    {
        return $this->userRepository->getAll();
    }

    public function findUserById($id)
    {
        return $this->userRepository->findById($id);
    }

    public function updateUser($id, array $data)
    {
        $user = $this->userRepository->findById($id);
        if (!$user) { return null; }
        $this->userRepository->update($user, $data);
        return $user;
    }

    public function deleteUser($id)
    {
        $user = $this->userRepository->findById($id);
        if (!$user) { return null; }
        $this->userRepository->delete($user);
        return $user;
    }
}