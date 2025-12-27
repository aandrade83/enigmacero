<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

use App\Http\Controllers\ClientController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\FileViewerController;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

Route::get('/', [AuthController::class, 'showLogin'])->name('login.form');
Route::get('/login', [AuthController::class, 'showLogin']);
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Usuarios (solo admin)
Route::middleware(["admin.only"])->group(function () {
    Route::resource("users", UserController::class)->except(["show"]);
});

/**
 * TEST GCS
 * - /test-gcs
 * - /test-gcs?debug=1
 */

Route::get('/test-gcs', function () {
    try {
        $path = 'perm_test/.keep';

        Storage::disk('gcs')->put($path, 'ok'); // si falla, ahora debería lanzar excepción
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


/*Route::get('/test-gcs', function () {
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


*/

/**
 * CLIENTES (Admin)
 */
    // Archivos (Admin / Employee / Client)
    Route::middleware(['role:admin,employee,client'])->group(function () {
        Route::get('/files', [FileViewerController::class, 'index'])->name('files.index');
        Route::post('/files/folders', [FileViewerController::class, 'folders'])->name('files.folders');
        Route::post('/files/list', [FileViewerController::class, 'listFiles'])->name('files.list');
        Route::post('/files/preview', [FileViewerController::class, 'preview'])->name('files.preview');
        Route::post('/files/download', [FileViewerController::class, 'download'])->name('files.download');

        // Carga (Admin / Employee / Client)
        Route::get('/uploads', [UploadController::class, 'index'])->name('uploads.index');
        Route::post('/uploads/folders', [UploadController::class, 'folders'])->name('uploads.folders');
        Route::post('/uploads', [UploadController::class, 'store'])->name('uploads.store');
    });

    // BORRAR archivos: SOLO Admin
    Route::delete('/files/delete', [FileViewerController::class, 'delete'])
        ->middleware(['admin.only'])
        ->name('files.delete');

    // Clientes (Admin / Employee) - Employee NO puede borrar
    Route::middleware(['role:admin,employee'])->group(function () {
        Route::resource('clients', ClientController::class)->except(['show', 'destroy']);
    });

    Route::delete('/clients/{client}', [ClientController::class, 'destroy'])
        ->middleware(['admin.only'])
        ->name('clients.destroy');
