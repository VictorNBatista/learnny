<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Professor;
use Laravel\Passport\TokenRepository;

class ProfessorAuthController extends Controller
{
    protected $tokenRepository;

    public function __construct(TokenRepository $tokenRepository)
    {
        $this->tokenRepository = $tokenRepository;
    }

    /**
     * Login do professor
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $professor = Professor::where('email', $request->email)->first();

        if (!$professor || !Hash::check($request->password, $professor->password)) {
            return response()->json([
                'status'  => 401,
                'message' => 'Credenciais inválidas!'
            ]);
        }

        if ($professor->status !== 'approved') {
            return ['error' => 'Seu cadastro ainda não foi aprovado por um administrador.', 'status' => 403];
        }

        $token = $professor->createToken('ProfessorToken')->accessToken;

        return response()->json([
            'status'    => 200,
            'message'   => 'Login realizado com sucesso!',
            'token'     => $token,
            'professor' => $professor
        ]);
    }

    /**
     * Logout do professor
     */
    public function logout(Request $request)
    {
        $tokenId = $request->user()->token()->id;
        $this->tokenRepository->revokeAccessToken($tokenId);

        return response()->json([
            'status'  => true,
            'message' => 'Professor deslogado com sucesso!'
        ]);
    }
}
