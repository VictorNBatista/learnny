<?php

namespace App\Repositories;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class AppointmentRepository
{
    protected $model;

    public function __construct(Appointment $model)
    {
        $this->model = $model;
    }

    /**
     * Busca agendamentos confirmados de um professor que colidem com um intervalo de tempo.
     *
     * @param int $professorId
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return Collection
     */
    public function getConfirmedAppointmentsForProfessor(int $professorId, Carbon $startDate, Carbon $endDate): Collection
    {
        return $this->model
            ->where('professor_id', $professorId)
            ->where('status', 'confirmed')
            // Esta lógica complexa de 'where' garante que pegamos qualquer agendamento
            // que sequer "toque" no intervalo de tempo desejado.
            ->where(function ($query) use ($startDate, $endDate) {
                $query->where('start_time', '<', $endDate)
                      ->where('end_time', '>', $startDate);
            })
            ->get();
    }

    /**
     * Cria um novo registro de agendamento.
     *
     * @param array $data
     * @return Appointment
     */
    public function create(array $data): Appointment
    {
        return $this->model->create($data);
    }

    /**
     * Busca todos os agendamentos de um aluno específico, com seus relacionamentos.
     *
     * @param int $userId
     * @return Collection
     */
    public function getByUserId(int $userId): Collection
    {
        return $this->model
            ->with(['professor', 'subject']) // Carrega o professor e a matéria
            ->where('user_id', $userId)
            ->orderBy('start_time', 'desc') // Ordena pelos mais recentes primeiro
            ->get();
    }

    /**
     * Busca todos os agendamentos de um professor específico, com seus relacionamentos.
     *
     * @param int $professorId
     * @return Collection
     */
    public function getByProfessorId(int $professorId): Collection
    {
        return $this->model
            ->with(['user', 'subject']) // Carrega o aluno e a matéria
            ->where('professor_id', $professorId)
            ->orderBy('start_time', 'desc')
            ->get();
    }

    /**
     * Atualiza um agendamento existente.
     *
     * @param Appointment $appointment
     * @param array $data
     * @return bool
     */
    public function update(Appointment $appointment, array $data): bool
    {
        return $appointment->update($data);
    }
}