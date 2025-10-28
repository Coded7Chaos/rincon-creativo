<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\AuthController;

// Rutas públicas de Autenticación
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
// Ruta para probar la obtención de la listenKey de Binance
Route::get('/test-binance-key', [TestController::class, 'probarListenKey']);

//Ruta para el webhook de binance, no requiere autenticacion, pero se comprueba la firma en el controlador
Route::post('/orders/payment-webhook', [OrderController::class, 'handlePaymentSuccess']);

// Rutas protegidas (requieren un token válido) y tener el rol de client
Route::middleware('auth:sanctum')->group(function () {
    
    // Ruta de Logout (requiere estar logueado para "desloguearse")
    Route::post('/logout', [AuthController::class, 'logout']);

    //Ruta para crear ordenes, se necesita estar loggeado
    Route::post('/orders/initiate-payment',[OrderController::class, 'initiatePayment']);

    // Grupo de rutas solo para admins
    Route::middleware('can:is-admin')
         ->group(function () {
        
        // GET /api/users
        Route::get('/users', [UserController::class, 'index']);

        //POST /api/users
        Route::post('/users', [UserController::class, 'store']);

        //GET /api/orders
        Route::get('/orders', [OrderController::class, 'index']);

    });

    Route::middleware('canany:is-admin,is-fulfillment')
         ->group(function () {
        
        Route::get('/orders', [OrderController::class, 'index']);
        });

    // Aquí podrías poner rutas para usuarios normales (rol 'client')
    // ej: GET /api/profile
    Route::get('/profile', function(Request $request) {
        return $request->user();
    });

});