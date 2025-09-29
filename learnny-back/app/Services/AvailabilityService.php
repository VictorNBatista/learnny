<?php

namespace App\Services;

use App\Models\Professor;
use App\Repositories\AvailabilityRepository;
use Illuminate\Support\Facades\DB;

class AvailabilityService
{
    protected $availabilityRepository;

    public function __construct(AvailabilityRepository $availabilityRepository)
    {
        $this->availabilityRepository = $availabilityRepository;
    }

    /**
     * Atualiza a grade de horários de um professor.
     *
     * @param Professor $professor
     * @param array $availabilitiesData
     * @return bool
     */
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