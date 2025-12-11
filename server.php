<?php

// Router para el servidor embebido de PHP en Cloud Run / desarrollo

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

// Ruta al archivo dentro de /public
$publicPath = __DIR__ . '/public' . $uri;

// Si existe un archivo físico en public (CSS, JS, imágenes, SVG, etc.)
// se lo dejamos servir directamente al servidor embebido:
if ($uri !== '/' && file_exists($publicPath)) {
    return false;
}

// Para todo lo demás, cargamos Laravel:
require __DIR__ . '/public/index.php';
