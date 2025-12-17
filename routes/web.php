<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;


Route::get('/', [AuthController::class, 'showLogin'])->name('login.form');
Route::get('/login', [AuthController::class, 'showLogin']);

Route::post('/login', [AuthController::class, 'login'])->name('login.post');

Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/clients', [ClientController::class, 'index'])
    ->middleware('admin.only')
    ->name('clients.index');

Route::get('/test-gcs', function () {
    Storage::disk('gcs')->makeDirectory('clientes/test_folder');
    return 'OK';
});
