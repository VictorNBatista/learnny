<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\TokenRepository;
use App\Models\Admin;

class AdminAuthController extends Controller
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

        $admin = Admin::where('email', strtolower($request->email))->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return response()->json([
                'status' => 401,
                'mensagem' => 'Credenciais inválidas!'
            ]);
        }

        $admin->token = $admin->createToken($admin->email)->accessToken;

        return response()->json([
            'status' => 200,
            'mensagem' => 'Login realizado com sucesso!',
            'admin' => $admin
        ]);
    }

    public function logout(Request $request)
    {
        $tokenId = $request->user()->token()->id;
        $this->tokenRepository->revokeAccessToken($tokenId);

        return response()->json(['status' => true, 'mensagem' => 'Logout realizado com sucesso!']);
    }
}
