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

/**
 * TEST GCS
 * - /test-gcs
 * - /test-gcs?debug=1
 */
Route::get('/test-gcs', function () {
    try {
        // Debug de config (no expone secretos)
        if (request()->boolean('debug')) {
            $disk = config('filesystems.disks.gcs');
            return response()->json([
                'exists'           => !empty($disk),
                'driver'           => $disk['driver'] ?? null,
                'project'          => $disk['project_id'] ?? null,
                'bucket'           => $disk['bucket'] ?? null,
                'prefix'           => $disk['path_prefix'] ?? null,
                'has_keyfile_path' => !empty($disk['key_file_path'] ?? null),
            ]);
        }

        Storage::disk('gcs')->makeDirectory('test_folder');
        return response('OK', 200);

    } catch (\Throwable $e) {
        Log::error('GCS test failed', ['error' => $e->getMessage()]);
        return response('GCS ERROR: ' . $e->getMessage(), 500);
    }
})->middleware('admin.only')->name('gcs.test');

/**
 * CLIENTES (Admin)
 */
Route::middleware(['admin.only'])->group(function () {

    Route::get('/clients', [ClientController::class, 'index'])
        ->name('clients.index');

    Route::get('/clients/create', [ClientController::class, 'create'])
        ->name('clients.create');

    Route::post('/clients', [ClientController::class, 'store'])
        ->name('clients.store');

    Route::get('/clients/{client}/edit', [ClientController::class, 'edit'])
        ->name('clients.edit');

    Route::put('/clients/{client}', [ClientController::class, 'update'])
        ->name('clients.update');

    Route::delete('/clients/{client}', [ClientController::class, 'destroy'])
        ->name('clients.destroy');
});
