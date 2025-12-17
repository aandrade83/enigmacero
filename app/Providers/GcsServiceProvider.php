<?php

namespace App\Providers;

use App\Services\GcsClientsBucket;
use Google\Cloud\Storage\StorageClient;
use Illuminate\Support\ServiceProvider;

class GcsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(StorageClient::class, function () {
            // En Cloud Run usa credenciales automáticas del service account
            return new StorageClient();
        });

        $this->app->singleton(GcsClientsBucket::class, function ($app) {
            return new GcsClientsBucket($app->make(StorageClient::class));
        });
    }
}
