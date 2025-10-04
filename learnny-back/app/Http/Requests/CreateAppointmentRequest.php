<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // A rota já está protegida pelo middleware 'auth:api', garantindo que é um aluno.
        return true;
    }

    public function rules(): array
    {
        return [
            'professor_id' => 'required|integer|exists:professors,id',
            'subject_id' => 'required|integer|exists:subjects,id',
            // O formato ISO 8601 (Y-m-d\TH:i:s) é o que nosso front-end vai receber do serviço
            'start_time' => 'required|date_format:Y-m-d\TH:i:s|after:now',
            'location_details' => 'nullable|string|max:255',
        ];
    }
}
