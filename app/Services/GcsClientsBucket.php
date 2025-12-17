<?php

namespace App\Services;

use Google\Cloud\Storage\Bucket;
use Google\Cloud\Storage\StorageClient;
use Illuminate\Support\Str;
use RuntimeException;

class GcsClientsBucket
{
    public function __construct(
        private readonly StorageClient $storage,
    ) {}

    private function bucketName(): string
    {
        $bucketName = (string) config('enigmacero.gcs_clients_bucket', '');

        $bucketName = trim($bucketName);
        if ($bucketName === '') {
            throw new RuntimeException(
                'GCS bucket no configurado. Definí GCS_CLIENTS_BUCKET en Cloud Run (ej: clientes_enigma).'
            );
        }

        return $bucketName;
    }

    private function bucket(): Bucket
    {
        $bucket = $this->storage->bucket($this->bucketName());

        // Opcional pero recomendado (para fallar con error claro)
        if (method_exists($bucket, 'exists') && !$bucket->exists()) {
            throw new RuntimeException(
                "El bucket '{$this->bucketName()}' no existe o el service account no tiene permisos."
            );
        }

        return $bucket;
    }

    private function prefix(): string
    {
        $prefix = (string) config('enigmacero.gcs_clients_prefix', 'clientes/');
        $prefix = trim($prefix);

        // normaliza a "clientes/"
        $prefix = rtrim($prefix, '/') . '/';

        // evita prefijos raros
        if ($prefix === '/' || $prefix === './' || Str::contains($prefix, ['..'])) {
            $prefix = 'clientes/';
        }

        return $prefix;
    }

    private function normalizeFolderName(string $folderName): string
    {
        $folderName = trim($folderName);

        // quita slashes y cosas raras
        $folderName = trim($folderName, "/ \t\n\r\0\x0B");

        // evita path traversal
        if ($folderName === '' || Str::contains($folderName, ['..', '\\'])) {
            throw new RuntimeException('folderName inválido.');
        }

        // normaliza espacios y mayúsculas (carpeta amigable para URLs/buckets)
        $folderName = Str::slug($folderName, '_');

        if ($folderName === '') {
            throw new RuntimeException('folderName inválido luego de normalizar.');
        }

        return $folderName;
    }

    /**
     * “Crea carpeta” creando un objeto vacío .keep dentro del prefijo.
     * Ej: clientes/acme_corp/.keep
     *
     * Retorna el objectName guardable en DB (folder + .keep).
     */
    public function createClientFolder(string $folderName): string
    {
        $folder = $this->normalizeFolderName($folderName);
        $objectName = $this->prefix() . $folder . '/.keep';

        $this->bucket()->upload('', [
            'name' => $objectName,
            'contentType' => 'text/plain',
        ]);

        return $objectName;
    }

    /**
     * “Elimina carpeta” borrando TODO lo que tenga ese prefijo.
     * Ej: borra clientes/acme_corp/*
     */
    public function deleteClientFolder(string $folderName): void
    {
        $folder = $this->normalizeFolderName($folderName);
        $prefix = $this->prefix() . $folder . '/';

        $bucket = $this->bucket();
        foreach ($bucket->objects(['prefix' => $prefix]) as $object) {
            $object->delete();
        }
    }

    /**
     * Útil para guardar en DB el nombre "limpio" de carpeta.
     */
    public function folderKey(string $folderName): string
    {
        return $this->normalizeFolderName($folderName);
    }
}

