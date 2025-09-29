<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\TokenRepository;
use App\Models\Professor;

class ProfessorAuthController extends Controller
{
    protected $tokenRepository;

    public function __construct(TokenRepository $tokenRepository)
    {
        $this->tokenRepository = $tokenRepository;
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $professor = Professor::where('email', strtolower($request->email))->first();

        if (!$professor || !Hash::check($request->password, $professor->password)) {
            return response()->json([
                'status'  => 401,
                'message' => 'Credenciais inválidas!'
            ]);
        }

        if ($professor->status !== 'approved') {
            return response()->json([
                'status'  => 403,
                'message' => 'Seu cadastro ainda não foi aprovado por um administrador.'
            ]);
        }

        $professor->token = $professor->createToken($professor->email)->accessToken;

        return response()->json([
            'status'    => 200,
            'message'   => 'Login realizado com sucesso!',
            'professor' => $professor
        ]);
    }

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
