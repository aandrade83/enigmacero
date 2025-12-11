<?php

// Router para el servidor embebido de PHP en producción (Cloud Run)
// Similar a cómo trabaja "php artisan serve"

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

$publicPath = __DIR__ . '/public';

// Si el archivo existe dentro de /public lo sirve directamente (CSS, JS, imágenes, SVG, etc.)
if ($uri !== '/' &&
    file_exists($publicPath . $uri) &&
    !is_dir($publicPath . $uri)
) {
    return false; // deja que el servidor embebido lo sirva tal cual
}

// Si no existe como archivo estático, carga la app de Laravel
require_once $publicPath . '/index.php';
