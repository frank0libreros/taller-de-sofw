<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\VentaController;

// Rutas de autenticación (no requieren JWT)
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/logout', [AuthController::class, 'logout']);

// Rutas protegidas por JWT
Route::middleware('jwt')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    
    // Rutas de ventas
    Route::post('/ventas', [VentaController::class, 'registrarVenta']);
    Route::get('/ventas', [VentaController::class, 'obtenerVentas']);
    Route::get('/ventas/{id}', [VentaController::class, 'obtenerVenta']);
});

// Ruta de salud para verificar que el gateway está activo
Route::get('/health', function () {
    return response()->json([
        'estado' => 'vivo',
        'servicio' => 'gateway-laravel',
        'timestamp' => now()
    ]);
});

