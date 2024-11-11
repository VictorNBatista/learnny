<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfessorController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/user/cadastrar', [UserController::class, 'store']);
Route::post('/professor/cadastrar', [ProfessorController::class, 'store']);
Route::put('/professor/atualizar/{id}', [ProfessorController::class, 'update']);

Route::middleware('auth:api')->group(function () {
  Route::post('/logout', [AuthController::class, 'logout']);

  Route::prefix('/user')->group(function () {
      Route::get('/listar', [UserController::class, 'index']);
      Route::put('/atualizar/{id}', [UserController::class, 'update']);
      Route::delete('/deletar/{id}', [UserController::class, 'destroy']);
      Route::get('/visualizar/{id}', [UserController::class, 'show']);
  });

  Route::prefix('/professor')->group(function () {
    Route::get('/listar', [ProfessorController::class, 'index']);
    Route::get('/visualizar/{id}', [ProfessorController::class, 'show']);
    
    Route::delete('/deletar/{id}', [ProfessorController::class, 'destroy']);
  });  
});


