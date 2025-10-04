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

class AvailabilityController extends Controller
{
    protected $availabilityService;

    public function __construct(AvailabilityService $availabilityService)
    {
        $this->availabilityService = $availabilityService;
    }

    public function storeOrUpdate(StoreAvailabilityRequest $request): JsonResponse
    {
        $professor = $request->user(); // Obtém o professor autenticado
        $validatedData = $request->validated(); // Obtém os dados validados

        $success = $this->availabilityService->updateProfessorAvailability(
            $professor,
            $validatedData['availabilities']
        );

        if ($success) {
            return response()->json(['message' => 'Disponibilidade atualizada com sucesso.'], 200);
        }
        
        // Em caso de falha na transação do service
        return response()->json(['message' => 'Ocorreu um erro ao atualizar a disponibilidade.'], 500);
    }

    /**
     * Lista os slots de horários disponíveis de um professor.
     */
    public function index(Request $request, Professor $professor): JsonResponse
    {
         // Validação simples para os parâmetros da query string
        $validator = Validator::make($request->all(), [
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Define padrões caso as datas não sejam fornecidas
        $startDate = $request->input('start_date', Carbon::today()->toDateString());
        $endDate = $request->input('end_date', Carbon::today()->addDays(7)->toDateString());

        $slots = $this->availabilityService->getAvailableSlots($professor, $startDate, $endDate);

        return response()->json($slots);
    }

    
    public function store(Request $request)
    {
        //
    }

    
    public function show(string $id)
    {
        //
    }

    
    public function update(Request $request, string $id)
    {
        //
    }

    
    public function destroy(string $id)
    {
        //
    }
}
