<?php

declare(strict_types=1);

namespace OpenBiblio\Modern\Database;

final class DatabaseConfig
{
    public function __construct(
        public readonly string $host,
        public readonly int $port,
        public readonly string $database,
        public readonly string $username,
        public readonly string $password,
    ) {
        if ($host === '' || str_contains($host, ';') || str_contains($host, "\0")) {
            throw new \InvalidArgumentException('Host MySQL inválido.');
        }
        if ($port < 1 || $port > 65535) {
            throw new \InvalidArgumentException('Porta MySQL fora do intervalo permitido.');
        }
        if ($database === '' || str_contains($database, ';') || str_contains($database, "\0")) {
            throw new \InvalidArgumentException('Nome do banco MySQL inválido.');
        }
        if ($username === '') {
            throw new \InvalidArgumentException('O usuário MySQL não pode ser vazio.');
        }
    }

    public static function fromEnvironment(): self
    {
        $configPath = getenv('OPENBIBLIO_DB_CONFIG');
        if ($configPath !== false && $configPath !== '') {
            return self::fromJsonFile($configPath);
        }

        $portValue = getenv('OPENBIBLIO_DB_PORT');
        if ($portValue === false || $portValue === '') {
            $port = 3306;
        } else {
            $validatedPort = filter_var(
                $portValue,
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1, 'max_range' => 65535]],
            );
            if ($validatedPort === false) {
                throw new \UnexpectedValueException('OPENBIBLIO_DB_PORT deve ser um inteiro entre 1 e 65535.');
            }
            $port = $validatedPort;
        }

        return new self(
            self::requiredEnvironmentValue('OPENBIBLIO_DB_HOST'),
            $port,
            self::requiredEnvironmentValue('OPENBIBLIO_DB_NAME'),
            self::requiredEnvironmentValue('OPENBIBLIO_DB_USER'),
            self::requiredEnvironmentValue('OPENBIBLIO_DB_PASSWORD', true),
        );
    }

    private static function fromJsonFile(string $path): self
    {
        if (!is_file($path) || !is_readable($path)) {
            throw new \UnexpectedValueException('O arquivo de configuração OPENBIBLIO_DB_CONFIG não pode ser lido.');
        }
        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new \RuntimeException('Não foi possível ler o arquivo de configuração do banco.');
        }
        $config = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($config)) {
            throw new \UnexpectedValueException('O arquivo de configuração do banco deve conter um objeto JSON.');
        }
        foreach (['host', 'database', 'username', 'password'] as $key) {
            if (!is_string($config[$key] ?? null)) {
                throw new \UnexpectedValueException('O arquivo de configuração do banco não contém valores válidos.');
            }
        }
        $port = $config['port'] ?? 3306;
        if (!is_int($port)) {
            throw new \UnexpectedValueException('A porta no arquivo de configuração do banco deve ser um inteiro.');
        }

        return new self(
            $config['host'],
            $port,
            $config['database'],
            $config['username'],
            $config['password'],
        );
    }

    public function dsn(): string
    {
        return sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            $this->host,
            $this->port,
            $this->database,
        );
    }

    private static function requiredEnvironmentValue(string $name, bool $allowEmpty = false): string
    {
        $value = getenv($name);
        if ($value === false || (!$allowEmpty && trim($value) === '')) {
            throw new \UnexpectedValueException("A variável de ambiente {$name} precisa ser configurada.");
        }

        return $value;
    }
}
