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
            'start_time' => 'required|date|after:now',
            'location_details' => 'nullable|string|max:255',
        ];
    }
}
