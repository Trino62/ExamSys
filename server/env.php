<?php
/**
 * Cargador de archivo .env
 * Las variables del entorno del sistema (ej. Docker) tienen precedencia.
 */
function loadEnv(string $path): void
{
    static $loaded = [];
    if (isset($loaded[$path])) return;
    $loaded[$path] = true;

    if (!file_exists($path)) return;

    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) continue;

        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value, " \t\"'");

        // No sobreescribir variables que ya existen en el entorno (Docker, etc.)
        if ($key !== '' && getenv($key) === false) {
            putenv("$key=$value");
            $_ENV[$key]    = $value;
            $_SERVER[$key] = $value;
        }
    }
}
