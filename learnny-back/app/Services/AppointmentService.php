<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Professor;
use App\Models\User;
use App\Repositories\AppointmentRepository;
use App\Repositories\ProfessorRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

/**
 * Serviço de Agendamentos
 * 
 * Encapsula a lógica de negócio para operações com agendamentos,
 * incluindo validações de conflito, disponibilidade e permissões.
 */
class AppointmentService
{
    protected $appointmentRepository;

    public function __construct(AppointmentRepository $appointmentRepository)
    {
        $this->appointmentRepository = $appointmentRepository;
    }

    /**
     * Cria um novo agendamento com validações completas.
     * 
     * Validações realizadas:
     * 1. Professor leciona a matéria solicitada
     * 2. Horário não possui conflitos com agendamentos confirmados
     * 3. Dados básicos do professor e matéria
     * 
     * @param User $student Aluno autenticado
     * @param array $data Dados validados da requisição (professor_id, subject_id, start_time, location_details)
     * @return Appointment Agendamento criado com status 'pending'
     * @throws Exception Se falhar em qualquer validação
     */
    public function createAppointment(User $student, array $data): Appointment
    {
        $professor = Professor::findOrFail($data['professor_id']);
        $startTime = Carbon::parse($data['start_time']);
        $endTime = $startTime->clone()->addHour(); // Aulas de 1h por padrão

        // VALIDAÇÃO 1: O professor leciona a matéria solicitada?
        if (!$professor->subjects()->where('subject_id', $data['subject_id'])->exists()) {
            throw new Exception('O professor selecionado não leciona esta matéria.', 422);
        }

        // VALIDAÇÃO 2: Existe conflito com agendamentos confirmados?
        $conflictingAppointments = $this->appointmentRepository
            ->getConfirmedAppointmentsForProfessor($professor->id, $startTime, $endTime);

        if ($conflictingAppointments->isNotEmpty()) {
            throw new Exception('Este horário não está mais disponível.', 409); // 409 Conflict
        }

        // Prepara os dados para criação
        $appointmentData = [
            'user_id' => $student->id,
            'professor_id' => $professor->id,
            'subject_id' => $data['subject_id'],
            'start_time' => $startTime,
            'end_time' => $endTime,
            'price_paid' => $professor->price,
            'location_details' => $data['location_details'] ?? null,
            'status' => 'pending', // Agendamento inicia como pendente
        ];

        return $this->appointmentRepository->create($appointmentData);
    }

    /**
     * Obtém agendamentos de um aluno com filtro opcional por status.
     * 
     * @param User $student Aluno
     * @param string|null $status Status para filtro (pending, confirmed, completed, cancelled)
     * @return Collection Coleção de agendamentos
     */
    public function getAppointmentsForUser(User $student, ?string $status = null): Collection
    {
        return $this->appointmentRepository->getByUserId($student->id, $status);
    }

    /**
     * Obtém agendamentos de um professor com filtro opcional por status.
     * 
     * @param Professor $professor Professor
     * @param string|null $status Status para filtro (pending, confirmed, completed, cancelled)
     * @return Collection Coleção de agendamentos
     */
    public function getAppointmentsForProfessor(Professor $professor, ?string $status = null): Collection
    {
        return $this->appointmentRepository->getByProfessorId($professor->id, $status);
    }

    /**
     * Confirma um agendamento pendente (ação do professor).
     * 
     * Validações:
     * 1. Agendamento pertence ao professor
     * 2. Agendamento está em status 'pending'
     * 
     * @param Professor $professor Professor autenticado
     * @param Appointment $appointment Agendamento a confirmar
     * @return Appointment Agendamento atualizado
     * @throws Exception Se falhar em qualquer validação
     */
    public function confirmAppointment(Professor $professor, Appointment $appointment): Appointment
    {
        // VALIDAÇÃO 1: O agendamento pertence a este professor?
        if ($appointment->professor_id !== $professor->id) {
            throw new Exception('Você não tem permissão para alterar este agendamento.', 403); // 403 Forbidden
        }

        // VALIDAÇÃO 2: O agendamento está pendente?
        if ($appointment->status !== 'pending') {
            throw new Exception('Este agendamento não pode mais ser alterado.', 409); // 409 Conflict
        }

        $this->appointmentRepository->update($appointment, ['status' => 'confirmed']);

        return $appointment->fresh();
    }

