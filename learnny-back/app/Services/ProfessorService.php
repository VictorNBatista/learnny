<?php 

namespace App\Services;

use App\Repositories\ProfessorRepository;
use App\Models\Professor;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Exception;

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

    public function getProfessorById($id)
    {
        return $this->professorRepository->findById($id);
    }

    public function createProfessor(array $data)
    {
        DB::beginTransaction();
        try {
            // Se vier subjects, guarda, senão array vazio
            $subjects = $data['subjects'] ?? [];
            unset($data['subjects']);

            // Criptografa a senha
            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            // Cria professor
            $professor = $this->professorRepository->create($data);

            // Associa matérias (se houver)
            if (!empty($subjects)) {
                $professor->subjects()->sync($subjects);
            }

            DB::commit();
            return $professor;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
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