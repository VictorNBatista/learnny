<?php

namespace App\Http\Controllers;

use App\Models\Professor;
use App\Http\Requests\ProfessorCreateRequest;
use App\Http\Requests\ProfessorUpdateRequest;
use App\Services\ProfessorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            'professors' => $professors
        ]);
    }

    public function store(ProfessorCreateRequest $request)
    {
        $professor = $this->professorService->createProfessor($request->validated());

        return response()->json([
            'status' => 201,
            'message' => 'Professor cadastrado com sucesso!',
            'professor' => $professor
        ]);
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
            'professor' => $professor
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
            'professor' => $professor
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
        $professor = Professor::where('status', 'pending')->get();

        return response()->json([
            'success' => true,
            'data' => $professor
        ], 200);
    }

    /**
     * Aprovar professor
     */
    public function approve($id)
    {
        $professor = Professor::findOrFail($id);
        $professor->status = 'approved';
        $professor->save();

        return response()->json([
            'success' => true,
            'message' => 'Professor aprovado com sucesso',
            'data' => $professor
        ], 200);
    }

    /**
     * Reprovar professor
     */
    public function reject($id)
    {
        $professor = Professor::findOrFail($id);
        $professor->status = 'rejected';
        $professor->save();

        return response()->json([
            'success' => true,
            'message' => 'Professor reprovado com sucesso',
            'data' => $professor
        ], 200);
    }

}
