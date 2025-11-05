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

class AppointmentService
{
    protected $appointmentRepository;

    public function __construct(AppointmentRepository $appointmentRepository)
    {
        $this->appointmentRepository = $appointmentRepository;
    }

    /**
     * Cria um novo agendamento após validar as regras de negócio.
     *
     * @param User $student O aluno autenticado
     * @param array $data Os dados validados da requisição
     * @return Appointment
     * @throws Exception
     */
    public function createAppointment(User $student, array $data): Appointment
    {
        $professor = Professor::findOrFail($data['professor_id']);
        $startTime = Carbon::parse($data['start_time']);
        $endTime = $startTime->clone()->addHour(); // Assumindo aulas de 1h

        // VALIDAÇÃO 1: O professor ensina a matéria solicitada?
        // A tabela pivô 'professor_subject' deve ter essa combinação.
        if (!$professor->subjects()->where('subject_id', $data['subject_id'])->exists()) {
            throw new Exception('O professor selecionado não leciona esta matéria.', 422);
        }

        // VALIDAÇÃO 2: O slot ainda está livre?
        // Busca agendamentos confirmados que colidam com o horário desejado.
        $conflictingAppointments = $this->appointmentRepository
            ->getConfirmedAppointmentsForProfessor($professor->id, $startTime, $endTime);

        if ($conflictingAppointments->isNotEmpty()) {
            throw new Exception('Este horário não está mais disponível.', 409); // 409 Conflict
        }

        // Se todas as validações passaram, prepara os dados para salvar
        $appointmentData = [
            'user_id' => $student->id,
            'professor_id' => $professor->id,
            'subject_id' => $data['subject_id'],
            'start_time' => $startTime,
            'end_time' => $endTime,
            'price_paid' => $professor->price, // Pega o preço do cadastro do professor
            'location_details' => $data['location_details'] ?? null,
            'status' => 'pending', // O agendamento começa como pendente
        ];

        return $this->appointmentRepository->create($appointmentData);
    }

    /**
     * Obtém a lista de agendamentos para um aluno.
     *
     * @param User $student
     * @param string|null $status
     * @return Collection
     */
    public function getAppointmentsForUser(User $student, ?string $status = null): Collection
    {
        return $this->appointmentRepository->getByUserId($student->id, $status);
    }

    /**
     * Obtém a lista de agendamentos para um professor.
     *
     * @param Professor $professor
     * @param string|null $status
     * @return Collection
     */
    public function getAppointmentsForProfessor(Professor $professor, ?string $status = null): Collection
    {
        return $this->appointmentRepository->getByProfessorId($professor->id, $status);
    }

    /**
     * Confirma um agendamento pendente.
     *
     * @param Professor $professor
     * @param Appointment $appointment
     * @return Appointment
     * @throws Exception
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

        return $appointment->fresh(); // Retorna a instância atualizada do agendamento
    }

    /**
     * Rejeita um agendamento pendente.
     *
     * @param Professor $professor
     * @param Appointment $appointment
     * @return Appointment
     * @throws Exception
     */
    public function rejectAppointment(Professor $professor, Appointment $appointment): Appointment
    {
        // A lógica de validação é a mesma da confirmação
        if ($appointment->professor_id !== $professor->id) {
            throw new Exception('Você não tem permissão para alterar este agendamento.', 403);
        }

        if ($appointment->status !== 'pending') {
            throw new Exception('Este agendamento не pode mais ser alterado.', 409);
        }

        $this->appointmentRepository->update($appointment, ['status' => 'cancelled_by_professor']);
        
        return $appointment->fresh();
    }

    /**
     * Cancela um agendamento a pedido do aluno.
     *
     * @param User $student
     * @param Appointment $appointment
     * @return Appointment
     * @throws Exception
     */
    public function cancelByUser(User $student, Appointment $appointment): Appointment
    {
        // VALIDAÇÃO 1: O agendamento pertence a este aluno? (Segurança)
        if ($appointment->user_id !== $student->id) {
            throw new Exception('Você não tem permissão para cancelar este agendamento.', 403); // Forbidden
        }

        // VALIDAÇÃO 2: O agendamento ainda está em um estado que permite cancelamento?
        if (!in_array($appointment->status, ['pending', 'confirmed'])) {
            throw new Exception('Este agendamento não pode mais ser cancelado.', 409); // Conflict
        }

        // VALIDAÇÃO OPCIONAL (Regra de Negócio Futura):
        // Permitir cancelamento apenas com 24h de antecedência?
        if (Carbon::now()->diffInHours($appointment->start_time) < 24) {
            throw new Exception('Cancelamentos só podem ser feitos com mais de 24 horas de antecedência.', 422);
        }

        $this->appointmentRepository->update($appointment, ['status' => 'cancelled_by_user']);

        return $appointment->fresh(); // Retorna a instância atualizada
    }

    /**
     * Cancela um agendamento a pedido do professor.
     *
     * @param Professor $professor
     * @param Appointment $appointment
     * @return Appointment
     * @throws Exception
     */
    public function cancelByProfessor(Professor $professor, Appointment $appointment): Appointment
    {
        // VALIDAÇÃO 1: O agendamento pertence a este professor? (Segurança)
        if ($appointment->professor_id !== $professor->id) {
            throw new Exception('Você não tem permissão para cancelar este agendamento.', 403); // Forbidden
        }

        // VALIDAÇÃO 2: Apenas agendamentos confirmados podem ser cancelados pelo professor.
        // (Cancelar um 'pending' é o mesmo que 'rejeitar').
        if ($appointment->status !== 'confirmed') {
            throw new Exception('Apenas agendamentos confirmados podem ser cancelados.', 409); // Conflict
        }

        $this->appointmentRepository->update($appointment, ['status' => 'cancelled_by_professor']);

        return $appointment->fresh(); // Retorna a instância atualizada
    }

    /**
     * Marca um agendamento como concluído.
     *
     * @param Professor $professor
     * @param Appointment $appointment
     * @return Appointment
     * @throws Exception
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
        // Compara o tempo de término do agendamento com o tempo atual.
        if (Carbon::parse($appointment->end_time)->isFuture()) {
             throw new Exception('Esta aula ainda não foi finalizada.', 422);
        }

        $this->appointmentRepository->update($appointment, ['status' => 'completed']);

        return $appointment->fresh();
    }
}