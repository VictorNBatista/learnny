<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\TokenRepository;
use App\Models\Admin;

/**
 * Controlador de Autenticação de Administradores
 * 
 * Gerencia o login e logout de administradores no sistema.
 * Utiliza autenticação manual com verificação de senha hasheada.
 */
class AdminAuthController extends Controller
{
    protected $tokenRepository;

    public function __construct(TokenRepository $tokenRepository)
    {
        $this->tokenRepository = $tokenRepository;
    }

    /**
     * Autentica um administrador e emite um token de acesso.
     * 
     * Valida as credenciais fornecidas (email e senha hasheada),
     * e caso sejam válidas, gera um token de autenticação.
     * 
     * @param Request $request Contém email e password
     * @return \Illuminate\Http\JsonResponse JSON com status, mensagem e token ou erro
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Busca o administrador pelo email (normalizado)
        $admin = Admin::where('email', strtolower($request->email))->first();

        // Verifica se o admin existe e se a senha fornecida corresponde à hasheada
        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return response()->json([
                'status' => 401,
                'mensagem' => 'Credenciais inválidas!'
            ]);
        }

        // Gera um token de acesso OAuth2 para o administrador
        $admin->token = $admin->createToken($admin->email)->accessToken;

        return response()->json([
            'status' => 200,
            'mensagem' => 'Login realizado com sucesso!',
            'admin' => $admin
        ]);
    }

    /**
     * Realiza logout do administrador autenticado.
     * 
     * Revoga o token de acesso do administrador, impedindo que este seja usado
     * em futuras requisições autenticadas.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse JSON com confirmação de logout
     */
    public function logout(Request $request)
    {
        // Obtém o ID do token atual do administrador autenticado
        $tokenId = $request->user()->token()->id;
        
        // Revoga o token OAuth2, invalidando-o
        $this->tokenRepository->revokeAccessToken($tokenId);

        return response()->json(['status' => true, 'mensagem' => 'Logout realizado com sucesso!']);
    }
}
