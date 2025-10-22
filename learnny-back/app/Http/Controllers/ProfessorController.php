<?php

namespace App\Http\Controllers;
use App\Models\Professor;
use App\Http\Requests\ProfessorCreateRequest;
use App\Http\Requests\ProfessorUpdateRequest;
use App\Services\ProfessorService;
use Illuminate\Http\Request;

class ProfessorController extends Controller
{
    protected $professorService;

    public function __construct(ProfessorService $professorService)
    {
        $this->professorService = $professorService;
    }

    public function index()
    {
        $professors = $this->professorService->listProfessors();

        return response()->json([
            'status' => 200,
            'message' => 'Professores encontrados!',
            'data' => $professors
        ]);
    }

    public function store(ProfessorCreateRequest $request)
    {
        try {
            // Tenta criar o professor (e provisionar no Moodle)
            $professor = $this->professorService->createProfessor($request->validated());

            // Resposta de Sucesso
            return response()->json([
                'status' => 201,
                'message' => 'Professor cadastrado com sucesso! Aguarde a aprovação de um administrador.',
                'data' => $professor
            ], 201); // É uma boa prática passar o código de status aqui também

        } catch (\Exception $e) {
            
            // Captura a exceção lançada pelo service (ex: falha no Moodle)
            return response()->json([
                'status' => 503, // 503 (Service Unavailable) é bom para falhas em APIs externas
                'message' => $e->getMessage() // Ex: "Falha ao provisionar professor no Moodle. Transação revertida."
            ], 503);
        }
    }

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
     * Listar professores pendentes de aprovação
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
     * Aprovar professor
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
     * Reprovar professor
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
