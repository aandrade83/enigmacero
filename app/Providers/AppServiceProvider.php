<?php 

use Google\Cloud\Storage\StorageClient;
use Illuminate\Support\Facades\Storage;
use Illuminate\Filesystem\FilesystemAdapter;
use League\Flysystem\Filesystem;
use League\Flysystem\GoogleCloudStorage\GoogleCloudStorageAdapter;

public function boot(): void
{
    Storage::extend('gcs', function ($app, $config) {
        $clientConfig = [];

        if (!empty($config['project_id'])) {
            $clientConfig['projectId'] = $config['project_id'];
        }

        // Si NO hay key_file_path, Google usa ADC (perfecto para Cloud Run)
        if (!empty($config['key_file_path'])) {
            $clientConfig['keyFilePath'] = $config['key_file_path'];
        }

        $storageClient = new StorageClient($clientConfig);
        $bucket = $storageClient->bucket($config['bucket']);

        $adapter = new GoogleCloudStorageAdapter(
            $bucket,
            $config['path_prefix'] ?? ''
        );

        $filesystem = new Filesystem($adapter);

        return new FilesystemAdapter($filesystem, $adapter, $config);
    });
}

