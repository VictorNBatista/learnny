<?php  

namespace App\Services;

use App\Repositories\ProfessorRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

    public function findProfessorById($id)
    {
        return $this->professorRepository->findById($id);
    }

    public function createProfessor(array $data)
    {
        return DB::transaction(function () use ($data) {
            try {
                $subjects = $data['subjects'] ?? [];
                unset($data['subjects']);

                if (isset($data['password'])) {
                    $data['password'] = Hash::make($data['password']);
                }

                $data['status'] = 'pending';

                $professor = $this->professorRepository->create($data);

                if (!empty($subjects)) {
                    $professor->subjects()->sync($subjects);
                }

                return $professor;

            } catch (Exception $e) {
                Log::error('Erro ao criar professor: '.$e->getMessage(), [
                    'data' => $data
                ]);
                throw $e;
            }
        });
    }

    public function updateProfessor($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            try {
                $subjects = $data['subjects'] ?? [];
                unset($data['subjects']);

                $professor = $this->professorRepository->update($id, $data);

                if (!empty($subjects)) {
                    $professor->subjects()->sync($subjects);
                }

                return $professor;

            } catch (Exception $e) {
                Log::error('Erro ao atualizar professor: '.$e->getMessage(), [
                    'id'   => $id,
                    'data' => $data
                ]);
                throw $e;
            }
        });
    }

    public function deleteProfessor($id)
    {
        return $this->professorRepository->delete($id);
    }

    public function listPendingProfessors()
    {
        return $this->professorRepository->getPendingProfessors();
    }

    public function approveProfessor($id)
    {
        return $this->professorRepository->updateStatus($id, 'approved');
    }

    public function rejectProfessor($id)
    {
        return $this->professorRepository->updateStatus($id, 'rejected');
    }
}
