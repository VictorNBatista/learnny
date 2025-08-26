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
     * Login do professor
     */
   public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (!Auth::guard('professor')->attempt($request->only('email', 'password'))) {
            return response()->json([
                'status' => 401,
                'message' => 'Credenciais inválidas!'
            ]);
        }

        $professor = Auth::guard('professor')->user();
        $token = $professor->createToken('ProfessorToken')->accessToken;

        return response()->json([
            'status' => 200,
            'message' => 'Login realizado com sucesso!',
            'token' => $token,
            'professor' => $professor
        ]);
    }
}
