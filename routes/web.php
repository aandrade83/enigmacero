<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Ruta raíz: mostramos el formulario de login
Route::get('/', [AuthController::class, 'showLogin'])->name('login');

// Procesar el formulario de login
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

// Dashboard protegido (después de loguearse)
Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');

// Cerrar sesión
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
