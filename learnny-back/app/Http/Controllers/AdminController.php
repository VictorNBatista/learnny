<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\AdminCreateRequest;
use App\Http\Requests\AdminUpdateRequest;
use App\Services\AdminService;

class AdminController extends Controller
{
    protected $adminService;

    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
    }

    public function index()
    {
        $admins = $this->adminService->getAll();

        return response()->json([
            'status' => 200,
            'message' => 'Admins encontrados!',
            'data' => $admins
        ]);
    }

    public function store(AdminCreateRequest $request)
    {
        $admin = $this->adminService->create($request->validated());

        return response()->json([
            'status' => true,
            'message' => 'Administrador criado com sucesso!',
            'data' => $admin
        ], 201);
    }

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
