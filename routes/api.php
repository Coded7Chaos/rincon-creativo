<?php

use Illuminate\Support\Facades\Route;
//use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\AuthController;


Route::apiResource('categories', CategoryController::class);
Route::apiResource('products', ProductController::class);

// Rutas públicas de Autenticación
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);



// Rutas protegidas (requieren un token válido) y tener el rol de client
Route::middleware('auth:sanctum')->group(function () {
    
    // Ruta de Logout (requiere estar logueado para "desloguearse")
    Route::post('/logout', [AuthController::class, 'logout']);

    // Grupo de rutas solo para admins
    Route::middleware('can:is-admin')
         ->group(function () {
        
        // GET /api/users
        Route::get('/users', [UserController::class, 'index']);
    });

    // Aquí podrías poner rutas para usuarios normales (rol 'client')
    // ej: GET /api/profile
    Route::get('/profile', function(Request $request) {
        return $request->user();
    });

});