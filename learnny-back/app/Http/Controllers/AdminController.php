<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use App\Http\Requests\AdminCreateRequest;
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
        return response()->json($this->adminService->getAll());
    }

    public function store(AdminCreateRequest $request)
    {

        $admin = $this->adminService->create($request->validated());

        return response()->json([
            'status' => 200,
            'message' => 'Admin cadastrado com sucesso!',
            'user' => $admin
        ]);
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
            'user' => $admin
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name'     => 'sometimes|string|max:255',
            'email'    => 'sometimes|email|unique:admins,email,' . $id,
            'password' => 'sometimes|string|min:6',
        ]);

        $admin = $this->adminService->update($id, $request->all());

        return response()->json($admin);
    }

    public function destroy($id)
    {
        $this->adminService->delete($id);
        
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
