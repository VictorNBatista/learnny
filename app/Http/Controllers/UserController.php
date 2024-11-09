<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserCreateRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\UserService;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index()
    {
        $users = $this->userService->listUsers();

        return response()->json([
            'status' => 200,
            'message' => 'Usuários encontrados!',
            'users' => $users
        ]);
    }

    public function store(UserCreateRequest $request)
    {
        $user = $this->userService->createUser($request->validated());

        return response()->json([
            'status' => 200,
            'message' => 'Usuário cadastrado com sucesso!',
            'user' => $user
        ]);
    }

    public function show($id)
    {
        $user = $this->userService->findUserById($id);

        if (!$user) {
            return response()->json([
                'status' => 404,
                'message' => 'Usuário não encontrado!'
            ]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Usuário encontrado!',
            'user' => $user
        ]);
    }

    public function update(UserUpdateRequest $request, $id)
    {
        $user = $this->userService->updateUser($id, $request->validated());

        if (!$user) {
            return response()->json([
                'status' => 404,
                'message' => 'Usuário não encontrado!'
            ]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Usuário atualizado com sucesso!',
            'user' => $user
        ]);
    }

    public function destroy($id)
    {
        $user = $this->userService->deleteUser($id);

        if (!$user) {
            return response()->json([
                'status' => 404,
                'message' => 'Usuário não encontrado!'
            ]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Usuário excluído com sucesso!'
        ]);
    }
}
