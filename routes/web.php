<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleAuthController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('auth/google', [GoogleAuthController::class, 'redirectToGoogle']);
Route::get('api/auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback']);
