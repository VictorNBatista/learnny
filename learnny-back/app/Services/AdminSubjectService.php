<?php

namespace App\Services;

use App\Repositories\AdminSubjectRepository;
use Illuminate\Validation\ValidationException;

/**
 * Serviço de Matérias (Gerenciamento Admin)
 * 
 * Encapsula a lógica de negócio para operações com matérias,
 * incluindo validações de dados.
 */
class AdminSubjectService
{
    protected $repository;

    public function __construct(AdminSubjectRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Lista todas as matérias cadastradas.
     * 
     * @return \Illuminate\Database\Eloquent\Collection Coleção de matérias
     */
    public function listSubjects()
    {
        return $this->repository->getAll();
    }

    /**
     * Cria uma nova matéria.
     * 
     * @param array $data Dados da matéria (nome)
     * @return \App\Models\Subject Matéria criada
     * @throws ValidationException Se o nome não for fornecido
     */
    public function createSubject(array $data)
    {
        if (empty($data['name'])) {
            throw ValidationException::withMessages([
                'name' => 'O campo nome é obrigatório.'
            ]);
        }

        return $this->repository->create($data);
    }

    /**
     * Busca uma matéria pelo ID.
     * 
     * @param int $id ID da matéria
     * @return \App\Models\Subject Matéria encontrada
     */
    public function getSubject($id)
    {
        return $this->repository->findById($id);
    }

    /**
     * Atualiza os dados de uma matéria existente.
     * 
     * @param int $id ID da matéria
     * @param array $data Dados a atualizar
     * @return \App\Models\Subject Matéria atualizada
     */
    public function updateSubject($id, array $data)
    {
        $subject = $this->repository->findById($id);
        return $this->repository->update($subject, $data);
    }

    /**
     * Exclui uma matéria do sistema.
     * 
     * @param int $id ID da matéria
     * @return \App\Models\Subject Matéria excluída
     */
    public function deleteSubject($id)
    {
        $subject = $this->repository->findById($id);
        return $this->repository->delete($subject);
    }
}
