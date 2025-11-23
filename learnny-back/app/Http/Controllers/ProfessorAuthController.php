<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\TokenRepository;
use App\Models\Professor;

/**
 * Controlador de Autenticação de Professores
 * 
 * Gerencia o login e logout de professores no sistema.
 * Valida o status de aprovação antes de permitir acesso.
 */
class ProfessorAuthController extends Controller
{
    protected $tokenRepository;

    public function __construct(TokenRepository $tokenRepository)
    {
        $this->tokenRepository = $tokenRepository;
    }

    /**
     * Autentica um professor e emite um token de acesso.
     * 
     * Valida as credenciais fornecidas (email e senha hasheada),
     * verifica se o professor foi aprovado e, caso positivo,
     * gera um token de autenticação.
     * 
     * @param Request $request Contém email e password
     * @return \Illuminate\Http\JsonResponse JSON com token, mensagem de sucesso ou erro
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        // Busca o professor pelo email (normalizado)
        $professor = Professor::where('email', strtolower($request->email))->first();

        // Verifica se o professor existe e se a senha fornecida corresponde à hasheada
        if (!$professor || !Hash::check($request->password, $professor->password)) {
            return response()->json([
                'status'  => 401,
                'message' => 'Credenciais inválidas!'
            ]);
        }

        // Verifica se o professor foi aprovado por um administrador
        if ($professor->status !== 'approved') {
            return response()->json([
                'status'  => 403,
                'message' => 'Seu cadastro ainda não foi aprovado por um administrador.'
            ]);
        }

        // Gera um token de acesso OAuth2 para o professor
        $professor->token = $professor->createToken($professor->email)->accessToken;

        return response()->json([
            'status'    => 200,
            'message'   => 'Login realizado com sucesso!',
            'professor' => $professor
        ]);
    }

    /**
     * Realiza logout do professor autenticado.
     * 
     * Revoga o token de acesso do professor, impedindo que este seja usado
     * em futuras requisições autenticadas.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse JSON com confirmação de logout
     */
    public function logout(Request $request)
    {
        // Obtém o ID do token atual do professor autenticado
        $tokenId = $request->user()->token()->id;
        
        // Revoga o token OAuth2, invalidando-o
        $this->tokenRepository->revokeAccessToken($tokenId);

        return response()->json([
            'status'  => true,
            'message' => 'Professor deslogado com sucesso!'
        ]);
    }
}
