<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\CreateAppointmentRequest;
use App\Services\AppointmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use App\Models\Appointment;

class AppointmentController extends Controller
{
    protected $appointmentService;

    public function __construct(AppointmentService $appointmentService)
    {
        $this->appointmentService = $appointmentService;
    }

    /**
     * Cria um novo agendamento para o aluno autenticado.
     */
    public function store(CreateAppointmentRequest $request): JsonResponse
    {
        try {
            $appointment = $this->appointmentService->createAppointment(
                $request->user(),
                $request->validated()
            );

            return response()->json($appointment, 201); // 201 Created

        } catch (\Exception $e) {
            // Retorna a mensagem de erro e o código que definimos no Service
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    /**
     * Lista os agendamentos do aluno autenticado.
     */
    public function listByUser(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['nullable', 'string', Rule::in(['pending', 'confirmed', 'completed', 'cancelled'])]
        ]);
        
        $status = $validated['status'] ?? null;
        $appointments = $this->appointmentService->getAppointmentsForUser($request->user(), $status);

        return response()->json($appointments);
    }

    /**
     * Cancela um agendamento. (Ação do Aluno)
     */
    public function cancelByUser(Request $request, Appointment $appointment): JsonResponse
    {
        try {
            $updatedAppointment = $this->appointmentService->cancelByUser(
                $request->user(), // Aluno autenticado
                $appointment     // Agendamento injetado da URL
            );
            return response()->json($updatedAppointment);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    /**
     * Lista os agendamentos do professor autenticado.
     */
    public function listByProfessor(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['nullable', 'string', Rule::in(['pending', 'confirmed', 'completed', 'cancelled'])]
        ]);
        
        $status = $validated['status'] ?? null;
        $appointments = $this->appointmentService->getAppointmentsForProfessor($request->user(), $status);

        return response()->json($appointments);
    }

    /**
     * Confirma um agendamento. (Ação do Professor)
     */
    public function confirm(Request $request, Appointment $appointment): JsonResponse
    {
        try {
            $updatedAppointment = $this->appointmentService->confirmAppointment(
                $request->user(), // Professor autenticado
                $appointment     // Agendamento injetado da URL
            );
            return response()->json($updatedAppointment);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    /**
     * Rejeita um agendamento. (Ação do Professor)
     */
    public function reject(Request $request, Appointment $appointment): JsonResponse
    {
        try {
            $updatedAppointment = $this->appointmentService->rejectAppointment(
                $request->user(),
                $appointment
            );
            return response()->json($updatedAppointment);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    /**
     * Marca um agendamento como concluído. (Ação do Professor)
     */
    public function complete(Request $request, Appointment $appointment): JsonResponse
    {
        try {
            $updatedAppointment = $this->appointmentService->completeAppointment(
                $request->user(), // Professor autenticado
                $appointment     // Agendamento injetado da URL
            );
            return response()->json($updatedAppointment);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    /**
     * Cancela um agendamento. (Ação do Professor)
     */
    public function cancelByProfessor(Request $request, Appointment $appointment): JsonResponse
    {
        try {
            $updatedAppointment = $this->appointmentService->cancelByProfessor(
                $request->user(), // Professor autenticado
                $appointment     // Agendamento injetado da URL
            );
            return response()->json($updatedAppointment);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }
    
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
