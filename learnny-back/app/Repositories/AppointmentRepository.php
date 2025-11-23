<?php

namespace App\Repositories;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repositório de Agendamentos
 * 
 * Encapsula o acesso aos dados de agendamentos no banco de dados,
 * providenciando métodos para operações CRUD e consultas complexas.
 */
class AppointmentRepository
{
    protected $model;

    public function __construct(Appointment $model)
    {
        $this->model = $model;
    }

    /**
     * Busca agendamentos confirmados de um professor em um intervalo de tempo.
     * 
     * Útil para detectar conflitos de horário ao criar novos agendamentos.
     * A lógica busca qualquer agendamento que "toque" no intervalo desejado.
     * 
     * @param int $professorId ID do professor
     * @param Carbon $startDate Data/hora de início
     * @param Carbon $endDate Data/hora de término
     * @return Collection Coleção de agendamentos confirmados em conflito
     */
    public function getConfirmedAppointmentsForProfessor(int $professorId, Carbon $startDate, Carbon $endDate): Collection
    {
        return $this->model
            ->where('professor_id', $professorId)
            ->where('status', 'confirmed')
            // Lógica: busca agendamentos que colidem com o intervalo
            ->where(function ($query) use ($startDate, $endDate) {
                $query->where('start_time', '<', $endDate)
                      ->where('end_time', '>', $startDate);
            })
            ->get();
    }

    /**
     * Cria um novo registro de agendamento.
     * 
     * @param array $data Dados do agendamento
     * @return Appointment Agendamento criado
     */
    public function create(array $data): Appointment
    {
        return $this->model->create($data);
    }

    /**
     * Busca todos os agendamentos de um aluno específico.
     * 
     * Carrega relacionamentos com professor e matéria.
     * Permite filtro opcional por status.
     * O filtro 'cancelled' agrupa 'cancelled_by_user' e 'cancelled_by_professor'.
     * 
     * @param int $userId ID do aluno
     * @param string|null $status Status para filtro (pending, confirmed, completed, cancelled)
     * @return Collection Coleção de agendamentos ordenados por data (mais recentes primeiro)
     */
    public function getByUserId(int $userId, ?string $status = null): Collection
    {
        return $this->model
            ->with(['professor', 'subject'])
            ->where('user_id', $userId)
            ->when($status, function ($query, $status) {
                // Se o status é 'cancelled', agrupa os dois tipos de cancelamento
                if ($status === 'cancelled') {
                    return $query->whereIn('status', ['cancelled_by_user', 'cancelled_by_professor']);
                }
                return $query->where('status', $status);
            })
            ->orderBy('start_time', 'desc')
            ->get();
    }

    /**
     * Busca todos os agendamentos de um professor específico.
     * 
     * Carrega relacionamentos com aluno e matéria.
     * Permite filtro opcional por status.
     * O filtro 'cancelled' agrupa 'cancelled_by_user' e 'cancelled_by_professor'.
     * 
     * @param int $professorId ID do professor
     * @param string|null $status Status para filtro (pending, confirmed, completed, cancelled)
     * @return Collection Coleção de agendamentos ordenados por data (mais recentes primeiro)
     */
    public function getByProfessorId(int $professorId, ?string $status = null): Collection
    {
        return $this->model
            ->with(['user', 'subject'])
            ->where('professor_id', $professorId)
            ->when($status, function ($query, $status) {
                if ($status === 'cancelled') {
                    return $query->whereIn('status', ['cancelled_by_user', 'cancelled_by_professor']);
                }
                return $query->where('status', $status);
            })
            ->orderBy('start_time', 'desc')
            ->get();
    }

    /**
     * Atualiza um agendamento existente.
     * 
     * @param Appointment $appointment Agendamento a atualizar
     * @param array $data Dados a atualizar
     * @return bool True se bem-sucedido
     */
    public function update(Appointment $appointment, array $data): bool
    {
        return $appointment->update($data);
    }
}