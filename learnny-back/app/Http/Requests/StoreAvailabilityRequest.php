<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAvailabilityRequest extends FormRequest
{
    /**
     * Determina se o usuário está autorizado a fazer esta requisição.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Apenas o professor autenticado pode fazer isso. A rota já garante isso
        // com o middleware 'auth:professor', então aqui podemos retornar true.
        return true;
    }

    /**
     * Obtém as regras de validação que se aplicam à requisição.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Esperamos um array chamado 'availabilities'
            'availabilities' => 'required|array',
            
            // Validação para cada item dentro do array 'availabilities'
            'availabilities.*.day_of_week' => 'required|integer|between:0,6',
            'availabilities.*.start_time' => 'required|date_format:H:i', // ou H:i:s se preferir
            'availabilities.*.end_time' => 'required|date_format:H:i|after:availabilities.*.start_time',
        ];
    }
}