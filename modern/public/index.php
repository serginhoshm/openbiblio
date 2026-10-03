<?php

declare(strict_types=1);

use OpenBiblio\Modern\Application;
use OpenBiblio\Modern\Http\Request;
use OpenBiblio\Modern\Http\Response;

if (PHP_VERSION_ID < 80200) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'A reescrita do OpenBiblio requer PHP 8.2 ou superior.';
    exit;
}

$autoload = dirname(__DIR__, 2) . '/vendor/autoload.php';
if (!is_file($autoload)) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Dependências da reescrita não instaladas. Execute composer install.';
    exit;
}

require $autoload;

try {
    (new Application())->handle(Request::fromGlobals())->send();
} catch (Throwable $error) {
    error_log(sprintf(
        'OpenBiblio request failed (%s): %s',
        $error::class,
        $error->getMessage(),
    ));
    (new Response('Ocorreu um erro interno. Tente novamente mais tarde.', 500))->send();
}
