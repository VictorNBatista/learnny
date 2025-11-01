<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ProfessorCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'       => 'required|string|max:255',
            'username' => 'required|string|max:100|alpha_dash|unique:professors,username',
            'email'      => 'required|string|email|max:255|unique:professors,email',
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
            'photo_url'  => 'nullable|url',
            'contact'    => 'required|string|max:20',
            'biography'  => 'required|string',
            'price'      => 'required|numeric|min:0',
            
            'subjects' => 'required|array|min:1', 
            'subjects.*' => 'exists:subjects,id'
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'O campo :attribute é obrigatório.',
            'string' => 'O campo :attribute deve ser uma string.',
            'url' => 'O campo :attribute deve ser uma URL válida.',
            'unique' => 'O campo :attribute deve ser único.',
            'max' => 'O campo :attribute deve ter no máximo :max caracteres.',
            'numeric' => 'O campo :attribute deve ser um número.',
            'min' => 'O campo :attribute deve ter um valor mínimo de :min.'
        ];
    }
}
