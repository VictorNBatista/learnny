<?php 

namespace App\Services;

use App\Repositories\ProfessorRepository;
use App\Models\Professor;
use Illuminate\Support\Facades\Hash;

class ProfessorService
{
    protected $professorRepository;

    public function __construct(ProfessorRepository $professorRepository)
    {
        $this->professorRepository = $professorRepository;
    }

    public function listProfessors()
    {
        return $this->professorRepository->getAll();
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
    }

    public function findProfessorById($id)
    {
        return $this->professorRepository->findById($id);
    }

    public function updateProfessor($id, array $data)
    {
        $professor = $this->professorRepository->findById($id);

        if (!$professor) {
            return null;
        }

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $this->professorRepository->update($professor, $data);
        return $professor;
    }

    public function deleteProfessor($id)
    {
        $professor = $this->professorRepository->findById($id);

        if (!$professor) {
            return null;
        }

        $this->professorRepository->delete($professor);
        return $professor;
    }
}
