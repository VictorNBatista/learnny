<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfessorController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\ProfessorAuthController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminSubjectController;
use App\Http\Controllers\AdminController;
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
    });
});

// =======================
// ROTAS PROTEGIDAS - PROFESSOR
// =======================
Route::middleware('auth:professor')->prefix('/professor')->group(function () {
    Route::post('/logout', [ProfessorAuthController::class, 'logout']);
    Route::get('/listar', [ProfessorController::class, 'index']);  
    Route::get('/visualizar/{id}', [ProfessorController::class, 'show']);
    Route::put('/atualizar/{id}', [ProfessorController::class, 'update']);
    Route::delete('/deletar/{id}', [ProfessorController::class, 'destroy']);
});

// =======================
// Subjects (público)
// =======================

Route::get('/subject/listar', [SubjectController::class, 'index']);

// =======================
// ADMINS
// =======================
Route::prefix('admin')->group(function () {
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
            Route::put('/aprovar/{id}', [ProfessorController::class, 'approve']);
            Route::put('/reprovar/{id}', [ProfessorController::class, 'reject']);
        });
    });
});
