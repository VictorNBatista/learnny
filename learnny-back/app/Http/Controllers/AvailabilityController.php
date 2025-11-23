<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAvailabilityRequest;
use App\Services\AvailabilityService;
use App\Models\Professor;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

/**
 * Controlador de Disponibilidade de Horários
 * 
 * Gerencia a configuração de horários disponíveis para professores
 * e a listagem de slots de agendamento.
 */
class AvailabilityController extends Controller
{
    protected $availabilityService;

    public function __construct(AvailabilityService $availabilityService)
    {
        $this->availabilityService = $availabilityService;
    }

    /**
     * Cria ou atualiza a disponibilidade de horários de um professor.
     * 
     * Remove todas as disponibilidades anteriores e cria novas regras
     * para cada dia da semana com horários de início e fim.
     * 
     * @param StoreAvailabilityRequest $request
     * @return JsonResponse JSON com confirmação ou erro
     */
    public function storeOrUpdate(StoreAvailabilityRequest $request): JsonResponse
    {
        // Obtém o professor autenticado via token
        $professor = $request->user();
        
        // Obtém os dados validados da requisição
        $validatedData = $request->validated();

        // Atualiza a disponibilidade do professor (com transação)
        $success = $this->availabilityService->updateProfessorAvailability(
            $professor,
            $validatedData['availabilities']
        );

        if ($success) {
            return response()->json(['message' => 'Disponibilidade atualizada com sucesso.'], 200);
        }
        
        // Em caso de falha na transação
        return response()->json(['message' => 'Ocorreu um erro ao atualizar a disponibilidade.'], 500);
    }

    /**
     * Lista os slots de horários disponíveis de um professor.
     * 
     * Retorna os períodos livres dentro de um intervalo de datas,
     * desconsiderando os agendamentos já confirmados.
     * Por padrão, retorna os próximos 7 dias se datas não forem informadas.
     * 
     * @param Request $request
     * @param Professor $professor
     * @return JsonResponse JSON com lista de slots disponíveis
     */
    public function index(Request $request, Professor $professor): JsonResponse
    {
        // Valida os parâmetros de query string (start_date e end_date)
        $validator = Validator::make($request->all(), [
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Define datas padrão caso não sejam fornecidas
        $startDate = $request->input('start_date', Carbon::today()->toDateString());
        $endDate = $request->input('end_date', Carbon::today()->addDays(7)->toDateString());

        // Obtém os slots disponíveis do professor no intervalo
        $slots = $this->availabilityService->getAvailableSlots($professor, $startDate, $endDate);

        return response()->json($slots);
    }

    /**
     * Obtém as regras de disponibilidade do professor autenticado.
     * 
     * Retorna a configuração de horários para cada dia da semana.
     * 
     * @param Request $request
     * @return JsonResponse JSON com regras de disponibilidade ou erro 401
     */
    public function show(Request $request): JsonResponse
    {
        try {
            // Obtém o professor autenticado via token
            $professor = $request->user();

            if (!$professor) {
                return response()->json(['message' => 'Professor não autenticado.'], 401);
            }

            // Busca as disponibilidades configuradas do professor
            $availabilities = $this->availabilityService->getAvailabilityForProfessor($professor);

            // Retorna os dados. Laravel cuidará da serialização para JSON.
            return response()->json($availabilities);

        } catch (\Exception $e) {
            // Loga o erro para debugging
            \Log::error('Erro ao buscar disponibilidade: ' . $e->getMessage());
            return response()->json(['message' => 'Erro ao buscar disponibilidade.'], 500);
        }
    }
    
    /**
     * Remove a disponibilidade de um professor (não implementado).
     * 
     * @param string $id
     */
    public function destroy(string $id)
    {
        // Implementar se necessário
    }
}
