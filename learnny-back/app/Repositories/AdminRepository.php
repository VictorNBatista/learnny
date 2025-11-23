<?php

namespace App\Repositories;

use App\Models\Admin;

/**
 * Repositório de Administradores
 * 
 * Encapsula o acesso aos dados de administradores no banco de dados,
 * providenciando métodos para operações CRUD.
 */
class AdminRepository
{
    /**
     * Obtém todos os administradores cadastrados.
     * 
     * @return \Illuminate\Database\Eloquent\Collection Coleção de administradores
     */
    public function getAll()
    {
        return Admin::all();
    }

    /**
     * Busca um administrador pelo ID.
     * 
     * @param int $id ID do administrador
     * @return Admin|null Administrador encontrado ou null
     */
    public function findById($id)
    {
        return Admin::find($id);
    }

    /**
     * Cria um novo administrador com os dados fornecidos.
     * 
     * @param array $data Dados do administrador (email, password, etc)
     * @return Admin Administrador criado
     */
    public function create(array $data)
    {
        return Admin::create($data);
    }

    /**
     * Atualiza os dados de um administrador existente.
     * 
     * @param int $id ID do administrador
     * @param array $data Dados a atualizar
     * @return Admin|null Administrador atualizado ou null se não encontrado
     */
    public function update($id, array $data)
    {
        $admin = Admin::find($id);
        if (!$admin) {
            return null;
        }

        $admin->update($data);
        return $admin;
    }

    /**
     * Exclui um administrador do sistema.
     * 
     * @param int $id ID do administrador
     * @return Admin|null Administrador excluído ou null se não encontrado
     */
    public function delete($id)
    {
        $admin = Admin::find($id);
        if (!$admin) {
            return null;
        }

        $admin->delete();
        return $admin;
    }
}
