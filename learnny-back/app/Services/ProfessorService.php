<?php

namespace App\Services;

use App\Models\Professor;
use App\Repositories\ProfessorRepository;
use Illuminate\Support\Facades\DB;
use Exception;

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
        DB::beginTransaction();
        try {
            $subjects = $data['subjects'];
            unset($data['subjects']);

            $professor = $this->professorRepository->create($data);

            // Associa as matérias
            $professor->subjects()->sync($subjects);

            DB::commit();
            return $professor;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
        
        // return $this->professorRepository->create($data);
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
