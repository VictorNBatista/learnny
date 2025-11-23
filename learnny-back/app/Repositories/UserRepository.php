<?php

namespace App\Repositories;

use App\Models\User;

/**
 * Repositório de Usuários
 * 
 * Encapsula o acesso aos dados de usuários no banco de dados,
 * providenciando métodos para operações CRUD.
 */
class UserRepository
{
    /**
     * Obtém todos os usuários cadastrados com paginação.
     * 
     * Inclui usuários deletados (soft delete).
     * 
     * @return \Illuminate\Pagination\Paginator Coleção paginada de usuários (15 por página)
     */
    public function getAll()
    {
        return User::select('id', 'name', 'email', 'contact')
                    ->withTrashed()
                    ->paginate(15);
    }

    /**
     * Busca um usuário pelo ID.
     * 
     * @param int $id ID do usuário
     * @return User|null Usuário encontrado ou null
     */
    public function findById($id)
    {
        return User::find($id);
    }

    /**
     * Cria um novo usuário com os dados fornecidos.
     * 
     * @param array $data Dados do usuário (nome, email, password, etc)
     * @return User Usuário criado
     */
    public function create(array $data)
    {
        return User::create($data);
    }

    /**
     * Atualiza os dados de um usuário existente.
     * 
     * @param User $user Usuário a atualizar
     * @param array $data Dados a atualizar
     * @return bool True se bem-sucedido
     */
    public function update(User $user, array $data)
    {
        return $user->update($data);
    }

    /**
     * Exclui (soft delete) um usuário.
     * 
     * @param User $user Usuário a excluir
     * @return bool True se bem-sucedido
     */
    public function delete(User $user)
    {
        return $user->delete();
    }
}
