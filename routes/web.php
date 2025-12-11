<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Página de login
Route::get('/', [AuthController::class, 'showLogin'])->name('login');

// Procesar login
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

// Dashboard (protegido por sesión, por ahora lo controlaremos en el controlador)
Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');

// Cerrar sesión
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
