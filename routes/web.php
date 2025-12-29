<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\FileViewerController;
use App\Http\Controllers\UploadController;

Route::get('/', function () {
    return redirect('/dashboard');
});

// Login / Logout
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rutas protegidas (cualquier usuario autenticado)
Route::middleware(['auth.only'])->group(function () {

    //Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
	Route::get('/dashboard', function () { return view('dashboard');})->name('dashboard');

    // Archivos (admin/employee/client)
    Route::get('/files', [FileViewerController::class, 'index'])->name('files.index');
    Route::post('/files/folders', [FileViewerController::class, 'folders'])->name('files.folders');
    Route::post('/files/list', [FileViewerController::class, 'listFiles'])->name('files.list');
    Route::get('/files/preview', [FileViewerController::class, 'preview'])->name('files.preview');
    Route::get('/files/download', [FileViewerController::class, 'download'])->name('files.download');
    Route::delete('/files/delete', [FileViewerController::class, 'delete'])->name('files.delete');

    // Cargas (admin/employee/client)
    Route::get('/uploads', [UploadController::class, 'index'])->name('uploads.index');
    Route::post('/uploads/folders', [UploadController::class, 'folders'])->name('uploads.folders');
    Route::post('/uploads', [UploadController::class, 'store'])->name('uploads.store');

    // Administración de clientes: admin y employee (pero solo admin puede borrar)
    Route::middleware(['role:admin,employee'])->group(function () {
        Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
        Route::get('/clients/create', [ClientController::class, 'create'])->name('clients.create');
        Route::post('/clients', [ClientController::class, 'store'])->name('clients.store');
        Route::get('/clients/{client}/edit', [ClientController::class, 'edit'])->name('clients.edit');
        Route::put('/clients/{client}', [ClientController::class, 'update'])->name('clients.update');
        Route::delete('/clients/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');
    });

    // Usuarios: solo admin
    Route::middleware(['admin.only'])->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });
});
