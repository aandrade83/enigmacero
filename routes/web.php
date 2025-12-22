<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\FileViewerController;
use App\Http\Controllers\UserController; // <-- NUEVO
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

Route::get('/', [AuthController::class, 'showLogin'])->name('login.form');
Route::get('/login', [AuthController::class, 'showLogin']);
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/**
 * TEST GCS
 * - /test-gcs
 */
Route::get('/test-gcs', function () {
    try {
        $path = 'perm_test/.keep';
        Storage::disk('gcs')->put($path, 'ok');

        return response()->json([
            'put' => true,
            'exists' => Storage::disk('gcs')->exists($path),
            'path' => $path,
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'error_class' => get_class($e),
            'error' => $e->getMessage(),
        ], 500);
    }
});

/**
 * MODULOS (Admin)
 */
Route::middleware(['admin.only'])->group(function () {

    /**
     * USUARIOS
     */
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    /**
     * VISUALIZAR ARCHIVOS
     */
    Route::get('/files', [FileViewerController::class, 'index'])->name('files.index');
    Route::get('/files/folders', [FileViewerController::class, 'folders'])->name('files.folders');
    Route::get('/files/list', [FileViewerController::class, 'list'])->name('files.list');
    Route::get('/files/preview', [FileViewerController::class, 'preview'])->name('files.preview');
    Route::get('/files/download', [FileViewerController::class, 'download'])->name('files.download');
    Route::delete('/files/delete', [FileViewerController::class, 'delete'])->name('files.delete');

    /**
     * CARGA ARCHIVOS
     */
    Route::get('/uploads', [UploadController::class, 'index'])->name('uploads.index');
    Route::get('/uploads/folders', [UploadController::class, 'folders'])->name('uploads.folders');
    Route::post('/uploads', [UploadController::class, 'store'])->name('uploads.store');

    /**
     * CLIENTES
     */
    Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
    Route::get('/clients/create', [ClientController::class, 'create'])->name('clients.create');
    Route::post('/clients', [ClientController::class, 'store'])->name('clients.store');
    Route::get('/clients/{client}/edit', [ClientController::class, 'edit'])->name('clients.edit');
    Route::put('/clients/{client}', [ClientController::class, 'update'])->name('clients.update');
    Route::delete('/clients/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');
});
