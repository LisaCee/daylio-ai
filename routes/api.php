<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MoodEntryController;

// Public authentication routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    // User management
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/user/profile', [AuthController::class, 'updateProfile']);
    Route::put('/user/password', [AuthController::class, 'changePassword']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Health check for authenticated users
    Route::get('/auth/check', function (Request $request) {
        return response()->json([
            'authenticated' => true,
            'user' => $request->user()->only(['id', 'name', 'email'])
        ]);
    });

    // Mood entry management
    Route::apiResource('mood-entries', MoodEntryController::class);
    Route::get('/mood-entries-stats', [MoodEntryController::class, 'stats']);
});
