<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAvailabilityRequest;
use App\Services\AvailabilityService;
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

    public function index()
    {
        //
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
