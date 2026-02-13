<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/google/disconnect', [\App\Http\Controllers\GoogleAuthController::class, 'disconnect']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::post('/profile', [ProfileController::class, 'store']);

    Route::get('/stats', [ActivityController::class, 'stats']);
    Route::get('/stats/weekly', [ActivityController::class, 'weeklyStats']);
    Route::get('/analysis', [ActivityController::class, 'dailyAnalysis']);
    Route::get('/activities', [ActivityController::class, 'index']);
    Route::post('/activities', [ActivityController::class, 'store']);

    Route::get('/meals', [\App\Http\Controllers\MealController::class, 'index']);
    Route::post('/meals', [\App\Http\Controllers\MealController::class, 'store']);
});
