<?php

use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\BookController;
use Illuminate\Support\Facades\Route;

// Routes d'authentification avec limitation de taux (10 requêtes par minute)
Route::middleware('throttle:10,1')->group(function () {
    Route::post('/register', [UserController::class, 'register']);
    Route::post('/login', [UserController::class, 'login']);
});

// Route de déconnexion (protégée)
Route::middleware('auth:sanctum')->post('/logout', [UserController::class, 'logout']);

// Routes publiques pour les livres (lecture seule)
Route::get('/books', [BookController::class, 'index'])->name('books.index');
Route::get('/books/{book}', [BookController::class, 'show'])->name('books.show');

// Routes protégées (création, modification, suppression)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/books', [BookController::class, 'store'])->name('books.store');
    Route::match(['patch', 'put'], '/books/{book}', [BookController::class, 'update'])->name('books.update');
    Route::delete('/books/{book}', [BookController::class, 'destroy'])->name('books.destroy');
});