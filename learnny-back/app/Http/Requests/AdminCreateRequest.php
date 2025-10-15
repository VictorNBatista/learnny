<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'     => 'required|string|max:255',
            'username' => 'required|string|max:100|alpha_dash|unique:users,username',
            'email'    => 'required|email|unique:admins,email',
            'password' => 'required|string|min:6|confirmed',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'     => 'O nome é obrigatório.',
            'email.required'    => 'O e-mail é obrigatório.',
            'email.email'       => 'O e-mail informado não é válido.',
            'email.unique'      => 'Este e-mail já está em uso.',
            'password.required' => 'A senha é obrigatória.',
            'password.min'      => 'A senha deve ter no mínimo 6 caracteres.',
            'password.confirmed'=> 'As senhas não conferem.',
        ];
    }
}
