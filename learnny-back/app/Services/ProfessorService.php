<?php

namespace App\Services;

use App\Repositories\ProfessorRepository;

class ProfessorService
{
    protected $professorRepository;

    public function __construct(ProfessorRepository $professorRepository)
    {
        $this->professorRepository = $professorRepository;
    }

    public function getAllProfessors()
    {
        return $this->professorRepository->getAll();
    }

    public function getProfessorById($id)
    {
        return $this->professorRepository->findById($id);
    }

    public function createProfessor(array $data)
    {
        return $this->professorRepository->create($data);
    }

    public function updateProfessor($id, array $data)
    {
        return $this->professorRepository->update($id, $data);
    }

    public function deleteProfessor($id)
    {
        return $this->professorRepository->delete($id);
    }
}
