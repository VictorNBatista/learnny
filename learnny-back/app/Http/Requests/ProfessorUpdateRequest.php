<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfessorUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:100',
            'photo_url' => 'sometimes|string|url',
            'contact' => 'sometimes|string|max:15|unique:professors,contact',
            'biography' => 'sometimes|string',
            'subjects' => 'sometimes|array',
            'subjects.*' => 'exists:subjects,id',
            'price' => 'sometimes|numeric|min:0'
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
