<?php

namespace App\Services;

// 1. IMPORTAR O MOODLE SERVICE
use App\Services\MoodleService; 
use App\Repositories\ProfessorRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ProfessorService
{
    protected $professorRepository;
    // 2. DECLARAR O MOODLE SERVICE
    protected $moodleService; 

    // 3. INJETAR O MOODLE SERVICE NO CONSTRUTOR
    public function __construct(
        ProfessorRepository $professorRepository, 
        MoodleService $moodleService
    ) {
        $this->professorRepository = $professorRepository;
        $this->moodleService = $moodleService; // Atribuir
    }

    public function listProfessors()
    {
        return $this->professorRepository->getAll();
    }

    public function findProfessorById($id)
    {
        return $this->professorRepository->findById($id);
    }

    public function findMe()
    {
        $professorId = auth()->id();
        return $this->professorRepository->findById($professorId);
    }

    /**
     * MÉTODO PRINCIPAL MODIFICADO
     */
    public function createProfessor(array $data)
    {
        // 4. Precisamos da senha em texto plano para o Moodle
        // Vamos assumir que $data['password'] vem do controller como texto plano
        if (!isset($data['password'])) {
            throw new Exception('O campo senha é obrigatório para criar professor.');
        }
        $plainTextPassword = $data['password'];

        // 5. Fazemos o Hash da senha AGORA, para salvar no banco local
        $data['password'] = Hash::make($plainTextPassword);

        return DB::transaction(function () use ($data, $plainTextPassword) {
            try {
                $subjects = $data['subjects'] ?? [];
                unset($data['subjects']);

                $data['status'] = 'pending'; // Como no seu original

                // 6. Cria o professor no banco local (Learnny)
                $professor = $this->professorRepository->create($data);

                if (!empty($subjects)) {
                    $professor->subjects()->sync($subjects);
                }

                // 7. CHAMA O MOODLE SERVICE
                // Passa o objeto $professor recém-criado e a senha em texto plano
                Log::info('Iniciando provisionamento do Moodle para o professor: ' . $professor->email);
                
                $moodleSuccess = $this->moodleService->provisionTeacher(
                    $professor, 
                    $plainTextPassword, 
                    $subjects // Passa os IDs das matérias
                );

                // 8. VERIFICA A FALHA
                if (!$moodleSuccess) {
                    // Se o provisionamento do Moodle falhar, nós forçamos
                    // o rollback da transação inteira.
                    throw new Exception('Falha ao provisionar professor no Moodle. Transação revertida.');
                }

                // Se tudo deu certo, retorna o professor
                return $professor;

            } catch (Exception $e) {
                // Loga o erro específico
                Log::error('Erro ao criar professor (Moodle/Local): '.$e->getMessage(), [
                    'data' => $data
                ]);
                // Re-lança a exceção para garantir que o DB::transaction faça o rollback
                throw $e;
            }
        });
    }

    public function updateProfessor($id, array $data)
    {
        // (Lógica original mantida)
        // ...
        // (Se precisar atualizar no Moodle, a lógica seria aqui)
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
        // (Aqui você também precisaria chamar o MoodleService para deletar/suspender)
        return $this->professorRepository->delete($id);
    }

    public function listPendingProfessors()
    {
        return $this->professorRepository->getPendingProfessors();
    }

    public function approveProfessor($id)
    {
        // (Aqui você poderia chamar o MoodleService para "des-suspender" o usuário)
        // Por enquanto, apenas cria o usuário no Moodle como ativo
        return $this->professorRepository->updateStatus($id, 'approved');
    }

    public function rejectProfessor($id)
    {
        return $this->professorRepository->updateStatus($id, 'rejected');
    }
}