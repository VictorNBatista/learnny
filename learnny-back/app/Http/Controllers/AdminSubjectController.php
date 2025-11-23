<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Services\AdminSubjectService;
use Illuminate\Http\Request;

/**
 * Controlador de Matérias (Gerenciamento Admin)
 * 
 * Gerencia operações CRUD de matérias na plataforma.
 * Delega a lógica de negócio para AdminSubjectService.
 */
class AdminSubjectController extends Controller
{
    protected $adminSubjectService;

    public function __construct(AdminSubjectService $adminSubjectService)
    {
        $this->adminSubjectService = $adminSubjectService;
    }

    /**
     * Lista todas as matérias cadastradas.
     * 
     * @return \Illuminate\Http\JsonResponse JSON com lista de matérias
     */
    public function index()
    {
        return response()->json($this->adminSubjectService->listSubjects());
    }

    /**
     * Cria uma nova matéria.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse JSON com dados da matéria criada
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:subjects,name|max:255',
        ]);

        $subject = $this->adminSubjectService->createSubject($request->only('name'));
        return response()->json($subject, 201);
    }

    /**
     * Obtém os dados de uma matéria específica pelo ID.
     * 
     * @param int $id ID da matéria
     * @return \Illuminate\Http\JsonResponse JSON com dados da matéria
     */
    public function show($id)
    {
        return response()->json($this->adminSubjectService->getSubject($id));
    }

    /**
     * Atualiza os dados de uma matéria existente.
     * 
     * @param Request $request
     * @param int $id ID da matéria a atualizar
     * @return \Illuminate\Http\JsonResponse JSON com dados da matéria atualizada
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|unique:subjects,name,' . $id,
        ]);

        $subject = $this->adminSubjectService->updateSubject($id, $request->only('name'));
        return response()->json($subject);
    }

    /**
     * Exclui uma matéria do sistema.
     * 
     * @param int $id ID da matéria a excluir
     * @return \Illuminate\Http\JsonResponse JSON com confirmação de exclusão
     */
    public function destroy($id)
    {
        $this->adminSubjectService->deleteSubject($id);
        return response()->json(['message' => 'Matéria excluída com sucesso.']);
    }
}
