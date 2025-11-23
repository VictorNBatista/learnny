<?php

namespace App\Services;

use App\Services\MoodleService;
use App\Repositories\ProfessorRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Serviço de Professores
 * 
 * Encapsula a lógica de negócio para operações com professores,
 * incluindo integração transacional com o Moodle para provisionamento.
 */
class ProfessorService
{
    protected $professorRepository;
    protected $moodleService;

    public function __construct(
        ProfessorRepository $professorRepository, 
        MoodleService $moodleService
    ) {
        $this->professorRepository = $professorRepository;
        $this->moodleService = $moodleService;
    }

    /**
     * Obtém todos os professores aprovados.
     * 
     * @return \Illuminate\Database\Eloquent\Collection Coleção de professores
     */
    public function listProfessors()
    {
        return $this->professorRepository->getAll();
    }

    /**
     * Busca um professor pelo ID.
     * 
     * @param int $id ID do professor
     * @return \App\Models\Professor|null Professor encontrado ou null
     */
    public function findProfessorById($id)
    {
        return $this->professorRepository->findById($id);
    }

    /**
     * Busca o professor autenticado pelo ID do token.
     * 
     * @return \App\Models\Professor|null Professor autenticado ou null
     */
    public function findMe()
    {
        $professorId = auth()->id();
        return $this->professorRepository->findById($professorId);
    }

    /**
     * Cria um novo professor com operação transacional.
     * 
     * Processo transacional:
     * 1. Cria professor no banco local
     * 2. Associa matérias ao professor
     * 3. Provisiona conta no Moodle
     * 4. Cria cursos no Moodle para cada matéria
     * 
     * Se qualquer etapa falhar, toda a transação é revertida,
     * garantindo consistência entre Learnny e Moodle.
     * 
     * @param array $data Dados do professor (nome, email, password, subjects, etc)
     * @return \App\Models\Professor Professor criado
     * @throws Exception Se falhar em qualquer etapa
     */
    public function createProfessor(array $data)
    {
        // Verifica se a senha foi fornecida
        if (!isset($data['password'])) {
            throw new Exception('O campo senha é obrigatório para criar professor.');
        }
        
        // Armazena a senha em texto plano para o Moodle
        $plainTextPassword = $data['password'];

        // Hash da senha para armazenar no banco local
        $data['password'] = Hash::make($plainTextPassword);

        return DB::transaction(function () use ($data, $plainTextPassword) {
            try {
                // Extrai as matérias (subjects) dos dados
                $subjects = $data['subjects'] ?? [];
                unset($data['subjects']);

                // Define status inicial como pendente
                $data['status'] = 'pending';

                // Cria o professor no banco local
                $professor = $this->professorRepository->create($data);

                // Associa as matérias ao professor
                if (!empty($subjects)) {
                    $professor->subjects()->sync($subjects);
                }

                // Log de início do provisionamento
                Log::info('Iniciando provisionamento do Moodle para o professor: ' . $professor->email);
                
                // Provisiona conta no Moodle com as matérias
                $moodleSuccess = $this->moodleService->provisionTeacher(
                    $professor, 
                    $plainTextPassword, 
                    $subjects
                );

                // Verifica se o provisionamento foi bem-sucedido
                if (!$moodleSuccess) {
                    // Se falhar, força rollback de toda a transação
                    throw new Exception('Falha ao provisionar professor no Moodle. Transação revertida.');
                }

                return $professor;

            } catch (Exception $e) {
                // Registra o erro completo para debugging
                Log::error('Erro ao criar professor (Moodle/Local): '.$e->getMessage(), [
                    'data' => $data
                ]);
                // Re-lança a exceção para o DB::transaction fazer o rollback
                throw $e;
            }
        });
    }

    /**
     * Atualiza dados de um professor existente.
     * 
     * @param int $id ID do professor
     * @param array $data Dados a atualizar
     * @return \App\Models\Professor Professor atualizado
     * @throws Exception
     */
    public function updateProfessor($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            try {
                // Extrai as matérias dos dados
                $subjects = $data['subjects'] ?? [];
                unset($data['subjects']);
                
                // Atualiza o professor
                $professor = $this->professorRepository->update($id, $data);
                
                // Sincroniza as matérias
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

    /**
     * Exclui um professor do sistema.
     * 
     * @param int $id ID do professor
     * @return \App\Models\Professor Professor excluído
     */
    public function deleteProfessor($id)
    {
        // Nota: Integração com Moodle para deletar/suspender pode ser implementada aqui
        return $this->professorRepository->delete($id);
    }

    /**
     * Lista todos os professores pendentes de aprovação.
     * 
     * @return \Illuminate\Database\Eloquent\Collection Coleção de professores pendentes
     */
    public function listPendingProfessors()
    {
        return $this->professorRepository->getPendingProfessors();
    }

    /**
     * Aprova um professor e ativa sua conta.
     * 
     * @param int $id ID do professor
     * @return \App\Models\Professor Professor aprovado
     */
    public function approveProfessor($id)
    {
        // Nota: Integração com Moodle para ativar conta pode ser implementada aqui
        return $this->professorRepository->updateStatus($id, 'approved');
    }

    /**
     * Rejeita um professor e desativa sua conta.
     * 
     * @param int $id ID do professor
     * @return \App\Models\Professor Professor rejeitado
     */
    public function rejectProfessor($id)
    {
        return $this->professorRepository->updateStatus($id, 'rejected');
    }
}