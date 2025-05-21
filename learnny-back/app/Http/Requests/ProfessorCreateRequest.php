<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfessorCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'photo_url' => 'nullable|string|url',
            'contact' => 'required|string|max:15|unique:professors,contact',
            'biography' => 'required|string',
            'price' => 'required|numeric|min:0',
            'subjects' => 'required|array',
            'subjects.*' => 'exists:subjects,id',
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
