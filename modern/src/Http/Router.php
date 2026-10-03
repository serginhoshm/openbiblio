<?php

declare(strict_types=1);

namespace OpenBiblio\Modern\Http;

final class Router
{
    /** @var array<string, array<string, callable(Request): Response>> */
    private array $routes = [];

    /**
     * @param callable(Request): Response $handler
     */
    public function get(string $path, callable $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    /**
     * @param callable(Request): Response $handler
     */
    public function post(string $path, callable $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    /**
     * @param callable(Request): Response $handler
     */
    private function add(string $method, string $path, callable $handler): void
    {
        if ($path === '' || $path[0] !== '/') {
            throw new \InvalidArgumentException('O caminho da rota deve ser absoluto.');
        }
        if (isset($this->routes[$method][$path])) {
            throw new \LogicException("A rota {$method} {$path} já foi registrada.");
        }

        $this->routes[$method][$path] = $handler;
    }

    public function dispatch(Request $request): Response
    {
        $handler = $this->routes[$request->method][$request->path] ?? null;
        if ($handler === null) {
            $allowedMethods = [];
            foreach ($this->routes as $method => $routes) {
                if (isset($routes[$request->path])) {
                    $allowedMethods[] = $method;
                }
            }
            if ($allowedMethods !== []) {
                return new Response(
                    'Método não permitido.',
                    405,
                    [
                        'Content-Type' => 'text/plain; charset=UTF-8',
                        'Allow' => implode(', ', $allowedMethods),
                    ],
                );
            }

            return new Response('Página não encontrada.', 404);
        }

        return $handler($request);
    }
}
