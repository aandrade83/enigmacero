<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
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
Route::middleware(['admin.only'])->group(function () {


    //VISUALIZAR ARCHIVOS
	Route::get('/files', [FileViewerController::class, 'index'])->name('files.index');
	Route::get('/files/folders', [FileViewerController::class, 'folders'])->name('files.folders');
	Route::get('/files/list', [FileViewerController::class, 'list'])->name('files.list');
	Route::get('/files/preview', [FileViewerController::class, 'preview'])->name('files.preview');
	Route::get('/files/download', [FileViewerController::class, 'download'])->name('files.download');
	Route::delete('/files/delete', [FileViewerController::class, 'delete'])->name('files.delete');








    //CARGA ARCHIVOS
    Route::get('/uploads', [UploadController::class, 'index'])->name('uploads.index');
    Route::get('/uploads/folders', [UploadController::class, 'folders'])->name('uploads.folders');
    Route::post('/uploads', [UploadController::class, 'store'])->name('uploads.store');




    // CLIENTES 

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
