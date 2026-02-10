<?php

use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\BookController;
use Illuminate\Support\Facades\Route;

// Routes d'authentification (non protégées)
Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);

// Route de déconnexion (protégée)
Route::middleware('auth:sanctum')->post('/logout', [UserController::class, 'logout']);

// Routes publiques pour les livres (lecture seule)
Route::get('/books', [BookController::class, 'index']);
Route::get('/books/{book}', [BookController::class, 'show']);

// Routes protégées (création, modification, suppression)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/books', [BookController::class, 'store']);
    Route::match(['patch', 'put'], '/books/{book}', [BookController::class, 'update']);
    Route::delete('/books/{book}', [BookController::class, 'destroy']);
});