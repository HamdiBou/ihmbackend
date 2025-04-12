<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\OffreController;
use App\Http\Controllers\CandidatureController;
use Illuminate\Support\Facades\Route;

// Public endpoints
Route::get('/offres', [OffreController::class, 'index']);
Route::get('/offres/{id}', [OffreController::class, 'show']);

// Auth endpoints
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected endpoints (require auth)
Route::middleware('auth:sanctum')->group(function () {
    // User profile
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Job seeker routes
    Route::prefix('chercheur')->group(function () {
        Route::get('/candidatures', [CandidatureController::class, 'index']);
        Route::post('/candidatures', [CandidatureController::class, 'store']);
        Route::get('/candidatures/{id}', [CandidatureController::class, 'show']);
        Route::delete('/candidatures/{id}', [CandidatureController::class, 'destroy']);
    });

    // Employer routes
    Route::middleware('employer')->group(function () {
        Route::post('/offres', [OffreController::class, 'store']);
        Route::put('/offres/{id}', [OffreController::class, 'update']);
        Route::delete('/offres/{id}', [OffreController::class, 'destroy']);
        Route::get('/offres/mes-offres', [OffreController::class, 'employerOffres']);
        Route::get('/offres/{id}/candidatures', [OffreController::class, 'offreCandidatures']);
    });
});
?>