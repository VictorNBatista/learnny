<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/cadastrar', [UserController::class, 'store']);
Route::get('/users', [UserController::class, 'index']);

Route::middleware('auth:api')->group(function () {
  Route::post('/logout',[AuthController::class, 'logout']);

  Route::prefix('/user')->group(function (){
      Route::put('/atualizar/{id}', [UserController::class, 'update']);
      Route::delete('/deletar/{id}', [UserController::class, 'destroy']);
      Route::get('/visualizar/{id}', [UserController::class, 'show']);
  });
});
