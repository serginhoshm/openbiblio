<?php

declare(strict_types=1);

namespace OpenBiblio\Modern\Auth;

final class NativeSessionStore implements SessionStore
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_DISABLED) {
            throw new \RuntimeException('Sessões PHP estão desabilitadas.');
        }
        if (session_status() === PHP_SESSION_NONE) {
            $timeout = self::sessionTimeout();
            ini_set('session.use_strict_mode', '1');
            ini_set('session.use_only_cookies', '1');
            ini_set('session.cookie_httponly', '1');
            ini_set('session.cookie_samesite', 'Lax');
            ini_set('session.gc_maxlifetime', (string) ($timeout * 60));
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            if (!session_start()) {
                throw new \RuntimeException('Não foi possível iniciar uma sessão PHP.');
            }
        }
        $this->enforceIdleTimeout(self::sessionTimeout());
    }

    public function get(string $key): mixed
    {
        return $_SESSION[$key] ?? null;
    }

    public function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public function regenerate(): void
    {
        if (!session_regenerate_id(true)) {
            throw new \RuntimeException('Não foi possível renovar o identificador da sessão.');
        }
    }

    public function destroy(): void
    {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            if (!session_destroy()) {
                throw new \RuntimeException('Não foi possível invalidar a sessão.');
            }
        }

        if (ini_get('session.use_cookies')) {
            $parameters = session_get_cookie_params();
            if (!setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $parameters['path'],
                'domain' => $parameters['domain'],
                'secure' => $parameters['secure'],
                'httponly' => $parameters['httponly'],
                'samesite' => $parameters['samesite'] ?? 'Lax',
            ])) {
                throw new \RuntimeException('Não foi possível expirar o cookie da sessão.');
            }
        }
    }

    private function enforceIdleTimeout(int $timeoutMinutes): void
    {
        $now = time();
        $lastActivity = $_SESSION['lastActivity'] ?? null;
        if (is_int($lastActivity) && $now - $lastActivity > $timeoutMinutes * 60) {
            $this->destroy();
            if (!session_start()) {
                throw new \RuntimeException('Não foi possível iniciar uma nova sessão após a expiração.');
            }
        }
        $_SESSION['lastActivity'] = $now;
    }

    private static function sessionTimeout(): int
    {
        $configured = getenv('OPENBIBLIO_SESSION_TIMEOUT');
        if ($configured === false || $configured === '') {
            return 30;
        }

        $timeout = filter_var($configured, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 1440],
        ]);
        if ($timeout === false) {
            throw new \UnexpectedValueException(
                'OPENBIBLIO_SESSION_TIMEOUT deve estar entre 1 e 1440 minutos.',
            );
        }

        return $timeout;
    }
}
