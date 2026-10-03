<?php

declare(strict_types=1);

namespace OpenBiblio\Modern\Http;

final class Response
{
    private const SECURITY_HEADERS = [
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'DENY',
        'Referrer-Policy' => 'no-referrer',
        'Content-Security-Policy' => "default-src 'self'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'",
    ];

    /**
     * @var array<string, string>
     */
    public readonly array $headers;

    /**
     * @param array<string, string> $headers
     */
    public function __construct(
        public readonly string $body,
        public readonly int $statusCode = 200,
        array $headers = ['Content-Type' => 'text/html; charset=UTF-8'],
    ) {
        if ($statusCode < 100 || $statusCode > 599) {
            throw new \InvalidArgumentException('Código HTTP fora do intervalo permitido.');
        }
        $this->headers = array_merge(self::SECURITY_HEADERS, $headers);
    }

    public static function json(array $data, int $statusCode = 200): self
    {
        return new self(
            json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
            $statusCode,
            ['Content-Type' => 'application/json; charset=UTF-8'],
        );
    }

    public function send(): void
    {
        http_response_code($this->statusCode);
        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }

        echo $this->body;
    }
}
