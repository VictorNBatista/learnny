<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\CreateAppointmentRequest;
use App\Services\AppointmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use App\Models\Appointment;

/**
 * Controlador de Agendamentos
 * 
 * Gerencia operações de agendamentos entre alunos e professores.
 * Delega a lógica de negócio e validações para AppointmentService.
 */
class AppointmentController extends Controller
{
    protected $appointmentService;

    public function __construct(AppointmentService $appointmentService)
    {
        $this->appointmentService = $appointmentService;
    }

    /**
     * Cria um novo agendamento para o aluno autenticado.
     * 
     * Valida:
     * - Se o professor leciona a matéria solicitada
     * - Se o horário está disponível (sem conflitos)
     * - Se o professor existe
     * 
     * @param CreateAppointmentRequest $request
     * @return JsonResponse JSON com dados do agendamento criado (status 201) ou erro
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
            // Retorna a mensagem de erro e o código definido no Service
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    /**
     * Lista os agendamentos do aluno autenticado.
     * 
     * Permite filtro por status (pending, confirmed, completed, cancelled).
     * 
     * @param Request $request
     * @return JsonResponse JSON com lista de agendamentos do aluno
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
     * Cancela um agendamento solicitado pelo aluno.
     * 
     * Valida:
     * - Se o agendamento pertence ao aluno autenticado
     * - Se o agendamento está em estado cancelável (pending ou confirmed)
     * - Se há prazo mínimo para cancelamento (24 horas de antecedência)
     * 
     * @param Request $request
     * @param Appointment $appointment
     * @return JsonResponse JSON com agendamento atualizado ou erro
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
     * 
     * Permite filtro por status (pending, confirmed, completed, cancelled).
     * 
     * @param Request $request
     * @return JsonResponse JSON com lista de agendamentos do professor
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
     * Confirma um agendamento pendente (ação do professor).
     * 
     * Altera o status de 'pending' para 'confirmed', indicando
     * que o professor aceitou a solicitação de agendamento.
     * 
     * @param Request $request
     * @param Appointment $appointment
     * @return JsonResponse JSON com agendamento atualizado ou erro
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
     * Rejeita um agendamento pendente (ação do professor).
     * 
     * Altera o status de 'pending' para 'cancelled_by_professor',
     * indicando que o professor recusou a solicitação.
     * 
     * @param Request $request
     * @param Appointment $appointment
     * @return JsonResponse JSON com agendamento atualizado ou erro
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
     * Marca um agendamento como concluído (ação do professor).
     * 
     * Altera o status para 'completed', indicando que a aula
     * foi realizada com sucesso.
     * 
     * @param Request $request
     * @param Appointment $appointment
     * @return JsonResponse JSON com agendamento atualizado ou erro
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
     * Cancela um agendamento confirmado (ação do professor).
     * 
     * Altera o status para 'cancelled_by_professor', permitindo que
     * o professor cancele um agendamento já confirmado quando necessário.
     * 
     * @param Request $request
     * @param Appointment $appointment
     * @return JsonResponse JSON com agendamento atualizado ou erro
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
}
