<?php

namespace App\Services;

use App\Repositories\AdminRepository;
use Illuminate\Support\Facades\Hash;

/**
 * Serviço de Administradores
 * 
 * Encapsula a lógica de negócio para operações com administradores,
 * incluindo hash de senhas antes de persisti-las.
 */
class AdminService
{
    protected $adminRepository;

    public function __construct(AdminRepository $adminRepository)
    {
        $this->adminRepository = $adminRepository;
    }

    /**
     * Obtém todos os administradores cadastrados.
     * 
     * @return \Illuminate\Database\Eloquent\Collection Coleção de administradores
     */
    public function getAll()
    {
        return $this->adminRepository->getAll();
    }

    /**
     * Busca um administrador pelo ID.
     * 
     * @param int $id ID do administrador
     * @return \App\Models\Admin|null Administrador encontrado ou null
     */
    public function findById($id)
    {
        return $this->adminRepository->findById($id);
    }

    /**
     * Cria um novo administrador com senha hasheada.
     * 
     * @param array $data Dados do administrador (nome, email, password, etc)
     * @return \App\Models\Admin Administrador criado
     */
    public function create(array $data)
    {
        // Hash a senha se fornecida
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return $this->adminRepository->create($data);
    }

    /**
     * Atualiza dados de um administrador existente.
     * 
     * @param int $id ID do administrador
     * @param array $data Dados a atualizar
     * @return \App\Models\Admin Administrador atualizado
     */
    public function update($id, array $data)
    {
        // Hash a nova senha se fornecida
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return $this->adminRepository->update($id, $data);
    }

    /**
     * Exclui um administrador do sistema.
     * 
     * @param int $id ID do administrador
     * @return \App\Models\Admin Administrador excluído
     */
    public function delete($id)
    {
        return $this->adminRepository->delete($id);
    }
}
