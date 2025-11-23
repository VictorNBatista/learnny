<?php

namespace App\Services;

use App\Models\Professor;
use App\Repositories\AppointmentRepository;
use App\Repositories\AvailabilityRepository;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

/**
 * Serviço de Disponibilidade de Horários
 * 
 * Gerencia as regras de disponibilidade de professores e calcula
 * slots de horários livres para agendamentos.
 */
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
     * Obtém as regras de disponibilidade cadastradas de um professor.
     * 
     * @param Professor $professor
     * @return Collection Coleção de regras de disponibilidade por dia da semana
     */
    public function getAvailabilityForProfessor(Professor $professor): Collection
    {
        return $this->availabilityRepository->findByProfessorId($professor->id);
    }

    /**
     * Calcula slots de horários disponíveis para agendamento.
     * 
     * Gera uma lista de horários livres dentro de um intervalo de datas,
     * descontando os agendamentos já confirmados e respeitando as
     * regras de disponibilidade do professor.
     * 
     * @param Professor $professor
     * @param string $startDateStr Data de início (formato: Y-m-d)
     * @param string $endDateStr Data de término (formato: Y-m-d)
     * @return array Array de slots de horários disponíveis em ISO-8601
     */
    public function getAvailableSlots(Professor $professor, string $startDateStr, string $endDateStr): array
    {
        $startDate = Carbon::parse($startDateStr)->startOfDay();
        $endDate = Carbon::parse($endDateStr)->endOfDay();
        
        // Carrega as regras de disponibilidade e agendamentos confirmados
        $generalAvailabilities = $professor->availabilities->keyBy('day_of_week');
        $confirmedAppointments = $this->appointmentRepository
            ->getConfirmedAppointmentsForProfessor($professor->id, $startDate, $endDate);

        $freeSlots = [];
        $slotDurationInMinutes = 60; // Duração padrão de cada aula

        // Itera por cada dia no intervalo solicitado
        $period = CarbonPeriod::create($startDate, $endDate);
        foreach ($period as $date) {
            $dayOfWeek = $date->dayOfWeek;

            // Se não houver regra de disponibilidade para este dia, continua
            if (!isset($generalAvailabilities[$dayOfWeek])) {
                continue;
            }

            // Gera os slots potenciais para o dia com base na regra
            $availability = $generalAvailabilities[$dayOfWeek];
            $slot = Carbon::parse($date->toDateString() . ' ' . $availability->start_time);
            $endTime = Carbon::parse($date->toDateString() . ' ' . $availability->end_time);

            // Itera pelos slots de 1 hora até o final do horário disponível
            while ($slot->lessThan($endTime)) {
                $isSlotFree = true;
                
                // Verifica se o slot colide com algum agendamento confirmado
                foreach ($confirmedAppointments as $appointment) {
                    if ($slot->between($appointment->start_time, $appointment->end_time, false) ||
                        $slot->clone()->addMinutes($slotDurationInMinutes)->isAfter($appointment->start_time) && $slot->isBefore($appointment->end_time)) {
                        $isSlotFree = false;
                        break;
                    }
                }

                // Se o slot está livre, adiciona à lista
                if ($isSlotFree) {
                    $freeSlots[] = $slot->toIso8601String();
                }

                // Vai para o próximo slot
                $slot->addMinutes($slotDurationInMinutes);
            }
        }
        
        return $freeSlots;
    }

    /**
     * Atualiza as regras de disponibilidade de um professor.
     * 
     * Operação transacional que:
     * 1. Remove todas as regras anterior do professor
     * 2. Cria novas regras com os dados fornecidos
     * 
     * Se houver erro, toda a transação é revertida.
     * 
     * @param Professor $professor
     * @param array $availabilitiesData Array com as novas regras (day_of_week, start_time, end_time)
     * @return bool True se bem-sucedido
     */
    public function updateProfessorAvailability(Professor $professor, array $availabilitiesData): bool
    {
        // Operação transacional: tudo ou nada
        return DB::transaction(function () use ($professor, $availabilitiesData) {
            
            // 1. Remove todas as disponibilidades antigas do professor
            $this->availabilityRepository->deleteByProfessorId($professor->id);

            // 2. Cria as novas disponibilidades
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