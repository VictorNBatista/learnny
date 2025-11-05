<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfessorController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\ProfessorAuthController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminSubjectController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AvailabilityController;

use Illuminate\Support\Facades\Route;

// =======================
// AUTH (Alunos e Professores)
// =======================
Route::post('/login', [AuthController::class, 'login']); // Login do aluno
Route::post('/professor/login', [ProfessorAuthController::class, 'login']); // Login do professor

// Cadastro inicial (público)
Route::post('/cadastrar', [UserController::class, 'store']);
Route::post('/professor/cadastrar', [ProfessorController::class, 'store']);

// =======================
// ROTAS PROTEGIDAS - USUÁRIO
// =======================
Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::prefix('/user')->group(function () {
        Route::get('/listar', [UserController::class, 'index']);
        Route::put('/atualizar/{id}', [UserController::class, 'update']);
        Route::delete('/deletar/{id}', [UserController::class, 'destroy']);
        Route::get('/visualizar/{id}', [UserController::class, 'show']);

        Route::prefix('professors')->group(function () {
            Route::get('/{professor}', [ProfessorController::class, 'show']); // Ver detalhes de um professor
        });
    });

    Route::prefix('appointments')->group(function () {
        Route::post('/', [AppointmentController::class, 'store']); // Criar um novo agendamento
        Route::get('/my', [AppointmentController::class, 'listByUser']); // Listar meus agendamentos
        Route::put('/{appointment}/cancel', [AppointmentController::class, 'cancelByUser']); // Cancelar um agendamento
    });
});

// =======================
// ROTAS PROTEGIDAS - PROFESSOR
// =======================
Route::middleware('auth:professor')->prefix('/professor')->group(function () {
    Route::post('/logout', [ProfessorAuthController::class, 'logout']); 
    Route::get('/me', [ProfessorController::class, 'me']);
    Route::put('/atualizar/{id}', [ProfessorController::class, 'update']);
    Route::delete('/deletar/{id}', [ProfessorController::class, 'destroy']);
    Route::get('/availabilities', [AvailabilityController::class, 'show']); // Ver minha disponibilidade
    Route::post('/availabilities', [AvailabilityController::class, 'storeOrUpdate']); // Criar ou atualizar minha disponibilidade

    // Gerenciamento de Agendamentos
    Route::get('/appointments', [AppointmentController::class, 'listByProfessor']); // Listar meus agendamentos
    Route::put('/appointments/{appointment}/confirm', [AppointmentController::class, 'confirm']); // Confirmar agendamento
    Route::put('/appointments/{appointment}/reject', [AppointmentController::class, 'reject']); // Rejeitar agendamento
    Route::put('/appointments/{appointment}/cancel', [AppointmentController::class, 'cancelByProfessor']); // Cancelar agendamento
    Route::put('/appointments/{appointment}/complete', [AppointmentController::class, 'complete']); // Marcar agendamento como concluído
});

// =======================
// Professor (público)
// =======================
Route::get('professor/listar', [ProfessorController::class, 'index']);
Route::get('/professor/{professor}/availabilities', [AvailabilityController::class, 'index']); 

// =======================
// Subjects (público)
// =======================
Route::get('/subject/listar', [SubjectController::class, 'index']);

// =======================
// ADMINS
// =======================
Route::prefix('/admin')->group(function () {
    // Auth
    Route::post('/cadastrar', [AdminController::class, 'store']);
    Route::post('/login', [AdminAuthController::class, 'login']);

    Route::middleware('auth:admin')->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout']);

        // CRUD de admins
        Route::get('/listar', [AdminController::class, 'index']);
        Route::get('/visualizar/{id}', [AdminController::class, 'show']);
        Route::put('/atualizar/{id}', [AdminController::class, 'update']);
        Route::delete('/deletar/{id}', [AdminController::class, 'destroy']);

        // Subjects (somente admin pode gerenciar)
        Route::prefix('/subjects')->group(function () {
            Route::get('/listar', [AdminSubjectController::class, 'index']);
            Route::post('/cadastrar', [AdminSubjectController::class, 'store']);
            Route::get('/visualizar/{id}', [AdminSubjectController::class, 'show']);
            Route::put('/atualizar/{id}', [AdminSubjectController::class, 'update']);
            Route::delete('/deletar/{id}', [AdminSubjectController::class, 'destroy']);
        });

        // Aprovação/Reprovação de Professores
        Route::prefix('/professores')->group(function () {
            Route::get('/pendentes', [ProfessorController::class, 'pending']); // listar professores aguardando aprovação
            Route::put('/aprovar/{id}', [ProfessorController::class, 'approve']); // aprovar professor
            Route::put('/reprovar/{id}', [ProfessorController::class, 'reject']); // reprovar professor
            Route::get('/visualizar/{id}', [ProfessorController::class, 'show']); // visualizar detalhes do professor
        });
    });
});
