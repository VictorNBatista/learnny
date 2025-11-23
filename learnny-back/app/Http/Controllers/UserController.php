<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserCreateRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\UserService;

/**
 * Controlador de Usuários (Alunos)
 * 
 * Gerencia operações CRUD de usuários na plataforma.
 * Delega a lógica de negócio para UserService.
 */
class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Lista todos os usuários cadastrados.
     * 
     * @return \Illuminate\Http\JsonResponse JSON com lista de usuários
     */
    public function index()
    {
        $users = $this->userService->listUsers();

        return response()->json([
            'status' => 200,
            'message' => 'Usuários encontrados!',
            'users' => $users
        ]);
    }

    /**
     * Cria um novo usuário na plataforma.
     * 
     * Valida os dados, cria o usuário localmente no Learnny
     * e provisiona uma conta no Moodle.
     * 
     * @param UserCreateRequest $request
     * @return \Illuminate\Http\JsonResponse JSON com dados do usuário criado
     */
    public function store(UserCreateRequest $request)
    {
        $user = $this->userService->createUser($request->validated());

        return response()->json([
            'status' => 200,
            'message' => 'Usuário cadastrado com sucesso!',
            'user' => $user
        ]);
    }

    /**
     * Obtém os dados de um usuário específico pelo ID.
     * 
     * @param int $id ID do usuário
     * @return \Illuminate\Http\JsonResponse JSON com dados do usuário ou erro 404
     */
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

    /**
     * Atualiza os dados de um usuário existente.
     * 
     * @param UserUpdateRequest $request
     * @param int $id ID do usuário a atualizar
     * @return \Illuminate\Http\JsonResponse JSON com dados do usuário atualizado ou erro 404
     */
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

    /**
     * Exclui um usuário do sistema.
     * 
     * @param int $id ID do usuário a excluir
     * @return \Illuminate\Http\JsonResponse JSON com confirmação ou erro 404
     */
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
