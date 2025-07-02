<?php

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function(){
    Route::post('/logout',[AuthController::class,'logout']);
     Route::get('/user', [AuthController::class, 'user']);
     Route::get('/users', [AuthController::class, 'allUsers']);
        Route::put('/user/{id}', [AuthController::class, 'updateUser']);
});


