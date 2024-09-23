<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UserCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:50',
            'email' => 'required|string|email|max:255|unique:users',
            'contact'=> 'required|string|max:15|unique:users',
            'password' => [
                'required',
                'string',
                'confirmed',
            ],
            'password_confirmation' => 'required|same:password'
        ];
    }

    public function messages()
    {
        return [
            'required' => 'O campo :attribute é obrigatório.',
            'string' => 'O campo :attribute deve ser uma string.',
            'letters' => 'O campo :attribute deve conter ao menos uma letra.',
            'mixedCase' => 'O campo :attribute deve conter ao menos uma letra maiúscula e uma letra minúscula.',
            'symbols' => 'O campo :attribute deve conter ao menos um simbolo.',
            'numbers' => 'O campo :attribute deve conter ao menos um número.',
            'max' => 'O campo :attribute deve ter no máximo :max.',
            'email' => 'O campo :attribute não é válido',
            'unique' => 'O campo :attribute deve ser único.',
            'confirmed' => 'O campo :attribute não confere.',
            'same' => 'Os campos confirmação da senha e senha devem corresponder.',
            'min' => 'O campo :attribute deve ter no mínimo :min caracteres.'
            
        ];
    }
}
