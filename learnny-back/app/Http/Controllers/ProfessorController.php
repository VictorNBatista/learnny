<?php

namespace App\Http\Controllers;
use App\Models\Professor;
use App\Http\Requests\ProfessorCreateRequest;
use App\Http\Requests\ProfessorUpdateRequest;
use App\Services\ProfessorService;
use Illuminate\Http\Request;

/**
 * Controlador de Professores
 * 
 * Gerencia operações CRUD de professores na plataforma, incluindo
 * aprovação, rejeição e provisão de contas no Moodle.
 * Delega a lógica de negócio para ProfessorService.
 */
class ProfessorController extends Controller
{
    protected $professorService;

    public function __construct(ProfessorService $professorService)
    {
        $this->professorService = $professorService;
    }

    /**
     * Lista todos os professores aprovados.
     * 
     * @return \Illuminate\Http\JsonResponse JSON com lista de professores
     */
    public function index()
    {
        $professors = $this->professorService->listProfessors();

        return response()->json([
            'status' => 200,
            'message' => 'Professores encontrados!',
            'data' => $professors
        ]);
    }

    /**
     * Cria um novo professor e provisiona conta no Moodle.
     * 
     * A criação é uma operação transacional que envolve:
     * 1. Salvar professor localmente no banco Learnny
     * 2. Provisionar conta no Moodle
     * 3. Criar cursos no Moodle para cada matéria
     * 
     * Se qualquer etapa falhar, toda a transação é revertida.
     * O professor inicia com status 'pending', aguardando aprovação de um administrador.
     * 
     * @param ProfessorCreateRequest $request
     * @return \Illuminate\Http\JsonResponse JSON com dados do professor criado ou erro 503 em caso de falha
     */
    public function store(ProfessorCreateRequest $request)
    {
        try {
            // Tenta criar o professor (e provisionar no Moodle)
            $professor = $this->professorService->createProfessor($request->validated());

            // Resposta de sucesso com status HTTP 201 (Created)
            return response()->json([
                'status' => 201,
                'message' => 'Professor cadastrado com sucesso! Aguarde a aprovação de um administrador.',
                'data' => $professor
            ], 201);

        } catch (\Exception $e) {
            // Captura exceções do service (ex: falha ao provisionar no Moodle)
            // Retorna status 503 (Service Unavailable) para erros de API externa
            return response()->json([
                'status' => 503,
                'message' => $e->getMessage()
            ], 503);
        }
    }

    /**
     * Obtém os dados de um professor específico pelo ID.
     * 
     * @param int $id ID do professor
     * @return \Illuminate\Http\JsonResponse JSON com dados do professor ou erro 404
     */
    public function show($id)
    {
        $professor = $this->professorService->findProfessorById($id);

        if (!$professor) {
            return response()->json([
                'status' => 404,
                'message' => 'Professor não encontrado!'
            ]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Professor encontrado!',
            'data' => $professor
        ]);
    }

    /**
     * Obtém os dados do professor autenticado.
     * 
     * @return \Illuminate\Http\JsonResponse JSON com dados do professor autenticado ou erro 404
     */
    public function me()
    {
        $professor = $this->professorService->findMe();
        
        if (!$professor) {
            return response()->json([
                'status' => 404,
                'message' => 'Professor não encontrado!'
            ]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Professor encontrado!',
            'data' => $professor
        ]);
    }

    /**
     * Atualiza os dados de um professor existente.
     * 
     * @param ProfessorUpdateRequest $request
     * @param int $id ID do professor a atualizar
     * @return \Illuminate\Http\JsonResponse JSON com dados do professor atualizado ou erro 404
     */
    public function update(ProfessorUpdateRequest $request, $id)
    {
        $professor = $this->professorService->updateProfessor($id, $request->validated());

        if (!$professor) {
            return response()->json([
                'status' => 404,
                'message' => 'Professor não encontrado!'
            ]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Professor atualizado com sucesso!',
            'data' => $professor
        ]);
    }

    /**
     * Exclui um professor do sistema.
     * 
     * @param int $id ID do professor a excluir
     * @return \Illuminate\Http\JsonResponse JSON com confirmação ou erro 404
     */
    public function destroy($id)
    {
        $professor = $this->professorService->deleteProfessor($id);

        if (!$professor) {
            return response()->json([
                'status' => 404,
                'message' => 'Professor não encontrado!'
            ]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Professor excluído com sucesso!'
        ]);
    }

    /**
     * Lista professores pendentes de aprovação.
     * 
     * Retorna uma coleção de professores cujo status é 'pending',
     * permitindo que administradores gerenciem aprovações.
     * 
     * @return \Illuminate\Http\JsonResponse JSON com lista de professores pendentes
     */
    public function pending()
    {
        $professors = $this->professorService->listPendingProfessors();

        if ($professors->isEmpty()) {
        return response()->json([
            'status' => 404,
            'message' => 'Nenhum professor pendente encontrado!'
        ]);
        }   

        return response()->json([
            'status' => 200,
            'message' => 'Professores pendentes encontrados!',
            'data' => $professors
        ]);
    }

    /**
     * Aprova um professor e ativa sua conta.
     * 
     * Altera o status do professor de 'pending' para 'approved',
     * permitindo que ele realize login e acesse a plataforma.
     * 
     * @param int $id ID do professor a aprovar
     * @return \Illuminate\Http\JsonResponse JSON com confirmação ou erro 404
     */
    public function approve($id)
    {
        $professor = $this->professorService->approveProfessor($id);

        if (!$professor) {
            return response()->json([
                'status' => 404,
                'message' => 'Professor não encontrado!'
            ]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Professor aprovado com sucesso!',
            'data' => $professor
        ]);
    }

    /**
     * Rejeita um professor e desativa sua conta.
     * 
     * Altera o status do professor de 'pending' para 'rejected',
     * impedindo que ele realize login e acesse a plataforma.
     * 
     * @param int $id ID do professor a rejeitar
     * @return \Illuminate\Http\JsonResponse JSON com confirmação ou erro 404
     */
    public function reject($id)
    {
        $professor = $this->professorService->rejectProfessor($id);

        if (!$professor) {
            return response()->json([
                'status' => 404,
                'message' => 'Professor não encontrado!'
            ]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Professor reprovado com sucesso!',
            'data' => $professor
        ]);
    }
}
