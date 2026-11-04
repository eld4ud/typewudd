<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DuelController;
use App\Http\Controllers\TypingController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');

Route::get('/typing-test', [TypingController::class, 'index']);
Route::get('/api/typing-content', [TypingController::class, 'content'])
    ->middleware('throttle:60,1');
Route::get('/leaderboard', [TypingController::class, 'leaderboard']);

Route::get('/login', [AuthController::class, 'login'])
    ->middleware('guest')
    ->name('login');
Route::post('/login', [AuthController::class, 'authenticate'])
    ->middleware(['guest', 'throttle:10,1']);
Route::get('/register', [AuthController::class, 'register'])->middleware('guest');
Route::post('/register', [AuthController::class, 'store'])
    ->middleware(['guest', 'throttle:10,1']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [TypingController::class, 'dashboard']);
    Route::post('/api/typing-results', [TypingController::class, 'save'])
        ->middleware('throttle:30,1');
    Route::post('/goals', [TypingController::class, 'goal']);
    Route::post('/report', [TypingController::class, 'report']);

    Route::get('/duel', [DuelController::class, 'index']);
    Route::post('/duel/create', [DuelController::class, 'create']);
    Route::post('/duel/join', [DuelController::class, 'join']);
    Route::get('/duel/{code}', [DuelController::class, 'show']);
    Route::get('/duel/{code}/state', [DuelController::class, 'state'])
        ->middleware('throttle:120,1');
    Route::post('/duel/{code}/ready', [DuelController::class, 'ready']);
    Route::post('/duel/{code}/progress', [DuelController::class, 'progress'])
        ->middleware('throttle:120,1');
    Route::post('/duel/{code}/finish', [DuelController::class, 'finish']);

    Route::get('/admin', [AdminController::class, 'index']);
    Route::post('/admin/content', [AdminController::class, 'content']);
    Route::post('/admin/content/{content}/toggle', [AdminController::class, 'toggle']);
    Route::post('/admin/users/{user}/toggle', [AdminController::class, 'user']);
});
