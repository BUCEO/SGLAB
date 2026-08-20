<?php

use App\Http\Controllers\EquipmentController;
use Illuminate\Support\Facades\Route;

// Rutas para equipos
Route::prefix('equipments')->group(function () {
    Route::get('/', [EquipmentController::class, 'index']);
    Route::post('/', [EquipmentController::class, 'store']);
    Route::get('/{id}', [EquipmentController::class, 'show']);
    Route::put('/{id}', [EquipmentController::class, 'update']);
    Route::delete('/{id}', [EquipmentController::class, 'destroy']);
    Route::post('/import', [EquipmentController::class, 'import']);
    Route::get('/{id}/history', [EquipmentController::class, 'history']);
});

// Rutas para tipos de equipos
Route::prefix('equipment-types')->group(function () {
    Route::get('/', [EquipmentTypeController::class, 'index']);
    Route::post('/', [EquipmentTypeController::class, 'store']);
});