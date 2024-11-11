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
        $professors = $this->professorService->getAllProfessors();
        return response()->json([
            'status' => 200,
            'message' => 'Professores encontrados!',
            'professores' => $professors
        ]);
    }

    public function show($id)
    {
        $professor = $this->professorService->getProfessorById($id);
        return response()->json([
            'status' => 200,
            'message' => 'Professor encontrado!',
            'professor' => $professor
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

    public function update(ProfessorUpdateRequest $request, $id)
    {
        $professor = $this->professorService->updateProfessor($id, $request->validated());
        return response()->json([
            'status' => 200,
            'message' => 'Professor atualizado com sucesso!',
            'professor' => $professor
        ]);
    }

    public function destroy($id)
    {
        $this->professorService->deleteProfessor($id);
        return response()->json([
            'status' => 200,
            'message' => 'Professor excluído com sucesso!'
        ]);
    }
}
