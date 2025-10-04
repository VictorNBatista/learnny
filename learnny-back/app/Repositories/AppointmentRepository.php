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
}