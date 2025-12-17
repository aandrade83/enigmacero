<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;


Route::get('/', [AuthController::class, 'showLogin'])->name('login.form');
Route::get('/login', [AuthController::class, 'showLogin']);

Route::post('/login', [AuthController::class, 'login'])->name('login.post');

Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/clients', [ClientController::class, 'index'])
    ->middleware('admin.only')
    ->name('clients.index');

Route::get('/test-gcs', function () {
    try {
        Storage::disk('gcs')->makeDirectory('clientes/test_folder');
        return response('OK', 200);
    } catch (\Throwable $e) {
        Log::error('GCS test failed', ['error' => $e->getMessage()]);
        return response('GCS ERROR: '.$e->getMessage(), 500);
    }
});

Route::get('/clients/create', [ClientController::class, 'create'])
    ->middleware('admin.only')
    ->name('clients.create');

Route::post('/clients', [ClientController::class, 'store'])
    ->middleware('admin.only')
    ->name('clients.store');

