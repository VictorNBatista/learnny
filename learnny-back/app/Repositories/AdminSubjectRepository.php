<?php

namespace App\Repositories;

use App\Models\Subject;

/**
 * Repositório de Matérias (Gerenciamento Admin)
 * 
 * Encapsula o acesso aos dados de matérias no banco de dados,
 * providenciando métodos para operações CRUD.
 */
class AdminSubjectRepository
{
    /**
     * Obtém todas as matérias cadastradas.
     * 
     * @return \Illuminate\Database\Eloquent\Collection Coleção de matérias
     */
    public function getAll()
    {
        return Subject::all();
    }

    /**
     * Busca uma matéria pelo ID.
     * 
     * @param int $id ID da matéria
     * @return Subject Matéria encontrada
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findById($id)
    {
        return Subject::findOrFail($id);
    }

    /**
     * Cria uma nova matéria com os dados fornecidos.
     * 
     * @param array $data Dados da matéria (nome, etc)
     * @return Subject Matéria criada
     */
    public function create(array $data)
    {
        return Subject::create($data);
    }

    /**
     * Atualiza os dados de uma matéria existente.
     * 
     * @param Subject $subject Matéria a atualizar
     * @param array $data Dados a atualizar
     * @return Subject Matéria atualizada
     */
    public function update(Subject $subject, array $data)
    {
        $subject->update($data);
        return $subject;
    }

    /**
     * Exclui uma matéria do sistema.
     * 
     * @param Subject $subject Matéria a excluir
     * @return bool True se bem-sucedido
     */
    public function delete(Subject $subject)
    {
        return $subject->delete();
    }
}
