<?php

namespace App\Repositories;

use App\Models\Availability;
use App\Models\Professor;
use Illuminate\Database\Eloquent\Collection;

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
     * @param int $professorId
     * @return bool
     */
    public function deleteByProfessorId(int $professorId): bool
    {
        return $this->model->where('professor_id', $professorId)->delete();
    }

    /**
     * Cria um novo registro de disponibilidade.
     *
     * @param array $data
     * @return Availability
     */
    public function create(array $data): Availability
    {
        return $this->model->create($data);
    }

    /**
     * Busca todas as disponibilidades de um professor específico.
     *
     * @param int $professorId
     * @return Collection
     */
    public function findByProfessorId(int $professorId): Collection
    {
        return $this->model->where('professor_id', $professorId)->get();
    }
}