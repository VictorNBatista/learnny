<?php

namespace App\Services;

use App\Repositories\UserRepository;
use App\Services\MoodleService;
use Illuminate\Support\Facades\Log;

/**
 * Serviço de Usuários (Alunos)
 * 
 * Encapsula a lógica de negócio para operações com usuários,
 * incluindo integração com o Moodle para provisionamento de contas.
 */
class UserService
{
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Cria um novo usuário no Learnny e provisiona conta no Moodle.
     * 
     * Processo:
     * 1. Cria registro do usuário no banco local
     * 2. Tenta provisionar conta no Moodle
     * 3. Se Moodle falhar, registra o erro mas não reverte a criação local
     * 
     * @param array $data Dados do usuário (nome, email, password, etc)
     * @return \App\Models\User Usuário criado
     */
    public function createUser(array $data)
    {
        // Armazena a senha em texto plano, pois o Moodle a necessita
        $plainTextPassword = $data['password'];

        // Cria o usuário no banco de dados local do Learnny
        $user = $this->userRepository->create($data);

        // Se a criação local foi bem-sucedida, tenta provisionar no Moodle
        if ($user) {
            try {
                // Chama o MoodleService para criar conta lá
                app(MoodleService::class)->provisionUser($user, $plainTextPassword);
            } catch (\Exception $e) {
                // Se o Moodle falhar, registra o erro crítico
                // O usuário no Learnny já foi criado, portanto não fazemos rollback
                Log::critical('Usuário criado no Learnny, mas falhou ao provisionar no Moodle.', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $user;
    }
    
    /**
     * Obtém todos os usuários cadastrados.
     * 
     * @return \Illuminate\Pagination\Paginator Coleção paginada de usuários
     */
    public function listUsers()
    {
        return $this->userRepository->getAll();
    }

    /**
     * Busca um usuário pelo ID.
     * 
     * @param int $id ID do usuário
     * @return \App\Models\User|null Usuário encontrado ou null
     */
    public function findUserById($id)
    {
        return $this->userRepository->findById($id);
    }

    /**
     * Atualiza dados de um usuário existente.
     * 
     * @param int $id ID do usuário
     * @param array $data Dados a atualizar
     * @return \App\Models\User|null Usuário atualizado ou null se não encontrado
     */
    public function updateUser($id, array $data)
    {
        $user = $this->userRepository->findById($id);
        if (!$user) { 
            return null; 
        }
        $this->userRepository->update($user, $data);
        return $user;
    }

    /**
     * Exclui um usuário do sistema.
     * 
     * @param int $id ID do usuário
     * @return \App\Models\User|null Usuário excluído ou null se não encontrado
     */
    public function deleteUser($id)
    {
        $user = $this->userRepository->findById($id);
        if (!$user) { 
            return null; 
        }
        $this->userRepository->delete($user);
        return $user;
    }
}