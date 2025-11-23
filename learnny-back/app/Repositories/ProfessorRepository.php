<?php

namespace App\Repositories;

use App\Models\Professor;

/**
 * Repositório de Professores
 * 
 * Encapsula o acesso aos dados de professores no banco de dados,
 * providenciando métodos para operações CRUD e consultas específicas.
 */
class ProfessorRepository
{
    protected $model;

    public function __construct(Professor $model)
    {
        $this->model = $model;
    }

    /**
     * Obtém todos os professores aprovados com suas matérias.
     * 
     * @return \Illuminate\Database\Eloquent\Collection Coleção de professores aprovados
     */
    public function getAll()
    {
        return $this->model
        ->with('subjects:id,name')
        ->where('status', 'approved')
        ->get();
    }

    /**
     * Busca um professor pelo ID com suas matérias.
     * 
     * @param int $id ID do professor
     * @return Professor|null Professor encontrado ou null
     */
    public function findById($id)
    {
        return $this->model->with('subjects:id,name')->find($id);
    }

    /**
     * Cria um novo professor com os dados fornecidos.
     * 
     * @param array $data Dados do professor (nome, email, password, etc)
     * @return Professor Professor criado
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * Atualiza os dados de um professor existente.
     * 
     * @param int $id ID do professor
     * @param array $data Dados a atualizar
     * @return Professor Professor atualizado
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function update($id, array $data)
    {
        $professor = $this->model->findOrFail($id);
        $professor->update($data);
        return $professor;
    }

    /**
     * Exclui um professor do sistema.
     * 
     * @param int $id ID do professor
     * @return Professor Professor excluído
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function delete($id)
    {
        $professor = $this->model->findOrFail($id);
        $professor->delete();
        return $professor;
    }

    /**
     * Obtém todos os professores pendentes de aprovação.
     * 
     * @return \Illuminate\Database\Eloquent\Collection Coleção de professores pendentes
     */
    public function getPendingProfessors()
    {
        return $this->model
        ->with('subjects:id,name')
        ->where('status', 'pending')
        ->get();
    }

    /**
     * Atualiza o status de um professor.
     * 
     * @param int $id ID do professor
     * @param string $status Novo status (pending, approved, rejected)
     * @return Professor|null Professor atualizado ou null se não encontrado
     */
    public function updateStatus($id, $status)
    {
        $professor = $this->model->find($id);
        if (!$professor) {
            return null;
        }

        $professor->status = $status;
        $professor->save();

        return $professor;
    }
}
