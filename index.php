<?php
require_once 'Route.php';

// Définir les routes
Route::get('users', [UserController::class, 'index']);
Route::get('users/{id}', [UserController::class, 'show']);

// Grouper les routes avec un préfixe et middleware
Route::group(['prefix' => 'api', 'middleware' => [AuthMiddleware::class]], function() {
    Route::post('users', [UserController::class, 'store']);
    Route::put('users/{id}', [UserController::class, 'update']);
    Route::delete('users/{id}', [UserController::class, 'destroy']);
});

// Dispatcher les routes
Route::dispatch();