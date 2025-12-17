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
    $cfg = config('filesystems.disks.gcs');

    // Modo debug: /test-gcs?debug=1
    if (request()->query('debug') == 1) {
        return response()->json([
            'exists'   => is_array($cfg),
            'driver'   => $cfg['driver'] ?? null,
            'project'  => $cfg['project_id'] ?? null,
            'bucket'   => $cfg['bucket'] ?? null,
            'prefix'   => $cfg['path_prefix'] ?? null,
            'has_keyfile_path' => !empty($cfg['key_file_path'] ?? null),
        ]);
    }

    try {
        Storage::disk('gcs')->makeDirectory('clientes/test_folder');
        return response('OK', 200);
    } catch (\Throwable $e) {
        Log::error('GCS test failed', ['error' => $e->getMessage()]);
        return response('GCS ERROR: ' . $e->getMessage(), 500);
    }
});


Route::get('/clients/create', [ClientController::class, 'create'])
    ->middleware('admin.only')
    ->name('clients.create');

Route::post('/clients', [ClientController::class, 'store'])
    ->middleware('admin.only')
    ->name('clients.store');

