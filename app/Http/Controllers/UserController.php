<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserCreateRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // Requisicao GET de ate 15 usuarios
    public function index()
    {
        $user = User::select('id', 'name', 'email', 'contact')
            ->paginate('15');

        return response()->json([
            'status' => 200,
            'mensagem' => 'Usuários encontrados!!',
            'user' => $user
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    //requisicao POST do usuario
    public function store(UserCreateRequest $request)
    {

        $data = $request->all();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'contact' => $data['contact'],
            'password' => $data['password'],
        ]);

        return response()->json([
            'status' => 200,
            'mensagem' => 'Usuário cadastrado com sucesso!!',
            'user' => $user
        ]);
    }

    // Requisicao GET de usuario especifico
    public function show(string $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => 404,
                'mensagem' => 'Usuário não encontrado!',
                'user' => $user
            ]);
        }

        return response()->json([
            'status' => 200,
            'mensagem' => 'Usuário encontrado!!',
            'user' => $user
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    // Requisicao PUT para 
    public function update(UserUpdateRequest $request, string $id)
    {
        $data = $request->all();
        
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => 404,
                'mensagem' => 'Usuário não encontrado!',
                'user' => $user
            ]);
        }

        $user->update($data);

        return response()->json([
            'status' => 200,
            'mensagem' => 'Usuário atualizado com sucesso!',
            'user' => $user
        ]);
    }

    // Requisicao DELETE
    public function destroy(string $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => 404,
                'mensagem' => 'Usuário não encontrado!',
                'user' => $user
            ]);
        }

        $user->delete($id);

        return response()->json([
            'status' => 200,
            'mensagem' => 'Usuário excluído com sucesso!',
            'user' => $user
        ]);
    }
}
