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
        if (request()->boolean('debug')) {
            $disk = config('filesystems.disks.gcs');

            return response()->json([
                'getenv' => [
                    'GOOGLE_CLOUD_PROJECT' => getenv('GOOGLE_CLOUD_PROJECT') ?: null,
                    'GCS_BUCKET'           => getenv('GCS_BUCKET') ?: null,
                    'GCS_PATH_PREFIX'      => getenv('GCS_PATH_PREFIX') ?: null,
                ],
                'env' => [
                    'GOOGLE_CLOUD_PROJECT' => env('GOOGLE_CLOUD_PROJECT'),
                    'GCS_BUCKET'           => env('GCS_BUCKET'),
                    'GCS_PATH_PREFIX'      => env('GCS_PATH_PREFIX'),
                ],
                'config_cache_exists' => file_exists(base_path('bootstrap/cache/config.php')),
                'disk' => $disk,
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
