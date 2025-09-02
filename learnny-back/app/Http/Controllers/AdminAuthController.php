<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\AdminService;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\TokenRepository;
use App\Models\Admin;

class AdminAuthController extends Controller
{
    protected $adminService;

    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        
        //Receber a credencial (email e senha)
        $admin = Admin::where('email', $request->email)->first();

        //Verificoaas credenciais estão no Banco
        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return response()->json([
                'status'  => 401,
                'message' => 'Credenciais inválidas!'
            ]);
        }

        $token = $admin->createToken('ProfessorToken')->accessToken;

        return response()->json([
            'status'    => 200,
            'message'   => 'Login realizado com sucesso!',
            'token'     => $token,
            'admin' => $admin
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        return response()->json(['message' => 'Logout realizado com sucesso!']);
    }
}
