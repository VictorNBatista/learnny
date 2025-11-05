<?php

namespace App\Services;

use App\Models\Professor;
use App\Repositories\AppointmentRepository;
use App\Repositories\AvailabilityRepository;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

class AvailabilityService
{
    protected $availabilityRepository;
    protected $appointmentRepository; 

    public function __construct(
        AvailabilityRepository $availabilityRepository,
        AppointmentRepository $appointmentRepository
    ) {
        $this->availabilityRepository = $availabilityRepository;
        $this->appointmentRepository = $appointmentRepository;
    }

    /**
     * Obtém a lista de regras de disponibilidade para um professor.
     *
     * @param Professor $professor
     * @return Collection
     */
    public function getAvailabilityForProfessor(Professor $professor): Collection
    {
        return $this->availabilityRepository->findByProfessorId($professor->id);
    }

    /**
     * Gera uma lista de slots de horários disponíveis para um professor
     * dentro de um intervalo de datas, descontando os horários já agendados.
     *
     * @param Professor $professor
     * @param string $startDateStr
     * @param string $endDateStr
     * @return array
     */

    public function getAvailableSlots(Professor $professor, string $startDateStr, string $endDateStr): array
    {
        $startDate = Carbon::parse($startDateStr)->startOfDay();
        $endDate = Carbon::parse($endDateStr)->endOfDay();
        
        // Puxa as regras de disponibilidade e os agendamentos confirmados UMA VEZ.
        $generalAvailabilities = $professor->availabilities->keyBy('day_of_week');
        $confirmedAppointments = $this->appointmentRepository
            ->getConfirmedAppointmentsForProfessor($professor->id, $startDate, $endDate);

        $freeSlots = [];
        $slotDurationInMinutes = 60; // Duração de cada aula (ex: 60 minutos)

        // Itera por cada dia no intervalo solicitado.
        $period = CarbonPeriod::create($startDate, $endDate);
        foreach ($period as $date) {
            $dayOfWeek = $date->dayOfWeek;

            // Se não houver regra de disponibilidade para este dia da semana, pula.
            if (!isset($generalAvailabilities[$dayOfWeek])) {
                continue;
            }

            // Gera os slots potenciais para o dia, com base na regra.
            $availability = $generalAvailabilities[$dayOfWeek];
            $slot = Carbon::parse($date->toDateString() . ' ' . $availability->start_time);
            $endTime = Carbon::parse($date->toDateString() . ' ' . $availability->end_time);

            while ($slot->lessThan($endTime)) {
                $isSlotFree = true;
                
                // Verifica se o slot atual colide com algum agendamento confirmado.
                foreach ($confirmedAppointments as $appointment) {
                    if ($slot->between($appointment->start_time, $appointment->end_time, false) ||
                        $slot->clone()->addMinutes($slotDurationInMinutes)->isAfter($appointment->start_time) && $slot->isBefore($appointment->end_time)) {
                        $isSlotFree = false;
                        break; // Se encontrou conflito, não precisa checar outros.
                    }
                }

                if ($isSlotFree) {
                    // Adiciona o slot livre à lista no formato ISO-8601
                    $freeSlots[] = $slot->toIso8601String();
                }

                // Vai para o próximo slot
                $slot->addMinutes($slotDurationInMinutes);
            }
        }
        
        return $freeSlots;
    }
    public function updateProfessorAvailability(Professor $professor, array $availabilitiesData): bool
    {
        // Ou tudo funciona, ou nada é salvo no banco.
        return DB::transaction(function () use ($professor, $availabilitiesData) {
            
            // 1. Apaga todas as disponibilidades antigas do professor.
            $this->availabilityRepository->deleteByProfessorId($professor->id);

            // 2. Itera sobre os novos dados de disponibilidade e os cria no banco.
            foreach ($availabilitiesData as $availability) {
                $this->availabilityRepository->create([
                    'professor_id' => $professor->id,
                    'day_of_week'  => $availability['day_of_week'],
                    'start_time'   => $availability['start_time'],
                    'end_time'     => $availability['end_time'],
                ]);
            }

            return true;
        });
    }
}