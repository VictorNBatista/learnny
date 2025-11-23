<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\AdminCreateRequest;
use App\Http\Requests\AdminUpdateRequest;
use App\Services\AdminService;

/**
 * Controlador de Administradores
 * 
 * Gerencia operações CRUD de administradores na plataforma.
 * Delega a lógica de negócio para AdminService.
 */
class AdminController extends Controller
{
    protected $adminService;

    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
    }

    /**
     * Lista todos os administradores cadastrados.
     * 
     * @return \Illuminate\Http\JsonResponse JSON com lista de administradores
     */
    public function index()
    {
        $admins = $this->adminService->getAll();

        return response()->json([
            'status' => 200,
            'message' => 'Admins encontrados!',
            'data' => $admins
        ]);
    }

    /**
     * Cria um novo administrador.
     * 
     * @param AdminCreateRequest $request
     * @return \Illuminate\Http\JsonResponse JSON com dados do administrador criado
     */
    public function store(AdminCreateRequest $request)
    {
        $admin = $this->adminService->create($request->validated());

        return response()->json([
            'status' => true,
            'message' => 'Administrador criado com sucesso!',
            'data' => $admin
        ], 201);
    }

    /**
     * Obtém os dados de um administrador específico pelo ID.
     * 
     * @param int $id ID do administrador
     * @return \Illuminate\Http\JsonResponse JSON com dados do administrador ou erro 404
     */
    public function show($id)
    {
        $admin = $this->adminService->findById($id);

        if (!$admin) {
            return response()->json([
                'status' => 404,
                'message' => 'Admin não encontrado!'
            ]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Admin encontrado!',
            'data' => $admin
        ]);
    }

    /**
     * Atualiza os dados de um administrador existente.
     * 
     * @param AdminUpdateRequest $request
     * @param int $id ID do administrador a atualizar
     * @return \Illuminate\Http\JsonResponse JSON com dados do administrador atualizado ou erro 404
     */
    public function update(AdminUpdateRequest $request, $id)
    {
        $admin = $this->adminService->update($id, $request->validated());

        if (!$admin) {
            return response()->json([
                'status' => false,
                'message' => 'Administrador não encontrado.'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Administrador atualizado com sucesso!',
            'data' => $admin
        ]);
    }

    /**
     * Exclui um administrador do sistema.
     * 
     * @param int $id ID do administrador a excluir
     * @return \Illuminate\Http\JsonResponse JSON com confirmação ou erro 404
     */
    public function destroy($id)
    {
        $admin = $this->adminService->delete($id);

        if (!$admin) {
            return response()->json([
                'status' => 404,
                'message' => 'Admin não encontrado!'
            ]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Admin excluído com sucesso!'
        ]);
    }
}
