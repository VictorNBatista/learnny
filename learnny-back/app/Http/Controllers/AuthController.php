<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\TokenRepository;
use App\Models\User;

/**
 * Controlador de Autenticação de Usuários
 * 
 * Gerencia o login e logout de usuários convencionais (alunos) no sistema.
 * Utiliza OAuth2 via Laravel Passport para emissão e gerenciamento de tokens.
 */
class AuthController extends Controller
{
    protected $tokenRepository;

    public function __construct(TokenRepository $tokenRepository)
    {
        $this->tokenRepository = $tokenRepository;
    }
    
    /**
     * Autentica um usuário e emite um token de acesso.
     * 
     * Valida as credenciais (email e senha) fornecidas na requisição,
     * e caso sejam válidas, gera um token de autenticação que permite
     * acesso aos endpoints protegidos da API.
     * 
     * @param Request $request Contém email e password
     * @return \Illuminate\Http\JsonResponse JSON com status, mensagem e dados do usuário autenticado ou erro
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        
        // Extrai as credenciais da requisição
        $data = $request->all();

        // Tenta autenticar o usuário com as credenciais fornecidas
        if (Auth::attempt(['email' => strtolower($data['email']), 'password' => $data['password']])) {
            // Obtém a instância do usuário autenticado
            $user = auth()->user();

            // Gera um token de acesso OAuth2 para o usuário
            $user->token = $user->createToken($user->email)->accessToken;
            return response()->json([
                'status' => 200,
                'mensagem' => "Usuário logado com sucesso",
                'usuario' => $user
            ]);
        } else {
            // Credenciais inválidas: email não existe ou senha incorreta
            return response()->json([
                'status' => 404,
                'mensagem' => "Usuário ou senha incorreto"
            ]);
        }
    }
    
    /**
     * Realiza logout do usuário autenticado.
     * 
     * Revoga o token de acesso do usuário, impedindo que este seja usado
     * em futuras requisições autenticadas.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse JSON com confirmação de logout
     */
    public function logout(Request $request)
    {
        // Obtém o ID do token atual do usuário autenticado
        $tokenId = $request->user()->token()->id;

        // Revoga o token OAuth2, invalidando-o
        $this->tokenRepository->revokeAccessToken($tokenId);

        return response()->json(['status' => true, 'mensagem' => "Usuário deslogado com sucesso!"]);
    }
}
