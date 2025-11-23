<?php

namespace App\Repositories;

use App\Models\Availability;
use App\Models\Professor;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repositório de Disponibilidade de Horários
 * 
 * Encapsula o acesso aos dados de disponibilidade no banco de dados,
 * providenciando métodos para gerenciar as regras de horários de professores.
 */
class AvailabilityRepository
{
    protected $model;

    public function __construct(Availability $model)
    {
        $this->model = $model;
    }

    /**
     * Remove todas as disponibilidades de um professor.
     * 
     * Útil para atualizar as regras de disponibilidade:
     * remove todas as antigas e cria novas.
     * 
     * @param int $professorId ID do professor
     * @return bool Número de registros deletados
     */
    public function deleteByProfessorId(int $professorId): bool
    {
        return $this->model->where('professor_id', $professorId)->delete();
    }

    /**
     * Cria um novo registro de disponibilidade.
     * 
     * @param array $data Dados de disponibilidade (professor_id, day_of_week, start_time, end_time)
     * @return Availability Disponibilidade criada
     */
    public function create(array $data): Availability
    {
        return $this->model->create($data);
    }

    /**
     * Busca todas as disponibilidades de um professor específico.
     * 
     * @param int $professorId ID do professor
     * @return Collection Coleção de regras de disponibilidade por dia da semana
     */
    public function findByProfessorId(int $professorId): Collection
    {
        return $this->model->where('professor_id', $professorId)->get();
    }
}