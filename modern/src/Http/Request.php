<?php

declare(strict_types=1);

namespace OpenBiblio\Modern\Http;

final class Request
{
    /**
     * @param array<string, mixed> $query
     * @param array<string, mixed> $body
     */
    public function __construct(
        public readonly string $method,
        public readonly string $path,
        public readonly array $query = [],
        public readonly array $body = [],
    ) {
        if ($method === '' || $path === '' || $path[0] !== '/') {
            throw new \InvalidArgumentException('A requisição precisa de método e caminho absolutos válidos.');
        }
    }

    public static function fromGlobals(): self
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? null;
        $uri = $_SERVER['REQUEST_URI'] ?? null;

        if (!is_string($method) || !is_string($uri)) {
            throw new \RuntimeException('O servidor não forneceu método ou URI da requisição.');
        }

        $path = parse_url($uri, PHP_URL_PATH);
        if (!is_string($path) || $path === '') {
            throw new \RuntimeException('A URI da requisição não contém um caminho válido.');
        }

        return new self(strtoupper($method), $path, $_GET, $_POST);
    }
}