    /**
     * Rejeita um agendamento pendente (ação do professor).
     * 
     * Validações:
     * 1. Agendamento pertence ao professor
     * 2. Agendamento está em status 'pending'
     * 
     * @param Professor $professor Professor autenticado
     * @param Appointment $appointment Agendamento a rejeitar
     * @return Appointment Agendamento atualizado
     * @throws Exception Se falhar em qualquer validação
     */
    public function rejectAppointment(Professor $professor, Appointment $appointment): Appointment
    {
        // Mesmas validações da confirmação
        if ($appointment->professor_id !== $professor->id) {
            throw new Exception('Você não tem permissão para alterar este agendamento.', 403);
        }

        if ($appointment->status !== 'pending') {
            throw new Exception('Este agendamento não pode mais ser alterado.', 409);
        }

        $this->appointmentRepository->update($appointment, ['status' => 'cancelled_by_professor']);
        
        return $appointment->fresh();
    }

    /**
     * Cancela um agendamento solicitado pelo aluno.
     * 
     * Validações:
     * 1. Agendamento pertence ao aluno
     * 2. Agendamento está em estado cancelável (pending ou confirmed)
     * 3. Há prazo mínimo de 24h para cancelamento
     * 
     * @param User $student Aluno autenticado
     * @param Appointment $appointment Agendamento a cancelar
     * @return Appointment Agendamento atualizado
     * @throws Exception Se falhar em qualquer validação
     */
    public function cancelByUser(User $student, Appointment $appointment): Appointment
    {
        // VALIDAÇÃO 1: O agendamento pertence a este aluno? (Segurança)
        if ($appointment->user_id !== $student->id) {
            throw new Exception('Você não tem permissão para cancelar este agendamento.', 403); // Forbidden
        }

        // VALIDAÇÃO 2: O agendamento ainda está em estado cancelável?
        if (!in_array($appointment->status, ['pending', 'confirmed'])) {
            throw new Exception('Este agendamento não pode mais ser cancelado.', 409); // Conflict
        }

        // VALIDAÇÃO 3: Há prazo mínimo de 24h para cancelamento?
        if (Carbon::now()->diffInHours($appointment->start_time) < 24) {
            throw new Exception('Cancelamentos só podem ser feitos com mais de 24 horas de antecedência.', 422);
        }

        $this->appointmentRepository->update($appointment, ['status' => 'cancelled_by_user']);

        return $appointment->fresh();
    }

    /**
     * Cancela um agendamento solicitado pelo professor.
     * 
     * Validações:
     * 1. Agendamento pertence ao professor
     * 2. Agendamento está em status 'confirmed'
     * 
     * @param Professor $professor Professor autenticado
     * @param Appointment $appointment Agendamento a cancelar
     * @return Appointment Agendamento atualizado
     * @throws Exception Se falhar em qualquer validação
     */
    public function cancelByProfessor(Professor $professor, Appointment $appointment): Appointment
    {
        // VALIDAÇÃO 1: O agendamento pertence a este professor? (Segurança)
        if ($appointment->professor_id !== $professor->id) {
            throw new Exception('Você não tem permissão para cancelar este agendamento.', 403); // Forbidden
        }

        // VALIDAÇÃO 2: Apenas agendamentos confirmados podem ser cancelados pelo professor
        if ($appointment->status !== 'confirmed') {
            throw new Exception('Apenas agendamentos confirmados podem ser cancelados.', 409); // Conflict
        }

        $this->appointmentRepository->update($appointment, ['status' => 'cancelled_by_professor']);

        return $appointment->fresh();
    }

    /**
     * Marca um agendamento como concluído (ação do professor).
     * 
     * Validações:
     * 1. Agendamento pertence ao professor
     * 2. Agendamento está em status 'confirmed'
     * 3. O horário de término já passou (aula já foi realizada)
     * 
     * @param Professor $professor Professor autenticado
     * @param Appointment $appointment Agendamento a marcar como concluído
     * @return Appointment Agendamento atualizado
     * @throws Exception Se falhar em qualquer validação
     */
    public function completeAppointment(Professor $professor, Appointment $appointment): Appointment
    {
        // VALIDAÇÃO 1: O agendamento pertence a este professor?
        if ($appointment->professor_id !== $professor->id) {
            throw new Exception('Você não tem permissão para alterar este agendamento.', 403);
        }

        // VALIDAÇÃO 2: Apenas agendamentos confirmados podem ser concluídos.
        if ($appointment->status !== 'confirmed') {
            throw new Exception('Apenas agendamentos confirmados podem ser marcados como concluídos.', 409);
        }

        // VALIDAÇÃO 3: A aula já terminou?
        if (Carbon::parse($appointment->end_time)->isFuture()) {
             throw new Exception('Esta aula ainda não foi finalizada.', 422);
        }

        $this->appointmentRepository->update($appointment, ['status' => 'completed']);

        return $appointment->fresh();
    }
}
