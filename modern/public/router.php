<?php

declare(strict_types=1);

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$file = __DIR__ . (is_string($path) ? $path : '/');
$resolvedFile = realpath($file);
$publicDirectory = realpath(__DIR__);

if (
    $path !== '/'
    && $resolvedFile !== false
    && $publicDirectory !== false
    && str_starts_with($resolvedFile, $publicDirectory . DIRECTORY_SEPARATOR)
    && is_file($resolvedFile)
) {
    return false;
}

require __DIR__ . '/index.php';
