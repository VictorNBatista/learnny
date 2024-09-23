<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\TokenRepository;
use App\Models\User;

class AuthController extends Controller
{
    protected $tokenRepository;

    public function __construct(TokenRepository $tokenRepository)
    {
        $this->tokenRepository = $tokenRepository;
    }
    
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        
        //Receber a credencial (email e senha)
        $data = $request->all();

        //Verificoaas credenciais estão no Banco
        if (Auth::attempt(['email' => strtolower($data['email']), 'password' => $data['password']])) {
            //Autentica o usuário
            $user = auth()->user();

            //cria um token
            $user->token = $user->createToken($user->email)->accessToken;
            return response()->json([
                'status' => 200,
                'mensagem' => "Usuário logado com sucesso",
                'usuario' => $user
            ]);
        } else {
            return response()->json([
                'status' => 404,
                'mensagem' => "Usuário ou senha incorreto"
            ]);
        }
    }
    
    public function logout(Request $request)
    {
        $tokenId = $request->user()->token()->id;

        $this->tokenRepository->revokeAccessToken($tokenId);

        return response()->json(['status' => true, 'mensagem' => "Usuário deslogado com sucesso!"]);
    }
}
